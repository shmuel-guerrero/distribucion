<?php


// valida metodo
if (is_ajax() && is_post()) {
    // Verifica la existencia de los datos enviados
    if (isset($_POST['nit_ci']) && isset($_POST['nombre_cliente']) && isset($_POST['productos']) && 
        isset($_POST['nombres']) && isset($_POST['cantidades']) && isset($_POST['precios']) && 
        isset($_POST['descuento_bs']) && isset($_POST['nro_registros']) && isset($_POST['monto_total']) && isset($_POST['almacen_id'])) {
        // Importa la libreria para convertir el numero a letra
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // Obtiene los datos de la preventa
        $nombre_cliente = trim($_POST['nombre_cliente']);
        $id_cliente = trim($_POST['id_cliente']);
        $nit_ci = trim($_POST['nit_ci']);
        $nombre_factura = trim($_POST['factura_cliente']);
        $telefono = trim($_POST['telefono']);
        $direccion = trim($_POST['direccion']);
        $ubicacion = trim($_POST['ubicacion']);
        $adelanto = trim($_POST['adelanto']);
        $prioridad = trim($_POST['prioridad']);
        $ruta = trim($_POST['ruta']);
        $observacion = trim($_POST['observacion']);
        $id_empleado = ($_POST['empleado']!=0) ? trim($_POST['empleado']) : $_user['persona_id'];
        //$id_empleado = $_user['persona_id'];

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
        //Cuentas
        $tipo_pago = (isset($_POST['tipo_pago'])) ? trim($_POST['tipo_pago']) : 'Efectivo';

        $descuento_porc = isset($_POST['descuento_porc']) ? trim($_POST['descuento_porc']) : 0;
        $descuento_bs = trim($_POST['descuento_bs']);
        $total_importe_descuento = $_POST['total_importe_descuento'] == '' ? 0 : trim($_POST['total_importe_descuento']);

        // $nro_cuentas = trim($_POST['nro_cuentas']);

        //Habilita las funciones internas de notificación
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT ); 
		try {

			//Se abre nueva transacción.
			$db->autocommit(false);
			$db->beginTransaction();

            // Agrega nuevo cliente
            if ($id_cliente == 0) {
                $cliente = array(
                    'id_cliente' => $id_cliente,
                    'nombre_factura' => $nombre_cliente,
                    'cliente' => $nombre_cliente,
                    'nit' => $nit_ci,
                    'telefono' => $telefono,
                    'direccion' => $direccion,
                    'ubicacion' => $ubicacion,
                    'nombre_factura' => $nombre_factura,
                    'cuentas_por_cobrar' => 'no'
                );
                $id_cliente = $db->insert('inv_clientes', $cliente);
                
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
            }

            $plan = (isset($_POST['forma_pago'])) ? trim($_POST['forma_pago']) : '';
            $plan = ($plan == "2") ? "si" : "no";
            if ($plan == "si") {

                $fechas = (isset($_POST['fecha'])) ? $_POST['fecha'] : array();
                $cuotas = (isset($_POST['cuota'])) ? $_POST['cuota'] : array();
            }

            // Para creditos HGC
            $credito = trim($_POST['credito']);

            // Obtiene el numero de nota
            $nro_factura = $db->query("select ifnull(max(nro_factura), 0) + 1 as nro_factura
                                        from inv_egresos
                                        where tipo = 'Venta'
                                        and estadoe > 1
                                        and provisionado = 'S'")->fetch_first();
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
                $aux = $db->query("select * from inv_productos where id_producto = $elemento2")->fetch_first();
                if ($aux['grupo'] != '') {
                    $a = $a + $precios[$nro2] * $cantidades[$nro2];
                    $b = $b + 1;
                }
            }
            $monto_total = $monto_total - $a;

            $proforma_id = 0;

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
                    'empleado_id' => $id_empleado,
                    'coordenadas' => $ubicacion,
                    'observacion' => $prioridad,
                    'estadoe' => 2,
                    'descripcion_venta' => $observacion,
                    'ruta_id' => $ruta,
                    'plan_de_pagos' => ($credito == '1' || $credito == 1) ? 'si' : 'no', //$plan
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
                $id_unidade = $db->query("select * from inv_asignaciones a 
                                            left join inv_unidades u ON a.unidad_id=u.id_unidad AND a.visible = 's' 
                                            where u.unidad = '" . $unidad[$nro] . "' q.visible = 's'
                                            AND a.producto_id = " . $productos[$nro])->fetch_first();
                if ($id_unidade) {
                    $id_unidad = $id_unidade['id_unidad'];
                    $cantidad = $cantidades[$nro] * $id_unidade['cantidad_unidad'];
                } else {
                    $id_uni = $db->query("select id_unidad
                                        from inv_unidades 
                                        where unidad = '" . $unidad[$nro] . "'")->fetch_first();
                    $id_unidad = $id_uni['id_unidad'];
                    $cantidad = $cantidades[$nro];
                }

                $aux = $db->query("select * from inv_productos where id_producto = " . $productos[$nro])->fetch_first();
                if ($aux['grupo'] == '' && $monto_total != 0) {

                    if ($aux['promocion'] == 'si') {
                        // Forma el detalle
                        $prod = $productos[$nro];
                        $promos = $db->select('producto_id, precio, unidad_id, cantidad, descuento , id_promocion as egreso_id, cantidad as promocion_id')->from('inv_promociones')->where('id_promocion', $prod)->fetch();
                        /////////////////////////////////////////////////////////////////////////////////////////
                        $Lote = '';
                        $CantidadAux = $cantidades[$nro];
                        $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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
                            'unidad_id' => 7,
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
                            $para_id = $promo['producto_id'];
                            $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$para_id' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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
                                // $Lote .= $Detalle['lote'] . ',';
                                $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                            endforeach;
                            // $Lote .= $Detalle['lote'] . '-' . $Cant . ',';
                            $Lote = trim($Lote, ',');
                            /////////////////////////////////////////////////////////////////////////////////////////
                            $promo['lote'] = $Lote;
                            $promo['egreso_id'] = $proforma_id;
                            $promo['promocion_id'] = $productos[$nro];
                            $promo['cantidad'] = $promo['cantidad'] * $cantidades[$nro];
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
                        $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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
                            'descuento' => ($descuentos[$nro]) ? $descuentos[$nro] : 0,
                            'producto_id' => $productos[$nro],
                            'egreso_id' => $proforma_id,
                            'lote' => $Lote
                        );

                        $precios[$nro] = (float)$precios[$nro];
                        $cantidades[$nro] = (float)$cantidades[$nro];
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
                        'empleado_id' => $id_empleado,
                        'coordenadas' => $ubicacion,
                        'observacion' => $prioridad,
                        'descripcion_venta' => $observacion,
                        'estadoe' => 2,
                        'grupo' => $aux['grupo'],
                        'ruta_id' => $ruta,
                        'plan_de_pagos' => ($credito == '1' || $credito == 1) ? 'si' : 'no', //$plan
                        'descuento_porcentaje' => $descuento_porc,
                        'descuento_bs' => $descuento_bs,
                        'monto_total_descuento' => $total_importe_descuento,
                    );
                    $id2 = $db->insert('inv_egresos', $egreso2);
                    $proforma_id = $id2;
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote = '';
                    $CantidadAux = $cantidad;
                    $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$productos[$nro]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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

            //Cuentas manejo de HGC
            if ($credito == '1' || $credito == 1) {
                $id_cli = (int)$id_cliente;
                $clienteCred = $db->query("select * from inv_clientes where id_cliente = " . $id_cli)->fetch_first();

                // Instancia el ingreso
                $ingresoPlan = array(
                    'movimiento_id' => $proforma_id,
                    'interes_pago' => 0,
                    'tipo' => 'Egreso'
                );
                // Guarda la informacion del ingreso general
                $ingreso_id_plan = $db->insert('inv_pagos', $ingresoPlan);

                $Date = date('Y-m-d');
                $Dias = ' + ' . $clienteCred['dias'] . ' days';
                $Fecha_pago = date('Y-m-d', strtotime($Date . $Dias));

                $detallePlan = array(
                    'nro_cuota' => 1,
                    'pago_id' => $ingreso_id_plan,
                    'fecha' => $Fecha_pago,
                    'fecha_pago' => $Fecha_pago,
                    'monto' => $monto_total - $descuento_bs,
                    'tipo_pago' => '',
                    'empleado_id' => $_user['persona_id'],
                    'estado'  => '0'
                );
                // Guarda la informacion
                $db->insert('inv_pagos_detalles', $detallePlan);
            }

            // PARA PREMIOS
            //guardar premio
            $IdExtra =      (isset($_POST['IdExtra']))      ? $_POST['IdExtra']      : [];
            $NombreExtra =  (isset($_POST['NombreExtra']))  ? $_POST['NombreExtra']  : [];
            $PrecioExtra =  (isset($_POST['PrecioExtra']))  ? $_POST['PrecioExtra']  : [];
            $UnidadExtra =  (isset($_POST['UnidadExtra']))  ? $_POST['UnidadExtra']  : [];
            $CantidadExtra = (isset($_POST['CantidadExtra'])) ? $_POST['CantidadExtra'] : [];
            if (count($IdExtra) > 0) :
                foreach ($IdExtra as $i => $IdE) :
                    // $IdUnidad=$db->select('id_unidad')
                    //             ->from('inv_unidades')
                    //             ->where(array('unidad' => $UnidadExtra[$i]))
                    //             ->fetch_first();

                    $IdUnidad = $db->query("select id_unidad
                                from inv_unidades
                                where unidad => $UnidadExtra[$i]")
                        ->fetch_first();

                    $Fecha = date('Y-m-d');
                    $IdPromocion = $db->query("SELECT id_promocion FROM inv_promociones_monto WHERE tipo='4' AND '{$Fecha}'>=fecha_ini AND '{$Fecha}'<=fecha_fin LIMIT 1")->fetch_first();
                    $IdPromocion = ($IdPromocion['id_promocion']) ? $IdPromocion['id_promocion'] : 0;
                    // echo $IdPromocion;
                    /////////////////////////////////////////////////////////////////////////////////////////
                    $Lote = '';
                    $CantidadAux = $CantidadExtra[$i];
                    $Detalles = $db->query("SELECT id_detalle,cantidad,lote,lote_cantidad FROM inv_ingresos_detalles WHERE producto_id='$IdExtra[$i]' AND lote_cantidad>0 ORDER BY id_detalle ASC")->fetch();
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
                    $Datos = array(
                        'precio' => $PrecioExtra[$i], //o 0
                        'unidad_id' => $IdUnidad['id_unidad'],
                        'cantidad' => $CantidadExtra[$i],
                        'descuento' => '100',
                        'producto_id' => $IdExtra[$i],
                        'egreso_id' => $proforma_id,
                        'promocion_id' => $IdPromocion,
                        'lote' => $Lote,
                        'asignacion_id' => '0'
                    );
                    $db->insert('inv_egresos_detalles', $Datos);
                endforeach;
            endif;

            // verifica las promociones por monto
            $mpromociones = $db->query('select * from inv_promociones_monto')->fetch();
            $egreso = $db->query('select * from inv_egresos where id_egreso=' . $proforma_id . '')->fetch_first();

            foreach ($mpromociones as $mpromocion) {
                $egreso_id = '';
                $fecha_ini = $mpromocion['fecha_ini'];
                $fecha_fin = $mpromocion['fecha_fin'];
                $monto_promo = $mpromocion['monto_promo'];

                if ($fecha_ini <= $egreso['fecha_egreso'] and $fecha_fin >= $egreso['fecha_egreso']) {

                    if ($egreso['monto_total'] >= $monto_promo) {

                        $egreso_id = $mpromocion['egresos_ids'] . ',' . $proforma_id;
                        $datos = array('egresos_ids' => $egreso_id);
                        $condicion = array('id_promocion' => $mpromocion['id_promocion']);

                        $db->where($condicion)->update('inv_promociones_monto', $datos);
                    }
                }
            }
            // FIN PARA PREMIOS

            //se cierra transaccion
			$db->commit();

            // Envia respuesta
            echo json_encode($respuesta);

        } catch (Exception $e) {
			$status = false;
			$error = $e->getMessage();
		
			// Instancia la variable de notificacion
			$_SESSION[temporary] = array(
				'alert' => 'danger',
				'title' => 'Problemas en el proceso de interacción con la base de datos.',
				'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
			);
			// Redirecciona a la pagina principal
			//redirect('?/egresos/listar');
			//Se devuelve el error en mensaje json
			echo json_encode(array("estado" => 'n', 'msg' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'));
		
			//se cierra transaccion
			$db->rollback();
		}

    } else {
        // Envia respuesta
        echo 'datos no definidos';
        var_dump($_POST);
    }
} else {
    // Error 404
    require_once not_found();
    exit;
}
