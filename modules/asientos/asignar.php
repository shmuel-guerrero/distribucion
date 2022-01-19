<?php

// Obtiene el id_user
$id_auto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el modelo users
$auto = $db->from('con_asientos_automaticos')->where('id_automatico', $id_auto)->fetch_first();

// Verifica si existe el usuario
if (!$auto) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los empleados
$menus = $db->select('*')->from('con_menus')->fetch();

// Obtiene al actual empleado
$empleado = $db->select('id_empleado, paterno, materno, nombres')->from('sys_empleados')->where('id_empleado', $user['persona_id'])->fetch();

// Adicionamos al empleado a la lista de empleados disponibles
$empleados = array_merge($empleados, $empleado);

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Asignar operación</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de asientos automáticos hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/asientos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<form method="POST" action="?/asientos/actualizar" class="form-horizontal">
				<div class="form-group">
					<label class="col-md-3 control-label">Nombre de asiento:</label>
					<div class="col-md-9">
						<p class="form-control-static"><?= escape($auto['titulo_automatico']); ?></p>
                        <input type="hidden" id="id_auto" name="id_auto" value="<?= $auto['id_automatico'] ?>"/>
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-3 control-label">Menus:</label>
					<div class="col-md-9">
                        <?php $ma = $db->select('*')->from('con_asientos_menus a')->join('con_menus','menu_id = id_menu')->where('automatico_id',$id_auto)->fetch();
                        foreach($ma as $mas){?>
						<b><p class="form-control-static"><?= escape($mas['menu']); ?></p></b>
                        <?php } ?>
					</div>
				</div>
				<div class="form-group">
					<label for="persona_id" class="col-md-3 control-label">Operaciones:</label>
					<div class="col-md-9">
						<select name="persona_id" id="persona_id" class="form-control" data-validation="number" data-validation="true">
							<option value="">Seleccionar</option>
							<?php foreach ($menus as $menu) { ?>
								<option value="<?= $menu['id_menu']; ?>" ><?= escape($menu['menu']); ?></option>
							<?php } ?>
						</select>
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
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
$(function () {
	$.validate();

	$('#persona_id').selectize({
		maxOptions: 6,
		onInitialize: function () {
			$('#persona_id').css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function (value) {
			$('#persona_id').trigger('blur');
		},
		onBlur: function () {
			$('#persona_id').trigger('blur');
		}
	});

	$(':reset').on('click', function () {
		$("#persona_id")[0].selectize.clear();
	});
});
</script>
<?php require_once show_template('footer-advanced'); ?>