<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_marca']) && isset($_POST['marca']) && isset($_POST['descripcion'])) {
		// Obtiene las datos de la categoría
		$id_marca = trim($_POST['id_marca']);
		$marca = trim($_POST['marca']);
		$descripcion = trim($_POST['descripcion']);
		
		$id_marca=$db->query("SELECT * FROM inv_marcas where  id_marca = '{$id_marca}'")->fetch_first();
		$id_marca = ($id_marca['id_marca']) ? $id_marca['id_marca'] : 0;
		// Instancia la categoría
		$datos = array(
			'marca' => $marca,
			'descripcion' => $descripcion
		);
		
		// Verifica si es creacion o modificacion
		if ($id_marca > 0) {
			// Genera la condicion
			$condicion = array('id_marca' => $id_marca);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_marcas', $datos);
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// Guarda la informacion
			$id = $db->insert('inv_marcas', $datos);
			
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/marcas/guardar',
				'detalle' => 'Se inserto marca con identificador numero ' . $id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/marcas/listar');
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