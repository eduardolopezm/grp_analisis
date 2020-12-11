<?php
/**
 * Modelo para el ABC de Identificaciónes de la fuente
 *
 * @category ABC
 * @package ap_grp
 * @author Jesús Reyes Santos <[<email address>]>
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
$funcion=2502;
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
    $id_identificacion = $_POST['id_identificacion'];
    $sqlUR = " WHERE activo = 1 ";

    if (!empty($id_identificacion)) {
        $sqlUR = " WHERE id_identificacion = '".trim($id_identificacion)."' AND activo = 1 ";
    }
    $info = array();
    $SQL = "SELECT id_identificacion, desc_identificacion FROM tb_cat_identificacion_fuente ".$sqlUR." ORDER BY id_identificacion asc";
    $ErrMsg = "No se obtuvieron las Identificaciónes de la fuente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_identificacion'],
            'Descripcion' => $myrow ['desc_identificacion'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_identificacion'].')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_identificacion'].',\''.$myrow ['desc_identificacion'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Descripcion', width: '81%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE tb_cat_identificacion_fuente SET desc_identificacion = '$descripcion', activo = 1 WHERE id_identificacion = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Identificación de la Fuente con éxito";
        $result = true;
    } else {
        $SQL = "SELECT activo FROM tb_cat_identificacion_fuente WHERE id_identificacion = '$clave'";
        $ErrMsg = "No se obtuvieron las Identificación de la Fuente";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO tb_cat_identificacion_fuente (`id_identificacion`, `desc_identificacion`, `activo`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
            $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$clave." del Catálogo Identificación de la Fuente con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);

            if($myrow['activo']==1){
                $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Identificación de la Fuente.";
                $contenido = "Ya existe la Identificación de la Fuente con la clave ".$clave;
                $result = false;
            }else{
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro ".$clave." del Catálogo Identificación de la Fuente con éxito";

                $SQL = "UPDATE tb_cat_identificacion_fuente SET desc_identificacion = '$descripcion', activo = 1 WHERE id_identificacion = '$clave'";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}
if ($option == 'eliminarUR') {
    $cve = $_POST['id_identificacion'];
    $descripcion = $_POST['desc_identificacion'];
    $clave = (string)$cve;

    $info = array();
    $SQL = "UPDATE tb_cat_identificacion_fuente SET activo = 0 WHERE id_identificacion = '$clave'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Identificación de la Fuente con éxito";
    $result = true;
}

if ($option == 'existeFuncion') {
    $cveFin = $_POST['id_identificacion'];
    $SQL = "SELECT * FROM tb_cat_fuente_recurso WHERE activo = 1 and id_identificacion = '".$cveFin."'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $result = false;
    }else{
        $result = true;
    }
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
