<?php
//echo "llega punto 000";

// echo json_encode($_POST); die();


if (is_post()) {

//echo "llega punto 111";
	if (isset($_POST['id_ingreso']) && isset($_POST['id_pago_detalle']) && isset($_POST['fecha']) && isset($_POST['tipo_pago']) && isset($_POST['monto']) ) {


		$resultado = $db->select('*')
				->from('inv_pagos_detalles i')
				->where('id_pago_detalle', $_POST['id_pago_detalle'])
				->fetch_first();
		// echo json_encode($resultado); die();
		$cantPagos = $db->query("SELECT COUNT(id_pago_detalle) as cantidad FROM inv_pagos_detalles WHERE pago_id = '{$resultado['pago_id']}'")->fetch_first();
		$cantPagos = ($cantPagos['cantidad']) ? $cantPagos['cantidad'] : 0;

		if($resultado) {

			$ingreso = array(
				'fecha_pago' => $_POST['fecha'],
				'tipo_pago' => $_POST['tipo_pago'],
				'monto' => $_POST['monto'],
				'estado' => 1
			);
			$db->where('id_pago_detalle', $resultado['id_pago_detalle'])->update('inv_pagos_detalles', $ingreso);
		}
		else{
			$detallePlan = array(
				'pago_id'=>$resultado['pago_id'],
				'nro_cuota'=>$cantPagos + 1,
				'fecha' => date('Y-m-d'),
				'fecha_pago' => $_POST['fecha'],
				'tipo_pago' => $_POST['tipo_pago'],
				'monto' => $_POST['monto'],
				'estado' => 1
			);
				// Guarda la informacion
			$db->insert('inv_pagos_detalles', $detallePlan);
		}

		$cantPagado = $db->query("SELECT COUNT(id_pago_detalle) as cantidad FROM inv_pagos_detalles WHERE pago_id = '{$resultado['pago_id']}' AND estado = '1'")->fetch_first();
		$cantPagado = ($cantPagado['cantidad']) ? $cantPagado['cantidad'] : 0;
		$monto_total=$db->query("SELECT monto_total FROM inv_ingresos WHERE id_ingreso='{$_POST['id_ingreso']}'")->fetch_first();
		$monto_total = ($monto_total['monto_total']) ? $monto_total['monto_total'] : 0;
		// echo $db->last_query();
		$cuotascanceladas=$db->query("SELECT SUM(monto) AS monto FROM inv_pagos_detalles WHERE pago_id='{$resultado['pago_id']}' AND estado='1'")->fetch_first();
		$cuotascanceladas = ($cuotascanceladas['monto']) ? $cuotascanceladas['monto'] : 0;

		// echo $db->last_query();
		// echo $monto_total . ' <br>';
		// echo $cuotascanceladas;
		// die();
		if ($cantPagado == $cantPagos) {
			if($cuotascanceladas<$monto_total){
				// echo 'wer';
				$detallePlanN = array(
					'pago_id' => $resultado['pago_id'],
					'nro_cuota' => $cantPagos+1,
					'fecha' => date('Y-m-d'),
					'fecha_pago' => '0000-00-00',
					// 'tipo_pago' => 'Efectivo',
					'monto' => $monto_total - $cuotascanceladas,
					'estado' => '0',
					'empleado_id' => $_user['persona_id'],
				);
				$db->insert('inv_pagos_detalles', $detallePlanN);
			}
		}


		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Cobro registrado satisfactoriamente!',
			'message' => 'El cobro se registró correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/pagar/ver/' . $_POST['id_ingreso']);




		//echo "llega punto 222";

		// $id=$_POST['f0'.$nro];
		// $f4=$_POST['monto'.$nro];
		// $id_pago=$_POST['pago'];
		// $nro_cuota=$_POST['fx'.$nro];
		// $estado=1;

		// if(isset($_POST['inicial_fecha_'.$nro])){
		// 	$f1=$_POST['inicial_fecha_'.$nro];
		// }
		// else{
		// 	$f1="";
		// }
		// if(isset($_POST['pago_fecha_'.$nro])){
		// 	$f2=$_POST['pago_fecha_'.$nro];
		// }
		// else{
		// 	$f2="";
		// }
		// $f3=$_POST['tipo'.$nro];

		// if($f1==""){				$estado=0;	}
		// if($f2==""){				$estado=0;	}
		// if($f3=="" || $f3=="-"){	$estado=0;	$f3=="";	}

		// $resultado = $db->select('*')
		// 				->from('inv_pagos_detalles i')
		// 				->where('id_pago_detalle', $id)
		// 				->fetch_first();

		// if($resultado) {

		// 	$ingreso = array(
		// 		'nro_cuota'=>$nro_cuota,
		// 		'fecha' => date_encode($f1),
		// 		'fecha_pago' => date_encode($f2),
		// 		'tipo_pago' => $f3,
		// 		'monto' => $f4,
		// 		'estado' => $estado
		// 	);

		// 	$condicion = array('id_pago_detalle' => $id);
		// 	$db->where($condicion)->update('inv_pagos_detalles', $ingreso);
		// 	echo "1|".$id."|".$estado;	
		// }
		// else{
		// 	$detallePlan = array(
		// 			'pago_id'=>$id_pago,
		// 			'nro_cuota'=>$nro_cuota,
		// 			'fecha' => date_encode($f1),
		// 			'fecha_pago' => date_encode($f2),
		// 			'tipo_pago' => $f3,
		// 			'monto' => $f4,			
		// 			'estado' => $estado		
		// 		);
		// 		// Guarda la informacion
		// 	$id=$db->insert('inv_pagos_detalles', $detallePlan);
		// 	echo "1|".$id."|".$estado;	
		// }
	
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
