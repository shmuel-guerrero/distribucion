<?php


//se crea la clase devoluciones distribucion
class devoluciones_distribucion
{

    private $id_movimiento = 0;
    private $adicionar = 0;

    private $id_distribuidor_obtenido = 0;
    private $id_productos_obtenidos = 0;
    private $id_egresos_obtenidos = 0;
    public $estado_validar = false;

    private $array_editado = array();
    private $array_devueltos = array();
    private $array_vendidos = array();

    /***
     * CONSTRUCTOR
     */
    //se crea el constructor
    function devoluciones_distribucion($db, $distribuidor = 0)
    {

        if ($distribuidor > 0) {

            //OBTIENE ID_EGRESOS
            $egresos_mov = $db->query("SELECT GROUP_CONCAT(DISTINCTROW(te.id_egreso) SEPARATOR ',')AS id_egresos,
                            GROUP_CONCAT(DISTINCTROW(ted.producto_id) SEPARATOR ',')AS id_productos
                            FROM tmp_egresos te 
                            LEFT JOIN tmp_egresos_detalles ted ON ted.tmp_egreso_id = te.id_tmp_egreso
                            WHERE te.estado = 3 AND te.distribuidor_id = '{$distribuidor}' 
                            AND te.distribuidor_estado NOT IN ('VENTA')")->fetch_first();

            if (count($egresos_mov) > 0 && $egresos_mov['id_egresos'] != null && $egresos_mov['id_productos'] != null && $distribuidor > 0) {
                $this->estado_validar = true;
                $this->id_productos_obtenidos = $egresos_mov['id_productos'];
                $this->id_egresos_obtenidos = $egresos_mov['id_egresos'];
                $this->id_distribuidor_obtenido = $distribuidor;
            } else {
                $this->estado_validar = false;
                $this->id_productos_obtenidos = 0;
                $this->id_egresos_obtenidos = 0;
                $this->id_distribuidor_obtenido = 0;
            }
        } else {
            $this->estado_validar = false;
            $this->id_productos_obtenidos = 0;
            $this->id_egresos_obtenidos = 0;
            $this->id_distribuidor_obtenido = 0;
        }
    }

    public function calculo_editados($db)
    {
        if ($this->estado_validar) {

            $id_productos = $this->id_productos_obtenidos;
            $id_egresos = $this->id_egresos_obtenidos;
            $id_distribuidor = $this->id_distribuidor_obtenido;

            //obtiene  PRODUCTOS ENTREGADOS. EDITAS, ELIMINADOS, ETC
            $detalles = $db->query("SELECT e.id_egreso,
             ROUND(IFNULL(SUM((edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS total_inicio, 
             ROUND(IFNULL(SUM(edi.cantidad),0),2)AS total_inicio_uno, 
             ROUND(IFNULL(SUM(edi.precio * (edi.cantidad / (IF(a.cantidad_unidad is null,1,IF(a.cantidad_unidad>0,a.cantidad_unidad, 1))))),0),2)AS t_precio_inicio, 
    
             u.id_unidad,
             uu.id_unidad AS id_unidad_base,
             u.unidad,
             uu.unidad AS unidad_base,
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
    
    
             FROM (SELECT DISTINCTROW(id_egreso) FROM tmp_egresos te WHERE te.distribuidor_id = '{$id_distribuidor}' AND te.distribuidor_estado NOT IN ('ENTREGA', 'VENTA') AND te.estado = 3) e 
             LEFT JOIN inv_egresos_detalles_inicio edi ON e.id_egreso = edi.egreso_id
             LEFT JOIN inv_asignaciones a ON a.producto_id = edi.producto_id AND a.unidad_id = edi.unidad_id  AND a.visible = 's'
             LEFT JOIN inv_productos p ON p.id_producto = edi.producto_id
             LEFT JOIN inv_unidades u ON u.id_unidad = edi.unidad_id
             LEFT JOIN inv_unidades uu ON uu.id_unidad = p.unidad_id
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
             WHERE edep.empleado_id_accion = '{$id_distribuidor}' AND edep.producto_id IN ({$id_productos}) AND edep.egreso_id IN ({$id_egresos}) 
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
             WHERE edepo.empleado_id_accion = '{$id_distribuidor}' AND edepo.producto_id IN ({$id_productos}) AND edepo.egreso_id IN ({$id_egresos})
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
             LEFT JOIN inv_asignaciones a ON a.producto_id = elpre.producto_id AND a.unidad_id = elpre.unidad_id AND a.visible = 's'
             LEFT JOIN inv_unidades u ON u.id_unidad = elpre.unidad_id
             WHERE elpre.empleado_id_accion = '{$id_distribuidor}' AND elpre.producto_id IN ({$id_productos}) AND elpre.egreso_id IN ({$id_egresos}) 
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
             LEFT JOIN inv_asignaciones a ON a.producto_id = elpost.producto_id AND a.unidad_id = elpost.unidad_id AND a.visible = 's'
             LEFT JOIN inv_unidades u ON u.id_unidad = elpost.unidad_id
             WHERE elpost.empleado_id_accion = '{$id_distribuidor}' AND elpost.producto_id IN ({$id_productos}) AND elpost.egreso_id IN ({$id_egresos}) 
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
             LEFT JOIN inv_asignaciones a ON a.producto_id = eda.producto_id AND a.unidad_id = eda.unidad_id AND a.visible = 's'
             LEFT JOIN inv_unidades u ON u.id_unidad = eda.unidad_id
             WHERE eda.empleado_id_accion = '{$id_distribuidor}' AND eda.producto_id IN ({$id_productos}) AND eda.egreso_id IN ({$id_egresos}) 
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
             LEFT JOIN inv_asignaciones a ON a.producto_id = edne.producto_id AND a.unidad_id = edne.unidad_id AND a.visible = 's'
             LEFT JOIN inv_unidades u ON u.id_unidad = edne.unidad_id
             WHERE edne.empleado_id_accion = '{$id_distribuidor}' AND edne.producto_id IN ({$id_productos}) AND edne.egreso_id IN ({$id_egresos}) 
             GROUP BY edne.producto_id, edne.egreso_id) F ON F.egreso_id = e.id_egreso AND F.producto_id = edi.producto_id
    
             WHERE edi.egreso_id IS NOT NULL AND 
             (A.total_dev_prev != 0 || B.total_dev_post != 0 || C.total_elim_prev != 0 || D.total_elim_post != 0 || E.total_anulados != 0 || F.total_noentrega != 0)
    
             GROUP BY e.id_egreso, edi.producto_id")->fetch();

            $datos_finales = array();
            $entregados = array();
            $devueltos = array();
            $unidad_entrega = 0;
            $total_entregados = 0;

            /**
             ****** PREPARACION DE DATOS*****
             * CALCULOS DE CANTIDADES EDITADAS  PREVIA O POSTERIOR ENTREGA 
             */

            foreach ($detalles as $key => $value) {
                $datos_finales[$key] = $detalles[$key];
                $cant_devuelta = 0;

                $total_entregados = 0;
                $unidad_entrega = 0;
                $total_precio = 0;

                if ($value['total_elim_prev'] == 0 && $value['total_elim_post'] == 0 && ($value['total_dev_prev'] != 0 || $value['total_dev_post'] != 0)) {

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
                    $cant_devuelta = (count($dato) > 0) ? $value['total_inicio_uno'] - $dato[0] : (($value['total_inicio_uno']) ? $value['total_inicio_uno'] : 0);
                    $cant_devuelta_previ = $cant_devuelta;

                    $dato = ($value['total_dev_post_uno']) ? explode('|', $value['total_dev_post_uno']) :  array();
                    $total_entregados = (count($dato) > 0) ? $dato[0] : $total_entregados;

                    //se calcula cantidad devuelta post entrega
                    $cant_devuelta = (count($dato) > 0) ? (($cant_devuelta > 0 && $cant_devuelta != null) ? $value['total_inicio_uno'] - $dato[0] : (($cant_devuelta) ? $cant_devuelta : 0)) : (($cant_devuelta) ? $cant_devuelta : 0);
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
                }else{
                    $detalles[$key]['entrega_unidad'] = 0;
                    $detalles[$key]['entrega_cantidad'] = 0;
                    $detalles[$key]['entrega_precios'] = 0;
                    $detalles[$key]['devueltos_cantidad'] = 0;
                    $detalles[$key]['cant_devuelta_previ'] = 0;
                    $detalles[$key]['cant_devuelta_post'] = 0;
                }
            }

            /**
             ****** PREPARACION DE DATOS *****
             * SE PREPARA ARRAY DE DEVOLUCIONES SIN TOMAR EN CUENTA LAS VENTAS DIRECTAS REALIZADAS POR EL DISTRIBUIDOR
             */

            foreach ($detalles as $key => $value) {
                $cant_devueltos = 0;
                $precios_devueltos = 0;

                // se prepara datos de productos entregados
                if ($value['total_anulados'] == 0 && $value['total_noentrega'] == 0) {

                    //array preparado para crear la tabla entregas
                    $entregados[$key]['cantidad'] = ($value['entrega_cantidad']) ? $value['entrega_cantidad'] : 0;
                    $entregados[$key]['unidad'] = ($value['entrega_unidad']) ? $value['entrega_unidad'] : 0;
                    $entregados[$key]['codigo'] = $value['codigo'];
                    $entregados[$key]['nombre_factura'] = $value['nombre_factura'];
                    $entregados[$key]['descripcion'] = 'Entrega';
                    $entregados[$key]['categoria'] = $value['categoria'];
                    $entregados[$key]['precio'] = ($value['entrega_precios']) ? $value['entrega_precios'] : 0;
                    $entregados[$key]['id_producto'] = $value['producto_id'];
                }

                // se valida si se tiene datos generados por las acciones de editar, eliminar u otros
                if (($value['devueltos_cantidad'] && $value['devueltos_cantidad'] != null) ||
                    ($value['total_elim_prev'] && $value['total_elim_prev'] != null) ||
                    ($value['total_elim_post'] && $value['total_elim_post'] != null) ||
                    ($value['total_noentrega'] && $value['total_noentrega'] != null) ||
                    ($value['total_anulados'] && $value['total_anulados'] != null)
                ) {

                    //se verifica la obtencion de cantidad y precios
                    if ($value['devueltos_cantidad'] && $value['devueltos_cantidad'] != null) {
                        $cant_devueltos = $value['devueltos_cantidad'];
                        $precios_devueltos = (($cant_devueltos && $cant_devueltos != null) ? $cant_devueltos : 0) * $value['precio_actual'];
                    }

                    //si una de estas acciones posee dato se le asigna los datos de origen
                    if ($value['total_elim_prev'] && $value['total_elim_prev'] != null) {
                        $cant_devueltos = $value['total_inicio_uno'];
                        $precios_devueltos = $value['precio_actual'];
                    } elseif ($value['total_elim_post'] && $value['total_elim_post'] != null) {
                        $cant_devueltos = $value['total_inicio_uno'];
                        $precios_devueltos = $value['precio_actual'];
                    } elseif ($value['total_noentrega'] && $value['total_noentrega'] != null) {
                        $cant_devueltos = $value['total_inicio_uno'];
                        $precios_devueltos = $value['precio_actual'];
                    } elseif ($value['total_anulados'] && $value['total_anulados'] != null) {
                        $cant_devueltos = $value['total_inicio_uno'];
                        $precios_devueltos = $value['precio_actual'];
                    }

                    //array preparado para crear la tabla devoluciones sin descontar ventas directas
                    $devueltos[$key]['cantidad'] = ($cant_devueltos && $cant_devueltos != null) ? $cant_devueltos : 0;
                    $devueltos[$key]['unidad'] = $value['unidad_base'];
                    $devueltos[$key]['codigo'] = $value['codigo'];
                    $devueltos[$key]['nombre_factura'] = $value['nombre_factura'];
                    $devueltos[$key]['descripcion'] = '';
                    $devueltos[$key]['categoria'] = $value['categoria'];
                    $devueltos[$key]['precio'] = ($precios_devueltos && $precios_devueltos != null) ? $precios_devueltos : 0;
                    $devueltos[$key]['id_producto'] = $value['producto_id'];
                    $devueltos[$key]['precio_actual'] = $value['precio_actual'];
                    $devueltos[$key]['id_unidad'] = $value['id_unidad_base'];
                }
            }

            //se pasa el array tratado al array local
            $this->array_devueltos = $devueltos;

            return $this->array_devueltos;
        } else {
            return array();
        }
    }

    public function ventas_directas($db)
    {

        $id_distribuidor = $this->id_distribuidor_obtenido;

        if ($id_distribuidor > 0) {

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
                    LEFT JOIN inv_asignaciones a ON a.producto_id = edi.producto_id AND a.unidad_id = edi.unidad_id AND a.visible = "s"
                    LEFT JOIN inv_productos p ON p.id_producto = edi.producto_id
                    LEFT JOIN inv_unidades u ON u.id_unidad = edi.unidad_id
                    LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id      
    
                    WHERE edi.egreso_id IS NOT NULL AND 
                    te.estado = 3 AND te.distribuidor_id = ' . $id_distribuidor . ' AND te.distribuidor_estado IN ("VENTA") AND edi.promocion_id != 1
                    GROUP BY edi.producto_id')->fetch();

            $this->array_vendidos = $ventas_directas;

            return ($this->array_vendidos) ? $this->array_vendidos : array();
        } else {
            return array();
        }
    }


    public function devoluciones($db)
    {
        $editados = array();
        $ventas = array();
        //se obtiene el array de los editados
        $editados = $this->calculo_editados($db);
        //se depura repetidos sumando las cantidades y precios
        $editados_depurado = (count($editados) > 0) ? $this->eliminar_duplicado($editados) : array();
        //se obtiene el array de las ventas directas
        $ventas = $this->ventas_directas($db);

        //se resta los 2 array; array_editados - array_ventas_directas
        $this->array_devueltos = $this->restar_cantidades($ventas, $editados_depurado);

        return (count($this->array_devueltos) > 0) ? $this->array_devueltos : array();
    }


    public function total_devoluciones($db)
    {
        $devoluciones  = array();
        $devoluciones = $this->devoluciones($db);
        $valor_total = 0;
        $total_item = 0;
        foreach ($devoluciones as $key => $value) {
            $total_item = (($value['cantidad'] > 0) ? $value['cantidad'] : 0) * (($value['precio_actual']) ? $value['precio_actual'] : 0);
            $valor_total += $total_item;
        }
        return ($valor_total > 0) ? $valor_total : 0;
    }


    /** 
     * FUNCIONES DE eliminacion registros duplicados sumando sus cantidades e importes 
     * */
    private function eliminar_duplicado($array_productos = array())
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
                }
                $result[$contador] = $product;
                $contador++;
            }
        }

        return $result;
    }


    /**
     * se resta dos array cantidades y se fija  el precio base
     */
    private function restar_cantidades($productos_vendidos = array(), $devueltos = array())
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
                if ($unico_id["cantidad"] > 0) {
                    $nuevo_array[$contador] =  $unico_id;
                    $contador++;
                }
            }
        }
        return $nuevo_array;
    }
}


/* $resultado = new devoluciones_distribucion($db, 105);

//$array_edit = $resultado->calculo_editados($db);
$array_edit = $resultado->devoluciones($db);
$total_devueltos = $resultado->total_devoluciones($db);

var_dump($array_edit);
echo $total_devueltos;
var_dump($total_devueltos); */



