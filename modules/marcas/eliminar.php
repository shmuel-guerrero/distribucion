<?php

// Obtiene el id_marca
$id_marca = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la marca
$marca = $db->from('inv_marcas')->where('id_marca', $id_marca)->fetch_first();

// Verifica si la marca existe
if ($marca) {
	// Elimina la marca
	$db->delete()->from('inv_marcas')->where('id_marca', $id_marca)->limit(1)->execute();
	
	//Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'd',
		'nivel' => 'l',
		'direccion' => '?/marcas/eliminar',
		'detalle' => 'Se elimino marca con identificador numero ' . $id_marca ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;
	

	// Verifica si fue la marca eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/marcas/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>