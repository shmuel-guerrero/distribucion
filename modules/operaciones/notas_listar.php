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
$ventas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, c.cliente')
            ->from('inv_egresos i')
            ->join('inv_clientes c', 'i.cliente_id = c.id_cliente', 'left')
            ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
            ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
            ->where('i.tipo', 'Venta')->where('i.codigo_control', '')->where('i.estadoe!=', 2)->where('i.estadoe!=', 1)->where('i.provisionado', 'S')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
$ventas2 = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno,  c.cliente')
            ->from('inv_egresos i')
            ->join('inv_clientes c', 'i.cliente_id = c.id_cliente', 'left')
            ->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')
            ->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')
            ->where('i.tipo', 'Venta')->where('i.codigo_control!=', '')->where('i.anulado', 2)->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
            
$ventas = array_merge($ventas,$ventas2);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('notas_crear', $permisos);
$permiso_ver = in_array('notas_ver', $permisos);
$permiso_eliminar = in_array('notas_eliminar', $permisos);
$permiso_imprimir = in_array('notas_imprimir', $permisos);
$permiso_activar_factura = in_array('activar_nota', $permisos);
$permiso_devolucion = in_array('notas_devolucion', $permisos);
$permiso_cambiar = true;

?>
<?php require_once show_template('header-advanced'); ?>
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
		<strong>Lista de todas las notas de remisión</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear) { ?>
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para realizar una nota de remisión hacer clic en el siguiente botón: </div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<?php if ($permiso_cambiar) { ?>
					<button class="btn btn-default" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar</span></button>
				<?php } ?>
				<?php if ($permiso_crear) { ?>
					<a href="?/operaciones/notas_crear" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span><span class="hidden-xs"> Vender</span></a>
				<?php } ?>
				<?php if ($permiso_imprimir) { ?>
					<a href="?/operaciones/notas_imprimir" class="btn btn-primary" target="_blank" data-imprimir="true"><span class="glyphicon glyphicon-file"></span><span class="hidden-xs"> Imprimir</span></a>
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
					<th class="text-nowrap">Codigo</th>
					<th class="text-nowrap">Cliente</th>
					<th class="text-nowrap">NIT/CI</th>
					<th class="text-nowrap">Pendiente</th>
					<th class="text-nowrap">Nro. Nota</th>
					<th class="text-nowrap">Tipo</th>
					<th class="text-nowrap">Monto total <?= escape($moneda); ?></th>
					<th class="text-nowrap">Registros</th>
					<th class="text-nowrap">Estado</th>
					<th class="text-nowrap">Almacen</th>
					<th class="text-nowrap">Empleado</th>
					<?php if ($permiso_ver || $permiso_eliminar) { ?>
						<th class="text-nowrap">Opciones</th>
					<?php } ?>
					<!--<th class="text-nowrap">-->
					<!--	<input type="checkbox" class="text-checkbox" data-toggle="tooltip" data-title="Seleccionar producto" data-grupo-seleccionar="true">-->
					<!--</th>-->
				</tr>
			</thead>
			<tfoot>
				<tr class="active">
					<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Codigo</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Pendiente</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Nro. Nota</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Monto total <?= escape($moneda); ?></th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Estado</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Almacen</th>
					<th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
					<?php if ($permiso_ver || $permiso_eliminar) { ?>
						<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
					<?php } ?>
					<!--<th class="text-nowrap" data-datafilter-filter="false"></th>-->
				</tr>
			</tfoot>
			<tbody>
				<?php foreach ($ventas as $nro => $venta) { ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap"><?= escape(date_decode($venta['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($venta['hora_egreso']); ?></small></td>
						<td class="text-nowrap">Nota de remisión</td>
						<td class="text-nowrap"><?= escape($venta['cliente_id']); ?></td>
						<!--<td class="text-nowrap"><?php // escape($venta['nombre_cliente']); ?></td>-->
						<td class="text-nowrap">
        					<b>Cliente: </b><?= ($venta['cliente'])?$venta['cliente']:$venta['nombre_cliente']; ?> <br>
        					<span class="text-muted"><b>Razón social: </b><?= escape($venta['nombre_cliente']); ?></span>
        				</td>
						<td class="text-nowrap"><?= escape($venta['nit_ci']); ?></td>
						<td class="text-nowrap">
							<?php if ($venta['cobrar'] == 'si') { ?><a href="?/operaciones/activar/<?= $venta['id_egreso']; ?>" class="label label-danger" data-toggle="tooltip" data-title="Cambiar estado" data-activar="true">Cobrar</a> <?= $venta['observacion']; ?><?php } else { ?><span class="label label-success">Pagado</span> <?php } ?>
						</td>
						<td class="text-nowrap text-right">
							<?= escape($venta['nro_factura']); ?></td>
						<td class="text-nowrap text-right">
							<?php if($venta['estadoe']!=0){echo 'Preventa';}else{echo 'Nota Remisión';} ?></td>
						<td class="text-nowrap text-right" data-total="<?= escape($venta['monto_total_descuento']); ?>"><?= escape($venta['monto_total_descuento']); ?></td>
						<td class="text-nowrap text-right"><?= escape($venta['nro_registros']); ?></td>
						<td class="text-nowrap text-right">
							<?php if($venta['anulado'] == 0){echo 'Activo'; }else{if($venta['anulado'] == 2){echo '<span class="text-info" >Anulado de factura</span>';}else{ echo 'Anulado';} } ?> </td> 
						<td class="text-nowrap"><?= escape($venta['almacen']); ?></td>
						<td class="width-md"><?= escape($venta['nombres'] . ' ' . $venta['paterno'] . ' ' . $venta['materno']); ?></td>
						<?php if ($permiso_ver || $permiso_eliminar) { ?>
							<td class="text-nowrap">
							    <?php if ($venta['anulado'] != 3 /* || $venta['fecha_egreso'] == date('Y-m-d') */) { ?>
							        <a href="?/operaciones/notas_editar/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Editar nota de remisión"><i class="glyphicon glyphicon-edit"></i></a>
							    <?php } ?>
								<?php if ($permiso_ver) { ?>
									<a href="?/operaciones/notas_ver/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de nota de remisión"><i class="glyphicon glyphicon-list-alt"></i></a>
								<?php } ?>
							<?php if ($venta['anulado'] != 3) { ?>
    								<?php if ($permiso_eliminar) { ?>
    									<a href="?/operaciones/notas_eliminar/<?= $venta['id_egreso']; ?>" data-toggle="tooltip" data-title="Eliminar nota de remisión" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
    								<?php } ?>
    							 <?php 
                				    if ($permiso_activar_factura && $venta['estadoe'] > 0){
                                            if ($venta['anulado'] == 1){ ?>
                                                <a href='?/operaciones/activar_nota/<?= $venta['id_egreso']; ?>' class='text-info' data-toggle='tooltip' data-title='Confirmar anulacion' data-activar-producto='true'><i class='glyphicon glyphicon-check'></i></a>
                                    <?php   } if($venta['anulado'] == 2) { ?>
                                                <a href='?/operaciones/activar_nota/<?= $venta['id_egreso']; ?>' class='text-danger' data-toggle='tooltip' data-title='Anular factura' data-activar-producto='true'><i class='glyphicon glyphicon-unchecked'></i></a>
                                    <?php   } if($venta['anulado'] == 0) { ?>
                                                <a href='?/operaciones/activar_nota/<?= $venta['id_egreso']; ?>' class='text-danger' data-toggle='tooltip' data-title='Anular factura' data-activar-producto='true'><i class='glyphicon glyphicon-unchecked'></i></a>
                                    <?php   } ;
                                    };
                                    if($permiso_devolucion): ?> 
                                        <!-- <a href='?/operaciones/notas_devolucion/<?= $venta['id_egreso']; ?>' data-toggle='tooltip' data-title='devolución'><span class='glyphicon glyphicon-transfer'></span></a>
    									<a onclick="convertir_facturar(<?= $venta['id_egreso']; ?>)" data-toggle='tooltip' data-title='Convertir de Nota a Electronica'><span class='glyphicon glyphicon-refresh'></span></a> -->
    									<!-- <a href='?/operaciones/nota_electronica/<?= $venta['id_egreso']; ?>' data-toggle='tooltip' data-title='Convertir de Nota a Electronica'><span class='glyphicon glyphicon-refresh'></span></a> -->
                                    <?php endif;
                                ?>
                            <?php } ?>
							</td>
						<?php } ?>
						
						<!--<th class="text-nowrap">-->
						<!--	<input type="checkbox" data-toggle="tooltip" data-title="Seleccionar" data-seleccionar="<?php // $venta['id_egreso']; ?>">-->
						<!--</th>-->
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
	<div class="well">
		<p class="lead margin-none">
			<b>Empleado:</b>
			<span><?= escape($_user['nombres'] . ' ' . $_user['paterno'] . ' ' . $_user['materno']); ?></span>
		</p>
		<p class="lead margin-none">
			<b>Total: </b><u id="total">0.00</u>
			<span><?= escape($moneda); ?></span>
		</p>
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
<script src="<?= js; ?>/jquery.maskedinput.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/moment.min.js"></script>
<script src="<?= js; ?>/moment.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
	$(function() {
		<?php if ($permiso_crear) { ?>
			$(window).bind('keydown', function(e) {
				if (e.altKey || e.metaKey) {
					switch (String.fromCharCode(e.which).toLowerCase()) {
						case 'n':
							e.preventDefault();
							window.location = '?/operaciones/notas_crear';
							break;
					}
				}
			});
		<?php } ?>

		<?php if ($permiso_eliminar) { ?>
			$('[data-eliminar]').on('click', function(e) {
				e.preventDefault();
				var url = $(this).attr('href');
				bootbox.confirm('Está seguro que desea eliminar la nota de remisión y todo su detalle?', function(result) {
					if (result) {
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

					window.location = '?/operaciones/notas_listar' + inicial_fecha + final_fecha;
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
		var $grupo_seleccionar = $('[data-grupo-seleccionar]'),
			$seleccionar = $('[data-seleccionar]'),
			$imprimir = $('[data-imprimir]');
		$grupo_seleccionar.on('change', function() {
			$seleccionar.prop('checked', $(this).prop('checked')).trigger('change');
		});

		$seleccionar.on('change', function() {
			var $this = $(this),
				todos = $seleccionar.size(),
				productos = [],
				check = 0;
			$seleccionar.filter(':checked').each(function() {
				productos.push($(this).attr('data-seleccionar'));
				check = check + 1;
			});
			if ($this.prop('checked')) {
				$this.closest('tr').addClass('info');
			} else {
				$this.closest('tr').removeClass('info');
			}
			switch (check) {
				case 0:
					$grupo_seleccionar.prop({
						checked: false,
						indeterminate: false
					});
					break;
				case todos:
					$grupo_seleccionar.prop({
						checked: true,
						indeterminate: false
					});
					break;
				default:
					$grupo_seleccionar.prop({
						checked: false,
						indeterminate: true
					});
					break;
			}
			productos = productos.join('-');
			productos = (productos != '') ? productos : 'true';
			$imprimir.attr('data-imprimir', productos);
		});
		
		$imprimir.on('click', function(e) {
			if ($imprimir.attr('data-imprimir') == 'true') {
				e.preventDefault();
				bootbox.alert('Debe seleccionar al menos una orden de compra.');
			} else {
				$imprimir.attr('href', $imprimir.attr('href') + '/' + $imprimir.attr('data-imprimir'));
				window.location.reload();
			}
		});

		<?php if ($ventas) { ?>
		var table = $('#table').on('draw.dt', function () { // search.dt order.dt page.dt length.dt
    		var suma = 0;
    		$('[data-total]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-total'));
                //console.log(total);
    			suma = suma + total;
    		});
    		$('#total').text(suma.toFixed(2));
    	}).DataFilter({
    		filter: true,
    		name: 'notas_remision',
    		reports: 'excel|word|pdf|html'
    	});
// 			var table = $('#table').DataFilter({
// 				filter: true,
// 				name: 'notas_remision',
// 				reports: 'excel|word|pdf|html'
// 			});
		<?php } ?>
	});
	function convertir_facturar (id_venta){
		// alert('mostrando id_venta '+id_venta);
		bootbox.confirm('¿Está seguro que desea convertir la Nota de remisión a Factura?', function (result) {
			if(result){
	
				$.ajax({
					type: 'post',
					dataType: 'json',
					url: '?/operaciones/nota_electronica',
					data: {
						id_venta: id_venta
					}
				}).done(function (egreso) {
					
						$('#loader').fadeIn(100);
						window.open('?/electronicas/imprimir/' + egreso, '_blank');						
						window.location = '?/operaciones/notas_listar/<?= $fecha_inicial."/".$fecha_final ?>';

					$.notify({
						message: 'La conversin se actualizó correctamente.'
					}, {
						type: 'success'
					});
				}).fail(function () {
					$.notify({
						message: 'No se puede convertir.'
					}, {
						type: 'danger'
					});
				});

			}

		});
	}
</script>
<?php require_once show_template('footer-advanced'); ?>