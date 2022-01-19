-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.3.16-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_inicio
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_inicio` (
  `id_detalleAccion` int(11) NOT NULL AUTO_INCREMENT,
  `id_detalle` int(11) NOT NULL DEFAULT 0,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) DEFAULT 0,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `lote` varchar(30) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `empleado_id_accion` int(11) NOT NULL DEFAULT 0,
  `accion_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_detalleAccion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_inicio
CREATE TABLE IF NOT EXISTS `inv_egresos_inicio` (
  `id_inicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date DEFAULT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` int(11) NOT NULL DEFAULT 0,
  `nro_autorizacion` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `codigo_control` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `fecha_limite` date NOT NULL,
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_porcentaje` int(11) DEFAULT 0,
  `descuento_bs` decimal(20,2) NOT NULL DEFAULT 0.00,
  `monto_total_descuento` decimal(20,2) NOT NULL DEFAULT 0.00,
  `cliente_id` int(11) DEFAULT 0,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_registros` int(11) NOT NULL DEFAULT 0,
  `estadoe` int(11) DEFAULT 0,
  `coordenadas` text DEFAULT NULL,
  `observacion` varchar(150) DEFAULT '',
  `dosificacion_id` int(11) NOT NULL DEFAULT 0,
  `almacen_id` int(11) NOT NULL DEFAULT 0,
  `empleado_id` int(11) NOT NULL,
  `motivo_id` int(11) DEFAULT 0,
  `motivo` char(120) DEFAULT NULL,
  `duracion` time DEFAULT '00:00:00',
  `cobrar` varchar(10) DEFAULT '',
  `grupo` varchar(50) DEFAULT '',
  `descripcion_venta` varchar(150) DEFAULT '',
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 0,
  `plan_de_pagos` enum('si','no') NOT NULL DEFAULT 'no',
  `ordenes_salidas_id` int(11) DEFAULT 0,
  `anulado` int(11) NOT NULL DEFAULT 0,
  `factura` enum('Factura','Nota','Ninguno') DEFAULT 'Ninguno',
  `evento` enum('Ninguno','Devuelto') DEFAULT 'Ninguno',
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `empleado_id_accion` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_inicio`),
  KEY `cliente_id` (`cliente_id`),
  KEY `dosificacion_id` (`dosificacion_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `tipo` (`tipo`),
  KEY `provisionado` (`provisionado`),
  KEY `motivo_id` (`motivo_id`),
  KEY `ruta_id` (`ruta_id`),
  KEY `estadoe` (`estadoe`),
  KEY `estado` (`estado`),
  KEY `anulado` (`anulado`),
  KEY `factura` (`factura`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  KEY `ordenes_salidas_id` (`ordenes_salidas_id`),
  FULLTEXT KEY `nombre_cliente` (`nombre_cliente`),
  FULLTEXT KEY `nit_ci` (`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
