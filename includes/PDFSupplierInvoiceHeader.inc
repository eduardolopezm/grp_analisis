<?php
/* $Revision: 1.16 $ */
/*
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - ($line_height*4)+10;


$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,30);

$FontSize=15;

$XPos = $Page_Width/2 - 100;




if ($OrderStatus == 'Printed') {
	$pdf->addText($XPos,$YPos+$line_height-80,$FontSize, _('Orden de Compra No. '). ' ' . $OrderNo);
	
} elseif ($OrderStatus == 'Pending') {
	$pdf->addText($XPos + 80,$YPos+$line_height-80,$FontSize, _('O.C. PENDIENTE DE AUTORIZAR No. '). ' ' . $OrderNo);
	
	$FontSize=16;
	$pdf->addText($XPos,$YPos,$FontSize, _('SOLO PARA REVISION') . ', ' . _('NO ENVIAR AL PROVEEDOR') );
	$pdf->addText($XPos,$YPos-$line_height,$FontSize, _('PROVEEDOR') . ' - ' . _('ESTO NO ES UNA ORDEN') );
	
} elseif ($OrderStatus == 'Cancelled') {
	$pdf->addText($XPos + 80,$YPos+$line_height-80,$FontSize, _('O.C. CANCELADA No. '). ' ' . $OrderNo);
	
	$FontSize=16;
	$pdf->addText($XPos,$YPos,$FontSize, _('SOLO PARA REVISION') . ', ' . _('NO ENVIAR AL PROVEEDOR') );
	$pdf->addText($XPos,$YPos-$line_height,$FontSize, _('PROVEEDOR') . ' - ' . _('ESTO NO ES UNA ORDEN VALIDA') );
} elseif ($OrderStatus == 'Authorised') {
	$pdf->addText($XPos + 80,$YPos+$line_height-80,$FontSize, _('Orden de Compra AUTORIZADA No. '). ' ' . $OrderNo);
	
} elseif ($OrderStatus == 'Rejected') {
	$pdf->addText($XPos + 80,$YPos+$line_height-80,$FontSize, _('O.C. RECHAZADA No. '). ' ' . $OrderNo);
	
	$FontSize=16;
	$pdf->addText($XPos,$YPos,$FontSize, _('SOLO PARA REVISION') . ', ' . _('NO ENVIAR AL PROVEEDOR') );
	$pdf->addText($XPos,$YPos-$line_height,$FontSize, _('PROVEEDOR') . ' - ' . _('ESTO NO ES UNA ORDEN') );
} else {
	$pdf->addText($XPos,$YPos+$line_height-80,$FontSize, _('Factura de Proveedor No. '). ' ' . $OrderNo . ' '.$OrderStatus);
			
}

$YPos -= ($line_height*3);
$FontSize=10;
$XPos = $Page_Width-$Right_Margin-50;
$pdf->addText($XPos,$YPos +80, $FontSize, _('Pagina') . ': ' .$PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= 1*$line_height;

//$pdf->addText($Left_Margin,$YPos, $FontSize, $_SESSION['TaxAuthorityReferenceName'] . ' ' . $_SESSION['CompanyRecord']['gstno']);

$pdf->addText($Left_Margin,$YPos-(1*$line_height), $FontSize, $_SESSION['CompanyRecord']['coyname']);
$pdf->addText($Left_Margin,$YPos-(2*$line_height), $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$pdf->addText($Left_Margin,$YPos-(3*$line_height), $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$pdf->addText($Left_Margin,$YPos-(4*$line_height), $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$pdf->addText($Left_Margin,$YPos-(5*$line_height), $FontSize, _('Ph'). ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' ._('Fax').': ' . $_SESSION['CompanyRecord']['fax'] );
$pdf->addText($Left_Margin,$YPos-(6*$line_height), $FontSize, _('Email'). ': ' . $_SESSION['CompanyRecord']['email']);

		 $SQL = "SELECT supptransdetails.stockid,
			supptransdetails.description,
			supptransdetails.orderno,
			supptransdetails.qty,
			supptransdetails.grns,
			supptransdetails.price,
			suppliers.taxid,
			suppliers.supplierid,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			suppliers.address5,
			suppliers.address6,
		        supptrans.currcode,
		        suppliers.suppname,
		        suppliers.supplierid as supplierno,
			DATE_FORMAT(supptrans.origtrandate,'%Y-%m-%d') as fecha,
			tags.tagdescription,
			ifnull(supptrans.currcode,suppliers.currcode) as currcode
		FROM supptransdetails INNER JOIN supptrans ON supptransdetails.supptransid=supptrans.id
			INNER JOIN suppliers ON suppliers.supplierid=supptrans.supplierno 
		INNER JOIN tags ON tags.tagref=supptrans.tagref
		WHERE supptrans.suppreference='" . $OrderNo."'
			AND supptrans.transno='" . $_GET['Transno']."'
			AND supptrans.type='" . $_GET['Type']."'";
		 $POHeade=DB_query($SQL,$db);
		//while ($myrowc=DB_fetch_array($POHeade)) {
		$myrowc=DB_fetch_array($POHeade);
/*Now the delivery details */
$YPos -=2*$line_height;
$YPos -= 10;
$XPos = $Page_Width/2 + 25;
$FontSize=9;
$pdf->addText($XPos+2,$YPos, $FontSize, _('Entregar en') . ':' );
$pdf->addTextWrap($XPos+2,$YPos-$line_height, 290,$FontSize, $myrowc['tagdescription']);

