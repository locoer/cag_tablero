<?php
class Sitio {
	private $nombre;
	private $headers;
	private $footers;
	private $pagina;
	static public $obj_bds;
	private $usuario;
	private $sesion;

	public function __construct() {
		$this->nombre = NOMBRE_SITIO;
		$sesion = new Sesion($this->nombre);
		$sesion->inicio_seguro ();
		self::$obj_bds = self::conecta_bds();
		$usuario = new Usuario();
		$this->defineUsuario($usuario);
		$this->headers = ["./vistas/header.php"];
		$this->footers = ["./vistas/footer.php"];
	}

	public function __destruct() {
		// clean up here
	}
	
	public static function conecta_bds () {
		$host = HOST . PUERTO;
		$dbname = DBNAME;
		$usr = USR;
		$psswd = PSSWD;
		$bds = new PDO("mysql:host=$host;dbname=$dbname", $usr, $psswd);
		$bds->exec("SET NAMES utf8");
		return $bds;
	}

	public function render() {
		foreach($this->headers as $header) {
			include $header;
		}
		if ( !empty($this->pagina) ) {
			$this->pagina->render();
		}

		foreach($this->footers as $footer) {
			include $footer;
		}
	}
	
	public function agregaHeader($vista) {
		$this->headers[] = "./vistas/$vista.php";
	}

	public function agregaFooter($vista) {
		array_unshift($this->footers, "./vistas/$vista.php");
	}

	public function definePagina(Pagina $pagina) {
		$this->pagina = $pagina;
	}
	
	public function defineUsuario (Usuario $usuario) {
		$this->usuario = $usuario;
	}
	
	public function defineVistaURL () {
		if ( !$this->usuario->nombre ) { //revisa si hay usuario logeado
			//No hay usuario, manda a login
			$pagina = new PaginaLimpia("COHEN Agentes Globales");
			$pagina->defineContenido("login");
		} else {
			if ( isset($_GET["vista"]) ) { //revisa vista del url
				$vista = htmlspecialchars($_GET["vista"]);
				$vista = explode("/", $vista);
				switch ($vista[0]) { //hace el switch según la vista y escoge el tipo de página
					case "login":
						$this->usuario->logout();
						$pagina = new PaginaLimpia("COHEN Agentes Globales");
						$pagina->defineContenido("login");
						break;
					case "logout":
						$this->usuario->logout();
						break;
					default:
						$pagina = new PaginaTablero("Tablero COHEN Agentes Globales");
						$pagina->defineContenido($vista[0]);
						break;
				}
			} else { //Si no hay vista, pero sí está logeado, escoge pagina tablero y vista inicio
				$pagina = new PaginaTablero("Tablero COHEN Agentes Globales");
				$pagina->defineContenido("inicio");
			}
		}
		$this->definePagina($pagina);
	}
	
	public function usuario_activo () {
		if ( $this->usuario->nombre ) {
			return $this->usuario;
		} else {
			return false;
		}
	}
}
?>