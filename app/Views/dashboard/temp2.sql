-- --------------------------------------------------------
-- Host:                         208.109.233.170
-- Server version:               8.0.40 - MySQL Community Server - GPL
-- Server OS:                    Linux
-- HeidiSQL Version:             12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for procedure vpsbeneleitmx_app.p_avance_corte
DELIMITER //
CREATE PROCEDURE `p_avance_corte`(
	IN `avance` JSON
)
    DETERMINISTIC
BEGIN
	UPDATE t_variables SET valor = avance WHERE codigo = 'avance_corte';
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_cobra_estrellas
DELIMITER //
CREATE PROCEDURE `p_cobra_estrellas`(
	IN `p_socio` MEDIUMINT,
	IN `p_resta` SMALLINT
)
BEGIN
	DECLARE c_id, c_cantidad, acumulado INT DEFAULT 0;

	while acumulado < p_resta do

		SELECT id, cantidad
		INTO c_id, c_cantidad
		FROM t_comisiones 
		WHERE usuario_id = p_socio
	    AND esquema_codigo = '120-BIEX-3ER-NIVEL'
	    AND estatus_codigo = '255-PENDIENTE'
		ORDER BY fecha ASC
		LIMIT 1;
				
		update t_comisiones set estatus_codigo = '420-PAGADO' WHERE id = c_id;
    	SET acumulado = acumulado + c_cantidad;
	END while;
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_genera_pagos
DELIMITER //
CREATE PROCEDURE `p_genera_pagos`(
	IN `input_periodo` VARCHAR(20),
	IN `offset` INT,
	IN `step` INT
)
    DETERMINISTIC
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
    DECLARE d_data, p_data, jsondata, avance JSON;
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
		
		if OFFSET = 0 then
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
				'$.proceso', JSON_ARRAY( OFFSET, step, pid, cont, avance->'$.pedidos', porcentaje )
			);
			
			call p_avance_corte( avance );

	    END LOOP lop_pedidos;
	END reparto;

	-- GENERAR PAGOS
	-- *************************************************************************
	if (OFFSET + step ) >= avance->'$.total_pedidos' then

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
				    u.data->>'$.clabe',
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
						( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
					) 
					AND substring( c.estatus_codigo, 1, 3 ) > 200
					AND e.estatus_codigo = '201-ACTIVO'
					AND substring( pe.estatus_codigo, 1, 3 ) > 400
					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
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
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
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
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
				) 
				AND substring( c.estatus_codigo, 1, 3 ) > 200
				AND e.estatus_codigo = '201-ACTIVO'
				AND substring( pe.estatus_codigo, 1, 3 ) > 400
				AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
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
				
					INSERT INTO t_pagos 
					VALUES(NULL, '250-EN-PROCESO', d_modelo, d_usuario, d_clabe, d_data );

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
					SET a_isr = f_calcula_isr( a_comisiones, 2024, 'SEMANAL' );
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
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_get_paquetes
DELIMITER //
CREATE PROCEDURE `p_get_paquetes`(
	IN `socio` MEDIUMINT,
	IN `m_0` VARCHAR(6)
)
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE v_id, v_padre, v_patrocinador, v_nivel, v_updated, v_antiguedad, v_verificado INT;
	DECLARE v_estatus, v_avatar, v_nombre, v_iniciales, v_rango, v_registro, modelo VARCHAR(225);
	DECLARE v_calificaciones, resultado, v_profundidad JSON DEFAULT JSON_ARRAY();
	DECLARE cacha, tempo JSON;
	
	SET modelo = '40-GASOLINAS';
	
	WITH recursive cte (id, calificacion, nivel) AS (
	   SELECT 
		 	u1.id, 
			SUBSTRING( f_get_calificacion(u1.id, m_0, modelo ), 5, 1 ),
			0
		FROM t_usuarios AS u1 
		WHERE u1.id = socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
			SUBSTRING( f_get_calificacion( u2.id, m_0, modelo ), 5, 1 ),
	    	cte.nivel + 1
		FROM cte
		JOIN t_usuarios AS u2 ON cte.id = IF( JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo,'".padre' ) ) ) = 'null', NULL, JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo,'".padre' ) ) ) )
	    WHERE cte.nivel < 4 
		AND SUBSTRING( JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),1,3 ) > 200 
		AND SUBSTRING( f_get_calificacion(u2.id, m_0, modelo ), 5, 1 )!= '-'
	)
	SELECT * FROM cte;

END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_update_niveles
DELIMITER //
CREATE PROCEDURE `p_update_niveles`(
	IN `i_socio` INT,
	IN `i_modelo` VARCHAR(20),
	IN `mes` VARCHAR(6)
)
BEGIN

	DECLARE upline, socio, profundidad, extraccion JSON;
	DECLARE i INT DEFAULT 0;

	SELECT f_get_upline( i_socio, i_modelo, 1, CAST( NOW() AS DATE ) ) INTO upline;

	WHILE i < JSON_LENGTH( upline ) && i < 3 DO
	    SET socio = JSON_EXTRACT( upline ,CONCAT( '$[',i + 1 ,']' ) );

		do f_update_nivel( socio->'$.id', i_modelo, mes);

	    SET i = i + 1;
	END WHILE;
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_update_padre
DELIMITER //
CREATE PROCEDURE `p_update_padre`(
	IN `socio` MEDIUMINT,
	IN `modelo` VARCHAR(20)
)
    DETERMINISTIC
BEGIN
	DECLARE e MEDIUMINT;
	DECLARE origen VARCHAR(20) DEFAULT 'patrocinador';
	
	SELECT 
		SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( p.data, CONCAT('$.estatus.modelos."', modelo, '"'))),1,3)
	INTO e
	FROM t_usuarios u 
	JOIN t_usuarios p ON p.id = JSON_UNQUOTE( JSON_EXTRACT( u.redes, IF( origen = 'padre', CONCAT( '$.modelos."', modelo, '".padre' ), '$.patrocinador' ) ) )
	WHERE 
		u.id = socio;
		
	if origen = 'patrocinador' then
		UPDATE t_usuarios 
		SET redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".padre' ), redes->'$.patrocinador' ) 
		WHERE id = socio;
	END if;
	
	if e < 200 then		
		UPDATE t_usuarios 
		SET redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".padre' ), f_get_padre( socio, modelo ) ) 
		WHERE id = socio;
	END if;		
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_update_rango
DELIMITER //
CREATE PROCEDURE `p_update_rango`(
	IN `socio` INT,
	IN `modelo` VARCHAR(20)
)
BEGIN
	DECLARE inicia, termina DATE;
	DECLARE suma DECIMAL(8,2);
	DECLARE nuevo_rango, actual, llave VARCHAR(20);
	DECLARE checks, cantidades, llaves JSON;
	DECLARE j, checa INT DEFAULT 0;
	
	-- obtenemos estatus de requisitos actuales
	SET checks = f_checks_rango( socio, mes );
	

	-- ciclo entre los 3 meses regresados
	-- si el check esta en los 3 y la cantidad del mes actual supera el rango
	-- activar rango
			
				
		UPDATE t_usuarios SET 
			redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".rango' ), rango ), 
			data = JSON_SET( data, '$.rango' , rango ), 
			historial = JSON_SET( historial, CONCAT( '$.modelos."', modelo, '".ingresos."', mes, '"' ), suma )
		WHERE id = socio;

		UPDATE t_usuarios SET 
			data = JSON_ARRAY_APPEND( data, '$.splash', JSON_OBJECT( 'tipo', 'rango', 'parametros', JSON_ARRAY( rango, suma ) ) )
		WHERE id = socio;

END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_calcula_estrellas
DELIMITER //
CREATE FUNCTION `f_calcula_estrellas`(
	`input_pedido` INT
) RETURNS decimal(5,2)
    DETERMINISTIC
