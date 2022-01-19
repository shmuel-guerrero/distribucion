<?php

// Obtiene el id_user
$id_user = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el user
$usuario = $db->from('sys_users')->where('id_user', $id_user)->fetch_first();

$verificar_movimientos_e = $db->query("select COUNT(*) as cant_registros from inv_egresos e 
										LEFT JOIN sys_empleados se ON e.empleado_id = se.id_empleado 
										LEFT JOIN sys_users u ON u.persona_id = se.id_empleado WHERE e.empleado_id = '" . $usuario['persona_id'] . "'")->fetch_first();
$verificar_movimientos_e = ($verificar_movimientos_e['cant_registros']) ? $verificar_movimientos_e['cant_registros'] : '';

$verificar_movimientos_1 = $db->query("select COUNT(*) as cant_registros from inv_ingresos i
										LEFT JOIN sys_empleados se ON i.empleado_id = se.id_empleado 
										LEFT JOIN sys_users u ON u.persona_id = se.id_empleado 
										WHERE i.empleado_id = '" . $usuario['persona_id'] . "'")->fetch_first();

$verificar_movimientos_1 = ($verificar_movimientos_1['cant_registros']) ? $verificar_movimientos_1['cant_registros'] : '';

if (!$verificar_movimientos_e && !$verificar_movimientos_1) {

	// Verifica si el user existe
	if ($usuario && $usuario['id_user'] != 1) {
		// Elimina el user
		$db->delete()->from('sys_users')->where('id_user', $id_user)->limit(1)->execute();
		
		// Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'u',
			'nivel' => 'l',
			'direccion' => '?/usuarios/eliminar',
			'detalle' => 'Se elimino usuario con identificador numero ' . $id_user ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ;

		// Verifica si fue el user eliminado
		if ($db->affected_rows) {
			// Define la variable de error
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Eliminacion satisfactoria!',
				'message' => 'El usuario fue eliminado correctamente.'
			);
		}

		// Redirecciona a la pagina principal
		redirect('?/usuarios/listar');
	} else {
		// Error 404
		require_once not_found();
		exit;
	}
}else {
	// Define la variable de error
	$_SESSION[temporary] = array(
		'alert' => 'danger',
		'title' => 'Eliminacion restringida!',
		'message' => 'El registro no puede ser eliminado; posee movimientos en la base de dados.'
	);
	// Redirecciona a la pagina principal
	redirect('?/usuarios/listar');
}

?>