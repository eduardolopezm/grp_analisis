<?php
if (!$FirstPage){ /* only initiate a new page if its not the first */
	//$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin;
$XPos = $Page_Width/2 - 250;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,$YPos-40,0,60);
$FontSize =15;
$pdf->addText($XPos+350, $YPos, $FontSize, _('PAGARE') . ' ');
$XPos = $Page_Width - 265;
$YPos -= 111;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+225,$YPos+93,0,90,10,10);
/*line to the top left */
$pdf->line($XPos+225, $YPos+103,$XPos, $YPos+103);
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+93,$XPos-10, $YPos);
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos,180,270,10,10);
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-10,$XPos+225, $YPos-10);
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+225, $YPos,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($XPos+235, $YPos,$XPos+235, $YPos+93);
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$YPos=$YPos-30;
$XPos = $Page_Width - 265;
//$XPos=$XPos-268;
$pdf->addText($XPos, $YPos, $FontSize, _('# Pagare: '));
$XPos=$XPos+80;
$pdf->addText($XPos, $YPos, $FontSize, $myrow2['transno']);
$YPos=$YPos-12;
$XPos = $Page_Width - 265;
$pdf->addText($XPos, $YPos, $FontSize, _('Cliente'));
$XPos=$XPos+80;
if (strlen($myrow2['name'])>=25 and strlen($myrow2['name'])<=25) {
$pdf->addTextWrap($XPos, $YPos,170, $FontSize, $myrow2['name']);
}else{
$pdf->addTextWrap($XPos-35, $YPos,200, $FontSize,$myrow2['name']);
}
$YPos=$YPos-12;
$XPos = $Page_Width - 265;
$pdf->addText($XPos, $YPos, $FontSize, _('Direccion'));
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
$YPos=$YPos-12;
$XPos = $Page_Width - 265;
$pdf->addText($XPos, $YPos, $FontSize, _('Fecha'));
$XPos=$XPos+80;
$pdf->addText($XPos, $YPos, $FontSize, ConvertSQLDate($myrow2['trandate']));

//**********
//Inicio datos empresa
//**********
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
$pdf->addText($XPos, $YPos, $FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);
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
$FontSize = 10;
$XPos = $Page_Width - 575;
$YPos -=$YPos-605  ;
$pdf->addText($XPos, $YPos, $FontSize, _('LUGAR: '));
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
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
$YPos -=$YPos-590  ;
$pdf->addText($XPos, $YPos, $FontSize, _('BUENO POR: '));
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
$pdf->addText($XPos, $YPos, $FontSize, $myrow2['name']);
$XPos = $Page_Width - 300;
$YPos -=$YPos-533  ;
$pdf->addText($XPos, $YPos, $FontSize, _('DOM(CALLE. NUM.) '));
$XPos = $Page_Width - 300;
$YPos -=$YPos-520  ;
$pdf->addText($XPos, $YPos, $FontSize, $myrow2['dir1']);
$XPos = $Page_Width - 570;
$YPos -=$YPos-505  ;
$pdf->addText($XPos, $YPos, $FontSize, _('(COLONIA, Z.P.,POBLACION,ESTADO) '));
$YPos -=$YPos-494  ;
$pdf->addTextWrap($XPos, $YPos,115, $FontSize, $myrow2['dir2']);
$XPos = $Page_Width - 445;
$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dir3']);
$XPos = $Page_Width - 360;
$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dir4']);
$XPos = $Page_Width - 260;
$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dir5']);
$XPos = $Page_Width - 140;
$pdf->addTextWrap($XPos, $YPos,70, $FontSize, $myrow2['dir6']);
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
$pdf->line($XPos-10, $YPos-33,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-34,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-44,$XPos+531,$YPos-44);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos-34,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos-33,$XPos+540, $YPos+93);//linea derecha de la cajita
//**********
//Fin de cajita texto
//**********
$YPos = $Page_Height - $Top_Margin - 10;
$FontSize = 10;
$XPos = $Page_Width - 550;
$YPos -=$YPos-300  ;
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
//**********
//Fin de aval sin restricciones
//**********
//**********
//Inicio de aceptamos firmas
//**********
$XPos = $Page_Width - 280;
$YPos = 185;
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
$YPos=$YPos-87;
$pdf->addTextWrap($XPos+101, $YPos,150, $FontSize, $myrow2['name']);
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
