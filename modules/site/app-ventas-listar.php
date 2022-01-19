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
            $usuario = $db->query("select id_user, id_empleado from sys_users LEFT JOIN sys_empleados ON persona_id = id_empleado where  id_user = '$id_usuario' and active = '1' limit 1")->fetch_first();

            // Verifica la existencia del usuario
            if ($usuario) {
                $total = $db->select('SUM(monto_total) as total, COUNT(cliente_id) as cont')->from('inv_egresos')->where('empleado_id',$usuario['id_empleado'])->where('estadoe',2)->where('fecha_egreso',date('Y-m-d'))->where('anulado',0)->fetch_first();
                $auxf = date('Y-m-d');
                $egresos = $db->select('e.*, c.nombre_factura, c.cliente')->from('inv_egresos e')->join('inv_clientes c','e.cliente_id = c.id_cliente','left')->where('e.empleado_id',$usuario['id_empleado'])->where('e.estadoe',2)->where('e.fecha_egreso',$auxf)->where('e.anulado',0)->fetch();
                
                foreach($egresos as $nro5 => $egreso){
                    $detalles = $db->select('b.nombre_factura, b.nombre as unidad, a.unidad_id, a.cantidad, a.producto_id')->from('inv_egresos_detalles a')->join('inv_productos b','a.producto_id = b.id_producto')->where('promocion_id!=',1)->where('a.egreso_id',$egreso['id_egreso'])->fetch();
                    foreach($detalles as $nro6 => $detalle){
                        $detalles[$nro6]['cantidad'] = (int)($detalle['cantidad']/cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']));
                        $detalles[$nro6]['unidad'] = nombre_unidad($db,$detalle['unidad_id']);
                    }
                    $egresos[$nro5]['descripcion_venta'] = $detalles;
                    $egresos[$nro5]['nombre_cliente'] = ($egresos[$nro5]['cliente'] != '' && $egresos[$nro5]['cliente'] != null ) ? $egresos[$nro5]['cliente'] : 'SIN DATO';
                }

                //se cierra transaccion
				$db->commit();
                
                if ($egresos) {                    
                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 's',
                        'nro_clientes' => $total['cont'],
                        'total' => $total['total'],
                        'cliente' => $egresos
                    );
                    
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'Uusario no registrado.',
                                            'nro_clientes' => 0,
                                            'total' => 0,
                                            'cliente' => 0));
                }
            } else {
                //se cierra transaccion
				$db->commit();
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'Uusario no registrado.'));
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