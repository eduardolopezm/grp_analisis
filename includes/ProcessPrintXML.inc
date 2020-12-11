<?php
ini_set('display_errors', 1);;
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting(E_ALL);



//for($dia=11;$dia<=11;$dia++){
	$ErrMsg='';
//	if ($dia!=0){
		$SQL="select debtortrans.*, case when debtortrans.invtext like '%cancelad%' then 1 else 0 end as cancelado
				 from debtortrans inner join  Xmls on Xmls.type=debtortrans.type and Xmls.transNo=debtortrans.transno
				and  debtortrans.invtext not like '%cancelad%' where Xmls.type in (11,13,65) and Xmls.transNo = 21
				
				";//
		$ResultDatos=DB_query($SQL,$db,$ErrMsg,'',false,true);
		$contador=0;
		while ($MyrowDatos=DB_fetch_array($ResultDatos)) {
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
			//echo '<br>folio:'.$serie.'|'.$folio.'deb:'.$DebtorNo.'contador:'.$contador;
			//
			//echo '<br><br><br><br>'.$folio;
			if ($tipodefacturacion==12){
				$OrderNo = 0;
				
				$compelectronico= XSAInvoicingRecibo($InvoiceNo, $OrderNo, '10080_32', $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//echo $compelectronico;
				//$factelectronica=utf8_encode($compelectronico);
				//$XMLElectronico=generaXML($compelectronico,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Recibo',$OrderNo,$db);
			}elseif($tipodefacturacion==21 or $tipodefacturacion==105){
				$OrderNo=0;
				$factelectronica= XSAInvoicingCreditdirect($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$factelectronica=utf8_encode($factelectronica);
				//$XMLElectronico=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'NCargo',$OrderNo,$db);
			}elseif($tipodefacturacion==13){
				$OrderNo=0;
				$factelectronica= XSAInvoicingCreditdirect($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$factelectronica=utf8_encode($factelectronica);
				//$XMLElectronico=generaXML($factelectronica,'egreso',$Tagref,$serie,$folio,$DebtorTransID,'NCreditoDirect',$OrderNo,$db);
			}elseif($tipodefacturacion==11){
				$factelectronica= XSAInvoicing($InvoiceNo, $Tagref, $OrderNo, $tipodefacturacion ,$Tagref,$serie,$folio, $db);
				//$factelectronica=utf8_encode($factelectronica);
				//$XMLElectronico=generaXML($factelectronica,'egreso',$Tagref,$serie,$folio,$DebtorTransID,'NCredito',$OrderNo,$db);
			}else{
				//echo "<br>entra";
				$factelectronica= XSAInvoicing($InvoiceNo, $OrderNo, $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
			}	//extrae de la base de datos el xml
				$SQL="SELECT * from Xmls where transNo=".$InvoiceNo." and type=".$tipodefacturacion;
				$ErrMsg=_('El Sql que fallo fue');
				$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
				$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				if (DB_num_rows($Result)==1) {
					$myrowtags = DB_fetch_array($Result);
					$xml=trim($myrowtags['xmlSat']);
					echo '<pre><br>'.htmlentities($xml);
				}else{
					echo _('No Existe el Documento solicitado , por favor verifique');
				}
				$XMLElectronico=$xml;
				$arraycadena=explode('@%',$factelectronica);
				$linea=$arraycadena[0];
				
				$datos=explode('|',$linea);
				$cantidadletra=$datos[11];
				$cadenatimbre=$MyrowDatos['cadenatimbre'];
				if($tipodefacturacion == 13){
					$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$cadenatimbre,$cantidadletra,$OrderNo,$db,13,$Tagref,$MyrowDatos['cancelado']);
				}else{
					$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$cadenatimbre,$cantidadletra,$OrderNo,$db,11,$Tagref,$MyrowDatos['cancelado']);
				}
				//$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$cadenatimbre,$cantidadletra,$OrderNo,$db,11,$Tagref,$MyrowDatos['cancelado']);
				$xmlImpresion= $array["xmlImpresion"];
				$rfcEmisor=$array["rfcEmisor"];
				$fechaEmision=$array["fechaEmision"];
				// Inicializo transaccion
				$Result = DB_Txn_Begin($db);
				if($transno==null || empty($transno)){
					$transno=$InvoiceNo;
				}
				
				
				$query="UPDATE Xmls SET xmlImpresion='".$xmlImpresion."' where transNo=".$transno ." and type=".$tipodefacturacion;
				$Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);
				echo $query;
				echo '<pre><br>'.htmlentities($query);
				
				// Finalizo transaccion
				$Result = DB_Txn_Commit($db);
				
				
				
				
				//$XMLElectronico=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
				
				
				
				//echo "<br>entra ****++++" . htmlentities($XMLElectronico);
			}
			
		//	echo "<br>entra ****++++" . htmlentities($XMLElectronico);
			/* ob_flush();
			 flush();
			 sleep(2);
			*/
			//echo '<br>contador:'.$contador.'id:'.$DebtorTransID.' tipo:'.$tipodefacturacion.' folio:'.$folio.' serie:'.$serie.'fecha:'.$MyrowDatos['origtrandate'].'<br>';
			//echo '<br>folio:'.$serie.'|'.$folio.'deb:'.$DebtorNo.'contador:'.$contador;
			//}
		
//	}
//	echo '<br>mes:2,dia:'.$dia.'num de cfd:'.$contador;
//}

			
			
			
			
?>