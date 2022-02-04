<?php

// Obtiene los parametros
$id_ruta = (isset($params[0])) ? $params[0] : 0;

// Obtiene los movimientos
//$movimientos = $db->query("select m.*, ifnull(concat(e.nombres, ' ', e.paterno, ' ', e.materno), '') as empleado from (select i.id_ingreso as id_movimiento, d.id_detalle, i.fecha_ingreso as fecha_movimiento, i.hora_ingreso as hora_movimiento, i.descripcion, d.cantidad, d.costo as monto, 'i' as tipo, i.empleado_id, i.almacen_id from inv_ingresos_detalles d left join inv_ingresos i on d.ingreso_id = i.id_ingreso where d.producto_id = $id_producto union select e.id_egreso as id_movimiento, d.id_detalle, e.fecha_egreso as fecha_movimiento, e.hora_egreso as hora_movimiento, e.descripcion, d.cantidad, d.precio as monto, 'e' as tipo, e.empleado_id, e.almacen_id from inv_egresos_detalles d left join inv_egresos e on d.egreso_id = e.id_egreso where d.producto_id = $id_producto) m left join sys_empleados e on m.empleado_id = e.id_empleado where m.almacen_id = $id_almacen order by m.fecha_movimiento asc, m.hora_movimiento asc")->fetch();
//obtiene la ruta
$ruta = $db->select('*')->from('gps_rutas')->where('id_ruta', $id_ruta)->fetch_first();

$historiales = $db->select('*')->from('gps_historial_asinacion')->join('sys_empleados','empleado_id = id_empleado')->where('ruta_id',$id_ruta)->fetch();

// Verifica si existen movimientos
if (!$ruta) {
	// Error 404
	require_once not_found();
	exit;
}


?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Historial de la ruta</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar de opción o de producto hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/control/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-menu-left"></span>
				<span>Regresar</span>
			</a>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-6">
			<div class="well">
				<h4 class="margin-none"><u>Ruta</u></h4>
				<dl class="margin-none">
					<dt>Nombre:</dt>
					<dd> - <?= escape($ruta['nombre']); ?></dd>
					<dt>Fecha:</dt>
					<dd> - <?= escape($ruta['fecha']); ?></dd>
				</dl>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="well">
				<h4 class="margin-none"><u></u></h4>
				<dl class="margin-none">
					<dt>Empresa:</dt>
					<dd>- <?php if($ruta['estado']==1){echo $_institution['empresa1'];}else{echo $_institution['empresa2'];} ?></dd>
					<dt></dt>
					<dd></dd>
					<dt></dt>
					<dd></dd>
				</dl>
			</div>
		</div>
	</div>
	<?php if ($historiales) { ?>
	<h3 class="text-center">EMPLEADOS ASIGNADOS</E></h3>
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped table-hover">
			<thead>
				<tr class="active">
					<th class="text-nowrap text-center text-middle" >#</th>
                    <th class="text-nowrap text-center text-middle" >Empleado</th>
                    <th class="text-nowrap text-center text-middle" >Empresa</th>
					<th class="text-nowrap text-center text-middle" >Fecha inicio</th>
                    <th></th>
					<th class="text-nowrap text-center text-middle" >Fecha final</th>
				</tr>
			</thead>
			<tbody>
				<?php $saldo_cantidad = 0; ?>
				<?php $saldo_costo = 0; ?>
				<?php $ingresos = array(); ?>
				<?php foreach ($historiales as $nro => $historial) { ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
                        <td class="text-nowrap">
                            <span><?= escape($historial['nombres'].' '.$historial['paterno'].' '.$historial['materno']); ?></span>
                        </td>
                        <td class="text-nowrap">
                            <span><?php if($historial['cargo']==1){echo $_institution['empresa1'];}else{echo $_institution['empresa2'];} ?></span>
                        </td>
                        <td class="text-nowrap text-right success text-primary text-center"><strong><?= escape($historial['fecha_ini']); ?></strong></td>
                        <td class="text-center">-</td>
                        <td class="text-nowrap text-right success text-primary text-center"><strong><?= escape($historial['fecha_fin']); ?></strong></td>
					</tr>

				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>El historial no puede mostrarse por que no existen movimientos registrados.</p>
	</div>
	<?php } ?>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
/*$(function () {
	var table = $('#table').DataFilter({
		filter: true,
		name: 'reporte_de_existencias',
		reports: 'excel|word|pdf|html'
	});
});*/
</script>
<?php require_once show_template('footer-configured'); ?>