<?php

/**
 * ABC_Jerarquias_Modelo
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 14/08/2018
 * Fecha Modificacion: 14/08/2018
 * 
 * @file: ABCBanks_Modelo.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
$funcion = 1304;
$contenido = array();
$result= ''; 
session_start();
include($PathPrefix . 'config.php');

include $PathPrefix . "includes/SecurityUrl.php";

include($PathPrefix . 'includes/ConnectDB.inc');

include($PathPrefix . 'includes/SecurityFunctions.inc');

include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');


// inclucion de modelos separados
include('./itinerarioModelo.php');


# tipo de movimiento ubicado en las tabas "systypesinvtrans" y "systypescat"
define('TYPEMOV', 501);

$option                     = $_POST['option'];
$columnasNombres            = '';
if ($option == 'mostrarBanco'){
    
    $sql = "SELECT bank_id as value,  bank_name as texto  FROM banks order by bank_name";
    $ErrMsg = "No se obtuvieron los bancos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}

if ($option == 'mostrarDescripcion'){
    $sql = "SELECT bank_id as value,  bank_shortdescription as texto  FROM banks order by bank_shortdescription";
    $ErrMsg = "No se obtuvieron los bancos";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}
if ($option == 'mostrarClave'){
    $sql = "SELECT bank_clave as value,  bank_clave as texto  FROM banks order by bank_clave";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
    $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
    $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}


if ($option == 'eliminarInformacion'){
    $idBanco      = $_POST["idBanco"]; 
    $descripcion  = $_POST["descripcion"];
    $sql          = ""; 
    $existenteBankaccounts = fnExistenteBankaccounts ($idBanco, $db );  
    if ( !$existenteBankaccounts){
        $existenteBankProveedor     = fnExistenteBankProveedor($idBanco, $db );  
        if (!$existenteBankProveedor){

            $sql = "UPDATE banks SET bank_active = 0 WHERE bank_id = $idBanco";; 
            $ErrMsg = "No se obtuvo las información";
            $TransResult = DB_query($sql, $db, $ErrMsg);
            if ($TransResult){
                $result     = true; 
            }
        } 
    }
}

if ($option == 'obtenerInformacion') {
    $banco       = $_POST["banco"];
    $descripcion = $_POST["descripcion"];
    $clave       = $_POST["clave"];

    $sqlWhere        = "";
    $ligaVer         = "";
    $ligaMod         = "";


    if ($banco != '') {
        $sqlBanco   = strpos($banco,",");
        if ( $sqlBanco === 4){
            $sqlWhere .= "  bank_id in ($banco )";
        }else{
            $sqlWhere .= "  bank_id = $banco ";
        }
    }
        
    if ($descripcion != '') {

        $sqlDescripcion  = strpos($descripcion,",");

        if ( $banco == '')
            $sqlWhere = '';
        else
            $sqlWhere .= " OR ";
        if ( $sqlDescripcion === 4){
            $sqlWhere .= "  bank_id in ($descripcion )";
        }else{
            $sqlWhere .= " Bank_id = $descripcion ";
        }
    }
    if ($clave != '') {
        $sqlClave  = strpos($clave,",");

        if ( $banco == '' && $descripcion == '')
                $sqlWhere = '';
            else
                $sqlWhere .= " OR ";

        if ( $sqlClave === 5){
             $sqlWhere .= "  bank_clave in ($clave )";
        }else{
            $sqlWhere .= "  bank_clave = $clave ";
        }
    }
    if ($banco == '' && empty($descripcion ))
    {
        if (empty($clave))
        {
            $variableAnd    = ' ';
            $finVariableANd = ' ' ;
        }
        
    }else{
        $variableAnd    = ' AND ( ';
        $finVariableANd = ' ) ' ;
    }
    $sql = " SELECT bank_id,bank_name, bank_shortdescription, bank_clave  FROM banks  WHERE bank_active =1  $variableAnd $sqlWhere $finVariableANd order by bank_clave";
    $ErrMsg      = "No se pudo consultar el catalogo de bancos"; 
    $TransResult = DB_query($sql, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {

        $urlVer     = "id=".$myrow['bank_id']."&ver=1";
        $ligaVer    =  $urlVer;
        $urlMod     = "id=".$myrow['bank_id']."&modificar=1";
        $ligaMod    =  $urlMod;
        
        $info[] = array(
            'bank_id'               =>  $myrow['bank_id'],
            'bank_name'             =>  htmlspecialchars(utf8_encode($myrow ['bank_name']), ENT_QUOTES),
            'bank_shortdescription' =>  htmlspecialchars(utf8_encode($myrow ['bank_shortdescription']), ENT_QUOTES),
            "clave"                 =>   $myrow['bank_clave'], 
            'Ver'                   => '<a href="ABCBanks_V.php?'.$ligaVer.'"><span class="glyphicon glyphicon-eye-open"></span></a>',
            'Modificar'             => '<a href="ABCBanks_V.php?'.$ligaMod.'"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar'              => '<a href="javascript:eliminar('.$myrow ['bank_id'].',\''.htmlspecialchars(utf8_encode($myrow ['bank_shortdescription']), ENT_QUOTES).'\')"><span class="glyphicon glyphicon-trash"></span></a>'
        );
    }
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'clave', type: 'string' },";
    $columnasNombres .= "{ name: 'bank_name', type: 'string' },";
    $columnasNombres .= "{ name: 'bank_shortdescription', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'clave', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Banco', datafield: 'bank_name', width: '40%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'bank_shortdescription', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '10%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'ABC_Banks_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;

}

$dataObj = array('sql' => "","contenido" => $contenido,"result"=>$result);  
echo json_encode($dataObj,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);


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
        if ($myrow['bankid']  != '')
            $existeBankAc  = true; 

    }
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
    return $existeBankAc; 
    
}


