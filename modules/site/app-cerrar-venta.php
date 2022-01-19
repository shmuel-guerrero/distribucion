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

        // Obtiene los usuarios que cumplen la condicion
        $id_user = $_POST['id_user'];

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
        
            // Obtiene el user
            $user = $db->from('sys_users')->where('id_user', $id_user)->fetch_first();

            // Verifica si el user existe
            if ($user) {

                $empleado = $db->from('sys_empleados')->where('id_empleado',$user['persona_id'])->fetch_first();
                // Obtiene el nuevo estado
                if($empleado){
                    $fecha_cierre = date("Y-m-d");
                    $hora_cierre = date('H:i:s');

                    // Instancia el user
                    $user = array(
                        'fecha' => $fecha_cierre,
                        'hora' => $hora_cierre
                    );

                    // Genera la condicion
                    $condicion = array('id_empleado' => $empleado['id_empleado']);

                    // Actualiza la informacion
                    $idg = $db->where($condicion)->update('sys_empleados', $user);
                    $modificacion = $db->affected_rows;
                    //se cierra transaccion
                    $db->commit();

                    if (($modificacion) > 0) {
                        // Instancia el objeto
                        $respuesta = array(
                            'estado' => 'v',
                            'cliente' => $idg
                        );
                        
                        // Devuelve los resultados
                        echo json_encode($respuesta);
                        
                    }else{
                        //Se devuelve el error en mensaje json
                        echo json_encode(array('estado' => 'n',
                                                'msg' => 'No se realizo ninguna modificacion.'));
                    }
                    
                }else{
                    //se cierra transaccion
    				$db->commit();
                    // Se devuelve el error en mensaje json
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'El usuario no posee un empleado asignado.'));
                }

            } else {                
                //se cierra transaccion
				$db->commit();
                //Se devuelve el error en mensaje json
                echo json_encode(array('estado' => 'n', 
                                        'msg' => 'Usuario no registrado.'));
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