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
 * Fecha creacion: 10/07/2018
 * Fecha Modificacion: 17/07/2018
 * 
 * @file: configuracionFirmasModelo.php
 */

session_start();
$PageSecurity = 11;
$PathPrefix = '../';

include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix.'config.php');
include($PathPrefix.'includes/ConnectDB.inc');

include($PathPrefix.'includes/SecurityFunctionsHeader.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$sql = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

$option                     = $_POST['option'];
$columnasNombres            = '';
$funcion                    = 1345;
if ($option == 'mostrarClaves'){
    
    $sql = "SELECT id as value, clave as texto
                FROM clasprog
                WHERE activo = 1
                ORDER BY clave ASC";
    $ErrMsg = "No se obtuvo las información";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars($myrow ['texto']);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}
  
if ($option == 'eliminarInformacion'){
    $idClave  = $_POST["idClave"]; 
    $validarClave   = fnValidarClave($idClave,$db);
    if (! $validarClave) 
    {
        $sql = "UPDATE clasprog SET ACTIVO = 0 WHERE id = $idClave";
        $ErrMsg = "No se obtuvo las información";
        $TransResult = DB_query($sql, $db, $ErrMsg);
        if ($TransResult){
            $result     = true; 
        }
    }
}

function fnGetIdReporte($reporte, $db){
    $idReporte      = 0; 
    $sql            = "SELECT id_nu_reportes_conac FROM tb_cat_reportes_conac  WHERE ind_activo = 1 and ln_reporte = $reporte";
    $ErrMsg         = "No se obtuvo las información";
    $TransResult    = DB_query($sql, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $idReporte =$myrow ["id_nu_reportes_conac"];

    }
    return  $idReporte; 

}

if ($option == 'mostrarPrograma'){
    $sql = "SELECT id_programa as value, 
    desc_programa AS  texto
                FROM clasprog_prog
                ORDER BY desc_programa ASC";
    $ErrMsg = "No se obtuvo las información";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlentities($myrow ["texto"]);

        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}
if ($option == 'mostrarGrupo'){
    $sql = "SELECT id as value,  name as texto FROM clasprog_grupo  ORDER BY name";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
    $texto = htmlspecialchars($myrow ['texto']);
    $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}

if ($option == 'obtenerInformacion') {
    $ur           = $_POST["ur"];
    $ue           = $_POST["ue"];
    $reportes     = $_POST["reportes"];
    $sqlWhere        = " WHERE ind_activo=1 ";
    $ligaVer         = "";
    $ligaMod         = "";

    $longReportes  = strlen ($reportes); // NO ME PERMITIA VALIDAR SI VENIA DIFERENTE DE CERO

    if ($longReportes > 3){
        $idReporte = fnGetIdReporte ($reportes,$db);
        $sqlWhere.= " AND id_nu_reportes_conac = $idReporte ";
    }
    $longUr  = strlen ($ur);

    if ($longUr > 4){
        $sqlWhere .= " AND ur=$ur";
    }

    if ($ue != "" ){
        $sqlUE  = strpos($ue,",");
        if ( $sqlUE === 4){
            $sqlWhere .= " AND ue in ($ue )";
        }else{
            $sqlWhere .= " AND ue=$ue";;
        }
    }
    $sql = " SELECT  
            id_nu_reportes_conac_firmas,
            (SELECT ln_descripcion FROM tb_cat_reportes_conac WHERE id_nu_reportes_conac = tb_reportes_conac_firmas.id_nu_reportes_conac) AS reporte,
            ur, ue
        FROM 
        tb_reportes_conac_firmas $sqlWhere"; 

    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {

        $enc        = new Encryption;
        $urlVer     = "id=".$myrow['id_nu_reportes_conac_firmas']."&ver=1";
        $ligaVer    =  $urlVer;

        $urlMod        = "id=".$myrow['id_nu_reportes_conac_firmas']."&modificar=1";
        $ligaMod       =  $urlMod;

        $info[] = array(
            'reporte'           => $myrow ['reporte'],
            'ur'                => $myrow ['ur'],
            'ue'                => $myrow ['ue'],
            'Ver'               => '<a href="configuracionFirmas_V.php?'.$ligaVer.'"><span class="glyphicon glyphicon-eye-open"></span></a>',
            'Modificar'         => '<a href="configuracionFirmas_V.php?'.$ligaMod.'"><span class="glyphicon glyphicon-edit"></span></a>'
        ); 

    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'contador', type: 'string' },";
    $columnasNombres .= "{ name: 'reporte', type: 'string' },";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Reporte', datafield: 'reporte', width: '75%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '10%', cellsalign: 'center', align: 'center', hidden: false }";
     $columnasNombresGrid .= "]";

    $nombreExcel = 'configuracion_firmas'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}
$dataObj = array('sql' => '',"contenido" => $contenido,"result"=>$result);  
echo json_encode($dataObj);
