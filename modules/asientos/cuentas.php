<?php

/**
 * FunctionPHP - Framework Functional PHP
 * 
 * @package  FunctionPHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Verifica si es una peticion ajax y post
if (true) {
	// Verifica la existencia de parametros
	if (true) {
		// Verifica la existencia de datos
		if (true) {

			// Obtiene los productos con el valor buscado
				$productos = $db->select('*')->from('con_plan')->fetch();
//var_dump($productos);
			// Devuelve los resultados
			echo json_encode($productos);
		} else {
			// Error 401
			require_once bad_request();
			exit;
		}		
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