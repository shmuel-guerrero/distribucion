<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

$aa = (sizeof($params) > 0) ? $params[0] : 0

?>
<?php require_once show_template('header-error'); ?>
<div class="panel-heading">
    <h3 class="panel-title">
        <span class="glyphicon glyphicon-option-vertical"></span>
        <strong>Crear factura</strong>
    </h3>
</div>
<div class="panel-body">
    <?php if ($permiso_listar) { ?>
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para regresar al listado de cuentas hacer clic en el siguiente botón:</div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <a href="?/cuentas/mostrar" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span><span> Listado</span></a>
            </div>
        </div>
        <hr>
    <?php } ?>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <form action="?/diario/factura_guardar" method="post" class="form-horizontal">
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Fecha:</label>
                    <div class="col-md-9">
                        <input type="date" name="fecha" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Nº DE NIT/CI:</label>
                    <div class="col-md-9">
                        <input type="text" name="nit" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">NOMBRE O RAZÓN SOCIAL DEL COMPRADOR:</label>
                    <div class="col-md-9">
                        <input type="text" name="razon" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Nº DE FACTURA:</label>
                    <div class="col-md-9">
                        <input type="text" name="fac" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Nº DE AUTORIZACIÓN:</label>
                    <div class="col-md-9">
                        <input type="text" name="auto" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">CÓDIGO DE CONTROL:</label>
                    <div class="col-md-9">
                        <input type="text" name="cod" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">TOTAL FACTURA:</label>
                    <div class="col-md-9">
                        <input type="text" name="tot" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">IMPORTES EXENTOS:</label>
                    <div class="col-md-9">
                        <input type="text" name="imp" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">TOTAL I.C.E.:</label>
                    <div class="col-md-9">
                        <input type="text" name="ICE" class="form-control" />
                        <input type="hidden" name="ida" value="<?= $aa ?>"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">TIPO:</label>
                    <div class="col-md-9">
                        <label class="radio-inline">
                            <input type="radio" name="tipo" value="VENTA" > VENTA
                        </label><BR>
                        <label class="radio-inline">
                            <input type="radio" name="tipo" value="COMPRA" > COMPRA
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="<?= ICON_SUBMIT; ?>"></span>
                            <span>Guardar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= JS; ?>/jquery.form-validator.min.js"></script>
<script src="<?= JS; ?>/jquery.form-validator.es.js"></script>
<script src="<?= JS; ?>/selectize.min.js"></script>
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
<?php require_once show_template('footer-sidebar'); ?>


