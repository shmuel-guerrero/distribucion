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
$permiso_imprimir = false;

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
        <strong>Cambio del patrimonio</strong>
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
                    <a href="?/estados_financieros/imprimir_patrimonio/" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
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
            <li><a href="?/estados_financieros/patrimonio/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> -
                    Actual</a></li>
            <?php
            $g++;
            foreach ($comp2 as $cp2) {
                ?>
                <li>
                    <a href="?/estados_financieros/patrimonio/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?>
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
        <h3 class="centro">ESTADO DE CAMBIO EN EL PATRIMONIO
            <br/><?php echo fechaCastellano($date); ?> <?php if ($b > 1000) {
                echo 'a la fecha actual';
            } else {
                echo 'al ' . fechaCastellano($date2);
            } ?>
            <br/>(Expresado en <?= $mon['moneda'] ?> )</h3>

        <div>
            <?php
            $actv = 0;
            $actvno = 0;
            $pasv = 0;
            $pasvno = 0;
            $cap = 0;

            $inim = $db->select('*')->from('con_comprobante')->where('codigo', $a)->fetch_first();
            $todo = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), a.comprobante FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '3%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

            $act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '1.1%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();

            $actn = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '1.2%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $pas = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '2.1%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $pasn = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '2.2%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $pat = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan like '3%' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            $dem = count($act) + count($actn) - (count($pas) + count($pasn) + count($pat));?>
            <table width="110%" class="gen">
                <tr>
                    <th>Cuenta</th>
                    <th>Concepto</th>
                    <th>Saldo a <?= $inim['fecha'] ?></th>
                    <th>Incremento</th>
                    <th>Disminucion</th>
                    <th>Saldo a <?= date('Y-m-d') ?></th>
                </tr>

                <?php $i = 1;
                foreach ($todo as $tod) {
                    $ini = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante=" . $a . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan = '" . $tod['n_plan'] . "' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
                    $parte = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>" . $a . " AND a.comprobante<" . $b . " AND a.cuenta=c.n_plan AND c.estado=1 AND c.n_plan = '" . $tod['n_plan'] . "' GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch_first();
                    $co = $db->select('*')->from('con_comprobante')->where('codigo', $tod['comprobante'])->fetch_first();
                    ?>
                    <tr>
                        <td><?= $tod['plan_cuenta'] ?></td>
                        <td><?= $co['glosa'] ?></td>
                        <td class="der"><?php echo convercion($ini['SUM(a.haber)'] - $ini['SUM(a.debe)'], 1, $db); $col[1][$i] = $ini['SUM(a.haber)'] - $ini['SUM(a.debe)']; ?></td>
                        <td class="der"><?php echo convercion($parte['SUM(a.haber)'], 1, $db);
                            $col[2][$i] = $parte['SUM(a.haber)']; ?></td>
                        <td class="der"><?php echo convercion($parte['SUM(a.debe)'], 1, $db);
                            $col[3][$i] = $parte['SUM(a.debe)']; ?></td>
                        <td class="der"><?php $fil[$i] = ($ini['SUM(a.haber)'] - $ini['SUM(a.debe)']) + ($parte['SUM(a.haber)'] - $parte['SUM(a.debe)']); ?> <?= convercion($fil[$i], 1, $db) ?></td>
                    </tr>
                    <?php $i++;
                }
                ?>
                <tr>
                    <td></td>
                    <td>Total</td>
                    <td class="der"><?= convercion(array_sum($col[1]), 1, $db) ?></td>
                    <td class="der"><?= convercion(array_sum($col[2]), 1, $db) ?></td>
                    <td class="der"><?= convercion(array_sum($col[3]), 1, $db) ?></td>
                    <td class="der"><?= convercion(array_sum($fil), 1, $db) ?></td>
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
