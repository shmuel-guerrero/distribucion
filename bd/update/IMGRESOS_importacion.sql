-- --------------------------------------------------------
-- Host:                         95.216.181.83
-- Versión del servidor:         10.3.35-MariaDB-cll-lve - MariaDB Server
-- SO del servidor:              Linux
-- HeidiSQL Versión:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla masivosebx_beta.backup_inv_egresos
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
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla masivosebx_beta.backup_inv_egresos: 24 rows
/*!40000 ALTER TABLE `backup_inv_egresos` DISABLE KEYS */;
INSERT INTO `backup_inv_egresos` (`backup_id_egreso`, `id_egreso`, `fecha_egreso`, `hora_egreso`, `tipo`, `provisionado`, `descripcion`, `nro_factura`, `nro_autorizacion`, `codigo_control`, `fecha_limite`, `monto_total`, `descuento_porcentaje`, `descuento_bs`, `monto_total_descuento`, `cliente_id`, `nombre_cliente`, `nit_ci`, `nro_registros`, `estadoe`, `coordenadas`, `observacion`, `dosificacion_id`, `almacen_id`, `empleado_id`, `motivo_id`, `motivo`, `duracion`, `cobrar`, `grupo`, `descripcion_venta`, `ruta_id`, `estado`, `plan_de_pagos`, `ordenes_salidas_id`, `anulado`, `factura`, `evento`, `delet_fecha_egreso`, `delet_hora_egreso`, `delet_empleado_id`) VALUES
	(1, 1, '2021-12-14', '12:49:11', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 2313.00, 0, 0.00, 2313.00, 3991, 'Yovana Ticona .', '', 6, 2, '-16.51164,-68.159011', 'Tarde', 0, 21, 144, 0, '', '00:02:37', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '14:15:29', 145),
	(2, 1, '2021-12-14', '12:49:11', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 2303.00, 0, 0.00, 0.00, 3991, 'Yovana Ticona .', '', 6, 2, '-16.51164,-68.159011', 'Tarde', 0, 21, 144, 0, '', '00:02:37', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '14:22:47', 145),
	(3, 1, '2021-12-14', '12:49:11', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 2303.00, 0, 0.00, 0.00, 3991, 'Yovana Ticona .', '', 6, 2, '-16.51164,-68.159011', 'Tarde', 0, 21, 144, 0, '', '00:02:37', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '14:26:32', 145),
	(4, 2, '2021-12-14', '12:51:40', 'Venta', 'S', 'Venta de productos con preventa', 2, '', '', '0000-00-00', 2496.00, 0, 0.00, 2496.00, 339, 'Daniel Muñoz .', '', 6, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:02:22', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '14:47:34', 145),
	(5, 2, '2021-12-14', '12:51:40', 'Venta', 'S', 'Venta de productos con preventa', 2, '', '', '0000-00-00', 2436.00, 0, 0.00, 0.00, 339, 'Daniel Muñoz .', '', 6, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:02:22', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '15:00:28', 145),
	(6, 2, '2021-12-14', '12:51:40', 'Venta', 'S', 'Venta de productos con preventa', 2, '', '', '0000-00-00', 2004.00, 0, 0.00, 0.00, 339, 'Daniel Muñoz .', '', 6, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:02:22', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '15:08:41', 145),
	(7, 2, '2021-12-14', '12:51:40', 'Venta', 'S', 'Venta de productos con preventa', 2, '', '', '0000-00-00', 2004.00, 0, 0.00, 0.00, 339, 'Daniel Muñoz .', '', 6, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:02:22', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-14', '15:12:27', 145),
	(8, 10, '2021-12-15', '12:13:40', 'Venta', 'S', 'Venta de productos con preventa', 10, '', '', '0000-00-00', 1250.00, 0, 0.00, 1250.00, 4424, 'TEST ERICK CLIENTE', '7575757575', 1, 2, '-16.5072947,-68.1627004', 'Tarde', 0, 21, 141, 0, '', '00:01:55', '', '', '', 6, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-15', '14:52:41', 143),
	(9, 13, '2021-12-15', '14:06:26', 'Venta', 'S', 'Venta de productos con preventa', 13, '', '', '0000-00-00', 2102.50, 0, 0.00, 2102.50, 339, 'Daniel Muñoz .', '', 9, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:00:11', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-15', '15:00:05', 145),
	(10, 13, '2021-12-15', '14:06:26', 'Venta', 'S', 'Venta de productos con preventa', 13, '', '', '0000-00-00', 2082.50, 0, 0.00, 0.00, 339, 'Daniel Muñoz .', '', 9, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:00:11', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-15', '15:00:21', 145),
	(11, 13, '2021-12-15', '14:06:26', 'Venta', 'S', 'Venta de productos con preventa', 13, '', '', '0000-00-00', 2042.50, 0, 0.00, 0.00, 339, 'Daniel Muñoz .', '', 9, 2, '-16.511578,-68.159057', 'Tarde', 0, 21, 144, 0, '', '00:00:11', '', '', '', 1, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-15', '15:01:22', 145),
	(12, 3, '2021-12-14', '12:52:00', 'Venta', 'S', 'Venta de productos con preventa', 3, '', '', '0000-00-00', 625.00, 0, 0.00, 625.00, 4424, 'TEST ERICK CLIENTE', '7575757575', 1, 2, '-16.5072947,-68.1627004', 'Mañana', 0, 21, 141, 0, '', '00:03:21', '', '', 'test', 3, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-15', '15:11:08', 143),
	(13, 1, '2021-12-16', '11:08:10', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 172.00, 0, 0.00, 172.00, 4428, 'ABCDEFG', '123456789', 2, 2, '-16.5073505,-68.1627228', 'Mañana', 0, 21, 142, 0, '', '00:00:23', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '12:09:48', 140),
	(14, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 418.00, 0, 0.00, 418.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '15:24:23', 140),
	(15, 2, '2021-12-16', '15:12:00', 'Venta', 'S', 'Venta de productos con preventa', 2, '', '', '0000-00-00', 2635.00, 0, 0.00, 2635.00, 4418, 'CLIENTE DE PRUEBA', '0', 4, 2, '-16.507387161254883,-68.16307067871094', 'Todo el día', 0, 21, 147, 0, '', '00:00:53', '', '', '', 2, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '15:26:41', 146),
	(16, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 376.00, 0, 0.00, 376.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:18:41', 140),
	(17, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 355.00, 0, 0.00, 355.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:19:59', 140),
	(18, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 334.00, 0, 0.00, 334.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:20:46', 140),
	(19, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 313.00, 0, 0.00, 313.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:25:08', 140),
	(20, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 250.00, 0, 0.00, 0.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:29:12', 140),
	(21, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 224.00, 0, 0.00, 224.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:31:20', 140),
	(22, 1, '2021-12-16', '14:54:22', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 198.00, 0, 0.00, 198.00, 4429, 'NONGUNA', '11070630', 2, 2, '-16.5073468,-68.1627073', 'Mañana', 0, 21, 142, 0, '', '00:03:57', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-16', '16:49:16', 140),
	(23, 2, '2021-12-17', '09:54:33', 'Venta', 'S', 'Venta de productos con preventa', 2, '', '', '1000-01-01', 75.00, 0, 0.00, 75.00, 4431, 'jhon macley', '132465798', 3, 2, '-17, -65', 'Mañana', 0, 21, 141, 0, '', '00:00:00', '', '', 'test', 13, 1, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-17', '09:59:41', 1),
	(24, 1, '2021-12-17', '17:21:01', 'Venta', 'S', 'Venta de productos con preventa', 1, '', '', '0000-00-00', 800.00, 0, 0.00, 800.00, 5, 'TEST CLIENTE', '949449494', 2, 2, '-16.5073479,-68.1627054', 'Mañana', 0, 21, 142, 0, '', '00:00:56', '', '', '', 8, 0, 'no', 0, 0, 'Ninguno', 'Ninguno', '2021-12-17', '18:53:42', 140);
/*!40000 ALTER TABLE `backup_inv_egresos` ENABLE KEYS */;

-- Volcando estructura para tabla masivosebx_beta.backup_inv_egresos_detalles
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
  PRIMARY KEY (`backup_id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `egreso_id` (`egreso_id`),
  KEY `asignacion_id` (`asignacion_id`),
  KEY `promocion_id` (`promocion_id`),
  KEY `unidad_id` (`unidad_id`),
  FULLTEXT KEY `lote` (`lote`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla masivosebx_beta.backup_inv_egresos_detalles: 30 rows
/*!40000 ALTER TABLE `backup_inv_egresos_detalles` DISABLE KEYS */;
INSERT INTO `backup_inv_egresos_detalles` (`backup_id_detalle`, `id_detalle`, `precio`, `unidad_id`, `cantidad`, `descuento`, `producto_id`, `egreso_id`, `promocion_id`, `asignacion_id`, `lote`, `delet_fecha_egreso`, `delet_hora_egreso`, `delet_empleado_id`) VALUES
	(1, 1, 60.00, 9, 18, 0, 265, 1, 0, 0, 'lt1-18', '2021-12-14', '14:15:29', 145),
	(2, 3, 90.00, 3, 36, 0, 268, 1, 0, 0, 'lt1-4', '2021-12-14', '14:22:47', 145),
	(3, 23, 45.00, 9, 36, 0, 268, 1, 0, 0, 'lt1-4', '2021-12-14', '14:26:32', 145),
	(4, 7, 120.00, 3, 36, 0, 265, 2, 0, 0, 'lt1-36', '2021-12-14', '14:47:34', 145),
	(5, 9, 324.00, 3, 24, 0, 267, 2, 0, 0, '', '2021-12-14', '15:00:28', 145),
	(6, 10, 228.00, 9, 30, 0, 266, 2, 0, 0, 'lt1-30', '2021-12-14', '15:08:41', 145),
	(7, 26, 54.00, 9, 24, 0, 267, 2, 0, 0, '', '2021-12-14', '15:12:27', 145),
	(8, 34, 12.50, 4, 100, 0, 121, 10, 0, 0, 'lt2-100', '2021-12-15', '14:52:41', 143),
	(9, 13, 125.00, 3, 200, 0, 121, 3, 0, 0, 'lt1-100,lt2-100', '2021-12-15', '14:53:22', 143),
	(10, 14, 50.00, 3, 80, 0, 9, 3, 0, 0, 'lt1-72,lt2-8', '2021-12-15', '14:53:46', 143),
	(11, 53, 60.00, 5, 15, 0, 12, 13, 0, 0, 'lt1-15', '2021-12-15', '15:00:05', 145),
	(12, 54, 120.00, 3, 24, 0, 264, 13, 0, 0, '', '2021-12-15', '15:00:21', 145),
	(13, 56, 39.00, 9, 18, 0, 263, 13, 0, 0, '', '2021-12-15', '15:01:22', 145),
	(14, 15, 12.50, 4, 50, 0, 119, 3, 0, 0, 'lt1-50', '2021-12-15', '15:11:08', 143),
	(15, 1, 21.00, 1, 2, 0, 1, 1, 0, 0, 'lt1-2', '2021-12-16', '12:09:48', 140),
	(16, 5, 21.00, 1, 1, 0, 1, 1, 0, 0, 'lt1-1', '2021-12-16', '12:17:58', 140),
	(17, 1, 21.00, 1, 10, 0, 1, 1, 0, 0, 'lt1-10', '2021-12-16', '15:24:23', 140),
	(18, 4, 21.00, 1, 25, 0, 1, 2, 0, 0, 'lt1-25', '2021-12-16', '15:26:41', 146),
	(19, 12, 21.00, 1, 8, 0, 1, 1, 0, 0, 'lt1-8', '2021-12-16', '16:18:41', 140),
	(20, 14, 21.00, 1, 7, 0, 1, 1, 0, 0, 'lt1-7', '2021-12-16', '16:19:59', 140),
	(21, 15, 21.00, 1, 6, 0, 1, 1, 0, 0, 'lt1-6', '2021-12-16', '16:20:46', 140),
	(22, 16, 21.00, 1, 5, 0, 1, 1, 0, 0, 'lt1-5', '2021-12-16', '16:25:08', 140),
	(23, 2, 26.00, 4, 8, 0, 2, 1, 0, 0, 'lt1-7', '2021-12-16', '16:29:12', 140),
	(24, 20, 26.00, 4, 7, 0, 2, 1, 0, 0, 'lt1-7', '2021-12-16', '16:31:20', 140),
	(25, 21, 26.00, 4, 6, 0, 2, 1, 0, 0, 'lt1-6', '2021-12-16', '16:49:16', 140),
	(26, 17, 42.00, 2, 2, 0, 1, 1, 0, 0, 'lt1-2', '2021-12-16', '16:58:07', 140),
	(27, 4, 21.00, 1, 1, 0, 1, 2, 0, 0, '', '2021-12-17', '09:59:41', 1),
	(28, 5, 26.00, 4, 1, 0, 2, 2, 0, 0, '', '2021-12-17', '09:59:41', 1),
	(29, 6, 28.00, 4, 1, 0, 5, 2, 0, 0, 'lt1-1', '2021-12-17', '09:59:41', 1),
	(30, 1, 21.00, 1, 20, 0, 1, 1, 0, 0, '', '2021-12-17', '18:53:42', 140);
/*!40000 ALTER TABLE `backup_inv_egresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla masivosebx_beta.backup_inv_ingresos
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
  PRIMARY KEY (`backup_id_ingreso`),
  KEY `almacen_id` (`almacen_id`),
  KEY `empleado_id` (`empleado_id`),
  KEY `tipo` (`tipo`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `plan_de_pagos` (`plan_de_pagos`),
  FULLTEXT KEY `nombre_proveedor` (`nombre_proveedor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla masivosebx_beta.backup_inv_ingresos: 0 rows
/*!40000 ALTER TABLE `backup_inv_ingresos` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_ingresos` ENABLE KEYS */;

-- Volcando estructura para tabla masivosebx_beta.backup_inv_ingresos_detalles
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
  PRIMARY KEY (`backup_id_detalle`),
  KEY `producto_id` (`producto_id`),
  KEY `ingreso_id` (`ingreso_id`),
  KEY `almacen_id` (`almacen_id`),
  KEY `asignacion_id` (`asignacion_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla masivosebx_beta.backup_inv_ingresos_detalles: 0 rows
/*!40000 ALTER TABLE `backup_inv_ingresos_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup_inv_ingresos_detalles` ENABLE KEYS */;

-- Volcando estructura para tabla masivosebx_beta.backup_inv_pagos
CREATE TABLE IF NOT EXISTS `backup_inv_pagos` (
  `backup_id_pago` int(11) NOT NULL AUTO_INCREMENT,
  `id_pago` int(11) NOT NULL,
  `movimiento_id` int(11) DEFAULT 0,
  `interes_pago` float NOT NULL DEFAULT 0,
  `tipo` enum('Ingreso','Egreso') NOT NULL,
  `delet_fecha_egreso` date NOT NULL,
  `delet_hora_egreso` time NOT NULL,
  `delet_empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`backup_id_pago`),
  KEY `movimiento_id` (`movimiento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla masivosebx_beta.backup_inv_pagos: ~19 rows (aproximadamente)
/*!40000 ALTER TABLE `backup_inv_pagos` DISABLE KEYS */;
INSERT INTO `backup_inv_pagos` (`backup_id_pago`, `id_pago`, `movimiento_id`, `interes_pago`, `tipo`, `delet_fecha_egreso`, `delet_hora_egreso`, `delet_empleado_id`) VALUES
	(1, 1, 1, 0, 'Egreso', '2021-12-14', '14:22:47', 145),
	(2, 2, 1, 0, 'Egreso', '2021-12-14', '14:26:32', 145),
	(3, 4, 2, 0, 'Egreso', '2021-12-14', '15:00:28', 145),
	(4, 5, 2, 0, 'Egreso', '2021-12-14', '15:08:41', 145),
	(5, 6, 2, 0, 'Egreso', '2021-12-14', '15:12:27', 145),
	(6, 9, 3, 0, 'Egreso', '2021-12-15', '14:53:46', 143),
	(7, 11, 13, 0, 'Egreso', '2021-12-15', '15:00:21', 145),
	(8, 12, 13, 0, 'Egreso', '2021-12-15', '15:01:22', 145),
	(9, 10, 3, 0, 'Egreso', '2021-12-15', '15:11:08', 143),
	(10, 3, 1, 0, 'Egreso', '2021-12-16', '12:09:48', 140),
	(11, 15, 1, 0, 'Egreso', '2021-12-16', '12:17:58', 140),
	(12, 16, 1, 0, 'Egreso', '2021-12-16', '15:24:23', 140),
	(13, 7, 2, 0, 'Egreso', '2021-12-16', '15:26:41', 146),
	(14, 17, 1, 0, 'Egreso', '2021-12-16', '16:18:41', 140),
	(15, 19, 1, 0, 'Egreso', '2021-12-16', '16:19:59', 140),
	(16, 20, 1, 0, 'Egreso', '2021-12-16', '16:20:46', 140),
	(17, 21, 1, 0, 'Egreso', '2021-12-16', '16:25:08', 140),
	(18, 22, 1, 0, 'Egreso', '2021-12-16', '16:29:12', 140),
	(19, 23, 1, 0, 'Egreso', '2021-12-16', '16:31:20', 140),
	(20, 24, 1, 0, 'Egreso', '2021-12-16', '16:49:16', 140),
	(21, 25, 1, 0, 'Egreso', '2021-12-16', '16:58:07', 140),
	(23, 26, 1, 0, 'Egreso', '2021-12-17', '18:53:42', 140);
/*!40000 ALTER TABLE `backup_inv_pagos` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
