<?php
// echo 'hola';exit();
// Obtiene los almacenes
$almacenes = $db->select('z.*')->from('inv_almacenes z')->order_by('z.id_almacen')->fetch();

$cuentas = $db->select('*')->from('con_cuenta')->order_by('id_cuenta','asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = true;
$permiso_editar = true;
$permiso_ver = true;
$permiso_eliminar = true;
$permiso_imprimir = true;
$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Cuentas</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $almacenes)) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para agregar nuevos almacenes hacer clic en el siguiente botón: </div>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">
                    <?php if ($permiso_imprimir) { ?>
                        <a href="?/almacenes/imprimir" target="_blank" class="btn btn-info"><span class="glyphicon glyphicon-print"></span><span class="hidden-xs"> Imprimir</span></a>
                    <?php } ?>
                    <?php if ($permiso_crear) { ?>
                        <a href="?/cuentas/crear" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span> Nuevo</span></a>
                    <?php } ?>
                </div>
            </div>
            <hr>
        <?php } ?>
        <?php if (isset($_SESSION[temporary])) { ?>
            <div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><?= $_SESSION[temporary]['title']; ?></strong>
                <p><?= $_SESSION[temporary]['message']; ?></p>
            </div>
            <?php unset($_SESSION[temporary]); ?>
        <?php } ?>
        <?php if ($cuentas) { ?>
            <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                <thead>
                <tr class="active">

                    <th class="text-nowrap">Cuenta</th>
                    <th class="text-nowrap">Descripción</th>
                    <th class="text-nowrap">Tipo</th>
                    <th class="text-nowrap">Clasificación</th>

                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap">Opciones</th>
                    <?php } ?>
                </tr>
                </thead>
                <tfoot>
                <tr class="active">

                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Cuenta</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Principal</th>

                    <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                        <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
                    <?php } ?>
                </tr>
                </tfoot>
                <tbody>
                <?php foreach ($cuentas as $nro => $cuenta) { ?>
                    <tr>
                        <?php $nro + 1; ?>
                        <td class="text-nowrap"><?= escape($cuenta['id_cuenta']); ?></td>
                        <td class="text-nowrap"><?= escape($cuenta['cuenta']); ?></td>
                        <td class="text-nowrap">TITULO</td>
                        <td class="text-nowrap"><?= escape($cuenta['cuenta']); ?></td>
                        <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                            <td class="text-nowrap">

                            </td>
                        <?php } ?>
                    </tr>
                <?php
                    $planes = $db->select('con_plan.*,con_cuenta.cuenta')->from('con_plan')->where('nodo',$cuenta['id_cuenta'])->join('con_cuenta','con_cuenta.id_cuenta=con_plan.nodo')->fetch();
                    foreach ($planes as  $plan) { ?>
                        <tr>
                            <?php $nro + 1; ?>
                            <td class="text-nowrap"><?= escape($plan['n_plan']); ?></td>
                            <td class="text-nowrap"><?= escape($plan['plan_cuenta']); ?></td>
                            <td class="text-nowrap"><?php if($plan['tipo']==1){echo 'TÍTULO';}if($plan['tipo']==2){echo 'CAPITULO';}if($plan['tipo']==3){echo 'GRUPO';}if($plan['tipo']==4){echo 'CUENTA';}if($plan['tipo']>=5){echo 'SUBCUENTA';} ?></td>
                            <td class="text-nowrap"><?= escape($plan['cuenta']); ?></td>
                            <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
                                <td class="text-nowrap">
                                    <?php if ($permiso_editar) { ?>
                                        <a href="?/cuentas/editar/<?= $plan['id_plan']; ?>" data-toggle="tooltip" data-title="Editar cuenta"><span class="glyphicon glyphicon-edit"></span></a>
                                    <?php } ?>
                                    <?php if ($permiso_eliminar) { ?>
                                        <a href="?/cuentas/eliminar/<?= $plan['id_plan']; ?>" data-toggle="tooltip" data-title="Eliminar cuenta" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php
                    }
                } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen almacenes registrados en la base de datos, para crear nuevos almacenes hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
                    if(result){
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