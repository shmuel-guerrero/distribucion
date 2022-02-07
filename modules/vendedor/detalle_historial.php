<?php

// Obtiene los parametros
$id_empleado = (isset($params[0])) ? $params[0] : 0;
$fecha_inicial = (isset($params[1])) ? $params[1] : 0;
$fecha_final = (isset($params[2])) ? $params[2] : 0;

$empleado = $db->select('*')->from('sys_empleados')->where('id_empleado',$id_empleado)->fetch_first();
// Obtiene los movimientos
$movimientos = $db->query("SELECT c.*, d.motivo, e.* FROM sys_users a
	LEFT JOIN sys_empleados b ON a.persona_id = b.id_empleado
	LEFT JOIN tmp_egresos c ON b.id_empleado = c.empleado_id
	LEFT JOIN gps_noventa_motivos d ON c.motivo_id = d.id_motivo
	LEFT JOIN sys_empleados e ON c.distribuidor_id = e.id_empleado
	WHERE a.rol_id != 4 AND b.id_empleado = '$id_empleado' AND c.distribuidor_fecha >= '$fecha_inicial' AND c.distribuidor_id <= '$fecha_final'")->fetch();

// Verifica si existen movimientos
if (!$movimientos) {
    // Error 404
    require_once not_found();
    exit;
}


?>
<?php require_once show_template('header-configured'); ?>
    <div class="panel-heading">
        <h3 class="panel-title">
            <span class="glyphicon glyphicon-option-vertical"></span>
            <strong>Vendedor</strong>
        </h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-8 hidden-xs">
                <div class="text-label">Para cambiar de almacén o de producto hacer clic en el siguiente botón: </div>
            </div>
            <div class="col-xs-12 col-sm-4 text-right">
                <a href="?/vendedor/historial/<?= $fecha_inicial?>/<?= $fecha_final ?>" class="btn btn-primary">
                    <span class="glyphicon glyphicon-menu-left"></span>
                    <span>Regresar</span>
                </a>
<!--                <a href="?/kardex/imprimir/--><?//= $id_almacen; ?><!--/--><?//= $id_producto; ?><!--" target="_blank" class="btn btn-default">-->
<!--                    <span class="glyphicon glyphicon-print"></span>-->
<!--                    <span>Imprimir</span>-->
<!--                </a>-->
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-sm-6">
                <div class="well">
                    <h4 class="margin-none"><u>Datos</u></h4>
                    <dl class="margin-none">
                        <dt>Nombre:</dt>
                        <dd><?= escape($empleado['nombres']); ?></dd>
                        <dt>Primer apellido:</dt>
                        <dd><?= escape($empleado['paterno']); ?></dd>
                        <dt>Segundo apellido:</dt>
                        <dd>
                            <?= escape($empleado['materno']); ?>
                        </dd>
                    </dl>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="well">
                    <h4 class="margin-none"><u></u></h4>
                    <dl class="margin-none">
                        <dt>Desde:</dt>
                        <dd><?= escape($fecha_inicial); ?></dd>
                        <dt>Hasta:</dt>
                        <dd><?= escape($fecha_final); ?></dd>
                        <dt></dt>
                        <dd></dd>
                    </dl>
                </div>
            </div>
        </div>
        <?php if ($movimientos) { ?>
            <h3 class="text-center">HISTORIAL VENDEDOR</h3>
            <div class="table-responsive">
                <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
                    <thead>
                    <tr class="active">
                        <th class="text-nowrap">#</th>
                        <th class="text-nowrap">Venta</th>
                        <th class="text-nowrap">Cliente</th>
                        <th class="text-nowrap">Registros</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Productos</th>
                        <th class="text-nowrap">Cantidad</th>
                        <th class="text-nowrap">Motivo</th>
                        <th class="text-nowrap">Registros</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Productos</th>
                        <th class="text-nowrap">Cantidad</th>
                        <th class="text-nowrap">Nombre</th>
                        <th class="text-nowrap">Fecha</th>
                        <th class="text-nowrap">Hora</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr class="active">
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">#</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Venta</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Productos</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Motivo</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Total</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Productos</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha</th>
                        <th class="text-nowrap text-middle" data-datafilter-filter="true">Hora</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php foreach($movimientos as $nro => $movimiento){
                        $productos = $db->select('*, a.unidad_id as unidad_venta')->from('tmp_egresos_detalles a')->join('inv_productos b','a.producto_id=b.id_producto')->where(array('a.egreso_id'=>$movimiento['id_egreso'],'tmp_egreso_id'=>$movimiento['id_tmp_egreso']))->fetch();
                        if($movimiento['distribuidor_estado']=='ENTREGA'){?>
                    <tr>
                        <th class="text-nowrap"><?= $nro + 1; ?></th>
                        <td class="text-nowrap">
                            <span><?= escape($movimiento['fecha_egreso']); ?></span>
                            <span class="text-primary"><?= escape($movimiento['hora_egreso']); ?></span>
                        </td>
                        <td class="text-nowrap"><?= (escape($movimiento['nombre_cliente'])); ?></td>
                        <td class="text-nowrap text-right success text-primary"><strong><?= escape($movimiento['nro_registros']); ?></strong></td>
                        <td class="text-nowrap text-right success"><strong><?= number_format(($movimiento['monto_total']), 2, '.', ''); ?></strong></td>
                        <td class="text-nowrap text-right success"><?php foreach($productos as $producto){echo $producto['nombre'].'<br>';}  ?></td>
                        <td class="text-nowrap text-right success"><?php foreach($productos as $producto){echo ($producto['cantidad']/cantidad_unidad($db,$producto['id_producto'],$producto['unidad_venta'])).' '.nombre_unidad($db,$producto['unidad_venta']).'<br>';}  ?></td>
                        <td class="text-nowrap text-right"></td>
                        <td class="text-nowrap text-right"></td>
                        <td class="text-nowrap text-right"></td>
                        <td class="text-nowrap text-right"></td>
                        <td class="text-nowrap text-right"></td>
                        <td class="text-nowrap text-center info text-primary"><strong><?= $movimiento['nombres'].' '.$movimiento['paterno']; ?></strong></td>
                        <td class="text-nowrap text-right info"><?= $movimiento['distribuidor_fecha']; ?></td>
                        <td class="text-nowrap text-right info"><?= $movimiento['distribuidor_hora']; ?></td>
                    </tr>
                    <?php }else{ ?>
                            <tr>
                                <th class="text-nowrap"><?= $nro + 1; ?></th>
                                <td class="text-nowrap">
                                    <span><?= escape($movimiento['fecha_egreso']); ?></span>
                                    <span class="text-primary"><?= escape($movimiento['hora_egreso']); ?></span>
                                </td>
                                <td class="text-nowrap"><?= (escape($movimiento['nombre_cliente'])); ?></td>
                                <td class="text-nowrap text-right text-primary"></td>
                                <td class="text-nowrap text-right"></td>
                                <td class="text-nowrap text-right"></td>
                                <td class="text-nowrap text-right"></td>
                                <td class="text-nowrap text-left danger"><?php if(isset($movimiento['motivo'])){echo escape($movimiento['motivo']);}else{if($movimiento['distribuidor_estado']=='ALMACEN'){echo 'Devuelto al almacén';}}  ?></td>
                                <td class="text-nowrap text-right danger"><strong><?= escape($movimiento['nro_registros']); ?></strong></td>
                                <td class="text-nowrap text-right danger"><strong><?= number_format(($movimiento['monto_total']), 2, '.', ''); ?></strong></td>
                                <td class="text-nowrap text-right danger"><?php foreach($productos as $producto){echo $producto['nombre'].'<br>';}  ?></td>
                                <td class="text-nowrap text-right danger"><?php foreach($productos as $producto){echo ($producto['cantidad']/cantidad_unidad($db,$producto['id_producto'],$producto['unidad_venta'])).' '.nombre_unidad($db,$producto['unidad_venta']).'<br>';}  ?></td>
                                <td class="text-nowrap text-center info text-primary"><strong><?= $movimiento['nombres'].' '.$movimiento['paterno']; ?></strong></td>
                                <td class="text-nowrap text-right info"><?= $movimiento['distribuidor_fecha']; ?></td>
                                <td class="text-nowrap text-right info"><?= $movimiento['distribuidor_hora']; ?></td>
                            </tr>
                    <?php   }
                    } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="alert alert-danger">
                <strong>Advertencia!</strong>
                <p>El kardex valorado no puede mostrarse por que no existen movimientos registrados.</p>
            </div>
        <?php } ?>
    </div>
    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script>
        var table = $('#table').DataFilter({
            filter: false,
            name: 'Historial vendedores',
            reports: 'xls|doc|pdf|html'
        });
        /*$(function () {
         var table = $('#table').DataFilter({
         filter: true,
         name: 'reporte_de_existencias',
         reports: 'xls|doc|pdf|html'
         });
         });*/
    </script>
<?php require_once show_template('footer-configured'); ?>