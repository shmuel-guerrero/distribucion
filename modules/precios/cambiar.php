<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['precio']) && isset($_POST['id_asignacion_precio'])) {
		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$precio = trim($_POST['precio']);
		$id_asignacion_precio = trim($_POST['id_asignacion_precio']);
		$estado = false;

		if ($id_asignacion_precio > 0) {
			$id_asignacion = $db->query("SELECT * FROM inv_asignaciones a WHERE a.id_asignacion = {$id_asignacion_precio} and a.visible = 's'")->fetch_first();

			
			
			if ($id_asignacion) {
				$datos = array(
					'id_asignacion' => $id_asignacion['id_asignacion'], 
					'producto_id' => $id_producto,
					'visible' => 's'
				);

				$db->where($datos)->update('inv_asignaciones', array('otro_precio' => $precio));
				$estado = true;
			}
		}else {
			$id_product = $db->query("SELECT * FROM inv_productos p WHERE p.id_producto = {$id_producto} and p.eliminado = '0'")->fetch_first();
			if ($id_product && $id_asignacion_precio == 'P') {
				$db->where('id_producto', $id_producto)->update('inv_productos', array('precio_actual' => $precio));
				$estado = true;
			}
		}

		if ($estado) {
			
			// Instancia el producto
			$producto = array(
				'precio' => $precio,
				'fecha_registro' => date('Y-m-d'),
				'hora_registro' => date('H:i:s'),
				'producto_id' => $id_producto,
				'empleado_id' => $_user['persona_id']
			);
			
			// Guarda la informacion
			$db->insert('inv_precios', $producto);

			// Envia respuesta
			echo json_encode($producto);
		}else {
			// Envia respuesta
			echo 'error';
		}


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