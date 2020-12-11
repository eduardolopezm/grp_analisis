<?php
/*
 ELABORO: Desarrollador
 FECHA DE MODIFICACION: 08-OCT-2012
 CAMBIOS: 
	1. Se genero funcionalidad   
 FIN DE CAMBIOS
*/



include('ProcessNoteCreditByCostumer.inc');
include('RegistraFactura.inc');

$SQL = "SELECT debtorsmaster.name,debtorsmaster.address1,debtorsmaster.coments,custbranch.taxid AS rfc, currencies.currency, paymentterms.terms,
		debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription,custbranch.phoneno AS tel ,
                debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,debtorsmaster.address6,
                custbranch.email,custbranch.defaultlocation,custbranch.salesman,custbranch.deliverblind,custbranch.defaultshipvia
	FROM debtorsmaster,
	     paymentterms,
	     holdreasons,
	     currencies,
	     custbranch
	WHERE
	     debtorsmaster.paymentterms = paymentterms.termsindicator
	     AND debtorsmaster.currcode = currencies.currabrev
	     AND debtorsmaster.debtorno = custbranch.debtorno
	     AND debtorsmaster.holdreason = holdreasons.reasoncode
	     AND debtorsmaster.debtorno = '" . $_SESSION['CustomerIDestinoRemisionInvoice'] . "'";
$ErrMsg =_('The customer details could not be retrieved by the SQL because');
$CustomerResultDestino = DB_query($SQL,$db,$ErrMsg);
if(DEBUG) {
	echo "<pre>$SQL</pre><br/>";
}

$CustomerRecordDestino = DB_fetch_array($CustomerResultDestino);
//round($CustomerRecord['overdue2'],2);
$telefonoDestino=$CustomerRecordDestino['tel'];
$rfcDestino=$CustomerRecordDestino['rfc'];
$DeliverTo=$CustomerRecordDestino['name'];
$DelAdd1=$CustomerRecordDestino['address1'];
$DelAdd2=$CustomerRecordDestino['address2'];
$DelAdd3=$CustomerRecordDestino['address3'];
$DelAdd4=$CustomerRecordDestino['address4'];
$DelAdd5=$CustomerRecordDestino['address5'];
$DelAdd6=$CustomerRecordDestino['address6'];
$Email=$CustomerRecordDestino['email'];
$Deliverblind=$CustomerRecordDestino['deliverblind'];
$Defaultshipvia=$CustomerRecordDestino['defaultshipvia'];

//$_POST['Salesman']=$CustomerRecordDestino['salesman']; se comento porque el valor viene en el arreglo del POST porque se agrego un combo para seleccionar al vendedor

$sql="SELECT termsindicator FROM paymentterms WHERE daysbeforedue='1' and numberOfPayments='0'";
$result =DB_query($sql,$db);
$myrow= DB_fetch_array($result);
$terminopago=$myrow['termsindicator'];

$CurrAbrev=$_POST['currcode'];
$CurrencyRate=1;
if ($CurrAbrev!=$_SESSION['CountryOfOperation']){
	$fechaemisionx=$_POST['fechafactura'];
	$separae = explode('/',$fechaemisionx);
	$diae=$separae[0];;
	$mese = $separae[1];
	$anioe = $separae[2];
	$fechaemisionpago=$anioe.'-'.$mese.'-'.$diae;
	$ratepago=GetCurrencyRateByDate($fechaemisionpago,$CurrAbrev,$db);
	if($ratepago!=0){
	    $CurrencyRate=$ratepago;
	}
}
$DeliveryDate = Date($_POST['fechafactura']);
$QuoteDate = Date($_POST['fechafactura']);
$ConfirmedDate = Date($_POST['fechafactura']);
$DelDate = FormatDateforSQL($DeliveryDate);
$QuotDate = FormatDateforSQL($QuoteDate);
$ConfDate = FormatDateforSQL($ConfirmedDate);

