<?php
include_once realpath(dirname(__FILE__)) . '/Emisor.class.php';
include_once realpath(dirname(__FILE__)) . '/Receptor.class.php';
include_once realpath(dirname(__FILE__)) . '/Concepto.class.php';
include_once realpath(dirname(__FILE__)) . '/Impuestos.class.php';
include_once realpath(dirname(__FILE__)) . '/TimbreFiscal.class.php';
include_once realpath(dirname(__FILE__)) . '/addendas/AddendaCFE.class.php';
 
class Comprobante {
	
	const CFD_VERSION_32 = "3.2";
	const CFD_VERSION_30 = "3.0";
	const CFD_VERSION_22 = "2.2";
	const CFD_VERSION_20 = "2.0";
	//
	private $version;
	private $serie;
	private $folio;
	private $fecha;
	private $sello;
	private $tipoDeComprobante;
	private $formaDePago;
	private $noCertificado;
	private $certificado;
	private $subTotal;
	private $descuento;
	private $total;
	private $metodoDePago;
	private $tipoCambio;
	private $moneda;
	private $lugarExpedicion;
	private $numCtaPago;
	private $noAprobacion;
	private $anoAprobacion;
	private $cadenaOriginal;
	private $xmlObject;
	private $rutaXml;
	private $nameSpace;
			
	private $emisor;
	private $receptor;
	private $conceptos;
	private $impuestos;
	private $impuestosLocales;
	private $timbreFiscal;
	private $addenda;
	
	public function __construct() {
		$this->version = "";
		$this->emisor = new Emisor();
		$this->receptor = new Receptor();
		$this->conceptos = array();
		$this->impuestos = new Impuestos();
		$this->impuestosLocales = new Impuestos();
		$this->timbreFiscal = new TimbreFiscal();
		
	}
	
	public function agregarConcepto($cantidad, $unidad, $noIdentificacion, $descripcion, $valorUnitario, $importe) {
		$concepto = new Concepto();
		$concepto->setCantidad($cantidad);
		$concepto->setUnidad($unidad);
		$concepto->setNoIdentificacion($noIdentificacion);
		$concepto->setDescripcion($descripcion);
		$concepto->setValorUnitario($valorUnitario);
		$concepto->setImporte($importe);
		$this->conceptos[] = $concepto;
	}
	
	public function getVersion() {
		return $this->version;
	}
	
	public function setVersion($version) {
		$this->version = $version;
	}
	
	public function getSerie() {
		return $this->serie;
	}
	
	public function setSerie($serie) {
		$this->serie = $serie;
	}
	
	public function getFolio() {
		return $this->folio;
	}
	
	public function setFolio($folio) {
		$this->folio = $folio;
	}
	
	public function getFecha() {
		return $this->fecha;
	}
	
	public function setFecha($fecha) {
		$this->fecha = $fecha;
	}
	
	public function getSello() {
		return $this->sello;
	}
	
	public function setSello($sello) {
		$this->sello = $sello;
	}
	
	public function getTipoDeComprobante() {
		return $this->tipoDeComprobante;
	}
	
	public function setTipoDeComprobante($tipoDeComprobante) {
		$this->tipoDeComprobante = $tipoDeComprobante;
	}
	
	public function getFormaDePago() {
		return $this->formaDePago;
	}
	
	public function setFormaDePago($formaDePago) {
		$this->formaDePago = $formaDePago;
	}
	
	public function getNoCertificado() {
		return $this->noCertificado;
	}
	
	public function setNoCertificado($noCertificado) {
		$this->noCertificado = $noCertificado;
	}
	
	public function getCertificado() {
		return $this->certificado;
	}
	
	public function setCertificado($certificado) {
		$this->certificado = $certificado;
	}
	
	public function getSubTotal() {
		return $this->subTotal;
	}
	
	public function setSubTotal($subTotal) {
		$this->subTotal = $subTotal;
	}
	
	public function getDescuento() {
		return $this->descuento;
	}
	
	public function setDescuento($descuento) {
		$this->descuento = $descuento;
	}
	
	public function getTotal() {
		return $this->total;
	}
	
	public function setTotal($total) {
		$this->total = $total;
	}
	
	public function getMetodoDePago() {
		return $this->metodoDePago;
	}
	
	public function setMetodoDePago($metodoDePago) {
		$this->metodoDePago = $metodoDePago;
	}
	
	public function getTipoCambio() {
		return $this->tipoCambio;
	}
	
	public function setTipoCambio($tipoCambio) {
		$this->tipoCambio = $tipoCambio;
	}
	
	public function getMoneda() {
		return $this->moneda;
	}
	
	public function setMoneda($moneda) {
		$this->moneda = $moneda;
	}
	
	public function getLugarExpedicion() {
		return $this->lugarExpedicion;
	}
	
	public function setLugarExpedicion($lugarExpedicion) {
		$this->lugarExpedicion = $lugarExpedicion;
	}
	
	public function getNumCtaPago() {
		return $this->numCtaPago;
	}
	
	public function setNumCtaPago($numCtaPago) {
		$this->numCtaPago = $numCtaPago;
	}
	
	public function getNoAprobacion() {
		return $this->noAprobacion;
	}
	
	public function setNoAprobacion($noAprobacion) {
		$this->noAprobacion = $noAprobacion;
	}
	
	public function getAnoAprobacion() {
		return $this->anoAprobacion;
	}
	
	public function setAnoAprobacion($anoAprobacion) {
		$this->anoAprobacion = $anoAprobacion;
	}
	
	public function getEmisor() {
		return $this->emisor;
	}
	
	public function setEmisor($emisor) {
		$this->emisor = $emisor;
	}
	
	public function getReceptor() {
		return $this->receptor;
	}
	
	public function setReceptor($receptor) {
		$this->receptor = $receptor;
	}
	
	public function getConceptos() {
		return $this->conceptos;
	}
	
	public function getImpuestos() {
		return $this->impuestos;
	}
	
	public function setImpuestos($impuestos) {
		$this->impuestos = $impuestos;
	}
	
	public function getImpuestosLocales() {
		return $this->impuestosLocales;
	}
	
	public function setImpuestosLocales($impuestosLocales) {
		$this->impuestosLocales = $impuestosLocales;
	}
	
	public function getTimbreFiscal() {
		return $this->timbreFiscal;
	}
	
	public function getRutaXml() {
		return $this->rutaXml;
	}
	
	public function setRutaXml($rutaXml) {
		$this->rutaXml = $rutaXml;
	}
	
	public function getCadenaOriginal() {
		return $this->cadenaOriginal;
	}
	
	public function setCadenaOriginal($cadenaOriginal) {
		$this->cadenaOriginal = $cadenaOriginal;
	}
	
	public function getNameSpace() {
		return $this->nameSpace;
	}
	
	public function setNameSpace($nameSpace) {
		$this->nameSpace = $nameSpace;
	}
	
	public function getXmlObject() {
		return $this->xmlObject;
	}
	
	public function setXmlObject($xmlObject) {
		$this->xmlObject = $xmlObject;
	}
	
	public function getAddenda() {
		return $this->addenda;
	}
	
	public function setAddenda($addenda) {
		$this->addenda = $addenda;
	}
}
?>