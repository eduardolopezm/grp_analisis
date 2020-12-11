<?php
include_once realpath(dirname(__FILE__)) . '/Domicilio.class.php';

class Emisor {

	private $rfc;
	private $nombre;
	private $regimenFiscal;
	private $domicilioFiscal;
	private $expedidoEn;

	public function __construct() {
		$this->domicilioFiscal = new Domicilio();
		$this->expedidoEn = new Domicilio();
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
	
	public function getRegimenFiscal() {
		return $this->regimenFiscal;
	}
	
	public function setRegimenFiscal($regimenFiscal) {
		$this->regimenFiscal = $regimenFiscal;
	}
	
	public function getDomicilioFiscal() {
		return $this->domicilioFiscal;
	}
	
	public function getExpedidoEn() {
		return $this->expedidoEn;
	}
}
?>