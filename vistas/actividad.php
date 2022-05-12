<?php 
$asesor = $GLOBALS["sitio"]->usuario_activo();
$info_asesor = $asesor->info();
$actividad = new Actividad ($asesor);

$polizas_pagadas = 0;
$polizas_pagadas_anuales = 0;

if ( isset($_POST["total_puntos"]) ) {
	$actividad->guarda_actividad();
}
// Arma las fechas según a cuál le picaron (semana pasada o semana siguiente)
if ( isset($_POST["sel_fecha"]) ) {
	if ( $fecha_inicio = $actividad->revisa_fecha($_POST["sel_fecha"]) ) {
		if ( strtotime($fecha_inicio) > strtotime("2018-04-09") ) {
			$flecha_ant = true;
			if ( strtotime($fecha_inicio) < strtotime("today") ) {
				$sabado = date('Y-m-d', strtotime('previous saturday', strtotime($fecha_inicio)));
				$viernes = date('Y-m-d', strtotime('next friday', strtotime($fecha_inicio)));
				$actividad->fecha_inicio($sabado);
				$actividad->fecha_fin($viernes);
				$flecha_sig = true;
			} else {
				$flecha_sig = false;
			}
		} else {
			$flecha_ant = false;
			$flecha_sig = true;
			$actividad->fecha_inicio("2018-04-07");
			$actividad->fecha_fin("2018-04-13");
		}
	}
} else {
	$flecha_ant = true;
	$flecha_sig = false;
}
// Arma información para los admins, select de asesores para ver su información y resumen semanal de los asesores
if ( $asesor->esAdmin() ) {
	$forma_sel_asesores = $actividad->forma_sel_asesores();
	if ( isset($_POST["id_asesor_sel"]) ) {
		$id_asesor_sel = preg_replace("/[^0-9]/", "", $_POST["id_asesor_sel"]);
		if ( $as = $actividad->asesores_activos() ) {
			$txt_asesor_sel = $as[$id_asesor_sel]['nombre_completo'];
		}
	}
	$actividad->tabla_resumen_actividad ();
	
} else {
	$forma_sel_asesores = "";
	$id_asesor_sel = "";
}

$lunes_pasado = date('Y-m-d', strtotime('previous monday', strtotime($actividad->desde())));
$siguiente_lunes = date('Y-m-d', strtotime('next monday', strtotime($actividad->hasta())));

