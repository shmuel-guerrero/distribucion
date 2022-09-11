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
	if (isset($_POST['id_unidad']) && isset($_POST['unidad']) && isset($_POST['sigla']) && isset($_POST['descripcion'])) {
		// Obtiene las datos de la unidad
		$id_unidad = trim($_POST['id_unidad']);
		$unidad = trim($_POST['unidad']);
		$sigla = trim($_POST['sigla']);
		$descripcion = trim($_POST['descripcion']);
		$categoria_unidad = (isset($_POST['categoria_unidad']) && $_POST['categoria_unidad'] == 'S') ? $_POST['categoria_unidad']: 'No';		
		
		// Instancia la unidad
		$datos = array(
			'unidad' => $unidad,
			'sigla' => $sigla,
			'descripcion' => $descripcion
		);
		
		// Verifica si es creacion o modificacion
		if ($id_unidad > 0) {
			// Genera la condicion
			$condicion = array('id_unidad' => $id_unidad);
			
			// Actualiza la informacion
			$db->where($condicion)->update('inv_unidades', $datos);
			// Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/unidades/guardar',
				'detalle' => 'Se actualizo la unidad con identificador numero ' . $id_unidad,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;
			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Actualización satisfactoria!',
				'message' => 'El registro se actualizó correctamente.'
			);
		} else {
			if (validar_atributo($db, $_plansistema['plan'], 'productos', 'crear', 'categoria_cliente')){
				if ($categoria_unidad == 'S') {
					//mi libreria de validadicon de token
					require_once(__DIR__.'/funciones/api-unidades.php');
					$acciones_productos = new unidades();
					$acciones_productos->registroVariasUnidades($unidad);
				}else {
					// Guarda la informacion
					$id = $db->insert('inv_unidades', $datos);
				}
			}else {
				// Guarda la informacion
				$id = $db->insert('inv_unidades', $datos);
			}


			// Guarda en el historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?/unidades/guardar',
				'detalle' => 'Se inserto la unidad con identificador numero ' . $id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;

			
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Adición satisfactoria!',
				'message' => 'El registro se guardó correctamente.'
			);
		}
		
		// Redirecciona a la pagina principal
		redirect('?/unidades/listar');
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

?>