$defaultlocation=$CustomerRecordDestino['defaultlocation'];
$OrderNo = GetNextTransNo(30, $db);
$HeaderSQL = 'INSERT INTO salesorders ( orderno,
                                        debtorno,
                                        branchcode,
                                        comments,
                                        orddate,
                                        ordertype,
                                        shipvia,
                                        deliverto,
                                        deladd1,
                                        deladd2,
                                        deladd3,
                                        deladd4,
                                        deladd5,
                                        deladd6,
                                        contactphone,
                                        contactemail,
                                        freightcost,
                                        fromstkloc,
                                        deliverydate,
                                        quotedate,
                                        confirmeddate,
                                        quotation,
                                        deliverblind,
                                        salesman,
                                        
                                        tagref,
                                        taxtotal,
                                        currcode,
                                        paytermsindicator,
                                        UserRegister,
										serie
                                       )
                                VALUES (
                                        '. $OrderNo . ',
                                        '."'" . $_SESSION['CustomerIDestinoRemisionInvoice']. "'".',
                                        '."'" . $_SESSION['BranchcodeDestinoRemisionInvoice'] . "'".',
                                        
                                        '."'". DB_escape_string("Factura de remision") ."'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'PL'".',
                                        1,
                                        '."'". DB_escape_string($DeliverTo) . "'".',
                                        '."'" . DB_escape_string($DelAdd1) . "'".',
                                        '."'" . DB_escape_string($DelAdd2) . "'".',
                                        '."'" . DB_escape_string($DelAdd3) . "'".',
                                        '."'" . DB_escape_string($DelAdd4) . "'".',
                                        '."'" . DB_escape_string($DelAdd5) . "'".',
                                        '."'" . DB_escape_string($DelAdd6) . "'".',
                                        '."'" . DB_escape_string($telefonoDestino) . "'".',
                                        '."'" . DB_escape_string($Email) . "'".',
                                        0,
                                        '."'" . $defaultlocation ."'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        4,
                                        ' . $Deliverblind .',
                                        ' ."'" . $_POST['salesman'] ."'" .',
                                        
                                        ' . $Tagref .',
                                        0,
                                        ' ."'" . $CurrAbrev . "'" .',
                                        ' ."'" . $terminopago . "'" .',
                                       
                                        ' ."'" . $_SESSION['UserID'] . "'" .',
                                        ' ."'" . $_POST['noproveedorf']. "'".'	
                                      )';
$ErrMsg = _('La orden no se puede agregar, por que');
$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
if(DEBUG) {
	echo "<pre>$HeaderSQL</pre><br/>";
}
$debtornoOrigen="";
for ($z=1;$z<=$totalcuenta;$z++){
    $iddoctochange=$_POST['doctoid'.$z];
    $change=$_POST['docto'.$z];
    if ($change==true){
    				//extraer debtorno
    				if ($debtornoOrigen==""){
    					$sql = "Select debtorno FROM debtortrans
    							WHERE id = ".$iddoctochange;
    					$rs = DB_query($sql,$db);
    					$reg = DB_fetch_array($rs);
    					$debtornoOrigen = $reg['debtorno'];
    				}
    	
                    //extrae descuento de inventario
                    // conocer si el servicio o mano de obra si lo es no inserta el registro en el pedido
                    $DetalleSQL = "
                    INSERT INTO salesorderdetails (orderlineno,orderno,stkcode,unitprice,
                                                    quantity,discountpercent,discountpercent1,
                                                    discountpercent2,narrative,fromstkloc,
                                                    showdescrip,warranty)
                    SELECT stockmoves.stkmoveno,".$OrderNo.",stockid,price,qty*-1,stockmoves.discountpercent,discountpercent1,
                            discountpercent2,narrative,loccode,showdescription,warranty                
                    FROM debtortrans
                                    inner join stockmoves on stockmoves.type=debtortrans.type
                                        and stockmoves.transno=debtortrans.transno and abs(qty*price)>0
                                 WHERE debtortrans.id = " . $iddoctochange ;
                    
                    $ErrMsg = _('La orden no se puede agregar, por que');
                    $Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);
                    if(DEBUG) {
                    	echo "<pre>$DetalleSQL</pre><br/>";
                    }                    
    }
}

//buscar datos del debtornoOrigen
$SQL = "SELECT debtorsmaster.name,debtorsmaster.address1,debtorsmaster.coments,custbranch.taxid AS rfc, currencies.currency, paymentterms.terms,
		debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription,custbranch.phoneno AS tel ,
                debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,debtorsmaster.address6,
                custbranch.email,custbranch.defaultlocation,custbranch.salesman,custbranch.deliverblind,custbranch.defaultshipvia
	FROM debtorsmaster,
	     paymentterms,
	     holdreasons,
	     currencies,
	     custbranch
	WHERE
	     debtorsmaster.paymentterms = paymentterms.termsindicator
	     AND debtorsmaster.currcode = currencies.currabrev
	     AND debtorsmaster.debtorno = custbranch.debtorno
	     AND debtorsmaster.holdreason = holdreasons.reasoncode
	     AND debtorsmaster.debtorno = '" . $debtornoOrigen. "'";
