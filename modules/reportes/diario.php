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
$ventas = $db->query("select v.*, p.codigo, p.nombre, p.nombre_factura, p.categoria_id, p.descripcion as descripcionp, upper(concat(e.nombres, ' ', e.paterno, ' ', e.materno)) as empleado, e.cargo 
						from (select e.fecha_egreso, e.hora_egreso, e.descripcion, e.cliente_id, e.nro_factura, e.nombre_cliente, e.nit_ci, e.empleado_id, d.*, d.unidad_id as unidad_otra, e.estadoe, 
						((e.descuento_bs/e.nro_registros) + (d.precio*d.descuento/100)) AS descuentoacumul,
						IF (e.codigo_control = '' AND e.provisionado = 'S','Nota de venta',(IF (e.fecha_limite = '0000-00-00','Venta manual',(IF (e.codigo_control != '' and e.fecha_limite != '0000-00-00','Venta con factura',''))))) as tipo
						from inv_egresos_detalles d 
						left join inv_egresos e on d.egreso_id = e.id_egreso 
						where e.tipo = 'venta' and e.anulado = 0 AND (estadoe = 3 OR estadoe = 0)) v 
						left join inv_productos p on v.producto_id = p.id_producto 
						left join sys_empleados e on v.empleado_id = e.id_empleado
                        where v.fecha_egreso between '$fecha_inicial' and '$fecha_final'")->fetch();

// echo $db->last_query(); die();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
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
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Reporte de ventas a detalle</strong>
	</h3>
</div>
<div class="panel-body" data-servidor="<?= ip_local . name_project . '/diario.php'; ?>">
	<?php if ($permiso_cambiar) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar la fecha hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<button class="btn btn-primary" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar fecha</span></button>
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
				<th class="text-nowrap">Código</th>				
				<th class="text-nowrap">Cliente</th>				
				<th class="text-nowrap">Tipo de venta</th>
				<th class="text-nowrap">Nro. venta</th>
				<th class="">Codigo producto</th>
				<th class="">Producto</th>
				<!-- <th class="text-nowrap">Lote</th>  --><!-- L&iacute;nea -->
				<th class="text-nowrap">Categoria</th>
				<th class="text-nowrap">Cantidad</th>
				<th class="text-nowrap">Unidad</th>
				<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap">Monto total <?= escape($moneda); ?></th>
				<th class="text-nowrap">Descuento <?= escape($moneda); ?></th>
				<th class="text-nowrap">Importe total <?= escape($moneda); ?></th>
                <th class="text-nowrap">Empleado</th>                
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>				
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>								
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>								
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo de venta</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nro. venta</th>
				<th class="text-middle" data-datafilter-filter="true">Codigo producto</th>
				<th class="text-middle" data-datafilter-filter="true">Producto</th>
				<!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">Lote</th>  --><!-- L&iacute;nea -->
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Monto total <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Descuento</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Importe total <?= escape($moneda); ?></th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>                
			</tr>
		</tfoot>
		<tbody>
			<?php $total = 0; ?>
			<?php foreach ($ventas as $nro => $venta) { ?>
			<?php $cantidad = escape($venta['cantidad']/cantidad_unidad($db,$venta['producto_id'],$venta['unidad_otra'])); ?>
			<?php $precio = escape($venta['precio']); ?>
			<?php $importe = $cantidad * $precio; ?>
			<?php $total = $total + $importe; ?>
			<?php $importe_total = $importe - $venta['descuentoacumul'];?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap">
					<span><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?></span><br>
					<small class="text-success"><?= escape($venta['hora_egreso']); ?></small>
				</td>				
				<td class="text-nowrap">
					<?= ($venta['cliente_id'] > 0) ? escape($venta['cliente_id']) : 'Distribuidor'; ?>					
				</td>							
				<td class="text-nowrap">
					<span><?= escape($venta['nombre_cliente']); ?></span><br>
					<small class="text-success"><?= escape($venta['nit_ci']); ?></small>					
				</td>							
				<td class="text-nowrap text-center"><?= escape($venta['tipo']); ?></td>
				<td class="text-nowrap text-right"><?= escape($venta['nro_factura']); ?></td>
				<td class="text-right">
					<b><?= escape($venta['codigo']); ?></b>
				</td>
				<td class="">
					<b><?= escape($venta['nombre_factura']); ?></b>
				</td>
				<!-- <td class="text-nowrap">
					<?php $lo = explode(',', $venta['lote']);
							foreach ($lo as $key => $val) {
								$lt = explode('-',$val);
								$nl = $db->query("SELECT lote2 FROM inv_ingresos_detalles WHERE producto_id = ".$venta['producto_id']." AND lote = '". $lt[0]."'")->fetch_first();
								$nl = ($nl['lote2']) ? $nl['lote2'] : '';
								echo '<b>'.$nl.'</b><br>';
							}
					?>
				</td> -->
				<td class="text-nowrap"><?php $categoria = $db->select('categoria')->from('inv_categorias')->where('id_categoria',$venta['categoria_id'])->fetch_first(); echo upper($categoria['categoria']); ?></td>
				<td class="text-nowrap text-right"><?= $cantidad; ?></td>
				<td class="text-nowrap text-right"><?= nombre_unidad($db,$venta['unidad_otra']); ?></td>
				<td class="text-nowrap text-right"><?= $precio; ?></td>
				<td class="text-nowrap text-right" data-total="<?= $importe; ?>"><?= number_format($importe, 2); ?></td>
				<td class="text-nowrap text-right"><?= number_format($venta['descuentoacumul'],2); ?></td>
				<td class="text-nowrap text-right"><?= number_format($importe_total,2); ?></td>
                <td class="width-md"><?= escape($venta['empleado']); ?></td>
                <!-- <td class="width-md"><?php if($venta['cargo']==1){echo $_institution['empresa1'];}else{echo $_institution['empresa2'];}; ?><?= escape($venta['cargo']); ?></td> -->
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
	<div class="well">
		<p class="lead margin-none">
			<b>Empleado:</b>
			<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
		</p>
		<p class="lead margin-none">
			<u id="total">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
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
<script src="<?= js; ?>/jquery.dataFiltersCustom.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
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
			
			window.location = '?/reportes/diario' + inicial_fecha + final_fecha;
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
	
	<?php if ($ventas) { ?>
	var table = $('#table').on('draw.dt', function () { // search.dt order.dt page.dt length.dt
		var suma = 0;
		$('[data-total]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-total'));
            // console.log(total);
			suma = suma + total;
		});
		$('#total').text(suma.toFixed(2));
	}).DataFilter({
		filter: true,
		name: 'Reporte diario',
		reports: 'xls|doc|pdf|html',
		total: 13,
		creacion: 'Para la fecha: ' + '<?= date('Y-m-d H:i') ?>',
		fechas: 'El reporte fue generado desde: <?= $fecha_inicial ?> hasta: <?= ($fecha_final == ((date('Y') + 16) . date('-m-d')) ) ? date('Y-m-d') : $fecha_final ?>',
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>