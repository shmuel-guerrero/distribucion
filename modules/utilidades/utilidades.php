<?php
$costoSSST = 0;
$importeSSST = 0;

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
// $query = "SELECT ed.*, e.tipo, p.nombre, P.promocion, u.unidad, SUM(precio*cantidad/IF(a.cantidad_unidad IS NULL, 1, a.cantidad_unidad)) AS importeAcumul, 
// 				SUM(cantidad)AS cantidadAcumul, GROUP_CONCAT(ed.lote) as loten
//             FROM inv_egresos_detalles ed
//             LEFT JOIN inv_egresos e ON ed.egreso_id = e.id_egreso AND e.tipo='Venta' AND e.anulado != 1
//             LEFT JOIN inv_asignaciones a ON ed.asignacion_id = a.id_asignacion
//             LEFT JOIN inv_productos p ON ed.producto_id = p.id_producto
//             LEFT JOIN inv_unidades u ON ed.unidad_id = u.id_unidad
//             WHERE e.fecha_egreso BETWEEN '$fecha_inicial' AND '$fecha_final'
//             GROUP BY p.id_producto, u.id_unidad ";
$query = "SELECT vd.*, p.codigo as codprod, p.nombre, p.promocion, u.unidad, SUM(cantidad)AS cantidadAcumul, (SUM(precio*cantidad/IF(a.cantidad_unidad IS NULL, 1, a.cantidad_unidad)) - (v.descuento_bs/v.nro_registros)) AS importeAcumul
		, GROUP_CONCAT(vd.lote) as loten, GROUP_CONCAT(vd.precio,'|',vd.lote) as precios, GROUP_CONCAT(vd.cantidad) as cantidades, c.categoria, v.descuento_bs, v.nro_registros
		FROM inv_productos p
		INNER JOIN inv_egresos_detalles vd ON vd.producto_id = p.id_producto
		INNER JOIN inv_egresos v ON vd.egreso_id=v.id_egreso
		LEFT JOIN inv_asignaciones a ON a.producto_id = vd.producto_id AND a.unidad_id = vd.unidad_id  AND a.visible = 's' 
		LEFT JOIN inv_unidades u ON u.id_unidad = vd.unidad_id
		LEFT JOIN inv_categorias c ON p.categoria_id = c.id_categoria
		WHERE v.fecha_egreso between '$fecha_inicial' and '$fecha_final' AND v.tipo='Venta' and v.anulado != 3 AND a.visible = 's' 
		GROUP BY p.id_producto, u.id_unidad";
$ventas = $db->query($query)->fetch();

// echo $db->last_query();

//var_dump($ventas);
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_cambiar = true;

$costoTotalAcumulado = 0;
$precioTotalAcumulado = 0;
$utilidadTotalAcumulado = 0;

