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

//cajas sumatoria
$caja = $db->select('id_unidad')->from('inv_unidades')->where('unidad', 'CAJA')->fetch_first();
$id_caja = (isset($caja['id_unidad'])) ? $caja['id_unidad'] : 0;

if ($distribuidor == 0) {
    // Error 404
    require_once not_found();
    exit;
}
// Obtiene datos del distribuidor
$dist = $db->query('SELECT * from sys_empleados e where e.id_empleado = '.$distribuidor)->fetch();
$distri = $dist[0]['nombres'].' '.$dist[0]['paterno'].' '.$dist[0]['materno'];

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
$pdf = new MYPDF('P', 'pt', array(612,935), true, 'UTF-8', false);

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
$pdf->SetAutoPageBreak(true, 55);

$orden = '';
// Adiciona la pagina
$pdf->AddPage();

if (true) {
// Obtiene los detalles
    $detalles = $db->query('SELECT GROUP_CONCAT(c.cantidad, "-", c.unidad_id SEPARATOR "|" ) AS cantidades, GROUP_CONCAT(c.precio SEPARATOR "|" ) AS precios, SUM(b.monto_total) AS m_total,  d.*, c.*, d.unidad_id as unidad_producto, f.categoria
    FROM tmp_egresos b
    LEFT JOIN tmp_egresos_detalles c ON b.id_egreso = c.egreso_id AND c.tmp_egreso_id = b.id_tmp_egreso
    LEFT JOIN inv_productos d ON c.producto_id = d.id_producto
    LEFT JOIN inv_categorias f ON d.categoria_id = f.id_categoria
    WHERE b.distribuidor_id = '.$distribuidor.' AND b.distribuidor_fecha BETWEEN "'.$fecha_inicial.'" AND "'.$fecha_final.'"
          GROUP BY d.id_producto ORDER BY d.descripcion ASC, d.nombre_factura ASC')->fetch();
// echo $db->last_query();
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
    /*$valor_nombre_cliente = escape($orden['nombre_cliente']);
    $valor_nit_ci = escape($orden['nit_ci']);
    $valor_direccion = escape($orden['direccion']);
    $valor_telefono = escape($orden['telefono']);
    $valor_monto_total = escape($orden['monto_total']);*/
    //$valor_empleado = escape($empleados['nombres'] . ' ' . $empleados['paterno'] . ' ' . $empleados['materno']);
    /*$valor_descuento = escape($orden['descuento']);
    $valor_observacion = escape($orden['observacion']);*/

    $valor_moneda = $moneda;
    $total = 0;

// Establece la fuente del contenido
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', 8);

// Estructura la tabla
    $body = '';
    $total = 0;
    foreach ($detalles as $nro => $detalle) {
        
        //$total = $total + $detalle['prec'];
        $importe = 0;
        $sugerido = escape($detalle['precio_sugerido']);
        //var_dump($detalle);exit();
        // $cantidad = escape($detalle['cantidad']);

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
                    if($detalle['id_producto'])
                        $importe = ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[$nro2];
                $importe_t = $importe_t + $importe ;
                $total = $total + $importe;
            }
        }else{
            $parte = explode('-', $unid[0]);
            $unidad = $parte[1];
            $cantid = $parte[0];
            $cantidades = $cantidades + $cantid;
            //$importe = ($cantid/cantidad_unidad($db,$detalle['id_producto'],$unidad))* $precios[0];
            //Validacion
            if(cantidad_unidad($db,$detalle['id_producto'],$unidad))
                if($detalle['id_producto'])
                    $importe = ($cantid / cantidad_unidad($db,$detalle['id_producto'],$unidad)) * $precios[0];
            $importe_t = $importe;
            $total = $total + $importe;
        } 
        // buscar id caja;
        $mayores = cantidad_unidad($db,$detalle['id_producto'],$id_caja);

        if(isset($mayores) && $mayores <= $cantidades){
            $unidad_mayor = $id_caja;
            $mayor = $mayores;
        }else{
            $unidad_mayor = $detalle['unidad_producto'];
            $mayor = 1;
        }

        $unidades_t = (int)($cantidades/$mayor);
        if($cantidades % $mayor == 0){
            $otra_unidad = '';
            $otra_cantid = '';
        }else{
            $otra_unidad = '<br>'.nombre_unidad($db,$detalle['unidad_producto']);
            $otra_cantid = '<br>'.($cantidades-($unidades_t*$mayor));
        }
       
//        $unidades4 = substr($unidades4,0,-4);
//         $total = $total + $detalle['m_total'];
        $body .= '<tr height="2%" >';
        $body .= '<td class="left-right bot" align="right">' . $unidades_t .''.$otra_cantid. '</td>';
        $body .= '<td class="left-right bot">' . nombre_unidad($db,$unidad_mayor).'('.$mayor.' U.)'.''.$otra_unidad. '</td>';
        $body .= '<td class="left-right bot" align="left">' . escape($detalle['nombre_factura']) . '</td>';
        $body .= '<td class="left-right bot" align="right">' . $detalle['descripcion']. '</td>';
        $body .= '<td class="left-right bot" align="right">' . $detalle['categoria']. '</td>';
        $body .= '<td class="left-right bot" align="right">' . number_format($importe_t, 2, '.', '') . '</td>';
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


    $body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;

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
	</style>
	<!--<table cellpadding="1">
		<tr>
			<td width="50%" class="none" colspan="2" rowspan="2" align="center" ><h1>HOJA DE SALIDA</h1></td>
			<td width="15%" class="none"><b>FECHA ENTREGA:</b></td>
			<td width="35%" class="none">____ / __ / __</td>
		</tr>
		<tr>
		<td width="15%" class="none"><b>FECHA:</b></td>
			<td width="35%" class="none">$valor_fecha</td>
		</tr>
		<tr>
			<td class="none"><b>VENDEDORES:</b></td>
			<td class="none">$valor_empleado2</td>
			<td class="none"><b>DISTRIBUIDOR:</b></td>
			<td class="none">$distri</td>
		</tr>
	</table>-->
    <table cellpadding="1">
      <tr>
        <td  colspan="4" align="center" bgcolor="#CCCCCC" ><h2>HOJA DE SALIDA</h2></td>
      </tr>
      <tr>
        <td align="left" width="20%"><b>VENDEDORES:</b></td>
        <td align="left" width="30%" >$valor_empleado2</td>
        <td align="left" width="20%"><b>DISTRIBUIDOR:</b></td>
        <td align="left" width="30%">$valor_empleado</td>
      </tr>
      
    </table>
	<br><br>
	<table cellpadding="3" class="bor">
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
     <table>
        <tr>
            <td align="right"><b>FECHA:</b></td>
            <td align="left">$valor_fecha</td>
            <td align="right"><b>FECHA ENTREGA:</b></td>
            <td align="left">____ / __ / __</td>
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
            <td width="50%" class="none" align="center" >Recibí conforme:<br>$valor_empleado</td>
            <td width="50%" class="none" align="center" >Entregué conforme:<br>Nombre:_________________________ </td>
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

            <td width="100%" class="none" align="center" colspan='2'>------------------------------------------------------</td>
        </tr>
        <tr>
            <td width="100%" class="none" align="center" colspan='2' >Responsable de carga:<br>Nombre:_________________________ </td>
        </tr>
    </table>
EOD;

// Imprime la tabla
    $pdf->writeHTML($tabla, true, false, false, false, '');

$style3 = array('width' => 1, 'cap' => 'round', 'join' => 'round', 'dash' => '2,10', 'color' => array(255, 0, 0));

}

// Genera el nombre del archivo
//$nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';
$nombre = 'orden_compra' . '_' . date('Y-m-d_H-i-s') . '.pdf';

// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

?>
