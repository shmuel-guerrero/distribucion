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
$ruta = $db->select('*')->from('gps_rutas')->where('empleado_id',$id_ruta)->fetch_first();
$uno = explode('*',$ruta['coordenadas']);

//coordenadas
$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.empleado_id >= ', $id_ruta)->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->where('i.estadoe>',0)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();
$rutt = '*'.$uno[1];
$estado1 = '*3';
foreach($proformas as $proforma){
    $rutt = $rutt.'*'.str_replace(' ','',$proforma['coordenadas']);
    $estado1 = $estado1.'*'.$proforma['estadoe'];
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

            <a href="?/control/listar" type="button" id="listar" class="btn btn-primary" >Recorrido</a>
        <?php if($permiso_imprimir){ ?>
            <button type="button" id="imprimir" class="btn btn-success" onclick="printDiv()">Imprimir</button>
        <?php }?>
            <input type="hidden" name="id_ruta" id="id_ruta" value="<?= $id_ruta ?>"/>
            <input type="hidden" id="mapa1" value="<?= $rutt ?>"/>
            <input type="hidden" id="estado1" value="<?= $estado1 ?>"/>

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
    function handleError(e) {
        if (e.error.status === -1) {
            // HTTP error, show our error banner
            document.querySelector('#osrm-error').style.display = 'block';
            L.DomEvent.on(document.querySelector('#osrm-error-close'), 'click', function(e) {
                document.querySelector('#osrm-error').style.display = 'none';
                L.DomEvent.preventDefault(e);
            });
        }
    }

    var coord = $('#mapa1').val();
    var porciones = coord.split('*');
    var estado = $('#estado1').val();
    var estados = estado.split('*');
    estados.shift();

    var LeafIcon = L.Icon.extend({
        options: {
            iconSize: [25, 41],
            iconAnchor:  [12, 41],
            popupAnchor: [1, -34],
            shadowSize:  [41, 41],
// 		iconUrl: 'glyph-marker-icon.png',
// 		iconSize: [35, 45],
// 		iconAnchor:   [17, 42],
// 		popupAnchor: [1, -32],
// 		shadowAnchor: [10, 12],
// 		shadowSize: [36, 16],
// 		bgPos: (Point)
            className: '',
            prefix: '',
            glyph: 'home',
            glyphColor: 'white',
            glyphSize: '11px',	// in CSS units
            glyphAnchor: [0, -7]
        }
    });
    var greenIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/green.png' ?>'}),
        redIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/red.png' ?>'}),
        blueIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/blue.png' ?>'});

    var waypoints1 = new Array();
    for (var i=1; porciones.length > i; i++) {
        var parte = porciones[i].split(',');
        waypoints1.push(L.latLng([parte[0], parte[1]]));
    }

    window.LRM = {
        apiToken: 'pk.eyJ1IjoibGllZG1hbiIsImEiOiJjamR3dW5zODgwNXN3MndqcmFiODdraTlvIn0.g_YeCZxrdh3vkzrsNN-Diw'
    };
    var map = L.map('map', { scrollWheelZoom: false }),
        waypoints = waypoints1;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?access_token=' + LRM.apiToken, {
    }).addTo(map);

    var control = L.Routing.control({
        router: L.routing.mapbox(LRM.apiToken),
        plan: L.Routing.plan(waypoints, {
            createMarker: function(i, wp) {
                if(estados[i]==1){
                    return L.marker( wp.latLng, {icon: redIcon });
                }else{
                    return L.marker( wp.latLng, {icon: greenIcon });
                }
            }
        }),
        routeWhileDragging: true,
        routeDragTimeout: 250,
        showAlternatives: true,
        altLineOptions: {
            styles: [
                {color: 'black', opacity: 0.15, weight: 9},
                {color: 'white', opacity: 0.8, weight: 6},
                {color: 'blue', opacity: 0.5, weight: 2}
            ]
        }
    })
        .addTo(map)
        .on('routingerror', function(e) {
            try {
                map.getCenter();
            } catch (e) {
                map.fitBounds(L.latLngBounds(waypoints));
            }

            handleError(e);
        });

    L.Routing.errorControl(control).addTo(map);

    function printDiv() {
        var objeto=document.getElementById('mapa2');

        //obtenemos el objeto a imprimir
        var ventana=window.open('','_blank');  //abrimos una ventana vacía nueva
        ventana.document.write(objeto.innerHTML);  //imprimimos el HTML del objeto en la nueva ventana
        ventana.document.close();  //cerramos el documento
        ventana.print();  //imprimimos la ventana
        ventana.close();  //cerramos la ventana
    }
</script>
<?php require_once show_template('footer-advanced'); ?>