<?php
    if(!isset($params[0])):
        require_once not_found();
	    die;
    endif;
    $IdImportacion=trim($params[0]);
    $Pendientes=$db->query("SELECT ig.id_importacion_gasto,ig.nombre,ig.codigo,ig.fecha,ig.total,ig.total_gasto,ip.actual
                            FROM inv_importacion_gasto AS ig
                            LEFT JOIN(
                                SELECT importacion_gasto_id,SUM(monto)AS actual
                                FROM inv_importacion_pagos
                                GROUP BY importacion_gasto_id
                            )AS ip ON ip.importacion_gasto_id=ig.id_importacion_gasto
                            WHERE ig.importacion_id='{$IdImportacion}'")->fetch();
    require_once show_template('header-advanced');
?>
<div class='row'>
    <div class='col-md-12'>
        <div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>
                    <span class='glyphicon glyphicon-list'></span>
                    <strong>Listado de Pagos Pendientes</strong>
                </h3>
            </div>
            <div class='panel-body'>
                <!--<h2 class='lead'>Pagos Pendientes</h2>-->
                <div class="row">
            		<div class="col-sm-8 hidden-xs">
            			<h2 class='lead'>Pagos Pendientes</h2>
            		</div>
            		<div class="col-xs-12 col-sm-4 text-right">
            			<a href="?/importaciones/gastos" class="btn btn-primary">
            				<span class="glyphicon glyphicon-menu-left"></span>
            				<span>Listar</span>
            			</a>
            		</div>
            	</div>
                <hr>
                <form method='POST' id='formularioF' action='?/importaciones/guardar_pagos'>
                    <input type="hidden" name='id_importacion' value='<?=$IdImportacion?>'>
                    <div class='margin-none' style='overflow-x:auto'>
                        <table id='preparacion' class='table table-bordered table-condensed table-restructured table-striped table-hover'>
                            <thead>
                                <tr class='active'>
                                    <th class='text-nowrap'>Nombre</th>
                                    <th class='text-nowrap'>Código</th>
                                    <th class='text-nowrap'>Fecha</th>
                                    <th class='text-nowrap'>Total</th>
                                    <th class='text-nowrap'>Pendiente</th>
                                    <th class='text-nowrap'>Pagar</th>
                                    <th class='text-nowrap'>Forma de pago</th>
                                    <th class='text-nowrap'>Nro. documento</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                foreach($Pendientes as $Fila=>$Pendiente):
                                    if($Pendiente['total']>$Pendiente['actual']):
                            ?>
                                <tr>
                                    <td><?=$Pendiente['nombre']?></td>
                                    <td><?=$Pendiente['codigo']?></td>
                                    <td><?=$Pendiente['fecha']?></td>
                                    <td><?=$Pendiente['total']?></td>
                                    <td><?=round($Pendiente['total']-$Pendiente['actual'],2)?></td>
                                    <td>
                                        <input type="hidden" name='id_importacion_gasto[]' value='<?=$Pendiente['id_importacion_gasto']?>'>
                                        <input type="text" name='pago[]' value='0' class="form-control" data-validation='required number' data-validation-allowing='float'>
                                    </td>
                                    <td>
                                        <!--<input type="text" name='pago[]' value='0' data-validation='required number' data-validation-allowing='float'>-->
                                        <select name="tipo_pago[]" id="tipo_pago" class="form-control">
                                            <option value="EFECTIVO">Efectivo</option>
                                            <option value="CHEQUE">Cheque</option>
                                            <option value="TRANSFERENCIA">Transferencia</option>
                                            <option value="TARJETA">Tarjeta</option>
                                        </select>
                                    </td>
                                    <td>
                                        <!--<input type="text" name='pago[]' value='0' data-validation='required number' data-validation-allowing='float'>-->
                                        <input type="text" name="nro_pago[]" placeholder="Nro documento" class="form-control" autocomplete="off" data-validation="number" aria-label="..." data-validation-optional="true">
                                    </td>
                                </tr>
                            <?php
                                    endif;
                                endforeach;
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary">
                        <i class='glyphicon glyphicon-plus'></i>Registrar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src='<?= js; ?>/jquery.form-validator.min.js'></script>
<script src='<?= js; ?>/jquery.form-validator.es.js'></script>
<script>
    //VALIDACION
    $(function(){
        $.validate({
            form: '#formularioF',
            modules: 'basic',
        });
    });
</script>
<?php
    require_once show_template('footer-advanced');