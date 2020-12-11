<?php

// Clase DTO (Data Transfer Object) Comprobante

class Comprobante {
	
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
			
	private $emisor;
	private $receptor;
	private $conceptos;
	private $impuestos;
	private $nomina;
	
	public function __construct() {
		$this->emisor = new Emisor();
		$this->receptor = new Receptor();
		$this->conceptos = array();
		$this->impuestos = new Impuestos();
		$this->nomina = new Nomina();
	}
	
	public function agregarConcepto($cantidad, $unidad, $noIdentificacion, $descripcion, $valorUnitario, $importe) {
		$concepto = new Concepto();
		$concepto->cantidad = $cantidad;
		$concepto->unidad = $unidad;
		$concepto->noIdentificacion = $noIdentificacion;
		$concepto->descripcion = $descripcion;
		$concepto->valorUnitario = $valorUnitario;
		$concepto->importe = $importe;
		$this->conceptos[] = $concepto;
	}
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

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
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class Receptor {

	private $rfc;
	private $nombre;
	private $domicilio;

	public function __construct() {
		$this->domicilio = new Domicilio();
	}
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

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
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class Concepto {
	
	private $cantidad;
	private $unidad;
	private $noIdentificacion;
	private $descripcion;
	private $valorUnitario;
	private $importe;
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class Impuestos {
	
	private $totalImpuestosRetenidos;
	private $totalImpuestosTrasladados;
	private $retenciones;
	private $traslados;
	
	public function __construct() {
		$this->retenciones = array();
		$this->traslados = array();
	}

	public function agregarRetencion($impuesto, $importe) {
		$retencion = new Retencion();
		$retencion->impuesto = $impuesto;
		$retencion->importe = $importe;
		$this->retenciones[] = $retencion;
	}
	
	public function agregarTraslado($impuesto, $importe, $tasa) {
		$traslado = new Traslado();
		$traslado->impuesto = $impuesto;
		$traslado->importe = $importe;
		$traslado->tasa = $tasa;
		$this->traslados[] = $traslado;
	}
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class Retencion {

	private $impuesto;
	private $importe;
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class Traslado {

	private $impuesto;
	private $importe;
	private $tasa;

	public function __set($name, $value) {
		return $this->$name = $value;
	}

	public function __get($name) {
		return $this->$name;
	}
}

// Clase DTO (Data Transfer Object) Nomina
class Nomina {
	
	private $version;
	private $registroPatronal;
	private $numEmpleado;
	private $curp;
	private $tipoRegimen;
	private $numSeguridadSocial;
	private $fechaPago;
	private $fechaInicialPago;
	private $fechaFinalPago;
	private $numDiasPagados;
	private $departamento;
	private $clabe;
	private $banco;
	private $fechaInicioRelLaboral;
	private $antiguedad;
	private $puesto;
	private $tipoContrato;
	private $tipoJornada;
	private $periodicidadPago;
	private $salarioBaseCotApor;
	private $riesgoPuesto;
	private $salarioDiarioIntegrado;
	

	private $percepciones;
	private $deducciones;
	private $incapacidades;
	private $horasExtras;
	
	public function __construct() {
		$this->percepciones = new ConceptosNomina();
		$this->deducciones = new ConceptosNomina();
		$this->incapacidades = array();
		$this->horasExtras = array();
	}
	
	public function asignarTotalesPercepcion($totalGravado, $totalExento) {
		$this->percepciones->totalGravado = $totalGravado;
		$this->percepciones->totalExento = $totalExento;
	}
	
	public function asignarTotalesDeduccion($totalGravado, $totalExento) {
		$this->deducciones->totalGravado = $totalGravado;
		$this->deducciones->totalExento = $totalExento;
	}
	
	public function agregarPercepcion($tipo, $clave, $concepto, $importeGravado, $importeExento) {
		$this->percepciones->agregarConcepto($tipo, $clave, $concepto, $importeGravado, $importeExento);
	}
	
	public function agregarDeduccion($tipo, $clave, $concepto, $importeGravado, $importeExento) {
		$this->deducciones->agregarConcepto($tipo, $clave, $concepto, $importeGravado, $importeExento);
	}
	
	public function agregarIncapacidad($dias, $tipo, $descuento) {
		$this->incapacidades[] = new Incapacidad($dias, $tipo, $descuento);
	}
	
	public function agregarHorasExtra($dias, $tipo, $horasExtra, $importePagado) {
		$this->horasExtras[] = new HorasExtra($dias, $tipo, $horasExtra, $importePagado);
	}
	
	public function tienePercepciones() {
		return $this->percepciones->tieneConceptos();
	}
	
	public function tieneDeducciones() {
		return $this->deducciones->tieneConceptos();
	}
	
	public function tieneIncapacidades() {
		return count($this->incapacidades) > 0;
	}
	
	public function tieneHorasExtras() {
		return count($this->horasExtras) > 0;
	}
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

/**
 * 
 * @description Clase utilizada para las deducciones y percepciones.
 *
 */
class ConceptosNomina {
	
	private $totalGravado;
	private $totalExento;	
	private $conceptos;
	
	public function __construct() {
		$this->conceptos = array();
	}
	
	public function agregarConcepto($tipo, $clave, $concepto, $importeGravado, $importeExento) {
		$this->conceptos[] = new ConceptoNomina($tipo, $clave, $concepto, $importeGravado, $importeExento);
	}
	
	public function tieneConceptos() {
		return count($this->conceptos) > 0;
	}
	
	public function __set($name, $value) {
		return $this->$name = $value;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class ConceptoNomina {

	private $tipo;
	private $clave;
	private $concepto;
	private $importeGravado;
	private $importeExento;
	
	public function  __construct($tipo, $clave, $concepto, $importeGravado, $importeExento) {
		$this->tipo = $tipo;
		$this->clave = $clave;
		$this->concepto = $concepto;
		$this->importeGravado = $importeGravado;
		$this->importeExento = $importeExento;
	}
	
	public function __get($name) {
		return $this->$name;
	}
}

class Incapacidad {

	private $dias;
	private $tipo;
	private $descuento;

	public function  __construct($dias, $tipo, $descuento) {
		$this->dias = $dias;
		$this->tipo = $tipo;
		$this->descuento = $descuento;
	}

	public function __get($name) {
		return $this->$name;
	}
}

class HorasExtra {

	private $dias;
	private $tipo;
	private $horasExtra;
	private $importePagado;

	public function  __construct($dias, $tipo, $horasExtra, $importePagado) {
		$this->dias = $dias;
		$this->tipo = $tipo;
		$this->horasExtra = $horasExtra;
		$this->importePagado = $importePagado;
	}

	public function __get($name) {
		return $this->$name;
	}
}
?>