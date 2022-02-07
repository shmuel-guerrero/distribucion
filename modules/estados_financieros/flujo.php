<?php

// Obtiene los almacenes
$almacenes = $db->select('z.*')->from('inv_almacenes z')->order_by('z.id_almacen')->fetch();

$cuentas = $db->select('*')->from('con_cuenta')->order_by('id_cuenta', 'asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir_flujo', $permisos);

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

$cuenta = $db->select('con_plan.*')->from('con_asiento')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
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

$cuenta = $db->distinct()->select('con_plan.*')->from('con_asiento')->where($where2)->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->order_by('n_plan', 'asc')->fetch();
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
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
                <a href="?/balances/imprimir_general" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
            <?php } ?>
            <?php if ($permiso_crear) { ?>
                <a href="?/cuentas/crear" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span> Nuevo</span></a>
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
<?php if (isset($_SESSION[temporary])) { ?>
    <div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><?= $_SESSION[temporary]['title']; ?></strong>

        <p><?= $_SESSION[temporary]['message']; ?></p>
    </div>
    <?php unset($_SESSION[temporary]); ?>
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
$act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
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
$virt = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=20 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$otp = 0;
$otn = 0;
$opeT = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=1 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$invp = 0;
$invn = 0;
$invT = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=2 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$finp = 0;
$finn = 0;
$finT = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $b . " AND c.actividadc=3 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

$efe1 = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=30 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
$efe2 = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=30 AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
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
        $vir = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=20 AND c.n_plan='" . $virto['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
        $vird = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=20 AND c.n_plan='" . $virto['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
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
            $ope = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=1 AND c.n_plan='" . $opeTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            $oped = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=1 AND c.n_plan='" . $opeTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
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
            <?php   $inv = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=2 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            $invd = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=2 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
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
            <?php   $fin = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND c.actividadc=3 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
            $find = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $c . " AND a.comprobante<" . $d . " AND c.actividadc=3 AND c.n_plan='" . $invTo['n_plan'] . "' AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
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
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
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
            reports: 'xls|doc|pdf|html'
        });
        <?php } ?>
    });
</script>
<?php require_once show_template('footer-configured'); ?>
