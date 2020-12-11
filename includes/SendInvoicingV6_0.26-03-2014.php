<?php
/*
 CGM 26/12/2013 Se agrego generacion de addenda en funcion para comprobantes de tipo cfdi
 
 CGM 9/12/2013  Si tipofacturacionxtag=11 entonces nota de credito
 				Si no sera = a 1 que son configuraciones para documentos de cargo (Facturas, remisiones,etc)
 				Se agrego validacion de tipo de documento para notas de credito;
 				
*/

function AgregaAddendaXML($xmlSat,$debtorno,$iddocto,$db){
	//Carga el xmlSat para modificarlo.
	$xml=new DOMdocument();
	$xml->loadXml($xmlSat);
	$root=new DOMXPath($xml);
	$comprobante=$root->query("/cfdi:Comprobante");
	$emisor=$root->query("/cfdi:Comprobante/cfdi:Receptor");
	$rfccliente=$emisor->item(0)->getAttribute("rfc");
	$root = $comprobante->item(0);
	
	$SQL = "
			SELECT custbranch.typeaddenda,typeaddenda.archivoaddenda
			FROM  custbranch
			INNER JOIN typeaddenda on custbranch.typeaddenda=typeaddenda.id_addenda
			WHERE taxid='".$rfccliente."'
					AND debtorno='" . $debtorno."'";
	$Result= DB_query($SQL,$db);
	if(DB_num_rows($Result) == 0) {
		$typeaddenda=0;
	} else {
		$myrowpag = DB_fetch_array($Result);
		$typeaddenda=$myrowpag['typeaddenda'];
		$fileaddenda=$myrowpag['archivoaddenda'];
	}
	
	if($typeaddenda > 0) {
		
		// Remover addendas en caso de que tenga - @inicio
		$addendas = array();
		$xmlRoot = $xml->documentElement;
		foreach ($xmlRoot->getElementsByTagName('Addenda') as $addenda) {
			$addendas[] = $addenda;
		}
		
		foreach ($addendas as $addenda) {
			$xmlRoot->removeChild($addenda);
		}
		// Remover addendas en caso de que tenga - @fin
		
		include_once($fileaddenda);
		
		//$comprobante->item(0)->appendChild($addenda);
		
		//$xml->formatOutput = true;
		//$xmlwhitAddenda = $xml->saveXML();
		
		$xml->formatOutput = true;
		$xmlwhitAddenda = $xml->saveXML();
			
	}else{
		$xmlwhitAddenda=$xmlSat;
	}
	
	
	return $xmlwhitAddenda;
}

function generaXMLIntermedio($txtinput,$xml,$cadenaOriginal,$cantidadLetra,$orderNo,$db,$tipofacturacionxtag,$tagref,$debtorId=0){
	// 	echo "<br>TXT:".$txtinput;
	// Se eliminan los nasmespaces para evitar error de validacion de estructura de xml
	$xmlSat=str_replace('xmlns="http://www.sat.gob.mx/cfd/2"','',$xml);
	$xmlSat=str_replace('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"','',$xmlSat);
	$xmlSat=str_replace('xsi:schemaLocation="http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd"','',$xmlSat);
	$xmlSat=str_replace('xmlns:cfdi="http://www.sat.gob.mx/cfd/3"','',$xmlSat);
	$xmlSat=str_replace('xmlns:ecfd="http://www.southconsulting.com/schemas/strict"','',$xmlSat);
	$xmlSat=str_replace('xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd"','',$xmlSat);
	$xmlSat=str_replace('xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd"','',$xmlSat);
	$xmlSat=str_replace('xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"','',$xmlSat);
	$xmlSat=str_replace('xmlns:nomina="http://www.sat.gob.mx/nomina"','',$xmlSat);
	$xmlSat=str_replace('cfdi:','',$xmlSat);
	$xmlSat=str_replace('tfd:','',$xmlSat);
	$xmlSat=str_replace('implocal:','',$xmlSat);
	$xmlSat=str_replace('nomina:','',$xmlSat);
	
	//Carga el xmlSat para modificarlo.
	$domXml=new DOMdocument();
	$domXml->loadXml($xmlSat);
	
	if($tipofacturacionxtag!=1 and strlen($tipofacturacionxtag)>0){
		$tipodocto=$tipofacturacionxtag;
	}else{
		// documento de tipo factura
		$tipodocto=1;
	}
	

	//Cargamos el xpath.
	$xpath=new DOMXPath($domXml);
	$comprobante=$xpath->query("/Comprobante");
	$emisor=$xpath->query("/Comprobante/Emisor");
	//	echo '<br><pre>emisor:'.htmlentities($xml);
	$rfcEmisor=$emisor->item(0)->getAttribute("rfc");

	$fechaEmision=$comprobante->item(0)->getAttribute("fecha");
	//Creamos atributo de cadena
	$cadena=$domXml->createAttribute("cadenaOriginal");
	$cadena->value="$cadenaOriginal";
	$cantidad=$domXml->createAttribute("cantidadLetra");
	$cantidad->value="$cantidadLetra";
	
	$comprobante->item(0)->appendChild($cantidad);
	$comprobante->item(0)->appendChild($cadena);
	if($tipodocto==1){
		$pedido=$domXml->createAttribute("pedidoVenta");
		$pedido->value="$orderNo";
		$comprobante->item(0)->appendChild($pedido);
	}elseif($tipodocto==11){
		$pedido=$domXml->createAttribute("pedidoVenta");
		$pedido->value="$orderNo";
		$comprobante->item(0)->appendChild($pedido);
	}
	
	$input=str_replace(chr(13).chr(10).'0','@%',utf8_encode($txtinput));
	$arraycadena= array();
	$arraycadena=explode('@%',$input);
	
	$encabezadoLine=explode('|',$arraycadena[0]);

	if($encabezadoLine[19]!=''){
		$comentarios=$domXml->createAttribute("comentarios");
		$comentarios->value="$encabezadoLine[19]";
		$comprobante->item(0)->appendChild($comentarios);
	}
	
	if($encabezadoLine[9]!=''){
		$descuento=$domXml->createAttribute("descuento");
		$descuento->value="$encabezadoLine[9]";
		$comprobante->item(0)->appendChild($descuento);
	}
	

	$input=str_replace(chr(13).chr(10).'0','@%',utf8_encode($txtinput));
	$arraycadena= array();
	$arraycadena=explode('@%',$input);
	$embarquerLine=explode('|',$arraycadena[count($arraycadena)-1]);
	
	
	
	//Se agrega el nodo embarque
	if($embarquerLine[2]!=''){
		$embarque=$domXml->createElement("Embarque");
		$nombreEnbarque=$domXml->createAttribute("nombre");
		$nombreEnbarque->value=$embarquerLine[1];
		$embarque->appendChild($nombreEnbarque);
		

		$domicilio=$domXml->createElement("Domicilio");
		$calle=$domXml->createAttribute("calle");
		$calle->value=$embarquerLine[2];
		$domicilio->appendChild($calle);
		$noExt=$domXml->createAttribute("noExterior");
		$noExt->value=$embarquerLine[3];
		$domicilio->appendChild($noExt);
		$noInt=$domXml->createAttribute("noInterior");
		$noInt->value=$embarquerLine[4];
		$domicilio->appendChild($noInt);
		$colonia=$domXml->createAttribute("colonia");
		$colonia->value=$embarquerLine[5];
		$domicilio->appendChild($colonia);
		$localidad=$domXml->createAttribute("localidad");
		$localidad->value=$embarquerLine[6];
		$domicilio->appendChild($localidad);
		$referencia=$domXml->createAttribute("referencia");
		$referencia->value=$embarquerLine[8];
		$domicilio->appendChild($referencia);
		$municipio=$domXml->createAttribute("municipio");
		$municipio->value=$embarquerLine[8];
		$domicilio->appendChild($municipio);
		$estado=$domXml->createAttribute("estado");
		$estado->value=$embarquerLine[9];
		$domicilio->appendChild($estado);
		$codigoPostal=$domXml->createAttribute("codigoPostal");
		$codigoPostal->value=$embarquerLine[7];
		$domicilio->appendChild($codigoPostal);
		$pais=$domXml->createAttribute("pais");
		$pais->value=$embarquerLine[9];
		$domicilio->appendChild($pais);

		$embarque->appendChild($domicilio);

		$receptor=$domXml->getElementsByTagName('Receptor')->item(0);
		$domXml->documentElement->insertBefore($embarque,$receptor->nextSibling);
	}
	

	$input=str_replace(chr(13).chr(10).'0','@%',utf8_encode($txtinput));
	$arraycadena= array();
	$arraycadena=explode('@%',$input);
	$noConcep=0;
	for($line=0;$line<=count($arraycadena)-2;$line++){
		$encabezadoLine=explode('|',$arraycadena[$line]);
		if($encabezadoLine[0]=='5'){
			$descuento1=$encabezadoLine[10];
			$descuento2=$encabezadoLine[11];
			$descuento3=$encabezadoLine[12];
			$importeCondescuentos=$encabezadoLine[13];
			$almacen=$encabezadoLine[17];
			$concepto=$xpath->query('/Comprobante/Conceptos/Concepto');

			$desc1=$domXml->createAttribute('descuento1');
			$desc1->value="$descuento1";
			$concepto->item($noConcep)->appendChild($desc1);
			
			$desc2=$domXml->createAttribute('descuento2');
			$desc2->value="$descuento2";
			$concepto->item($noConcep)->appendChild($desc2);
			$desc3=$domXml->createAttribute('descuento3');
			$desc3->value="$descuento3";
			$concepto->item($noConcep)->appendChild($desc3);
			$importeDescuentos=$domXml->createAttribute('importeDescuentos');
			$importeDescuentos->value="$importeCondescuentos";
			$concepto->item($noConcep)->appendChild($importeDescuentos);
			
			$almacen1=$domXml->createAttribute('almacen');
			$almacen1->value="$almacen";
			$concepto->item($noConcep)->appendChild($almacen1);
			
			$noConcep=$noConcep+1;
		}
	}
	
	$SQL="Select Titulo,Texto,consulta,noColumns from PDFTemplates where tipodocto=".$tipodocto;
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
	 //	echo $SQL;
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	if (DB_num_rows($Result)>0) {
		while($row=DB_fetch_array($Result)){
			$query=$row['consulta'];
			if($query!=null && !empty($query)){
				//echo "Titulo:".$row['Titulo'];
				if(($row['Titulo'] != 'InformacionSucursales')) {
					// Esta condicion se hizo para Tycqsa, si el tipo de doc. es 22 pasar el debtortrans id en los sqls de PDFTemplates ... 
					if($tipodocto == 22) {
						$query .= $debtorId;
					} else {
						$query .= $orderNo;
					}
				}
								//echo "<br>Query:".$query;
				$result2=DB_query($query,$db,$ErrMsg,$DbgMsg,true);
				if (DB_num_rows($result2) > 0) {
					$noColumns=$row['noColumns'];
					//GET result query config
					while($row2=DB_fetch_array($result2)){
						$nodeXPath=$xpath->query($row['Titulo']);
						if($nodeXPath->length>0){
							//El nodo ya existe por lo que no se crea
							$node=$domXml->createElement("Descripciones");
							if($noColumns==1){
								$valoretiqueta=$row['Texto'];
								$etiqueta=$domXml->createAttribute("etiqueta");
								$etiqueta->value="$valoretiqueta";
								$node->appendChild($etiqueta);
							}
							//se crean los atributos con respecto al numero de columnas de la query
							for($i = 0; $i<$noColumns; $i++){
								$attribute=$domXml->createAttribute("descripcion".$i);
								$attribute->value=$row2[$i];
								$node->appendChild($attribute);
							}
							$nodeXPath->item(0)->appendChild($node);
							$comprobante->item(0)->appendChild($nodeXPath->item(0));
						}else{
							//Se crea el nodo especificado
							if($row['Titulo']!=null && !empty($row['Titulo'])){
								$nodeXPath=$domXml->createElement($row['Titulo']);
							}else{
								$nodeXPath=$domXml->createElement('DefaultNode');
							}
							$node=$domXml->createElement("Descripciones");
							if($noColumns==1){
								$valoretiqueta=$row['Texto'];
								$etiqueta=$domXml->createAttribute("etiqueta");
								$etiqueta->value="$valoretiqueta";
								$node->appendChild($etiqueta);
							}
							for($ii = 0; $ii<$noColumns; $ii++){
								$attribute=$domXml->createAttribute("descripcion$ii");
								$attribute->value=$row2[$ii];
								$node->appendChild($attribute);
							}
							$nodeXPath->appendChild($node);
							$comprobante->item(0)->appendChild($nodeXPath);
						}

					}
					//END WHILE

				}
			}else{
				if($row['Texto']!=null && !empty($row['Texto'])){
					//Crea Nodos y atributos
					if($row['Titulo']!=null && !empty($row['Titulo'])){
						$nodeXPath=$xpath->query($row['Titulo']);
					}else{
						$nodeXPath=$xpath->query('DefaultNode');
					}
					if($nodeXPath->length>0){
						//El nodo ya existe por lo que no se crea
						$node=$domXml->createElement("Descripciones");
						$attribute=$domXml->createAttribute("descripcion0");
						$attribute->value=$row['Texto'];
						$node->appendChild($attribute);
						$nodeXPath->item(0)->appendChild($node);
						$comprobante->item(0)->appendChild($nodeXPath->item(0));
					}else{
						//Se crea el nodo especificado
						if($row['Titulo']!=null && !empty($row['Titulo'])){
							$nodeDB=$domXml->createElement($row['Titulo']);
						}else{
							$nodeDB=$domXml->createElement('DefaultNode');
						}
						$node=$domXml->createElement("Descripciones");
						$attribute=$domXml->createAttribute("descripcion0");
						$attribute->value=$row['Texto'];
						$node->appendChild($attribute);
						$nodeDB->appendChild($node);
						$comprobante->item(0)->appendChild($nodeDB);
					}
				}
			}
		}

	}



	

	//Creamos Nodo Pagares
	// 	$lastElemnt=$domXml->documentElement->lastChild;
	// 	$pagares=$domXml->createElement("Pagares");
	// 	$lastElemnt->parentNode->insertBefore($pagares,$lastElemnt->nextSibling);

	$array["rfcEmisor"]="$rfcEmisor";
	$array["fechaEmision"]="$fechaEmision";
	$xmlImpresion=$domXml->saveXml();
	//echo '<br><br><pre>XML:'.htmlentities($xmlImpresion);
	$array["xmlImpresion"]="$xmlImpresion";
    
	return $array;

}


