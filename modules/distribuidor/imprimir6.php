<?php

// Obtiene el orden de compra
$distribuidor = (isset($params[0])) ? $params[0] : 0;

// Obtiene fecha inicial
$fecha_inicial = (isset($params[1])) ? $params[1] : date('Y-m-d');
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[2])) ? $params[2] : date('Y-m-d');
$fecha_final = date_encode($fecha_final);

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
class MYPDF extends TCPDF
{
    public function Header()
    {

    }
    public function Footer()
    {

    }
}
$nro_nota = 0;
// Instancia el documento PDF
$pdf = new MYPDF('P', 'pt', array(612,935), true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(30, 20, -1, false);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, 50);


// Adiciona la pagina
$pdf->AddPage();

$aux1 = 0;
$aux2 = 0;

//BUSCAMOS LOS CLIENTES DEL VENDEDOR
        $ordenes = $db->query("SELECT te.*, SUM(te.monto_total) as suma_egreso, g.nombre as nombre_ruta, c.descripcion as referencia, a.almacen, a.principal, w.nombres, w.paterno, w.materno, w.cargo
                                FROM tmp_egresos te
                                LEFT JOIN sys_empleados w ON te.empleado_id = w.id_empleado
                                LEFT JOIN gps_rutas g ON g.id_ruta = te.ruta_id
                                LEFT JOIN inv_clientes c ON c.id_cliente = te.cliente_id
                                LEFT JOIN inv_almacenes a ON a.id_almacen = te.almacen_id
                                WHERE te.distribuidor_id = '$distribuidor' AND te.grupo = ''  AND te.distribuidor_fecha BETWEEN '$fecha_inicial' AND '$fecha_final'
                                GROUP BY te.id_egreso
                                ORDER BY te.cliente_id")->fetch();
//echo $db->last_query();
//var_dump($ordenes);
        if ($ordenes) {
            foreach ($ordenes as $orden) {
                $aux1 = $aux1 + 1;
                $id_orden3 = $orden['id_egreso'];

                // Obtiene los detalles
                $detalles = $db->query("select d.*, p.codigo, p.nombre, p.nombre_factura, f.nombre as nombre_promo
                                          from tmp_egresos_detalles d
                                          left join inv_productos p ON d.producto_id = p.id_producto
                                          left join (
                                                SELECT c.id_promocion, e.nombre
                                                FROM inv_promociones c
                                                left join inv_productos e on c.id_promocion = e.id_producto ) AS f ON d.promocion_id = f.id_promocion
                                          where d.egreso_id = '$id_orden3' and promocion_id != 1
                                          GROUP by d.id_detalle order by id_detalle asc")->fetch();
                //var_dump($detalles);
                $aux2 = $aux2 + count($detalles);

                if ($aux1 == 2) {
                    if ($aux2 > 49) {
                        $pdf->AddPage();
                        $aux1 = 1;
                        $aux2 = count($detalles);
                    }
                }
                if ($aux1 == 3) {
                    if ($aux2 > 36) {
                        $pdf->AddPage();
                        $aux1 = 1;
                        $aux2 = count($detalles);
                    }
                }
                if ($aux1 == 4) {
                    if ($aux2 > 27) {
                        $pdf->AddPage();
                        $aux1 = 1;
                        $aux2 = count($detalles);
                    }
                }
                if ($aux1 == 5) {
                    if ($aux2 > 18) {
                        $pdf->AddPage();
                        $aux1 = 1;
                        $aux2 = count($detalles);
                    }
                }
                if ($aux1 == 6) {
                    if ($aux2 > 10) {
                        $pdf->AddPage();
                        $aux1 = 1;
                        $aux2 = count($detalles);
                    }
                }
                if ($aux1 == 7) {
                    $pdf->AddPage();
                    $aux1 = 1;
                    $aux2 = count($detalles);
                }

// Asigna la orientacion de la pagina
                $pdf->SetPageOrientation('P');

// Establece la fuente del titulo
                $pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);

// Titulo del documento
                $pdf->Cell(0, 10, '', 0, true, 'C', false, '', 0, false, 'T', 'M');
                $h = substr($orden['fecha_egreso'], 0, 4);
                $m = substr($orden['fecha_egreso'], 5, 2);
                $d = substr($orden['fecha_egreso'], 8, 2);
                $nro_nota = $nro_nota + 1;
                $valor_empresa = ($orden['cargo'] == 1) ? $_institution['empresa1'] : $_institution['empresa2'];
                $valor_empleado = escape($orden['nombres'] . ' ' . $orden['paterno'] . ' ' . $orden['materno']);
                $valor_fecha = escape($orden['fecha_egreso']);
                $body1 = '<tr height="2%"><td width="20%" align="center"><h3> '.$valor_empresa.'</h3></td><td width="25%" align="center"><h3> NOTA DE VENTA #' . $nro_nota . '</h3></td><td width="45%"><b>VENDEDOR(A): </b>'.$valor_empleado.'</td><td width="10%" align="right"><h3>'.$valor_fecha.'</h3></td></tr>';
// Salto de linea

// Establece la fuente del contenido
                $pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);

// Define las variables
                $valor_fecha = escape(date_decode($orden['fecha_egreso'], $_institution['formato']) . ' ' . $orden['hora_egreso']);
                $valor_nombre_cliente = escape($orden['nombre_cliente']);
                $valor_nit_ci = escape($orden['nit_ci']);
                $valor_direccion = escape($orden['direccion']);
                $valor_descripcion = escape($orden['referencia']);
                $valor_telefono = escape($orden['telefono']);


                $valor_descuento = escape($orden['descuento']);
                $valor_observacion = escape($orden['observacion']);
                $valor_id_cliente = escape($orden['id_cliente']);
                $detalle_venta = escape($orden['descripcion_venta']);
                $valor_moneda = $moneda;
                $total = 0;


// Establece la fuente del contenido
                $pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

// Estructura la tabla
                $body = '';

                foreach ($detalles as $nro => $detalle) {
                    //var_dump($detalle);exit();
                    $cantidad = escape($detalle['cantidad']);
                    $precio = escape($detalle['precio']);
                    $pr = $db->select('*')->from('inv_productos a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad')->where('a.id_producto', $detalle['producto_id'])->fetch_first();
                    if ($pr['unidad_id'] == $detalle['unidad_id']) {
                        $unidad = $pr['unidad'];
                    } else {
                        $pr = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad  AND a.visible = "s"')->where(array('a.producto_id' => $detalle['producto_id'], 'a.unidad_id' => $detalle['unidad_id'], 'a.visible' => 's'))->fetch_first();
                        if($pr['cantidad_unidad'])
                        {
                            $unidad = $pr['unidad'];
                            $cantidad = $cantidad / $pr['cantidad_unidad'];
                        }
                    }
                    //Validacion
                    if ($detalle['unidad_id']!=0)
                        $uni_detalle = cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id']);

                    $precio_sugerido = $detalle['precio_sugerido'];
                    $importe = $cantidad * $precio;
                    $total = $total + $importe;
                    $body .= '<tr height="2%">';
                    $body .= '<td class="left-right bot" align="right">' . $cantidad . '</td>';
                    $body .= '<td class="left-right bot" align="right">' . ' ' . $unidad . '(' . $uni_detalle . ' U.)' . '</td>';
                    $body .= '<td class="left-right bot">' . escape($detalle['nombre_factura']) . '</td>';
                    $body .= '<td class="left-right bot">' . escape($detalle['nombre_promo']) . '</td>';
                    $body .= '<td class="left-right bot" align="right">' . round($precio/$uni_detalle, 2) . '</td>';
                    $body .= '<td class="left-right bot" align="right">' . number_format($importe, 2, '.', '') . '</td>';
                    $body .= '</tr>';
                }

// Obtiene el valor total
                $valor_total = number_format($total, 2, '.', '');

// Obtiene los datos del monto total
                $conversor = new NumberToLetterConverter();
                $monto_textual = explode('.', $valor_total);
                $monto_numeral = $monto_textual[0];
                $monto_decimal = $monto_textual[1];
                $monto_literal = strtoupper($conversor->to_word($monto_numeral));

                $body = ($body == '') ? '<tr><td colspan="6" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;

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
	.bot {
		border-top: 1px solid #444;
	}
	.none {
		border: 1px solid #fff;
	}
	.all {
		border: 1px solid #444;
	}
	</style>
	<table cellpadding="1" >
	    $body1
	</table>
	
	<table cellpadding="1">
		<tr>
			<td width="15%" class="none" align="right"><b>NOMBRE CLIENTE:</b></td>
			<td width="35%" class="none" align="left">$valor_nombre_cliente</td>
			<td width="11%" class="none" align="right"><b>NIT/CI:</b></td>
			<td width="14%" class="none" align="left">$valor_nit_ci</td>
			<td width="15%" class="none" align="right"><b>TELÉFONO:</b></td>
			<td width="10%" class="none" align="left">$valor_telefono</td>
		</tr>
		<tr>
            <td class="none" align="right" rowspan="2" ><b>DIRECCIÓN:</b></td>
			<td class="none" align="left" rowspan="2">$valor_direccion</td>
			<td class="none" align="right"><b>PRIORIDAD:</b></td>
			<td class="none" align="left">$valor_observacion</td>
            <td class="none" align="right"><b>CÓDIGO CLIENTE </b> </td>
			<td class="none" align="left"><h2> => $valor_id_cliente</h2></td>
		</tr>
		<tr>
		    <td class="none" align="right"><b>DESCRIPCIÓN:</b></td>
			<td class="none" align="left" colspan="2" >$valor_descripcion</td>
            
			<td class="none" align="left"></td>
		</tr>
	</table>
	<hr>
	<table cellpadding="2">
		<tr>
		    <th width="6%" class="all" align="right">CANT.</th>
			<th width="18%" class="all" align="right">UNIDAD</th>
            <th width="42%" class="all" align="left">DETALLE</th>
            <th width="15%" class="all" align="left">PROMO</th>
            <th width="7%" class="all" align="right">PRECIO</th>
            <th width="12%" class="all" align="right">IMPORTE $valor_moneda</th>
		</tr>
		$body
		<tr>
			<th class="all" align="left" colspan="5">IMPORTE TOTAL $valor_moneda</th>
			<th class="all" align="right">$valor_total</th>
			<th bgcolor="#FFFFFF"></th><th style="border: 1px solid black;" ></th>
		</tr>
	</table>
	<table cellpadding="2">
		<tr>
			<td width="5%">$detalle_venta</td>
			<td width="40%"><b></b></td>
			<td width="55%" class="none" align="right">$monto_literal $monto_decimal /100</td>
		</tr>
	</table>
	<HR>
EOD;
// Imprime la tabla
                $pdf->writeHTML($tabla, true, false, false, false, '');
            }
        }

// Genera el nombre del archivo
$nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
