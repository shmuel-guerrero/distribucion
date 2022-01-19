<?php
$a = (sizeof($params) > 0) ? $params[0] : 0;

$cuent = $db->select('*')->from('con_cuenta')->fetch();
$plan2 = $db->select('*')->from('con_plan')->where('id_plan', $a)->fetch_first();
// Obtiene el id_almacen


// Verifica si existe el almacén
if (!$a) {
    // Error 404
    require_once not_found();
    exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);
?>
<?php require_once show_template('header-advanced'); ?>
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="<?= icon_panel; ?>"></span>
            <strong>Editar cuenta</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
            <div class="row">
                <div class="col-sm-7 col-md-6 hidden-xs">
                    <div class="text-label">Para realizar una acción hacer clic en los botones:</div>
                </div>
                <div class="col-xs-12 col-sm-5 col-md-6 text-right">
                    <?php if ($permiso_crear) { ?>
                        <a href="?/cuentas/crear" class="btn btn-success">
                            <span class="glyphicon glyphicon-plus"></span><span
                                class="hidden-xs hidden-sm"> Nuevo</span></a>
                    <?php } ?>
                    <?php if ($permiso_eliminar) { ?>
                        <a href="?/cuentas/eliminar/<?= $a; ?>" class="btn btn-danger"
                           data-eliminar="true"><span class="glyphicon glyphicon-trash"></span><span class="hidden-xs hidden-sm"> Eliminar</span></a>
                    <?php } ?>
                    <?php if ($permiso_listar) { ?>
                        <a href="?/cuentas/mostrar" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span><span
                                class="hidden-xs"> Listado</span></a>
                    <?php } ?>
                </div>
            </div>
            <hr>
        <?php } ?>
        <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <form action="?/cuentas/guardar" method="post" class="form-horizontal">
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Tipo de Cuenta:</label>

                    <div class="col-md-9">
                        <select name="padre1" id="padre1" class="form-control">
                            <?php foreach ($cuent as $rs1) {
                                if ($plan2['nodo'] == $rs1['n_cuenta']) {
                                    ?>
                                    <option value="<?= $rs1['n_cuenta'] ?>" selected><?= $rs1['cuenta'] ?></option>
                                <?php } else { ?>
                                    <option value="<?= $rs1['n_cuenta'] ?>"><?= $rs1['cuenta'] ?></option>
                                <?php
                                }
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Cuenta:</label>

                    <div class="col-md-9">
                        <input type="hidden" name="id_plan" value="<?= $a ?>"/>
                        <input type="text" name="n_cuenta" placeholder="1.1" value="<?= $plan2['n_plan']; ?>"
                               class="form-control" autocomplete="off" data-validation="required letternumber"
                               data-validation-allowing="(). "/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Descripción:</label>

                    <div class="col-md-9">
                        <input type="text" name="cuenta" placeholder="Nombre del plan de cuenta"
                               value="<?= $plan2['plan_cuenta']; ?>" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Actividad:</label>

                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio"
                                   name="ajuste" <?php if ($plan2['actividadc'] == 0 || $plan2['actividadc'] > 9) {
                                echo 'checked';
                            } ?> value="0"> Ninguno
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" <?php if ($plan2['actividadc'] == 1) {
                                echo 'checked';
                            } ?> value="1"> Operación
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" <?php if ($plan2['actividadc'] == 2) {
                                echo 'checked';
                            } ?> value="2"> Inversión
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ajuste" <?php if ($plan2['actividadc'] == 3) {
                                echo 'checked';
                            } ?> value="3"> financiamiento
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Ganancia o perdida:</label>

                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio"
                                   name="utilidad" <?php if ($plan2['actividadc'] != 10 || $plan2['actividadc'] != 11) {
                                echo 'checked';
                            } ?> value="0"> Ninguno
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="utilidad" <?php if ($plan2['actividadc'] == 10) {
                                echo 'checked';
                            } ?> value="10"> Utilidad
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="utilidad" <?php if ($plan2['actividadc'] == 11) {
                                echo 'checked';
                            } ?> value="11"> Perdida
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Partida virtual:</label>

                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio" name="virtual" <?php if ($plan2['actividadc'] != 20) {
                                echo 'checked';
                            } ?> value="0"> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="virtual" <?php if ($plan2['actividadc'] == 20) {
                                echo 'checked';
                            } ?> value="20"> Si
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Saldo de efectivo:</label>

                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio" name="efectivo" <?php if ($plan2['actividadc'] != 30) {
                                echo 'checked';
                            } ?> value="0"> No
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="efectivo" <?php if ($plan2['actividadc'] == 30) {
                                echo 'checked';
                            } ?> value="30"> Si
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="glyphicon glyphicon-save"></span>
                            <span>Guardar</span>
                        </button>

                    </div>
                </div>

        </div>
        </form>
    </div>
</div>

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
    $(function () {
        $.validate({
            modules: 'basic'
        });

        $(':reset').on('click', function () {
            $('#telefono')[0].selectize.clear();
        });

        $('.form-control:first').select();

        <?php if ($permiso_eliminar) { ?>
        $('[data-eliminar]').on('click', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            bootbox.confirm('Está seguro que desea eliminar el almacén?', function (result) {
                if (result) {
                    window.location = url;
                }
            });
        });
        <?php } ?>
    });
</script>
<?php require_once show_template('footer-advanced'); ?>



