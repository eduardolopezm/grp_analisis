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
 * Fecha creacion: 02/07/2018
 * Fecha Modificacion: 02/07/2018
 * 
 * @file: ABC_Jerarquias_Modelo.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
$funcion = 2402;
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
if ($option == 'mostrarJerarquias'){
    
    $sql = "SELECT id_nu_jerarquia as value, sn_identificador,ln_descripcion as texto
                FROM tb_cat_jerarquia
                WHERE ind_activo=1
                ORDER BY sn_identificador ASC";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $texto = htmlspecialchars(utf8_encode($myrow ['sn_identificador']."-".$myrow ['texto']), ENT_QUOTES);
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $texto );
    }
    $result     = true; 
    $contenido  = array('datos' => $info);
}

if ($option == 'mostrarTipoComision'){
    $sql        = "Sin Query"; 
    $tipoSol[]  = array( 'value' =>1, 'texto' =>"Nacional" );
    $tipoSol[]  = array( 'value' =>2, 'texto' =>"Internacional" );
    $result     = true; 
    $contenido  = array('datos' => $tipoSol);
}
if ($option == 'mostrarTipoGasto'){
    $sql = "SELECT id_nu_zona_economica as value,  ln_descripcion as texto FROM tb_cat_zonas_economicas WHERE ind_activo=1 ORDER BY ln_descripcion";
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
    $jerarquia       = $_POST["jerarquia"];
    $tipoSol         = $_POST["tipoSol"];
    $tipoGasto       = $_POST["tipoGasto"];
    $comision        = ''; 
    $sqlWhere        = "";
    $ligaVer         = "";
    $ligaMod         = "";
    $info            = null; 

    if ($jerarquia != '') {
        $sqlJerarquiaIn   = strpos($jerarquia,",");
        if ( $sqlJerarquiaIn === 3){
            $sqlWhere .= " AND id_nu_jerarquia in ($jerarquia )";
        }else{
            $sqlWhere .= " AND id_nu_jerarquia = $jerarquia ";
        }
    }
        
    if ($tipoSol != '') {
        $sqlTipoSolIn  = strpos($tipoSol,",");
        if ( $sqlTipoSolIn === 3){
            $sqlWhere .= " AND ind_tipo in ($tipoSol )";
        }else{
            $sqlWhere .= " AND ind_tipo = $tipoSol ";
        }
    }
    if ($tipoGasto != '') {
        $sqlTipoGastoIn  = strpos($tipoGasto,",");
        if ( $sqlTipoGastoIn === 3){
            $sqlWhere .= " AND id_zona_economica in ($tipoGasto )";
        }else{
            $sqlWhere .= " AND id_zona_economica = $tipoGasto ";
        }
    }

    //// Se agrega  AND `tb_cat_zonas_economicas`.`ind_activo` = 1 para eliminar de pantalla la Zona Económica B
    $sql = " SELECT 
                id_nu_monto_jerarquia,id_nu_jerarquia,
                (SELECT sn_identificador FROM tb_cat_jerarquia WHERE tb_cat_jerarquia.id_nu_jerarquia = tb_monto_jerarquia.id_nu_jerarquia ) AS sn_identificador,
                (SELECT ln_descripcion FROM tb_cat_jerarquia WHERE tb_cat_jerarquia.id_nu_jerarquia = tb_monto_jerarquia.id_nu_jerarquia ) AS desc_jerarquia,
                (SELECT ln_descripcion FROM tb_cat_zonas_economicas WHERE tb_cat_zonas_economicas.id_nu_zona_economica = tb_monto_jerarquia.`id_zona_economica`) AS zona_economica,
                ind_tipo, amt_importe 
            FROM tb_monto_jerarquia
            LEFT JOIN `tb_cat_zonas_economicas` ON `tb_cat_zonas_economicas`.`id_nu_zona_economica` = `tb_monto_jerarquia`.`id_zona_economica`

            WHERE `tb_monto_jerarquia`.`ind_activo` = 1
            AND ( (`ind_tipo` = 1 AND `tb_cat_zonas_economicas`.`ind_activo` = 1) OR `ind_tipo` = 2 ) $sqlWhere 

            ORDER BY  
                desc_jerarquia";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    if ( $TransResult){ 
        while ($myrow = DB_fetch_array($TransResult)) {

            $enc        = new Encryption;

            $urlVer     = "id=".$myrow['id_nu_monto_jerarquia']."&ver=1";
            //$urlVer     = $enc->encode($urlVer);
            $ligaVer    =  $urlVer;

            $urlMod        = "id=".$myrow['id_nu_monto_jerarquia']."&modificar=1";
            //$urlMod        = $enc->encode($urlMod);
            $ligaMod       =  $urlMod;

            
            if ( $myrow ['ind_tipo'] == 1   )
                $comision = 'Nacional';
            if ( $myrow ['ind_tipo'] == 2   )
                $comision = 'Internacional';

            $info[] = array(
                'jerarquia' =>  htmlspecialchars(utf8_encode($myrow ['desc_jerarquia']), ENT_QUOTES),
                'tipoGasto' => $myrow ['zona_economica'],
                'tipoSol'   =>  $comision,
                'sn_identificador'=> $myrow['sn_identificador'],
                "importe"   =>  "$".number_format($myrow ["amt_importe"], 2, '.', ',') ,
                'Ver'       => '<a href="Jerarquias_V.php?'.$ligaVer.'"><span class="glyphicon glyphicon-eye-open"></span></a>',
                'Modificar' => '<a href="Jerarquias_V.php?'.$ligaMod.'"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar'  => '<a href="javascript:eliminar('.$myrow ['id_nu_monto_jerarquia'].',\''.htmlspecialchars(utf8_encode($myrow ['desc_jerarquia']), ENT_QUOTES).'\','.$myrow ['id_nu_jerarquia'].',\''.$myrow ['zona_economica'].'\')"><span class="glyphicon glyphicon-trash"></span></a>'
            );
        }
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'jerarquia', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoGasto', type: 'string' },";
    $columnasNombres .= "{ name: 'tipoSol', type: 'string' },";
    $columnasNombres .= "{ name: 'importe', type: 'string' },";
    $columnasNombres .= "{ name: 'sn_identificador', type: 'string' },";
    $columnasNombres .= "{ name: 'Ver', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'Identificador', datafield: 'sn_identificador', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";    
    $columnasNombresGrid .= " { text: 'Jerarquía', datafield: 'jerarquia', width: '40%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Zona Económica', datafield: 'tipoGasto', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo de Comisión', datafield: 'tipoSol', width: '11%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Importe', datafield: 'importe', width: '8%', cellsalign: 'right', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Ver', datafield: 'Ver', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'ABC_Jerarquias_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;

}

