<?php

function printer_image($file = '')
{
    $interlineado = ln();
    return "^FO200,80^GFA,1650,1650,22,,:F,FC,FF8,IF,IFC,IFE,IFE07FFCI07FFC007LFCJ0MF,IFE07FFCI07FFC01NF8007MFE,IFE07FFCI07FFC07NFC00OF,IFE07FFCI07FFC0OFE01OF8,IFE07FFCI07FFC0OFE03OFC,IFE07FFCI07FFC1PF03OFC,:IFE07FFCI07FFC1PF07OFC,::::IFEN07FFCY03FFC,::::IFE07OFC1IF0JFE007FFC,IFE07OFC1IF0KF807FFC,IFE07OFC1IF0KFC07FFC,IFE07OFC1IF0KFE07FFC,IFE07OFC1IF0LF07FFC,::::::IFE07OFC1IF0LF07FFCI03FFC,IFE07FFCI07FFC1IFI01IF07FFCI03FFC,IFE07FFCI07FFC1IFJ0IF07FFCI03FFC,::IFE07FFCI07FFC1IFJ0IF07OFC,IFE07FFCI07FFC1PF07OFC,::::IFE07FFCI07FFC1PF03OFC,:IFE07FFCI07FFC0OFE03OFC,IFE07FFCI07FFC0OFE01OF8,IFE07FFCI07FFC07NFC00OF,IFE07FFCI07FFC01NF8007MFE,IFE07FF8I07FFC007LFCI01MF,IFE,::::gXFE,gYFC,hF,hFE,hGF8,hHF,hHFC,hIF8,:!:7!,::^FS";
}

function printer_explode($string, $limit, $align = STR_PAD_BOTH)
{
    $string = explode(' ', $string);
    $line = '';
    $lines = array();
    $line_size = 0;
    foreach ($string as $key => $element) {

        $element_size = strlen($element) + 1;
        if ($line_size + $element_size <= $limit) {
            $line_size = $line_size + $element_size;
            $line = $line . $element . ' ';
        } else {
            array_push($lines, str_pad(trim($line), $limit, ' ', $align));
            $line_size = $element_size;
            $line = $element . ' ';
        }
    }
    array_push($lines, str_pad(trim($line), $limit, ' ', $align));
    return $lines;
}

/**
 * Alinea el texto
 */
function printer_justify($left = '', $right = '', $margin = 0)
{
    global $full_width, $altoLetra, $anchoLetra;
    $length = $full_width - strlen($left) - strlen($right) - ($margin * 2);

    if ($length > 0) {
        $space = str_pad('', $length, ' ', STR_PAD_RIGHT);
        $text = $left . $space . $right;
    } else {
        $text = substr($left . ' ' . $right, 0, $full_width - ($margin * 2));
    }
    $espacio = ln();

    return "^FT0,$espacio^AcN,$altoLetra,$anchoLetra^FH\^FD$text^FS";
}

/**
 * Imprime un texto
 */
function printer_draw_text_custom($string, $align = STR_PAD_BOTH)
{
    global $full_width;
    global $altoLetra;
    global $anchoLetra;
    $interlineado = 20;
    $string = printer_explode($string, $full_width, $align);
    $linea = "";
    foreach ($string as $key => $element) {
        $interlineado = ln();
        $linea .= "^FT0,$interlineado^AcN,$altoLetra,$anchoLetra^FH\^FD$element^FS";
    }
    return $linea;
}

/**
 * Imprime una linea
 */
function printer_draw_line_custom()
{
    global $altoLetra, $widthLabel;
    $line = ln() - intval(($altoLetra / 2));
    return "^FO0,$line^GB$widthLabel,0,2^FS";
}

/**
 * Ordena las columnas
 */

function printer_center($quantity = '', $detail = '', $price = '', $amount = '')
{
    $val1 = explode(" ", utf8_decode("Á É Í Ó Ú á é í ó ú ñ Ñ"));
    $val2 = array('\B5', '\90', '\D6', '\E3', '\E9', '\A0', '\82', '\A1', '\A2', '\A3', '\A4', '\A5');
    global $full_width;
    $column   = 10;
    $detail   = (($full_width - ($column * 3) - 2) > strlen($detail)) ? $detail : substr($detail, 0, ($full_width - ($column * 3) - 2));
    $quantity = ' ' . $quantity . ' ';
    $detail   = ' ' . str_replace($val1, $val2, utf8_decode($detail)) . ' ';
    $price    = ' ' . $price . ' ';
    $amount   = ' ' . $amount . ' ';
    $quantity = str_pad($quantity, $column - 3, ' ', STR_PAD_LEFT);
    $detail   = str_pad($detail, $full_width - ($column * 3) + 3, ' ', STR_PAD_RIGHT);
    $price    = str_pad($price, $column, ' ', STR_PAD_LEFT);
    $amount   = str_pad($amount, $column, ' ', STR_PAD_LEFT);
    return $quantity . $detail . $price . $amount;
}



