<?php
class CobranzaPolizas extends Info {
	protected $porc_crecimiento;
	protected $tolerancia; //meses de tolerancia
	protected $asesores_con_polizas;
	protected $tipo_polizas;
	
	public function __construct (Usuario $asesor) {
		parent::__construct($asesor);
		$this->porc_crecimiento = 0.05;
		$this->tolerancia = 1;
		$this->asesores_con_polizas = false;
		$this->tipo_polizas = false;
	}
	
	public function cambia_tolerancia ($tolerancia) {
		if ( preg_match("/^[0-9]+$/", $tolerancia ) ) {
			$this->tolerancia = $tolerancia;
			return true;
		} else {
			return false;
		}
	}
	
	public function asesores_con_polizas () {
		if( count( $this->asesores_con_polizas ) ) {
			return $this->asesores_con_polizas;
		} else {
			return false;
		}
	}
	public function tipo_polizas () {
		if( count( $this->tipo_polizas ) ) {
			return $this->tipo_polizas;
		} else {
			return false;
		}
	}
	
	public function polizas_problema ($ramo) {
		/*Función para detectar pólizas con problemas de cobranza de acuerdo al coeficiente de pago, último pago registrado y la fecha de vigor del último recibo*/
		if( !$polizas_activas = $this->polizas_activas($ramo) ) {
			return false;
		}
		
		$polizas_problema = array();
		$asesores_con_polizas = array();
		$tipo_polizas = array();
		$hoy = new DateTime("now");
		$porc_crecimiento = $this->porc_crecimiento;
		$tolerancia = $this->tolerancia;
		$anio_hoy = $hoy->format("Y");
		foreach ( $polizas_activas as $fila => $datos ) {
			
			$prima_neta_total = 0;
			$fecha_inicio = new DateTime($datos["fecha_inicio"]);
			$diferencia = $fecha_inicio->diff($hoy);
			$anios = $diferencia->format("%y");
			$meses = $diferencia->format("%m");
			
			if ( (preg_match ("/MetaLife/i", $datos["producto"]) && !preg_match("/lares/i", $datos["moneda"])) || preg_match ("/Crecientes/i", $datos["moneda"]) ) {
				//calcular prima  neta total pesos crecientes
				$prima_neta_total = ($datos["prima_neta"] * ($anios + ($porc_crecimiento * ( ($anios-1) * $anios ) / 2) ) ) + ($datos["prima_neta"] * (1 + ($porc_crecimiento * ($anios) ) ) * $meses/12);
			}elseif ( preg_match ("/lares/i", $datos["moneda"]) ){
				//calcular prima  neta total dólares
				$anio_inicio = $fecha_inicio->format("Y");
				$anios_for = $anio_inicio + $anios;
				for ( $i = $anio_inicio; $i < $anios_for; $i++) {
					$prima_neta_total += $datos["prima_neta"] * $this->valor_dolar[$i];
				}
				$anio_fin = date("Y", strtotime("today - $meses months"));
				$prima_neta_total += $datos["prima_neta"] * $this->valor_dolar[$anio_fin] * ($meses/12); //calcula año actual
			}elseif ( preg_match ("/UVAC/i", $datos["moneda"]) ){
				//calcular prima  neta total UVACs
				$anio_inicio = $fecha_inicio->format("Y");
				$anios_for = $anio_inicio + $anios;
				for ( $i = $anio_inicio; $i < $anios_for; $i++) {
					$prima_neta_total += $datos["prima_neta"] * $this->valor_uvac[$i];
				}
				$anio_fin = date("Y", strtotime("today - $meses months"));
				$prima_neta_total += $datos["prima_neta"] * $this->valor_uvac[$anio_fin] * ($meses/12); //calcula año actual
			}elseif ( preg_match ("/^Pesos[\s]?$/i", $datos["moneda"]) ){
				//calcular prima  neta total pesos normalitos =)
				$prima_neta_total = ($anios * $datos["prima_neta"]) + ($datos["prima_neta"] * $meses/12);
			}
			
			$meses_total = ($anios * 12) + $meses;
			if( $meses_total > 0 ) {
				$coeficiente_tolerancia = ($anios * 12 + $meses - $tolerancia) / $meses_total;
			} else {
				$coeficiente_tolerancia = 0;
			}
			
			if( $prima_neta_total > 0 ) {
				$coeficiente = $datos["total_pagado"] / $prima_neta_total;
			} else {
				$coeficiente = 0;
			}
			
			$polizas_activas[$fila]["prima_neta_total"] = $prima_neta_total;
			$polizas_activas[$fila]["coeficiente_tolerancia"] = $coeficiente_tolerancia;
			$polizas_activas[$fila]["coeficiente"] = $coeficiente;
			
			if ( $coeficiente < $coeficiente_tolerancia ) {
				$polizas_problema[] = $polizas_activas[$fila];
				
				if ( !isset( $asesores_con_polizas[$datos["id_asesor"]] ) ) {
					$asesores_con_polizas[$datos["id_asesor"]] = $datos["asesor"];
				}
				
				if ( !in_array($datos["tipo"], $tipo_polizas) ) {
					$tipo_polizas[] = $datos["tipo"];
				}
				
			}
			
		}
		
		$this->asesores_con_polizas = $asesores_con_polizas;
		$this->tipo_polizas = $tipo_polizas;
		
		return $polizas_problema;
	}
}

?>