function generaXML($cadena_original,$tipocomprobante,$tagref,$serie,$folio,$iddocto,$carpeta,$orderno=0,$db) {
	// 	echo "<pre>entra:".$cadena_original;
	global $xml, $cadena, $conn, $sello,$cadenasellar,$totalimporte;
	$banderaimpuestos=false;
	$banderaconceptos=false;
	$cadena=str_replace(chr(13).chr(10).'0','@%',$cadena_original);
	$tipocomprobante=strtolower($tipocomprobante);
	$noatt=  array();
	$arraycadena= array();
	$nufa = $serie.$folio;    // Junta el numero de factura   serie + folio
	$impuestofact=0;
	$cadenasellar="";

	$xml = new DOMdocument('1.0','UTF-8');
	$root = $xml->createElement("Comprobante");
	$root = $xml->appendChild($root);

	cargaAtt($root, array("xmlns"=>"http://www.sat.gob.mx/cfd/2",
	"xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
	"xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/2  http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd"
			)
	);
	// 	$SQL=" SELECT l.taxid,a.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
	// 			a.address1 as calle,a.address2 as noExterior,a.address3 as colonia,
	// 			a.address4 as localidad,a.address3 as municipio,a.address5 as estado,
	// 			a.cp as cp,
	// 			t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
	// 			t.address3 as coloniaexpedido,
	// 			t.address4 as localidadexpedido,
	// 			t.address4 as municipioexpedido,
	// 			t.address5 as estadoexpedido,
	// 			t.cp as codigoPostalExpedido,
	// 			t.address6 as paisexpedido,
	// 			a.Anioaprobacion,
	// 			a.Noaprobacion,
	// 			a.Nocertificado,
	// 			l.FileSAT,
	// 			l.regimenfiscal
	// 			FROM areas a, tags t, legalbusinessunit l
	// 			WHERE a.areacode=t.areacode
	// 			and l.legalid=t.legalid
	// 			AND tagref='".$tagref."'";

	$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
			l.address1 as calle,l.address2 as colonia,
			l.address3 as localidad,l.address3 as municipio,l.address4 as estado,
			l.address5 as cp,
			t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
			t.address3 as coloniaexpedido,
			t.address4 as localidadexpedido,
			t.address4 as municipioexpedido,
			t.address5 as estadoexpedido,
			t.cp as codigoPostalExpedido,
			t.address6 as paisexpedido,
			a.Anioaprobacion,
			a.Noaprobacion,
			a.Nocertificado,
			l.FileSAT,
			l.regimenfiscal
			FROM areas a, tags t, legalbusinessunit l
			WHERE a.areacode=t.areacode
			and l.legalid=t.legalid
			AND tagref='".$tagref."'";

	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
	// echo $SQL;
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==1) {
		$myrowtags = DB_fetch_array($Result);
		$rfc=trim($myrowtags['taxid']);
		$keyfact=$myrowtags['address5'];
		$nombre=$myrowtags['tagname'];
		$area=$myrowtags['areacode'];
		$legaid=$myrowtags['legalid'];
		$legalname=$myrowtags['empresa'];
	}



	$arraycadena=explode('@%',$cadena);
	// lee primero el arreglo y pone el monto de los productos
	$impuestosinifact=0;
	$totalimporte=0;
	for($cad=4;$cad<=count($arraycadena)-1;$cad++){
		$linea=$arraycadena[$cad];
		$datos=explode('|',$linea);
		if($cad>=4 and $datos[0]=='5'){

			if($carpeta=='Recibo'){
				$importe=$datos[6];
				$unidades=$datos[7];
			}elseif($carpeta=='NCargo' or $carpeta=='NCreditoDirect'){
				$importe=$datos[13];
				$unidades="unidades";
			}else{
				$importe=$datos[5]*$datos[3];//$datos[13];
				//echo '<br>importe envia:'.$importe;
				$unidades=$datos[7];
			}
			$totalimporte=$totalimporte+$importe;

		}elseif($cad>=4 and $datos[0]=='6'){

			$impuestosinifact=$impuestosinifact+trim($datos[3]);
			//$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
		}//fin de if de cad
	}
	$totalimporte=number_format($totalimporte,2,'.','');
	//echo '<br>total:'.$totalimporte.'<br>';
	//exit;
	for($cad=0;$cad<=count($arraycadena)-1;$cad++){
		$linea=$arraycadena[$cad];
		$datos=explode('|',$linea);

		if ($cad==0){
			$vendedor=$datos[15];
			$seriekm=$datos[15];
			$datosdos=explode('|',$arraycadena[1]);
			$aprobaxfolio=TraeAprobacionxFolio($rfc,$serie,$folio,$db);
			$aprobacionfolios=explode('|',$aprobaxfolio);
			$Certificado=$aprobacionfolios[0];
			$Noaprobacion=$aprobacionfolios[1];
			$anioAprobacion=$aprobacionfolios[2];
			$descuentofact = number_format($datos[9],2,'.','');
			if(empty($descuentofact)) {
				$descuentofact = 0;
			}

			cargaAtt($root,array(
			"version"=>"2.2",
			"serie"=>$serie,
			"folio"=>$folio,
			"fecha"=>str_replace(' ','T',str_replace('/','-',$datos[4])),
			"sello"=>"@",
			"noAprobacion"=>trim($Noaprobacion),
			"anoAprobacion"=>$anioAprobacion,
			"tipoDeComprobante"=>trim($tipocomprobante),
			"formaDePago"=>$datosdos[1],
			"noCertificado"=>trim($Certificado),
			"certificado"=>"@",
			"condicionesDePago"=>'MONEDA: '.$datos[12].', TC:'.$datos[13],
			"subTotal"=>$totalimporte,
			"descuento"=>$descuentofact,
			"TipoCambio"=>$datos[13],
			"Moneda"=>$datos[12],
			"total"=>number_format(($totalimporte-$descuentofact)+$impuestosinifact,2,'.',''),
			"metodoDePago"=>$datosdos[3],
			"LugarExpedicion"=>$myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido'],
			"NumCtaPago"=>$datosdos[5]
			)
			);
			$fechaamece=str_replace(' ','T',str_replace('/','-',$datos[4]));
			$cantidadletra=$datos[11];
		 if(empty($datos[2])) { // Si no tiene serie
		 	$cadenasellar=$cadenasellar."|2.2|".trim(ReglasXCadena($datos[3]))."|".trim(ReglasXCadena(str_replace(' ','T',str_replace('/','-',$datos[4]))));
		 } else {
		 	$cadenasellar=$cadenasellar."|2.2|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]))."|".trim(ReglasXCadena(str_replace(' ','T',str_replace('/','-',$datos[4]))));
		 }

		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($Noaprobacion))."|".trim(ReglasXCadena($anioAprobacion))."|".trim(ReglasXCadena($tipocomprobante));
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[1]))."|MONEDA: ".trim(ReglasXCadena($datos[12])).ReglasXCadena(', TC:').trim(ReglasXCadena($datos[13]));
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte));
		 $cadenasellar=$cadenasellar."|".ReglasXCadena($descuentofact);
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena(number_format(($totalimporte-$descuentofact)+$impuestosinifact,2,'.','')));
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));



		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido']));
		 $cadenasellar=$cadenasellar."|".ReglasXCadena($datosdos[5]);
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[13]));
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[12]));

		}elseif($cad==1){


			$emisor = $xml->createElement("Emisor");
			$emisor = $root->appendChild($emisor);
			//cargaAtt($emisor, array("rfc"=>$rfc,"nombre"=>$legalname));
			cargaAtt($emisor, array("rfc"=>trim($rfc),
			"nombre"=>trim($legalname)
			)
			);
			$domfis =  $xml->createElement("DomicilioFiscal");//$xml->createElement("DomicilioFiscal");
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($rfc))."|".trim(ReglasXCadena($legalname));

			$domfis = $emisor->appendChild($domfis);
			cargaAtt($domfis, array("calle"=>$myrowtags['calle'],
			"noExterior"=>$myrowtags['noExterior'],
			"noInterior"=>"",
			"colonia"=>$myrowtags['colonia'],
			"referencia"=>$legalname,
			"municipio"=>$myrowtags['municipio'],
			"estado"=>$myrowtags['estado'],
			"pais"=>"MEXICO",
			"codigoPostal"=>$myrowtags['cp']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['calle']))."|".trim(ReglasXCadena($myrowtags['noExterior']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['colonia']));//."|".trim($myrowtags['municipio']);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($legalname))."|".trim(ReglasXCadena($myrowtags['municipio']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['estadoexpedido']))."|".ReglasXCadena("MEXICO")."|".trim(ReglasXCadena($myrowtags['cp']));

			$expedido = $xml->createElement("ExpedidoEn");
			$expedido = $emisor->appendChild($expedido);
			cargaAtt($expedido, array("calle"=>$myrowtags['calleexpedido'],
			"noExterior"=>$myrowtags['noExteriorexpedido'],
			"noInterior"=>"",
			"colonia"=>$myrowtags['colonia'],
			"referencia"=>$myrowtags['tagname'],
			"municipio"=>$myrowtags['municipioexpedido'],
			"estado"=>$myrowtags['estadoexpedido'],
			"pais"=>$myrowtags['paisexpedido'],
			"codigoPostal"=>$myrowtags['codigoPostalExpedido']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['calleexpedido']))."|".trim(ReglasXCadena($myrowtags['noExteriorexpedido']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['colonia']));//."|".trim($myrowtags['municipioexpedido']);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['tagname']))."|".trim(ReglasXCadena($myrowtags['municipioexpedido']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['estadoexpedido']))."|".trim(ReglasXCadena($myrowtags['paisexpedido']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['codigoPostalExpedido']));

			//regimen fiscal
			$regimenfiscal = $xml->createElement("RegimenFiscal");
			$regimenfiscal = $emisor->appendChild($regimenfiscal);
			cargaAtt($regimenfiscal, array("Regimen"=>$myrowtags['regimenfiscal']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['regimenfiscal']));

			// }// fin de si tiene rows el tagref
		}elseif($cad==2){

			$receptor = $xml->createElement("Receptor");
			$receptor = $root->appendChild($receptor);
			cargaAtt($receptor, array("rfc"=>trim($datos[2]),
			"nombre"=>trim($datos[3])
			)
			);
			$rfccliente=trim($datos[2]);
			$debtorno=trim($datos[1]);
			//echo '<br>nombre cliente:'.$datos[3];

			//echo '<br>nombre dos:'.trim(ReglasXCadena($datos[3]));

			//echo '<br>nombre tres:'.DB_escape_string(trim(ReglasXCadena($datos[3])));

			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));

			$coloniarecep=$datos[8];
			$telrecep=$datos[10];
			$cad=$cad+1;
			$linea=$arraycadena[$cad];
			$datos=explode('|',$linea);
			//echo '<br>'.ReglasXCadena($coloniarecep).'<br>';
			$domicilio = $xml->createElement("Domicilio");
			$domicilio = $receptor->appendChild($domicilio);
			if($rfccliente!='XAXX010101000'){
				cargaAtt($domicilio, array("calle"=>trim($datos[4]),
				"noExterior"=>trim($datos[5]),
				"noInterior"=>trim($datos[6]),
				"colonia"=>trim($coloniarecep),
				"referencia"=>trim($telrecep),
				"localidad"=>trim($datos[10]),
				"municipio"=>trim($datos[10]),
				"estado"=>trim($datos[11]),
				"codigoPostal"=>trim($datos[12]),
				"pais"=>"MEXICO",
				)
				);
				// echo '<br>datos:'.$datos[5].'<br>';
				if (strlen(trim($datos[4]))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[4]));
				}
				if (strlen(trim($datos[5]))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[5]));
				}
				if (strlen(trim($datos[6]))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[6]));
				}
				if (strlen(trim($coloniarecep))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($coloniarecep));
				}
				if (strlen(trim(trim($datos[10])))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[10]));
				}
				if (strlen(trim($telrecep))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($telrecep));
				}

				if (strlen(trim(trim($datos[10])))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[10]));
				}
				if (strlen(trim(trim($datos[11])))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[11]));
				}
				$cadenasellar=$cadenasellar."|MEXICO";

				if (strlen(trim(trim($datos[12])))>0){
					$cadenasellar=$cadenasellar."|".trim($datos[12]);
				}

			}else{
				cargaAtt($domicilio, array("calle"=>"",
				"noExterior"=>"",
				"colonia"=>"",
				"referencia"=>"",
				"localidad"=>"",
				"municipio"=>"",
				"estado"=>"",
				"pais"=>"MEXICO",
				"codigoPostal"=>"",
				)
				);

				$cadenasellar=$cadenasellar."|MEXICO";


			}


		}elseif($cad>=4 and $datos[0]=='5'){
			if ($banderaconceptos==false){
				$conceptos = $xml->createElement("Conceptos");
				$conceptos = $root->appendChild($conceptos);

				$banderaconceptos=true;
			}
			$concepto = $xml->createElement("Concepto");
			$concepto = $conceptos->appendChild($concepto);
			if($carpeta=='Recibo'){
				$importe=$datos[6];
				$unidades=$datos[7];
			}elseif($carpeta=='NCargo' or $carpeta=='NCreditoDirect'){
				$importe=$datos[13];
				$unidades="unidades";
			}else{
				$importe=$datos[5]*$datos[3];//$datos[13];
				$unidades=$datos[7];
			}
			//echo "unidades".$unidades;
			cargaAtt($concepto, array("cantidad"=>trim($datos[3]),
			"unidad"=>trim($unidades),
			"noIdentificacion"=>trim($datos[2]),
			"descripcion"=>trim($datos[4]),
			"valorUnitario"=>trim($datos[5]),
			"importe"=>trim($importe)
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[3]))."|".trim(ReglasXCadena($unidades));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[4]));
			//echo $cadenasellar;




			$totalimporte=$totalimporte+$importe;
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[5]))."|".trim(ReglasXCadena($importe));

			// informcion aduanera
			if(strlen($datos[14])>1){
				$InformacionAduanera = $xml->createElement("InformacionAduanera");
				$InformacionAduanera = $concepto->appendChild($InformacionAduanera);

				cargaAtt($InformacionAduanera, array("numero"=>trim($datos[15]),
				"fecha"=>trim($datos[16]),
				"aduana"=>trim($datos[14])
				));

				$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[15]))."|".trim(ReglasXCadena($datos[16]));
				$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[14]));

			}

		}elseif($cad>=4 and $datos[0]=='6'){
			if ($banderaimpuestos==false){
				$impuestos = $xml->createElement("Impuestos");
				$impuestos = $root->appendChild($impuestos);
				$traslados = $xml->createElement("Traslados");
				$traslados = $impuestos->appendChild($traslados);

				$banderaimpuestos=true;
			}
			$traslado = $xml->createElement("Traslado");
			$traslado = $traslados->appendChild($traslado);
			cargaAtt($traslado, array("impuesto"=>trim($datos[1]),
			"tasa"=>trim($datos[2]),
			"importe"=>trim($datos[3])
			)
			);

			$impuestofact=$impuestofact+trim($datos[3]);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
		}
		
		
		//fin de if de cad
	}

	if ($banderaimpuestos==true){
		$impuestos->SetAttribute("totalImpuestosTrasladados",$impuestofact);
	}else{
		$impuestos = $xml->createElement("Impuestos");
		$impuestos = $root->appendChild($impuestos);
	}
	//$root->SetAttribute("subTotal",$totalimporte);
	//$root->SetAttribute("total",($totalimporte+$impuestofact));

	if ($banderaimpuestos==false){
		$cadenasellar=$cadenasellar;//."||";
	}else{
		$cadenasellar=$cadenasellar."|".ReglasXCadena($impuestofact);//."||";
	}

	$SQL = "
			SELECT custbranch.typeaddenda,typeaddenda.archivoaddenda
			FROM  custbranch
			INNER JOIN typeaddenda on custbranch.typeaddenda=typeaddenda.id_addenda
			WHERE taxid='".$rfccliente."'
					AND debtorno='" . $debtorno."'
							";

	$Result= DB_query($SQL,$db);
	if(DB_num_rows($Result) == 0) {
		$typeaddenda=0;
	} else {
		$myrowpag = DB_fetch_array($Result);
		$typeaddenda=$myrowpag['typeaddenda'];
		$fileaddenda=$myrowpag['archivoaddenda'];
	}

	if($typeaddenda > 0) {
		include_once($fileaddenda);
	}

	$cadenasellar ='|'.$cadenasellar."||";
	//echo '<br>cadena enviada:'.$cadenasellar.'<br>';
	// inicializa y termina la cadena original con el doble ||
	$certificado = $myrowtags['FileSAT'];
	//echo $certificado;
	$maquina = trim(`uname -n`);
	// echo '<br>nombre maquina'.$maquina;

	//echo "<pre>".$cadenasellar;

	$ruta = "/var/www/html".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/";
	$file=$ruta.$certificado.".key.pem";      // Ruta al archivo
	//echo $file;
	$pkeyid = openssl_get_privatekey(file_get_contents($file));
	openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
	openssl_free_key($pkeyid);
	$sello = base64_encode($crypttext);// lo codifica en formato base64
	$root->setAttribute("sello",$sello);
	$file=$ruta.$certificado.".cer.pem";      // Ruta al archivo

	$datos = file($file);
	$certificado = ""; $carga=false;
	for ($i=0; $i<sizeof($datos); $i++) {
		if (strstr($datos[$i],"END CERTIFICATE")) $carga=false;
		if ($carga) $certificado .= trim($datos[$i]);
		if (strstr($datos[$i],"BEGIN CERTIFICATE")) $carga=true;
	}

	$root->setAttribute("certificado",$certificado);
	// }}}
	// {{{ Genera un archivo de texto con el mensaje XML + EDI  O lo guarda en cfdsello
	$xml->formatOutput = true;
	$todo = $xml->saveXML();
	$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
	// echo $dir;
	//$dir = dirname($_SERVER['PHP_SELF'])."/SAT/";
	// echo "despues de save = "; var_dump($todo); echo "\n";
	if ($dir != "/dev/null") {
	$xml->formatOutput = true;
	//	echo "entra".$dir.$nufa.'<br />';
		$xml->save($dir.$nufa.".xml");
	} else {
		$paso = $todo;
		$conn->replace("cfdsello",array("selldocu"=>$nufa,"sellcade"=>$cadena_original,"sellxml"=>$paso),"selldocu",true);
	}

	// }}}
	// echo "antes de return = $todo\n";

	//guardamos la cadena y sello en la base de datos
	$sql="update debtortrans
	  set sello='".$sello."',
	  		cadena='".DB_escape_string($cadenasellar)."'
	  				where id=".$iddocto;
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
	$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//echo '<pre>xml:<br>'.htmlentities($todo);
	$array["xml"]="$todo";
	$array["cadenaOriginal"]="$cadenasellar";
	$array["cantidadLetra"]=$cantidadletra;

	//echo "Cantidad letra: ".$cantidadletra;

	return $array;
}




