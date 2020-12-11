<?php

$SQL = "SELECT debtortrans.*, legalbusinessunit.legalname, legalbusinessunit.taxid,systypescat.EnvioFiscal
	FROM debtortrans
	LEFT JOIN tags ON tags.tagref = debtortrans.tagref
	LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
		inner join systypescat ON systypescat.typeid=debtortrans.type
	WHERE
	 type IN (119)
	AND transno=3698";

$ResultDatos = DB_query($SQL, $db);
$carpeta = '';
$errores = array();
$esFiscal = 1;

while ($MyrowDatos = DB_fetch_array($ResultDatos)) {

	$InvoiceNo = $MyrowDatos['transno'];
	$OrderNo = $MyrowDatos['order_'];
	$DebtorNo = $MyrowDatos['debtorno'];
	$tipodefacturacion = $MyrowDatos['type'];
	$Tagref = $MyrowDatos['tagref'];
	$InvoiceNoTAG = $MyrowDatos['folio'];
	$rfc = $MyrowDatos['taxid'];
	$legalname = $MyrowDatos['legalname'];
	$separa = explode('|',$InvoiceNoTAG);
	$DebtorTransID = $MyrowDatos['id'];
	$enviofiscal=$MyrowDatos['EnvioFiscal'];

	if ($tipodefacturacion == 12) {
		$serie = $separa[1];
		$folio = $separa[0];
	} else {
		$serie = $separa[0];
		$folio = $separa[1];
	}

	$XMLElectronico = "";
	$sql = "SELECT xmlSat, fiscal,xmlImpresion FROM Xmls WHERE transNo = '$InvoiceNo' AND type = '$tipodefacturacion'";
	echo $sql;
	$xmlsRs = DB_query($sql, $db);
	if ($xmlsRow = DB_fetch_array($xmlsRs)) {
		$XMLElectronico = $xmlsRow['xmlSat'];
		$xmlImpresion1=$xmlsRow['xmlImpresion'];
		$enviofiscal=$xmlsRow['fiscal'];
		//$esFiscal = $xmlsRow['fiscal'];
	}

	echo '<pre>xml1:'.htmlentities($xmlImpresion1);

		//if ($XMLElectronico != "") {
			if ($tipodefacturacion == 12) {
				$OrderNo = 0;
				$compelectronico = XSAInvoicingRecibo($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$XMLElectronico = generaXMLCFDI($compelectronico,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Recibo',$OrderNo,$db);
			} else if ($tipodefacturacion == 21) {
				$OrderNo = 0;
				$factelectronica = XSACreditNote($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$XMLElectronico = generaXMLCFDI($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'NCargo',$OrderNo,$db);
			} else if ($tipodefacturacion == 13) {
				$OrderNo = 0;
				$factelectronica = XSACreditNote($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$XMLElectronico = generaXMLCFDI($factelectronica,'egreso',$Tagref,$serie,$folio,$DebtorTransID,'NCreditDirect',$OrderNo,$db);
			} else if ($tipodefacturacion == 11) {
				$factelectronica = XSACreditNote($InvoiceNo, $Tagref, $OrderNo, $tipodefacturacion ,$Tagref,$serie,$folio, $db);
				//$XMLElectronico = generaXMLCFDI($factelectronica,'egreso',$Tagref,$serie,$folio,$DebtorTransID,'NCredito',$OrderNo,$db);
			} else {
				$factelectronica = XSAInvoicing($InvoiceNo, $OrderNo, $DebtorNo, $tipodefacturacion,$Tagref,$serie,$folio, $db);
				//$XMLElectronico = generaXMLCFDI($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
			}
		//}

		if($enviofiscal==1){
			//$XMLElectronico=$XMLElectronico;
			$arrayGeneracion=generaXMLCFDI($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
		}else{
			$arrayGeneracion=generaXML($factelectronica,'ingreso',$Tagref,$serie,$folio,$DebtorTransID,'Facturas',$OrderNox,$db);
		}
		if($XMLElectronico==''){
			$XMLElectronico=$arrayGeneracion["xml"];
		}
		$array=generaXMLIntermedio($factelectronica,$XMLElectronico,$arrayGeneracion["cadenaOriginal"],$arrayGeneracion["cantidadLetra"],$OrderNox,$db,1,$Tagref);

		echo var_dump($arrayGeneracion);
		$xmlImpresion= utf8_decode($array["xmlImpresion"]);
		echo '<pre><br><br>xml2:'.htmlentities($xmlImpresion);
		$rfcEmisor=$arrayGeneracion["rfcEmisor"];
		$fechaEmision=$arrayGeneracion["fechaEmision"];

		$query="UPDATE Xmls
				SET xmlImpresion='".($xmlImpresion)."'
				where transNo=".$InvoiceNo ." and type=".$tipodefacturacion;
		$Result= DB_query($query,$db,$ErrMsg,$DbgMsg,true);


}

if(empty($errores) == false) {
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$to = "soporte@grp.com";
	$subject = "Errores Retimbrado";
	mail($to, $subject, implode('<br/>', $errores), $headers);
}
?>
