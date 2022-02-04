<?php
require config . '/poligono.php';
$id_ruta = (sizeof($params) > 0) ? $params[0] : 0;

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
$ruta = $db->select('*')->from('gps_rutas')->where('id_ruta',$id_ruta)->fetch_first();
$rutas2 = $db->query("SELECT a.*, COUNT(id_egreso) as contador_no_ventas  FROM gps_rutas a LEFT JOIN (SELECT id_egreso, ruta_id FROM inv_egresos WHERE ruta_id > 0 GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.id_ruta WHERE a.id_ruta = '$id_ruta' GROUP BY a.id_ruta ORDER BY fecha")->fetch_first();
$rutas1 = $db->query("SELECT a.*, COUNT(id_egreso) as contador_ventas  FROM gps_rutas a LEFT JOIN (SELECT id_egreso, ruta_id FROM inv_egresos WHERE ruta_id > 0 AND tipo = 'Venta' GROUP BY cliente_id, ruta_id) b ON b.ruta_id = a.id_ruta WHERE a.id_ruta = '$id_ruta' GROUP BY a.id_ruta ORDER BY fecha")->fetch_first();

$polygon = explode('*',$ruta['coordenadas']);
foreach ($polygon as $nro => $poly) {
    $aux = explode(',',$poly);
    $aux2 = (round($aux[0],6)-0.000044).','.(round($aux[1],6)+0.00003);
    $polygon[$nro] = str_replace(',', ' ', $aux2);
}
$polygon[0] = str_replace(',', ' ', $polygon[$nro]);
$pointLocation = new pointLocation();

$total = 0;
// Obtiene los clientes
$clientes = $db->select('*')->from('inv_clientes')->fetch();
$t_clientes = '';
foreach($clientes as $cliente){
    $aux2 = explode(',',$cliente['ubicacion']);
    $aux3 = $aux2[0] + 0.00005;
    $aux4 = $aux2[1] - 0.00003;
    $t_clientes = $t_clientes.'*'.$aux3.','.$aux4;
    $point = $aux3.' '.$aux4;
    $punto = $pointLocation->pointInPolygon($point, $polygon);
    if($punto == 'dentro'){
        $total = $total + 1;
    }
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
$permiso_cambiar = false;

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
			<div class="text-label">Para realizar una acción hacer clic en los siguientes botones: </div>
		</div>
        <div class="col-sm-4 col-xs-12">

            <a href="?/control/listar" type="button" id="listar" class="btn btn-primary" >Listar</a>
        <?php if($permiso_imprimir){ ?>
            <button type="button" id="imprimir" class="btn btn-success" onclick="printDiv()">Imprimir</button>
            <button type="button" class="btn btn-info" onclick="manualPrint()">Imagen</button>
        <?php }?>
            <input type="hidden" name="id_ruta" id="id_ruta" value="<?= $id_ruta ?>"/>
            <input type="hidden" id="mapa1" value="<?= $ruta['coordenadas'] ?>"/>
            <input type="hidden" id="nomb1" value="<?= $ruta['nombre'] ?>"/>
            <input type="hidden" id="color" value="<?= $ruta['color'] ?>"/>
            <input type="hidden" name="cliente" id="cliente" value="<?= $t_clientes ?>"/>
        <?php if($permiso_cambiar){ ?>
            <input type="button" value="Editar ruta" id="botonenviar" class="btn btn-info"/>
        <?php } ?>
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
		<div  class="col-xs-12 col-sm-2">
            <p class="lead margin-none">
    			<b>Clientes:</b>
    		</p>
    		<br>
    		<p class="lead margin-none">
    			<b>Activos(Ventas):</b><br>
    			<u><?= $rutas1['contador_ventas']; ?></u>
    			<span>clientes</span>
    		</p>
    		<br>
    		<p class="lead margin-none">
    			<b>Inactivos(No ventas):</b><br>
    			<u><?= $rutas2['contador_no_ventas']-$rutas1['contador_ventas']; ?></u>
    			<span>clientes</span>
    		</p>
    		<br>
    		<p class="lead margin-none">
    			<b>No visitados:</b><br>
    			<u><?php if($total - $rutas2['contador_no_ventas'] > 0){echo $total - $rutas2['contador_no_ventas'];}else{echo 0;} ?></u>
    			<span>clientes</span>
    		</p>
    		<br>
    		<p class="lead margin-none">
    			<b>Total:</b><br>
    			<u><?= $total; ?></u>
    			<span>clientes</span>
    		</p>
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
<script src="<?= js; ?>/bundle2.js"></script>

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
    var cliente = $('#cliente').val();
    var porciones1 = cliente.split('*');



    var coord = $('#mapa1').val();
    var coordnom = $('#nomb1').val();
    var porciones = coord.split('*');


    var waypoints1 = new Array();
    var punt = new Array();
    for (var i=1; porciones.length > i; i++) {
        var parte = porciones[i].split(',');
        waypoints1.push(L.latLng([parte[0], parte[1]]));
        punt.push([parte[0], parte[1]]);
    }



    window.LRM = {
        apiToken: 'pk.eyJ1IjoibGllZG1hbiIsImEiOiJjamR3dW5zODgwNXN3MndqcmFiODdraTlvIn0.g_YeCZxrdh3vkzrsNN-Diw'
    };
    var map = L.map('map').setView([parte[0], parte[1]], 15);

    var titles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?access_token=' + LRM.apiToken, {
    }).addTo(map);

    //var mymap = L.map('mapid').setView([51.505, -0.09], 13);
     var myRenderer = L.canvas({ padding: 1 });
        
        for (var i=1; porciones1.length > i; i++) {
            var parte = porciones1[i].split(',');
            var circleMarker = L.circleMarker(([parte[0], parte[1]]), {
               renderer: myRenderer,
               radius: 8,
                fillColor: '#3352FF',
                fillOpacity: 1,
                color: '#fff',
                weight: 3
            }).addTo(map);
            // L.marker([parte[0], parte[1]]).addTo(map);
        }

    L.polygon(punt).addTo(map).bindPopup(coordnom);
    
    var printer = L.easyPrint({
        tileLayer: titles,
        sizeModes: ['Current'],
        filename: 'myMap',
        exportOnly: true,
        hideControlContainer: true
    }).addTo(map);

    function manualPrint () {
        printer.printMap('CurrentSize', 'MyManualPrint')
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
//Asigna color al Area
 var color= $('#color').val();

 $('.leaflet-interactive').css('stroke', color);
 $('.leaflet-interactive').css('fill', color);

</script>
<?php require_once show_template('footer-configured'); ?>