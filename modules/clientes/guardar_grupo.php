<?php


// Verifica si es una peticion post
if (is_post()) {

    // Verifica la existencia de los datos enviados
    if (isset($_POST['grupo'])) {

        // Importa la libreria para subir la imagen
        require_once libraries . '/upload-class/class.upload.php';

        // Obtiene los datos del cliente
        $grupo = trim($_POST['grupo']);
        $descuento = trim($_POST['descuento']);
        $credito = trim($_POST['credito']);
        $permiso = trim($_POST['permiso']);
        $permiso = trim($_POST['estado']);

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            $datos = array(
                'nombre_grupo' => $grupo,
                'descuento_grupo' => $descuento,
                'credito_grupo' => $credito,
                'permiso_grupo' => $permiso,
                'estado_grupo' => $permiso

            );
            $id = $db->insert('inv_clientes_grupos', $datos);

            // Guardar Historial
            $data = array(
                'fecha_proceso' => date("Y-m-d"),
                'hora_proceso' => date("H:i:s"),
                'proceso' => 'c',
                'nivel' => 'l',
                'direccion' => '?/clientes/guardar_grupo',
                'detalle' => 'Se inserto grupo cliente con identificador numero ' . $id,
                'usuario_id' => $_SESSION[user]['id_user']
            );
            $db->insert('sys_procesos', $data);

            // Redirecciona a la pagina principal
            redirect('?/clientes/crear_grupo');
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
            // Redirecciona a la pagina principal o anterior			
            return redirect(back());
            //Se devuelve el error en mensaje json
            //echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

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
