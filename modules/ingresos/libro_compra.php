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

// Obtiene los ingresos
$Consulta = $db->query("SELECT inv_ingresos.fecha_ingreso,inv_proveedores.proveedor, inv_proveedores.nit, inv_ingresos.monto_total
                        FROM inv_ingresos 
                        LEFT JOIN inv_proveedores ON inv_ingresos.nombre_proveedor = inv_proveedores.proveedor 
                        WHERE inv_ingresos.tipo = 'Compra'
                        AND inv_ingresos.fecha_ingreso BETWEEN '{$fecha_inicial}' AND '{$fecha_final}' 
                        GROUP BY inv_ingresos.fecha_ingreso, inv_ingresos.nombre_proveedor")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = true;

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
		<b>Libro de compras</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear || $permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
            <?php if ($permiso_cambiar) { ?>
            <button class="btn btn-default" data-cambiar="true">
                <span class="glyphicon glyphicon-calendar"></span>
                <span class="hidden-xs">Cambiar</span>
            </button>
            <?php } ?>
            <?php if ($permiso_imprimir) { ?>
                <a href="?/ingresos/libro_compra_pdf/<?=$fecha_inicial?>/<?=$fecha_final?>" target="_blank" class="btn btn-info">
                <span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Pdf</span>

				<!--<a href="?/ingresos/libro_compra_excel/<?=$fecha_inicial?>/<?=$fecha_final?>" target="_blank" class="btn btn-info">
                <span class="glyphicon glyphicon-print"></span>
                <span class="hidden-xs">Excel</span>-->
            </a>
            <?php } ?>
            <a href="?/ingresos/listar" class="btn btn-primary"><span class="glyphicon glyphicon-log-out"></span><span class="hidden-xs"> Volver</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($Consulta) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">FECHA</th>
				<th class="text-nowrap">NIT</th>
				<th class="text-nowrap">Nombre o Razon Social del Proveedor</th>
				<th class="text-nowrap">Nro. Factura</th>
				<th class="text-nowrap">Nro. Autorizacion</th>
				<th class="text-nowrap">Código Control</th>
				<th class="text-nowrap">Total Factura <?= $moneda?></th>
                <!--<th class="text-nowrap">Total ICE</th>-->
                <!--<th class="text-nowrap">Imp. Exce.</th>-->
                <!--<th class="text-nowrap">Imp. Neto</th>-->
				<th class="text-nowrap">Crédito Fiscal <?= $moneda?></th>
				<th class="text-nowrap">Total Valido <?= $moneda?></th>
            </tr>
		</thead>
		<tfoot>
            <tr class="active">
                <td class="text-nowrap text-middle" data-datafilter-filter="false">#</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">FECHA</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">NIT</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Nombre o Razon Social del Comprador</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Nro. Factura</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Nro. Autorizacion</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Código Control</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Total Factura <?= $moneda?></td>
                <!--<td class="text-nowrap text-middle" data-datafilter-filter="true">Total ICE</td>-->
                <!--<td class="text-nowrap text-middle" data-datafilter-filter="true">Imp. Exce.</td>-->
                <!--<td class="text-nowrap text-middle" data-datafilter-filter="true">Imp. Neto</td>-->
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Crédito Fiscal <?= $moneda?></td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Total Valido <?= $moneda?></td>
            </tr>
		</tfoot>
		<tbody>
            <?php
                //$Total=0;
                foreach ($Consulta as $nro => $Dato) {
                $Fila=$Nro+1;
                // $SubTotal=$Dato['cantidad']*$Dato['costo'];
                $SubTotal = $Dato['monto_total'];
                //$Total=$Total+$SubTotal;
            ?>
                <tr>
                    <td class="text-left"><?=$Fila?></td>
                    <td class="text-left"><?=$Dato['fecha_ingreso']?></td>
                    <td class="text-left"><?=$Dato['nit']?></td>
                    <td class="text-left"><?=$Dato['proveedor']?></td>
                    <td class="text-right"><?=($Dato['factura']) ? $Dato['factura'] : 'S/N'?></td>
                    <td class="text-right"><?=($Dato['nro_autorizacion']) ? $Dato['nro_autorizacion'] : 'S/N'?></td>
                    <td class="text-right"><?=($Dato['nro_control']) ? $Dato['nro_control'] : 'S/N'?></td>
                    <td class="text-right" data-facturado="<?= $SubTotal ?>"><?=$SubTotal?></td>
                    <!--<td style="text-align: center;">0.00</td>-->
                    <!--<td style="text-align: center;">0.00</td>-->
                    <!--<td style="text-align: center;"><?php //$SubTotal?></td>-->
                    <td class="text-right" data-credito="<?= round($SubTotal*0.13,2) ?>"><?php echo round($SubTotal*0.13,2); ?></td>
                    <td class="text-right" data-valido="<?= round(($SubTotal-($SubTotal*0.13)),2) ?>"><?php echo round(($SubTotal-($SubTotal*0.13)),2); ?></td>
                </tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen ingresos registrados en la base de datos.</p>
	</div>
	<?php } ?>
	
	<div class="well">
		<div class="col-sm-4">
			<p class="lead margin-none">
				<b>Total Facturado:</b>
				<u id="total_facturado">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-4">
			<p class="lead margin-none">
				<b>Credito Fiscal:</b>
				<u id="credito_fiscal">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-4">
			<p class="lead margin-none">
				<b>Total Valido:</b>
				<u id="total_valido">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="clearfix">
		</div>
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
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el ingreso y todo su detalle?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

    $('[data-activar]').on('click', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        bootbox.confirm('Está seguro que desea cambiar el estado del ingreso?', function (result) {
            if(result){
                window.location = url;
            }
        });
    });

	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/ingresos/crear';
				break;
			}
		}
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

			window.location = '?/ingresos/libro_compra' + inicial_fecha + final_fecha;
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

	<?php if ($Consulta) { ?>
	    var table = $('#table').on('draw.dt', function() { // search.dt order.dt page.dt length.dt
            var suma1 = 0;
    		var suma2 = 0;
    		var suma3 = 0;
    
    		$('[data-facturado]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-facturado'));
                console.log(total);
    			suma1 = suma1 + total;
    		});
    		$('#total_facturado').text(suma1.toFixed(2));
    		$('[data-credito]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-credito'));
                console.log(total);
    			suma2 = suma2 + total;
    		});
    		$('#credito_fiscal').text(suma2.toFixed(2));
    		$('[data-valido]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-valido'));
                console.log(total);
    			suma3 = suma3 + total;
    		});
    		$('#total_valido').text(suma3.toFixed(2));
		}).DataFilter({
			filter: true,
			name: 'Ingresos',
			reports: 'xls|doc|pdf|html'
		});
				
    // 	var table = $('#table').DataFilter({
    // 		filter: true,
    // 		name: 'ingresos',
    // 		reports: 'xls|doc|pdf|html'
    // 	});

	$('#states_0').find(':radio[value="hide"]').trigger('click');
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>