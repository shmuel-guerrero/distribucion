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
$productos = $db->query("SELECT p.id_producto, p.promocion, z.id_asignacion, z.unidad_id, z.unidade, z.cantidad2, p.descripcion, p.imagen, p.codigo, p.nombre_factura as nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, IFNULL(e.cantidad_ingresos, 0) AS cantidad_ingresos, (IFNULL(s.cantidad_egresos, 0) + IFNULL(sp.cantidad_promocion, 0) + IFNULL(spr.cantidad_venta_promo, 0)) AS cantidad_egresos, u.unidad, u.sigla, c.categoria
					FROM inv_productos p
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_ingresos
						   FROM inv_ingresos_detalles d
						   LEFT JOIN inv_ingresos i ON i.id_ingreso = d.ingreso_id
						   WHERE  transitorio = 0 AND  i.almacen_id = '$id_almacen' GROUP BY d.producto_id) AS e ON e.producto_id = p.id_producto
					LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_egresos
						   FROM inv_egresos_detalles d LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
						   WHERE e.almacen_id = '$id_almacen' AND e.anulado != 3 AND d.promocion_id < 2 GROUP BY d.producto_id) AS s ON s.producto_id = p.id_producto
                    LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad) AS cantidad_venta_promo
							FROM inv_egresos_detalles d 
                            LEFT JOIN inv_egresos e ON e.id_egreso = d.egreso_id
                            LEFT JOIN inv_productos pr ON pr.id_producto = d.promocion_id
						   	WHERE e.almacen_id = '$id_almacen' AND  d.promocion_id > 2 AND e.anulado != 3 AND pr.fecha_limite < CURDATE() GROUP BY d.producto_id) AS spr ON spr.producto_id = p.id_producto
                    LEFT JOIN (SELECT d.producto_id, SUM(d.cantidad*a.cantidad) AS cantidad_promocion
						    FROM inv_ingresos_detalles a 
                            LEFT JOIN inv_ingresos b on b.id_ingreso = a.ingreso_id 
                            INNER JOIN inv_promociones d ON d.id_promocion = a.producto_id
                            INNER JOIN inv_productos c ON c.id_producto = d.id_promocion
                            INNER JOIN inv_productos e ON e.id_producto = d.producto_id
                            WHERE  transitorio = 0 AND  b.almacen_id = '$id_almacen' AND e.fecha_limite > CURDATE() GROUP BY d.producto_id) AS sp ON sp.producto_id = p.id_producto
					LEFT JOIN inv_unidades u ON u.id_unidad = p.unidad_id LEFT JOIN inv_categorias c ON c.id_categoria = p.categoria_id
					LEFT JOIN (SELECT w.producto_id, GROUP_CONCAT(w.id_asignacion SEPARATOR '|') AS id_asignacion, GROUP_CONCAT(w.unidad_id SEPARATOR '|') AS unidad_id, GROUP_CONCAT(w.cantidad_unidad,')',w.unidad,':',w.otro_precio SEPARATOR '&') AS unidade, GROUP_CONCAT(w.cantidad_unidad SEPARATOR '*') AS cantidad2
					   FROM (SELECT *
							FROM inv_asignaciones q
								  LEFT JOIN inv_unidades u ON q.unidad_id = u.id_unidad  AND q.visible = 's' 
										 ORDER BY u.unidad DESC) w GROUP BY w.producto_id ) z ON p.id_producto = z.producto_id WHERE p.promocion != 'si' AND p.eliminado = 0")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

//otro almacen
$otro_alma = $db->select('*')->from('inv_almacenes')->where('id_almacen !=',$id_almacen)->fetch();

