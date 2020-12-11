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
 * Fecha Creación: 05/07/2018

 * Se realizan operación pero el Alta, Baja y Modificación Jerarquia
 */


session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

$funcion = 1304;
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


if ($option == 'modificarBancos') {
    $idBanco    = $_POST["idBanco"]; 
    $arrayBanco = $_POST['arrayBanco'];

    $banco                = $arrayBanco['banco'];
    $descripcion          = $arrayBanco['descripcion'];
    $estatus              = $arrayBanco['estatus'];
    
    if ($estatus == 0){

        $existenteBankaccounts = fnExistenteBankaccounts ($idBanco, $db );  
        if ( !$existenteBankaccounts){
            $existenteBankProveedor     = fnExistenteBankProveedor($idBanco, $db );  
           
            if (!$existenteBankProveedor){
                $sql    = "UPDATE banks SET  bank_active = $estatus,bank_name='$banco',bank_shortdescription= '$descripcion'  WHERE bank_id = $idBanco"; 
                $result = true;
            }else{
                $ErrMsg  = "Hay transacciones relacionadas con esta cuenta";
            }
        }else{
            $ErrMsg  = "Hay transacciones relacionadas con esta cuenta";
        }
        
    }else{
        $sql    = "UPDATE banks SET  bank_active = $estatus,bank_name='$banco',bank_shortdescription= '$descripcion' WHERE bank_id = $idBanco"; 
        $result = true;
    }
    $ErrMsg = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    $contenido = $stockid;
   
 
    
}

if ($option == 'guardarBanco') {
    $arrayBanco = $_POST['arrayBanco'];
    $result     = false; 
    $banco      = $arrayBanco['banco'];
    $descripcion= $arrayBanco['descripcion'];
    $clave      = $arrayBanco['clave'];


    $cadena = "('$banco' ,1,'$descripcion','$clave')";
    $existenteBanco = fnExistenteBanco($banco, $db); 

    if (!$existenteBanco) {
        $existenteDescripcion  = fnExistenteDescripcion($descripcion, $db); 

        if (!$existenteDescripcion){
            $existeClave       = fnExistenteClave($clave, $db); 
            if (!$existeClave){
                $existeEnTabla    = fnExisteEnTabla($banco,$descripcion,$clave, $db); 
                if($existeEnTabla == 0){
                     $SQL = "INSERT INTO banks (
                        bank_name,
                        bank_active,
                        bank_shortdescription,
                        bank_clave )
                        VALUES ". $cadena; 
                        $InsResult = DB_query($SQL, $db);
                        $result =true; 
                }else{
                    $SQL = "UPDATE banks SET  bank_active = 1,bank_name='$banco',bank_shortdescription= '$descripcion',bank_clave = '$clave'  WHERE bank_id = $existeEnTabla"; 
                        $InsResult = DB_query($SQL, $db);
                        $result =true; 
                        
                }
               
            }else{
                $ErrMsg   = "La clave del banco ya esta registrada";
            }
        }else{
            $ErrMsg   = "La descripción del banco ya esta registrada";
        }
    }else{
        $ErrMsg   = "El nombre del banco ya esta registrado";
    }
}

if ($option == 'obtenerDatos') {
    $idBanco = $_POST['idBanco'];
    $contenido = array('datos' => fnDatos($idBanco, $db));
    $result = true;
}


if ($option == 'mostrarBanco') {
    $info = array();
    $SQL = "SELECT bank_id as value,bank_name as texto  FROM banks  WHERE bank_active =1";
    $ErrMsg = "No se obtuvo el Banco";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info); 
    $result = true;
}

if ($option == 'mostrarDescripcion') {
    $info = array();
    $SQL = "SELECT bank_id as value,  bank_shortdescription as texto FROM banks WHERE bank_active=1 ";
    $ErrMsg = "No se obtuvo la descripcion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'mostrarEstatus') {
    $SQL        = "Sin Query"; 
    $estatus[]  = array( 'value' =>1, 'texto' =>"Activo" );
    $estatus[]  = array( 'value' =>0, 'texto' =>"Inactivo" );
    $result     = true; 
    $contenido  = array('datos' => $estatus);
}

