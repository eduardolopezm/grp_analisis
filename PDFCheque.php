<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Pabono']))
    include('includes/PDFChequePAbono.inc');
else
    include('includes/PDFCheque.inc');
$pdf = new PDFCheque();
$pdf->exportPDF();

?>