//$pdf->addTextWrap($XPos+2,$YPos-$line_height, 260,$FontSize, $myrowc['deladd1']);
//$pdf->addTextWrap($XPos+2,$YPos-(2*$line_height), 260, $FontSize, $myrowc['deladd1']);
//$pdf->addTextWrap($XPos+2,$YPos-(3*$line_height), 260, $FontSize, $myrowc['deladd2']);
//$pdf->addTextWrap($XPos+2,$YPos-(4*$line_height), 260, $FontSize, $myrowc['deladd3'] . ' ' . $myrowc['deladd4'] . ' ' . $myrowc['deladd5']);
//$pdf->addTextWrap($XPos+2,$YPos-(5*$line_height), 260, $FontSize, $myrowc['deladd6']);
//$YPos-=$line_height;
$pdf->addTextWrap($XPos+2,$YPos-$line_height-15, 260,$FontSize, _('Proveedor').': '. $myrowc['supplierno'].'-'.$myrowc['suppname']);
$pdf->addTextWrap($XPos+2,$YPos-(2*$line_height)-15, 260,$FontSize, $myrowc['address1']);
$pdf->addTextWrap($XPos+2,$YPos-(3*$line_height)-15, 260,$FontSize, $myrowc['address2']);
$pdf->addTextWrap($XPos+2,$YPos-(4*$line_height)-15, 260,$FontSize, $myrowc['address3'].' ' . $myrowc['address4'] . ' ' . $myrowc['address5']);
$pdf->addTextWrap($XPos+2,$YPos-(5*$line_height)-15, 260,$FontSize, $myrowc['address6']);
/*$pdf->addText($XPos+2,$YPos-$line_height, $FontSize, _('Proveedor').': '. $myrowc['supplierno'].'-'.$myrowc['suppname']);
$pdf->addText($XPos+1,$YPos+$line_height, $FontSize,$myrowc['taxid']);
$pdf->addText($XPos+1,$YPos,$FontSize, $myrowc['address1']);
$pdf->addText($XPos+1,$YPos-(2*$line_height), $FontSize, $myrowc['address2']);
$pdf->addText($XPos+1,$YPos-(3*$line_height), $FontSize, $myrowc['address3']);
$pdf->addText($XPos+1+150,$YPos-(3*$line_height), $FontSize, $myrowc['address4']);
*/
//$YPos-=$line_height;

/*draw a nice curved corner box around the delivery to address */
/*from the top right */
$pdf->partEllipse($XPos+260-10,$YPos+10,0,70,10,10);//curvita sup der
/*line to the top left */
$pdf->line($XPos+263-10, $YPos+20,$XPos+10, $YPos+20);//linea arriba
/*Do top left corner */
$pdf->partEllipse($XPos+10, $YPos+10,90,180,10,10);//curvita sup izq
/*Do a line to the bottom left corner */
$pdf->line($XPos+10, $YPos-70-10,$XPos+263-10, $YPos-70-10);//linea abajo
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos+10, $YPos-70,180,270,10,10);//curvita inf izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-70,$XPos, $YPos+10);//line izquierda
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+260-10, $YPos-70,270,360,10,10);//curvita inf derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+260, $YPos-70,$XPos+260, $YPos+10);//linea derecha



