<?php
/**
 * Interface que implementan todos los timbradores
 * 
 * @package    timbradores
 * @author     jahepi
 * @version    1.1
 */
abstract class TimbradorInterface {
	
	/**
	 * Referencia a la base de datos del ERP
	 * @var resourse
	 */
	protected $db;
	/**
	 * RFC del emisor
	 * @var String
	 */
	protected $rfcEmisor;
	/**
	 * Arreglo que contiene los errores
	 * @var Array
	 */
	protected $errores;
	
	public function __construct() {
		$this->errores = array();
	}
	
	/**
	 * Metodo abstracto
	 * @param String $data XML que se ve a timbrar 
	 * @return String Regresa el xml timbrado
	 */
	abstract public function timbrarDocumento($data);
	
	/**
	 * Metodo abstracto
	 * @param String $data UIID que se va a cancelar
	 * @return Boolean
	 */
	abstract public function cancelarDocumento($data);
	
	public function tieneErrores() {
		return (empty($this->errores) == FALSE);
	}
	
	public function addError($error) {
		$this->errores[] = $error;
	}
	
	public function limpiarErrores() {
		$this->errores = array();
	}
	
	public function getErrores() {
		return $this->errores;
	}
	
	public function setRfcEmisor($rfc) {
            
		$this->rfcEmisor = $rfc;
	}
	
	public function getRfcEmisor() {
		return $this->rfcEmisor;
	}
	
	public function setDb($db) {
		$this->db = $db;
	}
	
	public function getDb() {
		return $this->db;
	}
}
?>