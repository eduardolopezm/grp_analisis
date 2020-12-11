<?php
include_once realpath(dirname(__FILE__)) . '/SatValidator.interface.php';
include_once realpath(dirname(__FILE__)) . '/../SatXmlError.class.php';
include_once realpath(dirname(__FILE__)) . '/../models/General.class.php';
 
/**
 * Class responsible of validating general stuff of the document (Comprobante)
 * @version 1.0
 */
class SatGeneralValidator extends SatValidator {
	
	const MAX_DATE_DAYS = 15;
	const FIEL_DAYS = 3;
	const ERROR_TYPE = 'PCIG';
	const UMBRAL_ERROR_CANTIDADES = 1;
	const TASA_IVA = 0.16;
	const UTF_8 = 1;
	const ASCII = 2;
	const ISO_8859_1 = 3;

	// Model class
	private $general;
	
	private $namespaces = array(
		Comprobante::CFD_VERSION_20 => 'http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv2.xsd',
		Comprobante::CFD_VERSION_22 => 'http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd',
		Comprobante::CFD_VERSION_30 => 'http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv3.xsd',
		Comprobante::CFD_VERSION_32 => 'http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd'
	);
	
	//private $numerico = 
	
	public function __construct($db) {
		parent::__construct();
		$this->general = new General($db);		
	}
	
	public function validate() {
		
		if ($this->comprobante == null) {
			$error = $this->buildError();
			$error->setCode('0');
			$error->setMessage("El XML no pudo se analizado porque esta mal formado");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			return false;
		}
		
		$this->cleanErrors();
		
		$cert = $this->comprobante->getNoCertificado();
		$date = $this->comprobante->getFecha();
		$daysdiff = $this->getDaysDiff(date('Y-m-d'), $date);
		if ($daysdiff > self::MAX_DATE_DAYS) {
			$error = $this->buildError();
			$error->setCode('1');
			$error->setMessage("La fecha de emision '$date' es mayor a '" . self::MAX_DATE_DAYS . "' dias");
			$error->setLevel(SatXmlError::WARNING);
			$error->setNode('Comprobante');
			$error->setAttribute('fecha');
			$error->setValue($date);
		}
		
		// Subtotal + iva + descuento validation
		$impuestos = $this->comprobante->getImpuestos();
		$total = $this->comprobante->getTotal();
		$subtotal = $this->comprobante->getSubTotal();
		$discount = $this->comprobante->getDescuento();
		$tax = $impuestos->getTotalImpuestosTrasladadosReal();
		$taxR = $impuestos->getTotalImpuestosRetenidosReal();
		$totalTemp = $subtotal + $tax - $taxR - $discount;
		$diff = $total - $totalTemp;
		$diff = abs($diff);
		if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
			$error = $this->buildError();
			$error->setCode('2');
			$error->setMessage("La suma del subtotal + totalImpuestosTrasladados - totalImpuestosRetenidos - descuento es '" . $totalTemp . "' y no es igual al total '" . $total."' ");
			$error->setLevel(SatXmlError::WARNING);
			$error->setNode('Comprobante');
			$error->setAttribute("total");
			$error->setValue($total);
		}
		
