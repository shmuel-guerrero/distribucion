<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['nro_factura']) && isset($_POST['nro_autorizacion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['almacen_id']) && isset($_POST['nro_registros']) && isset($_POST['monto_total'])) {
		// Obtiene los datos de la venta
		$nit_ci = trim($_POST['nit_ci']);
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$almacen_id = trim($_POST['almacen_id']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$plan = ($_POST['forma_pago'] == 1) ? 'no' : 'si';


		//Cuentas
		$tipo_pago = (isset($_POST['tipo_pago'])) ? trim($_POST['tipo_pago']) : 'Efectivo';
		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);

		$nro_cuentas = trim($_POST['nro_cuentas']);
		$plan = (isset($_POST['forma_pago'])) ? trim($_POST['forma_pago']) : '';
		$plan = ($plan == "2") ? "si" : "no";

		if ($plan == "si") {
			$fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
			$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota'] : array();
		}
		//

		//obtiene a el cliente
		$cliente = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombre_cliente, 'nit' => $nit_ci))->fetch_first();
		if (!$cliente) {
			$cl = array(
				'cliente' => $nombre_cliente,
				'nit' => $nit_ci
			);
			$db->insert('inv_clientes', $cl);
		}

		// Instancia la venta
		$venta = array(
			'fecha_egreso' => date('Y-m-d'),
			'hora_egreso' => date('H:i:s'),
			'tipo' => 'Venta',
			'provisionado' => 'N',
			'descripcion' => 'Venta de productos con factura manual',
			'nro_factura' => $nro_factura,
			'nro_autorizacion' => $nro_autorizacion,
			'codigo_control' => '',
			'fecha_limite' => '0000-00-00',
			'monto_total' => $monto_total,
			'nombre_cliente' => strtoupper($nombre_cliente),
			'nit_ci' => $nit_ci,
			'nro_registros' => $nro_registros,
			'plan_de_pagos' => $plan,
			'estado' => 1,
			'dosificacion_id' => 0,
			'almacen_id' => $almacen_id,
			'empleado_id' => $_user['persona_id'],
			'plan_de_pagos' => $plan
		);

		// Guarda la informacion
		$egreso_id = $db->insert('inv_egresos', $venta);

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			$id_unidade = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades u', 'a.unidad_id=u.id_unidad AND a.visible = "s"')->where(array('u.unidad' => $unidad[$nro], 'a.producto_id' => $productos[$nro]))->fetch_first();
			$id_unidad = 0;
			if ($id_unidade) {
				$id_unidad = $id_unidade['id_unidad'];
				$cantidad = $cantidades[$nro] * $id_unidade['cantidad_unidad'];
			} else {
				$id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
				$id_unidad = $id_uni['id_unidad'];
				$cantidad = $cantidades[$nro];
			}
			/////////////////////////////////////////////////////////////////////////////////////////
			$Lote='';
			$CantidadAux=$cantidad;
			$Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC LIMIT 3")->fetch();
			foreach($Detalles as $Fila=>$Detalle):
				if($CantidadAux>=$Detalle['lote_cantidad']):
					$Datos=array(
						'lote_cantidad'=>0,
					);
					$Cant=$Detalle['lote_cantidad'];
				elseif($CantidadAux>0):
					$Datos=array(
						'lote_cantidad'=>$Detalle['lote_cantidad']-$CantidadAux,
					);
					$Cant=$CantidadAux;
				else:
					break;
				endif;
				$Condicion=array(
						'id_detalle'=>$Detalle['id_detalle'],
				);
				$db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
				$CantidadAux=$CantidadAux-$Detalle['lote_cantidad'];
				$Lote.=$Detalle['lote'].'-'.$Cant.',';
			endforeach;
			$Lote=trim($Lote,',');
			/////////////////////////////////////////////////////////////////////////////////////////
			// Forma el detalle
			$detalle = array(
				'cantidad' => $cantidad,
				'precio' => $precios[$nro],
				'descuento' => $descuentos[$nro],
				'unidad_id' => $id_unidad,
				'producto_id' => $productos[$nro],
				'egreso_id' => $egreso_id,
				'lote'=>$Lote
			);

			// Guarda la informacion
			$db->insert('inv_egresos_detalles', $detalle);
		}
		//Cuentas
		if ($plan == "si") {
			// Instancia el ingreso
			$ingresoPlan = array(
				'movimiento_id' => $egreso_id,
				'interes_pago' => 0,
				'tipo' => 'Egreso'
			);
			// Guarda la informacion del ingreso general
			$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);

			$nro_cuota = 0;
			for ($nro2 = 0; $nro2 < $nro_cuentas; $nro2++) {
				if (isset($fechas[$nro2])) {
					$fecha_format = $fechas[$nro2];
				} else {
					$fecha_format = "00-00-0000";
				}

				$vfecha = explode("-", $fecha_format);

				if (count($vfecha) == 3) {
					$fecha_format = $vfecha[2] . "-" . $vfecha[1] . "-" . $vfecha[0];
				} else {
					$fecha_format = "0000-00-00";
				}

				$nro_cuota++;
				if ($nro2 == 0) {
					$detallePlan = array(
						'nro_cuota' => $nro_cuota,
						'pago_id' => $ingreso_id_plan,
						'fecha' => $fecha_format,
						'fecha_pago' => $fecha_format,
						'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2] : 0,
						'tipo_pago' => $tipo_pago,
						'empleado_id' => $_user['persona_id'],
						'estado'  => '1'
					);
				} else {
					$detallePlan = array(
						'nro_cuota' => $nro_cuota,
						'pago_id' => $ingreso_id_plan,
						'fecha' => $fecha_format,
						'fecha_pago' => $fecha_format,
						'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2] : 0,
						'tipo_pago' => '',
						'empleado_id' => $_user['persona_id'],
						'estado'  => '0'
					);
				}
				// Guarda la informacion
				$db->insert('inv_pagos_detalles', $detallePlan);
			}
		}

		//
		// Instancia la variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Adición satisfactoria!',
			'message' => 'El registro se guardó correctamente.'
		);

		// Redirecciona a la pagina principal
		redirect('?/manuales/crear');
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
