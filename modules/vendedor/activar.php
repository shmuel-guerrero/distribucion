<?php

// Obtiene el id_user
$id_user = (sizeof($params) > 0) ? $params[0] : 0;

//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {

	//Se abre nueva transacción.
	$db->autocommit(false);
	$db->beginTransaction();

	// Obtiene el user
	$user = $db->from('sys_empleados')->where('id_empleado', $id_user)->fetch_first();

	// Verifica si el user existe
	if ($user) {
		// Obtiene el nuevo estado
		$fecha_actual = date("d-m-Y");
		$nuevo = date("Y-m-d", strtotime($fecha_actual . "- 1 days"));
		$estado = ($user['fecha'] == date('Y-m-d')) ? $nuevo : date('Y-m-d');

		// Instancia el user
		$user = array(
			'fecha' => $estado,
			'hora' => date('H:i:s')
		);
		// Genera la condicion
		$condicion = array('id_empleado' => $id_user);

		// Actualiza la informacion
		$db->where($condicion)->update('sys_empleados', $user);
		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"),
			'proceso' => 'u',
			'nivel' => 'm',
			'direccion' => '?/vendedor/activar',
			'detalle' => 'Se actualizo empleado con identificador número ' . $id_user,
			'usuario_id' => $_SESSION[user]['id_user']
		);
		$db->insert('sys_procesos', $data);

		//se cierra transaccion
		$db->commit();

		// Redirecciona a la pagina principal
		redirect('?/vendedor/listar');
	} else {

		//se cierra transaccion
		$db->commit();

		// Error 404
		require_once not_found();
		exit;
	}


} catch (Exception $e) {
	$status = false;
	$error = $e->getMessage();

	// Instancia la variable de notificacion
	$_SESSION[temporary] = array(
		'alert' => 'danger',
		'title' => 'Problemas en el proceso de interacción con la base de datos.',
		'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
	);

	//Se devuelve el error en mensaje json
	//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

	//se cierra transaccion
	$db->rollback();

	// Redirecciona a la pagina principal
	return redirect(back());
}
