<?php

/**
 * Impresion Reporte de Conciliacion
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
//include('Numbers/Words.php');

if (isset($_GET['tagref'])) {
    $tagref = $_GET['tagref'];
}

if (isset($_GET['ln_ue'])) {
    $ln_ue = $_GET['ln_ue'];
}

if (isset($_GET['folio'])) {
    $folio = $_GET['folio'];
}

if (isset($_GET['clave'])) {
    $clave = $_GET['clave'];
}

/*echo "\n ".$tagref;
echo "\n ".$ln_ue;
echo "\n ".$folio;
echo "\n ".$clave;
echo "\n\n";
exit();
*/

/**** Reporte Jasper ****/
$jreport= "";
$JasperReport = new JasperReport($confJasper);
$jreport = $JasperReport->compilerReport("rpt_conciliacion_bancaria");

//$JasperReport->addParameter("tagref", $tagref);
//$JasperReport->addParameter("ln_ue", $ln_ue);
$JasperReport->addParameter("folio", (int)$folio);
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
