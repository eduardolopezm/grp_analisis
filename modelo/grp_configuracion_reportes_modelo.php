<?php

//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);
session_start();
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
header('Content-type: text/html; charset=ISO-8859-1');

include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//
if ($abajo) {
include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion = 2261;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

$ErrMsg = _( '' );
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

$TransResult = DB_query ( $SQL, $db );



$option = $_POST['option'];

if ($option == 'mostrarConfiguracionReportesDesdeBD') {
	$sqlUR = " ";

	

    $info = array();
    $SQL = "select id_reportes, reporte, parametro, valor from config_reportes_  where tagref = " . $_POST['tagref'];
    $ErrMsg = "No se obtuvo los parametros de configuración de reportes";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
            $info[] = array( 'id_reportes' => $myrow ['id_reportes'], 'reporte' => $myrow ['reporte'],
                 'parametro' => $myrow ['parametro'], 'valor' => $myrow ['valor'], );
        
    }

    $contenido = array('datosCatalogo' => $info);
    $result = true;
}


if ($option == 'mostrarCatalogoDeCuentas') {
    $sqlUR = " ";

    

    $info = array();
    $SQL = "select accountcode, concat(accountcode, ' - ', accountname) as descripcion from chartmaster ".$sqlUR . " order by accountcode ";
    $ErrMsg = "No se obtuvo el catálogo de cuentas";
    $TransResult = DB_query ( $SQL, $db, $ErrMsg );
    while ( $myrow = DB_fetch_array ( $TransResult ) ) {
            $info[] = array( 'id_cuenta' => $myrow ['accountcode'], 'descripcion' => $myrow ['descripcion']);
        
    }

    $contenido = array('datosCatalogo' => $info);
    $result = true;
}


function fnGrupoYOrden ($aReporte, $aParametro) 
{
    $grupoyorden = ["grupo", 1];


    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPImpuestos')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPCuotasyAportacionesdeSeguridadSocial')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPContribucionesdemejoras')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPDerechos')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPProductosdeTipoCorriente')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPAprovechamientosdeTipoCorriente')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPIngresosporVentadeBienesyServicios')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPIngresosnoComprendidosenlasFraccionesdelaLeydeIngresosCausadosenEjerciciosFiscalesAnteriores')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPPendientesdeLiquidacionoPago')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPParticipacionesyAportaciones')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPTransferenciasAsignacionesySubsidiosyOtrasAyudas')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPOtrosOrigenesdeOperacion')     $grupoyorden = ["Origen", 1];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPServiciosPersonales')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPMaterialesySuministros')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPServiciosGenerales')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPTransferenciasInternasyAsignacionesalSectorPublico')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPTransferenciasalrestodelSectorPublico')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPSubsidiosySubvenciones')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPAyudasSociales')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPPensionesyJubilaciones')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPTransferenciasaFideicomisosMandatosyContratosAnalogos')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPTransferenciasalaSeguridadSocial')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPDonativos')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPTransferenciasalExteriorParticipaciones')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPAportaciones')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPConvenios')     $grupoyorden = ["Aplicación", 2];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPBienesInmueblesInfraestructurayConstruccionesenProceso')     $grupoyorden = ["grupo", 5];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPBienesMuebles')     $grupoyorden = ["grupo", 5];
    if ($aReporte == 'flujoefectivo' && $aParametro == 'RflujoefectivoPOtrosOrigenesdeInversion')     $grupoyorden = ["grupo", 5];
    return $grupoyorden;
}

/*
*fngrabarReportesConfiguracion
*Sirve para insertar los parámetros de cada reporte, si ya existe solo lo actualiza
*/
function fngrabarReportesConfiguracion($aReporte, $aParametro, $aValorArray, $db, &$SQL, &$ErrMsg, &$TransResult, &$contenido)
{ 
    
    $aValor = implode(',', $aValorArray);

     $grupoyorden = fnGrupoYOrden($aReporte, $aParametro);

    print_r($grupoyorden);
    
    
	$SQL = "select * from config_reportes_ WHERE reporte = '$aReporte' and parametro = '$aParametro' ";
        $ErrMsg = "No se obtuvo la configuración del reporte";
        $TransResult = DB_query ( $SQL, $db, $ErrMsg );
        if (DB_num_rows($TransResult) > 0) {

            $info = array();
            $SQL = "Delete from  config_reportes_ where reporte = '$aReporte' and parametro = '$aParametro' and tagref = ".$_POST['tagref'];
            
            $ErrMsg = "No se borró la información de ".$aReporte." - ".$aParametro;
            
            $TransResult = DB_query ( $SQL, $db, $ErrMsg );

            $contenido = "Se borró la información de ".$aReporte." - ".$aParametro;
            $result = true;
    		
    	} else {
    		
    	}

        $valoresAInsertar ="";
        foreach ($aValorArray as $key => $value) {
             $valoresAInsertar .= "('".$aReporte."', '".$aParametro."', '".$value."',{$_POST['tagref']}, '{$grupoyorden[0]}',{$grupoyorden[1]}),";
        }

        $valoresAInsertar = substr($valoresAInsertar, 0, -1).";";
       
        $info = array();
            $SQL = "INSERT INTO config_reportes_ (`reporte`, `parametro`, `valor`, tagref, grupo, ordengrupo)
                    VALUES {$valoresAInsertar}";

            echo $SQL;
            $ErrMsg = "No se agrego la información de ".$aReporte." - ".$aParametro;
            

            $TransResult = DB_query ( $SQL, $db, $ErrMsg );

            $contenido = "Se agrego la información de ".$aReporte." - ".$aParametro;

            
            $result = true;

}

