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
    if (isset($_POST['id_egreso']) && isset($_POST['id_user']) && isset($_POST['id_cliente'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
       //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $cliente_id = $_POST['id_cliente'];
        $egreso_id = $_POST['id_egreso'];

        try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            // Obtiene los usuarios que cumplen la condicion
            $usuario = $db->from('sys_users')->join('sys_empleados','persona_id = id_empleado')->where('id_user',$_POST['id_user'])->fetch_first();
            $emp = $usuario['id_empleado'];

            $productos = $db->query('SELECT DISTINCT b.*,c.id_producto, c.promocion, c.codigo, c.nombre_factura as nombre, 
                                        c.precio_sugerido, c.precio_sugerido as stock, d.categoria, e.unidad, c.unidad_id AS unidad_idp, 
                                        b.unidad_id AS unidad_ide, e.unidad AS total, a.id_egreso
                                        FROM gps_asigna_distribucion g
                                        LEFT JOIN inv_egresos a ON g.grupo_id = a.grupo
                                        LEFT JOIN inv_egresos_detalles b ON a.id_egreso = b.egreso_id
                                        LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
                                        LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
                                        LEFT JOIN inv_unidades e ON c.unidad_id = e.id_unidad
                                        WHERE a.estadoe = 3                                    
                                        AND b.promocion_id != 1                                    
                                        and a.id_egreso = ' . $egreso_id
                                        )->fetch(); // AND a.fecha_egreso <= CURDATE()'

            $total = number_format(0, 2, '.', '');
            $egresos = array();
            if($productos){
                foreach ($productos as $nro => $producto) {
                    if($producto['unidad_idp']!=$producto['unidad_ide']){
                        $unidad = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad AND a.visible = "s"')->where('producto_id',$producto['id_producto'])->where('unidad_id',$producto['unidad_ide'])->where('a.visible',"s")->fetch_first();
                        $productos[$nro]['unidad'] = $unidad['unidad'];
                        $productos[$nro]['precio'] = $unidad['otro_precio'];
                        $productos[$nro]['stock'] = $producto['cantidad'];                    //$productos[$nro]['cantidad'] = $producto['cantidad'];
                        $productos[$nro]['cantidad'] = $producto['cantidad']/$unidad['cantidad_unidad'];
                        $productos[$nro]['total'] = number_format(($unidad['otro_precio'] * ($producto['cantidad']/$unidad['cantidad_unidad'])), 2, '.', '');
                        $total = $total + $productos[$nro]['total'];
                        $productos[$nro]['id_detalle'] = (int)$producto['id_detalle'];
                        $productos[$nro]['egreso_id'] = (int)$producto['egreso_id'];
                    }
                    else{
                        $productos[$nro]['stock'] = $producto['cantidad'];
                        $productos[$nro]['total'] = number_format(($producto['precio'] * $producto['cantidad']), 2, '.', '');
                        $total = $total + $productos[$nro]['total'];
                        $productos[$nro]['id_detalle'] = (int)$producto['id_detalle'];
                        $productos[$nro]['egreso_id'] = (int)$producto['egreso_id'];
                    }
                    $egresos[$nro] = $productos[$nro]['id_egreso'];
                }
                
                //se cierra transaccion
				$db->commit();
                if (count($egresos) > 0) {                    
                    $respuesta = array(
                        'estado' => 'd',
                        'total' => number_format($total, 2, '.', ''),
                        'egresos' => $egresos,
                        'cliente' => $productos
                    );
                    echo json_encode($respuesta);
                }else {
                    // Instancia el objeto
                    $respuesta = array('estado' => 'n',
                                         'msg' => 'No existe productos registrados.');
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }
            }else{
                //se cierra transaccion
				$db->commit();

                // Instancia el objeto
                $respuesta = array('estado' => 'n',
                                    'msg' => 'No existe productos registrados.');
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
                                'msg' => 'Datos no definidos.'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido'));
}

?>