<?php

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2242;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

$ErrMsg = _( '' );
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
$TransResult = DB_query ( $SQL, $db );

$opcion = $_POST['opcion'];


switch ($option) {
	case 'cargarDatosDesdeBD':
		
	

    $info = array();
    $SQL = "SELECT ctga,descripcion FROM g_cat_tipo_de_gasto  ORDER BY ctga asc";
    $ErrMsg = "No se obtuvieron elementos geograficos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
     $ErrMsg = "No se obtuvieron elementos geograficos";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	 $info[] = array( 'ctga' => $myrow ['ctga'], 'Descripcion' => $myrow ['descripcion']);
    }

    $contenido = array('datosCatalogo' => $info);
    $result = true;

		break;

	
	default:
		# code...
		break;
}



$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>