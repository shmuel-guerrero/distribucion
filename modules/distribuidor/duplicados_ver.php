<?php

$emp = (isset($params[0])) ? $params[0] : $gestion_base;

// Obtiene el rango de fechas
$gestion = date('Y');
$gestion_base = date('Y-m-d');
//$gestion_base = ($gestion - 16) . date('-m-d');
$gestion_limite = ($gestion + 16) . date('-m-d');

$duplicados=$db->query("SELECT * FROM(SELECT t.*, CONCAT(e.nombres, e.paterno) as vendedor, CONCAT(ed.nombres, ed.paterno) as distribuidor, IF((COUNT(id_egreso) > 1), 1, 0) as uno FROM tmp_egresos t LEFT JOIN sys_empleados e ON e.id_empleado = t.empleado_id LEFT JOIN sys_empleados ed ON ed.id_empleado = t.distribuidor_id WHERE distribuidor_id = '$emp' GROUP BY id_egreso, fecha_egreso, hora_egreso, monto_total, distribuidor_estado) as v WHERE v.uno > 0")->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

?>
<?php require_once show_template('header-configured'); ?>
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
		<b>Ver duplicado</b>
	</h3>
</div>
<div class="panel-body">
    <table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
        <thead>
        <tr class="active">
            <th class="text-nowrap">#</th>
            <th class="text-nowrap">Venta</th>
            <th class="text-nowrap">Fecha venta</th>
            <th class="text-nowrap">Vendedor</th>
            <th class="text-nowrap">Cliente</th>                   
            <th class="text-nowrap">Nit</th>                   
            <th class="text-nowrap">Monto</th>                   
            <th class="text-nowrap">Registros</th>
            <th class="text-nowrap">Distribuidor</th>
            <th class="text-nowrap">Fecha entrega</th>
            <th class="text-nowrap">Productos</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
        </tr>
        </thead>
        <tfoot>
        <tr class="active">
            <th class="text-nowrap text-middle" data-datafilter-filter="true">#</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Venta</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha venta</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Vendedor</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>                   
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Nit</th>                   
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Monto</th>                   
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Registros</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Distribuidor</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha entrega</th>
            <th class="text-nowrap text-middle" data-datafilter-filter="true">Productos</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($duplicados as $nro => $duplicado) { 
                $productos = $db->query("select GROUP_CONCAT('(', a.cantidad, ')', b.nombre SEPARATOR ' ||| ') as prods from tmp_egresos_detalles a left join inv_productos b on b.id_producto = a.producto_id where tmp_egreso_id = ".$duplicado['id_tmp_egreso'])->fetch_first();
            ?>
            <tr>
                <td class="text-nowrap"><?= $nro + 1  ?></td>
                <td class="text-nowrap"><?= $duplicado['nro_factura']; ?></td>
                <td class="text-nowrap"><?= $duplicado['fecha_egreso']; ?></td>
                <td class="text-nowrap"><?= $duplicado['vendedor']; ?></td>
                <td class="text-nowrap"><?= $duplicado['nombre_cliente']; ?></td>                   
                <td class="text-nowrap"><?= $duplicado['nit_ci']; ?></td>                   
                <td class="text-nowrap"><?= $duplicado['monto_total']; ?></td>                   
                <td class="text-nowrap"><?= $duplicado['nro_registros']; ?></td>
                <td class="text-nowrap"><?= $duplicado['distribuidor']; ?></td>
                <td class="text-nowrap"><?= $duplicado['distribuidor_fecha']; ?></td>
                <td class="text-nowrap"><?= $productos['prods']; ?></td>
                <td>
                    <a href="?/distribuidor/eliminar_duplicado/<?= $duplicado['id_tmp_egreso']; ?>/<?= $emp; ?>" data-toggle="tooltip" data-title="Eliminar empleado" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
    <script src="<?= js; ?>/jquery.dataTables.min.js"></script>
    <script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.min.js"></script>
    <script src="<?= js; ?>/jquery.form-validator.es.js"></script>
    <script src="<?= js; ?>/jquery.base64.js"></script>
    <script src="<?= js; ?>/pdfmake.min.js"></script>
    <script src="<?= js; ?>/vfs_fonts.js"></script>
    <script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
            
            var table = $('#table').DataFilter({
                filter: false,
                name: 'empleados',
                reports: 'xls|doc|pdf|html'
            });
            
            $('[data-eliminar]').on('click', function (e) {
        		e.preventDefault();
        		var url = $(this).attr('href');
        		bootbox.confirm('Está seguro que desea eliminar la venta duplicada?', function (result) {
        			if(result){
        				window.location = url;
        			}
        		});
        	});
            
        });
    </script>
<?php require_once show_template('footer-configured'); ?>