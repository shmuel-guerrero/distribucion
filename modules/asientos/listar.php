<?php

// Obtiene los usuarios
$automaticos = $db->select('*')->from('con_asientos_automaticos a')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_crear = true;
$permiso_editar = true;
$permiso_ver = true;
$permiso_eliminar = true;
$permiso_imprimir = true;
$permiso_activar = true;
$permiso_asignar = true;
$permiso_capturar = true;

?>
<?php require_once show_template('header-configured'); ?>
<link rel="stylesheet" href="<?= css; ?>/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="<?= css; ?>/jquery.Jcrop.min.css">
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Asientos</strong>
	</h3>
</div>
<div class="panel-body">
	<?php if (($permiso_crear || $permiso_imprimir) && ($permiso_crear || $_users)) { ?>
	<div class="row">
		<div class="col-sm-8 hidden-xs">
			<div class="text-label">Para agregar nuevos usuarios hacer clic en el siguiente botón: </div>
		</div>
		<div class="col-xs-12 col-sm-4 text-right">
			<?php if ($permiso_imprimir) { ?>
			<a href="?/usuarios/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>
			<?php } ?>
			<?php if ($permiso_crear) { ?>
			<a href="?/asientos/crear" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span> Nuevo</span></a>
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
	<?php if ($automaticos) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Menu</th>
                <th class="text-nowrap">Estado</th>
                <th class="text-nowrap">Asignar</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_activar || $permiso_asignar || $permiso_capturar) { ?>
				<th class="text-nowrap">Opciones</th>
				<?php } ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Menu</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Estado</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Asignación</th>
				<?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_activar || $permiso_asignar || $permiso_capturar) { ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
				<?php } ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($automaticos as $nro => $automatico) {

                $men = $db->from('con_asientos_menus')->join('con_menus','menu_id = id_menu')->where('automatico_id',$automatico['id_automatico'])->fetch();?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
                <td class="text-nowrap"><?= escape($automatico['titulo_automatico']); ?></td>
                <td class="text-nowrap"><b><?php echo $estado = ($automatico['estado']=='si') ? 'Activo' : 'Inactivo'; ?></b></td>
                <td class="text-nowrap"><b><?php foreach($men as $mens){echo $mens['menu'].'<br>';} ?></b></td>
                <?php if ($permiso_ver || $permiso_editar || $permiso_eliminar || $permiso_activar || $permiso_asignar || $permiso_capturar) { ?>
				<td class="text-nowrap">
					<?php if ($permiso_activar) { ?>   
						<?php if ($automatico['estado'] == 'si') { ?>
						<a href="?/asientos/activar/<?= $automatico['id_automatico']; ?>" class="text-success" data-toggle="tooltip" data-title="Bloquear asiento" data-activar="true"><i class="glyphicon glyphicon-check"></i></a>
						<?php } else { ?>
						<a href="?/asientos/activar/<?= $automatico['id_automatico']; ?>" class="text-danger" data-toggle="tooltip" data-title="Desbloquear asiento" data-activar="true"><i class="glyphicon glyphicon-unchecked"></i></a>
						<?php } ?>
					<?php } ?>
					<?php if ($permiso_editar) { ?>
					<a href="?/asientos/editar/<?= $automatico['id_automatico']; ?>" data-toggle="tooltip" data-title="Editar asiento"><i class="glyphicon glyphicon-edit"></i></a>
					<?php } ?>
					<?php if ($permiso_eliminar && $_user['id_user'] != 1) { ?>
					<a href="?/asientos/eliminar/<?= $automatico['id_automatico']; ?>" data-toggle="tooltip" data-title="Eliminar usuario" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
					<?php } ?>
					<?php if ($permiso_asignar) { ?>
						<a href="?/asientos/listar" data-toggle="tooltip" data-title="Asignar / cambiar menu" data-asignar="<?= $automatico['id_automatico']; ?>"><i class="glyphicon glyphicon-user"></i></a>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen usuarios registrados en la base de datos, para crear nuevos usuarios hacer clic en el botón nuevo o presionar las teclas <kbd>alt + n</kbd>.</p>
	</div>
	<?php } ?>
</div>


<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script src="<?= js; ?>/jquery.resize.min.js"></script>
<script src="<?= js; ?>/jquery.Jcrop.min.js"></script>
<script>
$(function () {
	<?php if ($permiso_crear) { ?>
	$(window).bind('keydown', function (e) {
		if (e.altKey || e.metaKey) {
			switch (String.fromCharCode(e.which).toLowerCase()) {
				case 'n':
					e.preventDefault();
					window.location = '?/usuarios/crear';
				break;
			}
		}
	});
	<?php } ?>

	<?php if ($permiso_activar) { ?>
	$('[data-activar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea cambiar el estado del asiento?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>
	
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el asiento?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_asignar) { ?>
	$('[data-asignar]').on('click', function (e) {
		e.preventDefault();
		var id_user = $(this).attr('data-asignar');
		bootbox.confirm('Desea asignar a este asientos un menu?', function (result) {
			if(result){
				window.location = '?/asientos/asignar/' + id_user;
			}
		});
	});
	<?php } ?>

	<?php if ($permiso_capturar) { ?>
	var $modal_capturar = $('#modal_capturar');

	$('[data-capturar]').on('click', function (e) {
		e.preventDefault();
		var id_user = $(this).attr('data-capturar');
		$('#id_user_capturar').val(id_user);
		$modal_capturar.modal({
			backdrop: 'static'
		});
	});

	$('#img_capturar').resize(function () {
		var estado = $(this).find('img').attr('data-verificar');
		if (estado == 'true') {
			$(this).html('<img src="<?= imgs; ?>/picture.jpg" class="img-responsive" data-verificar="false">');
		}
	});

	$('#form_capturar').on('submit', function (e) {
		var estado, id_user, lienzo, avatar_x, avatar_y, avatar_w, avatar_h;
		estado = $('#img_capturar').find('img').attr('data-verificar');
		id_user = $.trim($('#id_user_capturar').val());
		avatar_x = $.trim($('#avatar_x_capturar').val());
		avatar_y = $.trim($('#avatar_y_capturar').val());
		avatar_w = $.trim($('#avatar_w_capturar').val());
		avatar_h = $.trim($('#avatar_h_capturar').val());
		if (id_user != '') {
			if (estado == 'true') {
				if (avatar_x != '' && avatar_y != '' && avatar_w != '' && avatar_h != '') {
					lienzo = $('#img_capturar').find('img').attr('src');
					$('#avatar_capturar').val(lienzo);
					//$modal_capturar.modal('hide');
				} else {
					e.preventDefault();
					$('#alert_capturar').html('<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Advertencia!</strong><p>Debe definir el tamaño de la avatar, para ello debe arrastrar un marco en la avatar.</p></div>');
				}
			} else {
				e.preventDefault();
				$('#alert_capturar').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Advertencia!</strong><p>Debe capturar una avatar para guardar los cambios.</p></div>');
			}
		} else {
			e.preventDefault();
			$('#alert_capturar').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Advertencia!</strong><p>No se identificó a ningún usuario.</p></div>');
		}
	});

	$modal_capturar.on('show.bs.modal', function (e) {
		llenar_coordenadas_capturar('', '', '', '');
		if (Modernizr.getusermedia && Modernizr.canvas) {
			window.datos = {
				'video': null,
				'url': null
			},
			navigator.getUserMedia = Modernizr.prefixed('getUserMedia', navigator);
			navigator.getUserMedia({
				'audio': false,
				'video': true
			},
			function(video) {
				datos.video = video;
				datos.url = window.URL.createObjectURL(video);
				$('#video_capturar').attr('src', datos.url);
			},
			function() {
				$modal_capturar.modal('hide');
				bootbox.alert('No se puede tener acceso a la cámara.');
			});
		} else {
			bootbox.alert('Su navegador no soporta esta acción!');
		}
	});

	$modal_capturar.on('shown.bs.modal', function (e) {
		$('#tomar_capturar').focus();
	});

	$modal_capturar.on('hide.bs.modal', function (e) {
		var captura, contexto;
		captura = $('#canvas_capturar');
		contexto = captura[0].getContext('2d');
		contexto.clearRect(0, 0, captura.width(), captura.height());
		if (datos.video) {
			//datos.video.stop();
			//window.URL.revokeObjectURL(datos.url);
			window.location.reload();
		}
	});

	$('#tomar_capturar').on('click', function (e) {
		e.preventDefault();
		llenar_coordenadas_capturar('', '', '', '');
		var camara, captura, lienzo, contexto, ancho, alto, data;
		camara = $('#video_capturar');
		captura = $('#canvas_capturar');
		lienzo = $('#img_capturar');
		ancho = camara.width();
		alto = camara.height();
		captura.attr({
			'width': ancho,
			'height': alto
		});
		contexto = captura[0].getContext('2d');
		contexto.drawImage(camara[0], 0, 0, ancho, alto);
		data = captura[0].toDataURL('image/jpeg', 1);
		lienzo.html('<img class="img-responsive" data-verificar="true">');
		lienzo.find('img').attr('src', data);
		lienzo.find('img').Jcrop({
			aspectRatio: 1,
			onChange: function (data) {
				llenar_coordenadas_capturar('', '', '', '');
			},
			onSelect: function (data) {
				llenar_coordenadas_capturar(data.x, data.y, data.w, data.h);
			}
		});
	});

	$('#cancelar_capturar').on('click', function (e) {
		e.preventDefault();
		$('#id_user_capturar').val('');
		$modal_capturar.modal('hide');
	});

	var llenar_coordenadas_capturar = function (x, y, w, h) {
		$('#alert_capturar').html('');
		$('#avatar_x_capturar').val(x);
		$('#avatar_y_capturar').val(y);
		$('#avatar_w_capturar').val(w);
		$('#avatar_h_capturar').val(h);
	}
	<?php } ?>

	var table = $('#table').DataFilter({
		filter: false,
		name: 'usuarios',
		reports: 'xls|doc|pdf|html'
	});

});
</script>
<?php require_once show_template('footer-configured'); ?>