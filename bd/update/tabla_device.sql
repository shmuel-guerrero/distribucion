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

-- Volcando estructura para tabla distribuidhgc_beta.sys_users_devices
CREATE TABLE IF NOT EXISTS `sys_users_devices` (
  `id_device` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `model` varchar(500) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` longtext COLLATE utf8_spanish_ci DEFAULT NULL,
  `imei` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `cant_user` int(11) DEFAULT 0,
  `tipo` enum('Basic','Premium') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'Basic',
  PRIMARY KEY (`id_device`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla distribuidhgc_beta.sys_users_devices_detalles
CREATE TABLE IF NOT EXISTS `sys_users_devices_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `token` varchar(50) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `device_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
