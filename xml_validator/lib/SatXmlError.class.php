<?php
/**
 * Class that encapsulates the validation errors
 * @version 1.0
 */
class SatXmlError {

	const WARNING = 1;
	const ERROR = 2;
	const FATAL_ERROR = 3;

	private $code;
	private $message;
	private $level;
	private $line;
	private $class;
	private $node;
	private $attribute;
	private $value;
	private $type;
	private $version;

	public function __construct() {
		$this->code = 0;
		$this->line = 0;
	}

	public function setCode($code) {
		$this->code = $code;
	}

	public function getCode() {
		return $this->code;
	}

	public function setLine($line) {
		$this->line = $line;
	}

	public function getLine() {
		return $this->line;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function getMessage() {
		return $this->message;
	}

	public function setLevel($level) {
		$this->level = $level;
	}

	public function getLevel() {
		return $this->level;
	}

	public function setClass($class) {
		$this->class = $class;
	}

	public function getClass() {
		return $this->class;
	}

	public function setNode($node) {
		$this->node = $node;
	}

	public function getNode() {
		return $this->node;
	}

	public function setAttribute($attribute) {
		$this->attribute = $attribute;
	}

	public function getAttribute() {
		return $this->attribute;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function setType($type) {
		$this->type = $type;
	}

	public function getType() {
		return $this->type;
	}

	public function setVersion($version) {
		$this->version = $version;
	}

	public function getVersion() {
		return $this->version;
	}

	public function toHtml() {
		$return = "";
		switch ($this->level) {
			case self::WARNING:
				$return .= "<div class='alert alert-warning'><b>Advertencia $this->code</b>: ";
				break;
			case self::ERROR:
				$return .= "<div class='alert alert-danger'><b>Error $this->code</b>: ";
				break;
			case self::FATAL_ERROR:
				$return .= "<div class='alert alert-danger'><b>Error Fatal $this->code</b>: ";
				break;
		}
		$return .= $this->message;
		$return .= "</div>";

		return $return;
	}
}
?>
