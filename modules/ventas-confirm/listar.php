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
$ventas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')
				->from('inv_egresos i')
				->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
				->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
				->where('i.empleado_id', $_user['persona_id'])
				->where('i.tipo', 'Venta')
				->where('i.codigo_control', '')
				->where('i.provisionado', 'S')
				->where('i.fecha_egreso >= ', $fecha_inicial)
				->where('i.fecha_egreso <= ', $fecha_final)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = true;
$permiso_ver = true;
$permiso_eliminar = true;
$permiso_cambiar = true;

require libraries . '/mis_clases/class_ventas.php';
$validarVenta = new ventasClass();

?>
<?php require_once show_template('header-configured'); ?>
<style>
.table-xs tbody {
	font-size: 12px;
}
.width-sm {
	min-width: 150px;
}
.width-md {
	min-width: 200px;
}
.width-lg {
	min-width: 250px;
}
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Notas de remisión personales</b>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_cambiar || $permiso_crear)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar acciones clic en el siguiente botón(es): </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_cambiar) { ?>
			<button class="btn btn-default" data-cambiar="true">
				<span class="glyphicon glyphicon-calendar"></span>
				<span class="hidden-xs">Cambiar</span>
			</button>
			<?php } ?>
			<?php if ($permiso_crear && false) { ?>			
			<a href="?/notas/crear" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span><span class="hidden-xs"> Crear venta con nota de remisi&oacute;n</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($ventas) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Fecha</th>
				<th class="text-nowrap">Tipo</th>
				<th class="text-nowrap">Cliente</th>
				<th class="text-nowrap">NIT/CI</th>
				<th class="text-nowrap">Nro. Nota</th>
				<th class="text-nowrap">Importe total <?= escape($moneda); ?></th>
				<th class="text-nowrap">Registros</th>
                <th class="text-nowrap">Almacen</th>
                <th class="text-nowrap">Deuda</th>
				<th class="text-nowrap">Empleado</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nro. Nota</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Importe total <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Almacen</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Deuda</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
				<?php if ($permiso_ver || $permiso_eliminar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($ventas as $nro => $venta) { ?>
			<tr data-venta="<?= $venta['id_egreso']; ?>">
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($venta['hora_egreso']); ?></small></td>
				<td class="text-nowrap">Nota de remisión</td>
				<td class="text-nowrap"><?= escape($venta['nombre_cliente']); ?></td>
				<td class="text-nowrap"><?= escape($venta['nit_ci']); ?></td>
				<td class="text-nowrap text-right"><?= escape($venta['nro_factura']); ?></td>
				<td class="text-nowrap text-right" data-monto-total><?= escape($venta['monto_total_descuento']); ?></td>
				<td class="text-nowrap text-right"><?= escape($venta['nro_registros']); ?></td>
                <td class="text-nowrap"><?= escape($venta['almacen']); ?></td>
                <td class="text-nowrap"><?php if($venta['cobrar']=='si'){ ?><a href="?/notas/activar/<?= $venta['id_ingreso']; ?>"  class="label label-danger" >Deuda</a><?php }?></td>
				<td class="width-md"><?= escape($venta['nombres'] . ' ' . $venta['paterno'] . ' ' . $venta['materno']); ?></td>
				<?php if (($permiso_ver || $permiso_eliminar)) { ?>
				<td class="text-nowrap">
					
					<?php if ($permiso_ver) { ?>
						<a href="?/notas/ver/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de nota de remisión"><span class="glyphicon glyphicon-list-alt"></span></a>
						<?php } ?>
					<?php if ($validarVenta->verificaVenta($venta['id_egreso'])) { ?>					

							<?php if ($permiso_eliminar) { ?>
									<a href="?/notas/eliminar/<?= $venta['id_egreso']; ?>" class="text-danger" data-toggle="tooltip" data-title="Eliminar nota de remisión" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
							<?php } ?>
							<a onclick="confirmar_venta(<?= $venta['id_egreso']; ?>)" data-toggle="tooltip" data-title="Confirmar nota de remisión"><span class="glyphicon glyphicon-question-sign"></span></a>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen notas de remisión registradas en la base de datos.</p>
	</div>
	<?php } ?>
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


<!-- COMPONENTE DE MODAL DE CAMBIO DE EFECTIVO -->
<?= (validar_atributo($db, $_plansistema['plan'], 'productos', 'crear', 'categoria_cliente')) ? modal_efectivo_cambio(): '' ?>



<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/notas/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la nota de remisión y todo su detalle?', function (result) {
			if(result){
				window.location = url;
			}
		});
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
		onSuccess: function () {
			var inicial_fecha = $.trim($('#inicial_fecha').val());
			var final_fecha = $.trim($('#final_fecha').val());
			var vacio = gestion.replace(new RegExp('9', 'g'), '0');

			inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
			inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
			final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
			vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
			vacio = vacio.replace(new RegExp('/', 'g'), '-');
			final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
			inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : ''); 
			
			window.location = '?/notas/mostrar' + inicial_fecha + final_fecha;
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

	$inicial_fecha.on('dp.change', function (e) {
		$final_fecha.data('DateTimePicker').minDate(e.date);
	});
	
	$final_fecha.on('dp.change', function (e) {
		$inicial_fecha.data('DateTimePicker').maxDate(e.date);
	});

	var $form_fecha = $('#form_fecha');
	var $modal_fecha = $('#modal_fecha');

	$form_fecha.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_fecha.on('show.bs.modal', function () {
		$form_fecha.trigger('reset');
	});

	$modal_fecha.on('shown.bs.modal', function () {
		$modal_fecha.find('[data-aceptar]').focus();
	});

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

	$modal_fecha.find('[data-aceptar]').on('click', function () {
		$form_fecha.submit();
	});

	$('[data-cambiar]').on('click', function () {
		$('#modal_fecha').modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	
	<?php if ($ventas) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'ventas_notas_personales',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>




	var $modal_efectivo_cambio = $('#modal_efectivo_cambio');
	$modal_efectivo_cambio.on('hidden.bs.modal', function () {
		document.getElementById("modal_efect_cambio").reset();
		document.getElementById("detalleVenta").remove();
	});

	document.getElementById("modal_efect_cambio").addEventListener('submit', (e)=>{
		e.preventDefault();		
	});

	document.querySelector("#modal_efectivo_cambio [data-cancelar]").addEventListener('click',(e)=>{
		$modal_efectivo_cambio.modal('hide');
	});

	$.validate({
		form: '#modal_efect_cambio',
		modules: 'basic',
		onSuccess: function() {
			let formCambio = $('#modal_efect_cambio');
			$modal_efectivo_cambio.modal('hide');
			let idVentaObtenido = document.querySelector("[data-id-venta]").getAttribute("data-id-venta");
			
			guardar_venta(formCambio, idVentaObtenido);
		}
	});

});

function confirmar_venta(id_venta){
	var $modal_efectivo_cambio = $('#modal_efectivo_cambio');
	$modal_efectivo_cambio.modal('show');
	let import_total = document.querySelector(`#table tbody [data-venta="${id_venta}"]`);
	let nroItem = import_total.querySelector("th:nth-child(1)").textContent;
	let fechaItem = import_total.querySelector("td:nth-child(2)").textContent;
	let horaItem = fechaItem.split(" ");
	horaItem = horaItem[1];
	let tipoItem = import_total.querySelector("td:nth-child(3)").textContent;
	let clienteItem = import_total.querySelector("td:nth-child(4)").textContent;
	let nitItem = import_total.querySelector("td:nth-child(5)").textContent;
	let nroNotaItem = import_total.querySelector("td:nth-child(6)").textContent;
	let empleadoItem = import_total.querySelector("td:nth-child(11)").textContent;

	let datsVenta = `	
					<div id="detalleVenta" >
						<div class="form-group">
                            <div class="col-sm-12 col-md-5 text-right">
                                <span>FECHA:</span><br>
								<span>TIPO:</span><br>
								<span class="h3">NIT:</span><br>
								<span class="h3">CLIENTE:</span><br>
								<span>NRO MOVIMIENTO:</span><br>
								<span>EMPLEADO:</span><br>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="container">
									<span class="text-primary">${fechaItem}
									<small class="text-warning">${horaItem}</small></span><br>
									<span class="text-primary">${tipoItem}</span><br>
									<span class="text-danger h3">${nitItem}</span><br>
									<span class="text-danger h3">${clienteItem}</span><br>
									<span class="text-primary">${nroNotaItem}</span><br>
									<span class="text-primary">${empleadoItem}</span><br>
                                </div>
                            </div>
                        </div>
					</div>`;
	document.querySelector(`#modal_efectivo_cambio .modal-body`).insertAdjacentHTML("afterbegin", datsVenta);

	let importe_desc = import_total.querySelector(`[data-monto-total]`).textContent;
	importe_desc = (importe_desc > 0) ? importe_desc : 0;
	//console.log(total_dessssss);

	document.querySelector("[data-id-venta]").setAttribute('data-id-venta', id_venta);	
	document.getElementById("importeTotalModal").value = importe_desc;
	document.getElementById("modal_efect_cambio").reset;

	document.getElementById("pagoEfectivoModal").addEventListener("keyup", ()=>{
		let pagoEfectivo = document.getElementById("pagoEfectivoModal").value;

		importe_desc = (Number.isInteger(importe_desc)) ? `${importe_desc}.00`: ((importe_desc % 1)?importe_desc : `${importe_desc}.00`);
		
		let cambioCalculado = importe_desc - pagoEfectivo;	
		cambioCalculado = (Number.isInteger(cambioCalculado)) ? `${cambioCalculado}.00`: ((cambioCalculado % 1)?cambioCalculado : `${cambioCalculado}.00`);

		let numeroArray = cambioCalculado.toString().split('.');
		let decimal = numeroArray[1];
		let decimalArray = Array.from(decimal);
		decimal =  (decimalArray[1] > 0) ? decimalArray[0] +'0': '00';
		cambioCalculado = `${numeroArray[0]}.${decimal}`;
		
		cambioCalculado = ((cambioCalculado)*(-1)).toFixed(1);
		document.getElementById("cambioModal").value = `${(!isNaN(cambioCalculado) || parseFloat(cambioCalculado) || Number.isInteger(cambioCalculado)) ? cambioCalculado : 0}0`;
	});
}

function guardar_venta(formulario, idVent){
	let datos = formulario.serialize();
	datos += `&id_venta=${idVent}`;

	
	 $.ajax({
		type: 'post',
		dataType: 'json',
		url: '?/ventas-confirm/confirmar',
		data: datos
	}).done(function(resp){
		console.log(resp);

		switch (resp.status) {
			case 'success':
					$.notify({
							message: 'La nota de remisión se concreto satisfactoriamente.'
						}, {
							type: 'success'
						});
						imprimir_nota(resp.responce);
						$('#loader').fadeOut(100);
				break;
			case 'invalid':
				$('#loader').fadeOut(100);
						$.notify({								
							message: 'Ocurrio un evento ' + venta.responce
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

	}).fail(function (e) {
		console.log(e);
	}); 
}

function imprimir_nota(nota) {
		window.open('?/notas/imprimir/' + nota, true);
		window.location.reload();
	}

</script>
<?php require_once show_template('footer-configured'); ?>