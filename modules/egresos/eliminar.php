<?php

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0;

//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
try {

	//Se abre nueva transacción.
	$db->autocommit(false);
	$db->beginTransaction();
	// Obtiene el egreso
	$egreso = $db->from('inv_egresos')->where('id_egreso', $id_egreso)->fetch_first();

	// Verifica si el egreso existe
	if ($egreso) {
		//se crea backup de registros a eliminar
		$verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $id_egreso, '', '', $_user['persona_id'], 'SI', 0,'Eliminado');
		// Elimina el egreso
		$db->delete()->from('inv_egresos')->where('id_egreso', $id_egreso)->limit(1)->execute();
		//Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'd',
			'nivel' => 'l',
			'direccion' => '?/egresos/eliminar',
			'detalle' => 'Se elimino egreso con identificador numero ' . $id_egreso ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ;


		//se crea backup de registros a eliminar
		$verifica = backup_registros($db, 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $_user['persona_id'], 'NO', $verifica_id, 'Eliminado');
		// Elimina los detalles
		$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();
		
		//Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'd',
			'nivel' => 'l',
			'direccion' => '?/egresos/eliminar',
			'detalle' => 'Se elimino egreso detalle con identificador numero ' . $id_egreso ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ;
		

		// Verifica si fue el egreso eliminado
		if ($db->affected_rows) {
			// Instancia variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Eliminación satisfactoria!',
				'message' => 'El egreso y todo su detalle fueron eliminados correctamente.'
			);
		}
		//se cierra transaccion
		$db->commit();

		// Redirecciona a la pagina principal
		redirect('?/egresos/listar');


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
		'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
	);
	// Redirecciona a la pagina principal
	redirect('?/egresos/listar');
	//Se devuelve el error en mensaje json
	//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

	//se cierra transaccion
	$db->rollback();
}

?>