BEGIN

	DECLARE socio INT DEFAULT 0;
	DECLARE estrellas_generadas, estrellas_recibidas, puntos_generados DECIMAL(8,2);
	DECLARE m_0 VARCHAR(6);

	SELECT 
		p.usuario_id, 
		DATE_FORMAT( p.fechas->>'$.califica', '%Y%m' ),
		CAST( JSON_EXTRACT( u.historial, CONCAT( '$.modelos."', p.modelo_codigo , '".calificaciones."', DATE_FORMAT( p.fechas->>'$.califica', '%Y%m' ) , '"."010-DISTRIBUIDOR"' ) ) AS UNSIGNED )
	INTO 
		socio, 
		m_0,
		puntos_generados
	FROM t_pedidos p 
	JOIN t_usuarios u ON u.id = p.usuario_id
	WHERE p.id = input_pedido;

	SELECT IFNULL( SUM(c.cantidad), 0 ) into estrellas_generadas
	FROM t_comisiones c 
	JOIN t_pedidos p ON p.usuario_id = socio
	WHERE DATE_FORMAT( c.fecha, '%Y%m' ) = m_0
	AND esquema_codigo = '120-BIEX-3ER-NIVEL'
	AND SUBSTRING( p.estatus_codigo, 1, 3 ) > 400
	AND c.pedido_id = p.id;

	if puntos_generados > 6 then
		SET puntos_generados = 6;
	END if;
	
	SET puntos_generados = FLOOR( puntos_generados / 3 );
	SET estrellas_recibidas = puntos_generados - estrellas_generadas;

	RETURN IF( estrellas_recibidas > 0, estrellas_recibidas, 0 );

END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_calcula_isr
DELIMITER //
CREATE FUNCTION `f_calcula_isr`(
	`cantidad` DECIMAL(8,2),
	`anio` INT,
	`tipo` VARCHAR(10)
) RETURNS decimal(10,2)
    DETERMINISTIC
BEGIN
	DECLARE final, fijo, porcentaje, excedente, minimo DECIMAL( 10,2 );
	DECLARE entero INT;
	
	SELECT isr.fijo, isr.porcentaje, isr.minimo 
	INTO fijo, porcentaje, minimo
	FROM t_isr isr
	WHERE isr.tipo = tipo and isr.anio = anio and cantidad BETWEEN isr.minimo AND isr.maximo;
	
	SET excedente = cantidad - minimo;	
    -- RETURN CAST( ( fijo + ( excedente * porcentaje ) / 100) AS DECIMAL( 8,2 ) );
    
    SET entero = 100 * ( fijo + ( ( excedente * porcentaje ) / 100 ) );
    return entero / 100;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_checks_rango
DELIMITER //
CREATE FUNCTION `f_checks_rango`(`u` MEDIUMINT, `modelo` VARCHAR(20)) RETURNS json
    DETERMINISTIC
BEGIN
	DECLARE checks, cantidades, llaves, splash JSON;
	DECLARE j, checa, suma INT; 
	DECLARE mes VARCHAR(6);
	DECLARE pc VARCHAR(25);
	DECLARE rango_actual, rango_nuevo VARCHAR(20);
	DECLARE ingresos DECIMAL(10,2);
	
	DECLARE m_3 MEDIUMINT default DATE_FORMAT( LAST_DAY( LAST_DAY( now() ) - INTERVAL 4 MONTH ) + INTERVAL 1 DAY, '%Y%m' );
	DECLARE m_2 MEDIUMINT default DATE_FORMAT( LAST_DAY( LAST_DAY( now() ) - INTERVAL 3 MONTH ) + INTERVAL 1 DAY, '%Y%m' );
	DECLARE m_1 MEDIUMINT default DATE_FORMAT( LAST_DAY( LAST_DAY( now() ) - INTERVAL 2 MONTH ) + INTERVAL 1 DAY, '%Y%m' );
	DECLARE m_0 MEDIUMINT default DATE_FORMAT( now(), '%Y%m' );
	
	
	-- temporal para evitar que los rangos de diferentes modelos de negocio se mezclen
	SET modelo = '10-NUTRICION';
	
	select data->>'$.rango', data->>'$.splash' INTO rango_actual, splash FROM t_usuarios WHERE id = u;
	
	SELECT r.codigo, r.cantidades, f_fecha_primercompra( u, modelo )
	INTO rango_nuevo, cantidades, pc
	FROM t_rangos r
	JOIN t_usuarios u ON u.id = u
	-- LEFT JOIN t_pines p ON u.id = p.usuario_id AND p.rango_codigo = r.codigo
	WHERE r.modelo_codigo = modelo
	AND SUBSTRING( r.codigo, 1, 3 ) > SUBSTRING( rango_actual, 1, 3 )
	-- AND p.id IS null
	ORDER BY r.codigo ASC LIMIT 1;

	if rango_nuevo is not null then

		SET checks = JSON_OBJECT( 
			m_3, JSON_OBJECT( 'actual', rango_actual, 'nuevo', rango_nuevo, 'ingresos', f_get_ingresos( u, m_3, modelo ), 'calificacion', f_get_calificacion( u, m_3, modelo ) ), 
			m_2, JSON_OBJECT( 'actual', rango_actual, 'nuevo', rango_nuevo, 'ingresos', f_get_ingresos( u, m_2, modelo ), 'calificacion', f_get_calificacion( u, m_2, modelo ) ), 
			m_1, JSON_OBJECT( 'actual', rango_actual, 'nuevo', rango_nuevo, 'ingresos', f_get_ingresos( u, m_1, modelo ), 'calificacion', f_get_calificacion( u, m_1, modelo ) ),
			m_0, JSON_OBJECT( 'actual', rango_actual, 'nuevo', rango_nuevo, 'ingresos', f_get_ingresos( u, m_0, modelo ), 'calificacion', f_get_calificacion( u, m_0, modelo ) )
		);

		SELECT JSON_KEYS( checks ) into llaves;
		SET j = 0;
		SET suma = 0;
		
		WHILE j < JSON_LENGTH( llaves ) DO
			SET checa = 0;
			
			SET mes = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[',j,']' ) ) );

			if mes != m_0 then
		
				IF CAST( JSON_EXTRACT( checks, CONCAT( '$."', mes, '".ingresos' ) ) as DECIMAL(10,2) ) > CAST( cantidades->>'$[0]' as DECIMAL(10,2) ) THEN
					set checa = 1;
				END IF;

		    	SET suma = suma + checa;
			END if;		
			
			SET checks = JSON_SET( checks, CONCAT( '$."', mes, '".check' ), checa );
			SET j = j + 1;
		END WHILE;	
		
		SET ingresos = CAST( JSON_EXTRACT( checks, CONCAT( '$."', m_1, '".ingresos' ) ) AS DECIMAL(10,2) );

		if
			SUBSTRING( JSON_EXTRACT( checks, CONCAT( '$."', m_3, '".calificacion' ) ), 5, 2 ) = '6+' AND
			SUBSTRING( JSON_EXTRACT( checks, CONCAT( '$."', m_2, '".calificacion' ) ), 5, 2 ) = '6+' AND
			SUBSTRING( JSON_EXTRACT( checks, CONCAT( '$."', m_1, '".calificacion' ) ), 5, 2 ) = '6+'
		then
			set suma = 3;
			
			SET checks = JSON_SET( checks, CONCAT( '$."', m_3, '".check' ), checa );
			SET checks = JSON_SET( checks, CONCAT( '$."', m_2, '".check' ), checa );
			SET checks = JSON_SET( checks, CONCAT( '$."', m_1, '".check' ), checa );
		END if; 		
		
		UPDATE t_usuarios SET 
			data = JSON_SET( data, '$.checks', checks ), 
			historial = JSON_SET( historial, CONCAT( '$.modelos."', modelo, '".ingresos."', m_0, '"' ), ingresos )
		WHERE id = u;
		
							
		-- se agrega el check de calificación PREMIERE

		if /* SUBSTRING( JSON_EXTRACT( checks, CONCAT( '$."', m_1, '".calificacion' ) ), 5, 2 ) = '6+' AND */
			suma = 3 AND ingresos > cantidades->>'$[0]' AND JSON_CONTAINS( splash->'$[*].tipo', '"rango"' ) IS null then 
			UPDATE t_usuarios SET 
				DATA  = JSON_SET( data, '$.rango' , rango_nuevo ), 
				redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".rango' ), rango_nuevo )
			WHERE id  = u;
	
			UPDATE t_usuarios SET 
				DATA  = JSON_ARRAY_APPEND( data, '$.splash', JSON_OBJECT( 'tipo', 'rango', 'parametros', JSON_ARRAY( rango_nuevo ) ) )
			WHERE id  = u;
			
			INSERT IGNORE INTO t_pines VALUES( NULL, '225-ALCANZADO', rango_nuevo, u, CAST( NOW() AS DATE ) );
		END if;
	
	END if;
		
	RETURN checks;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_compresion_de_red
