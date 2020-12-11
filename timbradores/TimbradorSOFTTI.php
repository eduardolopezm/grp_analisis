<?php
include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';
//include_once '/lib/nusoap.php';

 /*
 DGJ:04-06-2019 TAREA 76873 a produccion 
 */


class TimbradorSOFTTI extends TimbradorInterface {
	
	private $user;
	private $pass;
	private $url = "http://201.120.57.165:81/";
	private $urlstamp;
	private $urlcancel;
	private $rfclogin;
	
	public function __construct($user, $pass, $urlstamp, $urlcancel, $rfclogin) { 
		parent::__construct();
		$this->user = $user;
		$this->pass = $pass;
		$this->urlstamp = $urlstamp;
		$this->urlcancel = $urlcancel;
		$this->rfclogin = $rfclogin;
	}
	public function timbrarDocumento($data) {
		try {
			$success = false;
			// url antes de agregar los cambios para timbrar retenciones
			// $this->url . "facturacion/wsdl/timbrev32.php?wsdl"
			$oSoapClient = new SoapClient($this->urlstamp, array("trace" => 1));
			// Se asigna RFC general Dado por el proveedor
			// Esto debeeria ser dinamico
			$id_sesion = $oSoapClient->fnIniciaSesion($this->rfclogin, $this->user, $this->pass);
			$id_sesion = explode("|", $id_sesion);
			if ($id_sesion[0] == 1) {
				$respuesta = $oSoapClient->Generartimbre($data, $id_sesion[1]);
				if (substr($respuesta, 0, 1) == '(') { // tiene errores
					$respuesta = explode("|", $respuesta);
					$this->addError($respuesta[1]);
				} else {
					$success = true;
					$cfdi = $respuesta;
				}
			} else {
				$this->addError($id_sesion[1]);
			}
			
			if ($success) {
				return $cfdi;
			}
		} catch (SoapFault $e) {
			$this->addError($e->getMessage());
		}
		return null;
	}
	
	/*public function timbrarDocumento($data) {

		//echo "entra";
		try {
			$success = false;
			// url antes de agregar los cambios para timbrar retenciones
			// $this->url . "facturacion/wsdl/timbrev32.php?wsdl"
			//$oSoapClient = new SoapClient($this->urlstamp, array("trace" => 1));
			$arrayLogin = array('rfc' => $this->rfclogin, 'usuario'=> $this->user, 'pass'=> $this->pass );


			$oSoapClient = new nusoap_client($this->urlstamp);  
			// Se asigna RFC general Dado por el proveedor
			// Esto debeeria ser dinamico
			//$id_sesion = $oSoapClient->fnIniciaSesion($this->rfclogin, $this->user, $this->pass);

			$id_sesion = $oSoapClient->call('fnIniciaSesion',$arrayLogin);

			$id_sesion = explode("|", $id_sesion);
			if ($id_sesion[0] == 1) {
				//$respuesta = $oSoapClient->Generartimbre($data, $id_sesion[1]);
				$arrayDatos= array('xml' => $data,'id_sesion'=>$id_sesion[1]);
				$respuesta = $oSoapClient->call('Generartimbre',$arrayDatos );

				if (substr($respuesta, 0, 1) == '(') { 
					// tiene errores
					$respuesta = explode("|", $respuesta);
					$this->addError($respuesta[1]);
				} else {
					$success = true;
					$cfdi = $respuesta;
				}
			} else {
				$this->addError($id_sesion[1]);
			}
			
			if ($success) {
				return $cfdi;
			}
		} catch (SoapFault $e) {
			$this->addError($e->getMessage());
		}
		return null;
	}*/

	/*public function cancelarDocumento($data) {
		
		$success = false;
		
		try {
			$uiid = $data;
			// url antes de agregar los cambios para timbrar retenciones
			// $this->url . "facturacion/wsdl/timbre_cancela.php?wsdl"
			$oSoapClient = new SoapClient($this->urlcancel , array("trace" => 1));
			// Se asigna RFC general Dado por el proveedor
			// Esto debeeria ser dinamico
			$id_sesion = $oSoapClient->fnIniciaSesion($this->rfclogin, $this->user, $this->pass);
			$id_sesion = explode("|",$id_sesion);
			if ($id_sesion[0] == 1) {
				$respuesta = $oSoapClient->CancelarCFDI($uiid, $id_sesion[1]);
				if (substr($respuesta, 0, 1) == '(') { // tiene errores
					$respuesta = explode("|", $respuesta);
					$this->addError($respuesta[1]);
				} else {
					$success = true;
				}
			} else {
				$this->addError($id_sesion[1]);
			}
		} catch (SoapFault $e) {
			$this->addError($e->getMessage());
		}
		return $success;
	}*/

	
	/*public function cancelarDocumento($data) {
			
		$success = false;
		
	 	try {
	 		$uiid = $data;

	 		$oSoapClient = new SoapClient($this->urlcancel, array("trace" => 1)); 

	 		$params = array('rfc' => $this->rfclogin, 
	 						'usuario' => $this->user, 
	 						'password' => $this->pass);

	 		$id_sesion = $oSoapClient->IniciaSesion($params);

	 		if($_SESSION['UserID'] == "desarrollo"){
	 			echo "<pre>";
	 			 echo "<br> rfclogin -> ".$this->rfclogin;
	 			 echo "<br> user -> ".$this->user;
	 			 echo "<br> password -> ".$this->pass;
	 			 echo "  id_sesion -> ";
	 			 print_r($id_sesion);
	 			 echo "</pre>";
	 		}

	 		$id_sesion = explode("|",$id_sesion);
	 		if ($id_sesion[0] == 1) {
	 			$respuesta = $oSoapClient->CancelaCFDI($uiid, $this->rfclogin, $id_sesion[1]);

	 			if (substr($respuesta, 0, 1) == '(') { // tiene errores
	 				$respuesta = explode("|", $respuesta);
	 				$this->addError($respuesta[1]);
	 			} else {
	 				$success = true;
	 			}
	 		} else {
	 			$this->addError($id_sesion[1]);
	 		}
	 	} catch (SoapFault $e) {
	 		$this->addError($e->getMessage());
	 	}
	 	return $success;
	}*/

