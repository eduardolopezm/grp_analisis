<?php
if ($FirstPage>1){ /* only initiate a new page if its not the first */
	$pdf->newPage();
}
if($myrow3['printedpackingslip']==1 and $Copy=='Original' ){
	if ($permisocadena==0){
		$Copy='Copia No Negociable';
	}
}
if ($Copy=='Original'){
	$pdf->addText(540, 42,8, $Copy);
}else{
	$pdf->SetTextColor(230,230,230); 
	//$pdf->addText($XPosx+500, $YPosx-235,12, $Copy);
	//$pdf->RotatedText(30,190,$Copy,45);
	$pdf->Rotate(45,0,250);
	//$pdf->rotate(90);
	//$this->Text($x,$y,$txt); 

	$pdf->addText(0, 250,48, $Copy);
	$pdf->Rotate(0); 
	$pdf->SetTextColor(0); 
}


$pdf->SetDrawColor(193,193,192); 
$YPos = $Page_Height - 45;//- $Top_Margin;
$YPosdos=$Top_Margin;
$XPos = $Page_Width/2 - 250;
//$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-25,$YPos-25,100,55);
//echo 'imagen'.$myrow3['logo'];
//exit;//
$pdf->addJpegFromFile($myrow3['logo'],$XPos-25,$YPos-20,0,47);
//$pdf->Image( $myrow3['logo'] ,$XPos-25,$YPos-20,0,47);
//$pdf->addJpegFromFile($myrow3['cedula'],$XPos-25,$YPos-15,0,35);
//$pdf->addText($XPos-10, $YPos-200, $FontSize,  $dir.'/'.$myrow3['logo']. ' ');
$FontSize =8;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos+472, $YPos+20, $FontSize, _('FACTURA') . ' ');
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
$pdf->addText($XPos-30, $YPos+5, $FontSize, 'ERP:'.$_GET['TransNo']);
$pdf->addText($XPos, $YPos+10, $FontSize, $foliosat);
$foliox=explode('|',$myrow3['folio']);
$serie=$foliox[0];
$folio=$foliox[1];
$folioX=$foliox[1];
$aprobaxfolio=TraeAprobacionxFolio($myrow3['rfcempre'],$serie,$folio,$db);
$aprobacionfolios=explode('|',$aprobaxfolio);
$Certificado=$aprobacionfolios[0];
$Noaprobacion=$aprobacionfolios[1];
$anioAprobacion=$aprobacionfolios[2];


$folio=$serie.$folio;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos-58, $YPos+15, 10, $folio);
$pdf->SetFont('helvetica','',9); 
$pdf->addText($XPos-71, $YPos-5, 8, str_replace(' ','T',$myrow3['fechafact']));
$pdf->addText($XPos-45, $YPos-15, 8, 'Pagina: '.$FirstPage.' de ' .$PageNumber);
$FontSize=12;


		    
$pdf->addText($XPos-100, $YPos-80, 8, str_replace(' ','T',$Noaprobacion.'/'.$anioAprobacion));

 //$pdf->SetFont('helvetica','',9); 
$pdf->addText($XPos-120, $YPos-115, 8, str_replace(' ','T',$Certificado));
$YPos=$YPos-58;
$XPos = $Page_Width - 570;
$FontSize=8;
$pdf->SetFont('helvetica','B',9); 
$pdf->addText($XPos, $YPos+13, $FontSize, _('Datos del Cliente'));
$pdf->addText($XPos+185, $YPos+13, $FontSize, _('Datos de Embarque'));
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


$pdf->addText($XPos+30, $YPos, 7, $myrow3['legalname']);


$pdf->SetFont('helvetica','',8); 
$FontSize=8;
$YPos=$YPos-10;
$pdf->addText($XPos+30, $YPos, 7, 'RFC' . ': ' . $myrow3['rfcempre']);
$YPos=$YPos-10;
$pdf->addText($XPos+30, $YPos,6,$myrow3['calleempre']);
$YPos=$YPos-10;
$pdf->addText($XPos+30, $YPos,6, $myrow3['coloniaempre']);
$YPos=$YPos-10;
$pdf->addText($XPos+30, $YPos,6, $myrow3['Noextempre']. ' ' . $myrow3['cdempre']);
//$YPos=$YPos-10;
//$pdf->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$YPos=$YPos-10;
$pdf->addText($XPos+30, $YPos, 6, _('Tel') . ':' . $myrow3['phoneempre'] . ' ' . _('Fax') . ': ' . $myrow3['faxempre']);
$YPos=$YPos-10;
$pdf->addText($XPos+30, $YPos, 6, _('Email') . ': ' . $myrow3['emailempre']);
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


