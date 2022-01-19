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
    if (isset($_POST['id_user']) && isset($_POST['id_cliente'])) {

        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $cliente_id = $_POST['id_cliente'];

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

                // Obtiene los usuarios que cumplen la condicion
                $usuario = $db->from('sys_users')->join('sys_empleados','persona_id = id_empleado')->where('id_user',$_POST['id_user'])->fetch_first();
                $emp = $usuario['id_empleado'];

                //buscar si es vendedor o distribuidor
                $deudas = $db->select('b.id_pago, a.fecha_egreso, a.tipo, sum(CASE WHEN c.estado = 0 THEN FORMAT(c.monto, 2) ELSE 0 END) as pendiente, COUNT(c.id_pago_detalle) as nro_cuotas , a.monto_total')
                                ->from('inv_egresos a')
                                ->join('inv_pagos b','b.movimiento_id = a.id_egreso')
                                ->join('inv_pagos_detalles c','c.pago_id = b.id_pago')
                                ->where('a.plan_de_pagos','si')
                                ->where('c.estado', 0)
                                ->where('a.cliente_id',$_POST['id_cliente'])
                                ->where('b.tipo', 'Egreso')
                                ->where_in('a.estadoe', array('3'))
                                ->group_by('a.id_egreso')->fetch();

                foreach($deudas as $nro => $deuda){
                    $detalles = $db->select('*')->from('inv_pagos_detalles')->where('pago_id',$deuda['id_pago'])->where('estado', 0)->fetch();
                    foreach($detalles as $nro2 => $detalle){
                        $detalles[$nro2]['id_cliente'] = $cliente_id;
                    }
                    $deudas[$nro]['detalle'] = $detalles;
                }
                
                //se cierra transaccion
				$db->commit();

                if ($deudas) {                    
                    $respuesta = array(
                        'estado' => 's',
                        'deudas' => $deudas
                    );
                    //se envia respuesta en json
                    echo json_encode($respuesta);
                }else{
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'No se tiene registro de deudas.'));
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
                                'msg' => 'Datos no definido.'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}

?>