<?php

// Obtiene los productos
$productos = $db->select('p.*, u.unidad, c.categoria')->from('inv_productos p')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->join('inv_categorias c', 'p.categoria_id = c.id_categoria', 'left')->where('u.unidad!=','')->order_by('p.id_producto')->fetch_first();

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();

$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = in_array('crear', $permisos);
$permiso_editar = in_array('editar', $permisos);
$permiso_ver = in_array('ver', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_cambiar = in_array('cambiar', $permisos);
$permiso_distribuir = in_array('activar', $permisos);
$permiso_promocion = in_array('promocion', $permisos);
$permiso_fijar = false;
$permiso_quitar = in_array('quitar', $permisos);
$permiso_ver_precio = true;
$permiso_asignar_precio = true;
$permiso_eliminados = in_array('eliminados', $permisos);

$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Catalogo de productos</strong>
	</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $productos)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos productos hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
		<?php if ($permiso_eliminados) { ?>
			<a href="?/productos/eliminados" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i><span class="hidden-xs"> Eliminados</span></a>
			<?php } ?>
			<?php if ($permiso_promocion) { ?>
			<a href="?/promociones/promocion" target="_blank" class="btn btn-warning"><i class="glyphicon glyphicon-star"></i><span class="hidden-xs"> Promociones</span></a>
			<?php } ?>
			<?php if ($permiso_imprimir) { ?>
			<a href="?/productos/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
				<a href="?/productos/crear" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Nuevo Producto (Alt+N)"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= ($_SESSION[temporary]['alert']) ? $_SESSION[temporary]['alert'] : $_SESSION[temporary]['type']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= ($_SESSION[temporary]['message']) ? $_SESSION[temporary]['message'] : $_SESSION[temporary]['content']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<?php if ($productos) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover" width='100%'>
		<thead>
			<tr class="active">
				<th class="text-nowrap text-middle width-collapse">#</th>
				<th class="text-nowrap text-middle width-collapse">Imagen</th>
				<th class="text-nowrap text-middle width-collapse">Código</th>
				<th class="text-nowrap text-middle">Nombre del producto</th>
				<!--<th class="text-nowrap text-middle">Nombre en la factura</th>-->
                <th class="text-nowrap text-middle width-collapse">Categoria</th>
                <th class="text-nowrap text-middle width-collapse">Descripción</th>
                <th class="text-nowrap text-middle width-collapse">Unidad</th>
                <th class="text-nowrap text-middle width-collapse">Cantidad caja</th>
				<th class="text-nowrap text-middle width-collapse">Cantidad mínima</th>
                <th class="text-nowrap text-middle width-collapse">Precio actual <?= $moneda; ?></th>
                <th class="text-nowrap text-middle width-collapse hidden">Precio sugerido <?= $moneda; ?></th>
				<!-- <th class="text-nowrap text-middle width-collapse">Precio <?= $moneda; ?></th> -->
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
				<th class="text-nowrap text-middle width-collapse">Opciones</th>
				
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Imagen</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
				<!--<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre en la factura</th>-->
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad caja</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad mínima</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio actual <?= $moneda; ?></th>
                <th class="text-nowrap text-middle hidden" data-datafilter-filter="true">Precio sugerido <?= $moneda; ?></th>
                <!-- <th class="text-nowrap text-middle" data-datafilter-filter="false">Precio <?= $moneda; ?></th> -->
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
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
		<p>No existen productos registrados en la base de datos, para crear nuevos productos hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
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
							<label class="control-label">Precio actual <?= $moneda; ?>:</label>
							<p id="actual_precio" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_precio">Precio nuevo <?= $moneda; ?>:</label>
							<input type="text" value="" id="producto_precio" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" value="" id="nuevo_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0;10000000],float">
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

    <div id="modal_asignar" class="modal fade" tabindex="-1">
        <?php $grupos = $db->select('grupo')->from('inv_productos')->group_by('grupo')->where('grupo!=','')->fetch(); ?>
        <div class="modal-dialog">
            <form method="post" id="form_asignar" class="modal-content loader-wrapper" autocomplete="off">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Asignar grupo</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unidad_id_asignar" class="control-label">Grupo:</label>
                        <select name="grupo" id="grupo" class="form-control text-uppercase" data-validation="letternumber" data-validation-allowing="-+./&() " required >
                            <option value="" selected="selected">Seleccionar</option>
                            <?php foreach ($grupos as $grupo) : ?>
                                <option value="<?= $grupo['grupo']; ?>"><?= escape($grupo['grupo']); ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                        <span>Guardar</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
