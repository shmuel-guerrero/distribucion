<?php

$id_ingreso_import= $_POST['idIngresoImport'];


$datos_ingreso = $db->from('inv_ingresos_import')->where(array('id_ingreso' => $id_ingreso_import, 'estado_import' => 'Import'))->fetch_first();

$detalles = $db->from('inv_ingresos_detalles_import')->where(array('ingreso_id' => $id_ingreso_import))->fetch();


unset($datos_ingreso['id_ingreso'], $datos_ingreso['estado_import']);

$datos_ingreso['fecha_ingreso'] = date('Y-m-d');
$datos_ingreso['hora_ingreso'] = date('H:i:s');

$db->where(array('id_ingreso' => $id_ingreso_import))->update('inv_ingresos_import', array('estado_import' => 'Confirmado') );

$id_ingreso = $db->insert('inv_ingresos', $datos_ingreso);

if ($id_ingreso) {
    
    foreach ($detalles as $value) {
        $value['ingreso_id'] = $id_ingreso;
        $db->insert('inv_ingresos_detalles', $value);
    }
}

echo json_encode($id_ingreso);