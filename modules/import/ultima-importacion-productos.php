<?php


$id_ingreso_import = $db->query("SELECT MAX(id_general)AS id_general FROM imports_generals WHERE estado_importacion = 'Import'")->fetch_first();

$id_import = (isset($id_ingreso_import['id_general'])) ? $id_ingreso_import['id_general'] : 0;

echo $id_import;