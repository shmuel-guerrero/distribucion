<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {

	//var_dump($_POST); die();
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_ruta']) ) {
		// Obtiene los datos del empleado
		$id_ruta= trim($_POST['id_ruta']);
		$color= trim($_POST['color']);

		$condicion = array('id_ruta' => $id_ruta);
		$seleccion = array('color' => $color);

		// Actualiza la informacion
		$db->where($condicion)->update('gps_rutas', $seleccion);
		
		// Redirecciona a la pagina principal
		redirect('?/ruta/listar');
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