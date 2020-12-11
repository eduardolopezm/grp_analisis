<?php

include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';

class TimbradorFinkokDebug extends TimbradorInterface {

	private $user;
	private $pass;
	private $database;
	private $urlstamp;
	private $urlcancel;
	private $datalog;
	private $iddocto;
	
	const DUPLICITY_ERROR = 307;
	
	public function __construct($user, $pass, $database, $urlstamp, $urlcancel,$iddocto = 0) {
		parent::__construct();
		$this->user = $user;
		$this->pass = $pass;
		$this->database = $database;
		$this->urlstamp = $urlstamp;
		$this->urlcancel = $urlcancel;
		$this->iddocto = $iddocto;
	}

	public function timbrarDocumento($data) {

		$this->datalog = $data;
	
		$params = array(
			"xml" => $data,
			"username" => $this->user,
			"password" => $this->pass
		);
	
		try {
			$client = new SoapClient($this->urlstamp, array('trace' => 1));
			$response = $client->__soapCall("stamp", array($params));
			$cfdi = $response->stampResult->xml;

			echo "<h2>Request Headers:</h2><br>". htmlentities($client->__getLastRequestHeaders()). "<br>";
			echo "<h2>Request :</h2><br>". htmlentities($client->__getLastRequest()). "<br>";
    		echo "<h2>Response Headers:</h2><br>". htmlentities($client->__getLastResponseHeaders()). "<br>";
    		echo "<h2>Response :</h2><br>". htmlentities($client->__getLastResponse()). "<br>";
	
			if (empty($cfdi) == false) {
				return $cfdi;
			} else {
	
				$incidencia = $response->stampResult->Incidencias->Incidencia;
				$this->parseIncidenciaToErrors($incidencia);
	
				if ($this->hasDuplicityError($incidencia)) {
					// Get CFDI that its already signed ...
					$this->limpiarErrores();
					$response = $client->__soapCall("stamped", array($params));
					$cfdi = $response->stampedResult->xml;
					if (empty($cfdi) == false) {
						return $cfdi;
					} else {
						$incidencias = $response->stampedResult->Incidencias->Incidencia;
						$this->parseIncidenciaToErrors($incidencia);
					}
				}
			}
		} catch (SoapFault $e) {
			$this->addError($e->getMessage());
		}
	
		return null;
	}
	
	private function hasDuplicityError($incidencia) {
		if (is_array($incidencia)) {
			foreach ($incidencia as $error) {
				if ($error->CodigoError == self::DUPLICITY_ERROR) {
					return true;
				}
			}
		} else {
			if ($incidencia->CodigoError == self::DUPLICITY_ERROR) {
				return true;
			}
		}
		return false;
	}
	
	private function parseIncidenciaToErrors($incidencia) {
		$errores = array();
		if (is_array($incidencia)) {
			foreach ($incidencia as $error) {
				if (empty($error->CodigoError) == false) {
					$errores[] = "[" . $error->CodigoError . "] " . $error->MensajeIncidencia;
				}
			}
		} else {
			$errores[] = "[" . $incidencia->CodigoError . "] " . $incidencia->MensajeIncidencia;
		}
	
		if (empty($errores) == false) {
			$this->addError(implode('<br />', $errores));
		} else {
			$this->addError("Error Desconocido");
		}
	}

	public function cancelarDocumento($data) {
		
		$this->datalog = $data;
		$success = false;
		$uiid = $data;

		$certificado = "";
		$legalname = "";
		if ($this->getDb() != null) {
			$sql = "SELECT FileSAT, legalname FROM legalbusinessunit WHERE taxid = '{$this->rfcEmisor}'";
			$rs = DB_query($sql, $this->getDb());
			if ($row = DB_fetch_array($rs)) {
				$certificado = $row["FileSAT"];
				$legalname = $row["legalname"];
			}
		}

		$path = "/var/www/html" . dirname($_SERVER['PHP_SELF']) . "/companies/" . $this->database . "/SAT/" . str_replace('.', '', str_replace(' ', '', $legalname)) . "/";

		$cer_path = $path . $certificado . ".cer.pem";
		$cer_file = fopen($cer_path, "r");
		$cer_content = fread($cer_file, filesize($cer_path));
		fclose($cer_file);

		$key_path = $path . $certificado . ".key.pem";
		$key_file = fopen($key_path, "r");
		$key_content = fread($key_file, filesize($key_path));
		fclose($key_file);
		
		$invoices = array($uiid);

		$params = array(
			"UUIDS" => array('uuids' => $invoices),
			"username" => $this->user,
			"password" => $this->pass,
			"taxpayer_id" => $this->getRfcEmisor(),
			"cer" => $cer_content,
			"key" => $key_content
		);

		try {
			$client = new SoapClient($this->urlcancel);
			$response = $client->__soapCall("cancel", array($params));
			
			$cancelResult = $response->cancelResult;
			if (empty($cancelResult->Folios) == false) {
				
				foreach ($cancelResult->Folios as $folio) {
					
					if ($folio->EstatusUUID == 201) {
						
						$success = true;
					} else {
						
						$this->addError($this->getCancelError($folio->EstatusUUID));
					}
					break;
				}
			} else {
				$this->addError($response->cancelResult->CodEstatus);
			}
		
		} catch (SoapFault $e) {
			$this->addError($e->getMessage());
		}

		return $success;
	}

	private function getCancelError($code) {
		
		$error = "";
		switch ($code) {
			case 202: $error = "UUID Previamente cancelado"; break;
			case 203: $error = "UUID No corresponde el RFC del Emisor y de quien solicita la cancelaci�n"; break;
			case 205: $error = "UUID No existe"; break;
			default: $error = "Error desconocido";
		}

		return $error;
	}
	
	// @Override Method
	public function addError($error) {
		parent::addError($error);
		if ($this->db != null) {
			$data = addslashes($this->datalog);
			
			$sql = "INSERT INTO logtimbrado (data, error, date, iddocto) VALUES ('$data', '$error', NOW(),'" . $this->iddocto . "')";
			DB_query($sql, $this->getDb());
		}
	}
}