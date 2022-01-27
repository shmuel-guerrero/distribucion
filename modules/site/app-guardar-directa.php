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


// Verifica si es una peticion post
if (is_post()) {

	// Verifica la existencia de los datos enviados
    if (isset($_POST['id_user']) && isset($_POST['cantidad']) && isset($_POST['id_unidad']) && isset($_POST['id_producto']) && isset($_POST['precio']) && isset($_POST['monto_total'])){ 
        
        require config . '/database.php';
   		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        $id_user        = trim($_POST['id_user']);
        $cantidad       = trim($_POST['cantidad']);
        $unidad         = trim($_POST['id_unidad']);
        $id_producto    = trim($_POST['id_producto']);
        $precio         = trim($_POST['precio']);
        $total          = trim($_POST['monto_total']);
        $id_cliente     = trim($_POST['codigo_cliente']);
        $token = (isset($_POST['token'])) ? $_POST['token'] : '';

        try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $usuario = $db->query("select * from sys_users LEFT JOIN sys_empleados ON persona_id = id_empleado where id_user = '$id_user' and active = '1' limit 1")->fetch_first();

            if($id_cliente != ''){
                $nombre = "DISTRIBUIDOR - " . $usuario['nombres'];
                $nit = 0;
            }else{
                $cliente = $db->query("SELECT * FROM inv_clientes WHERE id_cliente = '$id_cliente'")->fetch_first();
                if($cliente){
                    $nombre = $cliente['nombre_factura'];
                    $nit = $cliente['nit'];
                }else{
                    $nombre = $usuario['nombres'];
                    $nit = 0;
                }
            }

            /*envia datos a funcion para validar stock suficiente de los productos devueltos
            *validar_stock_devueltos($variable_conexion_base_datos, $id_producto, $cantidad, $unidad, $id_empleado_distribuidor)
            */
            //if (validar_stock_devueltos($db, $id_producto, $cantidad, $unidad, $usuario['id_empleado'])) {
            if (true) {
                
                //Obtiene numero de movimiento de la nota de remision
                $nro_factura = $db->query("select count(id_egreso) + 1 as nro_factura from inv_egresos where tipo = 'Venta' and provisionado = 'S'")->fetch_first();
                $nro_factura = $nro_factura['nro_factura'];
    
                if($cantidad > 0){

                    //obtiene id de alamacen principal
                    $id_almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first()['id_almacen'];

                    //se prepara las datos con el id del distribuidor de tipo VENTA
                    $nota = array(
                        'fecha_egreso' => date('Y-m-d'),
                        'hora_egreso' => date('H:i:s'),
                        'tipo' => 'Venta',
                        'provisionado' => 'S',
                        'descripcion' => 'VENTA DIRECTA de productos con productos del distribuidor',
                        'nro_factura' => $nro_factura,
                        'nro_autorizacion' => '',
                        'codigo_control' => '',
                        'fecha_limite' => '0000-00-00',
                        'monto_total' => $total,
                        'descuento_porcentaje' => 0,
                        'descuento_bs' => 0,
                        'monto_total_descuento' => $total,
                        'nit_ci' => $nit,
                        'nombre_cliente' => strtoupper($nombre),
                        'nro_registros' => 1,
                        'dosificacion_id' => 0,
                        'almacen_id' => $id_almacen,
                        'cobrar' => '',
                        'observacion' => '',
                        'empleado_id' => $usuario['id_empleado']
                    );
    
                    // Guarda la informacion
                    $egreso_id = $db->insert('inv_egresos', $nota);
    
                    $nota['distribuidor_fecha'] = date('Y-m-d');
                    $nota['id_egreso'] = $egreso_id;
                    $nota['distribuidor_hora'] = date('H:i:s');
                    $nota['distribuidor_estado'] = 'VENTA';
                    $nota['distribuidor_id'] = $usuario['id_empleado'];
                    $nota['estado'] = 3;
                    $egreso['accion'] = 'Venta';
                    $id = $db->insert('tmp_egresos', $nota);
    
                    // buscamos el producto
                    $producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();
                    // procesamos datos para formar el detalle
                    $cantidad_u = $cantidad * cantidad_unidad($db, $id_producto, $unidad);
    
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote='';
                    $CantidadAux=$cantidad_u;
                    $Detalles=$db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$id_producto' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                    foreach($Detalles as $Fila=>$Detalle):
                        if($CantidadAux>=$Detalle['lote_cantidad']):
                            $Datos=array(
                                'lote_cantidad'=>0,
                            );
                            $Cant=$Detalle['lote_cantidad'];
                        elseif($CantidadAux>0):
                            $Datos=array(
                                'lote_cantidad'=>$Detalle['lote_cantidad']-$CantidadAux,
                            );
                            $Cant=$CantidadAux;
                        else:
                            break;
                        endif;
                        $Condicion=array(
                                'id_detalle'=>$Detalle['id_detalle'],
                        );
                        $db->where($Condicion)->update('inv_ingresos_detalles',$Datos);
                        $CantidadAux=$CantidadAux-$Detalle['lote_cantidad'];
                        $Lote.=$Detalle['lote'].'-'.$Cant.',';
                    endforeach;
                    $Lote=trim($Lote,',');
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $detalle = array(
                        'cantidad' => $cantidad_u,
                        'precio' => $precio,
                        'unidad_id' => $unidad,
                        'descuento' => 0,
                        'producto_id' => $id_producto,
                        'egreso_id' => $egreso_id,
                        'lote'=>$Lote,
                        'promocion_id' => 0
                    );
    
                    // Guarda la informacion
                    $ide = $db->insert('inv_egresos_detalles', $detalle);
    
                    $detalle['tmp_egreso_id'] = $id;
                    $detalle['id_detalle'] = $ide;
                    $db->insert('tmp_egresos_detalles', $detalle);

                    //se guarda proceso u(update),c(create), r(read),d(delet), cr(cerrar), a(anular)
                    save_process($db, 'c', '?/site/app-guardar-directa', 'una venta directa', $egreso_id, $id_user, $token); 
    
                    //se cierra transaccion
                    $db->commit();
    
                    if ($egreso_id) {
                        $respuesta = array(
                            'estado' => 's'
                        );
                        echo json_encode($respuesta);                    
                    }else{
                        // Instancia el objeto
                        echo json_encode(array('estado' => 'n',
                                                'msg' => 'Se realizo acciones parcialmente.'));                   
                    }
                }else{
                    //se cierra transaccion
                    $db->commit();
    
                    // Instancia el objeto
                    $respuesta = array('estado' => 'n', 
                                        'msg' => 'La cantidad debe ser mayor a cero');
    
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }
            }else {
                 //se cierra transaccion
                 $db->commit();
    
                 // Instancia el objeto
                 $respuesta = array('estado' => 'n', 
                                     'msg' => 'Stock insuficiente');
 
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
        // Instancia el objeto
        $respuesta = array('estado' => 'n',
                            'msg' => 'Metodo no definido.');

        // Devuelve los resultados
        echo json_encode($respuesta);
	}
} else {
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
?>