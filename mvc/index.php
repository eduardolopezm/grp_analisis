<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
require_once 'config/global.php';
require_once 'core/ControladorBase.php';
require_once 'core/ControladorFrontal.func.php';

if(isset($_GET["controller"])){
	$controllerObj = cargarControlador($_GET["controller"]);
}else{
	$controllerObj = cargarControlador(CONTROLADOR_DEFECTO);
}

lanzarAccion($controllerObj);
*/


require_once 'dao/Custbranchdao.php';
require_once 'model/Custbranch.php';


$objeto = new Custbranchdao();
$objcust = new Custbranch();
//$objeto->Custbranchbyid('00720','admin');

$json = json_decode($objeto->Custbranchbyid('00720','admin'), true);

//echo '<pre>' . print_r($obj, true) . '</pre>';

$objcust = unserialize($json['data']['object']);
var_dump($objcust);
//echo "<br>NAMESS: " . $objcust->getBrname();


?>