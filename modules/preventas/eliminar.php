<?php


// Obtiene el id_proforma
$id_proforma = (isset($params[0])) ? $params[0] : 0;

//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
try {

	//Se abre nueva transacción.
	$db->autocommit(false);
	$db->beginTransaction();

	// Obtiene el proforma
	$proforma = $db->from('inv_proformas')
				->where('id_proforma', $id_proforma)
				->fetch_first();

	// Verifica si el proforma existe
	if ($proforma) {
		// Elimina el proforma
		$db->delete()->from('inv_proformas')->where('id_proforma', $id_proforma)->limit(1)->execute();

		// Elimina los detalles
		$db->delete()->from('inv_proformas_detalles')->where('proforma_id', $id_proforma)->execute();
		
		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'u',
			'nivel' => 'l',
			'direccion' => '?/preventas/eliminar',
			'detalle' => 'Se elimino inventario proforma detalle con identificador numero' . $id_proforma ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ;
		

		// Verifica si fue el proforma eliminado
		if ($db->affected_rows) {
			// Instancia variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Eliminación satisfactoria!',
				'message' => 'La proforma y todo su detalle fueron eliminados correctamente.'
			);
		}
		//se cierra transaccion
		$db->commit();

		// Redirecciona a la pagina principal
		redirect('?/preventas/mostrar');
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
	redirect('?/preventas/proformas_listar');
	//Se devuelve el error en mensaje json
	//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

	//se cierra transaccion
	$db->rollback();
}
?>