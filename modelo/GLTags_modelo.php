<?php
/**
 * Modelo para el ABC de Unidades Responsables
 *
 * @category ABC
 * @package ap_grp
 * @author Eduardo López Morales <[<email address>]>
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
$funcion=2241;
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
	$sqlWHERE = "";

	if (!empty($_POST['tagref'])) {
		$sqlWHERE = " AND tagref = '".trim($_POST['tagref'])."' ";
	}

    $info = array();
	$SQL = "SELECT tags.tagref, tags.tagdescription, tags.areacode, tags.legalid, areas.areadescription, 
			legalbusinessunit.legalname, departments.department
			FROM tags 
			LEFT JOIN departments ON tags.u_department = departments.u_department
			, areas, legalbusinessunit 
			WHERE tags.areacode = areas.areacode AND tags.legalid = legalbusinessunit.legalid AND tags.tagactive = 1 ".$sqlWHERE."
			ORDER BY tagdescription";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
    	if (!empty($_POST['tagref'])) {
    		//Solo nombre de campo para consulta de modificar y eliminar
	        $info[] = array( 'UR' => $myrow ['ur'], 'Descripcion' => $myrow ['desc_ur'],
	        	'Modificar' => '<a onclick="fnModificar('.$myrow ['ur'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
	        	'Eliminar' => '<a onclick="fnEliminar('.$myrow ['ur'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
	    }else{
	    	$info[] = array( 
	    		'Clave,5%,Clave,' => $myrow ['tagref'], 
	    		'Nombre,30%,Nombre,' => $myrow ['tagdescription'],
	    		'Razon Social,15%,Razon Social,' => $myrow ['legalname'],
	    		'Área,20%,Área,' => $myrow ['areadescription'],
	    		'Departamento,15%,Departamento,' => $myrow ['department'],
	        	'Modificar,8%,Modificar,' => '<a onclick="fnModificar('.$myrow ['tagref'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
	        	'Eliminar,7%,Eliminar,' => '<a onclick="fnEliminar('.$myrow ['tagref'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
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
	    $SQL = "UPDATE tb_cat_unidades_responsables SET desc_ur = '$descripcion' WHERE ur = '$clave'";
	    $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
	    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

	    $contenido = "Se actualizo la unidad responsable ".$clave." - ".$descripcion;
	    $result = true;
	}else{
		$SQL = "SELECT ur, desc_ur FROM tb_cat_unidades_responsables WHERE ur = '$clave' AND desc_ur = '$descripcion'";
	    $ErrMsg = "No se obtuvieron las unidades resposables";
	    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
	    if (DB_num_rows($TransResult) == 0) {
			$info = array();
		    $SQL = "INSERT INTO tb_cat_unidades_responsables (`ur`, `desc_ur`, `active`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
		    $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
		    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

		    $contenido = "Se agrego la unidad responsable ".$clave." - ".$descripcion;
		    $result = true;
		}else{
			$contenido = "Ya existe la unidad responsable con la clave ".$clave;
		    $result = true;
		}
	}
}

if ($option == 'eliminarUR') {
	$clave = $_POST['ur'];

	$info = array();
    $SQL = "UPDATE tb_cat_unidades_responsables SET active = 0 WHERE ur = '$clave'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );

    $contenido = "Se deshabilito la unidad responsable ";
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>