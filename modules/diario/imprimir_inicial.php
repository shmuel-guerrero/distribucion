<?php

require_once libraries . '/tcpdf-class/tcpdf.php';

function convercion($mon,$tip,$db){
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();
    if($tip>1){
        $tipob = $db->select('*')->from('con_tipo_moneda')->where('id_moneda',$tip)->fetch_first();
        $mon = $mon/$tipob['valor'];
    }

    $res = $mon  / $tipo['valor'];

    return number_format($res,2);
}
function sumar_vec($vec, $sum1){
    $vct = 0;
    foreach ($vec as $vc) {
        $vct = $vct + $vc[$sum1];
    }
    return $vct;
}
function fechaCastellano ($fecha) {
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return $nombredia." ".$numeroDia." de ".$nombreMes." de ".$anio;
}
$cuenta=$db->select('con_plan.*')->from('con_asiento')->join('con_plan','con_plan.n_plan=con_asiento.cuenta')->fetch();
$cuenta1=$db->select('*')->from('con_cuenta')->fetch();
$g=0;
$comp1=$db->select('*')->from('con_comprobante')->where('tipo',1)->order_by('fecha','desc')->fetch();
$comp2=$db->select('*')->from('con_comprobante')->where('tipo',2)->order_by('fecha','desc')->fetch();
if(sizeof($params) > 0){
    $a=$params[0];
    $b=$params[1];
}else{
    if(isset($comp1[0]['codigo'])){
        $a=$comp1[0]['codigo'];
        $b=100000;
    }else{
        $a=0;
        $b=100000;
    }
}
$where = array(
    'tipo' => 1,
    'codigo>=' => $a,
    'codigo<=' => $b
);
$comp=$db->select('*')->from('con_comprobante')->where($where)->fetch_first();
$date=$comp['fecha'];
$where2 = array(
    'tipo' => 2,
    'codigo>=' => $a,
    'codigo<=' => $b
);
$comp2=$db->select('*')->from('con_comprobante')->where($where2)->fetch_first();
$date2=$comp2['fecha'];

$mon = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();

$datos = $db->select('*')->from('con_datos_empresa')->where('id_empresa',1)->fetch_first();

$pdf->SetPageOrientation('v');
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetLeftMargin(50);
$pdf->SetRightMargin(50);
$pdf->SetTopMargin(70);

// Adiciona la pagina
$pdf->AddPage();

//$nombre= 'NOMBRE DE LAS EMPRESAS';

// Define el titulo del documento
$pdf->SetFont('Roboto', 'B', 13);
$pdf->Cell(0, 15, $datos['nombre_empresa'].' '.$datos['razon_social'],0, true, 'C', false, '', 0, false, 'T', 'M');
$pdf->Cell(0, 15, ' Libro Diario ',0, true, 'C', false, '', 0, false, 'T', 'M');
$cad=fechaCastellano($date);
if($b>1000){$cad=$cad.' a la fecha actual';}else{$cad=$cad.' al '.fechaCastellano($date2);}
$pdf->Cell(0, 15, $cad,0, true, 'C', false, '', 0, false, 'T', 'M');
$pdf->Cell(0, 15, '(Expresado en '.$mon['moneda'].')',0, true, 'C', false, '', 0, false, 'T', 'M');

// Salto de linea
$pdf->Ln(15);
$pdf->SetFont('Roboto', 'B', 10);
$pdf->setCellPaddings(1, 1, 1, 1);

// set cell margins
$pdf->setCellMargins(1, 1, 1, 1);

// set color for background
$pdf->SetFillColor(255, 255, 255);

$pdf->SetTextColor(255, 255, 255);


$aux='';
$caux=0;
$pdf->SetFont('Roboto', 'N', 9);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$where0 = array(
    'codigo >=' => $a,
    'codigo <=' => $b
);
$comprob0=$db->select('*')->from('con_comprobante')->where($where0)->fetch();
foreach($comprob0 as $comp){
    //$pdf->Cell(0, 15, ' Asiento Nº '.$comp['codigo'].'',0, true, 'C', false, '', 0, false, 'T', 'M');
    //$pdf->MultiCell(40, 1, $acn, 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(69, 20, 'ASIENTO Nº: ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(69, 1, $comp['codigo'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
    
    $asiento=$db->select('*')->from('con_asiento')->where('comprobante',$comp['codigo'])->join('con_plan', 'con_plan.n_plan = con_asiento.cuenta')->fetch();
    $sw=1;
    $sw1=1;
    foreach($asiento as $asi){ 
        $cuu=$db->select('*')->from('con_cuenta')->where('n_cuenta',$asi['nodo'])->fetch_first();
        
        if($sw==1){
            $pdf->MultiCell(49, 20, 'FECHA: ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
            $pdf->MultiCell(69, 1, $comp['fecha'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
            
            $sw=0;
            
            if($comp['tipo']==1){       
                $pdf->MultiCell(69, 20, 'TIPO: ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
                $pdf->MultiCell(69, 1, 'Apertura', 0, 'L', 1, 1, '', '', true, 0, false, true, 20, 'M');
            }else{
                if($comp['tipo']==2){   
                    $pdf->MultiCell(69, 20, 'TIPO: ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
                    $pdf->MultiCell(260, 1, 'Cierre', 0, 'L', 1, 1, '', '', true, 0, false, true, 20, 'M');
                }else{        
                    $pdf->MultiCell(69, 20, 'TIPO: ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');          
                    $pdf->MultiCell(260, 1, 'Contable', 0, 'L', 1, 1, '', '', true, 0, false, true, 20, 'M');
                }
            }
            
            $pdf->MultiCell(40, 20, 'Codigo', 1, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
            $pdf->MultiCell(79, 20, 'Clasificacion', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
            $pdf->MultiCell(270, 20, 'Descripcion', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
            $pdf->MultiCell(49, 20, 'Debe', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
            $pdf->MultiCell(49, 20, 'Haber', 1, 'C', 1, 1, '', '', true, 0, false, true, 20, 'M');
        }
        
        $pdf->MultiCell(49, 1, $asi['cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(79, 1, $cuu['cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(260, 1, $asi['plan_cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
        
        if($asi['debe']!=0){
            $deb = $asi['debe'];
            $pdf->MultiCell(49, 1, convercion($deb,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
        }else{
            $deb=number_format(0,2);
            $pdf->MultiCell(49, 1, convercion($deb,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
        }
        if($asi['haber']!=0){
            $deb = $asi['haber'];
            $pdf->MultiCell(49, 1, convercion($deb,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
        }else{
            $deb=number_format(0,2);
            $pdf->MultiCell(49, 1, convercion($deb,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
        }
    }
    $pdf->MultiCell(500, 1, $comp['glosa'], 0, 'L', 1, 1, '', '', true, 0, false, true, 20, 'M');
}

// Genera el nombre del archivo
$nombre = 'sumas_'  . '_' . date('Y-m-d_H-i-s') . '.pdf';


// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

