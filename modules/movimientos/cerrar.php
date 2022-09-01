<?php

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene la fecha
$fecha = str_replace('/', '-', now($_institution['formato']));

// Obtiene los formatos
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Verifica si existe el parametro
if (sizeof($params) == 1) {
	// Verifica el tipo del parametro
	if (!is_date($params[0])) {
		// Redirecciona la pagina
		redirect('?/movimientos/cerrar/' . $fecha);
	}
} else {
	// Redirecciona la pagina
	redirect('?/movimientos/cerrar/' . $fecha);
}

// Obtiene el parametro
$fecha = date_encode($params[0]);

$ultimo_registro_caja = $db->query("SELECT * FROM `inv_caja` WHERE fecha = (SELECT MAX(fecha) AS fecha FROM inv_caja) AND hora_caja = (SELECT MAX(hora_caja) AS fecha FROM inv_caja) AND id_caja = (SELECT MAX(id_caja) AS fecha FROM inv_caja)")->fetch_first();
$estado = '';
if ($ultimo_registro_caja) {
	if ($ultimo_registro_caja['estado'] == 'CAJA')
		$estado = 'CIERRE';
	if ($ultimo_registro_caja['estado'] == 'CIERRE')
		$estado = 'INICIO';
} else {
	$estado = 'INICIO';
}
//let_dump($ultimo_registro_caja['estado']);
?>

<!-- cabecera -->
<?php require_once show_template('header-configured'); ?>
<!-- body -->
<div class="panel-heading">
	<h3 class="panel-title" data-header="true">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Cierre de Caja</strong>
	</h3>
