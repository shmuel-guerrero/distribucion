<?php


//OBTIENE ID_EGRESOS
$egresos_mov = $db->query("SELECT GROUP_CONCAT(DISTINCTROW(te.id_egreso) SEPARATOR ',')AS id_egresos,
                            GROUP_CONCAT(DISTINCTROW(ted.producto_id) SEPARATOR ',')AS id_productos
                            FROM tmp_egresos te 
                            LEFT JOIN tmp_egresos_detalles ted ON ted.tmp_egreso_id = te.id_tmp_egreso
                            WHERE te.estado = 2 AND te.distribuidor_id = '{$distribuidor}' 
                            AND te.distribuidor_estado NOT IN ('VENTA')")->fetch_first();

if (($egresos_mov['id_egresos'] != '' || $egresos_mov['id_egresos'] != null) && ($egresos_mov['id_productos'] != '' || $egresos_mov['id_productos'] != null)) {
    
    //obtiene  PRODUCTOS ENTREGADOS
    // Obtiene los detalles
    
    $detalles = $db->query("SELECT e.id_egreso,
                            ROUND(IFNULL(SUM((edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS total_inicio, 
                            ROUND(IFNULL(SUM(edi.cantidad),0),2)AS total_inicio_uno, 
                            ROUND(IFNULL(SUM(edi.precio * (edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS t_precio_inicio, 
    
                            u.id_unidad,
                            u.unidad,
                            uno.unidad as unidad_actual,
                            p.precio_actual,
                            edi.producto_id, 
                            edi.unidad_id,
                            p.codigo, p.nombre, p.nombre_factura, c.categoria,
    
                            IFNULL(A.total_dev_prev, 0) AS total_dev_prev,
                            IFNULL(A.total_dev_prev_uno, 0) AS total_dev_prev_uno,
                            IFNULL(A.tprecio_dev_prev, 0) AS tprecio_dev_prev,
                            A.unidades_edit_previo,
                            A.idun_edit_previo,
                            IFNULL(B.total_dev_post, 0) AS total_dev_post,
                            IFNULL(B.total_dev_post_uno, 0) AS total_dev_post_uno,
                            IFNULL(B.tprecio_dev_post, 0) AS tprecio_dev_post,
                            B.unidades_edit_post,
                            B.idun_edit_post,
                            IFNULL(C.total_elim_prev, 0) AS total_elim_prev,
                            IFNULL(C.total_elim_prev_uno, 0) AS total_elim_prev_uno,
                            IFNULL(C.tprecio_elim_prev, 0) AS tprecio_elim_prev,
                            C.unidades_elim_prev,
                            C.idun_elim_prev,
                            IFNULL(D.total_elim_post, 0) AS total_elim_post,
                            IFNULL(D.total_elim_post_uno, 0) AS total_elim_post_uno,
                            IFNULL(D.tprecio_elim_post, 0) AS tprecio_elim_post,
                            D.unidades_elim_post,
                            D.idun_elim_post,
                            IFNULL(E.total_anulados, 0) AS total_anulados,
                            IFNULL(E.total_anulados_uno, 0) AS total_anulados_uno,
                            IFNULL(E.tprecio_anulados, 0) AS tprecio_anulados,
                            E.unidades_anular,
                            E.idun_anular,
                            IFNULL(F.total_noentrega, 0) AS total_noentrega,
                            IFNULL(F.total_noentrega_uno, 0) AS total_noentrega_uno,
                            IFNULL(F.tprecio_noentrega, 0) AS tprecio_noentrega,
                            F.unidades_noentrega,
                            F.idun_noentrega
    
    
                            FROM (SELECT DISTINCTROW(id_egreso) FROM tmp_egresos te WHERE te.estado = 2 AND te.distribuidor_id = {$distribuidor} AND te.distribuidor_estado NOT IN ('VENTA') ) e 
                            LEFT JOIN inv_egresos_detalles_inicio edi ON e.id_egreso = edi.egreso_id
                            LEFT JOIN inv_asignaciones a ON a.producto_id = edi.producto_id AND a.unidad_id = edi.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_productos p ON p.id_producto = edi.producto_id
                            LEFT JOIN inv_unidades u ON u.id_unidad = edi.unidad_id
                            LEFT JOIN inv_unidades uno ON uno.id_unidad = p.unidad_id
                            LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
    
    
                            -- DEVUELTOS PREVIAMENTE
                            LEFT JOIN (SELECT
                            GROUP_CONCAT(ROUND(IFNULL(((edep.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY edep.id_detalleAccion DESC SEPARATOR '|')AS total_dev_prev, 
                            GROUP_CONCAT(ROUND(IFNULL((edep.precio * (edep.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY edep.id_detalleAccion DESC SEPARATOR '|')AS tprecio_dev_prev, 
                            GROUP_CONCAT(ROUND(IFNULL(edep.cantidad ,0),2) ORDER BY edep.id_detalleAccion DESC SEPARATOR '|')AS total_dev_prev_uno, 
                            GROUP_CONCAT(u.unidad ORDER BY edep.id_detalleAccion DESC SEPARATOR '|') unidades_edit_previo,
                            GROUP_CONCAT(u.id_unidad ORDER BY edep.id_detalleAccion DESC SEPARATOR '|') idun_edit_previo,
                            edep.producto_id, edep.egreso_id
                            FROM inv_egresos_detalles_editar_previo edep 
                            LEFT JOIN inv_asignaciones a ON a.producto_id = edep.producto_id AND a.unidad_id = edep.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_unidades u ON u.id_unidad = edep.unidad_id
                            WHERE edep.empleado_id_accion = '{$distribuidor}' AND edep.producto_id IN ({$egresos_mov['id_productos']}) AND edep.egreso_id IN ({$egresos_mov['id_egresos']}) AND a.visible = 's'
                            GROUP BY edep.producto_id, edep.egreso_id) A ON A.egreso_id = e.id_egreso AND A.producto_id = edi.producto_id
    
                            -- DEVUELTOS POSTERIORMENTE
                            LEFT JOIN (SELECT
                            GROUP_CONCAT(ROUND(IFNULL(((edepo.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY edepo.id_detalleAccion DESC SEPARATOR '|')AS total_dev_post, 
                            GROUP_CONCAT(ROUND(IFNULL((edepo.precio * (edepo.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY edepo.id_detalleAccion DESC SEPARATOR '|')AS tprecio_dev_post, 
                            GROUP_CONCAT(ROUND(IFNULL(edepo.cantidad ,0),2) ORDER BY edepo.id_detalleAccion DESC SEPARATOR '|')AS total_dev_post_uno, 
                            GROUP_CONCAT(u.unidad ORDER BY edepo.id_detalleAccion DESC SEPARATOR '|') unidades_edit_post,
                            GROUP_CONCAT(u.id_unidad ORDER BY edepo.id_detalleAccion DESC SEPARATOR '|') idun_edit_post,
                            edepo.producto_id, edepo.egreso_id
                            FROM inv_egresos_detalles_editar_post edepo
                            LEFT JOIN inv_asignaciones a ON a.producto_id = edepo.producto_id AND a.unidad_id = edepo.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_unidades u ON u.id_unidad = edepo.unidad_id
                            WHERE edepo.empleado_id_accion = '{$distribuidor}' AND edepo.producto_id IN ({$egresos_mov['id_productos']}) AND edepo.egreso_id IN ({$egresos_mov['id_egresos']}) AND a.visible = 's'
                            GROUP BY edepo.producto_id, edepo.egreso_id) B ON B.egreso_id = e.id_egreso AND B.producto_id = edi.producto_id
    
    
                            -- ELIMINADO PREVIO
                            LEFT JOIN (SELECT
                            GROUP_CONCAT(ROUND(IFNULL(((elpre.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY elpre.id_detalleAccion DESC SEPARATOR '|')AS total_elim_prev, 
                            GROUP_CONCAT(ROUND(IFNULL((elpre.precio * (elpre.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY elpre.id_detalleAccion DESC SEPARATOR '|')AS tprecio_elim_prev, 
                            GROUP_CONCAT(ROUND(IFNULL(elpre.cantidad ,0),2) ORDER BY elpre.id_detalleAccion DESC SEPARATOR '|')AS total_elim_prev_uno, 
                            GROUP_CONCAT(u.unidad ORDER BY elpre.id_detalleAccion DESC SEPARATOR '|') unidades_elim_prev,
                            GROUP_CONCAT(u.id_unidad ORDER BY elpre.id_detalleAccion DESC SEPARATOR '|') idun_elim_prev,
                            elpre.producto_id, elpre.egreso_id
                            FROM inv_egresos_detalles_eliminar_previo elpre
                            LEFT JOIN inv_asignaciones a ON a.producto_id = elpre.producto_id AND a.unidad_id = elpre.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_unidades u ON u.id_unidad = elpre.unidad_id
                            WHERE elpre.empleado_id_accion = '{$distribuidor}' AND elpre.producto_id IN ({$egresos_mov['id_productos']}) AND elpre.egreso_id IN ({$egresos_mov['id_egresos']}) AND a.visible = 's'
                            GROUP BY elpre.producto_id, elpre.egreso_id) C ON C.egreso_id = e.id_egreso AND C.producto_id = edi.producto_id
    
                            -- DEVUELTOS POSTERIORMENTE
                            LEFT JOIN (SELECT
                            GROUP_CONCAT(ROUND(IFNULL(((elpost.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY elpost.id_detalleAccion DESC SEPARATOR '|')AS total_elim_post, 
                            GROUP_CONCAT(ROUND(IFNULL((elpost.precio * (elpost.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY elpost.id_detalleAccion DESC SEPARATOR '|')AS tprecio_elim_post, 
                            GROUP_CONCAT(ROUND(IFNULL(elpost.cantidad ,0),2) ORDER BY elpost.id_detalleAccion DESC SEPARATOR '|')AS total_elim_post_uno, 
                            GROUP_CONCAT(u.unidad ORDER BY elpost.id_detalleAccion DESC SEPARATOR '|') unidades_elim_post,
                            GROUP_CONCAT(u.id_unidad ORDER BY elpost.id_detalleAccion DESC SEPARATOR '|') idun_elim_post,
                            elpost.producto_id, elpost.egreso_id
                            FROM inv_egresos_detalles_eliminar_post elpost
                            LEFT JOIN inv_asignaciones a ON a.producto_id = elpost.producto_id AND a.unidad_id = elpost.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_unidades u ON u.id_unidad = elpost.unidad_id
                            WHERE elpost.empleado_id_accion = '{$distribuidor}' AND elpost.producto_id IN ({$egresos_mov['id_productos']}) AND elpost.egreso_id IN ({$egresos_mov['id_egresos']}) AND a.visible = 's'
                            GROUP BY elpost.producto_id, elpost.egreso_id) D ON D.egreso_id = e.id_egreso AND D.producto_id = edi.producto_id
    
    
                            -- anulados
                            LEFT JOIN(SELECT
                            GROUP_CONCAT(ROUND(IFNULL(((eda.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY eda.id_detalleAccion DESC SEPARATOR '|')AS total_anulados, 
                            GROUP_CONCAT(ROUND(IFNULL((eda.precio * (eda.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY eda.id_detalleAccion DESC SEPARATOR '|')AS tprecio_anulados, 
                            GROUP_CONCAT(ROUND(IFNULL(eda.cantidad ,0),2) ORDER BY eda.id_detalleAccion DESC SEPARATOR '|')AS total_anulados_uno, 
                            GROUP_CONCAT(u.unidad ORDER BY eda.id_detalleAccion DESC SEPARATOR '|') unidades_anular,
                            GROUP_CONCAT(u.id_unidad ORDER BY eda.id_detalleAccion DESC SEPARATOR '|') idun_anular,
                            eda.producto_id, eda.egreso_id
                            FROM inv_egresos_detalles_anular eda
                            LEFT JOIN inv_asignaciones a ON a.producto_id = eda.producto_id AND a.unidad_id = eda.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_unidades u ON u.id_unidad = eda.unidad_id
                            WHERE eda.empleado_id_accion = '{$distribuidor}' AND eda.producto_id IN ({$egresos_mov['id_productos']}) AND eda.egreso_id IN ({$egresos_mov['id_egresos']}) AND a.visible = 's'
                            GROUP BY eda.producto_id, eda.egreso_id)  E ON E.egreso_id = e.id_egreso AND E.producto_id = edi.producto_id
    
                            -- no entregas
                            LEFT JOIN (SELECT
                            GROUP_CONCAT(ROUND(IFNULL(((edne.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY edne.id_detalleAccion DESC SEPARATOR '|')AS total_noentrega, 
                            GROUP_CONCAT(ROUND(IFNULL((edne.precio * (edne.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2) ORDER BY edne.id_detalleAccion DESC SEPARATOR '|')AS tprecio_noentrega, 
                            GROUP_CONCAT(ROUND(IFNULL(edne.cantidad ,0),2) ORDER BY edne.id_detalleAccion DESC SEPARATOR '|')AS total_noentrega_uno, 
                            GROUP_CONCAT(u.unidad ORDER BY edne.id_detalleAccion DESC SEPARATOR '|') unidades_noentrega,
                            GROUP_CONCAT(u.id_unidad ORDER BY edne.id_detalleAccion DESC SEPARATOR '|') idun_noentrega,
                            edne.producto_id, edne.egreso_id
                            FROM inv_egresos_detalles_noentregas edne
                            LEFT JOIN inv_asignaciones a ON a.producto_id = edne.producto_id AND a.unidad_id = edne.unidad_id  AND a.visible = 's'
                            LEFT JOIN inv_unidades u ON u.id_unidad = edne.unidad_id
                            WHERE edne.empleado_id_accion = '{$distribuidor}' AND edne.producto_id IN ({$egresos_mov['id_productos']}) AND edne.egreso_id IN ({$egresos_mov['id_egresos']}) AND a.visible = 's'
                            GROUP BY edne.producto_id, edne.egreso_id) F ON F.egreso_id = e.id_egreso AND F.producto_id = edi.producto_id
    
                            WHERE edi.egreso_id IS NOT NULL  AND a.visible = 's'
                            GROUP BY e.id_egreso, edi.producto_id")->fetch();
    
    
    $datos_finales = array();
    $entregados = array();
    $devueltos = array();
    $unidad_entrega = 0;
    $total_entregados = 0;
    $total_devuelto = 0;
    
    //CALCULOS DE cantidades EDITADAS
    foreach ($detalles as $key => $value) {
        $datos_finales[$key] = $detalles[$key];
        $cant_devuelta = 0;
    
        if ($value['total_elim_prev'] == 0 && $value['total_elim_post'] == 0 && $value['total_dev_prev'] != 0 && $value['total_dev_prev'] != 0 
        && $value['total_dev_prev'] != null && $value['total_dev_prev'] != null) {
    
            //verificacion de id_unidad de EDITADO PREVIAMENTE
            $id_unidad_prev = ($value['idun_edit_previo']) ? explode('|', $value['idun_edit_previo']) :  array();
            //verificacion de id_unidad de ELIMINADO PREVIAMENTE
            $id_unidad_post = ($value['idun_edit_post']) ? explode('|', $value['idun_edit_post']) :  array();
    
            //verificacion de unidad de EDITADO PREVIAMENTE
            $unidad_dato = ($value['unidades_edit_previo']) ? explode('|', $value['unidades_edit_previo']) :  array();
            $unidad_entrega = (count($unidad_dato) > 0) ? $unidad_dato[0] : $value['unidad'];
            //verificacion de unidad de ELIMINADO PREVIAMENTE
            $unidad_dato = ($value['unidades_edit_post']) ? explode('|', $value['unidades_edit_post']) :  array();
            $unidad_entrega = (count($unidad_dato) > 0) ? $unidad_dato[0] : $unidad_entrega;
    
            //CALCULO DE LOS DEVUELTOS(EDICION) PREV Y POST
            $dato = ($value['total_dev_prev_uno']) ? explode('|', $value['total_dev_prev_uno']) :  array();
            $total_entregados = (count($dato) > 0) ? $dato[0] : $value['total_inicio'];
    
            //se calcula cantidad devuelta previa entrega
            //se calcula cantidad devuelta previa entrega
            $cant_devuelta = (count($dato) > 0) ? $value['total_inicio_uno'] - $dato[0] : (($value['total_inicio_uno']) ? $value['total_inicio_uno'] : 0);
            $cant_devuelta_previ = $cant_devuelta;
    
            $dato = ($value['total_dev_post_uno']) ? explode('|', $value['total_dev_post_uno']) :  array();
            $total_entregados = (count($dato) > 0) ? $dato[0] : $total_entregados;
    
            //se calcula cantidad devuelta post entrega
            $cant_devuelta = (count($dato) > 0) ? (($cant_devuelta > 0 && $cant_devuelta != null) ? $cant_devuelta - $dato[0] : $value['total_inicio_uno'] - $dato[0]) : (($cant_devuelta) ? $cant_devuelta : 0);
            $cant_devuelta_post = $cant_devuelta;
    
            //CALCULO DE LOS DEVUELTOS PREcIO(EDICION) PREV Y POST
            $dato = ($value['tprecio_dev_prev']) ? explode('|', $value['tprecio_dev_prev']) :  array();
            $total_precio = (count($dato) > 0) ? $dato[0] : $value['t_precio_inicio'];
    
            $dato = ($value['tprecio_dev_post']) ? explode('|', $value['tprecio_dev_post']) :  array();
            $total_precio = (count($dato) > 0) ? $dato[0] : $total_precio;
    
            $detalles[$key]['entrega_unidad'] = $unidad_entrega;
            $detalles[$key]['entrega_cantidad'] = $total_entregados;
            $detalles[$key]['entrega_precios'] = $total_precio;
            $detalles[$key]['devueltos_cantidad'] = $cant_devuelta;
            $detalles[$key]['cant_devuelta_previ'] = $cant_devuelta_previ;
            $detalles[$key]['cant_devuelta_post'] = $cant_devuelta_post;
        }
    }
    /* var_dump($detalles);
    echo "<br>";
    echo "<br>";
    echo "<br>"; */
    
    foreach ($detalles as $key => $value) {
        $$cant_devueltos = 0;
        $precios_devueltos = 0;
    
            // se prepara datos de productos entregados
        /* if ($value['total_anulados'] == 0 && $value['total_noentrega'] == 0) {
    
            //array preparado para crear la tabla entregas
            $entregados[$key]['cantidad'] = $value['entrega_cantidad'];
            $entregados[$key]['unidad'] = $value['entrega_unidad'];
            $entregados[$key]['codigo'] = $value['codigo'];
            $entregados[$key]['nombre_factura'] = $value['nombre_factura'];
            $entregados[$key]['descripcion'] = 'Entrega';
            $entregados[$key]['categoria'] = $value['categoria'];
            $entregados[$key]['precio'] = $value['entrega_precios'];
            $entregados[$key]['id_producto'] = $value['producto_id'];
        }  */
    
        if (($value['devueltos_cantidad'] && $value['devueltos_cantidad'] != 0 && $value['devueltos_cantidad'] != null) ||
            ($value['total_elim_prev'] && $value['total_elim_prev'] != 0 && $value['total_elim_prev'] != null) || 
            ($value['total_elim_post'] && $value['total_elim_post'] != 0 && $value['total_elim_post'] != null) || 
            ($value['total_noentrega'] && $value['total_noentrega'] != 0 && $value['total_noentrega'] != null) ||
            ($value['total_anulados'] && $value['total_anulados'] != 0 && $value['total_anulados'] != null)) {
                            
            if ($value['devueltos_cantidad'] && $value['devueltos_cantidad'] != null) {
                $cant_devueltos = $value['devueltos_cantidad'];
                $precios_devueltos = (($cant_devueltos && $cant_devueltos != null) ? $cant_devueltos : 0) * $value['precio_actual'];
            }
            if ($value['total_elim_prev'] && $value['total_elim_prev'] != null) {
                $cant_devueltos = $value['total_inicio_uno'];
                $precios_devueltos = $value['precio_actual'];
            }elseif ($value['total_elim_post'] && $value['total_elim_post'] != null) {
                $cant_devueltos = $value['total_inicio_uno'];
                $precios_devueltos = $value['precio_actual'];
            }elseif ($value['total_noentrega'] && $value['total_noentrega'] != null) {
                $cant_devueltos = $value['total_inicio_uno'];
                $precios_devueltos = $value['precio_actual'];
            }elseif ($value['total_anulados'] && $value['total_anulados'] != null) {
                $cant_devueltos = $value['total_inicio_uno'];
                $precios_devueltos = $value['precio_actual'];
            }       
            
            //array preparado para crear la tabla devoluciones sin descontar ventas directas
            $devueltos[$key]['cantidad'] = ($cant_devueltos && $cant_devueltos != null) ? $cant_devueltos : 0;
            $devueltos[$key]['unidad'] = $value['unidad_actual'];
            $devueltos[$key]['codigo'] = $value['codigo'];
            $devueltos[$key]['nombre_factura'] = $value['nombre_factura'];
            $devueltos[$key]['descripcion'] = '';
            $devueltos[$key]['categoria'] = $value['categoria'];
            $devueltos[$key]['precio'] = ($precios_devueltos && $precios_devueltos != null) ? $precios_devueltos : 0;
            $devueltos[$key]['id_producto'] = $value['producto_id'];
            $devueltos[$key]['precio_actual'] = $value['precio_actual'];
           /*  var_dump($devueltos[$key]);
            echo "<br>"; */
        }    
    }
    
    
    /* echo "<br>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    var_dump($devueltos);
    echo "<br>";
    echo "<br>";
    echo "<br>"; */
    
    
    ///Obtiene las entregas realizadas
    $prod_entregados = $db->query("SELECT 
                    GROUP_CONCAT(A.cantidad SEPARATOR '|') AS cantidades,
                    GROUP_CONCAT(A.unidad SEPARATOR '|') AS unidades,
                    GROUP_CONCAT(A.precio SEPARATOR '|')AS totales, A.*
                    FROM (SELECT edi.egreso_id,
                    ROUND(IFNULL(SUM((edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS cantidad, 
                    ROUND(IFNULL(SUM(edi.cantidad),0),2)AS total_inicio_uno,
                    ROUND(IFNULL(SUM(edi.precio * (edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS precio, 
    
                    'Entregas' AS descripcion,
                    u.unidad,
                    edi.producto_id as id_producto, 
                    edi.unidad_id,
                    p.codigo, p.nombre, p.nombre_factura, c.categoria, p.precio_actual
                    FROM inv_egresos_detalles edi
                    LEFT JOIN inv_asignaciones a ON a.producto_id = edi.producto_id AND a.unidad_id = edi.unidad_id  AND a.visible = 's'
                    LEFT JOIN inv_productos p ON p.id_producto = edi.producto_id
                    LEFT JOIN inv_unidades u ON u.id_unidad = edi.unidad_id
                    LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id      
                    WHERE edi.egreso_id IS NOT NULL
                    AND edi.egreso_id IN ({$egresos_mov['id_egresos']}) AND edi.promocion_id != 1
                    GROUP BY edi.producto_id, edi.unidad_id) A
                    GROUP BY A.id_producto")->fetch();
    
    foreach ($prod_entregados as $key => $value) {
        $total_precio = explode('|', $value['totales']);
        $precio_producto = (count($total_precio) > 0) ? array_sum($total_precio) : 0;
        $cantidades = explode("|", $value['cantidades']);
        $unidades = explode("|", $value['unidades']);
        $new_cantidades = implode(" <br> ", $cantidades);
        $new_unidades = implode(" <br> ", $unidades);
    
        $entregados[$key]['cantidad'] = $new_cantidades;
        $entregados[$key]['unidad'] = $new_unidades;
        $entregados[$key]['codigo'] = $value['codigo'];
        $entregados[$key]['nombre_factura'] = $value['nombre_factura'];
        $entregados[$key]['descripcion'] = 'Entrega';
        $entregados[$key]['categoria'] = $value['categoria'];
        $entregados[$key]['precio'] = $precio_producto;
        $entregados[$key]['id_producto'] = $value['producto_id'];
    }
    
    
    //obtiene PRODUCTOS VENDIDOS - VENTA DIRECTA(REVENDIDOS) POR PRODUCTO
    $ventas_directas = $db->query('SELECT te.id_egreso,
                    ROUND(IFNULL(SUM((edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS cantidad, 
                    ROUND(IFNULL(SUM(edi.cantidad),0),2)AS total_inicio_uno,
                    ROUND(IFNULL(SUM(edi.precio * (edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS precio, 
    
                    "Venta Directa" AS descripcion,
                    u.unidad,
                    edi.producto_id as id_producto, 
                    edi.unidad_id,
                    p.codigo, p.nombre, p.nombre_factura, c.categoria, p.precio_actual
    
                    FROM tmp_egresos te 
                    LEFT JOIN tmp_egresos_detalles edi ON te.id_egreso = edi.egreso_id
                    LEFT JOIN inv_asignaciones a ON a.producto_id = edi.producto_id AND a.unidad_id = edi.unidad_id  AND a.visible = "s"
                    LEFT JOIN inv_productos p ON p.id_producto = edi.producto_id
                    LEFT JOIN inv_unidades u ON u.id_unidad = edi.unidad_id
                    LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id      
    
                    WHERE edi.egreso_id IS NOT NULL AND a.visible = "s" AND 
                    te.estado = 2 AND te.distribuidor_id = ' . $distribuidor . ' AND te.distribuidor_estado IN ("VENTA") AND edi.promocion_id != 1
                    GROUP BY edi.producto_id')->fetch(); 
    
    //obtine los movimientos efectivos realizados por cliente
    $resumen_entregas = $db->query("SELECT c.id_cliente AS codigo_cliente, c.cliente, c.nombre_factura, c.nit, IFNULL(e.monto_total, 0)AS monto_total, 
                                IFNULL(p.interes_pago, 0)AS interes_pago, IFNULL(SUM(pd.monto), 0) as monto_cancelado, e.plan_de_pagos
                                FROM inv_egresos e 
                                LEFT JOIN inv_clientes c ON e.cliente_id = c.id_cliente
                                LEFT JOIN inv_pagos p ON p.movimiento_id = e.id_egreso AND p.tipo = 'Egreso'
                                LEFT JOIN inv_pagos_detalles pd ON pd.pago_id = p.id_pago
                                WHERE  e.id_egreso IN ({$egresos_mov['id_egresos']}) AND e.estadoe = 3 AND e.anulado = 0
                                GROUP BY e.cliente_id")->fetch();
    
    //obtien los cobros de deudas por cliente
    $cobros_deudas = $db->query("SELECT e.cliente_id AS codigo_cliente, c.nombre_factura, c.cliente, 
                                c.nit, e.monto_total, p.interes_pago, 
                                SUM(pd.monto)AS monto_cancelado, p.id_pago 
                                FROM inv_pagos_detalles pd 
                                LEFT JOIN inv_pagos p ON p.id_pago = pd.pago_id AND p.tipo = 'Egreso'
                                LEFT JOIN inv_egresos e ON e.id_egreso = p.movimiento_id AND e.plan_de_pagos = 'si'
                                LEFT JOIN tmp_egresos te ON te.id_egreso = e.id_egreso AND te.distribuidor_estado = 'ENTREGA' AND te.plan_de_pagos = 'si' AND te.anulado = 0
                                LEFT JOIN inv_clientes c ON c.id_cliente = e.cliente_id
                                WHERE pd.fecha_pago = CURDATE() 
                                AND e.plan_de_pagos = 'si' AND e.estadoe = 3 AND pd.estado = 1 AND te.distribuidor_id = '{$distribuidor}'
                                GROUP BY e.cliente_id")->fetch();
    
    // se prepara datos de tabla resumen
    $resumen = array();
    foreach ($resumen_entregas as $key => $value) {
        $resumen[$key]['codigo'] = $value['codigo_cliente'];
        $resumen[$key]['nit'] = $value['nit'];
        $resumen[$key]['nombre'] = $value['cliente'];
        $resumen[$key]['nombre_factura'] = $value['nombre_factura'];
        $resumen[$key]['monto1'] = number_format((($value['monto_total'] >= 0) ? $value['monto_total'] : 0), 2, '.', '');
        $resumen[$key]['monto2'] = number_format((($value['monto_total'] - $value['monto_cancelado'] >= 0 && $value['plan_de_pagos'] == 'si') ? $value['monto_total'] - $value['monto_cancelado'] : 0), 2, '.', '');
        $resumen[$key]['monto3'] = number_format((($value['monto_cancelado'] >= 0 && $value['plan_de_pagos'] == 'si') ? $value['monto_cancelado'] : (($value['monto_total']) ? $value['monto_total'] : 0)), 2, '.', '');
    }
    
    // se prepara datos de tabla cobros
    $cobros = array();
    foreach ($cobros_deudas as $key => $value) {
        $cobros[$key]['codigo'] = $value['codigo_cliente'];
        $cobros[$key]['nit'] = $value['nit'];
        $cobros[$key]['nombre'] = $value['cliente'];
        $cobros[$key]['nombre_factura'] = $value['nombre_factura'];
        $cobros[$key]['monto1'] = number_format((($value['monto_total'] >= 0) ? $value['monto_total'] : 0), 2, '.', '');
        $cobros[$key]['monto2'] = number_format((($value['monto_total'] - $value['monto_cancelado'] >= 0) ? $value['monto_total'] - $value['monto_cancelado'] : 0), 2, '.', '');
        $cobros[$key]['monto3'] = number_format((($value['monto_cancelado'] >= 0) ? $value['monto_cancelado'] : 0), 2, '.', '');
    }
     
    /* echo "<br>";
    var_dump($devueltos);
    echo "<br>";
    echo "<br>";
    echo "<br>";
    
    var_dump($ventas_directas);
    exit;  */
    
    
    $productos_devueltos = array();
    $datos_resumen = '';
    $datos_cobros = '';
    
    $productos_entregados = $entregados;
    
    //se hace de funciones para preparar datos y tablas a imprimir en el pdf
    $productos_devueltos = eliminar_duplicado($devueltos);
    $productos_devueltos = restar_cantidades($ventas_directas, $productos_devueltos);
    
    $datos_entregados = prepara_tabla($productos_entregados, "PRODUCTOS ENTREGADOS", $moneda);
    $datos_devueltos = prepara_tabla($productos_devueltos, "PRODUCTOS DEVUELTOS - NO ENTREGADOS", $moneda, "redondeo");
    $datos_ventas_directas = prepara_tabla($ventas_directas, "VENTAS DIRECTAS", $moneda, "redondeo");
    $datos_resumen = prepara_tabla2($resumen, "RESUMEN DE MOVIMIENTOS", $moneda);
    $datos_cobros = prepara_tabla2($cobros, "COBROS DEUDAS", $moneda);
    
}else{
    // Instancia la variable de notificacion
    $_SESSION[temporary] = array(
        'alert' => 'danger',
        'title' => 'No se encontro movimientos en la base de datos.',
        'message' => 'No se pudo generar el reporte.'
    );

    // Redirecciona a la pagina principal
    redirect('?/distribuidor/listar2');
}    





/**FUNCIONES DE ARMADO DE TABLAS PARA IMPRESION DE PDF */
function prepara_tabla($datos_array = array(), $titulo = "REPORTE", $valor_moneda = "", $tipo_cant = "")
{
    // Estructura la tabla
    $body = '';
    $total = 0;
    $body2 = '';
    $total_entregados = 0;
    $valor_total_entrega = number_format(0, 2, '.', '');
    $respuesta = '';

    //PRODUCTOS ENTREGADOS
    $body .= '<table cellpadding="1">
            <tr>
            <td colspan="3"></td>
            </tr>
            <tr>
            <td class="none" align="center" colspan="3" bgcolor="#CCCCCC" ><h3>' . $titulo . '</h3></td>
            </tr>
            </table>
            <br><br>
            <table cellpadding="2">
            <tr>
            <th width="6%" class="all" align="left">CANT.</th>
            <th width="19%" class="all" align="left">UNIDAD</th>
            <th width="35%" class="all" align="left">DETALLE</th>
            <th width="15%" class="all" align="left">DESCRIPCIÓN</th>
            <th width="13%" class="all" align="right">CATEGORÍA</th>
            <th width="12%" class="all" align="right">IMPORTE ' . $valor_moneda . '</th>
            </tr>';
    if (count($datos_array) > 0) {

        foreach ($datos_array as $nro => $detalle) {

            if ($detalle['cantidad'] > 0) {
                $body .= '<tr height="2%" >';
                $body .= '<td class="left-right bot" align="right">' . (($tipo_cant == 'redondeo') ? number_format((($detalle['cantidad'])? $detalle['cantidad'] : 0), 0, '.', '') : $detalle['cantidad'] ) . '</td>';
                $body .= '<td class="left-right bot">' . $detalle['unidad'] . '</td>';
                $body .= '<td class="left-right bot" align="left">' . $detalle['codigo'] . ' - ' . $detalle['nombre_factura'] . '</td>';
                $body .= '<td class="left-right bot" align="right">' . $detalle['descripcion'] . '</td>';
                $body .= '<td class="left-right bot" align="right">' . $detalle['categoria'] . '</td>';
                $body .= '<td class="left-right bot" align="right">' . number_format($detalle['precio'], 2, '.', '') . '</td>';
                $body .= '</tr>';

                $total_entregados = $total_entregados + $detalle['precio'];
            }
        }

        /** COBROS DEUDAS ANTERIORES */
        $valor_total_entrega = number_format($total_entregados, 2, '.', '');

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $valor_total_entrega);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = strtoupper($conversor->to_word($monto_numeral));

    }else{
        $body .= '<tr><td colspan="7" align="center" class="all">No existe registros de movimientos para este reporte.</td></tr>';
    }
    $body .= '<tr>
        <th class="all" align="right" colspan="5">IMPORTE TOTAL ' . $valor_moneda . '</th>
        <th class="all" align="right"> ' . $valor_total_entrega . '</th>
        </tr>
        </table>
        <p align="right">' . $monto_literal . ' ' . $monto_decimal . ' /100</p>';

    //RESPUESTA DE TABLA ARMADA
    $respuesta =  $body;
    return $respuesta;
}

function prepara_tabla2($datos_array = array(), $titulo = "REPORTE", $valor_moneda = "")
{
    // Estructura la tabla
    $body = '';
    $total = 0;
    $body2 = '';
    $total_entregados = 0;
    $valor_total_entrega = number_format(0, 2, '.', '');
    $respuesta = '';

    //PRODUCTOS ENTREGADOS
    $body .= '<table cellpadding="1">
            <tr>
            <td colspan="3"></td>
            </tr>
            <tr>
            <td class="none" align="center" colspan="3" bgcolor="#CCCCCC" ><h3>' . $titulo . '</h3></td>
            </tr>
            </table>
            <br><br>
            <table cellpadding="2">
            <tr>
            <th width="10%" class="all" align="left">CODIGO</th>
            <th width="33%" class="all" align="left">NIT - CLIENTE</th>
            <th width="19%" class="all" align="rihgt">MONTO VENDIDO ' . $valor_moneda . '</th>
            <th width="19%" class="all" align="right">SALDO ' . $valor_moneda . '</th>
            <th width="19%" class="all" align="right">COBRO ' . $valor_moneda . '</th>
            </tr>';
    if (count($datos_array) > 0) {

        foreach ($datos_array as $nro => $detalle) {

            if (true) {
                $body .= '<tr height="2%" >';
                $body .= '<td class="left-right bot" align="right">' . (($detalle['codigo'])? $detalle['codigo'] : '-') . '</td>';
                $body .= '<td class="left-right bot">' . $detalle['nit'] . ' - ' . $detalle['nombre_factura'] . '<small>' . $detalle['nombre'] . '</small>' . '</td>';                
                $body .= '<td class="left-right bot" align="right">' . number_format((($detalle['monto1']) ? $detalle['monto1'] : 0), 2, '.', '') . '</td>';
                $body .= '<td class="left-right bot" align="right">' . number_format((($detalle['monto2']) ? $detalle['monto2'] : 0), 2, '.', '') . '</td>';
                $body .= '<td class="left-right bot" align="right">' . number_format((($detalle['monto3']) ? $detalle['monto3'] : 0), 2, '.', '') . '</td>';
                $body .= '</tr>';

                $total_entregados = $total_entregados + $detalle['monto3'];
            }
        }

        /** COBROS DEUDAS ANTERIORES */
        $valor_total_entrega = number_format($total_entregados, 2, '.', '');

        
    }else{
        $body .= '<tr><td colspan="7" align="center" class="all">No existe registros de movimientos para este reporte.</td></tr>';
    }

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $valor_total_entrega);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = strtoupper($conversor->to_word($monto_numeral));
    
    $body .= '<tr>
        <th class="all" align="right" colspan="4">IMPORTE TOTAL ' . $valor_moneda . '</th>
        <th class="all" align="right"> ' . $valor_total_entrega . '</th>
        </tr>
        </table>
        <p align="right">' . $monto_literal . ' ' . $monto_decimal . ' /100</p>';
    //RESPUESTA DE TABLA ARMADA
    $respuesta =  $body;

    return $respuesta;
}

/** 
 * FUNCIONES DE eliminacion registros duplicados sumando sus cantidades e importes 
 * */
function eliminar_duplicado($array_productos = array())
{

    $ids_products = [];
    $result = [];
    if (count($array_productos) > 0) {

        // crea un array con los codigos unicos
        foreach ($array_productos as $datosProductos) {
            $id_product = $datosProductos["id_producto"];
            if (!in_array($id_product, $ids_products)) {
                $ids_products[] = $id_product;
            }
        }
        //dx($ids_products); 

        $contador = 0;
        foreach ($ids_products as $unico_id) {
            $temporal     = [];
            foreach ($array_productos as $datosProductos) {
                $id = $datosProductos["id_producto"];

                if ($id === $unico_id) {
                    $temporal[] = $datosProductos;
                }
            }

            $product = $temporal[0];

            $product["cantidad"] = 0;
            $product["precio"] = 0;
            foreach ($temporal as $product_temp) {
                $product["cantidad"] = $product["cantidad"] + $product_temp["cantidad"];
                $product["precio"] = $product["precio"] + $product_temp["precio"];
                //$product["unidad"] = (strpos($product["unidad"], $product_temp["unidad"]) === false) ? $product["unidad"] . " - " . $product_temp["unidad"] : $product["unidad"];
            }
            //dx($product["cantidad"]);

            $result[$contador] = $product;
            $contador++;
        }
    }

    return $result;
    /* //funcion imprime datos
    function dx($x): void
    {
        echo '<pre>';
        var_export($x);
        echo '</pre>';
    }
    */
}

/**
 * se resta dos array cantidades y se fija  el precio base
 */
function restar_cantidades($productos_vendidos = array(), $devueltos = array())
{
    $product = [];    
    $nuevo_array = [];        
    if (count($devueltos) > 0) {
        $contador = 0;
        foreach ($devueltos as $unico_id) {
            $temporal  = [];
            foreach ($productos_vendidos as $datosProductos) {
                $id = $datosProductos["id_producto"];

                if ($id === $unico_id['id_producto']) {
                    $temporal[] = $datosProductos;
                }
            }

            if (count($temporal) > 0) {                
                $product = $temporal[0];                
                $unico_id["cantidad"] = $unico_id["cantidad"] - $product["total_inicio_uno"];
                $unico_id["precio"] = $unico_id["cantidad"] * $product["precio_actual"];
                $unico_id['unidad'] = $unico_id['unidad'] . ' - BASE';
            }
            if($unico_id["cantidad"] > 0){
                $nuevo_array[$contador] =  $unico_id; 
                $contador++;
            }
        }
    }
    return $nuevo_array;
}