<?php

/**
 * ABC de Actividad Institucional (modelo)
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
//

if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2254;
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
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);


$option = $_POST['option'];

if ($option == 'mostrarCatalogo') {
    $sqlUR = " ";

    if (isset($_POST['cain'])) {
         $ramo = $_SESSION['ramo'];
         $cain = $_POST['cain'];
         $sqlUR = " where tb_cat_actividad_institucional.cve_ramo = '{$ramo}' and tb_cat_actividad_institucional.cain = '{$cain}' and activo = 1  ";
    } else {
        $sqlUR = " where activo = 1 ";
    }

    $info = array();
    $SQL = "SELECT tb_cat_actividad_institucional.cve_ramo, g_cat_ramo.desc_ramo, cain, descripcion, activo, fecha_efectiva FROM tb_cat_actividad_institucional
            left outer join g_cat_ramo on tb_cat_actividad_institucional.cve_ramo = g_cat_ramo.cve_ramo  ".$sqlUR. "ORDER BY cain";
    $ErrMsg = "No se obtuvieron las actividades institucionales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        /*if (isset($_POST['cain'])) {
            $info[] = array( 'cve_ramo' => $myrow ['cve_ramo'],
            'cain' => $myrow ['cain'],
            'descripcion' => $myrow ['descripcion'],
            'activo' => $myrow ['activo'],
            'fecha_efectiva' => date("d-m-Y", strtotime($myrow ['fecha_efectiva'])));
        } else {
            $info[] = array( 'cve_ramo,35%,Clave Ramo,h' => $myrow ['desc_ramo'],
            'cain,20%,CAIN,' => $myrow ['cain'],
            'descripcion,50%,Descripción,' => $myrow ['descripcion'],
            'activo,5%,Activo,h' => $myrow ['activo'],
            'fecha_efectiva,15%,Fecha Efectiva,h' => $myrow ['fecha_efectiva'],
            'Modificar,15%,Modificar,,noexportar' => '<a style="text-align:center" onclick="fnModificar('."'".$_SESSION['ramo']."','".$myrow ['cain']."'".')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar,15%,Eliminar,,noexportar' => '<a onclick="fnEliminar('."'".$_SESSION['ramo']."','".$myrow ['cain']."'".')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }*/
          $info[] = array( 'cve_ramo' => $myrow ['cve_ramo'],
            'cain' => $myrow ['cain'],
            'descripcion' => $myrow ['descripcion'],
            'activo' => $myrow ['activo'],
            'fecha_efectiva' => date("d-m-Y", strtotime($myrow ['fecha_efectiva'])),
            'Modificar' => '<a style="text-align:center" onclick="fnModificar('."'".$_SESSION['ramo']."','".$myrow ['cain']."'".')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('."'".$_SESSION['ramo']."','".$myrow ['cain']."'".')"><span class="glyphicon glyphicon-trash"></span></a>' );
        //);
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'cain', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'AI', datafield: 'cain', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
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
    $SQL = "SELECT cve_ramo as value, CONCAT(cve_ramo, ' - ', desc_ramo) as texto FROM g_cat_ramo WHERE active = '1'  ORDER BY cve_ramo ASC";
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
    $cain = $_POST['cain'];
    $descripcion = $_POST['descripcion'];
    $activo = 1;//$_POST['activo'];
    //$fecha_efectiva = $_POST['fechaefectiva'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        $cve_ramo_original = $_SESSION['ramo']; // $_POST['ramo_original'];
        $cain_original = $_POST['cain_original'];

        $existepreviamente = false;

        $SQL = "SELECT activo FROM tb_cat_actividad_institucional WHERE cve_ramo = '$cve_ramo' and cain = '$cain' and activo = 1 ";
        $contenido = "No se actualizó el registro, <br> Ya existe una actividad institucional con esa clave y ai. ".$cain;
        $Mensaje = "Error al actualizar la actividad institucional.";
        

        $ErrMsg = "No se obtuvieron las actividades institucionales";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (($cve_ramo!=$cve_ramo_original) || ($cain!=$cain_original)) {
            if (DB_num_rows($TransResult) > 0) {
                $existepreviamente = true;
            }
        }
        if (!$existepreviamente) {
            $SQL = "UPDATE tb_cat_actividad_institucional SET descripcion = '$descripcion', cve_ramo = '$cve_ramo', cain = '$cain' WHERE cve_ramo = '$cve_ramo_original' and cain = '$cain_original'";
            $ErrMsg = "No se agregó el registro ".$cain_original." del Catálogo Actividad Institucional";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $Mensaje = "1|Actualización Exitosa.";

            $contenido = "Se modificó el registro ".$cain_original." del Catálogo Actividad Institucional con éxito";
            $result = true;
        }
    } else {
        $SQL = "SELECT activo FROM tb_cat_actividad_institucional WHERE cve_ramo = '$cve_ramo' and cain = '$cain' ";
        

        $ErrMsg = "No se obtuvieron las actividades institucionales";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO tb_cat_actividad_institucional (`cve_ramo`, `cain`, `descripcion`, `activo`)
    	            VALUES ('".$cve_ramo."', '".$cain."', '".$descripcion."', ".$activo.")";
            $ErrMsg = "No se agregó la informacion de ".$cain;

            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $Mensaje = "1|Inserción Exitosa.";

            $contenido = "Se agregó el registro ".$cain." del Catálogo Actividad Institucional con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);

            if ($myrow['activo']==1) {
                $Mensaje = "3|Error al insertar la actividad institucional.";
                $contenido = "Ya existe la actividad institucional con la clave ".$cain;
                $result = true;
            } else {
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro ".$cain." del Catálogo Actividad Institucional con éxito";
                

                $SQL = "UPDATE tb_cat_actividad_institucional SET activo = 1, descripcion = '$descripcion', cve_ramo = '$cve_ramo', cain = '$cain' WHERE cve_ramo = '$cve_ramo' and cain = '$cain'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $ramo = $_SESSION['ramo'];
    $cain = $_POST['cain'];

    $info = array();
    $SQL = "update tb_cat_actividad_institucional set activo = 0 where cve_ramo = '$ramo' and cain = '$cain' ";
    $ErrMsg = "No se eliminó la información de ".$cain;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$cain." del Catálogo Actividad Institucional con éxito";
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => "", 'Mensaje' => "");

echo json_encode($dataObj);
