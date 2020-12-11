<?php

/**
 * Impresion Reporte de Reintegro
 *
 * @category Pdf
 * @package ap_grp
 * Fecha Creación: 03/10/2018
 * Fecha Modificación: 03/10/2018
 */

/*
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);*/

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
include('jasper/JasperReport.php');
//include('jasperconfig/JasperReport.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityUrl.php');
//include('NumberstoWords/number_to_words.php');
include('Numbers/Words.php');

if (isset($_GET['transno'])) {
    $Transno = $_GET['transno'];
}

if (isset($_GET['type'])) {
    $Type293 = $_GET['type'];
}

if(isset($_GET['Total'])){
    $TotalCompleto = $_GET['Total'];
}

if(isset($_GET['RMP'])){
    $tipoReintegro = $_GET['RMP'];
}

if(isset($_GET['status'])){
   $statusReintegro = $_GET['status'];
}

/**** Reporte Jasper ****/
$jreport= "";
$JasperReport = new JasperReport($confJasper);
$jreport = $JasperReport->compilerReport("rpt_reintegro");

//$V=new EnLetras();
//$montoletra=strtoupper($V->ValorEnLetras((float)$TotalCompleto,"pesos"));

//$montoletra = ValorEnLetras((float)$TotalCompleto,"pesos");

//echo "\n ".$montoletra;
//echo "\n\n";

if($tipoReintegro == 1){
    $cadenaDB = 'tb_ministracion';
}else{
    if($tipoReintegro == 2){
        $cadenaDB = '(select 0 as folio, NULL as ln_clcSiaff, NULL as ln_clcGRP, NULL as ln_clcSicop)';
    }else{
        if($tipoReintegro == 3){
            $cadenaDB = '(select 0 as folio, NULL as ln_clcSiaff, NULL as ln_clcGRP, NULL as ln_clcSicop)';
        }
    }
}

$currcode="MXN";
$monto= $TotalCompleto;

if($monto<0){
    $monto=$monto*(-1);
}

$separa = explode(".", $monto);
$montoletra = $separa [0];
$separa2 = explode(".", number_format($monto, 2)); //

$pos = strpos($monto,".");

if ($pos == true) {
    $monto =$monto; //.($separa2 [1]);
    $decimales=($separa2 [1]);
}else{
    $decimales=number_format(0, $_SESSION['DecimalPlaces'], '.', '');
    $decimales=explode(".",$decimales);
    $decimales=$decimales[1];
}

$objNumbers = new Numbers_Words(); // objeto de la Clase Numbers_Words

if ($currcode=='USD') {
    //$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
    $montoletra = $objNumbers->toWords(($montoletra), 'es');
} else {
    $montoletra = $objNumbers->toWords(($montoletra), 'es');
}

$montoGeneral=utf8_encode($montoletra);

$JasperReport->addParameter("transno", $Transno);
$JasperReport->addParameter("type293", (int)$Type293);
$JasperReport->addParameter("titles", "otros");
$JasperReport->addParameter("estatus", $statusReintegro);
$JasperReport->addParameter("TotalC", (float)$TotalCompleto);
$JasperReport->addParameter("TotalLetras", strtoupper($montoGeneral)." PESOS ".$decimales ."/100 M.N.");
$JasperReport->addParameter("cadenaB", $cadenaDB);
$JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/logoFirco.png"))));
$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");
//$JasperReport->addParameter("imagen", 'images/logoFirco.png');

$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
$pdfBytes = $JasperReport->exportReportPDF($jPrint);

header('Content-type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename=report.pdf');

echo $pdfBytes;
