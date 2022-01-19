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
$Consulta=$db->query("SELECT fecha_egreso,monto_total,nro_autorizacion,codigo_control,nro_factura,nombre_cliente,nit_ci
                        FROM inv_egresos
                        WHERE (nro_factura!='' OR nro_factura!='0') 
                        AND tipo='Venta'
                        AND anulado < 2
                        AND fecha_egreso BETWEEN '{$fecha_inicial}' AND '{$fecha_final}'")->fetch();

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
		<b>Libro de ventas</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_crear || $permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar un ingreso hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
            <?php if ($permiso_cambiar) { ?>
            <button class="btn btn-default" data-cambiar="true">
                <span class="glyphicon glyphicon-calendar"></span>
                <span class="hidden-xs">Cambiar</span>
            </button>
            <?php } ?>
            <?php if ($permiso_imprimir) { ?>
                <a href="?/egresos/libro_venta_pdf/<?=$fecha_inicial?>/<?=$fecha_final?>" target="_blank" class="btn btn-info">
                <span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Pdf</span>

				<!--<a href="?/egresos/libro_venta_excel/<?=$fecha_inicial?>/<?=$fecha_final?>" target="_blank" class="btn btn-info">
                <span class="glyphicon glyphicon-print"></span>
                <span class="hidden-xs">Excel</span>-->
            </a>
            <?php } ?>
            <a href="?/egresos/listar" class="btn btn-primary"><span class="glyphicon glyphicon-log-out"></span><span class="hidden-xs"> Volver</span></a>
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
				<th class="text-nowrap">Nombre o Razon Social del Comprador</th>
				<th class="text-nowrap">Nro. Factura</th>
				<th class="text-nowrap">Nro. Autorizacion</th>
				<th class="text-nowrap">Código Control</th>
				<th class="text-nowrap">Total Factura</th>
                <!--<th class="text-nowrap">Total ICE</th>-->
                <!--<th class="text-nowrap">Imp. Exce.</th>-->
                <!--<th class="text-nowrap">Imp. Neto</th>-->
				<th class="text-nowrap">Débido Fiscal</th>
				<th class="text-nowrap">IT</th>
				<th class="text-nowrap">Total Válido</th>
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
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Total Factura</td>
                <!--<td class="text-nowrap text-middle" data-datafilter-filter="true">Total ICE</td>-->
                <!--<td class="text-nowrap text-middle" data-datafilter-filter="true">Imp. Exce.</td>-->
                <!--<td class="text-nowrap text-middle" data-datafilter-filter="true">Imp. Neto</td>-->
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Débido Fiscal</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">IT</td>
                <td class="text-nowrap text-middle" data-datafilter-filter="true">Total Válido</td>
            </tr>
		</tfoot>
		<tbody>
            <?php
                //$Total=0;
                foreach ($Consulta as $Nro => $Dato) {
                $Fila=$Nro+1;
                $SubTotal=$Dato['cantidad']*$Dato['costo'];
                //$Total=$Total+$SubTotal;
                if ($Dato['codigo_control']) {
                    $debito = round($Dato['monto_total']*0.13 ,2);
                    $ite = round($Dato['monto_total']*0.03 ,2);
                    $valido = round( ($Dato['monto_total'] - ($debito + $ite)) ,2);
                } else {
                    $debito = 0.00;
                    $ite = 0.00;
                    $valido = round($Dato['monto_total'] ,2);
                }
            ?>
                <tr>
                    <td style="text-align: center;"><?=$Fila?></td>
                    <td style="text-align: center;"><?=$Dato['fecha_egreso']?></td>
                    <td style="text-align: center;"><?=$Dato['nit_ci']?></td>
                    <td style="text-align: center;"><?=$Dato['nombre_cliente']?></td>
                    <td style="text-align: center;"><?=$Dato['nro_factura']?></td>
                    <td style="text-align: center;"><?=$Dato['nro_autorizacion']?></td>
                    <td style="text-align: center;"><?=$Dato['codigo_control']?></td>
                    <td style="text-align: center;" data-facturado="<?=$Dato['monto_total']?>"><?=$Dato['monto_total']?></td>
                    <!--<td style="text-align: center;">0.00</td>-->
                    <!--<td style="text-align: center;">0.00</td>-->
                    <!--<td style="text-align: center;"><?php // $Dato['monto_total']?></td>-->
                    <td style="text-align: center;" data-debito="<?= $debito ?>"><?= $debito ?></td>
                    <td style="text-align: center;" data-ite="<?= $ite ?>"><?= $ite ?></td>
                    <td style="text-align: center;" data-valido ="<?= $valido ?>"><?= $valido ?></td>
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
		<div class="col-sm-3">
			<p class="lead margin-none">
				<b>Total Facturado:</b>
				<u id="total_facturado">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-3">
			<p class="lead margin-none">
				<b>Debito Fiscal:</b>
				<u id="debito_fiscal">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-3">
			<p class="lead margin-none">
				<b>Total Valido:</b>
				<u id="total_ite">0.00</u>
				<span><?= escape($moneda); ?></span>
			</p>
		</div>
		<div class="col-sm-3">
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

			window.location = '?/egresos/libro_venta' + inicial_fecha + final_fecha;
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
    		var suma4 = 0;
    
    		$('[data-facturado]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-facturado'));
                console.log(total);
    			suma1 = suma1 + total;
    		});
    		$('#total_facturado').text(suma1.toFixed(2));
    		$('[data-debito]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-debito'));
                console.log(total);
    			suma2 = suma2 + total;
    		});
    		$('#debito_fiscal').text(suma2.toFixed(2));
    		$('[data-ite]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-ite'));
                console.log(total);
    			suma4 = suma4 + total;
    		});
    		$('#total_ite').text(suma4.toFixed(2));
    		$('[data-valido]:visible').each(function (i) {
    			var total = parseFloat($(this).attr('data-valido'));
                console.log(total);
    			suma3 = suma3 + total;
    		});
    		$('#total_valido').text(suma3.toFixed(2));
		}).DataFilter({
			filter: true,
			name: 'Ingresos',
			reports: 'excel|word|pdf|html'
		});
	

	$('#states_0').find(':radio[value="hide"]').trigger('click');
	<?php } ?>
});
</script>
<?php require_once show_template('footer-advanced'); ?>