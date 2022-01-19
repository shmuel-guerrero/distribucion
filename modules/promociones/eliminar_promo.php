<?php

$id_promocion = (sizeof($params) > 0) ? $params[0] : 0;

$db->delete()->from('inv_promociones_monto')->where('id_promocion', $id_promocion)->execute();

$db->delete()->from('inv_requisitos_promo')->where('promocion_monto_id', $id_promocion)->execute();

$db->delete()->from('inv_participantes_promos')->where('promocion_monto_id', $id_promocion)->execute();

redirect('?/promociones/reporte_promos_monto');