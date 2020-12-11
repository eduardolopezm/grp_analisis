<?php
/*
 DGJ: TAREA 77899 a produccion 
 */
include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';
class TimbradorFinkok extends TimbradorInterface {

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

		$this->datalog = utf8_decode($data);
	
		$params = array(
			"xml" => $data,
			"username" => $this->user,
			"password" => $this->pass
		);
	
		try {
			$client = new SoapClient($this->urlstamp);
			$response = $client->__soapCall("stamp", array($params));
			$cfdi = $response->stampResult->xml;
	
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

		
		// var_dump($incidencia);
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
        $vrInfo = array();

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

        if ($_SESSION['UserID'] == 'desarrollo') {
             //echo '<br>path: ';
             //print_r($path);
        }
		//$path = utf8_encode($path); Se comentó porque causaba problemas con la ñ en matel
		
       
		$cer_path = $path . $certificado . ".cer.pem";
		$cer_path = utf8_encode($cer_path);
		if (file_exists($cer_path)) {

		}else{
			//echo "<br> No existe: ".$cer_path;
		}
        $cer_file = fopen($cer_path, "r");
		$cer_content = fread($cer_file, filesize($cer_path));
		fclose($cer_file);

        $key_path = $path . $certificado . ".key.pem";
        $key_path = utf8_encode($key_path);
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
					
					if ($folio->EstatusUUID == 201 or $folio->EstatusUUID == 1201 or $folio->EstatusUUID == 1202) {
                        $estatusCancelacion = json_encode($folio->EstatusCancelacion);
                        $estatusCancelacion =  str_replace ('"', '', str_replace("\u00f3", "o", $estatusCancelacion)) ;
                        
                        $vrInfo =array($folio->EstatusUUID, $estatusCancelacion);
                        $success = json_encode($vrInfo);
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

	public function getEstatus($UUID,$rfcEmisor ,$rfcReceptor,$total=0,$config ) // Obtiene el estatus de un documento en el SAT
	{
		$estatus = "No encontrado";
			try {

					//var_dump($params );
					//$url = "https://demo-facturacion.finkok.com/servicios/soap/cancel.wsdl";
					$url = $config['FinkokUrlCancel'];
					$params = array(
						"username" => $config['FinkokUser'] ,
						"password" => $config['FinkokPass'],
						"taxpayer_id" => $rfcEmisor,
						"rtaxpayer_id" => $rfcReceptor,
						"uuid"=>$UUID,
						"total"=>$total
						
					);
	                $client = new SoapClient($url);
	                
					# Petición al web service
					$response = $client->__soapCall("get_sat_status", array($params));
					# Petición al web service
					$cancelResult = $response->get_sat_statusResult;
				//echo "<br>Estatus: ".json_encode($cancelResult)."<br>";
				if (empty($cancelResult) == false) {
					$resultados = $cancelResult->sat;
						//echo "<br>Estatus: ".json_encode($resultados)."<br>";
						$estatus  = array();
						//echo "<br> entra".$resultados->EstatusCancelacion;
						$estatus['EsCancelable'] = $resultados->EsCancelable;
						$estatus['CodigoEstatus'] = $resultados->CodigoEstatus;
						$estatus['Estado'] = $resultados->Estado;
						if( strtoupper( $estatus['EsCancelable']) =='NO CANCELABLE'){
		                    $estatus['EstatusCancelacion'] = $estatus['EsCancelable'];
		                }else{
		                    $estatus['EstatusCancelacion'] = $resultados->EstatusCancelacion;
		                }
						//$estatus['EstatusCancelacion'] = $resultados->EstatusCancelacion;

					
				}


					//
			} catch (Exception $e) {
						//prnMsg("La verificacion del CFDi con el SAT No se ha podido realizar, Conexion al WS del SAT ha fallado. Intenta validarlo mas tarde: " . $uuid, "error");
						$estatus= "0";
				}
	    return $estatus;
	}
	

	private function getCancelError($code) {
		
		$error = "";
		/*switch ($code) {
			case 'no_cancelable': $error = "El UUID contiene CFDI relacionados"; break;
            case 202: $error = "Petición de cancelación realizada Previamente"; break;
			case 203: $error = "UUID No corresponde el RFC del Emisor y de quien solicita la cancelación"; break;
			case 205: $error = "UUID No existe"; break;
			default: $error = "Error desconocido";
		}*/
		if($code == 'no_cancelable'){
			$code = -1;
		}
		$SQL = "SELECT * FROM debtorerroresfinkok WHERE codigo ='".$code."' ";
		$resultado = DB_query($SQL, $this->getDb());
		if(DB_num_rows($resultado)){
			$myrow = DB_fetch_array($resultado);
			$error = $myrow['codigo']." - ".$myrow['descripcion'] ;
		}else{
			$error = $code." - Error desconocido";
		}



		return $error;
	}
	
	// @Override Method
	public function addError($error) {
		parent::addError($error);
		if ($this->db != null) {
			$data = addslashes($this->datalog);
			
			$sql = "INSERT INTO logtimbrado (data, error, date, iddocto) VALUES ('$data', '$error', NOW(),'" . $this->iddocto . "')";
			// DB_query($sql, $this->getDb());
		}
	}
}


?>