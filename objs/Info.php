<?php
class Info {
	protected $asesor;
	protected $asesores_activos;
	protected $fecha_inicio;
	protected $fecha_fin;
	protected $valor_dolar;
	protected $valor_uvac;
	protected $obj_con;
	
	public function __construct (Usuario $asesor) {
		$this->obj_con = Sitio::conecta_bds();
		$this->asesor = $asesor;
		$this->asesores_activos = $this->asesores_activos();
		$this->fecha_inicio = date("Y-m-d", strtotime("first day of January"));
		$this->fecha_fin = date("Y-m-d");
		$this->valor_dolar = array (
			2012 => 15.50,
			2013 => 16.00,
			2014 => 16.50,
			2015 => 17.00,
			2016 => 17.00,
			2017 => 18.5,
			2018 => 18.7,
			2019 => 19.5,
			2020 => 19.8
		);
		$this->valor_uvac = array (
			2015 => 15.00,
			2016 => 15.10,
			2017 => 15.5,
			2018 => 15.7,
			2019 => 15.9,
			2020 => 16.1
		);
	}
	
	public function __destruct () {
		// clean up here
	}
	public function f_inicio () {
		return $this->fecha_inicio;
	}
	public function f_fin () {
		return $this->fecha_fin;
	}
	public function fecha_inicio () {
		$fecha_inicio = utf8_encode(strftime ("%A %e de %B de %Y", strtotime($this->fecha_inicio)));
		return $fecha_inicio;
	}
	
	public function fecha_fin () {
		$fecha_fin = utf8_encode(strftime ("%A %e de %B de %Y", strtotime($this->fecha_fin)));
		return $fecha_fin;
	}
	
