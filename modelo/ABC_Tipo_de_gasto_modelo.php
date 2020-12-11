<?php
/**
 * ABC Tipo de gasto
 *
 * @category ABC
 * @package ap_grp
 * @author Arturo Lopez Peña  <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 04/10/2017
 * Se realizan operación pero el Alta, Baja y Modificación.Conforme a las validaciones creadas para la operación seleccionada
 */
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
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

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

switch ($option) {
case 'mostrarCatalogo':
        
    $sqlctga= " WHERE activo = 'S' ";
    if (!empty($_POST['ctga'])) {
        $sqlctga = " WHERE ctga = '".trim($_POST['ctga'])."' ";
    }

    $info = array();
    $SQL = "SELECT ctga,descripcion FROM g_cat_tipo_de_gasto ".$sqlctga." ORDER BY ctga asc";
    $ErrMsg = "No se obtuvieron registros del Catálogo Tipo de Gasto.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ( $myrow = DB_fetch_array($TransResult) ) {
        if (!empty($_POST['ctga'])) {
            //Solo nombre de campo para consulta de modificar y eliminar
           /* $info[] = array( 'ctga' => $myrow ['ctga'], 'Descripcion' => $myrow ['descripcion'],
            
             'Modificar' => '<a onclick="fnModificar('.$myrow ['ctga'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
             'Eliminar' => '<a onclick="fnEliminar('.$myrow ['ctga'].')"><span class="glyphicon glyphicon-trash"></span></a>' ); */

 $info[] = array( 
            'TG' => $myrow ['ctga'], 
            'Descripción' => $myrow ['descripcion'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['ctga'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
            'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['ctga'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );

        }else{ 
        /*
            $info[] = array( 'ctga,10%,TG,' => $myrow ['ctga'], 'descripcion,70%,Descripción,' => $myrow ['descripcion'],
             'Modificar,10%,Modificar,' => '<a onclick="fnModificar('.$myrow ['ctga'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
             'Eliminar,10%,Eliminar,' => '<a onclick="fnEliminar('.$myrow ['ctga'].')"><span class="glyphicon glyphicon-trash"></span></a>' ); */

             $info[] = array( 
            'TG' => $myrow ['ctga'], 
            'Descripción' => $myrow ['descripcion'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['ctga'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
            'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['ctga'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }
    }

     // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'TG', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripción', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'TG', datafield: 'TG', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Descripción', width: '81%', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $funcion = 2247;
    $nombre= traeNombreFuncionGeneral($funcion, $db);
    $nombre=str_replace(" ","_",$nombre);
    $nombreExcel = $nombre.'_'.date('dmY');
    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;

    break;

case 'AgregarCatalogo':
    
    $ctga = $_POST['ctganuevo'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $SQL = "UPDATE g_cat_tipo_de_gasto SET descripcion = '$descripcion'  WHERE ctga = '".$ctga."'";
        $ErrMsg = "No se modificó el registro para <b>".$ctga."</b> del Catálogo Tipo de Gasto.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro <b>".$ctga."</b> del Catálogo Tipo de Gasto con éxito.";
        $result = true;
    }
    else{
        $SQL = "SELECT ctga,activo FROM g_cat_tipo_de_gasto WHERE ctga='" . $ctga . "'";
        //AND descripcion = '$descripcion'";
        $ErrMsg = "No se obtuvieron registros del Catálogo Tipo de Gasto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows( $TransResult) == 0) {
            $SQL = "INSERT INTO g_cat_tipo_de_gasto (`ctga`, `descripcion`,`activo`)
           VALUES ('". $ctga . "','".$descripcion."','S')";
             
            $ErrMsg = "No se agregó el registro para <b>".$ctga."</b> del Catálogo Tipo de Gasto.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro <b>".$ctga."</b> del Catálogo Tipo de Gasto con éxito.";
            $result = true;
        }
        else{

            $activo='';
            while ($myrow = DB_fetch_array($TransResult)){
                $activo=$myrow['activo'];
            }
            if($activo=='N'){

            $SQL = "UPDATE g_cat_tipo_de_gasto SET activo ='S',descripcion='".$descripcion."' WHERE ctga ='".$ctga."'";
            $ErrMsg = "No se agregó el registro para <b>".$ctga."</b> del Catálogo Tipo de Gasto.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $contenido = "Se agregó el registro <b>".$ctga."</b> del Catálogo Tipo de Gasto con éxito.";
            $result = true;

            }else{
                $contenido = "Ya existe Tipo de Gasto <b>".$ctga."</b>";
                $result = true;
            }
//           
        }
    }
    break;

case 'eliminarctga':
    $ctga = $_POST['ctga'];
    $SQL = "UPDATE g_cat_tipo_de_gasto SET activo ='N' WHERE ctga ='".$ctga."'";
    //$SQl="DELETE FROM g_cat_tipo_de_gasto WHERE ctga ='$ctga'";
    //$SQL         = "DELETE FROM g_cat_tipo_de_gasto where ctga='" . $ctga . "'";
    $ErrMsg = "No se eliminó el registro <b> ".$ctga."</b> del Catálogo Tipo de Gasto.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido = "Se eliminó el registro <b>".$ctga."</b> del Catálogo Tipo de Gasto con éxito.";
    $result = true;
    break;
    
default:
    // code...
    break;
}



$dataObj = array('contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>