function cargaAtt(&$nodo, $attr) {
	// +-------------------------------------------------------------------------------+
	// | Ademas le concatena a la variable global los valores para la cadena origianl  |
	// +-------------------------------------------------------------------------------+
	global $xml, $cadena;
	$quitar = array('sello'=>1,'noCertificado'=>1,'certificado'=>1);
	foreach ($attr as $key => $val) {
		$val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5c
		$val = preg_replace('/\t/', ' ', $val);   // Regla 5a
		$val = preg_replace('/\r/', ' ', $val);   // Regla 5a
		$val = preg_replace('/\n/', ' ', $val);   // Regla 5a
		$val = trim($val);                           // Regla 5b

		if (strlen($val)>0) {   // Regla 6
			$val = str_replace("|","/",$val); // Regla 1
			if (detectUTF8($val)){
				$val = $val;
			}
			else
				$val = utf8_encode($val);

			$nodo->setAttribute($key,$val);
			if (!isset($quitar[$key]))
			if (substr($key,0,3) != "xml" &&
			substr($key,0,4) != "xsi:")
				$cadena .= $val . "|";
		}
	}
}

function detectUTF8($string) {
	return preg_match('%(?:
			[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
			|\xE0[\xA0-\xBF][\x80-\xBF]              # excluding overlongs
			|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
			|\xED[\x80-\x9F][\x80-\xBF]              # excluding surrogates
			|\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
			|[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
			|\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
	)+%xs', $string);
}



// {{{ Funcion que concatena el valor a la cadena original
function catCadena($val) {
	// +-------------------------------------------------------------------------------+
	// | Concatena los atributos a la cadena original                                  |
	// +-------------------------------------------------------------------------------+
	global $cadena;
	$val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5a y 5c
	$val = trim($val);                           // Regla 5b
	if (strlen($val)>0) {   // Regla 6
		$val = str_replace("|","/",$val); // Regla 1
		if (detectUTF8($val))
			$val = $val;
		else
			$val = utf8_encode($val);

		$cadena .= $val . "|";
	}
}

function ReglasXCadena($val) {
	// +-------------------------------------------------------------------------------+
	// | Concatena los atributos a la cadena original                                  |
	// +-------------------------------------------------------------------------------+
	//global $cadena;
	$val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5c
	$val = preg_replace('/\t/', ' ', $val);   // Regla 5a
	$val = preg_replace('/\r/', ' ', $val);   // Regla 5a
	$val = preg_replace('/\n/', ' ', $val);   // Regla 5a
	$val = trim($val);                           // Regla 5b

	if (strlen($val)>0) {   // Regla 6
		$val = str_replace("|","/",$val); // Regla 1
		if (detectUTF8($val))
			$val = $val;
		else
			$val = utf8_encode($val);

	}
	return $val;
}



function carga_eles($obj, $ele) {
	global $root, $xml;
	foreach ($ele as $key => $val) {
		$tmp = $xml->createElement($key, utf8_encode(trim($val)));
		$tmp = $obj->appendChild($tmp);
	}
	$tmp = $root->appendChild($obj);
}
// }}}
// {{{ carga_att : genera atributos al elemento indicado
function carga_att($obj, $ele) {
	global $root, $xml;
	foreach ($ele as $key => $val) $obj->setAttribute($key, utf8_encode(trim($val)));
}

function TraeAprobacionxFolio($rfcempre,$serieap,$folioap, &$db)
{
	global $aprobacionxfoliox;
	$SQLAprobacion="SELECT  anioAprobacion,noAprobacion,certificado
			FROM AprobacionFolios
			WHERE serie='".$serieap."'
					AND ".$folioap." BETWEEN Inicial AND final
	      AND rfc='".$rfcempre."'";
	
	//echo '<pre><br>'.$SQLAprobacion;
	$ResultAprobacion=DB_query($SQLAprobacion,$db);
	if (DB_num_rows($ResultAprobacion)>0) {
		$myrowaprobacion = DB_fetch_array($ResultAprobacion);
		$aprobacionxfoliox=$myrowaprobacion['certificado'].'|'.$myrowaprobacion['noAprobacion'].'|'.$myrowaprobacion['anioAprobacion'];
	}
	return $aprobacionxfoliox;
}

function TraeTimbreCFDI($cfdi)
{
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $cfdi, $tags);
	xml_parser_free($parser);

	$elements = array();  // the currently filling [child] XmlElement array
	$stack = array();
	$DatosCFDI = array();
	foreach ($tags as $tag) {
		$index = count($elements);
		if ($tag['type'] == "complete" || $tag['type'] == "open") {
			$elements[$index] = new XmlElement;
			$elements[$index]->name = $tag['tag'];
			$elements[$index]->attributes = $tag['attributes'];
			if($elements[$index]->name=='tfd:TimbreFiscalDigital'){
				$DatosCFDI=$tag['attributes'];
				//echo '<br>atributos: '.var_dump($tag['attributes']).'<br>index:'.$index;
			}
			$elements[$index]->content = $tag['value'];
			if ($tag['type'] == "open") {  // push
		  $elements[$index]->children = array();
		  $stack[count($stack)] = &$elements;
		  $elements = &$elements[$index]->children;
			}
		}
		if ($tag['type'] == "close") {  // pop
			$elements = &$stack[count($stack) - 1];
			unset($stack[count($stack) - 1]);
		}
	}
	return($DatosCFDI);
}


// +-------------------------------------------------------------------------------+
// | Funcion para generacion de documentos de tipo CFDI                            |
// +-------------------------------------------------------------------------------+
function generaXMLCFDI($cadena_original,$tipocomprobante,$tagref,$serie,$folio,$iddocto,$carpeta,$orderno=0,$db) {
	//echo $cadena_original;
	global $xml, $cadena, $conn, $sello,$cadenasellar,$totalimporte;
	$banderaimpuestos=false;
	$banderaconceptos=false;
	$cadena=str_replace(chr(13).chr(10).'0','@%',$cadena_original);
	error_reporting(E_ALL);
	$tipocomprobante=strtolower($tipocomprobante);
	$noatt=  array();
	$arraycadena= array();
	$nufa = $serie.$folio;    // Junta el numero de factura   serie + folio
	$impuestofact=0;
	$cadenasellar="";
	$xml = new DOMdocument('1.0','UTF-8');
	
	//$envioCFDI = $xml->createElement("soapenv:Envelope");
	//$envioCFDI = $xml->appendChild($envioCFDI);
	//cargaAtt($envioCFDI, array("xmlns:soapenv"=>"http://schemas.xmlsoap.org/soap/envelope/",
	//			  "xmlns:cfdi"=>"http://www.sat.gob.mx/cfd/3"
	//                       )
	//                 );
	//$envioCFDIHeader = $xml->createElement("soapenv:Header");
	//$envioCFDIHeader = $envioCFDI->appendChild($envioCFDIHeader);

	//$envioCFDIBody = $xml->createElement("soapenv:Body");
	//$envioCFDIBody = $envioCFDI->appendChild($envioCFDIBody);


	$root = $xml->createElement("cfdi:Comprobante");
	$root = $xml->appendChild($root);

	cargaAtt($root, array(
	"xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
	"xmlns:cfdi"=>"http://www.sat.gob.mx/cfd/3",
	"xmlns:ecfd"=>"http://www.southconsulting.com/schemas/strict",
	"xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/3  http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd"

			)
	);

	$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
			l.address1 as calle,l.address2 as colonia,
			l.address3 as localidad,l.address3 as municipio,l.address4 as estado,
			l.address5 as cp,
			t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
			t.address3 as coloniaexpedido,
			t.address4 as localidadexpedido,
			t.address4 as municipioexpedido,
			t.address5 as estadoexpedido,
			t.cp as codigoPostalExpedido,
			t.address6 as paisexpedido,
			a.Anioaprobacion,
			a.Noaprobacion,
			a.Nocertificado,
			l.FileSAT,
			l.regimenfiscal
			FROM areas a, tags t, legalbusinessunit l
			WHERE a.areacode=t.areacode
			and l.legalid=t.legalid
			AND tagref='".$tagref."'";
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
	// echo $SQL;
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==1) {
		$myrowtags = DB_fetch_array($Result);
		$rfc=trim($myrowtags['taxid']);
		$keyfact=$myrowtags['address5'];
		$nombre=$myrowtags['tagname'];
		$area=$myrowtags['areacode'];
		$legaid=$myrowtags['legalid'];
		$legalname=$myrowtags['empresa'];
	}



	$arraycadena=explode('@%',$cadena);
	// lee primero el arreglo y pone el monto de los productos
	$impuestosinifact=0;
	$totalimporte=0;
	for($cad=4;$cad<=count($arraycadena)-1;$cad++){
		$linea=$arraycadena[$cad];
		$datos=explode('|',$linea);
		if($cad>=4 and $datos[0]=='5'){

			if($carpeta=='Recibo'){
				$importe=$datos[6];
				$unidades=$datos[7];
			}elseif($carpeta=='NCargo' or $carpeta=='NCreditoDirect'){
				$importe=$datos[13];
				$unidades="unidades";
			}else{
				$importe=$datos[5]*$datos[3];//$datos[13];
				//echo '<br>importe envia:'.$importe;
				$unidades=$datos[7];
			}

			$totalimporte=$totalimporte+$importe;

		}elseif($cad>=4 and ($datos[0]=='7' or $datos[0]=='6' )){
			
			$impuestosinifact=$impuestosinifact+trim($datos[3]);

			//$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
		}//fin de if de cad
	}
	$totalimporte=number_format($totalimporte,6,'.','');
	for($cad=0;$cad<=count($arraycadena)-1;$cad++){
		$linea=$arraycadena[$cad];
		$datos=explode('|',$linea);//

		if ($cad==0){
			$vendedor=$datos[15];
			$seriekm=$datos[15];
			$datosdos=explode('|',$arraycadena[1]);
			$aprobaxfolio=TraeAprobacionxFolio($rfc,$serie,$folio,$db);
			$aprobacionfolios=explode('|',$aprobaxfolio);
			$Certificado=$aprobacionfolios[0];
			$Noaprobacion=$aprobacionfolios[1];
			$anioAprobacion=$aprobacionfolios[2];
			$descuentofact = number_format($datos[9],2,'.','');
			if(empty($descuentofact)) {
				$descuentofact = 0;
			}

			cargaAtt($root,array(
			"version"=>"3.2",
			"serie"=>$serie,
			"folio"=>$folio,
			"fecha"=>str_replace(' ','T',str_replace('/','-',$datos[4])),
			"sello"=>"@",
			"tipoDeComprobante"=>trim($tipocomprobante),
			"formaDePago"=>$datosdos[1],
			"noCertificado"=>trim($Certificado),
			"certificado"=>"@",
			"subTotal"=>$totalimporte,
			"descuento"=>$descuentofact,
			"total"=>number_format(($totalimporte-$descuentofact)+$impuestosinifact,6,'.',''),
			"metodoDePago"=>$datosdos[3],
			"TipoCambio"=>$datos[13],
			"Moneda"=>$datos[12],
			"LugarExpedicion"=>$myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido'],
			"NumCtaPago"=>$datosdos[5]
			)
			);
			$fechaamece=str_replace(' ','T',str_replace('/','-',$datos[4]));
			$cantidadletra=$datos[11];
			$cadenasellar=$cadenasellar."|3.2|".trim(ReglasXCadena(str_replace(' ','T',str_replace('/','-',$datos[4]))));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($tipocomprobante));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[1]));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte));
			$cadenasellar=$cadenasellar."|".trim($descuentofact);
			//$cadenasellar=$cadenasellar."|".trim($Certificado);
			//$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte))."|".trim(number_format($datos[8],2))."|".trim(ReglasXCadena(number_format($totalimporte+$impuestosinifact,2,'.','')));
			//$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[13]));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[12]));
			$cadenasellar=$cadenasellar."|".ReglasXCadena(number_format(($totalimporte-$descuentofact)+$impuestosinifact,6,'.',''));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido']));
			$cadenasellar=$cadenasellar."|".ReglasXCadena($datosdos[5]);
			//$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[13]));
			//$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[12]));
		}elseif($cad==1){


			$emisor = $xml->createElement("cfdi:Emisor");
			$emisor = $root->appendChild($emisor);
			//cargaAtt($emisor, array("rfc"=>$rfc,"nombre"=>$legalname));
			cargaAtt($emisor, array("rfc"=>trim($rfc),
			"nombre"=>trim($legalname)
			)
			);
			$domfis =  $xml->createElement("cfdi:DomicilioFiscal");//$xml->createElement("DomicilioFiscal");
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($rfc))."|".trim(ReglasXCadena($legalname));

			$domfis = $emisor->appendChild($domfis);
			cargaAtt($domfis, array("calle"=>$myrowtags['calle'],
			"noExterior"=>$myrowtags['noExterior'],
			"noInterior"=>"",
			"colonia"=>$myrowtags['colonia'],
			"referencia"=>$legalname,
			"municipio"=>$myrowtags['municipio'],
			"estado"=>$myrowtags['estado'],
			"pais"=>"MEXICO",
			"codigoPostal"=>$myrowtags['cp']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['calle']));//."|".trim(ReglasXCadena($myrowtags['noExterior']));
			
			if (strlen(trim($myrowtags['noExterior']))>0){
				$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['noExterior']));
			}
			
			
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['colonia']));//."|".trim($myrowtags['municipio']);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($legalname))."|".trim(ReglasXCadena($myrowtags['municipio']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['estado']))."|MEXICO|".trim(ReglasXCadena($myrowtags['cp']));

			$expedido = $xml->createElement("cfdi:ExpedidoEn");
			
			$expedido = $emisor->appendChild($expedido);
			cargaAtt($expedido, array("calle"=>$myrowtags['calleexpedido'],
			"noExterior"=>$myrowtags['noExteriorexpedido'],
			"noInterior"=>"",
			"colonia"=>$myrowtags['coloniaexpedido'],
			"referencia"=>$myrowtags['tagname'],
			"municipio"=>$myrowtags['municipioexpedido'],
			"estado"=>$myrowtags['estadoexpedido'],
			"pais"=>$myrowtags['paisexpedido'],
			"codigoPostal"=>$myrowtags['codigoPostalExpedido']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['calleexpedido']))."|".trim(ReglasXCadena($myrowtags['noExteriorexpedido']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['coloniaexpedido']));//."|".trim($myrowtags['municipioexpedido']);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['tagname']))."|".trim(ReglasXCadena($myrowtags['municipioexpedido']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['estadoexpedido']))."|".trim(ReglasXCadena($myrowtags['paisexpedido']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['codigoPostalExpedido']));
			//regimen fiscal
			$regimenfiscal = $xml->createElement("cfdi:RegimenFiscal");
			$regimenfiscal = $emisor->appendChild($regimenfiscal);
			cargaAtt($regimenfiscal, array("Regimen"=>$myrowtags['regimenfiscal']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['regimenfiscal']));


		}elseif($cad==2){

			$receptor = $xml->createElement("cfdi:Receptor");
			$receptor = $root->appendChild($receptor);
			cargaAtt($receptor, array("rfc"=>trim($datos[2]),
			"nombre"=>trim($datos[3])
			)
			);
			$rfccliente=trim($datos[2]);
			$debtorno=trim($datos[1]);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));

			$coloniarecep=$datos[8];
			$telrecep=$datos[10];
			$cad=$cad+1;
			$linea=$arraycadena[$cad];
			$datos=explode('|',$linea);
			//echo '<br>'.ReglasXCadena($coloniarecep).'<br>';
			$domicilio = $xml->createElement("cfdi:Domicilio");
			$domicilio = $receptor->appendChild($domicilio);
			if($rfccliente!='XAXX010101000'){
				cargaAtt($domicilio, array("calle"=>trim($datos[4]),
				"noExterior"=>trim($datos[5]),
				"noInterior"=>trim($datos[6]),
				"colonia"=>trim($coloniarecep),
				"referencia"=>trim($telrecep),
				"localidad"=>trim($datos[10]),
				"municipio"=>trim($datos[10]),
				"estado"=>trim($datos[11]),
				"codigoPostal"=>trim($datos[12]),
				"pais"=>"Mexico",
				)
				);
				// echo '<br>datos:'.$datos[5].'<br>';
				if (strlen(trim($datos[4]))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[4]));
				}
				if (strlen(trim($datos[5]))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[5]));
				}
				if (strlen(trim($datos[6]))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[6]));
				}
				if (strlen(trim($coloniarecep))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($coloniarecep));
				}


				if (strlen(trim(trim($datos[10])))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[10]));
				}

				if (strlen(trim($telrecep))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($telrecep));
				}

				if (strlen(trim(trim($datos[10])))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[10]));
				}
				if (strlen(trim(trim($datos[11])))>0){
					$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[11]));
				}
				$cadenasellar=$cadenasellar."|Mexico";
				if (strlen(trim(trim($datos[12])))>0){
					$cadenasellar=$cadenasellar."|".trim($datos[12]);
				}




			}else{
				cargaAtt($domicilio, array("calle"=>"",
				"noExterior"=>"",
				"colonia"=>"",
				"referencia"=>"",
				"localidad"=>"",
				"municipio"=>"",
				"estado"=>"",
				"pais"=>"Mexico",
				"codigoPostal"=>"",
				)
				);

				$cadenasellar=$cadenasellar."|Mexico";


			}


		}elseif($cad>=4 and $datos[0]=='5'){
			if ($banderaconceptos==false){
				$conceptos = $xml->createElement("cfdi:Conceptos");
				$conceptos = $root->appendChild($conceptos);

				$banderaconceptos=true;
			}
			$concepto = $xml->createElement("cfdi:Concepto");
			$concepto = $conceptos->appendChild($concepto);
			if($carpeta=='Recibo'){
				$importe=$datos[6];
				$unidades=$datos[7];
			}elseif($carpeta=='NCargo' or $carpeta=='NCreditoDirect'){
				$importe=$datos[13];
				$unidades="unidades";
			}else{
				$importe=$datos[5]*$datos[3];//$datos[13];
				$unidades=$datos[7];
			}
			//echo "unidades".$datos[5].'---'.$datos[3].' Importe ....'.$importe;
			cargaAtt($concepto, array("cantidad"=>trim($datos[3]),
			"unidad"=>trim($unidades),
			"noIdentificacion"=>trim($datos[2]),
			"descripcion"=>trim($datos[4]),
			"valorUnitario"=>trim(number_format($datos[5], 6, '.', '')),
			"importe"=>trim(number_format($importe, 6, '.', ''))
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[3]))."|".trim(ReglasXCadena($unidades));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[4]));
			//echo $cadenasellar;
			$totalimporte=$totalimporte+$importe;
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena(number_format($datos[5], 6, '.', '')))."|".trim(ReglasXCadena(number_format($importe, 6, '.', '')));
		}elseif($cad>=4 and $datos[0]=='7'){
			if ($banderageneraimpuestos==false){
				$impuestos = $xml->createElement("cfdi:Impuestos");
				$impuestos = $root->appendChild($impuestos);
				$banderageneraimpuestos=true;
			}
			
			if ($banderaimpuestos==false){
				$traslados = $xml->createElement("cfdi:Traslados");
				$traslados = $impuestos->appendChild($traslados);

				$banderaimpuestos=true;
			}
			$traslado = $xml->createElement("cfdi:Traslado");
			$traslado = $traslados->appendChild($traslado);
			cargaAtt($traslado, array("impuesto"=>trim($datos[1]),
			"tasa"=>trim($datos[2]),
			"importe"=>trim(number_format($datos[3], 6, '.', ''))
			)
			);

			$impuestofact=$impuestofact+trim($datos[3]);
			$cadenasellarimp=$cadenasellarimp."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena(number_format($datos[3], 6, '.', '')));
		}elseif($cad>=4 and $datos[0]=='6'){
			
			if ($banderageneraimpuestos==false){
				$impuestos = $xml->createElement("cfdi:Impuestos");
				$impuestos = $root->appendChild($impuestos);
				$banderageneraimpuestos=true;
			}
			
			if ($banderaimpuestosretenidos==false){
				$Retenciones = $xml->createElement("cfdi:Retenciones");
				$Retenciones = $impuestos->appendChild($Retenciones);
				$banderaimpuestosretenidos=true;
			}
			
			
			$retenido = $xml->createElement("cfdi:Retencion");
			$retenido = $Retenciones->appendChild($retenido);
			cargaAtt($retenido, array("impuesto"=>trim($datos[1]),
							"importe"=>trim(number_format(abs($datos[3]), 2, '.', '')))
			);

			$impuestoretenido=$impuestoretenido+trim($datos[3]);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena(number_format(abs($datos[3]), 2, '.', '')));
		}elseif($cad>=4 and $datos[0]=='8'){
			$complemento = $xml->createElement("cfdi:Complemento");
			$complemento = $root->appendChild($complemento);
			$impLocal = $xml->createElement("implocal:ImpuestosLocales");
			cargaAtt($impLocal, array("xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
									  "xmlns:implocal" => "http://www.sat.gob.mx/implocal",
									  "xsi:schemaLocation" => "http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd"
					)
			);
			$impLocal = $complemento->appendChild($impLocal);
			
			$ind=1;
			$tRet=0;
			$tTras=0;
			$cadimp = "";
			while ($ind<count($datos)){
				$desc = $datos[$ind];
				$porc = $datos[$ind+1];
				$cant = $datos[$ind+2];
				if ($cant < 0 ){
					$tRet+=abs($cant);
			
					$retLocales = $xml->createElement("implocal:RetencionesLocales");
					$retLocales = $impLocal->appendChild($retLocales);
			
					cargaAtt($retLocales,array("ImpLocRetenido"=>$desc,
					"TasadeRetencion"=>$porc,
					"Importe"=>number_format(abs($cant),2)
					)
					);
				}
				else{
					$tTras+=$cant;
			
					$trasLocales = $xml->createElement("implocal:TrasladosLocales");
					$trasLocales = $impLocal->appendChild($trasLocales);
			
					cargaAtt($trasLocales,array("ImpLocTrasladado"=>$desc,
					"TasadeTraslado"=>$porc,
					"Importe"=>number_format($cant,2)
					)
					);
				}
				$cadimp.="|".$desc."|".$porc."|".number_format(abs($cant),2);
			
			
				$ind+=3;
			}
			cargaAtt($impLocal,array("version"=>"1.0",
			"TotaldeRetenciones"=>number_format($tRet,2),
			"TotaldeTraslados"=>number_format($tTras,2)
			)
			);
			
			$cadimp = utf8_encode("|1.0|".number_format($tRet,2)."|".number_format($tTras,2).trim($cadimp));
		}
		
		
		
		//fin de if de cad
	}

	
	// impuestos retenidos federales
	if ($banderaimpuestosretenidos==true){
		$impuestos->SetAttribute("totalImpuestosRetenidos",number_format(abs($impuestoretenido), 6, '.', ''));
	}
	
	
	
	if ($banderaimpuestosretenidos==false){
		$cadenasellar=$cadenasellar;//."||";
	}else{
		$cadenasellar=$cadenasellar."|".ReglasXCadena(number_format(abs($impuestoretenido), 6, '.', ''));//."||";
	}
	
	if ($banderaimpuestos==true){
		$impuestos->SetAttribute("totalImpuestosTrasladados",number_format($impuestofact, 6, '.', ''));
	}else{
		$impuestos = $xml->createElement("cfdi:Impuestos");
		$impuestos = $root->appendChild($impuestos);
	}
	
	
	
	if ($banderaimpuestos==false){
		$cadenasellar=$cadenasellar;//."||";
	}else{
		$cadenasellar=$cadenasellar.$cadenasellarimp."|".ReglasXCadena(number_format($impuestofact, 6, '.', ''));//."||";
	}
	
	
	//echo 'addenda pemex:'.htmlentities($addenda);
	
	$cadenasellar ='|'.$cadenasellar."||";
	//agregado porque la cdena original debe ser codificada en utf8 segun anexo 20 del sat
	$cadenasellarx=$cadenasellar;
	//$cadenasellar = (DB_escape_string($cadenasellar));

	//echo '<br><br>'.$cadenasellar.'<br><br>';
	// inicializa y termina la cadena original con el doble ||

	$certificado = $myrowtags['FileSAT'];
	$maquina = trim(`uname -n`);
	$ruta = "/var/www/html".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/";
	$file=$ruta.$certificado.".key.pem";      // Ruta al archivo
	//echo "Key pem: " . $file . "<br/>";
	$pkeyid = openssl_get_privatekey(file_get_contents($file));
	openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
	openssl_free_key($pkeyid);
	$sello = base64_encode($crypttext);// lo codifica en formato base64
	$root->setAttribute("sello",$sello);
	$file=$ruta.$certificado.".cer.pem";      // Ruta al archivo

	//echo "Certificado: " . $certificado . "<br/>";
	//echo "Maq: " . $certificado . "<br/>";
	//echo "Cer pem: " . $file . "<br/>";

	$datos = file($file);
	$certificado = ""; $carga=false;
	for ($i=0; $i<sizeof($datos); $i++) {
		if (strstr($datos[$i],"END CERTIFICATE")) $carga=false;
		if ($carga) $certificado .= trim($datos[$i]);
		if (strstr($datos[$i],"BEGIN CERTIFICATE")) $carga=true;
	}
	$root->setAttribute("certificado",$certificado);
	$xml->formatOutput = true;
	$todo = $xml->saveXML();
	
	$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
	//echo "dir: " . $dir . "<br/>";
	if ($dir != "/dev/null") {
		$xml->formatOutput = true;
		$xml->save($dir.$nufa.".xml");
	} else {
		$paso = $todo;
		$conn->replace("cfdsello",array("selldocu"=>$nufa,"sellcade"=>$cadena_original,"sellxml"=>$paso),"selldocu",true);
	}
	//guardamos la cadena y sello en la base de datos
	$sql="update debtortrans
	  set sello='".$sello."',
	  		cadena='".addslashes($cadenasellar)."'
	  				where id=".$iddocto;
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
	$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	// echo '<br>todo:<pre>'.htmlentities($cadenasellar);

	//printf ("<pre>%s</pre>",  ($cadenasellar)."<br><br>");

	$array["xml"]="$todo";
	$array["cadenaOriginal"]="$cadenasellar";
	$array["cantidadLetra"]=$cantidadletra;
	$array["xmladdenda"]="$addendaXML";
	// 	return($todo);
	//echo '<pre>XML:'.htmlentities($todo);
	
	return $array;

}

