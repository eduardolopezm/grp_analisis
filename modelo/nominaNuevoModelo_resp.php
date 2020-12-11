<?php

/**
 * megaGLJournal_Modelo.php
 *
 * @category panel
 * @package  ap_grp
 * @author   Japheth Calzada López
 * @license  [<url>] [name]
 * @version  GIT: <1234>
 * @link     (target, link)
 * Fecha creacion: 20/09/2018
 * 
 * @file: megaGLJournal_Modelo.php
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



$option                     = $_POST['option'];
$columnasNombres            = '';
$sql                        = ''; 

if ($option == 'procesarNomina'){
    $ErrMsg         = "No se obtuvo las Partidas Genéricas";
    $fechaCaptura   = $_POST["fechaCaptura"]; 
    $fechaCaptura   = date('Y-m-d', strtotime( $fechaCaptura)); 
    $noQuincena     = $_POST["noQuincena"]; 
    $tipoNomina     = $_POST["tipoNomina"]; 
    $mes            = $_POST["mes"]; 
    $fechaInicio    = $_POST["fechaInicio"];  
    $anioFiscal     = $_SESSION['ejercicioFiscal']; 
    echo "fiscal $anioFiscal"; 

    
     // obtiene un numero de transaccion
    $sql            = " SELECT ur,pp,partida,SUM(importe) AS importe,
            (SELECT accountcode FROM chartdetailsbudgetbytag
            WHERE anho=$anioFiscal AND  cppt=tb_file_nomina.pp AND partida_esp=tb_file_nomina.partida ) AS clave
            FROM
            tb_file_nomina
            WHERE tipo_concepto='P'
            GROUP BY pp,partida
            ORDER BY pp,partida ";
    $TransResult    = DB_query($sql, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $period       = GetPeriod(date('d/m/Y'), $db);
        $UR           =  $myrow ['ur'] ;  
        $totalcompra  =  $myrow ['importe'] ; 
        $clave        =  $myrow["clave"];
        $partida      =  $myrow["partida"];
        $folioCon = GetNextTransNo(305, $db);
        //Comprometido
        $folioPolizaUe  = fnObtenerFolioUeGeneral($db, $UR, '09', 305);
        $resultadoComprometido  = GeneraMovimientoContablePresupuesto(
            305, // tipo de operacion
            "POREJERCER",
            "COMPROMETIDO",
            $folioCon, // Folio consecutivo
            $period, // Periodo actual
            $totalcompra, // Total en positivo
            $UR, // UR
            $fechaCaptura, // Fecha formato YYYY-mm-dd
            $clave, // Clave
            0, // 0
            $db,
            false,
            '',
            '',
            "DISPONIBLE - COMPROMISO PARTIDA $partida", // Justicacion
            '09', // UE
            1,
            1,
            $folioPolizaUe// Folio de la poliza
        );
        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra, 258, "", 'DISPONIBLE - COMPROMISO', 1, '', 0); // Abono
        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra * -1, 259, "", 'DISPONIBLE - COMPROMISO', 1, '', 0); // Cargo

        
        //********* Devengado
        $folioCon = GetNextTransNo(305, $db);
        $folioPolizaUe  = fnObtenerFolioUeGeneral($db, $UR, '09', 305);
        GeneraMovimientoContablePresupuesto(
            305,
            "COMPROMETIDO",
            "DEVENGADO",
            $folioCon,
            $period,
            $totalcompra,
            $UR,
            $fechaCaptura,
            $clave,
            0,
            $db,
            false,
            '',
            '',
            "COMPROMETIDO-DEVENGADO PARTIDA $partida",
            '09',
            1,
            1,
            $folioPolizaUe
        );
        // Log Presupuesto
        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra, 259, "", 'COMPROMETIDO-DEVENGADO', 1, '', 0); // Abono
        $agregoLog = fnInsertPresupuestoLog($db,305, $folioCon, $UR, $clave, $period, $totalcompra * -1, 260, "",'COMPROMETIDO-DEVENGADO', 1, '', 0); // Cargo


        //********** Ejercido
        $folioCon = GetNextTransNo(305, $db);
        $folioPolizaUe  = fnObtenerFolioUeGeneral($db, $UR, '09', 305);
        GeneraMovimientoContablePresupuesto(
            305, 
            "EJERCIDO", 
            "DEVENGADO", 
            $folioCon, 
            $period, 
            $totalcompra, 
            $UR, 
            $fechaCaptura, 
            $clave, 
            0, 
            $db, 
            false, 
            '', 
            '', 
            "EJERCIDO - DEVENGADO PARTIDA $partida", 
            '09', 
            1, 
            1,
            $folioPolizaUe 
        );

        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra, 261, "", 'EJERCIDO - DEVENGADO', 1, '', 0); // Abono
        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra * -1, 260, "", 'EJERCIDO - DEVENGADO', 1, '', 0); // Cargo

        //***** Pagado *****//
        $folioPolizaUe  = fnObtenerFolioUeGeneral($db, $UR, '09', 305);
        $folioCon = GetNextTransNo(305, $db);
        GeneraMovimientoContablePresupuesto(
            305, 
            "PAGADO", 
            "EJERCIDO", 
            $folioCon, 
            $period, 
            ($totalcompra*-1),
            $UR, 
            $fechaCaptura, 
            $clave, 
            0, 
            $db, 
            false, 
            '', 
            '', 
            "EJERCIDO - PAGADO PARTIDA $partida" , 
            '09', 
            1, 
            1,
            $folioPolizaUe 
        );

        // Log Presupuesto
        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra, 265, "", 'EJERCIDO - PAGADO', 1, '', 0); // Abono
        $agregoLog = fnInsertPresupuestoLog($db, 305, $folioCon, $UR, $clave, $period, $totalcompra * -1, 261, "", 'EJERCIDO - PAGADO', 1, '', 0); // Cargo

        // Reduce disponible del presupuesto
        $folioCon  = GetNextTransNo(305, $db);
        $periodo    = GetPeriod(date('d/m/Y',strtotime($fechaInicio)), $db);
        $qty       = $totalcompra * -1; 
        $respuesta = fnInsertPresupuestoLogAcomulado($db, 305, $folioCon, $UR, $clave, $periodo,$qty ,265, "", "PAGO DE NOMINA DE LA CLAVE $clave", 1, '', 0, '09', 'DESC', 'disponible', '', 'Reduccion', 1,'', '', 0);
        echo "  $folioCon, $UR, $clave, $periodo,$qty <br> "; 
        echo "respuesta";
        var_dump($respuesta); 
    }
     //cuenta contable nomina percepciones
    $sql                        = "SELECT DISTINCT ue  FROM tb_file_nomina ";
    $TransResult                = DB_query($sql, $db, $ErrMsg);
    while ($myrow  = DB_fetch_array($TransResult)) {
        $ue               =  $myrow ['ue'] ;  
        $folioPolizaUe    = fnObtenerFolioUeGeneral($db, $UR, '09', 305);
        $generarPoliza    = fnGeneraPoliza($ue,$UR,$db,$period,$fechaCaptura,$folioPolizaUe); 
    }

    // insertar nómina en tabla
    if ( $noQuincena <10)
            $noQuincena = '0'.$noQuincena; 

    if ( $tipoNomina   == 1 ){
        $tipoNomina = 'Extraordinaria';
    }else if ( $tipoNomina   == 2 ){
    $tipoNomina = 'Ordinaria';
    }
    if ( $tipoNomina =='Ordinaria'   ){
        
        $idTipoNomina   ="O-$noQuincena"; 
    }else if ( $tipoNomina =='Extraordinaria'   ){
        $numeroExtraordinaria  = fnObtenerExtraordinaria($db,$noQuincena ); 
        $numeroExtraordinaria++; 
        if ( $numeroExtraordinaria < 10)
            $numeroExtraordinaria       =  "00".$numeroExtraordinaria;
        else if ( $numeroExtraordinaria < 100)
            $numeroExtraordinaria       =  "0".$numeroExtraordinaria;
        $idTipoNomina   ="E-$noQuincena-$numeroExtraordinaria"; 
    }
    // se inserta en el proceso de nomina
    $SQL = "INSERT INTO tb_proceso_nomina (tipo_nomina,mes_nomina,
                        quincena,
                        id_tipo_nomina,
                        fecha_proceso_nomina,
                        usuario_proceso_nomina )
                        VALUES (
                        '$tipoNomina',
                        '" . $mes . "',
                        '" . $noQuincena . "',
                        '".$idTipoNomina."',
                        ". "NOW(),"
                        . "'".$_SESSION['UserID']."')";

    $ErrMsg = "No se agrego el primer registro de la póliza del devengado";
    $TransResult2 = DB_query($SQL, $db, $ErrMsg);   
                             
    // se inserta en la tabla release
    $sql ="INSERT INTO  tb_file_nomina_release( id_tipo_nom,UR,UE,PP,partida,cve_concepto,importe,tipo_concepto)
            SELECT '$idTipoNomina',UR,UE,PP,partida,cve_concepto,importe,tipo_concepto FROM tb_file_nomina";
    $TransResult = DB_query($sql, $db, $ErrMsg);   
    $result    = true;

    $info[]     = array(); 
    $contenido  = array('datos' => $info);
}
if ($option == 'selectExtraordinaria'){
    $noQuincena     = $_POST["quincena"];
    $extraordinaria = fnObtenerExtraordinaria($db, $noQuincena);

    $result     = true; 
    $contenido  = array('datos' => $extraordinaria);
}

