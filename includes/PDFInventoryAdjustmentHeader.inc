<?php

/*PDF page header for inventory check report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=9;
$YPos= $Page_Height-$Top_Margin;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$TransferRow['legalname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize, _('Impreso'). ': ' . Date($_SESSION['DefaultDateFormat']) /*. ' ' . _('Pagina'). ' ' . $PageNumber*/);

$YPos -=15;
$FontSize=9;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
_('Sucursal').': ' .$TransferRow['tagdescription']);


$YPos -=15;
$FontSize=9;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
_('Almacen').': ' .$TransferRow['locationname']);


$YPos -=15;
$FontSize=9;
$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
_('Orden de Ajuste de inventario No').': ' . $_GET['OrdernoAjuste'].' Status: '.$TransferRow['status']);

$YPos -=15;
$FontSize=9;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
_('Solicitado el'). ' ' . ConvertSQLDate($TransferRow['origtrandate']).' por '.$TransferRow['userregister']);

$YPos -=15;
$FontSize=9;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize,
_('Autorizado por ').' ' . $TransferRow['userauthorized']);

$YPos -=15;
$FontSize=9;
$LeftOvers2 = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, _('Procesado el ').'
' . ConvertSQLDate($TransferRow['trandate']).' por '.$TransferRow['userprocess']);

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
$LeftOvers = $pdf->addTextWrap(400,$YPos,70,$FontSize,_('Cantidad'), 'centre');
//$LeftOvers = $pdf->addTextWrap(460,$YPos,50,$FontSize,_('Cant. Env.'), 'centre');
//$LeftOvers = $pdf->addTextWrap(510,$YPos,50,$FontSize,_('Costo Prom'), 'centre');

$FontSize=10;
$YPos -= 30;

?>