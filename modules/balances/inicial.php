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
$permiso_imprimir = in_array('imprimir_inicial', $permisos);
$permiso_excel = in_array('excel_inicial', $permisos);

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

$institucion = $db->select('*')->from('sys_instituciones')->fetch_first();

$cuenta = $db->distinct()->select('con_plan.*')->from('con_asiento')->where($where2)->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->order_by('n_plan', 'asc')->fetch();
$mone = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Apertura</strong>
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
                    <a href="?/balances/excel_inicial/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-success"><span class="glyphicon glyphicon-file"></span><span class="hidden-xs"> Excel</span></a>
                <?php } ?>
                <?php if ($permiso_imprimir) { ?>
                    <a href="?/balances/imprimir_inicial/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
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
            <li><a href="?/balances/inicial/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> -
                    Actual</a></li>
            <?php
            $g++;
            foreach ($comp2 as $cp2) {
                ?>
                <li>
                    <a href="?/balances/inicial/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?>
                        - al - <?= $cp2['fecha'] ?></a></li>
                <?php $g++;
            }
            ?>
        </ul>
    </div>
    <hr/>
    <?php if (isset($_SESSION[permits])) { ?>
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
        <?= $institucion['nombre'] ?>
        <br/>La Paz - Bolivia
        <h3 class="centro">Balance de apertura <br/><?php echo fechaCastellano($date); ?> <?php if ($b > 1000) {
                echo 'a la fecha actual';
            } else {
                echo 'al ' . fechaCastellano($date2);
            } ?>
            <br/>(Expresado en <?= $mon['moneda'] ?>)</h3>

        <div class="uno" style="background: rgba(0,0,0,0.1); padding: 2%; width: 100%;">
            <?php
            $actv = 0;
            $actvno = 0;
            $pasv = 0;
            $pasvno = 0;
            $cap = 0;

            $act = $db->select('*')->from('con_asiento')->where('comprobante', $comp['codigo'])->like('cuenta', '1.1', 'after')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
            $actn = $db->select('*')->from('con_asiento')->where('comprobante', $comp['codigo'])->like('cuenta', '1.2', 'after')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
            $pas = $db->select('*')->from('con_asiento')->where('comprobante', $comp['codigo'])->like('cuenta', '2.1', 'after')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
            $pasn = $db->select('*')->from('con_asiento')->where('comprobante', $comp['codigo'])->like('cuenta', '2.2', 'after')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
            $pat = $db->select('*')->from('con_asiento')->where('comprobante', $comp['codigo'])->like('cuenta', '3', 'after')->join('con_plan', 'con_plan.n_plan=con_asiento.cuenta')->fetch();
            $dem = count($act) + count($actn) - (count($pas) + count($pasn) + count($pat));?>
            <table width="100%" class="tab">
                <tr>
                    <td><b>ACTIVO</b></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;<b>ACTIVO CORRIENTE</b></td>
                </tr>
                <?php foreach ($act as $ac) { ?>
                    <tr>
                        <td>&emsp;<?= $ac['plan_cuenta'] ?></td>
                        <td class="der"><?php $actv = $actv + $ac['debe'] - $ac['haber'];
                            echo convercion($ac['debe'] - $ac['haber'], 1, $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&nbsp;&nbsp;<b>TOTAL ACTIVO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($actv, 1, $db) ?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;<b>ACTIVO NO CORRIENTE</b></td>
                </tr>
                <?php foreach ($actn as $ac) { ?>
                    <tr>
                        <td>&emsp;<?= $ac['plan_cuenta'] ?></td>
                        <td class="der"><?php $actvno = $actvno + $ac['debe'] - $ac['haber'];
                            echo convercion($ac['debe'] - $ac['haber'], 1, $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&nbsp;&nbsp;<b>TOTAL NO ACTIVO CORRIENTE</b></td>
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
                    <td></td>
                    <td class="borde1 centro"><?= convercion($actv + $actvno, 1, $db) . ' ' . $mon['sigla'] ?></td>
                </tr>
            </table>
        </div>
        
        <br>
        
        <div class="uno" style="background: rgba(0,0,0,0.1); padding: 2%; width: 100%;">
            <?php
            ?>
            <table width="100%" class="tab">
                <tr>
                    <td><b>PASIVO</b></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;<b>PASIVO CORRIENTE</b></td>
                </tr>
                <?php foreach ($pas as $ps) { ?>
                    <tr>
                        <td>&emsp;<?= $ps['plan_cuenta'] ?></td>
                        <td class="der"><?php $pasv = $pasv + $ps['haber'] - $ps['debe'];
                            echo convercion($ps['haber'] - $ps['debe'], '1', $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&nbsp;&nbsp;<b>TOTAL PASIVO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($pasv, 1, $db) ?></td>
                </tr>
                <tr>
                    <td>&nbsp;&nbsp;<b>PASIVO NO CORRIENTE</b></td>
                </tr>
                <?php foreach ($pasn as $ps) { ?>
                    <tr>
                        <td>&emsp;<?= $ps['plan_cuenta'] ?></td>
                        <td class="der"><?php $pasvno = $pasvno + $ps['haber'] - $ps['debe'];
                            echo convercion($ps['haber'] - $ps['debe'], 1, $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&nbsp;&nbsp;<b>TOTAL PASIVO NO CORRIENTE</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($pasvno, 1, $db) ?></td>
                </tr>
                <tr>
                    <td><b>PATRIMONIO O CAPITAL</b></td>
                </tr>
                <?php foreach ($pat as $pt) { ?>
                    <tr>
                        <td>&emsp;<?= $pt['plan_cuenta'] ?></td>
                        <td class="der"><?php $cap = $cap + $pt['haber'] - $pt['debe'];
                            echo convercion($pt['haber'] - $pt['debe'], 1, $db); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>&nbsp;&nbsp;<b>TOTAL PATRIMONIO</b></td>
                    <td>
                        <hr/>
                    </td>
                    <td class="der"><?= convercion($cap, 1, $db) ?></td>
                </tr>
                <?php if ($dem > 0) {
                    for ($j = 0; $j < $dem - 2; $j++) {
                        echo '<tr><td><hr/></td></tr>';
                    }
                }?>
                <tr>
                    <td><b>TOTAL PASIVO Y CAPITAL</b></td>
                    <td></td>
                    <td class="borde1 centro"><?= convercion($pasv + $pasvno + $cap, 1, $db) . ' ' . $mon['sigla'] ?></td>
                </tr>
            </table>
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
