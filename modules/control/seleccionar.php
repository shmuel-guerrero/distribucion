<?php return redirect('?/control/visitas'); ?>
<?php require_once show_template('header-configured'); ?>
<div class="panel-heading">
	<h3 class="panel-title" data-header="true">
		<span class="glyphicon glyphicon-option-vertical"></span>
		<strong>Selección de sucursal</strong>
	</h3>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-xs-12">
			<div class="text-label">Seleccione la sucursal de la cual desea realizar la venta:</div>
		</div>
	</div>
	<hr>

	<div class="list-group">

        <a href="?/control/visitas" class="list-group-item">
            <strong class="list-group-item-heading"><?= $_institution['empresa1'] ?></strong>
            <p class="list-group-item-text"><?= $_institution['empresa1'] ?></p>
        </a>

	</div>

	<div class="alert alert-info">
		<strong>Atención!</strong>
		<ul>
			<li>Seleccione una de las dos empresas.</li>
			<li><b>Puede ver las ventas en el día de hoy.</b></li>
		</ul>
	</div>

</div>
<?php require_once show_template('footer-configured'); ?>