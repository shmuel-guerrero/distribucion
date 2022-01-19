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
                $totalentrega = $db->select('SUM(monto_total) as total, COUNT(cliente_id) as cont')->from('tmp_egresos')->where('distribuidor_id',$usuario['id_empleado'])->where('distribuidor_estado','ENTREGA')->where('estado',3)->where('anulado !=',3)->fetch_first();
                $totaldevuelto = $db->select('SUM(monto_total) as total')->from('tmp_egresos')->where('distribuidor_id',$usuario['id_empleado'])->where('distribuidor_estado!=','ENTREGA')->where('estado',3)->where('anulado !=',3)->fetch_first();
                $totaldescuento = $db->select('SUM(descripcion_venta) as descuento')->from('tmp_egresos')->where('distribuidor_id',$usuario['id_empleado'])->where('distribuidor_estado','ENTREGA')->where('estado',3)->where('anulado !=',3)->fetch_first();
                
                /*$egresos = $db->select('e.*, c.nombre_factura AS razon_social')->from('tmp_egresos e')
                                    ->join('inv_clientes c','c.id_cliente = e.cliente_id', 'left')
                                    ->where('e.distribuidor_id',$usuario['id_empleado'])
                                    ->where('e.distribuidor_estado','ENTREGA')
                                    ->where('e.estado',3)->where('e.anulado !=',3)->fetch();*/
                
                $egresos = $db->select('e.id_egreso, c.cliente AS nombre_cliente, c.nombre_factura AS razon_social, c.nit as nit_ci, 
                                        e.distribuidor_fecha, e.distribuidor_hora, e.observacion,
                                        e.fecha_egreso, e.hora_egreso,
                                    egr.nro_registros, egr.monto_total')                  
                                    ->from('tmp_egresos e')
                                    ->join('inv_egresos egr', 'e.id_egreso = egr.id_egreso', 'left')
                                    ->join('inv_clientes c','c.id_cliente = e.cliente_id', 'left')
                                    ->where('e.distribuidor_id', $usuario['id_empleado'])
                                    ->where('e.distribuidor_estado', 'ENTREGA')
                                    ->where('e.estado',3)->where('e.anulado !=',3)->fetch();

                foreach($egresos as $nro5 => $egreso){
                    $detalles = $db->select('b.nombre_factura, a.cantidad as cantidad_uno, c.unidad, a.unidad_id, ROUND(IFNULL(a.cantidad / (IF(asig.cantidad_unidad is null,1,IF(asig.cantidad_unidad>0, asig.cantidad_unidad, 1))),0), 0)AS cantidad')
                    ->from('inv_egresos_detalles a')
                    ->join('inv_asignaciones asig', 'asig.producto_id = a.producto_id AND asig.unidad_id = a.unidad_id AND asig.visible = "s"')
                    ->join('inv_productos b','a.producto_id = b.id_producto')
                    ->join('inv_unidades c','a.unidad_id = c.id_unidad')
                    ->where('a.egreso_id',$egreso['id_egreso'])
                    ->where('asig.visible', 's')->fetch();
                    
                    $egresos[$nro5]['detalles'] = $detalles;
                    $egresos[$nro5]['cobro'] = 111;
                    $egresos[$nro5]['deuda_anterior'] = 222;
                }

                //se cierra transaccion
                $db->commit();

                if (count($egresos) > 0) {
                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 's',
                        'nro_clientes' => $totalentrega['cont'],
                        'total_entregas' => $totalentrega['total'],
                        'total_devueltos' => $totaldevuelto['total'],
                        'total_descuentos' => $totaldescuento['descuento'],
                        'total_cobros' => 111,
                        'total_cobros_anteriores' => 222,
                        'cliente' => $egresos
                    );                
                    // Devuelve los resultados
                    echo json_encode($respuesta);                    
                }else {
                    // Devuelve los resultados
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'Sin datos.',
                                            'nro_clientes' => 0,
                                            'total_entregas' => 0,
                                            'total_devueltos' => 0,
                                            'total_descuentos' => 0,
                                            'total_cobros' => 0,
                                            'total_cobros_anteriores' => 0));
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
                                'msg' => 'Datos no definidos'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}

?>