<?php 
class Table {
	
	private $rows;
	
	public function __construct() {
		$this->rows = array();
	}
	
	public function addRow(Row $row) {
		$this->rows[] = $row;
	}
	
	public function hasRows() {
		return (empty($this->rows) == false);
	}
	
	public function hasError() {
		foreach ($this->rows as $row) {
			if ($row->hasColumnError() || $row->hasRowError()) {
				return true;
			}
		}
		return false;
	}
	
	public function clear() {
		$this->rows = array();
	}
	
	public function length() {
		return count($this->rows);
	}
	/**
	 *
	 * @return Row
	 */
	public function getRow($index) {
		return $this->rows[$index];
	}
}
?>