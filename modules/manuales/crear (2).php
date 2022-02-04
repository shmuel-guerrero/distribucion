<?php
$formato_textual = get_date_textual($_institution['formato']);
// Obtiene el almacen principal
$almacen = $db->from('inv_almacenes')->fetch_first();
//echo json_encode($almacen);
//exit();
//$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;
// Verifica si existe el almacen
if($params[0]!='')
	$almacen = $db->from('inv_almacenes')->where('id_almacen=',$params[0])->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la fecha de hoy
$hoy = date('Y-m-d');

// Obtiene los clientes
//$clientes = $db->select('nombre_cliente, nit_ci, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')->from('inv_egresos')->group_by('nombre_cliente, nit_ci')->order_by('nombre_cliente asc, nit_ci asc')->fetch();
$clientes = $db->query("SELECT DISTINCT a.nombre_cliente, a.nit_ci
						from inv_egresos a
						LEFT JOIN inv_clientes b ON a.nit_ci = b.nit
						UNION SELECT DISTINCT b.cliente as nombre_cliente, b.nit as nit_ci
						from inv_egresos a
						RIGHT JOIN inv_clientes b ON a.nit_ci = b.nit
						ORDER BY nombre_cliente asc, nit_ci asc")->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

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
#cuentasporpagar td{
		padding:0; height: 0; border-width: 0px;
	}
	.cuota_div{
		height:0; overflow: hidden;
	}
