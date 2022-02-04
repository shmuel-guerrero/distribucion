<?php

$id_ruta = ($_user['persona_id'] > 0) ? $_user['persona_id'] : 0;

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

// Obtiene las ruta
//$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->where('i.estadoe>',0)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
//$ruta = $db->select('*')->from('gps_rutas')->where('empleado_id',$id_ruta)->fetch_first();
$ruta = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->where('i.estadoe',1)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
$coordenadas='';
$estado1 = '';
$lugares = '';
foreach($ruta as $ruta2){
    $coordenadas='*'.$ruta2['coordenadas'].$coordenadas;
    $estado1 = '*'.$ruta2['estadoe'].$estado1;
    $lugares = '*'.$ruta2['nombre_cliente'].$lugares;
}
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
		<b>Ver ruta</b>
	</h3>
</div>
<div class="panel-body">

	<div class="row">
		<div class="col-sm-8 hidden-xs">
            <span class="text-danger"><?php if($id_ruta = 0){echo 'No se asigno empleado';} ?></span>
			<div class="text-label">Para realizar una acción hacer clic en los siguientes botones: </div>
		</div>
        <div class="col-sm-4 col-xs-12">

            <a href="?/distribuidor/visitas/<?= $fecha_inicial ?>/<?= $fecha_final ?>" type="button" id="listar" class="btn btn-primary" >Listas</a>
        <?php if($permiso_imprimir){ ?>
            <button type="button" id="imprimir" class="btn btn-success" onclick="printDiv()">Imprimir</button>
        <?php }?>
            <input type="hidden" name="id_ruta" id="id_ruta" value="<?= $id_ruta ?>"/>
            <input type="hidden" id="mapa1" value="<?= $coordenadas ?>"/>
            <input type="hidden" id="estado1" value="<?= $estado1 ?>"/>
            <input type="hidden" id="lugares1" value="<?= $lugares ?>"/>
        </div>
	</div>
	<hr>

	<div class="row" id="mapa2">
        <link rel="stylesheet" href="<?= css; ?>/leaflet.css">
        <link rel="stylesheet" href="<?= css; ?>/leaflet-routing-machine.css">
        <link rel="stylesheet" href="<?= css; ?>/site.css">
		<div  class="col-xs-12 col-sm-10">
            <div id="map" class="map col-sm-12 embed-responsive embed-responsive-16by9"></div>
		</div>
        <script src="<?= js; ?>/leaflet.js"></script>
        <script src="<?= js; ?>/leaflet-routing-machine.js"></script>
        <script src="<?= js; ?>/Leaflet.Icon.Glyph.js"></script>
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
    var cities = L.layerGroup();

    var map = L.map('map', {
        zoom: 15,
        layers: [cities]
    });

    var coord = $('#mapa1').val();
    var estad = $('#estado1').val();
    var lugar = $('#lugares1').val();
    lugar = lugar.split('*');
    lugar.shift();
    console.log(lugar);
    estad = estad.split('*');
    estad.shift();
    //console.log(coord);
    var porciones = coord.split('*');

    var waypoints1 = new Array();
    for (var i=1; porciones.length > i; i++) {
        var parte = porciones[i].split(',');
        waypoints1.push(L.latLng(parte[0], parte[1]));
    }

    //console.log(porciones);
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}{r}.png').addTo(map);

    var LeafIcon = L.Icon.extend({
        options: {
            iconAnchor:   [15, 40],
            popupAnchor:  [-3, -76]
        }
    });
    var greenIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/green.png' ?>'}),
        redIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/red.png' ?>'}),
        blueIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/blue.png' ?>'});

console.log(waypoints1);
    ar = L.Routing.control({
        waypoints: waypoints1,
        altLineOptions: {
            styles: [
                {color: 'black', opacity: 0.15, weight: 9},
                {color: 'white', opacity: 0.8, weight: 6},
                {color: 'blue', opacity: 0.5, weight: 2}
            ]
        },
        routeWhileDragging: true,
        createMarker: function(i, waypoints1, n) {
            if(estad[i]==1){
                return L.marker( waypoints1.latLng, {icon: greenIcon }).bindPopup(lugar[i]).openPopup(map);
            }if(estad[i]==2){
                return L.marker( waypoints1.latLng, {icon: redIcon });
            }if(estad[i]==3){
                return L.marker( waypoints1.latLng, {icon: blueIcon });
            }
        }
    });

    var waypoints = new Array();
    function ver(){
        for(var i=0; ar.getWaypoints().length>i;i++){
            console.log(ar.getWaypoints()[i].latLng);
        }
        console.log(waypoints);
    }
    function printDiv() {
        var objeto=document.getElementById('mapa2');
        //obtenemos el objeto a imprimir
        var ventana=window.open('','_blank');  //abrimos una ventana vacía nueva
        ventana.document.write(objeto.innerHTML);  //imprimimos el HTML del objeto en la nueva ventana
        ventana.document.close();  //cerramos el documento
        ventana.print();  //imprimimos la ventana
        ventana.close();  //cerramos la ventana
    }

$(function () {

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
                    return L.marker( waypoints.latLng, {icon: redIcon });
                }else{
                    return L.marker( waypoints.latLng, {icon: greenIcon });
                }
            }
        }).addTo(mymap);
	}
    $("#botonenviar").click(
        function() {     // Con esto establecemos la acción por defecto de nuestro botón de enviar.
        if(validaForm()){                               // Primero validará el formulario.
            //var wayt = new Array();
            var wayt='', lati, long;
            for(var i=0; ar.getWaypoints().length>i;i++){
                //wayt.push(ar.getWaypoints()[i].latLng);
                lati = ar.getWaypoints()[i].latLng.lat;
                long = ar.getWaypoints()[i].latLng.lng;
                wayt = wayt + '*' + lati + ',' + long;
            }
            //var way = JSON.stringify(wayt);
            console.log(wayt);
            var aa = $('#id_ruta').val();

            $.ajax({ //datos que se envian a traves de ajax
                type:  'post', //método de envio
                dataType: 'json',
                url:   '?/control/guardar', //archivo que recibe la peticion
                data:   {'wayt': wayt, 'id_ruta':aa}
            }).done(function (ruta) {
                if (ruta) {
                    $.notify({
                        message: 'La ruta fue registrada satisfactoriamente.'
                    }, {
                        type: 'success'
                    });
                } else {
                    $('#loader').fadeOut(100);
                    $.notify({
                        message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente.'
                    }, {
                        type: 'danger'
                    });
                }
            }).fail(function () {
                $('#loader').fadeOut(100);
                $.notify({
                    message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente.'
                }, {
                    type: 'danger'
                });
            });
        }
    });
});
function validaForm(){
    // Campos de texto
    if($("#nombre").val() == ""){
        alert("El campo Nombre no puede estar vacío.");
        $("#nombre").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    return true;
}

</script>
<?php require_once show_template('footer-configured'); ?>