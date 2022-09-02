-- Volcando estructura para tabla hgc_test_09092021.inv_asignaciones_almacenes
CREATE TABLE IF NOT EXISTS `inv_asignaciones_almacenes` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_registro` date NOT NULL,
  `hora_registro` time NOT NULL,
  `institucion_id` int(11) NOT NULL DEFAULT 0,
  `almacen_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_asignacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_caja
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


-- Volcando estructura para tabla hgc_test_09092021.inv_egresos_desc_asignaciones
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



-- Volcando estructura para tabla hgc_test_09092021.inv_tipo_calculo
CREATE TABLE IF NOT EXISTS `inv_tipo_calculo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `sigla` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.inv_tipo_movimientos
CREATE TABLE IF NOT EXISTS `inv_tipo_movimientos` (
  `id_tipo_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_movimiento` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tipo_movimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Volcando estructura para tabla hgc_test_09092021.sys_desc_generales
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

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla hgc_test_09092021.sys_desc_tipos
CREATE TABLE IF NOT EXISTS `sys_desc_tipos` (
  `id_tipo` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sigla` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tipo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- se agraga columnas
ALTER TABLE caj_movimientos ADD recibido_por INT(11) AFTER empleado_id;

ALTER TABLE caj_movimientos ADD sucursal_id INT(11) AFTER recibido_por;



/*!40000 ALTER TABLE `inv_tipo_calculo` DISABLE KEYS */;
INSERT INTO `inv_tipo_calculo` (`id`, `tipo`, `sigla`) VALUES
	(1, 'porcentual', '%'),
	(2, 'puntual', '$');
/*!40000 ALTER TABLE `inv_tipo_calculo` ENABLE KEYS */;

-- Volcando datos para la tabla hgc_test_09092021.inv_tipo_movimientos: ~4 rows (aproximadamente)
/*!40000 ALTER TABLE `inv_tipo_movimientos` DISABLE KEYS */;
INSERT INTO `inv_tipo_movimientos` (`id_tipo_movimiento`, `tipo_movimiento`) VALUES
	(1, 'electronica'),
	(2, 'nota'),
	(3, 'manual'),
	(4, 'proforma');
/*!40000 ALTER TABLE `inv_tipo_movimientos` ENABLE KEYS */;

-- Volcando datos para la tabla hgc_test_09092021.sys_desc_tipos: ~2 rows (aproximadamente)
/*!40000 ALTER TABLE `sys_desc_tipos` DISABLE KEYS */;
INSERT INTO `sys_desc_tipos` (`id_tipo`, `tipo`, `sigla`) VALUES
	(1, 'porcentaje', '%'),
	(2, 'efectivo', '$');

