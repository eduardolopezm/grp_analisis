<?php
if (!$FirstPage){ /* only initiate a new page if its not the first */
	//$pdf->newPage();
}
$YPos = $Page_Height - $Top_Margin-60;
$FontSize = 10;
$XPos = $Page_Width - 400;
//$YPos=$YPos-10;//
$XPos = $Page_Width/2-100 ;
$FontSize=8;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos, $YPos, $FontSize, _('Expedido En: '));
$pdf->SetFont('helvetica','',8); 
$FontSize=6;
$pdf->addText($XPos+54, $YPos,7, $myrow2['unidadnegocio']);
$YPos=$YPos-10;


$pdf->addText($XPos, $YPos,$FontSize, $myrow2['calleexpe'].' '.$myrow2['Noextexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow2['coloniaexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow2['cdexpe'] . ' ' . $myrow2['edoexpe'] );
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize,'Tel.'.$myrow2['telexpe'] );
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize,'Email: '.$myrow2['tagemail'] );

$YPos = $Page_Height - $Top_Margin;
$XPos = $Page_Width/2 - 250;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos-40,0,60);
$FontSize =15;
$pdf->addText($XPos+350, $YPos, $FontSize, _('PAGARE') . ' ');
$XPos = $Page_Width - 225;
$YPos -= 111;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+185,$YPos+93,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+185, $YPos+103,$XPos, $YPos+103);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+93,$XPos-10, $YPos-14);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-15.5,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-25.5,$XPos+188, $YPos-25.5);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+185, $YPos-15.5,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+195, $YPos-14,$XPos+195, $YPos+93);
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$YPos=$YPos-30;
$XPos = $Page_Width - 225;
//$XPos=$XPos-268;
$pdf->addText($XPos, $YPos+20, $FontSize, _('# Factura ERP: '));
$pdf->addText($XPos, $YPos+10, $FontSize, _('# Folio Fiscal: '));
$pdf->addText($XPos, $YPos, $FontSize, _('# Pagare: '));
$XPos=$XPos+80;
$FontSize=8;
$pdf->addText($XPos, $YPos+20, $FontSize, $_GET['TransNo']);
$pdf->addText($XPos, $YPos+10, $FontSize, $foliosat);
$pdf->addText($XPos, $YPos, $FontSize, $myrow2['transno']);
$YPos=$YPos-12;
$XPos = $Page_Width - 225;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPos, $YPos, $FontSize, _('No. Cliente'));
$pdf->SetFont('helvetica','',8);
$XPos=$XPos+80;
$pdf->addText($XPos, $YPos, $FontSize, $myrow2['debtorno']);
$YPos=$YPos-12;
$XPos = $Page_Width - 225;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPos, $YPos, $FontSize, _('Cliente'));
$pdf->SetFont('helvetica','',8);
$XPos=$XPos+80;

if (strlen($myrow2['name']) > 0) {
	//$YPos -= 10;
	$LeftOvers = $pdf->addTextWrap($XPos-35,$YPos,150,$FontSize,$myrow2['name']);
	while (strlen($LeftOvers) > 1 ) {
		$YPos -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos-35,$YPos,150,$FontSize,$LeftOvers);
	}
}
/*
if (strlen($myrow2['name'])>=25 and strlen($myrow2['name'])<=25) {
	$pdf->addTextWrap($XPos, $YPos,170, 8, $myrow2['name']);
}else{
	$pdf->addTextWrap($XPos-35, $YPos,200, 8,$myrow2['name']);
}
*/
$YPos=$YPos-12;
$XPos = $Page_Width - 225;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPos, $YPos, $FontSize, _('Direccion'));
$pdf->SetFont('helvetica','',8);
$XPos=$XPos+50;
/*if (strlen($myrow2['dir1'])>=25 and strlen($myrow2['dir1'])<=25) {
$pdf->addTextWrap($XPos, $YPos,155, 8, $myrow2['dir1'].' '.$myrow2['brnumext'].' '.$myrow2['brnumint']);
}else{
$pdf->addTextWrap($XPos-30, $YPos,155, 8, $myrow2['dir1'].' '.$myrow2['brnumext'].' '.$myrow2['brnumint']);	
}*/
$direccion=$myrow2['dir1'].' '.$myrow2['brnumext'].' '.$myrow2['brnumint'];
if (strlen($direccion) > 0) {
	//$YPos -= 10;
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,160,7,$direccion);
	while (strlen($LeftOvers) > 1 ) {
		$YPos -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos,160,7,$LeftOvers);
	}
}


