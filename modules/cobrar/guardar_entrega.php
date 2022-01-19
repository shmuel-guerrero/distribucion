<?php
if (is_post()) :
    if (isset($_POST['idcontrol'])) :
        /*
            $IdControl= isset($_POST['idcontrol'])  ?$_POST['idcontrol']:[];
            $CantidadD=  isset($_POST['cantidad'])   ?$_POST['cantidad'] :[];
            foreach($IdControl as $Fila=>$IdC):
                $Cantidad=$db->query("SELECT cantidad FROM inv_control WHERE id_control='{$IdControl[$Fila]}' LIMIT 1")->fetch_first();
                $Cantidad=$Cantidad-$CantidadD[$Fila];
                $Campos=['cantidad'=>$Cantidad];
                $Condicion=['id_control'=>$IdControl[$Fila]];
                if($Cantidad==0):
                    $Campos=array_merge($Campos,['estado'=>'entregado']);
                endif;
                $db->where($Condicion)->update('inv_control',$Campos);
            endforeach;
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Devolución satisfactoria!',
                'message' => 'La devolución fue registrada correctamente.'
            );
            redirect('?/cobrar/lista_material_cliente');
*/
        $IdCliente = $_POST['IdCliente'];
        $IdAlmacen = $_POST['IdAlmacen'];
        $IdControl = isset($_POST['idcontrol'])  ? $_POST['idcontrol'] : [];
        $CantidadD = isset($_POST['cantidad'])   ? $_POST['cantidad'] : [];
        $CantidadV = isset($_POST['cantidadv'])  ? $_POST['cantidadv'] : [];
        $PrecioV = isset($_POST['preciov'])  ? $_POST['preciov'] : [];
        //Variables Extras
        $Total = 0;
        $Contador = 0;
        $Controles = array();
        $Aux = $_SESSION[user]['id_user'];

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            $IdEmpleado = $db->query("SELECT persona_id FROM sys_users WHERE id_user='{$Aux}' LIMIT 1")->fetch_first();
            $IdEmpleado = ($IdEmpleado['persona_id']) ?  $IdEmpleado['persona_id'] : 0;
            foreach ($IdControl as $Fila => $IdC) :
                if ($CantidadD[$Fila] != 0) : //Devolucion
                    $Cantidad = $db->query("SELECT cantidad FROM inv_control WHERE id_control='{$IdControl[$Fila]}' LIMIT 1")->fetch_first();
                    $Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
                    $Cantidad = $Cantidad - $CantidadD[$Fila];
                    $Campos = array('cantidad' => $Cantidad);
                    $Condicion = array('id_control' => $IdControl[$Fila]);
                    if ($Cantidad == 0)
                        $Campos = array_merge($Campos, ['estado' => 'entregado']);
                    $db->where($Condicion)->update('inv_control', $Campos);
                    //Aumentar Stock
                    $IdMaterial = $db->query("SELECT id_materiales FROM inv_control WHERE id_control='{$IdControl[$Fila]}' LIMIT 1")->fetch_first();
                    $IdMaterial = ($IdMaterial['id_materiales']) ? $IdMaterial['id_materiales'] : 0;
                    $Cantidad = $db->query("SELECT stock FROM inv_materiales_stock WHERE materiales_id='{$IdMaterial}' AND almacen_id='{$IdAlmacen}' LIMIT 1")->fetch_first();
                    $Cantidad = ($Cantidad['stock']) ? $Cantidad['stock'] : 0;
                    $Cantidad = $Cantidad + $CantidadD[$Fila];
                    $Campos = array(
                        'stock' => $Cantidad
                    );
                    $Condicion = array(
                        'materiales_id' => $IdMaterial,
                        'almacen_id' => $IdAlmacen,
                    );
                    $db->where($Condicion)->update('inv_materiales_stock', $Campos);
                endif;
                if ($CantidadV[$Fila] != 0) :
                    $Cantidad = $db->query("SELECT cantidad FROM inv_control WHERE id_control='{$IdControl[$Fila]}' LIMIT 1")->fetch_first();
                    $Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
                    $Cantidad = $Cantidad - $CantidadV[$Fila];
                    $Campos = array('cantidad' => $Cantidad);
                    $Condicion = array('id_control' => $IdControl[$Fila]);
                    if ($Cantidad == 0)
                        $Campos = array_merge($Campos, ['estado' => 'entregado']);
                    $db->where($Condicion)->update('inv_control', $Campos);

                    $IdMaterial = $db->query("SELECT id_materiales FROM inv_control WHERE id_control='{$IdControl[$Fila]}' LIMIT 1")->fetch_first();
                    $IdMaterial = ($IdMaterial['id_materiales']) ? $IdMaterial['id_materiales'] : 0;
                    $Control = array(
                        'id_materiales'     => $IdMaterial,
                        'tipo'              => 'cliente',
                        'cantidad'          => $CantidadV[$Fila],
                        'stock'             => 'egreso',
                        'cliente_id'        => $IdCliente,
                        'empleado_id'       => $IdEmpleado,
                        'fecha_control'     => date('Y-m-d'),
                        'estado'            => 'vendido',
                        'proveedor'         => '',
                        'ordenes_salidas_id' => '',
                        'egreso_id'         => 0,
                    );
                    $Controles[] = $Control;
                    $Total = $Total + ($CantidadV[$Fila] * $PrecioV[$Fila]);
                    ++$Contador;
                endif;
            endforeach;
            if ($Total > 0) :
                $DatosCliente = $db->query("SELECT nombre_factura,nit FROM inv_clientes WHERE id_cliente='{$IdCliente}' LIMIT 1")->fetch_first();
                $Datos = array(
                    'fecha_egreso'      => date('Y-m-d'),
                    'hora_egreso'       => date('H:i:s'),
                    'tipo'              => 'Venta',
                    'provisionado'      => 'S',
                    'descripcion'       => 'Venta de materiales',
                    'nro_factura'       => '',
                    'nro_autorizacion'  => '',
                    'codigo_control'    => '',
                    'fecha_limite'      => '0000-00-00',
                    'monto_total'       => $Total,
                    'cliente_id'        => $IdCliente,
                    'nit_ci'            => $DatosCliente['nit'],
                    'nombre_cliente'    => $DatosCliente['nombre_factura'],
                    'nro_registros'     => $Contador,
                    'dosificacion_id'   => 0,
                    'almacen_id'        => $IdAlmacen,
                    'empleado_id'       => $IdEmpleado,
                    'coordenadas'       => '',
                    'observacion'       => '',
                    'plan_de_pagos'     => 'no',
                    'estado'            => 1,
                    'estadoe'           => 3,
                    'descripcion_venta' => '',
                    'ruta_id'           => 0
                );
                $IdEgreso = $db->insert('inv_egresos', $Datos);
                foreach ($Controles as $Fila => $Dato) :
                    $Dato['egreso_id'] = $IdEgreso;
                    $db->insert('inv_control', $Dato);
                endforeach;
            endif;
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Devolución satisfactoria!',
                'message' => 'La devolución fue registrada correctamente.'
            );
            redirect('?/cobrar/lista_material_cliente');
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
            return redirect('?/cobrar/lista_material_cliente');
            //Se devuelve el error en mensaje json
            //echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

        }
    else :
        require_once bad_request();
    endif;
else :
    require_once not_found();
endif;
