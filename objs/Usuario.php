<?php
class Usuario {
	private $id_bds;
	public $usuario;
	public $nombre;
	private $apellidos;
	private $nombre_completo;
	private $mail;
	private $rol;
	private $posicion;
	private $fecha_alta;
	private $admins;
	
	public function __construct () {
		$this->admins = array (
			5 => "Erick",
			6 => "Jacobo",
			8 => "Brisa",
			17 => "Margarita"
		);
		if ( $this->revisa_login () ) {
			if ( $datos_usr = $this->trae_datos($_SESSION["mail"])) {
				$this->id_bds = $datos_usr["id"];
				$this->usuario = $datos_usr["usuario"];
				$this->nombre = $datos_usr["nombre"];
				$this->apellidos = $datos_usr["apellidos"];
				$this->nombre_completo = $datos_usr["nombre_completo"];
				$this->mail = $datos_usr["mail"];
				$this->rol = $datos_usr["rol"];
				$this->posicion = $datos_usr["posicion"];
			}
		} else {
			foreach ( $this as $var ) {
				$var = false;
			}
		}
	}
	
	public function __destruct() {
		// clean up here
	}
	
	public function info () {
		foreach ( $this as $var => $val ) {
			$info[$var] = $val;
		}
		return $info;
	}
	public function esAdmin () {
		if ( in_array($this->id_bds, array_keys($this->admins)) ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function trae_datos($mail) {
		//$sitio = $GLOBALS["sitio"];
		$obj_con = Sitio::$obj_bds;
		$mail = preg_replace("/[^a-zA-Z0-9.@_\-]/","", $mail); //limpia mail
		if( preg_match("/^[a-z0-9._\-]+@[a-z]+.[a-z.]+/", $mail) ) { //revisa que sea un mail
			$txt_qry = "
				SELECT usuarios.id AS id, usuarios.user_name AS usuario, usuarios.user_password AS clave, usuarios.first_name AS nombre, usuarios.last_name AS apellidos, CONCAT(usuarios.first_name, ' ', usuarios.last_name) AS nombre_completo, usuarios.email1 AS mail, usuarios.title AS posicion, 
				roles.rolename AS rol
				FROM vtiger_users AS usuarios
				JOIN vtiger_user2role AS usr_rol ON usuarios.id = usr_rol.userid 
				JOIN vtiger_role AS roles ON usr_rol.roleid = roles.roleid
				WHERE email1 = :mail
				AND status = 'Active'
			";
			$vals_qry = array (
				":mail" => $mail
			);
			$qry = $obj_con->prepare($txt_qry);
			if ( $qry->execute($vals_qry) ){
				$datos = $qry->fetch(PDO::FETCH_ASSOC);
				return $datos;
			} else {
				return false;
			}
		}
	}
	
	public function revisa_login () {
		if ( isset($_POST["mail"], $_POST["clave"]) ){
				if ( !$login = $this->login($_POST["mail"], $_POST["clave"]) ) { //que haga login
					return false;
				}
		}
		if ( isset($_SESSION["txt_login"], $_SESSION["mail"], $_SESSION["id_bds"]) ) {
			$mail = $_SESSION["mail"];
			if ($datos_usr = $this->trae_datos($mail)) { //trae datos desde la bds según el mail
				$browser = $_SERVER['HTTP_USER_AGENT'];
				$txt_login_bds = $datos_usr["id"] . $browser . $datos_usr["mail"]; //arma el txt_login con la info de la BDs
				if ( $txt_login_bds == $_SESSION["txt_login"] ) { //lo revisa con el text login de la sesión
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function login ($mail, $clave) {
		if ($datos_usr = $this->trae_datos($mail)) { //trae datos desde la bds según el mail
			//hace el cript del psswd
			$salt = substr($datos_usr["usuario"], 0, 2);
			$salt = '$1$' . str_pad($salt, 9, '0');
			$clave_ecriptada = crypt($clave, $salt);
			//lo revisa con la base de datos
			if ( $clave_ecriptada == $datos_usr["clave"] ) {
				//el login es correcto, guarda datos de sesión
				$browser = $_SERVER['HTTP_USER_AGENT'];
				$id_bds = preg_replace("/[^0-9]+/", "", $datos_usr["id"]);
				$mail = preg_replace("/[^a-z0-9._@\-]+/", "", $datos_usr["mail"]);
				$_SESSION["id_bds"] = $id_bds;
				$_SESSION["mail"] = $mail;
				$_SESSION["txt_login"] = $id_bds . $browser . $mail;
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function logout () {
		// Desconfigura todos los valores de sesión.
		if ( isset($_SESSION) ) {
			$_SESSION = array();
		}
		// Obtiene los parámetros de sesión.
		$params = session_get_cookie_params();
		// Borra el cookie actual.
		setcookie(session_name(),
				'', time() - 42000, 
				$params["path"], 
				$params["domain"], 
				$params["secure"], 
				$params["httponly"]);
		 
		// Destruye sesión. 
		session_destroy();
		//redirecciona al index del sitio
		header ('Location: ' . DOMINIO );
	}
}

?>