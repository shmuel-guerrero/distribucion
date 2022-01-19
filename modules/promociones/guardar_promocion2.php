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
        $productos = (isset($_POST['productos'])) ? $_POST['productos']: array();
        $unidades = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios']: array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos']: array();
		$almacen_id = trim($_POST['almacen_id']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);

        //obtiene a el cliente
        $unidad_nombre = $db->select('*')->from('inv_unidades')->where('unidad',$unidad_id)->fetch_first();
        if(!$unidad_nombre){
            $unidad_id = $db->insert('inv_unidades',array('unidad' => $unidad_id));
        }else{
        	$unidad_id = $unidad_nombre['id_unidad'];
        }

        // Instancia el producto
        $producto = array(
			'codigo' => $codigo,
			'nombre' => $nombre,
			'nombre_factura' => $nombre,
            'precio_actual' => $monto_total,
			'cantidad_minima' => 1,
			'descripcion' => $descripcion,
			'unidad_id' => $unidad_id,
            'categoria_id' => $categoria_id,
            'promocion' => 'si'
		);
		if ($id_producto > 0) {
            // editar productos
            $condicion = array('id_producto' => $id_producto);

            // Actualiza la informacion
            $db->where($condicion)->update('inv_productos', $producto);

            $db->delete()->from('inv_promociones')->where('id_promocion', $id_producto)->execute();
            // guarda la promocion
            foreach ($productos as $nro => $elemento) {
                $id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidades[$nro])->fetch_first();
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
                $db->insert('inv_promociones', $detalle);
            }

			// Actualizamos el ingreso
			$db->where('producto_id', $id_producto)->update('inv_ingresos_detalles', array('cantidad' => $stock, 'costo' => $monto_total));
			$ingreso_a = $db->from('inv_ingresos_detalles')->where('producto_id', $id_producto)->fetch_first();
			$db->where('id_ingreso', $ingreso_a['ingreso_id'])->update('inv_ingresos', array('monto_total' => $monto_total * $stock));

            // Instancia la variable de notificacion
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Adición satisfactoria!',
                'message' => 'El registro se guardó correctamente.'
            );
		}else{
			// adiciona la fecha y hora de creacion
			$producto['fecha_registro'] = date('Y-m-d');
			$producto['hora_registro'] = date('H:i:s');
			$producto['imagen'] = '';

			// Guarda la informacion
			$id_producto = $db->insert('inv_productos', $producto);

			// guarda la promocion
			foreach ($productos as $nro => $elemento) {
                $id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidades[$nro])->fetch_first();
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
				$db->insert('inv_promociones', $detalle);
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

			//detalle de la promoción
			$detalle_p = array(
				'cantidad' => $stock,
				'costo' => $monto_total,
				'producto_id' => $id_producto,
				'ingreso_id' => $ingreso_id
			);

			// Guarda la informacion
			$db->insert('inv_ingresos_detalles', $detalle_p);
			
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

?>