<?php

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

    $id_meta=(sizeof($params)>0)?$params[0]:0;
    $Consulta=$db->query("SELECT *
                        FROM inv_meta_producto AS mp
                        LEFT JOIN inv_productos AS p ON p.id_producto=mp.producto_id
                        WHERE id_meta_producto='{$id_meta}'")->fetch_first();

    $FechaInicio=$Consulta['fecha_inicio'];
    $FechaFinal=$Consulta['fecha_fin'];
    $IdProducto=$Consulta['producto_id'];

    $Empleados=$db->query("SELECT em.nombres,em.paterno,em.materno,SUM(ed.precio*ed.cantidad)AS monto
                        FROM sys_empleados AS em
                        LEFT JOIN inv_egresos AS e ON e.empleado_id = em.id_empleado
                        LEFT JOIN inv_egresos_detalles AS ed ON e.id_egreso=ed.egreso_id  
                        WHERE  e.anulado = 0 AND e.estadoe != 0 AND ed.producto_id='{$IdProducto}' AND 
                        fecha_egreso BETWEEN '{$FechaInicio}' AND '{$FechaFinal}' GROUP BY em.id_empleado ORDER BY em.id_empleado")->fetch();

    $Conseguido=$db->query("SELECT IFNULL(SUM(ed.precio*(ed.cantidad/(IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))),0)AS total
                            FROM inv_egresos_detalles AS ed
                            LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id AND asi.visible = 's'
                            LEFT JOIN inv_egresos AS e ON e.id_egreso=ed.egreso_id
                            WHERE ed.producto_id='{$Consulta['producto_id']}' AND e.anulado = 0 AND e.estadoe != 0
                            AND e.fecha_egreso BETWEEN '{$Consulta['fecha_inicio']}' AND '{$Consulta['fecha_fin']}'")->fetch_first();
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
			<a href='?/metas-productos/listar' class='btn btn-primary'><i class='glyphicon glyphicon-list-alt'></i><span> Listado</span></a>
		</div>
    </div>
    <hr>
    <div class='row'>
        <div class='col-sm-6 col-sm-offset-3'>
            <div class='form-horizontal'>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Codigo:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['codigo']?>' class='form-control' readonly>
                    </div>
                </div>
                <div class='form-group'>
                    <label class='col-md-3 control-label'>Producto:</label>
                    <div class='col-md-9'>
                        <input type='text' value='<?=$Consulta['nombre']?>' class='form-control' readonly>
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
                    <label class='col-md-3 control-label'>Monto Conjunto Conseguido:</label>
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
                                <th class='text-nowrap'>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            foreach($Empleados as $Fila=>$Empleado):
                                
                                $Porcentaje=($Empleado['monto'] * 100)/$Consulta['monto'];
                                $Porcentaje=round($Porcentaje,2);
                        ?>
                            <tr>
                                <td><?=$Fila+1?></td>
                                <td><?="{$Empleado['nombres']} {$Empleado['paterno']} {$Empleado['materno']}"?></td>
                                <td><?=$Empleado['monto']?></td>
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