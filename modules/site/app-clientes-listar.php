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
            $usuario = $db->from('inv_clientes')->fetch_first();

            // Verifica la existencia del usuario
            if ($usuario) {

                $id_almacen=$usuario['almacen_id'];

                // Obtiene los productos
                $clientes = $db->select('id_cliente, cliente, nombre_factura, nit, telefono, direccion, descripcion, imagen, ubicacion, ubicacion as latitud, ubicacion as longitud')->from('inv_clientes')->fetch();

                // Reformula los productos
                foreach ($clientes as $nro => $cliente) {
                    $clientes[$nro]['imagen'] = ($cliente['imagen'] == '') ? imgs . '/image.jpg' : tiendas . '/' . $cliente['imagen'];
                    $a = explode(',',$cliente['ubicacion']);
                    $clientes[$nro]['latitud'] = (float)$a[0];
                    $clientes[$nro]['longitud'] = (float)$a[1];
                }

                //se cierra transaccion
				$db->commit();

                if (count($clientes) > 0) {                    
                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 's',
                        'cliente' => $clientes
                    );                    
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n', 'msg' => 'No se tiena clientes registrados'));
                }

            } else {
                
                //se cierra transaccion
				$db->commit();
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n', 'msg' => 'No se tiena clientes registrados.'));
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
        echo json_encode(array('estado' => 'n', 'msg' => 'Datos no definidos'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n', 'msg' => 'Metodo no definido'));
}

?>