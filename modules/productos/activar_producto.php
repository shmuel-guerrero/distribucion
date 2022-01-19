<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_producto
$id_producto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();

// Verifica si el producto existe
if ($producto) {
	// Obtiene el nuevo estado
	$estado = ($producto['estado'] == 0) ? 1 : 0;

	// Instancia el producto
	$producto = array(
		'estado' => $estado
	);

	// Genera la condicion
	$condicion = array('id_producto' => $id_producto);

	// Actualiza la informacion
	$db->where($condicion)->update('inv_productos', $producto);

	// Redirecciona a la pagina principal
	redirect('?/productos/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>