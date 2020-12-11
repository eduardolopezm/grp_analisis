<?php
/* $Revision: 1.14 $ */
/* Archivo modificado por  */
if ($PageNumber>1){
	$pdf->newPage();
}

/* if the deliver blind flag is set on the order, we do not want to output
the company logo */
if ($DeliverBlind < 2) {
	$XPos =10;
    $pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,730,0,60);
}

/* if the deliver blind flag is set on the order, we do not want to output
the company contact info */
if ($DeliverBlind < 2) {
    $FontSize=10;
    $YPos = 720;
    $pdf->addText($XPos, $YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
    $FontSize =8;
    $pdf->addText($XPos, $YPos-12,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
    $pdf->addText($XPos, $YPos-21,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
    $pdf->addText($XPos, $YPos-30,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
    $pdf->addText($XPos, $YPos-39,$FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
    $pdf->addText($XPos, $YPos-48,$FontSize, $_SESSION['CompanyRecord']['email']);

}
$FontSize=10;

$pdf->addText($XPos, 638,$FontSize, _('Nombre').' : ');
$pdf->addText($XPos, 626,$FontSize, _('Direccion:'));

$FontSize=10;
$pdf->addText(380, 740,$FontSize, _('Order No'). ':');
$pdf->addText(440, 740,$FontSize, $_GET['TransNo']);
$pdf->addText(380, 725,$FontSize,  _('Impreso') . ': ');
$pdf->addText(440, 725,$FontSize,  Date($_SESSION['DefaultDateFormat']));
$pdf->addText(380, 725-12,$FontSize,  _('Datos de envio'));
$pdf->addText(380, 725-24,$FontSize,  _('Entregar a').':');
$pdf->addText(380, 725-36,$FontSize,  _('Direccion').':');
$pdf->addText(380, 725-82,$FontSize,  _('Termino Pago').':');
$pdf->addText(380, 725-94,$FontSize,  _('Lista Precio').':');

/*if ($_GET['verplaca']==1 && $_GET['verserie']==0 && $_GET['verkilometraje']==0){
	$pdf->addText(380, 725-105,$FontSize,  _('Placa').':');
} elseif ($_GET['verserie']==1 && $_GET['verplaca']==0 && $_GET['verkilometraje']==0){
	$pdf->addText(380, 725-105,$FontSize,  _('Serie').':');
} elseif ($_GET['verkilometraje']==1 && $_GET['verplaca']==0 && $_GET['verserie']==0){
	$pdf->addText(380, 725-105,$FontSize,  _('Kilometraje').':');	
} elseif ($_GET['verserie']==1 && $_GET['verplaca']==1 && $_GET['verkilometraje']==1){
	$pdf->addText(380, 725-105,$FontSize,  _('Placa').':');
	$pdf->addText(380, 725-117,$FontSize,  _('Serie').':');
	$pdf->addText(380, 725-127,$FontSize,  _('Kilometraje').':');	
} elseif ($_GET['verserie']==1 && $_GET['verplaca']==1 && $_GET['verkilometraje']==0){
	$pdf->addText(380, 725-105,$FontSize,  _('Placa').':');
	$pdf->addText(380, 725-117,$FontSize,  _('Serie').':');
} elseif ($_GET['verserie']==0 && $_GET['verplaca']==1 && $_GET['verkilometraje']==1){
	$pdf->addText(380, 725-105,$FontSize,  _('Placa').':');
	$pdf->addText(380, 725-117,$FontSize,  _('Kilometraje').':');	
} elseif ($_GET['verserie']==1 && $_GET['verplaca']==0 && $_GET['verkilometraje']==1){
	$pdf->addText(380, 725-105,$FontSize,  _('Serie').':');
	$pdf->addText(380, 725-117,$FontSize,  _('Kilometraje').':');	
}*/



$YPos -= 140;
$XPos = 5;
$LeftOvers = $pdf->addTextWrap(38,$YPos,127,$FontSize, _('Fecha'),'left');
$LeftOvers = $pdf->addTextWrap(100,$YPos,255,$FontSize, _('Vendedor'),'left');
$LeftOvers = $pdf->addTextWrap(255,$YPos,85,$FontSize, _('Comentarios'),'right');

$LeftOvers = $pdf->addTextWrap(150,$YPos-90,127,$FontSize, _('Codigo'),'left');
$LeftOvers = $pdf->addTextWrap(240,$YPos-90,100,$FontSize, _('Descripcion'),'left');
$LeftOvers = $pdf->addTextWrap(400,$YPos-90,85,$FontSize, _('Cantidad'),'right');
//$LeftOvers = $pdf->addTextWrap(250,$YPos-90,85,$FontSize,_('Precio'),'right');
//$LeftOvers = $pdf->addTextWrap(300,$YPos-90,85,$FontSize, _('Desc'),'right');
//$LeftOvers = $pdf->addTextWrap(340,$YPos-90,85,$FontSize, _('Desc1'),'right');
//$LeftOvers = $pdf->addTextWrap(380,$YPos-90,85,$FontSize, _('Desc2'),'right');
$LeftOvers = $pdf->addTextWrap(470,$YPos-90,85,$FontSize, _('Importe'),'right');

//$LeftOvers = $pdf->addTextWrap(150,$YPos-420,120,$FontSize, _('Cantidad con letra'),'right');
$FontSize=10;
$YPosi=220;
$LeftOvers = $pdf->addTextWrap(420,$YPosi,85,$FontSize,_('SubTotal'),'right');
$YPosi=$YPosi-10;
$LeftOvers = $pdf->addTextWrap(420,$YPosi,85,$FontSize, _('Iva'),'right');
$YPosi=$YPosi-10;
$LeftOvers = $pdf->addTextWrap(420,$YPosi,85,$FontSize, _('Total'),'right');
$FontSize=10;
$YPosi=$YPosi-180;
$LeftOvers = $pdf->addTextWrap(178,$YPosi,120,$FontSize, _('Firma'),'right');

$YPos -= $line_height;

$FontSize =10;

?>
