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

$pdf->SetPageOrientation('L');
$pdf->setPrintHeader(false);
$pdf->SetPrintFooter(false);
$pdf->SetLeftMargin(45);
$pdf->SetRightMargin(50);
$pdf->SetTopMargin(45);

// Adiciona la pagina
$pdf->AddPage();

//$nombre= 'NOMBRE DE LAS EMPRESAS';

// Define el titulo del documento
$pdf->SetFont('Roboto', 'B', 13);
$pdf->Cell(0, 15, $datos['nombre_empresa'].' '.$datos['razon_social'],0, true, 'C', false, '', 0, false, 'T', 'M');
$pdf->Cell(0, 15, ' Hoja de trabajo de diez columnas ',0, true, 'C', false, '', 0, false, 'T', 'M');
$cad=fechaCastellano($date);
if($b>1000){$cad=$cad.' a la fecha actual';}else{$cad=$cad.' al '.fechaCastellano($date2);}
$pdf->Cell(0, 15, $cad,0, true, 'C', false, '', 0, false, 'T', 'M');
$pdf->Cell(0, 15, '(Expresado en '.$mon['moneda'].')',0, true, 'C', false, '', 0, false, 'T', 'M');

// Salto de linea
$pdf->Ln(15);
$pdf->SetFont('Roboto', 'B', 8);
$pdf->setCellPaddings(1, 1, 1, 1);

// set cell margins
$pdf->setCellMargins(1, 1, 1, 1);

// set color for background
$pdf->SetFillColor(255, 255, 255);

$pdf->SetTextColor(255, 255, 255);

