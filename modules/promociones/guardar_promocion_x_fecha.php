<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// echo json_encode($_POST); die();

// Verifica si es una peticion post

if (is_post()) {
	//var_dump($_POST);
	//die();
	// Verifica la existencia de los datos enviados
	if (isset($_POST['nombre'])) {
		// Obtiene los datos de la venta
        $codigo = trim($_POST['id_promocion']);

		$nombre 		= trim($_POST['nombre']);
		$tipo 			= 4;
		$fecha_ini 		= trim($_POST['fecha_ini']);
        $fecha_fin 		= trim($_POST['fecha_fin']);
		$descripcion 	= trim($_POST['descripcion']);
		$min_promo 			= trim($_POST['min_promo']);
		$productos_id 			= trim($_POST['productos_id']);

		$unidades 		= (isset($_POST['unidad'])) ? $_POST['unidad']: array();
		$nombres 		= (isset($_POST['nombres'])) ? $_POST['nombres']: array();
		$cantidades 	= (isset($_POST['cantidades'])) ? $_POST['cantidades']: array();
		$precios 		= (isset($_POST['precios'])) ? $_POST['precios']: array();
		$descuentos 	= (isset($_POST['descuentos'])) ? $_POST['descuentos']: array();

		$Datos=array(
			'nombre'			=>$nombre,
			'tipo'				=>$tipo,
			'fecha_ini'			=>$fecha_ini,
			'fecha_fin'			=>$fecha_fin,
			'descripcion'		=>$descripcion,
			'min_promo'			=>$min_promo,
			'descuento_promo'	=>0,
			'monto_promo'		=>0,
			// 'item_promo' 		=> $productos_id,
		);


		if($codigo > 0){
			$Datos=Filtro($Datos);
			$productos_id	=isset($_POST['productos_id'])	?$_POST['productos_id'][0]	:[];
			$precios	=0;
			$unidad		=isset($_POST['unidad'])	?$_POST['unidad'][0]	:[];
			$cantidades	=isset($_POST['cantidades'])?$_POST['cantidades'][0]:[];
			$descuentos	=0;
			$nombres	=isset($_POST['nombres'])	?$_POST['nombres'][0]	:[];
			$Concatenado=array('item_promo'=>"{$productos_id}--{$nombres}--{$precios}--{$unidad}--{$cantidades}--{$descuentos}");
			$Datos=array_merge($Datos,$Concatenado);
			
			// $condicion = array('id_promocion' => $codigo);
			// Actualiza la informacion
			$db->where('id_promocion', $codigo)->update('inv_promociones_monto', $Datos);

		} else {
			$Datos=Filtro($Datos);
			$productos_id	=isset($_POST['productos_id'])	?$_POST['productos_id'][0]	:[];
			$precios	=0;
			$unidad		=isset($_POST['unidad'])	?$_POST['unidad'][0]	:[];
			$cantidades	=isset($_POST['cantidades'])?$_POST['cantidades'][0]:[];
			$descuentos	=0;
			$nombres	=isset($_POST['nombres'])	?$_POST['nombres'][0]	:[];
			$Concatenado=array('item_promo'=>"{$productos_id}--{$nombres}--{$precios}--{$unidad}--{$cantidades}--{$descuentos}");
			$Datos=array_merge($Datos,$Concatenado);
			$idPromocionItems=$db->insert('inv_promociones_monto',$Datos);
		}

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

function Filtro($Datos){
	if($Datos['descuento_promo']==''):
		$Datos['descuento_promo']=0;
	endif;
	if($Datos['monto_promo']==''):
		$Datos['monto_promo']=0;
	endif;
	if($Datos['item_promo']==''):
		$Datos['item_promo']=0;
	endif;
	return $Datos;
}


?>