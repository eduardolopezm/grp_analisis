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
session_start();

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
//header('Content-type: text/html; charset=ISO-8859-1');
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2503;
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
    $id_fuente = $_POST['id_fuente'];
    $sqlUR = " WHERE tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 ";

    if (!empty($id_fuente)) {
        $sqlUR = " WHERE tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 and tb_cat_identificacion_fuente.id_identificacion = '".trim($id_identificacion)."' and id_fuente = '".trim($id_fuente)."' ";
    }
    $info = array();
    $SQL = "SELECT DISTINCT id_fuente , desc_fuente, lm_fuente_recurso, tb_cat_identificacion_fuente.id_identificacion as idFinalidad, tb_cat_identificacion_fuente.desc_identificacion as finalidad
            FROM tb_cat_fuente_recurso
            JOIN tb_cat_identificacion_fuente on (tb_cat_fuente_recurso.id_identificacion = tb_cat_identificacion_fuente.id_identificacion)
            ".$sqlUR."
            ORDER BY tb_cat_identificacion_fuente.id_identificacion, id_fuente ASC";
    $ErrMsg = "No se obtuvieron las Fuentes del Recurso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_fuente'],
            'Funcion' => $myrow ['desc_fuente'],
            'Fuente' => $myrow ['lm_fuente_recurso'],
            'idFinalidad' => $myrow ['idFinalidad'],
            'Finalidad' => $myrow ['finalidad'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_fuente'].',\''.$myrow ['desc_fuente'].'\','.$myrow ['idFinalidad'].',\''.$myrow ['finalidad'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_fuente'].',\''.$myrow ['desc_fuente'].'\','.$myrow ['idFinalidad'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idFinalidad', type: 'string' },";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Fuente', type: 'string' },";
    $columnasNombres .= "{ name: 'Funcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'idFinalidad', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fuente', datafield: 'Fuente', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Funcion', width: '71%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}
/*
if ($option == 'mostrarCatalogo') {
    $id_identificacion = $_POST['id_identificacion'];
    $id_fuente = $_POST['id_fuente'];
    $sqlUR = " WHERE tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 ";

    if (!empty($idFuncion)) {
        $sqlUR = " WHERE tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 and tb_cat_identificacion_fuente.id_identificacion = '".trim($id_identificacion)."' and id_fuente = '".trim($id_fuente)."' ";
    }
    $info = array();
    $SQL = "SELECT DISTINCT id_fuente , desc_fuente, tb_cat_identificacion_fuente.id_identificacion as idIdentificacion, tb_cat_identificacion_fuente.desc_identificacion as identificacion
            FROM tb_cat_fuente_recurso
            JOIN tb_cat_identificacion_fuente on (tb_cat_fuente_recurso.id_identificacion = tb_cat_identificacion_fuente.id_identificacion)
            ".$sqlUR."
            ORDER BY tb_cat_identificacion_fuente.id_identificacion, id_fuente ASC";
    $ErrMsg = "No se obtuvieron las fuentes de recurso"; 
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_fuente'],
            'Funcion' => $myrow ['desc_fuente'],
            'dIdentificacion' => $myrow ['idIdentificacion'],
            'Identificacion' => $myrow ['identificacion'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_fuente'].',\''.$myrow ['desc_fuente'].'\','.$myrow ['idIdentificacion'].',\''.$myrow ['identificacion'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_fuente'].',\''.$myrow ['desc_fuente'].'\','.$myrow ['idIdentificacion'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idIdentificacion', type: 'string' },";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Funcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Identificación Fuente', datafield: 'idIdentificacion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Funcion', width: '76%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}
*/
if ($option == 'AgregarCatalogo') {
    $id_identificacion = $_POST['id_identificacion'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];
    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE tb_cat_fuente_recurso SET desc_fuente = '$descripcion', activo = 1 WHERE id_identificacion = '$id_identificacion' and  id_fuente = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Fuente del Recurso con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($id_identificacion, $db)){
            $SQL = "SELECT activo FROM tb_cat_fuente_recurso WHERE id_identificacion = '$id_identificacion' and id_fuente = '$clave' ORDER BY id_fuente ASC";
            $ErrMsg = "No se obtuvieron las unidades resposables";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $SQL = "INSERT INTO tb_cat_fuente_recurso (`id_identificacion`,`id_fuente`, `desc_fuente`, `activo`, `lm_fuente_recurso`)
                        VALUES ('".$id_identificacion."','".$clave."', '".$descripcion."', '1', '".$id_identificacion.'.'.$clave."')";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$clave." del Catálogo Fuente del Recurso con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Fuente del Recurso.";
                    $contenido = "Ya existe la fuente del recurso con la clave ".$clave;
                    $result = false;
                }else{
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$clave." del Catálogo Fuente del Recurso con éxito";

                    $SQL = "UPDATE tb_cat_fuente_recurso SET desc_fuente = '$descripcion', activo = 1 WHERE id_identificacion = '$id_identificacion' and  id_fuente = '$clave'";

                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "No existe la Identificación de la Fuente con la clave ".$id_identificacion;
            $result = false;
        }
    }
}

if ($option == 'eliminarUR') {
    $clave = $_POST['idFuente'];
    $fin = $_POST['idIdentificacion'];
    $descripcion = $_POST['descripcion'];

    $info = array();
    $SQL = "UPDATE tb_cat_fuente_recurso SET activo = 0 WHERE id_fuente = '$clave' and id_identificacion = '$fin'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Fuente del Recurso con éxito";
    $result = true;
}

if ($option == 'existeSubfuncion') {
    $cveFin = $_POST['idIdentificacion'];
    $cveFun = $_POST['idFuente'];
    $SQL = "SELECT * FROM tb_cat_fuente_financiamiento WHERE activo = 1 AND id_identificacion = '".$cveFin."' AND id_fuente = '". $cveFun."'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $result = false;
    }else{
        $result = true;
    }
}

function fnValidarExiste($idIdentificacion, $db){
    $SQL = "SELECT * FROM tb_cat_identificacion_fuente WHERE activo = 1 and id_identificacion = '".$idIdentificacion."' ORDER BY id_identificacion ASC";
    $ErrMsg = "No se encontro la informacion de ".$idIdentificacion;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFin = true;
    }else{
        $existeFin = false;
    }
    return $existeFin;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
