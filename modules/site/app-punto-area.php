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
    if (isset($_POST['id_user']) && isset($_POST['latitud']) && isset($_POST['longitud'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
        require config . '/poligono.php';
   		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
                    
            $id_user = $_POST['id_user'];
            $latitud = $_POST['latitud'];
            $longitud = $_POST['longitud'];
            $dia = date('w');
            $rol = $db->select('rol_id')->from('sys_users')->where('id_user',$_POST['id_user'])->fetch_first();
            $area = $db->select('*')->from('sys_users a')->join('sys_empleados b', 'a.persona_id = b.id_empleado')->join('gps_rutas c','b.id_empleado = c.empleado_id')->where('a.id_user',$id_user)->where('c.dia',$dia)->fetch_first();

            if($rol['rol_id'] != 4){
                if($area){
                    $point = $latitud.' '.$longitud;
                    $polygon = explode('*',$area['coordenadas']);
                    foreach ($polygon as $nro => $poly) {
                        $aux = explode(',',$poly);
                        $aux2 = (round($aux[0],6)-0.00005).','.(round($aux[1],6)+0.00003);
                        $polygon[$nro] = str_replace(',', ' ', $aux2);
                    }
                    $polygon[0] = str_replace(',', ' ', $polygon[$nro]);
                    $pointLocation = new pointLocation();

                    // Las últimas coordenadas tienen que ser las mismas que las primeras, para "cerrar el círculo"

                    $punto = $pointLocation->pointInPolygon($point, $polygon);
                    $respuesta = array(
                        'estado' => 's',
                        'lugar' => $punto
                    );
                }else{
                    $respuesta = array(
                        'estado' => 'sr',
                        'lugar' => 'No tiene ruta asignada'
                    );
                }
            }else{
                $respuesta = array(
                    'estado' => 'sd',
                    'lugar' => 'Distribuidor'
                );
            }        
            echo json_encode($respuesta);

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
                            'msg' => 'Metodo no definido'));
}

?>