function printer_center2($aux1='',$aux2=''){
    $val1 = explode(" ", utf8_decode("Á É Í Ó Ú á é í ó ú ñ Ñ"));
    $val2 = array('\B5', '\90', '\D6', '\E3', '\E9', '\A0', '\82', '\A1', '\A2', '\A3', '\A4', '\A5');
    $column     = 2;
    $aux1   = ' ' . $aux1 . ' ';
    $aux2   = ' ' . str_replace($val1,$val2,utf8_decode($aux2)) . ' ';
    $aux1   = str_pad($aux1, $column+36, ' ', STR_PAD_RIGHT);
    $aux2   = str_pad($aux2, $column, ' ', STR_PAD_RIGHT);
    return $aux1.$aux2;
}



function printer_center3($aux1='',$aux2='',$aux3=''){
    $val1 = explode(" ", utf8_decode("Á É Í Ó Ú á é í ó ú ñ Ñ"));
    $val2 = array('\B5', '\90', '\D6', '\E3', '\E9', '\A0', '\82', '\A1', '\A2', '\A3', '\A4', '\A5');
    $column     = 16;
    $aux1   = ' ' . $aux1 . ' ';
    $aux2   = ' ' . $aux2 . ' ';
    $aux3   = ' ' . $aux3 . ' ';
    $aux1   = str_pad($aux1, $column, ' ', STR_PAD_RIGHT);
    $aux2   = str_pad($aux2, $column, ' ', STR_PAD_RIGHT);
    $aux3   = str_pad($aux3, $column, ' ', STR_PAD_RIGHT);
    return $aux1.$aux2.$aux3;
}


/**
 * Retorna la posición de la siguiente linea
 */
function ln($size = 1)
{
    global $jump, $altoLetra;
    $jump = $jump + ($altoLetra * $size);
    return $jump;
}

function contador_signos($string)
{
    $count = 0;
    $signos = array(utf8_decode('Á'), utf8_decode('É'), utf8_decode('Í'), utf8_decode('Ó'), utf8_decode('Ú'), utf8_decode('á'), utf8_decode('é'), utf8_decode('í'), utf8_decode('ó'), utf8_decode('ú'), utf8_decode('ñ'), utf8_decode('Ñ'), utf8_decode('º'), utf8_decode('¡'));

    for ($i = 0; $i < strlen($string); $i++) {
        $car = substr($string, $i, 1);
        if (in_array($car, $signos)) {
            $count++;
        }
    }
    return $count;
}

/**
 * Definiendo el ancho de la etiqueta (en milimetros)
 */
$anchoEtiqueta = 70;
/**
 * convirtiendo el ancho de la etiqueta en su equivalente en puntos
 */
$widthLabel    = intval(8 * $anchoEtiqueta) - 2;
/**
 * Definiendo el ancho de impresión
 */
$printWidth    = $widthLabel + 2;
/**
 * Definiendo el tamaño de la fuente
 */
$anchoLetra    = 10;
$altoLetra     = 25;
/**
 * Definimos el número de caracteres
 */
$full_width    = round($widthLabel / ($anchoLetra + 2));
/**
 * Definimos el número de columna para la tabla detalle de venta
 */
$column = 8;
/**
 * Definimos el punto de inicio de la primera linea
 */
$jump = 0 - $altoLetra;


