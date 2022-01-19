<?php


// Obtiene el id_ingreso
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nro'])) {
		$id_ingreso = $_POST['nro'];
		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();
			// Obtiene el ingreso
			$ingreso = $db->select('*')
				->from('inv_pagos_detalles')
				->where('id_pago_detalle', $id_ingreso)
				->fetch_first();

			// Verifica si el ingreso existe
			if ($ingreso) {
				// Elimina el ingreso
				$db->delete()->from('inv_pagos_detalles')->where('id_pago_detalle', $id_ingreso)->limit(1)->execute();

				// Verifica si fue el ingreso eliminado
				if ($db->affected_rows) {
					// Instancia variable de notificacion
					echo "1";
				} else {
					echo "0";
				}
			} else {
				echo "2"; //NO EXISTE EL DATO EXTERNO
			}
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
		echo "0";
	}
} else {
	echo "0";
}
