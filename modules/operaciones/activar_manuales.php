<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author  FABIO CHOQUE
 */

// Obtiene el id_producto
$id_factura = (sizeof($params) > 0) ? $params[0] : 0;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {

	//Se abre nueva transacción.
	$db->autocommit(false);
	$db->beginTransaction();

	// Obtiene el producto
	$venta = $db->from('inv_egresos')->where('id_egreso', $id_factura)->fetch_first();

	// Verifica si el producto existe
	if ($venta) {
		// Obtiene el nuevo estado
		$estado = ($venta['anulado'] == 0) ? 1 : 0;

		// Instancia el producto
		$dato = array(
			'anulado' => $estado
		);

		// Genera la condicion
		$condicion = array('id_egreso' => $id_factura);

		// Actualiza la informacion
		$db->where($condicion)->update('inv_egresos', $dato);

		// echo json_encode($Lotes); die();
		$_SESSION[temporary] = array(
			'alert' => 'success',
			'title' => 'Se anulo correctamente!',
			'message' => 'La operacion se realizó correctamente.'
		);

		//se cierra transaccion
		$db->commit();

		// Redirecciona a la pagina principal
		redirect('?/operaciones/manuales_listar');
	} else {

		//se cierra transaccion
		$db->commit();

		// Error 404
		require_once not_found();
		exit;
	}
	
} catch (Exception $e) {
	$status = false;
	$error = $e->getMessage();

	// Instancia la variable de notificacion
	$_SESSION[temporary] = array(
		'alert' => 'danger',
		'title' => 'Problemas en el proceso de interacción con la base de datos.',
		'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
	);

	//Se devuelve el error en mensaje json
	//echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

	//se cierra transaccion
	$db->rollback();

	// Redirecciona a la pagina principal
	return redirect(back());
}