//**********
//Inicio de cajita de datos de cliente custbranch
//**********
$XPos = $Page_Width - 385;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+180,$YPos+170,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+180, $YPos+180,$XPos, $YPos+180);//linea de arriba de la cajita
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+170,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+110,$XPos-10, $YPos+170);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+110,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+100,$XPos+179, $YPos+100);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+180, $YPos+110,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+190, $YPos+110,$XPos+190, $YPos+170);//linea derecha de la cajita
/*************************************************************************/



/*************************************************************************/
//**********
//Inicio de cajita de datos de cliente fecha y num certificado
//**********

$XPos = $Page_Width - 180;
$YPos = 515;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+95,$YPos+170,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
//$pdf->setColor(0,0,0);

$pdf->line($XPos+95, $YPos+180,$XPos, $YPos+180);//linea de arriba de la cajita
$pdf->line($XPos+105, $YPos+162,$XPos-10, $YPos+162);//linea de aprobacion
$pdf->line($XPos+105, $YPos+142,$XPos-10, $YPos+142);//linea de aprobacion
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+170,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos+110,$XPos-10, $YPos+170);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos+110,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos+100,$XPos+95, $YPos+100);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+95, $YPos+110,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+105, $YPos+110,$XPos+105, $YPos+170);//linea derecha de la cajita
/*************************************************************************/



/*********************************/
$YPos = $Page_Height - $Top_Margin + 5;
$FontSize = 10;
$XPos = $Page_Width - 300;
//$YPos=$YPos-10;
$XPos = $Page_Width/2 ;
$FontSize=8;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPos+50, $YPos, $FontSize, _('Expedido En: '));
$pdf->SetFont('helvetica','',8); 
$FontSize=6;
$pdf->addText($XPos+110, $YPos,8, $myrow3['unidadnegocio']);
$YPos=$YPos-10;


