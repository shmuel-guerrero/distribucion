<?php


// Obtiene el id_proforma
$id_proforma = (isset($params[0])) ? $params[0] : 0;

// Obtiene el proforma
$venta = $db->from('inv_egresos')->where('id_egreso', $id_proforma)->where('tipo', 'No venta')->fetch_first();

// Verifica si el proforma existe
if ($venta) {

	/**
	 * SEVALIDA LA EXISTENCIA DE CREDITO EN LA BASE DE DATOS Y SE ELIMINA 
	 */

	//Cuentas manejo de HGC
	$id_cli = (int)$venta['cliente_id'];
	$clienteCred = $db->from('inv_clientes')->where('id_cliente', $id_cli)->fetch_first();

	$credito = $clienteCred['credito'];

	if ($credito == '1' || $credito = 1) {
		
		//se obtien el credito
		$creditoEx = $db->from('inv_pagos')->where('tipo', 'Egreso')->where('movimiento_id', $id_proforma)->fetch_first(); 

		//se valida la existencia de credito
		if ($creditoEx){
			//se crea backup de registros a eliminar
			$verifica_id = backup_registros($db, 'inv_pagos', 'movimiento_id', $id_proforma, 'tipo', 'Egreso', $_user['persona_id'], 'SI', 0, "Eliminado");
			//se eliminan registro
			$db->delete()->from('inv_pagos')->where('movimiento_id', $id_proforma)->where( 'tipo', 'Egreso')->execute();
			//se crea backup de registros a eliminar
			$verifica = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $creditoEx['id_pago'], '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");
			//se eliminan registro
			$db->delete()->from('inv_pagos_detalles')->where('pago_id', $creditoEx['id_pago'])->execute();
		}			
	}

	//se guarda una copia de la factura original
	$verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $id_proforma, '', '', $_user['persona_id'], 'SI', 0, "Eliminado");

	// Elimina el proforma
	$db->delete()->from('inv_egresos')->where('id_egreso', $id_proforma)->where('tipo', 'No venta')->limit(1)->execute();
	
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/operaciones/notas_listar',
		'detalle' => 'Se elimino inventario egreso con identificador numero' . $id_proforma ,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data) ;


	// Verifica si fue el proforma eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'El movimiento y todo su detalle fueron eliminados correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/operaciones/preventas_noventas_listar');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>