<?php
function parse($xml, $name,$almacen,$supplierid,$userid) {

	$isCFDI = true;
	$xmlfirst=$xml;
	$xml = simplexml_load_string($xml);

	if(empty($xml) == FALSE) {
		//Parse as CFDI
		$childs = $xml->children('http://www.sat.gob.mx/cfd/3');
		if(empty($childs)) {
			// Parse as CFD
			$childs = $xml->children('http://www.sat.gob.mx/cfd/2');
			$isCFDI = false;
		}

		if(empty($childs) == FALSE) {
				
			$serie 		= $xml['serie'];
			$folio 		= $xml['folio'];
			$emisor		= $childs->Emisor->attributes();
			$domicilio 	= $childs->Emisor->DomicilioFiscal->attributes();
				
			// CFD Does not have Moneda Attribute...
			if(empty($xml['Moneda'])) {
				$condiciones = $xml['condicionesDePago'];
				if(empty($condiciones) == FALSE) {
					if(strpos($condiciones, 'MXN') !== FALSE) {
						$xml['Moneda'] = 'MXN';
					}
					if(strpos($condiciones, 'USD') !== FALSE) {
						$xml['Moneda'] = 'USD';
					}
				}
			}
				
			if(empty($xml['Moneda'])) {
				$metodPago = $xml['metodoDePago'];
				if(empty($metodPago) == FALSE) {
					if(strpos($metodPago, 'MXN') !== FALSE) {
						$xml['Moneda'] = 'MXN';
					}
					if(strpos($metodPago, 'USD') !== FALSE) {
						$xml['Moneda'] = 'USD';
					}
				}
			}
				
			// CFD Does not have TipoCambio Attribute, 1 default...
			if(empty($xml['TipoCambio'])) {
				$xml['TipoCambio'] = 1;
			}
				
			$data = array(
					'moneda' 	=> (string) $xml['Moneda'],
					'total' 	=> (string) $xml['total'],
					'subtotal' 	=> (string) $xml['subTotal'],
					'iva'		=> (string) ($xml['total'] - $xml['subTotal']),
					'tipoc' 	=> (string) $xml['TipoCambio'],
					'fecha'		=> str_replace('T', ' ', (string) $xml['fecha']),
					'LugarExp'	=> (string) $xml['LugarExpedicion'],
					'NumCtaPag' => (string) $xml['NumCtaPago'],
					'rfc'		=> (string) $emisor['rfc'],
					'nombre'	=> utf8_decode((string) $emisor['nombre']),
					'calle'		=> utf8_decode((string) $domicilio['calle']),
					'colonia'	=> utf8_decode((string) $domicilio['colonia']),
					'pais'		=> utf8_decode((string) $domicilio['pais']),
					'municipio'	=> utf8_decode((string) $domicilio['municipio']),
					'estado'	=> utf8_decode((string) $domicilio['estado']),
					'cp'		=> utf8_decode((string) $domicilio['codigoPostal']),
					'file'		=> $name,
					'folio'		=> "$serie|$folio"
			);
				
			//	echo var_dump($data);
				
			$link=conectToDatabase();
			//$validacion=array();
			$validacion=ValidToDatabase($data,$link,$xmlfirst);
			//echo 'val:'.$validacion['Error'];
			if($validacion['Error']==0){
				$orderno=loadPurchoOrderToDatabase($data,$link,$almacen,$supplierid,$userid);
				$alterorderno=loadPurchoOrderDetailsToDatabase($link,$xmlfirst,$orderno);
				//inserta en tabla de archivos de proveedores
				//$alterarchivo=loadArchivoToDatabase($link,$orderno,$data);
				echo '<br>Archivo Procesado:'.$name.' Serie:'.$serie.' Folio:'.$folio.'No Orden:'.$orderno;

			}else{
				return 0;
			}
				
		}
	}

	return $orderno;
}

function getDecodeValue($message, $coding) {
	switch($coding) {
		case 1:
			$message = imap_8bit($message);
			break;
		case 2:
			$message = imap_binary($message);
			break;
		case 0:
		case 3:
		case 5:
		case 6:
		case 7:
			$message=imap_base64($message);
			break;
		case 4:
			$message = imap_qprint($message);
			break;
	}
	return $message;
}

function conectToDatabase() {
	global $db;
	
	$host 	= "localhost";
	$user 	= "root";
	$pass 	= "Elc4N742!";
	$db 	= "gruposervillantas";
	$port 	= 3306;
	$type	= 20;
	$debug  = 0;
	//$dbsocket='/home/mysql/mysql.sock';
	//$link = mysqli_connect($host, $user, $pass, $db, $port,$dbsocket);
	$link = $db;
	
	return $link;
}


