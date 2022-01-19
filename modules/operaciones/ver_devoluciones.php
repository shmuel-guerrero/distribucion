<?php

// Obtiene el id_proforma
$id_tmp_reposiciones = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la proforma
$reposicion = $db->select("r.*, a.almacen, a.principal, upper(CONCAT(e.nombres,' ', e.paterno, ' ', e.materno)) AS empleado_v, upper(CONCAT(re.nombres,' ', re.paterno, ' ', re.materno)) AS empleado_r, c.cliente as cliente_c, c.nombre_factura as razon_social, uv.username as user_v, ur.username as user_r, IF (r.codigo_control = '' AND r.provisionado = 'S','Nota de venta',(IF (r.fecha_limite = '0000-00-00','Venta manual',(IF (r.codigo_control != '' and r.fecha_limite != '0000-00-00','Venta con factura',IF (r.estadoe > 1, 'Preventa','No se pudo identificar el tipo de venta')))))) as tipo")
						->from('tmp_reposiciones r')
						->join('inv_almacenes a', 'r.almacen_id = a.id_almacen', 'left')
						->join('sys_empleados e', 'r.empleado_id = e.id_empleado', 'left')
						->join('sys_empleados re', 're.id_empleado = r.empleado_id_reposicion', 'left')
						->join('sys_users uv', 'e.id_empleado = uv.persona_id')
                        ->join('sys_users ur', 're.id_empleado = ur.persona_id')
						->JOIN('inv_clientes c', 'c.id_cliente = r.cliente_id','left')
						->where('r.id_tmp_reposiciones', $id_tmp_reposiciones)->fetch_first();
