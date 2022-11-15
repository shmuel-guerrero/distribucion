<?php
/* ::BECA
* Se cambió la lógica de como se guarda un nuevo proveedor (anteriormente el sistema generaba duplicados cada que se creaba un 
* nuevo ingreso y agrupaban los duplicados ya sean nuevos proveedores o antiguos el sistema generaba duplicados)
* Se agregó un algoritmo para crear nuevo proveedor
*/
// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['almacen_id']) && isset($_POST['id_proveedor']) && isset($_POST['descripcion']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['productos']) && isset($_POST['cantidades']) && isset($_POST['costos'])) {
		// Obtiene los datos del producto
		$almacen_id = trim($_POST['almacen_id']);		
		$descripcion = trim($_POST['descripcion']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$des_reserva = trim($_POST['des_reserva']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$elaboracion = (isset($_POST['elaboracion'])) ? $_POST['elaboracion'] : array();
		$vencimientos = (isset($_POST['fechas'])) ? $_POST['fechas'] : array();
		$facturas = (isset($_POST['facturas'])) ? $_POST['facturas'] : array();
		$costos = (isset($_POST['costos'])) ? $_POST['costos'] : array();
		$lote = (isset($_POST['lote'])) ? $_POST['lote'] : array();
		$duis = (isset($_POST['duis'])) ? $_POST['duis'] : array();
		$contenedores = (isset($_POST['contenedores'])) ? $_POST['contenedores'] : array();
		$ingre_id = (isset($_POST['id_ingreso'])) ? $_POST['id_ingreso'] : 0;
		$nro_cuentas = trim($_POST['nro_cuentas']);
		$plan = trim($_POST['forma_pago']);
		$plan = ($plan=="2") ? "si" : "no";

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();
		
			if ($_POST['reserva']) {
				$reserva = 1;
			} else {
				$reserva = 0;
			}
			
			//obtiene el id del proveedor
			if($_POST['id_proveedor'] > 0 ){
				$id_proveedor = trim($_POST['id_proveedor']);
			}else{
				$datos_prov = array('proveedor' => trim($_POST['id_proveedor']),
									'nit' => 0,
									'direccion' => '',
									'telefono' => 0);

				// se inserta el nuevo proveedor
				$id_proveedor = $db->insert('inv_proveedores', $datos_prov);
			}

			//obtine el proveedor
			$nombre_proveedor = $db->query("SELECT proveedor FROM inv_proveedores WHERE id_proveedor = '{$id_proveedor}'")->fetch_first();
			$nombre_proveedor = ($nombre_proveedor['proveedor']) ? $nombre_proveedor['proveedor'] : '';

			//valida que nombre no este vacio
			if (!$nombre_proveedor || $nombre_proveedor == '' || $nombre_proveedor == null) {
				$nombre_proveedor = $id_proveedor;
				$datos_prov = array('proveedor' => $id_proveedor,
									'nit' => 0,
									'direccion' => '',
									'telefono' => 0);
				//se inserta el nuevo proveedor
				$id_proveedor = $db->insert('inv_proveedores', $datos_prov);
			}
					

			// Obtiene el almacen
			$almacen = $db->from('inv_almacenes')->where('id_almacen', $almacen_id)->fetch_first();

			if($plan=="si"){
				$fechas = (isset($_POST['fecha'])) ? $_POST['fecha']: array();
				$cuotas = (isset($_POST['cuota'])) ? $_POST['cuota']: array();
			}

			// Instancia el ingreso
			$ingreso = array(
				'fecha_ingreso' => date('Y-m-d'),
				'hora_ingreso' => date('H:i:s'),
				'tipo' => 'Compra',
				'descripcion' => $descripcion,
				'monto_total' => $monto_total,			
				'nombre_proveedor' => $nombre_proveedor,
				'proveedor_id' => $id_proveedor,
				'nro_registros' => $nro_registros,
				'almacen_id' => $almacen_id,
				'transitorio' => $reserva,
				'des_transitorio' => $des_reserva,
				'empleado_id' => $_user['persona_id'],
				'plan_de_pagos' => $plan
			);

			// se valida que no se recibio el id de un ingreso para modificar
			if($ingre_id > 0){
				//lo que sobro del lote anterior
				$lotecantidades = (isset($_POST['lote_cantidad'])) ? $_POST['lote_cantidad'] : array();
				$cantidad_anterior = (isset($_POST['cantidad_ant'])) ? $_POST['cantidad_ant'] : array();
				$lote_anterior = (isset($_POST['lote_ant'])) ? $_POST['lote_ant'] : array();

				$id_ingreso = $db->query("select * from inv_ingresos where id_ingreso = '{$ingre_id}'")->fetch_first()['tipo'];
				
				if ($id_ingreso != 'Traspaso') {
					
					//se crea backup de registros 
					$verifica_id = backup_registros($db, 'inv_ingresos', 'id_ingreso', $ingre_id, '', '', $_user['persona_id'], 'SI', 0, "Editado");
					// se modifica el registro con el nuevo dato
					$db->where('id_ingreso',$ingre_id)->update('inv_ingresos', $ingreso);

					// Guarda Historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"),
						'proceso' => 'u',
						'nivel' => 'l',
						'direccion' => '?/ingresos/guardar',
						'detalle' => 'Se modifico el ingreso con identificador numero ' . $ingre_id,
						'usuario_id' => $_SESSION[user]['id_user']
					);
	
					$db->insert('sys_procesos', $data);
		
					//se crea backup de registros a eliminar
					$verifica = backup_registros($db, 'inv_ingresos_detalles', 'ingreso_id', $ingre_id, '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");

					//se eliminan registro
					$db->delete()->from('inv_ingresos_detalles')->where('ingreso_id', $ingre_id)->execute();
		
					foreach ($productos as $nro => $elemento) {
						if($lotecantidades[$nro] == 'a'){
							$lotecantidad = $cantidades[$nro];
							$Cantidad=$db->query("SELECT COUNT(id_detalle)AS cantidad FROM inv_ingresos_detalles WHERE producto_id='{$productos[$nro]}' LIMIT 1")->fetch_first();
							$Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
						}else{
							$lotecantidad = $cantidad_anterior[$nro] - $lotecantidades[$nro];
							$lotecantidad = $cantidades[$nro] - $lotecantidad;
							$Cantidad=$lote_anterior[$nro];
						}
						$fecha = new DateTime($vencimientos[$nro]);
						$vencimientos[$nro] = $fecha->format('Y-m-d');
						// Forma el detalle
		
						$detalle = array(
							'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro] : 0,
							'costo' => (isset($costos[$nro])) ? $costos[$nro] : 0,
							'vencimiento' => (isset($vencimientos[$nro])) ? $vencimientos[$nro] : 0,
							// 'dui' => (isset($duis[$nro])) ? $duis[$nro]: 0,
							'lote2'=>$lote[$nro],
							'elaboracion'=>($elaboracion[$nro]!='') ? $vencimientos[$nro] : '0000-00-00',
							'factura' => (isset($facturas[$nro])) ? $facturas[$nro] : 0,
							'contenedor' => (isset($contenedores[$nro])) ? $contenedores[$nro] : 0,
							'producto_id' => $productos[$nro],
							'ingreso_id' => $ingre_id,
							'lote'=>'lt'.($Cantidad+1),
							'lote_cantidad'=>$lotecantidad,
						);
		
						// Guarda la informacion
						$id_detalle = $db->insert('inv_ingresos_detalles', $detalle);
						// Guarda Historial
						$data = array(
							'fecha_proceso' => date("Y-m-d"),
							'hora_proceso' => date("H:i:s"),
							'proceso' => 'c',
							'nivel' => 'l',
							'direccion' => '?/ingresos/guardar',
							'detalle' => 'Se creo ingreso detalle con identificador numero ' . $id_detalle,
							'usuario_id' => $_SESSION[user]['id_user']
						);
		
						$db->insert('sys_procesos', $data);
					}
		
					$para_pago = $db->from('inv_pagos')->where('movimiento_id', $ingre_id)->where('tipo', 'Ingreso')->fetch_first();
					//se crea backup de registros a eliminar
					$verifica_id = backup_registros($db, 'inv_pagos', 'id_pago', $para_pago["id_pago"], '', '', $_user['persona_id'], 'SI', 0, "Eliminado");
					//se eliminan registro
					$db->delete()->from('inv_pagos')->where('id_pago', $para_pago["id_pago"])->execute();
					//se crea backup de registros a eliminar
					$verifica = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $para_pago['id_pago'], '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");
					//se eliminan registro
					$db->delete()->from('inv_pagos_detalles')->where('pago_id', $para_pago["id_pago"])->execute();

					//se valida si el movimiento tiene plan de pagos
					if($plan=="si") {
						// Instancia el ingreso
						$ingresoPlan = array(
							'movimiento_id' => $ingre_id,
							'interes_pago' => 0,
							'tipo' => 'Ingreso'
						);
						// Guarda la informacion del ingreso general
						$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
						$nro_cuota=0;

						$fecha_format=(is_date($fechas[$nro2])) ? date_encode($fechas[$nro2]): date_encode($fechas[$nro2]);
						$nro_cuota++;
						
						
						$detallePlan = array(
							'nro_cuota' => $nro_cuota,
							'pago_id' => $ingreso_id_plan,
							'fecha' => $fecha_format,
							'fecha_pago' => $fecha_format,
							'empleado_id' => $_user['persona_id'],
							'tipo_pago' => "",
							'monto' => $monto_total,
							'estado'  => '0'
						);
						
						// Guarda la informacion
						$db->insert('inv_pagos_detalles', $detallePlan);
						
					}
				}else {
					// Instancia la variable de notificacion
					$_SESSION[temporary] = array(
						'alert' => 'danger',
						'title' => 'La acción no puede ser realizada!',
						'message' => 'La edición del registro generara afectacion en ele inventario; se recomienda eliminar el registro.'
					);
				}


			} else {
				
				// Guarda la informacion
				$ingreso_id = $db->insert('inv_ingresos', $ingreso);

				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?/ingresos/guardar',
					'detalle' => 'Se creo ingreso con identificador numero ' . $ingreso_id,
					'usuario_id' => $_SESSION[user]['id_user']
				);

				$db->insert('sys_procesos', $data);

				foreach ($productos as $nro => $elemento) {
					$fecha = new DateTime($vencimientos[$nro]);
					$vencimientos[$nro] = $fecha->format('Y-m-d');
					// Forma el detalle
					$Cantidad=$db->query("SELECT COUNT(id_detalle)AS cantidad FROM inv_ingresos_detalles WHERE producto_id='{$productos[$nro]}' LIMIT 1")->fetch_first();
					$Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
					$detalle = array(
						'cantidad' => (isset($cantidades[$nro])) ? $cantidades[$nro] : 0,
						'costo' => (isset($costos[$nro])) ? $costos[$nro] : 0,
						'vencimiento' => (isset($vencimientos[$nro])) ? $vencimientos[$nro] : 0,
						// 'dui' => (isset($duis[$nro])) ? $duis[$nro]: 0,
						'lote2'=>$lote[$nro],
						'elaboracion'=>($elaboracion[$nro]!='') ? $vencimientos[$nro] : '0000-00-00',
						'factura' => (isset($facturas[$nro])) ? $facturas[$nro] : 0,
						'contenedor' => (isset($contenedores[$nro])) ? $contenedores[$nro] : 0,
						'producto_id' => $productos[$nro],
						'ingreso_id' => $ingreso_id,
						'lote'=>'lt'.($Cantidad+1),
						'lote_cantidad'=>(isset($cantidades[$nro])) ? $cantidades[$nro] : 0,
					);

					// Guarda la informacion
					$id_detalle = $db->insert('inv_ingresos_detalles', $detalle);
					// Guarda Historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"),
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/ingresos/guardar',
						'detalle' => 'Se creo ingreso detalle con identificador numero ' . $id_detalle,
						'usuario_id' => $_SESSION[user]['id_user']
					);
					
					$db->insert('sys_procesos', $data);
				}

				if($plan=="si") {
					// Instancia el ingreso
					$ingresoPlan = array(
						'movimiento_id' => $ingreso_id,
						'interes_pago' => 0,
						'tipo' => 'Ingreso'
					);
					// Guarda la informacion del ingreso general
					$ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);

					// Guarda Historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"),
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/ingresos/guardar',
						'detalle' => 'Se creo credito con identificador numero ' . $ingreso_id_plan,
						'usuario_id' => $_SESSION[user]['id_user']
					);
					
					$db->insert('sys_procesos', $data);

					$nro_cuota=0;
					$fecha_format=(is_date($fechas[$nro2])) ? date_encode($fechas[$nro2]): "0000-00-00";
					$nro_cuota++;
					//si la cuota es igual a la actual se guarda como pagada en efectivo

					$detallePlan = array(
						'nro_cuota' => $nro_cuota,
						'pago_id' => $ingreso_id_plan,
						'fecha' => $fecha_format,
						'fecha_pago' => $fecha_format,
						'monto' => $monto_total,
						'tipo_pago' => "",
						'empleado_id' => $_user['persona_id'],
						'estado'  => '0'
					);

					// Guarda la informacion
					$db->insert('inv_pagos_detalles', $detallePlan);
					
				}

				$_user=$db->query("SELECT CONCAT(em.nombres,' ',em.paterno,' ',em.materno)AS empleado
									FROM inv_ingresos AS e
									LEFT JOIN sys_empleados AS em ON em.id_empleado=e.empleado_id
									WHERE e.id_ingreso='{$egreso_id}' LIMIT 1")->fetch_first();
				$_user = ($_user['empleado']) ? $_user['empleado'] : 0;
				Contabilidad($db,0,$almacen_id,$ingreso_id,$monto_total,5,$_user);
			}
			
			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/ingresos/listar');
		
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


