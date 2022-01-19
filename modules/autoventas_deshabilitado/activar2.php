<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// Obtiene el id_user
$id_user = (sizeof($params) > 0) ? $params[0] : 0;


// Obtiene el user
$user = $db->from('sys_empleados')->where('id_empleado', $id_user)->fetch_first();
$id_user = $user['id_empleado'];

// Verifica si el user existe
if ($user) {
	
    $db->where(array('estado' => 'salida', 'empleado_id' => $id_user))->update('inv_ordenes_salidas',array('estado' => 'entregado'))->execute();
     // Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar2',
		'detalle' => 'Se actualizo estado 3  ditribuidor con identificador número ' . $id_user ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;
    

//  $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $monto_total));
	
	
	
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar2',
		'detalle' => 'Se actualizo empleado con identificador número ' . $id_user ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;
	

	// Redirecciona a la pagina principal
	redirect('?/autoventas/listar_distribucion');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>