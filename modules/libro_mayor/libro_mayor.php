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
<style>
.th-table{  background-color: #eee; color: #000; border:1px solid #000;   }
</style>
<?php require_once show_template('header-configured'); ?>
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Libro mayor</strong>
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
                        <a href="?/libro_mayor/imprimir_inicial/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-info"><i
                                class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
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
                <li><a href="?/libro_mayor/libro_mayor<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> -
                        Actual</a></li>
                <?php
                $g++;
                foreach ($comp2 as $cp2) {
                    ?>
                    <li>
                        <a href="?/libro_mayor/libro_mayor/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?>
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
        <?php if ($cuenta) { ?>
            <?php
            foreach ($cuenta as $cuent) {
                ?>
                <div class="table-responsive col-sm-8 col-sm-offset-2">
                <table class="table table-bordered table-condensed table-striped">
                    <tr>
                        <th colspan="5" class="borde">Cuenta: <?= $cuent['plan_cuenta'] ?></th>
                    </tr>
                    <tr class="osc">
                        <th class="th-table" width="15%">Fecha</th>
                        <th class="th-table" width="40%">Descripción</th>
                        <th class="der th-table" width="15%">Debe</th>
                        <th class="der th-table" width="15%">Haber</th>
                        <th class="der th-table" width="15%">Saldo</th>
                    </tr>
                    <?php 
                    $saldo = number_format(0, 2);
                    $saldoDebe = number_format(0, 2);
                    $saldoHaber = number_format(0, 2);

                    $where3 = array(
                        'cuenta' => $cuent['n_plan'],
                        'comprobante>=' => $a,
                        'comprobante<=' => $b
                    );
                    $asiento = $db->select('*')->from('con_asiento')->where($where3)->join('con_comprobante', 'con_comprobante.codigo=con_asiento.comprobante')->fetch();
                    $sw = 1;
                    foreach ($asiento as $asient) {
                        ?>
                        <tr>
                            <td><?= $asient['fecha'] ?></td>
                            <td><?= utf8_decode($asient['glosa']) ?></td>
                            <td class="der">
                                <?php if ($asient['debe'] != 0) {
                                    echo convercion($asient['debe'], 1, $db) . ' ' . $mone['sigla'];
                                } else {
                                    echo number_format(0, 2);
                                } ?></td>
                            <td class="der">
                                <?php if ($asient['haber'] != 0) {
                                    echo convercion($asient['haber'], 1, $db) . ' ' . $mone['sigla'];
                                } else {
                                    echo number_format(0, 2);
                                } ?></td>
                            <td class="der">
                                <?php //if ($cuent['nodo'] == 1 || $cuent['nodo'] == 5 || $cuent['nodo'] == 6) {
                                    $saldo = $saldo + $asient['debe'] - $asient['haber'];
                                //} else {
                                    //$saldo = $saldo - $asient['debe'] + $asient['haber'];
                                //}
                                $saldoDebe = $saldoDebe + $asient['debe'];
                                $saldoHaber = $saldoHaber + $asient['haber'];
                                echo convercion($saldo, 1, $db) . ' ' . $mone['sigla']; ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <th colspan="2"></th>
                        <th class="borde1 der"><?= convercion($saldoDebe, 1, $db) . ' ' . $mone['sigla'] ?></th>
                        <th class="borde1 der"><?= convercion($saldoHaber, 1, $db) . ' ' . $mone['sigla'] ?></th>
                        <th class="borde1 der"></th>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
                        <?php if($saldoDebe>=$saldoHaber){ ?>
                            <th class="borde1 der"><?= convercion($saldo, 1, $db) . ' ' . $mone['sigla'] ?></th>
                            <th class="borde1 der"></th>
                        <?php }else{ ?>
                            <th class="borde1 der"></th>
                            <th class="borde1 der"><?= convercion(($saldo)*-1, 1, $db) . ' ' . $mone['sigla'] ?></th>
                        <?php } ?>
                        <th class="borde1 der"></th>                    
                    </tr>
                </table>
                </div>
                <br/>
            <?php
            }

            ?>

        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>

                <p>No existen almacenes registrados en la base de datos, para crear nuevos almacenes hacer clic en el
                    botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
            </div>
        <?php } ?>
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