$YPos=$YPos-12;
$XPos = $Page_Width - 225;
$XPos=$XPos+5;

$direccion=$myrow2['dirfrom6'].' '.$myrow2['dirfrom2'].' '.$myrow2['dirfrom3'].' '.$myrow2['dirfrom4'].' '.$myrow2['dirfrom5'];
if (strlen($direccion) > 0) {
	//$YPos -= 10;
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,180,7,$direccion);
	while (strlen($LeftOvers) > 1 ) {
		$YPos -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos,180,7,$LeftOvers);
	}
}
/*

if (strlen($myrow2['dir2'])>=25 and strlen($myrow2['dir2'])<=25) {
$pdf->addTextWrap($XPos, $YPos,155, 8, $myrow2['dirfrom6'].' '.$myrow2['dirfrom2'].' '.$myrow2['dirfrom3'].' '.$myrow2['dirfrom4'].' '.$myrow2['dirfrom5']);
} else{
$pdf->addTextWrap($XPos-30, $YPos,155, 8, $myrow2['dirfrom6'].' '.$myrow2['dirfrom2'].' '.$myrow2['dirfrom3'].' '.$myrow2['dirfrom4'].' '.$myrow2['dirfrom5']);
}
*/
$YPos=$YPos-12;
$XPos = $Page_Width - 225;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPos, $YPos, $FontSize, _('Fecha Emision'));
$pdf->SetFont('helvetica','',8);
$XPos=$XPos+80;
$pdf->addText($XPos, $YPos, $FontSize, ConvertSQLDate($myrow2['origtrandate']));


$YPos=$YPos-12;
$XPos = $Page_Width - 225;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPos, $YPos, $FontSize, _('Fecha Vencimiento'));
$pdf->SetFont('helvetica','',8);
$XPos=$XPos+80;
$pdf->addText($XPos, $YPos, $FontSize, ConvertSQLDate($myrow2['trandate']));

//**********
//Inicio datos empresa
//**********
$YPos = $Page_Height - $Top_Margin - 10;
$YPos=$YPos-50;
$XPos = $Page_Width/2 - 250;

$pdf->addText($XPos, $YPos, $FontSize,$myrow2['legalname']);
$FontSize=8;
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, 'RFC' . ': ' . $myrow2['rfcempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow2['calleempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize,$myrow2['colempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow2['cpempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, $myrow2['edoempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, _('Tel') . ' :' .$myrow2['telempre'].' '._('Fax') . ' :' .$myrow2['faxempre'] );
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, _('Email') . ': ' . $myrow2['emailempre']);
//**********
//Fin datos empresa
//**********

//**********
//Inicio de cajita de lugar
//**********
$XPos = $Page_Width - 570;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+140,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+140, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+60,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+60,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+50,$XPos+139, $YPos+50);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+140, $YPos+60,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+150, $YPos+60,$XPos+150, $YPos+93);//linea derecha de la cajita
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 9;
$XPos = $Page_Width - 575;
$YPos -=$YPos-605  ;
$pdf->addText($XPos, $YPos, $FontSize, _('LUGAR EXPEDICION: '));
$YPos=$YPos-10;
//$pdf->addText($XPos, $YPos,$FontSize, $myrow2['calleempre']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow2['cdexpe'] . ' , ' . $myrow2['edoexpe'] );
$YPos=$YPos-10;
//$pdf->addText($XPos, $YPos,$FontSize,$myrow2['edoempre'].' '. $myrow2['cpempre']);
//**********
//Fin de cajita de lugar
//**********

//**********
//Inicio de cajita de fecha
//**********
$XPos = $Page_Width - 374;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+140,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+140, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+60,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+60,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+50,$XPos+139, $YPos+50);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+140, $YPos+60,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+150, $YPos+60,$XPos+150, $YPos+93);//linea derecha de la cajita
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$XPos = $Page_Width - 360;
$YPos -=$YPos-600  ;
$pdf->addText($XPos, $YPos, $FontSize, _('FECHA DE EXPEDICION: '));
//**********
//Fin de cajita de fecha
//**********

