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



if (is_post()) {
    if (
        isset($_POST['id_cliente']) && isset($_POST['id_user']) &&
        isset($_POST['productos']) && isset($_POST['cantidades']) &&
        isset($_POST['unidad']) && isset($_POST['precios']) &&
        (count($_POST['cantidades']) > 0) && (count($_POST['productos']) > 0) &&
        (count($_POST['unidad']) > 0) && (count($_POST['precios']) > 0)
    ) {
        require config . '/database.php';
        //Habilita las funciones internas de notificación
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $nit = $_POST['nit'];
        $nombre_cliente = $_POST['nombre_factura'];
        $id_cliente = $_POST['id_cliente'];
        $productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
        $cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $sw = 0;
        try {

            //Se abre nueva transacción.
            $db->autocommit(false);
            $db->beginTransaction();

            //$verifica = $db->select('*')->from('inv_egresos')->where('cliente_id',$id_cliente)->where('fecha_egreso',date('Y-m-d'))->fetch_first();
            $id_user = $_POST['id_user'];
            $empleado = $db->select('persona_id,almacen_id')->from('sys_users')->where('id_user', $id_user)->fetch_first();
            $id_almacen = $empleado['almacen_id'];

            // PARA EDICION DEL CLIENTE
            $cliente_ver = $db->from('inv_clientes')->where('id_cliente', $id_cliente)->fetch_first();

            if ($cliente_ver['cliente'] != $_POST['cliente'] || $cliente_ver['nombre_factura'] != $_POST['nombre_factura'] || $cliente_ver['nit'] != $_POST['nit'] || isset($_POST['telefono'])) {
                $cliente_edit = $_POST['cliente'];
                $nom_fact_edit = $_POST['nombre_factura'];
                $nit_edit = $_POST['nit'];
                if (isset($_POST['telefono'])) {
                    $telefono_edit = $_POST['telefono'];
                    $db->where('id_cliente', $id_cliente)->update('inv_clientes', array('cliente' => $cliente_edit, 'nombre_factura' => $nom_fact_edit, 'nit' => $nit_edit, 'telefono' => $telefono_edit));
                }
                $db->where('id_cliente', $id_cliente)->update('inv_clientes', array('cliente' => $cliente_edit, 'nombre_factura' => $nom_fact_edit, 'nit' => $nit_edit));
            }
            // FIN PARA EDICION DEL CLIENTE


            if (validar_registro_cliente($db, 'cliente_id', $id_cliente, 'estadoe!=', 0)) {
                foreach ($productos as $key2 => $producto) {
                    $prod = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0) + IFNULL(sp.cantidad_promocion, 0) + IFNULL(spr.cantidad_venta_promo, 0)) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
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
                        LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.cantidad_unidad,')',w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
                        FROM (SELECT *
                                FROM inv_asignaciones q
                                    LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad AND q.visible = 's'
                                            ORDER BY u.unidad DESC) w GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id
                                            WHERE p.id_producto = " . $producto)->fetch_first();
                    if (($prod['cantidad_ingresos'] - $prod['cantidad_egresos']) < $cantidades[$key2]) {
                        $sin_stock[$sw] = $prod;
                        $sw = $sw + 1;
                    }
                }
                if ($sw < 1) {
                    $nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
                    $unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
                    $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
                    $promociones = (isset($_POST['promocion'])) ? $_POST['promocion'] : array();
                    $nro_registros = count($productos);
                    $monto_total = $_POST['monto_total'];
                    $des_venta = $_POST['descripcion_venta'];
                    $id_user = $_POST['id_user'];
                    //  $duracion = $_POST['duracion'];
                    $ubicacion = $_POST['ubicacion'];
                    $observacion = $_POST['prioridad'];
                    $hora_ini = $_POST['hora_inicial'];
                    $hora_fin = $_POST['hora_final'];

                    $horaInicio = new DateTime($hora_fin);
                    $horaTermino = new DateTime($hora_ini);

                    $duracion = $horaInicio->diff($horaTermino);
                    $duracion = $duracion->format('%H:%I:%s');

                    $empleado = $db->select('persona_id')->from('sys_users')->where('id_user', $id_user)->fetch_first();
                    $id_empleado = $empleado['persona_id'];

                    //buscamos la ruta que tiene
                    $ruta = $db->select('id_ruta')->from('gps_rutas')->where('empleado_id', $id_empleado)->where('dia', date('w'))->fetch_first();
                    ///////////////////////////////////////////////////////////////////////////
                    $DatosEgresoI = array();
                    $monto_totalA = $monto_total;
                    $DescuentoMonto = 0;
                    $DescuentoPorcentaje = 0;
                    $Fecha = date('Y-m-d');
                    $Promociones = $db->query("SELECT id_promocion,tipo,min_promo,descuento_promo,monto_promo,item_promo FROM inv_promociones_monto WHERE '{$Fecha}'>=fecha_ini AND '{$Fecha}'<=fecha_fin AND (tipo='3' OR tipo='2' OR tipo='4')")->fetch();
                    if ($Promociones) :
                        foreach ($Promociones as $Fila => $Promocion) :
                            if ($_POST['monto_total'] >= $Promocion['min_promo']) :
                                switch ($Promocion['tipo']):
                                    case 2:
                                        $DescuentoMonto += $Promocion['monto_promo'];
                                        $monto_totalA = $monto_totalA - $Promocion['monto_promo'];
                                        break;
                                    case 3:
                                        $DescuentoPorcentaje += $Promocion['descuento_promo'];
                                        $porcentaje = round((0.01 * $Promocion['descuento_promo']), Redondeo);
                                        $Aux = round(($monto_total * $porcentaje), Redondeo);
                                        $monto_totalA = $monto_totalA - $Aux;
                                        break;
                                    case 4:
                                        //1--Creme 30ml-- 4.800--Unidad--2--0
                                        $ProductoI = explode('--', $Promocion['item_promo']);
                                        $IdUnidad = $db->select('id_unidad')
                                            ->from('inv_unidades')
                                            ->where(array('unidad' => $ProductoI[3]))
                                            ->fetch_first();
                                        $DatosEgresoI[] = array(
                                            'precio' => $ProductoI[2], //o 0
                                            'unidad_id' => $IdUnidad['id_unidad'],
                                            'cantidad' => $ProductoI[4],
                                            'descuento' => '100',
                                            'producto_id' => $ProductoI[0],
                                            'egreso_id' => 1,
                                            'promocion_id' => $Promocion['id_promocion'],
                                            'asignacion_id' => '0'
                                        );
                                        break;
                                endswitch;
                            endif;
                        endforeach;
                    endif;
                    //////////////////////////////////////////////////////////////////
                    // Obtiene el numero de nota
                    $nro_factura = $db->query("SELECT COUNT(nro_factura) + 1 as nro_factura from inv_egresos where tipo = 'Venta' and provisionado = 'S'")->fetch_first();
                    $nro_factura = $nro_factura['nro_factura'];
                    $prom = 0;
                    $a = 0;
                    $b = 0;

                    foreach ($productos as $nro2 => $elemento2) {
                        $aux = $db->select('*')->from('inv_productos')->where('id_producto', $productos[$nro2])->fetch_first();
                        if ($aux['grupo'] != '') {
                            $a = $a + $precios[$nro2] * $cantidades[$nro2];
                            $b = $b + 1;
                        }
                    }
                    $monto_total = $monto_total - $a;

                    if (($nro_registros - $b) != 0) {

                        //se valida que no se tiene registro de clientes con preventas
                        $con = $db->select('*')->from('inv_egresos')->where(array('fecha_egreso' => date('Y-m-d'), 'cliente_id' => $id_cliente, 'estadoe>'=>0))->fetch_first();
                        if ($con) {
                        } else {

                            $credito = $cliente_ver['credito'];
                            // GUARDAR EGRESO CREDITO HGC (JOSE)
                            $egreso = array(
                                'fecha_egreso' => date('Y-m-d'),
                                'hora_egreso' => date('H:i:s'),
                                'tipo' => 'Venta',
                                'provisionado' => 'S',
                                'descripcion' => 'Venta de productos con preventa',
                                'nro_factura' => $nro_factura,
                                'nro_autorizacion' => '',
                                'codigo_control' => '',
                                'fecha_limite' => '0000-00-00',
                                'monto_total' => $monto_total,
                                'cliente_id' => $id_cliente,
                                'nit_ci' => $nit,
                                'nombre_cliente' => $nombre_cliente,
                                'nro_registros' => $nro_registros - $b,
                                'dosificacion_id' => 0,
                                'almacen_id' => $id_almacen,
                                'empleado_id' => $id_empleado,
                                'coordenadas' => $ubicacion,
                                'observacion' => $observacion,
                                'estadoe' => 2,
                                'duracion' => $duracion,
                                'descripcion_venta' => $des_venta,
                                'ruta_id' => $ruta['id_ruta'],
                                'plan_de_pagos' => ($credito == '1' || $credito == 1) ? 'si' : 'no', //$plan
                                'descuento_porcentaje' => 0,
                                'descuento_bs' => 0,
                                'monto_total_descuento' => $monto_total,
                            );
                            $id = $db->insert('inv_egresos', $egreso);

                            //Cuentas manejo de HGC
                            if ($credito == '1' || $credito == 1) {

                                // Instancia el ingreso
                                $ingresoPlan = array(
                                    'movimiento_id' => $id,
                                    'interes_pago' => 0,
                                    'tipo' => 'Egreso'
                                );
                                // Guarda la informacion del ingreso general
                                $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);

                                $Date = date('Y-m-d');
                                $Dias = ' + ' . $cliente_ver['dias'] . ' days';
                                $Fecha_pago = date('Y-m-d', strtotime($Date . $Dias));

                                $detallePlan = array(
                                    'nro_cuota' => 1,
                                    'pago_id' => $ingreso_id_plan,
                                    'fecha' => $Fecha_pago,
                                    'fecha_pago' => $Fecha_pago,
                                    'monto' => $monto_total,
                                    'tipo_pago' => '',
                                    'empleado_id' => $id_empleado,
                                    'estado'  => '0'
                                );
                                // Guarda la informacion
                                $db->insert('inv_pagos_detalles', $detallePlan);
                            }
                        }
                    }

                    // Recorre los productos
                    foreach ($precios as $nro => $elemento) {
                        $id_unidade = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades u', 'a.unidad_id=u.id_unidad AND a.visible = "s"')->where(array('u.unidad' => $unidad[$nro], 'a.producto_id' => $productos[$nro], 'a.visible' => 's'))->fetch_first();
                        if ($id_unidade) {
                            $id_unidad = $id_unidade['id_unidad'];
                            $cantidad = $cantidades[$nro] * $id_unidade['cantidad_unidad'];
                        } else {
                            $id_uni = $db->select('id_unidad')->from('inv_unidades')->where('unidad', $unidad[$nro])->fetch_first();
                            $id_unidad = $id_uni['id_unidad'];
                            $cantidad = $cantidades[$nro];
                        }
                        $aux = $db->select('*')->from('inv_productos')->where('id_producto', $productos[$nro])->fetch_first();
                        if ($aux['grupo'] == '' && $monto_total != 0) {
                            if ($promociones[$nro] != '') {

                                /////////////////////////////////////////////////////////////////////////////////////////
                                $Lote = '';
                                $CantidadAux = $cantidad;
                                $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                                foreach ($Detalles as $Fila => $Detalle) :
                                    if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                        $Datos = array(
                                            'lote_cantidad' => 0,
                                        );
                                        $Cant = $Detalle['lote_cantidad'];
                                    elseif ($CantidadAux > 0) :
                                        $Datos = array(
                                            'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                        );
                                        $Cant = $CantidadAux;
                                    else :
                                        break;
                                    endif;
                                    $Condicion = array(
                                        'id_detalle' => $Detalle['id_detalle'],
                                    );
                                    $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                                    $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                                    $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                                endforeach;
                                $Lote = trim($Lote, ',');
                                /////////////////////////////////////////////////////////////////////////////////////////
                                // Forma el detalle
                                $prod = $productos[$nro];
                                $promos = $db->select('producto_id, precio, unidad_id, cantidad, descuento , id_promocion as egreso_id, cantidad as promocion_id')
                                    ->from('inv_promociones')->where('id_promocion', $prod)->fetch();
                                $detalle = array(
                                    'cantidad' => $cantidades[$nro],
                                    'precio' => $precios[$nro],
                                    'descuento' => 0,
                                    'unidad_id' => 11,
                                    'producto_id' => $productos[$nro],
                                    'egreso_id' => $id,
                                    'promocion_id' => 1,
                                    'lote' => $Lote
                                );
                                // Guarda la informacion
                                $db->insert('inv_egresos_detalles', $detalle);
                                foreach ($promos as $key => $promo) {
                                    /////////////////////////////////////////////////////////////////////////////////////////
                                    $Lote = '';
                                    $CantidadAux = $promo['cantidad'] * $cantidades[$nro];
                                    $prodpromo = $promo['producto_id'];
                                    $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$prodpromo' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                                    foreach ($Detalles as $Fila => $Detalle) :
                                        if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                            $Datos = array(
                                                'lote_cantidad' => 0
                                            );
                                            $Cant = $Detalle['lote_cantidad'];
                                        elseif ($CantidadAux > 0) :
                                            $Datos = array(
                                                'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux
                                            );
                                            $Cant = $CantidadAux;
                                        else :
                                            break;
                                        endif;
                                        $Condicion = array(
                                            'id_detalle' => $Detalle['id_detalle']
                                        );
                                        $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                                        $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                                        $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                                    endforeach;
                                    $Lote = trim($Lote, ',');
                                    /////////////////////////////////////////////////////////////////////////////////////////
                                    $promo['lote'] = $Lote;
                                    $promo['egreso_id'] = $id;
                                    $promo['promocion_id'] = $productos[$nro];
                                    $promo['cantidad'] = $promo['cantidad'] * $cantidades[$nro];
                                    // Guarda la informacion
                                    $db->insert('inv_egresos_detalles', $promo);
                                }
                            } else {
                                /////////////////////////////////////////////////////////////////////////////////////////
                                $Lote = '';
                                $CantidadAux = $cantidad;
                                $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                                foreach ($Detalles as $Fila => $Detalle) :
                                    if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                        $Datos = array(
                                            'lote_cantidad' => 0,
                                        );
                                        $Cant = $Detalle['lote_cantidad'];
                                    elseif ($CantidadAux > 0) :
                                        $Datos = array(
                                            'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                        );
                                        $Cant = $CantidadAux;
                                    else :
                                        break;
                                    endif;
                                    $Condicion = array(
                                        'id_detalle' => $Detalle['id_detalle'],
                                    );
                                    $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                                    $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                                    $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                                endforeach;
                                $Lote = trim($Lote, ',');
                                /////////////////////////////////////////////////////////////////////////////////////////
                                // Forma el detalle
                                $detalle = array(
                                    'cantidad' => $cantidad,
                                    'precio' => $precios[$nro],
                                    'descuento' => 0,
                                    'unidad_id' => $id_unidad,
                                    'producto_id' => $productos[$nro],
                                    'egreso_id' => $id,
                                    'lote' => $Lote
                                );
                                // Guarda la informacion
                                $db->insert('inv_egresos_detalles', $detalle);
                            }
                        } else {
                            $nro_factura2 = $db->query("select count(id_egreso) + 1 as nro_factura from inv_egresos where tipo = 'Venta' and provisionado = 'S'")->fetch_first();
                            $nro_factura2 = $nro_factura2['nro_factura'];
                            $egreso2 = array(
                                'fecha_egreso' => date('Y-m-d'),
                                'hora_egreso' => date('H:i:s'),
                                'tipo' => 'Venta',
                                'provisionado' => 'S',
                                'descripcion' => 'Venta de productos con preventa',
                                'nro_factura' => $nro_factura2,
                                'nro_autorizacion' => '',
                                'codigo_control' => '',
                                'fecha_limite' => '0000-00-00',
                                'monto_total' => $precios[$nro] * $cantidades[$nro],
                                'cliente_id' => $id_cliente,
                                'nit_ci' => $nit,
                                'nombre_cliente' => $nombre_cliente,
                                'nro_registros' => 1,
                                'dosificacion_id' => 0,
                                'almacen_id' => $id_almacen,
                                'empleado_id' => $id_empleado,
                                'coordenadas' => $ubicacion,
                                'observacion' => $observacion,
                                'estadoe' => 2,
                                'duracion' => $duracion,
                                'grupo' => $aux['grupo'],
                                'ruta_id' => $ruta['id_ruta']
                            );
                            $id2 = $db->insert('inv_egresos', $egreso2);
                            /////////////////////////////////////////////////////////////////////////////////////////
                            $Lote = '';
                            $CantidadAux = $cantidad;
                            $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                            foreach ($Detalles as $Fila => $Detalle) :
                                if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                    $Datos = array(
                                        'lote_cantidad' => 0,
                                    );
                                    $Cant = $Detalle['lote_cantidad'];
                                elseif ($CantidadAux > 0) :
                                    $Datos = array(
                                        'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                    );
                                    $Cant = $CantidadAux;
                                else :
                                    break;
                                endif;
                                $Condicion = array(
                                    'id_detalle' => $Detalle['id_detalle'],
                                );
                                $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                                $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                                $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                            endforeach;
                            $Lote = trim($Lote, ',');
                            /////////////////////////////////////////////////////////////////////////////////////////

                            $detalle2 = array(
                                'cantidad' => $cantidad,
                                'precio' => $precios[$nro],
                                'descuento' => 0,
                                'unidad_id' => $id_unidad,
                                'producto_id' => $productos[$nro],
                                'egreso_id' => $id2,
                                'lote' => $Lote
                            );
                            $db->insert('inv_egresos_detalles', $detalle2);
                        }
                    }

                    /**
                     * se inserta la entrega y su detalle en la tabla de inv_egresos_entregas 
                     * con la finalidad de generar reportes mas exactos
                     */
                    
                    $id_movimiento = registros_historial($db, '_inicio', 'inv_egresos', 'id_egreso', $id, '', '', $id_user, 'SI', 0);
                    $movimiento = registros_historial($db, '_inicio', 'inv_egresos_detalles', 'egreso_id', $id, '', '', $id_user, 'NO', $id_movimiento);

                    if ($id || $id2) {
                        for ($i = 0; $i < count($DatosEgresoI); ++$i) :
                            $producto_id = $DatosEgresoI[$i]['producto_id'];
                            $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$producto_id' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
                            $Lote = '';
                            $CantidadAux = $DatosEgresoI[$i]['cantidad'];
                            foreach ($Detalles as $Fila => $Detalle) :
                                if ($CantidadAux >= $Detalle['lote_cantidad']) :
                                    $Datos = [
                                        'lote_cantidad' => 0,
                                    ];
                                    $Cant = $Detalle['lote_cantidad'];
                                elseif ($CantidadAux > 0) :
                                    $Datos = [
                                        'lote_cantidad' => $Detalle['lote_cantidad'] - $CantidadAux,
                                    ];
                                    $Cant = $CantidadAux;
                                else :
                                    break;
                                endif;
                                $Condicion = [
                                    'id_detalle' => $Detalle['id_detalle'],
                                ];
                                $db->where($Condicion)->update('inv_ingresos_detalles', $Datos);
                                $CantidadAux = $CantidadAux - $Detalle['lote_cantidad'];
                                $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                            endforeach;
                            $Lote = trim($Lote, ',');
                            $DatosEgresoI[$i]['egreso_id'] = $id;
                            $DatosEgresoI[$i]['lote'] = $Lote;
                            $db->insert('inv_egresos_detalles', $DatosEgresoI[$i]);
                        endfor;

                        //se cierra transaccion
                        $db->commit();
                        $respuesta = array(
                            'estado' => 's',
                            'estadoe' => 2
                        );
                        echo json_encode($respuesta);
                    } else {
                        //se cierra transaccion
                        $db->commit();
                        echo json_encode(array('estado' => 'n', 'msg'=> 'No guardo'));
                    }
                } else {
                    //se cierra transaccion
                    $db->commit();
                    echo json_encode(array(
                        'estado' => 'n',
                        'msg' => 'No tiene stock'
                    ));
                }
            } else {
                //se cierra transaccion
                $db->commit();
                echo json_encode(array(
                    'estado' => 'n',
                    'msg' => 'Cliente con movimiento.'
                ));
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
        echo json_encode(array(
            'estado' => 'n',
            'msg' => 'datos no definidos.'
        ));
    }
} else {
    echo json_encode(array(
        'estado' => 'n',
        'msg' => 'Metodo no definido'
    ));
}