?>
<?php require_once show_template('header-advanced'); ?>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<strong>Reporte de Utilidad Neta</strong>
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
					<th class="text-nowrap">Codigo</th>
					<th class="text-nowrap">Producto</th>
					<th class="text-nowrap">Categoria</th>
					
					<!-- <th class="text-nowrap">Cantidad compra</th> -->
					<th class="text-nowrap">Costo unitario <?= $moneda;?></th>
					<th class="text-nowrap">Unidad compra</th>
					
					<th class="text-nowrap">Cantidad venta</th>
                    <th class="text-nowrap">Unidad venta</th>
					<!-- <th class="text-nowrap">Precio <?= $moneda;?></th> -->					
                    <!-- <th class="text-nowrap">Unidad</th> -->
					
					<th class="text-nowrap">Costo Total <?= $moneda;?></th>
					<th class="text-nowrap">Precio Total <?= $moneda;?></th>
					<th class="text-nowrap">Utilidad</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="active">
					<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Codigo</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Producto</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>

					<!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad compra</th> -->
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Costo unitario</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad compra</th>

					<th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad venta</th>
                    <th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad venta</th>
					<!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">Precio</th> -->					
                    <!-- <th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th> -->
					
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Costo Total</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio Total</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Utilidad</th>
				</tr>
			</tfoot>
			<tbody>
				<?php $total = 0; ?>
                <?php foreach($ventas as $key => $venta) { ?>
                    <?php $precio_total = ($venta['importeAcumul']); ?>
                    <?php 	$id_pro = $venta['producto_id'];

							$lotes = explode(",", $venta['loten']);
							$para = ''; $ant=0; $cos = 0;
							$mostrar = array();
							$totalCompra=0;
							$c = 0;

							foreach ($lotes as $i => $lote) {
								$l = explode('-', $lote);
								$lo = $l[0];
								$ca = $l[1];

								$deta = $db->select('d.*, u.unidad')->from('inv_ingresos_detalles d')->join('inv_productos p', 'd.producto_id = p.id_producto')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->where('d.lote', $lo)->where('d.producto_id', $venta['producto_id'])->fetch_first();
								if ($cos != $deta['costo']) {
									$cos = $deta['costo'];
								}

								if(count($mostrar) == 0){
									$mostrar[$c][1] = $lo;
									$mostrar[$c][2] = $ca;
									$mostrar[$c][3] = $cos;
									//$c++;
								}else{
									$sw = 0;
									$sw1 = 0;
									foreach($mostrar as &$mostrar1){
										if($mostrar1[3] == $cos){
											$mostrar1[2] = $mostrar1[2] + $ca;

											if($mostrar1[3] != $cos){
												$mostrar1[3] = $cos;
											}
											$sw = 1;
										}
									}
									if($sw == 0){
										$c++;
										$mostrar[$c][1] = $lo;
										$mostrar[$c][2] = $ca;
										$mostrar[$c][3] = $cos;
									}
								}
							}

							$content = '';
							$costoCantidad = '';
							foreach($mostrar as $item){

								$costoCantidad .= ' (' . $item[2] .')'.$deta['unidad'].':'. $item[3] .'&#10; ';

								$totalCompra = $totalCompra + ($item[2] * $item[3]);
							}
						;?>
						<?php 
							// $descuento = ($venta['descuento_bs'] != 0 ? $venta['descuento_bs'] / $venta['nro_registros'] : 0)
							// $importe = $venta['precio'] - $descuento;
						?>
                    <tr>
                        <td><?= $key+1 ?></td>
                        <td><?= $venta['codprod'] ?></td>
                        <td><?= $venta['nombre'] ?></td>
                        <td><?= $venta['categoria'] ?></td>
						
						<!-- <td><?= $venta['cantidadAcumul'] ?></td> -->
						<td data-toggle="tooltip" title="<?=$costoCantidad ?>" ><?= number_format($totalCompra/$venta['cantidadAcumul'] ,2) ?> </td>
						<td><?= $deta['unidad'] ?></td>

                        <td><?= $venta['cantidadAcumul']/cantidad_unidad($db, $venta['producto_id'] , $venta['unidad_id'])?></td>
						<td><?= '('.cantidad_unidad($db, $venta['producto_id'] , $venta['unidad_id']).') '.$venta['unidad'] ?></td>
                        
                        <!-- <td><?= $venta['precio'] ?></td> -->
						<!-- <td><?= $descuento ?></td> -->
						<!-- <td><?= $venta['precio'] ?></td> -->
                        <td class="text-right" data-costo="<?= $totalCompra ?>"><?= number_format($totalCompra ,2) ?></td>
                        <td class="text-right" data-precio="<?= $precio_total ?>"><?= number_format($precio_total ,2) ?></td>
                        <td class="text-right <?= (($precio_total - $totalCompra) <= 0)? 'text-danger':'text-success' ?>" data-total="<?= ($precio_total - $totalCompra) ?>"><b><?= number_format(($precio_total - $totalCompra) ,2) ?></b></td>
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
		<div class="col-sm-4">
			<p class="lead margin-none">
				<b>Costo Total:</b>
				<u id="costototal">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-4">
			<p class="lead margin-none">
				<b>Precio Total:</b>
				<u id="preciototal">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-4">
			<p class="lead margin-none">
				<b>Utilidad Total:</b>
				<u id="total">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="clearfix">
		</div>
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
	<script src="<?= js; ?>/jquery.base64.js"></script>
	<script src="<?= js; ?>/pdfmake.min.js"></script>
	<script src="<?= js; ?>/vfs_fonts.js"></script>
	<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
	<script src="<?= js; ?>/moment.min.js"></script>
	<script src="<?= js; ?>/moment.es.js"></script>
	<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
	<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
	<script>
		$(function() {
			<?php if ($permiso_cambiar) { ?>
				var formato = $('[data-formato]').attr('data-formato');
				var mascara = $('[data-mascara]').attr('data-mascara');
				var gestion = $('[data-gestion]').attr('data-gestion');
				var $inicial_fecha = $('#inicial_fecha');
				var $final_fecha = $('#final_fecha');

				$.validate({
					form: '#form_fecha',
					modules: 'date',
					onSuccess: function() {
						var inicial_fecha = $.trim($('#inicial_fecha').val());
						var final_fecha = $.trim($('#final_fecha').val());
						var vacio = gestion.replace(new RegExp('9', 'g'), '0');

						inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
						inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
						final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
						final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
						vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
						vacio = vacio.replace(new RegExp('/', 'g'), '-');
						final_fecha = (final_fecha != '') ? ('/' + final_fecha) : '';
						inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');
						window.location = '?/utilidades/utilidades' + inicial_fecha + final_fecha;
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

				$inicial_fecha.on('dp.change', function(e) {
					$final_fecha.data('DateTimePicker').minDate(e.date);
				});

				$final_fecha.on('dp.change', function(e) {
					$inicial_fecha.data('DateTimePicker').maxDate(e.date);
				});

				var $form_fecha = $('#form_fecha');
				var $modal_fecha = $('#modal_fecha');

				$form_fecha.on('submit', function(e) {
					e.preventDefault();
				});

				$modal_fecha.on('show.bs.modal', function() {
					$form_fecha.trigger('reset');
				});

				$modal_fecha.on('shown.bs.modal', function() {
					$modal_fecha.find('[data-aceptar]').focus();
				});

				$modal_fecha.find('[data-cancelar]').on('click', function() {
					$modal_fecha.modal('hide');
				});

				$modal_fecha.find('[data-aceptar]').on('click', function() {
					$form_fecha.submit();
				});

				$('[data-cambiar]').on('click', function() {
					$('#modal_fecha').modal({
						backdrop: 'static'
					});
				});
			<?php } ?>

			<?php if ($ventas) { ?>
				var table = $('#table').on('draw.dt', function() { // search.dt order.dt page.dt length.dt
                    var suma1 = 0;
            		var suma2 = 0;
            		var suma3 = 0;
            
            		$('[data-costo]:visible').each(function (i) {
            			var total = parseFloat($(this).attr('data-costo'));
                        console.log(total);
            			suma1 = suma1 + total;
            		});
            		$('#costototal').text(suma1.toFixed(2));
            		$('[data-precio]:visible').each(function (i) {
            			var total = parseFloat($(this).attr('data-precio'));
                        console.log(total);
            			suma2 = suma2 + total;
            		});
            		$('#preciototal').text(suma2.toFixed(2));
            		$('[data-total]:visible').each(function (i) {
            			var total = parseFloat($(this).attr('data-total'));
                        console.log(total);
            			suma3 = suma3 + total;
            		});
            		$('#total').text(suma3.toFixed(2));
				}).DataFilter({
					filter: true,
					name: 'reporte_diario',
					reports: 'excel|word|pdf|html'
				});
			<?php } ?>
		});
	</script>
	<?php
	function Fracciones($ultimoSaldo, $ultimoTamanio)
	{
		$str = "";
		if ($ultimoTamanio != 1) {
			$modulo = $ultimoSaldo % $ultimoTamanio;
			$entero = ($ultimoSaldo - $modulo) / $ultimoTamanio;

			if ($entero != 0) {
				$str .= $entero;
			}
			if ($entero != 0 && $modulo != 0) {
				$str .= " ";
			}
			if ($modulo != 0) {
				$str .= "<span>" . $modulo . " / " . $ultimoTamanio . "</span>";
			}
		} else {
			$str = $ultimoSaldo;
		}
		return $str;
	}
	require_once show_template('footer-advanced');
	?>