<?php
/* $Revision: 1.11 $ */
/* PDFlib code to set up a new page */
/*
$PARCHE 10.1.1
ARCHIVO MODIFICADO POR: Desarrollador
FECHA DE CREACION: 28/OCTUBRE/2010
DESCRIPCION: se anexo en la impresion de la poliza el mes de acuerdo al periodo en curso.
*/

/*
**Modificado 16-12-2009
**FRUEBEL CANTERA
**MODIFIQUE EL ORDEN DE MOSTRAR LA INFORMACION
**
*/

if ($PageNumber>1){
	$pdf->newPage();
}

$Perforation = $Page_Width - $RightMargin - 200;

$YPos = $Page_Height - $Top_Margin;
$_SESSION["FlagLogo"];
if ($_SESSION["FlagLogo"]==1){
	$pdf->addJpegFromFile($myrow2['logo'],$Left_Margin,$YPos-10,0,30);
}else{
	$pdf->addJpegFromFile($myrow2['logo'],$Left_Margin,$YPos-10,0,40);
}
	


// Title
$FontSize =15;
$XPos = $Page_Width/2 - 110;
$pdf->addText($XPos, $YPos,$FontSize, _('POLIZA CONTABLE') );

$FontSize = 12;


//$pdf->addText($XPos + 80, $YPos,$FontSize, ' '. _('de').' ' . Date($_SESSION['DefaultDateFormat']));

// Remittance header

$YPosR = $YPos;
$FontSize = 10;
$LineHeight = 13;
$LineCountR = 0;
$Remit1 = $Perforation +2;

#$pdf->addText($Remit1, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Remittance Advice') );
#$LineCountR += 1;
#$pdf->addText($Remit1, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Statement dated').' ' . Date($_SESSION['DefaultDateFormat']));
#$LineCountR += 1;
#$pdf->addText($Remit1, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Page').': ' . $PageNumber);

/*Also show the page number on the main section */
#$pdf->addText($Perforation-50, $YPos,$FontSize, _('Page').': ' . $PageNumber);

/*Now print out company info at the top left */

$XPos = $Left_Margin;
//$YPos = $Page_Height - $Top_Margin - 30;

$FontSize = 10;
$LineHeight = 13;
$LineCount = 0;


/*
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize = 8;
$LineHeight = 10;

if ($_SESSION['CompanyRecord']['regoffice1'] <> '') {
  $LineCount += 1;
  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
}
if ($_SESSION['CompanyRecord']['regoffice2'] <> '') {
  $LineCount += 1;
  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
}
if (($_SESSION['CompanyRecord']['regoffice3'] <> '') OR ($_SESSION['CompanyRecord']['regoffice4'] <> '') OR ($_SESSION['CompanyRecord']['regoffice5'] <> '')) { 
  $LineCount += 1;
  $pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);  // country in 6 not printed
}
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$LineCount += 1;
$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);
*/


/*Now the remittance section just company and return postal address */
/*
$FontSize = 8;
$LineHeight = 13;

$LineCountR += 2;
$pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Enviar con Remitente a').':');
$LineCountR += 1;
$pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight, $FontSize, $_SESSION['CompanyRecord']['coyname']);
if ($_SESSION['CompanyRecord']['regoffice1'] <> '') {
  $LineCountR += 1;
  $pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice1']);
}
if ($_SESSION['CompanyRecord']['regoffice2'] <> '') {
  $LineCountR += 1;
  $pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
}
if (($_SESSION['CompanyRecord']['regoffice3'] <> '') OR ($_SESSION['CompanyRecord']['regoffice4'] <> '') OR ($_SESSION['CompanyRecord']['regoffice5'] <> '')) { 
  $LineCountR += 1;
  $pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);  // country in 6 not printed
}

*/
/*Now the customer details and statement address */

$FontSize = 10;
$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin - 40;

$LineCount = 0;
$FontSize = 8;
$sqlperiods="SELECT month(lastdate_in_period)  as mes
	     FROM periods
	     WHERE periodno='".$periodo."'";
$Resultperiods=DB_query($sqlperiods,$db, $ErrMsg);
if ($myrowP=DB_fetch_array($Resultperiods)){
	$mesperiodo = add_ceros($myrowP['mes'],2);
}

