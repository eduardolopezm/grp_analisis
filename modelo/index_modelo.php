<?php

//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'modificar_funcion') {
    //Acutalizar
    $SQL = "UPDATE sec_functions_new  
    SET 
    sec_functions_new.title='" . trim($_POST['txtNombreFuncion']) . "', 
    sec_functions_new.shortdescription='" . trim(substr($_POST['txtNombreFuncion'], 0, 29)) . "', 
    sec_functions_new.comments='" . trim($_POST['txtNombreFuncion']) . "', 
    sec_functions_new.submoduleid='" . trim($_POST['txtcapituloid']) . "', 
    sec_functions_new.categoryid='" . trim($_POST['txtcategoria']) . "', 
    sec_functions_new.active='" . trim($_POST['txtactivo']) . "' 
    WHERE sec_functions_new.functionid='" . trim($_POST['txtfunctionid']) . "'";
    $ErrMsg = "No se actualizó la información.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $SQL = "UPDATE sec_functions 
    SET 
    sec_functions.title='" . trim($_POST['txtNombreFuncion']) . "', 
    sec_functions.shortdescription='" . trim(substr($_POST['txtNombreFuncion'], 0, 29)) . "', 
    sec_functions.comments='" . trim($_POST['txtNombreFuncion']) . "', 
    sec_functions.submoduleid='" . trim($_POST['txtcapituloid']) . "', 
    sec_functions.categoryid='" . trim($_POST['txtcategoria']) . "', 
    sec_functions.active='" . trim($_POST['txtactivo']) . "' 
    WHERE sec_functions.functionid='" . trim($_POST['txtfunctionid']) . "'";
    $ErrMsg = "No se actualizó la información.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = $_POST['txtNombreFuncion'];
    $Mensaje = "Datos Actualizados..";
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
