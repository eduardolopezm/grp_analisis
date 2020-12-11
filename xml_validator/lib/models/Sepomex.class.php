<?php
/**
 * Model class responsible of querying Sepomex data
 * @version 1.0 
 */
class Sepomex {
	
	private $db;
	private $table = "SAT.sepomex";
	
	public function __construct($db) {
		$this->db = $db;
	}
	
	public function hasColony($colony) {
		$colony=strtoupper($colony);
		$colony=strtolower($colony);
		
		$rs = mysqli_query($this->db, "SELECT 1 FROM {$this->table} WHERE lower(d_asenta) rlike '$colony'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false; 
	}  
	
	public function hasCity($city) {
		$city=strtoupper($city);
		$city=strtolower($city);
		$rs = mysqli_query($this->db, "SELECT 1 FROM {$this->table} WHERE lower(d_ciudad) like '$city%' OR lower(D_mnpio) like '$city%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
	
	public function hasCp($cp) {
		//echo "SELECT 1 FROM {$this->table} WHERE d_codigo = '$cp' OR d_CP = '$cp'";
		$rs = mysqli_query($this->db, "SELECT 1 FROM {$this->table} WHERE lower(d_codigo) = '$cp' OR lower(d_CP) = '$cp'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
	
	public function hasState($state) {
		$state=strtoupper($state);
		$state=strtolower($state);
		
		$rs = mysqli_query($this->db, "SELECT 1 FROM {$this->table} WHERE lower(d_estado) like '$state%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
}
?>