// +-------------------------------------------------------------------------------+
// | Funcion para generacion de archivo de cancelacion de CFDI                     |
// +-------------------------------------------------------------------------------+
function generaXMLCancelCFDI($UIID,$tipocomprobante,$tagref,$serie,$folio,$iddocto,$carpeta,$fechaorigen,$db) {
	//echo $cadena_original;
	global $xml, $cadena, $conn, $sello,$cadenasellar,$totalimporte;
	$xml = new DOMdocument('1.0','UTF-8');
	$root = $xml->createElement("CancelaCFD");
	$root = $xml->appendChild($root);

	cargaAtt($root, array("xmlns"=>"http://cancelacfd.sat.gob.mx",
	"xmlns:soapenv"=>"http://schemas.xmlsoap.org/soap/envelope/"
			)
	);

	$SQL=" SELECT l.taxid,a.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
			a.address1 as calle,a.address2 as noExterior,a.address3 as colonia,
			a.address4 as localidad,a.address4 as municipio,a.address5 as estado,
			a.cp as cp,
			t.address1 as calleexpedido,t.address2 as noExteriorexpedido,
			t.address3 as coloniaexpedido,
			t.address4 as localidadexpedido,
			t.address4 as municipioexpedido,
			t.address5 as estadoexpedido,
			t.cp as codigoPostalExpedido,
			t.address6 as paisexpedido,
			a.Anioaprobacion,
			a.Noaprobacion,
			a.Nocertificado,
			l.FileSAT,
			l.regimenfiscal
			FROM areas a, tags t, legalbusinessunit l
			WHERE a.areacode=t.areacode
			and l.legalid=t.legalid
			AND tagref='".$tagref."'";
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
	// echo $SQL;
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==1) {
	 $myrowtags = DB_fetch_array($Result);
	 $rfc=trim($myrowtags['taxid']);
	 $keyfact=$myrowtags['address5'];
	 $nombre=$myrowtags['tagname'];
	 $area=$myrowtags['areacode'];
	 $legaid=$myrowtags['legalid'];
	 $legalname=$myrowtags['empresa'];
	}
	$Cancelacion = $xml->createElement("Cancelacion");
	$Cancelacion= $root->appendChild($Cancelacion);
	cargaAtt($Cancelacion,array(
	"xmlns:xsd"=>"http://www.w3.org/2001/XMLSchema",
	"xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
	"fecha"=>str_replace(' ','T',str_replace('/','-',$fechaorigen)),
	"RfcEmisor"=>trim($rfc)
	)
	);
	$cadenasellar=$cadenasellar."|".str_replace(' ','T',str_replace('/','-',$fechaorigen))."|".trim($rfc);

	$Folio = $xml->createElement("Folios");
	$Folio= $Cancelacion->appendChild($Folio);

	$UUID_ = $xml->createElement("UUID");
	$UUID_->appendChild($xml->createTextNode($UIID));
	$Folio->appendChild($UUID_);

	$cadenasellar=$cadenasellar."|".trim($UIID);

	$Signature = $xml->createElement("Signature");
	$Signature= $Cancelacion->appendChild($Signature);
	cargaAtt($Signature,array("xmlns"=>"http://www.w3.org/2000/09/xmldsig#"));

	$SignedInfo = $xml->createElement("SignedInfo");
	$SignedInfo = $Signature->appendChild($SignedInfo);

	$CanonicalizationMethod = $xml->createElement("CanonicalizationMethod");
	$CanonicalizationMethod = $SignedInfo->appendChild($CanonicalizationMethod);
	cargaAtt($CanonicalizationMethod, array("Algorithm"=>"http://www.w3.org/TR/2001/REC-xml-c14n-20010315"));

	$SignatureMethod = $xml->createElement("SignatureMethod");
	$SignatureMethod = $SignedInfo->appendChild($SignatureMethod);
	cargaAtt($SignatureMethod,array("Algorithm"=>"http://www.w3.org/2000/09/xmldsig#rsa-sha1"));



	$Reference = $xml->createElement("Reference");
	$Reference = $SignedInfo->appendChild($Reference);
	cargaAtt($Reference,array("URI"=>""));

	$Transforms = $xml->createElement("Transforms");
	$Transforms = $Reference->appendChild($Transforms);

	$Transform = $xml->createElement("Transform");
	$Transform = $Transforms->appendChild($Transform);

	cargaAtt($Transform,array("Algorithm"=>"http://www.w3.org/2000/09/xmldsig#enveloped-signature"));

	$DigestMethod = $xml->createElement("DigestMethod");
	$DigestMethod = $Reference->appendChild($DigestMethod);
	cargaAtt($DigestMethod,array("Algorithm"=>"http://www.w3.org/2000/09/xmldsig#sha1"));

	$DigestValue = $xml->createElement("DigestValue");
	$DigestValue = $Reference->appendChild($DigestValue);
	//$DigestValue->appendChild($xml->createTextNode("DigestValue"));


	$SignatureValue = $xml->createElement("SignatureValue");
	$SignatureValue = $Signature->appendChild($SignatureValue);
	//$SignatureValue->appendChild($xml->createTextNode("SignatureValue"));

	$certificado = $myrowtags['FileSAT'];
	$maquina = trim(`uname -n`);
	$ruta = "/var/www/html".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/";
	$file=$ruta.$certificado.".key.pem";      // Ruta al archivo

	$fileKeyPem = $file;

	$pkeyid = openssl_get_privatekey(file_get_contents($file));
	//echo $file;
	openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_SHA1);
	openssl_free_key($pkeyid);
	$sello = base64_encode($crypttext);// lo codifica en formato base64
	//$root->setAttribute("sello",$sello);
	$file=$ruta.$certificado.".cer.pem";      // Ruta al archivo

	$fileCerPem = $file;

	/*$DigestValue = $xml->createElement("DigestValue");
	 $DigestValue = $Reference->appendChild($DigestValue);
	$DigestValue->SetAttribute("DigestValue",$impuestofact);
	$SignatureValue = $xml->createElement("SignatureValue");
	$SignatureValue = $Cancelacion->appendChild($DigestValue);
	$SignatureValue->SetAttribute("SignatureValue",$sello);*/

	$KeyInfo = $xml->createElement("KeyInfo");
	$KeyInfo = $Signature->appendChild($KeyInfo);

	$X509Data = $xml->createElement("X509Data");
	$X509Data = $KeyInfo->appendChild($X509Data);

	$datos = file($file);
	$certificado = ""; $carga=false;
	for ($i=0; $i<sizeof($datos); $i++) {
		if (strstr($datos[$i],"END CERTIFICATE")) $carga=false;
		if ($carga) $certificado .= trim($datos[$i]);
		if (strstr($datos[$i],"BEGIN CERTIFICATE")) $carga=true;
	}

	$X509Certificate = $xml->createElement("X509Certificate");
	//$X509Certificate->appendChild($xml->createTextNode($certificado));
	$X509Data->appendChild($X509Certificate);

	$xml->formatOutput = true;
	$nufa = $serie . $folio;
	$todo = $xml->saveXML();
	$dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
	if ($dir != "/dev/null") {
		$xml->formatOutput = true;
		$xml->save($dir.$nufa."_ACUSE.xml");
	} else {
		$paso = $todo;
		$conn->replace("cfdsello",array("selldocu"=>$nufa,"sellcade"=>$cadena_original,"sellxml"=>$paso),"selldocu",true);
	}

	//printf ("<pre>%s</pre>", htmlentities ($todo));

	return($todo);
}

function TraeDatosCFD($cfdi,$campo)
{
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $cfdi, $tags);
	xml_parser_free($parser);

	$elements = array();  // the currently filling [child] XmlElement array
	$stack = array();
	$DatosCFDI = array();
	foreach ($tags as $tag) {
		$index = count($elements);
		if ($tag['type'] == "complete" || $tag['type'] == "open") {
			$elements[$index] = new XmlElement;
			$elements[$index]->name = $tag['tag'];
			$elements[$index]->attributes = $tag['attributes'];
			$tmpname = str_ireplace('cfdi:', '', $elements[$index]->name);
			if($tmpname==$campo){
				$DatosCFDI=$tag['attributes'];
				//echo '<br>atributos: '.var_dump($tag['attributes']).'<br>index:'.$index;
			}
			$elements[$index]->content = $tag['value'];
			if ($tag['type'] == "open") {  // push
		  $elements[$index]->children = array();
		  $stack[count($stack)] = &$elements;
		  $elements = &$elements[$index]->children;
			}
		}
		if ($tag['type'] == "close") {  // pop
			$elements = &$stack[count($stack) - 1];
			unset($stack[count($stack) - 1]);
		}
	}
	return($DatosCFDI);
}

?>
