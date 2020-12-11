<?php
/*
ARCHIVO MODIFICADO POR: ISRAEL BARRERA
FECHA DE MODIFICACION: 26-NOVIEMBRE-2010 
CAMBIOS:
   1.- Impresion de encabezado de reporte de precio de lista
FIN DE CAMBIOS
*/

$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
}


$FontSize = 8;
$YPos = $Page_Height-$Top_Margin;
$XPos = $XPos + 20;

$YPos = $YPos -50;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos,0,48);

$pdf->selectFont('Arial Bold');
$pdf->addText($Right_Margin +50, $YPos+15,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Right_Margin +50, $YPos,$FontSize, _('Reporte De Ventas Por Producto RANKING'));
$LeftOvers = $pdf->addTextWrap($Right_Margin +410, $YPos, 150,$FontSize,'Usuario:'.'  '.$_SESSION['UsersRealName']);
while(strlen($LeftOvers)>0){
	$LeftOvers = $pdf->addTextWrap($Right_Margin +445, $YPos -10, 150, $FontSize,$LeftOvers);
$YPos -= 5;
}
$pdf->selectFont('Arial');

$YPos -=$line_height;



$FontSize = 8;
$FontSize = 8;
$pdf->addText($Page_Width-$Right_Margin-125,$YPos,$FontSize, _('Impreso') . ':  ' . date("d M Y"));
$pdf->addText($Page_Width-$Right_Margin-90,$YPos -10,$FontSize, date ("h:i:s:a"));
$pdf->addText($Page_Width-$Right_Margin-50,$YPos +50,$FontSize, _('Pagina') . ' ' . $PageNumber);

$count = 0;
$blok = 3;
$XPos = 20;
$YPos = $YPos -50;
$FontSize = 7;

if ($_GET['xGrupo']) {
	$pdf->selectFont('Arial Bold');
	$pdf->addText($XPos,$YPos,$FontSize, _('GRUPO'). ':');
	$pdf->selectFont('Arial');
	$pdf->addText($XPos +30,$YPos,$FontSize, _($_GET['xGrupo']));
	$XPos += 180;
	$count++;
	if ($count % $blok == 0) {
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xLinea']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,140,$FontSize, _('LINEA'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +30,$YPos,140,$FontSize, _($_GET['xLinea']));
	$YPostemp = $YPos;
	while (strlen($LeftOvers) > 0) {
		$YPostemp -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos,$YPostemp,140,$FontSize,$LeftOvers);
	}
	$XPos +=200;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xCategoria']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,200,$FontSize, _('CATEGORIA'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +50,$YPos,140,$FontSize, _($_GET['xCategoria']));
	$YPostemp = $YPos;
	while (strlen($LeftOvers) > 0) {
		$YPostemp -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos,$YPostemp,200,$FontSize,$LeftOvers);
	}
	$XPos += 250;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xRazonSocial']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,150,$FontSize, _('RAZON SOCIAL'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +60,$YPos,120,$FontSize, _($_GET['xRazonSocial']));
	$YPostemp = $YPos;
	while (strlen($LeftOvers) > 0) {
		$YPostemp -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos,$YPostemp,250,$FontSize,$LeftOvers);
	}
	$XPos += 180;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xRegion']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,250,$FontSize, _('REGION'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +35,$YPos,250,$FontSize, _($_GET['xRegion']));
	$XPos += 200;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}


if ($_GET['xArea']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,250,$FontSize, _('AREA'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +30,$YPos,250,$FontSize, _($_GET['xArea']));
	$XPos += 180;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xDepartamento']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,250,$FontSize, _('DEPTO'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +30,$YPos,250,$FontSize, _($_GET['xDepartamento']));
	$XPos += 180;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xUnidadDeNegocio']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,250,$FontSize, _('U_NEGOCIO'). ':');
	$pdf->selectFont('Arial');
	$LeftOvers = $pdf->addTextWrap($XPos +50,$YPos,250,$FontSize, _($_GET['xUnidadDeNegocio']));
	$XPos +=200;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}
}

if ($_GET['xAlmacen']){
	$pdf->selectFont('Arial Bold');
	$pdf->addTextWrap($XPos,$YPos,250,$FontSize, _('ALMACEN'). ':');
	$pdf->selectFont('Arial');
	$pdf->addTextWrap($XPos +40,$YPos,250,$FontSize, _($_GET['xAlmacen']));
	}

$YPos -=(3*$line_height);

/*Draw a rectangle to put the headings in     */
$pdf->line($Page_Width-$Right_Margin +10, $YPos-5,$Left_Margin -10, $YPos-5);
$pdf->line($Page_Width-$Right_Margin +10, $YPos+$line_height,$Left_Margin -10, $YPos+$line_height);
$pdf->line($Page_Width-$Right_Margin +10, $YPos+$line_height,$Page_Width-$Right_Margin +10, $YPos-5);
$pdf->line($Left_Margin -10, $YPos+$line_height,$Left_Margin -10, $YPos-5);

/*set up the headings */
$Xpos = $Left_Margin +10;

$pdf->selectFont('Arial Bold');
$pdf->addTextWrap($Xpos,$YPos,200 - $Left_Margin,$FontSize,_('ID'),'left');
$pdf->addTextWrap(85,$YPos,60,$FontSize,_('AREA'),'left');
$pdf->addTextWrap(150,$YPos,60,$FontSize,_('TOTAL QTY'),'left');

$XPos = $Left_Margin +160;
for ($i=1;$i<=$_GET['TOP'];$i++){

$XPos = $XPos +38;
$pdf->addTextWrap($XPos,$YPos,600,$FontSize,$i,'centre');
}
$pdf->selectFont('Arial');
$YPos = $YPos - (2*$line_height);
?>