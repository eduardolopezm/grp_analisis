<?php
/*
ARCHIVO MODIFICADO POR: ISRAEL BARRERA
FECHA DE MODIFICACION: 16-DICIEMBRE-2010 
CAMBIOS:
   1.- Impresion de encabezado de reporte de precio de lista
FIN DE CAMBIOS
*/
/*ARCHIVO MODIFICADO POR: ISRAEL BARRERA
FECHA DE MODIFICACION: 27-ENERO-2011
CAMBIOS:
   1.- SE AGREGO CONSULTA PARA QUE IMPRIMA LA RAZON SOCIAL SELECCIONADA 
FIN DE CAMBIOS
*/
$PageNumber++;
if ($PageNumber>1){
	$pdf->newPage();
	$XPos =0;
}

$FontSize = 8;
$YPos = $Page_Height-$Top_Margin;
$XPos = $XPos + 20;

$YPos = $YPos -50;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos,0,48);

$pdf->selectFont('Arial Bold');
$pdf->addText($Right_Margin +80, $YPos+15,$FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Right_Margin +80, $YPos,$FontSize, _('Estado de Resultados X Unidad de Negocio'));
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

$sql='SELECT legalname FROM legalbusinessunit WHERE legalid='.$_GET['legalid'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$n_razonsocial = $myrow[0];
	
$pdf->selectFont('Arail Bold');
$FontSize =10;
$Heading = _('Estado de Resultados ');
$pdf->addText($Left_Margin, $YPos -10, $FontSize, $Heading);
$pdf->addText($Left_Margin, $YPos -30, $FontSize,('Desde').':'. $PeriodFromDate . _(' Hasta: ') . $PeriodToDate);
$pdf->addText($Left_Margin, $YPos -20, $FontSize, $n_razonsocial);
	
$count = 0;
$blok = 3;
$XPos = 20;
$YPos = $YPos -50;
$FontSize = 7;

/*
for ($i=1;$i<=$_GET['TOP'];$i++){

$XPos = $XPos +38;
$pdf->addTextWrap($XPos,$YPos,600,$FontSize,$i,'centre');
}
$pdf->selectFont('Arial');
$YPos = $YPos - (2*$line_height);*/
?>