</style>
<div class="row">
	<?php if ($almacen) { ?>
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-list"></span>
					<strong>Datos de la venta</strong>
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
				<form method="post" action="?/manuales/guardar" class="form-horizontal">
					<div class="form-group">
						<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
						<div class="col-sm-8">
							<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./& " data-validation-optional="true">
								<option value="">Buscar</option>
								<?php foreach ($clientes as $cliente) { ?>
								<option value="<?= escape($cliente['nit_ci']) . '|' . escape($cliente['nombre_cliente']); ?>"><?= escape($cliente['nit_ci']) . ' &mdash; ' . escape($cliente['nombre_cliente']); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./& " data-validation-length="max100">
						</div>
					</div>
					<div class="form-group">
						<label for="nro_factura" class="col-sm-4 control-label">Número de factura:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nro_factura" id="nro_factura" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
						</div>
					</div>
					<div class="form-group">
						<label for="nro_autorizacion" class="col-sm-4 control-label">Número de autorización:</label>
						<div class="col-sm-8">
							<input type="text" value="" name="nro_autorizacion" id="nro_autorizacion" class="form-control text-uppercase" autocomplete="off" data-validation="required number">
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
                                    <th class="text-nowrap">Unidad</th>
									<th class="text-nowrap">Precio</th>
									<th class="text-nowrap">Descuento</th>
									<th class="text-nowrap">Importe</th>
									<th class="text-nowrap text-center"><span class="glyphicon glyphicon-trash"></span></th>
								</tr>
							</thead>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="7">Importe total <?= escape($moneda); ?></th>
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
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;20]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a 20">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a 1000000.00">
						</div>
					</div>


					<!-- cuentas --->
					
					<?php if ($_user['rol'] == 'Superusuario' || $_user['rol'] == 'Administrador') { ?>
							<div class="form-group">
								<label for="almacen" class="col-md-4 control-label">Forma de Pago:</label>
								<div class="col-md-8">
									<select name="forma_pago" id="forma_pago" class="form-control" data-validation="required number" onchange="set_plan_pagos()">
										<option value="1">Pago Completo</option>
										<option value="2">Plan de Pagos</option>								
									</select>
								</div>
							</div>
						<?php } ?>
									
					<div id="plan_de_pagos" style="display:none">
						<div class="form-group">
							<label for="almacen" class="col-md-4 control-label">Nro Cuotas:</label>
							<div class="col-md-8">
								<input type="text" value="1" id="nro_cuentas" name="nro_cuentas" class="form-control text-right" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo" onkeyup="set_cuotas()">
							</div>
						</div>
						
						<table id="cuentasporpagar" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center col-xs-4">Detalle</th>
									<th class="text-nowrap text-center col-xs-4">Fecha de Pago</th>
									<th class="text-nowrap text-center col-xs-4">Monto</th>
								</tr>
							</thead>
							<tbody>
								<?php for($i=1;$i<=36;$i++){ ?>
									<tr class="active cuotaclass">
										<?php if($i==1){ ?>
											<td class="text-nowrap" valign="center">
												<div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Pago Inicial:</div>
											</td>																	
										<?php } else{ ?>
											<td class="text-nowrap" valign="center">
												<div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Cuota <?= $i ?>:</div>
											</td>							
										<?php } ?>
										
										<td><div data-cuota="<?= $i ?>" class="cuota_div"><div class="col-sm-12">
											<input id="inicial_fecha_<?= $i ?>" name="fecha[]" value="" class="form-control" autocomplete="off" <?php if($i==1){ ?> data-validation="required" <?php } ?> data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $i ?>);" onblur="javascript:change_date(<?= $i ?>);" 
											<?php if($i>1){ ?>
												disabled="disabled"
											<?php } ?>
											>
										</div></div></td>
										<td><div data-cuota="<?= $i ?>" class="cuota_div"><input type="text" value="0" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="0" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $i ?>');"></div></td>
									</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-center" colspan="2">Importe total <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-totalcuota="">0.00</th>
								</tr>
							</tfoot>							
						</table>
						<br>
					</div>
					
					<!--<div class="form-group">
						<div class="col-xs-12">
							<input type="text" id="nro_plan_pagos" name="nro_plan_pagos" value="1" class="translate" tabindex="-1" data-nro-pagos="1" data-validation="required number" data-validation-allowing="range[1;360]" data-validation-error-msg="Debe existir como mínimo una cuota">
							<input type="text" id="monto_plan_pagos" name="monto_plan_pagos" value="0" class="translate" tabindex="-1" data-total-pagos="" data-validation="required number" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="La suma de las cuotas debe ser igual al costo total de la venta">
						</div>
					</div>-->
					
						

					<!-------------------->

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
		<div class="panel panel-default" data-servidor="<?= ip_local . name_project . '/factura.php'; ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-cog"></span>
					<strong>Datos generales</strong>
				</h3>
			</div>
			<div class="panel-body">
				<ul class="list-group">
					<li class="list-group-item">
						<i class="glyphicon glyphicon-home"></i>
						<strong>Casa Matriz: </strong>
						<span><?= escape($_institution['nombre']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-qrcode"></i>
						<strong>NIT: </strong>
						<span><?= escape($_institution['nit']); ?></span>
					</li>
					<li class="list-group-item">
						<i class="glyphicon glyphicon-user"></i>
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
							<th class="text-nowrap">Precio</th>
							<th class="text-nowrap"><i class="glyphicon glyphicon-cog"></i></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($productos as $nro => $producto) {
                            $otro_precio = $db->select('*')->from('inv_asignaciones a')->join('inv_unidades b','a.unidad_id=b.id_unidad AND a.visible = "s"')->where('a.producto_id',$producto['id_producto'])->fetch();
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
                                *<?= escape($producto['unidad'].': '); ?><b><?= escape($producto['precio_actual']); ?></b>
                                <?php foreach($otro_precio as $otro){ ?>
                                    <br/>*<?= escape($otro['unidad'].': '); ?><b><?= escape($otro['otro_precio']); ?></b>
                                <?php } ?>
                            </td>
							<td class="text-nowrap">
								<button type="button" class="btn btn-xs btn-primary" data-vender="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Vender"><span class="glyphicon glyphicon-share-alt"></span></button>
								<button type="button" class="btn btn-xs btn-success" data-actualizar="<?= $producto['id_producto']; ?>" data-toggle="tooltip" data-title="Actualizar stock y precio del producto"><span class="glyphicon glyphicon-refresh"></span></button>
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
</div>
<h2 class="btn-primary position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una venta manual" data-placement="right"><i class="glyphicon glyphicon-edit display-cell"></i></h2>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>

<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>

<script>
$(function () {
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
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
	var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
	var stock = $.trim($('[data-stock=' + id_producto + ']').text());
    var valor = $.trim($('[data-valor=' + id_producto + ']').text());

    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');
    //console.log(porciones);
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
						'<td><input type="text" value="1" name="cantidades[]" class="form-control input-xs text-right" maxlength="7" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ser un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
		if(porciones.length>2){
            plantilla = plantilla+'<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control input-xs" >';
            aparte = porciones[1].split(':');
            for(var ic=1;ic<porciones.length;ic++){
                    parte = porciones[ic].split(':');
                //console.log(parte);
                plantilla = plantilla+'<option value="' +parte[0]+ '" data-yyy="' +parte[1]+ '" >' +parte[0]+ '</option>';
            }
            plantilla = plantilla+'</select></td>'+
            '<td><input type="text" value="' + aparte[1] + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + aparte[1] + '"  readonly  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcularimporte(' + id_producto + ')"></td>';
        }
        else{
            parte = porciones[1].split(':');
            plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" name="unidad[]" class="form-control input-xs text-right" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
                                    '<td><input type="text" value="' + parte[1] + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + parte[1] + '"  readonly  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcularimporte(' + id_producto + ')"></td>';
        }
						plantilla = plantilla +'<td><input type="text" value="0" name="descuentos[]" class="form-control input-xs text-right" maxlength="2" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="range[0;50]" data-validation-error-msg="Debe ser un número positivo entre 0 y 50" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
						'<td class="text-nowrap text-right" data-importe="">0.00</td>' +
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
	$('[data-total]').val(total.toFixed(3)).trigger('blur');
}


function calcular_descuento_total(){
	var valor_descuento = $("#valor_descuento").val();
	var total = parseFloat($('[data-total]:first').val());
	var $porcentaje = $('[data-subporcentaje]:first');

	if(valor_descuento == "" || valor_descuento >= 100){
		descuento_total = total;
	}
	else{
		descuento = (total * parseFloat(valor_descuento)) / 100;
		descuento_total = total - descuento;
	}
	$porcentaje.text(descuento_total.toFixed(1)+"0");
	$('[data-porcentaje]:first').val(descuento_total.toFixed(1)+"0").trigger('blur');

	set_cuotas_val();
	calcular_cuota(1000);	

}

function guardar_nota() {
	var data = $('#formulario').serialize();

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/manuales/guardar',
		data: data
	}).done(function (venta) {
		if (venta) {
			$.notify({
				message: 'La venta manual fue realizada satisfactoriamente.'
			}, {
				type: 'success'
			});
			imprimir_factura(venta);
		} else {
			$('#loader').fadeOut(100);
			$.notify({
				message: 'Ocurrió un problema en el proceso, no se puedo obtener la dosificación ni tampoco guardar los datos de la venta, verifique si la se guardó parcialmente.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function (e) {
		console.log(e);
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurrió un problema en el proceso, no se puedo obtener la dosificación ni tampoco guardar los datos de la venta, verifique si la se guardó parcialmente.'
		}, {
			type: 'danger'
		});
	});
}

function imprimir_factura(venta) {
	//$.open('?/manuales/imprimir/' + venta, true);
	window.location.reload();
}


function cargar_cantidad(){
	var id_producto = $("#id_producto_cantidad").val();
	var id_asignacion = $("#id_asignacion_cantidad").val();
	var cantidad = $("#cant_cantidad").val();
}

function actualizar(elemento) {
	var $elemento = $(elemento), id_asignacion,id_producto;
	id_producto = $elemento.attr('data-actualizar-producto');
	id_asignacion = $elemento.attr('data-actualizar');

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/manuales/actualizar',
		data: {
			id_producto: id_producto,
			id_asignacion: id_asignacion
		}
	}).done(function (producto) {
		if (producto) {
			var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
			var unidad = producto.unidad;
			var precio = parseFloat(producto.precio_actual).toFixed(1)+"0";
			var stock = parseInt(producto.stock);

			$busqueda.find('[data-stock]').text(stock);
			$busqueda.find('[data-nombre-unidad='+id_asignacion+']').text(unidad);
			$busqueda.find('[data-precio-asignacion='+id_asignacion+']').text(precio);
			
			var $producto = $('[data-asignacion=' + producto.id_asignacion + ']');
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
				message: 'El stock y el precio del producto se actualizaron satisfactoriamente.'
			}, {
				type: 'success'
			});
		} else {
			$.notify({
				message: 'Ocurrió un problema durante el proceso, es posible que no existe un almacén principal.'
			}, {
				type: 'danger'
			});
		}
	}).fail(function () {
		$.notify({
			message: 'Ocurrió un problema durante el proceso, no se pudo actualizar el stock ni el precio del producto.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#loader').fadeOut(100);
	});


}

