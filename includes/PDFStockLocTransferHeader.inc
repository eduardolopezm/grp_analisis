<?php

/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}


$FontSize=9;
$YPos= $Page_Height-$Top_Margin;
$pdf->addJpegFromFile($_SESSION['LogoFile'], $Left_Margin, $YPos-40, 0, 30);
$LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize, _('Impreso'). ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Pagina'). ' ' . $PageNumber);

$YPos -=15;
$FontSize=9;
$LeftOvers = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize,
_('Transferencia No').': ' . $_GET['TransferNo']);

$YPos -=15;
$FontSize=9;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize,
_('Enviado'). ' ' . ConvertSQLDate($TransferRow['shipdate']));

$YPos -=15;
$FontSize=9;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize,
_('Desde').' ' . $TransferRow['shiplocname']);

$YPos -=15;
$FontSize=9;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize, _('a').'
' . $TransferRow['reclocname']);
if($TransferRow['debtorno'] <> ""){
	$Cliente = $TransferRow['debtorno'].' - '.$TransferRow['nombre'];
	$Sucursal = $TransferRow['branchcode'].' - '.$TransferRow['braddress1'];
	$YPos -=15;
	$FontSize=9;
	$LeftOvers2 = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize, _('Cliente').'
	' . $Cliente);

	$YPos -=15;
	$FontSize=9;
	$LeftOvers2 = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize, _('Suc. Cliente').'
	' . $Sucursal);
	if($TransferRow['NoContratoConsigAuthCust'] <> ""){
		$YPos -=15;
		$FontSize=9;
		$LeftOvers2 = $pdf->addTextWrap($Left_Margin+120,$YPos,550,$FontSize, _('No. Contrato').'
	' . $TransferRow['NoContratoConsigAuthCust']);
	}
	
}
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
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,100-$Left_Margin,$FontSize,_('Código'), 'centre');
$LeftOvers = $pdf->addTextWrap(100,$YPos,150-$Left_Margin,$FontSize,_('Descripcion'), 'centre');
$LeftOvers = $pdf->addTextWrap(200,$YPos,150-$Left_Margin,$FontSize,_('Cliente'), 'centre');
$LeftOvers = $pdf->addTextWrap(300,$YPos,150-$Left_Margin,$FontSize,_('Pedido'), 'centre');
$LeftOvers = $pdf->addTextWrap(340,$YPos,150-$Left_Margin,$FontSize,_('Vendedor'), 'centre');

$LeftOvers = $pdf->addTextWrap(420,$YPos,70,$FontSize,_('Cant. T.'), 'centre');
$LeftOvers = $pdf->addTextWrap(460,$YPos,50,$FontSize,_('Cant. Env.'), 'centre');
$LeftOvers = $pdf->addTextWrap(510,$YPos,50,$FontSize,_('Costo Prom'), 'centre');

$FontSize=10;
$YPos -= 30;

?>