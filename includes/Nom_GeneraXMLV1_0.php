<?php

/**
 * Funcion que genera el XML de la nomina a partir de la instancia de la clase Nomina (includes/nomina.class.php)
 * @param Comprobante $comprobante
 * @param Resource $db
 * @return String 
 */
function generaXMLReciboNomina($comprobante, $db) {
	
	
	// XML de muestra generado a partir del XSD de la siguiente URL http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina11.xsd
	// Liga donde se genero el XML Muestra http://www.xsd2xml.com/
	/*
	<?xml version="1.0" encoding="utf-8"?>
	<Nomina Version="1.1" RegistroPatronal="str1234" NumEmpleado="str1234" CURP="str123400000000000" TipoRegimen="123" NumSeguridadSocial="str1234" FechaPago="2012-12-13" FechaInicialPago="2012-12-13" FechaFinalPago="2012-12-13" NumDiasPagados="123.45" Departamento="str1234" CLABE="1234" Banco="123" FechaInicioRelLaboral="2012-12-13" Antiguedad="123" Puesto="str1234" TipoContrato="str1234" TipoJornada="str1234" PeriodicidadPago="str1234" SalarioBaseCotApor="123.45" RiesgoPuesto="123" SalarioDiarioIntegrado="123.45">
	  <Percepciones TotalGravado="123.45" TotalExento="123.45">
	    <Percepcion TipoPercepcion="123" Clave="str1234" Concepto="str1234" ImporteGravado="123.45" ImporteExento="123.45" />
	  </Percepciones>
	  <Deducciones TotalGravado="123.45" TotalExento="123.45">
	    <Deduccion TipoDeduccion="123" Clave="str1234" Concepto="str1234" ImporteGravado="123.45" ImporteExento="123.45" />
	  </Deducciones>
	  <Incapacidades>
	    <Incapacidad DiasIncapacidad="123.45" TipoIncapacidad="123" Descuento="123.45" />
	  </Incapacidades>
	  <HorasExtras>
	    <HorasExtra Dias="123" TipoHoras="Dobles" HorasExtra="123" ImportePagado="123.45" />
	  </HorasExtras>
	</Nomina>
	*/
	
	$emisor = $comprobante->emisor;
	$receptor = $comprobante->receptor;
	$impuestosComp = $comprobante->impuestos;
	$nomina = $comprobante->nomina;
	
	$xml = new DOMdocument('1.0', 'UTF-8');
	$xml->formatOutput = true;
	
	###################
	// Nodo Comprobante
	$root = $xml->createElement("cfdi:Comprobante");
	$root = $xml->appendChild($root);
	
	cargaAtt($root, array(
			"xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
			"xmlns:cfdi" => "http://www.sat.gob.mx/cfd/3",
			"xmlns:nomina" => "http://www.sat.gob.mx/nomina",
			"xmlns:ecfd" => "http://www.southconsulting.com/schemas/strict",
			"xsi:schemaLocation" => "http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/nomina http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina11.xsd"
		)
	);
	
	$certificado = $comprobante->noCertificado;
	$legalnamePath = str_replace('.', '', str_replace(' ', '', $emisor->nombre));
	
	cargaAtt($root, array(
			"version" => $comprobante->version,
			"serie" => $comprobante->serie,
			"folio" => $comprobante->folio,
			"fecha" => str_replace(' ', 'T', str_replace('/', '-', $comprobante->fecha)),
			"sello" => $comprobante->sello,
			"tipoDeComprobante" => $comprobante->tipoDeComprobante,
			"formaDePago" => $comprobante->formaDePago,
			"noCertificado" => $comprobante->noCertificado,
			"certificado" => $comprobante->certificado,
			"subTotal" => $comprobante->subTotal,
			"descuento" => $comprobante->descuento,
			"total" => $comprobante->total,
			"metodoDePago" => $comprobante->metodoDePago,
			"TipoCambio" => $comprobante->tipoCambio,
			"Moneda" => $comprobante->moneda,
			"LugarExpedicion" => $comprobante->lugarExpedicion,
			"NumCtaPago" => $comprobante->numCtaPago
		)
	);
	
	##############
	// Nodo Emisor
	$emisorNode = $xml->createElement("cfdi:Emisor");
	$emisorNode = $root->appendChild($emisorNode);
	cargaAtt($emisorNode, array(
			"rfc" => $emisor->rfc,
			"nombre" => $emisor->nombre
		)
	);
	
	###################
	// Domicilio Fiscal
	$domfis =  $xml->createElement("cfdi:DomicilioFiscal");
	$domfis = $emisorNode->appendChild($domfis);
	cargaAtt($domfis, array(
			"calle" => $emisor->domicilioFiscal->calle,
			"noExterior" => $emisor->domicilioFiscal->noExterior,
			"noInterior"=> $emisor->domicilioFiscal->noInterior,
			"colonia" => $emisor->domicilioFiscal->colonia,
			"referencia" => $emisor->domicilioFiscal->referencia,
			"municipio" => $emisor->domicilioFiscal->municipio,
			"estado"=> $emisor->domicilioFiscal->estado,
			"pais" => $emisor->domicilioFiscal->pais,
			"codigoPostal" => $emisor->domicilioFiscal->codigoPostal
		)
	);
	
	###################
	// Nodo Expedido En
	$expedido = $xml->createElement("cfdi:ExpedidoEn");	
	$expedido = $emisorNode->appendChild($expedido);
	cargaAtt($expedido, array(
			"calle" => $emisor->expedidoEn->calle,
			"noExterior" => $emisor->expedidoEn->noExterior,
			"noInterior" => $emisor->expedidoEn->noInterior,
			"colonia" => $emisor->expedidoEn->colonia,
			"referencia" => $emisor->expedidoEn->referencia,
			"municipio" => $emisor->expedidoEn->municipio,
			"estado" => $emisor->expedidoEn->estado,
			"pais" => $emisor->expedidoEn->pais,
			"codigoPostal" => $emisor->expedidoEn->codigoPostal
		)
	);
	
	######################
	// Nodo Regimen Fiscal
	$regimenfiscal = $xml->createElement("cfdi:RegimenFiscal");
	$regimenfiscal = $emisorNode->appendChild($regimenfiscal);
	cargaAtt($regimenfiscal, array(
			"Regimen" => $emisor->regimenFiscal
		)
	);
	
	################
	// Nodo Receptor
	$receptorNode = $xml->createElement("cfdi:Receptor");
	$receptorNode = $root->appendChild($receptorNode);
	cargaAtt($receptorNode, array(
			"rfc" => $receptor->rfc,
			"nombre" => $receptor->nombre
		)
	);
	
	#####################
	// Domicilio Receptor
	$domicilioRecep = $xml->createElement("cfdi:Domicilio");
	$domicilioRecep = $receptorNode->appendChild($domicilioRecep);
	cargaAtt($domicilioRecep, array(
			"calle" => $receptor->domicilio->calle,
			"noExterior" => $receptor->domicilio->noExterior,
			"noInterior" => $receptor->domicilio->noInterior,
			"colonia" => $receptor->domicilio->colonia,
			"referencia" => $receptor->domicilio->referencia,
			"localidad" => $receptor->domicilio->localidad,
			"municipio" => $receptor->domicilio->municipio,
			"estado" => $receptor->domicilio->estado,
			"codigoPostal" => $receptor->domicilio->codigoPostal,
			"pais" => $receptor->domicilio->pais
		)
	);
	
	##########################################################
	// Conceptos (Un solo concepto para la nomina, cantidad 1)
	$conceptos = $xml->createElement("cfdi:Conceptos");
	$conceptos = $root->appendChild($conceptos);
	
	foreach ($comprobante->conceptos as $conceptoComprobante) {
		$concepto = $xml->createElement("cfdi:Concepto");
		$concepto = $conceptos->appendChild($concepto);
		cargaAtt($concepto, array(
				"cantidad" =>  $conceptoComprobante->cantidad,
				"unidad" =>  $conceptoComprobante->unidad,
				"noIdentificacion" =>  $conceptoComprobante->noIdentificacion,
				"descripcion" =>  $conceptoComprobante->descripcion,
				"valorUnitario" =>  $conceptoComprobante->valorUnitario,
				"importe" =>  $conceptoComprobante->importe
			)
		);
	}
	
	#################
	// Nodo Impuestos
	$impuestos = $xml->createElement("cfdi:Impuestos");
	$impuestos = $root->appendChild($impuestos);
	cargaAtt($impuestos, array(
			"totalImpuestosRetenidos" => $impuestosComp->totalImpuestosRetenidos,
			"totalImpuestosTrasladados" => $impuestosComp->totalImpuestosTrasladados
		)
	);
	
	###################
	// Nodo Retenciones
	$Retenciones = $xml->createElement("cfdi:Retenciones");
	$Retenciones = $impuestos->appendChild($Retenciones);
	
	foreach ($impuestosComp->retenciones as $ret) {
		$retenido = $xml->createElement("cfdi:Retencion");
		$retenido = $Retenciones->appendChild($retenido);
		cargaAtt($retenido, array(
				"impuesto" => $ret->impuesto,
				"importe" => $ret->importe
			)
		);
	}
	
	#################
	// Nodo Traslados
	$traslados = $xml->createElement("cfdi:Traslados");
	$traslados = $impuestos->appendChild($traslados);
	
	foreach ($impuestosComp->traslados as $tras) {
		$traslado = $xml->createElement("cfdi:Traslado");
		$traslado = $traslados->appendChild($traslado);
		cargaAtt($traslado, array(
				"impuesto" => $tras->impuesto,
				"tasa" => $tras->tasa,
				"importe" => $tras->importe
			)
		);
	}
	
	##################
	// Nodo Compemento
	$complemento = $xml->createElement("cfdi:Complemento");
	$complemento = $root->appendChild($complemento);
	
	##############
	// Nodo Nomina
	$nominaNode = $xml->createElement("nomina:Nomina");
	$nominaNode = $complemento->appendChild($nominaNode);
	cargaAtt($nominaNode, array(
			"Version" => $nomina->version,
			"RegistroPatronal" => $nomina->registroPatronal,
			"NumEmpleado" => $nomina->numEmpleado,
			"CURP" => $nomina->curp,
			"TipoRegimen" => $nomina->tipoRegimen,
			"NumSeguridadSocial" => $nomina->numSeguridadSocial,
			"FechaPago" => date('Y-m-d', strtotime($nomina->fechaPago)),
			"FechaInicialPago" => date('Y-m-d', strtotime($nomina->fechaInicialPago)),
			"FechaFinalPago" => date('Y-m-d', strtotime($nomina->fechaFinalPago)),
			"NumDiasPagados" => $nomina->numDiasPagados,
			"Departamento" => $nomina->departamento,
			"CLABE" => $nomina->clabe,
			"Banco" => $nomina->banco,
			"FechaInicioRelLaboral" => date('Y-m-d', strtotime($nomina->fechaInicioRelLaboral)),
			"Antiguedad" => $nomina->antiguedad,
			"Puesto" => $nomina->puesto,
			"TipoContrato" => $nomina->tipoContrato,
			"TipoJornada" => $nomina->tipoJornada,
			"PeriodicidadPago" => $nomina->periodicidadPago,
			"SalarioBaseCotApor" => $nomina->salarioBaseCotApor,
			"RiesgoPuesto" => $nomina->riesgoPuesto,
			"SalarioDiarioIntegrado" => $nomina->salarioDiarioIntegrado
		)
	);
	
	####################
	// Nodo Percepciones
	if ($nomina->tienePercepciones()) {
		$percepciones = $xml->createElement("nomina:Percepciones");
		$percepciones = $nominaNode->appendChild($percepciones);
		cargaAtt($percepciones, array(
				"TotalGravado" => $nomina->percepciones->totalGravado,
				"TotalExento" => $nomina->percepciones->totalExento
			)
		);
		
		foreach ($nomina->percepciones->conceptos as $percepcionObj) {
			$percepcion = $xml->createElement("nomina:Percepcion");
			$percepcion = $percepciones->appendChild($percepcion);
			cargaAtt($percepcion, array(
					"TipoPercepcion" => str_pad($percepcionObj->tipo, 3, "0", STR_PAD_LEFT),
					"Clave" => str_pad($percepcionObj->clave, 3, "0", STR_PAD_LEFT),
					"Concepto" => $percepcionObj->concepto,
					"ImporteGravado" => $percepcionObj->importeGravado,
					"ImporteExento" => $percepcionObj->importeExento
				)
			);
		}
	}
	
	###################
	// Nodo Deducciones
	if ($nomina->tieneDeducciones()) {
		$deducciones = $xml->createElement("nomina:Deducciones");
		$deducciones = $nominaNode->appendChild($deducciones);
		cargaAtt($deducciones, array(
				"TotalGravado" => $nomina->deducciones->totalGravado,
				"TotalExento" => $nomina->deducciones->totalExento
			)
		);
		
		foreach ($nomina->deducciones->conceptos as $deduccionObj) {
			$deduccion = $xml->createElement("nomina:Deduccion");
			$deduccion = $deducciones->appendChild($deduccion);
			cargaAtt($deduccion, array(
					"TipoDeduccion" => str_pad($deduccionObj->tipo, 3, "0", STR_PAD_LEFT),
					"Clave" => str_pad($deduccionObj->clave, 3, "0", STR_PAD_LEFT),
					"Concepto" => $deduccionObj->concepto,
					"ImporteGravado" => $deduccionObj->importeGravado,
					"ImporteExento" => $deduccionObj->importeExento
				)
			);
		}
	}
	
	#####################
	// Nodo Incapacidades
	if ($nomina->tieneIncapacidades()) {
		$incapacidades = $xml->createElement("nomina:Incapacidades");
		$incapacidades = $nominaNode->appendChild($incapacidades);
		
		foreach ($nomina->incapacidades as $incapacidadObj) {
			$incapacidad = $xml->createElement("nomina:Incapacidad");
			$incapacidad = $incapacidades->appendChild($incapacidad);
			cargaAtt($incapacidad, array(
					"DiasIncapacidad" => $incapacidadObj->dias,
					"TipoIncapacidad" => $incapacidadObj->tipo,
					"Descuento" => $incapacidadObj->descuento
				)
			);
		}
	}
	
	####################
	// Nodo Horas Extras
	if ($nomina->tieneHorasExtras()) {
		$horasExtras = $xml->createElement("nomina:HorasExtras");
		$horasExtras = $nominaNode->appendChild($horasExtras);
		
		foreach ($nomina->horasExtras as $horasExtraObj) {
			$horasExtra = $xml->createElement("nomina:HorasExtra");
			$horasExtra = $horasExtras->appendChild($horasExtra);
			cargaAtt($horasExtra, array(
					"Dias" => $horasExtraObj->dias,
					"TipoHoras" => $horasExtraObj->tipo,
					"HorasExtra" => $horasExtraObj->horasExtra,
					"ImportePagado" => $horasExtraObj->importePagado
				)
			);
		}
	}
	
	// Generar cadena
	$xmlStr = $xml->saveXML();
	$cadenasellar = generarCadena($xmlStr);
	
	$baseDeDatos = "";
	
	$sql = "SELECT DATABASE()";
	
	$rs = DB_query($sql, $db);
	
	if ($row = DB_fetch_array($rs)) {
		$baseDeDatos = $row[0];
	}
	
	$ruta = "/var/www/html" . dirname($_SERVER['PHP_SELF']) . "/companies/" . $baseDeDatos . "/SAT/" . $legalnamePath . "/";
	$file = $ruta . $certificado . ".key.pem";
	$pkeyid = openssl_get_privatekey(file_get_contents($file));
	openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
	openssl_free_key($pkeyid);
	$sello = base64_encode($crypttext);
	$root->setAttribute("sello", $sello);
	$file = $ruta . $certificado . ".cer.pem";
	
	$datos = file($file);
	$certificado = ""; 
	$carga = false;
	for ($i = 0; $i < sizeof($datos); $i++) {
		if (strstr($datos[$i], "END CERTIFICATE")) { 
			$carga = false;
		}
		if ($carga) {
			$certificado .= trim($datos[$i]);
		}
		if (strstr($datos[$i], "BEGIN CERTIFICATE")) {
			$carga = true;
		}
	}
	
	$root->setAttribute("certificado", $certificado);

	$xmlStr = $xml->saveXML();
	
	return $xmlStr;
}

function generarCadena($xml) {
	
	// Ruta al documento XSLT del SAT para generar la cadena
	$rutaXslt = "http://www.sat.gob.mx/sitio_internet/cfd/3/cadenaoriginal_3_2/cadenaoriginal_3_2.xslt";
	$cadena = "";
	
	try {
		$xmlObj = new DOMDocument();
		$xmlObj->loadXML($xml);
		
		$xsl = new DOMDocument();
		$xsl->load($rutaXslt);
		
		$proc = new XSLTProcessor();
		$proc->importStyleSheet($xsl);
		
		$cadena = $proc->transformToXML($xmlObj);
		
	} catch (Exception $exp) {
		echo $exp->getMessage();	
	}
	
	return $cadena;
}
?>
