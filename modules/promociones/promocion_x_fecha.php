<?php

// Obtiene el almacen principal
if($params[0]!='')
	$almacen = $db->from('inv_almacenes')->where('id_almacen=',$params[0])->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Verifica si existe el almacen
if ($id_almacen != 0) {

	$productos = $db->query("select p.id_producto, p.descripcion, p.imagen, p.codigo, p.nombre, p.nombre_factura, p.cantidad_minima, p.precio_actual, p.unidad_id, ifnull(e.cantidad_ingresos, 0) as cantidad_ingresos, ifnull(s.cantidad_egresos, 0) as cantidad_egresos, IFNULL(e.costo, 0) AS costo_ingresos, u.unidad, u.sigla, c.categoria
    from inv_productos p
    left join (select d.producto_id, d.costo, sum(d.cantidad) as cantidad_ingresos from inv_ingresos_detalles d left join inv_ingresos i on i.id_ingreso = d.ingreso_id where i.almacen_id = $id_almacen  group by d.producto_id ) as e on e.producto_id = p.id_producto left join (select d.producto_id, sum(d.cantidad) as cantidad_egresos from inv_egresos_detalles d left join inv_egresos e on e.id_egreso = d.egreso_id where e.almacen_id = $id_almacen group by d.producto_id ) as s on s.producto_id = p.id_producto left join inv_unidades u on u.id_unidad = p.unidad_id left join inv_categorias c on c.id_categoria = p.categoria_id where p.grupo = '' and p.promocion = ''")->fetch();

} else {
	$productos = null;
	// Instancia la variable de notificacion
	$_SESSION[temporary] = array(
		'alert' => 'danger',
		'title' => 'Seleccione un almacen válido!',
		'message' => 'Para crear una promoción ebe seleccionar un almacen.'
	);
	return redirect('?/promociones/almacen_promo');
}
// Obtiene grupos
$grupos = $db->select('*')->from('inv_clientes_grupos')->where('estado_grupo=','1')->fetch();
$clientes_list = $db->select('*')->from('inv_clientes')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la fecha de hoy
$hoy = date('Y-m-d');

// Obtiene los clientes
//$clientes = $db->select('nombre_cliente, nit_ci, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')->from('inv_egresos')->group_by('nombre_cliente, nit_ci')->order_by('nombre_cliente asc, nit_ci asc')->fetch();
// $clientes = $db->query("select DISTINCT a.nombre_cliente, a.nit_ci from inv_egresos a LEFT JOIN inv_clientes b ON a.nit_ci = b.nit UNION
// select DISTINCT b.cliente as nombre_cliente, b.nit as nit_ci from inv_egresos a RIGHT JOIN inv_clientes b ON a.nit_ci = b.nit
// ORDER BY nombre_cliente asc, nit_ci asc")->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

// Obtiene el modelo unidades
$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();

// Obtiene el modelo categorias
$categorias = $db->from('inv_categorias')->order_by('categoria')->fetch();

// Obtiene el tipo de promo
//$tipo_promo = $db->from('inv_tipo_promo')->order_by('tipo')->fetch();

$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
<link rel="stylesheet" href="css/selectize.bootstrap3.min.css">
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
<div class="col-sm-12 text-right">
	<a href="?/promociones/reporte_promos_monto" class="btn btn-info">
	<i class="glyphicon glyphicon-list"></i><span class="hidden-xs"> Volver al Listado</span>
</a><br><br>
	
</div>

<div class="row">
	<?php if ($almacen) { ?>

	<div class="col-md-6">
	
		<div class="panel panel-default">
			<div class="panel-heading">
				
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos de la Promoción por Fecha</strong>
				</h3>
			</div>
			<div class="panel-body">
				<?php if (isset($_SESSION[temporary])) { ?>
				<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong><?= $_SESSION[temporary]['title']; ?></strong>
					<p><?= $_SESSION[temporary]['message']; ?></p>
				</div>
				<?php unset($_SESSION[temporary]); ?>
				<?php } ?>
				<form method="post" action="?/promociones/guardar_promocion_x_fecha" class="form-horizontal">
					<div class="form-group">
						<!--<label for="id_promocion" class="col-md-3 control-label">Código:</label>-->
						<div class="col-md-9">
							<input type="hidden" value="" name="nombre" data-validation="required">
							<!--<input type="text" value="" name="id_promocion" id="id_promocion" class="form-control" data-validation-allowing="-/.#º() " data-validation-length="max50" data-validation-url="?/productos/validar">
							-->
						</div>
					</div>
					<div class="form-group">
						<label for="nombre" class="col-md-3 control-label">Nombre del promoción:</label>
						<div class="col-md-9">
							<input type="text" value="" name="nombre" id="nombre" class="form-control" data-validation="required letternumber length" data-validation-allowing='-+/.,:;#&º"() ' data-validation-length="max100">
						</div>
					</div>
					<div class="form-group">
						<label for="stock" class="col-md-3 control-label">Monto:</label>
						<div class="col-md-9">
							<input type="text" value="100" name="min_promo" id="min_promo" class="form-control" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="stock" class="col-md-3 control-label">Fecha Inicio:</label>
						<div class="col-md-9">
							<input type="date" value="10" name="fecha_ini" id="fecha_ini" class="form-control" data-validation="required date">
						</div>
					</div>
					<div class="form-group">
						<label for="stock" class="col-md-3 control-label">Fecha Fin:</label>
						<div class="col-md-9">
							<input type="date" value="10" name="fecha_fin" id="fecha_fin" class="form-control" data-validation="required date date_end">
						</div>
					</div>

					<div class="form-group">
						<label for="descripcion" class="col-md-3 control-label">Descripción:</label>
						<div class="col-md-9">
							<textarea name="descripcion" id="descripcion" class="form-control" rows="3" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
						</div>
					</div>

					<!-- <div class="form-group">
						<label for="almacen" class="col-md-3 control-label">Tipo:</label>
						<div class="col-md-9">
							<select name="tipo" id="tipo" class="form-control" data-validation="required number" onchange="set_tipo_promo()">
								<option value="1">Seleccionar</option>
								<option value="2">Descuento en Bolivianos</option>
								<option value="3">Descuento en Porcentaje</option>
								<option value="3">Grupos</option>
								<option value="4">Item</option>
							</select>
						</div>
					</div> -->
					<div id="monto" style="display:none">
						<div class="form-group">
							<label for="almacen" class="col-md-3 control-label">Descuento en Bs:</label>
							<div class="col-md-9">
								<!--<input type="text" value="" id="monto_promo" name="monto_promo" class="form-control text-left" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo">-->
								<input type="text" value="" name="monto_promo" id="monto_promo" class="form-control" data-validation="required number">
							</div>
						</div>
					</div>

					<!-- <div id="div_grupo">
						<div class="form-group">
							<label for="grupo" class="col-md-3 control-label">Grupos:</label>
							 <div class="col-md-9 ">
							 	<input id="grupos_item" name="grupos_item" type="hidden" >
								<select id="grupob" class="form-control" multiple="multiple" placeholder="Choose">
	                            <?php //foreach ($grupos as $nro => $grupo) { ?>
	                                <option value="<?//= $grupo['id_cliente_grupo'] ?>"><?//= $grupo['nombre_grupo'] ?>
	                                </option>
	                            <?php //} ?>
	                        	</select>
							</div>
						</div>
						<div class="form-group">
							<label for="cliente" class="col-md-3 control-label">Clientes:</label>
							 <div class="col-md-9 ">
							 <input id="cliente_id" name="cliente_id" type="hidden" >
								<select  id="clienteb" class="form-control" multiple="multiple" placeholder="Choose">
	                            <?php // foreach ($clientes_list as $nro => $client) { ?>
	                                <option value="<?//= $client['id_cliente'] ?>"><?///= $client['cliente'].' '.$client['nombre_factura'] ?>
	                                </option>
	                            <?php //} ?>
	                        	</select>
							</div>
						</div>
					</div> -->

					<div id="item">
						<div class="table-responsive margin-none">
							<table id="ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
								<thead>
									<tr class="active">
										<th class="text-nowrap" width="5%">#</th>
										<th class="text-nowrap" width="20%">Código</th>
										<th class="text-nowrap" width="30%">Nombre</th>
										<th class="text-nowrap" width="15%">Cantidad</th>
										<th class="text-nowrap" width="15%">Unidad</th>
										<!-- <th class="text-nowrap" width="10%">Precio</th> -->
										<!--<th class="text-nowrap" width="20%">Importe</th>-->
										<th class="text-nowrap text-center" width="15%"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>
								</thead>
								<tfoot>
									<!--<tr class="active">
										<th class="text-nowrap text-right" colspan="6">Importe total <?//= escape($moneda); ?></th>
										<th class="text-nowrap text-right" data-subtotal="">0.00</th>
										<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
									</tr>-->
								</tfoot>
								<tbody></tbody>
							</table>
						</div>
					</div>
					<!--
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?//= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;20]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 20">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>-->
					<br>
					<div class="form-group">
						<div class="col-xs-12 text-right">
							<button type="submit" class="btn btn-primary">
								<span class="glyphicon glyphicon-floppy-disk"></span>
								<span>Guardar</span>
							</button>
							<a href="?/promociones/reporte_promos_monto" class="btn btn-info">
								<i class="glyphicon glyphicon-remove"></i><span class="hidden-xs"> Cancelar</span>
							</a>
							<!-- <button type="reset" class="btn btn-default">
								<span class="glyphicon glyphicon-refresh"></span>
								<span>Restablecer</span>
							</button> -->
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!--REQUISITOS-->
	<!--<div id="busqueda" style="display:none" class="col-md-6">-->
	<!--<div id="busqueda" class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">-->
					<!--<h3 class="panel-title">-->
						<!--<span class="glyphicon glyphicon-search"></span>-->
						<!--<strong>REQUISITOS</strong>-->
					<!--</h3>-->

				<!--</div>
			</div>
	</div>-->

		<!--<div id="busqueda" style="display:none" class="col-md-6">-->
		<div id="busqueda" class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<span class="glyphicon glyphicon-search"></span>
						<strong>Búsqueda de productos</strong>
					</h3>
				</div>
				<div class="panel-body">
					<?php if ($permiso_mostrar) { ?>
					<div class="row">
						<div class="col-xs-12 text-right">
							<a href="?/manuales/mostrar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span> Ventas personales</span></a>
						</div>
					</div>
					<hr>
					<?php } ?>
					<?php if ($productos) { ?>
					<table id="productos" class="table table-bordered table-condensed table-striped table-hover table-xs">
						<thead>
							<tr class="active">
								<th class="text-nowrap">Imagen</th>
								<th class="text-nowrap">Código</th>
								<th class="text-nowrap">Nombre</th>
								<th class="text-nowrap">Descripción</th>
								<th class="text-nowrap">Tipo</th>
								<th class="text-nowrap">Stock</th>
								<th class="text-nowrap">Costo</th>
								<th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($productos as $nro => $producto) {
								$otro_precio = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id=b.id_unidad')->where('a.producto_id',$producto['id_producto'])->fetch();
								?>
							<tr>
								<td class="text-nowrap"><img src="<?= ($producto['imagen'] == '') ? imgs . '/image.jpg' : files . '/productos/' . $producto['imagen']; ?>" width="75" height="75"></td>
								<td class="text-nowrap" data-codigo="<?= $producto['id_producto']; ?>"><?= escape($producto['codigo']); ?></td>
								<td>
									<span><?= escape($producto['nombre']); ?></span>
									<span class="hidden" data-nombre="<?= $producto['id_producto']; ?>"><?= escape($producto['nombre_factura']); ?></span>
								</td>
								<td class="text-nowrap"><?= escape($producto['descripcion']); ?></td>
								<td class="text-nowrap"><?= escape($producto['categoria']); ?></td>
								<td class="text-nowrap text-right" data-stock="<?= $producto['id_producto']; ?>"><?= escape($producto['cantidad_ingresos'] - $producto['cantidad_egresos']); ?></td>
								<td class="text-nowrap text-right" data-valor="<?= $producto['id_producto']; ?>">
									*<?= escape($producto['unidad'].': '); ?><b><?= escape($producto['costo_ingresos']); ?></b>
								</td>
								<td class="text-nowrap">
									<?php if('ventas' !=1){?>
										<button type="button" class="btn btn-xs btn-primary" data-vender="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Vender"><span class="glyphicon glyphicon-share-alt"></span></button>
										<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
									<?php } else { ?>
										<div class="alert alert-danger">
											<strong>Advertencia!</strong>
											<p>Solo se puede escojer un item.</p>
										</div>
									<?php } ?>
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
		<?php } else { ?>
		<div class="col-xs-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="glyphicon glyphicon-home"></i> Ventas manuales</h3>
				</div>
				<div class="panel-body">
					<div class="alert alert-danger">
						<strong>Advertencia!</strong>
						<p>Usted no puede realizar esta operación, verifique que todo este en orden tomando en cuenta las siguientes sugerencias:</p>
						<ul>
							<li>No existe el almacén principal de la cual se puedan tomar los productos necesarios para la venta, debe fijar un almacén principal.</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
	<!--</div>-->
</div>
<h2 class="btn-primary position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una venta manual" data-placement="right"><i class="glyphicon glyphicon-edit display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
$(function () {

	//Obtiene valores de los campos selectize de Clientes 

	// $('#busqueda').css({'display':'none'});
	// $('#ventas').css({'display':'none'});

	$('#grupob').selectize({ plugins: ['remove_button'] });
	$('#clienteb').selectize({ plugins: ['remove_button'] });

	$('#grupob').on('change', function() {
		var group = $("#grupob").val();
		$("#grupos_item").val(group);
	});	

	$('#clienteb').on('change', function() {
		
		var cli = $("#clienteb").val();
		$("#cliente_id").val(cli);
	});	
	

	var table;
	var $cliente = $('#cliente');
	var $nit_ci = $('#nit_ci');
	var $nombre_cliente = $('#nombre_cliente');

	$('[data-vender]').on('click', function () {
		adicionar_producto($.trim($(this).attr('data-vender')));
	});

	$('[data-actualizar]').on('click', function () {
		var id_producto = $.trim($(this).attr('data-actualizar'));
		
		$('#loader').fadeIn(100);

		$.ajax({
			type: 'post',
			dataType: 'json',
			url: '?/manuales/actualizar',
			data: {
				id_producto: id_producto
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
					descontar_precio(producto.id_producto);
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

	$cliente.selectize({
		persist: false,
		createOnBlur: true,
		create: true,
		onInitialize: function () {
			$cliente.css({
				display: 'block',
				left: '-10000px',
				opacity: '0',
				position: 'absolute',
				top: '-10000px'
			});
		},
		onChange: function () {
			$cliente.trigger('blur');
		},
		onBlur: function () {
			$cliente.trigger('blur');
		}
	}).on('change', function (e) {
		var valor = $(this).val();
		valor = valor.split('|');
		$(this)[0].selectize.clear();
		if (valor.length != 1) {
			$nit_ci.prop('readonly', true);
			$nombre_cliente.prop('readonly', true);
			$nit_ci.val(valor[0]);
			$nombre_cliente.val(valor[1]);
		} else {
			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			if (es_nit(valor[0])) {
				$nit_ci.val(valor[0]);
				$nombre_cliente.val('').focus();
			} else {
				$nombre_cliente.val(valor[0]);
				$nit_ci.val('').focus();
			}
		}
	});
	

    //Validacion de la fecha fin
	$.formUtils.addValidator({
        name : 'date_end',
        validatorFunction : function(value, $el) {
        	var date_start = $(fecha_ini).val();
        	var date_fin = value;
        	if (date_start > date_fin) 
        		return false;
        	else
        		return true;		            
        },
        errorMessage : 'La fecha fin es menor que la fecha de inicio.',     
    });

	$.validate({
		modules: 'basic'
	});


	$('form:first').on('reset', function () {
		$('#ventas tbody').empty();
		$nit_ci.prop('readonly', false);
		$nombre_cliente.prop('readonly', false);
		calcular_total();
	});
});

function es_nit(texto) {
	var numeros = '0123456789';
	for(i = 0; i < texto.length; i++){
		if (numeros.indexOf(texto.charAt(i), 0) != -1){
			return true;
		}
	}
	return false;
}

function asig(val){

}

function adicionar_producto(id_producto) {

	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size()+1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
    var valor = $.trim($('[data-valor=' + id_producto + ']').text());	

    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');
    //console.log(porciones);
	var plantilla = '';
	var cantidad;
	if(numero<2){
		if ($producto.size()) {
			cantidad = $.trim($cantidad.val());
			cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
			cantidad = (cantidad < 1) ? cantidad = 1: cantidad;
			$cantidad.val(cantidad).trigger('blur');
		} else {
			//console.log('[data-producto]',222);
			plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
							'<td class="text-nowrap">' + numero + '</td>' +
							'<td class="text-nowrap"><input type="text" value="' + id_producto + '" name="productos_id" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
							'<td><input type="text" value="' + nombre + '" name="nombres[]" class="translate" tabindex="-1" data-validation="required">' + nombre + '</td>' +
							'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" readonly  data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
			if(porciones.length>2){
				plantilla = plantilla+'<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control input-xs" readonly >';
				aparte = porciones[1].split(':');
				if(numero < 0){
					for(var ic=1;ic<porciones.length;ic++){
							parte = porciones[ic].split(':');
						plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
					}
					plantilla = plantilla+'</select></td>'; /*+
					'<td><input type="text" value="' + aparte[1] + '" name="precios[]" readonly class="form-control input-xs text-right" autocomplete="off" data-precio="' + aparte[1] + '"  readonly  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';*/
				}
			}
			else{
				parte = porciones[1].split(':');
				plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>';/*+
										'<td><input type="text" value="' + parte[1] + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parte[1] + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';*/
				}
							plantilla = plantilla +/*'<td class="hidden"><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un número positivo entre 0 y 50" onkeyup="descontar_precio(' + id_producto + ')"></td>' +*/
							// '<td class="text-nowrap text-right" data-importe="">0.00</td>' +
							'<td class="text-nowrap text-center">' +
								'<button type="button" class="btn btn-xs btn-danger" data-toggle="tooltip" data-title="Eliminar producto" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')"><span class="glyphicon glyphicon-remove"></span></button>' +
							'</td>' +
						'</tr>';

			$ventas.append(plantilla);

			$ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
				$(this).select();
			});
			$ventas.find('[data-xxx]').on('change', function () {
				var v = $(this).find('option:selected').attr('data-yyy');
				$(this).parent().parent().find('[data-precio]').val(v);
				$(this).parent().parent().find('[data-precio]').attr(v);
				calcular_importe(id_producto);
			});

			$ventas.find('[title]').tooltip({
				container: 'body',
				trigger: 'hover'
			});

			$.validate({
				form: '#formulario',
				modules: 'basic',
				onSuccess: function () {
					guardar_factura();
				}
			});
		}
	calcular_importe(id_producto);
	}
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

function descontar_precio(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $precio = $producto.find('[data-precio]').val();
    console.log($precio);
	var $descuento = $producto.find('[data-descuento]');
	var precio, descuento;

	precio = $.trim($precio);
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseInt(descuento) : 0;
	precio = precio - (precio * descuento / 100);
    $producto.find('[data-precio]').val(precio.toFixed(2));

	calcular_importe(id_producto);
}

function calcular_importe(id_producto) {
	var $producto = $('[data-producto=' + id_producto + ']');
	var $cantidad = $producto.find('[data-cantidad]');
	var $precio = $producto.find('[data-precio]');
	var $descuento = $producto.find('[data-descuento]');
	var $importe = $producto.find('[data-importe]');
	var cantidad, precio, importe, fijo;

	fijo = $descuento.attr('data-descuento');
	fijo = ($.isNumeric(fijo)) ? parseInt(fijo) : 0;
	cantidad = $.trim($cantidad.val());
	cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
	precio = $.trim($precio.val());
	precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
	descuento = $.trim($descuento.val());
	descuento = ($.isNumeric(descuento)) ? parseInt(descuento) : 0;
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
	$('[data-total]:first').val(total.toFixed(3)).trigger('blur');
}

// function set_tipo_promo(){
// 	//CONTROL DE DESLIEGUES

// 	if($("#tipo").val()==2){

// 		$('#monto').css({'display':'block'});
// 		$('#rango').css({'display':'none'});

// 		$('#busqueda').css({'display':'none'});
// 		$('#ventas').css({'display':'none'});

// 	}else if($("#tipo").val()==4){
// 		$('#rango').css({'display':'none'});
// 		$('#monto').css({'display':'none'});
// 		$('#item').css({'display':'block'});

// 		$('#busqueda').css({'display':'block'});
// 		$('#ventas').css({'display':'block'});
// 	}
// }


</script>
<?php require_once show_template('footer-configured'); ?>