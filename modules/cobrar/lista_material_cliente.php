<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion) . date('-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
$Pendientes=$db->query("SELECT c.id_control,e.id_egreso,e.fecha_egreso,cl.id_cliente,cl.cliente,cl.nit,cl.tipo,em.nombres,em.paterno,em.materno
					FROM inv_control AS c
					LEFT JOIN inv_egresos AS e ON e.id_egreso=c.egreso_id
					LEFT JOIN inv_clientes AS cl ON cl.id_cliente=e.cliente_id
					LEFT JOIN sys_empleados AS em ON em.id_empleado=e.empleado_id
					WHERE c.estado='pendiente' AND c.cantidad NOT IN(0) AND c.egreso_id NOT IN(0) AND e.fecha_egreso BETWEEN '{$fecha_inicial}' AND '{$fecha_final}'
					GROUP BY c.egreso_id ORDER BY e.id_egreso DESC")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = true;
$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
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

<?php include("utilidad.php"); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista de Cuentas de Envios</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear) { ?>
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para realizar acciones clic en el siguiente botón(es): </div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<?php if ($permiso_cambiar) { ?>
					<button class="btn btn-default" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar</span></button>
				<?php } ?>
			</div>
		</div>
		<hr>
	<?php
		}
		if ($Pendientes) {
	?>
		<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
			<thead>
				<tr class="active">
					<th class="text-nowrap">#</th>
					<th class="text-nowrap">Fecha</th>
					<th class="text-nowrap">Tipo</th>
					<th class="text-nowrap">Cliente</th>
					<th class="text-nowrap">NIT/CI</th>
					<th class="text-nowrap">Cantidad</th>
					<th class="text-nowrap">Empleado</th>
					<?php if ($permiso_ver) { ?>
						<th class="text-nowrap">Opciones</th>
					<?php } ?>
				</tr>
			</thead>
			<tfoot>
				<tr class="active">
					<th class="text-nowrap align-middle" data-datafilter-filter="false">#</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Fecha</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Tipo</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Cliente</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">NIT/CI</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Cantidad</th>
					<th class="text-nowrap align-middle" data-datafilter-filter="true">Empleado</th>
					<?php if ($permiso_ver) { ?>
						<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
					<?php } ?>
				</tr>
			</tfoot>
			<tbody>
				<?php
					foreach($Pendientes as $Fila=>$Pendiente):
						$Cantidad=$Pendiente['id_egreso'];
						$Cantidad=$db->query("SELECT SUM(cantidad)AS cantidad FROM inv_control WHERE egreso_id='{$Cantidad}'")->fetch_first();
						$Cantidad = ($Cantidad['cantidad']) ? $Cantidad['cantidad'] : 0;
				?>
				<tr>
					<td><?=$Fila+1?></td>
					<td><?=$Pendiente['fecha_egreso']?></td>
					<td><?=$Pendiente['tipo']?></td>
					<td><?=$Pendiente['cliente']?></td>
					<td><?=$Pendiente['nit']?></td>
					<td><?=$Cantidad?></td>
					<td><?="{$Pendiente['nombres']} {$Pendiente['paterno']} {$Pendiente['materno']}"?></td>
					<td>
					<?php if ($permiso_ver) { ?>
						<a href="?/cobrar/detalle_envio/<?= $Pendiente['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de nota de remisión"><i class="glyphicon glyphicon-list-alt"></i></a>
					<?php } ?>
					</td>
				</tr>
				<?php
					endforeach;
				?>
			</tbody>
		</table>
	<?php } else { ?>
		<div class="alert alert-danger">
			<strong>Advertencia!</strong>
			<p>No existen notas de remisión registradas en la base de datos.</p>
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
<script>
	$(function() {

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
					window.location = '?/cobrar/lista_material_cliente' + inicial_fecha + final_fecha;
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

		var table = $('#table').DataFilter({
			filter: true,
			name: 'notas_remision',
			reports: 'xls|doc|pdf|html'
		});
	});
</script>
<?php require_once show_template('footer-configured');