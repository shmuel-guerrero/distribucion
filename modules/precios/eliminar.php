<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene los parametros
$id_producto = (isset($params[0])) ? $params[0] : 0;
$id_precio = (isset($params[1])) ? $params[1] : 0;

// Obtiene el precio
$precio = $db->from('inv_precios')->where(array('id_precio' => $id_precio, 'producto_id' => $id_producto))->fetch_first();

// Verifica si el precio existe
if ($precio) {
	// Elimina el precio
	$db->delete()->from('inv_precios')->where('id_precio', $id_precio)->limit(1)->execute();
	
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/precios/eliminar',
		'detalle' => 'Se elimino precio con identificador numero' . $id_precio ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;
	
	// Verifica si fue el precio eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Obtiene el precio actual
	$precio_actual = $db->from('inv_precios')
						->where('producto_id', $id_producto)
						->order_by('fecha_registro desc, hora_registro desc')
						->fetch_first();

	$precio_actual = ($precio_actual) ? $precio_actual['precio'] : 0;

	// Actualiza la informacion
	$db->where('id_producto', $id_producto)->update('inv_productos', array('precio_actual' => $precio_actual));
	
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/precios/eliminar',
		'detalle' => 'Se actualizo producto con identificador numero ' . $id_producto ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);	
	$db->insert('sys_procesos', $data) ; 


	// Redirecciona a la pagina principal
	redirect('?/precios/ver/' . $id_producto);
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>