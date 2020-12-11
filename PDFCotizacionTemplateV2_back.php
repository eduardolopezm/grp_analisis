<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

$PageSecurity = 2;

//var para no visializar tipo de cambio
$reportepdf= true;
include('includes/session.inc');
$funcion = 1968;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('Numbers/Words.php');
include('includes/SendInvoicing.inc');
ini_set('memory_limit', '500M');
//include ('includes/MiscFunctions.php');

//include ('includes/PDFCotizacionTemplate.inc');

//$basedatos = str_replace('_CAPA', '', $_SESSION ['DatabaseName']);
//$basedatos = str_replace('_DES', '', $_SESSION ['DatabaseName']);

define('FPDF_FONTPATH', './fonts/');
include_once('includes/fpdf.php');
include_once('includes/fpdi.php');
include_once('Numbers/Words.php');

// Extrae unidad de negocio de pedido de venta
if (! isset($_GET ['legalid'])) {
    $sql = "select legalid from salesorders inner join tags on tags.tagref=salesorders.tagref
				 where orderno=" . $_GET ['TransNo'];
    $rstxt = DB_query($sql, $db);
    $reg = DB_fetch_array($rstxt);
    $_GET ['legalid'] = $reg ['legalid'];
}

//if (strpos("@".$_SESSION['DatabaseName'],  "erpjibe")) {
//	include ('includes/PDFCotizacionTemplateV2.inc');
//}else{
    //echo $_SESSION ['DatabaseName'];
    //echo "<br>".str_replace("_DES", '', str_replace('_CAPA', '', $_SESSION ['DatabaseName']))."<br>";

include('includes/PDFCotizacionTemplateV2.inc');
//}
/*if ($_SESSION ['UserID'] == 'desarrollo' OR $_SESSION ['UserID'] == 'admin'  ) {
    ini_set ( 'display_errors', 1 );
	ini_set ( 'log_errors', 1 );
	ini_set ( 'error_log', dirname ( __FILE__ ) . '/error_log.txt' );
	error_reporting ( E_ALL );
	echo 'companies/' . $_SESSION ['DatabaseName'] . '/PDFCotizacionTemplateV2.inc';
}*/

function nombremeslargo($idmes)
{
    $nombremeslargo = "";
    switch ($idmes) {
        case 1:
            $nombremeslargo = "Enero";
            break;
        case 2:
            $nombremeslargo = "Febrero";
            break;
        case 3:
            $nombremeslargo = "Marzo";
            break;
        case 4:
            $nombremeslargo = "Abril";
            break;
        case 5:
            $nombremeslargo = "Mayo";
            break;
        case 6:
            $nombremeslargo = "Junio";
            break;
        case 7:
            $nombremeslargo = "Julio";
            break;
        case 8:
            $nombremeslargo = "Agosto";
            break;
        case 9:
            $nombremeslargo = "Septiembre";
            break;
        case 10:
            $nombremeslargo = "Octubre";
            break;
        case 11:
            $nombremeslargo = "Noviembre";
            break;
        case 12:
            $nombremeslargo = "Diciembre";
            break;
    }
    return $nombremeslargo;
}
$pdf = new pdfCotizacionTemplate();

$pdf->exportPDF();
