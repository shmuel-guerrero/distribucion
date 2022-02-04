<?php

// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->where('id_almacen', $id_almacen)->fetch_first();

// Verifica si existe el almacen
if (!$almacen) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los productos
$productos = $db->query("select p.id_producto, p.codigo, p.nombre, p.cantidad_minima, p.precio_actual, 
						ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, 
						ifnull(s.cantidad_egresos, 0) as cantidad_egresos, 
						u.unidad, u.sigla, c.categoria 
						from inv_productos p 
						left join (select d.producto_id, sum(d.cantidad) as cantidad_ingresos 
							from inv_ingresos_detalles d 
							left join inv_ingresos i on i.id_ingreso = d.ingreso_id 
							where i.almacen_id = $id_almacen 
							group by d.producto_id) as e on e.producto_id = p.id_producto 
						left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos 
							from inv_egresos_detalles d 
							left join inv_egresos e on e.id_egreso = d.egreso_id 
							where e.almacen_id = $id_almacen 
							group by d.producto_id) as s on s.producto_id = p.id_producto 
						left join inv_unidades u on u.id_unidad = p.unidad_id 
						left join inv_categorias c on c.id_categoria = p.categoria_id")
				->fetch();
$materiales = $db->select('m.id_materiales, m.nombre, m.precio, u.unidad, p.nombre_factura ')
				->from ('inv_materiales m')
				->join('inv_unidades u','u.id_unidad=m.id_unidad')
				->join('inv_productos p','p.id_producto=m.id_producto')
				->order_by('m.id_materiales')
				->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

//otro almacen
$otro_alma = $db->select('*')
				->from('inv_almacenes')
				->where('id_almacen !=',$id_almacen)->fetch();

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
.position-left-bottom {
	bottom: 0;
	left: 0;
	position: fixed;
	z-index: 1030;
}
.margin-all {
	margin: 15px;
}
.display-table {
	display: table;
}
.display-cell {
	display: table-cell;
	text-align: center;
	vertical-align: middle;
}
.btn-circle {
	border-radius: 50%;
	height: 75px;
	width: 75px;
}
</style>
<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos de la Recepcion</strong>
				</h3>
			</div>
			<div class="panel-body">
				<form method="post" action="?/materiales/guardar_recepcion" id="formulario" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
						</div>
					</div>
					<div class="form-group">
						<label for="planilla" class="col-sm-4 control-label">Planilla:</label>
						<div class="col-sm-8">
							<input type="text" value="0" name="planilla" id="planilla" class="form-control" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="placa" class="col-sm-4 control-label">Placa:</label>
						<div class="col-sm-8">
							<input type="text" value="0" name="placa" id="placa" class="form-control">
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap">#</th>
									<th class="text-nowrap">Código</th>
									<th class="text-nowrap">Nombre</th>
									<th class="text-nowrap">Cantidad</th>
									<th class="text-nowrap">Precio</th>
									<th class="text-nowrap">Importe</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="5">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="">0.00</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</tfoot>
							<tbody></tbody>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 100">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-12 text-right">
							<button type="submit" class="btn btn-primary">
								<span class="glyphicon glyphicon-floppy-disk"></span>
								<span>Guardar</span>
							</button>
							<button type="reset" class="btn btn-default">
								<span class="glyphicon glyphicon-refresh"></span>
								<span>Restablecer</span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-cog"></span>
					<strong>Datos generales</strong>
				</h3>
			</div>
			<div class="panel-body">
				<ul class="list-group">
					<li class="list-group-item">
						<span class="glyphicon glyphicon-home"></span>
						<strong>Casa Matriz: </strong>
						<span><?= escape($_institution['nombre']); ?></span>
					</li>
					<li class="list-group-item">
						<span class="glyphicon glyphicon-qrcode"></span>
						<strong>NIT: </strong>
						<span><?= escape($_institution['nit']); ?></span>
					</li>
					<li class="list-group-item">
						<span class="glyphicon glyphicon-user"></span>
						<strong>Empleado: </strong>
						<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
					</li>
				</ul>
			</div>
			<div class="panel-footer text-center"><?= credits; ?></div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-search"></span>
					<strong>Búsqueda de productos</strong>
				</h3>
			</div>
			<div class="panel-body">
				<?php if ($permiso_listar) { ?>
				<div class="row">
					<div class="col-xs-12 text-right">
						<a href="?/materiales/lista_recepcion_materiales" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Lista de Recepcion</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($materiales) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">#</th>
							<th class="text-nowrap">Nombre</th>
							<th class="text-nowrap">Producto</th>
							<th class="text-nowrap">Unidad</th>
							<th class="text-nowrap">Precio</th>
							<th class="text-nowrap"><span class="glyphicon glyphicon-cog"></span></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($materiales as $nro => $producto) { ?>
						<tr>
							<td class="text-nowrap" data-codigo="<?= $producto['id_materiales']; ?>"><?= escape($producto['id_materiales']); ?></td>
							<td>
								<span data-nombre="<?= $producto['id_materiales']; ?>"><?= escape($producto['nombre']); ?></span>
							</td>
							<td class="text-nowrap"><?= escape($producto['nombre_factura']); ?></td>
							<td class="text-nowrap" data-unidad="<?= $producto['id_materiales']; ?>"><?= escape($producto['unidad']); ?></td>
							<td class="text-nowrap text-right" data-precio="<?= $producto['id_materiales']; ?>"><?= escape($producto['precio']); ?></td>
							<td class="text-nowrap">
								<button type="button" class="btn btn-xs btn-primary" data-egresar="<?= $producto['id_materiales']; ?>" data-toggle="tooltip" data-title="Egresar"><span class="glyphicon glyphicon-share-alt"></span></button>
								<!--<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto'] . '|' . $almacen['id_almacen']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>-->
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php } else { ?>
				<div class="alert alert-danger">
					<strong>Advertencia!</strong>
					<p>No existen productos registrados en la base de datos.</p>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<h2 class="btn-info position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es un egreso" data-placement="right"><i class="glyphicon glyphicon-log-out display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {
	var table;

	$('[data-egresar]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-egresar')));
	});

	$('[data-actualizar]').on('click', function () {
		var actualizar = $.trim($(this).attr('data-actualizar'));
		actualizar = actualizar.split('|');
		var id_materiales = actualizar[0];
		var id_almacen = actualizar[1];
		
		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/egresos/actualizar',
			data: {
				id_materiales: id_materiales,
				id_almacen: id_almacen
			}
		}).done(function (producto) {
			if (producto) {
				var precio = parseFloat(producto.precio).toFixed(2);
				var stock = parseInt(producto.stock); 
				var cell;

				cell = table.cell($('[data-precio=' + producto.id_materiales + ']'));
				cell.data(precio);
				cell = table.cell($('[data-stock=' + producto.id_materiales + ']'));
				cell.data(stock);
				table.draw();

				var $producto = $('[data-producto=' + producto.id_materiales + ']');
				var $cantidad = $producto.find('[data-cantidad]');
				var $precio = $producto.find('[data-precio]');

				if ($producto.size()) {
					$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
					
					$precio.val(precio);
					$precio.attr('data-precio', precio);
					calcular_importe(producto.id_materiales);
				}

				$.notify({
					title: '<strong>Actualización satisfactoria!</strong>',
					message: '<div>El stock y el precio del producto se actualizaron correctamente.</div>'
				}, {
					type: 'success'
				});
			} else {
				$.notify({
					title: '<strong>Advertencia!</strong>',
					message: '<div>Ocurrió un problema, no existe almacén principal.</div>'
				}, {
					type: 'danger'
				});
			}
		}).fail(function () {
			$.notify({
				title: '<strong>Advertencia!</strong>',
				message: '<div>Ocurrió un problema y no se pudo actualizar el stock ni el precio del producto.</div>'
			}, {
				type: 'danger'
			});
		}).always(function () {
			$('#loader').fadeOut(100);
		});
	});

	table = $('#productos').DataTable({
		info: false,
		lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'Todos']],
		order: []
	});

	$('#productos_wrapper .dataTables_paginate').parent().attr('class', 'col-sm-12 text-right');

	$.validate({
		form: '#formulario',
		modules: 'basic'
	});

	$('#formulario').on('reset', function () {
		$('#ventas tbody').empty();
		calcular_total();
	});
});

