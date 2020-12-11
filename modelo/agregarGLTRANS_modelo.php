<?php

ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);
session_start();
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
//header('Content-type: text/html; charset=ISO-8859-1');

include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//
if ($abajo) {
include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion = 2261;
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


function fnGrupoYOrden ($aReporte, $aParametro) 
{
   // $grupoyorden = {"grupo", "orden"};
    return $grupoyorden;
}

function fngrabarReportesConfiguracion($aReporte, $aParametro, $aValorArray, $db, &$SQL, &$ErrMsg, &$TransResult, &$contenido)
{ 
    
    $aValor = implode(',', $aValorArray);
    $tagref = $_POST['tagref'];

//        $grupoyorden = fnGrupoYOrden($aReporte, $aParametro);

//        print_r($grupoyorden);
    
    
    
        $valoresAInsertar ="";
        foreach ($aValorArray as $key => $value) {
             $valoresAInsertar .= "('".$aReporte."', '".$aParametro."', '".$value."', '', '', {$tagref}),";
        }

        $valoresAInsertar = substr($valoresAInsertar, 0, -1).";";
       
        $info = array();
            $SQL = "INSERT INTO gltrans (`trandate`, `account`, `amount`, loccode, stockid, tag)
                    VALUES {$valoresAInsertar}";

            //echo $SQL;
            $ErrMsg = "No se agrego la información de ".$aReporte." - ".$aParametro;
            

            $TransResult = DB_query ( $SQL, $db, $ErrMsg );

            $contenido = "Se agrego la información de ".$aReporte." - ".$aParametro;

            
            $result = true;

}

function fnActualzarRegistro($aValorArray, $db, &$SQL, &$ErrMsg, &$TransResult, &$contenido)
{ 
    
    $aValor = $aValorArray["amount"];
    $aRowIndex  = $aValorArray["counterindex"];
    
    
    
            $SQL = "UPDATE  gltrans set amount = {$aValor} WHERE counterindex = {$aRowIndex} ";

            //echo $SQL;
            $ErrMsg = "No se actualizó la información de ".$aRowIndex;
            

            $TransResult = DB_query ( $SQL, $db, $ErrMsg );

            $contenido = "Se actualizó la información de ".$aRowIndex;

            
            $result = true;

}

function fnRandomFecha($aYear) {

//Start point of our date range.
$start = strtotime("1 January ".$aYear);
 
//End point of our date range.
$end = strtotime("31 December ".$aYear);
 
//Custom range.
$timestamp = mt_rand($start, $end);
 
//Print it out.
return date("Y-m-d", $timestamp);
}

function fnRandomValue()
{
    $a = '';
for ($i = 0; $i<4; $i++) 
{
    $a .= mt_rand(0,9);
}
return $a;
}


if (isset($_POST['option'])) {
    if ($_POST['option'] =='agregar') {

        $arregloAInsertar = [];
        $arregloAInsertar[]  = (fnRandomValue());
        $arregloAInsertar[]  = (fnRandomValue());
        $arregloAInsertar[]  = (fnRandomValue());

        fngrabarReportesConfiguracion(fnRandomFecha("2017"), $_POST['cuentaContable'], $arregloAInsertar, $db, $SQL, $ErrMsg, $TransResult, $contenido);


        $arregloAInsertar = [];
         $arregloAInsertar[]  = (fnRandomValue());
         $arregloAInsertar[]  = (fnRandomValue());
         $arregloAInsertar[]  = (fnRandomValue());

        fngrabarReportesConfiguracion(fnRandomFecha("2016"), $_POST['cuentaContable'], $arregloAInsertar, $db, $SQL, $ErrMsg, $TransResult, $contenido);
    }

    if ($_POST['option'] =='actualizar') {
        fnActualzarRegistro($_POST["row"], $db, $SQL, $ErrMsg, $TransResult, $contenido);
    }

}

if (isset($_GET['option'])) {

    if ($_GET['option'] =='cargagrid') {

        $info = array();
        $tagref = $_GET["tagref"];
        $account = $_GET["account"];
        $SQL = "select counterindex, amount, account, trandate from gltrans where tag = '{$tagref}' and account = '$account' ";
        $ErrMsg = "No se obtuvieron las objetos de gasto";
        $TransResult = DB_query ( $SQL, $db, $ErrMsg );
        while ( $myrow = DB_fetch_array ( $TransResult ) ) {

         
                $info[] = array( 'counterindex' => $myrow ['counterindex'], 
                    'amount' => $myrow ['amount'],
                    'account' => $myrow ['account'],
                    'trandate' => $myrow ['trandate']);
        }
        

        $contenido = array('datosCatalogo' => $info);
        $result = true;
        
    }
}
//if (isset($_GET['option']))
//    if ($_GET['option']=='cargagrid') exit;

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);





?>