<?php
$time = microtime(TRUE);
$mem = memory_get_usage();

$asesor = $GLOBALS["sitio"]->usuario_activo();
$info = new Info ($asesor);
$info_asesores = new InfoAsesores ($asesor);

if ( isset( $_POST["forma_fecha_inicio"] ) ) {
	$forma_fecha_inicio = $_POST["forma_fecha_inicio"];
	$info->cambia_fecha_inicio($forma_fecha_inicio);
	$info_asesores->cambia_fecha_inicio($forma_fecha_inicio);
}
if ( isset( $_POST["forma_fecha_fin"] ) ) {
	$forma_fecha_fin = $_POST["forma_fecha_fin"];
	$info->cambia_fecha_fin($forma_fecha_fin);
	$info_asesores->cambia_fecha_fin($forma_fecha_fin);
}

//$info->cambia_fecha_inicio('2018-01-01');

$resumen_asesores = $info_asesores->resumen_asesores();
//$pruebas2 = $info_asesores->prima_pagada_nueva("vida");
//print_r($resumen_asesores);
//echo "<br/><br/>";
//print_r($pruebas2);

$meta_agentes_nuevos = 8;
$agentes_conectados = $info->agentes_conectados();

?>
<style>
.agentes {
    font-size: 32px;
    margin: 5px 8px;
    color: #264583;
}

.goal {
  font-size: 24px;
  text-align: right;
  @media only screen and (max-width : 640px) {
    text-align: center;  
  }
  
}

.glass {
  width: 100%;
  height: 20px;
  /*background: #c7c7c7;*/
  background: #939694;
  border-radius: 10px;
  float: left;
  overflow: hidden;
}

.progress {
  float: left;
  height: 20px;
  /*background: #FF5D50;*/
  background: #264583;
  z-index: 333;
  //border-radius: 5px;
}

.goal-stat {
  width: 25%;
  //height: 30px;
  padding: 10px;
  float: left;
  margin: 0;
  
  @media only screen and (max-width : 640px) {
    width: 50%;
    text-align: center;
  }
}

.goal-number, .goal-label {
  display: block;
}

.goal-number {
  font-weight: bold;
}
.reclutamiento span {
	font-size: 35px;
	margin-left: 20px;
	font-weight: bold;
}
.totales, .tabla_resumen tr th {
	border-bottom: 2px solid #000;
}
.card li {
	list-style: none;
	margin: 0 3px;
	font-size: 16px;
}
.pols {
	margin: 0 5px;
	color: #92a7c7;
}
.pols.p21 {
	color: #264583;
}
</style>
<script type="text/javascript" src="<?php echo DOMINIO; ?>libs/datepicker/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="<?php echo DOMINIO; ?>libs/datepicker/locales/bootstrap-datepicker.es.min.js" charset="UTF-8"></script>
<link href="<?php echo DOMINIO; ?>libs/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2">Avances promotoría al <?php echo $info->fecha_fin() ?></h1>
	<div class="btn-toolbar mb-2 mb-md-0 justify-content-between">
		<div class="btn-group mb-1 ml-1" role="group" aria-label="Second group">
			<button id="escoge_fechas" class="btn btn-sm btn-outline-secondary" type="button">
				<i class="fa fa-calendar"></i> Escoger Fechas
			</button>
		</div>
		<div id="inputs_fechas" class="input-group input-daterange input-rango-fechas ml-1 d-none">
			<form class="forma_fechas d-flex" action="<?php echo DOMINIO . "{$_GET["vista"]}"; ?>" method="post">
				<div class="input-group-prepend">
					<div class="input-group-text" id="btnGroupAddon1">Desde</div>
				</div>
				<input type="text" name="forma_fecha_inicio" class="form-control" value="<?php echo $info->f_inicio(); ?>">
				<div class="input-group-prepend ml-1">
					<div class="input-group-text" id="btnGroupAddon2">Hasta</div>
				</div>
				<input type="text" name="forma_fecha_fin" class="form-control" value="<?php echo $info->f_fin(); ?>">
				<button class="btn btn-sm btn-secondary ml-2" type="submit">Consultar <i class="fa fa-paper-plane"></i></button>
			</form>
		</div>
	</div>
