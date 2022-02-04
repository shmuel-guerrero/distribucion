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
$ventas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, p.*,m.precio,c.cantidad,c.estado, COUNT(pd.pago_id) as nro_cuotas, SUM(px.monto)as pagos_realizados')
			->from('inv_egresos i')
			->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
            ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
            ->join('inv_materiales m', 'm.id_empleado = e.id_empleado', 'left')
            ->join('inv_control c', 'c.id_materiales = m.id_materiales', 'left')
			->join('inv_pagos p', 'p.movimiento_id = i.id_egreso', 'left')
			
			->join('inv_pagos_detalles pd', 'pd.pago_id = p.id_pago', 'left')
            ->join('inv_pagos_detalles px', 'px.pago_id = p.id_pago AND px.id_pago_detalle = pd.id_pago_detalle AND px.estado="1"', 'left')
            
            ->where('c.tipo =', 'cliente')
			->where('i.tipo', 'Venta')
			//->where('i.codigo_control', '')
			->where('i.plan_de_pagos = ', 'si')
			->where('p.tipo = ', 'Egreso')
			//->where('i.estado = ', 'V')
			->where('i.estado = ', 1)
			->group_by('pd.pago_id')
			->order_by('i.fecha_egreso desc, i.hora_egreso desc')
			->fetch();
//->where('i.provisionado', 'S')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
//$permiso_crear = in_array('notas_crear', $permisos);
$permiso_ver = true;
//$permiso_eliminar = in_array('notas_eliminar', $permisos);
$permiso_cambiar = true;

$MontoTotalAcumulado=0;
$precioTotalAcumulado=0;
$MontoPendiente =0;
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
		<strong>Lista General de Pagos y Materiales</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar una nota de remisión hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_cambiar) { ?>
			<button class="btn btn-default" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar</span></button>
			<?php } ?>
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
				<th class="text-nowrap">Nro. Nota</th>
				<th class="text-nowrap">Monto total <?= escape($moneda); ?></th>
				<th class="text-nowrap">Monto pendiente</th>
				<th class="text-nowrap">Nro Cuotas</th>
				<th class="text-nowrap">Detalle</th>

                <th class="text-nowrap">Cantidad Materiales</th>
				<th class="text-nowrap">Precio Materiales</th>
				<th class="text-nowrap">Estado</th>

				<th class="text-nowrap">Almacen</th>
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
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Nro. Nota</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Monto total <?= escape($moneda); ?></th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Monto pendiente</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Nro. cuotas</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Detalle</th>

                <th class="text-nowrap align-middle" data-datafilter-filter="true">Cantidad Materiales</th>		
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Precio Materiales</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Estado</th>

				<th class="text-nowrap align-middle" data-datafilter-filter="true">Almacen</th>
				<th class="text-nowrap align-middle" data-datafilter-filter="true">Empleado</th>
				<?php if ($permiso_ver) { ?>
				<th class="text-nowrap align-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php 
			$total_deudas=0;
			foreach ($ventas as $nro => $venta) { 
				$subtotal=$venta['monto_total']-$venta['pagos_realizados'];
                $total_deudas+=$venta['monto_total']-$venta['pagos_realizados'];
                $MontoTotalAcumulado+=$venta['monto_total'];
                $MontoPendiente+=$venta['monto_total']-$venta['pagos_realizados'];
                $precioTotalAcumulado+=$venta['precio'];
			?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($venta['hora_egreso']); ?></small></td>
				
				<td class="text-nowrap"><?php
					if($venta['codigo_control'] != ''){
						echo "Venta electrónica";
					}
					else{
						if($venta['provisionado'] == 'S'){
							echo "Venta con nota de remisión";
						}
						else{
							echo "Venta manual";
						}
					}					
				?></td>

				<td class="text-nowrap"><?= escape($venta['nombre_cliente']); ?></td>
				<td class="text-nowrap"><?= escape($venta['nit_ci']); ?></td>
				<td class="text-nowrap text-right"><?= escape($venta['nro_factura']); ?></td>
				<td class="text-nowrap text-right"><?= escape($venta['monto_total']*(1-$venta['descuento']/100) ); ?></td>
				<td class="text-nowrap text-right">
					<?php if($venta['pagos_realizados']<$venta['monto_total']*(1-$venta['descuento']/100)){ ?>
						<span class="text-danger">
					<?php } else{ ?>
						<span class="text-success">
					<?php }	?> <?= number_format($subtotal,2,'.',''); ?></span>
				</td>				

				<td class="text-nowrap text-right"><?= escape($venta['nro_cuotas']); ?></td>
				
				<td class="text-nowrap">
					<?php if($venta['pagos_realizados']<$venta['monto_total']*(1-$venta['descuento']/100)){ ?>
						<span class="text-danger"><b><?php //echo $ingreso['deuda']." ".$ingreso['nro_cuotas']; ?>Tiene cuentas pendientes</b></span> 
					<?php }else{  ?>
						<span class="text-success"><b><?php //echo $ingreso['deuda']." ".$ingreso['nro_cuotas']; ?>Cuentas al dia</b></span>
					<?php 
					} 
				?>							
				</td>
                <!--materiales-->
                <td class="text-nowrap"><?= escape($venta['cantidad']); ?></td>
				<td class="text-nowrap"><?= escape($venta['precio']); ?></td>
                <td class="text-nowrap">
					<?php if($venta['estado']=='si'){ ?>
						<span class="text-danger"><b><?php //echo $ingreso['deuda']." ".$ingreso['nro_cuotas']; ?>Pendiente</b></span> 
					<?php }else{  ?>
						<span class="text-success"><b><?php //echo $ingreso['deuda']." ".$ingreso['nro_cuotas']; ?>Entregado</b></span>
					<?php 
					} 
				?>							
				</td>

				<td class="text-nowrap"><?= escape($venta['almacen']); ?></td>
				<td class="width-md"><?= escape($venta['nombres'] . ' ' . $venta['paterno'] . ' ' . $venta['materno']); ?></td>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
					<a href="?/cobrar/notas_ver/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de nota de remisión"><i class="glyphicon glyphicon-list-alt"></i></a>
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
		<p>No existen notas de remisión registradas en la base de datos.</p>
	</div>
	<?php } ?>
</div>
<div class="well">
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Monto Total:</b>
			<u id="montototal"><?= number_format($MontoTotalAcumulado,2,'.','') ?></u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Monto Pendiente Total:</b>
			<u id="preciototal"><?= number_format($MontoPendiente,2,'.','') ?></u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="col-sm-4">
		<p class="lead margin-none">
			<b>Precio Materiales Total:</b>
			<u id="preciototal"><?= number_format($precioTotalAcumulado,2,'.','') ?></u>
			<span><?= escape($moneda); ?></span>
		</p>
	</div>
	<div class="clearfix">
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
<!--<script src="<?//= js; ?>/FileSaver.min.js"></script>-->
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
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
			
			window.location = '?/cobrar/listar' + inicial_fecha + final_fecha;
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
	var table = $('#table').DataFilter({
		filter: true,
		name: 'notas_remision',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>
