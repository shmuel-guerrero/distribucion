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


//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

try {

    //Se abre nueva transacción.
    $db->autocommit(false);
    $db->beginTransaction();


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
    WHERE a.distribuidor_id = '.$distribuidor.' AND a.estado=1 AND b.estadoe= 3 AND c.promocion_id != 1 
    AND (b.fecha_egreso <= w.fecha or b.fecha_egreso < CURDATE()) GROUP BY w.id_empleado ORDER BY w.paterno ASC, d.nombre_factura ASC')->fetch();


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
$pdf->SetAutoPageBreak(true, 15);

$orden = '';

// Adiciona la pagina
$pdf->AddPage('P', 'LETTER');

if (true) {

        
    require_once("service_liquidacion.php");

    

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
    $total_entregados = 0;
    
    //PRODUCTOS ENTREGADOS
    foreach ($entregados as $nro => $detalle) {        

            if ($detalle['cantidad'] > 0) {  
                $body .= '<tr height="2%" >';
                $body .= '<td class="left-right bot" align="right">' . number_format($detalle['cantidad'], 0, '.', '') . '</td>';
                $body .= '<td class="left-right bot">' . $detalle['unidad'] . '</td>';
                $body .= '<td class="left-right bot" align="left">' . $detalle['codigo'] . ' - ' . $detalle['nombre_factura'] . '</td>';
                $body .= '<td class="left-right bot" align="right">' . $detalle['descripcion']. '</td>';
                $body .= '<td class="left-right bot" align="right">' . $detalle['categoria']. '</td>';
                $body .= '<td class="left-right bot" align="right">' . number_format($detalle['precio'], 2, '.', '') . '</td>';
                $body .= '</tr>';

                $total_entregados = $total_entregados + $detalle['precio'];
            }
    }



    /** COBROS DEUDAS ANTERIORES */
    $valor_total_entrega = number_format($total_entregados, 2, '.', '');

    // Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $valor_total_entrega);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = strtoupper($conversor->to_word($monto_numeral));

    //$body = ($body == '') ? '<tr><td colspan="7" align="center" class="all">No existen cobros realizados en la jornada.</td></tr>' : $body;


    // Obtiene el total de descuentos vendedores
    $valor_total3 = number_format($importeD, 2, '.', '');
    //VALOR FINAL
    $valor_final = number_format((($valor_total + $valor_total2) - $valor_total12), 2, '.', '');
    
    $valor_total_T = number_format($total7 + $valor_total12, 2, '.', '');
    
    
    // MONTO LIQUIDACION
    $valor_total_liquidacion = number_format(( $valor_total_entrega) , 2, '.', '');


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
</table>
<br><br>


{$datos_entregados}
{$datos_devueltos}
{$datos_ventas_directas}
{$datos_resumen}
{$datos_cobros}
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


//se cierra transaccion
$db->commit();

} catch (Exception $e) {
    $status = false;
    $error = $e->getMessage();

    // Instancia la variable de notificacion
    $_SESSION[temporary] = array(
        'alert' => 'danger',
        'title' => 'Problemas en el proceso de interacción con la base de datos.',
        'message' => (environment == 'development' || ($_user['id_user'] == 1 && $_user['rol'] == 'Superusuario' )) ? $error: 'Error en el proceso; comunicarse con soporte tecnico'
    );

            //Se devuelve el error en mensaje json
    //echo json_encode(array("estado" => 'n', 'msg'=>$error));

    // Error 404
    return redirect(back());
    exit;
    //se cierra transaccion
    $db->rollback();
}

?>
