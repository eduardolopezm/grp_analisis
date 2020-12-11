<?php
/**
 * Impresión Orden de Compra
 *
 * @category pdf
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 15/15/2017
 * Fecha Modificación: 15/15/2017
 * Impresión de la Orden de Compra
 */

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');
include("includes/SecurityUrl.php");

if (isset($_GET['error'])) {
    // Parametro para mostrar errores
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
}
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$nomUsuConsulta = "";
$sql = "SELECT realname FROM www_users WHERE userid = '".$_SESSION['UserID']."'";
$resultNomUsu = DB_query($sql, $db);
if ($myrow=DB_fetch_array($resultNomUsu)) {
    $nomUsuConsulta = $myrow['realname'];
}

$jreport= "";
$JasperReport = new JasperReport($confJasper);
$jreport = $JasperReport->compilerReport("impresion_compra");

$sqllogo = "SELECT legalbusinessunit.logo
FROM purchorders
INNER JOIN tags ON tags.tagref = purchorders.tagref
INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
WHERE purchorders.orderno = '".$_GET["OrderNo"]."'";

$resutlogo = DB_query($sqllogo, $db);
$rowlogo = DB_fetch_array($resutlogo);
$logo = $rowlogo ['logo'];

$JasperReport->addParameter("OrderNo", $_GET["OrderNo"]);
$JasperReport->addParameter("usuarioConsulta", $nomUsuConsulta);
//$JasperReport->addParameter("imagen", $JasperReport->getPathFile()."/images/logo_sagarpa_01.jpg");
//$ruta= $JasperReport->getPathFile()."images/logo_sagarpa_01.jpg";
$ruta = $JasperReport->getPathFile()."".$rowlogo ['logo'];
$ruta=str_replace('jasper/','', $ruta);
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
