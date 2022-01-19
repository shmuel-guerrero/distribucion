
<?php

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_materiales']) && isset($_POST['nombre']) && isset($_POST['cantidad']) && isset($_POST['precio'])) {
		// Obtiene los datos del producto
		$id_materiales = trim($_POST['id_materiales']);
		$nombre = trim($_POST['nombre']);
		// $fecha_ven = trim($_POST['ven_fecha']);
		$precio = ($_POST['precio']) ? trim($_POST['precio']) : '0.00';
		$cantidad = trim($_POST['cantidad']);

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

			// Instancia el control
			$control = array(
				'estado' => 'pendiente',
				'fecha_control' => date('Y-m-d'),
				'empleado_id' => $_SESSION[user]['id_user'],
				'proveedor' => 'Fabrica',
				'cliente_id' => 0,
				'stock' => 'egreso',
				'cantidad' => $cantidad,
				'tipo' => 'fabrica',
				'id_materiales' => $id_materiales['id_materiales']
			);
			$db->insert('inv_control', $control);
			/*
			// Verifica si es creacion o modificacion
			if ($id_materiales > 0) {
				// Genera la condicion
				$condicion = array('id_materiales' => $id_materiales);
				
				// Actualiza la informacion
				$db->where($condicion)->update('inv_materiales', $materiales);
				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'u',
					'nivel' => 'l',
					'direccion' => '?/materiales/guardar',
					'detalle' => 'Se actualizó inventario de producto con identificador número ' . $id_materiales,
					'usuario_id' => $_SESSION[user]['id_user']			
				);			
				$db->insert('sys_procesos', $data) ;
				
				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			} else {
				// adiciona la fecha de creacion
				$materiales['fecha_material'] = date('Y-m-d');
				

				// Guarda la informacion
				$id_materiales = $db->insert('inv_materiales', $materiales);
				
				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/materiales/guardar',
					'detalle' => 'Se inserto el producto con identificador numero ' . $id_materiales ,
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
			*/
				// Redirecciona a la pagina principal
			redirect('?/cobrar/lista_material_fabrica');
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

?>