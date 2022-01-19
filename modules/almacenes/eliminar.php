<?php

// Obtiene el id_almacen
$id_almacen = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el almacén
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

// Verifica si el almacén existe
if ($almacen) {
    
    $existe = $db->query("SELECT id_egreso
							from inv_egresos
                            where almacen_id = ".$almacen['id_almacen']."
                            LIMIT 1")->fetch();
    if(count($existe) > 0){
       // Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'danger',
			'title' => 'No se puede eliminar!',
			'message' => 'No se puede eliminar el almacen porque tiene egresos registrados.'
		);
    	// Redirecciona a la pagina principal
    	redirect('?/almacenes/listar'); 
    }
        
    
	// Elimina el almacén
	$db->delete()->from('inv_almacenes')->where('id_almacen', $id_almacen)->limit(1)->execute();
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/almacenes/eliminar',
		'detalle' => 'Se elimino almacén con identificador número' . $id_almacen ,
		'usuario_id' => $_SESSION[user]['id_user']			
	);			
	$db->insert('sys_procesos', $data) ;

	// Verifica si fue el almacén eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El registro fue eliminado correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/almacenes/listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>