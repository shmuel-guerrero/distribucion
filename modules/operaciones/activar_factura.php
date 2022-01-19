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
	$estado = ($venta['anulado'] == 0) ? 2 : 0;

	// Instancia el producto
	$dato = array(
		'anulado' => $estado
	);

	// Genera la condicion
	$condicion = array('id_egreso' => $id_factura);

	// Actualiza la informacion
	$db->where($condicion)->update('inv_egresos', $dato);

	// Redirecciona a la pagina principal
	redirect('?/operaciones/facturas_listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>