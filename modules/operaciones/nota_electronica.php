<?php
/**
 * SimplePHP - Simple Framework PHP
 *
 * @package  SimplePHP
 * @author   Checkcode2.1
 */

// Verifica si es una peticion ajax y post
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	$id_egreso = trim($_POST['id_venta']);

	if ($id_egreso) {
            require_once libraries . '/controlcode-class/ControlCode.php';
            require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
            $hoy = date('Y-m-d');

    		// Obtiene la dosificacion del periodo actual
    		$dosificacion = $db->from('inv_dosificaciones')
							// ->join('inv_sucursal', 'id_sucursal=sucursal_id', 'inner')
							->where('fecha_registro <=', $hoy)
							->where('fecha_limite >=', $hoy)
							->where('activo', 'S')
							->fetch_first();

            $egreso = $db->query('SELECT*FROM inv_egresos WHERE id_egreso ='.$id_egreso)->fetch_first();
            
			// Obtiene los datos para el codigo de control
			$nro_autorizacion = $dosificacion['nro_autorizacion'];
			$nro_factura = intval($dosificacion['nro_facturas']) + 1;
			$nit_ci = $egreso['nit_ci'];
			$fecha = date('Ymd');
            $monto_total = $egreso['monto_total'];
			$total = round($monto_total, 0);
			$llave_dosificacion = base64_decode($dosificacion['llave_dosificacion']);

			// Genera el codigo de control
			$codigo_control = new ControlCode();
			$codigo_control = $codigo_control->generate($nro_autorizacion, $nro_factura, $nit_ci, $fecha, $total, $llave_dosificacion);

        $egreso = array(
			'provisionado' => 'N',
            'nro_autorizacion' => $nro_autorizacion,
            'codigo_control' => $codigo_control,
            'nro_factura' => $nro_factura
		);		
		// Actualiza la informacion
		$condicion = array('id_egreso' => $id_egreso);
		$db->where($condicion)->update('inv_egresos', $egreso);
		$db->where('id_dosificacion', $dosificacion['id_dosificacion'])->update('inv_dosificaciones', array('nro_facturas' => $nro_factura));
        
	// 	$nit_ci 		= trim($_POST['nit_ci']);
	// 	$nombre_cliente = trim($_POST['nombre_cliente']);
	// 	$telefono 		= trim($_POST['telefono']);
	// 	$direccion 		= trim($_POST['direccion']);
	// 	$observacion 	= trim($_POST['observacion']);
	// 	$productos 		= (isset($_POST['productos'])) ? $_POST['productos'] : array();
	// 	$asignaciones   = (isset($_POST['asignacion'])) ? $_POST['asignacion']: array();
	// 	$nombres 		= (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
	// 	$cantidades 	= (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
	// 	$precios 		= (isset($_POST['costos'])) ? $_POST['costos'] : array();
	// 	$nro_registros 	= trim($_POST['nro_registros']);
	// 	$monto_total 	= trim($_POST['monto_total']);
	// 	$id_proforma 	= trim($_POST['proforma_id']);
		
	// 	$valor_descuento= 0;
		
	// 	$almacen_id 	= trim($_POST['almacen_id']);
	// 	$sucursal_id	= trim($_POST['sucursal_id']);
	// 	$tipo_pago 		= trim($_POST['tipo_pago']);
	// 	// var_dump($nota); die();
	// 	// Obtiene el numero de nota
	// 	$nro_factura = $db->query("select count(id_egreso) + 1 as nro_factura 
	// 								from inv_egresos 
	// 								where tipo = 'Venta' AND provisionado = 'S' AND almacen_id = '$almacen_id'
	// 								")->fetch_first();

	// 	$nro_factura = $nro_factura['nro_factura'];
		
	// 	// Define la variable de subtotales
	// 	$subtotales = array();

	// 	// Obtiene la moneda
	// 	$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
	// 	$moneda = ($moneda) ? $moneda['moneda'] : '';

	// 	// Obtiene los datos del monto total
	// 	$conversor = new NumberToLetterConverter();

	// 	$monto_textual = explode('.', $monto_total);
	// 	$monto_numeral = $monto_textual[0];
	// 	$monto_decimal = $monto_textual[1];
	// 	$monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));
		
		

	// 	// Instancia la nota
	// 	$nota = array(
	// 		'fecha_egreso' 		=> date('Y-m-d'),
	// 		'hora_egreso'		=> date('H:i:s'),
	// 		'tipo' 				=> 'Venta',
	// 		'provisionado' 		=> 'S',
	// 		'descripcion' 		=> 'Orden de compra',
	// 		'nro_factura' 		=> $nro_factura,
	// 		'nro_autorizacion'	=> '',
	// 		'codigo_control' 	=> '',
	// 		'fecha_limite' 		=> '0000-00-00',
	// 		'monto_total' 		=> $monto_total,
	// 		'nit_ci' 			=> $nit_ci,
	// 		'telefono' 			=> $telefono,
	// 		'direccion' 		=> $direccion,
	// 		'descuento' 		=> $valor_descuento,
	// 		'observacion' 		=> $observacion,
	// 		'nombre_cliente' 	=> mb_strtoupper($nombre_cliente, 'UTF-8'),
	// 		'nro_registros' 	=> $nro_registros,
	// 		'dosificacion_id' 	=> 0,
	// 		'almacen_id' 		=> $almacen_id,
	// 		'sucursal_id' 		=> $sucursal_id,
	// 		'plan_de_pagos' 	=> 'no',
	// 		'tipo_de_pago' 		=> 'Efectivo',
	// 		'empleado_id' 		=> $_user['persona_id']
	// 		// 'convertido'		=> 'proforma'
	// 	);
		
	// 	// Guarda la informacion
	// 	$egreso_id = $db->insert('inv_egresos', $nota);
		
	// 	$res_client = $db->query("SELECT id_cliente 
	// 								FROM inv_clientes 
	// 								WHERE nit_ci='$nit_ci' AND telefono='$telefono' AND escalafon='$direccion' AND nombre_cliente='$nombre_cliente'")->fetch();
	// 	if(!$res_client){
	// 		$client = array(
	// 			'nit_ci' => $nit_ci,
	// 			'telefono' => $telefono,
	// 			'escalafon' => $direccion,
	// 			'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8')
	// 		);
	// 		$db->insert('inv_clientes', $client);
	// 	}

	// 	// Recorre los productos
	// 	foreach ($productos as $nro => $elemento) {
			
	// 		$lamismavariable = $db->query("SELECT * FROM inv_asignaciones q
    //                                  LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad
    //                                  WHERE tipo = 'principal' and producto_id='".$productos[$nro]."'")->fetch_first();
	// 		$unidad[$nro] = $lamismavariable["unidad"];
	// 		$nombres[$nro]=$nombres[$nro]." (".$unidad[$nro].")";
	// 		$asig = $lamismavariable["id_asignacion"];
			
	// 		// Forma el detalle
	// 		$detalle = array(
	// 			'cantidad' 		=> $cantidades[$nro],
	// 			'precio' 		=> $precios[$nro],
	// 			'asignacion_id' => $asig,
	// 			'producto_id' 	=> $productos[$nro],
	// 			'egreso_id' 	=> $egreso_id
	// 		);

	// 		// Genera los subtotales
	// 		$subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

	// 		// Guarda la informacion
	// 		$db->insert('inv_egresos_detalles', $detalle);
	// 	}

	// 	// Instancia la respuesta
	// 	$respuesta = array(
	// 		'papel_ancho' => 10,
	// 		'papel_alto' => 30,
	// 		'papel_limite' => 576,
	// 		'empresa_nombre' => $_institution['nombre'],
	// 		'empresa_sucursal' => 'SUCURSAL Nº 1',
	// 		'empresa_direccion' => $_institution['direccion'],
	// 		'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
	// 		'empresa_ciudad' => 'LA PAZ - BOLIVIA',
	// 		'empresa_actividad' => $_institution['razon_social'],
	// 		'empresa_nit' => $_institution['nit'],
	// 		'nota_titulo' => 'N O T A   D E   R E M I S I Ó N',
	// 		'nota_numero' => $nota['nro_factura'],
	// 		'nota_fecha' => date_decode($nota['fecha_egreso'], 'd/m/Y'),
	// 		'nota_hora' => substr($nota['hora_egreso'], 0, 5),
	// 		'cliente_nit' => $nota['nit_ci'],
	// 		'cliente_nombre' => $nota['nombre_cliente'],
	// 		//'venta_titulos' => array('CANTIDAD', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'IMPORTE TOTAL'),
	// 		'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL', 'IMPORTE TOTAL','DESCUENTO','IMPORTE TOTAL CON DESCUENTO'),
	// 		'venta_cantidades' => $cantidades,
	// 		'venta_detalles' => $nombres,
	// 		'venta_precios' => $precios,
	// 		'venta_subtotales' => $subtotales,
	// 		'venta_total_numeral' => $nota['monto_total'],
	// 		'venta_total_literal' => $monto_literal,
	// 		'venta_total_decimal' => $monto_decimal . '/100',
	// 		'venta_moneda' => $moneda,
	// 		'impresora' => $_terminal['impresora'],
	// 		'forma_de_pago' =>'Efectivo',
	// 		'venta_valor_descuento' => $valor_descuento,
	// 		'venta_total_descuento' => $monto_total
	// 	);

	// 	//Termico
	// 	//echo json_encode($respuesta);
		
		//PDF
		echo json_encode($id_egreso);

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