DELIMITER //
CREATE FUNCTION `f_compresion_de_red`(
	`u` MEDIUMINT,
	`m` VARCHAR(20)
) RETURNS json
    DETERMINISTIC
BEGIN
	DECLARE padre, patrocinador_estatus INT;
	DECLARE socios JSON DEFAULT JSON_ARRAY();
	
	SELECT SUBSTRING( u.estatus_codigo, 1, 3 ) INTO patrocinador_estatus
	FROM t_usuarios e 
	JOIN t_usuarios u ON u.id = JSON_UNQUOTE( JSON_EXTRACT( e.redes, CONCAT( '$.modelos."', m, '".padre' ) ) ) 
	WHERE e.id = u;
	
	if patrocinador_estatus < 200 then
	
		SET padre = f_get_padre( u, m );
				
		SELECT json_arrayagg( id ) into socios FROM t_usuarios 
		WHERE JSON_UNQUOTE( JSON_EXTRACT( redes, CONCAT( '$.modelos."', m, '".padre' ) ) ) = u
		AND SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( DATA, CONCAT( '$.estatus.modelos."', m, '"' ) ) ), 1, 3 ) > 200;

		UPDATE t_usuarios 
		SET redes = JSON_SET( redes, CONCAT( '$.modelos."', m, '".padre' ), padre ) 
		WHERE CAST( JSON_UNQUOTE( JSON_EXTRACT( redes, CONCAT( '$.modelos."', m, '".padre' ) ) ) AS UNSIGNED ) = u;
		
		if socios IS NULL then 
			SET socios = JSON_ARRAY();
		ELSE
			INSERT INTO t_compresiones VALUES( NULL, m, u, DATE_FORMAT( NOW(), '%Y-%m-%d' ), socios );
		END if;

	END if;
	
	RETURN socios;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_compulsa_valores
DELIMITER //
CREATE FUNCTION `f_compulsa_valores`(
	`periodo` VARCHAR(20)
) RETURNS json
    DETERMINISTIC
