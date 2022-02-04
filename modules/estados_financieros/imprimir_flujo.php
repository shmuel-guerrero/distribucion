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



/*
// Obtiene los almacenes
$almacenes = $db->select('z.*')->from('con_inv_almacenes z')->order_by('z.id_almacen')->fetch();

$cuentas = $db->select('*')->from('con_cuenta')->order_by('id_cuenta', 'asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', PERMITS);

// Almacena los permisos en variables
$permiso_crear = in_array(FILE_CREATE, $permisos);
$permiso_editar = in_array(FILE_UPDATE, $permisos);
$permiso_ver = in_array(FILE_READ, $permisos);
$permiso_eliminar = in_array(FILE_DELETE, $permisos);
$permiso_imprimir = in_array(FILE_PRINT, $permisos);

function convercion($mon, $tip, $db)
{
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
    if ($tip > 1) {
        $tipob = $db->select('*')->from('con_tipo_moneda')->where('id_moneda', $tip)->fetch_first();
        $mon = $mon / $tipob['valor'];
    }

    $res = $mon / $tipo['valor'];

    echo number_format($res, 2);
}

function fechaCastellano($fecha)
{
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
    return $nombredia . " " . $numeroDia . " de " . $nombreMes . " de " . $anio;
}

$cuenta = $db->select('plan.*')->from('con_asiento')->join('plan', 'plan.n_plan=asiento.cuenta')->fetch();
$g = 0;
$comp1 = $db->select('*')->from('con_comprobante')->where('tipo', 1)->order_by('fecha', 'desc')->fetch();
$comp2 = $db->select('*')->from('con_comprobante')->where('tipo', 2)->order_by('fecha', 'desc')->fetch();

if (sizeof($params) > 0) {
    $a = $params[0];
    $b = $params[1];
    $c = $params[2];
    $d = $params[3];
} else {
    if (isset($comp1[1]['codigo'])) {
        $a = $comp1[0]['codigo'];
        $b = 100000;
        $c = $comp1[1]['codigo'];
        $d = $comp2[0]['codigo'];
    } else {
        $a = 0;
        $b = 100000;
        header('Location: balance_general.php');
    }
}
$where = array(
    'codigo >=' => $a,
    'codigo <=' => $b
);
$comprob = $db->select('*')->from('con_comprobante')->where($where)->fetch();
$where2 = array(
    'comprobante>=' => $a,
    'comprobante<=' => $b,
);

$cuenta = $db->distinct()->select('plan.*')->from('con_asiento')->where($where2)->join('plan', 'plan.n_plan=asiento.cuenta')->order_by('n_plan', 'asc')->fetch();
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="<?= ICON_PANEL; ?>"></span>
        <strong>Estado de flujo</strong>
    </h3>
</div>
<div class="panel-body">
<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $almacenes)) { ?>
    <div class="row">
        <div class="col-sm-8 hidden-xs">
            <div class="text-label">Para agregar nuevos almacenes hacer clic en el siguiente botón:</div>
        </div>
        <div class="col-xs-12 col-sm-4 text-right">
            <?php if ($permiso_imprimir) { ?>
                <a href="?/almacenes/imprimir" target="_blank" class="btn btn-info"><i
                        class="<?= ICON_PRINT; ?>"></i><span class="hidden-xs"> Imprimir</span></a>
            <?php } ?>
            <?php if ($permiso_crear) { ?>
                <a href="?/cuentas/crear" class="btn btn-primary"><i class="<?= ICON_CREATE; ?>"></i><span> Nuevo</span></a>
            <?php } ?>
        </div>
    </div>

    <hr>
<?php } ?>
<div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="true">
        Seleccionar periodo
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li>
            <a href="?/estados_financieros/flujo/<?= $comp1[$g + 1]['codigo'] ?>/<?= $comp2[$g]['codigo'] ?>/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g + 1]['codigo'] ?>
                - <?= $comp2[$g]['codigo'] ?> con <?= $comp1[$g]['codigo'] ?>
                - Actual</a></li>
        <?php
        $g++;
        for ($i = 1; $i < count($comp1) - 1; $i++) {
            ?>
            <li>
                <a href="?/estados_financieros/flujo/<?= $comp1[$i + 1]['codigo'] ?>/<?= $comp2[$i]['codigo'] ?>/<?= $comp1[$i]['codigo'] ?>/<?= $comp2[$i - 1]['codigo'] ?>"><?= $comp1[$i + 1]['codigo'] ?>
                    - <?= $comp2[$i]['codigo'] ?> con <?= $comp1[$i]['codigo'] ?> - <?= $comp2[$i - 1]['codigo'] ?></a>
            </li>
            <?php $g++;
        }
        ?>
    </ul>
</div>
<hr/>
<?php if (isset($_SESSION[TEMPORARY])) { ?>
    <div class="alert alert-<?= $_SESSION[TEMPORARY]['alert']; ?>">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><?= $_SESSION[TEMPORARY]['title']; ?></strong>

        <p><?= $_SESSION[TEMPORARY]['message']; ?></p>
    </div>
    <?php unset($_SESSION[TEMPORARY]); ?>
<?php } ?>
<?php
$where = array(
    'tipo' => 1,
    'codigo>=' => $a,
    'codigo<=' => $b
);
$comp = $db->select('*')->from('con_comprobante')->where($where)->fetch_first();
$date = $comp['fecha'];
$where2 = array(
    'tipo' => 2,
    'codigo>=' => $a,
    'codigo<=' => $b
);
$comp2 = $db->select('*')->from('con_comprobante')->where($where2)->fetch_first();
$date2 = $comp2['fecha'];

$mon = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
?>
<div class="col-sm-8 col-sm-offset-2">
Checkcode S.R.L.
<br/>La Paz - Bolivia
<h3 class="centro">Estado de Flujo de Efectivo Comparativo
    <br/><?php echo fechaCastellano($date); ?> <?php if ($b > 1000) {
        echo 'a la fecha actual';
    } else {
        echo 'al ' . fechaCastellano($date2);
    } ?>
    <br/>(Expresado en <?= $mon['moneda'] ?>)</h3>

<div>
<?php
$deb = 0;
$hab = 0;
$act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
foreach ($act as $ac) {
    if ($ac['nodo'] == 1 || $ac['nodo'] == 2 || $ac['nodo'] == 3) {
        if ($ac['SUM(a.debe)'] > $ac['SUM(a.haber)']) {
            $deb = $deb + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
        } else {
            $hab = $hab + $ac['SUM(a.haber)'] - $ac['SUM(a.debe)'];
        }
    }
}
$opep = 0;
$open = 0;
$virt = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=20 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$otp = 0;
$otn = 0;
$opeT = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=1 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$invp = 0;
$invn = 0;
$invT = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=2 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$finp = 0;
$finn = 0;
$finT = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=3 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$efe1 = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=30 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
$efe2 = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=30 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
?>
<table width="100%">
    <tr>
        <td colspan="2"></td>
        <td>ORIGEN</td>
        <td>APLICACIONES</td>
    </tr>
    <tr>
        <td>ACTIVIDADES DE OPERACIÓN</td>
    </tr>
    <tr>
        <?php if ($deb > $hab) { ?>
            <td>Utilidad Neta</td>
            <td><?php convercion($deb - $hab, 1, $db);
                $opep = $opep + $deb - $hab; ?></td>
        <?php } else { ?>
            <td>Perdida Neta</td>
            <td><?php convercion($hab - $deb, 1, $db);
                $open = $open + $hab - $deb; ?></td>
        <?php } ?>
    </tr>
    <?php
    foreach ($virt as $virto) {
        $vir = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=20 AND c.n_plan='" . $virto['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
        $vird = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=20 AND c.n_plan='" . $virto['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
        if ((($vir['SUM(a.debe)'] - $vir['SUM(a.haber)']) + ($vird['SUM(a.debe)'] - $vird['SUM(a.haber)'])) > 0) {
            $opep = $opep + ($vir['SUM(a.debe)'] - $vir['SUM(a.haber)']) + ($vird['SUM(a.debe)'] - $vird['SUM(a.haber)']); ?>
            <tr>
            <td><?= $virto['plan_cuenta'] ?></td>
            <td><?= convercion((($vir['SUM(a.debe)'] - $vir['SUM(a.haber)']) + ($vird['SUM(a.debe)'] - $vird['SUM(a.haber)'])), 1, $db) ?></td>
        <?php } else {
            $open = $open + ($vir['SUM(a.haber)'] - $vir['SUM(a.debe)']) + ($vird['SUM(a.haber)'] - $vird['SUM(a.debe)']); ?>
            <td><?= $virto['plan_cuenta'] ?></td>
            <td><?= convercion((($vir['SUM(a.haber)'] - $vir['SUM(a.debe)']) + ($vird['SUM(a.haber)'] - $vird['SUM(a.debe)'])), 1, $db) ?></td>
            </tr>
        <?php
        }
    }
    ?>
    <tr>
        <td colspan="2">Suma</td>
        <?php if ($opep - $open > 0) { ?>
            <td><?= convercion($opep - $open, 1, $db) ?></td>
            <td></td>
        <?php } else { ?>
            <td></td>
            <td><?= convercion($open - $opep, 1, $db) ?></td>
        <?php } ?>
    </tr>

    <?php
    foreach ($opeT as $opeTo) {
        ?>
        <tr>
            <td><?= $opeTo['plan_cuenta'] ?></td>
            <td></td>
            <?php
            $ope = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=1 AND c.n_plan='" . $opeTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            $oped = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=1 AND c.n_plan='" . $opeTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            if ($opeTo['nodo'] == 1) {
                if ((($ope['SUM(a.debe)'] - $ope['SUM(a.haber)']) - ($oped['SUM(a.debe)'] - $oped['SUM(a.haber)'])) < 0) {
                    $opep = $opep + (($ope['SUM(a.debe)'] - $ope['SUM(a.haber)']) - ($oped['SUM(a.debe)'] - $oped['SUM(a.haber)'])) * (-1);?>
                    <td><?= convercion(((($ope['SUM(a.debe)'] - $ope['SUM(a.haber)']) - ($oped['SUM(a.debe)'] - $oped['SUM(a.haber)'])) * (-1)), 1, $db) ?></td>
                    <td></td>
                <?php
                } else {
                    $open = $open + (($ope['SUM(a.debe)'] - $ope['SUM(a.haber)']) - ($oped['SUM(a.debe)'] - $oped['SUM(a.haber)']));?>
                    <td></td>
                    <td><?= convercion(((($ope['SUM(a.debe)'] - $ope['SUM(a.haber)']) - ($oped['SUM(a.debe)'] - $oped['SUM(a.haber)']))), 1, $db) ?></td>
                <?php
                }
            } else {
                if ((($ope['SUM(a.haber)'] - $ope['SUM(a.debe)']) - ($oped['SUM(a.haber)'] - $oped['SUM(a.debe)'])) < 0) {
                    $opep = $opep + ($ope['SUM(a.haber)'] - $ope['SUM(a.debe)']) - ($oped['SUM(a.haber)'] - $oped['SUM(a.debe)']); ?>
                    <td><?= convercion(((($ope['SUM(a.haber)'] - $ope['SUM(a.debe)']) - ($oped['SUM(a.haber)'] - $oped['SUM(a.debe)'])) * (-1)), 1, $db) ?></td>
                <?php
                } else {
                    $open = $open + (($ope['SUM(a.haber)'] - $ope['SUM(a.debe)']) - ($oped['SUM(a.haber)'] - $oped['SUM(a.debe)'])) * (-1);?>
                    <td><?= convercion(((($ope['SUM(a.haber)'] - $ope['SUM(a.debe)']) - ($oped['SUM(a.haber)'] - $oped['SUM(a.debe)']))), 1, $db) ?></td>
                <?php
                }
            } ?>
        </tr>
    <?php
    }
    ?>
    <tr>
        <td class="der">SUBTOTALES</td>
        <td></td>
        <td><?= convercion($opep, 1, $db) ?></td>
        <td><?= convercion($open, 1, $db) ?></td>
    </tr>
    <tr>
        <td>Flujos netos de efectivo de actividades de operación</td>
        <td></td><?php
        if ($opep - $open > 0) {
            $otp = $opep - $open;
            $otn = 0; ?>
            <td><?= convercion($otp, 1, $db) ?></td>
            <td></td>
        <?php
        } else {
            $otn = $open - $opep;
            $otp = 0; ?>
            <td></td>
            <td><?= convercion($otn, 1, $db) ?></td>
        <?php
        }
        ?>
    </tr>
    <tr>
        <td>ACTIVIDADES DE INVERSIÓN</td>
    </tr>
    <?php
    foreach ($invT as $invTo) {
        ?>
        <tr>
            <td><?= $invTo['plan_cuenta'] ?></td>
            <td></td>
            <?php   $inv = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=2 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            $invd = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=2 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            if ($invTo['nodo'] == 1) {
                if ((($inv['SUM(a.debe)'] - $inv['SUM(a.haber)']) - ($invd['SUM(a.debe)'] - $invd['SUM(a.haber)'])) > 0) {
                    $invp = $invp + ($inv['SUM(a.debe)'] - $inv['SUM(a.haber)']) - ($invd['SUM(a.debe)'] - $invd['SUM(a.haber)']);?>
                    <td><?= convercion((($inv['SUM(a.debe)'] - $inv['SUM(a.haber)']) - ($invd['SUM(a.debe)'] - $invd['SUM(a.haber)'])), 1, $db) ?></td>
                    <td></td>
                <?php
                } else {
                    $invn = $invn + (($inv['SUM(a.debe)'] - $inv['SUM(a.haber)']) - ($invd['SUM(a.debe)'] - $invd['SUM(a.haber)'])) * (-1);?>
                    <td><?= convercion(((($inv['SUM(a.debe)'] - $inv['SUM(a.haber)']) - ($invd['SUM(a.debe)'] - $invd['SUM(a.haber)'])) * (-1)), 1, $db) ?></td>
                <?php
                }
            } else {
                if ((($inv['SUM(a.haber)'] - $inv['SUM(a.debe)']) - ($invd['SUM(a.haber)'] - $invd['SUM(a.debe)'])) < 0) {
                    $invp = $invp + ($inv['SUM(a.haber)'] - $inv['SUM(a.debe)']) - ($invd['SUM(a.haber)'] - $invd['SUM(a.debe)']); ?>
                    <td><?= convercion((($inv['SUM(a.haber)'] - $inv['SUM(a.debe)']) - ($invd['SUM(a.haber)'] - $invd['SUM(a.debe)'])), 1, $db) ?></td>
                <?php
                } else {
                    $invn = $invn + (($inv['SUM(a.haber)'] - $inv['SUM(a.debe)']) - ($invd['SUM(a.haber)'] - $invd['SUM(a.debe)'])) * (-1);?>
                    <td><?= convercion(((($inv['SUM(a.haber)'] - $inv['SUM(a.debe)']) - ($invd['SUM(a.haber)'] - $invd['SUM(a.debe)'])) * (-1)), 1, $db) ?></td>
                <?php
                }
            } ?>
        </tr>
    <?php
    }
    ?>
    <tr>
        <td class="der">SUBTOTALES</td>
        <td></td>
        <td><?= convercion($invp, 1, $db) ?></td>
        <td><?= convercion($invn, 1, $db) ?></td>
    </tr>
    <tr>
        <td>Flujos netos de efectivo de actividades de inversión</td>
        <td></td><?php
        if ($invp - $invn > 0) {
            $itp = $invp - $invn; ?>
            <td><?= convercion($itp, 1, $db) ?></td>
            <td></td>
        <?php
        } else {
            $itn = $invn - $invp; ?>
            <td></td>
            <td><?= convercion($itn, 1, $db) ?></td>
        <?php
        }
        ?>
    </tr>
    <tr>
        <td>ACTIVIDADES DE FINANCIAMIENTO</td>
    </tr>
    <?php
    foreach ($finT as $finTo) {
        ?>
        <tr>
            <td><?= $finTo['plan_cuenta'] ?></td>
            <td></td>
            <?php   $fin = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=3 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            $find = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM asiento a,plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=3 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            if ($finTo['nodo'] == 1) {
                if ((($fin['SUM(a.debe)'] - $fin['SUM(a.haber)']) - ($find['SUM(a.debe)'] - $find['SUM(a.haber)'])) > 0) {
                    $finp = $finp + ($fin['SUM(a.debe)'] - $fin['SUM(a.haber)']) - ($find['SUM(a.debe)'] - $find['SUM(a.haber)']);?>
                    <td><?= convercion((($fin['SUM(a.debe)'] - $fin['SUM(a.haber)']) - ($find['SUM(a.debe)'] - $find['SUM(a.haber)'])), 1, $db) ?></td>
                    <td></td>
                <?php
                } else {
                    $finn = $finn + (($fin['SUM(a.debe)'] - $fin['SUM(a.haber)']) - ($find['SUM(a.debe)'] - $find['SUM(a.haber)'])) * (-1);?>
                    <td><?= convercion(((($fin['SUM(a.debe)'] - $fin['SUM(a.haber)']) - ($find['SUM(a.debe)'] - $find['SUM(a.haber)'])) * (-1)), 1, $db) ?></td>
                <?php
                }
            } else {
                if ((($fin['SUM(a.haber)'] - $fin['SUM(a.debe)']) - ($find['SUM(a.haber)'] - $find['SUM(a.debe)'])) < 0) {
                    $finp = $finp + ($inv['SUM(a.haber)'] - $fin['SUM(a.debe)']) - ($find['SUM(a.haber)'] - $find['SUM(a.debe)']); ?>
                    <td><?= convercion((($fin['SUM(a.haber)'] - $fin['SUM(a.debe)']) - ($find['SUM(a.haber)'] - $find['SUM(a.debe)'])), 1, $db) ?></td>
                <?php
                } else {
                    $finn = $finn + (($fin['SUM(a.haber)'] - $fin['SUM(a.debe)']) - ($find['SUM(a.haber)'] - $find['SUM(a.debe)'])) * (-1);?>
                    <td><?= convercion(((($fin['SUM(a.haber)'] - $fin['SUM(a.debe)']) - ($find['SUM(a.haber)'] - $find['SUM(a.debe)'])) * (-1)), 1, $db) ?></td>
                <?php
                }
            } ?>
        </tr>
    <?php
    }
    ?>
    <tr>
        <td class="der">SUBTOTALES</td>
        <td></td>
        <td><?= convercion($finp, 1, $db) ?></td>
        <td><?= convercion($finn, 1, $db) ?></td>
    </tr>
    <tr>
        <td>Flujos netos de efectivo de actividades de financiamiento</td>
        <td></td><?php
        if ($finp - $finn > 0) {
            $ftp = $finp - $finn; ?>
            <td><?= convercion($ftp, 1, $db) ?></td>
            <td></td>
        <?php
        } else {
            $ftn = $finn - $finp; ?>
            <td></td>
            <td><?= convercion($ftn, 1, $db) ?></td>
        <?php
        }
        ?>
    </tr>
    <tr>
        <td>INCREMENTO NETO DE EFECTIVO</td>
        <td></td>
        <td></td>
        <td><?= convercion(($finp - $finn + $invp - $invn + $opep - $open), 1, $db) ?></td>
    </tr>
    <tr>
        <td>SALDO DE EFECTIVO AL INICIO DEL PERIODO</td>
    </tr>
    <?php foreach ($efe2 as $ef2) { ?>
        <tr>
            <td><?= $ef2['plan_cuenta'] ?></td>
            <td><?php convercion($ef2['SUM(a.debe)'] - $ef2['SUM(a.haber)'], 1, $db); ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td>SALDO DE EFECTIVO AL FINAL DEL PERIODO</td>
    </tr>
    <?php foreach ($efe1 as $ef1) { ?>
        <tr>
            <td><?= $ef1['plan_cuenta'] ?></td>
            <td><?php convercion($ef1['SUM(a.debe)'] - $ef1['SUM(a.haber)'], 1, $db); ?></td>
        </tr>
    <?php } ?>

</table>
<?php //balsnce actual

?>

<br/><br/><br/>
</div>
</div>
</div>
<script src="<?= JS; ?>/jquery.dataTables.min.js"></script>
<script src="<?= JS; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= JS; ?>/jquery.base64.js"></script>
<script src="<?= JS; ?>/pdfmake.min.js"></script>
<script src="<?= JS; ?>/vfs_fonts.js"></script>
<script src="<?= JS; ?>/jquery.dataFilters.min.js"></script>
<script>
    $(function () {
        <?php if ($permiso_eliminar) { ?>
        $('[data-eliminar]').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            bootbox.confirm('Está seguro que desea eliminar la cuenta?', function (result) {
                if (result) {
                    window.location = url;
                }
            });
        });
        <?php } ?>

        <?php if ($permiso_crear) { ?>
        $(window).bind('keydown', function (e) {
            if (e.altKey || e.metaKey) {
                switch (String.fromCharCode(e.which).toLowerCase()) {
                    case 'n':
                        e.preventDefault();
                        window.location = '?/almacenes/crear';
                        break;
                }
            }
        });
        <?php } ?>

        <?php if ($almacenes) { ?>
        var table = $('#table').DataFilter({
            filter: false,
            name: 'almacenes',
            reports: 'excel|word|pdf|html'
        });
        <?php } ?>
    });
</script>
<?php require_once show_template('footer-configured'); ?>