//**********
//Inicio de cajita de bueno por
//**********
$XPos = $Page_Width - 180;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+140,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+140, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+60,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+60,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+50,$XPos+139, $YPos+50);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+140, $YPos+60,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+150, $YPos+60,$XPos+150, $YPos+93);//linea derecha de la cajita
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$XPos = $Page_Width - 180;
$YPos -=$YPos-600  ;
$pdf->addText($XPos, $YPos, $FontSize, _('BUENO POR: '));
//$pdf->addText($XPos, $YPos, $FontSize, _('FECHA DE EXPEDICION: '));
//**********
//Fin de cajita de bueno por
//**********
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$XPos = $Page_Width - 350;
$YPos -=$YPos-550  ;
$pdf->addText($XPos, $YPos, $FontSize, _('DATOS DEL DEUDOR '));

//**********
//Inicio de cajita de DATOS DEL DEUDOR
//**********
$XPos = $Page_Width - 570;
$YPos -= 111;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+60,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+60,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+50,$XPos+530, $YPos+50);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos+60,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos+60,$XPos+540, $YPos+93);//linea derecha de la cajita
$pdf->line($XPos-10, $YPos+77,$XPos+540, $YPos+77);//linea de enmedio de la cajita
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 9;
$XPos = $Page_Width - 570;
$YPos -=$YPos-533  ;
$pdf->addText($XPos, $YPos, $FontSize, _('NOMBRE: '));
$YPos -=$YPos-520  ;
$pdf->addText($XPos, $YPos, 8, $myrow2['name']);
$XPos = $Page_Width - 300;
$YPos -=$YPos-533  ;
$pdf->addText($XPos+100, $YPos, $FontSize, _('DOM(CALLE. NUM.) '));
$XPos = $Page_Width - 300;
$YPos -=$YPos-520  ;
$pdf->addText($XPos+50, $YPos, $FontSize, $myrow2['dir1'].' '.$myrow2['brnumext'].' '.$myrow2['brnumint']);
$XPos = $Page_Width - 570;
$YPos -=$YPos-505  ;
$pdf->addText($XPos, $YPos, $FontSize, _('(COLONIA, Z.P.,POBLACION,ESTADO) '));
$pdf->addText($Page_Width-220, $YPos, $FontSize, _('RFC'));
$pdf->addText($Page_Width-120, $YPos, $FontSize, _('TELEFONO'));
$YPos -=$YPos-490  ;
$pdf->addTextWrap($XPos, $YPos,350, $FontSize, $myrow2['dirfrom6'].' '.$myrow2['dirfrom2'].' '.$myrow2['dirfrom3'].' '.$myrow2['dirfrom4'].' '.$myrow2['dirfrom5']);

$XPos = $Page_Width - 220;
$pdf->addTextWrap($XPos, $YPos,350, $FontSize, $myrow2['taxid']);

//$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dirfrom3']);
$XPos = $Page_Width - 135;
$pdf->addTextWrap($XPos, $YPos,350, $FontSize, $myrow2['telcliente']);
//$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dirfrom4']);
$XPos = $Page_Width - 260;
//$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dirfrom5']);
$XPos = $Page_Width - 140;
//$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dirfrom6']);
//**********
//Fin de cajita de DATOS DEL DEUDOR
//**********

