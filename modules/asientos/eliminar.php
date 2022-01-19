<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_user
$id_auto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el user
$auto = $db->from('con_asientos_automaticos')->where('id_automatico', $id_auto)->fetch_first();

// Verifica si el user existe
if ($auto) {
	// Elimina el user
	$db->delete()->from('con_asientos_automaticos')->where('id_automatico', $id_auto)->limit(1)->execute();

	// Verifica si fue el user eliminado
	if ($db->affected_rows) {
		// Define la variable de error
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El asiento fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/asientos/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>