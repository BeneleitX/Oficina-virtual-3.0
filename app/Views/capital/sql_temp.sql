BEGIN

	-- declaración de variables
	DECLARE v_promociones, v_PTS, promocion, v_upline, checks JSON;
	DECLARE beneficiados, temporal JSON DEFAULT JSON_ARRAY();
	DECLARE v_fechareparte, lunes, v_corte, v_primercompra, dia_primero, dia_ultimo DATE;
	DECLARE v_modelo, v_periodo, nuevo_periodo, estatus_final VARCHAR(25);
	DECLARE v_usuario, v_estatus, compresion, l, primercompra, v_productos, gasolinas, v_activo INT;
	DECLARE existe BOOL DEFAULT FALSE;
	DECLARE v_USDT DECIMAL(8,2);
	DECLARE m_2, m_1, m_0, tercermes, primermes MEDIUMINT;

	-- Extracción de datos de pedido, socio y upline
	SELECT
        pedido.promociones,
        modelo.codigo COLLATE utf8mb4_0900_ai_ci, 
        SUBSTRING( pedido.fechas->>'$.reparte', 1, 10 ), 
        pedido.usuario_id,
        SUBSTRING( pedido.estatus_codigo, 1, 3 ) COLLATE utf8mb4_0900_ai_ci,
        pedido.fechas->>'$.corte',
        pedido.PTS,
        pedido.data->'$.productos',
        modelo.settings->>'$.periodo',
        IFNULL( usuario.data->>'$.saldo."50-INVERSION".USDT', 0 ),
        substring( json_unquote( json_Extract( usuario.data, concat( '$.estatus.modelos."', modelo.codigo, '"' ) ) ), 1, 3 )
    INTO  
        v_promociones, v_modelo, v_fechareparte, v_usuario, v_estatus, v_corte, v_PTS, v_productos, v_periodo, v_USDT, v_activo
    FROM  
	 	 t_pedidos  pedido
    JOIN t_modelos  modelo  ON modelo.codigo COLLATE utf8mb4_0900_ai_ci = pedido.modelo_codigo COLLATE utf8mb4_0900_ai_ci
    JOIN t_usuarios usuario ON usuario.id = pedido.usuario_id
    WHERE 
        pedido.id = input_pedido;
	
	SET v_primercompra = CAST( f_fecha_primercompra( v_usuario, v_modelo ) AS DATE);

	set dia_primero = DATE_FORMAT( v_fechareparte, '%Y-%m-01');
	set dia_ultimo  = DATE_FORMAT( LAST_DAY( v_fechareparte ), '%Y-%m-%d');

	set m_2 = DATE_FORMAT(LAST_DAY( LAST_DAY( v_fechareparte ) - INTERVAL 3 MONTH ) + INTERVAL 1 DAY, '%Y%m');		
	set m_1 = DATE_FORMAT(LAST_DAY( LAST_DAY( v_fechareparte ) - INTERVAL 2 MONTH ) + INTERVAL 1 DAY, '%Y%m');		
	set m_0 = DATE_FORMAT( v_fechareparte, '%Y%m' );
		
	set tercermes = DATE_FORMAT( LAST_DAY( LAST_DAY( v_primercompra ) + INTERVAL 1 MONTH ) + INTERVAL 1 DAY, '%Y%m' );
	set primermes = DATE_FORMAT( v_primercompra, '%Y%m' );

	SET v_upline  =  f_get_upline( v_usuario, v_modelo COLLATE utf8mb4_0900_ai_ci, 1, v_fechareparte );

	-- Solo entran pedidos pagados que aun no han entrado a corte
	-- Si no esta pagado aun o ya se hizo corte, no avanzar			
	if v_estatus < 400 OR v_corte IS NOT NULL then
		RETURN beneficiados;
	END if;

	-- nos aseguramos de que el periodo existe y si no, lo creamos
	SET	lunes = DATE_SUB( v_fechareparte, INTERVAL WEEKDAY( v_fechareparte ) DAY);

	-- generamos nombre del periodo en curso, si no existe, se crea
	SET nuevo_periodo = CONCAT( 
			SUBSTRING( v_modelo,  1, 2 ), 
			SUBSTRING( v_periodo, 1, 1 ), 
			IF( v_periodo = 'SEMANAL', DATE_FORMAT( v_fechareparte, '%x' ), DATE_FORMAT( v_fechareparte, '%Y' ) ),
			IF( v_periodo = 'SEMANAL', DATE_FORMAT( v_fechareparte, '%v' ), DATE_FORMAT( v_fechareparte, '%m' ) ) 
		);
			
	INSERT IGNORE INTO t_periodos 
	VALUES ( 
		nuevo_periodo, 
		'250-EN-PROCESO', 
		v_modelo, 
		v_periodo, 
		IF( v_periodo = 'SEMANAL', lunes, dia_primero ),
		IF( v_periodo = 'SEMANAL', DATE_ADD( lunes, INTERVAL 6 DAY ), dia_ultimo ),
		JSON_OBJECT( 
			"periodo", null,
			"pedidos", 0,
			"pagos", 0,
			"socios", 0,
			"comisiones", 0,
			"isr", 0,
			"total", 0,
			"total_pedidos", 0, 
			"total_pagos", 0,
			"porcentaje_comisiones", 0,
			"proceso", JSON_OBJECT(),
			"porcentaje_pagos", 0 
		) 
	);

	-- Limpiamos registro de comisiones previo
	DELETE from t_comisiones where pedido_id = input_pedido and esquema_codigo != '520-SALDO';		
	
	-- Repasamos todos los esquemas de comisiones habilitados para ese modelo de negocio
	esquemas: BEGIN
		-- Obtenemos el puntero con los esquemas 
		DECLARE v_esquema, llave VARCHAR(20);
		DECLARE tura VARCHAR(200);
		DECLARE activos_fecha, fecha_pago DATE; 
		DECLARE v_settings, socio, v_nivel, step, llaves, ll_prods, prods, niveles, tmp JSON;
		DECLARE nivel, i, j, k, calificacion, comprastercer, activos, q, estrellas, total_estrellas, cantidad, tp INT;
		DECLARE comision, ctem, comisionable, bolsa, bolsa2, rebaja, prec, vaso DECIMAL(8,2) DEFAULT 0.00;
		DECLARE aplica, paga, done BOOL DEFAULT FALSE;
		DECLARE cur CURSOR FOR 
			SELECT codigo, settings 
			FROM t_esquemas 
			WHERE modelo_codigo = v_modelo COLLATE utf8mb4_0900_ai_ci 
			AND estatus_codigo COLLATE utf8mb4_0900_ai_ci = '201-ACTIVO' 
			AND NOW() between inicia AND termina;
		
		DECLARE continue handler for not found set done = TRUE;
			
		OPEN cur;
	
		LOOP_esquemas: loop
			
			fetch cur into v_esquema, v_settings;

			if done then
				close cur;
				leave LOOP_esquemas;
					
			END if;

			CASE
			    WHEN v_esquema = '210-TELEFONIA' THEN 
					SELECT JSON_KEYS( v_promociones, '$."310-TELEFONIA".productos' ) into prods;
					SELECT precio->>'$.reparte' INTO niveles FROM t_productos WHERE codigo = prods->>'$[0]';

			    WHEN v_esquema = '220-TELEFONIA-1ER' THEN 
					SELECT JSON_KEYS( v_promociones, '$."310-TELEFONIA".productos' ) into prods;
					SELECT JSON_ARRAY( SUM(t.qt) ) into niveles FROM t_productos s, JSON_TABLE( precio->>'$.reparte', '$[*]' COLUMNS (qt DECIMAL(8,2) PATH '$')) t wHERE codigo = prods->>'$[0]';

			    WHEN v_esquema = '124-PLUS' THEN 
					SELECT JSON_KEYS( v_promociones, '$."030-PLUS".productos' ) into prods;
					SET niveles = JSON_EXTRACT( v_settings, '$.niveles' );	

					SET j = 0;	
					WHILE j < JSON_LENGTH( niveles ) DO
						SET niveles = JSON_SET( niveles, CONCAT( '$[', j, ']' ), ( v_PTS->'$."030-PLUS"' / 3 ) * JSON_EXTRACT( niveles, CONCAT( '$[', j, ']' ) ) );
					
				    	SET j = j + 1;
					END WHILE;
					
			    WHEN v_esquema = '410-GAS' or v_esquema = '412-GAS-180' THEN 
					SELECT JSON_KEYS( v_promociones, '$."414-GASOLINA".productos' ) into prods;

					SET gasolinas = v_PTS->>'$."414-GASOLINA"' + v_PTS->>'$."415-COMODIN"';
					
					-- SELECT precio->>'$.reparte' INTO niveles FROM t_productos WHERE codigo = prods->>'$[0]';
			
					SET niveles = JSON_EXTRACT( v_settings, '$.niveles' );
			
					SET j = 0;	
					WHILE j < JSON_LENGTH( niveles ) DO
						SET niveles = JSON_SET( niveles, CONCAT( '$[', j, ']' ), gasolinas * JSON_EXTRACT( niveles, CONCAT( '$[', j, ']' ) ) );
					
				    	SET j = j + 1;
					END WHILE;

				ELSE
					SET niveles = JSON_EXTRACT( v_settings, '$.niveles' );				
		   	END case;
			
			SET existe = 1;
				
	    	-- -------------------------------------------------------------------------------------------------
	
			-- EXISTE
			  
			select JSON_KEYS( v_settings->>'$.existe.PTS' ) into llaves;
			
			SET j = 0;	
			WHILE j < JSON_LENGTH( llaves ) DO
				SET llave = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[',j,']' ) ) );
				SET tp = IFNULL( JSON_EXTRACT( v_PTS, CONCAT( '$."', llave, '"' ) ), 0 );
	
				IF JSON_EXTRACT( v_settings, CONCAT( '$.existe.PTS."', llave, '"' ) ) >	tp THEN
					set existe = 0;
					
				END IF;
			
			    SET j = j + 1;
			END WHILE;	
			
			-- Si las comisiones aplican para bonos de inicio rápido en el primer mes 
			-- se verifica la fecha de primer compra
			
			if v_settings->'$.existe.primermes' IS NOT NULL then
				if ( v_settings->'$.existe.primermes' = TRUE AND primermes < m_0 ) OR ( v_settings->'$.existe.primermes' = false AND primermes = m_0 ) then 
					set existe = 0;
				END if;
			END if;	

			-- Si las comisiones aplican para bonos de inicio rápido solo en la primer compra (telefonía)
			-- se verifica la fecha de primer compra coincida con el pedido
			  
			if v_settings->'$.existe.primercompra' IS NOT NULL then
			
				-- se obtiene la fecha de la primer compra
				
				SELECT id into primercompra FROM t_pedidos 
				WHERE usuario_id = v_usuario AND modelo_codigo = v_modelo 
				AND SUBSTRING( estatus_codigo, 1, 3 ) > 400
				ORDER BY fechas->'$.califica' asc, id asc
				LIMIT 1;
				
				-- se compara con la del pedido
				
				if ( v_settings->'$.existe.primercompra' = TRUE AND v_primercompra != v_fechareparte ) OR ( v_settings->'$.existe.primercompra' = false AND v_primercompra = v_fechareparte ) then 
					set existe = 0;
				END if;
			END if;				

			-- Si las comisiones aplican para bonos de inicio rápido en el primer trimestre (3 meses de bono)
			-- se verifica la fecha de primer compra y si está dentro del periodo

			if v_settings->'$.existe.trimestre' IS NOT NULL then	
				if ( v_settings->'$.existe.trimestre' = TRUE AND tercermes < m_0 ) OR ( v_settings->'$.existe.trimestre' = false AND tercermes >= m_0 ) then 
					set existe = 0;
				END if;
			END if;			
			
			IF v_esquema = '520-SALDO' then
				if ( v_USDT > 0 and v_activo > 300 ) THEN
					update t_usuarios set data = json_set( data, '$.saldo."50-INVERSION".USDT', 0 ) where id = v_usuario; 
				else
					set existe = 0;
				end if;
					set existe = 0;
		
			END IF;
				
	    	-- -------------------------------------------------------------------------------------------------
		
			if existe then 
					
				-- reparto por esquema
				SET nivel = 1;
				SET i     = 1;
								    		
				WHILE nivel < JSON_LENGTH( niveles ) + 1 DO
				
				    SET socio 		 = JSON_EXTRACT( v_upline ,CONCAT( '$[',i ,']' ) );
					SET comision 	 = 0.00;
					SET bolsa        = 0.00;
					SET bolsa2       = 0.00;
				    SET aplica 	     = 1;
				    SET estrellas    = 0;
				    SET paga         = 1;
					SET calificacion = IFNULL( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( socio, CONCAT( IF( v_esquema = '210-TELEFONIA' OR v_esquema = '220-TELEFONIA-1ER' or v_esquema = '410-GAS' or v_esquema = '412-GAS-180', '$.calificaciones[1]', '$.calificaciones[0]' ) ) ) ) ,1, 2), 0 );

			    	-- -------------------------------------------------------------------------------------------------

					-- APLICA
					
					-- si la calificación del socio está por arriba de la mínima requerida, se paga la comisión
					-- si está por debajo, se va a bolsa
					-- ejemplo: requiere biex (20) y el sicio es básico (11) no cobra
					-- pero si es biex (21), ejecutivo (31) o premiere (41) si cobra
			
		
					IF v_settings->>'$.aplica.calificacion' > calificacion THEN
						SET aplica = 0;
					END IF; 
											
				--	IF v_settings->>'$.aplica.nivel' < nivel THEN
					--	SET aplica = 0;
				--	END IF; 					

					IF v_settings->>'$.aplica.profundidad' IS NOT null then
						SET k = 0;		
						
						-- revisamos los 3 niveles de profundidad verificando que cumplan los mínimos
						-- si falla en alguno, cancela aplicar
						WHILE k < JSON_LENGTH( v_settings->>'$.aplica.profundidad' ) DO
							IF JSON_EXTRACT( v_settings, CONCAT( '$.aplica.profundidad[', k ,']' ) ) > JSON_EXTRACT( socio, CONCAT( '$.profundidad[', k, ']' ) ) THEN
								SET aplica = 0;
							END IF;
							SET k = k + 1;
						END WHILE;
		 
		 				-- si el socio ya existe en el array, significa que no puede ser receptor del bono
						IF JSON_CONTAINS( beneficiados, socio->>'$.id' ) THEN
							SET aplica = 0;	
						END IF;
					END IF;
	
			    	-- -------------------------------------------------------------------------------------------------

					-- PAGA
					
					IF v_settings->'$.paga.calificacion' > calificacion THEN
						SET paga = 0;
					END IF; 
