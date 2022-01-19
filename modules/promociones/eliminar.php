<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_producto
$id_producto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();

// Verifica si el producto existe
if ($producto) {
	// Elimina el producto
	$verifica = $db->select('*')->from('inv_egresos_detalles')->where('producto_id',$id_producto)->fetch_first();
	if($verifica['id_detalle']){
    	// Verifica si fue el producto eliminado
    	if ($db->affected_rows) {
    		// Instancia variable de notificacion
    		$_SESSION[temporary] = array(
    			'alert' => 'danger',
    			'title' => 'Problemas con la eliminación!',
    			'message' => 'No se pudo eliminar por que ya se registraron ventas con esta promoción.'
    		);
    	}
	}else{
	    $db->delete()->from('inv_productos')->where('id_producto', $id_producto)->limit(1)->execute();

    	// Verifica si fue el producto eliminado
    	if ($db->affected_rows) {
    		// Instancia variable de notificacion
    		$_SESSION[temporary] = array(
    			'alert' => 'success',
    			'title' => 'Eliminación satisfactoria!',
    			'message' => 'El registro fue eliminado correctamente.'
    		);
    	}
	}
	

	// Redirecciona a la pagina principal
	redirect('?/promociones/promocion_x_item');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>