<?php 
$asesor = $GLOBALS["sitio"]->usuario_activo();
$cobranza = new CobranzaPolizas ($asesor);

if ( $polizas_problema = $cobranza->polizas_problema("todo") ) {
	//print_r($polizas_problema);
	
	$asesores_con_polizas = $cobranza->asesores_con_polizas();
	$tipo_polizas = $cobranza->tipo_polizas();
	
	foreach( $polizas_problema as $fila => $datos ) {
		$polizas_problema[$fila]["prima_anual"] = money_format("%.2n", $datos["prima_anual"]);
		$polizas_problema[$fila]["prima_neta"] = money_format("%.2n", $datos["prima_neta"]);
		$polizas_problema[$fila]["total_pagado"] = money_format("%.2n", $datos["total_pagado"]);
	}
	$polizas_problema_json = json_encode($polizas_problema, JSON_UNESCAPED_UNICODE);
	$tabla_th = array_keys($polizas_problema[0]);
}
?>
<link href="<?php echo DOMINIO; ?>libs/datatables/datatables.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo DOMINIO;?>libs/datatables/datatables.min.js"></script>
<style type="text/css">
	div#select_asesores {
		width: 35%;
	}
</style>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
	<h1 class="h2">Pólizas con posibles problemas de cobranza</h1>
	<div class="btn-toolbar mb-2 mr-2">
		<!--<div class="btn-group mr-2">
			<button class="btn btn-sm btn-outline-secondary">Share</button>
			<button class="btn btn-sm btn-outline-secondary">Export</button>
		</div>
		<button class="btn btn-sm btn-outline-secondary dropdown-toggle">
			<i class="fa fa-calendar"></i> This week
		</button>-->
		<div class="btn-group" role="group">
			<button id="drop_tipo_poliza" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="far fa-file-alt mr-1"></i> Tipo
			</button>
			<div class="dropdown-menu" aria-labelledby="drop_tipo_poliza">
				<a class="dropdown-item tipo_polizas" href="#">Todos</a>
				<?php 
					if ( $tipo_polizas ) {
						foreach ( $tipo_polizas as $tipo ) {
							echo "<a class='dropdown-item tipo_polizas' href='#'>$tipo</a>";
						}
					}
				?>
			</div>
		</div>
	</div>
	<div id="select_asesores" class="input-group mb-2">
		<div class="input-group-prepend">
			<label class="input-group-text" for="asesores_con_polizas"><i class="fa fa-user mr-1"></i> Asesores</label>
		</div>
		<select class="custom-select" id="asesores_con_polizas">
			<option value="0" selected>Seleccionar...</option>
			<?php 
				if ( $asesores_con_polizas ) {
					foreach ( $asesores_con_polizas as $id => $asesor ) {
						echo "<option value='$id'>$asesor</option>";
					}
				}
			?>
		</select>
	</div>
</div>
<div class="col-xs-12">
	<div class="table-responsive">
		<table id="tabla_polizas_problema" class="display table table-condensed table-hover table-striped">
			<thead>
				<tr>
				<?php 
					foreach( $tabla_th as $fila => $th ) {
						echo "<th>$th</th>"; 
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
		var datos = <?php echo $polizas_problema_json; ?>;
		//var datos = JSON.parse(datos_json);
		//console.log(datos_json);
		var table = $("#tabla_polizas_problema").DataTable( {
			data: datos,
			columns: [
				<?php 
					foreach( $tabla_th as $fila => $th ) {
						echo "{ data: '$th' }," ;
					}
				?>
			],
			/*select: {
				style: 'single'
			},*/
			fixedHeader: true,
			"pageLength": 25,
			"orderMulti": true,
			"order": [[ 13, 'asc' ], [ 2, 'desc' ]],
			responsive: false,
			"scrollX": true,
			"scrollY": "600px",
			fixedColumns: {
				leftColumns: 4
			},
			dom: "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-right'f>>" +
			"<'row'<'col-sm-12'tr>>" +
			"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
			buttons: [
				'copy', 'excel', 'pdf'
			]
		} );
		
		$("#asesores_con_polizas").on("change", function() {
			var id = this.value;
			//alert(id);
			if ( id > 0 ) {
				table.columns( 22 ).search( "^" + id + "$", true, false ).draw();
			} else {
				table.columns( 22 ).search( "[0-9]*", true, false ).draw();
			}
		});
		$(".tipo_polizas").on("click", function() {
			var tipo = $(this).text();
			//alert(tipo);
			$("#drop_tipo_poliza").text(tipo);
			if ( tipo != "Todos" ) {
				table.columns( 1 ).search( "^" + tipo + "$", true, false ).draw();
			} else {
				table.columns( 1 ).search( "[A-Za-z]*", true, false ).draw();
			}
		});
		
	});
</script>