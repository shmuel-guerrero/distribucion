<?php

/**
 * SimplePHP - Simple Framework PHP
 *
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
//echo 'hola'; exit();

// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
    if(isset($_POST['id'])){
        $ruta_id = $_POST['id'];
        if (isset($_POST['wayt']) && $_POST['wayt'] != ''){
            $coordenadas = trim($_POST['wayt']);
            $nombre = trim($_POST['nombre']);
            $ruta = array(
                'nombre' => $nombre,
                'coordenadas' => $coordenadas
            );
            $db->where('id_ruta',$ruta_id)->update('gps_rutas',$ruta);
        } else{
            $nombre = trim($_POST['nombre']);
            $ruta = array(
                'nombre' => $nombre
            );
            $db->where('id_ruta',$ruta_id)->update('gps_rutas',$ruta);
            
            // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/control/guardar',
				'detalle' => 'Se actualizo ruta con identificador numero ' . $ruta_id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ; 
        }
        echo json_encode($ruta);
    }else{
        if (isset($_POST['wayt'])){

            $empresa = (isset($params[0])) ? $params[0] : 1;
            // Obtiene los datos de la venta
            $coordenadas = trim($_POST['wayt']);

            if(isset($_POST['id_ruta'])){
                $ruta_id = trim($_POST['id_ruta']);
                $ruta = array(
                    'coordenadas' => $coordenadas,
                );
                $db->where('id_ruta',$ruta_id)->update('gps_rutas',$ruta);
                
                // Guarda Historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'u',
    				'nivel' => 'l',
    				'direccion' => '?/control/guardar',
    				'detalle' => 'Se actualizo ruta con identificador numero ' . $ruta_id ,
    				'usuario_id' => $_SESSION[user]['id_user']			
    			);			
    			$db->insert('sys_procesos', $data) ;
                
            }else{
                $nombre = trim($_POST['nombre']);

                // Instancia la venta
                $ruta = array(
                    'nombre' => $nombre,
                    'coordenadas' => $coordenadas,
                    'fecha' => date('Y-m-d'),
                    'estado' => $empresa,
                    'dia' => 7,
                    'empleado_id'=>1
                );

                // Guarda la informacion
                $ruta_id = $db->insert('gps_rutas', $ruta);
                
                // Guarda Historial
    			$data = array(
    				'fecha_proceso' => date("Y-m-d"),
    				'hora_proceso' => date("H:i:s"), 
    				'proceso' => 'c',
    				'nivel' => 'l',
    				'direccion' => '?/control/guardar',
    				'detalle' => 'Se creó ruta con identificador numero ' . $id ,
    				'usuario_id' => $_SESSION[user]['id_user']			
    			);
    			$db->insert('sys_procesos', $data) ; 
                
            }
            echo json_encode($ruta);
        } else {
            // Error 401
            require_once bad_request();
            exit;
        }
    }

} else {
	// Error 404
	require_once not_found();
	exit;
}

?>