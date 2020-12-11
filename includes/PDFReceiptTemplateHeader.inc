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
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-20,$YPos-30,0,24);
$FontSize =10;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos+460, $YPos+20, $FontSize, _('RECIBO') . ' ');
$pdf->SetFont('helvetica','',8); 
$XPos = $Page_Width - 265;
$YPos = $Page_Height - 45;//- $Top_Margin;
$FontSize = 9;
$YPos=$YPos-5;
$XPos = $Page_Width - 130;
//$XPos=$XPos-268;

//$pdf->addText($XPos, $YPos+10, $FontSize, _('Folio '));
//$pdf->addText($XPos, $YPos, $FontSize, _('Fecha '));
//$pdf->addText($XPos, $YPos, $FontSize, _('# Pagare: '));
$FontSize=7;
$XPos=$XPos+80;
//$pdf->addText($XPos, $YPos+20, $FontSize, $_GET['TransNo']);
$pdf->addText($XPos, $YPos+10, $FontSize, $foliosat);
$foliox=explode('|',$myrow3['folio']);
$serie=$foliox[0];
$folio=$foliox[1];
$folio=$folio.$serie;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos-50, $YPos+10, 11, $folio);
$pdf->SetFont('helvetica','',9); 
$pdf->addText($XPos-95, $YPos, 10, str_replace(' ','T',$myrow3['fechafact']));
$FontSize=12;


//$pdf->addText($XPos-100, $YPos-80, 8, str_replace(' ','T',$myrow3['numdigital']));

 //$pdf->SetFont('helvetica','',9); 
//$pdf->addText($XPos-110, $YPos-115, 8, str_replace(' ','T',$myrow3['certificado']));
$YPos=$YPos-58;
$XPos = $Page_Width - 570;
$FontSize=8;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos, $YPos, $FontSize, _('Datos del Cliente'));
//$pdf->addText($XPos+200, $YPos, $FontSize, _('Datos de Embarque'));
$pdf->SetFont('helvetica','',9); 
$XPos=$XPos+80;

$YPos=$YPos-12;

$XPos = $Page_Width - 570;
$FontSize=8;
//$pdf->addText($XPos, $YPos, $FontSize, _('Cliente'));
$XPos=$XPos+80;
//$pdf->addText($XPos-80, $YPos, $FontSize, $myrow3['cliente']);
if (strlen($myrow2['name'])>=25 and strlen($myrow2['name'])<=25) {
$pdf->addTextWrap($XPos-60, $YPos,170, $FontSize, $myrow2['name']);
}else{
$pdf->addTextWrap($XPos-60, $YPos,200, $FontSize,$myrow2['name']);
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
$pdf->partEllipse($XPos+200,$YPos+170,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+200, $YPos+180,$XPos, $YPos+180);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+170,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+110,$XPos-10, $YPos+170);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+110,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+100,$XPos+200, $YPos+100);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+200, $YPos+110,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+210, $YPos+110,$XPos+210, $YPos+170);//linea derecha de la cajita
/*************************************************************************/








