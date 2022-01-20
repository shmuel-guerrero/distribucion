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

-- Volcando datos para la tabla hgc_test_09092021.sys_reportes: ~1 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_reportes` DISABLE KEYS */;
INSERT INTO `sys_reportes` (`id_reporte`, `fecha_registro`, `hora_registro`, `reporte`, `modulo`, `archivo`, `habilitado`) VALUES
	(1, '2022-01-14', '10:09:24', 'pdf', 'vendedor', 'imprimir', 'No');
/*!40000 ALTER TABLE `sys_reportes` ENABLE KEYS */;

-- Volcando estructura para tabla hgc_test_09092021.sys_reportes_detalles
CREATE TABLE IF NOT EXISTS `sys_reportes_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `atributo` varchar(500) COLLATE utf8_spanish_ci DEFAULT NULL,
  `detalle` text COLLATE utf8_spanish_ci DEFAULT NULL,
  `reporte_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Volcando datos para la tabla hgc_test_09092021.sys_reportes_detalles: ~1 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_reportes_detalles` DISABLE KEYS */;
INSERT INTO `sys_reportes_detalles` (`id_detalle`, `atributo`, `detalle`, `reporte_id`) VALUES
	(1, 'titulo', 'COTIZACIÓN', 1);
/*!40000 ALTER TABLE `sys_reportes_detalles` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
