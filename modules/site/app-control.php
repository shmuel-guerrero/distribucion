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
    if (isset($_POST['id_user']) && isset($_POST['latitud']) && isset($_POST['longitud'])) {
        require config . '/database.php';
		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $id_user = $_POST['id_user'];
        $latitud = $_POST['latitud'];
        $logitud = $_POST['longitud'];
        $fecha = date('Y-m-d');
        $hora = '*'.date('H:i:s');
        //$ubicacion
        $coordenada = '*'.$latitud.','.$logitud;

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $respuesta = array();
            $ubicacion = $db->select('*')->from('gps_seguimientos')->where('user_id',$id_user)->where('fecha_seguimiento', date('Y-m-d'))->fetch_first();
            if($ubicacion){
                $datos = array(
                    'coordenadas' => $ubicacion['coordenadas'].$coordenada,
                    'hora_seguimiento' => $ubicacion['hora_seguimiento'].$hora
                );
                $db->where(array('user_id' => $id_user, 'fecha_seguimiento' => $fecha))->update('gps_seguimientos',$datos);
                $id = 1;
            }else{
                $datos = array(
                    'coordenadas' => $coordenada,
                    'fecha_seguimiento' => $fecha,
                    'hora_seguimiento' => $hora,
                    'user_id' => $id_user
                );
                $id = $db->insert('gps_seguimientos',$datos);
            }

        	//se cierra transaccion
			$db->commit();

            if ($id) {
                $respuesta = array(
                    'estado' => 's'
                );
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
        echo json_encode(array('estado' => 'n',
                                'msg' => 'datos no definidos.'));
    }
}else{
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
?>