function generar_zpl($datos)
{

    global $altoLetra,$jump,$anchoLetra,$widthLabel,$printWidth,$full_width,$column;
	
	/**
	* Definimos una lista con los carácteres especiales
	*/
	$val1 = explode(" ",utf8_decode("Á É Í Ó Ú á é í ó ú ñ Ñ º ¡"));
	$val2 = array('\B5','\90','\D6','\E3','\E9','\A0','\82','\A1','\A2','\A3','\A4','\A5','\A7','\AD');

	$empresa_nombre         = utf8_decode($datos['empresa_nombre']);
	$empresa_sucursal       = utf8_decode($datos['empresa_sucursal']);
	$empresa_direccion      = utf8_decode($datos['empresa_direccion']);
	$empresa_telefono       = utf8_decode($datos['empresa_telefono']);
	$empresa_ciudad         = utf8_decode($datos['empresa_ciudad']);
	$empresa_actividad      = utf8_decode($datos['empresa_actividad']);
	$empresa_nit            = utf8_decode($datos['empresa_nit']);
	$factura_titulo         = utf8_decode($datos['factura_titulo']);
	$factura_numero         = utf8_decode($datos['factura_numero']);
	$factura_autorizacion   = utf8_decode($datos['factura_autorizacion']);
	$factura_fecha          = utf8_decode($datos['factura_fecha']);
	$factura_hora           = utf8_decode($datos['factura_hora']);
	$factura_codigo         = utf8_decode($datos['factura_codigo']);
	$factura_limite         = utf8_decode($datos['factura_limite']);
	$factura_autenticidad   = utf8_decode($datos['factura_autenticidad']);
	$factura_leyenda        = utf8_decode($datos['factura_leyenda']);
	$cliente_nit            = utf8_decode($datos['cliente_nit']);
	$cliente_nombre         = utf8_decode($datos['cliente_nombre']);
	$venta_titulos          = $datos['venta_titulos'];
	$venta_cantidades       = $datos['venta_cantidades'];
	$venta_detalles         = $datos['venta_detalles'];
	$venta_precios          = $datos['venta_precios'];
	$venta_subtotales       = $datos['venta_subtotales'];
	$venta_total_titulo     = utf8_decode($datos['venta_total_titulo']);
	$venta_total_numeral    = $datos['venta_total_numeral'];
	$venta_total_literal    = utf8_decode($datos['venta_total_literal']);
	$factura_qr             = $datos['factura_qr'];
	$factura_vendedor       = $datos['factura_vendedor'];
	$factura_agradecimiento = $datos['factura_agradecimiento'];

	ln(4);
	$imagen = printer_image();
	ln();
	ln();
	ln();
	/**
	* Imprime el nombre de la empresa
	*/
	$empresa_nombre = str_replace($val1, $val2,printer_draw_text_custom($empresa_nombre)); 
	/**
	* Imprime la sucursal de la empresa
	*/
	$empresa_sucursal  = str_replace($val1, $val2,printer_draw_text_custom($empresa_sucursal));
	/**
	* Imprime la direccion de la empresa
	*/
	$empresa_direccion = str_replace($val1, $val2,printer_draw_text_custom($empresa_direccion));
	/**
	* Imprime el telefono de la empresa
	*/
	$empresa_telefono  = str_replace($val1, $val2,printer_draw_text_custom($empresa_telefono));
	/**
	* Imprime la ciudad de funcionamiento de la empresa
	*/
	$empresa_ciudad    = str_replace($val1, $val2, printer_draw_text_custom($empresa_ciudad));

	ln();
	/**
	* Imprime el titulo de la factura
	*/
	$factura_titulo       = str_replace($val1, $val2, printer_draw_text_custom($factura_titulo));

	ln();
	/**
	* Dibuja una linea
	*/
	$linea1               = printer_draw_line_custom();
	/**
	* Imprime el nit de la empresa
	*/
	$empresa_nit          = str_replace($val1, $val2, printer_draw_text_custom($empresa_nit));
	/**
	* Imprime el numero de la factura
	*/
	$factura_numero       = str_replace($val1, $val2, printer_draw_text_custom($factura_numero));
	/**
	* Imprime el numero de autorizacion de la factura
	*/
	$factura_autorizacion = str_replace($val1, $val2, printer_draw_text_custom($factura_autorizacion));
	/**
	* Dibuja una linea
	*/
	$linea2               = printer_draw_line_custom();
	/**
	* Imprime la actividad de la empresa
	*/
	$empresa_actividad    = str_replace($val1, $val2, printer_draw_text_custom($empresa_actividad));
	/**
	* Imprime la fecha y hora de emision de la factura
	*/
	$fecha_hora           = printer_justify($factura_fecha,$factura_hora);
	/**
	* Imprime el nit del cliente
	*/
	$cliente_nit          = str_replace($val1, $val2, printer_draw_text_custom($cliente_nit,STR_PAD_RIGHT));
	/**
	* Imprime el nombre del cliente
	*/
	$cliente_nombre       = str_replace($val1, $val2,printer_draw_text_custom($cliente_nombre,STR_PAD_RIGHT));//printer_draw_text_custom(str_replace($val1, $val2, $cliente_nombre),STR_PAD_RIGHT);

	$linea_1top = $jump+intval(($altoLetra/2));
	/**
	* Dibuja una linea
	*/
	$linea_cab  = printer_draw_line_custom();
	$linea_1 = ln();
	/**
	* Imprime los titulos de la venta
	*/
	$res = printer_center(isset($venta_titulos[0]) ? $venta_titulos[0] : '',isset($venta_titulos[1]) ? $venta_titulos[1] : '',isset($venta_titulos[2]) ? $venta_titulos[2] : '',isset($venta_titulos[3]) ? $venta_titulos[3] : '');
	/**
	* Definime la posicion de los titulos de la venta
	*/
	$cabecera   = "^FT0,$linea_1^AcN,$altoLetra,$anchoLetra^FH\^FD$res^FS";
	$spaceH     = $linea_1;
	/**
	* Dibuja una linea
	*/
	$linea_cab2 = printer_draw_line_custom();

	$padding = 2;
	$tabla = "";

	$spaceH = $spaceH + ($altoLetra*1+$padding);
	/**
	* Imprime las cantidades, los detalles, los precios y los subtotales de la venta
	*/
	foreach ($venta_cantidades as $key => $cantidad) {
		$lineat = ln();
		$detalle  = (isset($venta_detalles[$key])) ? $venta_detalles[$key] : '';
		$precio   = (isset($venta_precios[$key])) ? $venta_precios[$key] : '';
		$subtotal = (isset($venta_subtotales[$key])) ? $venta_subtotales[$key] : '';
		$res = printer_center($cantidad,$detalle,$precio,$subtotal);
		$tabla.= "^FT0,$lineat^AcN,$altoLetra,$anchoLetra^FH\^FD$res^FS";

	}
	/**
	* Dibuja una linea
	*/
	$linea_cab3          = printer_draw_line_custom();
	/**
	* Imprime el total de la venta
	*/
	$pie                 = printer_justify(' ' . $venta_total_titulo,$venta_total_numeral . ' ');
	$temC                = $jump;
	/**
	* Dibuja una linea
	*/
	$linea_cab4          = printer_draw_line_custom();
	$temLR               = $jump;
	/**
	* Imprime el monto total en literal
	*/
	$venta_total_literal = str_replace($val1, $val2, printer_draw_text_custom($venta_total_literal,STR_PAD_RIGHT));
	/**
	* Dibuja una linea
	*/
	$linea_cab5          = printer_draw_line_custom();
	/**
	* Imprime el codigo de control
	*/
	if($factura_autenticidad !=  ''){
	    $factura_codigo      = str_replace($val1, $val2, printer_draw_text_custom($factura_codigo,STR_PAD_RIGHT));
	}else{
	    $factura_codigo = '';
	}
	/**
	* Imprime la fecha limite de emision
	*/
	if($factura_autenticidad !=  ''){
	    $factura_limite        = str_replace($val1, $val2, printer_draw_text_custom($factura_limite,STR_PAD_RIGHT));
	}else{
	    $factura_limite = '';
	}

	$temC   = $temC-intval(($altoLetra/2));
	$lineat = $temLR+intval(($altoLetra/2));
	/**
	* Definimos la posicion en que se imprimira el monto total de la venta
	*/
	$pie = "^FT0,$lineat^AcN,$altoLetra,$anchoLetra^FH\^FD$pie^FS";
	/**
	* Imprime el nombre de la empresa
	*/
	$alv = $lineat-$linea_1+($altoLetra/2);
	$alvC= $temC-$linea_1+($altoLetra/2);

	$verticalL = "^FO0,$linea_1top^GB1,$alv,3^FS";
	$verticalR = "^FO$widthLabel,$linea_1top^GB1,$alv,3^FS";

	/**
	* posicion en el eje x (en puntos) para la primera linea vertical
	*/
	//$p1 = $column*($anchoLetra+2);
	/**
	* posicion en el eje x (en puntos) para la segunda linea vertical
	*/
	//$p2 = (($column * 3)+4)*($anchoLetra+2);
	/**
	* posicion en el eje x (en puntos) para la tercera linea vertical
	*/
	//$p3 = $p2+9*($anchoLetra+2);

	$p3 = $printWidth-120;
	$p2 = $printWidth-240;
	$p1 = 80;

	/**
	*  Dibuja las lineas verticales
	*/
	$verticalC1 = "^FO$p1,$linea_1top^GB1,$alvC,3^FS";
	$verticalC2 = "^FO$p2,$linea_1top^GB1,$alvC,3^FS";
	$verticalC3 = "^FO$p3,$linea_1top^GB1,$alvC,3^FS";
	/**
	* Definimos las dimensiones del codigo QR
	*/
	$dpi = 6;
	$lineaqr = $jump + 10;

	/**
	* Imprime el codigo QR
	*/
	if($factura_autenticidad !=  ''){
    	$cen = 12*(($full_width-17)/2);
    	$qr = "^FO$cen,$lineaqr
    	^BQN,2,$dpi
    	^FH\^FDLA,$factura_qr^FS";
    	ln(10);
	}else{
	    $factura_qr = '';
	}
	if($factura_autenticidad !=  ''){
	/**
	* Imprime el texto de autenticidad
	*/
	    $factura_autenticidad = str_replace($val1, $val2, printer_draw_text_custom($factura_autenticidad));
	}else{
	    $factura_autenticidad = '';
	}
	if($factura_autenticidad !=  ''){
    	/**
    	* Imprime la leyenda
    	*/
    	$factura_leyenda      = str_replace($val1, $val2, printer_draw_text_custom($factura_leyenda));
    	$linea3               = printer_draw_line_custom();
	}else{
	    $factura_leyenda = '';
        $linea3               = '';
	}
	
	$factura_vendedor       = str_replace($val1, $val2,printer_draw_text_custom($factura_vendedor));
	$factura_agradecimiento       = str_replace($val1, $val2,printer_draw_text_custom($factura_agradecimiento));
	ln();

	/**
	* Definimos la estructura que tendra la etiqueta en lenguaje zpl
	*/

	$zpl = "^XA
	^PW$printWidth
	^LL$jump
	^LH0,0
	^FS".$imagen.$empresa_nombre.$empresa_sucursal.$empresa_direccion.$empresa_telefono.$empresa_ciudad.$factura_titulo.$linea1.$empresa_nit.$factura_numero.$factura_autorizacion.$linea2.$empresa_actividad.$fecha_hora.$cliente_nit.$cliente_nombre.$cabecera.$tabla.$linea_cab.$linea_cab2.$linea_cab3.$pie.$linea_cab4.$verticalL.$verticalR.$verticalC1.$verticalC2.$verticalC3.$venta_total_literal.$linea_cab5.$factura_codigo.$factura_limite.$qr.$factura_autenticidad.$factura_leyenda.$linea3.$factura_vendedor.$factura_agradecimiento."^XZ";

	return $zpl;
}

