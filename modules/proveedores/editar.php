<?php

// Obtiene el id_empleado
$id_cliente = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado
$cliente = $db->select('z.*')->from('inv_proveedores z')->where('z.id_proveedor', $id_cliente)->fetch_first();

if (!$cliente) {
    // Error 404
    require_once not_found();
    exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Editar empleado</strong>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($permiso_listar) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para regresar al listado de empleados hacer clic en el siguiente botón:</div>
                </div>
                <div class="col-xs-12 col-sm-4 text-right">
                    <a href="?/proveedores/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
                </div>
            </div>
            <hr>
        <?php } ?>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <form method="post" action="?/proveedores/guardar" class="form-horizontal">
                    <div class="form-group">
                        <label for="nombres" class="col-md-3 control-label">Nombres:</label>
                        <div class="col-md-9">
                            <input type="hidden" value="<?= $cliente['id_proveedor'] ?>" name="id_cliente" data-validation="required number">
                            <input type="text" value="<?= $cliente['proveedor'] ?>" name="nombres" id="nombres" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing=" " data-validation-length="max200" maxlength="200">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ci" class="col-md-3 control-label">CI/NIT:</label>
                        <div class="col-md-9">
                            <input type="text" value="<?= $cliente['nit'] ?>" name="ci" id="ci" class="form-control" autocomplete="off" data-validation="letternumber length" data-validation-allowing=" " data-validation-length="max50" data-validation-optional="true" maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="direccion" class="col-md-3 control-label">Dirección:</label>
                        <div class="col-md-9">
                            <input type="text" value="<?= $cliente['direccion'] ?>" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="letternumber length" data-validation-allowing=" " data-validation-length="max65" data-validation-optional="true" maxlength="65">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="telefono" class="col-md-3 control-label">Teléfono:</label>
                        <div class="col-md-9">
                            <input type="text" value="<?= $cliente['telefono'] ?>" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="number length" data-validation-length="max50" data-validation-optional="true" maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                <span>Guardar</span>
                            </button>
                            <button type="reset" class="btn btn-default">
                                <span class="glyphicon glyphicon-refresh"></span>
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
    <script src="<?= js; ?>/moment.min.js"></script>
    <script src="<?= js; ?>/moment.es.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
    <script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
    <script src="<?= js; ?>/selectize.min.js"></script>
    <script>
        $(function () {
            $.validate({
                modules: 'basic,date'
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

            $('#fecha_nacimiento').mask('<?= $formato_numeral; ?>').datetimepicker({
                format: '<?= strtoupper($formato_textual); ?>'
            }).on('dp.change', function () {
                $(this).trigger('blur');
            });

            $('.form-control:first').select();
        });
    </script>
<?php require_once show_template('footer-advanced'); ?>

