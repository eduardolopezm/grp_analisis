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

	public function agregarRetencion($impuesto, $importe, $tasa) {
		$retencion = new Impuesto();
		$retencion->setImpuesto($impuesto);
		$retencion->setImporte($importe);
		$retencion->setTasa($tasa);
		$this->retenciones[] = $retencion;
	}

	public function agregarTraslado($impuesto, $importe, $tasa) {
		$traslado = new Impuesto();
		$traslado->setImpuesto($impuesto);
		$traslado->setImporte($importe);
		$traslado->setTasa($tasa);
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
	
	public function getSumaRetenciones() {
		$total = 0;
		foreach ($this->retenciones as $retencion) {
			$total += $retencion->getImporte();
		}
		return $total;
	}
	
	public function getTotalImpuestosRetenidosReal() {
		if ($this->getTotalImpuestosRetenidos() == null) {
			return $this->getSumaRetenciones();
		}
		return $this->getTotalImpuestosRetenidos();
	}
	
	public function getTraslados() {
		return $this->traslados;
	}
	
	public function getSumaTrasladados() {
		$total = 0;
		foreach ($this->traslados as $traslado) {
			$total += $traslado->getImporte();
		}
		return $total;
	}
	
	public function getTotalImpuestosTrasladadosReal() {
		if ($this->getTotalImpuestosTrasladados() == null) {
			return $this->getSumaTrasladados();
		}
		return $this->getTotalImpuestosTrasladados();
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
}
?>