function ValidToDatabase($data,$link,$xml) {
	$xml = simplexml_load_string($xml);
	//$xml = simplexml_load_file('test.xml');
	$ns = $xml->getNamespaces(true);
	$xml->registerXPathNamespace('c', $ns['cfdi']);
	$xml->registerXPathNamespace('t', $ns['tfd']);


	$errorproducto=0;
	$msg='';
	foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto){
		$sql="SELECT stockid FROM stockmaster WHERE stockid = '{$Concepto['noIdentificacion']}'";
		$result = mysqli_query($link, $sql);
		if($row = mysqli_fetch_array($result)) {
				
		} else {
			$errorproducto=1;
			$msg=$msg.'@El Codigo de producto:'.$Concepto['noIdentificacion'].' No fue encontrado<br>';
		}


	}
	$array["Error"]=$errorproducto;
	$array["DescripcionError"]=$msg;

	return $array;

}

function loadPurchoOrderToDatabase(&$data,$link,$almacen,$supplierid,$userid) {

	$AlmacenStock=$almacen;
	$result = mysqli_query($link, "SELECT tagref FROM locations WHERE loccode = '{$AlmacenStock}'");
	if($row = mysqli_fetch_array($result)) {
		$tagref = $row['tagref'];
	}
	
	//$tagref=1;
	$currcode_='MXN';
	$sql = "INSERT INTO purchorders (
                        supplierno,
						contact,
                        orddate,
                        rate,
                        initiator,
                        requisitionno,
                        intostocklocation,
                        deladd1,
                        deladd2,
                        deladd3,
                        deladd4,
                        deladd5,
                        deladd6,
                        version,
                        realorderno,
                        revised,
                        status,
                        stat_comment,
                        deliverydate,
                        tagref

                        )
                VALUES(
                        '" . $supplierid. "',
                        '".$data['folio']."',
                   	    now(),
                        '',
                        '".$userid."',
                        0,
                        '" . $AlmacenStock . "',
                        '" . $DelAdd1 . "',
                        '" . $DelAdd2 . "',
                        '" . $DelAdd3 . "',
                        '" . $DelAdd4 . "',
                        '" . $DelAdd5 . "',
                        '" . $DelAdd6 . "',
                        '0',
                        0,
                        now(),";
	$sql.="'" . 'Pending'. "',";
	$sql.="		'Compra desde email',
                        'now()',
                        ".$tagref."

                )";

	if($debug) {
		echo '<pre>' . $sql;
	} else {
		mysqli_query($link, $sql);
	}
	//echo '<pre>sql:'.$sql;
	$orderNoCompra= mysqli_insert_id($link);
	return $orderNoCompra;
}


function loadPurchoOrderDetailsToDatabase($link,$xml,$OrderNoCompra) {

	$xml = simplexml_load_string($xml);
	$ns = $xml->getNamespaces(true);
	$xml->registerXPathNamespace('c', $ns['cfdi']);
	$xml->registerXPathNamespace('t', $ns['tfd']);


	$errorproducto=0;
	$msg='';
	foreach ($xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto){
		//Inserta registro en detalle de OC
		$sqlCAT = "SELECT stockcategory.stockact,stockmaster.mbflag
			FROM stockmaster,stockcategory
			WHERE stockcategory.categoryid = stockmaster.categoryid
                            AND stockmaster.stockid='" .  $Concepto['noIdentificacion'] . "'";
		$result = mysqli_query($link, $sqlCAT);
		if($row = mysqli_fetch_array($result)) {
			$CompraStockidglcode = $row['stockact'];
			$mbflag = $row['mbflag'];
		}

		$sql = "INSERT INTO purchorderdetails (
                        orderno,
                        itemcode,
                        deliverydate,
                        itemdescription,
                        glcode,
                        unitprice,
                        quantityord,
                        shiptref,
                        jobref,
                        itemno,
                        uom,
                        suppliers_partno,
                        subtotal_amount
                        )
                    VALUES (
                        " . $OrderNoCompra . ",
                        '" .  $Concepto['noIdentificacion'] . "',
                        now(),
                        '" .  $Concepto['descripcion'] . "',
                        '" . $CompraStockidglcode . "',
                        '" . ($Concepto['valorUnitario']). "',
                        " . ($Concepto['cantidad']). ",
                        0,
                        0,
                        '" .  $Concepto['descripcion'] . "',
                        '" .  $Concepto['unidad'] . "',
                        '" .  $Concepto['noIdentificacion'] . "',
                        '" . ($Concepto['cantidad']*$Concepto['valorUnitario']) . "'
                        )";

		if($debug) {
			echo '<pre>' . $sql;
		} else {
			mysqli_query($link, $sql);
		}


	}
	return 1;
}

?>