</div>
<div class="panel-body" data-servidor="<?= ip_local . name_project . '/diario.php'; ?>">
	<div class="row">
		<div class="col-xs-6">
			<div class="text-label hidden-xs">Seleccionar acción:</div>
			<div class="text-label visible-xs-block">Acciones:</div>
		</div>
		<div class="col-xs-6 text-right">
			<a href="?/movimientos/imprimir/<?= $fecha; ?>" target="_blank" class="btn btn-default"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs hidden-sm"> Exportar</span></a>
			<!-- <a href="?/movimientos/caja/<?= $fecha; ?>" target="_blank" class="btn btn-default"><i class="glyphicon glyphicon-stats"></i><span class="hidden-xs hidden-sm"> Listado Caja</span></a> -->


			<div class="btn-group">
				<button tipo="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="glyphicon glyphicon-wrench"></span>
					<span class="hidden-xs">Acciones</span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<li class="dropdown-header visible-xs-block">Seleccionar acción</li>
					<li><a href="#" data-toggle="modal" data-target="#modal_cambiar" data-backdrop="static" data-keyboard="false"><span class="glyphicon glyphicon-calendar"></span> Cambiar fecha</a></li>
				</ul>
			</div>
		</div>
	</div>
	<hr>
	<?php if (isset($_SESSION[temporary])) { ?>
		<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
			<button tipo="button" class="close" data-dismiss="alert">&times;</button>
			<strong><?= $_SESSION[temporary]['title']; ?></strong>
			<p><?= $_SESSION[temporary]['message']; ?></p>
		</div>
		<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<div class="well">
		<p class="lead margin-none">
			<b>Empleado:</b>
			<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
		</p>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div class="col-sm-12">
				<p class="lead"><b><a href="?/movimientos/ingresos_listar" class="text-success">Ingresos</a></b></p>
				<div id="ingresos"></div>
				<!-- @etysoft aquí el contenido renderizado en JS - thead -->
				<!-- @etysoft aquí el contenido renderizado en JS - tbody -->
				<!-- @etysoft aquí el contenido renderizado en JS - tfoot -->
			</div>

			<div class="col-sm-12"><br>
				<p class="lead"><b><a href="?/reportes/diario" class="text-success">Ventas</a></b></p>
				<div id="ventas"></div>
				<!-- @etysoft aquí el contenido renderizado en JS - thead -->
				<!-- @etysoft aquí el contenido renderizado en JS - tbody -->
				<!-- @etysoft aquí el contenido renderizado en JS - tfoot -->
			</div>
		</div>

		<div class="col-sm-6">
			<div class="col-sm-12">
				<p class="lead"><b><a href="?/movimientos/egresos_listar" class="text-danger">Egresos</a></b></p>
				<div id="egresos"></div>
				<!-- @etysoft aquí el contenido renderizado en JS - thead -->
				<!-- @etysoft aquí el contenido renderizado en JS - tbody -->
				<!-- @etysoft aquí el contenido renderizado en JS - tfoot -->
			</div>

			<div class="col-sm-12"><br>
				<p class="lead"><b><a href="#" class="text-danger">Compras</a></b></p>
				<div id="compras"></div>
				<!-- @etysoft aquí el contenido renderizado en JS - thead -->
				<!-- @etysoft aquí el contenido renderizado en JS - tbody -->
				<!-- @etysoft aquí el contenido renderizado en JS - tfoot -->
			</div>
			<div class="col-sm-12"><br>
				<p class="lead"><b><a href="?/movimientos/gastos_listar" class="text-danger">Gastos</a></b></p>
				<div id="gastos"></div>
				<!-- @etysoft aquí el contenido renderizado en JS - thead -->
				<!-- @etysoft aquí el contenido renderizado en JS - tbody -->
				<!-- @etysoft aquí el contenido renderizado en JS - tfoot -->
			</div>
		</div>
	</div>
	<br>
	<div class="well">
		<p class="lead margin-none">
			<b>Total Efectivo:</b>
			<u id="total"></u>
			<span><?= escape($moneda); ?></span>
		</p>
		<p class="margin-none">
			<em>El total corresponde a la siguiente fórmula:</em>
			<samp><b>( Ingresos + Ventas ) - ( Egresos + Compras + Gastos ) </b></samp>
		</p>
	</div>
</div>

<!-- Modal cambiar inicio -->
<div id="modal_cambiar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<form method="post" action="?/movimientos/cerrar" id="form_cambiar" class="modal-content loader-wrapper" autocomplete="off">
			<div class="modal-header">
				<button tipo="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Cambiar fecha</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="fecha_cambiar" class="control-label">Fecha:</label>
					<input tipo="text" value="<?= date_decode($fecha, $_institution['formato']); ?>" name="fecha" id="fecha_cambiar" class="form-control" data-validation="required date" data-validation-format="<?= $formato_textual; ?>">
				</div>
			</div>
			<div class="modal-footer">
				<button tipo="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-share-alt"></span>
					<span>Cambiar</span>
				</button>
				<button tipo="reset" class="btn btn-default">
					<span class="glyphicon glyphicon-refresh"></span>
					<span>Restablecer</span>
				</button>
			</div>
			<div id="loader_cambiar" class="loader-wrapper-backdrop hidden">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>



<div id="abrir_caja" class="abrir_caja modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/movimientos/abrir_caja" id="form_abrir_caja" class="modal-content loader-wrapper" autocomplete="off">
			<div class="modal-header">
				<h4 class="modal-title">Abrir Caja</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="unidad_id_asignar" class="control-label">Fecha:</label>
					<input tipo="text" value="<?= date_decode($fecha, $_institution['formato']); ?>" name="fecha_abrir_caja" id="fecha_abrir_caja" class="form-control" data-validation="required date" data-validation-format="<?= $formato_textual; ?>">
				</div>
			</div>
			<div class="modal-footer">
				<button tipo="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button tipo="reset" class="btn btn-default" data-cancelar-asignar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_abrir" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>


<!-- INICIAR CAJA -->
<div id="iniciar_caja" class="iniciar_caja modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/movimientos/abrir_caja" id="form_iniciar_caja" class="modal-content loader-wrapper" autocomplete="off">
			<div class="modal-header">
				<h4 class="modal-title">Iniciar Caja</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="unidad_id_asignar" class="control-label">Fecha:</label>
					<input tipo="text" value="<?= date_decode($fecha, $_institution['formato']); ?>" name="fecha_inicio_caja" id="fecha_inicio_caja" class="form-control" data-validation="required date" data-validation-format="<?= $formato_textual; ?>">
				</div>
			</div>
			<div class="modal-footer">
				<button tipo="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button tipo="reset" class="btn btn-default" data-cancelar-asignar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_iniciar" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<!-- CERRAR CAJA -->
<div id="cerrar_caja" class="cerrar_caja modal fade">
	<div class="modal-dialog">
		<form method="post" action="?/movimientos/cerrar_caja" id="form_cerrar_caja" class="modal-content loader-wrapper" autocomplete="off">
			<div class="modal-header">
				<h4 class="modal-title">Cerrar Caja</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="unidad_id_asignar" class="control-label">Fecha:</label>
					<input tipo="text" value="<?= date_decode($fecha, $_institution['formato']); ?>" name="fecha_cierre_caja" id="fecha_cierre_caja" class="form-control" data-validation="required date" data-validation-format="<?= $formato_textual; ?>">
				</div>
			</div>
			<div class="modal-footer">
				<button tipo="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button tipo="reset" class="btn btn-default" data-cancelar-asignar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_cerrar" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script>
	$(function() {
		let $modal_cambiar = $('#modal_cambiar'),
			$form_cambiar = $('#form_cambiar'),
			$loader_cambiar = $('#loader_cambiar'),
			$fecha_cambiar = $('#fecha_cambiar'),
			$abrir_caja = $('#abrir_caja'),
			$form_abrir_caja = $('#form_abrir_caja'),
			$fecha_abrir_caja = $('#fecha_abrir_caja'),
			$iniciar_caja = $('#iniciar_caja'),
			$form_iniciar_caja = $('#form_iniciar_caja'),
			$fecha_inicio_caja = $('#fecha_inicio_caja');
		let $loader_iniciar = $('loader_iniciar'),
			$cerrar_caja = $('#cerrar_caja'),
			$form_cerrar_caja = $('#form_cerrar_caja'),
			$fecha_cierre_caja = $('#fecha_cierre_caja'),
			$loader_abrir = $('#loader_abrir');

		$.validate({
			form: '#form_cambiar',
			modules: 'date',
			onSuccess: function() {
				$loader_cambiar.removeClass('hidden');
				let direccion_cambiar = $.trim($form_cambiar.attr('action')),
					fecha_cambiar = $.trim($fecha_cambiar.val());
				fecha_cambiar = fecha_cambiar.replace(new RegExp('/', 'g'), '-');
				window.location = direccion_cambiar + '/' + fecha_cambiar;
			}
		});

		<?php if (!$ultimo_registro_caja) { ?>
			$.validate({
				form: '#form_iniciar_caja',
				modules: 'date',
				onSuccess: function() {
					$loader_iniciar.removeClass('hidden');
					let estado = "<?= $estado; ?>";
					let direccion_cambiar = $.trim($form_iniciar_caja.attr('action')),
						fecha_cambiar = $.trim($fecha_inicio_caja.val());
					fecha_cambiar = fecha_cambiar.replace(new RegExp('/', 'g'), '-');
					$.ajax({
						tipo: 'post',
						dataType: 'json',
						url: direccion_cambiar,
						data: {
							estado: estado,
							fecha: fecha_cambiar,
						}
					}).done(function(producto) {
						window.location.reload();
						$iniciar_caja.modal('hide');
					}).fail(function() {
						$.notify({
							message: 'Ocurrió un problema al realizar el incio de caja.'
						}, {
							tipo: 'danger'
						});
					}).always(function() {
						$loader_iniciar.fadeOut(100, function() {
							$iniciar_caja.modal('hide');
						});
					});
				}
			});
		<?php } ?>

		<?php if ($estado == 'INICIO') { ?>
			$.validate({
				form: '#form_abrir_caja',
				modules: 'date',
				onSuccess: function() {
					$loader_iniciar.removeClass('hidden');
					let estado = "<?= $estado;  ?>";
					let direccion_cambiar = $.trim($form_abrir_caja.attr('action')),
						fecha_cambiar = $.trim($fecha_abrir_caja.val());
					fecha_cambiar = fecha_cambiar.replace(new RegExp('/', 'g'), '-');
					$.ajax({
						tipo: 'post',
						dataType: 'json',
						url: direccion_cambiar,
						data: {
							estado: estado,
							fecha: fecha_cambiar,
						}
					}).done(function(producto) {
						window.location.reload();
						$abrir_caja.modal('hide');
					}).fail(function() {
						$.notify({
							message: 'Ocurrió un problema al realizar el incio de caja.'
						}, {
							tipo: 'danger'
						});
					}).always(function() {
						$loader_abrir.fadeOut(100, function() {
							$abrir_caja.modal('hide');
						});
					});
				}
			});
		<?php } ?>



		<?php if ($estado == 'CIERRE') { ?>
			$.validate({
				form: '#form_cerrar_caja',
				modules: 'date',
				onSuccess: function() {
					$loader_iniciar.removeClass('hidden');
					let estado = "<?= $estado;  ?>";
					let direccion_cambiar = $.trim($form_cerrar_caja.attr('action')),
						fecha_cambiar = $.trim($fecha_cierre_caja.val());
					fecha_cambiar = fecha_cambiar.replace(new RegExp('/', 'g'), '-');

					$.ajax({
						tipo: 'post',
						dataType: 'json',
						url: direccion_cambiar,
						data: {
							estado: estado,
							fecha: fecha_cambiar,
						}
					}).done(function(producto) {
						$iniciar_caja.modal('hide');
						window.location.reload();
						$.notify({
							message: 'Se inicio correctamente la caja.'
						}, {
							tipo: 'success'
						});
					}).fail(function() {
						$.notify({
							message: 'Ocurrió un problema al realizar el incio de caja.'
						}, {
							tipo: 'danger'
						});
					}).always(function() {
						$loader_iniciar.fadeOut(100, function() {
							$iniciar_caja.modal('hide');
						});
					});
				}
			});
		<?php } ?>

		$fecha_inicio_caja.datetimepicker({
			format: '<?= strtoupper($formato_textual); ?>'
		});

		$fecha_cambiar.datetimepicker({
			format: '<?= strtoupper($formato_textual); ?>'
		});

		let date = new Date();
		let today = new Date(date.getFullYear(), date.getMonth(), date.getDate());

		$fecha_abrir_caja.datetimepicker({
			format: '<?= strtoupper($formato_textual); ?>',
			minDate: today
		});

		$fecha_cierre_caja.datetimepicker({
			format: '<?= strtoupper($formato_textual); ?>',
			minDate: today
		});

		$form_cambiar.on('submit', function(e) {
			e.preventDefault();
		});

		$modal_cambiar.on('hidden.bs.modal', function() {
			$form_cambiar.trigger('reset');
		}).on('show.bs.modal', function(e) {
			if ($('.modal:visible').size() != 0) {
				e.preventDefault();
			}
		});

		$abrir_caja.on('hidden.bs.modal', function() {
			$form_abrir_caja.trigger('reset');
		}).on('show.bs.modal', function(e) {
			if ($('.modal:visible').size() != 0) {
				e.preventDefault();
			}
		});

		$abrir_caja.find('[data-cancelar-asignar]').on('click', function() {
			$abrir_caja.modal('hide');
		});

		$form_abrir_caja.on('submit', function(e) {
			e.preventDefault();
		});

		$iniciar_caja.find('[data-cancelar-asignar]').on('click', function() {
			$iniciar_caja.modal('hide');
		});

		$form_iniciar_caja.on('submit', function(e) {
			e.preventDefault();
		});

		$cerrar_caja.find('[data-cancelar-asignar]').on('click', function() {
			$cerrar_caja.modal('hide');
		});

		$form_cerrar_caja.on('submit', function(e) {
			e.preventDefault();
		});


		$('#table').DataFilter({
			name: 'asistencias',
			reports: 'excel|word|pdf|html',
			values: {
				stateSave: true
			}
		});
	});

	function imprimir_diario() {
		let fecha = '<?= $fecha; ?>';
		$.open('?/movimientos/imprimir/' + fecha, true);
		window.location.reload();
	}
</script>

<script>
	let fecha = "<?= $fecha; ?>";

	let moneda = "<?= $moneda; ?>"

	// @etysoft funcion para cargar la tabla de ingresos, recibe como parametro fecha y condicion personal
	async function loadDataTableIngresos(fecha, personal = true) {

		let contenedor = document.getElementById("ingresos");

		let formData = new FormData();
		formData.append("fecha", fecha);
		formData.append("personal", personal);

		const url_ingresos = `?/movimientos/api_obtener_ingresos`;
		const url_cobros = `?/movimientos/api_obtener_cobros`;

		const data_ingresos = await axios({
			method: "post",
			url: url_ingresos,
			data: formData
		}).then(
			response => response.arr_ingresos
		).catch(
			err => console.error
		);

		const data_cobros = await axios({
			method: "post",
			url: url_cobros,
			data: formData
		}).then(
			response => response.arr_cobros
		).catch(
			err => console.error
		);

		let total_movimiento_efectivo = 0;
		let total_movimiento_diferido = 0;
		let total_movimiento_real = 0;

		// console.log(data_ingresos , data_cobros)
		if (data_ingresos?.length > 0 || data_cobros.length > 0){
		
			template = `<div class="table-responsive margin-none">
							<table id="table_ingresos" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							</table>
						</div>`;
			contenedor.innerHTML = template;

			// función para armar el header de la tabla
			crearHeader("#table_ingresos");

			//inicializamos el datatable
			let dataTableIngresos = $('#table_ingresos').DataTable({
				name: 'ingresos',
				searching: false,
				paging: false,
				ordering: false,
				scrollY: true,
				scrollX: false,
				dom: `<'row'<'col-sm-12'tr>>
					<'row row-center'<'col-sm-12'p>>`,
			});
			// console.log(data_ingresos)
			if (data_ingresos) {
				data_ingresos.forEach((el, index) => {

					let monto_ingreso = _toFixed(el.monto);

					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(el.monto) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(el.monto) : total_movimiento_diferido;

					let template_row_ingresos = `<tr>
													<td class="text-nowrap text-middle text-right">${el.nro_comprobante}</td>
													<td class="text-nowrap text-middle">${el.fecha_movimiento}</td>
													<td class="text-nowrap text-middle">${el.concepto}</td>
													<td class="text-nowrap text-middle text-right">${monto_ingreso}</td>
													<td class="text-right">${monto_ingreso}</td>
													<td class="text-right">${el.tipo_pago}</td>
												</tr>`;
					const row_ingresos = $(template_row_ingresos);
					dataTableIngresos.row.add(row_ingresos[0]).draw();
				});
			}

			// console.log(data_cobros)
			if (data_cobros) {
				data_cobros.forEach((el, index) => {

					let monto_cobro_importe = _toFixed(el.subtotal);
					let monto_cobro_total = (el.descuento != 0 && el.sigla == '%') ? _toFixed(el.monto_total * (1 - el.descuento / 100)) : ((el.descuento != 0 && el.sigla == '$') ? _toFixed(el.monto_total - el.descuento) : _toFixed(el.monto_total));

					let monto_importe = el.subtotal;
					let monto_total = (el.descuento != 0 && el.sigla == '%') ? el.monto_total * (1 - el.descuento / 100) : ((el.descuento != 0 && el.sigla == '$') ? el.monto_total - el.descuento : el.monto_total);


					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_importe) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_importe) : total_movimiento_diferido;

					let template_row_cobros = `<tr>
													<td class="text-nowrap text-middle text-right">${el.nro_factura}</td>
													<td class="text-nowrap text-middle">${el.fecha_pago}</td>
													<td class="text-nowrap text-middle">
														<font size="1">${(el.nit_ci) ? 'NIT: '+ el.nit_ci + ' - ' + el.tipo + ': ' + el.fecha_egreso : '' }</font>
													</td>
													<td class="text-nowrap text-middle text-right">${monto_cobro_total}</td>
													<td class="text-right">${monto_cobro_importe}</td>
													<td class="text-right">${el.tipo_pago}</td>
												</tr>`;
					const row_cobros = $(template_row_cobros);
					dataTableIngresos.row.add(row_cobros[0]).draw();
				});
			}

			// console.log(total_movimiento_real, total_movimiento_efectivo,total_movimiento_diferido)
			total_movimiento_real = total_movimiento_efectivo + total_movimiento_diferido;

			let arr_totales = [total_movimiento_efectivo, total_movimiento_diferido, total_movimiento_real]; // array de totales
			// función para armar el footer de la tabla
			crearFooterTotales("#table_ingresos", "success", arr_totales);
		}else{
			template = `<div class="well">No hay ingresos</div>`
			contenedor.innerHTML = template;
		}

		return new Promise(resolve => {
			setTimeout(() => {
			resolve(total_movimiento_real);
			}, 50);
		});
	}

	// @etysoft funcion para cargar la tabla de ventas, recibe como parametro fecha y condicion personal
	async function loadDataTableVentas(fecha, personal = true) {

		let contenedor = document.getElementById("ventas");

		let formData = new FormData();
		formData.append("fecha", fecha);
		formData.append("personal", personal);

		const url_ventas = `?/movimientos/api_obtener_ventas`;

		const data_ventas = await axios({
			method: "post",
			url: url_ventas,
			data: formData
		}).then(
			response => response.arr_ventas
		).catch(
			err => console.error
		);

		let total_movimiento_efectivo = 0;
		let total_movimiento_diferido = 0;
		let total_movimiento_real = 0;

		// console.log(data_ventas)
		if (data_ventas?.length > 0 ){

			template = `<div class="table-responsive margin-none">
							<table id="table_ventas" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							</table>
						</div>`;
			contenedor.innerHTML = template;

			// función para armar el header de la tabla
			crearHeader("#table_ventas");

			//inicializamos el datatable
			let dataTableVentas = $('#table_ventas').DataTable({
				name: 'ventas',
				searching: false,
				paging: false,
				ordering: false,
				scrollY: true,
				scrollX: false,
				dom: `<'row'<'col-sm-12'tr>>
					<'row row-center'<'col-sm-12'p>>`,
			});

			// console.log(data_ventas)
			if (data_ventas) {
				data_ventas.forEach((el, index) => {

					let monto_venta_cuota = _toFixed(el.subtotal);
					let monto_venta_total = (el.descuento != 0 && el.sigla == '%') ? _toFixed(el.monto_total * (1 - el.descuento / 100)) : ((el.descuento != 0 && el.sigla == '$') ? _toFixed(el.monto_total - el.descuento) : _toFixed(el.monto_total));

					let monto_cuota = el.subtotal;
					let monto_total = (el.descuento != 0 && el.sigla == '%') ? el.monto_total * (1 - el.descuento / 100) : ((el.descuento != 0 && el.sigla == '$') ? el.monto_total - el.descuento : el.monto_total);

					let template_row_ventas;

					if (el.plan_de_pagos == "no") {

						total_movimiento_efectivo = (el.tipo_de_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_total) : total_movimiento_efectivo;
						total_movimiento_diferido = (el.tipo_de_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_total) : total_movimiento_diferido;

						template_row_ventas = `<tr>
													<td class="text-nowrap text-middle text-right">${el.nro_factura}</td>
													<td class="text-nowrap text-middle">${el.fecha_egreso}</td>
													<td class="text-nowrap text-middle">
														<font size="1">${(el.nombre_cliente) ? el.nombre_cliente + `<br>` + 'NIT: '+ el.nit_ci + ' - ' + el.tipo : ''}</font>
													</td>
													<td class="text-nowrap text-middle text-right">${monto_venta_total}</td>
													<td class="text-right">${monto_venta_total}</td>
													<td class="text-right">${el.tipo_de_pago}</td>
												</tr>`;
					} else {
						total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_cuota) : total_movimiento_efectivo;
						total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_cuota) : total_movimiento_diferido;

						template_row_ventas = `<tr>
													<td class="text-nowrap text-middle text-right">${el.nro_factura}</td>
													<td class="text-nowrap text-middle">${el.fecha_pago}</td>
													<td class="text-nowrap text-middle">
														<font size="1">${(el.nombre_cliente) ? 'NIT: '+ el.nit_ci + ' - ' + el.tipo : ''}</font>
														<font size="1">${(el.plan_de_pagos) ? 'Plan de pagos':'' }</font>
													</td>
													<td class="text-nowrap text-middle text-right">${monto_venta_total}</td>
													<td class="text-right">${monto_venta_cuota}</td>
													<td class="text-right">${el.tipo_pago}</td>
												</tr>`;

					}

					const row_ventas = $(template_row_ventas);
					dataTableVentas.row.add(row_ventas[0]).draw();
				});
			}
			// console.log(total_movimiento_real, total_movimiento_efectivo,total_movimiento_diferido)
			total_movimiento_real = total_movimiento_efectivo + total_movimiento_diferido;

			let arr_totales = [total_movimiento_efectivo, total_movimiento_diferido, total_movimiento_real]
			// función para armar el footer de la tabla
			crearFooterTotales("#table_ventas", "success", arr_totales);
		}else{
			template = `<div class="well">No hay ventas</div>`
			contenedor.innerHTML = template;
		}

		return new Promise(resolve => {
			setTimeout(() => {
			resolve(total_movimiento_real);
			}, 50);
		});
	}

	// @etysoft funcion para cargar la tabla de ventas, recibe como parametro fecha y condicion personal
	async function loadDataTableEgresos(fecha, personal = true) {

		let contenedor = document.getElementById("egresos");

		let formData = new FormData();
		formData.append("fecha", fecha);
		formData.append("personal", personal);

		const url_egresos = `?/movimientos/api_obtener_egresos`;
		const url_pagos = `?/movimientos/api_obtener_pagos`;
		const url_cronogramas = `?/movimientos/api_obtener_cronogramas`;

		const data_egresos = await axios({
			method: "post",
			url: url_egresos,
			data: formData
		}).then(
			response => response.arr_egresos
		).catch(
			err => console.error
		);

		const data_pagos = await axios({
			method: "post",
			url: url_pagos,
			data: formData
		}).then(
			response => response.arr_pagos
		).catch(
			err => console.error
		);

		const data_cronogramas = await axios({
			method: "post",
			url: url_cronogramas,
			data: formData
		}).then(
			response => response.arr_cronogramas
		).catch(
			err => console.error
		);

		let total_movimiento_efectivo = 0;
		let total_movimiento_diferido = 0;
		let total_movimiento_real = 0;

		// console.log(data_ventas, data_pagos, data_cronogramas)
		if (data_egresos?.length > 0 || data_pagos?.length > 0  || data_cronogramas > 0){

			template = `<div class="table-responsive margin-none">
							<table id="table_egresos" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							</table>
						</div>`;
			contenedor.innerHTML = template;

			// función para armar el header de la tabla
			crearHeader("#table_egresos");

			//inicializamos el datatable
			let dataTableEgresos = $('#table_egresos').DataTable({
				name: 'egresos',
				searching: false,
				paging: false,
				ordering: false,
				scrollY: true,
				scrollX: false,
				dom: `<'row'<'col-sm-12'tr>>
					<'row row-center'<'col-sm-12'p>>`,
			});

			// console.log(data_egresos)
			if (data_egresos) {
				data_egresos.forEach((el, index) => {

					let monto_egreso = _toFixed(el.monto);

					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(el.monto) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(el.monto) : total_movimiento_diferido;

					let template_row_egresos = `<tr>
													<td class="text-nowrap text-middle text-right">${el.nro_comprobante}</td>
													<td class="text-nowrap text-middle">${el.fecha_movimiento}</td>
													<td class="text-nowrap text-middle">${el.concepto}</td>
													<td class="text-nowrap text-middle text-right">${monto_egreso}</td>
													<td class="text-right">${monto_egreso}</td>
													<td class="text-right">${el.tipo_pago}</td>
												</tr>`;
					const row_egreso = $(template_row_egresos);
					dataTableEgresos.row.add(row_egreso[0]).draw();
				});
			}

			// console.log(data_pagos)
			if (data_pagos) {
				data_pagos.forEach((el, index) => {

					let monto_pago_importe = _toFixed(el.subtotal);
					let monto_pago_total = (el.descuento != 0 && el.sigla == '%') ? _toFixed(el.monto_total * (1 - el.descuento / 100)) : ((el.descuento != 0 && el.sigla == '$') ? _toFixed(el.monto_total - el.descuento) : _toFixed(el.monto_total));

					let monto_importe = el.subtotal;
					let monto_total = (el.descuento != 0 && el.sigla == '%') ? el.monto_total * (1 - el.descuento / 100) : ((el.descuento != 0 && el.sigla == '$') ? el.monto_total - el.descuento : el.monto_total);


					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_importe) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_importe) : total_movimiento_diferido;

					let template_row_pagos = `<tr>
													<td class="text-nowrap text-middle text-right">${el.id_ingreso}</td>
													<td class="text-nowrap text-middle">${el.fecha_pago}</td>
													<td class="text-nowrap text-middle">
														<font size="1">${(el.nombre_proveedor) ? el.nombre_proveedor + ' - ' + el.tipo + ': ' + el.fecha_ingreso : '' }</font>
													</td>
													<td class="text-nowrap text-middle text-right">${monto_pago_total}</td>
													<td class="text-right">${monto_pago_importe}</td>
													<td class="text-right">${el.tipo_pago}</td>
												</tr>`;
					const row_pagos = $(template_row_pagos);
					dataTableEgresos.row.add(row_pagos[0]).draw();
				});
			}

			// console.log(data_cronogramas)
			if (data_cronogramas) {
				data_cronogramas.forEach((el, index) => {
					console.log(data_cronogramas)
					let monto_pago_importe = _toFixed(el.monto);
					let monto_pago_total = (el.descuento != 0 && el.sigla == '%') ? _toFixed(el.monto_total * (1 - el.descuento / 100)) : ((el.descuento != 0 && el.sigla == '$') ? _toFixed(el.monto_total - el.descuento) : _toFixed(el.monto_total));

					let monto_importe = el.monto;
					let monto_total = (el.descuento != 0 && el.sigla == '%') ? el.monto_total * (1 - el.descuento / 100) : ((el.descuento != 0 && el.sigla == '$') ? el.monto_total - el.descuento : el.monto_total);


					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_importe) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_importe) : total_movimiento_diferido;

					let template_row_cronogramas = `<tr>
														<td class="text-nowrap text-middle text-right">${el.id_cronograma_cuentas}</td>
														<td class="text-nowrap text-middle">${el.fecha_pago}</td>
														<td class="text-nowrap text-middle">
															<font size="1">${'Cronograma: '+ el.detalle + ' - ' + el.periodo }</font>
														</td>
														<td class="text-nowrap text-middle text-right">${monto_pago_importe}</td>
														<td class="text-right">${monto_pago_importe}</td>
														<td class="text-right">${el.tipo_pago}</td>
													</tr>`;
					const row_cronogramas = $(template_row_cronogramas);
					dataTableEgresos.row.add(row_cronogramas[0]).draw();
				});
			}

			// console.log(total_movimiento_real, total_movimiento_efectivo,total_movimiento_diferido)
			total_movimiento_real = total_movimiento_efectivo + total_movimiento_diferido;
			
			let arr_totales = [total_movimiento_efectivo, total_movimiento_diferido, total_movimiento_real]
			// función para armar el footer de la tabla
			crearFooterTotales("#table_egresos", "danger", arr_totales);
		
		}else{
			template = `<div class="well">No hay egresos</div>`
			contenedor.innerHTML = template;
		}

		return new Promise(resolve => {
			setTimeout(() => {
			resolve(total_movimiento_real);
			}, 50);
		});
	}

	// @etysoft funcion para cargar la tabla de compras, recibe como parametro fecha y condicion personal
	async function loadDataTableCompras(fecha, personal = true) {

		let contenedor = document.getElementById("compras");

		let formData = new FormData();
		formData.append("fecha", fecha);
		formData.append("personal", personal);

		const url_compras = `?/movimientos/api_obtener_compras`;

		const data_compras = await axios({
			method: "post",
			url: url_compras,
			data: formData
		}).then(
			response => response.arr_compras
		).catch(
			err => console.error
		);

		let total_movimiento_efectivo = 0;
		let total_movimiento_diferido = 0;
		let total_movimiento_real = 0;

		// console.log(data_compras)
		if (data_compras?.length > 0){

			template = `<div class="table-responsive margin-none">
							<table id="table_compras" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							</table>
						</div>`;
			contenedor.innerHTML = template;

			// función para armar el header de la tabla
			crearHeader("#table_compras");

			//inicializamos el datatable
			let dataTableCompras = $('#table_compras').DataTable({
				name: 'compras',
				searching: false,
				paging: false,
				ordering: false,
				scrollY: true,
				scrollX: false,
				dom: `<'row'<'col-sm-12'tr>>
					<'row row-center'<'col-sm-12'p>>`,
			});

			// console.log(data_compras)
			data_compras.forEach((el, index) => {

				let monto_compra_cuota = _toFixed(el.subtotal);
				let monto_compra_total = (el.descuento != 0 && el.sigla == '%') ? _toFixed(el.monto_total * (1 - el.descuento / 100)) : ((el.descuento != 0 && el.sigla == '$') ? _toFixed(el.monto_total - el.descuento) : _toFixed(el.monto_total));

				let monto_cuota = el.subtotal;
				let monto_total = (el.descuento != 0 && el.sigla == '%') ? el.monto_total * (1 - el.descuento / 100) : ((el.descuento != 0 && el.sigla == '$') ? el.monto_total - el.descuento : el.monto_total);

				let template_row_compras;

				if (el.plan_de_pagos == "no") {
					total_movimiento_efectivo = (el.tipo_de_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_total) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_de_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_total) : total_movimiento_diferido;

					template_row_compras = `<tr>
												<td class="text-nowrap text-middle text-right">${el.id_ingreso}</td>
												<td class="text-nowrap text-middle">${el.fecha_ingreso}</td>
												<td class="text-nowrap text-middle">
													<font size="1">${(el.nombre_proveedor) ? el.nombre_proveedor + ' - ' + el.tipo : ''}</font>
												</td>
												<td class="text-nowrap text-middle text-right">${monto_compra_total}</td>
												<td class="text-right">${monto_compra_total}</td>
												<td class="text-right">${el.tipo_de_pago}</td>
											</tr>`;
				} else {
					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(monto_cuota) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(monto_cuota) : total_movimiento_diferido;

					template_row_compras = `<tr>
												<td class="text-nowrap text-middle text-right">${el.id_ingreso}</td>
												<td class="text-nowrap text-middle">${el.fecha_pago}</td>
												<td class="text-nowrap text-middle">
													<font size="1">${(el.nombre_proveedor) ? el.nombre_proveedor + ' - ' + el.tipo : ''}</font>
													<font size="1">${(el.plan_de_pagos) ? 'Plan de pagos':'' }</font>
												</td>
												<td class="text-nowrap text-middle text-right">${monto_compra_total}</td>
												<td class="text-right">${monto_compra_cuota}</td>
												<td class="text-right">${el.tipo_pago}</td>
											</tr>`;
				}

				const row_compras = $(template_row_compras);
				dataTableCompras.row.add(row_compras[0]).draw();
			});

			// console.log(total_movimiento_real, total_movimiento_efectivo,total_movimiento_diferido)
			total_movimiento_real = total_movimiento_efectivo + total_movimiento_diferido;

			let arr_totales = [total_movimiento_efectivo, total_movimiento_diferido, total_movimiento_real]
			// función para armar el footer de la tabla
			crearFooterTotales("#table_compras", "danger", arr_totales);
		
		}else{
			template = `<div class="well">No hay compras</div>`
			contenedor.innerHTML = template;
		}
		
		return new Promise(resolve => {
			setTimeout(() => {
			resolve(total_movimiento_real);
			}, 50);
		});
	}

	// @etysoft funcion para cargar la tabla de gastos, recibe como parametro fecha y condicion personal
	async function loadDataTableGastos(fecha, personal = true) {

		let contenedor = document.getElementById("gastos");

		let formData = new FormData();
		formData.append("fecha", fecha);
		formData.append("personal", personal);

		const url_gastos = `?/movimientos/api_obtener_gastos`;

		const data_gastos = await axios({
			method: "post",
			url: url_gastos,
			data: formData
		}).then(
			response => response.arr_gastos
		).catch(
			err => console.error
		);

		let total_movimiento_efectivo = 0;
		let total_movimiento_diferido = 0;
		let total_movimiento_real = 0;

		// console.log(data_gastos)
		if (data_gastos?.length > 0){

			template = `<div class="table-responsive margin-none">
							<table id="table_gastos" class="table table-bordered table-condensed table-striped table-hover table-xs margin-none">
							</table>
						</div>`;
			contenedor.innerHTML = template;

			crearHeader("#table_gastos");
			//inicializamos el datatable
			let dataTableGastos = $('#table_gastos').DataTable({
				name: 'gastos',
				searching: false,
				paging: false,
				ordering: false,
				scrollY: true,
				scrollX: false,
				dom: `<'row'<'col-sm-12'tr>>
					<'row row-center'<'col-sm-12'p>>`,
			});

			// console.log(data_gastos)
			if (data_gastos) {
				data_gastos.forEach((el, index) => {

					let monto_gasto = _toFixed(el.monto);

					total_movimiento_efectivo = (el.tipo_pago == 'Efectivo') ? total_movimiento_efectivo + parseFloat(el.monto) : total_movimiento_efectivo;
					total_movimiento_diferido = (el.tipo_pago != 'Efectivo') ? total_movimiento_diferido + parseFloat(el.monto) : total_movimiento_diferido;

					let template_row_gastos = `<tr>
													<td class="text-nowrap text-middle text-right">${el.nro_comprobante}</td>
													<td class="text-nowrap text-middle">${el.fecha_movimiento}</td>
													<td class="text-nowrap text-middle">${el.concepto}</td>
													<td class="text-nowrap text-middle text-right">${monto_gasto}</td>
													<td class="text-right">${monto_gasto}</td>
													<td class="text-right">${el.tipo_pago}</td>
												</tr>`;
					const row_gastos = $(template_row_gastos);
					dataTableGastos.row.add(row_gastos[0]).draw();
				});
			}
			// console.log(total_movimiento_real, total_movimiento_efectivo,total_movimiento_diferido)
			total_movimiento_real = total_movimiento_efectivo + total_movimiento_diferido;

			let arr_totales = [total_movimiento_efectivo, total_movimiento_diferido, total_movimiento_real]
			// función para armar el footer de la tabla
			crearFooterTotales("#table_gastos", "danger", arr_totales);

		}else{
			template = `<div class="well">No hay gastos</div>`
			contenedor.innerHTML = template;
		}

		return new Promise(resolve => {
			setTimeout(() => {
			resolve(total_movimiento_real);
			}, 50);
		});
	}

	// @etysoft totales para todos los componentes
	function crearHeader(selector_tabla) {

		// crear el header
		let contenedor = document.querySelector(selector_tabla);

		const header = `<thead>
							<tr class="info">
								<th class="text-middle" rowspan="2">Nº DOC.</th>
								<th class="text-nowrap text-middle" rowspan="2">FECHA</th>
								<th class="text-nowrap text-middle" rowspan="2">DETALLE</th>
								<th class="text-middle text-right" rowspan="2">TOTAL CONCEPTO</th>
								<th class="text-nowrap text-middle text-center" colspan="2">TOTAL PAGADO</th>
							</tr>
							<tr class="info">
								<th class="text-nowrap text-middle text-right">MONTO</th>
								<th class="text-nowrap text-middle text-right">TIPO PAGADO</th>
							</tr>
						</thead>`;
		contenedor.innerHTML = header;
	}

	// @etysoft totales para todos los componentes
	function crearFooterTotales(selector_tabla, tipo, arr_totales) {

		// crear el footer
		let footer = document.createElement("tfoot");

		footer.innerHTML = `<tr class="${tipo}">
								<th class="text-nowrap text-right" colspan="4">Importe total efectivo ${moneda}</th>
								<th class="text-nowrap text-right" data-subtotal="">${_toFixed(arr_totales[0])}</th>
								<th class="text-nowrap text-left" data-subtotal="" colspan="2">Efectivo</th>
							</tr>
							<tr class="${tipo}">
								<th class="text-nowrap text-right" colspan="4">Importe total entidad financiera ${moneda}</th>
								<th class="text-nowrap text-right" data-subtotal="">${_toFixed(arr_totales[1])}</th>
								<th class="text-nowrap text-left" data-subtotal="" colspan="2">Entidad Bancaria</th>
							</tr>
							<tr class="${tipo}">
								<th class="text-nowrap text-right" colspan="4">Total de totales ${moneda}</th>
								<th class="text-nowrap text-right" data-subtotal="">${_toFixed(arr_totales[2])}</th>
								<th class="text-nowrap text-left" data-subtotal="" colspan="2">Monto Total</th>
							</tr>`;

		// añadir el footer a la tabla
		document.querySelector(selector_tabla).appendChild(footer);
	}

	// @etysoft inicializa el datatable
	async function loadSumaAllDataTables() {
		// console.log(fecha)
		let total = document.getElementById("total");

		let ingresos = await loadDataTableIngresos(fecha);
		let ventas = await loadDataTableVentas(fecha);
		let egresos = await loadDataTableEgresos(fecha);
		let compras = await loadDataTableCompras(fecha);
		let gastos = await loadDataTableGastos(fecha);

		// console.log(ingresos,ventas,egresos,compras,gastos);
		total.innerText = _toFixed((ingresos + ventas) - (egresos + compras + gastos));
	}

	// @etysoft inicializa el datatable
	document.addEventListener("DOMContentLoaded", () => {
		// console.log(fecha)
		loadSumaAllDataTables();
	});

	// @etysoft retorna el valor redondeado, con la configuración global
	function _toFixed(value) {
		return parseFloat(value).toFixed(2)
	}
</script>

<?php require_once show_template('footer-configured'); ?>


