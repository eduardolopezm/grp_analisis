<?php
/**
 * Visualizar Reportes
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 0
 * Visualizar reportes conac y ldf
 */
//  ini_set('display_errors', 1);
//  ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
include('config.php');
include('includes/session.inc');
$PrintPDF = $_GET ['PrintPDF'];
$_POST ['PrintPDF'] = $PrintPDF;
include('jasper/JasperReport.php');
include('includes/SQL_CommonFunctions.inc');

include $PathPrefix . "includes/SecurityUrl.php";
$enc = new Encryption;

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

ini_set('memory_limit', '3068M');
set_time_limit(600);

if (isset($_GET['reporte'])) {
    $jreport= "";
    $JasperReport = new JasperReport($confJasper);
    if($_GET["tipoDescarga"]=="x"){
    $XLS = (  empty($_GET["tipoDescarga"]) ? "" : ( strtolower($_GET["tipoDescarga"])=="x" ? "_xls" : "" )  );
    $rutaReporte = $_GET['reporte'].'_excel';
    }else{
        $XLS = (  empty($_GET["tipoDescarga"]) ? "" : ( strtolower($_GET["tipoDescarga"])=="x" ? "_xls" : "" )  );
        $rutaReporte = $_GET['reporte'];
    }
    
    // $rutaReporte = ( !$XLS ? $rutaReporte : ( $rutaReporte).$XLS.".jasper" );

    $jreport = $JasperReport->compilerReport($rutaReporte);

    $mesFin = "";
    $dia = "";
    $anio = "";

    $mesInicio = "";
    $anioInicio = "";

    if(isset($_GET["fechainicial"])){
        $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
        $newDateStringInicial = $myDateTime->format('Y-m-d');
        $JasperReport->addParameter("fechainicio", $newDateStringInicial." 00:00:00");
        $mesFin = $myDateTime->format('m');
        $dia = $myDateTime->format('d');
        $anio = $myDateTime->format('Y');

        $anyo = $anio - 1;
        $JasperReport->addParameter("inicio",  $anyo."-".$mesFin."-".$dia);
    } else {
        $JasperReport->addParameter("fechainicio", date('Y-m-d')." 00:00:00");
        $mesFin = date('m');
        $dia = date('d');
        $anio = date('Y');
        
        $anyo = $anio - 1;
        $JasperReport->addParameter("inicio",  $anyo."-".$mesFin."-".$dia);
    }

    $mesInicio = $mesFin;
    $anioInicio = $anio;
    
    $SQL = "SELECT UPPER(mes) as mes FROM cat_Months WHERE u_mes = '".$mesFin."'";
    $result = DB_query($SQL,$db);
    $myrows = DB_fetch_array($result);
    $fechaInilarga = " ".$dia." DE ".$myrows['mes']." DEL ".$anio;
    $nombreMes = $myrows['mes'];
    $JasperReport->addParameter("fechaIniText", $fechaInilarga);

    if(isset($_GET["fechafinal"])){
        $myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
        $newDateStringFinal = $myDateTime->format('Y-m-d');
        $mesFin = $myDateTime->format('m');
        $dia = $myDateTime->format('d');
        $anio = $myDateTime->format('Y');
        
        $anyo = $anio - 1;
        $JasperReport->addParameter("fechafin", $newDateStringFinal." 23:59:59");
        $JasperReport->addParameter("fechainicio2", $anio."-".$mesFin."-01 00:00:00");
        $JasperReport->addParameter("fin",  $anyo."-".$mesFin."-".$dia);
    } else {
        $JasperReport->addParameter("fechafin", date('Y-m-d')." 23:59:59");
        $mesFin = date('m');
        $dia = date('d');
        $anio = date('Y');
        

        $anyo = $anio - 1;
        $JasperReport->addParameter("fechainicio2", date('Y-m-')."01 00:00:00");
        $JasperReport->addParameter("fin",  $anyo."-".$mesFin."-".$dia);
    }
    
    // $JasperReport->addParameter("fechainicio2", date('Y-m-')."01 00:00:00");

    $SQL = "SELECT UPPER(mes) as mes FROM cat_Months WHERE u_mes = '".$mesFin."'";
    $result = DB_query($SQL,$db);
    $myrows = DB_fetch_array($result);
    $fechalarga = " ".$dia." DE ".$myrows['mes']." DEL ".$anio;
    $nombreMes = $myrows['mes'];
    $JasperReport->addParameter("fechaFinText", $fechalarga);

    $JasperReport->addParameter("mesInicio", $mesInicio);
    $JasperReport->addParameter("anioInicio", $anioInicio);

    $JasperReport->addParameter("fechalarga", $fechalarga);
    $JasperReport->addParameter("nombreMes", $nombreMes);
    $JasperReport->addParameter("diaFinal", $dia);
    $JasperReport->addParameter("mesFinal", $mesFin);
    $JasperReport->addParameter("anioFinal", $anio);
    $JasperReport->addParameter("anioFinal2", ($anio - 1));

    $razonsocial = "";
    if (isset($_GET['razonsocial'])) {
        $razonsocial = $_GET['razonsocial'];
    }

    $JasperReport->addParameter("razonsocial", $razonsocial);
    $JasperReport->addParameter("tagref", $_GET['selectUnidadNegocio']);
    $JasperReport->addParameter("userid", $_SESSION['UserID']);
    
    $selectObjetoPrincipal = "-1";
    if (!empty( ( $_SESSION['selectObjetoPrincipal'.$_GET['selectObjetoPrincipal']] ) )) {
        $selectObjetoPrincipal = implode("', '", explode(",", $_SESSION['selectObjetoPrincipal'.$_GET['selectObjetoPrincipal']]));
    }

    $selectObjetoParcial = "-1";    
    if (!empty( ( $_SESSION['selectObjetoParcial'.$_GET['selectObjetoParcial']] ) )) {
        $selectObjetoParcial = implode("', '", explode(",", $_SESSION['selectObjetoParcial'.$_GET['selectObjetoParcial']]));
    }
    
    // echo "<br>selectObjetoPrincipal is_null: ".is_null($_SESSION['selectObjetoPrincipal'.$_GET['selectObjetoPrincipal']]);
    // echo "<br>selectObjetoParcial is_null: ".is_null($_SESSION['selectObjetoParcial'.$_GET['selectObjetoParcial']]);

    // echo "<br>selectObjetoPrincipal session: ".$_SESSION['selectObjetoPrincipal'.$_GET['selectObjetoPrincipal']];
    // echo "<br>selectObjetoParcial session: ".$_SESSION['selectObjetoParcial'.$_GET['selectObjetoParcial']];

    // echo "<br>selectObjetoPrincipal: ".$selectObjetoPrincipal;
    // echo "<br>selectObjetoParcial: ".$selectObjetoParcial;
    // exit();
    
    $JasperReport->addParameter("loccode", $selectObjetoPrincipal);
    $JasperReport->addParameter("stockid", $selectObjetoParcial);
    
    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/logo_tampico_02.jpg"))));
    
    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/logo_estado_tampico_color.jpg"))));
    $JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "");
    //echo $JasperReport->getPathFile();
    //exit;
    $conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
    $jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
    // $pdfBytes = $JasperReport->exportReportPDF($jPrint);
    $pdfBytes = ( $XLS ? $JasperReport->exportReportXLS($jPrint) : $JasperReport->exportReportPDF($jPrint) );

    $filename = str_replace(' ', '_', $_GET['nombreArchivo'])."_".date('Y-m-d');

    if ($xlsx) {
        // $filename = $_GET['reporte'];
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Length: ' . filesize($filename));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
    } else {
        // header('Content-type: application/pdf');
        header('Content-type: application/'.( $XLS ? "vnd.ms-excel" : "pdf" ));

        header('Content-Length: ' . strlen($pdfBytes));
        // header('Content-Disposition: inline; filename=report.pdf');
        header('Content-Disposition: inline; filename='."$filename.".( $XLS ? "xls" : "pdf" ));
    }

    echo $pdfBytes;

} else {
    echo "Sin Configuración";
    exit();
}