<?php

/**
 * ABC de Programa Presupuestario (modelo)
 *
 * @category ABC
 * @package ap_grp
 * @author Julio Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/08/2017
 * Fecha Modificación: 21/08/2017
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
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2255;
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

    if (isset($_POST['cppt'])) {
         $ramo = $_SESSION['ramo'];
         $cppt = $_POST['cppt'];
         
        $sqlUR = " where tb_cat_programa_presupuestario.cve_ramo = '{$ramo}' and tb_cat_programa_presupuestario.cppt = '{$cppt}' and activo = 1  ";
    } else {
        $sqlUR = " where activo = 1 ";
    }

    $info = array();
    $SQL = "SELECT tb_cat_programa_presupuestario.cve_ramo, g_cat_ramo.desc_ramo, cppt, descripcion, activo, fecha_efectiva FROM tb_cat_programa_presupuestario 
             join g_cat_ramo on tb_cat_programa_presupuestario.cve_ramo = g_cat_ramo.cve_ramo ".$sqlUR. "ORDER BY cppt";
    $ErrMsg = "No se obtuvieron los programas pruesupuestarios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        /*if (isset($_POST['cppt'])) {
            $info[] = array( 'cve_ramo' => $myrow ['cve_ramo'],
            'cppt' => $myrow ['cppt'],
            'descripcion' => $myrow ['descripcion'],
            'activo' => $myrow ['activo'],
            'fecha_efectiva' => date("d-m-Y", strtotime($myrow ['fecha_efectiva'])));
        } else {
            $info[] = array( 'cve_ramo,35%,Clave Ramo,h' => $myrow ['desc_ramo'],
            'cppt,20%,CPPT,' => $myrow ['cppt'],
            'descripcion,50%,Descripción,' => $myrow ['descripcion'],
            'activo,5%,Activo,h' => $myrow ['activo'],
            'fecha_efectiva,15%,Fecha Efectiva,h' => $myrow ['fecha_efectiva'],
             'Modificar,15%,Modificar,,noexportar' => '<a onclick="fnModificar('."'".$myrow['cve_ramo']."','".$myrow ['cppt']."'".')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar,15%,Eliminar,,noexportar' => '<a onclick="fnEliminar('."'".$myrow['cve_ramo']."','".$myrow ['cppt']."'".')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }*/

         $info[] = array( 'cve_ramo' => $myrow ['desc_ramo'],
            'cppt' => $myrow ['cppt'],
            'descripcion' => $myrow ['descripcion'],
            'activo' => $myrow ['activo'],
            'fecha_efectiva' => $myrow ['fecha_efectiva'],
             'Modificar' => '<a onclick="fnModificar('."'".$myrow['cve_ramo']."','".$myrow ['cppt']."'".')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('."'".$myrow['cve_ramo']."','".$myrow ['cppt']."'".')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }

     // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'cppt', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'PP', datafield: 'cppt', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion', cellsalign: 'left', align: 'center', width: '76%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '8%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(" ", "_", traeNombreFuncionGeneral($funcion, $db, $ponerNombre = '0'))."_".date('dmY');

    $contenido = array('datosCatalogo' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);

    //$contenido = array('datosCatalogo' => $info);
    $result = true;
}

