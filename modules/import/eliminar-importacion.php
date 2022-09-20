<?php 

$id_ingreso_import= $_POST['idIngresoImport'];


$datos_ingreso = $db->from('inv_ingresos_import')->where(array('id_ingreso' => $id_ingreso_import, 'estado_import' => 'Import'))->fetch_first();

$detalles = $db->from('inv_ingresos_detalles_import')->where(array('ingreso_id' => $id_ingreso_import))->fetch();


if ($datos_ingreso) {
    $db->delete('inv_ingresos_import')->where(array('id_ingreso' => $id_ingreso_import))->limit(1)->execute();
}
$ingreso_eliminado = $db->affected_rows;

if ($detalles) {
    $db->delete('inv_ingresos_detalles_import')->where(array('ingreso_id' => $id_ingreso_import))->execute();
}

$detalles_eliminado = $db->affected_rows;


echo ($ingreso_eliminado && $detalles_eliminado ) ? 'ingreso importado eliminado' : 'No se pudo eliminar los registros; o fueron eliminado parcialmente';