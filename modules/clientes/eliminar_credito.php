<?php

// echo json_encode($_POST); die();

$id_cliente = (sizeof($params) > 0) ? $params[0] : 0;

if ($id_cliente == 0) {
    set_notification('danger', 'Operación fallida!', 'Seleccione un cliente válido.');
    // Envia respuesta
    return redirect(back());
}

if ($id_cliente != 0) {

    //Habilita las funciones internas de notificación
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {

        //Se abre nueva transacción.
        $db->autocommit(false);
        $db->beginTransaction();
        $cliente = $db->select('*')->from('inv_clientes')->where('id_cliente', $id_cliente)->fetch_first();


        if ($cliente) {
            $cl = array(
                'credito' => 0,
                'dias' => 0,
            );
            // $id_cliente = $db->insert('inv_clientes', $cl);
            $db->where('id_cliente', (int)$id_cliente)->update('inv_clientes', $cl);

            // Guarda Historial
            $data = array(
                'fecha_proceso' => date("Y-m-d"),
                'hora_proceso' => date("H:i:s"),
                'proceso' => 'c',
                'nivel' => 'l',
                'direccion' => '?/clientes/guardar_credito',
                'detalle' => 'Se actualizó cliente con identificador numero ' . $id_cliente,
                'usuario_id' => $_SESSION[user]['id_user']
            );
            $db->insert('sys_procesos', $data);


            set_notification('success', 'Operación exitosa!', 'Se quitó el cliente de la lista de aptos para creditos.');
            // Envia respuesta
            return redirect('?/clientes/credito');
        } else {
            set_notification('danger', 'Operación fallida!', 'No existe el cliente.');
            // Envia respuesta
            return redirect(back());
        }
    } catch (Exception $e) {
        $status = false;
        $error = $e->getMessage();
        //se cierra transaccion
        $db->rollback();

        // Instancia la variable de notificacion
        $_SESSION[temporary] = array(
            'alert' => 'danger',
            'title' => 'Problemas en el proceso de interacción con la base de datos.',
            'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
        );

        return redirect(back());
    }
} else {
    set_notification('danger', 'Operación fallida!', 'Revise que se haya seleccionado un cliente.');
    // Envia respuesta
    return redirect(back());
}
