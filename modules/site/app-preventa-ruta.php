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

// Verifica la peticion post
if (is_post()) {
    // Verifica la existencia de datos
    if (isset($_POST['id_user'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $id_user = $_POST['id_user'];
        $dia = date('w');

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $ruta = $db->select('*')->from('sys_users a')->join('gps_rutas b','a.persona_id = b.empleado_id')->where('a.id_user',$id_user)->where('b.dia',$dia)->fetch_first();

            if($ruta['coordenadas']){
                $coordenadas = explode('*',$ruta['coordenadas']);
                $coor = array();
                $sw = false;
                foreach($coordenadas as $nro => $coordenada){
                    $parte = explode(',',$coordenadas[$nro]);
                    if($sw){
                        array_push($coor, array('latitud'=>(double)($parte[0] - 0.00005),'longitud'=>(double)($parte[1] + 0.00003)));
                    }else{
                        $sw = true;
                    }
                }
                $sw = false;
                $parte = explode(',',$coordenadas[1]);
                array_push($coor, array('latitud'=>(double)($parte[0] - 0.00005),'longitud'=>(double)($parte[1] + 0.00003)));        

                //se cierra transaccion
                $db->commit();

                if (count($coor) > 0) {                    
                    $respuesta = array(
                        'estado' => 's',
                        'id_ruta' => $ruta['id_ruta'],
                        'color' => ($ruta['color'] && $ruta['color'] != '') ? $ruta['color'] : '#7DCF9C',
                        'coordenadas' => $coor
                    );
                    echo json_encode($respuesta);
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'Sin datos.'));
                }
            }else{
                //se cierra transaccion
                $db->commit();
                // Instancia el objeto
                $respuesta = array(
                    'id_ruta' => 0,
                    'coordenadas' => '',
                    'estado' => 'n'
                );

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