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

// Verifica si es una peticion post
if (is_post()) {
    // Verifica la existencia de los datos enviados

    if (isset($_POST['id_user']) && isset($_POST['id_meta'])) {
        require config . '/database.php';
        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $id_user        = $_POST['id_user'];
        $tipo_meta      = $_POST['id_meta'];

        try {

            //Se abre nueva transacci贸n.
            $db->autocommit(false);
            $db->beginTransaction();

            $empleado = $db->select('*')->from('sys_users a')->join('sys_empleados b', 'b.id_empleado = a.persona_id')->where('a.id_user', $id_user)->fetch_first();
            if ($empleado) {

                if ($tipo_meta == 1) { /// META VENDEDOR
                    require config . '/poligono.php';
                    $hoy = date('Y-m-d');
                    $vendedores = $db->select('a.id_meta,a.monto, a.fecha_inicio, a.fecha_fin, a.empleado_id, b.nombres as nombre')
                        ->from('inv_meta a')
                        ->join('sys_empleados b', 'b.id_empleado = a.empleado_id')
                        ->where('a.empleado_id', $empleado['id_empleado'])
                        ->where('a.fecha_inicio<=', date('Y-m-d'))
                        ->where('a.fecha_fin>=', date('Y-m-d'))
                        ->fetch();
                    foreach ($vendedores as $nrop => $vendedor) {
                        $ini = ($vendedor['fecha_inicio']) ? $vendedor['fecha_inicio'] : 'no tiene';
                        $fin = ($vendedor['fecha_fin']) ? $vendedor['fecha_fin'] : 'no tiene';
                        $meta = ($vendedor['monto']) ? $vendedor['monto'] : 'no tiene';
                        if ($ini != 'no tiene' && $fin != 'no tiene' && $meta != 'no tiene') {

                            $total = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total
                                                FROM inv_egresos
                                                WHERE anulado != 3
                                                AND tipo='Venta'
                                                AND empleado_id='{$empleado['id_empleado']}'
                                                AND fecha_egreso
                                                BETWEEN '{$ini}' AND '{$fin}'")->fetch_first();
                            $total = ($total['total']) ? $total['total'] : 0;
                            $total = ($total) ? $total : 0;
                            $porcen = ($total * 100) / $meta;

                            // VENTAS DEL DIA
                            $ventas_del_dia = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM inv_egresos WHERE anulado != 3 AND tipo='Venta' AND empleado_id='{$empleado['id_empleado']}' AND fecha_egreso = '{$hoy}'")->fetch_first();
                            $ventas_del_dia = ($ventas_del_dia['total']) ? $ventas_del_dia['total'] : 0;
                            $ventas_dia = number_format($ventas_del_dia, 2);

                            // para sacar el los clientes asignados
                            $total_clientes = 0;
                            $rutas = $db->select('*')->from('gps_rutas')->where('dia', date('w'))->where('empleado_id', $empleado['id_empleado'])->fetch();
                            $rutas1 = 0;
                            $rutas2 = 0;
                            foreach ($rutas as $key => $ruta) {
                                $polygon = explode('*', $ruta['coordenadas']);
                                foreach ($polygon as $nro => $poly) {
                                    $aux = explode(',', $poly);
                                    $aux2 = (round($aux[0], 6) - 0.000044) . ',' . (round($aux[1], 6) + 0.00003);
                                    $polygon[$nro] = str_replace(',', ' ', $aux2);
                                }
                                $polygon[0] = str_replace(',', ' ', $polygon[$nro]);
                                $pointLocation = new pointLocation();

                                // Obtiene los clientes
                                $clientes = $db->select('*')->from('inv_clientes')->fetch();
                                foreach ($clientes as $cliente) {
                                    $aux2 = explode(',', $cliente['ubicacion']);
                                    $aux3 = $aux2[0] + 0.00005;
                                    $aux4 = $aux2[1] - 0.00003;
                                    $point = $aux3 . ' ' . $aux4;
                                    $punto = $pointLocation->pointInPolygon($point, $polygon);
                                    if ($punto == 'dentro') {
                                        $total_clientes = $total_clientes + 1;
                                    }
                                }
                                $id_ruta = $ruta['id_ruta'];
                                $rutas2a  = $db->query("SELECT a.*, COUNT(id_egreso) as contador_no_ventas  
                                            FROM gps_rutas a LEFT JOIN (SELECT id_egreso, ruta_id FROM inv_egresos 
                                            WHERE ruta_id > 0 AND fecha_egreso BETWEEN '{$ini}' AND '{$fin}'  
                                            GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.id_ruta 
                                            WHERE a.id_ruta = '$id_ruta' GROUP BY a.id_ruta ORDER BY fecha")->fetch_first();

                                $rutas1a = $db->query("SELECT a.*, COUNT(id_egreso) as contador_ventas  
                                                    FROM gps_rutas a LEFT JOIN (SELECT id_egreso, ruta_id FROM inv_egresos 
                                                    WHERE ruta_id > 0 AND fecha_egreso BETWEEN '{$ini}' AND '{$fin}'
                                                    AND tipo = 'Venta' GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.id_ruta 
                                                    WHERE a.id_ruta = '$id_ruta' GROUP BY a.id_ruta ORDER BY fecha")->fetch_first();

                                $rutas2 = $rutas2 + $rutas2a['contador_no_ventas'];
                                $rutas1 = $rutas1 + $rutas1a['contador_ventas'];
                            }

                            $clientes_activos = $rutas1;
                            $clientes_inactivos = $rutas2;
                            $clientes_no_visitados = ($total_clientes - ($rutas1 + $rutas2) > 0) ? ($total_clientes - ($rutas1 + $rutas2)) : 0;
                            
                        } else {
                            $total = 'no tiene';
                            $porcen = 'no tiene';
                            $ventas_dia = 'no tiene';
                            $total_clientes = 'no tiene';
                            $clientes_activos = 'no tiene';
                            $clientes_inactivos = 'no tiene';
                            $clientes_no_visitados = 'no tiene';
                        }
                        $vendedores[$nrop]['total_p'] = $total;
                        $vendedores[$nrop]['porcentaje_p'] = number_format(round($porcen, 2), 2, '.', '');
                        $vendedores[$nrop]['ventas_dia'] = $ventas_dia;
                        $vendedores[$nrop]['total_clientes'] = $total_clientes;
                        $vendedores[$nrop]['clientes_activos'] = $clientes_activos;
                        $vendedores[$nrop]['clientes_inactivos'] = $clientes_inactivos;
                        $vendedores[$nrop]['clientes_no_visitados'] = $clientes_no_visitados;
                    }

                    //se cierra transaccion
                    $db->commit();
                    if (count($vendedores) > 0) {
                        $respuesta = array(
                            'estado' => 's',
                            'metas' => $vendedores
                        );
                        echo json_encode($respuesta);
                    } else {
                        // Instancia el objeto
                        $respuesta = array(
                            'estado' => 'n',
                            'msg' => 'Sin datos.'
                        );

                        // Devuelve los resultados
                        echo json_encode($respuesta);
                    }
                } elseif ($tipo_meta == 2) { ///META PRODUCTOS
                    //Obtiene la as metas de los productos
                    $productos =  $db->select('a.id_meta_producto as id_meta, a.monto, a.fecha_inicio, a.fecha_fin, a.producto_id, b.nombre')
                            ->from('inv_meta_producto a')->join('inv_productos b', 'b.id_producto = a.producto_id')
                            ->where('a.fecha_inicio<=', date('Y-m-d'))->where('a.fecha_fin>=', date('Y-m-d'))->fetch();

                    foreach ($productos as $nrop => $producto) {
                        $ini = ($producto['fecha_inicio']) ? $producto['fecha_inicio'] : 'no tiene';
                        $fin = ($producto['fecha_fin']) ? $producto['fecha_fin'] : 'no tiene';
                        $meta = ($producto['monto']) ? $producto['monto'] : 'no tiene';
                        if ($ini != 'no tiene' && $fin != 'no tiene' && $meta != 'no tiene') {

                            $total = $db->query("SELECT 
                            IFNULL(SUM(ed.precio*(ed.cantidad/(IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))),0)AS total
                            FROM inv_egresos_detalles AS ed
                            LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id  AND asi.visible = 's'
                            LEFT JOIN inv_egresos AS e ON e.id_egreso=ed.egreso_id
                            WHERE ed.producto_id='{$producto['producto_id']}' AND e.anulado = 0 AND e.estadoe != 0
                            AND e.empleado_id='{$empleado['id_empleado']}'
                            AND e.fecha_egreso BETWEEN'{$ini}' AND '{$fin}' ")->fetch_first()['total'];
 


                           /*  $total = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total
                                    FROM inv_egresos
                                    WHERE anulado != 3
                                    AND estadoe != 2
                                    AND empleado_id='{$empleado['id_empleado']}'
                                    AND fecha_egreso
                                    BETWEEN '{$ini}' AND '{$fin}'")->fetch_first(); */

                            //$total = ($total['total']) ? $total['total'] : 0;
                            
                            $total = ($total) ? $total : 0;
                            $porcen = ($total * 100) / $meta;
                        } else {
                            $total = 'no tiene';
                            $porcen = 'no tiene';
                        }
                        $productos[$nrop]['total_p'] = number_format(round($total, 2), 2, '.', '');
                        $productos[$nrop]['porcentaje_p'] = number_format(round($porcen, 2), 2, '.', '');

                        $productos[$nrop]['ventas_dia'] = 'no tiene';
                        $productos[$nrop]['total_clientes'] = 'no tiene';
                        $productos[$nrop]['clientes_activos'] = 'no tiene';
                        $productos[$nrop]['clientes_inactivos'] = 'no tiene';
                        $productos[$nrop]['clientes_no_visitados'] = 'no tiene';
                    }
                    //se cierra transaccion
                    $db->commit();

                    if (count($productos) > 0) {
                        $respuesta = array(
                            'estado' => 's',
                            'metas' => $productos
                        );
                        //devuelve los resultado
                        echo json_encode($respuesta);
                    } else {
                        // Instancia el objeto
                        $respuesta = array(
                            'estado' => 'n',
                            'msg' => 'Sin datos.'
                        );

                        // Devuelve los resultados
                        echo json_encode($respuesta);
                    }
                } elseif ($tipo_meta == 3) { /// META CATEGORIA
                    //Obtien las metas de las categorias
                    $categorias =  $db->select('a.id_meta_categoria as id_meta, a.monto, a.fecha_inicio, a.fecha_fin, a.categoria_id, b.categoria as nombre')
                        ->from('inv_meta_categoria a')->join('inv_categorias b', 'b.id_categoria = a.categoria_id')
                        ->where('a.fecha_inicio<=', date('Y-m-d'))->where('a.fecha_fin>=', date('Y-m-d'))->fetch();

                    foreach ($categorias as $nrop => $categoria) {
                        $ini = ($categoria['fecha_inicio']) ? $categoria['fecha_inicio'] : 'no tiene';
                        $fin = ($categoria['fecha_fin']) ? $categoria['fecha_fin'] : 'no tiene';
                        $meta = ($categoria['monto']) ? $categoria['monto'] : 'no tiene';
                        if ($ini != 'no tiene' && $fin != 'no tiene' && $meta != 'no tiene') {
                            $total = $db->query("SELECT IFNULL(SUM(ed.precio * (ed.cantidad / (IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))),0)AS total
                                    FROM inv_egresos_detalles AS ed
                                    LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id AND asi.visible = 's'
                                    LEFT JOIN inv_egresos AS e ON e.id_egreso = ed.egreso_id
                                    LEFT JOIN inv_productos AS p ON ed.producto_id=p.id_producto
                                    WHERE p.categoria_id='{$categoria['categoria_id']}' AND e.anulado = 0 AND e.estadoe != 0
                                    AND e.empleado_id = '{$empleado['id_empleado']}'
                                    AND e.fecha_egreso BETWEEN '{$ini}' AND '{$fin}' ")->fetch_first();

                            /* $total=$db->query("SELECT IFNULL(SUM(monto_total),0)AS total
                                            FROM inv_egresos
                                            WHERE anulado != 3
                                            AND estadoe != 2
                                            AND empleado_id='{$empleado['id_empleado']}'
                                            AND fecha_egreso
                                            BETWEEN '{$ini}' AND '{$fin}'")->fetch_first(); */

                            $total = ($total['total']) ? $total['total'] : 0;
                            $total = ($total) ? $total : 0;
                            $porcen = ($total * 100) / $meta;
                        } else {
                            $total = 'no tiene';
                            $porcen = 'no tiene';
                        }
                        $categorias[$nrop]['total_p'] = number_format(round($total, 2), 2, '.', '');
                        $categorias[$nrop]['porcentaje_p'] = number_format(round($porcen, 2), 2, '.', '');

                        $categorias[$nrop]['ventas_dia'] = 'no tiene';
                        $categorias[$nrop]['total_clientes'] = 'no tiene';
                        $categorias[$nrop]['clientes_activos'] = 'no tiene';
                        $categorias[$nrop]['clientes_inactivos'] = 'no tiene';
                        $categorias[$nrop]['clientes_no_visitados'] = 'no tiene';
                    }
                    //se cierra transaccion
                    $db->commit();
                    if (count($categorias) > 0) {
                        $respuesta = array(
                            'estado' => 's',
                            'metas' => $categorias
                        );
                        echo json_encode($respuesta);
                    } else {
                        $respuesta = array(
                            'estado' => 'n',
                            'msg' => 'Sin datos.'
                        );
                        echo json_encode($respuesta);
                    }
                } elseif ($tipo_meta == 4) { /// META DE DISTRIBUIDOR
                    require config . '/poligono.php';
                    $hoy = date('Y-m-d');
                    // $empleado['id_empleado']
                    //metas distribuidor
                    $distros = $db->select("m.id_meta,m.monto,m.fecha_inicio,m.fecha_fin,m.distribuidor_id,e.nombres as nombre,e.paterno,e.materno, e.id_empleado")
                        ->from('inv_metas_distribuidor AS m')
                        ->join('sys_empleados AS e', 'e.id_empleado=m.distribuidor_id', 'left')
                        ->where('CURDATE() BETWEEN m.fecha_inicio AND m.fecha_fin')
                        ->where('m.distribuidor_id', $empleado['id_empleado'])->fetch();

                    foreach ($distros as $nrop => $distro) { 
                        $ini = ($distro['fecha_inicio']) ? $distro['fecha_inicio'] : 'no tiene';
                        $fin = ($distro['fecha_fin']) ? $distro['fecha_fin'] : 'no tiene';
                        $meta = ($distro['monto']) ? $distro['monto'] : 'no tiene';

                        if ($ini != 'no tiene' && $fin != 'no tiene' && $meta != 'no tiene') {

                            $total = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total
                                            FROM tmp_egresos
                                            WHERE anulado != 3
                                            AND distribuidor_estado IN ('ENTREGA', 'VENTA') 
                                            AND distribuidor_id='" . $empleado['id_empleado']. "'
                                            AND distribuidor_fecha
                                            BETWEEN '{$ini}' AND '{$fin}'")->fetch_first();
                            $total = ($total['total']) ? $total['total'] : 0;

                            $devoluciones_jornada = $db->query("SELECT IFNULL(SUM(te.monto_total), 0) total_devuelto
                                    FROM tmp_egresos te 
                                    LEFT JOIN tmp_egresos_detalles td ON te.id_tmp_egreso=td.tmp_egreso_id        
                                    WHERE te.estado = 2 AND te.distribuidor_id = '" . $empleado['id_empleado']. "'
                                    AND te.distribuidor_estado NOT IN ('ENTREGA', 'VENTA') 
                                    AND (te.accion IN ('VentaDevuelto') OR (te.estadoe = 3 AND te.estado = 2 AND te.distribuidor_estado = 'DEVUELTO'))
                                    AND te.distribuidor_fecha = CURDATE() 
                                    AND td.promocion_id != 1")->fetch_first()['total_devuelto'];
                            $devoluciones_jornada = ($devoluciones_jornada >= 0) ? $devoluciones_jornada : 0;

                            $total = (($total - $devoluciones_jornada) >= 0) ? $total - $devoluciones_jornada : (($total >= 0) ? $total : 0);

                            $total = ($total) ? $total : 0;
                            $porcen = ($total * 100) / $meta;

                            //adiciones VENTAS DEL DIA
                            $ventas_del_dia = $db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM tmp_egresos WHERE anulado != 3 AND distribuidor_estado='ENTREGA' AND distribuidor_id='{$empleado['id_empleado']}' AND distribuidor_fecha  = '{$hoy}'")->fetch_first();
                            $ventas_del_dia = ($ventas_del_dia['total']) ? $ventas_del_dia['total'] : 0;
                            $ventas_dia = number_format($ventas_del_dia, 2);
                            //adiciones TOTAL CLIENTES

                            // para sacar el los clientes asignados
                            $total_clientes = 0;
                            $rutas = $db->select('gps_rutas.*')->from('gps_asigna_distribucion as ad')
                                ->join('gps_rutas', 'ad.ruta_id = gps_rutas.id_ruta')
                                ->where('ad.distribuidor_id', $empleado['id_empleado'])
                                ->where('ad.fecha_ini >=', $distro["fecha_inicio"])
                                ->where('ad.fecha_fin <=', $distro["fecha_fin"])->fetch();

                            $rutas1 = 0;
                            $rutas2 = 0;
                            foreach ($rutas as $key => $ruta) {
                                $polygon = explode('*', $ruta['coordenadas']);

                                foreach ($polygon as $nro => $poly) {
                                    $aux = explode(',', $poly);
                                    $aux2 = (round($aux[0], 6) - 0.000044) . ',' . (round($aux[1], 6) + 0.00003);
                                    $polygon[$nro] = str_replace(',', ' ', $aux2);
                                }
                                $polygon[0] = str_replace(',', ' ', $polygon[$nro]);
                                $pointLocation = new pointLocation();

                                // Obtiene los clientes
                                $clientes = $db->select('*')->from('inv_clientes')->fetch();
                                // echo json_encode($clientes); die();
                                foreach ($clientes as $cliente) {
                                    $aux2 = explode(',', $cliente['ubicacion']);
                                    $aux3 = $aux2[0] + 0.00005;
                                    $aux4 = $aux2[1] - 0.00003;
                                    $point = $aux3 . ' ' . $aux4;
                                    $punto = $pointLocation->pointInPolygon($point, $polygon);
                                    if ($punto == 'dentro') {
                                        $total_clientes = $total_clientes + 1;
                                    }
                                }
                                $id_ruta = $ruta['id_ruta'];

                                $rutas2a = $db->query("SELECT a.*, COUNT(id_egreso) as contador_no_ventas
                                                    FROM gps_asigna_distribucion a
                                                    LEFT JOIN (SELECT id_egreso, ruta_id
                                                            FROM tmp_egresos
                                                            WHERE ruta_id > 0
                                                            AND distribuidor_fecha
                                                            BETWEEN '{$distro['fecha_inicio']}' AND '{$distro['fecha_fin']}'
                                                            AND distribuidor_estado='NO ENTREGA'
                                                            AND distribuidor_id='" . $empleado['id_empleado']. "'
                                                            GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.ruta_id
                                                    WHERE a.ruta_id = '$id_ruta'
                                                    GROUP BY a.ruta_id")->fetch_first();

                                $rutas1a = $db->query("SELECT a.*, COUNT(id_egreso) as contador_ventas
                                                    FROM gps_asigna_distribucion a
                                                    LEFT JOIN (SELECT id_egreso, ruta_id
                                                            FROM tmp_egresos
                                                            WHERE ruta_id > 0
                                                            AND distribuidor_fecha
                                                            BETWEEN '{$distro['fecha_inicio']}' AND '{$distro['fecha_fin']}'
                                                            AND distribuidor_estado='ENTREGA'
                                                            AND distribuidor_id='" . $empleado['id_empleado']. "'
                                                            AND tipo = 'Venta'
                                                            GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.ruta_id
                                                    WHERE a.ruta_id = '$id_ruta'
                                                    GROUP BY a.ruta_id")->fetch_first();
                                // echo json_encode($rutas1a); die();
                                $rutas2 = $rutas2 + $rutas2a['contador_no_ventas'];
                                $rutas1 = $rutas1 + $rutas1a['contador_ventas'];
                            }

                            $clientes_activos = $rutas1;
                            $clientes_inactivos = $rutas2;
                            $clientes_no_visitados = ($total_clientes - ($rutas1 + $rutas2) > 0) ? ($total_clientes - ($rutas1 + $rutas2)) : 0;
                        } else {
                            $total = 'no tiene';
                            $porcen = 'no tiene';
                            $ventas_dia = 'no tiene';
                            $total_clientes = 'no tiene';
                            $clientes_activos = 'no tiene';
                            $clientes_inactivos = 'no tiene';
                            $clientes_no_visitados = 'no tiene';
                        }
                        $distros[$nrop]['total_p'] = $total;
                        $distros[$nrop]['porcentaje_p'] = number_format(round($porcen, 2), 2, '.', '');
                        $distros[$nrop]['ventas_dia'] = $ventas_dia;
                        $distros[$nrop]['total_clientes'] = $total_clientes;
                        $distros[$nrop]['clientes_activos'] = $clientes_activos;
                        $distros[$nrop]['clientes_inactivos'] = $clientes_inactivos;
                        $distros[$nrop]['clientes_no_visitados'] = $clientes_no_visitados;
                    }
                    //se cierra transaccion
                    $db->commit();

                    if (count($distros) > 0) {
                        $respuesta = array(
                            'estado' => 's',
                            'metas' => $distros
                        );
                        echo json_encode($respuesta);
                    } else {
                        $respuesta = array(
                            'estado' => 'n',
                            'msg' => 'Sin datos.'
                        );
                        echo json_encode($respuesta);
                    }
                } else {
                    //se cierra transaccion
                    $db->commit();

                    // Instancia el objeto
                    $respuesta = array(
                        'estado' => 'n',
                        'msg' => 'No exite metas'
                    );
                    // Devuelve los resultados
                    echo json_encode($respuesta);
                }
            }else{
                 //se cierra transaccion
                 $db->commit();

                 // Instancia el objeto
                 $respuesta = array(
                     'estado' => 'n',
                     'msg' => 'No exite empleado registrado'
                 );
                 // Devuelve los resultados
                 echo json_encode($respuesta);
            }
        } catch (Exception $e) {
            $status = false;
            $error = $e->getMessage();

            //Se devuelve el error en mensaje json
            echo json_encode(array("estado" => 'n', 'msg' => $error));

            //se cierra transaccion
            $db->rollback();
        }
    } else {
        // Instancia el objeto
        $respuesta = array(
            'estado' => 'n',
            'msg' => 'datos no definidos.'
        );

        // Devuelve los resultados
        echo json_encode($respuesta);
    }
} else {
    echo json_encode(array(
        'estado' => 'n',
        'msg' => 'Metodo no definido.'
    ));
}
