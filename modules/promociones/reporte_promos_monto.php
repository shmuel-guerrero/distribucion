<?php

// Obtiene los almacenes
$almacenes = $db->get('inv_almacenes');

// Obtiene los productos
//$productos = $db->select('p.*, u.unidad, c.categoria')->from('inv_productos p')->join('inv_unidades u', 'p.unidad_id = u.id_unidad', 'left')->join('inv_categorias c', 'p.categoria_id = c.id_categoria', 'left')->order_by('p.id_producto')->fetch();

// Obtiene a los clientes
//$clientes = $db->select('id_egreso, nombre_cliente, nit_ci, count(nombre_cliente) as nro_visitas, sum(monto_total) as total_ventas')->from('inv_egresos')->group_by('nombre_cliente, nit_ci')->order_by('total_ventas desc, nro_visitas desc')->fetch();

$promos = $db->select('id_promocion, nombre, tipo, fecha_ini, fecha_fin,descripcion,descuento_promo,monto_promo,item_promo,min_promo')->from('inv_promociones_monto')->order_by('id_promocion')->fetch();

// Obtiene los permisos
$permisos = explode(',', permits);

// Almacena los permisos en variables
$permiso_editar = in_array('editar', $permisos);
$permiso_eliminar = in_array('eliminar_promo', $permisos);
?>
<?php require_once show_template('header-configured'); ?>

<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Lista de Promociones por Fecha</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-12 text-right">
			<a href="?/promociones/almacen_promo" class="btn btn-info">
			<i class="glyphicon glyphicon-plus"></i><span class="hidden-xs"> Crear Promociones</span>
			</a><br><br>
			
		</div>
	</div>

	<?php if ($promos) { ?>
	<table id="table" class="table table-bordered table-condensed table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
				<th class="text-nowrap">Codigo</th>
                <th class="text-nowrap">Nombre</th>
                <th class="text-nowrap">Monto a Alcanzar</th>
                <th class="text-nowrap">Tipo</th>
                <th class="text-nowrap">Monto Descuento</th>
                
                <th class="text-nowrap">Item Regalo</th>
                <th class="text-nowrap">Fecha Incial</th>
                <th class="text-nowrap">Fecha Fin</th>
                <th class="text-nowrap">Descripcion</th>
				<th class="text-nowrap">Opciones</th>

			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Codigo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Nombre</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Monto a Alcanzar</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Monto Descuento</th>
                
                 <th class="text-nowrap text-middle" data-datafilter-filter="true">Item Regalo</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha Incial</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Fecha Final</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Descripcion</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="false">Opciones</th>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($promos as $nro => $promo) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
				<td class="text-nowrap"><?= escape($promo['id_promocion']); ?></td>
				<td class="text-nowrap"><?= escape($promo['nombre']); ?></td>
				<td class="text-nowrap"> 
					
					  <?= escape($promo['min_promo']); ?>
				</td>
                <?php if($promo['tipo'] == 2){?>
                    <td class="text-nowrap"><?= escape("Descuento en Bolivianos"); ?></td>
                    
                <?php }else if($promo['tipo'] == 4){ ?>
                    <td class="text-nowrap"><?= escape("Descuento en Item"); ?></td>
                    
                <?php } ?>

                <td class="text-nowrap">
                	<?php if($promo['tipo'] == 2) {
						echo $promo['monto_promo']; 
					  } else { 
						echo '---';
					 } ?>	
				</td>


                <td class="text-nowrap"> 
                <?php if($promo['tipo'] == 4) {

                	    $reg = $db->select('nombre')->from('inv_productos')->where('id_producto=',$promo['item_promo'])->fetch_first();
						echo $reg['nombre']; 

					  } else { 
						echo '---';
					  } ?>
				</td>

                <td class="text-nowrap"><?= escape($promo['fecha_ini']); ?></td>
                <td class="text-nowrap"><?= escape($promo['fecha_fin']); ?></td>
                <td class="text-nowrap"><?= escape($promo['descripcion']); ?></td>
				<td class="text-nowrap">
						<?php if($promo['promocion_x_fecha']==''){ ?>
                            
								<a href="?/promociones/eliminar_promo/<?= $promo['id_promocion']; ?>" data-toggle="tooltip" data-title="Eliminar Promoción" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
							
							<a href="?/promociones/editar/<?= $promo['id_promocion']; ?>" data-toggle="tooltip" data-title="Editar Promoción"><i class="glyphicon glyphicon-edit"></i></a>
                        <?php }else{ ?>
                           
								<a href="?/promociones/eliminar_promo/<?= $promo['id_promocion']; ?>" data-toggle="tooltip" data-title="Eliminar Promoción" data-eliminar="true"><i class="glyphicon glyphicon-trash"></i></a>
							
							<a href="?/promociones/editar/<?= $promo['id_promocion']; ?>" data-toggle="tooltip" data-title="Editar Promoción"><i class="glyphicon glyphicon-edit"></i></a>
                        <?php } ?>
					<!--<a href="?/promociones/promocion_x_fecha" data-toggle="tooltip" data-title="nueva promocion"><span class="glyphicon glyphicon-book"></span></a>-->

					<!-- <a href="?/promociones/asignar_producto_promocion/<?//= $promo['id_promocion']; ?>" data-toggle="tooltip" data-title="Asignar productos promocion"><span class="glyphicon glyphicon-share-alt"></span></a> -->

				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen productos registrados en la base de datos.</p>
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
$(function () {	
	<?php if ($promos) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'lista_promos',
		reports: 'xls|doc|pdf|html'
	});
	<?php } ?>
});

$(function () {
	<?php if ($permiso_eliminar) { ?>
	$('[data-eliminar]').on('click', function (e) {
		e.preventDefault();
		var url = $(this).attr('href');
		bootbox.confirm('Está seguro que desea eliminar el empleado?', function (result) {
			if(result){
				window.location = url;
			}
		});
	});
	<?php } ?>	
});
</script>

	
<?php require_once show_template('footer-configured'); ?>