if ($option == 'mostrarRamo') {
    $info = array();
    $SQL = "SELECT cve_ramo as value, CONCAT(cve_ramo, ' - ', desc_ramo) as texto FROM g_cat_ramo WHERE active = '1' ORDER BY cve_ramo ASC";
    $ErrMsg = "No se obtuvo los Ramos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'AgregarCatalogo') {
    $cve_ramo = $_SESSION['ramo'];
    $cppt = $_POST['cppt'];
    $descripcion = $_POST['descripcion'];
    $activo = 1;
    //$fecha_efectiva = $_POST['fechaefectiva'];
    $proceso = $_POST['proceso'];
    if ($proceso == 'Modificar') {
        $info = array();
        $cve_ramo_original = $_SESSION['ramo']; // $_POST['ramo_original'];
        $cppt_original = $_POST['cppt_original'];
        $existepreviamente = false;

        $SQL = "SELECT cve_ramo FROM tb_cat_programa_presupuestario WHERE cve_ramo = '$cve_ramo' and cppt = '$cppt' and activo = 1 ";
        $contenido = "No se modificó el registro, <br> Ya existe un programa presupuestario con esa clave y pp. ".$cve_ramo." - ".$cppt;
        $Mensaje = "3|Error al modificar el programa presupuestario.";

        $ErrMsg = "No se obtuvieron las partidas de gasto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (($cve_ramo!=$cve_ramo_original) || ($cppt!=$cppt_original)) {
            if (DB_num_rows($TransResult) > 0) {
                $existepreviamente = true;
            }
        }
        if (!$existepreviamente) {
            $SQL = "UPDATE tb_cat_programa_presupuestario SET descripcion = '$descripcion', activo = $activo, cve_ramo = '$cve_ramo', cppt = '$cppt' WHERE cve_ramo = '$cve_ramo_original' and cppt = '$cppt_original'";
            $ErrMsg = "No se modificó el registro de ".$cppt;
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $Mensaje = "Modificación Exitosa.";

            $contenido = "Se modificó el registro ".$cppt." del Catálogo Programa Presupuestario con éxito";
            $result = true;
        }
    } else {
        //Revisa si la clave se encuentra en la tabla clasprog
        $clave       = substr ($cppt,0,1); 
        $SQL        = " SELECT id FROM clasprog WHERE clave = '$clave' and activo = 1 "; 
        $Mensaje     = "3|Error al agregar el programa presupuestario.";
        $ErrMsg      = "Clave no existe en Clasificación Programatica";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if (DB_num_rows($TransResult) == 1) {
            $myrow      = DB_fetch_array($TransResult);
            $idClave    = $myrow['id'];
            $SQL = "SELECT activo FROM tb_cat_programa_presupuestario WHERE cve_ramo = '$cve_ramo' and cppt = '$cppt'";
            $Mensaje = "3|Error al agregar el programa presupuestario.";
            $ErrMsg = "No se obtuvieron las partidas de gasto";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $activo = 1;
                $SQL = "INSERT INTO `tb_cat_programa_presupuestario` (`cve_ramo`, `cppt`, `descripcion`, `activo`,`id_clasprog`)
                        VALUES ('$cve_ramo', '$cppt', '$descripcion', '$activo', '$idClave')";
                $ErrMsg = "No se agregó la informacion del programa presupuestario ".$cppt;
                $Mensaje = "Se agregó el registro ".$cppt." del Catálogo Programa Presupuestario con éxito.";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$cppt." del Catálogo Programa Presupuestario con éxito.";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);
                
                if ($myrow['activo']==1) {
                    $Mensaje = "3|Error al insertar el programa presupuestario.";
                    $contenido = "Ya existe el programa presupuestario con la clave ".$cppt;
                    $result = true;
                } else {
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$cppt." de Catálogo Programa Presupuestario con éxito.";
                    

                    $SQL = "UPDATE `tb_cat_programa_presupuestario` SET `activo` = '1', `descripcion` = '$descripcion', `cve_ramo` = '$cve_ramo', `cppt` = '$cppt', `id_clasprog` = '$idClave' WHERE `cve_ramo` = '$cve_ramo' AND `cppt` = '$cppt'";

                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "Clave no existe en Clasificación Programatica"; 
        }
    }
}

if ($option == 'eliminarUR') {
    $ramo = $_SESSION['ramo'];
    $cppt = $_POST['cppt'];
    $registroEliminable = fnRegistroEliminable($cppt,$db);
    $contenido = "No se eliminó el registro $cppt porque está siendo usado en Presupuesto.";
    if($registroEliminable){
        $SQL = "UPDATE `tb_cat_programa_presupuestario` SET `activo` = '0' WHERE `cve_ramo` = '$ramo' AND `cppt` = '$cppt' ";
        $ErrMsg = "No se eliminó el registro de ".$cppt." del Catálogo Programa Presupuestario.";
        $Mensaje = "Eliminación Exitosa.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se eliminó el registro ".$cppt." de Catálogo Programa Presupuestario con éxito.";
        $result = true;
    }
}
function fnRegistroEliminable($cppt,$db){
    $eliminable     = true;
    $sql            = "SELECT COUNT(`budgetid`) AS 'RegistrosEncontrados' FROM `chartdetailsbudgetbytag` WHERE  cppt = '$cppt'";
    $ErrMsg         = "No se obtuvo la información";
    if(DB_fetch_array(DB_query($sql, $db, $ErrMsg))['RegistrosEncontrados']>0){
        $eliminable = false;
    }
    return $eliminable;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