function ca( obj , x )
{
    var aa = obj[ obj.selectedIndex ].value;
    if(aa=="Traspaso"){
        $('#alma').show();
    }else{
        $('#alma').hide();
    }
}

function adicionar_producto(id_materiales) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_materiales + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_materiales + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_materiales + ']').text());
	var unidad = $.trim($('[data-unidad=' + id_materiales + ']').text());
	var precio = $.trim($('[data-precio=' + id_materiales + ']').text());
	var plantilla = '';
	var cantidad;

	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_materiales + '">' +
						'<td class="text-nowrap">' + numero + '</td>' +
						'<td class="text-nowrap"><input type="text" value="' + id_materiales + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" onkeyup="calcular_importe(' + id_materiales + ')"></td>' +
						'<td><input type="text" value="' + precio + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + precio + '" data-validation="required number" readonly data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_materiales + ')"></td>' +
						'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap text-center">' +
							'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_materiales + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
						'</td>' +
					'</tr>';

		$ventas.append(plantilla);

		$ventas.find('[data-cantidad], [data-precio]').on('click', function () {
			$(this).select();
		});

		$ventas.find('[title]').tooltip({
			container: 'body',
			trigger: 'hover'
		});

		$.validate({
			form: '#formulario',
			modules: 'basic'
		});
	}

	calcular_importe(id_materiales);
}

function eliminar_producto(id_materiales) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-producto=' + id_materiales + ']').remove();
			renumerar_productos();
			calcular_total();
		}
	});
}

function renumerar_productos() {
	var $ventas = $('#ventas tbody');
	var $materiales = $ventas.find('[data-producto]');
	$materiales.each(function (i) {
		$(this).find('td:first').text(i + 1);
	});
}

function calcular_importe(id_materiales) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_materiales + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $precio = $producto.find('[data-precio]');
	var $importe = $producto.find('[data-importe]');
	var cantidad, precio, importe;

	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio = $.trim($precio.val());
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	importe = cantidad * precio;
	importe = importe.toFixed(2);
	$importe.text(importe);

	calcular_total();
}

function calcular_total() {
	var $ventas = $('#ventas tbody');
	var $total = $('[data-subtotal]:first');
	var $importes = $ventas.find('[data-importe]');
	var importe, total = 0;

	$importes.each(function (i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;
	});

	$total.text(total.toFixed(2));
	$('[data-ventas]:first').val($importes.size()).trigger('blur');
	$('[data-total]:first').val(total.toFixed(2)).trigger('blur');
}
</script>
<?php require_once show_template('footer-configured'); ?>