<?php

// Verifica si es una peticion ajax y post
if (is_post()) {
    // Verifica la existencia de los datos enviados
    if (isset($_POST['id_egreso'])  && isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
        // Importa la libreria para convertir el numero a letra
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // Obtiene los datos de la proforma
        $nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $telefono = trim($_POST['telefono_cliente']);
        $validez = trim($_POST['validez']);
        $observacion = trim($_POST['observacion']);
        $direccion = trim($_POST['direccion']);
        $atencion = trim($_POST['atencion']);
        $productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
        $nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
        $cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
        $descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
        $nro_registros = trim($_POST['nro_registros']);
        $monto_total = trim($_POST['monto_total']);
        $almacen_id = trim($_POST['almacen_id']);
        $adelanto = trim($_POST['adelanto']);
        $cliente_id = trim($_POST['cliente_id']);
        $id_egreso = ($_POST['id_egreso']) ? $_POST['id_egreso'] : 0;

        // Para creditos HGC
        $credito = trim($_POST['credito']);

        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            //se envia datos a validar el stock de los productos
            $validar_stock_productos = validar_stock($db, $productos, $cantidades, $unidad, $almacen_id);
            $message = "";

            //validar que se e¡tiene elementos con stock por debajo de lo requerido
            //if (count($validar_stock_productos) > 0) {
            if (false) {
                // Instancia la variable de notificacion
                $_SESSION[temporary] = array(
                    'alert' => 'danger',
                    'title' => 'Accion erronea!',
                    'message' => 'No se guardo registro.'
                );

                $message = preparar_mensaje($validar_stock_productos);
                echo json_encode(array('status' => 'invalid', 'responce' => $message));
                redirect('?/operaciones/preventas_listar');
                exit;
            }

            //obtiene al cliente
            $cliente = $db->select('*')->from('inv_clientes')->where(array('id_cliente' => $cliente_id))->fetch_first();

            if (!$cliente) {
                $cl = array(
                    'cliente' => $nombre_cliente,
                    'nit' => $nit_ci,
                    'telefono' => $telefono,
                    'ubicacion' => $atencion,
                    'direccion' => $direccion
                );
                $id_cliente = $db->insert('inv_clientes', $cl);
                // Guarda Historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'c',
                    'nivel' => 'l',
                    'direccion' => '?/operaciones/guardar',
                    'detalle' => 'Se creo cliente con identificador numero ' . $id_cliente,
                    'usuario_id' => $_SESSION[user]['id_user']
                );
                $db->insert('sys_procesos', $data);
            } else {
                $id_cliente = $cliente['id_cliente'];
            }
            if ($id_egreso > 0) {

                /**
                 * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                 * con la finalidad de generar reportes mas exactos
                 */

                $id_movimiento = registros_historial($db, '_ventas_editadas', 'inv_egresos', 'id_egreso', $id_egreso, '', '', $_user['persona_id'], 'SI', 0);
                $movimiento = registros_historial($db, '_ventas_editadas', 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $_user['persona_id'], 'NO', $id_movimiento);

                //se guarda una copia de la factura original
                $verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $id_egreso, '', '', $_user['persona_id'], 'SI', 0, "Eliminado");

                $proforma = array(
                    'monto_total' => $monto_total,
                    'monto_total_descuento' => $monto_total,
                    'descuento_bs' => 0,
                    'cliente_id' => $id_cliente,
                    'nit_ci' => $nit_ci,
                    'nombre_cliente' => mb_strtoupper($nombre_cliente, 'UTF-8'),
                    'nro_registros' => $nro_registros,
                    // 'empleado_id' => $_user['persona_id'],
                    'coordenadas' => $atencion,
                    'observacion' => $observacion,
                    'estado' => 2,
                    'plan_de_pagos' => ($credito == '1' || $credito == 1) ? 'si' : 'no', //$plan
                );

                // Guarda la informacion
                $db->where('id_egreso', $id_egreso)->update('inv_egresos', $proforma);
                $db->where('id_egreso', $id_egreso)->update('inv_egresos_inicio', $proforma);
                // Guarda Historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'u',
                    'nivel' => 'l',
                    'direccion' => '?/operaciones/guardar',
                    'detalle' => 'Se actualizo egreso con identificador numero ' . $id_egreso,
                    'usuario_id' => $_SESSION[user]['id_user']
                );
                $db->insert('sys_procesos', $data);

                /////////////////////////////////////////////////////////////////////
                $Lotes = $db->query("SELECT producto_id,lote,unidad_id
                                FROM inv_egresos_detalles AS ed
                                LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
                                WHERE egreso_id='{$id_egreso}'")->fetch();
                foreach ($Lotes as $Fila => $Lote) :
                    $IdProducto = $Lote['producto_id'];
                    $UnidadId = $Lote['unidad_id'];
                    $LoteGeneral = explode(',', $Lote['lote']);
                    for ($i = 0; $i < count($LoteGeneral); ++$i) :
                        $SubLote = explode('-', $LoteGeneral[$i]);
                        $Lot = $SubLote[0];
                        $Cantidad = $SubLote[1];
                        $DetalleIngreso = $db->query("SELECT id_detalle,lote_cantidad
                                                FROM inv_ingresos_detalles
                                                WHERE producto_id='{$IdProducto}' AND lote='{$Lot}'
                                                LIMIT 1")->fetch_first();
                        $Condicion = array(
                            'id_detalle' => $DetalleIngreso['id_detalle'],
                            'lote' => $Lot,
                        );
                        $CantidadAux = $Cantidad;
                        $Datos = array(
                            'lote_cantidad' => (strval($DetalleIngreso['lote_cantidad']) + strval($CantidadAux)),
                        );
                        $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                    endfor;
                endforeach;
                /////////////////////////////////////////////////////////////////////
                //$db->delete()->from('inv_clientes_grupos')->where('id_cliente_grupo', $id_grupo)->limit(1)->execute();

                //se guarda una copia del detalle de la factura original
                $verifica = backup_registros($db, 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");

                $db->delete()->from('inv_egresos_detalles')->where('egreso_id', $id_egreso)->execute();
                $db->delete()->from('inv_egresos_detalles_inicio')->where('egreso_id', $id_egreso)->execute();
                // Guarda Historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'u',
                    'nivel' => 'l',
                    'direccion' => '?/operaciones/guardar',
                    'detalle' => 'Se elimino inventario egreso detalle con identificador numero' . $id_egreso,
                    'usuario_id' => $_SESSION[user]['id_user']
                );
                $db->insert('sys_procesos', $data);

                foreach ($productos as $nro => $elemento) {
                    // Forma el detalle
                    $aux = $db->select('*')->from('inv_productos')->where('id_producto', $productos[$nro])->fetch_first();

                    ///Verifica si  el producto es de promocion
                    if ($aux['promocion'] == 'si') {
                        // Forma el detalle
                        $prod = $productos[$nro];
                        $promos = $db->select('producto_id, precio, unidad_id, cantidad, descuento , id_promocion as egreso_id, cantidad as promocion_id')
                            ->from('inv_promociones')->where('id_promocion', $prod)->fetch();
                        /////////////////////////////////////////////////////////////////////////////////////////
                        $Lote = '';
                        $CantidadAux = $cantidades[$nro];
                        $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                        foreach ($Detalles as $Fila => $Detalle) :
                            if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                $Datos = array(
                                    'lote_cantidad' => 0,
                                );
                                $Cant = $Detalle['lote_cantidad'];
                            elseif ($CantidadAux > 0) :
                                $Datos = array(
                                    'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                );
                                $Cant = $CantidadAux;
                            else :
                                break;
                            endif;
                            $Condicion = array(
                                'id_detalle' => $Detalle['id_detalle'],
                            );
                            $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                            $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                            $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                        endforeach;
                        $Lote = trim($Lote, ',');
                        /////////////////////////////////////////////////////////////////////////////////////////
                        $detalle = array(
                            'cantidad' => $cantidades[$nro],
                            'precio' => $precios[$nro],
                            'descuento' => 0,
                            'unidad_id' => 7,
                            'producto_id' => $productos[$nro],
                            'egreso_id' => $id_egreso,
                            'promocion_id' => 1,
                            'lote' => $Lote
                        );
                        // Guarda la informacion
                        $id = $db->insert('inv_egresos_detalles', $detalle);

                        // Guarda en el historial
                        $data = array(
                            'fecha_proceso' => date("Y-m-d"),
                            'hora_proceso' => date("H:i:s"),
                            'proceso' => 'c',
                            'nivel' => 'l',
                            'direccion' => '?/operaciones/guardar',
                            'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
                            'usuario_id' => $_SESSION[user]['id_user']
                        );
                        $db->insert('sys_procesos', $data);

                        foreach ($promos as $key => $promo) {
                            /////////////////////////////////////////////////////////////////////////////////////////
                            $Lote = '';
                            $CantidadAux = $promo['cantidad'] * $cantidades[$nro];
                            $prodpromo = $promo['producto_id'];
                            $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$prodpromo' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                            foreach ($Detalles as $Fila => $Detalle) :
                                if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                    $Datos = array(
                                        'lote_cantidad' => 0,
                                    );
                                    $Cant = $Detalle['lote_cantidad'];
                                elseif ($CantidadAux > 0) :
                                    $Datos = array(
                                        'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                    );
                                    $Cant = $CantidadAux;
                                else :
                                    break;
                                endif;
                                $Condicion = array(
                                    'id_detalle' => $Detalle['id_detalle'],
                                );
                                $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                                $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                                $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                            endforeach;
                            $Lote = trim($Lote, ',');
                            /////////////////////////////////////////////////////////////////////////////////////////
                            $promo['lote'] = $Lote;
                            $promo['egreso_id'] = $id_egreso;
                            $promo['promocion_id'] = $productos[$nro];
                            $promo['cantidad'] = $promo['cantidad'] * $cantidades[$nro];

                            // Guarda la informacion
                            $db->insert('inv_egresos_detalles', $promo);
                            // Guarda en el historial
                            $data = array(
                                'fecha_proceso' => date("Y-m-d"),
                                'hora_proceso' => date("H:i:s"),
                                'proceso' => 'c',
                                'nivel' => 'l',
                                'direccion' => '?/operaciones/guardar',
                                'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $egreso_id,
                                'usuario_id' => $_SESSION[user]['id_user']
                            );
                            $db->insert('sys_procesos', $data);
                        }
                    } else {
                        $id_unidad = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
                        $cantidad = $cantidades[$nro] * cantidad_unidad($db, $productos[$nro], $id_unidad['id_unidad']);

                        //Actualiza el lote de los productos
                        $Lote = '';
                        $CantidadAux = $cantidad;
                        $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                        foreach ($Detalles as $Fila => $Detalle) :
                            if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                $Datos = array(
                                    'lote_cantidad' => 0,
                                );
                                $Cant = $Detalle['lote_cantidad'];
                            elseif ($CantidadAux > 0) :
                                $Datos = array(
                                    'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                );
                                $Cant = $CantidadAux;
                            else :
                                break;
                            endif;
                            $Condicion = array(
                                'id_detalle' => $Detalle['id_detalle'],
                            );
                            $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                            $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                            $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                        endforeach;
                        $Lote = trim($Lote, ',');
                        /////////////////////////////////////////////////////////////////////////////////////////

                        $detalle = array(
                            'cantidad' => $cantidad,
                            'precio' => $precios[$nro],
                            'unidad_id' => $id_unidad['id_unidad'],
                            'descuento' => $descuentos[$nro],
                            'producto_id' => $productos[$nro],
                            'egreso_id' => $id_egreso,
                            'lote' => $Lote
                        );
                        // Genera los subtotales
                        $subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

                        // Guarda la informacion
                        $id = $db->insert('inv_egresos_detalles', $detalle);
                        $id_inicio = $db->insert('inv_egresos_detalles_inicio', $detalle);
                        // Guarda en el historial
                        $data = array(
                            'fecha_proceso' => date("Y-m-d"),
                            'hora_proceso' => date("H:i:s"),
                            'proceso' => 'c',
                            'nivel' => 'l',
                            'direccion' => '?/operaciones/guardar',
                            'detalle' => 'Se inserto el inventario egreso detalle con identificador numero ' . $id,
                            'usuario_id' => $_SESSION[user]['id_user']
                        );
                        $db->insert('sys_procesos', $data);
                    }
                }


                //Cuentas manejo de HGC
                if ($credito == '1' || $credito = 1) {

                    $creditoEx = $db->from('inv_pagos')->where('tipo', 'Egreso')->where('movimiento_id', $id_egreso)->fetch_first();
                    if ($creditoEx) {

                        // guarda backup de lo que sea que se haya eliminado
                        $verifica_id = backup_registros($db, 'inv_pagos', 'movimiento_id', $id_egreso, 'tipo', 'egreso', $_user['persona_id'], 'SI', 0, "Eliminado");
                        $verifica = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $creditoEx['id_pago'], '', '', $_user['persona_id'], 'NO', $verifica_id, "Eliminado");

                        $db->delete()->from('inv_pagos')->where('movimiento_id', $id_egreso)->where('tipo', 'Egreso')->execute();
                        $db->delete()->from('inv_pagos_detalles')->where('pago_id', $creditoEx['id_pago'])->execute();
                    }


                    $id_cli = (int)$_POST['id_cliente'];
                    $clienteCred = $db->from('inv_clientes')->where('id_cliente', $id_cli)->fetch_first();
                    // var_dump($clienteCred);
                    // echo $db->last_query();
                    // Instancia el ingreso
                    $ingresoPlan = array(
                        'movimiento_id' => $id_egreso,
                        'interes_pago' => 0,
                        'tipo' => 'Egreso'
                    );
                    // Guarda la informacion del ingreso general
                    $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);

                    $parafecha = $db->select('fecha_egreso')->from('inv_egresos')->where('id_egreso', $id_egreso)->fetch_first();
                    $parafecha = ($parafecha['fecha_egreso']) ? $parafecha['fecha_egreso'] : date('Y-m-d');

                    // $Date = date('Y-m-d');
                    $Date = $parafecha;
                    $Dias = ' + ' . $clienteCred['dias'] . ' days';
                    $Fecha_pago = date('Y-m-d', strtotime($Date . $Dias));

                    $detallePlan = array(
                        'nro_cuota' => 1,
                        'pago_id' => $ingreso_id_plan,
                        'fecha' => $Fecha_pago,
                        'fecha_pago' => $Fecha_pago,
                        'monto' => $monto_total,
                        'tipo_pago' => '',
                        'empleado_id' => $_user['persona_id'],
                        'estado'  => '0'
                    );
                    // Guarda la informacion
                    $db->insert('inv_pagos_detalles', $detallePlan);
                }
            }
            $_SESSION[temporary] = array(
                'alert' => 'success',
                'title' => 'Se creo el nuevo cliente!',
                'message' => 'El registro se realizó correctamente.'
            );
            redirect('?/operaciones/preventas_listar');

        } catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();

            // Instancia la variable de notificacion
            $_SESSION[temporary] = array(
                'alert' => 'danger',
                'title' => 'Problemas en el proceso de interacción con la base de datos.',
                'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario')) ? $error : 'Error en el proceso; comunicarse con soporte tecnico'
            );
            //se cierra transaccion
            $db->rollback();
            // Redirecciona a la pagina principal
            redirect('?/operaciones/preventas_listar');
            //Se devuelve el error en mensaje json
            //echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));

        }
    } else {
        // Envia respuesta
        // echo 'error';
        $_SESSION[temporary] = array(
            'alert' => 'danger',
            'title' => 'Revise los datos!',
            'message' => 'Revise que este enviando los datos correctamente, y al menos un produto, no se puede enviar sin productos.'
        );
        return redirect(back());
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
