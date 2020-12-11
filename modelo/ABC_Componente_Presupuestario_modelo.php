<?php

/**
 * ABC de Componente Presupuestario (modelo)
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
$funcion=2256;
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
    if (!empty($_POST['cp'])) {
         $cp = $_POST['cp'];
         $sqlUR = " where cp = '{$cp}' and activo = 1 ";
    } else {
        $sqlUR = " where activo = 1 ";
    }

    $info = array();
    $SQL = "SELECT cp, descripcion FROM tb_cat_componente_presupuestario ".$sqlUR;
    $ErrMsg = "No se obtuvieron las componentes presupuestarios";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        /*if (!empty($_POST['cp'])) {
            $info[] = array( 'cp' => $myrow ['cp'],
            'descripcion' => $myrow ['descripcion']);
        } else {
            $info[] = array( 'cp,10%,cp,' => $myrow ['cp'],
            'descripcion,70%,Descripción,' => $myrow ['descripcion'],
            'Modificar,10%,Modificar,,noexportar' => '<a onclick=fnModificar('.'"'.$myrow ['cp'].'"'.')><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar,10%,Eliminar,,noexportar' => '<a onclick=fnEliminar('.'"'.$myrow ['cp'].'"'.')><span class="glyphicon glyphicon-trash"></span></a>' );
        }*/

          $info[] = array( 'cp' => $myrow ['cp'],
            'descripcion' => $myrow ['descripcion'],
            'Modificar' => '<a onclick=fnModificar('.'"'.$myrow ['cp'].'"'.')><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick=fnEliminar('.'"'.$myrow ['cp'].'"'.')><span class="glyphicon glyphicon-trash"></span></a>' );
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'cp', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'AUX3', datafield: 'cp', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion', cellsalign: 'left', align: 'center', width: '76%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '8%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(" ", "_", traeNombreFuncionGeneral($funcion, $db, $ponerNombre = '0'))."_".date('dmY');

    $contenido = array('datosCatalogo' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);

    //$contenido = array('datosCatalogo' => $info);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $cp = $_POST['cp'];

    //si tiene 3 caracteres significa que es nuevo, le tenemos que agregar siete ceros
    /*if (strlen($cp) == 3) {
        $cp = $cp."0000000";
    }*/

    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];


    if ($proceso == 'Modificar') {
        $info = array();
        $cp_original = $_POST['cp_original'];

        $existepreviamente = false;

        $SQL = "SELECT cp FROM tb_cat_componente_presupuestario WHERE cp = '$cp' and activo = 1 ";
        $contenido = "No se modificó el registro, <br> Ya existe el Auxiliar 3 con la Clave: ".$cp;
        $Mensaje = "Error al modificar el Auxiliar 3.";
        

        $ErrMsg = "No se obtuvieron los componentes presupuestarios.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if ($cp!=$cp_original) {
            if (DB_num_rows($TransResult) > 0) {
                $existepreviamente = true;
            }
        }

        if (!$existepreviamente) {
            $SQL = "UPDATE tb_cat_componente_presupuestario SET descripcion = '$descripcion', cp = '$cp' WHERE cp = '$cp_original' ";
            $ErrMsg = "No se agrego la informacion de ".$cp;
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $contenido = "Se modificó el registro ".$cp." del Catálogo Auxiliar 3 con éxito";
            $Mensaje = "Actualización Exitosa ";
            $result = true;
        }
    } else {
        $SQL = "SELECT activo FROM tb_cat_componente_presupuestario WHERE cp = '$cp'";
        $ErrMsg = "No se obtuvieron las partidas de gasto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO tb_cat_componente_presupuestario (`cp`, `descripcion`, activo)
		            VALUES ('".$cp."', '".$descripcion."', 1)";
            $ErrMsg = "No se agregó el registro de ".$cp." - ".$descripcion." del Catálogo Auxiliar 3";

            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$cp."  del Catálogo Auxiliar 3 con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);
            
            if ($myrow['activo']==1) {
                $Mensaje = "Error al insertar el Auxiliar 3.";
                $contenido = "Ya existe el Auxiliar 3 con la clave ".$cp;
                $result = true;
            } else {
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro ".$cp." del Catálogo Auxiliar 3 con éxito";
                

                $SQL = "UPDATE tb_cat_componente_presupuestario SET activo = 1, descripcion = '$descripcion',  cp = '$cp' WHERE cp = '$cp'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $cp = $_POST['cp'];

    $info = array();
    $SQL = "update tb_cat_componente_presupuestario set activo = 0 where cp = '$cp' ";
    $ErrMsg = "No se eliminó el registro ".$cp." del catálogo Auxiliar 3";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$cp." del Catálogo Auxiliar 3 con éxito";
    $result = true;
}


$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
