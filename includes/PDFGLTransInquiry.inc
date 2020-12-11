<?php
$typeSQL = 'SELECT typename,
		   typeno
	    FROM systypescat
	    WHERE typeid = ' . $_GET['TypeID'];
$TypeResult = DB_query($typeSQL,$db);
if ( DB_num_rows($TypeResult) == 0 ){
//prnMsg(_('No transaction of this type with id') . ' ' . $_GET['TypeID'],'error');
//echo $menuUrl;
} else {
	$myrow = DB_fetch_row($TypeResult);
	DB_free_result($TypeResult);
	$TransName = $myrow[0];
}
$YPos = $Page_Height - $Top_Margin;
$XPos = $Page_Width/2 - 250;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos-40,0,60);
$FontSize =12;
$pdf->addText($XPos+300, $YPos-65, $FontSize, _('TRANSACCIONES CONTABLES'));
$pdf->addText($XPos+320, $YPos-80, $FontSize, strtoupper($TransName));
$XPos = $Page_Width - 265;
$YPos -= 111;
//draw a nice curved corner box around the billing details 
//rom the top right 
$pdf->partEllipse($XPos+225,$YPos+93,0,90,10,10);
//line to the top left 
$pdf->line($XPos+225, $YPos+103,$XPos, $YPos+103);
//Dow top left corner 
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);
//Do a line to the bottom left corner 
$pdf->line($XPos-10, $YPos+93,$XPos-10, $YPos);
//Now do the bottom left corner 180 - 270 coming back west
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
//Now a line to the bottom right 
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
//Now do the bottom right corner 
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
//Finally join up to the top right corner where started 
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+93);
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$YPos=$YPos-30;
$XPos = $Page_Width - 265;




//$XPos=$XPos-268;
//Inicio datos empresa
$YPos = $Page_Height - $Top_Margin - 10;
$YPos=$YPos-50;
$XPos = $Page_Width/2 - 250;
$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
$FontSize=8;
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, 'RFC' . ': ' . $_SESSION['CompanyRecord']['gstno']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, _('Telefono') . ' ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, _('Email') . ' ' . $_SESSION['CompanyRecord']['email']);
//Fin de cajita de fecha
$XPos = $Page_Width - 570;
$YPos -= 125;

//draw a nice curved corner box around the billing details 
//from the top right 
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
//line to the top left 
$pdf->line($XPos+540, $YPos+70,$XPos-10, $YPos+70);//linea de arriba de la cajita
//$YPos=$YPos+60;
$FontSize=10;
$XPosx=$XPos;
$YPosx=$YPos+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Fecha'));
$XPosx=$XPosx+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Periodo'));
$XPosx=$XPosx+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Cuenta'));
$XPosx=$XPosx+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Cargos'));
$XPosx=$XPosx+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Abonos'));
$XPosx=$XPosx+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Descripcion'));
$XPosx=$XPosx+80;
$pdf->addText($XPosx, $YPosx, $FontSize, _('Publicado'));


//Dow top left corner 
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
//Do a line to the bottom left corner 
$pdf->line($XPos-10, $YPos-450,$XPos-10, $YPos+93);//linea izquierda de la cajita
//Now do the bottom left corner 180 - 270 coming back west
$pdf->partEllipse($XPos, $YPos-450,180,270,10,10);//curvita inferiror izquierda
//Now a line to the bottom right 
$pdf->line($XPos, $YPos-460,$XPos+531,$YPos-460);//linea de abajo de la cajita
$pdf->line($XPos-10, $YPos-430,$XPos+540,$YPos-430);//linea de abajo de la cajita
$XPosx=$XPos;
$XPosx=$XPosx+180;
$YPostot=$YPos;
$pdf->addText($XPosx, $YPos-445, 12, _('Totales'));
//$XPosx=$XPosx+80;
//$pdf->addText($XPosx, $YPosx, $FontSize, _('Abonos'));
//$XPosx=$XPosx+80;
//$pdf->addText($XPosx, $YPosx, $FontSize, _('Descripcion'));

//Now do the bottom right corner 
$pdf->partEllipse($XPos+530, $YPos-450,270,360,10,10);//curvita inferior derecha
//Finally join up to the top right corner where started 
$pdf->line($XPos+540, $YPos-450,$XPos+540, $YPos+93);//linea derecha de la cajita



?>