$dataObj = array('sql' => "","contenido" => $contenido,"result"=>$result);  
echo json_encode($dataObj,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);


function getZonaEconomica($db) {

    $data = array();

    $sql = "SELECT tb_cat_zonas_economicas.id_nu_zona_economica AS id_nu_zona_economica,ln_descripcion FROM tb_cat_zonas_economicas INNER JOIN tb_cat_entidad_federativa ON tb_cat_zonas_economicas.id_nu_zona_economica = tb_cat_entidad_federativa.id_nu_zona_economica WHERE tb_cat_entidad_federativa.id_nu_entidad_federativa=".$_POST["estado"];

     //var_export($sql);


     DB_Txn_Begin($db);

     $result = DB_query($sql, $db);

     if ($result==true) {

          DB_Txn_Commit($db);

          while ($rs = DB_fetch_array($result) ) {
            $data["zona"]   = $rs["ln_descripcion"];
            $data["idZona"] = $rs["id_nu_zona_economica"];
          }          

     } else {
          DB_Txn_Rollback($db);
     } 
     return $data; 
}
function fnExistenteViatico ($jerarquia, $zonaEconomica,$db ) {
    $existeViatico = false; 
    $sql = "SELECT 
                id_nu_jerarquia ,ch_zona_economica
            FROM 
                tb_viaticos vi  
                JOIN tb_empleados em ON vi.id_nu_empleado = em.id_nu_empleado
                JOIN tb_cat_puesto pues ON pues.id_nu_puesto = em.id_nu_puesto
                JOIN tb_solicitud_itinerario iti ON iti.id_nu_solicitud_viaticos = id_nu_viaticos
            WHERE
                id_nu_jerarquia = $jerarquia";
    $ErrMsg     = "Error al consultar la base de Datos"; 
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ( $myrow["ch_zona_economica"] == $zonaEconomica){
            $existeViatico  = true; 
        }
    }
    return $existeViatico; 
    
}