<?php

// Obtiene los formatos para la fecha

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

$grupos = $db->select('*')->from('inv_clientes_grupos')->fetch();

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Grupo de Clientes </strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de clientes hacer clic en el siguiente botón:</div>
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
						<th class="text-nowrap">Grupo cliente</th>
						<th class="text-nowrap">Descuento</th>
						<th class="text-nowrap">Credito</th>
						<!--<th class="text-nowrap">Permiso</th>-->
						<th class="text-nowrap">Estado</th>
	                    <th class="text-nowrap">Opciones</th>	          
					</tr>
				</thead>
				<tfoot>
					<tr class="active">
						<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Grupo cliente</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Descuento</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Credito</th>
						<!--<th class="text-nowrap text-middle" data-datafilter-filter="true">Permiso</th>-->
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Estado</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Opciones</th>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($grupos as $nro => $grupo) { ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap"><?= escape($grupo['nombre_grupo']); ?></td>
						<td class="text-nowrap"><?= escape($grupo['descuento_grupo']); ?></td>
						<td class="text-nowrap"><?= escape($grupo['credito_grupo']); ?></td>
						<!--<td class="text-nowrap"><?//= escape($grupo['permiso_grupo']); ?></td>-->
						<td class="text-nowrap">
						<?php
							if($grupo['estado_grupo']=='1')
							{
								echo 'ACTIVO'; 
							} 
							 else {
							 	echo 'NO ACTIVO';
							}
						?>
							
						</td>
		                <td class="text-nowrap">
	                        <a href="?/clientes/eliminar_grupo/<?= $grupo['id_cliente_grupo']; ?>" data-toggle="tooltip" data-title="Eliminar grupo" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
	
		</div>
		<div class="col-sm-6">
			<form method="post" action="?/clientes/guardar_grupo" class="form-horizontal" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="grupo" class="col-md-3 control-label">Grupo cliente:</label>
                    <div class="col-md-9">
                        <!--<input type="hidden" value="0" name="id_grupo" data-validation="required number">-->
                        <input type="text" value="" name="grupo" id="grupo" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max100">
					</div>
                </div>
                <div class="form-group">
                    <label for="descuento" class="col-md-3 control-label">Descuento:</label>
                    <div class="col-md-9">
	                  	<input type="text" value="" name="descuento" id="descuento" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max100">
					</div>
                </div>
                <div class="form-group">
                    <label for="credito" class="col-md-3 control-label">Credito:</label>
                    <div class="col-md-9">
                    	<!--<input type="text" value="" name="credito" id="credito" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max100">-->

                    	<select class="form-control" name="credito" id="credito">
                    		<option value="si">SI</option>
                    		<option value="no">NO</option>
                    	</select>

                    </div>
                </div>
                 <!--<div class="form-group">
                    <label for="grupo" class="col-md-3 control-label">Permiso:</label>
                    <div class="col-md-9">
                  		<input type="text" value="" name="permiso" id="permiso" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max100">
                  		 </div>
                </div>-->
                 <div class="form-group">
                    <label for="estado" class="col-md-3 control-label">Estado:</label>
                    <div class="col-md-9">
                    	<!--<input type="text" value="" name="estado" id="estado" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="- " data-validation-length="max100">-->
                    	<select class="form-control" name="estado" id="estado">
                    		<option value="1">ACTIVO</option>
                    		<option value="0">NO ACTIVO</option>
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
		reports: 'excel|word|pdf|html'
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


//Funcion para obtener latitud y longitud
function mostrarUbicacion(position) {
    var latitud = position.coords.latitude; //Obtener latitud
    var longitud = position.coords.longitude; //Obtener longitud
    var div = document.getElementById("atencion");
    $('#atencion').val(latitud+', '+longitud)
    //innerHTML = "<br>Latitud: " + latitud + "<br>Longitud: " + longitud; //Imprime latitud y longitud
    //console.log(latitud);
}

function Excepciones(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            alert('Activa permisos de geolocalizacion');
            break;
        case error.POSITION_UNAVAILABLE:
            alert('Activa localizacion por GPS o Redes .');
            break;
        default:
            alert('ERROR: ' + error.code);
    }
}

</script>
<?php require_once show_template('footer-advanced'); ?>