</div>
<div class="col-xs-12">
	<h2>Acumulado</h2>
	<p>Desde <?php echo $info->fecha_inicio(); ?> - Hasta <?php echo $info->fecha_fin(); ?></p>
	<div class="row">
		<div class="col">
			<div class="card shadow-sm">
				<div class="card-body">
					<h4 class="card-title text-center">Datos Vida</h4>
					<ul>
						<li><b>Prima Ingresada: </b>
							<?php 
								if ( $negocios_ingresados_vida = $info->negocios_ingresados("vida") ) {
									echo money_format("%.2n", $negocios_ingresados_vida["prima_anual"]) . "<br/>" . $negocios_ingresados_vida["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php 
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Emitida: </b>
							<?php 
								if ( $negocios_emitidos_vida = $info->negocios_emitidos("vida") ) {
									echo money_format("%.2n", $negocios_emitidos_vida["prima_anual"]) . "<br/>" . $negocios_emitidos_vida["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Nueva Anualizada: </b>
							<?php 
								if ( $negocios_nuevos_activos = $info->negocios_nuevos_activos("vida") ) {
									echo money_format("%.2n", $negocios_nuevos_activos["prima_anual"]) . "<br/>" . $negocios_nuevos_activos["polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $negocios_nuevos_activos["polizas_21"]; ?><i class="fas fa-file-alt pols p21"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Pagada 1er año: </b>
							<?php 
								if ( $prima_pagada_nueva_vida = $info->prima_pagada_nueva("vida") ) {
									echo money_format("%.2n", $prima_pagada_nueva_vida); 
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Pagada Renovación: </b>
							<?php 
								if ( $prima_pagada_renovacion_vida = $info->prima_pagada_renovacion("vida") ) {
									echo money_format("%.2n", $prima_pagada_renovacion_vida); 
								} else {
									echo 0;
								}
							?>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card shadow-sm">
				<div class="card-body">
					<h4 class="card-title text-center">Datos MetaLife</h4>
					<ul>
						<li><b>Prima Ingresada: </b>
							<?php 
								if ( $negocios_ingresados_metalife = $info->negocios_ingresados("MetaLife") ) {
									echo money_format("%.2n", $negocios_ingresados_metalife["prima_anual"]) . "<br/>" . $negocios_ingresados_metalife["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Emitida: </b>
							<?php 
								if ( $negocios_emitidos_metalife = $info->negocios_emitidos("MetaLife") ) {
									echo money_format("%.2n", $negocios_emitidos_metalife["prima_anual"]) . "<br/>" . $negocios_emitidos_metalife["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Nueva Anualizada: </b>
							<?php 
								if ( $negocios_nuevos_activos_metalife = $info->negocios_nuevos_activos("Metalife") ) {
									echo money_format("%.2n", $negocios_nuevos_activos_metalife["prima_anual"]) . "<br/>" . $negocios_nuevos_activos_metalife["polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $negocios_nuevos_activos_metalife["polizas_21"]; ?><i class="fas fa-file-alt pols p21"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Pagada 1er año: </b>
							<?php 
								if ( $prima_pagada_nueva_metalife = $info->prima_pagada_nueva("MetaLife") ) {
									echo money_format("%.2n", $prima_pagada_nueva_metalife); 
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Pagada Renovación: </b>
							<?php 
								if ( $prima_pagada_renovacion_metalife = $info->prima_pagada_renovacion("Metalife") ) {
									echo money_format("%.2n", $prima_pagada_renovacion_metalife); 
								} else {
									echo 0;
								}
							?>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card shadow-sm">
				<div class="card-body">
					<h4 class="card-title text-center">Datos GMM</h4>
					<ul>
						<li><b>Prima Ingresada: </b>
							<?php 
								if ( $negocios_ingresados_gmm = $info->negocios_ingresados("gmm") ) {
									echo money_format("%.2n", $negocios_ingresados_gmm["prima_anual"]) . "<br/>" . $negocios_ingresados_gmm["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Emitida: </b>
							<?php 
								if ( $negocios_emitidos_gmm = $info->negocios_emitidos("gmm") ) {
									echo money_format("%.2n", $negocios_emitidos_gmm["prima_anual"]) . "<br/>" . $negocios_emitidos_gmm["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Nueva Anualizada: </b>
							<?php 
								if ( $negocios_nuevos_activos_gmm = $info->negocios_nuevos_activos("GMM") ) {
									echo money_format("%.2n", $negocios_nuevos_activos_gmm["prima_anual"]) . "<br/>" . $negocios_nuevos_activos_gmm["polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $negocios_nuevos_activos_gmm["polizas_21"];  ?><i class="fas fa-file-alt pols p21"></i><?php
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Pagada 1er año: </b>
							<?php 
								if ( $prima_pagada_nueva_gmm = $info->prima_pagada_nueva("GMM") ) {
									echo money_format("%.2n", $prima_pagada_nueva_gmm); 
								} else {
									echo 0;
								}
							?>
						</li>
						<li><b>Prima Pagada Renovación: </b>
							<?php 
								if ( $prima_pagada_renovacion_gmm = $info->prima_pagada_renovacion("GMM") ) {
									echo money_format("%.2n", $prima_pagada_renovacion_gmm); 
								} else {
									echo 0;
								}
							?>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<?php 
$meta_vida = 6000000;
$meta_metalife = 3500000;
$meta_gmm = 1600000;
$avance_metas = array(
	"vida" => round((($prima_pagada_nueva_vida / $meta_vida) * 100),2),
	"metalife" => round((($prima_pagada_nueva_metalife / $meta_metalife) * 100),2),
	"gmm" => round((($prima_pagada_nueva_gmm / $meta_gmm) * 100),2)
);
//print_r($avance_metas);
?>
<div class="col-xs-12 mt-3 mb-3">
	<h3><span id="countdown"></span> Días para lograrlo</h3>
	<div class="row">
		<div class="col">
			<h3>Vida</h3>
			<div class="goal">Meta: <?php echo money_format("%.2n", $meta_vida); ?></div>
			<div class="glass">
				<div class="progress" style="width:<?php echo $avance_metas["vida"]; ?>%">
				</div>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $avance_metas["vida"]; ?>%</span>
				<span class="goal-label">Logrado</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo money_format("%.2n", $prima_pagada_nueva_vida);?></span>
				<span class="goal-label">Pagado Vida</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $negocios_nuevos_activos["polizas"]; ?></span>
				<span class="goal-label">Pólizas</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $negocios_nuevos_activos["prima_anual"]; ?></span>
				<span class="goal-label">PNA</span>
			</div>
		</div>
		<div class="col">
			<h3>MetaLife</h3>
			<div class="goal">Meta: <?php echo money_format("%.2n", $meta_metalife); ?></div>
			<div class="glass">
				<div class="progress" style="width:<?php echo $avance_metas["metalife"]; ?>%">
				</div>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $avance_metas["metalife"]; ?>%</span>
				<span class="goal-label">Logrado</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo money_format("%.2n", $prima_pagada_nueva_metalife);?></span>
				<span class="goal-label">Pagado Vida</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $negocios_nuevos_activos_metalife["polizas"]; ?></span>
				<span class="goal-label">Pólizas</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $negocios_nuevos_activos_metalife["prima_anual"]; ?></span>
				<span class="goal-label">PNA</span>
			</div>
		</div>
		<div class="col">
			<h3>GMM</h3>
			<div class="goal">Meta: <?php echo money_format("%.2n", $meta_gmm); ?></div>
			<div class="glass">
				<div class="progress" style="width:<?php echo $avance_metas["gmm"]; ?>%">
				</div>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $avance_metas["gmm"]; ?>%</span>
				<span class="goal-label">Logrado</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo money_format("%.2n", $prima_pagada_nueva_gmm);?></span>
				<span class="goal-label">Pagado Vida</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $negocios_nuevos_activos_gmm["polizas"]; ?></span>
				<span class="goal-label">Pólizas</span>
			</div>
			<div class="goal-stat">
				<span class="goal-number"><?php echo $negocios_nuevos_activos_gmm["prima_anual"]; ?></span>
				<span class="goal-label">PNA</span>
			</div>
		</div>
	</div>
</div>
<div class="col-xs-12">
	<h3>Reclutamiento</h3>
	<div class="row">
		<div class="col reclutamiento">
			<p>
			<?php 
				for( $i = 1; $i <= $agentes_conectados["novel"]; $i++ ) {
					?> <i class="fas fa-user agentes"></i> <?php
				}
				for( $i = 1; $i <= ($meta_agentes_nuevos - $agentes_conectados["novel"]); $i++ ) {
					?> <i class="far fa-user agentes"></i> <?php
				} 
			?>
			<span><?php echo $agentes_conectados["novel"] . " / " . $meta_agentes_nuevos; ?> </span>
			</p>
		</div>
	</div>
</div>
<div class="col-xs-12 mt-3 mb-3">
	<h2>Datos por asesor</h2>
	<div class="row">
		<div class="col table-responsive tabla_resumen">
			<table class="table table-hover">
				<thead class="thead-dark">
					<tr>
						<th scope="col">Asesor</th>
						<th scope="col"></th>
						<th scope="col">Ingresado</th>
						<th scope="col">Emitido</th>
						<th scope="col">Anualizado</th>
						<th scope="col">Pagado</th>
						<th scope="col">Renovación</th>
					</tr>
				</thead>
				<tbody>
				<?php 
					foreach ( $resumen_asesores as $asesor => $datos ) {
						$totales = array ();
						?>
						<tr>
							<th scope="row" rowspan="4"><?php echo $asesor; ?></th>
							<td>Datos Vida</td>
							<td>
							<?php 
								if( $datos["negocios_ingresados_vida"] ) {
									$totales["negocios_ingresados_solicitudes"] = $datos["negocios_ingresados_vida"]["solicitudes"];
									$totales["negocios_ingresados_prima"] = $datos["negocios_ingresados_vida"]["prima_anual"];
									echo $datos["negocios_ingresados_vida"]["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $datos["negocios_ingresados_vida"]["prima_anual"]);
								} else {
									$totales["negocios_ingresados_solicitudes"] = 0;
									$totales["negocios_ingresados_prima"] = 0;
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								if( $datos["negocios_emitidos_vida"] ) {
									$totales["negocios_emitidos_solicitudes"] = $datos["negocios_emitidos_vida"]["solicitudes"];
									$totales["negocios_emitidos_prima"] = $datos["negocios_emitidos_vida"]["prima_anual"];
									echo $datos["negocios_emitidos_vida"]["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $datos["negocios_emitidos_vida"]["prima_anual"]);
								} else {
									$totales["negocios_emitidos_solicitudes"] = 0;
									$totales["negocios_emitidos_prima"] = 0;
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								if( $datos["negocios_nuevos_activos_vida"] ) { 
									$totales["negocios_nuevos_activos_polizas"] = $datos["negocios_nuevos_activos_vida"]["polizas"];
									$totales["negocios_nuevos_activos_polizas_21"] = $datos["negocios_nuevos_activos_vida"]["polizas_21"];
									$totales["negocios_nuevos_activos_prima"] = $datos["negocios_nuevos_activos_vida"]["prima_anual"];
									echo $datos["negocios_nuevos_activos_vida"]["polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $datos["negocios_nuevos_activos_vida"]["polizas_21"]; ?><i class="fas	 fa-file-alt pols p21"></i><?php echo money_format("%.2n", $datos["negocios_nuevos_activos_vida"]["prima_anual"]);
								} else {
									$totales["negocios_nuevos_activos_polizas"] = 0;
									$totales["negocios_nuevos_activos_polizas_21"] = 0;
									$totales["negocios_nuevos_activos_prima"] = 0;
									echo 0;
								}
							?>
							</td>
							<td>
							<?php
								$totales["prima_pagada_nueva"] = $datos["prima_pagada_nueva_vida"];
								echo money_format("%.2n", $datos["prima_pagada_nueva_vida"]);
							?>
							</td>
							<td>
							<?php 
								$totales["prima_pagada_renovacion"] = $datos["prima_pagada_renovacion_vida"];
								echo money_format("%.2n", $datos["prima_pagada_renovacion_vida"]);
							?>
							</td>
						</tr>
						<tr>
							<td>Datos MetaLife</td>
							<td>
							<?php 
								if( $datos["negocios_ingresados_metalife"] ) { 
									echo $datos["negocios_ingresados_metalife"]["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $datos["negocios_ingresados_metalife"]["prima_anual"]);
								} else {
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								if( $datos["negocios_emitidos_metalife"] ) { 
									echo $datos["negocios_emitidos_metalife"]["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $datos["negocios_emitidos_metalife"]["prima_anual"]);
								} else {
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								if( $datos["negocios_nuevos_activos_metalife"] ) { 
									echo $datos["negocios_nuevos_activos_metalife"]["polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $datos["negocios_nuevos_activos_metalife"]["polizas_21"]; ?><i class="fas fa-file-alt pols p21"></i><?php echo money_format("%.2n", $datos["negocios_nuevos_activos_metalife"]["prima_anual"]);
								} else {
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								echo money_format("%.2n", $datos["prima_pagada_nueva_metalife"]);
							?>
							</td>
							<td>
							<?php 
								echo money_format("%.2n", $datos["prima_pagada_renovacion_metalife"]);
							?>
							</td>
						</tr>
						<tr>
							<td>Datos GMM</td>
							<td>
							<?php 
								if( $datos["negocios_ingresados_gmm"] ) { 
									$totales["negocios_ingresados_solicitudes"] += $datos["negocios_ingresados_gmm"]["solicitudes"];
									$totales["negocios_ingresados_prima"] += $datos["negocios_ingresados_gmm"]["prima_anual"];
									echo $datos["negocios_ingresados_gmm"]["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $datos["negocios_ingresados_gmm"]["prima_anual"]);
								} else {
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								if( $datos["negocios_emitidos_gmm"] ) { 
									$totales["negocios_emitidos_solicitudes"] += $datos["negocios_emitidos_gmm"]["solicitudes"];
									$totales["negocios_emitidos_prima"] += $datos["negocios_emitidos_gmm"]["prima_anual"];
									echo $datos["negocios_emitidos_gmm"]["solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $datos["negocios_emitidos_gmm"]["prima_anual"]);
								} else {
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								if( $datos["negocios_nuevos_activos_gmm"] ) { 
									$totales["negocios_nuevos_activos_polizas"] += $datos["negocios_nuevos_activos_gmm"]["polizas"];
									$totales["negocios_nuevos_activos_polizas_21"] += $datos["negocios_nuevos_activos_gmm"]["polizas_21"];
									$totales["negocios_nuevos_activos_prima"] += $datos["negocios_nuevos_activos_gmm"]["prima_anual"];
									echo $datos["negocios_nuevos_activos_gmm"]["polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $datos["negocios_nuevos_activos_gmm"]["polizas_21"]; ?><i class="fas fa-file-alt pols p21"></i><?php echo money_format("%.2n", $datos["negocios_nuevos_activos_gmm"]["prima_anual"]);
								} else {
									echo 0;
								}
							?>
							</td>
							<td>
							<?php 
								$totales["prima_pagada_nueva"] += $datos["prima_pagada_nueva_gmm"];
								echo money_format("%.2n", $datos["prima_pagada_nueva_gmm"]);
							?>
							</td>
							<td>
							<?php 
								$totales["prima_pagada_renovacion"] += $datos["prima_pagada_renovacion_gmm"];
								echo money_format("%.2n", $datos["prima_pagada_renovacion_gmm"]);
							?>
							</td>
						</tr>
						<tr class="totales">
							<td>Total</td>
							<td>
							<?php
								echo $totales["negocios_ingresados_solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $totales["negocios_ingresados_prima"]);
							?>
							</td>
							<td>
							<?php
								echo $totales["negocios_emitidos_solicitudes"]; ?><i class="far fa-file-alt pols"></i><?php echo money_format("%.2n", $totales["negocios_emitidos_prima"]);
							?>
							</td>
							<td>
							<?php
								echo $totales["negocios_nuevos_activos_polizas"]; ?><i class="far fa-file-alt pols"></i><?php echo $totales["negocios_nuevos_activos_polizas_21"]; ?><i class="fas fa-file-alt pols p21"></i><?php echo money_format("%.2n", $totales["negocios_nuevos_activos_prima"]);
							?>
							</td>
							<td><?php echo money_format("%.2n", $totales["prima_pagada_nueva"]); ?></td>
							<td><?php echo money_format("%.2n", $totales["prima_pagada_renovacion"]); ?></td>
						</tr>
						<?php
					}
				?>
				</tbody>
			</table>
		</div>
	</div>	
</div>
<div class="separador d-flex col-xs-12 mt-3 mb-3">
</div>
<script type="text/javascript">
	var hoy = new Date();
	$('.input-rango-fechas input').each(function() {
		$(this).datepicker({
			//defaultViewDate: hoy
			format: "yyyy-mm-dd",
			language: "es-MX",
			clearBtn: true,
			todayBtn: "linked",
			todayHighlight: true,
			weekStart: 1,
			startDate: "2015-01-01",
			endDate: hoy
		});
	});
	
	$("#escoge_fechas").click( function() {
		var inputs = $("#inputs_fechas");
		if( inputs.hasClass("d-none") ) {
			inputs.removeClass("d-none");
			//inputs.addClass("d-flex");
		} else {
			//inputs.removeClass("d-flex");
			inputs.addClass("d-none");
		}
	});
	
	CountDownTimer('12/31/2019 11:59 PM', 'countdown');
    //CountDownTimer('12/20/2020 10:1 AM', 'newcountdown');

    function CountDownTimer(dt, id)
    {
        var end = new Date(dt);

        var _second = 1000;
        var _minute = _second * 60;
        var _hour = _minute * 60;
        var _day = _hour * 24;
        var timer;

        function showRemaining() {
            var now = new Date();
            var distance = end - now;
            if (distance < 0) {

                clearInterval(timer);
                document.getElementById(id).innerHTML = '0';

                return;
            }
            var days = Math.floor(distance / _day);
            var hours = Math.floor((distance % _day) / _hour);
            var minutes = Math.floor((distance % _hour) / _minute);
            var seconds = Math.floor((distance % _minute) / _second);

            document.getElementById(id).innerHTML = days /*+ ' days'*/;
            //document.getElementById(id).innerHTML += hours + 'hrs ';
            //document.getElementById(id).innerHTML += minutes + 'mins ';
            //document.getElementById(id).innerHTML += seconds + 'secs';
        }

        timer = setInterval(showRemaining, 1000);
    }
</script>
<?php 
print_r(array(
  'memory' => (memory_get_usage() - $mem) / (1024 * 1024),
  'seconds' => microtime(TRUE) - $time
));
?>