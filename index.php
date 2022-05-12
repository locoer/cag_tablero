<?php
ob_start();//ob_start("ob_gzhandler");
function __autoload($class) {
	include "./objs/$class.php";
}
include_once "./config.php";

$sitio = new Sitio;
$sitio->defineVistaURL();
$sitio->render();

ob_end_flush();

?>