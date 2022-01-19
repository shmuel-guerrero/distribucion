<?php

require_once libraries . '/tcpdf-class/tcpdf.php';

function convercion($mon,$tip,$db){
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado',1)->fetch_first();
    if($tip>1){
        $tipob = $db->select('*')
                    ->from('con_tipo_moneda')
                    ->where('id_moneda',$tip)
                    ->fetch_first();
        $mon = $mon/$tipob['valor'];
    }
    $res = $mon  / $tipo['valor'];
    return $res;
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

$pdf->SetPageOrientation('V');
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetLeftMargin(50);
$pdf->SetRightMargin(50);
$pdf->SetTopMargin(70);

// Adiciona la pagina
$pdf->AddPage();

// Establece la fuente del titulo
$pdf->SetFont('Roboto', 'BU', 20);
//$nombre= 'NOMBRE DE LAS EMPRESAS';
$orden=   '123';
$cliente=   'RICARDO SIÑANI';
$pdf->Ln(5);
// Define el titulo del documento
$pdf->SetFont('Roboto', 'B', 13);
$pdf->Cell(0, 15, $datos['nombre_empresa'].' '.$datos['razon_social'],0, true, 'C', false, '', 0, false, 'T', 'M');
$pdf->Cell(0, 15, ' Libro Mayor ',0, true, 'C', false, '', 0, false, 'T', 'M');
$cad=fechaCastellano($date);
if($b>1000){
    $cad=$cad.' a la fecha actual';
}else{
    $cad=$cad.' al '.fechaCastellano($date2);
}
$pdf->Cell(0, 15, $cad,0, true, 'C', false, '', 0, false, 'T', 'M');
$pdf->Cell(0, 15, '(Expresado en '.$mon['moneda'].')',0, true, 'C', false, '', 0, false, 'T', 'M');

// Salto de linea
$pdf->Ln(15);
$pdf->SetFont('Roboto', 'B', 10);
$pdf->setCellPaddings(1, 1, 1, 1);

// set cell margins
$pdf->setCellMargins(1, 1, 1, 1);

// set color for background
//$pdf->SetFillColor(255, 255, 255);

$pdf->SetTextColor(0, 0, 0);

// Vertical alignment
$pdf->SetFillColor(240, 240, 240);

$pdf->SetFont('Roboto', 'B', 9);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);


$where0 = array(
    'codigo >=' => $a,
    'codigo <=' => $b
);
$comprob = $db->select('*')->from('con_comprobante')->where($where)->fetch();
$where3 = array(
    'comprobante>=' => $a,
    'comprobante<=' => $b,
);
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
$cuenta0 = $db->distinct()->select('con_plan.*')->from('con_asiento')->where($where3)->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->order_by('n_plan', 'asc')->fetch();
if ($cuenta0){
    foreach ($cuenta0 as $cuent0) {
        $pdf->MultiCell(69, 20, 'CUENTA: ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(189, 1, $cuent0['plan_cuenta'], 0, 'L', 1, 1, '', '', true, 0, false, true, 20, 'M');
        $saldo = number_format(0, 2);
        $saldoDebe = number_format(0, 2);
        $saldoHaber = number_format(0, 2);
        $where4 = array(
            'cuenta' => $cuent0['n_plan'],
            'comprobante>=' => $a,
            'comprobante<=' => $b
        );
        $asiento = $db->select('*')->from('con_asiento')->where($where4)->join('con_comprobante', 'con_comprobante.codigo=con_asiento.comprobante')->fetch();
        $sw = 1;
        $pdf->MultiCell(59, 20, 'FECHA ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(280, 20, 'DESCRIPCION ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(69, 20, 'DEBE ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(69, 20, 'HABER ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(69, 20, 'SALDO ', 0, 'J', 1, 1, '', '', true, 0, false, true, 20, 'M');
        
        foreach ($asiento as $asient) {
            $pdf->MultiCell(59, 1, $asient['fecha'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
            $pdf->MultiCell(250, 1, $asient['glosa'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
            if ($asient['debe'] != 0) {
                $deb = $asient['debe'];
                $debe= convercion($deb,1,$db,'',$mone['sigla']);
                $pdf->MultiCell(59, 1, number_format($debe,2,'.',' '), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
            }else{
                $hab=number_format(0, 2);
                $debe= convercion($hab,1,$db,'',$mone['sigla']);
                $pdf->MultiCell(59, 1, number_format($debe,2,'.',' '), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
            }
            if ($asient['haber'] != 0) {
                $hab = $asient['haber'];
                $habe= convercion($hab,1,$db,'',$mone['sigla']);
                $pdf->MultiCell(69, 1, number_format($habe,2,'.',' '), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
            }else{
                $hab=number_format(0,2);
                $habe= convercion($hab,1,$db,'',$mone['sigla']);
                $pdf->MultiCell(69, 1, number_format($habe,2,'.',' '), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
            }
            $saldo = $saldo + $asient['debe'] - $asient['haber'];
            $saldoDebe = $saldoDebe + $asient['debe'];
            $saldoHaber = $saldoHaber + $asient['haber'];
            $saldoo= convercion($saldo,1,$db,'',$mone['sigla']);
            $pdf->MultiCell(75, 1, number_format($saldoo,2,'.',' '), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
            
        }
        $pdf->MultiCell(300, 1, 'SALDOS', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $saldoDebee= convercion($saldoDebe,1,$db,'',$mone['sigla']);
        $pdf->MultiCell(70, 1, number_format($saldoDebee,2,'.',' '), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $saldoHaberr= convercion($saldoHaber,1,$db,'',$mone['sigla']);
        $pdf->MultiCell(70, 1, number_format($saldoHaberr,2,'.',' '), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');   
        $pdf->MultiCell(299, 1, 'TOTAL', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
        if($saldoDebe>=$saldoHaber){
            $total= convercion($saldo, 1, $db);
            $pdf->MultiCell(72, 1, number_format($total,2,'.',' '), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
        }else{
            $total= convercion(($saldo)*-1, 1, $db);
            $pdf->MultiCell(143, 1, number_format($total,2,'.',' '), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
        }
    }
}


// Genera el nombre del archivo
$nombre = 'sumas_'  . '_' . date('Y-m-d_H-i-s') . '.pdf';


// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

