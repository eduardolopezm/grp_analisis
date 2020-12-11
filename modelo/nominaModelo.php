<?php

/**
 * nominaModelo.php
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 20/09/2018
 * 
 * @file: nominaModelo.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 
$PageSecurity = 1;
//$PageSecurity = 4;
$PathPrefix = '../';
$funcion = 500;
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

    $mes             = $_POST["mes"];
    $tipoNomina      = $_POST["tipoNomina"];
    $noQuincena      = $_POST["noQuincena"];
    $folio           = $_POST["folio"]; 
    $txtFechaInicio  = $_POST["txtFechaInicio"];
    $txtFechaFin     = $_POST["txtFechaFin"];

    $sqlWhere = ''; 

    if ($tipoNomina != '') {
        $sqltipoNomina   = strpos($tipoNomina,",");
        if ( $sqltipoNomina === 16){
            $sqlWhere .= " AND tipo_nomina in ($tipoNomina )";
        }else{
            $sqlWhere .= " AND tipo_nomina = $tipoNomina ";
        }
    }

    if ($noQuincena != '') {
        $sqlnoQuincena   = strpos($noQuincena,",");
        
        if ( $sqlnoQuincena === 3){
            $sqlWhere .= " AND quincena in ($noQuincena )";
        }else{
            $sqlWhere .= " AND quincena = $noQuincena ";
        }
    }
    if ($mes != '') {
        $sqlMes   = strpos($mes,",");
        if ( $sqlMes === 7){
            $sqlWhere .= " AND mes_nomina in ($mes )";
        }else{
            $sqlWhere .= " AND mes_nomina = $mes ";
        }
    }
 
    if ($txtFechaInicio != '' &&  $txtFechaFin != '' ){
        $txtFechaInicio = date('Y-m-d',strtotime($txtFechaInicio)); 
        $txtFechaFin    = date('Y-m-d',strtotime($txtFechaFin)); 
        $sqlWhere .= " AND  DATE_FORMAT( fecha_proceso_nomina, '%Y-%m-%d') BETWEEN '$txtFechaInicio' AND  '$txtFechaFin' ";
    }
    
    if ($folio != '')
        $sqlWhere .= " AND id_tipo_nomina = '$folio' ";
    
    $sql    = " SELECT id_proceso_nomina, tipo_nomina, quincena, id_tipo_nomina,fecha_proceso_nomina,        
                mes_nomina,usuario_proceso_nomina  
                FROM tb_proceso_nomina 
                WHERE
                 1=1
                 $sqlWhere
                ORDER BY quincena";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    $info   = null;
    if ( $TransResult){ 
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array(
                'UR'=> 'I6L',
                'id_proceso_nomina' =>  $myrow ['id_proceso_nomina'],
                'tipo_nomina' => $myrow ['tipo_nomina'],
                'quincena'   =>  $myrow ['quincena'],
                'id_tipo_nomina'=> $myrow['id_tipo_nomina'],
                "fecha_proceso_nomina"   => date("d-m-Y",strtotime($myrow ['fecha_proceso_nomina'])),
                'mes_nomina'       => htmlspecialchars(utf8_encode($myrow ['mes_nomina']), ENT_QUOTES),
                'usuario_proceso_nomina' =>  $myrow ['usuario_proceso_nomina'],
                'estatus'=> 'Procesada'
            );
        }
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'UR', type: 'string' },";
    $columnasNombres .= "{ name: 'id_proceso_nomina', type: 'string' },";
    $columnasNombres .= "{ name: 'tipo_nomina', type: 'string' },";
    $columnasNombres .= "{ name: 'quincena', type: 'string' },";
    $columnasNombres .= "{ name: 'id_tipo_nomina', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_proceso_nomina', type: 'string' },";
    $columnasNombres .= "{ name: 'mes_nomina', type: 'string' },";
    $columnasNombres .= "{ name: 'estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'usuario_proceso_nomina', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'ID', datafield: 'id_proceso_nomina', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'UR', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_proceso_nomina', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo de nómina', datafield: 'tipo_nomina', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'id_tipo_nomina', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Mes', datafield: 'mes_nomina', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'No. Quincena', datafield: 'quincena', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estatus', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Usuario', datafield: 'usuario_proceso_nomina', width: '20%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'ServiciosPersonales_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;

}

$dataObj = array('sql' => '',"contenido" => $contenido,"result"=>$result);  
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