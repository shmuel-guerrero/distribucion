<?php


// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['unidad'])) {
		// Obtiene los datos del producto
		$unidad = trim($_POST['unidad']);

		// Obtiene los productos con el valor buscado
		$unidads = $db->select('id_unidad, unidad, COUNT(*)nro_registros')->from('inv_unidades')->where('unidad', $unidad)->fetch_first()['nro_registros'];

		// Verifica si existe coincidencias
		if ($unidads) {
			$response = array('valid' => false, 'message' => 'La unidad "' . $unidads['unidad'] . '" ya fue registrado');
		} else {
			$response = array('valid' => true);
		}

		// Devuelve los resultados
		echo json_encode($response);
	} else {
		// Error 401
		require_once bad_request();
		exit;
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>