<?php 
$actualiza = new Actualiza();

if ( $datos_bds_pp = $actualiza->trae_datos_todos() ) {
	$datos_bds_pp_json = json_encode($datos_bds_pp, JSON_UNESCAPED_UNICODE);
} else {
	echo "<h1>Error con la info</h1>";
}
/*if ( $atualiza_registros_crm = $actualiza->atualiza_registros_crm() ) {
	print_r($atualiza_registros_crm);
}*/
?>
<style type="text/css">
	.fila:not(:first-child) {
		margin:32px 0;
	}
	.arch_sobre {
		background-color: #bfbfbf;
		border-color: #f5f5f5;
	}
	#click_mas_arch #click_arch {
		display: none;
	}
	#cont_mas {
		display: block;
		width: 60px;
		margin: 0 auto;
		cursor: pointer;
	}
	#area_soltar {
		min-height: 20vh;
	}
</style>
<link href="<?php echo DOMINIO; ?>libs/datatables/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo DOMINIO;?>libs/datatables/datatables.min.js"></script>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2">Actualizador de datos de Prima Pagada MetLife</h1>
	<div class="btn-toolbar mb-2 mb-md-0">
		<!--<div class="btn-group mr-2">
			<button class="btn btn-sm btn-outline-secondary">Share</button>
			<button class="btn btn-sm btn-outline-secondary">Export</button>
		</div>
		<button class="btn btn-sm btn-outline-secondary dropdown-toggle">
			<i class="fa fa-calendar"></i> This week
		</button>-->
	</div>
</div>
<div class="row col-xs-12">
	<div class="col-md-6">
		<h3>Datos del servidor</h3>
		<ul>
			<li>Total de registros: <?php echo $actualiza->registros(); ?></li>
			<li>última fecha de pago: <?php echo $actualiza->ult_fecha_pago(); ?></li>
			<li>Registros del mes: <?php echo $actualiza->registros_mes(); ?></li>
			<li>Último corte: <?php echo $actualiza->ult_corte(); ?></li>
		</ul>
	</div>
	<div class="col-md-6">
		<div id="area_soltar" class="row text-center justify-content-center">
			<div class="align-self-center">
				<h5 class="">Suelta aquí el archivo a actualizar</h5>
				<div id="click_mas_arch">
					<div id="cont_mas">
						<i class="fa fa-plus fa-4x"></i>
					</div>
					<input type="file" name="click_arch" id="click_arch" />
				</div>
			</div>
		</div>
	</div>
</div>
<div id="respuesta" class="col-xs-12 mt-3">

</div>
<div class="col-xs-12 mt-3">
	<div class="table-responsive">
		<table id="tabla_pagos" class="display table table-condensed table-hover table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th>Corte</th>
					<th>Póliza</th>
					<th>Ramo</th>
					<th>Asegurado</th>
					<th>Tipo Comisión</th>
					<th>Vigor</th>
					<th>Folio</th>
					<th>Fecha Pago</th>
					<th>Monto</th>
					<th>ID Ase.</th>
					<th>Asesor</th>
					<th>Status Ase.</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var datos = <?php echo $datos_bds_pp_json; ?>;
		//var datos = JSON.parse(datos_json);
		//console.log(datos);
		var table = $("#tabla_pagos").DataTable( {
			data: datos,
			columns: [
				{ data: 'idpagos' },
				{ data: 'corte' },
				{ data: 'polizaMet' },
				{ data: 'ramo' },
				{ data: 'asegurado' },
				{ data: 'tipo' },
				{ data: 'vigor' },
				{ data: 'folio' },
				{ data: 'fechapago' },
				{ data: 'monto' },
				{ data: 'id_asesor' },
				{ data: 'nombrecompleto' },
				{ data: 'statusasesor' }
			],
			select: {
				style: 'single'
			},
			fixedHeader: true,
			"pageLength": 25,
			"orderMulti": true,
			"order": [[ 8, 'desc' ], [ 0, 'desc' ]],
			responsive: true,
			dom: "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-right'f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			buttons: [
				'copy', 'excel', 'pdf'
			]
		} );
	});
</script>
<script src="<?php echo DOMINIO; ?>/js/actualiza.js" type="text/javascript"></script>