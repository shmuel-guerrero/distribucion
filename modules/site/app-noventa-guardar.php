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

if(is_post()) {

    if (isset($_POST['id_cliente']) && isset($_POST['id_user'])) {
        require config . '/database.php';
        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );
        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            //identificamos el usuario vendedor o distribuidor
            $rol = $db->select('a.rol_id, a.persona_id, b.fecha, a.almacen_id')->from('sys_users a')->join('sys_empleados b','a.persona_id = b.id_empleado')->where('a.id_user',$_POST['id_user'])->fetch_first();
            $id_user = $rol['persona_id'];
            if($rol['fecha'] != date('Y-m-d')){
                if( $rol['rol_id'] != 4 ){
                    $nit = $_POST['nit'];
                    $nombre_cliente = $_POST['cliente'];
                    $id_cliente = $_POST['id_cliente'];
                    $id_user = $_POST['id_user'];
                    $ubicacion = $_POST['ubicacion'];
                    $observacion = $_POST['prioridad'];
                    $hora_ini = $_POST['hora_inicial'];
                    $hora_fin = $_POST['hora_final'];
                    $motivo = $_POST['motivo_id'];

                    $horaInicio = new DateTime($hora_fin);
                    $horaTermino = new DateTime($hora_ini);

                    $duracion = $horaInicio->diff($horaTermino);
                    $duracion = $duracion->format('%H:%I:%s');

                    $empleado = $db->select('persona_id')->from('sys_users')->where('id_user',$id_user)->fetch_first();
                    $id_empleado = $empleado['persona_id'];

                    //buscamos la ruta que tiene
                    $ruta = $db->select('id_ruta')->from('gps_rutas')->where('empleado_id',$id_empleado)->where('dia',date('w'))->fetch_first();

                    //obtiene id de alamacen principal
                    $id_almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first()['id_almacen'];

                    $egreso = array(
                        'fecha_egreso' => date('Y-m-d'),
                        'hora_egreso' => date('H:i:s'),
                        'descripcion' => 'No se realizo ninguna venta',
                        'cliente_id' => $id_cliente,
                        'nit_ci' => $nit,
                        'nombre_cliente' => strtoupper($nombre_cliente),
                        'empleado_id' => $id_empleado,
                        'coordenadas' => $ubicacion,
                        'motivo_id' => $motivo,
                        'estadoe' => 1,
                        'duracion' => $duracion,
                        'tipo' => 'NO VENTA',
                        'provisionado' => 'N',
                        'nro_factura' => 0,
                        'nro_autorizacion' => 0,
                        'codigo_control' => 0,
                        'fecha_limite' => '0000-00-00',
                        'monto_total' => 0,
                        'nro_registros' => 0,
                        'dosificacion_id' => 0,
                        'almacen_id' => 0,
                        'ruta_id' => $ruta['id_ruta']
                    );

                    $id = $db->insert('inv_egresos',$egreso);

                    //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                    save_process($db, 'c', '?/site/app-noventa-guardar', 'se guarda no venta', $id, $id_user, $token); 

                    if($id){
                        $respuesta = array(
                            'estado' => 's',
                            'estadoe' => 1
                        );
                        echo json_encode($respuesta);
                    }else{
                        echo json_encode(array('estado' => 'no guardo'));
                    }
                }else{                

                    $verifica = $db->select('*')->from('tmp_egresos')->where('distribuidor_estado','NO ENTREGA')->where('estado',3)->where('cliente_id',$_POST['id_cliente'])->fetch_first();
                    $bandera = 0;

                    if(!$verifica){
                        $motivo = $_POST['motivo_id'];
                        $id_cliente = $_POST['id_cliente'];

                        $fecha = $db->select('a.fecha')->from('sys_empleados a')->join('inv_egresos b','a.id_empleado = b.empleado_id')->where('b.cliente_id',$id_cliente)->where('b.estadoe',2)->fetch_first();

                        if($fecha['fecha'] == date('Y-m-d')){
                            $egresos = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.fecha_egreso <=',date('Y-m-d'))->where('b.cliente_id',$_POST['id_cliente'])->where('b.estadoe',2)->fetch();
                            foreach ($egresos as $nro => $egreso) {
                                $id_egreso = $egreso['id_egreso'];
            
                                //obtiene las preventas registradas que tienen un estado distinto al 3(entregado)
                                $datos_egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.id_egreso',$id_egreso)->where('b.estadoe =',2)->fetch_first();
                                
                                if ($rol) {
                                    // se incrementa bandera si se encuentra algun movimiento de otro almacen
                                    $bandera = ($datos_egreso['almacen_id'] != $rol['almacen_id'])? $bandera + 1 : $bandera;
                                }
                            }
                        }
                        else{
                            $egresos = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.fecha_egreso <',date('Y-m-d'))->where('b.cliente_id',$_POST['id_cliente'])->where('b.estadoe',2)->fetch();
                            foreach ($egresos as $nro => $egreso) {
                                $id_egreso = $egreso['id_egreso'];
            
                                //obtiene las preventas registradas que tienen un estado distinto al 3(entregado)
                                $datos_egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.id_egreso',$id_egreso)->where('b.estadoe =',2)->fetch_first();
                                
                                if ($rol) {
                                    // se incrementa bandera si se encuentra algun movimiento de otro almacen
                                    $bandera = ($datos_egreso['almacen_id'] != $rol['almacen_id'])? $bandera + 1 : $bandera;
                                }
                            }
                        }


                        //se valida que los 
                        if ($bandera > 0) {
                            //se cierra transaccion
                            $db->commit();
                            // Instancia el objeto
                            $respuesta = array('estado' => 'n',
                                                'msg' => 'El distribuidor no tiene asignado el mismo almacen que el preventista.');
                            // Devuelve los resultados
                            echo json_encode($respuesta);
                            exit;
                        }


                        foreach($egresos as $nro2 => $egreso){
                            $egreso['distribuidor_fecha'] = date('Y-m-d');
                            $egreso['distribuidor_hora'] = date('H:i:s');
                            $egreso['distribuidor_estado'] = 'NO ENTREGA';
                            $egreso['motivo_id'] = $_POST['motivo_id'];
                            $egreso['distribuidor_id'] = $id_user;
                            $egreso['estado'] = 3;
                            $egreso['estadoe'] = 4;
                            $id_egreso = $egreso['id_egreso'];

                            $id = $db->insert('tmp_egresos', $egreso);

                            /**
                             * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                             * con la finalidad de generar reportes mas exactos
                             */
                            $id_movimiento = registros_historial($db, '_noentregas', 'inv_egresos', 'id_egreso', $egreso['id_egreso'], '', '', $id_user, 'SI', 0);
                            $movimiento = registros_historial($db, '_noentregas', 'inv_egresos_detalles', 'egreso_id', $egreso['id_egreso'] , '', '', $id_user, 'NO', $id_movimiento);

                            $db->delete()->from('inv_egresos')->where('id_egreso',$id_egreso)->limit(1)->execute();

                            $detalles = $db->select('a.*, a.id_detalle as tmp_egreso_id')->from('inv_egresos_detalles a')->where('a.egreso_id',$id_egreso)->fetch();
                            /////////////////////////////////////////////////////////////////////
                            $Lotes=$db->query("SELECT producto_id,lote,unidad_id
                                        FROM inv_egresos_detalles AS ed
                                        LEFT JOIN inv_unidades AS u ON ed.unidad_id=u.id_unidad
                                        WHERE egreso_id='{$id_egreso}'")->fetch();

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
                            foreach($detalles as $nro => $detalle){
                                $detalle['tmp_egreso_id'] = $id;
                                $id_detalle = $detalle['id_detalle'];
                                $accion = $db->insert('tmp_egresos_detalles', $detalle);
                                $db->delete()->from('inv_egresos_detalles')->where('id_detalle',$id_detalle)->limit(1)->execute();
                            }

                            //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                            save_process($db, 'c', '?/site/app-noventa-guardar', 'se guarda no entrega', $egreso['id_egreso'], $id_user, $token); 
                        }


                        //se cierra transaccion
					    $db->commit();

                        if ($accion && $id) {                            
                            $respuesta = array(
                                'estado' => 's',
                                'estadoe' => 4
                            );
                            echo json_encode($respuesta);
                        }else {
                            echo json_encode(array('estado' => 'n',
                                                    'msg' => 'Se guardo los datos parcialmente.'));                            
                        }
                    }else{
                        //se cierra transaccion
					    $db->commit();
                        echo json_encode(array('estado' => 'n',
                                                'msg' => 'El cliente tiene movimiento'));
                    }
                }
            }else{
                //se cierra transaccion
				$db->commit();
                echo json_encode(array('estado' => 'Inactivo'));
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
        echo json_encode(array('estado' => 'n',
                                'msg' => 'Datos no definidos.'));
    }
}else{
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
?>