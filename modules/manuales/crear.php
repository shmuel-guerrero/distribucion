<?php

// Obtiene el almacen principal
//$almacen = $db->from('inv_almacenes')->where('principal', 'S')->fetch_first();
//$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;
if(!isset($params[0])){
	redirect('?/manuales/seleccionar_almacen');
}
if($params[0]!='')
	$almacen = $db->from('inv_almacenes')->where('id_almacen=',$params[0])->fetch_first();
$id_almacen = ($almacen) ? $almacen['id_almacen'] : 0;

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Define la fecha de hoy
$hoy = date('Y-m-d');

// Obtiene la dosificacion del periodo actual
$dosificacion = $db->from('inv_dosificaciones')->where('fecha_registro <=', $hoy)->where('fecha_limite >=', $hoy)->where('activo', 'S')->fetch_first();

// Obtiene los clientes
$clientes = $db->select('id_cliente, id_cliente as codigo_cliente,  nit as nit_ci, cliente as nombre_cliente, direccion, telefono, ubicacion, credito, dias')
				->from('inv_clientes')
				->fetch();

// Define el limite de filas
$limite_longitud = 200;

// Define el limite monetario
$limite_monetario = 10000000;
$limite_monetario = number_format($limite_monetario, 2, '.', '');

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);

//Agregado Recien
$_terminal=true;

