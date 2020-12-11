<?php

include_once realpath(dirname(__FILE__)) . '/../dto/Comprobante.class.php';
class ComprobanteFactory {

	public static $parametros = array(
		'version' => 'version',
		'sello'   => 'sello',
		'certificado' => 'certificado',
		'fecha' => 'fecha',
		'noCertificado' => 'noCertificado',
		'total' => 'total',
		'subTotal' => 'subTotal',
		'descuento' => 'descuento',
		'Moneda' => 'Moneda',
		'serie' =>  'serie',
		'folio' => 'folio',
		'nombre' => 'nombre',
		'rfc' => 'rfc',
		'formaDePago' => 'formaDePago',
		'TipoCambio' => 'TipoCambio',
		'tipoDeComprobante' => 'tipoDeComprobante',
		'NumCtaPago' => 'NumCtaPago',
		'LugarExpedicion' => 'LugarExpedicion',
		'metodoDePago' => 'metodoDePago',
		'anoAprobacion' => 'anoAprobacion',
		'noAprobacion' => 'noAprobacion',
		'calle' => 'calle',
		'colonia' => 'colonia',
		'municipio' => 'municipio',
		'estado' => 'estado',
		'codigoPostal' => 'codigoPostal',
		'pais' => 'pais',
		'localidad' => 'localidad',
		'referencia' => 'referencia',
		'noExterior' => 'noExterior',
		'noInterior' => 'noInterior',
		'noCertificadoSAT' => 'noCertificadoSAT',
		'FechaTimbrado' => 'FechaTimbrado',
		'selloSAT' => 'selloSAT',
		'selloCFD' => 'selloCFD',
		'totalImpuestosTrasladados' => 'totalImpuestosTrasladados',
		'totalImpuestosRetenidos' => 'totalImpuestosRetenidos',
		'impuesto' => 'impuesto',
		'importe' => 'importe',
		'tasa' => 'tasa',
		'cantidad' => 'cantidad',
		'unidad' => 'unidad',
		'noIdentificacion' => 'noIdentificacion',
		'descripcion' => 'descripcion',
		'valorUnitario' => 'valorUnitario',
		'TotaldeTraslados' => 'TotaldeTraslados',
		'TotaldeRetenciones' => 'TotaldeRetenciones',
		'ImpLocRetenido' => 'ImpLocRetenido',
		'TasadeRetencion' => 'TasadeRetencion',
		'ImpLocTraslado' => 'ImpLocTraslado',
		'TasadeTraslado' => 'TasadeTraslado',
		'condicionesDePago' => 'condicionesDePago', // 3.3 concepto
		'numRegIdTrib' => 'numRegIdTrib',			// 3.3 receptor
		'usoCFDI' => 'usoCFDI',						// 3.3 receptor
		'base' => 'base',					// 3.3 impuestos
		'tipoFactor' =>'tipoFactor',	 	// 3.3 impuesto
		'claveProdServ' => 'claveProdServ', // 3.3 concepto
		'claveUnidad' 	=> 'claveUnidad',	// 3.3 concepto
		'RegimenFiscal' => 'RegimenFiscal',	// 3.3 emisor
		'UUID' =>	'UUID',
		'confirmacion' =>	'confirmacion',
		'residenciaFiscal' => 'residenciaFiscal', // 3.3 CfdiRelacionados
		'tipoRelacion' => 'tipoRelacion',
		'RfcProvCertif' => 'RfcProvCertif',
		'Importe'=>'Importe' // 3.3 TrasladosLocales
	);

