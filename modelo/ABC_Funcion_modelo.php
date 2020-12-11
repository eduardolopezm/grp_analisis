<?php
/**
 * Modelo para el ABC de Funcion
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
session_start();

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
//header('Content-type: text/html; charset=ISO-8859-1');
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2251;
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
    $idFinalidad = $_POST['idFinalidad'];
    $idFuncion = $_POST['idFuncion'];
    $sqlUR = " WHERE g_cat_funcion.activo = 1 and g_cat_finalidad.activo = 1 ";

    if (!empty($idFuncion)) {
        $sqlUR = " WHERE g_cat_funcion.activo = 1 and g_cat_finalidad.activo = 1 and g_cat_finalidad.id_finalidad = '".trim($idFinalidad)."' and id_funcion = '".trim($idFuncion)."' ";
    }
    $info = array();
    $SQL = "SELECT DISTINCT id_funcion , desc_fun, g_cat_finalidad.id_finalidad as idFinalidad, g_cat_finalidad.desc_fin as finalidad
            FROM g_cat_funcion
            JOIN g_cat_finalidad on (g_cat_funcion.id_finalidad = g_cat_finalidad.id_finalidad)
            ".$sqlUR."
            ORDER BY g_cat_finalidad.id_finalidad, id_funcion ASC";
    $ErrMsg = "No se obtuvieron las Funciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_funcion'],
            'Funcion' => $myrow ['desc_fun'],
            'idFinalidad' => $myrow ['idFinalidad'],
            'Finalidad' => $myrow ['finalidad'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_funcion'].',\''.$myrow ['desc_fun'].'\','.$myrow ['idFinalidad'].',\''.$myrow ['finalidad'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_funcion'].',\''.$myrow ['desc_fun'].'\','.$myrow ['idFinalidad'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idFinalidad', type: 'string' },";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Funcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'FI', datafield: 'idFinalidad', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'FU', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Funcion', width: '76%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $idFinalidad = $_POST['idFinalidad'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];
    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE g_cat_funcion SET desc_fun = '$descripcion', activo = 1 WHERE id_finalidad = '$idFinalidad' and  id_funcion = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Función con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($idFinalidad, $db)){
            $SQL = "SELECT activo FROM g_cat_funcion WHERE id_finalidad = '$idFinalidad' and id_funcion = '$clave' ORDER BY id_funcion ASC";
            $ErrMsg = "No se obtuvieron las unidades resposables";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $SQL = "INSERT INTO g_cat_funcion (`id_finalidad`,`id_funcion`, `desc_fun`, `activo`)
                        VALUES ('".$idFinalidad."','".$clave."', '".$descripcion."', '1')";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$clave." del Catálogo Función con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Función.";
                    $contenido = "Ya existe la función con la clave ".$clave;
                    $result = false;
                }else{
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$clave." del Catálogo Función con éxito";

                    $SQL = "UPDATE g_cat_funcion SET desc_fun = '$descripcion', activo = 1 WHERE id_finalidad = '$idFinalidad' and  id_funcion = '$clave'";

                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "No existe la finalidad con la clave ".$idFinalidad;
            $result = false;
        }
    }
}

if ($option == 'eliminarUR') {
    $clave = $_POST['idFuncion'];
    $fin = $_POST['idFinalidad'];
    $descripcion = $_POST['descripcion'];

    $info = array();
    $SQL = "UPDATE g_cat_funcion SET activo = 0 WHERE id_funcion = '$clave' and id_finalidad = '$fin'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Función con éxito";
    $result = true;
}

if ($option == 'existeSubfuncion') {
    $cveFin = $_POST['idFinalidad'];
    $cveFun = $_POST['idFuncion'];
    $SQL = "SELECT * FROM g_cat_sub_funcion WHERE activo = 1 AND id_finalidad = '".$cveFin."' AND id_funcion = '". $cveFun."'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $result = false;
    }else{
        $result = true;
    }
}
function fnValidarExiste($idFinalidad, $db){
    $SQL = "SELECT * FROM g_cat_finalidad WHERE activo = 1 and id_finalidad = '".$idFinalidad."' ORDER BY id_finalidad ASC";
    $ErrMsg = "No se encontro la informacion de ".$idFinalidad;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFin = true;
    }else{
        $existeFin = false;
    }
    return $existeFin;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
