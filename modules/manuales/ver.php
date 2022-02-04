<?php

// Obtiene el id_venta
$id_venta = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la venta
$venta = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('id_egreso', $id_venta)->fetch_first();

// Verifica si existe el egreso
if (!$venta || $venta['empleado_id'] != $_user['persona_id']) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los detalles
$detalles = $db->select('d.*, p.codigo, p.nombre, p.nombre_factura')->from('inv_egresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')->where('d.egreso_id', $id_venta)->order_by('id_detalle asc')->fetch();

// echo json_encode($detalles); die();
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la dosificacion del periodo actual
$dosificacion = $db->from('inv_dosificaciones')->where('fecha_limite', $venta['fecha_limite'])->fetch_first();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_eliminar = false; //in_array('eliminar', $permisos);
$permiso_suprimir = in_array('suprimir', $permisos);
$permiso_mostrar = in_array('mostrar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading" data-venta="<?= $id_venta; ?>" data-servidor="<?= ip_local . name_project . '/factura.php'; ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Detalle de venta manual</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_eliminar || $permiso_editar || $permiso_crear || $permiso_mostrar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_eliminar) { ?>
			<a href="?/manuales/eliminar/<?= $venta['id_egreso']; ?>" class="btn btn-danger" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span><span class="hidden-xs hidden-sm"> Eliminar</span></a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<button type="button" class="btn btn-warning" data-editar="true"><i class="glyphicon glyphicon-edit"></i><span class="hidden-xs"> Editar</span></button>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/manuales/crear" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span><span class="hidden-xs"> Crear venta manual</span></a>
			<?php } ?>
			<?php if ($permiso_mostrar) { ?>
			<a href="?/manuales/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs hidden-sm hidden-md"> Listado de mis ventas manuales</span></a>
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
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle de la venta</h3>
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
									<th class="text-nowrap text-center">Cantidad</th>
									<th class="text-nowrap text-center">Precio <?= escape($moneda); ?></th>									
									<th class="text-nowrap text-center">Importe <?= escape($moneda); ?></th>
									<?php // if ($permiso_suprimir) { ?>
									<!-- <th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th> -->
									<?php // } ?>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detalles as $nro => $detalle) { ?>
								<tr>
									<?php if($detalle['promocion_id'] != 1) { ?>
										<?php $cantidad = escape($detalle['cantidad']); ?>
										<?php $precio = escape($detalle['precio']);
										$pr = $db->select('*')->from('inv_productos a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad')->where('a.id_producto',$detalle['producto_id'])->fetch_first();
										if($pr['unidad_id'] == $detalle['unidad_id']){
											$unidad = $pr['unidad'];
										}else{
											$pr = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b', 'a.unidad_id = b.id_unidad AND a.visible = "s"')->where(array('a.producto_id'=>$detalle['producto_id'],'a.unidad_id'=>$detalle['unidad_id']))->fetch_first();
											$unidad = $pr['unidad'];
											$cantidad = $cantidad/$pr['cantidad_unidad'];
										}
										?>
										<?php $importe = $cantidad * $precio; ?>
										<?php $total = $total + $importe; ?>
										<th class="text-nowrap"><?= $nro + 1; ?></th>
										<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
										<td class="text-nowrap"><?= escape($detalle['nombre_factura']); ?></td>
										<!-- <td class="text-nowrap">
											<?php $lo = explode(',', $detalle['lote']);
												foreach ($lo as $key => $val) {
													$lt = explode('-',$val);
													$nl = $db->query("SELECT lote2 FROM inv_ingresos_detalles WHERE producto_id = ".$detalle['producto_id']." AND lote = '". $lt[0]."'")->fetch_first();
													$nl = ($nl['lote2']) ? $nl['lote2'] : '';
													echo '<b>'.$nl.'</b><br>';
												}
											?>
										</td> -->
										<td class="text-nowrap text-right"><?= $cantidad.' '.$unidad; ?></td>
										<td class="text-nowrap text-right"><?= $precio; ?></td>										
										<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
										<?php // if ($permiso_suprimir) { ?>
										<!-- <td class="text-nowrap text-center">
											<a href="?/manuales/suprimir/<?php // $venta['id_egreso']; ?>/<?php // $detalle['id_detalle']; ?>" data-toggle="tooltip" data-title="Eliminar detalle" data-suprimir="true"><span class="glyphicon glyphicon-trash"></span></a>
										</td> -->
										<?php // } ?>
									<?php } else { ?>
										<th class="text-nowrap"><?= $nro + 1; ?></th>
										<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
										<td class="text-nowrap" colspan="5"><?= escape($detalle['nombre_factura']); ?></td>
										<td class="text-nowrap"></td>
									<?php } ?>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="5">Monto total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right"><?= number_format($venta['monto_total'], 2, '.', ''); ?></th>
									<!-- <th class="text-nowrap text-right"></th> -->
								</tr>
								<?php if($venta['descuento_bs'] != 0){ ?>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="5">Descuento <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= escape($venta['descuento_porcentaje'] == 0 ? number_format($venta['descuento_bs'], 2, '.', '') : number_format($venta['descuento_bs'], 2, '.', '').' ('.$venta['descuento_porcentaje'].'%)'); ?></th>
									</tr>
									<tr class="active">
										<th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= number_format($venta['monto_total_descuento'], 2, '.', ''); ?></th>
									</tr>
								<?php } ?>
							</tfoot>
						</table>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Esta venta no tiene detalle, es muy importante que todos los egresos cuenten con un detalle que especifique la operación realizada.</p>
					</div>
					<?php } ?>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-log-out"></i> Información de la venta</h3>
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
							<label class="col-md-3 control-label">Número de autorización:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($venta['nro_autorizacion']); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Descripción:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= escape($venta['descripcion']); ?></p>
							</div>
						</div>
						<!-- <div class="form-group">
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
						</div>   -->
						<div class="form-group">
							<label class="col-md-3 control-label">Importe total:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= number_format(($venta['monto_total']-$venta['descuento_bs']) ,2 ) ?></p>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label">Forma de pago:</label>
							<div class="col-md-9">
								<p class="form-control-static"><?= ($venta['plan_de_pagos'] == 'si')?'Plan de pagos':'Pago completo' ?></p>
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
</div>

<!-- Inicio modal cliente -->
<?php if ($permiso_editar) { ?>
<div id="modal_cliente" class="modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/manuales/editar" id="form_cliente" class="modal-content">
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
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nro_factura">Número de factura:</label>
							<input type="text" name="nro_factura" value="<?= $venta['nro_factura']; ?>" id="nro_factura" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nro_autorizacion">Número de autorización:</label>
							<input type="text" name="nro_autorizacion" value="<?= $venta['nro_autorizacion']; ?>" id="nro_autorizacion" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
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

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/manuales/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la venta y todo su detalle?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_suprimir) { ?>
	$('[data-suprimir]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el detalle de la venta?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_editar) { ?>
	$.validate({
		modules: 'basic'
	});

	var $modal_cliente = $('#modal_cliente');
	var $form_cliente = $('#form_cliente');

	$modal_cliente.on('hidden.bs.modal', function () {
		$form_cliente.trigger('reset');
	});

	$modal_cliente.on('shown.bs.modal', function () {
		$modal_cliente.find('.form-control:first').focus();
	});

	$modal_cliente.find('[data-cancelar]').on('click', function () {
		$modal_cliente.modal('hide');
	});

	$('[data-editar]').on('click', function () {
		$modal_cliente.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>