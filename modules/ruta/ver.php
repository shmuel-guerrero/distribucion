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
$rutas = $db->select('*')->from('gps_rutas')->where('empleado_id',$id_ruta)->fetch();

$area = '';
$dia = '';
foreach($rutas as $ruta){
    $area = $area.'/'.$ruta['coordenadas'];
    switch($ruta['dia']){case 0: $aux = "Domingo"; break; case 1: $aux = "Lunes"; break; case 2: $aux = "Martes"; break; case 3: $aux = "Miércoles"; break; case 4: $aux = "Jueves"; break; case 5: $aux = "Viernes"; break; case 6: $aux = "Sábado"; break;}
    $dia = $dia.'/'.$aux;
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

            <!--<a href="?/ruta/recorrido" type="button" id="listar" class="btn btn-primary" >Recorrido</a>-->
        <?php if($permiso_imprimir){ ?>
            <button type="button" id="imprimir" class="btn btn-success" onclick="printDiv()">Imprimir</button>
            <button type="button" id="imprimir" class="btn btn-info" onclick="manualPrint()">Imagen</button>
        <?php }?>
            <input type="hidden" name="id_ruta" id="id_ruta" value="<?= $id_ruta ?>"/>
            <input type="hidden" id="mapa1" value="<?= $area ?>"/>
            <input type="hidden" id="dias" value="<?= $dia ?>"/>
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
    <script src="<?= js; ?>/bundle2.js" ></script>


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
    var dias = $('#dias').val();
    var porciones2 = coord.split('/');
    porciones2.shift();
    var dia = dias.split('/');
    dia.shift();
//    console.log(porciones2);
//    console.log(dia);
    var porciones3 = porciones2[0].split('*');
    porciones3.shift();
console.log(porciones3);
    var parte3 = porciones3[0].split(',');
    window.LRM = {
        apiToken: 'pk.eyJ1IjoibGllZG1hbiIsImEiOiJjamR3dW5zODgwNXN3MndqcmFiODdraTlvIn0.g_YeCZxrdh3vkzrsNN-Diw'
    };
    var map = L.map('map').setView([parte3[0], parte3[1]], 15);

    var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?access_token=' + LRM.apiToken, {
    }).addTo(map);

    for(var j=0; porciones2.length > j; j++){
        var porciones = porciones2[j].split('*');

        var waypoints1 = new Array();
        for (var i=1; porciones.length > i; i++) {
            var parte = porciones[i].split(',');
            waypoints1.push(L.latLng([parte[0], parte[1]]));
        }

        L.polygon(waypoints1).addTo(map).bindPopup(dia[j]);
    }
    var printer = L.easyPrint({
        tileLayer: tiles,
        sizeModes: ['Current', 'A4Landscape', 'A4Portrait'],
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

</script>
<?php require_once show_template('footer-advanced'); ?>