?>
<?php require_once show_template('header-configured'); ?>
<style>
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
	.width-none {
		width: 10px;
	}
	.table-display > .thead > .tr,
	.table-display > .tbody > .tr,
	.table-display > .tfoot > .tr {
		margin-bottom: 15px;
	}
	.table-display > .thead > .tr > .th,
	.table-display > .tbody > .tr > .th,
	.table-display > .tfoot > .tr > .th {
		font-weight: bold;
	}
	@media (min-width: 768px) {
		.table-display {
			display: table;
		}
		.table-display > .thead,
		.table-display > .tbody,
		.table-display > .tfoot {
			display: table-row-group;
		}
		.table-display > .thead > .tr,
		.table-display > .tbody > .tr,
		.table-display > .tfoot > .tr {
			display: table-row;
		}
		.table-display > .thead > .tr > .th,
		.table-display > .thead > .tr > .td,
		.table-display > .tbody > .tr > .th,
		.table-display > .tbody > .tr > .td,
		.table-display > .tfoot > .tr > .th,
		.table-display > .tfoot > .tr > .td {
			display: table-cell;
		}
		.table-display > .tbody > .tr > .td,
		.table-display > .tbody > .tr > .th,
		.table-display > .tfoot > .tr > .td,
		.table-display > .tfoot > .tr > .th,
		.table-display > .thead > .tr > .td,
		.table-display > .thead > .tr > .th {
			padding-bottom: 15px;
			vertical-align: top;
		}
		.table-display > .tbody > .tr > .td:first-child,
		.table-display > .tbody > .tr > .th:first-child,
		.table-display > .tfoot > .tr > .td:first-child,
		.table-display > .tfoot > .tr > .th:first-child,
		.table-display > .thead > .tr > .td:first-child,
		.table-display > .thead > .tr > .th:first-child {
			padding-right: 15px;
		}
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
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Venta con factura manual</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-default">Venta con factura manual</h2>
				<hr>
				<form id="formulario" class="form-horizontal">
					<div style="zoom: 1;">
						<div class="form-group">
							<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
							<div class="col-sm-8">
								<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
									<option value="">Buscar</option>
									<?php foreach ($clientes as $cliente) { ?>
										<option value="<?= escape($cliente['nit_ci']) . '|' . escape($cliente['nombre_cliente']) . '|' . escape($cliente['id_cliente']) . '|' . escape($cliente['direccion']) . '|' . escape($cliente['telefono']) . '|' . escape($cliente['ubicacion']) . '|' . escape($cliente['credito']) . '|' . escape($cliente['dias']); ?>"><?= escape($cliente['codigo_cliente']) . ' &mdash; ' . escape($cliente['nombre_cliente']) . ' &mdash; ' . escape($cliente['nit_ci']); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
							<div class="col-sm-8">
								<input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100" maxlength="100">
								<input type="hidden" name="id_cliente" id="id_cliente" value="0" />
								<input type="hidden" value="0" name="credito" id="credito">
							</div>
						</div>
						<div class="form-group">
							<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" data-validation="required number" maxlength="20">
							</div>
						</div>
						<div class="form-group">
							<label for="telefono" class="col-sm-4 control-label">Teléfono:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="telefono" id="telefono" class="form-control text-uppercase" autocomplete="off" data-validation="required number" data-validation-allowing="-+./&() " data-validation-length="max100" maxlength="100">
							</div>
						</div>
						<div class="form-group">
							<label for="direccion" class="col-sm-4 control-label">Dirección:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="direccion" id="direccion" class="form-control text-uppercase" autocomplete="off" data-validation-allowing="-+./&() " maxlength="65">
							</div>
						</div>

						<div class="form-group" style="display:none">
							<label for="ubicacion" class="col-sm-4 control-label">Ubicación:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="ubicacion" id="ubicacion" class="form-control text-uppercase" autocomplete="off">
							</div>
						</div>
						<div class="form-group">
							<label for="nro_factura" class="col-sm-4 control-label">Número de factura:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="nro_factura" id="nro_factura" class="form-control" autocomplete="off" data-validation="required number" maxlength="15">
							</div>
						</div>
						<div class="form-group">
							<label for="nro_autorizacion" class="col-sm-4 control-label">Número de autorización:</label>
							<div class="col-sm-8">
								<input type="text" value="" name="nro_autorizacion" id="nro_autorizacion" class="form-control" autocomplete="off" data-validation="required number" maxlength="15">
							</div>
						</div>
					</div>
					<div class="table-responsive margin-none">
						<table id="ventas" class="table table-bordered table-condensed table-striped table-hover margin-none">
							<thead>
								<tr class="active">
									<th class="text-nowrap text-center">#</th>
									<th class="text-nowrap text-center">CÓDIGO</th>
									<th class="text-nowrap text-center">PRODUCTO</th>
                                    <th class="text-nowrap text-center">CANTIDAD</th>
                                    <th class="text-nowrap text-center">UNIDAD</th>
									<th class="text-nowrap text-center">PRECIO</th>
									<!-- <th class="text-nowrap text-center">DESCUENTO</th> -->
									<th class="text-nowrap text-center">IMPORTE</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</thead>
							<tfoot>
								<tr class="active">
									<th class="text-nowrap text-right" colspan="6">IMPORTE TOTAL <?= escape($moneda); ?></th>
									<th class="text-nowrap text-right" data-subtotal="">0.00</th>
									<th class="text-nowrap text-center">ACCIONES</th>
								</tr>
							</tfoot>
							<tbody></tbody>
						</table>
					</div>
					<div class="table-responsive" style="display:none">
						<table class="table table-bordered table-condensed table-striped table-hover margin-none">
							<thead>
								<tr>
									<th colspan='5' class="text-nowrap text-center">EXTRAS</th>
								</tr>
								<tr>
									<th class='text-center'>NOMBRE</th>
									<th class='text-center'>PRECIO</th>
									<th class='text-center'>UNIDAD</th>
									<th class='text-center'>CANTIDAD</th>
									<th class='text-center'>DESCUENTO</th>
								</tr>
							</thead>
							<tbody id='ExtrasF'>
							</tbody>
						</table>
					</div>
					<div class="form-group">
						<div class="col-xs-12">
							<input type="text" name="almacen_id" value="<?= $almacen['id_almacen']; ?>" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="El almacén no esta definido">
							<input type="text" name="nro_registros" value="0" class="translate" tabindex="-1" data-ventas="" data-validation="required number" data-validation-allowing="range[1;<?= $limite_longitud; ?>]" data-validation-error-msg="El número de productos a vender debe ser mayor a cero y menor a <?= $limite_longitud; ?>">
							<input type="text" name="monto_total" value="0" class="translate" tabindex="-1" data-total="" data-validation="required number" data-validation-allowing="range[0.01;<?= $limite_monetario; ?>],float" data-validation-error-msg="El monto total de la venta debe ser mayor a cero y menor a <?= $limite_monetario; ?>">
						</div>
					</div>

					<div class="form-group">
						<div class="col-xs-12 text-right">
								<!--Descuentos-->
								<div class="col-xs-12">
									<div class="col-lg-3  col-md-3 col-xs-3">
										<label for="tipo" class="col-sm-6 control-label">Descuento:</label>
										<div class="col-sm-6">
											<select name="tipo" id="tipo" onchange="tipo_descuento()" class="calcular_descuento form-control">
												<option value="0">Bs</option>
												<option value="1">%</option>
											</select>
										</div>
									</div>
									<div class="col-lg-8 col-md-8 col-xs-8"></div>

									<div class="col-xs-3" id="div-descuento" style="display:none">
										<label for="descuento" class="col-sm-4 control-label">(%):</label>
										<div class="col-sm-8">																					
										<input type="number" name="descuento_porc" id="descuento_porc" value="0" onchange="calcular_descuento_total()" class="calcular_descuento form-control" data-validation="number" data-validation-allowing="range[0.00;100.00],float" data-validation-error-msg="Debe ingresar un número entre 0% y 100%" maxlength="3">
										</div>
									</div>
									<div class="col-xs-3">
										<label for="descuento" class="col-sm-4 control-label">(Bs):</label>
										<div class="col-sm-8">
											<input type="text" value="0" name="descuento_bs" id="descuento_bs" onchange="calcular_descuento_total()" class="calcular_descuento form-control" data-validation="number" data-validation-allowing="range[0.00;<?= $limite_monetario; ?>],float">
										</div>
									</div>

									<div class="col-xs-3">
										<label for="importe_total_descuento" class="col-sm-4 control-label">Importe:</label>
										<div class="col-sm-8">
											<label id="importe_total_descuento" class="calcular_descuento col-sm-6 control-label"></label>
										</div>
										<input type="hidden" name="total_importe_descuento" id="total_importe_descuento" data-validation="number" data-validation-length="max100">
									</div>
								</div>
								<p>&nbsp;</p>
								<!----------------------->

						</div>
					</div>

					<!--<div class="col-xs-12 text-left">-->
					<!--	<label for="almacen" class="col-md-5 control-label">Venta empleado:</label>-->
					<!--	<div class="col-md-7 right">-->
					<!--		<div class="input-group">-->
					<!--			<span class="input-group-addon">-->
					<!--				<input type="checkbox" name="reserva">-->
					<!--			</span>-->
								<!--<input type="text" name="des_reserva" placeholder="Motivo" class="form-control">-->
					<!--			<select name="des_reserva" class="form-control">-->
					<!--				<option value='Sueldo'>Sueldo</option>-->
					<!--				<option value='Credito'>Credito</option>-->
					<!--				<option value='Contado'>Contado</option>-->
					<!--			</select>-->
					<!--		</div>-->
					<!--	</div>-->
					<!--</div>-->
					<!--<p>&nbsp;</p>-->
					
					
					
					<!-- cuentas --->
					<div id="credito_cliente">
					    <p class="text-info" id="cred"></p>
					</div>

					<?php if ($_user['rol'] == 'Superusuario' || $_user['rol'] == 'Administrador') { ?>
							<!--<div class="form-group">-->
							<!--	<label for="almacen" class="col-md-4 control-label">Forma de Pago:</label>-->
							<!--	<div class="col-md-8">-->
							<!--		<select name="forma_pago" id="forma_pago" class="form-control" data-validation="required number" onchange="set_plan_pagos()">-->
							<!--			<option value="1">Pago Completo</option>-->
							<!--			<option value="2">Plan de Pagos</option>-->
							<!--		</select>-->
							<!--	</div>-->
							<!--</div>-->
						<?php } ?>
					<!--    <div id="plan_de_pagos" style="display:none">-->
					<!--	<div class="form-group">-->
					<!--		<label for="almacen" class="col-md-4 control-label">Nro Cuotas:</label>-->
					<!--		<div class="col-md-8">-->
					<!--			<input type="text" value="1" id="nro_cuentas" name="nro_cuentas" class="form-control text-right" autocomplete="off" data-cuentas="" data-validation="required number" data-validation-allowing="range[1;360],int" data-validation-error-msg="Debe ser número entero positivo" onkeyup="set_cuotas()">-->
					<!--		</div>-->
					<!--	</div>-->
					<!--	<table id="cuentasporpagar" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">-->
					<!--		<thead>-->
					<!--			<tr class="active">-->
					<!--				<th class="text-nowrap text-center col-xs-4">Detalle</th>-->
					<!--				<th class="text-nowrap text-center col-xs-4">Fecha de Pago</th>-->
					<!--				<th class="text-nowrap text-center col-xs-4">Monto</th>-->
					<!--			</tr>-->
					<!--		</thead>-->
					<!--		<tbody>-->
					<!--			<?php for($i=1;$i<=36;$i++){ ?>-->
					<!--				<tr class="active cuotaclass">-->
					<!--					<?php if($i==1){ ?>-->
					<!--						<td class="text-nowrap" valign="center">-->
					<!--							<div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Pago Inicial:</div>-->
					<!--						</td>-->
					<!--					<?php } else{ ?>-->
					<!--						<td class="text-nowrap" valign="center">-->
					<!--							<div data-cuota="<?= $i ?>" data-cuota2="<?= $i ?>" class="cuota_div">Cuota <?= $i ?>:</div>-->
					<!--						</td>-->
					<!--					<?php } ?>-->
					<!--					<td><div data-cuota="<?= $i ?>" class="cuota_div"><div class="col-sm-12">-->
					<!--						<input id="inicial_fecha_<?= $i ?>" name="fecha[]" value="" class="form-control" autocomplete="off" <?php if($i==1){ ?> data-validation="required" <?php } ?> data-validation-format="<?= $formato_textual; ?>" onchange="javascript:change_date(<?= $i ?>);" onblur="javascript:change_date(<?= $i ?>);" -->
					<!--						<?php if($i>1){ ?>-->
					<!--							disabled="disabled"-->
					<!--						<?php } ?>-->
					<!--						>-->
					<!--					</div></div></td>-->
					<!--					<td><div data-cuota="<?= $i ?>" class="cuota_div"><input type="text" value="0" name="cuota[]" class="form-control text-right monto_cuota" maxlength="7" autocomplete="off" data-montocuota="0" data-validation-allowing="range[0.01;1000000.00],float" data-validation-error-msg="Debe ser número decimal positivo" onchange="javascript:calcular_cuota('<?= $i ?>');"></div></td>-->
					<!--				</tr>-->
					<!--			<?php } ?>-->
					<!--		</tbody>-->
					<!--		<tfoot>-->
					<!--			<tr class="active">-->
					<!--				<th class="text-nowrap text-center" colspan="2">Importe total <?= escape($moneda); ?></th>-->
					<!--				<th class="text-nowrap text-right" data-totalcuota="">0.00</th>-->
					<!--			</tr>-->
					<!--		</tfoot>-->
					<!--	</table>-->
					<!--	<br>-->
					<!--</div>-->

					<div class="form-group">
						<div class="col-xs-12 text-right">
							<button type="submit" class="btn btn-info">Guardar</button>
							<button type="reset" class="btn btn-default">Restablecer</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="panel panel-default" data-servidor="<?= ip_local . name_project . '/factura.php'; ?>">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-menu-hamburger"></span>
					<strong>Información sobre la transacción</strong>
				</h3>
			</div>
			<div class="panel-body">
				<h2 class="lead text-info">Información sobre la transacción</h2>
				<hr>
				<div class="table-display">
					<div class="tbody">
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-home"></span>
								<span>Casa matriz:</span>
							</div>
							<div class="td"><?= escape($_institution['nombre']); ?></div>
						</div>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-qrcode"></span>
								<span>NIT:</span>
							</div>
							<div class="td"><?= escape($_institution['nit']); ?></div>
						</div>
						<!-- <div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-phone"></span>
								<span>Terminal:</span>
							</div>
							<div class="td"><?= escape($_terminal['terminal']); ?></div>
						</div>
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-print"></span>
								<span>Impresora:</span>
							</div>
							<div class="td"><?= escape($_terminal['impresora']); ?></div>
						</div> -->
						<div class="tr">
							<div class="th">
								<span class="glyphicon glyphicon-user"></span>
								<span>Empleado:</span>
							</div>
							<div class="td"><?= ($_user['persona_id'] == 0) ? 'No asignado' : escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></div>
						</div>
					</div>
				</div>
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
				<h2 class="lead">Búsqueda de productos</h2>
				<hr>
				<?php if ($permiso_mostrar) : ?>
				<p class="text-right">
					<a href="?/manuales/mostrar" class="btn btn-info">Listado de mis ventas manuales</a>
				</p>
				<?php endif ?>
				<form method="post" action="?/manuales/buscar" id="form_buscar_0" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código" autofocus="autofocus">
						<span class="glyphicon glyphicon-barcode form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<form method="post" action="?/manuales/buscar" id="form_buscar_1" class="margin-bottom" autocomplete="off">
					<div class="form-group has-feedback">
						<input type="text" value="" name="busqueda" class="form-control" placeholder="Buscar por código, producto o categoría">
						<span class="glyphicon glyphicon-search form-control-feedback"></span>
					</div>
					<button type="submit" class="translate" tabindex="-1"></button>
				</form>
				<div id="contenido_filtrar"></div>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div class="col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<span class="glyphicon glyphicon-option-vertical"></span>
					<strong>Venta con factura manual</strong>
				</h3>
			</div>
			<div class="panel-body">
				<div class="alert alert-danger">
					<p>Usted no puede realizar ventas con facturas manuales, verifique que la siguiente información sea correcta:</p>
					<ul>
						<?php if (!$_terminal) { ?>
						<li>La terminal no está registrada ni autorizada para la venta con factura manual, ingrese al apartado de "terminales", verifique la terminal y reinicie el sistema.</li>
						<?php } ?>
						<?php if (!$dosificacion) { ?>
						<li>La dosificación actual no existe o ya caducó, ingrese al apartado "dosificaciones" para registrar o habilitar una nueva dosificación.</li>
						<?php } ?>
						<?php if (!$almacen) { ?>
						<li>El almacén principal no esta definido, ingrese al apartado de "almacenes" y designe a uno de los almacenes como principal.</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
</div>
<!-- <h2 class="btn-info position-left-bottom display-table btn-circle margin-all display-table" data-toggle="tooltip" data-title="Esto es una venta con factura manual" data-placement="right">
	<span class="glyphicon glyphicon-pencil display-cell"></span>
</h2> -->

<!-- Plantillas filtrar inicio -->
<div id="tabla_filtrar" class="hidden">
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped table-hover" style="width: 100%;;">
			<thead>
				<tr class="active">
					<th class="text-nowrap text-middle text-center" width="10%">Imagen</th>
					<th class="text-nowrap text-middle text-center" width="10%">Código</th>
					<th class="text-nowrap text-middle text-center" width="20%">Producto</th>
					<th class="text-nowrap text-middle text-center" width="5%">Categoria</th>
					<th class="text-nowrap text-middle text-center" width="10%">Stock</th>
					<th class="text-middle text-center" width="20%">Precio</th>
					<th class="text-nowrap text-middle text-center" width="20%">Acciones</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>
<table class="hidden">
	<tbody id="fila_filtrar" data-negativo="<?= imgs; ?>/" data-positivo="<?= files; ?>/productos/">
		<tr onclick="desp(this)">
			<td class="text-nowrap text-middle text-center">
				<img src="" class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
			</td>
			<td class="text-wrap text-middle" data-codigo=""></td>
			<td class="text-middle">
				<em></em>
				<span class="hidden" data-nombre=""></span>
			</td>
			<td class="text-wrap text-middle"></td>
			<td class="text-nowrap text-middle text-right" data-stock=""></td>
			<td class="text-nowrap text-middle text-right" data-valor=""></td>
			<td class="text-nowrap text-middle text-center ">
				<button type="button" class="btn btn-info" data-vender="" onclick="vender(this)">Vender</button>
				<button type="button" class="btn btn-default" data-actualizar="" onclick="actualizar(this)">Actualizar</button>
			</td>
			<td class="hidden" data-cant=""></td>
            <td class="hidden" data-stock2=""></td>
		</tr>
        <tr>
            <td colspan="6" class="text-nowrap text-middle text-center " data-desc="">
                <em2></em2>
            </td>
        </tr>
	</tbody>
</table>
<div id="mensaje_filtrar" class="hidden">
	<div class="alert alert-danger">No se encontraron resultados</div>
</div>
<!-- Plantillas filtrar fin -->

<!-- Modal mostrar inicio -->
<div id="modal_mostrar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content loader-wrapper">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<img src="" class="img-responsive img-rounded" data-modal-image="">
			</div>
			<div id="loader_mostrar" class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
	</div>
</div>
<!-- Modal mostrar fin -->
<?php
	$Fecha=date('Y-m-d');
	$Promociones=$db->query("SELECT id_promocion,nombre,tipo,min_promo,descuento_promo,monto_promo,item_promo FROM inv_promociones_monto WHERE '{$Fecha}'>=fecha_ini AND '{$Fecha}'<=fecha_fin")->fetch();
	$Valor='';
	foreach($Promociones as $Fila=>$Promocion):
		$Valor.="{$Promocion['id_promocion']}|{$Promocion['tipo']}|{$Promocion['min_promo']}|{$Promocion['descuento_promo']}|{$Promocion['monto_promo']}|{$Promocion['item_promo']}||";
	endforeach;
	$Valor=rtrim($Valor,'||');
?>
<input type='hidden' id='PromocionesF' value='<?=$Valor?>'>
<script src="<?= js; ?>/jquery.form-validator.min.js "></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
	var $cliente = $('#cliente');
	var $id_cliente = $('#id_cliente');
	var $nombre_cliente = $('#nombre_cliente');
	var $nit_ci = $('#nit_ci');
	var $telefono = $('#telefono');
	var $direccion = $('#direccion');
	var $ubicacion = $('#ubicacion');
	var $credito = $('#credito'); // para credito

	var $formulario = $('#formulario');

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
			$direccion.prop('readonly', true);
			$telefono.prop('readonly', true);
			$ubicacion.prop('readonly', true);

			$nit_ci.val(valor[0]);
			$nombre_cliente.val(valor[1]);
			$direccion.val(valor[3]);
			$telefono.val(valor[4]);
			$ubicacion.val(valor[5]);
			$id_cliente.val(valor[2]);
			
			$credito.val(valor[6]);
			$('#borrar').remove('#borrar')
			if(valor[6] == true || valor[6] == 1 || valor[6] == '1') {
				// $('#cred').text('Las ventas a este cliente son a credito de: ' + valor[8] + ' dias.');
				$('#cred').prepend(' <div class="alert alert-info" id="borrar"> <b>Forma de pago: </b> El cliente tiene un contrato de créditos de: '+ valor[7] + ' días.  </div> ');
			} else {
				$('#cred').prepend(' <div class="alert alert-success" id="borrar"> <b>Forma de pago: </b> El cliente no tiene contrato de créditos. </div> ');
			}
            calcular_descuento_total();
                
		} else {
			
			$('#id_cliente').val(0);
			$('#borrar').remove('#borrar')	

			$nit_ci.prop('readonly', false);
			$nombre_cliente.prop('readonly', false);
			$direccion.prop('readonly', false);
			$telefono.prop('readonly', false);
			$ubicacion.prop('readonly', false);

			if (es_nit(valor[0])) {				
				$nit_ci.val(valor[0]);
				$direccion.val(valor[3]);
				$telefono.val(valor[4]);
				$ubicacion.val(valor[5]);
				$nombre_cliente.val('').focus();
									
				//agrega 0 en telefono para los clientes nuevos					
				$telefono.val(0);
			} else {
				$nombre_cliente.val(valor[0]);
				$direccion.val(valor[3]);
				$telefono.val(valor[4]);
				$ubicacion.val(valor[5]);
				$nit_ci.val('').focus();
				
				//agrega 0 en nit y telefono para los clientes nuevos
				$nit_ci.val(0);
				$telefono.val(0);
			}
		}
	});

	$.validate({
		form: '#formulario',
		modules: 'basic',
		onSuccess: function () {
			bootbox.confirm('&iquest;Está seguro de realizar la venta con factura manual?', function (respuesta) {
				if (respuesta) {
					guardar_factura();
				}
			});
		}
	});

	$formulario.on('submit', function (e) {
		e.preventDefault();
	});

	$formulario.on('reset', function () {
		$('#ventas tbody').empty();
		$nit_ci.prop('readonly', false);
		$nombre_cliente.prop('readonly', false);
		$direccion.prop('readonly', false);
		$telefono.prop('readonly', false);
		$ubicacion.prop('readonly', false);
		calcular_total();
	}).trigger('reset');

	var blup = new buzz.sound('<?= media; ?>/blup.mp3');

	var $form_filtrar = $('#form_buscar_0, #form_buscar_1'), $contenido_filtrar = $('#contenido_filtrar'), $tabla_filtrar = $('#tabla_filtrar'), $fila_filtrar = $('#fila_filtrar'), $mensaje_filtrar = $('#mensaje_filtrar'), $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar');

	$form_filtrar.on('submit', function (e) {
		e.preventDefault();
		var $this, url, busqueda;
		$this = $(this);
		url = $this.attr('action');
		busqueda = $this.find(':text').val();
		$this.find(':text').attr('value', '');
		$this.find(':text').val('');
		if ($.trim(busqueda) != '') {
			$.ajax({
				type: 'post',
				dataType: 'json',
				url: url,
				data: {
					busqueda: busqueda,
					id_almacen: <?=$id_almacen?>
				}
			}).done(function (productos) {
				if (productos.length) {
                    var $ultimo;
                    var $ultimo2;
					$contenido_filtrar.html($tabla_filtrar.html());
					for (var i in productos) {
					    if ((parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos)) > 0) { // validacion stock mayor a cero
    						productos[i].imagen = (productos[i].imagen == '') ? $fila_filtrar.attr('data-negativo') + 'image.jpg' : $fila_filtrar.attr('data-positivo') + productos[i].imagen;
    						productos[i].codigo = productos[i].codigo;
    						$contenido_filtrar.find('tbody').append($fila_filtrar.html());
    						$contenido_filtrar.find('tbody tr:last').attr('data-busqueda', productos[i].id_producto);
                            $ultimo = $contenido_filtrar.find('tbody tr:nth-last-child(2)').children();
                            $ultimo2 = $contenido_filtrar.find('tbody tr:last').children();
                            $ultimo2.eq(0).find('em2').text(productos[i].descripcion);
                            $ultimo.eq(0).find('img').attr('src', productos[i].imagen);
    						$ultimo.eq(1).attr('data-codigo', productos[i].id_producto);
    						$ultimo.eq(1).text(productos[i].codigo);
    						$ultimo.eq(2).find('em').text(productos[i].nombre);
    						$ultimo.eq(2).find('span').attr('data-nombre', productos[i].id_producto);
    						$ultimo.eq(2).find('span').text(productos[i].nombre_factura);
    						$ultimo.eq(3).text(productos[i].categoria);
    
                            var str = productos[i].unidade;
    
                            if(!str){
                                str='';
                                str = '*(1)' +productos[i].unidad+ ':'+productos[i].precio_actual;
                            }else{
                                str = '*' + '(1)' + productos[i].unidad + ':'+productos[i].precio_actual+'\n '+' *('+str;
                            }
    						var res = str.replace(/\n/g, "<br>");
    						res = res.replace(/&/g, "<br>*(");
    						
    
    						// var res = str;
                            //console.log(res);
    						$ultimo.eq(4).attr('data-stock', productos[i].id_producto);
    						$ultimo.eq(4).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
    						$ultimo.eq(5).attr('data-valor', productos[i].id_producto);
    						$ultimo.eq(5).html(res);
    						$ultimo.eq(6).find(':button:first').attr('data-vender', productos[i].id_producto);
    						$ultimo.eq(6).find(':button:last').attr('data-actualizar', productos[i].id_producto);
    						$ultimo.eq(7).attr('data-cant', productos[i].id_producto);
                            $ultimo.eq(7).text(productos[i].cantidad2);
                            $ultimo.eq(8).attr('data-stock2', productos[i].id_producto);
                            $ultimo.eq(8).text(parseInt(productos[i].cantidad_ingresos) - parseInt(productos[i].cantidad_egresos));
					    } // fin validacion stock mayor a cero
					}
					if (productos.length == 1) {
					   // $contenido_filtrar.find('table tbody tr button').trigger('click');
					}
					$.notify({
						message: 'La operación fue ejecutada con éxito, se encontraron ' + productos.length + ' resultados.'
					}, {
						type: 'success'
					});
					blup.stop().play();
				} else {
					$contenido_filtrar.html($mensaje_filtrar.html());
				}
			}).fail(function () {
				$contenido_filtrar.html($mensaje_filtrar.html());
				$.notify({
					message: 'La operación fue interrumpida por un fallo.'
				}, {
					type: 'danger'
				});
				blup.stop().play();
			});
		} else {
			$contenido_filtrar.html($mensaje_filtrar.html());
		}
	}).trigger('submit');

	var $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar'), size, title, image;

	$modal_mostrar.on('hidden.bs.modal', function () {
		$loader_mostrar.show();
		$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
		$modal_mostrar.find('.modal-title').text('');
	}).on('show.bs.modal', function (e) {
		size = $(e.relatedTarget).attr('data-modal-size');
		title = $(e.relatedTarget).attr('data-modal-title');
		image = $(e.relatedTarget).attr('src');
		size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
		title = (title) ? title : 'Imagen';
		$modal_mostrar.find('.modal-dialog').attr('class', size);
		$modal_mostrar.find('.modal-title').text(title);
		$modal_mostrar.find('[data-modal-image]').attr('src', image);
	}).on('shown.bs.modal', function () {
		$loader_mostrar.hide();
	});	

	$('.calcular_descuento').on('keyup blur', function() {
		calcular_descuento_total();
	})
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
function desp(elemento) {
    $(elemento).next('tr').toggle();
}
function adicionar_producto(id_producto) {
	var $ventas = $('#ventas tbody');
	var $producto = $ventas.find('[data-producto=' + id_producto + ']');
    //id_producto = id_producto[id_producto.length - 1];
    //console.log(id_producto);
    //console.log('hola');
	var $cantidad = $producto.find('[data-cantidad]');
	var numero = $ventas.find('[data-producto]').size() + 1;
	var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
    var nombre = $.trim($('[data-nombre=' + id_producto + ']').text());
    var cantidad2 = $.trim($('[data-cant=' + id_producto + ']').text());
    var stock = $.trim($('[data-stock=' + id_producto + ']').text());
	var valor = $.trim($('[data-valor=' + id_producto + ']').text());
	var plantilla = '';
	var cantidad;
    var $modcant = $('#modcant');
    //console.log(aa);
    var posicion = valor.indexOf(':');
    var porciones = valor.split('*');

    cantidad2 = '1*' + cantidad2;
    z=1;
    var porci2 = cantidad2.split('*');
//console.log(porci2);
	if ($producto.size()) {
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		cantidad = (cantidad < 9999999) ? cantidad + 1 : cantidad;
		$cantidad.val(cantidad).trigger('blur');
        //console.log('hola');
	} else {
		plantilla = '<tr class="active" data-producto="' + id_producto + '">' +
						'<td class="text-nowrap text-middle"><b>' + numero + '</b></td>' +
						'<td class="text-nowrap text-middle"><input type="text" value="' + id_producto + '" name="productos[]" class="translate" tabindex="-1" data-validation="required number" data-validation-error-msg="Debe ser número">' + codigo + '</td>' +
						'<td class="text-middle">'+nombre+'<input type="hidden" value=\'' + nombre + '\' name="nombres[]" class="form-control" data-validation="required"></td>' +
						'<td class="text-middle"><input type="text" value="1" name="cantidades[]" class="form-control text-right" style="width: 70px;" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;' + stock + ']" data-validation-error-msg="Debe ingresar un número positivo entre 1 y ' + stock + '" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        // if(porciones.length>2){
        //     plantilla = plantilla+'<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control input-xs" >';
        //     aparte = porciones[1].split(':');
        //     for(var ic=1;ic<porciones.length;ic++){
        //         parte = porciones[ic].split(':');
        //         parte2 = parte[0].split(')');
        //         parte3 = parte2[0].split('(');
        //         console.log(parte2);
        //         plantilla = plantilla+'<option value="' +parte2[1]+ '" data-xyyz="' +stock+ '" data-yyy="' +parte[1]+ '" data-yyz="' +parte3[1]+ '" >' +parte2[1]+ '</option>';
        //     }



        //     plantilla = plantilla+'</select></td>'+
        //     '<td><input type="text" value="' + aparte[1] + '" name="precios[]" class="form-control input-xs text-right" autocomplete="off" data-precio="' + aparte[1] + '"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        // }else{
        //     sincant = porciones[1].split(')');
        //     console.log(sincant);
        //     parte = sincant[1].split(':');
        //     plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" data-xyyz="' +stock+ '" name="unidad[]" class="form-control  text-right" autocomplete="off" data-unidad="' + parte[0] + '" data-validation-error-msg="Debe ser un número decimal positivo"></td>'+
        //     '<td data-xyyz="' + stock + '" ><input type="text" value="' + parte[1] + '" name="precios[]" class="form-control  text-right" autocomplete="off"  data-precio="' + parte[1] + '" data-cant2="1"  data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>';
        // }
        if (porciones.length > 2) {
				plantilla = plantilla + '<td><select name="unidad[]" id="unidad[]" data-xxx="true" class="form-control"  >';
				aparte = porciones[1].split(':');
				for (var ic = 1; ic < porciones.length; ic++) {
					parte = porciones[ic].split(':');
					oparte = parte[0].split(')');

					//                console.log(parte);
					plantilla = plantilla + '<option value="' + oparte[1] + '" data-xyyz="' + stock + '" data-yyy="' + parte[1] + '" data-yyz="' + porci2[ic - 1] + '" >' + oparte[1] + '</option>';
				}
				aparte[1] = aparte[1].trimEnd();
				plantilla = plantilla + '</select></td>' +
					'<td><input type="text" value="' + $.trim(aparte[1]) + '" name="precios[]" class="form-control  text-right" autocomplete="off" data-precio="' + $.trim(aparte[1]) + '"  data-validation="required number" data-validation-allowing="range[0.1;10000000.00],float"    data-validation-error-msg="Debe ingresar un número decimal positivo mayor que 0" onkeyup="calcular_importe(' + id_producto + ')"></td>';
			} else {
				sincant = porciones[1].split(')');
				//            console.log(sincant);
				parte = sincant[1].split(':');
				plantilla = plantilla + '<td><input type="text" value="' + parte[0] + '" data-xyyz="' + stock + '" name="unidad[]" class="form-control  text-lefth" autocomplete="off" data-unidad="' + parte[0] + '" readonly data-validation-error-msg="Debe ingresar un número decimal positivo mayor que 0"></td>' +
					'<td data-xyyz="' + stock + '" ><input type="text" value="' + $.trim(parte[1]) + '" name="precios[]" class="form-control  text-right" autocomplete="off"  data-precio="' + parte[1] + '" data-cant2="1" data-validation="required number" data-validation-allowing="range[0.1;10000000.00],float" data-validation-error-msg="Debe ingresar un número decimal positivo mayor que 0 y menor que 10000000" onkeyup="calcular_importe(' + id_producto + ')"></td>';
			}

		/************************************************/
		//console.log(aparte[1]);

        //'<td class="text-middle"><input type="text" value="' + valor + '" name="precios[]" class="form-control text-right" style="width: 100px;" autocomplete="off" data-precio="' + valor + '" data-validation="required number" data-validation-allowing="range[0.01;10000000.00],float" data-validation-error-msg="Debe ser un número decimal positivo" onkeyup="calcular_importe(' + id_producto + ')"></td>' +
        plantilla = plantilla +//'<td><input type="hidden" value="0" name="descuentos[]" class="form-control text-right" style="width: 100px;" maxlength="10" autocomplete="off" data-descuento="0" data-validation="required number" data-validation-allowing="float,range[-100.00;100.00],negative" data-validation-error-msg="Debe ser un número entre -100.00 y 100.00" onkeyup="descontar_precio(' + id_producto + ')"></td>' +
						
						'<td class="text-nowrap text-middle text-right" data-importe="">0.00</td>' +
						'<td class="text-nowrap text-middle text-center">' +
							'<button type="button" class="btn btn-success" tabindex="-1" onclick="eliminar_producto(' + id_producto + ')">Eliminar</button>' +
						'</td>' +
					'</tr>';

		$ventas.append(plantilla);

		$ventas.find('[data-cantidad], [data-precio], [data-descuento]').on('click', function () {
			$(this).select();
		});

        $ventas.find('[data-producto=' + id_producto + ']').find('[data-xxx]').on('change', function () {
            var v = $(this).find('option:selected').attr('data-yyy');
			v = v.trim();
            var st = $(this).find('option:selected').attr('data-xyyz');

            $(this).parent().parent().find('[data-precio]').val(v);
            $(this).parent().parent().find('[data-precio]').attr(v);
            $(this).parent().parent().find('[data-precio]').attr(v);
            var z = $(this).find('option:selected').attr('data-yyz');
            var x = $.trim($('[data-stock2=' + id_producto + ']').text());
            var ze = Math.trunc(x / z);
            var zt = Math.trunc(st / z);
            $.trim($('[data-stock=' + id_producto + ']').text(ze));
            $(this).parent().parent().find('[data-cantidad]').attr('data-validation-allowing','range[1;' + zt + ']');
            $(this).parent().parent().find('[data-cantidad]').attr('data-validation-error-msg','Debe ingresar un número positivo entre 1 y ' + zt );
            //console.log($(this).parent().parent().find('[data-cantidad]').attr('data-validation-allowing'));

			//Convierte en 0 el descuento por producto si se cambia de unidad ::BECA
			$(this).parent().parent().find('[data-descuento]').val(0);				
            
			calcular_importe(id_producto);
			calcular_descuento_total();
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
			calcular_descuento_total();
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
	// var $producto = $('[data-producto=' + id_producto + ']');
	// var $precio = $producto.find('[data-precio]');
	// var $descuento = $producto.find('[data-descuento]');
	// var precio, descuento;

	// precio = $.trim($precio.attr('data-precio'));
	// precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0;
	// descuento = $.trim($descuento.val());
	// descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
	// precio = precio - (precio * descuento / 100);
	// $precio.val(precio.toFixed(2));

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
		fijo = ($.isNumeric(fijo)) ? parseFloat(fijo) : 0;
		cantidad = $.trim($cantidad.val());
		cantidad = ($.isNumeric(cantidad)) ? parseInt(cantidad) : 0;
		precio = $.trim($precio.val());
		precio = ($.isNumeric(precio)) ? parseFloat(precio) : 0.00;
		descuento = $.trim($descuento.val());
		descuento = ($.isNumeric(descuento)) ? parseFloat(descuento) : 0;
		
		var precio_con_descuento = precio - (precio * descuento / 100);

		// importe = cantidad * precio;
		importe = cantidad * precio_con_descuento;
		importe = importe.toFixed(2);
		$importe.text(importe);

		calcular_total();		
		calcular_descuento_total();
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

	$('#importe_total_descuento').text(total.toFixed(2));
	
	////////////////////////////////////////////////////////////////////////////
	let DescuentoMonto=0,
	DescuentoPorcentaje=0,
	PromocionesF=document.getElementById('PromocionesF').value,
	totalA=total;
	console.log(PromocionesF);
	if(PromocionesF!=''){
		let ExtrasF=document.getElementById('ExtrasF');
		ExtrasF.innerHTML='';
		PromocionesF=PromocionesF.split('||');
		for(let i=0;i<PromocionesF.length;++i){
			let Promocion=PromocionesF[i].split('|');
			if(total>=Promocion[2]){
				switch(parseInt(Promocion[1])){
					case 2:
						DescuentoMonto+=parseInt(Promocion[4]);
						totalA=totalA-Promocion[4];
					break;
					case 3:
						DescuentoPorcentaje+=parseInt(Promocion[3]);
						let porcentaje=(0.01*Promocion[3]).toFixed(3);
						let Aux=(total*porcentaje).toFixed(3);
						totalA=totalA-Aux;
					break;
					case 4:
						var DatosE=Promocion[5].split('--');
						ExtrasF.innerHTML+=`<tr>
									<td>
										<input type='hidden' name='IdExtra[]' value='${DatosE[0]}'>
										<input type='text' name='NombreExtra[]' value='${DatosE[1]}' class='form-control' readonly>
									</td>
									<td class='text-nowrap text-center'>
										<input type='text' name='PrecioExtra[]' value='${DatosE[2]}' class='form-control' readonly>
									</td>
									<td>
										<input type='text' name='UnidadExtra[]' value='${DatosE[3]}' class='form-control' readonly>
									</td>
									<td>
										<input type='text' name='CantidadExtra[]' value='${DatosE[4]}' class='form-control' readonly>
									</td>
									<td class='text-nowrap text-center'>100%</td>
								</tr>`;
					break;
				}
			}
		}
	}
	////////////////////////////////////////////////////////////////////////////--
	calcular_descuento_total();
}

function guardar_factura() {
	var data = $('#formulario').serialize();

	$('#loader').fadeIn(100);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/manuales/guardar',
		data: data
	}).done(function (venta) {
		switch (venta.status) {
				case 'success':
						$.notify({
								message: 'La venta con factura manual fue realizada satisfactoriamente.'
							}, {
								type: 'success'
							});						
						window.location.reload();
					break;
				case 'invalid':
					$('#loader').fadeOut(100);
							$.notify({								
								message: 'Productos observados' + venta.responce
							}, {
								type: 'warning'
							});
					break;			
				default:
					$('#loader').fadeOut(100);
						$.notify({
							message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de remisión, verifique si la se guardó parcialmente.'
						}, {
							type: 'danger'
						});
						break;
			}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurrió un problema en el proceso, no se puedo obtener la dosificación ni tampoco guardar los datos de la venta, verifique si la se guardó parcialmente.'
		}, {
			type: 'danger'
		});
	});
}

