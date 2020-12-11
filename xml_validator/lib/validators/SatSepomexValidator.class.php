<?php
include_once realpath(dirname(__FILE__)) . '/SatValidator.interface.php';
include_once realpath(dirname(__FILE__)) . '/../SatXmlError.class.php';
include_once realpath(dirname(__FILE__)) . '/../models/Sepomex.class.php';
 
/**
 * Class responsible of validating Sepomex data
 * @version 1.0
 */
class SatSepomexValidator extends SatValidator {
	
	const ERROR_TYPE = 'PCIS';
	
	private $sepomex;
	
	public function __construct($db) {
		parent::__construct();
		$this->sepomex = new Sepomex($db);
	}
	
	public function validate() {
		
		if ($this->comprobante == null) {
			$error = $this->buildError();
			$error->setCode('0');
			$error->setMessage("La validacion SEPOMEX no pudo ser completada");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			return false;
		}
		
		$this->cleanErrors();
		
		$emisor = $this->comprobante->getEmisor();
		$receptor = $this->comprobante->getReceptor();
		$domicilioFiscal = $emisor->getDomicilioFiscal();
		$expedidoEn = $emisor->getExpedidoEn();
		$domicilio = $receptor->getDomicilio();
		
		// Domicilio Fiscal validation
		$colony = $domicilioFiscal->getColonia();
		$city = $domicilioFiscal->getMunicipio();
		$state = $domicilioFiscal->getEstado();
		$cp = $domicilioFiscal->getCodigoPostal();
			
		if (empty($colony) == false) {
			if ($this->sepomex->hasColony($colony) == false) {
				$error = $this->buildError();
				$error->setCode('1');
				$error->setMessage("La colonia $colony del nodo Emisor/DomicilioFiscal no fue encontrada en SEPOMEX");
				$error->setNode('DomicilioFiscal');
				$error->setAttribute('colonia');
				$error->setValue($colony);
			}
		}
		
		if (empty($city) == false) {
			if ($this->sepomex->hasCity($city) == false) {
				$error = $this->buildError();
				$error->setCode('2');
				$error->setMessage("El municipio $city del nodo Emisor/DomicilioFiscal no fue encontrada en SEPOMEX");
				$error->setNode('DomicilioFiscal');
				$error->setAttribute('municipio');
				$error->setValue($city);
			}
		}
		
		if (empty($cp) == false) {

			if ($this->sepomex->hasCp($cp) == false) {
				$error = $this->buildError();
				$error->setCode('3');
				$error->setMessage("El codigo postal $cp del nodo Emisor/DomicilioFiscal no fue encontrada en SEPOMEX");
				$error->setNode('DomicilioFiscal');
				$error->setAttribute('codigoPostal');
				$error->setValue($cp);
			}
		}
		
		if (empty($state) == false) {
			if ($this->sepomex->hasState($state) == false) {
				$error = $this->buildError();
				$error->setCode('4');
				$error->setMessage("El estado $state del nodo Emisor/DomicilioFiscal no fue encontrada en SEPOMEX");
				$error->setNode('DomicilioFiscal');
				$error->setAttribute('estado');
				$error->setValue($state);
			}
		}
		
		// Expedido En validation
		$colony = $expedidoEn->getColonia();
		$city = $expedidoEn->getMunicipio();
		$state = $expedidoEn->getEstado();
		$cp = $expedidoEn->getCodigoPostal();
		
		if (empty($colony) == false) {
			if ($this->sepomex->hasColony($colony) == false) {
				$error = $this->buildError();
				$error->setCode('5');
				$error->setMessage("La colonia $colony del nodo Emisor/ExpedidoEn no fue encontrada en SEPOMEX");
				$error->setNode('ExpedidoEn');
				$error->setAttribute('colonia');
				$error->setValue($colony);
			}
		}
		
		if (empty($city) == false) {
			if ($this->sepomex->hasCity($city) == false) {
				$error = $this->buildError();
				$error->setCode('6');
				$error->setMessage("El municipio $city del nodo Emisor/ExpedidoEn no fue encontrada en SEPOMEX");
				$error->setNode('ExpedidoEn');
				$error->setAttribute('municipio');
				$error->setValue($city);
			}
		}
		
		if (empty($cp) == false) {
			if ($this->sepomex->hasCp($cp) == false) {
				$error = $this->buildError();
				$error->setCode('7');
				$error->setMessage("El codigo postal $cp del nodo Emisor/ExpedidoEn no fue encontrada en SEPOMEX");
				$error->setNode('ExpedidoEn');
				$error->setAttribute('codigoPostal');
				$error->setValue($cp);
			}
		}
		
		if (empty($state) == false) {
			if ($this->sepomex->hasState($state) == false) {
				$error = $this->buildError();
				$error->setCode('8');
				$error->setMessage("El estado $state del nodo Emisor/ExpedidoEn no fue encontrada en SEPOMEX");
				$error->setNode('ExpedidoEn');
				$error->setAttribute('estado');
				$error->setValue($state);
			}
		}
		
		// Receptor validation
		$colony = $domicilio->getColonia();
		$city = $domicilio->getMunicipio();
		$state = $domicilio->getEstado();
		$cp = $domicilio->getCodigoPostal();
		
		if (empty($colony) == false) {
			if ($this->sepomex->hasColony($colony) == false) {
				$error = $this->buildError();
				$error->setCode('9');
				$error->setMessage("La colonia $colony del nodo Receptor/Domicilio no fue encontrada en SEPOMEX");
				$error->setNode('Domicilio');
				$error->setAttribute('colonia');
				$error->setValue($colony);
			}
		}
		
		if (empty($city) == false) {
			if ($this->sepomex->hasCity($city) == false) {
				$error = $this->buildError();
				$error->setCode('10');
				$error->setMessage("El municipio $city del nodo Receptor/Domicilio no fue encontrada en SEPOMEX");
				$error->setNode('Domicilio');
				$error->setAttribute('municipio');
				$error->setValue($city);
			}
		}
		
		if (empty($cp) == false) {
			if ($this->sepomex->hasCp($cp) == false) {
				$error = $this->buildError();
				$error->setCode('11');
				$error->setMessage("El codigo postal $cp del nodo Receptor/Domicilio no fue encontrada en SEPOMEX");
				$error->setNode('Domicilio');
				$error->setAttribute('codigoPostal');
				$error->setValue($cp);
			}
		}
		
		if (empty($state) == false) {
			if ($this->sepomex->hasState($state) == false) {
				$error = $this->buildError();
				$error->setCode('12');
				$error->setMessage("El estado $state del nodo Receptor/Domicilio no fue encontrada en SEPOMEX");
				$error->setNode('Domicilio');
				$error->setAttribute('estado');
				$error->setValue($state);
			}
		}
		
		return ($this->hasErrors() == false);
	}
	
	private function buildError() {
		$error = new SatXmlError();
		$error->setType(self::ERROR_TYPE);
		$error->setLevel(SatXmlError::WARNING);
		$error->setClass(get_class($this));
		if ($this->comprobante != null) {
			$error->setVersion($this->comprobante->getVersion());
		}
		$this->addError($error);
		return $error;
	}
}
?>