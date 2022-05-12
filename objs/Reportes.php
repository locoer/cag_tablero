<?php
class Reportes {
	protected $asesor;
	protected $asesores_activos;
	protected $fecha_inicio;
	protected $fecha_fin;
	protected $valor_dolar;
	protected $valor_uvac;
	
	public function __construct (Usuario $asesor) {
		$this->asesor = $asesor;
		$this->asesores_activos = $this->asesores_activos();
		$this->fecha_inicio = date("Y-m-d", strtotime("first day of January"));
		$this->fecha_fin = date("Y-m-d");
		$this->valor_dolar = array (
			2016 => 17.00,
			2017 => 18.5,
			2018 => 18.7,
			2019 => 19.5
		);
		$this->valor_uvac = array (
			2016 => 15.10,
			2017 => 15.5,
			2018 => 15.7,
			2019 => 15.9
		);
	}
	
	public function __destruct () {
		// clean up here
	}
	
	public function desde () {
		return $this->fecha_inicio;
	}
	
	public function hasta () {
		return $this->fecha_fin;
	}
	
	public function fecha_inicio ($fecha_inicio) { //revisar que el formato sea una fecha
		if ( $this->revisa_fecha($fecha_inicio) ) {
			if ( strtotime($fecha_inicio) > strtotime($this->fecha_fin) ) {
				$this->fecha_inicio = $this->fecha_fin;
			} elseif ( strtotime($fecha_inicio) < strtotime("2016-01-01") ) {
				$this->fecha_inicio = date ("Y-m-d", strtotime("2016-01-01"));
			} else {
				$this->fecha_inicio = $fecha_inicio;
			}
		}
	}
	
	public function fecha_fin ($fecha_fin) { //revisar que el formato sea una fecha
		if ( $this->revisa_fecha($fecha_fin) ) {
			if ( strtotime($fecha_fin) < strtotime($this->fecha_inicio) ) {
				$this->fecha_fin = $this->fecha_inicio;
			} elseif ( strtotime($fecha_fin) > strtotime("today") ) {
				$this->fecha_fin = date ("Y-m-d", strtotime("today"));
			} else {
				$this->fecha_fin = $fecha_fin;
			}
		}
	}
	
	public function periodo ($fecha_inicio, $fecha_fin) { //revisar que el formato sea una fecha
		if ( $fecha_inicio = $this->revisa_fecha($fecha_inicio) && $fecha_fin = $this->revisa_fecha($fecha_fin) ) {
			if ( strtotime($fecha_inicio) < strtotime($fecha_fin) ) {
				if (strtotime($fecha_inicio) > strtotime("2016-01-01")) {
					$this->fecha_inicio = $fecha_inicio;
				} else {
					$this->fecha_inicio = "2016-01-01";
				}
				if (strtotime($fecha_fin) < strtotime("today")) {
					$this->fecha_fin = $fecha_fin;
				} else {
					$this->fecha_fin = date ("Y-m-d", strtotime("today"));
				}
			} else {
				$this->fecha_inicio = date("Y-m-d", strtotime("first day of January"));
				$this->fecha_fin = date("Y-m-d");
			}
		}
	}
	
