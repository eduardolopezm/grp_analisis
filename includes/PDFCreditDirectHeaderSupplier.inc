<?php
if (!$FirstPage){ /* only initiate a new page if its not the first */
	//$pdf->newPage();
}
$pdf->SetDrawColor(193,193,192); 
$YPos = $Page_Height - 45;//- $Top_Margin;
$YPosdos=$Top_Margin;
$XPos = $Page_Width/2 - 250;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-20,$YPos-35,0,30);
$FontSize =9;
$line_height=18;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPos+380, $YPos+5,$FontSize,$myrow3['typename'] . ' ');
$pdf->addText($XPos+480, $YPos-15, $FontSize, 'Folio:'.$myrow3['Trans'] . ' ');
$pdf->addText($XPos+445, $YPos-35, $FontSize, 'Fecha:'.$myrow3['fechadoc'] . ' ');
$pdf->SetFont('helvetica','',8); 
$XPos = $Page_Width - 265;
$YPos = $Page_Height - 45;//- $Top_Margin;
$FontSize = 9;
$YPos=$YPos-5;
$XPos = $Page_Width - 130;
$FontSize=7;
$XPos=$XPos+80;

$YPos=$YPos-78;
$XPos = $Page_Width - 570;
$FontSize=8;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos, $YPos, $FontSize, _('Datos del Proveedor'));
$pdf->SetFont('helvetica','',9); 
$XPos=$XPos+80;

$YPos=$YPos-12;
$XPos = $Page_Width - 570;
$FontSize=8;
$XPos=$XPos+80;
$YPos=$YPos-12;
$XPos = $Page_Width - 570;
$XPos=$XPos+80;
$YPos=$YPos-12;
$XPos = $Page_Width - 265;
$XPos=$XPos+80;

//**********
//Inicio datos empresa
//**********
//$YPos = $Page_Height - $Top_Margin;
$YPos = $Page_Height +10;//- $YPosdos - 10;
$YPos=$YPos-50;
$XPos = $Page_Width/2 - 100 ;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos-30, $YPos, 7, $myrow3['legalname']);
$pdf->SetFont('helvetica','',8); 
$FontSize=8;
$YPos=$YPos-10;
$pdf->addText($XPos-30, $YPos, 7, 'RFC' . ': ' . $myrow3['rfcempre']);
$YPos=$YPos-10;
$pdf->addText($XPos-30, $YPos,6,$myrow3['calleempre']);
$YPos=$YPos-10;
$pdf->addText($XPos-30, $YPos,6, $myrow3['coloniaempre']);
$YPos=$YPos-10;
$pdf->addText($XPos-30, $YPos,6, $myrow3['Noextempre']. ' ' . $myrow3['cdempre']);
//$YPos=$YPos-10;
//$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$YPos=$YPos-10;
$pdf->addText($XPos-30, $YPos, 6, _('Tel') . ':' . $myrow3['phoneempre'] . ' ' . _('Fax') . ': ' . $myrow3['faxempre']);
$YPos=$YPos-10;
$pdf->addText($XPos-30, $YPos, 6, _('Email') . ': ' . $myrow3['emailempre']);
//**********
//Fin datos empresa
//**********

//**********
//Inicio de cajita de datos de cliente debtormaster
//**********
//$XPos = $Page_Width - 570;
//$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
//$pdf->partEllipse($XPos+140,$YPos+170,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
//$pdf->line($XPos+140, $YPos+180,$XPos, $YPos+180);//linea de arriba de la cajita
/*Dow top left corner */
//$pdf->partEllipse($XPos, $YPos+170,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
//$pdf->line($XPos-10, $YPos+110,$XPos-10, $YPos+170);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
//$pdf->partEllipse($XPos, $YPos+110,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
//$pdf->line($XPos, $YPos+100,$XPos+139, $YPos+100);//linea de abajo de la cajita
/*Now do the bottom right corner */
//$pdf->partEllipse($XPos+140, $YPos+110,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
//$pdf->line($XPos+150, $YPos+110,$XPos+150, $YPos+170);//linea derecha de la cajita
/*************************************************************************/





/*************************************************************************/
//**********
//Inicio de cajita de datos de cliente fecha y num certificado
//**********
$XPos = $Page_Width - 180;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */




/*********************************/
$YPos = $Page_Height - $Top_Margin -10;
$FontSize = 10;
$XPos = $Page_Width - 150;
//$YPos=$YPos-10;
$XPos = $Page_Width/2 + 45;
$FontSize=8;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos-35, $YPos, $FontSize, _('Expedido En: '));
$pdf->SetFont('helvetica','',8); 
$YPos=$YPos-10;
$FontSize=6;
$pdf->addText($XPos-35, $YPos,$FontSize, $myrow3['calleexpe'].' '.$myrow3['Noextexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos-35, $YPos,$FontSize, $myrow3['coloniaexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos-35, $YPos,$FontSize, $myrow3['cdexpe'] . ' ' . $myrow3['edoexpe'] );
$YPosx=680;
$XPosx=410;
$pdf->SetFont('helvetica','B',8);

$YPosx=650;
$XPosx=410;

$YPosx=592;
$XPosx=40;

//$pdf->addText($XPosx, $YPosx,8, _('Cant.'));
//$XPosx=$XPosx+35;
$pdf->addText($XPosx, $YPosx,8, _('No. Documento'));
$XPosx=$XPosx+120;
$pdf->addText($XPosx, $YPosx,8, _('Descripcion'));
$XPosx=$XPosx+180;
//$pdf->addText($XPosx, $YPosx,8, _('Precio'));
$XPosx=$XPosx+40;
//$pdf->addText($XPosx, $YPosx,8, _('Desc. 1'));
$XPosx=$XPosx+40;

$XPosx=$XPosx+40;

$XPosx=$XPosx+30;
$pdf->addText($XPosx, $YPosx,8, _('SubTotal'));
$XPosx=$XPosx+50;
$pdf->addText($XPosx, $YPosx,8, _('Total'));


$YPosx=305;
$XPosx=525;
$pdf->addText($XPosx, $YPosx-20,9, _('Subtotal'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx-15,9, _('Impuestos'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx-10,9, _('Total'));


$YPosx=179;
$XPosx=40;
$YPosx=142;

$YPosx=305;
$pdf->addText($XPosx, $YPosx-15,9, _('Cantidad en letra'));
$YPosx=267;
$pdf->addText($XPosx, $YPosx-15,9, _('Informacion Comercial'));
 $pdf->SetFont('helvetica','',8);
$SQL=" SELECT count(*) as pagares
       FROM  debtortrans
       WHERE debtortrans.type=70
	AND debtortrans.order_=" . $_GET['TransNo'] ;
$Resultd= DB_query($SQL,$db);
if (DB_num_rows($Resultd)==0) {
	$Tipopago="Una Exhibicion";
}else{
	$myrowpag = DB_fetch_array($Resultd);
	$numpagares=intval($myrowpag['pagares']);
	if ($numpagares<=1){
		$Tipopago="Una Exhibicion";
	}else{
		$Tipopago="Parcialidades";
	}
}
$SQL=" SELECT * 
       FROM  paymentterms
       WHERE termsindicator='" . $myrow3['termino']."'" ;
$Resultd= DB_query($SQL,$db);
if (DB_num_rows($Resultd)>0) {
	$myrowpag = DB_fetch_array($Resultd);
	$terminopago=$myrowpag['terms'];
}
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx-28,8, _('Forma de pago:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+80, $YPosx-28,7,$Tipopago.' '.$terminopago);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx+320, $YPosx-28,8, _('Id Docto:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+370, $YPosx-28,7, $_GET['TransNo']);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx-58,8, _('Metodo de Pago:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+80, $YPosx-58,7, _('Varios Moneda: '). $myrow3['moneda']._(' TC: ').(1/$myrow3['tipocambio']));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+320, $YPosx-24,8, _('Vendedor:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+370, $YPosx-24,7, $myrow3['vendedor']);

$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx-42,8, _('Condiciones:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+80, $YPosx-42,7, _('Genera Interes Moratorio 3% mensual/credito'));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+320, $YPosx-36,8, _('Usuario Registro:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+400, $YPosx-36,7, $myrow3['userr']);
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-48,8, _('Cuenta Referenciada:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+100, $YPosx-48,7, $myrow3['bancos']._(' Referencia:') . $myrow3['ref1']);
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+10, $YPosx-65,6, _('La tenencia de esta factura no acredita su pago si no se justifica con el comprobante respectivo.'));
$pdf->SetFont('helvetica','B',8);

$pdf->SetFont('helvetica','',8);

//**********
//Fin de cajita de lugar
//**********

//**********
//Inicio de cajita texto
//**********
$XPos = $Page_Width - 570;
$YPos = $Page_Height - $Top_Margin -263;
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
$pdf->line($XPos-10, $YPos-181,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-181,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-191,$XPos+531,$YPos-190);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos-180,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos-180,$XPos+540, $YPos+93);//linea derecha de la cajita
//**********


//**********
//Inicio de cajita texto para monto y totales
//**********
$XPos = $Page_Width - 570;
$YPos = $Page_Height - $Top_Margin -565;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
$pdf->line($XPos+460, $YPos+65,$XPos-10, $YPos+65);//linea de arriba de la cajita
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
$pdf->line($XPos+460, $YPos-15,$XPos+460, $YPos+103);//linea derecha de la cajita
$pdf->line($XPos, $YPos-15,$XPos+531,$YPos-15);//linea de abajo de la cajita


//**********
//Inicio de cajita texto para sello y cadena original
//**********


$FontSize=7;
	$sobranteNombre = $pdf->addTextWrap(40,670-30,300,$FontSize,_('PROVEEDOR') . ':' .' '.$myrow3['codsupp'].' '.$myrow3['suupplierno'].' - '.$myrow3['nombrefrom']);
	$pdf->addTextWrap(40,670-30,300,$FontSize,$sobranteNombre);
//$pdf->addTextWrap(40,670,527,$FontSize,$myrow3['nombrefrom']);
$pdf->addTextWrap(40,660-30,527,$FontSize,'RFC:'.''.$myrow3['RFC']);

$pdf->addTextWrap(40,650-30,170,$FontSize,_('CALLE') . ':' .' '.$myrow3['dirfrom1']);
$pdf->addTextWrap(350,670-30,117,$FontSize,_('COLONIA') . ':' .' '.$myrow3['dirfrom2'] .',');
$pdf->addTextWrap(350,660-30,117,$FontSize,_('ESTADO') . ':' .' '.$myrow3['dirfrom3']);
$pdf->addTextWrap(350,650-30,117,$FontSize,$myrow3['dirfrom4']);
	
$pdf->SetFont('helvetica','',8);

$YPos = $Page_Height - $Top_Margin-95 ;
//Fin de cajita texto
?>
