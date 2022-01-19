<?php

/**
 * SimplePHP - Simple Framework PHP
 * @package  SimplePHP
 * @author   Wilfredo Nina <wilnicho@hotmail.com>
 **/
if (is_ajax() && is_post()) {
    if (isset($_POST['nit_ci'])) {
        $cliente =           trim($_POST['cliente']);
        $nit_ci =            trim($_POST['nit_ci']);
        $id_cliente =        trim($_POST['id_cliente']);
        $nombre_cliente =    trim($_POST['nombre_cliente']);
        $adelanto =          trim($_POST['adelanto']);
        $telefono_cliente =  trim($_POST['telefono_cliente']);
        $atencion =          trim($_POST['atencion']);
        $direccion =         trim($_POST['direccion']);
        $prioridad =         trim($_POST['prioridad']);
        $ruta =              trim($_POST['ruta']);
        $observacion =       trim($_POST['observacion']);
        $idordendetalle =    isset($_POST['idordendetalle']) ?    $_POST['idordendetalle'] : [];
        $idcontrol =         isset($_POST['idcontrol']) ?         $_POST['idcontrol'] :      [];
        $productos =         isset($_POST['productos']) ?         $_POST['productos'] :      [];
        $nombres =           isset($_POST['nombres']) ?           $_POST['nombres'] :        [];
        $cantidades =        isset($_POST['cantidades']) ?        $_POST['cantidades'] :     [];
        $cantidades_pres =   isset($_POST['cantidades_pres']) ?   $_POST['cantidades_pres'] : [];
        $opcion_material =   isset($_POST['opcion_material']) ?   $_POST['opcion_material'] : [];
        $precio_material =   isset($_POST['precio_material']) ?   $_POST['precio_material'] : [];
        $unidad =            isset($_POST['unidad']) ?            $_POST['unidad'] :         [];
        $precios =           isset($_POST['precios']) ?           $_POST['precios'] :        [];
        $descuentos =        isset($_POST['descuentos']) ?        $_POST['descuentos'] :     [];
        $idmaterialm =       isset($_POST['idmaterialm']) ?       $_POST['idmaterialm'] :    [];
        $almacen_id =        trim($_POST['almacen_id']);
        $nro_registros =     trim($_POST['nro_registros']);
        $monto_total =       trim($_POST['monto_total']);
        $nro_cuentas =       trim($_POST['nro_cuentas']);


        $plan = (isset($_POST['forma_pago'])) ? trim($_POST['forma_pago']) : '';
        $plan = ($plan == '2') ? 'si' : 'no';
        if ($plan == 'si') :
            $fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : [];
            $cuotas = (isset($_POST['cuota'])) ? $_POST['cuota'] : [];
        endif;

        if ($id_cliente != 0) :
            $cliente = $db->select('*')->from('inv_clientes')->where(array('cliente' => $nombre_cliente, 'nit' => $nit_ci))->fetch_first();
            if (!$cliente) :
                $cl = array(
                    'cliente'   => $nombre_cliente,
                    'nit'       => $nit_ci,
                    'telefono'  => $telefono,
                    'ubicacion' => $atencion,
                    'direccion' => $direccion
                );
                $id_cliente = $db->insert('inv_clientes', $cl);
            endif;
        else :
            $id_cliente = $cliente['id_cliente'];
        endif;
        // Obtiene la moneda
        $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
        $moneda = ($moneda) ? $moneda['moneda'] : '';
        // Obtiene el numero de nota
        $nro_factura = $db->query("select count(id_egreso) + 1 as nro_factura from inv_egresos where tipo = 'Venta' and provisionado = 'S'")->fetch_first();
        $nro_factura = $nro_factura['nro_factura'];

        $IdOrdenSalida = trim($_POST['id_orden_salida']);

        if (count($productos) > 0) :
            $egreso = array(
                'fecha_egreso'      => date('Y-m-d'),
                'hora_egreso'       => date('H:i:s'),
                'tipo'              => 'Venta',
                'provisionado'      => 'S',
                'descripcion'       => 'Venta de productos con preventa',
                'nro_factura'       => '', //$nro_factura,
                'nro_autorizacion'  => '',
                'codigo_control'    => '',
                'fecha_limite'      => '0000-00-00',
                'monto_total'       => $monto_total,
                'cliente_id'        => $id_cliente,
                'nit_ci'            => $nit_ci,
                'nombre_cliente'    => $nombre_cliente,
                'nro_registros'     => $nro_registros,
                'dosificacion_id'   => 0,
                'almacen_id'        => $almacen_id,
                'empleado_id'       => $_user['persona_id'],
                'coordenadas'       => $atencion,
                'observacion'       => $prioridad,
                'plan_de_pagos'     => $plan,
                'estado'            => 1,
                'estadoe'           => 3,
                'descripcion_venta' => $observacion,
                'ruta_id'           => $ruta,
                'ordenes_salidas_id' => $IdOrdenSalida,
            );
            $id_egreso = $db->insert('inv_egresos', $egreso);

            foreach ($productos as $nro => $elemento) :
                //Registrar Detalle Egreso
                $IdUnidad = $db->query("SELECT id_unidad FROM inv_unidades WHERE unidad='{$unidad[$nro]}' LIMIT 1")->fetch_first();
                $IdUnidad = ($IdUnidad['id_unidad']) ? $IdUnidad['id_unidad'] : 0 ;
                $detalleegreso = [
                    'precio'        => $precios[$nro],
                    'unidad_id'     => $IdUnidad,
                    'cantidad'      => $cantidades[$nro],
                    'descuento'     => 0,
                    'producto_id'   => $elemento[$nro],
                    'egreso_id'     => $id_egreso,
                    'promocion_id'  => 0,
                    'asignacion_id' => 0,
                    'lote'          => null,
                ];
                $db->insert('inv_egresos_detalles', $detalleegreso);
                //Actualizar Detalle Orden
                $Cantidad = $db->query("SELECT cantidad FROM inv_ordenes_detalles WHERE id_orden_detalle='{$idordendetalle[$nro]}' LIMIT 1")->fetch_first();
                $Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
                $Cantidad = $Cantidad - $cantidades[$nro];
                $Campos = [
                    'cantidad'          => $Cantidad,
                ];
                $Condicion = [
                    'id_orden_detalle'  => $idordendetalle[$nro]
                ];
                $db->where($Condicion)->update('inv_ordenes_detalles', $Campos);
                if ($idcontrol[$nro] != 0 && $cantidades_pres[$nro] != 0) :
                    //Registrar Detalle Control
                    $detallecontrol = [
                        'id_materiales' => $idmaterialm[$nro],
                        'tipo' => 'cliente',
                        'cantidad' => $cantidades_pres[$nro],
                        'stock' => 'egreso',
                        'cliente_id' => $id_cliente,
                        'empleado_id' => 1,
                        'fecha_control' => date('Y-m-d'),
                        'estado' => $opcion_material[$nro],
                        'proveedor' => '',
                        'ordenes_salidas_id' => 0,
                        'egreso_id' => $id_egreso,
                        //'cantidad_inicial'=>0,
                    ];
                    $db->insert('inv_control', $detallecontrol);
                    //Actualizar Control
                    $Cantidad = $db->query("SELECT cantidad FROM inv_control WHERE id_control='{$idcontrol[$nro]}' LIMIT 1")->fetch_first();
                    $Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
                    $Cantidad = $Cantidad - $cantidades_pres[$nro];
                    $Campos = [
                        'cantidad'      => $Cantidad,
                    ];
                    $Condicion = [
                        'id_control'    => $idcontrol[$nro]
                    ];
                    $db->where($Condicion)->update('inv_control', $Campos);

                    if ($idmaterialm[$nro] != 0) :
                        $MaterialesStock = $db->query("SELECT id_materiales_stock,stock FROM inv_materiales_stock WHERE almacen_id='{$almacen_id}' AND materiales_id='{$idmaterialm[$nro]}' LIMIT 1")->fetch_first();
                        $Cantidad = $MaterialesStock['stock'] - $cantidades_pres[$nro];
                        $Campos = [
                            'stock'      => $Cantidad,
                        ];
                        $Condicion = [
                            'id_materiales_stock'    => $MaterialesStock['id_materiales_stock']
                        ];
                        $db->where($Condicion)->update('inv_materiales_stock', $Campos);
                    endif;
                endif;
            endforeach;

            //Cuentas
            if ($plan=='si'):
                // Instancia el ingreso
                $ingresoPlan=array(
                        'movimiento_id' => $id_egreso,
                        'interes_pago'  => 0,
                        'tipo'          => 'Egreso',
                );
                // Guarda la informacion del ingreso general
                $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);
                $nro_cuota = 0;
                for($nro2=0;$nro2<$nro_cuentas;++$nro2):
                    if (isset($fechas[$nro2]))
                        $fecha_format = $fechas[$nro2];
                    else
                        $fecha_format = '00-00-0000';
                    $vfecha = explode("-", $fecha_format);
                    if (count($vfecha) == 3)
                        $fecha_format = $vfecha[2] . "-" . $vfecha[1] . "-" . $vfecha[0];
                    else
                        $fecha_format = '0000-00-00';
                    $nro_cuota++;
                    if ($nro2 == 0):
                        $detallePlan = [
                            'nro_cuota'     => $nro_cuota,
                            'pago_id'       => $ingreso_id_plan,
                            'fecha'         => $fecha_format,
                            'fecha_pago'    => $fecha_format,
                            'monto'         => (isset($cuotas[$nro2])) ? $cuotas[$nro2] : 0,
                            'tipo_pago'     => $tipo_pago,
                            'empleado_id'   => $_user['persona_id'],
                            'estado'        => '1'
                        ];
                    else:
                        $detallePlan = [
                            'nro_cuota'     => $nro_cuota,
                            'pago_id'       => $ingreso_id_plan,
                            'fecha'         => $fecha_format,
                            'fecha_pago'    => $fecha_format,
                            'monto'         => (isset($cuotas[$nro2])) ? $cuotas[$nro2] : 0,
                            'tipo_pago'     => '',
                            'empleado_id'   => $_user['persona_id'],
                            'estado'        => '0'
                        ];
                    endif;
                    // Guarda la informacion
                    $db->insert('inv_pagos_detalles', $detallePlan);
                endfor;
            endif;

            echo json_encode(true);
            die();
        endif;
        echo 'error';
    } else {
        // Envia respuesta
        echo 'error';
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