	public function revisa_fecha ($fecha) {
		$fecha_bien = preg_replace ("/[^0-9\-]/", "", $fecha); //quita caracteres que no sean dígitos o guiones
		if ( preg_match ("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $fecha_bien) ) { //revisa que siga el formato de una fecha
			return $fecha_bien;
		} else {
			return false;
		}
	}
	
	public function fecha_formato ($fecha) {
		$fecha_formato = utf8_encode(strftime ("%A %e de %B de %Y", strtotime($fecha)));
		return $fecha_formato;
	}
	
	protected function arma_filtros ($filtros) { // sólo para tablas del crm junto con la de prima pagada
		$ids = 0;
		$filtro_asesores = "";
		if ( !$this->asesor->esAdmin() ) {
			$filtros_qry = "AND entity.smownerid = {$this->asesor->info()["id_bds"]} ";
		} else {
			$filtros_qry = "";
		}
		
		foreach ( $filtros as $filtro ) {
			switch ( $filtro ) {
				case "vida" :
					$filtros_qry .= "AND prpa.Ramo LIKE '%Vida%' ";
					break;
				case "gmm" :
					$filtros_qry .= "AND prpa.Ramo LIKE '%Gastos%' ";
					break;
				case "vida_crm" :
					$filtros_qry .= "AND pols.contract_type LIKE '%Vida%' ";
					break;
				case "gmm_crm" :
					$filtros_qry .= "AND pols.contract_type LIKE '%GMM%' ";
					break;
				default :
					if ( is_int($filtro) && $filtro > 0 && $this->asesor->esAdmin() ) {
						if ( $ids === 0 ) {
							$filtro_asesores .= "( entity.smownerid = $filtro ";
						} else {
							$filtro_asesores .= "OR entity.smownerid = $filtro ";
						}
						$ids++;
					}
					break;
			}
		}
		if ( !empty($filtro_asesores) ) {
			$filtros_qry .= "AND $filtro_asesores ) ";
		}
		return $filtros_qry;
	}
	
	public function asesores_activos () {
		if ( !$this->asesor->esAdmin() ) {
			return false;
		}
		$asesores_baja = array(1, 9, 17, 18, 20, 21, 26, 29, 31, 33, 34, 42,); //17 es  margarita
		$txt_asesores_baja = implode(", ", $asesores_baja);
		$txt_qry = "
			SELECT usuarios.id AS id, usuarios.user_name AS usuario, usuarios.first_name AS nombre, usuarios.last_name AS apellido, CONCAT(usuarios.first_name, ' ', usuarios.last_name) AS nombre_completo, usuarios.email1 AS email, 
			rol.roleid AS id_rol, rol.rolename AS rol
			FROM vtiger_users AS usuarios 
			JOIN vtiger_user2role AS usuario_rol ON usuarios.id = usuario_rol.userid
			JOIN vtiger_role AS rol ON usuario_rol.roleid = rol.roleid
			WHERE status = 'Active' 
			AND usuarios.id NOT IN ($txt_asesores_baja)
			ORDER BY usuarios.last_name ASC
		";
		$datos = array();
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos_raw = $qry->fetchAll(PDO::FETCH_ASSOC);
			foreach ( $datos_raw as $fila => $datos_asesor ) {
				$datos[$datos_asesor["id"]] = $datos_asesor;
			}
			return $datos;
		} else {
			return false;
		}
	}
	
	public function convierte_pesos (...$datos) {
		$tipo_de_cambio = 0;
		$monto = 0;
		$moneda = "USD";
		$anio = 2019;
		foreach( $datos as $var ) {
			if( preg_match("/lares$/", $var) ) {
				$moneda = "USD";
			} elseif ( preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $var) ) {
				$anio = substr($var,0,4);
			} elseif ( is_float($var) ) {
				$monto = $var;
			}
		}
		if ( $moneda == "USD" ) {
			$tipo_de_cambio = $this->valor_dolar[$anio];
		} elseif ( $moneda == "UVAC" ) {
			$tipo_de_cambio = $valor_uvac[$anio];
		} else {
			$tipo_de_cambio = 1;
		}
		$monto_pesos = $monto * $tipo_de_cambio;
		
