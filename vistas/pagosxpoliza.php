<?php
/*
Algoritmo:
- Traer pólizas activas (al menos 1 pago, que no estén canceladas o completadas)
- Ubicar la fecha mmínima de inicio de las pólizas, para armar el grid
- Hacer una tabla con la póliza, el RFC, el nombre del contratante y los meses desde la fecha mínima hasta el día de hoy
- Armar la tabla con los datos de las pólizas, sumar los pagos del mes y contar la cantidad de pagos por mes
- Identificar las pólizas a las que les faltan pagos y ponerlas al inicio
*/
$asesor = $GLOBALS["sitio"]->usuario_activo();
$info_asesor = $asesor->info();
$cobranza = new Cobranza ($asesor);
$cobranza->fecha_inicio("2015-01-01");

if ( $datos_tabla_cobranza = $cobranza->datos_tabla_cobranza() ) {
	$cols_tabla = array();
	$aux_cols = 0;
	foreach ( $datos_tabla_cobranza as $fila => $poliza ) {
		$datos_tabla_cobranza[$fila]["total_pagado"] = money_format("%.2n", $poliza["total_pagado"]);
		$datos_tabla_cobranza[$fila]["prima_anual"] = money_format("%.2n", $poliza["prima_anual"]);
		$datos_tabla_cobranza[$fila]["suma_aseg"] = money_format("%.2n", $poliza["suma_aseg"]);
		foreach ( $poliza as $col => $valor ) {
			if ( preg_match("/^pago_[a-z]{3}_[0-9]{4}$/", $col) ) {
				$datos_tabla_cobranza[$fila][$col] = money_format("%.2n", $poliza[$col]);
			}
			if ( $aux_cols == 0 ) {
				if ( !preg_match("/^pago_[a-z]{3}_[0-9]{4}$/",$col) ) {
					$var = array("data" => $col);
					array_push($cols_tabla, $var);
				}
				if ( preg_match("/^pago_[a-z]{3}_[0-9]{4}$/",$col) ) {
					$var = array("data" => $col);
					array_push($cols_tabla, $var);
				}
			}
		}
		$aux_cols = 1;
	}
	$datos_tabla_cobranza_json = json_encode($datos_tabla_cobranza, JSON_UNESCAPED_UNICODE);
	$cols_tabla_json = json_encode($cols_tabla, JSON_UNESCAPED_UNICODE);
}

?>
<link href="<?php echo DOMINIO; ?>libs/datatables/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo DOMINIO;?>libs/datatables/datatables.min.js"></script>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2">Resumen de Pagos por Mes de Pólizas MetLife</h1>
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
		<table id="tabla_pagos" class="display table table-condensed table-hover table-striped">
			<thead>
				<tr>
					<?php 
					foreach($cols_tabla as $fila => $data) {
						echo "<th>{$data['data']}</th>";
					}
					?>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var datos = <?php echo $datos_tabla_cobranza_json; ?>;
		var columnas = <?php echo $cols_tabla_json; ?>;
		//var datos = JSON.parse(datos_json);
		//console.log(datos_json);
		var table = $("#tabla_pagos").DataTable( {
			data: datos,
			columns: columnas,
			select: {
				style: 'single'
			},
			fixedHeader: true,
			"pageLength": 25,
			"orderMulti": true,
			"order": [[ 1, 'desc' ], [ 4, 'desc' ]],
			/*responsive: {
				details: {
					type: 'inline' //column
				}
			}*/
			"scrollX": true,
			"scrollY": "500px",
			fixedColumns: {
				leftColumns: 5
			},
			dom: "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-right'f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			buttons: [
				'copy', 'excel', 'pdf'
			]
		} );
	});
</script>