function imprimir_factura(venta) {
	window.open('?/manuales/imprimir/' + venta, true);
	window.location.reload();
	return;
	/*var servidor = $.trim($('[data-servidor]').attr('data-servidor'));
	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: servidor,
		data: venta
	}).done(function (respuesta) {
		$('#loader').fadeOut(100);
		switch (respuesta.estado) {
			case 's':
				window.location.reload();
				break;
			case 'p':
				$.notify({
					message: 'La impresora no responde, asegurese de que este conectada y registrada en el sistema, una vez solucionado el problema vuelva a intentarlo nuevamente.'
				}, {
					type: 'danger'
				});
				break;
			default:
				$.notify({
					message: 'Ocurrió un problema durante el proceso, no se envió los datos para la impresión de la factura.'
				}, {
					type: 'danger'
				});
				break;
		}
	}).fail(function () {
		$('#loader').fadeOut(100);
		$.notify({
			message: 'Ocurrió un problema durante el proceso, reinicie la terminal para dar solución al problema y si el problema persiste contactese con el con los desarrolladores.'
		}, {
			type: 'danger'
		});
	}).always(function () {
		$('#formulario').trigger('reset');
		$('#form_buscar_0').trigger('submit');
	});*/
}

function vender(elemento) {
	var $elemento = $(elemento), vender;
	vender = $elemento.attr('data-vender');
	adicionar_producto(vender);
}

