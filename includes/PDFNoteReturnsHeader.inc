<?php
if (!$FirstPage){ /* only initiate a new page if its not the first */
	//$pdf->newPage();
}

if ($PageNumber>1){
	$pdf->newPage();
}

$pdf->SetDrawColor(193,193,192); 
$YPos = $Page_Height - 45;//- $Top_Margin;
$YPosdos=$Top_Margin;
$XPos = $Page_Width/2 - 250;
//$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-25,$YPos-25,100,55);
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-20,$YPos-30,0,55);
$FontSize =10;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos+455, $YPos+20, $FontSize, _('Nota Devolucion') . ' ');

$XPos = $Page_Width - 265;
$YPos = $Page_Height - 45;//- $Top_Margin;
$FontSize = 9;
$YPos=$YPos-5;
$XPos = $Page_Width - 130;
//$XPos=$XPos-268;

$pdf->addText($XPos+50, $YPos+10, $FontSize, $_GET['TransNo']);
$pdf->SetFont('helvetica','',8); 
//$pdf->addText($XPos, $YPos, $FontSize, _('Fecha '));
//$pdf->addText($XPos, $YPos, $FontSize, _('# Pagare: '));
$FontSize=7;
$XPos=$XPos+55;
//$pdf->addText($XPos, $YPos+20, $FontSize, $_GET['TransNo']);
/***CAJAS DE TEXTO*****/
//$pdf->addTextWrap(380, $YPos+10,150 ,$FontSize, $foliosat,'right');
//$pdf->addTextWrap($xpos+420,302,150,$FontSize,'$ '.number_format($myrow2['subtotal'],2),'right');

$foliox=explode('|',$myrow3['folio']);
$serie=$foliox[0];
$folio=$foliox[1];
//$aprobaxfolio=TraeAprobacionxFolio($myrow3['rfcempre'],$serie,$folio,$db);
//$aprobacionfolios=explode('|',$aprobaxfolio);
//$Certificado=$aprobacionfolios[0];
//$Noaprobacion=$aprobacionfolios[1];
//$anioAprobacion=$aprobacionfolios[2];


$folio=$serie.$folio;
$pdf->SetFont('helvetica','B',9); 
//$pdf->addText($XPos-60, $YPos+10, 11, $folio,'right');
$pdf->addTextWrap($XPos-120,$YPos+10,150,11,$folio,'right');
$pdf->SetFont('helvetica','',9); 
$pdf->addText($XPos-55, $YPos-20, 10, str_replace(' ','T',$myrow3['fechafact']));
$FontSize=12;


		    
//$pdf->addText($XPos-100, $YPos-80, 8, str_replace(' ','T',$Noaprobacion.'/'.$anioAprobacion));

 //$pdf->SetFont('helvetica','',9); 
//$pdf->addText($XPos-110, $YPos-115, 8, str_replace(' ','T',$Certificado));
$YPos=$YPos-58;
$XPos = $Page_Width - 570;
$FontSize=8;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos, $YPos+13, $FontSize, _('Datos del Proveedor'));
//$pdf->addText($XPos+185, $YPos+13, $FontSize, _('Datos de Embarque'));
$pdf->SetFont('helvetica','',9); 
$XPos=$XPos+80;

$YPos=$YPos-12;

$XPos = $Page_Width - 570;
$FontSize=8;
//$pdf->addText($XPos, $YPos, $FontSize, _('Cliente'));
$XPos=$XPos+80;
//$pdf->addText($XPos-80, $YPos, $FontSize, $myrow3['cliente']);

if (strlen($myrow2['name'])<=25 and strlen($myrow2['name'])<=25) {
	$sobranteNombre = $pdf->addTextWrap($XPos-60, $YPos,170, $FontSize, $myrow2['name']);
}else{
	$sobranteNombre = $pdf->addTextWrap($XPos-60, $YPos,200, $FontSize, $myrow2['name']);
}

