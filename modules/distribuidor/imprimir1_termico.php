<?php

// Obtiene el orden de compra
$distribuidor = (isset($_POST['distribuidor'])) ? $_POST['distribuidor'] : 0;

// Obtiene los empleados
$empleados = $db->select('w.id_empleado, w.nombres, w.paterno, w.materno, GROUP_CONCAT(a.ruta_id SEPARATOR "&") as emp')->from('gps_asigna_distribucion a')->join('gps_rutas z','a.ruta_id = z.id_ruta')->join('sys_empleados w','z.empleado_id = w.id_empleado')->where('a.grupo_id', '')->where('a.distribuidor_id', $distribuidor)->where('a.estado', 1)->group_by('a.distribuidor_id')->fetch_first();
$empleados = explode('&', $empleados['emp']);
// var_dump($empleados);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

$respuestas = array();
$nro_nota = 0;
if($empleados) {
    foreach ($empleados as $id_orden) {
        $id_orden = (int)$id_orden;
        //BUSCAMOS LOS CLIENTES DEL VENDEDOR
        $orden = $db->query('SELECT GROUP_CONCAT(e.id_egreso , "," , e.cliente_id ORDER BY e.cliente_id ASC SEPARATOR "|") AS ides1
            FROM gps_rutas z
            LEFT JOIN inv_egresos e ON z.id_ruta = e.ruta_id
            LEFT JOIN sys_empleados w ON e.empleado_id = w.id_empleado
            WHERE e.estadoe = 2 AND e.ruta_id = ' . $id_orden . ' AND e.grupo = ""  AND (e.fecha_egreso <= w.fecha OR e.fecha_egreso < CURDATE())
            GROUP BY z.id_ruta
            ORDER BY e.cliente_id')->fetch_first();
        //var_dump($orden);
        $id_orden1 = $orden['ides1'];

        if ($id_orden1) {
            $id_ordenes = explode('|', $id_orden1);
//    var_dump($id_ordenes);
            //var_dump($id_ordenes);

            foreach ($id_ordenes as $id_orden2) {
                $aaux = explode(',', $id_orden2);
                $id_orden3 = $aaux[0];

                // Obtiene el orden de compra
                $orden = $db->select('c.*, c.descripcion as referencia, n.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, e.cargo ')
                    ->from('inv_egresos n')
                    ->join('inv_almacenes a', 'n.almacen_id = a.id_almacen', 'left')
                    ->join('sys_empleados e', 'n.empleado_id = e.id_empleado', 'left')
                    ->join('inv_clientes c', 'n.cliente_id = c.id_cliente')
                    ->where('n.id_egreso', $id_orden3)->where('n.tipo', 'Venta')->where('n.provisionado', 'S')->fetch_first();

                // Obtiene los detalles
                $detalles = $db->query("select d.*, p.codigo, p.nombre, p.nombre_factura, f.nombre as nombre_promo
                    from inv_egresos_detalles d
                    left join inv_productos p ON d.producto_id = p.id_producto
                    left join (SELECT c.id_promocion, e.nombre FROM inv_promociones c left join inv_productos e on c.id_promocion = e.id_producto ) AS f ON d.promocion_id = f.id_promocion
                    where d.egreso_id = '$id_orden3' and promocion_id != 1 GROUP by d.id_detalle order by id_detalle asc")->fetch();

                $nro_nota = $nro_nota + 1;

                $valor_empresa = ($orden['cargo'] == 1) ? $_institution['empresa1'] : $_institution['empresa2'];
                $valor_empleado = escape($orden['nombres'] . ' ' . $orden['paterno']);
                $valor_fecha = escape($orden['fecha_egreso']);
                $valor_hora = escape($orden['hora_egreso']);
                $valor_nombre_cliente = escape($orden['nombre_cliente']);
                $valor_nit_ci = escape($orden['nit_ci']);
                $valor_direccion = escape($orden['direccion']);
                $valor_descripcion = escape($orden['referencia']);
                $valor_telefono = escape($orden['nottelefono']);
                $valor_factura = escape($orden['nro_factura']);

                $valor_descuento = escape($orden['descuento']);
                $valor_observacion = escape($orden['observacion']);
                $valor_id_cliente = escape($orden['id_cliente']);
                $detalle_venta = escape($orden['descripcion_venta']);
                $valor_moneda = $moneda;
                $total = 0;
                $productos = array();
                $cantidades = array();
                $precios = array();
                $subtotal = array();
                foreach ($detalles as $nro => $detalle) {
                    //var_dump($detalle);exit();
                    $cantidad = escape($detalle['cantidad']);
                    $precio = escape($detalle['precio']);
                    $pr = $db->select('*')->from('inv_productos a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad')->where('a.id_producto', $detalle['producto_id'])->fetch_first();
                    if ($pr['unidad_id'] == $detalle['unidad_id']) {
                        $unidad = $pr['unidad'];
                        $sigla = $pr['sigla'];
                    } else {
                        $pr = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad AND a.visible = "s"')->where(array('a.producto_id' => $detalle['producto_id'], 'a.unidad_id' => $detalle['unidad_id']))->fetch_first();
                        $sigla = $pr['sigla'];
                        if ($pr['cantidad_unidad']) {
                            $unidad = $pr['unidad'];
                            $cantidad = $cantidad / $pr['cantidad_unidad'];
                        }
                    }
                    $uni_detalle = cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']);
                    $precio_sugerido = $detalle['precio_sugerido'];
                    $importe = $cantidad * $precio;
                    $total = $total + $importe;
                    $body .= '<tr height="2%">';
                    $body .= '<td class="left-right bot" align="right">' . $cantidad . '</td>';
                    $body .= '<td class="left-right bot" align="right">' . ' ' . $unidad . '(' . $uni_detalle . ' U.)' . '</td>';
                    $body .= '<td class="left-right bot">' . escape($detalle['nombre_factura']) . '</td>';
                    $body .= '<td class="left-right bot">' . escape($detalle['nombre_promo']) . '</td>';
                    $body .= '<td class="left-right bot" align="right">' . round($precio / $uni_detalle, 2) . '</td>';
                    $body .= '<td class="left-right bot" align="right">' . number_format($importe, 2, '.', '') . '</td>';
                    $body .= '</tr>';
                    $sub = number_format($precio * $cantidad, 2, '.', '');
                    array_push($productos, $detalle['nombre_factura']);

                    $cantidad1 = $cantidad.' '.$sigla;
                    array_push($cantidades, $cantidad1);
                    array_push($precios, $precio);
                    array_push($subtotal, $sub);
                }

// Obtiene el valor total
                $valor_total = number_format($total, 2, '.', '');

// Obtiene los datos del monto total
                $conversor = new NumberToLetterConverter();
                $monto_textual = explode('.', $valor_total);
                $monto_numeral = $monto_textual[0];
                $monto_decimal = $monto_textual[1];
                $monto_literal = strtoupper($conversor->to_word($monto_numeral));
                $resultado = array(
                    'papel_ancho' => 10,
                    'papel_alto' => 30,
                    'papel_limite' => 576,
                    'empresa_empleado' => $valor_empleado,
                    'empresa_nombre' => $_institution['nombre'],
                    'empresa_sucursal' => 'SUCURSAL Nº 1',
                    'empresa_direccion' => $_institution['direccion'],
                    'empresa_telefono' => 'TELÉFONO ' . $_institution['telefono'],
                    'empresa_ciudad' => 'LA PAZ - BOLIVIA',
                    'empresa_actividad' => $_institution['razon_social'],
                    'empresa_nit' => $_institution['nit'],
                    'nota_titulo' => 'N O T A   D E   V E N T A',
                    'nota_numero' => $valor_factura,
                    'nota_fecha' => date_decode($valor_fecha, 'd/m/Y'),
                    'nota_hora' => substr($valor_hora, 0, 5),
                    'cliente_nit' => $valor_nit_ci,
                    'cliente_nombre' => $valor_nombre_cliente,
                    'venta_titulos' => array('CANTIDAD', 'DETALLE', 'P. UNIT.', 'SUBTOTAL  ', 'TOTAL'),
                    'venta_cantidades' => $cantidades,
                    'venta_detalles' => $productos,
                    'venta_precios' => $precios,
                    'venta_subtotales' => $subtotal,
                    'venta_total_numeral' => $valor_total,
                    'venta_total_literal' => $monto_literal,
                    'venta_total_decimal' => $monto_decimal . '/100',
                    'venta_moneda' => $moneda,
                    'impresora' => $_terminal['impresora']
                );
                array_push($respuestas,$resultado);
            }

//            echo json_encode($respuestas);
        }
    }
//    var_dump($respuestas);
    echo json_encode($respuestas);
}
?>
