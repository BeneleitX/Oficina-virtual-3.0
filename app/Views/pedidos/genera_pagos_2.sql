BEGIN   

	-- GENERAR PAGOS DE PERIODO
	
	-- Esta función tardó 6 años en perfeccionarse, aquí se hace toda la magia del CORTE SEMANAL
	-- tambien aqui se incluyen los pagos mensuales y anuales ya que detecta
	-- cuando hay cambio de mes y cambio de año (año beneleit, 1 de septiembre)
	
	-- Desarrollada por Alex (scabbia@gmail.com) para BENELEIT
	
	-- NO MOVER NADA SI NO SE SABE LO QUE SE ESTA HACIENDO
	-- tengo miedo!!!!!!!!
	
	-- ************************************************************************

	-- Variables a utilizar en el entorno global
	
    DECLARE d_usuario, d_menor, d_retencion INT DEFAULT 0;
    DECLARE p_pagos, d_bolsa, pedidos, porcentaje INT DEFAULT 0;
    DECLARE anterior_abierto, abiertos, total_socios INT DEFAULT 0;
    DECLARE d_comisiones, d_isr, p_comisiones, p_isr, b_total DECIMAL(10,2) DEFAULT 0.00;
    DECLARE d_data, p_data, jsondata, avance, datos JSON;
	DECLARE a_json JSON DEFAULT f_compulsa_valores( input_periodo );
    DECLARE d_modelo, d_clabe varchar(60);
    DECLARE m_ini, m_ter VARCHAR( 6 );
    DECLARE f_ini, f_ter VARCHAR( 10 );
    
   	-- Obtenemos un 0 si todos los periodos anteriores ya han sido cerrados 
	-- o un 1 si existen periodos pendientes por cerrar
	-- En caso de obtener un 1, no consideramos en el corte parcial las comisiones pendientes de pago 
	-- que pertenezcan a periodos anteriores, ya sea porque no alcanzan el mínimo ($100) o por
	-- pedidos fuera de fecha que se hayan marcado pagados
	-- (Se ignoran los periodos anteriores al nuevo sistema para evitar confusión)
	
	SELECT COUNT(*) into abiertos
	FROM t_periodos p1 
	JOIN t_periodos p2 ON p2.codigo = input_periodo
	WHERE p1.modelo_codigo = p2.modelo_codigo 
	AND p1.inicia > '2024-08-30' 
	AND p1.termina < p2.inicia 
	AND SUBSTRING( p1.estatus_codigo, 1, 3) < 400;  

	-- Obtenemos estadísticas de avance del corte para saber si se está iniciando o ya lleva un avance
	
	SELECT valor INTO avance 
	from t_variables 
	WHERE codigo = 'avance_corte';
	
	-- Obtenemos el mes en el que el corte inicia y el mes en el que el siguiente corte inicia
	-- Si son diferentes significa que este corte incluye el último día del mes
	-- por lo tanto, se deben incluir las comisiones de pago mensual como
	-- por ejemplo, el bono de compras al 50%
	
	SELECT DATE_FORMAT( inicia, '%Y%m' ), DATE_FORMAT( termina + INTERVAL 1 DAY , '%Y%m' ), modelo_codigo 
	INTO m_ini, m_ter, d_modelo 
	FROM t_periodos 
	WHERE codigo = input_periodo;
	
	-- GENERAR COMISIONES
	-- ************************************************************************
	reparto: BEGIN
		-- Variables de entorno local para reparto
	
		DECLARE pid, cont INT;
		DECLARE socios, socios_nuevos JSON DEFAULT JSON_ARRAY();
		DECLARE fin_pedidos boolean default false;
    
    	-- Cursor para recorrer los pedidos del periodo en la iteración actual, definida
    	-- por el step y el offset
    	
	    DECLARE cur_pedidos CURSOR FOR 
			SELECT pd.id 
			FROM t_pedidos pd 
			JOIN t_periodos pe ON codigo = input_periodo
		    WHERE SUBSTRING( pd.estatus_codigo, 1, 3 ) > 400 
		    AND pe.modelo_codigo = pd.modelo_codigo COLLATE utf8mb4_0900_ai_ci
		    AND CAST( pd.fechas->>'$.reparte' AS DATE ) between pe.inicia AND pe.termina
			ORDER BY pd.id ASC 
			LIMIT offset, step;
			
	    DECLARE CONTINUE HANDLER FOR NOT FOUND SET fin_pedidos = true;

		-- Ejecutamos la misma consulta del cursor para conocer la cantidad total de pedidos
		-- Y poder generar estadística de progreso porcentual
		
		-- Esta consulta puede ir dentro del IF siguiente para no ejecutarla en todas las iteraciones,
		-- sólo en la primera, y en las siguientes extraer el dato de avance (pendiente de testeo)
		
		if offset = 0 then
			-- Si el corte va a comenzar (primer iteración) guardamos el total de pedidos
			-- aquí puede ir la consulta de arriba

			SELECT count(*) 
			INTO pedidos 
			FROM t_pedidos pd 
			JOIN t_periodos pe ON codigo = input_periodo
		    WHERE SUBSTRING( pd.estatus_codigo, 1, 3 ) > 400 
		    AND pe.modelo_codigo = pd.modelo_codigo COLLATE utf8mb4_0900_ai_ci
		    AND CAST( pd.fechas->>'$.reparte' AS DATE ) between pe.inicia AND pe.termina;

			SET avance = JSON_SET( avance, '$.total_pedidos', pedidos, '$.pedidos', 0, '$.proceso', JSON_OBJECT() );
			call p_avance_corte( avance );
			
		else
			-- Extraemos el total de socios beneficiados, esto se ejecuta cuando no es la primer
			-- iteración porque en esa por lógica es cero
			
			SET pedidos = avance->'$.total_pedidos';
			SET total_socios = avance->'$.socios';
			
			CALL p_avance_corte( avance );
		END if;

	    OPEN cur_pedidos;
	    
	    lop_pedidos : LOOP
	        FETCH FROM cur_pedidos INTO pid;
	        
        	IF fin_pedidos THEN
	            CLOSE cur_pedidos;
	            LEAVE lop_pedidos;
        	END IF;
  
        	SELECT f_reparte_comisiones( pid, 0 ) into socios_nuevos;

			IF JSON_LENGTH( socios_nuevos ) THEN
				SELECT json_arrayagg(fruit) INTO socios
				FROM (
				  	select fruit 
					FROM JSON_TABLE( json_merge_preserve( socios, socios_nuevos ),	'$[*]' 
					COLUMNS ( fruit int PATH '$' ) ) AS fruits
				  	group by fruit
				) a;
			END if;
	      
		    SET cont = avance->'$.pedidos' + 1;	
        	SET porcentaje = CEIL( cont * 100 / pedidos );
        	
			SET avance = JSON_SET( avance, 
				'$.porcentaje_comisiones', porcentaje, 
				'$.pedidos', cont, 
				'$.socios', IFNULL( JSON_LENGTH( socios ) + total_socios, 0 ),
				'$.proceso', JSON_ARRAY( offset, step, pid, cont, avance->'$.pedidos', porcentaje )
			);
			
			call p_avance_corte( avance );

	    END LOOP lop_pedidos;
	END reparto;