BEGIN
	DECLARE fin_valores boolean default false;
	DECLARE pagos, impuestos, valor, pila, abiertos INT;
	DECLARE total, test_a, test_b, compulsa DECIMAL(10,2) DEFAULT 0;
	DECLARE elementos JSON;
	DECLARE modelo VARCHAR(20);
	
    DECLARE m_ini, m_ter VARCHAR( 6 );
    DECLARE f_ini, f_ter VARCHAR( 10 );

	SET elementos = JSON_ARRAY();
	
	SELECT COUNT(*) into abiertos
	FROM t_periodos p1 
	JOIN t_periodos p2 ON p2.codigo = periodo
	WHERE p1.modelo_codigo = p2.modelo_codigo 
	AND p1.inicia > '2024-08-30' 
	AND p1.termina < p2.inicia 
	AND SUBSTRING( p1.estatus_codigo, 1, 3) < 400;  	
	
	-- Al automatizar los cortes semanales
	-- esta función quedará obsoleta 
	-- (se puede eliminar de p_genera_pagos, sin problema)
	
	if abiertos = 0 then
		SELECT DATE_FORMAT( inicia, '%Y%m' ), DATE_FORMAT( termina + INTERVAL 1 DAY , '%Y%m' ), modelo_codigo 
		INTO m_ini, m_ter, modelo 
		FROM t_periodos 
		WHERE codigo = periodo;
		
		SELECT COUNT(*) into pila	
		FROM ( SELECT u.id 		
		FROM t_comisiones c
			LEFT JOIN t_pedidos pe ON pe.id = c.pedido_id
			left JOIN t_periodos p ON p.codigo = periodo COLLATE utf8mb4_0900_ai_ci
			LEFT JOIN t_usuarios u ON u.id = c.usuario_id
			LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo COLLATE utf8mb4_0900_ai_ci
		WHERE 
			( 
				( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2023-09-01' AND '2024-08-31' ) OR
				( e.codigo = '118-PROMOS-50' AND c.fecha BETWEEN f_ini AND f_ter ) OR 
				( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
				( e.settings->>'$.periodo' = 'SEMANAL' AND c.estatus_codigo = '255-PENDIENTE' AND c.fecha < p.inicia AND abiertos = 0 ) 
			) 
			-- c.fecha BETWEEN IF( e.codigo = '118-PROMOS-50', f_ini, p.inicia ) AND IF( e.codigo = '118-PROMOS-50', f_ter, p.termina )
			AND substring( c.estatus_codigo, 1, 3 ) > 200
			AND e.estatus_codigo = '201-ACTIVO'
			AND substring( pe.estatus_codigo, 1, 3 ) > 400
			AND e.settings->>'$.periodo' IN ('SEMANAL', IF( e.codigo = '118-PROMOS-50', 'MENSUAL', 'NO-MENSUAL' ), IF( periodo = '10S202443', 'ANUAL', 'NO-ANUAL' ) )
			and e.modelo_codigo = p.modelo_codigo COLLATE utf8mb4_0900_ai_ci
		GROUP BY u.id ) x;
			
		compulsa: BEGIN
		    DECLARE cur_valores CURSOR FOR 
				SELECT id, JSON_EXTRACT( historial, CONCAT( '$.modelos."', modelo, '".periodos."', periodo, '"' ) )
				FROM t_usuarios 
				WHERE JSON_EXTRACT( historial, CONCAT( '$.modelos."', modelo, '".periodos."', periodo, '"' ) ) IS NOT NULL;
							
			DECLARE CONTINUE HANDLER FOR NOT FOUND SET fin_valores = true;			
		
			 OPEN cur_valores;
			lop_valores : LOOP
		        FETCH FROM cur_valores INTO valor, compulsa;
		
		        IF fin_valores THEN
		            CLOSE cur_valores;
		            LEAVE lop_valores;
		        END IF;
		        
		        -- pares de validación
		        
		        SET elementos = JSON_ARRAY_APPEND( elementos, '$', JSON_OBJECT( valor, compulsa ) );
		        
		    END LOOP lop_valores;
		END compulsa;
	else
		SET elementos = JSON_ARRAY();
	END IF;
						
	return elementos;	
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_estrellas_en_pedido
DELIMITER //
CREATE FUNCTION `f_estrellas_en_pedido`(
	`input_pedido` DECIMAL(8,2)
) RETURNS int
    DETERMINISTIC
BEGIN
	DECLARE estrellas INT;

	SELECT sum(cantidad) into estrellas FROM t_comisiones WHERE esquema_codigo = '120-BIEX-3ER-NIVEL' AND pedido_id = input_pedido;
	
	RETURN estrellas;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_es_verificado
DELIMITER //
CREATE FUNCTION `f_es_verificado`(
	`u` INT
) RETURNS json
    DETERMINISTIC
BEGIN

	DECLARE puntos, punto, llaves, u_data, tempo JSON;
	DECLARE j, k, total, requerido, verificados, porcentaje INT DEFAULT 0;
	DECLARE llave, formula VARCHAR(50);
	DECLARE fn DATE;
	
	SELECT valor into puntos FROM t_variables WHERE codigo = 'puntos_verificacion';
	SELECT DATA, fechanac into u_data, fn FROM t_usuarios WHERE id = u;
	
	SET tempo = JSON_OBJECT();
	
	select JSON_KEYS( puntos ) into llaves;
	SET j = 0;	
	WHILE j < JSON_LENGTH( llaves ) DO
		SET llave = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[', j, ']' ) ) );
		SET punto = JSON_EXTRACT( puntos, CONCAT( '$."', llave, '"' ) );
		
			CASE
			    WHEN llave = "clabe" THEN 
			    	SET requerido = IF( fn <= DATE_SUB(NOW(), INTERVAL -18 YEAR), 0, 1 );
			    WHEN llave = "csf" THEN 
			    	SET requerido = IF( u_data->'$.sat.estatus', 1, 0 );
			   	ELSE  
			   		SET requerido = JSON_EXTRACT( punto, '$.requerido');
			END case;

		SET tempo = JSON_SET( tempo, CONCAT( '$."', llave, '"' ), JSON_OBJECT( "requerido", requerido, "checked", JSON_EXTRACT( u_data, CONCAT( '$.verificacion."', llave, '"' ) ) ) );

		if requerido then
			if JSON_EXTRACT( u_data, CONCAT( '$.verificacion."', llave, '"' ) ) then
				set verificados = verificados + 1;
			END if;
			
			SET total = total + 1;
		end if;
	
	    SET j = j + 1;
	END WHILE;	

	SET porcentaje = CEIL( verificados * 100 / total );
	RETURN JSON_OBJECT( "puntos", tempo, "porcentaje", porcentaje, "estatus", if( porcentaje = 100, TRUE, FALSE ), "formula", formula );
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_fecha_primercompra
DELIMITER //
CREATE FUNCTION `f_fecha_primercompra`(
	`input_socio` INT,
	`input_modelo` VARCHAR(20)
) RETURNS varchar(100) CHARSET utf8mb4
    DETERMINISTIC
BEGIN
	DECLARE promo VARCHAR(20);
	DECLARE fecha, tempo VARCHAR(10) DEFAULT NULL;
	DECLARE promos_base JSON;
	DECLARE i, en_corte INT DEFAULT 0;
	
	SELECT settings->>'$.promocion_base' into promos_base FROM t_modelos WHERE codigo = input_modelo;

		WHILE i < JSON_LENGTH( promos_base ) DO
			SET promo = JSON_UNQUOTE( JSON_EXTRACT( promos_base, CONCAT( '$[', i ,']' ) ) );

			SELECT 
			JSON_UNQUOTE( JSON_EXTRACT( historial, CONCAT( '$.modelos."', input_modelo,'".primercompra."', promo ,'"' ) ) )
			INTO tempo
			FROM t_usuarios WHERE id = input_socio;

			if LENGTH( tempo ) = 10 then
				if fecha IS NULL OR tempo < fecha then 
					SET fecha = tempo;
				END if;
			END if;

		    SET i   = i + 1;
		END WHILE;


	if fecha IS NULL or fecha = 'null' then
		SET fecha = NULL;
	else
		if DAYNAME( fecha ) IS NULL then
			SET fecha = NULL;
		else
			SET fecha = CAST( fecha AS DATE );
		END if;
	END if;
	
	if fecha IS NULL then
		SELECT CAST( fechas->>'$.califica' AS DATE ) INTO fecha 
		FROM t_pedidos
		WHERE usuario_id = input_socio 
		AND modelo_codigo = input_modelo 
		AND CAST( substring(estatus_codigo, 1, 3 ) AS UNSIGNED ) > 400
		AND JSON_EXTRACT( PTS, CONCAT( '$."', promo,'"')) >= 1
		ORDER BY fechas->>'$.califica' asc LIMIT 1;
		
		if fecha IS NOT NULL then
			UPDATE t_usuarios SET historial = JSON_SET( historial, CONCAT('$.modelos."', input_modelo, '".primercompra."', promo, '"' ), fecha ) WHERE id = input_socio;
		END if;
	END if;
	
	RETURN fecha;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_avatar
DELIMITER //
CREATE FUNCTION `f_get_avatar`(
	`socio` MEDIUMINT
) RETURNS varchar(50) CHARSET utf8mb4
    DETERMINISTIC
BEGIN
	DECLARE avatar VARCHAR(50);
	
	SELECT IF( data->>'$.avatar.activo' = "null", null, 
			JSON_UNQUOTE( JSON_EXTRACT( data, CONCAT( '$.avatar.imagenes[ ', data->>'$.avatar.activo', ' ]' ) ) ) ) INTO avatar
	FROM t_usuarios WHERE id = socio;
	
	RETURN avatar;	
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_calificacion
DELIMITER //
CREATE FUNCTION `f_get_calificacion`(
	`socio` INT,
	`mes` VARCHAR(10),
	`modelo` VARCHAR(20)
) RETURNS varchar(20) CHARSET utf8mb4
    DETERMINISTIC
BEGIN
	DECLARE PTS, PTS_primer, roles JSON;
	DECLARE p, primercompra VARCHAR( 25 );
	DECLARE respuesta, mes2 VARCHAR(25);
	DECLARE pc DATE;
	
	SET mes2 = mes;
	if LENGTH( mes2 ) > 6 then
		SET mes2 = DATE_FORMAT( mes, '%Y%m' );
	END if;

	SET pc = f_fecha_primercompra( socio, modelo );
	SET p = DATE_FORMAT( LAST_DAY( CONCAT( SUBSTRING(mes2, 1,4), '-', SUBSTRING( mes2, 5,2 ), '-', '-01' ) ) + INTERVAL 1 DAY, '%Y%m' );

	SELECT 
		JSON_EXTRACT( historial, CONCAT('$.modelos."', modelo, '".calificaciones."', mes2 , '"' ) ),
		JSON_EXTRACT( historial, CONCAT('$.modelos."', modelo, '".calificaciones."', IF(
			pc is null, p, 	DATE_FORMAT( pc, '%Y%m' ) ), '"' ) ),
		IF( pc IS not NULL, DATE_FORMAT( pc, '%Y%m' ), NULL ),
		rol_codigos
	INTO PTS, PTS_primer, primercompra, roles
	FROM t_usuarios
	WHERE id = socio;

	IF JSON_CONTAINS( roles, '"00-BLOQUEADO"', '$') THEN
		SELECT settings->>'$.calificacion_base' INTO respuesta from t_modelos WHERE codigo = modelo COLLATE utf8mb4_0900_ai_ci ;
		RETURN respuesta; 
	END IF;

	-- Calificaciones máximas en todos los modelos de negocio para socios con rol de PERMANENTE
	IF JSON_CONTAINS( roles, '"42-PERMANENTE"', '$') THEN
		CASE
		    WHEN modelo = '10-NUTRICION' THEN 
				RETURN '41-6+';
			WHEN modelo = '20-TELEFONIA' THEN 
				RETURN '50-PRE';
			WHEN modelo = '30-ALIMENTOS' THEN 	
				RETURN '13-OK';
			WHEN modelo = '40-GASOLINAS' THEN 	
				RETURN '54-G5';
		end case;
	END IF;

	if primercompra IS not NULL then

		CASE
		    WHEN modelo = '10-NUTRICION' THEN 		    
				if primercompra >= p then
					SET PTS = PTS_primer;
				END if;

				IF PTS->>'$."010-DISTRIBUIDOR"' >= 6 AND PTS->>'$."030-PLUS"' > 2 THEN
					RETURN "41-6+";
				END IF;
						
				IF PTS->>'$."010-DISTRIBUIDOR"' >= 3 THEN
						
					IF PTS->>'$."030-PLUS"' IS NOT NULL AND PTS->>'$."030-PLUS"' != "null" AND PTS->>'$."030-PLUS"' > 2 THEN
						RETURN "31-3+";
					END IF;

					RETURN "21-03";
				END IF;
			
				IF PTS->>'$."010-DISTRIBUIDOR"' >= 1 THEN
					RETURN "11-01";
				END IF;

		    WHEN modelo = '20-TELEFONIA' THEN 				
				if LENGTH( mes ) = 6 then
					SET mes = CAST( NOW() AS DATE );
				END if;
				
				SELECT ca.codigo INTO respuesta
				FROM t_pedidos pe
				left JOIN t_productos pr 
					ON pr.codigo = JSON_UNQUOTE( JSON_EXTRACT( JSON_KEYS( pe.promociones->>'$.\"310-TELEFONIA\".productos' ) , '$[0]' ) )
				left JOIN t_calificaciones ca 
					ON SUBSTRING( ca.codigo, 4, 3 ) = SUBSTRING( pr.codigo, 5, 3 )
				WHERE pe.estatus_codigo = '420-PAGADO' AND pe.modelo_codigo = '20-TELEFONIA' AND pe.usuario_id = socio
				AND mes BETWEEN CAST( pe.fechas->>'$.pagado' AS DATE ) 
				AND CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE )
				ORDER BY pr.data->'$.puntos.\"310-TELEFONIA\"' DESC 
				LIMIT 1;
	
				if respuesta IS NOT NULL then
					RETURN respuesta;
				END if;

		    WHEN modelo = '30-ALIMENTOS' THEN 			
				if primercompra = p then
					SET PTS = PTS_primer;
				END if;

				IF PTS->'$."011-DISTRIBUIDOR"' >= 1 OR PTS->'$."021-INICIAL"' >= 1 THEN
					RETURN "13-OK";
				END IF;

		    WHEN modelo = '40-GASOLINAS' THEN 			
				if primercompra = p then
					SET PTS = PTS_primer;
				END if;

				CASE
                  	WHEN IFNULL( PTS->'$."414-GASOLINA"', 0 ) + IFNULL( PTS->'$."415-COMODIN"', 0 ) >= 5 THEN RETURN '54-G5';
					WHEN IFNULL( PTS->'$."414-GASOLINA"', 0 ) + IFNULL( PTS->'$."415-COMODIN"', 0 ) >= 4 THEN RETURN '44-G4';
					WHEN IFNULL( PTS->'$."414-GASOLINA"', 0 ) + IFNULL( PTS->'$."415-COMODIN"', 0 ) >= 3 THEN RETURN '34-G3';
					WHEN IFNULL( PTS->'$."414-GASOLINA"', 0 ) + IFNULL( PTS->'$."415-COMODIN"', 0 ) >= 2 THEN RETURN '24-G2';
					WHEN IFNULL( PTS->'$."414-GASOLINA"', 0 ) + IFNULL( PTS->'$."415-COMODIN"', 0 ) >= 1 THEN RETURN '14-G1';
				    ELSE RETURN "04---";
				END case;
				
		END CASE;

	END if;

	SELECT settings->>'$.calificacion_base' INTO respuesta from t_modelos WHERE codigo = modelo COLLATE utf8mb4_0900_ai_ci ;
	RETURN respuesta;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_downline
DELIMITER //
CREATE FUNCTION `f_get_downline`(
	`socio` MEDIUMINT,
	`modelo` VARCHAR(20),
	`niveles` INT
) RETURNS json
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE v_id, v_padre, v_patrocinador, v_nivel, v_updated, v_antiguedad, v_verificado INT;
	DECLARE v_estatus, v_avatar, v_nombre, v_iniciales, v_rango, v_registro VARCHAR(225);
	DECLARE v_calificaciones, resultado, v_profundidad JSON DEFAULT JSON_ARRAY();
	DECLARE cacha, tempo JSON;
	
	DECLARE m_1 MEDIUMINT default DATE_FORMAT(LAST_DAY( LAST_DAY(NOW()) - INTERVAL 2 MONTH ) + INTERVAL 1 DAY, '%Y%m');
	DECLARE m_0 MEDIUMINT default DATE_FORMAT( NOW(), '%Y%m' );

	DECLARE team_cursor CURSOR FOR
	WITH recursive cte (id, avatar, nombre, iniciales, estatus, registro, updated, padre, patrocinador, calificaciones, rango, nivel, profundidad, verificado) AS (
	   SELECT 
		 	u1.id, 
		 	f_get_avatar( u1.id ),
		 	UPPER( CONCAT_WS( ' ', u1.data->>'$.nombre', u1.data->>'$.apellidos[0]', u1.data->>'$.apellidos[1]' ) ),
		 	UPPER( CONCAT( SUBSTR( u1.data->>'$.nombre', 1, 1), SUBSTR( u1.data->>'$.apellidos[0]', 1, 1) ) ),
		 	JSON_UNQUOTE(JSON_EXTRACT( u1.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),
			u1.historial->>'$.registro',
		 	u1.data->>'$.estatus.updated',	
			0,
			0,
			JSON_ARRAY(f_get_calificacion(u1.id, m_1, modelo ), f_get_calificacion(u1.id, m_0, modelo )),
			JSON_UNQUOTE( JSON_EXTRACT( u1.redes, CONCAT('$.modelos."', modelo,'".rango'))),
			0,
			JSON_EXTRACT( u1.redes, CONCAT( '$.modelos."', modelo, '".profundidad."',m_0,'".calificados') ),
			TRUE -- JSON_EXTRACT( f_es_verificado( u1.id ), '$.estatus' )
		FROM t_usuarios AS u1 
		WHERE u1.id = socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
		 	f_get_avatar( u2.id ),
		 	UPPER( CONCAT_WS( ' ', u2.data->>'$.nombre', u2.data->>'$.apellidos[0]', u2.data->>'$.apellidos[1]' ) ),
		 	UPPER( CONCAT( SUBSTR( u2.data->>'$.nombre', 1, 1), SUBSTR( u2.data->>'$.apellidos[0]', 1, 1 ) ) ),
			JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),
			u2.historial->>'$.registro',
			u2.data->>'$.updated',	
			JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".padre'))),
			u2.redes->>'$.patrocinador',
			JSON_ARRAY( f_get_calificacion( u2.id, m_1, modelo ), f_get_calificacion( u2.id, m_0, modelo )),
			JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".rango'))),
	    	cte.nivel + 1,
	    	JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo, '".profundidad."',m_0,'".calificados') ),
			TRUE -- JSON_EXTRACT( f_es_verificado( u2.id ), '$.estatus' )	
		FROM cte
		JOIN t_usuarios AS u2 ON cte.id = IF( JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo,'".padre' ) ) ) = 'null', NULL, JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo,'".padre' ) ) ) )
	    WHERE cte.nivel < niveles AND SUBSTRING( JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),1,3 ) > 200 AND u2.redes->'$.verificado' is null
	)
	SELECT * FROM cte;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	OPEN team_cursor;
	teams_loop: LOOP 

		FETCH team_cursor INTO v_id, v_avatar, v_nombre, v_iniciales, v_estatus, v_registro, v_updated, v_padre, v_patrocinador, v_calificaciones, v_rango, v_nivel, v_profundidad, v_verificado;
		IF done = 1  THEN 
			LEAVE teams_loop; 
		END IF;

		-- UPDATE estatus si es de mes anterior
		IF v_updated < m_0 THEN	
		
			SET cacha = JSON_OBJECT();
		-- ["2024-06-24", 100000, null, 0, 202406]
		-- RETURN JSON_ARRAY( v_registro, v_id, cacha,v_updated , m_0 );
		
		-- 	SET cacha = f_get_estatus( v_id );
			
		--	SET v_estatus = JSON_UNQUOTE( JSON_EXTRACT( cacha , CONCAT( '$."', modelo, '"' ) ) );
		END IF;
		
		if v_rango IS NULL OR v_rango = 'null' then
			SElecT settings->>'$.rango_base' into v_rango from t_modelos WHERE codigo = modelo;
		END if;
		
		SET v_antiguedad = m_0 - CAST( CONCAT( SUBSTRING( v_registro, 1, 4), SUBSTRING( v_registro, 6, 2) ) AS UNSIGNED );
		
		SET resultado = JSON_ARRAY_APPEND( resultado, '$', JSON_OBJECT( "id", v_id, "avatar", v_avatar, "nombre", v_nombre,"iniciales", v_iniciales, "estatus", v_estatus, "registro", v_registro, "antiguedad", v_antiguedad, "padre", v_padre, "patrocinador", v_patrocinador, "calificaciones", v_calificaciones, "rango", v_rango, "nivel", v_nivel, "profundidad", v_profundidad, "verificado", v_verificado ) );
	
	END LOOP teams_loop;
	
	CLOSE team_cursor;
	
	RETURN resultado;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_estatus