$pdf->addText($XPos+50, $YPos,$FontSize, $myrow3['calleexpe'].' '.$myrow3['Noextexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos+50, $YPos,$FontSize, $myrow3['coloniaexpe']);
$YPos=$YPos-10;
$pdf->addText($XPos+50, $YPos,$FontSize, $myrow3['cdexpe'] . ' ' . $myrow3['edoexpe'] );
$YPos=$YPos-10;
$pdf->addText($XPos+50, $YPos,$FontSize,'Tel.'.$myrow3['telexpe'] );
$YPos=$YPos-10;
$pdf->addText($XPos+50, $YPos,$FontSize,'Email: '.$myrow3['tagemail'] );
$YPosx=680;
$XPosx=410;
$pdf->SetFont('helvetica','B',8); 
$pdf->addText($XPosx+15, $YPosx,8, _('Número/Año de Aprobacion'));
$YPosx=650;
$XPosx=410;
$pdf->addText($XPosx+30, $YPosx-10,8, _('No. Certificado Digital'));//
if($myrow3['cedula'] <> ""){
	$pdf->addJpegFromFile($myrow3['cedula'],$XPosx+130,$YPosx-32,0,90);
}

$YPosx=602;
$XPosx=40;

$pdf->addText($XPosx, $YPosx,8, _('Cant.'));
$XPosx=$XPosx+35;
$pdf->addText($XPosx, $YPosx,8, _('Codigo'));
$XPosx=$XPosx+60;
$pdf->addText($XPosx, $YPosx,8, _('Descripcion'));
$XPosx=$XPosx+20;	
if ($_SESSION['ShowPriceList']==1){
	$pdf->addText($XPosx, $YPosx,8, _('L.P.'));
}


$XPosx=$XPosx+200;
$pdf->addText($XPosx, $YPosx,8, _('Precio'));

if ($_SESSION['ShowWorkers']==1){	
	$pdf->addText($XPosx-55, $YPosx,8, _('Trab'));
}

$XPosx=$XPosx+50;
$pdf->addText($XPosx, $YPosx,8, _('Desc. 1'));
$XPosx=$XPosx+40;

//if ($_SESSION['Showdiscount1']==1){	
	$pdf->addText($XPosx, $YPosx,8, _('Desc. 2'));
//}
$XPosx=$XPosx+40;
//if ($_SESSION['Showdiscount2']==1){
	
	$pdf->addText($XPosx, $YPosx,8, _('Desc. 3'));
//}
$XPosx=$XPosx+50;
//$pdf->addText($XPosx, $YPosx,8, _('SubTotal'));
$XPosx=$XPosx+20;
$pdf->addText($XPosx, $YPosx,8, _('Total'));


$YPosx=305;
$XPosx=505;//
$pdf->addText($XPosx, $YPosx,9, _('Total Venta'));
$YPosx=$YPosx-30;

$pdf->addText($XPosx, $YPosx-20,7.5, _('Impuesto IVA '));	
$pdf->addText($XPosx, $YPosx-30,6, number_format($myrow3['percentiva'],2)."%");		

$YPosx=$YPosx-20;

if($myrow3['ivaret']>0){
	$pdf->addText($XPosx, $YPosx,9, _('SubTotal'));
}
$YPosx=$YPosx-20;
if($myrow3['ivaret']>0){
	$pdf->addText($XPosx, $YPosx,7.5, _('IVA Retenido').number_format($myrow3['percentiva'],2)."%");
}
$YPosx=$YPosx-20;
$pdf->addText($XPosx, $YPosx,9, _('Total'));

$YPosx=179;
$XPosx=40;
$pdf->addText($XPosx, $YPosx,9, _('Sello Digital'));
$YPosx=142;
$pdf->addText($XPosx, $YPosx,9, _('Cadena Original'));
$YPosx=305;
$pdf->addText($XPosx, $YPosx,9, _('Cantidad en letras'));
$YPosx=277;
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
if($myrow3['descuentopago']>0){
	$descuento=" con descuento de pronto pago de ".$myrow3['descuentopago']*100 ." %";
}else{
	$descuento="";
}
$infoextra="";
		$numpago=0;
		$cadenapagares="";
		if ($_GET['Type']!=110){
		$SQL=" SELECT trandate,ovamount+ovgst as monto
		       FROM  debtortrans
		       WHERE debtortrans.type=70
			AND debtortrans.order_=" . $_GET['TransNo'] ;
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)>0) {
			while ($myrowpagos=DB_fetch_array($Result)){
				if ($numpago==0){
					$fechapagoinicial=ConvertSQLDate($myrowpagos['trandate']);
				}
				$numpago=$numpago+1;
				$fechapago=ConvertSQLDate($myrowpagos['trandate']);
				$montopago=$myrowpagos['monto'];
				$montopago=number_format($montopago,2,'.','');
				$cadenapagares=$cadenapagares." No:".$numpago." Vence: ".$fechapago." $".$montopago .' '.$moneda;
			}
		}else{
			$fechapagoinicial=ConvertSQLDate($myrow3['trandatefact']);
		}
		}else{
			$fechapagoinicial=ConvertSQLDate($myrow3['trandatefact']);
		}
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx, $YPosx-12,7, _('La tenencia de esta factura no acredita su pago si no se justifica con el comprobante respectivo.'));
$YPosx=$YPosx-10;
$textotenencia=_('La tenencia de esta factura no acredita su pago si no se justifica con el comprobante respectivo.     ').'Lista de Precio: '.$myrow3['sales_type'];
$LeftOvers = $pdf->addTextWrap($XPosx, $YPosx,400,6,$textotenencia);
while (strlen($LeftOvers) > 1 ) {
	$YPosx -= 5;
	$LeftOvers = $pdf->addTextWrap($XPosx, $YPosx,170,6,$LeftOvers);
}

