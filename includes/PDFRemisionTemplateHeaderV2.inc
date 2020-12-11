<?php
if (!$FirstPage){ /* only initiate a new page if its not the first */
	//$pdf->newPage();
}
$pdf->SetDrawColor(193,193,192); 
$YPos = $Page_Height - 45;//- $Top_Margin;
$YPosdos=$Top_Margin;
$XPos = $Page_Width/2 - 250;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-25,$YPos-25,200,55);
//$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-25,$YPos+5,0,18);
$FontSize =10;
$pdf->SetFont('helvetica','B',8); 
//$pdf->addText($XPos+460, $YPos+20, $FontSize, _('FACTURA') . ' ');
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
$pdf->addText($XPos, $YPos-80, 6, $foliosat);
$foliox=explode('|',$myrow3['folio']);
$serie=$foliox[0];
$folio=$foliox[1];
$folio=$serie.$folio;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos-45, $YPos-130, 8, $folio);
$pdf->SetFont('helvetica','',9); 
$pdf->addText($XPos-70, $YPos-150, 8, str_replace(' ','T',$myrow3['fechafact']));
$FontSize=12;


		    
//$pdf->addText($XPos-100, $YPos-80, 8, str_replace(' ','T',$Noaprobacion.'/'.$anioAprobacion));

 //$pdf->SetFont('helvetica','',9); 
//$pdf->addText($XPos-110, $YPos-115, 8, str_replace(' ','T',$Certificado));
$YPos=$YPos-138;
$XPos = $Page_Width - 570;
$FontSize=8;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos,610, $FontSize, _('Datos del Cliente'));
$pdf->addText($XPos+185, 610, $FontSize, _('Direccion Embarque'));
$pdf->SetFont('helvetica','',9); 


$YPosx=682;
$XPosx=40;
/*$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPosx, $YPosx,8, _('Cant.'));
$XPosx=$XPosx+35;
$pdf->addText($XPosx, $YPosx,8, _('Codigo'));
$XPosx=$XPosx+60;
$pdf->addText($XPosx, $YPosx,8, _('Descripcion'));
$XPosx=$XPosx+20;	
if ($_SESSION['ShowPriceList']==1){
	$pdf->addText($XPosx, $YPosx,8, _('L.P.'));
}


$XPosx=$XPosx+160;
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


$YPosx=305;
$XPosx=525;
$pdf->addText($XPosx, $YPosx,9, _('Subtotal'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx,9, _('Impuestos'));
$YPosx=$YPosx-40;
$pdf->addText($XPosx, $YPosx,9, _('Total'));
*/

$YPosx=269;
$XPosx=40;
//$pdf->addText($XPosx, $YPosx,9, _('Sello Digital'));
$YPosx=142;
//$pdf->addText($XPosx, $YPosx,9, _('Cadena Original'));
$YPosx=365;
//$pdf->addText($XPosx, $YPosx-150-100,9, _('Cantidad en letras'));
$YPosx=277;
//$pdf->addText($XPosx, $YPosx,9, _('Informacion Comercial'));
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
//$pdf->addText($XPosx, $YPosx-12,7, _('Forma de pago:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx, $YPosx-102,7,$Tipopago.' '.$terminopago);
$pdf->SetFont('helvetica','B',8);
/*$pdf->addText($XPosx+320, $YPosx-12,8, _('Id Docto:'));*/
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+150, $YPosx-12,7, $_GET['TransNo']);
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-24,8, _('Metodo de Pago:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+80, $YPosx-24,7, _('Varios Moneda: '). $myrow3['moneda']._(' TC: ').(1/$myrow3['tipocambio']));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+320, $YPosx-24,8, _('Vendedor:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+370, $YPosx-24,7, $myrow3['vendedor']);
$pdf->SetFont('helvetica','B',8);

