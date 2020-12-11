<?php
$FontSize=10;
$YPos=100;
$FontSize=8;
if (strpos($myrow3['cadena'],'XAXX010101000')!=true){
//$pdf->addTextWrap($xpos+420,235,150,$FontSize,'$ '.$iva,'right');
}
$YPos=$YPos+65;
$FontSize=6;

//
$FontSize=8;

$sql2="select salesorders.currcode,
				(sum((salesorderdetails.quantity*salesorderdetails.unitprice)-((salesorderdetails.quantity*salesorderdetails.unitprice)-(((salesorderdetails.quantity*salesorderdetails.unitprice)*(1-salesorderdetails.discountpercent))*(1-salesorderdetails.discountpercent1))*(1-salesorderdetails.discountpercent2))))as subtotal,
				(sum(((salesorderdetails.unitprice*salesorderdetails.quantity)*(1-salesorderdetails.discountpercent)* (1-salesorderdetails.discountpercent1)*(1-salesorderdetails.discountpercent2))) + salesorders.taxtotal) AS total
			from salesorderdetails,
				salesorders,debtorsmaster
			where salesorders.orderno = salesorderdetails.orderno AND debtorsmaster.debtorno = salesorders.debtorno
				AND salesorders.orderno=" .$_GET['TransNo'] ." GROUP by currcode ";
			$result2=DB_query($sql2,$db, $ErrMsg);
			if (DB_num_rows($result2)>0){
				while ($myrow2=DB_fetch_array($result2)){
					$pdf->addTextWrap($xpos+400,180,90,$FontSize=12,_('SubTotal'),'right');
					$pdf->addTextWrap($xpos+500,180,90,$FontSize,'$ '.number_format($myrow2['subtotal'],2),'right');
					//aqui le pongo la coma con el number_format para que se separe la cantida de los decimales
					//532648 queda 532,648.00
					$pdf->addTextWrap($xpos+400,160,90,$FontSize=12,_('Iva'),'right');
					$pdf->addTextWrap($xpos+500,160,90,$FontSize,'$ '.$iva,'right');
					$total=number_format($myrow2['total'],2);
					$pdf->addTextWrap($xpos+400,140,90,$FontSize=12,_('Total'),'right');
					$pdf->addTextWrap($xpos+500,140,90,$FontSize,'$ '.$total,'right');
					$YPos=365;
						//aqui lo que se realiza es la cantidad en letra
						//esta linea guarda en una lista los valores enteros en $montoctvs1 y los decimales en $montoctvs2
						//el explode sirve para encontrar los valores despues del punto
						list($montoctvs1,$montoctvs2)=explode(".",$myrow2['total']);
						//aqui reasigna a $montoletra la cantidad entera
						//la funcion Numbers_Words::toWords convierte los numeros en letras
						$montoletra=Numbers_Words::toWords($montoctvs1,'es');
						//aqui reasigna a $montocentavos la cantidad decimal
						$montocentavos=Numbers_Words::toWords($montoctvs2,'es');
						if ($myrow2['currcode']=='MXN'){
							$pdf->addText($Page_Width-$Right_Margin-580,$YPos-240,$FontSize, " ( ".ucwords($montoletra) . " Pesos ". $montoctvs2 ."/100 M.N. ) " ,'right');	
						}
						else
						{		
							$pdf->addText($Page_Width-$Right_Margin-500, $YPos-225,$FontSize, " ( ".ucwords($montoletra) . " Dolares ". $montoctvs2 ."/100 USD ) " ,'right');				
						}
				}//fin del while reult2 de subtotal y total
			}

?>