<?php } ?>
<!-- Fin modal precio-->
<?php if ($permiso_asignar_precio) : ?>
    <div id="modal_asignar_precio" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" id="form_asignar_precio" class="modal-content loader-wrapper" autocomplete="off">
                <input type="hidden" name="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Asignar unidad</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unidad_id_asignar_precio" class="control-label">Unidad de venta:</label>
                        <select name="unidad_id" id="unidad_id_asignar_precio" class="form-control" data-validation="required">
                            <option value="" selected="selected">Seleccionar</option>
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
                        <input type="text" value="" name="precio" id="producto_precio" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[0.01;10000.00],float" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="tamano" class="control-label">
                            <span>Cantidad de unidades:</span>
                            <span class="text-primary"></span>
                        </label>
                        <input type="text" value="" name="tamano" id="tamano" class="form-control" autocomplete="off" data-validation="required number" data-validation-allowing="range[1;10000],float" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="observacion_asignar" class="control-label">Observación:</label>
                        <textarea name="observacion" id="observacion_asignar" class="form-control" rows="4" data-validation="letternumber" data-validation-allowing='-+/.,:;@#&"()_\n ' data-validation-optional="true" maxlength="65"></textarea>
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
                <div id="loader_asignar_precio" class="loader-wrapper-backdrop">
                    <span class="loader"></span>
                </div>
            </form>
        </div>
    </div>
<?php endif ?>
    <!-- Fin modal precio-->
<!-- Modal mostrar inicio -->
<div id="modal_mostrar" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content loader-wrapper">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<img src="" class="img-responsive img-rounded" data-modal-image="">
			</div>
			<div id="loader_mostrar" class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
	</div>
</div>
<!-- Modal mostrar fin -->

