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

//obtener la ruta
$ruta = $db->select('a.*, b.username')->from('gps_seguimientos a')->join('sys_users b', 'a.user_id = b.id_user')->where('b.persona_id',$empleado)->where('a.fecha_seguimiento>=',$fecha_inicial)->where('a.fecha_seguimiento<=',$fecha_final)->fetch_first();
//var_dump($ruta);
$coordenadas = $ruta['coordenadas'];
$horas = $ruta['hora_seguimiento'];
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
            <b>Lista de todas las preventas</b>
        </h3>
    </div>
    <div class="panel-body">
        <?php if ($permiso_cambiar || $permiso_imprimir) { ?>
            <div class="row">
                <div class="col-sm-8 hidden-xs">
                    <div class="text-label">Para realizar una acción hacer clic en los siguientes botones: </div>
                </div>
                <form action="">
                    <input type="hidden" id="puntos" value="<?= $coordenadas ?>"/>
                    <input type="hidden" id="horas" value="<?= $horas ?>"/>
                </form>
                <div class="col-xs-12 col-sm-4 text-right">
                    <a href="?/vendedor/listar" class="btn btn-success">
                        <span class="glyphicon glyphicon-list-alt"></span>
                        <span class="hidden-xs">Listar</span>
                    </a>
                    <?php if ($permiso_cambiar) { ?>
                        <button class="btn btn-default" data-cambiar="true">
                            <span class="glyphicon glyphicon-calendar"></span>
                            <span class="hidden-xs">Cambiar</span>
                        </button>
                    <?php } ?>
                </div>
            </div>
            <hr>
        <?php } ?>
        <?php if($ruta){ ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-6">
                            <h4 class="lead">Ruta del preventista <?= $ruta['username'] ?></h4>
                        </div>
                    </div>
                    <hr>
                    <div id="map" class="embed-responsive embed-responsive-16by9"></div>
                </div>
            </div>
    <?php }else{ ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>No existen datos.</p>
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
    <script src="<?= js; ?>/Leaflet.Icon.Glyph.js"></script>
    <script>
    $(function () {


        <?php if ($permiso_cambiar) { ?>
        var formato = $('[data-formato]').attr('data-formato');
        var mascara = $('[data-mascara]').attr('data-mascara');
        var gestion = $('[data-gestion]').attr('data-gestion');
        var $inicial_fecha = $('#inicial_fecha');

        $.validate({
            form: '#form_fecha',
            modules: 'date',
            onSuccess: function () {
                var inicial_fecha = $.trim($('#inicial_fecha').val());
                var empleado = $.trim($('#empleado').val());

                var vacio = gestion.replace(new RegExp('9', 'g'), '0');

                inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
                inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
                vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
                vacio = vacio.replace(new RegExp('/', 'g'), '-');
                inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((inicial_fecha != '') ? ('/' + vacio) : '');

                window.location = '?/vendedor/control/' + empleado + inicial_fecha;
            }
        });

        //$inicial_fecha.mask(mascara).datetimepicker({
        $inicial_fecha.datetimepicker({
            format: formato
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
        <?php if($ruta){ ?>
        var $empleado = $('#empleado');

        var coord = $('#puntos').val();
        var coordnom = $('#horas').val();
        var porciones = coordnom.split('*');
        var porciones2 = coord.split('*');

//        porciones.shift();
//        porciones2.shift();

        console.log(porciones);
        console.log(porciones2);

        var waypoints1 = new Array();
        var punt = new Array();
        for (var i=1; porciones2.length > i; i++) {
            var parte = porciones2[i].split(',');
            waypoints1.push(L.latLng([parte[0], parte[1]]));
            punt.push([parte[0], parte[1]]);
        }

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
        var map = L.map('map', { scrollWheelZoom: false }),
            waypoints = waypoints1;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?access_token=' + LRM.apiToken, {
        }).addTo(map);


        var control = L.Routing.control({
            addWaypoints : false,
            router: L.routing.mapbox(LRM.apiToken),
            plan: L.Routing.plan(waypoints, {
                createMarker: function(i, wp) {
                    if(i === 1){
                        return L.marker( wp.latLng, {icon: redIcon }).bindPopup('Hora: '+porciones[i+1]).openPopup();
                    }
                    else if(i === wp){
                        return L.marker( wp.latLng, {icon: blueIcon }).bindPopup('Hora: '+porciones[i+1]).openPopup();
                    }else{
                        return L.marker( wp.latLng, {icon: greenIcon }).bindPopup('Hora: '+porciones[i+1]).openPopup();
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
        <?php } ?>



//        var latitudes = new Array(), longitudes = new Array(), estados = new Array(), nombres = new Array();
//
//        var sw = $("#table tbody tr").length;
//        console.log(sw);
//        $('.coordenadas').each(function (i) {
//            var latitud = $.trim($(this).find('.latitud').text());
//            var longitud = $.trim($(this).find('.longitud').text());
//            var estado = $.trim($(this).find('.estadoo').text());
//            var nombre = $.trim($(this).find('.nombre').text());
//            if (latitud != '0.0' && longitud != '0.0') {
//                latitudes.push(latitud);
//                longitudes.push(longitud);
//                estados.push(estado);
//                nombres.push(nombre);
//                if(sw === 1){
//                    latitudes.push(latitud);
//                    longitudes.push(longitud);
//                    estados.push(estado);
//                    nombres.push(nombre);
//                    sw = 2;
//                }
//            }
//        });

        //console.log(nombres);
    });
    </script>
<?php require_once show_template('footer-configured'); ?>