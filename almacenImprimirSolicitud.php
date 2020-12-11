<?php

/*
ini_set('display_errors', 1);
ini_set('log_errors', 1);
 ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
 error_reporting(E_ALL);
/*

/**
 * Impresion de solititud para la entrega en almacen
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 23/10/2017
 * Fecha Modificación: 09/10/2017
 */
 // ini_set('display_errors', 1);
 // ini_set('log_errors', 1);
 // error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');

$todos=0;
$sqllogo = "SELECT
DISTINCT legalbusinessunit.logo, legalbusinessunit.legalname, CONCAT(tags.tagref, ' - ', tags.tagdescription) as tagdescriptionConcat
FROM tags
JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
WHERE tags.tagref = '".$_GET["ur"]."'";

$resutlogo = DB_query($sqllogo, $db);
$rowlogo = DB_fetch_array($resutlogo);


if (isset($_GET['PrintPDF'])) {
    if(isset($_GET['todos'])){
      $todos=$_GET['todos'];
    }
    
    $jreport= "";
    $JasperReport = new JasperReport($confJasper);

    switch ($todos) {
      case '0':
        $jreport = $JasperReport->compilerReport("../jasper/formato_entrega_solicitud");
        // $JasperReport->addParameter("usarioEntrega",$_SESSION['UserID']);
        break;
      
      case '1':
        $jreport = $JasperReport->compilerReport("../jasper/salidas_totales");
        break;

      default:
        # code...
        break;
    }

    /*$myDateTime = DateTime::createFromFormat("d-m-Y", $_GET["fechainicial"]);
    $newDateStringInicial = $myDateTime->format('Y-m-d');
    $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
    $newDateStringFinal = $myDateTime->format('Y-m-d');*/
    /*$JasperReport->addParameter("fechainicial", $newDateStringInicial);*/
    $JasperReport->addParameter("idsolicitud",$_GET["solicitud"]);
    $JasperReport->addParameter("nu_folio",$_GET["nu_folio"]);
    $JasperReport->addParameter("ur",$_GET["ur"]);
    $JasperReport->addParameter("urName",$rowlogo["tagdescriptionConcat"]);
    $JasperReport->addParameter("fechasolicitud",$_GET["fechasolicitud"]);
    $JasperReport->addParameter("almacen",$_GET["almacen"]);
    $JasperReport->addParameter("dependencia",$rowlogo["legalname"]);
    $JasperReport->addParameter("usuarioSolicitud",$_GET['usuariosolicitud']);

    if(isset($_GET['usuarioentrega'])){
      $JasperReport->addParameter("usuarioEntrega",$_GET['usuarioentrega']);
    }

    $ruta = $JasperReport->getPathFile()."".$rowlogo ['logo'];
    $ruta = str_replace('jasper/', '', $ruta);
    $ruta = str_replace('jasperconfig/', '', $ruta);
    
    $JasperReport->addParameter("imagen",$ruta);
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");

    $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
    $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
    $pdfBytes = $JasperReport->exportReportPDF($jPrint);

    header('Content-type: application/pdf');
    header('Content-Length: ' . strlen($pdfBytes));
    header('Content-Disposition: inline; filename=report.pdf');

    echo $pdfBytes;
}