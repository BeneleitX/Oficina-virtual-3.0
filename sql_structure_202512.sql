-- --------------------------------------------------------
-- Host:                         208.109.233.170
-- Server version:               8.0.44 - MySQL Community Server - GPL
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

-- Dumping structure for table vpsbeneleitmx_app.t_acciones
CREATE TABLE IF NOT EXISTS `t_acciones` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_almacenes
CREATE TABLE IF NOT EXISTS `t_almacenes` (
  `codigo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `settings` json NOT NULL,
  PRIMARY KEY (`codigo`) USING BTREE,
  KEY `FK_t_almacenes_t_estatus` (`estatus_codigo`),
  KEY `FK_t_almacenes_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_t_almacenes_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_almacenes_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_apiconsumos
CREATE TABLE IF NOT EXISTS `t_apiconsumos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `apikey` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT (now()),
  `ip` varchar(50) NOT NULL,
  `peticion` json NOT NULL,
  `respuesta` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_apiconsumos_t_apikeys` (`apikey`),
  CONSTRAINT `FK_t_apiconsumos_t_apikeys` FOREIGN KEY (`apikey`) REFERENCES `t_apikeys` (`apikey`)
) ENGINE=InnoDB AUTO_INCREMENT=267806 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_apikeys
CREATE TABLE IF NOT EXISTS `t_apikeys` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '140-SUSPENDIDO',
  `apikey` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT (upper(substr(uuid(),1,8))),
  `cliente` varchar(50) NOT NULL,
  `dominio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'localhost',
  PRIMARY KEY (`id`),
  UNIQUE KEY `apikey` (`apikey`),
  KEY `FK_t_apikeys_t_estatus` (`estatus_codigo`),
  CONSTRAINT `FK_t_apikeys_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_bancos
CREATE TABLE IF NOT EXISTS `t_bancos` (
  `codigo` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK1_estatus` (`estatus_codigo`),
  CONSTRAINT `FK1_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_banners
CREATE TABLE IF NOT EXISTS `t_banners` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `archivo` varchar(80) NOT NULL DEFAULT '',
  `posicion` tinyint unsigned NOT NULL,
  `estatus_codigo` varchar(25) NOT NULL,
  `inicia` date NOT NULL,
  `vigencia` date NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_bitacoras
CREATE TABLE IF NOT EXISTS `t_bitacoras` (
  `Id` mediumint NOT NULL AUTO_INCREMENT,
  `accion_id` mediumint NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `variables` json NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`Id`) USING BTREE,
  KEY `fk_t_bitacoras_t_acciones1_idx` (`accion_id`),
  CONSTRAINT `fk_t_bitacoras_t_acciones1` FOREIGN KEY (`accion_id`) REFERENCES `t_acciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1014234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_bloques
CREATE TABLE IF NOT EXISTS `t_bloques` (
  `codigo` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `columna` int NOT NULL,
  `orden` int NOT NULL,
  `data` json NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `columna_orden` (`columna`,`orden`),
  KEY `FK_t_bloques_t_estatus` (`estatus_codigo`),
  CONSTRAINT `FK_t_bloques_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_calificaciones
CREATE TABLE IF NOT EXISTS `t_calificaciones` (
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`codigo`) USING BTREE,
  KEY `fk_t_calificaciones_t_modelos1_idx` (`modelo_codigo`),
  KEY `FK_t_calificaciones_t_estatus` (`estatus_codigo`),
  CONSTRAINT `FK_t_calificaciones_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `fk_t_calificaciones_t_modelos1` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_celulares
CREATE TABLE IF NOT EXISTS `t_celulares` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `usuario_id` mediumint unsigned NOT NULL DEFAULT '0',
  `imei` varchar(15) DEFAULT NULL,
  `fechas` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_celulares_t_estatus` (`estatus_codigo`),
  KEY `FK_t_celulares_t_usuarios` (`usuario_id`),
  CONSTRAINT `FK_t_celulares_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_celulares_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_colonias
CREATE TABLE IF NOT EXISTS `t_colonias` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `codigopostal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `localidad_id` mediumint NOT NULL,
  `entidad_id` mediumint NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK1_colonia_localidad` (`localidad_id`),
  KEY `fk_t_colonias_t_entidades1_idx` (`entidad_id`),
  CONSTRAINT `FK1_colonia_localidad` FOREIGN KEY (`localidad_id`) REFERENCES `t_localidades` (`id`),
  CONSTRAINT `fk_t_colonias_t_entidades1` FOREIGN KEY (`entidad_id`) REFERENCES `t_entidades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=143204 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_comisiones
CREATE TABLE IF NOT EXISTS `t_comisiones` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pedido_id` int DEFAULT NULL,
  `usuario_id` mediumint unsigned DEFAULT NULL,
  `esquema_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nivel` tinyint NOT NULL,
  `compresion` tinyint NOT NULL DEFAULT (0),
  `cantidad` decimal(8,2) NOT NULL DEFAULT '0.00',
  `fecha` date NOT NULL,
  `periodo_codigo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `pedido_id_esquema_codigo_nivel` (`pedido_id`,`esquema_codigo`,`estatus_codigo`,`usuario_id`,`periodo_codigo`,`cantidad`,`nivel`) USING BTREE,
  KEY `FK_t_comisiones_t_estatus` (`estatus_codigo`),
  KEY `FK_t_comisiones_t_usuarios` (`usuario_id`),
  KEY `FK_t_comisiones_t_modelos` (`esquema_codigo`) USING BTREE,
  KEY `FK_t_comisiones_t_periodos` (`periodo_codigo`),
  CONSTRAINT `FK_t_comisiones_t_esquemas` FOREIGN KEY (`esquema_codigo`) REFERENCES `t_esquemas` (`codigo`),
  CONSTRAINT `FK_t_comisiones_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_comisiones_t_pedidos` FOREIGN KEY (`pedido_id`) REFERENCES `t_pedidos` (`id`),
  CONSTRAINT `FK_t_comisiones_t_periodos` FOREIGN KEY (`periodo_codigo`) REFERENCES `t_periodos` (`codigo`),
  CONSTRAINT `FK_t_comisiones_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3982261 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_compresiones
CREATE TABLE IF NOT EXISTS `t_compresiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modelo_codigo` varchar(20) NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `fecha` date NOT NULL,
  `socios` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_compresiones_t_usuarios` (`usuario_id`),
  KEY `FK_t_compresiones_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_t_compresiones_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`),
  CONSTRAINT `FK_t_compresiones_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44043 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_domicilios
CREATE TABLE IF NOT EXISTS `t_domicilios` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `calleynumero` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `colonia_id` mediumint DEFAULT NULL,
  `referencias` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `migracion` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK3_domicilio_colonia` (`colonia_id`),
  KEY `FK1_domicilio_usuario` (`usuario_id`),
  KEY `FK2_domicilio_estatus` (`estatus_codigo`),
  CONSTRAINT `FK1_domicilio_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`),
  CONSTRAINT `FK2_domicilio_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK3_domicilio_colonia` FOREIGN KEY (`colonia_id`) REFERENCES `t_colonias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7974 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_entidades
CREATE TABLE IF NOT EXISTS `t_entidades` (
  `id` mediumint NOT NULL,
  `nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `abreviado` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `abreviado_curp` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_UNIQUE` (`nombre`) /*!80000 INVISIBLE */,
  UNIQUE KEY `abreviado_UNIQUE` (`abreviado`) /*!80000 INVISIBLE */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_esquemas
CREATE TABLE IF NOT EXISTS `t_esquemas` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `settings` json NOT NULL,
  `inicia` timestamp NOT NULL,
  `termina` timestamp NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_t_esquemas_t_estatus` (`estatus_codigo`),
  KEY `FK_t_esquemas_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_t_esquemas_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_esquemas_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_estatus
CREATE TABLE IF NOT EXISTS `t_estatus` (
  `codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `color` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_fondeos
CREATE TABLE IF NOT EXISTS `t_fondeos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `operacion` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `fecha` date NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `metodopago_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `usuario_id` mediumint unsigned DEFAULT NULL,
  `referencia` int DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '0.00',
  `extras` json NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operacion_fecha` (`operacion`,`fecha`,`usuario_id`) USING BTREE,
  KEY `FK_t_ingresos_t_estatus` (`estatus_codigo`),
  KEY `FK_t_ingresos_t_metodospago` (`metodopago_codigo`),
  CONSTRAINT `FK_t_ingresos_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_ingresos_t_metodospago` FOREIGN KEY (`metodopago_codigo`) REFERENCES `t_metodospago` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=20834 DEFAULT CHARSET=utf8mb3;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_gasolina
CREATE TABLE IF NOT EXISTS `t_gasolina` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) NOT NULL,
  `pedido_id` int NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `tarjeta` varchar(19) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fecha` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_gasolina_t_estatus` (`estatus_codigo`),
  KEY `FK_t_gasolina_t_pedidos` (`pedido_id`),
  KEY `FK_t_gasolina_t_usuarios` (`usuario_id`),
  CONSTRAINT `FK_t_gasolina_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_gasolina_t_pedidos` FOREIGN KEY (`pedido_id`) REFERENCES `t_pedidos` (`id`),
  CONSTRAINT `FK_t_gasolina_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1361 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_historico
