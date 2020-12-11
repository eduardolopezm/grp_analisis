<?php

function generaXMLIntermedio($xml,$cadenaOriginal,$cantidadLetra,$orderNo,$db){
	//Se eliminan los nasmespaces para evitar error de validacion de estructura de xml
	$xmlSat=str_replace('xmlns="http://www.sat.gob.mx/cfd/2"','',$xml);
	$xmlSat=str_replace('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"','',$xmlSat);
	$xmlSat=str_replace('xsi:schemaLocation="http://www.sat.gob.mx/cfd/2 http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd"','',$xmlSat);
	$xmlSat=str_replace('xmlns:cfdi="http://www.sat.gob.mx/cfd/3"','',$xmlSat);
	$xmlSat=str_replace('xmlns:ecfd="http://www.southconsulting.com/schemas/strict"','',$xmlSat);
	$xmlSat=str_replace('xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd"','',$xmlSat);
	$xmlSat=str_replace('xsi:schemaLocation="http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd"','',$xmlSat);
	$xmlSat=str_replace('xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"','',$xmlSat);
	$xmlSat=str_replace('cfdi:','',$xmlSat);
	
	//Carga el xmlSat para modificarlo.
	$domXml=new DOMdocument();
	$domXml->loadXml($xmlSat);
	
	//Cargamos el xpath.
	$xpath=new DOMXPath($domXml);
	$comprobante=$xpath->query("/Comprobante");
	$emisor=$xpath->query("/Comprobante/Emisor");
	$rfcEmisor=$emisor->item(0)->getAttribute("rfc");
	
	$fechaEmision=$comprobante->item(0)->getAttribute("fecha");
	//Creamos atributo de cadena
	$cadena=$domXml->createAttribute("cadenaOriginal");
	$cadena->value="$cadenaOriginal";
	$cantidad=$domXml->createAttribute("cantidadLetra");
	$cantidad->value="$cantidadLetra";
	$comprobante->item(0)->appendChild($cantidad);
	$comprobante->item(0)->appendChild($cadena);


	$SQL="Select Titulo,Texto,consulta,noColumns from PDFTemplates where tipodocto=13";
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
	// echo $SQL;
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	if (DB_num_rows($Result)>0) {
		while($row=DB_fetch_array($Result)){
			$query=$row['consulta'];
			if($query!=null && !empty($query)){
				$result2=DB_query($query.$orderNo,$db,$ErrMsg,$DbgMsg,true);
				if (DB_num_rows($result2) > 0) {
					$noColumns=$row['noColumns'];
					//GET result query config
					while($row2=DB_fetch_array($result2)){
						$nodeXPath=$xpath->query($row['Titulo']);
						if($nodeXPath->length>0){
							//El nodo ya existe por lo que no se crea
							$node=$domXml->createElement("Descripciones");
							//se crean los atributos con respecto al numero de columnas de la query
							for($i = 0; $i<$noColumns; $i++){
								$attribute=$domXml->createAttribute("descripcion".$i);
								$attribute->value=$row2[$i];
								$node->appendChild($attribute);
							}
							$nodeXPath->item(0)->appendChild($node);
						}else{
							//Se crea el nodo especificado
							$nodeDB=$domXml->createElement($row['Titulo']);
							$node=$domXml->createElement("Descripciones");

							for($ii = 0; $ii<$noColumns; $ii++){
								$attribute=$domXml->createAttribute("descripcion$ii");
								$attribute->value=$row2[$ii];
								$node->appendChild($attribute);
							}
							$nodeDB->appendChild($node);
							$comprobante->item(0)->appendChild($nodeDB);
						}
					}
					//END WHILE

				}
			}else{
				if($row['Texto']!=null && empty($row['Texto'])){
					//Crea Nodos y atributos
					$nodeXPath=$xpath->query($row['Titulo']);
					if($nodeXPath->length>0){
						//El nodo ya existe por lo que no se crea
						$node=$domXml->createElement("Descripciones");
						$attribute=$domXml->createAttribute("descripcion0");
						$attribute->value=$row['Texto'];
						$node->appendChild($attribute);
						$nodeXPath->item(0)->appendChild($node);
					}else{
						//Se crea el nodo especificado
						$nodeDB=$domXml->createElement($row['Titulo']);
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
	$array["xmlImpresion"]="$xmlImpresion";
	
	return $array;

}


function generaXML($cadena_original,$tipocomprobante,$tagref,$serie,$folio,$iddocto,$carpeta,$orderno=0,$db) {
	//echo "<pre>".$cadena_original;
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
	$SQL=" SELECT l.taxid,a.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
			a.address1 as calle,a.address2 as noExterior,a.address3 as colonia,
			a.address4 as localidad,a.address3 as municipio,a.address5 as estado,
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
			$cantidadletra=$datos[10];

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
			"estado"=>$myrowtags['estadoexpedido'],
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
		}//fin de if de cad
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
		//echo "entra".$dir.$nufa.'<br />';
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

	$array["xml"]="$todo";
	$array["cadenaOriginal"]="$cadenasellar";
	$array["cantidadLetra"]=$cantidadletra;

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

	$SQL=" SELECT l.taxid,a.address5,t.tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
			a.address1 as calle,a.address2 as noExterior,a.address3 as colonia,
			a.address4 as localidad,a.address3 as municipio,a.address5 as estado,
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
			"version"=>"3.2",
			"fecha"=>str_replace(' ','T',str_replace('/','-',$datos[4])),
			"sello"=>"@",
			"tipoDeComprobante"=>trim($tipocomprobante),
			"formaDePago"=>$datosdos[1],
			"noCertificado"=>trim($Certificado),
			"certificado"=>"@",
			"subTotal"=>$totalimporte,
			"descuento"=>$descuentofact,
			"total"=>number_format(($totalimporte-$descuentofact)+$impuestosinifact,2,'.',''),
			"metodoDePago"=>$datosdos[3],
			"TipoCambio"=>$datos[13],
			"Moneda"=>$datos[12],
			"LugarExpedicion"=>$myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido'],
			"NumCtaPago"=>$datosdos[5]
			)
			);
			$fechaamece=str_replace(' ','T',str_replace('/','-',$datos[4]));
			$cantidadletra=$datos[10];
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
			$cadenasellar=$cadenasellar."|".ReglasXCadena(number_format(($totalimporte-$descuentofact)+$impuestosinifact,2,'.',''));
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
			"estado"=>$myrowtags['estadoexpedido'],
			"pais"=>"MEXICO",
			"codigoPostal"=>$myrowtags['cp']
			)
			);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['calle']))."|".trim(ReglasXCadena($myrowtags['noExterior']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['colonia']));//."|".trim($myrowtags['municipio']);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($legalname))."|".trim(ReglasXCadena($myrowtags['municipio']));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['estadoexpedido']))."|MEXICO|".trim(ReglasXCadena($myrowtags['cp']));

			$expedido = $xml->createElement("cfdi:ExpedidoEn");
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
		}elseif($cad>=4 and $datos[0]=='6'){
			if ($banderaimpuestos==false){
				$impuestos = $xml->createElement("cfdi:Impuestos");
				$impuestos = $root->appendChild($impuestos);
				$traslados = $xml->createElement("cfdi:Traslados");
				$traslados = $impuestos->appendChild($traslados);
					
				$banderaimpuestos=true;
			}
			$traslado = $xml->createElement("cfdi:Traslado");
			$traslado = $traslados->appendChild($traslado);
			cargaAtt($traslado, array("impuesto"=>trim($datos[1]),
			"tasa"=>trim($datos[2]),
			"importe"=>trim($datos[3])
			)
			);

			$impuestofact=$impuestofact+trim($datos[3]);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
		}//fin de if de cad
	}

	if ($banderaimpuestos==true){
		$impuestos->SetAttribute("totalImpuestosTrasladados",$impuestofact);
	}else{
		$impuestos = $xml->createElement("cfdi:Impuestos");
		$impuestos = $root->appendChild($impuestos);
	}
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
	  		cadena='".$cadenasellar."'
	  				where id=".$iddocto;
	$ErrMsg=_('El Sql que fallo fue');
	$DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
	$Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	// echo '<br>todo:<pre>'.htmlentities($cadenasellar);

	//printf ("<pre>%s</pre>",  ($cadenasellar)."<br><br>");

	$array["xml"]="$todo";
	$array["cadenaOriginal"]="$cadenasellar";
	$array["cantidadLetra"]=$cantidadletra;
	
// 	return($todo);

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
