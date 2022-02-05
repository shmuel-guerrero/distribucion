<?php

// Obtiene el orden de compra
$id_egreso = (isset($params[0])) ? $params[0] : 0;
$id_tmp_egreso = (isset($params[1])) ? $params[1] : 0;

if ($id_egreso == 0) {
    // Error 404
    require_once not_found();
    exit;
}
$egreso = $db->select('*')->from('tmp_egresos a')->join('inv_clientes c','a.cliente_id = c.id_cliente')->join('sys_empleados b','a.empleado_id = b.id_empleado')->where('a.id_egreso ', $id_egreso)->where('a.id_tmp_egreso ', $id_tmp_egreso)->fetch_first();
$dia_egreso = date('w', strtotime($egreso['fecha_egreso']));
$ruta = $db->select('nombre')->from('gps_rutas')->where('empleado_id',$egreso['empleado_id'])->where('dia', $dia_egreso)->fetch_first();
$ruta = $ruta['nombre'];

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
$pdf = new MYPDF('P', 'pt', array(612,935), true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(30, 20, 30);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, 50 + 55);

// Adiciona la pagina
$pdf->AddPage();

$aux1 = 0;
$aux2 = 0;

// Obtiene los detalles
$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura, p.precio_sugerido')->from('tmp_egresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')->where('d.egreso_id', $id_egreso)->where('d.tmp_egreso_id', $id_tmp_egreso)->order_by('id_detalle asc')->fetch();
$detalles = $db->query("select d.*, p.codigo, p.nombre, p.nombre_factura, f.nombre as nombre_promo from tmp_egresos_detalles d left join inv_productos p ON d.producto_id = p.id_producto left join (SELECT c.id_promocion, e.nombre FROM inv_promociones c left join inv_productos e on c.id_promocion = e.id_producto ) AS f ON d.promocion_id = f.id_promocion where d.egreso_id = '$id_egreso' and d.tmp_egreso_id = '$id_tmp_egreso' and promocion_id != 1 GROUP by d.id_detalle order by id_detalle asc")->fetch();

$auxiliar1 = count($detalles);
$aux2 = $aux2 + $auxiliar1;

// Asigna la orientacion de la pagina
$pdf->SetPageOrientation('P');

// Establece la fuente del titulo
$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);

// Titulo del documento
$pdf->Cell(0, 10, '', 0, true, 'C', false, '', 0, false, 'T', 'M');
$h = substr($egreso['fecha_egreso'],0,4);
$m = substr($egreso['fecha_egreso'],5,2);
$d = substr($egreso['fecha_egreso'],8,2);

// Salto de linea


// Establece la fuente del contenido
$pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);

// Define las variables
$valor_fecha = escape(date_decode($egreso['fecha_egreso'], $_institution['formato']) . ' ' . $egreso['hora_egreso']);
$valor_nombre_cliente = escape($egreso['nombre_cliente']);
$valor_nit_ci = escape($egreso['nit_ci']);
$valor_direccion = escape($egreso['direccion']);
$valor_descripcion = escape($egreso['descripcion']);
$valor_telefono = escape($egreso['telefono']);
$valor_empleado = escape($egreso['nombres'] . ' ' . $egreso['paterno'] . ' ' . $egreso['materno']);
$valor_descuento = escape($egreso['descuento']);
$valor_empresa = ($egreso['cargo']==1) ? $_institution['empresa1'] : $_institution['empresa2'];
$valor_observacion = escape($egreso['observacion']);
$valor_id_cliente = escape($egreso['id_cliente']);
$valor_telefono = escape($egreso['telefono']);
$detalle_venta = escape($egreso['descripcion_venta']);
$valor_moneda = $moneda;
$total = 0;
$nro_nota = 1;
$body1 = '<tr height="2%"><td width="20%" align="center"><h3> '.$valor_empresa.'</h3></td><td width="25%" align="center"><h3> NOTA DE VENTA ' . $nro_nota . '</h3></td><td width="45%"><b>VENDEDOR(A): </b>'.$valor_empleado.'</td><td width="10%" align="right">'.date('d/m/d').'</td></tr>';

