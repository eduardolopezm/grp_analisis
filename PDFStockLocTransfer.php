<?php
/*error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/<error_log class="txt"></error_log>');*/

$PageSecurity =1;
$funcion=46;

include "includes/SecurityUrl.php";
include('includes/session.inc');
include('includes/PDFStarter.php');

$title= traeNombreFuncion($funcion, $db);

if (!isset($_GET['TransferNo'])) {
    include('includes/header.inc');
    echo '<p>';
    //prnMsg(_('This page must be called with a location transfer reference number'), 'error');
    prnMsg(_('Esta pagina requiere un movimiento de transferencia realizada'), 'error');
    include('includes/footer_Index.inc');
    exit;
}

$FontSize=10;
$pdf->addinfo('Title', _('Entrada por Transferencia'));
$pdf->addinfo('Subject', _('Entrada por Transferencia') . ' # ' . $_GET['TransferNo']);

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>'. _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT loctransfers.reference,
			   loctransfers.stockid,
			   stockmaster.barcode,
			   stockmaster.description,
			   stockmaster.materialcost,
			   stockcostsxlegal.avgcost,
			   loctransfers.shipqty,
			   loctransfers.shipdate,
			   loctransfers.shiploc,
			   locations.locationname as shiplocname,
			   loctransfers.recloc,
			   locationsrec.locationname as reclocname,
			   loctransfers.comments,
				loctransfers.debtorno,
			   loctransfers.branchcode,
				loctransfers.NoContratoConsigAuthCust,
				debtorsmaster.name as nombre,
				custbranch.braddress1
			   FROM loctransfers
			   INNER JOIN stockmaster ON loctransfers.stockid=stockmaster.stockid
			   INNER JOIN locations ON loctransfers.shiploc=locations.loccode
			   INNER JOIN locations AS locationsrec ON loctransfers.recloc = locationsrec.loccode
			   INNER JOIN tags ON locations.tagref = tags.tagref
			   LEFT JOIN stockcostsxlegal ON tags.legalid = stockcostsxlegal.legalid AND stockcostsxlegal.stockid = loctransfers.stockid
				LEFT JOIN debtorsmaster ON debtorsmaster.debtorno = loctransfers.debtorno
			   LEFT JOIN custbranch ON custbranch.debtorno = loctransfers.debtorno and custbranch.branchcode = loctransfers.branchcode
			   WHERE loctransfers.reference=" . $_GET['TransferNo'];
//echo '<pre>'.$sql;
$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($result)==0) {
    include('includes/header.inc');
    prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'), 'error');
    include('includes/footer_Index.inc');
    exit;
}

$TransferRow = DB_fetch_array($result);

$PageNumber=1;
include('includes/PDFStockLocTransferHeader.inc');
$line_height=30;
$FontSize=10;

$comment = '';
$totQty = 0;

do {
    $Vendedor= "";
    $Orderno= "";
    $Name= "";
    $Debtorno= "";
    $LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos+1, 100, 9, $TransferRow['stockid'], 'left');
    //agregar no cliente y nombre cliente si tiene requisicion
    
    $sql = "SELECT debtorsmaster.debtorno,debtorsmaster.name,salesorders.orderno,salesorders.salesman,salesman.salesmanname
		FROM loctransfers
		INNER JOIN transferrequistions ON transferrequistions.transferno=loctransfers.reference
		INNER JOIN requisitionorderdetails ON requisitionorderdetails.podetailitem=transferrequistions.norequisition
		INNER JOIN salesorders ON salesorders.orderno=requisitionorderdetails.orderno
		INNER JOIN salesman ON salesorders.salesman=salesman.salesmancode
		INNER JOIN debtorsmaster  ON salesorders.debtorno=debtorsmaster.debtorno
		WHERE loctransfers.reference=" . $_GET['TransferNo']."
		And loctransfers.stockid='".$TransferRow['stockid']."'";
        //echo '<pre>'.$sql ;exit;;
    $QOOResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
    if (DB_num_rows($QOOResult)==1) {
            $QOORow = DB_fetch_row($QOOResult);
            $Debtorno =  $QOORow[0];
            $Name=$QOORow[1];
            $Orderno=$QOORow[2];
            $Vendedor=$QOORow[4];
    } else {
        $QOO = 0;
    }
    
    $LeftOvers = $pdf->addTextWrap(100, $YPos, 200, $FontSize, $TransferRow['description'], 'left');
    $LeftOvers = $pdf->addTextWrap(200, $YPos, 200, 6, left($Debtorno.' - '.$Name, 30), 'left');
    $LeftOvers = $pdf->addTextWrap(310, $YPos, 200, 6, $Orderno, 'left');
    $LeftOvers = $pdf->addTextWrap(350, $YPos, 200, 6, $Vendedor, 'left');
    $LeftOvers = $pdf->addTextWrap(400, $YPos, 70, $FontSize, $TransferRow['shipqty'], 'center');
    $LeftOvers = $pdf->addTextWrap(450, $YPos, 70, $FontSize, $TransferRow['shipqty'], 'center');
    $LeftOvers = $pdf->addTextWrap(490, $YPos, 70, $FontSize, "$ " . number_format($TransferRow['avgcost']), 'center');
    
    $comment = $TransferRow['comments'];
    
    $pdf->line($Left_Margin, $YPos-2, $Page_Width-$Right_Margin, $YPos-2);

    $YPos -= $line_height;

    if ($YPos < $Bottom_Margin + $line_height) {
        $PageNumber++;
        include('includes/PDFStockLocTransferHeader.inc');
    }
    
    $totQty = $totQty + $TransferRow['shipqty'];
} while ($TransferRow = DB_fetch_array($result));

$LeftOvers = $pdf->addTextWrap(150, $YPos, 200, $FontSize, _('CANTIDAD PRODUCTOS:'), 'right');
$LeftOvers = $pdf->addTextWrap(350, $YPos, 70, $FontSize+1, $totQty, 'center');
$YPos -= $line_height;

$YPos -= $line_height;
$LeftOvers = $pdf->addTextWrap(150, $YPos, 300, $FontSize, "COMENTARIOS: ".$comment, 'center');
$YPos -= $line_height/2;
$LeftOvers = $pdf->addTextWrap(150, $YPos, 300, $FontSize, $LeftOvers, 'center');
$YPos -= $line_height/2;
$LeftOvers = $pdf->addTextWrap(150, $YPos, 300, $FontSize, $LeftOvers, 'center');
$YPos -= $line_height/2;
$LeftOvers = $pdf->addTextWrap(150, $YPos, 300, $FontSize, $LeftOvers, 'center');
$pdfcode = $pdf->output();
$len = strlen($pdfcode);

if ($len<=20) {
    include('includes/header.inc');
    echo '<p>';
    prnMsg(_('There was no stock location transfer to print out'), 'warn');
    echo '<br><a href="' . $rootpath. '/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
    include('includes/footer_Index.inc');
    exit;
} else {
    header('Content-type: application/pdf');
    header('Content-Length: ' . $len);
    header('Content-Disposition: inline; filename=transferencia.pdf');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    $pdf->Stream();
}
