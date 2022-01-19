<?php
    require_once libraries . '/tcpdf/tcpdf.php';
    require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';
    $distribuidor = (isset($params[0])) ? $params[0] : 0;
    if ($distribuidor == 0) {
        require_once not_found();
        exit;
    }
    $salida = $db->select('a.id_orden, b.*')->from('inv_ordenes_salidas a')->join('sys_empleados b', 'b.id_empleado = a.empleado_id')->where('a.empleado_id', $distribuidor)->order_by('a.id_orden', 'asc')->fetch_first();
    $id_salida = $salida['id_orden'];
    $valor_empleado2 = $salida['nombres'] . ' ' . $salida['paterno'];
    $permisos = explode(',', permits);
    $moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
    $moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';
    list($ancho_header, $alto_header) = getimagesize(imgs . '/header.jpg');
    $relacion = $alto_header / $ancho_header;
    $ancho_header = 612;
    $alto_header = round(312 * $relacion);
    define('ancho_header', $ancho_header);
    define('alto_header', $alto_header);
    list($ancho_footer, $alto_footer) = getimagesize(imgs . '/header.jpg');
    $relacion = $alto_footer / $ancho_footer;
    $ancho_footer = 612;
    $alto_footer = round(312 * $relacion);
    define('ancho_footer', $ancho_footer);
    define('alto_footer', $alto_footer);
    class MYPDF extends TCPDF
    {
        public function Header()
        {
        }
        public function Footer()
        {
        }
    }
    $pdf = new MYPDF('P', 'pt', array(612, 935), true, 'UTF-8', false);
    $pdf->SetCreator(name_autor);
    $pdf->SetAuthor(name_autor);
    $pdf->SetTitle($_institution['nombre']);
    $pdf->SetSubject($_institution['propietario']);
    $pdf->SetKeywords($_institution['sigla']);
    $pdf->SetMargins(30, 10, 30);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(true, alto_footer + 55);
    $orden = '';
    $pdf->AddPage();
    $detalles = $db->query("SELECT GROUP_CONCAT(b.cantidad, '-', b.unidad_id SEPARATOR '|' ) AS cantidades, GROUP_CONCAT(b.precio SEPARATOR '|' ) AS precios, SUM(e.monto_total) AS m_total, e.*, c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
            FROM inv_egresos e
            LEFT JOIN inv_egresos_detalles b ON e.id_egreso = b.egreso_id
            LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
            LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
            WHERE e.estadoe = 3 AND e.ordenes_salidas_id = '$id_salida' AND b.promocion_id < 2
            GROUP BY c.id_producto ORDER BY c.descripcion ASC, c.nombre_factura ASC")->fetch();
    //productos no vendidos
    $detalles2 = $db->query("SELECT GROUP_CONCAT(a.cantidad, '-', a.unidad_id SEPARATOR '|' ) AS cantidades, GROUP_CONCAT(a.precio_id SEPARATOR '|' ) AS precios, SUM(a.precio_id * a.cantidad) AS m_total, a.precio_id as precio, a.*, c.*, c.unidad_id AS unidad_producto, d.categoria
            FROM inv_ordenes_detalles a
            LEFT JOIN inv_productos c ON a.producto_id = c.id_producto
            LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
            WHERE a.orden_salida_id = '$id_salida' AND a.promocion_id != 1
            GROUP BY c.id_producto ORDER BY c.descripcion ASC, c.nombre_factura ASC")->fetch();
    //Cobro de ventas hechas por cuentas por cobrar;
    $detalles4 = $db->query("SELECT a.cliente_id , a.nombre_cliente, a.nit_ci, a.monto_total, p.interes_pago, sum(d.monto) as monto_cancelado ,e.*, a.plan_de_pagos
            FROM inv_egresos a
            LEFT JOIN inv_pagos p ON p.movimiento_id = a.id_egreso
            LEFT JOIN inv_pagos_detalles d ON d.pago_id = p.id_pago AND d.estado = 1
            LEFT JOIN sys_empleados e ON a.empleado_id = e.id_empleado
            WHERE  a.ordenes_salidas_id = '$id_salida' GROUP BY a.cliente_id
            ORDER BY a.nombre_cliente ASC")->fetch();
    //Cobro de deudas anteriores;
    $detalles5 = $db->query("SELECT a.cliente_id , a.nombre_cliente, a.nit_ci, a.monto_total, p.interes_pago, sum(d.monto) as monto_cancelado ,e.nombres, e.paterno, p.id_pago 
            FROM inv_pagos_detalles d
            LEFT JOIN inv_pagos p ON p.id_pago = d.pago_id
            LEFT JOIN inv_egresos a ON a.id_egreso = p.movimiento_id
            LEFT JOIN sys_empleados e ON d.empleado_id = e.id_empleado
            WHERE  d.ordenes_salidas_id = '$id_salida' GROUP BY a.cliente_id
            ORDER BY a.nombre_cliente ASC")->fetch();
    $auxiliar = $db->affected_rows;
    // Asigna la orientacion de la pagina
    $pdf->SetPageOrientation('P');
    // Establece la fuente del titulo
    $pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);
    // Titulo del documento
    $pdf->Cell(0, 5, '', 0, true, 'C', false, '', 0, false, 'T', 'M');
    // Establece la fuente del contenido
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);
    // Define las variables
    $valor_fecha = escape(date_decode(date('Y-m-d H:s:i'), $_institution['formato']) . ' ' . $orden['hora_egreso']);
    $valor_nombre_cliente = escape($orden['nombre_cliente']);
    $valor_nit_ci = escape($orden['nit_ci']);
    $valor_direccion = escape($orden['direccion']);
    $valor_telefono = escape($orden['telefono']);
    $valor_monto_total = escape($orden['monto_total']);
    $valor_empleado = escape($empleados['nombres'] . ' ' . $empleados['paterno'] . ' ' . $empleados['materno']);
    $valor_descuento = escape($orden['descuento']);
    $valor_observacion = escape($orden['observacion']);
    $valor_moneda = $moneda;
    $total = 0;
    // Establece la fuente del contenido
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);
    // Estructura la tabla
    $body = '';
    $total = 0;
    $body2 = '';
    $producto_vendido = array();
    foreach ($detalles as $nro => $detalle) {
        $importe = 0;
        $sugerido = escape($detalle['precio_sugerido']);
        $descuento = escape($detalle['descuento']);
        $unid = explode('|', $detalle['cantidades']);
        $precios = explode('|', $detalle['precios']);
        $mayor = 0;
        $unidad_mayor = 0;
        $cantidades = 0;
        if (count($unid) > 1) {
            $importe_t = 0;
            foreach ($unid as $nro2 => $uni) {
                $parte = explode('-', $uni);
                $unidad = $parte[1];
                $cantid = $parte[0];
                $cantidades = $cantidades + $cantid;
                if (cantidad_unidad($db, $detalle['id_producto'], $unidad))
                    $importe = ($cantid / cantidad_unidad($db, $detalle['id_producto'], $unidad)) * $precios[$nro2];
                $importe_t = $importe_t + $importe;
                $total = $total + $importe;
            }
        } else {
            $parte = explode('-', $unid[0]);
            $unidad = $parte[1];
            $cantid = $parte[0];
            $cantidades = $cantidades + $cantid;
            if (cantidad_unidad($db, $detalle['id_producto'], $unidad))
                $importe = ($cantid / cantidad_unidad($db, $detalle['id_producto'], $unidad)) * $precios[0];
            $importe_t = $importe;
            $total = $total + $importe;
        }
        $producto_vendido[$detalle['id_producto']]['cantidad'] = $cantidades;
        $mayores = cantidad_unidad($db, $detalle['id_producto'], $id_caja);
        if (!$mayores) {
            $mayores = '1';
        }
        if ($mayores != 1 && $mayores < $cantidades) {
            $unidad_mayor = $id_caja;
            $mayor = $mayores;
        } else {
            $unidad_mayor = $detalle['unidad_producto'];
            $mayor = 1;
        }
        $unidades_t = (int) ($cantidades / cantidad_unidad($db, $detalle['id_producto'], $unidad_mayor));
        if ($cantidades % cantidad_unidad($db, $detalle['id_producto'], $unidad_mayor) == 0) {
            $otra_unidad = '';
            $otra_cantid = '';
        } else {
            $otra_unidad = '<br>' . nombre_unidad($db, $detalle['unidad_producto']);
            $otra_cantid = '<br>' . ($cantidades - ($unidades_t * cantidad_unidad($db, $detalle['id_producto'], $unidad_mayor)));
        }
        $unidades4 = substr($unidades4, 0, -4);
        $Aux1 = nombre_unidad($db, $unidad_mayor);
        $Aux2 = escape($detalle['nombre']);
        $Aux3 = number_format($importe_t, 2, '.', '');
        $body .= <<<EOD
        <tr height="2%">
            <td class="left-right bot" align="right">$unidades_t $otra_cantid</td>
            <td class="left-right bot">$Aux1 ($mayor U.) $otra_unidad</td>
            <td class="left-right bot" align="left">$Aux2</td>
            <td class="left-right bot" align="right">{$detalle['descripcion']}</td>
            <td class="left-right bot" align="right">{$detalle['categoria']}</td>
            <td class="left-right bot" align="right">$Aux3</td>
        </tr>
EOD;
        $producto_vendido[$detalle['id_producto']]['precio'] = $importe_t;
    }

    $total2 = 0;
    foreach ($detalles2 as $nro => $detalle) {
        $importe = 0;
        $sugerido = escape($detalle['precio_sugerido']);
        $descuento = escape($detalle['descuento']);
        $unid = explode('|', $detalle['cantidades']);
        $precios = explode('|', $detalle['precios']);
        $mayor = 0;
        $total2 = $total2 - $producto_vendido[$detalle['id_producto']]['precio'];
        $unidad_mayor = 0;
        $cantidades = 0 - $producto_vendido[$detalle['id_producto']]['cantidad'];
        $importe_t = 0 - $producto_vendido[$detalle['id_producto']]['precio'];
        if (count($unid) > 1) {
            foreach ($unid as $nro2 => $uni) {
                $parte = explode('-', $uni);
                $unidad = $parte[1];
                $cantid = $parte[0];
                $cantidades = $cantidades + $cantid;
                $importe = (int) ($cantid / cantidad_unidad($db, $detalle['id_producto'], $unidad)) * $precios[$nro2];
                $importe_t = $importe_t + $importe;
                $total2 = $total2 + $importe;
            }
        } else {
            $parte = explode('-', $unid[0]);
            $unidad = $parte[1];
            $cantid = $parte[0];
            $cantidades = $cantidades + $cantid;
            $importe = (int) ($cantid / cantidad_unidad($db, $detalle['id_producto'], $unidad)) * $precios[0];
            $importe_t = $importe_t + $importe;
            $total2 = $total2 + $importe;
        }
        $mayores = cantidad_unidad($db, $detalle['id_producto'], $id_caja);
        if (!$mayores) {
            $mayores = '1';
        }
        if ($mayores != 1 && $mayores < $cantidades) {
            $unidad_mayor = $id_caja;
            $mayor = $mayores;
        } else {
            $unidad_mayor = $detalle['unidad_producto'];
            $mayor = 1;
        }
        $unidades_t = (int) ($cantidades / cantidad_unidad($db, $detalle['id_producto'], $unidad_mayor));
        if ($cantidades % cantidad_unidad($db, $detalle['id_producto'], $unidad_mayor) == 0) {
            $otra_unidad = '';
            $otra_cantid = '';
        } else {
            $otra_unidad = '<br>' . nombre_unidad($db, $detalle['unidad_producto']);
            $otra_cantid = '<br>' . ($cantidades - ($unidades_t * cantidad_unidad($db, $detalle['id_producto'], $unidad_mayor)));
        }
        $unidades4 = substr($unidades4, 0, -4);
        $Aux1=nombre_unidad($db,$unidad_mayor);
        $Aux2=escape($detalle['nombre_factura']);
        $Aux3=number_format($importe_t, 2, '.', '');
        $body2=<<<EOD
        <tr height="2%">
            <td class="left-right bot" align="right">$unidades_t $otra_cantid</td>
            <td class="left-right bot">$Aux1 ($mayor U.)$otra_unidad</td>
            <td class="left-right bot" align="left">$Aux2</td>
            <td class="left-right bot" align="right">{$detalle['descripcion']}</td>
            <td class="left-right bot" align="right">{$detalle['categoria']}</td>
            <td class="left-right bot" align="right">$Aux3</td>
        </tr>
EOD;
    }
    $body7 = '';
    $importeC = 0;
    $saldoC = 0;
    $totalCC = 0;
    foreach ($detalles4 as $nro => $cuentas):
        if ($cuentas['plan_de_pagos'] == 'si'):
            $importeC = $importeC + $cuentas['monto_cancelado'];
            $m_pendiente = $cuentas['monto_total'] - $cuentas['monto_cancelado'];
            $m_pendiente = number_format($m_pendiente, 2, '.', '');
            $m_cancelado = number_format($cuentas['monto_cancelado'], 2, '.', '');
        else:
            $importeC = $importeC + $cuentas['monto_total'];
            $m_pendiente = 0;
            $m_cancelado = number_format($cuentas['monto_total'], 2, '.', '');
        endif;
        $totalCC = $totalCC + $cuentas['monto_total'];
        $saldoC = $saldoC + $m_pendiente;
        $body7 .= '<tr height="2%">';
        $body7 .= '<td class="left-right bot" >' . $cuentas['cliente_id'] . '</td>';
        $body7 .= '<td class="left-right bot">' . $cuentas['nombre_cliente'] . ' ' . $cuentas['nit_ci'] . '</td>';
        $body7 .= '<td class="left-right bot">' . $cuentas['monto_total'] . '</td>';
        $body7 .= '<td class="left-right bot">' . escape($m_pendiente) . '</td>';
        $body7 .= '<td class="left-right bot" align="right">' . escape($m_cancelado) . '</td>';
        $body7 .= '</tr>';
    endforeach;
    $body8 = '';
    $importePendiente = 0;
    foreach ($detalles5 as $nro => $cuentas):
        $cancelado = $db->select('sum(if(estado = 1, monto,0)) as cancelado, sum(if(estado = 0, monto,0)) as pendiente')->from('inv_pagos_detalles')->where('pago_id', $cuentas['id_pago'])->fetch_first();
        $importePendiente = $importePendiente + $cuentas['monto_cancelado'];
        $body8 .= '<tr height="2%">';
        $body8 .= '<td class="left-right bot" >' . $cuentas['cliente_id'] . '</td>';
        $body8 .= '<td class="left-right bot">' . $cuentas['nombre_cliente'] . ' ' . $cuentas['nit_ci'] . '</td>';
        $body8 .= '<td class="left-right bot">' . $cuentas['monto_total'] . '</td>';
        $body8 .= '<td class="left-right bot">' . escape($cancelado['cancelado']) . '</td>';
        $body8 .= '<td class="left-right bot">' . escape($cancelado['pendiente']) . '</td>';
        $body8 .= '<td class="left-right bot" align="right">' . escape(number_format($cuentas['monto_cancelado'], 2, '.', '')) . '</td>';
        $body8 .= '</tr>';
    endforeach;
    $valor_total = number_format(($total), 2, '.', '');
    $valor_totalT = number_format(($total - $importeD), 2, '.', '');
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $valor_total);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = strtoupper($conversor->to_word($monto_numeral));
    $body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;
    $valor_total2 = number_format($total2, 2, '.', '');
    $conversor = new NumberToLetterConverter();
    $monto_textual2 = explode('.', $valor_total2);
    $monto_numeral2 = $monto_textual2[0];
    $monto_decimal2 = $monto_textual2[1];
    $monto_literal2 = strtoupper($conversor->to_word($monto_numeral2));
    $body2 = ($body2 == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body2;
    $valor_total3 = number_format($importeD, 2, '.', '');
    $valor_final = number_format(($valor_total + $valor_total2), 2, '.', '');
    $conversor = new NumberToLetterConverter();
    $monto_textual3 = explode('.', $valor_total3);
    $monto_numeral3 = $monto_textual3[0];
    $monto_decimal3 = $monto_textual3[1];
    $monto_literal3 = strtoupper($conversor->to_word($monto_numeral3));
    $body6 = ($body6 == '') ? '<tr><td colspan="4" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body6;
    $valor_total7 = number_format($importeC, 2, '.', '');
    $totalCC = number_format($totalCC, 2, '.', '');
    $saldoC = number_format($saldoC, 2, '.', '');
    $conversor = new NumberToLetterConverter();
    $monto_textual7 = explode('.', $valor_total7);
    $monto_numeral7 = $monto_textual7[0];
    $monto_decimal7 = $monto_textual7[1];
    $monto_literal7 = strtoupper($conversor->to_word($monto_numeral7));
    $body7 = ($body7 == '') ? '<tr><td colspan="4" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body7;
    $valor_total8 = number_format($importePendiente, 2, '.', '');
    $conversor = new NumberToLetterConverter();
    $monto_textual8 = explode('.', $valor_total8);
    $monto_numeral8 = $monto_textual8[0];
    $monto_decimal8 = $monto_textual8[1];
    $monto_literal8 = strtoupper($conversor->to_word($monto_numeral8));
    $body8 = ($body8 == '') ? '<tr><td colspan="6" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body8;
    $tabla = <<<EOD
        <style>
        th {
            background-color: #eee;
            font-weight: bold;
        }
        .left-right {
            border-left: 1px solid #444;
            border-right: 1px solid #444;
        }
        .none {
            border: 1px solid #fff;
        }
        .all {
            border: 1px solid #444;
        }
        .bot{
            border-top: 1px solid #444;
        }
        .bot2{
            border: 1px solid #444;
        }
        </style>
        <table cellpadding="1">
            <tr>
                <td width="50%" class="none" colspan='2' rowspan='2' align="center" ><h1>LIQUIDACIÓN AUTOVENDEDOR</h1></td>
                <td width="15%" class="none"><b>FECHA:</b></td>
                <td width="35%" class="none">$valor_fecha</td>
            </tr>
            <tr>
                <td width="25%" class="none" rowspan='2'><b>VENDEDORES:</b></td>
                <td width="25%" class="none" rowspan='2'>$valor_empleado2</td>
                <td width="15%" class="none"><b>DISTRIBUIDOR:</b></td>
                <td width="35%" class="none">$valor_empleado</td>
            </tr>
            <tr><td></td><td></td>
                <td width="15%" class="none"><b>HOJA DE SALIDA:</b></td>
                <td width="35%" class="none">$valor_final</td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td class="none" align="center" colspan="4" ><h3>PRODUCTOS VENDIDOS</h3></td>
            </tr>
        </table>
        <br><br>
        <table cellpadding="2">
            <tr>
                <th width="6%" class="all" align="left">CANT.</th>
                <th width="16%" class="all" align="left">UNIDAD</th>
                <th width="40%" class="all" align="left">DETALLE</th>
                <th width="13%" class="all" align="left">LINEA</th>
                <th width="13%" class="all" align="right">CATEGORÍA</th>
                <th width="12%" class="all" align="right">IMPORTE $valor_moneda</th>
            </tr>
            $body
            <tr>
                <th class="all" align="left" colspan="5">IMPORTE TOTAL $valor_moneda</th>
                <th class="all" align="right">$valor_total</th>
            </tr>
        </table>
        <p align="right">$monto_literal $monto_decimal /100</p>
        <table cellpadding="1">
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td class="none" align="center" colspan="3" ><h3>PRODUCTOS NO VENDIDOS</h3></td>
            </tr>
        </table>
        <br><br>
        <table cellpadding="2">
            <tr>
                <th width="6%" class="all" align="left">CANT.</th>
                <th width="16%" class="all" align="left">UNIDAD</th>
                <th width="40%" class="all" align="left">DETALLE</th>
                <th width="13%" class="all" align="left">LINEA</th>
                <th width="13%" class="all" align="right">CATEGORÍA</th>
                <th width="12%" class="all" align="right">IMPORTE $valor_moneda</th>
            </tr>
            $body2
            <tr>
                <th class="all" align="left" colspan="5">IMPORTE TOTAL $valor_moneda</th>
                <th class="all" align="right">$valor_total2</th>
            </tr>
        </table>
        <p align="right">$monto_literal2 $monto_decimal2 /100</p>
        <table cellpadding="1">
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td class="none" align="center" colspan="3" ><h3>CUENTAS POR PAGAR</h3></td>
            </tr>
        </table>
        <BR>
        <br>
        <table cellpadding="2">
            <tr>
                <th width="10%" class="all" align="left">CODIGO</th>
                <th width="51%" class="all" align="left">CLIENTE</th>
                <th width="15%" class="all" align="left">MONTO VENDIDO</th>
                <th width="12%" class="all" align="left">SALDO (Bs)</th>
                <th width="12%" class="all" align="left">COBRO (Bs)</th>
            </tr>
            $body7
            <tr>
                <th class="all" align="left" colspan="2">IMPORTE TOTAL $valor_moneda</th>
                <th class="all" align="left">$totalCC</th>
                <th class="all" align="left">$saldoC</th>
                <th class="all" align="right">$valor_total7</th>
            </tr>
        </table>
        <p align="right">$monto_literal7 $monto_decimal7 /100</p>
        <table cellpadding="1">
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td class="none" align="center" colspan="3" ><h3>COBROS DEUDAS ANTERIORES</h3></td>
            </tr>
        </table>
        <BR>
        <br>
        <table cellpadding="2">
            <tr>
                <th width="10%" class="all" align="left">CODIGO</th>
                <th width="42%" class="all" align="left">CLIENTE</th>
                <th width="12%" class="all" align="left">TOTAL</th>
                <th width="12%" class="all" align="left">T. PAGADO</th>
                <th width="12%" class="all" align="left">SALDO (Bs)</th>
                <th width="12%" class="all" align="left">COBRO (Bs)</th>
            </tr>
            $body8
            <tr>
                <th class="all" align="left" colspan="5">IMPORTE TOTAL $valor_moneda</th>
                <th class="all" align="right">$valor_total8</th>
            </tr>
        </table>
        <p align="right">$monto_literal8 $monto_decimal8 /100</p>
        <table cellpadding="1">
            <tr>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td class="none" align="center" colspan="3" ><h3>CONTROL DE CAJAS</h3></td>
            </tr>
        </table>
        <BR>
        <br>
        <table cellpadding="2">
            <tr>
                <th width="10%" class="all" align="left">CANTIDAD</th>
                <th width="48%" class="all" align="left">NOMBRE</th>
                <th width="30%" class="all" align="left">CANTIDAD</th>
                <th width="12%" class="all" align="left">IMPORTE $valor_moneda</th>
            </tr>
            $body6
            <tr>
                <th class="all" align="left" colspan="3">IMPORTE TOTAL $valor_moneda</th>
                <th class="all" align="right">$valor_total3</th>
            </tr>
        </table>
        <p align="right">$monto_literal3 $monto_decimal3 /100</p>
        <table cellpadding="2">
            <tr>
                <td width="50%" align="right"></td>
                <td width="38%" align="right">MONTO LIQUIDACIÓN $valor_moneda</td>
                <td width="12%" class="bot2" align="RIGHT">$valor_total</td>
            </tr>
        </table>
        <table cellpadding="1">
            <tr>
                <td width="50%" class="none" align="center" ></td>
                <td width="50%" class="none" align="center" ></td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" ></td>
                <td width="50%" class="none" align="center" ></td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" ></td>
                <td width="50%" class="none" align="center" ></td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" >------------------------------------------------------</td>
                <td width="50%" class="none" align="center" >------------------------------------------------------</td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" >Entregué conforme </td>
                <td width="50%" class="none" align="center" >Recibi conforme cajas</td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" >$valor_empleado</td>
                <td width="50%" class="none" align="center" >Nombre:_________________________ </td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" ></td>
                <td width="50%" class="none" align="center" ></td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" ></td>
                <td width="50%" class="none" align="center" ></td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" ></td>
                <td width="50%" class="none" align="center" ></td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" >------------------------------------------------------</td>
                <td width="50%" class="none" align="center" >__ / __ / ____</td>
            </tr>
            <tr>
                <td width="50%" class="none" align="center" >Recibi conforme (Almacen)</td>
                <td width="50%" class="none" align="center" >Fecha liquidación</td>
            </tr>
            <tr>
                <td width="49%" class="none" align="center" >Nombre:_________________________ </td>
                <td width="49%" class="none" align="center" ></td>
            </tr>
        </table>
EOD;
    $pdf->writeHTML($tabla, true, false, false, false, '');
    if ($auxiliar == 10):
        $pdf->Ln(2);
    elseif ($auxiliar == 9):
        $pdf->Ln(25);
    elseif ($auxiliar == 8):
        $pdf->Ln(65);
    elseif ($auxiliar == 7):
        $pdf->Ln(65);
    elseif ($auxiliar == 6):
        $pdf->Ln(85);
    elseif ($auxiliar == 5):
        $pdf->Ln(105);
    elseif ($auxiliar < 5):
        $pdf->Ln(185);
    endif;
    $style3 = array('width' => 1, 'cap' => 'round', 'join' => 'round', 'dash' => '2,10', 'color' => array(255, 0, 0));
    $nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output($nombre, 'I');