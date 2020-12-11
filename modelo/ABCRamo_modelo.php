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
    $SQL = "SELECT id_ramo, cve_ramo, desc_ramo FROM g_cat_ramo ".$sqlUR." ORDER BY cve_ramo asc";
    $ErrMsg = "No se obtuvieron los Ramos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'Clave' => $myrow ['cve_ramo'], 'Descripcion' => $myrow ['desc_ramo'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_ramo'].')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_ramo'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datosCatalogo' => $info, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $claveId = $_POST['claveId'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE g_cat_ramo SET desc_ramo = '$descripcion', cve_ramo = '$clave', active = 1 WHERE id_ramo = '$claveId'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo ".$nombreCatalogo." con éxito";
        $result = true;
    } else {
        $SQL = "SELECT id_ramo, cve_ramo, desc_ramo, active FROM g_cat_ramo WHERE cve_ramo = '$clave'";
        $ErrMsg = "No se obtuvieron los Ramos";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO g_cat_ramo (`cve_ramo`, `desc_ramo`, `active`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
            $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$clave." del Catálogo ".$nombreCatalogo." con éxito";
            $result = true;
        } else {
            $active = "";
            while ($myrow = DB_fetch_array($TransResult)) {
                $active = $myrow['active'];
            }
            if ($active != 1) {
                $SQL = "UPDATE g_cat_ramo SET desc_ramo = '$descripcion', active = 1 WHERE cve_ramo = '$clave'";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$clave." del Catálogo ".$nombreCatalogo." con éxito";
                $result = true;
            } else {
                $contenido = "El registro ".$clave." del Catálogo ".$nombreCatalogo." ya existe";
                $result = false;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $clave = $_POST['claveId'];

    $nombre = "";
    $result = false;
    $SQL = "SELECT `cve_ramo` FROM `g_cat_ramo` WHERE `id_ramo` = '$clave'";
    $ErrMsg = "No se obtuvieron los Ramos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $nombre = $myrow['cve_ramo'];
    }

    if($nombre){
        $SQL = "SELECT `ramo` FROM `g_cat_ppi` WHERE `ramo` = '$nombre' AND `activo` = 'S'";
        $ErrMsg = "No se obtuvieron los Ramos.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if(DB_num_rows($TransResult)){
            $contenido = "No puede eliminarse el Ramo ".$nombre." porque está siendo usado en el Catálogo Programa Proyecto de Inversión.";
        }else{
            $info = array();
            $SQL = "UPDATE `g_cat_ramo` SET `active` = 0 WHERE `id_ramo` = '$clave'";
            $ErrMsg = "No se eliminó la informacion de ".$nombre;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se eliminó el registro ".$nombre." del Catálogo ".$nombreCatalogo." con éxito";
            $result = true;
        }
    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
