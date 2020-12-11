<?php
/*
ARCHIVO MODIFICADO POR: ALEJANDRA ROSAS
FECHA DE MODIFICACION: 08-JUNIO-2010 
CAMBIOS:
   1.- Impresion de encabezado de reporte de precio de lista
FIN DE CAMBIOS
*/

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=8;
$YPos= $Page_Height-$Top_Margin;

$pdf->addText($Left_Margin, $YPos,$FontSize, $_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$FontSize =10;
$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Impreso') . ': ' . Date("d M Y") . '  ' ._('Pagina') . ' ' . $PageNumber);

$YPos -=(3*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin, $YPos-5,$Left_Margin, $YPos-5);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos-5);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin+18;

$pdf->addTextWrap($Xpos,$YPos,220 - $Left_Margin,$FontSize,_('Clave'),'centre');
$pdf->addTextWrap(140,$YPos,60,$FontSize,_('Descripcion'),'centre');
$pdf->addTextWrap(345,$YPos,60,$FontSize,_('Marca'),'centre');
$pdf->addTextWrap(455,$YPos,60,$FontSize,_('Precio'),'centre');
$pdf->addTextWrap(510,$YPos,60,$FontSize,_('Lista de Precio'),'centre');

$YPos =$YPos - (2*$line_height);
?>