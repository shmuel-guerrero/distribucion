<?php
    // Obtiene los permisos
    $permisos = explode(',', permits);
    // Almacena los permisos en variables
    $permiso_almacen = in_array('permiso_almacen', $permisos);
    $permiso_mostrar = in_array('mostrar', $permisos);
?>
<style>
    .panel-heading h2{
        text-align: center;
    }
    .panel-heading h2 span{
        font-size: 40px;
    }
    .panel-heading h2 a{
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

			<?php if ($permiso_mostrar) : ?>
			<br>
			
			<?php endif ?>
			<div class="panel-body">
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<strong>Advertencia!</strong>
					<ul>
						<li>Elija el punto de venta desde el cual hara la compra.</li>
					</ul>
				</div>
					<?php
					$almacen = $db->from('inv_almacenes')
								->fetch();
					foreach($almacen as $nro => $almacenX){
					?>
					<div class="col-md-4">
						<a class="seleccionarAlmacen" href="?/autoventas/crear_orden/<?php echo $almacenX["id_almacen"]; ?>">
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
<?php require_once show_template('footer-configured'); ?>