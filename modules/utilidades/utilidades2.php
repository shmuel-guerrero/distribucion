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
$query = "SELECT *, SUM(cantidad)AS cantidadAcumul, SUM(precio*cantidad/IF(a.cantidad_unidad IS NULL, 1, a.cantidad_unidad)) AS importeAcumul, 
			(SUM(precio*cantidad/IF(a.cantidad_unidad IS NULL, 1, a.cantidad_unidad))-(v.descuento_bs/v.nro_registros)) AS importeAcumul_con_descuento
				, GROUP_CONCAT(vd.lote) as lotes, GROUP_CONCAT(vd.precio,'-',vd.lote) as precios, GROUP_CONCAT(vd.cantidad) as cantidades
            FROM inv_productos p
            INNER JOIN inv_egresos_detalles vd ON vd.producto_id = p.id_producto
            INNER JOIN inv_egresos v ON vd.egreso_id=v.id_egreso
            LEFT JOIN inv_asignaciones a ON a.producto_id = vd.producto_id AND a.unidad_id = vd.unidad_id  AND a.visible = 's' 
            LEFT JOIN inv_unidades u ON u.id_unidad = vd.unidad_id
            LEFT JOIN inv_categorias c ON p.categoria_id = c.id_categoria
            WHERE v.tipo='Venta' and v.anulado != 3 AND v.fecha_egreso between '$fecha_inicial' and '$fecha_final' 
            GROUP BY p.id_producto";
$query2 = "SELECT *, SUM(cantidad) AS cantidadAcumul, SUM(precio*cantidad/IF(a.cantidad_unidad IS NULL, 1, a.cantidad_unidad)) AS importeAcumul , GROUP_CONCAT(vd.lote) as lotes, GROUP_CONCAT((IF(vd.precio = 0.00, 0, vd.precio)),'-',vd.lote,'-',IFNULL(a.cantidad_unidad, 1)) as precios, GROUP_CONCAT(vd.cantidad) as cantidades
			, GROUP_CONCAT(DISTINCT(u.unidad)) as unidades, (cantidad*precio) as importe_total,
			SUM((v.descuento_bs/v.nro_registros) + (vd.precio*vd.descuento/100)) AS descuentoacumul
			FROM inv_productos p 
			INNER JOIN inv_egresos_detalles vd ON vd.producto_id = p.id_producto 
			INNER JOIN inv_egresos v ON vd.egreso_id=v.id_egreso 
			LEFT JOIN inv_asignaciones a ON a.producto_id = vd.producto_id AND a.unidad_id = vd.unidad_id   AND a.visible = 's' 
			LEFT JOIN inv_unidades u ON u.id_unidad = vd.unidad_id 
			LEFT JOIN inv_categorias c ON p.categoria_id = c.id_categoria 
			WHERE v.tipo='Venta'  
			and v.anulado != 3 
			AND v.fecha_egreso 
            between '$fecha_inicial' and '$fecha_final'
            GROUP BY p.id_producto";
$ventas = $db->query($query2)->fetch();
// echo $db->last_query();

// echo json_encode($ventas); die();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Almacena los permisos en variables
$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
<style type="text/css">
	.Table{
			display: table;
			width: 100%;
		}

	.Row{
			display: table-row;
		}

	.Cell{
			display: table-cell;
			border: 0px;
			border-width: thin;
			padding-left: 5px;
			padding-right: 5px;
		}
</style>

