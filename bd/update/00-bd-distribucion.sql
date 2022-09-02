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


-- Volcando estructura de base de datos para distribucion
CREATE DATABASE IF NOT EXISTS `distribucion` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `distribucion`;

-- Volcando estructura para tabla distribucion.backup_inv_asignaciones
CREATE TABLE IF NOT EXISTS `backup_inv_asignaciones` (
  `backup_id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad_unidad` int(11) NOT NULL,
  `otro_precio` decimal(20,2) NOT NULL,
  `visible` enum('s','n') NOT NULL DEFAULT 's',
  `delet_fecha_egreso` date DEFAULT NULL,
  `delet_hora_egreso` time DEFAULT NULL,
  `delet_empleado_id` int(11) DEFAULT NULL,
  `accion_backup` enum('Editado','Eliminado','Backup') DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_asignacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_inv_asignaciones: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `backup_inv_asignaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_asignaciones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_egresos
CREATE TABLE IF NOT EXISTS `backup_inv_egresos` (
  `backup_id_egreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM AUTO_INCREMENT=192 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_inv_egresos: 0 rows
/*!40000 ALTER TABLE `backup_inv_egresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_egresos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_egresos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=465 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_inv_egresos_detalles: 0 rows
/*!40000 ALTER TABLE `backup_inv_egresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_egresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_ingresos
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

-- Volcando datos para la tabla distribucion.backup_inv_ingresos: 0 rows
/*!40000 ALTER TABLE `backup_inv_ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_ingresos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_ingresos_detalles
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

-- Volcando datos para la tabla distribucion.backup_inv_ingresos_detalles: 0 rows
/*!40000 ALTER TABLE `backup_inv_ingresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_ingresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_pagos
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
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_inv_pagos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `backup_inv_pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_pagos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_pagos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.backup_inv_pagos_detalles: 0 rows
/*!40000 ALTER TABLE `backup_inv_pagos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_pagos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_inv_productos
CREATE TABLE IF NOT EXISTS `backup_inv_productos` (
  `backup_id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT '',
  `nombre` varchar(100) NOT NULL,
  `nombre_factura` varchar(100) NOT NULL,
  `promocion` varchar(10) DEFAULT '',
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_limite` date DEFAULT '1000-01-01',
  `precio_actual` decimal(10,2) DEFAULT 0.00,
  `cantidad_minima` int(11) NOT NULL,
  `imagen` varchar(100) NOT NULL DEFAULT '',
  `ubicacion` text DEFAULT NULL,
  `descripcion` text NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `sigla_contenedor` varchar(100) DEFAULT NULL,
  `nro_dui` varchar(100) NOT NULL DEFAULT '0',
  `precio_sugerido` decimal(10,2) DEFAULT 0.00,
  `grupo` varchar(50) DEFAULT '',
  `regalo` int(11) NOT NULL DEFAULT 0,
  `marca` varchar(100) DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT 0,
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`backup_id_producto`),
  KEY `unidad_id` (`unidad_id`),
  KEY `categoria_id` (`categoria_id`)
) ENGINE=MyISAM AUTO_INCREMENT=236 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_inv_productos: 0 rows
/*!40000 ALTER TABLE `backup_inv_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_productos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_tmp_egresos
CREATE TABLE IF NOT EXISTS `backup_tmp_egresos` (
  `backup_id_tmp_egreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_tmp_egreso` int(11) NOT NULL,
  `id_egreso` int(11) NOT NULL,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '',
  `nro_autorizacion` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `codigo_control` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fecha_limite` date NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `descuento_porcentaje` int(11) NOT NULL DEFAULT 0,
  `descuento_bs` decimal(20,2) NOT NULL DEFAULT 0.00,
  `monto_total_descuento` decimal(20,2) NOT NULL DEFAULT 0.00,
  `cliente_id` int(11) NOT NULL DEFAULT 0,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nro_registros` int(11) NOT NULL DEFAULT 0,
  `estadoe` int(11) NOT NULL DEFAULT 0,
  `coordenadas` text DEFAULT NULL,
  `observacion` varchar(150) NOT NULL,
  `dosificacion_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `motivo_id` int(11) NOT NULL DEFAULT 0,
  `motivo` char(120) NOT NULL DEFAULT '0',
  `duracion` time NOT NULL DEFAULT '00:00:00',
  `cobrar` varchar(10) NOT NULL DEFAULT '',
  `grupo` varchar(50) NOT NULL DEFAULT '',
  `descripcion_venta` varchar(150) NOT NULL DEFAULT '',
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_fecha` date DEFAULT NULL,
  `distribuidor_hora` time NOT NULL DEFAULT '00:00:00',
  `distribuidor_estado` enum('DEVUELTO','ALMACEN','ENTREGA','NO ENTREGA','VENTA') NOT NULL,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 0,
  `plan_de_pagos` enum('si','no') NOT NULL DEFAULT 'no',
  `ordenes_salidas_id` int(11) NOT NULL DEFAULT 0,
  `anulado` int(11) NOT NULL DEFAULT 0,
  `factura` enum('Factura','Nota','Ninguno') DEFAULT 'Ninguno',
  `evento` enum('Ninguno','Devuelto') NOT NULL DEFAULT 'Ninguno',
  `accion` enum('Entrega','Venta','VentaDevuelto','Noentrega','Anulado','Devuelto','Eliminado','VentaEliminado') NOT NULL DEFAULT 'Entrega',
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_tmp_egreso`),
  KEY `id_egreso` (`id_egreso`),
  KEY `cliente_id` (`cliente_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `ruta_id` (`ruta_id`),
  KEY `distribuidor_id` (`distribuidor_id`),
  KEY `tipo_provisionado` (`tipo`,`provisionado`),
  KEY `estadoe` (`estadoe`),
  KEY `dosificacion_id` (`dosificacion_id`),
  KEY `motivo_id` (`motivo_id`),
  KEY `estado` (`estado`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  KEY `factura` (`factura`),
  KEY `distribuidor_estado` (`distribuidor_estado`),
  FULLTEXT KEY `nombre_cliente_nit_ci` (`nombre_cliente`,`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_tmp_egresos: 0 rows
/*!40000 ALTER TABLE `backup_tmp_egresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_tmp_egresos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.backup_tmp_egresos_detalles
CREATE TABLE IF NOT EXISTS `backup_tmp_egresos_detalles` (
  `backup_id_tmp_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_tmp_detalle` int(11) NOT NULL,
  `id_detalle` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `tmp_egreso_id` int(11) NOT NULL,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `lote` varchar(30) NOT NULL DEFAULT '',
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  `accion_id_backup` int(11) NOT NULL DEFAULT 0,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_tmp_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=267 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.backup_tmp_egresos_detalles: 0 rows
/*!40000 ALTER TABLE `backup_tmp_egresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_tmp_egresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.caj_movimientos
CREATE TABLE IF NOT EXISTS `caj_movimientos` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_movimiento` date NOT NULL,
  `hora_movimiento` time NOT NULL,
  `nro_comprobante` varchar(50) NOT NULL,
  `tipo` enum('i','e','g') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `concepto` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `observacion` text NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `recibido_por` int(11) DEFAULT NULL,
  `sucursal_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_movimiento`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.caj_movimientos: 0 rows
/*!40000 ALTER TABLE `caj_movimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `caj_movimientos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_asiento
CREATE TABLE IF NOT EXISTS `con_asiento` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `cuenta` varchar(30) NOT NULL,
  `debe` double NOT NULL,
  `haber` double NOT NULL,
  `factura` int(11) NOT NULL DEFAULT 0,
  `comprobante` int(11) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_asiento: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_asiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_asiento` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_asientos_automaticos
CREATE TABLE IF NOT EXISTS `con_asientos_automaticos` (
  `id_automatico` int(11) NOT NULL AUTO_INCREMENT,
  `titulo_automatico` varchar(200) NOT NULL,
  `detalle_automatico` varchar(300) NOT NULL,
  `estado` enum('si','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id_automatico`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_asientos_automaticos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_asientos_automaticos` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_asientos_automaticos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_asientos_menus
CREATE TABLE IF NOT EXISTS `con_asientos_menus` (
  `id_asiento_menu` int(11) NOT NULL AUTO_INCREMENT,
  `automatico_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  PRIMARY KEY (`id_asiento_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_asientos_menus: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_asientos_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_asientos_menus` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_clasificacion
CREATE TABLE IF NOT EXISTS `con_clasificacion` (
  `id_clasificacion` int(11) NOT NULL,
  `clasificacion` varchar(50) NOT NULL,
  `tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_clasificacion: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_clasificacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_clasificacion` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_comprobante
CREATE TABLE IF NOT EXISTS `con_comprobante` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` int(11) NOT NULL,
  `glosa` varchar(500) NOT NULL,
  `fecha` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 0,
  `dolar` double NOT NULL,
  `operacion_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_comprobante: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_comprobante` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_comprobante` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_cuenta
CREATE TABLE IF NOT EXISTS `con_cuenta` (
  `id_cuenta` int(11) NOT NULL,
  `n_cuenta` int(11) NOT NULL,
  `cuenta` varchar(50) NOT NULL,
  `estado` int(11) NOT NULL,
  `actividad` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_cuenta: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_cuenta` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_cuenta` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_datos_empresa
CREATE TABLE IF NOT EXISTS `con_datos_empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `razon_social` varchar(50) NOT NULL,
  `pais` varchar(20) NOT NULL,
  `ciudad` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_datos_empresa: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_datos_empresa` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_datos_empresa` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_detalles_automaticos
CREATE TABLE IF NOT EXISTS `con_detalles_automaticos` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `automatico_id` int(11) NOT NULL,
  `plan_id` varchar(30) NOT NULL,
  `porcentaje` float NOT NULL,
  `tipo` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_detalles_automaticos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_detalles_automaticos` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_detalles_automaticos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_factura
CREATE TABLE IF NOT EXISTS `con_factura` (
  `id_factura` int(11) NOT NULL,
  `fecha_f` date NOT NULL,
  `nit_f` varchar(50) NOT NULL,
  `nombre_f` varchar(100) NOT NULL,
  `nro_f` int(11) NOT NULL,
  `autorizacion_f` varchar(50) NOT NULL,
  `codigo_f` varchar(50) NOT NULL,
  `total_f` double NOT NULL,
  `importes_f` double NOT NULL,
  `ice_f` double NOT NULL,
  `tipo_f` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_factura: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_factura` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_factura` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_menus
CREATE TABLE IF NOT EXISTS `con_menus` (
  `id_menu` int(11) NOT NULL,
  `menu` varchar(100) NOT NULL,
  `estado` int(11) NOT NULL,
  `descripcion` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_menus: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_menus` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_plan
CREATE TABLE IF NOT EXISTS `con_plan` (
  `id_plan` int(11) NOT NULL AUTO_INCREMENT,
  `n_plan` varchar(30) NOT NULL,
  `plan_cuenta` varchar(150) NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` int(11) NOT NULL,
  `actividadc` int(11) NOT NULL DEFAULT 0,
  `utilidadc` int(11) NOT NULL DEFAULT 0,
  `nodo` int(11) NOT NULL,
  PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_plan: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_plan` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_tipo_moneda
CREATE TABLE IF NOT EXISTS `con_tipo_moneda` (
  `id_moneda` int(11) NOT NULL,
  `moneda` varchar(50) NOT NULL,
  `sigla` varchar(20) NOT NULL,
  `valor` float NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_tipo_moneda: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_tipo_moneda` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_tipo_moneda` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.con_ufv
CREATE TABLE IF NOT EXISTS `con_ufv` (
  `id_ufv` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `mes` varchar(20) NOT NULL,
  `dias` int(11) NOT NULL,
  `ufv` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.con_ufv: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `con_ufv` DISABLE KEYS */;
/*!40000 ALTER TABLE `con_ufv` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.cronograma
CREATE TABLE IF NOT EXISTS `cronograma` (
  `id_cronograma` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT '0000-00-00',
  `periodo` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `detalle` text CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `monto` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_cronograma`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.cronograma: 0 rows
/*!40000 ALTER TABLE `cronograma` DISABLE KEYS */;
/*!40000 ALTER TABLE `cronograma` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.cronograma_cuentas
CREATE TABLE IF NOT EXISTS `cronograma_cuentas` (
  `id_cronograma_cuentas` int(11) NOT NULL AUTO_INCREMENT,
  `cronograma_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 0,
  `fecha_pago` date NOT NULL DEFAULT '0000-00-00',
  `tipo_pago` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `detalle` text CHARACTER SET utf8 NOT NULL,
  `monto` double NOT NULL DEFAULT 0,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_cronograma_cuentas`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.cronograma_cuentas: 0 rows
/*!40000 ALTER TABLE `cronograma_cuentas` DISABLE KEYS */;
/*!40000 ALTER TABLE `cronograma_cuentas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_asigna_distribucion
CREATE TABLE IF NOT EXISTS `gps_asigna_distribucion` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `ruta_id` int(11) NOT NULL,
  `grupo_id` varchar(50) DEFAULT '',
  `distribuidor_id` int(11) NOT NULL,
  `fecha_ini` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `distribuidor_id` (`distribuidor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.gps_asigna_distribucion: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_asigna_distribucion` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_asigna_distribucion` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_historial_asinacion
CREATE TABLE IF NOT EXISTS `gps_historial_asinacion` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `ruta_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `fecha_ini` date NOT NULL DEFAULT '0000-00-00',
  `fecha_fin` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id_historial`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.gps_historial_asinacion: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_historial_asinacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_historial_asinacion` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_historial_distribuidores_vendedores
CREATE TABLE IF NOT EXISTS `gps_historial_distribuidores_vendedores` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `vendedor_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `grupo_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_historial`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.gps_historial_distribuidores_vendedores: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_historial_distribuidores_vendedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_historial_distribuidores_vendedores` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_noventa_motivos
CREATE TABLE IF NOT EXISTS `gps_noventa_motivos` (
  `id_motivo` int(11) NOT NULL AUTO_INCREMENT,
  `motivo` varchar(200) NOT NULL,
  PRIMARY KEY (`id_motivo`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.gps_noventa_motivos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_noventa_motivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_noventa_motivos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_no_venta
CREATE TABLE IF NOT EXISTS `gps_no_venta` (
  `id_noventa` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `coordenadas` text NOT NULL,
  `direccion` varchar(250) NOT NULL,
  `motivo_id` int(11) NOT NULL,
  PRIMARY KEY (`id_noventa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.gps_no_venta: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_no_venta` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_no_venta` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_rutas
CREATE TABLE IF NOT EXISTS `gps_rutas` (
  `id_ruta` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `coordenadas` text NOT NULL,
  `fecha` date NOT NULL,
  `estado` int(11) NOT NULL,
  `dia` int(11) NOT NULL DEFAULT 0,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `color` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_ruta`),
  KEY `empleado_id` (`empleado_id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.gps_rutas: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_rutas` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_rutas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.gps_seguimientos
CREATE TABLE IF NOT EXISTS `gps_seguimientos` (
  `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT,
  `coordenadas` longtext NOT NULL,
  `fecha_seguimiento` date NOT NULL DEFAULT '0000-00-00',
  `hora_seguimiento` longtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id_seguimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.gps_seguimientos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `gps_seguimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `gps_seguimientos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.hist_conversiones
CREATE TABLE IF NOT EXISTS `hist_conversiones` (
  `id_conversion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `id_origen` int(11) NOT NULL DEFAULT 0,
  `origen_movimiento` enum('Proforma','NotaRemision','Reserva','Preventa','Electronicas') NOT NULL,
  `id_destino` int(11) NOT NULL DEFAULT 0,
  `destino_movimiento` enum('Electronicas','NotaRemision','Manuales','Preventa') NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `tipo` enum('ConversionDirecta','ConversionEdicion') NOT NULL DEFAULT 'ConversionDirecta',
  `id_backup_egreso` int(11) NOT NULL DEFAULT 0,
  `ids_backup_detalles` varchar(250) NOT NULL DEFAULT '',
  `dispositivo` enum('Movil','Web') NOT NULL DEFAULT 'Movil',
  PRIMARY KEY (`id_conversion`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.hist_conversiones: 0 rows
/*!40000 ALTER TABLE `hist_conversiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_conversiones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_almacenes
CREATE TABLE IF NOT EXISTS `inv_almacenes` (
  `id_almacen` int(11) NOT NULL AUTO_INCREMENT,
  `almacen` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telefono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `principal` enum('N','S') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_almacen`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_almacenes: 2 rows
/*!40000 ALTER TABLE `inv_almacenes` DISABLE KEYS */;
INSERT INTO `inv_almacenes` (`id_almacen`, `almacen`, `direccion`, `telefono`, `descripcion`, `principal`) VALUES
	(22, 'almacen principal', 'almacen principal', '7000001', '', 'S'),
	(23, 'almacén secundarios', 'almacén secundarios', '600002', '', 'N');
/*!40000 ALTER TABLE `inv_almacenes` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_asignaciones
CREATE TABLE IF NOT EXISTS `inv_asignaciones` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad_unidad` int(11) NOT NULL,
  `otro_precio` decimal(20,2) NOT NULL,
  `visible` enum('s','n') NOT NULL DEFAULT 's',
  PRIMARY KEY (`id_asignacion`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_asignaciones: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_asignaciones` DISABLE KEYS */;
INSERT INTO `inv_asignaciones` (`id_asignacion`, `producto_id`, `unidad_id`, `cantidad_unidad`, `otro_precio`, `visible`) VALUES
	(33, 1, 3, 15, 570.00, 's');
/*!40000 ALTER TABLE `inv_asignaciones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_asignaciones_almacenes
CREATE TABLE IF NOT EXISTS `inv_asignaciones_almacenes` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `institucion_id` int(11) NOT NULL DEFAULT 0,
  `almacen_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_asignacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_asignaciones_almacenes: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_asignaciones_almacenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_asignaciones_almacenes` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_caja
CREATE TABLE IF NOT EXISTS `inv_caja` (
  `id_caja` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `hora_caja` time NOT NULL,
  `total_ingresos` decimal(10,3) NOT NULL DEFAULT 0.000,
  `total_egresos` decimal(10,3) NOT NULL DEFAULT 0.000,
  `total_saldo` decimal(10,3) NOT NULL DEFAULT 0.000,
  `total_total` decimal(10,3) NOT NULL DEFAULT 0.000,
  `estado` enum('INICIO','CAJA','CIERRE') NOT NULL,
  PRIMARY KEY (`id_caja`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_caja: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_caja` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_caja` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_categorias
CREATE TABLE IF NOT EXISTS `inv_categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(100) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `categoria_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_categorias: 1 rows
/*!40000 ALTER TABLE `inv_categorias` DISABLE KEYS */;
INSERT INTO `inv_categorias` (`id_categoria`, `categoria`, `descripcion`, `categoria_id`) VALUES
	(17, 'perecederos', '', 0);
/*!40000 ALTER TABLE `inv_categorias` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_clientes
CREATE TABLE IF NOT EXISTS `inv_clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time NOT NULL DEFAULT '00:00:00',
  `cliente` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nombre_factura` varchar(200) NOT NULL,
  `nit` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(50) NOT NULL DEFAULT '',
  `clave` varchar(60) NOT NULL DEFAULT '',
  `estado` enum('si','no') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'si',
  `cuentas_por_cobrar` enum('no','si') NOT NULL DEFAULT 'no',
  `telefono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `direccion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `descripcion` text NOT NULL DEFAULT '',
  `ubicacion` text NOT NULL DEFAULT '-16.507330653609248, -68.16302964994102',
  `imagen` text NOT NULL DEFAULT '',
  `categoria` int(11) NOT NULL DEFAULT 0,
  `tipo` varchar(50) NOT NULL DEFAULT '',
  `token` varchar(60) NOT NULL DEFAULT '',
  `cliente_grupo_id` int(11) NOT NULL DEFAULT 0,
  `credito` tinyint(1) NOT NULL DEFAULT 0,
  `dias` int(3) DEFAULT 0,
  `fecha_modificacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `estado` (`estado`),
  KEY `cuentas_por_cobrar` (`cuentas_por_cobrar`),
  FULLTEXT KEY `nombre_factura` (`nombre_factura`),
  FULLTEXT KEY `nit` (`nit`),
  FULLTEXT KEY `cliente` (`cliente`)
) ENGINE=MyISAM AUTO_INCREMENT=4994 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_clientes: 0 rows
/*!40000 ALTER TABLE `inv_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_clientes` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_clientes_grupos
CREATE TABLE IF NOT EXISTS `inv_clientes_grupos` (
  `id_cliente_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_grupo` varchar(50) NOT NULL,
  `descuento_grupo` int(11) NOT NULL DEFAULT 0,
  `credito_grupo` enum('no','si') NOT NULL DEFAULT 'si',
  `permiso_grupo` int(11) NOT NULL DEFAULT 0,
  `estado_grupo` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_cliente_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_clientes_grupos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_clientes_grupos` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_clientes_grupos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_control
CREATE TABLE IF NOT EXISTS `inv_control` (
  `id_control` int(11) NOT NULL AUTO_INCREMENT,
  `id_materiales` int(11) NOT NULL,
  `tipo` enum('fabrica','cliente','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `stock` enum('ingreso','egreso') NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha_control` date NOT NULL,
  `estado` enum('pendiente','entregado','vendido') DEFAULT NULL,
  `proveedor` varchar(100) NOT NULL,
  `ordenes_salidas_id` int(11) NOT NULL DEFAULT 0,
  `egreso_id` int(11) NOT NULL DEFAULT 0,
  `planilla` varchar(10) NOT NULL DEFAULT '',
  `placa` varchar(10) NOT NULL DEFAULT '',
  `almacen_id` int(11) NOT NULL,
  `cantidad_inicial` int(11) DEFAULT 0,
  PRIMARY KEY (`id_control`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_control: 0 rows
/*!40000 ALTER TABLE `inv_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_control` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_control_detalle
CREATE TABLE IF NOT EXISTS `inv_control_detalle` (
  `id_control_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `control_id` int(11) NOT NULL,
  `stock` enum('ingreso','egreso') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `ordenes_salidas_id` int(11) NOT NULL,
  PRIMARY KEY (`id_control_detalle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_control_detalle: 0 rows
/*!40000 ALTER TABLE `inv_control_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_control_detalle` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_detalle_ingreso_material
CREATE TABLE IF NOT EXISTS `inv_detalle_ingreso_material` (
  `cantidad` int(11) DEFAULT NULL,
  `ingreso_material_id` int(11) DEFAULT NULL,
  `materiales_stock_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_detalle_ingreso_material: 0 rows
/*!40000 ALTER TABLE `inv_detalle_ingreso_material` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_detalle_ingreso_material` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_dosificaciones
CREATE TABLE IF NOT EXISTS `inv_dosificaciones` (
  `id_dosificacion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `nro_tramite` varchar(50) NOT NULL,
  `nro_autorizacion` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `llave_dosificacion` varchar(200) CHARACTER SET latin1 NOT NULL,
  `fecha_limite` date NOT NULL,
  `leyenda` text NOT NULL,
  `activo` enum('N','S') NOT NULL,
  `nro_facturas` int(11) NOT NULL,
  `observacion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_dosificacion`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_dosificaciones: 0 rows
/*!40000 ALTER TABLE `inv_dosificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_dosificaciones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos
CREATE TABLE IF NOT EXISTS `inv_egresos` (
  `id_egreso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  `estadoe` int(11) DEFAULT 0,
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 0,
  `plan_de_pagos` enum('si','no') NOT NULL DEFAULT 'no',
  `ordenes_salidas_id` int(11) DEFAULT 0,
  `anulado` int(11) NOT NULL DEFAULT 0,
  `factura` enum('Factura','Nota','Ninguno') DEFAULT 'Ninguno',
  `evento` enum('Ninguno','Devuelto') NOT NULL DEFAULT 'Ninguno',
  PRIMARY KEY (`id_egreso`),
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
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos: 0 rows
/*!40000 ALTER TABLE `inv_egresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_anular
CREATE TABLE IF NOT EXISTS `inv_egresos_anular` (
  `id_anular` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') DEFAULT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id_anular`),
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

-- Volcando datos para la tabla distribucion.inv_egresos_anular: 0 rows
/*!40000 ALTER TABLE `inv_egresos_anular` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_anular` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_desc_asignaciones
CREATE TABLE IF NOT EXISTS `inv_egresos_desc_asignaciones` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `egreso_id` int(11) NOT NULL DEFAULT 0,
  `model_id` int(11) NOT NULL,
  `model_table` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tipo_id` int(11) NOT NULL DEFAULT 0,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `desc_efectivo` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_asignacion`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando datos para la tabla distribucion.inv_egresos_desc_asignaciones: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_egresos_desc_asignaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_desc_asignaciones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) DEFAULT 0,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `lote` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_anular
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_anular` (
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
  `accion_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalleAccion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_anular: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_anular` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_anular` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_editar_post
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_editar_post` (
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
  `accion_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalleAccion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=153 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_editar_post: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_editar_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_editar_post` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_editar_previo
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_editar_previo` (
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
  `accion_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalleAccion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_editar_previo: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_editar_previo` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_editar_previo` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_eliminar_post
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_eliminar_post` (
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
  `accion_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalleAccion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=147 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_eliminar_post: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_eliminar_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_eliminar_post` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_eliminar_previo
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_eliminar_previo` (
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
  `accion_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalleAccion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_eliminar_previo: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_eliminar_previo` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_eliminar_previo` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_entregas
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_entregas` (
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
) ENGINE=MyISAM AUTO_INCREMENT=168 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_entregas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_entregas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_entregas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_inicio
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
) ENGINE=MyISAM AUTO_INCREMENT=178 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_inicio: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_inicio` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_inicio` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_noentregas
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_noentregas` (
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_noentregas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_noentregas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_noentregas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_noventas
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_noventas` (
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_noventas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_noventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_noventas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_detalles_ventas_editadas
CREATE TABLE IF NOT EXISTS `inv_egresos_detalles_ventas_editadas` (
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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_detalles_ventas_editadas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_detalles_ventas_editadas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_detalles_ventas_editadas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_editar_post
CREATE TABLE IF NOT EXISTS `inv_egresos_editar_post` (
  `id_editarPost` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id_editarPost`),
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
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_editar_post: 0 rows
/*!40000 ALTER TABLE `inv_egresos_editar_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_editar_post` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_editar_previo
CREATE TABLE IF NOT EXISTS `inv_egresos_editar_previo` (
  `id_editarPrevio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id_editarPrevio`),
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
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_editar_previo: 0 rows
/*!40000 ALTER TABLE `inv_egresos_editar_previo` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_editar_previo` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_eliminar_post
CREATE TABLE IF NOT EXISTS `inv_egresos_eliminar_post` (
  `id_eliminarPost` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id_eliminarPost`),
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
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_eliminar_post: 0 rows
/*!40000 ALTER TABLE `inv_egresos_eliminar_post` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_eliminar_post` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_eliminar_previo
CREATE TABLE IF NOT EXISTS `inv_egresos_eliminar_previo` (
  `id_eliminarPrevio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id_eliminarPrevio`),
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

-- Volcando datos para la tabla distribucion.inv_egresos_eliminar_previo: 0 rows
/*!40000 ALTER TABLE `inv_egresos_eliminar_previo` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_eliminar_previo` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_entregas
CREATE TABLE IF NOT EXISTS `inv_egresos_entregas` (
  `id_entrega` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date DEFAULT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  PRIMARY KEY (`id_entrega`),
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
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_entregas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_entregas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_entregas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_inicio
CREATE TABLE IF NOT EXISTS `inv_egresos_inicio` (
  `id_inicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date DEFAULT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  `estado_vendedor` enum('Abierto','Cerrado') NOT NULL DEFAULT 'Abierto',
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
) ENGINE=MyISAM AUTO_INCREMENT=83 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_inicio: 0 rows
/*!40000 ALTER TABLE `inv_egresos_inicio` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_inicio` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_noentregas
CREATE TABLE IF NOT EXISTS `inv_egresos_noentregas` (
  `id_inicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date DEFAULT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `nombre_cliente` (`nombre_cliente`),
  FULLTEXT KEY `nit_ci` (`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_noentregas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_noentregas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_noentregas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_noventas
CREATE TABLE IF NOT EXISTS `inv_egresos_noventas` (
  `id_inicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date DEFAULT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_noventas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_noventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_noventas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_egresos_ventas_editadas
CREATE TABLE IF NOT EXISTS `inv_egresos_ventas_editadas` (
  `id_inicio` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL DEFAULT 0,
  `fecha_egreso` date DEFAULT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja','Perdida','Devolucion','No venta') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_egresos_ventas_editadas: 0 rows
/*!40000 ALTER TABLE `inv_egresos_ventas_editadas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_egresos_ventas_editadas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_ingresos
CREATE TABLE IF NOT EXISTS `inv_ingresos` (
  `id_ingreso` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id_ingreso`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `tipo` (`tipo`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  FULLTEXT KEY `nombre_proveedor` (`nombre_proveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_ingresos: 0 rows
/*!40000 ALTER TABLE `inv_ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_ingresos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_ingresos_detalles
CREATE TABLE IF NOT EXISTS `inv_ingresos_detalles` (
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
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_ingresos_detalles: 0 rows
/*!40000 ALTER TABLE `inv_ingresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_ingresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_ingreso_material
CREATE TABLE IF NOT EXISTS `inv_ingreso_material` (
  `id_ingreso_material` int(11) NOT NULL AUTO_INCREMENT,
  `Fecha` date DEFAULT NULL,
  `Planilla` varchar(15) DEFAULT NULL,
  `Placa` varchar(15) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_ingreso_material`),
  KEY `empleado_id` (`empleado_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_ingreso_material: 0 rows
/*!40000 ALTER TABLE `inv_ingreso_material` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_ingreso_material` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_marcas
CREATE TABLE IF NOT EXISTS `inv_marcas` (
  `id_marca` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `descripcion` varchar(1500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_marca`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.inv_marcas: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_marcas` DISABLE KEYS */;
INSERT INTO `inv_marcas` (`id_marca`, `marca`, `descripcion`) VALUES
	(5, 'toyota', '');
/*!40000 ALTER TABLE `inv_marcas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_materiales
CREATE TABLE IF NOT EXISTS `inv_materiales` (
  `id_materiales` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `id_unidad` int(11) NOT NULL,
  `precio` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `fecha_material` date NOT NULL,
  PRIMARY KEY (`id_materiales`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_materiales: 0 rows
/*!40000 ALTER TABLE `inv_materiales` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_materiales` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_materiales_stock
CREATE TABLE IF NOT EXISTS `inv_materiales_stock` (
  `id_materiales_stock` int(11) NOT NULL AUTO_INCREMENT,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `almacen_id` int(11) NOT NULL,
  `materiales_id` int(11) NOT NULL,
  PRIMARY KEY (`id_materiales_stock`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_materiales_stock: 0 rows
/*!40000 ALTER TABLE `inv_materiales_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_materiales_stock` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_meta
CREATE TABLE IF NOT EXISTS `inv_meta` (
  `id_meta` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL DEFAULT '0000-00-00',
  `hora_registro` time NOT NULL DEFAULT '00:00:00',
  `monto` decimal(10,2) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `id_empleado_q_registro` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_meta`),
  KEY `empleado_id` (`empleado_id`),
  KEY `id_empleado_q_registro` (`id_empleado_q_registro`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_meta: 0 rows
/*!40000 ALTER TABLE `inv_meta` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_meta` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_metas_distribuidor
CREATE TABLE IF NOT EXISTS `inv_metas_distribuidor` (
  `id_meta` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL DEFAULT '0000-00-00',
  `hora_registro` time NOT NULL DEFAULT '00:00:00',
  `monto` double(11,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `distribuidor_id` int(11) NOT NULL,
  `id_empleado_q_registro` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_meta`),
  KEY `distribuidor_id` (`distribuidor_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_metas_distribuidor: 0 rows
/*!40000 ALTER TABLE `inv_metas_distribuidor` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_metas_distribuidor` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_meta_categoria
CREATE TABLE IF NOT EXISTS `inv_meta_categoria` (
  `id_meta_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL DEFAULT '0000-00-00',
  `hora_registro` time NOT NULL DEFAULT '00:00:00',
  `monto` decimal(8,2) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `id_empleado_q_registro` int(11) DEFAULT 0,
  PRIMARY KEY (`id_meta_categoria`),
  KEY `categoria_id` (`categoria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_meta_categoria: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_meta_categoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_meta_categoria` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_meta_producto
CREATE TABLE IF NOT EXISTS `inv_meta_producto` (
  `id_meta_producto` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL DEFAULT '0000-00-00',
  `hora_registro` time NOT NULL DEFAULT '00:00:00',
  `monto` decimal(8,2) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `id_empleado_q_registro` int(11) DEFAULT 0,
  PRIMARY KEY (`id_meta_producto`),
  KEY `producto_id` (`producto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;

-- Volcando datos para la tabla distribucion.inv_meta_producto: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_meta_producto` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_meta_producto` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_monedas
CREATE TABLE IF NOT EXISTS `inv_monedas` (
  `id_moneda` int(11) NOT NULL AUTO_INCREMENT,
  `moneda` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL,
  `oficial` enum('N','S') CHARACTER SET latin1 NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id_moneda`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_monedas: 1 rows
/*!40000 ALTER TABLE `inv_monedas` DISABLE KEYS */;
INSERT INTO `inv_monedas` (`id_moneda`, `moneda`, `sigla`, `oficial`) VALUES
	(22, 'Boliviano', 'Bs', 'S');
/*!40000 ALTER TABLE `inv_monedas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_ordenes_detalles
CREATE TABLE IF NOT EXISTS `inv_ordenes_detalles` (
  `id_orden_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `orden_salida_id` int(11) NOT NULL,
  `precio_id` decimal(10,2) DEFAULT 0.00,
  `cantidad` int(11) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `cantidad_inicial` int(11) DEFAULT 0,
  PRIMARY KEY (`id_orden_detalle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_ordenes_detalles: 0 rows
/*!40000 ALTER TABLE `inv_ordenes_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_ordenes_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_ordenes_salidas
CREATE TABLE IF NOT EXISTS `inv_ordenes_salidas` (
  `id_orden` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_orden` date NOT NULL,
  `hora_orden` time NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `estado` enum('salida','entregado') NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_regitro_id` int(11) NOT NULL,
  `empleado_entrega_id` int(11) NOT NULL,
  PRIMARY KEY (`id_orden`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_ordenes_salidas: 0 rows
/*!40000 ALTER TABLE `inv_ordenes_salidas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_ordenes_salidas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_pagos
CREATE TABLE IF NOT EXISTS `inv_pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `movimiento_id` int(11) DEFAULT 0,
  `interes_pago` float NOT NULL DEFAULT 0,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `movimiento_id` (`movimiento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_pagos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_pagos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_pagos_detalles
CREATE TABLE IF NOT EXISTS `inv_pagos_detalles` (
  `id_pago_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `pago_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float NOT NULL,
  `estado` int(11) NOT NULL,
  `fecha_pago` date NOT NULL,
  `tipo_pago` varchar(10) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `nro_cuota` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_pago_detalle`),
  KEY `nro_cuota` (`nro_cuota`),
  KEY `pago_id` (`pago_id`)
) ENGINE=MyISAM AUTO_INCREMENT=338 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla distribucion.inv_pagos_detalles: 0 rows
/*!40000 ALTER TABLE `inv_pagos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_pagos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_participantes_promos
CREATE TABLE IF NOT EXISTS `inv_participantes_promos` (
  `id_participante_promo` int(11) NOT NULL AUTO_INCREMENT,
  `promocion_monto_id` int(11) NOT NULL,
  `cliente_grupo_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  PRIMARY KEY (`id_participante_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_participantes_promos: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_participantes_promos` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_participantes_promos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_precios
CREATE TABLE IF NOT EXISTS `inv_precios` (
  `id_precio` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_precio`)
) ENGINE=MyISAM AUTO_INCREMENT=492 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_precios: 1 rows
/*!40000 ALTER TABLE `inv_precios` DISABLE KEYS */;
INSERT INTO `inv_precios` (`id_precio`, `precio`, `fecha_registro`, `hora_registro`, `asignacion_id`, `producto_id`, `empleado_id`) VALUES
	(491, 570.00, '2022-09-01', '22:33:15', 33, 1, 1);
/*!40000 ALTER TABLE `inv_precios` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_prioridades_ventas
CREATE TABLE IF NOT EXISTS `inv_prioridades_ventas` (
  `id_prioridad_venta` int(11) NOT NULL AUTO_INCREMENT,
  `prioridad` varchar(100) NOT NULL,
  PRIMARY KEY (`id_prioridad_venta`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_prioridades_ventas: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_prioridades_ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_prioridades_ventas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_productos
CREATE TABLE IF NOT EXISTS `inv_productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT '',
  `nombre` varchar(100) NOT NULL,
  `nombre_factura` varchar(100) NOT NULL,
  `promocion` varchar(10) DEFAULT '',
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_limite` date DEFAULT '1000-01-01',
  `precio_actual` decimal(10,2) DEFAULT 0.00,
  `cantidad_minima` int(11) NOT NULL,
  `imagen` varchar(100) NOT NULL DEFAULT '',
  `ubicacion` text DEFAULT NULL,
  `descripcion` text NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `marca_id` int(11) NOT NULL DEFAULT 0,
  `sigla_contenedor` varchar(100) DEFAULT NULL,
  `nro_dui` varchar(100) NOT NULL DEFAULT '0',
  `precio_sugerido` decimal(10,2) DEFAULT 0.00,
  `grupo` varchar(50) DEFAULT '',
  `regalo` int(11) NOT NULL DEFAULT 0,
  `marca` varchar(100) DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_producto`),
  KEY `unidad_id` (`unidad_id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `marca_id` (`marca_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_productos: 1 rows
/*!40000 ALTER TABLE `inv_productos` DISABLE KEYS */;
INSERT INTO `inv_productos` (`id_producto`, `codigo`, `codigo_barras`, `nombre`, `nombre_factura`, `promocion`, `fecha_registro`, `hora_registro`, `fecha_vencimiento`, `fecha_limite`, `precio_actual`, `cantidad_minima`, `imagen`, `ubicacion`, `descripcion`, `unidad_id`, `categoria_id`, `marca_id`, `sigla_contenedor`, `nro_dui`, `precio_sugerido`, `grupo`, `regalo`, `marca`, `eliminado`) VALUES
	(1, '', 'CB', 'producto de prueba', 'producto de prueba', '', '2022-09-01', '20:09:49', NULL, '1000-01-01', 25.00, 10, '', '', '', 1, 17, 0, NULL, '0', 0.00, '', 0, NULL, 0);
/*!40000 ALTER TABLE `inv_productos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_proformas
CREATE TABLE IF NOT EXISTS `inv_proformas` (
  `id_proforma` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_proforma` date NOT NULL,
  `hora_proforma` time NOT NULL,
  `descripcion` text NOT NULL,
  `nro_proforma` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `adelanto` decimal(10,2) NOT NULL,
  `cliente_id` int(11) NOT NULL DEFAULT 0,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_registros` int(11) NOT NULL,
  `validez` int(11) NOT NULL,
  `observacion` text NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `descuento_bs` decimal(10,2) DEFAULT NULL,
  `descuento_porcentaje` int(11) DEFAULT NULL,
  `monto_total_descuento` decimal(10,2) DEFAULT NULL,
  `id_egreso_convertido` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_proforma`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `cliente_id` (`cliente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_proformas: 0 rows
/*!40000 ALTER TABLE `inv_proformas` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_proformas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_proformas_detalles
CREATE TABLE IF NOT EXISTS `inv_proformas_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `proforma_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `proforma_id` (`proforma_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_proformas_detalles: 0 rows
/*!40000 ALTER TABLE `inv_proformas_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_proformas_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_promociones
CREATE TABLE IF NOT EXISTS `inv_promociones` (
  `id_promocion` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  KEY `producto_id` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_promociones: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_promociones` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_promociones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_promociones_monto
CREATE TABLE IF NOT EXISTS `inv_promociones_monto` (
  `id_promocion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `tipo` varchar(30) NOT NULL DEFAULT '',
  `fecha_ini` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` text NOT NULL,
  `min_promo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `item_promo` varchar(200) NOT NULL DEFAULT '',
  `descuento_promo` int(10) NOT NULL DEFAULT 0,
  `monto_promo` decimal(10,2) NOT NULL,
  `egresos_ids` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_promocion`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_promociones_monto: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_promociones_monto` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_promociones_monto` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_proveedores
CREATE TABLE IF NOT EXISTS `inv_proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor` varchar(200) NOT NULL,
  `nit` varchar(50) NOT NULL DEFAULT '',
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=377 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_proveedores: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_proveedores` DISABLE KEYS */;
INSERT INTO `inv_proveedores` (`id_proveedor`, `proveedor`, `nit`, `direccion`, `telefono`) VALUES
	(376, 'PROVEEEDOR DE PRUEBA', '7239099333', 'PROVEEEDOR DE PRUEBA', '600000005');
/*!40000 ALTER TABLE `inv_proveedores` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_requisitos_promo
CREATE TABLE IF NOT EXISTS `inv_requisitos_promo` (
  `id_requisitos` int(11) NOT NULL AUTO_INCREMENT,
  `promocion_monto_id` int(11) NOT NULL,
  `productos_id` int(11) NOT NULL,
  PRIMARY KEY (`id_requisitos`),
  KEY `productos_id` (`productos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_requisitos_promo: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_requisitos_promo` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_requisitos_promo` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_terminales
CREATE TABLE IF NOT EXISTS `inv_terminales` (
  `id_terminal` int(11) NOT NULL AUTO_INCREMENT,
  `terminal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `identificador` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `impresora` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_terminal`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando datos para la tabla distribucion.inv_terminales: 0 rows
/*!40000 ALTER TABLE `inv_terminales` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_terminales` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_tipos_clientes
CREATE TABLE IF NOT EXISTS `inv_tipos_clientes` (
  `id_tipo_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_cliente` varchar(50) NOT NULL,
  PRIMARY KEY (`id_tipo_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_tipos_clientes: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_tipos_clientes` DISABLE KEYS */;
INSERT INTO `inv_tipos_clientes` (`id_tipo_cliente`, `tipo_cliente`) VALUES
	(17, 'Talleres'),
	(18, 'Publico en general'),
	(20, 'Mayorista o minorista');
/*!40000 ALTER TABLE `inv_tipos_clientes` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_tipo_calculo
CREATE TABLE IF NOT EXISTS `inv_tipo_calculo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `sigla` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.inv_tipo_calculo: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_tipo_calculo` DISABLE KEYS */;
INSERT INTO `inv_tipo_calculo` (`id`, `tipo`, `sigla`) VALUES
	(1, 'porcentual', '%'),
	(2, 'puntual', '$');
/*!40000 ALTER TABLE `inv_tipo_calculo` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_tipo_movimientos
CREATE TABLE IF NOT EXISTS `inv_tipo_movimientos` (
  `id_tipo_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_movimiento` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tipo_movimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando datos para la tabla distribucion.inv_tipo_movimientos: ~4 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_tipo_movimientos` DISABLE KEYS */;
INSERT INTO `inv_tipo_movimientos` (`id_tipo_movimiento`, `tipo_movimiento`) VALUES
	(1, 'electronica'),
	(2, 'nota'),
	(3, 'manual'),
	(4, 'proforma');
/*!40000 ALTER TABLE `inv_tipo_movimientos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.inv_unidades
CREATE TABLE IF NOT EXISTS `inv_unidades` (
  `id_unidad` int(11) NOT NULL AUTO_INCREMENT,
  `unidad` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tamanio` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_unidad`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.inv_unidades: 12 rows
/*!40000 ALTER TABLE `inv_unidades` DISABLE KEYS */;
INSERT INTO `inv_unidades` (`id_unidad`, `unidad`, `sigla`, `descripcion`, `tamanio`) VALUES
	(1, 'UNIDAD', 'UN', '', NULL),
	(2, 'UNIDADES', 'UNDS', '', NULL),
	(3, 'CAJA', 'CJA', '', NULL),
	(4, 'BOLSA', 'BOL', '', NULL),
	(5, 'SACO', 'CA', '', NULL),
	(6, 'ARROBA', 'A', '', NULL),
	(7, 'PROMOCION', '', '', NULL),
	(8, 'PAQUETE', 'PQT', '', NULL),
	(9, 'TIRA', 'TIR', '', NULL),
	(10, 'MEDIA SACO', 'MESAC', '', NULL),
	(11, '15 UNIDADES', '15UNI', 'MERC', NULL),
	(12, 'unidad bolsa con cajas', 'BC', 'unidad bolsa con cajas, prueba beca', NULL);
/*!40000 ALTER TABLE `inv_unidades` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_desc_generales
CREATE TABLE IF NOT EXISTS `sys_desc_generales` (
  `id_config_descuento` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `model_table` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo_id` int(11) NOT NULL,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Activo','Inactivo') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Activo',
  PRIMARY KEY (`id_config_descuento`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando datos para la tabla distribucion.sys_desc_generales: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_desc_generales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_desc_generales` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_desc_tipos
CREATE TABLE IF NOT EXISTS `sys_desc_tipos` (
  `id_tipo` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sigla` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando datos para la tabla distribucion.sys_desc_tipos: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_desc_tipos` DISABLE KEYS */;
INSERT INTO `sys_desc_tipos` (`id_tipo`, `tipo`, `sigla`) VALUES
	(1, 'porcentaje', '%'),
	(2, 'efectivo', '$');
/*!40000 ALTER TABLE `sys_desc_tipos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_empleados
CREATE TABLE IF NOT EXISTS `sys_empleados` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) CHARACTER SET latin1 NOT NULL,
  `paterno` varchar(100) CHARACTER SET latin1 NOT NULL,
  `materno` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `genero` enum('Masculino','Femenino') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Masculino',
  `fecha_nacimiento` date NOT NULL DEFAULT '0000-00-00',
  `telefono` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `empresa` varchar(20) DEFAULT '',
  `cargo` varchar(100) CHARACTER SET latin1 DEFAULT '',
  `fecha` date DEFAULT '0000-00-00',
  `hora` time DEFAULT '00:00:00',
  PRIMARY KEY (`id_empleado`)
) ENGINE=MyISAM AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_empleados: 1 rows
/*!40000 ALTER TABLE `sys_empleados` DISABLE KEYS */;
INSERT INTO `sys_empleados` (`id_empleado`, `nombres`, `paterno`, `materno`, `genero`, `fecha_nacimiento`, `telefono`, `empresa`, `cargo`, `fecha`, `hora`) VALUES
	(1, 'checkcode', 'checkcode', '', 'Masculino', '0000-00-00', '', NULL, '1', '2021-03-26', '16:14:20');
/*!40000 ALTER TABLE `sys_empleados` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_instituciones
CREATE TABLE IF NOT EXISTS `sys_instituciones` (
  `id_institucion` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL,
  `lema` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `razon_social` text CHARACTER SET latin1 NOT NULL,
  `propietario` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(1500) NOT NULL DEFAULT '',
  `telefono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nit` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(100) NOT NULL,
  `imagen_encabezado` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pie_pagina` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `formato` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tema` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `empresa1` varchar(20) NOT NULL DEFAULT '',
  `empresa2` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_institucion`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_instituciones: 1 rows
/*!40000 ALTER TABLE `sys_instituciones` DISABLE KEYS */;
INSERT INTO `sys_instituciones` (`id_institucion`, `nombre`, `sigla`, `lema`, `razon_social`, `propietario`, `direccion`, `descripcion`, `telefono`, `nit`, `correo`, `imagen_encabezado`, `pie_pagina`, `formato`, `tema`, `empresa1`, `empresa2`) VALUES
	(1, 'CHECKCODE', 'FA', 'VENTA DE PRODUCTOS', 'VENTA DE PRODUCTOS', 'CHECKCODE', 'CALLE 3 AVENIDA 6 DE MARZO', 'Atención: Lun. a Vie. de 08:30 a 18:30 y Sáb. de 08:30 a 13:00', '6000001', '6000001', 'checkcode@checkcode.bo', 'c1b0f085771c01f9b9614c7574f4d7af.jpg', 'CHECKCODE-DISTRIBUCION', 'Y-m-d', 'bootstrap', 'CHECKCODE', '');
/*!40000 ALTER TABLE `sys_instituciones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_menus
CREATE TABLE IF NOT EXISTS `sys_menus` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `menu` varchar(100) CHARACTER SET latin1 NOT NULL,
  `icono` varchar(100) CHARACTER SET latin1 NOT NULL,
  `ruta` varchar(200) CHARACTER SET latin1 NOT NULL,
  `modulo` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `antecesor_id` int(11) NOT NULL,
  PRIMARY KEY (`id_menu`)
) ENGINE=MyISAM AUTO_INCREMENT=141 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_menus: 105 rows
/*!40000 ALTER TABLE `sys_menus` DISABLE KEYS */;
INSERT INTO `sys_menus` (`id_menu`, `menu`, `icono`, `ruta`, `modulo`, `orden`, `antecesor_id`) VALUES
	(1, 'Módulo Administración', 'dashboard', '', '', 1, 0),
	(2, 'Configuración general', 'cog', '', '', 1, 1),
	(3, 'Apariencia del sistema', 'tint', '?/configuraciones/apariencia', 'configuraciones', 4, 2),
	(4, 'Información de la empresa', 'home', '?/configuraciones/institucion', 'configuraciones', 1, 2),
	(5, 'Ajustes sobre la fecha', 'cog', '?/configuraciones/preferencias', 'configuraciones', 3, 2),
	(6, 'Ajustes sobre los reportes', 'print', '?/configuraciones/reportes', 'configuraciones', 2, 2),
	(7, 'Registro de roles', 'stats', '?/roles/listar', 'roles', 2, 1),
	(9, 'Registro de usuarios', 'user', '?/usuarios/listar', 'usuarios', 5, 1),
	(10, 'Registro de empleados', 'eye-open', '?/empleados/listar', 'empleados', 4, 1),
	(11, 'Módulo General', 'globe', '', '', 2, 0),
	(12, 'Registro de almacenes', 'home', '?/almacenes/listar', 'almacenes', 0, 11),
	(13, 'Registro de tipo de prod', 'tag', '?/tipo/listar', 'tipo', 1, 11),
	(14, 'Registro de monedas', 'piggy-bank', '?/monedas/listar', 'monedas', 2, 11),
	(15, 'Personas', 'user', '', '', 5, 11),
	(103, 'Reporte de Clientes', 'file', '?/clientes/reporte', 'clientes', 0, 79),
	(17, 'Lista de proveedores', 'plane', '?/proveedores/listar', 'proveedores', 0, 15),
	(18, 'Registro de unidades', 'filter', '?/unidades/listar', 'unidades', 3, 11),
	(19, 'Módulo Inventario', 'inbox', '', '', 3, 0),
	(20, 'Catálogo de productos', 'scale', '?/productos/listar', 'productos', 0, 19),
	(21, 'Inventario de productos', 'book', '?/inventarios/listar', 'inventarios', 0, 19),
	(22, 'Lista de precios', 'usd', '?/precios/listar', 'precios', 0, 19),
	(23, 'Stock de productos', 'stats', '?/stocks/listar', 'stocks', 0, 19),
	(25, 'Reporte de existencias', 'search', '?/existencias/listar', 'existencias', 0, 19),
	(26, 'Reporte de ventas', 'stats', '', '', 6, 30),
	(27, 'Módulo Facturación', 'qrcode', '', '', 5, 0),
	(28, 'Registro de terminales', 'phone', '?/terminales/listar', 'terminales', 3, 27),
	(29, 'Registro de dosificaciones', 'lock', '?/dosificaciones/listar', 'dosificaciones', 2, 27),
	(30, 'Módulo Ventas', 'shopping-cart', '', '', 4, 0),
	(31, 'Compras', 'log-in', '?/ingresos/listar', 'ingresos', 0, 19),
	(32, 'Salidas', 'log-out', '?/egresos/listar', 'egresos', 0, 19),
	(33, 'Proformas', 'list-alt', '?/proformas/seleccionar_almacen', 'proformas', 3, 30),
	(34, 'Ventas computarizadas', 'shopping-cart', '?/electronicas/seleccionar_almacen', 'electronicas', 1, 30),
	(35, 'Reporte de ventas generales', 'briefcase', '?/reportes/ventas_generales', 'reportes', 1, 26),
	(36, 'Ventas manuales', 'edit', '?/manuales/seleccionar_almacen', 'manuales', 4, 30),
	(37, 'Reporte de ventas computarizadas', 'qrcode', '?/reportes/ventas_electronicas', 'reportes', 2, 26),
	(38, 'Reporte de ventas manuales', 'paste', '?/reportes/ventas_manuales', 'reportes', 4, 26),
	(39, 'Reporte de ventas personales', 'user', '?/reportes/ventas_personales', 'reportes', 5, 26),
	(41, 'Operaciones', 'list', '', '', 5, 30),
	(42, 'Listado de facturas', 'qrcode', '?/operaciones/facturas_listar', 'operaciones', 1, 41),
	(43, 'Listado de preventas', 'list-alt', '?/operaciones/preventas_listar', 'operaciones', 3, 41),
	(48, 'Notas de remisión', 'edit', '?/notas/seleccionar_almacen', 'notas', 2, 30),
	(124, 'Reporte de utilidades2', 'usd', '?/utilidades/utilidades2', 'utilidades', 0, 132),
	(53, 'Kardex físico y valorado', 'folder-close', '?/kardex/listar', 'kardex', 0, 19),
	(54, 'Listado de notas de remision', 'edit', '?/operaciones/notas_listar', 'operaciones', 2, 41),
	(55, 'Reporte de ventas notas de remisión', 'edit', '?/reportes/ventas_notas', 'reportes', 3, 26),
	(56, 'Reporte de ventas a detalle', 'file', '?/reportes/diario', 'reportes', 7, 132),
	(57, 'Certificación del sistema', 'ok', '?/evaluacion/verificar', 'evaluacion', 1, 27),
	(58, 'Listado de ventas manuales', 'paste', '?/operaciones/manuales_listar', 'operaciones', 0, 41),
	(60, 'Preventas', 'list-alt', '?/preventas/seleccionar_almacen', 'preventas', 0, 30),
	(67, 'Reporte de ventas por empleado', 'user', '?/reporte/listar', 'reporte', 0, 26),
	(129, 'Cuentas clientes', 'user', '', '', 0, 97),
	(76, 'Módulo Distribución', 'inbox', '', '', 0, 0),
	(77, 'Preventista', 'road', '', '', 0, 76),
	(78, 'Distribuidor', 'bed', '', '', 1, 76),
	(79, 'Clientes', 'user', '', '', 3, 76),
	(80, 'Listar', 'file', '?/clientes/listar', 'clientes', 0, 79),
	(81, 'Tipo de Clientes', 'th-large', '?/clientes/crear_tipo', 'clientes', 0, 79),
	(82, 'Rutas', 'map-marker', '?/ruta/listar', 'ruta', 2, 76),
	(128, 'Configuraciones', 'cog', '', '', 4, 76),
	(84, 'Ver preventista', 'shopping-cart', '?/vendedor/listar', 'vendedor', 0, 77),
	(85, 'Historial', 'file', '?/vendedor/historial', 'vendedor', 0, 77),
	(130, 'Cuentas proveeedores', 'object-align-left', '', '', 1, 97),
	(88, 'Promoción de Items', 'file', '?/promociones/promocion_x_item', 'promociones', 0, 87),
	(89, 'Promoción por Monto', 'file', '?/promociones/promocion_x_fecha', 'promociones', 0, 87),
	(90, 'Visitas', 'map-marker', '?/control/seleccionar', 'control', 0, 77),
	(91, 'Asignación', 'map-marker', '?/distribuidor/listar', 'distribuidor', 1, 78),
	(92, 'Ver Distribuidor', 'bed', '?/distribuidor/listar2', 'distribuidor', 0, 78),
	(93, 'Historial', 'file', '?/distribuidor/listar3', 'distribuidor', 2, 78),
	(94, 'Registro Prioridades', 'edit', '?/prioridades/crear_prioridad', 'prioridades', 0, 128),
	(95, 'Registro Motivos No Venta', 'edit', '?/prioridades/crear_motivo', 'prioridades', 0, 128),
	(96, 'Lista de Promos por Monto', 'paste', '?/promociones/reporte_promos_monto', 'promociones', 0, 87),
	(97, 'Módulo Cuentas', 'credit-card', '', '', 0, 0),
	(98, 'Cuentas por cobrar', 'plus', '?/cobrar/listar', 'cobrar', 0, 129),
	(99, 'Cuentas por pagar', 'minus', '?/pagar/plan_pagos', 'pagar', 0, 130),
	(100, 'Cronograma de cuentas', 'calendar', '?/cronograma/cronograma', 'cronograma', 2, 97),
	(101, 'Reporte de clientes', 'file', '?/cobrar/reporte_clientes', 'cobrar', 0, 129),
	(102, 'Reporte de proveedores', 'file', '?/pagar/reporte_proveedores', 'pagar', 0, 130),
	(105, 'Listado de proformas', 'list-alt', '?/operaciones/proformas_listar', 'operaciones', 4, 41),
	(107, 'Reporte de utilidades', 'usd', '?/utilidades/utilidades', 'utilidades', 7, 132),
	(106, 'Promociones Acumuladas', 'gift', '?/promociones/listar_clientes_promo', 'promociones', 0, 87),
	(108, 'Metas', 'queen', '', '', 6, 128),
	(109, 'Autovendedor', 'usd', '', '', 0, 76),
	(110, 'Autoventas', 'shopping-cart', '?/autoventas/crear', 'autoventas', 0, 109),
	(111, 'Ver Autovendedor', 'bed', '?/autoventas/listar_distribucion', 'autoventas', 0, 109),
	(112, 'Historial', 'paste', '?/autoventas/historial', 'autoventas', 0, 109),
	(113, 'Orden de Salida', 'file', '?/autoventas/seleccionar_almacen', 'autoventas', 0, 109),
	(114, 'Cuentas de Materiales Cliente', 'bed', '?/cobrar/lista_material_cliente', 'cobrar', 0, 97),
	(115, 'Cuentas de Materiales Fabrica', 'bed', '?/cobrar/seleccionar_almacen', 'cobrar', 0, 97),
	(116, 'Reporte General Clientes', 'list-alt', '?/cobrar/lista_general', 'cobrar', 0, 129),
	(117, 'Registrar Materiales', 'shopping-cart', '?/materiales/listar', 'materiales', 0, 11),
	(118, 'Recepcion de Cajas', 'list-alt', '?/materiales/lista_recepcion_materiales', 'materiales', 0, 19),
	(119, 'Metas Productos', 'queen', '?/metas-productos/listar', 'metas-productos', 1, 108),
	(120, 'Metas vendedor', 'queen', '?/metas/listar', 'metas', 0, 108),
	(121, 'Metas Categorias', 'queen', '?/metas-categorias/listar', 'metas-categorias', 2, 108),
	(122, 'Reporte de productos', 'list-alt', '?/reportes/productos', 'reportes', 0, 132),
	(125, 'Metas distribuidor', 'queen', '?/metas_distribuidor/listar', 'metas_distribuidor', 0, 108),
	(126, 'Configurar credito de clientes', 'usd', '?/clientes/credito', 'clientes', 0, 79),
	(131, 'Registro de marcas', 'copyright-mark', '?/marcas/listar', 'marcas', 4, 11),
	(132, 'Reportes Generales de venta', 'duplicate', '', '', 7, 30),
	(135, 'Modulo Caja', 'usd', '', '', 0, 0),
	(136, 'Registro de Ingreso Efectivo', 'plus', '?/movimientos/ingresos_listar', 'movimientos', 0, 135),
	(137, 'Registro de Egresos Efectivo', 'minus', '?/movimientos/egresos_listar', 'movimientos', 0, 135),
	(138, 'Registro de Gastos Efectivo', 'minus-sign', '?/movimientos/gastos_listar', 'movimientos', 0, 135),
	(139, 'Reporte Personal Caja', 'user', '?/movimientos/cerrar', 'movimientos', 0, 135),
	(140, 'Reporte General de Caja', 'stats', '?/movimientos/mostrar', 'movimientos', 0, 135);
/*!40000 ALTER TABLE `sys_menus` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_permisos
CREATE TABLE IF NOT EXISTS `sys_permisos` (
  `rol_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `archivos` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`rol_id`,`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_permisos: 291 rows
/*!40000 ALTER TABLE `sys_permisos` DISABLE KEYS */;
INSERT INTO `sys_permisos` (`rol_id`, `menu_id`, `archivos`) VALUES
	(2, 43, 'facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,preventas_obtener,preventa_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(4, 42, 'guardar_devolucionn,preventas_imprimir,notas_obtener,xproformas_imprimir,proformas_ver,buscar,proforma_ver,guardar_devolucion,facturas_listar,facturas_ver,facturas_listar2,facturas_imprimir,notas_actualizar,preventas_devolucion,activar_nota,proformas_modificar,notas_devolucion,guardar,proformas_editar,manuales_ver,notas_listar2,proformas_obtener,preventas_obtener,activar_factura,preventas_facturar,proformas_eliminar,facturas_obtener,listar_manuales,proformas_listar,proformas_facturar,preventas_ver,manuales_eliminar,preventas_listar,preventa_ver,preventas_editar2,proformas_imprimir,notas_imprimir,proformas_devolucion,preventas_editar,preventas_eliminar,notas_ver,manuales_editar,notas_editar,preventas_listar2,notas_listar,facturas_editar'),
	(2, 41, ''),
	(2, 54, 'facturas_editar,facturas_imprimir,facturas_listar,facturas_obtener,facturas_ver,listar_manuales,manuales_editar,manuales_eliminar,manuales_ver,notas_imprimir,notas_listar,notas_obtener,notas_ver,preventas_obtener,preventa_ver,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,xproformas_imprimir'),
	(2, 36, 'actualizar,crear,editar,editar_venta,eliminar,guardar,mostrar,obtener,suprimir,ver'),
	(2, 48, 'actualizar,buscar,crear,crear2,crear3,editar,facturar,guardar,imprimir,mostrar,obtener,seleccionar,ver'),
	(2, 30, ''),
	(2, 34, 'actualizar,buscar,crear,editar,facturar,guardar,mostrar,obtener,ver'),
	(2, 53, 'detallar,imprimir,listar'),
	(2, 32, 'actualizar,crear,eliminar,guardar,imprimir,listar,suprimir,ver'),
	(3, 60, 'a,actualizar,buscar,crear-copia,crear,crear_ant,editar,eliminar,facturar,guardar,guardar2,guardar_ant,guardar_noventa,imprimir,imprimir_nota,modificar,mostrar,noventa,obtener,proformas_editar,proformas_imprimir,proformas_listar,proformas_listar_ant,seleccionar_almacen,ver'),
	(5, 25, 'listar'),
	(5, 21, 'listar'),
	(5, 22, 'listar'),
	(5, 23, 'listar, mostrar'),
	(5, 20, 'imprimir, listar,ver'),
	(5, 13, 'imprimir, listar, ver'),
	(5, 14, 'imprimir, listar, ver'),
	(5, 17, 'imprimir, listar'),
	(6, 19, ''),
	(6, 20, 'disponibles — activar, cambiar, crear, editar, eliminar, generar, generarbc, guardar, guardar_promocion, imprimir, listar, promocion, saltar, subir, suprimir, validar, validar_barras, ver, editar_promocion, ver_promocion, quitar, fijar, ver_precio'),
	(2, 22, 'actualizar,asignar,cambiar,eliminar,fijar,imprimir,listar,quitar'),
	(2, 21, 'listar'),
	(2, 20, 'activar,cambiar,crear,editar,eliminar,generar,generarbc,guardar,imprimir,listar,saltar,subir,suprimir,validar,validar_barras,ver'),
	(2, 25, 'listar'),
	(2, 23, 'listar,mostrar'),
	(2, 13, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(2, 19, ''),
	(2, 31, 'activar,crear,eliminar,guardar-cambio,guardar,imprimir,listar,modificar,suprimir,ver,seleccionar_almacen'),
	(2, 14, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(2, 15, ''),
	(2, 17, 'crear,editar,eliminar,guardar,imprimir,listar'),
	(2, 18, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(2, 11, ''),
	(2, 12, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(2, 9, 'activar,actualizar,asignar,capturar,crear,editar,eliminar,guardar,imprimir,listar,subir,validar,ver'),
	(2, 10, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(5, 15, ''),
	(5, 18, 'imprimir, listar, ver'),
	(5, 12, 'imprimir, listar, ver'),
	(5, 11, ''),
	(2, 8, 'asignar,guardar,listar'),
	(2, 3, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(2, 7, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(2, 5, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(2, 6, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(2, 4, 'apariencia,apariencia_guardar,institucion,institucion_editar,institucion_guardar,preferencias,preferencias_editar,preferencias_guardar,reportes,reportes_editar,reportes_guardar'),
	(2, 2, ''),
	(2, 1, ''),
	(2, 95, ''),
	(2, 94, ''),
	(2, 93, ''),
	(2, 92, ''),
	(2, 91, ''),
	(2, 78, ''),
	(2, 103, ''),
	(12, 34, ''),
	(12, 48, ''),
	(12, 33, ''),
	(12, 36, ''),
	(12, 41, ''),
	(12, 26, ''),
	(12, 56, ''),
	(12, 122, '[reportes] disponibles — diario, ventas_electronicas, productos, ventas_notas, ventas_personales, listar_productos, ventas_generales, ventas_manuales, utilidades_ant, utilidades, utilidades_dia, ventas_generales2'),
	(5, 26, ''),
	(5, 67, ''),
	(5, 35, ''),
	(5, 37, ''),
	(5, 55, ''),
	(5, 38, ''),
	(5, 39, ''),
	(4, 36, 'actualizar,crear,editar,editar_venta,eliminar,guardar,mostrar,obtener,suprimir,ver'),
	(4, 41, ''),
	(4, 58, 'guardar_devolucionn,preventas_imprimir,notas_obtener,xproformas_imprimir,proformas_ver,buscar,proforma_ver,guardar_devolucion,facturas_listar,facturas_ver,facturas_listar2,facturas_imprimir,notas_actualizar,preventas_devolucion,activar_nota,proformas_modificar,notas_devolucion,guardar,proformas_editar,manuales_ver,notas_listar2,proformas_obtener,preventas_obtener,activar_factura,preventas_facturar,proformas_eliminar,facturas_obtener,listar_manuales,proformas_listar,proformas_facturar,preventas_ver,manuales_eliminar,preventas_listar,preventa_ver,preventas_editar2,proformas_imprimir,notas_imprimir,proformas_devolucion,preventas_editar,preventas_eliminar,notas_ver,manuales_editar,notas_editar,preventas_listar2,notas_listar,facturas_editar'),
	(4, 76, ''),
	(12, 60, 'preventas] disponibles — crear1, actualizar, buscar, proformas_listar_ant, a, guardar_noventa, editar, guardar, proformas_editar, imprimir, facturar, seleccionar_almacen, ver, eliminar, proformas_listar, imprimir_nota, guardar1, crear, modificar, proformas_imprimir, obtener, mostrar, noventa'),
	(12, 30, ''),
	(12, 21, ''),
	(12, 32, ''),
	(12, 31, ''),
	(12, 25, ''),
	(12, 53, ''),
	(12, 86, 'imprimir,listar,ver'),
	(12, 23, ''),
	(12, 70, ''),
	(12, 20, ''),
	(12, 22, ''),
	(12, 19, ''),
	(12, 95, 'guardar_motivo,eliminar_motivo,crear_prioridad,crear_motivo,eliminar_prioridad,guardar_prioridad'),
	(2, 79, ''),
	(2, 81, ''),
	(2, 80, ''),
	(2, 82, ''),
	(2, 96, 'imprimir,promocion,validar,listar,generarbc,subir,editar_promocion,guardar_promocion_x_fecha,promocion_x_fecha,eliminar,saltar,ver,generar,asignar,quitar,guardar_promocion,validar_barras,guardar,suprimir,crear,editar,activar,reporte_promos_monto,fijar,cambiar,ver_promocion'),
	(2, 90, ''),
	(2, 89, 'imprimir,promocion,validar,listar,generarbc,subir,editar_promocion,guardar_promocion_x_fecha,promocion_x_fecha,eliminar,saltar,ver,generar,asignar,quitar,guardar_promocion,validar_barras,guardar,suprimir,crear,editar,activar,reporte_promos_monto,fijar,cambiar,ver_promocion'),
	(2, 87, ''),
	(2, 88, 'imprimir,promocion,validar,listar,generarbc,subir,editar_promocion,guardar_promocion_x_fecha,promocion_x_fecha,eliminar,saltar,ver,generar,asignar,quitar,guardar_promocion,validar_barras,guardar,suprimir,crear,editar,activar,reporte_promos_monto,fijar,cambiar,ver_promocion'),
	(2, 85, ''),
	(2, 84, ''),
	(2, 86, ''),
	(2, 76, ''),
	(2, 77, ''),
	(2, 83, ''),
	(12, 87, ''),
	(12, 88, 'promocion,editar_promocion,guardar_promocion_x_fecha,promocion_x_fecha,guardar_promocion,reporte_promos_monto,ver_promocion'),
	(12, 94, 'guardar_motivo,eliminar_motivo,crear_prioridad,crear_motivo,eliminar_prioridad,guardar_prioridad'),
	(12, 93, 'imprimir,imprimir6,activar2,listar,ver3,ver4,listar3,imprimir2,imprimir1,activar3,eliminar,ver,asignar2,imprimir3,asignar,visitas,imprimir7,guardar,imprimir4,imprimir5,editar,listar2,visitas2,ver2,activar'),
	(12, 92, 'imprimir,imprimir6,activar2,listar,ver3,ver4,listar3,imprimir2,imprimir1,activar3,eliminar,ver,asignar2,imprimir3,asignar,visitas,imprimir7,guardar,imprimir4,imprimir5,editar,listar2,visitas2,ver2,activar'),
	(12, 78, ''),
	(12, 91, 'imprimir,imprimir6,activar2,listar,ver3,ver4,listar3,imprimir2,imprimir1,activar3,eliminar,ver,asignar2,imprimir3,asignar,visitas,imprimir7,guardar,imprimir4,imprimir5,editar,listar2,visitas2,ver2,activar'),
	(12, 103, 'imprimir,imprimir_reporte,listar,guardar_tipo,eliminar,guardar,crear,editar,eliminar_tipo,detallar,reporte,crear_tipo'),
	(12, 80, 'imprimir,imprimir_reporte,listar,guardar_tipo,eliminar,guardar,crear,editar,eliminar_tipo,detallar,reporte,crear_tipo'),
	(12, 79, ''),
	(12, 81, 'imprimir,imprimir_reporte,listar,guardar_tipo,eliminar,guardar,crear,editar,eliminar_tipo,detallar,reporte,crear_tipo'),
	(12, 90, 'imprimir,vertodo,listar,vertodo2,eliminar,ver,asignar,historial,visitas,crear2,guardar,crear,seleccionar,editar,crear1,visitas2,asignar_dia'),
	(12, 89, 'promocion,editar_promocion,guardar_promocion_x_fecha,promocion_x_fecha,guardar_promocion,reporte_promos_monto,ver_promocion'),
	(12, 96, 'promocion,editar_promocion,guardar_promocion_x_fecha,promocion_x_fecha,guardar_promocion,reporte_promos_monto,ver_promocion'),
	(13, 76, ''),
	(13, 77, ''),
	(13, 79, ''),
	(13, 80, ''),
	(13, 81, ''),
	(13, 103, ''),
	(13, 83, ''),
	(13, 84, ''),
	(13, 85, ''),
	(13, 78, ''),
	(1, 105, 'activar_factura,activar_manuales,activar_nota,bajas_devoluciones,buscar,facturas_editar,facturas_imprimir,facturas_listar,facturas_listar2,facturas_obtener,facturas_ver,guardar,guardar_conversion,guardar_devolucion,guardar_devolucionn,manuales_actualizar,manuales_editar,manuales_eliminar,manuales_listar,manuales_ver,notas_actualizar,notas_devolucion,notas_editar,notas_imprimir,notas_listar,notas_listar2,notas_obtener,notas_ver,nota_electronica,preventas_devolucion,preventas_editar,preventas_editar2,preventas_eliminar,preventas_facturar,preventas_facturar_directo,preventas_imprimir,preventas_listar,preventas_listar2,preventas_obtener,preventas_ver,preventa_ver,proformas_actualizar,proformas_devolucion,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,proforma_ver,ver_devoluciones,xproformas_imprimir'),
	(1, 43, 'activar_factura,activar_manuales,activar_nota,bajas_devoluciones,buscar,facturas_editar,facturas_imprimir,facturas_listar,facturas_listar2,facturas_obtener,facturas_ver,guardar,guardar_conversion,guardar_devolucion,guardar_devolucionn,manuales_actualizar,manuales_editar,manuales_eliminar,manuales_listar,manuales_ver,notas_actualizar,notas_devolucion,notas_editar,notas_imprimir,notas_listar,notas_listar2,notas_obtener,notas_ver,nota_electronica,preventas_devolucion,preventas_editar,preventas_editar2,preventas_eliminar,preventas_facturar,preventas_facturar_directo,preventas_imprimir,preventas_listar,preventas_listar2,preventas_obtener,preventas_ver,preventa_ver,proformas_actualizar,proformas_devolucion,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,proforma_ver,ver_devoluciones,xproformas_imprimir'),
	(1, 54, 'activar_factura,activar_manuales,activar_nota,bajas_devoluciones,buscar,facturas_editar,facturas_imprimir,facturas_listar,facturas_listar2,facturas_obtener,facturas_ver,guardar,guardar_conversion,guardar_devolucion,guardar_devolucionn,manuales_actualizar,manuales_editar,manuales_eliminar,manuales_listar,manuales_ver,notas_actualizar,notas_devolucion,notas_editar,notas_imprimir,notas_listar,notas_listar2,notas_obtener,notas_ver,nota_electronica,preventas_devolucion,preventas_editar,preventas_editar2,preventas_eliminar,preventas_facturar,preventas_facturar_directo,preventas_imprimir,preventas_listar,preventas_listar2,preventas_obtener,preventas_ver,preventa_ver,proformas_actualizar,proformas_devolucion,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,proforma_ver,ver_devoluciones,xproformas_imprimir'),
	(1, 42, 'activar_factura,activar_manuales,activar_nota,bajas_devoluciones,buscar,facturas_editar,facturas_imprimir,facturas_listar,facturas_listar2,facturas_obtener,facturas_ver,guardar,guardar_conversion,guardar_devolucion,guardar_devolucionn,manuales_actualizar,manuales_editar,manuales_eliminar,manuales_listar,manuales_ver,notas_actualizar,notas_devolucion,notas_editar,notas_imprimir,notas_listar,notas_listar2,notas_obtener,notas_ver,nota_electronica,preventas_devolucion,preventas_editar,preventas_editar2,preventas_eliminar,preventas_facturar,preventas_facturar_directo,preventas_imprimir,preventas_listar,preventas_listar2,preventas_obtener,preventas_ver,preventa_ver,proformas_actualizar,proformas_devolucion,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,proforma_ver,ver_devoluciones,xproformas_imprimir'),
	(3, 30, ''),
	(1, 36, 'actualizar,buscar,buscar_cliente,crear(2),crear,editar,editar_venta,editar_venta_ant,eliminar,guardar,guardar_ant,guardar_c,guardar_copia,guardar_m,mostrar,obtener,seleccionar_almacen,suprimir,ver'),
	(1, 41, ''),
	(1, 58, 'activar_factura,activar_manuales,activar_nota,bajas_devoluciones,buscar,facturas_editar,facturas_imprimir,facturas_listar,facturas_listar2,facturas_obtener,facturas_ver,guardar,guardar_conversion,guardar_devolucion,guardar_devolucionn,manuales_actualizar,manuales_editar,manuales_eliminar,manuales_listar,manuales_ver,notas_actualizar,notas_devolucion,notas_editar,notas_imprimir,notas_listar,notas_listar2,notas_obtener,notas_ver,nota_electronica,preventas_devolucion,preventas_editar,preventas_editar2,preventas_eliminar,preventas_facturar,preventas_facturar_directo,preventas_imprimir,preventas_listar,preventas_listar2,preventas_obtener,preventas_ver,preventa_ver,proformas_actualizar,proformas_devolucion,proformas_editar,proformas_eliminar,proformas_facturar,proformas_imprimir,proformas_listar,proformas_modificar,proformas_obtener,proformas_ver,proforma_ver,ver_devoluciones,xproformas_imprimir'),
	(1, 33, 'a,actualizar,buscar,crear-copia,crear,crear_ant,editar,eliminar,facturar,guardar,guardar_ant,guardar_noventa,imprimir,imprimir_nota,modificar,mostrar,noventa,obtener,proformas_editar,proformas_imprimir,proformas_listar,proformas_listar_ant,seleccionar_almacen,ver'),
	(1, 48, 'activar,actualizar,buscar,crear,crear1,editar,facturar,guardar,guardar1,imprimir,mostrar,obtener,seleccionar,seleccionar_almacen,ver'),
	(1, 34, 'actualizar,buscar,crear,crear1,editar,facturar,guardar,guardar1,imprimir,mostrar,obtener,seleccionar_almacen,ver'),
	(1, 60, 'a,actualizar,buscar,crear,crear1,editar,editar_antiguo,eliminar,facturar,guardar,guardar1,guardar_noventa,imprimir,imprimir_nota,modificar,mostrar,noventa,obtener,proformas_editar,proformas_imprimir,proformas_listar,proformas_listar_ant,ruta_por_empleado,seleccionar_almacen,ver'),
	(1, 30, ''),
	(1, 20, 'asignar,saltar,ver_ant,validar,activar_producto,ver_promocion,subir,listar_eliminados,suprimir,editar,listar_sin_ajax,quitar,guardar,validar_barras,listar_productos,imprimir,listar,eliminados,activar,fijar,generar,ver,eliminar,cambiar,crear,generarbc'),
	(1, 22, 'asignar,actualizar,ver_ant,listar_producto,quitar,imprimir,listar,fijar,ver,eliminar,cambiar'),
	(1, 21, 'listar_producto,listar'),
	(1, 25, 'listar_producto,listar'),
	(1, 23, 'listar_producto,actualizar_stock,listar,buscar_detalle,mostrar'),
	(1, 32, 'libro_venta_excel,actualizar,libro_venta,libro_venta_pdf,suprimir,guardar,imprimir,listar,ver,eliminar,crear,bajas_devoluciones'),
	(1, 53, 'listar_producto,detallar,imprimir,listar'),
	(1, 31, 'guardar-cambio,libro_compra,suprimir,editar,guardar,listar_productos,imprimir,listar1,listar,activar,seleccionar_almacen,ver,eliminar,libro_compra_pdf,guardar1,crear,modificar,libro_compra_excel'),
	(1, 7, 'editar,guardar,imprimir,listar,ver,eliminar,crear'),
	(1, 10, 'imprimir,listar,eliminar,ver,guardar,crear,editar'),
	(1, 9, 'asignar,actualizar,validar,subir,crear_ant,editar,guardar_ant,guardar,imprimir,listar,activar,ver,eliminar,listar_ant,crear,capturar,editar_ant'),
	(1, 11, ''),
	(1, 12, 'editar,guardar,imprimir,listar,ver,eliminar,crear'),
	(1, 13, 'editar,guardar,imprimir,listar,ver,eliminar,crear'),
	(1, 14, 'editar,guardar,imprimir,listar,ver,eliminar,crear'),
	(1, 18, 'crear,editar,eliminar,guardar,imprimir,listar,validar,ver'),
	(1, 131, 'crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 15, ''),
	(1, 17, 'editar,guardar,imprimir,listar,eliminar,crear'),
	(1, 19, ''),
	(1, 3, 'reportes,reportes_guardar,institucion,apariencia,institucion_editar,reportes_editar,preferencias_editar,apariencia_guardar,institucion_guardar,preferencias,preferencias_guardar'),
	(1, 6, 'reportes,reportes_guardar,institucion,apariencia,institucion_editar,reportes_editar,preferencias_editar,apariencia_guardar,institucion_guardar,preferencias,preferencias_guardar'),
	(1, 5, 'reportes,reportes_guardar,institucion,apariencia,institucion_editar,reportes_editar,preferencias_editar,apariencia_guardar,institucion_guardar,preferencias,preferencias_guardar'),
	(1, 1, ''),
	(1, 2, ''),
	(1, 4, 'reportes,reportes_guardar,institucion,apariencia,institucion_editar,reportes_editar,preferencias_editar,apariencia_guardar,institucion_guardar,preferencias,preferencias_guardar'),
	(3, 48, 'obtener,imprimir,buscar,crear_ant,seleccionar_almacen,guardar_ant,ver,mostrar,guardar,crear,seleccionar,editar,facturar,actualizar,activar'),
	(3, 26, ''),
	(3, 56, ''),
	(3, 107, ''),
	(1, 139, 'abrir_caja,api_obtener_cobros,api_obtener_compras,api_obtener_cronogramas,api_obtener_egresos,api_obtener_gastos,api_obtener_ingresos,api_obtener_pagos,api_obtener_ventas,balance_caja,caja,cerrar,cerrar_caja,egresos_crear,egresos_eliminar,egresos_guardar,egresos_imprimir,egresos_listar,egresos_modificar,gastos_crear,gastos_eliminar,gastos_guardar,gastos_imprimir,gastos_listar,gastos_modificar,imprimir,imprimir_general,ingresos_crear,ingresos_eliminar,ingresos_guardar,ingresos_imprimir,ingresos_listar,ingresos_modificar,mostrar,obtener'),
	(16, 101, ''),
	(16, 116, ''),
	(16, 90, ''),
	(16, 106, ''),
	(16, 96, ''),
	(16, 85, ''),
	(16, 89, ''),
	(16, 88, ''),
	(16, 87, ''),
	(16, 86, ''),
	(16, 84, ''),
	(16, 65, ''),
	(16, 76, ''),
	(16, 77, ''),
	(16, 79, ''),
	(16, 80, ''),
	(16, 81, ''),
	(16, 103, ''),
	(16, 82, ''),
	(16, 83, ''),
	(16, 108, ''),
	(16, 120, ''),
	(16, 119, ''),
	(16, 121, ''),
	(16, 19, ''),
	(16, 70, ''),
	(16, 23, ''),
	(16, 22, ''),
	(16, 21, ''),
	(16, 20, ''),
	(16, 30, ''),
	(16, 60, ''),
	(16, 48, ''),
	(16, 33, ''),
	(16, 41, ''),
	(16, 42, ''),
	(16, 54, ''),
	(16, 43, ''),
	(16, 105, ''),
	(16, 26, ''),
	(16, 56, ''),
	(12, 84, 'imprimir,listar,detalle_historial,imprimir2,eliminar,control,ver,asignar,historial,visitas,guardar,crear,editar,activar'),
	(12, 83, ''),
	(12, 85, 'imprimir,listar,detalle_historial,imprimir2,eliminar,control,ver,asignar,historial,visitas,guardar,crear,editar,activar'),
	(12, 82, 'imprimir,listar,imprimir2,eliminar,ver,visitas,guardar,crear,editar,ver2,recorrido'),
	(12, 77, ''),
	(12, 76, ''),
	(1, 138, 'abrir_caja,api_obtener_cobros,api_obtener_compras,api_obtener_cronogramas,api_obtener_egresos,api_obtener_gastos,api_obtener_ingresos,api_obtener_pagos,api_obtener_ventas,balance_caja,caja,cerrar,cerrar_caja,egresos_crear,egresos_eliminar,egresos_guardar,egresos_imprimir,egresos_listar,egresos_modificar,gastos_crear,gastos_eliminar,gastos_guardar,gastos_imprimir,gastos_listar,gastos_modificar,imprimir,imprimir_general,ingresos_crear,ingresos_eliminar,ingresos_guardar,ingresos_imprimir,ingresos_listar,ingresos_modificar,mostrar,obtener'),
	(1, 137, 'abrir_caja,api_obtener_cobros,api_obtener_compras,api_obtener_cronogramas,api_obtener_egresos,api_obtener_gastos,api_obtener_ingresos,api_obtener_pagos,api_obtener_ventas,balance_caja,caja,cerrar,cerrar_caja,egresos_crear,egresos_eliminar,egresos_guardar,egresos_imprimir,egresos_listar,egresos_modificar,gastos_crear,gastos_eliminar,gastos_guardar,gastos_imprimir,gastos_listar,gastos_modificar,imprimir,imprimir_general,ingresos_crear,ingresos_eliminar,ingresos_guardar,ingresos_imprimir,ingresos_listar,ingresos_modificar,mostrar,obtener'),
	(4, 105, 'guardar_devolucionn,preventas_imprimir,notas_obtener,xproformas_imprimir,proformas_ver,buscar,proforma_ver,guardar_devolucion,facturas_listar,facturas_ver,facturas_listar2,facturas_imprimir,notas_actualizar,preventas_devolucion,activar_nota,proformas_modificar,notas_devolucion,guardar,proformas_editar,manuales_ver,notas_listar2,proformas_obtener,preventas_obtener,activar_factura,preventas_facturar,proformas_eliminar,facturas_obtener,listar_manuales,proformas_listar,proformas_facturar,preventas_ver,manuales_eliminar,preventas_listar,preventa_ver,preventas_editar2,proformas_imprimir,notas_imprimir,proformas_devolucion,preventas_editar,preventas_eliminar,notas_ver,manuales_editar,notas_editar,preventas_listar2,notas_listar,facturas_editar'),
	(4, 54, 'guardar_devolucionn,preventas_imprimir,notas_obtener,xproformas_imprimir,proformas_ver,buscar,proforma_ver,guardar_devolucion,facturas_listar,facturas_ver,facturas_listar2,facturas_imprimir,notas_actualizar,preventas_devolucion,activar_nota,proformas_modificar,notas_devolucion,guardar,proformas_editar,manuales_ver,notas_listar2,proformas_obtener,preventas_obtener,activar_factura,preventas_facturar,proformas_eliminar,facturas_obtener,listar_manuales,proformas_listar,proformas_facturar,preventas_ver,manuales_eliminar,preventas_listar,preventa_ver,preventas_editar2,proformas_imprimir,notas_imprimir,proformas_devolucion,preventas_editar,preventas_eliminar,notas_ver,manuales_editar,notas_editar,preventas_listar2,notas_listar,facturas_editar'),
	(4, 43, 'guardar_devolucionn,preventas_imprimir,notas_obtener,xproformas_imprimir,proformas_ver,buscar,proforma_ver,guardar_devolucion,facturas_listar,facturas_ver,facturas_listar2,facturas_imprimir,notas_actualizar,preventas_devolucion,activar_nota,proformas_modificar,notas_devolucion,guardar,proformas_editar,manuales_ver,notas_listar2,proformas_obtener,preventas_obtener,activar_factura,preventas_facturar,proformas_eliminar,facturas_obtener,listar_manuales,proformas_listar,proformas_facturar,preventas_ver,manuales_eliminar,preventas_listar,preventa_ver,preventas_editar2,proformas_imprimir,notas_imprimir,proformas_devolucion,preventas_editar,preventas_eliminar,notas_ver,manuales_editar,notas_editar,preventas_listar2,notas_listar,facturas_editar'),
	(4, 77, ''),
	(4, 79, ''),
	(4, 81, ''),
	(4, 80, 'listar_clientes,crear_tipo,crear_grupo,guardar_tipo,imprimir_reporte,editar,eliminar_tipo,listar_sin_ajax,guardar,detallar,reporte,imprimir,listar,eliminar,listar_ant,eliminar_grupo,crear,cliente_cuenta,guardar_grupo'),
	(4, 78, ''),
	(4, 92, 'asignar,asignar2,ver4,imprimir2_ant,listar2_2,ver2,duplicados_ver,editar,imprimir4,guardar,imprimir3,imprimir4_ant,imprimir,imprimir5,imprimir7,listar,imprimir6_ant,activar,imprimir6,buscar_duplicado,ver,imprimir2,eliminar,ver3,listar3,activar2,visitas2,activar3,imprimir1,listar2,eliminar_duplicado,visitas,imprimir1_termico'),
	(4, 109, ''),
	(4, 30, ''),
	(4, 48, 'actualizar,buscar,crear,crear2,crear3,editar,facturar,guardar,imprimir,mostrar,obtener,seleccionar,ver'),
	(4, 26, ''),
	(4, 56, ''),
	(1, 136, 'abrir_caja,api_obtener_cobros,api_obtener_compras,api_obtener_cronogramas,api_obtener_egresos,api_obtener_gastos,api_obtener_ingresos,api_obtener_pagos,api_obtener_ventas,balance_caja,caja,cerrar,cerrar_caja,egresos_crear,egresos_eliminar,egresos_guardar,egresos_imprimir,egresos_listar,egresos_modificar,gastos_crear,gastos_eliminar,gastos_guardar,gastos_imprimir,gastos_listar,gastos_modificar,imprimir,imprimir_general,ingresos_crear,ingresos_eliminar,ingresos_guardar,ingresos_imprimir,ingresos_listar,ingresos_modificar,mostrar,obtener'),
	(1, 119, 'guardar,listar,ver,eliminar,crear,buscar_producto'),
	(1, 121, 'guardar,listar,ver,eliminar,crear'),
	(1, 135, ''),
	(1, 140, 'abrir_caja,api_obtener_cobros,api_obtener_compras,api_obtener_cronogramas,api_obtener_egresos,api_obtener_gastos,api_obtener_ingresos,api_obtener_pagos,api_obtener_ventas,balance_caja,caja,cerrar,cerrar_caja,egresos_crear,egresos_eliminar,egresos_guardar,egresos_imprimir,egresos_listar,egresos_modificar,gastos_crear,gastos_eliminar,gastos_guardar,gastos_imprimir,gastos_listar,gastos_modificar,imprimir,imprimir_general,ingresos_crear,ingresos_eliminar,ingresos_guardar,ingresos_imprimir,ingresos_listar,ingresos_modificar,mostrar,obtener'),
	(1, 128, ''),
	(1, 94, 'eliminar_prioridad,eliminar_motivo,guardar_prioridad,crear_prioridad,guardar_motivo,crear_motivo'),
	(1, 95, 'eliminar_prioridad,eliminar_motivo,guardar_prioridad,crear_prioridad,guardar_motivo,crear_motivo'),
	(1, 108, ''),
	(1, 120, 'guardar,listar,ver,eliminar,crear'),
	(1, 125, 'guardar,listar,ver,eliminar,crear'),
	(2, 26, ''),
	(2, 35, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 55, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 38, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(2, 39, 'diario,ventas_electronicas,ventas_generales,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 103, 'listar_credito,listar_clientes,editar_credito,crear_tipo,crear_grupo,guardar_tipo,crear_credito,imprimir_reporte,editar,eliminar_tipo,listar_sin_ajax,guardar,detallar,reporte,imprimir,listar,guardar_credito,eliminar,listar_ant,eliminar_grupo,crear,eliminar_credito,cliente_cuenta,guardar_grupo,credito'),
	(1, 81, 'listar_credito,listar_clientes,editar_credito,crear_tipo,crear_grupo,guardar_tipo,crear_credito,imprimir_reporte,editar,eliminar_tipo,listar_sin_ajax,guardar,detallar,reporte,imprimir,listar,guardar_credito,eliminar,listar_ant,eliminar_grupo,crear,eliminar_credito,cliente_cuenta,guardar_grupo,credito'),
	(1, 126, 'listar_credito,listar_clientes,editar_credito,crear_tipo,crear_grupo,guardar_tipo,crear_credito,imprimir_reporte,editar,eliminar_tipo,listar_sin_ajax,guardar,detallar,reporte,imprimir,listar,guardar_credito,eliminar,listar_ant,eliminar_grupo,crear,eliminar_credito,cliente_cuenta,guardar_grupo,credito'),
	(1, 79, ''),
	(1, 80, 'listar_credito,listar_clientes,editar_credito,crear_tipo,crear_grupo,guardar_tipo,crear_credito,imprimir_reporte,editar,eliminar_tipo,listar_sin_ajax,guardar,detallar,reporte,imprimir,listar,guardar_credito,eliminar,listar_ant,eliminar_grupo,crear,eliminar_credito,cliente_cuenta,guardar_grupo,credito'),
	(1, 82, 'recorrido,asignar_color,ver2,editar,guardar,imprimir,listar,ver,imprimir2,eliminar,crear,visitas,guardar_color'),
	(1, 91, 'asignar,asignar2,ver4,imprimir2_ant,listar2_2,ver2,duplicados_ver,editar,imprimir4,guardar,imprimir3,imprimir4_ant,imprimir,imprimir5,imprimir7,listar,imprimir6_ant,activar,imprimir6,buscar_duplicado,ver,imprimir2,eliminar,ver3,listar3,activar2,visitas2,activar3,imprimir1,listar2,eliminar_duplicado,visitas,imprimir1_termico'),
	(1, 93, 'asignar,asignar2,ver4,imprimir2_ant,listar2_2,ver2,duplicados_ver,editar,imprimir4,guardar,imprimir3,imprimir4_ant,imprimir,imprimir5,imprimir7,listar,imprimir6_ant,activar,imprimir6,buscar_duplicado,ver,imprimir2,eliminar,ver3,listar3,activar2,visitas2,activar3,imprimir1,listar2,eliminar_duplicado,visitas,imprimir1_termico'),
	(1, 90, 'crear1,asignar,seleccionar,editar,guardar,imprimir,listar,historial,vertodo,crear2,ver,eliminar,asignar_dia,crear,visitas2,visitas,vertodo2'),
	(1, 78, ''),
	(1, 92, 'activar,activar2,activar3,asignar,asignar2,buscar_duplicado,duplicados_ver,editar,eliminar,eliminar_duplicado,guardar,imprimir,imprimir1,imprimir1_termico,imprimir2,imprimir2_ant,imprimir3,imprimir3_v2,imprimir4,imprimir4_ant,imprimir5,imprimir6,imprimir6_ant,imprimir7,listar,listar2,listar2_2,listar3,ver,ver2,ver3,ver4,visitas,visitas2'),
	(1, 76, ''),
	(1, 77, ''),
	(1, 84, 'imprimir_ant,asignar,editar,guardar,imprimir2_termico,imprimir_termico,listar2(1),imprimir,listar1,listar,historial,activar,control,ver,imprimir2,eliminar,detalle_historial,crear,visitas'),
	(1, 85, 'imprimir_ant,asignar,editar,guardar,imprimir2_termico,imprimir_termico,listar2(1),imprimir,listar1,listar,historial,activar,control,ver,imprimir2,eliminar,detalle_historial,crear,visitas'),
	(1, 100, 'guardar_pagos,reporte_cuentas_pagar,guardar,listar,utilidad,delete,plan_pagos,ver,eliminar,crear,pagar,cronograma'),
	(1, 102, 'imprimir_comprobante,guardar_pago_varios,reporte_cuentas_pagar,guardar_pago,guardar_plan_pagos,utilidad,plan_pagos,ver,eliminar,crear,eliminar_pago,pagar,reporte_proveedores_detalle,reporte_proveedores'),
	(1, 130, ''),
	(1, 99, 'imprimir_comprobante,guardar_pago_varios,reporte_cuentas_pagar,guardar_pago,guardar_plan_pagos,utilidad,plan_pagos,ver,eliminar,crear,eliminar_pago,pagar,reporte_proveedores_detalle,reporte_proveedores'),
	(1, 116, 'guardar_entrega,imprimir_comprobante,guardar_devolucion,detalle_envio,guardar_pago_varios,devolucion,guardar_pago,lista_general,reporte_clientes,guardar_plan_pagos,lista_material_fabrica,listar,utilidad,lista_material_cliente,seleccionar_almacen,eliminar_pago,imprimir_factura,reporte_cuentas_cobrar,pagar,notas_ver,reporte_clientes_detalle,guardar_ingreso'),
	(1, 101, 'guardar_entrega,imprimir_comprobante,guardar_devolucion,detalle_envio,guardar_pago_varios,devolucion,guardar_pago,lista_general,reporte_clientes,guardar_plan_pagos,lista_material_fabrica,listar,utilidad,lista_material_cliente,seleccionar_almacen,eliminar_pago,imprimir_factura,reporte_cuentas_cobrar,pagar,notas_ver,reporte_clientes_detalle,guardar_ingreso'),
	(1, 98, 'guardar_entrega,imprimir_comprobante,guardar_devolucion,detalle_envio,guardar_pago_varios,devolucion,guardar_pago,lista_general,reporte_clientes,guardar_plan_pagos,lista_material_fabrica,listar,utilidad,lista_material_cliente,seleccionar_almacen,eliminar_pago,imprimir_factura,reporte_cuentas_cobrar,pagar,notas_ver,reporte_clientes_detalle,guardar_ingreso'),
	(1, 129, ''),
	(1, 97, ''),
	(1, 26, ''),
	(1, 67, 'crear,editar,eliminar,guardar,imprimir,imprimir2,listar,ver,visitas'),
	(1, 35, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 37, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 55, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 38, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 39, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 132, ''),
	(1, 124, 'utilidades,utilidades2,utilidades_ant,utilidades_BACK,utilidades_dia'),
	(1, 122, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 56, 'diario,listar_productos,productos,utilidades,utilidades_ant,utilidades_dia,ventas_electronicas,ventas_generales,ventas_generales2,ventas_manuales,ventas_notas,ventas_personales'),
	(1, 107, 'utilidades,utilidades2,utilidades_ant,utilidades_BACK,utilidades_dia'),
	(1, 27, ''),
	(1, 57, 'verificar'),
	(1, 29, 'bloquear,crear,editar,eliminar,guardar,imprimir,listar,ver'),
	(1, 28, 'crear,descargar,editar,eliminar,guardar,imprimir,listar,ver');
/*!40000 ALTER TABLE `sys_permisos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_planes
CREATE TABLE IF NOT EXISTS `sys_planes` (
  `id_plan` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `plan` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `observaciones` varchar(1500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `notificacion_id` int(11) NOT NULL,
  `estado` enum('Activo','Inactivo') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Inactivo',
  PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_planes: ~5 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_planes` DISABLE KEYS */;
INSERT INTO `sys_planes` (`id_plan`, `fecha_registro`, `hora_registro`, `plan`, `observaciones`, `notificacion_id`, `estado`) VALUES
	(1, '2021-10-19', '17:13:02', 'A', 'PLAN INICIAL -PLATA', 1, 'Activo'),
	(2, '2021-10-19', '17:13:46', 'B', 'PLAN BASICO - COBRE', 1, 'Inactivo'),
	(3, '2021-10-19', '17:15:02', 'C', 'PLAN BASICO PLUS - ORO', 2, 'Inactivo'),
	(4, '2021-10-19', '17:17:40', 'D', 'PLAN PREMIUM - DIAMANTE', 2, 'Inactivo'),
	(5, '2021-10-19', '17:21:02', 'E', 'PLAN MASTER - RUBI', 3, 'Inactivo');
/*!40000 ALTER TABLE `sys_planes` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_planes_atributos
CREATE TABLE IF NOT EXISTS `sys_planes_atributos` (
  `id_atributo` int(11) NOT NULL AUTO_INCREMENT,
  `atributo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `estado` enum('Visible','Oculto') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Oculto',
  PRIMARY KEY (`id_atributo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_planes_atributos: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_planes_atributos` DISABLE KEYS */;
INSERT INTO `sys_planes_atributos` (`id_atributo`, `atributo`, `estado`) VALUES
	(1, 'rastreo', 'Oculto'),
	(2, 'deuda', 'Oculto'),
	(3, 'devoluciones', 'Oculto'),
	(4, 'editar_preventista', 'Visible'),
	(7, 'libro_compras', 'Oculto'),
	(8, 'libro_ventas', 'Oculto'),
	(9, 'categoria_cliente', 'Visible');
/*!40000 ALTER TABLE `sys_planes_atributos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_planes_caracteristicas
CREATE TABLE IF NOT EXISTS `sys_planes_caracteristicas` (
  `id_caracteristica` int(11) NOT NULL AUTO_INCREMENT,
  `caracteristicas` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `descripcion` varchar(500) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id_caracteristica`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_planes_caracteristicas: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_planes_caracteristicas` DISABLE KEYS */;
INSERT INTO `sys_planes_caracteristicas` (`id_caracteristica`, `caracteristicas`, `descripcion`) VALUES
	(1, 'usuarios', 'control limite de creacion de usuarios'),
	(2, 'rutas', 'control creacion de limite de rutas'),
	(3, 'clientes', 'control creacion limites de clientes'),
	(4, 'almacenes', 'control creacion limites de almacenes'),
	(5, 'sucursales', 'control creacion limites de sucursal'),
	(6, 'productos', 'control creacion limites de productos'),
	(7, 'categorizacion clientes formulario de ventas', 'los precios de formularios de ventas se configura para de acuerdo a la categoria del cliente los precios seran seleccionados de manera predeterminada');
/*!40000 ALTER TABLE `sys_planes_caracteristicas` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_planes_config
CREATE TABLE IF NOT EXISTS `sys_planes_config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL DEFAULT 0,
  `modulo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `configuracion` enum('Habilitado','Deshabilitado') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Deshabilitado',
  `descripcion` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_config`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_planes_config: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_planes_config` DISABLE KEYS */;
INSERT INTO `sys_planes_config` (`id_config`, `plan_id`, `modulo`, `configuracion`, `descripcion`) VALUES
	(1, 1, 'vendedor', 'Deshabilitado', 'Muestra rastreo de preventistas'),
	(2, 1, 'notas', 'Deshabilitado', 'Muestra el total de cada deuda pendiente a cancelar'),
	(3, 1, 'operaciones', 'Deshabilitado', 'Devoluciones gestion registro'),
	(4, 1, 'operaciones', 'Deshabilitado', 'Editar preventas del preventista antes de entrega por el distribuidor'),
	(7, 1, 'ingresos', 'Deshabilitado', 'Libro de compras'),
	(8, 1, 'egresos', 'Deshabilitado', 'Libro de ventas');
/*!40000 ALTER TABLE `sys_planes_config` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_planes_config_detalles
CREATE TABLE IF NOT EXISTS `sys_planes_config_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `config_id` int(11) NOT NULL,
  `atributo_id` int(11) NOT NULL,
  `archivo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_planes_config_detalles: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_planes_config_detalles` DISABLE KEYS */;
INSERT INTO `sys_planes_config_detalles` (`id_detalle`, `config_id`, `atributo_id`, `archivo`) VALUES
	(1, 1, 1, 'listar'),
	(2, 2, 2, 'imprimir'),
	(3, 3, 3, 'preventas_listar'),
	(4, 4, 4, 'preventas_listar'),
	(7, 5, 5, 'listar'),
	(8, 6, 6, 'listar');
/*!40000 ALTER TABLE `sys_planes_config_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_planes_detalles
CREATE TABLE IF NOT EXISTS `sys_planes_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `caracteristica_id` int(11) NOT NULL,
  `limite` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_planes_detalles: ~6 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_planes_detalles` DISABLE KEYS */;
INSERT INTO `sys_planes_detalles` (`id_detalle`, `caracteristica_id`, `limite`, `plan_id`) VALUES
	(1, 1, 40, 1),
	(2, 2, 50, 1),
	(3, 3, 5000, 1),
	(4, 4, 5, 1),
	(5, 5, 5, 1),
	(6, 6, 1000, 1);
/*!40000 ALTER TABLE `sys_planes_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_procesos
CREATE TABLE IF NOT EXISTS `sys_procesos` (
  `id_proceso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_proceso` datetime NOT NULL,
  `hora_proceso` time NOT NULL,
  `proceso` enum('c','r','u','d') CHARACTER SET latin1 NOT NULL,
  `nivel` enum('l','m','h') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `detalle` text NOT NULL,
  `usuario_id` int(11) NOT NULL,
  PRIMARY KEY (`id_proceso`)
) ENGINE=MyISAM AUTO_INCREMENT=206 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_procesos: 205 rows
/*!40000 ALTER TABLE `sys_procesos` DISABLE KEYS */;
INSERT INTO `sys_procesos` (`id_proceso`, `fecha_proceso`, `hora_proceso`, `proceso`, `nivel`, `direccion`, `detalle`, `usuario_id`) VALUES
	(1, '2022-09-01 00:00:00', '17:09:02', 'c', 'l', '?/tipo/guardar', 'Se inserto categoria con identificador numero 17', 1),
	(2, '2022-09-01 00:00:00', '17:09:15', 'c', 'l', '?/unidades/guardar', 'Se inserto la unidad con identificador numero 16', 1),
	(3, '2022-09-01 00:00:00', '17:09:27', 'c', 'l', '?/unidades/guardar', 'Se inserto la unidad con identificador numero 17', 1),
	(4, '2022-09-01 00:00:00', '17:42:15', 'c', 'l', '?/productos/guardar', 'Se inserto el producto con identificador numero 273', 1),
	(5, '2022-09-01 00:00:00', '18:18:48', 'c', 'l', '?/productos/guardar', 'Se inserto el producto con identificador numero 274', 1),
	(6, '2022-09-01 00:00:00', '18:40:57', 'c', 'l', '?/productos/guardar', 'Se inserto el producto con identificador numero 275', 1),
	(7, '2022-09-01 00:00:00', '20:09:49', 'c', 'l', '?/productos/guardar', 'Se inserto el producto con identificador numero 1', 1),
	(8, '2022-09-01 00:00:00', '21:30:49', 'c', 'l', '?/clientes/guardar_tipo', 'Se inserto tipo cliente con identificador numero 15', 1),
	(9, '2022-09-01 00:00:00', '21:31:00', 'd', 'l', '?/clientes/eliminar_tipo', 'Se elimino tipo cliente con identificador numero 15', 1),
	(10, '2022-09-01 00:00:00', '21:31:12', 'c', 'l', '?/clientes/guardar_tipo', 'Se inserto tipo cliente con identificador numero 16', 1),
	(11, '2022-09-01 00:00:00', '21:31:31', 'c', 'l', '?/clientes/guardar_tipo', 'Se inserto tipo cliente con identificador numero 17', 1),
	(12, '2022-09-01 00:00:00', '21:31:45', 'c', 'l', '?/clientes/guardar_tipo', 'Se inserto tipo cliente con identificador numero 18', 1),
	(13, '2022-09-01 00:00:00', '21:31:52', 'd', 'l', '?/clientes/eliminar_tipo', 'Se elimino tipo cliente con identificador numero 16', 1),
	(14, '2022-09-01 00:00:00', '21:32:12', 'c', 'l', '?/clientes/guardar_tipo', 'Se inserto tipo cliente con identificador numero 19', 1),
	(15, '2022-09-01 00:00:00', '21:33:56', 'd', 'l', '?/clientes/eliminar_tipo', 'Se elimino tipo cliente con identificador numero 19', 1),
	(16, '2022-09-01 00:00:00', '21:34:11', 'c', 'l', '?/clientes/guardar_tipo', 'Se inserto tipo cliente con identificador numero 20', 1),
	(17, '2022-09-01 00:00:00', '22:14:44', 'u', 'l', '?/almacenes/guardar', 'Se actualizo almacén con identificador número 0', 1),
	(18, '2022-09-01 00:00:00', '22:14:44', 'c', 'l', '?/almacenes/guardar', 'Se creó almacen con identificador número 22', 1),
	(19, '2022-09-01 00:00:00', '22:15:12', 'c', 'l', '?/almacenes/guardar', 'Se creó almacen con identificador número 23', 1),
	(20, '2022-09-01 00:00:00', '22:29:48', 'c', 'l', '?/monedas/guardar', 'Se inserto la moneda con identificador numero 22', 1),
	(21, '2022-09-01 00:00:00', '22:30:25', 'c', 'l', '?/marcas/guardar', 'Se inserto marca con identificador numero 5', 1),
	(22, '2022-09-01 00:00:00', '22:32:11', 'c', 'l', '?/proveedores/guardar', 'Se inserto proveedor con identificador numero 376', 1),
	(23, '2022-09-01 00:00:00', '22:33:15', 'c', 'l', '?/productos/asignar', 'Se creó asignacion con identificador número 33', 1),
	(24, '2022-09-01 00:00:00', '22:33:15', 'c', 'l', '?/productos/asignar', 'Se creó precio con identificador número 491', 1),
	(25, '2022-09-02 00:00:00', '08:23:15', 'u', 'l', '?/usuarios/guardar', 'Se actualizo usuario con identificador número 1', 1),
	(26, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(27, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(28, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(29, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(30, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(31, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(32, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(33, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(34, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(35, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(36, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(37, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(38, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(39, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(40, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(41, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(42, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(43, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(44, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(45, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(46, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(47, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(48, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(49, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(50, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(51, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(52, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(53, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(54, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(55, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(56, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(57, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(58, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(59, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(60, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(61, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(62, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(63, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(64, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(65, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(66, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(67, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(68, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(69, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(70, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(71, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(72, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(73, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(74, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(75, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(76, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(77, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(78, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(79, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(80, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(81, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(82, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(83, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(84, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(85, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(86, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(87, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(88, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(89, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(90, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(91, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(92, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(93, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(94, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(95, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(96, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(97, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(98, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(99, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(100, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(101, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(102, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(103, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(104, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(105, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(106, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(107, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(108, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(109, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(110, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(111, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(112, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(113, '2022-09-02 00:00:00', '09:03:58', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(114, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(115, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(116, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(117, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(118, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(119, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(120, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(121, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(122, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(123, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(124, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(125, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(126, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(127, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(128, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(129, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(130, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(131, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(132, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(133, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(134, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(135, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(136, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(137, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(138, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(139, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(140, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(141, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(142, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(143, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(144, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(145, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(146, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(147, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(148, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(149, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(150, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(151, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(152, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(153, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(154, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(155, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(156, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(157, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(158, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(159, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(160, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(161, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(162, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(163, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(164, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(165, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(166, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(167, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(168, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(169, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(170, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(171, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(172, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(173, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(174, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(175, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(176, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(177, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(178, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(179, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(180, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(181, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(182, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(183, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(184, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(185, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(186, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(187, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(188, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(189, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(190, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(191, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(192, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(193, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(194, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(195, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(196, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(197, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(198, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(199, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(200, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(201, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(202, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(203, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(204, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1),
	(205, '2022-09-02 00:00:00', '09:15:09', 'c', 'l', '?/permisos/guardar', 'Se creo permiso del rol con identificador 1', 1);
/*!40000 ALTER TABLE `sys_procesos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_procesos_device
CREATE TABLE IF NOT EXISTS `sys_procesos_device` (
  `id_proceso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_proceso` datetime NOT NULL,
  `hora_proceso` time NOT NULL,
  `proceso` enum('c','r','u','d','ce','a') CHARACTER SET latin1 NOT NULL,
  `nivel` enum('l','m','h') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `detalle` text NOT NULL,
  `id_movimiento` int(11) NOT NULL DEFAULT 0,
  `usuario_id` int(11) NOT NULL,
  `imei` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_proceso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_procesos_device: 0 rows
/*!40000 ALTER TABLE `sys_procesos_device` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_procesos_device` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_reportes
CREATE TABLE IF NOT EXISTS `sys_reportes` (
  `id_reporte` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `reporte` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `modulo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `archivo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `habilitado` enum('No','Si') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'No',
  PRIMARY KEY (`id_reporte`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_reportes: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_reportes` DISABLE KEYS */;
INSERT INTO `sys_reportes` (`id_reporte`, `fecha_registro`, `hora_registro`, `reporte`, `modulo`, `archivo`, `habilitado`) VALUES
	(1, '2022-01-14', '10:09:24', 'pdf', 'vendedor', 'imprimir', 'No');
/*!40000 ALTER TABLE `sys_reportes` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_reportes_detalles
CREATE TABLE IF NOT EXISTS `sys_reportes_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `atributo` varchar(500) COLLATE utf8_spanish_ci DEFAULT NULL,
  `detalle` text COLLATE utf8_spanish_ci DEFAULT NULL,
  `reporte_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_reportes_detalles: ~0 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_reportes_detalles` DISABLE KEYS */;
INSERT INTO `sys_reportes_detalles` (`id_detalle`, `atributo`, `detalle`, `reporte_id`) VALUES
	(1, 'titulo', 'COTIZACIÓN', 1);
/*!40000 ALTER TABLE `sys_reportes_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_roles
CREATE TABLE IF NOT EXISTS `sys_roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(100) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_roles: 5 rows
/*!40000 ALTER TABLE `sys_roles` DISABLE KEYS */;
INSERT INTO `sys_roles` (`id_rol`, `rol`, `descripcion`) VALUES
	(1, 'Superusuario', 'Usuario con acceso total del sistema'),
	(2, 'Administrador', 'Usuario con acceso general del sistema'),
	(3, 'Preventistas', 'Usuario con acceso parcial del sistema'),
	(4, 'Distribuidor', 'Encargado de  entregar los pedidos que registraron los vendedores'),
	(5, 'Encargado de almacen', 'Encargado de almacen');
/*!40000 ALTER TABLE `sys_roles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_users
CREATE TABLE IF NOT EXISTS `sys_users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET latin1 NOT NULL,
  `password` varchar(100) CHARACTER SET latin1 NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 NOT NULL,
  `avatar` varchar(100) CHARACTER SET latin1 NOT NULL,
  `active` int(11) NOT NULL,
  `login_at` datetime NOT NULL,
  `logout_at` datetime NOT NULL,
  `rol_id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_user`),
  KEY `rol_id` (`rol_id`),
  KEY `persona_id` (`persona_id`),
  KEY `almacen_id` (`almacen_id`)
) ENGINE=MyISAM AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.sys_users: 1 rows
/*!40000 ALTER TABLE `sys_users` DISABLE KEYS */;
INSERT INTO `sys_users` (`id_user`, `username`, `password`, `email`, `avatar`, `active`, `login_at`, `logout_at`, `rol_id`, `persona_id`, `almacen_id`) VALUES
	(1, 'checkcode', '727e8337449f57726ebf45335e2e0984c7c9cf8a', 'info@dominio.com', 'cc885704c6d1ecd69f310b36a26ec129.jpg', 1, '2022-09-02 08:22:35', '2022-09-02 08:22:35', 1, 1, 22);
/*!40000 ALTER TABLE `sys_users` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_users_devices
CREATE TABLE IF NOT EXISTS `sys_users_devices` (
  `id_device` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `model` varchar(500) COLLATE utf8_spanish_ci NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `imei` varchar(500) COLLATE utf8_spanish_ci NOT NULL,
  `cant_users` int(11) NOT NULL DEFAULT 0,
  `tipo` enum('Basic','Premium') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Basic',
  PRIMARY KEY (`id_device`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_users_devices: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_users_devices` DISABLE KEYS */;
INSERT INTO `sys_users_devices` (`id_device`, `fecha_registro`, `hora_registro`, `model`, `user_id`, `token`, `imei`, `cant_users`, `tipo`) VALUES
	(4, '2022-02-14', '10:55:04', 'Xiaomi M2101K6G', 101, 'fc78ba2c8bee5c39d67b745189f9adf7ec91ebc5', 'b243f63b7dcfb366', 0, 'Premium'),
	(5, '2022-02-15', '14:32:26', 'Xiaomi M2101K6R', 101, '6016c97ab0151f63be5726fc85ebc00253aa30c1', '5ac90add7b824a70', 0, 'Premium');
/*!40000 ALTER TABLE `sys_users_devices` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.sys_users_devices_detalles
CREATE TABLE IF NOT EXISTS `sys_users_devices_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `device_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla distribucion.sys_users_devices_detalles: ~3 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_users_devices_detalles` DISABLE KEYS */;
INSERT INTO `sys_users_devices_detalles` (`id_detalle`, `fecha_registro`, `hora_registro`, `user_id`, `token`, `device_id`) VALUES
	(1, '2022-02-16', '14:16:35', 76, 'b4e785ab71888c308a0d9cf115529b27578c9846', 4),
	(2, '2022-02-16', '14:22:46', 82, '88c59353aa9a43f99e2951f3ea3f9c3608a79b98', 4),
	(3, '2022-02-18', '11:34:23', 102, '38205dc09ed115e8780a299ffecd874bc867c53e', 4);
/*!40000 ALTER TABLE `sys_users_devices_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.tmp_egresos
CREATE TABLE IF NOT EXISTS `tmp_egresos` (
  `id_tmp_egreso` int(11) NOT NULL AUTO_INCREMENT,
  `id_egreso` int(11) NOT NULL,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '',
  `nro_autorizacion` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `codigo_control` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fecha_limite` date NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `descuento_porcentaje` int(11) NOT NULL DEFAULT 0,
  `descuento_bs` decimal(20,2) NOT NULL DEFAULT 0.00,
  `monto_total_descuento` decimal(20,2) NOT NULL DEFAULT 0.00,
  `cliente_id` int(11) NOT NULL DEFAULT 0,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nro_registros` int(11) NOT NULL DEFAULT 0,
  `estadoe` int(11) NOT NULL DEFAULT 0,
  `coordenadas` text DEFAULT NULL,
  `observacion` varchar(150) NOT NULL,
  `dosificacion_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `motivo_id` int(11) NOT NULL DEFAULT 0,
  `motivo` char(120) NOT NULL DEFAULT '0',
  `duracion` time NOT NULL DEFAULT '00:00:00',
  `cobrar` varchar(10) NOT NULL DEFAULT '',
  `grupo` varchar(50) NOT NULL DEFAULT '',
  `descripcion_venta` varchar(150) NOT NULL DEFAULT '',
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_fecha` date DEFAULT NULL,
  `distribuidor_hora` time NOT NULL DEFAULT '00:00:00',
  `distribuidor_estado` enum('DEVUELTO','ALMACEN','ENTREGA','NO ENTREGA','VENTA','ANULADO') NOT NULL,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 0,
  `plan_de_pagos` enum('si','no') NOT NULL DEFAULT 'no',
  `ordenes_salidas_id` int(11) NOT NULL DEFAULT 0,
  `anulado` int(11) NOT NULL DEFAULT 0,
  `factura` enum('Factura','Nota','Ninguno') DEFAULT 'Ninguno',
  `evento` enum('Ninguno','Devuelto') NOT NULL DEFAULT 'Ninguno',
  `accion` enum('Entrega','Venta','VentaDevuelto','Noentrega','Anulado','Devuelto','Eliminado','VentaEliminado') DEFAULT 'Entrega',
  PRIMARY KEY (`id_tmp_egreso`),
  KEY `id_egreso` (`id_egreso`),
  KEY `cliente_id` (`cliente_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `ruta_id` (`ruta_id`),
  KEY `distribuidor_id` (`distribuidor_id`),
  KEY `tipo_provisionado` (`tipo`,`provisionado`),
  KEY `estadoe` (`estadoe`),
  KEY `dosificacion_id` (`dosificacion_id`),
  KEY `motivo_id` (`motivo_id`),
  KEY `estado` (`estado`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  KEY `factura` (`factura`),
  KEY `distribuidor_estado` (`distribuidor_estado`),
  FULLTEXT KEY `nombre_cliente_nit_ci` (`nombre_cliente`,`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.tmp_egresos: 0 rows
/*!40000 ALTER TABLE `tmp_egresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_egresos` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.tmp_egresos_detalles
CREATE TABLE IF NOT EXISTS `tmp_egresos_detalles` (
  `id_tmp_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_detalle` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `tmp_egreso_id` int(11) NOT NULL,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `lote` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_tmp_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.tmp_egresos_detalles: 0 rows
/*!40000 ALTER TABLE `tmp_egresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_egresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.tmp_reposiciones
CREATE TABLE IF NOT EXISTS `tmp_reposiciones` (
  `id_tmp_reposiciones` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_reposicion` date NOT NULL,
  `hora_reposicion` time NOT NULL,
  `empleado_id_reposicion` int(11) NOT NULL DEFAULT 0,
  `id_egreso` int(11) NOT NULL,
  `fecha_egreso` date NOT NULL,
  `hora_egreso` time NOT NULL,
  `tipo` enum('Venta','Traspaso','Baja') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `provisionado` enum('N','S') NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nro_factura` varchar(20) NOT NULL DEFAULT '',
  `nro_autorizacion` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `codigo_control` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fecha_limite` date NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `descuento_porcentaje` int(11) NOT NULL DEFAULT 0,
  `descuento_bs` decimal(20,2) NOT NULL DEFAULT 0.00,
  `monto_total_descuento` decimal(20,2) NOT NULL DEFAULT 0.00,
  `cliente_id` int(11) NOT NULL DEFAULT 0,
  `nombre_cliente` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nit_ci` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nro_registros` int(11) NOT NULL DEFAULT 0,
  `estadoe` int(11) NOT NULL DEFAULT 0,
  `coordenadas` text DEFAULT NULL,
  `observacion` varchar(150) NOT NULL,
  `dosificacion_id` int(11) NOT NULL,
  `almacen_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `motivo_id` int(11) NOT NULL DEFAULT 0,
  `motivo` char(120) NOT NULL DEFAULT '0',
  `duracion` time NOT NULL DEFAULT '00:00:00',
  `cobrar` varchar(10) NOT NULL DEFAULT '',
  `grupo` varchar(50) NOT NULL DEFAULT '',
  `descripcion_venta` varchar(150) NOT NULL DEFAULT '',
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_fecha` date DEFAULT NULL,
  `distribuidor_hora` time NOT NULL DEFAULT '00:00:00',
  `distribuidor_estado` enum('DEVUELTO','ALMACEN','ENTREGA','NO ENTREGA','VENTA','ANULADO') NOT NULL,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `estado` int(11) NOT NULL DEFAULT 0,
  `plan_de_pagos` enum('si','no') NOT NULL DEFAULT 'no',
  `ordenes_salidas_id` int(11) NOT NULL DEFAULT 0,
  `anulado` int(11) NOT NULL DEFAULT 0,
  `factura` enum('Factura','Nota','Ninguno') DEFAULT 'Ninguno',
  `evento` enum('Ninguno','Devuelto') NOT NULL DEFAULT 'Ninguno',
  PRIMARY KEY (`id_tmp_reposiciones`),
  KEY `id_egreso` (`id_egreso`),
  KEY `cliente_id` (`cliente_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `empleado_id_reposicion` (`empleado_id_reposicion`),
  KEY `ruta_id` (`ruta_id`),
  KEY `distribuidor_id` (`distribuidor_id`),
  KEY `tipo_provisionado` (`tipo`,`provisionado`),
  KEY `estadoe` (`estadoe`),
  KEY `dosificacion_id` (`dosificacion_id`),
  KEY `motivo_id` (`motivo_id`),
  KEY `estado` (`estado`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  KEY `factura` (`factura`),
  KEY `distribuidor_estado` (`distribuidor_estado`),
  FULLTEXT KEY `nombre_cliente_nit_ci` (`nombre_cliente`,`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.tmp_reposiciones: 0 rows
/*!40000 ALTER TABLE `tmp_reposiciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_reposiciones` ENABLE KEYS */;

-- Volcando estructura para tabla distribucion.tmp_reposiciones_detalles
CREATE TABLE IF NOT EXISTS `tmp_reposiciones_detalles` (
  `id_tmp_detalle_reposicion` int(11) NOT NULL AUTO_INCREMENT,
  `tmp_reposiciones_id` int(11) NOT NULL,
  `id_detalle` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `egreso_id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL DEFAULT 0,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `lote` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_tmp_detalle_reposicion`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=169 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla distribucion.tmp_reposiciones_detalles: 0 rows
/*!40000 ALTER TABLE `tmp_reposiciones_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `tmp_reposiciones_detalles` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
