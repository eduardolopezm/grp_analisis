<?php
include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';

class TimbradorFakeRet extends TimbradorInterface {

	public function __construct(){
		parent::__construct();
	}

	public function timbrarDocumento($data) {
          

		$domXml = new DOMdocument('1.0', 'UTF-8');
		$domXml->formatOutput = true;
		$domXml->loadXml($data);
		// $xml->encoding = 'UTF-8';
		$xpath = new DOMXPath($domXml);
		$xpath->registerNamespace("retenciones", "http://www.sat.gob.mx/TimbreFiscalDigital");
		$comprobante = $xpath->query("/retenciones:Retenciones");

		//Creacion de nodo de Timbre
		$complemento = $domXml->createElement("retenciones:Complemento");

		$TimbreFake = $domXml->createElement("tfd:TimbreFiscalDigital");
		
		$xmlns = $domXml->createAttribute("xmlns:tfd");
		$xmlns->value = "http://www.sat.gob.mx/TimbreFiscalDigital";
		$TimbreFake->appendChild($xmlns);
		$xsi = $domXml->createAttribute("xsi:schemaLocation");
		$xsi->value = "http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd";
		$TimbreFake->appendChild($xsi);
		$version = $domXml->createAttribute("version");
		$version->value = "1.0";
		$TimbreFake->appendChild($version);
		$uuid=$domXml->createAttribute("UUID");
		$uuid->value = "00000000-0000-0000-0000-000000000000";
		$TimbreFake->appendChild($uuid);
		//Fecha Actual
		$datenow = gmdate('Y-m-d\TH:i:s');
		$fechaTimbrado = $domXml->createAttribute("FechaTimbrado");
		$fechaTimbrado->value = "$datenow";
		$TimbreFake->appendChild($fechaTimbrado);
		$selloCFD = $domXml->createAttribute("selloCFD");
		$selloCFD->value="SELLO_CFDI_FAKE";
		$TimbreFake->appendChild($selloCFD);
		$noCertificadoSAT = $domXml->createAttribute("noCertificadoSAT");
		$noCertificadoSAT->value="00001000000100010001";
		$TimbreFake->appendChild($noCertificadoSAT);
		$selloSAT = $domXml->createAttribute("selloSAT");
		$selloSAT->value = "SELLO_SAT_FAKE";
		$TimbreFake->appendChild($selloSAT);
		$complemento->appendChild($TimbreFake);
		
		$comprobante->item(0)->appendChild($complemento);

		$cfdiTimbrado = $domXml->saveXml();

		return $cfdiTimbrado;
	}

	public function cancelarDocumento($data){
		return true;
	}
}