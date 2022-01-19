-- --------------------------------------------------------
-- Host:                         95.216.181.83
-- Versión del servidor:         10.3.32-MariaDB-cll-lve - MariaDB Server
-- SO del servidor:              Linux
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_egresos
CREATE TABLE IF NOT EXISTS `backup_inv_egresos` (
  `backup_id_egreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL,
  `fecha_egreso` date NOT NULL,
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
  `evento` enum('Ninguno','Devuelto') NOT NULL DEFAULT 'Ninguno',
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_egreso`) USING BTREE,
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
  KEY `evento` (`evento`),
  FULLTEXT KEY `nombre_cliente` (`nombre_cliente`),
  FULLTEXT KEY `nit_ci` (`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_egresos_detalles
CREATE TABLE IF NOT EXISTS `backup_inv_egresos_detalles` (
  `backup_id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_detalle` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) DEFAULT 0,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `lote` varchar(30) DEFAULT NULL,
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_id_backup` int(11) NOT NULL DEFAULT 0,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=232 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_ingresos
CREATE TABLE IF NOT EXISTS `backup_inv_ingresos` (
  `backup_id_ingreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_ingreso` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL DEFAULT '0000-00-00',
  `hora_ingreso` time DEFAULT '00:00:00',
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
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_ingreso`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `tipo` (`tipo`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  FULLTEXT KEY `nombre_proveedor` (`nombre_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_ingresos_detalles
CREATE TABLE IF NOT EXISTS `backup_inv_ingresos_detalles` (
  `backup_id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_detalle` int(11) NOT NULL,
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
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_id_backup` int(11) NOT NULL DEFAULT 0,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `ingreso_id` (`ingreso_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_pagos
CREATE TABLE IF NOT EXISTS `backup_inv_pagos` (
  `backup_id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_pago` int(11) NOT NULL,
  `movimiento_id` int(11) DEFAULT 0,
  `interes_pago` float NOT NULL DEFAULT 0,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_pago`),
  KEY `movimiento_id` (`movimiento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_pagos_detalles
CREATE TABLE IF NOT EXISTS `backup_inv_pagos_detalles` (
  `backup_id_pago_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_pago_detalle` int(11) NOT NULL,
  `pago_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `tipo_pago` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `nro_cuota` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_id_backup` int(11) NOT NULL DEFAULT 0,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_pago_detalle`),
  KEY `nro_cuota` (`nro_cuota`),
  KEY `pago_id` (`pago_id`)
) ENGINE=MyISAM AUTO_INCREMENT=115 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
