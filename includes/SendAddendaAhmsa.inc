<?php

   $SQL = "select * from debtortrans
   inner join salesorders on salesorders.orderno=debtortrans.order_
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
    
    cargaAtt($addenda, array("xmlns:ahmsa"=>"http://www.ahmsa.com/xsd/AddendaAHM1",
    	"xsi:schemaLocation"=>"http://www.ahmsa.com/xsd/AddendaAHM1 http://www.ahmsa.com/xsd/AddendaAHM1/AddendaAHM.xsd"
    	)
    );
    
    $requestForPayment=$xml->createElement("ahmsa:AddendaAHM");
    $requestForPayment=$addenda->appendChild($requestForPayment);
    
    cargaAtt($requestForPayment, array("Version"=>"1.0"));
    
   
    
    $requestForPaymentIdentification = $xml->createElement("ahmsa:Documento");
    $requestForPayment->appendChild($requestForPaymentIdentification);
    cargaAtt($requestForPaymentIdentification, array("Tipo"=>"1","Clase"=>"PE"));
    
    $entityHeader = $xml->createElement("ahmsa:Encabezado");
    $requestForPaymentIdentification->appendChild($entityHeader);
    cargaAtt($entityHeader, array("NumSociedad"=>"S001",
    "NumDivision"=>"1",
    "NumProveedor"=>"120158",   
    "Correo"=>$_SESSION["FEMSAMail"],
    "Moneda"=>$myrowAddenda['currcode']
    ));
    
    $entityHeader->setAttribute("NumDivision",'');
    
    $Detalle = $xml->createElement("ahmsa:Detalle");
    $requestForPaymentIdentification->appendChild($Detalle);
  
    $DetallePedido = $xml->createElement("ahmsa:Pedido");
    $Detalle->appendChild($DetallePedido);    
    cargaAtt($DetallePedido, array("Num"=>$myrowAddenda['nopedido'])); 
    
    $DetallePedidoRec = $xml->createElement("ahmsa:Recepcion");
    $DetallePedido->appendChild($DetallePedidoRec);
    $DetallePedidoRec->appendChild($xml->createTextNode($myrowAddenda['noentrada']));
   // $entityType->appendChild($xml->createTextNode($tipodocto));
    
    
?>