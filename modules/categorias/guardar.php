<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_categoria']) && isset($_POST['categoria']) && isset($_POST['descripcion'])) {
		// Obtiene las datos de la categoría
		$id_categoria = trim($_POST['id_categoria']);
		$categoria = trim($_POST['categoria']);
		$descripcion = trim($_POST['descripcion']);

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

			$id_categoria = $db->query("SELECT COUNT(id_categoria)AS id_categoria FROM inv_categorias")->fetch_first();
			$id_categoria = ($id_categoria['id_categoria']) ? $id_categoria['id_categoria'] : 0;
			// Instancia la categoría
			$categoria = array(
				'categoria' => $categoria,
				'descripcion' => $descripcion,
				'categoria_id' => ($id_categoria + 1),
			);

			// Verifica si es creacion o modificacion
			if ($id_categoria > 0) {
				// Genera la condicion
				$condicion = array('id_categoria' => $id_categoria);

				// Actualiza la informacion
				$db->where($condicion)->update('inv_categorias', $categoria);

				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			} else {
				// Guarda la informacion
				$id = $db->insert('inv_categorias', $categoria);

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/categorias/guardar',
					'detalle' => 'Se inserto categoria con identificador numero ' . $id,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);

				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Adición satisfactoria!',
					'message' => 'El registro se guardó correctamente.'
				);
			}
			
			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/categorias/listar');

		} catch (Exception $e) {
			$status = false;
			$error = $e->getMessage();
			//se cierra transaccion
			$db->rollback();

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
			);
			// Redirecciona a la pagina principal
			redirect('?/categorias/listar');
			//Se devuelve el error en mensaje json
			//echo json_encode(array("status" => 'failed', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'));

		}
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
