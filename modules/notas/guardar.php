<?php

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
		// Importa la libreria para convertir el numero a letra
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
		$unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$des_reserva = isset($_POST['des_reserva']) ? trim($_POST['des_reserva']) : '';
		$almacen_id = trim($_POST['almacen_id']);
		
		//Cuentas
		$tipo_pago = (isset($_POST['tipo_pago'])) ? trim($_POST['tipo_pago']) : 'Efectivo';
		// $nro_factura = (isset($_POST['nro_factura'])) ? trim($_POST['nro_factura']) : 0;;
		$nro_autorizacion = (isset($_POST['nro_autorizacion'])) ? trim($_POST['nro_autorizacion']) : '';

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {
 
			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

			// Agrega nuevo cliente
			if($id_cliente == 0){

				$cliente_id_buscado = $db->query("SELECT * FROM inv_clientes c WHERE 
											(nombre_factura ='{$nombre_cliente}' OR 
											cliente = '{$nombre_cliente}') AND
											(nit = '{$nit_ci}') ")->fetch_first()['id_cliente'];

				if ($cliente_id_buscado && $cliente_id_buscado > 0) {
					$id_cliente = $cliente_id_buscado;
				}else{
					$cliente = array(
						'id_cliente' => $id_cliente,						
						'fecha_registro' => date('Y-m-d'),
						'hora_registro' => date('H:i:s'),
						'nombre_factura' => $nombre_cliente,
						'cliente' => $nombre_cliente,
						'nit' => $nit_ci,
						'telefono' => $telefono,
						'direccion' => $direccion,
						'ubicacion' => '-16.50699104268714, -68.1630445139506',
						'cuentas_por_cobrar' => 'no'
					);
					$id_cliente = $db->insert('inv_clientes', $cliente);
				}			
			}

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

			// $nro_cuentas = trim($_POST['nro_cuentas']);
			$plan = (isset($_POST['forma_pago'])) ? trim($_POST['forma_pago']) : '';
			$plan = ($plan == "2") ? "si" : "no";
			if ($plan == "si") {
				$fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
				$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota'] : array();
			}
			if (isset($_POST['reserva']))
				$reserva = 'si';
			else
				$reserva = 'no';
				
			// Para creditos HGC
			$credito = trim($_POST['credito']);

			//descuento
			$descuento_porcentaje = (isset($_POST['descuento_porc'])) ? clear($_POST['descuento_porc']) : 0;
			$descuento_bs = (isset($_POST['descuento_bs'])) ? clear($_POST['descuento_bs']) : 0;
			$total_importe_descuento = ($_POST['total_importe_descuento']) ? $_POST['total_importe_descuento'] : $monto_total;

			// Obtiene el numero de nota
			$nro_factura = $db->query("select ifnull(max(nro_factura), 0) + 1 as nro_factura
										from inv_egresos
										where tipo = 'Venta'
										and provisionado = 'S'
										AND codigo_control = ''")->fetch_first();
			$nro_factura = $nro_factura['nro_factura'];

			// Define la variable de subtotales
			$subtotales = array();

			// Obtiene la moneda
			$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
			$moneda = ($moneda) ? $moneda['moneda'] : '';

			// Obtiene los datos del monto total
			$conversor = new NumberToLetterConverter();
			$monto_textual = explode('.', $monto_total);
			$monto_numeral = $monto_textual[0];
			$monto_decimal = $monto_textual[1];
			$monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

			// Instancia la nota
			$nota = array(
				'fecha_egreso' => date('Y-m-d'),
				'hora_egreso' => date('H:i:s'),
				'tipo' => 'Venta',
				'provisionado' => 'S',
				'descripcion' => 'Venta de productos con nota de remisión',
				'nro_factura' => $nro_factura,
				'nro_autorizacion' => '',
				'codigo_control' => '',
				'fecha_limite' => '0000-00-00',
				'monto_total' => $monto_total,
				'descuento_porcentaje' => ($descuento_porcentaje == '') ? 0 : $descuento_porcentaje,
				'descuento_bs' => $descuento_bs,
				'monto_total_descuento' => ($total_importe_descuento == '') ? 0 : $total_importe_descuento,
				'cliente_id' => $id_cliente,
				'nit_ci' => $nit_ci,
				'nombre_cliente' => strtoupper($nombre_cliente),
				'nro_registros' => $nro_registros,
				'dosificacion_id' => 0,
				'almacen_id' => $almacen_id,
				'cobrar' => $reserva,
				'estado' => 1,
				'observacion' => $des_reserva,
				'empleado_id' => $_user['persona_id'],
				'plan_de_pagos' => ($credito == '1' || $credito == 1)?'si':'no', //$plan
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
				'detalle' => 'Se inserto el inventario egreso con identificador numero ' . $egreso_id,
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

				if ($aux['promocion'] == 'si') {
					// Forma el detalle
					$prod = $productos[$nro];
					$promos = $db->query("SELECT producto_id, precio, unidad_id, cantidad, descuento , id_promocion as egreso_id, cantidad as promocion_id
										FROM inv_promociones 
										WHERE id_promocion='$prod'
										")->fetch();
					
					/////////////////////////////////////////////////////////////////////////////////////////
					$Lote='';
					$CantidadAux=$cantidades[$nro];
					$Detalles=$db->query("	SELECT id_detalle,cantidad,lote,lote_cantidad 
											FROM inv_ingresos_detalles 
											WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 
											ORDER BY id_detalle ASC
										")->fetch();

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
					$detalle = array(
						'cantidad' => $cantidades[$nro],
						'precio' => $precios[$nro],
						'descuento' => 0,
						'unidad_id' => 7,
						'producto_id' => $productos[$nro],
						'egreso_id' => $egreso_id,
						'promocion_id' => 1,
						'lote'=>$Lote
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
						'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
						'usuario_id' => $_SESSION[user]['id_user']
					);
					$db->insert('sys_procesos', $data);

					foreach ($promos as $key => $promo) {
						/////////////////////////////////////////////////////////////////////////////////////////
						$Lote='';
						$CantidadAux=$promo['cantidad'] * $cantidades[$nro];
						$prodpromo = $promo['producto_id'];
						$Detalles=$db->query("	SELECT id_detalle,cantidad,lote,lote_cantidad 
												FROM inv_ingresos_detalles 
												WHERE producto_id='$prodpromo' AND lote_cantidad>0 
												ORDER BY id_detalle ASC
											")->fetch();
						
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
						$promo['lote'] = $Lote;
						$promo['egreso_id'] = $egreso_id;
						$promo['promocion_id'] = $productos[$nro];
						$promo['cantidad'] = $promo['cantidad'] * $cantidades[$nro];

						// Guarda la informacion
						$db->insert('inv_egresos_detalles', $promo);
						// Guarda en el historial
						$data = array(
							'fecha_proceso' => date("Y-m-d"),
							'hora_proceso' => date("H:i:s"),
							'proceso' => 'c',
							'nivel' => 'l',
							'direccion' => '?/notas/guardar',
							'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $egreso_id,
							'usuario_id' => $_SESSION[user]['id_user']
						);
						$db->insert('sys_procesos', $data);
					}
				} else {
					$id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
					$cantidad = $cantidades[$nro] * cantidad_unidad($db, $productos[$nro], $id_unidad['id_unidad']);

					/////////////////////////////////////////////////////////////////////////////////////////
					$Lote='';
					$CantidadAux=$cantidad;
					$Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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

					$detalle = array(
						'cantidad' => $cantidad,
						'precio' => $precios[$nro],
						'unidad_id' => $id_unidad['id_unidad'],
						'descuento' => $descuentos[$nro],
						'producto_id' => $productos[$nro],
						'egreso_id' => $egreso_id,
						'lote'=>$Lote
					);
					// Guarda la informacion
					$id = $db->insert('inv_egresos_detalles', $detalle);
				}

				// Genera los subtotales				
				$precios[$nro] = (float)$precios[$nro];
				$cantidades[$nro] = (float)$cantidades[$nro];
				$subtotales[$nro] = $precios[$nro] * $cantidades[$nro];			

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/notas/guardar',
					'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);
			}
			
			//Cuentas manejo de HGC
			if ($credito == '1' || $credito = 1) {
				$id_cli = (int)$id_cliente;

				// obtiene datos de cliente		
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
			

			// PARA PREMIOS
			//guardar premio
			$IdExtra=      (isset($_POST['IdExtra']))      ?$_POST['IdExtra']      :[];
			$NombreExtra=  (isset($_POST['NombreExtra']))  ?$_POST['NombreExtra']  :[];
			$PrecioExtra=  (isset($_POST['PrecioExtra']))  ?$_POST['PrecioExtra']  :[];
			$UnidadExtra=  (isset($_POST['UnidadExtra']))  ?$_POST['UnidadExtra']  :[];
			$CantidadExtra=(isset($_POST['CantidadExtra']))?$_POST['CantidadExtra']:[];
			
			if(count($IdExtra)>0):
				foreach($IdExtra as $i=>$IdE):
					$IdUnidad=$db->select('id_unidad')
								->from('inv_unidades')
								->where(array('unidad' => $UnidadExtra[$i]))
								->fetch_first();
					$Fecha=date('Y-m-d');
					$IdPromocion=$db->query("SELECT id_promocion FROM inv_promociones_monto WHERE tipo='4' AND '{$Fecha}'>=fecha_ini AND '{$Fecha}'<=fecha_fin LIMIT 1")->fetch_first();
					$IdPromocion = ($IdPromocion['total']) ? $IdPromocion['total'] : 0;
					// echo $IdPromocion;
					/////////////////////////////////////////////////////////////////////////////////////////
					$Lote = '';
					$CantidadAux = $CantidadExtra[$i];
					$Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$IdExtra[$i]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
					foreach ($Detalles as $Fila => $Detalle) :
						if ($CantidadAux >= $Detalle['lote_cantidad']) :
							$Datos = [
								'lote_cantidad' => 0,
							];
							$Cant = $Detalle['lote_cantidad'];
						elseif ($CantidadAux > 0) :
							$Datos = [
								'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
							];
							$Cant = $CantidadAux;
						else :
							break;
						endif;
						$Condicion = [
							'id_detalle' => $Detalle['id_detalle'],
						];
						$db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
						$CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
						$Lote .= $Detalle['lote'] . '-' . $Cant . ',';
					endforeach;
					$Lote = trim($Lote, ',');
					/////////////////////////////////////////////////////////////////////////////////////////
					$Datos=array(
							'precio'=>$PrecioExtra[$i],//o 0
							'unidad_id'=>$IdUnidad['id_unidad'],
							'cantidad'=>$CantidadExtra[$i],
							'descuento'=>'100',
							'producto_id'=>$IdExtra[$i],
							'egreso_id'=>$egreso_id,
							'promocion_id'=>$IdPromocion,
							'lote'=>$Lote,
							'asignacion_id'=>'0'
					);
					$db->insert('inv_egresos_detalles',$Datos);
				endforeach;
			endif;

			// verifica las promociones por monto
			$mpromociones = $db->query('select * from inv_promociones_monto')->fetch();
			$egreso = $db->query('select * from inv_egresos where id_egreso='.$egreso_id.'')->fetch_first();

			foreach ($mpromociones as $mpromocion) {

				$egreso_id_p= '';
				$fecha_ini = $mpromocion['fecha_ini'];
				$fecha_fin = $mpromocion['fecha_fin'];
				$monto_promo = $mpromocion['monto_promo'];

				if( $fecha_ini <= $egreso['fecha_egreso'] and $fecha_fin >= $egreso['fecha_egreso']) {

						if($egreso['monto_total'] >= $monto_promo){

						$egreso_id_p = $mpromocion['egresos_ids'].','.$egreso_id_p;
						$datos = array('egresos_ids' => $egreso_id_p);
						$condicion = array('id_promocion' => $mpromocion['id_promocion']);

						$db->where($condicion)->update('inv_promociones_monto', $datos);
					}
				}
			}
			// FIN PARA PREMIOS


			$Usuario=$db->query("SELECT CONCAT(em.nombres,' ',em.paterno,' ',em.materno)AS empleado
								FROM inv_egresos AS e
								LEFT JOIN sys_empleados AS em ON em.id_empleado=e.empleado_id
								WHERE e.id_egreso='{$egreso_id}' LIMIT 1")->fetch_first();
			$Usuario = ($Usuario['empleado']) ? $Usuario['empleado'] : '';
			Contabilidad($db,0,$almacen_id,$egreso_id,$monto_total,3,$Usuario);

			//se cierra transaccion
			$db->commit();

			// Envia respuesta
			echo json_encode(array('status' => 'success', 'responce' => $egreso_id));

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
			//redirect('?/notas/mostrar');
			//Se devuelve el error en mensaje json
			echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));
		
			//se cierra transaccion
			$db->rollback();
		}

	} else {
		// Envia respuesta
		echo 'error';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}
