<?php
error_reporting ( 0 );

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

include ('includes/FreightCalculation.inc');
include ('includes/GetSalesTransGLCodes.inc');
include ('includes/Functions.inc');
include ('Numbers/Words.php');
include ('includes/XSAInvoicing.inc');
include ('includes/SendInvoicing.inc');
include_once ('phpqrcode/qrlib.php');

$_SESSION ['DefaultDateFormat'] = 'd/m/Y';
global $db;

include ('includes/pdfReceiptClass.inc');
$pdf = new pdfRecibo ();
$pdf->printPDF ();
?>