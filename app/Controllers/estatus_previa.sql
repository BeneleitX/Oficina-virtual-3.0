BEGIN
	-- variables
	DECLARE i, k, actualiza, baja INT DEFAULT 0;
	
	
	DECLARE dias_inicio, verificacion MEDIUMINT;
	DECLARE param_modelo, updated, validacion, nuevo_estatus VARCHAR(25);
	DECLARE registro, primera, ultima VARCHAR(10); 
	DECLARE modelo_base VARCHAR(20) DEFAULT '10-NUTRICION';
	DECLARE roles, estatus, a_0, a_1, a_2, promocion_base, modelos, historial, actuales JSON;
	DECLARE f_compra, f_vigencia, f_baja DATE;
	DECLARE ruta VARCHAR(200);

	


	WHILE k < JSON_LENGTH( modelos ) DO




	

		
		



		-- este loop nunca va a iterar, es solo para hacer el jump al resto de IFs
		final: LOOP

		-- ----------------------------------------------------------------------------------
			
		IF JSON_CONTAINS( roles, '"00-BLOQUEADO"', '$') THEN
			
			-- manualmente con rol de bloqueado 
			SET nuevo_estatus = '120-BAJA';
			DO f_compresion_de_red( param_usuario, param_modelo );
			LEAVE final;
		END IF;
			 				    	
		IF JSON_CONTAINS( roles, '"42-PERMANENTE"', '$') THEN
			
			-- rol de staff
			SET nuevo_estatus = '612-STAFF-PERMANENTE';
			LEAVE final;
		END IF;

		if verificacion = 2024 AND param_modelo = '10-NUTRICION'  then
			
			-- estatus para que verificación sea procesada y evitar consultas en corte parcial a socios inactivos
			SET nuevo_estatus = '410-CALIFICADO';
			LEAVE final;
		END if;


		IF primera IS NOT NULL THEN	

			if param_modelo = '50-INVERSION' then
				SET nuevo_estatus = '520-CALIFICADO-ACTUAL';
				LEAVE final;
			else
				if param_modelo = '20-TELEFONIA' then
						
					SELECT 
						CAST( pe.fechas->>'$.pagado' AS DATE ),
						CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE ),
						CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL 31 + pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE )
					INTO f_compra, f_vigencia, f_baja
					FROM t_pedidos pe
						left JOIN t_productos pr ON pr.codigo = JSON_UNQUOTE( JSON_EXTRACT( JSON_KEYS( pe.promociones->>'$.\"310-TELEFONIA\".productos' ) , '$[0]' ) )
					WHERE substring(pe.estatus_codigo,1,3) > 400 AND pe.modelo_codigo = '20-TELEFONIA' AND pe.usuario_id = param_usuario
					ORDER BY CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL 31 + pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE ) DESC LIMIT 1;			

					-- activo
					IF DATE_FORMAT(f_vigencia, '%Y%m%d') >= DATE_FORMAT( NOW(), '%Y%m%d') then

						-- si es primer compra
						IF DATE_FORMAT(primera, '%Y%m%d') = DATE_FORMAT(f_compra, '%Y%m%d') then
							SET nuevo_estatus = '510-NUEVO-CALIFICADO';
							LEAVE final;
						else
							SET nuevo_estatus = '520-CALIFICADO-ACTUAL';
							LEAVE final;
						END if;
					else
						-- si esta en periodo de gracia
						IF DATE_FORMAT(f_baja, '%Y%m%d') >= DATE_FORMAT( NOW(), '%Y%m%d') then
							-- no calificado
							SET nuevo_estatus = '310-NO-CALIFICADO';
							LEAVE final;
						else
							SET nuevo_estatus = '140-SUSPENDIDO';			      	
							LEAVE final;
							
							-- probablemente aqui vaya el reset a su red
						END if;
					END if;
				
				else	
					if param_modelo = '40-GASOLINAS' then
						IF p_0 >= 1 THEN
							SET nuevo_estatus = '520-CALIFICADO-ACTUAL';
							LEAVE final;
						else
						
							IF p_1 >= 1 OR p_2 >= 1 THEN
								SET nuevo_estatus = '310-NO-CALIFICADO';
								LEAVE final;			      			
							end if;
						END if;
							
					else
						IF p_0 >= 1 THEN
							IF DATE_FORMAT(primera, '%Y%m') = fm_0 THEN
								-- registrado en los ultimos 30 días, con compras	
								SET nuevo_estatus = '510-NUEVO-CALIFICADO';
								LEAVE final;
							ELSE
								IF p_1 >= 1 THEN
									-- con compras en mes actual 
									SET nuevo_estatus = '520-CALIFICADO-ACTUAL';
									LEAVE final;
								ELSE
									-- con compras en mes actual sin compra en mes anterior
									SET nuevo_estatus = '320-NO-CALIFICADO-COMPRA';
									LEAVE final;
								END IF;
							END IF;				  		      		
						END IF;
					
						IF p_1 >= 1 THEN
							-- con compras en el mes anterior, pero sin compras en mes actual
							SET nuevo_estatus = '410-CALIFICADO';
							LEAVE final;
						END IF;
				
						IF p_2 >= 1 THEN
							-- sin compras en los ultimos 2 meses
							SET nuevo_estatus = '310-NO-CALIFICADO';
		
							if param_modelo = '10-NUTRICION' then
								-- cancelar bono aniversario
								UPDATE t_comisiones SET estatus_codigo = '118-BOLSA-POR-BAJA' WHERE usuario_id = param_usuario AND estatus_codigo = '255-PENDIENTE' AND esquema_codigo = '116-ANIVERSARIO';
							END if;
							
							LEAVE final;
						END IF;	
					END if;
				
					-- no tiene compras en los ultimos 3 meses
					SET nuevo_estatus = '140-SUSPENDIDO';
					LEAVE final;
				END IF;
			END IF;
		ELSE	
		--	return json_array(registro, DATE_FORMAT( CAST( NOW() AS DATE ) - INTERVAL dias_inicio DAY, '%Y-%m-%d' ));
			IF registro > DATE_FORMAT( CAST( NOW() AS DATE ) - INTERVAL dias_inicio DAY, '%Y-%m-%d' ) THEN
						
				IF validacion THEN 
					-- registrado en los ultimos 30 días, aun sin compras pero verificado
					SET nuevo_estatus = '220-NUEVO-VERIFICADO';
					LEAVE final;
					
				ELSE
			
					-- registrado en los ultimos 30 días, aun sin compras y sin verificar
					SET nuevo_estatus = '210-NUEVO';
					LEAVE final;	
				END IF;	
				
			ELSE
			
				-- temporal
				if param_modelo = '40-GASOLINAS' then
					SET nuevo_estatus = '210-NUEVO';
					LEAVE final;					
				end if;
	    	
				-- nunca hizo compras y venció su periodo de nuevo (30 días)
				SET nuevo_estatus = '130-NUEVO-SUSPENDIDO';
				LEAVE final;
			END IF;
		END IF;
		

		LEAVE final;

		-- ----------------------------------------------------------------------------------
		
		END LOOP;





	
	    SET k = k + 1;
	    

	END WHILE;

