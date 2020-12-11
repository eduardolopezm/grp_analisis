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
include ('Numbers/Words.php');

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


if($_GET['placa'] != ''){
    $SQL = "SET NAMES 'utf8'";
    $TransResult = DB_query($SQL, $db);

    $SQLIMG = "SELECT DISTINCT legalbusinessunit.logo as logo,
	debtorsmaster.name as contribuyente,
	CONCAT(configContrato.id_loccode,' - ', locations.locationname) AS nameConfing
    FROM tb_propiedades_atributos as propAttr
    JOIN tb_contratos AS contratos  on contratos.id_contrato = propAttr.id_folio_contrato
    JOIN tags on (tags.tagref = contratos.tagref)    
    JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
    INNER JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)
    INNER JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos)
    INNER JOIN locations on (configContrato.id_loccode = locations.loccode)
    WHERE propAttr.ln_valor = '".$_GET['placa']."';";

    $result = DB_query($SQLIMG,$db);
    $myrows = DB_fetch_array($result);
    $logo_legal = $myrows['logo'] != '' ? $myrows['logo'] : $logo_legal;
    $contribuyente = $myrows['contribuyente'] != '' ? $myrows['contribuyente'] : '' ;
    $nameConfing = $myrows['nameConfing'] != '' ? $myrows['nameConfing'] : '';

    $result2 = DB_query($SQLIMG,$db);

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

    $jreport= "";
    $JasperReport = new JasperReport($confJasper);
    $jreport = $JasperReport->compilerReport("/impresion_estado_contratos");
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/logo_estado_tampico_color.jpg"))));
    $JasperReport->addParameter("placa", $_GET["placa"]);
    $JasperReport->addParameter("contribuyente", $contribuyente);
    $JasperReport->addParameter("fechaInicio", $fechaInicio);
    $JasperReport->addParameter("fechaFin", $fechaFin);
    $JasperReport->addParameter("dateini", date("Y-m-d", strtotime($_GET['fechaInicio']))." 00:00:00");
    $JasperReport->addParameter("datefin", date("Y-m-d", strtotime($_GET['fechaFin']))." 23:59:59");
    if(isset($_GET["confContrato"])){
        if($_GET['confContrato'] == '7'){
            $JasperReport->addParameter("confContrato", $_GET["confContrato"]);
            $nameConfing = 'ESTN - ESTACIONOMETROS MULTAS';

        }else{
            $nameConfing = 'TRAN - TRANSITO MUNICIPAL INFRACCION';
            $JasperReport->addParameter("confContrato", '4');
        }
       
    }
    if(isset($_GET["typeAtributo"])){
        $JasperReport->addParameter("atributo",$_GET["typeAtributo"]);
    }
    

    $JasperReport->addParameter("nameConfing", $nameConfing);
    
        


    $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
    $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
    $pdfBytes = $JasperReport->exportReportPDF($jPrint);

    header('Content-type: application/pdf');
    header('Content-Length: ' . strlen($pdfBytes));
    header('Content-Disposition: inline; filename=report.pdf');

    echo $pdfBytes;
} else {
    echo "<h3>Ocurrio un problema al visualizar la información</h3>";
}
