<?php
class Actividad extends Reportes {
	
	public function __construct (Usuario $asesor) {
		parent::__construct($asesor);
		$this->fecha_inicio = date("Y-m-d", strtotime("last saturday"));
		$this->fecha_fin = date("Y-m-d", strtotime("{$this->fecha_inicio} +6 day"));
	}
	
	public function forma_sel_asesores () {
		$dominio = DOMINIO . "actividad";
		$select_html = "
			<form id='forma_sel_asesores' class='form-inline' action='$dominio' method='post'>
				<select class='custom-select w-75' name='id_asesor_sel'>
					<option selected>Selecciona un asesor...</option>
		";
		if ( $asesores = $this->asesores_activos ) {
			foreach ( $asesores as $id => $datos_asesor ) {
				$select_html .= "<option value='$id'>{$datos_asesor['nombre_completo']}</option>";
			}
		}
		
		$select_html .= "
				</select>
		";
		if ( isset($_POST["sel_fecha"]) ){
			$select_html .= "
				<input type='text' readonly class='form-control-plaintext d-none' name='sel_fecha' value='{$_POST["sel_fecha"]}'>
			";
		}
		$select_html .= "
			<button type='submit' class='btn btn-primary btn-sm ml-3'><i class='fas fa-paper-plane'></i></button>
			</form>
		";
		return $select_html;
	}
	
	public function forma_registro () {
		if ( $actividades_seguimiento = $this->actividades_seguimiento() ) {
			$inputs_actividad = "";
			foreach ( $actividades_seguimiento as $fila => $datos ) {
				if ( $datos['id'] <= 7 || $datos['id'] >= 10 ) {
					$inputs_actividad .= "
						<div class='form-group row my-0'>
							<label for='actividad_{$datos['id']}' class='col-sm-9 col-form-label text-left'>{$datos['actividad']} <small>pts:{$datos['puntos']}</small></label>
							<div class='col-sm-3'>
								<input type='number' class='form-control form-control-sm' placeholder='0' name='actividad_{$datos['id']}' id='actividad_{$datos['id']}' data-puntos='{$datos['puntos']}'>
							</div>
						</div>
					";
				} else {
					$inputs_actividad .= "
						<div class='form-group row my-0'>
							<label for='actividad_{$datos['id']}' class='col-sm-9 col-form-label text-left'>{$datos['actividad']} <small>pts:{$datos['puntos']}</small></label>
							<div class='col-sm-3'>
								<input type='text' readonly class='form-control-plaintext form-inline' name='actividad_{$datos['id']}' id='actividad_{$datos['id']}' data-puntos='{$datos['puntos']}' value='0'>
							</div>
						</div>
					";
				}
			}
			return $inputs_actividad;
		} else {
			return false;
		}
	}
	
	function guarda_actividad () {
		$actividades_seguimiento = $this->actividades_seguimiento();
		if( !$actividades_seguimiento || !isset($_POST) ) {
			return false;
		}
		$fecha_datos = preg_replace("/[^0-9\-]/", "", $_POST["res_input_fecha"]);
		if ( !preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $fecha_datos) ) {
			return false;
		}
		
		// === OjO === Revisar que no exista un registro en la base de datos.
		$txt_qry_revisa = "SELECT id FROM cohenag_actividad_asesor WHERE fecha = '$fecha_datos' AND id_asesor = {$this->asesor->info()["id_bds"]}";
		
		$txt_qry = "
			INSERT INTO cohenag_actividad_asesor (id_asesor, fecha, referidos, citas_nuevas, citas_iniciales, citas_cierre, citas_poliza, citas_ci, solicitud_firmada, poliza_pagada, poliza_anual, citas_acompanamiento, cierre_acompanamiento) 
			VALUES (:id_asesor, :fecha, :referidos, :citas_nuevas, :citas_iniciales, :citas_cierre, :citas_poliza, :citas_ci, :solicitud_firmada, :poliza_pagada, :poliza_anual, :citas_acompanamiento, :cierre_acompanamiento)
		";
		
