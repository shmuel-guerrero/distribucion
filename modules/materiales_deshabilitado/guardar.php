<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	if (isset($_POST['id_materiales']) && isset($_POST['nombre']) && isset($_POST['id_producto']) && isset($_POST['precio']) && isset($_POST['id_unidad'])) {
		// Obtiene los datos del producto
		$id_materiales = trim($_POST['id_materiales']);
		$nombre = trim($_POST['nombre']);
		// $fecha_ven = trim($_POST['ven_fecha']);
		$precio = ($_POST['precio']) ? trim($_POST['precio']) : '0.00';
		$unidad_id = trim($_POST['id_unidad']);
		$producto_id = trim($_POST['id_producto']);


		// Instancia el producto
		$materiales = array(
			'nombre' => $nombre,
			'id_unidad' => $unidad_id,
			'precio' => $precio,
			'id_producto' => $producto_id,
			'id_empleado' => $_SESSION[user]['id_user'],
			'fecha_material' => date("Y-m-d")
		);

		// Verifica si es creacion o modificacion
		if ($id_materiales > 0) {
			// Genera la condicion
			$condicion = array('id_materiales' => $id_materiales);

			// Actualiza la informacion
			$db->where($condicion)->update('inv_materiales', $materiales);
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/materiales/guardar',
				'detalle' => 'Se actualizó inventario de producto con identificador número ' . $id_materiales,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			// adiciona la fecha de creacion
			$materiales['fecha_material'] = date('Y-m-d');


			// Guarda la informacion
			$id_materiales = $db->insert('inv_materiales', $materiales);

			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"),
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/materiales/guardar',
				'detalle' => 'Se inserto el producto con identificador numero ' . $id_materiales,
				'usuario_id' => $_SESSION[user]['id_user']
			);
			$db->insert('sys_procesos', $data);

			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}

		// Redirecciona a la pagina principal
		redirect('?/materiales/listar');
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
