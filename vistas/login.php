<?php
if (!isset($_GET["vista"])) {
	$_GET["vista"] = "inicio";
} 
?>
<div class="text-center">
	<form class="form-signin" action="<?php echo DOMINIO . "{$_GET["vista"]}/"; ?>" method="post">
		<img class="mb-4" src="<?php echo DOMINIO; ?>/imgs/COHEN_AG_logo.png" alt="" width="250"/>
		<!--<h1 class="h3 mb-3 font-weight-normal">Favor de ingresar sus datos</h1>-->
		<label for="inputEmail" class="sr-only">Correo Electrónico</label>
		<input type="email" id="inputEmail" name="mail" class="form-control" placeholder="Correo Electrónico" required="" autofocus=""/>
		<label for="inputPassword" class="sr-only">Contraseña</label>
		<input type="password" id="inputPassword" name="clave" class="form-control" placeholder="Contraseña" required=""/>
		<!--<div class="checkbox mb-3">
			<label>
				<input type="checkbox" value="remember-me"/> Recuérdame.
			</label>
		</div>-->
		<input class="btn btn-lg btn-primary btn-block" type="submit" value="Ingresar">
		<p class="mt-5 mb-3 text-muted">©COHEN Agentes Globales 2016-<?php echo date("Y"); ?></p>
	</form>
</div>
<style>
html,
body {
  height: 100%;
}

body {
  display: -ms-flexbox;
  display: -webkit-box;
  display: flex;
  -ms-flex-align: center;
  -ms-flex-pack: center;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-pack: center;
  justify-content: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
}

.form-signin {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .checkbox {
  font-weight: 400;
}
.form-signin .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>
