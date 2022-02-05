<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
$ventas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
	->from('inv_egresos i')
	->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
	->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
	->where('i.tipo', 'Venta')
	->where('i.nro_autorizacion !=', '')
	// ->where('i.codigo_control!=', '')
	// ->where('i.codigo_control', '')
	//->where('i.nro_autorizacion!=', 0)
	->where('i.fecha_egreso >= ', $fecha_inicial)
	->where('i.fecha_egreso <= ', $fecha_final)
	->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();

// echo $db->last_query(); die();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('manuales_ver', $permisos);
$permiso_eliminar = in_array('manuales_eliminar', $permisos);
$permiso_anular = in_array('activar_manuales', $permisos);
$permiso_cambiar = true;
$permiso_editar = in_array('manuales_editar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<style>
	.table-xs tbody {
		font-size: 12px;
	}

	.width-sm {
		min-width: 150px;
	}

	.width-md {
		min-width: 200px;
	}

	.width-lg {
		min-width: 250px;
	}
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Listado de ventas manuales </strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar) { ?>
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para cambiar la fecha hacer clic en el siguiente botón: </div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<button class="btn btn-primary" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class=""> Cambiar fecha</span></button>
			</div>
		</div>
		<hr>
	<?php } ?>
	<?php if ($ventas) { ?>
		<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
			<thead>
				<tr class="active">
					<th class="text-nowrap">#</th>
					<th class="text-nowrap">Fecha</th>
					<th class="text-nowrap">Tipo</th>
					<th class="text-nowrap">Cliente</th>
					<th class="text-nowrap">NIT/CI</th>
					<th class="text-nowrap">Nro. Factura</th>
					<th class="text-nowrap">Importe total <?= escape($moneda); ?></th>
					<th class="text-nowrap">Nro. Registros</th>
					<th class="text-nowrap">Almacen</th>
					<th class="text-nowrap">Empleado</th>
					<th class="text-nowrap">Estado</th>
					<?php if ($permiso_ver || $permiso_eliminar || $permiso_anular) { ?>
						<th class="text-nowrap">Opciones</th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr class="active">
					<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Nro. Factura</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Importe total <?= escape($moneda); ?></th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Nro. Registros</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Almacen</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Estado</th>
					<?php if ($permiso_ver || $permiso_eliminar || $permiso_anular) { ?>
						<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
					<?php } ?>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($ventas as $nro => $venta) { ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($venta['hora_egreso']); ?></small></td>
						<td class="text-nowrap"><?= escape($venta['tipo'] . ' manual'); ?></td>
						<td class="text-nowrap"><?= escape($venta['nombre_cliente']); ?></td>
						<td class="text-nowrap"><?= escape($venta['nit_ci']); ?></td>
						<td class="text-nowrap text-right"><?= escape($venta['nro_factura']); ?></td>
						<td class="text-nowrap text-right"><?= escape($venta['monto_total']); ?></td>
						<td class="text-nowrap text-right"><?= escape($venta['nro_registros']); ?></td>
						<td class="text-nowrap"><?= escape($venta['almacen']); ?></td>
						<td class="width-md text-uppercase"><?= escape($venta['nombres'] . ' ' . $venta['paterno'] . ' ' . $venta['materno']); ?></td>
						<?php $estadoAnulado = $venta['anulado'] == 0 ? true : false ?>
						<td class="text-nowrap text-center  text-uppercase h5 <?= (($venta['anulado'] == 0)? 'text-primary' : 'text-danger') ?>"><?= ($estadoAnulado ? '<span class="text-info">Activo</span>' : '<span class="text-danger">Anulado</span>'); ?></td>
						<td class="text-nowrap">
							<?php if ($permiso_ver) { ?>
								<a href="?/operaciones/manuales_ver/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" style="margin-right: 5px" data-title="Ver detalle de venta"><i class="glyphicon glyphicon-list-alt"></i></a>
							<?php } ?>
							<?php if ($permiso_editar /* || $venta['fecha_egreso'] == date('Y-m-d') */) { ?>
								<a href="?/operaciones/manuales_editar/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" style="margin-right: 5px" data-title="Modificar venta"><i class="glyphicon glyphicon-edit"></i></a>
							<?php } ?>
							<?php $masUnMesdate = date("Y-m-d", strtotime($venta['fecha_egreso'] . "+ 1 month")); ?>
							<?php if ($permiso_anular && strtotime(date('Y-m-d')) <= strtotime($masUnMesdate)) { ?>
								<a href="?/operaciones/activar_manuales/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" style="margin-right: 5px" data-title="<?= $estadoAnulado ? 'Anular' : 'Activar' ?> (puede <?= $estadoAnulado ? 'anular' : 'activar' ?> venta hasta el <?= $masUnMesdate; ?>)."  data-activar-producto='true'><i class="text-<?= $estadoAnulado ? 'info' : 'warning' ?> glyphicon glyphicon-<?= $estadoAnulado ? 'remove-circle' : 'ok-circle' ?>"></i></a>
							<?php } ?>
							<?php if ($permiso_eliminar && $venta['anulado'] == 1) { ?>
								<a href="?/operaciones/manuales_eliminar/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" style="margin-right: 5px" data-title="Eliminar venta" data-eliminar="true"><i class="glyphicon glyphicon-trash text-danger"></i></a>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		<div class="alert alert-danger">
			<strong>Advertencia!</strong>
			<p>No existen ventas electrónicas registrados en la base de datos.</p>
		</div>
	<?php } ?>
</div>

<!-- Inicio modal fecha -->
<?php if ($permiso_cambiar) { ?>
	<div id="modal_fecha" class="modal fade">
		<div class="modal-dialog">
			<form id="form_fecha" class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Cambiar fecha</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="inicial_fecha">Fecha inicial:</label>
								<input type="text" name="inicial" value="<?= ($fecha_inicial != $gestion_base) ? date_decode($fecha_inicial, $_institution['formato']) : ''; ?>" id="inicial_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="final_fecha">Fecha final:</label>
								<input type="text" name="final" value="<?= ($fecha_final != $gestion_limite) ? date_decode($fecha_final, $_institution['formato']) : ''; ?>" id="final_fecha" class="form-control" autocomplete="off" data-validation="date" data-validation-format="<?= $formato_textual; ?>" data-validation-optional="true">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" data-aceptar="true">
						<span class="glyphicon glyphicon-ok"></span>
						<span>Aceptar</span>
					</button>
					<button type="button" class="btn btn-default" data-cancelar="true">
						<span class="glyphicon glyphicon-remove"></span>
						<span>Cancelar</span>
					</button>
				</div>
			</form>
		</div>
	</div>
<?php } ?>
<!-- Fin modal fecha -->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/sweetalert2.all.min.js"></script>
<script>
	$(function() {
		<?php if ($permiso_eliminar) { ?>
			$('[data-eliminar]').on('click', function(e) {
				e.preventDefault();
				var url = $(this).attr('href');
				Swal.fire({
					title: 'ESTA SEGURO DE REALIZAR ACCIÓN ELIMINAR?',
					width: 800,
					html: "<h5 class='text-danger'>Si el movimiento tiene credito tambien se eliminara estos registros!</h5>",
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d33',
					cancelButtonColor: '#3085d6',
					cancelButtonText: 'CANCELAR',
					confirmButtonText: 'SI, ELIMINAR!'
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire(
							'Eliminado!',
							'Losregistro fueron eliminados.',
							'success'
						);
						window.location = url;
					}
				});
			});
		<?php } ?>


		$('[data-activar-producto]').on('click', function(e) {
			e.preventDefault();
			var url = $(this).attr('href');
			 bootbox.confirm('Está seguro que desea anular ó activar el movimiento y todo su detalle?', function(result) {
				if (result) {
					window.location = url;
				}
			}); 
		});

		<?php if ($permiso_cambiar) { ?>
			var formato = $('[data-formato]').attr('data-formato');
			var mascara = $('[data-mascara]').attr('data-mascara');
			var gestion = $('[data-gestion]').attr('data-gestion');
			var $inicial_fecha = $('#inicial_fecha');
			var $final_fecha = $('#final_fecha');

			$.validate({
				form: '#form_fecha',
				modules: 'date',
				onSuccess: function() {
					var inicial_fecha = $.trim($('#inicial_fecha').val());
					var final_fecha = $.trim($('#final_fecha').val());
					var vacio = gestion.replace(new RegExp('9', 'g'), '0');

					inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
					inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
					final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
					final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
					vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
					vacio = vacio.replace(new RegExp('/', 'g'), '-');
					final_fecha = (final_fecha != '') ? ('/' + final_fecha) : '';
					inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');

					window.location = '?/operaciones/manuales_listar' + inicial_fecha + final_fecha;
				}
			});

			//$inicial_fecha.mask(mascara).datetimepicker({
			$inicial_fecha.datetimepicker({
				format: formato
			});

			//$final_fecha.mask(mascara).datetimepicker({
			$final_fecha.datetimepicker({
				format: formato
			});

			$inicial_fecha.on('dp.change', function(e) {
				$final_fecha.data('DateTimePicker').minDate(e.date);
			});

			$final_fecha.on('dp.change', function(e) {
				$inicial_fecha.data('DateTimePicker').maxDate(e.date);
			});

			var $form_fecha = $('#form_fecha');
			var $modal_fecha = $('#modal_fecha');

			$form_fecha.on('submit', function(e) {
				e.preventDefault();
			});

			$modal_fecha.on('show.bs.modal', function() {
				$form_fecha.trigger('reset');
			});

			$modal_fecha.on('shown.bs.modal', function() {
				$modal_fecha.find('[data-aceptar]').focus();
			});

			$modal_fecha.find('[data-cancelar]').on('click', function() {
				$modal_fecha.modal('hide');
			});

			$modal_fecha.find('[data-aceptar]').on('click', function() {
				$form_fecha.submit();
			});

			$('[data-cambiar]').on('click', function() {
				$('#modal_fecha').modal({
					backdrop: 'static'
				});
			});
		<?php } ?>

		<?php if ($ventas) { ?>
			var table = $('#table').DataFilter({
				filter: true,
				name: 'reporte_ventas_manuales',
				reports: 'excel|word|pdf|html'
			});
		<?php } ?>
	});
</script>
<?php require_once show_template('footer-advanced'); ?>