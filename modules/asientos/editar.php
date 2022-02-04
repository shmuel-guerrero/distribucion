<?php

// Obtiene el id_user
$id_auto = (sizeof($params) > 0) ? $params[0] : 0;

// Obtiene el modelo roles
$roles = $db->from('sys_roles')->order_by('rol', 'asc')->fetch();

// Obtiene asientos automaticos
$auto = $db->select('*')->from('con_asientos_automaticos a')->where('a.id_automatico', $id_auto)->fetch_first();

//obtiene cuentas
$planes = $db->select('*')->from('con_plan')->fetch();

// Verifica si existe el usuario
if (!$auto) {
	// Error 404
	require_once not_found();
	exit;
}

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_listar = in_array('listar', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Editar asiento</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_crear || $permiso_ver || $permiso_eliminar || $permiso_listar) { ?>
	<div class="row">
		<div class="col-sm-7 col-md-6 hidden-xs">
			<div class="text-label">Para realizar una acción hacer clic en los botones:</div>
		</div>
		<div class="col-xs-12 col-sm-5 col-md-6 text-right">
			<?php if ($permiso_crear) { ?>
			<a href="?/asientos/crear" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs hidden-sm"> Nuevo</span></a>
			<?php } ?>
			<?php if ($permiso_ver) { ?>
			<a href="?/usuarios/ver/<?= $user['id_user']; ?>" class="btn btn-warning"><i class="glyphicon glyphicon-search"></i><span class="hidden-xs hidden-sm"> Ver</span></a>
			<?php } ?>
			<?php if ($permiso_eliminar) { ?>
			<a href="?/usuarios/eliminar/<?= $user['id_user']; ?>" class="btn btn-danger" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i><span class="hidden-xs hidden-sm"> Eliminar</span></a>
			<?php } ?>
			<?php if ($permiso_listar) { ?>
			<a href="?/asientos/listar" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i><span class="hidden-xs"> Listado</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<div class="row">
		<div class="col-sm-10 col-sm-offset-1">
			<form id="formulario" class="form-horizontal">
				<div class="form-group">
					<label for="username" class="col-md-6 control-label">Nombre del asiento:</label>
					<div class="col-md-6">
                        <input type="text" value="<?= $auto['titulo_automatico'] ?>" name="id_user" class="form-control" data-validation="required">
                        <input type="hidden" value="<?= $id_auto ?>" name="id_auto" >
					</div>
				</div>
                <div class="form-group">
                    <div class="col-md-6">
                        <div class="panel panel-default ">
                            <div class="panel-heading col-md-12">Debe</div>
                            <div class="panel-body" id="debe">
                                <br/><br/>
                                <div class="form-group">
                                    <?php $deb = $db->select('*')->from('con_detalles_automaticos')->where('automatico_id',$id_auto)->where('tipo',1)->fetch(); ?>
                                    <table id="debe" class="table table-bordered table-condensed table-striped table-hover margin-none">
                                        <thead>
                                        <tr class="active">
                                            <th class="text-nowrap text-center width-collapse">#</th>
                                            <th class="text-nowrap text-center ">CUENTA</th>
                                            <th class="text-nowrap text-center width-collapse">PORCENTAJE</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr class="active">
                                            <th class="text-nowrap text-right" colspan="2">TOTAL %</th>
                                            <th class="text-nowrap text-right" data-subtotald="">0</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        <?php foreach($deb as $nro => $debe){ ?>
                                            <tr class="active" data-haber="<?= $nro + 1 ?>" >
                                                <td class="text-nowrap text-middle"><b><?= $nro + 1 ?></b></td>
                                                <td class="text-nowrap text-middle">
                                                    <select name="debeasi[]" id="debeasi" class="form-control" data-validation="required">
                                                        <option value="">Seleccionar cuenta...</option>
                                                        <?php foreach ($planes as $plan) {
                                                            if($debe['plan_id'] == $plan['n_plan']){?>
                                                            <option value="<?= $plan['n_plan'] ?>" selected><?= $plan['plan_cuenta'] ?></option>
                                                        <?php }else { ?>
                                                            <option value="<?= $plan['n_plan'] ?>"><?= $plan['plan_cuenta'] ?></option>
                                                        <?php }} ?>
                                                    </select>
                                                </td>
                                                <td class="text-nowrap text-middle"><input type="text" value="<?= $debe['porcentaje'] ?>" name="debepor[]" class="form-control" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100" onkeyup="calcular_totald()" ></td>
                                                </tr>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    <div class="text-right">
                                        <a href="#ttt" type="button" class="btn btn-success" data-asiento-debe="0" onclick="adicionard(this)">
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </a>
                                        <a href="#ttt" type="button" class="btn btn-danger" data-asiento-debe="0" onclick="eliminard()">
                                            <span class="glyphicon glyphicon-minus"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default ">
                            <div class="panel-heading col-md-12">Haber</div>
                            <div class="panel-body" id="haber">
                                <br/><br/>
                                <div class="form-group">
                                    <?php $hab = $db->select('*')->from('con_detalles_automaticos')->where('automatico_id',$id_auto)->where('tipo',2)->fetch(); ?>
                                    <table id="haber" class="table table-bordered table-condensed table-striped table-hover margin-none">
                                        <thead>
                                        <tr class="active">
                                            <th class="text-nowrap text-center width-collapse">#</th>
                                            <th class="text-nowrap text-center ">CUENTA</th>
                                            <th class="text-nowrap text-center width-collapse">PORCENTAJE</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr class="active">
                                            <th class="text-nowrap text-right" colspan="2">TOTAL %</th>
                                            <th class="text-nowrap text-right" data-subtotalh="" >0</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        <?php foreach($hab as $nro => $haber){ ?>
                                            <tr class="active" data-haber="<?= $nro + 1 ?>" >
                                                <td class="text-nowrap text-middle"><b><?= $nro + 1 ?></b></td>
                                                <td class="text-nowrap text-middle">
                                                    <select name="haberasi[]" id="haberasi" class="form-control" data-validation="required">
                                                        <option value="">Seleccionar cuenta...</option>
                                                        <?php foreach ($planes as $plan) {
                                                            if($haber['plan_id'] == $plan['n_plan']){?>
                                                        <option value="<?= $plan['n_plan'] ?>" selected><?= $plan['plan_cuenta'] ?></option>
                                                            <?php }else { ?>
                                                        <option value="<?= $plan['n_plan'] ?>"><?= $plan['plan_cuenta'] ?></option>
                                                        <?php }} ?>
                                                    </select>
                                                </td>
                                                <td class="text-nowrap text-middle"><input type="text" value="<?= $haber['porcentaje'] ?>" name="haberpor[]" class="form-control" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100" onkeyup="calcular_totalh()" ></td>
                                            </tr>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                    <div class="text-right">
                                        <a href="#ttt" type="button" class="btn btn-success" data-asiento-haber="0" onclick="adicionarh(this)">
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </a>
                                        <a href="#ttt" type="button" class="btn btn-danger" data-asiento-haber="0" onclick="eliminarh()">
                                            <span class="glyphicon glyphicon-minus"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
					<div class="col-md-6 col-md-offset-9">
						<button type="submit" class="btn btn-primary">
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
	</div>
</div>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script>
$(function () {
    $("#formulario").on('submit',function(e){
        e.preventDefault();
    });
	$.validate({
		modules: 'security'
	});
	
	$('.form-control:first').select();
	
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el usuario?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
    calcular_totald();
    calcular_totalh();

});
function adicionarh(id) {
    var busqueda;
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: '?/asientos/cuentas'

    }).done(function (productos) {
        if (productos.length) {


            var $haber = $('#haber tbody');
//    var $canti = $('#haber tbody tr').length + 1;
            var canti = $('#haber tbody tr').size() + 1;

            plantilla = '<tr class="active" data-haber="' + canti + '">' +
            '<td class="text-nowrap text-middle"><b>' + canti + '</b></td>' +
            '<td class="text-nowrap text-middle"><select name="haberasi[]" id="haberasi" class="form-control" data-validation="required"><option value="">Seleccionar cuenta...</option>';
            for (var i in productos) {
                plantilla = plantilla + '<option value="' +productos[i].n_plan+ '">' + productos[i].plan_cuenta + '</option>';
            }
            plantilla = plantilla + '</select></td>';
            plantilla = plantilla + '<td class="text-nowrap text-middle"><input type="text"';
            if(canti == 1){
                plantilla = plantilla + 'value="100" name="haberpor[]" class="form-control" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100" onkeyup="calcular_totalh()" ></td>' +
                '</tr>';
            }else{
                plantilla = plantilla + 'value="0" name="haberpor[]" class="form-control" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100" onkeyup="calcular_totalh()" ></td>' +
                '</tr>';
            }
            $haber.append(plantilla);
           calcular_totalh();
        } else {
            $contenido_filtrar.html($mensaje_filtrar.html());
        }
    }).fail(function () {
        $contenido_filtrar.html($mensaje_filtrar.html());
        $.notify({
            message: 'La operación fue interrumpida por un fallo.'
        }, {
            type: 'danger'
        });
        blup.stop().play();
    });
    $.validate({
        form: '#formulario',
        onSuccess: function () {
            guardar_nota();
        }
    });
}
function eliminarh(id) {
    var $haber = $('#haber tbody');
//    var $canti = $('#haber tbody tr').length + 1;
    var canti = $('#haber tbody tr').size() + 1;

    if(canti > 2)
    {
        // Eliminamos la ultima columna
        $("#haber tbody tr:last").remove();
    }
    calcular_totalh();
}
function calcular_totalh() {
    var $haber = $('#haber tbody tr');
    var $total = $('[data-subtotalh]:first').text();
    //console.log($haber);
    var $importes = $haber.find('[data-porcentaje]');
    var importe, total = 0;

    $haber.each(function (i) {
        importe = $.trim($(this).find("input").val());

        total = total + parseInt(importe);
    });
    var $total = $('[data-subtotalh]:first').text(total);
    $.validate({
        form: '#formulario',
        onSuccess: function () {
            guardar_nota();
        }
    });
}

function adicionard(id) {
    var busqueda;
    $.ajax({
        type: 'post',
        dataType: 'json',
        url: '?/asientos/cuentas'

    }).done(function (productos) {
        if (productos.length) {


            var $haber = $('#debe tbody');
//    var $canti = $('#haber tbody tr').length + 1;
            var canti = $('#debe tbody tr').size() + 1;

            plantilla = '<tr class="active" data-haber="' + canti + '">' +
            '<td class="text-nowrap text-middle"><b>' + canti + '</b></td>' +
            '<td class="text-nowrap text-middle"><select name="debeasi[]" id="debeasi" class="form-control" data-validation="required"><option value="">Seleccionar cuenta...</option>';
            for (var i in productos) {
                plantilla = plantilla + '<option value="' +productos[i].n_plan+ '">' + productos[i].plan_cuenta + '</option>';
            }
            plantilla = plantilla + '</select></td>';
            plantilla = plantilla + '<td class="text-nowrap text-middle"><input type="text"';
            if(canti == 1){
                plantilla = plantilla + 'value="100" name="debepor[]" class="form-control" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100" onkeyup="calcular_totald()" ></td>' +
                '</tr>';
            }else{
                plantilla = plantilla + 'value="0" name="debepor[]" class="form-control" maxlength="10" autocomplete="off" data-cantidad="" data-validation="required number" data-validation-allowing="range[1;100]" data-validation-error-msg="Debe ser un número positivo entre 1 y 100" onkeyup="calcular_totald()" ></td>' +
                '</tr>';
            }
            $haber.append(plantilla);
            calcular_totald();
        } else {
            $contenido_filtrar.html($mensaje_filtrar.html());
        }
    }).fail(function () {
        $contenido_filtrar.html($mensaje_filtrar.html());
        $.notify({
            message: 'La operación fue interrumpida por un fallo.'
        }, {
            type: 'danger'
        });
        blup.stop().play();
    });
    $.validate({
        form: '#formulario',
        onSuccess: function () {
            guardar_nota();
        }
    });
}
function eliminard(id) {
    var $debe = $('#debe tbody');
//    var $canti = $('#haber tbody tr').length + 1;
    var canti = $('#debe tbody tr').size() + 1;

    if(canti > 2)
    {
        // Eliminamos la ultima columna
        $("#debe tbody tr:last").remove();
    }
    calcular_totald();
}
function calcular_totald() {
    var $debe = $('#debe tbody tr');
    var $total = $('[data-subtotald]:first').text();
    //console.log($haber);
    var $importes = $debe.find('[data-porcentaje]');
    var importe, total = 0;

    $debe.each(function (i) {
        importe = $.trim($(this).find("input").val());

        total = total + parseInt(importe);
    });
    var $total = $('[data-subtotald]:first').text(total);
}
function guardar_nota() {

    var totalh = $('[data-subtotalh]:first').text();
    var totald = $('[data-subtotald]:first').text();

    if(totalh=='100' & totald=='100'){
        var data = $('#formulario').serialize();

        $('#loader').fadeIn(100);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?/asientos/guardar',
            data: data
        }).done(function (venta) {

            if (venta) {
                $("#formulario").trigger("reset");
                $('#loader').fadeOut(100);
                $.notify({
                    message: 'La nota de remisión fue realizada satisfactoriamente.'
                }, {
                    type: 'success'
                });
            } else {
                $('#loader').fadeOut(100);
                $.notify({
                    message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de remisión, verifique si la se guardó parcialmente.'
                }, {
                    type: 'danger'
                });
            }
        }).fail(function () {
            $('#loader').fadeOut(100);
            $.notify({
                message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos de la nota de remisión, verifique si la se guardó parcialmente.'
            }, {
                type: 'danger'
            });
        });
    }else{
        $('#loader').fadeOut(100);
        $.notify({
            message: 'Ocurrió un problema en el proceso, el total del debe y haber debe ser 100%.'
        }, {
            type: 'danger'
        });
    }
}
</script>
<?php require_once show_template('footer-configured'); ?>