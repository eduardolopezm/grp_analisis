<?php 


function generaXML($cadena_original,$tipocomprobante,$tagref,$serie,$folio,$iddocto,$carpeta,$orderno=0,$db) {
    //echo $cadena_original;
    global $xml, $cadena, $conn, $sello,$cadenasellar,$totalimporte;
    $banderaimpuestos=false;
    $banderaconceptos=false;
    $cadena=str_replace(chr(13).chr(10).'0','@%',$cadena_original);
    //echo '<br><br><br><br>'.$cadena;
error_reporting(E_ALL);
//$cadena='||'.$cadena;
$tipocomprobante=strtolower($tipocomprobante);
$noatt=  array();
$arraycadena= array();
$nufa = $serie.$folio;    // Junta el numero de factura   serie + folio
// }}}
$impuestofact=0;
$cadenasellar="";
// {{{  Datos generales del Comprobante
$xml = new DOMdocument('1.0','UTF-8');
$root = $xml->createElement("Comprobante");
$root = $xml->appendChild($root);

   cargaAtt($root, array("xmlns"=>"http://www.sat.gob.mx/cfd/2",
                          "xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
                          "xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/2  http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv2.xsd"
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
			    l.FileSAT
                        FROM areas a, tags t, legalbusinessunit l 
                        WHERE a.areacode=t.areacode
			and l.legalid=t.legalid
			AND tagref='".$tagref."'";
                      $ErrMsg=_('El Sql que fallo fue');
                      $DbgMsg=_('No se pudo obtener los datos de la unidad de negocio');
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
		    
		    cargaAtt($root,array(
					"version"=>"2.0",
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
					"condicionesDePago"=>'MONEDA: '.$datos[12].', TC:'.$datos[13].' '.$datosdos[2],
					"subTotal"=>$datos[5],
					"descuento"=>number_format($datos[8],2),
					"total"=>$datos[6],
					"metodoDePago"=>$datosdos[3].' Moneda:'.$datos[12].' TC.:'.$datos[13]
					)
                             );
		    $fechaamece=str_replace(' ','T',str_replace('/','-',$datos[4]));
		    $cantidadletra=$datos[10];
                 $cadenasellar=$cadenasellar."|2.0|".trim($datos[2])."|".trim($datos[3])."|".trim(str_replace(' ','T',str_replace('/','-',$datos[4])));
		 $cadenasellar=$cadenasellar."|".trim($Noaprobacion)."|".trim($anioAprobacion)."|".trim($tipocomprobante);
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[1]))."|".'MONEDA: '.trim(ReglasXCadena($datos[12])).', TC:'.trim(ReglasXCadena($datos[13])).' '.trim(ReglasXCadena($datosdos[2]));
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[5]))."|".trim(number_format($datos[8],2))."|".trim(ReglasXCadena($datos[6]));
		}elseif($cad==1){
                    
              
                        $emisor = $xml->createElement("Emisor");
			$emisor = $root->appendChild($emisor);
			//cargaAtt($emisor, array("rfc"=>$rfc,"nombre"=>$legalname));
                         cargaAtt($emisor, array("rfc"=>trim($rfc),
                              "nombre"=>trim($legalname)
                          )
                        );       
                        $domfis =  $xml->createElement("DomicilioFiscal");//$xml->createElement("DomicilioFiscal");
                        $cadenasellar=$cadenasellar."|".trim($rfc)."|".trim($legalname);
			
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
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['estado']))."|MEXICO|".trim(ReglasXCadena($myrowtags['cp']));
			
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
			
			
			
                   // }// fin de si tiene rows el tagref
                }elseif($cad==2){
                    
                        $receptor = $xml->createElement("Receptor");
                        $receptor = $root->appendChild($receptor);
                        cargaAtt($receptor, array("rfc"=>trim($datos[2]),
                              "nombre"=>trim($datos[3])
                          )
                        );
			$rfccliente=trim($datos[2]);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
			
			$coloniarecep=$datos[8];
			$telrecep=$datos[10];
                        $cad=$cad+1;
                        $linea=$arraycadena[$cad];
                        $datos=explode('|',$linea);
                        
                        $domicilio = $xml->createElement("Domicilio");
                        $domicilio = $receptor->appendChild($domicilio);
                        if($rfccliente!='XAXX010101000'){
			    cargaAtt($domicilio, array("calle"=>trim($datos[4]),
				    "noExterior"=>trim($datos[4]),
				   "colonia"=>trim($coloniarecep),
				   "referencia"=>trim($telrecep),
				   "localidad"=>trim($datos[10]),
				   "municipio"=>trim($datos[10]),
				   "estado"=>trim($datos[11]),
				   "codigoPostal"=>trim($datos[12]),
				   "pais"=>"MEXICO",
			       )
			   );
			    // echo $cadenasellar;
			    if (strlen(trim($datos[4]))>0){
				$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[4]))."|".trim(ReglasXCadena($datos[4]));
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
			    if (strlen(trim(trim($datos[12])))>0){
				$cadenasellar=$cadenasellar."|".trim($datos[12]);
			    }
			    
			    //$cadenasellar=$cadenasellar."|".trim($telrecep)."|".trim($datos[10]);
			    $cadenasellar=$cadenasellar."|MEXICO";
			    
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
			}elseif($carpeta=='NCargo' or $carpeta=='NCreditDirect'){
			    $importe=$datos[13];
			    $unidades="unidades";
			}else{
			    $importe=$datos[13];
			    $unidades=$datos[7];
			}
			//echo "unidades".$unidades;
                        cargaAtt($concepto, array("cantidad"=>trim($datos[3]),
				  "unidad"=>trim($unidades),
				  "noIdentificacion"=>trim($datos[2]),
                                  "descripcion"=>trim($datos[4]),
                                  "valorUnitario"=>trim(round($datos[5],2)),
                                  "importe"=>trim($importe)
                       )
                    );
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[3]))."|".trim(ReglasXCadena($unidades));
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[4]));
			//echo $cadenasellar;
			$totalimporte=$totalimporte+$importe;
			$cadenasellar=$cadenasellar."|".trim(round($datos[5],2))."|".trim($importe);
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
    
    if ($impuestofact>0){
        $impuestos->SetAttribute("totalImpuestosTrasladados",$impuestofact);
    }else{
	$impuestos = $xml->createElement("Impuestos");
	$impuestos = $root->appendChild($impuestos);
    }
    
    
    if ($banderaimpuestos==false){
	$cadenasellar=$cadenasellar;//."||";
    }else{
	$cadenasellar=$cadenasellar."|".$impuestofact;//."||";
    }
    
    // agregar addenda PARA LOS PRODUCTOS
    
    $addenda = $xml->createElement("Addenda");
    $addenda = $root->appendChild($addenda);
    $requestForPayment=$xml->createElement("requestForPayment");
    $requestForPayment=$addenda->appendChild($requestForPayment);
    
    cargaAtt($requestForPayment, array("type"=>"SimpleInvoiceType",
					"contentVersion"=>"1.3.1",
					"documentStrucutreVersion"=>"AMC7.1",
					"documentStatus"=>"ORIGINAL",
					"DeliveryDate"=>$fechaamece
				       )
				    );
    if($carpeta=='Recibo'){
	$tipodocto="PARTIAL_INVOICE";
    }elseif($carpeta=='Facturas'){
	$tipodocto="INVOICE";
    }else{
	$tipodocto="CREDIT_NOTE";
    }
    $requestForPaymentIdentification = $xml->createElement("requestForPaymentIdentification");
    $requestForPaymentIdentification = $requestForPayment->appendChild($requestForPaymentIdentification);
     cargaAtt($requestForPaymentIdentification, array("entityType"=>$tipodocto,
					"UniqueCreatorIdentificaton"=>$serie.$folio
				       )
				    );
     // ZZZ: es cantidad en letras
    $specialInstruction = $xml->createElement("specialInstruction");
    $specialInstruction = $addenda->appendChild($specialInstruction);
    cargaAtt($specialInstruction, array("text"=>$cantidadletra));
    $specialInstruction->SetAttribute("code","ZZZ");
    
    $orderIdentification=$xml->createElement("orderIdentification");
    $orderIdentification=$addenda->appendChild($orderIdentification);
    
    $referenceIdentification = $xml->createElement("referenceIdentification");
    $referenceIdentification = $orderIdentification->appendChild($referenceIdentification);
     cargaAtt($referenceIdentification, array("referenceIdentification"=>$orderno,
					      "ReferenceDate"=>$fechaamece)
	      ); 
    $referenceIdentification->SetAttribute("type","ON");
   
    $AdditionalInformation = $xml->createElement("AdditionalInformation");
    $AdditionalInformation = $addenda->appendChild($AdditionalInformation);
    //$referenceIdentification = $xml->createElement("referenceIdentification");
    //$referenceIdentification = $AdditionalInformation->appendChild($referenceIdentification);
    cargaAtt($AdditionalInformation, array("referenceIdentification"=>$iddocto
					
				       )
				    );
    //IV es numero de factura
    $AdditionalInformation->SetAttribute("type","IV");
    
    $seller = $xml->createElement("seller");
    $seller = $addenda->appendChild($seller);
    //$referenceIdentification = $xml->createElement("referenceIdentification");
    //$referenceIdentification = $AdditionalInformation->appendChild($referenceIdentification);
    cargaAtt($seller, array("gln"=>$vendedor,
			    "alternatePartyIdentification"=>$vendedor
			    ));
    $vende=explode(' ',$vendedor);
    
    $seller->SetAttribute("type",ReglasXCadena($vende[1]));
    // detalle de la adenda
    $nolinea=0;
    for($cad=4;$cad<=count($arraycadena)-1;$cad++){
	$linea=$arraycadena[$cad];
        $datos=explode('|',$linea);
	if($datos[0]=='5'){
	    $nolinea=$nolinea+1;
	    $lineItem = $xml->createElement("lineItem");
	    $lineItem = $addenda->appendChild($lineItem);
	    $lineItem->SetAttribute("type","SimpleInvoiceLineItemType");
	    $lineItem->SetAttribute("number",$nolinea);
	    
	    $tradeItemIdentification = $xml->createElement("tradeItemIdentification");
	    $tradeItemIdentification = $lineItem->appendChild($tradeItemIdentification);
	    cargaAtt($tradeItemIdentification, array("gtin"=>trim($datos[1]),
				    "tradeItemDescriptionInformation"=>trim($datos[2])
				    ));
	    $grossPrice = $xml->createElement("grossPrice");
	    $grossPrice = $lineItem->appendChild($grossPrice);
	    cargaAtt($grossPrice, array("Amount"=>trim($datos[5])));
	    
	    $netPrice = $xml->createElement("netPrice");
	    $netPrice = $lineItem->appendChild($netPrice);
	    cargaAtt($netPrice, array("Amount"=>trim($datos[5]),
				      "AdditionalInformation"=>trim($datos[4])
				      ));
	    
	    $Customs = $xml->createElement("Customs");
	    $Customs = $lineItem->appendChild($Customs);
	    $alternatePartyIdentification = $xml->createElement("alternatePartyIdentification");
	    $alternatePartyIdentification = $Customs->appendChild($alternatePartyIdentification);
	    cargaAtt($alternatePartyIdentification, array("ReferenceDate"=>$fechaamece,
							  "nameAndAddress"=>"SIN NOMBRE",
							  "name"=>"SIN NOMBRE"
							  ));
	     $alternatePartyIdentification->SetAttribute("type",$orderno);
	     
	    $LogisticUnits = $xml->createElement("LogisticUnits");
	    $LogisticUnits = $Customs->appendChild($LogisticUnits);
	    cargaAtt($LogisticUnits, array("serialShippingContainerCode"=>"123,1254"
							  
							  ));
	     $alternatePartyIdentification->SetAttribute("type","BJ");
	    if($carpeta=='Recibo'){
		$importe=$datos[5]-$datos[6];
	    }else{
		$importe=$datos[5]-$datos[13];
	    }
	    $allowanceCharge = $xml->createElement("allowanceCharge");
	    $allowanceCharge = $Customs->appendChild($allowanceCharge);
	    if($carpeta=='Recibo'){
		$percent=0;
		$percent1=0;
		$percent2=0;
		
	    }else{
		$percent=$datos[10];
		$percent1=$datos[11];
		$percent2=$datos[12];
	    }
	    cargaAtt($allowanceCharge, array("specialServicesType"=>"DI",
					    "monetaryAmountOrPercentage"=>$importe,
					    "percentagePerUnit"=>$percent,
					    "percentagePerUnit1"=>$percent1,
					    "percentagePerUnit2"=>$percent2
					    ));
	    
	    $allowanceCharge->SetAttribute("allowanceChargeType","ALLOWANCE_GLOBAL");
	    $allowanceCharge->SetAttribute("allowanceChargeType","ALLOWANCE_GLOBAL");
	    
	}
	
	
    }
    
    
  //  $cadenasellar=$cadenasellar."|SimpleInvoiceType|1.3.1|AMC7.1|ORIGINAL|".$fechaamece;
  //  echo '<br> cadena generada:'.$cadena;
    $cadenasellar ='|'.$cadenasellar."||";
    //echo '<br>cadena enviada:'.$cadenasellar.'<br>';
    // inicializa y termina la cadena original con el doble ||
    $certificado = $myrowtags['FileSAT'];
    $maquina = trim(`uname -n`);
   // echo '<br>nombre maquina'.$maquina;
   
    $ruta = "/var/www/html".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
    $file=$ruta.$certificado.".key.pem";      // Ruta al archivo
    $pkeyid = openssl_get_privatekey(file_get_contents($file));
    //openssl_sign($cadenasellar, $crypttext, $pkeyid, OPENSSL_ALGO_MD5);
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
	  cadena='".$cadenasellar."'
	  where id=".$iddocto;
    $ErrMsg=_('El Sql que fallo fue');
    $DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
    $Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	  
	  
    
    //genera el archivo xld para validacion de cadena correcta en hacienda
    /*
    $xmlsd = new DOMdocument('1.0','UTF-8');
    $root = $xmlsd->createElement("Comprobante");
    $root = $xmlsd->appendChild($root);
    
    

   cargaAtt($root, array("xmlns"=>"http://www.sat.gob.mx/cfd/2",
                          "xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
                          "xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/2  http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv2.xsd"
                         )
                     );

    */

    return($todo); 
}


