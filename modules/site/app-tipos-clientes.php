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
if (true) {
    // Verifica la existencia de datos
    if (true) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
            // Obtiene los usuarios que cumplen la condicion
            $tipos = $db->from('inv_tipos_clientes')->fetch();

            // Verifica la existencia del usuario
            if ($tipos) {
                foreach($tipos as $nro => $tipo){
                    $tipos[$nro]['id_tipo_cliente'] = (int)$tipo['id_tipo_cliente'];
                }
                 //se cierra transaccion
				$db->commit();
                if (count($tipos)) {                    
                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 's',
                        'categorias' => $tipos
                    );                    
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n'));
                }
            } else {                
                //se cierra transaccion
				$db->commit();
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'No existe registrado tipos de clientes'));
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
        echo json_encode(array('estado' => 'n'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n'));
}

?>