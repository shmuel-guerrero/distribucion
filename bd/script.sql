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


/* AGREGAR CAMPOS EN LAS TABLAS  DE caj_movimientos*/
ALTER TABLE `caj_movimientos`
	ADD COLUMN `recibido_por` INT(11) NOT NULL DEFAULT '0' AFTER `empleado_id`,
	ADD COLUMN `sucursal_id` INT(11) NOT NULL DEFAULT '0' AFTER `recibido_por`;










------------------------->>>>>>>>>>>>>>>>>10012022

/* AGREGAR CAMPOS EN LAS TABLAS DE BACKUP_PAGOS_DETALLES*/


--->>>>> Ejecutar acrchivo:::::::       update/tabla_device.sql  

ALTER TABLE backup_inv_egresos MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE backup_tmp_egresos MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos MODIFY COLUMN nro_factura VARCHAR(20);

ALTER TABLE inv_egresos_anular MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_editar_post MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_editar_previo MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_eliminar_post MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_eliminar_previo MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_entregas MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_inicio MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_noentregas MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_noventas MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE inv_egresos_ventas_editadas MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE tmp_egresos MODIFY COLUMN nro_factura VARCHAR(20);
ALTER TABLE tmp_reposiciones MODIFY COLUMN nro_factura VARCHAR(20);





/* AGREGAR CAMPOS EN LAS TABLAS DE sys_institucion*/
ALTER TABLE `sys_instituciones`
	ADD COLUMN `descripcion`  varchar(1500) NOT NULL DEFAULT '' AFTER `direccion`;







/*
*	SE AÑADIIOO NUEVOS CAMPOS A BACKUP
*/

/* AGREGAR CAMPOS EN LAS TABLAS DE backup_tmp_egresos*/
ALTER TABLE `backup_tmp_egresos`
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `delet_empleado_id`;
--- actualizar  tabla _backup_tmp_egresos campo accion enum ('Entrega','Venta','VentaDevuelto','Noentrega','Anulado','Devuelto','Eliminado','VentaEliminado')



ALTER TABLE `backup_tmp_egresos_detalles`
ADD COLUMN `accion_id_backup` INT(11) NOT NULL DEFAULT '0' AFTER `delet_empleado_id`,
	ADD COLUMN `accion_backup` ENUM('Editado','Eliminado','Backup') NOT NULL DEFAULT 'Backup' AFTER `accion_id_backup`;




ALTER TABLE `inv_egresos_efectivo`
	ADD COLUMN `estado` enum('Pedido','Editado','Cerrado') NOT NULL DEFAULT 'Pedido' AFTER `tipo_movimiento`;





































-- se AGREGA el campo tipo
/* 
ALTER TABLE backup_inv_egresos ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE backup_tmp_egresos ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_anular ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_editar_post ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_editar_previo ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_eliminar_post ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_eliminar_previo ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_entregas ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_inicio ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_noentregas ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_noventas ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE inv_egresos_ventas_editadas ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE tmp_egresos ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';
ALTER TABLE tmp_reposiciones ADD COLUMN tipo_venta ENUM('Preventa','Nota','Manual','Electronica','otros')  NOT NULL DEFAULT 'otros' AFTER 'tipo';

 */