	public static function getComprobante($path, $db, &$manager) {

		$comprobante = null;

		$xmlObj = new DOMDocument("1.0", "UTF-8");
		$loadResult = $xmlObj->load($path);

		$xpath = new DOMXPath($xmlObj);
		$xpath->registerNamespace("cfdi", "http://www.sat.gob.mx/cfd/2");
		$node = $xpath->query("/cfdi:Comprobante");

		if ($node != null) {
			$node = $node->item(0);
		}

		if ($node != null) {
			$comprobante = new Comprobante();
			$comprobante->setVersion($node->getAttribute(self::$parametros['version']));
			if($comprobante->getVersion() == "")
			{
				self::$parametros = array_map(function($valor){
					switch ($valor) {
						case 'tasa':
							$valor = "TasaOCuota";
							break;
						case 'formaDePago':
							$valor = "FormaPago";
							break;
						case "metodoDePago":
							$valor = "MetodoPago";
							break;
						case "TotaldeTraslados":
							$valor = "TotalImpuestosTrasladados";
							break;
						case "TotaldeRetenciones":
							$valor = "TotalImpuestosRetenidos";
							break;
						default:
							$valor = ucfirst($valor);
							break;
					}
					return $valor;
				}, self::$parametros);

			}
			$comprobante->setVersion($node->getAttribute(self::$parametros['version']));
			$sello = $node->getAttribute(self::$parametros["sello"]);
			$sello = preg_replace("/[ \r\n\t]/", "", $sello);
			$comprobante->setSello($sello);

			$certificado = $node->getAttribute(self::$parametros["certificado"]);
			$certificado = preg_replace("/[ \r\n\t]/", "", $certificado);
			$comprobante->setCertificado($certificado);

			$comprobante->setFecha(str_replace('T', ' ', $node->getAttribute(self::$parametros["fecha"])));
			$comprobante->setNoCertificado($node->getAttribute(self::$parametros["noCertificado"]));
			$comprobante->setTotal($node->getAttribute(self::$parametros["total"]));
			$comprobante->setSubTotal($node->getAttribute(self::$parametros["subTotal"]));
			$comprobante->setDescuento($node->getAttribute(self::$parametros["descuento"]));
			$comprobante->setMoneda($node->getAttribute(self::$parametros["Moneda"]));
			$comprobante->setSerie($node->getAttribute(self::$parametros["serie"]));
			$comprobante->setFolio($node->getAttribute(self::$parametros["folio"]));
			$comprobante->setFormaDePago($node->getAttribute(self::$parametros["formaDePago"]));
			$comprobante->setTipoCambio($node->getAttribute(self::$parametros["TipoCambio"]));
			$comprobante->setTipoDeComprobante($node->getAttribute(self::$parametros["tipoDeComprobante"]));
			$comprobante->setNumCtaPago($node->getAttribute(self::$parametros["NumCtaPago"]));
			$comprobante->setLugarExpedicion($node->getAttribute(self::$parametros["LugarExpedicion"]));
			$comprobante->setMetodoDePago($node->getAttribute(self::$parametros["metodoDePago"]));
			$comprobante->setAnoAprobacion($node->getAttribute(self::$parametros["anoAprobacion"]));
			$comprobante->setNoAprobacion($node->getAttribute(self::$parametros["noAprobacion"]));

			$comprobante->setCondicionesDePago($node->getAttribute(self::$parametros["condicionesDePago"])); // 3.3
			$comprobante->setConfirmacion($node->getAttribute(self::$parametros["confirmacion"])); // 3.3

			$comprobante->setNameSpace($node->getAttribute("xsi:schemaLocation"));//
			$comprobante->setXmlObject($xmlObj);
			$comprobante->setRutaXml($path);

			// CfdiRelacionados redrogo
			$cfdiRelacionados = $comprobante->getCfdiRelacionados();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:CfdiRelacionados");
			$node = $node->item(0);
			if ($node != null) {
				$cfdiRelacionados->setTipoRelacion($node->getAttribute(self::$parametros["tipoRelacion"])); // 3.3 redrogo

				$items = $xpath->query("/cfdi:Comprobante/cfdi:CfdiRelacionados/cfdi:CfdiRelacionado");
				if ($items != null) {
					$cfdisRelacion = array();
					for ($i = 0; $i < $items->length; $i++) {
						$cfdeRelacion = $items->item($i);
						array_push($cfdisRelacion, array(
							'uuid' => $cfdeRelacion->getAttribute(self::$parametros["UUID"])
						));
					}
				}
				$item = $items->item(0);
				$comprobante->agregarCfdiRelacionados(
					$item->getAttribute(self::$parametros["tipoRelacion"]),
					$cfdisRelacion // 3.3
				);
			}


			// Emisor
			$emisor = $comprobante->getEmisor();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Emisor");
			$node = $node->item(0);
			if ($node != null) {
				$emisor->setRfc($node->getAttribute(self::$parametros["rfc"]));
				$emisor->setNombre(utf8_decode($node->getAttribute(self::$parametros["nombre"]))); // redrogo
				$emisor->setRegimenFiscal(utf8_decode($node->getAttribute(self::$parametros["RegimenFiscal"]))); // redrogo
			}
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal");
			$node = $node->item(0);
			if ($node != null) {
				$domicilio = $emisor->getDomicilioFiscal();
				$domicilio->setCalle(utf8_decode($node->getAttribute(self::$parametros["calle"])));
				$domicilio->setColonia(utf8_decode($node->getAttribute(self::$parametros["colonia"])));
				$domicilio->setMunicipio(utf8_decode($node->getAttribute(self::$parametros["municipio"])));
				$domicilio->setEstado(utf8_decode($node->getAttribute(self::$parametros["estado"])));
				$domicilio->setCodigoPostal($node->getAttribute(self::$parametros["codigoPostal"]));
				$domicilio->setPais(utf8_decode($node->getAttribute(self::$parametros["pais"])));
				$domicilio->setLocalidad(utf8_decode($node->getAttribute(self::$parametros["localidad"])));
				$domicilio->setReferencia(utf8_decode($node->getAttribute(self::$parametros["referencia"])));
				$domicilio->setNoExterior(utf8_decode($node->getAttribute(self::$parametros["noExterior"])));
				$domicilio->setNoInterior(utf8_decode($node->getAttribute(self::$parametros["noInterior"])));
			}
			// Emisor Expedido En
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Emisor/cfdi:ExpedidoEn");
			$node = $node->item(0);
			if ($node != null) {
				$emisor = $comprobante->getEmisor();
				$expedidoEn = $emisor->getExpedidoEn();
				$expedidoEn->setCalle(utf8_decode($node->getAttribute(self::$parametros["calle"])));
				$expedidoEn->setColonia(utf8_decode($node->getAttribute(self::$parametros["colonia"])));
				$expedidoEn->setMunicipio(utf8_decode($node->getAttribute(self::$parametros["municipio"])));
				$expedidoEn->setEstado(utf8_decode($node->getAttribute(self::$parametros["estado"])));
				$expedidoEn->setCodigoPostal($node->getAttribute(self::$parametros["codigoPostal"]));
				$expedidoEn->setPais(utf8_decode($node->getAttribute(self::$parametros["pais"])));
				$expedidoEn->setLocalidad(utf8_decode($node->getAttribute(self::$parametros["localidad"])));
				$expedidoEn->setReferencia(utf8_decode($node->getAttribute(self::$parametros["referencia"])));
				$expedidoEn->setNoExterior(utf8_decode($node->getAttribute(self::$parametros["noExterior"])));
				$expedidoEn->setNoInterior(utf8_decode($node->getAttribute(self::$parametros["noInterior"])));
			}
			
			// Receptor
			$receptor = $comprobante->getReceptor();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Receptor");
			$node = $node->item(0);
			if ($node != null) {
				$receptor->setRfc($node->getAttribute(self::$parametros["rfc"]));
				$receptor->setNombre(utf8_decode($node->getAttribute(self::$parametros["nombre"])));
				$receptor->setNombre(utf8_decode($node->getAttribute(self::$parametros["residenciaFiscal"]))); // 3.3
				$receptor->setNumRegIdTrib($node->getAttribute(self::$parametros["numRegIdTrib"])); // 3.3
				$receptor->setUsoCFDI($node->getAttribute(self::$parametros["usoCFDI"])); // 3.3

			}
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Receptor/cfdi:Domicilio");
			$node = $node->item(0);
			if ($node != null) {
				$domicilio = $receptor->getDomicilio();
				$domicilio->setCalle(utf8_decode($node->getAttribute(self::$parametros["calle"])));
				$domicilio->setColonia(utf8_decode($node->getAttribute(self::$parametros["colonia"])));
				$domicilio->setMunicipio(utf8_decode($node->getAttribute(self::$parametros["municipio"])));
				$domicilio->setEstado(utf8_decode($node->getAttribute(self::$parametros["estado"])));
				$domicilio->setCodigoPostal($node->getAttribute(self::$parametros["codigoPostal"]));
				$domicilio->setPais(utf8_decode($node->getAttribute(self::$parametros["pais"])));
				$domicilio->setLocalidad(utf8_decode($node->getAttribute(self::$parametros["localidad"])));
				$domicilio->setReferencia(utf8_decode($node->getAttribute(self::$parametros["referencia"])));
				$domicilio->setNoExterior(utf8_decode($node->getAttribute(self::$parametros["noExterior"])));
				$domicilio->setNoInterior(utf8_decode($node->getAttribute(self::$parametros["noInterior"])));
				$domicilio->setNoInterior(utf8_decode($node->getAttribute(self::$parametros["numRegIdTrib"])));

			}
			// Timbre Fiscal
			$xpath->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital");
			$node = $node->item(0);
			if ($node != null) {
				$timbreFiscal = $comprobante->getTimbreFiscal();
				$timbreFiscal->setNoCertificadoSAT($node->getAttribute(self::$parametros["noCertificadoSAT"]));
				$timbreFiscal->setFechaTimbrado(str_replace('T', ' ', $node->getAttribute(self::$parametros["FechaTimbrado"])));
				$timbreFiscal->setUuid($node->getAttribute(self::$parametros["UUID"]));
				$timbreFiscal->setSelloSAT($node->getAttribute(self::$parametros["selloSAT"]));
				$timbreFiscal->setSelloCFD($node->getAttribute(self::$parametros["selloCFD"]));
				$timbreFiscal->setVersion($node->getAttribute(self::$parametros["version"]));
				$timbreFiscal->setRfcProvCertif($node->getAttribute(self::$parametros["RfcProvCertif"]));
				$timbreFiscal->setNameSpace($node->getAttribute("xsi:schemaLocation"));
			}
			// Impuestos
			$impuestos = $comprobante->getImpuestos();
			$node = $xpath->query("/cfdi:Comprobante/cfdi:Impuestos");
			$node = $node->item(0);
			if ($node != null) {
				$impuestos->setTotalImpuestosTrasladados($node->getAttribute(self::$parametros["totalImpuestosTrasladados"]));
				$impuestos->setTotalImpuestosRetenidos($node->getAttribute(self::$parametros["totalImpuestosRetenidos"]));
			}
			$items = $xpath->query("/cfdi:Comprobante/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestos->agregarTraslado(
						$item->getAttribute(self::$parametros["impuesto"]),
						$item->getAttribute(self::$parametros["importe"]),
						$item->getAttribute(self::$parametros["tasa"]),
						$item->getAttribute(self::$parametros["base"]),
						$item->getAttribute(self::$parametros["tipoFactor"])
					);
				}
			}
			$items = $xpath->query("/cfdi:Comprobante/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestos->agregarRetencion(
						$item->getAttribute(self::$parametros["impuesto"]),
						$item->getAttribute(self::$parametros["importe"]),
						$item->getAttribute(self::$parametros["tasa"]),
						$item->getAttribute(self::$parametros["base"]),
						$item->getAttribute(self::$parametros["tipoFactor"])

					);
				}
			}
			// Conceptos//////
			$items = $xpath->query("/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					// 3.3
					$itemTrasladados = $xpath->query("/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado");
					$impuestosTrasladadosConcepto = array();
					$impuestosRetenidosConcepto = array();
					if($itemTrasladados != null)
					{
						for ($j=0; $j < $itemTrasladados->length; $j++) { //	redrogo
							$traslado = $itemTrasladados->item($j);
							array_push($impuestosTrasladadosConcepto, array(
								'impuesto' => $traslado->getAttribute(self::$parametros["impuesto"]),
								'importe' => $traslado->getAttribute(self::$parametros["importe"]),
								'tasa' => $traslado->getAttribute(self::$parametros["tasa"]),
								'base' => $traslado->getAttribute(self::$parametros["base"]),
								'tipoFactor' => $traslado->getAttribute(self::$parametros["tipoFactor"])
							));
						}
					}
					$itemRetenidos = $xpath->query("/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto/cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion");

					if($itemRetenidos != null)
					{
						for ($j=0; $j < $itemRetenidos->length; $j++) { //	redrogo
							$retencion = $itemRetenidos->item($j);
							array_push($impuestosRetenidosConcepto, array(
								'impuesto' => $retencion->getAttribute(self::$parametros["impuesto"]),
								'importe' => $retencion->getAttribute(self::$parametros["importe"]),
								'tasa' => $retencion->getAttribute(self::$parametros["tasa"]),
								'base' => $retencion->getAttribute(self::$parametros["base"]),
								'tipoFactor' => $retencion->getAttribute(self::$parametros["tipoFactor"])
							));
						}
					}
					// termina 3.3
					$item = $items->item($i);
					$comprobante->agregarConcepto(
						$item->getAttribute(self::$parametros["cantidad"]),
						$item->getAttribute(self::$parametros["unidad"]),
						$item->getAttribute(self::$parametros["noIdentificacion"]),
						utf8_decode($item->getAttribute(self::$parametros["descripcion"])),
						$item->getAttribute(self::$parametros["valorUnitario"]),
						$item->getAttribute(self::$parametros["importe"]),
						$item->getAttribute(self::$parametros["claveProdServ"]), // 3.3
						$item->getAttribute(self::$parametros["claveUnidad"]),	 // 3.3
						$item->getAttribute(self::$parametros["descuento"]),	 // 3.3
						$impuestosRetenidosConcepto, // 3.3
						$impuestosTrasladadosConcepto // 3.3
					);
				}
			}

			// Impuestos Locales
			$impuestosLocales = $comprobante->getImpuestosLocales();
			$xpath->registerNamespace('implocal', 'http://www.sat.gob.mx/implocal');
			$node = $xpath->query("//implocal:ImpuestosLocales");
			$node = $node->item(0);
			if ($node != null) {
				$impuestosLocales->setTotalImpuestosTrasladados($node->getAttribute(self::$parametros["TotaldeTraslados"]));
				$impuestosLocales->setTotalImpuestosRetenidos($node->getAttribute(self::$parametros["TotaldeRetenciones"]));
				$impuestosLocales->setNameSpace($node->getAttribute("xsi:schemaLocation"));
			}
			$items = $xpath->query("//implocal:ImpuestosLocales/implocal:RetencionesLocales");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestosLocales->agregarRetencion(
						strtolower($item->getAttribute(self::$parametros["ImpLocRetenido"])),
						$item->getAttribute(self::$parametros["Importe"]),
						$item->getAttribute(self::$parametros["TasadeRetencion"])
					);
				}
			}
			$items = $xpath->query("//implocal:ImpuestosLocales/implocal:TrasladosLocales");
			if ($items != null) {
				for ($i = 0; $i < $items->length; $i++) {
					$item = $items->item($i);
					$impuestosLocales->agregarTraslado(
						strtolower($item->getAttribute(self::$parametros["ImpLocTraslado"])),
						$item->getAttribute(self::$parametros["Importe"]),
						$item->getAttribute(self::$parametros["TasadeTraslado"])
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
		} else {

			libxml_use_internal_errors(true);

			// Se tomo fija la version 3.2 que la mas actual al momento, ya que si
			// existe un error en la esctructura del xml, no es posible leerlo
			// por lo tanto no se puede obtener la version del cfdi, para seleccionar
			// el xsd adecuado
			$xsd = realpath(dirname(__FILE__)) . '/../sat/v3.2/xsd/cfdv32.xsd';

			if (!$xmlObj->schemaValidate($xsd)) {

			    $errors = libxml_get_errors();
			    $msgerror = "Error en la estructura del xml: ";
				foreach ($errors as $error) {
			    	$manager->addExternError($error->code, $msgerror . $error->message, 'XML', 'Comprobante', 'CFDI');
			    }

			    libxml_clear_errors();
			}
		}
		return $comprobante;
	}


	/**
	 *
	 * @param String $noCert
	 * @return String
	 */
	public static function buildCertificate($noCert, $updateCert = false, $rfc = 'XAXX010101000', $db = '') {

		$fileId = uniqid();
		$certificado = "";
		$certificatesPath = realpath(dirname(__FILE__)) . "/../sat/certificados/";
	
		$certificatePath = $certificatesPath . $noCert . ".cer";

		// echo "<br><pre>";
		// 	print_r($certificatesPath);
		// echo "</pre><br>";
		if (file_exists($certificatePath) == false) {

			ini_set("default_socket_timeout", 30);
			// el ftp2 dejo de funcionar se cambio por url
			// obtenida de https://portalsat.plataforma.sat.gob.mx/RecuperacionDeCertificados/
			// http://www.sat.gob.mx/fichas_tematicas/fiel/Paginas/descarga_certificados.aspx
			// $url = "ftp://ftp2.sat.gob.mx/Certificados/FEA/";

			//https://rdc.sat.gob.mx/rccf/000010/000004/06/08/59/00001000000406085996.cer

			$url = "https://rdc.sat.gob.mx/rccf/";
			$path = substr($noCert, 0, 18);
			$path = substr_replace($path, "/", 6, 0);
			$path = substr_replace($path, "/", 13, 0);
			$path = substr_replace($path, "/", 16, 0);
			$path = substr_replace($path, "/", 19, 0);
			$path = $url . $path . "/" . $noCert . ".cer";
			//echo "<br><pre>".print_r($path);
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
				if (!is_writable($certificatesPath)) {
		            if (!chmod($certificatesPath, 0666)) {
		                echo "Cannot change the mode of file ($filename)";
		            }
		        }
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

			// Se agrega proceso para actualizar las fechas de vigencia si es
			// necesario //
			if($updateCert) {
				$data_cer = openssl_x509_parse($content);
				$validFrom = date('Y-m-d H:i:s', $data_cer['validFrom_time_t']);
				$validTo = date('Y-m-d H:i:s', $data_cer['validTo_time_t']);
				$model = new General($db);
				$model->updateOrCreateCertificado($noCert, $rfc, $validFrom, $validTo);
			}

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
