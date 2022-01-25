<?php



// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['almacen_id']) && isset($_POST['tipo']) && isset($_POST['descripcion']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['nro_registros']) && isset($_POST['monto_total'])) {
		// Obtiene los datos de la venta
		$almacen_id = trim($_POST['almacen_id']);
		$tipo = trim($_POST['tipo']);
		$descripcion = trim($_POST['descripcion']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();
		
			// para motivo
			$motivo = trim($_POST['motivo']);

			if ($tipo == 'Traspaso') {
				$otro_almacen = trim($_POST['almac']);
				$al2 = $db->from('inv_almacenes')->where('id_almacen', $otro_almacen)->fetch_first();
				$almacen_salida = $db->from('inv_almacenes')->where('id_almacen', $almacen_id)->fetch_first();
				$almacen2 = $al2['almacen'];

				$ingreso = array(
					'fecha_ingreso' => date('Y-m-d'),
					'hora_ingreso' => date('H:i:s'),
					'tipo' => 'Traspaso',
					'descripcion' => ($descripcion != '')?$descripcion:'Traspaso de productos',
					'monto_total' => $monto_total,
					'nombre_proveedor' => 'Almacen - ' . strtoupper(($almacen_salida['almacen']) ? $almacen_salida['almacen'] : 'Origen no definido'),
					'nro_registros' => $nro_registros,
					'almacen_id' => $otro_almacen,
					'empleado_id' => $_user['persona_id'],
					'proveedor_id' => 0
				);
				// Guarda la informacion
				$ingreso_id = $db->insert('inv_ingresos', $ingreso);

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/egresos/guardar',
					'detalle' => 'Se inserto ingreso por traspaso con identificador numero ' . $ingreso_id,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);

				foreach ($productos as $nro => $elemento) {
					// Forma el detalle
					$Cantidad=$db->query("SELECT COUNT(id_detalle)AS cantidad FROM inv_ingresos_detalles WHERE producto_id='{$productos[$nro]}' LIMIT 1")->fetch_first();
					$Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
					$detalle = array(
						'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro] : 0,
						'costo' => (isset($precios[$nro])) ? $precios[$nro] : 0,
						'producto_id' => $productos[$nro],
						'ingreso_id' => $ingreso_id,
						'lote'=>'lt'.($Cantidad+1),
						'lote_cantidad'=>(isset($cantidades[$nro])) ? $cantidades[$nro] : 0,
						'vencimiento'=>'0000-00-00'
					);

					// Guarda la informacion
					$id_detalle = $db->insert('inv_ingresos_detalles', $detalle);
					// Guarda en el historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"),
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/egresos/guardar',
						'detalle' => 'Se inserto inventario egreso detalle con identificador numero ' . $id_detalle,
						'usuario_id' => $_SESSION[user]['id_user']
					);
					$db->insert('sys_procesos', $data);
				}
				$almacen1 = $almacen1 . ' a  ' . $almacen2;
				
				$_user1=$db->query("SELECT CONCAT(em.nombres,' ',em.paterno,' ',em.materno)AS empleado
								FROM inv_ingresos AS e
								LEFT JOIN sys_empleados AS em ON em.id_empleado=e.empleado_id
								WHERE e.id_ingreso='{$egreso_id}' LIMIT 1")->fetch_first();
				$_user1 = ($_user1['empleado']) ? $_user1['empleado'] : '';
				Contabilidad($db,0,$almacen_id,$ingreso_id,$monto_total,5,$_user1);
			}

			// Instancia la venta
			$venta = array(
				'fecha_egreso' => date('Y-m-d'),
				'hora_egreso' => date('H:i:s'),
				'tipo' => ($tipo == 'Baja') ? $tipo : 'Baja',
				'provisionado' => 'N',
				'descripcion' => ($descripcion != '') ?  (($tipo == 'Perdida') ? 'Perdida - ' . $descripcion : $descripcion) : 'Traspaso de productos',
				'nro_factura' => 0,
				'nro_autorizacion' => 0,
				'codigo_control' => '',
				'fecha_limite' => '0000-00-00',
				'monto_total' => $monto_total,
				'nombre_cliente' => '',
				'nit_ci' => 0,
				'nro_registros' => $nro_registros,
				'dosificacion_id' => 0,
				'almacen_id' => $almacen_id,
				'empleado_id' => $_user['persona_id'],
				'motivo' => ($tipo != 'Traspaso')?$motivo:''
			);

			// Guarda la informacion
			$egreso_id = $db->insert('inv_egresos', $venta);
			
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/egresos/guardar',
				'detalle' => 'Se inserto inventario egreso con identificador numero ' . $egreso_id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			//validar si es trapaso y si se registro el egreso
			if ($tipo == 'Traspaso' && $egreso_id && $ingreso_id) {
				$condicion = array('id_ingreso' => $ingreso_id);
				$datos = array('proveedor_id' => $egreso_id);

				//se actualiza el proveedor con el id de egreso registrado para futura eliminacion de traspaso si hubiera
				$db->where($condicion)->update('inv_ingresos', $datos);
			}

			// Recorre los productos
			foreach ($productos as $nro => $elemento) {
				// Forma el detalle
				$unidad = $db->select('unidad_id')->from('inv_productos')->where('id_producto', $productos[$nro])->fetch_first();
				/////////////////////////////////////////////////////////////////////////////////////////
				$Lote='';
				$CantidadAux=$cantidades[$nro];
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

				$detalle = array(
					'cantidad' => $cantidades[$nro],
					'precio' => $precios[$nro],
					'descuento' => 0,
					'unidad_id' => $unidad['unidad_id'],
					'producto_id' => $productos[$nro],
					'egreso_id' => $egreso_id,
					'lote'=>$Lote
				);

				// Guarda la informacion
				$id_detalle = $db->insert('inv_egresos_detalles', $detalle);

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/egresos/guardar',
					'detalle' => 'Se inserto inventario egreso detalle con identificador numero ' . $id_detalle,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);
			}

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);

			$_user1=$db->query("SELECT CONCAT(em.nombres,' ',em.paterno,' ',em.materno)AS empleado
								FROM inv_egresos AS e
								LEFT JOIN sys_empleados AS em ON em.id_empleado=e.empleado_id
								WHERE e.id_egreso='{$egreso_id}' LIMIT 1")->fetch_first();
			$_user1 = ($_user1['empleado']) ? $_user1['empleado'] : '';
			Contabilidad($db,0,$almacen_id,$egreso_id,$monto_total,4,$_user1);

			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/egresos/listar');

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