$YPosx=$YPosx-10;
$pdf->addText($XPosx, $YPosx,8, _('Forma de pago:'));
$pdf->SetFont('helvetica','',8);
$YPosx=$YPosx+10;
$YPosd=$YPosx;
//$pdf->addText($XPosx+70, $YPosx-12,6,$Tipopago.' '.$terminopago.' '.$cadenapagares);
$datos=$Tipopago.' '.$terminopago.' '.$cadenapagares;
$LeftOvers = $pdf->addTextWrap($XPosx+61, $YPosx-12,170,6,$datos);
while (strlen($LeftOvers) > 1 ) {
	$YPosx -= 20;
	$LeftOvers = $pdf->addTextWrap($XPosx, $YPosx,170,6,$LeftOvers);
}
$datos="";
$YPosx -= 30;
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+290, $YPosx-12,8, _('Id Docto:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+340, $YPosx-12,7, $_GET['TransNo']);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx,8, _('Metodo de Pago:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+65, $YPosx,7, _('Varios Moneda: '). $myrow3['moneda']._(' TC: ').(1/$myrow3['tipocambio']));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+290, $YPosx-24,8, _('Vendedor:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+330, $YPosx-24,7, $myrow3['vendedor']);
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
//$pdf->addText($XPosx, $YPosx+60,7, _('Comentarios:'));
$pdf->SetFont('helvetica','',8);
$SQL=" SELECT *
       FROM  debtortransmovs
       WHERE debtortransmovs.type=" . $_GET['Type']."
	AND debtortransmovs.transno=" . $_GET['TransNo'] ;
$Resultregister= DB_query($SQL,$db);
$myrowregister=DB_fetch_array($Resultregister);
$usuarioreg=$myrowregister['userid'];

$YPosxC=$YPosx+130;
$comentrenglon='';
if ($myrow3['showcomments']==1){
	$comentrenglon=str_replace('\r\n',' ',$myrow3['comenrenglon']).' . ';
}
if ( $myrow3['showvehicle']==1){
	$comentrenglon=$comentrenglon.$_SESSION['LabelText1'].' : '.$myrow3['placas'].'; '.$_SESSION['LabelText3'].' : '.$myrow3['serie'].';';
	$comentrenglon=$comentrenglon.' Num. Eco: '.$myrow3['numeco'].'; '.$_SESSION['LabelText2'].' : '.$myrow3['kilometraje'].'; Marca:'.$myrow3['mark'].'; Modelo:'.$myrow3['model'].'; Color:'.$myrow3['color'].'; Año:'.$myrow3['yearvehicle'];
}

$comentrenglon=$comentrenglon.'; Orden venta: '.$_GET['OrderNo'].'; Usuario Pedido:'.$myrow3['userr'].' Vendedor:'.$myrow3['vendedor'];
$comentrenglon=$comentrenglon.'; Usuario Factura:'.$usuarioreg;
$LeftOversdc = $pdf->addTextWrap(90,$YPosxC,365,8,$comentrenglon);
while (strlen($LeftOversdc) > 1 ) {
	//
	$YPosxC -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap(90,$YPosxC,365,8,$LeftOversdc);
}
$YPosx=	$YPosd;
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx-36,8, _('Condiciones:'));
$pdf->SetFont('helvetica','',8);
$pdf->addText($XPosx+60, $YPosx-36,7, _('Genera Interes Moratorio 3% mensual/credito'));
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+290, $YPosx-36,8, _('Usuario Registro:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+370, $YPosx-36,7, $myrow3['userr']);


$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx, $YPosx-61,7, _('Cuenta Referenciada:'));
$pdf->SetFont('helvetica','',8);
//$pdf->addText($XPosx+100, $YPosx-48,7, $myrow3['bancos']._(' Referencia:') . $myrow3['ref1']);
/*$LeftOversdc = $pdf->addText($XPosx+90, $YPosx-48,100,6, $myrow3['bancos']._(' Referencia:') . $myrow3['ref1']);
while (strlen($LeftOversdc) > 1 ) {
	//
	$YPosxC -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap(90,$YPosxC,365,6,$LeftOversdc);
}*/
$refbanco="";
$nobanco=0;
$sqlr="SELECT distinct banco,referenciasbancarias.referencia
	FROM referenciasbancarias
	INNER JOIN bancosreferencia ON referenciasbancarias.bancoid=bancosreferencia.bancoid
	WHERE transid='".$myrow3['iddocto']."' and bancosreferencia.active=1";
$resultbanco=DB_query($sqlr,$db, $ErrMsg);
while ($myrowbancos=DB_fetch_array($resultbanco)){
	if ($nobanco==0){
		$refbanco='Banco:'.$myrowbancos['banco'].', Ref: '. $myrowbancos['referencia'];
	}else{
		$refbanco=$refbanco.'; Banco:'.$myrowbancos['banco'].', Ref: '. $myrowbancos['referencia'];
	}
	$nobanco=$nobanco+1;
	
}
$SQL=" SELECT * FROM bankinvoice
where  active=1 and tagref=" . $myrow3['tagbank'];
$Resultregister= DB_query($SQL,$db);
$myrowregister=DB_fetch_array($Resultregister);
$refbanco=$myrowregister['bankname'];
if (strlen($refbanco)==0){
	$refbanco=$myrow3['referenciaempre'];	
}else{
	
	$refbanco='Favor de realizar sus depositos a nombre de: '.$myrow3['legalname'].' '.$refbanco.' Cuenta:'.$myrowregister['accountbank'].' CLABE:'.$myrowregister['labelbank'];	
}