$ErrMsg =_('The customer details could not be retrieved by the SQL because');
$CustomerResultOrigen = DB_query($SQL,$db,$ErrMsg);
if(DEBUG) {
	echo "<pre>$SQL</pre><br/>";
}

$CustomerRecordOrigen = DB_fetch_array($CustomerResultOrigen);
//round($CustomerRecord['overdue2'],2);
$telefonoDestino=$CustomerRecordOrigen['tel'];
$rfcDestino=$CustomerRecordOrigen['rfc'];
$DeliverTo=$CustomerRecordOrigen['name'];
$DelAdd1=$CustomerRecordOrigen['address1'];
$DelAdd2=$CustomerRecordOrigen['address2'];
$DelAdd3=$CustomerRecordOrigen['address3'];
$DelAdd4=$CustomerRecordOrigen['address4'];
$DelAdd5=$CustomerRecordOrigen['address5'];
$DelAdd6=$CustomerRecordOrigen['address6'];
$Email=$CustomerRecordDestino['email'];
$Deliverblind=$CustomerRecordOrigen['deliverblind'];
$Defaultshipvia=$CustomerRecordOrigen['defaultshipvia'];


$OrderNoNC = GetNextTransNo(95, $db);
// inserta la orden de la nota de credito
$HeaderSQLNC = 'INSERT INTO notesorders ( orderno,
                                        debtorno,
                                        branchcode,
                                        comments,
                                        orddate,
                                        ordertype,
                                        shipvia,
                                        deliverto,
                                        deladd1,
                                        deladd2,
                                        deladd3,
                                        deladd4,
                                        deladd5,
                                        deladd6,
                                        contactphone,
                                        contactemail,
                                        freightcost,
                                        fromstkloc,
                                        deliverydate,
                                        quotedate,
                                        confirmeddate,
                                        quotation,
                                        deliverblind,
                                        salesman,
                                        
                                        tagref,
                                        taxtotal,
                                        currcode,
                                        paytermsindicator,
                                        UserRegister
                                       )
                                VALUES (
                                        '. $OrderNoNC . ',
                                        '."'" . $debtornoOrigen. "'".',
                                        '."'" . $debtornoOrigen. "'".',
                                        
                                        '."'". DB_escape_string("Factura de remision") ."'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'PL'".',
                                        1,
                                        '."'". DB_escape_string($DeliverTo) . "'".',
                                        '."'" . DB_escape_string($DelAdd1) . "'".',
                                        '."'" . DB_escape_string($DelAdd2) . "'".',
                                        '."'" . DB_escape_string($DelAdd3) . "'".',
                                        '."'" . DB_escape_string($DelAdd4) . "'".',
                                        '."'" . DB_escape_string($DelAdd5) . "'".',
                                        '."'" . DB_escape_string($DelAdd6) . "'".',
                                        '."'" . DB_escape_string($telefonoDestino) . "'".',
                                        '."'" . DB_escape_string($Email) . "'".',
                                        0,
                                        '."'" . $defaultlocation ."'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        5,
                                        ' . $Deliverblind .',
                                        ' ."'" . $_POST['salesman'] ."'" .',
                                        
                                        ' . $Tagref .',
                                        0,
                                        ' ."'" . $CurrAbrev . "'" .',
                                        ' ."'" . $terminopago . "'" .',
                                       
                                        ' ."'" . $_SESSION['UserID'] . "'" .'
                                      )';
$ErrMsg = _('La orden no se puede agregar, por que');
$Result = DB_query($HeaderSQLNC,$db,$ErrMsg,$DbgMsg,true);
if(DEBUG) {
	echo "<pre>$HeaderSQLNC</pre><br/>";
}

//registrar la nota de credito
$DetalleSQL = "
    INSERT INTO notesorderdetails (orderlineno,orderno,stkcode,unitprice,
                                    quantity,discountpercent,discountpercent1,
                                    discountpercent2,narrative,fromstkloc,
                                    warranty)
    SELECT orderlineno,".$OrderNoNC.",stkcode,unitprice,
                                    quantity,discountpercent,discountpercent1,
                                    discountpercent2,narrative,fromstkloc,
                                    warranty              
    FROM salesorderdetails
    WHERE orderno = " . $OrderNo ;
