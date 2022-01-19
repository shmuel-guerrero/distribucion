<?php
/* ::BECA
* convierte las proformas en notas de remisión
* inicalmente hace una consulta en la tabla proformas y guarda los datos obtenidos en una nueva nota de remisión
*/
// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	if (isset($_POST['id_venta'])) {
		/* ::BECA
		* Verifica si existe stock suficiente para realizar la conversión
		*/
		$id_proforma = trim($_POST['id_venta']);
		$proforma = $db->query("select *
									from inv_proformas
									where id_proforma = $id_proforma")->fetch_first();
		$detalles = $db->query("select *
									from inv_proformas_detalles
									where proforma_id = $id_proforma")->fetch();
		$stock_suficiente = true;
		foreach ($detalles as $nro => $detalle) {						
			$cantidad = $detalle['cantidad'] * cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']);
			
			$id_producto =$detalle['producto_id'];
			$ingreso_total = $db->query("SELECT sum(d.cantidad) as total
						FROM inv_ingresos_detalles d
						LEFT JOIN inv_ingresos i ON d.ingreso_id=i.id_ingreso
						WHERE  transitorio = 0 AND  d.producto_id='{$id_producto}'")->fetch_first()['total'];
			$egreso_total = $db->query("SELECT sum(d.cantidad) as total
						FROM inv_egresos_detalles d
						INNER JOIN inv_egresos e ON d.egreso_id=e.id_egreso
						WHERE d.producto_id='{$id_producto} AND e.anulado != 3'")->fetch_first()['total'];
			
			$resta_stock = $ingreso_total - $egreso_total;
			if($stock_suficiente){
				$stock_suficiente = (($resta_stock - $cantidad) >= 0 ? true : false);
			}			
		}
		
		// Verifica si existe stock suficiente
		if ($stock_suficiente) {
			
			// Obtiene los datos del cliente			
			$cliente = $db->select('id_cliente, nit, cliente, nombre_factura, direccion, telefono, ubicacion, credito, dias')
							->from('inv_clientes')
							->where('id_cliente', $proforma['cliente_id']) 
							->fetch_first();
			
			$nombre_cliente = trim($proforma['nombre_cliente']);
			$nit_ci = trim($proforma['nit_ci']);
			$telefono = trim($proforma['telefono']);
			$direccion = trim($proforma['direccion']);
			$almacen_id = trim($proforma['almacen_id']);
			$nro_registros = trim($proforma['nro_registros']);
			$monto_total = trim($proforma['monto_total']);
			$descuento_bs = trim($proforma['descuento_bs']);
			$descuento_porcentaje = trim($proforma['descuento_porcentaje']);
			$monto_total_descuento = trim($proforma['monto_total_descuento']);
			$observacion = trim($proforma['observacion']);

			// Obtiene el número de nota
			$nro_n = $db->query("select ifnull(max(nro_factura), 0) + 1 as nro_factura
										from inv_egresos
										where tipo = 'Venta'
										and provisionado = 'S'
										AND codigo_control = ''")->fetch_first();
			$nro_nota = $nro_n['nro_factura'];
			
			// Obtiene la moneda
			$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
			$moneda = ($moneda) ? $moneda['moneda'] : '';

			// Instancia la nota
			$nota = array(
				'fecha_egreso' => date('Y-m-d'),
				'hora_egreso' => date('H:i:s'),
				'tipo' => 'Venta',
				'provisionado' => 'S',
				'descripcion' => 'Venta de productos con nota de remisión (convertido de proforma)',
				'nro_factura' => $nro_nota,
				'nro_autorizacion' => '',
				'codigo_control' => '',
				'fecha_limite' => '0000-00-00',
				'monto_total' => $monto_total,
				'descuento_porcentaje' => $descuento_porcentaje,
				'descuento_bs' => $descuento_bs,
				'monto_total_descuento' => $monto_total_descuento,
				'cliente_id' => $proforma['cliente_id'],
				'nit_ci' => $cliente['nit'],
				'nombre_cliente' => strtoupper($cliente['nombre_factura']),
				'nro_registros' => $nro_registros,
				'dosificacion_id' => 0,
				'almacen_id' => $almacen_id,
				'cobrar' => 'no',
				'estado' => 1,
				'observacion' => $observacion,
				'empleado_id' => $_user['persona_id'],
				'plan_de_pagos' => ($cliente['credito'] == '1' || $cliente['credito'] == 1)?'si':'no', 
			);

			// Guarda la informacion
			$egreso_id = $db->insert('inv_egresos', $nota);
			
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/notas/guardar',
				'detalle' => 'Se inserto el inventario egreso (convertido de proforma) con identificador numero ' . $egreso_id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
			
			// ::BECA -actualiza el campo que indica que la proforma fué convertida
			$actualizar = array('id_egreso_convertido' => $egreso_id);
			$condicion = array('id_proforma' => $id_proforma);
			$db->where($condicion)->update('inv_proformas', $actualizar);
		
			// Recorre el detalle
			foreach ($detalles as $nro => $detalle) {			
				// ::BECA convierte las unidades a las que corresponden (en proformas no se guarda de la misma forma que lo hace egresos, por ello es necesario convertir)
				$cantidad = $detalle['cantidad'] * cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']);
				// Forma el detalle
				$detalle = array(
					'cantidad' => $cantidad,
					'precio' => $detalle['precio'],
					'unidad_id' => $detalle['unidad_id'],
					'descuento' => 0,
					'producto_id' => $detalle['producto_id'],
					'egreso_id' => $egreso_id,
					'lote'=> 0
				);
				// Guarda la informacion
				$id = $db->insert('inv_egresos_detalles', $detalle);

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/notas/guardar',
					'detalle' => 'Se inserto en inventario egreso (convertido de proforma) detalle con identificador numero ' . $id,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);
			}
			
			//Cuentas manejo de HGC
			if ($credito == '1' || $credito = 1) {
				$id_cli = (int)$id_cliente;				
				$clienteCred = $db->query("SELECT * FROM inv_clientes WHERE id_cliente = $id_cli")->fetch_first();
							
				// Instancia el ingreso
				$ingresoPlan = array(
					'movimiento_id' => $egreso_id,
					'interes_pago' => 0,
					'tipo' => 'Egreso'
				);
				// Guarda la informacion del ingreso general
				$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
				
				$Date = date('Y-m-d');
				$Dias = ' + ' . $clienteCred['dias'] .' days';
				$Fecha_pago = date('Y-m-d', strtotime($Date. $Dias));
				
				$detallePlan = array(
					'nro_cuota' => 1,
					'pago_id' => $ingreso_id_plan,
					'fecha' => $Fecha_pago,
					'fecha_pago' => $Fecha_pago,
					'monto' => $monto_total - $descuento_bs,
					'tipo_pago' => '',
					'empleado_id' => $_user['persona_id'],
					'estado'  => '0'
				);
				// Guarda la informacion
				$db->insert('inv_pagos_detalles', $detallePlan);						
			}

			// Envia respuesta
			echo json_encode($egreso_id);
		} else {
			// Envia respuesta
			
			echo json_encode(-1);
			// echo 'Error, no hay suficiente stock en el almacen';
		}
	} else {
		// Envia respuesta
		echo 'Error, no se guardó dato alguno (no llegó el id de la proforma) ';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}
