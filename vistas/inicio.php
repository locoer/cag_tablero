<?php
$asesor = $GLOBALS["sitio"]->usuario_activo();
$reportes = new Reportes ($asesor);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2"><?php echo $this->titulo; ?></h1>
	<div class="btn-toolbar mb-2 mb-md-0">
		<div class="btn-group mr-2">
			<button class="btn btn-sm btn-outline-secondary">Share</button>
			<button class="btn btn-sm btn-outline-secondary">Export</button>
		</div>
		<button class="btn btn-sm btn-outline-secondary dropdown-toggle">
			<i class="fa fa-calendar"></i> This week
		</button>
	</div>
</div>
<div class="col-xs-12">
	<h2>Datos Anuales</h2>
	<p>Desde <?php echo strftime("%e de %B de %G", strtotime($reportes->desde())); ?> - Hasta <?php echo strftime("%e de %B de %G", strtotime($reportes->hasta())); ?></p>
	<div class="row">
		<div class="col">
			<h4>Datos Vida</h4>
			<ul>
				<?php if ( $prima_nueva_ingresada = $reportes->prima_nueva_ingresada("vida_crm") ) { ?>
				<li><b>Prima Nueva Ingresada: </b><?php echo money_format("%.2n", $prima_nueva_ingresada); ?> (Solicitudes ingresadas)</li>
				<?php } ?>
				<?php if ( $prima_nueva_emitida = $reportes->prima_nueva_emitida("vida_crm") ) { ?>
				<li><b>Prima Nueva Emitida: </b><?php echo money_format("%.2n", $prima_nueva_emitida); ?> (Pólizas por activar)</li>
				<?php } ?>
				<?php if ( $prima_nueva_anualizada = $reportes->prima_nueva_anualizada("vida") ) { ?>
				<li><b>Prima Nueva Anualizada: </b><?php echo money_format("%.2n", $prima_nueva_anualizada); ?> (Pólizas pagadas)</li>
				<?php } ?>
				<?php if ( $prima_pagada_nueva = $reportes->prima_pagada_nueva("vida") ) { ?>
				<li><b>Prima Pagada 1er Año: </b><?php echo money_format("%.2n", $prima_pagada_nueva); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_renovacion = $reportes->prima_pagada_renovacion("vida") ) { ?>
				<li><b>Prima Pagada Renovación: </b><?php echo money_format("%.2n", $prima_pagada_renovacion); ?> </li>
				<?php } ?>
			</ul>
		</div>
		<div class="col">
			<h4>Datos GMM</h4>
			<ul>
				<?php if ( $prima_nueva_ingresada = $reportes->prima_nueva_ingresada("gmm_crm") ) { ?>
				<li><b>Prima Nueva Ingresada: </b><?php echo money_format("%.2n", $prima_nueva_ingresada); ?> </li>
				<?php } ?>
				<?php if ( $prima_nueva_emitida = $reportes->prima_nueva_emitida("gmm_crm") ) { ?>
				<li><b>Prima Nueva Emitida: </b><?php echo money_format("%.2n", $prima_nueva_emitida); ?> </li>
				<?php } ?>
				<?php if ( $prima_nueva_anualizada = $reportes->prima_nueva_anualizada("gmm") ) { ?>
				<li><b>Prima Nueva Anualizada: </b><?php echo money_format("%.2n", $prima_nueva_anualizada); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_nueva = $reportes->prima_pagada_nueva("gmm") ) { ?>
				<li><b>Prima Pagada 1er Año: </b><?php echo money_format("%.2n", $prima_pagada_nueva); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_renovacion = $reportes->prima_pagada_renovacion("gmm") ) { ?>
				<li><b>Prima Pagada Renovación: </b><?php echo money_format("%.2n", $prima_pagada_renovacion); ?> </li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
<div class="col-xs-12">
	<h2>Datos Trimestrales</h2>
<?php 
$hoy = strtotime($reportes->hasta());
$x = 1;
do {
	$fecha_fin_trim = strtotime($reportes->desde() . " + 2 month");
	$fecha_fin_trim = strtotime("last day of " . date("F Y", $fecha_fin_trim));
	$fecha_fin_trim = date("Y-m-d", $fecha_fin_trim);
	$reportes->fecha_fin($fecha_fin_trim);
	
?>
	
	<div class="row">
		<div class="col-12 border-bottom">
			<h3>Trimestre <?php echo $x . " - " . strftime("%d-%b-%Y", strtotime($reportes->desde())); ?> al <?php echo strftime("%d-%b-%Y", strtotime($reportes->hasta())); ?></h3>
		</div>
		<div class="col">
			<h4>Datos Vida</h4>
			<ul>
				<?php if ( $prima_nueva_ingresada = $reportes->prima_nueva_ingresada("vida_crm") ) { ?>
				<li><b>Prima Nueva Ingresada: </b><?php echo money_format("%.2n", $prima_nueva_ingresada); ?> (Solicitudes ingresadas)</li>
				<?php } ?>
				<?php if ( $prima_nueva_emitida = $reportes->prima_nueva_emitida("vida_crm") ) { ?>
				<li><b>Prima Nueva Emitida: </b><?php echo money_format("%.2n", $prima_nueva_emitida); ?> (Pólizas por activar)</li>
				<?php } ?>
				<?php if ( $prima_nueva_anualizada = $reportes->prima_nueva_anualizada("vida") ) { ?>
				<li><b>Prima Nueva Anualizada: </b><?php echo money_format("%.2n", $prima_nueva_anualizada); ?> (Pólizas pagadas)</li>
				<?php } ?>
				<?php if ( $prima_pagada_nueva = $reportes->prima_pagada_nueva("vida") ) { ?>
				<li><b>Prima Pagada 1er Año: </b><?php echo money_format("%.2n", $prima_pagada_nueva); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_renovacion = $reportes->prima_pagada_renovacion("vida") ) { ?>
				<li><b>Prima Pagada Renovación: </b><?php echo money_format("%.2n", $prima_pagada_renovacion); ?> </li>
				<?php } ?>
			</ul>
		</div>
		<div class="col">
			<h4>Datos GMM</h4>
			<ul>
				<?php if ( $prima_nueva_ingresada = $reportes->prima_nueva_ingresada("gmm_crm") ) { ?>
				<li><b>Prima Nueva Ingresada: </b><?php echo money_format("%.2n", $prima_nueva_ingresada); ?> </li>
				<?php } ?>
				<?php if ( $prima_nueva_emitida = $reportes->prima_nueva_emitida("gmm_crm") ) { ?>
				<li><b>Prima Nueva Emitida: </b><?php echo money_format("%.2n", $prima_nueva_emitida); ?> </li>
				<?php } ?>
				<?php if ( $prima_nueva_anualizada = $reportes->prima_nueva_anualizada("gmm") ) { ?>
				<li><b>Prima Nueva Anualizada: </b><?php echo money_format("%.2n", $prima_nueva_anualizada); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_nueva = $reportes->prima_pagada_nueva("gmm") ) { ?>
				<li><b>Prima Pagada 1er Año: </b><?php echo money_format("%.2n", $prima_pagada_nueva); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_renovacion = $reportes->prima_pagada_renovacion("gmm") ) { ?>
				<li><b>Prima Pagada Renovación: </b><?php echo money_format("%.2n", $prima_pagada_renovacion); ?> </li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php 
	$fecha_inicio_trim = strtotime($reportes->hasta() . " + 1 day");
	$fecha_inicio_trim = date("Y-m-d", $fecha_inicio_trim);
	
	$fecha_fin_trim = strtotime($fecha_inicio_trim . " + 2 month");
	$fecha_fin_trim = strtotime("last day of " . date("F Y", $fecha_fin_trim));
	$fecha_fin_trim = date("Y-m-d", $fecha_fin_trim);
	
	$reportes->fecha_fin($fecha_fin_trim);
	$reportes->fecha_inicio($fecha_inicio_trim);
	
	$validacion = strtotime($reportes->desde());
	$x++;
	//echo "<h3>$validacion</h3><h3>$hoy</h3>";
} while ($validacion < $hoy);
	
	?>
</div>
<div class="col-xs-12">
	<h2>Datos Mensuales</h2>
<?php 
$reportes->fecha_inicio(date("Y-m-d", strtotime('first day of january this year')));
$hoy = strtotime($reportes->hasta());
$x = 1;
do {
	$fecha_fin_trim = strtotime("last day of " . $reportes->desde());
	$fecha_fin_trim = date("Y-m-d", $fecha_fin_trim);
	$reportes->fecha_fin($fecha_fin_trim);
	
?>
	
	<div class="row">
		<div class="col-12 border-bottom">
			<h3><?php echo strftime("%B %Y", strtotime($reportes->desde())); ?></h3>
		</div>
		<div class="col">
			<h4>Datos Vida</h4>
			<ul>
				<?php if ( $prima_nueva_ingresada = $reportes->prima_nueva_ingresada("vida_crm") ) { ?>
				<li><b>Prima Nueva Ingresada: </b><?php echo money_format("%.2n", $prima_nueva_ingresada); ?> (Solicitudes ingresadas)</li>
				<?php } ?>
				<?php if ( $prima_nueva_emitida = $reportes->prima_nueva_emitida("vida_crm") ) { ?>
				<li><b>Prima Nueva Emitida: </b><?php echo money_format("%.2n", $prima_nueva_emitida); ?> (Pólizas por activar)</li>
				<?php } ?>
				<?php if ( $prima_nueva_anualizada = $reportes->prima_nueva_anualizada("vida") ) { ?>
				<li><b>Prima Nueva Anualizada: </b><?php echo money_format("%.2n", $prima_nueva_anualizada); ?> (Pólizas pagadas)</li>
				<?php } ?>
				<?php if ( $prima_pagada_nueva = $reportes->prima_pagada_nueva("vida") ) { ?>
				<li><b>Prima Pagada 1er Año: </b><?php echo money_format("%.2n", $prima_pagada_nueva); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_renovacion = $reportes->prima_pagada_renovacion("vida") ) { ?>
				<li><b>Prima Pagada Renovación: </b><?php echo money_format("%.2n", $prima_pagada_renovacion); ?> </li>
				<?php } ?>
			</ul>
		</div>
		<div class="col">
			<h4>Datos GMM</h4>
			<ul>
				<?php if ( $prima_nueva_ingresada = $reportes->prima_nueva_ingresada("gmm_crm") ) { ?>
				<li><b>Prima Nueva Ingresada: </b><?php echo money_format("%.2n", $prima_nueva_ingresada); ?> </li>
				<?php } ?>
				<?php if ( $prima_nueva_emitida = $reportes->prima_nueva_emitida("gmm_crm") ) { ?>
				<li><b>Prima Nueva Emitida: </b><?php echo money_format("%.2n", $prima_nueva_emitida); ?> </li>
				<?php } ?>
				<?php if ( $prima_nueva_anualizada = $reportes->prima_nueva_anualizada("gmm") ) { ?>
				<li><b>Prima Nueva Anualizada: </b><?php echo money_format("%.2n", $prima_nueva_anualizada); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_nueva = $reportes->prima_pagada_nueva("gmm") ) { ?>
				<li><b>Prima Pagada 1er Año: </b><?php echo money_format("%.2n", $prima_pagada_nueva); ?> </li>
				<?php } ?>
				<?php if ( $prima_pagada_renovacion = $reportes->prima_pagada_renovacion("gmm") ) { ?>
				<li><b>Prima Pagada Renovación: </b><?php echo money_format("%.2n", $prima_pagada_renovacion); ?> </li>
				<?php } ?>
			</ul>
		</div>
	</div>
<?php 
	$fecha_inicio_trim = strtotime($reportes->hasta() . " + 1 day");
	$fecha_inicio_trim = date("Y-m-d", $fecha_inicio_trim);
	
	$fecha_fin_trim = strtotime("last day of " . $fecha_inicio_trim);
	$fecha_fin_trim = date("Y-m-d", $fecha_fin_trim);
	
	$reportes->fecha_fin($fecha_fin_trim);
	$reportes->fecha_inicio($fecha_inicio_trim);
	
	$validacion = strtotime($reportes->desde());
	$x++;
	//echo "<h3>$validacion</h3><h3>$hoy</h3>";
} while ($validacion < $hoy);
	
	?>
</div>