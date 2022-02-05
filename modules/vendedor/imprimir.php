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

// Importa la libreria para el generado del pdf
require_once libraries . '/tcpdf/tcpdf.php';

// Importa la libreria para convertir el numero a letra
require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';



//Habilita las funciones internas de notificación
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT );

try {

    //Se abre nueva transacción.
    $db->autocommit(false);
    $db->beginTransaction();




//BUSCAMOS LOS CLIENTES DEL VENDEDOR
$orden = $db->select('GROUP_CONCAT(e.id_egreso SEPARATOR "|") AS ides1')->from('sys_empleados z')
            ->join('inv_egresos e','z.id_empleado = e.empleado_id')
            ->where('e.fecha_egreso >= ', $fecha_inicial)
            ->where('e.fecha_egreso <= ', $fecha_final)
            ->where('e.estadoe',2)->where('e.empleado_id',$id_orden)
            ->group_by('z.id_empleado')->order_by('z.id_empleado')->fetch_first();


// var_dump($orden);
$id_orden = $orden['ides1'];

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';


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
$pdf->SetMargins(30, 20, -1,false);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);
$pdf->SetAutoPageBreak(true, 50);


$id_ordenes = explode('|', $id_orden);

$nro_nota = 0;
// Adiciona la pagina
$pdf->AddPage();

