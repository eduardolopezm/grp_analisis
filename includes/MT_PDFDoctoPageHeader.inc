<?php
/* $Revision: 1.14 $ */
/*
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - ($line_height*2);

$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,60);

$FontSize=15;

$XPos = $Page_Width/2 - 60;

$pdf->addText($XPos,$YPos+$line_height,$FontSize, 'TRANSFERENCIA NO: ' . $TransfNo);

$YPos -= ($line_height*3);
$FontSize=12;
$XPos = $Page_Width-$Right_Margin-50;
$pdf->addText($XPos,$YPos +40, $FontSize, _('Page') . ': ' .$PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= 1*$line_height;
$pdf->addText($Left_Margin,$YPos-(0.8*$line_height), $FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Left_Margin,$YPos-(1.6*$line_height), $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($Left_Margin,$YPos-(2.4*$line_height), $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($Left_Margin,$YPos-(3.2*$line_height), $FontSize, ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($Left_Margin,$YPos-(4*$line_height), $FontSize, _('Ph'). ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' ._('Fax').': ' . $_SESSION['CompanyRecord']['fax'] );
$pdf->addText($Left_Margin,$YPos-(4.8*$line_height), $FontSize, _('Email'). ': ' . $_SESSION['CompanyRecord']['email']);

$YPos -=2*$line_height;

/*Now the delivery details */
$YPos -= 8;
$XPos = $Page_Width/2 + 25;
$FontSize=12;
/*
$pdf->addText($XPos+2,$YPos, $FontSize, _('Deliver To') . ':' . 'P01');
$pdf->addText($XPos+2,$YPos-$line_height, $FontSize, 'P02');
$pdf->addText($XPos+2,$YPos-(2*$line_height), $FontSize, 'P03');
$pdf->addText($XPos+2,$YPos-(3*$line_height), $FontSize, 'P04');
$pdf->addText($XPos+2,$YPos-(4*$line_height), $FontSize, 'P05');
*/

/*draw a nice curved corner box around the delivery to address */
/*from the top right */
//$pdf->partEllipse($XPos+250-10,$YPos+10,0,90,10,10);
/*line to the top left */
//$pdf->line($XPos+250-10, $YPos+20,$XPos+10, $YPos+20);
/*Do top left corner */
//$pdf->partEllipse($XPos+10, $YPos+10,90,180,10,10);
/*Do a line to the bottom left corner */
//$pdf->line($XPos+10, $YPos-70-10,$XPos+250-10, $YPos-70-10);
/*Now do the bottom left corner 180 - 270 coming back west*/
//$pdf->partEllipse($XPos+10, $YPos-70,180,270,10,10);
/*Now a line to the bottom right */
//$pdf->line($XPos, $YPos-70,$XPos, $YPos+10);
/*Now do the bottom right corner */
//$pdf->partEllipse($XPos+250-10, $YPos-70,270,360,10,10);
/*Finally join up to the top right corner where started */
//$pdf->line($XPos+250, $YPos-70,$XPos+250, $YPos+10);



/*end of the small table showing the coy name and delivery address details */

$XPos = $Left_Margin;
$YPos -= ($line_height*4.5);         /* 4 lines down */

/*
$font=PDF_findfont($p,"Helvetica-Bold","host",0);
PDF_setfont($p, $font, 12.0);
*/

$YPos -= 20;
$pdf->addText($XPos+1,$YPos, $FontSize, 'Para/Desde: ');
$pdf->addText($XPos+100,$YPos, $FontSize, $almacen);

$YPos-=$line_height;
$pdf->addText($XPos+1,$YPos, $FontSize,  _('Fecha Orden') . ':');
$pdf->addText($XPos+100,$YPos, $FontSize, $fechaTransf);

$YPos -= $line_height;
//$pdf->addText($XPos,$YPos, $FontSize, _('Initiator').': ');
//$pdf->addText($XPos+70,$YPos, $FontSize, $POHeader['initiator']);

$YPos -= $line_height;
$pdf->addText($XPos,$YPos, $FontSize, _('Comentarios') . ':' );

$YPos -= $line_height;
$LeftOvers = $pdf->addTextWrap($XPos, $YPos,350,$FontSize,$commentsTransf, 'left');
if (strlen($LeftOvers)>0){
	$LeftOvers = $pdf->addTextWrap($XPos, $YPos-$line_height,250,$FontSize,$LeftOvers, 'left');
}

$XPos = $Left_Margin;
$YPos -= ($line_height*2);

/*draw a square grid for entering line items */
$pdf->line($Left_Margin, $YPos, $Page_Width-$Right_Margin, $YPos);
$pdf->line($Page_Width-$Right_Margin, $YPos, $Page_Width-$Right_Margin, $Bottom_Margin+200);
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+200, $Left_Margin, $Bottom_Margin+200);
$pdf->line($Left_Margin, $Bottom_Margin+200, $Left_Margin, $YPos);


$YPos -= $line_height;
/*Set up headings */
$FontSize=10;
$pdf->addText($Left_Margin+1,$YPos, $FontSize, _('Codigo') );
$pdf->addText($Left_Margin+65,$YPos, $FontSize, _('Descripcion') );
$pdf->addText($Left_Margin+300,$YPos, $FontSize, _('Cantidad') );
$pdf->addText($Left_Margin+350,$YPos, $FontSize, _('Unidades') );
$pdf->addText($Left_Margin+420,$YPos, $FontSize, _('Costo') );
$pdf->addText($Left_Margin+490,$YPos, $FontSize, _('Total') );

$YPos-=$line_height;

//Para las firmas
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+100, $Page_Width-$Right_Margin-100, $Bottom_Margin+100);
$pdf->addText($Page_Width-$Right_Margin-100,$Bottom_Margin+80, $FontSize, 'RECIBIO :' );

$pdf->line($Left_Margin, $Bottom_Margin+100, $Left_Margin + 100, $Bottom_Margin+100);
$pdf->addText($Left_Margin, $Bottom_Margin+80, $FontSize, 'ENTREGO :' );


/*draw a line */
$pdf->line($Left_Margin, $YPos, $Page_Width-$Right_Margin, $YPos);

?>