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

-- Volcando estructura para tabla nextcorp_distribucion.inv_deposito
CREATE TABLE IF NOT EXISTS `inv_deposito` (
  `id_deposito` int(11) NOT NULL AUTO_INCREMENT,
  `banco` varchar(25) NOT NULL,
  `nro_deposito` varchar(20) NOT NULL,
  `monto_deposito` decimal(8,2) NOT NULL,
  `fecha` date DEFAULT NULL,
  `estado` enum('pendiente','concluido','anulado') NOT NULL DEFAULT 'pendiente',
  `empleado_id` int(11) NOT NULL,
  PRIMARY KEY (`id_deposito`),
  KEY `empleado_id` (`empleado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla nextcorp_distribucion.inv_deposito: 5 rows
/*!40000 ALTER TABLE `inv_deposito` DISABLE KEYS */;
INSERT INTO `inv_deposito` (`id_deposito`, `banco`, `nro_deposito`, `monto_deposito`, `fecha`, `estado`, `empleado_id`) VALUES
	(1, '1', '479', 880.00, '2021-11-18', 'concluido', 27),
	(2, '1', '416', 1200.00, '2021-11-18', 'concluido', 27),
	(3, '1', '587', 8183.00, '2021-11-18', 'concluido', 27),
	(4, '1', '594', 7790.00, '2021-11-18', 'concluido', 27),
	(5, '1', '1235464', 570.00, '2022-06-01', 'concluido', 27);
/*!40000 ALTER TABLE `inv_deposito` ENABLE KEYS */;

-- Volcando estructura para tabla nextcorp_distribucion.inv_deposito_detalle
CREATE TABLE IF NOT EXISTS `inv_deposito_detalle` (
  `id_deposito_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `deposito_id` int(11) NOT NULL,
  `pago_detalle_id` int(11) NOT NULL,
  PRIMARY KEY (`id_deposito_detalle`),
  KEY `deposito_id` (`deposito_id`,`pago_detalle_id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1;

-- Volcando datos para la tabla nextcorp_distribucion.inv_deposito_detalle: 35 rows
/*!40000 ALTER TABLE `inv_deposito_detalle` DISABLE KEYS */;
INSERT INTO `inv_deposito_detalle` (`id_deposito_detalle`, `deposito_id`, `pago_detalle_id`) VALUES
	(1, 1, 2935),
	(2, 1, 2934),
	(3, 1, 2698),
	(4, 1, 751),
	(5, 1, 749),
	(6, 1, 2608),
	(7, 2, 2174),
	(8, 2, 3244),
	(9, 3, 2589),
	(10, 3, 1994),
	(11, 3, 1931),
	(12, 3, 3114),
	(13, 3, 2799),
	(14, 3, 1927),
	(15, 3, 2710),
	(16, 3, 3014),
	(17, 3, 1905),
	(18, 3, 2576),
	(19, 3, 2592),
	(20, 3, 2562),
	(21, 3, 2344),
	(22, 3, 1908),
	(23, 4, 671),
	(24, 4, 3233),
	(25, 4, 3168),
	(26, 4, 3172),
	(27, 4, 2973),
	(28, 4, 3231),
	(29, 4, 2974),
	(30, 4, 2962),
	(31, 4, 2006),
	(32, 4, 2237),
	(33, 5, 2699),
	(34, 5, 3392),
	(35, 5, 2609);
/*!40000 ALTER TABLE `inv_deposito_detalle` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
