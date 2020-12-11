<?php
/**
 * Modelo para el ABC de Ramo
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/07/2017
 * Fecha Modificación: 31/07/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */
 
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
$funcion=2246;
include($PathPrefix.'includes/SecurityFunctions.inc');
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

$nombreCatalogo = "Ramo";

if ($option == 'mostrarCatalogo') {
    $sqlUR = " WHERE active = 1 ";

    if (!empty($_POST['desc_ramo'])) {
        $sqlUR = " WHERE id_ramo = '".$_POST['desc_ramo']."' ";
    }

    $info = array();
    $SQL = "SELECT
    config.confname as cve_ramo,
    config.confcomentarios as desc_ramo,
    config.confvalue as valor
    FROM config
    WHERE
    config.nu_visual_config = 1";
    $ErrMsg = "No se obtuvieron los Ramos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 
            'Clave' => $myrow ['valor'], 
            'Descripcion' => $myrow ['desc_ramo'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['cve_ramo'].'\', \''.$myrow ['desc_ramo'].'\', \''.$myrow ['valor'].'\')"><span class="glyphicon glyphicon-edit"></span></a>'
        );
    }

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datosCatalogo' => $info, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $claveId = $_POST['claveId'];
    $valor = $_POST['valor'];
    $txtDesc = $_POST['txtDesc'];

    $info = array();
    $SQL = "UPDATE config SET confvalue = '$valor' WHERE confname = '$claveId'";
    $ErrMsg = "No se agrego la configuración de ".$claveId;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $Mensaje = "Se modificó el registro ".$txtDesc." con éxito";
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
