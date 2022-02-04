
<?php

//var_dump($clientes);
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

// Obtiene los clientes
$clientes = $db->select('*, 1 as empresa')->from('inv_clientes')->fetch();
// $clientes = $db->select('a.*, GROUP_CONCAT(DISTINCT c.cargo SEPARATOR "|") as empresa')->from('inv_clientes a')->join('inv_egresos b','a.id_cliente = b.cliente_id')->join('sys_empleados c','b.empleado_id = c.id_empleado')->group_by('a.id_cliente')->limit(1000)->fetch();

//$ruta = $db->select('*')->from('gps_rutas')->where('id_ruta',$id_ruta)->fetch_first();
/* $polygon = explode('*',$ruta['coordenadas']);
foreach ($polygon as $nro => $poly) {
    $aux = explode(',',$poly);
    $aux2 = (round($aux[0],6)-0.000044).','.(round($aux[1],6)+0.00003);
    $polygon[$nro] = str_replace(',', ' ', $aux2);
}
$polygon[0] = str_replace(',', ' ', $polygon[$nro]);
$pointLocation = new pointLocation();
 */



$t_clientes = '';
$n_clientes = '';
$empresa = '';
foreach($clientes as $cliente){
    $aux2 = explode(',',$cliente['ubicacion']);
    $aux3 = $aux2[0] + 0.00005;
    $aux4 = $aux2[1] - 0.00003;

    //corrdenadas
   // $point = $aux3.' '.$aux4;
    // se envia a objeto apra verificar si esta dentro del poligono
    //$punto = $pointLocation->pointInPolygon($point, $polygon);
   // if($punto == 'dentro'){

        $t_clientes = $t_clientes.'*'.$aux3.','.$aux4;
        $n_clientes = $n_clientes.'*'.$cliente['cliente'];

        $aux = explode('|',$cliente['empresa']);

        $cliente['empresa'];
        if(count($aux)==1){
            if($aux[0]==2){
                $empresa = $empresa.'*'.'1';
            }elseif($aux[0]==1){
                $empresa = $empresa.'*'.'2';
            }else{
                $empresa = $empresa.'*'.'0';
            }
        }else{
            $empresa = $empresa.'*'.'3';
        }
    //}
}
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
		<b>Lista de todas las rutas <?= $_institution['empresa1'] ?></b>
	</h3>
