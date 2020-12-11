<?php
/**
 * Impresion Compromisos
 *
 * @category Pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 23/01/2019
 * Fecha Modificación: 23/01/2019
 * Impresion Compromisos
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
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$jreport= "";
$JasperReport = new JasperReport($confJasper);
$jreport = $JasperReport->compilerReport("devengado");

$sqllogo = "SELECT legalbusinessunit.logo
FROM tb_pagos
INNER JOIN tags ON tags.tagref = tb_pagos.sn_tagref
INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
WHERE tb_pagos.nu_type = '".$_GET["type"]."' AND tb_pagos.nu_transno = '".$_GET["transno"]."'";

$resutlogo = DB_query($sqllogo, $db);
$rowlogo = DB_fetch_array($resutlogo);

$JasperReport->addParameter("transno", $_GET["transno"]);
$JasperReport->addParameter("type", $_GET["type"]);

$ruta = $JasperReport->getPathFile()."".$rowlogo ['logo'];
$ruta = str_replace('jasper/', '', $ruta);
$ruta = str_replace('jasperconfig/', '', $ruta);
$JasperReport->addParameter("imagen", $ruta);
$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");

$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
$pdfBytes = $JasperReport->exportReportPDF($jPrint);

header('Content-type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename=report.pdf');

echo $pdfBytes;
