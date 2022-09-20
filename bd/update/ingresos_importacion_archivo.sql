-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.22-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla distribucion.inv_ingresos_detalles_import
CREATE TABLE IF NOT EXISTS `inv_ingresos_detalles_import` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `lote` varchar(30) NOT NULL DEFAULT ' ',
  `lote_cantidad` int(11) DEFAULT NULL,
  `lote2` varchar(25) NOT NULL DEFAULT '',
  `producto_id` int(11) NOT NULL,
  `ingreso_id` int(11) NOT NULL,
  `elaboracion` date DEFAULT NULL,
  `vencimiento` date NOT NULL,
  `nro_autorizacion` varchar(25) DEFAULT '',
  `contenedor` varchar(50) NOT NULL DEFAULT '',
  `factura` varchar(20) NOT NULL DEFAULT '',
  `almacen_id` int(11) DEFAULT 0,
  `asignacion_id` int(11) DEFAULT 0,
  `nro_control` varchar(25) DEFAULT '',
  PRIMARY KEY (`id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `ingreso_id` (`ingreso_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribucion.inv_ingresos_import
CREATE TABLE IF NOT EXISTS `inv_ingresos_import` (
  `id_ingreso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_ingreso` date DEFAULT NULL,
  `hora_ingreso` time DEFAULT NULL,
  `tipo` enum('Compra','Traspaso') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `monto_total` decimal(10,2) DEFAULT 0.00,
  `descuento` int(11) NOT NULL DEFAULT 0,
  `monto_total_descuento` decimal(20,0) NOT NULL DEFAULT 0,
  `nombre_proveedor` varchar(200) NOT NULL,
  `nro_registros` int(11) DEFAULT 0,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `transitorio` int(11) NOT NULL DEFAULT 0,
  `des_transitorio` varchar(150) NOT NULL DEFAULT '0',
  `plan_de_pagos` enum('si','no') NOT NULL DEFAULT 'no',
  `proveedor_id` int(3) NOT NULL DEFAULT 0,
  `estado_import` enum('Import','Confirmado') NOT NULL DEFAULT 'Import',
  PRIMARY KEY (`id_ingreso`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `tipo` (`tipo`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  FULLTEXT KEY `nombre_proveedor` (`nombre_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
