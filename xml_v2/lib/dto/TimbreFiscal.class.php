<?php
class TimbreFiscal {

	private $version;
	private $uuid;
	private $noCertificadoSAT;
	private $FechaTimbrado;
	private $selloSAT;
	private $selloCFD;
	private $nameSpace;
	
	public function getUuid() {
		return $this->uuid;
	}
	
	public function setUuid($uuid) {
		$this->uuid = $uuid;
	}
	
	public function getNoCertificadoSAT() {
		return $this->noCertificadoSAT;
	}
	
	public function setNoCertificadoSAT($noCertificadoSAT) {
		$this->noCertificadoSAT = $noCertificadoSAT;
	}
	
	public function getFechaTimbrado() {
		return $this->FechaTimbrado;
	}
	
	public function setFechaTimbrado($FechaTimbrado) {
		$this->FechaTimbrado = $FechaTimbrado;
	}
	
	public function getSelloSAT() {
		return $this->selloSAT;
	}
	
	public function setSelloSAT($selloSAT) {
		$this->selloSAT = $selloSAT;
	}
	
	public function getSelloCFD() {
		return $this->selloCFD;
	}
	
	public function setSelloCFD($selloCFD) {
		$this->selloCFD = $selloCFD;
	}
	
	public function getVersion() {
		return $this->version;
	}
	
	public function setVersion($version) {
		$this->version = $version;
	}
	
	public function getNameSpace() {
		return $this->nameSpace;
	}
	
	public function setNameSpace($nameSpace) {
		$this->nameSpace = $nameSpace;
	}
}
?>