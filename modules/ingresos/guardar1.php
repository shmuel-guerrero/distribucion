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
	// Verifica la existencia de los datos enviados
	if (isset($_POST['almacen_id']) && isset($_POST['nombre_proveedor']) && isset($_POST['descripcion']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['productos']) && isset($_POST['cantidades']) && isset($_POST['costos'])) {
		// Obtiene los datos del producto
		$almacen_id = trim($_POST['almacen_id']);
		$nombre_proveedor = trim($_POST['nombre_proveedor']);
		$descripcion = trim($_POST['descripcion']);
		$nro_registros = trim($_POST['nro_registros']);
		$monto_total = trim($_POST['monto_total']);
		$des_reserva = trim($_POST['des_reserva']);
		if ($_POST['reserva']) {
			$reserva = 1;
		} else {
			$reserva = 0;
		}

		//descuento
		// 		$descuento = trim($_POST['descuento']);		
		// 		$total_importe_descuento = trim($_POST['total_importe_descuento']);

		$productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
		$cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
		$elaboracion = (isset($_POST['elaboracion'])) ? $_POST['elaboracion'] : array();
		$vencimientos = (isset($_POST['fechas'])) ? $_POST['fechas'] : array();
		$facturas = (isset($_POST['facturas'])) ? $_POST['facturas'] : array();
		$costos = (isset($_POST['costos'])) ? $_POST['costos'] : array();
		$lote = (isset($_POST['lote'])) ? $_POST['lote'] : array();
		$duis = (isset($_POST['duis'])) ? $_POST['duis'] : array();
		$contenedores = (isset($_POST['contenedores'])) ? $_POST['contenedores'] : array();

		// Obtiene el almacen
		$almacen = $db->from('inv_almacenes')->where('id_almacen', $almacen_id)->fetch_first();

		// Instancia el ingreso
		$ingreso = array(
			'fecha_ingreso' => date('Y-m-d'),
			'hora_ingreso' => date('H:i:s'),
			'tipo' => 'Compra',
			'descripcion' => $descripcion,
			'monto_total' => $monto_total,
			// 			'descuento' => $descuento,
			// 			'monto_total_descuento' => $total_importe_descuento,
			'nombre_proveedor' => $nombre_proveedor,
			'nro_registros' => $nro_registros,
			'almacen_id' => $almacen_id,
			'transitorio' => $reserva,
			'des_transitorio' => $des_reserva,
			'empleado_id' => $_user['persona_id']
		);

		// Guarda la informacion
		$ingreso_id = $db->insert('inv_ingresos', $ingreso);

		$db->insert('inv_proveedores', array('proveedor' => $nombre_proveedor));

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

		// Redirecciona a la pagina principal
		redirect('?/ingresos/listar');
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
