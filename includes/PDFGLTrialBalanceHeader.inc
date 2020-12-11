<?php
header("Content-Type: text/html;charset=utf-8");
/*
ARCHIVO MODIFICADO POR: ISRAEL BARRERA
FECHA DE MODIFICACION: 16-DICIEMBRE-2010 
CAMBIOS:
   1.- Impresion de encabezado de reporte de precio de lista
   2.- Impresion de Razon Social 27-Enero-2011
FIN DE CAMBIOS
*/



$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
	$XPos =0;
}

//$SQL = 'SELECT * FROM'



$FontSize = 8;
$YPos = $Page_Height-$Top_Margin;
$XPos = $XPos + 20;

$YPos = $YPos -50;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos,0,48);

$pdf->selectFont('Arial Bold');
$pdf->addText($Right_Margin +150, $YPos+15,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Right_Margin +150, $YPos,$FontSize, utf8_decode(_('Balance de Comprobación')));
$LeftOvers = $pdf->addTextWrap($Right_Margin +410, $YPos, 150,$FontSize,'Usuario:'.'  '.utf8_decode($_SESSION['UsersRealName']));
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
$pdf->addText($Page_Width-$Right_Margin-50,$YPos +50,$FontSize, utf8_decode(_('Página')) . ' ' . $PageNumber);

$pdf->selectFont('Arail Bold');
$FontSize =10;
if ($_GET['legalid']){
	$pdf->selectFont('Arial Bold');
	$LeftOvers = $pdf->addTextWrap($Right_Margin +170,$YPos -20,300,$FontSize, _($_GET['legalid']));
	$YPostemp = $YPos;
	while (strlen($LeftOvers) > 0) {
		$YPostemp -= 10;
		$LeftOvers = $pdf->addTextWrap($Right_Margin +170,$YPostemp -30,300,$FontSize,$LeftOvers);
	}
	/*$XPos += 180;
	$count++;
	if ($count % $blok == 0){
		$YPos -= 30;
		$XPos = 20;
	}*/
}
$YPos -=30;
$Heading = utf8_decode(_('Balanza de Comprobación '));
$pdf->addText($Left_Margin, $YPos -10, $FontSize, $Heading);
$pdf->addText($Left_Margin, $YPos -20, $FontSize,('Desde').':'. $PeriodFromDate . _(' Hasta: ') . $PeriodToDate);

$count = 0;
$blok = 3;
$XPos = 20;
$YPos = $YPos -50;
$FontSize = 7;


/*$XPos = $Left_Margin +160;
for ($i=1;$i<=$_GET['TOP'];$i++){

$XPos = $XPos +38;
$pdf->addTextWrap($XPos,$YPos,600,$FontSize,$i,'centre');
}
$pdf->selectFont('Arial');
$YPos = $YPos - (2*$line_height);*/
?>