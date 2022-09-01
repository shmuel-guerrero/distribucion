<?php

// Obtiene el menu de herramientas
$_herramientas = json_decode(@file_get_contents(storage . '/herramientas.json'), true);

// Obtiene los menus
$_menus = $db->select('m.*, p.archivos')->from('sys_permisos p')->join('sys_menus m', 'p.menu_id = m.id_menu')->where(array('p.rol_id' => $_SESSION[user]['rol_id']))->where_not_in('m.id_menu', array('0'))->order_by('m.orden', 'asc')->fetch();

// Construye la barra de menus
$_menus = construir_sidebar($_menus);

?>
<!doctype html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="theme-color" content="#3d3d3d">
	<title><?= $_institution['nombre']; ?></title>
	<link rel="stylesheet" href="<?= css; ?>/bootstrap.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-grid.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-utilities.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-icons.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-spinner.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-dashboard.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-datetimepicker.min.css">
	<link rel="stylesheet" href="<?= css; ?>/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="<?= css; ?>/selectize.bootstrap3.min.css">
	<link rel="stylesheet" href="<?= css; ?>/metisMenu.min.css">
	<link rel="stylesheet" href="<?= themes . '/' . (($_institution['tema'] == '') ? 'bootstrap' : $_institution['tema']); ?>/style.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-structured.min.css">
	<link rel="stylesheet" href="<?= css; ?>/animate.min.css">
	<link rel="stylesheet" href="<?= css; ?>/bootstrap-toggle.min.css">
	<link rel="stylesheet" href="<?= css; ?>/jquery.treegrid.css">
	<link rel="stylesheet" href="<?= css; ?>/materialdesignicons.min.css">


	<link rel="icon" type="image/png" href="<?= project; ?>/favicon.png">
	<style>
		.wrapper {
			/* background: url('imgs/bg-2.jpg') no-repeat center center fixed; 
			-webkit-background-size: cover;
			-moz-background-size: cover;
			background-size: cover; */
			/* background-image: linear-gradient(to top, #1c5292, #04b3a0); */
			/*  background-image: linear-gradient(to top, #4b134f, #c94b4b);  */
			/* background-image: -webkit-linear-gradient(to top, #0a8ce8, #00d7ff); */
			/* background-image: -moz-linear-gradient(to top, #0a8ce8, #00d7ff); */
			/* background-image: linear-gradient(to top, #003366, #0b8bb8 ); */
			/* background-color: #563d7c; */
		}
	</style>
	<!--[if lt ie 9]>
			<script src="<?= js; ?>/html5shiv.min.js"></script>
			<script src="<?= js; ?>/respond.min.js"></script>
		<![endif]-->
	<script src="<?= js; ?>/jquery.min.js"></script>
	<script src="<?= js; ?>/bootstrap.min.js"></script>
	<script src="<?= js; ?>/metisMenu.min.js"></script>
	<script src="<?= js; ?>/bootbox.min.js"></script>
	<script src="<?= js; ?>/bootstrap-notify.min.js"></script>
	<script src="<?= js; ?>/bootstrap-lightbox.min.js"></script>
	<script src="<?= js; ?>/bootstrap-filestyle.min.js"></script>
	<script src="<?= js; ?>/jquery.request.min.js"></script>
	<script src="<?= js; ?>/moment.min.js"></script>
	<script src="<?= js; ?>/moment.es.js"></script>
	<script src="<?= js; ?>/animo.min.js"></script>
	<script src="<?= js; ?>/buzz.min.js"></script>
	<script src="<?= js; ?>/functions.min.js"></script>
	<script src="<?= js; ?>/docs.min.js"></script>
	<script src="<?= js; ?>/bootstrap-toggle.min.js"></script>


	<script src="<?= js; ?>/axios.min.js"></script>
	<script src="<?= js; ?>/app.js"></script>

	<link rel="stylesheet" href="<?= css; ?>/bootstrap-extended.min.css">
	<link rel="stylesheet" href="<?= css; ?>/colors.min.css">
	<link rel="stylesheet" href="<?= css; ?>/style.min.css">
	<style>
	 	@media screen and (max-width: 768px){
			#icons-general{
				display: none;
				opacity: 0;
			}
		} 


	</style>
</head>