		$valores = array (
			":id_asesor" => $this->asesor->info()["id_bds"],
			":fecha" => $fecha_datos,
			":referidos" => preg_replace ("/[^0-9]/", "", $_POST["actividad_5"]),
			":citas_nuevas" => preg_replace ("/[^0-9]/", "", $_POST["actividad_6"]),
			":citas_iniciales" => preg_replace ("/[^0-9]/", "", $_POST["actividad_1"]),
			":citas_cierre" => preg_replace ("/[^0-9]/", "", $_POST["actividad_2"]),
			":citas_poliza" => preg_replace ("/[^0-9]/", "", $_POST["actividad_3"]),
			":citas_ci" => preg_replace ("/[^0-9]/", "", $_POST["actividad_4"]),
			":solicitud_firmada" => preg_replace ("/[^0-9]/", "", $_POST["actividad_7"]),
			":poliza_pagada" => preg_replace ("/[^0-9]/", "", $_POST["actividad_8"]),
			":poliza_anual" => preg_replace ("/[^0-9]/", "", $_POST["actividad_9"]),
			":citas_acompanamiento" => preg_replace ("/[^0-9]/", "", $_POST["actividad_10"]),
			":cierre_acompanamiento" => preg_replace ("/[^0-9]/", "", $_POST["actividad_11"])
		);
		
		$obj_con = Sitio::$obj_bds;
		
		if( $qry_revisa = $obj_con->query($txt_qry_revisa) ) {
			$res_revisa = $qry_revisa->fetch(PDO::FETCH_ASSOC);
			if ( $res_revisa["id"] > 0 ) {
				$txt_respuesta = "
					<div class='alert alert-warning alert-dismissible fade show' role='alert'>
						El día {$this->fecha_formato($fecha_datos)} ya tiene un registro, favor de contactar al administrador.
						<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
						<span aria-hidden='true'>&times;</span>
						</button>
					</div>
				";
				echo $txt_respuesta;
				return false;
			}
		}
		
