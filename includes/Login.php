<?php
/**
 * Pagina de inicio de aplicacion
 *
 * @category Inicio
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link (target, link)
 * Fecha Creaci�n: 01/08/2017
 * Fecha Modificaci�n: 21/08/2017
 */

include ('LanguageSetup.php');

if ($allow_demo_mode == True AND !isset($demo_text)) {
	$demo_text = _('usuario') .': <i>' . _('admin') . '</i><BR>' ._('contrase&ntilde;a') . ': <i>' . _('GRP') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Favor de firmarse aqui');
}
?>

<html>
<head>
	<title>GRP Seguridad</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo _('ISO-8859-1'); ?>" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />

	<link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
</head>

<body>

<?php
if (get_magic_quotes_gpc()){
	echo '<p style="background:white">';
	echo _('Su servidor web esta configurado para habilitar Magic Quotes. Esto puede causar problemas si usted utiliza puntuacion (como comillas) al capturar datos. Favor de contactar al administrador del sistema para des-habilitar Magic Quotes');
	echo '</p>';
}
?>

<div id="container">
	<div id="login_logo"></div>
	<div id="login_box">
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post" autocomplete="off">
	<div class="container-fluid">
		<div class="form-group">
			<label for="exampleInputEmail1">Empresa:</label>
			<?php
			if(!isset($_COOKIE['defaultempresa'])){
				$getdir=explode('.',($_SERVER['HTTP_HOST']));
				$DefaultCompanyGET=$getdir[0];
			} else {
				$DefaultCompanyGET=$_COOKIE['defaultempresa'];
			}

			if (strpos($_SERVER['REQUEST_URI'], "CAPA")) {
				echo '  <input type="text" class="form-control" size=20 name="CompanyNameField" value="ap_grp_CAPA" onkeypress="return fnEnterIniciarSesion(event)">';
			} else {
				echo '  <input type="text" class="form-control" size=20 name="CompanyNameField" value="ap_grp" onkeypress="return fnEnterIniciarSesion(event)">';
			}
			?>
		</div>

		<div class="form-group">
			<label for="exampleInputEmail1">Ejercicio Fiscal:</label>
			<input type="TEXT" class="form-control" name="txtEjercioFiscal" id="txtEjercioFiscal" autocomplete="off" maxlength="4" value="<?php echo date('Y'); ?>" onkeypress="return fnEnterIniciarSesion(event)" />
		</div>

		<div class="form-group">
			<label for="exampleInputEmail1">Nombre Usuario:</label>
			<input type="TEXT" class="form-control" name="UserNameEntryField" autocomplete="off" onkeypress="return fnEnterIniciarSesion(event)" />
		</div>

		<div class="form-group">
			<label for="exampleInputEmail1">Contrase&ntilde;a:</label>
			<input type="PASSWORD" class="form-control" name="Password" autocomplete="off" onkeypress="return fnEnterIniciarSesion(event)" />
		</div>

		<?php
		$recquest=explode ( '/' , $_SERVER['REQUEST_URI']);
		$URL= $url="http://".$_SERVER['HTTP_HOST']."/".$recquest[1]."/includes/Z_ConexionManualLogin.php?SID";
		?>

		<div id="demo_text">
			<p align="center"><?php echo $demo_text;?></p>
			<div id="mensajeNavegadorEjercicio" name="mensajeNavegadorEjercicio" style="color: red; display: none;">
				<h5>El Ejercicio Fiscal debe ser en formato yyyy</h5>
			</div>
			<div id="mensajeNavegadorActualizar" name="mensajeNavegadorActualizar" style="color: red; display: none;">
				<h5>Actualizar el Navegador para poder Iniciar Sesi&oacuten</h5>
			</div>
			<div id="mensajeNavegadorVersion" name="mensajeNavegadorVersion" style="color: red; display: none;">
				<h5>Para Iniciar Sesi&oacuten debe usar los navegadores: </h5>
				<p>* Firefox Versi&oacuten 50 y/o superior</p>
				<p>* Opera Versi&oacuten 50 y/o superior</p>
				<p>* Safari Versi&oacuten 11 y/o superior</p>
				<p>* Chrome Versi&oacuten 60 y/o superior</p>
			</div>
		</div>
		<div id="divBotonEntrar" name="divBotonEntrar" align="center">
			<button type="button" id="SubmitUser" name="SubmitUser" class="btn btn-default" onclick="fnValidarEjercicioFiscal()" value="<?php echo _('Ingresar'); ?>">&nbsp;Ingresar</button>
		</div>
	</div>

	<script type="text/javascript">
		if(typeof(Storage)!=="undefined"){
			if(typeof(window.localStorage.uePorUsuario)!=="undefined"){
				delete window.localStorage.uePorUsuario;
			}
		}

		function fnEnterIniciarSesion(e) {
			var key=e.keyCode || e.which;
			if (key==13){
				fnValidarEjercicioFiscal();
			}
		}
	</script>
	
	<br>
	<?php //echo "<div align='center' ><a style = ' font-size:10px; color:#06A100;' href='javascript:openNewWindowBig(\"".$URL."\");' title='Restablecimiento de Contrase&ntilde;a'> No puedes Acceder?</a></div>"; ?>
	</form>
	</div>
