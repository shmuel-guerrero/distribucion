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

-- Volcando estructura para tabla distribuidhgc_beta.backup_inv_asignaciones
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

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
