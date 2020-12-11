<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Japheth Calzada López <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 11/07/2018

 * Se realizan operación pero el Alta, Baja y Modificación Jerarquia
 */


session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

$funcion = 1345;
include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

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

if ($option == 'modificarClas') {
    $arrayClas                = $_POST['arrayClas'];
    $idClave                  = $arrayClas['idClave'];
    $programa                 = $arrayClas['programa'];
    $grupo                 = $arrayClas['grupo'];
        
    // $cadena                   = "idprog = '".$programa."'";
    $cadena                   = "name = '".$programa."', nu_id_grupo = '".$grupo."'";

    $validarClave             = false; 
    if ($activo == 0){
        $validarClave       = false; // fnValidarClave($idClave,$db) ; 
        $ErrMsg             = "El registro ya que esta relacionado con un Programa Presupuestario"; 
        $contenido          = "presupuestario"; 
    }
    if (! $validarClave) 
    {
        $SQL            = " UPDATE clasprog SET ".$cadena." WHERE id = '".$idClave."'" ;
        $ErrMsg         = "No se obtuvo Información modificar";   
        $TransResult    = DB_query($SQL, $db, $ErrMsg);
        $contenido      = "";
        $result = true;
    }
}
function fnValidarClave($idClave,$db){
    $validar        = false; 
    $sql            = "SELECT id_nu_programa_presupuestario FROM tb_cat_programa_presupuestario  WHERE activo = 1 and id_clasprog = $idClave";
    $ErrMsg         = "No se obtuvo las información";
    $TransResult    = DB_query($sql, $db, $ErrMsg);
    $myrow          = DB_fetch_array($TransResult); 
    if ($myrow[0]>0) {
        $validar  = true;
    }
    return  $validar; 
}

if ($option == 'guardarClas') {

    $arrayClas                = $_POST['arrayClas'];
    $result                   = false; 
    $programa                 = $arrayClas['programa'];
    $descClave                = $arrayClas['descClave'];
    $grupo                 = $arrayClas['grupo'];

    $cadena                   = "('$descClave', 0, $programa, $grupo)";

    $validarExistente = fnValidarExistente($descClave, $db); 
    if (!$validarExistente) {
        $SQL = "INSERT INTO clasprog (
        clave, idprog, name, nu_id_grupo)
        VALUES ". $cadena;

        $InsResult = DB_query($SQL, $db);
        $result =true; 
    }
    else{
        $validarActualizar = fnValidarActualizar($descClave, $db); 
        $cadena            = "activo = 1, name = '$programa', nu_id_grupo = '$grupo'"; 
        if ($validarActualizar > 0) {
            $SQL        = "UPDATE clasprog SET ".$cadena." WHERE id = '".$validarActualizar."'"; 
            $InsResult  = DB_query($SQL, $db);
            $result     = true; 
        }
    }
}

if ($option == 'obtenerDatos') {
    $idClave = $_POST['idClave'];
    $contenido = array('datos' => fnDatos($idClave, $db));
    $result = true;
}

if ($option == 'mostrarClave') {
    $info = array();
    $SQL = "SELECT id as value, clave as texto
            FROM clasprog
            ORDER BY clave ASC";
    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarPrograma') {
    $info = array();
    $SQL = "SELECT id_programa as value, 
            desc_programa AS  texto
            FROM clasprog_prog
            ORDER BY desc_programa ASC";

    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarGrupo') {
    $SQL = "SELECT id as value,  name as texto FROM clasprog_grupo  ORDER BY name";
    $ErrMsg = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
     }
    $contenido = array('datos' => $info);
    $result = true;
}

function fnValidarExistente($descClave,$db){
    $validarExistente         = false; 
    $sqlWhere       = ''; 


    $sqlWhere   .= " clave = '$descClave' ";
    $SQL         = "SELECT id  FROM clasprog WHERE $sqlWhere and activo in (1,0)";
    $ErrMsg      = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow       = DB_fetch_array($TransResult); 
    if ($myrow[0]>0) {
        $contenido = 1;
        $validarExistente = true;
    }
    return $validarExistente; 
}

function fnValidarActualizar($descClave,$db){
    $validarActualizar         = 0; 
    $idClave        = 0; 
    $sqlWhere       = ''; 


    $sqlWhere   .= " clave = '$descClave' ";
    $SQL         = "SELECT id  FROM clasprog WHERE $sqlWhere and activo = 0 ";
    $ErrMsg      = "No se obtuvo Información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow       = DB_fetch_array($TransResult);
    $idClave       =  $myrow['id'];

    if (DB_num_rows($TransResult) > 0) {
        $validarActualizar  = $idClave; 
    }
    return $validarActualizar; 
}

function fnDatos($idClave, $db)
{
    $arrayDatos = array();
    $SQL = "SELECT clasprog.id AS idClave, clasprog.name as id_programa, clasprog.nu_id_grupo AS idGrupo, clasprog.clave
            FROM clasprog
            LEFT JOIN clasprog_prog ON clasprog.idprog=clasprog_prog.id_programa
            WHERE clasprog.id = '$idClave'";
    $result = DB_query($SQL, $db);

    if (DB_num_rows($result) > 0) {
        while ($myrow = DB_fetch_array($result)) {
           
            $arrayDatos[] = array(
                'idClave'       => $myrow ['idClave'],
                'id_programa'   => $myrow ['id_programa'],
                'idGrupo'       => $myrow ['idGrupo'],
                'clave'         =>  $myrow ['clave']);
        }
    } else {
        $arrayDatos = "";
    }
    
    return $arrayDatos;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
