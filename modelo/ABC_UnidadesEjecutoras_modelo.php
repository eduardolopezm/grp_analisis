<?php
/**
 * Modelo para el ABC de Unidades Ejecutoras
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 01/08/2017
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
$funcion=2244;
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

$nombreCatalogo = "Auxiliar 1";

if ($option == 'mostrarCatalogo') {
    $sqlUR = " WHERE active = 1 ";
    // sec_unegsxuser.userid = '".$_SESSION['UserID']."' AND

    if (!empty($_POST['ue'])) {
        $sqlUR = " WHERE id = '".$_POST['ue']."' ";
    }

    $info = array();
    // $SQL = "SELECT id, ur, ue, cg, desc_ue, ln_aux1
    //     FROM tb_cat_unidades_ejecutoras
    //     JOIN tags ON tags.tagref = tb_cat_unidades_ejecutoras.ur
    //     JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
    //     ".$sqlUR." ORDER BY ur, ue asc";
    $SQL = "SELECT id, ur, ue, cg, desc_ue, ln_aux1 
        FROM tb_cat_unidades_ejecutoras
        ".$sqlUR." ORDER BY ln_aux1 asc";
    $ErrMsg = "No se obtuvieron las unidades ejecutoras";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'UR' => $myrow ['ur'],
            'UE' => $myrow ['ue'],
            'Estado' => $myrow ['cg'],
            'Auxiliar1' => $myrow ['ln_aux1'],
            'Descripcion' => $myrow ['desc_ue'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id'].')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
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
    $ur = $_POST['ur'];
    $estado = $_POST['estado'];
    $ln_aux1 = $ur.$clave;

    if ($proceso == 'Modificar') {
        $SQL = "SELECT id, ue, desc_ue FROM tb_cat_unidades_ejecutoras WHERE ln_aux1 = '$ln_aux1' and id != '$claveId'";
        $ErrMsg = "No se obtuvieron las Unidades Ejecutoras";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "UPDATE tb_cat_unidades_ejecutoras 
                SET desc_ue = '$descripcion', 
                ue = '$clave', ur = '$ur', 
                cg = '$estado',
                ln_aux1 = '$ln_aux1', 
                active = 1 WHERE id = '$claveId'";
            $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se modificó el registro ".$ln_aux1." del Catálogo ".$nombreCatalogo." con éxito";
            $result = true;
        } else {
            $contenido = "El registro ".$ln_aux1." del Catálogo ".$nombreCatalogo." ya existe";
            $result = false;
        }
    } else {
        $SQL = "SELECT id, ue, desc_ue, active FROM tb_cat_unidades_ejecutoras WHERE ur = '".$ur."' AND ue = '".$clave."'";
        $ErrMsg = "No se obtuvieron las Unidades Ejecutoras";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO tb_cat_unidades_ejecutoras (`ue`, `desc_ue`, `active`, `ur`, `cg`, `ln_aux1`)
		            VALUES ('".$clave."', '".$descripcion."', '1', '".$ur."', '".$estado."', '".$ln_aux1."')";
            $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$ln_aux1." del Catálogo ".$nombreCatalogo." con éxito";
            $result = true;
        } else {
            $active = "";
            while ($myrow = DB_fetch_array($TransResult)) {
                $active = $myrow['active'];
            }
            //echo $active;
            if ($active == 0 ) {
                $SQL = "UPDATE tb_cat_unidades_ejecutoras SET active = 1, cg = '', desc_ue = '".$descripcion."'
                WHERE ur = '".$ur."' AND ue = '".$clave."'";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$ln_aux1." del Catálogo ".$nombreCatalogo." con éxito";
                $result = true;
            } else {
                $contenido = "El registro ".$ln_aux1." del Catálogo ".$nombreCatalogo." ya existe";
                $result = false;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $clave = $_POST['claveId'];

    $ln_aux1 = "";
    $SQL = "SELECT ln_aux1 FROM tb_cat_unidades_ejecutoras WHERE id = '$clave'";
    $ErrMsg = "No se obtuvieron las Unidades Ejecutoras";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $ln_aux1 = $myrow['ln_aux1'];
    }

    $info = array();
    $SQL = "UPDATE tb_cat_unidades_ejecutoras SET active = 0 WHERE id = '$clave'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$ln_aux1." del Catálogo ".$nombreCatalogo." con éxito";
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