if ($option == 'infoArchivo'){
    $file    = $_FILES["ElementoDefault"]; 
    $ruta    =  $_SERVER['DOCUMENT_ROOT'] ; 
    $linea   = 0; 
    $archivo = fopen($file["tmp_name"], "r");
    $quincena= $_POST["quincena"]; 
    $tipoNomina = $_POST["tipoNomina"]; 
    $validarOrdinaria    = validarOrdinaria($db, $quincena);
    $validacion = false; 
    if ( $tipoNomina == 2){
        if ( $validarOrdinaria){
            $validacion = true; 
        }
    }else{
        if (!$validarOrdinaria){
            $validacion = true; 
        }
    }
   
    if ( $archivo && $validacion === false )
    {   

        $sql            = " TRUNCATE tb_file_nomina";
        $ErrMsg         = "No se obtuvo el query Solicitado";
        $TransResult    = DB_query($sql, $db, $ErrMsg);
        
        while (($datos  = fgetcsv($archivo, ",")) == true) {
            $partida        = $datos[3];
            $claveConcepto  = $datos[4]; 
            $importe        = str_replace(',','',$datos[6]);
            $tipoConcepto   = $datos[7]; 

            $num = count($datos); 
            if ( $num != 8){
                $result = false;  
                break;  
            }

            $linea++;
            if ( $linea == 1){
                continue; 
            }
            if ( $partida ==''){
                $partida = 0;
            }
            if ($claveConcepto == ''){
                $claveConcepto  = 0; 
            }
            if ( $tipoConcepto  == ''){
                $tipoConcepto   = ''; 
            }
            $sql ="INSERT INTO tb_file_nomina (UR,UE,PP,partida,cve_concepto,desc_concepto,importe,tipo_concepto)
            VALUES('$datos[0]','$datos[1]','$datos[2]', $partida,$claveConcepto ,'$datos[5]', $importe,'$tipoConcepto' )";
            
            $ErrMsg = "No se obtuvo el query Solicitado";
            $TransResult = DB_query($sql, $db, $ErrMsg);
            $result = true; 
        }
        //Cerramos el archivo
        fclose($archivo);
    }else{
        $result = false;
        if ($validacion && $tipoNomina == 2){
            $contenido ='repetido'; 
        }
        if ($validacion && $tipoNomina == 1){
            $contenido ='sin ordinaria'; 
        }
    }


}
if ($option == 'validarstore'){

    $mes         = $_POST["mes"];
    $result      = false; 
    $yearSystem  = date("Y"); 
    $ErrMsg      = "no pudo ejecutarse el sp"; 
    
        // Calculamos el periodo
    $obtenerPeriodo = fnObtenerPeriodo($mes,$yearSystem,$db );
        if($obtenerPeriodo){
            $sql          = "CALL sp_proc_nomina_1($obtenerPeriodo,$yearSystem)"; 
            $TransResult    = DB_query($sql, $db, $ErrMsg);
            while ($myrow = DB_fetch_array($TransResult)) {
                $respuestaProc  =  $myrow ['vResp'] ;  
                if ($respuestaProc == 'Cuenta con Disponible' ){
                    $result = true; 
                }
                $info[] = array( 'value' => $respuestaProc );
                $contenido  = array('datos' => $info);
            }
            
        }
        
}