$YPos=$YPos-12;
$XPos = $Page_Width - 570;
//$pdf->addText($XPos, $YPos, $FontSize, _('Direccion'));
$XPos=$XPos+80;
if (strlen($myrow2['dir1'])>=25 and strlen($myrow2['dir1'])<=25) {
$pdf->addTextWrap($XPos, $YPos,170, $FontSize, $myrow2['dir1']);
}else{
$pdf->addTextWrap($XPos-30, $YPos,180, $FontSize, $myrow2['dir1']);	
}
$YPos=$YPos-12;
$XPos = $Page_Width - 265;
$XPos=$XPos+80;
if (strlen($myrow2['dir2'])>=25 and strlen($myrow2['dir2'])<=25) {
$pdf->addTextWrap($XPos, $YPos,170, $FontSize, $myrow2['dir2']);
} else{
$pdf->addTextWrap($XPos-30, $YPos,180, $FontSize, $myrow2['dir2']);
}
/*$YPos=$YPos-12;
$XPos = $Page_Width - 265;
$pdf->addText($XPos, $YPos, $FontSize, _('Fecha'));
$XPos=$XPos+80;
$pdf->addText($XPos, $YPos, $FontSize, ConvertSQLDate($myrow2['trandate']));
*/
//**********
//Inicio datos empresa
//**********
//$YPos = $Page_Height - $Top_Margin;
$YPos = $Page_Height +25 ;//- $YPosdos - 10;
$YPos=$YPos-50;
$XPos = $Page_Width/2 - 165 ;
$pdf->SetFont('helvetica','B',8);


$pdf->addText($XPos, $YPos, 7, $myrow3['legalname']);


$pdf->SetFont('helvetica','',8); 
$FontSize=8;
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, 7, 'RFC' . ': ' . $myrow3['rfcempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,6,$myrow3['calleempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,6, $myrow3['coloniaempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,6, $myrow3['Noextempre']. ' ' . $myrow3['cdempre'] . ' ' . $myrow3['edoempre']);
//$YPos=$YPos-10;
//$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, 6, _('Tel') . ':' . $myrow3['phoneempre'] . ' ' . _('Fax') . ': ' . $myrow3['faxempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, 6, _('Email') . ': ' . $myrow3['emailempre']);
//**********
//Fin datos empresa
//**********

//**********
//Inicio de cajita de datos de cliente debtormaster
//**********
$XPos = $Page_Width - 570;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+160,$YPos+170,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+160, $YPos+180,$XPos, $YPos+180);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+170,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+110,$XPos-10, $YPos+170);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+110,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+100,$XPos+159, $YPos+100);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+160, $YPos+110,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+170, $YPos+110,$XPos+170, $YPos+170);//linea derecha de la cajita
/*************************************************************************/



