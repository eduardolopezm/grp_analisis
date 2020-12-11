<?php
class Concepto {

	private $cantidad;
	private $unidad;
	private $noIdentificacion;
	private $descripcion;
	private $valorUnitario;
	private $importe;
	
	public function getCantidad() {
		return $this->cantidad;
	}
	//
	public function setCantidad($cantidad) {
		$this->cantidad = $cantidad;
	}
	
	public function getUnidad() {
		return $this->unidad;
	}
	
	public function setUnidad($unidad) {
		$this->unidad = $unidad;
	}
	
	public function getNoIdentificacion() {
		return $this->noIdentificacion;
	}
	
	public function setNoIdentificacion($noIdentificacion) {
		$this->noIdentificacion = $noIdentificacion;
	}
	
	public function getDescripcion() {
		return $this->descripcion;
	}
	
	public function setDescripcion($descripcion) {
		$this->descripcion = $descripcion;
	}
	
	public function getValorUnitario(){
		return $this->valorUnitario;
	}
	
	public function setValorUnitario($valorUnitario) {
		$this->valorUnitario = $valorUnitario;
	}
	
	public function getImporte() {
		return $this->importe;
	}
	
	public function setImporte($importe) {
		$this->importe = $importe;
	}
}
?>