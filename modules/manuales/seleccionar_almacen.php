<?php
	// Obtiene los permisos
	$permisos = explode(',', permits);
	// Almacena los permisos en variables
	$permiso_almacen = in_array('permiso_almacen', $permisos);
	$permiso_mostrar = in_array('mostrar', $permisos);
	
	if ($_user['rol'] == 'Superusuario' || $_user['rol'] == 'superusuario' || $_user['rol'] == 'Administrador' || $_user['rol'] == 'administrador' || $_user['rol'] == 'Almacenero' || $_user['rol'] == 'almacenero' || $_user['rol'] == 'Despacho' || $_user['rol'] == 'despacho') {
?>
    <style>
    	.panel-heading h2{
    		text-align: center;
    	}
    	.panel-heading h2 span{
    		font-size: 40px;
    	}
    </style>
    
    <?php require_once show_template('header-configured'); ?>
    
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title">
    					<span class="glyphicon glyphicon-list"></span>
    					<strong>Seleccionar el punto de venta</strong>
    				</h3>
    			</div>
    
    			<div class="panel-body">
    			<?php if ($permiso_mostrar) { ?>
    			<p class="text-right">
    				<a href="?/manuales/mostrar" class="btn btn-warning">Mis ventas manuales</a>
    			</p>
    			<?php } ?>
    				<div class="alert alert-info">
    					<button type="button" class="close" data-dismiss="alert">&times;</button>
    					<strong>Advertencia!</strong>
    					<ul>
    						<li>Elija el punto de venta desde el cual hara la compra.</li>
    					</ul>
    				</div>
    					<?php
    					// 	$almacen = $db->from('inv_almacenes')->fetch();
    				$almacen = $db->from('inv_almacenes')->fetch();
    					foreach($almacen as $nro => $almacenX){
    					?>
    					<div class="col-md-4">
    						<a class="seleccionarAlmacen" href="?/manuales/crear/<?php echo $almacenX["id_almacen"]; ?>">
    							<div class="panel panel-default">
    							<div class="panel-heading">
    								<h2 class="panel-title">
    									<span class="glyphicon glyphicon-list"></span>
    									<br>
    									<br>
    									<?php echo $almacenX["almacen"]; ?>
    								</h2>
    							</div>
    							</div>
    						</a>
    					</div>
    					<?php
    					}
    					?>
    			</div>
    		</div>
    	</div>
    </div>
    <script>
    	$(document).ready(function(){
    		$('.seleccionarAlmacen').hover(
    			function(){ $(this).children('div').addClass("panel-primary"); $(this).children('div').removeClass("panel-default"); },
    			function(){ $(this).children('div').addClass("panel-default"); $(this).children('div').removeClass("panel-primary"); }
    		);
    	});
    </script>

<?php } else {
	return redirect('?/manuales/crear/' . $_user['almacen_id']);
} ?>
<?php require_once show_template('footer-configured'); ?>