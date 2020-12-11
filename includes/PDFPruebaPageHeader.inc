<?php
/* $Revision: 1.14 $ */
/* Archivo modificado por  */
if ($PageNumber>1){
	$pdf->newPage();
}
$YPos = $Page_Height;
$XPos = $Page_Width/4;
$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos-20,$YPos-57,0,60);

$FontSize =15;
//$pdf->addText($XPos+350, $YPos, $FontSize, _('PAGARE') . ' ');
$XPos = $Page_Width - 265;
$YPos -= 111;
/* if the deliver blind flag is set on the order, we do not want to output
the company logo */
if ($DeliverBlind < 2) {
	$XPos =10;
    //$pdf->addJpegFromFile($_SESSION['LogoFile'],$XPos,730,0,60);
}

/* if the deliver blind flag is set on the order, we do not want to output
the company contact info */
if ($DeliverBlind < 2) {
    $FontSize=10;
   // $YPos = 720;
   // $pdf->addText($XPos, $YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
    $FontSize =8;
    $pdf->addText($XPos, $YPos+45,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
    $pdf->addText($XPos, $YPos+35,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
    $pdf->addText($XPos, $YPos+25,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
    $pdf->addText($XPos, $YPos+15,$FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
    $pdf->addText($XPos, $YPos+5,$FontSize, $_SESSION['CompanyRecord']['email']);

}
$cuenta=0;
$sql="SELECT * FROM conf_printdocsdetails
WHERE tipo='encabezado' and conf_printdocsdetails.u_conf=".$u_conf ." and conf_printdocsdetails.activo=1";
/*if($_SESSION['UserID'] == "admin"){
	echo '<pre>'.$sql;
}*/
$result_h=DB_query($sql,$db, $ErrMsg);
while ($myrow_h=DB_fetch_array($result_h))
{
$cuenta=$cuenta+1;
	if ($myrow_h['valor']=='titlenom') {
		$posxti=$myrow_h['posx'];
		$posyti=$myrow_h['posy'];
		$fontsizeti=$myrow_h['font_size'];
		$lonti=$myrow_h['longitud'];
	}

	if ($myrow_h['valor']=='iva') {
		$iva=$myrowdatos[$myrow_h['posvalor']];
		$ivatot=$iva+$ivatot;
		$decivat=$myrowdatos[$myrow_h['decimales']];
		$ivato=number_format($ivatot,$myrow_h['decimales']);
	}
	if ($myrow_h['valor']=='montocheque'){
		$xmontomen=$myrowdatos[$myrow_h['posvalor']];
		$posxt=$myrow_h['posx'];
		$post=$myrow_h['posy'];
		$fontsizet=$myrow_h['font_size'];
		$lont=$myrow_h['longitud'];
		$dect=$myrow_h['decimales'];
		
	}
	if ($myrow_h['valor']=='subtotal') {
		$posxsub=$myrow_h['posx'];
		$posysub=$myrow_h['posy'];
		$fontsizesub=$myrow_h['font_size'];
		$lonsub=$myrow_h['longitud'];
		$decsub=$myrow_h['decimales'];
	} 
	if ($myrow_h['valor']=='total') {
		$posxtot=$myrow_h['posx'];
		$posytot=$myrow_h['posy'];
		$fontsizetot=$myrow_h['font_size'];
		$lontot=$myrow_h['longitud'];
		$dectot=$myrow_h['decimales'];
	}//
	if ($myrow_h[3]=='Label'){
		$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$myrow_h['valor']);
	} else {
		$valor=str_replace("&QUOT;","\"",$myrowdatos[$myrow_h['posvalor']]);
		//$valor = iconv('UTF-8', 'windows-1252', $myrowdatos[$myrow_h['posvalor']]);
		$valor= str_replace('|','',$valor);
		if (trim($myrow_h['formato'])=='numerico') {
			$valor=number_format($valor,$myrow_h['decimales']);
		}
		if (trim($myrow_h['formato'])=='fecha' and $u_conf!=8 and $u_conf!=9 and $u_conf!=10 and $u_conf!=13) {
			$fech=$myrowdatos[$myrow_h['posvalor']];
			//echo 'entra'.$myrow_h['posvalor'];
			$separaf=explode("-",$fech);
			$año= $separaf[0];
			$mes= $separaf[1];
			$dia= $separaf[2];
			$hora= $separaf[3];
			$min= $separaf[4];
			$seg= $separaf[5];
			$valor=$dia.'-'.$mes.'-'.$año.' '.$hora.':'.$min.':'.$seg;
			$valor = $fech;
		}
		if ($myrow_h['valor']=='orderno'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$valor.'-');
		}
		if ($myrow_h['valor']!='iva' and $myrow_h['formato']!='numerico' and $myrow_h['valor']!='orderno') {
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$valor);
		}
	}
	
	
	if($myrow_h['valor']=='comentariosrenglon' or $myrow_h['valor']=='comenrenglon') {
		$Left_Marginxc=$myrow_h['posx'];
		$posyc=$myrow_h['posy'];
		$longc=$myrow_h['longitud'];
		$fonsizexc=$myrow_h['font_size'];
		$datosc=$myrowdatos[$myrow_h['posvalor']];
		$LeftOvers = $pdf->addTextWrap($Left_Marginxc,$posyc,$longc,$fonsizexc,$datosc);
		while (strlen($LeftOvers) > 1 ) {
			$posyc -= 1*$line_height*0.35;
			$LeftOvers = $pdf->addTextWrap($Left_Marginxc,$posyc,$longc,$fonsizexc,$LeftOvers);
		}
	}
	
	if ($myrow_h['valor']=='canletra') {
		$posxcanletra=$myrow_h['posx'];
		$posycanletra=$myrow_h['posy'];
		$fontsizecanletra=$myrow_h['font_size'];
		$loncanletra=$myrow_h['longitud'];
	}
	if ($myrow_h['valor']=='smontoma') {
		$posxma=$myrow_h['posx'];
		$posma=$myrow_h['posy'];
		$fontsizema=$myrow_h['font_size'];
		$lonma=$myrow_h['longitud'];
		$decma=$myrow_h['decimales'];
	}
	if ($myrow_h['valor']=='smontome') {
		$posxme=$myrow_h['posx'];
		$posme=$myrow_h['posy'];
		$fontsizeme=$myrow_h['font_size'];
		$lonme=$myrow_h['longitud'];
		$decme=$myrow_h['decimales'];
	}
	
}

?>