DELIMITER //
CREATE FUNCTION `f_get_estatus`(
	`param_usuario` INT,
	`update_niveles` INT
) RETURNS json
    DETERMINISTIC
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
	WHERE estatus_codigo = '201-ACTIVO' AND settings->>'$.efectivo' = 'true';
	
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
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_factor_promos
DELIMITER //
CREATE FUNCTION `f_get_factor_promos`(
	`socio` MEDIUMINT,
	`mes` VARCHAR(6)
) RETURNS decimal(8,2)
BEGIN
	DECLARE bono DECIMAL(5,2); 
	declare premieres INT;
	
	SELECT JSON_EXTRACT( redes, CONCAT('$.modelos."10-NUTRICION".profundidad."', mes, '".premieres') ) into premieres FROM t_usuarios WHERE id = socio;

	CASE
	    WHEN premieres > 2 THEN 
	    	SET bono = 15;
	    WHEN premieres = 2 THEN 
	    	SET bono = 10;
	    ELSE
	    	SET bono = 2.5;
	END case;

	RETURN bono;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_ingresos
DELIMITER //
CREATE FUNCTION `f_get_ingresos`(
	`usuario` MEDIUMINT,
	`mes` VARCHAR(6),
	`modelo` VARCHAR(20)
) RETURNS decimal(10,2)
    DETERMINISTIC