function actualizar(elemento) {
	var $elemento = $(elemento), actualizar;
	actualizar = $elemento.attr('data-actualizar');
		
	$('#loader').fadeIn(100);

	$.ajax({
		type: 'POST',
		dataType: 'json',
		url: '?/manuales/actualizar',
		data: {
			id_producto: actualizar
		}
	}).done(function (producto) {
		if (producto) {
			var $busqueda = $('[data-busqueda="' + producto.id_producto + '"]');
			var precio = parseFloat(producto.precio).toFixed(2);
			var stock = parseInt(producto.stock);

			$busqueda.find('[data-stock]').text(stock);
			$busqueda.find('[data-valor]').text(precio);

			var $producto = $('[data-producto=' + producto.id_producto + ']');
			var $cantidad = $producto.find('[data-cantidad]');
			var $precio = $producto.find('[data-precio]');

			if ($producto.size()) {
				$cantidad.attr('data-validation-allowing', 'range[1;' + stock + ']');
				$cantidad.attr('data-validation-error-msg', 'Debe ingresar un número positivo entre 1 y ' + stock);
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
	// valorG=parseFloat($('[data-subtotal]:first').text());
	valorG=parseFloat($('#total_importe_descuento').val());

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

function tipo_descuento() {
	var descuento = $('#tipo').val();
	$('#descuento_bs').val('0');
	$('#descuento_porc').val('0');		
	if (descuento == 0) {
		console.log(0);
		$('#div-descuento').hide();
	} else if (descuento == 1) {
		console.log(1);
		$('#div-descuento').show();
	}		
	calcular_descuento_total();
}
function calcular_descuento() {
	var $ventas = $('#ventas tbody');
	var $total = $('[data-subtotal]:first');
	var $importes = $ventas.find('[data-importe]');

	var descuento = $('#descuento_porc').val();

	var importe, total = 0;

	$importes.each(function(i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;
	});
	$total.text(total.toFixed(2));
	var importe_total = total.toFixed(2);
	//console.log(importe_total);

	var total_descuento = 0,
		formula = 0,
		total_importe_descuento = 0;
	console.log(descuento + 'jhfhgdghd');

	if (descuento == null) {
		var descuento_bs = $('#descuento_bs').val();
		console.log(descuento_bs + 'vacio0000');
		total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));

	} else if (descuento == 0) {
		var descuento_bs = $('#descuento_bs').val();
		console.log(descuento_bs + 'vacio0000');
		total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));

	} else if (descuento != "") {
		//console.log(descuento+'dif vacio');
		//var total_descuento=0, formula=0, total_importe_descuento=0;
		//total_descuento=descuento*100;
		//formula=(descuento/importe_total)*100;
		formula = (descuento / 100) * importe_total;

		total_importe_descuento = parseFloat(importe_total) - parseFloat(formula);

		$('#descuento_bs').val(formula.toFixed(2));
		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));
	}
}

