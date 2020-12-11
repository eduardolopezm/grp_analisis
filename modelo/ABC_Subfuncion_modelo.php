<?php
/** 
 * Modelo para el ABC de las Subfunciones
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


//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL);
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
$funcion=2249;
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
    $idFinalidad = $_POST['idFinalidad'];
    $idFuncion = $_POST['idFuncion'];
    $idSubfuncion = $_POST['idSubfuncion'];
    $sqlUR = " WHERE g_cat_sub_funcion.activo = 1 and g_cat_funcion.activo = 1 and g_cat_finalidad.activo = 1 ";

    if (!empty($idSubfuncion)) {
        if (strlen($idSubfuncion) == 1) {
            $sqlUR = " WHERE g_cat_sub_funcion.activo = 1 and g_cat_funcion.activo = 1 and g_cat_finalidad.activo = 1 and g_cat_finalidad.id_finalidad = '".trim($idFinalidad)."' and g_cat_funcion.id_funcion = '".trim($idFuncion)."' and id_subfuncion ='0".trim($idSubfuncion)."' ";
        } else {
            $sqlUR = " WHERE g_cat_sub_funcion.activo = 1 and g_cat_funcion.activo = 1 and g_cat_finalidad.activo = 1 and g_cat_finalidad.id_finalidad = '".trim($idFinalidad)."' and g_cat_funcion.id_funcion = '".trim($idFuncion)."' and id_subfuncion ='".trim($idSubfuncion)."' ";
        }
    }
    $info = array();
    $SQL ="SELECT DISTINCT
     g_cat_sub_funcion.id_finalidad as id_finalidad, g_cat_finalidad.desc_fin as finalidad, 
     g_cat_sub_funcion.id_funcion as id_funcion, g_cat_funcion.desc_fun as funcion, 
     g_cat_sub_funcion.id_subfuncion as id_subfuncion, g_cat_sub_funcion.desc_subfun as desc_subfun
     FROM g_cat_sub_funcion 
     JOIN g_cat_funcion on (g_cat_sub_funcion.id_funcion = g_cat_funcion.id_funcion and g_cat_sub_funcion.id_finalidad = g_cat_funcion.id_finalidad )
     JOIN g_cat_finalidad on (g_cat_funcion.id_finalidad = g_cat_finalidad.id_finalidad)
     ".$sqlUR."
     ORDER BY g_cat_finalidad.id_finalidad, g_cat_funcion.id_funcion,  g_cat_sub_funcion.id_subfuncion ASC";
    $ErrMsg = "No se obtuvieron las Subfunciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'Clave' => $myrow ['id_subfuncion'],
                'Subfuncion' => $myrow ['desc_subfun'],
                'IDFuncion' => $myrow ['id_funcion'],
                'Funcion' => $myrow ['funcion'],
                'IDFinalidad' => $myrow ['id_finalidad'],
                'Finalidad' => $myrow ['finalidad'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_subfuncion'].',\''.$myrow ['desc_subfun'].'\','.$myrow ['id_funcion'].',\''.$myrow ['funcion'].'\','.$myrow ['id_finalidad'].',\''.$myrow ['finalidad'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_subfuncion'].',\''.$myrow ['desc_subfun'].'\','.$myrow ['id_funcion'].','.$myrow ['id_finalidad'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'IDFinalidad', type: 'string' },";
    $columnasNombres .= "{ name: 'IDFuncion', type: 'string' },";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Subfuncion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'FI', datafield: 'IDFinalidad', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'FU', datafield: 'IDFuncion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'SF', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Subfuncion', width: '71%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $finalidad = $_POST['finalidad'];
    $funcion = $_POST['funcion'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $proceso = $_POST['proceso'];
    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE g_cat_sub_funcion SET desc_subfun = '$descripcion', activo = 1 WHERE id_finalidad = '$finalidad' and id_funcion = '$funcion' and id_subfuncion = '$clave'";
        $ErrMsg = "No se agregó la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Subfunción con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($funcion , $db)){
            $SQL = "SELECT activo FROM g_cat_sub_funcion WHERE id_finalidad = '$finalidad' and id_funcion = '$funcion' and id_subfuncion = '$clave'";
            $ErrMsg = "No se obtuvieron las subfunciones";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $SQL = "INSERT INTO g_cat_sub_funcion (`id_finalidad`,`id_funcion`,`id_subfuncion`, `desc_subfun`, `activo`)
                        VALUES ('".$finalidad."','".$funcion."','".$clave."','".$descripcion."','1')";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$clave." del Catálogo Subfunción con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Subfunción.";
                    $contenido = "Ya existe la subfunción con la clave: ".$clave;
                    $result = false;
                }else{
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$clave." del Catálogo Subfunción con éxito";

                    $SQL = "UPDATE g_cat_sub_funcion SET desc_subfun = '$descripcion', activo = 1 WHERE id_finalidad = '$finalidad' and id_funcion = '$funcion' and id_subfuncion = '$clave'";

                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "No existe la función con la clave ".$funcion;
            $result = false;
        }
    }
}

if ($option == 'eliminarUR') {
    $fin = $_POST['idFinalidad'];
    $fun = $_POST['idFuncion'];
    $clave = $_POST['idSubfuncion'];
    $descripcion = $_POST['descripcion'];

    $info = array();
    
    $SQL = "UPDATE g_cat_sub_funcion SET activo = 0 WHERE id_finalidad = '$fin' and id_funcion = '$fun' and id_subfuncion = '$clave'";
    $ErrMsg = "No se realizó:  ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Subfunción con éxito";
    $result = true;
}

if ($option == 'mostrarFuncion') {
    $idFinalidad = $_POST['idFinalidad'];
    $info = array();
    $SQL = "SELECT id_funcion, CONCAT(id_funcion, ' - ', desc_fun) as funciondescription  FROM g_cat_funcion WHERE activo = 1 and id_finalidad = '$idFinalidad' ORDER BY id_finalidad, id_funcion ASC ";
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_funcion' => $myrow ['id_funcion'], 'funciondescription' => $myrow ['funciondescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

function fnValidarExiste($idFuncion, $db){
    $SQL = "SELECT * FROM g_cat_funcion WHERE activo = 1 and id_funcion = '".$idFuncion."'";
    $ErrMsg = "No se encontro la informacion de ".$idFuncion;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFun = true;
    }else{
        $existeFun = false;
    }
    return $existeFun;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
