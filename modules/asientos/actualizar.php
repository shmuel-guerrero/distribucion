<?php



// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_auto']) && isset($_POST['persona_id'])) {
		// Obtiene los datos del user
		$id_auto = trim($_POST['id_auto']);
		$menu_id = trim($_POST['persona_id']);

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();
			$ver = $db->select('*')->from('con_asientos_menus')->where(array('automatico_id' => $id_auto, 'menu_id' => $menu_id))->fetch();
			if (!$ver) {
				// Instancia el user
				$user = array(
					'menu_id' => $menu_id,
					'automatico_id' => $id_auto
				);

				// Actualiza la informacion
				$db->insert('con_asientos_menus', $user);
			}

			// Redirecciona a la pagina principal
			redirect('?/asientos/listar');

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
			// Redirecciona a la pagina principal o anterior			
			return redirect(back());
			//Se devuelve el error en mensaje json
			//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

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
