<?php
	/* 
	 * PDF page header for the profit and loss report.
	 * Suren Naidu 28/08/2005
	 *
	 */
	 
	$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize = 8;
	$YPos = $Page_Height - $Top_Margin;
	$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	
	$YPos -= $line_height;
	$FontSize =10;
	$pdf->selectFont('./fonts/Helvetica-Bold.afm');
	$Heading = _('Estado de Resultados Desde: ') . $PeriodFromDate . _(' Hasta:') . $PeriodToDate;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);

	$FontSize = 8;
	$pdf->selectFont('./fonts/Helvetica.afm');
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize,
		_('Impreso'). ': ' . Date($_SESSION['DefaultDateFormat'])
		. '   '. _('Pagina'). ' ' . $PageNumber);

	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Cuenta'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,_('Nombre'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,_('Periodo Actual'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,70,$FontSize,_('%ING'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+410,$YPos,70,$FontSize,_('Acumulado'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+450,$YPos,70,$FontSize,_('%ING'),'right');
	$YPos -= (2 * $line_height);
?>
