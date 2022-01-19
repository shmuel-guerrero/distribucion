<?php

// echo json_encode($_POST); die();

if (is_post()) {
	if ( isset($_POST['id_venta']) && isset($_POST['id_pago_detalle']) && isset($_POST['fecha_programada']) && isset($_POST['fecha']) && isset($_POST['monto']) ) {

		$resultado = $db->select('*')
			->from('inv_pagos_detalles i')
			->where('id_pago_detalle', $_POST['id_pago_detalle'])
			->fetch_first();

			// echo json_encode($resultado); die();

		$idventa=$_POST['id_venta'];
		$cantPagos = $db->query("SELECT COUNT(id_pago_detalle) as cantidad FROM inv_pagos_detalles WHERE pago_id = '{$resultado['pago_id']}'")->fetch_first();
		$cantPagos = ($cantPagos['cantidad']) ? $cantPagos['cantidad'] : 0;
		// echo json_encode($cantPagado); die();

		if ($resultado) {
			// echo 'asd1';
			$ingreso = array(
				// 'nro_cuota' => $resultado->nro_cuota,
				// 'fecha' => date_encode($f1),
				'fecha_pago' => date('Y-m-d'),
				'tipo_pago' => 'Efectivo',
				'monto' => $_POST['monto'],
				'estado' => 1
			);

			// $condicion = array('id_pago_detalle' => $resultado->id_pago_detalle);
			$db->where('id_pago_detalle', $resultado['id_pago_detalle'])->update('inv_pagos_detalles', $ingreso);
			// echo "1|" . $id . "|" . 1;
			// echo $db->last_query();
			// die();

		} else {
			echo 'asd2';
			$detallePlan = array(
				'pago_id' => $resultado['pago_id'],
				'nro_cuota' => $cantPagos+1,
				'fecha' => date('Y-m-d'),
				'fecha_pago' => date('Y-m-d'),
				'tipo_pago' => 'Efectivo',
				'monto' => $_POST['monto'],
				'estado' => 1,
				'empleado_id' => $_user['persona_id'],
			);
			// Guarda la informacion
			$db->insert('inv_pagos_detalles', $detallePlan);
			// echo "1|" . $id . "|" . 1;
		}

		$cantPagado = $db->query("SELECT COUNT(id_pago_detalle) as cantidad FROM inv_pagos_detalles WHERE pago_id = '{$resultado['pago_id']}' AND estado = '1'")->fetch_first();
		$cantPagado = ($cantPagado['cantidad']) ? $cantPagado['cantidad'] : 0;
		$monto_total=$db->query("SELECT monto_total_descuento FROM inv_egresos WHERE id_egreso='{$idventa}'")->fetch_first();
		$monto_total = ($monto_total['monto_total_descuento']) ? $monto_total['monto_total_descuento'] : 0;
		$cuotascanceladas=$db->query("SELECT SUM(monto) AS monto FROM inv_pagos_detalles WHERE pago_id='{$resultado['pago_id']}' AND estado='1'")->fetch_first();
		$cuotascanceladas = ($cuotascanceladas['monto']) ? $cuotascanceladas['monto'] : 0;

		// echo $db->last_query();
		// echo $monto_total . ' <br>';
		// echo $cuotascanceladas;
		// die();
		if ($cantPagado == $cantPagos) {
			if($cuotascanceladas<$monto_total){
				// echo 'wer';
				$detallePlan = array(
					'pago_id' => $resultado['pago_id'],
					'nro_cuota' => $cantPagos+1,
					'fecha' => date('Y-m-d'),
					'fecha_pago' => '0000-00-00',
					// 'tipo_pago' => 'Efectivo',
					'monto' => $monto_total - $cuotascanceladas,
					'estado' => '0',
					'empleado_id' => $_user['persona_id'],
				);
				$db->insert('inv_pagos_detalles', $detallePlan);
			}
		}


		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Cobro registrado satisfactoriamente!',
			'message' => 'El cobro se registró correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/cobrar/notas_ver/' . $_POST['id_venta']);

	} else {
		// Error 404
		require_once not_found();
		exit;
	}

} else {
	// Error 404
	require_once not_found();
	exit;
}

?>