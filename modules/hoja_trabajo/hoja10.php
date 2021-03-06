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
$permiso_imprimir = in_array('imprimir_hoja10', $permisos);

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

<style type="text/css">
.center{
    text-align: center;
}
</style>

<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Diez columnas</strong>
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
                    <a href="?/hoja_trabajo/imprimir_hoja10/<?= $a ?>/<?= $b ?>" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
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
            <li><a href="?/hoja_trabajo/hoja10/<?= $comp1[$g]['codigo'] ?>/10000"><?= $comp1[$g]['fecha'] ?> -
                    Actual</a></li>
            <?php
            $g++;
            foreach ($comp2 as $cp2) {
                ?>
                <li>
                    <a href="?/hoja_trabajo/hoja10/<?= $comp1[$g]['codigo'] ?>/<?= $cp2['codigo'] ?>"><?= $comp1[$g]['fecha'] ?>
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
    //encontrar datos de la cuenta de apertura
    $where = array(
        'tipo' => 1,
        'codigo>=' => $a,
        'codigo<=' => $b
    );
    $comp = $db->select('*')->from('con_comprobante')->where($where)->fetch_first();
    $saj = $comp['codigo'];
    $date = $comp['fecha'];
    //encontrar datos del cierre de cuenta
    $where2 = array(
        'tipo' => 2,
        'codigo>=' => $a,
        'codigo<=' => $b
    );
    $comp2 = $db->select('*')->from('con_comprobante')->where($where2)->fetch_first();
    $date2 = $comp2['fecha'];

    $mon = $db->select('*')->from('con_tipo_moneda')->where('estado', 1)->fetch_first();
    ?>
    <div>
        Checkcode S.R.L.
        <br/>La Paz - Bolivia
        <h3 class="centro">Hoja de Trabajo (10 Columnas)
            <br/>
            <?php 
            echo fechaCastellano($date); 
            if ($b > 1000) {
                echo 'a la fecha actual';
            } else {
                echo 'al ' . fechaCastellano($date2);
            } ?>
            <br/>(Expresado en <?= $mon['moneda'] ?>)</h3>

        <div>
            <?php
            $deb = 0;
            $hab = 0;
            $ajdeb = 0;
            $ajhab = 0;
            $deu = 0;
            $acr = 0;
            $ajdeu = 0;
            $ajacr = 0;
            $gas = 0;
            $ing = 0;
            $activ = 0;
            $pasiv = 0;
            $s1 = 1;
            $ad1 = 0;
            $ah1 = 0;
            //datos del inicio de apertura
             "SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo
                                FROM con_asiento a, con_plan c 
                                WHERE a.comprobante=" . $a . " AND a.cuenta=c.n_plan AND c.estado=1 
                                GROUP BY c.n_plan 
                                ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC";
             "<br>";
             "SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo
                                FROM con_asiento a, con_plan c 
                                WHERE a.comprobante>" . $a . " AND a.cuenta=c.n_plan AND c.estado=1 AND a.comprobante< " . $b . "  
                                GROUP BY c.n_plan 
                                ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC";

            $act2 = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo 
                                FROM con_asiento a, con_plan c 
                                WHERE a.comprobante=" . $a . " AND a.cuenta=c.n_plan AND c.estado=1 
                                GROUP BY c.n_plan 
                                ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            //datos de ajustes
            $act = $db->query("SELECT c.n_plan,c.plan_cuenta,SUM(a.debe),SUM(a.haber), c.nodo 
                                FROM con_asiento a, con_plan c 
                                WHERE a.comprobante>" . $a . " AND a.cuenta=c.n_plan AND c.estado=1 AND a.comprobante< " . $b . "  
                                GROUP BY c.n_plan 
                                ORDER BY c.nodo ASC,LENGTH(c.n_plan) ASC,c.n_plan ASC")->fetch();
            ?>
            <div class="table-responsive">
            <table align="center" class="table">
                <tr class="osc">
                    <th rowspan="2">Nro<?php echo $b; ?></th>
                    <th rowspan="2">Cuentas</th>
                    <th colspan="2" class="osc center">SUMAS</th>
                    <th colspan="2" class="osc center">SALDOS A AJUSTAR</th>
                    <th colspan="2" class="osc center">AJUSTES</th>
                    <th colspan="2" class="osc center">SALDOS AJUSTADOS</th>
                    <th colspan="2" class="osc center">ESTADO DE RESUL.</th>
                    <th colspan="2" class="osc center">BALANCE GENERAL</th>
                </tr>
                <tr class="osc">
                    <th class="center">Debe</th>
                    <th class="center">Haber</th>
                    <th class="center">Deudor</th>
                    <th class="center">Acreedor</th>
                    <th class="center">Debe</th>
                    <th class="center">Haber</th>
                    <th class="center">Deudor</th>
                    <th class="center">Acreedor</th>
                    <th class="center">Gastos</th>
                    <th class="center">Ingresos</th>
                    <th class="center">Activos</th>
                    <th class="center">Pasivos</th>
                </tr>
                <?php foreach ($act2 as $ac) {
                    $ad1 = 0;
                    $ah1 = 0; ?>
                    <tr>
                    <td><?= $ac['n_plan'] ?></td>
                    <td><?= $ac['plan_cuenta'] ?></td>
                    <td class="der">
                        <?php 
                        if($ac['SUM(a.debe)']!="0"){
                            echo convercion($ac['SUM(a.debe)'], 1, $db);
                            $deb = $deb + $ac['SUM(a.debe)']; 
                        }
                        ?>                            
                    </td>
                    <td class="der">
                        <?php 
                        if($ac['SUM(a.haber)']!="0"){
                            echo convercion($ac['SUM(a.haber)'], 1, $db);
                            $hab = $hab + $ac['SUM(a.haber)']; 
                        }
                        ?>
                    </td>
                    <td class="der"><?php if ($ac['SUM(a.debe)'] > $ac['SUM(a.haber)']) {
                            echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db);
                            $deu = $deu + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                        } else {
                            //echo number_format(0, 2);
                        } ?>                            
                    </td>
                    <td class="der"><?php if ($ac['SUM(a.debe)'] < $ac['SUM(a.haber)']) {
                            echo convercion($ac['SUM(a.haber)'] - $ac['SUM(a.debe)'], 1, $db);
                            $acr = $acr - $ac['SUM(a.debe)'] + $ac['SUM(a.haber)'];
                        } else {
                            //echo number_format(0, 2);
                        } ?>                            
                    </td>
                    <?php $s1 = 1;
                    foreach ($act as $ac2) {
                        if ($ac2['n_plan'] == $ac['n_plan']) {
                            $s1 = 0; ?>
                            <td class="der"><?php echo convercion($ac2['SUM(a.debe)'], 1, $db);
                                $ajdeb = $ajdeb + $ac2['SUM(a.debe)'];
                                $ad1 = $ac2['SUM(a.debe)']; ?></td>
                            <td class="der"><?php echo convercion($ac2['SUM(a.haber)'], 1, $db);
                                $ajhab = $ajhab + $ac2['SUM(a.haber)'];
                                $ah1 = $ac2['SUM(a.haber)']; ?></td>
                        <?php }
                    }
                    if ($s1 == 1) { ?>
                        <td class="der"><?php //echo number_format(0, 2); ?></td>
                        <td class="der"><?php //echo number_format(0, 2); ?></td>
                    <?php
                    }
                    ?>
                    <td class="der">
                        <?php if ($ac['SUM(a.debe)'] + $ad1 > $ac['SUM(a.haber)'] + $ah1) {
                            echo convercion(($ac['SUM(a.debe)'] + $ad1) - ($ac['SUM(a.haber)'] + $ah1), 1, $db);
                            $ajdeu = $ajdeu + $ac['SUM(a.debe)'] + $ad1 - $ac['SUM(a.haber)'] - $ah1;
                        } else {
                            //echo number_format(0, 2);
                        } ?></td>
                    <td class="der">
                        <?php if ($ac['SUM(a.debe)'] + $ad1 < $ac['SUM(a.haber)'] + $ah1) {
                            echo convercion(($ac['SUM(a.haber)'] + $ah1) - ($ac['SUM(a.debe)'] + $ad1), 1, $db);
                            $ajacr = $ajacr - $ac['SUM(a.debe)'] - $ad1 + $ac['SUM(a.haber)'] + $ah1;
                        } else {
                            //echo number_format(0, 2);
                        } ?></td>
                    <td class="der">
                        <?php if ($ac['nodo'] == 5 || $ac['nodo'] == 6) {
                            echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db);
                            $gas = $gas + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                        } else {
                            //echo number_format(0, 2);
                        } ?></td>
                    <td class="der">
                        <?php if ($ac['nodo'] == 4) {
                            echo convercion($ac['SUM(a.haber)'] - $ac['SUM(a.debe)'], 1, $db);
                            $ing = $ing - $ac['SUM(a.debe)'] + $ac['SUM(a.haber)'];
                        } else {
                            //echo number_format(0, 2);
                        } ?></td>
                    <td class="der">
                        <?php if ($ac['nodo'] == 1) {
                            echo convercion($ac['SUM(a.debe)'] + $ad1 - $ac['SUM(a.haber)'] - $ah1, 1, $db);
                            $activ = $activ + $ac['SUM(a.debe)'] + $ad1 - $ac['SUM(a.haber)'] - $ah1;
                        } else {
                            //echo number_format(0, 2);
                        } ?></td>
                    <td class="der">
                        <?php if ($ac['nodo'] == 2 || $ac['nodo'] == 3) {
                            echo convercion($ac['SUM(a.haber)'] + $ah1 - $ac['SUM(a.debe)'] - $ad1, 1, $db);
                            $pasiv = $pasiv - $ac['SUM(a.debe)'] - $ad1 + $ac['SUM(a.haber)'] + $ah1;
                        } else {
                            //echo number_format(0, 2);
                        } ?></td>
                    </tr>
                <?php 
                } //foreach
                ?>
                <tr style="background: rgba(0,0,0,0.1);">
                    <td colspan="2"></td>
                    <td class="der"><?= convercion($deb, 1, $db) ?></td>
                    <td class="der"><?= convercion($hab, 1, $db) ?></td>
                    <td class="der"><?= convercion($deu, 1, $db) ?></td>
                    <td class="der"><?= convercion($acr, 1, $db) ?></td>
                </tr>
                <?php
                foreach ($act as $ac) {
                    $sw = 0;
                    foreach ($act2 as $ac2) {
                        //SI NO EXISTE EN ESTE LISTADO, LO PROCESAMOS AHORA
                        if ($ac2['n_plan'] == $ac['n_plan']) {
                            $sw = 1;
                        }
                    }
                    if ($sw == 0) {
                        ?>
                        <tr>
                        <td><?= $ac['n_plan'] ?></td>
                        <td><?= $ac['plan_cuenta'] ?></td>
                        <td class="der">
                            <?php 
                             if($ac['SUM(a.debe)']!=0){
                                convercion($ac['SUM(a.debe)'], 1, $db);
                                $deb = $deb + $ac['SUM(a.debe)']; 
                            }
                            ?>                                
                        </td>
                        <td class="der">
                            <?php
                            if($ac['SUM(a.haber)'] !=0){
                            convercion($ac['SUM(a.haber)'], 1, $db);
                            $hab = $hab + $ac['SUM(a.haber)']; 
                            }
                            ?>                            
                        </td>

                        <td class="der">
                            <?php 
                            if ($ac['SUM(a.debe)'] > $ac['SUM(a.haber)']) {
                                convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db);
                                $deu = $deu + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                            } else {
                                //number_format(0, 2);
                            } 
                            ?>                                
                        </td>
                        <td class="der"><?php if ($ac['SUM(a.debe)'] < $ac['SUM(a.haber)']) {
                                convercion($ac['SUM(a.haber)'] - $ac['SUM(a.debe)'], 1, $db);
                                $acr = $acr - $ac['SUM(a.debe)'] + $ac['SUM(a.haber)'];
                            } else {
                                //number_format(0, 2);
                            } ?></td>
                        <td class="der"><?php 
                            if($ac['SUM(a.debe)'] !=0){
                                echo convercion($ac['SUM(a.debe)'], 1, $db);
                                $ajdeb = $ajdeb + $ac['SUM(a.debe)']; 
                            }
                            ?>                                
                        </td>
                        <td class="der"><?php 
                            if($ac['SUM(a.haber)'] !=0){
                                echo convercion($ac['SUM(a.haber)'], 1, $db);
                                $ajhab = $ajhab + $ac['SUM(a.haber)']; 
                            }
                            ?>                                
                        </td>
                        <td class="der"><?php if ($ac['SUM(a.debe)'] > $ac['SUM(a.haber)']) {
                                echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db);
                                $ajdeu = $ajdeu + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                            } else {
                                //echo number_format(0, 2);
                            } ?></td>
                        <td class="der"><?php if ($ac['SUM(a.debe)'] < $ac['SUM(a.haber)']) {
                                echo convercion($ac['SUM(a.haber)'] - $ac['SUM(a.debe)'], 1, $db);
                                $ajacr = $ajacr - $ac['SUM(a.debe)'] + $ac['SUM(a.haber)'];
                            } else {
                                //echo number_format(0, 2);
                            } ?></td>
                        <td class="der"><?php if ($ac['nodo'] == 5 || $ac['nodo'] == 6) {
                                echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db);
                                $gas = $gas + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                            } else {
                                //echo number_format(0, 2);
                            } ?></td>
                        <td class="der"><?php if ($ac['nodo'] == 4) {
                                echo convercion($ac['SUM(a.haber)'] - $ac['SUM(a.debe)'], 1, $db);
                                $ing = $ing - $ac['SUM(a.debe)'] + $ac['SUM(a.haber)'];
                            } else {
                                //echo number_format(0, 2);
                            } ?></td>
                        <td class="der"><?php if ($ac['nodo'] == 1) {
                                echo convercion($ac['SUM(a.debe)'] - $ac['SUM(a.haber)'], 1, $db);
                                $activ = $activ + $ac['SUM(a.debe)'] - $ac['SUM(a.haber)'];
                            } else {
                                //echo number_format(0, 2);
                            } ?></td>
                        <td class="der"><?php if ($ac['nodo'] == 2 || $ac['nodo'] == 3) {
                                echo convercion($ac['SUM(a.haber)'] - $ac['SUM(a.debe)'], 1, $db);
                                $pasiv = $pasiv - $ac['SUM(a.debe)'] + $ac['SUM(a.haber)'];
                            } else {
                                //echo number_format(0, 2);
                            } ?></td></tr><?php }
                } ?>
                <tr style="background: rgba(0,0,0,0.1);">
                    <td></td>
                    <td>TOTALES</td>
                    <td class="der"></td>
                    <td class="der"></td>
                    <td class="der"></td>
                    <td class="der"></td>
                    <td class="der"><?php if($ajdeb!=0){ echo convercion($ajdeb, 1, $db); } ?></td>
                    <td class="der"><?php if($ajhab!=0){ echo convercion($ajhab, 1, $db); } ?></td>
                    <td class="der"><?php if($ajdeu!=0){ echo convercion($ajdeu, 1, $db); } ?></td>
                    <td class="der"><?php if($ajacr!=0){ echo convercion($ajacr, 1, $db); } ?></td>
                    <td class="der"><?php if($gas!=0){ echo convercion($gas, 1, $db); } ?></td>
                    <td class="der"><?php if($ing!=0){ echo convercion($ing, 1, $db); } ?></td>
                    <td class="der"><?php if($activ!=0){ echo convercion($activ, 1, $db); } ?></td>
                    <td class="der"><?php if($pasiv!=0){ echo convercion($pasiv, 1, $db); } ?></td>
                </tr>
                <tr style="background: rgba(0,0,0,0.15);">
                    <td></td>
                    <td>UTILIDAD DEL PERIODO</td>
                    <td colspan="8"></td>
                    <td class="der"><?= convercion($ing - $gas, 1, $db); ?></td>
                    <td class="der"></td>
                    <td class="der"></td>
                    <td class="der"><?= convercion($ing - $gas, 1, $db); ?></td>
                </tr>
                <tr style="background: rgba(0,0,0,0.18);">
                    <td colspan="10"></td>
                    <td class="der"><?= convercion($gas + $ing - $gas, 1, $db); ?></td>
                    <td class="der"><?= convercion($ing, 1, $db); ?></td>
                    <td class="der"><?= convercion($activ, 1, $db); ?></td>
                    <td class="der"><?= convercion($pasiv + $ing - $gas, 1, $db); ?></td>
                </tr>
            </table>
            </div>
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