// var_dump($reposiciones);die();
// Verifica si existe el proforma
if (!isset($reposicion)) { // || $reposicion['empleado_id'] != $_user['persona_id']
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los detalles
$detalles = $db->select('d.*, d.unidad_id as unidad_otra, p.codigo, p.nombre, p.nombre_factura')
				->from('tmp_reposiciones_detalles d')
				->join('inv_productos p', 'd.producto_id = p.id_producto', 'left')
				->where('d.tmp_reposiciones_id', $id_tmp_reposiciones)
				->order_by('id_tmp_detalle_reposicion asc')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = false; //in_array('crear', $permisos);
$permiso_editar = false; //in_array('editar', $permisos);
$permiso_eliminar = false; //in_array('eliminar', $permisos);
$permiso_mostrar = false; //in_array('mostrar', $permisos);
$permiso_reimprimir = false; //in_array('reimprimir', $permisos);

$permiso_devolucion = in_array('preventas_devolucion', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
<style>
.table-display > .thead > .tr,
.table-display > .tbody > .tr,
.table-display > .tfoot > .tr {
	margin-bottom: 15px;
}
.table-display > .thead > .tr > .th,
.table-display > .tbody > .tr > .th,
.table-display > .tfoot > .tr > .th {
	font-weight: bold;
}
@media (min-width: 768px) {
	.table-display {
		display: table;
	}
	.table-display > .thead,
	.table-display > .tbody,
	.table-display > .tfoot {
		display: table-row-group;
	}
	.table-display > .thead > .tr,
	.table-display > .tbody > .tr,
	.table-display > .tfoot > .tr {
		display: table-row;
	}
	.table-display > .thead > .tr > .th,
	.table-display > .thead > .tr > .td,
	.table-display > .tbody > .tr > .th,
	.table-display > .tbody > .tr > .td,
	.table-display > .tfoot > .tr > .th,
	.table-display > .tfoot > .tr > .td {
		display: table-cell;
	}
	.table-display > .tbody > .tr > .td,
	.table-display > .tbody > .tr > .th,
	.table-display > .tfoot > .tr > .td,
	.table-display > .tfoot > .tr > .th,
	.table-display > .thead > .tr > .td,
	.table-display > .thead > .tr > .th {
		padding-bottom: 15px;
		vertical-align: top;
	}
	.table-display > .tbody > .tr > .td:first-child,
	.table-display > .tbody > .tr > .th:first-child,
	.table-display > .tfoot > .tr > .td:first-child,
	.table-display > .tfoot > .tr > .th:first-child,
	.table-display > .thead > .tr > .td:first-child,
	.table-display > .thead > .tr > .th:first-child {
		padding-right: 15px;
	}
}
</style>
<div class="panel-heading" data-proforma="<?= $id_proforma; ?>" data-servidor="<?= ip_local . name_project . '/proforma.php'; ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Detalle de la reposicion</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_reimprimir || $permiso_eliminar || $permiso_editar || $permiso_crear || $permiso_mostrar || $permiso_devolucion) { ?>
	<div class="row">
		<div class="col-sm-3 hidden-xs">
			<div class="text-label">Seleccionar acción:</div>
		</div>
		<div class="col-xs-12 col-sm-9 text-right">			
			<?php if ($permiso_devolucion) { ?>
				<a href="?/operaciones/preventas_listar" class="btn btn-success"><span class="glyphicon glyphicon-list"></span><span class="hidden-xs"> Listado de preventas</span></a>				
			<?php } ?>
			<?php if ($permiso_devolucion) { ?>
				<a href="?/operaciones/bajas_devoluciones" class="btn btn-info"><span class="glyphicon glyphicon-sort"></span><span class="hidden-xs"> Listado de devoluciones</span></a>				
			<?php } ?>
			<?php if ($permiso_reimprimir) { ?>
			<button type="button" class="btn btn-info" data-reimprimir="true"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Reimprimir</span></button>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/proformas/eliminar/<?= $reposicion['id_tmp_reposiciones']; ?>" class="btn btn-danger" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span><span class="hidden-xs"> Eliminar</span></a>
			<?php } ?>
			<?php if ($permiso_editar) { ?>
			<button type="button" class="btn btn-warning" data-editar="true"><i class="glyphicon glyphicon-edit"></i><span class="hidden-xs"> Editar datos del cliente</span></button>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/proformas/crear" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span><span class="hidden-xs hidden-sm"> Crear proforma</span></a>
			<?php } ?>
			<?php if ($permiso_mostrar) { ?>
			<a href="?/proformas/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs hidden-sm"> Listado de proformas</span></a>
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
		<div class="col-md-4">
			<div class="panel panel-info">
				<div class="panel-heading">
					<h3 class="panel-title">
						<span class="glyphicon glyphicon-menu-hamburger"></span>
						<span>Información sobre la transacción</span>
					</h3>
				</div>
				<div class="panel-body">
					<div class="table-display">
						<div class="tbody">
							<div class="tr">
								<div class="th">Fecha y hora de devoluci&oacute;n:</div>
								<div class="td"><?= escape(date_decode($reposicion['fecha_reposicion'], $_institution['formato']))?> <br> <small class="text-success"><?= escape($reposicion['hora_reposicion']); ?></small></div>
							</div>
							<div class="tr">
								<div class="th">Fecha y hora de venta:</div>
								<div class="td"><?= escape(date_decode($reposicion['fecha_egreso'], $_institution['formato']))?> <br> <small class="text-success"><?= escape($reposicion['hora_egreso']); ?></small></div>
							</div>
							<div class="tr">
								<div class="th">Tipo de venta:</div>
								<div class="td"><?= escape($reposicion['tipo']); ?></div>
							</div>
							<div class="tr">
								<div class="th">Monto total:</div>
								<div class="td"><?= escape($reposicion['monto_total']); ?></div>
							</div>
							<div class="tr">
								<div class="th">Número de registros:</div>
								<div class="td"><?= escape($reposicion['nro_registros']); ?></div>
							</div>
							<div class="tr">
								<div class="th">Observaci&oacute;n:</div>
								<div class="td"><?= escape($reposicion['observacion']); ?></div>
							</div>
							<div class="tr">
								<div class="th">Descripci&oacute;n:</div>
								<div class="td"><?= escape($reposicion['descripcion']); ?></div>
							</div>
							<div class="tr">
								<div class="th">Cliente:</div>
								<div class="td"><?= escape($reposicion['nombre_cliente']); ?></div>
							</div>
							<div class="tr">
								<div class="th">NIT / CI:</div>
								<div class="td"><?= escape($reposicion['nit_ci']); ?></div>
							</div>							
							<div class="tr">
								<div class="th">Almacén de origen:</div>
								<div class="td"><?= escape($reposicion['almacen']); ?></div>
							</div>
							<div class="tr">
								<div class="th">Vendedor:</div>
								<div class="td"><?= escape($reposicion['empleado_v']); ?> <br> <small class="text-success">Usuario: <?= escape($reposicion['user_v']); ?></small></div>
							</div>
							<div class="tr">
								<div class="th">Empleado que registr&oacute; la devoluci&oacute;n:</div>
								<div class="td"><?= escape($reposicion['empleado_r']); ?> <br> <small class="text-success">Usuario: <?= escape($reposicion['user_r']); ?></small></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="panel panel-info">
				<div class="panel-heading">
					<h3 class="panel-title">
						<span class="glyphicon glyphicon-option-vertical"></span>
						<span>Detalle de la devolución</span>
					</h3>
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
									<th class="text-nowrap">Cantidad</th>
									<th class="text-nowrap">Precio <?= escape($moneda); ?></th>									
									<th class="text-nowrap">Subtotal <?= escape($moneda); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php $total = 0; ?>
								<?php foreach ($detalles as $nro => $detalle) { ?>
								<tr>
									<?php 
									$cantidad = escape($detalle['cantidad']); 
									// $cantidad = escape($detalle['cantidad']/cantidad_unidad($db,$detalle['producto_id'],$detalle['unidad_otra']));
									?>
									<?php $precio = escape($detalle['precio']); ?>
									<?php $importe = $cantidad * $precio; ?>
									<?php $total = $total + $importe; ?>
									<th class="text-nowrap"><?= $nro + 1; ?></th>
									<td class="text-nowrap"><?= escape($detalle['codigo']); ?></td>
									<td class="text-nowrap"><?= escape($detalle['nombre_factura']); ?></td>
									<td class="text-nowrap text-right"><?= $cantidad . ' '. nombre_unidad($db,$detalle['unidad_otra']); ?></td>
									<td class="text-nowrap text-right"><?= $precio; ?></td>									
									<td class="text-nowrap text-right"><?= number_format($importe, 2, '.', ''); ?></td>
								</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-lefth" colspan="5">Monto total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right"><?= number_format($reposicion['monto_total'], 2, '.', ''); ?></th>
								</tr>
								<?php if($reposicion['descuento_bs'] != 0){?>
									<!-- <tr class="active">
										<th class="text-nowrap text-lefth" colspan="5">Descuento <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= number_format($reposicion['descuento_bs'], 2, '.', ''); ?><?= isset($reposicion['descuento_porcentaje']) ? ' ('.$reposicion['descuento_porcentaje'].'%)' : ''; ?></th>
									</tr>
									<tr class="active">
										<th class="text-nowrap text-lefth" colspan="5">Importe total <?= escape($moneda); ?></th>
										<th class="text-nowrap text-right"><?= number_format($reposicion['monto_total_descuento'], 2, '.', ''); ?></th>
									</tr> -->
								<?php } ?>
							</tfoot>
						</table>
					</div>
					<?php } else { ?>
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Esta devolución no tiene detalle, es muy importante que todas las reposiciones cuenten con un detalle que especifique la operación realizada.</p>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Inicio modal cliente -->
<?php if ($permiso_editar) { ?>
<div id="modal_cliente" class="modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/proformas/editar" id="form_cliente" class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Editar datos del cliente</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nit_ci">NIT / CI:</label>
							<input type="text" name="id_proforma" value="<?= $reposicion['id_tmp_reposiciones']; ?>" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" name="nit_ci" value="<?= $reposicion['nit_ci']; ?>" id="nit_ci" class="form-control" autocomplete="off" data-validation="required number">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nombre_cliente">Señor(es):</label>
							<input type="text" name="nombre_cliente" value="<?= $reposicion['nombre_cliente']; ?>" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./& " data-validation-length="max100">
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
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('&iquest;Está seguro que desea eliminar la devolución y todo su detalle?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_reimprimir) { ?>
	var id_proforma = $('[data-proforma]').attr('data-proforma');

	$('[data-reimprimir]').on('click', function () {
		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/proformas/obtener',
			data: {
				id_proforma: id_proforma
			}
		}).done(function (proforma) {
			if (proforma) {
				var servidor = $.trim($('[data-servidor]').attr('data-servidor'));

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: servidor,
					data: proforma
				}).done(function (respuesta) {
					switch (respuesta.estado) {
						case 'success':
							$('#loader').fadeOut(100);
							$.notify({
								title: '<strong>Operación satisfactoria!</strong>',
								message: '<div>Imprimiendo proforma...</div>'
							}, {
								type: 'success'
							});
							break;
						default:
							$('#loader').fadeOut(100);
							$.notify({
								title: '<strong>Advertencia!</strong>',
								message: '<div>La impresora no responde, asegurese de que este conectada y vuelva a intentarlo nuevamente.</div>'
							}, {
								type: 'danger'
							});
							break;
					}
				}).fail(function () {
					$('#loader').fadeOut(100);
					$.notify({
						title: '<strong>Error!</strong>',
						message: '<div>Ocurrió un problema en el envio de la información, vuelva a intentarlo nuevamente y si persiste el problema contactese con el administrador.</div>'
					}, {
						type: 'danger'
					});
				});
			} else {
				$('#loader').fadeOut(100);
				$.notify({
					title: '<strong>Error!</strong>',
					message: '<div>Ocurrió un problema al obtener los datos de la devolución.</div>'
				}, {
					type: 'danger'
				});
			}
		}).fail(function () {
			$('#loader').fadeOut(100);
			$.notify({
				title: '<strong>Error!</strong>',
				message: '<div>Ocurrió un problema al obtener los datos de la devolución.</div>'
			}, {
				type: 'danger'
			});
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
<?php require_once show_template('footer-advanced'); ?>