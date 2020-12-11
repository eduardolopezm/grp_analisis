<?php
/* $Revision: 1.7 $ */
/*PDF page header for inventory valuation report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Valuacion de Inventarios X Categorias de Producto'));
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Impreso') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Pagina') . ' ' . $PageNumber);

$YPos -=(1*$line_height);

$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_POST['FromCriteria'] . ' ' . _('y') . ' ' . $_POST['ToCriteria'] . ' ' . _('en el almacen:') . ' ' . $_POST['Location'] );
$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;

if ($_POST['DetailedReport']=='Yes'){
	
	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,_('Categoria') . '/' . _('Producto'), 'centre');
	$LeftOvers = $pdf->addTextWrap(390,$YPos,60,$FontSize,_('Cantidad'), 'centre');
	$LeftOvers = $pdf->addTextWrap(450,$YPos,60,$FontSize,_('Costo Promedio'), 'centre');
	$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,_('Valor'), 'centre');
} else {
	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,320-$Left_Margin,$FontSize,_('Categoria'), 'centre');
	$LeftOvers = $pdf->addTextWrap(510,$YPos,60,$FontSize,_('Valor'), 'centre');
}

$FontSize=8;
$YPos =$YPos - (2*$line_height);

$PageNumber++;

?>
