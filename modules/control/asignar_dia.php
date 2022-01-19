<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
var_dump($_POST);
// Verifica la peticion post
if (is_post()) {
	// Verifica la cadena csrf
	if (isset($_POST['unidad_id'])) {
		// Obtiene los parametros
		$ruta_id = (isset($params[0])) ? $params[0] : 0;

		// Obtiene el producto
		$ruta = $db->select('id_ruta, dia')->from('gps_rutas')->where('id_ruta', $ruta_id)->fetch_first();

		// Verifica si existen los productos
		if ($ruta) {
			//cambiamos la fecha
            $dia = $_POST['unidad_id'];
            $asigna = array(
                'dia' => $dia
            );
            $db->where('id_ruta',$ruta_id)->update('gps_rutas', $asigna);
            // Guarda Historial
			$data = array(
				'fecha_proceso' => date("Y-m-d"),
				'hora_proceso' => date("H:i:s"), 
				'proceso' => 'u',
				'nivel' => 'l',
				'direccion' => '?/control/asignar_dia',
				'detalle' => 'Se actualizo ruta con identificador número ' . $ruta_id ,
				'usuario_id' => $_SESSION[user]['id_user']			
			);			
			$db->insert('sys_procesos', $data) ;

            
            set_notification('success', 'Asignación exitosa!', 'El día se fijó satisfactoriamente.');
            // Redirecciona la pagina
            redirect('?/control/listar');

		} else {
			// Error 400
			require_once bad_request();
			exit;
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