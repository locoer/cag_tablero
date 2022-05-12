<?php
class InfoAsesores extends Info {
	public function negocios_ingresados ($tipo) {
		/* Función para traer información sobre la prima nueva ingresada a la promotoría, solicitudes en trámite que aún no tienen número de póliza.
		Sólo toma en cuenta la "fecha_fin" del objeto para traer todo lo ingresado hasta x fecha
		La variable tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima ingresada de solicitudes de Vida
		- GMM: Trae sólo la prima ingresada de solicitudes de GMM
		- MetaLife: Trae sólo la prima ingresada de solicitudes de MetaLife
		La función regresa un arreglo con el monto de prima ingresada (en pesos), la suma asegurada (en pesos) y el número de solicitudes ingresadas:
		$negocios_ingresados = array(
			"asesor_1" => array (
				"prima_anual" => ###,
				"suma_asegurada" => ###,
				"solicitudes" => ###
			),
			"asesor_n" => ...
		);
		*/
		$filtros_tipo = "";
		if ( preg_match("/vida/i", $tipo) ) {
			$filtros_tipo = "AND pols.contract_type LIKE '%Vida%' ";
		}elseif ( preg_match("/GMM/i", $tipo) ){
			$filtros_tipo = "AND pols.contract_type LIKE '%GMM%' ";
		}elseif ( preg_match("/MetaLife/i", $tipo) ){
			$filtros_tipo = "AND polcf.cf_853 LIKE '%MetaLife%' "; //Filtra por el campo de producto
		}
		
		$txt_qry = "
			SELECT polcf.cf_855 AS moneda, SUM(polcf.cf_849) AS prima_anual, SUM(polcf.cf_845) AS suma_aseg, COUNT(entity.crmid) AS solicitudes, asesores.id AS id_asesor, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo 
			FROM vtiger_servicecontractscf AS polcf
			JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
			JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
			JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE entity.deleted = 0 
			$filtros_tipo
			{$this->filtro_asesor()}
			AND polcf.cf_841 = '' /* Numero de poliza esta vacio */
			AND polcf.cf_941 IS NULL /* NO hay fecha de primer pago */
			AND polcf.cf_903 <= '{$this->fecha_fin}' 
			AND pols.contract_status NOT LIKE '%No Tomada%'
			AND pols.contract_status NOT LIKE '%Rechazada%' 
			AND pols.contract_status NOT LIKE '%Cancelada%' 
			AND pols.contract_status NOT LIKE '%Complet%' 
			AND pols.contract_type NOT LIKE '%Renovac%'
			GROUP BY polcf.cf_855, asesores.id ORDER BY polcf.cf_855 DESC /* Lo agrupa por tipo de moneda */
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$negocios_ingresados = array();
			$anio = date("Y", strtotime($this->fecha_fin));
			$valor_dolar = $this->valor_dolar[$anio];
			foreach( $datos as $fila => $info ) {
				if ( !isset($negocios_ingresados[$info["nombre_completo"]]) ) {
					$negocios_ingresados[$info["nombre_completo"]] = array (
						"prima_anual" => 0,
						"suma_asegurada" => 0,
						"solicitudes" => 0
					);
				}
				if( preg_match ("/Pesos/i", $info["moneda"] ) ){
					$negocios_ingresados [$info["nombre_completo"]]["prima_anual"] += $info["prima_anual"];
					$negocios_ingresados [$info["nombre_completo"]]["suma_asegurada"] += $info["suma_aseg"];
					$negocios_ingresados [$info["nombre_completo"]]["solicitudes"] += $info["solicitudes"];
				}elseif ( preg_match ("/lares/i", $info["moneda"] ) ) {
					$negocios_ingresados [$info["nombre_completo"]]["prima_anual"] += $info["prima_anual"] * $valor_dolar;
					$negocios_ingresados [$info["nombre_completo"]]["suma_asegurada"] += $info["suma_aseg"] * $valor_dolar;
					$negocios_ingresados [$info["nombre_completo"]]["solicitudes"] += $info["solicitudes"];
				}
			}
			
			return $negocios_ingresados;
		} else {
			return false;
		}
	}
	
	public function negocios_emitidos ($tipo) {
		/* Función para traer información sobre los negocio nuevos emitidos en la promotoría, pólizas emitidas que aún no tienen un pago registrado.
		Sólo toma en cuenta la "fecha_fin" del objeto para traer todo lo emitido hasta x fecha
		La variable tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima emitida de solicitudes de Vida
		- GMM: Trae sólo la prima emitida de solicitudes de GMM
		- MetaLife: Trae sólo la prima emitida de solicitudes de MetaLife
		La función regresa un arreglo con el monto de prima emitida (en pesos), la suma asegurada (en pesos) y el número de solicitudes emitidas:
		$negocios_emitidos = array(
			"asesor_1" => array (
				"prima_anual" => ###,
				"suma_asegurada" => ###,
				"solicitudes" => ###
			),
			"asesor_n" => ...
		);
		*/
		$filtros_tipo = "";
		if ( preg_match("/vida/i", $tipo) ) {
			$filtros_tipo = "AND pols.contract_type LIKE '%Vida%' ";
		}elseif ( preg_match("/GMM/i", $tipo) ){
			$filtros_tipo = "AND pols.contract_type LIKE '%GMM%' ";
		}elseif ( preg_match("/MetaLife/i", $tipo) ){
			$filtros_tipo = "AND polcf.cf_853 LIKE '%MetaLife%' "; //Filtra por el campo de producto
		}
		
		$txt_qry = "
			SELECT polcf.cf_855 AS moneda, SUM(polcf.cf_849) AS prima_anual, SUM(polcf.cf_845) AS suma_aseg, COUNT(entity.crmid) AS solicitudes, asesores.id AS id_asesor, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo 
			FROM vtiger_servicecontractscf AS polcf
			JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
			JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
			JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE entity.deleted = 0 
			$filtros_tipo
			{$this->filtro_asesor()}
			AND polcf.cf_841 <> '' /* Numero de poliza NO esta vacio */
			AND polcf.cf_941 IS NULL /* NO hay fecha de primer pago */
			AND polcf.cf_903 <= '{$this->fecha_fin}' 
			AND pols.contract_status NOT LIKE '%No Tomada%'
			AND pols.contract_status NOT LIKE '%Rechazada%' 
			AND pols.contract_status NOT LIKE '%Cancelada%' 
			AND pols.contract_status NOT LIKE '%Complet%' 
			AND pols.contract_type NOT LIKE '%Renovac%'
			GROUP BY polcf.cf_855, asesores.id ORDER BY polcf.cf_855 DESC /* Lo agrupa por tipo de moneda */
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$negocios_emitidos = array();
			$anio = date("Y", strtotime($this->fecha_fin));
			$valor_dolar = $this->valor_dolar[$anio];
			foreach( $datos as $fila => $info ) {
				if ( !isset($negocios_emitidos[$info["nombre_completo"]]) ) {
					$negocios_emitidos[$info["nombre_completo"]] = array (
						"prima_anual" => 0,
						"suma_asegurada" => 0,
						"solicitudes" => 0
					);
				}
				if( preg_match ("/Pesos/i", $info["moneda"] ) ){
					$negocios_emitidos [$info["nombre_completo"]]["prima_anual"] += $info["prima_anual"];
					$negocios_emitidos [$info["nombre_completo"]]["suma_asegurada"] += $info["suma_aseg"];
					$negocios_emitidos [$info["nombre_completo"]]["solicitudes"] += $info["solicitudes"];
				}elseif ( preg_match ("/lares/i", $info["moneda"] ) ) {
					$negocios_emitidos [$info["nombre_completo"]]["prima_anual"] += $info["prima_anual"] * $valor_dolar;
					$negocios_emitidos [$info["nombre_completo"]]["suma_asegurada"] += $info["suma_aseg"] * $valor_dolar;
					$negocios_emitidos [$info["nombre_completo"]]["solicitudes"] += $info["solicitudes"];
				}
			}
			
			return $negocios_emitidos;
		} else {
			return false;
		}
	}
	
	public function prima_pagada_nueva ($tipo) {
		/* Función para traer información sobre la prima pagada de pólizas de 1er año en la promotoría.
		Toma en cuenta la "fecha_inicio" y la "fecha_fin" del objeto
		La variable $tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima pagada de solicitudes de Vida
		- GMM: Trae sólo la prima pagada de solicitudes de GMM
		- MetaLife: Trae sólo la prima pagada de solicitudes de MetaLife
		La función regresa un arreglo con el monto de prima pagada (en pesos) de cada asesor
		$prima_pagada = array (
			"Asesor_1" => ###,
			"Asesor_n" => ###
		);
		*/
		$filtros_tipo = "";
		if ( preg_match("/vida/i", $tipo) ) {
			$filtros_tipo = "AND prpa.Ramo LIKE '%Vida%' ";
		}elseif ( preg_match("/GMM/i", $tipo) ){
			$filtros_tipo = "AND prpa.Ramo LIKE '%Gastos%' ";
		}elseif ( preg_match("/MetaLife/i", $tipo) ){
			$filtros_tipo = "AND polcf.cf_853 LIKE '%MetaLife%' "; //Filtra por el campo de producto
		}
		$txt_qry = "
			SELECT SUM(prpa.Prima_Neta) AS prima_pagada_nueva, asesores.id AS id_asesor, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo 
			FROM finova_prima_pagada AS prpa
				JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
				JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
				JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
				JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE prpa.Dia_de_Pago BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				AND entity.deleted = 0 
				$filtros_tipo
				{$this->filtro_asesor()}
				AND (
					prpa.Tipo_de_Comision LIKE '%PROM%'
					OR prpa.Tipo_de_Comision LIKE '%MANUAL%'
					)
				AND prpa.Tipo_de_Comision NOT LIKE '%BONO%'
				AND prpa.Tipo_de_Comision NOT LIKE '%RENOV%' 
				AND prpa.Tipo_de_Comision NOT LIKE '%COMP%' 
				GROUP BY asesores.id
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$prima_pagada = array();
			foreach ( $datos as $fila => $info ) {
				$prima_pagada[$info["nombre_completo"]] = $info["prima_pagada_nueva"];
			}
			return $prima_pagada;
		} else {
			return false;
		}
	}
	
	public function prima_pagada_renovacion ($tipo) {
		/* Función para traer información sobre la prima pagada de pólizas de 1er año en la promotoría.
		Toma en cuenta la "fecha_inicio" y la "fecha_fin" del objeto
		La variable $tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima pagada de solicitudes de Vida
		- GMM: Trae sólo la prima pagada de solicitudes de GMM
		- MetaLife: Trae sólo la prima pagada de solicitudes de MetaLife
		La función regresa un arreglo con el monto de prima pagada (en pesos) de cada asesor
		$prima_pagada_renovacion = array (
			"Asesor_1" => ###,
			"Asesor_n" => ###
		);
		*/
		$filtros_tipo = "";
		if ( preg_match("/vida/i", $tipo) ) {
			$filtros_tipo = "AND prpa.Ramo LIKE '%Vida%' ";
		}elseif ( preg_match("/GMM/i", $tipo) ){
			$filtros_tipo = "AND prpa.Ramo LIKE '%Gastos%' ";
		}elseif ( preg_match("/MetaLife/i", $tipo) ){
			$filtros_tipo = "AND polcf.cf_853 LIKE '%MetaLife%' "; //Filtra por el campo de producto
		}
		$txt_qry = "
			SELECT SUM(prpa.Prima_Neta) AS prima_pagada_renovacion, asesores.id AS id_asesor, CONCAT(asesores.first_name, ' ', asesores.last_name) AS nombre_completo 
			FROM finova_prima_pagada AS prpa
				JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
				JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
				JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
				JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
			WHERE prpa.Dia_de_Pago BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				AND entity.deleted = 0 
				$filtros_tipo
				{$this->filtro_asesor()}
				AND (
					prpa.Tipo_de_Comision LIKE '%PROM%'
					OR prpa.Tipo_de_Comision LIKE '%MANUAL%'
					)
				AND prpa.Tipo_de_Comision NOT LIKE '%BONO%'
				AND prpa.Tipo_de_Comision LIKE '%RENOV%' 
				AND prpa.Tipo_de_Comision NOT LIKE '%COMP%'
				GROUP BY asesores.id
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$prima_pagada_renovacion = array();
			foreach ( $datos as $fila => $info ) {
				$prima_pagada_renovacion[$info["nombre_completo"]] = $info["prima_pagada_renovacion"];
			}
			
			return $prima_pagada_renovacion;
		} else {
			return false;
		}
	}
	
	public function negocios_nuevos_activos ($tipo) {
		/* Función para traer información sobre los negocio nuevos activos en la promotoría, pólizas emitidas con al menos un pago registrado.
		Toma en cuenta la "fecha_inicio" y la "fecha_fin" del objeto
		La variable tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima nueva anualizada de solicitudes de Vida
		- GMM: Trae sólo la prima nueva anualizada de solicitudes de GMM
		- MetaLife: Trae sólo la prima nueva anualizada de solicitudes de MetaLife
		La función regresa un arreglo con el monto de prima nueva anualizada (en pesos), la suma asegurada (en pesos) y el número de pólizas activas:
		$negocios_nuevos_activos = array(
			"asesor_1" => array(
				"prima_anual" => ###,
				"prima_pagada" => ###,
				"suma_asegurada" => ###,
				"polizas" => ###,
				"polizas_21" => ###
			),
			"asesor_n" => array(
				"prima_anual" => ###,
				"prima_pagada" => ###,
				"suma_asegurada" => ###,
				"polizas" => ###,
				"polizas_21" => ###
			)
		);
		*/
		$filtros_tipo = "";
		if ( preg_match("/vida/i", $tipo) ) {
			$filtros_tipo = "AND pols.contract_type LIKE '%Vida%' ";
		}elseif ( preg_match("/GMM/i", $tipo) ){
			$filtros_tipo = "AND pols.contract_type LIKE '%GMM%' ";
		}elseif ( preg_match("/MetaLife/i", $tipo) ){
			$filtros_tipo = "AND polcf.cf_853 LIKE '%MetaLife%' "; //Filtra por el campo de producto
		}
		
		$hoy = date("Y-m-d");
		$txt_qry = "
			SELECT SUM(total_pagado) AS suma_total_pagado, SUM(prima_anual) AS suma_prima_anual, moneda, anio_anualiza,  SUM(suma_aseg) AS suma_suma_aseg, COUNT(poliza) AS suma_polizas, SUM(pol_21) AS pols_21, id_asesor, nombre_completo
			FROM (
					SELECT 
						prpa.Ramo AS ramo, prpa.Asegurado AS asegurado, prpa.Poliza AS poliza, MIN(prpa.Dia_de_Pago)  AS fecha_anualiza, YEAR(MIN(prpa.Dia_de_Pago))  AS anio_anualiza, SUM(prpa.Prima_Neta) AS total_pagado, SUM(prpa.Ingreso_X_Comision) AS total_comision,
						polcf.cf_841 AS num_poliza, polcf.servicecontractsid AS id_polcf, polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_947 AS prima_neta, polcf.cf_855 AS moneda, polcf.cf_845 AS suma_aseg, IF(polcf.cf_855 LIKE '%lares%', IF(polcf.cf_947>=1165,1,0), IF(polcf.cf_947>= 21000, 1,0)) AS pol_21,
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
						$filtros_tipo
						{$this->filtro_asesor()}
						AND entity.deleted = 0
						AND prpa.Tipo_de_Comision NOT LIKE  '%BONO%'
						AND prpa.Tipo_de_Comision NOT LIKE  '%RENOV%'
						AND prpa.Poliza NOT LIKE  ' '
					GROUP BY prpa.Poliza
			) AS tablota
			WHERE tablota.fecha_anualiza BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' 
				GROUP BY anio_anualiza, moneda, id_asesor
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$negocios_nuevos_activos = array();
			$anio = date("Y", strtotime($this->fecha_fin));
			$valor_dolar = $this->valor_dolar[$anio];
			foreach( $datos as $fila => $info ) {
				if ( !isset($negocios_nuevos_activos[$info["nombre_completo"]]) ) {
					$negocios_nuevos_activos[$info["nombre_completo"]] = array (
						"prima_anual" => 0,
						"prima_pagada" => 0,
						"suma_asegurada" => 0,
						"polizas" => 0,
						"polizas_21" => 0
					);
				}
				if( preg_match ("/Pesos/i", $info["moneda"] ) ){
					$negocios_nuevos_activos [$info["nombre_completo"]]["prima_anual"] += $info["suma_prima_anual"];
					$negocios_nuevos_activos [$info["nombre_completo"]]["prima_pagada"] += $info["suma_total_pagado"];
					$negocios_nuevos_activos [$info["nombre_completo"]]["suma_asegurada"] += $info["suma_suma_aseg"];
					$negocios_nuevos_activos [$info["nombre_completo"]]["polizas"] += $info["suma_polizas"];
					$negocios_nuevos_activos [$info["nombre_completo"]]["polizas_21"] += $info["pols_21"];
				}elseif ( preg_match ("/lares/i", $info["moneda"] ) ) {
					$negocios_nuevos_activos [$info["nombre_completo"]]["prima_anual"] += $info["suma_prima_anual"] * $this->valor_dolar[$info["anio_anualiza"]];
					$negocios_nuevos_activos [$info["nombre_completo"]]["prima_pagada"] += $info["suma_total_pagado"];
					$negocios_nuevos_activos [$info["nombre_completo"]]["suma_asegurada"] += $info["suma_suma_aseg"] * $this->valor_dolar[$info["anio_anualiza"]];
					$negocios_nuevos_activos [$info["nombre_completo"]]["polizas"] += $info["suma_polizas"];
					$negocios_nuevos_activos [$info["nombre_completo"]]["polizas_21"] += $info["pols_21"];
				}
			}
			
			return $negocios_nuevos_activos;
		} else {
			return false;
		}
	}
	
	public function resumen_asesores() {
		//$resumen_asesores = array();
		if ( !$asesores = $this->asesores_activos ) {
			return false;
		}
		/*
		foreach ( $asesores as $id => $datos ) {
			$resumen_asesores[$datos["nombre_completo"]] = array(
				"negocios_ingresados_vida" => 0,
				"negocios_ingresados_metalife" => 0,
				"negocios_ingresados_gmm" => 0,
				"negocios_emitidos_vida" => 0,
				"negocios_emitidos_metalife" => 0,
				"negocios_emitidos_gmm" => 0,
				"negocios_nuevos_activos_vida" => 0,
				"negocios_nuevos_activos_metalife" => 0,
				"negocios_nuevos_activos_gmm" => 0,
				"prima_pagada_nueva_vida" => 0,
				"prima_pagada_nueva_metalife" => 0,
				"prima_pagada_nueva_gmm" => 0,
				"prima_pagada_renovacion_vida" => 0,
				"prima_pagada_renovacion_metalife" => 0,
				"prima_pagada_renovacion_gmm" => 0,
			);
		}
		*/
		
		$negocios_ingresados_vida = $this->negocios_ingresados("vida");
		$negocios_ingresados_metalife = $this->negocios_ingresados("metalife");
		$negocios_ingresados_gmm = $this->negocios_ingresados("gmm");
		
		$negocios_emitidos_vida = $this->negocios_emitidos("vida");
		$negocios_emitidos_metalife = $this->negocios_emitidos("metalife");
		$negocios_emitidos_gmm = $this->negocios_emitidos("gmm");
		
		$negocios_nuevos_activos_vida = $this->negocios_nuevos_activos("vida");
		$negocios_nuevos_activos_metalife = $this->negocios_nuevos_activos("metalife");
		$negocios_nuevos_activos_gmm = $this->negocios_nuevos_activos("gmm");
		
		$prima_pagada_nueva_vida = $this->prima_pagada_nueva("vida");
		$prima_pagada_nueva_metalife = $this->prima_pagada_nueva("metalife");
		$prima_pagada_nueva_gmm = $this->prima_pagada_nueva("gmm");
		
		$prima_pagada_renovacion_vida = $this->prima_pagada_renovacion("vida");
		$prima_pagada_renovacion_metalife = $this->prima_pagada_renovacion("metalife");
		$prima_pagada_renovacion_gmm = $this->prima_pagada_renovacion("gmm");
		
		$resumen_asesores = array();
		arsort($prima_pagada_nueva_vida);
		foreach( $prima_pagada_nueva_vida as $asesor => $prima ) {
			$resumen_asesores[$asesor] = array(
				"negocios_ingresados_vida" => 0,
				"negocios_ingresados_metalife" => 0,
				"negocios_ingresados_gmm" => 0,
				"negocios_emitidos_vida" => 0,
				"negocios_emitidos_metalife" => 0,
				"negocios_emitidos_gmm" => 0,
				"negocios_nuevos_activos_vida" => 0,
				"negocios_nuevos_activos_metalife" => 0,
				"negocios_nuevos_activos_gmm" => 0,
				"prima_pagada_nueva_vida" => 0,
				"prima_pagada_nueva_metalife" => 0,
				"prima_pagada_nueva_gmm" => 0,
				"prima_pagada_renovacion_vida" => 0,
				"prima_pagada_renovacion_metalife" => 0,
				"prima_pagada_renovacion_gmm" => 0,
			);
		}
		
		foreach( $asesores as $id => $datos ) {
			if ( !array_key_exists($datos["nombre_completo"], $resumen_asesores)  ) {
				$resumen_asesores[$datos["nombre_completo"]] = array(
					"negocios_ingresados_vida" => 0,
					"negocios_ingresados_metalife" => 0,
					"negocios_ingresados_gmm" => 0,
					"negocios_emitidos_vida" => 0,
					"negocios_emitidos_metalife" => 0,
					"negocios_emitidos_gmm" => 0,
					"negocios_nuevos_activos_vida" => 0,
					"negocios_nuevos_activos_metalife" => 0,
					"negocios_nuevos_activos_gmm" => 0,
					"prima_pagada_nueva_vida" => 0,
					"prima_pagada_nueva_metalife" => 0,
					"prima_pagada_nueva_gmm" => 0,
					"prima_pagada_renovacion_vida" => 0,
					"prima_pagada_renovacion_metalife" => 0,
					"prima_pagada_renovacion_gmm" => 0,
				);
			}
		}
		
		foreach ( $resumen_asesores as $asesor => $info ) {
			foreach ( $info as $var => $valor ) {
				$tmp = $$var;
				if( isset( $tmp[$asesor] ) ) {
					$resumen_asesores[$asesor][$var] = $tmp[$asesor];
				}
			}
		}
		return $resumen_asesores;
	}
}
?>