if ($option == 'mostrarClave') {
    $info = array();
    $SQL = "SELECT bank_clave as value,  bank_clave as texto FROM banks WHERE bank_active=1 ";
    $ErrMsg = "No se obtuvo la descripcion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}


function fnExistenteBanco($banco,$db){
    $validarExistente         = false; 
    $sqlWhere        = '';       
    $sqlWhere       .= " bank_active = 1 AND bank_name = '$banco' ";
    $SQL             = "SELECT bank_id   FROM banks WHERE $sqlWhere";
    $ErrMsg          = "No se obtuvo los Tipos de Productos";
    $TransResult     = DB_query($SQL, $db, $ErrMsg);
    $myrow           = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
        $contenido = 1;
        $validarExistente = true;
    }
    return $validarExistente; 
}
function fnExistenteDescripcion($descripcion,$db){
    $validarExistente         = false; 
    $sqlWhere       = ''; 
    $sqlWhere       .= " bank_active = 1 AND bank_shortdescription = '$descripcion' ";
    $SQL            = "SELECT bank_id   FROM banks WHERE $sqlWhere";
    $ErrMsg         = "No se obtuvo los Tipos de Productos";
    $TransResult    = DB_query($SQL, $db, $ErrMsg);
    $myrow          = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
        $contenido = 1;
        $validarExistente = true;
    }
    return $validarExistente; 
}
function fnExistenteClave($clave,$db){
    $validarExistente         = false; 
    $sqlWhere       = ''; 


    $sqlWhere   .= " bank_active = 1 AND bank_clave = '$clave' ";
    $SQL         = "SELECT bank_id   FROM banks WHERE $sqlWhere";
    $ErrMsg      = "No se obtuvo los Tipos de Productos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $myrow       = DB_fetch_array($TransResult);
     
    if ($myrow[0]>0) {
        $contenido = 1;
        $validarExistente = true;
    }
    return $validarExistente; 
}

function fnExisteEnTabla($banco,$descripcion,$clave, $db){
    $validarExistente         = 0; 
    $sqlWhere       = ''; 


    $sqlWhere   .= " bank_active = 0 AND (bank_name = '$banco' OR bank_shortdescription = '$descripcion' OR bank_clave = '$clave')";
    $SQL         = "SELECT bank_id   FROM banks WHERE $sqlWhere";
    $ErrMsg      = "No se obtuvo los Tipos de Productos";

    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $validarExistente       = $myrow ['bank_id']; 
    }
    return $validarExistente; 
}

function fnDatos($idBanco, $db)
{
    $arrayDatos = array();
    $SQL = "SELECT
            bank_id,bank_name, bank_shortdescription, bank_clave
            FROM banks
            WHERE bank_active =1 AND bank_id = $idBanco";
    $result = DB_query($SQL, $db);

    if (DB_num_rows($result) > 0) {
        while ($myrow = DB_fetch_array($result)) {
           
            $arrayDatos[] = array(
                'bank_id' => $myrow ['bank_id'],
                'bank_name' => $myrow ['bank_name'],
                'bank_shortdescription' => $myrow ["bank_shortdescription"],
                'bank_clave' => $myrow ['bank_clave']); 
        }
    } else {
        $arrayDatos = "";
    }
    
    return $arrayDatos;
}

function fnExistenteBankaccounts ($idBanco, $db ) {
    $existeBankAc = false; 
    $sql = "SELECT 
                bankid
            FROM 
                bankaccounts  
               
            WHERE
                bankid = $idBanco";
    $ErrMsg     = "Error al consultar la base de Datos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ( $myrow["ch_zona_economica"] == $zonaEconomica){
            $existeBankAc  = true; 
        }
    }
    echo $existeBankAc;
    return $existeBankAc; 
    
}

function fnExistenteBankProveedor ($idBanco, $db ) {
    $existeBankAc = false; 
    $sql = "SELECT 
                nu_id
            FROM 
                tb_bancos_proveedores  
               
            WHERE
                ln_bank_id = $idBanco";
    $ErrMsg     = "Error al consultar la base de Datos"; 
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($myrow['nu_id']  != '')
            $existeBankAc  = true; 

    }
    echo $existeBankAc;
    return $existeBankAc; 
    
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);

echo json_encode($dataObj);
