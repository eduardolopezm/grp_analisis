<?php


include_once realpath(dirname(__FILE__)) . '/../dto/Comprobante.class.php';

class ComprobanteFactory {
	
	public static function getComprobante($path, $db) {
		
		$comprobante = null;
		
		$xmlObj = new DOMDocument("1.0", "UTF-8");
		$xmlObj->load($path);
		$xpath = new DOMXPath($xmlObj);
		$xpath->registerNamespace("cfdi", "http://www.sat.gob.mx/cfd/2");
		$node = $xpath->query("/cfdi:Comprobante");
		if ($node != null) {
			$node = $node->item(0);
		}
		
		if ($node != null) {
			$comprobante = new Comprobante();
			$comprobante->setVersion($node->getAttribute("version"));
			
			$sello = $node->getAttribute("sello");
			$sello = preg_replace("/[ \r\n\t]/", "", $sello);
			$comprobante->setSello($sello);
			
			$certificado = $node->getAttribute("certificado");
			$certificado = preg_replace("/[ \r\n\t]/", "", $certificado);
			$comprobante->setCertificado($certificado);
			
			$comprobante->setFecha(str_replace('T', ' ', $node->getAttribute("fecha")));
			$comprobante->setNoCertificado($node->getAttribute("noCertificado"));
			$comprobante->setTotal($node->getAttribute("total"));
			$comprobante->setSubTotal($node->getAttribute("subTotal"));
			$comprobante->setDescuento($node->getAttribute("descuento"));
			$comprobante->setMoneda($node->getAttribute("Moneda"));
			$comprobante->setSerie($node->getAttribute("serie"));
			$comprobante->setFolio($node->getAttribute("folio"));
			$comprobante->setFormaDePago($node->getAttribute("formaDePago"));
			$comprobante->setTipoCambio($node->getAttribute("TipoCambio"));
			$comprobante->setTipoDeComprobante($node->getAttribute("tipoDeComprobante"));
			$comprobante->setNumCtaPago($node->getAttribute("NumCtaPago"));
			$comprobante->setLugarExpedicion($node->getAttribute("LugarExpedicion"));
			$comprobante->setMetodoDePago($node->getAttribute("metodoDePago"));
			$comprobante->setAnoAprobacion($node->getAttribute("anoAprobacion"));
			$comprobante->setNoAprobacion($node->getAttribute("noAprobacion"));
			$comprobante->setNameSpace($node->getAttribute("xsi:schemaLocation"));//
			$comprobante->setXmlObject($xmlObj);
			$comprobante->setRutaXml($path);
			
			
			// Emisor
			$emisor = $comprobante->getEmisor();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Emisor");
			$node = $node->item(0);
			if ($node != null) {
				$emisor->setRfc($node->getAttribute("rfc"));
				$emisor->setNombre(utf8_decode($node->getAttribute("nombre")));
			}
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal");
			$node = $node->item(0);
			if ($node != null) {
				$domicilio = $emisor->getDomicilioFiscal();
				$domicilio->setCalle(utf8_decode($node->getAttribute("calle")));
				$domicilio->setColonia(utf8_decode($node->getAttribute("colonia")));
				$domicilio->setMunicipio(utf8_decode($node->getAttribute("municipio")));
				$domicilio->setEstado(utf8_decode($node->getAttribute("estado")));
				$domicilio->setCodigoPostal($node->getAttribute("codigoPostal"));
				$domicilio->setPais(utf8_decode($node->getAttribute("pais")));
				$domicilio->setLocalidad(utf8_decode($node->getAttribute("localidad")));
				$domicilio->setReferencia(utf8_decode($node->getAttribute("referencia")));
				$domicilio->setNoExterior(utf8_decode($node->getAttribute("noExterior")));
				$domicilio->setNoInterior(utf8_decode($node->getAttribute("noInterior")));
			}
			// Emisor Expedido En
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Emisor/cfdi:ExpedidoEn");
			$node = $node->item(0);
			if ($node != null) {
				$emisor = $comprobante->getEmisor();
				$expedidoEn = $emisor->getExpedidoEn();
				$expedidoEn->setCalle(utf8_decode($node->getAttribute("calle")));
				$expedidoEn->setColonia(utf8_decode($node->getAttribute("colonia")));
				$expedidoEn->setMunicipio(utf8_decode($node->getAttribute("municipio")));
				$expedidoEn->setEstado(utf8_decode($node->getAttribute("estado")));
				$expedidoEn->setCodigoPostal($node->getAttribute("codigoPostal"));
				$expedidoEn->setPais(utf8_decode($node->getAttribute("pais")));
				$expedidoEn->setLocalidad(utf8_decode($node->getAttribute("localidad")));
				$expedidoEn->setReferencia(utf8_decode($node->getAttribute("referencia")));
				$expedidoEn->setNoExterior(utf8_decode($node->getAttribute("noExterior")));
				$expedidoEn->setNoInterior(utf8_decode($node->getAttribute("noInterior")));
			}
			// Receptor
			$receptor = $comprobante->getReceptor();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Receptor");
			$node = $node->item(0);
			if ($node != null) {
				$receptor->setRfc($node->getAttribute("rfc"));
				$receptor->setNombre(utf8_decode($node->getAttribute("nombre")));
			}
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Receptor/cfdi:Domicilio");
			$node = $node->item(0);
			if ($node != null) {
				$domicilio = $receptor->getDomicilio();
				$domicilio->setCalle(utf8_decode($node->getAttribute("calle")));
				$domicilio->setColonia(utf8_decode($node->getAttribute("colonia")));
				$domicilio->setMunicipio(utf8_decode($node->getAttribute("municipio")));
				$domicilio->setEstado(utf8_decode($node->getAttribute("estado")));
				$domicilio->setCodigoPostal($node->getAttribute("codigoPostal"));
				$domicilio->setPais(utf8_decode($node->getAttribute("pais")));
				$domicilio->setLocalidad(utf8_decode($node->getAttribute("localidad")));
				$domicilio->setReferencia(utf8_decode($node->getAttribute("referencia")));
				$domicilio->setNoExterior(utf8_decode($node->getAttribute("noExterior")));
				$domicilio->setNoInterior(utf8_decode($node->getAttribute("noInterior")));
			}
			// Timbre Fiscal
			$xpath->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital");
			$node = $node->item(0);
			if ($node != null) {
				$timbreFiscal = $comprobante->getTimbreFiscal();
				$timbreFiscal->setNoCertificadoSAT($node->getAttribute("noCertificadoSAT"));
				$timbreFiscal->setFechaTimbrado(str_replace('T', ' ', $node->getAttribute("FechaTimbrado")));
				$timbreFiscal->setUuid($node->getAttribute("UUID"));
				$timbreFiscal->setSelloSAT($node->getAttribute("selloSAT"));
				$timbreFiscal->setSelloCFD($node->getAttribute("selloCFD"));
				$timbreFiscal->setVersion($node->getAttribute("version"));
				$timbreFiscal->setNameSpace($node->getAttribute("xsi:schemaLocation"));
			}
			// Impuestos
			$impuestos = $comprobante->getImpuestos();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Impuestos");
			$node = $node->item(0);
			if ($node != null) {
				$impuestos->setTotalImpuestosTrasladados($node->getAttribute("totalImpuestosTrasladados"));
				$impuestos->setTotalImpuestosRetenidos($node->getAttribute("totalImpuestosRetenidos"));
			}
			$items = $xpath->query("/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestos->agregarTraslado(
						$item->getAttribute("impuesto"), 
						$item->getAttribute("importe"), 
						$item->getAttribute("tasa")
					);
				}
			}
			$items = $xpath->query("/cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestos->agregarRetencion(
						$item->getAttribute("impuesto"),
						$item->getAttribute("importe"),
						$item->getAttribute("tasa")
					);
				}
			}
			// Conceptos//////
			$items = $xpath->query("/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$comprobante->agregarConcepto(
						$item->getAttribute("cantidad"), 
						$item->getAttribute("unidad"), 
						$item->getAttribute("noIdentificacion"), 
						utf8_decode($item->getAttribute("descripcion")), 
						$item->getAttribute("valorUnitario"), 
						$item->getAttribute("importe")
					);
				}
			}
			// Impuestos Locales
			$impuestosLocales = $comprobante->getImpuestosLocales();
			$xpath->registerNamespace('implocal', 'http://www.sat.gob.mx/implocal');
			$node = $xpath->query("//implocal:ImpuestosLocales");
			$node = $node->item(0);
			if ($node != null) {
				$impuestosLocales->setTotalImpuestosTrasladados($node->getAttribute("TotaldeTraslados"));
				$impuestosLocales->setTotalImpuestosRetenidos($node->getAttribute("TotaldeRetenciones"));
				$impuestosLocales->setNameSpace($node->getAttribute("xsi:schemaLocation"));
			}
			$items = $xpath->query("//implocal:ImpuestosLocales/implocal:RetencionesLocales");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestosLocales->agregarRetencion(
						strtolower($item->getAttribute("ImpLocRetenido")), 
						$item->getAttribute("Importe"),
						$item->getAttribute("TasadeRetencion")
					);
				}
			}
			$items = $xpath->query("//implocal:ImpuestosLocales/implocal:TrasladosLocales");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestosLocales->agregarTraslado(
						strtolower($item->getAttribute("ImpLocTraslado")),
						$item->getAttribute("Importe"),
						$item->getAttribute("TasadeTraslado")
					);
				}
			}
			
			$xsltPath = realpath(dirname(__FILE__)) . "/../sat/v" . $comprobante->getVersion()
			. "/xslt/cadenaoriginal_" . str_replace('.', '_', $comprobante->getVersion()) . ".xslt";
			
			$xslt = new DOMDocument("1.0", "UTF-8");
			$xslt->load($xsltPath);
			$xsltProcessor = new XSLTProcessor();
			$xsltProcessor->importStyleSheet($xslt);
			
			$satStr = trim($xsltProcessor->transformToXML($comprobante->getXmlObject()));
			$comprobante->setCadenaOriginal($satStr);
			
			$certificado = $comprobante->getCertificado();
			if (empty($certificado)) {
				$certificado = self::buildCertificate($comprobante->getNoCertificado());
				if (empty($certificado) == false) {
					$comprobante->setCertificado($certificado);
					$emisor = $comprobante->getEmisor();
					$model = new General($db);
					$model->saveUnknownCertificate($comprobante->getNoCertificado(), $emisor->getRfc());
				}
			}
			
			// Addenda CFE
			$xpath->registerNamespace('cfe', 'http://www.itcomplements.com/cfd/cfe/v1');
			$item = $xpath->query("//cfe:CFE//IMPDAP");
			if ($item != null) {
				if ($item->item(0) != null) {
					$sdoant = "";
					$impdap = $item->item(0)->nodeValue; 
					$item = $xpath->query("//cfe:CFE//SDOANT");
					if ($item != null) {
						$sdoant = $item->item(0)->nodeValue;
					}
					
					$addenda = new AddendaCFE();
					$addenda->setImpdap($impdap);
					$addenda->setSdoant($sdoant);
					$comprobante->setAddenda($addenda);
				}
			}
		}
		
		return $comprobante;
	}
	
	/**
	 * 
	 * @param String $noCert
	 * @return String
	 */
	public static function buildCertificate($noCert) {
		
		$fileId = uniqid();
		$certificado = "";
		$certificatesPath = realpath(dirname(__FILE__)) . "/../sat/certificados/";
		$certificatePath = $certificatesPath . $noCert . ".cer";
		if (file_exists($certificatePath) == false) {
			
			ini_set("default_socket_timeout", 30);
			$url = "ftp://ftp2.sat.gob.mx/Certificados/FEA/";
			$path = substr($noCert, 0, 18);
			$path = substr_replace($path, "/", 6, 0);
			$path = substr_replace($path, "/", 13, 0);
			$path = substr_replace($path, "/", 16, 0);
			$path = substr_replace($path, "/", 19, 0);
			$path = $url . $path . "/" . $noCert . ".cer";
			$content = @file_get_contents($path);
			
			//Se realizan varios intentos de descarga , $ic_max
			if (empty($content) == true) {
				$ic_max = 5;
				for ($ic = 0; $ic <= $ic_max; $ic++) {
					$content = @file_get_contents($path);
					if (empty($content) == false) {
						$ic = $ic_max + 1;
					}
				}	
			}
			
			if (empty($content) == false) {
				$handle = fopen($certificatePath, "w");
				fwrite($handle, $content);
				fclose($handle);
			}
		}
		
		if (file_exists($certificatePath)) {
					
			$command = 'openssl x509 -inform DER -outform PEM -in "' . $certificatesPath . $noCert . '.cer" -pubkey >{path}' . $noCert . '.PEM';
			exec(str_replace('{path}', $certificatesPath . $fileId, $command));
			
			$filename = $certificatesPath . $fileId . $noCert . '.PEM';
			$handle = fopen($filename, "r");
			$content = fread($handle, filesize($filename));
			$content = explode(SatStampValidator::KEY_FOOTER, $content);
			if (count($content) > 1) {
				$content = $content[1];
				$content = str_replace(PHP_EOL, "", $content);
				$content = str_replace(SatStampValidator::CERT_HEADER, "", $content);
				$content = str_replace(SatStampValidator::CERT_FOOTER, "", $content);
				$certificado = $content;
			}
			@unlink($filename);
		}
		
		return $certificado;
	}
}