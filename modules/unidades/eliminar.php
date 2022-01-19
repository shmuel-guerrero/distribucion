<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_unidad
$id_unidad = (sizeof($params) > 0) ? $params[0] : 0;


$veri = $db->select('sum(cantidad) as tot')->from('inv_egresos_detalles')->where('unidad_id',$id_unidad)->fetch_first(); 

//echo $veri['tot']; die();

 if($veri['tot'] <= 0){
	// Obtiene la unidad
	$unidad = $db->from('inv_unidades')->where('id_unidad', $id_unidad)->fetch_first();

	// Verifica si la unidad existe
	if ($unidad) {
		// Elimina la unidad
		$db->delete()->from('inv_unidades')->where('id_unidad', $id_unidad)->limit(1)->execute();
		//Guarda en el historial
		$data = array(
			'fecha_proceso' => date("Y-m-d"),
			'hora_proceso' => date("H:i:s"), 
			'proceso' => 'd',
			'nivel' => 'l',
			'direccion' => '?/unidades/eliminar',
			'detalle' => 'Se elimino la unidad con identificador numero ' . $id_unidad ,
			'usuario_id' => $_SESSION[user]['id_user']			
		);			
		$db->insert('sys_procesos', $data) ; 

		// Verifica si fue la unidad eliminado
		if ($db->affected_rows) {
			// Instancia variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'success',
				'title' => 'Eliminacion satisfactoria!',
				'message' => 'El registro fue eliminado correctamente.'
			);
		}

		// Redirecciona a la pagina principal
		redirect('?/unidades/listar');
	} else {
		// Error 404
		require_once not_found();
		exit;
	}
} else 
{
	$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Eliminacion fallida!',
				'message' => 'La unidad esta siendo utilizada.'
			);
	redirect('?/unidades/listar');
}

?>