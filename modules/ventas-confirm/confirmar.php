<?php

$importTotal = $_POST['importeTotalModal'];
$pagoEfectivo = $_POST['pagoEfectivoModal'];
$cambio = $_POST['cambioModal'];
$id_venta = $_POST['id_venta'];


//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
try {

    //Se abre nueva transacción.
    $db->autocommit(false);
    $db->beginTransaction();


    $datos = array(
        'estado' => 'Cerrado',
        'importe_total' => $importTotal,
        'pago_efectivo' => $pagoEfectivo,
        'cambio_efectivo' => $cambio
    );

    $condicion = array(
        'movimiento_id' => $id_venta, 
        'tipo_movimiento' => 'Egreso'
    );


    $db->where($condicion)->update('inv_egresos_efectivo', $datos);
    //echo "Affected Rows : " . $db->affected_rows ;    

	//se cierra transaccion
    $db->commit();

    echo json_encode(array('status' => 'success', 'responce' => $id_venta));

} catch (Exception $e) {
    $status = false;
    $error = $e->getMessage();

    // Instancia la variable de notificacion
    $_SESSION[temporary] = array(
        'alert' => 'danger',
        'title' => 'Problemas en el proceso de interacción con la base de datos.',
        'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
    );
    // Redirecciona a la pagina principal
    //redirect('?/notas/mostrar');
    //Se devuelve el error en mensaje json
    echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

    //se cierra transaccion
    $db->rollback();
}

