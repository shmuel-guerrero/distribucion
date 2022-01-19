<?php


// Obtiene los clientes
//$clientes = $db->query("select * from ((select nombre_cliente, nit_ci from inv_egresos) union (select nombre_cliente, nit_ci from inv_proformas)) c group by c.nombre_cliente, c.nit_ci order by c.nombre_cliente asc, c.nit_ci asc")->fetch();
$clientes = $db->query("select * FROM inv_clientes ORDER BY cliente asc, nit asc")->fetch();
$prioridades = $db->select('*')->from('inv_prioridades_ventas')->fetch();

//listado de rutas
$rutas = $db->from('gps_rutas')->fetch();

// Define el limite de filas
$limite_longitud = 200;


//categorias
$cate = $db->select('*')->from('inv_categorias')->fetch();

// Define el limite monetario
$limite_monetario = 10000000;
$limite_monetario = number_format($limite_monetario, 2, '.', '');

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_mostrar = in_array('mostrar', $permisos);



?>
<?php require_once show_template('header-advanced'); ?>

<div class="row">
    <form id="formulario" class="form-horizontal" action="?/clientes/guardar_credito" method="POST">
    	<div class="col-md-6">
    		<div class="panel panel-success">
    			<div class="panel-heading">
    				<h3 class="panel-title">
    					<span class="glyphicon glyphicon-option-vertical"></span>
    					<strong>Clientes</strong>
    				</h3>
    			</div>
    			<div class="panel-body">
    				<h2 class="lead text-success">Clientes</h2>
    				<hr>
    				<?php if ($message = get_notification()) : ?>
                        <div class="alert alert-<?= $message['type']; ?>">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong><?= $message['title']; ?></strong>
                            <p><?= $message['content']; ?></p>
                        </div>
                    <?php endif ?>
    				<!--<form id="formulario" class="form-horizontal" action="?/clientes/guardar_credito" method="POST">-->
    					<div style="zoom: 1;">
    						<div class="form-group">
    							<label for="cliente" class="col-sm-4 control-label">Buscar:</label>
    							<div class="col-sm-8">
    								<select name="cliente" id="cliente" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " data-validation-optional="true">
    									<option value="">Buscar</option>
    									<?php foreach ($clientes as $cliente) { ?>
    									<option value="<?= escape($cliente['nit']) . '|' . escape($cliente['cliente']) . '|' . escape($cliente['direccion']) . '|' . escape($cliente['ubicacion']) . '|' . escape($cliente['telefono']) . '|' . escape($cliente['id_cliente']) . '|' . escape($cliente['nombre_factura']); ?>"><?= escape($cliente['id_cliente']) . ' &mdash; ' . $cliente['nit'] . ' &mdash; ' . escape($cliente['cliente']); ?></option>
    									<?php } ?>
    								</select>
    							</div>
    						</div>
    						<div class="form-group">
    							<label for="codigo" class="col-sm-4 control-label">Codigo:</label>
    							<div class="col-sm-8">
                                    <input type="text" value="" name="codigo" id="codigo" class="form-control text-uppercase" autocomplete="off" readonly data-validation="required number">
                                    <input type="hidden" value="0" name="codigo" id="codigo">
    							</div>
    						</div>
    						<div class="form-group">
    							<label for="nit_ci" class="col-sm-4 control-label">NIT / CI:</label>
    							<div class="col-sm-8">
                                    <input type="text" value="" name="nit_ci" id="nit_ci" class="form-control text-uppercase" autocomplete="off" readonly data-validation="required number">
                                    <input type="hidden" value="0" name="id_cliente" id="id_cliente">
    							</div>
    						</div>
                            <div class="form-group">
                                <label for="nombre_cliente" class="col-sm-4 control-label">Señor(es):</label>
                                <div class="col-sm-8">
                                    <input type="text" value="" name="nombre_cliente" id="nombre_cliente" class="form-control text-uppercase" readonly autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="factura_cliente" class="col-sm-4 control-label">Nombre factura:</label>
                                <div class="col-sm-8">
                                    <input type="text" value="" name="factura_cliente" id="factura_cliente" class="form-control text-uppercase" readonly autocomplete="off" data-validation="required letternumber length" data-validation-allowing="-+./&() " data-validation-length="max100">
                                </div>
                            </div>
                            <div class="form-group hidden">
                                <label for="adelanto" class="col-md-4 control-label">Adelanto:</label>
                                <div class="col-md-8">
                                    <input type="hidden" value="0" name="adelanto" id="adelanto" class="form-control" data-validation="required number" readonly data-validation-allowing="float">
                                </div>
                            </div>
    						<div class="form-group">
    							<label for="telefono_cliente" class="col-sm-4 control-label">Teléfono:</label>
    							<div class="col-sm-8">
    								<input type="text" value="0" name="telefono_cliente" id="telefono_cliente" class="form-control text-uppercase" readonly autocomplete="off" data-validation="required" data-validation-length="max100">
    							</div>
    						</div>
                            <div class="form-group">
                                <label for="atencion" class="col-sm-4 control-label">Ubicación:</label>
                                <div class="col-sm-8">
                                    <input type="text" value="" name="atencion" id="atencion" class="form-control text-uppercase" autocomplete="off" readonly data-validation="required letternumber length" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-length="max100">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="direccion" class="col-sm-4 control-label">Dirección:</label>
                                <div class="col-sm-8">
                                    <textarea name="direccion" id="direccion" class="form-control text-uppercase" rows="3" autocomplete="off" readonly data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
                                </div>
                            </div>
                            
    					</div>
    				
    				    <div class="form-group">
    						<div class="col-xs-12 text-right">
    							<button type="submit" class="btn btn-success">Actualizar cliente</button>
    							<button type="reset" class="btn btn-default">Restablecer</button>
    						</div>
    					</div>
    				<!--</form>-->
    			</div>
    		</div>
    	</div>
    	<div class="col-md-6">
    		<div class="panel panel-default">
                
    			<div class="panel-heading">
    				<h3 class="panel-title">
    					<span class="glyphicon glyphicon-search"></span>
    					<strong>Confirmar credito y asignar los días para el crédito</strong>
    				</h3>
    			</div>
    			<div class="panel-body">
    			    <div class="row">
    			        <div class="col-xs-12 col-sm-6 text-left">
                            <h2 class="lead">Asignar contrato de créditos</h2>
                        </div>
        				<div class="col-xs-12 col-sm-6 text-right">
                            <a href="?/clientes/credito" class="btn btn-success"data-toggle="tooltip" data-placement="top" title="Asignar credito"><i class="glyphicon glyphicon-arrow-left"></i><span class="hidden-xs"> Atras</span></a>
                        </div>
    			    </div>
    				<hr>
    				<div class="form-group">
    					<label for="credito" class="col-sm-4 control-label">Confirmar crédito:</label>
    					<div class="col-sm-8">
    						<select name="credito" id="credito" class="form-control text-uppercase" data-validation="required">
    						    <option value="">Seleccionar...</option>
    							<option value="Credito">Crédito</option>
    						</select>
    					</div>
    				</div>
    				<div class="form-group">
    					<label for="dias" class="col-sm-4 control-label">Asignar días:</label>
    					<div class="col-sm-8">
    						<input type="number" value="1" step="1" name="dias" max="365" maxlenght="3" id="dias" class="form-control" data-validation="required number" data-validation-length="max3">
    					</div>
    				</div>
                    
    			</div>
    		</div>
    	</div>
	</form>
