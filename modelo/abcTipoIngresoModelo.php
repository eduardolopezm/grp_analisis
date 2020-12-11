<?php
/**
 * Modelo para el ABC de Funcion
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
$funcion=2500;
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
    $claveRubro = $_POST['claveRubro'];
    $idTipo = $_POST['idTipo'];
    $sqlUR = " WHERE tipo_ingreso.activo = 1 and rubro_ingreso.activo = 1 ";

    if (!empty($idTipo)) {
        $sqlUR = " WHERE tipo_ingreso.activo = 1 and rubro_ingreso.activo = 1 and rubro_ingreso.clave =  '".$claveRubro."' and tipo_ingreso.clave = '".$idTipo."' ";
    }
    $info = array();
        $SQL = "SELECT DISTINCT tipo_ingreso.clave as clave, tipo_ingreso.descripcion as descripcion, rubro_ingreso.clave as claveRubro, rubro_ingreso.descripcion as rubroDescripcion
            FROM tipo_ingreso
            JOIN rubro_ingreso on (tipo_ingreso.id_rubro = rubro_ingreso.clave)
            ".$sqlUR."
            ORDER BY rubro_ingreso.clave, clave ASC";
    $ErrMsg = "No se obtuvieron las Funciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'descripcion' => $myrow ['descripcion'],
            'claveRubro' => $myrow ['claveRubro'],
            'tipoIngreso' => $myrow ['clave'],
            'rt' => $myrow ['claveRubro'].'.'.$myrow ['clave'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['clave'].',\''.$myrow ['descripcion'].'\','.$myrow ['claveRubro'].',\''.$myrow ['rubroDescripcion'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['clave'].',\''.$myrow ['descripcion'].'\','.$myrow ['claveRubro'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
            
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'claveRubro', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoIngreso', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'rt', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Rubro', datafield: 'claveRubro', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'tipoIngreso', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Rubro Ingreso', datafield: 'rt', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion', width: '68%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $claveRubro = $_POST['claveRubro'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];
    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE tipo_ingreso SET descripcion = '$descripcion', activo = 1 WHERE id_rubro = '$claveRubro' and  clave = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$claveRubro.".".$clave." del Catálogo Tipo con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($claveRubro, $db)){
            $SQL = "SELECT activo FROM tipo_ingreso WHERE id_rubro = '$claveRubro' and clave = '$clave' ORDER BY id ASC";
            $ErrMsg = "No se obtuvieron las unidades resposables";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $SQL = "INSERT INTO tipo_ingreso (`id_rubro`,`clave`, `descripcion`, `activo`)
                        VALUES ('".$claveRubro."','".$clave."', '".$descripcion."', '1')";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$claveRubro.".".$clave." del Catálogo Tipo Ingreso con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Tipo Ingreso.";
                    $contenido = "Ya existe la función con la clave ".$claveRubro.".".$clave;
                    $result = false;
                }else{
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$claveRubro.".".$clave." del Catálogo Tipo Ingreso con éxito";

                    $SQL = "UPDATE tipo_ingreso SET descripcion = '$descripcion', activo = 1 WHERE id_rubro = '$claveRubro' and  clave = '$clave'";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "No existe la finalidad con la clave ".$claveRubro;
            $result = false;
        }
    }
}

if ($option == 'eliminarUR') {
    $clave = $_POST['idTipo'];
    $fin = $_POST['claveRubro'];
    $descripcion = $_POST['descripcion'];

    $info = array();
    $SQL = "UPDATE tipo_ingreso SET activo = 0 WHERE clave = '$clave' and id_rubro = '$fin'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$fin.".".$clave." del Catálogo Tipo con éxito";
    $result = true;
}

if ($option == 'existeSubfuncion') {
    $cveFin = $_POST['claveRubro'];
    $cveFun = $_POST['idTipo'];
    $SQL = "SELECT * FROM g_cat_sub_funcion WHERE activo = 1 AND id = '".$cveFin."' AND id = '". $cveFun."'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $result = false;
    }else{
        $result = true;
    }
}
function fnValidarExiste($claveRubro, $db){
    $SQL = "SELECT * FROM rubro_ingreso WHERE activo = 1 and clave = '".$claveRubro."' ORDER BY id ASC";
    $ErrMsg = "No se encontro la informacion de ".$claveRubro;
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
