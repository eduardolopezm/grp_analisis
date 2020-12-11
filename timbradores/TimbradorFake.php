<?php
// 
include_once realpath(dirname(__FILE__)) . '/TimbradorInterface.php';

class TimbradorFake extends TimbradorInterface {

	public function __construct(){
		parent::__construct();
	}

	public function timbrarDocumento($data) {

		$domXml = new DOMdocument('1.0', 'UTF-8');
		$domXml->formatOutput = true;
		$domXml->loadXml($data);
		// $xml->encoding = 'UTF-8';
		$xpath = new DOMXPath($domXml);
		$xpath->registerNamespace("cfdi", "http://www.sat.gob.mx/cfd/2");

		// $comprobante = $xpath->query("/cfdi:Comprobante");
		$complemento = $xpath->query("//cfdi:Complemento");

		if($xpath->query("/retenciones:Retenciones")){
			$comprobante = $xpath->query("/retenciones:Retenciones");
		}else{
			$comprobante = $xpath->query("/cfdi:Comprobante ");
		}
		
		try{
			$ncomprobante = $comprobante->item(0);

			$version = $ncomprobante->getAttribute('Version');
			if(empty($version)) {
				$version = $ncomprobante->getAttribute('version');	    	
			}
			
		}catch(Exception $e){
			echo $e;
		}
		

	    if($version == '3.3') {
	    	$etiquetaSelloSAT = 'SelloSAT';
		    $etiquetaSelloCFD = 'SelloCFD';
		    $etiquetaNoCertificadoSAT = 'NoCertificadoSAT';
		    $etiquetaVersion = 'Version';
		    $schemaLocation = "http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd";
		    $valorVersion = "1.1";
	    } else {
	    	$etiquetaSelloSAT = 'selloSAT';
		    $etiquetaSelloCFD = 'selloCFD';
		    $etiquetaNoCertificadoSAT = 'noCertificadoSAT';
		    $etiquetaVersion = 'version';
		    $schemaLocation = "http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd";
		    $valorVersion = "1.0";
	    }

		/*$existcomplement = true;
		if($complemento == null) {
			$existcomplement = false;
			//Creacion de nodo de Timbre
			$complemento = $domXml->createElement("cfdi:Complemento");
		}*/
		$existcomplement = false;
		foreach ($complemento as $entry) {
			$existcomplement = true;
		}
		
		if(!$existcomplement) {
			$existcomplement = false;
			//Creacion de nodo de Timbre
			$complemento = $domXml->createElement("cfdi:Complemento");
		}

		$TimbreFake = $domXml->createElement("tfd:TimbreFiscalDigital");
		$xmlns = $domXml->createAttribute("xmlns:tfd");
		$xmlns->value = "http://www.sat.gob.mx/TimbreFiscalDigital";
		$TimbreFake->appendChild($xmlns);
		$xsi = $domXml->createAttribute("xsi:schemaLocation");
		$xsi->value = "$schemaLocation";
		$TimbreFake->appendChild($xsi);

		$version = $domXml->createAttribute($etiquetaVersion);
		$version->value = "$valorVersion";
		$TimbreFake->appendChild($version);

		$uuid=$domXml->createAttribute("UUID");
		$uuid->value = "00000000-0000-0000-0000-000000000000";
		$TimbreFake->appendChild($uuid);
		
		$datenow = gmdate('Y-m-d\TH:i:s');
		$fechaTimbrado = $domXml->createAttribute("FechaTimbrado");
		$fechaTimbrado->value = "$datenow";
		$TimbreFake->appendChild($fechaTimbrado);

		$selloCFD = $domXml->createAttribute($etiquetaSelloCFD);
		$selloCFD->value="SELLO_CFDI_FAKE";
		$TimbreFake->appendChild($selloCFD);
		$noCertificadoSAT = $domXml->createAttribute($etiquetaNoCertificadoSAT);
		$noCertificadoSAT->value="00001000000100010001";
		$TimbreFake->appendChild($noCertificadoSAT);
		$selloSAT = $domXml->createAttribute($etiquetaSelloSAT);
		$selloSAT->value = "SELLO_SAT_FAKE";
		$TimbreFake->appendChild($selloSAT);

		if($existcomplement) {
			$complemento->item(0)->appendChild($TimbreFake);
		} else {
			$complemento->appendChild($TimbreFake);
			$comprobante->item(0)->appendChild($complemento);	
		}

		$cfdiTimbrado = $domXml->saveXml();

		return $cfdiTimbrado;
	}

	public function cancelarDocumento($data){
		return true;
	}

	public function getEstatus($UUID,$rfcEmisor ,$rfcReceptor,$total=0,$config ) // Obtiene el estatus de un documento en el SAT
	{
		
			
		$estatus['EsCancelable'] = "Cancelable sin aceptacion";
		$estatus['CodigoEstatus'] = "201";
		$estatus['Estado'] = "Cancelado";
		$estatus['EstatusCancelacion'] = "Cancelado sin aceptacion";

					
				
	    return $estatus;
	}
}
?>
