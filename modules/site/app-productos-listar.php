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

    if (isset($_POST['pagina']) && isset($_POST['filtro']) && isset($_POST['id_user'])) {
        require config . '/database.php';
  		//Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

        try {
            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            $item = 10;
            $pagina = $item*($_POST['pagina']);
            $busqueda = $_POST['filtro'];

            $usuario = $db->select('*')->from('sys_users a')->join('sys_empleados b','a.persona_id = b.id_empleado')->where('a.id_user',$_POST['id_user'])->fetch_first();
            $id_almacen = $usuario['almacen_id'];
            $Fecha = date('Y-m-d');
            
            if($usuario['fecha'] != date('Y-m-d')){

                $id_categoria = $_POST['id_categoria'];
                $productos2 = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0) + IFNULL(sp.cantidad_promocion, 0) + IFNULL(spr.cantidad_venta_promo, 0)) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
                        FROM inv_productos p
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
                            FROM inv_ingresos_detalles d
                            LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                            WHERE  i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
                            FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                            WHERE e.almacen_id = '$id_almacen' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_venta_promo
                                FROM inv_egresos_detalles d 
                                LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                                LEFT JOIN inv_productos pr ON pr.id_producto = d.promocion_id
                                WHERE e.almacen_id = '$id_almacen' AND  d.promocion_id > 2 AND e.anulado != 3 AND pr.fecha_limite < CURDATE() GROUP BY d.producto_id) AS spr ON spr.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad*a.cantidad) AS cantidad_promocion
                                FROM inv_ingresos_detalles a 
                                LEFT JOIN inv_ingresos b on b.id_ingreso = a.ingreso_id 
                                INNER JOIN inv_promociones d ON d.id_promocion = a.producto_id
                                INNER JOIN inv_productos c ON c.id_producto = d.id_promocion
                                INNER JOIN inv_productos e ON e.id_producto = d.producto_id
                                WHERE  b.almacen_id = '$id_almacen' AND e.fecha_limite > CURDATE() GROUP BY d.producto_id) AS sp ON sp.producto_id = p.id_producto
                        LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id 
                        LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
                        LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
                                FROM (SELECT *
                                    FROM inv_asignaciones q
                                    LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad AND q.visible = 's' WHERE q.visible = 's'
                                    ORDER BY u.unidad DESC) w
                                GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id 
                        WHERE ('$Fecha'<=p.fecha_limite OR p.fecha_limite='1000-01-01') AND  p.promocion != 'si' AND p.eliminado = 0
                        AND (p.codigo like '%" . $busqueda . "%' OR p.nombre_factura like '%" . $busqueda . "%' OR p.nombre like '%" . $busqueda . "%' OR c.categoria like '%" . $busqueda . "%') ")->fetch();

                $nroProducts = $db->affected_rows;
                $nroPaginas= ceil($nroProducts / $item);
                $productos = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, 
                                        IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0) + IFNULL(sp.cantidad_promocion, 0) + IFNULL(spr.cantidad_venta_promo, 0)) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
                        FROM inv_productos p
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
                            FROM inv_ingresos_detalles d
                            LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
                            WHERE i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
                            FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                            WHERE e.almacen_id = '$id_almacen' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_venta_promo
                                FROM inv_egresos_detalles d 
                                LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                                LEFT JOIN inv_productos pr ON pr.id_producto = d.promocion_id
                                WHERE e.almacen_id = '$id_almacen' AND  d.promocion_id > 2 AND e.anulado != 3 AND pr.fecha_limite < CURDATE() GROUP BY d.producto_id) AS spr ON spr.producto_id = p.id_producto
                        LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad*a.cantidad) AS cantidad_promocion
                                FROM inv_ingresos_detalles a 
                                LEFT JOIN inv_ingresos b on b.id_ingreso = a.ingreso_id 
                                INNER JOIN inv_promociones d ON d.id_promocion = a.producto_id
                                INNER JOIN inv_productos c ON c.id_producto = d.id_promocion
                                INNER JOIN inv_productos e ON e.id_producto = d.producto_id
                                WHERE b.almacen_id = '$id_almacen' AND e.fecha_limite > CURDATE() GROUP BY d.producto_id) AS sp ON sp.producto_id = p.id_producto
                        LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
                        LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
                        FROM (SELECT *
                                FROM inv_asignaciones q
                                    LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad AND q.visible = 's' WHERE q.visible = 's'
                                            ORDER BY u.unidad DESC) w GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id
                                            WHERE ('$Fecha'<=p.fecha_limite OR p.fecha_limite='1000-01-01')  AND  p.promocion != 'si' AND p.eliminado = 0
                                            AND (p.codigo like '%" . $busqueda . "%' OR p.nombre_factura like '%" . $busqueda . "%' OR p.nombre like '%" . $busqueda . "%' OR c.categoria like '%" . $busqueda . "%') ")->limit( $pagina, 0 )->fetch();
                $datos = array();

                foreach($productos as $nro => $producto){                        

                        if($producto['promocion']=='si' && ($producto['cantidad_ingresos']-$producto['cantidad_egresos']) > 0) {
                            $producto['promocion']='EN PROMOCIÓN';
                            $promociones = $db->select('a.precio, a.cantidad, b.nombre, b.nombre_factura, c.unidad')
                                                ->from('inv_promociones a')
                                                ->join('inv_productos b','a.producto_id = b.id_producto')
                                                ->join('inv_unidades c','b.unidad_id = c.id_unidad')
                                                ->where('a.id_promocion',$producto['id_producto'])
                                                ->fetch();
                            if (count($promociones) > 0) {
                                foreach($promociones as $nrp => $promocion){
                                    $promo[$nrp] = array(
                                        'promocion_nombre' => $promocion['nombre_factura'],
                                        'promocion_cantidad' => $promocion['cantidad'],
                                        'promocion_unidad' => $promocion['unidad'],
                                        'promocion_precio' => $promocion['precio']
                                    );
                                }   
                            } else {
                                $promocion = '';
                                $promo = array();
                            }
                            //  var_dump($promo);
                        } else {
                            $promocion = '';
                            $promo = array();
                        }
                        if(!$producto['promocion']){
                            $promocion = '';
                            $promo = array();
                        }
                      
                        
                        $datos[$nro] = array(
                            'id_producto' => (int)$producto['id_producto'],
                            'descripcion' => $producto['descripcion'],
                            //'imagen' => ($producto['imagen'] == '') ? url1 . imgs . '/image.jpg' : url1. productos . '/' . $producto['imagen'],
                            'imagen' => ($producto['imagen'] == '') ? imgs2 . '/image.jpg' : productos2 . '/' . $producto['imagen'],
                            'codigo' => $producto['codigo'],
                            'nombre' => $producto['nombre_factura'],
                            'promocion' => $producto['promocion'],
                            'nombre_factura' => $producto['nombre_factura'],
                            'cantidad_minima' => $producto['cantidad_minima'],
                            'stock' => ( ($producto['cantidad_ingresos']-$producto['cantidad_egresos']) > 0 )?($producto['cantidad_ingresos']-$producto['cantidad_egresos']):0,
                            'categoria' => $producto['categoria'],
                            'precio_sugerido' => $producto['precio_sugerido'],
                            'precios' => array(),
                            'promociones' => $promo
                        );
                        array_push($datos[$nro]['precios'],array('unidad' => $producto['unidad'],'precio' => $producto['precio_actual'],'cantidad' => 1));

                        $as = explode('&',$producto['unidade']);
                        $ac = explode('*',$producto['cantidad2']);
                        foreach($as as $nr => $a){
                            $b = explode(':',$as[$nr]);
                            $c = $ac[$nr];
                            if($b[0]!=''){
                                array_push($datos[$nro]['precios'],array('unidad' => $b[0],'precio' => $b[1],'cantidad' => (int)$c));
                            }
                        }                        
                }

                //se cierra transaccion
				$db->commit();
                
                if(count($productos) > 0){
                    $respuesta = array(
                        'nro_products' => $nroProducts,
                        'nro_pages' => $nroPaginas ,
                        'page' => (int)$_POST['pagina'],
                        'estado' => 's',
                        'producto' => $datos
                    );
                    echo json_encode($respuesta);
                }else{
                    echo json_encode(array('estado' => 'n',
                                            'msg' => 'No se encuentran productos'));
                }
            }else{
                //se cierra transaccion
				$db->commit();
                echo json_encode(array('estado' => 'Inactivo'));
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