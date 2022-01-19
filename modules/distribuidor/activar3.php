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

	// Obtiene el nuevo estado
    $fecha_actual = date("Y-m-d");
    $nuevo = date("Y-m-d",strtotime($fecha_actual."- 1 days"));
	$estado = ($user['fecha'] == date('Y-m-d')) ? $nuevo : date('Y-m-d');

	// Instancia el user
	$user = array(
		'fecha' => $estado,	
        'hora' => date('H:i:s')
	);
	// Genera la condicion
	$condicion = array('id_empleado' => $id_user);

	// Actualiza la informacion
	$db->where($condicion)->update('sys_empleados', $user);
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar',
		'detalle' => 'Se actualizo empleado con identificador número ' . $id_user ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;

	// Redirecciona a la pagina principal
	redirect('?/distribuidor/listar2');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>