$ErrMsg = _('La orden no se puede agregar, por que');
$Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);
if(DEBUG) {
	echo "<pre>$DetalleSQL</pre><br/>";
}

$fechaemision=$_POST['fechafactura'];
$separae = explode('/',$fechaemision);
$diae=$separae[0];;
$mese = $separae[1];
$anioe = $separae[2];
$horax = date('H:i:s');
$horax = strtotime($horax);
$hora=date(H)-1;
$minuto=date('i');
$segundo=date('s');
$fechainic=mktime($hora,$minuto,$segundo,rtrim($mese),rtrim($diae),rtrim($anioe));
$fechaemision=date("Y-m-d H:i:s",$fechainic);
// genera factura de venta
$Facturaventa=RegistrarFacturaPedido($OrderNo,66,$db);
$separa = explode('|',$Facturaventa);
$InvoiceNo = $separa[0];
$tipodefacturacion = $separa[1];
$qohsql = "SELECT  id
                FROM debtortrans
                WHERE   type = '" . $tipodefacturacion . "'
                AND transno=".$InvoiceNo;
$qohresult =  DB_query($qohsql,$db);
$qohrow = DB_fetch_row($qohresult);
$IDtransfactura=$qohrow[0];
// genera nota de credito
$NotaCredito=RegistrarNCredito($OrderNoNC,65,$db);
$separa = explode('|',$NotaCredito);
$NotaNo = $separa[0];
$tiponc = $separa[1];
$qohsql = "SELECT  id
                FROM debtortrans
                WHERE   type = '" . $tiponc . "'
                AND transno=".$NotaNo;
$qohresult =  DB_query($qohsql,$db);
if(DEBUG) {
	echo "<pre>$qohsql</pre><br/>";
}
$qohrow = DB_fetch_row($qohresult);
$IDtransCorrecion=$qohrow[0];
$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,l.legalname,l.keysend
        FROM legalbusinessunit l, tags t
        WHERE l.legalid=t.legalid AND tagref='".$Tagref."'";
$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
if(DEBUG) {
	echo "<pre>$SQL</pre><br/>";
}
$tipofacturacionxtag=0;
if (DB_num_rows($Result)==1) {
     $myrowtags = DB_fetch_array($Result);
     $rfc=trim($myrowtags['taxid']);
     $keyfact=$myrowtags['keysend'];
     $nombre=$myrowtags['tagname'];
     $area=$myrowtags['areacode'];
     $legaid=$myrowtags['legalid'];
     $tipofacturacionxtag=$myrowtags['typeinvoice'];
     $legalname=$myrowtags['legalname'];
}

//****//
$InvoiceNoTAGNC = DocumentNext(65, $Tagref,$area,$legaid, $db);
if(DEBUG) {
	echo "InvoiceNoTAGNC: $InvoiceNoTAGNC<br/>";
}

$separa = explode('|',$InvoiceNoTAGNC);
$serieNC = $separa[1];
$folioNC = $separa[0];

//Actualizar el documento para folio
$SQL="UPDATE debtortrans
      SET folio='" . $serieNC.'|'.$folioNC . "'
      WHERE id=".$IDtransCorrecion;
$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
if(DEBUG) {
	echo "<pre>$SQL</pre><br/>";
}
if($tiponc==14 or $tiponc==65 ){
    $liga="PDFNoteCreditDirectRemision.php";
}else{
    $liga="PDFNoteCreditDirect.php";
}

$PrintDispatchNote = $rootpath . '/' . $liga  . '?' . SID . 'PrintPDF=Yes&OrderNo='.$OrderNoNC.'&Tagref='.$Tagref.'&TransNo='.$NotaNo;
$ligaNotaCR='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'.$liga.'?OrderNo='.$OrderNoNC.'&TransNo=' . $NotaNo .'&Type='.$tiponc.'&Tagref='.$Tagref.'">'. _('Imprimir Nota Credito') . ' (' . _('Laser') . ')' .'</a>';


$SQL = "INSERT INTO custallocns(
                    amt,
                    datealloc,
                    transid_allocfrom,
                    transid_allocto
              )VALUES (
                    '0',
                    '" . $fechaemision . "',
                    " . $IDtransCorrecion . ",
                    " . $IDtransfactura . "
              )";
$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro del pagare para el cliente no se realizo');
$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
//$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
//$IDcustallocns = DB_Last_Insert_ID($db,'custallocns','id');
//if(DEBUG) {
//	echo "<pre>$SQL</pre><br/>";
//}

?>