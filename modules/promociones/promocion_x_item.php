<?php
// Obtiene los formatos para la fecha
$formato_textual = get_date_textual($_institution['formato']);
$formato_numeral = get_date_numeral($_institution['formato']);

	// Obtiene la moneda oficial
	$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
	$moneda = ($moneda) ? '(' . escape($moneda['sigla']) . ')' : '';
	// Obtiene los permisos
	$permisos = explode(',', permits);
	// Almacena los permisos en variables
	$permiso_crear = true;
	$permiso_editar = true;
	$permiso_ver = true;
	$permiso_eliminar = true;
	$permiso_imprimir = true;
	$permiso_cambiar = true;
	$permiso_distribuir = true;
	$permiso_promocion = true;
	$permiso_fijar = false;
	$permiso_quitar = true;
	$permiso_ver_precio = true;
	$permiso_asignar_precio = true;
	$unidades = $db->from('inv_unidades')->order_by('unidad')->fetch();
?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Productos</strong>
	</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos productos hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_promocion) { ?>
			<a href="?/promociones/seleccionar_almacen" class="btn btn-warning"><i class="glyphicon glyphicon-star"></i><span class="hidden-xs"> Promociones</span></a>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php } ?>
	<?php if (isset($_SESSION[temporary])) { ?>
	<div class="alert alert-<?= $_SESSION[temporary]['alert']; ?>">
		<button type="button" class="close" data-dismiss="alert">&times;</button>
		<strong><?= $_SESSION[temporary]['title']; ?></strong>
		<p><?= $_SESSION[temporary]['message']; ?></p>
	</div>
	<?php unset($_SESSION[temporary]); ?>
	<?php } ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover" width="100%">
		<thead>
			<tr class="active">
				<th class="text-nowrap text-middle width-collapse" width="2%">#</th>
				<th class="text-nowrap text-middle width-collapse">Imagen</th>
				<th class="text-nowrap text-middle width-collapse">Código</th>
				<!--<th class="text-nowrap text-middle width-collapse">Código de barras</th>-->
				<th class="text-nowrap text-middle">Nombre del producto</th>
				<th class="text-nowrap text-middle">Nombre en la factura</th>
                <th class="text-nowrap text-middle width-collapse">Categoria</th>
                <th class="text-nowrap text-middle width-collapse">Fecha Limite</th>
                <th class="text-nowrap text-middle width-collapse">Descripción</th>
                <th class="text-nowrap text-middle width-collapse">Precio actual <?= $moneda; ?></th>
                <th class="text-nowrap text-middle width-collapse">Unidad</th>
                <th class="text-nowrap text-middle width-collapse">Cantidad caja</th>
                <th class="text-nowrap text-middle width-collapse">Otro precio <?= $moneda; ?></th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
				<th class="text-nowrap text-middle width-collapse" >Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false" width="2%">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Imagen</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Código</th>
				<!--<th class="text-nowrap text-middle" data-datafilter-filter="true">Código de barras</th>-->
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre del producto</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre en la factura</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Categoria</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha Limite</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripción</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Precio actual <?= $moneda; ?></th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Unidad</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Cantidad caja</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="false">Otro precio <?= $moneda; ?></th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_cambiar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
		</tbody>
	</table>
</div>

<!-- Inicio modal precio-->
<?php if ($permiso_cambiar) { ?>
<div id="modal_fecha" class="modal fade">
	<div class="modal-dialog">
		<form id="form_fecha" class="modal-content loader-wrapper">
			<div class="modal-header">
				<h4 class="modal-title">Actualizar fecha limite</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Promocion:</label>
							<p id="codigo_promocion" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Fecha actual:</label>
							<p id="actual_fecha" class="form-control-static"></p>
						</div>
					</div>
					<div class="col-sm-12">
						<div class="form-group">
							<label for="nuevo_precio">Nueva fecha:</label>
							<input type="text" value="" id="producto_promocion" class="translate" tabindex="-1" data-validation="required number">
							<input type="text" value="" id="nueva_fecha" class="form-control" autocomplete="off" data-validation="required">
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
			<div id="loader_fecha" class="loader-wrapper-backdrop occult">
				<span class="loader"></span>
			</div>
		</form>
	</div>
</div>
    
<?php } ?>
<!-- Fin modal precio-->

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
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/selectize.min.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.min.js"></script>
<script src="<?= js; ?>/jquery.form-validator.es.js"></script>
<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
    <script src="<?= js; ?>/selectize.min.js"></script>
    <script src="<?= js; ?>/bootstrap-datetimepicker.min.js"></script>
