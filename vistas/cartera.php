<?php
$asesor = $GLOBALS["sitio"]->usuario_activo();
$reportes = new Reportes ($asesor);
$reportes->fecha_inicio("2016-01-01");
if ( $polizas_activas_todas = $reportes->polizas_activas_todas() ) {
	//print_r ($pagos);
	foreach ( $polizas_activas_todas as $fila => $poliza ) {
		$polizas_activas_todas[$fila]["total_pagado"] = money_format("%.2n", $poliza["total_pagado"]);
		$polizas_activas_todas[$fila]["prima_anual"] = money_format("%.2n", $poliza["prima_anual"]);
	}
	$polizas_activas_todas_json = json_encode($polizas_activas_todas, JSON_UNESCAPED_UNICODE);
}
?>
<link href="<?php echo DOMINIO; ?>libs/datatables/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo DOMINIO;?>libs/datatables/datatables.min.js"></script>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2">Pólizas Activas MetLife</h1>
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
	<div class="table-responsive">
		<table id="tabla_polizas" class="display table table-condensed table-hover table-striped">
			<thead>
				<tr>
					<th>Póliza</th>
					<th>Ramo</th>
					<th>Producto</th>
					<th>Contratante</th>
					<th>Estatus</th>
					<th>Prima Anual</th>
					<th>Fecha 1er pago</th>
					<th>Total Pagado</th>
					<th>Asesor</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var datos = <?php echo $polizas_activas_todas_json; ?>;
		//var datos = JSON.parse(datos_json);
		//console.log(datos_json);
		var table = $("#tabla_polizas").DataTable( {
			data: datos,
			columns: [
				{ data: 'poliza' },
				{ data: 'ramo' },
				{ data: 'producto' },
				{ data: 'asegurado' },
				{ data: 'estatus' },
				{ data: 'prima_anual' },
				{ data: 'fecha_anualiza' },
				{ data: 'total_pagado' },
				{ data: 'nombre' }
			],
			select: {
				style: 'single'
			},
			fixedHeader: true,
			"pageLength": 25,
			"orderMulti": true,
			"order": [[ 1, 'desc' ], [ 4, 'desc' ]],
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