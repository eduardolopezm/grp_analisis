<?php

include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';

class TimbradorTralix extends TimbradorInterface {
	
	private $xsa;
	private $keyfact;
	
	public function __construct($xsa, $keyfact) {
		parent::__construct();
		$this->xsa = $xsa;
		$this->keyfact = $keyfact;
	}
	
	public function timbrarDocumento($data) {
		
		$CadenaEnvio = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cfdi="http://www.sat.gob.mx/cfd/3">
			<soapenv:Header/>
			<soapenv:Body>' . $data . '
			</soapenv:Body>
			</soapenv:Envelope>';

		$host = str_replace('https://', '', $this->xsa);
		$host = str_replace('/', '', $host);
			
		//inicia el envio
		$CurlHandle = curl_init();
		//mandamos encabezado por metodo post
		curl_setopt($CurlHandle, CURLOPT_POST, TRUE);
		//especificamos el host de recepcion
		curl_setopt($CurlHandle, CURLOPT_URL, $this->xsa);
		//agregamos encabezado
		curl_setopt($CurlHandle, CURLOPT_HTTPHEADER, array(
			'Content-Type: text/xml;charset=UTF-8',
			'SOAPAction:"urn:TimbradoCFD"',
			'CustomerKey:' . $this->keyfact,
			'Host: ' . $host,
			'Content-Length: ' . strlen($CadenaEnvio))
		);
		//solicitamos nos devuelva la respuesta en una variable
		curl_setopt($CurlHandle, CURLOPT_RETURNTRANSFER, 1);
		//agregamos el XML generado
		curl_setopt($CurlHandle, CURLOPT_POSTFIELDS, $CadenaEnvio);
		//Solicitamos no verifique los certificados de seguridad del servidor
		curl_setopt($CurlHandle, CURLOPT_SSL_VERIFYPEER, false);
		//enviamos la peticion y la respuesta se almacena en variable
		$cfdi = curl_exec($CurlHandle);
		//en caso de error nos devuelve el numero de error
		$curl_errno = curl_errno($CurlHandle);
		//nos devuelve la descripcion del error
		$curl_error = curl_error($CurlHandle);
		//cerramos la conexion con el servidor
		curl_close($CurlHandle);
		//echo 'entra'.htmlentities($CadenaEnvio);
		$success = ($curl_errno == 0);
		
		if ($success) {
			return $cfdi;
		}
		
		$this->addError($cfdi);
		return null;
	}
	
	public function cancelarDocumento($data) {
		// IMPLEMENTAR CANCELACION TRALIX ... (ESTA DIFICIL)
		return true;
	}
}

?>