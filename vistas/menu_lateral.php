<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
	<a class="navbar-brand col-sm-3 col-md-2 mr-0 text-center" href="<?php echo DOMINIO; ?>"><img src="https://cohenag.com/crm/test/logo/vtiger-crm-logo.png" class="img-fluid" alt="COHEN Agentes Globales"/></a>
	<!--<input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">-->
	<h2 class="w-100 text-light mx-3"><?php echo "Hola, {$GLOBALS['sitio']->usuario_activo()->info()['nombre_completo']}"; ?></h2>
	<ul class="navbar-nav px-3">
		<li class="nav-item text-nowrap">
			<a class="nav-link" href="<?php echo DOMINIO; ?>actividad/">Actividad</a>
			<a class="nav-link" href="<?php echo DOMINIO; ?>logout/">Salir</a>
		</li>
	</ul>
</nav>

<div class="container-fluid">
	<div class="row">
		<nav class="col-md-2 d-none d-md-block bg-light sidebar">
			<div class="sidebar-sticky">
				<ul class="nav flex-column">
					<li class="nav-item mt-5">
						<a class="nav-link active" href="<?php echo DOMINIO; ?>inicio/">
							<i class="fa fa-home"></i> Inicio <span class="sr-only">(current)</span>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>cartera/">
							<i class="fa fa-home"></i> Cartera
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>pagos/">
							<i class="fa fa-home"></i> Pagos
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>pagosxpoliza/">
							<i class="fa fa-home"></i> Pagos X Póliza
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>cobranza/">
							<i class="fa fa-home"></i> Cobranza
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>actividad/">
							<i class="fa fa-home"></i> Actividad
						</a>
					</li>
				</ul>

				<h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
					<span>Menu admin</span>
					<a class="d-flex align-items-center text-muted" href="#">
						<i class="fa fa-plus"></i>
					</a>
				</h6>
				<ul class="nav flex-column mb-2">
					<?php
						if ( $GLOBALS['sitio']->usuario_activo()->esAdmin() ) {
					?>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>actualiza/">
							<i class="fa fa-sync-alt"></i> Actualizar
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>avances/">
							<i class="fa fa-chart-line"></i> Avances
						</a>
					</li>
					<?php
						}
					?>
					<li class="nav-item">
						<a class="nav-link" target="_blank" href="/crm/">
							<i class="fa fa-paper-plane"></i> CRM
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="<?php echo DOMINIO; ?>logout/">
							<i class="fa fa-user"></i> Salir
						</a>
					</li>
				</ul>
			</div>
		</nav>
		<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
		<!-- OJO CERRAR EL DIV DEL CONTENIDO Y EL DIV DEL ROW Y EL MAIN!!! -->