<?php

// Obtiene el orden de compra
$distribuidor = (isset($params[0])) ? $params[0] : 0;
// Obtiene el rango de fechas
$gestion = date('Y');
//$gestion_base = date('Y-m-d');
$gestion_base = date("d-m-Y",strtotime(date('Y-m-d')."- 1 days"));

//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = $gestion_base;

// Obtiene fecha inicial
$fecha_inicial = (isset($params[1])) ? $params[1] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[2])) ? $params[2] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

$caja = $db->select('id_unidad')->from('inv_unidades')->where('unidad', 'CAJA')->fetch_first();

$id_caja = (isset($caja['id_unidad'])) ? $caja['id_unidad'] : 0;

if ($distribuidor == 0) {
    // Error 404
    require_once not_found();
    exit;
}

// Obtiene los empleados
$empleados = $db->select('w.id_empleado, w.nombres, w.paterno, w.materno, GROUP_CONCAT(a.ruta_id SEPARATOR "&") as emp')->from('gps_asigna_distribucion a')->join('gps_rutas z','a.ruta_id = z.id_ruta')->join('sys_empleados w','a.distribuidor_id = w.id_empleado')->where('a.distribuidor_id',$distribuidor)->where('a.estado',1)->group_by('a.distribuidor_id')->fetch_first();
$prueba = explode('&',$empleados['emp']);
$c=0;
$preg = '(';
for ($c = 0; $c < count($prueba); $c++) {
    if ($c == 0) {
        $preg = $preg . 'a.empleado_id = ' . $prueba[$c] . ' ';
    } else {
        $preg = $preg . 'OR a.empleado_id = ' . $prueba[$c] . ' ';
    }
}
$empleados2 = $db->query('SELECT  w.*
FROM gps_asigna_distribucion a
    LEFT JOIN gps_rutas e ON a.ruta_id = e.id_ruta
    LEFT JOIN sys_empleados w ON e.empleado_id = w.id_empleado
    LEFT JOIN inv_egresos b ON e.id_ruta = b.ruta_id
    LEFT JOIN inv_egresos_detalles c ON b.id_egreso = c.egreso_id
    LEFT JOIN inv_productos d ON c.producto_id = d.id_producto
    LEFT JOIN inv_categorias f ON d.categoria_id = f.id_categoria
    WHERE a.distribuidor_id = '.$distribuidor.' AND a.estado=1 AND b.estadoe= 2 AND c.promocion_id != 1 AND (b.fecha_egreso <= w.fecha or b.fecha_egreso < CURDATE()) GROUP BY w.id_empleado ORDER BY w.paterno ASC, d.nombre_factura ASC')->fetch();
$valor_empleado2 = '';
foreach($empleados2 as $empleado2){
    $valor_empleado2 = $valor_empleado2.'<br>'.$empleado2['nombres'].' '.$empleado2['paterno'];
}
$preg = $preg . ')';

//var_dump($empleados);
// Obtiene los permisos
$permisos = explode(',', permits);


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

// Operaciones con la imagen del header
list($ancho_header, $alto_header) = getimagesize(imgs . '/header.jpg');
$relacion = $alto_header / $ancho_header;
$ancho_header = 612;
$alto_header = round(312 * $relacion);
define('ancho_header', $ancho_header);
define('alto_header', $alto_header);

// Operaciones con la imagen del footer
list($ancho_footer, $alto_footer) = getimagesize(imgs . '/header.jpg');
$relacion = $alto_footer / $ancho_footer;
$ancho_footer = 612;
$alto_footer = round(312 * $relacion);
define('ancho_footer', $ancho_footer);
define('alto_footer', $alto_footer);


// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
    public function Header() {
    }
    public function Footer() {
    }
}

// Instancia el documento PDF
$pdf = new MYPDF('P', 'pt', 'LETTER', true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(30, 10 , 30);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, alto_footer + 55);

$orden = '';

// Adiciona la pagina
$pdf->AddPage('P', 'LETTER');

