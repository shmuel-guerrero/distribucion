<?php

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear unidad</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de unidades hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/unidades/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="post" action="?/unidades/guardar" class="form-horizontal">
				<div class="form-group">
					<label for="unidad" class="col-md-3 control-label">Unidad:</label>
					<div class="col-md-9">
						<input type="hidden" value="0" name="id_unidad" data-validation="required">
						<input type="text" value="" name="unidad" id="unidad" class="form-control" data-validation="server"  data-validation-url="?/unidades/validar"  data-validation-length="max50" maxlength="50" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-.() ">
					</div>
				</div>
				<div class="form-group">
					<label for="sigla" class="col-md-3 control-label">Sigla:</label>
					<div class="col-md-9">
						<input type="text" value="" name="sigla" id="sigla" class="form-control" autocomplete="off" data-validation="required letternumber" data-validation-allowing="-.">
					</div>
				</div>
				<div class="form-group">
					<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
					<div class="col-md-9">
						<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#()\n " data-validation-optional="true"></textarea>
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
<script>
$(function () {
	$.validate({
		modules: 'basic'
	});
	
	$('.form-control:first').select();
});

$("#unidad").on('keyup', function() {
	this.value = this.value.replace(/[^A-Za-z0-9 ]/g,'');			
});

$("#sigla").on('keyup', function() {
	this.value = this.value.replace(/[^A-Za-z0-9$]/g,'');			
});



</script>
<?php require_once show_template('footer-configured'); ?>