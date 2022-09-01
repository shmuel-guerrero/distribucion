<?php

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Sucursala los permisos en variables
$permiso_listar = in_array('ingresos_listar', $permisos);

// Obtiene el numero de comprobante

$comprobante = $db->query("select ifnull(max(nro_comprobante), 0) + 1 as nro_comprobante from caj_movimientos where tipo = 'i'")->fetch_first();
$sucursales = $db->query("select id_institucion as id_sucursal, nombre as sucursal from sys_instituciones")->fetch();

$nro_comprobante = $comprobante['nro_comprobante'];

$empleados = $db->query("select * from sys_empleados")->fetch();

$us = $_user['persona_id'];
$usuario = $db->query("select * from sys_empleados where id_empleado = $us")->fetch_first();

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Crear nuevo ingreso</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de ingresos hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/movimientos/ingresos_listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Listado de ingresos</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<!--<form method="post" action="?/movimientos/ingresos_guardar" class="form-horizontal" autocomplete="off">-->
			<form id="formulario" class="form-horizontal" data-locked="false">
				<div class="form-group">
					<label for="recibido" class="col-md-3 control-label">Sucursal:</label>
					<div class="col-md-9">
						<select id="id_sucursal" name="id_sucursal" class="form-control text-uppercase" data-validation="required" data-validation-allowing="-+./&()">
						    <option value="" >Seleccione un sucursal</option>
						    <?php foreach($sucursales as $nro => $sucursal){ ?>
						        <option value="<?= escape($sucursal['id_sucursal']); ?>"><?= upper($sucursal['sucursal']); ?></option>
						    <?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="nro_comprobante" class="col-md-3 control-label">Número de comprobante:</label>
					<div class="col-md-9">
						<input type="hidden" value="0" name="id_movimiento" data-validation="required">
						<input type="hidden" value="<?= date($_institution['formato']); ?>" name="fecha_movimiento" data-validation="required">
						<input type="hidden" value="<?= date('H:i:s'); ?>" name="hora_movimiento" data-validation="required">
						<input type="text" value="<?= $nro_comprobante; ?>" name="nro_comprobante" readonly id="nro_comprobante" class="form-control" data-validation="required number">
					</div>
				</div>
				<div class="form-group">
					<label for="monto" class="col-md-3 control-label">Monto <?= $moneda; ?>:</label>
					<div class="col-md-9">
						<input type="text" value="" name="monto" id="monto" maxlength="10" class="form-control" data-validation="required number" data-validation-allowing="float" onchange="isDecimal(this)"  autofocus="autofocus">
					</div>
				</div>
				<div class="form-group">
					<label for="concepto" class="col-md-3 control-label">Por concepto de:</label>
					<div class="col-md-9">
						<textarea name="concepto" id="concepto" maxlength="65" class="form-control" data-validation="required letternumber" data-validation-allowing="+-/.,:;#()\n "></textarea>
					</div>
				</div>
				<div class="form-group">
					<label for="autorizado" class="col-md-3 control-label">Autorizado por:</label>
					<div class="col-md-9">
						<input type="text" value= "<?= upper($usuario['nombres'].' '.$usuario['paterno'].' '.$usuario['materno']); ?>" class="form-control" readonly></input>
						<input type="hidden"name="id_empleado_a" id="id_empleado_a" value= "<?= $_user['persona_id']; ?>" class="form-control" ></input>
					</div>
				</div>
				<div class="form-group">
					<label for="recibido" class="col-md-3 control-label">Recibido por:</label>
					<div class="col-md-9">
						<select id="id_empleado_r" name="id_empleado_r" class="form-control text-uppercase" data-validation="required" data-validation-allowing="-+./&()">
						    <option value="" >Seleccione a un empleado</option>
						    <?php foreach($empleados as $nro => $empleado){ ?>
						        <option value="<?= escape($empleado['id_empleado']); ?>"><?= upper($empleado['nombres'].' '.$empleado['paterno'].' '.$empleado['materno']); ?></option>
						    <?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="observacion" class="col-md-3 control-label">Observación:</label>
					<div class="col-md-9">
						<textarea name="observacion" id="observacion" maxlength="65" class="form-control" data-validation="letternumber" data-validation-allowing="+-/.,:;#()\n " data-validation-optional="true"></textarea>
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
    var $formulario = $('#formulario');

    $.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function (form) {
			guardar_ingreso(form);
		}
	});
	
	$formulario.on('submit', function (e) {
		e.preventDefault();
	});
});

function guardar_ingreso(form) {
	$form = $(form);
	if ($form.data('locked') != undefined && !$form.data('locked')) {
		var data = $form.serialize();
		$('#loader').fadeIn(100);
		
		$.ajax({
		    type: 'post',
		    dataType: 'json',
		    url: '?/movimientos/ingresos_guardar',
		    data: data,
		    beforeSend: function() {
		    	$form.data('locked', true);
		 	}
		}).done(function (movimiento_id) {
		    if (movimiento_id) {
		        $.notify({
		            message: 'El registro del ingreso fue realizado satisfactoriamente.'
		        }, {
		            type: 'success'
		        });
		        imprimir_nota(movimiento_id);
		        $form.data('locked', false);
		    } else {
		        $('#loader').fadeOut(100);
		        $.notify({
		            message: 'Ocurri&oacute un problema en el proceso, no se puedo obtener los datos del ingreso, verifique si el ingreso se guard&oacute parcialmente.'
		        }, {
		            type: 'danger'
		        });
		    	$form.data('locked', false);
		    }
		
		}).fail(function (e) {
			console.log(e);
		    $('#loader').fadeOut(100);
		    $.notify({
		        message: 'Ocurri&oacute un problema en el proceso, no se puedo obtener los datos del ingreso, verifique si el ingreso se guard&oacute parcialmente.'
		    }, {
		        type: 'danger'
		    });
		    $form.data('locked', false);
		});
	}
}

function imprimir_nota(movimiento_id) {
	window.open('?/movimientos/ingresos_imprimir/' + movimiento_id, '_blank');
	window.location.reload();
}

</script>

<script>
	//@etysoft validamos el input peso y retornamos un decimal de configuración
	function isDecimal(e) {
		let decimal = e.value;
		if (decimal !== '') {
			e.value = (isNaN(decimal) || decimal <= 0) ? _toFixed(parseFloat(0)) : _toFixed(parseFloat(e.value));
		} else {
			e.value = 0;
		}
	}
</script>
<?php require_once show_template('footer-configured'); ?>