$aux1 = 0;
$aux2 = 0;
// var_dump($id_ordenes);
foreach ($id_ordenes as $id_orden) {
    $aux1 = $aux1 + 1;
    //RUTA
    $rut = $db->select('*')->from('gps_rutas')->where('empleado_id',$id_emp)->order_by('id_ruta desc')->fetch_first();
    $ruta = $rut['nombre'];

// Obtiene el orden de compra
    $orden = $db->select('c.*, c.descripcion as referencia, n.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, e.cargo ')->from('inv_egresos n')->join('inv_almacenes a', 'n.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'n.empleado_id = e.id_empleado', 'left')->join('inv_clientes c','n.cliente_id = c.id_cliente')->where('n.id_egreso', $id_orden)->where('n.tipo', 'Venta')->where('n.provisionado', 'S')->fetch_first();

// Obtiene los detalles
    $detalles = $db->query("select d.*, p.codigo, p.nombre, p.nombre_factura, f.nombre as nombre_promo from inv_egresos_detalles d 
    left join inv_productos p ON d.producto_id = p.id_producto 
    left join (SELECT c.id_promocion, e.nombre FROM inv_promociones c left join inv_productos e on c.id_promocion = e.id_producto ) AS f 
    ON d.promocion_id = f.id_promocion 
    where d.egreso_id = '$id_orden' and promocion_id != 1 GROUP by d.id_detalle order by id_detalle asc")->fetch();

    $aux2 = $aux2 + count($detalles);

    if ($aux1 == 2) {
        if ($aux2 > 50) {
            $pdf->AddPage();
            $aux1 = 1;
            $aux2 = count($detalles);
        }
    }
    if ($aux1 == 3) {
        if ($aux2 > 37) {
            $pdf->AddPage();
            $aux1 = 1;
            $aux2 = count($detalles);
        }
    }
    if ($aux1 == 4) {
        if ($aux2 > 28) {
            $pdf->AddPage();
            $aux1 = 1;
            $aux2 = count($detalles);
        }
    }
    if ($aux1 == 5) {
        if ($aux2 > 19) {
            $pdf->AddPage();
            $aux1 = 1;
            $aux2 = count($detalles);
        }
    }
    if ($aux1 == 6) {
        if ($aux2 > 11) {
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
    $h = substr($orden['fecha_egreso'],0,4);
    $m = substr($orden['fecha_egreso'],5,2);
    $d = substr($orden['fecha_egreso'],8,2);
    $nro_nota = $nro_nota + 1;
// Salto de linea

// var_dump($id_ordenes);
// Establece la fuente del contenido
    $pdf->SetFont(PDF_FONT_NAME_DATA, '', 9);

// Define las variables
    $valor_fecha = escape(date_decode($orden['fecha_egreso'], $_institution['formato']) . ' ' . $orden['hora_egreso']);
    $valor_nombre_cliente = ($orden['nombre_factura'] != '' && $orden['nombre_factura'] != null) ? escape($orden['nombre_factura']) : escape($orden['nombre_cliente']);
    $valor_nit_ci = escape($orden['nit_ci']);
    $valor_direccion = escape($orden['direccion']);
    $valor_descripcion = escape($orden['referencia']);
    $valor_telefono = escape($orden['telefono']);
    $valor_empleado = escape($orden['nombres'] . ' ' . $orden['paterno'] . ' ' . $orden['materno']);
    $valor_empresa = ($orden['cargo']==1) ? $_institution['empresa1'] : $_institution['empresa2'];
    $valor_descuento = escape($orden['descuento']);
    $valor_observacion = escape($orden['observacion']);
    $valor_id_cliente = escape($orden['id_cliente']);
    $detalle_venta = escape($orden['descripcion_venta']);
    $valor_moneda = $moneda;
    $total = 0;
    $fecha_actual=date('Y-m-d');

    $body1 = '<tr height="2%"><td width="20%" align="center"><h3> '.$valor_empresa.'</h3></td><td width="25%" align="center"><h3> NOTA DE VENTA #' . $nro_nota . '</h3></td><td width="45%"><b>VENDEDOR(A): </b>'.$valor_empleado.'</td><td width="10%" align="right"><h3>'.date('d/m/Y').'</h3></td></tr>';
// var_dump($id_ordenes);
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
            $pr = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad  AND a.visible = "s" ')->where(array('a.producto_id'=>$detalle['producto_id'],'a.unidad_id'=>$detalle['unidad_id']))->fetch_first();
            $unidad = $pr['unidad'];
            $cantidad = $cantidad/$pr['cantidad_unidad'];
        }
        $uni_detalle = cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_id']);

        $precio_sugerido = $detalle['precio_sugerido'];
        $importe = $cantidad * $precio;
        $total = $total + $importe;
        $body .= '<tr height="2%">';
        $body .= '<td class="left-right bot" align="right">' . $cantidad . '</td>';
        $body .= '<td class="left-right bot" align="right">' . ' ' . $unidad . '(' . $uni_detalle . ' U.)' . '</td>';
        $body .= '<td class="left-right bot">' . escape($detalle['nombre_factura']) . '</td>';
        $body .= '<td class="left-right bot">' . escape($detalle['nombre_promo']) . '</td>';
        $body .= '<td class="left-right bot" align="right">' . number_format(((($precio/$uni_detalle)>0) ? round($precio/$uni_detalle, 2) : 0), 2, '.', '') . '</td>';
        $body .= '<td class="left-right bot" align="right">' . number_format($importe, 2, '.', '') . '</td>';
        $body .= '</tr>';
    }
// var_dump($id_ordenes);
// Obtiene el valor total
    $valor_total = number_format($total, 2, '.', '');

// Obtiene los datos del monto total
    $conversor = new NumberToLetterConverter();
    $monto_textual = explode('.', $valor_total);
    $monto_numeral = $monto_textual[0];
    $monto_decimal = $monto_textual[1];
    $monto_literal = strtoupper($conversor->to_word($monto_numeral));

    $body = ($body == '') ? '<tr><td colspan="6" align="center" class="all">Este egreso no tiene detalle, es muy importante que todos los egresos cuenten con un detalle de venta.</td></tr>' : $body;

    $titulo_comprobante = (configurar_atributo($db, 'pdf', module, file, 'titulo')) ? configurar_atributo($db, 'pdf', module, file, 'titulo') : ' NOTA DE VENTA ';


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
      <table cellpadding="1">
          <tr >
            <td  align="center" bgcolor="#CCCCCC"><h3> $valor_empresa</h3></td>
            <td align="center" bgcolor="#CCCCCC"><h3 align="left"> $titulo_comprobante   $nro_nota </h3>  </td>
            <td align="center" bgcolor="#CCCCCC"><strong>C&Oacute;DIGO</strong>: $valor_id_cliente</td>
          </tr>
          <tr>
            <td align="right"><b>NOMBRE CLIENTE:</b></td>
            <td colspan="2" align="left">$valor_nombre_cliente</td>
          </tr>
          <tr>
            <td align="right" ><b>DIRECCIÓN:</b></td>
            <td colspan="2" align="left">$valor_direccion</td>
          </tr>
          <tr>
            <td align="right" ><b>TEL&Eacute;FONO:</b></td>
            <td colspan="2" align="left">$valor_telefono</td>
          </tr>
          <tr>
            <td align="right" ><b>NIT/CI:</b></td>
            <td colspan="2" align="left">$valor_nit_ci</td>
          </tr>
          <tr>
            <td align="right" ><b>DESCRIPCI&Oacute;N:</b></td>
            <td colspan="2" align="left" > $valor_descripcion</td>
          </tr>
        </table>
    <br><br>
    <hr>
    <table cellpadding="3">
        <tr>
            <th width="8%" class="all" align="right">CANT.</th>
            <th width="18%" class="all" align="right">UNIDAD</th>
            <th width="35%" class="all" align="left">DETALLE</th>
            <th width="16%" class="all" align="left">PROMO</th>
            <th width="10%" class="all" align="right">PRECIO</th>
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
            <td width="100%"  align="right">$monto_literal $monto_decimal /100</td>
        </tr>
        <tr>
            <td width="100%" align="left">$detalle_venta</td>
        </tr>
        <tr>
            <td width="100%" align="left">Empleado: $valor_empleado</td>
        </tr>
    </table>
    <p><b>FECHA:<b>$fecha_actual</p>
    <HR>
EOD;
// Imprime la tabla
    $pdf->writeHTML($tabla, true, false, false, false, '');
}

// Genera el nombre del archivo
$nombre = 'orden_compra' . $id_orden . '_' . date('Y-m-d_H-i-s') . '.pdf';
// ob_end_clean();
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


    //se cierra transaccion
    $db->rollback();
    
    // Error 404
    return redirect(back());
    exit;
}


?>
