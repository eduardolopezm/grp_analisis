<?php
function cargaControlador($controller){
	$controlador = ucwords($controller) . 'Controller';
	$strFileController = 'controller/' . $controlador . '.php';

	if(!is_file($strFileController)){
		$strFileController = 'controller/' . ucwords(CONTROLADOR_DEFECTO) . 'Controller.php';
	}

	require_once $strFIleController;
	$controllerObj = new $controlador();
	return $controllerObj;
}

function cargarAccion($controllerObj, $action){
	$accion = $action;
	$controllerObj->$accion();
}

function lanzarAccion($controllerObj){	
	if(isset($_GET["action"]) && method_exist($controllerObj, $_GET["action"])){
		cargarAccion($controllerObj, $_GET["action"]);
	}else{
		cargarAccion($controllerObj, ACCION_DEFECTO);
	}

}

?>