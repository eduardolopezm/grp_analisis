<?php
/**
 * Modelo para el ABC de Reasignaciones
 *
 * @category ABC
 * @package ap_grp
 * @author Luis Aguilar Sandoval <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
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
$funcion=2243;
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

if ($option == 'mostrarCatalogo') {
    $idreasignacion = $_POST['idreasignacion'];
    $sqlUR = " WHERE activo = 1 ";

    if (!empty($idreasignacion)) {
        if (strlen($idreasignacion) == 1) {
            $sqlUR = " WHERE cprg = '0".trim($idreasignacion)."' AND activo = 1 ";
        } else {
            $sqlUR = " WHERE cprg = '".trim($idreasignacion)."' AND activo = 1 ";
        }
    }
    $info = array();
    $SQL = "SELECT cprg, desc_rg FROM g_cat_reasignacion ".$sqlUR." ORDER BY cprg asc";
    $ErrMsg = "No se obtuvieron las Reasignaciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['cprg'],
            'Descripcion' => $myrow ['desc_rg'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['cprg'].')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['cprg'].',\''.$myrow ['desc_rg'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'RG', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Descripcion', width: '81%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE g_cat_reasignacion SET desc_rg = '$descripcion', activo = 1 WHERE cprg = '$clave'";
        $ErrMsg = "No se agrego la información de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Reasignación con éxito";
        $result = true;
    } else {
        $SQL = "SELECT activo FROM g_cat_reasignacion WHERE cprg = '$clave'";
        $ErrMsg = "No se obtuvieron las reasignaciones";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO g_cat_reasignacion (`cprg`, `desc_rg`, `activo`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
            $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$clave." del Catálogo Reasignación con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);

            if($myrow['activo']==1){
                $Mensaje = "3|Error al insertar el registro".$clave." del Catálogo Reasignación.";
                $contenido = "Ya existe la reasignación con la clave ".$clave;
                $result = true;
            }else{
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro ".$clave." del Catálogo Reasignación con éxito";

                $SQL = "UPDATE g_cat_reasignacion SET desc_rg = '$descripcion', activo = 1 WHERE cprg = '$clave'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    if (strlen($_POST['idreasignacion']) == 1) {
        $clave = '0'.$_POST['idreasignacion'];
    } else {
        $clave = $_POST['idreasignacion'];
        if ($_POST['idreasignacion'] == 0) {
            $clave = '0'.$_POST['idreasignacion'];
        }
    }
    $descripcion = $_POST['descripcion'];

    $info = array();
    $SQL = "UPDATE g_cat_reasignacion SET activo = 0 WHERE cprg = '$clave'";
    $ErrMsg = "No se elimino la información de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Reasignación con éxito";
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