BEGIN
	DECLARE resultado DECIMAL(10,2) DEFAULT 0.00;	
	declare m_0 VARCHAR(6) default DATE_FORMAT( NOW(), '%Y%m' );

	SELECT   
		SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( c.usuario_id, m_0 ), 1 ) ) INTO resultado
	FROM t_comisiones c
		LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo
	WHERE 
		SUBSTRING( c.estatus_codigo, 1, 3) > 200 
		AND e.modelo_codigo = modelo
		AND c.usuario_id = usuario
		AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = mes
		AND e.settings->>'$.periodo' IN ( 'MENSUAL', 'SEMANAL', 'ANUAL');
		
	return CAST( IFNULL( resultado, 0 ) AS DECIMAL(10,2) );
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_inventario
DELIMITER //
CREATE FUNCTION `f_get_inventario`(
	`input_almacen` VARCHAR(20)
) RETURNS json
    DETERMINISTIC
BEGIN
	DECLARE json_venta, json_transfers_origen, json_transfers_destino, json_inventario, json_balance JSON DEFAULT JSON_object();
	DECLARE llaves JSON;
	DECLARE j, t_transfer_o, t_transfer_d, t_venta INT DEFAULT 0;
	DECLARE producto VARCHAR( 20 );

	 SELECT JSON_OBJECTAGG(estatus2, producto2) into json_venta FROM (
		SELECT estatus1 AS estatus2, JSON_OBJECTAGG(producto1, cantidad) AS producto2 FROM (
			SELECT 
				SUBSTRING( p.estatus_codigo, 1, 3 ) AS estatus1,
				r.codigo AS producto1,
				SUM(j.suma) AS cantidad
			FROM t_almacenes a
			LEFT JOIN t_productos r ON r.estatus_codigo = '201-ACTIVO' AND r.modelo_codigo = a.modelo_codigo

			left join t_pedidos p ON IF( a.settings->>'$.tipo' != 'ALMACEN', SUBSTRING(p.metodoentrega_codigo, 4 ) = 'ALMACEN' AND p.data->>'$.entrega' = a.codigo, SUBSTRING(p.metodoentrega_codigo, 4 ) = 'PAQUETERIA' AND p.fechas->>'$.pagado' > '2024-08-01' ),

			-- left join t_pedidos p ON p.data->>'$.entrega' = a.codigo,
				JSON_TABLE( 
					JSON_EXTRACT( p.promociones, CONCAT( '$.*.productos."', r.codigo, '".cantidad' ) ), 
					'$[*]' COLUMNS (suma INTEGER PATH '$') 
				) j
			WHERE a.codigo = input_almacen
			GROUP BY estatus1, r.codigo
		) X GROUP BY estatus2
	) Y;
	
				
	SELECT JSON_OBJECTAGG( estatus1, producto2 ) INTO json_transfers_destino FROM (
		SELECT estatus1, JSON_OBJECTAGG( producto1, cantidad1 ) AS producto2 FROM (
			SELECT 
				SUBSTRING( estatus_codigo, 1, 3 ) AS estatus1,
			 	producto_codigo AS producto1, 
				 sum(cantidad) AS cantidad1
			FROM t_transferencias
			WHERE destino = input_almacen
			GROUP BY estatus1, producto_codigo
		) x
		GROUP BY estatus1
	) Y;

	SELECT IFNULL( JSON_OBJECTAGG( producto1, cantidad1 ), JSON_OBJECT() ) into json_transfers_origen FROM (
		SELECT 
		 	producto_codigo AS producto1, 
			 sum(cantidad) AS cantidad1
		FROM t_transferencias
		WHERE origen = input_almacen
		GROUP BY producto_codigo
	) x;
	


	SET j = 0;
	select JSON_KEYS( json_transfers_destino->'$."530"' ) into llaves;
	WHILE j < JSON_LENGTH( llaves ) DO
		SET producto     = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[', j, ']' ) ) );
		SET json_balance = JSON_SET( json_balance, CONCAT( '$."', producto, '"' ), 0 );
	    SET j = j + 1;
	END WHILE;		
	
	SET j = 0;
	select JSON_KEYS( json_transfers_destino->'$."620"' ) into llaves;	
	WHILE j < JSON_LENGTH( llaves ) DO
		SET producto     = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[', j, ']' ) ) );
		SET t_transfer_d = IFNULL( JSON_EXTRACT( json_transfers_destino, CONCAT( '$."620"."', producto, '"' ) ), 0 );
		SET t_transfer_o = IFNULL( JSON_EXTRACT( json_transfers_origen, CONCAT( '$."', producto, '"' ) ), 0 );
 		SET t_venta      = IFNULL( JSON_EXTRACT( json_venta, CONCAT( '$."420"."', producto, '"' ) ), 0 ) + IFNULL( JSON_EXTRACT( json_venta, CONCAT( '$."622"."', producto, '"' ) ), 0 );
		SET json_balance = JSON_SET( json_balance, CONCAT( '$."', producto, '"' ), t_transfer_d - t_venta - t_transfer_o );
	    SET j = j + 1;
	END WHILE;	
	
	SET json_inventario = JSON_SET( json_inventario, 
		'$.transfers_origen', json_transfers_origen,
		'$.transfers_destino', json_transfers_destino,
		'$.balance', json_balance,
		'$.venta', json_venta
	);
	
	RETURN json_inventario;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_niveles
DELIMITER //
CREATE FUNCTION `f_get_niveles`(
	`i_usuario` INT,
	`i_modelo` VARCHAR(20),
	`mes` VARCHAR(6)
) RETURNS json
    DETERMINISTIC
