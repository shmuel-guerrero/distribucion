<?php


// Obtiene el id_empleado
$id_empleado = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado
$empleado = $db->from('sys_empleados')->where('id_empleado', $id_empleado)->fetch_first();

$verificar_movimientos_e = $db->query("select COUNT(*) as cant_registros from inv_egresos WHERE empleado_id = '{$id_empleado}'")->fetch_first();
$verificar_movimientos_e = ($verificar_movimientos_e['cant_registros']) ? $verificar_movimientos_e['cant_registros'] : 0;

$verificar_movimientos_1 = $db->query("select COUNT(*) as cant_registros from inv_ingresos WHERE empleado_id = '{$id_empleado}'")->fetch_first();
$verificar_movimientos_1 = ($verificar_movimientos_1['cant_registros']) ? $verificar_movimientos_1['cant_registros'] : 0;

if (!$verificar_movimientos_e && !$verificar_movimientos_1) {

	// Verifica si el empleado existe
	if ($empleado) {
		// Elimina el empleado
		$db->delete()->from('sys_empleados')->where('id_empleado', $id_empleado)->limit(1)->execute();
		
		//Guarda Historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'd',
			'nivel' => 'l',
			'direccion' => '?/empleados/eliminar',
			'detalle' => 'Se elimino empleado con identificador numero ' . $id_empleado ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ;
	
		// Verifica si fue el empleado eliminado
		if ($db->affected_rows) {
			// Instancia variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Eliminación satisfactoria!',
				'message' => 'El registro fue eliminado correctamente.'
			);
		}
	
		// Redirecciona a la pagina principal
		redirect('?/empleados/listar');
	} else {
		// Error 404
		require_once not_found();
		exit;
	}
	
}else {
	$_SESSION[temporary] = array(
		'alert' => 'danger',
		'title' => 'Eliminación restringida',
		'message' => 'El registro no puede ser eliminado; posee movimientos en la base de dados.'
	);
	// Redirecciona a la pagina principal
	redirect('?/empleados/listar');
}

?>