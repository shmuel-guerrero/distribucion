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

        // Obtiene los datos
        $id_usuario = trim($_POST['id_user']);
       
		try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            // Obtiene los usuarios que cumplen la condicion
            $usuario = $db->query("SELECT u.id_user, u.username, e.id_empleado, password from sys_users u
            LEFT JOIN sys_empleados e ON u.persona_id = e.id_empleado WHERE  u.id_user = '$id_usuario' AND u.active = '1' limit 1")->fetch_first();

            // Verifica la existencia del usuario
            if ($usuario) {
                
                $distribuidor = $usuario['id_empleado'];
                $contrasenia_distri = $usuario['password'];
                $estado_validar_consulta_datos = false;
                
                require_once("app-no-entregas-service.php");
                //var_dump($estado_validar_consulta_datos);

                $devueltos = $productos_devueltos; 

                foreach ($devueltos as $key => $value) {
                    $datos = array();
                    $total = (($devueltos[$key]['cantidad'] > 0 && $devueltos[$key]['cantidad'] != '') ? $devueltos[$key]['cantidad'] : 0) * (($devueltos[$key]['precio'] > 0 && $devueltos[$key]['precio'] != '') ? $devueltos[$key]['precio'] : 0);
                    $total = number_format($total, 2, '.', '');
                    $cantidad = (($devueltos[$key]['cantidad'] > 0 && $devueltos[$key]['cantidad'] != '') ? $devueltos[$key]['cantidad'] : 0);
                    $cantidad = number_format($cantidad, 2, '.', '');
                    $precio = (($devueltos[$key]['precio_actual'] > 0 && $devueltos[$key]['precio_actual'] != '') ? $devueltos[$key]['precio_actual'] : 0);
                    $precio = number_format($precio, 2, '.', '');

                    $imagen = $db->query("SELECT * FROM inv_productos WHERE id_producto = '{$value['id_producto']}'")->fetch_first()['imagen'];
                    $devueltos[$key]['imagen'] = ($imagen == '') ? imgs2 . '/image.jpg' : productos2 . '/' . $imagen;
                    $devueltos[$key]['total'] = $total;
                    $devueltos[$key]['nombre'] = $devueltos[$key]['nombre_factura'];
                    $devueltos[$key]['cantidad'] = round(floatval($cantidad), 2);
                    $devueltos[$key]['precio'] = $precio;
                    
                    $datos[] = array('unidad_id'=> $devueltos[$key]['id_unidad'],
                                    'cantidad'=> $cantidad,
                                    'precio'=> $precio
                            );
                    $devueltos[$key]['precios_detalle'] = $datos;
                }

    			//se cierra transaccion
	    		$db->commit();

                if (count($devueltos) > 0) {                
                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 's',
                        'producto' => $devueltos
                    );                
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'No existen registro en la base de datos.'));
                }

            } else {
    			//se cierra transaccion
	    		$db->commit();

                // Devuelve los resultados
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
                                'msg' => 'Datos no definidos.'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}

?>