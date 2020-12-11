<?php

/* Archivo modificado por: Maria Alejandra Rosas Portillo
    Fecha de modificacion: 10-11-09 
    Cambios:
    1.- Se dejo toda la funcionalidad en blanco para poder ser reutilizado
    Fin de cambios*/
	 
	$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize = 8;
	$YPos = $Page_Height - $Top_Margin;
	$pdf->selectFont('./fonts/Helvetica.afm');
	$pdf->addText($Left_Margin,$YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$pdf->addText($Page_Width-$Right_Margin-120,$YPos,$FontSize, _('Fecha de impresion'). ': ' . Date($_SESSION['DefaultDateFormat'])  . '   ' . _('Pagina'). ' ' . $PageNumber);

	$YPos -= $line_height;
	$FontSize =10;
	$pdf->selectFont('./fonts/Helvetica-Bold.afm');
	$Heading = _('Reporte del periodo '); // . $PeriodFromDate . _(' al periodo ') . $PeriodToDate;
	$pdf->addText($Left_Margin, $YPos, $FontSize, $Heading);
	
	$YPos -= (2 * $line_height);
	$FontSize = 8;
	
	// Print total for each account
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Indice'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,190,$FontSize,_('Docto'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,40,$FontSize,_('Folio'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+290,$YPos,190,$FontSize,_('Cuenta Contable'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+480,$YPos,70,$FontSize,_('Monto'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+550,$YPos,20,$FontSize,_('N'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+570,$YPos,200,$FontSize,_('Concepto'));
	$pdf->selectFont('./fonts/Helvetica.afm');
	$YPos -= (2 * $line_height);
?>