// Almacena los permisos en variables
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-advanced'); ?>
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
					<strong>Datos del egreso</strong>
				</h3>
			</div>
			<div class="panel-body">
				<form method="post" action="?/egresos/guardar" id="formulario" class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-4 control-label">Almacén:</label>
						<div class="col-sm-8">
							<p class="form-control-static"><?= escape($almacen['almacen']); ?></p>
						</div>
					</div>
					<div class="form-group">
						<label for="tipo" class="col-sm-4 control-label">Tipo de egreso:</label>
                        <div class="col-sm-8">
                            <select name="tipo" id="tipo" class="form-control" data-validation="required" onchange="ca(this,1)">
                                <option value="">Seleccionar</option>
                                <option value="Traspaso">Egreso como traspaso</option>
                                <option value="Baja">Egreso como baja</option>
                                <option value="Perdida">Egreso como perdida</option>
                            </select>
                        </div>
					</div>
                    <div class="form-group" id="alma">
                        <label for="almac" class="col-sm-4 control-label">Almacén:</label>
                        <div class="col-sm-8">
                            <select name="almac" id="almac" class="form-control" data-validation="required">
                                <?php foreach($otro_alma as $otro){ ?>
                                    <option value="<?= $otro['id_almacen'] ?>"><?= $otro['almacen'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" id="motiv">
                        <label for="motivo" class="col-sm-4 control-label">Motivo:</label>
                        <div class="col-sm-8">
                            <select name="motivo" id="motivo" class="form-control text-uppercase" data-validation="required">
                                <option value="FECHA DE CADUCIDAD VENCIDA">FECHA DE CADUCIDAD VENCIDA</option>
                                <option value="MALA PRODUCCIÓN">MALA PRODUCCIÓN</option>
                                <option value="PRODUCTO MAL MANIPULADO">PRODUCTO MAL MANIPULADO</option>
                                <option value="PRODUCTO DAÑADO">PRODUCTO DAÑADO</option>
                                <option value="OTROS">OTROS</option>
                            </select>
                        </div>
                    </div>
                    
                    
					<div class="form-group">
						<label for="descripcion" class="col-sm-4 control-label">Descripción:</label>
						<div class="col-sm-8">
							<textarea name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="letternumber" data-validation-allowing="+-/.,:;#º()\n " data-validation-optional="true"></textarea>
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
						<a href="?/egresos/listar" class="btn btn-primary">
				<span class="glyphicon glyphicon-list-alt"></span>
				<span>Lista de egresos</span></a>
					</div>
				</div>
				<hr>
				<?php } ?>
				<?php if ($productos) { ?>
				<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
					<thead>
						<tr class="active">
							<th class="text-nowrap">Código</th>
							<th class="text-nowrap">Nombre</th>
							<th class="text-nowrap">Categoría</th>
							<th class="text-nowrap">Stock</th>
							<th class="text-nowrap">Costo</th>
							<th class="text-nowrap"><span class="glyphicon glyphicon-cog"></span></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($productos as $nro => $producto) { ?>
						<tr>
							<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
							<td>
								<span data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre']); ?></span>
							</td>
							<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
							<td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
							<?php 
									$precio_r = 0;
                                    $precio_r = $db->query("SELECT d.costo FROM inv_ingresos_detalles d LEFT JOIN inv_ingresos i on i.id_ingreso = d.ingreso_id WHERE d.producto_id=".$producto['id_producto']." ORDER BY d.id_detalle DESC")->fetch_first()['costo'];
                                    ?>
							<td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>"><?= escape($precio_r); ?></td>
							<td class="text-nowrap">
								<button type="button" class="btn btn-xs btn-primary" data-egresar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Egresar"><span class="glyphicon glyphicon-share-alt"></span></button>
								<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto'] . '|' . $almacen['id_almacen']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
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
		var id_producto = actualizar[0];
		var id_almacen = actualizar[1];
		
		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/egresos/actualizar',
			data: {
				id_producto: id_producto,
				id_almacen: id_almacen
			}
		}).done(function (producto) {
			if (producto) {
				var precio = parseFloat(producto.precio).toFixed(2);
				var stock = parseInt(producto.stock); 
				var cell;

				cell = table.cell($('[data-valor=' + producto.id_producto + ']'));
				cell.data(precio);
				cell = table.cell($('[data-stock=' + producto.id_producto + ']'));
				cell.data(stock);
				table.draw();

				var $producto = $('[data-producto=' + producto.id_producto + ']');
				var $cantidad = $producto.find('[data-cantidad]');
				var $precio = $producto.find('[data-precio]');

				if ($producto.size()) {
					$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
					$cantidad.attr('data-validation-error-msg', 'Debe ser un número positivo entre 1 y ' + stock);
					$precio.val(precio);
					$precio.attr('data-precio', precio);
					calcular_importe(producto.id_producto);
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
        $('#motiv').hide();
    }else{
        $('#alma').hide();
        $('#motiv').show();
    }
}

function adicionar_producto(id_producto) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
	var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	var plantilla = '';
	var cantidad;

	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1: cantidad;
		$cantidad.val(cantidad).trigger('blur');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
						'<td class="text-nowrap">' + numero + '</td>' +
						'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
						'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
						'<td><input type="text" value="' + valor + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + valor + '" data-validation="required number" readonly data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
						'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap text-center">' +
							'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
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

	calcular_importe(id_producto);
}

function eliminar_producto(id_producto) {
	bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
		if(result){
			$('[data-producto=' + id_producto + ']').remove();
			renumerar_productos();
			calcular_total();
		}
	});
}

function renumerar_productos() {
	var $ventas = $('#ventas tbody');
	var $productos = $ventas.find('[data-producto]');
	$productos.each(function (i) {
		$(this).find('td:first').text(i + 1);
	});
}

function calcular_importe(id_producto) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
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
<?php require_once show_template('footer-advanced'); ?>