// Define las cabeceras
header('Content-Type: application/json');

// Verifica la peticion post
if (is_post()) {
    // Verifica la existencia de datos
    if (isset($_POST['id_cliente'])) {
        // Importa la configuracion para el manejo de la base de datos
        require config . '/database.php';

        // Importa la libreria para el codigo de control
        require_once libraries . '/controlcode-class/ControlCode.php';

        // Importa la libreria para la conversion de numeros a letras
        require_once libraries . '/numbertoletter-class/NumberToLetterConverter.php';

        // Obtiene los datos de la venta
        $id_cliente = $_POST['id_cliente'];
        $fecha = date('Y-m-d');
        $venta = $db->select('*')->from('tmp_egresos a')->where('a.cliente_id', $id_cliente)->where(array('a.estado' => 3, 'a.distribuidor_estado' => 'ENTREGA'))->fetch_first();
        $cliente = $db->select('*')->from('inv_clientes')->where('id_cliente',$venta['cliente_id'])->fetch_first();
        if($cliente){
            $nombre_factura = $cliente['nombre_factura'];
        }else{
            $nombre_factura = $venta['nombre_cliente'];
        }
        $empleado = $db->select("CONCAT(paterno, ' ', nombres) as empleado")->from('sys_empleados')->where('id_empleado',$venta['empleado_id'])->fetch_first();
		$empleado = ($empleado['empleado']) ? $empleado['empleado'] : '';
        $productos = $db->select('b.nombre_factura, if(c.cantidad_unidad is null, a.cantidad, a.cantidad/c.cantidad_unidad) as cantidad, a.precio, if(c.cantidad_unidad is null, a.precio*a.cantidad, a.precio*(a.cantidad/c.cantidad_unidad)) as subtotal, d.unidad')->from('inv_egresos_detalles a')->join('inv_productos b', 'a.producto_id = b.id_producto')->join('inv_asignaciones c', 'a.producto_id = c.producto_id and a.unidad_id = c.unidad_id AND c.visible = "s"')->join('inv_unidades d', 'a.unidad_id = d.id_unidad')->where('a.egreso_id', $venta['id_egreso'])->fetch();
        // $precios = $db->select('if(b.cantidad_unidad is null, a.precio*a.cantidad, a.precio*(a.cantidad/b.cantidad_unidad)) as precio')->from('inv_egresos_detalles a')->join('inv_asignaciones b','a.producto_id = b.producto_id and a.unidad_id = b.unidad_id')->join('inv_unidades c','a.unidad_id = c.id_unidad')->where('a.egreso_id',$venta['id_egreso'])->fetch();
        $producto = array();
        $precios1 = array();
        $cantidades1 = array();
        $subtotales = array();
        foreach ($productos as $nro => $opcion) {
            array_push($producto, $opcion['nombre_factura']);
            array_push($precios1, number_format(($opcion['precio']), 2, ',', ''));
            array_push($subtotales, number_format(($opcion['subtotal']), 2, ',', ''));
            $cantidad_i = round($opcion['cantidad'], 2);
            $unidad_i = $opcion['unidad'][0];
            array_push($cantidades1, $cantidad_i.' '.$unidad_i);
        }

        // Obtiene los datos del monto total
        $conversor = new NumberToLetterConverter();
        $monto_textual = explode('.', $venta['monto_total']);
        $monto_numeral = $monto_textual[0];
        $monto_decimal = $monto_textual[1];
        $monto_literal = ucfirst(strtolower(trim($conversor->to_word($monto_numeral))));


        // $materialesm=$db->query("SELECT m.nombre,m.precio,
        //     IFNULL(
        //         (SELECT SUM(cantidad) FROM inv_control WHERE estado='pendiente' AND inv_control.id_materiales=m.id_materiales AND fecha_control='{$fecha}' AND cliente_id='{$id_cliente}'
        //     ),0)AS pendiente,
        //     IFNULL(
        //         (SELECT SUM(cantidad) FROM inv_control WHERE estado='vendido' AND inv_control.id_materiales=m.id_materiales AND fecha_control='{$fecha}' AND cliente_id='{$id_cliente}'
        //     ),0)AS vendido,
        //     IFNULL(
        //         (SELECT SUM(cantidad) FROM inv_control WHERE estado='entregado' AND inv_control.id_materiales=m.id_materiales AND fecha_control='{$fecha}' AND cliente_id='{$id_cliente}'
        //     ),0)AS entregado
        //     FROM inv_control AS c
        //     LEFT JOIN inv_materiales AS m ON m.id_materiales=c.id_materiales
        //     WHERE c.fecha_control='{$fecha}' AND c.cliente_id='{$id_cliente}' GROUP BY m.id_materiales")->fetch();
            
            $materialesm='';

        $materialm=array();
        $preciosm=array();
        $pendientem=array();
        $vendidom=array();
        $entregadom=array();
        $subtotalm=array();
        $totalm=0;
        foreach($materialesm as $nro=>$Dato):
            array_push($materialm,'Canastilla');
            array_push($preciosm,$Dato['cantidad_pres']);
            array_push($pendientem , $Dato['cantidad_pres']);
        endforeach;
        $totalm=number_format($totalm,2);
        $monto_textualm=explode('.',$totalm);
        $monto_numeralm=$monto_textualm[0];
        $monto_decimalm=$monto_textualm[1];
        $monto_literalm=ucfirst(strtolower(trim($conversor->to_word($monto_numeralm))));




        $pagos=$db->query("SELECT pd.nro_cuota,pd.monto,pd.tipo_pago
                        FROM inv_pagos_detalles AS pd
                        LEFT JOIN inv_pagos AS p ON p.id_pago=pd.pago_id
                        LEFT JOIN inv_egresos AS e ON e.id_egreso=p.movimiento_id
                        WHERE pd.fecha='{$fecha}' AND e.cliente_id='{$id_cliente}'")->fetch();
        $nro_cuotap=array();
        $montop=array();
        $tipo_pagop=array();
        $totalp=0;
        foreach($pagos as $nro=>$Dato):
            array_push($nro_cuotap,$Dato['nro_cuota']);
            array_push($montop,$Dato['monto']);
            array_push($tipo_pagop,$Dato['tipo_pago']);
            $totalp=$totalp+$Dato['monto'];
        endforeach;
        $totalp=number_format($totalp,2);
        $monto_textualp=explode('.',$totalp);
        $monto_numeralp=$monto_textualp[0];
        $monto_decimalp=$monto_textualp[1];
        $monto_literalp=ucfirst(strtolower(trim($conversor->to_word($monto_numeralp))));


        // Obtiene datos de la empresa $_institution = palabra reservada
        $_institution = $db->from('sys_instituciones')->fetch_first();

		if($_POST['factura'] == 'si'){
		    $titulo = ' F A C T U R A';
		    $nro_factura = 'Nº DE FACTURA: ' . $venta['nro_factura'];
			//almacen
			$id_almacen = $venta['almacen_id'];

			// Obtiene la fecha de hoy
			$hoy = date('Y-m-d');

			// Obtiene la dosificacion del periodo actual
			$dosificacion = $db->from('inv_dosificaciones')->where('fecha_registro <=', $hoy)->where('fecha_limite >=', $hoy)->where('activo', 'S')->fetch_first();
			$id_v = $venta['id_egreso'];
            $venta2 = $db->query("SELECT nro_autorizacion FROM inv_egresos WHERE id_egreso = '$id_v'")->fetch_first();
			if($dosificacion){
			    if($venta2['nro_autorizacion'] != ''){
			        $codigo_control = '';
    				$fecha_limite = '';
    				$factura_autenticidad = '';
    				$factura_leyenda = '';
    				$factura_qr = '';
			    }else{
			        // Obtiene los datos para el codigo de control
    				$nro_autorizacion = $dosificacion['nro_autorizacion'];
    				$nro_factura = intval($dosificacion['nro_facturas']) + 1;
    				$nit_cliente = $venta['almacen_id'];
    				$fecha = date('Ymd');
    				$total = round($venta['monto_total'], 0);
    				$llave_dosificacion = base64_decode($dosificacion['llave_dosificacion']);
    
    				// Genera el codigo de control
    				$codigo_control = new ControlCode();
    				$codigo_control = $codigo_control->generate($nro_autorizacion, $nro_factura, $nit_cliente, $fecha, $total, $llave_dosificacion);
    				$datos_venta = array(
    					'tipo' => 'Venta',
    					'provisionado' => 'N',
    					'descripcion' => 'Venta de productos con preventa',
    					'nro_factura' => $nro_factura,
    					'nro_autorizacion' => $nro_autorizacion,
    					'codigo_control' => $codigo_control,
    					'fecha_limite' => $dosificacion['fecha_limite'],
    					'dosificacion_id' => $dosificacion['id_dosificacion']
    				);
    				$db->where('id_egreso', $venta['id_egreso'])->update('inv_egresos', $datos_venta);
    				$db->where('id_dosificacion', $dosificacion['id_dosificacion'])->update('inv_dosificaciones', array('nro_facturas' => $nro_factura));
    
    				// Gereramos el codigo de seguridad QR
    				$factura_qr = $_institution['nit'] . '|' . $nro_factura . '|' . $nro_autorizacion . '|' . date_decode($venta['fecha_egreso'], 'd/m/Y') . '|' . $venta['monto_total'] . '|0.00|' . $codigo_control . '|' . $venta['nit_ci'] . '|0.00|0.00|0.00|0.00';
    				$factura_autenticidad = '"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS. EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO DE ACUERDO A LEY"';
    				$factura_leyenda = 'Ley Nº 453: "' . $dosificacion['leyenda'] . '".';
    				$fecha_limite = $dosificacion['fecha_limite'];
			    }
				
			}else{
			    $nro_factura = 'Nº DE VENTA: ' . $venta['nro_factura'];
				$codigo_control = '';
				$fecha_limite = '';
				$factura_autenticidad = '';
				$factura_leyenda = '';
				$factura_qr = '';
			}

		}else{
		    $titulo = 'N O T A  D E  R E M I S I O N';
		    $nro_factura = 'Nº DE VENTA: ' . $venta['nro_factura'];
			$codigo_control = '';
			$fecha_limite = '';
			$factura_autenticidad = '';
			$factura_leyenda = '';
			$factura_qr = '';
		}
		
        // Verifica la existencia del usuario
        if (true) {
            // Verifica si la dosificación existe
            if ($venta['id_egreso']) {
                // Arma los datos para la factura
                $datos = array(
                    'empresa_nombre' => $_institution['nombre'],
                    'empresa_sucursal' => $almacen['empresa_sucursal'],
                    'empresa_direccion' => $_institution['direccion'],
                    'empresa_telefono' => 'TELÉFONO: ' . $_institution['telefono'],
                    'empresa_ciudad' => 'La Paz - El Alto',
                    'empresa_actividad' => $_institution['razon_social'],
                    'empresa_nit' => 'NIT: ' . $_institution['nit'],
                    'factura_titulo' => $titulo,
                    'factura_numero' => $nro_factura,
                    'factura_autorizacion' => $nro_autorizacion,
                    'factura_fecha' => 'FECHA: ' . date_decode($venta['fecha_egreso'], 'd/m/Y'),
                    'factura_hora' => 'HORA: ' . substr($venta['hora_egreso'], 0, 5),
                    'factura_codigo' => 'CÓDIGO DE CONTROL: '.$codigo_control,
                    'factura_limite' => 'FECHA LÍMITE DE EMISIÓN: '.$fecha_limite,
                    'factura_autenticidad' => $factura_autenticidad,
                    'factura_leyenda' => $factura_leyenda,
                    'cliente_nit' => 'NIT/CI: ' . $venta['nit_ci'],
                    'cliente_nombre' => 'SEÑOR(ES): ' . $nombre_factura,
                    'venta_titulos' => array('CANT.', 'DETALLE', 'P. UNIT.', 'SUBTOTAL'),
                    'venta_cantidades' => $cantidades1,
                    'venta_detalles' => $producto,
                    'venta_precios' => $precios1,
                    'venta_subtotales' => $subtotales,
                    'venta_total_titulo' => 'TOTAL BOLIVIANOS',
                    'venta_total_numeral' => $venta['monto_total'],
                    'venta_total_literal' => 'SON: ' . mb_strtoupper($monto_literal . ' ' . $monto_decimal . '/100 ' . $moneda, 'UTF-8'),


                    'venta_titulosm' => array('DETALLE', 'CANT.'),
                    'venta_pendientem' => $pendientem,
                    'venta_vendidom' => $vendidom,
                    'venta_entregadom' => $entregadom,
                    'venta_detallesm' => $materialm,
                    'venta_preciosm' => $preciosm,
                    'venta_subtotalesm' => $subtotalm,
                    'venta_total_titulom' => 'TOTAL BOLIVIANOS',
                    'venta_total_numeralm' => $totalm,
                    'venta_total_literalm' => 'SON: '.mb_strtoupper($monto_literalm.' '.$monto_decimalm.'/100 '.$moneda,'UTF-8'),


                    'venta_titulosp' => array('CUOTA', 'MONTO', 'T. PAGO'),
                    'nro_cuotap' => $nro_cuotap,
                    'montop' => $montop,
                    'tipo_pagop' => $tipo_pagop,
                    'venta_total_titulop' => 'TOTAL BOLIVIANOS',
                    'venta_total_numeralp' => $totalp,
                    'venta_total_literalp' => 'SON: '.mb_strtoupper($monto_literalp.' '.$monto_decimalp.'/100 '.$moneda,'UTF-8'),


                    'factura_qr' => $factura_qr,
                    'factura_vendedor' => 'VENDEDOR: ' . mb_strtoupper($empleado, 'UTF-8'),
                    'factura_agradecimiento' => 'GRACIAS POR SU COMPRA'
                );
                //print_r($datos);

                // Genera el zpl
                $zpl = generar_zpl($datos);

                // Instancia el objeto
                $respuesta = array(
                    'estado' => 's',
                    'zpl' => ($zpl)
                );

                // Devuelve los resultados
                echo json_encode($respuesta);
            } else {
                // Devuelve los resultados
                echo json_encode(array('estado' => 'No se encuentra la venta'));
            }
        } else {
            // Devuelve los resultados
            echo json_encode(array('estado' => 'n'));
        }
    } else {
        // Devuelve los resultados
        echo json_encode(array('estado' => 'n'));
    }
} else {
    // Devuelve los resultados
    echo json_encode(array('estado' => 'n'));
}
