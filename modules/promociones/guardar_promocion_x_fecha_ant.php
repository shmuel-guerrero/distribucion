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
	if (isset($_POST['nombre'])) {
		// Obtiene los datos de la venta
		$id_producto = trim($_POST['id_producto']);
        //$codigo = trim($_POST['id_promocion']);
		$nombre = trim($_POST['nombre']);
        $monto = trim($_POST['min_promo']);
        $fecha_ini = trim($_POST['fecha_ini']);
        $fecha_fin = trim($_POST['fecha_fin']);
		$descripcion = trim($_POST['descripcion']);
		$tipo = trim($_POST['tipo']);
        $monto_promo = trim($_POST['monto_promo']);
        $descuento_promo = trim($_POST['descuento_promo']);
        $item_promo = trim($_POST['codigo']);

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
            // Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/promociones/guardar_promocion_x_fecha',
				'detalle' => 'Se inserto inventario unidad con identificador número ' . $unidad_id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;
			
        }else{
        	$unidad_id = $unidad_nombre['id_unidad'];
		}
		$detalle1 = array(
			'monto_promo' => $monto_promo,
			'descuento_promo' => $descuento_promo,
			'item_promo' => $productos[$nro],
			'min_promo' => $monto,
			'descripcion' => $descripcion,
			'fecha_fin' => $fecha_fin,
			'fecha_ini' => $fecha_ini,
			'tipo' => $tipo,
			'nombre' => $nombre
			//'id_promocion' => $codigo
		);
        // Instancia el producto
        $producto = array(
			'codigo' => $codigo,
			'nombre' => $nombre,
			'nombre_factura' => $nombre,
            'precio_actual' => $monto_total,
			'cantidad_minima' => 1,
			'descripcion' => $descripcion,
			//'unidad_id' => $unidad_id,
            'categoria_id' => $categoria_id,
            'promocion' => 'si'
		);
		if ($id_producto >= 0 && $descuento_promo > 0 && $monto_promo > 0) {
			// Guarda la informacion
			//$db->insert('inv_promociones_monto', $detalle1);
            // editar productos
            $condicion = array('id_producto' => $id_producto);

            // Actualiza la informacion
            $db->where($condicion)->update('inv_productos', $producto);
            // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/promociones/guardar_promocion_x_fecha',
				'detalle' => 'Se actualizo inventario de producto con identificador numero ' . $id_producto,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;

			$db->delete()->from('inv_promociones')->where('id_promocion', $id_producto)->execute();
			//Guarda en el historial
        	$data = array(
        		'fecha_proceso' => date("Y-m-d"),
        		'hora_proceso' => date("H:i:s"), 
        		'proceso' => 'd',
        		'nivel' => 'l',
        		'direccion' => '?/promociones/guardar_promocion_x_fecha',
        		'detalle' => 'Se elimino inventario promociones con identificador numero ' . $id_producto ,
        		'usuario_id' => $_SESSION[user]['id_user']			
        	);			
        	$db->insert('sys_procesos', $data) ;
			

            // guarda la promocion
            foreach ($productos as $nro => $elemento) {
                $id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidades[$nro])->fetch_first();
                $id_unidad = $id_uni['id_unidad'];

				$detalle = array(
					'monto_promo' => $monto_promo,
					'descuento_promo' => $descuento_promo,
					'item_promo' => $productos[$nro],
					'min_promo' => $monto,
					'descripcion' => $descripcion,
					'fecha_fin' => $fecha_fin,
					'fecha_ini' => $fecha_ini,
					'tipo' => $tipo,
					'nombre' => $nombre
					//'id_promocion' => $codigo
				);
		
				// Guarda la informacion
				//$db->insert('inv_promociones_monto', $detalle1);
				
            }
			$id = $db->insert('inv_promociones_monto', $detalle1);
			
			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/promociones/guardar_promocion_x_fecha',
				'detalle' => 'Se inserto inventario promociones monto con identificador numero ' . $id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;

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
			//$db->insert('inv_promociones_monto', $detalle1);
			// Guarda la informacion
			//$id_producto = $db->insert('inv_productos', $producto);

			// guarda la promocion
			foreach ($productos as $nro => $elemento) {
                $id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad',$unidades[$nro])->fetch_first();
				$id_unidad = $id_uni['id_unidad'];
				
				$detalle = array(
					'monto_promo' => $monto_promo,
					'descuento_promo' => $descuento_promo,
					'item_promo' => $productos[$nro],
					'min_promo' => $monto,
					'descripcion' => $descripcion,
					'fecha_fin' => $fecha_fin,
					'fecha_ini' => $fecha_ini,
					'tipo' => $tipo,
					'nombre' => $nombre
					//'id_promocion' => $codigo
				);
		
				// Guarda la informacion
				$id = $db->insert('inv_promociones_monto', $detalle);
				
				// Guarda en el historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'c',
    				'nivel' => 'l',
    				'direccion' => '?/promociones/guardar_promocion_x_fecha',
    				'detalle' => 'Se inserto inventario promociones monto con identificador numero ' . $id ,
    				'usuario_id' => $_SESSION[user]['id_user']			
    			);			
    			$db->insert('sys_procesos', $data) ;
				
			}
			//$db->insert('inv_promociones_monto', $detalle);
			/*
			// Instancia el ingreso
			$ingreso = array(
				'fecha_ingreso' => date('Y-m-d'),
				'hora_ingreso' => date('H:i:s'),
				'tipo' => 'Compra',
				'descripcion' => 'Ingreso de promociones',
				'monto_total' => $monto_total * $stock,
				'descuento' => $descuento_promo,
				'monto_total_descuento' => $monto_promo,
				'nombre_proveedor' => 'Promoción Por Fecha',
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
			);*/
		}

		// Redirecciona a la pagina principal
		redirect('?/productos/listar');
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