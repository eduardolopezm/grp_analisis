<?php
/**
 * Modelo para el ABC de Fuente del Recurso
 * 
 * @category ABC
 * @package ap_grp
 * @author Jesùs Reyes Santos <[<email address>]>
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
/*
stockid = clave
description = descripcion
Units = unidad de medida
mbflag B fijo
decimalplace fijo2
discontinued = activo
sat_stock_code = id_producto

borrar eliminar

SELECT DISTINCT id_parcial , desc_parcial, tb_cat_objeto_parcial.estatus as estatus, tb_cat_objeto_parcial.disminuye_ingreso as ingreso, locations.loccode as idFinalidad, locations.locationname as finalidad
            FROM tb_cat_objeto_parcial
            JOIN locations on (tb_cat_objeto_parcial.loccode = locations.loccode)
            ".$sqlUR."
            ORDER BY locations.loccode, id_parcial ASC";

*/
session_start();

$PageSecurity = 5;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
//header('Content-type: text/html; charset=ISO-8859-1');
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2307;
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
    $assetid = $_POST['id_adjunto'];
    //$sqlUR = " WHERE  stockmaster.tipo_dato = 2";

    // if (!empty($id_parcial)) {
       
    // }
    $info = array();

    $SQL = "SELECT
    fixedassets_adjuntos.id_adjunto as idAdjunto,
	fixedassets_adjuntos.assetid as id,
	fixedassets_adjuntos.nombre as namei,
	fixedassets_adjuntos.adjunto as urli
    FROM fixedassets_adjuntos
    WHERE   fixedassets_adjuntos.assetid = $assetid
    ORDER BY fixedassets_adjuntos.id_adjunto ASC";
    $ErrMsg = "No se obtuvieron los adjuntos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);


    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Id' => $myrow ['id'],
            'Nombre' => $myrow ['namei'],
            'Url' => $myrow ['urli'],
            'Imprimir' => '<a href="'.$myrow ['urli'].'" target="_blank"><span class="glyphicon glyphicon-download"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['idAdjunto'].',\''.$myrow ['urli'].'\',\''.$myrow ['namei'].'\','.$myrow ['id'].')"><span class="glyphicon glyphicon-trash"></span></a>' 
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Nombre', type: 'string' },";
    $columnasNombres .= "{ name: 'Url', type: 'string' },";
    $columnasNombres .= "{ name: 'Imprimir', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'Nombre', width: '42%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Archivo', datafield: 'Url', width: '42%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descargar', datafield: 'Imprimir', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'eliminarUR') {
    $idEliminar = $_POST['idEliminar'];
    $ruta = $_POST['ruta'];

    $info = array();
    $SQL = "DELETE FROM  fixedassets_adjuntos  WHERE id_adjunto = '$idEliminar'";
    $ErrMsg = "No se realizó:  ".$idEliminar;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    unlink($ruta);
    $contenido = "Se eliminó el registro ".$idEliminar." de los archivos con éxito";
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);