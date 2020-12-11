<?php

include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';
include_once realpath(dirname(__FILE__)) . '/../ecodex/class/Seguridad.class.php';
include_once realpath(dirname(__FILE__)) . '/../ecodex/class/Timbrado.class.php';
require_once realpath(dirname(__FILE__)) . '/../ecodex/lib/nusoap.php';

class TimbradorEcodex extends TimbradorInterface {
	
	private $integrador;
	
	public function __construct($integrador) {
		parent::__construct();
		$this->integrador = $integrador;
	}
	
	public function timbrarDocumento($data) {

		$token = new Seguridad();
		$trsID = rand(1, 10000);
		$generaToken = $token->setToken($this->rfcEmisor, $trsID, $this->integrador);
		$getToken = $token->getToken();
		$Timbra = new Timbrado();
		$trsID = rand(1, 10000);
		$timbrar = $Timbra->setTimbrado($data, $this->rfcEmisor, $trsID, $getToken);
		$cfdi = $Timbra->getTimbrado();
		$success = ($timbrar == TRUE);
		
		if ($success) {
			$cfdi = str_replace('<!--?xml version="1.0" encoding="utf-8"?-->', '', $cfdi);
			return $cfdi;
		}
		
		$this->addError($cfdi);
		return null;
	}
	
	public function cancelarDocumento($data) {

		$UIID = $data;
		$token = new Seguridad();
		$trsID = rand(1, 10000);
		$generaToken = $token->setToken($this->rfcEmisor, $trsID, $integrador);
		$getToken = $token->getToken();		
		$Timbra = new Timbrado();
		$trsID = rand(1, 10000);
		$cancelar = $Timbra->setCancela($this->rfcEmisor, $getToken, $trsID, $UIID);
		$response = $Timbra->getCancela();
		$success = ($cancelar == TRUE);
		
		if ($success == false) {
			$this->addError($response);
		}
		
		return $success;
	}
}

?>