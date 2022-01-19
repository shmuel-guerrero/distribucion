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
	// Obtiene el nuevo estado
	$estado = ($auto['estado'] == 'no') ? 'si' : 'no';

	// Instancia el user
	$user = array(
		'estado' => $estado
	);

	// Genera la condicion
	$condicion = array('id_automatico' => $id_auto);

	// Actualiza la informacion
	$db->where($condicion)->update('con_asientos_automaticos', $user);

	// Redirecciona a la pagina principal
	redirect('?/asientos/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>