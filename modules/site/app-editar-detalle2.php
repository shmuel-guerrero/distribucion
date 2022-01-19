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
    if (isset($_POST['id_user']) && isset($_POST['id_detalle']) && isset($_POST['cantidad']) && isset($_POST['unidad_id'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';
   		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

		try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $empleado = $db->select('persona_id')->from('sys_users')->where('id_user',$_POST['id_user'])->fetch_first();
            $id_user = $empleado['persona_id'];
            $id_detalle = $_POST['id_detalle'];
            $cantidad = $_POST['cantidad'];
            $id_unidad = $_POST['unidad_id'];

            //buscamos el detalle y sus datos
            $detalle = $db->select('*, id_detalle as tmp_egreso_id')->from('inv_egresos_detalles')->where('id_detalle',$id_detalle)->fetch_first();
            $id_egreso = $detalle['egreso_id'];

            //detalles del egreso
            $egreso = $db->select('b.*, b.fecha_egreso as distribuidor_fecha , b.hora_egreso as distribuidor_hora, b.almacen_id as distribuidor_estado, b.almacen_id as distribuidor_id, b.estadoe as estado')->from('inv_egresos b')->where('b.id_egreso',$id_egreso)->fetch_first();
            
            //detalles productos
            $producto = $db->select('*')->from('inv_productos')->where('id_producto',$detalle['producto_id'])->fetch_first();

            $precio = $detalle['precio'];
            $monto_sub_total = $cantidad * $precio;
            $cantidad_detalle = $detalle['cantidad'] / cantidad_unidad($db, $detalle['producto_id'], $id_unidad);

            if($id_unidad == $detalle['unidad_id']){

                //se valida la que el nuevo dato es menor al dato que se tiene en la base de datos
                if($cantidad < $cantidad_detalle){
                    //reducimos la cantidad y el precio
                    
                    $monto_total = $egreso['monto_total'] - (($cantidad_detalle - $cantidad) * $precio);
                    $aux = $cantidad * cantidad_unidad($db, $detalle['producto_id'], $id_unidad);
                    
                    $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $monto_total));
                    $db->where('id_detalle',$id_detalle)->update('inv_egresos_detalles',array('cantidad' => $aux));
                    //datos tmp
                    $egreso['monto_total'] = (($cantidad_detalle - $cantidad) * $precio);
                    $egreso['nro_registros'] = 1;
                    $egreso['distribuidor_fecha'] = date('Y-m-d');
                    $egreso['distribuidor_hora'] = date('H:i:s');
                    $egreso['distribuidor_estado'] = 'DEVUELTO';
                    $egreso['distribuidor_id'] = $id_user;
                    $egreso['estado'] = 3;
                    $id = $db->insert('tmp_egresos', $egreso);

                    $detalle['tmp_egreso_id'] = $id;
                    $detalle['cantidad'] = $detalle['cantidad'] - ($cantidad * cantidad_unidad($db, $detalle['producto_id'], $id_unidad));
                    $detalle['unidad_id'] = $id_unidad;
                    $detalle['precio'] = $precio;
                    $id = $db->insert('tmp_egresos_detalles', $detalle);

                }elseif($cantidad == $detalle['cantidad']){
                    //no se realiza ningun cambio
                }
            }else{
                $monto_total_ant = ($detalle['cantidad'] / cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id'])) * $precio;
                $otro_precio = precio_unidad($db, $detalle['producto_id'], $id_unidad);
                $otra_unidad = cantidad_unidad($db, $detalle['producto_id'], $id_unidad);
                $monto_total = $egreso['monto_total'] - $monto_total_ant + ($cantidad * $otro_precio);
                $db->where('id_egreso',$id_egreso)->update('inv_egresos',array('monto_total' => $monto_total));
                $db->where('id_detalle',$id_detalle)->update('inv_egresos_detalles',array('cantidad' => $cantidad*$otra_unidad, 'precio' => $otro_precio, 'unidad_id' => $id_unidad));

                //detales de tmp
                $egreso['monto_total'] = $cantidad * $otro_precio;
                $egreso['nro_registros'] = 1;
                $egreso['distribuidor_fecha'] = date('Y-m-d');
                $egreso['distribuidor_hora'] = date('H:i:s');
                $egreso['distribuidor_estado'] = 'DEVUELTO';
                $egreso['distribuidor_id'] = $id_user;
                $egreso['estado'] = 3;
                $id = $db->insert('tmp_egresos', $egreso);

                $detalle['tmp_egreso_id'] = $id;
                $detalle['cantidad'] = $detalle['cantidad'] - ($cantidad * $otra_unidad);
                $detalle['unidad_id'] = $id_unidad;
                $detalle['precio'] = $otro_precio;
                $id = $db->insert('tmp_egresos_detalles', $detalle);

            }

            //se cierra transaccion
			$db->commit();

            //validar que se realizao accion sobre la base de datos
            if (($db->affectedrows()) > 0) {                
                $respuesta = array(
                    'estado' => 's'
                );
                echo json_encode($respuesta);
            }else {
                // Devuelve los resultados
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'Datos guardados parcialmente, verificar.'));
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
                                'msg' => 'datos no definidos.'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido'));
}

?>