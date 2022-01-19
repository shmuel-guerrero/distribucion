<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);


$aa = $_POST['a'];
$factura = $db->select('*')->from('con_factura')->where('id_factura',$aa)->fetch_first();
?>

<div class="panel-body">
    <div class="row">
        <div>
            <form action="?/diario/factura_guardar" method="post" class="form-horizontal">
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">Fecha:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['fecha_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">Nº DE NIT/CI:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['nit_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">NOMBRE O RAZÓN SOCIAL DEL COMPRADOR:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['nombre_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">Nº DE FACTURA:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['nro_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">Nº DE AUTORIZACIÓN:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['autorizacion_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">CÓDIGO DE CONTROL:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['codigo_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">TOTAL FACTURA:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['total_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">IMPORTES EXENTOS:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['importes_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">TOTAL I.C.E.:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['ice_f'] ?></label>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-6 text-right">TIPO:</label>
                    <label for="direccion" class="col-md-6 "><?= $factura['tipo_f'] ?></label>
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


