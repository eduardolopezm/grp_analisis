<?php

/**
 * Modelo para el ABC de Unidades Responsables
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
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
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2241;
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
    $sqlUR = " WHERE tagactive = 1 ";

    if (!empty($_POST['ur'])) {
        $sqlUR = " WHERE tagactive = 1 AND tagref = '".trim($_POST['ur'])."' ";
    }

    $info = array();
    $SQL = "SELECT tags.tagref , tags.tagdescription, tags.address1, tags.address2, tags.address3, tags.address4, tags.address5, tags.address6, tags.legalid, tags.cp,
    legalname, tags.nu_interior, tags.ln_tipo
    FROM tags
    join legalbusinessunit on tags.legalid = legalbusinessunit.legalid ".$sqlUR." ORDER BY tagref asc";
    $ErrMsg = "No se obtuvieron las Unidades Responsables";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if (!empty($_POST['ur'])) {
            //Solo nombre de campo para consulta de modificar y eliminar
            $info[] = array(
                'tagref' => strtoupper($myrow ['tagref']),
                'Descripcion' => $myrow ['tagdescription'],
                'cp' => $myrow['cp'],
                'legalname' => $myrow['legalname'],
                'ln_tipo' => $myrow['ln_tipo'],
                'address1' => $myrow['address1'], 'address2' => $myrow['address2'], 'address3' => $myrow['address3'],
                'address4' => $myrow['address4'], 'address5' => $myrow['address5'], 'address6' => $myrow['address6'],
                'legalid' => $myrow['legalid'], 'nu_interior' => $myrow['nu_interior'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['tagref'].')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['tagref'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
        } else {
            $info[] = array(
                'tagref' => strtoupper($myrow ['tagref']),
                'Descripcion' => $myrow ['tagdescription'],
                'legalname' => $myrow['legalname'],
                'Modificar' => '<a onclick=fnModificar('.'"'.$myrow ['tagref'].'"'.')><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick=fnEliminar('.'"'.$myrow ['tagref'].'"'.')><span class="glyphicon glyphicon-trash"></span></a>' );
        }
    }


    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'tagref', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripcion', type: 'string' },";
    //$columnasNombres .= "{ name: 'legalname', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'tagref', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Descripcion', cellsalign: 'left', align: 'center', width: '76%', hidden: false },";
    //$columnasNombresGrid .= " { text: 'Dependencia', datafield: 'legalname', cellsalign: 'left', align: 'center', width: '16%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '8%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datosCatalogo' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);

    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $clave = strtoupper($_POST['clave']);
    $proceso = $_POST['proceso'];
    $legalid = 2;
    $areacode = $_POST['areacode'];
    $department = $_POST['department'];
    $description = $_POST['description'];
    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $address3 = $_POST['address3'];
    $address4 = $_POST['address4'];
    $address5 = $_POST['address5'];
    $pais = "MEXICO";
    $cp = $_POST['cp'];
    $tipofact = 0;//$_POST["tipofact"];
    $tagdebtorno = $_POST['tagdebtorno'];
    $tagsupplier = $_POST['tagsupplier'];
    $cmbTipo = $_POST['cmbTipo'];
    $nu_interior = $_POST['txtNumInterior'];

    if (!isset($nu_interior) || $nu_interior == "") {
        $nu_interior = 'null';
    }

    if (!isset($department) || $department == "") {
        $department = 24;
    }


    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE tags SET tagdescription = '$description', legalid = '$legalid', areacode = '$areacode',
        address1 = '$address1', address2 = '$address2', address3 = '$address3', address4 = '$address4', address5 = '$address5',
        cp = '$cp', nu_interior = $nu_interior, ln_tipo = '$cmbTipo' WHERE tagref = '$clave'";
        $ErrMsg = "No se modificó el registro <b>".$clave."</b> del Catálogo  Unidades Responsables con éxito.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro <b>".$clave."</b> del Catálogo  Unidades Responsables con éxito.";
        $result = true;
    } else {
        $SQL = "SELECT tagactive FROM tags WHERE tagref = '$clave'";
        $ErrMsg = "No se obtuvieron registros del Catálogo Unidades Responsables";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT  INTO tags (tagref, tagdescription, legalid, areacode, u_department, tagname, address1, address2, address3, address4, address5, address6,
                cp,typeinvoice, tagdebtorno, tagsupplier, ln_tipo, nu_interior,tagactive)
                values('$clave', '$description', '$legalid', '$areacode', $department, '$description', '$address1', '$address2', '$address3', '$address4', '$address5', '$pais',
                '$cp', '$tipofact','$tagdebtorno','$tagsupplier','$cmbTipo', $nu_interior,'1')";

            $ErrMsg = "No se agregó el registro <b>".$clave."</b> del Catálogo Unidades Responsables";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido =  "Se agregó el registro <b>".$clave."</b> del Catálogo Unidades Responsables con éxito.";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);

            if ($myrow['tagactive']==1) {
                $Mensaje = "Error al insertar la Unidad Responsable .";
                $contenido = "Ya existe Unidad Responsable con la clave <b>".$clave."</b>";
                $result = true;
            } else {
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro <b>".$clave."</b> del Catálogo Unidades Responsables con éxito.";


                $SQL = "UPDATE tags SET tagdescription = '$description', legalid = '$legalid', areacode = '$areacode',
        address1 = '$address1', address2 = '$address2', address3 = '$address3', address4 = '$address4', address5 = '$address5',
        cp = '$cp', nu_interior = $nu_interior, ln_tipo = '$cmbTipo', tagactive = 1 WHERE tagref = '$clave'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $clave = $_POST['ur'];

    $result = false;

    $SQL = "SELECT `cunr` FROM `g_cat_ppi` WHERE `cunr` = '$clave' AND `activo` = 'S'";
    $ErrMsg = "No se obtuvieron las Unidades Responsables.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if(DB_num_rows($TransResult)){
        $contenido = "No puede eliminarse la Unidad Responsable ".$clave." porque está siendo usada en el Catálogo Programa Proyecto de Inversión.";
    }else{
        $info = array();
        $SQL = "UPDATE `tags` SET `tagactive` = 0 WHERE `tagref` = '$clave'";
        $ErrMsg = "No se eliminó el registro <b> ".$clave."</b> del Catálogo Unidades Responsables.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se eliminó el registro <b>".$clave."</b> del Catálogo Unidades Responsables con éxito.";
        $result = true;
    }
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