$Yposbanco=$YPosx-61;

$LeftOversdc = $pdf->addTextWrap($XPosx+78,$YPosx-61,180,6,str_replace('\r\n',' ',$refbanco));
while (strlen($LeftOversdc) > 1 ) {
	//
	$Yposbanco -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap($XPosx+20,$Yposbanco,255,6,$LeftOversdc);
}
/*monto de letras*/
//aqui empieza la consulta para sacar el subtotal y el total
$totaletras=abs($myrow3['montofact']);				
$separa=explode(".",$totaletras);
$montoctvs2 = $separa[1];
$montoctvs1 = $separa[0];
//$montoctvs1x= $separa[1];
if (left($montoctvs2,3)>=995){
$montoctvs1=$montoctvs1+1;
//$montoctvs1x='entra';
}

if ($monedacliente=='USD' and $myrow2['currcode']='USD'){
$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
}else{
$montoletra=Numbers_Words::toWords($montoctvs1,'es');
}
$totaletras=number_format($totaletras,2);
$separa=explode(".",$totaletras);
$montoctvs2 = $separa[1];
if (left($montoctvs2,3)>956){
$montoctvs2=0;
}
$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
if ($myrow3['moneda']=='MXN'){
$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
}
else
{
if($monedacliente=='USD'){
$montoletra=ucwords($montoletra) . " Dollars ". $montoctvs2 ."/100 USD";
}else{

$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
}
}





