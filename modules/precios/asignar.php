<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// Verifica la peticion post
if (is_post()) {
	// Verifica la cadena csrf
	if (true) {
		// Obtiene los parametros
		$producto_id = (isset($params[0])) ? $params[0] : 0;

		// Obtiene el producto
		$producto = $db->select('id_producto')->from('inv_productos')->where('id_producto', $producto_id)->fetch_first();

		// Verifica si existen los productos
		if ($producto) {
			// Verifica la existencia de datos
			if (isset($_POST['unidad_id']) && isset($_POST['precio']) && isset($_POST['observacion'])) {
                // Obtiene los datos
                $unidad_id = clear($_POST['unidad_id']);
                $precio = clear($_POST['precio']);
                $tamano = clear($_POST['tamano']);
                $observacion = clear($_POST['observacion']);
                $precio = (is_numeric($precio)) ? $precio : 0;
                $estado_asignacion = false;
                $estado_precio = false;

                //busqueda de exitenncia
                $ex = $db->select('*')->from('inv_asignaciones')->where(array('producto_id' => $producto_id, 'unidad_id' => $unidad_id, 'visible' => 's'))->fetch_first();
                if($ex){
                    $asigna = array(
                        'cantidad_unidad' => $tamano,
                        'otro_precio' => $precio
                    );

                    // Cambia la asignacion
                    $db->where(array('producto_id' => $producto_id, 'unidad_id' => $unidad_id, 'visible' => 's'))->update('inv_asignaciones', $asigna);
                    // Guarda Historial
        			$data = array(
        				'fecha_proceso' => date("Y-m-d"),
        				'hora_proceso' => date("H:i:s"), 
        				'proceso' => 'u',
        				'nivel' => 'l',
        				'direccion' => '?/productos/asignar',
        				'detalle' => 'Se actualizo asignacion de producto con identificador número ' . $producto_id ,
        				'usuario_id' => $_SESSION[user]['id_user']			
        			);
					$db->insert('sys_procesos', $data) ;

					$id_asignacion=$db->query("SELECT id_asignacion FROM inv_asignaciones WHERE  visible = 's' AND  producto_id='{$producto_id}' AND unidad_id='{$unidad_id}'")->fetch_first();
					$id_asignacion = ($id_asignacion['id_asignacion']) ? $id_asignacion['id_asignacion'] : 0;

                    $precio = array(
                        'precio' => $precio,
						'fecha_registro' => date('Y-m-d'),
						'hora_registro' => date('H:i:s'),
						'empleado_id' => $_user['id_user'],
						'producto_id' => $producto_id,
						'asignacion_id' => $id_asignacion,
						//'unidad_id' => $unidad_id,
                    );

                    // Cambia el precio
					//$db->where(array('asignacion_id' => $ex['id_asignacion']))->update('inv_precios', $precio);
					$id_precio = $db->insert('inv_precios', $precio);
                     // Guarda Historial
        			$data = array(
        				'fecha_proceso' => date("Y-m-d"),
        				'hora_proceso' => date("H:i:s"), 
        				'proceso' => 'u',
        				'nivel' => 'l',
        				'direccion' => '?/productos/asignar',
        				'detalle' => 'Se actualizo precio de asignacion con identificador número ' . $ex['id_asignacion'] ,
        				'usuario_id' => $_SESSION[user]['id_user']			
        			);			
        			$db->insert('sys_procesos', $data) ;
                    $estado_precio = true;
                    $estado_asignacion = true;

                }else{
                    $asigna = array(
                        'producto_id' => $producto_id,
                        'unidad_id' => $unidad_id,
                        'cantidad_unidad' => $tamano,
                        'otro_precio' => $precio
                    );
                    // Obtiene la asignacion
                    $id_asignacion = $db->insert('inv_asignaciones', $asigna);
                    // Guarda Historial
        			$data = array(
        				'fecha_proceso' => date("Y-m-d"),
        				'hora_proceso' => date("H:i:s"), 
        				'proceso' => 'c',
        				'nivel' => 'l',
        				'direccion' => '?/productos/asignar',
        				'detalle' => 'Se creó asignacion con identificador número ' . $id_asignacion ,
        				'usuario_id' => $_SESSION[user]['id_user']			
        			);
        			$db->insert('sys_procesos', $data) ; 

                    $precio = array(
                        'precio' => $precio,
                        'fecha_registro' => date('Y-m-d'),
                        'hora_registro' => date('H:i:s'),
                        'asignacion_id' => $id_asignacion,
                        'producto_id' => $producto_id,
						'empleado_id' => $_user['id_user'],
						//'unidad_id' => $unidad_id,
                    );

                    // Crea el precio
                    $id_precio = $db->insert('inv_precios', $precio);
                    // Guarda Historial
        			$data = array(
        				'fecha_proceso' => date("Y-m-d"),
        				'hora_proceso' => date("H:i:s"), 
        				'proceso' => 'c',
        				'nivel' => 'l',
        				'direccion' => '?/productos/asignar',
        				'detalle' => 'Se creó precio con identificador número ' . $id_precio,
        				'usuario_id' => $_SESSION[user]['id_user']			
        			);
        			$db->insert('sys_procesos', $data) ; 
                    $estado_precio = true;
                    $estado_asignacion = true;
                }

				// Verifica los estados
				if ($estado_asignacion && $estado_precio) {
					// Crea la notificacion
					set_notification('success', 'Asignación exitosa!', 'La unidad se asignó y el precio se fijó satisfactoriamente.');
                    // Redirecciona la pagina
                    redirect('?/productos/listar');
				} else {
					if ($estado_asignacion) {
						// Crea la notificacion
						set_notification('success', 'Asignación exitosa!', 'La unidad se asignó satisfactoriamente.');
                        // Redirecciona la pagina
                        redirect('?/productos/listar');
					} else {
						if ($estado_precio) {
							// Crea la notificacion
							set_notification('success', 'Asignación exitosa!', 'El precio se fijó satisfactoriamente.');
                            // Redirecciona la pagina
                            redirect('?/productos/listar');
						} else {
							// Crea la notificacion
							set_notification('danger', 'Asignación fallida!', 'Los cambios no fueron registrados.');
                            // Redirecciona la pagina
                            redirect('?/productos/listar');
						}
					}
				}


			} else {
				// Error 400
				require_once bad_request();
				exit;
			}
		} else {
			// Error 400
			require_once bad_request();
			exit;
		}
	} else {
		// Redirecciona la pagina
		redirect(back());
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>
<select name="" id="op">
    <option value=""></option></select>