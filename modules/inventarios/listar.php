<?php

// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

// Obtiene el id_almacen
$id_almacen = (isset($params[0])) ? $params[0] : 0;

// Verifica si hay parametros
if ($id_almacen == 0) {
	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();
} else {
	// Obtiene los id_almacen
	$id_almacen = explode('-', $id_almacen);

	// Obtiene los almacenes
	$almacenes = $db->from('inv_almacenes')->where_in('id_almacen', $id_almacen)->order_by('id_almacen')->fetch();
}

// Verifica si existen almacenes
if (!$almacenes) {
	// Error 404
	require_once not_found();
	exit;
}


// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')
			 ->where('oficial', 'S')
			 ->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los ubicaciones
$ubicaciones = $db->from('inv_almacenes')->order_by('id_almacen')->fetch();

?>
<?php require_once show_template('header-advanced'); ?>
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
</style>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Inventario general de productos</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para cambiar de almacén hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<button type="button" class="btn btn-primary" data-cambiar="true">
				<span class="glyphicon glyphicon-refresh"></span>
				<span>Cambiar</span>
			</button>
		</div>
	</div>
	<hr>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover table-xs">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
                <th class="text-nowrap">Nombre del producto</th>
                <th class="text-nowrap">Nombre factura</th>
                <th class="text-nowrap">Descripción</th>
				<th class="text-nowrap">Categoría</th>
				<th class="text-nowrap">Mínimo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap"><?= $almacen['almacen']; ?></th>
				<?php } ?>
				<th class="text-nowrap">Inv. total</th>
				<th class="text-nowrap">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap">Inv. valor <?= escape($moneda); ?></th>
				<th class="text-nowrap">U. Costo <?= escape($moneda); ?></th>
				<th class="text-nowrap">Inv. valor <?= escape($moneda); ?></th>
				<th class="text-nowrap">Unidad</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre factura</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Mínimo</th>
				<?php foreach ($almacenes as $nro => $almacen) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false"><?= $almacen['almacen']; ?></th>
				<?php } ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Inv. total</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Inv. valor <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">U. Costo <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Inv. valor <?= escape($moneda); ?></th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Unidad</th>
			</tr>
		</tfoot>
		<tbody>
		</tbody>
	</table>

	<div class="well">
        <p class="lead margin-none">
            <b>Total(Precio):</b>
            <u id="total">0.00</u>
            <span><?= escape($moneda); ?></span>
        </p>
        <p class="lead margin-none">
            <b>Total(Costo):</b>
            <u id="total2">0.00</u>
            <span><?= escape($moneda); ?></span>
        </p>
    </div>
</div>

<!-- Inicio modal almacen-->
<div id="modal_almacen" class="modal fade">
	<div class="modal-dialog">
		<form id="form_almacen" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Cambiar almacén</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
							<?php foreach ($ubicaciones as $nro => $almacen) { ?>
							<div class="checkbox">
								<label>
									<input type="checkbox" value="<?= escape($almacen['id_almacen']); ?>" data-seleccion="<?= escape($almacen['id_almacen']); ?>">
									<span><?= escape($almacen['almacen']); ?></span>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary" data-aceptar="true">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Aceptar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_almacen" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<!-- Fin modal almacen-->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters2.min.js"></script>
<script>
$(function () {
    <?php
		$url=institucion.'/'.$_institution['imagen_encabezado'];
        $image=file_get_contents($url);
        if($image!==false):
            $imag='data:image/jpg;base64,'.base64_encode($image);
		endif;
	?>
	$loader_mostrar = $('#loader_mostrar')
    var table = $('#table').DataFilter({
        filter: true,
		name: 'Inventario de Productos',
        imag: '<?= imgs.'/logo-color.png'; ?>',
        empresa: '<?= $_institution['nombre']; ?>',
        direccion: '<?= $_institution['direccion'] ?>',
        telefono: '<?= $_institution['telefono'] ?>',
        reports: 'excel|word|pdf|html',
		size: 8,
        values: {
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: '?/inventarios/listar_producto',
                type: 'post',
				complete:function(){
					var suma = 0;
					var suma2 = 0;
					$('[data-total]:visible').each(function (i) {
						var total = parseFloat($(this).attr('data-total'));
						suma = suma + total;
					});
					$('[data-total2]:visible').each(function (i) {
						var total = parseFloat($(this).attr('data-total2'));
						suma2 = suma2 + total;
					});
					$('#total').text(suma.toFixed(3));
					$('#total2').text(suma2.toFixed(3));
					$loader_mostrar.show();
				}
				/*beforeSend:function(){
                    $loader_mostrar.show();
                },
                error: function () {},*/
            },
            drawCallback: function(settings) {
                $loader_mostrar.hide();
            },
            createdRow:function(nRow, aData, iDisplayIndex){
                $(nRow).attr('data-producto',aData[0]);
                $('td', nRow).eq(0).addClass('text-nowrap');
				$('td', nRow).eq(1).addClass('text-nowrap');
				$('td', nRow).eq(2).addClass('width-lg');
				$('td', nRow).eq(3).addClass('width-lg');
				$('td', nRow).eq(4).addClass('width-lg');
				$('td', nRow).eq(5).addClass('text-nowrap');
				$('td', nRow).eq(7).addClass('text-nowrap text-right');
				for(let i=8;i<aData.length-6;++i)
					$('td', nRow).eq(i).addClass('text-nowrap text-right');
				$('td', nRow).eq(aData.length-6).addClass('text-nowrap text-right');
				$('td', nRow).eq(aData.length-5).addClass('text-nowrap text-right').attr('data-total',aData[aData.length-5]);
				$('td', nRow).eq(aData.length-4).addClass('text-nowrap text-right');
				$('td', nRow).eq(aData.length-3).addClass('text-nowrap text-right').attr('data-total2',aData[aData.length-3]);
				$('td', nRow).eq(aData.length-2).addClass('text-nowrap');
            }
        }
    });
	var $fields = $('#fields_0');
	//$fields.find(':checkbox[value="3"]').trigger('click');
	//$fields.find(':checkbox[value="5"]').trigger('click');

	var $modal_almacen = $('#modal_almacen');
	var $form_almacen = $('#form_almacen');

	$form_almacen.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_almacen.find('[data-cancelar]').on('click', function () {
		$modal_almacen.modal('hide');
	});

	$modal_almacen.find('[data-aceptar]').on('click', function () {
		var almacenes = [];

		$('[data-seleccion]:checked').each(function () {
			almacenes.push($(this).attr('data-seleccion'));
		});

		almacenes = almacenes.join('-');
		$modal_almacen.modal('hide');
		
		if (almacenes != '') {
			window.location = '?/inventarios/listar/' + almacenes;
		} else {
			window.location = '?/inventarios/listar';
		}
		
	});

	$('[data-cambiar]').on('click', function () {
		$modal_almacen.modal({
			backdrop: 'static'
		});
	});
});
</script>
<?php require_once show_template('footer-advanced'); ?>