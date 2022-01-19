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
$permiso_imprimir = in_array('imprimir_sumas', $permisos);

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
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Sumas y saldos</strong>
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
                    <a href="?/balances/imprimir_sumas/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
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
            <li><a href="?/balances/sumas/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> -
                    Actual</a></li>
            <?php
            $g++;
            foreach ($comp2 as $cp2) {
                ?>
                <li>
                    <a href="?/balances/sumas/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?>
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
    ?>
    <div class="col-sm-8 col-sm-offset-2">
        Checkcode S.R.L.
        <br/>La Paz - Bolivia
        <h3 class="centro">Balance de Sumas y Saldos <br/><?php echo fechaCastellano($date); ?> <?php if($b>1000){echo 'a la fecha actual';}else{echo 'al '.fechaCastellano($date2);} ?>
            <br/>(Expresado en <?= $mon['moneda'] ?>)</h3>
        <div class="table-responsive">
            <?php
            $deb=0;
            $hab=0;
            $deu=0;
            $acr=0;

            $act=$db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber) FROM con_asiento a,con_plan c WHERE a.comprobante>=".$a." AND a.comprobante< ".$b." AND a.cuenta=c.n_plan AND c.estado=1 GROUP BY c.n_plan ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            ?>
            <table class="table">
                <tr><th colspan="2"></th><th colspan="2" class="osc centro">SUMAS</th><th colspan="2" class="osc centro">SALDOS</th></tr>
                <tr class="osc"><th>Nro</th><th>Cuentas</th><th class="der">Debe</th><th class="der">Haber</th><th class="der">Deudor</th><th class="der">Acreedor</th></tr>
                <?php foreach($act as $ac){ ?>
                <tr>
                    <td><?= $ac['n_plan'] ?></td>
                    <td><?= $ac['plan_cuenta'] ?></td>
                    <td class="der"><?php if($ac['SUM(a.debe)']!=0){ echo convercion($ac['SUM(a.debe)'],1,$db); $deb=$deb+$ac['SUM(a.debe)']; } ?></td>
                    <td class="der"><?php if($ac['SUM(a.haber)']!=0){ echo convercion($ac['SUM(a.haber)'],1,$db); $hab=$hab+$ac['SUM(a.haber)']; } ?></td>
                    <td class="der">
                    <?php if($ac['SUM(a.debe)']>$ac['SUM(a.haber)']){
                        echo convercion($ac['SUM(a.debe)']-$ac['SUM(a.haber)'],1,$db);
                        $deu=$deu+$ac['SUM(a.debe)']-$ac['SUM(a.haber)'];
                    }else{
                        //echo number_format(0,2);
                    } ?></td>
                    <td class="der">
                    <?php if($ac['SUM(a.debe)']<$ac['SUM(a.haber)']){
                        echo convercion($ac['SUM(a.haber)']-$ac['SUM(a.debe)'],1,$db);
                        $acr=$acr-$ac['SUM(a.debe)']+$ac['SUM(a.haber)'];
                    }else{
                        //echo number_format(0,2);
                    } ?></td>
                    <?php } ?>
                <tr><td colspan="2"></td>
                    <td class="der borde1"><?= convercion($deb,1,$db) ?></td>
                    <td class="der borde1"><?= convercion($hab,1,$db) ?></td>
                    <td class="der borde1"><?= convercion($deu,1,$db) ?></td>
                    <td class="der borde1"><?= convercion($acr,1,$db) ?></td></tr>
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
            reports: 'excel|word|pdf|html'
        });
        <?php } ?>
    });
</script>
<?php require_once show_template('footer-advanced'); ?>
