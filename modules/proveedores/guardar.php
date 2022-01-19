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
	if (isset($_POST['nombres']) && isset($_POST['ci']) ) {
		// Obtiene los datos del empleado
		$nombres = trim($_POST['nombres']);
		$ci = trim($_POST['ci']);
		$direccion= trim($_POST['direccion']);
		$telefono = trim($_POST['telefono']);

        if($_POST['id_cliente']!=0){
            $id = $_POST['id_cliente'];
            $cliente = array(
                'proveedor' => $nombres,
                'nit' => $ci,
                'direccion' => $direccion,
                'telefono' => $telefono,
            );
            $db->where('id_proveedor',$id)->update('inv_proveedores', $cliente);
            // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/proveedores/guardar',
				'detalle' => 'Se actualizo proveedor con identificador numero ' . $id,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;
        }else{
            $bus = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombres, 'nit' => $ci))->fetch_first();

            if($bus){
                $_SESSION[temporary] = array(
                    'alert' => 'danger',
                    'title' => 'El cliente ya existe!',
                    'message' => 'El registro no se realizó correctamente.'
                );
            }else{
                $cliente = array(
                    'proveedor' => $nombres,
                    'nit' => $ci,
                    'direccion' => $direccion,
                    'telefono' => $telefono,
                );
                $id = $db->insert('inv_proveedores', $cliente);
                
                // Guarda en el historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"), 
                    'proceso' => 'c',
                    'nivel' => 'l',
                    'direccion' => '?/proveedores/guardar',
                    'detalle' => 'Se inserto proveedor con identificador numero ' . $id ,
                    'usuario_id' => $_SESSION[user]['id_user']			
                );			
                $db->insert('sys_procesos', $data) ; 
                
                $_SESSION[temporary] = array(
                    'alert' => 'success',
                    'title' => 'Se creo el nuevo cliente!',
                    'message' => 'El registro se realizó correctamente.'
                );
            }
        }
		// Redirecciona a la pagina principal
		redirect('?/proveedores/listar');
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