<?php

$id_ingresoi = $_POST['id_iimport'];

$compra = $db->query("SELECT ii.*, a.almacen, GROUP_CONCAT(e.nombres, ' ', e.paterno)as empleado FROM inv_ingresos_import ii 
                        LEFT JOIN inv_almacenes a ON ii.almacen_id = a.id_almacen
                        LEFT JOIN sys_empleados e ON ii.empleado_id = e.id_empleado
                        WHERE ii.id_ingreso='{$id_ingresoi}' AND estado_import = 'Import'")->fetch_first();


$detalles = $db->query("SELECT di.*, p.codigo, p.nombre, nombre_factura FROM inv_ingresos_detalles_import di 
                        LEFT JOIN inv_productos p ON di.producto_id = p.id_producto
                        WHERE di.ingreso_id = '{$id_ingresoi}'")->fetch();
//$detalles = $db->from('inv_ingresos_detalles_import')->where(array('ingreso_id' => $id_ingresoi))->fetch();

$respuesta = array(
    'compra' => $compra, 
    'detalles' => $detalles
);

echo json_encode($respuesta);