	public function cancelarDocumento($data) {
			
		$success = false;
        

	 	try {
	 		
			$oSoapClient = new SoapClient($this->urlcancel, array('exceptions' => 0));
	        //$result = $client->SomeFunction();
	        $datos_entrada = array( "rfc" => $this->rfclogin, "usuario" => $this->user, "password" => $this->pass );
	        $id_sesion = $oSoapClient->IniciaSesion($datos_entrada);
	        if (is_soap_fault($id_sesion)) {
	            trigger_error("SOAP Fault: (faultcode: {$id_sesion->faultcode}, faultstring: {$id_sesion->faultstring})", E_USER_ERROR);
	        }
			$uiid = $data;
	 		
	 		$id_sesion = explode("|",$id_sesion->IniciaSesionResult);
	 		if ($id_sesion[0] == 1) {
				$arryDatos = array('uuid' => $uiid, 
	 						'rfc' => $this->rfcEmisor, 
	 						'sesion_id' => $id_sesion[1]);
	 			//$respuesta = $oSoapClient->CancelaCFDI($uiid, $this->rfclogin, $id_sesion[1]);
	 			$respuesta = $oSoapClient->CancelaCFDI($arryDatos);
	 			$xmlres = simplexml_load_string($respuesta->CancelaCFDIResult);

				//convert into json
				$json  = json_encode($xmlres);
				$xmlArr = json_decode($json, true);
	 			if ($xmlArr['Folios']['EstatusUUID'] == 201) {
                        $estatusCancelacion = json_encode($xmlArr['Folios']['EstatusUUID']);
                        //$estatusCancelacion =  str_replace ('"', '', str_replace("\u00f3", "o", $estatusCancelacion)) ;
                        
                        $vrInfo =array($xmlArr['Folios']['EstatusUUID'] );
                        $success = json_encode($vrInfo);
				}elseif($xmlArr['Folios']['EstatusUUID'] == 202){
					$this->addError($xmlArr['Folios']['EstatusUUID']." - Peticion de cancelacion enviada previamente" );
				} elseif( !empty($xmlArr['Folios']['EstatusUUID'] )) {
					$this->addError("Error:".$xmlArr['Folios']['EstatusUUID'] );
				} elseif( !empty($xmlArr['@attributes']['CodEstatus'] )) {
					$this->addError("Error:".$xmlArr['@attributes']['CodEstatus']." Xml mal formado" );
				}
	 		} else {
	 			$this->addError($id_sesion[1]);
	 		}
	 	} catch (SoapFault $e) {
	 		$this->addError($e->getMessage());
	 	}
	 	return $success;
	}

	public function getEstatus($UUID,$rfcEmisor ,$rfcReceptor,$total=0,$config ) // Obtiene el estatus de un documento en el SAT
	{
			try {
					 $datos_entrada = array( "rfc" => $this->rfclogin, "usuario" => $this->user, "password" => $this->pass );
	                $oSoapClient = new SoapClient($this->urlcancel, array('exceptions' => 0));
			        
			        $id_sesion = $oSoapClient->IniciaSesion($datos_entrada);
			        if (is_soap_fault($id_sesion)) {
			           // trigger_error("SOAP Fault: (faultcode: {$id_sesion->faultcode}, faultstring: {$id_sesion->faultstring})", E_USER_ERROR);
			        }
					$uiid = $UUID;
			 		
			 		$id_sesion = explode("|",$id_sesion->IniciaSesionResult);
			 		if ($id_sesion[0] == 1) {
			 			$params = array( "uuid" => $uiid, "emisor" => $rfcEmisor , "receptor" => $rfcReceptor, "total" => $total, "sesion_id"=> $id_sesion[1] );

			 			$respuesta = $oSoapClient->ConsultaEstatus($params);
			 			if (empty($respuesta) == false) {
				 			$xmlres = simplexml_load_string($respuesta->ConsultaEstatusResult);

							//convert into json
							$json  = json_encode($xmlres);
							$xmlArr = json_decode($json, true);
							$estatus  = array();
							//echo "<br> entra".$resultados->EstatusCancelacion;
							$estatus['EsCancelable'] = $xmlArr['EsCancelable'];
							$estatus['CodigoEstatus'] = $xmlArr['CodigoEstatus'];
							$estatus['Estado'] = $xmlArr['Estado'];
							$estatus['EstatusCancelacion'] = $xmlArr['EstatusCancelacion'];

							//exit();

						
						}

			 		}
				


					//
			} catch (Exception $e) {
						//prnMsg("La verificacion del CFDi con el SAT No se ha podido realizar, Conexion al WS del SAT ha fallado. Intenta validarlo mas tarde: " . $uuid, "error");
						$estatus= "0";
				}
	    return $estatus;
	}

}
?>