<?php
/* $Revision: 1.14 $ */
/* Archivo modificado por  */

if (!$FirstPage){
}

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
    $YPos = 720;
   // $pdf->addText($XPos, $YPos,$FontSize,$_SESSION['CompanyRecord']['coyname']);
    $FontSize =8;
    //$pdf->addText($XPos, $YPos-12,$FontSize,$_SESSION['CompanyRecord']['regoffice1']);
    //$pdf->addText($XPos, $YPos-21,$FontSize, $_SESSION['CompanyRecord']['regoffice2']);
    //$pdf->addText($XPos, $YPos-30,$FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
    //$pdf->addText($XPos, $YPos-39,$FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax'). ': ' . $_SESSION['CompanyRecord']['fax']);
    //$pdf->addText($XPos, $YPos-48,$FontSize, $_SESSION['CompanyRecord']['email']);

}
$sql="SELECT * FROM conf_printdocsdetails
WHERE tipo='encabezado' and conf_printdocsdetails.u_conf=".$u_conf ." and conf_printdocsdetails.activo=1";
$result_h=DB_query($sql,$db, $ErrMsg);
while ($myrow_h=DB_fetch_array($result_h))
{
	if ($myrow_h['valor']=='total') {
		$posxtot=$myrow_h['posx'];
		$posytot=$myrow_h['posy'];
		$fontsizetot=$myrow_h['font_size'];
		$lontot=$myrow_h['longitud'];
		$dectot=$myrow_h['decimales'];
	}
	if ($myrow_h[3]=='Label'){
		$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$myrow_h['valor']);
	} else {
		$valor=$myrowdatos[$myrow_h['posvalor']];
		if (trim($myrow_h['formato'])=='numerico') {
			$valor=number_format($valor,$myrow_h['decimales']);
		}
		if (trim($myrow_h['formato'])=='fecha') {
			$valor=ConvertSQLDate($valor);
		}
		if ($myrow_h['valor']=='iva'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$valor,'right');
		}
		if ($myrow_h['valor']!='iva' and $myrow_h['formato']!='numerico'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$valor);
		}
		if ($myrow_h['valor']=='lugar1'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$_SESSION['CompanyRecord']['regoffice1']);
		}
		if ($myrow_h['valor']=='lugar2'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$_SESSION['CompanyRecord']['regoffice2']);
		}
		if ($myrow_h['valor']=='lugar3'){
			$pdf->addTextWrap($myrow_h['posx'],$myrow_h['posy'],$myrow_h['longitud'],$myrow_h['font_size'],$_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
		}
	}
	
	if ($myrow_h['valor']=='canletra') {
		$posxcanletra=$myrow_h['posx'];
		$posycanletra=$myrow_h['posy'];
		$fontsizecanletra=$myrow_h['font_size'];
		$loncanletra=$myrow_h['longitud'];
	}  
	
}

?>