		// Conceptos validation
		$itemsTotal = 0;
		foreach ($this->comprobante->getConceptos() as $concepto) {
			$cantidad = $concepto->getCantidad();
			$valorUnitario = $concepto->getValorUnitario();
			$itemsTotal += $cantidad * $valorUnitario; 
		}
		$diff = $subtotal - $itemsTotal;
		$diff = abs($diff);
		if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
			$error = $this->buildError();
			$error->setCode('3');
			$error->setMessage("La suma de la cantidad por el valor unitario de los conceptos es '" . $itemsTotal. "' y no corresponde con el subtotal '" . $subtotal."' ");
			$error->setLevel(SatXmlError::WARNING);
			$error->setNode('Conceptos');
			$error->setAttribute("importes");
			$error->setValue("$itemsTotal");
		}
		
		// totalImpuestosTrasladados = Subtotal - descuento validation
		$taxTemp = ($subtotal - $discount) * self::TASA_IVA;
		$diff = $tax - $taxTemp;
		$diff = abs($diff);
		if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
			$error = $this->buildError();
			$error->setCode('4');
			$error->setMessage("El calculo del totalImpuestosTrasladados = '$tax' No corresponde con el calculo (subtotal - descuento) * tasa = '$taxTemp'");
			$error->setLevel(SatXmlError::WARNING);
			$error->setNode('Impuestos');
			$error->setAttribute("totalImpuestosTrasladados");
			$error->setValue($tax);
		}
		
		// Receptor validacion
		$receptor = $this->comprobante->getReceptor();
		$domicilio = $receptor->getDomicilio();
		$rfc = $receptor->getRfc();
		$nombre = $receptor->getNombre();
		$calle = $domicilio->getCalle();
		$colonia = $domicilio->getColonia();
		$municipio = $domicilio->getMunicipio();
		$estado = $domicilio->getEstado();
		$cp = $domicilio->getCodigoPostal();

		if (!$this->general->hasRfc($rfc)) {
			$error = $this->buildError();
			$error->setCode('5');
			$error->setMessage("El RFC '".$rfc."' del nodo Receptor No concuerda con el Esperado");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			$error->setAttribute("RFC");
			$error->setNode('Receptor');
			$error->setValue($rfc);
		}
		
		if (!$this->general->hasName($nombre)) {
			$error = $this->buildError();
			$error->setCode('6');
			$error->setMessage("El nombre '".$nombre."' del nodo Receptor No concuerda con el Esperado");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("Nombre");
			$error->setNode('Receptor');
			$error->setValue($nombre);
		}
		
		if (!$this->general->hasStreet($calle)) {
			$error = $this->buildError();
			$error->setCode('7');
			$error->setMessage("La Calle '".$calle."' del nodo Receptor No concuerda con la Esperada");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("Calle");
			$error->setNode('Receptor');
			$error->setValue($calle);
		}
		
		if (!$this->general->hasColony($colonia)) {
			$error = $this->buildError();
			$error->setCode('8');
			$error->setMessage("La Colonia '".$colonia."' del nodo Receptor No concuerda con la Esperada");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("Colonia");
			$error->setNode('Receptor');
			$error->setValue($colonia);
		}
		
		if (!$this->general->hasMunicipio($municipio)) {
			$error = $this->buildError();
			$error->setCode('9');
			$error->setMessage("El Municipio '".$municipio."' del nodo Receptor No concuerda con el Esperado");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("Municipio");
			$error->setNode('Receptor');
			$error->setValue($municipio);
		}
		
		if (!$this->general->hasState($estado)) {
			$error = $this->buildError();
			$error->setCode('10');
			$error->setMessage("El Estado '".$estado."' del nodo Receptor No concuerda con el Esperado");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("Estado");
			$error->setNode('Receptor');
			$error->setValue($estado);
		}
		
		if (!$this->general->hasCp($cp)) {
			$error = $this->buildError();
			$error->setCode('11');
			$error->setMessage("El Codigo Postal '".$cp."' del nodo Receptor No concuerda con el Esperado");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("CP");
			$error->setNode('Receptor');
			$error->setValue($cp);
		}
		
		// Validar certificado 
		$emisor = $this->comprobante->getEmisor();
		$rfcEmisor = $emisor->getRfc();
		if (!$this->general->hasCertificado($cert, $rfcEmisor)) {
			$error = $this->buildError();
			$error->setCode('12');
			$error->setMessage("El Certificado CSD '".$cert."' no se encuentra registrado");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("noCertificado");
			$error->setNode('Comprobante');
			$error->setValue($cert);
		}
		
		// Validar impuestos locales total 
		$impuestosLocales = $this->comprobante->getImpuestosLocales();
		$taxTras = $impuestos->getTotalImpuestosTrasladadosReal();
		$taxRet = $impuestos->getTotalImpuestosRetenidosReal();	
		$taxLocalRet = $impuestosLocales->getTotalImpuestosRetenidos();
		$taxLocalTras = $impuestosLocales->getTotalImpuestosTrasladados();
		$totalTemp = $subtotal + $taxTras - $taxRet - $discount + $taxLocalTras - $taxLocalRet;
		$diff = $total - $totalTemp;
		$diff = abs($diff);
		if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
			$error = $this->buildError();
			$error->setCode('13');
			$error->setMessage("La suma del subtotal + totalImpuestosTrasladados - descuento - totalImpuestosRetenidos - TotaldeRetenciones + TotaldeTraslados es '" . $totalTemp . "' y no es igual al total '" . $total."' ");
			$error->setLevel(SatXmlError::WARNING);
			$error->setNode('Comprobante');
			$error->setAttribute("total");
			$error->setValue($total);
		}
			
		// Validar impuestos locales
		foreach ($impuestosLocales->getRetenciones() as $retencion) {
			$impLocRetenido = $retencion->getImpuesto();
			$importe = $retencion->getImporte();
			$millarVariants5 = $this->general->get5MillarVariants();
			$millarVariants2 = $this->general->get2MillarVariants();
			$millarVariants1 = $this->general->get1MillarVariants();
			$icicVariants = $this->general->getICICVariants();
			$obsVariants = $this->general->getOBSVariants();
			$uneteVariants = $this->general->getUNETEVariants();
			if (in_array($impLocRetenido, $millarVariants5)) {
				$importeMillarTemp = $subtotal * $this->general->get5MillarPercentage();
				$diff = $importe - $importeMillarTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('14');
					$error->setMessage("El total del impuesto 5 al millar es '" . $importe . "' y debe ser '" . $importeMillarTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeMillarTemp);
				}
			}
			if (in_array($impLocRetenido, $millarVariants2)) {
				$importeMillarTemp = $subtotal * $this->general->get2MillarPercentage();
				$diff = $importe - $importeMillarTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('15');
					$error->setMessage("El total del impuesto 2 al millar es '" . $importe . "' y debe ser '" . $importeMillarTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeMillarTemp);
				}
			}
			if (in_array($impLocRetenido, $millarVariants1)) {
				$importeMillarTemp = $subtotal * $this->general->get1MillarPercentage();
				$diff = $importe - $importeMillarTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('16');
					$error->setMessage("El total del impuesto 1 al millar es '" . $importe . "' y debe ser '" . $importeMillarTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeMillarTemp);
				}
			}
			if (in_array($impLocRetenido, $icicVariants)) {
				$importeTemp = $subtotal * $this->general->getICICPercentage();
				$diff = $importe - $importeTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('17');
					$error->setMessage("El total del impuesto ICIC es '" . $importe . "' y debe ser '" . $importeTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeTemp);
				}
			}
			if (in_array($impLocRetenido, $obsVariants)) {
				$importeTemp = $subtotal * $this->general->getOBSPercentage();
				$diff = $importe - $importeTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('18');
					$error->setMessage("El total del impuesto OBS es '" . $importe . "' y debe ser '" . $importeTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeTemp);
				}
			}
			if (in_array($impLocRetenido, $uneteVariants)) {
				$importeTemp = $subtotal * $this->general->getUNETEPercentage();
				$diff = $importe - $importeTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('19');
					$error->setMessage("El total del impuesto UNETE es '" . $importe . "' y debe ser '" . $importeTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeTemp);
				}
			}
		}
		
		// Validar certificado fecha y rfc
		$docDate = $this->comprobante->getFecha();
		if ($this->general->hasCertificado($cert, $rfcEmisor)) {
			if ($this->general->getCertificado($cert, $rfcEmisor, $docDate) == null) {
				$error = $this->buildError();
				$error->setCode('20');
				$error->setMessage("El certificado en el momento de emitir el documento no se encontraba vigente");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("noCertificado");
				$error->setNode('Comprobante');
				$error->setValue($cert);
			}
		}
		
		// Validar nameSpace
		$version = $this->comprobante->getVersion();
		$namespace = $this->namespaces[$version];
		if (preg_match("@$namespace@i", $this->comprobante->getNameSpace()) == false) {
			$valornamespace = $this->comprobante->getNameSpace();
			$error = $this->buildError();
			$error->setCode('21');
			$error->setMessage("El namespace es incorrecto: '" . $this->comprobante->getNameSpace() . "' ");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("xsi:schemaLocation");
			$error->setNode('Comprobante');
			$error->setValue($valornamespace);
		}
		
		// Validar encoding
		$rutaXml = $this->comprobante->getRutaXml();
		if ($this->codification($rutaXml) != self::UTF_8) {
			$error = $this->buildError();
			$error->setCode('22');
			$error->setMessage("La codificacion del XML No es la esperada, Debe ser UTF-8");
			$error->setLevel(SatXmlError::WARNING);
		}
		
		// Validar certificado fiel
		$nowDate = date('Y-m-d h:i:s');
		if ($this->getDaysDiff($nowDate, $docDate) >= self::FIEL_DAYS) {
			$emisor = $this->comprobante->getEmisor();
			if ($this->general->getCertificadoByRfc($cert, $emisor->getRfc()) == null) {
				$error = $this->buildError();
				$error->setCode('23');
				$error->setMessage("El certificado es del tipo FIEL");
				$error->setLevel(SatXmlError::WARNING);
				$error->setAttribute("noCertificado");
				$error->setNode('Comprobante');
				$error->setValue($cert);	
			}
		}
		
		foreach ($this->comprobante->getConceptos() as $concepto) {
			//echo'cantidad'. $concepto->getCantidad();//
			if (is_numeric(trim($concepto->getCantidad())) == false) {
				$error = $this->buildError();
				$error->setCode('24');
				$error->setMessage("la cantidad no es formato numerico");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("cantidad");
				$error->setNode("Concepto");
				$error->setValue($concepto->getCantidad());
			}
			
			if (is_numeric(trim($concepto->getValorUnitario())) == false) {
				$error = $this->buildError();
				$error->setCode('25');
				$error->setMessage("El valor unitario no es formato numerico");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("valorUnitario");
				$error->setNode("Concepto");
				$error->setValue($concepto->getValorUnitario());
			}
			
			if (is_numeric(trim($concepto->getImporte())) == false) {
				$error = $this->buildError();
				$error->setCode('26');
				$error->setMessage("El importe no es formato numerico");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("importe");
				$error->setNode("Concepto");
				$error->setValue($concepto->getImporte());
			}	
		}
		
		if (is_numeric(trim($this->comprobante->getTotal())) == false) {
			$error = $this->buildError();
			$error->setCode('27');
			$error->setMessage("El total no es formato numerico");
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("total");
			$error->setNode("Comprobante");
			$error->setValue($this->comprobante->getTotal());
		}
		
		if (is_numeric(trim($this->comprobante->getSubTotal())) == false) {
			$error = $this->buildError();
			$error->setCode('28');
			$error->setMessage("El Subtotal no es formato numerico");
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("subTotal");
			$error->setNode("Comprobante");
			$error->setValue($this->comprobante->getSubTotal());
		}
		
		// Validar otros impuestos locales
		foreach ($impuestosLocales->getRetenciones() as $retencion) {
			$impLocRetenido = $retencion->getImpuesto();
			$importe = $retencion->getImporte();
			$variants = $this->general->getVariants();
			if (in_array($impLocRetenido, $variants) == false) {
				$importeTemp = $subtotal * $retencion->getTasa();
				$diff = $importe - $importeTemp;
				$diff = abs($diff);
				if ($diff > self::UMBRAL_ERROR_CANTIDADES) {
					$error = $this->buildError();
					$error->setCode('29');
					$error->setMessage("El total del impuesto " . $impLocRetenido . " es '" . $importe . "' y debe ser '" . $importeTemp . "' ");
					$error->setLevel(SatXmlError::WARNING);
					$error->setNode('ImpuestosLocales');
					$error->setAttribute("importe");
					$error->setValue($importeTemp);
				}
			}
		}
		
		// Validar esquema impuestos locales
		$impuestosLocales = $this->comprobante->getImpuestosLocales();
		// Verificar si tiene impuestos locales
		if (count($impuestosLocales->getRetenciones()) > 0 || count($impuestosLocales->getTraslados()) > 0) {
			if (preg_match("@http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd@i", $this->comprobante->getNameSpace()) == false
					&& preg_match("@http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd@i", $impuestosLocales->getNameSpace()) == false) {
				$error = $this->buildError();
				$error->setCode('30');
				$error->setMessage("El esquema de impuestos locales es invalido");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("xsi:schemaLocation");
				$error->setNode('Comprobante');
				$error->setValue($this->comprobante->getNameSpace());
			}
		}
		
		// Validar importes de impuestos locales Retenidos
		foreach ($impuestosLocales->getRetenciones() as $retencionesLocales) {
			$tasaRet = $retencionesLocales->getTasa();
			$impRet = $retencionesLocales->getImporte();
			if (is_numeric(trim($tasaRet)) == false) {
					$error = $this->buildError();
					$error->setCode('31');
					$error->setMessage("El Impuesto no es formato numerico");
					$error->setLevel(SatXmlError::ERROR);
					$error->setAttribute("TasadeRetencion");
					$error->setNode("RetencionesLocales");
					$error->setValue($tasaRet);
			}
			if (is_numeric(trim($impRet)) == false) {
				$error = $this->buildError();
				$error->setCode('32');
				$error->setMessage("El Importe no es formato numerico");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("Importe");
				$error->setNode("RetencionesLocales");
				$error->setValue($impRet);
			}	
		}
		
		// Validar importes de impuestos locales Trasladados 
		foreach ($impuestosLocales->getTraslados() as $trasladosLocales) {
			$tasaTras = $trasladosLocales->getTasa();
			$impTras = $trasladosLocales->getImporte();
			if (is_numeric(trim($tasaTras)) == false) {
				$error = $this->buildError();
				$error->setCode('33');
				$error->setMessage("El Impuesto no es formato numerico");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("TasadeTraslado");
				$error->setNode("TrasladosLocales");
				$error->setValue($tasaTras);
			}
			if (is_numeric(trim($impTras)) == false) {
				$error = $this->buildError();
				$error->setCode('34');
				$error->setMessage("El Importe no es formato numerico");
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("Importe");
				$error->setNode("TrasladosLocales");
				$error->setValue($impTras);
			}
		}
		
		// Validar que tenga definido el nodo totalImpuestosTrasladados
		if (count($impuestos->getTraslados()) > 0) {
			if ($impuestos->getTotalImpuestosTrasladados() == null) {
				$impuestos->setTotalImpuestosTrasladados(0);
				$error = $this->buildError();
				$error->setCode('35');
				$error->setMessage("El nodo totalImpuestosTrasladados no esta definido en el documento");
				$error->setLevel(SatXmlError::WARNING);
				$error->setAttribute("totalImpuestosTrasladados");
				$error->setNode("Impuestos");
				$error->setValue(0);
			}
		}
		
		// Validar que tenga definido el nodo totalImpuestosRetenidos
		if (count($impuestos->getRetenciones()) > 0) {
			if ($impuestos->getTotalImpuestosRetenidos() == null) {
				$impuestos->setTotalImpuestosRetenidos(0);
				$error = $this->buildError();
				$error->setCode('36');
				$error->setMessage("El nodo totalImpuestosRetenidos no esta definido en el documento");
				$error->setLevel(SatXmlError::WARNING);
				$error->setAttribute("totalImpuestosRetenidos");
				$error->setNode("Impuestos");
				$error->setValue(0);
			}
		}
		
		return ($this->hasErrors() == false);
	}
	
	private function buildError() {
		$error = new SatXmlError();
		$error->setType(self::ERROR_TYPE);
		$error->setClass(get_class($this));
		if ($this->comprobante != null) {
			$error->setVersion($this->comprobante->getVersion());
		}
		$this->addError($error);
		return $error;
	} 
	
	private function getDaysDiff($date1, $date2) {
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$datediff = $date1 - $date2;
		return floor($datediff / (60 * 60 * 24));
	}
	
	private function codification($filename) {
		$texto = file_get_contents($filename);
		$c = 0;
		$ascii = true;
		for ($i = 0; $i < strlen($texto); $i++) {
			$byte = ord($texto[$i]);
			if ($c > 0) {
				if (($byte>>6) != 0x2) {
					return self::ISO_8859_1;
				} else {
					$c--;
				}
			} elseif ($byte&0x80) {
				$ascii = false;
				if (($byte >> 5) == 0x6) {
					$c = 1;
				} elseif (($byte >> 4) == 0xE) {
					$c = 2;
				} elseif (($byte >> 3) == 0x1E) {
					$c = 3;
				} else {
					return self::ISO_8859_1;
				}
			}
		}
		return ($ascii) ? self::ASCII : self::UTF_8;
	}
}
?>