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

if($_GET['TransNo'] != ''){
    $SQL = "SET NAMES 'utf8'";
    $TransResult = DB_query($SQL, $db);

    $SQLIMG = "SELECT
	legalbusinessunit.logo,
	(salesorderdetails.quantity * salesorderdetails.unitprice ) - (( salesorderdetails.quantity * salesorderdetails.unitprice ) * ( salesorderdetails.discountpercent ) )  AS total, salesorders.comments, salesorders.txt_pagador
	FROM salesorders
	JOIN tags ON tags.tagref = salesorders.tagref
	JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
	INNER JOIN salesorderdetails
               ON salesorders.orderno = salesorderdetails.orderno
	WHERE salesorders.orderno =  '".$_GET['TransNo']. "'";
    $result = DB_query($SQLIMG,$db);
    $myrows = DB_fetch_array($result);
    $logo_legal = $myrows['logo'];
    $result2 = DB_query($SQLIMG,$db);
    $comments = "";
    $txt_pagador = "";
	while ($rs = DB_fetch_array($result2)) {
		$total += $rs['total'];
        if (!empty($rs['comments'])) {
            $comments = $rs['comments'];
        }
        if (!empty($rs['txt_pagador'])) {
            $txt_pagador = $rs['txt_pagador'];
        }
	}

    $separa=explode(".",str_replace(",","",number_format($total,2)));
    $montoctvs2 = $separa[1];

    if ($montoctvs2=="")
        $montoctvs2="00";

    $montoctvs1 = $separa[0];
    if (left($montoctvs2,3)>=995){
        $montoctvs1=$montoctvs1+1;
    }

    $montoletra=Numbers_Words::toWords($montoctvs1,'es');
    $montoLetra='('.strtoupper($montoletra) . " PESOS ". $montoctvs2 ." /100 M.N.)";

    $jreport= "";
    $JasperReport = new JasperReport($confJasper);
    $jreport = $JasperReport->compilerReport("/impresion_pedidos");

    $JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));

    $JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/logo_estado_tampico_color.jpg"))));

    $JasperReport->addParameter("orderNo", $_GET["TransNo"]);
    $JasperReport->addParameter("addressT", "EKU9003173C9 \n Tampico \n Tamaulipas, MEXICO \n C.P.:89000 \n Telefono: :9829023923");
    $JasperReport->addParameter("montoLetra", $montoLetra);
    $JasperReport->addParameter("comments", $comments);
    $JasperReport->addParameter("txt_pagador", $txt_pagador);

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
?>