?>
<script type="text/javascript" src="<?php echo DOMINIO;?>libs/datatables/datatables.min.js"></script>
<script type="text/javascript" src="<?php echo DOMINIO; ?>libs/datepicker/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="<?php echo DOMINIO; ?>libs/datepicker/js/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="<?php echo DOMINIO; ?>libs/chartist/chartist.min.js"></script>
<link href="<?php echo DOMINIO; ?>libs/datatables/datatables.min.css" rel="stylesheet">
<link href="<?php echo DOMINIO; ?>libs/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
<link href="<?php echo DOMINIO; ?>libs/chartist/chartist.min.css" rel="stylesheet">
<div class="container">
	<div class="row mb-5 pb-3">
		<div class="col-12 text-center mb-5 mt-3">
			<h3>Resumen de la semana</h3>
			<h5>Del <?php $lunes_tit = date("Y-m-d", strtotime("{$actividad->desde()} +2day ")); echo "{$actividad->fecha_formato($lunes_tit)} hasta el {$actividad->fecha_formato($actividad->hasta())}" ?></h5>
			<?php if( isset($txt_asesor_sel) ) { ?>
			<h5>De <?php echo $txt_asesor_sel; ?></h5>
			<?php } ?>
		</div>
		<?php if ( $asesor->esAdmin() ) { ?>
			<div class="col-12 text-center mb-5 mt-3">
				<?php echo $actividad->tabla_resumen_actividad(); ?>
			</div>
		<?php } ?>
		<div id="cont_resumen" class="col-12 col-md-5">
			<div class="row">
				<div class="col-12">
					<div id="cont_grafs">
						<div id="graf_puntos" class="ct-chart ct-golden-section"></div>
						<div id="graf_puntos2" class="ct-chart ct-golden-section"></div>
					</div>
				</div>
				<div class="col-12 text-center">
					<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_forma_registro">
						<i class="fas fa-pencil-alt mr-2"></i>Registra tu actividad
					</button>
				</div>
			</div>
		</div>
		<div id="cont_tabla" class="col-12 col-md-7">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="form-group row">
							<div class="col-1 text-left px-0">
								<?php if( $flecha_ant ) { ?>
								<form action="<?php echo DOMINIO . "actividad/" ?>" method="post">
									<input type="text" readonly class="d-none form-control-plaintext form-inline" name="sel_fecha" value="<?php echo $lunes_pasado; ?>">
									<?php if ( isset($_POST["id_asesor_sel"]) ) { ?>
										<input type='text' readonly class='form-control-plaintext d-none' name='id_asesor_sel' value='<?php echo $_POST["id_asesor_sel"];?>'>
									<?php } ?>
									<button type="submit" class="btn btn-primary btn-sm pull-left"><i class="fas fa-angle-double-left"></i></button>
								</form>
								<?php } ?>
							</div>
							<div class="col-10 text-center">
								<?php echo $forma_sel_asesores; ?>
							</div>
							<div class="col-1 text-right px-0">
								<?php if( $flecha_sig ) { ?>
								<form action="<?php echo DOMINIO . "actividad/" ?>" method="post">
									<input type="text" readonly class="d-none form-control-plaintext form-inline" name="sel_fecha" value="<?php echo $siguiente_lunes; ?>">
									<?php if ( isset($_POST["id_asesor_sel"]) ) { ?>
										<input type='text' readonly class='form-control-plaintext d-none' name='id_asesor_sel' value='<?php echo $_POST["id_asesor_sel"];?>'>
									<?php } ?>
									<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-angle-double-right"></i></button>
								</form>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="col-12">
					</div>
				</div>
				<?php echo $actividad->resumen_semanal($id_asesor_sel); ?>
			</div>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="modal_forma_registro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form id="forma_registro_act" action="<?php echo DOMINIO . "actividad/" ?>" method="post">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Registra tu Actividad</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="row">
							<div id="cont_registro" class="col-12 col-md-12">		
								<div class="card text-center mx-auto my-3 w-75 activo" id="card_fecha">
									<div class="card-body">
										<h5 class="card-title">Selecciona la fecha a regsitrar</h5>
										<div class="text-center my-3 mx-auto">
											<div id="fecha"></div>
											<input type="hidden" id="input_fecha"/>
										</div>
									</div>
									<div class="card-footer">
										<div class="row">
											<div class="col-12">
												<h5 id="seleccion_fecha"></h5>
											</div>
										</div>
									</div>
								</div>
								<div class="card text-center mx-auto my-3 w-75 d-none" id="registro_puntos">
									<div class="card-body">
										<h5 class="card-title">Registra tu actividad del día</h5>
										<p id="txt_seleccion_fecha" class="my-0 text-center"></p>
										<input type="text" readonly class="d-none form-control-plaintext form-inline w-50 mx-auto text-center" id="res_input_fecha" name="res_input_fecha" value="0">
										<?php echo $actividad->forma_registro(); ?>
									</div>
									<div class="card-footer">
										<div class="form-group row">
											<div class="col-sm-2 text-left">
												
											</div>
											<label for="total_puntos" class="col-sm-5 col-form-label text-right">Total Puntos:</label>
											<div class="col-sm-3 text-left">
												<input type="text" readonly class="form-control-plaintext form-inline w-50" id="total_puntos" name="total_puntos" value="0">
											</div>
											<div class="col-sm-2">
												
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button> -->
						<button type="button" class="btn btn-primary btn-sm float-left regresar d-none">Regresar</button>
						<button type="button" class="btn btn-primary siguiente d-none">Siguiente</button>
						<button id="guardar" type="submit" class="btn btn-primary btn-sm float-right d-none">¡Guardar!</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<style>
	.datepicker-inline {
		margin: 0 auto;
	}
	label.col-form-label {
		font-size: 17px;
	}
	.ct-series-a .ct-slice-donut-solid {
		fill: #FF0000;
	}
	.ct-series-b .ct-slice-donut-solid {
		fill: #FF530D;
	}
	.ct-series-c .ct-slice-donut-solid {
		fill: #FFFC19;
	}
	.ct-series-d .ct-slice-donut-solid {
		fill: #00B233;
	}
	.ct-series-e .ct-slice-donut-solid {
		fill: #D8AF46;
	}
	#graf_puntos2 .ct-series-a .ct-slice-donut-solid {
		fill: #264583;
	}
	#graf_puntos2 {
		position: absolute;
		top: 0%;
		left: 0%;
	}
	#cont_grafs {
		position: relative;
	}
	#graf_puntos .ct-label {
		font-size: 14px;
		font-weight: bold;
	}
	#graf_puntos2 .ct-label {
		font-size: 35px;
	}
</style>
<script type="text/javascript">
	$(document).ready( function () {
		var $polizas_pagadas = <?php echo $polizas_pagadas; ?>;
		var $polizas_pagadas_anuales = <?php echo $polizas_pagadas_anuales; ?>;
		
		$('#fecha').datepicker({
			format: "yyyy-mm-dd",
			startDate: new Date("2018-04-09"),
			endDate: new Date(),
			maxViewMode: 2,
			todayBtn: "linked",
			language: "es",
			daysOfWeekHighlighted: "0,6"
		});
	});
	
</script>
<script type="text/javascript">
	$(document).ready( function () {
		var total_puntos = $("#total_pts").text();
		new Chartist.Pie('#graf_puntos', {
			labels: ['0 a 30','30 a 60','60 a 80','80 a 100','100 +'],
			series: [
				{
					value: 30,
					name: '0 a 30',
					meta: 'Meta One'
				},
				{
					value: 30,
					name: '30 a 60',
					meta: 'Meta One'
				},
				{
					value: 20,
					name: '60 a 80',
				},
				{
					value: 20,
					name: '80 a 100',
				},
				{
					value: 20,
					name: 'más de 100',
				}	
			]
		}, {
		  donut: true,
		  donutWidth: 50,
		  donutSolid: true,
		  startAngle: 270,
		  total: 240,
		  showLabel: true
		});
		new Chartist.Pie('#graf_puntos2', {
			labels: [(total_puntos + ' pts' )],
			series: [
				{
					value: total_puntos,
					name: 'puntos',
					meta: 'Puntos Asesor'
				}	
			]
		}, {
		  donut: true,
		  donutWidth: 10,
		  donutSolid: true,
		  startAngle: 270,
		  total: 240,
		  showLabel: true
		});
	});
</script>

<script type="text/javascript" src="<?php echo DOMINIO; ?>js/actividad.js"></script>