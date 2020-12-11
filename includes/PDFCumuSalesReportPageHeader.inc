<?php
/* $Revision: 1.14 $ */
/* Archivo modificado por  */
if ($PageNumber>1){
	$pdf->newPage();
}
if ($DeliverBlind < 2) {
	$XPos =10;
    $pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,730,0,60);
}
if ($DeliverBlind < 2) {
    $FontSize=10;
    $YPos = 725;
    $pdf->addText($XPos, $YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
    /*$FontSize =8;
    $pdf->addText($XPos, $YPos-12,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
    $pdf->addText($XPos, $YPos-21,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
    $pdf->addText($XPos, $YPos-30,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
    $pdf->addText($XPos, $YPos-39,$FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
    $pdf->addText($XPos, $YPos-48,$FontSize, $_SESSION['CompanyRecord']['email']);
    */
}

$YPosl = 800;
//$LeftOvers = $pdf->addTextWrap(60,$YPosl-90,127,$FontSize, _('Sucursal'),'left');
$LeftOvers = $pdf->addTextWrap(-40,$YPosl-90,150,$FontSize, _('Concepto'),'right');
$LeftOvers = $pdf->addTextWrap(340,$YPosl-90,85,$FontSize, _('Cantidad'),'right');
$LeftOvers = $pdf->addTextWrap(480,$YPosl-90,60,$FontSize, _('Precio'),'right');
$YPosl -= $line_height;

?>
