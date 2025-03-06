BEGIN
    -- variables globales

    DECLARE _historial, _promociones, _actuales, _roles, _estatuses, _modelos, _califiaciones JSON;
    DECLARE _baja, _k, _i  INT default 0;
    DECLARE _modelo, _estatus, _validacion, _updated, _promocion varchar( 25 );
    declare _verificacion, _diasinicio mediumint;
    DECLARE _registro, _primera date;
    DECLARE _pts0, _pts1, _pts2 DECIMAL(8,2);

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

	-- obtener modelos activos
	SELECT CONCAT( 
        '[', 
        GROUP_CONCAT(
            JSON_OBJECT(
                'codigo', 		  codigo, 
                'promocion_base', settings->>'$.promocion_base', 
                'dias_inicio', 	  settings->>'$.dias_inicio',
                'arranque', 	  settings->>'$.fecha_arranque'
            )
        ),
        ']' 
    ) 
    INTO _modelos 
	FROM t_modelos 
	WHERE estatus_codigo = '201-ACTIVO' 
    AND settings->>'$.efectivo' = 'true';

    /* 
    Genera un JSON array de objetos con la siguiente estructura:
    {
        "codigo"        : "40-GASOLINAS",
        "arranque"      : "2024-08-01",
        "dias_inicio"   : "180",
        "promocion_base": "[\"414-GASOLINA\", \"415-COMODIN\"]"
    }
    */

	SELECT 
		u.rol_codigos, 
		u.historial,
		IFNULL( u.redes->>'$.verificado', 0 ),
		u.data->>'$.estatus.modelos',
		CAST( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( u.historial, '$.registro' ) ), 1, 10 ) AS DATE),
		JSON_EXTRACT( u.historial, '$.validacion' ),
		u.data->>'$.updated'
 	INTO _roles, _historial, _verificacion, _actuales, _registro, _validacion, _updated
 	FROM t_usuarios u
 	WHERE u.id = _usuario;

    SET _validacion = IF( validacion is null or validacion = 'null', NULL, SUBSTRING( validacion, 1, 10 ) );

    -- Ciclo de modelos de negocio

	WHILE _k < JSON_LENGTH( _modelos ) DO

		-- inicializamos
		
        /*******************************/
        SET _estatus = '000-DESCONOCIDO'; 
        /*******************************/

        SET _primera = f_fecha_primercompra( _usuario, _modelo );	

		-- obtener parametros de modelo

        SET _modelo      = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].codigo' ) ) );
		SET _promociones = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', k, '].promocion_base' ) ) );
		SET _diasinicio  = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', k, '].dias_inicio' ) ) );

		set _pts0 = 0;
		set _pts1 = 0;
		set _pts2 = 0;
		SET _i    = 0;


        case  
        when _modelo = '10-NUTRICION' then      

            /**************************************************************************************/

            -- denominación de meses anteriores para recibir calificaciones
            
            SET _mes0 = DATE_FORMAT( NOW(), "%Y%m" );
            SET _mes1 = DATE_FORMAT( CONCAT( YEAR( NOW() ), '-', MONTH( NOW() ), '-01') - INTERVAL 1 MONTH, '%Y%m');
            SET _mes2 = DATE_FORMAT( CONCAT( YEAR( NOW() ), '-', MONTH( NOW() ), '-01') - INTERVAL 2 MONTH, '%Y%m');

            -- Obtenemos calificaciones

            SET _calificaciones = JSON_ARRAY( 
                JSON_EXTRACT( historial, CONCAT( '$.modelos."', _modelo, '".calificaciones."', _mes0, '"') ),
                JSON_EXTRACT( historial, CONCAT( '$.modelos."', _modelo, '".calificaciones."', _mes1, '"') ),
                JSON_EXTRACT( historial, CONCAT( '$.modelos."', _modelo, '".calificaciones."', _mes2, '"') )
            );

            -- Sumamos los puntos de la promoción base
            -- o promociones, en caso de ser más de una como con reto120 o frijoles o gasolina (comodines)

            WHILE _i < JSON_LENGTH( _promociones ) DO
                SET _promocion = CONCAT( '$.', JSON_EXTRACT( _promociones, CONCAT( '$[', _i ,']' ) ) );

                SET _pts0 = _pts0 + IFNULL( JSON_EXTRACT( _calificaciones->>'$.[0]', _promocion ), 0 );
                SET _pts1 = _pts1 + IFNULL( JSON_EXTRACT( _calificaciones->>'$.[1]', _promocion ), 0 );
                SET _pts2 = _pts2 + IFNULL( JSON_EXTRACT( _calificaciones->>'$.[2]', _promocion ), 0 );
                
                SET _i    = _i + 1;
            END WHILE;







            /**************************************************************************************/

        when _modelo = '20-TELEFONIA' then      



        when _modelo = '30-ALIMENTOS' then      



        when _modelo = '40-GASOLINAS' then      



        when _modelo = '50-INVERSION' then      



        else 
            /*******************************/
            SET _estatus = '000-DESCONOCIDO'; 
            /*******************************/

        end case; 



		-- Agregamos estatus de modelo a JSON de respuesta

		SET _estatuses = JSON_SET( _estatuses, CONCAT( '$."', _modelo,'"' ), _estatus );
		
		-- En caso de encontrar algun estatus activo, cancelamos la baja

		IF SUBSTRING( _estatus, 1, 3 ) < 200 THEN
			
            -- compresión de red
			
            DO f_compresion_de_red( _usuario, _modelo );
			
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
            -- se cancela la baja de cuenta

			SET baja = 0;		 	
		END IF;
                
        SET _k = _k + 1;
    END WHILE;

    -- Antes había un ciclo de modelos para saber si hubo cambios en algun estatus
    -- ahora se compara el objeto completo

    IF _actuales != _estatuses THEN

        -- actualizar estatus en base de datos
        
        UPDATE t_usuarios u
        SET u.data = JSON_SET( u.data, '$.estatus.modelos', _estatuses, '$.updated', date_format( '%Y%m' ) ) 
        WHERE u.id = _usuario;
    END IF;

    -- Si nungun modelo está activo, dar de baja permanente la cuenta
    -- No aplica para cuentas de staff ni calificado permanente

    -- Actualizamos estatus general del usuario

    UPDATE t_usuarios 
    SET estatus_codigo = IF( 
        baja = 1 AND _usuario > 60, 
        '120-BAJA', 
        '201-ACTIVO'  
    )
    WHERE id = _usuario;

	-- Enviamos respuesta
	
    RETURN estatuses;

END
