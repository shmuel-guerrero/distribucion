<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="<?= icon_panel; ?>"></span>
        <strong>Crear almacén</strong>
    </h3>
</div>
<div class="panel-body">
    <?php if ($permiso_listar) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para regresar al listado de cuentas hacer clic en el siguiente botón:</div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <a href="?/cuentas/mostrar" class="btn btn-primary"><i class="<?= icon_list; ?>"></i><span> Listado</span></a>
            </div>
        </div>
        <hr>
    <?php } ?>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <form method="POST" action="?/cuentas/guardar" class="form-horizontal">
                <div class="form-group">
                    <label for="almacen" class="col-md-3 control-label">Tipo de Cuenta:</label>
                    <div class="col-md-9">
                        <select name="padre1" id="padre1" class="form-control">
                            <option value="1">ACTIVO</option>
                            <option value="2">PASIVO</option>
                            <option value="3">CAPITAL O PATRIMONIO</option>
                            <option value="4">CUENTAS DE INGRESO</option>
                            <option value="5">CUENTAS DE EGRESO</option>
                            <option value="6">CUENTAS DE ORDEN</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Cuenta:</label>
                    <div class="col-md-9">
                        <input type="text" name="n_cuenta" placeholder="1.1" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing=".() "/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono" class="col-md-3 control-label">Descripción:</label>
                    <div class="col-md-9">
                        <input type="text" name="cuenta" placeholder="Nombre de la cuenta" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-allowing="-+,() " data-validation-length="max100"/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="<?= ICON_SUBMIT; ?>"></span>
                            <span>Guardar</span>
                        </button>
                        <button type="reset" class="btn btn-default">
                            <span class="<?= ICON_RESET; ?>"></span>
                            <span>Restablecer</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
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

        $('#telefono').selectize({
            persist: false,
            createOnBlur: true,
            create: true,
            onInitialize: function () {
                $('#telefono').css({
                    display: 'block',
                    left: '-10000px',
                    opacity: '0',
                    position: 'absolute',
                    top: '-10000px'
                });
            },
            onChange: function () {
                $('#telefono').trigger('blur');
            },
            onBlur: function () {
                $('#telefono').trigger('blur');
            }
        });

        $(':reset').on('click', function () {
            $('#telefono')[0].selectize.clear();
        });

        $('.form-control:first').select();
    });
</script>
<?php require_once show_template('footer-configured'); ?>