BEGIN
    
	DECLARE n1, n2, n3 INT DEFAULT 0;
	DECLARE c1, c2, c3 INT DEFAULT 0;
	DECLARE premieres  INT DEFAULT 0;
	
	if( mes = DATE_FORMAT( NOW(), '%Y%m' ) ) then
	
		WITH recursive cte (id, estatus, nivel) AS (
		   SELECT 
			 	u1.id, 
			 	SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( u1.data, CONCAT( '$.estatus.modelos."', i_modelo, '"' ) ) ), 1, 3 ),
				0
			FROM t_usuarios AS u1 
			WHERE u1.id = i_usuario
			
		   UNION ALL
		    
			SELECT 
			 	u2.id,
			 	SUBSTRING( JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', i_modelo, '"' ) ) ), 1, 3 ),
		    	cte.nivel + 1
			FROM cte
			JOIN t_usuarios AS u2 ON 
				200    < SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', i_modelo,'"' ) ) ), 1, 3 ) AND 
				cte.id = JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', i_modelo,'".padre' ) ) )
		    WHERE cte.nivel < 3
		)
		
		SELECT 
			SUM(IF(nivel = 1 AND estatus > 200, 1, 0)), 
			SUM(IF(nivel = 2 AND estatus > 200, 1, 0)), 
			SUM(IF(nivel = 3 AND estatus > 200, 1, 0)), 
			SUM(IF(nivel = 1 AND estatus > 315, 1, 0)), 
			SUM(IF(nivel = 2 AND estatus > 315, 1, 0)), 
			SUM(IF(nivel = 3 AND estatus > 315, 1, 0)) INTO n1, n2, n3, c1, c2, c3 FROM cte;

	END if;	

	SELECT COUNT(*) into premieres FROM (
		SELECT 
		JSON_EXTRACT( historial, CONCAT( '$.modelos."10-NUTRICION".calificaciones."', mes, '"."010-DISTRIBUIDOR"' ) ) AS biex,
		JSON_EXTRACT( historial, CONCAT( '$.modelos."10-NUTRICION".calificaciones."', mes, '"."030-PLUS"' ) ) AS plus,
		redes->>'$.modelos."10-NUTRICION".padre' AS padre,
		id,
		DATE_FORMAT( historial->>'$.modelos."10-NUTRICION".primercompra."010-DISTRIBUIDOR"', '%Y%m' ) AS primercompra
		FROM t_usuarios
		WHERE 
		
		redes->>'$.modelos."10-NUTRICION".padre' = i_usuario
		HAVING biex >= 6 AND plus >= 3 AND primercompra = mes 
	) x;
		
	RETURN JSON_OBJECT("premieres", premieres, "activos", JSON_ARRAY(n1, n2, n3), "calificados", JSON_ARRAY(c1,c2,c3));
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_padre
DELIMITER //
CREATE FUNCTION `f_get_padre`(
	`u` INT,
	`m` VARCHAR(20)
) RETURNS mediumint
    DETERMINISTIC
BEGIN
	DECLARE padre, estatus MEDIUMINT DEFAULT 0;

    WHILE estatus < 200 AND u > 0 DO
		SELECT 
			SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( p.data, CONCAT( '$.estatus.modelos."', m, '"' ) ) ), 1, 3 ),
			p.id
		INTO estatus, padre 
		FROM t_usuarios u
		LEFT JOIN t_usuarios p ON p.id = JSON_UNQUOTE( JSON_EXTRACT( u.redes, CONCAT( '$.modelos."', m, '".padre' ) ) )
		WHERE u.id = u;
		
		SET u = padre;
    END WHILE;
    
    RETURN u;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_upline
DELIMITER //
CREATE FUNCTION `f_get_upline`(
	`socio` MEDIUMINT,
	`modelo` VARCHAR(20),
	`limitado` TINYINT,
	`fecha` VARCHAR(10)
) RETURNS json
BEGIN
	DECLARE done INT DEFAULT 0;
	DECLARE v_id, v_padre, v_patrocinador, v_nivel, v_updated, v_activos, v_antiguedad INT;
	DECLARE v_estatus, v_avatar, v_nombre, v_iniciales, v_rango VARCHAR(225);
	DECLARE v_calificaciones, resultado, v_profundidad JSON DEFAULT JSON_ARRAY();
	DECLARE cacha, tempo JSON;
	
	DECLARE m_1 MEDIUMINT default DATE_FORMAT(LAST_DAY( LAST_DAY( fecha ) - INTERVAL 2 MONTH ) + INTERVAL 1 DAY, '%Y%m');
	DECLARE m_0 MEDIUMINT default DATE_FORMAT( fecha, '%Y%m' );

	DECLARE team_cursor CURSOR FOR
	WITH recursive cte (id, activos, avatar, nombre, iniciales, estatus, antiguedad, updated, padre, patrocinador, calificaciones, rango, nivel, profundidad) AS (
	   SELECT 
		 	u1.id, 
		 	JSON_EXTRACT( u1.redes, CONCAT( '$.modelos."20-TELEFONIA".activos."', CAST( NOW() AS DATE ), '"' ) ),
		 	f_get_avatar( u1.id ),
		 	UPPER( CONCAT_WS( ' ', u1.data->>'$.nombre', u1.data->>'$.apellidos[0]', u1.data->>'$.apellidos[1]' ) ),
		 	UPPER( CONCAT( SUBSTR( u1.data->>'$.nombre', 1, 1), SUBSTR( u1.data->>'$.apellidos[0]', 1, 1) ) ),
		 	JSON_UNQUOTE(JSON_EXTRACT( u1.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),
			3,	 	
		 	u1.data->>'$.estatus.updated',	
			JSON_UNQUOTE( JSON_EXTRACT( u1.redes, CONCAT('$.modelos."', modelo,'".padre'))),
			0,
			IF( limitado = 0, JSON_ARRAY(), JSON_ARRAY( 
				IF( modelo  = '20-TELEFONIA', '01---', f_get_calificacion( u1.id, m_1, modelo ) ), 
				f_get_calificacion( u1.id, IF( modelo  = '20-TELEFONIA', fecha, m_0 ), modelo ) 
			) ),
			JSON_UNQUOTE( JSON_EXTRACT( u1.data, '$.rango')),
			0,
			JSON_EXTRACT( u1.redes, CONCAT( '$.modelos."', modelo, '".profundidad."',m_0,'".calificados') )
		FROM t_usuarios AS u1 
		WHERE u1.id = socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
		 	JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."20-TELEFONIA".activos."', CAST( NOW() AS DATE ), '"' ) ),
		 	f_get_avatar( u2.id ),
		 	UPPER( CONCAT_WS( ' ', u2.data->>'$.nombre', u2.data->>'$.apellidos[0]', u2.data->>'$.apellidos[1]' ) ),
		 	UPPER( CONCAT( SUBSTR( u2.data->>'$.nombre', 1, 1), SUBSTR( u2.data->>'$.apellidos[0]', 1, 1 ) ) ),
			JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),
			FLOOR( RAND() * 4 ),
			u2.data->>'$.updated',	
			JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".padre'))),
			u2.redes->>'$.patrocinador',
			IF( limitado = 0, JSON_ARRAY(), JSON_ARRAY( 
				IF( modelo  = '20-TELEFONIA', '01---', f_get_calificacion( u2.id, m_1, modelo ) ),  
				f_get_calificacion( u2.id, IF( modelo  = '20-TELEFONIA', fecha, m_0 ), modelo )
			) ),
			JSON_UNQUOTE( JSON_EXTRACT( u2.data, '$.rango')),
	    	cte.nivel + 1,
	    	IF( JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ) = '612-STAFF-PERMANENTE', JSON_ARRAY(99,99,99), JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo, '".profundidad."',m_0,'".calificados') ) )
		FROM cte
		JOIN t_usuarios AS u2 ON u2.id = cte.padre
	    WHERE cte.padre is NOT null AND CAST( cte.padre AS UNSIGNED ) > 0 -- se agregó segunda condición (monitorear)
	)
	SELECT * FROM cte;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

	OPEN team_cursor;
	teams_loop: LOOP 
		FETCH team_cursor INTO v_id, v_activos, v_avatar, v_nombre, v_iniciales, v_estatus, v_antiguedad, v_updated, v_padre, v_patrocinador, v_calificaciones, v_rango, v_nivel, v_profundidad;
		IF done = 1  THEN 
			LEAVE teams_loop; 
		END IF;

		-- UPDATE estatus si es de mes anterior
