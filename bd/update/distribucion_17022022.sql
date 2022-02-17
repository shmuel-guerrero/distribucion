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

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_asignaciones
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_egresos
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
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_egresos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=183 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_ingresos
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

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_ingresos_detalles
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

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_pagos
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
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_pagos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_inv_productos
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_tmp_egresos
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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.backup_tmp_egresos_detalles
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
  `accion_id_backup` int(11) NOT NULL,
  `accion_backup` enum('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup',
  PRIMARY KEY (`backup_id_tmp_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.caj_movimientos
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
  PRIMARY KEY (`id_movimiento`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_asiento
CREATE TABLE IF NOT EXISTS `con_asiento` (
  `codigo` int(11) NOT NULL AUTO_INCREMENT,
  `cuenta` varchar(30) NOT NULL,
  `debe` double NOT NULL,
  `haber` double NOT NULL,
  `factura` int(11) NOT NULL DEFAULT 0,
  `comprobante` int(11) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_asientos_automaticos
CREATE TABLE IF NOT EXISTS `con_asientos_automaticos` (
  `id_automatico` int(11) NOT NULL AUTO_INCREMENT,
  `titulo_automatico` varchar(200) NOT NULL,
  `detalle_automatico` varchar(300) NOT NULL,
  `estado` enum('si','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`id_automatico`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_asientos_menus
CREATE TABLE IF NOT EXISTS `con_asientos_menus` (
  `id_asiento_menu` int(11) NOT NULL AUTO_INCREMENT,
  `automatico_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  PRIMARY KEY (`id_asiento_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_clasificacion
CREATE TABLE IF NOT EXISTS `con_clasificacion` (
  `id_clasificacion` int(11) NOT NULL,
  `clasificacion` varchar(50) NOT NULL,
  `tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_comprobante
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_cuenta
CREATE TABLE IF NOT EXISTS `con_cuenta` (
  `id_cuenta` int(11) NOT NULL,
  `n_cuenta` int(11) NOT NULL,
  `cuenta` varchar(50) NOT NULL,
  `estado` int(11) NOT NULL,
  `actividad` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_datos_empresa
CREATE TABLE IF NOT EXISTS `con_datos_empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `razon_social` varchar(50) NOT NULL,
  `pais` varchar(20) NOT NULL,
  `ciudad` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_detalles_automaticos
CREATE TABLE IF NOT EXISTS `con_detalles_automaticos` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `automatico_id` int(11) NOT NULL,
  `plan_id` varchar(30) NOT NULL,
  `porcentaje` float NOT NULL,
  `tipo` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_factura
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_menus
CREATE TABLE IF NOT EXISTS `con_menus` (
  `id_menu` int(11) NOT NULL,
  `menu` varchar(100) NOT NULL,
  `estado` int(11) NOT NULL,
  `descripcion` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_plan
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_tipo_moneda
CREATE TABLE IF NOT EXISTS `con_tipo_moneda` (
  `id_moneda` int(11) NOT NULL,
  `moneda` varchar(50) NOT NULL,
  `sigla` varchar(20) NOT NULL,
  `valor` float NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.con_ufv
CREATE TABLE IF NOT EXISTS `con_ufv` (
  `id_ufv` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `mes` varchar(20) NOT NULL,
  `dias` int(11) NOT NULL,
  `ufv` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.cronograma
CREATE TABLE IF NOT EXISTS `cronograma` (
  `id_cronograma` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT '0000-00-00',
  `periodo` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `detalle` text CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `monto` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_cronograma`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.cronograma_cuentas
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_asigna_distribucion
CREATE TABLE IF NOT EXISTS `gps_asigna_distribucion` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `ruta_id` int(11) NOT NULL,
  `grupo_id` varchar(50) DEFAULT '',
  `distribuidor_id` int(11) NOT NULL,
  `fecha_ini` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `distribuidor_id` (`distribuidor_id`),
  KEY `ruta_id` (`ruta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_historial_asinacion
CREATE TABLE IF NOT EXISTS `gps_historial_asinacion` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `ruta_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `fecha_ini` date NOT NULL DEFAULT '0000-00-00',
  `fecha_fin` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id_historial`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_historial_distribuidores_vendedores
CREATE TABLE IF NOT EXISTS `gps_historial_distribuidores_vendedores` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `ruta_id` int(11) NOT NULL DEFAULT 0,
  `vendedor_id` int(11) NOT NULL DEFAULT 0,
  `distribuidor_id` int(11) NOT NULL DEFAULT 0,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `grupo_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_historial`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_noventa_motivos
CREATE TABLE IF NOT EXISTS `gps_noventa_motivos` (
  `id_motivo` int(11) NOT NULL AUTO_INCREMENT,
  `motivo` varchar(200) NOT NULL,
  PRIMARY KEY (`id_motivo`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_no_venta
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_rutas
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
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.gps_seguimientos
CREATE TABLE IF NOT EXISTS `gps_seguimientos` (
  `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT,
  `coordenadas` longtext NOT NULL,
  `fecha_seguimiento` date NOT NULL DEFAULT '0000-00-00',
  `hora_seguimiento` longtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id_seguimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.hist_conversiones
CREATE TABLE IF NOT EXISTS `hist_conversiones` (
  `id_conversion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `id_origen` int(11) NOT NULL DEFAULT 0,
  `origen_movimiento` enum('Electronicas','Proforma','NotaRemision','Reserva','Preventa') NOT NULL,
  `id_destino` int(11) NOT NULL DEFAULT 0,
  `destino_movimiento` enum('Electronicas','NotaRemision','Manuales','Preventa') NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  `tipo` enum('ConversionDirecta','ConversionEdicion') NOT NULL DEFAULT 'ConversionDirecta',
  `id_backup_egreso` int(11) NOT NULL DEFAULT 0,
  `ids_backup_detalles` varchar(250) NOT NULL DEFAULT '',
  `dispositivo` enum('Movil','Web') NOT NULL DEFAULT 'Movil',
  PRIMARY KEY (`id_conversion`),
  KEY `id_origen` (`id_origen`),
  KEY `empleado_id` (`empleado_id`),
  KEY `id_destino` (`id_destino`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_almacenes
CREATE TABLE IF NOT EXISTS `inv_almacenes` (
  `id_almacen` int(11) NOT NULL AUTO_INCREMENT,
  `almacen` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `telefono` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `principal` enum('N','S') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_almacen`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_asignaciones
CREATE TABLE IF NOT EXISTS `inv_asignaciones` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad_unidad` int(11) NOT NULL,
  `otro_precio` decimal(20,2) NOT NULL,
  `visible` enum('s','n') NOT NULL DEFAULT 's',
  PRIMARY KEY (`id_asignacion`),
  KEY `producto_id` (`producto_id`),
  KEY `unidad_id` (`unidad_id`),
  KEY `visible` (`visible`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_categorias
CREATE TABLE IF NOT EXISTS `inv_categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(100) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `categoria_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_clientes
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
) ENGINE=MyISAM AUTO_INCREMENT=4987 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_clientes_grupos
CREATE TABLE IF NOT EXISTS `inv_clientes_grupos` (
  `id_cliente_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_grupo` varchar(50) NOT NULL,
  `descuento_grupo` int(11) NOT NULL DEFAULT 0,
  `credito_grupo` enum('no','si') NOT NULL DEFAULT 'si',
  `permiso_grupo` int(11) NOT NULL DEFAULT 0,
  `estado_grupo` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_cliente_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_control
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_control_detalle
CREATE TABLE IF NOT EXISTS `inv_control_detalle` (
  `id_control_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `control_id` int(11) NOT NULL,
  `stock` enum('ingreso','egreso') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `ordenes_salidas_id` int(11) NOT NULL,
  PRIMARY KEY (`id_control_detalle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_detalle_ingreso_material
CREATE TABLE IF NOT EXISTS `inv_detalle_ingreso_material` (
  `cantidad` int(11) DEFAULT NULL,
  `ingreso_material_id` int(11) DEFAULT NULL,
  `materiales_stock_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_dosificaciones
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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos
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
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_anular
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=121 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_anular
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_editar_post
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_editar_previo
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_eliminar_post
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_eliminar_previo
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_entregas
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_noentregas
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
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_noventas
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_detalles_ventas_editadas
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_editar_post
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
  KEY `empleado_id_accion` (`empleado_id_accion`),
  FULLTEXT KEY `nombre_cliente` (`nombre_cliente`),
  FULLTEXT KEY `nit_ci` (`nit_ci`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_editar_previo
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
) ENGINE=MyISAM AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_eliminar_post
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
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_eliminar_previo
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_entregas
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
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_noentregas
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_noventas
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_ventas_editadas
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_ingresos
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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_ingresos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_ingreso_material
CREATE TABLE IF NOT EXISTS `inv_ingreso_material` (
  `id_ingreso_material` int(11) NOT NULL AUTO_INCREMENT,
  `Fecha` date DEFAULT NULL,
  `Planilla` varchar(15) DEFAULT NULL,
  `Placa` varchar(15) DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_ingreso_material`),
  KEY `empleado_id` (`empleado_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_marcas
CREATE TABLE IF NOT EXISTS `inv_marcas` (
  `id_marca` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `descripcion` varchar(1500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_marca`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_materiales
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_materiales_stock
CREATE TABLE IF NOT EXISTS `inv_materiales_stock` (
  `id_materiales_stock` int(11) NOT NULL AUTO_INCREMENT,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `almacen_id` int(11) NOT NULL,
  `materiales_id` int(11) NOT NULL,
  PRIMARY KEY (`id_materiales_stock`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_meta
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_metas_distribuidor
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_meta_categoria
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_meta_producto
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_monedas
CREATE TABLE IF NOT EXISTS `inv_monedas` (
  `id_moneda` int(11) NOT NULL AUTO_INCREMENT,
  `moneda` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL,
  `oficial` enum('N','S') CHARACTER SET latin1 NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id_moneda`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_ordenes_detalles
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_ordenes_salidas
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_pagos
CREATE TABLE IF NOT EXISTS `inv_pagos` (
  `id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `movimiento_id` int(11) DEFAULT 0,
  `interes_pago` float NOT NULL DEFAULT 0,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `movimiento_id` (`movimiento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_pagos_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=319 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_participantes_promos
CREATE TABLE IF NOT EXISTS `inv_participantes_promos` (
  `id_participante_promo` int(11) NOT NULL AUTO_INCREMENT,
  `promocion_monto_id` int(11) NOT NULL,
  `cliente_grupo_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  PRIMARY KEY (`id_participante_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_precios
CREATE TABLE IF NOT EXISTS `inv_precios` (
  `id_precio` int(11) NOT NULL AUTO_INCREMENT,
  `precio` decimal(10,2) NOT NULL,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `asignacion_id` int(11) NOT NULL DEFAULT 0,
  `producto_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_precio`)
) ENGINE=MyISAM AUTO_INCREMENT=478 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_prioridades_ventas
CREATE TABLE IF NOT EXISTS `inv_prioridades_ventas` (
  `id_prioridad_venta` int(11) NOT NULL AUTO_INCREMENT,
  `prioridad` varchar(100) NOT NULL,
  PRIMARY KEY (`id_prioridad_venta`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_productos
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
) ENGINE=MyISAM AUTO_INCREMENT=261 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_proformas
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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_proformas_detalles
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
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_promociones
CREATE TABLE IF NOT EXISTS `inv_promociones` (
  `id_promocion` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `unidad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descuento` int(11) NOT NULL DEFAULT 0,
  KEY `producto_id` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_promociones_monto
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_proveedores
CREATE TABLE IF NOT EXISTS `inv_proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor` varchar(200) NOT NULL,
  `nit` varchar(50) NOT NULL DEFAULT '',
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_requisitos_promo
CREATE TABLE IF NOT EXISTS `inv_requisitos_promo` (
  `id_requisitos` int(11) NOT NULL AUTO_INCREMENT,
  `promocion_monto_id` int(11) NOT NULL,
  `productos_id` int(11) NOT NULL,
  PRIMARY KEY (`id_requisitos`),
  KEY `productos_id` (`productos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_terminales
CREATE TABLE IF NOT EXISTS `inv_terminales` (
  `id_terminal` int(11) NOT NULL AUTO_INCREMENT,
  `terminal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `identificador` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `impresora` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_terminal`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_tipos_clientes
CREATE TABLE IF NOT EXISTS `inv_tipos_clientes` (
  `id_tipo_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_cliente` varchar(50) NOT NULL,
  PRIMARY KEY (`id_tipo_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_unidades
CREATE TABLE IF NOT EXISTS `inv_unidades` (
  `id_unidad` int(11) NOT NULL AUTO_INCREMENT,
  `unidad` varchar(100) CHARACTER SET latin1 NOT NULL,
  `sigla` varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tamanio` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_unidad`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_empleados
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
) ENGINE=MyISAM AUTO_INCREMENT=125 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_instituciones
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_menus
CREATE TABLE IF NOT EXISTS `sys_menus` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `menu` varchar(100) CHARACTER SET latin1 NOT NULL,
  `icono` varchar(100) CHARACTER SET latin1 NOT NULL,
  `ruta` varchar(200) CHARACTER SET latin1 NOT NULL,
  `modulo` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `antecesor_id` int(11) NOT NULL,
  PRIMARY KEY (`id_menu`)
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_permisos
CREATE TABLE IF NOT EXISTS `sys_permisos` (
  `rol_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `archivos` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`rol_id`,`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_planes
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_planes_atributos
CREATE TABLE IF NOT EXISTS `sys_planes_atributos` (
  `id_atributo` int(11) NOT NULL AUTO_INCREMENT,
  `atributo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `estado` enum('Visible','Oculto') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Oculto',
  PRIMARY KEY (`id_atributo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_planes_caracteristicas
CREATE TABLE IF NOT EXISTS `sys_planes_caracteristicas` (
  `id_caracteristica` int(11) NOT NULL AUTO_INCREMENT,
  `caracteristicas` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `descripcion` varchar(500) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id_caracteristica`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_planes_config
CREATE TABLE IF NOT EXISTS `sys_planes_config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `plan_id` int(11) NOT NULL DEFAULT 0,
  `modulo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `configuracion` enum('Habilitado','Deshabilitado') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Deshabilitado',
  `descripcion` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_config`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_planes_config_detalles
CREATE TABLE IF NOT EXISTS `sys_planes_config_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `config_id` int(11) NOT NULL,
  `atributo_id` int(11) NOT NULL,
  `archivo` varchar(500) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_planes_detalles
CREATE TABLE IF NOT EXISTS `sys_planes_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `caracteristica_id` int(11) NOT NULL,
  `limite` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_procesos
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
) ENGINE=MyISAM AUTO_INCREMENT=22568 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_procesos_device
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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_reportes
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_reportes_detalles
CREATE TABLE IF NOT EXISTS `sys_reportes_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `atributo` varchar(500) COLLATE utf8_spanish_ci DEFAULT NULL,
  `detalle` text COLLATE utf8_spanish_ci DEFAULT NULL,
  `reporte_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_roles
CREATE TABLE IF NOT EXISTS `sys_roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(100) CHARACTER SET latin1 NOT NULL,
  `descripcion` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_users
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
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_users_devices
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_users_devices_detalles
CREATE TABLE IF NOT EXISTS `sys_users_devices_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `device_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.tmp_egresos
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
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.tmp_egresos_detalles
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
  KEY `asignacion_id` (`asignacion_id`),
  KEY `tmp_egreso_id` (`tmp_egreso_id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.tmp_reposiciones
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.tmp_reposiciones_detalles
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

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
