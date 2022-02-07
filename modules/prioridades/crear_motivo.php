<?php

// Obtiene los formatos para la fecha

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

$tipos = $db->select('*')->from('gps_noventa_motivos')->fetch();

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear motivos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de motivos hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/clientes/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-6">
			<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
				<thead>
					<tr class="active">
						<th class="text-nowrap">#</th>
						<th class="text-nowrap">Motivos</th>
	                    <th class="text-nowrap">Opciones</th>	          
					</tr>
				</thead>
				<tfoot>
					<tr class="active">
						<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Motivos</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($tipos as $nro => $tipo) { ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap"><?= escape($tipo['motivo']); ?></td>
						<?php 
        					$existe = $db->query("SELECT id_egreso
                            						from inv_egresos
                                                    where motivo_id = ".$tipo['id_motivo']."
                                                    LIMIT 1")->fetch();
                            $existe = count($existe);
    					?>
    					<?php if($existe == 0){?>
    		                <td class="text-nowrap">
    	                        <a href="?/prioridades/eliminar_motivo/<?= $tipo['id_motivo']; ?>" data-toggle="tooltip" data-title="Eliminar motivo" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
    						</td>
						<?php }else{ ?>
						    <td class="text-nowrap">No se puede eliminar</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</tbody>
			</table>
	
		</div>
		<div class="col-sm-6">
			<form method="post" action="?/prioridades/guardar_motivo" class="form-horizontal" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="tipo" class="col-md-3 control-label">Motivo:</label>
                    <div class="col-md-9">
                        <input type="hidden" value="0" name="id_tipo" data-validation="required number">
                        <input type="text" value="" name="tipo" id="tipo" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max200" maxlength="200">
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
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script>
$(function () {
	$.validate({
		modules: 'basic,date,file'
	});

	var table = $('#table').DataFilter({
		filter: true,
		name: 'clientes',
		reports: 'xls|doc|pdf|html'
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
<?php require_once show_template('footer-configured'); ?>