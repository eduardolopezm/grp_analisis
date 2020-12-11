<?php
include_once realpath(dirname(__FILE__)) . '/SatValidator.interface.php';
include_once realpath(dirname(__FILE__)) . '/../SatXmlError.class.php';
include_once realpath(dirname(__FILE__)) . '/../models/General.class.php';
 
/**
 * Class responsible of validating documents version 3.X
 * @version 1.0
 */
class SatDocument3xValidator extends SatValidator {
	
	const ERROR_TYPE = 'PCID3';
	const MAX_STAMP_DAYS = 3;
	const SAT_WS_SUCCESS = "Vigente|Cancelado";
	const SAT_WS_URL = "https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl";
	
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
			$error->setMessage("Las validaciones 3.X no pudieron ser procesadas");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			return false;
		}
		
		$this->cleanErrors();
		
		// TimbreFiscalDigital node validation
		$timbreFiscal = $this->comprobante->getTimbreFiscal();
		if ($timbreFiscal->getUuid() == null) {
			$error = $this->buildError();
			$error->setCode('1');
			$error->setMessage("El nodo TimbreFiscalDigital no existe");
			$error->setLevel(SatXmlError::ERROR);
			$error->setNode('Comprobante');
		}
		
		// FechaTimbrado attribute validation
		$certSAT = $timbreFiscal->getNoCertificadoSAT();
		$stampDate = $timbreFiscal->getFechaTimbrado();
		$date = $this->comprobante->getFecha();
		$daysdiff = $this->getDaysDiff($stampDate, $date);
		if ($daysdiff > self::MAX_STAMP_DAYS) {
			$error = $this->buildError();
			$error->setCode('2');
			$error->setMessage("La fecha de fecha de timbrado ($stampDate) es mayor a " . self::MAX_STAMP_DAYS . " dias que la fecha de emision");
			$error->setLevel(SatXmlError::WARNING);
			$error->setNode('TimbreFiscalDigital');
			$error->setAttribute('FechaTimbrado');
			$error->setValue($stampDate);
		}
		
		// Validar certificado sat////
		if (!$this->general->hasCertificadoSat($certSAT, $stampDate)) {
			$error = $this->buildError();
			$error->setCode('3');
			$error->setMessage("El Certificado CSD del PAC '".$certSAT."' No se encuentra registrado en los listados del SAT.");
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("noCertificadoSAT");
			$error->setNode('TimbreFiscalDigital');
			$error->setValue($certSAT);
		}
		
		// Validar uuid
		$uuid = strtolower($timbreFiscal->getUuid());
		if (preg_match("/^[0-9a-g]{8}-[0-9a-g]{4}-[0-9a-g]{4}-[0-9a-g]{4}-[0-9a-g]{12}$/i", $uuid) == false) {
			$error = $this->buildError();
			$error->setCode('4');
			$error->setMessage("El formato del UUID no es correcto: [0-9a-g]{8}-[0-9a-g]{4}-[0-9a-g]{4}-[0-9a-g]{4}-[0-9a-g]{12}");
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("UUID");
			$error->setNode('TimbreFiscalDigital');
			$error->setValue($timbreFiscal->getUuid());
		}
		
		// Validar esquema timbre fiscal
		if (preg_match("@http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd@i", $timbreFiscal->getNameSpace()) == false
			&& preg_match("@http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd@i", $timbreFiscal->getNameSpace() == false) 
		) {
			$error = $this->buildError();
			$error->setCode('6');
			$error->setMessage("xsi:schemaLocation del Complemento tfd:TimbreFiscalDigital no declarado o mal formado.");
			$error->setLevel(SatXmlError::ERROR);
			$error->setAttribute("xsi:schemaLocation");
			$error->setNode('TimbreFiscalDigital');
			$error->setValue($timbreFiscal->getNameSpace());
		}
		
		// Validar Webservice ...
		$this->validateSatWs();
		
		return ($this->hasErrors() == false);
	}
	
	private function validateSatWs() {
		
		$timbreFiscal = $this->comprobante->getTimbreFiscal();
		
		try {
			$client = new SoapClient(self::SAT_WS_URL);
			$emisor = $this->comprobante->getEmisor();
			$receptor = $this->comprobante->getReceptor();
			$total = number_format($this->comprobante->getTotal(), 6, '.', '');
			$uuid = strtoupper($timbreFiscal->getUuid());
			$rfcEmisor = str_replace("&","&amp;",strtoupper($emisor->getRfc()));
			$rfcReceptor = strtoupper($receptor->getRfc());
			$cadena = "re=$rfcEmisor&rr=$rfcReceptor&tt=$total&id=$uuid";
			$param = array('expresionImpresa' => $cadena);
			$response = $client->Consulta($param);
			$responseCode = $response->ConsultaResult->Estado;
			$responseString = $response->ConsultaResult->CodigoEstatus;
			
			if (preg_match("/" . self::SAT_WS_SUCCESS . "/i", $responseCode)) {
				if ($responseCode == "Cancelado") {		
					$cadena="El CFDi se encuentra Cancelado en listados del SAT";
					$error = $this->buildError();
					$error->setCode('7');
					$error->setMessage($responseString);
					$error->setLevel(SatXmlError::ERROR);
					$error->setAttribute("UUID");
					$error->setNode('TimbreFiscalDigital');
					$error->setValue($responseCode);
				} else {
					return true;
				}
			} else {
				$code = 0;
				$parts = explode(':', $responseString);
				if (empty($parts) == false) {
					$parts = $parts[0];
					$parts = explode('-', $parts);
					if (count($parts) > 1) {
						$code = trim($parts[1]);
					}
				}
				
				$error = $this->buildError();
				$error->setCode($code);
				$error->setMessage($responseString);
				$error->setLevel(SatXmlError::ERROR);
				$error->setAttribute("UUID");
				$error->setNode('TimbreFiscalDigital');
				$error->setValue($cadena);
			}
		} catch (Exception $e) {
			$error = $this->buildError();
			$error->setCode('5');
			$error->setMessage("La verificaci�n del CFDi con el SAT No se ha podido realizar, Conexi�n al WS del SAT ha fallado.");
			$error->setLevel(SatXmlError::WARNING);
			$error->setAttribute("UUID");
			$error->setNode('TimbreFiscalDigital');
			$error->setValue($timbreFiscal->getUuid());
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
	
	private function getDaysDiff($date1, $date2) {
		$date1 = strtotime($date1);
		$date2 = strtotime($date2);
		$datediff = $date1 - $date2;
		return floor($datediff / (60 * 60 * 24));
	}
}
?>
