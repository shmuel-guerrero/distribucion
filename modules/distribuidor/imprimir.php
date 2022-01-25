<?php

// Obtiene el orden de compra
$id_orden = (isset($params[0])) ? $params[0] : 0;
$id_emp = $id_orden;
// Obtiene fecha inicial
$fecha_inicial = (isset($params[1])) ? $params[1] : date('Y-m-d');
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[2])) ? $params[2] : date('Y-m-d');
$fecha_final = date_encode($fecha_final);

if ($id_orden == 0) {
        // Error 404
        require_once not_found();
        exit;
}
$asig = $db->select('b.*')->from('gps_asigna_distribucion a')->join('inv_egresos b', 'a.empleado_id=b.empleado_id')->where('distribuidor_id', $id_emp)->where('estado', 1)->fetch();
//var_dump($asig);

$detalles = $db->query('SELECT  GROUP_CONCAT(c.cantidad, "-", c.unidad_id SEPARATOR "|" ) AS cantidades, GROUP_CONCAT(c.precio SEPARATOR "|" ) AS precios, SUM(b.monto_total) AS m_total,  d.*, c.*, d.unidad_id as unidad_producto, f.categoria
FROM gps_asigna_distribucion a
    LEFT JOIN sys_empleados e ON a.empleado_id = e.id_empleado
    LEFT JOIN inv_egresos b ON e.id_empleado = b.empleado_id
    LEFT JOIN inv_egresos_detalles c ON b.id_egreso = c.egreso_id
    LEFT JOIN inv_productos d ON c.producto_id = d.id_producto
    LEFT JOIN inv_categorias f ON d.categoria_id = f.id_categoria
    WHERE a.distribuidor_id = ' . $id_emp . ' AND b.grupo = "" AND a.estado=1 AND b.estadoe= 2 AND b.fecha_egreso <= e.fecha AND b.fecha_egreso < NOW() GROUP BY d.id_producto ORDER BY f.categoria')->fetch();
//var_dump($detalles);



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

// Instancia el documento PDF
$pdf = new MYPDF('P', 'pt', 'A4', true, 'UTF-8', false);

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

foreach ($asig as $asi) {
        $aux = $asi['empleado_id'];
        $orden = $db->select('GROUP_CONCAT(e.id_egreso SEPARATOR "|") AS ides1')->from('sys_empleados z')->join('inv_egresos e', 'z.id_empleado = e.empleado_id')->where('e.fecha_egreso >= ', $fecha_inicial)->where('e.fecha_egreso <= ', $fecha_final)->where('e.estadoe', 1)->where('e.empleado_id', $aux)->group_by('z.id_empleado')->order_by('z.id_empleado')->fetch_first();
        $id_orden = $orden['ides1'];
        $id_ordenes = explode('|', $id_orden);


        // Adiciona la pagina
        $pdf->AddPage();


        foreach ($id_ordenes as $id_orden) {
                //RUTA
                $rut = $db->select('*')->from('gps_rutas')->where('empleado_id', $id_emp)->order_by('id_ruta desc')->fetch_first();
                $ruta = $rut['nombre'];

                // Obtiene el orden de compra
                $orden = $db->select('c.*, c.descripcion as referencia, n.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos n')->join('inv_almacenes a', 'n.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'n.empleado_id = e.id_empleado', 'left')->join('inv_clientes c', 'n.cliente_id = c.id_cliente')->where('n.id_egreso', $id_orden)->where('n.tipo', 'Venta')->where('n.provisionado', 'S')->fetch_first();

                // Obtiene los detalles
                $detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura, p.precio_sugerido')->from('inv_egresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')->where('d.egreso_id', $id_orden)->order_by('id_detalle asc')->fetch();

                $auxiliar = $db->affected_rows;

                // Asigna la orientacion de la pagina
                $pdf->SetPageOrientation('P');

                // Establece la fuente del titulo
                $pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);

                // Titulo del documento
                $pdf->Cell(0, 10, '', 0, true, 'C', false, '', 0, false, 'T', 'M');
                $h = substr($orden['fecha_egreso'], 0, 4);
                $m = substr($orden['fecha_egreso'], 5, 2);
                $d = substr($orden['fecha_egreso'], 8, 2);
                $body1 = '<tr height="4%"><td width="40%" align="center"><h2>NOTA DE VENTA ' . $orden['nro_factura'] . '</h2></td><td width="30%">RUTA: ' . $ruta . '</td><td width="10%">DÍA: ' . $d . '</td><td width="10%">MES: ' . $m . '</td><td width="10%">AÑO: ' . $h . '</td></tr>';
                // Salto de linea


                // Establece la fuente del contenido
                $pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);

                // Define las variables
                $valor_fecha = escape(date_decode($orden['fecha_egreso'], $_institution['formato']) . ' ' . $orden['hora_egreso']);
                $valor_nombre_cliente = escape($orden['nombre_cliente']);
                $valor_nit_ci = escape($orden['nit_ci']);
                $valor_direccion = escape($orden['direccion']);
                $valor_descripcion = escape($orden['referencia']);
                $valor_empleado = escape($orden['nombres'] . ' ' . $orden['paterno'] . ' ' . $orden['materno']);
                $valor_descuento = escape($orden['descuento']);
                $valor_observacion = escape($orden['observacion']);
                $valor_id_cliente = escape($orden['id_cliente']);
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
                                $pr = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad AND a.visible = "s"')->where(array('a.producto_id' => $detalle['producto_id'], 'a.unidad_id' => $detalle['unidad_id']))->fetch_first();
                                //Validacion
                                if ($pr['cantidad_unidad']) {
                                        $unidad = $pr['unidad'];
                                        $cantidad = $cantidad / $pr['cantidad_unidad'];
                                }
                        }


                        $precio_sugerido = $detalle['precio_sugerido'];
                        $importe = $cantidad * $precio;
                        $total = $total + $importe;
                        $body .= '<tr height="2%">';
                        $body .= '<td class="left-right" align="right">' . $cantidad . ' ' . $unidad . '</td>';
                        $body .= '<td class="left-right">' . escape($detalle['nombre_factura']) . '</td>';
                        $body .= '<td class="left-right">' . $precio . '</td>';
                        $body .= '<td class="left-right" align="right">' . $cantidad . '</td>';
                        $body .= '<td class="left-right" align="right">' . $precio_sugerido . '</td>';
                        $body .= '<td class="left-right" align="right">' . number_format($importe, 2, '.', '') . '</td>';
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
	.all {
		border: 1px solid #444;
	}
	</style>
	<table cellpadding="1" >
	    $body1
	</table>
	<table cellpadding="1">
		<tr>
			<td width="40%" class="none"></td>

			<td width="60%" class="none"></td>

		</tr>
		<tr>
			<td class="none" align="right"><b>NOMBRE CLIENTE:</b></td>
			<td class="none" align="center">$valor_nombre_cliente</td>
		</tr>
		<tr>
			<td class="none" align="right"><b>REFERENCIA TIENDA:</b></td>
			<td class="none" align="center">$valor_descripcion</td>
		</tr>
		<tr>
			<td class="none" align="right"><b>DIRECCIÓN:</b></td>
			<td class="none" align="center">$valor_direccion</td>
		</tr>
	</table>
	<br><br>
	<table cellpadding="5">
		<tr>
			<td width="30%"><b>RECLAMOS: </b>800-1010-12</td>
			<td width="30%" align="right"><b>VENDEDOR(A):</b></td>
			<td width="30%" align="left"><b>$valor_empleado</b></td>
			<td width="10%"><b>N° $valor_id_cliente</b></td>
		</tr>
	</table>
	<br><br>
	<table cellpadding="5">
		<tr>
			<th width="13%" class="all" align="right">CANTIDAD</th>
			<th width="40%" class="all" align="left">DETALLE</th>
			<th width="13%" class="all" align="left">PRECIO <BR> UNID.</th>
			<th width="7%" class="all" align="right">CANT.</th>
			<th width="13%" class="all" align="right">PRECIO SUGERIDO $valor_moneda</th>
			<th width="13%" class="all" align="right">IMPORTE $valor_moneda</th>
		</tr>
		$body
		<tr>
			<th class="all" align="left" colspan="5">IMPORTE TOTAL $valor_moneda</th>
			<th class="all" align="right">$valor_total</th>
		</tr>
	</table>
	<p align="right">$monto_literal $monto_decimal /100</p>
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