//**********
//Inicio de cajita texto
//**********
$XPos = $Page_Width - 570;
$YPos -= 125;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos-103,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-103,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-113,$XPos+531,$YPos-113);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos-103,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos-103,$XPos+540, $YPos+93);//linea derecha de la cajita
//**********
//Fin de cajita texto
//**********
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$XPos = $Page_Width - 550;
$YPos -=$YPos-200  ;
$pdf->addText($XPos, $YPos, $FontSize, _('POR AVAL SIN RESTRICCIONES '));
$XPos = $Page_Width - 200;
$pdf->addText($XPos, $YPos, $FontSize, _('ACEPTO(AMOS) FIRMA(S)'));
//**********
//Inicio de aval sin restricciones
//**********
$XPos = $Page_Width - 570;
$YPos -= 115;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+200,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+200, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos-5,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-5,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-15,$XPos+200, $YPos-15);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+200, $YPos-5,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+210, $YPos-5,$XPos+210, $YPos+93);//linea derecha de la cajita
$pdf->line($XPos-10, $YPos+65,$XPos+210, $YPos+65);//linea de enmedio1 de la cajita
$pdf->line($XPos-10, $YPos+25,$XPos+210, $YPos+25);//linea de enmedio2 de la cajita
$FontSize =8;
$YPos+= 90;
$pdf->addText($XPos, $YPos, $FontSize, _('NOMBRE: '));
$YPos=$YPos-35;
$pdf->addText($XPos, $YPos, $FontSize, _('DOMICILIO (CALLE,NUM,COLONIA,Z.P, POBLACION) '));
$YPos=$YPos-40;
$pdf->addText($XPos, $YPos, $FontSize, _('TELEFONO'));
//**********
//Fin de aval sin restricciones
//**********
//**********
//Inicio de aceptamos firmas
//**********
$XPos = $Page_Width - 280;
$YPos = 85;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+220,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+220, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos-5,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-5,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-15,$XPos+220, $YPos-15);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+220, $YPos-5,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+230, $YPos-5,$XPos+230, $YPos+93);//linea derecha de la cajita
$pdf->line($XPos+100, $YPos-15,$XPos+100, $YPos+103);//linea de enmedioparada de la cajita
$pdf->line($XPos-10, $YPos+25,$XPos+230, $YPos+25);//linea de enmedioacostada de la cajita
$YPos+= 90;
$pdf->addText($XPos, $YPos, $FontSize, _('AVAL '));
$pdf->addText($XPos+110, $YPos, $FontSize, _('DEUDOR '));
$FontSize =7;
$YPos=$YPos-77;
if (strlen($myrow2['name']) > 0) {
	//$YPos -= 10;
	$LeftOvers = $pdf->addTextWrap($XPos+101,$YPos,130,$FontSize,$myrow2['name'],'center');
	while (strlen($LeftOvers) > 1 ) {
		$YPos -= 10;
		$LeftOvers = $pdf->addTextWrap($XPos+101,$YPos,130,$FontSize,$LeftOvers);
	}
}

//$pdf->addTextWrap($XPos+101, $YPos,150, $FontSize, $myrow2['name']);
//**********
//Fin de aceptamos firmas
//**********
$YPos = $Page_Height - $Top_Margin - 10;
$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;
$FontSize=10;
//$pdf->addText($XPos, $YPos, $FontSize, _('Datos Cliente') . ':');
$XPos +=80;
//$pdf->addText($XPos, $YPos, $FontSize, $myrow2['name']);
//$pdf->addText($XPos, $YPos-14, $FontSize, $myrow2['address1']);
//$pdf->addText($XPos, $YPos-28, $FontSize, $myrow2['address2']);
//$pdf->addText($XPos, $YPos-42, $FontSize, $myrow2['address3'] . ' ' . $myrow2['address4']);
$XPos -=80;
$YPos -=($line_height*4);
$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin ;
$XPos = $Page_Width/2;
$XPos = $Left_Margin;
$YPos -= ($line_height*2)+85;
//$pdf->addText($Left_Margin, $YPos-8, $FontSize, _('Tipo de Cambio') . ' - ' . $myrow['currcode']);
/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
//$pdf->partEllipse($YPos, $Bottom_Margin+410,0,90,10,10);
/*line to the top left */
//$YPos=$Page_Width-$Right_Margin+90;
//$pdf->line($YPos, $Bottom_Margin+410,$Left_Margin+10, $Bottom_Margin+410);
/*Dow top left corner */
//$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+400,90,180,10,10);
/*Do a line to the bottom left corner */
//$pdf->line($Left_Margin, $Bottom_Margin+400,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
//$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
//$pdf->line($Left_Margin+10, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
//$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
//$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $Bottom_Margin+400);
$YPos -= ($line_height*2);
/*Set up headings */
$FontSize=10;
$YPos-=8;
/*draw a line */
$pdf->line($XPos, $YPos+50,$Page_Width-$Right_Margin, $YPos+50);
$YPos -= ($line_height);
?>
