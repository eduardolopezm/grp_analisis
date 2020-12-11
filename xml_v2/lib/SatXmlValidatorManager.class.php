<?php 
/**
 * Class responsible of validating the XML
 * @version 1.0
 */
class SatXmlValidatorManager {
	
	const ERROR_TYPE = 'EMFX';
	
	/**
	 * @var Comprobante
	 */
	private $comprobante;
	/**
	 * Errors collection of type SatXmlError
	 * @var Array
	 */
	private $errors;
	private $validators;

	
	public function __construct($path, $db) {
		$this->errors = array();
		$this->validators = array();
		$this->comprobante = ComprobanteFactory::getComprobante($path, $db);
	}
	
	public function addValidator(SatValidator $validator) {
		$validator->setComprobante($this->comprobante);
		$this->validators[] = $validator;
	}
	
	public function validate() {
		if ($this->comprobante != null) {
			foreach ($this->validators as $validator) {
				if ($validator->validate() == false) {
					$errors = $validator->getErrors();
					$this->addMultipleErrors($errors);
				}
			}
		} else {
			$error = new SatXmlError();
			$error->setCode('1');
			$error->setMessage("Documento mal formado, no cumple con los standares de la W3C");
			$error->setLevel(SatXmlError::ERROR);
			$error->setClass(get_class($this));
			$error->setType(self::ERROR_TYPE);
			$this->addError($error);
		}
	}
	
	public function getXmlString() {
		if ($this->comprobante != null) {
			$xmlObject = $this->comprobante->getXmlObject();
			return $xmlObject->saveXML();
		}
		return null;
	}
	
	public function getXMLObject() {
		if ($this->comprobante != null) {
			return $this->comprobante->getXmlObject();
		}
		return null;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	private function addError($error) {
		$this->errors[] = $error;
	}
	
	private function addMultipleErrors($errors) {
		foreach ($errors as $error) {
			$this->errors[] = $error;
		}
	}
	
	public function cleanErrors() {
		$this->errors = array();
		foreach ($this->validators as $validator) {
			$validator->cleanErrors();
		}
	}
	
	/**
	 * @return Comprobante
	 */
	public function getComprobante() {
		return $this->comprobante;
	}
}
?>