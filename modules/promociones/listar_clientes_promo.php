<?php

// Obtiene los clientes
$clientes = $db->query('SELECT a.cliente, a.imagen, a.nit, a.id_cliente, a.estado, a.telefono, 
    a.direccion, a.tipo FROM inv_clientes a LEFT OUTER JOIN inv_egresos b ON a.cliente = b.nombre_cliente 
    left outer join inv_participantes_promos p on  a.id_cliente=p.cliente_id
    left outer join inv_clientes_grupos g on a.cliente_grupo_id=p.cliente_grupo_id')
    ->group_by('a.cliente, a.nit')->order_by('cliente asc, nit asc')->fetch();

// Obtiene los permisos
//$permisos = explode(',', permits);

// Almacena los permisos en variables
/*$permiso_imprimir = in_array('imprimir', $permisos);
$permiso_eliminar = in_array('eliminar', $permisos);
$permiso_modificar = in_array('editar', $permisos);*/

// Obtiene la moneda oficial
$moneda = $db->from('inv_monedas')->where('oficial', 'S')->fetch_first();
$moneda = ($moneda) ? '(' . $moneda['sigla'] . ')' : '';

?>
<?php require_once show_template('header-advanced'); ?>
<div class="panel-heading">
	<h3 class="panel-title">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Clientes con Promocion</strong>
	</h3>
</div>
<div class="panel-body">
	
	<?php if ($clientes) { ?>
	<table id="table" class="table table-bordered table-condensed table-restructured table-striped table-hover">
		<thead>
			<tr class="active">
				<th class="text-nowrap">#</th>
                <th class="text-nowrap">Imagen</th>
                <th class="text-nowrap">Código cliente</th>
                <th class="text-nowrap">Cliente</th>
                <th class="text-nowrap">NIT/CI</th>
                <th class="text-nowrap">Telefono</th>
                <th class="text-nowrap">Dirección</th>
                <th class="text-nowrap">Tipo</th>
                <th class="text-nowrap">Opciones</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="active">
				<th class="text-nowrap text-middle" data-datafilter-filter="false">#</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Imagen</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Cliente</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Código cliente</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">NIT/CI</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Telefono</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Dirección</th>
                <th class="text-nowrap text-middle" data-datafilter-filter="true">Tipo</th>
				<th class="text-nowrap text-middle" data-datafilter-filter="true">Opciones</th>
           
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($clientes as $nro => $cliente) { ?>
			<tr>
				<th class="text-nowrap"><?= $nro + 1; ?></th>
                <td class="text-nowrap text-middle text-center">
                    <img src="<?= ($cliente['imagen'] == '') ? imgs . '/image.jpg' : files . '/tiendas/' . $cliente['imagen']; ?>"  class="img-rounded cursor-pointer" data-toggle="modal" data-target="#modal_mostrar" data-modal-size="modal-md" data-modal-title="Imagen" width="75" height="75">
                </td>
				<td class="text-nowrap"><?= escape($cliente['id_cliente']); ?></td>
				<td class="text-nowrap"><?= escape($cliente['cliente']); ?></td>
                <td class="text-nowrap"><?= escape($cliente['nit']); ?></td>
                <td class="text-nowrap"><?= escape($cliente['telefono']); ?></td>
                <td class="text-nowrap"><?= escape($cliente['direccion']); ?></td>
                <td class="text-nowrap"><?= escape($cliente['tipo']); ?></td>
				
                <td class="text-nowrap" align="center">
                    <a href="?/promociones/detalle_clientes_promo/<?= $cliente['id_cliente']; ?>" data-toggle="tooltip" data-title="Modificar cliente"><span class="glyphicon glyphicon-file"></span></a>
				</td>
                

			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } else { ?>
	<div class="alert alert-danger">
		<strong>Advertencia!</strong>
		<p>No existen clientes registrados en la base de datos.</p>
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
	<?php if ($clientes) { ?>
	var table = $('#table').DataFilter({
		filter: true,
		name: 'clientes',
		reports: 'excel|word|pdf|html'
	});
	<?php } ?>
});

</script>
<?php require_once show_template('footer-advanced'); ?>