CREATE TABLE IF NOT EXISTS `t_historico` (
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `modelo_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mes` int DEFAULT NULL,
  `cantidad` decimal(9,2) DEFAULT NULL,
  `updated` timestamp NOT NULL,
  UNIQUE KEY `mes_codigo` (`mes`,`codigo`,`modelo_codigo`) USING BTREE,
  KEY `FK_t_historico_t_modelos` (`modelo_codigo`) USING BTREE,
  CONSTRAINT `FK_t_historico_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_inversiones
CREATE TABLE IF NOT EXISTS `t_inversiones` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(50) NOT NULL,
  `pedido_id` int NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `producto_codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `cantidad` int NOT NULL,
  `fechas` json NOT NULL,
  `extras` json NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_t_inversiones_t_estatus` (`estatus_codigo`),
  KEY `FK_t_inversiones_t_usuarios` (`usuario_id`),
  KEY `FK_t_inversiones_t_productos` (`producto_codigo`),
  KEY `FK_t_inversiones_t_pedidos` (`pedido_id`),
  CONSTRAINT `FK_t_inversiones_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_inversiones_t_pedidos` FOREIGN KEY (`pedido_id`) REFERENCES `t_pedidos` (`id`),
  CONSTRAINT `FK_t_inversiones_t_productos` FOREIGN KEY (`producto_codigo`) REFERENCES `t_productos` (`codigo`),
  CONSTRAINT `FK_t_inversiones_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2404 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_isr
CREATE TABLE IF NOT EXISTS `t_isr` (
  `anio` int NOT NULL DEFAULT (0),
  `tipo` enum('ANUAL','SEMANAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `minimo` decimal(10,2) unsigned NOT NULL,
  `maximo` decimal(10,2) unsigned NOT NULL,
  `fijo` decimal(10,2) unsigned NOT NULL,
  `porcentaje` decimal(10,2) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_localidades
CREATE TABLE IF NOT EXISTS `t_localidades` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `entidad_id` mediumint NOT NULL,
  PRIMARY KEY (`id`,`entidad_id`) USING BTREE,
  KEY `FK1_entidad` (`entidad_id`) USING BTREE,
  CONSTRAINT `FK1_localidad_entidad` FOREIGN KEY (`entidad_id`) REFERENCES `t_entidades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=571 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_metodosentrega
CREATE TABLE IF NOT EXISTS `t_metodosentrega` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `settings` json NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_t_metodosentrega_t_estatus` (`estatus_codigo`),
  KEY `FK_t_metodosentrega_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_t_metodosentrega_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_metodosentrega_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_metodospago
CREATE TABLE IF NOT EXISTS `t_metodospago` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `settings` json NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_t_metodopago_t_estatus` (`estatus_codigo`),
  KEY `FK_t_metodopago_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_t_metodopago_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_metodopago_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_modelos
CREATE TABLE IF NOT EXISTS `t_modelos` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `settings` json NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`codigo`),
  KEY `FK_t_modelos_t_estatus` (`estatus_codigo`),
  CONSTRAINT `FK_t_modelos_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_pagos
CREATE TABLE IF NOT EXISTS `t_pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `clabe` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_pago_usuario` (`usuario_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=177801 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_pedidos
CREATE TABLE IF NOT EXISTS `t_pedidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `referencia` int DEFAULT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `PTS` json NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `data` json NOT NULL,
  `metodoentrega_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `promociones` json NOT NULL,
  `metodopago_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `fechas` json NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK1_pedido_usuario` (`usuario_id`),
  KEY `FK2_pedido_estatus` (`estatus_codigo`),
  KEY `fk_t_pedidos_t_modelos1_idx` (`modelo_codigo`),
  KEY `FK_t_pedidos_t_metodopago` (`metodopago_codigo`),
  KEY `FK_t_pedidos_t_metodosentrega` (`metodoentrega_codigo`),
  KEY `referencia` (`referencia`),
  CONSTRAINT `FK1_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`),
  CONSTRAINT `FK2_pedido_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_pedidos_t_metodopago` FOREIGN KEY (`metodopago_codigo`) REFERENCES `t_metodospago` (`codigo`),
  CONSTRAINT `FK_t_pedidos_t_metodosentrega` FOREIGN KEY (`metodoentrega_codigo`) REFERENCES `t_metodosentrega` (`codigo`),
  CONSTRAINT `fk_t_pedidos_t_modelos1` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=1268397 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_periodos
CREATE TABLE IF NOT EXISTS `t_periodos` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tipo` enum('ANUAL','SEMANAL','MENSUAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `inicia` date DEFAULT NULL,
  `termina` date DEFAULT NULL,
  `data` json NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_t_periodos_t_estatus` (`estatus_codigo`),
  CONSTRAINT `FK_t_periodos_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_pines
CREATE TABLE IF NOT EXISTS `t_pines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) NOT NULL,
  `rango_codigo` varchar(20) NOT NULL,
  `usuario_id` mediumint unsigned NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rango_codigo_usuario_id` (`rango_codigo`,`usuario_id`),
  KEY `FK_t_pines_t_estatus` (`estatus_codigo`),
  KEY `FK_t_pines_t_usuarios` (`usuario_id`),
  CONSTRAINT `FK_t_pines_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_pines_t_rangos` FOREIGN KEY (`rango_codigo`) REFERENCES `t_rangos` (`codigo`),
  CONSTRAINT `FK_t_pines_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1903 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_productos
CREATE TABLE IF NOT EXISTS `t_productos` (
  `codigo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `categoria_id` smallint unsigned DEFAULT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `precio` json NOT NULL,
  `data` json NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `fk_t_productos_t_modelos1_idx` (`modelo_codigo`),
  KEY `FK1_estatus` (`estatus_codigo`),
  CONSTRAINT `fk_t_productos_t_estatus1` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `fk_t_productos_t_modelos1` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_promociones
CREATE TABLE IF NOT EXISTS `t_promociones` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `settings` json NOT NULL,
  `inicia` timestamp NOT NULL,
  `termina` timestamp NOT NULL,
  `productos` json NOT NULL,
  `formulas` json NOT NULL,
  PRIMARY KEY (`codigo`) USING BTREE,
  KEY `FK_estatus` (`estatus_codigo`),
  KEY `FK_t_promociones_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_promociones_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_rangos
CREATE TABLE IF NOT EXISTS `t_rangos` (
  `codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `hex` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `cantidades` json NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_t_rangos_t_modelos` (`modelo_codigo`),
  CONSTRAINT `FK_t_rangos_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_recargas
CREATE TABLE IF NOT EXISTS `t_recargas` (
  `paquete` varchar(150) DEFAULT NULL,
  `usuario_id` mediumint unsigned DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_recompensas
CREATE TABLE IF NOT EXISTS `t_recompensas` (
  `codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `modelo_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `estrellas` mediumint NOT NULL DEFAULT (0),
  `ciclo` tinyint NOT NULL DEFAULT (1),
  `clase` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `icono` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `rango_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `FK_t_recompensas_t_estatus` (`estatus_codigo`),
  KEY `FK_t_recompensas_t_modelos` (`modelo_codigo`),
  KEY `FK_t_recompensas_t_rangos` (`rango_codigo`),
  CONSTRAINT `FK_t_recompensas_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_recompensas_t_modelos` FOREIGN KEY (`modelo_codigo`) REFERENCES `t_modelos` (`codigo`),
  CONSTRAINT `FK_t_recompensas_t_rangos` FOREIGN KEY (`rango_codigo`) REFERENCES `t_rangos` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_redenciones
CREATE TABLE IF NOT EXISTS `t_redenciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) NOT NULL DEFAULT '',
  `usuario_id` mediumint unsigned NOT NULL,
  `recompensa_codigo` varchar(20) NOT NULL DEFAULT '',
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_redenciones_t_estatus` (`estatus_codigo`),
  KEY `FK_t_redenciones_t_usuarios` (`usuario_id`),
  KEY `FK_t_redenciones_t_recompensas` (`recompensa_codigo`),
  CONSTRAINT `FK_t_redenciones_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_redenciones_t_recompensas` FOREIGN KEY (`recompensa_codigo`) REFERENCES `t_recompensas` (`codigo`),
  CONSTRAINT `FK_t_redenciones_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_retiros
CREATE TABLE IF NOT EXISTS `t_retiros` (
  `id` mediumint NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) NOT NULL DEFAULT '',
  `usuario_id` mediumint unsigned NOT NULL,
  `inversion_id` mediumint NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `deposito` decimal(10,2) NOT NULL,
  `tipo` enum('MENSUAL','TOTAL','PARCIAL','STOTAL','SPARCIAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fechas` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_retiros_t_estatus` (`estatus_codigo`),
  KEY `FK_t_retiros_t_usuarios` (`usuario_id`),
  KEY `FK_t_retiros_t_inversiones` (`inversion_id`),
  CONSTRAINT `FK_t_retiros_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_retiros_t_inversiones` FOREIGN KEY (`inversion_id`) REFERENCES `t_inversiones` (`id`),
  CONSTRAINT `FK_t_retiros_t_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1470 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_roles
CREATE TABLE IF NOT EXISTS `t_roles` (
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tipo` enum('BLOQUEO','SOCIO','ADMIN','ROOT','PERMANENTE') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_tarjetas
CREATE TABLE IF NOT EXISTS `t_tarjetas` (
  `tarjeta` int NOT NULL,
  `empleado` int NOT NULL,
  UNIQUE KEY `tarjeta` (`tarjeta`),
  UNIQUE KEY `empleado` (`empleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_transferencias
CREATE TABLE IF NOT EXISTS `t_transferencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) NOT NULL,
  `producto_codigo` varchar(20) NOT NULL,
  `cantidad` smallint NOT NULL DEFAULT (0),
  `origen` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `destino` varchar(20) NOT NULL,
  `fecha` date DEFAULT NULL,
  `envia` mediumint unsigned NOT NULL,
  `recibe` mediumint unsigned DEFAULT NULL,
  `notas` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_transferencias_t_estatus` (`estatus_codigo`),
  KEY `FK_t_transferencias_t_productos` (`producto_codigo`),
  KEY `FK_t_transferencias_t_almacenes` (`origen`),
  KEY `FK_t_transferencias_t_almacenes_2` (`destino`),
  KEY `FK_t_transferencias_t_usuarios` (`envia`),
  KEY `FK_t_transferencias_t_usuarios_2` (`recibe`),
  CONSTRAINT `FK_t_transferencias_t_almacenes` FOREIGN KEY (`origen`) REFERENCES `t_almacenes` (`codigo`),
  CONSTRAINT `FK_t_transferencias_t_almacenes_2` FOREIGN KEY (`destino`) REFERENCES `t_almacenes` (`codigo`),
  CONSTRAINT `FK_t_transferencias_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`),
  CONSTRAINT `FK_t_transferencias_t_productos` FOREIGN KEY (`producto_codigo`) REFERENCES `t_productos` (`codigo`),
  CONSTRAINT `FK_t_transferencias_t_usuarios` FOREIGN KEY (`envia`) REFERENCES `t_usuarios` (`id`),
  CONSTRAINT `FK_t_transferencias_t_usuarios_2` FOREIGN KEY (`recibe`) REFERENCES `t_usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6424 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Movimientos de mercancía entre almacenes';

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_usuarios
CREATE TABLE IF NOT EXISTS `t_usuarios` (
  `id` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `estatus_codigo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `rol_codigos` json NOT NULL,
  `password` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '0',
  `data` json NOT NULL,
  `correo` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `telefono` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fechanac` date DEFAULT NULL,
  `curp` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `redes` json NOT NULL,
  `historial` json NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_t_usuarios_t_estatus` (`estatus_codigo`),
  CONSTRAINT `FK_t_usuarios_t_estatus` FOREIGN KEY (`estatus_codigo`) REFERENCES `t_estatus` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=169116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for table vpsbeneleitmx_app.t_variables
CREATE TABLE IF NOT EXISTS `t_variables` (
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `descripcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tipo` enum('NUMERO','TEXTO','JSON') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'TEXTO',
  `valor` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Data exporting was unselected.

-- Dumping structure for view vpsbeneleitmx_app.v_moodle
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_moodle` (
	`id` MEDIUMINT(7) UNSIGNED NOT NULL,
	`password` LONGTEXT NULL COLLATE 'utf8mb4_bin',
	`nombre` LONGTEXT NULL COLLATE 'utf8mb4_bin',
	`apellidos` LONGTEXT NULL COLLATE 'utf8mb4_bin',
	`correo` VARCHAR(70) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`pais` LONGTEXT NULL COLLATE 'utf8mb4_bin',
	`telefono` VARCHAR(10) NOT NULL COLLATE 'utf8mb4_0900_ai_ci'
) ENGINE=MyISAM;

-- Dumping structure for procedure vpsbeneleitmx_app.p_avance_corte
DELIMITER //
CREATE PROCEDURE `p_avance_corte`(IN `avance` JSON)
    DETERMINISTIC
BEGIN
	UPDATE t_variables SET valor = avance WHERE codigo = 'avance_corte';
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_cobra_estrellas
DELIMITER //
CREATE PROCEDURE `p_cobra_estrellas`(IN `p_socio` MEDIUMINT, IN `p_resta` SMALLINT)
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
corte: BEGIN   

	-- GENERAR PAGOS DE PERIODO
	
	-- Esta función tardó 6 años en perfeccionarse, aquí se hace toda la magia del CORTE SEMANAL
	-- tambien aqui se incluyen los pagos mensuales y anuales ya que detecta
	-- cuando hay cambio de mes y cambio de año (año beneleit, 1 de septiembre)
	
	-- Desarrollada por Alex (scabbia@gmail.com) para BENELEIT
	
	-- NO MOVER NADA SI NO SE SABE LO QUE SE ESTA HACIENDO
	-- tengo miedo!!!!!!!!
	
	-- ************************************************************************

	-- Variables a utilizar en el entorno global
	
    DECLARE 
		d_usuario, 
		d_menor, 
		d_retencion, 
		p_pagos, 
		d_bolsa, 
		pedidos, 
		porcentaje, 
		anterior_abierto, 
		abiertos, 
		total_socios INT DEFAULT 0;
		
    DECLARE d_comisiones, d_isr, p_comisiones, p_isr, b_total, total_venta DECIMAL(10,2) DEFAULT 0.00;
    DECLARE d_data, p_data, jsondata, avance, datos JSON;
	DECLARE a_json JSON DEFAULT f_compulsa_valores( input_periodo );
    DECLARE d_modelo, d_clabe, d_wallet varchar(60);
    DECLARE m_ini, m_ter VARCHAR( 6 );
    DECLARE f_ini, f_ter VARCHAR( 10 );
    declare piloto JSON default json_array();
    
   	-- Obtenemos un 0 si todos los periodos anteriores ya han sido cerrados 
	-- o un 1 si existen periodos pendientes por cerrar
	-- En caso de obtener un 1, no consideramos en el corte parcial las comisiones pendientes de pago 
	-- que pertenezcan a periodos anteriores, ya sea porque no alcanzan el mínimo ($100) o por
	-- pedidos fuera de fecha que se hayan marcado pagados
	-- (Se ignoran los periodos anteriores al nuevo sistema para evitar confusión)
	
	SELECT 
		COUNT(*) 
	into abiertos
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
			SELECT pd.id,
				pd.data->>'$.total' 
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

			SET avance = JSON_SET( avance, '$.total_pedidos', pedidos, '$.pedidos', 0, '$.proceso', JSON_OBJECT(), '$.venta', 0 );
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
	        FETCH FROM cur_pedidos INTO pid, total_venta;
	        
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
        	SET total_venta = total_venta + avance->>'$.venta';
        	
			SET avance = JSON_SET( avance, 
				'$.porcentaje_comisiones', porcentaje, 
				'$.pedidos', cont, 
				'$.venta', total_venta,
				'$.socios', IFNULL( JSON_LENGTH( socios ) + total_socios, 0 ),
				'$.proceso', JSON_ARRAY( offset, step, pid, cont, avance->'$.pedidos', porcentaje )
			);
			
			call p_avance_corte( avance );

	    END LOOP lop_pedidos;
	END reparto;


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
				    IF( d_modelo != '50-INVERSION', u.data->'$.sat.estatus', 0 ),
				    cast( u.data->>'$.clabe' as char ),
					cast( u.data->>'$.wallet' as char ),
					SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( u.id, m_ini), 1 ) ),
					IF( d_modelo != '50-INVERSION' AND u.data->'$.sat.estatus' < 2, CAST( f_calcula_isr( SUM( c.cantidad * IF( e.codigo = '118-PROMOS-50', f_get_factor_promos( u.id, m_ini), 1 ) ), 2024, 'SEMANAL' ) AS DECIMAL( 10,2 ) ), 0)
				FROM t_comisiones c
					LEFT JOIN t_pedidos pe ON pe.id = c.pedido_id
					left JOIN t_periodos p ON p.codigo = input_periodo COLLATE utf8mb4_0900_ai_ci
					LEFT JOIN t_usuarios u ON u.id = c.usuario_id
					LEFT JOIN t_esquemas e ON e.codigo = c.esquema_codigo COLLATE utf8mb4_0900_ai_ci
				WHERE 
					( 
						( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2024-09-01' AND '2025-08-31' ) OR
						( e.codigo IN ( '118-PROMOS-50' ) AND c.fecha BETWEEN f_ini AND f_ter ) OR 
						( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
 						( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha < p.inicia AND abiertos = 0 )						
					) 
					AND substring( c.estatus_codigo, 1, 3 ) > 200
					AND e.estatus_codigo = '201-ACTIVO'
					AND c.estatus_codigo = '255-PENDIENTE' 
					AND substring( pe.estatus_codigo, 1, 3 ) > 400
--					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF(input_periodo = '10S202537', 'ANUAL', 'NO-ANUAL' ) )
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
					( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2024-09-01' AND '2025-08-31' ) OR
					( e.codigo IN ( '118-PROMOS-50' ) AND c.fecha BETWEEN f_ini AND f_ter ) OR 
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
 					( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha < p.inicia AND abiertos = 0 )						
				) 
				-- c.fecha BETWEEN IF( e.codigo = '118-PROMOS-50', f_ini, p.inicia ) AND IF( e.codigo = '118-PROMOS-50', f_ter, p.termina )
				AND substring( c.estatus_codigo, 1, 3 ) > 200
				AND e.estatus_codigo = '201-ACTIVO'
				AND c.estatus_codigo = '255-PENDIENTE'
				AND substring( pe.estatus_codigo, 1, 3 ) > 400
--					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF(input_periodo = '10S202537', 'ANUAL', 'NO-ANUAL' ) )
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
					( e.codigo = '116-ANIVERSARIO' AND c.fecha BETWEEN '2024-09-01' AND '2025-08-31' ) OR
					( e.codigo IN ( '118-PROMOS-50' ) AND c.fecha BETWEEN f_ini AND f_ter ) OR 
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha BETWEEN p.inicia AND p.termina ) OR 
					( e.settings->>'$.periodo' = 'SEMANAL' AND c.fecha < p.inicia AND abiertos = 0 )
				) 
				AND substring( c.estatus_codigo, 1, 3 ) > 200
				AND e.estatus_codigo = '201-ACTIVO'
				AND c.estatus_codigo = '255-PENDIENTE' 
				AND substring( pe.estatus_codigo, 1, 3 ) > 400
--					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF( SUBSTRING( m_ini, 5, 2) = '08' AND SUBSTRING( m_ter, 5, 2) = '09', 'ANUAL', 'NO-ANUAL' ) )
					AND e.settings->>'$.periodo' IN ( p.tipo, IF( m_ini != m_ter, 'MENSUAL', 'NO-MENSUAL' ), IF( input_periodo = '10S202537', 'ANUAL', 'NO-ANUAL' ) )
				and e.modelo_codigo = p.modelo_codigo COLLATE utf8mb4_0900_ai_ci;	
		    		    
		    		    
		    		   
		    		    
		    -- Guardamos el total de pagos a generar para datos estadísticos de progreso del corte
				
		    SET avance = JSON_SET( avance, '$.total_pagos', pagos + JSON_LENGTH( a_json ) );
		    call p_avance_corte( avance );
		    
		    -- Limpiamos todos los pagos de ese periodo hechos en cortes parciales anteriores
	    
		    DELETE from t_pagos WHERE data->>'$.periodos.creacion' = input_periodo;
		            
		    OPEN cur_pagos;
		 
		    lop_pagos : LOOP
		        FETCH FROM cur_pagos INTO d_usuario, d_menor, d_retencion, d_clabe, d_wallet, d_comisiones, d_isr;
		         
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
				-- (Quizas ese JSON se pueda generar desde una función para no ensuciar este código)
				-- y así ya no tener que hacer el cálculo en backend 
				-- sino que solo se elijan los campos necesarios dependiendo el tipo de facturación de cada socio
					      
	            SET d_data = JSON_OBJECT(
	                "retencion", impuestos,
	                "menor", d_menor,
	                "verificado", 
					0 --	JSON_EXTRACT( f_es_verificado( d_usuario ), '$.estatus' ) 
						AND IF( 
							d_modelo = '50-INVERSION', 
							d_wallet is not null and length( d_wallet ) = 34, 
							d_clabe is not null and length( d_clabe ) = 18 
							),
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
	      --  set piloto = json_array_append( piloto, '$', json_array( d_usuario, cast( JSON_EXTRACT( f_es_verificado( d_usuario ), '$.estatus' ) as unsigned ) ) );

				-- IMPORTANTE: solo pagos mayores de $100 se procesan (excepto inversiones)
				-- Regla aplicada a partir de la semana 40-2024
				-- Los pagos menores a $100 se cancelan y las comisiones se conservan en estatus pendiente
				-- para agregarse en automático en el siguiente corte
							
				if total >= 100 OR d_modelo = '50-INVERSION' then 
					-- Si cumple el mínimo, se procesa el pago
				
					INSERT INTO t_pagos VALUES(NULL, '250-EN-PROCESO', d_modelo, d_usuario, IF( d_modelo != '50-INVERSION', d_clabe, d_wallet ), d_data );
	            
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
				
		        
--		        set piloto = json_array_append( piloto, '$', json_array(d_usuario, d_menor, d_retencion, d_clabe, d_wallet, d_comisiones, d_isr) );
		    END LOOP lop_pagos;
		END pagos;
		
		
	       --    return piloto;
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

END corte//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_get_compras
DELIMITER //
CREATE PROCEDURE `p_get_compras`(
	IN `socio` MEDIUMINT
)
BEGIN
	WITH recursive cte (id, estatus, nivel, c202510, c202511, c202512, c202601 ) AS (
	   SELECT 
		 	u1.id, 
			SUBSTRING( u1.data->>'$.estatus.modelos."10-NUTRICION"', 1, 3 ),
			0,
			0,0,0,0
		FROM t_usuarios AS u1 
		WHERE u1.id = socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
			SUBSTRING( u2.data->>'$.estatus.modelos."10-NUTRICION"', 1, 3 ),
	    	cte.nivel + 1,
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202510"."010-DISTRIBUIDOR"', 0 ),
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202511"."010-DISTRIBUIDOR"', 0 ),
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202512"."010-DISTRIBUIDOR"', 0 ),
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202601"."010-DISTRIBUIDOR"', 0 )
		FROM cte
		JOIN t_usuarios AS u2 ON cte.id = IF( u2.redes->>'$.modelos."10-NUTRICION".padre' = 'null', NULL, u2.redes->>'$.modelos."10-NUTRICION".padre' )
	    WHERE cte.nivel < 3 
		AND cte.estatus > 200 
	)
	SELECT * FROM cte where estatus > 200;
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_get_estadistica
DELIMITER //
CREATE PROCEDURE `p_get_estadistica`(
	IN `socio` MEDIUMINT,
	IN `mes` INT,
	IN `modelo` VARCHAR(20)
)
BEGIN
	DECLARE niveles int default 3;
	DECLARE done INT DEFAULT 0;
	DECLARE v_id, v_padre, v_patrocinador, v_nivel, v_updated, v_antiguedad, v_verificado INT;
	DECLARE v_estatus, v_avatar, v_nombre, v_iniciales, v_rango, v_registro VARCHAR(225);
	DECLARE v_calificaciones, resultado, v_profundidad JSON DEFAULT JSON_ARRAY();
	DECLARE cacha, tempo JSON;
	
	select settings->'$.niveles' into niveles from t_modelos where codigo = modelo;
	
	WITH recursive cte (id, estatus, registro, padre, nivel, ingresos, primercompra, consumo ) AS (
	   SELECT 
		 	u1.id, 
		 	JSON_UNQUOTE(JSON_EXTRACT( u1.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),
			cast( u1.historial->>'$.registro' as date ),
			0,
			0,
			f_get_ingresos(u1.id, mes, modelo ),
			f_fecha_primercompra( u1.id, modelo ),
			f_get_consumo( u1.id, mes, modelo )
		FROM t_usuarios AS u1 
		WHERE u1.id = socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
			JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),
			cast( u2.historial->>'$.registro' as date ),
			JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".padre' ) ) ),
	    	cte.nivel + 1,
	    	f_get_ingresos(u2.id, mes, modelo ),
	    	f_fecha_primercompra( u2.id, modelo ),
  			f_get_consumo( u2.id, mes, modelo )
		FROM cte
		JOIN t_usuarios AS u2 ON cte.id = IF( JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo,'".padre' ) ) ) = 'null', NULL, JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT( '$.modelos."', modelo,'".padre' ) ) ) )
	    WHERE cte.nivel < niveles AND SUBSTRING( JSON_UNQUOTE(JSON_EXTRACT( u2.data, CONCAT( '$.estatus.modelos."', modelo, '"' ) ) ),1,3 ) > 200 AND u2.redes->'$.verificado' is null
	)
	SELECT * FROM cte;
	
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_get_inversiones
DELIMITER //
CREATE PROCEDURE `p_get_inversiones`(
	IN `socio` MEDIUMINT,
	IN `mes` VARCHAR(6)
)
BEGIN
	WITH recursive cte (id, estatus, nivel, semilla, activacion) AS (
	   SELECT 
		 	u1.id, 
			SUBSTRING( u1.data->>'$.estatus.modelos."50-INVERSION"', 1, 3 ),
			0,
			f_get_semilla( u1.id, mes, socio ),
			u1.historial->>'$.modelos."50-INVERSION".primercompra."510-SEMILLA"'
		FROM t_usuarios AS u1 
		WHERE u1.id = socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
			SUBSTRING( u2.data->>'$.estatus.modelos."50-INVERSION"', 1, 3 ),
	    	cte.nivel + 1,
			f_get_semilla( u2.id, mes, socio ),
			u2.historial->>'$.modelos."50-INVERSION".primercompra."510-SEMILLA"'
		FROM cte
		JOIN t_usuarios AS u2 ON cte.id = IF( u2.redes->>'$.modelos."50-INVERSION".padre' = 'null', NULL, u2.redes->>'$.modelos."50-INVERSION".padre' )
	    WHERE cte.nivel < 4 
		AND cte.estatus > 200 
	)
	SELECT * FROM cte order by activacion, nivel;
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_get_paquetes
DELIMITER //
CREATE PROCEDURE `p_get_paquetes`(IN `socio` MEDIUMINT, IN `m_0` VARCHAR(6))
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
		-- AND SUBSTRING( f_get_calificacion(u2.id, m_0, modelo ), 5, 1 )!= '-'
	)
	SELECT * FROM cte;