<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<strong>Reporte de Utilidades</strong>
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
					<th class="text-nowrap">Cantidad</th>
					<th class="text-nowrap">Costos</th>
					<th class="text-nowrap">Precios</th>
					<th class="text-nowrap">Descuento total</th>
					<th class="text-nowrap">Costo Total</th>
					<th class="text-nowrap">Precio Total</th>
					<th class="text-nowrap">Utilidad</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="active">
					<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Codigo</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Producto</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Costos</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Precios</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Descuento Total</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Costo Total</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio Total</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Utilidad</th>
				</tr>
			</tfoot>
			<tbody>
            <?php foreach ($ventas as $key => $venta) { ?>
                <tr>
                    <td><?= $key+1 ?></td>
                    <td><?= $venta['codigo'] ?></td>
                    <td><?= $venta['nombre'] ?></td>
                    <td><?= $venta['categoria'] ?></td>
					<td><?= $venta['cantidadAcumul'] ?></td>
					<td>
						<?php
							$lotes = explode(",", $venta['lotes']);
							$para = ''; $ant=0; $cos = 0;
                        
							$mostrar = array();
							$totalCompra=0;
							$c = 0;
							// exit();
							foreach ($lotes as $i => $lote) {
								$l = explode('-', $lote);
								$lo = $l[0];
								$ca = $l[1];

								$deta = $db->select('*')->from('inv_ingresos_detalles')->where('lote', $lo)->where('producto_id', $venta['id_producto'])->fetch_first();
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
							foreach($mostrar as $item){
								$content.='<div class="Row">'.
									'<div class="Cell text-nowrap" width="20%">'.$item[2].'</div>'.
									'<div class="Cell text-nowrap" width="30%">UNIDAD </div>'.
									'<div class="Cell text-nowrap" width="10%"> </div>'.
									'<div class="Cell text-nowrap" width="40%">'.$item[3].' ' .$moneda. '</div>'.
								'</div>';
								$totalCompra = $totalCompra + ($item[2] * $item[3]);
							}

							$html = '<span class="text-sm" width="100%">
								<div class="Table" width="100%">
									<div class="Row" width="100%">
										<div class="Cell text-nowrap" width="70%"><span><table><tbody>'.trim($content).'</tbody></table></span></div>
										<div class="Cell text-nowrap" width="10%"></div>
										<div class="Cell text-nowrap text-right" width="30%"><b>'.number_format($totalCompra).'</b> '.$moneda.'</div>
									</div>
								</div>
							</span>';

							echo $html;

						?>
					</td>
                    <td>
						<?php
							$lotes = explode(",", $venta['precios']);
							$unidades = explode(",", $venta['unidades']);
							$para = ''; $ant=0; $cos = 0;

							$listar = array();
							$totalVenta=0;
							$c = 1;

							foreach ($lotes as $i => $lote) {
								$l = explode('-', $lote);

								if (is_numeric($l[0])) {
									$cos = $l[0];
									$lo = $l[1];
									$ca = $l[2];
									$uni = $l[3];

									$ant = $cos;
								} else {
									$cos = $ant;
									$lo = $l[0];
									$ca = $l[1];
									$uni = $l[2];
								}

								if(count($listar) == 0){
									$listar[$c][1] = $lo;
									$listar[$c][2] = $ca;
									$listar[$c][3] = $cos;
									$listar[$c][4] = $uni;
									
								}else{
									$sw = 0;
									$sw1 = 0;
									foreach($listar as &$mostrar1){
										if($mostrar1[3] == $cos){
											$mostrar1[2] = $mostrar1[2] + $ca;
											$sw = 1;
										}
									}
									if($sw == 0){
										$c++;
										$listar[$c][1] = $lo;
										$listar[$c][2] = $ca;
										$listar[$c][3] = $cos;
										$listar[$c][4] = $uni;
									}
								}
							}

							$content = '';
							foreach($listar as $in1 => $item){
							    $subt_ = ($item[2]/$item[4]);
							    $blabla = ((string)$subt_ != 'INF')?$subt_:1;
								$content.='<div class="Row">'.
									'<div class="Cell text-nowrap" width="20%">'. $blabla .'</div>'.
									'<div class="Cell text-nowrap" width="30%">'.$unidades[$in1-1].' </div>'.
									'<div class="Cell text-nowrap" width="10%"> </div>'.
									'<div class="Cell text-nowrap" width="40%">'.$item[3].' ' .$moneda. '</div>'.
								'</div>';
								$totalVenta = $totalVenta + ( $blabla * $item[3]);
							}
							$html = '<span class="text-sm" width="100%">
								<div class="Table" width="100%">
									<div class="Row" width="100%">
										<div class="Cell text-nowrap" width="70%"><span><table><tbody>'.trim($content).'</tbody></table></span></div>
										<div class="Cell text-nowrap" width="10%"></div>
										<div class="Cell text-nowrap text-right" width="30%"><b>'.number_format($totalVenta).'</b> '.$moneda.'</div>
									</div>
								</div>
							</span>';
							echo $html;
							$utilidad = ($totalVenta - $totalCompra);
						?>
					
					<td class="text-right" data-costo="<?= $totalCompra ?>"><b><?= number_format($venta['descuentoacumul'], 2, ',', '.') ?></b></td></td>
                    <td class="text-right" data-costo="<?= $totalCompra ?>"><b><?= number_format($totalCompra, 2, ',', '.') ?></b></td>
                    <td class="text-right" data-precio="<?= $totalVenta ?>"><b><?= number_format($totalVenta, 2, ',', '.') ?></b></td>
					<td class="text-right <?= ($utilidad>0) ? 'text-success':'text-danger' ?>" data-total="<?= $utilidad ?>"><b><?= $utilidad ?></b></td>
                </tr>
            <?php } ?>
            </tbody>
		</table>

	<?php } else { ?>
		<div class="alert alert-danger">
			<strong>Advertencia!</strong>
			<p>No existen registros en la base de datos.</p>
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
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>


<script>
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
			name: 'Reporte de Utilidades',
			fechas: 'Reporte desde: <?= $fecha_inicial ?> hasta el: <?= ($fecha_final == $gestion_limite) ? date('Y-m-d') : $fecha_final ?>',
            creacion: 'Generado el: <?= date('Y-m-d') ?>',
            total: 7,
			reports: 'xls|doc|pdf|html'
		});
	<?php } ?>
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
				window.location = '?/utilidades/utilidades2' + inicial_fecha + final_fecha;
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
</script>

<?php require_once show_template('footer-configured'); ?>