/*end of the small table showing the coy name and delivery address details */

$XPos = $Left_Margin;
$YPos -= ($line_height*4.5);         /* 4 lines down */

/*
PDF_moveto($p, $XPos+230, $YPos-100);
PDF_arc($p, $XPos+220, $YPos, 10, 0, 90);
PDF_arc($p, $XPos+5, $YPos, 10, 90, 180);
PDF_arc($p, $XPos+5, $YPos-100, 10, 180, 270);
PDF_arc($p, $XPos+220, $YPos-100, 10, 270, 360);
PDF_stroke($p);

$font=PDF_findfont($p,"Helvetica-Bold","host",0);
PDF_setfont($p, $font, 12.0);
*/

$YPos -= 20;



$XPos = $Page_Width/2 + 25;
$pdf->addText($XPos,$YPos-20, $FontSize, _('Fecha ') . ':' );
$pdf->addText($XPos+70,$YPos-20, $FontSize, ConvertSQLDate($myrowc['fecha']));


$YPos -= $line_height;
//$pdf->addText($XPos,$YPos, $FontSize, _('Elaboró').': ');
//$pdf->addText($XPos+70,$YPos, $FontSize, $myrowc['initiator']);

$YPos -= $line_height;
//$pdf->addText($XPos,$YPos, $FontSize, _('Comentarios') . ':' );
//$YPos -= $line_height;
//$LeftOvers = $pdf->addTextWrap($XPos, $YPos,250,$FontSize,$myrowc['comments'], 'left');

while (strlen($LeftOvers)>0){
	$LeftOvers = $pdf->addTextWrap($XPos, $YPos-$line_height,250,$FontSize,$LeftOvers, 'left');
	$YPos-=$line_height;
}

$XPos = $Left_Margin;
$YPos -= ($line_height*2);
$pdf->addText($Left_Margin,$YPos+20, $FontSize, _('Los montos son expresados en ').' - ' . $myrowc['currcode']);

		//}
$YPos -= ($line_height*.5);
/*draw a square grid for entering line items */
$pdf->line($Left_Margin, $YPos+20, $Page_Width-$Right_Margin+10, $YPos+20);//linea arriba cuadro
$pdf->line($Page_Width-$Right_Margin+10, $YPos+20, $Page_Width-$Right_Margin+10, $Bottom_Margin-10);//linea derecha cuadro
$pdf->line($Page_Width-$Right_Margin+10, $Bottom_Margin-10, $Left_Margin, $Bottom_Margin-10);//linea abajo cuadro
$pdf->line($Left_Margin, $Bottom_Margin-10, $Left_Margin, $YPos+20);//linea izquierda cuadro

$YPos -= $line_height-20;
/*Set up headings */
$FontSize=9;
$pdf->addText($Left_Margin+1,$YPos, $FontSize, _('Codigo') );
$pdf->addText($Left_Margin+95,$YPos, $FontSize, _('Descripcion') );
$pdf->addText($Left_Margin+260,$YPos, $FontSize, _('Cantidad') );
//$pdf->addText($Left_Margin+263,$YPos, $FontSize, _('Unid.') );
//$pdf->addText($Left_Margin+295,$YPos, $FontSize, _('Fecha '));
$pdf->addText($Left_Margin+370,$YPos, $FontSize, _('Precio') );
$pdf->addText($Left_Margin+420,$YPos, $FontSize, _('Orden') );
$pdf->addText($Left_Margin+450,$YPos, $FontSize, _('Recepcion') );
//$pdf->addText($Left_Margin+480,$YPos, $FontSize, _('Des3') );
$pdf->addText($Left_Margin+530,$YPos, $FontSize, _('Total') );
$YPos-=$line_height;

/*draw a line */
$pdf->line($Left_Margin, $YPos+10, $Page_Width-$Right_Margin+10, $YPos+10);
//linea enmedio cuadro
		

?>