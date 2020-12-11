<?php
class Concepto {
	private $cantidad;
	private $unidad;
	private $noIdentificacion;
	private $descripcion;
	private $valorUnitario;
	private $importe;
	private $claveProdServ; // 3.3
	private $claveUnidad;	// 3.3
	private $descuento;		// 3.3

	private $impuestos;
	public function __construct(){
		$this->impuestos = new Impuestos();
	}

	public function setImpuestos($impuestos)
	{
		$this->impuestos = $impuestos;
	}

	public function getImpuestos()
	{
		return $this->impuestos;
	}

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

	public function getClaveProdServ() {
		return $this->claveProdServ;
	}

	public function setClaveProdServ($claveProdServ) {
		$this->claveProdServ = $claveProdServ;
	}

	public function getClaveUnidad() {
		return $this->claveUnidad;
	}

	public function setClaveUnidad($claveUnidad) {
		$this->claveUnidad = $claveUnidad;
	}

	public function getDescuento() {
		return $this->descuento;
	}

	public function setDescuento($descuento) {
		$this->descuento = $descuento;
	}
}
?>
