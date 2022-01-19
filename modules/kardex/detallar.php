<?php
// Obtiene los parametros
$id_almacen = (isset($params[0])) ? $params[0] : 0;
$id_producto = (isset($params[1])) ? $params[1] : 0;
// Obtiene los movimientos
$movimientos = $db->query("SELECT m.*,IFNULL(CONCAT(e.nombres,' ',e.paterno,' ', e.materno),'')AS empleado
		FROM(
			SELECT i.id_ingreso AS id_movimiento,d.id_detalle,i.fecha_ingreso AS fecha_movimiento,i.hora_ingreso AS hora_movimiento,i.descripcion,d.cantidad,d.costo AS monto,'i' AS tipo,i.empleado_id,i.almacen_id
			FROM inv_ingresos_detalles d
			LEFT JOIN inv_ingresos i ON d.ingreso_id=i.id_ingreso
			WHERE  transitorio = 0 AND d.producto_id='{$id_producto}'
			UNION
			SELECT e.id_egreso AS id_movimiento,d.id_detalle,e.fecha_egreso AS fecha_movimiento,e.hora_egreso AS hora_movimiento,e.descripcion,IF(e.anulado=3, 0, d.cantidad) as cantidad, IF(e.anulado=3, 0, d.precio) AS monto,'e' AS tipo,e.empleado_id,e.almacen_id
			FROM inv_egresos_detalles d
			LEFT JOIN inv_egresos e ON d.egreso_id=e.id_egreso
			WHERE d.producto_id='{$id_producto}' AND e.anulado != 3
		)m LEFT JOIN sys_empleados e ON m.empleado_id=e.id_empleado
		WHERE m.almacen_id='{$id_almacen}'
		ORDER BY m.fecha_movimiento ASC,m.hora_movimiento ASC")->fetch();
$tota_ingresos = $db->query("SELECT sum(d.cantidad) as total
			FROM inv_ingresos_detalles d
			LEFT JOIN inv_ingresos i ON d.ingreso_id=i.id_ingreso
			WHERE  transitorio = 0 AND  d.producto_id='{$id_producto}'")->fetch_first();
$tota_ingresos = ($tota_ingresos['total']) ? $tota_ingresos['total'] : 0;
$tota_egresos = $db->query("SELECT sum(d.cantidad) as total
			FROM inv_egresos_detalles d
			INNER JOIN inv_egresos e ON d.egreso_id=e.id_egreso
			WHERE d.producto_id='{$id_producto} AND e.anulado != 3'")->fetch_first();
$tota_egresos = ($tota_egresos['total']) ? $tota_egresos['total'] : 0;

// Verifica si existen movimientos
if (!$movimientos) {
	$tota_ingresos = 0;
	$tota_egresos = 0;
}
// Obtiene el almacen
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();
// Obtiene el producto
$producto = $db->from('inv_productos')->where('id_producto', $id_producto)->fetch_first();
require_once show_template('header-advanced');
?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Kardex valorado</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar de almacén o de producto hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/kardex/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-menu-left"></span>
				<span>Regresar</span>
			</a>
			<a href="?/kardex/imprimir/<?= $id_almacen; ?>/<?= $id_producto; ?>" target="_blank" class="btn btn-default">
				<span class="glyphicon glyphicon-print"></span>
				<span>Imprimir</span>
			</a>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-6">
			<div class="well">
				<h4 class="margin-none"><u>Producto</u></h4>
				<dl class="margin-none">
					<dt>Código:</dt>
					<dd><?= escape($producto['codigo']); ?></dd>
					<dt>Producto:</dt>
					<dd><?= escape($producto['nombre']); ?></dd>
					<dt>Precio:</dt>
					<dd>
						<a href="?/precios/ver/<?= $id_producto; ?>" target="_blank"><?= escape($producto['precio_actual']); ?></a>
					</dd>
					<dt>Cantidad total ingresos:</dt>
					<dd><?= escape($tota_ingresos); ?></dd>
					<dt>Cantidad total salidas:</dt>
					<dd><?= escape($tota_egresos); ?></dd>
				</dl>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="well">
				<h4 class="margin-none"><u>Almacén</u></h4>
				<dl class="margin-none">
					<dt>Almacén:</dt>
					<dd><?= escape($almacen['almacen']); ?></dd>
					<dt>Dirección:</dt>
					<dd><?= escape($almacen['direccion']); ?></dd>
					<dt>Principal:</dt>
					<dd><?= ($almacen['principal'] == 'S') ? 'Si' : 'No'; ?></dd>
				</dl>
			</div>
		</div>
	</div>
	<?php if ($movimientos) { ?>
		<h3 class="text-center">KARDEX VALORADO</h3>
		<div class="table-responsive">
			<table class="table table-bordered table-condensed table-striped table-hover">
				<thead>
					<tr class="active">
						<th class="text-nowrap text-center text-middle" rowspan="2">#</th>
						<th class="text-nowrap text-center text-middle" rowspan="2">Fecha</th>
						<th class="text-nowrap text-center text-middle" rowspan="2">Descripción</th>
						<th class="text-nowrap text-center text-middle" colspan="3">Entradas</th>
						<th class="text-nowrap text-center text-middle" colspan="3">Salidas</th>
						<th class="text-nowrap text-center text-middle" colspan="3">Saldos</th>
						<th class="text-nowrap text-center text-middle" rowspan="2">Empleado</th>
					</tr>
					<tr class="active">
						<th class="text-nowrap text-center text-middle">Cantidad</th>
						<th class="text-nowrap text-center text-middle">Costo</th>
						<th class="text-nowrap text-center text-middle">Total</th>
						<th class="text-nowrap text-center text-middle">Cantidad</th>
						<th class="text-nowrap text-center text-middle">Costo</th>
						<th class="text-nowrap text-center text-middle">Total</th>
						<th class="text-nowrap text-center text-middle">Cantidad</th>
						<th class="text-nowrap text-center text-middle">Costo</th>
						<th class="text-nowrap text-center text-middle">Total</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$saldo_cantidad = 0;
					$saldo_costo = 0;
					$ingresos = array();
					foreach ($movimientos as $nro => $movimiento) {
						if ($movimiento['tipo'] == 'i') :
							array_push($ingresos, array('cantidad' => $movimiento['cantidad'], 'costo' => $movimiento['monto']));
							$saldo_cantidad = $saldo_cantidad + $movimiento['cantidad'];
							$saldo_costo = $saldo_costo + ($movimiento['cantidad'] * $movimiento['monto']);
					?>
							<tr>
								<th class="text-nowrap"><?= $nro + 1; ?></th>
								<td class="text-nowrap">
									<span><?= escape($movimiento['fecha_movimiento']); ?></span>
									<span class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></span>
								</td>
								<td class="text-nowrap"><?= (escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén' : escape($movimiento['descripcion']); ?></td>
								<td class="text-nowrap text-right success text-primary"><strong><?= escape($movimiento['cantidad']); ?></strong></td>
								<td class="text-nowrap text-right success"><?= escape($movimiento['monto']); ?></td>
								<td class="text-nowrap text-right success"><strong><?= number_format(($movimiento['cantidad'] * $movimiento['monto']), 2, '.', ''); ?></strong></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right"></td>
								<td class="text-nowrap text-right info text-primary"><strong><?= $saldo_cantidad; ?></strong></td>
								<td class="text-nowrap text-right info"></td>
								<td class="text-nowrap text-right info"><strong><?= number_format($saldo_costo, 2, '.', ''); ?></strong></td>
								<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
							</tr>
							<?php
						else :
							/*$ciclo = true;
							do {*/
								$ingreso = array_shift($ingresos);
								if ($ingreso['cantidad'] >= $movimiento['cantidad']) {
									$ingreso['cantidad'] = $ingreso['cantidad'] - $movimiento['cantidad'];
									if ($ingreso['cantidad'] > 0) {
										array_unshift($ingresos, $ingreso);
									}
									$ciclo = false;
									$saldo_cantidad = $saldo_cantidad - $movimiento['cantidad'];
									$saldo_costo = $saldo_costo - ($movimiento['cantidad'] * $ingreso['costo']);
							?>
									<tr>
										<th class="text-nowrap"><?= $nro + 1; ?></th>
										<td class="text-nowrap">
											<span><?= escape($movimiento['fecha_movimiento']); ?></span>
											<span class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></span>
										</td>
										<td class="text-nowrap"><?= (escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén' : escape($movimiento['descripcion']); ?></td>
										<td class="text-nowrap text-right"></td>
										<td class="text-nowrap text-right"></td>
										<td class="text-nowrap text-right"></td>
										<td class="text-nowrap text-right danger text-primary"><strong><?= escape($movimiento['cantidad']); ?></strong></td>
										<td class="text-nowrap text-right danger"><?= escape($ingreso['costo']); ?></td>
										<td class="text-nowrap text-right danger"><strong><?= number_format(($movimiento['cantidad'] * $ingreso['costo']), 2, '.', ''); ?></strong></td>
										<td class="text-nowrap text-right info text-primary"><strong><?= $saldo_cantidad; ?></strong></td>
										<td class="text-nowrap text-right info"></td>
										<td class="text-nowrap text-right info"><strong><?= number_format($saldo_costo, 2, '.', ''); ?></strong></td>
										<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
									</tr>
								<?php
								} else {
								    if($ingreso['cantidad']){
								        $saldo_cantidad = $saldo_cantidad - $movimiento['cantidad'];
								        $cant_ingreso = $movimiento['cantidad'];
								        $cost_ingreso = $ingreso['costo'];
								        $sub_ingreso = $movimiento['cantidad'] * $ingreso['costo'];
								    }else{
								        $saldo_cantidad = $saldo_cantidad - $movimiento['cantidad'];
								        $cant_ingreso = $movimiento['cantidad'];
								        $cost_ingreso = $ingreso['costo'];
								        $sub_ingreso = $movimiento['cantidad'] * $ingreso['costo'];
								    }
									
									$saldo_costo = $saldo_costo - ($movimiento['cantidad'] * $ingreso['costo']);
								?>
									<tr>
										<th class="text-nowrap"><?= $nro + 1; ?></th>
										<td class="text-nowrap">
											<span><?= escape($movimiento['fecha_movimiento']); ?></span>
											<span class="text-primary"><?= escape($movimiento['hora_movimiento']); ?></span>
										</td>
										<td class="text-nowrap"><?= (escape($movimiento['descripcion']) == '') ? 'Ingreso de productos a almacén' : escape($movimiento['descripcion']); ?></td>
										<td class="text-nowrap text-right"></td>
										<td class="text-nowrap text-right"></td>
										<td class="text-nowrap text-right"></td>
										<td class="text-nowrap text-right danger text-primary"><strong><?= escape($cant_ingreso); ?></strong></td>
										<td class="text-nowrap text-right danger"><?= escape($cost_ingreso); ?></td>
										<td class="text-nowrap text-right danger"><strong><?= number_format(($sub_ingreso), 2, '.', ''); ?></strong></td>
										<td class="text-nowrap text-right info text-primary"><strong><?= $saldo_cantidad; ?></strong></td>
										<td class="text-nowrap text-right info"></td>
										<td class="text-nowrap text-right info"><strong><?= number_format($saldo_costo, 2, '.', ''); ?></strong></td>
										<td class="text-nowrap"><?= escape($movimiento['empleado']); ?></td>
									</tr>
					<?php
									$movimiento['cantidad'] = $movimiento['cantidad'] - $ingreso['cantidad'];
								}
							//} while ($ciclo);
						endif;
					}
					?>
				</tbody>
			</table>
		</div>
	<?php
	} else {
	?>
		<div class="alert alert-danger">
			<strong>Advertencia!</strong>
			<p>El kardex valorado no puede mostrarse por que no existen movimientos registrados.</p>
		</div>
	<?php
	}
	?>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
</script>
<?php require_once show_template('footer-advanced');
