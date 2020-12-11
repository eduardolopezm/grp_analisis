<?php

/**
 * ABC_Conceptos_Nomina_Modelo
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 08/08/2019
 * Fecha Modificacion: 08/08/2019
 * 
 * @file: ABC_Conceptos_Nomina_Modelo.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
$funcion = 3061;
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
if ($option == 'mostrarPp'){
    
    $sql = "SELECT cppt as value, cppt as texto
                FROM tb_cat_programa_presupuestario
                WHERE id_nu_programa_presupuestario <> 4
                ORDER BY cppt ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true;     
    $contenido  = array('datos' => $info);
}


if ($option == 'mostrarPartida'){
    $sql = "SELECT partidacalculada as value,  partidacalculada as texto FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE ccap = 1 ORDER BY partidacalculada";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
    $texto = htmlspecialchars(utf8_encode($myrow ['texto']), ENT_QUOTES);
    $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}
if ($option == 'mostrarConcepto'){
    $sql = "SELECT DISTINCT clave_concepto as value,  desc_concepto as texto FROM tb_cat_concepto_nomina WHERE activo = 1 ORDER BY desc_concepto";
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
    $idMonto      = $_POST["idMonto"]; 
    $jerarquia    = $_POST["jerarquia"];
    $zonaEconomica= $_POST["zonaEconomica"];
    $sql          = ""; 
    $existenteViatico = fnExistenteViatico ($jerarquia, $zonaEconomica,$db );  
    if ( !$existenteViatico){
        $sql = "UPDATE tb_monto_jerarquia SET ind_activo = 0 WHERE id_nu_monto_jerarquia = $idMonto";; 
        $ErrMsg = "No se obtuvo las información";
        $TransResult = DB_query($sql, $db, $ErrMsg);
        if ($TransResult){
            $result     = true; 
        }
    }
}

if ($option == 'obtenerInformacion') {
    $pp              = $_POST["pp"];
    $partida         = $_POST["partida"];
    $concepto        = $_POST["concepto"];
    $comision        = ''; 
    $sqlWhere        = "";
    $ligaVer         = "";
    $ligaMod         = "";
    $info            = null; 

    if ($pp != '') {
        $sqlPpIn   = strpos($pp,",");
        
        if ( $sqlPpIn === 6){
            $sqlWhere .= " AND pp in ($pp )";
        }else{
            $sqlWhere .= " AND pp = $pp ";
        }
    }
    if ($partida != '') {
        $sqlPartidaIn  = strpos($partida,",");
        
        if ( $sqlPartidaIn === 7){
            $sqlWhere .= " AND partida in ($partida )";
        }else{
            $sqlWhere .= " AND partida = $partida ";
        }
    }
    if ($concepto != '') {
        $sqlConceptoIn  = strpos($concepto,",");

        if ( $sqlConceptoIn === 3){
            $sqlWhere .= " AND clave_concepto in ($concepto )";
        }else{
            $sqlWhere .= " AND clave_concepto = $concepto ";
        }
    }

    $sql = " SELECT  
                id_concepto_nomina, pp, partida, clave_concepto , desc_concepto as concepto,tipo_concepto , cta_contable
             FROM 
                tb_cat_concepto_nomina 
             WHERE 
                activo = 1 $sqlWhere   ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";

    $TransResult = DB_query($sql, $db, $ErrMsg);
    if ( $TransResult){ 
        while ($myrow = DB_fetch_array($TransResult)) {

            $enc        = new Encryption;

            $urlVer     = "id=".$myrow['id_concepto_nomina']."&ver=1";
            //$urlVer     = $enc->encode($urlVer);
            $ligaVer    =  $urlVer;

            $urlMod        = "id=".$myrow['id_concepto_nomina']."&modificar=1";
            //$urlMod        = $enc->encode($urlMod);
            $ligaMod       =  $urlMod;

            
        
            $info[] = array(
                'pp' =>  $myrow ['pp'],
                'partida' => $myrow ['partida'],
                'concepto'   =>  htmlspecialchars(utf8_encode($myrow ['concepto']), ENT_QUOTES),
                'cta_contable' => $myrow['cta_contable'],
                'Ver'       => '<a href="ABC_Conceptos_Nomina_V.php?'.$ligaVer.'"><span class="glyphicon glyphicon-eye-open"></span></a>',
                'Modificar' => '<a href="ABC_Conceptos_Nomina_V.php?'.$ligaMod.'"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar'  => '<a href="javascript:eliminar('.$myrow ['id_concepto_nomina'].',\''.htmlspecialchars(utf8_encode($myrow ['clave_concepto']), ENT_QUOTES).'\','.$myrow ['id_concepto_nomina'].',\''.$myrow ['pp'].'\')"><span class="glyphicon glyphicon-trash"></span></a>'
            );
        }
    }
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'pp', type: 'string' },";
    $columnasNombres .= "{ name: 'partida', type: 'string' },";
    $columnasNombres .= "{ name: 'concepto', type: 'string' },";
    $columnasNombres .= "{ name: 'cta_contable', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'PP', datafield: 'pp', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";    
    $columnasNombresGrid .= " { text: 'Partida', datafield: 'partida', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Concepto', datafield: 'concepto', width: '40%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Cuenta Contable', datafield: 'cta_contable', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '6%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'ABC_Concepto_Contable_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;


}

$dataObj = array('sql' => $sql,"contenido" => $contenido,"result"=>$result);  
  ; 
echo json_encode($dataObj,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

