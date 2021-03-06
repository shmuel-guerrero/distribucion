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
$permiso_imprimir = in_array('imprimir_general', $permisos);
$permiso_excel = in_array('excel_general', $permisos);

function convercion($mon, $tip, $db)
{
    $tipo = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
    if ($tip > 1) {
        $tipob = $db->select('*')->from('con_tipo_moneda')->where('id_moneda', $tip)->fetch_first();
        $mon = $mon / $tipob['valor'];
    }

    $res = $mon / $tipo['valor'];

    return number_format($res, 2);
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
} else {
    if (isset($comp1[0]['codigo'])) {
        $a = $comp1[0]['codigo'];
        $b = 100000;
    } else {
        $a = 0;
        $b = 100000;
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
        <strong>Balance general</strong>
    </h3>
</div>
<div class="panel-body">
    <?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $almacenes)) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para agregar nuevos almacenes hacer clic en el siguiente botón:</div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <?php if ($permiso_excel) { ?>
                    <a href="?/balances/excel_general/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-success"><span class="glyphicon glyphicon-file"></span><span class="hidden-xs"> Excel</span></a>
                <?php } ?>
                <?php if ($permiso_imprimir) { ?>
                    <a href="?/balances/imprimir_general/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
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
            <li><a href="?/balances/general/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> -
                    Actual</a></li>
            <?php
            $g++;
            foreach ($comp2 as $cp2) {
                ?>
                <li>
                    <a href="?/balances/general/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?>
                        - al - <?= $cp2['fecha'] ?></a></li>
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
        <h3 class="centro">Balance de general <br/><?php echo fechaCastellano($date); ?> <?php if ($b > 1000) {
                echo 'a la fecha actual';
            } else {
                echo 'al ' . fechaCastellano($date2);
            } ?>
            <br/>(Expresado en <?= $mon['moneda'] ?>)</h3>

        <div>
            <?php
            $actv = 0;
            $actvno = 0;
            $pasv = 0;
            $pasvno = 0;
            $cap = 0;
            $act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '1.1%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

            $actn = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '1.2%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $pas = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '2.1%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $pasn = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '2.2%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $pat = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '3%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $dem = count($act) + count($actn) - (count($pas) + count($pasn) + count($pat));?>
            <table width="98%" class="gen">
                <tr>
                    <td colspan="4" class="centro"><b>ACTIVO</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="2"><b>ACTIVO CORRIENTE</b></td>
                    <td colspan="3"></td>
                </tr>
                <?php foreach ($act as $ac) { ?>
                    <tr>
                        <td>&emsp;<?= $ac['plan_cuenta'] ?></td>
                        <td class="der"><?php $actv = $actv + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                            echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><b>TOTAL ACTIVO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($actv, 1, $db) ?></td>
                </tr>
                <tr>
                    <td><b>ACTIVO NO CORRIENTE</b></td>
                </tr>
                <?php foreach ($actn as $ac) { ?>
                    <tr>
                        <td>&emsp;<?= $ac['plan_cuenta'] ?></td>
                        <td class="der"><?php $actvno = $actvno + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                            echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><b>TOTAL NO ACTIVO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($actvno, 1, $db) ?></td>
                </tr>
                <?php if ($dem < 0) {
                    $dem = $dem * -1;
                    for ($j = 0; $j < $dem; $j++) {
                        echo '<tr><td><hr/></td></tr>';
                    }
                }
                ?>

                <tr>
                    <td><b>TOTAL ACTIVO</b></td>
                    <td colspan="2">
                        <hr/>
                    </td>
                    <td class="borde1 centro"><?= convercion($actv + $actvno, 1, $db) . ' ' . $mon['sigla'] ?></td>
                </tr>


                <tr>
                    <td colspan="4" class="centro"><b>PASIVO</b></td>
                </tr>
                <tr>
                    <td colspan="2"><b>PASIVO CORRIENTE</b></td>
                </tr>
                <?php foreach ($pas as $ps) { ?>
                    <tr>
                        <td>&emsp;<?= $ps['plan_cuenta'] ?></td>
                        <td class="der"><?php $pasv = $pasv + $ps['SUM(a.haber)'] - $ps['SUM(a.debe)'];
                            echo convercion($ps['SUM(a.haber)'] - $ps['SUM(a.debe)'], 1, $db) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><b>TOTAL PASIVO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($pasv, 1, $db) ?></td>
                </tr>
                <tr>
                    <td><b>PASIVO NO CORRIENTE</b></td>
                </tr>
                <?php foreach ($pasn as $ps) { ?>
                    <tr>
                        <td>&emsp;<?= $ps['plan_cuenta'] ?></td>
                        <td class="der"><?php $pasvno = $pasvno + $ps['SUM(a.haber)'] - $ps['SUM(a.debe)'];
                            echo convercion($ps['SUM(a.haber)'] - $ps['SUM(a.debe)'], 1, $db) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><b>TOTAL PASIVO NO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($pasvno, 1, $db) ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="centro"><b>PATRIMONIO O CAPITAL</b></td>
                </tr>
                <?php foreach ($pat as $pt) { ?>
                    <tr>
                        <td>&emsp;<?= $pt['plan_cuenta'] ?></td>
                        <td class="der"><?php $cap = $cap + $pt['SUM(a.haber)'] - $pt['SUM(a.debe)'];
                            echo convercion($pt['SUM(a.haber)'] - $pt['SUM(a.debe)'], 1, $db) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&emsp;UTILIDAD NETA</td>
                    <td class="der"><u><?php $aux = $actv + $actvno - $pasv - $pasvno - $cap;
                            echo convercion($aux, 1, $db);
                            $cap = $cap + $aux; ?></u></td>
                </tr>
                <tr>
                    <td><b>TOTAL PATRIMONIO</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($cap, 1, $db) ?></td>
                </tr>
                <?php if ($dem > 0) {

                }?>
                <tr>
                    <td><b>TOTAL PASIVO Y CAPITAL</b></td>
                    <td colspan="2">
                        <hr/>
                    </td>
                    <td class="borde1 centro"><?= convercion($pasv + $pasvno + $cap, 1, $db) . ' ' . $mon['sigla'] ?></td>
                </tr>
            </table>
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