-- if( !paga ) then
-- return json_array( v_settings->'$.paga.calificacion', calificacion, paga, socio, comision, nivel );
-- end if;
			    	-- -------------------------------------------------------------------------------------------------

				    			
				    set fecha_pago = v_fechareparte;
												
					-- Condición para aplicar comision
				
				    IF aplica OR socio IS NULL THEN
			
						-- STEP es el nivel actual a asignar y pagar del esquema
						
				    	SET step = JSON_EXTRACT( niveles, CONCAT( '$[ ', nivel - 1, ' ]') );

						if step then
												
							CASE
							
							    WHEN v_settings->>'$.reparto' = 'porcentaje' THEN 

							    	if v_esquema = '510-INVERSION' then
							    		SET comision = step * v_promociones->'$."510-SEMILLA".precio' / 100;
							    		
							    		-- defase de 8 semanas para pagos mayores a 10K USD
							    		if 	v_promociones->'$."510-SEMILLA".precio' > 10000 THEN
								    		set fecha_pago = v_fechareparte + INTERVAL + ( ( 7 * 7 ) +  6 - weekday( v_fechareparte ) ) DAY;
				    					end if;
				    
							    	else
								   		SET vaso = 0;
								   		
								   		IF v_promociones->'$."010-DISTRIBUIDOR".comisionable' IS NOT NULL AND v_promociones->'$."010-DISTRIBUIDOR".comisionable' != 'null' then  		
											set vaso = v_promociones->'$."010-DISTRIBUIDOR".comisionable';
										END if;
	
										-- Protección para comisionable equivocado	
										
								   		if vaso = 0 AND v_productos > 0 then
									   		SET vaso = f_rasura_comision( input_pedido, '41' );
								   		END if;
											
										SET comision = step * vaso / 100;
									end if;

							    WHEN v_settings->>'$.reparto' = 'promos' THEN 
									SET cantidad = 0;
									SET q = 0;	
									select JSON_KEYS( v_promociones->>'$."020-PROMO-50".productos' ) into prods;
									WHILE q < JSON_LENGTH( prods ) DO
										SET ll_prods = JSON_EXTRACT( prods, CONCAT( '$[', q, ']' ) );
										SET cantidad = cantidad + JSON_EXTRACT( v_promociones, CONCAT( '$."020-PROMO-50".productos.', ll_prods, '.cantidad' ) );
										SET q = q + 1;
									END WHILE;	
						    		SET comision = cantidad;
							    
							    WHEN v_settings->>'$.reparto' = 'estrellas' THEN 	
								 														
									SET comision  = f_calcula_estrellas( input_pedido );
 									SET estrellas = comision;

							    ELSE 
							    	-- conteo de productos a precio regular
							    	-- para calcular bono de aniversario
							    	
							    	if '116-ANIVERSARIO' = v_esquema COLLATE utf8mb4_0900_ai_ci then
							    
										SET cantidad = 0;
										SET q = 0;	
									
										select JSON_KEYS( v_promociones->>'$."010-DISTRIBUIDOR".productos' ) into prods;
									
										WHILE q < JSON_LENGTH( prods ) DO
											SET ll_prods = JSON_EXTRACT( prods, CONCAT( '$[', q, ']' ) );
											
											-- evitamos productos promocionales (productos que no generan puntos) 
											-- aunque despues esto debe integrarse en un array de codigos de producto que participen en la promocion
											
											IF (
												JSON_EXTRACT( v_promociones, CONCAT( '$."010-DISTRIBUIDOR".productos.', ll_prods, '.puntos' ) ) > 0 
												AND ll_prods not IN ('230-BNOX', '170-BMEL', '310-FRSH') 
											) THEN
	
												SET cantidad = cantidad + JSON_EXTRACT( v_promociones, CONCAT( '$."010-DISTRIBUIDOR".productos.', ll_prods, '.cantidad' ) );
											END IF;
											
											SET q = q + 1;
										END WHILE;	

							    		SET comision = step * cantidad;

							    	ELSE
							    		
										IF (
											calificacion > 10 
											AND socio IS not NULL 
											AND ( 
--												'220-TELEFONIA-1ER' = v_esquema COLLATE utf8mb4_0900_ai_ci OR
												'210-TELEFONIA' = v_esquema COLLATE utf8mb4_0900_ai_ci 
											)
										) THEN

										
											SET rebaja   = f_rasura_movil( nivel, step, calificacion, prods->>'$[0]', v_esquema );
											SET comision = rebaja;
											SET bolsa    = step - rebaja;

											if nivel = 4 then										
												set rebaja = socio->'$.id';
												
												
												
												-- obtener directos activos en telefonía
												/*
												SELECT JSON_EXTRACT( redes, CONCAT( '$.modelos."20-TELEFONIA".activos."', DATE_FORMAT( NOW(), '%Y-%m-%d' ), '"' ) ) 
												INTO activos 
												FROM t_usuarios 
												WHERE id = socio->'$.id'; */
																		
												if socio->>'$.activos' IS NULL or socio->>'$.activos' = 'null' then
																							
													SELECT COUNT(*) 
													INTO activos 
													FROM t_usuarios u
													WHERE u.redes->>'$.modelos."20-TELEFONIA".padre' = rebaja
													AND SUBSTRING( u.data->>'$.estatus.modelos."20-TELEFONIA"', 1, 3 ) > 300;
													-- AND SUBSTRING( f_get_calificacion( u.id, date_format( fecha_pago , "%Y-%m-%d"), '20-TELEFONIA'), 4 ) != '--'; 
													
													update t_usuarios
													SET redes = JSON_SET( 
														redes, 
														'$.modelos."20-TELEFONIA".activos', 
														JSON_OBJECT( CAST( NOW() AS DATE ), activos )
													)
													WHERE id  = socio->'$.id'; 
												else		
													SET activos = socio->'$.activos';												
												END if;
													
												if activos > 4 then
													SET activos = 4;
												END if;	
									
												SET rebaja   = ( comision * ( activos * 25 ) ) / 100;
												SET bolsa2   = comision - rebaja;
												SET comision = rebaja;
												
										   		if bolsa2 > 0 then
											    	INSERT INTO t_comisiones VALUES (NULL, '116-BOLSA-DIRECTOS', input_pedido, socio->'$.id', v_esquema, nivel, IF( nivel = i, 0, 1), bolsa2, fecha_pago, null);
											    	
										    		SET bolsa2 = 0;
										    	END if;	
										    END if;
										else
											IF (
												calificacion > 10 
												AND socio IS not NULL 
												AND '410-GAS' = v_esquema COLLATE utf8mb4_0900_ai_ci OR '412-GAS-180' = v_esquema COLLATE utf8mb4_0900_ai_ci
											) THEN
												
												SET comision = step;

											else

										SET comision = step;
											END if;
										END if;

									END if;
							END CASE;

					    	if socio IS not NULL AND comision > 0 then
								if v_esquema COLLATE utf8mb4_0900_ai_ci = '110-SINERGY' OR v_esquema COLLATE utf8mb4_0900_ai_ci = '112-SINERGY-180' THEN
									SET prec 		 = comision;
						    		SET comisionable = f_rasura_comision( input_pedido, calificacion );	    		
						    		SET ctem         = step * comisionable / 100;
						    		SET bolsa        = comision - ctem;
						    		SET comision     = ctem;
					    		END if;
							END if;
				    	END IF;

			    		if bolsa > 0 then
				    		INSERT INTO t_comisiones VALUES (NULL, '113-BOLSA-DIF-CALIFICA', input_pedido, socio->'$.id', v_esquema, nivel, IF( nivel = i, 0, 1), bolsa, fecha_pago, null);
				    	END if;
							  
						
						comisiones: begin
							DECLARE beneficiario, nivel_real INT;
							DECLARE efectivo DECIMAL(8,2);
						
							IF v_esquema = '520-SALDO' THEN
								SET beneficiario = v_usuario;
								SET nivel_real   = 0;
								SET efectivo 	 = v_USDT;
							ELSE
								set beneficiario = socio->'$.id';
								SET nivel_real   = nivel;
								SET efectivo 	 = comision;									
							END IF;

							IF comision > 0 THEN
					
								-- Estatus para puntos biex
	
					    		if paga then
						    		SET estatus_final = '255-PENDIENTE' COLLATE utf8mb4_0900_ai_ci;
						    	else
						    		SET estatus_final = '112-BOLSA' COLLATE utf8mb4_0900_ai_ci;
						    	END if;
						    
							    INSERT INTO t_comisiones VALUES (NULL, estatus_final, input_pedido, beneficiario, v_esquema, nivel_real, IF( nivel_real = 0 OR nivel_real = i OR socio IS NULL, 0, 1), efectivo, fecha_pago, null);  
							    UPDATE t_periodos set estatus_codigo = '255-PENDIENTE' where termina < now() and estatus_codigo = '250-EN-PROCESO';
													    
							    IF beneficiario IS NOT NULL THEN
							    
									select json_arrayagg(fruit) INTO beneficiados
									from (
										select fruit
										from json_table(
											json_merge_preserve( beneficiados, JSON_ARRAY( beneficiario ) ),
											'$[*]' columns (
											fruit int path '$'
											)
										) as fruits
										group by fruit -- group here!
									) a;
							    
				                --	SET beneficiados = JSON_ARRAY_APPEND( beneficiados, '$',  );
				                END IF;
							END IF;
			                
		                end comisiones;
		                
						SET nivel = nivel + 1;
					END IF;
	
				    SET i = i + 1;
				END WHILE;
				
			END if;

		END loop LOOP_esquemas;

	END esquemas;

	if en_corte then 
		SET l = 0;
		WHILE l < JSON_LENGTH( beneficiados ) DO
		
			-- select f_checks_rango( JSON_UNQUOTE( JSON_EXTRACT( beneficiados, CONCAT( '$[', l, ']' ) ) ),  v_modelo ) into checks;
		    SET l = l + 1;
		END WHILE;
	END if;

	RETURN beneficiados;
END