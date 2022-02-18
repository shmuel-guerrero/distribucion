<?php

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
		// Importa la libreria para el codigo de control
		require_once libraries . '/controlcode-class/ControlCode.php';
		require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

		// Obtiene los datos de la nota
		$id_cliente = trim($_POST['id_cliente']);		
		$nombre_cliente = trim($_POST['nombre_cliente']);
		$nit_ci = trim($_POST['nit_ci']);
		$telefono = trim($_POST['telefono']);
		$direccion = trim($_POST['direccion']);
		
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		
		// descuentos
		$descuento_porc = trim($_POST['descuento_porc']) ? $_POST['descuento_porc'] : 0;
		$total_importe_descuento = trim($_POST['total_importe_descuento']);
		$descuento_bs = trim($_POST['descuento_bs']) ? $_POST['descuento_bs'] : 0;
		
		$almacen_id = trim($_POST['almacen_id']);
		//Cuentas
		$tipo_pago = (isset($_POST['tipo_pago'])) ? trim($_POST['tipo_pago']) : 'Efectivo';		

		$nro_factura = trim($_POST['nro_factura']);
		$nro_autorizacion = trim($_POST['nro_autorizacion']);

		//se envia datos a validar el stock de los productos
		$validar_stock_productos = validar_stock($db, $productos, $cantidades, $unidad, $almacen_id);
		$message = "";

		//validar que se e¡tiene elementos con stock por debajo de lo requerido
		if (count($validar_stock_productos) > 0) {			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Accion erronea!',
				'message' => 'No se guardo registro.'
			);

			$message = preparar_mensaje($validar_stock_productos);
			echo json_encode(array('status' => 'invalid', 'responce' => $message));
			exit;			
		}

		// Agrega nuevo cliente
		if($id_cliente == 0){
			$cliente = array(
				'id_cliente' => $id_cliente,
				'nombre_factura' => $nombre_cliente,
				'cliente' => $nombre_cliente,
				'nit' => $nit_ci,
				'telefono' => $telefono,
				'direccion' => $direccion,
				
				'cuentas_por_cobrar' => 'no'
			);
			$id_cliente = $db->insert('inv_clientes', $cliente);			
		}
		
		$nro_cuentas = isset($_POST['nro_cuentas']) ? trim($_POST['nro_cuentas']) : 0;
		$plan = (isset($_POST['forma_pago'])) ? trim($_POST['forma_pago']) : '';
		$plan = ($plan == "2") ? "si" : "no";
		if ($plan == "si") {
			$fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
			$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota'] : array();
		}

		if (isset($_POST['reserva'])){
			$reserva = 'si';
		} else {
			$reserva = 'no';
		}
		
		// Para creditos HGC
        $credito = trim($_POST['credito']);
        $id_cliente = $_POST['id_cliente'];

		// Verifica si la dosificación existe
		
			// Instancia la venta
		$venta = array(
			'fecha_egreso' => date('Y-m-d'),
			'hora_egreso' => date('H:i:s'),
			'tipo' => 'Venta',
			'descripcion' => 'Venta de productos con factura manual',
			'nro_factura' => $nro_factura,
			'nro_autorizacion' => $nro_autorizacion,
			'codigo_control' => 0,
			'fecha_limite' => '0000-00-00',
			'monto_total' => $monto_total,
			
			'descuento_porcentaje' => $descuento_porc,
			'descuento_bs' => $descuento_bs,
			'monto_total_descuento' => $total_importe_descuento,
			
			'cliente_id' => $id_cliente,
			'nit_ci' => $nit_ci,
			'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8'),
			'nro_registros' => $nro_registros,
			'dosificacion_id' => 0,
			'almacen_id' => $almacen_id,
			'empleado_id' => $_user['persona_id'],
			
			'plan_de_pagos' => ($credito == '1' || $credito == 1)?'si':'no', //$plan
		);

		// Guarda la informacion
		$egreso_id = $db->insert('inv_egresos', $venta);

		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"),
			'proceso' => 'c',
			'nivel' => 'l',
			'direccion' => '?/manuales/guardar',
			'detalle' => 'Se creo inventario egreso con identificador numero ' . $egreso_id,
			'usuario_id' => $_SESSION[user]['id_user']
		);

		$db->insert('sys_procesos', $data);

		// Recorre los productos
		foreach ($productos as $nro => $elemento) {
			// Forma el detalle
			$aux = $db->query("	SELECT *
								FROM inv_productos
								where id_producto='".$productos[$nro]."'
								")->fetch_first();
								
								$id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
								$cantidad = $cantidades[$nro] * cantidad_unidad($db, $productos[$nro], $id_unidad['id_unidad']);
								
								/////////////////////////////////////////////////////////////////////////////////////////
								$Lote='';
								$CantidadAux=$cantidad;
			$Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
			foreach($Detalles as $Fila=>$Detalle):
				if($CantidadAux>=$Detalle['lote_cantidad']):
					$Datos=[
						'lote_cantidad'=>0,
					];
					$Cant=$Detalle['lote_cantidad'];
				elseif($CantidadAux>0):
					$Datos=[
						'lote_cantidad'=>$Detalle['lote_cantidad']-$CantidadAux,
					];
					$Cant=$CantidadAux;
				else:
					break;
				endif;
				$Condicion=[
					'id_detalle'=>$Detalle['id_detalle'],
				];
				$db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
				$CantidadAux=$CantidadAux-$Detalle['lote_cantidad'];
				$Lote.=$Detalle['lote'].'-'.$Cant.',';
			endforeach;
			$Lote=trim($Lote,',');
			/////////////////////////////////////////////////////////////////////////////////////////
			
			$detalle = array(
				'cantidad' => $cantidad,
				'precio' => $precios[$nro],
				'unidad_id' => $id_unidad['id_unidad'],
				'descuento' => 0,
				'producto_id' => $productos[$nro],
				'egreso_id' => $egreso_id,
				'lote'=> 0
			);
			// Genera los subtotales
			//echo $precios[$nro];
			//echo "<br>";
			//echo $cantidades[$nro];
			//echo "<br>";
			$numero_auxiliar = floatval($precios[$nro]) * floatval($cantidades[$nro]);
			$subtotales[$nro] = number_format($numero_auxiliar, 2, '.', '');
			
			// Guarda la informacion
			$id = $db->insert('inv_egresos_detalles', $detalle);
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/electronicas/guardar',
				'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
			
		}

		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"),
			'proceso' => 'u',
			'nivel' => 'l',
			'direccion' => '?/manuales/guardar',
			'detalle' => 'Se actualizó stock en almacen con venta manual nro.'.$nro_factura,
			'usuario_id' => $_SESSION[user]['id_user']
		);
		$db->insert('sys_procesos', $data);
		// Instancia la respuesta
		$respuesta = array(
			'papel_ancho' => 10,
			'papel_alto' => 25,
			'papel_limite' => 576,
			'empresa_nombre' => $_institution['nombre'],
			'empresa_sucursal' => 'SUCURSAL Nº 1',
			'empresa_direccion' => $_institution['direccion'],
			'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
			'empresa_ciudad' => 'EL ALTO - BOLIVIA',
			'empresa_actividad' => $_institution['razon_social'],
			'empresa_nit' => $_institution['nit'],
			'empresa_empleado' => ($_user['persona_id'] == 0) ?strtoupper($_user['username']) :strtoupper(trim($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno'])),
			'empresa_agradecimiento' => '¡Gracias por tu compra!',
			'factura_titulo' => 'F  A  C  T  U  R  A',
			'factura_numero' => $venta['nro_factura'],
			'factura_autorizacion' => $venta['nro_autorizacion'],
			'factura_fecha' => date_decode($venta['fecha_egreso'], 'd/m/Y'),
			'factura_hora' => substr($venta['hora_egreso'], 0, 5),
			'factura_codigo' => $venta['codigo_control'],
			'factura_limite' => date_decode($venta['fecha_limite'], 'd/m/Y'),
			'factura_autenticidad' => '"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"',
			'factura_leyenda' => 'Ley Nº 453: leyenda',
			'cliente_nit' => $venta['nit_ci'],
			'cliente_nombre' => $venta['nombre_cliente'],
			'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'TOTAL'),
			'venta_cantidades' => $cantidades,
			'venta_detalles' => $nombres,
			'venta_precios' => $precios,
			'venta_subtotales' => $subtotales,
			'venta_total_numeral' => $venta['monto_total'],
			'venta_total_literal' => '$monto_literal',
			'venta_total_decimal' => '$monto_decimal' . '/100',
			'venta_moneda' => '$moneda',
			'importe_base' => '0',
			'importe_ice' => '0',
			'importe_venta' => '0',
			'importe_credito' => '0',
			'importe_descuento' => '0',
			'impresora' => $_terminal['impresora']
		);
		
		
		//Cuentas manejo de HGC
		if ($credito == '1' || $credito = 1) {
			$id_cli = (int)$id_cliente;
			$clienteCred = $db->query("select * from inv_clientes
										where id_cliente = $id_cli")->fetch_first();
			
			// echo $db->last_query();
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
		//echo json_encode($respuesta);
		echo json_encode(array('status' => 'success', 'responce' => $egreso_id));

	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>