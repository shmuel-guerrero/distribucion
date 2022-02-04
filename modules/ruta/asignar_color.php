<?php

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

// Obtiene los clientes
$clientes = $db->select('*')->from('inv_clientes')->fetch();
$t_clientes = '';
foreach($clientes as $cliente){
    $aux2 = explode(',',$cliente['ubicacion']);
    $aux3 = $aux2[0] + 0.00005;
    $aux4 = $aux2[1] - 0.00003;
    $t_clientes = $t_clientes.'*'.$aux3.','.$aux4;
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
<link href="<?= css; ?>/bootstrap-colorselector.css" rel="stylesheet" />

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
        	<div class="col-sm-12 col-xs-12">

                <a href="?/control/listar" type="button" id="listar" class="btn btn-primary" >Listar</a>
                <input type="hidden" name="id_ruta" id="id_ruta" value="<?= $id_ruta ?>"/>
                <?php if($permiso_cambiar){ ?>
                    <input type="button" value="Editar ruta" id="botonenviar" class="btn btn-info"/>
                <?php } ?>
            </div>
            <div class="col-sm-8 hidden-xs">
                <form method="post" class="form-horizontal"  action="?/ruta/guardar_color">
                    <div class="row">
                        <div class="form-group">
                            
                            <label for="nombre" class="col-md-3 control-label">Nombre de la ruta:</label>
                            <div class="col-md-6">
                                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= $ruta['nombre'] ?>" readonly />
                                <input type="hidden" name="id_ruta" id="id_ruta" value="<?= $ruta['id_ruta'] ?>" />
                                <input type="hidden" name="cliente" id="cliente" value="<?= $t_clientes ?>"/>
                                <input type="hidden" name="empresa" id="empresa" value="<?= $empresa ?>"/>
                            </div>
                         </div> 
                         <div class="form-group">
                            <label for="color" class="col-md-3 control-label">Asignar color:</label>
                            <div class="col-md-6">

                            <input type="hidden" value="<?= $ruta['color']; ?>" id="color_g">

	                            <select id="colorselector">
								    <option value="1" data-color="#A0522D" ></option>
								    <option value="2" data-color="#CD5C5C" ></option>
								    <option value="3" data-color="#FF4500"></option>
								    <option value="4" data-color="#DC143C"></option>
								    <option value="5" data-color="#4c1130"></option>
								    <option value="6" data-color="#e6b8af"></option>
								    <option value="7" data-color="#FFCC00"></option>
								    <option value="8" data-color="#7f6000"></option>
								    <option value="9" data-color="#00ffff"></option>
								    <option value="10" data-color="#ff00ff"></option>
								    <option value="11" data-color="#783f04"></option>
								</select>

								<input type="hidden" id="colorColor" name="color" />
								 
                            </div>

                            <div class="col-md-12" >
	                            <br><br><p align="center">
	                                <input type="submit" value="Guardar"  class="btn btn-info  "/>
	                            </p>
                            </div>
                        
                        </div> 

                    </div>
                </form>
            </div>
            
        </div>

      </div>


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
    <script src="<?= js; ?>/bootstrap-colorselector.js"></script>

    <script>
        function validaForm(){
            // Campos de texto
            if($("#nombre").val() == ""){
                alert("El campo Nombre no puede estar vacío.");
                $("#nombre").focus();       // Esta función coloca el foco de escritura del usuario en el campo Nombre directamente.
                return false;
            }
            return true;
        }
	    $('#colorselector').colorselector({
	          callback: function (value, color, title) {
	              $("#colorColor").val(color);
	          }
	    });


	    var color_seleccionado = $('#color_g').val();
	    if(color_seleccionado!='')
	    {
			$('.btn-colorselector').css('background',color_seleccionado);
	    }
    

    </script>
<?php require_once show_template('footer-configured'); ?>