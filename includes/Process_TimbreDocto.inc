<?php
$sql = "SELECT debtortrans.*, legalbusinessunit.legalname, legalbusinessunit.taxid FROM debtortrans
	LEFT JOIN tags ON tags.tagref = debtortrans.tagref
	LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
	WHERE uuid IS NULL 
	AND flagfiscal = 1
	AND invtext NOT LIKE '%cancel%'
	AND date_format(origtrandate, '%Y-%m-%d') = SUBDATE(CURRENT_DATE(), 1)
	AND type IN (10, 110, 11, 12, 21, 13) 
	-- AND folio = 'ONCR|1765'";

$rs = DB_query($sql, $db);
$carpeta = '';
$errores = array();

while ($MyrowDatos = DB_fetch_array($rs)) {

	$InvoiceNo = $MyrowDatos['transno'];
	$OrderNo = $MyrowDatos['order_'];
	$DebtorNo = $MyrowDatos['debtorno'];
	$tipodefacturacion = $MyrowDatos['type'];
	$Tagref = $MyrowDatos['tagref'];
	$InvoiceNoTAG = $MyrowDatos['folio'];
	$rfc = $MyrowDatos['taxid'];
	$legalname = $MyrowDatos['legalname'];
	$separa = explode('|', $InvoiceNoTAG);
	$DebtorTransID = $MyrowDatos['id'];
	$esFiscal = $MyrowDatos['flagfiscal'];
	$XMLElectronico = "";
	
	if ($esFiscal == 1) {
		
		if ($tipodefacturacion == 12) {
			$serie = $separa[1];
			$folio = $separa[0];
		} else {
			$serie = $separa[0];
			$folio = $separa[1];
		}
		
		$sql = "SELECT xmlSat, fiscal FROM Xmls 
			WHERE transNo = '$InvoiceNo' AND type = '$tipodefacturacion'";
		$xmlsRs = DB_query($sql, $db);
		if ($xmlsRow = DB_fetch_array($xmlsRs)) {
			$XMLElectronico = $xmlsRow['xmlSat'];
		}
		
		if ($XMLElectronico == "") {	
				
			if ($tipodefacturacion == 12) {
				$OrderNo = 0;
				$compelectronico = XSAInvoicingRecibo($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion, $Tagref, $serie, $folio, $db);
				$XMLElectronico = generaXMLCFDI($compelectronico, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Recibo', $OrderNo, $db);
			} else if ($tipodefacturacion == 21) {
				$OrderNo = 0;
				$factelectronica = XSACreditNote($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion, $Tagref, $serie, $folio, $db);
				$XMLElectronico = generaXMLCFDI($factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'NCargo', $OrderNo, $db);
			} else if ($tipodefacturacion == 13) {
				$OrderNo = 0;
				$factelectronica = XSACreditNote($InvoiceNo, $OrderNo , $DebtorNo, $tipodefacturacion, $Tagref, $serie, $folio, $db);
				$XMLElectronico = generaXMLCFDI($factelectronica, 'egreso', $Tagref, $serie, $folio, $DebtorTransID, 'NCreditDirect', $OrderNo, $db);
			} else if ($tipodefacturacion == 11) {
				$factelectronica = XSACreditNote($InvoiceNo, $Tagref, $OrderNo, $tipodefacturacion, $Tagref, $serie, $folio, $db);
				$XMLElectronico = generaXMLCFDI($factelectronica, 'egreso', $Tagref, $serie, $folio, $DebtorTransID, 'NCredito', $OrderNo, $db);
			} else {
				$factelectronica = XSAInvoicing($InvoiceNo, $OrderNo, $DebtorNo, $tipodefacturacion, $Tagref, $serie, $folio, $db);
				$XMLElectronico = generaXMLCFDI($factelectronica, 'ingreso', $Tagref, $serie, $folio, $DebtorTransID, 'Facturas', $OrderNo, $db);
			}
		}
			
		if ($tipodefacturacion == 12) {
			$carpeta = 'Recibo';
		} else if ($tipodefacturacion == 21) {
			$carpeta = 'NCargo';
		} else if ($tipodefacturacion == 13) {
			$carpeta = 'NCreditDirect';
		} else if ($tipodefacturacion == 11) {
			$carpeta = 'NCredito';
		} else {
			$carpeta = 'Facturas';
		}
		
		if ($XMLElectronico != "") {
			
			// Timbrado ...
			$success = false;
			$XMLElectronico = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $XMLElectronico);
			$timbrador = "";
			$sqlConf = "SELECT confname, confvalue FROM config";
			$rsConf = DB_query($sqlConf, $db);
			$config = array();
			while ($rowConf = DB_fetch_array($rsConf)) {
				$config[$rowConf['confname']] = $rowConf['confvalue'];
			}
			
			include_once 'timbradores/TimbradorFactory.php';
			$timbrador = TimbradorFactory::getTimbrador($config);
			$cfdi = "";
			
			if($timbrador != null) {
				$timbrador->setRfcEmisor($rfc);
				$timbrador->setDb($db);
				$cfdi = $timbrador->timbrarDocumento($XMLElectronico);
				$success = ($timbrador->tieneErrores() == false);
				foreach($timbrador->getErrores() as $error) {
					echo "<p style='color:red'>$error</p>";
					$errores[] = "Folio: $InvoiceNoTAG Error: $error";
				}
			} else {
				echo 'No hay un timbrador configurado en el sistema';
			}
			
			echo "<div style='border: 1px solid black; padding:1em'>";
			echo "<h4>Folio: " . $InvoiceNoTAG . "</h4>";
			echo "<p>Documento Original</p>";
			echo "<pre style='background-color:red; overflow:scroll'>" . htmlentities($XMLElectronico) . "</pre>";
			if (empty($cfdi) == false) {
				echo "<hr />";
				echo "<p>Documento Timbrado</p>";
				echo "<pre style='background-color:green; overflow:scroll'>" . htmlentities($cfdi) . "</pre>";
			}
			echo "</div>";
			
			if ($success) {
				$DatosCFDI = TraeTimbreCFDI($cfdi);
				if (strlen($DatosCFDI['FechaTimbrado']) > 0) {
					
					$cadenatimbre = '||1.0|' . $DatosCFDI['UUID'] . '|' . $DatosCFDI['FechaTimbrado'] . '|' . $DatosCFDI['selloCFD'] . '|' . $DatosCFDI['noCertificadoSAT'] . '||';
		
					// guardamos el timbre fiscal en la base de datos para efectos de impresion de datos
					$sql = "UPDATE debtortrans
						SET fechatimbrado = '" . $DatosCFDI['FechaTimbrado'] . "',
						uuid = '" . $DatosCFDI['UUID'] . "',
						timbre = '" . $DatosCFDI['selloSAT'] . "',
						cadenatimbre = '" . $cadenatimbre . "'
						WHERE id = " . $DebtorTransID;
					
					$Result= DB_query($sql, $db);
					$XMLElectronico = $cfdi;
		
					//Guardamos el XML una vez que se agrego el timbre fiscal
					$dir = "/var/www/html/erpdistribucion/companies/" . $_SESSION['DatabaseName'] . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/XML/" . $carpeta . "/";
					$nufa = $serie.$folio;
					$mitxt = $dir . $nufa . ".xml";
					unlink($mitxt);
					$fp = fopen($mitxt, "w");
					fwrite($fp, $XMLElectronico);
					fclose($fp);
				}
				
				//Actualiza registro en tabla de documentos XML
				$xmlImpresion = "";
				$query = "SELECT idXml FROM Xmls  WHERE transno = '$InvoiceNo' AND type = '$tipodefacturacion'";
				$result = DB_query($query, $db);
				if (DB_num_rows($result) > 0) {
					$query = "UPDATE Xmls SET xmlSat = '$XMLElectronico' 
						WHERE transno = '$InvoiceNo' AND type = '$tipodefacturacion'";
					$Result = DB_query($query, $db);
				} else {
					$query = "INSERT INTO Xmls(transno, type, rfcEmisor, fechaEmision, xmlSat, xmlImpresion) VALUES('$InvoiceNo', '$tipodefacturacion', '$rfc', NOW(), '$XMLElectronico', '$xmlImpresion')";
					$Result = DB_query($query, $db);
				}
			}
		}
	}
}

if (empty($errores) == false) {
	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$to = "soporte@tecnoaplicada.com";
	$subject = "Errores Retimbrado";
	mail($to, $subject, implode('<br/>', $errores), $headers);
}
?>