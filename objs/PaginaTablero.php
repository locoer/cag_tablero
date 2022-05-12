<?php
class PaginaTablero extends Pagina {
	public function render () {
		$this->defineMenu("menu_lateral");
		$this->definePie("pie_menu_lateral");
		
		echo $this->menu;
		if ( !empty($this->contenido) ) {
			echo $this->contenido;
		}
		echo $this->pie_pagina;
	}
}
?>