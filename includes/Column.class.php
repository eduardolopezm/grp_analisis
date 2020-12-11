<?php 
class Column {
	
	private $name;
	private $value;
	private $extra;
	private $error;
	
	public function __construct($name, $value) {
		$this->name = $name;
		$this->setValue($value);
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setValue($value) {
		$this->value = trim($value);
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setError($error) {
		$this->error = $error;	
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function hasError() {
		return (empty($this->error) == false);
	}
	
	public function setExtra($value) {
		$this->extra = trim($value);
	}
	
	public function getExtra() {
		return $this->extra;
	}
}

?>