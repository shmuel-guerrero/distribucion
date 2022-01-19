<?php

// Obtiene el id_ingreso
$id_ingreso = (isset($params[0])) ? $params[0] : 0;

//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 

try {

	//Se abre nueva transacción.
	$db->autocommit(false);
	$db->beginTransaction();

	// Obtiene el ingreso
	$ingreso = $db->select('i.*, a.principal')
				->from('inv_ingresos i')
				->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
				->where('i.id_ingreso', $id_ingreso)
				->fetch_first();

	//Obtiene detalle de la compra
	$detalles = $db->query("SELECT * FROM inv_ingresos_detalles WHERE ingreso_id = " . $ingreso['id_ingreso'])->fetch();

	//obtiene los ids del array
	$keys = array_keys($detalles);

	foreach ($detalles as $key => $value) {	
		$consulta .= "(SELECT SUM(d.cantidad) as sum_lotes, d.* FROM `inv_egresos_detalles` d  WHERE d.lote LIKE '". $value['lote'] .'-%' ."' AND d.producto_id='" . $value['producto_id'] . "' GROUP BY d.producto_id) ";
		if ($key < end($keys)) {
			$consulta .= " UNION ";
		}
	}


	//se realiza la consulta mysql
	$verifica = $db->query($consulta)->fetch(); 

	//verifica si la variable esta vacia
	if (!$verifica) {

		// Verifica si el ingreso existe
		if ($ingreso) {
			if ($ingreso['tipo'] == 'Traspaso') {
		
				//se crea backup de registros a eliminar
				$verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $ingreso['proveedor_id'], 'tipo', 'Traspaso', $_user['persona_id'], 'SI', 0, "Eliminado");

				// Elimina el ingreso
				$db->delete()->from('inv_egresos')->where('id_egreso', $ingreso['proveedor_id'])->where('tipo', 'Traspaso')->limit(1)->execute();

				//se crea backup de registros a eliminar
				$verifica = backup_registros($db, 'inv_egresos_detalles', 'egreso_id', $ingreso['proveedor_id'], '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");
				// Elimina los detalles
				$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $ingreso['proveedor_id'])->execute();
			}
			//se crea backup de registros a eliminar
			$verifica_id = backup_registros($db, 'inv_ingresos', 'id_ingreso', $id_ingreso, '', '', $_user['persona_id'], 'SI', 0, "Eliminado");

			// Elimina el ingreso
			$db->delete()->from('inv_ingresos')->where('id_ingreso', $id_ingreso)->limit(1)->execute();
			
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/ingresos/eliminar',
				'detalle' => 'Se elimino ingreso con identificador numero' . $id_ingreso ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;
			
			//se crea backup de registros a eliminar
			$verifica = backup_registros($db, 'inv_ingresos_detalles', 'ingreso_id', $id_ingreso, '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");
			// Elimina los detalles
			$db->delete()->from('inv_ingresos_detalles')->where('ingreso_id', $id_ingreso)->execute();
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/ingresos/eliminar',
				'detalle' => 'Se elimino ingreso detalle con identificador numero' . $id_ingreso ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;

			// Verifica si fue el ingreso eliminado
			if ($db->affected_rows) {
				// Instancia variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Eliminacion satisfactoria!',
					'message' => 'El ingreso y todo su detalle fue eliminado correctamente.'
				);
			}

			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/ingresos/listar');
		} else {
			// Error 404
			require_once not_found();
			exit;
		}
	}else{
		//se cierra transaccion
		$db->commit();

		// Verifica si fue el ingreso eliminado
		if ($db->affected_rows) {
			// Instancia variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Eliminacion no realizada!',
				'message' => 'El ingreso contiene items que ya fueron utilizados en movimientos de la empresa. La eliminación no puede ser realizada. '
			);
		}

		// Redirecciona a la pagina principal
		redirect('?/ingresos/listar');
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
	redirect('?/ingresos/listar');
	//Se devuelve el error en mensaje json
	//echo json_encode(array("estado" => 'n', 'msg'=>(environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

	//se cierra transaccion
	$db->rollback();
}
?>