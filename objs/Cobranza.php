<?php
class Cobranza extends Reportes {
	private $inicio_cobranza;
	private $hoy;
	
	public function __construct (Usuario $asesor) {
		parent::__construct($asesor);
		$this->inicio_cobranza = "2015-01-01";
		$this->hoy = date("Y-m-d");
	}
	
	public function datos_tabla_cobranza (...$filtros) {
		if ( $polizas_cobranza = $this->polizas_cobranza (...$filtros) ) {
			foreach ( $polizas_cobranza as $fila => $poliza ) {
				$mes = strtotime($this->inicio_cobranza);
				$hoy = strtotime($this->hoy);
				$j = 1;
				while ( $mes <= $hoy ) { // for ($j=1; $j<=42; $j++ )
					$key = "pago_" . strftime("%b_%Y", $mes);
					$polizas_cobranza[$fila][$key] = 0;
					$mes = strtotime($this->inicio_cobranza . "+ $j month");
					$j++;
				}
				
				if( $pagos = $this->busca_pagos($poliza["poliza"]) ) {
					foreach ( $pagos as $fila_pago => $pago ) {
						$fechapago_key = "pago_" . strftime("%b_%Y", strtotime($pago["fechapago"]));
						$polizas_cobranza[$fila][$fechapago_key] += $pago["monto"];
					}
				}
			}
			return $polizas_cobranza;
		} else {
			return false;
		}
	}
}
?>