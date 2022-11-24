<?php


// Verifica si es una peticion ajax
if (is_ajax()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_producto']) && isset($_POST['precio']) && isset($_POST['id_asignacion'])) {
		
		// Obtiene los datos del producto
		$id_producto = trim($_POST['id_producto']);
		$id_asignacion = trim($_POST['id_asignacion']);
		$precio = trim($_POST['precio']);

		// Instancia el producto
		$producto = array(
			'precio' => $precio,
			'fecha_registro' => date('Y-m-d'),
			'hora_registro' => date('H:i:s'),
			'producto_id' => $id_producto,
			'empleado_id' => $_user['persona_id']
		);

		// Verifica la existencia
        if ($id_producto!= '' && $id_asignacion== '0' && $precio!= '') {
			
			// Guarda la informacion
			$db->insert('inv_precios', $producto);
	
			// Actualiza la informacion
			$db->where('id_producto', $id_producto)->update('inv_productos', array('precio_actual' => $precio));
			

		}

		if ($id_asignacion > 0) {
			$id_asignacion = $db->query("SELECT * FROM inv_asignaciones a WHERE a.id_asignacion = {$id_asignacion} and a.visible = 's'")->fetch_first();
			
			if ($id_asignacion['id_asignacion']) {
				// Instancia el producto
				$producto['asignacion_id'] = $id_asignacion['id_asignacion'];					

				// Guarda la informacion
				$db->insert('inv_precios', $producto);

				$datos = array(
					'id_asignacion' => $id_asignacion['id_asignacion'], 
					'producto_id' => $id_producto,
					'visible' => 's'
				);

				$db->where($datos)->update('inv_asignaciones', array('otro_precio' => $precio));
				$estado = true;
			}
		}

		// Envia respuesta
		echo json_encode($producto);

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