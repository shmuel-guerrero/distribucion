<?php
if (is_post()) :
    if (isset($_POST['monto_total']) && isset($_POST['id_almacen'])) :
        $id_almacen = trim($_POST['id_almacen']);
        $monto_total = trim($_POST['monto_total']);
        $idmaterialstock = isset($_POST['idmaterialstock']) ? $_POST['idmaterialstock'] : [];
        $id_material = isset($_POST['id_material']) ? $_POST['id_material'] : [];
        $cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : [];
        $costo = isset($_POST['costo']) ? $_POST['costo'] : [];
        $planilla = trim($_POST['planilla']);
        $placa = trim($_POST['placa']);

        $IdUsuario = $_SESSION[user]['id_user'];

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $IdEmpleado = $db->query("SELECT persona_id FROM sys_users WHERE id_user='{$IdUsuario}'")->fetch_first();
            $IdEmpleado = ($IdEmpleado['persona_id']) ? $IdEmpleado['persona_id'] : 0;
            $Datos = array(
                'Planilla' => $planilla,
                'Placa' => $placa,
                'Fecha' => date('Y-m-d'),
                'empleado_id' => $IdEmpleado,
            );
            $IdIngresoMaterial = $db->insert('inv_ingreso_material', $Datos);

            for ($i = 0; $i < count($idmaterialstock); ++$i) :
                if ($idmaterialstock[$i] != 0) :
                    $CantidadC = $db->query("SELECT stock FROM inv_materiales_stock WHERE id_materiales_stock='{$idmaterialstock[$i]}' LIMIT 1")->fetch_first();
                    $CantidadC = ($CantidadC['stock']) ? $CantidadC['stock'] : 0;
                    $CantidadC = $CantidadC + $cantidad[$i];
                    $Campos = array('stock' => $CantidadC);
                    $Condicion = array('id_materiales_stock' => $idmaterialstock[$i]);
                    $db->where($Condicion)->update('inv_materiales_stock', $Campos);

                    $Datos = array(
                        'cantidad' => $cantidad[$i],
                        'ingreso_material_id' => $IdIngresoMaterial,
                        'materiales_stock_id' => $id_material[$i],
                    );
                    $db->insert('inv_detalle_ingreso_material', $Datos);
                else :
                    $Datos = array(
                        'stock' => $cantidad[$i],
                        'almacen_id' => $id_almacen,
                        'materiales_id' => $id_material[$i],
                    );
                    $IdMaterialStock = $db->insert('inv_materiales_stock', $Datos);

                    $Datos = array(
                        'cantidad' => $cantidad[$i],
                        'ingreso_material_id' => $IdIngresoMaterial,
                        'materiales_stock_id' => $id_material[$i],
                    );
                    $db->insert('inv_detalle_ingreso_material', $Datos);
                endif;
            endfor;
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Ingreso satisfactorio!',
                'message' => 'El ingreso fue registrado correctamente.'
            );
            redirect("?/cobrar/lista_material_fabrica/{$id_almacen}");
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
    else :
        require_once bad_request();
    endif;
else :
    require_once not_found();
endif;
