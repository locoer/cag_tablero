<?php
function __autoload($class) {
	include "./objs/$class.php";
}
include_once "./config.php";

$sesion = new Sesion(NOMBRE_SITIO);
$sesion->inicio_seguro();
if ( $datos_ajax = $_POST || $datos_ajax = $_FILES ) {
	if (isset($_FILES['arch_act_pp'])) {
		
		$actualiza = new Actualiza();
		$archivo = $_FILES['arch_act_pp'];
		unset($_SESSION["datos_registrar_bds"]);
		
		if ( $datos_actualizar = $actualiza->valida_archivo($archivo) ) {
			//var_dump($datos_actualizar);
			if ( $datos_registrar_bds = $actualiza->datos_a_registrar($datos_actualizar) ) {
					$datos_registrar_bds_json = json_encode($datos_registrar_bds);
					if(isset($_SESSION)){
						$_SESSION["datos_registrar_bds"] = $datos_registrar_bds_json;
						?>
						<h3>Datos a actualizar en la Base de Datos</h3>
						<div class="table-responsive">
							<table id="tabla_datos_actualizar" class="display table table-condensed table-hover table-striped">
								<thead>
									<tr>
										<th>Agregar</th>
										<th>Corte</th>
										<th>Ramo</th>
										<th>Asegurado</th>
										<th>Póliza</th>
										<th>Tipo Comisión</th>
										<th>Vigor</th>
										<th>Folio</th>
										<th>Fecha Pago</th>
										<th>PCom</th>
										<th>Prima</th>
										<th>Comision</th>
									</tr>
								</thead>
								<tbody>
								<?php
									foreach( $datos_registrar_bds as $corte => $registros ) {
										foreach ( $registros as $fila => $datos ) {
											echo "
												<tr>
													<td><input type='checkbox' name='agregar_registro' value='$corte-$fila' checked='checked'></td>
													<td>{$datos['Corte']}</td>
													<td>{$datos['Ramo']}</td>
													<td>{$datos['Asegurado']}</td>
													<td>{$datos['Poliza']}</td>
													<td>{$datos['Tipo_de_Comision']}</td>
													<td>{$datos['Inicio_Vigor']}</td>
													<td>{$datos['Folio_Recibido']}</td>
													<td>{$datos['Dia_de_Pago']}</td>
													<td>{$datos['Porc_Comision']}</td>
													<td>{$datos['Prima_Neta']}</td>
													<td>{$datos['Comision']}</td>
												</tr>
											";
										}
									}
								?>
								</tbody>
							</table>
							<button type="button" id="actualizar_datos" class="btn btn-success">Actualizar Datos</button>
						</div>
						<script type="text/javascript">
							$("#actualizar_datos").on("click", function() {
								var datos_quitar = {};
								var fila = 0;
								var datos_registro;
								$("input[name=agregar_registro]:checkbox:not(:checked)").each( function(){
									datos_registro = $(this).val().split("-");
									datos_quitar[fila] = {"Corte" : datos_registro[0], "Fila" : datos_registro[1]};
									fila++;
								});
								datos_quitar_json = JSON.stringify(datos_quitar);
								console.log(datos_quitar_json);
								//var total_datos = datos_quitar.length;
								//alert("Enviar a BDs y quitar registros: " + datos_quitar_json);
								$.ajax({
								  url: "https://cohenag.com/tablerov2/ajax.php",
								  data: {"datos_quitar" : datos_quitar_json},
								  type: 'POST',
								  success: function( respuesta ) {
									//alert(respuesta);
									$("#respuesta").html(respuesta);
								  }
								}).fail(function( jqXHR, textStatus ) {
								  alert( "Falló el Ajax: " + textStatus );
								});
							});
						
						</script>
						<?php
					}
			} else {
				echo "<h3>Algo falló o no hubo registros nuevooos</h3>";
			}
		} else {
			return false;
		}
	}
	if ( isset($_POST["datos_quitar"]) && isset($_SESSION["datos_registrar_bds"]) ){
		$actualiza = new Actualiza();
		
		$datos_registrar_bds_json = $_SESSION["datos_registrar_bds"];
		$datos_quitar_json = $_POST["datos_quitar"];
		
		$datos_registrar_bds = json_decode($datos_registrar_bds_json, true);
		$datos_quitar = json_decode($datos_quitar_json, true);
		
		if ( count($datos_quitar) > 0 ) {
			foreach($datos_quitar as $datos ) {
				unset( $datos_registrar_bds[$datos["Corte"]][$datos["Fila"]] );
			}
			foreach( $datos_registrar_bds as $corte => $datos ) {
				if ( count($datos_registrar_bds[$corte]) == 0 ) {
					unset($datos_registrar_bds[$corte]);
				}
			}
		}
		if ( count($datos_registrar_bds) > 0 ) {
			if ( $respuesta_actualizar_bds_pp = $actualiza->actualizar_bds_pp($datos_registrar_bds) ) {
				echo "<h3>Se actualizó correctamente el archivo</h3>";
				if ( isset($respuesta_actualizar_bds_pp["Sin_Registro"]) && count($respuesta_actualizar_bds_pp["Sin_Registro"]) > 0 ) {
					?>
					<h3>Pólizas que faltan en el CRM</h3>
					<div class="table-responsive">
						<table id="tabla_datos_actualizar" class="display table table-condensed table-hover table-striped">
							<thead>
								<tr>
									<th>Ramo</th>
									<th>Asegurado</th>
									<th>Póliza</th>
									<th>Fecha Pago</th>
								</tr>
							</thead>
							<tbody>
					<?php
					foreach ( $respuesta_actualizar_bds_pp["Sin_Registro"] as $fila => $datos ) {
						echo "
							<tr>
								<td>{$datos['Ramo']}</td>
								<td>{$datos['Asegurado']}</td>
								<td>{$datos['Poliza']}</td>
								<td>{$datos['Dia_de_Pago']}</td>
							</tr>
						";
					}
					?>
							</tbody>
						</table>
					</div>
					<?php
				}
				if ( isset($respuesta_actualizar_bds_pp["Error"]) && count($respuesta_actualizar_bds_pp["Error"]) > 0 ) {
					?>
					<h3>Pólizas que reportaron error</h3>
					<div class="table-responsive">
						<table id="tabla_datos_actualizar" class="display table table-condensed table-hover table-striped">
							<thead>
								<tr>
									<th>Ramo</th>
									<th>Asegurado</th>
									<th>Póliza</th>
									<th>Fecha Pago</th>
								</tr>
							</thead>
							<tbody>
					<?php
					foreach ( $respuesta_actualizar_bds_pp["Error"] as $fila => $datos ) {
						echo "
							<tr>
								<td>{$datos['Ramo']}</td>
								<td>{$datos['Asegurado']}</td>
								<td>{$datos['Poliza']}</td>
								<td>{$datos['Dia_de_Pago']}</td>
							</tr>
						";
					}
					?>
							</tbody>
						</table>
					</div>
					<?php
				}
				//print_r($respuesta_actualizar_bds_pp);
			} else {
				echo "<h3>Ocurrió un error al actualizar la Base de Datos</h3>";
			}
		}
		
		unset($_SESSION["datos_registrar_bds"]);
	}
}
?>