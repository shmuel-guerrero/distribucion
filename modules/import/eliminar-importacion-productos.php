<?php 

$id_ingreso_import= $_POST['idIngresoImport'];


$datos_ingreso = $db->from('imports_generals')->where(array('id_general' => $id_ingreso_import, 'estado_importacion' => 'Import'))->fetch_first();

$detalles = $db->from('import_inv_productos')->where(array('general_id' => $id_ingreso_import))->fetch();


if ($datos_ingreso) {
    $db->delete('imports_generals')->where(array('id_general' => $id_ingreso_import))->limit(1)->execute();
}
$ingreso_eliminado = $db->affected_rows;

if ($detalles) {
    $db->delete('import_inv_productos')->where(array('general_id' => $id_ingreso_import))->execute();
}

$detalles_eliminado = $db->affected_rows;


echo ($ingreso_eliminado && $detalles_eliminado ) ? 'ingreso importado eliminado' : 'No se pudo eliminar los registros; o fueron eliminado parcialmente';