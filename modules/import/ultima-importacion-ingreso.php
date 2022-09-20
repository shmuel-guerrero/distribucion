<?php

$id_ingreso_import = $db->query("SELECT MAX(id_ingreso)AS id_ingreso FROM inv_ingresos_import WHERE estado_import = 'Import'")->fetch_first();

$id_import = (isset($id_ingreso_import['id_ingreso'])) ? $id_ingreso_import['id_ingreso'] : 0;

echo $id_import;