<script>
$(function () {
    //var $quitar = $('[data-quitar]');
    <?php if ($permiso_quitar) : ?>
    //$quitar.on('click', function (e) {
	$(document).on('click', '[data-quitar]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var csrf = '<?= $csrf; ?>';
        bootbox.confirm('Está seguro que desea eliminar la unidad?', function (result) {
            if (result) {
                $.request(href, csrf);
            }
        });
    });
    <?php endif ?>

	<?php if ($permiso_eliminar) { ?>
	//$('[data-eliminar]').on('click', function (e) {
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
    //$('[data-activar]').on('click', function (e) {
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
		//$asignar = $('[data-asignar]');
    //$asignar.on('click', function (e) {
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
		//$asignar_precio = $('[data-asignar-precio]');
    //$asignar_precio.on('click', function (e) {
	$(document).on('click', '[data-asignar-precio]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $form_asignar_precio.attr('action', href);
        $modal_asignar_precio.modal('show');
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
					window.location = '?/promociones/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_cambiar) { ?>
	var $modal_fecha = $('#modal_fecha');
	var $form_fecha = $('#form_fecha');
	var $loader_fecha = $('#loader_fecha');

	$form_fecha.on('submit', function (e) {
		e.preventDefault();
	});

	$modal_fecha.on('hidden.bs.modal', function () {
		$form_fecha.trigger('reset');
	});

	$modal_fecha.on('shown.bs.modal', function () {
		$modal_fecha.find('.form-control:first').focus();
	});

	$modal_fecha.find('[data-cancelar]').on('click', function () {
		$modal_fecha.modal('hide');
	});

    $('#nueva_fecha').datetimepicker({
        format: '<?= strtoupper($formato_textual); ?>'
    }).on('dp.change', function () {
        $(this).trigger('blur');
    });

	$(document).on('click', '[data-actualizar]', function(e) {
	//$('[data-actualizar]').on('click', function (e) {

		e.preventDefault();
		var id_producto = $(this).attr('data-actualizar');
		var codigo = $.trim($('[data-codigo=' + id_producto + ']').text());
		var precio = $.trim($('[data-fecha=' + id_producto + ']').text());

        $('#producto_fecha').val(id_producto);
        $('#producto_promocion').val(id_producto);
		$('#codigo_promocion').text(codigo);
		$('#actual_fecha').text(precio);
		
		$modal_fecha.modal({
			backdrop: 'static'
		});
	});
	<?php }
		$url = institucion . '/' . $_institution['imagen_encabezado'];
		$image = file_get_contents($url);
		if ($image !== false){
			$imag = 'data:image/jpg;base64,'.base64_encode($image);
		}   ?>
	$loader_mostrar = $('#loader_mostrar')
    var table = $('#table').DataFilter({
        filter: true,
		name: 'Productos en promocion',
        imag: '<?= imgs . '/logo-color.png'; ?>',
        imag2: '<?= $imag; ?>',
        empresa: '<?= $_institution['nombre']; ?>',
        direccion: '<?= $_institution['direccion'] ?>',
        telefono: '<?= $_institution['telefono'] ?>',
        reports: 'xls|doc|pdf|html',
		size: 8,
        values: {
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: '?/promociones/listar_promocion',
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
				$('td', nRow).eq(3).addClass('text-middle');
				$('td', nRow).eq(4).addClass('text-middle');
				$('td', nRow).eq(5).addClass('text-nowrap text-middle');
				$('td', nRow).eq(6).addClass('text-nowrap text-middle text-right lead').attr('data-fecha',aData[13]);
				$('td', nRow).eq(7).addClass('text-nowrap text-middle ');
				$('td', nRow).eq(8).addClass('text-nowrap text-middle');
				$('td', nRow).eq(9).addClass('text-nowrap text-middle');
				$('td', nRow).eq(10).addClass('text-nowrap text-middle text-right');
				$('td', nRow).eq(11).addClass('text-nowrap text-middle text-right');
				$('td', nRow).eq(12).addClass('text-nowrap text-middle text-right lead');
				$('td', nRow).eq(13).addClass('text-nowrap text-middle');
            }
        }
    });

	$.validate({
		form: '#form_fecha',
		modules: 'basic',
		onSuccess: function () {
            console.log('hola');
			var producto = $('#producto_promocion').val();
			var fecha = $('#nueva_fecha').val();

			$loader_fecha.fadeIn(100);

			$.ajax({
				type: 'post',
				dataType: 'json',
				url: '?/promociones/cambiar',
				data: {
					id_producto: producto,
					fecha: fecha
				}
			}).done(function (producto) {
				$.notify({
					message: 'La fecha limite se actualizó correctamente.'
				}, {
					type: 'success'
				});
                setTimeout("location.href='?/promociones/promocion_x_item'", 800);
			}).fail(function () {
				$.notify({
					message: 'Ocurrió un problema y al guardar no se actualizó correctamente.'
				}, {
					type: 'danger'
				});
			}).always(function () {
				$loader_fecha.fadeOut(100, function () {
					$modal_fecha.modal('hide');
				});
			});
		}
	});
	var $modal_mostrar = $('#modal_mostrar'),
		$loader_mostrar = $('#loader_mostrar'),
		size,
		title,
		image;

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
});
</script>
<?php require_once show_template('footer-configured'); ?>