		if ( $qry = $obj_con->prepare($txt_qry) ) {
			if ( $res = $qry->execute($valores) ) {
				$txt_respuesta = "
					<div class='alert alert-success alert-dismissible fade show' role='alert'>
						Se guardó correctamente tu actividad del día {$this->fecha_formato($fecha_datos)}.
						<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
						<span aria-hidden='true'>&times;</span>
						</button>
					</div>
				";
				echo $txt_respuesta;
				return true;
			}
		} else {
			$txt_respuesta = "
				<div class='alert alert-danger alert-dismissible fade show' role='alert'>
					Hubo un error al guardar tu actividad del día {$this->fecha_formato($fecha_datos)}, favor de intentar de nuevo o contactar al administrador.
					<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
					<span aria-hidden='true'>&times;</span>
					</button>
				</div>
			";
			echo $txt_respuesta;
			return false;
		}
	}
	
	public function resumen_semanal ($asesor) {
		$datos_acts = $this->actividades_asesor($asesor);
		$acts = $this->actividades_seguimiento();
		$lunes = date("Y-m-d", strtotime("{$this->fecha_inicio} +2 day"));
		$html_acts = "";
		if ( $datos_acts && $acts ) {
			$actividades = array();
			foreach ( $acts as $fila => $datos ) {
				$actividades[$datos["nom_col"]] = array (
					"actividad" => $datos["actividad"],
					"puntos" => $datos["puntos"]
				);
				$bloques_resumen_acts[$datos["nom_col"]] = array();
				for ( $d = 0; $d < 5; $d++ ) {
					$fd = date("Y-m-d", strtotime("$lunes +$d day"));
					$bloques_resumen_acts[$datos["nom_col"]][$fd] = 0;
				}
			}
			foreach ( $datos_acts as $fila2 => $datos2 ) {
				foreach ( $datos2 as $nombre_col => $val ) {
					if ( preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $val) ) {
						$fecha = $val;
					} elseif ( $nombre_col != "asesor" ) {
						if (  isset( $bloques_resumen_acts[$nombre_col][$fecha] ) ) {
							$bloques_resumen_acts[$nombre_col][$fecha] += $val;
						} elseif( isset( $bloques_resumen_acts[$nombre_col] ) ) {
							$bloques_resumen_acts[$nombre_col][$lunes] += $val;
						}
					} else {
						
					}
				}
			}
			$html_acts = "
				<div class='row border-bottom'>
					<div class='col-3'><b>Actividad</b></div>
					<div class='col-1 border-right'><b>Pts</b></div>
					<div class='col-1'><b>Lun</b></div>
					<div class='col-1'><b>Mar</b></div>
					<div class='col-1'><b>Mie</b></div>
					<div class='col-1'><b>Jue</b></div>
					<div class='col-1'><b>Vie</b></div>
					<div class='col-1 border-left'><b>Total</b></div>
					<div class='col-2'><b>Puntos</b></div>
				</div>
			";
			$puntos_semana = array("total"=>0);
			foreach( $bloques_resumen_acts as $activ => $vals ) {
				if ( $activ != "asesor" ) {
					$html_acts .= "<div class='row'>";
					$html_acts .= "<div class='col-3 nombre_act'><p class='my-0'>{$actividades[$activ]['actividad']}</p></div>";
					$html_acts .= "<div class='col-1 border-right'><p class='my-0'>{$actividades[$activ]['puntos']}</p></div>";
					$total = 0;
					foreach ( $vals as $fecha => $cant ) {
						$html_acts .= "	
							<div class='col-1 puntos_act'><p class='my-0'>$cant</p></div>
						";
						$total += $cant;
						$puntos_semana[$fecha] += $cant * $actividades[$activ]['puntos'];
					}
					$puntos_act = $total * $actividades[$activ]['puntos'];
					$puntos_semana["total"] += $puntos_act;
					
					$html_acts .= "<div class='col-1 border-left'><p class='my-0'>$total</p></div>";
					$html_acts .= "<div class='col-2'><p class='my-0'>$puntos_act</p></div>";
					$html_acts .= "</div>";
				}
			}
			$html_acts .= "<div class='row border-top'>";
			$html_acts .= "<div class='col-4 border-right'><b>Totales</b></div>";
			$total_acts = 0;
			foreach ( $puntos_semana as $col => $totales ) {
				if ( $col != total ) {
					$html_acts .= "<div class='col-1'><b>$totales</b></div>";
					$total_acts += $totales;
				}
			}
			$html_acts .= "<div class='col-1 border-left'><b>$total_acts</b></div>";
			$html_acts .= "<div class='col-2'><b><span id='total_pts'>{$puntos_semana['total']}</span></b></div>";
			$html_acts .= "</div>";
			//print_r($bloques_resumen_acts);
			
			//$datos_acts_json = json_encode($datos_acts, JSON_UNESCAPED_UNICODE);
			//echo $datos_tabla_json;
			
			return $html_acts;
		} else {
			return false;
		}
	}
	
	public function tabla_resumen_actividad () {
		$asesores = $this->asesores_activos;
		unset($asesores[5]);//quitar a Erick
		unset($asesores[6]);//quitar a Jacobo
		unset($asesores[8]);//quitar a Brisa
		unset($asesores[22]);//quitar a Alfredo
		ksort($asesores);
		$resumen_actividad = $this->resumen_actividad();
		$actividades = $this->actividades_seguimiento ();
		$tabla_resumen_actividad = "
			<table class='table table-bordered table-hover table-sm table-responsive-md'>
				<caption>Resumen semanal de actividad</caption>
				<tr>
					<th>Asesor</th>
		";
		
		foreach ( $actividades as $fila => $datos_actividades ) {
			$tabla_resumen_actividad .= "<th>{$datos_actividades['actividad']}</th>";
		}
		
		$tabla_resumen_actividad .= "
				<th>Puntos</th>
				<th>Dif.</th>
			</tr>
		";
		
		foreach ( $asesores as $id => $datos_asesores ) {
			$puntos = 0;
			$tabla_resumen_actividad .= "
					<tr>
						<td>{$datos_asesores['nombre']}</td>
				";
			if ( isset($resumen_actividad[$id]) ) {
				foreach ( $actividades as $fila => $datos_actividades ) {
					$puntos += $datos_actividades['puntos'] * $resumen_actividad[$id][$datos_actividades['nom_col']];
					$tabla_resumen_actividad .= "
						<td>{$resumen_actividad[$id][$datos_actividades['nom_col']]}</td>
					";
				}
				
				//$tabla_resumen_actividad = preg_replace("/0{1}/", "", $tabla_resumen_actividad);
				
				$diferencia = 100 - $puntos;
				switch ( $puntos ) {
					case $puntos == 0 :
						$clase = "bg-danger text-light";
						break;
					case $puntos < 60 :
						$clase = "bg-danger text-light";
						break;
					case $puntos < 80 :
						$clase = "bg-warning";
						break;
					case $puntos < 100 :
						$clase = "bg-success text-light";
						break;
					case $puntos > 100 :
						$clase = "bg-primary tu_jefa text-light";
						break;
					default :
						$clase = "bg-danger text-light";
						break;
				}
				$tabla_resumen_actividad .= "
						<td class='$clase'>$puntos</td>
						<td class='$clase'>$diferencia</td>
					</tr>
				";
			} else {
				foreach ( $actividades as $fila => $datos_actividades ) {
					$puntos += $datos_actividades['puntos'] * $resumen_actividad[$id][$datos_actividades['nom_col']];
					$tabla_resumen_actividad .= "<td>0</td>";
				}
				$tabla_resumen_actividad .= "
						<td class='bg-danger text-light'>0</td>
						<td class='bg-danger text-light'>0</td>
					</tr>
				";
			}
		}
		$tabla_resumen_actividad .= "</table>";
		
		return $tabla_resumen_actividad;
	}
}
?>