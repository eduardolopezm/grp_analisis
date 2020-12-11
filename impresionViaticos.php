<?php


/**
 * Impresion viaticos
 *
 * @category Pdf
 * @package ap_grp
 * @author  ARTURO LOPEZ PEÑA <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/11/2017
 * Fecha Modificación: 02/11/2017
 * Impresion Suficiencia ManuaL y Automática
 */

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');
include("includes/SecurityUrl.php");


// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$sqllogo = "SELECT
legalbusinessunit.logo
FROM tb_viaticos
JOIN tags ON tags.tagref = tb_viaticos.tagref
JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
WHERE
tb_viaticos.sn_folio_solicitud = '".$_GET["solicitud"]."'";
$resutlogo = DB_query($sqllogo, $db);
$rowlogo = DB_fetch_array($resutlogo);

$jreport= "";
$JasperReport = new JasperReport($confJasper);

$jreport = $JasperReport->compilerReport("oficionComisionViaticos");

//number_format(($myrow['ovamount']  + $myrow['ovgst']), $_SESSION['DecimalPlaces'], '.', ',')
$JasperReport->addParameter("solicitud", $_GET["solicitud"]);

$ruta = $JasperReport->getPathFile()."".$rowlogo ['logo'];
$ruta = str_replace('jasper/', '', $ruta);
$ruta = str_replace('jasperconfig/', '', $ruta);
$JasperReport->addParameter("imagen", $ruta);
$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");

//echo $JasperReport->getPathFile();
//exit;
$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
$pdfBytes = $JasperReport->exportReportPDF($jPrint);

header('Content-type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename=report.pdf');

echo $pdfBytes;