--		IF limitado = 0 AND v_updated < m_0 THEN	
--			SET cacha = f_get_estatus( v_id, 0 );
--			SET v_estatus = JSON_UNQUOTE( JSON_EXTRACT( cacha , CONCAT( '$."', modelo, '"' ) ) );			
--		END IF;
		
		SET resultado = JSON_ARRAY_APPEND( resultado, '$', JSON_OBJECT( "id", v_id, "activos", v_activos, "avatar", v_avatar, "nombre", v_nombre,"iniciales", v_iniciales, "estatus", v_estatus, "antiguedad", v_antiguedad, "padre", v_padre, "patrocinador", v_patrocinador, "calificaciones", v_calificaciones, "rango", v_rango, "nivel", v_nivel, "profundidad", v_profundidad ) );
	
	END LOOP teams_loop;
	
	CLOSE team_cursor;
	
	RETURN resultado;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_rasura_comision
DELIMITER //
CREATE FUNCTION `f_rasura_comision`(
	`input_pedido` MEDIUMINT,
	`calificacion` INT
) RETURNS decimal(10,2)
    DETERMINISTIC
BEGIN
	-- declaración de variables
	DECLARE productos, p, llaves, puntos, temp JSON;
	DECLARE comision, PTS DECIMAL( 8,2 ) DEFAULT 0.00;
	DECLARE i, j, ps INT DEFAULT 0;
	DECLARE llave, modelo VARCHAR( 20 );


	-- calificaciones posibles
	SET puntos = JSON_OBJECT(
		'11' , 1,
		'21' , 3,
		'31' , 3,
		'41' , 99
	);
	
	SET p = JSON_object();
	
	-- Extracción de datos de pedido, socio y upline
	SELECT pedido.modelo_codigo, pedido.promociones->'$."010-DISTRIBUIDOR".productos'
	INTO   modelo, productos
    FROM   t_pedidos pedido
    JOIN t_modelos modelo ON modelo.codigo = pedido.modelo_codigo
    WHERE  pedido.id = input_pedido;
    
	SET i = 0;
	select JSON_KEYS( productos ) into llaves;

	WHILE i < JSON_LENGTH( llaves ) AND PTS < 1 DO
		SET llave = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[', i, ']' ) ) );
		SET temp  = JSON_EXTRACT( productos, CONCAT( '$."', llave, '"' ) );
		SET p     = JSON_SET( p , CONCAT('$."', temp->'$.orden', '"'), JSON_ARRAY( temp->'$.cantidad', temp->'$.puntos', temp->'$.comisionable' ) );	
	    SET i     = i + 1;
	END WHILE;
	
	SET i = 0;
	select JSON_KEYS( p ) into llaves;

	WHILE i < JSON_LENGTH( llaves ) DO
		SET llave = JSON_UNQUOTE( JSON_EXTRACT( llaves, CONCAT( '$[', i, ']' ) ) );
		SET temp  = JSON_EXTRACT( p, CONCAT( '$."', llave, '"' ) );
		SET ps    = JSON_EXTRACT( puntos , CONCAT( '$."', calificacion, '"' ) );
		SET j     = 0;
		while j < temp->'$[0]' AND PTS < ps do
			SET PTS = PTS + temp->'$[1]';
			SET comision = comision + temp->'$[2]';
	    	SET j = j + 1;
		END while;

	    SET i = i + 1;
	END WHILE;

	RETURN comision;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_rasura_movil
DELIMITER //
CREATE FUNCTION `f_rasura_movil`(
	`input_nivel` INT,
	`input_comision` DECIMAL(8,2),
	`input_calificacion` INT,
	`input_producto` VARCHAR(10 ),
	`input_esquema` VARCHAR(20)
) RETURNS decimal(8,2)
    DETERMINISTIC
BEGIN
	-- declaración de variables
	DECLARE productos, p, llaves, nuevas JSON;
	DECLARE comision, PTS DECIMAL( 8,2 ) DEFAULT 0.00;
	DECLARE paquete, j, ps INT DEFAULT 0;
	DECLARE llave, modelo VARCHAR( 20 );

	SET comision = input_comision;
	SET paquete = SUBSTRING( input_producto, 2,1);

	if input_esquema = '220-TELEFONIA-1ER' then
		SELECT JSON_ARRAY( SUM(t.qt) ) into nuevas FROM t_productos s, JSON_TABLE( precio->>'$.reparte', '$[*]' COLUMNS (qt DECIMAL(8,2) PATH '$')) t wHERE DATA->'$.dias' = 30 AND SUBSTRING( codigo, 1, 2) = CONCAT( '7', substring(input_calificacion,1,1) ) LIMIT 1;
	ELSE 
		SELECT precio->'$.reparte' into nuevas FROM t_productos WHERE DATA->'$.dias' = 30 AND SUBSTRING( codigo, 1, 2) = CONCAT( '7', substring(input_calificacion,1,1) ) LIMIT 1;
    END if; 
    

    -- si la compra es mayor a la calificación, aplicar rasurado
	if input_comision > JSON_EXTRACT( nuevas, CONCAT( '$[', input_nivel - 1, ']' ) ) then
		RETURN JSON_EXTRACT( nuevas, CONCAT( '$[', input_nivel - 1, ']' ) );
	END if;
	
	RETURN input_comision;
	
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_reparte_comisiones
DELIMITER //
//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_update_nivel
DELIMITER //
CREATE FUNCTION `f_update_nivel`(
	`socio` INT,
	`modelo` VARCHAR(20),
	`mes` INT
) RETURNS json
BEGIN
	DECLARE profundidad, extraccion JSON;

    set profundidad = f_get_niveles( socio, modelo, mes );
    
    if( mes = DATE_FORMAT( NOW(), '%Y%m' ) ) then
	    UPDATE t_usuarios SET redes = JSON_SET( redes, CONCAT('$.modelos."', modelo ,'".profundidad."', mes, '"'), profundidad ) WHERE id = socio;
	else
		select JSON_extract( redes, CONCAT('$.modelos."', modelo ,'".profundidad."', mes, '"') ) into extraccion FROM t_usuarios WHERE id = socio;
		
		if extraccion IS null then
			UPDATE t_usuarios SET redes = JSON_SET( redes, CONCAT('$.modelos."', modelo ,'".profundidad."', mes, '"'), JSON_OBJECT( "premieres", profundidad->'$.premieres') ) WHERE id = socio;
		else
			UPDATE t_usuarios SET redes = JSON_SET( redes, CONCAT('$.modelos."', modelo ,'".profundidad."', mes, '".premieres'), profundidad->'$.premieres' ) WHERE id = socio;
		END if;
	END if;
	
	RETURN profundidad;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_update_PTS
DELIMITER //
CREATE FUNCTION `f_update_PTS`(
	`usuario` INT,
	`modelo` VARCHAR(25),
	`Ym` INT
) RETURNS json
BEGIN
	DECLARE new_json, calificaciones JSON;
	
	SELECT JSON_OBJECTAGG(promocion, puntos) INTO new_json
	FROM (
		SELECT promocion.codigo AS promocion, SUM( JSON_EXTRACT( p.PTS, CONCAT('$."', promocion.codigo, '"' ))) AS puntos
		FROM t_promociones promocion 
		LEFT join t_pedidos p ON p.usuario_id = usuario
		WHERE DATE_FORMAT(p.fechas->>'$.califica', "%Y%m") = Ym AND promocion.modelo_codigo = modelo COLLATE utf8mb4_0900_ai_ci 
		AND SUBSTRING( p.estatus_codigo, 1, 3 ) > 400
		GROUP BY promocion
		HAVING puntos
	) x;
	
	SELECT JSON_EXTRACT( historial, CONCAT( '$.modelos."',modelo,'".calificaciones') ) INTO calificaciones FROM t_usuarios WHERE id = usuario;
	
	if JSON_CONTAINS( calificaciones, cast('[]' as JSON),  '$' ) then
		UPDATE t_usuarios SET historial = JSON_SET( historial, CONCAT( '$.modelos."',modelo,'".calificaciones' ), JSON_OBJECT( Ym, JSON_OBJECT() ) ) WHERE id = usuario;
	END if;
	
	UPDATE t_usuarios SET historial = JSON_SET( historial, CONCAT( '$.modelos."',modelo,'".calificaciones."',Ym,'"'), new_json ) WHERE id = usuario;
	
	RETURN new_json;
END//
DELIMITER ;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
