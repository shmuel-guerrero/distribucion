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
$proformas = $db->select('i.*, a.almacen, a.principal, e.nombres, e.paterno, e.materno, f.motivo')->from('inv_egresos i')->join('inv_almacenes a', 'i.almacen_id = a.id_almacen', 'left')->join('sys_empleados e', 'i.empleado_id = e.id_empleado', 'left')->join('gps_noventa_motivos f', 'i.motivo_id = f.id_motivo', 'left')->where('i.fecha_egreso >= ', $fecha_inicial)->where('i.fecha_egreso <= ', $fecha_final)->where('i.estadoe>',0)->order_by('i.fecha_egreso desc, i.hora_egreso desc')->fetch();

// Obtiene los empleados
$empleados = $db->select('z.*')->from('sys_empleados z')->join('sys_users a','z.id_empleado = a.persona_id')->where('a.rol_id !=',4)->order_by('z.id_empleado')->fetch();
//$empleados = $db->select('z.*, SUM(e.monto_total) as total, COUNT(e.id_egreso) as reg')->from('sys_empleados z')->join('inv_egresos e','z.id_empleado = e.empleado_id')->where('e.fecha_egreso >= ', $fecha_inicial)->where('e.fecha_egreso <= ', $fecha_final)->where('e.estadoe',2)->group_by('z.id_empleado')->order_by('z.id_empleado')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_activar = in_array('activar', $permisos);
$permiso_control = in_array('control', $permisos);

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading" data-formato="<?= strtoupper($formato_textual); ?>" data-mascara="<?= $formato_numeral; ?>" data-gestion="<?= date_decode($gestion_base, $_institution['formato']); ?>">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Empleados</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $empleados)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos empleados hacer clic en el siguiente bot??n: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/empleados/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?>
            <button class="btn btn-default" data-cambiar="true">
                <span class="glyphicon glyphicon-calendar"></span>
                <span class="hidden-xs">Cambiar</span>
            </button>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<?php if ($empleados) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Nombres</th>
				<th class="text-nowrap">Apellido paterno</th>
				<th class="text-nowrap">Apellido materno</th>
                <th class="text-nowrap">Empresa</th>
                <th class="">Efectividad</th>
                <th class="text-nowrap">Total</th>
                <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombres</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido paterno</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Apellido materno</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Empresa</th>
                <th data-datafilter-filter="true">Efectividad</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($empleados as $nro => $empleado) {
                $id = $empleado['id_empleado'];
                $det = $db->select('z.*, SUM(e.monto_total) as total, COUNT(e.id_egreso) as reg')->from('sys_empleados z')->join('inv_egresos e','z.id_empleado = e.empleado_id')->where('e.fecha_egreso >= ', $fecha_inicial)->where('e.fecha_egreso <= ', $fecha_final)->where('e.estadoe',2)->where('z.id_empleado',$id)->group_by('z.id_empleado')->fetch_first();
                $det2 = $db->select('z.*, COUNT(e.id_egreso) as reg')->from('sys_empleados z')->join('inv_egresos e','z.id_empleado = e.empleado_id')->where('e.fecha_egreso >= ', $fecha_inicial)->where('e.fecha_egreso <= ', $fecha_final)->where('e.estadoe',1)->where('z.id_empleado',$id)->group_by('z.id_empleado')->fetch_first();

                ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($empleado['nombres']); ?></td>
				<td class="text-nowrap"><?= escape($empleado['paterno']); ?></td>
				<td class="text-nowrap"><?= escape($empleado['materno']); ?></td>
                <td class="text-nowrap"><?php if($empleado['cargo']==1){echo $_institution['empresa1'];}else{echo $_institution['empresa2'];}; ?></td>
                <td class="text-nowrap"><?php if(!$det){echo '0'.' %';}else{if($det2){$efect = ($det['reg']*100)/($det['reg']+$det2['reg']); echo round($efect,2).' %';}else{echo '100 %';}} ?></td>
                <td class="text-nowrap"><?= escape($det['total']); ?></td>

				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_ver) { ?>
                        <a href="?/vendedor/visitas/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" data-toggle="tooltip" data-title="Ver empleado"><i class="glyphicon glyphicon-search"></i></a>
					<?php } ?>
                    <?php if ($permiso_activar) { ?>
                        <?php if ($empleado['fecha'] == date('Y-m-d')) { ?>
                            <a href="?/vendedor/activar/<?= $empleado['id_empleado']; ?>" class="text-success" data-toggle="tooltip" data-title="Venta cerrada" data-activar="true"><i class="glyphicon glyphicon-check"></i></a>
                        <?php } else { ?>
                            <a href="?/vendedor/activar/<?= $empleado['id_empleado']; ?>" class="text-danger" data-toggle="tooltip" data-title="Sigue vendiendo" data-activar="true"><i class="glyphicon glyphicon-unchecked"></i></a>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($permiso_imprimir) { ?>
                        <a href="?/vendedor/imprimir/<?= $empleado['id_empleado']; ?>/<?= $fecha_inicial ?>/<?= $fecha_final ?>" target="_blank" data-title="Imprimir ventas" ><i class="glyphicon glyphicon-print"></i></a>
                    <?php } ?>
                    <?php if ($empleado['fecha'] == date('Y-m-d')) { ?>
                        <!-- mostramos la hora de cierre del dia -->
                        <b class="text-info"> <?= $empleado['hora'] ?></b>
                    <?php } ?>
                    <?php if ($permiso_control) { ?>
                        <a href="?/vendedor/control/<?= $empleado['id_empleado']; ?>" data-toggle="tooltip" data-title="Control vendedor"><i class="glyphicon glyphicon-eye-open"></i></a>
                    <?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen empleados registrados en la base de datos, para crear nuevos empleados hacer clic en el bot??n nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
	</div>
	<?php } ?>
</div>
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
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>


    <script>
$(function () {
    var formato = $('[data-formato]').attr('data-formato');
    var mascara = $('[data-mascara]').attr('data-mascara');
    var gestion = $('[data-gestion]').attr('data-gestion');
    var $inicial_fecha = $('#inicial_fecha');
    var $final_fecha = $('#final_fecha');

    $.validate({
        form: '#form_fecha',
        modules: 'date',
        onSuccess: function () {
            var inicial_fecha = $.trim($('#inicial_fecha').val());
            var final_fecha = $.trim($('#final_fecha').val());
            var vacio = gestion.replace(new RegExp('9', 'g'), '0');

            inicial_fecha = inicial_fecha.replace(new RegExp('\\.', 'g'), '-');
            inicial_fecha = inicial_fecha.replace(new RegExp('/', 'g'), '-');
            final_fecha = final_fecha.replace(new RegExp('\\.', 'g'), '-');
            final_fecha = final_fecha.replace(new RegExp('/', 'g'), '-');
            vacio = vacio.replace(new RegExp('\\.', 'g'), '-');
            vacio = vacio.replace(new RegExp('/', 'g'), '-');
            final_fecha = (final_fecha != '') ? ('/' + final_fecha ) : '';
            inicial_fecha = (inicial_fecha != '') ? ('/' + inicial_fecha) : ((final_fecha != '') ? ('/' + vacio) : '');

            window.location = '?/vendedor/listar' + inicial_fecha + final_fecha;
        }
    });

    //$inicial_fecha.mask(mascara).datetimepicker({
    $inicial_fecha.datetimepicker({
        format: formato
    });

    //$final_fecha.mask(mascara).datetimepicker({
    $final_fecha.datetimepicker({
        format: formato
    });

    $inicial_fecha.on('dp.change', function (e) {
        $final_fecha.data('DateTimePicker').minDate(e.date);
    });

    $final_fecha.on('dp.change', function (e) {
        $inicial_fecha.data('DateTimePicker').maxDate(e.date);
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
    <?php if ($permiso_activar) { ?>
    $('[data-activar]').on('click', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        bootbox.confirm('Est?? seguro que desea cambiar el estado del usuario?', function (result) {
            if(result){
                window.location = url;
            }
        });
    });
    <?php } ?>

    var $modal_asignar = $('#modal_asignar'), $form_asignar = $('#form_asignar'), $asignar = $('[data-asignar]');
    $asignar.on('click', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $form_asignar.attr('action', href);
        $modal_asignar.modal('show');
    });

	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Est?? seguro que desea eliminar el empleado?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/empleados/crear';
				break;
			}
		}
	});
	<?php } ?>
	
	<?php if ($empleados) { ?>
	var table = $('#table').DataFilter({
		filter: false,
		name: 'empleados',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>