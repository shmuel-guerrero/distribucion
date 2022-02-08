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

	<link rel="stylesheet" href="<?= css; ?>/bootstrap-extended.min.css">
	<link rel="stylesheet" href="<?= css; ?>/colors.min.css">
	<link rel="stylesheet" href="<?= css; ?>/style.min.css">
</head>

<body>
	<?php if (environment == 'production') : ?>
		<!-- <div class="spinner-wrapper spinner-wrapper-fixed" data-spinner="true">
			<div class="spinner-wrapper-backdrop">
				<span class="spinner"></span>
			</div>
		</div> -->
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
								<small><span><?= $_institution['nombre']; ?></span></small>
								<sup><small>v.3.02.1</small></sup>
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
											<a href="?/site/logout">
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

			<div class="d-flex flex-column flex-shrink-0 bg-light" style="width: 7rem;" id="icons-general">				
				<ul class="nav nav-pills nav-flush flex-column mb-auto text-center">
					<li class="nav-item">
						<a href="#" class="nav-link active py-3 border-bottom" aria-current="page" title="Home" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-map-marker-check-outline h1 align-middle"></span>
						</a>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link active py-3 border-bottom" aria-current="page" title="Home" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-truck-delivery h1 align-middle"></span>
						</a>
					</li>
					<li class="nav-item">
						<a href="#" class="nav-link active py-3 border-bottom" aria-current="page" title="Home" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-bitcoin h1 align-middle"></span>
						</a>
					</li>
					<li>
						<a href="#" class="nav-link py-3 border-bottom" title="Dashboard" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-baguette h1 align-middle"></span>
						</a>
					</li>
					<li>
						<a href="#" class="nav-link py-3 border-bottom" title="Dashboard" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-basket-fill h1 align-middle"></span>
						</a>
					</li>
					<li>
						<a href="#" class="nav-link py-3 border-bottom" title="Dashboard" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-chart-bar h1 align-middle"></span>
						</a>
					</li>
					<li>
						<a href="#" class="nav-link py-3 border-bottom" title="Orders" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-qrcode-edit h1 align-middle"></span>
						</a>
					</li>
					<li>
						<a href="#" class="nav-link py-3 border-bottom" title="Products" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-cart h1 align-middle"></span>
						</a>
					</li>
					<li>
						<a href="#" class="nav-link py-3 border-bottom" title="Customers" data-bs-toggle="tooltip" data-bs-placement="right">
							<span class="mdi mdi-basket-unfill h1 align-middle"></span>
						</a>
					</li>
				</ul>
				<!-- <div class="col-auto">
					<ul class="nav navbar-nav navbar-right"></ul>
					<ul class="nav navbar-nav navbar-right">
						<li class="dropup ">
							<ul class="dropup flex-column mb-auto text-center"  aria-labelledby="dLabel">														
								<a href="#" class="dropdown-toggle" title="Customers" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									<span class="mdi mdi-storefront h1 align-middle"></span>
								</a>
								<ul class="dropdown-menu">
									<li><a href="#">Action</a></li>
									<li><a href="#">Another action</a></li>
									<li><a href="#">Something else here</a></li>
									<li role="separator" class="divider"></li>
									<li><a href="#">Separated link</a></li>
									<li role="separator" class="divider"></li>
									<li><a href="#">One more separated link</a></li>
								</ul>
								</li>
							</ul>
						</li>
					</ul>
				</div> -->
			</div>
			<div class="wrapper-main">
				<div class="p-4">