<?php
/* $Revision: 1.3 $ */
/*PDF page header for price list report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
//echo '<br>Imagen:'.var_dump($myrow2['logo']);
$pdf->addJpegFromFile($myrow2['logo'],$XPos+30,$YPos-50,0,55);

$YPosi = 720;
$pdf->addText($XPos+30, $YPosi,$FontSize,$myrow2['legalname']);
$FontSize =8;
$pdf->addText($XPos+30, $YPosi-12,$FontSize,$myrow2['address1']);
$pdf->addText($XPos+30, $YPosi-21,$FontSize, $myrow2['address2']);
$mensajeDatos = "";
if (trim($myrow2['address3']) != '') {
    $mensajeDatos = $myrow2['address3'];
}
if (trim($myrow2['address4']) != '') {
    if ($mensajeDatos == '') {
        $mensajeDatos = $myrow2['address4'];
    }else{
        $mensajeDatos .= ' '.$myrow2['address4'];
    }
}
if (trim($myrow2['address5']) != '') {
    if ($mensajeDatos == '') {
        $mensajeDatos = $myrow2['address5'];
    }else{
        $mensajeDatos .= ' '.$myrow2['address5'];
    }
}
// $myrow2['address3']. ' ' . $myrow2['address4'].' '.$myrow2['address5']
$pdf->addText($XPos+30, $YPosi-30,$FontSize, $mensajeDatos);
$pdf->addText($XPos+30, $YPosi-39,$FontSize, _('Tel') . ': ' . $myrow2['telephone'] . ' ' . _('Fax'). ': ' . $myrow2['fax']);
$pdf->addText($XPos+30, $YPosi-48,$FontSize, $myrow2['email']);

$FontSize=10;
//$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-220,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*1.5),550,$FontSize, utf8_decode('Recepción No. ').'                          ' . $_POST['GRNNo'] );
if(empty($_GET['SupplierOrderNo'])) {
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*3),550,$FontSize, utf8_decode('Número de orden de compra').'     ' . $_GET['PONo'] );
} else {
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*3),550,$FontSize, utf8_decode('Número de orden proveedor').'     ' . $_GET['SupplierOrderNo'] );
}
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*4.5),240,$FontSize, _('Impreso').': ' . Date($_SESSION['DefaultDateFormat']) . '      '. _('Pagina'). '  ' . $PageNumber);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-180,$YPos-($line_height*6),240,$FontSize, _('Usuario').': ' . $_SESSION['UserID']);

$YPos -= 100;

$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(2*$line_height);

/*Draw a rectangle to put the headings in     */

$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

/*set up the headings */
$Xpos = $Left_Margin+1;
$FontSize=8;
$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,300-$Left_Margin,$FontSize,  utf8_decode('Código'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+85,$YPos,300-$Left_Margin,$FontSize,  utf8_decode('Descripción'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+260,$YPos,300-$Left_Margin,$FontSize,  _('Serie/Lote'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+345,$YPos,300-$Left_Margin,$FontSize,  _('Cantidad'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+430,$YPos,300-$Left_Margin,$FontSize,  _('Costo'), 'centre');
$LeftOvers = $pdf->addTextWrap($Xpos+490,$YPos,300-$Left_Margin,$FontSize,  _('Total'), 'centre');


$FontSize=8;
$YPos -= (1.5 * $line_height);

$PageNumber++;

?>