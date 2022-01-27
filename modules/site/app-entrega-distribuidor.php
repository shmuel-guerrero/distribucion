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
    if (isset($_POST['id_user'])) { 
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

            $egresos = (isset($_POST['id_egreso'])) ? $_POST['id_egreso'] : array();
            $id_user = $_POST['id_user'];
            $acciones = 0;
            $token = (isset($_POST['token'])) ? $_POST['token'] : '';

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $user = $db->select('*')->from('sys_users')->where('id_user',$id_user)->fetch_first();
            $bandera = 0;
                       
            if($egresos){
                $egresos = str_replace('[','',$egresos);
                $egresos = str_replace(']','',$egresos);
                $egresos = str_replace('"','',$egresos);
                $egreso = explode(',',$egresos);
                $egresos = array_unique($egreso);

                foreach ($egresos as $nro => $egreso) {
                    $id_egreso = $egresos[$nro];

                    //obtiene las preventas registradas que tienen un estado distinto al 3(entregado)
                    $datos_egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.id_egreso',$id_egreso)->where('b.estadoe !=',3)->fetch_first();
                    
                    if ($user && count($egresos) > 0) {
                        // se incrementa bandera si se encuentra algun movimiento de otro almacen
                        $bandera = ($datos_egreso['almacen_id'] != $user['almacen_id'])? $bandera + 1 : $bandera;
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

                foreach ($egresos as $nro => $egreso) {
                    $id_egreso = $egresos[$nro];

                    //obtiene las preventas registradas que tienen un estado distinto al 3(entregado)
                    $datos_egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.id_egreso',$id_egreso)->where('b.estadoe !=',3)->fetch_first();
                    if ($user && count($egresos) > 0) {
                        $bandera = ($datos_egreso['almacen_id'] != $user['almacen_id'])? $bandera + 1 : $bandera;
                    }

                    if($datos_egreso){

                        // Para agregar nit y razon social
                        if (isset($_POST['razon_social']) && isset($_POST['nit'])) {
                            $cliente = isset($_POST['razon_social']);
                            $nit = isset($_POST['nit']);
                            $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('estadoe' => 3, 'nombre_cliente' => $cliente, 'nit_ci' => $nit));
                            $datos_egreso['nombre_cliente'] = $cliente;
                            $datos_egreso['nit_ci'] = $nit;
                        } else {
                            $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('estadoe' => 3));
                        }
                        
                        /**
                         * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                         * con la finalidad de generar reportes mas exactos
                         */

                        $id_movimiento = registros_historial($db, '_entregas', 'inv_egresos', 'id_egreso', $id_egreso, '', '', $user['persona_id'], 'SI', 0);                        
                        $movimiento = registros_historial($db, '_entregas', 'inv_egresos_detalles', 'egreso_id', $id_egreso, '', '', $user['persona_id'], 'NO', $id_movimiento);                        



                        // Fin Para agregar nit y razon social
                        $datos_egreso['distribuidor_fecha'] = date('Y-m-d');
                        $datos_egreso['distribuidor_hora'] = date('H:i:s');
                        $datos_egreso['distribuidor_estado'] = 'ENTREGA';
                        $datos_egreso['distribuidor_id'] = $user['persona_id'];
                        $datos_egreso['estado'] = 3;
                        $datos_egreso['factura'] = 'Ninguno';
                        $id = $db->insert('tmp_egresos', $datos_egreso);
                        $egresos_detalles = $db->select('*, egreso_id as tmp_egreso_id')->from('inv_egresos_detalles')->where('egreso_id',$id_egreso)->fetch();
                        foreach ($egresos_detalles as $nr => $detalle) {
                            $detalle['tmp_egreso_id'] = $id;
                            $db->insert('tmp_egresos_detalles', $detalle);
                        }
                    } 

                    //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                    save_process($db, 'u', '?/site/app-entrega-distribuidor', 'se realizo entrega', $id_egreso, $id_user, $token); 
                }


                //se cierra transaccion
                $db->commit();
                             
                 if ($datos_egreso['monto_total']) {
                                        
                    $respuesta = array(
                        'estado' => 's',
                        'estadoe' => 3,
                        'monto_total' => $datos_egreso['monto_total']
                    );
                    echo json_encode($respuesta);
                }else{
                    // Instancia el objeto
                    $respuesta = array('estado' => 'n',
                                         'msg' => 'Los datos fueron guardados parcialmente; revisar.');
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }  
            }else{
                //se cierra transaccion
                $db->commit();

                // Instancia el objeto
                $respuesta = array('estado' => 'n',
                                     'msg' => 'No existe registro de ventas.');

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
        // Devuelve los resultados
        echo json_encode(array('estado' => 'n',
                                'msg' => 'Datos no definidos'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido'));
}

?>