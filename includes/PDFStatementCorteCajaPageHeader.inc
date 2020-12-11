<?php
/* $Revision: 1.11 $ */
/* PDFlib code to set up a new page */

/*
**Modificado 16-12-2009
**FRUEBEL CANTERA
**MODIFIQUE EL ORDEN DE MOSTRAR LA INFORMACION
**
*/

$Perforation = $Page_Width - $RightMargin - 200;
$YPos = $Page_Height - $Top_Margin;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos-10,0,30);

// Title
$FontSize =15;
$XPos = $Page_Width - 210;
$pdf->addText($XPos, $YPos,$FontSize, _('Corte de Caja   ') . "   :" . strftime("%d-%m-%Y",strtotime($fechacorte)));

$FontSize = 12;


//$pdf->addText($XPos + 80, $YPos,$FontSize, ' '. _('de').' ' . Date($_SESSION['DefaultDateFormat']));

// Remittance header

$YPosR = $YPos;
$FontSize = 10;
$LineHeight = 13;
$LineCountR = 0;
$Remit1 = $Perforation +2;

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 30;

$FontSize = 10;
$LineHeight = 13;
$LineCount = 0;

$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize = 8;
$LineHeight = 10;

$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);

$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);

$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] );

$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice4'] );

$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice5'] );  

$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Telefono') . ':' . $_SESSION['CompanyRecord']['telephone']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('USUARIO') . ': ' . $_SESSION['UserID']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('UNIDAD DE NEGOCIO') . ': ' . $nombreunidadnegocio);
/*Now the remittance section just company and return postal address */

$FontSize = 8;
$LineHeight = 13;


$LineCountR += 7;
$pdf->addText($XPos + 250, $YPosR-$LineCountR*$LineHeight,$FontSize, 'Realizo Prepoliza: ' . $userid . ' - ' . $realname);

/*Now the customer details and statement address */

$FontSize = 10;

$YPos = $Page_Height - $Top_Margin - 120;

$YPos = $Page_Height - $Top_Margin - 150;

/*Now note the customer code in the remittance section too */

//$FontSize=10;
//$LineCountR += 2;
//$pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Customer Code').': ' . $StmtHeader['debtorno']);

$FontSize=7;
$XPos = $Page_Width/2 + 95;
$YPos = $Page_Height - $Top_Margin - 130;
//$YPos -= $line_height;
$XPos = $Left_Margin;


?>
