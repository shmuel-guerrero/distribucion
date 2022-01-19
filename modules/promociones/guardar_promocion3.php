<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
//var_dump($_POST);exit();

// Verifica si es una peticion post

if (is_post()) {
	//var_dump($_POST);
	//die();
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nombre'])) {
		// Obtiene los datos de la venta
		
        $codigo = trim($_POST['id_promocion']);
        //$promocion_id = trim($_POST['id_promocion']);
		$nombre = trim($_POST['nombre']);
        $monto = trim($_POST['min_promo']);
        $fecha_ini = trim($_POST['fecha_ini']);
        $fecha_fin = trim($_POST['fecha_fin']);
		$descripcion = trim($_POST['descripcion']);
		$tipo = trim($_POST['tipo']);
        $monto_promo = ($_POST['monto_promo'])?$_POST['monto_promo']:'0.00';
        $descuento_promo = ($_POST['descuento_promo'])?$_POST['descuento_promo']:'0.00';
        $item_promo = trim($_POST['codigo']);
		$productos_id = trim($_POST['productos_id']);
        $unidades = (isset($_POST['unidad'])) ? $_POST['unidad']: array();
		$nombres = (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios = (isset($_POST['precios'])) ? $_POST['precios']: array();
		$descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos']: array();
		$almacen_id = trim($_POST['almacen_id']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);

		$grupo = $_POST['grupos_item'];
		$grupos = explode(',',$grupo);
		$cliente = $_POST['cliente_id'];
		$clients = explode(',',$cliente);

		// $detalle1 = array(
		// 	'monto_promo' => $monto_promo,
		// 	'descuento_promo' => $descuento_promo,
		// 	'item_promo' => $productos[$nro],
		// 	'min_promo' => $monto,
		// 	'descripcion' => $descripcion,
		// 	'fecha_fin' => $fecha_fin,
		// 	'fecha_ini' => $fecha_ini,
		// 	'tipo' => $tipo,
		// 	'nombre' => $nombre
		// 	//'id_promocion' => $codigo
		// );

		//if ($id_producto >= 0 && $descuento_promo > 0 && $monto_promo > 0) {
		if ($codigo>0) {
			if($tipo == 2)
			{				
				$promo_data = array(
					'monto_promo' => $monto_promo,
					'descuento_promo' => $descuento_promo,
					'item_promo' => 0,
					'min_promo' => $monto,
					'descripcion' => $descripcion,
					'fecha_fin' => $fecha_fin,
					'fecha_ini' => $fecha_ini,
					'tipo' => $tipo,
					'nombre' => $nombre							
				);
				$condicion = array('id_promocion' => $codigo);			
				// Actualiza la informacion
				$db->where($condicion)->update('inv_promociones_monto', $promo_data);
			
				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'u',
					'nivel' => 'l',
					'direccion' => '?/promociones/guardar_promocion_x_fecha',
					'detalle' => 'Se actualizo promocion con identificador numero ' . $codigo ,
					'usuario_id' => $_SESSION[user]['id_user']			
				);			
				$db->insert('sys_procesos', $data) ;			
				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			}else{
				
				$promo_data = array(
					'monto_promo' => 0,
					'descuento_promo' => $descuento_promo,
					'item_promo' => $productos_id,
					'min_promo' => $monto,
					'descripcion' => $descripcion,
					'fecha_fin' => $fecha_fin,
					'fecha_ini' => $fecha_ini,
					'tipo' => $tipo,
					'nombre' => $nombre
					//'id_promocion' => $codigo
				);
				$condicion = array('id_promocion' => $codigo);			
				// Actualiza la informacion
				$db->where($condicion)->update('inv_promociones_monto', $promo_data);
				
				// Guarda Historial
				$data = array(
					'fecha_proceso' => date("Y-m-d"),
					'hora_proceso' => date("H:i:s"), 
					'proceso' => 'u',
					'nivel' => 'l',
					'direccion' => '?/promociones/guardar_promocion_x_fecha',
					'detalle' => 'Se actualizo promocion con identificador numero ' . $codigo ,
					'usuario_id' => $_SESSION[user]['id_user']			
				);			
				$db->insert('sys_procesos', $data) ;			
				// Instancia la variable de notificacion
				$_SESSION[temporary] = array(
					'alert' => 'success',
					'title' => 'Actualización satisfactoria!',
					'message' => 'El registro se actualizó correctamente.'
				);
			}

		}else
		{
			// iser's
			if($tipo == 2)
			{
				$data = array(
					'monto_promo' => $monto_promo,
					'descuento_promo' => $descuento_promo,
					'item_promo' => 0,
					'min_promo' => $monto,
					'descripcion' => $descripcion,
					'fecha_fin' => $fecha_fin,
					'fecha_ini' => $fecha_ini,
					'tipo' => $tipo,
					'nombre' => $nombre
					//'id_promocion' => $codigo
				);
		
						// Guarda la informacion
				$id_promocion = $db->insert('inv_promociones_monto', $data);

				foreach($grupos as $grupo){
					$datosg = array(
						'promocion_monto_id' => $id_promocion,
						'cliente_grupo_id' => $grupo,
						'cliente_id' => '0'
					);

					$db->insert('inv_participantes_promos', $datosg);
				}

				foreach($clients as $client){
					$datosc = array(
						'promocion_monto_id' => $id_promocion,
						'cliente_grupo_id' => '0',
						'cliente_id' => $client
					);

					$db->insert('inv_participantes_promos', $datosc);
				}
			}else{
				$detalle = array(
					'monto_promo' => 0,
					'descuento_promo' => $descuento_promo,
					'item_promo' => $productos_id,
					'min_promo' => $monto,
					'descripcion' => $descripcion,
					'fecha_fin' => $fecha_fin,
					'fecha_ini' => $fecha_ini,
					'tipo' => $tipo,
					'nombre' => $nombre
					//'id_promocion' => $codigo
				);
		
				// Guarda la informacion
				$id_promocion = $db->insert('inv_promociones_monto', $detalle);
				/**************************************/
				
			}
		}
				
		// Redirecciona a la pagina principal
		redirect('?/promociones/reporte_promos_monto');
		
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