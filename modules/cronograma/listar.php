<?php

$id_cronograma = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene los ingresos
$ingresos = $db->select('*')
				->from('cronograma_cuentas')
				->where('cronograma_id',$id_cronograma)				
				->order_by('fecha')
				->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
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

<?php //include("utilidad.php"); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Cronograma</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $empleados)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/cronograma/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	

	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<?php if ($ingresos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Detalle</th>
				<th class="text-nowrap">Fecha</th>
				<th class="text-nowrap">Estado</th>
				<th class="text-nowrap">Fecha de pago</th>
				<th class="text-nowrap">Monto</th>
				<th class="text-nowrap">Tipo</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Detalle</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Fecha</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Estado</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Fecha de pago</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Monto</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Tipo</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($ingresos as $nro => $ingreso) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($ingreso['detalle']); ?></td>
				<td class="text-nowrap"><?= escape(date_decode($ingreso['fecha'], $_institution['formato'])); ?></td>
				<td class="text-nowrap">				
										<?php 
										if($ingreso['estado']==0){
											?><span class="text-danger"><b>Pendiente</b></span><?php
										}else{
											?><span class="text-success"><b>Cancelado</b></span><?php
										}
										?>
				</td>
				<td class="text-nowrap"><?= escape(date_decode($ingreso['fecha_pago'], $_institution['formato'])); ?></td>
				<td class="text-nowrap"><?= number_format($ingreso['monto'],2,"."," "); ?></td>
				<td class="text-nowrap"><?= escape($ingreso['tipo_pago']); ?></td>
				
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<a href="?/cronograma/pagar/<?= $ingreso['id_cronograma_cuentas']; ?>" data-toggle="tooltip" data-title="Pago de la cuota" data-pagar="true"><span class="glyphicon glyphicon-usd"></span></a>
					<?php if ($permiso_eliminar) { ?>
					<a href="?/cronograma/delete/<?= $ingreso['id_cronograma_cuentas']; ?>" data-toggle="tooltip" data-title="Eliminar ingreso" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ingresos registrados en la base de datos.</p>
	</div>
	<?php } ?>
</div>


<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el ingreso y todo su detalle?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/ingresos/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_cambiar) { ?>
	var formato = $('[data-formato]').attr('data-formato');
	var mascara = $('[data-mascara]').attr('data-mascara');
	var gestion = $('[data-gestion]').attr('data-gestion');
	var $inicial_fecha = $('#inicial_fecha');
	var $final_fecha = $('#final_fecha');

	$.validate({
		form: '#form_fecha',
		modules: 'date',
		onSuccess: function () {
			var inicial_fecha = $.trim($('#inicial_fecha').val());
			var final_fecha = $.trim($('#final_fecha').val());
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : ''); 
			
			window.location = '?/ingresos/reporte_cuentas_pagar' + inicial_fecha + final_fecha;
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

	$inicial_fecha.on('dp.change', function (e) {
		$final_fecha.data('DateTimePicker').minDate(e.date);
	});
	
	$final_fecha.on('dp.change', function (e) {
		$inicial_fecha.data('DateTimePicker').maxDate(e.date);
	});

	var $form_fecha = $('#form_fecha');
	var $modal_fecha = $('#modal_fecha');

	$form_fecha.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_fecha.on('show.bs.modal', function () {
		$form_fecha.trigger('reset');
	});

	$modal_fecha.on('shown.bs.modal', function () {
		$modal_fecha.find('[data-aceptar]').focus();
	});

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

	$modal_fecha.find('[data-aceptar]').on('click', function () {
		$form_fecha.submit();
	});

	$('[data-cambiar]').on('click', function () {
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	
	<?php if ($ingresos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'ingresos',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>