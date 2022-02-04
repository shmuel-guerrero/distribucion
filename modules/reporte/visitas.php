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
$empleado = (isset($params[0])) ? $params[0] : 0;

// Obtiene fecha inicial
$fecha_inicial = (isset($params[1])) ? $params[1] : $gestion_base;
$fecha_inicial = (is_date($fecha_inicial)) ? $fecha_inicial : $gestion_base;
$fecha_inicial = date_encode($fecha_inicial);

// Obtiene fecha final
$fecha_final = (isset($params[2])) ? $params[2] : $gestion_limite;
$fecha_final = (is_date($fecha_final)) ? $fecha_final : $gestion_limite;
$fecha_final = date_encode($fecha_final);

// Obtiene las ventas
$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, f.motivo')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->join('gps_noventa_motivos f', 'i.motivo_id = f.id_motivo', 'left')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->where('i.estadoe>',0)->where('empleado_id',$empleado)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
$noventas = $db->select('*')->from('gps_no_venta')->join('gps_noventa_motivos','motivo_id = id_motivo')->where('empleado_id',$empleado)->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_ver = in_array('proformas_ver', $permisos);
$permiso_eliminar = in_array('proformas_eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_facturar = in_array('proformas_facturar', $permisos);
$permiso_cambiar = true;

?>
<?php require_once show_template('header-configured'); ?>
<link rel="stylesheet" href="<?= css; ?>/leaflet.css">
<link rel="stylesheet" href="<?= css; ?>/leaflet-routing-machine.css">
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
.leaflet-control-attribution,
.leaflet-routing-container {
	display: none;
}
</style>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<b>Lista de todas las proformas</b>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_cambiar || $permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los siguientes botones: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_cambiar) { ?>
			<button class="btn btn-default" data-cambiar="true">
				<span class="glyphicon glyphicon-calendar"></span>
				<span class="hidden-xs">Cambiar</span>
			</button>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/operaciones/preventas_imprimir" class="btn btn-info" target="_blank">
				<span class="glyphicon glyphicon-print"></span>
				<span class="hidden-xs">Imprimir</span>
			</a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($proformas) { ?>
	<div class="row">
		<div class="col-sm-6">
			<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
				<thead>
					<tr class="active">
						<th class="text-nowrap">#</th>
						<th class="text-nowrap">Fecha</th>
						<th class="text-nowrap">Cliente</th>
						<th class="text-nowrap">NIT/CI</th>
						<th class="text-nowrap">Preventa</th>
						<th class="text-nowrap">Ubicación</th>
						<th class="text-nowrap">Motivo</th>
						<?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
						<th class="text-nowrap">Opciones</th>
						<?php } ?>
					</tr>
				</thead>
				<tfoot>
					<tr class="active">
						<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Preventa</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Ubicación</th>
						<th class="text-nowrap text-middle" data-datafilter-filter="true">Motivo</th>
						<?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
						<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
						<?php } ?>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($proformas as $nro => $proforma) {
                        $dir = $db->select('direccion')->from('inv_clientes')->where(array('cliente' => $proforma['nombre_cliente'], 'nit' => $proforma['nit_ci'] ))->fetch_first();
                        ?>
					<tr>
						<th class="text-nowrap"><?= $nro + 1; ?></th>
						<td class="text-nowrap"><?= escape(date_decode($proforma['fecha_egreso'], $_institution['formato'])); ?> <small class="text-success"><?= escape($proforma['hora_egreso']); ?></small></td>
						<td class="text-nowrap"><?= escape($proforma['nombre_cliente']); ?></td>
						<td class="text-nowrap"><?= escape($proforma['nit_ci']); ?></td>
						<td class="text-nowrap text-right"><?= escape($proforma['nro_factura']); ?></td>
						<td class="text-nowrap text-center text-middle coordenadas">
                            <?php $ubi = explode(', ', $proforma['coordenadas']) ?>
							<span class="latitud hidden"><?= $ubi[0] ?></span>
                            <span class="longitud hidden"><?= $ubi[1] ?></span>
                            <span class="estadoo hidden"><?= $proforma['estadoe'] ?></span>
                            <span class=""><?= $dir['direccion'] ?></span>
						</td>
						<td class="width-md"><?= escape($proforma['motivo']); ?></td>
						<?php if ($permiso_facturar || $permiso_ver || $permiso_eliminar || $permiso_imprimir) { ?>
						<td class="text-nowrap">
							<?php if ($permiso_facturar) { ?>
							<a href="?/operaciones/proformas_facturar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Convertir en facturar"><span class="glyphicon glyphicon-qrcode"></span></a>
							<?php } ?>
							<?php if ($permiso_ver) { ?>
							<a href="?/operaciones/proformas_ver/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Ver detalle de la proforma"><span class="glyphicon glyphicon-list-alt"></span></a>
							<?php } ?>
                            <?php if ($permiso_eliminar) { ?>
                                <a href="?/operaciones/proformas_eliminar/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Eliminar proforma" data-eliminar="true"><span class="glyphicon glyphicon-trash"></span></a>
                            <?php } ?>
                            <?php if ($permiso_imprimir) {
                                if($proforma['estadoe']==1){?>
                                <a href="?/vendedor/imprimir/<?= $proforma['id_egreso']; ?>" data-toggle="tooltip" data-title="Imprimir" data-eliminar="true"><span class="glyphicon glyphicon-print" style="color:green"></span></a>
                            <?php } if($proforma['estadoe']==2){?>
                                <a  data-toggle="tooltip" data-title="No hay venta" ><span class="glyphicon glyphicon-print" style="color:red"></span></a>
                            <?php }if($proforma['estadoe']==3){ ?>
                                <a  data-toggle="tooltip" data-title="Imprimir" ><span class="glyphicon glyphicon-print" style="color:blue"></span></a>
                            <?php    }} ?>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="col-sm-6">
			<h4 class="lead">Ruta de preventas</h4>
			<hr>
			<div id="mapid" class="embed-responsive embed-responsive-16by9"></div>
		</div>
	</div>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen preventas registradas en la base de datos.</p>
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
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label for="empleado">Empleado:</label>
                            <input type="text" name="empleado" value="<?= $empleado ?>" id="empleado" readonly class="form-control" autocomplete="off" data-validation-optional="true">
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
<script src="<?= js; ?>/leaflet.js"></script>
<script src="<?= js; ?>/leaflet-routing-machine.js"></script>
<script>
$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar la proforma y todo su detalle?', function (result) {
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
    var $empleado = $('#empleado');

	$.validate({
		form: '#form_fecha',
		modules: 'date',
		onSuccess: function () {
            var inicial_fecha = $.trim($('#inicial_fecha').val());
            var empleado = $.trim($('#empleado').val());
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
			
			window.location = '?/vendedor/visitas/' + empleado + inicial_fecha + final_fecha;
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
	
	<?php if ($proformas) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'proformas',
		reports: 'excel|word|pdf|html'
	});

	$('#states_0').find(':radio[value="hide"]').trigger('click');
	<?php } ?>


	var latitudes = new Array(), longitudes = new Array(), estados = new Array();

	$('.coordenadas').each(function (i) {
		var latitud = $.trim($(this).find('.latitud').text());
        var longitud = $.trim($(this).find('.longitud').text());
        var estado = $.trim($(this).find('.estadoo').text());
		if (latitud != '0.0' && longitud != '0.0') {
			latitudes.push(latitud);
			longitudes.push(longitud);
            estados.push(estado);
		}
	});

	if (latitudes.length != 0 && longitudes.length != 0) {

		console.log(latitudes, longitudes);

        var mbUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        //var mbUrl = 'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw';
		var cities = L.layerGroup();

		/* var myIcon = L.icon({
		     iconUrl: 'assets/imgs/my-icon.png',
		     iconSize: [38, 95],
		     iconAnchor: [22, 94],
		     popupAnchor: [-3, -76],
		     shadowUrl: 'my-icon-shadow.png',
		     shadowSize: [68, 95],
		     shadowAnchor: [22, 94]
		 }); */

		p1 = (parseFloat(latitudes[0]) + parseFloat(latitudes[latitudes.length - 1])) / 2;
		p2 = (parseFloat(longitudes[0]) + parseFloat(longitudes[longitudes.length - 1])) / 2;

		var mymap = L.map('mapid', {
			center: [p1, p2],
			zoom: 15,
			layers: [cities]
		});
        var LeafIcon = L.Icon.extend({
            options: {
                iconAnchor:   [15, 40],
                popupAnchor:  [-3, -76]
            }
        });
		//L.marker([50.505, 30.57], {icon: myIcon}).addTo(mymap);

		L.tileLayer(mbUrl, {
			maxZoom: 20,
			id: 'mapbox.light'
		}).addTo(mymap);

		// L.circle([x1, y1], 30, {
		// 	color: 'red',
		// 	fillColor: '#f03',
		// 	fillOpacity: 0.5
		// }).addTo(mymap).bindPopup("I am a circle.");
        var greenIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/green.png' ?>'}),
            redIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/red.png' ?>'}),
            blueIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/blue.png' ?>'});

		var waypoints = new Array();

		for (i in latitudes) {
			waypoints.push(L.latLng(latitudes[i], longitudes[i]));
		}
        console.log(waypoints);
		L.Routing.control({
            waypoints: waypoints,
            createMarker: function(i, waypoints, n) {
                if(estados[i]==1){
                    return L.marker( waypoints.latLng, {icon: greenIcon });
                }else{
                    return L.marker( waypoints.latLng, {icon: redIcon });
                }
            }
        }).addTo(mymap);
	}
});
</script>
<?php require_once show_template('footer-configured'); ?>