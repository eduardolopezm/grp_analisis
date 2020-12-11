<?php
include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';

class TimbradorInvoiceOne extends TimbradorInterface {
	
	private $user;
	private $pass;
	
	public function __construct($user, $pass) {
		parent::__construct();
		$this->user = $user;
		$this->pass = $pass;
	}
	
	public function timbrarDocumento($data) {
		
	}
	
	public function cancelarDocumento($data) {
		
	}
}

?>