/*********************************/
$YPos = $Page_Height - $Top_Margin + 5;
$FontSize = 10;
$XPos = $Page_Width - 300;
//$YPos=$YPos-10;
$XPos = $Page_Width/2 ;
$FontSize=8;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos+5, $YPos, $FontSize, _('Expedido En: '));
$pdf->SetFont('helvetica','',8); 
$YPos=$YPos-10;
$FontSize=6;
$pdf->addText($XPos+5, $YPos,$FontSize, $myrow3['calleexpe'].' '.$myrow3['Noextexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos+5, $YPos,$FontSize, $myrow3['coloniaexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos+5, $YPos,$FontSize, $myrow3['cdexpe'] . ' ' . $myrow3['edoexpe'] );
$YPosx=680;
$XPosx=410;
$pdf->SetFont('helvetica','B',8); 
//$pdf->addText($XPosx+20, $YPosx,8, _('Número/Año de Aprobacion'));
$YPosx=650;
$XPosx=410;
//$pdf->addText($XPosx+20, $YPosx-10,8, _('No. Certificado Digital'));

$YPosx=602;
$XPosx=40;

$pdf->addText($XPosx, $YPosx,8, _('Cant.'));
$XPosx=$XPosx+35;
$pdf->addText($XPosx, $YPosx,8, _('Codigo'));
$XPosx=$XPosx+60;
$pdf->addText($XPosx, $YPosx,8, _('Descripcion'));
$XPosx=$XPosx+20;	
if ($_SESSION['ShowPriceList']==1){
	$pdf->addText($XPosx+145, $YPosx,8, _('L.P.'));
}


$XPosx=$XPosx+170;
$pdf->addText($XPosx, $YPosx,8, _('Precio'));

if ($_SESSION['ShowWorkers']==1){	
	$pdf->addText($XPosx-70, $YPosx,8, _('Trab'));
}

$XPosx=$XPosx+40;
$pdf->addText($XPosx, $YPosx,8, _('Desc. 1'));
$XPosx=$XPosx+40;

if ($_SESSION['Showdiscount1']==1){	
	$pdf->addText($XPosx, $YPosx,8, _('Desc. 2'));
}
$XPosx=$XPosx+40;
if ($_SESSION['Showdiscount2']==1){
	
	$pdf->addText($XPosx, $YPosx,8, _('Desc. 3'));
}
$XPosx=$XPosx+50;
$pdf->addText($XPosx, $YPosx,8, _('SubTotal'));
$XPosx=$XPosx+60;
$pdf->addText($XPosx, $YPosx,8, _('Total'));


$YPosx=145;
$XPosx=488;
$pdf->addText($XPosx, $YPosx,8, _('Subtotal:'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx,8, _('Impuestos:'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx,8, _('Total:'));


$YPosx=179;
$XPosx=40;
$YPosx=142;
$YPosx=150;
$pdf->addText($XPosx, $YPosx,9, _('Cantidad en letras'));
$YPosx=277;
$pdf->SetFont('helvetica','',8);


//**********
//Inicio de cajita texto
//**********
$XPos = $Page_Width - 570;
$YPos = $Page_Height - $Top_Margin -253;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/***********************/
/* etiquetas*/

$pdf->line($XPos+540, $YPos+90,$XPos-10, $YPos+90);//linea que separa etiquetas
/*************************/
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos-335,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-334,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
//$pdf->line($XPos, $YPos-191,$XPos+531,$YPos-190);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos-334,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos-335,$XPos+540, $YPos+93);//linea derecha de la cajita
//**********


//**********
//Inicio de cajita texto para monto y totales
//**********
$XPos = $Page_Width - 570;
$YPos = $Page_Height - $Top_Margin -700;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
$pdf->line($XPos+445, $YPos+75,$XPos-10, $YPos+75);//linea de arriba de la cajita
/***********************/
/* etiquetas*/

//$pdf->line($XPos+540, $YPos+85,$XPos-10, $YPos+85);//linea que separa etiquetas
/*************************/
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos-5,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-5,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */

/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos-5,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos-5,$XPos+540, $YPos+93);//linea derecha de la cajita
$pdf->line($XPos+445, $YPos-15,$XPos+445, $YPos+103);//linea derecha de la cajita
$pdf->line($XPos, $YPos-15,$XPos+531,$YPos-15);//linea de abajo de la cajita
	$sobranteNombre = $pdf->addTextWrap(40,670+13,180,$FontSize,$myrow3['supplierno'].'-'.$myrow3['nombre']);
	$pdf->addTextWrap(40,670+3,180,$FontSize,$sobranteNombre);
	
	$pdf->addTextWrap(40,660,527,$FontSize,'RFC:'.$myrow3['RFC']);
	
	$pdf->addTextWrap(40,650,170,$FontSize,$myrow3['address1']);
	$pdf->addTextWrap(40,641,170,$FontSize,$myrow3['address2'] .' , '.$myrow3['address3']);
	$pdf->addTextWrap(40,631,117,$FontSize,$myrow3['address4']);

	

$YPos = $Page_Height - $Top_Margin-95 ;
//Fin de cajita texto
?>
