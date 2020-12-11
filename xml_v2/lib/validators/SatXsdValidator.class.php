<?php
include_once realpath(dirname(__FILE__)) . '/SatValidator.interface.php';
include_once realpath(dirname(__FILE__)) . '/../SatXmlError.class.php';
 
/**
 * Class responsible of validating the XML schema
 * @version 1.0
 */
class SatXsdValidator extends SatValidator {
	
	const ERROR_TYPE = 'PCIX';
	
	/**
	 * XSD Path
	 * @var String
	 */
	private $xsdPath;
	/**	
	 * List of ingored errors
	 * @var Array
	 */
	private $ignoreErrors;
	
	public function __construct() {
		parent::__construct();
		$this->ignoreErrors = array();
		libxml_use_internal_errors(true);
	}
	
	public function validate() {
		
		libxml_clear_errors();
		
		if ($this->comprobante == null) {
			$error = new SatXmlError();
			$error->setCode('X0');
			$error->setMessage("La validacion del esquema XSD no pudo ser completada");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			$error->setClass(get_class($this));
			$error->setType(self::ERROR_TYPE);
			$this->addError($error);
			return false;
		}
		
		$this->cleanErrors();
		
		$this->xsdPath = realpath(dirname(__FILE__)) . '/../sat/v' . $this->comprobante->getVersion() 
			. '/xsd/cfdv' . str_replace('.', '', $this->comprobante->getVersion()) .'.xsd';
		
		$xmlObject = $this->comprobante->getXmlObject();
		$xmlObject->schemaValidate($this->xsdPath);
		$this->parseErrors();
		
		return ($this->hasErrors() == false);
	}
	
	public function addIgnoreError($error) {
		$this->ignoreErrors[] = $error;
	}
	
	private function parseErrors() {
		$errors = libxml_get_errors();
		foreach ($errors as $error) {
			if (in_array($error->code, $this->ignoreErrors) == false) {
				$satError = new SatXmlError();
				$satError->setCode($error->code);
				$satError->setMessage($error->message);
				$satError->setLevel($error->level);
				$satError->setLine($error->line);
				$satError->setClass(get_class($this));
				$satError->setType(self::ERROR_TYPE);
				$satError->setVersion($this->comprobante->getVersion());
				$this->setNodeData($satError);
				$this->addError($satError);
			}
		}
		libxml_clear_errors();
	}
	
	private function setNodeData(SatXmlError $error) {
		$data = array();
		preg_match('@Element \'{http://www.sat.gob.mx/cfd/.}([^\']+)\'@', $error->getMessage(), $data);
		if (empty($data[1]) == false) {
			$error->setNode($data[1]);
		}
		$node = $error->getNode();
		if (empty($node)) {
			preg_match('@Element \'@', $error->getMessage(), $data);
			if (empty($data[1]) == false) {
				$error->setNode($data[1]);
			}
		}
		preg_match('@attribute \'([^\']+)\'@', $error->getMessage(), $data);
		if (empty($data[1]) == false) {
			$error->setAttribute($data[1]);
		}
		preg_match('@The value \'([^\']+)\'@', $error->getMessage(), $data);
		if (empty($data[1]) == false) {
			$error->setValue($data[1]);
		}
	}
}
?>