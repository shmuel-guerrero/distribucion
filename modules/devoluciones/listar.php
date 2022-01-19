<?php
$id_empleado = (isset($params[0])) ? $params[0] : 0;
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

// Obtiene los egresos
$egresos = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.tipo != ', 'Venta')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los almacenes
$almacenes = $db->from('inv_almacenes')->fetch();

$empleado = $db->select('*')->from('sys_empleados')->where('id_empleado',$id_empleado)->fetch_first();
// Obtiene los movimientos
$movimientos = $db->query("SELECT c.*, d.motivo, e.* FROM sys_users a
	LEFT JOIN sys_empleados b ON a.persona_id = b.id_empleado
	LEFT JOIN tmp_egresos c ON b.id_empleado = c.empleado_id
	LEFT JOIN gps_noventa_motivos d ON c.motivo_id = d.id_motivo
	LEFT JOIN sys_empleados e ON c.distribuidor_id = e.id_empleado
    WHERE a.rol_id != 4 AND b.id_empleado = '$id_empleado' AND c.distribuidor_fecha >= '$fecha_inicial' AND c.distribuidor_id <= '$fecha_final'")->fetch();
    
// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
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
		<strong>Listado de Devoluciones</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear || $permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar un egreso hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_cambiar) { ?>
			<button class="btn btn-default" data-cambiar="true">
				<span class="glyphicon glyphicon-calendar"></span>
				<span class="hidden-xs">Cambiar</span>
			</button>
			<?php } ?>
			<!--<?php if ($permiso_imprimir) { ?>
			<a href="?/egresos/imprimir" target="_blank" class="btn btn-info">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>-->
			<?php if ($permiso_crear) { ?>
			<div class="btn-group">
				<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
					<span class="glyphicon glyphicon-plus"></span>
					<span>Egresar</span>
				</button>
				<ul class="dropdown-menu dropdown-menu-right">
					<?php foreach ($almacenes as $elemento) { ?>
					<li><a href="?/egresos/crear/<?= $elemento['id_almacen']; ?>"><span class="glyphicon glyphicon-star"></span> <?= escape($elemento['almacen'] . (($elemento['principal'] == 'S') ? ' (principal)' : '')); ?></a></li>
					<?php } ?>
				</ul>
			</div>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($movimientos) { ?>
            <h3 class="text-center">DEVOLUCIONES</h3>
            <div class="table-responsive">
                <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                    <thead>
                    <tr class="active">
                        <th class="text-nowrap">#</th>
                        <th class="text-nowrap">Venta</th>
                        <th class="text-nowrap">Cliente</th>
                        <th class="text-nowrap">Motivo</th>
                        <th class="text-nowrap">Registros</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Productos</th>
                        <th class="text-nowrap">Cantidad</th>
                        <th class="text-nowrap">Nombre</th>
                        <th class="text-nowrap">Fecha</th>
                        <th class="text-nowrap">Hora</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr class="active">
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">#</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Venta</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Motivo</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Productos</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Hora</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php foreach($movimientos as $nro => $movimiento){
                        $productos = $db->select('*, a.unidad_id as unidad_venta')->from('tmp_egresos_detalles a')->join('inv_productos b','a.producto_id=b.id_producto')->where(array('a.egreso_id'=>$movimiento['id_egreso'],'tmp_egreso_id'=>$movimiento['id_tmp_egreso']))->fetch();
                        if($movimiento['distribuidor_estado']=='ENTREGA'){?>
                    
                    <?php }else{ ?>
                            <tr>
                                <th class="text-nowrap"><?= $nro + 1; ?></th>
                                <td class="text-nowrap">
                                    <span><?= escape($movimiento['fecha_egreso']); ?></span>
                                    <span class="text-primary"><?= escape($movimiento['hora_egreso']); ?></span>
                                </td>
                                <td class="text-nowrap"><?= (escape($movimiento['nombre_cliente'])); ?></td>
                                <td class="text-nowrap text-left danger"><?php if(isset($movimiento['motivo'])){echo escape($movimiento['motivo']);}else{if($movimiento['distribuidor_estado']=='ALMACEN'){echo 'Devuelto al almacen';}}  ?></td>
                                <td class="text-nowrap text-right danger"><strong><?= escape($movimiento['nro_registros']); ?></strong></td>
                                <td class="text-nowrap text-right danger"><strong><?= number_format(($movimiento['monto_total']), 2, '.', ''); ?></strong></td>
                                <td class="text-nowrap text-right danger"><?php foreach($productos as $producto){echo $producto['nombre'].'<br>';}  ?></td>
                                <td class="text-nowrap text-right danger"><?php foreach($productos as $producto){echo ($producto['cantidad']/cantidad_unidad($db,$producto['id_producto'],$producto['unidad_venta'])).' '.nombre_unidad($db,$producto['unidad_venta']).'<br>';}  ?></td>
                                <td class="text-nowrap text-center info text-primary"><strong><?= $movimiento['nombres'].' '.$movimiento['paterno']; ?></strong></td>
                                <td class="text-nowrap text-right info"><?= $movimiento['distribuidor_fecha']; ?></td>
                                <td class="text-nowrap text-right info"><?= $movimiento['distribuidor_hora']; ?></td>
                            </tr>
                    <?php   }
                    } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>El listado de devoluciones no puede mostrarse por que no existen movimientos registrados.</p>
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
			
			window.location = '?/devoluciones/listar' + inicial_fecha + final_fecha;
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
	
	<?php if ($egresos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'Devoluciones',
		reports: 'excel|word|pdf|html'
	});

	$('#states_0').find(':radio[value="hide"]').trigger('click');
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>