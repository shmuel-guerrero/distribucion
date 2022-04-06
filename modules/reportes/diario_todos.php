<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = date('Y-m-d');

// Obtiene fecha inicial
$fecha_inicial = (isset($params[0])) ? $params[0] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[1])) ? $params[1] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
$IdUsuario=$_user['id_user'];
$ventas = $db->query("SELECT e.fecha_egreso, e.hora_egreso, e.descripcion, e.nro_factura as nro_movimiento, 
                        e.nombre_cliente, e.nit_ci, cl.id_cliente as codigo, 
                        e.tipo, d.*, d.unidad_id as unidad_otra, e.monto_total, p.nombre, ca.categoria, p.descripcion AS descripcionp, concat(em.nombres, ' ', em.paterno, ' ', em.materno) as empleado, d.producto_id, em.cargo
                    FROM inv_egresos_detalles d
                    INNER JOIN inv_egresos e ON d.egreso_id = e.id_egreso
                    LEFT JOIN inv_clientes cl ON e.cliente_id = cl.id_cliente
                    
                
                LEFT JOIN inv_productos p ON d.producto_id = p.id_producto
                LEFT JOIN inv_categorias ca ON p.categoria_id = ca.id_categoria
                LEFT JOIN sys_empleados em ON e.empleado_id = em.id_empleado
                WHERE e.tipo = 'Venta' AND e.fecha_egreso between '$fecha_inicial' AND '$fecha_final'
                ")->fetch();

				//LEFT JOIN sys_users AS u ON em.id_empleado=u.persona_id
                

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Reporte de ventas por producto</strong>
	</h3>
</div>
<div class="panel-body" data-servidor="<?= ip_local . name_project . '/diario_todos.php'; ?>">
		<div class="row">
			<div class="col-sm-8 hidden-xs">
				<div class="text-label">Para cambiar la fecha hacer clic en el siguiente botón: </div>
			</div>
			<div class="col-xs-12 col-sm-4 text-right">
				<button class="btn btn-primary" data-cambiar="true"><i class="glyphicon glyphicon-calendar"></i><span class="hidden-xs"> Cambiar fecha</span></button>
			</div>
		</div>
		<hr>
	
	<?php 
	if (true) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">Fecha</th>
				<th class="text-nowrap">C&oacute;digo</th>
				<th class="text-nowrap">Cliente</th>
				<th class="text-nowrap">NIT/CI</th>
				<th class="text-nowrap">Direccion</th>
				
				<th class="text-nowrap">Tipo</th>
				<th class="text-nowrap">Factura</th>
				<th class="text-nowrap">Producto</th>
				<th class="text-nowrap">L&iacute;nea</th>
				<th class="text-nowrap">Categoria</th>
				
				<th class="text-nowrap">Cantidad</th>
				<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap">Descuento (%)</th>
				<th class="text-nowrap">Importe <?= escape($moneda); ?></th>
				
				<th class="text-nowrap">Empleado</th>
				<th class="text-nowrap">Empresa</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">C&oacute;digo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Direccion</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Factura</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Producto</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">L&iacute;nea</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Descuento (%)</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Importe <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Empleado</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Empresa</th>
			</tr>
		</tfoot>
		<tbody>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen unidades registradas en la base de datos, para crear nuevas unidades hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
	</div>
	<?php } ?>
</div>

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
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>

<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>

$(function() {
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

			window.location = '?/reportes/diario_todos' + inicial_fecha + final_fecha;
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

	CargarBD("<?= $fecha_inicial ?>");
});

function CargarBD(date1){
	$.ajax({
	  	method: "POST",
	  	url: "?/reportes/diario_todos_buscar/"+date1+"/<?= $fecha_final ?>",
	  	data: { name: "John", location: "Boston" }
	})
	.done(function( msg ) {
		
		msg1=msg.substring(2, 12);
		msg2=msg.substring(13, msg.length);

		$('#table tbody').append(msg2);
		
		//alert("yy"+msg1+"yy");
		
		if(msg1!="0000-00-00"){
			//alert(msg2);
			CargarBD(msg1);
		}
		else{
			<?php if ($ventas) { ?>
				var table = $('#table').DataFilter({
					filter: false,
					name: 'unidades',
					reports: 'xls|doc|pdf|html',
					
				});
			<?php } ?>
		}
	});
}

</script>
<?php require_once show_template('footer-configured'); ?>