<body>

	<?php if (environment == 'production') : ?>
		<div class="spinner-wrapper spinner-wrapper-fixed" data-spinner="true">
			<div class="spinner-wrapper-backdrop">
				<span class="spinner"></span>
			</div>
		</div> 
	<?php endif ?>
	<div class="wrapper wrapper-light">
		<nav class="navbar navbar-static-top m-0">
			<div class="container-fluid">
				<div class="collapse navbar-collapse">
					<div class="row align-items-center">
						<div class="col">
							<button type="button" class="navbar-toggle float-left d-inline-block" id="menu-hamburguesa">
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<a href="<?= index_private; ?>" class="navbar-brand">
								<small><span class="hidden-xs hidden-sm"><?= $_institution['nombre']; ?></span></small>
								<sup><small class="h5">CheckcoDGps</small><small class="hidden-xs hidden-sm"> <?= date('Y') ?> </small><small> v.3.02.1</small></sup>
							</a>
						</div>
						<div class="col-auto">
							<ul class="nav navbar-nav navbar-right"></ul>
							<ul class="nav navbar-nav navbar-right">
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">
										<img src="<?= ($_user['avatar'] == '') ? imgs . '/avatar-default.jpg' : profiles . '/' . $_user['avatar']; ?>" class="rounded-circle" width="32" height="32" style="margin: -15px 0;">
										<span class="text-capitalize hidden-xs"><?= ($_user['persona_id'] == 0) ? escape($_user['username']) : escape($_user['nombres']); ?></span>
									</a>
									<ul class="dropdown-menu">
										<li class="dropdown-header visible-xs-block"><?= ($_user['persona_id'] == 0) ? escape($_user['username']) : escape($_user['nombres']); ?></li>
										<li>
											<a href="<?= index_private; ?>">
												<span class="glyphicon glyphicon-home"></span>
												<span>Página de inicio</span>
											</a>
										</li>
										<li>
											<a href="?/home/perfil_ver">
												<span class="glyphicon glyphicon-user"></span>
												<span>Perfil de usuario</span>
											</a>
										</li>
										<li>
											<a href="?/site/logout" data-cerrar-sesion>
												<span class="glyphicon glyphicon-lock"></span>
												<span>Cerrar sesión</span>
											</a>
										</li>
										<li>
											<a href="#" data-toggle="modal" data-target="#modal_ayudar" data-comando-ayudar="true">
												<span class="glyphicon glyphicon-question-sign"></span>
												<span>Ayuda</span>
											</a>
										</li>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</nav>
		<div class="wrapper-body">
			<div class="wrapper-aside" id="menu-general">
				<div class="py-4">
					<div class="px-4 text-center">
						<div class="h2 m-0 text-truncate text-monospace" data-datetime="time"><?= date('H:i:s'); ?></div>
						<div class="h6 m-0 text-truncate" data-datetime="date"><?= date_decode(date('Y-m-d'), $_institution['formato']); ?></div>
					</div>
					<hr>
					<div class="px-4">
						<a href="?/electronicas/crear" class="btn btn-block btn-primary text-truncate mb-4">Vender</a>
						<a href="?/ingresos/listar" class="btn btn-block btn-info text-truncate mb-4">Comprar</a>
						<a href="?/productos/listar" class="btn btn-block btn-default text-truncate mb-4">Catálogo</a>
					</div>
					<hr>
					<ul class="nav sidebar-nav animated fadeIn"><?= $_menus; ?></ul>
					
				</div>
			</div>

		
			<div class="flex-column flex-shrink-0 bg-light hidden" style="width: 8rem;" id="icons-general">				
				<ul class="nav nav-pills nav-flush flex-column mb-auto text-center">
					<li class="nav-item">
						<a href="?/vendedor/listar" class="nav-link active py-3 border-bottom" aria-current="page" title="Preventistas" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-map-marker-check-outline h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">PREVENTISTAS</small>
						</a>
					</li>
					<li class="nav-item">
						<a href="?/distribuidor/listar2" class="nav-link active py-3 border-bottom" aria-current="page" title="Distribuidores" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-truck-delivery h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">DISTRIBUIDORES</small>
						</a>
					</li>
					<li class="nav-item">
						<a href="?/cobrar/listar" class="nav-link active py-3 border-bottom" aria-current="page" title="Deudas de Clientes" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-cash-100 h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">COBROS</small>
						</a>
					</li>
					<li>
						<a href="?/productos/listar" class="nav-link py-3 border-bottom" title="Catalogo de productos" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-baguette h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">CATALOGO</small>
						</a>
					</li>
					<li>
						<a href="?/ingresos/crear" class="nav-link py-3 border-bottom" title="Ingresos" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-basket-fill h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">INGRESOS</small>
						</a>
					</li>
					<li>
						<a href="?/egresos/listar" class="nav-link py-3 border-bottom" title="Egresos / Bajas" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-basket-unfill h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">EGRESOS</small>
						</a>
					</li>
					<li>
						<a href="?/stocks/listar" class="nav-link py-3 border-bottom" title="Stock" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-chart-bar h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;"> STOCK </small>
						</a>
					</li>
					<li>
						<a href="?/electronicas/crear" class="nav-link py-3 border-bottom" title="Electronicas" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-qrcode-edit h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">ELECTRONICAS</small>
						</a>
					</li>
					<li>
						<a href="?/notas/crear" class="nav-link py-3 border-bottom" title="Notas" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-cart h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;"> NOTAS </small>
						</a>
					</li>
					<li>
						<a href="?/reportes/diario" class="nav-link py-3 border-bottom" title="Reporte de Ventas" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-chart-line h1 align-middle"></span>
							<small class="text-xs h6" style="font-size:7px;">REPORTE</small>
						</a>
					</li>
				</ul>
			</div>
			<div class="wrapper-main">
				<div class="p-4">