END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_update_niveles
DELIMITER //
CREATE PROCEDURE `p_update_niveles`(IN `i_socio` INT, IN `i_modelo` VARCHAR(20), IN `mes` VARCHAR(6))
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


			
	UPDATE t_usuarios 
		SET redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".padre' ), f_get_padre( socio, modelo ) ) 
		WHERE id = socio;
			
	

end//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_update_primercompra
DELIMITER //
CREATE PROCEDURE `p_update_primercompra`(
	IN `input_socio` INT,
	IN `input_modelo` VARCHAR(20)
)
    DETERMINISTIC
BEGIN
	
	DECLARE fecha DATE;
	declare puntos, pedido, ps, k, pc INT;
	declare prom, checa VARCHAR(25);
	declare ruta VARCHAR(225);	
	declare PTS, llaves JSON;
	
	
	SELECT id, f, PTS, pc
	into   pedido, fecha, PTS, pc
	from (
		SELECT 
			sum( JSON_EXTRACT( p.PTS, CONCAT( '$."', promo,'"') ) ) as ptss, 
			p.id,
			p.fechas->>'$.califica' as f,
			p.PTS,
			p.data->'$.primercompra' as pc
		from t_pedidos p
		join t_modelos m on m.codigo = p.modelo_codigo,
		JSON_TABLE( m.settings->>'$.promocion_base', '$[*]' COLUMNS (
		     promo VARCHAR(40)  PATH '$'
		 ) ) promos
		 
		 where m.codigo   = input_modelo
		 and p.usuario_id = input_socio 
		 AND CAST( substring( p.estatus_codigo, 1, 3 ) AS UNSIGNED ) > 400
		 
		 group by p.id
		 having ptss >= 1
		 ORDER BY fechas->>'$.califica' asc /* , data->'$.total' desc */ LIMIT 1
	) a;
	
	
	IF pedido is not null THEN
		-- SELECT p.PTS into PTS from t_pedidos p where p.id = pedido;
			
		-- listado de promociones compradas
		
		SET k = 0;
		SELECT JSON_KEYS( PTS ) into llaves;
			
		-- aplicamos la fecha de primer compra a cada una
	
		WHILE k < JSON_LENGTH( llaves ) DO
			SET prom = JSON_EXTRACT( llaves, CONCAT( '$[', k, ']' ) );
			SET ruta = CONCAT( '$.modelos."', input_modelo, '".primercompra.', prom );
		
			UPDATE t_usuarios u
			set u.historial = JSON_SET( u.historial, ruta, fecha )
			where u.id = input_socio;
			
			SET k = k + 1;
		END WHILE;
		
		-- Ultima compra calificada para ese modelo
		
		SELECT JSON_UNQUOTE( JSON_EXTRACT( historial, CONCAT( '$.modelos."', input_modelo, '".ultimacompra' ) ) )
		into checa
		from t_usuarios
		where id = input_socio;
	
		IF checa is null or checa = 'null' or LENGTH( checa ) != 10 THEN
			UPDATE t_usuarios u
			set u.historial = JSON_SET( u.historial, CONCAT( '$.modelos."', input_modelo, '".ultimacompra' ), fecha )
			where u.id = input_socio;		
		END IF;
		
		-- ****************************************************************
		-- return json_array(pedido, pc);
		-- [1235941, null]
		
		IF pc is null or pc != 1 THEN
	
			UPDATE t_pedidos p set p.data = json_set( p.data, '$.primercompra', 0) where p.modelo_codigo = input_modelo and p.usuario_id = input_socio;
			UPDATE t_pedidos p set p.data = json_set( p.data, '$.primercompra', 1) where p.id = pedido;
			/*
			UPDATE t_pedidos p, (
			    SELECT 
			        usuario_id, 
					id as pedido,
			        MIN( cast( fechas->>'$.pagado' as date ) ) AS fecha_primera,
			        data->'$.total' as total
			    FROM t_pedidos
			    where 
			    usuario_id = input_socio
				and substring( estatus_codigo, 1, 3) > 400
			    and modelo_codigo = input_modelo
			    GROUP BY usuario_id, id
			    order by total desc
			    limit 1
			) primera
			set p.data = json_set( data, '$.primercompra', 1) where p.id = primera.pedido;	
			*/
				
		END IF;
		
		-- ****************************************************************		
	END IF;
