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


// Verifica si es una peticion post
if (is_post()) {
	// Verifica la existencia de los datos enviados
	
    if (isset($_POST['id_pago']) && isset($_POST['id_pago_detalle']) && isset($_POST['pago']) && isset($_POST['id_user']) && isset($_POST['id_cliente']) && (isset($_POST['pago'])) > 0 && is_numeric($_POST['pago']))   { 
        require config . '/database.php';
   		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $id_user        = trim($_POST['id_user']);
        $id_cliente     = trim($_POST['id_cliente']);
        $id_pago        = trim($_POST['id_pago']);
        $id_pago_detalle= trim($_POST['id_pago_detalle']);
        $pago           = trim($_POST['pago']);
        $token = (isset($_POST['token'])) ? $_POST['token'] : '';
        $accion_device = 'u';
        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            if($_POST['id_pago'] > 0){
                $venta = $db->select('*, count(c.id_pago_detalle) as cuotas')->from('inv_egresos a')->join('inv_pagos b','b.movimiento_id = a.id_egreso')->join('inv_pagos_detalles c','c.pago_id = b.id_pago')->where('b.id_pago',$id_pago)->where('a.cliente_id',$id_cliente)->group_by('a.id_egreso')->fetch_first();
                
                if($venta){
                    // Obtiene los usuarios que cumplen la condicion
                    $usuario = $db->query("select id_user, id_empleado from sys_users LEFT JOIN sys_empleados ON persona_id = id_empleado where  id_user = '$id_user' and active = '1' limit 1")->fetch_first();
                    $detalle = $db->select('*')->from('inv_pagos_detalles')->where('id_pago_detalle',$id_pago_detalle)->fetch_first();
                    //si el pago es el mismo que tiene que cancela solo cobramos
                    if($detalle['monto'] == $pago){
                        
                        $accion_device = 'u';
                        $db->where('id_pago_detalle',$id_pago_detalle)->update('inv_pagos_detalles', array('estado' => 1, 'fecha_pago' => date('Y-m-d'),'tipo_pago' => 'efectivo','empleado_id'=>$usuario['id_empleado']));
                    }else{
                        //en el caso que cancele mas de su deuda
                        if($pago > $detalle['monto']){
                            //buscamos la siguiente deuda
                            $db->where('id_pago_detalle',$id_pago_detalle)->update('inv_pagos_detalles', array('estado' => 1, 'fecha_pago' => date('Y-m-d'),'tipo_pago' => 'efectivo','empleado_id'=>$usuario['id_empleado']));
                            $pago = $pago - $detalle['monto'];
                            $elementos = $db->select('*')->from('inv_pagos_detalles')->where('pago_id',$id_pago)->where('estado',0)->fetch();
                            foreach($elementos as $elemento){
                                if($pago >= $elemento['monto']){
                                    $db->where('id_pago_detalle',$elemento['id_pago_detalle'])->update('inv_pagos_detalles', array('estado' => 1, 'fecha_pago' => date('Y-m-d'),'tipo_pago' => 'efectivo','empleado_id'=>$usuario['id_empleado']));
                                    $pago = $pago - $elemento['monto'];
                                }else{
                                    $aux = $elemento['monto'] - $pago;
                                    $db->where('id_pago_detalle',$elemento['id_pago_detalle'])->update('inv_pagos_detalles', array('monto' => $aux));
                                }
                            }
                            if($pago > 0){
                                $aux = $detalle['monto'] + $pago;
                                $accion = $db->where('id_pago_detalle',$id_pago_detalle)->update('inv_pagos_detalles', array('monto' => $aux));
                            }
                        }
                        //en el caso que cancele menos de su deuda
                        else{
                            $monto = $detalle['monto'] - $pago;
                            $db->where('id_pago_detalle',$id_pago_detalle)->update('inv_pagos_detalles', array('estado' => 1, 'fecha_pago' => date('Y-m-d'),'tipo_pago' => 'efectivo','empleado_id'=>$usuario['id_empleado'],'monto'=>$pago));
                            $elementos = $db->select('*')->from('inv_pagos_detalles')->where('pago_id',$id_pago)->where('estado',0)->fetch();
                            foreach($elementos as $elemento){
                                if($monto > 0){
                                    $aux = $elemento['monto'] + $monto;
                                    $db->where('id_pago_detalle',$elemento['id_pago_detalle'])->update('inv_pagos_detalles', array('monto' => $aux));
                                    $monto = 0;
                                }
                            }
                            $accion_device = 'u';
                            if($monto > 0){
                                $dat = $db->select('*')->from('inv_pagos_detalles')->where('pago_id',$id_pago)->fetch();
                                $cont = count($dat)+1;

                                //se crea nuevo credito con plazo de una semana
                                $fecha_n = date("Y-m-d",strtotime(date('Y-m-d')."+ 1 week"));
                                $detallePlan = array(
                                    'pago_id'   => $id_pago,
                                    'fecha'     => $fecha_n,
                                    'monto'     => $monto,
                                    'estado'    => 0,
                                    'fecha_pago'=> $fecha_n,
                                    'tipo_pago' => 'cuotas',
                                    'nro_cuota' => $cont,
                                    'empleado_id' => $usuario['id_empleado']
                                );
                                $accion = $db->insert('inv_pagos_detalles', $detallePlan);
                                $accion_device = 'c';
                            }
                        }
                    }

                    //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                    save_process($db, $accion_device, '?/site/app-guardar-pago', 'se guarda pago', $id_pago, $id_user, $token); 

                    //se cierra transaccion
                    $db->commit();
                    
                    if ($accion) {
                        $respuesta = array('estado' => 's');
                        // Devuelve los resultados
                        echo json_encode($respuesta);
                    }else {
                        // Instancia el objeto
                        $respuesta = array('estado' => 'n',
                                    'msg' => 'El cliente no coincide con el pago.');
                         // Devuelve los resultados
                        echo json_encode($respuesta);
                    }
                }else{
                    //se cierra transaccion
					$db->commit();
                    // Instancia el objeto
                    $respuesta = array('estado' => 'n',
                                        'msg' => 'El cliente no coincide con el pago.');
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }
            }else{
                // Instancia el objeto
                $respuesta = array('estado' => 'n',
                                    'msg' => 'El pago debe ser mayor a cero.' );
                // Devuelve los resultados
                echo json_encode($respuesta);
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
        // Instancia el objeto
        $respuesta = array('estado' => 'n',
                            'msg' => 'Datos no definidos.');

        // Devuelve los resultados
        echo json_encode($respuesta);
	}
} else {
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
?>