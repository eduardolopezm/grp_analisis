<?php
/* $Revision: 1.16 $ */
/*
	R & OS PHP-PDF class code to set up a new page
	a new page is implicit on the establishment of a new pdf object so
	only for subsequent pages
*/
/*MODIFICADO POR: ISRAEL BARRERA NAVARRO*/
/*FECHA DE MODIFICACION 27-ENERO-2011*/
//1.- SE AJUSTARON COMENTARIOS PARA QUE NO SE SALGAN DEL MARGEN

if ($PageNumber>1){
	$pdf->newPage();
}

$YPos = $Page_Height - $Top_Margin - ($line_height*4);


//$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos,0,30);

$FontSize=15;

$XPos = $Page_Width/2 - 120;




$sql = "SELECT *
	FROM supptrans
	WHERE id =" . $OrderNo;
$results=DB_query($sql,$db);
while ($myrows=DB_fetch_array($results)) {
	$supptrans=$myrows['transno'];
	
}

$pdf->addText($XPos,$YPos+$line_height-50,$FontSize, _('Factura de Compra No. '). ' ' . $supptrans);
	



$YPos -= ($line_height*3);
$FontSize=10;
$XPos = $Page_Width-$Right_Margin-50;
$pdf->addText($XPos,$YPos +80, $FontSize, _('Pagina') . ': ' .$PageNumber);

/*Now print out the company name and address */
$XPos = $Left_Margin;
$YPos -= 1*$line_height;

//$pdf->addText($Left_Margin,$YPos, $FontSize, $_SESSION['TaxAuthorityReferenceName'] . ' ' . $_SESSION['CompanyRecord']['gstno']);


		$SQL = "SELECT
			supptrans.supplierno,
			suppliers.taxid,
			suppliers.suppname,
			suppliers.address1,
			suppliers.address2,
			suppliers.address3,
			suppliers.address4,
			supptrans.transtext as comments,
			supptrans.origtrandate as orddate,
			supptrans.rate,
			supptrans.currcode,
			tags.tagdescription,
			legalbusinessunit.logo,
			legalbusinessunit.legalname,
			legalbusinessunit.address1 AS calleempre,
			legalbusinessunit.address2 as coloniaempre,
			legalbusinessunit.address3 as Noextempre,
			legalbusinessunit.address4 as cdempre,
			legalbusinessunit.address5 as edoempre,
			legalbusinessunit.address6 as cpempre,
			legalbusinessunit.telephone as phoneempre,
			legalbusinessunit.fax as faxempre,
			legalbusinessunit.email as emailempre,
			legalbusinessunit.taxid as rfcempre,
			1/supptrans.rate as tcfactura
		FROM supptrans INNER JOIN suppliers
			ON supptrans.supplierno = suppliers.supplierid
			INNER JOIN tags ON supptrans.tagref = tags.tagref
			INNER JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
		WHERE supptrans.id = " . $OrderNo;
		 $POHeade=DB_query($SQL,$db);
		while ($myrowc=DB_fetch_array($POHeade)) {
	$pdf->addJpegFromFile($myrowc['logo'],$Left_Margin,$YPos +30,0,30);
	
$pdf->addText($Left_Margin,$YPos-(1*$line_height)+10, $FontSize, $myrowc['calleempre']);
$pdf->addText($Left_Margin,$YPos-(2*$line_height)+10, $FontSize,$myrowc['coloniaempre']);
$pdf->addText($Left_Margin,$YPos-(3*$line_height)+10, $FontSize, $myrowc['Noextempre']);
$pdf->addText($Left_Margin,$YPos-(4*$line_height)+10, $FontSize,$myrowc['cdempre']. ' ' . $myrowc['edoempre'] . ' ' . $myrowc['cpempre']);
$pdf->addText($Left_Margin,$YPos-(5*$line_height)+10, $FontSize, _('Tel'). ': ' . $myrowc['phoneempre'] . ' ' ._('Fax').': ' . $myrowc['faxempre'] );
$pdf->addText($Left_Margin,$YPos-(6*$line_height)+10, $FontSize, _('Email'). ': ' . $myrowc['emailempre']);
			
/*Now the delivery details */
$YPos -=2*$line_height;
$YPos -= 10;
$XPos = $Page_Width/2 + 25;
$FontSize=9;
$pdf->addText($XPos+2,$YPos +20, $FontSize, _('Unidad Negocio') . ':' );

$pdf->addTextWrap($XPos+2,$YPos-$line_height +20, 290,$FontSize, $myrowc['tagdescription']);

//$pdf->addTextWrap($XPos+2,$YPos-$line_height, 260,$FontSize, $myrowc['deladd1']);
$pdf->addTextWrap($XPos+2,$YPos-(2*$line_height)+20, 260, $FontSize, $myrowc['deladd1']);
$pdf->addTextWrap($XPos+2,$YPos-(3*$line_height)+20, 260, $FontSize, $myrowc['deladd2']);
$pdf->addTextWrap($XPos+2,$YPos-(4*$line_height)+20, 260, $FontSize, $myrowc['deladd3'] . ' ' . $myrowc['deladd4'] . ' ' . $myrowc['deladd5']);
$pdf->addTextWrap($XPos+2,$YPos-(5*$line_height)+20, 260, $FontSize, $myrowc['deladd6']);
/*if ($myrowc['requisitionno']!=''){
	$SQLventa = "SELECT *
		FROM salesorders inner join debtorsmaster on salesorders.debtorno=debtorsmaster.debtorno
		WHERE orderno = " . $myrowc['requisitionno'];
	$POVenta=DB_query($SQLventa,$db);
	if (DB_num_rows($POVenta)==1){
		$PoventaRow = DB_fetch_array($POVenta);
		$pdf->addTextWrap($XPos+2,$YPos-(10*$line_height) +20, 260, $FontSize, 'Pedido Venta: '.$PoventaRow['orderno'] );
		$pdf->addTextWrap($XPos+2,$YPos-(11*$line_height)+20, 260, $FontSize, 'Cliente Venta:'. $PoventaRow['debtorno'].' - '. $PoventaRow['name']);
	}
}
$pdf->partEllipse($XPos+260-10,$YPos+30,0,70,10,10);//curvita sup der

$pdf->line($XPos+263-10, $YPos+40,$XPos+10, $YPos+40);//linea arriba

$pdf->partEllipse($XPos+10, $YPos+30,90,180,10,10);//curvita sup izq
$pdf->line($XPos+10, $YPos-50-10,$XPos+263-10, $YPos-50-10);//linea abajo
$pdf->partEllipse($XPos+10, $YPos-50,180,270,10,10);//curvita inf izquierda
$pdf->line($XPos, $YPos-50,$XPos, $YPos+30);//line izquierda
$pdf->partEllipse($XPos+260-10, $YPos-50,270,360,10,10);//curvita inf derecha

$pdf->line($XPos+260, $YPos-50,$XPos+260, $YPos+30);//linea derecha

 */

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

$YPos-=$line_height;
$pdf->addText($XPos-10,$YPos+$line_height*2, $FontSize, _('Para').': '. $myrowc['supplierno'].'-'.$myrowc['suppname']);
$pdf->addText($XPos+1,$YPos+$line_height, $FontSize,$myrowc['taxid']);
$pdf->addText($XPos+1,$YPos,$FontSize, $myrowc['address1']);
$pdf->addText($XPos+1,$YPos-10, $FontSize, $myrowc['address2']);
$pdf->addText($XPos+1,$YPos-20, $FontSize, $myrowc['address3']);
$pdf->addText($XPos+1+150,$YPos-20, $FontSize, $myrowc['address4']);


$XPos = $Page_Width/2 + 25;
$pdf->addText($XPos,$YPos +20, $FontSize, _('Fecha ') . ':' );
$pdf->addText($XPos+70,$YPos +20, $FontSize, ConvertSQLDate($myrowc['orddate']));


$YPos -= $line_height;
$pdf->addText($XPos,$YPos +20, $FontSize, _('Elaboró').': ');
$pdf->addText($XPos+70,$YPos +20, $FontSize, $myrowc['initiator']);

$YPos -= $line_height;
$pdf->addText($XPos,$YPos +20, $FontSize, _('Comentarios') . ':' );
$YPos -= $line_height-12;
$LeftOvers = $pdf->addTextWrap($XPos+60, $YPos +20,200,$FontSize,$myrowc['comments'], 'left');

while (strlen($LeftOvers)>0){
	$LeftOvers = $pdf->addTextWrap($XPos, $YPos-$line_height,250,$FontSize,$LeftOvers, 'left');
	$YPos-=$line_height;
}

$XPos = $Left_Margin;
$YPos -= ($line_height*2);
$pdf->addText($Left_Margin,$YPos+20, $FontSize, _('Los montos son expresados en ').'  ' . $myrowc['currcode'] ._(' Tipo Cambio Factura: ').number_format($myrowc['tcfactura'],4));

		}