END//
DELIMITER ;

-- Dumping structure for procedure vpsbeneleitmx_app.p_update_rango
DELIMITER //
CREATE PROCEDURE `p_update_rango`(IN `socio` INT, IN `modelo` VARCHAR(20))
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

	if puntos_generados > 9 then
		SET puntos_generados = 9;
	END if;
	
	SET puntos_generados = FLOOR( puntos_generados / 3 );
	SET estrellas_recibidas = puntos_generados - estrellas_generadas;

	RETURN IF( estrellas_recibidas > 0, estrellas_recibidas, 0 );

END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_calcula_isr
DELIMITER //
CREATE FUNCTION `f_calcula_isr`(`cantidad` DECIMAL(8,2), `anio` INT, `tipo` VARCHAR(10)) RETURNS decimal(10,2)
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
CREATE FUNCTION `f_checks_rango`(
	`u` MEDIUMINT,
	`modelo` VARCHAR(20)
) RETURNS json
    DETERMINISTIC
BEGIN
	DECLARE checks, cantidades, splash JSON;
	DECLARE rango_actual, rango_nuevo VARCHAR(20);
	DECLARE ingresos DECIMAL(10,2);
	
	DECLARE mes MEDIUMINT default DATE_FORMAT( now(), '%Y%m' );
	DECLARE anterior MEDIUMINT default DATE_FORMAT( date_sub( now(), interval 1 month ), '%Y%m' );
	
	-- temporal para evitar que los rangos de diferentes modelos de negocio se mezclen
	SET modelo = '10-NUTRICION';
	
	select data->>'$.rango', data->>'$.splash' INTO rango_actual, splash FROM t_usuarios WHERE id = u;

	SELECT r.codigo, r.cantidades
	INTO rango_nuevo, cantidades
	FROM t_rangos r
	JOIN t_usuarios u ON u.id = u
	WHERE r.modelo_codigo = modelo
	AND SUBSTRING( r.codigo, 1, 3 ) > SUBSTRING( rango_actual, 1, 3 )
	ORDER BY r.codigo ASC LIMIT 1;

	if rango_nuevo is not null then

		SET checks = JSON_OBJECT( 
			'actual', 		rango_actual, 
			'nuevo', 		rango_nuevo, 
			'ingresos', 	f_get_ingresos( u, anterior, modelo )
		);
		
		SET ingresos = CAST( JSON_EXTRACT( checks, CONCAT( '$.ingresos' ) ) AS DECIMAL(10,2) );
	
		UPDATE t_usuarios SET 
			historial = JSON_SET( historial, CONCAT( '$.modelos."', modelo, '".ingresos."', anterior, '"' ), ingresos )
		WHERE id = u;
		
							
		-- si se cubre la cantidad
		-- se agrega el rango, el pin y el splash
		-- return json_array(anterior, ingresos, cantidades, splash);
		if ingresos > cantidades->>'$[0]' AND JSON_CONTAINS( splash->'$[*].tipo', '"rango"' ) IS null then 
			UPDATE t_usuarios SET 
				DATA  = JSON_SET( data, '$.rango' , rango_nuevo ), 
				redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".rango' ), rango_nuevo )
			WHERE id  = u;
	
			UPDATE t_usuarios SET 
				DATA  = JSON_ARRAY_APPEND( data, '$.splash', JSON_OBJECT( 'tipo', 'rango', 'parametros', JSON_ARRAY( rango_nuevo ) ) )
			WHERE id  = u;
			
			INSERT IGNORE INTO t_pines VALUES( NULL, '225-ALCANZADO', rango_nuevo, u, CAST( NOW() AS DATE ) );
		ELSE
	
		
			SET checks = JSON_OBJECT( 
				'actual', 		rango_actual, 
				'nuevo', 		rango_nuevo, 
				'ingresos', 	f_get_ingresos( u, mes, modelo )
			);
			
			SET ingresos = CAST( JSON_EXTRACT( checks, CONCAT( '$.ingresos' ) ) AS DECIMAL(10,2) );
		
			UPDATE t_usuarios SET 
				historial = JSON_SET( historial, CONCAT( '$.modelos."', modelo, '".ingresos."', mes, '"' ), ingresos )
			WHERE id = u;
			
								
			-- si se cubre la cantidad
			-- se agrega el rango, el pin y el splash
	
			if ingresos > cantidades->>'$[0]' AND JSON_CONTAINS( splash->'$[*].tipo', '"rango"' ) IS null then 
				UPDATE t_usuarios SET 
					DATA  = JSON_SET( data, '$.rango' , rango_nuevo ), 
					redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".rango' ), rango_nuevo )
				WHERE id  = u;
		
				UPDATE t_usuarios SET 
					DATA  = JSON_ARRAY_APPEND( data, '$.splash', JSON_OBJECT( 'tipo', 'rango', 'parametros', JSON_ARRAY( rango_nuevo ) ) )
				WHERE id  = u;
				
				INSERT IGNORE INTO t_pines VALUES( NULL, '225-ALCANZADO', rango_nuevo, u, CAST( NOW() AS DATE ) );
			END if;	
		END IF;
	END if;
		
	RETURN checks;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_compresion_de_red
DELIMITER //
CREATE FUNCTION `f_compresion_de_red`(`u` MEDIUMINT, `m` VARCHAR(20)) RETURNS json
    DETERMINISTIC
BEGIN
	DECLARE ppt, padre, patrocinador_estatus INT;
	DECLARE socios JSON DEFAULT JSON_ARRAY();
	
	SELECT u.id, SUBSTRING( u.estatus_codigo, 1, 3 ) INTO ppt, patrocinador_estatus
	FROM t_usuarios e 
	JOIN t_usuarios u ON u.id = JSON_UNQUOTE( JSON_EXTRACT( e.redes, CONCAT( '$.modelos."', m, '".padre' ) ) ) 
	WHERE e.id = u;

	if patrocinador_estatus < 200 then
	
		SET padre = f_get_padre( u, m );
				
		SELECT json_arrayagg( id ) into socios FROM t_usuarios 
		WHERE JSON_UNQUOTE( JSON_EXTRACT( redes, CONCAT( '$.modelos."', m, '".padre' ) ) ) = u
		AND SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( DATA, CONCAT( '$.estatus.modelos."', m, '"' ) ) ), 1, 3 ) > 200;

		RETURN JSON_ARRAY(ppt, patrocinador_estatus, padre);
	
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
CREATE FUNCTION `f_compulsa_valores`(`periodo` VARCHAR(20)) RETURNS json
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
CREATE FUNCTION `f_estrellas_en_pedido`(`input_pedido` DECIMAL(8,2)) RETURNS int
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
	DECLARE fn varchar(10);
	
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
			    	SET requerido = IF( LENGTH( fn ) = 10 AND fn <= DATE_SUB(NOW(), INTERVAL -18 YEAR), 0, 1 );
			    WHEN llave = "csf" THEN 
			    	SET requerido = IF( u_data->'$.sat.estatus', 1, 0 );
			   	ELSE  
			   		SET requerido = JSON_EXTRACT( punto, '$.requerido');
			END case;

		SET tempo = JSON_SET( tempo, CONCAT( '$."', llave, '"' ), JSON_OBJECT( "requerido", requerido, "checked", IF( JSON_EXTRACT( u_data, CONCAT( '$.verificacion."', llave, '"' ) ) in ('true', '1', 1, true ), true, false ) ) );

		if requerido then
			if JSON_EXTRACT( u_data, CONCAT( '$.verificacion."', llave, '"' ) ) then
				set verificados = verificados + 1;
			END if;
			
			SET total = total + 1;
		end if;
		
	    SET j = j + 1;
	END WHILE;	


	SET porcentaje = IF( total > 0, CEIL( verificados * 100 / total ), 0 );
	RETURN JSON_OBJECT( "puntos", tempo, "porcentaje", porcentaje, "estatus", if( porcentaje = 100, TRUE, FALSE ), "formula", formula );
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_fecha_primercompra
DELIMITER //
CREATE FUNCTION `f_fecha_primercompra`(
	`input_socio` INT,
	`input_modelo` VARCHAR(20)
) RETURNS date
    DETERMINISTIC
BEGIN
	
	DECLARE fecha DATE;
	declare puntos, pedido, k INT;
	declare prom, checa VARCHAR(25);
	declare PTS, llaves JSON;
	
	
	SELECT ptss, id, f, PTS
	into   puntos, pedido, fecha, PTS
	from (
		SELECT 
			sum( JSON_EXTRACT( p.PTS, CONCAT( '$."', promo,'"') ) ) as ptss, 
			p.id,
			p.fechas->>'$.califica' as f,
			p.PTS
		from t_pedidos p
		join t_modelos m on m.codigo = p.modelo_codigo,
		JSON_TABLE( m.settings->>'$.promocion_base', '$[*]' COLUMNS (
		     promo VARCHAR(40)  PATH '$'
		 ) ) promos
		 
		 where m.codigo   = input_modelo
		 and p.usuario_id = input_socio 
		 AND CAST( substring( p.estatus_codigo, 1, 3 ) AS UNSIGNED ) > 400
		 -- and IF( m.codigo = '10-NUTRICION', p.PTS->'$."101-SEMILLERO-16"', 0 ) = 0
		 
		 group by p.id
		 having ptss > 0
		 ORDER BY fechas->>'$.califica' asc LIMIT 1
	) a;
	
	/*
	SET k = 0;
	SELECT JSON_KEYS( PTS ) into llaves;
	
	WHILE k < JSON_LENGTH( llaves ) DO
		SET prom = JSON_EXTRACT( llaves, CONCAT( '$[', k, ']' ) );

		UPDATE t_usuarios u
		set u.historial = JSON_SET( u.historial, CONCAT( '$.modelos."', input_modelo, '".primercompra."', prom, '"' ), fecha )
		where u.id = input_socio;
		
		SET k   = k + 1;
	END WHILE;
	
	SELECT JSON_UNQUOTE( JSON_EXTRACT( historial, CONCAT( '$.modelos."', input_modelo, '".ultimacompra' ) ) )
	into checa
	from t_usuarios
	where id = input_socio;

	IF checa is null or checa = 'null' or LENGTH( checa ) != 10 THEN
		UPDATE t_usuarios u
		set u.historial = JSON_SET( u.historial, CONCAT( '$.modelos."', input_modelo, '".ultimacompra' ), fecha )
		where u.id = input_socio;		
	END IF;
	*/

	RETURN fecha;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_avatar
