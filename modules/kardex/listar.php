<?php

// Obtiene los almacenes
$almacenes = $db->get('inv_almacenes');

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista general de productos</strong>
	</h3>
</div>
<div class="panel-body">
	<table id="table" class="table table-bordered table-condensed table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
                <th class="text-nowrap">Nombre del producto</th>
                <th class="text-nowrap">Nombre factura</th>
                <th class="text-nowrap">Descripción</th>
				<th class="text-nowrap">Categoría</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap"><?= $almacen['almacen']; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre factura</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Categoría</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false"><?= $almacen['almacen']; ?></th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
		</tbody>
	</table>
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
$(function () {	
	<?php
		$url = (institucion.'/'.$_institution['imagen_encabezado'] != '') ? institucion.'/'.$_institution['imagen_encabezado'] : institucion.'/logo_institution.jpg';
		$url = file_exists($url) ? $url : institucion.'/logo_institution.jpg'; 
        $image=file_get_contents($url);
        if($image!==false):
            $imag='data:image/jpg;base64,'.base64_encode($image);
		endif;
	?>
	$loader_mostrar = $('#loader_mostrar')
    var table = $('#table').DataFilter({
        filter: true,
		name: 'Kardex Producto',
		imag: '<?= imgs.'/logo-color.png' ?>',
		imag2: '<?= $imag ?>',
		empresa: '<?= $_institution['nombre']; ?>',
		direccion: '<?= $_institution['direccion'] ?>',
		telefono: '<?= $_institution['telefono'] ?>',
		reports: 'xls|doc|pdf|html',
		size: 8,
        values: {
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: '?/kardex/listar_producto',
                type: 'post',
                beforeSend:function(){
                    $loader_mostrar.show();
                },
                error: function () {}
            },
            drawCallback: function(settings) {
                $loader_mostrar.hide();
            },
            createdRow:function(nRow, aData, iDisplayIndex){
                $(nRow).attr('data-producto',aData[0]);
                $('td', nRow).eq(0).addClass('text-nowrap');
				$('td', nRow).eq(1).addClass('text-nowrap');
				$('td', nRow).eq(2).addClass('text-nowrap');
				$('td', nRow).eq(3).addClass('text-nowrap');
				$('td', nRow).eq(4).addClass('text-nowrap');
				$('td', nRow).eq(5).addClass('text-nowrap');
				for(i=6;i<aData.length;++i)
					$('td', nRow).eq(i).addClass('text-nowrap');
            }
        }
    });
});
</script>
<?php require_once show_template('footer-configured'); ?>