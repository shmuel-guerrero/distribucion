-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.20-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla nextcorp_distribucion.inv_importacion
CREATE TABLE IF NOT EXISTS `inv_importacion` (
  `id_importacion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_final` datetime DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `total_gastos` decimal(11,2) DEFAULT 0.00,
  `total_costo` decimal(10,2) DEFAULT 0.00,
  `descripcion` tinytext DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `nro_registros` int(11) DEFAULT NULL,
  `id_proveedor` varchar(50) DEFAULT NULL,
  `almacen_id` int(11) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `etapa` enum('preparacion','concluido') NOT NULL,
  `ingreso_id` int(11) DEFAULT NULL,
  `fecha_factura` date NOT NULL DEFAULT '0000-00-00',
  `nro_factura` varchar(20) NOT NULL DEFAULT '',
  `nro_correlativo` int(11) NOT NULL,
  PRIMARY KEY (`id_importacion`),
  KEY `id_proveedor` (`id_proveedor`,`almacen_id`,`empleado_id`,`ingreso_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla nextcorp_distribucion.inv_importacion_gasto
CREATE TABLE IF NOT EXISTS `inv_importacion_gasto` (
  `id_importacion_gasto` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `codigo` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `total_gasto` decimal(10,2) DEFAULT NULL,
  `tipo_pago` varchar(100) DEFAULT NULL,
  `pago` decimal(10,2) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `importacion_id` int(11) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_importacion_gasto`),
  KEY `importacion_id` (`importacion_id`,`empleado_id`,`proveedor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla nextcorp_distribucion.inv_importacion_gasto_detalle
CREATE TABLE IF NOT EXISTS `inv_importacion_gasto_detalle` (
  `id_importacion_gasto_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `gasto` varchar(100) DEFAULT NULL,
  `factura` varchar(50) DEFAULT NULL,
  `costo_anadido` decimal(10,2) DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `gastos_id` int(11) DEFAULT 0,
  `importacion_gasto_id` int(11) DEFAULT 0,
  PRIMARY KEY (`id_importacion_gasto_detalle`),
  KEY `gastos_id` (`gastos_id`,`importacion_gasto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=240 DEFAULT CHARSET=utf8mb4;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla nextcorp_distribucion.inv_importacion_pagos
CREATE TABLE IF NOT EXISTS `inv_importacion_pagos` (
  `id_importacion_pagos` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `forma_pago` char(25) DEFAULT NULL,
  `comprobante` char(15) DEFAULT NULL,
  `importacion_gasto_id` int(11) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_importacion_pagos`),
  KEY `importacion_gasto_id` (`importacion_gasto_id`,`empleado_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
