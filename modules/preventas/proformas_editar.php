<?php


// Verifica si es una peticion post
if (is_post()) {	
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_proforma']) && isset($_POST['nit_ci']) && isset($_POST['nombre_cliente'])) {
		// Obtiene los datos del producto
		$id_proforma = trim($_POST['id_proforma']);
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);

		
		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

			// Instancia la proforma
			$proforma = array(
				'nit_ci' => $nit_ci,
				'nombre_cliente' => strtoupper($nombre_cliente)
			);
			
			// Actualiza la informacion
			$db->where('id_egreso', $id_proforma)->update('inv_egresos', $proforma);

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/operaciones/proformas_ver/' . $id_proforma);

		} catch (Exception $e) {
			$status = false;
			$error = $e->getMessage();
		
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
			);
			// Redirecciona a la pagina principal
			redirect('?/preventas/proformas_listar');
			//Se devuelve el error en mensaje json
			//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));
		
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

?>