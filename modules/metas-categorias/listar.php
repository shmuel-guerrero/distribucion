<?php

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

$Consulta = $db->query("SELECT m.id_meta_categoria, m.monto, m.fecha_inicio, m.fecha_fin, m.categoria_id, c.categoria, c.descripcion
                        FROM inv_meta_categoria AS m
                        LEFT JOIN inv_categorias AS c ON c.id_categoria = m.categoria_id
                        WHERE CURDATE() BETWEEN m.fecha_inicio AND m.fecha_fin
                        GROUP BY m.id_meta_categoria ORDER BY m.id_meta_categoria DESC")->fetch();
require_once show_template('header-configured');
?>
<div class='panel-heading'>
    <h3 class='panel-title'>
        <span class='glyphicon glyphicon-option-vertical'></span>
        <strong>Metas por categoria</strong>
    </h3>
</div>
<div class='panel-body'>
    <div class='row'>
        <div class='col-sm-8 hidden-xs'>
            <div class='text-label'>Para agregar nuevas metas hacer clic en el siguiente botón: </div>
        </div>
        <div class='col-xs-12 col-sm-4 text-right'>
            <a href='?/metas-categorias/crear' class='btn btn-primary' data-toggle="tooltip" data-placement="top" title="Nueva Meta (Alt+N)"><i class='glyphicon glyphicon-plus'></i><span> Nuevo</span></a>
        </div>
    </div>
    <hr>
    <?php
    if ($Consulta) :
    ?>
        <table id='table' class='table table-bordered table-condensed table-restructured table-striped table-hover'>
            <thead>
                <tr class='active'>
                    <th class='text-nowrap'>#</th>
                    <th class='text-nowrap'>Categoria</th>
                    <th class='text-nowrap'>Descripcion</th>
                    <th class='text-nowrap'>Monto<?= escape($moneda); ?></th>
                    <th class='text-nowrap'>Monto Conjunto<?= escape($moneda); ?></th> 
                    <th class='text-nowrap'>Porcentaje %</th> 
                    <th class='text-nowrap'>Fecha Inicial</th>
                    <th class='text-nowrap'>Fecha Final</th>
                    <th class='text-nowrap'>Opciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr class='active'>
                    <th class='text-nowrap'>#</th>
                    <th class='text-nowrap'>Categoria</th>
                    <th class='text-nowrap'>Descripcion</th>
                    <th class='text-nowrap'>Monto<?= escape($moneda); ?></th>
                    <th class='text-nowrap'>Monto Conjunto<?= escape($moneda); ?></th>                    
                    <th class='text-nowrap'>Porcentaje %</th>                    
                    <th class='text-nowrap'>Fecha Inicial</th>
                    <th class='text-nowrap'>Fecha Final</th>
                    <th class='text-nowrap'>Opciones</th>
                </tr>
            </tfoot>
            <tbody>
                <?php
                foreach ($Consulta as $Fila => $Dato) :
                    $Conseguido=$db->query("SELECT IFNULL(SUM(ed.precio * (ed.cantidad / (IF(asi.cantidad_unidad is null,1,asi.cantidad_unidad)))),0)AS total
                            FROM inv_egresos_detalles AS ed
                            LEFT JOIN inv_asignaciones asi ON asi.producto_id = ed.producto_id AND asi.unidad_id = ed.unidad_id AND asi.visible = 's'
                            LEFT JOIN inv_egresos AS e ON e.id_egreso = ed.egreso_id
                            LEFT JOIN inv_productos AS p ON ed.producto_id=p.id_producto
                            WHERE p.categoria_id='{$Dato['categoria_id']}' AND e.anulado = 0 AND e.estadoe != 0
                            AND e.fecha_egreso BETWEEN '{$Dato['fecha_inicio']}' AND '{$Dato['fecha_fin']}'")->fetch_first();
                    $Conseguido = ($Conseguido['total'] >= 0) ? $Conseguido['total'] : 0;
                    $meta = ($Dato['monto'] >= 0) ? $Dato['monto']: 0;
                    $porcen = ($Conseguido * 100) / $meta;

                ?>
                    <tr>
                        <th class='text-nowrap'><?= $Fila + 1 ?></th>
                        <th class='text-nowrap'><?= $Dato['categoria'] ?></th>
                        <th class='text-nowrap'><?= $Dato['descripcion'] ?></th>
                        <th class='text-nowrap'><?= number_format((($Dato['monto'] >= 0) ? $Dato['monto']: 0),2,'.','') ?></th>
                        <th class='text-nowrap'><?= number_format($Conseguido,2,'.','') ?></th>
                        <th class='text-nowrap'><?= number_format($porcen,2,'.','') ?> %</th>
                        <th class='text-nowrap'><?= escape(date_decode($Dato['fecha_inicio'], $_institution['formato'])) ?></th>
                        <th class='text-nowrap'><?= escape(date_decode($Dato['fecha_fin'], $_institution['formato'])) ?></th>
                        <th class='text-nowrap'>
                            <a href='?/metas-categorias/ver/<?= $Dato['id_meta_categoria'] ?>' data-toggle='tooltip' data-title='Ver Meta' data-ver='true'><i class='glyphicon glyphicon-search'></i></a>
                            <a href='?/metas-categorias/eliminar-<?= $Dato['id_meta_categoria'] ?>' data-toggle='tooltip' data-title='Eliminar Meta' data-eliminar='true'><i class='glyphicon glyphicon-trash'></i></a>
                        </th>
                    </tr>
                <?php
                endforeach;
                ?>
            </tbody>
        </table>
    <?php
    else :
    ?>
        <div class="alert alert-danger">
            <strong>Advertencia!</strong>
            <p>No existen metas registradas en la base de datos, para crear nuevas metas hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
        </div>
    <?php
    endif;
    ?>
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
    $(document).on('click', '[data-eliminar]', function(e) {
        e.preventDefault();
        let fila = this.parentNode.parentNode;
        let url = $(this).attr('href');
        url = url.split('-');
        console.log(url)
        bootbox.confirm('Está seguro que desea eliminar esta meta?', function(result) {
            if (result) {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: url[0]+'-'+url[1],
                    data: {
                        'id_meta': url[2]
                    }
                }).done(function(ruta) {
                    if (ruta) {
                        $.notify({
                            message: 'La ruta fue registrada satisfactoriamente.'
                        }, {
                            type: 'success'
                        });
                        fila.parentNode.removeChild(fila);
                    } else {
                        $.notify({
                            message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente.'
                        }, {
                            type: 'danger'
                        });
                    }
                }).fail(function() {
                    $.notify({
                        message: 'Ocurrió un problema en el proceso, no se puedo guardar los datos, verifique si la se guardó parcialmente.'
                    }, {
                        type: 'danger'
                    });
                });
            }
        });
    });
    $(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/metas-categorias/crear';
				break;
			}
		}
	});
	$(function () {
	   
    	var table = $('#table').DataFilter({
    		filter: true,
    		name: 'reporte_ventas_manuales',
    		reports: 'excel|word|pdf|html'
    	});
    	
    });
</script>
<?php require_once show_template('footer-configured');
