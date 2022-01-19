<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_producto
$id_producto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();

// Verifica si el producto existe
if ($producto) {
	// Elimina el producto
	// $db->delete()->from('inv_productos')->where('id_producto', $id_producto)->limit(1)->execute();
	// //Guarda en el historial
	// $data = array(
	// 	'fecha_proceso' => date("Y-m-d"),
	// 	'hora_proceso' => date("H:i:s"), 
	// 	'proceso' => 'd',
	// 	'nivel' => 'l',
	// 	'direccion' => '?/productos/eliminar',
	// 	'detalle' => 'Se elimino producto con identificador numero ' . $id_producto ,
	// 	'usuario_id' => $_SESSION[user]['id_user']			
	// );			
	// $db->insert('sys_procesos', $data) ;

	// // Verifica si fue el producto eliminado
	// if ($db->affected_rows) {
	// 	// Instancia variable de notificacion
	// 	$_SESSION[temporary] = array(
	// 		'alert' => 'success',
	// 		'title' => 'Eliminación satisfactoria!',
	// 		'message' => 'El registro fue eliminado correctamente.'
	// 	);
	// }

	if ($producto['eliminado'] == 0) {
		$db->where(array('id_producto' => $id_producto))->update('inv_productos', array('eliminado' => 1));
	} else {
		$db->where(array('id_producto' => $id_producto))->update('inv_productos', array('eliminado' => 0));
	}

	//Guarda en el historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"),
		'proceso' => 'd',
		'nivel' => 'l',
		'direccion' => '?/productos/eliminar',
		'detalle' => 'Se cambio el estado del producto con identificador numero ' . $id_producto ,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data) ;
	// Instancia variable de notificacion
	$_SESSION[temporary] = array(
		'alert' => 'success',
		'title' => 'Acción satisfactoria!',
		'message' => 'El registro fue cambiado de estado correctamente.'
	);

	// Redirecciona a la pagina principal
	redirect('?/productos/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>