<?php
include_once realpath(dirname(__FILE__)) . '/SatValidator.interface.php';
include_once realpath(dirname(__FILE__)) . '/../SatXmlError.class.php';
include_once realpath(dirname(__FILE__)) . '/../models/General.class.php';
/**
 * Class responsible of validating documents version 2.X
 * @version 1.0
 */
class SatDocument2xValidator extends SatValidator {
	
	const ERROR_TYPE = 'PCID2';
	const SAT_WS_SUCCESS = "VV";
	const SAT_WS_URL = "https://tramitesdigitales.sat.gob.mx/Sicofi.wsExtValidacionCFD/WsValidacionCFDsExt.asmx?WSDL";
	
	// Model class
	private $general;
	
	public function __construct($db) {
		parent::__construct();
		$this->general = new General($db);
	}
	
	public function validate() {
		
		if ($this->comprobante == null) {
			$error = $this->buildError();
			$error->setCode('0');
			$error->setMessage("Las validaciones 2.X no pudieron ser procesadas");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			return false;
		}
		
		$this->cleanErrors();
		
		// Validaciones aqui...
		//
		// Validar Webservice
		//$this->validateSatWs();
		$NoAprobacion  = $this->comprobante->getNoAprobacion();
		$serie = $this->comprobante->getSerie();
		$folio = $this->comprobante->getFolio();
		$anio = $this->comprobante->getAnoAprobacion();
		$emisor = $this->comprobante->getEmisor();
		$rfc = $emisor->getRfc();
		if ($this->general->getvalidaExistSerieFolio($rfc, $NoAprobacion, $anio, $serie, $folio) == false) {
			$error = $this->buildError();
			$error->setCode('3');
			$error->setMessage("La serie y el folio no se encuentran aprobadas");
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("Serie/Folio");
			$error->setNode('Comprobante');
			$error->setValue($serie . "/" . $folio);
		}
		
		
		return ($this->hasErrors() == false);
	}
	
	private function validateSatWs() {
		
		$emisor = $this->comprobante->getEmisor();
		$noCertificado = $this->comprobante->getNoCertificado();
		$fecha = str_replace(' ', 'T', $this->comprobante->getFecha());
		$anoAprobacion = $this->comprobante->getAnoAprobacion();
		$noAprobacion = $this->comprobante->getNoAprobacion();
		$serie = $this->comprobante->getSerie();
		$folio = $this->comprobante->getFolio();
		$rfc = $emisor->getRfc();
		
		$wsErrors = array(
			'II' => 'Datos del folio y certificado son invalidos',
			'VI' => 'Los datos del folio son validos, pero el certificado es invalido',
			'IV' => 'Los datos del folio son invalidos y el certificado es valido'
		);
		
		try {
			$client = new SoapClient(self::SAT_WS_URL);
			$xml = '<cfd:ColleccionFoliosCfd xsi:schemaLocation="http://www.sat.gob.mx/Asf/Sicofi/ValidacionFoliosCFD/1.0.0 FoliosCFDNuevo.xsd"
				xmlns:cfd="http://www.sat.gob.mx/Asf/Sicofi/ValidacionFoliosCFD/1.0.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
				<cfd:Folio>
				<cfd:Id>1</cfd:Id>
				<cfd:Rfc>' . $rfc . '</cfd:Rfc>
				<cfd:Serie>' . $serie . '</cfd:Serie>
				<cfd:NumeroFolio>' . $folio . '</cfd:NumeroFolio>
				<cfd:NumeroAprobacion>' . $noAprobacion . '</cfd:NumeroAprobacion>
				<cfd:AnioAprobacion>' . $anoAprobacion . '</cfd:AnioAprobacion>
				<cfd:CertificadoNumeroSerie>' . $noCertificado . '</cfd:CertificadoNumeroSerie>
				<cfd:CertificadoFechaEmision>' . $fecha . '.0Z</cfd:CertificadoFechaEmision>
				</cfd:Folio>
				</cfd:ColleccionFoliosCfd>';
			$response = $client->ValidarXmlCFD(array('xml' => $xml));
			$xml = new DOMDocument();
			$xml->loadXML($response->ValidarXmlCFDResult);
			$xpath = new DOMXPath($xml);
			$xpath->registerNamespace("cfd", "http://www.sat.gob.mx/Asf/Sicofi/RespuestaFoliosCFD/1.0.0.0");
			$node = $xpath->query("//cfd:ResultadoValidacion/cfd:ResultadoValidacion");
			if ($node != null) {
				$node = $node->item(0);
				if ($node->nodeValue == self::SAT_WS_SUCCESS) {
					return true;
				} else {
					$error = $this->buildError();
					$error->setCode('1');
					$error->setMessage($wsErrors[$node->nodeValue]);
					$error->setLevel(SatXmlError::ERROR);
					$error->setAttribute("Serie/Folio");
					$error->setNode('Comprobante');
					$error->setValue($serie."/".$folio);
				}
			}
		} catch(Exception $e) {
			$error = $this->buildError();
			$error->setCode('2');
			$error->setMessage("La validaci�n de la serie y folio No se ha podido realizar, Conexi�n al WS del SAT ha fallado.  " . $e->getMessage());
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("Serie/Folio");
			$error->setNode('Comprobante');
			$error->setValue($serie . "/" . $folio);
		}
		return false;
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
}
?>