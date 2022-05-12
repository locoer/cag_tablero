<?php
class Actualiza {
	protected $archivo;
	protected $ult_corte;
	protected $ult_fecha_pago;
	protected $registros;
	protected $registros_mes;
	protected $obj_con;
	
	public function __construct () {
		$this->obj_con = Sitio::conecta_bds();
		if ( $resumen_bds_pp = $this->trae_info() ) {
			$this->ult_corte = $resumen_bds_pp["ult_corte"];
			$this->ult_fecha_pago = $resumen_bds_pp["ult_fecha_pago"];
			$this->registros = $resumen_bds_pp["registros"];
			$this->registros_mes = $resumen_bds_pp["registros_mes"];
		} else {
			$this->ult_corte = false;
			$this->ult_fecha_pago = false;
			$this->registros = false;
			$this->registros_mes = false;
		}
	}
	
	public function __destruct () {
		// clean up here
	}
	
	public function ult_corte () {
		return $this->ult_corte;
	}
	public function ult_fecha_pago () {
		$ult_fecha_pago = date("d/m/Y", strtotime($this->ult_fecha_pago) );
		return $ult_fecha_pago;
	}
	public function registros () {
		return $this->registros;
	}
	public function registros_mes () {
		return $this->registros_mes;
	}
	
	protected function trae_info () {
		$datos_tabla_pp = array();
		$fecha_inicio = date("Y-m-d", strtotime("first day of this month"));
		$fecha_fin = date("Y-m-d");
		
		$txt_qry_todos = "
			SELECT COUNT(pagos.ID) AS registros, MAX(pagos.Dia_de_Pago) AS maxfechapago
			FROM finova_prima_pagada AS pagos 
		";
		$txt_qry_mes = "
			SELECT COUNT(pagos.ID) AS registros, MAX(pagos.Corte) AS ultimocorte 
			FROM finova_prima_pagada AS pagos 
			WHERE pagos.Dia_de_Pago BETWEEN :fecha_inicio AND :fecha_fin
		";
		$vals_qry_mes = array (
			":fecha_inicio" => $fecha_inicio,
			":fecha_fin" => $fecha_fin
		);
		
		$obj_con = $this->obj_con;
		
		if ( $qry1 = $obj_con->query($txt_qry_todos) ) {
			if ( $qry1->rowCount() > 0 ) {
				$datos_qry1 = $qry1->fetch(PDO::FETCH_ASSOC);
				$datos_tabla_pp["registros"] = $datos_qry1["registros"];
				$datos_tabla_pp["ult_fecha_pago"] = $datos_qry1["maxfechapago"];
			}
		}
		$qry2 = $obj_con->prepare($txt_qry_mes);
		if ( $qry2->execute($vals_qry_mes) && $qry2->rowCount() > 0 ){
			$datos_qry2 = $qry2->fetch(PDO::FETCH_ASSOC);
			$datos_tabla_pp["registros_mes"] = $datos_qry2["registros"];
			$datos_tabla_pp["ult_corte"] = $datos_qry2["ultimocorte"];
		} 
		
		if ( count($datos_tabla_pp) > 0 ) {
			return $datos_tabla_pp;
		} else {
			return false;
		}
	}
	public function trae_datos_todos () {
		
		$txt_qry = "
			SELECT pagos.ID AS idpagos, pagos.Corte AS corte, pagos.Ramo AS ramo, pagos.Asegurado AS asegurado, pagos.Poliza AS polizaMet, pagos.Tipo_de_Comision AS tipo, pagos.Inicio_Vigor AS vigor, pagos.Folio_Recibido AS folio, pagos.Dia_de_Pago AS fechapago, pagos.Prima_Neta AS monto, pagos.id_asesor AS id_asesor,
			CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombrecompleto, asesores.status AS statusasesor
			FROM finova_prima_pagada AS pagos 
			LEFT JOIN vtiger_users AS asesores ON asesores.id = pagos.id_asesor
			ORDER BY pagos.Dia_de_Pago DESC, pagos.Poliza DESC
		";
		$obj_con = $this->obj_con;
		
		if ( $qry = $obj_con->query($txt_qry) ) {
			if ( $qry->rowCount() > 0 ) {
				$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
				return $datos;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	public function valida_archivo ($archivo) {
		if ( !isset($archivo["type"]) ) {
			return false;
		}
		$nombre_separado = explode(".", $archivo["name"]);
		$extension = end($nombre_separado);
		
		if ($archivo["error"] > 0 
			|| $archivo["type"] != "application/vnd.ms-excel" 
			|| $extension != "csv" 
			|| $archivo["size"] > 3072000
		) {
			echo "Favor de subir un archivo de tipo CSV menor a 3 Mb y no del tipo: $extension";
			return false;
		}
		$tmpName = $archivo['tmp_name'];
		$arch_csv = array_map('str_getcsv', file($tmpName));
		$agente = $arch_csv[1][0];
		
		if ( $agente != CLAVE_AGENTE ) {
			echo "El archivo ingresado no corresponde a la promotoría o es de otro agente";
			return false;
		}
		
		$periodo = $arch_csv[1][2];
		$fechas = explode('-', $periodo);
		$fecha_inicio = trim($fechas[0]);
		$fecha_fin = trim($fechas[1]);
		
		$mes_nombre = array (
			"Enero" => "January",
			"Febrero" => "February",
			"Marzo" => "March",
			"Abril" => "April",
			"Mayo" => "May",
			"Junio" => "June",
			"Julio" => "July",
			"Agosto" => "August",
			"Septiembre" => "September",
			"Octubre" => "October",
			"Noviembre" => "November",
			"Diciembre" => "December"
		);
		
		$datos_actualizar = array ();
		
		foreach ( $mes_nombre as $mes => $mes_eng ) {
			$regex = "/\s$mes\s/";
			if ( preg_match($regex, $fecha_inicio) ) {
				$fecha_inicio = preg_replace($regex, " $mes_eng ", $fecha_inicio);
				$datos_actualizar["fecha_inicio"] = date("Y-m-d", strtotime($fecha_inicio)); 
				$anio_mes_archivo = date("Y-m-", strtotime($fecha_inicio)); 
			}
			if ( preg_match($regex, $fecha_fin) ) {
				$fecha_fin = preg_replace($regex, " $mes_eng ", $fecha_fin);
				$datos_actualizar["fecha_fin"] = date("Y-m-d", strtotime($fecha_fin));
			}
		}
		
		$fila_inicio_datos = false;
		foreach ( $arch_csv as $fila => $cols ) {
			if ( $fila_inicio_datos && !is_null($cols[0])) {
				$Prima_Neta = preg_replace("/[^0-9.]/", "", $cols[9]);
				$Comision = preg_replace("/[^0-9.-]/", "", $cols[10]);
				if ( $Comision < 0 ) {
					$Prima_Neta = (-1) * $Prima_Neta;
				}
				$datos_actualizar[$cols[0]][] = array(
					"Corte" => $cols[0], 
					"Ramo" => trim($cols[1]), 
					"Asegurado" => trim($cols[2]), 
					"Poliza" => $cols[3], 
					"Tipo_de_Comision" => trim($cols[4]), 
					"Inicio_Vigor" => date("Y-m-d", strtotime($cols[5])), 
					"Folio_Recibido" => $cols[6], 
					"Dia_de_Pago" => date("Y-m-d",strtotime($anio_mes_archivo.$cols[7])), 
					"Porc_Comision" => $cols[8], 
					"Prima_Neta" => $Prima_Neta, 
					"Comision" => $Comision
				);
			}
			if ( 
				$cols[0] == 'Corte' &&
				$cols[1] == 'Ramo' &&
				$cols[2] == 'Asegurado' &&
				$cols[3] == 'Poliza' &&
				$cols[4] == 'Tipo de Comision' &&
				$cols[5] == 'Inicio Vigor' &&
				$cols[6] == 'Folio Recibido' &&
				$cols[7] == 'Dia' &&
				$cols[8] == '% Comision' &&
				$cols[9] == 'Prima Neta' &&
				$cols[10] == 'Comision' 
			) {
				$fila_inicio_datos = true;
			}
		}
		
		return $datos_actualizar;
	}
	
	public function datos_a_registrar ($datos_actualizar) {
		$obj_con = $this->obj_con;
		$txt_qry_cortes = "
			SELECT COUNT(pagos.ID) 
			FROM finova_prima_pagada AS pagos 
			WHERE Dia_de_Pago BETWEEN :fecha_inicio AND :fecha_fin
			AND Corte = :corte
		";
		$txt_qry_registros = "
			SELECT Corte, Ramo, Asegurado, Poliza, Tipo_de_Comision, Inicio_Vigor, Folio_Recibido, Dia_de_Pago, Porc_Comision, Prima_Neta, Comision
			FROM finova_prima_pagada 
			WHERE Dia_de_Pago BETWEEN :fecha_inicio AND :fecha_fin
			AND Corte = :corte
		";
		if ( array_key_exists("fecha_inicio", $datos_actualizar) && array_key_exists("fecha_fin", $datos_actualizar) ) {
			if ( 
				preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $datos_actualizar["fecha_inicio"]) &&
				preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $datos_actualizar["fecha_fin"])
			) {
				$fecha_inicio = $datos_actualizar["fecha_inicio"];
				$fecha_fin = $datos_actualizar["fecha_fin"];
				unset($datos_actualizar["fecha_inicio"]);
				unset($datos_actualizar["fecha_fin"]);
				$vals_qry_cortes = array (
					":fecha_inicio" => $fecha_inicio,
					":fecha_fin" => $fecha_fin
				);
			}
		} else {
			return false;
		}
		foreach( $datos_actualizar as $corte => $registros ) {
			//echo "Voy a revisar el corte: $corte <br/>";
			$vals_qry_cortes[":corte"] = $corte;
			$qry = $obj_con->prepare($txt_qry_registros);
			if ( $qry->execute($vals_qry_cortes) && $qry->rowCount() > 0 ){
				$num_registros = count($registros);
				$num_regs_bds = $qry->rowCount();
				if ( count($registros) > $qry->rowCount() ) {
					$datos_qry = $qry->fetchAll(PDO::FETCH_ASSOC);
					foreach( $registros as $fila => $datos ) {						
						if( in_array($datos, $datos_qry) ) {
							unset( $datos_actualizar[$corte][$fila] );
							//echo "Quité el registro del corte: $corte y fila: $fila<br/>";
						}
					}
				} else {
					//echo "Voy a quitar los registros del corte: $corte<br/>";
					unset($datos_actualizar[$corte]);
				}
			} 
		}
		return $datos_actualizar;
	}
	
	public function actualizar_bds_pp ($datos_actualizar) {
		/*
		Función que actualiza lo BDs de prima pagada y después los registros del CRM de fecha de primer pago y fecha en que se anualizó la póliza.
		Respuesta:
		Arreglo $respuesta (
			$respuesta["Sin_Registro"] -> Pólizas que no tienen registro en el CRM (son los datos del archivo)
			$respuesta["Actualizados"] -> Registro guardado en la BDs de prima pagada
			$respuesta["Error"] -> Registros que por algún error no se pudieron actualizar
			$respuesta["Actualizados_CRM"] -> Array de la función atualiza_registros_crm
		)
		*/
		$respuesta = array();
		$obj_con = $this->obj_con;
		$qry_agregar = "
			INSERT INTO finova_prima_pagada (ID, Corte, Ramo, Asegurado, Poliza, Tipo_de_Comision, Inicio_Vigor, Folio_Recibido, Dia_de_Pago, Porc_Comision, Prima_Neta, Comision, IVA, ISR_Retenido, IVA_Retenido, ISRPT_Retenido, Ingreso_X_Comision, id_asesor)
			VALUES (:ID, :Corte, :Ramo, :Asegurado, :Poliza, :Tipo_de_Comision, :Inicio_Vigor, :Folio_Recibido, :Dia_de_Pago, :Porc_Comision, :Prima_Neta, :Comision, :IVA, :ISR_Retenido, :IVA_Retenido, :ISRPT_Retenido, :Ingreso_X_Comision, :id_asesor)
		";
		$qry = $obj_con->prepare($qry_agregar);
		foreach ( $datos_actualizar as $corte => $registros ) {
			foreach( $registros as $fila => $datos ) {
				if( $datos_crm = $this->busca_poliza($datos["Poliza"]) ) {
					$datos_actualizar[$corte][$fila]["id_asesor"] = $datos_crm["id_usuario"];
				} else {
					$respuesta["Sin_Registro"][] = $datos;
					$datos_actualizar[$corte][$fila]["id_asesor"] = "";
				}
				if( preg_match("/Vida[A-Za-z\s]*/", $datos["Ramo"]) ) {
					$datos_actualizar[$corte][$fila]["IVA"] = 0;
					$datos_actualizar[$corte][$fila]["IVA_Retenido"] = 0;
					$datos_actualizar[$corte][$fila]["ISRPT_Retenido"] = 0.03 * $datos_actualizar[$corte][$fila]["Comision"];
				} elseif ( preg_match("/Gastos[A-Za-z\s]*/", $datos["Ramo"]) || preg_match("/GMM[A-Za-z\s]*/", $datos["Ramo"]) ){
					$datos_actualizar[$corte][$fila]["IVA"] = 0.16 * $datos_actualizar[$corte][$fila]["Comision"];
					$datos_actualizar[$corte][$fila]["IVA_Retenido"] = (2/3) * $datos_actualizar[$corte][$fila]["IVA"];
					$datos_actualizar[$corte][$fila]["ISRPT_Retenido"] = 0;
				}
				/*if ( $datos_actualizar[$corte][$fila]["Comision"] < 0 ) {
					$datos_actualizar[$corte][$fila]["Prima_Neta"] = (-1) * $datos_actualizar[$corte][$fila]["Prima_Neta"];
				}*/
				
				$datos_actualizar[$corte][$fila]["ISR_Retenido"] = 0.1 * $datos_actualizar[$corte][$fila]["Comision"];
				
				$datos_actualizar[$corte][$fila]["Ingreso_X_Comision"] = $datos_actualizar[$corte][$fila]["Comision"] + $datos_actualizar[$corte][$fila]["IVA"] - $datos_actualizar[$corte][$fila]["ISR_Retenido"] - $datos_actualizar[$corte][$fila]["IVA_Retenido"] - $datos_actualizar[$corte][$fila]["ISRPT_Retenido"];
				
				$vals_qry = array(
					":ID" => "",
					":Corte" => $datos_actualizar[$corte][$fila]["Corte"],
					":Ramo" => $datos_actualizar[$corte][$fila]["Ramo"],
					":Asegurado" => $datos_actualizar[$corte][$fila]["Asegurado"],
					":Poliza" => $datos_actualizar[$corte][$fila]["Poliza"],
					":Tipo_de_Comision" => $datos_actualizar[$corte][$fila]["Tipo_de_Comision"],
					":Inicio_Vigor" => $datos_actualizar[$corte][$fila]["Inicio_Vigor"],
					":Folio_Recibido" => $datos_actualizar[$corte][$fila]["Folio_Recibido"],
					":Dia_de_Pago" => $datos_actualizar[$corte][$fila]["Dia_de_Pago"],
					":Porc_Comision" => $datos_actualizar[$corte][$fila]["Porc_Comision"],
					":Prima_Neta" => $datos_actualizar[$corte][$fila]["Prima_Neta"],
					":Comision" => $datos_actualizar[$corte][$fila]["Comision"],
					":IVA" => $datos_actualizar[$corte][$fila]["IVA"],
					":ISR_Retenido" => $datos_actualizar[$corte][$fila]["ISR_Retenido"],
					":IVA_Retenido" => $datos_actualizar[$corte][$fila]["IVA_Retenido"],
					":ISRPT_Retenido" => $datos_actualizar[$corte][$fila]["ISRPT_Retenido"],
					":Ingreso_X_Comision" => $datos_actualizar[$corte][$fila]["Ingreso_X_Comision"],
					":id_asesor" => $datos_actualizar[$corte][$fila]["id_asesor"]
				);
				if ( $qry->execute($vals_qry) ){
					$respuesta["Actualizados"][] = $datos;
				} else {
					$respuesta["Error"][] = $datos;
				}
			}
		}
		if ( count($respuesta["Actualizados"]) > 0 ) {
			if ( $atualiza_registros_crm = $this->atualiza_registros_crm() ) {
				$respuesta["Actualizados_CRM"] = $atualiza_registros_crm;
			}
		}
		
		if ( count($respuesta) > 0 ) {
			return $respuesta;
		} else {
			return false;
		}
	}
	
	public function atualiza_registros_crm () {
		/*
		Actualiza los registros del CRM de fecha de primer pago y fecha en que se anualizó una póliza
		Respuesta:
		Arreglo $respuesta (
			$respuesta["Actualizados"] -> Registros del CRM que se actualizaron los campos
			$respuesta["Error"] -> Registros que por algún error del query no se pudieron actualizar
			$respuesta["Sin_pagos"] -> Registros del CRM que siguen sin tener pagos (pendientes de pago)
		)
		*/
		if ( $registros_sin_pagos = $this->registros_sin_pagos() ) {
			$obj_con = $this->obj_con;
			$respuesta = array();
			foreach ( $registros_sin_pagos as $fila => $datos ) {
				if( $fecha_primer_pago = $this->busca_primer_pago($datos["num_poliza"]) ) {
					$vals_qry = array();
					if( is_null($datos["fecha_primer_pago"]) ) {
						$qry_fecha_primer_pago = "cf_941 = :fecha_primer_pago,";
						$vals_qry[":fecha_primer_pago"] = "$fecha_primer_pago";
					} else {
						$qry_fecha_primer_pago = "";
					}
					$txt_qry = "
						UPDATE vtiger_servicecontractscf 
						SET $qry_fecha_primer_pago cf_945 = :fecha_anualizo
						WHERE servicecontractsid = :servicecontractsid
					";
					$vals_qry[":fecha_anualizo"] = "$fecha_primer_pago";
					$vals_qry[":servicecontractsid"] = $datos["id_polcf"];
					$qry = $obj_con->prepare($txt_qry);
					if ( $qry->execute($vals_qry) ){
						$respuesta["Actualizados"][] = $datos;
					} else {
						$respuesta["Error"][] = $datos;
					}
				} else {
					$respuesta["Sin_pagos"][] = $datos;
				}
			}
			if( count($respuesta)>0 ){
				return $respuesta;
			} else {
				return false;
			}
		} else {
			echo "<h3>No hay pólizas sin pagos registrados en el CRM</h3>";
			return false;
		}
	}
	
	protected function busca_poliza ($poliza) {
		$poliza = trim($poliza);
		if ( preg_match("/[IMG0-9]?[0-9]{6}/", $poliza) ) {
			$txt_qry = "
				SELECT polcf.cf_841 AS num_poliza, polcf.servicecontractsid AS id_polcf, polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_855 AS moneda, polcf.cf_845 AS suma_aseg, polcf.cf_857 AS forma_pago, polcf.cf_901 AS id_seguimiento, polcf.cf_903 AS fecha_ingreso_sol, polcf.cf_905 AS fecha_entrega, polcf.cf_843 AS aseguradotxt, polcf.cf_869 AS aseguradoid, polcf.cf_941 AS fecha_primer_pago, polcf.cf_945 AS fecha_anualizo, 
				pols.servicecontractsid AS id_pols, pols.contract_status AS estatus, pols.contract_type AS tipo, pols.start_date AS fecha_inicio, 
				entity.crmid AS id_crm, entity.smownerid AS id_usuario, entity.deleted AS deleted,
				asesores.id AS id_asesor, asesores.user_name AS usuario, asesores.first_name AS nombre, asesores.last_name AS apellido, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo, asesores.title AS cargo, asesores.status AS status_asesor 
				FROM vtiger_servicecontractscf AS polcf
				JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
				JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
				JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
				WHERE entity.deleted = 0 
				AND polcf.cf_841 = :poliza 
			";
			$vals_qry = array(
				":poliza" => "$poliza"
			);
			$obj_con = $this->obj_con;
			$qry = $obj_con->prepare($txt_qry);
			if ( $qry->execute($vals_qry) ) {
				if ( $qry->rowCount() > 0 ) {
					$datos_qry = $qry->fetch(PDO::FETCH_ASSOC);
					return $datos_qry;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}
	
	protected function busca_primer_pago ($poliza) {
		$poliza = trim($poliza);
		if ( preg_match("/[IMG0-9]?[0-9]{6}/", $poliza) ) {
			$txt_qry = "
				SELECT MIN(Dia_de_Pago) AS fechapago
				FROM finova_prima_pagada 
				WHERE Poliza = :poliza
			";
			$vals_qry = array(
				":poliza" => "$poliza"
			);
			$obj_con = $this->obj_con;
			$qry = $obj_con->prepare($txt_qry);
			if ( $qry->execute($vals_qry) ) {
				if ( $qry->rowCount() > 0 ) {
					$datos_qry = $qry->fetch(PDO::FETCH_ASSOC);
					return $datos_qry["fechapago"];
				} else {
					return false;
				}
			}			
		} else {
			exit("El número de póliza $poliza no es válido");
		}
	}
	
	protected function registros_sin_pagos () {
		$obj_con = $this->obj_con;
		$txt_qry = "
			SELECT polcf.cf_841 AS num_poliza, polcf.servicecontractsid AS id_polcf, polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_855 AS moneda, polcf.cf_845 AS suma_aseg, polcf.cf_857 AS forma_pago, polcf.cf_901 AS id_seguimiento, polcf.cf_903 AS fecha_ingreso_sol, polcf.cf_905 AS fecha_entrega, polcf.cf_843 AS aseguradotxt, polcf.cf_869 AS aseguradoid, polcf.cf_941 AS fecha_primer_pago, polcf.cf_945 AS fecha_anualizo, 
			CONCAT (contactos.firstname, ' ', contactos.lastname) AS contratante_crm, contactos.contactid AS id_contacto, 
			contactoscf.cf_829 AS rfc_contratante, contactoscf.contactid AS id_contactocf, 
			CONCAT (aseguradoscf.firstname, ' ', aseguradoscf.lastname) AS aseguradoidtxt,
			pols.servicecontractsid AS id_pols, pols.contract_status AS estatus, pols.contract_type AS tipo, pols.start_date AS fecha_inicio, 
			entity.crmid AS id_crm, entity.smownerid AS id_usuario, entity.deleted AS deleted,
			asesores.id AS id_asesor, asesores.user_name AS usuario, asesores.first_name AS nombre, asesores.last_name AS apellido, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo, asesores.title AS cargo, asesores.status AS status_asesor 
			FROM vtiger_servicecontractscf AS polcf
			JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
			JOIN vtiger_contactdetails AS contactos ON contactos.contactid = pols.sc_related_to 
			JOIN vtiger_contactscf AS contactoscf ON contactoscf.contactid = contactos.contactid
			LEFT JOIN vtiger_contactdetails AS aseguradoscf ON aseguradoscf.contactid = polcf.cf_869
			JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
			JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE entity.deleted = 0
			AND polcf.cf_853 NOT LIKE '%Otros%'
			AND polcf.cf_853 NOT LIKE '%Responsabilidad%'
			AND pols.subject NOT LIKE '%New York Life%'
			AND pols.contract_status <> 'Complete'
			AND pols.contract_status <> 'Cancelada'
			AND pols.contract_status <> 'Prorrogada'
			AND pols.contract_status <> 'NO Tomada'
			AND pols.contract_status <> 'En Trámite'
			AND pols.contract_status <> 'Trámite Suspendido'
			AND pols.contract_status <> 'Rechazada'
			AND polcf.cf_841 <> ''
			AND polcf.cf_945 IS NULL
		";
		if ( $qry = $obj_con->query($txt_qry) ) {
			if ( $qry->rowCount() > 0 ) {
				$datos_qry = $qry->fetchAll(PDO::FETCH_ASSOC);
				return $datos_qry;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

?>