// Vertical alignment
$pdf->MultiCell(40, 20, ' ', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(160, 20, ' ', 0, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');

$pdf->SetFillColor(0, 0, 0);

$pdf->MultiCell(100, 20, 'SALDOS A AJUSTAR', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(100, 20, 'AJUSTES', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(100, 20, 'SALDOS AJUSTADOS', 1, 'C', 1, 0   , '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(100, 20, 'ESTADO RESULTADOS', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(100, 20, 'BALANCE GENERAL', 1, 'C', 1, 1, '', '', true, 0, false, true, 20, 'M');

$pdf->MultiCell(40, 20, ' ', 1, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(160, 20, 'Descripcion', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Deudor', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Acreedor', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Debe', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Haber', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Deudor', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Acreedor', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Gastos', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Ingresos', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Activos', 1, 'C', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, 'Pasivos', 1, 'C', 1, 1, '', '', true, 0, false, true, 20, 'M');


$t1=0;
$t2=0;
$t3=0;
$t4=0;
$t5=0;
$t6=0;
$t7=0;
$t8=0;
$t9=0;
$tA=0;
$sw=0;

$aux='';
$caux=0;
$pdf->SetFont('Roboto', 'N', 9);
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
//$cue = $db->select('comprobante')->from('con_asiento')->where('comprobante>=',$a)->where('comprobante<',$b)->join('plan','plan.n_plan=asiento.cuenta')->order_by('comprobante','asc')->order_by('cuenta','asc')->fetch();
$cue = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) 
                FROM con_asiento a,con_plan c WHERE a.comprobante=".$a." AND a.cuenta=c.n_plan AND c.estado=1 
                GROUP BY c.n_plan 
                ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

foreach($cue as $cu){
    $ajus = $db->select('*')->from('con_asiento')->where('comprobante>',$a)->where('comprobante<',$b)->where('cuenta',$cu['n_plan'])->fetch();
    $acn=str_replace('.','',$cu['n_plan']);
    $aux[$caux]=$cu['n_plan'];
    if($cu['SUM(a.debe)']>$cu['SUM(a.haber)']){
        $e=$cu['SUM(a.debe)']-$cu['SUM(a.haber)'];$f=0;
        $t1=$t1+$e;
    }else{
        $f=$cu['SUM(a.haber)']-$cu['SUM(a.debe)'];$e=0;
        $t2=$t2+$f;
    }
    if(isset($ajus)){
        $g = sumar_vec($ajus,'debe');
        $t3=$t3+$g;
        $h = sumar_vec($ajus,'haber');
        $t4=$t4+$h;
    }
    if($e+$g > $f+$h){
        $i=$e+$g-($f+$h);
        $t5=$t5+$i;
        $j=0;
    }else{
        $i=0;
        $j=$f+$h-($e+$g);
        $t6=$t6+$j;
    }
    if(substr($cu['n_plan'],0,1)=='3' || substr($cu['n_plan'],0,1)=='4'){
       $k = sumar_vec($ajus,'debe');
       $l = sumar_vec($ajus,'haber');
        $t7=$t7+$k;
        $t8=$t8+$l;
    }else{
       $k=0;$l=0;
    }
    if(substr($cu['n_plan'],0,1)=='1' || substr($cu['n_plan'],0,1)=='2' || substr($cu['n_plan'],0,1)=='3'){
        $m=$i;
        $n=$j;
        $t9=$t9+$m;
        $tA=$tA+$n;
    }else{
        $m=0;$n=0;
    }


    $pdf->MultiCell(40, 1, $acn, 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(160, 1, $cu['plan_cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($e,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($f,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($g,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($h,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($i,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($j,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($k,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($l,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($m,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($n,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
}
$pdf->MultiCell(40, 1, '', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(160, 1, '', 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 1, convercion($t1,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 1, convercion($t2,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 1, convercion($t3,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 1, convercion($t4,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');

$cue = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) 
                    FROM con_asiento a,con_plan c 
                    WHERE a.comprobante>".$a." AND a.comprobante<".$b." AND a.cuenta=c.n_plan AND c.estado=1 
                    GROUP BY c.n_plan 
                    ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

foreach($cue as $cu){
    $ajus = $db->select('*')
                ->from('con_asiento')
                ->where('comprobante>',$a)
                ->where('comprobante<',$b)
                ->where('cuenta',$cu['n_plan'])
                ->fetch();
    $acn=str_replace('.','',$cu['n_plan']);
    $aux[$caux]=$cu['n_plan'];
    if($cu['SUM(a.debe)']>$cu['SUM(a.haber)']){
        $e=$cu['SUM(a.debe)']-$cu['SUM(a.haber)'];
        $f=0;
        $t1=$t1+$e;
    }else{
        $f=$cu['SUM(a.haber)']-$cu['SUM(a.debe)'];
        $e=0;
        $t2=$t2+$f;
    }
    if(isset($ajus)){
        $g = sumar_vec($ajus,'debe');
        $t3=$t3+$g;
        $h = sumar_vec($ajus,'haber');
        $t4=$t4+$h;
    }
    if($e+$g > $f+$h){
        $i=$e+$g-($f+$h);
        $t5=$t5+$i;
        $j=0;
    }else{
        $i=0;
        $j=$f+$h-($e+$g);
        $t6=$t6+$j;
    }
    if(substr($cu['n_plan'],0,1)=='3' || substr($cu['n_plan'],0,1)=='4'){
        $k = sumar_vec($ajus,'debe');
        $l = sumar_vec($ajus,'haber');
        $t7=$t7+$k;
        $t8=$t8+$l;
    }else{
        $k=0;$l=0;
    }
    if(substr($cu['n_plan'],0,1)=='1' || substr($cu['n_plan'],0,1)=='2' || substr($cu['n_plan'],0,1)=='3'){
        $m=$i;
        $n=$j;
        $t9=$t9+$m;
        $tA=$tA+$n;
    }else{
        $m=0;$n=0;
    }
/*
    $pdf->MultiCell(40, 1, $acn, 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(160, 1, $cu['plan_cuenta'], 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($e,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($f,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($g,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($h,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($i,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($j,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($k,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($l,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($m,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
    $pdf->MultiCell(49, 1, convercion($n,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');
*/
}

/*
$pdf->SetFillColor(0, 0, 0, 10);
$pdf->MultiCell(40, 20, '', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(160, 20, 'TOTAL', 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t1,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t2,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t3,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t4,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t5,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t6,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t7,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t8,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');

if($t5>$t6){
    $t16=$t5-$t6;$t15=0;
}else{
    $t15=$t6-$t5;$t16=0;
}
if($t7<$t8){
    $t17=$t8-$t7;$t18=0;
}else{
    $t18=$t7-$t8;$t17=0;
}
$pdf->SetFillColor(0, 0, 0, 15);
$pdf->MultiCell(40, 20, '', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(160, 20, 'UTILIDAD DEL PERIODO', 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t15,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t16,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t15,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t16,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t17,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t18,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');

$t15=$t15+$t5;
$t16=$t16+$t6;
$t17=$t17+$t7;
$t18=$t18+$t8;
$pdf->SetFillColor(0, 0, 0, 20);
$pdf->MultiCell(40, 20, '', 0, 'J', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(160, 20, ' ', 0, 'L', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, '', 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t15,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t16,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t15,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t16,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t17,1,$db), 0, 'R', 1, 0, '', '', true, 0, false, true, 20, 'M');
$pdf->MultiCell(49, 20, convercion($t18,1,$db), 0, 'R', 1, 1, '', '', true, 0, false, true, 20, 'M');

*/

// Genera el nombre del archivo
$nombre = 'sumas_'  . '_' . date('Y-m-d_H-i-s') . '.pdf';


// Cierra y devuelve el fichero pdf
$pdf->Output($nombre, 'I');

