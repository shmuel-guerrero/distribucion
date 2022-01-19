<?php

/** 
 * SOLO TRABAJA CON LOS DATOS TEMPORALES CAMBIANDOLOS DE ESTADO Y ACTUALIZANDO LA FECHA DEL DISTRIBUIDOR ESTO CON EL FIN DE GENARAR LA LIQUIDACION
 * 
*/
// Obtiene el id_user
$id_user = (sizeof($params) > 0) ? $params[0] : 0;


// Obtiene el user
$user = $db->from('sys_empleados')->where('id_empleado', $id_user)->fetch_first();
$id_user = $user['id_empleado'];

// Verifica si el user existe
if ($user) {
	$egresos = $db->query('SELECT  b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, estadoe as estado
    FROM gps_asigna_distribucion a
    LEFT JOIN inv_egresos b ON a.ruta_id = b.ruta_id
    WHERE a.distribuidor_id = '.$id_user.' AND b.grupo = "" AND a.estado = 1 AND b.estadoe = 2 AND b.fecha_egreso < CURDATE()')->fetch();

	//obtiene los ids de preventistas y distribuidor
	$datos_historial = $db->query("SELECT e.ruta_id, e.empleado_id, ga.distribuidor_id 
								FROM tmp_egresos e  LEFT JOIN gps_asigna_distribucion ga ON e.ruta_id = ga.ruta_id
								WHERE e.distribuidor_id = '{$id_user}' AND (e.estado = 3 OR e.estado = 2) AND ga.estado = 1
								GROUP BY e.ruta_id, e.empleado_id, e.distribuidor_id")->fetch();

	//valida que tenga elementos								
	if (count($datos_historial) > 0) {
		
		//itera lo obtenido
		foreach ($datos_historial as $key => $value) {
				
				//Datos  para historial
				$datos = array('ruta_id' => $value['ruta_id'],
				'vendedor_id' => $value['empleado_id'],
				'distribuidor_id' => $id_user,
				'fecha_registro' => date('Y-m-d'),
				'hora_registro' => date('H:i:s')					
			);
			
			//inserta registro de historial
			$db->insert('gps_historial_distribuidores_vendedores', $datos);			
		}
	}
	
    $db->where(array('estado' => 2, 'distribuidor_id' => $id_user))->update('tmp_egresos',array('estado' => 1))->execute();
    // Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/distribuidor/activar2',
		'detalle' => 'Se actualizo estado 2 ditribuidor con identificador número ' . $id_user ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;
    
    $db->where(array('estado' => 3, 'distribuidor_id' => $id_user))->update('tmp_egresos',array('estado' => 2))->execute();
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
    
	//$db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $monto_total));
	
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
		'direccion' => '?/distribuidor/activar2',
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