<?php
/**
 * Impresión boleta de estacionamiento
 *
 * @category Pdf
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/12/2019
 * Fecha Modificación: 31/12/2019
 * impresion_ boleta_estacionamiento
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
// impresion_ boleta_estacionamiento.php?idContrato=18&folioRecibo=59

$idContrato = 0;
if (isset($_POST['idContrato'])) {
	$idContrato = $_POST['idContrato'];
} elseif (isset($_GET['idContrato'])) {
	$idContrato = $_GET['idContrato'];
}

$folioRecibo = 0;
if (isset($_POST['folioRecibo'])) {
	$folioRecibo = $_POST['folioRecibo'];
} elseif (isset($_GET['folioRecibo'])) {
	$folioRecibo = $_GET['folioRecibo'];
}

$folioPase = 0;
if (isset($_POST['folioPase'])) {
	$folioPase = $_POST['folioPase'];
} elseif (isset($_GET['folioPase'])) {
	$folioPase = $_GET['folioPase'];
}
// echo "<br>idContrato: ".$idContrato;
// echo "<br>folioRecibo: ".$folioRecibo;
// echo "<br>folioPase: ".$folioPase;
// exit();
$jreport= "";
$JasperReport = new JasperReport($confJasper);
$jreport = $JasperReport->compilerReport("boleta_estacionamiento");

$sql = "SELECT
tb_reportes_contratos.sn_reporte,
tb_cat_atributos_contrato.ln_etiqueta,
tb_propiedades_atributos.ln_valor,
tb_administracion_contratos.folio_recibo,
tb_administracion_contratos.id_periodo,
SUBSTRING(tb_administracion_contratos.id_periodo, 1, 4) as anio,
SUBSTRING(tb_administracion_contratos.id_periodo, 5, 6) as mes,
cat_Months.mes as mesLetra,
tb_administracion_contratos.id_contrato
-- , '---', tb_reportes_contratos.*
FROM tb_administracion_contratos
JOIN tb_contratos ON tb_contratos.id_contrato = tb_administracion_contratos.id_contrato
JOIN tb_contratos_contribuyentes ON tb_contratos_contribuyentes.id_contratos = tb_contratos.id_confcontratos
JOIN tb_reportes_contratos ON tb_reportes_contratos.nu_tipo = tb_contratos_contribuyentes.reporte
JOIN tb_cat_atributos_contrato ON tb_cat_atributos_contrato.id_contratos = tb_contratos.id_confcontratos
JOIN tb_propiedades_atributos ON tb_propiedades_atributos.id_folio_contrato = tb_administracion_contratos.id_contrato AND tb_propiedades_atributos.id_folio_configuracion = tb_contratos.id_confcontratos AND tb_propiedades_atributos.id_etiqueta_atributo = tb_cat_atributos_contrato.id_atributos
LEFT JOIN cat_Months ON cat_Months.u_mes = SUBSTRING(tb_administracion_contratos.id_periodo, 5, 6)
WHERE
tb_administracion_contratos.id_contrato = '".$idContrato."'
AND tb_administracion_contratos.pase_cobro = '".$folioPase."'
AND tb_administracion_contratos.folio_recibo = '".$folioRecibo."'";
$result = DB_query($sql, $db);
$placa = "";
$modelo = "";
$color = "";
while ($myrow = DB_fetch_array($result)) {
	if (strtoupper($myrow['ln_etiqueta']) == 'PLACA') {
		$placa = $myrow['ln_valor'];
	}
	if (strtoupper($myrow['ln_etiqueta']) == 'MODELO') {
		$modelo = $myrow['ln_valor'];
	}
	if (strtoupper($myrow['ln_etiqueta']) == 'COLOR') {
		$color = $myrow['ln_valor'];
	}
}

$JasperReport->addParameter("idContrato", $idContrato);
$JasperReport->addParameter("folioRecibo", $folioRecibo);
$JasperReport->addParameter("folioPase", $folioPase);
$JasperReport->addParameter("placa", $placa);
$JasperReport->addParameter("modelo", $modelo);
$JasperReport->addParameter("color", $color);

$ruta = $JasperReport->getPathFile()."images/estadoTamp.jpeg";
$ruta = str_replace('jasper/', '', $ruta);
$ruta = str_replace('jasperconfig/', '', $ruta);
$JasperReport->addParameter("imagenEstado", $ruta);
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
