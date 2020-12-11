<?php
/**
 * Modelo para el ABC de las Subfunciones
 *
 * @category ABC
 * @package ap_grp
 * @author Juan José Ledesma <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2019
 * Fecha Modificación: 06/11/2019
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
    $sqlUR = " WHERE clasificador_ingreso.activo = 1 and tipo_ingreso.activo = 1 and rubro_ingreso.activo = 1 ";

    if (!empty($idSubfuncion)) {
        if (strlen($idSubfuncion) == 1) {
            $sqlUR = " WHERE clasificador_ingreso.activo = 1 and tipo_ingreso.activo = 1 and rubro_ingreso.activo = 1 and rubro_ingreso.clave = '".$idFinalidad."' and tipo_ingreso.clave = '".$idFuncion."' and clasificador_ingreso.clave ='0".$idSubfuncion."' ";
        } else {
            $sqlUR = " WHERE clasificador_ingreso.activo = 1 and tipo_ingreso.activo = 1 and rubro_ingreso.activo = 1 and rubro_ingreso.clave = '".$idFinalidad."' and tipo_ingreso.clave = '".$idFuncion."' and clasificador_ingreso.clave ='".$idSubfuncion."' ";
        }
    }
    $info = array();
    $SQL ="SELECT DISTINCT
    clasificador_ingreso.id_rubro as id_finalidad, rubro_ingreso.descripcion as finalidad, 
    clasificador_ingreso.id_tipo as id_funcion, tipo_ingreso.descripcion as funcion, 
    clasificador_ingreso.clave as id_subfuncion, clasificador_ingreso.descripcion as desc_subfun,
    clasificador_ingreso.rtc as rtc
    FROM clasificador_ingreso 
    JOIN tipo_ingreso on (clasificador_ingreso.id_tipo = tipo_ingreso.clave and clasificador_ingreso.id_rubro = tipo_ingreso.id_rubro )
    JOIN rubro_ingreso on (tipo_ingreso.clave = rubro_ingreso.clave)
     ".$sqlUR."
     ORDER BY rubro_ingreso.clave, tipo_ingreso.id_rubro,  clasificador_ingreso.clave ASC";
    $ErrMsg = "No se obtuvieron las Subfunciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'Clave' => $myrow ['id_subfuncion'],
                'Subfuncion' => $myrow ['desc_subfun'],
                'IDFuncion' => $myrow ['id_funcion'],
                'Funcion' => $myrow ['funcion'],
                'rtc' => $myrow ['rtc'],
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
    $columnasNombres .= "{ name: 'rtc', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Rubro', datafield: 'IDFinalidad', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo', datafield: 'IDFuncion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clasificador', datafield: 'Clave', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clasificador Rubro', datafield: 'rtc', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Subfuncion', width: '54%', cellsalign: 'left', align: 'center', hidden: false },";
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
    $rtc = $finalidad.'.'.$funcion.'.'.$clave;

    if ($proceso == 'Modificar') {
        $info = array();
        $sql2 = "SELECT activo, rtc FROM clasificador_ingreso WHERE  clave = '$clave'";
        $ErrMsg = "No se obtuvieron los clasificador Ingreso Tipo";
        $TransResult2 = DB_query($sql2, $db, $ErrMsg);
        $myrow2 = DB_fetch_array($TransResult2);

        $SQL = "UPDATE clasificador_ingreso SET descripcion = '$descripcion', activo = 1 WHERE id_rubro = '$finalidad' and id_tipo = '$funcion' and clave = '$clave'";
        $ErrMsg = "No se agregó la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$myrow2["rtc"]." del catálogo de Clasificador por Rubro de Ingreso con éxito";
        $result = true;
    } else {
        if(!fnValidarExiste($rtc, $db)){
            $SQL = "SELECT activo, rtc FROM clasificador_ingreso WHERE id_rubro = '$finalidad' and id_tipo = '$funcion' and clave = '$clave'";
            $ErrMsg = "No se obtuvieron los clasificador Ingreso Tipo";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $myrow2 = DB_fetch_array($TransResult);

            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $SQL = "INSERT INTO clasificador_ingreso (`id_rubro`,`id_tipo`,`clave`,`rtc`, `descripcion`, `activo`)
                        VALUES ('".$finalidad."','".$funcion."','".$clave."','".$finalidad.".".$funcion.".".$clave."','".$descripcion."','1')";
                $ErrMsg = "No se agrego la informacion de ".$rtc." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó ".$rtc." del catálogo de Clasificador por Rubro de Ingreso con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$myrow2["rtc"]." del Catálogo CRI.";
                    $contenido = "Ya existe la subfunción con la clave: ".$myrow2["rtc"];
                    $result = false;
                }else{
                    $Mensaje = "Proceso completado.";
                    $contenido = "Se agregó el registro ".$myrow2["rtc"]." del catálogo de Clasificador por Rubro de Ingreso";

                    $SQL = "UPDATE clasificador_ingreso SET descripcion = '$descripcion', activo = 1 WHERE id_rubro = '$finalidad' and id_tipo = '$funcion' and clave = '$clave'";

                    $TransResult = DB_query($SQL, $db, $ErrMsg);

                    $result = true;
                }
            }
        }else{
            $contenido = "Ya existe el Clasificador Tipo Ingreso con la clave ".$rtc;
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
    
    $SQL = "UPDATE clasificador_ingreso SET activo = 0 WHERE id_rubro = '$fin' and id_tipo = '$fun' and clave = '$clave'";
    $ErrMsg = "No se realizó:  ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$clave." del Catálogo CRI con éxito";
    $result = true;
}

if ($option == 'mostrarFuncion') {
    $idFinalidad = $_POST['idFinalidad'];
    $info = array();
    $SQL = "SELECT clave, CONCAT(clave, ' - ', descripcion) as tipoDescripcion  FROM tipo_ingreso WHERE activo = 1 and id_rubro = '$idFinalidad' ORDER BY clave ASC ";
    $ErrMsg = "No se obtuvo el Tipo";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_funcion' => $myrow ['clave'], 'funciondescription' => substr($myrow ['tipoDescripcion'], 0,70));
    }

    $contenido = array('datos' => $info);
    $result = true;
}

function fnValidarExiste($rtc, $db){
    $SQL = "SELECT * FROM clasificador_ingreso WHERE activo = 1 and rtc = '$rtc'";
    $ErrMsg = "No se encontro la informacion de ".$rtc;
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
