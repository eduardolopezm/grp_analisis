<?php 
class Row {
	
	private $columns;
	private $line;
	private $error;
	
	public function __construct($line) {
		$this->columns = array();
		$this->line = $line;
	}
	
	public function addColumn(Column $column) {
		$this->columns[] = $column;
	}
	
	public function getColumnByName($name) {
		foreach ($this->columns as $column) {
			if ($column->getName() == $name) {
				return $column;
			}
		}
		return null;
	}
	
	public function getLine() {
		return $this->line;
	}
	
	public function length() {
		return count($this->columns);
	}
	/**
	 * 
	 * @return Column
	 */
	public function getColumn($index) {
		return $this->columns[$index];
	}
	
	public function setError($error) {
		$this->error = $error;
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function hasRowError() {
		return (empty($this->error) == false);
	}
	
	public function hasColumnError() {
		foreach ($this->columns as $column) {
			if ($column->hasError()) {
				return true;
			}
		}
		return (empty($this->error) == false);
	}
}
?>