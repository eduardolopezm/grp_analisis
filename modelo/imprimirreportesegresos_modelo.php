<?php
/**
 * Panel Visualizar Reportes
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Visualizar reportes conac y ldf
 */ 

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2538;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

include $PathPrefix . "includes/SecurityUrl.php";
$enc = new Encryption;

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
$SQL = "";
# opcion a ser ejecutada
$option = $_POST['option'];

/*
/* Encriptar URL
*/
if($option == 'encryptarURL'){
    $url = $_POST['url'];
    $url = $enc->encode($url);
    $liga= "URL=". $url;
    $Mensaje='PrintSituacionEgresos.php?'.$liga;
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje, 'url' => $_POST['url']);
echo json_encode($dataObj);
