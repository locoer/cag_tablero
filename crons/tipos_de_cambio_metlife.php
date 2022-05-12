<?php
include_once "../config.php";
//$bds = DBNAME;

$sitio_metlife_tc = "https://www.metlife.com.mx/soy-cliente/tipo-de-cambio/";

$pg_metlife_tc = file_get_contents($sitio_metlife_tc);
$regex = "/[A-Za-zó*]*:\s?\s?\\$\d*\.\d*/";

$meses = array(
	"01" => "Enero",
	"02" => "Febrero",
	"03" => "Marzo",
	"04" => "Abril",
	"05" => "Mayo",
	"06" => "Junio",
	"07" => "Julio",
	"08" => "Agosto",
	"09" => "Septiembre",
	"10" => "Octubre",
	"11" => "Noviembre",
	"12" => "Diciembre",
);

if (!$pg_metlife_tc) {
	echo "Hubo un problema al cargar el sitio $sitio_metlife_tc";
	exit;
} 

preg_match_all($regex, $pg_metlife_tc, $matches);

//$regex2 = "/\<[a-z]*\s*[a-z]*\=\"page-title__subtitle\"\s*[a-z=\"\d]*><p>[\w\sáéíóú:\/]*([0-9]{2})\s([\w\sáéíóú\s]*)\s([0-9]{4})<\/[a-z]*>/";
$regex2 = "/\:[áéíóú\s*\w]*([0-9]{2})([\s\w]*)([0-9]{4})/";
preg_match($regex2, $pg_metlife_tc, $matches_fecha);

if ( count($matches[0]) ) {
	//var_dump($matches);
	$tipos_de_cambio_met = $matches[0];
	$tipos_de_cambio = array();
	
	foreach( $tipos_de_cambio_met as $tc ) {
		if ( preg_match("/Dólar/i", $tc) ) {
			$valor = preg_replace("/[^0-9\.]/", "", $tc);
			$tipos_de_cambio["Dolar"] = $valor;
			echo "El valor del dólar es: $valor<br/>";
		}elseif( preg_match("/UVAC/i", $tc) ) {
			$valor = preg_replace("/[^0-9\.]/", "", $tc);
			$tipos_de_cambio["UVAC"] = $valor;
			echo "El valor del UVAC es: $valor<br/>";
		}
	}
} else {
	echo "No se encontraron matcheses";
	exit;
}

if ( count($matches_fecha) ) {
	//var_dump($matches_fecha);
	$fecha_tc = $matches_fecha[3] . "-";
	foreach( $meses as $num => $mes ) {
		if ( preg_match( "/$mes/", $matches_fecha[2]) ) {
			$fecha_tc .= $num . "-";
		}
	}
	$fecha_tc .= $matches_fecha[1];
	//print_r ($fecha_tc);
}

if( isset($fecha_tc) && preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $fecha_tc) ) {
	//echo "La fecha para la BDs es: $fecha_tc<br>";
}

$mensaje = "
	<p>Se ha actualizado la base de datos de tipos de cambio con los siguientes valores</p>
	<ul>
		<li>Fecha: $fecha_tc</li>
		<li>Dólar: \${$tipos_de_cambio["Dolar"]}</li>
		<li>UVAC: \${$tipos_de_cambio["UVAC"]}</li>
	</ul>
	<p>¡Besos!</p>
";
$headers = "From: operaciones@cohenag.com\r\nContent-type: text/html\r\n";
//mail("erick@cohenag.com", "Tipo de cambio MetLife", $mensaje, $headers);

print_r($mensaje);
?>