	public function cambia_fecha_inicio ($fecha_inicio) { //revisar que el formato sea una fecha
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
	
	public function cambia_fecha_fin ($fecha_fin) { //revisar que el formato sea una fecha
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
	
	public function revisa_fecha ($fecha) {
		$fecha_bien = preg_replace ("/[^0-9\-]/", "", $fecha); //quita caracteres que no sean dígitos o guiones
		if ( preg_match ("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $fecha_bien) ) { //revisa que siga el formato de una fecha
			return $fecha_bien;
		} else {
			return false;
		}
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
		$obj_con = $this->obj_con;
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
	
	protected function filtro_asesor() {
		$filtro_asesor = "";
		if ( !$this->asesor->esAdmin() ){
			$filtro_asesor = "AND entity.smownerid = {$this->asesor->info()["id_bds"]} ";
		}
		return $filtro_asesor;
	}
	
	public function negocios_ingresados ($tipo) {
		/* Función para traer información sobre la prima nueva ingresada a la promotoría, solicitudes en trámite que aún no tienen número de póliza.
		Sólo toma en cuenta la "fecha_fin" del objeto para traer todo lo ingresado hasta x fecha
		La variable tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima ingresada de solicitudes de Vida
		- GMM: Trae sólo la prima ingresada de solicitudes de GMM
		- MetaLife: Trae sólo la prima ingresada de solicitudes de MetaLife
		La función regresa un arreglo con el monto de prima ingresada (en pesos), la suma asegurada (en pesos) y el número de solicitudes ingresadas:
		$negocios_ingresados = array(
			"prima_anual" => ###,
			"suma_asegurada" => ###,
			"solicitudes" => ###
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
			SELECT polcf.cf_855 AS moneda, SUM(polcf.cf_849) AS prima_anual, SUM(polcf.cf_845) AS suma_aseg, COUNT(entity.crmid) AS solicitudes 
			FROM vtiger_servicecontractscf AS polcf
			JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
			JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
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
			GROUP BY polcf.cf_855 ORDER BY polcf.cf_855 DESC /* Lo agrupa por tipo de moneda */
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$negocios_ingresados = array(
				"prima_anual" => 0,
				"suma_asegurada" => 0,
				"solicitudes" => 0
			);
			$anio = date("Y", strtotime($this->fecha_fin));
			$valor_dolar = $this->valor_dolar[$anio];
			foreach( $datos as $fila => $info ) {
				if( preg_match ("/Pesos/i", $info["moneda"] ) ){
					$negocios_ingresados ["prima_anual"] += $info["prima_anual"];
					$negocios_ingresados ["suma_asegurada"] += $info["suma_aseg"];
					$negocios_ingresados ["solicitudes"] += $info["solicitudes"];
				}elseif ( preg_match ("/lares/i", $info["moneda"] ) ) {
					$negocios_ingresados ["prima_anual"] += $info["prima_anual"] * $valor_dolar;
					$negocios_ingresados ["suma_asegurada"] += $info["suma_aseg"] * $valor_dolar;
					$negocios_ingresados ["solicitudes"] += $info["solicitudes"];
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
			"prima_anual" => ###,
			"suma_asegurada" => ###,
			"solicitudes" => ###
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
			SELECT polcf.cf_855 AS moneda, SUM(polcf.cf_849) AS prima_anual, SUM(polcf.cf_845) AS suma_aseg, COUNT(entity.crmid) AS solicitudes 
			FROM vtiger_servicecontractscf AS polcf
			JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
			JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
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
			GROUP BY polcf.cf_855 ORDER BY polcf.cf_855 DESC /* Lo agrupa por tipo de moneda */
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$negocios_emitidos = array(
				"prima_anual" => 0,
				"suma_asegurada" => 0,
				"solicitudes" => 0
			);
			$anio = date("Y", strtotime($this->fecha_fin));
			$valor_dolar = $this->valor_dolar[$anio];
			foreach( $datos as $fila => $info ) {
				if( preg_match ("/Pesos/i", $info["moneda"] ) ){
					$negocios_emitidos ["prima_anual"] += $info["prima_anual"];
					$negocios_emitidos ["suma_asegurada"] += $info["suma_aseg"];
					$negocios_emitidos ["solicitudes"] += $info["solicitudes"];
				}elseif ( preg_match ("/lares/i", $info["moneda"] ) ) {
					$negocios_emitidos ["prima_anual"] += $info["prima_anual"] * $valor_dolar;
					$negocios_emitidos ["suma_asegurada"] += $info["suma_aseg"] * $valor_dolar;
					$negocios_emitidos ["solicitudes"] += $info["solicitudes"];
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
		La función regresa el monto de prima pagada (en pesos)
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
			SELECT SUM(prpa.Prima_Neta) AS prima_pagada_nueva
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
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetch(PDO::FETCH_ASSOC);
			return $datos["prima_pagada_nueva"];
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
		La función regresa el monto de prima pagada (en pesos)
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
			SELECT SUM(prpa.Prima_Neta) AS prima_pagada_renovacion
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
		";
		$obj_con = Sitio::$obj_bds;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetch(PDO::FETCH_ASSOC);
			return $datos["prima_pagada_renovacion"];
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
			"prima_anual" => ###,
			"prima_pagada" => ###,
			"suma_asegurada" => ###,
			"polizas" => ###,
			"polizas_21" => ###
		);
		*/
		$filtros_tipo = "";
		if ( preg_match("/vida/i", $tipo) ) {
			$filtros_tipo = "AND pols.contract_type LIKE '%liza Vida%' ";
		}elseif ( preg_match("/GMM/i", $tipo) ){
			$filtros_tipo = "AND pols.contract_type LIKE '%liza GMM%' ";
		}elseif ( preg_match("/MetaLife/i", $tipo) ){
			$filtros_tipo = "AND polcf.cf_853 LIKE '%MetaLife%' "; //Filtra por el campo de producto
		}
		
		$hoy = date("Y-m-d");
		$txt_qry = "
			SELECT SUM(total_pagado) AS suma_total_pagado, SUM(prima_anual) AS suma_prima_anual, moneda, anio_anualiza,  SUM(suma_aseg) AS suma_suma_aseg, COUNT(poliza) AS suma_polizas, SUM(pol_21) AS pols_21
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
				GROUP BY anio_anualiza, moneda
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$negocios_nuevos_activos = array(
				"prima_anual" => 0,
				"prima_pagada" => 0,
				"suma_asegurada" => 0,
				"polizas" => 0,
				"polizas_21" => 0
			);
			$anio = date("Y", strtotime($this->fecha_fin));
			$valor_dolar = $this->valor_dolar[$anio];
			foreach( $datos as $fila => $info ) {
				if( preg_match ("/Pesos/i", $info["moneda"] ) ){
					$negocios_nuevos_activos ["prima_anual"] += $info["suma_prima_anual"];
					$negocios_nuevos_activos ["prima_pagada"] += $info["suma_total_pagado"];
					$negocios_nuevos_activos ["suma_asegurada"] += $info["suma_suma_aseg"];
					$negocios_nuevos_activos ["polizas"] += $info["suma_polizas"];
					$negocios_nuevos_activos ["polizas_21"] += $info["pols_21"];
				}elseif ( preg_match ("/lares/i", $info["moneda"] ) ) {
					$negocios_nuevos_activos ["prima_anual"] += $info["suma_prima_anual"] * $this->valor_dolar[$info["anio_anualiza"]];
					$negocios_nuevos_activos ["prima_pagada"] += $info["suma_total_pagado"];
					$negocios_nuevos_activos ["suma_asegurada"] += $info["suma_suma_aseg"] * $this->valor_dolar[$info["anio_anualiza"]];
					$negocios_nuevos_activos ["polizas"] += $info["suma_polizas"];
					$negocios_nuevos_activos ["polizas_21"] += $info["pols_21"];
				}
			}
			
			return $negocios_nuevos_activos;
		} else {
			return false;
		}
	}
	
	public function agentes_conectados () {
		/* Función para traer información sobre los agentes conectados en la promotoría, agentes cuya alta ha sido en la fecha determinada
		Toma en cuenta la "fecha_inicio" y la "fecha_fin" del objeto
		La función regresa un arreglo con el número de asesores conectados según el tipo de asesor:
		$agentes_conectados = array(
			"novel" => ###,
			"consolidado" => ###,
			"todos" => ###
		);
		*/
		$txt_qry = "
			SELECT COUNT(opts.potentialid) AS num_asesores, opts.potentialtype AS tipo 
			FROM vtiger_potential AS opts
			JOIN vtiger_potentialscf AS opts_cf ON opts.potentialid = opts_cf.potentialid 
			WHERE opts_cf.cf_923 BETWEEN '{$this->fecha_inicio}' AND '{$this->fecha_fin}'
			AND opts.potentialtype NOT LIKE '%liza%' 
			GROUP BY opts.potentialtype
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			$agentes_conectados = array(
				"novel" => 0,
				"consolidado" => 0,
				"todos" => 0
			);
			foreach( $datos as $fila => $info ) {
				if( preg_match ("/Nuevo/i", $info["tipo"] ) ){
					$agentes_conectados ["novel"] += $info["num_asesores"];
				}else {
					$agentes_conectados ["consolidado"] += $info["num_asesores"];
				}
			}
			$agentes_conectados["todos"] = $agentes_conectados ["novel"] + $agentes_conectados ["consolidado"];
			
			return $agentes_conectados;
		} else {
			return false;
		}
	}
	public function polizas_activas ($tipo) {
		/* Función para traer información sobre las pólizas activas en la promotoría, pólizas emitidas con al menos un pago registrado y que no están canceladas.
		Toma en cuenta la "fecha_inicio" y la "fecha_fin" del objeto
		La variable tipo acepta 3 opciones para filtrar los resultados:
		- Vida: Trae sólo la prima nueva anualizada de solicitudes de Vida
		- GMM: Trae sólo la prima nueva anualizada de solicitudes de GMM
		- MetaLife: Trae sólo la prima nueva anualizada de solicitudes de MetaLife
		La función regresa un arreglo con la información de las pólizas activas, según lo regresa el query:
		$polizas_activas = array(
			array("datos fila_1"),
			array("datos fila_2"),
			array("datos fila_n")
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
			SELECT ramo, tipo, poliza, contratante, rfc_contratante, producto, prima_anual, prima_neta, moneda, fecha_inicio, fecha_anualiza, anio_anualiza, ultimo_pago, ultimo_vigor, total_pagado, cuenta_pagos, estatus, asesor, status_asesor, id_pols, id_crm, id_contacto, id_asesor
			FROM (
					SELECT 
						prpa.Ramo AS ramo, prpa.Poliza AS poliza, MIN(prpa.Dia_de_Pago)  AS fecha_anualiza, MAX(prpa.Dia_de_Pago)  AS ultimo_pago, YEAR(MIN(prpa.Dia_de_Pago))  AS anio_anualiza, SUM(prpa.Prima_Neta) AS total_pagado, COUNT(prpa.Prima_Neta) AS cuenta_pagos, MAX(prpa.Inicio_Vigor) AS ultimo_vigor, 
						polcf.cf_853 AS producto, polcf.cf_849 AS prima_anual, polcf.cf_947 AS prima_neta, polcf.cf_855 AS moneda,
						pols.servicecontractsid AS id_pols, pols.contract_status AS estatus, pols.contract_type AS tipo, pols.start_date AS fecha_inicio, 
						CONCAT (contactos.firstname, ' ', contactos.lastname) AS contratante, contactos.contactid AS id_contacto, contactoscf.cf_829 AS rfc_contratante, 
						entity.crmid AS id_crm,
						asesores.id AS id_asesor, CONCAT(asesores.first_name, ' ', asesores.last_name) AS asesor, asesores.status AS status_asesor 
					FROM finova_prima_pagada AS prpa
						JOIN vtiger_servicecontractscf AS polcf ON prpa.Poliza = polcf.cf_841
						JOIN vtiger_servicecontracts AS pols ON polcf.servicecontractsid = pols.servicecontractsid
						JOIN vtiger_contactdetails AS contactos ON contactos.contactid = pols.sc_related_to 
						JOIN vtiger_contactscf AS contactoscf ON contactoscf.contactid = contactos.contactid
						JOIN vtiger_crmentity AS entity ON pols.servicecontractsid = entity.crmid
						JOIN vtiger_users AS asesores ON asesores.id = entity.smownerid
					WHERE prpa.Dia_de_Pago BETWEEN '2015-01-01' 
						AND '$hoy' 
						$filtros_tipo
						{$this->filtro_asesor()}
						AND entity.deleted = 0
						AND pols.contract_status NOT LIKE '%Cancelada%'
						AND pols.contract_status NOT LIKE '%Complet%'
						AND pols.contract_status NOT LIKE '%Renovada%'
						AND prpa.Tipo_de_Comision NOT LIKE  '%BONO%'
						AND (prpa.Tipo_de_Comision LIKE '%PROM%'
						OR prpa.Tipo_de_Comision LIKE '%MANUAL%')
						AND prpa.Poliza NOT LIKE  ' '
					GROUP BY prpa.Poliza
			) AS tablota
			/*WHERE tablota.fecha_anualiza BETWEEN '{$this->fecha_inicio}' 
				AND '{$this->fecha_fin}' */
				ORDER BY ramo DESC, poliza ASC
		";
		
		$obj_con = $this->obj_con;
		if ( $qry = $obj_con->query($txt_qry) ){
			$datos = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $datos;
		} else {
			return false;
		}
	}
	
}
?>