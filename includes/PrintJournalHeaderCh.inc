<?php

include ('Numbers/Words.php');

if ($PageNumber>1){
	$pdf->newPage();
}

$Perforation = $Page_Width - $RightMargin - 200;

$YPos = $Page_Height - $Top_Margin;

//valores del cheque
$sql = "Select * from banktrans
		where type= ".$type." 
		and transno = ".$TransNo;
$res = DB_query($sql,$db);
$beneficiario="";
if ($myrowb=DB_fetch_array($res)){
	$beneficiario = $myrowb['beneficiary'];
	$transno = $myrowb['transno'];
	$chequeno = $myrowb['chequeno'];
	$total = $myrowb['amount']*-1;
	$moneda = $myrowb['currcode'];
	$fecha = $myrowb['transdate'];
}

$separa=explode(".",round($total,2));
$montoctvs2 = $separa[1];
if ($montoctvs2=="")
	$montoctvs2="00";
	
$montoctvs1 = $separa[0];
if (left($montoctvs2,3)>=995){
	$montoctvs1=$montoctvs1+1;
	
}
//$montoctvs1x=$montoctvs1x.' '.left($montoctvs2,3);
if ($moneda=='USD' ){
	$montoletra=Numbers_Words::toWords($montoctvs1,'en_US');
}else{
	$montoletra=Numbers_Words::toWords($montoctvs1,'es');
}

$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
if ($moneda=='MXN'){
	$montoletra=ucwords($montoletra) . " Pesos ". $montoctvs2 ." /100 M.N.";
}
else
{
	$montoletra=ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD";
}

//$pdf->addJpegFromFile($_SESSION['LogoFile'],$Left_Margin,$YPos-10,0,30);

//escribir datos en el cheque
$FontSize =13;

$pdf->addText($Page_Width-150, $YPos-10,$FontSize,"Cheque No:".$chequeno,'right');
$pdf->addText($Page_Width-150, $YPos-25,$FontSize,"ERP:".$transno,'right');

$pdf->addText($Page_Width-150, $YPos-45,$FontSize,$fecha);
$pdf->addText($Left_Margin+10, $YPos-90,$FontSize,$beneficiario);
$pdf->addText($Page_Width-100, $YPos-90,$FontSize,number_format($total,2));
$pdf->addText($Left_Margin+10, $YPos-130,$FontSize,$montoletra);


$YPos = $Page_Height - $Top_Margin - 40;

$LineCountR += 3;
$YPos = $Page_Height - $Top_Margin - 130;

$FontSize=8;
$YPos -= $line_height*12;
$XPos = $Left_Margin;

?>
