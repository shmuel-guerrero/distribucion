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

-- Volcando estructura para tabla distribuidhgc_beta.hist_conversiones
CREATE TABLE IF NOT EXISTS `hist_conversiones` (
  `id_conversion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date DEFAULT NULL,
  `hora_registro` time DEFAULT NULL,
  `id_origen` int(11) NOT NULL DEFAULT 0,
  `origen_movimiento` enum('Proforma','NotaRemision','Reserva','Preventa') NOT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- La exportación de datos fue deseleccionada.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
