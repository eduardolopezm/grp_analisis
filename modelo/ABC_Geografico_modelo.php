<?php
/**
 * ABC Geografico o entidad federativa
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 03/10/2017
 * Se realizan operación pero el Alta, Baja y Modificación, conforme a las validaciones creadas para la operación seleccionada
 */

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix.'abajo.php';
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
/*if ($abajo) {
    include $PathPrefix . 'includes/LanguageSetup.php';
} */
$funcion=2247;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$columnasNombres = "";
$columnasNombresGrid = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

switch ($option) {
case 'mostrarCatalogo':

    $sqlcg= " WHERE activo = 'S' ";
    if (!empty($_POST['cg'])) {
        $sqlcg = " WHERE cg = '".trim($_POST['cg'])."' ";
    }

    $info = array();
    $SQL = "SELECT cg,descripcion FROM g_cat_geografico ".$sqlcg." ORDER BY cg asc";
    $ErrMsg = "No se obtuvieron registros del Catálogo Entidad Federativa.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ( $myrow = DB_fetch_array($TransResult) ) 
    {
        if (!empty($_POST['cg'])) {
            //Solo nombre de campo para consulta de modificar y eliminar
            /*$info[] = array( 'cg' => $myrow ['cg'], 'Descripcion' => $myrow ['descripcion'],
                
                'Modificar' => '<a onclick="fnModificar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
                'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' ); */

            $info[] = array(     'CG' => $myrow ['cg'], 
            'Descripción' => $myrow ['descripcion'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
            'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }
        else
        {
            /*$info[] = array( 'cg,20%,CG,' => $myrow ['cg'], 'descripcion,40%,Descripción,' => $myrow ['descripcion'],
                'Modificar,20%,Modificar,' => '<a onclick="fnModificar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
                'Eliminar,20%,Eliminar,' => '<a onclick="fnEliminar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' ); */

            $info[] = array(      'CG' => $myrow ['cg'], 
            'Descripción' => $myrow ['descripcion'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
            'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['cg'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'CG', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripción', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'EF', datafield: 'CG', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción',datafield: 'Descripción', width: '81%', align: 'center',hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";
    $funcion = 2242;
    $nombre= traeNombreFuncionGeneral($funcion, $db);
    $nombre=str_replace(" ","_",$nombre);
    $nombreExcel = $nombre.'_'.date('dmY');
    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;

    break;

case 'AgregarCatalogo':
    
    $cg = $_POST['cgnuevo'];
    $descripcion = $_POST['descripcion'];

    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE g_cat_geografico SET descripcion = '$descripcion'  WHERE cg = '".$cg."'";
        $ErrMsg = "No se modificó el registro para <b>".$cg."</b> del Catálogo Entidad Federativa.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $contenido = "Se modificó el registro <b>".$cg."</b> del Catálogo Entidad Federativa con éxito.";
        $result = true;
    }
    else
    {
        $SQL = "SELECT cg,activo FROM g_cat_geografico WHERE cg='" . $cg . "'";
        //AND descripcion = '$descripcion'";
        $ErrMsg = "No se obtuvieron registros del Catálogo Entidad Federativa";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
          $SQL = "INSERT INTO g_cat_geografico (`cg`, `descripcion`,`activo`)
          VALUES ('". $cg . "','".$descripcion."','S')";
          $ErrMsg = "No se agregó el registro para <b>".$cg."</b> del Catálogo Entidad Federativa.";
          $TransResult = DB_query($SQL, $db, $ErrMsg);
          $contenido = "Se agregó el registro <b>".$cg."</b> del Catálogo Entidad Federativa con éxito.";
          $result = true;
        }
        else
        {
            $activo='';
            while ($myrow = DB_fetch_array($TransResult)){
                $activo=$myrow['activo'];
            }
            if($activo=='N'){

            $SQL = "UPDATE g_cat_geografico SET activo ='S',descripcion='".$descripcion."' WHERE cg ='".$cg."'";
            $ErrMsg = "No se agregó el registro para <b>".$cg ."</b> del Catálogo Entidad Federativa.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $contenido = "Se agregó el registro <b>".$cg."</b> del Catálogo Entidad Federativa con éxito.";
            $result = true;

            }else{
                $contenido = "Ya existe Entidad Federativa <b>".$cg."</b>";
                $result = true;
            }
        }
    }
    break;

case 'eliminarcg':
    $cg = $_POST['cg'];
    $SQL         = "UPDATE  g_cat_geografico SET activo='N' where cg='".$cg."'"; 
    //$SQL         = "DELETE FROM g_cat_geografico where cg='".$cg."'";
    $ErrMsg = "No se eliminó el registro <b> ".$cg."</b> del Catálogo Entidad Federativa.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = "Se eliminó el registro <b>".$cg."</b> del Catálogo Entidad Federativa con éxito.";
    $result = true;
    break;
    
default:
    // codigo futuro...
    break;
}

$dataObj = array('contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>