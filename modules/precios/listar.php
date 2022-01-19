<?php

// Obtiene los productos
$productos = $db->select('z.*, a.unidad as unidad, b.categoria as categoria')->from('inv_productos z')->join('inv_unidades a', 'z.unidad_id = a.id_unidad', 'left')->join('inv_categorias b', 'z.categoria_id = b.id_categoria', 'left')->where('z.promocion','')->order_by('z.id_producto')->fetch();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

//otros precios
$otro = $db->select('*')->from('inv_asignaciones')->fetch();

// Obtiene el modelo unidades
$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_asignar = true;
$permiso_fijar = false;
$permiso_quitar = true;

$almacenes=$db->from('inv_almacenes')->order_by('id_almacen')->fetch();

?>
<?php require_once show_template('header-advanced'); ?>
<style>
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
<?php if ($message = get_notification()) : ?>
    <div class="alert alert-<?= $message['type']; ?>" data-alert="true">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong><?= $message['title']; ?></strong>
        <p><?= $message['content']; ?></p>
    </div>
<?php endif ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista de precios</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if ($permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para imprimir el informe general hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<a href="?/precios/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Código</th>
                <th class="text-nowrap">Nombre</th>
                <th class="text-nowrap">Nombre factura</th>
                <th class="text-nowrap">Descripción</th>
                <!--th class="text-nowrap">Cantidad caja</th>
				<th class="text-nowrap">Precio actual <?php //escape($moneda); ?></th-->
				<th class="text-nowrap">Categoría</th>
				<?php
					foreach($almacenes as $nro => $almacen):
				?>
				<th class="text-nowrap"><?=$almacen['almacen']?></th>
				<?php
					endforeach;
				?>
                <th class="text-nowrap">Otro precio <?= escape($moneda); ?></th>
				<?php if ($permiso_ver || $permiso_cambiar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre factura</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
                <!--th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad caja</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio actual <?php // escape($moneda); ?></th-->
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<?php
					foreach($almacenes as $nro => $almacen):
				?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false"><?=$almacen['almacen']?></th>
				<?php
					endforeach;
				?>
                <th class="text-nowrap text-middle" data-datafilter-filter="false">Otro precio <?= escape($moneda); ?></th>
				<?php if ($permiso_ver || $permiso_cambiar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen productos registrados en la base de datos.</p>
	</div>
	<?php } ?>
</div>

<!-- Inicio modal precio-->
<?php if ($permiso_cambiar) { ?>
<div id="modal_precio" class="modal fade">
	<div class="modal-dialog">
		<form id="form_precio" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Actualizar precio</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Código:</label>
							<p id="codigo_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Precio actual <?= escape($moneda); ?>:</label>
							<p id="actual_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_precio">Precio nuevo <?= escape($moneda); ?>:</label>
							<input type="text" value="" id="producto_precio" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" name="id_asignacion_precio" value="" id="id_asignacion_precio"  class="translate">
							<input type="text" value="" id="nuevo_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000],float">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary">
					<span class="glyphicon glyphicon-ok"></span>
					<span>Guardar</span>
				</button>
				<button type="button" class="btn btn-default" data-cancelar="true">
					<span class="glyphicon glyphicon-remove"></span>
					<span>Cancelar</span>
				</button>
			</div>
			<div id="loader_precio" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
<?php } ?>
<?php if ($permiso_asignar) : ?>
    <div id="modal_asignar" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" id="form_asignar" class="modal-content loader-wrapper" autocomplete="off">
                <input type="hidden" name="<?php // $csrf; ?>">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Asignar unidad</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unidad_id_asignar" class="control-label">Unidad de venta:</label>
                        <select name="unidad_id" id="unidad_id_asignar" class="form-control" data-validation="required">
                            <option value='' selected='selected' disabled>Seleccionar</option>
                            <?php foreach ($unidades as $unidad) : ?>
                                <option value="<?= $unidad['id_unidad']; ?>"><?= escape($unidad['unidad']); ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="producto_precio" class="control-label">
                            <span>Precio de venta:</span>
                            <span class="text-primary"><?= $moneda; ?></span>
                        </label>
                        <input type="text" value="" name="precio" id="producto_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000],float">
                    </div>
                    <div class="form-group">
                        <label for="tamano" class="control-label">
                            <span>Cantidad de unidades:</span>
                            <span class="text-primary"></span>
                        </label>
                        <input type="text" value="" name="tamano" id="tamano" class="form-control" data-validation="number" data-validation-optional="true">
                    </div>
                    <div class="form-group">
                        <label for="observacion_asignar" class="control-label">Observación:</label>
                        <textarea name="observacion" id="observacion_asignar" class="form-control" rows="4" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        <span>Guardar</span>
                    </button>
                    <button type="reset" class="btn btn-default">
                        <span class="glyphicon glyphicon-refresh"></span>
                        <span>Restablecer</span>
                    </button>
                </div>
                <div id="loader_asignar" class="loader-wrapper-backdrop">
                    <span class="loader"></span>
                </div>
            </form>
        </div>
    </div>
<?php endif ?>
<!-- Fin modal precio-->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters2.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script>
$(function () {
    <?php if ($permiso_quitar) : ?>
		$(document).on('click', '[data-quitar]', function(e) {
		//var $quitar = $('[data-quitar]');
		//$quitar.on('click', function (e) {
			e.preventDefault();
			var href = $(this).attr('href');
			var csrf = '<?php $csrf; ?>';
			console.log(href);
			bootbox.confirm('Está seguro que desea eliminar la unidad?', function (result) {
				if (result) {
					$.request(href, csrf);
				}
			});
		});
    <?php endif ?>
	<?php if ($permiso_cambiar) { ?>
	var $modal_precio = $('#modal_precio'),
		$form_precio = $('#form_precio'),
		$loader_precio = $('#loader_precio');

	$form_precio.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_precio.on('hidden.bs.modal', function () {
		$form_precio.trigger('reset');
	});

	$modal_precio.on('shown.bs.modal', function () {
		$modal_precio.find('.form-control:first').focus();
	});

	$modal_precio.find('[data-cancelar]').on('click', function () {
		$modal_precio.modal('hide');
	});

	$(document).on('click', '[data-cambiar]', function(e) {
		//$('[data-cambiar]').on('click', function (e) {
		e.preventDefault();
		let id_asignacion = $(this).attr('data-cambiar');
		let id_producto = $(this).attr('data-producto');
		//console.log(id_producto, id_asignacion);
		let codigo = $.trim($('[data-codigo=' + id_producto + ']').first().text());
		let precio = $.trim($('[data-precio=' + id_producto + ']').text());
		var precio_obtenido = '';
		let precio_editado = $('[data-precio=' + id_producto + ']').map(function(){			
			let asignacion_precio = $.trim($(this).data('asignacion-precio'));

			if (id_asignacion == asignacion_precio && asignacion_precio > 0) {
				precio_obtenido = $(this).first().text();	
				$('#actual_precio').text(precio_obtenido);			
			}else if(id_asignacion == '0' && asignacion_precio == 'P') {
				precio_obtenido = $(this).first().text();		
				$('#actual_precio').text(precio_obtenido);										
			}
		});
		
		let valor = (id_asignacion > 0) ? id_asignacion : ((id_asignacion == '0') ? 'P': 0);
		console.log(valor);

		$('#id_asignacion_precio').val(valor);			
		$('#producto_precio').val(id_producto);
		$('#codigo_precio').text(codigo);
		//$('#actual_precio').text(precio_editado);
		$modal_precio.modal({ 
			backdrop: 'static'
		});
	});
	<?php } ?>

    <?php if ($permiso_asignar) : ?>
    var $modal_asignar = $('#modal_asignar'),
		$loader_asignar = $('#loader_asignar'),
		$form_asignar = $('#form_asignar'),
		$unidad_id_asignar = $('#unidad_id_asignar'),
		$precio_asignar = $('#precio_asignar');

	$(document).on('click', '[data-asignar]', function(e) {
	//var $asignar = $('[data-asignar]');
    //$asignar.on('click', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
		//
		$.validate({
			form: '#form_asignar',
			modules: 'basic'
		});
		$form_asignar.attr('action', href);
		//
        $modal_asignar.modal('show');
    });


    $unidad_id_asignar.selectize({
        create: false,
        createOnBlur: false,
        maxOptions: 7,
        persist: false,
        onInitialize: function () {
            $unidad_id_asignar.show().addClass('selectize-translate');
        },
        onChange: function () {
            $unidad_id_asignar.trigger('blur');
        },
        onBlur: function () {
            $unidad_id_asignar.trigger('blur');
        }
    });

    $form_asignar.on('reset', function () {
        $unidad_id_asignar.get(0).selectize.clear();
    });
    $modal_asignar.on('hidden.bs.modal', function () {
        $form_asignar.trigger('reset');
        $loader_asignar.show();
    }).on('shown.bs.modal', function () {
        $loader_asignar.hide();
        $precio_asignar.trigger('focus');
    });
    <?php endif ?>

	<?php if ($productos) { ?>
	$loader_mostrar = $('#loader_precio')
    let table=$('#table').DataFilter({
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
                url: '?/precios/listar_producto',
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
                //console.log(aData);
                $(nRow).attr('data-producto',aData[0]);
                $('td', nRow).eq(0).addClass('text-nowrap');
				$('td', nRow).eq(1).addClass('text-nowrap').attr('data-codigo',aData[14]);
				$('td', nRow).eq(2).addClass('width-lg');
				$('td', nRow).eq(3).addClass('width-lg');
				$('td', nRow).eq(4).addClass('text-nowrap');
				//$('td', nRow).eq(5).addClass('text-nowrap');
				//$('td', nRow).eq(6).addClass('text-nowrap').attr('data-precio',aData[14]);
				$('td', nRow).eq(7).addClass('text-nowrap');
				
				for(let i=8;i<aData.length-3;++i){
					$('td', nRow).eq(i).addClass('text-nowrap text-right');
				}
				
				$('td', nRow).eq(aData.length-3).addClass('text-nowrap');
				$('td', nRow).eq(aData.length-2).addClass('text-nowrap');
            }
        }
    });
	<?php
	}
	if ($permiso_cambiar) { ?>
	$.validate({
		form: '#form_precio',
		modules: 'basic',
		onSuccess: function () {
			var producto = $('#producto_precio').val();
			var precio = $('#nuevo_precio').val();
			var asignacion_precio = $('#id_asignacion_precio').val();

			$loader_precio.fadeIn(100);

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/precios/cambiar',
				data: {
					id_producto: producto,
					precio: parseFloat(precio).toFixed(2), 
					id_asignacion_precio: asignacion_precio
				}
			}).done(function (producto) {

				//var cell = table.cell($('[data-producto=' + producto.producto_id + ']'));
				//let tabla = document.getElementById("table").getElementsByTagName("tbody").getElementsByTagName("tr");
				//cell.data(producto.precio).draw();
				$.notify({
					message: 'El precio del producto se actualizó correctamente.'
				}, {
					type: 'success'
				});

				
				location.reload();
				
			}).fail(function (e) {
				console.log(e);
				$.notify({
					title: '<strong>Advertencia!</strong>',
					message: '<div>Ocurrió un problema y el precio del producto no se actualizó correctamente.</div>'
				}, {
					type: 'danger'
				});
			}).always(function () {
				$loader_precio.fadeOut(100, function () {
					$modal_precio.modal('hide');
				});				
			});
		}
	});
	<?php } ?>
});

/* let tabla = document.querySelectorAll("table tbody").item(0);
let fila =  tabla.getElementsByTagName("tr");
let columnas =  fila.getElementsByTagName("td");
console.log(columnas);  */

</script>
<?php require_once show_template('footer-advanced'); ?>