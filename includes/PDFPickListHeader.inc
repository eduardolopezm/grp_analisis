<?php

/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=9;
$YPos= $Page_Height-$Top_Margin;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,180,$FontSize, _('Impreso'). ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Pagina'). ': ' . $PageNumber);
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-20,300,14,_('Reporte Pick List '));
$YPos -=15;
$FontSize=9;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos-35,550,$FontSize,_('Almacen de Distribucion').': ' . $_GET['StockLocationDestino']);

$YPos -=15;
$FontSize=9;
//$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,_('Enviado'). ' ' . ConvertSQLDate($TransferRow['shipdate']));

$YPos -=15;
$FontSize=9;
//$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,_('Desde').' ' . $TransferRow['shiplocname']);

$YPos -=15;
$FontSize=9;
//$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, _('a').'' . $TransferRow['reclocname']);

/*Draw a rectangle to put the headings in     */
$BoxHeight =20;
$FontSize=9;
$YPos-=20;
$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
$pdf->line($Left_Margin, $YPos,$Left_Margin, $YPos-$BoxHeight);
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$pdf->line($Page_Width-$Right_Margin, $YPos,$Page_Width-$Right_Margin, $YPos-$BoxHeight);
$YPos -=20;

$YPos +=5;

/*set up the headings */
$Xpos = $Left_Margin+1;
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,150-$Left_Margin,$FontSize,_('Alm. Req'), 'centre');
$LeftOvers = $pdf->addTextWrap(150,$YPos,150,$FontSize,_('Localizacion'), 'centre');
$LeftOvers = $pdf->addTextWrap(210,$YPos,150,$FontSize,_('Código'), 'centre');
$LeftOvers = $pdf->addTextWrap(300,$YPos,200,$FontSize,_('Descripcion'), 'centre');

$LeftOvers = $pdf->addTextWrap(460,$YPos,70,$FontSize,_('Cant. Sol.'), 'centre');
$LeftOvers = $pdf->addTextWrap(510,$YPos,80,$FontSize,_('Cant. Tranf.'), 'centre');
//$LeftOvers = $pdf->addTextWrap(510,$YPos,50,$FontSize,_('Costo Prom'), 'centre');

$FontSize=10;
$YPos -= 30;

?>