<?php

$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el id_venta
$id_venta = (sizeof($params) > 0) ? $params[0] : 0;



// Obtiene la venta
$venta = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, i.plan_de_pagos, p.id_pago')
	->from('inv_egresos i')
	->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
	->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
	->join('inv_pagos p', 'p.movimiento_id = i.id_egreso AND p.tipo="Egreso"', 'left')
	->where('id_egreso', $id_venta)
	->where('p.tipo', 'Egreso')
	->fetch_first();

// Verifica si existe el egreso
if (!$venta) {
	// Error 404
	require_once not_found();
	exit;
}
// echo json_encode($venta); die();

// Obtiene los detalles
$detalles = $db->query("SELECT d.*, p.codigo, p.nombre, p.nombre_factura, u.unidad, d.cantidad AS tamanio
								FROM inv_egresos_detalles d
								LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
								LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
								LEFT JOIN inv_asignaciones a ON a.producto_id = d.producto_id AND a.unidad_id = d.unidad_id  AND a.visible = 's'
								LEFT JOIN inv_unidades u ON u.id_unidad = d.unidad_id
								WHERE d.egreso_id = $id_venta 
								ORDER BY codigo asc")->fetch();

// echo json_encode($detalles); die();

$detallesCuotaN = $db->select('COUNT(pd.pago_id) AS NRO_LINES')
	->from('inv_pagos_detalles pd')
	->where('pd.pago_id', $venta['id_pago'])
	->order_by('nro_cuota, fecha asc, fecha_pago asc')
	->fetch_first();


$NRO_LINES = $detallesCuotaN['NRO_LINES'];

// Obtiene los detalles
$detallesCuota = $db->select('*')
	->from('inv_pagos_detalles pd')
	->where('pd.pago_id', $venta['id_pago'])
	->order_by('nro_cuota, fecha asc, fecha_pago asc')
	->fetch();

// echo json_encode($detallesCuota); die();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la dosificacion del periodo actual
// $dosificacion = $db->from('inv_dosificaciones')->where('fecha_limite', $venta['fecha_limite'])->fetch_first();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_editar = in_array('notas_editar', $permisos);
$permiso_listar = in_array('notas_listar', $permisos);
$permiso_reimprimir = in_array('notas_obtener', $permisos);

$permiso_guardar_pago = in_array('guardar_pago', $permisos);
$permiso_eliminar_pago = in_array('eliminar_pago', $permisos);
$permiso_imprimir_comprobante = in_array('imprimir_comprobante', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<?php // include("utilidad.php"); ?>

<style>
	.table-responsive {
		overflow-y: visible;
		overflow-x: visible;
		overflow: visible;
	}

	#cuotas_table td {
		padding: 0;
		height: 0;
		border-width: 0px;
	}

	.cuota_div {
		height: 0;
		overflow: hidden;
	}
</style>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-venta="<?= $id_venta; ?>" data-servidor="<?= ip_local . 'sistema/nota.php'; ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Detalle de nota de remisión</strong>
	</h3>
</div>

<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para regresar al listado de almacenes hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/cobrar/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Listado</span>
			</a>
		</div>
	</div>
	<hr>
	

	<form id="fromii" class="form-horizontal" autocomplete="off">
		<input type='hidden' name='idventa' value='<?= $id_venta ?>'>
		<input id="pago" name="pago" type="hidden" value="<?= $venta['id_pago']; ?>">

		<div class="row">
			<div class="col-sm-12 col-md-8">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle de la nota de remisión</h3>
					</div>
					
					
					
					<div class="panel-body">
						<?php if ($detalles) { ?>
							<div class="table-responsive">
								<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
									<thead>
										<tr class="active">
											<th class="text-nowrap">#</th>
											<th class="text-nowrap">Código</th>
											<th class="text-nowrap">Nombre</th>
											<th class="text-nowrap">Unidad</th>
											<th class="text-nowrap">Cantidad</th>
											<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
											<th class="text-nowrap hidden">Descuento (%)</th>
											<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php $total = 0; ?>
										<?php foreach ($detalles as $nro => $detalle) { ?>
											<tr>
												<?php $cantidad = escape($detalle['cantidad'] / cantidad_unidad($db, $detalle['producto_id'], $detalle['unidad_id'])); ?>
												<?php $precio = escape($detalle['precio']); ?>
												<?php $importe = $cantidad * $precio; ?>
												<?php $total = $total + $importe; ?>
												<th class="text-nowrap"><?= $nro + 1; ?></th>
												<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
												<td class="text-nowrap"><?= escape($detalle['nombre_factura']); ?></td>
												<td class="text-nowrap"><?= escape($detalle['unidad']) . " (" . escape($detalle['tamanio']) . ")"; ?></td>
												<td class="text-nowrap text-right"><?= $cantidad; ?></td>
												<td class="text-nowrap text-right"><?= $precio; ?></td>
												<td class="text-nowrap text-right hidden"><?= $venta['descuento']; ?></td>
												<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
											</tr>
										<?php } ?>
									</tbody>
									<tfoot>
										<?php
										if ($total > 0) {
											$descuento = ($total * $venta['descuento']) / 100;
											$descuento_total = $total - $descuento;
										} else {
											$descuento_total = $total;
										}
										if ($venta['descuento'] > 0.00) {
										?>
											<tr class="active">
												<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
												<th class="text-nowrap text-right"><?= number_format($total, 2, '.', ''); ?></th>
											</tr>
											<tr class="active">
												<th class="text-nowrap text-right" colspan="6">DESCUENTO DEL <?= escape(number_format($venta['descuento']), 0) . " %" ?></th>
												<th class="text-nowrap text-right"><?= escape(number_format($descuento, 2, '.', '')) . "" ?></th>
											</tr>
											<tr class="active">
												<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL CON DESCUENTO<?= escape($moneda); ?></th>
												<th class="text-nowrap text-right"><?= number_format($descuento_total, 2, '.', '') ?></th>
												</th>
											</tr>
										<?php
										} else {
										?>
											<tr class="active">
												<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
												<th class="text-nowrap text-right"><?= number_format($descuento_total, 2, '.', ''); ?></th>
											</tr>
										<?php
										}
										?>
										<input id="totalProducto" type='hidden' value="<?= $descuento_total ?>">
									</tfoot>

								</table>
							</div>
						<?php } else { ?>
							<div class="alert alert-danger">
								<strong>Advertencia!</strong>
								<p>Esta nota de remisión no tiene detalle, es muy importante que todos los egresos cuenten con un detalle que especifique la operación realizada.</p>
							</div>
						<?php } ?>
					</div>
				</div>
				
				<?php // die(); ?>

				<?php if (escape($venta['plan_de_pagos']) == "si") { ?>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle del las cuotas</h3>
						</div>
						<div class="panel-body">
							<?php if ($detallesCuota) { ?>
								<div class="table-responsive">
									<table id="cuotas_table1" class="table table-bordered table-condensed table-restructured table-striped table-hover">
										<thead>
											<tr class="active">
												<th class="text-nowrap">#</th>
												<th class="text-nowrap">Descripción</th>
												<th class="text-nowrap">Fecha Programada</th>
												<th class="text-nowrap">Fecha de Pago</th>
												<th class="text-nowrap">Monto <?= escape($moneda); ?></th>
												<th class="text-nowrap">Estado</th>

												<?php if ($permiso_guardar_pago) { ?>
													<th class="text-nowrap">Guardar</th>
												<?php } ?>
												<?php if ($permiso_imprimir_comprobante) { ?>
													<th class="text-nowrap">Comprobante</th>
												<?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php $total = 0; ?>
											<?php foreach ($detallesCuota as $nro => $detalle) {
												if ($detalle['estado'] == 1) {
													$total = $total + $detalle['monto'];
												}												
												$i = $nro + 1
											?>
												<tr>
													<td class="text-nowrap"><?= $i ?></td>
													<td class="text-nowrap">Pago #<?= $i ?></td>
													<td class="text-nowrap"><?= $detalle['fecha'] ?></td>
													<td class="text-nowrap"><?= $detalle['fecha_pago']?></td>
													<td class="text-nowrap"> <?= round($detalle['monto'], 1, PHP_ROUND_HALF_UP); ?></td>
													<td class="text-nowrap">
													<?php
														if ($detalle['estado'] == 0) {
														?>
															<span class="text-danger"><b>Pendiente</b></span>

														<?php
														} else {
														?>
															<span  class="text-success"><b>Cancelado</b></span>
														<?php
														}
													?>
													</td>
													<td class="text-nowrap">
													<?php if ($permiso_guardar_pago) { ?>
														<?php
															if ($detalle['estado'] == 0) {
															?>
																<button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#CobrarCuotaModal<?= $detalle['id_pago_detalle'] ?>">
																Cobrar <i class="glyphicon glyphicon-usd"></i>
																</button>
															<?php
															}
														?>
													<?php } ?>
													</td>
													<td class="text-nowrap">
														<div	>
															<?php
															if ($detalle['estado'] == 1) {
															?><a href="?/cobrar/imprimir_comprobante/<?= $detalle['id_pago_detalle']; ?>" target="_blank" data-toggle="tooltip" data-title="Imprimir comprobante"><span class="glyphicon glyphicon-print"></span></a>
															<?php } ?>
														</div>

													</td>
												</tr>
											<?php } ?>

										</tbody>
										<tfoot>
											<tr class="active">
												<th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
												<th class="text-nowrap text-right" ><?= escape(number_format($total, 2)); ?></th>
												<th class="text-nowrap" colspan="4">
													<?php if($total < $venta['monto_total_descuento']){ ?>
													<span class="text-danger"><?php echo escape($total) . ' - ' . escape($venta['monto_total_descuento'] . ' : Pendiente ' . escape($venta['monto_total_descuento'] - $total)); ?></span>
													<?php } else { ?>
														<span>Cuotas completadas</span>
													<?php }?>
												</th>
											</tr>
										</tfoot>
									</table>

									<!-- <div class="col-sm-12 text-center" id="agregar">
										<a class="btn btn-success" onclick="javascript:AddCuota();"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs hidden-sm"> Nueva Cuota</span></a>
										<?php if ($permiso_eliminar_pago) { ?>
											<a class="btn btn-success" onclick="javascript:DeleteCuota();"><i class="glyphicon glyphicon-remove"></i><span class="hidden-xs hidden-sm"> Eliminar Cuota</span></a>
										<?php } ?>
									</div> -->
								</div>
							<?php } else { ?>
								<div class="alert alert-danger">
									<strong>Advertencia!</strong>
									<p>Este ingreso no tiene detalle, es muy importante que todos los ingresos cuenten con un detalle de la compra.</p>
								</div>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
			<div class="col-sm-12 col-md-4">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="glyphicon glyphicon-log-out"></i> Información de la nota de remisión</h3>
					</div>
					<div class="panel-body">
						<div class="form-horizontal">
							<div class="form-group">
								<label class="col-md-3 control-label">Fecha y hora:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($venta['hora_egreso']); ?></small></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Cliente:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nombre_cliente']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">NIT / CI:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nit_ci']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Tipo de egreso:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['tipo']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Número de factura:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nro_factura']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Descripción:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['descripcion']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Monto total:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['monto_total']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Descuento:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['descuento_bs']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Monto total neto:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['monto_total_descuento']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Código de control:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['codigo_control']); ?></p>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 control-label">Tipo de Pago:</label>
								<div class="col-md-9">
									<?php if (escape($venta['plan_de_pagos']) == "si") { ?>
										<p class="form-control-static">crédito</p>
									<?php } else { ?>
										<p class="form-control-static">Pago Completo</p>
									<?php } ?>
								</div>
							</div>

							<div class="form-group">
								<label class="col-md-3 control-label">Número de registros:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nro_registros']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Almacén:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['almacen']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Empleado:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($venta['nombres'] . ' ' . $venta['paterno'] . ' ' . $venta['materno']); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<!-- Inicio modal cliente -->
<?php if ($permiso_editar) { ?>
	<div id="modal_cliente" class="modal fade">
		<div class="modal-dialog">
			<form method="post" action="?/cobrar/notas_editar" id="form_cliente" class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Editar datos del cliente</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="nit_ci">NIT / CI:</label>
								<input type="text" name="id_egreso" value="<?= $venta['id_egreso']; ?>" class="translate" tabindex="-1" data-validation="required number">
								<input type="text" name="nit_ci" value="<?= $venta['nit_ci']; ?>" id="nit_ci" class="form-control" autocomplete="off" data-validation="required number">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group">
								<label for="nombre_cliente">Señor(es):</label>
								<input type="text" name="nombre_cliente" value="<?= $venta['nombre_cliente']; ?>" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./& " data-validation-length="max100">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">
						<span class="glyphicon glyphicon-ok"></span>
						<span>Guardar</span>
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
<!-- Fin modal cliente -->



<!-- Modal guardar pago -->
<?php if ($permiso_guardar_pago) { ?>
	<?php foreach ($detallesCuota as $nro => $detalle) { ?>
		<?php
			if ($detalle['estado'] == 0) {
			?>
				<div class="modal fade" id="CobrarCuotaModal<?= $detalle['id_pago_detalle'] ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<form action="?/cobrar/guardar_pago" method="POST">
								<div class="modal-header">
									<h4 class="modal-title" id="exampleModalLabel">Cobrar cuota</h4>
								</div>
								<div class="modal-body">
									<input type="hidden" name="id_venta" value="<?= $venta['id_egreso'] ?>">
									<input type="hidden" name="id_pago_detalle" value="<?= $detalle['id_pago_detalle'] ?>">
									<div class="form-group">
										<label for="fecha_programada">Fecha de programada</label>
										<input type="date" class="form-control" name="fecha_programada" id="fecha_programada" value="<?= $detalle['fecha'] ?>"  aria-describedby="heplFecha" placeholder="Fecha programada" readonly>
										<small id="heplFecha" class="form-text text-muted">La fecha muestra la fecha programada.</small>
									</div>

									<div class="form-group">
										<label for="fecha">Fecha de pago</label>
										<input type="date" class="form-control" name="fecha" id="fecha" value="<?= date('Y-m-d')?>" min="<?= date('Y-m-d')?>"  aria-describedby="heplFecha" placeholder="Fecha actual">
										<small id="heplFecha" class="form-text text-muted">La fecha que se regsitra es la fecha actual.</small>
									</div>
									<div class="form-group">
										<label for="monto">Monto de la cuota</label>
										<input type="number" class="form-control" name="monto" id="monto" min="0.00" value="<?= round($detalle['monto'], 1, PHP_ROUND_HALF_UP);  ?>" max="<?= round($detalle['monto'], 1, PHP_ROUND_HALF_UP);  ?>" step="any" aria-describedby="helpMonto" placeholder="Monto de la cuota">
										<small id="helpMonto" class="form-text text-muted">Monto de la cuota programada.</small>
									</div>
								</div>
								<div class="modal-footer">
									<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Registrar</button>
									<button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php
			}
		?>
	<?php } ?>
<?php } ?>




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

<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>

<script>
	var nroCuota = <?php echo $nro + 1; ?>;

	var formato = $('[data-formato]').attr('data-formato');
	var $inicial_fecha = new Array();
	var $pago_fecha = new Array();
	var NRO_LINES = <?PHP echo $NRO_LINES; ?>;

	$(function() {
		var formato = $('[data-formato]').attr('data-formato');

		//alert(formato);

		<?php
		foreach ($detallesCuota as $nro => $detalle) {
		?>
			$("#tipo<?php echo ($nro + 1); ?> option[value='<?php echo $detalle['tipo_pago']; ?>']").attr("selected", true);
		<?php
		}
		?>

		for (i = 1; i < 36; i++) {
			$inicial_fecha[i] = $('#inicial_fecha_' + i + '');
			$inicial_fecha[i].datetimepicker({
				format: formato
			});

			$pago_fecha[i] = $('#pago_fecha_' + i + '');
			$pago_fecha[i].datetimepicker({
				format: formato
			});
		}

		$inicial_fecha[1].on('dp.change', function(e) {
			$inicial_fecha[2].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[2].on('dp.change', function(e) {
			$inicial_fecha[3].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[3].on('dp.change', function(e) {
			$inicial_fecha[4].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[4].on('dp.change', function(e) {
			$inicial_fecha[5].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[5].on('dp.change', function(e) {
			$inicial_fecha[6].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[6].on('dp.change', function(e) {
			$inicial_fecha[7].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[7].on('dp.change', function(e) {
			$inicial_fecha[8].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[8].on('dp.change', function(e) {
			$inicial_fecha[9].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[9].on('dp.change', function(e) {
			$inicial_fecha[10].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[10].on('dp.change', function(e) {
			$inicial_fecha[11].data('DateTimePicker').minDate(e.date);
		});

		$inicial_fecha[11].on('dp.change', function(e) {
			$inicial_fecha[12].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[12].on('dp.change', function(e) {
			$inicial_fecha[13].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[13].on('dp.change', function(e) {
			$inicial_fecha[14].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[14].on('dp.change', function(e) {
			$inicial_fecha[15].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[15].on('dp.change', function(e) {
			$inicial_fecha[16].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[16].on('dp.change', function(e) {
			$inicial_fecha[17].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[17].on('dp.change', function(e) {
			$inicial_fecha[18].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[18].on('dp.change', function(e) {
			$inicial_fecha[19].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[19].on('dp.change', function(e) {
			$inicial_fecha[20].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[20].on('dp.change', function(e) {
			$inicial_fecha[21].data('DateTimePicker').minDate(e.date);
		});

		$inicial_fecha[21].on('dp.change', function(e) {
			$inicial_fecha[22].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[22].on('dp.change', function(e) {
			$inicial_fecha[23].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[23].on('dp.change', function(e) {
			$inicial_fecha[24].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[24].on('dp.change', function(e) {
			$inicial_fecha[25].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[25].on('dp.change', function(e) {
			$inicial_fecha[26].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[26].on('dp.change', function(e) {
			$inicial_fecha[27].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[27].on('dp.change', function(e) {
			$inicial_fecha[28].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[28].on('dp.change', function(e) {
			$inicial_fecha[29].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[29].on('dp.change', function(e) {
			$inicial_fecha[30].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[30].on('dp.change', function(e) {
			$inicial_fecha[31].data('DateTimePicker').minDate(e.date);
		});

		$inicial_fecha[31].on('dp.change', function(e) {
			$inicial_fecha[32].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[32].on('dp.change', function(e) {
			$inicial_fecha[33].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[33].on('dp.change', function(e) {
			$inicial_fecha[34].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[34].on('dp.change', function(e) {
			$inicial_fecha[35].data('DateTimePicker').minDate(e.date);
		});
		$inicial_fecha[35].on('dp.change', function(e) {
			$inicial_fecha[36].data('DateTimePicker').minDate(e.date);
		});

		$pago_fecha[1].on('dp.change', function(e) {
			$pago_fecha[2].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[2].on('dp.change', function(e) {
			$pago_fecha[3].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[3].on('dp.change', function(e) {
			$pago_fecha[4].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[4].on('dp.change', function(e) {
			$pago_fecha[5].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[5].on('dp.change', function(e) {
			$pago_fecha[6].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[6].on('dp.change', function(e) {
			$pago_fecha[7].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[7].on('dp.change', function(e) {
			$pago_fecha[8].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[8].on('dp.change', function(e) {
			$pago_fecha[9].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[9].on('dp.change', function(e) {
			$pago_fecha[10].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[10].on('dp.change', function(e) {
			$pago_fecha[11].data('DateTimePicker').minDate(e.date);
		});

		$pago_fecha[11].on('dp.change', function(e) {
			$pago_fecha[12].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[12].on('dp.change', function(e) {
			$pago_fecha[13].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[13].on('dp.change', function(e) {
			$pago_fecha[14].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[14].on('dp.change', function(e) {
			$pago_fecha[15].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[15].on('dp.change', function(e) {
			$pago_fecha[16].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[16].on('dp.change', function(e) {
			$pago_fecha[17].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[17].on('dp.change', function(e) {
			$pago_fecha[18].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[18].on('dp.change', function(e) {
			$pago_fecha[19].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[19].on('dp.change', function(e) {
			$pago_fecha[20].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[20].on('dp.change', function(e) {
			$pago_fecha[21].data('DateTimePicker').minDate(e.date);
		});

		$pago_fecha[21].on('dp.change', function(e) {
			$pago_fecha[22].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[22].on('dp.change', function(e) {
			$pago_fecha[23].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[23].on('dp.change', function(e) {
			$pago_fecha[24].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[24].on('dp.change', function(e) {
			$pago_fecha[25].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[25].on('dp.change', function(e) {
			$pago_fecha[26].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[26].on('dp.change', function(e) {
			$pago_fecha[27].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[27].on('dp.change', function(e) {
			$pago_fecha[28].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[28].on('dp.change', function(e) {
			$pago_fecha[29].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[29].on('dp.change', function(e) {
			$pago_fecha[30].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[30].on('dp.change', function(e) {
			$pago_fecha[31].data('DateTimePicker').minDate(e.date);
		});

		$pago_fecha[31].on('dp.change', function(e) {
			$pago_fecha[32].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[32].on('dp.change', function(e) {
			$pago_fecha[33].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[33].on('dp.change', function(e) {
			$pago_fecha[34].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[34].on('dp.change', function(e) {
			$pago_fecha[35].data('DateTimePicker').minDate(e.date);
		});
		$pago_fecha[35].on('dp.change', function(e) {
			$pago_fecha[36].data('DateTimePicker').minDate(e.date);
		});

			// disabled_date();
			// set_cuotas();
			// calcular_cuota(<?PHP echo $NRO_LINES; ?>);
	});





	// function saveData(x) {
	// 	f0 = $('#f0' + x).val();
	// 	f1 = $('#fecha' + x).val();
	// 	f2 = $('#fechap' + x).val();
	// 	f3 = $('#tipo' + x).val();
	// 	f4 = $('#monto' + x).val();

	// 	if (f1 == "") {
	// 		$('#fechaerror' + x).html("No puede estar vacio el campo");
	// 		$('#fechaerror' + x).parent('div').addClass('has-error');
	// 	} else {
	// 		$('#fechaerror' + x).html("");
	// 		$('#fechaerror' + x).parent('div').removeClass('has-error');
	// 	}

	// 	if (f2 == "") {
	// 		$('#fechaperror' + x).html("No puede estar vacio el campo");
	// 		$('#fechaperror' + x).parent('div').addClass('has-error');
	// 	} else {
	// 		$('#fechaperror' + x).html("");
	// 		$('#fechaperror' + x).parent('div').removeClass('has-error');
	// 	}
	// 	if (parseFloat(f4) <= 0 || isNaN(f4)) {
	// 		$('#montoerror' + x).html("Debe ser un número decimal positivo");
	// 		$('#montoerror' + x).parent('div').addClass('has-error');
	// 	} else {
	// 		$('#montoerror' + x).html("");
	// 		$('#montoerror' + x).parent('div').removeClass('has-error');
	// 	}
	// 	if (parseFloat(f4) > 0) {
	// 		saveData2(x);
	// 	}
	// }

	// function saveData2(x) {
	// 	console.log('asd')
	// 	datox = $("#fromii").serialize() + "&nro=" + x
	// 		$.ajax({
	// 			url: '?/cobrar/guardar_pago',
	// 			type: 'post',
	// 			data: "" + datox,
	// 			success: function(data) {
	// 				v = (data.trim()).split("|");
	// 				if (v[0] == "1") {
	// 					if (v[2] == "1" || v[2] == 1) {
	// 						$("#estado" + x).removeClass("text-danger");
	// 						$("#estado" + x).addClass("text-success");
	// 						$("#estado" + x).html("<b>Cancelado</b>");
	// 						$("#guardar" + x).html('');
	// 						$("#monto" + x).attr('disabled', 'disabled');
	// 						$("#imprimir" + x).html('<a href="?/cobrar/imprimir_comprobante/' + v[1] + '" target="_blank" data-toggle="tooltip" data-title="Imprimir comprobante"><span class="glyphicon glyphicon-print"></span></a>');
	// 						$("#f0" + x).val('' + v[1]);
	// 						bootbox.alert('Se ha guardado los cambios', );
	// 					} else {
	// 						$("#estado" + x).addClass("text-danger");
	// 						$("#estado" + x).removeClass("text-success");
	// 						$("#estado" + x).html("<b>Pendiente</b>");
	// 						$("#imprimir" + x).html('');
	// 						$("#f0" + x).val('' + v[1]);
	// 						bootbox.alert('Se ha guardado los cambios', );
	// 					}
	// 				}
	// 				setTimeout(()=>{
	// 					location.href='?'+window.location.href.split('?')[1];
	// 				},500);
	// 			},
	// 			error: function(XMLHttpRequest, textStatus, errorThrown) {},
	// 			complete: function(data) {}
	// 		});
	// }

	// function calcular_cuota(nroExt) {
	// 	var totalProductos = $('#totalProducto').val();

	// 	tot2 = 0;
	// 	for (i = 1; i <= nroExt; i++) {
	// 		tot2 += parseFloat($('#monto' + i).val());
	// 	}

	// 	tot = parseFloat(totalProductos);
	// 	nro = NRO_LINES - nroExt;
	// 	if (nro != 0) {
	// 		res = (tot - tot2) / nro;

	// 		for (i = nroExt + 1; i <= NRO_LINES; i++) {
	// 			if (i == NRO_LINES) {
	// 				res = tot - tot2 - (res.toFixed(1) * 1) * (i - (nroExt + 1));
	// 				$('#monto' + i).val(res.toFixed(2) + "0");
	// 			} else {
	// 				$('#monto' + i).val(res.toFixed(2) + "0");
	// 			}
	// 		}
	// 	}

	// 	var $compras = $('#cuotas_table tbody');
	// 	var $importes = $compras.find('[data-montocuota]');
	// 	var total = 0;
	// 	ic = 0;
	// 	reg = 0;
	// 	$importes.each(function(i) {
	// 		//if($('#estadohidden' + ic).val()=="1"){
	// 		importe = $.trim($(this).val());
	// 		importe = parseFloat(importe);
	// 		if (!isNaN(importe)) {
	// 			total = total + importe;
	// 		}
	// 		//alert(total +" --- "+ importe);
	// 		reg++;
	// 		//}
	// 		ic++;
	// 	});

	// 	$('#total_cuotas').html(total.toFixed(2));

	// 	// if (parseFloat(totalProductos) != parseFloat(total)) {
	// 	// 	if (parseFloat(totalProductos) > parseFloat(total)) {
	// 	// 		$("#conclusion").html("<b>La suma de las cuotas <br>y el costo del Ingreso <br>no coinciden</b><br>" + parseFloat(totalProductos) + " > " + parseFloat(total))
	// 	// 	} else {
	// 	// 		$("#conclusion").html("<b>La suma de las cuotas <br>y el costo del Ingreso <br>no coinciden</b><br>" + parseFloat(totalProductos) + " < " + parseFloat(total))
	// 	// 	}
	// 	// } else {
	// 	// 	$("#conclusion").html("")
	// 	// }
	// }




	// function change_date(x) {
	// 	if ($('#inicial_fecha_' + x).val() != "") {
	// 		if (x < 36) {
	// 			$('#inicial_fecha_' + (x + 1)).removeAttr("disabled");
	// 		}
	// 	} else {
	// 		for (i = x; i <= 35; i++) {
	// 			$('#inicial_fecha_' + (i + 1)).val("");
	// 			$('#inicial_fecha_' + (i + 1)).attr("disabled", "disabled");
	// 		}
	// 	}
	// }

	// function change_date2(x) {
	// 	if ($('#pago_fecha_' + x).val() != "") {
	// 		if (x < 36) {
	// 			$('#pago_fecha_' + (x + 1)).removeAttr("disabled");
	// 		}
	// 	} else {
	// 		for (i = x; i <= 35; i++) {
	// 			$('#pago_fecha_' + (i + 1)).val("");
	// 			$('#pago_fecha_' + (i + 1)).attr("disabled", "disabled");
	// 		}
	// 	}
	// }

	// function disabled_date() {
	// 	for (i = 1; i <= 35; i++) {
	// 		if ($('#pago_fecha_' + i).val() == "") {
	// 			$('#pago_fecha_' + (i + 1)).attr("disabled", "disabled");
	// 		}
	// 		if ($('#inicial_fecha_' + i).val() == "") {
	// 			$('#inicial_fecha_' + (i + 1)).attr("disabled", "disabled");
	// 		}
	// 	}
	// }

	// function set_cuotas() {
	// 	for (i = 1; i <= NRO_LINES; i++) {
	// 		$('[data-cuota=' + i + ']').css({
	// 			'height': 'auto',
	// 			'overflow': 'visible'
	// 		});
	// 		$('[data-cuota2=' + i + ']').css({
	// 			'margin-top': '10px;'
	// 		});
	// 		$('[data-cuota=' + i + ']').parent('td').css({
	// 			'height': 'auto',
	// 			'border-width': '1px',
	// 			'padding': '5px'
	// 		});
	// 	}
	// 	for (i = parseInt(NRO_LINES) + 1; i <= 36; i++) {
	// 		$('[data-cuota=' + i + ']').css({
	// 			'height': '0px',
	// 			'overflow': 'hidden'
	// 		});
	// 		$('[data-cuota2=' + i + ']').css({
	// 			'margin-top': '0px;'
	// 		});
	// 		$('[data-cuota=' + i + ']').parent('td').css({
	// 			'height': '0px',
	// 			'border-width': '0px',
	// 			'padding': '0px'
	// 		});
	// 	}
	// }

	// function visibleCell(x) {
	// 	$('[data-cuota=' + x + ']').css({
	// 		'height': 'auto',
	// 		'overflow': 'visible'
	// 	});
	// 	$('[data-cuota2=' + x + ']').css({
	// 		'margin-top': '10px;'
	// 	});
	// 	$('[data-cuota=' + x + ']').parent('td').css({
	// 		'height': 'auto',
	// 		'border-width': '1px',
	// 		'padding': '5px'
	// 	});
	// }

	// function DeleteCell(x) {
	// 	$('[data-cuota=' + x + ']').css({
	// 		'height': '0px',
	// 		'overflow': 'hidden'
	// 	});
	// 	$('[data-cuota2=' + x + ']').css({
	// 		'margin-top': '0px;'
	// 	});
	// 	$('[data-cuota=' + x + ']').parent('td').css({
	// 		'height': '0px',
	// 		'border-width': '0px',
	// 		'padding': '0px'
	// 	});
	// }

	// function AddCuota() {
	// 	NRO_LINES++;
	// 	visibleCell(NRO_LINES);
	// }

	// function DeleteCuota() {
	// 	id = $("#f0" + NRO_LINES).val();
	// 	bootbox.confirm('Está seguro que desea eliminar el producto?', function(result) {
	// 		if (result) {
	// 			if (id != 0) {
	// 				datox = "nro=" + id,
	// 					$.ajax({
	// 						url: '?/cobrar/eliminar_pago',
	// 						type: 'post',
	// 						data: "" + datox,
	// 						success: function(data) {
	// 							if (data == 1 || data == 2) {
	// 								$("#monto" + NRO_LINES).val("");
	// 								$("#inicial_fecha_" + NRO_LINES).val("");
	// 								$("#pago_fecha_" + NRO_LINES).val("");
	// 								$("#tipo" + NRO_LINES + " option[value='-']").attr("selected", true);
	// 								DeleteCell(NRO_LINES);
	// 								NRO_LINES--;
	// 								calcular_cuota(NRO_LINES);
	// 							}
	// 						},
	// 						error: function(XMLHttpRequest, textStatus, errorThrown) {
	// 								//alert(textStatus);
	// 							} //EINDE error
	// 							,
	// 						complete: function(data) {} //EINDE complete
	// 					});
	// 			} else {
	// 				$("#monto" + NRO_LINES).val("");
	// 				$("#inicial_fecha_" + NRO_LINES).val("");
	// 				$("#pago_fecha_" + NRO_LINES).val("");
	// 				$("#tipo" + NRO_LINES + " option[value='-']").attr("selected", true);
	// 				DeleteCell(NRO_LINES);
	// 				NRO_LINES--;
	// 				calcular_cuota(NRO_LINES);
	// 			}
	// 		}
	// 	});
	// }
</script>

<?php require_once show_template('footer-configured'); ?>