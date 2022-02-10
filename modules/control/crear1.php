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
$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->where('i.estadoe>',0)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();

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
<link rel="stylesheet" href="<?= css; ?>/site.css">
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

	<div class="row">
		<div class="col-sm-9 hidden-xs">
			<div class="text-label">Para realizar acciones clic en el siguiente botón(es): </div>
		</div>
		<div class="col-xs-12 col-sm-3 text-right">
            <form method="post">
                <div class="row">
                    <div class="col-sm-8">
                        <div class="form-group">
                            <div  class="col-sm-6">
                                <label for="nombre">Nombre de la ruta:</label>
                            </div>
                            <div class="col-sm-6">
                                <input type="text" name="nombre" id="nombre" class="form-control" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <input type="button" value="Guardar ruta" id="botonenviar" class="btn btn-info form-control"/>
                        </div>
                    </div>
                </div>
            </form>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-sm-12">
            <div id="map" class="map col-sm-12 embed-responsive embed-responsive-16by9"></div>
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
<script src="<?= js; ?>/leaflet.js"></script>
<script src="<?= js; ?>/leaflet-routing-machine.js"></script>
<script src="<?= js; ?>/Leaflet.Icon.Glyph.js"></script>
<script src="<?= js; ?>/Leaflet.Editable.js"></script>
<script src="<?= js; ?>/leaflet_measure.js"></script>
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

    window.LRM = {
        apiToken: 'pk.eyJ1IjoibGllZG1hbiIsImEiOiJjamR3dW5zODgwNXN3MndqcmFiODdraTlvIn0.g_YeCZxrdh3vkzrsNN-Diw'
    };
    var map = L.map('map', {
            scrollWheelZoom: false,
            editable: true,
            printable: true,
            downloadable: true}),
        waypoints = [
            L.latLng(['-16.506578', '-68.162785']),
            L.latLng(['-16.509929', '-68.159171'])
        ];

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?access_token=' + LRM.apiToken, {
    }).addTo(map);

    var control = L.Routing.control({
        router: L.routing.mapbox(LRM.apiToken),
        plan: L.Routing.plan(waypoints, {
            createMarker: function(i, wp) {
                return L.marker(wp.latLng, {
                    draggable: true,
                    icon: L.icon.glyph({ glyph: String.fromCharCode(65 + i) })
                });
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

    var waypoints = new Array();
    function ver(){
        for(var i=0; ar.getWaypoints().length>i;i++){
            console.log(ar.getWaypoints()[i].latLng);
        }
        console.log(waypoints);
    }

$(function () {
    $("#botonenviar").click(
        function() {     // Con esto establecemos la acción por defecto de nuestro botón de enviar.
        if(validaForm()){                               // Primero validará el formulario.
            //var wayt = new Array();
            var wayt='', lati, long;
            for(var i=0; control.getWaypoints().length>i;i++){
                //wayt.push(ar.getWaypoints()[i].latLng);
                lati = control.getWaypoints()[i].latLng.lat;
                long = control.getWaypoints()[i].latLng.lng;
                wayt = wayt + '*' + lati + ',' + long;
            }
            //var way = JSON.stringify(wayt);
            console.log(wayt);
            var aa = $('#nombre').val();

            $.ajax({ //datos que se envian a traves de ajax
                type:  'post', //método de envio
                dataType: 'json',
                url:   '?/control/guardar', //archivo que recibe la peticion
                data:   {'wayt': wayt, 'nombre':aa}
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