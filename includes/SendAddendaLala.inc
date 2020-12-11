<?php
    // CONSULTO ORIGEN DE ADDENDA
   //$SQL = "select idorigen from debtortrans where id=".$iddocto;
   //$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de servicio de venta no se pudo actualizar');
   //$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
   //$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
   //$myroworigen=DB_fetch_array($Result);
    // CONSULTO DETALLE DE ADDENDA
  //$SQL = "select * from facturacion.encabezado where id_encabezado=".$myroworigen[0];
   $SQL = "select * from debtortrans
   INNER JOIN custbranch on debtortrans.branchcode=custbranch.branchcode
   INNER JOIN debtorsmaster on debtortrans.debtorno=debtorsmaster.debtorno
   INNER JOIN paymentterms ON paymentterms.termsindicator=debtorsmaster.paymentterms
   
   where id=".$iddocto;
   $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de servicio de venta no se pudo actualizar');
   $DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
   $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
   $myrowAddenda=DB_fetch_array($Result);


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
    $requestForPayment->appendChild($requestForPaymentIdentification);
    $entityType = $xml->createElement("entityType");
    $requestForPaymentIdentification->appendChild($entityType);
    $entityType->appendChild($xml->createTextNode($tipodocto));

    $uniqueCreatorIdentification = $xml->createElement("uniqueCreatorIdentification");
    $requestForPaymentIdentification->appendChild($uniqueCreatorIdentification);
    $uniqueCreatorIdentification->appendChild($xml->createTextNode($nufa));


     // ZZZ: es cantidad en letras
    $specialInstruction = $xml->createElement("specialInstruction");
    $specialInstruction = $requestForPayment->appendChild($specialInstruction);
    //cargaAtt($specialInstruction, array("text"=>$cantidadletra));
    $myrowAddenda["add_SIspecialInstructionCode"]='ZZZ';
    $specialInstruction->SetAttribute("code",$myrowAddenda["add_SIspecialInstructionCode"]);
    
    $text1 = $xml->createElement("text");
    $specialInstruction->appendChild($text1);
    $text1->appendChild($xml->createTextNode($cantidadletra));
  
    $orderIdentification=$xml->createElement("orderIdentification");
    $orderIdentification=$requestForPayment->appendChild($orderIdentification);
    $referenceIdentification = $xml->createElement("referenceIdentification");
    $referenceIdentification = $orderIdentification->appendChild($referenceIdentification);
    $referenceIdentification->appendChild($xml->createTextNode($myrowAddenda["nopedidof"]));
    $ReferenceDate = $xml->createElement("ReferenceDate");
    $orderIdentification->appendChild($ReferenceDate);
    $ReferenceDate->appendChild($xml->createTextNode($fechaamece));
    $myrowAddenda["add_OIreferenceIdentificationType"]='ON';
    $referenceIdentification->SetAttribute("type",$myrowAddenda["add_OIreferenceIdentificationType"]);
   
    $AdditionalInformation=$xml->createElement("AdditionalInformation");
    $AdditionalInformation=$requestForPayment->appendChild($AdditionalInformation);
    
    $referenceIdentification = $xml->createElement("referenceIdentification");
    $referenceIdentification = $AdditionalInformation->appendChild($referenceIdentification);
    $referenceIdentification->appendChild($xml->createTextNode($myrowAddenda["noentradaf"]));
    $referenceIdentification->SetAttribute("type","DIV");
  
    $buyer = $xml->createElement("buyer");
    $requestForPayment->appendChild($buyer);
    $contactInformation = $xml->createElement("contactInformation");
    $buyer->appendChild($contactInformation);
    $personOrDepartmentName = $xml->createElement("personOrDepartmentName");
    $contactInformation->appendChild($personOrDepartmentName);
    $tex2 = $xml->createElement("text");
    $personOrDepartmentName->appendChild($tex2);
    $myrowAddenda["add_PODNpersonOrDepartmentName"]='contacto';
    
    $tex2->appendChild($xml->createTextNode($myrowAddenda["add_PODNpersonOrDepartmentName"]));

    $seller = $xml->createElement("seller");
    $requestForPayment->appendChild($seller);
    $alternatePartyIdentification = $xml->createElement("alternatePartyIdentification");
    $seller->appendChild($alternatePartyIdentification);
    $alternatePartyIdentification->appendChild($xml->createTextNode($myrowAddenda["noremisionf"]));
    $myrowAddenda["add_APIalternatePartyIdentificationType"]='SELLER_ASSIGNED_IDENTIFIER_FOR_A_PARTY';
    $alternatePartyIdentification->SetAttribute("type",$myrowAddenda["add_APIalternatePartyIdentificationType"]);
    
    $shipTo = $xml->createElement("shipTo");
    $requestForPayment->appendChild($shipTo);
    $nameAndAddress = $xml->createElement("nameAndAddress");
    $shipTo->appendChild($nameAndAddress);
    
    $name= $xml->createElement("name");
    $nameAndAddress->appendChild($name);
    $name->appendChild($xml->createTextNode($myrowAddenda["brname"]));
    
    $streetAddressOne = $xml->createElement("streetAddressOne");
    $nameAndAddress->appendChild($streetAddressOne);
    $streetAddressOne->appendChild($xml->createTextNode($myrowAddenda["braddress1"]));
    
    $city = $xml->createElement("city");
    $nameAndAddress->appendChild($city);
    $city->appendChild($xml->createTextNode(ReglasXCadena($myrowAddenda["braddress3"])));
    
    $postalCode = $xml->createElement("postalCode");
    $nameAndAddress->appendChild($postalCode);
    $postalCode->appendChild($xml->createTextNode(ReglasXCadena($myrowAddenda["braddress5"])));
    
    $currency = $xml->createElement("currency");
    $requestForPayment->appendChild($currency);
    $currencyFunction = $xml->createElement("currencyFunction");
    $currency->appendChild($currencyFunction);
    $myrowAddenda["add_CURcurrencyFunction"]='BILLING_CURRENCY';
    $currencyFunction->appendChild($xml->createTextNode($myrowAddenda["add_CURcurrencyFunction"]));
    $rateOfChange = $xml->createElement("rateOfChange");
    $currency->appendChild($rateOfChange);
    $rateOfChange->appendChild($xml->createTextNode(1/$myrowAddenda["rate"]));
    $currency->SetAttribute("currencyISOCode",$myrowAddenda["currcode"]);
    $paymentTerms = $xml->createElement("paymentTerms");
    $requestForPayment->appendChild($paymentTerms);
    $netPayment = $xml->createElement("netPayment");
    $paymentTerms->appendChild($netPayment);
    $myrowAddenda["add_PTNPnetPaymentTermsType"]='BASIC_NET';
    $netPayment->SetAttribute("netPaymentTermsType",$myrowAddenda["add_PTNPnetPaymentTermsType"]);
    $paymentTimePeriod = $xml->createElement("paymentTimePeriod");
    $netPayment->appendChild($paymentTimePeriod);
    $timePeriodDue = $xml->createElement("timePeriodDue");
    $paymentTimePeriod->appendChild($timePeriodDue);
    $myrowAddenda["add_PTTPDtimePeriodDueType"]='DAYS';
    $timePeriodDue->SetAttribute("timePeriod",$myrowAddenda["add_PTTPDtimePeriodDueType"]);
    $value = $xml->createElement("value");
    $timePeriodDue->appendChild($value);
    //$myrowAddenda["add_PTTPDtimePeriodDueValue"]='DATE_OF_INVOICE';
    $value->appendChild($xml->createTextNode($myrowAddenda["daysbeforedue"]));
    $myrowAddenda["add_PTpaymentTermsEvent"]='DATE_OF_INVOICE';
    $paymentTerms->SetAttribute("paymentTermsEvent",$myrowAddenda["add_PTpaymentTermsEvent"]);
    $myrowAddenda["add_PTpaymentTermsRelationTime"]='REFERENCE_AFTER';
    $paymentTerms->SetAttribute("PaymentTermsRelationTime",$myrowAddenda["add_PTpaymentTermsRelationTime"]);
    
    // detalle de la adenda
    $nolinea=0;
    for($cad=4;$cad<=count($arraycadena)-1;$cad++){
	$linea=$arraycadena[$cad];
        $datos=explode('|',$linea);
	if($datos[0]=='5'){
	    $nolinea=$nolinea+1;
	    $lineItem = $xml->createElement("lineItem");
	    $lineItem = $requestForPayment->appendChild($lineItem);
	    $lineItem->SetAttribute("type","SimpleInvoiceLineItemType");
	    $lineItem->SetAttribute("number",$nolinea);
	    
	    $alternateTradeItemIdentification = $xml->createElement("alternateTradeItemIdentification");
	    $lineItem->appendChild($alternateTradeItemIdentification);
	    $alternateTradeItemIdentification->appendChild($xml->createTextNode($datos[1]));
	    $alternateTradeItemIdentification->SetAttribute("type","BUYER_ASSIGNED");
	    
	    $tradeItemDescriptionInformation = $xml->createElement("tradeItemDescriptionInformation");
	    $lineItem->appendChild($tradeItemDescriptionInformation);
	    $longText = $xml->createElement("longText");
	    $tradeItemDescriptionInformation->appendChild($longText);
	    $longText->appendChild($xml->createTextNode(ReglasXCadena($datos[4])));
	    $alternateTradeItemIdentification->SetAttribute("language","ES");
	    
	    $invoicedQuantity = $xml->createElement("invoicedQuantity");
	    $lineItem->appendChild($invoicedQuantity);
	    $invoicedQuantity->appendChild($xml->createTextNode($datos[3]));
	    $invoicedQuantity->SetAttribute("unitOfMeasure",$datos[7]);
	    
	    $grossPrice= $xml->createElement("grossPrice");
	    $lineItem->appendChild($grossPrice);
	    $Amount= $xml->createElement("Amount");
	    $grossPrice->appendChild($Amount);
	    $Amount->appendChild($xml->createTextNode($datos[5]));
	    
	    $netPrice= $xml->createElement("netPrice");
	    $lineItem->appendChild($netPrice);
	    $Amount= $xml->createElement("Amount");
	    $netPrice->appendChild($Amount);
	    $Amount->appendChild($xml->createTextNode($datos[5]));
	    
	    $SQL = "select * from stockmaster inner join taxauthrates on taxauthrates.taxcatid=stockmaster.taxcatid where stockid='".$datos[1]."' limit ".$nolinea;
	    $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de servicio de venta no se pudo actualizar');
	    $DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	    $myrowDetAddenda=DB_fetch_array($Result);
	    
	    $tradeItemTaxInformation= $xml->createElement("tradeItemTaxInformation");
	    $lineItem->appendChild($tradeItemTaxInformation);
	    $taxTypeDescription= $xml->createElement("taxTypeDescription");
	    $tradeItemTaxInformation->appendChild($taxTypeDescription);
	    $taxTypeDescription->appendChild($xml->createTextNode("VAT"));
	    
	    $tradeItemTaxAmount= $xml->createElement("tradeItemTaxAmount");
	    $tradeItemTaxInformation->appendChild($tradeItemTaxAmount);
	    
	    $taxPercentage= $xml->createElement("taxPercentage");
	    $tradeItemTaxAmount->appendChild($taxPercentage);
	    $taxPercentage->appendChild($xml->createTextNode($myrowDetAddenda["taxrate"]));
	    
	    $taxAmount= $xml->createElement("taxAmount");
	    $tradeItemTaxAmount->appendChild($taxAmount);
	    $taxAmount->appendChild($xml->createTextNode(($datos[5]*.16)));
	    
	    $totalLineAmount= $xml->createElement("totalLineAmount");
	    $lineItem->appendChild($totalLineAmount);
	    $grossAmount= $xml->createElement("grossAmount");
	    $totalLineAmount->appendChild($grossAmount);
	    
	    $Amount= $xml->createElement("Amount");
	    $grossAmount->appendChild($Amount);
	    $Amount->appendChild($xml->createTextNode($datos[6]));
	    
	    $netAmount= $xml->createElement("netAmount");
	    $totalLineAmount->appendChild($netAmount);
	    
	    $Amount= $xml->createElement("Amount");
	    $netAmount->appendChild($Amount);
	    $Amount->appendChild($xml->createTextNode($datos[6]));
	    $PERCIVA=$myrowDetAddenda["taxrate"];
	}
	
    }
    $linea=$arraycadena[0];
    $datos=explode('|',$linea);
    $totalAmount = $xml->createElement("totalAmount");
    $requestForPayment->appendChild($totalAmount);
    $Amount= $xml->createElement("Amount");
    $totalAmount->appendChild($Amount);
    $Amount->appendChild($xml->createTextNode($datos[5]));
	    
    $TotalAllowanceCharge = $xml->createElement("TotalAllowanceCharge");
    $requestForPayment->appendChild($TotalAllowanceCharge);
    $Amount= $xml->createElement("Amount");
    $TotalAllowanceCharge->appendChild($Amount);
    $myrowAddenda["add_TACAmount"]=0;
    $Amount->appendChild($xml->createTextNode($myrowAddenda["add_TACAmount"]));
    $myrowAddenda["add_TACallowanceOrChargeType"]='ALLOWANCE';
    $TotalAllowanceCharge->SetAttribute("allowanceOrChargeType",$myrowAddenda["add_TACallowanceOrChargeType"]);
    
    $baseAmount = $xml->createElement("baseAmount");
    $requestForPayment->appendChild($baseAmount);
    $Amount= $xml->createElement("Amount");
    $baseAmount->appendChild($Amount);
    $Amount->appendChild($xml->createTextNode($datos[5]));
    $tax = $xml->createElement("tax");
    $requestForPayment->appendChild($tax);
    $myrowAddenda["add_TAXType"]='VAT';
    $tax->SetAttribute("type",$myrowAddenda["add_TAXType"]);
    $taxPercentage= $xml->createElement("taxPercentage");
    $tax->appendChild($taxPercentage);
 
    $taxPercentage->appendChild($xml->createTextNode($PERCIVA));
    $taxAmount= $xml->createElement("taxAmount");
    $tax->appendChild($taxAmount);
    $taxAmount->appendChild($xml->createTextNode(abs($myrowAddenda["ovgst"])));
    $taxCategory= $xml->createElement("taxCategory");
    $tax->appendChild($taxCategory);
    $myrowAddenda["add_TAXTaxCategory"]='TRANSFERIDO';
    $taxCategory->appendChild($xml->createTextNode($myrowAddenda["add_TAXTaxCategory"]));
    
    $payableAmount = $xml->createElement("payableAmount");
    $requestForPayment->appendChild($payableAmount);
    $Amount= $xml->createElement("Amount");
    $payableAmount->appendChild($Amount);
    $Amount->appendChild($xml->createTextNode($datos[6]));
    

    
    
?>