if ($option == 'validarTabla'){

    $result      = false; 
    //Llamo a store para validar totales
    $sql         = "CALL sp_valida_totales();"; 
    $ErrMsg      = "No se obtuvo el SP";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
       $respuesta  =  $myrow ['vResp'] ;  
       $result= true; 
    }
    $info[] = array( 'value' => $respuesta );
    $contenido  = array('datos' => $info);
}


if ($option == 'validarOrdinaria'){
    $noQuincena         = $_POST["quincena"]; 
    $sql = "SELECT COUNT(*) AS ordinarias FROM tb_proceso_nomina WHERE tipo_nomina = 'Ordinaria' AND quincena = $noQuincena";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $ordinarias = $myrow ['ordinarias'];
        $result     = true; 
    }
   
    $contenido  = array('datos' => $ordinarias);
}


if ( $option == 'obtenerInformacionTabla'){
    $sqlWhere = ''; 
    $totales  = array();
    $ErrMsg  = "No se pudo obtener la información";
    
    //Total Percepciones
    $sql    = " SELECT SUM(importe) AS percepciones FROM tb_file_nomina  WHERE cve_concepto > 0 AND tipo_concepto = 'P'  ";
    $TransResult = DB_query($sql, $db, $ErrMsg); 

    if ( $TransResult){
        while ($myrow = DB_fetch_array($TransResult)) {
            $totalPercepciones      = "$".number_format($myrow['percepciones'],2);
          
        }
    }
    // Total deducciones
    $sql    = " SELECT SUM(importe) as deducciones FROM tb_file_nomina  WHERE cve_concepto > 0 AND tipo_concepto = 'D'";
    $TransResult = DB_query($sql, $db, $ErrMsg); 

    if ( $TransResult){
        while ($myrow = DB_fetch_array($TransResult)) {
            $totalDeduccciones      = "$".number_format($myrow['deducciones'],2);
        }
    }
    // Total Neto
    $sql    = " SELECT SUM(importe)as neto FROM tb_file_nomina  WHERE cve_concepto = 0" ;
    $TransResult = DB_query($sql, $db, $ErrMsg); 
    
    if ( $TransResult){
        while ($myrow = DB_fetch_array($TransResult)) {
            $totalNeto         = "$".number_format($myrow['neto'],2); 
            
        }
    }

    $sql    = " SELECT * FROM tb_file_nomina ";
    $TransResult = DB_query($sql, $db, $ErrMsg); 

    if ( $TransResult){
        while ($myrow = DB_fetch_array($TransResult)) {

            $info[] = array(
                'UR' =>  $myrow ['UR'],
                'UE' => $myrow ['UE'],
                'PP'   =>  $myrow ['PP'],
                'partida'=> $myrow['partida'],
                'cve_concepto'=> $myrow['cve_concepto'],
               'desc_concepto'=> htmlspecialchars(utf8_encode($myrow ['desc_concepto']), ENT_QUOTES),
                'importe'=> "$".number_format($myrow['importe'],2),
                'tipo_concepto'=> $myrow['tipo_concepto']
            );
          
        }
    }
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'UR', type: 'string' },";
    $columnasNombres .= "{ name: 'UE', type: 'string' },";
    $columnasNombres .= "{ name: 'PP', type: 'string' },";
    $columnasNombres .= "{ name: 'partida', type: 'string' },";
    $columnasNombres .= "{ name: 'cve_concepto', type: 'string' },";
    $columnasNombres .= "{ name: 'desc_concepto', type: 'string' },";
    $columnasNombres .= "{ name: 'importe', type: 'string' },";
    $columnasNombres .= "{ name: 'tipo_concepto', type: 'string' }";
    $columnasNombres .= "]";
    
    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'UR', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'UE', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'PP', datafield: 'PP', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida', datafield: 'partida', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave Concepto', datafield: 'cve_concepto', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción Concepto', datafield: 'desc_concepto', width: '30%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Importe', datafield: 'importe', width: '10%', cellsalign: 'right', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Concepto', datafield: 'tipo_concepto', width: '10%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = 'ServiciosPersonales_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel,'totalPercepciones'=>$totalPercepciones, 'totalDeducciones'=> $totalDeduccciones, 'totalNeto'=> $totalNeto);
    $result = true;

}
if ($option == 'obtenerInformacion') {
    $sqlWhere = ''; 

    $info[] = array();
    $sql    = " ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'ur', type: 'string' },";
    $columnasNombres .= "{ name: 'ue', type: 'string' },";
    $columnasNombres .= "{ name: 'pp', type: 'string' },";
    $columnasNombres .= "{ name: 'partida', type: 'string' },";
    $columnasNombres .= "{ name: 'clave_concepto', type: 'string' },";
    $columnasNombres .= "{ name: 'desc_concepto', type: 'string' },";
    $columnasNombres .= "{ name: 'importe', type: 'string' },";
    $columnasNombres .= "{ name: 'tipo_concepto', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid  = "[";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'ur', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'ue', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'PP', datafield: 'pp', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida', datafield: 'partida', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave Concepto', datafield: 'clave_concepto', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción Concepto', datafield: 'desc_concepto', width: '30%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Importe', datafield: 'importe', width: '10%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo Concepto', datafield: 'tipo_concepto', width: '10%', cellsalign: 'left', align: 'center', hidden: false }";
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

          while ($myrow = DB_fetch_array($result) ) {
            $data["zona"]   = $myrow["ln_descripcion"];
            $data["idZona"] = $myrow["id_nu_zona_economica"];
          }          

     } else {
          DB_Txn_Rollback($db);
     } 
     return $data; 
}

