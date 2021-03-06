<?php
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

$Consulta = $db->query("SELECT id_empleado,nombres,paterno,materno, m.fecha_registro, m.fecha_inicio, m.fecha_fin FROM sys_empleados e 
                        LEFT JOIN (SELECT MAX(fecha_registro)AS fecha_registro, distribuidor_id, fecha_inicio, fecha_fin FROM  inv_metas_distribuidor GROUP BY distribuidor_id) m ON e.id_empleado = m.distribuidor_id
                        LEFT JOIN sys_users u ON u.persona_id = e.id_empleado
                        WHERE  u.rol_id = 4 AND ( CURDATE() NOT BETWEEN  m.fecha_inicio AND  m.fecha_fin 
                        OR m.fecha_registro IS NULL )")->fetch();

require_once show_template('header-configured');
?>
<div class='panel-heading' data-formato='<?= strtoupper($formato_textual); ?>' data-mascara='<?= $formato_numeral; ?>' data-gestion='<?= date_decode($gestion_base, $_institution['formato']); ?>'>
    <h3 class='panel-title'>
        <span class='glyphicon glyphicon-option-vertical'></span>
        <strong>Crear Metas</strong>
    </h3>
</div>
<div class='panel-body'>
    <div class='row'>
        <div class='col-sm-8 hidden-xs'>
            <div class='text-label'>Para ver las metas hacer clic en el siguiente botón: </div>
        </div>
        <div class='col-xs-12 col-sm-4 text-right'>
            <a href='?/metas_distribuidor/listar' class='btn btn-primary'><i class='glyphicon glyphicon-list-alt'></i><span> Listado</span></a>
        </div>
    </div>
    <div class='row'>
        <div class='col-sm-6 col-sm-offset-3'>
            <form method='post' id='meta_form' action='?/metas_distribuidor/guardar' class='form-horizontal' enctype='multipart/form-data'>
                <div class='form-group'>
                    <label for='personal' class='col-md-3 control-label'>Personal:</label>
                    <div class='col-md-9'>
                        <select name='personal' id='personal' class='form-control' data-validation='required'>
                            <?php
                            foreach ($Consulta as $Fila => $Dato) :
                            ?>
                                <option value='<?= $Dato['id_empleado'] ?>'><?= $Dato['nombres'] . ' ' . $Dato['paterno'] . ' ' . $Dato['materno'] ?></option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='monto' class='col-md-3 control-label'>Monto:</label>
                    <div class='col-md-9'>
                        <input type='number' value='' name='monto' id='monto' class='form-control' autocomplete='off' data-validation='required number length' data-validation-allowing="range[0.1;10000000.00],float" data-validation-length='max100'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='fecha_ini' class='col-md-3 control-label'>Fecha Inicial:</label>
                    <div class='col-md-9'>
                        <input type='text' value='' name='fecha_ini' id='fecha_ini' class='form-control' autocomplete='off' data-validation='date required' data-validation-format='<?= $formato_textual; ?>'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='fecha_final' class='col-md-3 control-label'>Fecha Final:</label>
                    <div class='col-md-9'>
                        <input type='text' value='' name='fecha_final' id='fecha_final' class='form-control' autocomplete='off' data-validation='required date'  data-validation-format='<?= $formato_textual; ?>'>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='submit' class='btn btn-primary' data-aceptar='true'>
                        <span class='glyphicon glyphicon-ok'></span>
                        <span>Aceptar</span>
                    </button>
                    <button type='reset' class='btn btn-default' data-cancelar='true'>
                        <span class='glyphicon glyphicon-remove'></span>
                        <span>Cancelar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src='<?= js; ?>/jquery.dataTables.min.js'></script>
<script src='<?= js; ?>/dataTables.bootstrap.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script src='<?= js; ?>/jquery.maskedinput.min.js'></script>
<script src='<?= js; ?>/jquery.base64.js'></script>
<script src='<?= js; ?>/pdfmake.min.js'></script>
<script src='<?= js; ?>/vfs_fonts.js'></script>
<script src='<?= js; ?>/jquery.dataFilters.min.js'></script>
<script src='<?= js; ?>/moment.min.js'></script>
<script src='<?= js; ?>/moment.es.js'></script>
<script src='<?= js; ?>/bootstrap-datetimepicker.min.js'></script>
<script>
    $.validate({
		modules: 'basic'
	});
    const formato = $('[data-formato]').attr('data-formato'),
        $fecha_ini = $('#fecha_ini'),
        $fecha_final = $('#fecha_final');
    $fecha_ini.datetimepicker({
        format: formato,
        minDate: '<?= date("Y-m-d") ?>'
    });
    $fecha_ini.on('dp.change', function(e) {
        $fecha_final.data('DateTimePicker').minDate(e.date);
    });
    $fecha_final.datetimepicker({
        format: formato,
        minDate: '<?= date("Y-m-d") ?>'
    });
    $fecha_final.on('dp.change', function(e) {
        $fecha_ini.data('DateTimePicker').minDate(e.date);
    });
</script>
<?php require_once show_template('footer-configured'); ?>