</div>
	<script language="JavaScript" type="text/javascript">
	//<![CDATA[
			<!--
				document.loginform.UserNameEntryField.focus();
			//-->
	//]]>
	</script>
</body>
</html>

<script>
	var ventana = null;
	var ventanaBig = null;

	function openNewWindow(url){
		if (ventana==null || ventana.closed)
			ventana = window.open(url,'','width=450,height=250'); // 500 y 200
		else
			alert('Esta funci�n ya se esta ejecutando, favor de cerrar la ventana antes de abrir otra');
	}

	function openNewWindowBig(url){
		if (ventanaBig==null || ventanaBig.closed)
			ventana = window.open(url,'','width=450,height=250'); 
		else
			alert('Esta funci�n ya se esta ejecutando, favor de cerrar la ventana antes de abrir otra');	
	}

	function fnValidarEjercicioFiscal() {
		var txtEjercioFiscal = document.getElementById("txtEjercioFiscal");
		if (txtEjercioFiscal.value.trim().length == 4) {
			var btn=document.getElementById('SubmitUser');
			btn.setAttribute('type', 'submit');
			btn.click();
		} else {
			document.getElementById('mensajeNavegadorEjercicio').style.display = 'inline';
		}
	}

	function fnObtenerVersionNavegador () {
		var ua= navigator.userAgent, tem,
		M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
		if(/trident/i.test(M[1])){
			tem = /\brv[ :]+(\d+)/g.exec(ua) || [];
			return 'IE '+(tem[1] || '');
		}
		if(M[1]=== 'Chrome'){
			tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
			if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
		}
		M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
		if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
		return M.join(' ');
	}

	var versionNavegador = fnObtenerVersionNavegador();
	var infoNavegador = versionNavegador.split(" ");

	var ocultaBoton = 0;
	if (infoNavegador[0] == 'Firefox' && Number(infoNavegador[1]) < Number(50)) {
		// alert("Tiene Firefox");
		ocultaBoton = 1;
	}else if (infoNavegador[0] == 'Opera' && Number(infoNavegador[1]) < Number(50)) {
		// alert("Tiene Opera");
		ocultaBoton = 1;
	}else if (infoNavegador[0] == 'Safari' && Number(infoNavegador[1]) < Number(11)) {
		// alert("Tiene Safari");
		ocultaBoton = 1;
	}else if (infoNavegador[0] == 'Chrome' && Number(infoNavegador[1]) < Number(60)) {
		// alert("Tiene Chrome");
		ocultaBoton = 1;
	}

	if (ocultaBoton == '1') {
		var msjValNavegador = '<h4>Actualizar el Navegador para poder inciar Sesi�n</h4>';
		document.getElementById('divBotonEntrar').style.display = 'none';

		var btn=document.getElementById('SubmitUser');
		btn.setAttribute('type', 'button');

		document.getElementById('mensajeNavegadorActualizar').style.display = 'inline';
	}

	if (infoNavegador[0] != 'Firefox' && infoNavegador[0] != 'Opera' && infoNavegador[0] != 'Safari' && infoNavegador[0] != 'Chrome') {
		document.getElementById('divBotonEntrar').style.display = 'none';

		var btn=document.getElementById('SubmitUser');
		btn.setAttribute('type', 'submit');

		document.getElementById('mensajeNavegadorVersion').style.display = 'inline';
	}

	// Tipo boton para validar ejercicio fiscal
	var btn=document.getElementById('SubmitUser');
	btn.setAttribute('type', 'button');
</script>
