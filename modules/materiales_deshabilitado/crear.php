<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el modelo unidades
$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();

// Obtiene el modelo productos
$productos = $db->from('inv_productos')->order_by('nombre')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear Caja</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de cajas hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<!--<a href="?/materiales/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>-->
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/materiales/guardar" class="form-horizontal" autocomplete="off">
				<div class="form-group">
					<label for="nombre" class="col-md-3 control-label">Nombre:</label>
					<div class="col-md-9">
                        <input type="hidden" value="0" name="id_materiales" data-validation="required">   
						<input type="text" value="" name="nombre" id="nombre" class="form-control" data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º"() ' data-validation-length="max100">
					</div>
				</div>
				<div class="form-group">
					<label for="id_producto" class="col-md-3 control-label">Producto:</label>
					<div class="col-md-9">
						<select name="id_producto" id="id_producto" class="form-control" data-validation="required">
							<option value="">Seleccionar</option>
							<?php foreach ($productos as $elemento) { ?>
							<option value="<?= $elemento['id_producto']; ?>"><?= escape($elemento['nombre']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="id_unidad" class="col-md-3 control-label">Unidad:</label>
					<div class="col-md-9">
						<select name="id_unidad" id="id_unidad" class="form-control" data-validation="required">
							<option value="">Seleccionar</option>
							<?php foreach ($unidades as $elemento) { ?>
							<option value="<?= $elemento['id_unidad']; ?>"><?= escape($elemento['unidad']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
                <div class="form-group">
					<label for="precio" class="col-md-3 control-label">Precio:</label>
					<div class="col-md-9">
						<input type="text" value="1" name="precio" id="precio" class="form-control" data-validation="required number">
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
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript">
$(function () {
    var $fecha = $('#ven_fecha');

    var formato = $('[data-formato]').attr('data-formato');
    $fecha.datetimepicker({
        format: formato
    });


	$.validate({
		modules: 'basic,security'
	});

	$('#nombre').on('keyup', function () {
		$('#nombre_factura').val($.trim($(this).val()));
	});
	
	$('.form-control:first').select();
});

</script>
<?php require_once show_template('footer-advanced'); ?>