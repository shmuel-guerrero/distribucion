<?php
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el id_control
$IdEgreso = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene la venta
$DetalleVenta = $db->query("SELECT c.id_control,e.id_egreso,e.fecha_egreso,e.almacen_id,cl.id_cliente,cl.cliente,cl.nit,cl.tipo,cl.telefono,cl.direccion,em.nombres,em.paterno,em.materno
		FROM inv_control AS c
		LEFT JOIN inv_egresos AS e ON e.id_egreso=c.egreso_id
		LEFT JOIN inv_clientes AS cl ON cl.id_cliente=e.cliente_id
		LEFT JOIN sys_empleados AS em ON em.id_empleado=e.empleado_id
		WHERE e.id_egreso='{$IdEgreso}'
		GROUP BY c.egreso_id ORDER BY e.id_egreso DESC")->fetch_first();
// Verifica si existe el egreso
if (!$DetalleVenta) {
	// Error 404
	require_once not_found();
	exit;
}

$Controles=$db->query("SELECT u.unidad,
			m.nombre,m.precio,
			c.id_control,c.cantidad,c.cantidad_inicial,c.estado,m.precio
		FROM inv_control AS c
		LEFT JOIN inv_materiales AS m ON m.id_materiales=c.id_materiales
		LEFT JOIN inv_unidades AS u ON u.id_unidad=m.id_unidad
		WHERE c.egreso_id='{$IdEgreso}'")->fetch();

$Productos=$db->query("SELECT p.codigo,p.nombre,ed.precio,ed.cantidad,u.unidad
		FROM inv_egresos_detalles AS ed
		LEFT JOIN inv_productos AS p ON p.id_producto=ed.producto_id
		LEFT JOIN inv_unidades AS u ON u.id_unidad=ed.unidad_id
		WHERE ed.egreso_id='{$IdEgreso}'")->fetch();
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

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
			<div class="text-label">Para regresar al listado hacer clic en el siguiente botón:</div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/cobrar/listar_envio" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Listado</span>
			</a>
		</div>
	</div>
	<hr>

		<div class="row">
			<div class="col-sm-10 col-sm-offset-1">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle de Control de Materiales</h3>
					</div>
					<div class="panel-body">
						<?php if($DetalleVenta) { ?>
							<div class="table-responsive">
								<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
									<thead>
										<tr class="active">
											<th class="text-nowrap">#</th>
											<th class="text-nowrap">Nombre</th>
											<th class="text-nowrap">Unidad</th>
											<th class="text-nowrap">Estado</th>
											<th class="text-nowrap">Cantidad Actual</th>
											<th class="text-nowrap">Devolucion</th>
											<th class="text-nowrap">Venta</th>
											<th class="text-nowrap">Cantidad Inicial</th>
											<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
											<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
											$TotalControl=0;
											foreach($Controles as $Fila=>$Control):
										?>
										<tr class="active">
											<th class="text-nowrap"><?=$Fila+1?></th>
											<th class="text-nowrap"><?=$Control['nombre']?></th>
											<th class="text-nowrap"><?=$Control['unidad']?></th>
											<th class="text-nowrap"><?=$Control['estado']?></th>
											<th class="text-nowrap text-center"><?=$Control['cantidad']?></th>
											<th class="text-nowrap">
												<?php
													if($Control['estado']=='pendiente'):
												?>
												<input type='hidden' form='FormularioF' name='idcontrol[]' value='<?=$Control['id_control']?>'>
												<input type='number' form='FormularioF' class='form-control text-left' style='width:90px;float:right' name='cantidad[]' min='0' max='<?=$Control['cantidad']?>' value='0' required>
												<?php
													else:
														echo "<center>{$Control['cantidad']}</center>";
													endif;
												?>
											</th>
											<th class="text-nowrap">
												<?php
													if($Control['estado']=='pendiente'):
												?>
												<input type='hidden' form='FormularioF' name='preciov[]' value='<?=$Control['precio']?>'>
												<input type='number' form='FormularioF' class='form-control text-left' style='width:90px;float:right' name='cantidadv[]' min='0' max='<?=$Control['cantidad']?>' value='0' data-toggle='tooltip' data-placement='top' id='Venta<?=$Fila?>' onchange="precioTotal(<?=$Fila?>,this.value,<?=$Control['precio']?>)" required>
												<?php
													else:
														echo "<center>{$Control['cantidad']}</center>";
													endif;
												?>
											</th>
											<th class="text-nowrap text-center"><?=$Control['cantidad_inicial']?></th>
											<th class="text-nowrap text-center"><?=$Control['precio']?></th>
											<th class="text-nowrap text-center">
												<?php
													if($Control['estado']!='pendiente'):
														$SubTotal=$Control['cantidad_inicial']*$Control['precio'];
														echo $SubTotal;
														$TotalControl+=$SubTotal;
													else:
														echo '0.00';
													endif;
												?>
											</th>
										</tr>
										<?php
											endforeach;
										?>
									</tbody>
									<tfoot>
										<tr class="active">
											<th class="text-nowrap text-right" colspan="9">IMPORTE TOTAL <?= escape($moneda); ?></th>
											<th class="text-nowrap text-center"><?= number_format($TotalControl, 2, '.', ''); ?></th>
										</tr>
									</tfoot>
								</table>
								<form id='FormularioF' method='POST' action='?/cobrar/guardar_entrega'>
									<input type='hidden' name='IdAlmacen' value='<?=$DetalleVenta['almacen_id']?>'>
									<input type='hidden' name='IdCliente' value='<?=$DetalleVenta['id_cliente']?>'>
									<center>
										<button class='btn btn-primary'>Recibir Material</button>
									</center>
								</form>
							</div>
						<?php } else { ?>
							<div class="alert alert-danger">
								<strong>Advertencia!</strong>
								<p>Esta nota de remisión no tiene detalle, es muy importante que todos los egresos cuenten con un detalle que especifique la operación realizada.</p>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="col-sm-10 col-sm-offset-1">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="glyphicon glyphicon-list"></i> Detalle de Egresos</h3>
					</div>
					<div class="panel-body">
						<?php if($Productos) { ?>
							<div class="table-responsive">
								<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
									<thead>
										<tr class="active">
											<th class="text-nowrap">#</th>
											<th class="text-nowrap">Nombre</th>
											<th class="text-nowrap">Codigo</th>
											<th class="text-nowrap">Unidad</th>
											<th class="text-nowrap">Cantidad</th>
											<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
											<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
											$TotalProducto=0;
											foreach($Productos as $Fila=>$Producto):
												$TotalProducto+=$Producto['cantidad']*$Producto['precio'];
										?>
										<tr>
											<td><?=$Fila+1?></td>
											<td><?=$Producto['nombre']?></td>
											<td><?=$Producto['codigo']?></td>
											<td><?=$Producto['unidad']?></td>
											<td><?=$Producto['cantidad']?></td>
											<td><?=$Producto['precio']?></td>
											<td><?=$Producto['cantidad']*$Producto['precio']?></td>
										</tr>
										<?php
											endforeach;
										?>
									</tbody>
									<tfoot>
										<tr class="active">
											<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
											<th class="text-nowrap text-right"><?= number_format($TotalProducto, 2, '.', ''); ?></th>
										</tr>
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
			</div>
			<div class="col-sm-10 col-sm-offset-1">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="glyphicon glyphicon-log-out"></i> Información del Control de Materiales</h3>
					</div>
					<div class="panel-body">
						<div class="form-horizontal">
							<div class="form-group">
								<label class="col-md-3 control-label">Tipo:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($DetalleVenta['tipo']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Cliente:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($DetalleVenta['cliente']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">NIT / CI:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($DetalleVenta['nit']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Telefono:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($DetalleVenta['telefono']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Direccion:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($DetalleVenta['direccion']); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Empleado:</label>
								<div class="col-md-9">
									<p class="form-control-static"><?= escape($DetalleVenta['nombres'] . ' ' . $detalle['paterno'] . ' ' . $detalle['materno']); ?></p>
								</div>
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
	function precioTotal(elemento,cantidad,precio){
		let Elemento=document.getElementById('Venta'+elemento);
		let Total=cantidad*precio;
		Elemento.title=`Total: ${Total}`;
	}
</script>
<?php
	require_once show_template('footer-configured');