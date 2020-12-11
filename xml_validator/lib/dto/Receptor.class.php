<?php
include_once realpath(dirname(__FILE__)) . '/Domicilio.class.php';

class Receptor {

	private $rfc;
	private $nombre;
	private $domicilio;
	private $numRegIdTrib;
	private $usoCFDI;
	private $residenciaFiscal;

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

	public function getNumRegIdTrib() {
		return $this->numRegIdTrib;
	}

	public function setNumRegIdTrib($numRegIdTrib) {
		$this->numRegIdTrib = $numRegIdTrib;
	}
	public function getUsoCFDI() {
		return $this->usoCFDI;
	}

	public function setUsoCFDI($usoCFDI) {
		$this->usoCFDI = $usoCFDI;
	}

	public function getResidenciaFiscal() {
		return $this->residenciaFiscal;
	}

	public function setResidenciaFiscal($residenciaFiscal) {
		$this->residenciaFiscal = $residenciaFiscal;
	}
}
?>
