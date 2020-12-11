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
    $pdf->addText($XPos, $YPos,$FontSize,$myrow['legalname']);
    $FontSize =8;
    $pdf->addText($XPos, $YPos-12,$FontSize,$myrow['address1']);
    $pdf->addText($XPos, $YPos-21,$FontSize,$myrow['address2']);
    $pdf->addText($XPos, $YPos-30,$FontSize, $myrow['address4'] . ' ' . $myrow['address3'] );
    $pdf->addText($XPos, $YPos-39,$FontSize, _('Ph') . ': ' . $myrow['telephone'] . ' ' . _('Fax'). ': ' . $myrow['fax']);
    $pdf->addText($XPos, $YPos-48,$FontSize, $myrow['email']);

}
$FontSize=10;

$pdf->addText($XPos, 648,$FontSize, _('Nombre').' : ');
$pdf->addText($XPos, 636,$FontSize, _('Direccion:'));
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
$pdf->addText(380, 725-108,$FontSize,  _('Usuario').':');

$YPos -= 158;
$XPos = 5;
$pdf->addTextWrap(38,$YPos,127,$FontSize, _('Fecha'),'left');
$pdf->addTextWrap(100,$YPos,255,$FontSize, _('Vendedor'),'left');
//$LeftOvers = $pdf->addTextWrap(255,$YPos,85,$FontSize, _('Comentarios'),'right');

$YPos =605 ;
$pdf->addTextWrap(30,$YPos-90,127,$FontSize, _('Codigo'),'left');
$pdf->addTextWrap(90,$YPos-90,100,$FontSize, _('Descripcion'),'left');
$pdf->addTextWrap(200,$YPos-90,100,$FontSize, _('Info Extra'),'left');
$pdf->addTextWrap(270,$YPos-90,85,$FontSize, _('Cantidad'),'left');
$pdf->addTextWrap(330,$YPos-90,85,$FontSize,_('Precio'),'left');
$pdf->addTextWrap(325,$YPos-90,85,$FontSize, _('Desc1'),'right');
$pdf->addTextWrap(365,$YPos-90,85,$FontSize, _('Desc2'),'right');
$pdf->addTextWrap(405,$YPos-90,85,$FontSize, _('Desc3'),'right');
$pdf->addTextWrap(510,$YPos-90,60,$FontSize, _('Importe'),'left');

$FontSize=12;
$pdf->addTextWrap(400,$YPos-425,85,$FontSize,_('SubTotal'),'right');
$pdf->addTextWrap(400,$YPos-445,85,$FontSize, _('Iva'),'right');
$pdf->addTextWrap(400,$YPos-465,85,$FontSize, _('Total'),'right');
$FontSize=10;
$pdf->addTextWrap(178,$YPos-530,120,$FontSize, _('Firma'),'right');

$YPos -= $line_height;

?>
