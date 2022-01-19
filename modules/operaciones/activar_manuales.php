<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author  FABIO CHOQUE
 */

// Obtiene el id_producto
$id_factura = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el producto
$venta = $db->from('inv_egresos')->where('id_egreso', $id_factura)->fetch_first();

// Verifica si el producto existe
if ($venta) {
	// Obtiene el nuevo estado
	$estado = ($venta['anulado'] == 0) ? 1 : 0;

	// Instancia el producto
	$dato = array(
		'anulado' => $estado
	);

	// Genera la condicion
	$condicion = array('id_egreso' => $id_factura);

	// Actualiza la informacion
	$db->where($condicion)->update('inv_egresos', $dato);
	
	// echo json_encode($Lotes); die();
	$_SESSION[temporary] = array(
		'alert' => 'success',
		'title' => 'Se anulo correctamente!',
		'message' => 'La operacion se realizó correctamente.'
	);

	// Redirecciona a la pagina principal
	redirect('?/operaciones/manuales_listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>