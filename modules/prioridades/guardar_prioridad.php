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
	if (isset($_POST['tipo']) ) {

        // Importa la libreria para subir la imagen
        require_once libraries . '/upload-class/class.upload.php';
       
        // Obtiene los datos del cliente
        $tipo = trim($_POST['tipo']);
        
        $id = $db->insert('inv_prioridades_ventas', array('prioridad' => $tipo));
        
        // Guarda en el historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'c',
			'nivel' => 'l',
			'direccion' => '?/prioridades/guardar_prioridad',
			'detalle' => 'Se inserto la prioridad con identificador numero ' . $id ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ; 
		

		// Redirecciona a la pagina principal
		redirect('?/prioridades/crear_prioridad');

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