		return $monto_pesos;
	}
	
	public function busca_pagos($poliza) {
		//revisar que $poliza sea una póliza con regex
		$obj_con = Sitio::$obj_bds;
		$info_asesor = $this->asesor->info();
		if ( !$this->asesor->esAdmin() ) {
			$filtro_usuario = "AND entity.smownerid = {$info_asesor["id_bds"]}";
		} else {
			$filtro_usuario = "";
		}
		$txt_qry = "
			SELECT pagos.ID AS idpagos, pagos.Corte AS corte, pagos.Poliza AS polizaMet, pagos.Tipo_de_Comision AS tipo, pagos.Inicio_Vigor AS vigor, pagos.Dia_de_Pago AS fechapago, pagos.Prima_Neta AS monto, 
			asesores.id AS idasesor, asesores.user_name AS usuario, asesores.first_name AS nombre, asesores.last_name AS apellidos, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombrecompleto, asesores.title AS posicion, asesores.status AS statusasesor
			FROM finova_prima_pagada AS pagos 
			LEFT JOIN vtiger_users AS asesores ON asesores.id = pagos.id_asesor
			WHERE pagos.Poliza = '$poliza'
			AND pagos.Tipo_de_Comision LIKE '%PROM%' 
			AND pagos.Tipo_de_Comision NOT LIKE '%BONO%'
			ORDER BY pagos.Dia_de_Pago DESC
		";
		//$qry = $obj_con->prepare($txt_qry);
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function pagos (...$filtros) {
		$obj_con = Sitio::$obj_bds;
		$info_asesor = $this->asesor->info();
		if ( !$this->asesor->esAdmin() ) {
			$filtro_usuario = "AND entity.smownerid = {$info_asesor["id_bds"]}";
		} else {
			$filtro_usuario = "";
		}
		$txt_qry = "
			SELECT pagos.ID AS idpagos, pagos.Corte AS corte, pagos.Ramo AS ramo, pagos.Poliza AS polizaMet, pagos.Asegurado AS contratante, pagos.Tipo_de_Comision AS tipo, pagos.Inicio_Vigor AS vigor, pagos.Dia_de_Pago AS fechapago, pagos.Prima_Neta AS monto, 
			polcf.cf_841 AS poliza, polcf.servicecontractsid AS idcrm, polcf.cf_853 AS producto, polcf.cf_849 AS primaanual, polcf.cf_855 AS moneda, polcf.cf_845 AS sumaasegurada, 
			pols.servicecontractsid, pols.contract_status AS statuspoliza, pols.contract_type AS tipopoliza, 
			entity.crmid, entity.smownerid, entity.deleted,
			asesores.id AS idasesor, asesores.user_name AS usuario, asesores.first_name AS nombre, asesores.last_name AS apellidos, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombrecompleto, asesores.title AS posicion, asesores.status AS statusasesor
			FROM finova_prima_pagada AS pagos 
			JOIN vtiger_servicecontractscf AS polcf ON pagos.Poliza = polcf.cf_841
			JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
			JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
			JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE entity.deleted = 0
			AND (
				pagos.Tipo_de_Comision LIKE '%PROM%'
				OR pagos.Tipo_de_Comision LIKE '%MANUAL%'
				OR pagos.Ramo LIKE '%Colectivo%'
				)
			AND pagos.Tipo_de_Comision NOT LIKE '%BONO%'
			AND pagos.Poliza <> '' 
			{$this->arma_filtros($filtros)} 
			ORDER BY pagos.Dia_de_Pago DESC
		";
		//$qry = $obj_con->prepare($txt_qry);
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function prima_pagada_nueva (...$filtros) {
		$txt_qry = "
			SELECT SUM(prpa.Prima_Neta) AS prima_pagada_nueva
			FROM finova_prima_pagada AS prpa
				JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
				JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
				JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
				JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE prpa.Dia_de_Pago BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				{$this->arma_filtros($filtros)} 
				AND entity.deleted = 0
				AND (
					prpa.Tipo_de_Comision LIKE '%PROM%'
					OR prpa.Tipo_de_Comision LIKE '%MANUAL%'
					)
				AND prpa.Tipo_de_Comision NOT LIKE '%BONO%'
				AND prpa.Tipo_de_Comision NOT LIKE '%RENOV%' 
				AND prpa.Tipo_de_Comision NOT LIKE '%COMP%'
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetch(PDO::FETCH_ASSOC);
			return $datos["prima_pagada_nueva"];
		} else {
			return false;
		}
	}
	
	public function prima_pagada_renovacion (...$filtros) {
		$txt_qry = "
			SELECT SUM(prpa.Prima_Neta) AS prima_pagada_renovacion
			FROM finova_prima_pagada AS prpa
				JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
				JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
				JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
				JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE prpa.Dia_de_Pago BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				{$this->arma_filtros($filtros)} 
				AND entity.deleted = 0
				AND (
					prpa.Tipo_de_Comision LIKE '%PROM%'
					OR prpa.Tipo_de_Comision LIKE 'MANUAL%'
					)
				AND prpa.Tipo_de_Comision NOT LIKE '%BONO%'
				AND prpa.Tipo_de_Comision LIKE '%RENOV%' 
				AND prpa.Tipo_de_Comision NOT LIKE '%COMP%' 
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetch(PDO::FETCH_ASSOC);
			return $datos["prima_pagada_renovacion"];
		} else {
			return false;
		}
	}
	
	public function prima_nueva_ingresada (...$filtros) {
		//Identificar las que son en otra moneda que no sea pesos
		$polizas_ingresadas = $this->polizas_ingresadas(...$filtros);
		$prima_nueva_ingresada = 0;
		foreach ( $polizas_ingresadas as $fila => $poliza ) {
			if ( preg_match("/lares$/i", $poliza["moneda"]) || preg_match("/^UVAC$/i", $poliza["moneda"]) ) {
				$prima_nueva_ingresada += $this->convierte_pesos($poliza["prima_anual"], $poliza["moneda"], $poliza["fecha_ingreso_sol"]);
			} else {
				$prima_nueva_ingresada += $poliza["prima_anual"];
			}
		}
		return $prima_nueva_ingresada;
	}
	
	public function prima_nueva_emitida (...$filtros) {
		//Identificar las que son en otra moneda que no sea pesos
		$polizas_emitidas = $this->polizas_emitidas(...$filtros);
		$prima_nueva_emitida = 0;
		foreach ( $polizas_emitidas as $fila => $poliza ) {
			if ( preg_match("/lares$/i", $poliza["moneda"]) || preg_match("/^UVAC$/i", $poliza["moneda"]) ) {
				$prima_nueva_emitida += $this->convierte_pesos($poliza["prima_anual"], $poliza["moneda"], $poliza["fecha_ingreso_sol"]);
			} else {
				$prima_nueva_emitida += $poliza["prima_anual"];
			}
		}
		return $prima_nueva_emitida;
	}
	
	public function prima_nueva_anualizada (...$filtros) {
		//Identificar las que son en otra moneda que no sea pesos
		$polizas_activas = $this->polizas_activas(...$filtros);
		$prima_nueva_anualizada = 0;
		foreach ( $polizas_activas as $fila => $poliza ) {
			if ( preg_match("/lares$/i", $poliza["moneda"]) || preg_match("/^UVAC$/i", $poliza["moneda"]) ) {
				$prima_nueva_anualizada += $this->convierte_pesos($poliza["prima_anual"], $poliza["moneda"], $poliza["fecha_anualiza"]);
			} else {
				$prima_nueva_anualizada += $poliza["prima_anual"];
			}
		}
		return $prima_nueva_anualizada;
	}
	
	public function polizas_ingresadas (...$filtros) {
		$hoy = date("Y-m-d");
		
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
			{$this->arma_filtros($filtros)}
			AND polcf.cf_841 = '' 
			AND polcf.cf_941 IS NULL 
			AND polcf.cf_903 <= '{$this->fecha_fin}' 
			AND pols.contract_status NOT LIKE '%No Tomada%' 
			AND pols.contract_status NOT LIKE '%Rechazada%' 
			AND pols.contract_status NOT LIKE '%Cancelada%' 
			AND pols.contract_status NOT LIKE '%Complet%' 
			AND pols.contract_type NOT LIKE '%Otras Compa%'
			AND pols.contract_type NOT LIKE '%liza RC%'
			AND polcf.cf_841 NOT LIKE '%VI%' 
			AND polcf.cf_841 NOT LIKE '%GM%' 
			GROUP BY entity.crmid ORDER BY pols.contract_type DESC, polcf.cf_841 DESC
		";
		
		
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function polizas_emitidas (...$filtros) {
		$hoy = date("Y-m-d");
		
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
			{$this->arma_filtros($filtros)}
			AND polcf.cf_841 <> '' 
			AND polcf.cf_941 IS NULL 
			AND polcf.cf_903 <= '{$this->fecha_inicio}' 
			AND pols.contract_status NOT LIKE '%No Tomada%' 
			AND pols.contract_status NOT LIKE '%Rechazada%' 
			AND pols.contract_status NOT LIKE '%Cancelada%' 
			AND pols.contract_status NOT LIKE '%Complet%' 
			AND pols.contract_type NOT LIKE '%Otras Compa%'
			AND pols.contract_type NOT LIKE '%liza RC%'
			AND polcf.cf_841 NOT LIKE '%VI%' 
			AND polcf.cf_841 NOT LIKE '%GM%' 
			GROUP BY entity.crmid ORDER BY pols.contract_type DESC, polcf.cf_841 DESC
		";
		
		
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function polizas_activas (...$filtros) {
		$hoy = date("Y-m-d");
		$txt_qry = "
			SELECT ramo, asegurado, poliza, fecha_anualiza, total_pagado, total_comision,
				num_poliza, producto, prima_anual, moneda, suma_aseg, 
				estatus, tipo, id_crm, id_usuario, usuario, nombre, nombre_completo, cargo, status_asesor
			FROM (
					SELECT 
						prpa.Ramo AS ramo, prpa.Asegurado AS asegurado, prpa.Poliza AS poliza, MIN(prpa.Dia_de_Pago)  AS fecha_anualiza, SUM(prpa.Prima_Neta) AS total_pagado, SUM(prpa.Ingreso_X_Comision) AS total_comision,
						polcf.cf_841 AS num_poliza, polcf.servicecontractsid AS id_polcf, polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_855 AS moneda, polcf.cf_845 AS suma_aseg, 
						pols.servicecontractsid AS id_pols, pols.contract_status AS estatus, pols.contract_type AS tipo, 
						entity.crmid AS id_crm, entity.smownerid AS id_usuario, entity.deleted AS deleted,
						asesores.id AS id_asesor, asesores.user_name AS usuario, asesores.first_name AS nombre, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo, asesores.title AS cargo, asesores.status AS status_asesor 
					FROM finova_prima_pagada AS prpa
						JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
						JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
						JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
						JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
					WHERE prpa.Dia_de_Pago BETWEEN '2015-01-01' 
						AND '$hoy' 
						{$this->arma_filtros($filtros)}
						AND entity.deleted = 0
						AND prpa.Tipo_de_Comision NOT LIKE  '%BONO%'
						AND prpa.Tipo_de_Comision NOT LIKE  '%RENOV%'
						AND prpa.Poliza NOT LIKE  ' '
					GROUP BY prpa.Poliza
			) AS tablota
			WHERE tablota.fecha_anualiza BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				ORDER BY ramo DESC, fecha_anualiza DESC
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function polizas_activas_todas (...$filtros) {
		$hoy = date("Y-m-d");
		$txt_qry = "
			SELECT ramo, asegurado, poliza, fecha_anualiza, total_pagado, total_comision,
				num_poliza, producto, prima_anual, moneda, suma_aseg, 
				estatus, tipo, id_crm, id_usuario, usuario, nombre, nombre_completo, cargo, status_asesor
			FROM (
					SELECT 
						prpa.Ramo AS ramo, prpa.Asegurado AS asegurado, prpa.Poliza AS poliza, MIN(prpa.Dia_de_Pago)  AS fecha_anualiza, SUM(prpa.Prima_Neta) AS total_pagado, SUM(prpa.Ingreso_X_Comision) AS total_comision,
						polcf.cf_841 AS num_poliza, polcf.servicecontractsid AS id_polcf, polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_855 AS moneda, polcf.cf_845 AS suma_aseg, 
						pols.servicecontractsid AS id_pols, pols.contract_status AS estatus, pols.contract_type AS tipo, 
						entity.crmid AS id_crm, entity.smownerid AS id_usuario, entity.deleted AS deleted,
						asesores.id AS id_asesor, asesores.user_name AS usuario, asesores.first_name AS nombre, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo, asesores.title AS cargo, asesores.status AS status_asesor 
					FROM finova_prima_pagada AS prpa
						JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
						JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
						JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
						JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
					WHERE prpa.Dia_de_Pago BETWEEN '2015-01-01' 
						AND '$hoy' 
						{$this->arma_filtros($filtros)}
						AND entity.deleted = 0
						AND prpa.Tipo_de_Comision NOT LIKE  '%BONO%'
						AND prpa.Poliza NOT LIKE  ' '
					GROUP BY prpa.Poliza
			) AS tablota
			WHERE tablota.fecha_anualiza BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				ORDER BY ramo DESC, fecha_anualiza DESC
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	/*
		función polizas_cobranza()
		Recibe filtros 
		Regresa las pólizas del crm con al menos 1 pago registrado.
	*/
	public function polizas_cobranza (...$filtros) {
		$hoy = date("Y-m-d");
		$txt_qry = "
			SELECT tipo, producto, moneda, poliza, contratante, rfc_contratante, fecha_anualiza, total_pagado, prima_anual, suma_aseg, asesor
			FROM (
					SELECT 
						prpa.Ramo AS ramo, prpa.Asegurado AS asegurado, prpa.Poliza AS poliza, MIN(prpa.Dia_de_Pago)  AS fecha_anualiza, SUM(prpa.Prima_Neta) AS total_pagado, SUM(prpa.Ingreso_X_Comision) AS total_comision,
						polcf.cf_841 AS num_poliza, polcf.servicecontractsid AS id_polcf, polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_855 AS moneda, polcf.cf_845 AS suma_aseg, 
						pols.servicecontractsid AS id_pols, pols.contract_status AS estatus, pols.contract_type AS tipo, 
						CONCAT (contactos.firstname, ' ', contactos.lastname) AS contratante, contactos.contactid AS id_contacto, contactoscf.cf_829 AS rfc_contratante, contactoscf.contactid AS id_contactocf, 
						entity.crmid AS id_crm, entity.smownerid AS id_usuario, entity.deleted AS deleted,
						asesores.id AS id_asesor, asesores.user_name AS usuario, asesores.first_name AS nombre, CONCAT(asesores.first_name, ' ', asesores.last_name) AS asesor, asesores.title AS cargo, asesores.status AS status_asesor 
					FROM finova_prima_pagada AS prpa
						JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
						JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
						JOIN vtiger_contactdetails AS contactos ON contactos.contactid = pols.sc_related_to 
						JOIN vtiger_contactscf AS contactoscf ON contactoscf.contactid = contactos.contactid
						LEFT JOIN vtiger_contactdetails AS aseguradoscf ON aseguradoscf.contactid = polcf.cf_869
						JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
						JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
					WHERE prpa.Dia_de_Pago BETWEEN '2015-01-01' 
						AND '$hoy' 
						{$this->arma_filtros($filtros)}
						AND entity.deleted = 0
						AND prpa.Tipo_de_Comision NOT LIKE  '%BONO%'
						AND prpa.Poliza NOT LIKE  ' ' 
						AND pols.contract_status NOT LIKE '%Complet%' 
						AND pols.contract_status NOT LIKE '%Prorr%' 
						AND pols.contract_status NOT LIKE '%Cancelada%' 
					GROUP BY prpa.Poliza
			) AS tablota
			WHERE tablota.fecha_anualiza BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				ORDER BY ramo DESC, fecha_anualiza DESC
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	/*
		función polizas_todas()
		Recibe filtros 
		Regresa todas las pólizas del crm, sin la tabla de pagos
	*/
	function polizas_todas (...$filtros) {
		$hoy = date("Y-m-d");
		
		$txt_qry = "
			SELECT num_poliza, tipo, producto, prima_anual, moneda, suma_aseg, forma_pago, id_seguimiento, fecha_ingreso_sol, fecha_entrega, fecha_inicio, fecha_primer_pago, 
			contratante_crm, rfc_contratante, 
			aseguradotxt, aseguradoidtxt, 
			estatus, id_crm, id_usuario, usuario, nombre_completo, nombre, apellido, cargo, status_asesor
			FROM (
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
				WHERE polcf.cf_903 BETWEEN '2015-01-01' 
				AND '$hoy' 
				{$this->arma_filtros($filtros)}
				AND entity.deleted = 0
				AND polcf.cf_853 NOT LIKE '%Imagina%'
				AND polcf.cf_853 NOT LIKE '%Alfa%'
				AND polcf.cf_853 NOT LIKE '%ORVI%'
				AND polcf.cf_853 NOT LIKE '%Otros%'
				AND polcf.cf_853 NOT LIKE '%Realiza%'
				AND polcf.cf_853 NOT LIKE '%Vida Mujer%'
				GROUP BY entity.crmid ORDER BY pols.contract_type DESC, polcf.cf_841 DESC) AS tablota 
			WHERE tablota.fecha_ingreso_sol BETWEEN '{$this->fecha_inicio}' 
			AND '{$this->fecha_fin}'
		";
		
		
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	function actividades_seguimiento () {
		$txt_qry = "SELECT * FROM cohenag_actividades_seguimiento ORDER BY orden ASC;";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function actividades_asesor (...$filtros) {
		$filtro_asesor = "";
		if( isset($filtros) ) {
			foreach ( $filtros as $filtro ) {
				if ( preg_match("/^[0-9]*$/", $filtro) && $filtro > 0 ) {
					$filtro_asesor .= "AND puntos.id_asesor = $filtro ";
				}
			}
		}
		if ( !$this->asesor->esAdmin() ) {
			$filtro_asesor = "AND puntos.id_asesor = {$this->asesor->info()["id_bds"]}";
		}
		$txt_qry = "
			SELECT puntos.fecha AS fecha, puntos.referidos AS referidos, puntos.citas_nuevas AS citas_nuevas, puntos.citas_iniciales AS citas_iniciales, puntos.citas_cierre AS citas_cierre, puntos.citas_poliza AS citas_poliza, puntos.citas_ci AS citas_ci, puntos.solicitud_firmada AS solicitud_firmada, puntos.poliza_pagada AS poliza_pagada, puntos.poliza_anual AS poliza_anual, puntos.citas_acompanamiento AS citas_acompanamiento, puntos.cierre_acompanamiento AS cierre_acompanamiento, 
			CONCAT(usuarios.first_name, ' ', usuarios.last_name) AS asesor
			FROM cohenag_actividad_asesor AS puntos 
			JOIN vtiger_users AS usuarios ON usuarios.id = puntos.id_asesor
			WHERE puntos.fecha BETWEEN '{$this->fecha_inicio}' AND '{$this->fecha_fin}'
			$filtro_asesor
			ORDER BY puntos.id_asesor ASC, puntos.fecha ASC
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ) {
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
	public function resumen_actividad () {
		
		if ( !$actividades = $this->actividades_seguimiento() ) {
			return false;
		}
		$txt_qry = "SELECT puntos.id AS id, puntos.id_asesor AS id_asesor, ";
		foreach ( $actividades as $fila => $datos ) {
			$txt_qry .= "SUM(puntos.{$datos['nom_col']}) AS {$datos['nom_col']}, ";
		}
		//$txt_qry = preg_replace("/,\s{1}$/", " ", $txt_qry);
		$txt_qry .= "
			CONCAT(usuarios.first_name, ' ', usuarios.last_name) AS asesor 
			FROM cohenag_actividad_asesor as puntos 
			JOIN vtiger_users AS usuarios ON usuarios.id = puntos.id_asesor
			WHERE puntos.fecha BETWEEN '{$this->fecha_inicio}' AND '{$this->fecha_fin}'
			GROUP BY puntos.id_asesor 
			ORDER BY puntos.id_asesor ASC, puntos.fecha ASC
		";
		
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ) {
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			foreach ( $datos as $fila => $info ) {
				$resumen_actividad[$info["id_asesor"]] = $info;
			}
			return $resumen_actividad;
		} else {
			return false;
		}
		
	}
}
?>