$pdf->addText($XPos, $YPos-$LineCount*$LineHeight,$FontSize, _('Periodo').': ' . $periodo. _(' - Mes').': '.$mesperiodo);
$LineCount += 1;
$FontSize = 8;//
if($_SESSION['ConfImprPol'] == 1){
	$sqlgl = "SELECT origtrandategl as fecha,
					www_users.realname
			  FROM gltrans_user
				INNER JOIN www_users ON www_users.userid = gltrans_user.userid
			 WHERE id = '".$counterindex."'";
	$Resultgl=DB_query($sqlgl,$db, $ErrMsg);
	if ($myrowgl=DB_fetch_array($Resultgl)){
		$fechaalta = $myrowgl['fecha'];
		$fechausuario = $myrowgl['realname'];
	}
	
	$sqlgl="SELECT logmodificapolizas.origtrandate,
						www_users.realname
			FROM logmodificapolizas
				INNER JOIN www_users ON www_users.userid = logmodificapolizas.userid
			WHERE  type = '".$type."'
			AND typeno = '".$TransNo."'";
	$fechamod = "";
	$usuamod = "";
	$Resultgl=DB_query($sqlgl,$db, $ErrMsg);
	if ($myrowgl=DB_fetch_array($Resultgl)){
		$fechamod = $myrowgl['fecha'];
		$usuamod = $myrowgl['realname'];
	}
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Fecha Contabilizacion').': ' . $trandate);
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight-10, $FontSize, _('Fecha Alta').': ' . $fechaalta);
	$pdf->addText($Perforation+2, $YPos-$LineCount*$LineHeight-11, $FontSize, _('Usuario Registro').': ' . $fechausuario);
	if($fechamod <> ""){
		$pdf->addText($XPos, $YPos-$LineCount*$LineHeight-20, $FontSize, _('Fecha Modificacion').': ' . $fechamod);
		$pdf->addText($Perforation+2, $YPos-$LineCount*$LineHeight-20, $FontSize, _('Usuario Modifico').': ' . $usuamod);
	}
	
}else{
	$pdf->addText($XPos, $YPos-$LineCount*$LineHeight, $FontSize, _('Fecha ').': ' . $trandate);
}
$LineCountR += 3;
$pdf->addText($Perforation+2, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Poliza no').':' . $TransNo);

$pdf->addText($Perforation+2, $YPos-$LineCount*$LineHeight, $FontSize, _('Tipo Poliza').': ' . $typename);

if(strlen($userid)>0){
	$pdf->addText($Perforation+2, $YPos-$LineCount*($LineHeight+22), $FontSize, _('Usuario').': ' . $userid);
}



/*Now note the customer code in the remittance section too */

//$FontSize=10;
//$LineCountR += 2;
//$pdf->addText($Perforation+1, $YPosR-$LineCountR*$LineHeight,$FontSize, _('Customer Code').': ' . $StmtHeader['debtorno']);

//$FontSize=7;
//$XPos = $Page_Width/2 + 95;
//$pdf->addText($XPos, $YPos,$FontSize, _('Todas las cantidades estan en') . ' - ' . $TransNo);
//$YPos -= $line_height;
//$pdf->addText($XPos, $YPos,$FontSize, _('Terminos de pago') . ": " . $TransNo);

$YPos = $Page_Height - $Top_Margin - 70;
//$YPos -= $line_height;
$XPos = $Left_Margin;


/*draw a nice curved corner box around the statement details */
/*from the top right */
//$pdf->partEllipse($Perforation-10,$YPos-10,0,90,10,10);
/*line to the top left */
#$pdf->line($Perforation-10, $YPos,$Left_Margin+10, $YPos);
/*Do top left corner */
#$pdf->partEllipse($Left_Margin+10, $YPos-10,90,180,10,10);
/*Do a line to the bottom left corner */
$pdf->line($Left_Margin, $YPos-16,$Left_Margin, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
#$pdf->partEllipse($Left_Margin+10, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
//$pdf->line($Left_Margin+10, $Bottom_Margin,$Perforation-10, $Bottom_Margin);
/*Now do the bottom right corner */
#$pdf->partEllipse($Perforation-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
#$pdf->line($Perforation, $Bottom_Margin+10,$Perforation, $YPos-10);


/*draw a nice curved corner box around the remittance advice section */
#$pdf->partEllipse($Page_Width-$Right_Margin-10,$YPos-10,0,90,10,10);
/*line to the top left */
//$pdf->line($Page_Width-$Right_Margin-10, $YPos, $Perforation + 11, $YPos);
/*Do top left corner */
#$pdf->partEllipse($Perforation+11, $YPos-10,90,180,10,10);
/*Do a line to the bottom left corner */
#$pdf->line($Perforation+1, $YPos-10,$Perforation+1, $Bottom_Margin+10);
/*Now do the bottom left corner 180 - 270 coming back west*/
//$pdf->partEllipse($Perforation+11, $Bottom_Margin+10,180,270,10,10);
/*Now a line to the bottom right */
//$pdf->line($Perforation+11, $Bottom_Margin,$Page_Width-$Right_Margin-10, $Bottom_Margin);
/*Now do the bottom right corner */
//$pdf->partEllipse($Page_Width-$Right_Margin-10, $Bottom_Margin+10,270,360,10,10);
/*Finally join up to the top right corner where started */
$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin+10,$Page_Width-$Right_Margin, $YPos-16);




$YPos -= $line_height;
$FontSize =10;
/*Set up headings */
$pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);
$YPos -= $line_height;
$pdf->addText($Left_Margin+1, $YPos,$FontSize, _('Unidad de Negocio') );
$pdf->addText($Left_Margin+160, $YPos,$FontSize, _('Cuenta') );
$pdf->addText($Left_Margin+280, $YPos,$FontSize, _('Cargo') );
$pdf->addText($Left_Margin+330, $YPos,$FontSize, _('Abono') );
$pdf->addText($Left_Margin+380, $YPos,$FontSize, _('Descripcion') );
//$pdf->addText($Left_Margin+285, $YPos,$FontSize, _('Asignado') );
//$pdf->addText($Left_Margin+336, $YPos,$FontSize, _('Pendientes') );


/*Set up remittance section headings */
$FontSize=8;
//$pdf->addText($Perforation+40, $YPos,$FontSize, _('Trans') );
//$pdf->addText($Perforation+70, $YPos,$FontSize, _('Numero') );
//$pdf->addText($Perforation+115, $YPos,$FontSize, _('Pendiente'));


$YPos -= $line_height;
/*draw a line */
$pdf->line($Page_Width-$Right_Margin, $YPos,$XPos, $YPos);

$YPos -= $line_height;
$XPos = $Left_Margin;

?>
