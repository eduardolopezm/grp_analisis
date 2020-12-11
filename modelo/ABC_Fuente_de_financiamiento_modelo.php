<?php
/** 
 * Modelo para el ABC de Fuente de Financiamiento
 *
 * @category ABC
 * @package ap_grp
 * @author Jesús Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 07/11/2019
 * Fecha Modificación: 07/11/2019
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
$funcion=2252;
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
    $id_financiamiento = $_POST['id_financiamiento'];
    $sqlUR = " WHERE tb_cat_fuente_financiamiento.activo = 1 and tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 ";

    if (!empty($id_financiamiento)) {
        if (strlen($id_financiamiento) == 1) {
            $sqlUR = " WHERE tb_cat_fuente_financiamiento.activo = 1 and tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 and tb_cat_identificacion_fuente.id_identificacion = '".trim($id_identificacion)."' and tb_cat_fuente_recurso.id_fuente = '".trim($id_fuente)."' and id_financiamiento ='0".trim($id_financiamiento)."' ";
        } else {
            $sqlUR = " WHERE tb_cat_fuente_financiamiento.activo = 1 and tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 and tb_cat_identificacion_fuente.id_identificacion = '".trim($id_identificacion)."' and tb_cat_fuente_recurso.id_fuente = '".trim($id_fuente)."' and id_financiamiento ='".trim($id_financiamiento)."' ";
        }
    }
    $info = array();
    $SQL ="SELECT DISTINCT
     tb_cat_fuente_financiamiento.id_identificacion as id_identificacion, tb_cat_fuente_financiamiento.lm_fuente_financiamiento as financiamiento, tb_cat_identificacion_fuente.desc_identificacion as identificacion, 
     tb_cat_fuente_financiamiento.id_fuente as id_fuente, tb_cat_fuente_recurso.desc_fuente as fuente, 
     tb_cat_fuente_financiamiento.id_financiamiento as id_financiamiento, tb_cat_fuente_financiamiento.desc_financiamiento as desc_fin
     FROM tb_cat_fuente_financiamiento 
     JOIN tb_cat_fuente_recurso on (tb_cat_fuente_financiamiento.id_fuente = tb_cat_fuente_recurso.id_fuente and tb_cat_fuente_financiamiento.id_identificacion = tb_cat_fuente_recurso.id_identificacion )
     JOIN tb_cat_identificacion_fuente on (tb_cat_fuente_recurso.id_identificacion = tb_cat_identificacion_fuente.id_identificacion)
     ".$sqlUR."
     ORDER BY tb_cat_identificacion_fuente.id_identificacion, tb_cat_fuente_recurso.id_fuente,  tb_cat_fuente_financiamiento.id_financiamiento ASC";
    $ErrMsg = "No se obtuvieron las Subfunciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'Clave' => $myrow ['id_financiamiento'],
                'Subfuncion' => $myrow ['desc_fin'],
                'IDFuncion' => $myrow ['id_fuente'],
                'Funcion' => $myrow ['fuente'],
                'Financiamiento' => $myrow ['financiamiento'],
                'IDFinalidad' => $myrow ['id_identificacion'],
                'Finalidad' => $myrow ['identificacion'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_financiamiento'].',\''.$myrow ['desc_fin'].'\','.$myrow ['id_fuente'].',\''.$myrow ['fuente'].'\','.$myrow ['id_identificacion'].',\''.$myrow ['identificacion'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_financiamiento'].',\''.$myrow ['desc_fin'].'\','.$myrow ['id_fuente'].','.$myrow ['id_identificacion'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'IDFinalidad', type: 'string' },";
    $columnasNombres .= "{ name: 'IDFuncion', type: 'string' },";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Financiamiento', type: 'string' },";
    $columnasNombres .= "{ name: 'Subfuncion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'IDFinalidad', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fuente', datafield: 'IDFuncion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Identificador', datafield: 'Financiamiento', width: '7%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Subfuncion', width: '64%', cellsalign: 'left', align: 'center', hidden: false },";
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
        $SQL = "UPDATE tb_cat_fuente_financiamiento SET desc_financiamiento = '$descripcion', activo = 1 WHERE id_identificacion = '$finalidad' and id_fuente = '$funcion' and id_financiamiento = '$clave'";
        $ErrMsg = "No se agregó la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Fuente de Financiamiento con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($funcion , $db)){
            $SQL = "SELECT activo FROM tb_cat_fuente_financiamiento WHERE id_identificacion = '$finalidad' and id_fuente = '$funcion' and id_financiamiento = '$clave'";
            $ErrMsg = "No se obtuvieron las subfunciones";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $SQL = "INSERT INTO tb_cat_fuente_financiamiento (`id_identificacion`,`id_fuente`,`id_financiamiento`, `desc_financiamiento`, `lm_fuente_financiamiento`, `activo`)
                        VALUES ('".$finalidad."','".$funcion."','".$clave."','".$descripcion."','".$finalidad.'.'.$funcion.'.'.$clave."','1')";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$clave." del Catálogo Fuente de Financiamiento con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Fuente de Financiamiento.";
                    $contenido = "Ya existe la Fuente de Financiamiento con la clave: ".$clave;
                    $result = false;
                }else{
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$clave." del Catálogo Fuente de Financiamiento con éxito";

                    $SQL = "UPDATE tb_cat_fuente_financiamiento SET desc_financiamiento = '$descripcion', activo = 1 WHERE id_identificacion = '$finalidad' and id_fuente = '$funcion' and id_financiamiento = '$clave' and lm_fuente_financiamiento = '$finalidad.'.'.$funcion.'.'.$clave'";

                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "No existe la Fuente de Financiamiento con la clave ".$funcion;
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
    
    $SQL = "UPDATE tb_cat_fuente_financiamiento SET activo = 0 WHERE id_identificacion = '$fin' and id_fuente = '$fun' and id_financiamiento = '$clave'";
    $ErrMsg = "No se realizó:  ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo Fuente de Financiamiento con éxito";
    $result = true;
}

if ($option == 'mostrarFuncion') {
    $id_identificacion = $_POST['id_identificacion'];
    $info = array();
    $SQL = "SELECT id_fuente, CONCAT(id_fuente, ' - ', desc_fuente) as fuentedescription  FROM tb_cat_fuente_recurso WHERE activo = 1 and id_identificacion = '$id_identificacion' ORDER BY id_identificacion, id_fuente ASC ";
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_fuente' => $myrow ['id_fuente'], 'fuentedescription' => $myrow ['fuentedescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

function fnValidarExiste($idFuncion, $db){
    $SQL = "SELECT * FROM tb_cat_fuente_recurso WHERE activo = 1 and id_fuente = '".$idFuncion."'";
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
