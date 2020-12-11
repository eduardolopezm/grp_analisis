<?php 
/**
 * Validator Interface
 * @version 1.0
 */
abstract class SatValidator {
	
	protected $errors;
	/**
	 * @var Comprobante
	 */
	protected $comprobante;
	
	public function __construct() {
		$this->errors = array();
	}
	
	public function setComprobante(Comprobante $comprobante = null) {
		$this->comprobante = $comprobante;
	}
	
	abstract public function validate();
	
	public function addError(SatXmlError $error) {
		$this->errors[] = $error;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function hasErrors() {
		return (empty($this->errors) == false);
	}
	
	public function cleanErrors() {
		$this->errors = array();
	}
	
	public function getXmlObj() {
		if ($this->comprobante != null) {
			return $this->comprobante->getXmlObject();
		}
		return null;
	}
	
	public function getXml() {
		if ($this->comprobante != null) {
			$xmlObject = $this->comprobante->getXmlObject();
			return $xmlObject->saveXML();
		}
		return null;
	}
}
?>