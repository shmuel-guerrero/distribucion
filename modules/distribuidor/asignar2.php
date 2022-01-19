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
	if (isset($_POST['distribuidor_id'])) {
		// Obtiene los parametros

        if(isset($_POST['grupo_asignar']) && $_POST['grupo_asignar']!=''){
            $grupo_id = $_POST['grupo_asignar'];
        }
        $grupo_id = str_replace('--',' ',$grupo_id);
        $distribuidor = $_POST['distribuidor_id'];
		// Obtiene el producto
		$asigna = $db->select('*')->from('gps_asigna_distribucion')->where(array('grupo_id' => $grupo_id, 'distribuidor_id' => $distribuidor))->fetch_first();

		// Verifica si existen los productos
		if ($asigna) {
			// Verifica la existencia de datos
			if ($asigna['estado']==0) {
                //si hay una asignacion
                $ant = $db->select('*')->from('gps_asigna_distribucion')->where(array('grupo_id' => $grupo_id, 'estado' => 1))->fetch_first();
                if($ant){
                    //actualizando antigua asignacion
                    $db->where('id_asignacion',$ant['id_asignacion'])->update('gps_asigna_distribucion', array('fecha_fin'=>date('Y-m-d'),'estado'=>0));
                    // Guarda Historial
        			$data = array(
        				'fecha_proceso' => date("Y-m-d"),
        				'hora_proceso' => date("H:i:s"), 
        				'proceso' => 'u',
        				'nivel' => 'l',
        				'direccion' => '?/distribuidor/asignar2',
        				'detalle' => 'Se actualizo distribucion asigna con identificador número ' . $grupo_id ,
        				'usuario_id' => $_SESSION[user]['id_user']			
        			);			
        			$db->insert('sys_procesos', $data) ;
                }
                // nueva asignacion
                $datos = array(
                    'distribuidor_id' => $distribuidor,
                    'grupo_id' => $grupo_id,
                    'fecha_ini' => date('Y-m-d'),
                    'estado' => 1
                );
                $id = $db->insert('gps_asigna_distribucion', $datos);
                // Guarda Historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'c',
    				'nivel' => 'l',
    				'direccion' => '?distribuidor/asignar2',
    				'detalle' => 'Se creó asignacion distribucion con identificador número ' . $id ,
    				'usuario_id' => $_SESSION[user]['id_user']			
    			);
    			
    			$db->insert('sys_procesos', $data) ; 

                // Crea el precio
                $estado_precio = true;
                $estado_asignacion = true;
            }else {
                // Crea el precio
                $estado_precio = true;
                $estado_asignacion = true;
            }
				// Verifica los estados
				if ($estado_asignacion && $estado_precio) {
					// Crea la notificacion
					set_notification('success', 'Asignación exitosa!', 'La ruta se fijó satisfactoriamente.');
                    // Redirecciona la pagina
                    redirect('?/distribuidor/listar');
				} else {
					if ($estado_asignacion) {
						// Crea la notificacion
						set_notification('success', 'Asignación exitosa!', 'La ruta se asignó satisfactoriamente.');
                        // Redirecciona la pagina
                        redirect('?/distribuidor/listar');
					} else {
						if ($estado_precio) {
							// Crea la notificacion
							set_notification('success', 'Asignación exitosa!', 'La ruta se fijó satisfactoriamente.');
                            // Redirecciona la pagina
                            redirect('?/distribuidor/listar');
						} else {
							// Crea la notificacion
							set_notification('danger', 'Asignación fallida!', 'Los cambios no fueron registrados.');
                            // Redirecciona la pagina
                            redirect('?/distribuidor/listar');
						}
					}
				}



		} else {
            $ant = $db->select('*')->from('gps_asigna_distribucion')->where(array('grupo_id' => $grupo_id, 'estado' => 1))->fetch_first();
            if($ant){
                //actualizando antigua asignacion
                $db->where('id_asignacion',$ant['id_asignacion'])->update('gps_asigna_distribucion', array('fecha_fin'=>date('Y-m-d'),'estado'=>0));
                
                // Guarda Historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'u',
    				'nivel' => 'l',
    				'direccion' => '?/distribuidor/asignar2',
    				'detalle' => 'Se actualizo distribucion asigna con identificador número ' . $grupo_id ,
    				'usuario_id' => $_SESSION[user]['id_user']			
    			);			
    			$db->insert('sys_procesos', $data) ;
                
            }
            // nueva asignacion
            $datos = array(
                'distribuidor_id' => $distribuidor,
                'grupo_id' => $grupo_id,
                'fecha_ini' => date('Y-m-d'),
                'estado' => 1
            );
            $id = $db->insert('gps_asigna_distribucion', $datos);
            
            // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'c',
				'nivel' => 'l',
				'direccion' => '?distribuidor/asignar2',
				'detalle' => 'Se creó asignacion distribucion con identificador número ' . $id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);
			$db->insert('sys_procesos', $data) ;
			
            // Crea la notificacion
            set_notification('success', 'Asignación exitosa!', 'La ruta se fijó satisfactoriamente.');
            // Redirecciona la pagina
            redirect('?/distribuidor/listar');
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