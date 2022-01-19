<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */

// Obtiene el id_proforma
$id_proforma = (isset($params[0])) ? $params[0] : 0;

// Obtiene el proforma
$venta = $db->from('inv_egresos')
			   ->where('id_egreso', $id_proforma)
			   ->fetch_first();

// Verifica si el proforma existe
if ($venta) {
	// Elimina el proforma
	$db->delete()->from('inv_egresos')->where('id_egreso', $id_proforma)->limit(1)->execute();
	
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/operaciones/manuales_eliminar',
		'detalle' => 'Se elimino inventario egreso con identificador numero' . $id_proforma ,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data) ;
	// Devolvemos los productos al lote correspondiente
	/////////////////////////////////////////////////////////////////////
	$Lotes=$db->query("SELECT producto_id,lote,unidad_id
						FROM inv_egresos_detalles AS ed
						LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
						WHERE egreso_id='{$id_proforma}'")->fetch();
	foreach($Lotes as $Fila=>$Lote):
		$IdProducto=$Lote['producto_id'];
		$UnidadId=$Lote['unidad_id'];
		$LoteGeneral=explode(',',$Lote['lote']);
		for($i=0;$i<count($LoteGeneral);++$i):
			$SubLote=explode('-',$LoteGeneral[$i]);
			$Lot=$SubLote[0];
			$Cantidad=$SubLote[1];
			$DetalleIngreso=$db->query("SELECT id_detalle,lote_cantidad
										FROM inv_ingresos_detalles
										WHERE producto_id='{$IdProducto}' AND lote='{$Lot}'
										LIMIT 1")->fetch_first();
			$Condicion=array(
				'id_detalle'=>$DetalleIngreso['id_detalle'],
				'lote'=>$Lot,
			);
			$CantidadAux=$Cantidad;
			$Datos=array(
				'lote_cantidad'=>(strval($DetalleIngreso['lote_cantidad'])+strval($CantidadAux)),
			);
			$db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
		endfor;
	endforeach;
	/////////////////////////////////////////////////////////////////////
	// Elimina los detalles
	// Elimina los detalles
	$db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_proforma)->execute();
	
	// Guarda Historial
	$data = array(
		'fecha_proceso' => date("Y-m-d"),
		'hora_proceso' => date("H:i:s"), 
		'proceso' => 'u',
		'nivel' => 'l',
		'direccion' => '?/operaciones/manuales_eliminar',
		'detalle' => 'Se elimino inventario egreso detalle con identificador numero' . $id_proforma ,
		'usuario_id' => $_SESSION[user]['id_user']
	);
	$db->insert('sys_procesos', $data) ;

	// Verifica si fue el proforma eliminado
	if ($db->affected_rows) {
		// Instancia variable de notificacion
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Eliminación satisfactoria!',
			'message' => 'La venta manual y todo su detalle fueron eliminados correctamente.'
		);
	}

	// Redirecciona a la pagina principal
	redirect('?/operaciones/listar_manuales');
} else {
	// Error 404
	require_once not_found();
	exit;
}

?>