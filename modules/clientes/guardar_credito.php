<?php

// echo json_encode($_POST); die();
if (is_post()) { // is_ajax() && 
    // Verifica la existencia de los datos enviados
    if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['credito']) && isset($_POST['dias']) && isset($_POST['id_cliente'])) {
        // Importa la libreria para convertir el numero a letra

        // Obtiene los datos de la proforma
        $nit_ci = trim($_POST['nit_ci']);
        $id_cliente = trim($_POST['id_cliente']);
        $credito = trim($_POST['credito']);
        $dias = trim($_POST['dias']);


        $id_empleado = ($_POST['empleado'] != 0) ? trim($_POST['empleado']) : $_user['persona_id'];

        if ($dias <= 0 || $credito != 'Credito') {
            set_notification('danger', 'Operación fallida!', 'Revise que se haya confirmado el credito y que haya proporcionado los días.');
            // Envia respuesta
            return redirect(back());
        }

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            if ($id_cliente != 0) {
                $cliente = $db->select('*')->from('inv_clientes')->where('id_cliente', $id_cliente)->fetch_first();
                if ($cliente) {
                    $cl = array(
                        'credito' => 1,
                        'dias' => $dias,
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

                    //se cierra transaccion
                    $db->commit();

                    set_notification('success', 'Operación exitosa!', 'Se asignó los dias para los créditos del cliente.');
                    // Envia respuesta
                    return redirect('?/clientes/credito');
                } else {

                    //se cierra transaccion
                    $db->commit();
                    
                    set_notification('danger', 'Operación fallida!', 'No existe el cliente.');
                    // Envia respuesta
                    return redirect(back());
                }
            } else {

                //se cierra transaccion
                $db->commit();

                set_notification('danger', 'Operación fallida!', 'Revise que se haya seleccionado un cliente.');
                // Envia respuesta
                return redirect(back());
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
            // Redirecciona a la pagina principal
            redirect('?/clientes/credito');
            //Se devuelve el error en mensaje json
            //echo json_encode(array("status" => 'failed', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'));

            //se cierra transaccion
            $db->rollback();
        }
    } else {
        set_notification('danger', 'Operación fallida!', 'Revise que se haya confirmado el credito y que haya proporcionado los días.');
        // Envia respuesta
        return redirect(back());
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
