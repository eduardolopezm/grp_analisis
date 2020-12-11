<?php
/**
 * Modelo para el ABC de Finalidad
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
$funcion=2248;
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
    $idfinalidad = $_POST['idfinalidad'];
    $sqlUR = " WHERE activo = 1 ";

    if (!empty($idfinalidad)) {
        $sqlUR = " WHERE id_finalidad = '".trim($idfinalidad)."' AND activo = 1 ";
    }
    $info = array();
    $SQL = "SELECT id_finalidad, desc_fin FROM g_cat_finalidad ".$sqlUR." ORDER BY id_finalidad asc";
    $ErrMsg = "No se obtuvieron las Finalidades";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_finalidad'],
            'Descripcion' => $myrow ['desc_fin'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_finalidad'].')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_finalidad'].',\''.$myrow ['desc_fin'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
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
    $columnasNombresGrid .= " { text: 'FI', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
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
        $SQL = "UPDATE g_cat_finalidad SET desc_fin = '$descripcion', activo = 1 WHERE id_finalidad = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Finalidad con éxito";
        $result = true;
    } else {
        $SQL = "SELECT activo FROM g_cat_finalidad WHERE id_finalidad = '$clave'";
        $ErrMsg = "No se obtuvieron las Finalidades";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO g_cat_finalidad (`id_finalidad`, `desc_fin`, `activo`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
            $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$clave." del Catálogo Finalidad con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);

            if($myrow['activo']==1){
                $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Finalidad.";
                $contenido = "Ya existe la finalidad con la clave ".$clave;
                $result = false;
            }else{
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro ".$clave." del Catálogo Finalidad con éxito";

                $SQL = "UPDATE g_cat_finalidad SET desc_fin = '$descripcion', activo = 1 WHERE id_finalidad = '$clave'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}
if ($option == 'eliminarUR') {
    $cve = $_POST['idfinalidad'];
    $descripcion = $_POST['descripcion'];
    $clave = (string)$cve;

    $info = array();
    $SQL = "UPDATE g_cat_finalidad SET activo = 0 WHERE id_finalidad = '$clave'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Finalidad con éxito";
    $result = true;
}

if ($option == 'existeFuncion') {
    $cveFin = $_POST['idFinalidad'];
    $SQL = "SELECT * FROM g_cat_funcion WHERE activo = 1 and id_finalidad = '".$cveFin."'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $result = false;
    }else{
        $result = true;
    }
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