function fnObtenerPeriodo ($mes,$yearSystem,$db){
    $periodno  =  '' ;  
    $ErrMsg    = "No se obtuvo el SP";
    $sql       = "SELECT periodno FROM periods WHERE  MONTH(lastdate_in_period) = $mes AND YEAR(lastdate_in_period) = $yearSystem";
    $sqlRespuesta = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($sqlRespuesta)) {
        $periodno  =  $myrow ['periodno'] ;  
    }
    return $periodno; 
}
function fnGeneraPoliza ($ue,$UR,$db,$period,$fechaCaptura,$folioPolizaUe){
    $cuentaContable = null; 
    $sql       = " SELECT  ue, tb_file_nomina.desc_concepto,importe,cta_contable,tb_file_nomina.tipo_concepto FROM tb_file_nomina INNER  JOIN tb_cat_concepto_nomina ON cve_concepto = clave_concepto WHERE  activo = 1 AND importe > 0 
    AND tb_file_nomina.pp = tb_cat_concepto_nomina.PP AND tb_file_nomina.partida = tb_cat_concepto_nomina.partida AND ue = '$ue' ";
    $ErrMsg   = ''; 
    $sqlRespuesta = DB_query($sql, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($sqlRespuesta)) {

        
        $tipoConcepto        = $myrow["tipo_concepto"]; 
        $importe             = $myrow ["importe"]; 
        if ( $tipoConcepto == 'D' || $tipoConcepto == ''){
            $importe = "-".$importe; 
        }
        if ( $myrow["cta_contable"] != null)
        {
            $arrayCuentaContable = explode(".", $myrow["cta_contable"]) ; 
            $cuentaContable      = $arrayCuentaContable[0].".".$arrayCuentaContable[1].".".$arrayCuentaContable[2].".".$arrayCuentaContable[3].".".$arrayCuentaContable[4].".".$myrow["ue"].".".$arrayCuentaContable[6].".".$arrayCuentaContable[7].".".$arrayCuentaContable[8]; 
        }
        $SQL = "INSERT INTO gltrans (type,
                        typeno,
                        trandate,
                        periodno,
                        account,
                        narrative,
                        amount,
                        tag,
                        dateadded,
                        userid,
                        posted,
                        ln_ue,
                        nu_folio_ue)
                        VALUES ('305',
                        '1',
                        '$fechaCaptura',
                        '" . $period . "',
                        '".$cuentaContable."',
                        'Poliza " . $myrow ["desc_concepto"]. "',
                        '" . $importe . "',
                        '" . $UR . "',"
                        . "NOW(),"
                        . "'".$_SESSION['UserID']."',
                        '1',
                        '".$myrow["ue"]."',
                        '".$folioPolizaUe."')";

            $ErrMsg = "No se agrego el primer registro de la póliza del devengado";
            $TransResult2 = DB_query($SQL, $db, $ErrMsg);
    
    }

}
function fnObtenerExtraordinaria($db,$noQuincena){

    $extraordinaria  =  '' ;  
    $ErrMsg          = "No se obtuvo extraordinaria";
    $sql             = " SELECT COUNT(*) AS extraordinaria FROM tb_proceso_nomina WHERE tipo_nomina = 'Extraordinaria' AND  quincena =$noQuincena ";
    $sqlRespuesta    = DB_query($sql, $db, $ErrMsg);
    while ($myrow  = DB_fetch_array($sqlRespuesta)) {
        $extraordinaria  =  $myrow ['extraordinaria'] ;  
    }
    return $extraordinaria; 
}
function validarOrdinaria($db, $quincena){
    $result  = false; 
    $sql =   "SELECT COUNT(*) AS ordinarias FROM tb_proceso_nomina WHERE tipo_nomina = 'Ordinaria' AND quincena = $quincena";
    $ErrMsg = "No se obtuvo las Partidas Genéricas";
    $TransResult = DB_query($sql, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $ordinarias = $myrow ['ordinarias'];
        
    }
    if ( $ordinarias > 0){
        $result     = true; 
    }
    return $result; 
}