function cargaAtt(&$nodo, $attr) {
// +-------------------------------------------------------------------------------+
// | Ademas le concatena a la variable global los valores para la cadena origianl  |
// +-------------------------------------------------------------------------------+
global $xml, $cadena;
$quitar = array('sello'=>1,'noCertificado'=>1,'certificado'=>1);
foreach ($attr as $key => $val) {
    $val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5a y 5c
    $val = trim($val);                           // Regla 5b
    if (strlen($val)>0) {   // Regla 6
        $val = utf8_encode(str_replace("|","/",$val)); // Regla 1
        $nodo->setAttribute($key,$val);
        if (!isset($quitar[$key])) 
            if (substr($key,0,3) != "xml" &&
                substr($key,0,4) != "xsi:")
             $cadena .= $val . "|";
    }
}
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
   $val = utf8_encode(str_replace("|","/",$val)); // Regla 1
   $cadena .= $val . "|";
   }
}

function ReglasXCadena($val) {
// +-------------------------------------------------------------------------------+
// | Concatena los atributos a la cadena original                                  |
// +-------------------------------------------------------------------------------+
//global $cadena;
    $val = preg_replace('/\s\s+/', ' ', $val);   // Regla 5a y 5c
    $val = trim($val);                           // Regla 5b
    if (strlen($val)>0) {   // Regla 6
       $val = utf8_encode(str_replace("|","/",$val)); // Regla 1
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
	//echo $SQLAprobacion;
	$ResultAprobacion=DB_query($SQLAprobacion,$db);
	if (DB_num_rows($ResultAprobacion)>0) {
		$myrowaprobacion = DB_fetch_array($ResultAprobacion);
		$aprobacionxfoliox=$myrowaprobacion['certificado'].'|'.$myrowaprobacion['noAprobacion'].'|'.$myrowaprobacion['anioAprobacion'];
	}
	return $aprobacionxfoliox;
} 






?>