<?php
//valida metodo
if (is_post()) :

    //Valida si variable esta definida
    if (isset($_POST['monto']) && isset($_POST['categoria'])) :
        $monto = trim($_POST['monto']);
        $fecha_ini = trim($_POST['fecha_ini']);
        $fecha_final = trim($_POST['fecha_final']);
        $categoria = $_POST['categoria'];

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            //Obtine la categoria
            $categoria = $db->query("SELECT id_categoria FROM inv_categorias WHERE id_categoria='$categoria' LIMIT 1")->fetch_first();
            $categoria = ($categoria['id_categoria']) ? $categoria['id_categoria'] : 0;

            if ($categoria > 0) {
                $Datos = array(
                    'fecha_registro' => date("Y-m-d"),
                    'hora_registro' => date("H:i:s"),
                    'monto' => $monto,
                    'fecha_inicio' => $fecha_ini,
                    'fecha_fin' => $fecha_final,
                    'categoria_id' => $categoria,
                    'id_empleado_q_registro' => $_user['persona_id']
                );
                $id_meta = $db->insert('inv_meta_categoria', $Datos);
                $_SESSION[temporary] = array(
                    'alert' => 'success',
                    'title' => 'Se creo la nueva!',
                    'message' => 'El registro se realizó correctamente.'
                );
                //se cierra transaccion
				$db->commit();

                redirect('?/metas-categorias/listar');
            } else {
                $_SESSION[temporary] = array(
                    'alert' => 'danger',
                    'title' => 'Error!',
                    'message' => 'No existen categorias registradas para poder crear la meta.'
                );
                //se cierra transaccion
				$db->commit();

                redirect('?/metas-categorias/listar');
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
            redirect('?/metas-categorias/listar');
            //Se devuelve el error en mensaje json
            //echo json_encode(array("status" => 'failed', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'));

            //se cierra transaccion
            $db->rollback();
        }
    else :
        echo 'Datos no definidos.';
    endif;
else :
    require_once not_found();
    exit;
endif;
