<?php
include_once realpath(dirname(__FILE__)) . '/Domicilio.class.php';

class Receptor {

	private $rfc;
	private $nombre;
	private $domicilio;

	public function __construct() {
		$this->domicilio = new Domicilio();
	}

	public function getRfc() {
		return $this->rfc;
	}
	
	public function setRfc($rfc) {
		$this->rfc = $rfc;
	}
	
	public function getNombre() {
		return $this->nombre;
	}
	
	public function setNombre($nombre) {
		$this->nombre = $nombre;
	}
	
	public function getDomicilio() {
		return $this->domicilio;
	}
}
?>