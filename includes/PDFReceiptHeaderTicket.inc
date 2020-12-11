<?php
if (!$FirstPage){ /* only initiate a new page if its not the first */
	//$pdf->newPage();
}

if ($PageNumber>1){
//	$pdf->newPage();
}

$line_height=18;

$FontSize=7;
$pdf->SetDrawColor(193,193,192); 
$YPos = $Page_Height ;//- $Top_Margin;
$YPosdos=$Top_Margin;
$XPos = $Page_Width/2 ;

$FontSize =5;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos+30, $YPosdos+330, $FontSize, _('RECIBO DE PAGO'));
$pdf->SetFont('helvetica','',8);

// datos de la empresa y de serie y folio
$pdf->SetFont('helvetica','B',8); 

$pdf->addText($XPos-80, $YPosdos+315, $FontSize,$myrow3['legalname'] );
$pdf->addText($XPos-80, $YPosdos+310, $FontSize,'RFC' . ': ' . $myrow3['rfcempre']);
$pdf->addText($XPos-80, $YPosdos+305, $FontSize, $myrow3['calleempre']);
$pdf->addText($XPos-80, $YPosdos+300, $FontSize,$myrow3['coloniaempre']);
$pdf->addText($XPos-80, $YPosdos+295, $FontSize,$myrow3['Noextempre']. ' ' . $myrow3['cdempre']);
$pdf->addText($XPos-80, $YPosdos+290, 4, _('Tel') . ':' . $myrow3['phoneempre'] . ' ' . _('Fax') . ': ' . $myrow3['faxempre']);
$pdf->addText($XPos-80, $YPosdos+285, 4,  _('Email') . ': ' . $myrow3['emailempre']);

$pdf->SetFont('helvetica','',8); 
$pdf->addText($XPos+12, $YPosdos+315, $FontSize, 'ERP:'.$_GET['TransNo']);
$pdf->addText($XPos+12, $YPosdos+310, $FontSize, _('Folio:').str_replace("|","",$myrow3['folio']));
$pdf->addText($XPos+12, $YPosdos+305, $FontSize, _('Fecha:').$myrow3['fechafact']);
//$pdf->addText($XPos+7, $YPosdos+300, $FontSize, _('Num / Año Aprobacion:').str_replace(' ','T',$Noaprobacion.'/'.$anioAprobacion));
//$pdf->addText($XPos+7, $YPosdos+295, $FontSize,_('Num. Certificado:'). str_replace(' ','T',$Certificado));
$pdf->SetFont('helvetica','B',8); 
//$pdf->addText($XPos+7, $YPosdos+275, $FontSize,_('Datos Cliente') );
$pdf->addText($XPos-80, $YPosdos+275, $FontSize,_('Datos Cliente') );
$pdf->SetFont('helvetica','',8); 
//datos de sucursal
$pdf->addText($XPos-80, $YPosdos+270, $FontSize, $myrow3['nombrefrom'] );
$pdf->addText($XPos-80, $YPosdos+265, $FontSize,  $myrow3['dirfrom1'] );
$pdf->addText($XPos-80, $YPosdos+260, $FontSize,  $myrow3['dirfrom2'] );
$pdf->addText($XPos-80, $YPosdos+255, $FontSize, $myrow3['dirfrom3'] . ' ' . $myrow3['dirfrom4'] );
//$pdf->addText($XPos-80, $YPosdos+250, $FontSize, 'Tel.'.$myrow3['dirfrim5']  );


$pdf->addTextWrap($XPos-80, $YPosdos+235,100,$FontSize,_('Cant'));
$pdf->addTextWrap($XPos-60, $YPosdos+235,100,$FontSize,_('Codigo'));
$pdf->addTextWrap($XPos-20, $YPosdos+235,100,$FontSize,_('Descripcion'));
$pdf->addTextWrap($XPos+35, $YPosdos+235,100,$FontSize,_('Precio'));
$pdf->addTextWrap($XPos+65, $YPosdos+235,100,$FontSize,_('Importe'));

$XPos = $Page_Width - 265;
$YPos = $Page_Height - 40;//- $Top_Margin;
$FontSize = 9;
$YPos=$YPos-5;
$XPos = $Page_Width - 130;

?>