function calcular_descuento_total() {
	var $ventas = $('#ventas tbody');
	var $total = $('[data-subtotal]:first');
	var $importes = $ventas.find('[data-importe]');

	var descuento = $('#descuento_porc').val();

	var importe, total = 0;

	$importes.each(function(i) {
		importe = $.trim($(this).text());
		importe = parseFloat(importe);
		total = total + importe;
	});
	$total.text(total.toFixed(2));
	var importe_total = total.toFixed(2);
	//console.log(importe_total);

	var total_descuento = 0,
		formula = 0,
		total_importe_descuento = 0;
	// Éste código ya no es necesario pues los valores siempre serán 0 (nunca habrá valores null o menor que 0) ::BECA
	if (descuento == null || descuento == '') {
		var descuento_bs = $('#descuento_bs').val();
		//console.log(descuento_bs+'vacio');
		// descuento_bs = (descuento_bs == 0 || descuento_bs == '')?0:descuento_bs;
		total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));

	} else if (descuento < 0) {
		var descuento_bs = $('#descuento_bs').val();
		//console.log(descuento_bs+'vacio');
		// escuento_bs = (descuento_bs == 0 || descuento_bs == '')?0:descuento_bs;
		total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

		$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
		$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));		

	} else if (descuento != "") {

		//console.log(descuento+'dif vacio');
		//var total_descuento=0, formula=0, total_importe_descuento=0;
		//total_descuento=descuento*100;
		//formula=(descuento/importe_total)*100;

		if(descuento == 0){
			var descuento_bs = $('#descuento_bs').val();
			if(descuento_bs == null || descuento_bs == ''){
				descuento_bs = 0;
			}			
			total_importe_descuento = parseFloat(importe_total) - parseFloat(descuento_bs);

			if(total_importe_descuento < 0){
				alert('El descuento está excediendo el Monto Total');
				var aux = 0;
				$('#descuento_bs').val(aux);
			}

			$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
			$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));
		}else{
			formula = (descuento / 100) * importe_total;				
			total_importe_descuento = parseFloat(importe_total) - parseFloat(formula);

			$('#descuento_bs').val(formula.toFixed(2));
			$('#importe_total_descuento').html(total_importe_descuento.toFixed(2));
			$('#total_importe_descuento').val(total_importe_descuento.toFixed(2));
		}
		
	} 
}
</script>
<?php require_once show_template('footer-configured'); ?>