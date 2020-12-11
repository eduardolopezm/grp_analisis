<?php

/**
 * ABC de Objeto Gasto (Partida especifica) modelo
 *
 * @category ABC
 * @package ap_grp
 * @author Juan José Ledesma <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2019
 * Fecha Modificación: 06/11/2019
 * Se realizan operación pero el Alta, Baja y Modificación
 */



//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);
session_start();
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
header('Content-type: text/html; charset=ISO-8859-1');

include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2499;
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
$columnasNombres="";
$columnasNombresGrid = "";


header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";

$TransResult = DB_query($SQL, $db);



$option = $_POST['option'];

if ($option == 'mostrarCatalogo') {
    $sqlUR = " ";

    if (!empty($_POST['ur'])) {
        $ur = $_POST['ur'];
        $sqlUR = " WHERE id = '".$ur."' and  activo = 1";
    } else {
        $sqlUR = " WHERE activo = 1  ";
    }

    $info = array();
    $SQL = "SELECT id,clave, descripcion FROM rubro_ingreso ".$sqlUR." ORDER BY clave ";
    $ErrMsg = "No se obtuvieron las objetos de gasto";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if (!empty($_POST['ur'])) {
            $info[] = array(
            'clave' => $myrow ['clave'],
            'descripcion' => $myrow ['descripcion']);
        } else {
            $info[] = array( 
                'clave' => $myrow ['clave'],
                'descripcion' => $myrow ['descripcion'],
                'Modificar' => '<a onclick="fnModificarOG('.$myrow ['id'].')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminarOG('.$myrow ['id'].', '.$myrow ['clave'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }
    }

     // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'clave', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    //$columnasNombres .= "{ name: 'Detalle', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'clave', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion', width: '76%', cellsalign: 'left', align: 'center', hidden: false },"; //antes width: '45%'
    //$columnasNombresGrid .= " { text: 'Ver Detalle', datafield: 'Detalle', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(" ", "_", traeNombreFuncionGeneral($funcion, $db, $ponerNombre = '0'))."_".date('dmY');

    $contenido = array('datosCatalogo' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);


    //$contenido = array('datosCatalogo' => $info);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $clave = $_POST['clave'];
    $description = $_POST['description'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        
        $SQL = "UPDATE rubro_ingreso SET descripcion = '$description', clave = '$clave' WHERE clave = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$partidaespecifica." del Catálogo Rubro Ingreso con éxito";
        $result = true;
    } else {
        $SQL = "SELECT clave, activo FROM rubro_ingreso WHERE clave = '$clave'";
        $ErrMsg = "No se obtuvieron las partidas de gasto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO rubro_ingreso (`clave`, `descripcion`)
                    VALUES ('".$clave."', '".$description."')";
            $ErrMsg = "No se agregó la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$clave." - ".$description." del Catálogo Rubro Ingreso con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);
            if ($myrow['activo'] == 0) {
                $SQL = "UPDATE rubro_ingreso SET descripcion = '".$description."', activo = '1' WHERE clave = '$clave' ";
                $ErrMsg = "No se agregó la informacion de ".$clave." - ".$descripcion;
                $Mensaje = "1|Agregar Exitosa.";
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$clave." - ".$description." del Catálogo Rubro Ingreso con éxito";
                $result = true;
            } else {
                $Mensaje = "3|Error al insertar la partida específica.";
                $contenido = "Ya existe el Rubro Ingreso con la clave ".$clave;
                $result = true;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $id = $_POST['id'];
    $clave = $_POST['clave'];

    $info = array();
    $SQL = "UPDATE rubro_ingreso SET activo = '0' WHERE id = '$id' ";
    $ErrMsg = "No se eliminó el registro ".$id." del Catálogo Rubrs Ingresos con éxito";
    $Mensaje = "1|Eliminación Exitosa.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó  el registro ".$clave." del Catálogo Rubro Ingreso con éxito";
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