$textofactura=_('Debo(emos) y pagaré(emos) incondicionalmente en la Ciudad de '.$myrow3['cdexpe'] . ', ' . $myrow3['edoexpe'] .' en la fecha '.$fechapagoinicial.' 
		a la orden de '.$myrow3['legalname'] .'
					por la cantidad '.number_format($myrow3['montofact'],2) .' '.$myrow3['currcode'].' ('.$montoletra.'), valor de la mercancía que recibi(mos) a mi(nuestra) entera satisfacción.
					Pagaré sustentado por Cap. III de la Ley General de Títulos y Operaciones de Crédito.
					
					
					
					
					');
//$LeftOvers=$pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$LeftOvers);
$pdf->SetFont('helvetica','B',8);
//$pdf->addText($XPosx+10, $YPosx-68,5, );
$Yposbanco=$YPosx-2;
$Yposbanco -= 1*$line_height*0.40;
$LeftOversdc = $pdf->addTextWrap($XPosx+250,$YPosx-10,210,6,str_replace('\r\n',' ',$textofactura));
while (strlen($LeftOversdc) > 1 ) {
	//
	$Yposbanco -= 1*$line_height*0.40;
	//
	$LeftOversdc = $pdf->addTextWrap($XPosx+250,$Yposbanco,210,6,$LeftOversdc);
}
$Yposbanco -= 1.4*$line_height*0.40;
$pdf->line($XPosx+250, $Yposbanco,$XPos+180, $Yposbanco);//linea derecha de la cajita
$Yposbanco -= 1*$line_height*0.40;
$LeftOversdc = $pdf->addTextWrap($XPosx+280,$Yposbanco,210,6,'Firma de Conformidad del Cliente');

//$LeftOvers=$pdf->addTextWrap($xpos+40,$YPos,520,$FontSize,$LeftOvers);
$pdf->SetFont('helvetica','B',8);
$pdf->addText($XPosx+50, $YPosx-235,8, _('ESTE DOCUMENTO ES UNA REPRESENTACION IMPRESA DE UN CFD'));


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
$YPos = $Page_Height - $Top_Margin -550;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
$pdf->line($XPos+460, $YPos+75,$XPos-10, $YPos+75);//linea de arriba de la cajita
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
$XPos = $Page_Width - 570;
$YPos = $Page_Height - $Top_Margin -671;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$pdf->partEllipse($XPos+530,$YPos+93,0,90,10,10);//curvita superiror derecha de la cajita
/*line to the top left */
$pdf->line($XPos+530, $YPos+103,$XPos, $YPos+103);//linea de arriba de la cajita
$pdf->line($XPos+540, $YPos+60,$XPos-10, $YPos+60);//linea de que separa sello
/***********************/
/* etiquetas*/

//$pdf->line($XPos+540, $YPos+85,$XPos-10, $YPos+85);//linea que separa etiquetas
/*************************/
/*Dow top left corner */
$pdf->partEllipse($XPos, $YPos+93,90,180,10,10);//curvita superiror izquierda
/*Do a line to the bottom left corner */
$pdf->line($XPos-10, $YPos-30,$XPos-10, $YPos+93);//linea izquierda de la cajita
/*Now do the bottom left corner 180 - 270 coming back west*/
$pdf->partEllipse($XPos, $YPos-30,180,270,10,10);//curvita inferiror izquierda
/*Now a line to the bottom right */
$pdf->line($XPos, $YPos-40,$XPos+531,$YPos-40);//linea de abajo de la cajita
/*Now do the bottom right corner */
$pdf->partEllipse($XPos+530, $YPos-30,270,360,10,10);//curvita inferior derecha
/*Finally join up to the top right corner where started */
$pdf->line($XPos+540, $YPos-30,$XPos+540, $YPos+93);//linea derecha de la cajita
//$pdf->line($XPos+480, $YPos-40,$XPos+480, $YPos+103);//linea derecha de la cajita

$FontSize=7;
$carpeta='Facturas';
$legalname=$myrow3['legalname'];
$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
$nufa = $serie.$folioX;
$mitxt=$dir.$nufa.".xml";
$cfd=file_get_contents($mitxt); 
//echo htmlentities($cfd);

$DatosCFD = TraeDatosCFD($cfd,'Receptor');


$cadenaDatos=explode('|',$myrow3['cadena']);
if (strpos($myrow3['cadena'],'XAXX010101000')==true){
	$pdf->addTextWrap(40,670,527,$FontSize,'PUBLICO GENERAL');
	$pdf->addTextWrap(40,660,527,$FontSize,'RFC: XAXX010101000');
	
	/*$pdf->addTextWrap(40,650,170,$FontSize,$myrow3['dirfrom1']);
	$pdf->addTextWrap(40,641,117,$FontSize,$myrow3['dirfrom2'] .',');
	$pdf->addTextWrap(40,631,117,$FontSize,$myrow3['dirfrom3']);
	$pdf->addTextWrap(40,621,117,$FontSize,$myrow3['dirfrom4']);
*/

}else{
	if(strlen($DatosCFD['rfc'])==0){
		$rfc=$myrow3['RFC'];
	}else{
		$rfc=$DatosCFD['rfc'];
	}
	
	if(strlen($DatosCFD['nombre'])==0){
		$nombrebr=$myrow3['brname'];
	}else{
		$nombrebr=$DatosCFD['nombre'];
	}
	
	$domicilio=$DomicilioCFD['calle'].' '.$DomicilioCFD['noExterior'].' '.$DomicilioCFD['noInterior'];
	if(strlen(trim($domicilio))<=2){
		$domicilio=$myrow3['dirfrom1'];
		
	}
	$colonia=$DomicilioCFD['colonia'];
	if(trim($colonia)==0){
		$colonia=$myrow3['dirfrom6'];
	}
	$dircp=$DomicilioCFD['municipio'].' , '.$DomicilioCFD['codigoPostal'].' '.$DomicilioCFD['estado'];
	
	if(strlen(trim($dircp))<=4){
		$dircp=$myrow3['dirfrom3'].' '.$myrow3['dirfrom4'];
	}
	$dirpais=$DomicilioCFD['pais'];
	if(strlen(trim($dirpais))==0){
		$dirpais='Mexico';
	}
	
	$sobranteNombre = $pdf->addTextWrap(40,670+13,170,$FontSize,$myrow3['cliente'].' '.utf8_decode($nombrebr));
	$pdf->addTextWrap(40,670+3,170,$FontSize,$sobranteNombre);
	
	$pdf->addTextWrap(40,660,527,$FontSize,'RFC:'.utf8_decode($rfc));
	$DomicilioCFD=TraeDatosCFD($cfd,'Domicilio');
	$pdf->addTextWrap(40,650,170,$FontSize,$domicilio);
	if($cadenaDatos[35] != "")
		$pdf->addTextWrap(40,641,180,$FontSize,$colonia );
	else
		$pdf->addTextWrap(40,641,180,$FontSize,$colonia);
	if(!is_numeric($DomicilioCFD['codigoPostal'])){
		$pdf->addTextWrap(40,631,190,6.5,$dircp);
		$pdf->addTextWrap(40,621,317,6.5,$dirpais.' '.$DomicilioCFD['referencia']);
	}else{
		$pdf->addTextWrap(40,631,190,6,$dircp);
		$pdf->addTextWrap(40,621,317,6.5,$dirpais.' '.$DomicilioCFD['referencia']);
		
	}
		/*
	}
	if($cadenaDatos[39]=='MEXICO'){
		$pdf->addTextWrap(40,631,180,$FontSize,$cadenaDatos[36] .','.$cadenaDatos[40].' '.$cadenaDatos[41]);
		$pdf->addTextWrap(40,621,317,6.5,$cadenaDatos[39].' '.$cadenaDatos[37]);
	}else{
		$pdf->addTextWrap(40,631,180,$FontSize,$cadenaDatos[36] .','.$cadenaDatos[39].' '.$cadenaDatos[41]);
		$pdf->addTextWrap(40,621,317,6.5,$cadenaDatos[40].' '.$cadenaDatos[37]);
		
	}*/
	
}
if (strpos($myrow3['cadena'],'XAXX010101000')==false){
	$sobranteNombre = $pdf->addTextWrap(220,670+13,170,$FontSize,$myrow3['cliente'].' '.utf8_decode($nombrebr));
	$pdf->addTextWrap(220,670+3,170,$FontSize,$sobranteNombre);
	$pdf->addTextWrap(220,660,170,$FontSize,$domicilio);
	$pdf->addTextWrap(220,650,180,$FontSize,$colonia);
	if(!is_numeric($DomicilioCFD['codigoPostal'])){
		$pdf->addTextWrap(220,641,190,6.5,$dircp);
		$pdf->addTextWrap(220,631,317,6.5,$dirpais);
	}else{
		$pdf->addTextWrap(220,641,210,6.5,$dircp);
		$pdf->addTextWrap(220,631,317,6.5,$dirpais);
		
	}
	
	/*if($cadenaDatos[39]=='MEXICO'){
		//$pdf->addTextWrap(220,641,117,$FontSize,$cadenaDatos[36] .' , '.$cadenaDatos[40].'  '.$cadenaDatos[41]);
		$pdf->addTextWrap(220,641,190,$FontSize,$cadenaDatos[36] .','.$cadenaDatos[40].' '.$cadenaDatos[41]);
		$pdf->addTextWrap(220,631,117,$FontSize,$cadenaDatos[39]);
	}else{
		$pdf->addTextWrap(220,641,190,$FontSize,$cadenaDatos[36] .' , '.$cadenaDatos[39].'  '.$cadenaDatos[41]);
		$pdf->addTextWrap(220,631,117,$FontSize,$cadenaDatos[40]);
	}*/
	
	$pdf->addTextWrap(220,621,117,$FontSize,$DomicilioCFD['referencia']);
}else{
	$sobranteNombre = $pdf->addTextWrap(220,670+13,170,$FontSize,$myrow3['cliente'].' '.$myrow3['brname']);
	$pdf->addTextWrap(220,670+3,170,$FontSize,$sobranteNombre);
	$pdf->addTextWrap(220,660,170,$FontSize,$myrow3['dirfrom1']);
	if($myrow3['dirfrom6'] != "")
		$pdf->addTextWrap(220,650,180,$FontSize,$myrow3['dirfrom6'] .' , '.$myrow3['dirfrom2']);
	else
		$pdf->addTextWrap(220,650,180,$FontSize,$myrow3['dirfrom6'] .$myrow3['dirfrom2']);
	$pdf->addTextWrap(220,641,117,$FontSize,$myrow3['dirfrom3']);
	$pdf->addTextWrap(220,631,117,$FontSize,$myrow3['dirfrom4']);
	$pdf->addTextWrap(220,621,117,$FontSize,'Tel.'.$myrow3['telcliente']);	
}

$YPos = $Page_Height - $Top_Margin-95 ;



//Fin de cajita texto
?>
