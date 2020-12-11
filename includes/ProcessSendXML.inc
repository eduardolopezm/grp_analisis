<?php
//for($dia=11;$dia<=11;$dia++){
	$ErrMsg='';
//	if ($dia!=0){
		$SQL="select debtortrans.*
			from debtortrans where type in (11, 13)
				
				";
			echo "<br>entra aki";
		$ResultDatos=DB_query($SQL,$db,$ErrMsg,'',false,true);
		$contador=0;
		while ($MyrowDatos=DB_fetch_array($ResultDatos)) {
			echo "<br>entra aki 222";
			$contador=$contador+1;	
			$InvoiceNo=$MyrowDatos['transno'];
			$OrderNo=$MyrowDatos['order_'];
			$DebtorNo=$MyrowDatos['debtorno'];
			$tipodefacturacion=$MyrowDatos['type'];
			$Tagref=$MyrowDatos['tagref'];
			$InvoiceNoTAG=$MyrowDatos['folio'];
			$separa = explode('|',$InvoiceNoTAG);
			$DebtorTransID=$MyrowDatos['id'];
			if ($tipodefacturacion==12){
				$serie = $separa[1];
				$folio = $separa[0];
			}else{
				$serie = $separa[0];
				$folio = $separa[1];
			}
			echo '<br><br><br><br>'.$folio;
			if ($tipodefacturacion==12){
				$OrderNo = 0;
				
				$compelectronico= XSAInvoicingRecibo($InvoiceNo, $OrderNo, '10080_32', $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//echo $compelectronico;
				//$factelectronica=utf8_encode($compelectronico);
				$XMLElectronico=generaXML($compelectronico,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Recibo',$OrderNo,$db);
			}elseif($tipodefacturacion==21 or $tipodefacturacion==105){
				$OrderNo=0;
				$factelectronica= XSAInvoicingCreditdirect($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$factelectronica=utf8_encode($factelectronica);
				$XMLElectronico=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'NCargo',$OrderNo,$db);
			}elseif($tipodefacturacion==13){
				$OrderNo=0;
				$factelectronica= XSAInvoicingCreditdirect($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$factelectronica=utf8_encode($factelectronica);
				$XMLElectronico=generaXML($factelectronica,'egreso',$Tagref,$serie,$folio,$DebtorTransID,'NCreditoDirect',$OrderNo,$db);
			}elseif($tipodefacturacion==11){
				$factelectronica= XSACreditNote($InvoiceNo, $Tagref, $OrderNo, $tipodefacturacion ,$Tagref,$serie,$folio, $db);
				//$factelectronica=utf8_encode($factelectronica);
				$XMLElectronico=generaXML($factelectronica,'egreso',$Tagref,$serie,$folio,$DebtorTransID,'NCredito',$OrderNo,$db);
			}else{
				//echo "<br>entra";
				$factelectronica= XSAInvoicing($InvoiceNo, $OrderNo, $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//echo "<br>entra ****" . $factelectronica;
				//$factelectronica=utf8_encode($factelectronica);
				$XMLElectronico=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
				echo "<br>entra ****++++" . htmlentities($XMLElectronico);
			}
			
			echo "<br>entra ****++++" . htmlentities($XMLElectronico);
			/* ob_flush();
			 flush();
			 sleep(2);
			*/
			//echo '<br>contador:'.$contador.'id:'.$DebtorTransID.' tipo:'.$tipodefacturacion.' folio:'.$folio.' serie:'.$serie.'fecha:'.$MyrowDatos['origtrandate'].'<br>';
			echo '<br>folio:'.$serie.'|'.$folio.'deb:'.$DebtorNo;
			}
		
//	}
//	echo '<br>mes:2,dia:'.$dia.'num de cfd:'.$contador;
//}
?>