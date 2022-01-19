<?php
    $id_meta=(sizeof($params)>0)?$params[0]:0;
    $Consulta=$db->query("SELECT*FROM inv_metas_distribuidor WHERE id_meta='{$id_meta}'")->fetch_first();
    $Conseguido=$db->query("SELECT IFNULL(SUM(monto_total),0)AS total FROM tmp_egresos WHERE distribuidor_id='{$Consulta['distribuidor_id']}' AND distribuidor_estado='ENTREGA' AND distribuidor_fecha BETWEEN '{$Consulta['fecha_inicio']}' AND '{$Consulta['fecha_fin']}'")->fetch_first();
    $Conseguido = ($Conseguido['total']) ? $Conseguido['total'] : 0;
    require_once show_template('header-advanced');
?>
<div class='panel-heading'>
    <h3 class='panel-title'>
        <span class='glyphicon glyphicon-option-vertical'></span>
        <strong>Meta</strong>
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
    <hr>
    <div class='row'>
        <div class='col-sm-6 col-sm-offset-3'>
            <div class='form-horizontal'>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Monto:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['monto']?>' class='form-control' readonly>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Fecha Inicial:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['fecha_inicio']?>' class='form-control' readonly>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Fecha Final:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['fecha_fin']?>' class='form-control' readonly>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Monto Conseguido:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Conseguido?>' class='form-control' readonly>
                    </div>
                </div>
            </div>
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
<?php require_once show_template('footer-advanced');