<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// Verifica la peticion post
if (is_post()) {
	// Verifica la cadena csrf
	if (true) {
		// Obtiene los parametros
		$id_asignacion = (isset($params[0])) ? $params[0] : 0;

		// Obtiene la asignacion
		$asignacion = $db->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->fetch_first();

		// Verifica si la asignacion existe
		if ($asignacion) {
			// Elimina la asignacion
			$db->delete()->from('inv_asignaciones')->where('id_asignacion', $id_asignacion)->execute();
			
			// Verifica la eliminacion
			if ($db->affected_rows) {
				// Elimina los dependientes
				$db->delete()->from('inv_precios')->where('asignacion_id', $id_asignacion)->execute();
				
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