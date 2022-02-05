<?php

/**
 * Returns the requested information after verification of received data.
 * performs actions on the database. 
 * CAUTION IN DATA HANDLING
 *
 * @access protected
 * @param Simple-Service-Web 
 * @author Revision Shmuel Guerrero  
 * @return json
 * @static
 * @version @Revision v1 2021-08
 */
 
// Define las cabeceras
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: application/json; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
date_default_timezone_set('America/La_Paz');


// Verifica la peticion post
if (is_post()) {
    // Verifica la existencia de datos
    if (isset($_POST['id_egreso']) && isset($_POST['id_user'])) {

        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $id_user_recibido = ($_POST['id_user']) ? $_POST['id_user'] : 0;
            //buscamos al empleado
            $empleado = $db->select('persona_id')->from('sys_users')->where('id_user', $_POST['id_user'])->fetch_first();
            $id_user = $empleado['persona_id'];
            $id_egreso = $_POST['id_egreso'];
            $token = (isset($_POST['token'])) ? $_POST['token'] : '';

            //buscamos el egreso        
            $egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, 
                                    b.almacen_id as distribuidor_id, b.estadoe as estado')
                        ->from('inv_egresos b')->where('id_egreso', $id_egreso)->fetch_first();


            // verificamos si existe el egreso
            if($egreso){
                // Actualiza la informacion (anulamos la factura)
                $db->where('id_egreso', $id_egreso)->update('inv_egresos', array('estadoe' => 0, 'anulado' => 3, 'plan_de_pagos' => 'no' ));
                $db->where('id_egreso', $id_egreso)->update('tmp_egresos', array('estadoe' => 0, 'anulado' => 3, 'plan_de_pagos' => 'no' ));

                $egreso['distribuidor_fecha'] = date('Y-m-d');
                $egreso['distribuidor_hora'] = date('H:i:s');
                $egreso['distribuidor_estado'] = 'DEVUELTO';
                $egreso['distribuidor_id'] = $id_user;
                $egreso['estado'] = 3;
                $egreso['accion'] = 'Anulado';

                $id_egreso_tmp = $db->insert('tmp_egresos', $egreso);

                // listamos los detalles del egreso
                $detalles = $db->select('a.*, a.id_detalle as tmp_egreso_id')
                            ->from('inv_egresos_detalles a')
                            ->where('egreso_id', $id_egreso)
                            ->fetch();

                // recorremos los detalles
                foreach ($detalles as $key => $detalle) {

                    //Validae promocion
                    if ($detalle['promocion_id'] != 0 && $detalle['promocion_id'] != '') {
                        echo json_encode(array('estado' => 'promocion'));
                    } else {
                        $unidad = $detalle['unidad_id'];
                        $id_producto = $detalle['producto_id'];
                        $cantidad_unidad = $detalle['cantidad'];
                        $precio = $detalle['precio'];
                        $monto_total = $egreso['monto_total'];
                        $registros = $egreso['nro_registros'];
                        /////////////////////////////////////////////////////////////////////
                        $Lotes=$db->query("SELECT producto_id,lote,unidad_id
                                        FROM inv_egresos_detalles AS ed
                                        LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
                                        WHERE id_detalle='{$id_detalle}'")->fetch();
                                        
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
                        //datos del producto
                        // $db->delete()->from('inv_egresos_detalles')->where('id_detalle', $id_detalle)->limit(1)->execute();
                        $detalle['tmp_egreso_id'] = $id_egreso_tmp;
                        $db->insert('tmp_egresos_detalles', $detalle);
                    }
                }

                // if ($registros > 1) {
                //     $c = cantidad_unidad($db, $id_producto, $unidad);
                //     $monto_total2 = ($precio * ($cantidad_unidad / $c));
                //     $monto_total = $monto_total - ($precio * ($cantidad_unidad / $c));
                //     // echo json_encode($c);exit(); 
                //     $db->where('id_egreso', $egreso['id_egreso'])->update('inv_egresos', array('monto_total' => $monto_total, 'nro_registros' => ($registros - 1), 'monto_total_descuento' => ($monto_total - $egreso['descuento_bs'])));
                // } else {
                //     $db->delete()->from('inv_egresos')->where('id_egreso', $detalle['egreso_id'])->limit(1)->execute();
                //     $monto_total2 = $monto_total;
                // }



                /**
                 * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                 * con la finalidad de generar reportes mas exactos
                 */
                
                $id_movimiento = registros_historial($db, '_anular', 'inv_egresos', 'id_egreso', $id_egreso, '', '', $id_user, 'SI', 0);
                $movimiento = registros_historial($db, '_anular', 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $id_user, 'NO', $id_movimiento);
               

                // para anular y borrar los pagos del egreso
                if ($egreso['plan_de_pagos'] == 'si') {
                    $pago = $db->from('inv_pagos')->where('movimiento_id', $egreso['id_egreso'])->where('tipo', 'Egreso')->fetch_first();
                    if ($pago) {
                        // $cuotas = $db->from('inv_pagos_detalles')->where('pago_id', $pago['id_pago'])->fetch();

                         //se crea backup de registros a eliminar
                        $verifica_id = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $pago['id_pago'], '', '', $empleado['persona_id'], 'NO', 0, "Eliminado");
                        //se eliminan registro
                        $db->delete()->from('inv_pagos_detalles')->where('pago_id', $pago['id_pago'])->execute();
                    }
                    //se crea backup de registros a eliminar
                    $verifica = backup_registros($db, 'inv_pagos', 'id_pago', $pago['id_pago'], '', '', $empleado['persona_id'], 'SI', 0, "Eliminado");
                    //se eliminan registro                     
                    $db->delete()->from('inv_pagos')->where('id_pago', $pago['id_pago'])->execute();
                    // $db->where('id_egreso', $egreso['id_egreso'])->update('inv_egresos', array('plan_de_pagos' => 'no'));
                    // $db->where('id_egreso', $egreso['id_egreso'])->update('tmp_egresos', array('plan_de_pagos' => 'no'));
                }

                 //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                 save_process($db, 'a', '?/site/app-anular-entrega', 'anulo movimiento', $id_egreso, $id_user_recibido, $token);

                //se cierra transaccion
				$db->commit();

                $respuesta = array(
                    'estado' => 'anulado',
                    'estadoe' => 0
                );
                echo json_encode($respuesta);

            }else{
        		//se cierra transaccion
    			$db->commit();
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'Movimiento no registrado.'));
            }

        } catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();

            //Se devuelve el error en mensaje json
            echo json_encode(array("estado" => 'n', 'msg'=>$error));

            //se cierra transaccion
            $db->rollback();
        }

    } else {
        // Devuelve los resultados
        echo json_encode(array('estado' => 'n',
                                'msg' => 'Datos no definidos.'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
