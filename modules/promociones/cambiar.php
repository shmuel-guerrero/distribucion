<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   FABIO CHOQUE
 */

//var_dump($_POST);exit();
// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['fecha'])) {
		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$fecha = trim($_POST['fecha']);

		// Instancia el producto
		$producto = array(
			'fecha_limite' => $fecha
		);

		// Actualiza la informacion
		$db->where('id_producto', $id_producto)->update('inv_productos', $producto);
		
		// Envia respuesta
		echo json_encode(array('fecha' => 's'));
	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>