// Establece la fuente del contenido
$pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

// Estructura la tabla
$body = '';
foreach ($detalles as $nro => $detalle) {
    //var_dump($detalle);exit();
    $cantidad = escape($detalle['cantidad']);
    $precio = escape($detalle['precio']);
    $pr = $db->select('*')->from('inv_productos a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad')->where('a.id_producto',$detalle['producto_id'])->fetch_first();
    if($pr['unidad_id'] == $detalle['unidad_id']){
        $unidad = $pr['unidad'];
    }else{
        $pr = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad AND a.visible = "s"')->where(array('a.producto_id'=>$detalle['producto_id'],'a.unidad_id'=>$detalle['unidad_id']))->fetch_first();
        $unidad = $pr['unidad'];
        $cantidad = $cantidad/$pr['cantidad_unidad'];
    }
    $uni_detalle = cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_id']);
    $precio_sugerido = $detalle['precio_sugerido'];
    $importe = $cantidad * $precio;
    $total = $total + $importe;
    $body .= '<tr height="2%">';
    $body .= '<td class="left-right bot" align="right">' . $cantidad .' '.$unidad.'('.$uni_detalle.'U.)'. '</td>';
    $body .= '<td class="left-right bot">' . escape($detalle['nombre_factura']) . '</td>';
    $body .= '<td class="left-right bot">' . escape($detalle['nombre_promo']) . '</td>';
    $body .= '<td class="left-right bot" align="right">' . $precio . '</td>';
    $body .= '<td class="left-right bot" align="right">' . $cantidad . '</td>';
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
	.none {
		border: 1px solid #fff;
	}
	.bot {
		border-top: 1px solid #444;
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
			<td width="32%" class="none" align="left">$valor_nombre_cliente</td>
			<td width="11%" class="none" align="right"><b>NIT/CI:</b></td>
			<td width="17%" class="none" align="left">$valor_nit_ci</td>
			<td width="15%" class="none" align="right"><b>TELÉFONO:</b></td>
			<td width="10%" class="none" align="left">$valor_telefono</td>
		</tr>
		<tr>
            <td class="none" align="right"><b>DIRECCIÓN:</b></td>
			<td class="none" align="left">$valor_direccion</td>
			<td class="none" align="right"><b>PRIORIDAD:</b></td>
			<td class="none" align="left">$valor_observacion</td>
            <td class="none" align="right"><b>CÓDIGO CLIENTE </b> </td>
			<td class="none" align="center"><h2>$valor_id_cliente</h2></td>
		</tr>
	</table>
	<hr>
	<table cellpadding="3">
		<tr>
			<th width="18%" class="all" align="right">CANTIDAD</th>
            <th width="35%" class="all" align="left">DETALLE</th>
            <th width="16%" class="all" align="left">PROMO</th>
            <th width="10%" class="all" align="right">PRECIO</th>
            <th width="8%" class="all" align="right">CANT.</th>
            <th width="13%" class="all" align="right">IMPORTE $valor_moneda</th>
		</tr>
		$body
		<tr>
			<th class="all" align="left" colspan="5">IMPORTE TOTAL $valor_moneda</th>
			<th class="all" align="right">$valor_total</th>
			<th bgcolor="#FFFFFF"></th><th style="border: 1px solid black;" ></th>
		</tr>
	</table>
	<table cellpadding="5">
		<tr>
			<td width="5%">$detalle_venta</td>
			<td width="40%"></td>
			<td width="55%" class="none" align="right">$monto_literal $monto_decimal /100</td>
		</tr>
	</table>
	<HR>
EOD;

// Imprime la tabla
$pdf->writeHTML($tabla, true, false, false, false, '');

if($aux1 == 3 && $aux2 > 2 && $aux2 < 8){
    $pdf->ln(10);
    //echo 'hola';
    $aux1 = 0;
    $aux2 = 0;
}


// Genera el nombre del archivo
$nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
