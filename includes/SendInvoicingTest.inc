<?php
/*

 FECHA DE MODIFICACION: 14-may-2012
 CAMBIOS: 
	1. Cambios SAT 2012
	ALTER TABLE `legalbusinessunit` ADD COLUMN `regimenfiscal` 
	VARCHAR(100) DEFAULT '' COMMENT 'Regimen fiscal en el que tributa la empresa' AFTER `cedula`;
 FIN DE CAMBIOS
 


*/
// +-------------------------------------------------------------------------------+
// | Funcion para generacion de documentos de tipo CFD                             |
// +-------------------------------------------------------------------------------+
function generaXML($cadena_original,$tipocomprobante,$tagref,$serie,$folio,$iddocto,$carpeta,$orderno=0,$db) {
    //echo $cadena_original;
    global $xml, $cadena, $conn, $sello,$cadenasellar,$totalimporte;
    $banderaimpuestos=false;
    $banderaconceptos=false;
    $banderaimpuestosret=false;
    $cadena=str_replace(chr(13).chr(10).'0','@%',$cadena_original);
//    error_reporting(E_ALL);
    $tipocomprobante=strtolower($tipocomprobante);
    $noatt=  array();
    $arraycadena= array();
    $nufa = $serie.$folio;    // Junta el numero de factura   serie + folio
    $impuestofact=0;
    $cadenasellar="";
    $xml = new DOMdocument('1.0','UTF-8');
    $root = $xml->createElement("Comprobante");
    $root = $xml->appendChild($root);
    $fechaDocumento = '';

    cargaAtt($root, array("xmlns"=>"http://www.sat.gob.mx/cfd/2",
                          "xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
                          "xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/2  http://www.sat.gob.mx/sitio_internet/cfd/2/cfdv22.xsd"
                         )
                     );
      $SQL=" SELECT l.taxid,a.address5,t.tagdescription as tagname,t.areacode,l.legalid,l.legalname as empresa,a.areadescription as legalname,
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
                }elseif($cad>=4 and $datos[0]=='7'){
			    
			$impuestosinifactret=$impuestosinifactret+trim($datos[3]);
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
		    $fechaDocumento = $datos[4];
		    $aprobaxfolio=TraeAprobacionxFolio($rfc,$serie,$folio,$db, $fechaDocumento);
		    $aprobacionfolios=explode('|',$aprobaxfolio);
		    $Certificado=$aprobacionfolios[0];
		    $Noaprobacion=$aprobacionfolios[1];
		    $anioAprobacion=$aprobacionfolios[2];
		    
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
					"condicionesDePago"=>$datosdos[2],
					"subTotal"=>$totalimporte,
					"descuento"=>number_format($datos[8],2),
					"TipoCambio"=>$datos[13],
					"Moneda"=>$datos[12],
					"total"=>number_format($totalimporte+($impuestosinifact-$impuestosinifactret),2,'.',''),				
					"metodoDePago"=>$datosdos[3],
					"LugarExpedicion"=>$myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido'],
					"NumCtaPago"=>$datosdos[5]
					)
                             );
		    $banderastatus=$datos[15];
		    $fechaamece=str_replace(' ','T',str_replace('/','-',$datos[4]));
		    $cantidadletra=$datos[11];
		    $cadenasellar=$cadenasellar."|2.2|".trim($datos[2])."|".trim($datos[3])."|".trim(str_replace(' ','T',str_replace('/','-',$datos[4])));
		    $cadenasellar=$cadenasellar."|".trim($Noaprobacion)."|".trim($anioAprobacion)."|".trim($tipocomprobante);
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[1]))."|".trim(ReglasXCadena($datosdos[2]));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte));
		    $cadenasellar=$cadenasellar."|".trim(number_format($datos[8],2));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena(number_format($totalimporte+($impuestosinifact-$impuestosinifactret),2,'.','')));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));
		
		 
		 
		 $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido']));
		 if(strlen($datosdos[5])>0){
		    
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[5]));   
		 }
		 
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
			$regimenfiscal = $xml->createElement("RegimenFiscal");
                        $regimenfiscal = $emisor->appendChild($regimenfiscal);
                        cargaAtt($regimenfiscal, array("Regimen"=>$myrowtags['regimenfiscal']
                                                )
                                );
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['regimenfiscal']));
			
	        }elseif($cad==2){
                    
                        $receptor = $xml->createElement("Receptor");
                        $receptor = $root->appendChild($receptor);
                        cargaAtt($receptor, array("rfc"=>trim($datos[2]),
                              "nombre"=>trim($datos[3])
                          )
                        );
			//echo '<pre>car:'.(trim($datos[3]))."<br><br><br><br>";
			$rfccliente=trim($datos[2]);
			$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena(DB_escape_string($datos[3])));
			
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
			    //$importe=$datos[5]*$datos[3];//$datos[13]; number_format($myrow2['fxprice'],2,'.','')
			    $importe=$datos[12];//$datos[13]; number_format($myrow2['fxprice'],2,'.','')
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
			$cadenasellar=$cadenasellar."|".trim($datos[5])."|".trim($importe);
			
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
			 if ($datos[2]>0){
			      if ($banderaimpuestos==false){
				  if ($banderaimpuestosret==false){
				      $impuestos = $xml->createElement("Impuestos");
				      $impuestos = $root->appendChild($impuestos);
				  }	
				  $traslados = $xml->createElement("Traslados");
				  $traslados = $impuestos->appendChild($traslados);
				  
				  $banderaimpuestos=true;
			      }
			    /*  $traslado = $xml->createElement("Traslado");
			      $traslado = $traslados->appendChild($traslado);
			      cargaAtt($traslado, array("impuesto"=>trim($datos[1]),
							"tasa"=>trim($datos[2]),
							"importe"=>trim($datos[3])
						       )
						   );
			       
			      $impuestofact=$impuestofact+trim($datos[3]);
			      $cadenasellarimp=$cadenasellarimp."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
			    */
			    
			    if($rfccliente != 'MTE010529QV8') {
				  $traslado = $xml->createElement("Traslado");
				  $traslado = $traslados->appendChild($traslado);
				  cargaAtt($traslado, array("impuesto"=>trim($datos[1]),
							    "tasa"=>trim($datos[2]),
							    "importe"=>trim($datos[3])
							   )
						       );
				   
				  $impuestofact=$impuestofact+trim($datos[3]);
				  $cadenasellarimp=$cadenasellarimp."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($datos[3]));
			    }else{
				    if ($banderaimpuestosFem==false){
						$impuestosinifact = number_format($impuestosinifact,6,'.','');

						$traslado = $xml->createElement("Traslado");
						$traslado = $traslados->appendChild($traslado);
						cargaAtt($traslado, array("impuesto"=>trim($datos[1]),
							    				"tasa"=>trim($datos[2]),
												"importe"=>trim($impuestosinifact)
												   )
						       );
					
					$cadenasellarimp=$cadenasellarimp."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[2]))."|".trim(ReglasXCadena($impuestosinifact));
					$banderaimpuestosFem=true;
					
				    }
				    
				    
				     $impuestofact=$impuestofact+trim($datos[3]);
			    }
			    
			    
			    
			    
			 }
                }elseif($cad>=4 and $datos[0]=='7'){
						if ($banderaimpuestosret==false){
							$impuestos = $xml->createElement("Impuestos");
							$impuestos = $root->appendChild($impuestos);
							$Retenciones = $xml->createElement("Retenciones");
							$Retenciones = $impuestos->appendChild($Retenciones);
							
							$banderaimpuestosret=true;
						}
						$Retencion = $xml->createElement("Retencion");
									$Retencion = $Retenciones->appendChild($Retencion);
									cargaAtt($Retencion, array("impuesto"=>trim($datos[1]),
															   
															  "importe"=>trim($datos[3])
															 )
														 );
									 
						$impuestofactret=$impuestofactret+trim($datos[3]);
						$cadenasellarimptret=$cadenasellarimptret."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[3]));
                }elseif($cad>=4 and $datos[0]=='8'){
						if ($banderaimpuestosret==false){
							//$impuestos = $xml->createElement("Impuestos");
							//$impuestos = $root->appendChild($impuestos);
							$Retenciones = $xml->createElement("Retenciones");
							$Retenciones = $impuestos->appendChild($Retenciones);
							
							$banderaimpuestosret=true;
						}
						$Retencion = $xml->createElement("Retencion");
									$Retencion = $Retenciones->appendChild($Retencion);
									cargaAtt($Retencion, array("impuesto"=>trim($datos[1]),
															   
															  "importe"=>trim($datos[3])
															 )
														 );
									 
						$impuestofactret=$impuestofactret+trim($datos[3]);
						$cadenasellarimptret=$cadenasellarimptret."|".trim(ReglasXCadena($datos[1]))."|".trim(ReglasXCadena($datos[3]));
                }//fin de if de cad
		
		
		//fin de if de cad
		
		
        }
	
    $cadenasellar=$cadenasellar.$cadenasellarimptret;
    
    if ($banderaimpuestosret==false){
	$cadenasellar=$cadenasellar;//."||";
    }else{
	$cadenasellar=$cadenasellar."|".$impuestofactret;//."||";
    }
    
    if ($banderaimpuestosret==true){
        $impuestos->SetAttribute("totalImpuestosRetenidos",$impuestofactret);
    }
    $cadenasellar=$cadenasellar.$cadenasellarimp;
    
    
    if ($banderaimpuestos==true){
		if($rfccliente != 'MTE010529QV8')
        	$impuestos->SetAttribute("totalImpuestosTrasladados",$impuestofact);
		else{
      		$impuestofact = number_format($impuestofact,6,'.','');
			$impuestos->SetAttribute("totalImpuestosTrasladados",$impuestofact);
		}
    }else{
	$impuestos = $xml->createElement("Impuestos");
	$impuestos = $root->appendChild($impuestos);
    }
    
    if ($banderaimpuestos==false){
	$cadenasellar=$cadenasellar;//."||";
    }else{
	$cadenasellar=$cadenasellar."|".$impuestofact;//."||";
    }
    
   
    
    //incluye la addenda de lala
    if($rfccliente=='CLD0507145H6'){
        include('SendAddendaLala.inc');
    }
    
    //Addenda Femsa
    if($rfccliente == 'MTE010529QV8') {
    	include('SendAddendaFemsa.inc');
    }
    
    
    $cadenasellar ='|'.$cadenasellar."||";
    // inicializa y termina la cadena original con el doble ||
    $fechaDocumento = strtotime($fechaDocumento);
    $fechaCorte = strtotime("2013-04-19 08:00:00");
    
    $certificado = $myrowtags['FileSAT'] . '2013';
    if($fechaDocumento > $fechaCorte) {
    	$certificado = $myrowtags['FileSAT'];
    }
    
    $maquina = trim(`uname -n`);
    $ruta = "/var/www/html".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/";
    $file=$ruta.$certificado.".key.pem";      // Ruta al archivo
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
	//echo "entra".$dir.$nufa;
        //$xml->save($dir.$nufa.".xml");
	//$nufa=$nufa.date('YmdHi');
	unlink($dir.$nufa.".xml");
        $xml->save($dir.$nufa.".xml");
    } else {
        $paso = $todo;
        $conn->replace("cfdsello",array("selldocu"=>$nufa,"sellcade"=>$cadena_original,"sellxml"=>$paso),"selldocu",true);
    }
    // }}}
    // echo "antes de return = $todo\n";
   
    //guardamos la cadena y sello en la base de datos
    if ($banderastatus==0){
	
	$sql="update debtortrans
	  set sello='".$sello."',
	  cadena='".DB_escape_string($cadenasellar)."',
	  transactiondate=now(),
	  ref2='PROCESADOPRINT'
	  where id=".$iddocto;
	
    }else{
	$sql="update debtortrans
	  set sello='".$sello."',
	  cadena='".DB_escape_string($cadenasellar)."',
	  transactiondate=now(),
	  ref2='PROCESADO'
	  where id=".$iddocto;
	
    }
    $ErrMsg=_('El Sql que fallo fue');
    $DbgMsg=_('No se pudo actualizar el sello y cadena del documento');
    $Result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);	  

    return($todo); 
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

       cargaAtt($root, array("xmlns"=>"http://www.sat.gob.mx/cfd/3",
                          "xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
			  "xmlns:cfdi"=>"http://www.sat.gob.mx/cfd/3",
			  "xmlns:ecfd"=>"http://www.southconsulting.com/schemas/strict",
                          "xsi:schemaLocation"=>"http://www.sat.gob.mx/cfd/3  http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv3.xsd"
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
		    
		    cargaAtt($root,array(
					"version"=>"3.2",
					"fecha"=>str_replace(' ','T',str_replace('/','-',$datos[4])),
					"sello"=>"@",
					"tipoDeComprobante"=>trim($tipocomprobante),
					"formaDePago"=>$datosdos[1],
					"noCertificado"=>trim($Certificado),
					"certificado"=>"@",
					"subTotal"=>$totalimporte,
					"descuento"=>number_format($datos[8],2),
					"total"=>number_format($totalimporte+$impuestosinifact,2,'.',''),
					"metodoDePago"=>$datosdos[3],
					"TipoCambio"=>$datos[13],
					"Moneda"=>$datos[12],
					"LugarExpedicion"=>$myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido'],
					"NumCtaPago"=>"No identificado"
					)
                             );
		    $fechaamece=str_replace(' ','T',str_replace('/','-',$datos[4]));
		    $cantidadletra=$datos[10];
		    $cadenasellar=$cadenasellar."|3.2|".trim(str_replace(' ','T',str_replace('/','-',$datos[4])));
		    $cadenasellar=$cadenasellar."|".trim($tipocomprobante);
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[1]));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte));
		    $cadenasellar=$cadenasellar."|".trim(number_format($datos[8],2));
		    //$cadenasellar=$cadenasellar."|".trim($Certificado);
		    //$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($totalimporte))."|".trim(number_format($datos[8],2))."|".trim(ReglasXCadena(number_format($totalimporte+$impuestosinifact,2,'.','')));
		    //$cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[13]));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datos[12]));
		    $cadenasellar=$cadenasellar."|".number_format($totalimporte+$impuestosinifact,2,'.','');
		     $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($datosdos[3]));
		    $cadenasellar=$cadenasellar."|".trim(ReglasXCadena($myrowtags['municipioexpedido'].','.$myrowtags['estadoexpedido']));
		    $cadenasellar=$cadenasellar."|No identificado";
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
                        $cadenasellar=$cadenasellar."|".trim($rfc)."|".trim($legalname);
			
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
				   "pais"=>"MEXICO",
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
			$cadenasellar=$cadenasellar."|".trim($datos[5])."|".trim($importe);
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
	$cadenasellar=$cadenasellar."|".$impuestofact;//."||";
    }    
    $cadenasellar ='|'.$cadenasellar."||";
    //echo '<br><br>'.$cadenasellar.'<br><br>';
    // inicializa y termina la cadena original con el doble ||
    $certificado = $myrowtags['FileSAT'];
    $maquina = trim(`uname -n`);
    $ruta = "/var/www/html".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/";
    $file=$ruta.$certificado.".key.pem";      // Ruta al archivo
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
    $xml->formatOutput = true;
    $todo = $xml->saveXML();
    $dir="/var/www/html/".dirname($_SERVER['PHP_SELF'])."/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace('.','',str_replace(' ','',$legalname))."/XML/".$carpeta."/";
    if ($dir != "/dev/null") {
        $xml->formatOutput = true;
	$nufa=$nufa.date('YmdHi');
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
   // echo '<br>todo:<br>'.$todo;
    
    //printf ("<pre>%s</pre>", htmlentities ($todo));

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
    $val = preg_replace('/\t/', ' ', $val);   // Regla 5a
    $val = preg_replace('/\r/', ' ', $val);   // Regla 5a
    $val = preg_replace('/\n/', ' ', $val);   // Regla 5a
    $val = trim($val);                             // Regla 5b
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

function TraeAprobacionxFolio($rfcempre,$serieap,$folioap, &$db, $fechaEmision = '')
{
    global $aprobacionxfoliox;
    
    $tablaAprobacion = "AprobacionFolios2013";
    if(empty($fechaEmision) == FALSE) {
    	$fechaEmision = strtotime($fechaEmision);
    	$fechaCorte = strtotime("2013-04-19 08:00:00");
    	if($fechaEmision > $fechaCorte) {
    		$tablaAprobacion = "AprobacionFolios";
    	}
    }
    
	$SQLAprobacion="SELECT  anioAprobacion,noAprobacion,certificado
	      FROM $tablaAprobacion
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