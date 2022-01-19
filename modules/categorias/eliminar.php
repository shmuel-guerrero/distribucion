<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_categoria
$id_categoria = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la categoría
$categoria = $db->from('inv_categorias')->where('id_categoria', $id_categoria)->fetch_first();

// Verifica si la categoría existe
if ($categoria) {
	// Elimina la categoría
	$db->delete()->from('inv_categorias')->where('id_categoria', $id_categoria)->limit(1)->execute();
	
	//Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'd',
		'nivel' => 'l',
		'direccion' => '?/categorias/eliminar',
		'detalle' => 'Se elimino categoria con identificador numero ' . $id_categoria ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;
	

	// Verifica si fue la categoría eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/categorias/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>