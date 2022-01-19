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

                $total = $db->select('SUM(monto_total) as total, COUNT(cliente_id) as cont')->from('tmp_egresos')->where('distribuidor_id',$usuario['id_empleado'])->where('distribuidor_estado','ENTREGA')->where('estado',3)->fetch_first();
                $productos_auxiliar_sin_uso = $db->query('SELECT GROUP_CONCAT(b.cantidad, "-", b.unidad_id SEPARATOR "|" ) AS cantidades, GROUP_CONCAT(b.precio SEPARATOR "|" ) AS precios, 
                    SUM(a.monto_total) AS m_total,  c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
                    FROM tmp_egresos a
                    LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
                    LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
                    LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
                    WHERE a.estado = 3 AND a.distribuidor_id = '.$usuario['id_empleado'].' 
                    AND a.distribuidor_estado != "ENTREGA" AND a.distribuidor_estado != "VENTA" and b.promocion_id != 1
                    GROUP BY c.id_producto ORDER BY d.categoria')->fetch();

                $productos = $db->query('SELECT GROUP_CONCAT(A.cantidad_detalle, "-", A.unidad_id SEPARATOR "|" ) AS cantidades, 
                        GROUP_CONCAT(A.precio SEPARATOR "|" ) AS precios, A.nombre_factura, 
                    A.codigo, A.categoria, A.imagen, A.unidad_producto, A.precio, A.id_producto
                    FROM  (SELECT SUM(b.cantidad) AS cantidad_detalle,
                    b.*,c.id_producto, c.nombre, c.nombre_factura, c.imagen, c.codigo, c.unidad_id AS unidad_producto, d.categoria
                    FROM tmp_egresos a
                    LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
                    LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
                    LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
                    WHERE a.estado = 3 AND a.distribuidor_id = '.$usuario['id_empleado'].' 
                    AND a.distribuidor_estado != "ENTREGA" AND a.distribuidor_estado != "VENTA" and b.promocion_id != 1
                    GROUP  BY c.id_producto, b.unidad_id ORDER BY c.id_producto ) A 
                    GROUP BY A.id_producto')->fetch();

                $devueltos = array();

                foreach($productos as $nro => $detalle){
                    $cantidades = 0;
                    $precio_total = 0;
                    $canti = explode('|', $detalle['cantidades']);
                    $precios = explode('|', $detalle['precios']);
                    
                    $detalle_precios = array();
                    if(count($canti) > 1){
                        //si tiene mas unidades
                        $importe_t = 0;
                        $cantidad_total = 0;
                        foreach ($canti as $nro2 => $uni) {
                            $parte = explode('-', $uni);
                            $unidad = $parte[1];
                            $cantid = $parte[0];

                            $cantidades = $cantidades + ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad)) ;
                            $importe = (int)($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[$nro2];
                            $importe_t = $importe_t + $importe ;
                            $precio_total = $precio_total + ($precios[$nro2] * $cantid);

                            $detalle_precios[$nro2] = array(
                                                'unidad_id' => $unidad,
                                                'cantidad' => $cantid,
                                                'precio' => $precios[$nro2]    
                                                );
                        }
                    }else{
                        $parte = explode('-', $canti[0]);
                        $unidad = $parte[1];
                        $cantid = $parte[0];
                        $cantidades = $cantidades + $cantid;
                        $importe = (int)($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[0];
                        $importe_t = $importe;
                        $precio_total = $precio_total + ($precios[0] * $cantid);

                        $detalle_precios[0] = array(
                            'unidad_id' => $unidad,
                            'cantidad' => $cantid,
                            'precio' => $precios[0]    
                            );
                    }
                    $tipo_unidad = nombre_unidad($db,$detalle['unidad_producto']);

                    //Obtiene las ventas directas del distribuidor                                        
                    $venta_directa = $db->query("SELECT IFNULL(SUM(b.cantidad/(IF(asg.cantidad_unidad is null,1,asg.cantidad_unidad))), 0) AS suma FROM tmp_egresos a  
                                        LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
                                        LEFT JOIN inv_asignaciones asg ON asg.producto_id = b.producto_id  AND asg.visible = 's'
                                        WHERE a.estadoe = 0 AND a.distribuidor_id = '{$usuario['id_empleado']}' 
                                        AND b.producto_id = '{$detalle['id_producto']}' AND a.distribuidor_estado = 'VENTA'")->fetch_first()['suma'];
                    
                    // Verifica elemento 
                    $sum = ($venta_directa > 0) ? round($venta_directa, 2) : 0;

                    $devueltos[$nro]['nombre'] = $detalle['nombre_factura'];
                    $devueltos[$nro]['codigo'] = $detalle['codigo'];
                    $devueltos[$nro]['id_producto'] = $detalle['id_producto'];
                    $devueltos[$nro]['categoria'] = $detalle['categoria'];
                    $devueltos[$nro]['imagen'] = ($detalle['imagen'] == '') ? imgs2 . '/image.jpg' : productos2 . '/' . $detalle['imagen'];
                    $devueltos[$nro]['cantidad'] = round(($cantidades - $sum),0);
                    $devueltos[$nro]['unidad'] = $tipo_unidad;
                    $devueltos[$nro]['id_unidad'] = $detalle['unidad_producto'];
                    $devueltos[$nro]['precio'] = number_format(($detalle['precio']), 2);
                    $devueltos[$nro]['total'] = number_format(($precio_total), 2);
                    $devueltos[$nro]['precios_detalle'] = $detalle_precios;
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
                                            'msg' => 'Usuario no registrado.'));
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