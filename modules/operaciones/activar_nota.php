<?php



// Obtiene el id_producto
$id_factura = (sizeof($params) > 0) ? $params[0] : 0;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {

	//Se abre nueva transacción.
	$db->autocommit(false);
	$db->beginTransaction();

	// Obtiene el producto
	$venta = $db->from('inv_egresos')->where('id_egreso', $id_factura)->fetch_first();



	// Verifica si el producto existe
	if ($venta) {

		if ($venta['estadoe'] == 3) {

			// Obtiene el nuevo estado
			$estado = ($venta['anulado'] == 0) ? 1 : 3;
		} else if ($venta['estadoe'] == 0) {
			// Obtiene el nuevo estado
			$estado = ($venta['anulado'] == 0) ? 1 : 0;
		}

		// Instancia el producto
		$dato = array(
			'anulado' => $estado
		);

		// Genera la condicion
		$condicion = array('id_egreso' => $id_factura);

		// Actualiza la informacion
		$db->where($condicion)->update('inv_egresos', $dato);

				/* 
			/////////////////////////////////////////////////////////////////////////////////////
			// Agregamos el retorno del stock
			$Lotes=$db->query("SELECT producto_id,lote,unidad_id
										FROM inv_egresos_detalles AS ed
										LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
										WHERE egreso_id='$id_factura'")->fetch();

			foreach($Lotes as $Fila=>$Lote){
				$IdProducto=$Lote['producto_id'];
				$UnidadId=$Lote['unidad_id'];
				$LoteGeneral=explode(',',$Lote['lote']);
				for($i=0;$i<count($LoteGeneral);++$i){
					$SubLote=explode('-',$LoteGeneral[$i]);
					$Lot=$SubLote[0];
					$Cantidad=$SubLote[1];
					$DetalleIngreso=$db->query("SELECT id_detalle,lote_cantidad
												FROM inv_ingresos_detalles
												WHERE producto_id='{$IdProducto}' AND lote='{$Lot}'
												LIMIT 1")->fetch_first();
					$Condicion=array(
						'id_detalle'=>$DetalleIngreso['id_detalle'],
						'lote'=>$Lot,
					);
					$CantidadAux=$Cantidad;
					$Datos=array(
						'lote_cantidad'=>(strval($DetalleIngreso['lote_cantidad'])+strval($CantidadAux)),
					);
					$db->where($Condicion)->update('inv_ingresos_detalles',$Datos);

					// Guarda Historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"),
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/operaciones/activar_nota',
						'detalle' => 'Se creo actualizo el ingreso detalle con identificador numero ' . $DetalleIngreso['id_detalle'],
						'usuario_id' => $_SESSION[user]['id_user']
					);

					$db->insert('sys_procesos', $data);
				};
			};
		*/
		// echo json_encode($Lotes); die();
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Se anulo correctamente!',
			'message' => 'La operacion se realizó correctamente.'
		);

		//se cierra transaccion
		$db->commit();

		// Redirecciona a la pagina principal
		redirect('?/operaciones/notas_listar');
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
