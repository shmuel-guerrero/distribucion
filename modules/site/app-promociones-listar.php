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

if(is_post()) {

    if (isset($_POST['id_user'])) {
        require config . '/database.php';
        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();
        
            $usuario = $db->from('sys_users')->where('id_user',$_POST['id_user'])->fetch_first();
            $alm = $usuario['almacen_id'];
            $productos = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.precio_sugerido, p.imagen, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, IFNULL(s.cantidad_egresos, 0) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
                        FROM inv_productos p
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
                            FROM inv_ingresos_detalles d
                            LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                            WHERE transitorio = 0 AND  i.almacen_id = $alm GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
                            FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                            WHERE e.almacen_id = $alm GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
                        LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id 
                        LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
                        LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
                                    FROM (SELECT *
                                        FROM inv_asignaciones q
                                        LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad AND q.visible = 's' WHERE q.visible = 's'
                                        ORDER BY u.unidad DESC) w 
                                    GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id
                        WHERE p.promocion = 'si' ")->fetch();
            $nro2 = 0;
            foreach($productos as $nro => $producto){

                if( ($producto['cantidad_ingresos'] - $producto['cantidad_egresos']) > 0 ){

                    $promo = $db->select('b.nombre as nombre_detalle, a.cantidad as cantidad_detalle, b.codigo as codigo_detalle, c.unidad as unidad_detalle')
                                ->from('inv_promociones a')
                                ->join('inv_productos b','a.producto_id = b.id_producto')
                                ->join('inv_unidades c','b.unidad_id = c.id_unidad')
                                ->where('id_promocion',$producto['id_producto'])->fetch();
                    $datos[$nro2] = array(
                        'id_producto' => (int)$producto['id_producto'],
                        'descripcion' => $producto['descripcion'],
                        //'imagen' => ($producto['imagen'] == '') ? url1 . imgs . '/image.jpg' : url1. productos . '/' . $producto['imagen'],
                        'imagen' => ($producto['imagen'] == '') ? imgs2 . '/image.jpg' : productos2 . '/' . $producto['imagen'],
                        'codigo' => $producto['codigo'],
                        'nombre' => $producto['nombre'],
                        'promocion' => $producto['promocion'],
                        'nombre_factura' => $producto['nombre_factura'],
                        'cantidad_minima' => $producto['cantidad_minima'],
                        'stock' => $producto['cantidad_ingresos']-$producto['cantidad_egresos'],
                        'categoria' => $producto['categoria'],
                        'precio_sugerido' => $producto['precio_sugerido'],
                        'precios' => array(),
                        'detalle' => $promo
                    );
                    array_push($datos[$nro2]['precios'],array('unidad' => $producto['unidad'],'precio' => $producto['precio_actual'],'cantidad' => 1));
                    $as = explode('&',$producto['unidade']);
                    $ac = explode('*',$producto['cantidad2']);
                    foreach($as as $nr => $a){
                        $b = explode(':',$as[$nr]);
                        $c = $ac[$nr];
                        if($b[0]!=''){
                            array_push($datos[$nro2]['precios'],array('unidad' => $b[0],'precio' => $b[1],'cantidad' => (int)$c));
                        }
                    }
                    $nro2 = $nro2 + 1;
                }
            }

            //se cierra transaccion
			$db->commit();

            if(count($datos) > 0){
                $respuesta = array(
                    'estado' => 's',
                    'producto' => $datos
                );
                echo json_encode($respuesta);
            }else{
                echo json_encode(array('estado' => 'n',
                                        'msg' => 'Sin registro de promociones.'));
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
        echo json_encode(array('estado' => 'n',
                                'msg' => 'Datos no definidos.'));
    }
}else{
    echo json_encode(array('estado' => 'n',
                            'msg' => 'Metodo no definido.'));
}
?>