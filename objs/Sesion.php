<?php
class Sesion {
	private $nombre;
	
	public function __construct($nombre) {
		$this->nombre = $nombre;
	}

	public function __destruct() {
		// clean up here
	}
	public function inicio_seguro () {
		$session_name = $this->nombre;   // Configura un nombre de sesión personalizado.
		$secure = false;
		// Esto detiene que JavaScript sea capaz de acceder a la identificación de la sesión.
		$httponly = true;
		// Obliga a las sesiones a solo utilizar cookies.
		if (ini_set('session.use_only_cookies', 1) === FALSE) {
			//header ( 'Location: ' . $dominio . '/error.php?err=Could not initiate a safe session (ini_set)' );
			exit ("No se pudo iniciar una sesión segura, contacte a su proveedor de hospedaje");
		}
		
		// Obtiene los params de los cookies actuales.
		$cookieParams = session_get_cookie_params ();
		session_set_cookie_params (
			$cookieParams["lifetime"],
			$cookieParams["path"], 
			$cookieParams["domain"], 
			$secure,
			$httponly
		);
		// Configura el nombre de sesión al configurado arriba.
		session_name ($session_name);
		session_start ();            // Inicia la sesión PHP.
		session_regenerate_id ();    // Regenera la sesión, borra la previa. 
	}
	public function termina_sesion () { //Usar para el logout
		// Desconfigura todos los valores de sesión.
		if ( isset($_SESSION) ) {
			$_SESSION = array();
		}
		// Obtiene los parámetros de sesión.
		$params = session_get_cookie_params();
		// Borra el cookie actual.
		setcookie(
			session_name(),
			'', 
			time() - 42000, 
			$params["path"], 
			$params["domain"], 
			$params["secure"], 
			$params["httponly"]
		);
		 
		// Destruye sesión. 
		session_destroy();
	}
}
?>