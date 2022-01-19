<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_empleado
$id_tipo = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado
$tipo = $db->from('inv_prioridades_ventas')->where('id_prioridad_venta', $id_tipo)->fetch_first();

// Verifica si el empleado existe
if ($tipo) {
	// Elimina el empleado
	$db->delete()->from('inv_prioridades_ventas')->where('id_prioridad_venta', $id_tipo)->limit(1)->execute();
	//Guarda en el historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'd',
		'nivel' => 'l',
		'direccion' => '?/prioridades/eliminar_prioridad',
		'detalle' => 'Se elimino la prioridad con identificador numero ' . $id_tipo ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ; 

	// Verifica si fue el empleado eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/prioridades/crear_prioridad');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>