if ($option == 'grabarConfiguracionReportes') {
	$sqlUR = " ";

	$reporte =  $_POST['reporte'];


    foreach ($_POST as $param_name => $param_val) {
        if (is_array ($param_val)) {
           // echo $param_name; print_r($param_val);
            //foreach ($param_val as $valor) {
            fngrabarReportesConfiguracion($reporte, $param_name, $param_val, $db, $SQL, $ErrMsg, $TransResult, $contenido);


            //}

        } else
    echo "Param: $param_name; Value: $param_val<br />\n";
}

    if ($reporte == " aa") {

            $RSituacionFinancieraPEfectivoyEquivalentes = $_POST['RSituacionFinancieraPEfectivoyEquivalentes'];
    $RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes = $_POST['RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes'];
    $RSituacionFinancieraPDerechosaRecibirBienesoServicios = $_POST['RSituacionFinancieraPDerechosaRecibirBienesoServicios'];
    $RSituacionFinancieraPInventarios = $_POST['RSituacionFinancieraPInventarios'];
    $RSituacionFinancieraPAlmacenes = $_POST['RSituacionFinancieraPAlmacenes'];
    $RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes = $_POST['RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes'];
    $RSituacionFinancieraPOtrosActivosCirculantes = $_POST['RSituacionFinancieraPOtrosActivosCirculantes'];
    $RSituacionFinancieraPInversionesFinancierasaLargoPlazo = $_POST['RSituacionFinancieraPInversionesFinancierasaLargoPlazo'];
    $RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo = $_POST['RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo'];
    $RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso = $_POST['RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso'];
    $RSituacionFinancieraPBienesMuebles = $_POST['RSituacionFinancieraPBienesMuebles'];
    $RSituacionFinancieraPActivosIntangibles = $_POST['RSituacionFinancieraPActivosIntangibles'];
    $RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes = $_POST['RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes'];
    $RSituacionFinancieraPActivosDiferidos = $_POST['RSituacionFinancieraPActivosDiferidos'];
    $RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes = $_POST['RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes'];
    $RSituacionFinancieraPOtrosActivosnoCirculantes = $_POST['RSituacionFinancieraPOtrosActivosnoCirculantes'];
    $RSituacionFinancieraPCuentasporPagaraCortoPlazo = $_POST['RSituacionFinancieraPCuentasporPagaraCortoPlazo'];
    $RSituacionFinancieraPDocumentosporPagaraCortoPlazo = $_POST['RSituacionFinancieraPDocumentosporPagaraCortoPlazo'];
    $RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo = $_POST['RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo'];
    $RSituacionFinancieraPTitulosyValoresaCortoPlazo = $_POST['RSituacionFinancieraPTitulosyValoresaCortoPlazo'];
    $RSituacionFinancieraPPasivosDiferidosaCortoPlazo = $_POST['RSituacionFinancieraPPasivosDiferidosaCortoPlazo'];
    $RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo = $_POST['RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo'];
    $RSituacionFinancieraPProvisionesaCortoPlazo = $_POST['RSituacionFinancieraPProvisionesaCortoPlazo'];
    $RSituacionFinancieraPOtrosPasivosaCortoPlazo = $_POST['RSituacionFinancieraPOtrosPasivosaCortoPlazo'];



    $RSituacionFinancieraPCuentasporPagaraLargoPlazo = $_POST['RSituacionFinancieraPCuentasporPagaraLargoPlazo'];
    $RSituacionFinancieraPDocumentosporPagaraLargoPlazo = $_POST['RSituacionFinancieraPDocumentosporPagaraLargoPlazo'];
    $RSituacionFinancieraPDeudaPublicaaLargoPlazo = $_POST['RSituacionFinancieraPDeudaPublicaaLargoPlazo'];
    $RSituacionFinancieraPPasivosDiferidosaLargoPlazo = $_POST['RSituacionFinancieraPPasivosDiferidosaLargoPlazo'];
    $RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo = $_POST['RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo'];
    $RSituacionFinancieraPProvisionesaLargoPlazo = $_POST['RSituacionFinancieraPProvisionesaLargoPlazo'];





    $RSituacionFinancieraPAportaciones = $_POST['RSituacionFinancieraPAportaciones'];
    $RSituacionFinancieraPDonacionesdeCapital = $_POST['RSituacionFinancieraPDonacionesdeCapital'];
    $RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio = $_POST['RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio'];
    $RSituacionFinancieraPResultadosdeEjerciciosAnteriores = $_POST['RSituacionFinancieraPResultadosdeEjerciciosAnteriores'];
    $RSituacionFinancieraPRevaluos = $_POST['RSituacionFinancieraPRevaluos'];
    $RSituacionFinancieraPReservas = $_POST['RSituacionFinancieraPReservas'];




        	fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPEfectivoyEquivalentes', $RSituacionFinancieraPEfectivoyEquivalentes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes', $RSituacionFinancieraPDerechoARecibirEfectivoyEquivalentes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDerechosaRecibirBienesoServicios', $RSituacionFinancieraPDerechosaRecibirBienesoServicios, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPInventarios', $RSituacionFinancieraPInventarios, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPAlmacenes', $RSituacionFinancieraPAlmacenes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes', $RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosCirculantes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPOtrosActivosCirculantes', $RSituacionFinancieraPOtrosActivosCirculantes, $db, $SQL, $ErrMsg, $TransResult, $contenido);



            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPInversionesFinancierasaLargoPlazo', $RSituacionFinancieraPInversionesFinancierasaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
                fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo', $RSituacionFinancieraPDerechosaRecibirEfectivooEquivalentesaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);

            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso', $RSituacionFinancieraPBienesInmueblesInfraestructurayConstruccionesenProceso, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPBienesMuebles', $RSituacionFinancieraPBienesMuebles, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPActivosIntangibles', $RSituacionFinancieraPActivosIntangibles, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes', $RSituacionFinancieraPDepreciacionDeterioroyAmortizacionAcumuladadeBienes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPActivosDiferidos', $RSituacionFinancieraPActivosDiferidos, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes', $RSituacionFinancieraPEstimacionporPerdidaoDeteriorodeActivosnoCirculantes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPOtrosActivosnoCirculantes', $RSituacionFinancieraPOtrosActivosnoCirculantes, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPCuentasporPagaraCortoPlazo', $RSituacionFinancieraPCuentasporPagaraCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDocumentosporPagaraCortoPlazo', $RSituacionFinancieraPDocumentosporPagaraCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo', $RSituacionFinancieraPPorcionaCortoPlazodelaDeudaPublicaaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPTitulosyValoresaCortoPlazo', $RSituacionFinancieraPTitulosyValoresaCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPPasivosDiferidosaCortoPlazo', $RSituacionFinancieraPPasivosDiferidosaCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo', $RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoAdministracionaCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPProvisionesaCortoPlazo', $RSituacionFinancieraPProvisionesaCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPOtrosPasivosaCortoPlazo', $RSituacionFinancieraPOtrosPasivosaCortoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);


            //pasivos no circulantes
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPCuentasporPagaraLargoPlazo', $RSituacionFinancieraPCuentasporPagaraLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDocumentosporPagaraLargoPlazo', $RSituacionFinancieraPDocumentosporPagaraLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDeudaPublicaaLargoPlazo', $RSituacionFinancieraPDeudaPublicaaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPPasivosDiferidosaLargoPlazo', $RSituacionFinancieraPPasivosDiferidosaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo', $RSituacionFinancieraPFondosyBienesdeTercerosenGarantiayoenAdministracionaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPProvisionesaLargoPlazo', $RSituacionFinancieraPProvisionesaLargoPlazo, $db, $SQL, $ErrMsg, $TransResult, $contenido);



            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPAportaciones', $RSituacionFinancieraPAportaciones, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPDonacionesdeCapital', $RSituacionFinancieraPDonacionesdeCapital, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio', $RSituacionFinancieraPActualizaciondelaHaciendaPublicaPatrimonio, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPResultadosdeEjerciciosAnteriores', $RSituacionFinancieraPResultadosdeEjerciciosAnteriores, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPRevaluos', $RSituacionFinancieraPRevaluos, $db, $SQL, $ErrMsg, $TransResult, $contenido);
            fngrabarReportesConfiguracion($reporte, 'RSituacionFinancieraPReservas', $RSituacionFinancieraPReservas, $db, $SQL, $ErrMsg, $TransResult, $contenido);
    }

    $result = true;
}


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);


?>