
<?php

//var_dump($clientes);
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

$id_cliente = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el empleado
$cliente = $db->select('z.*')->from('inv_clientes z')->where('z.id_cliente', $id_cliente)->fetch_first();
//var_dump($cliente);

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

// Obtiene los clientes
//$clientes = $db->select('*')->from('inv_clientes')->fetch();

//var_dump($t_clientes);
//var_dump($n_clientes);
//echo $empresa;
//obtener las rutas
$rutas = $db->select('*')->from('gps_rutas')->where('estado',1)->fetch();

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
        <b>Editar cliente <?= $_institution['empresa1'] ?></b>
    </h3>
</div>
<div class="panel-body">

    <div class="row">
        <div class="col-sm-9 hidden-xs">

        </div>
        <div class="col-xs-12 col-sm-3 text-right">
            <a href="?/clientes/listar" type="button" id="listar" class="btn btn-primary" >Listar</a>
        </div>
    </div>
    <hr/>
    <div>
        <table id="coord" class="hidden">
            <tbody >
            <?php foreach($rutas as $ruta){ ?>
                <tr><td><?= $ruta['nombre'] ?></td>
                    <td><?= $ruta['coordenadas'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <form method="post" id="cliente_form" action="?/clientes/guardar" class="form-horizontal" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombres" class="col-md-3 control-label">Codigo cliente:</label>
                    <label for="nombres" class="col-md-9" ><?= $id_cliente; ?></label>
                </div>
                <div class="form-group">
                    <label for="nombres" class="col-md-3 control-label">Cliente:</label>
                    <div class="col-md-9">
                        <input type="hidden" id="punto" value="<?php if($cliente['ubicacion']!=''){ echo $cliente['ubicacion'];}else{ echo '-16.503961,-68.162241';} ?>"/>
                        <input type="hidden" value="<?= $id_cliente; ?>" name="id_cliente22" id="id_cliente22" data-validation="required number">
                        <input type="text" value="<?= $cliente['cliente'] ?>" name="nombres" id="nombres" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-., " data-validation-length="max100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="nombres_factura" class="col-md-3 control-label">Nombres de factura:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['nombre_factura'] ?>" name="nombres_factura" id="nombres_factura" class="form-control" autocomplete="off" data-validation="required letternumber length" data-validation-allowing=" " data-validation-length="max100" data-validation-optional="true">
                    </div>
                </div>
                <div class="form-group">
                    <label for="ci" class="col-md-3 control-label">CI/NIT:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['nit'] ?>" name="ci" id="ci" class="form-control" autocomplete="off" data-validation="letternumber length" data-validation-allowing=" " data-validation-length="max100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccion" class="col-md-3 control-label">Dirección:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['direccion'] ?>" name="direccion" id="direccion" class="form-control" autocomplete="off" data-validation="letternumber length" data-validation-allowing=" ,:-/\#$" data-validation-length="max100" >
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono" class="col-md-3 control-label">Telefono:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['telefono'] ?>" name="telefono" id="telefono" class="form-control" autocomplete="off" data-validation="number length" data-validation-allowing=" " data-validation-length="max100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-md-3 control-label">Email:</label>
                    <div class="col-md-9">
                        <input type="email" value="<?= $cliente['email'] ?>" name="email" id="email" class="form-control" autocomplete="off" data-validation="length" data-validation-allowing=" " data-validation-length="max100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="descripcion" class="col-md-3 control-label">Descripción:</label>
                    <div class="col-md-9">
                        <input type="text" value="<?= $cliente['descripcion'] ?>" name="descripcion" id="descripcion" class="form-control" autocomplete="off" data-validation="alphanumeric length" data-validation-allowing="-+,() " data-validation-length="max100" >
                    </div>
                </div>
                <div class="form-group">
                    <label for="tipo" class="col-sm-3 control-label">Tipo:</label>
                    <div class="col-sm-9">
                        <select name="tipo" id="tipo" class="form-control">
                            <?php $tipos = $db->select('*')->from('inv_tipos_clientes')->fetch();
                            foreach ($tipos as $nro => $tipo) {
                                if($cliente['tipo']==$tipo['tipo_cliente']){ ?>
                                    <option value="<?= $tipo['tipo_cliente'] ?>" selected><?= $tipo['tipo_cliente'] ?></option>
                                    <?php }else{?>
                                    <option value="<?= $tipo['tipo_cliente'] ?>"><?= $tipo['tipo_cliente'] ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="atencion" class="col-md-3 control-label">Ubicación:</label>
                    <div class="col-md-9">
                        <input type="text" value="" placeholder='Longitud,Latitud Sin Espacios' name="atencion" id="atencion" class="form-control" autocomplete="off" data-validation-allowing=" ,.-" data-validation="alphanumeric length required" data-validation-length="max100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="telefono" class="col-md-3 control-label">Imagen:</label>
                    <div class="col-md-6 card" >
                        <input type="file" class="form-control" name="imagen" id="imagen" accept="image/jpeg">
                    </div>
                    <div class="col-md-3 card" >
                        <img src="<?= ($cliente['imagen'] == '') ? imgs . '/image.jpg' : files . '/tiendas/' . $cliente['imagen']; ?>"  class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="100%">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="button" id="botonenviar" class="btn btn-primary">
                            <span class="glyphicon glyphicon-floppy-disk"></span>
                            <span>Guardar</span>
                        </button>
                        <button type="reset" class="btn btn-default">
                            <span class="glyphicon glyphicon-refresh"></span>
                            <span>Restablecer</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
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
var lime1Icon = new LeafIcon({iconUrl: '<?= files .'/puntero/lime1.png' ?>'}),
    lime2Icon = new LeafIcon({iconUrl: '<?= files .'/puntero/lime2.png' ?>'}),
    lime3Icon = new LeafIcon({iconUrl: '<?= files .'/puntero/lime3.png' ?>'}),
    blueIcon = new LeafIcon({iconUrl: '<?= files .'/puntero/blue.png' ?>'});

window.LRM = {
    apiToken: 'pk.eyJ1IjoibGllZG1hbiIsImEiOiJjamR3dW5zODgwNXN3MndqcmFiODdraTlvIn0.g_YeCZxrdh3vkzrsNN-Diw'
};

//    console.log(nomb);

var waypoints1 = new Array();

var punto = $('#punto').val();
var punto2 = punto.split(',');
console.log(punto);

var centerPoint = [punto2[0], punto2[1]];


// Create leaflet map.
var map = L.map('map').setView(centerPoint, 17);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    minZoom: 10,
	maxZoom: 19,
}).addTo(map);


L.marker(centerPoint, {icon: lime1Icon}).bindPopup("antiguo").addTo(map);


// Create custom measere tools instances.
var measure = L.measureBase(map, {});
//measure.circleBaseTool.startMeasure()

function afterRender(result) {
    return result;
}

function afterExport(result) {
    return result;
}

$(function () {
    $.validate({
        modules: 'basic,date,file'
    });

    $c1 = 1;

    $("#coord tbody tr").each(function (i) {
        var rutas = $.trim($(this).find("td").text());
        $rutas1 = new Array();
        var ruta = rutas.split('*');
        for (var i=1; ruta.length > i; i++) {
            var parte1 = ruta[i].split(',');
            $rutas1.push([parte1[0],parte1[1]]);
        }

        L.polygon($rutas1).addTo(map).bindPopup(ruta[0]);

    });




    measure.markerBaseTool.startMeasure();

    $("#botonenviar").click(
        function() {
            if(validaForm()){
                /*if(typeof measure.markerBaseTool.measureLayer.dragging == 'undefined'){
                    var wayt = '';
                }else{
                    var lat = measure.markerBaseTool.measureLayer._latlng.lat;
                    var lng = measure.markerBaseTool.measureLayer._latlng.lng;
                    var wayt = lat + ',' + lng;
                }*/

                //console.log(wayt);
                var nombre = $('#nombres').val();
                var nombrefactura = $('#nombres_factura').val();
                var ci = $("#ci").val();
                var email = $("#email").val();
                var id_cliente = $("#id_cliente22").val();

                var direccion = $("#direccion").val();
                var tipo = $("#tipo option:selected").text();
                var telefono = $("#telefono").val();

                var descripcion = $("#descripcion").val();

                if ($("#atencion").val() == '') {
                    var lat = measure.markerBaseTool.measureLayer._latlng.lat;
                    var lng = measure.markerBaseTool.measureLayer._latlng.lng;
                    $("#atencion").val() = lat + ',' + lng;
                }
                var atencion = $("#atencion").val();

                var formData = new FormData();
                var files = $('#imagen')[0].files[0];
                formData.append('imagen',files);
                formData.append('nombre',nombre);
                formData.append('nombre_factura',nombrefactura);
                formData.append('ci',ci);
                formData.append('email',email);
                formData.append('direccion',direccion);
                formData.append('telefono',telefono);
                formData.append('tipo',tipo);
                formData.append('descripcion',descripcion);
                formData.append('atencion',atencion);
                formData.append('id_cliente',id_cliente);

                $.ajax({ //datos que se envian a traves de ajax
                    type:  'post', //método de envio
                    dataType: 'json',
                    url:   '?/clientes/guardar', //archivo que recibe la peticion
                    data:  formData,
                    contentType: false,
                    processData: false
                }).done(function (ruta) {
                    console.log(ruta);
                    if (ruta.estado == 's') {
                        $('#cliente_form').trigger("reset");
                        $('#loader').fadeOut(100);
                        $.notify({
                            message: 'El cliente fue modificado satisfactoriamente.'
                        }, {
                            type: 'success'
                        });
                        setTimeout("location.href='?/clientes/listar'", 200);
                    } else if(ruta.estado == 'y'){
                        $('#loader').fadeOut(100);
                        $.notify({
                            message: 'Ocurrió un problema en el proceso, el cliente ya se encuentra registrado..........'
                        }, {
                            type: 'danger'
                        });
                    } else{
                        $('#loader').fadeOut(100);
                        $.notify({
                            message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos ..........'
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
    if($("#nombres").val() == ""){
        $("#nombres").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    if($("#ci").val() == ""){
        $("#ci").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    if($("#direccion").val() == ""){
        $("#direccion").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    if($("#telefono").val() == ""){
        $("#telefono").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    if($("#descripcion").val() == ""){
        $("#descripcion").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
        return false;
    }
    if ($("#atencion").val() == "") {
            if (typeof measure.markerBaseTool.measureLayer.dragging != 'undefined') {
                var lat = measure.markerBaseTool.measureLayer._latlng.lat;
                var lng = measure.markerBaseTool.measureLayer._latlng.lng;
                $("#atencion").val(lat + ',' + lng);
            }
            $("#atencion").focus(); // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
            return false;
        }
//    if($("#imagen").val() == ""){
//        $("#imagen").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
//        return false;
//    }
//    if(typeof measure.markerBaseTool.measureLayer.dragging == 'undefined'){
//        $.notify({
//            message: 'Debe seleccionar un punto en el mapa.'
//        }, {
//            type: 'danger'
//        });
//        return false;
//    }
    return true;
}

</script>
<?php require_once show_template('footer-configured'); ?>