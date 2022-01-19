<?php
/* ::BECA
 *	recibe empleado_id y devulve las rutas de ese empleado 
 */

// Verifica si es una peticion ajax y post
if (is_ajax() && is_post()) {
	// Verifica la existencia del empleado_id
	if (isset($_POST['empleado_id'])) {
		
		// Obtiene los datos de las rutas
		$empleado_id = trim($_POST['empleado_id']);        
        $rutas = $db->query("SELECT id_ruta, nombre FROM gps_rutas WHERE empleado_id = $empleado_id GROUP BY id_ruta")->fetch();        
        
		// Envia respuesta
		echo json_encode($rutas);
	} else {
		// Envia respuesta
		echo 'error, no llegó el empleado_id';
	}
} else {
	// Error 404
	require_once not_found();
	exit;
}