DELIMITER //
CREATE FUNCTION `f_get_avatar`(`socio` MEDIUMINT) RETURNS varchar(50) CHARSET utf8mb4
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
	DECLARE p, primercompra, permanente VARCHAR( 25 );
	DECLARE respuesta, mes2 VARCHAR(25);
	DECLARE pc DATE;

	IF modelo = '90-SEMILLERO' THEN 	
		RETURN '09---';
	END IF;
	
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
		rol_codigos,
		IFNULL( JSON_UNQUOTE( JSON_EXTRACT( data, CONCAT( '$.permanentes."', modelo, '"' ) ) ), '' )
	INTO PTS, PTS_primer, primercompra, roles, permanente
	FROM t_usuarios
	WHERE id = socio;

	IF JSON_CONTAINS( roles, '"00-BLOQUEADO"', '$') THEN
		SELECT settings->>'$.calificacion_base' INTO respuesta from t_modelos WHERE codigo = modelo COLLATE utf8mb4_0900_ai_ci ;
		RETURN respuesta; 
	END IF;

	-- Calificaciones máximas en todos los modelos de negocio para socios con rol de PERMANENTE
	IF ( JSON_CONTAINS( roles, '"42-PERMANENTE"', '$') OR JSON_CONTAINS( roles, '"41-PERMANENTE-U"', '$') ) AND ( substring( permanente, 3, 1 ) != '-' OR substring( permanente, 4, 2 ) = '--' ) THEN
		CASE
		    WHEN modelo = '10-NUTRICION' THEN 
				RETURN '71-E';
			WHEN modelo = '20-TELEFONIA' THEN 
				RETURN '71-M50';
			WHEN modelo = '30-ALIMENTOS' THEN 	
				RETURN '13-OK';
			WHEN modelo = '40-GASOLINAS' THEN 	
				RETURN '54-G5';
			WHEN modelo = '50-INVERSION' THEN 	
				RETURN '15-INV';
		end case;
	END IF;

	-- PERMANENTE PERSONALIZADO
	IF substring( permanente, 3, 1 ) = '-' AND substring( permanente, 4, 2 ) != '--' then
		RETURN permanente;
	end if;


	if primercompra IS not NULL then

		CASE
		    WHEN modelo = '10-NUTRICION' THEN 		    
	
				if primercompra >= p then
					SET PTS = PTS_primer;
				END if;

                IF mes < 202510 THEN
                
	                -- parte de la migración es adecuar las calificaciones anteriores a las nuevas en OCTUBRE 2025
	
	                -- los basico se mantienen en basico
	                -- los BIEX se van a MASTER
	                -- los EJECUTIVO (BIEX) y PREMIERE se van a ELITE

                	set PTS = json_set( PTS, '$."010-DISTRIBUIDOR"', PTS->>'$."010-DISTRIBUIDOR"' + IF( PTS->>'$."030-PLUS"' > 2, 3, 0 ) + IF( PTS->>'$."010-DISTRIBUIDOR"' > 0, 3, 0 ) );
                END IF;

               
				  case 
				    when PTS->>'$."010-DISTRIBUIDOR"' >= 9 then
				      return '71-E';
				    when PTS->>'$."010-DISTRIBUIDOR"' >= 6 then
				      return '61-M';
				    when PTS->>'$."010-DISTRIBUIDOR"' >= 3 then
				      return '51-B';
				    when PTS->>'$."010-DISTRIBUIDOR"' > 0 then
				      return '01-C';	  
					else
					  return '01---';
				  end case;
		

		    WHEN modelo = '20-TELEFONIA' THEN 	
							
				if LENGTH( mes ) = 6 then
					SET mes = CAST( NOW() AS DATE );
				END if;
				
				SELECT ca.codigo INTO respuesta
				FROM t_pedidos pe
				left JOIN t_productos pr ON pr.codigo = JSON_UNQUOTE( JSON_EXTRACT( JSON_KEYS( pe.promociones->>'$."310-TELEFONIA".productos' ) , '$[0]' ) )
				left JOIN t_calificaciones ca ON 
					
					IF( SUBSTRING( pr.codigo, 5, 1 ) IN ( 'S', 'Q', 'M' ), 
						SUBSTRING( ca.codigo, 4, 3 ) = CONCAT( SUBSTRING( pr.codigo, 5, 1 ), SUBSTRING( pr.codigo, 9,2 ) ),
						SUBSTRING( ca.codigo, 1, 1 ) = pr.data->>'$.puntos."310-TELEFONIA"' AND SUBSTRING( ca.codigo, 3, 2 ) = '-M' 
					)
				
				WHERE pe.estatus_codigo = '420-PAGADO' AND pe.modelo_codigo = '20-TELEFONIA' AND pe.usuario_id = socio
				AND mes BETWEEN 
					CAST( pe.fechas->>'$.pagado' AS DATE ) AND 
					CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE )
				ORDER BY pr.data->'$.puntos."310-TELEFONIA"' DESC 
				LIMIT 1;
				
				if respuesta IS NOT NULL then
					RETURN respuesta;
				END if;	
				
				/*
				
				SELECT ca.codigo INTO respuesta
				FROM t_pedidos pe
				left JOIN t_productos pr ON pr.codigo = JSON_UNQUOTE( JSON_EXTRACT( JSON_KEYS( pe.promociones->>'$.\"310-TELEFONIA\".productos' ) , '$[0]' ) )
				left JOIN t_calificaciones ca ON SUBSTRING( ca.codigo, 4, 3 ) = CONCAT( SUBSTRING( pr.codigo, 5, 1 ), SUBSTRING( pr.codigo, 9,2 ) )
				WHERE pe.estatus_codigo = '420-PAGADO' AND pe.modelo_codigo = '20-TELEFONIA' AND pe.usuario_id = socio
				AND mes BETWEEN CAST( pe.fechas->>'$.pagado' AS DATE ) 
				AND CAST( DATE_FORMAT( CAST( pe.fechas->>'$.pagado' AS DATE ) + INTERVAL pr.data->>'$.dias' DAY, '%Y-%m-%d' ) AS DATE )
				ORDER BY pr.data->'$.puntos.\"310-TELEFONIA\"' DESC 
				LIMIT 1;
	
				if respuesta IS NULL then
				
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
				
				else
					RETURN respuesta;
				END if;
				
				*/

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
			
			WHEN modelo = '50-INVERSION' THEN 			
				RETURN "15-INV";

		END CASE;

	END if;

	SELECT settings->>'$.calificacion_base' INTO respuesta from t_modelos WHERE codigo = modelo COLLATE utf8mb4_0900_ai_ci ;
	RETURN respuesta;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_clabe
DELIMITER //
CREATE FUNCTION `f_get_clabe`(
	`_socio` MEDIUMINT,
	`_clabe` VARCHAR(18),
	`_fechanac` DATE
) RETURNS varchar(18) CHARSET utf8mb4
    DETERMINISTIC
BEGIN   
    DECLARE _nueva VARCHAR(18);
    
	IF TIMESTAMPDIFF( YEAR, _fechanac, CURDATE() ) BETWEEN 1 AND 17 THEN
		select u.data->>'$.clabe'
		into _nueva
		from t_usuarios u
		join t_usuarios s on s.id = _socio
		where u.id = s.redes->>'$.modelos."10-NUTRICION".padre';
		
		return _nueva;
	else
		return _clabe;
	END IF;
 
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_compras
DELIMITER //
CREATE FUNCTION `f_get_compras`(
	`input_socio` INT,
	`input_pts` VARCHAR(20)
) RETURNS int
    DETERMINISTIC
BEGIN
	DECLARE bDone, _id, _estatus, _nivel, _compras INT;
	declare _c202510, _c202511, _c202512, _c202601 decimal(7,2);
	DECLARE curs CURSOR FOR  
  
	WITH recursive cte (id, estatus, nivel, c202510, c202511, c202512, c202601 ) AS (
	   SELECT 
		 	u1.id, 
			SUBSTRING( u1.data->>'$.estatus.modelos."10-NUTRICION"', 1, 3 ),
			0,
			0,0,0,0
		FROM t_usuarios AS u1 
		WHERE u1.id = input_socio
		
	   UNION ALL
	    
		SELECT 
		 	u2.id,
			SUBSTRING( u2.data->>'$.estatus.modelos."10-NUTRICION"', 1, 3 ),
	    	cte.nivel + 1,
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202510"."010-DISTRIBUIDOR"', 0 ),
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202511"."010-DISTRIBUIDOR"', 0 ),
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202512"."010-DISTRIBUIDOR"', 0 ),
			IFNULL( historial->>'$.modelos."10-NUTRICION".calificaciones."202601"."010-DISTRIBUIDOR"', 0 )
		FROM cte
		JOIN t_usuarios AS u2 ON cte.id = IF( u2.redes->>'$.modelos."10-NUTRICION".padre' = 'null', NULL, u2.redes->>'$.modelos."10-NUTRICION".padre' )
	    WHERE cte.nivel < 3 
		AND cte.estatus > 200 
	)
	SELECT * FROM cte where estatus > 200;

	DECLARE CONTINUE HANDLER FOR NOT FOUND SET bDone = 1;

  OPEN curs;

	set _compras = 0;

  SET bDone = 0;
  REPEAT
    FETCH curs INTO _id, _estatus, _nivel, _c202510, _c202511, _c202512, _c202601;
	if _nivel > 0 then
		set _compras = _compras + IF(_c202510 >= input_pts, 1, 0 ) + IF(_c202511 >= input_pts, 1, 0 ) + IF(_c202512 >= input_pts, 1, 0 ) + IF(_c202601 >= input_pts, 1, 0 );
	end if;
  UNTIL bDone END REPEAT;

  CLOSE curs;
  
	update t_usuarios
	set historial = json_set( historial, '$.viajecancun', _compras ) where id = input_socio;
  
return _compras;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_consumo
DELIMITER //
CREATE FUNCTION `f_get_consumo`(
	`input_socio` INT,
	`input_mes` INT,
	`input_modelo` VARCHAR(20)
) RETURNS json
    DETERMINISTIC
BEGIN
	
	declare consumo DECIMAL(10,2) default 0.00;
	declare compras INT default 0;
	
	SELECT 
		sum( p.data->'$.total' ),
		count( * )
	into consumo, compras
	from t_pedidos p
	where p.modelo_codigo   = input_modelo
	and p.usuario_id = input_socio
	AND CAST( substring( p.estatus_codigo, 1, 3 ) AS UNSIGNED ) > 400
	and date_format( CAST( p.fechas->>'$.califica' AS date ), '%Y%m' ) = input_mes;



	RETURN JSON_OBJECT( 'consumo', consumo, 'compras', compras);
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
			JSON_ARRAY(f_get_calificacion(u1.id, m_1, modelo ), f_get_calificacion(u1.id, m_0, modelo ) ),
			IF( modelo = '50-INVERSION', u1.data->>'$.rango_inversion', JSON_UNQUOTE( JSON_EXTRACT( u1.redes, CONCAT('$.modelos."', modelo,'".rango' ) ) ) ),
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
			JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".padre' ) ) ),
			u2.redes->>'$.patrocinador',
			JSON_ARRAY( f_get_calificacion( u2.id, m_1, modelo ), f_get_calificacion( u2.id, m_0, modelo ) ),
			IF( modelo = '50-INVERSION', u2.data->>'$.rango_inversion', JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".rango' ) ) ) ),
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
	`_usuario` INT,
	`_niveles` INT
) RETURNS json
    DETERMINISTIC
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
        _calificaciones,-- puntos del socio listados por compras en el mes
        _permanentes
        JSON;

    DECLARE 
        _baja,          -- baja de sistema (activa solo cuando hay baja en todas las empresas)
        _inversion,     -- inversiones activas
        _k,             -- variable para ciclos while
        _i,             -- variable para ciclos while
        _bloqueos,
        _retiro
        INT default 0;

    DECLARE 
        _modelo,        -- modelo en turno (dentro de ciclo while)
        _estatus,       -- estatus del socio en el modelo actual
        _promocion,     -- revisar promociones base de una por una en promos para calificar en esa empresa
        _permanente,
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
        _arranque,
        _reset_inv
        DATE;

    DECLARE 
        _pts0, 
        _pts1, 
        _pts2,
        _directos_inv
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
        max( json_unquote( fecha ) )   
    into 
        _activos,
        _ultima
    from json_table( _modelos, '$[*]' 
		COLUMNS ( 
        	fecha JSON PATH '$.primer_compra'
		)
    ) _json; 
   
    -- Obtiene los datos esenciales del socio para calcular los estatus en cada unidad de negocio

	SELECT 
		u.rol_codigos, 
		u.historial,
		IFNULL( IF( u.data->>'$.saldo."50-INVERSION".USDT' = '', 0, u.data->>'$.saldo."50-INVERSION".USDT' ), 0 ),
		IFNULL( u.redes->>'$.verificado', 0 ),
		u.data->>'$.estatus.modelos',
		u.data->>'$.permanentes',
		u.historial->>'$.modelos."50-INVERSION".reset',
		CAST( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( u.historial, '$.registro' ) ), 1, 10 ) AS DATE)
 	INTO _roles, _historial, _directos_inv, _verificacion, _actuales, _permanentes, _reset_inv, _registro
 	FROM t_usuarios u
 	WHERE u.id = _usuario
	group by u.id 
	;
    
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


	    CASE
			WHEN _activos > 0  OR _ultima is not null THEN

			    -- Si la fecha de fecha de arranque de una empresa es mayor a la ultima activación
			    -- La fecha de arranque pasa a ser la fecha de activación
		
		    	IF _ultima < _arranque THEN
		    		set _ultima = _arranque; 
		    	END IF;
		    	
		    	-- Calculamos fecha de compresión definitiva en empresas donde se termina tiempo de gracia
		    
		        SET _fechabaja   = DATE_FORMAT( _ultima   + INTERVAL 184 DAY, '%Y-%m-%d' );

			WHEN _directos_inv > 0 THEN
    	
		    	-- Calculamos fecha de compresión definitiva en empresas donde se termina tiempo de gracia
		    
		        SET _fechabaja   = DATE_FORMAT( _registro   + INTERVAL 184 DAY, '%Y-%m-%d' );
	
		    ELSE
		        SET _fechabaja   = DATE_FORMAT( _registro + INTERVAL 92 DAY, '%Y-%m-%d' );
	    END CASE;
	    

		-- este loop nunca va a iterar, es solo para hacer el jump al resto de IFs
		final: LOOP

			-- Protegemos socios sin compras

			if JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].primer_compra' ) ) ) = 'null' then
				SET _primera = NULL; 
			ELSE
		    	SET _primera = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].primer_compra' ) ) );
			end if;

			-- verificamos que no exista calificación manual permanente para este modelo de negocio

			set _permanente = JSON_UNQUOTE( JSON_EXTRACT( _permanentes, CONCAT( '$."', _modelo, '"' ) ) );

            CASE
            
            -- WHEN JSON_CONTAINS( _roles, '"00-BLOQUEADO"', '$') THEN 
                
                -- manualmente con rol de bloqueado 
                /*******************************/
                -- SET _estatus = '110-ELIMINADO';
                /*******************************/
                -- LEAVE final; 

            WHEN JSON_CONTAINS( _roles, '"42-PERMANENTE"', '$') AND ( _permanente is null OR substring( _permanente, 4, 2 ) = '--' ) THEN 
                        
                -- rol de staff
                /*******************************/
                SET _estatus = '612-STAFF-PERMANENTE';
                /*******************************/
                LEAVE final;


            WHEN JSON_CONTAINS( _roles, '"41-PERMANENTE-U"', '$') AND ( _permanente is null OR substring( _permanente, 4, 2 ) = '--' ) THEN 
                        
                -- rol de staff
                /*******************************/
                SET _estatus = '520-CALIFICADO-ACTUAL';
                /*******************************/
                LEAVE final;
								   
				          
            WHEN _verificacion = 2025 AND _modelo = '10-NUTRICION' THEN 
                
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

			
			-- Bloqueo manual
			if JSON_UNQUOTE( JSON_EXTRACT( _actuales, CONCAT( '$."', _modelo, '"' ) ) ) = '110-ELIMINADO' then

				SET _bloqueos = _bloqueos + 1;
				
                /*******************************/
                SET _estatus = '110-ELIMINADO';
                /*******************************/
                LEAVE final;
            
            else
            
            	-- calificación permanente personalizada por modelo
            	
            	if _permanente is not null and substring( _permanente, 4, 2 ) != '--' then
	                /*******************************/
	                SET _estatus = '520-CALIFICADO-ACTUAL';
	                /*******************************/
	                LEAVE final;
            	end if;
			end if;
			

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
 
 				-- para compatibilidad entre esquemas de calificación
 				
                 IF _mes0 = 202510 THEN
                	set _pts1 = _pts1 * 3;
                	set _pts2 = _pts2 * 3;
                END IF;

                IF _mes0 = 202511 THEN
                	set _pts2 = _pts2 * 3;
                END IF;
                
                -- Si tiene compras
                
				IF _primera IS NOT NULL THEN

                    -- Si tiene compras en mes actual
                    IF _pts0 >= 3 THEN

                        -- Si es su primer compra/mes
                        IF DATE_FORMAT( _primera, '%Y%m' ) = _mes0 THEN
                        
                            -- nuevo registrado en los ultimos 30 días, con compras	
                            /*******************************/
                            SET _estatus = '510-NUEVO-CALIFICADO';
                            /*******************************/
                            LEAVE final;

                        ELSE
                            IF _pts1 >= 3 THEN

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
                        IF _pts1 >= 3 THEN

							-- con compras en el mes anterior, pero sin compras en mes actual
                            /*******************************/
							SET _estatus = '410-CALIFICADO';
                            /*******************************/
							LEAVE final;

                        -- Si no tiene compras en mes actual ni en mes anterior
                        ELSE

                            -- Si tiene compras en mes anterior al anterior
                            IF _pts2 >= 3 THEN
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

								-- checamos si tiene puntos para cliente preferente}
								IF  ( _pts0 + _pts1 ) > 0 THEN
									-- es cliente preferente
									/*******************************/
									SET _estatus = '309-CLIENTE';
									/*******************************/
									LEAVE final;

								ELSE
									-- no tiene compras en los ultimos 3 meses
									/*******************************/
									SET _estatus = '140-SUSPENDIDO';
									/*******************************/
									LEAVE final;

	                            END IF;	
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
                _    _     ___ __  __ _____ _   _ _____ ___  ____  
               / \  | |   |_ _|  \/  | ____| \ | |_   _/ _ \/ ___| 
              / _ \ | |    | || |\/| |  _| |  \| | | || | | \___ \ 
             / ___ \| |___ | || |  | | |___| |\  | | || |_| |___) |
            /_/   \_\_____|___|_|  |_|_____|_| \_| |_| \___/|____/ 
                                                                                                    
            **************************************************************************************
            */

            when _modelo = '30-ALIMENTOS' then      
				
				-- [["021-INICIAL", "011-DISTRIBUIDOR"]]
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
             ___ _   ___     _______ ____  ____ ___ ___  _   _ 
            |_ _| \ | \ \   / / ____|  _ \/ ___|_ _/ _ \| \ | |
             | ||  \| |\ \ / /|  _| | |_) \___ \| | | | |  \| |
             | || |\  | \ V / | |___|  _ < ___) | | |_| | |\  |
            |___|_| \_|  \_/  |_____|_| \_\____/___\___/|_| \_|
                                                    
            **************************************************************************************
            */

            when _modelo = '50-INVERSION' then      

                -- extraer info para saber si tiene inversiones activas
                
                SELECT sum(ifnull(i.cantidad, 0 )) - sum(ifnull(r.cantidad, 0)) into _inversion
                from t_inversiones i
                left join t_retiros r on r.inversion_id = i.id and r.estatus_codigo = '421-APLICADO' and r.tipo in ( 'STOTAL', 'SPARCIAL' )
                where i.estatus_codigo = '625-ACTIVA'
                and i.usuario_id = _usuario
                and curdate() < i.fechas->>'$.cierre';
                
