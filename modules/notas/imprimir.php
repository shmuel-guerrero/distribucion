<?php 

// Obtiene el id_egreso
$id_egreso = (isset($params[0])) ? $params[0] : 0;

//var_dump($_plansistema['plan'], module, fi)
//var_dump(validar_atributo($db, $_plansistema['plan'], module, file, 'deuda'));

if ($id_egreso == 0) {
	// Obtiene las egresos
	$egresos = $db->select('p.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
					->from('inv_egresos p')
					->join('inv_almacenes a', 'p.almacen_id = a.id_almacen', 'left')
					->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')
					->where('p.empleado_id', $_user['persona_id'])
					->order_by('p.fecha_egreso desc, p.hora_egreso desc')
					->fetch();
} else {
	// Obtiene los permisos
	$permisos = explode(',', permits);
	
	// Almacena los permisos en variables
	$permiso_ver = in_array('ver', $permisos);
	
	// Obtiene la egreso
	$egreso = $db->select('p.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, cl.dias')
				  ->from('inv_egresos p')
				  ->join('inv_almacenes a', 'p.almacen_id = a.id_almacen', 'left')
				  ->join('sys_empleados e', 'p.empleado_id = e.id_empleado', 'left')
				  ->join('inv_clientes cl', 'p.cliente_id = cl.id_cliente', 'left')
				  ->where('id_egreso', $id_egreso)
				  ->fetch_first();
				  
				//   echo json_encode($egreso); die();
	
	// Verifica si existe el egreso
	if (!$egreso || $egreso['empleado_id'] != $_user['persona_id']) {
		// Error 404
		require_once not_found();
		exit;
	} elseif (!$permiso_ver) {
		// Error 401
		require_once bad_request();
		exit;
	}

	// Obtiene los detalles
	$detalles = $db->select('d.*,d.unidad_id as unidad_otra, p.codigo, p.nombre, p.nombre_factura, m.marca')
				   ->from('inv_egresos_detalles d')
				   ->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				   ->join('inv_marcas m', 'p.marca_id = m.id_marca')
				   ->where('d.egreso_id', $id_egreso)
				   ->order_by('id_detalle asc')
				   ->fetch();
}

// Obtiene las deudas

/* $deuda = $db->select('sum(monto) as monto_parcial ')
			->from('inv_pagos p')
			->join('inv_pagos_detalles pd', 'pd.pago_id=p.id_pago', 'left')
			->where('p.movimiento_id = ', $id_egreso)
			->where('estado != ', '1')
			->fetch();

$deuda_pendiente = number_format($deuda[0]['monto_parcial'], 2, '.', '');
 */

$deuda = $db->query("SELECT * FROM (SELECT ROUND(SUM(pd.monto), 2)AS total_deuda, e.nro_factura, e.cliente_id 
						FROM inv_pagos_detalles pd 
						LEFT JOIN inv_pagos p ON pd.pago_id = p.id_pago
						LEFT JOIN inv_egresos e ON e.id_egreso = p.movimiento_id
						WHERE p.tipo = 'Egreso' AND e.plan_de_pagos = 'si' 
						AND e.estadoe NOT IN (2,1) AND pd.estado != 1 AND e.cliente_id= '{$egreso['cliente_id']}'
						GROUP BY pd.pago_id) A  WHERE A.total_deuda > 0")->fetch();


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda_sin_parentesis = $moneda['sigla'];
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Define variables globales
define('DIRECCION', escape($_institution['pie_pagina']));
define('IMAGEN', escape($_institution['imagen_encabezado']));
define('ATENCION', escape($_institution['descripcion']));
define('PIE', escape($_institution['pie_pagina']));
define('TELEFONO', escape(str_replace(',', ', ', 'Telefono: '.$_institution['telefono'])));
//define('TELEFONO', date(escape($_institution['formato'])) . ' ' . date('H:i:s'));

// Extiende la clase TCPDF para crear Header y Footer
class MYPDF extends TCPDF {
	public function Header() {
		$this->Ln(5);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$this->Cell(0, 5, DIRECCION, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, ATENCION, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, TELEFONO, 0, true, 'R', false, '', 0, false, 'T', 'M');
		$imagen = (IMAGEN != '') ? institucion . '/' . IMAGEN : imgs . '/empty.jpg' ;
		$this->Image($imagen, PDF_MARGIN_LEFT, 5, '', 14, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
	}
	
	public function Footer() {
		$this->SetY(-10);
		$this->SetFont(PDF_FONT_NAME_HEAD, 'I', PDF_FONT_SIZE_HEAD);
		$length = ($this->getPageWidth() - PDF_MARGIN_LEFT - PDF_MARGIN_RIGHT) / 2;
		$number = $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages();
		$this->Cell($length, 5, $number, 'T', false, 'L', false, '', 0, false, 'T', 'M');
		$this->Cell($length, 5, PIE, 'T', true, 'R', false, '', 0, false, 'T', 'M');
	}
}

// Instancia el documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Asigna la informacion al documento
$pdf->SetCreator(name_autor);
$pdf->SetAuthor(name_autor);
$pdf->SetTitle($_institution['nombre']);
$pdf->SetSubject($_institution['propietario']);
$pdf->SetKeywords($_institution['sigla']);

// Asignamos margenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// ------------------------------------------------------------

if ($id_egreso == 0) {
	// Documento general -----------------------------------------------------

	// Adiciona la pagina
	/*$pdf->AddPage('L', array(PDF_PAGE_FORMAT_WIDTH, PDF_PAGE_FORMAT_HEIGHT));
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'BU', PDF_FONT_SIZE_MAIN);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'LISTA DE PROFORMAS PERSONALES', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA);

	// Define variables
	$valor_moneda = $moneda;

	// Estructura la tabla
	$body = '';
	foreach ($egresos as $nro => $egreso) {
		$body .= '<tr>';
		$body .= '<td>' . ($nro + 1) . '</td>';
		$body .= '<td>' . escape(date_decode($egreso['fecha_egreso'], $_institution['formato']) . ' ' . $egreso['hora_egreso']) . '</td>';
		$body .= '<td>' . escape($egreso['nombre_cliente']) . '</td>';
		$body .= '<td>' . escape($egreso['nit_ci']) . '</td>';
		$body .= '<td align="right">' . escape($egreso['nro_egreso']) . '</td>';
		$body .= '<td align="right">' . escape($egreso['monto_total']) . '</td>';
		$body .= '<td align="right">' . escape($egreso['nro_registros']) . '</td>';
		$body .= '<td>' . escape($egreso['almacen']) . '</td>';
		$body .= '<td>' . escape($egreso['nombres'] . ' ' . $egreso['paterno'] . ' ' . $egreso['materno']) . '</td>';
		$body .= '</tr>';
	}
	
	$body = ($body == '') ? '<tr><td colspan="9" align="center">No existen egresos registrados en la base de datos</td></tr>' : $body;
	
	// Formateamos la tabla
	$tabla = <<<EOD
	<style>
	th {
		background-color: #eee;
		border: 1px solid #444;
		font-weight: bold;
	}
	td {
		border-left: 1px solid #444;
		border-right: 1px solid #444;
	}
	table {
		border-bottom: 1px solid #444;
	}
	</style>
	<table cellpadding="5">
		<tr>
			<th width="6%">#</th>
			<th width="8%">Fecha</th>
			<th width="16%">Cliente</th>
			<th width="10%">NIT/CI</th>
			<th width="10%">Proforma</th>
			<th width="10%">Monto $valor_moneda</th>
			<th width="8%">Registros</th>
			<th width="12%">Almacén</th>
			<th width="20%">Empleado</th>
		</tr>
		$body
	</table>
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'egresos_' . date('Y-m-d_H-i-s') . '.pdf';*/
} else {
	// Documento individual --------------------------------------------------
	
	// Asigna la orientacion de la pagina
	$pdf->SetPageOrientation('P');
	
	// Adiciona la pagina
	$pdf->AddPage();
	
	// Establece la fuente del titulo
	$pdf->SetFont(PDF_FONT_NAME_MAIN, 'B', 16);
	
	// Titulo del documento
	$pdf->Cell(0, 10, 'NOTA DE REMISIÓN', 0, true, 'C', false, '', 0, false, 'T', 'M');
	
	// Salto de linea
	$pdf->Ln(5);
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);
	
	// Define las variables
	$valor_fecha = escape(date_decode($egreso['fecha_egreso'], $_institution['formato']) . ' ' . $egreso['hora_egreso']);
	$valor_nombre_cliente = escape($egreso['nombre_cliente']);
	$valor_nit_ci = escape($egreso['nit_ci']);
	$valor_nro_egreso = escape($egreso['nro_egreso']);
	$valor_monto_total = escape($egreso['monto_total']);
	$valor_nro_registros = escape($egreso['nro_factura']);
	$valor_almacen = escape($egreso['almacen']);
	$valor_empleado = escape($egreso['nombres'] . ' ' . $egreso['paterno'] . ' ' . $egreso['materno']);
	$valor_descuento_global = escape($egreso['descuento_bs']);
	$valor_moneda = $moneda;
	$total = 0;
	$valor_lugar = 'LA PAZ';
	
	$monto_total = number_format($egreso['monto_total'], 2, '.', '');
	$monto_total_descuento = number_format($egreso['monto_total_descuento'], 2, '.', '');

	$valor_descuento_porc = ($egreso['descuento_porcentaje'] != 0 ? ' ('.escape($egreso['descuento_porcentaje']).'%)' : '');
		
	// para creditos HGC
	if ($egreso['plan_de_pagos'] == 'si') {
	    $dias = $egreso['dias'];
	   // $credito = '<span><br><center><b>CRÉDITO</b></center></span>';
	    $credito = '<br> <div style="border-width: 1px; border-style: dashed; "> <span style="text-align: center;"><br><center>GRACIAS POR SU PREFERENCIA</center></span> <br> Modalidad de Venta: <b>CRÉDITO</b>  <br> Días plazo: <b>'.$dias.'</b>  </div>';
	} else {
	    $creditto = '';
	}
	
	// Establece la fuente del contenido
	$pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

	// Estructura la tabla
	$body = '';
	foreach ($detalles as $nro => $detalle) {
		if($detalle['promocion_id'] != 1) {
			$cantidad = escape($detalle['cantidad']/cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_otra']));
			$precio = escape($detalle['precio']);
			
			$descuento = escape($detalle['descuento']);
			$monto_descuento = $precio * $descuento / 100;
			$descuento = $descuento == 0 ? '0' : number_format($monto_descuento, 2, '.', '').' ('.$descuento.'%)';
			$precio_con_descuento = $precio - $monto_descuento;
			
			$importe = $cantidad * $precio_con_descuento;
			// var_dump($cantidad.' '.$precio_con_descuento.' '.$importe);die();
			$total = $total + $importe;

			$body .= '<tr>';
			$body .= '<td class="left-right">' . ($nro + 1) . '</td>';
			$body .= '<td class="left-right">' . escape($detalle['codigo']) . '</td>';
			$body .= '<td class="left-right">' . escape($detalle['nombre_factura']) . ' <small class="text-success"> ' . (($detalle['marca']) ? escape($detalle['marca']) : '') . '</small>' . '</td>';
			$body .= '<td class="left-right" align="right">' . $cantidad . ' '.nombre_unidad($db,$detalle['unidad_otra']). '</td>';
			$body .= '<td class="left-right" align="right">' . $precio . '</td>';
			//$body .= '<td class="left-right" align="right">' . $descuento . '</td>';
			$body .= '<td class="left-right" align="right">' . number_format($importe, 2, '.', '') . '</td>';
			$body .= '</tr>';
		} else {
			$body .= '<tr style="background-color: #eee">';
			$body .= '<td class="left-right">' . ($nro + 1) . '</td>';
			$body .= '<td class="left-right">' . escape($detalle['codigo']) . '</td>';
			$body .= '<td class="left-right">' . escape($detalle['nombre_factura']) . ' <small class="text-success"> ' . (($detalle['marca']) ? escape($detalle['marca']) : '') . '</small>' . '</td>';
			$body .= '<td class="left-right"></td>';
			$body .= '<td class="left-right"></td>';
			$body .= '<td class="left-right"></td>';
			$body .= '<td class="left-right"></td>';
			$body .= '</tr>';
		}
}

	/*	DETALLE DEL MONTO TOTAL DE CADA DEUDA DEL CLIENTE	*/
	$detalle_filas_deudas = '';
	$detalle_nro_mov_deudas = '';
	$detalle_monto_deudas = '';
	if (count($deuda) > 0 && validar_atributo($db, $_plansistema['plan'], module, file, 'deuda') ) {	
		foreach ($deuda as $key => $value) {
			$detalle_nro_mov_deudas .= "<span>" . "Nro:  " . $value['nro_factura'] . "</span>"  . ((count($deuda) > 0) ? '<br>' : '' );
			$detalle_monto_deudas .= "<span>" . $valor_moneda . ' '. $value['total_deuda'] .  "</span>" . ((count($deuda) > 0) ? '<br>' : '' );
		}
		$detalle_filas_deudas .= '<tr><th class="all" align="center" colspan="7">DEUDA(S) PENDIENTE(S)' . $valor_moneda .'</th>';							
		$detalle_filas_deudas .= '</tr>';
		$detalle_filas_deudas .= '<tr>';
		$detalle_filas_deudas .= '<th  colspan="6" class="all" align="right">';
		$detalle_filas_deudas .= $detalle_nro_mov_deudas;
		$detalle_filas_deudas .= "</th>" ;
		$detalle_filas_deudas .= '<th class="all" align="right">';
		$detalle_filas_deudas .= $detalle_monto_deudas;
		$detalle_filas_deudas .= "</th>";
		$detalle_filas_deudas .= '</tr>';
	}
	
	//$valor_total = number_format($total, 2, '.', '');

	$valor_total = number_format($total, 2, '.', '');
	$total_con_descuento=$valor_total-$valor_descuento_global;
	$valor_total_con_descuento = number_format($total_con_descuento, 2, '.', '');
	$body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos las egresos cuenten con un detalle de venta.</td></tr>' : $body;
	
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
	<table cellpadding="1">
		<tr>
			<td width="22%" class="none"><b>LUGAR, FECHA Y HORA:</b></td>
			<td width="28%" class="none">$valor_lugar, $valor_fecha</td>
			<td width="22%" class="none"><b>OPERADOR:</b></td>
			<td width="28%" class="none">$valor_empleado</td>
		</tr>
		<tr>
			<td class="none"><b>SEÑOR(ES):</b></td>
			<td class="none">$valor_nombre_cliente</td>
			<td class="none"><b>ALMACÉN:</b></td>
			<td class="none">$valor_almacen</td>
		</tr>
		<tr>
			<td class="none"><b>NIT / CI:</b></td>
			<td class="none">$valor_nit_ci</td>
			<td class="none"><b>NÚNERO DE NOTA:</b></td>
			<td class="none">$valor_nro_registros</td>
		</tr>
	</table>
	<br><br>
	<table cellpadding="5">
		<tr>
			<th width="5%" class="all" align="center">#</th>
			<th width="12%" class="all" align="center">CÓDIGO</th>
			<th width="47%" class="all" align="center">NOMBRE</th>
			<th width="12%" class="all" align="center">CANTIDAD</th>
			<th width="12%" class="all" align="center">PRECIO $valor_moneda</th>

			<th width="12%" class="all" align="center">IMPORTE $valor_moneda</th>
		</tr>
		$body
		<tr>
			<th class="all" align="right" colspan="5">SUBTOTAL: </th>
			<th class="all" align="right">$monto_total</th>
		</tr>
		<tr>
			<th class="all" align="right" colspan="5">DESCUENTO $valor_descuento_porc: </th>
			<th class="all" align="right">$valor_descuento_global</th>
		</tr>
		<tr>
			<th class="all" align="right" colspan="5">IMPORTE TOTAL $valor_moneda</th>
			<th class="all" align="right">$monto_total_descuento</th>
		</tr>
		$detalle_filas_deudas
	</table>
	<td width="100%" align="left">$credito</td>
EOD;
	
	// Imprime la tabla
	$pdf->writeHTML($tabla, true, false, false, false, '');
	
	// Genera el nombre del archivo
	$nombre = 'nota_venta_' . $id_egreso . '_' . date('Y-m-d_H-i-s') . '.pdf';
}

// ------------------------------------------------------------

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
