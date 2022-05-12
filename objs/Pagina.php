<?php
abstract class Pagina {
	protected $titulo;
	protected $menu;
	protected $encabezado;
	protected $contenido;
	protected $pie_pagina;

	public function __construct($titulo) {
		$this->titulo = $titulo;
	}

	public function __destruct() {
		// clean up here
	}
	
	protected function trae_vista ($vista) {
		if ( !file_exists("./vistas/$vista.php") ) {
			return false;
		}
		ob_start();
		include_once "./vistas/$vista.php";
		$cont = ob_get_contents();
		ob_end_clean();
		$this->titulo .= " - $vista";
		return $cont;
	}

	public function render() {
		echo $this->menu;
		echo $this->encabezado;
		echo $this->contenido;
		echo $this->pie_pagina;
	}
	
	public function titulo () {
		echo $this->titulo;
	}
	
	public function defineMenu ($menu) {
		if ($menu = $this->trae_vista($menu)) {
			$this->menu = $menu;
		} else {
			$this->menu = ""; //menu default
		}
	}

	public function defineEncabezado ($encabezado) {
		if ($encabezado = $this->trae_vista($encabezado)) {
			$this->encabezado = $encabezado;
		} else {
			$this->encabezado = ""; //encabezado default
		}
	}
	
	public function defineContenido($contenido) {
		if ($cont = $this->trae_vista($contenido)) {
			$this->contenido = $cont;
		} else {
			$cont = $this->trae_vista("inicio"); //contenido default
			$this->contenido = $cont;
		}
	}
	
	public function definePie($pie_pagina) {
		if ($pie_pagina = $this->trae_vista($pie_pagina)) {
			$this->pie_pagina = $pie_pagina;
		} else {
			$this->pie_pagina = ""; //pie de pagina default
		}
	}
}
?>