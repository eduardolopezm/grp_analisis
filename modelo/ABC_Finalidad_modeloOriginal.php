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

$ErrMsg = _( '' );
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
$TransResult = DB_query ( $SQL, $db );

$option = $_POST['option'];

if ($option == 'mostrarCatalogo') {
	$sqlUR = " WHERE activo = 1 ";

    if (!empty($_POST['ur'])) {
		$sqlUR = " WHERE id_finalidad = '".trim($_POST['ur'])."' AND activo = 1 ";
	}


    $info = array();
    $SQL = "SELECT id_finalidad as ur, desc_fin FROM g_cat_finalidad ".$sqlUR." ORDER BY id_finalidad asc";
    $ErrMsg = "No se obtuvieron las Finalidades";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	if (!empty($_POST['ur'])) {
    		//Solo nombre de campo para consulta de modificar y eliminar
	        $info[] = array( 'Clave' => $myrow ['ur'], 'Descripcion' => $myrow ['desc_fin'],
	        	'Modificar' => '<a onclick="fnModificar('.$myrow ['ur'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
	        	'Eliminar' => '<a onclick="fnEliminar('.$myrow ['ur'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
	    }else{
	    	$info[] = array( 
	    		'Clave,5%,Clave,' => $myrow ['ur'], 
	    		'Descripcion,85%,Descripción,' => $myrow ['desc_fin'],
	        	'Modificar,5%,Modificar,,noexportar' => '<a onclick="fnModificar('.$myrow ['ur'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
	        	'Eliminar,5%,Eliminar,,noexportar' => '<a onclick="fnEliminar('.$myrow ['ur'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
	    }
    }

    $contenido = array('datosCatalogo' => $info);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
	$clave = $_POST['clave'];
	$descripcion = $_POST['descripcion'];
	$proceso = $_POST['proceso'];

	if ($proceso == 'Modificar') {
		$info = array();
	    $SQL = "UPDATE g_cat_finalidad SET desc_fin = '$descripcion', activo = 1 WHERE id_finalidad = '$clave' and activo = 1";
	    $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
	    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

	    $contenido = "Se actualizo la finalidad ".$clave." - ".$descripcion;
	    $result = true;
	}else{
		$SQL = "SELECT id_finalidad, desc_fin FROM g_cat_finalidad WHERE id_finalidad = '$clave' and activo = 1";
	    $ErrMsg = "No se obtuvieron las Finalidades";
	    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
	    if (DB_num_rows($TransResult) == 0) {
			$info = array();
		    $SQL = "INSERT INTO g_cat_finalidad (`id_finalidad`, `desc_fin`, `activo`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
		    $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
		    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

		    $contenido = "Se agrego la finalidad con la clave ".$clave." - ".$descripcion;
		    $result = true;
		}else{
			$contenido = "Ya existe la finalidad con la clave ".$clave;
		    $result = false;
		}
	}
}

if ($option == 'eliminarUR') {
	$cve = $_POST['ur'];
    $clave = (string)$cve;

	$info = array();
    $SQL = "UPDATE g_cat_finalidad SET activo = 0 WHERE id_finalidad = '$clave'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

    $contenido = "Se elimino la finalidad ";
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>