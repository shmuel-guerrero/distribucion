/***************************************************************/
-- CAMPOS AGREGADOS A LA BASE DE DATOS
/*******************************************************************/


-------->>>>>>>>>>>>> 05012022

/* AGREGAR CAMPOS EN LAS TABLAS  DE BACKUP_EGRESOS_*/
ALTER TABLE `backup_inv_egresos`
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `delet_empleado_id`;


/* AGREGAR CAMPOS EN LAS TABLAS DE BACKUP_EGRESOS_dETALLES*/
ALTER TABLE `backup_inv_egresos_detalles`
	ADD COLUMN `accion_id_backup` INT(11) NOT NULL DEFAULT '0' AFTER `delet_empleado_id`,
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `accion_id_backup`;


/* AGREGAR CAMPOS EN LAS TABLAS  DE BACKUP_INGRESOS_*/
ALTER TABLE `backup_inv_ingresos`
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `delet_empleado_id`;


/* AGREGAR CAMPOS EN LAS TABLAS DE BACKUP_INGRESOS_DETALLES*/
ALTER TABLE `backup_inv_ingresos_detalles`
	ADD COLUMN `accion_id_backup` INT(11) NOT NULL DEFAULT '0' AFTER `delet_empleado_id`,
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `accion_id_backup`;


/* AGREGAR CAMPOS EN LAS TABLAS  DE BACKUP_PAGOS*/
ALTER TABLE `backup_inv_pagos`
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `delet_empleado_id`;


/* AGREGAR CAMPOS EN LAS TABLAS DE BACKUP_PAGOS_DETALLES*/
ALTER TABLE `backup_inv_pagos_detalles`
	ADD COLUMN `accion_id_backup` INT(11) NOT NULL DEFAULT '0' AFTER `delet_empleado_id`,
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `accion_id_backup`;


-----------------------<

------------------------->>>>>>>>>>>>>>>>>10012022

/* AGREGAR CAMPOS EN LAS TABLAS DE BACKUP_PAGOS_DETALLES*/


--->>>>> Ejecutar acrchivo:::::::       update/tabla_device.sql  

