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
    if (isset($_POST['producto_id'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $id_producto = $_POST['producto_id'];   

		try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $tipo_precio1 = $db->select('precio_actual as precio, b.unidad, unidad_id')->from('inv_productos')->join('inv_unidades b','unidad_id = b.id_unidad')->where('id_producto',$id_producto)->fetch_first();
            // Obtiene los usuarios que cumplen la condicion
            $tipo_precio2 = $db->select('a.*, b.unidad')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id = b.id_unidad AND a.visible = "s"')->where('producto_id',$id_producto)->where('a.visible','s')->fetch();

            $tipos_precios = array();
            $nro = 0;

            $tipos_precios[0] = array(
                'unidad' => $tipo_precio1['unidad'],
                'id_unidad' => (int)$tipo_precio1['unidad_id'],
                'cantidad' => '1',
                'precio' => $tipo_precio1['precio']
            );
            
            foreach($tipo_precio2 as $tipo_precio){
                $nro = $nro + 1;
                $tipos_precios[$nro] = array(
                    'unidad' => $tipo_precio['unidad'],
                    'id_unidad' => (int)$tipo_precio['unidad_id'],
                    'cantidad' => $tipo_precio['cantidad_unidad'],
                    'precio' => $tipo_precio['otro_precio']
                );
            }

            //se cierra transaccion
            $db->commit();

            if (count($tipos_precios)) {                
                $respuesta = array(
                    'estado' => 's',
                    'tipos' => $tipos_precios
                );
                echo json_encode($respuesta);
            }else {
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'datos no definidos'));
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
                                'msg' => 'datos no definidos'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}

?>