-- select p_genera_pagos( '40M202501', 0, 600 )
-- [0, 600, 558, 558, 100]
-- return json_array(offset, step, avance->'$.pedidos', avance->'$.total_pedidos', porcentaje );

	-- GENERAR PAGOS
	-- *************************************************************************
	if (offset + step ) >= avance->'$.total_pedidos' then

		SET avance = JSON_SET( avance, '$.porcentaje_comisiones', 100 );
		call p_avance_corte( avance );
		
		SET f_ini = CONCAT( SUBSTRING( m_ini, 1, 4), '-', SUBSTRING( m_ini, 5, 2), '-01' );
		SET f_ter = DATE_FORMAT( DATE_FORMAT( f_ini  + interval 1 MONTH, '%Y-%m-%d' ) - INTERVAL 1 DAY, '%Y-%m-%d' );
		
		pagos: BEGIN
			DECLARE fin_pagos boolean default false;
			DECLARE pagos, impuestos INT;
			DECLARE total, test_a, test_b DECIMAL(10,2) DEFAULT 0;
			
	 	    DECLARE cur_pagos CURSOR FOR 
				SELECT    
				    u.id, 
				    IF(TIMESTAMPDIFF(YEAR, u.fechanac, CURDATE()) BETWEEN 3 AND 17, u.redes->>'$.patrocinador', 0),
				    u.data->'$.sat.estatus',
				    cast( u.data->>'$.clabe' as char ),
					SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( u.id, m_ini), 1 ) ),
					IF( u.data->'$.sat.estatus' < 2, CAST( f_calcula_isr( SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( u.id, m_ini), 1 ) ), 2024, 'SEMANAL' ) AS DECIMAL( 10,2 ) ), 0)
				FROM t_comisiones c
					LEFT JOIN t_pedidos pe ON pe.id = c.pedido_id
					left JOIN t_periodos p ON p.codigo = input_periodo COLLATE utf8mb4_0900_ai_ci
					LEFT JOIN t_usuarios u ON u.id = c.usuario_id
					LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo COLLATE utf8mb4_0900_ai_ci
				WHERE 
					( 
						( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2023-09-01' AND '2024-08-31' ) OR
						( e.codigo IN ( '118-PROMOS-50', '410-GAS', '412-GAS-180' ) AND c.fecha BETWEEN f_ini AND f_ter ) OR 
						( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
			--			( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
 ( ( e.settings->>'$.periodo' = 'SEMANAL' OR e.codigo IN ( '410-GAS', '412-GAS-180' ) ) AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 )						
					) 
					AND substring( c.estatus_codigo, 1, 3 ) > 200
					AND e.estatus_codigo = '201-ACTIVO'
					AND substring( pe.estatus_codigo, 1, 3 ) > 400
					AND e.settings->>'$.periodo' IN ( p.tipo, IF( e.codigo = '118-PROMOS-50', 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
					and e.modelo_codigo = p.modelo_codigo COLLATE utf8mb4_0900_ai_ci				
				GROUP BY u.id;
				
	        DECLARE CONTINUE HANDLER FOR NOT FOUND SET fin_pagos = true;
		    
			SELECT COUNT(*) INTO pagos	
			FROM ( SELECT u.id 		
			FROM t_comisiones c
				LEFT JOIN t_pedidos pe ON pe.id = c.pedido_id
				left JOIN t_periodos p ON p.codigo = input_periodo COLLATE utf8mb4_0900_ai_ci
				LEFT JOIN t_usuarios u ON u.id = c.usuario_id
				LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo COLLATE utf8mb4_0900_ai_ci
			WHERE 
				( 
					( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2023-09-01' AND '2024-08-31' ) OR
					( e.codigo IN ( '118-PROMOS-50', '410-GAS', '412-GAS-180' ) AND c.fecha BETWEEN f_ini AND f_ter ) OR 
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
		--			( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
 ( ( e.settings->>'$.periodo' = 'SEMANAL' OR e.codigo IN ( '410-GAS', '412-GAS-180' ) ) AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 )					
					
				) 
				-- c.fecha BETWEEN IF( e.codigo = '118-PROMOS-50', f_ini, p.inicia ) AND IF( e.codigo = '118-PROMOS-50', f_ter, p.termina )
				AND substring( c.estatus_codigo, 1, 3 ) > 200
				AND e.estatus_codigo = '201-ACTIVO'
				AND substring( pe.estatus_codigo, 1, 3 ) > 400
				AND e.settings->>'$.periodo' IN ( p.tipo, IF( e.codigo = '118-PROMOS-50', 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
				and e.modelo_codigo = p.modelo_codigo COLLATE utf8mb4_0900_ai_ci
			GROUP BY u.id ) x;
			
			-- Estas 2 consultas siguientes podrían hacerse al principio para marcar las comisiones 
			-- a incluir en el corte y así evitar las dos primeras con los mismos joins, simplemente
			-- se buscan los registros marcados desde periodo_codigo
			
			-- Pendiente de testear esto en ambiente DEV
			-- Es arriesgado pero se puede, y ayudaría a agilizar el proceso
			
			-- Eliminarmos toda marca de periodo en las comisiones para marcar de nuevo con la consulta actual
			-- Esto por si hubo algunas huerfanas o movidas de fecha y evitar que se incluyan por error
			
			UPDATE t_comisiones set periodo_codigo = NULL where periodo_codigo = input_periodo;
			
			-- Hacemos el marcaje de las comisiones generadas por el corte actual
	
			UPDATE t_comisiones c 
				LEFT JOIN t_pedidos pe ON pe.id = c.pedido_id
				left JOIN t_periodos p ON p.codigo = input_periodo COLLATE utf8mb4_0900_ai_ci
    			LEFT JOIN t_usuarios u ON u.id = c.usuario_id
				LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo COLLATE utf8mb4_0900_ai_ci
			SET c.periodo_codigo = input_periodo
			WHERE 
				( 
					( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2023-09-01' AND '2024-08-31' ) OR
					( e.codigo IN ( '118-PROMOS-50', '410-GAS', '412-GAS-180' ) AND c.fecha BETWEEN f_ini AND f_ter ) OR 
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
			--		( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
 ( ( e.settings->>'$.periodo' = 'SEMANAL' OR e.codigo IN ( '410-GAS', '412-GAS-180' ) ) AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 )					
					
				) 
				AND substring( c.estatus_codigo, 1, 3 ) > 200
				AND e.estatus_codigo = '201-ACTIVO'
				AND substring( pe.estatus_codigo, 1, 3 ) > 400
				AND e.settings->>'$.periodo' IN ( p.tipo, IF( e.codigo = '118-PROMOS-50', 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
				and e.modelo_codigo = p.modelo_codigo COLLATE utf8mb4_0900_ai_ci;	
		    		    
		    -- Guardamos el total de pagos a generar para datos estadísticos de progreso del corte
				
		    SET avance = JSON_SET( avance, '$.total_pagos', pagos + JSON_LENGTH( a_json ) );
		    call p_avance_corte( avance );
		    
		    -- Limpiamos todos los pagos de ese periodo hechos en cortes parciales anteriores
	    
		    DELETE from t_pagos WHERE data->>'$.periodos.creacion' = input_periodo;
		            
		    OPEN cur_pagos;
		 
		    lop_pagos : LOOP
		        FETCH FROM cur_pagos INTO d_usuario, d_menor, d_retencion, d_clabe, d_comisiones, d_isr;
		         
		        IF fin_pagos THEN
		            CLOSE cur_pagos;
		            LEAVE lop_pagos;
		        END IF;
		        
		        SET impuestos = 0;
		        
		        if d_retencion = 100 then
			        set impuestos = 2;
		        ELSE 
					if d_retencion = 2 then
		        		set impuestos = 1;
		        	END if;
		        end if;
		        
		        SET p_comisiones = p_comisiones + d_comisiones;
		        SET p_isr = p_isr + d_isr;
		        
		        SET avance = JSON_SET( avance, '$.comisiones', p_comisiones, '$.isr', p_isr );
		        
		        if( d_menor > 0 ) then
		        	SELECT data->>'$.clabe' INTO d_clabe
		        	FROM t_usuarios WHERE id = d_menor;
		        END if;
		
	        	SET total = CAST( FLOOR( ( d_comisiones - d_isr ) * 100 ) / 100 AS DECIMAL( 10,2 ) );
      			SET b_total = b_total + total;

				-- Se llena la estructura del JSON a insertar en el nuevo pago directamente
				-- al campo DATA de la tabla t_pagos
				
				-- Aquí es donde se debe modificar y agregar un apartado de formulas 
				-- (o conservar le de cantidades, solo poniendo atención en los nombres de las llaves)
				-- para agregar no solo el ISR sino todas las que utiliza contabilidad 
				-- (Quizas ese JSON se pueda generar desde una función par ano ensuciar este código)
				-- y así ya no tener que hacer el cálculo en backend 
				-- sino que solo se elijan los campos necesarios dependiendo el tipo de facturación de cada socio
					        
	            SET d_data = JSON_OBJECT(
	                "retencion", impuestos,
	                "menor", d_menor,
	                "verificado", JSON_EXTRACT( f_es_verificado( d_usuario ), '$.estatus' ),
	                "factura", null,
	                "cantidades", JSON_OBJECT(
	                    "subtotal", d_comisiones,
	                    "isr",  d_isr,
	                    "total", total
	                    
	                    -- Aquí meter demás calculos de las formulas de excel para contabilidad
	                ),
	                "periodos", JSON_OBJECT(
	                    "creacion", input_periodo,
	                    "deposito", null
	                )	                
	            );
	            

				-- IMPORTANTE: solo pagos mayores de $100 se procesan
				-- Regla aplicada a partir de la semana 40-2024
				-- Los pagos menores a $100 se cancelan y las comisiones se conservan en estatus pendiente
				-- para agregarse en automático en el siguiente corte
				
				if total >= 100 then 
					-- Si cumple el mínimo, se procesa el pago
				
					INSERT INTO t_pagos VALUES(NULL, '250-EN-PROCESO', d_modelo, d_usuario, d_clabe, d_data );

	  	           -- if d_usuario = 129078 then
		          --  	return json_array( d_data, total );
				--	end if;
	            
	            
					SET p_pagos = p_pagos + 1;		
					
				else
					-- Si no cumple el mínimo además de evitarse la generación del pago
					-- Se revierte el marcado de las comisiones, regresandolas a estatus pendiente
					-- y quitando el identificador del periodo procesado, cambiandolo a NULL
					-- para que puedan ser sumadas en el siguiente corte
					
					UPDATE t_comisiones c 
					SET c.estatus_codigo = '255-PENDIENTE', c.periodo_codigo = NULL
					WHERE c.usuario_id = d_usuario 
					AND c.periodo_codigo = input_periodo;	
				END if;
		
				SET avance = JSON_SET( avance, '$.pagos', p_pagos, '$.total', b_total );	
	
				call p_avance_corte( avance );
		        
		    END LOOP lop_pagos;
		END pagos;
		
		if JSON_LENGTH( a_json ) then		
			-- ----------------------------------------------------
			extras: BEGIN
				DECLARE a_comisiones, a_isr, a_total DECIMAL(8,2) DEFAULT 0;
				DECLARE a_socio, j INT DEFAULT 0;
				DECLARE a_clabe VARCHAR(18);
				DECLARE a_elemento, a_key JSON;

				SET j = 0;
				while j < JSON_LENGTH( a_json ) do
				
					SET a_elemento = JSON_EXTRACT( a_json, CONCAT( '$[', j, ']' ) );
					SET a_socio = JSON_EXTRACT( JSON_KEYS( a_elemento ), '$[ 0 ]' );
					SET a_comisiones = CAST( JSON_EXTRACT( a_elemento, CONCAT( '$."', a_socio, '"' ) ) AS DECIMAL( 8, 2) );			
		--			SET a_comisiones = CAST( JSON_EXTRACT( a_elemento, CONCAT( '$[', j, '][1]' ) ) AS DECIMAL(8,2) ); 
					SET a_isr = f_calcula_isr( a_comisiones, date_format( now(), '%Y' ), 'SEMANAL' );
		--			SET a_socio = CAST( JSON_UNQUOTE( JSON_EXTRACT( a_json, CONCAT( '$[', j, '][0]' ) ) ) AS UNSIGNED );
					
					SELECT DATA->>'$.clabe' into a_clabe FROM t_usuarios WHERE id = a_socio;
			        SET a_total = CAST( FLOOR( ( a_comisiones - a_isr ) * 100 ) / 100 AS DECIMAL( 10,2 ) );
					
			        SET p_comisiones = p_comisiones + a_comisiones;
			        SET p_isr = p_isr + a_isr;
					set b_total = b_total + a_total;
			 	
					SET d_data = JSON_SET( d_data, 
						'$.retencion', 0,
						'$.cantidades', JSON_OBJECT(
							'isr', a_isr, 
							'total', a_total, 
							'subtotal', a_comisiones 
						), 
						'$.verificado', 1		
					);
			
					INSERT INTO t_pagos VALUES ( NULL, '250-EN-PROCESO', d_modelo, a_socio, a_clabe, d_data );
			       					
					SET j = j + 1;
				END while;

				SET avance = JSON_SET( avance, 
					'$.pagos', p_pagos + JSON_LENGTH( a_json ), 
					'$.total', b_total, 
					'$.comisiones', p_comisiones, 
					'$.isr', p_isr
				);	

			END extras;
		END if;
		
		-- ----------------------------------------------------
				
		-- Al terminar el loop de generar pagos, confirmamos que se completó el 100% para mostrar 
		-- en la estadística de la interfaz de usuario
		
		SET avance = JSON_SET( avance, '$.porcentaje_pagos', 100 );
	    
	    -- Guardamos la info de avance en los datos estadísticos del periodo
	    
		UPDATE t_periodos 
		SET DATA = avance 
		WHERE codigo = input_periodo COLLATE utf8mb4_0900_ai_ci;

		call p_avance_corte( avance );
	END if;

END