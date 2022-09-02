<?php


// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['codigo']) && isset($_POST['codigo_barras']) && 
	isset($_POST['nombre']) && isset($_POST['nombre_factura']) && isset($_POST['cantidad_minima']) && 
	isset($_POST['precio_actual']) && isset($_POST['unidad_id']) && isset($_POST['categoria_id']) && 
	isset($_POST['descripcion'])) {

		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$codigo = trim($_POST['codigo']);
		$codigo_barras = trim($_POST['codigo_barras']);
		$nombre = trim($_POST['nombre']);
		$color = (isset($_POST['color'])) ? trim($_POST['color']) : '';
		// $fecha_ven = trim($_POST['ven_fecha']);
		$nombre_factura = trim($_POST['nombre_factura']);
		$cantidad_minima = trim($_POST['cantidad_minima']);

		$precio_actual = ($_POST['precio_actual']) ? trim($_POST['precio_actual']) : '0.00';
		$precio_sugerido = ($_POST['precio_sugerido']) ? trim($_POST['precio_sugerido']) : '0.00';

		$unidad_id = trim($_POST['unidad_id']);
		$categoria_id = trim($_POST['categoria_id']);
		$marca_id = trim($_POST['marca_id']);
		
		$ubicacion = ($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
		$descripcion = trim($_POST['descripcion']);
		// $contenedor = trim($_POST['contenedor']);
		// $dui = trim($_POST['dui']);

		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();


			// Instancia el producto
			$producto = array(
				'codigo' => $codigo,
				'codigo_barras' => 'CB' . $codigo_barras,
				'nombre' => $nombre,
				'nombre_factura' => $nombre_factura,
				'precio_actual' => $precio_actual,
				'precio_sugerido' => $precio_sugerido,
				'cantidad_minima' => $cantidad_minima,
				'ubicacion' => $ubicacion,
				'descripcion' => $descripcion,
				'unidad_id' => $unidad_id,
				'categoria_id' => $categoria_id,
				'marca_id' => ($marca_id) ? $marca_id : 0
			);

			// Verifica si es creacion o modificacion
			if ($id_producto > 0) {
				// Genera la condicion
				$condicion = array('id_producto' => $id_producto);

				// Actualiza la informacion
				$db->where($condicion)->update('inv_productos', $producto);
				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'u',
					'nivel' => 'l',
					'direccion' => '?/productos/guardar',
					'detalle' => 'Se actualizó inventario de producto con identificador número ' . $id_producto,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);

				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			} else {

				//obtiene el plan habilitado.
				$plan = $db->query("SELECT sp.plan FROM sys_planes sp WHERE sp.estado = 'Activo'")->fetch_first()['plan'];

				// se obtiene el limite permitido de creacion registros de clientes
				$limite = validar_plan($db, array('plan' => $plan, 'caracteristica' => 'productos'));

				//obtiene la cantidad de registros en la base de datos
				$registros = $db->query("SELECT count(*)as nro_registros FROM inv_productos")->fetch_first()['nro_registros'];

				//Valida que los registros sean menor o igual al limite del plan
				if ($registros <= $limite) {

					// adiciona la fecha y hora de creacion
					$producto['fecha_registro'] = date('Y-m-d');
					$producto['hora_registro'] = date('H:i:s');
					$producto['imagen'] = '';

					// Guarda la informacion
					$id_producto = $db->insert('inv_productos', $producto);

					// Guarda en el historial
					$data = array(
						'fecha_proceso' => date("Y-m-d"),
						'hora_proceso' => date("H:i:s"),
						'proceso' => 'c',
						'nivel' => 'l',
						'direccion' => '?/productos/guardar',
						'detalle' => 'Se inserto el producto con identificador numero ' . $id_producto,
						'usuario_id' => $_SESSION[user]['id_user']
					);
					$db->insert('sys_procesos', $data);

					// Instancia la variable de notificacion
					$_SESSION[temporary] = array(
						'alert' => 'success',
						'title' => 'Adición satisfactoria!',
						'message' => 'El registro se guardó correctamente.'
					);
				} else {
					
					//se cierra transaccion
					$db->commit();

					// Instancia la variable de notificacion
					$_SESSION[temporary] = array(
						'alert' => 'danger',
						'title' => 'Adicion restringida!',
						'message' => 'Excedio el limite de registros permitidos en el plan obtenido.'
					);
					
					// Redirecciona a la pagina principal
					redirect('?/productos/listar');
					exit();
				}
			}

			//se cierra transaccion
			$db->commit();

			// Redirecciona a la pagina principal
			redirect('?/productos/ver/' . $id_producto);

		} catch (Exception $e) {
			$status = false;
			$error = $e->getMessage();
			//se cierra transaccion
			$db->rollback();

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
			);
			// Redirecciona a la pagina principal o anterior			
			return redirect(back());
			//Se devuelve el error en mensaje json
			//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

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
