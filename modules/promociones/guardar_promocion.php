<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// var_dump($_POST);exit();
// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['codigo']) && isset($_POST['nombre'])) {
		// Obtiene los datos de la venta
		$id_producto = trim($_POST['id_producto']);
		$codigo = trim($_POST['codigo']);
		$nombre = trim($_POST['nombre']);
		$stock = trim($_POST['stock']);
		$descripcion = trim($_POST['descripcion']);
		$unidad_id = trim($_POST['unidad_id']);
		$categoria_id = trim($_POST['categoria_id']);
		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$unidades = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
		$almacen_id = trim($_POST['almacen_id']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$id_asignacion = trim($_POST['id_asign']);
		$limite = trim($_POST['limite']);


		//obtiene a el cliente
		$unidad_nombre = $db->select('*')->from('inv_unidades')->where('unidad', $unidad_id)->fetch_first();
		if (!$unidad_nombre) {
			$Datos=array('unidad' => $unidad_id,'descripcion'=>'');
			$unidad_id = $db->insert('inv_unidades',$Datos);

			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/promociones/guardar_promocion',
				'detalle' => 'Se inserto inventario de unidades con identificador numero ' . $unidad_id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);
		} else {
			$unidad_id = $unidad_nombre['id_unidad'];
		}

		// Instancia el producto
		$producto = array(
			'codigo' => $codigo,
			'nombre' => $nombre,
			'nombre_factura' => $nombre,
			'precio_actual' => $monto_total,
			'cantidad_minima' => 10,
			'descripcion' => $descripcion,
			'unidad_id' => $unidad_id,
			'categoria_id' => $categoria_id,
			'promocion' => 'si'
		);
		if ($id_producto > 0) {

			// Actualiza la informacion en productos
			$db->where('id_producto', $id_producto)->update('inv_productos', $producto);

			// Actualiza la el precio y unidad en asignaciones
			$precio = array('otro_precio' => $monto_total, 'unidad_id' => $unidad_id);
			$db->where('id_asignacion', $id_asignacion)->update('inv_asignaciones', $precio);

			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/promociones/guardar_promocion',
				'detalle' => 'Se actualizo inventario de producto con identificador numero ' . $id_producto,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			$db->delete()->from('inv_promociones')->where('id_promocion', $id_producto)->execute();

			//Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'd',
				'nivel' => 'l',
				'direccion' => '?/promociones/guardar_promocion',
				'detalle' => 'Se elimino producto con identificador numero ' . $id_producto,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// guarda la promocion
			foreach ($productos as $nro => $elemento) {
				$id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidades[$nro])->fetch_first();
				$id_unidad = $id_uni['id_unidad'];

				// Forma el detalle
				$detalle = array(
					'cantidad' => $cantidades[$nro],
					'precio' => $precios[$nro],
					'descuento' => $descuentos[$nro],
					'unidad_id' => $id_unidad,
					'producto_id' => $productos[$nro],
					'id_promocion' => $id_producto
				);

				// Guarda la informacion
				$id = $db->insert('inv_promociones', $detalle);

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?promociones/guardar_promocion',
					'detalle' => 'Se inserto el producto con identificador numero ' . $id,
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
		} else {
			// adiciona la fecha y hora de creacion
			$producto['fecha_registro'] = date('Y-m-d');
			$producto['hora_registro'] = date('H:i:s');
			$producto['imagen'] = '';
			$producto['fecha_vencimiento'] = $limite;
			$producto['fecha_limite'] = $limite;

			// Guarda la informacion
			$id_producto = $db->insert('inv_productos', $producto);

			//asignacion de precio
			$data_asig = array(
				'producto_id' => $id_producto,
				'unidad_id' => $unidad_id,
				'cantidad_unidad' => 1,
				'otro_precio' => $monto_total,
				//'precio_actual' => $monto_total,
				//'tipo_entrada' =>'principal',
				//'utilidad' => '0',
				//'estado' => 1
			);
			$id_asign = $db->insert('inv_asignaciones', $data_asig);

			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?promociones/guardar_promocion',
				'detalle' => 'Se inserto inventario producto con identificador numero ' . $id_producto,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// guarda la promocion
			foreach ($productos as $nro => $elemento) {
				$id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidades[$nro])->fetch_first();
				$id_unidad = $id_uni['id_unidad'];
				$id_asignacion = $db->select('id_asignacion')->from('inv_asignaciones')->where('producto_id', $elemento)->fetch_first();

				// Forma el detalle
				$detalle = array(
					'cantidad' => $cantidades[$nro],
					'precio' => $precios[$nro],
					'descuento' => $descuentos[$nro],
					'unidad_id' => $id_unidad,
					'producto_id' => $productos[$nro],
					'id_promocion' => $id_producto,
					//'asignacion_id' => $id_asignacion['id_asignacion']
				);

				// Guarda la informacion
				$id = $db->insert('inv_promociones', $detalle);

				// Guarda en el historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"),
					'proceso' => 'c',
					'nivel' => 'l',
					'direccion' => '?promociones/guardar_promocion',
					'detalle' => 'Se inserto inventario producto con identificador numero ' . $id,
					'usuario_id' => $_SESSION[user]['id_user']
				);
				$db->insert('sys_procesos', $data);
			}

			// Instancia el ingreso
			$ingreso = array(
				'fecha_ingreso' => date('Y-m-d'),
				'hora_ingreso' => date('H:i:s'),
				'tipo' => 'Compra',
				'descripcion' => 'Ingreso de promociones',
				'monto_total' => $monto_total * $stock,
				'descuento' => 0,
				'monto_total_descuento' => 0,
				'nombre_proveedor' => 'Promoción',
				'nro_registros' => 1,
				'almacen_id' => $almacen_id,
				'transitorio' => 0,
				'des_transitorio' => '',
				'empleado_id' => $_user['persona_id']
			);

			// Guarda la informacion
			$ingreso_id = $db->insert('inv_ingresos', $ingreso);
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?promociones/guardar_promocion',
				'detalle' => 'Se inserto el inventario ingreso con identificador numero ' . $ingreso_id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			$pa_lote=$db->query("SELECT COUNT(id_detalle)AS cantidad FROM inv_ingresos_detalles WHERE producto_id='{$id}' LIMIT 1")->fetch_first();
			$pa_lote = ($pa_lote['cantidad']) ? $pa_lote['cantidad'] : 0;
			//detalle de la promoción
			$detalle_p = array(
				'cantidad' => $stock,
				'lote'=>'lt'.($pa_lote+1),
				'lote_cantidad' => $stock,
				'costo' => $monto_total,
				'producto_id' => $id_producto,
				'ingreso_id' => $ingreso_id,
				'lote2'=>'',
				'elaboracion' => date('Y-m-d'),
				'vencimiento'=>$limite,
			);

			// Guarda la informacion
			$id = $db->insert('inv_ingresos_detalles', $detalle_p);
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?promociones/guardar_promocion',
				'detalle' => 'Se inserto inventario ingreso detalle con identificador numero ' . $id,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}

		// Redirecciona a la pagina principal
		redirect('?/promociones/promocion_x_item');
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
