BEGIN
	-- variables
	DECLARE i, k, actualiza, baja INT DEFAULT 0;
	DECLARE p_0, p_1, p_2 DECIMAL(8,2) DEFAULT 0;
	DECLARE fm_0, fm_1, fm_2 VARCHAR(6);
	DECLARE dias_inicio, verificacion MEDIUMINT;
	DECLARE param_modelo, updated, validacion, nuevo_estatus VARCHAR(25);
	DECLARE registro, primera, ultima VARCHAR(10); 
	DECLARE modelo_base VARCHAR(20) DEFAULT '10-NUTRICION';
	DECLARE roles, estatus, a_0, a_1, a_2, promocion_base, modelos, historial, actuales JSON;
	DECLARE f_compra, f_vigencia, f_baja DATE;
	DECLARE ruta VARCHAR(200);

	-- obtener modelos activos
	SELECT CONCAT( '[', GROUP_CONCAT(
		JSON_OBJECT(
			'codigo', 		  codigo, 
			'promocion_base', settings->>'$.promocion_base', 
			'dias_inicio', 	  settings->>'$.dias_inicio'
		)
	), ']' ) into modelos 
	FROM t_modelos 
	WHERE /*estatus_codigo = '201-ACTIVO' AND */settings->>'$.efectivo' = 'true';
	
	SET estatus = JSON_OBJECT();
	SET baja = 1;
	
	SELECT 
		u.rol_codigos, 
		u.historial,
		IFNULL( u.redes->>'$.verificado', 0 ),
		u.data->>'$.estatus.modelos',
		CAST( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( u.historial, '$.registro' ) ), 1, 10 ) AS DATE),
		JSON_EXTRACT( u.historial, '$.validacion' ),
		u.data->>'$.updated'
 	INTO roles, historial, verificacion, actuales, registro, validacion, updated
 	FROM t_usuarios u
 	WHERE u.id = param_usuario;

	WHILE k < JSON_LENGTH( modelos ) DO

		set p_0 = 0;
		set p_1 = 0;
		set p_2 = 0;
		SET i   = 0;

		-- obtener parametros de modelo
		SET param_modelo   = JSON_UNQUOTE( JSON_EXTRACT( modelos, CONCAT( '$[', k, '].codigo' ) ) );
		SET promocion_base = JSON_UNQUOTE( JSON_EXTRACT( modelos, CONCAT( '$[', k, '].promocion_base' ) ) );
		SET dias_inicio    = JSON_UNQUOTE( JSON_EXTRACT( modelos, CONCAT( '$[', k, '].dias_inicio' ) ) );
		
		-- inicializamos
		SET nuevo_estatus = '000-DESCONOCIDO';

		-- denominación de meses anteriores para recibir calificaciones
		SET fm_0 = DATE_FORMAT( NOW(), "%Y%m" );
		SET fm_1 = DATE_FORMAT( CONCAT( YEAR( NOW() ), '-', MONTH( NOW() ), '-01') - INTERVAL 1 MONTH, '%Y%m');
		SET fm_2 = DATE_FORMAT( CONCAT( YEAR( NOW() ), '-', MONTH( NOW() ), '-01') - INTERVAL 2 MONTH, '%Y%m');
		SET a_0  = JSON_EXTRACT( historial, CONCAT( '$.modelos."', param_modelo, '".calificaciones."', fm_0, '"') );
		SET a_1  = JSON_EXTRACT( historial, CONCAT( '$.modelos."', param_modelo, '".calificaciones."', fm_1, '"') );
		SET a_2  = JSON_EXTRACT( historial, CONCAT( '$.modelos."', param_modelo, '".calificaciones."', fm_2, '"') );
	

		SET primera    = f_fecha_primercompra( param_usuario, param_modelo );	
		SET validacion = IF( validacion is null or validacion = 'null', NULL, SUBSTRING( validacion, 1, 10 ) );

		-- Sumamos los puntos de la promoción base (o promociones, en caso de ser más de una como con reto120 o frijoles)
		WHILE i < JSON_LENGTH( promocion_base ) DO
			SET p_0 = p_0 + IFNULL( JSON_EXTRACT( a_0, CONCAT( '$.', JSON_EXTRACT( promocion_base, CONCAT( '$[', i ,']' ) ) ) ), 0 );
			SET p_1 = p_1 + IFNULL( JSON_EXTRACT( a_1, CONCAT( '$.', JSON_EXTRACT( promocion_base, CONCAT( '$[', i ,']' ) ) ) ), 0 );
			SET p_2 = p_2 + IFNULL( JSON_EXTRACT( a_2, CONCAT( '$.', JSON_EXTRACT( promocion_base, CONCAT( '$[', i ,']' ) ) ) ), 0 );
		    SET i   = i + 1;
		END WHILE;

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
					
						-- nunca hizo compras y venció su periodo de nuevo (30 días)
						SET nuevo_estatus = '130-NUEVO-SUSPENDIDO';
						LEAVE final;
					END IF;
						      		
			      	
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
		ELSE	
	
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

		-- Actualizamos JSON de respuesta
		SET estatus = JSON_SET( estatus, CONCAT( '$."', param_modelo,'"' ), nuevo_estatus );
		
		-- En caso de encontrar algun estatus activo, cancelamos la baja
		IF SUBSTRING( nuevo_estatus, 1, 3 ) > 200 THEN
			SET baja = 0;
			
		else
			DO f_compresion_de_red( param_usuario, param_modelo );
			
			-- Aplicar un reset local a modelo de negocios
			UPDATE t_usuarios SET historial = JSON_SET( historial, CONCAT( '$.modelos."', param_modelo, '".reset' ), CURDATE() ) WHERE id = param_usuario;
			
      		if param_modelo = '10-NUTRICION' then
    	  		-- cancelar estrellas
      			UPDATE t_usuarios SET DATA = JSON_SET( DATA, '$.recompensas.inicia', curdate()  ) WHERE id = param_usuario;
      		
      		END if;	
			 	
		END IF;
	
	    SET k = k + 1;
	    

	END WHILE;

