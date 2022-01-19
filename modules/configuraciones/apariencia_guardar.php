<?php


// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['tema'])) {
		// Obtiene los datos de la institucion
		$id_institucion = $_institution['id_institucion'];
		$tema = $_POST['tema'];

		// Instancia la institucion
		$institucion = array(
			'tema' => trim($tema)
		);
		
		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

			// Actualiza la informacion
			$db->where('id_institucion', $id_institucion)->update('sys_instituciones', $institucion);

			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/configuraciones/apariencia_guardar',
				'detalle' => 'Se actualizo institucion con identificador numero ' . $id_institucion,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// Define el mensaje de exito
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizo correctamente.'
			);

			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/configuraciones/apariencia');
		} catch (Exception $e) {
			$status = false;
			$error = $e->getMessage();

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
			);
			// Redirecciona a la pagina principal
			redirect('?/configuraciones/apariencia');
			//Se devuelve el error en mensaje json
			//echo json_encode(array("status" => 'failed', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'));

			//se cierra transaccion
			$db->rollback();
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
