BEGIN

/*


888888b.                              888          d8b 888    
888  "88b                             888          Y8P 888    
888  .88P                             888              888    
8888888K.   .d88b.  88888b.   .d88b.  888  .d88b.  888 888888 
888  "Y88b d8P  Y8b 888 "88b d8P  Y8b 888 d8P  Y8b 888 888    
888    888 88888888 888  888 88888888 888 88888888 888 888    
888   d88P Y8b.     888  888 Y8b.     888 Y8b.     888 Y88b.  
8888888P"   "Y8888  888  888  "Y8888  888  "Y8888  888  "Y888 
_____     _        _                                                                           
| ____|___| |_ __ _| |_ _   _ ___    _ __   ___  _ __    ___ _ __ ___  _ __  _ __ ___  ___  __ _ 
|  _| / __| __/ _` | __| | | / __|  | '_ \ / _ \| '__|  / _ \ '_ ` _ \| '_ \| '__/ _ \/ __|/ _` |
| |___\__ \ || (_| | |_| |_| \__ \  | |_) | (_) | |    |  __/ | | | | | |_) | | |  __/\__ \ (_| |
|_____|___/\__\__,_|\__|\__,_|___/  | .__/ \___/|_|     \___|_| |_| |_| .__/|_|  \___||___/\__,_|
                                    |_|                               |_|                        

Primera versión: 
scabbia@gmail.com Abril 2014

Ultima actualización: 
scabbia@gmail.com Marzo 2025


*/
    -- variables globales

    DECLARE 
        _historial,     -- volcado de campo historial de la tabla t_usuarios
        _promociones,   -- lista de promociones base del modelo de negocio
        _actuales,      -- clon de estatuses actuales en base de datos para comparativa final
        _roles,         -- Roles asociados al usuario
        _estatuses,     -- array con concentrado de estatus de todas las empresas para respuesta de función
        _modelos,       -- Catálogo de modelos con su ingormación basica (Promo base, días de gracia, fecha de arranque)
        _calificaciones -- puntos del socio listados por compras en el mes
        JSON;

    DECLARE 
        _baja,          -- baja de sistema (activa solo cuando hay baja en todas las empresas)
        _inversion,     -- inversiones activas
        _k,             -- variable para ciclos while
        _i              -- variable para ciclos while
        INT default 0;

    DECLARE 
        _modelo,        -- modelo en turno (dentro de ciclo while)
        _estatus,       -- estatus del socio en el modelo actual
        _promocion,     -- revisar promociones base de una por una en promos para calificar en esa empresa
        _mes0,
        _mes1,
        _mes2			-- Variables para almacenar nomenclatura (YYYYMM) de mes actual y anteriores
        VARCHAR( 25 );

    declare 
        _verificacion,  -- estatus de verificación de cuenta del usuario (datos completos)
        _activos        -- modelos en los que el socio está activo
        MEDIUMINT;

    DECLARE 
        _registro,      -- fecha de registro del socio
        _primera,       -- fecha de primer compra de promoción para PTS de empresa en loop
        _ultima,        -- fecha de activación más reciente (en caso de existir)
        _fechabaja,     -- fecha límite para dar de baja empresas sin activación
        _tcompra,       -- fecha de compra de recarga telefonia
        _tvigencia,     -- fecha de vigencia de recarga
        _tbaja,         -- fecha de baja telefonia
        _arranque
        DATE;

    DECLARE 
        _pts0, 
        _pts1, 
        _pts2 
        DECIMAL(8,2);

    /*
    función que devuelve el estatus de un socio en el modelo de negocio especificada
    entradas: 
        id_socio
        codigo_empresa
    */

	-- array para retornar el resultado final
	SET _estatuses = JSON_OBJECT();
	
    -- si hay al menos una empresa activa, se cancela la baja
    SET _baja = 1;

    /* 
    Genera un JSON array de objetos con la siguiente estructura:
    {
        "codigo"        : "40-GASOLINAS",
        "arranque"      : "2024-08-01",
        "dias_inicio"   : "180",
        "promocion_base": "[\"414-GASOLINA\", \"415-COMODIN\"]"
    }
    */

	-- obtener modelos activos

	SELECT CONCAT( 
        '[', 
        GROUP_CONCAT(
            JSON_OBJECT(
                'codigo', 		  codigo, 
                'promocion_base', settings->>'$.promocion_base', 
                'arranque', 	  settings->>'$.fecha_arranque',
                'primer_compra',  f_fecha_primercompra( _usuario, codigo )
            )
        ),
        ']' 
    ) 
    INTO _modelos 
	FROM t_modelos 
	WHERE estatus_codigo = '201-ACTIVO' 
    AND settings->>'$.efectivo' = 'true';

    -- Almacenamos en variable la cantidad de empresas donde el socio esta o ha estado activo

    select 
        cast( sum( length( json_unquote( fecha ) ) ) / 10 as unsigned ),
        max( json_unquote( fecha ) ) as ultima  
    into 
        _activos,
        _ultima
    from json_table( _modelos, '$[*]' COLUMNS ( 
        fecha JSON PATH '$.primer_compra') 
    ) _json; 
   
    -- Obtiene los datos esenciales del socio para calcular los estatus en cada unidad de negocio

	SELECT 
		u.rol_codigos, 
		u.historial,
		IFNULL( u.redes->>'$.verificado', 0 ),
		u.data->>'$.estatus.modelos',
		CAST( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( u.historial, '$.registro' ) ), 1, 10 ) AS DATE)
 	INTO _roles, _historial, _verificacion, _actuales, _registro
 	FROM t_usuarios u
 	WHERE u.id = _usuario;
    
    -- Ciclo de modelos de negocio

	WHILE _k < JSON_LENGTH( _modelos ) DO

		-- inicializamos
	
        -- obtener parametros de modelo

        SET _modelo      = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].codigo' ) ) );
        SET _promociones = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].promocion_base' ) ) );
        SET _arranque    = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].arranque' ) ) );

	    -- dias de gracia para socios nuevos
	    -- Si es socio nuevo, tiene 30 días para activarse en cualquier empresa
	    -- Si ya está activo en alguna, tiene 6 meses para activarse en alguna otra
	    -- Y reiniciar el contador a 60 días
	    -- de lo contrario será dado de baja en las empresas donde no esté activo
	    -- Podrá darse de alta en el futuro pero iniciando su red desde CERO
	
	    -- Si ya está activo en alguna empresa, la fecha de inicio de conteo de días de gracia
	    -- se reinicia a la fecha de activación
	    
	    IF _activos > 0 THEN

		    -- Si la fecha de fecha de arranque de una empresa es mayor a la ultima activación
		    -- La fecha de arranque pasa a ser la fecha de activación
	
	    	IF _ultima < _arranque THEN
	    		set _ultima = _arranque; 
	    	END IF;
	    	
	    	-- Calculamos fecha de compresión definitiva en empresas donde se termina tiempo de gracia
	    
	        SET _fechabaja   = DATE_FORMAT( _ultima   + INTERVAL 180 DAY, '%Y-%m-%d' );
	    ELSE
	        SET _fechabaja   = DATE_FORMAT( _registro + INTERVAL 90 DAY, '%Y-%m-%d' );
	    END IF;

		-- if _modelo = '50-INVERSION' THEN	    
		-- return json_array(_ultima, _arranque, _fechabaja, _activos );
		-- end if;

		-- este loop nunca va a iterar, es solo para hacer el jump al resto de IFs
		final: LOOP

			-- Protegemos socios sin compras
			
			if JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].primer_compra' ) ) ) = 'null' then
				SET _primera = NULL; 
			ELSE
		    	SET _primera = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].primer_compra' ) ) );
			end if;

            CASE
            WHEN JSON_CONTAINS( _roles, '"00-BLOQUEADO"', '$') THEN 
                
                -- manualmente con rol de bloqueado 
                /*******************************/
                SET _estatus = '120-BAJA';
                /*******************************/
                LEAVE final;    

            WHEN JSON_CONTAINS( _roles, '"42-PERMANENTE"', '$') THEN 
                        
                -- rol de staff
                /*******************************/
                SET _estatus = '612-STAFF-PERMANENTE';
                /*******************************/
                LEAVE final;

            WHEN _verificacion = 2024 AND _modelo = '10-NUTRICION' THEN 
                
                -- estatus para que verificación sea procesada y evitar consultas en corte parcial a socios inactivos
                /*******************************/
                SET _estatus = '410-CALIFICADO';
                /*******************************/
                LEAVE final;
                
			ELSE
			
				-- Socio normal, inicializamos estatus y continuamos con reglas de negocio
                /*******************************/
                SET _estatus = '000-DESCONOCIDO'; 
                /*******************************/

            END CASE;

            -- denominación de meses anteriores para recibir calificaciones (YYYYMM)
            
            SET _mes0 = DATE_FORMAT( NOW(), '%Y%m' );
            SET _mes1 = DATE_FORMAT( CONCAT( YEAR( NOW() ), '-', MONTH( NOW() ), '-01') - INTERVAL 1 MONTH, '%Y%m');
            SET _mes2 = DATE_FORMAT( CONCAT( YEAR( NOW() ), '-', MONTH( NOW() ), '-01') - INTERVAL 2 MONTH, '%Y%m');

            -- Obtenemos calificaciones

            SET _calificaciones = JSON_ARRAY( 
                JSON_EXTRACT( _historial, CONCAT( '$.modelos."', _modelo, '".calificaciones."', _mes0, '"') ),
                JSON_EXTRACT( _historial, CONCAT( '$.modelos."', _modelo, '".calificaciones."', _mes1, '"') ),
                JSON_EXTRACT( _historial, CONCAT( '$.modelos."', _modelo, '".calificaciones."', _mes2, '"') )
            );
            
            case
            /*
            **************************************************************************************
             _   _ _   _ _____ ____  ___ ____ ___ ___  _   _ 
            | \ | | | | |_   _|  _ \|_ _/ ___|_ _/ _ \| \ | |
            |  \| | | | | | | | |_) || | |    | | | | |  \| |
            | |\  | |_| | | | |  _ < | | |___ | | |_| | |\  |
            |_| \_|\___/  |_| |_| \_\___\____|___\___/|_| \_|
                                
            **************************************************************************************
            */

            when _modelo = '10-NUTRICION' then      
               
                SET _i = 0;

                WHILE _i < JSON_LENGTH( _promociones ) DO
                    IF JSON_UNQUOTE( JSON_EXTRACT( _calificaciones, CONCAT( '$[', _i ,']' ) ) ) = 'null' THEN
                        SET _calificaciones = JSON_SET( _calificaciones, CONCAT( '$[', _i ,']' ), JSON_OBJECT() );
                    END IF;

                    SET _i = _i + 1;
                END WHILE;

                -- Sumamos los puntos de la promoción base
                -- o promociones, en caso de ser más de una como con reto120 o frijoles o gasolina (comodines)

                set _pts0 = 0;
                set _pts1 = 0;
                set _pts2 = 0;
                SET _i    = 0;

                WHILE _i < JSON_LENGTH( _promociones ) DO
         
                    SET _promocion = CONCAT( '$.', JSON_EXTRACT( _promociones, CONCAT( '$[', _i ,']' ) ) );

                    SET _pts0 = _pts0 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[0]', _promocion ), 0 );
                    SET _pts1 = _pts1 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[1]', _promocion ), 0 );
                    SET _pts2 = _pts2 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[2]', _promocion ), 0 );
                  
                    SET _i    = _i + 1;
                END WHILE;

                -- Si tiene compras
                IF _primera IS NOT NULL THEN

                    -- Si tiene compras en mes actual
                    IF _pts0 >= 1 THEN

                        -- Si es su primer compra/mes
                        IF DATE_FORMAT( _primera, '%Y%m' ) = _mes0 THEN
                        
                            -- nuevo registrado en los ultimos 30 días, con compras	
                            /*******************************/
                            SET _estatus = '510-NUEVO-CALIFICADO';
                            /*******************************/
                            LEAVE final;

                        ELSE
                            IF _pts1 >= 1 THEN

                                -- con compras en mes anterior y actual 
                                /*******************************/
                                SET _estatus = '520-CALIFICADO-ACTUAL';
                                /*******************************/
                                LEAVE final;

                            ELSE

                                -- con compras en mes actual sin compra en mes anterior
                                /*******************************/
                                SET _estatus = '320-NO-CALIFICADO-COMPRA';
                                /*******************************/
                                LEAVE final;

                            END IF;
                        END IF;	

                    -- Si no tiene compras en mes actual
                    ELSE
                        IF _pts1 >= 1 THEN

							-- con compras en el mes anterior, pero sin compras en mes actual
                            /*******************************/
							SET _estatus = '410-CALIFICADO';
                            /*******************************/
							LEAVE final;

                        -- Si no tiene compras en mes actual ni en mes anterior
                        ELSE

                            -- Si tiene compras en mes anterior al anterior
                            IF _pts2 >= 1 THEN
                                -- sin compras en los ultimos 2 meses
                                /*******************************/
                                SET _estatus = '310-NO-CALIFICADO';
                                /*******************************/
            
                                -- cancelar bono aniversario
                                UPDATE t_comisiones 
                                SET estatus_codigo = '118-BOLSA-POR-BAJA' 
                                WHERE usuario_id   = _usuario 
                                AND estatus_codigo = '255-PENDIENTE' 
                                AND esquema_codigo = '116-ANIVERSARIO';
                                
                                LEAVE final;
                            
                            -- Si tampoco tiene compras en el mes anterior del anterior (3 meses sin comprar)
                            ELSE
                            	-- no tiene compras en los ultimos 3 meses
                                /*******************************/
                                SET _estatus = '140-SUSPENDIDO';
                                /*******************************/
                                LEAVE final;

                            END IF;	
                        END IF;
                    END IF;
                
                -- Si nunca ha comprado
                ELSE
                    IF _fechabaja > CAST( NOW() AS DATE ) THEN
                                
                        -- registrado dentro de tiempo de gracia, aun sin compras
                        /*******************************/
                        SET _estatus = '210-NUEVO';
                        /*******************************/
                        LEAVE final;	

                    -- tiempo de gracia vencido
                    ELSE

                        -- nunca hizo compras y venció su periodo de nuevo socio en esa empresa
                        /*******************************/
                        SET _estatus = '130-NUEVO-SUSPENDIDO';
                        /*******************************/
                        LEAVE final;
                        
                    END IF;
                END IF;

                LEAVE final;  
                
            /*
            **************************************************************************************
             _____ _____ _     _____ _____ ___  _   _ ___    _    
            |_   _| ____| |   | ____|  ___/ _ \| \ | |_ _|  / \   
              | | |  _| | |   |  _| | |_ | | | |  \| || |  / _ \  
              | | | |___| |___| |___|  _|| |_| | |\  || | / ___ \ 
              |_| |_____|_____|_____|_|   \___/|_| \_|___/_/   \_\
                                                        
            **************************************************************************************
            */

            when _modelo = '20-TELEFONIA' then      

                -- hacemos el escaneo de sus recargas beneleit movil

                SELECT 
					CAST( pe.fechas->>'$.pagado' AS DATE ),
					CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE ),
					CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL 31 + pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE )
				INTO _tcompra, _tvigencia, _tbaja
				FROM t_pedidos pe
					left JOIN t_productos pr ON pr.codigo = JSON_UNQUOTE( JSON_EXTRACT( JSON_KEYS( pe.promociones->>'$.\"310-TELEFONIA\".productos' ) , '$[0]' ) )
				WHERE substring(pe.estatus_codigo,1,3) > 400 AND pe.modelo_codigo = '20-TELEFONIA' AND pe.usuario_id = _usuario
				ORDER BY CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL 31 + pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE ) DESC LIMIT 1;			

                IF _primera IS NOT NULL THEN	

					-- activo
					IF DATE_FORMAT(_tvigencia, '%Y%m%d') >= DATE_FORMAT( NOW(), '%Y%m%d') then

						-- si es primer compra
						IF DATE_FORMAT( _primera, '%Y%m%d') = DATE_FORMAT(_tcompra, '%Y%m%d') then
                            -- nuevo registrado en los ultimos 30 días, con compras	
                            /*******************************/
                            SET _estatus = '510-NUEVO-CALIFICADO';
                            /*******************************/
                            LEAVE final;

						else
                            -- con compras en mes anterior y actual 
                            /*******************************/
                            SET _estatus = '520-CALIFICADO-ACTUAL';
                            /*******************************/
                            LEAVE final;
						END if;
					else
						-- si esta en periodo de gracia
						IF DATE_FORMAT(_tbaja, '%Y%m%d') >= DATE_FORMAT( NOW(), '%Y%m%d') then
							-- con compras en el mes anterior, pero sin compras en mes actual
                            /*******************************/
                            SET _estatus = '310-NO-CALIFICADO';
                            /*******************************/
                            LEAVE final;

                        -- Si tampoco tiene compras en el mes anterior (2 meses sin comprar)
						else
                            -- no tiene compras en los ultimos 3 meses
                            /*******************************/
                            SET _estatus = '140-SUSPENDIDO';
                            /*******************************/
                            LEAVE final;
							
							-- probablemente aqui vaya el reset a su red
						END if;
					END if;

                -- Si nunca ha comprado
                ELSE
                    IF _tbaja > CAST( NOW() AS DATE ) THEN
                                
                        -- registrado dentro de tiempo de gracia, aun sin compras
                        /*******************************/
                        SET _estatus = '210-NUEVO';
                        /*******************************/
                        LEAVE final;	

                    -- tiempo de gracia vencido
                    ELSE

                        -- nunca hizo compras y venció su periodo de nuevo socio en esa empresa
                        /*******************************/
                        SET _estatus = '130-NUEVO-SUSPENDIDO';
                        /*******************************/
                        LEAVE final;
                        
                    END IF;
                END IF;

                LEAVE final;                

            /*
            **************************************************************************************
                _    _     ___ __  __ _____ _   _ _____ ___  ____  
               / \  | |   |_ _|  \/  | ____| \ | |_   _/ _ \/ ___| 
              / _ \ | |    | || |\/| |  _| |  \| | | || | | \___ \ 
             / ___ \| |___ | || |  | | |___| |\  | | || |_| |___) |
            /_/   \_\_____|___|_|  |_|_____|_| \_| |_| \___/|____/ 
                                                                                                    
            **************************************************************************************
            */

            when _modelo = '30-ALIMENTOS' then      

                SET _i = 0;

                WHILE _i < JSON_LENGTH( _promociones ) DO
                    IF JSON_UNQUOTE( JSON_EXTRACT( _calificaciones, CONCAT( '$[', _i ,']' ) ) ) = 'null' THEN
                        SET _calificaciones = JSON_SET( _calificaciones, CONCAT( '$[', _i ,']' ), JSON_OBJECT() );
                    END IF;

                    SET _i = _i + 1;
                END WHILE;

                -- Sumamos los puntos de la promoción base
                -- o promociones, en caso de ser más de una como con reto120 o frijoles o gasolina (comodines)

                set _pts0 = 0;
                set _pts1 = 0;
                set _pts2 = 0;
                SET _i    = 0;

                WHILE _i < JSON_LENGTH( _promociones ) DO
         
                    SET _promocion = CONCAT( '$.', JSON_EXTRACT( _promociones, CONCAT( '$[', _i ,']' ) ) );

                    SET _pts0 = _pts0 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[0]', _promocion ), 0 );
                    SET _pts1 = _pts1 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[1]', _promocion ), 0 );
                    SET _pts2 = _pts2 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[2]', _promocion ), 0 );
                  
                    SET _i    = _i + 1;
                END WHILE;

                -- Si tiene compras
                IF _primera IS NOT NULL THEN

                    -- Si tiene compras en mes actual
                    IF _pts0 >= 1 THEN

                        -- Si es su primer compra/mes
                        IF DATE_FORMAT( _primera, '%Y%m' ) = _mes0 THEN
                        
                            -- nuevo registrado en los ultimos 30 días, con compras	
                            /*******************************/
                            SET _estatus = '510-NUEVO-CALIFICADO';
                            /*******************************/
                            LEAVE final;

                        ELSE
                            IF _pts1 >= 1 THEN

                                -- con compras en mes anterior y actual 
                                /*******************************/
                                SET _estatus = '520-CALIFICADO-ACTUAL';
                                /*******************************/
                                LEAVE final;

                            ELSE

                                -- con compras en mes actual sin compra en mes anterior
                                /*******************************/
                                SET _estatus = '320-NO-CALIFICADO-COMPRA';
                                /*******************************/
                                LEAVE final;

                            END IF;
                        END IF;	

                    -- Si no tiene compras en mes actual
                    ELSE
                        IF _pts1 >= 1 THEN

							-- con compras en el mes anterior, pero sin compras en mes actual
                            /*******************************/
							SET _estatus = '410-CALIFICADO';
                            /*******************************/
							LEAVE final;

                        -- Si no tiene compras en mes actual ni en mes anterior
                        ELSE

                            -- Si tiene compras en mes anterior al anterior
                            IF _pts2 >= 1 THEN
                                -- sin compras en los ultimos 2 meses
                                /*******************************/
                                SET _estatus = '310-NO-CALIFICADO';
                                /*******************************/
            
                                -- cancelar bono aniversario
                                UPDATE t_comisiones 
                                SET estatus_codigo = '118-BOLSA-POR-BAJA' 
                                WHERE usuario_id   = _usuario 
                                AND estatus_codigo = '255-PENDIENTE' 
                                AND esquema_codigo = '116-ANIVERSARIO';
                                
                                LEAVE final;
                            
                            -- Si tampoco tiene compras en el mes anterior del anterior (3 meses sin comprar)
                            ELSE
                            	-- no tiene compras en los ultimos 3 meses
                                /*******************************/
                                SET _estatus = '140-SUSPENDIDO';
                                /*******************************/
                                LEAVE final;

                            END IF;	
                        END IF;
                    END IF;
                
                -- Si nunca ha comprado
                ELSE
                    IF _fechabaja > CAST( NOW() AS DATE ) THEN
                                
                        -- registrado dentro de tiempo de gracia, aun sin compras
                        /*******************************/
                        SET _estatus = '210-NUEVO';
                        /*******************************/
                        LEAVE final;	

                    -- tiempo de gracia vencido
                    ELSE

                        -- nunca hizo compras y venció su periodo de nuevo socio en esa empresa
                        /*******************************/
                        SET _estatus = '130-NUEVO-SUSPENDIDO';
                        /*******************************/
                        LEAVE final;
                        
                    END IF;
                END IF;

                LEAVE final;                

            /*
            **************************************************************************************
              ____    _    ____   ___  _     ___ _   _    _    ____  
             / ___|  / \  / ___| / _ \| |   |_ _| \ | |  / \  / ___| 
            | |  _  / _ \ \___ \| | | | |    | ||  \| | / _ \ \___ \ 
            | |_| |/ ___ \ ___) | |_| | |___ | || |\  |/ ___ \ ___) |
             \____/_/   \_\____/ \___/|_____|___|_| \_/_/   \_\____/ 
                                                                                                    
            **************************************************************************************
            */

            when _modelo = '40-GASOLINAS' then      

                SET _i = 0;

                WHILE _i < JSON_LENGTH( _promociones ) DO
                    IF JSON_UNQUOTE( JSON_EXTRACT( _calificaciones, CONCAT( '$[', _i ,']' ) ) ) = 'null' THEN
                        SET _calificaciones = JSON_SET( _calificaciones, CONCAT( '$[', _i ,']' ), JSON_OBJECT() );
                    END IF;

                    SET _i = _i + 1;
                END WHILE;

                -- Sumamos los puntos de la promoción base
                -- o promociones, en caso de ser más de una como con reto120 o frijoles o gasolina (comodines)

                set _pts0 = 0;
                set _pts1 = 0;
                SET _i    = 0;

                WHILE _i < JSON_LENGTH( _promociones ) DO
         
                    SET _promocion = CONCAT( '$.', JSON_EXTRACT( _promociones, CONCAT( '$[', _i ,']' ) ) );

                    SET _pts0 = _pts0 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[0]', _promocion ), 0 );
                    SET _pts1 = _pts1 + IFNULL( JSON_EXTRACT( _calificaciones->>'$[1]', _promocion ), 0 );
                  
                    SET _i    = _i + 1;
                END WHILE;

                -- Si tiene compras
                IF _primera IS NOT NULL THEN

                    -- Si tiene compras en mes actual
                    IF _pts0 >= 1 THEN

                        -- Si es su primer compra/mes
                        IF DATE_FORMAT( _primera, '%Y%m' ) = _mes0 THEN
                        
                            -- nuevo registrado en los ultimos 30 días, con compras	
                            /*******************************/
                            SET _estatus = '510-NUEVO-CALIFICADO';
                            /*******************************/
                            LEAVE final;

                        ELSE
                            -- con compras en mes anterior y actual 
                            /*******************************/
                            SET _estatus = '520-CALIFICADO-ACTUAL';
                            /*******************************/
                            LEAVE final;
                        END IF;	

                    -- Si no tiene compras en mes actual
                    ELSE
                        IF _pts1 >= 1 THEN

							-- con compras en el mes anterior, pero sin compras en mes actual
                            /*******************************/
                            SET _estatus = '310-NO-CALIFICADO';
                            /*******************************/
                            LEAVE final;
                            
                        -- Si tampoco tiene compras en el mes anterior (2 meses sin comprar)
                        ELSE
                            -- no tiene compras en los ultimos 3 meses
                            /*******************************/
                            SET _estatus = '140-SUSPENDIDO';
                            /*******************************/
                            LEAVE final;

                        END IF;	
                    END IF;
                
                -- Si nunca ha comprado
                ELSE
                    IF _fechabaja > CAST( NOW() AS DATE ) THEN
                                
                        -- registrado dentro de tiempo de gracia, aun sin compras
                        /*******************************/
                        SET _estatus = '210-NUEVO';
                        /*******************************/
                        LEAVE final;	

                    -- tiempo de gracia vencido
                    ELSE

                        -- nunca hizo compras y venció su periodo de nuevo socio en esa empresa
                        /*******************************/
                        SET _estatus = '130-NUEVO-SUSPENDIDO';
                        /*******************************/
                        LEAVE final;
                        
                    END IF;
                END IF;

                LEAVE final;                

            /*
            **************************************************************************************
             ___ _   ___     _______ ____  ____ ___ ___  _   _ 
            |_ _| \ | \ \   / / ____|  _ \/ ___|_ _/ _ \| \ | |
             | ||  \| |\ \ / /|  _| | |_) \___ \| | | | |  \| |
             | || |\  | \ V / | |___|  _ < ___) | | |_| | |\  |
            |___|_| \_|  \_/  |_____|_| \_\____/___\___/|_| \_|
                                                    
            **************************************************************************************
            */

            when _modelo = '50-INVERSION' then      

                -- extraer info para saber si tiene inversiones activas
                SELECT count(*) into _inversion 
                from t_inversiones
                where estatus_codigo = '625-ACTIVA'
                and usuario_id = _usuario
                and curdate() < fechas->>'$.cierre';

                -- Si tiene compras
                IF _primera IS NOT NULL THEN
                    IF _inversion THEN
                        -- con compras en mes anterior y actual 
                        /*******************************/
                        SET _estatus = '520-CALIFICADO-ACTUAL';
                        /*******************************/
                        LEAVE final;
                        
                    -- Si no tiene inversiones activas
                    ELSE
                        -- no tiene compras activas
                        /*******************************/
                        SET _estatus = '140-SUSPENDIDO';
                        /*******************************/
                        LEAVE final;

                    END IF;	
                
                -- Si nunca ha comprado
                ELSE
                    IF _fechabaja > CAST( NOW() AS DATE ) THEN
                                
                        -- registrado dentro de tiempo de gracia, aun sin compras
                        /*******************************/
                        SET _estatus = '210-NUEVO';
                        /*******************************/
                        LEAVE final;	

                    -- tiempo de gracia vencido
                    ELSE

                        -- nunca hizo compras y venció su periodo de nuevo socio en esa empresa
                        /*******************************/
                        SET _estatus = '130-NUEVO-SUSPENDIDO';
                        /*******************************/
                        LEAVE final;
                        
                    END IF;
                END IF;

                LEAVE final;                

            else 

                LEAVE final;

            end case; 

        END LOOP; 

		-- Agregamos estatus de modelo a JSON de respuesta

		SET _estatuses = JSON_SET( _estatuses, CONCAT( '$."', _modelo,'"' ), _estatus );
		
		-- Acciones de estatus inactivo

		IF SUBSTRING( _estatus, 1, 3 ) < 200 THEN
			
            -- Comprimir red de manera permanente
            -- Función f_compresion_de_red está pendiente de revisión

            -- DO f_compresion_de_red( _usuario, _modelo );
			
			-- Aplicar un reset local a modelo de negocios

			UPDATE t_usuarios u 
            SET u.historial = JSON_SET( u.historial, CONCAT( '$.modelos."', _modelo, '".reset' ), CURDATE() ) 
            WHERE u.id = _usuario;
			
            -- cancelar estrellas

      		if _modelo = '10-NUTRICION' then

      			UPDATE t_usuarios u 
                SET u.data = JSON_SET(  u.data, '$.recompensas.inicia', curdate() ) 
                WHERE u.id = _usuario;
      		
      		END if;	

        else
            -- Si está activo en alguna empresa, se cancela la baja de cuenta

			SET _baja = 0;		 	
		END IF;
                
        SET _k = _k + 1;
    END WHILE;

	-- Chequeo para eliminar cuentas que no tengan empresas activas
	-- sin iomprotar cuanto tiempo de gracia lleven
	
	IF curdate() > DATE_FORMAT( _registro + INTERVAL 90 DAY, '%Y-%m-%d' ) THEN
	    -- Buscamos estatus activos
	
		SET _k = 0;
	    SET _estatus = true;
	
		WHILE _k < JSON_LENGTH( _modelos ) DO
	
	            -- obtener parametros de modelo
	            SET _modelo  = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].codigo' ) ) );
	
	            IF SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( _estatuses, CONCAT( '$."', _modelo, '"' ) ) ), 1, 3 ) > 300 THEN
	                SET _estatus = false;
	            END IF;
	
	        SET _k = _k + 1;
	    END WHILE;
	
	    -- Eliminamos estatus nuevos cuando no hay empresas activas
	
	    IF _estatus = true THEN
	        SET _k = 0;
	        SET _baja = 1;	
	
	        WHILE _k < JSON_LENGTH( _modelos ) DO
	
	                -- obtener parametros de modelo
	                SET _modelo = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].codigo' ) ) );
	
	                IF SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( _estatuses, CONCAT( '$."', _modelo, '"' ) ) ), 1, 3 ) between 200 AND 300 THEN
	                    SET _estatuses = JSON_SET( _estatuses, CONCAT( '$."', _modelo,'"' ), '140-SUSPENDIDO' );
	                END IF;
	
	            SET _k = _k + 1;
	        END WHILE;
	    END IF;
    END IF;

    -- Antes había un ciclo de modelos para saber si hubo cambios en algun estatus
    -- ahora se compara el objeto completo

    IF _actuales != _estatuses THEN

        -- actualizar estatus en base de datos
        
        UPDATE t_usuarios u
        SET u.data = JSON_SET( u.data, '$.estatus.modelos', _estatuses, '$.updated', date_format( NOW(), '%Y%m' ) ) 
        WHERE u.id = _usuario;
    END IF;

    -- Si nungun modelo está activo, dar de baja permanente la cuenta
    -- No aplica para cuentas de staff ni calificado permanente

    -- Actualizamos estatus general del usuario

    UPDATE t_usuarios 
    SET 
		data = json_set( data, '$.estatus.migrated', curdate() ),
		estatus_codigo = IF( 
        _baja = 1 AND _usuario > 60, 
        '120-BAJA', 
        '201-ACTIVO'  
    )
    WHERE id = _usuario;

	-- Enviamos respuesta
	
    RETURN _estatuses;

END