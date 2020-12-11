<?php
class Impuestos {

	private $totalImpuestosRetenidos;
	private $totalImpuestosTrasladados;
	private $retenciones;
	private $traslados;
	private $nameSpace;
	public function __construct() {
		$this->retenciones = array();
		$this->traslados = array();
	}
	public function agregarRetencion($impuesto, $importe, $tasa ="", $base = "", $tipoFactor = "") {
		$retencion = new Impuesto();
		$retencion->setImpuesto($impuesto);
		$retencion->setImporte($importe);
		$retencion->setTasa($tasa);
		$retencion->setBase($base);
		$retencion->setTipoFactor($tipoFactor);
		$this->retenciones[] = $retencion;
	}
	public function agregarTraslado($impuesto, $importe, $tasa, $base = "", $tipoFactor = "") {
		$traslado = new Impuesto();
		$traslado->setImpuesto($impuesto);
		$traslado->setImporte($importe);
		$traslado->setTasa($tasa);
		$traslado ->setBase($base);
		$traslado ->setTipoFactor($tipoFactor);
		$this->traslados[] = $traslado;
	}

	public function getTotalImpuestosRetenidos() {
		return $this->totalImpuestosRetenidos;
	}

	public function setTotalImpuestosRetenidos($totalImpuestosRetenidos) {
		$this->totalImpuestosRetenidos = $totalImpuestosRetenidos;
	}

	public function getTotalImpuestosTrasladados() {
		return $this->totalImpuestosTrasladados;
	}

	public function setTotalImpuestosTrasladados($totalImpuestosTrasladados) {
		$this->totalImpuestosTrasladados = $totalImpuestosTrasladados;
	}

	public function getRetenciones() {
		return $this->retenciones;
	}

	public function getTraslados() {
		return $this->traslados;
	}

	public function getNameSpace() {
		return $this->nameSpace;
	}

	public function setNameSpace($nameSpace) {
		$this->nameSpace = $nameSpace;
	}
}


class Impuesto {

	private $impuesto;
	private $importe;
	private $tasa;
	private $base; // 3.3
	private $tipoFactor; // 3.3

	public function getImpuesto() {
		return $this->impuesto;
	}

	public function setImpuesto($impuesto) {
		$this->impuesto = $impuesto;
	}

	public function getImporte(){
		return $this->importe;
	}

	public function setImporte($importe) {
		$this->importe = $importe;
	}

	public function getTasa() {
		return $this->tasa;
	}

	public function setTasa($tasa) {
		$this->tasa = $tasa;
	}

	public function getBase() {
		return $this->base;
	}

	public function setBase($base) {
		$this->base = $base;
	}

	public function getTipoFactor() {
		return $this->tipoFactor;
	}

	public function setTipoFactor($tipoFactor) {
		$this->tipoFactor = $tipoFactor;
	}
}
?>
