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
$ventas = $db->query("SELECT * FROM (
	(SELECT * FROM (
        (SELECT a.id_producto, a.nombre, a.categoria_id, a.unidad_id, u.unidad , a.codigo
        FROM inv_productos a 
        LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id 
        INNER JOIN inv_ingresos_detalles c ON c.producto_id = a.id_producto 
        LEFT JOIN inv_ingresos ci ON c.ingreso_id = ci.id_ingreso 
        WHERE ci.fecha_ingreso between '$fecha_inicial' and '$fecha_final' GROUP BY a.id_producto ORDER BY a.id_producto)
    	UNION
    	(SELECT a.id_producto, a.nombre, a.categoria_id, a.unidad_id, u.unidad , a.codigo
        FROM inv_productos a 
        LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
        INNER JOIN tmp_egresos_detalles t ON t.producto_id = a.id_producto
        LEFT JOIN tmp_egresos te ON te.id_egreso = t.egreso_id
        WHERE te.fecha_egreso between '$fecha_inicial' and '$fecha_final' AND te.distribuidor_estado != 'ENTREGA' GROUP BY a.id_producto ORDER BY a.id_producto)) as a)
    UNION
    (SELECT a.id_producto, a.nombre, a.categoria_id, a.unidad_id, u.unidad , a.codigo
	FROM inv_productos a 
    LEFT JOIN inv_unidades u ON u.id_unidad = a.unidad_id
    INNER JOIN inv_egresos_detalles b ON b.producto_id = a.id_producto
    LEFT JOIN inv_egresos be ON be.id_egreso = b.egreso_id
    WHERE be.anulado != 3 AND be.fecha_egreso between '$fecha_inicial' and '$fecha_final' GROUP BY a.id_producto ORDER BY a.id_producto)
) AS asa ORDER BY asa.id_producto")->fetch();

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
		<strong>Reporte de producto</strong>
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
				<th class="text-nowrap">Código</th>
				<th class="text-nowrap">Producto</th>
				<th class="text-nowrap">Categoria</th>
				<th class="text-nowrap">Unidades</th>
				<th class="text-nowrap">Ventas</th>
				<th class="text-nowrap">Total Ventas <?= escape($moneda); ?></th>
				<th class="text-nowrap">Devueltos</th>
				<th class="text-nowrap">Total Devueltos <?= escape($moneda); ?></th>
				<th class="text-nowrap">Compras realizadas</th>
				<th class="text-nowrap">Total Compras <?= escape($moneda); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Producto</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Unidades</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Ventas</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Total Ventas <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Devueltos</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Total Devueltos <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Compras</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Total Compras <?= escape($moneda); ?></th>
			</tr>
		</tfoot>
		<tbody>
			<?php $total = 0; ?>
			<?php foreach ($ventas as $nro => $venta) { 
			    $id_producto = $venta['id_producto'];
			    $ventas2 = $db->query("SELECT SUM(a.cantidad) as cantidad_total, SUM(a.precio*(a.cantidad/(IF(d.cantidad_unidad is null, 1, d.cantidad_unidad)))) as valor_total
			        FROM inv_egresos_detalles a 
			        INNER JOIN inv_egresos b ON b.id_egreso = a.egreso_id
			        LEFT JOIN inv_asignaciones d ON d.producto_id = a.producto_id AND d.unidad_id = a.unidad_id   AND d.visible = 's'
			        WHERE a.producto_id = '$id_producto' AND b.tipo = 'Venta' AND b.fecha_egreso between '$fecha_inicial' and '$fecha_final' ")->fetch_first();
		        $devueltos = $db->query("SELECT SUM(a.cantidad) as cantidad_total, SUM(a.precio*(a.cantidad/(IF(d.cantidad_unidad is null, 1, d.cantidad_unidad)))) as valor_total
			        FROM tmp_egresos_detalles a 
			        LEFT JOIN tmp_egresos b ON b.id_egreso = a.egreso_id
			        LEFT JOIN inv_asignaciones d ON d.producto_id = a.producto_id AND d.unidad_id = a.unidad_id  AND d.visible = 's'
			        WHERE a.producto_id = '$id_producto' AND b.distribuidor_estado NOT IN ('ENTREGA', 'VENTA') AND b.fecha_egreso between '$fecha_inicial' and '$fecha_final' ")->fetch_first();
		        $compras = $db->query("SELECT SUM(a.cantidad) as cantidad_total, SUM(a.costo * a.cantidad) as valor_total
			        FROM inv_ingresos_detalles a 
			        LEFT JOIN inv_ingresos b ON b.id_ingreso = a.ingreso_id
			        WHERE a.producto_id = '$id_producto' AND b.fecha_ingreso between '$fecha_inicial' and '$fecha_final' ")->fetch_first();
			?>
			
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<th class="text-nowrap"><?= $venta['codigo'] ?></th>
				<td class="text-nowrap">
					<b><?= escape($venta['nombre']); ?></b>
				</td>
				<td class="text-nowrap"><?php $categoria = $db->select('categoria')->from('inv_categorias')->where('id_categoria',$venta['categoria_id'])->fetch_first(); echo $categoria['categoria']; ?></td>
				<td class="text-nowrap text-right"><?= $venta['unidad'] ?></td>
				<td class="text-nowrap text-right"><?= $ventas2['cantidad_total'] ?></td>
				<td class="text-nowrap text-right" data-total1="<?= round($ventas2['valor_total'],2) ?>"><?= round($ventas2['valor_total'],2) ?></td>
				<td class="text-nowrap text-right"><?= ($devueltos['cantidad_total'])?$devueltos['cantidad_total']:0; ?> </td>
				<td class="text-nowrap text-right" data-total2="<?= round($devueltos['valor_total'],2) ?>"><?= round($devueltos['valor_total'],2) ?></td>
				<td class="text-nowrap text-right"><?= ($compras['cantidad_total'])?$compras['cantidad_total']:0; ?> </td>
				<td class="text-nowrap text-right" data-total3="<?= round($compras['valor_total'],2) ?>"><?= round($compras['valor_total'],2) ?></td>
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
			<b>Total Ventas:</b>
			<u id="total1">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
		<p class="lead margin-none">
			<b>Total Devueltos:</b>
			<u id="total2">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
		<p class="lead margin-none">
			<b>Total Compras:</b>
			<u id="total3">0.00</u>
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
			
			window.location = '?/reportes/productos' + inicial_fecha + final_fecha;
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
		var suma1 = 0;
		var suma2 = 0;
		var suma3 = 0;
		$('[data-total1]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-total1'));
            console.log(total);
			suma1 = suma1 + total;
		});
		$('#total1').text(suma1.toFixed(2));
		$('[data-total2]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-total2'));
            console.log(total);
			suma2 = suma2 + total;
		});
		$('#total2').text(suma2.toFixed(2));
		$('[data-total3]:visible').each(function (i) {
			var total = parseFloat($(this).attr('data-total3'));
            console.log(total);
			suma3 = suma3 + total;
		});
		$('#total3').text(suma3.toFixed(2));
	}).DataFilter({
		filter: true,
		name: 'reporte_diario',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>