/*
                SELECT count(*) into _inversion 
                from t_inversiones
                where estatus_codigo = '625-ACTIVA'
                and usuario_id = _usuario
                and curdate() < fechas->>'$.cierre';
*/

                -- Si tiene compras
                IF _primera IS NOT NULL THEN
                
                    IF _inversion THEN
                        -- con compras en mes anterior y actual 
                        /*******************************/
                        SET _estatus = '520-CALIFICADO-ACTUAL';
                        /*******************************/
                        LEAVE final;
                        
                    -- Si no tiene capital semilla
                    ELSE
                        SELECT fechas->>'$.mes' into _retiro
                        from t_retiros 
                        where estatus_codigo = '421-APLICADO' 
                        and tipo in ( 'STOTAL', 'SPARCIAL' ) 
                        and usuario_id = _usuario;

                        IF _retiro = DATE_FORMAT( DATE_SUB( CAST( NOW() AS DATE ), INTERVAL 1 MONTH ), '%Y%m' ) THEN
                            -- Es su mes de gracia para reactivarse
                            /*******************************/
                            SET _estatus = '310-NO-CALIFICADO';
                            /*******************************/
    
                            LEAVE final;

                        -- Si su mes de gracia ya terminó               
                        ELSE
                            -- no tiene compras activas
                            /*******************************/
                            SET _estatus = '140-SUSPENDIDO';
                            /*******************************/
                            LEAVE final;
                        END IF;	

                    END IF;	
                
                -- Si nunca ha comprado
                ELSE
                	IF _directos_inv > 0 then
            	        SET _fechabaja   = DATE_FORMAT( _registro + INTERVAL IF( _usuario = 164683, 8, 6) MONTH - INTERVAL 1 DAY, '%Y-%m-%d' ); -- _reset_inv
            	        -- return json_array( _fechabaja );
            	    end if;

                    IF _fechabaja > CAST( NOW() AS DATE ) or _usuario = 61172 THEN
						
						IF _directos_inv > 0 THEN
	                        -- registrado dentro de tiempo de gracia, aun sin compras
	                        /*******************************/
	                        SET _estatus = '215-NUEVO-SALDO';
	                        /*******************************/
	                        LEAVE final;	
						ELSE                                
	                        -- registrado dentro de tiempo de gracia, aun sin compras
	                        /*******************************/
	                        SET _estatus = '210-NUEVO';
	                        /*******************************/
	                        LEAVE final;	
	                    END IF;

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

		IF SUBSTRING( _estatus, 1, 3 ) > 200 THEN
			
            -- Si está activo en alguna empresa, se cancela la baja de cuenta

			SET _baja = 0;		 	
		END IF;
                
        SET _k = _k + 1;
    END WHILE;

	-- Chequeo para eliminar cuentas que no tengan empresas activas
	-- sin importar cuanto tiempo de gracia lleven
	
	
	IF curdate() > DATE_FORMAT( _registro + INTERVAL 31 DAY, '%Y-%m-%d' ) THEN
	
	    -- Buscamos estatus activos
	
		SET _k = 0;
	    SET _estatus = true;
	
		WHILE _k < JSON_LENGTH( _modelos ) DO
	
	            -- obtener parametros de modelo
	            SET _modelo  = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].codigo' ) ) );
	
				-- Si existe al menos una red activa, cancelamos reset general
	            IF SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( _estatuses, CONCAT( '$."', _modelo, '"' ) ) ), 1, 3 ) > IF( _modelo = '50-INVERSION' AND _directos_inv > 0, 200, 300 ) THEN 
	                SET _estatus = false;
	            END IF;
	
	        SET _k = _k + 1;
	    END WHILE;
	
	    -- Eliminamos estatus nuevos cuando no hay empresas activas (reset general)
	
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

	-- ahora que sabemos que los estatus ya no cambiarán
	-- Aplicamos el reset a las redes inactivas

    SET _k = 0;

    WHILE _k < JSON_LENGTH( _modelos ) DO

            -- obtener parametros de modelo
            SET _modelo = JSON_UNQUOTE( JSON_EXTRACT( _modelos, CONCAT( '$[', _k, '].codigo' ) ) );

            IF SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( _estatuses, CONCAT( '$."', _modelo, '"' ) ) ), 1, 3 ) < 200 OR JSON_UNQUOTE( JSON_EXTRACT( _estatuses, CONCAT( '$."', _modelo, '"' ) ) ) = '309-CLIENTE' THEN
            
                -- Comprimir red de manera permanente
	            -- Función f_compresion_de_red está pendiente de revisión
	
	            -- DO f_compresion_de_red( _usuario, _modelo ); <---- IMPORTANTE AQUI - PENDIENTE
				
				-- Aplicar un reset local a modelo de negocios

				UPDATE t_usuarios u 
	            SET u.historial = JSON_SET( u.historial, CONCAT( '$.modelos."', _modelo, '".reset' ), CURDATE() ) 
	            WHERE u.id = _usuario;
	            
		        -- bitacora marcar baja
		        /*
		        if _actuales->>'' != then
			        INSERT into t_bitacoras 
					values( NULL, 103, _usuario, now(), json_object( 'modelo', _modelo ), '0.0.0.0');
	            end if;
	            */
				
				CASE  
					when _modelo = '10-NUTRICION' then      
				        -- cancelar estrellas
						UPDATE t_usuarios u 
				        SET u.data = JSON_SET(  u.data, '$.recompensas.inicia', curdate() ) 
				        WHERE u.id = _usuario;
				        
					/*
					when _modelo = '50-INVERSION' then
						-- eliminar saldo no utilizado
						update t_usuarios 
						set data = json_set( data, '$.saldo."50-INVERSION".USDT', 0 ) 
						where id = _usuario;
						*/
					else
						BEGIN END;
				end case; 

            END IF;

        SET _k = _k + 1;
    END WHILE;


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

    -- si tiene bloqueo en todas las redes, lo marcamos como BLOQUEADO

    UPDATE t_usuarios 
    SET 
		data = json_set( data, '$.estatus.migrated', curdate() ),
		estatus_codigo = IF( 
        _baja = 1 AND _usuario > 60, 
        '120-BAJA', 
        '201-ACTIVO'
    	),
    	rol_codigos = IF( JSON_LENGTH( rol_codigos ) = 1, json_set( rol_codigos, '$', JSON_ARRAY( IF( JSON_LENGTH( _modelos ) = _bloqueos, '00-BLOQUEADO', '10-SOCIO' ) ) ), rol_codigos )
    WHERE id = _usuario;

	-- Enviamos respuesta
	
    RETURN _estatuses;

END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_factor_promos
DELIMITER //
CREATE FUNCTION `f_get_factor_promos`(`socio` MEDIUMINT, `mes` VARCHAR(6)) RETURNS decimal(8,2)
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
		join t_pedidos  p on p.id = c.pedido_id
		JOIN t_esquemas e ON e.codigo = c.esquema_codigo
	WHERE 
		SUBSTRING( c.estatus_codigo, 1, 3) > 200 
		AND SUBSTRING( p.estatus_codigo, 1, 3) > 400 
		AND e.modelo_codigo = modelo
		AND c.usuario_id = usuario
		AND CONCAT( substring(c.fecha, 1, 4), substring(c.fecha, 6, 2)) = mes
		AND e.settings->>'$.periodo' IN ( 'MENSUAL', 'SEMANAL', 'ANUAL');
		
	return CAST( IFNULL( resultado, 0 ) AS DECIMAL(10,2) );
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_inventario
DELIMITER //
CREATE FUNCTION `f_get_inventario`(`input_almacen` VARCHAR(20)) RETURNS json
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

-- Dumping structure for function vpsbeneleitmx_app.f_get_last_rojo
DELIMITER //
CREATE FUNCTION `f_get_last_rojo`(
	`input_socio` INT,
	`input_year` INT
) RETURNS int
    DETERMINISTIC