/*********************************/
$YPos = $Page_Height - $Top_Margin + 5;
$FontSize = 10;
$XPos = $Page_Width - 300;
//$YPos=$YPos-10;
$XPos = $Page_Width/2 ;
$FontSize=8;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos, $YPos, $FontSize, _('Expedido En: '));
$pdf->SetFont('helvetica','',8); 
$YPos=$YPos-10;
$FontSize=6;
$pdf->addText($XPos, $YPos,$FontSize, $myrow3['calleexpe'].' '.$myrow3['Noextexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow3['coloniaexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos, $YPos,$FontSize, $myrow3['cdexpe'] . ' ' . $myrow3['edoexpe'] );
$YPosx=680;
$XPosx=410;
$pdf->SetFont('helvetica','B',8); 
//$pdf->addText($XPosx+20, $YPosx,8, _('Número/Año de Aprobacion'));
$YPosx=650;
$XPosx=410;
//$pdf->addText($XPosx+20, $YPosx-10,8, _('No. Certificado Digital'));

$YPosx=602;
$XPosx=40;

//$pdf->addText($XPosx, $YPosx,8, _('Cant.'));
$XPosx=$XPosx+35;
$pdf->addText($XPosx, $YPosx,8, _('Codigo'));
$XPosx=$XPosx+60;
$pdf->addText($XPosx, $YPosx,8, _('Descripcion'));
$XPosx=$XPosx+185;
//$pdf->addText($XPosx, $YPosx,8, _('Banco'));
$XPosx=$XPosx+35;
//$pdf->addText($XPosx, $YPosx,8, _('Desc. 1'));
$XPosx=$XPosx+40;
//$pdf->addText($XPosx, $YPosx,8, _('Desc. 2'));
$XPosx=$XPosx+40;
$pdf->addText($XPosx, $YPosx,8, _('Subtotal'));
$XPosx=$XPosx+60;
$pdf->addText($XPosx, $YPosx,8, _('IVA'));
$XPosx=$XPosx+50;
$pdf->addText($XPosx, $YPosx,8, _('Total'));


$YPosx=105;
$XPosx=525;
$pdf->addText($XPosx, $YPosx+120,9, _('Subtotal'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx+125,9, _('Impuestos'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx+130,9, _('Total'));


$YPosx=379;
$XPosx=40;
//$pdf->addText($XPosx, $YPosx,9, _('Sello Digital'));
$YPosx=142;
//$pdf->addText($XPosx, $YPosx,9, _('Cadena Original'));
$YPosx=225;
$pdf->addText($XPosx, $YPosx,9, _('Cantidad en letras'));
$YPosx=187;
$pdf->addText($XPosx, $YPosx,9, _('Informacion Comercial'));
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
$pdf->addText($XPosx, $YPosx-12,8, _('Forma de pago:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+80, $YPosx-12,7,$Tipopago.' '.$terminopago);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx+320, $YPosx-12,8, _('Id Docto:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+370, $YPosx-12,7, $_GET['TransNo']);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx-24,8, _('Metodo de Pago:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+80, $YPosx-24,7, _('Varios Moneda: '). $myrow3['moneda']._(' TC: ').(1/$myrow3['tipocambiorec']));
$pdf->SetFont('helvetica','B',8);

$qry = "select bankaccountname 
		from bankaccounts 
			inner join gltrans
			ON accountcode = account
		WHERE typeno=".$_GET['TransNo']."
		and type=12";
$rs = DB_query($qry,$db);
if (DB_num_rows($rs) > 0){
	$reg = DB_fetch_array($rs);
	$pdf->addText($XPosx, $YPosx-36,8, _('Cuenta Banco:'));
	$pdf->SetFont('helvetica','',8);
	$pdf->addText($XPosx+70, $YPosx-36,8, $reg['bankaccountname']);
	
}

//$pdf->addText($XPosx+320, $YPosx-24,8, _('Vendedor:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+370, $YPosx-24,7, $myrow3['vendedor']);

$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-36,8, _('Condiciones:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+80, $YPosx-36,7, _('Genera Interes Moratorio 3% mensual/credito'));
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
//$pdf->addText($XPosx+50, $YPosx-245,8, _('ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFD'));
$pdf->SetFont('helvetica','',8);

//**********
//Fin de cajita de lugar
//**********






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
$YPos = $Page_Height - $Top_Margin -629;
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



$FontSize=7;
if (strpos('XAXX010101000',$myrow3['nombrefrom'])==true){
$pdf->addTextWrap(40,670,527,$FontSize,'PUBLICO GENERAL');
$pdf->addTextWrap(40,660,527,$FontSize,'RFC: XAXX010101000');

/*$pdf->addTextWrap(40,650,170,$FontSize,$myrow3['dirfrom1']);
$pdf->addTextWrap(40,641,117,$FontSize,$myrow3['dirfrom2'] .',');
$pdf->addTextWrap(40,631,117,$FontSize,$myrow3['dirfrom3']);
$pdf->addTextWrap(40,621,117,$FontSize,$myrow3['dirfrom4']);
*/

}else{
$pdf->addTextWrap(40,670,527,$FontSize,$myrow3['nombrefrom']);
$pdf->addTextWrap(40,660,527,$FontSize,'RFC:'.$myrow3['RFC']);

$pdf->addTextWrap(40,650,170,$FontSize,$myrow3['dirfrom1']);
$pdf->addTextWrap(40,641,117,$FontSize,$myrow3['dirfrom2'] .',');
$pdf->addTextWrap(40,631,117,$FontSize,$myrow3['dirfrom3']);
$pdf->addTextWrap(40,621,117,$FontSize,$myrow3['dirfrom4']);
	
}


/*$pdf->addTextWrap(220,670,527,$FontSize,$myrow3['cliente'].' '.$myrow3['nombrefrom']);
$pdf->addTextWrap(220,660,170,$FontSize,$myrow3['dirfrom1']);
$pdf->addTextWrap(220,650,117,$FontSize,$myrow3['dirfrom2'] .',');
$pdf->addTextWrap(220,641,117,$FontSize,$myrow3['dirfrom3']);
$pdf->addTextWrap(220,631,117,$FontSize,$myrow3['dirfrom4']);

*/


$YPos = $Page_Height - $Top_Margin-95 ;
//Fin de cajita texto
?>