if ($_SESSION['ShowLabelText1']==1){
	$pdf->addText($XPosx+230, $YPosx-48,8, $_SESSION['LabelText1']);
}
if ($_SESSION['ShowLabelText3']==1){
	$pdf->addText($XPosx+300, $YPosx-48,8, $_SESSION['LabelText3']);
}
if ($_SESSION['ShowLabelText2']==1){
	$pdf->addText($XPosx+385, $YPosx-48,8, $_SESSION['LabelText2']);
}
$pdf->SetFont('helvetica','',8);
if ($_SESSION['ShowLabelText1']==1){
	$pdf->addText($XPosx+260, $YPosx-48,7, $myrow3['placas']);
}
if ($_SESSION['ShowLabelText3']==1){
	$pdf->addText($XPosx+338, $YPosx-48,7, $myrow3['serie']);
}
if ($_SESSION['ShowLabelText2']==1){
	$pdf->addText($XPosx+435, $YPosx-48,6, $myrow3['kilometraje']);
}
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-63,7, _('Comentarios:'));
$pdf->SetFont('helvetica','',8);
$YPosxC=$YPosx-63;
$LeftOversdc = $pdf->addTextWrap(40,$YPosxC-80,365,6,str_replace('\r\n',' ',$myrow3['comenrenglon']).', Vendedor:'.$myrow3['vendedor'].', Usuario:'.$myrow3['userr'].', Id Docto: '. $_GET['TransNo']);
while (strlen($LeftOversdc) > 1 ) {
	//
	$YPosxC -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap(90,$YPosxC,365,6,$LeftOversdc);
}
				
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-36,8, _('Condiciones:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx, $YPosx-36,7, _('Genera Interes Moratorio 3% mensual/credito'));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+320, $YPosx-36,8, _('Usuario Registro:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+400, $YPosx-36,7, $myrow3['userr']);
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-48,8, _('Cuenta Referenciada:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+100, $YPosx-48,7, $myrow3['bancos']._(' Referencia:') . $myrow3['ref1']);
/*$LeftOversdc = $pdf->addText($XPosx+90, $YPosx-48,100,6, $myrow3['bancos']._(' Referencia:') . $myrow3['ref1']);
while (strlen($LeftOversdc) > 1 ) {
	//
	$YPosxC -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap(90,$YPosxC,365,6,$LeftOversdc);
}*/
$Yposbanco=$YPosx-48;
$refbanco=$myrow3['bancos']._(' Referencia:') . $myrow3['ref1'];
//$LeftOversdc = $pdf->addTextWrap($XPosx+82,$YPosx-48,160,6,str_replace('\r\n',' ',$refbanco));
while (strlen($LeftOversdc) > 1 ) {
	//
	$Yposbanco -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap($XPosx+82,$Yposbanco,360,6,$LeftOversdc);
}
//$LeftOvers=$pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$LeftOvers);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx+10, $YPosx-148,5, _('La tenencia de esta remision no acredita su pago si no se justifica con el comprobante respectivo.'));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+50, $YPosx-235,8, _('ESTE DOCUMENTO ES UNA IMPRESIÓN DE UN COMPROBANTE FISCAL DIGITAL'));
$pdf->SetFont('helvetica','',8);

$YMas = 100;
$FontSize=7;
if (strpos('XAXX010101000',$myrow3['cadena'])==true){
	$pdf->addTextWrap(40,705-$YMas,180,$FontSize,'PUBLICO GENERAL');
	$pdf->addTextWrap(40,700-$YMas,180,$FontSize,'RFC: XAXX010101000');
	
	/*$pdf->addTextWrap(40,650,170,$FontSize,$myrow3['dirfrom1']);
	$pdf->addTextWrap(40,641,117,$FontSize,$myrow3['dirfrom2'] .',');
	$pdf->addTextWrap(40,631,117,$FontSize,$myrow3['dirfrom3']);
	$pdf->addTextWrap(40,621,117,$FontSize,$myrow3['dirfrom4']);
*/

}else{
	$sobranteNombre = $pdf->addTextWrap(40,700-$YMas,180,$FontSize,$myrow3['nombre']);
	$pdf->addTextWrap(40,690-$YMas,180,$FontSize,$sobranteNombre);
	
	$pdf->addTextWrap(40,680-$YMas,527,$FontSize,'RFC:'.$myrow3['RFC']);
	
	$pdf->addTextWrap(40,670-$YMas,170,$FontSize,$myrow3['dir1']);
	$pdf->addTextWrap(40,651-$YMas,170,$FontSize,$myrow3['coloniasuc'] .' , '.$myrow3['dir2']);
	$pdf->addTextWrap(40,641-$YMas,117,$FontSize,$myrow3['dir3']);
	$pdf->addTextWrap(40,631-$YMas,117,$FontSize,$myrow3['dir4'] .' '. 'C.P.' .$myrow3['dir5']);
	
}

$sobranteNombre = $pdf->addTextWrap(220,700-$YMas,180,$FontSize,$myrow3['cliente'].' '.$myrow3['brname']);

$pdf->addTextWrap(220,690-$YMas,180,$FontSize,$sobranteNombre);

$pdf->addTextWrap(220,670-$YMas,170,$FontSize,$myrow3['dirfrom1']);
$pdf->addTextWrap(220,651-$YMas,180,$FontSize,$myrow3['coloniasuc'] .' , '.$myrow3['dirfrom2']);
$pdf->addTextWrap(220,641-$YMas,117,$FontSize,$myrow3['dirfrom3']);
$pdf->addTextWrap(220,631-$YMas,117,$FontSize,'C.P.' .$myrow3['dirfrom4']);


$YPos = $Page_Height - $Top_Margin-95-$YMas-20 ;
//Fin de cajita texto
?>