</div>
<div class="panel-body">

	<div class="row">
        <div class="col-sm-9 hidden-xs">
            <form method="post" class="form-horizontal" >
                <div class="row">
                    <div class="form-group">
                        <label for="nombre" class="col-md-3 control-label">Nombre de la ruta:</label>
                        <div class="col-md-6">
                            <input type="text" name="nombre" id="nombre" class="form-control"  autocomplete="off" />
                            <input type="hidden" name="cliente" id="cliente" value="<?= $t_clientes ?>"/>
                            <input type="hidden" name="empresa" id="empresa" value="<?= $empresa ?>"/>
                            <input type="hidden" name="nombre2" id="nombre2" value="<?= $n_clientes ?>"/>
                        </div>
                        <div class="col-md-3" >
                            <input type="button" value="Guardar ruta" id="botonenviar" class="btn btn-info  "/>
                        </div>
                    </div>

                </div>
            </form>
        </div>
        <div class="col-xs-12 col-sm-3 text-right">
            <a href="?/control/listar" type="button" id="listar" class="btn btn-primary" >Listar</a>
            
        </div>
	</div>

    <div>
        <table id="coord" class="hidden">
            <tbody >
                <?php foreach($rutas as $ruta){ ?>
                <tr><td><?= $ruta['color'] ?>%</td>
                    <td><?= $ruta['nombre'] ?></td>
                    <td><?= $ruta['coordenadas'] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
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
    var coord = $('#cliente').val();
    var emp = $('#empresa').val();
    var nomb = $('#nombre2').val();
//    console.log(nomb);
    var porciones = coord.split('*');
    var porciones2 = emp.split('*');
    var porciones3 = nomb.split('*');
    porciones.shift();
    porciones2.shift();
    porciones3.shift();
//    console.log(nomb);

    var waypoints1 = new Array();

    var centerPoint = [-16.507354, -68.162908];


    // Create leaflet map.
    var map = L.map('map').setView(centerPoint, 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: 10,
	    maxZoom: 19,
    }).addTo(map);
    
    var myRenderer = L.canvas({ padding: 1 });
    
    for (var i=1; porciones.length > i; i++) {
        var parte = porciones[i].split(',');
        if(porciones2[i] == 1){
            var circleMarker = L.circleMarker(([parte[0], parte[1]]), {
               renderer: myRenderer,
               radius: 8,
                fillColor: '#3352FF',
                fillOpacity: 1,
                color: '#fff',
                weight: 3
            }).bindPopup(" "+porciones3[i]).addTo(map);
        }else if(porciones2[i] == 2){
            var circleMarker = L.circleMarker(([parte[0], parte[1]]), {
               renderer: myRenderer,
               radius: 8,
                fillColor: '#3352FF',
                fillOpacity: 1,
                color: '#fff',
                weight: 3
            }).bindPopup(" "+porciones3[i]).addTo(map);
        }else if(porciones2[i] == 3){
            var circleMarker = L.circleMarker(([parte[0], parte[1]]), {
               renderer: myRenderer,
               radius: 8,
                fillColor: '#3352FF',
                fillOpacity: 1,
                color: '#fff',
                weight: 3
            }).bindPopup(" "+porciones3[i]).addTo(map);
        }else{
            var circleMarker = L.circleMarker(([parte[0], parte[1]]), {
               renderer: myRenderer,
               radius: 8,
                fillColor: '#3352FF',
                fillOpacity: 1,
                color: '#fff',
                weight: 3
            }).bindPopup("NINGUNO <br>"+porciones3[i]).addTo(map);
        }

        //L.marker([parte[0], parte[1]]).addTo(map);
    }



    // Create custom measere tools instances.
    var measure = L.measureBase(map, {});
    //measure.circleBaseTool.startMeasure()

    function afterRender(result) {
        return result;
    }

    function afterExport(result) {
        return result;
    }



    function generarLetra(){
        var letras = ["a","b","c","d","e","f","0","1","2","3","4","5","6","7","8","9"];
        var numero = (Math.random()*15).toFixed(0);
        return letras[numero];
    }
        
    function colorHEX(){
        var coolor = "";
        for(var i=0;i<6;i++){
            coolor = coolor + generarLetra() ;
        }
        return "#" + coolor;
    }

    $(function () {
        $c1 = 1;

        $("#coord tbody tr").each(function (i) {
            var rutas = $.trim($(this).find("td").text());
            //console.log(rutas);
            $rutas1 = new Array();
            var ruta = rutas.split('*');
            for (var i=1; ruta.length > i; i++) {
                var parte1 = ruta[i].split(',');
                $rutas1.push([parte1[0],parte1[1]]);
            }
            //var aux = ruta[0].split('%');             
            var aux = [colorHEX()]; 
            if(aux[0] != ''){
                L.polygon($rutas1, {
                fillColor: aux[0],
                color: aux[0], stroke: true, weight: 3, fillOpacity: 0}).addTo(map).bindPopup(aux[1]);
            }else{
                L.polygon($rutas1, {
                fillColor: '#3352FF',
                color: '#3352FF', stroke: true, weight: 3, fillOpacity: 0}).addTo(map).bindPopup(aux[1]);
            }
        });
// $('.leaflet-interactive').css('stroke', color);
//  $('.leaflet-interactive').css('fill', color);



        measure.polygonBaseTool.startMeasure();

        $("#botonenviar").click(

            function() {
                if(validaForm()){

                    //console.log(measure.polygonBaseTool.measureLayer._latlngs);
                                     // Primero validará el formulario.
                    //var wayt = new Array();
                    var wayt='', lati, long;
                    for(var i=0; measure.polygonBaseTool.measureLayer._latlngs[0].length>i;i++){
                        //wayt.push(ar.getWaypoints()[i].latLng);
                        lati = measure.polygonBaseTool.measureLayer._latlngs[0][i].lat;
                        long = measure.polygonBaseTool.measureLayer._latlngs[0][i].lng;
                        wayt = wayt + '*' + lati + ',' + long;
                    }
                    //var way = JSON.stringify(wayt);
                    //console.log(wayt);
                    var aa = $('#nombre').val();
                                                            
                     if(wayt != ""){
                    
                    $.ajax({ //datos que se envian a traves de ajax
                        type:  'post', //método de envio
                        dataType: 'json',
                        url:   '?/control/guardar/1', //archivo que recibe la peticion
                        data:   {'wayt': wayt, 'nombre':aa}
                    }).done(function (ruta) {
                        if (ruta) {
                            $.notify({
                                message: 'La ruta fue registrada satisfactoriamente.'
                            }, {
                                type: 'success'
                            });
                            setTimeout("location.href='?/control/listar'", 0);
                        } else {
                            $('#loader').fadeOut(100);
                            $.notify({
                                message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente..........'
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
                    
                    
                    }else{
                        $.notify({
                            message: 'Debe trazar un area obligatoriamente.'
                        }, {
                            type: 'danger'
                        });
                        
                    }
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