//cuentas
var formato = $('[data-formato]').attr('data-formato');
var $inicial_fecha=new Array();
for(i=1;i<36;i++){
	$inicial_fecha[i] = $('#inicial_fecha_'+i+'');
	$inicial_fecha[i].datetimepicker({
		//format: formato
		format: 'DD-MM-YYYY'
	});
}
function set_cuotas() {	
	var cantidad = $('#nro_cuentas').val();
	var $compras = $('#cuentasporpagar tbody');
	
	$("#nro_plan_pagos").val(cantidad);

	if(cantidad>36){
		cantidad=36;
		$('#nro_cuentas').val("36")
	}	
	for(i=1;i<=cantidad;i++){
		$('[data-cuota=' + i + ']').css({'height':'auto', 'overflow':'visible'});				
		$('[data-cuota2=' + i + ']').css({'margin-top':'10px;'});				
		$('[data-cuota=' + i + ']').parent('td').css({'height':'auto', 'border-width':'1px','padding':'5px'});				
	}
	for(i=parseInt(cantidad)+1;i<=36;i++){
		$('[data-cuota=' + i + ']').css({'height':'0px', 'overflow':'hidden'});				
		$('[data-cuota2=' + i + ']').css({'margin-top':'0px;'});				
		$('[data-cuota=' + i + ']').parent('td').css({'height':'0px', 'border-width':'0px','padding':'0px'});
	}
	set_cuotas_val();
	calcular_cuota(1000);
}
function set_cuotas_val() {
	nro=$('#nro_cuentas').val();	
	valorG=parseFloat($('[data-subtotal]:first').text());

	valor=valorG/nro;
	for(i=1;i<=nro;i++){
		if(i==nro){
			final=valorG-(valor.toFixed(1)*(i-1));
			$('[data-cuota=' + i + ']').children('.monto_cuota').val(final.toFixed(1)+"0");
		}else{
			$('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
		}
	}		
}

function set_plan_pagos(){
	if($("#forma_pago").val()==1){
		$('#plan_de_pagos').css({'display':'none'});
		if( $('#nro_cuentas').val()<=0 ){
			$('#nro_cuentas').val('1');
			calcular_cuota(1000);
			$("#nro_plan_pagos").val('1');
		}
	}
	else{
		$('#plan_de_pagos').css({'display':'block'});
	}
}
function calcular_cuota(x) {
	var cantidad = $('#nro_cuentas').val();
	var total = 0;
	
	for(i=1;i<=x && i<=cantidad;i++){
		importe=$('[data-cuota=' + i + ']').children('.monto_cuota').val();
		importe = parseFloat(importe);
		total = total + importe;
	}
	//console.log(total);
	valorTotal=parseFloat($('[data-total]:first').val());
	if(nro>x){
		valor=(valorTotal-total)/(nro-x);
	}
	else{
		valor=0;
	}

	for(i=(parseInt(x)+1);i<=cantidad;i++){
		if(valor>=0){
			if(i==cantidad){
				valor=valorTotal-total;
				$('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
			}
			else{
				$('[data-cuota=' + i + ']').children('.monto_cuota').val(valor.toFixed(1)+"0");
			}
			total = total + (valor.toFixed(1)*1);
		}
		else{
			$('[data-cuota=' + i + ']').children('.monto_cuota').val("0.00");
		}
	}	
	
	$('[data-totalcuota]').text( total.toFixed(1)+"0" );
	valor=parseFloat( $('[data-subporcentaje]:first').text() );
	if(valor==total.toFixed(1)+"0"){
		$('[data-total-pagos]:first').val(1);	
		$('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg","");	
	}
	else{
		$('[data-total-pagos]:first').val(0);	
		$('[data-total-pagos]:first').parent('div').children('#monto_plan_pagos').attr("data-validation-error-msg","La suma de las cuotas es diferente al costo total « "+total.toFixed(1)+"0"+" / "+valor.toFixed(1)+"0"+" »");	
	}

}
function change_date(x){
	if($('#inicial_fecha_'+x).val()!=""){
		if(x<36){
			$('#inicial_fecha_'+(x+1)).removeAttr("disabled");
		}
	}	
	else{
		for(i=x;i<=35;i++){
			$('#inicial_fecha_'+(i+1)).val("");
			$('#inicial_fecha_'+(i+1)).attr("disabled","disabled");
		}
	}
}

function setPago(){
	$('#data-tipo-pago').val(2);
}



</script>
<?php require_once show_template('footer-configured'); ?>