<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_materiales
$id_materiales = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la materiales
$material = $db->from('inv_materiales')->where('id_materiales', $id_materiales)->fetch_first();

// Verifica si el material existe
if ($material) {
	// Elimina el material
	$db->delete()->from('inv_materiales')->where('id_materiales', $id_materiales)->limit(1)->execute();
	//Guarda en el historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'd',
		'nivel' => 'l',
		'direccion' => '?/materiales/eliminar',
		'detalle' => 'Se elimino el material con identificador numero ' . $id_materiales ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ; 

	// Verifica si fue el material eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminacion satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/materiales/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>