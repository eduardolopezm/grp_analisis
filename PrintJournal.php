<?php

/*error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');
*/

/*chinga tu madre */


/**
 * Impresión Poliza Contable
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 15/10/2017
 * Fecha Modificación: 15/10/2017
 * Archivo para impresión de la Poliza Contable
 */

$PageSecurity = 1;
include('config.php');
require "includes/SecurityUrl.php";
include('includes/session.inc');

// include('includes/SQL_CommonFunctions.inc');
/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');
*/

// if (isset($_GET['error'])) {
    // Ver errores
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    // ini_set('log_errors', 1);
// }

if (isset($_GET['PrintPDF'])) {
    $FromCust = $_GET ['FromCust'];
    $ToCust = $_GET ['ToCust'];
    $PrintPDF = $_GET ['PrintPDF'];
    $_POST ['FromCust'] = $FromCust;
    $_POST ['ToCust'] = $ToCust;
    $_POST ['PrintPDF'] = $PrintPDF;
    // include('jasperconfig/JasperReport.php');
    include('jasper/JasperReport.php');

    $JasperReport = new JasperReport($confJasper);
    $jreport = $JasperReport->compilerReport("../jasper/PrintJournalJasper");

    $sqllogo = "SELECT legalbusinessunit.logo
	FROM gltrans
	INNER JOIN tags ON gltrans.tag = tags.tagref
	INNER JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
	WHERE typeno='" . $_GET ["TransNo"] . "' and type='" . $_GET ["type"] . "'";

    $resutlogo = DB_query($sqllogo, $db);
    $rowlogo = DB_fetch_array($resutlogo);
    $logo = $rowlogo ['logo'];
    $logo = "/var/www/html" . $rootpath . "/" . $logo;

    $JasperReport->addParameter("transno", $_GET ["TransNo"]);
    $JasperReport->addParameter("type", $_GET ["type"]);
    $JasperReport->addParameter("folioUe", $_GET ["folioUe"]);
    $JasperReport->addParameter("ue", $_GET ["ue"]);
    //$JasperReport->addParameter("logo", $logo);

    $ruta = $JasperReport->getPathFile()."".$rowlogo ['logo'];
    $ruta = str_replace('jasper/', '', $ruta);
    $ruta = str_replace('jasperconfig/', '', $ruta);
    $JasperReport->addParameter("logo", $ruta);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");

    if ($_SESSION ['UserID'] == "desarrollo") {
        //echo '<br><pre>getConexionDB<br>' . 'Database: ' . $_SESSION ["DatabaseName"] . ' <br>Usuario: ' . $dbuser . '<br>Password: ' . $dbpassword;
        // echo "<br>ruta: ".$ruta;
        // exit();
    }

    /*echo "DB: ".$_SESSION ["DatabaseName"]."<br>";
	echo " ".$dbuser."<br>";
	echo " ".$dbpassword."<br>";*/

    $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
    $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
    $pdfBytes = $JasperReport->exportReportPDF($jPrint);

    header('Content-type: application/pdf');
    header('Content-Length: ' . strlen($pdfBytes));
    header('Content-Disposition: inline; filename=report.pdf');

    echo $pdfBytes;
}
