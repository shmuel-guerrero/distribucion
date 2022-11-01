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

-- Volcando estructura para tabla nextcorp_distribucion.inv_bancos
CREATE TABLE IF NOT EXISTS `inv_bancos` (
  `id_banco` int(11) NOT NULL AUTO_INCREMENT,
  `banco` char(60) NOT NULL,
  `tipo` enum('bnb','bisa') NOT NULL DEFAULT 'bnb',
  `cuenta` varchar(25) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_banco`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- Volcando datos para la tabla nextcorp_distribucion.inv_bancos: ~4 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_bancos` DISABLE KEYS */;
INSERT INTO `inv_bancos` (`id_banco`, `banco`, `tipo`, `cuenta`, `estado`) VALUES
	(1, 'Banco Nacional De Bolivia Bs.', 'bnb', '1000240466', 1),
	(2, 'Banco Bisa Bs.', 'bisa', '2310440020', 1),
	(3, 'Banco Nacional De Bolivia $us', 'bnb', '1400607326', 1),
	(4, 'Banco Union Bs.', 'bnb', '10000024846913', 1);
/*!40000 ALTER TABLE `inv_bancos` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
