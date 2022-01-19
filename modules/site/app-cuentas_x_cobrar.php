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
    if (isset($_POST['id_egreso']) && isset($_POST['monto_total']) && isset($_POST['fecha_pago_inicial']) && isset($_POST['monto_pago_inicial']) && isset($_POST['id_user'])){

        require config . '/database.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $monto_total        = $_POST['monto_total'];
        $nombre_cliente     = $_POST['nombre_cliente'];
        $pago_inicial       = $_POST['monto_pago_inicial'];
        $cuota_dos          = $_POST['monto_cuota_dos'];
        $cuota_tres         = $_POST['monto_cuota_tres'];

        $fecha_pago_inicial = trim($_POST['fecha_pago_inicial']);
        $fecha_pago_inicial = date("Y-m-d", strtotime(str_replace('/','-',$fecha_pago_inicial)));
        $fecha_cuota_dos    = trim($_POST['fecha_cuota_dos']);
        $fecha_cuota_dos = date("Y-m-d", strtotime(str_replace('/','-',$fecha_cuota_dos)));
        $fecha_cuota_tres   = trim($_POST['fecha_cuota_tres']);
        $fecha_cuota_tres = date("Y-m-d", strtotime(str_replace('/','-',$fecha_cuota_tres)));
        $detalle            = $_POST['motivo'];
        $nro_cuota          = $_POST['nro_cuotas'];

        $egresos = (isset($_POST['id_egreso'])) ? $_POST['id_egreso'] : array();
        $id_user = $_POST['id_user'];

        $user = $db->select('*')->from('sys_users')->where('id_user',$id_user)->fetch_first();
        $empleado_id = $user['persona_id'];

        if($egresos){
            $egresos = str_replace('[','',$egresos);
            $egresos = str_replace(']','',$egresos);
            $egresos = str_replace('"','',$egresos);
            $egreso = explode(',',$egresos);
            $egresos = array_unique($egreso);

            foreach ($egresos as $nro => $egreso) {
                $id_egreso = $egresos[$nro];
                $datos_egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')
                                    ->from('inv_egresos b')
                                    ->where('b.id_egreso',$id_egreso)
                                    ->fetch_first();
                if($datos_egreso){
                    $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('estadoe' => 3, 'plan_de_pagos' => 'si'));
                    $datos_egreso['distribuidor_fecha'] = date('Y-m-d');
                    $datos_egreso['distribuidor_hora'] = date('H:i:s');
                    $datos_egreso['distribuidor_estado'] = 'ENTREGA';
                    $datos_egreso['plan_de_pagos'] = 'si';
                    $datos_egreso['distribuidor_id'] = $user['persona_id'];
                    $datos_egreso['estado'] = 3;
                    $id = $db->insert('tmp_egresos', $datos_egreso);
                    $egresos_detalles = $db->select('*, egreso_id as tmp_egreso_id')->from('inv_egresos_detalles')->where('egreso_id',$id_egreso)->fetch();
                    foreach ($egresos_detalles as $nr => $detalle) {
                        $detalle['tmp_egreso_id'] = $id;
                        $db->insert('tmp_egresos_detalles', $detalle);
                    }
                }
                $fechas = array($fecha_pago_inicial, $fecha_cuota_dos, $fecha_cuota_tres);
                $cuotas = array($pago_inicial,$cuota_dos,$cuota_tres);
                $monto_total_cuotas = $pago_inicial+$cuota_dos+$cuota_tres;

                if($monto_total <= $monto_total_cuotas){
                    $fecha_format=(isset($fechas[0])) ? $fechas[0]: "00-00-0000";
                    $vfecha=explode("-",$fecha_format);
                    $fecha_format=$vfecha[0]."-".$vfecha[1]."-".$vfecha[2];
                    $ingresoPlan = array (
                        'movimiento_id' => $id_egreso,
                        'tipo' => 'Egreso'
                    );
                    // Guarda la informacion del ingreso general
                    $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
                    //inserta en cronograma
                    $datos_c = array(
                        'fecha' => $fecha_format,
                        'periodo' =>'trimestral',
                        'detalle' => $detalle,
                        'monto'=> $monto_total
                    );

                    $id_cronograma = $db->insert('cronograma', $datos_c);

                    if($cuotas[0]!=''){
                        $fecha_format = (isset($fechas[0]) && $fechas[0]!='') ? $fechas[0]: "00-00-0000";
                        $vfecha       = explode("-",$fecha_format);
                        $fecha_format = $vfecha[0]."-".$vfecha[1]."-".$vfecha[2];
                        $detallePlan = array(
                            'pago_id'   => $ingreso_id_plan,
                            'fecha'     => $fecha_format,
                            'monto'     => (isset($cuotas[0])) ? $cuotas[0]: 0,
                            'estado'    => 1,
                            'fecha_pago'=> $fecha_format,
                            'tipo_pago' => 'efectivo',
                            'nro_cuota' => 1,
                            'empleado_id' => $empleado_id
                        );
                        $db->insert('inv_pagos_detalles', $detallePlan);

                        //Inserta en cronograma detalle
                        $datos_cron_det = array(
                            'cronograma_id' => $id_cronograma,
                            'fecha'         => $fecha_format,
                            'estado'        => 1,
                            'fecha_pago'    => $fecha_format,
                            'tipo_pago'     => "efectivo",
                            'detalle'       => $detalle,
                            'empleado_id'   => $empleado_id,
                            'monto'          => (isset($cuotas[0])) ? $cuotas[0]: 0
                        );
                        $db->insert('cronograma_cuentas', $datos_cron_det);
                    }
                    if($cuotas[1]!='' && $cuotas[1]!=0){
                        $fecha_format = (isset($fechas[1]) && $fechas[1]!='') ? $fechas[1]: "00-00-0000";
                        $vfecha       = explode("-",$fecha_format);
                        $fecha_format = $vfecha[0]."-".$vfecha[1]."-".$vfecha[2];
                        $detallePlan = array(
                            'pago_id'   => $ingreso_id_plan,
                            'fecha'     => $fecha_format,
                            'monto'     => (isset($cuotas[1])) ? $cuotas[1]: 0,
                            'estado'    => 0,
                            'fecha_pago'=> $fecha_format,
                            'tipo_pago' => 'cuotas',
                            'nro_cuota' => 2,
                            'empleado_id' => $empleado_id
                        );
                        $db->insert('inv_pagos_detalles', $detallePlan);

                        //Inserta en cronograma detalle
                        $datos_cron_det = array(
                            'cronograma_id' => $id_cronograma,
                            'fecha'         => $fecha_format,
                            'estado'        => 0,
                            'fecha_pago'    => $fecha_format,
                            'tipo_pago'     => "cuotas",
                            'detalle'       => $detalle,
                            'empleado_id'   => $empleado_id,
                            'monto'          => (isset($cuotas[1])) ? $cuotas[1]: 0
                        );
                        $db->insert('cronograma_cuentas', $datos_cron_det);
                    }
                    if($cuotas[2]!='' && $cuotas[2]!=0){
                        $fecha_format = (isset($fechas[2]) && $fechas[2]!='') ? $fechas[2]: "00-00-0000";
                        $vfecha       = explode("-",$fecha_format);
                        $fecha_format = $vfecha[0]."-".$vfecha[1]."-".$vfecha[2];
                        $detallePlan = array(
                            'pago_id'   => $ingreso_id_plan,
                            'fecha'     => $fecha_format,
                            'monto'     => (isset($cuotas[2])) ? $cuotas[2]: 0,
                            'estado'    => 0,
                            'fecha_pago'=> $fecha_format,
                            'tipo_pago' => 'cuotas',
                            'nro_cuota' => 3,
                            'empleado_id' => $empleado_id
                        );
                        $db->insert('inv_pagos_detalles', $detallePlan);

                        //Inserta en cronograma detalle
                        $datos_cron_det = array(
                            'cronograma_id' => $id_cronograma,
                            'fecha'         => $fecha_format,
                            'estado'        => 0,
                            'fecha_pago'    => $fecha_format,
                            'tipo_pago'     => "cuotas",
                            'detalle'       => $detalle,
                            'empleado_id'   => $empleado_id,
                            'monto'          => (isset($cuotas[2])) ? $cuotas[2]: 0
                        );
                        $db->insert('cronograma_cuentas', $datos_cron_det);
                    }
                }
            }
             //se cierra transaccion
             $db->commit();
             
            if ($monto_total > 0) {                
                $respuesta = array(
                    'estado' => 's',
                    'estadoe' => 6,
                    'monto_total' => $monto_total
                );
                echo json_encode($respuesta);
            }else {
                // Instancia el objeto
                $respuesta = array('estado' => 'n',
                                    'msg' => 'El monto total es menor o igual a cero' );
                }
        }else{
            //se cierra transaccion
            $db->commit();
            // Instancia el objeto
            $respuesta = array('estado' => 'n',
                                'msg' => 'No se tiene registro de movimientos.' );
            // Devuelve los resultados
            echo json_encode($respuesta);
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