BEGIN
	
	DECLARE json_data JSON;
	declare m DATE;
	declare respuesta, i, mes, pts, a INT;

	
	-- obtenemos calificaciones del año
	SELECT JSON_OBJECTAGG( mesc, ptsc ) 
	into json_data 
	from (
		SELECT 
			date_format( p.fechas->>'$.califica', '%Y%m' ) as mesc,
			cast( sum( p.PTS->'$."010-DISTRIBUIDOR"' ) as decimal ) as ptsc
		from t_pedidos p
		where p.usuario_id = input_socio
		and p.modelo_codigo = '10-NUTRICION'
		and SUBSTRING( p.estatus_codigo, 1, 3 ) > 400
		and date_format( p.fechas->>'$.califica', '%Y%m' ) between ( ( input_year - 1 ) * 100 ) + 9 and ( input_year * 100 ) + 8
		group by date_format( p.fechas->>'$.califica', '%Y%m' )
	) ca;
	

	SET i = 0;
	set m = cast( CONCAT( YEAR( CURDATE() ), '-08-01'  ) as date );
	set mes = NULL;
	
	-- buscamos meses en rojo 
	WHILE i < 12 DO
		set pts = IFNULL( JSON_EXTRACT( json_data, CONCAT( '$."', date_format( m, '%Y%m' ) ,'"' ) ), 0 );
	    IF pts < 1 THEN
		    RETURN mes;
		ELSE
	    	set mes = date_format( m, '%Y%m' );
	    END IF;
		set m = DATE_SUB( m, INTERVAL 1 MONTH);
	    SET i = i + 1;
	END WHILE;

	RETURN mes;
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
	
	if( true or mes = DATE_FORMAT( NOW(), '%Y%m' ) ) then
	
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
	`_usuario` INT,
	`_modelo` VARCHAR(20)
) RETURNS mediumint
    DETERMINISTIC
BEGIN
	DECLARE 
		_padre, 
		_estatus,
		_padre_actual,
		_patrocinador
		MEDIUMINT DEFAULT 0;
		
		declare t JSON default json_array();
		
	DECLARE 
		_reset_padre,
		_reset_usuario,
		_arranque
		DATE;


	SELECT 
		cast( IFNULL( json_unquote( json_extract( historial, concat( '$.modelos."', _modelo, '".reset' ) ) ), historial->>'$.reset' ) as date ),
		m.settings->>'$.fecha_arranque',
		json_unquote( json_extract( redes, concat( '$.modelos."', _modelo, '".padre' ) ) ),
		json_unquote( json_extract( redes, concat( '$.modelos."', _modelo, '".patrocinador' ) ) )
	into _reset_usuario, _arranque, _padre_actual, _patrocinador
	from t_usuarios
	join t_modelos m on m.codigo = _modelo
	where id = _usuario;
	
	IF _reset_usuario < _arranque THEN 
		SET _reset_usuario = _arranque;
	END IF;

	set _reset_padre = _reset_usuario;
	
    WHILE ( _estatus < 200 OR ( _reset_padre > _reset_usuario /* AND _padre_actual != _patrocinador */ )  ) AND _usuario > 0 AND _usuario IS NOT NULL DO
	
		SELECT 
			IFNULL( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( p.data, CONCAT( '$.estatus.modelos."', _modelo, '"' ) ) ), 1, 3 ), 612 ),
			IFNULL( p.id, 0 ),
			cast( IFNULL( json_unquote( json_extract( p.historial, concat( '$.modelos."', _modelo, '".reset' ) ) ), IFNULL( p.historial->>'$.reset', _arranque ) ) as date )
		INTO _estatus, _padre, _reset_padre
		FROM t_usuarios u
		LEFT JOIN t_usuarios p ON p.id = IF( 
			json_unquote( json_extract( u.redes, CONCAT('$.modelos."', _modelo, '".patrocinador') ) ), 
			json_unquote( json_extract( u.redes, CONCAT('$.modelos."', _modelo, '".patrocinador') ) ), 
			u.redes->>'$.patrocinador' )
		WHERE u.id = _usuario;
		
		IF _reset_padre < _arranque THEN 
			SET _reset_padre = _arranque;
		END IF;

		SET _usuario = _padre;
		
    END WHILE;

	--	return json_array( _usuario, _padre, _reset_padre, _reset_usuario, _padre_actual, _patrocinador );    
    -- Return ultimo padre (activo, con fecha de reset anterior a la del socio )

	if _padre_actual != _usuario then
		set _padre = 1;
	END IF;


    RETURN _usuario;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_semilla
DELIMITER //
CREATE FUNCTION `f_get_semilla`(
	`socio` INT,
	`mes` INT,
	`receptor` INT
) RETURNS decimal(11,2)
    DETERMINISTIC
BEGIN

	declare _semilla int;

	select sum( i.cantidad ) into _semilla
	from
	t_inversiones i
	join t_pedidos p on p.id = i.pedido_id
	where i.usuario_id = socio
	and substring( p.estatus_codigo, 1, 3 ) > 400
	and substring( i.estatus_codigo, 1, 3 ) > 200
    and json_unquote( json_extract( json_keys( p.promociones->>'$."510-SEMILLA".productos' ), '$[0]' ) ) in ( '603-CAPITAL-3',  '606-CAPITAL-6',  '609-CAPITAL-9', IF( receptor in ( 49775, 25918 ), '606-CAPITAL-6A', '' ) )
	and date_format( p.fechas->>'$.pagado', '%Y%m' ) < mes;
	
	/*

	DECLARE _meses, _inversiones, _inversion, temp JSON;
	declare _k int default 0;
	DECLARE _semilla, _cantidad decimal(10,2) default 0;
	DECLARE _invertido, _pagado DATE;

	DECLARE _done BOOL DEFAULT FALSE;
	DECLARE _cursor CURSOR FOR 
		SELECT i.extras->'$.meses', i.fechas->>'$.inversion', i.fechas->>'$.pagado', i.cantidad 
		FROM t_inversiones i
		join t_pedidos p on p.id = i.pedido_id
		WHERE i.usuario_id = socio 
		AND i.estatus_codigo = '625-ACTIVA'
		and substring( p.estatus_codigo, 1, 3 ) > 400;
	
	DECLARE continue handler for not found set _done = TRUE;
			
	OPEN _cursor;
	set temp = json_array();
	LOOP_esquemas: loop
		
		fetch _cursor into _meses, _invertido, _pagado, _cantidad;

		if _done then
			close _cursor;
			leave LOOP_esquemas;
		END if;

		IF date_format( _pagado, '%Y%m' ) < date_format( _invertido, '%Y%m' ) AND date_format( _pagado, '%Y%m' ) = mes THEN
			return _cantidad;
		END IF;

		set _k = 0;
		WHILE _k < JSON_LENGTH( _meses ) DO
	
            -- obtener detalles del mes
            
			SET _inversion  = JSON_UNQUOTE( JSON_EXTRACT( _meses, CONCAT( '$[', _k, ']' ) ) );

			
            IF _inversion->>'$.Ym' = mes and mes <= date_format( now(), "%Y%m" ) THEN
			    set _semilla = _semilla + _inversion->>'$.semilla';
			  
            END IF;
	
	        SET _k = _k + 1;
	    END WHILE; 
	    
	end loop;
	
	*/

    return _semilla;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_get_tipocuenta
DELIMITER //
CREATE FUNCTION `f_get_tipocuenta`(
	`_sat` SMALLINT,
	`_fechanac` DATE
) RETURNS varchar(25) CHARSET utf8mb4
    DETERMINISTIC
BEGIN
	CASE
	    WHEN _sat > 0 THEN 
			return 'FACTURA';
	    WHEN STR_TO_DATE( _fechanac, '%Y-%m-%d') > DATE_SUB( NOW(), INTERVAL 18 YEAR ) THEN 
			return 'MENOR';
	    ELSE 
			return 'REGULAR';
	END CASE;
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
			IF( JSON_UNQUOTE( JSON_EXTRACT( u1.redes, CONCAT('$.modelos."', modelo,'".padre') ) ) is null or JSON_UNQUOTE( JSON_EXTRACT( u1.redes, CONCAT('$.modelos."', modelo,'".padre') ) ) = 'null', f_get_padre( u1.id, modelo ), JSON_UNQUOTE( JSON_EXTRACT( u1.redes, CONCAT('$.modelos."', modelo,'".padre') ) ) ),
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
			IFNULL( JSON_UNQUOTE( JSON_EXTRACT( u2.redes, CONCAT('$.modelos."', modelo,'".padre') ) ), f_get_padre( u2.id, modelo ) ),
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

-- Dumping structure for function vpsbeneleitmx_app.f_get_verificacion
DELIMITER //
CREATE FUNCTION `f_get_verificacion`(
	`_usuario` INT,
	`_modelo` VARCHAR(25)
) RETURNS json
    DETERMINISTIC
BEGIN

	DECLARE _tempo, _puntos, _llaves, _data JSON;
	DECLARE _j, _porcentaje, _verificados, _total INT DEFAULT 0;
	DECLARE _tipo, _llave, _path VARCHAR(150);
	DECLARE _requerido, _valor, _permanente BOOLEAN;
	DECLARE _fechanac DATE;
	
	-- obtenemos info del modelo
	
	set _permanente = 0;
	
	SELECT 
		m.settings->'$.verificaciones' 
	into _puntos 
	FROM t_modelos m
	WHERE m.codigo = _modelo;
	
	-- obtenemos info del socio
	
	SELECT 
		u.data,
		IF( u.rol_codigos like '%42-PERMANENTE"%', 1, 0 ),
		cast( u.fechanac as date ) 
	into _data, _permanente, _fechanac
	FROM t_usuarios u
	WHERE u.id = _usuario;
	
	-- inicializamos objeto de respuesta
	
	SET _tempo  = JSON_OBJECT();
	
	-- definimos tipo de cuenta

	set _tipo   = f_get_tipocuenta( _data->'$.sat.estatus', _fechanac );
	set _puntos = json_extract( _puntos, CONCAT( '$."', _tipo,'"' ) );
	set _llaves = JSON_KEYS( _puntos );
	SET _j 		= 0;	
	
	-- crear bloque si no existe
	
	IF JSON_EXTRACT( _data, '$.verificaciones' ) IS NULL THEN
		UPDATE t_usuarios
		SET data = JSON_SET( data, '$.verificaciones', json_object() )
		WHERE id = _usuario;
	END IF;

    -- revisión de puntos

	WHILE _j < JSON_LENGTH( _llaves ) DO
		SET _llave = JSON_UNQUOTE( JSON_EXTRACT( _llaves, CONCAT( '$[', _j, ']' ) ) );
		SET _path = CONCAT( '$.verificaciones."', _llave, '"' );
	
		-- Si no existe el punto en el data del socio (auto adición)
		
		IF JSON_EXTRACT( _data, _path ) IS NULL THEN
			UPDATE t_usuarios
			SET data = JSON_SET( data, _path, false )
			WHERE id = _usuario;
		END IF;
	
		-- si el punto de verificación es requerido para este tipo de cuenta
		 
		if JSON_EXTRACT( _puntos, CONCAT( '$."', _llave, '"' ) ) THEN
			SET _valor = JSON_EXTRACT( _data, _path );
			SET _tempo = JSON_SET( _tempo, CONCAT( '$."', _llave, '"' ), _valor );

			if _valor OR _permanente THEN
				set _verificados = _verificados + 1;
			END if;
			
			SET _total = _total + 1;
		end if;
	
	    SET _j = _j + 1;
	END WHILE;

	SET _porcentaje = IF( _total, CEIL( _verificados * 100 / _total ), 0 );
	
	RETURN JSON_OBJECT( "tipo", _tipo, "puntos", _tempo, "porcentaje", _porcentaje, "estatus", if( _porcentaje = 100, TRUE, FALSE ) );
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
		'51' , 3,
		'61' , 6,
		'71' , 99
	);
	
	SET p = JSON_object();
	
	-- Extracción de datos de pedido, socio y upline
	SELECT pedido.modelo_codigo, pedido.promociones->'$."010-DISTRIBUIDOR".productos'
	INTO   modelo, productos
    FROM   t_pedidos pedido
    JOIN   t_modelos modelo ON modelo.codigo = pedido.modelo_codigo
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
	`input_producto` VARCHAR(10),
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
	
	select p.precio->>'$.reparte' into nuevas
	from t_productos p
	where p.data->>'$.puntos."310-TELEFONIA"' = SUBSTRING( input_calificacion, 1, 1)
	and p.codigo like '%-MES-%';	 

    -- si la compra es mayor a la calificación, aplicar rasurado
    
	if input_comision > JSON_EXTRACT( nuevas, CONCAT( '$[', input_nivel - 1, ']' ) ) then
		RETURN JSON_EXTRACT( nuevas, CONCAT( '$[', input_nivel - 1, ']' ) );
	END if;
	
	RETURN input_comision;
	
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_reparte_comisiones
DELIMITER //
CREATE FUNCTION `f_reparte_comisiones`(
	`input_pedido` INT,
	`en_corte` TINYINT
) RETURNS json
    DETERMINISTIC