$YPos -= ($line_height*.5);
/*draw a square grid for entering line items */
$pdf->line($Left_Margin, $YPos+20, $Page_Width-$Right_Margin+10, $YPos+20);//linea arriba cuadro
$pdf->line($Page_Width-$Right_Margin+10, $YPos+20, $Page_Width-$Right_Margin+10, $Bottom_Margin-15);//linea derecha cuadro
$pdf->line($Page_Width-$Right_Margin+10, $Bottom_Margin-15, $Left_Margin, $Bottom_Margin-15);//linea abajo cuadro
$pdf->line($Left_Margin, $Bottom_Margin-15, $Left_Margin, $YPos+20);//linea izquierda cuadro

$YPos -= $line_height-20;
/*Set up headings */
$FontSize=9;
$pdf->addText($Left_Margin+1,$YPos, $FontSize, _('Codigo') );
$pdf->addText($Left_Margin+95,$YPos, $FontSize, _('Descripcion') );
$pdf->addText($Left_Margin+220,$YPos, $FontSize, _('Cantidad') );
$pdf->addText($Left_Margin+263,$YPos, $FontSize, _('Unid.') );
$pdf->addText($Left_Margin+295,$YPos, $FontSize, _('Fecha Req.'));
$pdf->addText($Left_Margin+370,$YPos, $FontSize, _('Precio') );
$pdf->addText($Left_Margin+425,$YPos, $FontSize, _('O.C') );
$pdf->addText($Left_Margin+460,$YPos, $FontSize, _('Recep') );
$pdf->addText($Left_Margin+495,$YPos, $FontSize, _('T.C.R.') );
//$pdf->addText($Left_Margin+450,$YPos, $FontSize, _('Des2') );
//$pdf->addText($Left_Margin+480,$YPos, $FontSize, _('Des3') );
$pdf->addText($Left_Margin+530,$YPos, $FontSize, _('Total') );
$YPos-=$line_height;

/*draw a line */
$pdf->line($Left_Margin, $YPos+10, $Page_Width-$Right_Margin+10, $YPos+10);
//linea enmedio cuadro
		

?>