<?php

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

    $id_meta=(sizeof($params)>0)?$params[0]:0;
    $Consulta=$db->query("SELECT *
                        FROM inv_meta_categoria AS mp
                        LEFT JOIN inv_categorias AS p ON p.categoria_id=mp.categoria_id
                        WHERE id_meta_categoria='{$id_meta}'")->fetch_first();

    $FechaInicio=$Consulta['fecha_inicio'];
    $FechaFinal=$Consulta['fecha_fin'];
    $IdCategoria=$Consulta['categoria_id'];

    $Empleados=$db->query("SELECT em.nombres,em.paterno,em.materno,SUM(ed.precio*(ed.cantidad/(IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad))))AS monto
                        FROM sys_empleados AS em
                        LEFT JOIN inv_egresos AS e ON e.empleado_id=em.id_empleado
                        LEFT JOIN inv_egresos_detalles AS ed ON e.id_egreso=ed.egreso_id
                        LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id AND asi.visible = 's'
                        LEFT JOIN inv_productos AS p ON ed.producto_id=p.id_producto
                        WHERE p.categoria_id='{$IdCategoria}'  AND e.anulado = 0 AND e.estadoe != 0 AND asi.visible = 's'
                        AND fecha_egreso BETWEEN '{$FechaInicio}' AND '{$FechaFinal}' GROUP BY em.id_empleado ORDER BY em.id_empleado")->fetch();

    $Conseguido=$db->query("SELECT IFNULL(SUM(ed.precio*(ed.cantidad/(IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))),0)AS total
                            FROM inv_egresos_detalles AS ed
                            LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id AND asi.visible = 's'
                            LEFT JOIN inv_egresos AS e ON e.id_egreso=ed.egreso_id
                            LEFT JOIN inv_productos AS p ON ed.producto_id=p.id_producto
                            WHERE p.categoria_id='{$IdCategoria}'  AND e.anulado = 0 AND e.estadoe != 0 AND asi.visible = 's'
                            AND e.fecha_egreso BETWEEN '{$Consulta['fecha_inicio']}' AND '{$Consulta['fecha_fin']}'")->fetch_first();
    $Conseguido = ($Conseguido['total']) ? $Conseguido['total'] : 0 ;
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
			<a href='?/metas-categorias/listar' class='btn btn-primary'><i class='glyphicon glyphicon-list-alt'></i><span> Listado</span></a>
		</div>
    </div>
    <hr>
    <div class='row'>
        <div class='col-sm-6 col-sm-offset-3'>
            <div class='form-horizontal'>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Categoria:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['categoria']?>' class='form-control' readonly>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Descripcion:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['descripcion']?>' class='form-control' readonly>
                    </div>
                </div>
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
                    <label class='col-md-3 control-label'>Monto Conjunto Conseguido<?= escape($moneda); ?>:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?= round($Conseguido,2) ?>' class='form-control' readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-12'>
            <div class='container'>
                <h2>Efectividad</h2>
                <div style='overflow-x:auto'>
                    <table class='table table-bordered table-condensed table-restructured table-striped table-hover'>
                        <thead>
                            <tr class='active'>
                                <th class='text-nowrap'>#</th>
                                <th class='text-nowrap'>Empleado</th>
                                <th class='text-nowrap'>Monto <?= escape($moneda); ?></th>
                                <th class='text-nowrap'>Porcentaje (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach($Empleados as $Fila=>$Empleado):
                                $Porcentaje=($Empleado['monto']*100)/$Consulta['monto'];
                                $Porcentaje=round($Porcentaje,2);
                        ?>
                            <tr>
                                <td><?=$Fila+1?></td>
                                <td><?="{$Empleado['nombres']} {$Empleado['paterno']} {$Empleado['materno']}"?></td>
                                <td><?= round($Empleado['monto'],2) ?></td>
                                <td><?=$Porcentaje?></td>
                            </tr>
                        <?php
                            endforeach;
                        ?>
                        </tbody>
                        <tfoot>
                            <tr class='active'>
                                <th class='text-nowrap'>#</th>
                                <th class='text-nowrap'>Empleado</th>
                                <th class='text-nowrap'>Monto</th>
                                <th class='text-nowrap'>Porcentaje</th>
                            </tr>
                        </tfoot>
                    </table>
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