BEGIN

	-- declaración de variables
	DECLARE v_promociones, v_PTS, promocion, v_upline, checks, tempo JSON;
	DECLARE beneficiados, temporal JSON DEFAULT JSON_ARRAY();
	DECLARE v_fechareparte, lunes, v_corte, v_primercompra, dia_primero, dia_ultimo DATE;
	DECLARE v_modelo, v_periodo, nuevo_periodo, estatus_final, _inversion VARCHAR(25);
	DECLARE v_usuario, v_estatus, compresion, l, primercompra, v_productos, gasolinas, v_activo, v_pc, v_pc_pedido, anterior, jj INT;
	DECLARE existe BOOL DEFAULT FALSE;
	DECLARE v_USDT, _acumulado DECIMAL(8,2) DEFAULT 0;

	DECLARE m_2, m_1, m_0, tercermes, primermes MEDIUMINT;

	-- Extracción de datos de pedido, socio y upline
	SELECT
        pedido.promociones,
        modelo.codigo COLLATE utf8mb4_0900_ai_ci, 
        cast( pedido.fechas->>'$.reparte' as date ), 
        pedido.usuario_id,
        SUBSTRING( pedido.estatus_codigo, 1, 3 ) COLLATE utf8mb4_0900_ai_ci,
        pedido.fechas->>'$.corte',
        IF( pedido.data->>'$.primercompra' = 'null', null, pedido.data->>'$.primercompra' ),
        pedido.PTS,
        pedido.data->'$.productos',
        modelo.settings->>'$.periodo',
        IFNULL( usuario.data->>'$.saldo."50-INVERSION".USDT', 0 ),
        substring( json_unquote( json_Extract( usuario.data, concat( '$.estatus.modelos."', modelo.codigo, '"' ) ) ), 1, 3 )
    INTO  
        v_promociones, v_modelo, v_fechareparte, v_usuario, v_estatus, v_corte, v_pc, v_PTS, v_productos, v_periodo, v_USDT, v_activo
    FROM  
	 	 t_pedidos  pedido
    JOIN t_modelos  modelo  ON modelo.codigo COLLATE utf8mb4_0900_ai_ci = pedido.modelo_codigo COLLATE utf8mb4_0900_ai_ci
    JOIN t_usuarios usuario ON usuario.id = pedido.usuario_id
    WHERE 
        pedido.id = input_pedido; 
	
	SET v_primercompra = CAST( f_fecha_primercompra( v_usuario, v_modelo ) AS DATE);
	

	-- -------------------------------------
	-- verifica primer compra
	-- si no tiene bandera recalcula banderas para los pedidos del modelo
		
	IF v_pc IS NULL THEN
		
		update t_pedidos set data = json_set( data, '$.primercompra', 0) where modelo_codigo = v_modelo and usuario_id = v_usuario;

		UPDATE t_pedidos p
		JOIN (
		    SELECT 
		        MIN( cast( fechas->>'$.pagado' as date ) ) AS fecha_primera
		    FROM t_pedidos
		    where substring( estatus_codigo, 1, 3) > 400
		    and modelo_codigo = v_modelo
		    and usuario_id = v_usuario
		) primera
		ON p.usuario_id = v_usuario AND cast( p.fechas->>'$.pagado' as date ) = primera.fecha_primera
		SET data = json_set( p.data, '$.primercompra', 1 )
		where substring( p.estatus_codigo, 1, 3) > 400 
		and p.modelo_codigo = v_modelo;
		
		SELECT
	        pedido.data->>'$.primercompra'
	    INTO  
	        v_pc
	    FROM  
		 	 t_pedidos  pedido
	    WHERE 
        pedido.id = input_pedido; 		
			
	END IF;

	-- calculo de acumulado invertido en als ultimas 4 semanas

	IF v_modelo = '50-INVERSION' THEN
		set _inversion = json_unquote( json_extract( json_keys( json_extract( v_promociones, '$."510-SEMILLA".productos' ) ), '$[0]' ) );
	
		select sum( cantidad ) into _acumulado
		from t_inversiones
		where usuario_id = v_usuario
		and estatus_codigo = '625-ACTIVA'
		and producto_codigo = _inversion
		and date_add( cast( fechas->>'$.pagado' as date ), INTERVAL 4 WEEK ) > cast( now() as date );	
				
	END IF;
	
	-- -------------------------------------	

	set dia_primero = DATE_FORMAT( v_fechareparte, '%Y-%m-01');
	set dia_ultimo  = DATE_FORMAT( LAST_DAY( v_fechareparte ), '%Y-%m-%d');

	set m_2 = DATE_FORMAT(LAST_DAY( LAST_DAY( v_fechareparte ) - INTERVAL 3 MONTH ) + INTERVAL 1 DAY, '%Y%m');		
	set m_1 = DATE_FORMAT(LAST_DAY( LAST_DAY( v_fechareparte ) - INTERVAL 2 MONTH ) + INTERVAL 1 DAY, '%Y%m');		
	set m_0 = DATE_FORMAT( v_fechareparte, '%Y%m' );
		
	set tercermes = DATE_FORMAT( LAST_DAY( LAST_DAY( v_primercompra ) + INTERVAL 1 MONTH ) + INTERVAL 1 DAY, '%Y%m' );
	set primermes = DATE_FORMAT( v_primercompra, '%Y%m' );

	SET v_upline  =  f_get_upline( v_usuario, v_modelo COLLATE utf8mb4_0900_ai_ci, 1, v_fechareparte );
	
	-- validar que no haya gente en GRIS
	
	SET jj = 0;	
	set anterior = v_usuario;
	
	WHILE jj < JSON_LENGTH( v_upline ) DO
		SET tempo = JSON_EXTRACT( v_upline, CONCAT( '$[',jj,']' ) );

		IF substring( tempo->>'$.estatus', 1, 3 ) < 200 THEN
			call p_update_padre( anterior, v_modelo ); 
			SET v_upline  =  f_get_upline( v_usuario, v_modelo COLLATE utf8mb4_0900_ai_ci, 1, v_fechareparte );
		END IF;
	
		set anterior = tempo->>'$.id';
	    SET jj = jj + 1;
	END WHILE;		

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
	DELETE from t_comisiones where pedido_id = input_pedido and esquema_codigo != '520-SALDO' 
		AND substring( estatus_codigo, 1, 3 ) < 300;		
	
	-- Repasamos todos los esquemas de comisiones habilitados para ese modelo de negocio
	esquemas: BEGIN
		-- Obtenemos el puntero con los esquemas 
		DECLARE v_esquema, llave VARCHAR(20);
		DECLARE tura VARCHAR(200);
		DECLARE activos_fecha, fecha_pago DATE; 
		DECLARE v_settings, socio, v_nivel, step, llaves, ll_prods, prods, niveles, tmp JSON;
		DECLARE nivel, i, j, k, calificacion, comprastercer, activos, q, estrellas, total_estrellas, cantidad, tp INT;
		DECLARE comision, ctem, comisionable, bolsa, bolsa2, rebaja, prec, vaso DECIMAL(8,2) DEFAULT 0.00;
		DECLARE aplica, paga, done, duplicado BOOL DEFAULT FALSE;
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
					SET niveles   = JSON_EXTRACT( v_settings, '$.niveles' );
			
					SET j = 0;	
					WHILE j < JSON_LENGTH( niveles ) DO
						SET niveles = JSON_SET( niveles, CONCAT( '$[', j, ']' ), gasolinas * JSON_EXTRACT( niveles, CONCAT( '$[', j, ']' ) ) );
					
				    	SET j = j + 1;
					END WHILE;
					
				WHEN v_esquema = '510-INVERSION' THEN
					SELECT JSON_KEYS( v_promociones, '$."510-SEMILLA".productos' ) into prods;
					SELECT precio->>'$.reparte' INTO niveles FROM t_productos WHERE codigo = prods->>'$[0]';
					
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
					SET calificacion = IFNULL( SUBSTRING( JSON_UNQUOTE( JSON_EXTRACT( socio, CONCAT( IF( v_esquema = '210-TELEFONIA' OR v_esquema = '220-TELEFONIA-1ER', '$.calificaciones[1]', '$.calificaciones[0]' ) ) ) ) ,1, 2), 0 );
					/* or v_esquema = '410-GAS' or v_esquema = '412-GAS-180' */

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
						
					/*
					ELSE
						
						if v_esquema = '120-BIEX-3ER-NIVEL' THEN
					
							-- no basta con ser EJECUTIVO (biex+plus) 
							-- Tambien se debe tener un paquete de telefonía de 30 días activo					
							
							if substring( f_get_calificacion( socio->'$.id', v_fechareparte, '20-TELEFONIA'), 4, 1 ) != 'M' THEN
								SET paga = 0;
								
								return json_array("no");
							end if;
						END IF;											
						*/
					 
					END IF; 								

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
							    		
							    		-- se cancela el metodo de solo invresiones mayores a 10K
							    		-- if 	v_promociones->'$."510-SEMILLA".precio' > 10000 THEN
							    		
							    		-- ahora se implementa con inversiones que superen los 10K en acumulado con las inversiones de las ultimas 4 semanas

							    		if _acumulado > 10000 and input_pedido not in ( 1266261 ) THEN
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
												AND ll_prods not IN ('230-BNOX', '170-BMEL', '310-FRSH', '206-CITR', '330-BOFF') 
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
											--	'220-TELEFONIA-1ER' = v_esquema COLLATE utf8mb4_0900_ai_ci OR
												'210-TELEFONIA' = v_esquema COLLATE utf8mb4_0900_ai_ci 
											)
										) THEN

  											-- return json_array(  nivel, step, calificacion, prods->>'$[0]', v_esquema );
											-- [1, 11, 21, "774-MES-35", "210-TELEFONIA"]
											
											SET rebaja   = f_rasura_movil( nivel, step, calificacion, prods->>'$[0]', v_esquema );
											SET comision = rebaja;
											SET bolsa    = step - rebaja;

											if false and nivel = 4 then										
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
								if ( v_esquema COLLATE utf8mb4_0900_ai_ci = '110-SINERGY' OR v_esquema COLLATE utf8mb4_0900_ai_ci = '112-SINERGY-180' ) THEN
									SET prec 		 = comision;
						    		SET comisionable = f_rasura_comision( input_pedido, calificacion );	  
  		
						    		SET ctem         = step * comisionable / 100;
						    		SET bolsa        = comision - ctem;
						    		SET comision     = ctem;
						    		
						    		
-- return json_array( prec, comision, comisionable, calificacion, ctem, bolsa);
						    		
					    		END if;
					    		
								if false AND ( v_esquema COLLATE utf8mb4_0900_ai_ci = '410-GAS' OR v_esquema COLLATE utf8mb4_0900_ai_ci = '412-GAS-180' ) THEN
									SET prec 		 = comision;
						    		SET comisionable = f_rasura_gas( input_pedido, calificacion );
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
						    
						    
						    	IF prods->>'$[0]' = '606-CAPITAL-6A' and nivel_real > 4 THEN
						    		set nivel_real = nivel_real - 5;
						    	END IF;
						    
						    
						    	-- CREAR COMISION PARA PAGO EN CORTE 
							    -- *************************************************
								
								select count(*) into duplicado from t_comisiones 
								where pedido_id = input_pedido
								and usuario_id = beneficiario
								and esquema_codigo = v_esquema
								and periodo_codigo is not null
								and substring( estatus_codigo, 1, 3 ) > 300;
								
								IF not duplicado THEN				
								    INSERT INTO t_comisiones 
									VALUES (
										NULL, 
										estatus_final, 
										input_pedido, 
										beneficiario, 
										v_esquema, 
										nivel_real, 
										IF( nivel_real = 0 OR nivel_real = i OR socio IS NULL, 0, 1), 
										efectivo, 
										fecha_pago, 
										null
									);  
								END IF;
							        
							    -- *************************************************
								
													    
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
	
	UPDATE t_periodos set estatus_codigo = '255-PENDIENTE' where termina < now() and estatus_codigo = '250-EN-PROCESO';

	RETURN beneficiados;
END//
DELIMITER ;

-- Dumping structure for function vpsbeneleitmx_app.f_reset_padre
DELIMITER //
CREATE FUNCTION `f_reset_padre`(
	`socio` INT,
	`modelo` VARCHAR(25)
) RETURNS int
    DETERMINISTIC
BEGIN

			
	UPDATE t_usuarios 
	SET redes = JSON_SET( redes, CONCAT( '$.modelos."', modelo, '".padre' ), f_get_padre( socio, modelo ) ) 
	WHERE id = socio;
		
	RETURN socio;
END//
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
    UPDATE t_usuarios SET redes = JSON_SET( redes, CONCAT('$.modelos."', modelo ,'".profundidad."', mes, '"'), profundidad ) WHERE id = socio;
    
    if( mes < DATE_FORMAT( NOW(), '%Y%m' ) ) then	    
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
CREATE FUNCTION `f_update_PTS`(`usuario` INT, `modelo` VARCHAR(25), `Ym` INT) RETURNS json
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

-- Dumping structure for trigger vpsbeneleitmx_app.t_pagos_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `t_pagos_after_update` AFTER UPDATE ON `t_pagos` FOR EACH ROW BEGIN
	
	if NEW.estatus_codigo = '420-PAGADO' then
		UPDATE t_usuarios 
	    SET data = JSON_ARRAY_APPEND( 
	        data, 
	        '$.splash', 
	        JSON_OBJECT( 'tipo', 'cash', 'parametros', JSON_ARRAY( OLD.id ) ) 
	    )
	    WHERE id = OLD.usuario_id;
	END if;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_moodle`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_moodle` AS select `t_usuarios`.`id` AS `id`,json_unquote(json_extract(`t_usuarios`.`data`,'$.hash')) AS `password`,upper(trim(json_unquote(json_extract(`t_usuarios`.`data`,'$.nombre')))) AS `nombre`,upper(concat(trim(json_unquote(json_extract(`t_usuarios`.`data`,'$.apellidos[0]'))),' ',trim(json_unquote(json_extract(`t_usuarios`.`data`,'$.apellidos[1]'))))) AS `apellidos`,`t_usuarios`.`correo` AS `correo`,json_unquote(json_extract(`t_usuarios`.`data`,'$.ubicacion.code')) AS `pais`,`t_usuarios`.`telefono` AS `telefono` from `t_usuarios` where (`t_usuarios`.`estatus_codigo` = '201-ACTIVO');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
