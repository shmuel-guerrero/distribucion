<?php

/**
 * SimplePHP - Simple Framework PHP
 * 
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 */
// Verifica si es una peticion ajax y post
/*
print_r($_POST);
die;
*/
if (is_ajax() && is_post()) {
    // Verifica la existencia de los datos enviados
    if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && isset($_POST['descuentos']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
        // Importa la libreria para convertir el numero a letra
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // Obtiene los datos de la proforma
        $nit_ci = trim($_POST['nit_ci']);
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $telefono = trim($_POST['telefono_cliente']);

        $observacion = trim($_POST['observacion']);
        $direccion = trim($_POST['direccion']);
        $atencion = trim($_POST['atencion']);
        $id_cliente = trim($_POST['id_cliente']);
        $prioridad = trim($_POST['prioridad']);
        $productos = (isset($_POST['productos'])) ? $_POST['productos'] : array();
        $nombres = (isset($_POST['nombres'])) ? $_POST['nombres'] : array();
        $cantidades = (isset($_POST['cantidades'])) ? $_POST['cantidades'] : array();
        $unidad = (isset($_POST['unidad'])) ? $_POST['unidad'] : array();
        $precios = (isset($_POST['precios'])) ? $_POST['precios'] : array();
        $descuentos = (isset($_POST['descuentos'])) ? $_POST['descuentos'] : array();
        $nro_registros = trim($_POST['nro_registros']);
        $monto_total = trim($_POST['monto_total']);
        $almacen_id = trim($_POST['almacen_id']);
        $adelanto = trim($_POST['adelanto']);
        $ruta = trim($_POST['ruta']);
        //Cuentas
        $tipo_pago = (isset($_POST['tipo_pago'])) ? trim($_POST['tipo_pago']) : 'Efectivo';
        $nro_factura = trim($_POST['nro_factura']);
        $nro_autorizacion = trim($_POST['nro_autorizacion']);

        $descuento_porc = isset($_POST['descuento_porc'])?trim($_POST['descuento_porc']):0;
        $descuento_bs = trim($_POST['descuento_bs']);
        $total_importe_descuento =$_POST['total_importe_descuento']==''?0:trim($_POST['total_importe_descuento']);

        $nro_cuentas = trim($_POST['nro_cuentas']);
        $plan = (isset($_POST['forma_pago'])) ? trim($_POST['forma_pago']) : '';
        $plan = ($plan == "2") ? "si" : "no";
        if ($plan == "si") {

            $fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
            $cuotas = (isset($_POST['cuota'])) ? $_POST['cuota'] : array();
        }

        if ($_POST['reserva']) {
            $reserva = 'si';
        } else {
            $reserva = 'no';
        }

        //obtiene al cliente


        if ($id_cliente != 0) {
            $cliente = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombre_cliente, 'nit' => $nit_ci))->fetch_first();
            if (!$cliente) {
                $cl = array(
                    'cliente' => $nombre_cliente,
                    'nit' => $nit_ci,
                    'telefono' => $telefono,
                    'ubicacion' => $atencion,
                    'direccion' => $direccion
                );
                $id_cliente = $db->insert('inv_clientes', $cl);
                // Guarda Historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'c',
                    'nivel' => 'l',
                    'direccion' => '?/preventas/guardar',
                    'detalle' => 'Se creo cliente con identificador numero ' . $id_cliente,
                    'usuario_id' => $_SESSION[user]['id_user']
                );

                $db->insert('sys_procesos', $data);
            } else {
                $id_cliente = $cliente['id_cliente'];
            }
        }

        // Obtiene el numero de nota
        $nro_factura = $db->query("select count(id_egreso) + 1 as nro_factura from inv_egresos where tipo = 'Venta' and provisionado = 'S'")->fetch_first();
        $nro_factura = $nro_factura['nro_factura'];

        // Define la variable de subtotales
        $subtotales = array();

        // Obtiene la moneda
        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $monto_total);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));

        // Obtiene el numero de la proforma
        $nro_proforma = $db->query("select ifnull(max(nro_proforma), 0) + 1 as nro_proforma from inv_proformas")->fetch_first();
        $nro_proforma = $nro_proforma['nro_proforma'];
        $a = 0;
        $b = 0;
        foreach ($productos as $nro2 => $elemento2) {
            $aux = $db->select('*')->from('inv_productos')->where('id_producto', $elemento2)->fetch_first();
            if ($aux['grupo'] != '') {
                $a = $a + $precios[$nro2] * $cantidades[$nro2];
                $b = $b + 1;
            }
        }
        $monto_total = $monto_total - $a;

        $proforma_id=0;

        if (($nro_registros - $b) != 0) {
            // Instancia la proforma
            $proforma = array(
                'fecha_egreso' => date('Y-m-d'),
                'hora_egreso' => date('H:i:s'),
                'tipo' => 'Venta',
                'provisionado' => 'S',
                'descripcion' => 'Venta de productos con preventa',
                'nro_factura' => $nro_factura,
                'nro_autorizacion' => '',
                'codigo_control' => '',
                'fecha_limite' => '1000-01-01',
                'monto_total' => $monto_total,
                'cliente_id' => $id_cliente,
                'nit_ci' => $nit_ci,
                'nombre_cliente' => $nombre_cliente,
                'nro_registros' => $nro_registros - $b,
                'dosificacion_id' => 0,
                'almacen_id' => $almacen_id,
                'empleado_id' => $_user['persona_id'],
                'coordenadas' => $atencion,
                'observacion' => $prioridad,
                'estadoe' => 2,
                'descripcion_venta' => $observacion,
                'ruta_id' => $ruta,
                'plan_de_pagos' => $plan,
                'estado' => 1,
                'descuento_porcentaje' => $descuento_porc,
                'descuento_bs' => $descuento_bs,
                'monto_total_descuento' => $total_importe_descuento,
            );

            // Guarda la informacion
            $proforma_id = $db->insert('inv_egresos', $proforma);

            // Guarda Historial
            $data = array(
                'fecha_proceso' => date("Y-m-d"),
                'hora_proceso' => date("H:i:s"),
                'proceso' => 'c',
                'nivel' => 'l',
                'direccion' => '?/almacenes/guardar',
                'detalle' => 'Se creo inventario egreso con identificador numero ' . $proforma_id,
                'usuario_id' => $_SESSION[user]['id_user']
            );

            $db->insert('sys_procesos', $data);
        }

        // Recorre los productos
        foreach ($productos as $nro => $elemento) {
            $id_unidade = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades u', 'a.unidad_id=u.id_unidad AND a.visible = "s" ')->where(array('u.unidad' => $unidad[$nro], 'a.producto_id' => $productos[$nro]))->fetch_first();
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

                if ($aux['promocion'] == 'si') {
                    // Forma el detalle
                    $prod = $productos[$nro];
                    $promos = $db->select('producto_id, precio, unidad_id, cantidad, descuento , id_promocion as egreso_id, cantidad as promocion_id')->from('inv_promociones')->where('id_promocion', $prod)->fetch();
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote = '';
                    $CantidadAux = $cantidades[$nro];
                    $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC LIMIT 3")->fetch();
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
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $detalle = array(
                        'cantidad' => $cantidades[$nro],
                        'precio' => $precios[$nro],
                        'descuento' => 0,
                        'unidad_id' => 11,
                        'producto_id' => $productos[$nro],
                        'egreso_id' => $proforma_id,
                        'promocion_id' => 1,
                        'lote' => $Lote
                    );
                    // Guarda la informacion
                    $id_detalle = $db->insert('inv_egresos_detalles', $detalle);

                    // Guarda Historial
                    $data = array(
                        'fecha_proceso' => date("Y-m-d"),
                        'hora_proceso' => date("H:i:s"),
                        'proceso' => 'c',
                        'nivel' => 'l',
                        'direccion' => '?/preventas/guardar',
                        'detalle' => 'Se creó inventario egreso detalle con identificador número ' . $id_detalle,
                        'usuario_id' => $_SESSION[user]['id_user']
                    );

                    $db->insert('sys_procesos', $data);

                    foreach ($promos as $key => $promo) {
                        /////////////////////////////////////////////////////////////////////////////////////////
                        $Lote = '';
                        $CantidadAux = $promo['cantidad'] * $cantidades[$nro];
                        $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC LIMIT 3")->fetch();
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
                            $Lote .= $Detalle['lote'] . ',';
                        endforeach;
                        $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                        /////////////////////////////////////////////////////////////////////////////////////////
                        $promo['lote'] = $Lote;
                        $promo['egreso_id'] = $proforma_id;
                        $promo['promocion_id'] = $productos[$nro];
                        $promos[$key]['cantidad'] = $promo['cantidad'] * $cantidades[$nro];
                        // Guarda la informacion
                        $id_detalle = $db->insert('inv_egresos_detalles', $promo);
                        // Guarda Historial
                        $data = array(
                            'fecha_proceso' => date("Y-m-d"),
                            'hora_proceso' => date("H:i:s"),
                            'proceso' => 'c',
                            'nivel' => 'l',
                            'direccion' => '?/preventas/guardar',
                            'detalle' => 'Se creó inventario egreso detalle con identificador número ' . $id_detalle,
                            'usuario_id' => $_SESSION[user]['id_user']
                        );
                        $db->insert('sys_procesos', $data);
                    }
                } else {
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote = '';
                    $CantidadAux = $cantidad;
                    $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC LIMIT 3")->fetch();
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
                    /////////////////////////////////////////////////////////////////////////////////////////
                    // Forma el detalle
                    $detalle = array(
                        'cantidad' => $cantidad,
                        'unidad_id' => $id_unidad,
                        'precio' => $precios[$nro],
                        'descuento' => $descuentos[$nro],
                        'producto_id' => $productos[$nro],
                        'egreso_id' => $proforma_id,
                        'lote' => $Lote
                    );

                    // Genera los subtotales
                    $subtotales[$nro] = number_format($precios[$nro] * $cantidades[$nro], 2, '.', '');

                    // Guarda la informacion
                    $id_detalle = $db->insert('inv_egresos_detalles', $detalle);
                    // Guarda Historial
                    $data = array(
                        'fecha_proceso' => date("Y-m-d"),
                        'hora_proceso' => date("H:i:s"),
                        'proceso' => 'c',
                        'nivel' => 'l',
                        'direccion' => '?/preventas/guardar',
                        'detalle' => 'Se creó inventario egreso detalle con identificador número ' . $id_detalle,
                        'usuario_id' => $_SESSION[user]['id_user']
                    );

                    $db->insert('sys_procesos', $data);
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
                    'nit_ci' => $nit_ci,
                    'nombre_cliente' => $nombre_cliente,
                    'nro_registros' => 1,
                    'dosificacion_id' => 0,
                    'almacen_id' => $almacen_id,
                    'empleado_id' => $_user['persona_id'],
                    'coordenadas' => $atencion,
                    'observacion' => $prioridad,
                    'descripcion_venta' => $observacion,
                    'estadoe' => 2,
                    'grupo' => $aux['grupo'],
                    'ruta_id' => $ruta,
                    'plan_de_pagos' => $plan,
                    'descuento_porcentaje' => $descuento_porc,
                    'descuento_bs' => $descuento_bs,
                    'monto_total_descuento' => $total_importe_descuento,
                );
                $id2 = $db->insert('inv_egresos', $egreso2);
                $proforma_id=$id2;
                /////////////////////////////////////////////////////////////////////////////////////////
                $Lote = '';
                $CantidadAux = $cantidad;
                $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC LIMIT 3")->fetch();
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
                $id_detalle = $db->insert('inv_egresos_detalles', $detalle2);

                // Guarda Historial
                $data = array(
                    'fecha_proceso' => date("Y-m-d"),
                    'hora_proceso' => date("H:i:s"),
                    'proceso' => 'c',
                    'nivel' => 'l',
                    'direccion' => '?/preventas/guardar',
                    'detalle' => 'Se creó inventario egreso detalle con identificador número ' . $id_detalle,
                    'usuario_id' => $_SESSION[user]['id_user']
                );

                $db->insert('sys_procesos', $data);
            }
        }


        // Instancia la respuesta
        $respuesta = array(
            'papel_ancho' => 10,
            'papel_alto' => 25,
            'papel_limite' => 576,
            'empresa_nombre' => $_institution['nombre'],
            'empresa_sucursal' => 'SUCURSAL Nº 1',
            'empresa_direccion' => $_institution['direccion'],
            'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
            'empresa_ciudad' => 'EL ALTO - BOLIVIA',
            'empresa_actividad' => $_institution['razon_social'],
            'empresa_nit' => $_institution['nit'],
            'id_egreso' => $proforma_id
        );


        //Cuentas
        if ($plan == "si") {
            // Instancia el ingreso
            $ingresoPlan = array(
                'movimiento_id' => $proforma_id,
                'interes_pago' => 0,
                'tipo' => 'Egreso'
            );
            // Guarda la informacion del ingreso general
            $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);

            $nro_cuota = 0;
            for ($nro2 = 0; $nro2 < $nro_cuentas; $nro2++) {
                if (isset($fechas[$nro2])) {
                    $fecha_format = $fechas[$nro2];
                } else {
                    $fecha_format = "00-00-0000";
                }

                $vfecha = explode("-", $fecha_format);

                if (count($vfecha) > 3) {
                    $fecha_format = $vfecha[2] . "-" . $vfecha[1] . "-" . $vfecha[0];
                } else {
                    $fecha_format = "0000-00-00";
                }

                $nro_cuota++;
                if ($nro2 == 0) {
                    $detallePlan = array(
                        'nro_cuota' => $nro_cuota,
                        'pago_id' => $ingreso_id_plan,
                        'fecha' => $fecha_format,
                        'fecha_pago' => $fecha_format,
                        'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2] : 0,
                        'tipo_pago' => $tipo_pago,
                        'empleado_id' => $_user['persona_id'],
                        'estado'  => '1'
                    );
                } else {
                    $detallePlan = array(
                        'nro_cuota' => $nro_cuota,
                        'pago_id' => $ingreso_id_plan,
                        'fecha' => $fecha_format,
                        'fecha_pago' => $fecha_format,
                        'monto' => (isset($cuotas[$nro2])) ? $cuotas[$nro2] : 0,
                        'tipo_pago' => '',
                        'empleado_id' => $_user['persona_id'],
                        'estado'  => '0'
                    );
                }
                // Guarda la informacion
                $db->insert('inv_pagos_detalles', $detallePlan);
            }
        }

        // Envia respuesta
        echo json_encode($respuesta);
    } else {
        // Envia respuesta
        echo 'error';
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
