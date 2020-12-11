<?php
class Domicilio {

	private $calle;
	private $noExterior;
	private $noInterior;
	private $colonia;
	private $referencia;
	private $localidad;
	private $municipio;
	private $estado;
	private $pais;
	private $codigoPostal;
	
	public function getCalle() {
		return $this->calle;
	}
	
	public function setCalle($calle) {
		$this->calle = $calle;
	}
	
	public function getNoExterior() {
		return $this->noExterior;
	}
	
	public function setNoExterior($noExterior) {
		$this->noExterior = $noExterior;
	}
	
	public function getNoInterior() {
		return $this->noInterior;
	}
	
	public function setNoInterior($noInterior) {
		$this->noInterior = $noInterior;
	}
	
	public function getColonia() {
		return $this->colonia;
	}
	
	public function setColonia($colonia) {
		$this->colonia = $colonia;
	}
	
	public function getReferencia() {
		return $this->referencia;
	}
	
	public function setReferencia($referencia) {
		$this->referencia = $referencia;
	}
	
	public function getLocalidad() {
		return $this->localidad;
	}
	
	public function setLocalidad($localidad) {
		$this->localidad = $localidad;
	}
	
	public function getMunicipio() {
		return $this->municipio;
	}
	
	public function setMunicipio($municipio) {
		$this->municipio = $municipio;
	}
	
	public function getEstado() {
		return $this->estado;
	}
	
	public function setEstado($estado) {
		$this->estado = $estado;
	}
	
	public function getPais() {
		return $this->pais;
	}
	
	public function setPais($pais) {
		$this->pais = $pais;
	}
	
	public function getCodigoPostal() {
		return $this->codigoPostal;
	}
	
	public function setCodigoPostal($codigoPostal) {
		$this->codigoPostal = $codigoPostal;
	}
}
?>