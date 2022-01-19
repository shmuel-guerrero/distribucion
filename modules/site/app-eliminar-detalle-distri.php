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
    if (isset($_POST['id_detalle']) && isset($_POST['id_user']) && isset($_POST['estadoE'])) {

        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            //buscamos al empleado
            $empleado = $db->select('persona_id')->from('sys_users')->where('id_user', $_POST['id_user'])->fetch_first();
            $id_user = $empleado['persona_id'];
            $id_detalle = $_POST['id_detalle'];
            $estadoE = $_POST['estadoE'];

            //buscamos el detalle
            $detalle = $db->select('a.*, a.id_detalle as tmp_egreso_id')->from('inv_egresos_detalles a')->where('id_detalle', $id_detalle)->fetch_first();
            $egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('id_egreso', $detalle['egreso_id'])->fetch_first();

            if($detalle){
                if ($detalle['promocion_id'] != 0 && $detalle['promocion_id'] != '') {
                    //se cierra transaccion
					$db->commit();
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

                    /**
                     * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                     * con la finalidad de generar reportes mas exactos
                     */
                    $id_movimiento = registros_historial($db, '_eliminar_post', 'inv_egresos', 'id_egreso', $egreso['id_egreso'], '', '', $id_user, 'SI', 0);
                    $movimiento = registros_historial($db, '_eliminar_post', 'inv_egresos_detalles', 'id_detalle', $id_detalle, '', '', $id_user, 'NO', $id_movimiento);

                    //se crea backup de registros a eliminar
                    $verifica_id = backup_registros($db, 'inv_egresos', 'id_egreso', $egreso['id_egreso'], '', '', $empleado['persona_id'], 'SI', 0, "Backup");

                    //se crea backup de registros a eliminar
                    $verifica = backup_registros($db, 'inv_egresos_detalles', 'id_detalle', $id_detalle, '', '', $empleado['persona_id'], 'NO', $verifica_id, "Eliminado");
                    // se eliminan registros     
                    $db->delete()->from('inv_egresos_detalles')->where('id_detalle', $id_detalle)->limit(1)->execute();

                    if ($registros > 1) {
                        $c = cantidad_unidad($db, $id_producto, $unidad);
                        $monto_total2 = ($precio * ($cantidad_unidad / $c));
                        $monto_total = $monto_total - ($precio * ($cantidad_unidad / $c));
                        // echo json_encode($c);exit();
                        $db->where('id_egreso', $egreso['id_egreso'])->update('inv_egresos', array('monto_total' => $monto_total, 'nro_registros' => ($registros - 1), 'monto_total_descuento' => ($monto_total - $egreso['descuento_bs'])));
                    } else {
                        // se eliminan registros  
                        $db->delete()->from('inv_egresos')->where('id_egreso', $detalle['egreso_id'])->limit(1)->execute();
                        $monto_total2 = $monto_total;
                        $monto_total = 0;       

                        //SE MODIFICA EL DISTRIBUIDOR_ESTADO = ANULADO SI YA NO EXISTEN ITEM A ENTREGAR
                        $db->where(array('id_egreso' => $egreso['id_egreso'], 'distribuidor_estado' => 'ENTREGA'))->update('tmp_egresos', array('distribuidor_estado' => 'ANULADO', 'plan_de_pagos' => 'no'));               
                        $egreso['plan_de_pagos'] = 'no';
                    }
                    $egreso['monto_total'] = $monto_total2;
                    $egreso['nro_registros'] = 1;
                    $egreso['distribuidor_fecha'] = date('Y-m-d');
                    $egreso['distribuidor_hora'] = date('H:i:s');
                    $egreso['distribuidor_estado'] = 'DEVUELTO';
                    $egreso['accion'] = 'VentaEliminado';
                    $egreso['distribuidor_id'] = $id_user;
                    $egreso['estado'] = 3;

                    $id = $db->insert('tmp_egresos', $egreso);

                    $detalle['tmp_egreso_id'] = $id;
                    $id = $db->insert('tmp_egresos_detalles', $detalle);
                    
                    //Cuentas manejo de HGC
                    $id_cli = (int)$egreso['cliente_id'];
                    $clienteCred = $db->from('inv_clientes')->where('id_cliente', $id_cli)->fetch_first();
                    $credito = $clienteCred['credito'];
                    if ($credito == '1' || $credito = 1) {
                        
                        $creditoEx = $db->from('inv_pagos')->where('tipo', 'Egreso')->where('movimiento_id', $egreso['id_egreso'])->fetch_first(); 
                        if ($creditoEx){
                            //se crea backup de registros a eliminar
                            $verifica_id = backup_registros($db, 'inv_pagos', 'movimiento_id', $egreso['id_egreso'], 'tipo', 'Egreso', $empleado['persona_id'], 'SI', 0, "Eliminado");

                            // se eliminan registros 
                            $db->delete()->from('inv_pagos')->where('movimiento_id', $egreso['id_egreso'])->where( 'tipo', 'Egreso')->execute();

                            //se crea backup de registros a eliminar
                            $verifica = backup_registros($db, 'inv_pagos_detalles', 'pago_id', $creditoEx['id_pago'], '', '', $empleado['persona_id'], 'NO', $verifica_id, "Eliminado");

                            // se eliminan registros 
                            $db->delete()->from('inv_pagos_detalles')->where('pago_id', $creditoEx['id_pago'])->execute();
                        }
                        
                        if ($registros > 1) {
                            
                            // Instancia el ingreso
                            $ingresoPlan = array(
                                'movimiento_id' => $egreso['id_egreso'],
                                'interes_pago' => 0,
                                'tipo' => 'Egreso'
                            );
                            // Guarda la informacion del ingreso general
                            $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
                            
                            $parafecha = $db->select('fecha_egreso')->from('inv_egresos')->where('id_egreso', $egreso['id_egreso'])->fetch_first();
                            $parafecha = ($parafecha['fecha_egreso']) ? $parafecha['fecha_egreso'] : date('Y-m-d');
                            
                            // $Date = date('Y-m-d');
                            $Date = $parafecha;
                            $Dias = ' + ' . $clienteCred['dias'] .' days';
                            $Fecha_pago = date('Y-m-d', strtotime($Date. $Dias));
                            
                            $detallePlan = array(
                                'nro_cuota' => 1,
                                'pago_id' => $ingreso_id_plan,
                                'fecha' => $Fecha_pago,
                                'fecha_pago' => $Fecha_pago,
                                'monto' => ($monto_total - $egreso['descuento_bs']),
                                'tipo_pago' => '',
                                'empleado_id' => $empleado['persona_id'],
                                'estado'  => '0'
                            );
                            // Guarda la informacion
                            $db->insert('inv_pagos_detalles', $detallePlan);
                        }
                    }
                    
                    //se cierra transaccion
					$db->commit();

                    if ($egreso['plan_de_pagos']) {                                                
                        $respuesta = array(
                            'estado' => 's',
                            'monto_total' => number_format($monto_total ,2),
                            'cuentas_por_cobrar' => $egreso['plan_de_pagos'],
                            'estadoE' => $estadoE
                        );
                        if($monto_total == 0 || $registros == 1){
                            $respuesta['estadoE'] = 0;
                        }
                        echo json_encode($respuesta);
                    }else{
                        // Devuelve los resultados
                        echo json_encode(array('estado' => 'n',
                                                'msg' => 'Sin plan de pagos.'));
                    }
                }
            }else{
          		//se cierra transaccion
    			$db->commit();
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'El detalle ya fue eliminado.'));
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
                            'msg' => 'M etodo no definido.'));
}
