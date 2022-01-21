<?php


// var_dump($_POST); die();
// Verifica la peticion post
if (is_post()) {
	// Verifica la cadena csrf
	if (true) {
		// Obtiene los parametros
		$id_asignacion = (isset($params[0])) ? $params[0] : 0;
		

		// Obtiene la asignacion
		$asignacion = $db->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->fetch_first();

		$existe = $db->select('COUNT(id_detalle) as total')
							 ->from('inv_egresos_detalles')
							 ->where('producto_id', $asignacion['producto_id'])
							 ->where('unidad_id', $asignacion['unidad_id'])
							 ->fetch_first();
		if($existe['total'] > 0) {
			// Crea la notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Operación fallida!',
				'message' => 'Esta asignación cuenta con egresos registrados, no puede ser eliminado.'
			);
			// Redirecciona la pagina
			redirect('?/productos/listar');
		}


		// Verifica si la asignacion existe
		if ($asignacion) {

			/**
			 * Se saca un backup y se elimina el registro para que no afecte en la opracion de los servicios de APK
			 */
			//se crea backup de registros//////
			$verifica_id = backup_registros($db, 'inv_asignaciones', 'id_asignacion', $asignacion['id_asignacion'], '', '', $_user['persona_id'], 'SI', 0, "Eliminado");


			/* $actualizar = array(
				'visible' => 'n',
			);		
			// Actualiza la informacion
			$condicion = array('id_asignacion' => $id_asignacion); 
			$db->where($condicion)->update('inv_asignaciones', $actualizar); */

			// Elimina la asignacion
			$db->delete()->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->limit(1)->execute();
			
			// Verifica la eliminacion
			if ($db->affected_rows) {// $db->affected_rows
				// Elimina los dependientes
				// $db->delete()->from('inv_precios')->where('asignacion_id', $id_asignacion)->execute();

				// Guarda el proceso
				$db->insert('sys_procesos', array(
					'fecha_proceso' => date('Y-m-d H:i:s'),
					'hora_proceso' => date('H:i:s'),
					'proceso' => 'd',
					'nivel' => 'h',
					'detalle' => 'Se eliminó la unidad con identificador número ' . $asignacion['unidad_id'] . ' del producto con identificador número ' . $asignacion['producto_id'] . '.',
					'direccion' => $_location,
					'usuario_id' => $_user['id_user']
				));
				
				// Crea la notificacion
				set_notification('success', 'Eliminación exitosa!', 'La unidad fue elimina satisfactoriamente.');
			} else {
				// Crea la notificacion
				set_notification('danger', 'Eliminación fallida!', 'La unidad no fue eliminada.');
			}

			// Redirecciona la pagina
			redirect(back());
		} else {
			// Error 400
			require_once bad_request();
			exit;
		}
	} else {
		// Redirecciona la pagina
		redirect(back());
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>