/*
	SET k = 0;	
	WHILE k < JSON_LENGTH( modelos ) DO
		SET param_modelo   = JSON_UNQUOTE( JSON_EXTRACT( modelos, CONCAT( '$[', k, '].codigo' ) ) );
		IF JSON_EXTRACT( actuales, CONCAT( '$."', param_modelo,'"' ) ) != JSON_EXTRACT( estatus, CONCAT( '$."', param_modelo,'"' ) ) THEN
			set actualiza = 1;
		END IF;
   	    SET k = k + 1;
	END WHILE;
*/

--	IF actualiza OR updated is null OR updated = 'null' or updated < fm_0 THEN

	    -- actualizamos tabla de usuario
		UPDATE t_usuarios SET data = JSON_SET( data, '$.estatus.modelos', estatus, '$.updated', fm_0 ) WHERE id = param_usuario;
	
		if update_niveles then
			CALL p_update_niveles( param_usuario, '10-NUTRICION', fm_0 );
		END if;
--	END IF;

	IF baja = 1 AND param_usuario > 60 THEN
	    -- Si ningun modelo está activo, damos de baja al socio / reset
		UPDATE t_usuarios SET estatus_codigo = '120-BAJA' WHERE id = param_usuario;
	
		-- compresión de red
		SET k = 0;	
		WHILE k < JSON_LENGTH( modelos ) DO
			SET param_modelo = JSON_UNQUOTE( JSON_EXTRACT( modelos, CONCAT( '$[', k, '].codigo' ) ) );
			
			SET i = f_get_padre( param_usuario, param_modelo );
			SET ruta = CONCAT( '$.modelos."', param_modelo, '".padre' );
			
			UPDATE t_usuarios u 
			SET u.redes = JSON_SET( u.redes, ruta, i ) 
			WHERE u.estatus_codigo = '201-ACTIVO' 
			and IF( JSON_UNQUOTE( JSON_EXTRACT( u.redes, ruta ) ) = 'null' OR JSON_UNQUOTE( JSON_EXTRACT( u.redes, ruta ) ) is null, 0, JSON_UNQUOTE( JSON_EXTRACT( u.redes, ruta ) ) ) = param_usuario;
			
	   	    SET k = k + 1;
		END WHILE;	
		
	else
		UPDATE t_usuarios   SET estatus_codigo = '201-ACTIVO' WHERE id = param_usuario;
	END IF;	
				      	
	-- Enviamos respuesta
	RETURN estatus;
END