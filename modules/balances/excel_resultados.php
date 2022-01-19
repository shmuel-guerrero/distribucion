<?php
// Importa la libreria para generar el reporte
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
$pdf->SetLeftMargin(70);
$pdf->SetRightMargin(70);
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
$pdf->Cell(0, 15, ' Balance General ',0, true, 'C', false, '', 0, false, 'T', 'M');
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

$txt = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

// set color for background
$pdf->SetFillColor(0, 0, 0);

$pdf->SetTextColor(255, 255, 255);

// Vertical alignment
$pdf->MultiCell(80, 20, ' ', 1, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(305, 20, 'Descripcion ', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(80, 20, ' ', 1, 'J', 1, 1, '', '', true, 0, false, true, 20, 'M');

$t1=0;
$t2=0;

$aux2=$db->select('*')->from('con_cuenta')->where('id_cuenta<',4)->fetch();
foreach($aux2 as $au2){
    $cue = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=".$a." AND a.comprobante<".$b." AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '".$au2['id_cuenta']."%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
    if($au2['id_cuenta']==1){$totc = sumar_vec($cue,'SUM(a.debe)')-sumar_vec($cue,'SUM(a.haber)');$t1=$t1+$totc;}else{$totc = sumar_vec($cue,'SUM(a.haber)')-sumar_vec($cue,'SUM(a.debe)');$t2=$t2+$totc;}
    $pdf->SetFont('Roboto', 'B', 10);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(0, 0, 0);
// Multicell test
    $pdf->MultiCell(80, 20, $au2['n_cuenta'], 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(305, 20, $au2['cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(80, 20, convercion($totc,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
    $aux1=$db->select('*')->from('con_plan')->where('tipo','2')->where('nodo',$au2['id_cuenta'])->fetch();
    foreach($aux1 as $aux){
        $act=$db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=".$a." AND a.comprobante<".$b." AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '".$aux['n_plan']."%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
        if($aux['nodo']==1){$tot=sumar_vec($act,'SUM(a.debe)')-sumar_vec($act,'SUM(a.haber)');}else{$tot=sumar_vec($act,'SUM(a.haber)')-sumar_vec($act,'SUM(a.debe)');}
        $pdf->SetFont('Roboto', 'B', 10);
        $auxn = str_replace('.','',$aux['n_plan']);
        $pdf->MultiCell(80, 20, $auxn, 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(305, 20, $aux['plan_cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(80, 20, convercion($tot,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');

        $pdf->SetFont('Roboto', '', 10);
        foreach($act as $ac){
            if($aux['nodo']==1){$acd=$ac['SUM(a.debe)']-$ac['SUM(a.haber)'];}else{$acd=$ac['SUM(a.haber)']-$ac['SUM(a.debe)'];};
            if($acd!=0){
                $e=convercion($acd,1,$db);
                $acn=str_replace('.','',$ac['n_plan']);
                $pdf->MultiCell(80, 20, $acn, 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
                $pdf->MultiCell(305, 20, $ac['plan_cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
                $pdf->MultiCell(80, 20, $e, 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
            }
        }

    }
    if($au2['id_cuenta']==1){
        $pdf->SetFont('Roboto', 'B', 10);
        $pdf->MultiCell(80, 20, $au2['n_cuenta'], 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(305, 20, 'TOTAL '.$au2['cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
        $pdf->MultiCell(80, 20, convercion($totc,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
    }
}
if($t1!=$t2){
    $pdf->SetFont('Roboto', '', 10);
    $pdf->MultiCell(80, 20, '', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(305, 20, 'UTILIDAD NETA', 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(80, 20, convercion($t1-$t2,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
    $t2=$t2+$t1-$t2;
}
$pdf->SetFont('Roboto', 'B', 10);
$pdf->MultiCell(80, 20, '', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(305, 20, 'TOTAL PASIVO Y '.$au2['cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(80, 20, convercion($t2,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');



// Genera el nombre del archivo
$nombre = 'recepcion_'  . '_' . date('Y-m-d_H-i-s') . '.pdf';


// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

