<?php
	/* 
	 * PDF page header for the trial balance report.
	 * Suren Naidu 18/08/2005
	 *
	 */
	 
	$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize = 8;
	$YPos = $Page_Height - $Top_Margin;
	$pdf->selectFont('./fonts/Helvetica.afm');
	$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Impreso el '). ': ' . Date($_SESSION['DefaultDateFormat'])  . '   ' . _('Pagina'). ' ' . $PageNumber);

	$YPos -= $line_height;
	$FontSize =10;
	$pdf->selectFont('./fonts/Helvetica-Bold.afm');
	$Heading = _('Balanza de Comprobacion Desde: ') . $PeriodFromDate . _(' Hasta: ') . $PeriodToDate;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);
	
	
	$YPos -= (2 * $line_height);
	$FontSize = 8;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Codigo Cuenta'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,_('Nombre de la Cuenta'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,70,$FontSize,_('Saldo Inicial'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,_('Cargos'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,_('Abonos'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,_('Saldo Final'),'right');
	
	$YPos -= (1 * $line_height);
	$FontSize = 8;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,'');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,'');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,140,$FontSize,_('deudora   acreedora'),'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,'','right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,'','right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+440,$YPos,140,$FontSize,_('deudora   acreedora'),'left');
	
	$pdf->selectFont('./fonts/Helvetica.afm');
	$YPos -= (1 * $line_height);
?>