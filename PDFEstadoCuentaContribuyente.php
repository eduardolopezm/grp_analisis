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
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$logo_legal = $_SESSION['LogoFile'];

if($_GET['contribuyenteID'] != ''){
    $SQL = "SET NAMES 'utf8'";
    $TransResult = DB_query($SQL, $db);

    $SQLIMG = "SELECT DISTINCT legalbusinessunit.logo as logo,
	debtorsmaster.name as contribuyente
    FROM salesorders
    JOIN tags on (tags.tagref = salesorders.tagref)    
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    INNER JOIN debtorsmaster on (debtorsmaster.debtorno = salesorders.debtorno)
    WHERE salesorders.debtorno = '".$_GET['contribuyenteID']."';";

    $result = DB_query($SQLIMG,$db);
    $myrows = DB_fetch_array($result);
    $logo_legal = $myrows['logo'] != '' ? $myrows['logo'] : $logo_legal;
    $contribuyente = $myrows['contribuyente'] != '' ? $myrows['contribuyente'] : '' ;
    $nameConfing = 'CONTRIBUYENTE';

    $result2 = DB_query($SQLIMG,$db);
    $reporte = 'impresion_estado_contribuyente';

    if($_GET["tipoDescarga"]=="x"){
        $XLS = (  empty($_GET["tipoDescarga"]) ? "" : ( strtolower($_GET["tipoDescarga"])=="x" ? "_xls" : "" )  );
        $rutaReporte = $reporte.'_excel';
    }else{
        $XLS = (  empty($_GET["tipoDescarga"]) ? "" : ( strtolower($_GET["tipoDescarga"])=="x" ? "_xls" : "" )  );
        $rutaReporte = $reporte;
    }

    if(isset($_GET["fechaInicio"])){
        $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechaInicio"]);
        $newDateStringFinal = $myDateTime->format('Y-m-d');
        $mesFin = $myDateTime->format('m');
        $dia = $myDateTime->format('d');
        $anio = $myDateTime->format('Y');
    } else {
        $mesFin = date('m');
        $dia = date('d');
        $anio = date('Y');
    }

    $SQL = "SELECT UPPER(mes) as mes FROM cat_Months WHERE u_mes = '".$mesFin."'";
    $result = DB_query($SQL,$db);
    $myrows = DB_fetch_array($result);
    $fechaInicio = " ".$dia." DE ".$myrows['mes']." DEL ".$anio;

    if(isset($_GET["fechaFin"])){
        $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechaFin"]);
        $newDateStringFinal = $myDateTime->format('Y-m-d');
        $mesFin = $myDateTime->format('m');
        $dia = $myDateTime->format('d');
        $anio = $myDateTime->format('Y');
    } else {
        $mesFin = date('m');
        $dia = date('d');
        $anio = date('Y');
    }

    $SQL = "SELECT UPPER(mes) as mes FROM cat_Months WHERE u_mes = '".$mesFin."'";
    $result = DB_query($SQL,$db);
    $myrows = DB_fetch_array($result);
    $fechaFin = " ".$dia." DE ".$myrows['mes']." DEL ".$anio;
    $usuario = $_SESSION ['UserID'];
    $jreport= "";
    $JasperReport = new JasperReport($confJasper);
    $jreport = $JasperReport->compilerReport("/impresion_estado_contribuyente");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/logo_estado_tampico_color.jpg"))));
    $JasperReport->addParameter("contribuyenteID", $_GET["contribuyenteID"]);
    $JasperReport->addParameter("contribuyente", $contribuyente);
    $JasperReport->addParameter("nameConfing", $nameConfing);
    $JasperReport->addParameter("fechaInicio", $fechaInicio);
    $JasperReport->addParameter("usuario", $usuario);
    $JasperReport->addParameter("fechaFin", $fechaFin);
    $JasperReport->addParameter("dateini", date("Y-m-d", strtotime($_GET['fechaInicio']))." 00:00:00");
    $JasperReport->addParameter("datefin", date("Y-m-d", strtotime($_GET['fechaFin']))." 23:59:59");
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");
    $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
    $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);

    $pdfBytes = ( $XLS ? $JasperReport->exportReportXLS($jPrint) : $JasperReport->exportReportPDF($jPrint) );

    header('Content-type: application/'.( $XLS ? "vnd.ms-excel" : "pdf" ));
    header('Content-Length: ' . strlen($pdfBytes));
    header('Content-Disposition: inline; filename='."$reporte.".( $XLS ? "xls" : "pdf" ));

    echo $pdfBytes;
} else {
    echo "<h3>Ocurrio un problema al visualizar la información</h3>";
}
