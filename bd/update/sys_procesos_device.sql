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

-- Volcando estructura para tabla distribuidhgc_beta.sys_procesos
CREATE TABLE IF NOT EXISTS `sys_procesos_device` (
  `id_proceso` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_proceso` datetime NOT NULL,
  `hora_proceso` time NOT NULL,
  `proceso` enum('c','r','u','d') CHARACTER SET latin1 NOT NULL,
  `nivel` enum('l','m','h') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `detalle` text NOT NULL,
  `usuario_id` int(11) NOT NULL,
  PRIMARY KEY (`id_proceso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
