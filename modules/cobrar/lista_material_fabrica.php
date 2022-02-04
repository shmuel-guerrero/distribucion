<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

// Obtiene el almacen principal
if ($params[0] != '')
	$almacen = $db->from('inv_almacenes')->where('id_almacen=', $params[0])->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Verifica si existe el almacen
if ($id_almacen != 0) :
	$productos = $db->query("SELECT m.id_materiales,m.nombre,m.precio,p.nombre AS nombrep,u.unidad
			,IFNULL((SELECT id_materiales_stock FROM inv_materiales_stock WHERE almacen_id='{$id_almacen}' AND materiales_id=m.id_materiales LIMIT 1),0)AS id_materiales_stock
			,IFNULL((SELECT stock FROM inv_materiales_stock WHERE almacen_id='{$id_almacen}' AND materiales_id=m.id_materiales LIMIT 1),0)AS stock
		FROM inv_materiales AS m
		LEFT JOIN inv_productos AS p ON p.id_producto=m.id_producto
		LEFT JOIN inv_unidades AS u ON u.id_unidad=m.id_unidad")->fetch();
else :
	$productos = null;
endif;
// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene el modelo almacenes
$almacenes = $db->from('inv_almacenes')->order_by('almacen')->fetch();

// Obtiene los proveedores
$proveedores = $db->select('id_proveedor, proveedor as nombre_proveedor')
	->from('inv_proveedores')
	->group_by('proveedor')
	->order_by('proveedor asc')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<style>
	.table-xs tbody {
		font-size: 12px;
	}

	.input-xs {
		height: 22px;
		padding: 1px 5px;
		font-size: 12px;
		line-height: 1.5;
		border-radius: 3px;
	}

	.input-width {
		width: 60px;
	}
</style>
<div class='row' data-formato='<?= strtoupper($formato_textual); ?>' data-mascara='<?= $formato_numeral; ?>' data-gestion='<?= date_decode($gestion_base, $_institution['formato']); ?>'>
	<div class='col-md-6'>
		<div class='panel panel-default'>
			<div class='panel-heading'>
				<h3 class='panel-title'>
					<span class='glyphicon glyphicon-list'></span>
					<strong>Datos de Ingresos de Materiales</strong>
				</h3>
			</div>
			<div class='panel-body'>
				<form method='post' action='?/cobrar/guardar_ingreso' class='form-horizontal'>
					<div style="zoom: 1;">
						<div class="form-group">
							<label for="planilla" class="col-md-3 control-label">Planilla:</label>
							<div class="col-md-9">
								<input type="text" name="planilla" id="planilla" class="form-control" autocomplete="off" required>
							</div>
						</div>
						<div class="form-group">
							<label for="placa" class="col-md-3 control-label">Placa:</label>
							<div class="col-md-9">
								<input type="text" name="placa" id="placa" class="form-control" autocomplete="off" required>
							</div>
						</div>
					</div>
					<div class='margin-none'>
						<table id='compras' class='table table-bordered table-condensed table-striped table-hover table-xs margin-none'>
							<thead>
								<tr class='active'>
									<th class='text-nowrap'>Nombre Material</th>
									<th class='text-nowrap'>Nombre Producto</th>
									<th class='text-nowrap'>Unidad</th>
									<th class='text-nowrap'>Cantidad</th>
									<th class='text-nowrap text-center'>
										<span class='glyphicon glyphicon-trash'></span>
									</th>
								</tr>
							</thead>
							<tfoot>
								<tr class='active'>
									<th class='text-nowrap text-right' colspan='3'>Importe total <?= escape($moneda); ?></th>
									<th class='text-nowrap text-right' data-subtotal=''>0.00</th>
									<th class='text-nowrap text-center'>
										<span class='glyphicon glyphicon-trash'></span>
									</th>
								</tr>
							</tfoot>
							<tbody></tbody>
						</table>
					</div>
					<div class='form-group'>
						<div class='col-xs-12'>
							<input type='text' name='nro_registros' value='0' class='translate' data-compras>
							<input type='text' name='monto_total' value='0' class='translate' data-total>
							<input type='hidden' name='id_almacen' value='<?= $id_almacen ?>'>
						</div>
					</div>
					<div class='form-group'>
						<div class='col-xs-6 text-right'>
							<button type='submit' class='btn btn-primary'>
								<span class='glyphicon glyphicon-floppy-disk'></span>
								<span>Guardar</span>
							</button>
							<button type='reset' class='btn btn-default'>
								<span class='glyphicon glyphicon-refresh'></span>
								<span>Restablecer</span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class='col-md-6'>
		<div class='panel panel-default'>
			<div class='panel-heading'>
				<h3 class='panel-title'>
					<span class='glyphicon glyphicon-search'></span>
					<strong>Búsqueda de Materiales</strong>
				</h3>
			</div>
			<div class='panel-body'>
				<?php if ($productos) { ?>
					<table id='productos' class='table table-bordered table-condensed table-striped table-hover table-xs'>
						<thead>
							<tr class='active'>
								<th class='text-nowrap'>Nro</th>
								<th class='text-nowrap'>Nombre Material</th>
								<th class='text-nowrap'>Nombre Producto</th>
								<th class='text-nowrap'>Unidad</th>
								<th class='text-nowrap'>Precio</th>
								<th class='text-nowrap'>Stock</th>
								<th class='text-nowrap'><i class='glyphicon glyphicon-cog'></i></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($productos as $nro => $producto) { ?>
								<tr>
									<td class='text-nowrap'><?= $nro + 1 ?></td>
									<td data-nombre='<?= $producto['id_materiales'] ?>' class='text-nowrap'><?= $producto['nombre'] ?></td>
									<td data-nombrep='<?= $producto['id_materiales'] ?>' class='text-nowrap'><?= $producto['nombrep'] ?></td>
									<td data-unidad='<?= $producto['id_materiales'] ?>' class='text-nowrap'><?= $producto['unidad'] ?></td>
									<td class='text-nowrap'><?= $producto['precio'] ?></td>
									<td class='text-nowrap'><?= $producto['stock'] ?></td>
									<td class='text-nowrap'>
										<button type='button' class='btn btn-xs btn-primary' data-material='<?= $producto['id_materiales'] ?>' data-toggle='tooltip' data-title='Comprar'>
											<span class='glyphicon glyphicon-share-alt'></span>
										</button>
									</td>
									<td class='hidden' data-idmaterialstock='<?= $producto['id_materiales'] ?>'><?= $producto['id_materiales_stock'] ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php } else { ?>
					<div class='alert alert-danger'>
						<strong>Advertencia!</strong>
						<p>No existen productos registrados en la base de datos.</p>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src='<?= js; ?>/jquery.dataTables.min.js'></script>
<script src='<?= js; ?>/dataTables.bootstrap.min.js'></script>
<script src='<?= js; ?>/bootstrap-datetimepicker.min.js'></script>
<script src='<?= js; ?>/jquery.maskedinput.min.js'></script>
<script src='<?= js; ?>/jquery.base64.js'></script>
<script src='<?= js; ?>/pdfmake.min.js'></script>
<script src='<?= js; ?>/vfs_fonts.js'></script>
<script src='<?= js; ?>/jquery.dataFilters.min.js'></script>
<script src='<?= js; ?>/moment.min.js'></script>
<script src='<?= js; ?>/moment.es.js'></script>
<script src='<?= js; ?>/bootstrap-datetimepicker.min.js'></script>
<script>
	$(function() {
		$('[data-material]').on('click', function() {
			adicionar_producto($.trim($(this).attr('data-material')));
		});
	});

	function adicionar_producto(id_producto) {
		let $compras = $('#compras tbody'),
			idproducto = $(`[data-producto=${id_producto}]`),
			nombre = $(`[data-nombre=${id_producto}]`).text(),
			nombrep = $(`[data-nombrep=${id_producto}]`).text(),
			unidad = $(`[data-unidad=${id_producto}]`).text(),
			idmaterialstock = $(`[data-idmaterialstock=${id_producto}]`).text(),
			$cantidad = idproducto.find('[data-cantidad]');
		if (idproducto.size()) {
			cantidad = $.trim($cantidad.val());
			cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
			cantidad = (cantidad < 9999999) ? cantidad + 1 : cantidad;
			$cantidad.val(cantidad).trigger('blur');
		} else {
			let plantilla = `<tr class='active' data-producto='${id_producto}'>
								<td>
									${nombre}
									<input type='hidden' name='idmaterialstock[]' value='${idmaterialstock}'>
									<input type='hidden' name='id_material[]' value='${id_producto}'>
								</td>
								<td>${nombrep}</td>
								<td>${unidad}</td>
								<td>
									<input type='text' class='input-width form-control input-xs text-right' value='0' onkeyup='calcular_importe(${id_producto})' name='cantidad[]' data-cantidad data-validation='required number' data-validation-allowing='range[0.01;1000000.00],float' data-validation-error-msg='Debe ser número decimal positivo'>
								</td>`
			/*<td>
				<input type='text' class='input-width form-control input-xs text-right' value='0.00' onkeyup='calcular_importe(${id_producto})' name='costo[]' data-costo data-validation='required number' data-validation-allowing='range[0.01;1000000.00],float' data-validation-error-msg='Debe ser número decimal positivo'>
			</td>
			<td class='text-nowrap text-right' data-importe>
				0.00
			</td>*/
			plantilla += `<td>
									<button type='button' class='btn btn-sm btn-danger' onclick='eliminar_producto(${id_producto})'>
										<span class='glyphicon glyphicon-trash'></span>
									</button>
								</td>
							</tr>`;
			$compras.append(plantilla);
			$.validate({
				modules: 'basic'
			});
		}
		calcular_importe(id_producto);
	}

	function calcular_importe(id_producto) {
		let $producto = $(`[data-producto=${id_producto}]`),
			$cantidad = $producto.find('[data-cantidad]'),
			$costo = $producto.find('[data-costo]'),
			$importe = $producto.find('[data-importe]'),
			cantidad,
			costo,
			importe;
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		costo = $.trim($costo.val());
		costo = ($.isNumeric(costo)) ? parseFloat(costo) : 0.00;
		importe = cantidad * costo;
		importe = importe.toFixed(2);
		$importe.text(importe);
		calcular_total();
	}

	function calcular_total() {
		let $compras = $('#compras tbody'),
			$total = $('[data-subtotal]:first'),
			$importes = $compras.find('[data-importe]'),
			importe,
			total = 0;
		$importes.each(function(i) {
			importe = $.trim($(this).text());
			importe = parseFloat(importe);
			total = total + importe;
		});
		$total.text(total.toFixed(2));
		$('[data-compras]:first').val($importes.size()).trigger('blur');
		$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
	}

	function eliminar_producto(id_producto) {
		bootbox.confirm('Está seguro que desea eliminar el producto?', function(result) {
			if (result) {
				$('[data-producto=' + id_producto + ']').remove();
				calcular_total();
			}
		});
	}
</script>
<?php require_once show_template('footer-configured');