<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/FileSaver.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/loadingoverlay.min.js"></script>
<script>
$(function () {
    <?php if ($permiso_quitar) : ?>
	$(document).on('click', '[data-quitar]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var csrf = '<?php $csrf; ?>';
        bootbox.confirm('Está seguro que desea eliminar la unidad?', function (result) {
            if (result) {
                $.request(href, csrf);
				// alert('aceptó');
            }
        });
    });
    <?php endif ?>

	<?php if ($permiso_eliminar) { ?>
	$(document).on('click', '[data-eliminar]', function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el producto?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

    <?php if ($permiso_distribuir) { ?>
	$(document).on('click', '[data-activar]', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        bootbox.confirm('Está seguro de quitar del grupo?', function (result) {
            if(result){
                window.location = url;
            }
        });
    });

    var $modal_asignar = $('#modal_asignar'),
		$form_asignar = $('#form_asignar');
	$(document).on('click', '[data-asignar]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $form_asignar.attr('action', href);
        $modal_asignar.modal('show');
    });

    <?php if ($permiso_asignar_precio) : ?>
    var $modal_asignar_precio = $('#modal_asignar_precio'),
		$loader_asignar_precio = $('#loader_asignar_precio'),
		$form_asignar_precio = $('#form_asignar_precio'),
		$unidad_id_asignar_precio = $('#unidad_id_asignar_precio'),
		$precio_asignar_precio = $('#precio_asignar_precio');

	$(document).on('click', '[data-asignar-precio]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $form_asignar_precio.attr('action', href);
        $modal_asignar_precio.modal('show');

		$.validate({
				form: '#form_asignar_precio',
				modules: 'basic'
			});
    });


    $unidad_id_asignar_precio.selectize({
        create: false,
        createOnBlur: false,
        maxOptions: 7,
        persist: false,
        onInitialize: function () {
            $unidad_id_asignar_precio.show().addClass('selectize-translate');
        },
        onChange: function () {
            $unidad_id_asignar_precio.trigger('blur');
        },
        onBlur: function () {
            $unidad_id_asignar_precio.trigger('blur');
        }
    });

    $form_asignar_precio.on('reset', function () {
        $unidad_id_asignar_precio.get(0).selectize.clear();
    });

    $modal_asignar_precio.on('hidden.bs.modal', function () {
        $form_asignar_precio.trigger('reset');
        $loader_asignar_precio.show();
    }).on('shown.bs.modal', function () {
        $loader_asignar_precio.hide();
        $precio_asignar_precio.trigger('focus');
    });
    <?php endif ?>

    var $grupo = $('#grupo');
    $grupo.selectize({
        persist: false,
        createOnBlur: true,
        create: true,
        onInitialize: function () {
            $grupo.css({
                display: 'block',
                left: '-10000px',
                opacity: '0',
                position: 'absolute',
                top: '-10000px'
            });
        }
    });

    <?php } ?>
	
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/productos/crear';
				break;
			}
		}
	});
	<?php } ?>

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

	$(document).on('click', '[data-actualizar]', function(e) {
		e.preventDefault();
		var id_producto = $(this).attr('data-actualizar'),
			codigo = $.trim($('[data-codigo=' + id_producto + ']').text()),
			precio = $.trim($('[data-precio=' + id_producto + ']').text());

		$('#producto_precio').val(id_producto);
		$('#codigo_precio').text(codigo);
		$('#actual_precio').text(precio);
		$modal_precio.modal({
			backdrop: 'static'
		});
	});
	<?php } ?>
	<?php if ($productos) : ?>
	$loader_mostrar = $('#loader_mostrar')
	<?php
		$url=institucion.'/'.$_institution['imagen_encabezado'];
        $image=file_get_contents($url);
        if($image!==false):
            $imag='data:image/jpg;base64,'.base64_encode($image);
		endif;
	?>
    var table = $('#table').DataFilter({
        filter: true,
		name: 'Inventario de Productos',
        imag: '<?= imgs.'/logo-color.png'; ?>',
        empresa: '<?= $_institution['nombre']; ?>',
        direccion: '<?= $_institution['direccion'] ?>',
        telefono: '<?= $_institution['telefono'] ?>',
        reports: 'xls|doc|pdf|html',
		size: 8,
        values: {
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: '?/productos/listar_productos',
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
                $('td', nRow).eq(0).addClass('text-nowrap text-middle text-right');
				$('td', nRow).eq(1).addClass('text-nowrap text-middle text-center');
				$('td', nRow).eq(2).addClass('text-nowrap text-middle').attr('data-codigo',aData[13]);
				$('td', nRow).eq(3).addClass('text-middle text-uppercase');
				$('td', nRow).eq(4).addClass('text-middle');
				$('td', nRow).eq(5).addClass('text-nowrap text-middle');
				$('td', nRow).eq(6).addClass('text-nowrap text-middle');
				$('td', nRow).eq(8).addClass('text-nowrap text-middle');
				$('td', nRow).eq(9).addClass('text-nowrap text-middle');
				$('td', nRow).eq(10).addClass('text-nowrap text-middle text-right');
				$('td', nRow).eq(7).addClass('text-nowrap text-middle text-right').attr('data-precio',aData[13]);
				// $('td', nRow).eq(11).addClass('text-nowrap text-middle text-right');
				$('td', nRow).eq(11).addClass('hidden');
            }
        }
    });

	$.validate({
		form: '#form_precio',
		modules: 'basic',
		onSuccess: function () {
			var producto = $('#producto_precio').val();
			var precio = $('#nuevo_precio').val();

			$loader_precio.fadeIn(100);

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/productos/cambiar',
				data: {
					id_producto: producto,
					precio: parseFloat(precio).toFixed(2)
				}
			}).done(function (producto) {				
				$.trim($('[data-precio=' + producto.producto_id + ']').text(producto.precio));
				$.notify({
					message: 'El precio del producto se actualizó correctamente.'
				}, {
					type: 'success'
				});
			}).fail(function () {
				$.notify({
					message: 'Ocurrió un problema y el precio del producto no se actualizó correctamente.'
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

	var $modal_mostrar = $('#modal_mostrar'), $loader_mostrar = $('#loader_mostrar'), size, title, image;

	$modal_mostrar.on('hidden.bs.modal', function () {
		$loader_mostrar.show();
		$modal_mostrar.find('.modal-dialog').attr('class', 'modal-dialog');
		$modal_mostrar.find('.modal-title').text('');
	}).on('show.bs.modal', function (e) {
		size = $(e.relatedTarget).attr('data-modal-size');
		title = $(e.relatedTarget).attr('data-modal-title');
		image = $(e.relatedTarget).attr('src');
		size = (size) ? 'modal-dialog ' + size : 'modal-dialog';
		title = (title) ? title : 'Imagen';
		$modal_mostrar.find('.modal-dialog').attr('class', size);
		$modal_mostrar.find('.modal-title').text(title);
		$modal_mostrar.find('[data-modal-image]').attr('src', image);
	}).on('shown.bs.modal', function () {
		$loader_mostrar.hide();
	});
	<?php endif ?>
});
</script>
<?php require_once show_template('footer-configured'); ?>