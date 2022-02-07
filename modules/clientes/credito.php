<?php

// Obtiene los clientes
//$clientes = $db->query('SELECT a.cliente, a.imagen, a.nit, a.id_cliente, a.estado, a.telefono, a.direccion, a.tipo,  count(a.cliente) as nro_visitas FROM inv_clientes a LEFT OUTER JOIN inv_egresos b ON a.cliente = b.nombre_cliente ')->order_by('cliente asc, nit asc')->fetch();
//$clientes = $db->query('SELECT a.cliente, a.imagen, a.nit, a.id_cliente, a.estado, a.telefono, a.direccion, a.tipo,  count(a.cliente) as nro_visitas FROM inv_clientes a LEFT OUTER JOIN inv_egresos b ON a.cliente = b.nombre_cliente ')->group_by('a.cliente, a.nit')->order_by('cliente asc, nit asc')->fetch_first();

// Obtiene los permisos
$permisos = explode(',', permits);
// Almacena los permisos en variables
$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_eliminar = in_array('eliminar_credito', $permisos);
$permiso_modificar = in_array('editar', $permisos);

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Clientes con contrato de créditos</strong>
	</h3>
</div>
<div class="panel-body">
    <?php if ($message = get_notification()) : ?>
        <div class="alert alert-<?= $message['type']; ?>">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong><?= $message['title']; ?></strong>
            <p><?= $message['content']; ?></p>
        </div>
    <?php endif ?>
            
	<?php // if ($permiso_imprimir) { ?>
	<div class="row">
		<div class="col-sm-6 hidden-xs">
			<div class="text-label">Para asignar hacer clic en el siguiente botón: </div>
		</div>
        <div class="col-xs-12 col-sm-6 text-right">
            <!--<a href="?/clientes/imprimir" target="_blank" class="btn btn-info"><i class="glyphicon glyphicon-print"></i><span class="hidden-xs"> Imprimir</span></a>-->
            <a href="?/clientes/crear_credito" class="btn btn-success"data-toggle="tooltip" data-placement="top" title="Asignar credito"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs"> Asignar créditos</span></a>
            <!--<a href="?/clientes/crear_tipo" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs"> Crear tipo</span></a>-->
            <!--<a href="?/clientes/crear_grupo" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i><span class="hidden-xs"> Crear grupo</span></a>-->
        </div>
	</div>
	<hr>
<?php
    // }
    if(true){
?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover" style="width: 100%;">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
                <th class="text-nowrap">Imagen</th>
                <th class="text-nowrap">Código cliente</th>
                <th class="text-nowrap">Cliente</th>
                <th class="text-nowrap">NIT/CI</th>
                <th class="text-nowrap">Telefono</th>
                <th class="text-nowrap">Crédito</th>
                <th class="text-nowrap">Periodo</th>
            <?php if ($permiso_modificar || $permiso_eliminar) : ?>
                    <th class="text-nowrap">Opciones</th>
            <?php endif ?>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="false">Imagen</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Código cliente</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Telefono</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Crédito</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Periodo</th>
            <?php if ($permiso_modificar || $permiso_eliminar) : ?>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
            <?php endif ?>
			</tr>
		</tfoot>
		<tbody>
		</tbody>
	</table>
<?php
    }
    else{
?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen clientes registrados en la base de datos.</p>
	</div>
<?php
    }
?>
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
</div>
<script src="<?= js; ?>/jquery.dataTables.min.js"></script>
<script src="<?= js; ?>/dataTables.bootstrap.min.js"></script>
<script src="<?= js; ?>/jquery.base64.js"></script>
<script src="<?= js; ?>/pdfmake.min.js"></script>
<script src="<?= js; ?>/vfs_fonts.js"></script>
<script src="<?= js; ?>/jquery.dataFilters.min.js"></script>
<script>
    $(function(){

    <?php if ($permiso_eliminar) { ?>
        $(document).on('click', '[data-eliminar]', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            bootbox.confirm('Está seguro que desea eliminar el credito del cliente?', function (result) {
                if(result){
                    window.location = url;
                }
            });
        });
	<?php } ?>

<?php
    if($permiso_imprimir){
?>
        $(window).bind('keydown', function (e) {
            if (e.altKey || e.metaKey) {
                switch (String.fromCharCode(e.which).toLowerCase()) {
                    case 'p':
                        e.preventDefault();
                        window.location = '?/clientes/imprimir';
                    break;
                    case 'n':
                        e.preventDefault();
                        window.location = '?/clientes/crear';
                    break;
                }
            }
        });
<?php
    }
    //if($clientes){
?>
        $loader_mostrar = $('#loader_mostrar')
        <?php
            $url=institucion.'/'.$_institution['imagen_encabezado'];
            $image=file_get_contents($url);
            if($image!==false):
                $imag='data:image/jpg;base64,'.base64_encode($image);
            endif;
        ?>
        var table = $('#table').DataFilter({
            filter: false,
            name: 'Lista de Clientes',
            imag: '<?= imgs . '/logo-color.jpg'; ?>',
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
                    url: '?/clientes/listar_credito',
                    type: 'POST',
                    beforeSend:function(){
                        $loader_mostrar.show();
                    },
                    error: function(){}
                },
                drawCallback: function(settings) {
                    $loader_mostrar.hide();
                },
                createdRow:function(nRow, aData, iDisplayIndex){
                    $(nRow).attr('data-producto',aData[0]);
                    $('td', nRow).eq(0).addClass('text-nowrap');
                    $('td', nRow).eq(1).addClass('text-nowrap text-middle text-center');
                    $('td', nRow).eq(2).addClass('text-nowrap');
                    $('td', nRow).eq(3).addClass('text-nowrap');
                    $('td', nRow).eq(4).addClass('text-nowrap');
                    $('td', nRow).eq(5).addClass('text-nowrap');
                    $('td', nRow).eq(6).addClass('text-nowrap');
                    $('td', nRow).eq(7).addClass('text-nowrap');
                    // $('td', nRow).eq(8).addClass('text-nowrap');
                    <?php
                        if($permiso_modificar || $permiso_eliminar):
                    ?>
                        $('td', nRow).eq(8).addClass('text-nowrap');
                    <?php
                        endif;
                    ?>
                }
            }
        });
<?php
    //}
?>
    });
    var $modal_mostrar=$('#modal_mostrar'),
        $loader_mostrar=$('#loader_mostrar'),
        size,
        title,
        image;
    $modal_mostrar.on('hidden.bs.modal',function(){
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
</script>
<?php require_once show_template('footer-configured'); ?>