</div>


<div id="mensaje_filtrar" class="hidden">
	<div class="alert alert-danger">No se encontraron resultados</div>
</div>
<!-- Plantillas filtrar fin -->

<!-- Modal mostrar inicio -->
<div id="modal_mostrar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content loader-wrapper">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<img src="" class="img-responsive img-rounded" data-modal-image="">
			</div>
			<div id="loader_mostrar" class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/buzz.min.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>

$(function () {
    //==============
    var $cliente = $('#cliente');
    var $nit_ci = $('#nit_ci');
    var $nombre_cliente = $('#nombre_cliente');
    var $direccion = $('#direccion');
    var $atencion = $('#atencion');
    var $telefono = $('#telefono_cliente');
    var $factura = $('#factura_cliente');
    var $id_cliente = $('#id_cliente');
    var $codigo = $('#codigo');
    var $cliente2 = $('#cliente2');
    var $nit_ci2 = $('#nit_ci2');
    var $nombre_cliente2 = $('#nombre_cliente2');
    var $adelanto = $('#adelanto');
	var $formulario = $('#formulario');
    var $asignar = $('[data-asignar]'),
		$form_asignar = $('#form_asignar'),
		$modal_asignar = $('#modal_asignar'),
		$unidad_id_asignar = $('#unidad_id_asignar');



    $cliente.selectize({
        persist: false,
        createOnBlur: true,
        create: true,
        onInitialize: function () {
            $cliente.css({
                display: 'block',
                left: '-10000px',
                opacity: '0',
                position: 'absolute',
                top: '-10000px'
            });
        },
        onChange: function () {
            $cliente.trigger('blur');
        },
        onBlur: function () {
            $cliente.trigger('blur');
        }
    }).on('change', function (e) {
        var valor = $(this).val();
        valor = valor.split('|');
        $(this)[0].selectize.clear();
        if (valor.length != 1) {
            $nit_ci.prop('readonly', true);
            $codigo.prop('readonly', true);
            $nombre_cliente.prop('readonly', true);
            $telefono.prop('readonly', true);
            $direccion.prop('readonly', true);
            $factura.prop('readonly', true);
            $atencion.prop('readonly', false);
            $nit_ci.val(valor[0]);
            $id_cliente.val(valor[5]);
            $codigo.val(valor[5]);
            $factura.val(valor[6]);
            $nombre_cliente.val(valor[1]);
            $telefono.val(valor[4]);
            $direccion.val(valor[2]);
            $atencion.val(valor[3]);
        } else {
            $codigo.prop('readonly', false);
            $nit_ci.prop('readonly', false);
            $nombre_cliente.prop('readonly', false);
            // if (es_nit(valor[0])) {
            //     $nit_ci.val(valor[0]);
            //     $nombre_cliente.val('').focus();
            // } else {
            //     $nombre_cliente.val(valor[0]);
            //     $nit_ci.val('').focus();
            // }
        }
    });


	$.validate({
		form: '#formulario',
		modules: 'basic',
// 		onSuccess: function () {
// 			guardar_proforma();
// 		}
	});
});


</script>
<?php require_once show_template('footer-advanced'); ?>