if (true) {

// Obtiene el orden de compra
    $orden = $db->select('n.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos n')->join('inv_almacenes a', 'n.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'n.empleado_id = e.id_empleado', 'left')->where('n.id_egreso', $id_orden)->where('n.tipo', 'Venta')->where('n.provisionado', 'S')->fetch_first();

    //obtiene  PRODUCTOS ENTREGADOS
    // Obtiene los detalles
    $detalles = $db->query('SELECT GROUP_CONCAT(a.id_egreso SEPARATOR "|" ) AS id_egreso, GROUP_CONCAT(b.cantidad, "-", b.unidad_id SEPARATOR "|" ) AS cantidades, GROUP_CONCAT(b.precio SEPARATOR "|" ) AS precios, SUM(a.monto_total) AS m_total,  c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
		FROM tmp_egresos a
		LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
		LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
		LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
		WHERE a.estado = 2 AND a.distribuidor_id = '.$distribuidor.' AND a.distribuidor_estado = "ENTREGA" and b.promocion_id != 1 AND a.anulado != 3
		GROUP BY c.id_producto ORDER BY c.descripcion ASC, c.nombre_factura ASC')->fetch();
	
    //obtiene PRODUCTOS VENDIDOS - VENTA DIRECTA(REVENDIDOS)
	$detalles12 = $db->query('SELECT GROUP_CONCAT(b.cantidad, "-", b.unidad_id SEPARATOR "|" ) AS cantidades, GROUP_CONCAT(b.precio SEPARATOR "|" ) AS precios, SUM(a.monto_total) AS m_total,  c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
		FROM tmp_egresos a
		LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
		LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
		LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
		WHERE a.estado = 2 AND a.distribuidor_id = '.$distribuidor.' AND a.distribuidor_estado = "VENTA" and b.promocion_id != 1
		GROUP BY c.id_producto ORDER BY c.descripcion ASC, c.nombre_factura ASC')->fetch();

    //obtiene la suma de las observaciones
    $total_obs = $db->query('SELECT SUM(a.descripcion_venta) AS obs_total
		FROM tmp_egresos a
		WHERE a.estado = 2 AND a.distribuidor_id = '.$distribuidor.' AND a.distribuidor_estado = "ENTREGA" GROUP BY a.id_egreso')->fetch_first();
    
    /** PRODUCTOS DEVUELTOS */
    $detalles2 = $db->query('SELECT GROUP_CONCAT(b.cantidad, "-", b.unidad_id SEPARATOR "|" ) AS cantidades, GROUP_CONCAT(b.precio SEPARATOR "|" ) AS precios, SUM(a.monto_total) AS m_total,  c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
		FROM tmp_egresos a
		LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
		LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
		LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
		WHERE a.estado = 2 AND a.distribuidor_id = '.$distribuidor.' AND a.distribuidor_estado NOT IN ("ENTREGA", "VENTA") and b.promocion_id != 1
		GROUP BY c.id_producto ORDER BY c.descripcion ASC, c.nombre_factura ASC')->fetch();

    $detalles3 = $db->query('SELECT a.cliente_id, a.descripcion_venta, a.nombre_cliente
		FROM tmp_egresos a
		WHERE a.estado = 2 AND a.distribuidor_id = '.$distribuidor.' AND a.distribuidor_estado = "ENTREGA" AND a.descripcion_venta
		ORDER BY a.nombre_cliente ASC')->fetch();

       /** RESUMEN DE MOVIMIENTOS */
    //Cobro de ventas hechas por cuentas por cobrar;
    $detalles4 = $db->query("SELECT a.id_egreso, a.cliente_id , c.cliente, c.nombre_factura, a.nit_ci, IFNULL(a.monto_total, 0)AS monto_total, p.interes_pago, IFNULL(sum(d.monto), 0) as monto_cancelado, e.*, a.plan_de_pagos
        FROM tmp_egresos a
        LEFT JOIN inv_clientes c ON c.id_cliente = a.cliente_id
        LEFT JOIN inv_pagos p ON p.movimiento_id = a.id_egreso
        LEFT JOIN inv_pagos_detalles d ON d.pago_id = p.id_pago AND d.estado = 1
        LEFT JOIN sys_empleados e ON a.distribuidor_id = e.id_empleado
        WHERE a.distribuidor_id = '" . $distribuidor . "' AND a.estado = 2 AND a.distribuidor_estado = 'ENTREGA' GROUP BY a.cliente_id
        ORDER BY a.nombre_cliente ASC")->fetch();

     /** RESUMEN DE ANULADOS POR CLIENTE */
    $anulados = $db->query('SELECT a.anulado, a.distribuidor_estado, a.estadoe, a.estado, a.id_egreso, a.cliente_id , cl.cliente, cl.nombre_factura, a.nit_ci, IFNULL(a.monto_total, 0)AS monto_total,  c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
    FROM tmp_egresos a
    LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
    LEFT JOIN inv_clientes cl ON cl.id_cliente = a.cliente_id
    LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
    LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
    WHERE a.estado = 2 AND a.distribuidor_id = '.$distribuidor.' AND a.distribuidor_estado IN ("DEVUELTO") and b.promocion_id != 1 AND a.accion = "Anulado"
     GROUP BY a.cliente_id  ORDER BY a.nombre_cliente ASC')->fetch();

    
    //Cobro de ventas hechas anteriormente;
    $detalles5 = $db->query("SELECT a.cliente_id , a.nombre_cliente, a.nit_ci, a.monto_total, p.interes_pago, sum(d.monto) as monto_cancelado ,e.nombres, e.paterno, p.id_pago 
                    FROM inv_pagos_detalles d
                    LEFT JOIN inv_pagos p ON p.id_pago = d.pago_id
                    LEFT JOIN inv_egresos a ON a.id_egreso = p.movimiento_id
                    LEFT JOIN sys_empleados e ON d.empleado_id = e.id_empleado
                    WHERE d.estado = 1 AND d.fecha_pago = CURDATE() AND a.estadoe = 3 AND a.plan_de_pagos = 'si' and p.tipo = 'Egreso'
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
    
    //PRODUCTOS ENTREGADOS
    foreach ($detalles as $nro => $detalle) {
        
        $importe = 0;
        $sugerido = escape($detalle['precio_sugerido']);
        $descuento = escape($detalle['descuento']);
        $unid = explode('|', $detalle['cantidades']);
        $precios = explode('|', $detalle['precios']);
        $mayor = 0;
        $unidad_mayor = 0;
        $cantidades = 0;
        $detalle_unidades = '';
        $importe_t = 0;
        $total_venta_devuelto = 0;
        $cantidad_devuelta_producto = 0;
        $venta_devuelto = 0;
        
        if(count($unid)>1){
            //si tiene mas unidades
            foreach ($unid as $nro2 => $uni) {
                $parte = explode('-', $uni);
                $unidad = $parte[1];
                $cantid = $parte[0];
                $cantidades = $cantidades + $cantid;
                //$importe = ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad))* $precios[$nro2];
                       //Validacion
                if(cantidad_unidad($db,$detalle['id_producto'],$unidad)){
                    $importe = ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[$nro2];
                    $importe_t = $importe_t + $importe ;
                    $total = $total + $importe;
                    //$detalle_unidades .= ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) . " " .  substr(nombre_unidad($db,$unidad), 0 ,3) . "(" . cantidad_unidad($db,$detalle['id_producto'],$unidad) . ") " . $moneda . $precios[$nro2] ."<br>";
                }
            }
        }else{
            $parte = explode('-', $unid[0]);
            $unidad = $parte[1];
            $cantid = $parte[0];
            $cantidades = $cantidades + $cantid;
            //$importe = ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad))* $precios[0];
                   //Validacion
            if(cantidad_unidad($db,$detalle['id_producto'],$unidad))
                $importe = ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[0];
            $importe_t = $importe;
            $total = $total + $importe;
            //$detalle_unidades .= ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) . " " .  substr(nombre_unidad($db,$unidad), 0 ,3) . "(" . cantidad_unidad($db,$detalle['id_producto'],$unidad) . ") " . $moneda . $precios[$nro2] ."<br>";
        }

        $id_producto = $detalle['id_producto'];
        $producto_devuelto = $db->query('SELECT te.nro_factura, te.id_egreso, td.producto_id, te.id_egreso,
                GROUP_CONCAT(te.id_tmp_egreso SEPARATOR "|")AS id_tmp_egreso, 
                GROUP_CONCAT(td.precio SEPARATOR "|")AS precio, 
                GROUP_CONCAT(td.cantidad, "-", td.unidad_id SEPARATOR "|" )AS cantidad 
                FROM tmp_egresos te 
                LEFT JOIN tmp_egresos_detalles td ON te.id_tmp_egreso=td.tmp_egreso_id        
                WHERE te.estado = 2 AND te.distribuidor_id = '.$distribuidor.' 
                AND td.producto_id = ' . $id_producto . '
                AND te.distribuidor_estado NOT IN ("ENTREGA", "VENTA") 
                AND (te.accion IN ("VentaDevuelto") OR (te.estadoe = 3 AND te.estado = 2 AND te.distribuidor_estado = "DEVUELTO"))
                AND td.promocion_id != 1
                GROUP BY td.producto_id')->fetch_first();
        
        if (count($producto_devuelto) > 0 ) {
            //se convierte en array las entregas del producto
            $egresos_entregados = explode('|', $detalle['id_tmp_egreso']);

            //se itera los movimientos
            foreach ($egresos_entregados as $key => $value) {

                //se valida si las id entregados son iguales al id_devuelto despues de la entrega
                //if ($producto_devuelto['id_egreso'] == $value) {
                if (true) {
                    if ($producto_devuelto) {
                        $precio_dev = explode("|", $producto_devuelto['precio']);
                        $cant_unid_dev = explode("|", $producto_devuelto['cantidad']);                        
                       
                        foreach ($cant_unid_dev as $key => $value) {
                            $parte = explode('-', $value);
                            $unidad = $parte[1];
                            $cantid = $parte[0];
                            if(true){
                                $venta_devuelto = ($cantid / cantidad_unidad($db, $id_producto, $unidad)) * $precio_dev[$key];
                                $total_venta_devuelto = $total_venta_devuelto + $venta_devuelto ;
                                $cantidad_devuelta_producto += (($cantid / cantidad_unidad($db, $id_producto, $unidad))) ? ($cantid / cantidad_unidad($db, $id_producto, $unidad)) : 0;
                            }                
                        }
                        $importe_t =  $importe_t - $total_venta_devuelto;
                        $total =  $total - $total_venta_devuelto;
                    }                    
                }                
            }
        }

            // buscar id caja;
            $mayores = cantidad_unidad($db,$detalle['id_producto'],$id_caja);

            if(!$mayores){
                $mayores = '1';
            }

            if($mayores != 1 && $mayores < $cantidades){
                $unidad_mayor = $id_caja;
                $mayor = $mayores;
            }else{
                $unidad_mayor = $detalle['unidad_producto'];
                $mayor = 1;
            }
            $unidades_t = (int)($cantidades/cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor));
            if($cantidades % cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor)==0){
                $otra_unidad = '';
                $otra_cantid = '';
            }else{
                $otra_unidad = '<br>'.nombre_unidad($db,$detalle['unidad_producto']);
                $otra_cantid = '<br>'.($cantidades-($unidades_t*cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor)));
            }
            $unidades4 = substr($unidades4,0,-4);

            $unidades_t = (count($producto_devuelto) > 0) ? $unidades_t - $cantidad_devuelta_producto: $unidades_t;

            if ($unidades_t > 0) {  
                $body .= '<tr height="2%" >';
                $body .= '<td class="left-right bot" align="right">' . $unidades_t .''.$otra_cantid. '</td>';
                $body .= '<td class="left-right bot">' . nombre_unidad($db,$unidad_mayor).'('.$mayor.' U.)'.''.$otra_unidad. '</td>';
                $body .= '<td class="left-right bot" align="left">' . escape($detalle['codigo']) . " - " . escape($detalle['nombre']) . '</td>';
                $body .= '<td class="left-right bot" align="right">' . $detalle['descripcion']. '</td>';
                $body .= '<td class="left-right bot" align="right">' . $detalle['categoria']. '</td>';
                $body .= '<td class="left-right bot" align="right">' . number_format($importe_t, 2, '.', '') . '</td>';
                $body .= '</tr>';
            }
    }


    //obtiene PRODUCTOS VENDIDOS - VENTA DIRECTA(REVENDIDOS)
    $body12 = '';
    $total12 = 0;
    foreach ($detalles12 as $nro => $detalle) {
        $importe = 0;
        $sugerido = 2;

        // $precio = escape($detalle['precio']);
        $descuento = escape($detalle['descuento']);

        //$total = $total + $importe;
        $unid = explode('|', $detalle['cantidades']);
        $precios = explode('|', $detalle['precios']);
        $mayor = 0;
        $unidad_mayor = 0;
        $cantidades = 0;
        if(count($unid)>1){
            //si tiene mas unidades
            $importe_t = 0;
            foreach ($unid as $nro2 => $uni) {
                $parte = explode('-', $uni);
                $unidad = $parte[1];
                $cantid = $parte[0];
                $cantidades = $cantidades + $cantid;
                
                if(cantidad_unidad($db,$detalle['id_producto'],$unidad))
                    $importe = ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[$nro2];
                $importe_t = $importe_t + $importe ;
                $total12 = $total12 + $importe;
            }
        }else{
            $parte = explode('-', $unid[0]);
            $unidad = $parte[1];
            $cantid = $parte[0];
            $cantidades = $cantidades + $cantid;
            //$importe = ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad))* $precios[0];
                   //Validacion
            if(cantidad_unidad($db,$detalle['id_producto'],$unidad))
                $importe = ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[0];
            $importe_t = $importe;
            $total12 = $total12 + $importe;
        }
        // buscar id caja;
        $mayores = cantidad_unidad($db,$detalle['id_producto'],$id_caja);

        if(!$mayores){
            $mayores = '1';
        }
        //        var_dump($mayores);
        if($mayores != 1 && $mayores < $cantidades){
            $unidad_mayor = $id_caja;
            $mayor = $mayores;
        }else{
            $unidad_mayor = $detalle['unidad_producto'];
            $mayor = 1;
        }
        $unidades_t = (int)($cantidades/cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor));
        if($cantidades % cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor)==0){
            $otra_unidad = '';
            $otra_cantid = '';
        }else{
            $otra_unidad = '<br>'.nombre_unidad($db,$detalle['unidad_producto']);
            $otra_cantid = '<br>'.($cantidades-($unidades_t*cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor)));
        }
        $unidades4 = substr($unidades4,0,-4);
        
        $body12 .= '<tr height="2%" >';
        $body12 .= '<td class="left-right bot" align="right">' . $unidades_t .''.$otra_cantid. '</td>';
        $body12 .= '<td class="left-right bot">' . nombre_unidad($db,$unidad_mayor).'('.$mayor.' U.)'.''.$otra_unidad. '</td>';
        // Josema:: add line
        $body12 .= '<td class="left-right bot" align="left">' . escape($detalle['codigo']) . '</td>';
        // Josema:: add line
        $body12 .= '<td class="left-right bot" align="left">' . escape($detalle['nombre']) . '</td>';
        $body12 .= '<td class="left-right bot" align="right">' . $detalle['descripcion']. '</td>';
        $body12 .= '<td class="left-right bot" align="right">' . $detalle['categoria']. '</td>';
        $body12 .= '<td class="left-right bot" align="right">' . number_format($importe_t, 2, '.', '') . '</td>';
        $body12 .= '</tr>';
    }

      /*
    *PRODUCTOS DEVUELTOS
     */

    $vendidos = $detalles12;

    /** PRODUCTOS DEVUELTOS */
    $total2 = 0;
    foreach ($detalles2 as $nro => $detalle) {
        $importe = 0;
        $sugerido = escape($detalle['precio_sugerido']);
        $descuento = escape($detalle['descuento']);
        $unid = explode('|', $detalle['cantidades']);
        $precios = explode('|', $detalle['precios']);
        $mayor = 0;
        $unidad_mayor = 0;
        $cantidades = 0;
        $importe_t = 0;
        $total_venta_devuelto = 0;
        $cantidad_devuelta_producto = 0;
        $venta_devuelto = 0;

        if(count($unid)>1){
            //si tiene mas unidades
            foreach ($unid as $nro2 => $uni) {
                $parte = explode('-', $uni);
                $unidad = $parte[1];
                $cantid = $parte[0];
                $cantidades = $cantidades + $cantid;
                $importe = ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[$nro2];
                $importe_t = $importe_t + $importe ;
                $total2 = $total2 + $importe;
            }
        }else{
            $parte = explode('-', $unid[0]);
            $unidad = $parte[1];
            $cantid = $parte[0];
            $cantidades = $cantidades + $cantid;
            $importe = ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[0];
            $importe_t = $importe;
            $total2 = $total2 + $importe;
        }

        $id_producto = $detalle['id_producto'];

        //obtiene las ventas directas realizadas
        $producto_devuelto = $db->query('SELECT GROUP_CONCAT(b.cantidad, "-", b.unidad_id SEPARATOR "|" ) AS cantidades, 
        GROUP_CONCAT(b.precio SEPARATOR "|" ) AS precios, SUM(a.monto_total) AS m_total,  c.*, b.*, c.unidad_id AS unidad_producto, d.categoria
		FROM tmp_egresos a
		LEFT JOIN tmp_egresos_detalles b ON a.id_tmp_egreso = b.tmp_egreso_id
		LEFT JOIN inv_productos c ON b.producto_id = c.id_producto
		LEFT JOIN inv_categorias d ON c.categoria_id = d.id_categoria
		WHERE a.estado = 2 AND a.distribuidor_id = ' . $distribuidor . ' AND a.distribuidor_estado = "VENTA" and b.promocion_id != 1
		AND b.producto_id = ' . $id_producto . '
		GROUP BY c.id_producto ORDER BY c.descripcion ASC, c.nombre_factura ASC')->fetch_first();
        
        if (count($producto_devuelto) > 0) {

            $precio_dev = explode("|", $producto_devuelto['precios']);
            $cant_unid_dev = explode("|", $producto_devuelto['cantidades']);
            
            foreach ($cant_unid_dev as $key => $value) {
                $parte = explode('-', $value);
                $unidad = $parte[1];
                $cantid = $parte[0];
                if(cantidad_unidad($db, $id_producto, $unidad)){
                    $venta_devuelto = ($cantid / cantidad_unidad($db, $id_producto, $unidad)) * $precio_dev[$key];
                    $total_venta_devuelto = $total_venta_devuelto + $venta_devuelto ;
                    $cantidad_devuelta_producto = (($cantid / cantidad_unidad($db, $id_producto, $unidad))) ? ($cantid / cantidad_unidad($db, $id_producto, $unidad)) : 0;
                }                
            }
            $importe_t =  $importe_t - $total_venta_devuelto;
            $total2 =  $total2 - $total_venta_devuelto;
        }

        // buscar id caja;
        $mayores = cantidad_unidad($db,$detalle['id_producto'],$id_caja);
        if(!$mayores){
            $mayores = '1';
        }

        if($mayores != 1 && $mayores < $cantidades){
            $unidad_mayor = $id_caja;
            $mayor = $mayores;
        }else{
            $unidad_mayor = $detalle['unidad_producto'];
            $mayor = 1;
        }

        $unidades_t = (int)($cantidades/cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor));

        if($cantidades % cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor)==0){
            $otra_unidad = '';
            $otra_cantid = '';

        }else{
            $otra_unidad = '<br>'.nombre_unidad($db,$detalle['unidad_producto']);
            $otra_cantid = '<br>'.($cantidades-($unidades_t * cantidad_unidad($db,$detalle['id_producto'],$unidad_mayor)));

        }

        $unidades_t = (count($producto_devuelto) > 0) ? $unidades_t - $cantidad_devuelta_producto: $unidades_t;

        if ($unidades_t > 0) {            
            $unidades4 = substr($unidades4,0,-4);
            $body2 .= '<tr height="2%">';
            $body2 .= '<td class="left-right bot" align="right">' . $unidades_t .''.$otra_cantid. '</td>';
            $body2 .= '<td class="left-right bot">' . nombre_unidad($db,$unidad_mayor).'('.$mayor.' U.)' .''.$otra_unidad. '</td>';
            // Josema:: add line
            $body2 .= '<td class="left-right bot" align="left">' . escape($detalle['codigo']) . '</td>';
            // Josema:: add line
            $body2 .= '<td class="left-right bot" align="left">' . escape($detalle['nombre_factura']) . '</td>';
            $body2 .= '<td class="left-right bot" align="right">' . $detalle['descripcion']. '</td>';
            $body2 .= '<td class="left-right bot" align="right">' . $detalle['categoria']. '</td>';
            $body2 .= '<td class="left-right bot" align="right">' . number_format($importe_t, 2, '.', '') . '</td>';
            $body2 .= '</tr>';
        }
    }


    $body6 = '';
    $importeD = 0;
    foreach ($detalles3 as $nro => $detalle) {
        //$total = $total + $detalle['prec'];
        $importeD = $importeD + $detalle['descripcion_venta'];

        $body6 .= '<tr height="2%">';
        $body6 .= '<td class="left-right bot" >' . $detalle['cliente_id']. '</td>';
        $body6 .= '<td class="left-right bot">'.$detalle['nombre_cliente'].'</td>';
        $body6 .= '<td class="left-right bot" align="right">' . escape($detalle['descripcion_venta']) . '</td>';
        $body6 .= '</tr>';

    }

    /** RESUMEN DE ANULADOS POR CLIENTE */
    $resumen = $detalles4;
    if ($anulados) {
        foreach ($resumen as $nro => $valor) {
            foreach ($anulados as $key => $value) {  
                if (($anulados[$key]['id_egreso'] == $resumen[$nro]['id_egreso']) && ($anulados[$key]['cliente_id'] == $resumen[$nro]['cliente_id'])) {
                    unset($resumen[$nro]);
                }                
            }
        }
        $detalles4 = $resumen;
    }

   /** RESUMEN DE MOVIMIENTOS */
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

        $saldoC = $saldoC + $m_pendiente;
        $totalCC = $totalCC + $cuentas['monto_total'];
        $total7 = $total7 + $m_cancelado;
        
        $body7 .= '<tr height="2%">';
        $body7 .= '<td class="left-right bot" >' . $cuentas['cliente_id'] . '</td>';
        $body7 .= '<td class="left-right bot">' . $cuentas['cliente']  . ' <small> | Razon Social:' . $cuentas['nombre_factura'] . '</small>' . ' </td>';
        $body7 .= '<td class="left-right bot">' . $cuentas['nit_ci'] . '</td>';
        $body7 .= '<td class="left-right bot">' . $cuentas['monto_total'] . '</td>';
        $body7 .= '<td class="left-right bot">' . escape($m_pendiente) . '</td>';
        $body7 .= '<td class="left-right bot" align="right">' . escape($m_cancelado) . '</td>';
        $body7 .= '</tr>';
    endforeach;


    /**COBROS DEUDAS ANTERIORES */

    $body8 = '';
    $importePendiente = 0; $total_cajas_cob=0;

    foreach ($detalles5 as $nro => $cuentas):
        $cancelado = $db->select('sum(if(estado = 1, monto,0)) as cancelado, sum(if(estado = 0, monto,0)) as pendiente')->from('inv_pagos_detalles')->where('pago_id', $cuentas['id_pago'])->fetch_first();
        $importePendiente = $importePendiente + $cuentas['monto_cancelado'];
        //$cajas_cobrados = 0;

        $total8 = escape((($cuentas['monto_cancelado'] > 0) ? $cuentas['monto_cancelado'] : 0));

        //$total_cajas_cob=$total_cajas_cob+$cajas_cobrados;
        $body8 .= '<tr height="2%">';
        $body8 .= '<td class="left-right bot" >' . $cuentas['cliente_id'] . '</td>';
        $body8 .= '<td class="left-right bot">' . $cuentas['nombre_cliente'] . ' ' . $cuentas['nit_ci'] . '</td>';
        //$body8 .= '<td class="left-right bot">' . $cajas_cobrados . '</td>';
        $body8 .= '<td class="left-right bot">' . $cuentas['monto_total'] . '</td>';
        $body8 .= '<td class="left-right bot">' . escape(number_format($cancelado['cancelado'], 2, '.', '')) . '</td>';
        $body8 .= '<td class="left-right bot">' . escape(number_format($cancelado['pendiente'], 2, '.', '')) . '</td>';
        $body8 .= '<td class="left-right bot" align="right">' . escape(number_format($cuentas['monto_cancelado'], 2, '.', '')) . '</td>';
        $body8 .= '</tr>';
    endforeach;

    /** PRODUCTOS ENTREGADOS */
    // Obtiene el valor total
    $valor_total = number_format(($total), 2, '.', '');
    $valor_totalT = number_format(($total - $importeD), 2, '.', '');

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $valor_total);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = strtoupper($conversor->to_word($monto_numeral));

    $body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;
    
    
    /** PRODUCTOS VENDIDOS - VENTA DIRECTA */
    // Obtiene el valor total
    $valor_total12 = number_format(($total12), 2, '.', '');
    
    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual12 = explode('.', $valor_total12);
    $monto_numeral12 = $monto_textual12[0];
    $monto_decimal12 = $monto_textual12[1];
    $monto_literal12 = strtoupper($conversor->to_word($monto_numeral12));

    $body12 = ($body12 == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body12;

    /** PRODUCTOS DEVUELTOS */
    // Obtiene el valor total
    $valor_total2 = number_format($total2, 2, '.', '');

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual2 = explode('.', $valor_total2);
    $monto_numeral2 = $monto_textual2[0];
    $monto_decimal2 = $monto_textual2[1];
    $monto_literal2 = strtoupper($conversor->to_word($monto_numeral2));

    $body2 = ($body2 == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body2;
    

    /** RESUMEN DE MOVIMIENTOS */
    // Obtiene el valor total
    $valor_total7 = number_format($total7, 2, '.', '');

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual7 = explode('.', $valor_total7);
    $monto_numeral7 = $monto_textual7[0];
    $monto_decimal7 = $monto_textual7[1];
    $monto_literal7 = strtoupper($conversor->to_word($monto_numeral7));

    $body7 = ($body7 == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body7;
    
    

    /** COBROS DEUDAS ANTERIORES */
    $valor_total8 = number_format($total8, 2, '.', '');

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual8 = explode('.', $valor_total8);
    $monto_numeral8 = $monto_textual8[0];
    $monto_decimal8 = $monto_textual8[1];
    $monto_literal8 = strtoupper($conversor->to_word($monto_numeral8));

    $body8 = ($body8 == '') ? '<tr><td colspan="7" align="center" class="all">No existen cobros realizados en la jornada.</td></tr>' : $body8;


    // Obtiene el total de descuentos vendedores
    $valor_total3 = number_format($importeD, 2, '.', '');
    //VALOR FINAL
    $valor_final = number_format((($valor_total + $valor_total2) - $valor_total12), 2, '.', '');
    
    $valor_total_T = number_format($total7 + $valor_total12, 2, '.', '');
    
    
    // MONTO LIQUIDACION
    $valor_total_liquidacion = number_format(($valor_total12 + $total7 +  $valor_total8) , 2, '.', '');

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textualT = explode('.', $valor_totalT);
    $monto_numeralT = $monto_textualT[0];
    $monto_decimalT = $monto_textualT[1];
    $monto_literalT = strtoupper($conversor->to_word($monto_numeralT));


    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual3 = explode('.', $valor_total3);
    $monto_numeral3 = $monto_textual3[0];
    $monto_decimal3 = $monto_textual3[1];
    $monto_literal3 = strtoupper($conversor->to_word($monto_numeral3));

    $body6 = ($body6 == '') ? '<tr><td colspan="3" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body6;

// Formateamos la tabla
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
<td colspan="4" align="center"  bgcolor="#CCCCCC" ><h1>LIQUIDACIÓN</h1></td>
</tr>

<tr><td align="right"><b>VENDEDORES:</b></td>
<td align="left">$valor_empleado2</td>
<td align="right"><b>HOJA DE SALIDA:</b></td>
<td  align="left">$valor_final</td>
</tr>
<tr>
<td align="right"><b>DISTRIBUIDOR:</b></td>
<td align="left">$valor_empleado</td>
<td align="right"><strong>FECHA</strong></td>
<td align="left">$valor_fecha</td>
</tr>
<tr>
<td  align="center" colspan="4" ></td>
</tr>

<tr>
<td  align="center" colspan="4"  bgcolor="#CCCCCC"><h3>PRODUCTOS ENTREGADOS</h3></td>
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
<th width="12%" class="all" align="right">IMPORTE $valor_moneda</th>
</tr>
$body
<tr>
<th class="all" align="right" colspan="5">IMPORTE TOTAL $valor_moneda</th>
<th class="all" align="right">$valor_total</th>
</tr>
</table>
<p align="right">$monto_literal $monto_decimal /100</p>
<table cellpadding="1">
<tr>
<td colspan="3"></td>
</tr>
<tr>
<td class="none" align="center" colspan="3" bgcolor="#CCCCCC" ><h3>PRODUCTOS DEVUELTOS - NO ENTREGADOS</h3></td>
</tr>
</table>
<br><br>
<table cellpadding="2">
<tr>
<th width="6%" class="all" align="left">CANT.</th>
<th width="12%" class="all" align="left">UNIDAD</th>
<th width="7%" class="all" align="left">CODIGO</th>
<th width="35%" class="all" align="left">DETALLE</th>
<th width="15%" class="all" align="left">DESCRIPCIÓN</th>
<th width="13%" class="all" align="right">CATEGORÍA</th>
<th width="12%" class="all" align="right">IMPORTE $valor_moneda</th>
</tr>
$body2
<tr>
<th class="all" align="right" colspan="6">IMPORTE TOTAL $valor_moneda</th>
<th class="all" align="right">$valor_total2</th>
</tr>
</table>
<p align="right">$monto_literal2 $monto_decimal2 /100</p>
<table cellpadding="1">
<tr>
<td colspan="3"></td>
</tr>
<tr>
<td  align="center" colspan="4"  bgcolor="#CCCCCC"><h3>PRODUCTOS - VENTA DIRECTA</h3></td>
</tr>
</table>
<BR>
<br>
<table cellpadding="2">
<tr>
<th width="6%" class="all" align="left">CANT.</th>
<th width="11%" class="all" align="left">UNIDAD</th>
<th width="8%" class="all" align="left">CODIGO</th>
<th width="35%" class="all" align="left">DETALLE</th>
<th width="15%" class="all" align="left">DESCRIPCIÓN</th>
<th width="13%" class="all" align="right">CATEGORÍA</th>
<th width="12%" class="all" align="right">IMPORTE $valor_moneda</th>
</tr>
$body12
<tr>
<th class="all" align="right" colspan="6">IMPORTE TOTAL - EFECTIVO $valor_moneda</th>
<th class="all" align="right">$valor_total12</th>
</tr>
</table>
<p align="right">$monto_literal12 $monto_decimal12 /100</p>
<table cellpadding="1">
<tr>
<td colspan="3"></td>
</tr>
<tr>
<td align="center" colspan="3" bgcolor="#CCCCCC"><h3>RESUMEN DE MOVIMIENTOS</h3></td>
</tr>
</table>
<BR>
<br>
<table cellpadding="2">
<tr>
<th width="10%" class="all" align="left">CODIGO</th>
<th width="40%" class="all" align="left">CLIENTE</th>
<th width="10%" class="all" align="left">NIT/CI</th>
<th width="15%" class="all" align="left">MONTO VENDIDO</th>
<th width="13%" class="all" align="left">SALDO (Bs)</th>
<th width="12%" class="all" align="left">COBRO (Bs)</th>
</tr>
$body7
<tr>
<th class="all" align="right" colspan="3">IMPORTE TOTAL $valor_moneda</th>
<th class="all" align="left">$totalCC</th>
<th class="all" align="left">$saldoC</th>
<th class="all" align="right">$valor_total7</th>
</tr>
<tr>
<th class="all" align="right" colspan="5">IMPORTE TOTAL - EFECTIVO $valor_moneda</th>
<th class="all" align="right">$valor_total7</th>
</tr>
</table>
<p align="right">$monto_literal7 $monto_decimal7 /100</p>
<table cellpadding="1">
<tr>
<td colspan="3"></td>
</tr>
<tr>
<td align="center" colspan="3" bgcolor="#CCCCCC"><h3>COBROS DEUDAS ANTERIORES</h3></td>
</tr>
</table>
<BR>
<br>
<table cellpadding="2">
<tr>
<th width="10%" class="all" align="left">CODIGO</th>
<th width="46%" class="all" align="left">CLIENTE</th>
<th width="11%" class="all" align="left">TOTAL</th>
<th width="11%" class="all" align="left">T. PAGADO</th>
<th width="11%" class="all" align="left">SALDO (Bs)</th>
<th width="11%" class="all" align="left">COBRO (Bs)</th>
</tr>
$body8

<tr>
<th class="all" colspan="5" align="right">IMPORTE TOTAL - EFECTIVO $valor_moneda</th>
<th class="all" align="right">$valor_total8</th>
</tr>
</table>
<p align="right">$monto_literal8 $monto_decimal8 /100</p>
<table cellpadding="2">
<tr>
<td width="50%" align="right"></td>
<td width="38%" align="right">MONTO LIQUIDACIÓN - EFECTIVO $valor_moneda</td>
<td width="12%" class="bot2" align="RIGHT">$valor_total_liquidacion</td>
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
<td width="50%" class="none" align="center" >Recibi conforme </td>
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

// Imprime la tabla
    $pdf->writeHTML($tabla, true, false, false, false, '');


    if ($auxiliar == 10) {
// Salto de linea
        $pdf->Ln(2);
    }
    elseif ($auxiliar == 9) {
// Salto de linea
        $pdf->Ln(25);
    }elseif ($auxiliar == 8) {
        // Salto de linea
        $pdf->Ln(65);
    }elseif ($auxiliar == 7) {
        // Salto de linea
        $pdf->Ln(65);
    }elseif ($auxiliar == 6) {
        // Salto de linea
        $pdf->Ln(85);
    }elseif ($auxiliar == 5) {
        // Salto de linea
        $pdf->Ln(105);
    }elseif ($auxiliar < 5) {
        // Salto de linea
        $pdf->Ln(185);
    }
$style3 = array('width' => 1, 'cap' => 'round', 'join' => 'round', 'dash' => '2,10', 'color' => array(255, 0, 0));

}

// Genera el nombre del archivo
$nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
