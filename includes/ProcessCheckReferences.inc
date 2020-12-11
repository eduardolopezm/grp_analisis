<?php
$SQL = "SELECT debtorsmaster.name,debtorsmaster.address1,debtorsmaster.coments,custbranch.taxid AS rfc, currencies.currency, paymentterms.terms,
		debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription,custbranch.phoneno AS tel ,
                debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,debtorsmaster.address6,
                custbranch.email,custbranch.defaultlocation,custbranch.salesman,custbranch.deliverblind,custbranch.defaultshipvia,
				custbranch.paymentname, custbranch.nocuenta
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
	     AND debtorsmaster.debtorno = '" . $_SESSION['CustomerIDestinoRemision'] . "'";
$ErrMsg =_('The customer details could not be retrieved by the SQL because');
$CustomerResultDestino = DB_query($SQL,$db,$ErrMsg);
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
$paymentname = $CustomerRecordDestino['paymentname'];
$nocuenta = $CustomerRecordDestino['nocuenta'];
$terminopago=$CustomerRecordDestino['terms'];

/*
$_POST['Salesman']=$CustomerRecordDestino['salesman'];
$sql="SELECT termsindicator FROM paymentterms WHERE daysbeforedue='1' and numberOfPayments='0'";
$result =DB_query($sql,$db);
$myrow= DB_fetch_array($result);
$terminopago=$myrow['termsindicator'];
*/
$CurrAbrev='MXN';
$CurrencyRate=1;
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
										serie,
										paymentname,
										nocuenta
                                       )
                                VALUES (
                                        '. $OrderNo . ',
                                        '."'" . $_SESSION['CustomerIDestinoRemision']. "'".',
                                        '."'" . $_SESSION['BranchcodeDestinoRemision'] . "'".',
                                        
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
                                        ' ."'" . $_POST['Salesman'] ."'" .',
                                        
                                        ' . $Tagref .',
                                        0,
                                        ' ."'" . $CurrAbrev . "'" .',
                                        ' ."'" . $terminopago . "'" .',
                                       
                                        ' ."'" . $_SESSION['UserID'] . "'" .',
                                        ' ."'" . $_POST['noproveedorf']. "'".',
                                        ' ."'" . $paymentname. "'".',
                                        ' ."'" . $nocuenta. "'".'						
                                      )';
$ErrMsg = _('La orden no se puede agregar, por que');
$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);

$Narrative='Facturas de remisiones';
$Comments=$Narrative;
$Quantity =1;
$Price=0;
$StockID='facturaremision';
$StartOf_LineItemsSQL = 'INSERT INTO salesorderdetails (
                                orderlineno,
                                orderno,
                                stkcode,
                                unitprice,
                                quantity,
                                narrative,
                                poline,
                                itemdue,
                                fromstkloc,
                                warranty
                                )
                    VALUES (';
$DbgMsg = _('El SQL que fallo es:');
$LineItemsSQL = $StartOf_LineItemsSQL .
                            0 . ',
                            ' . $OrderNo . ',
                            '."'" . $StockID . "'".',
                            '. $Price . ',
                            ' . $Quantity . ',
                            '."'" . trim(DB_escape_string(htmlspecialchars_decode($Narrative,ENT_NOQUOTES))) . "'".',
                            '."'0'".',
                            '."'" . $QuotDate . "'".',
                            '."'" . $defaultlocation . "'".',
                            0
                    )';
$ErrMsg = _('No se puede agregar el producto a la orden por que');
$Result = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);

// inserta movimiento de remisiones
$SQL = "INSERT INTO stockmoves (
                                stockid,
                                type,
                                transno,
                                loccode,
                                trandate,
                                debtorno,
                                branchcode,
                                prd,
                                reference,
                                qty,
                                standardcost,
                                show_on_inv_crds,
                                newqoh,
                                tagref,
                               
                                avgcost
                                ) VALUES (
                                '" .$StockID. "',
                                 " . $tipodefacturacion . ",
                                 " . $InvoiceNo . ",
                                 '" . $defaultlocation . "',
                                 '" . $DefaultDispatchDate . "',
                                 '" . $_SESSION['CustomerIDestinoRemision']  . "',
                                 '" . $_SESSION['BranchcodeDestinoRemision']  . "',
                                 " . $PeriodNo . ",
                                 '"  . $OrderNo . "',
                                 " . -$Quantity . ",
                                0,
                                 1,
                                0,
                                 " .$Tagref .",
                               
                                0
                                )";
$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
$DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

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

$SQL = "INSERT INTO debtortrans (
                    transno,
                    type,
                    debtorno,
                    branchcode,
                    trandate,
                    prd,
                    reference,
                    tpe,
                    order_,
                    ovamount,
                    ovgst,
                    ovfreight,
                    rate,
                    invtext,
                    shipvia,
                    consignment,
                    currcode,
                    tagref,
                    folio,
                    origtrandate,
					nopedidof,
					noentradaf,
					observf,
					noproveedorf,
					paymentname,
					nocuenta
                    )
            VALUES (
                    ". $InvoiceNo . ",
                    " . $tipodefacturacion . ",
                    '" . $_SESSION['CustomerIDestinoRemision'] . "',
                    '" . $_SESSION['BranchcodeDestinoRemision'] . "',
                    '" . $DefaultDispatchDate . "',
                    " . $PeriodNo . ",
                    '',
                    'PL',
                    " . $OrderNo . ",
                    0,
                    0,
                    0,
                    " . $CurrencyRate. ",
                    '" . str_replace('CANCELADA','',DB_escape_string(strtoupper($Comments))) . "',
                    " . $Defaultshipvia . ",
                    '0',
                    '" . $CurrAbrev . "',
                    " . $Tagref .",
                    '" . $folio . "',
                    '" . $fechaemision . "',
                    '".$_POST['nopedidof']."',
                    '".$_POST['noentradaf']."',
                    '".$_POST['observf']."',
                    '".$_POST['noproveedorf']."',
                    '" . $paymentname. "',
                    '" . $nocuenta. "'											
        )";
$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
$IDtransfactura = DB_Last_Insert_ID($db,'debtortrans','id');

$transno = GetNextTransNo(112,$db);
$SQL = "INSERT INTO debtortrans (
                    transno,
                    type,
                    debtorno,
                    branchcode,
                    trandate,
                    prd,
                    reference,
                    tpe,
                    order_,
                    ovamount,
                    ovgst,
                    ovfreight,
                    rate,
                    invtext,
                    shipvia,
                    consignment,
                    currcode,
                    tagref,
                    origtrandate
                    )
            VALUES (
                    ". $transno . ",
                    " . 112 . ",
                    '" . $_SESSION['CustomerIDestinoRemision'] . "',
                    '" . $_SESSION['BranchcodeDestinoRemision'] . "',
                    '" . $DefaultDispatchDate . "',
                    " . $PeriodNo . ",
                    '',
                    'PL',
                    0,
                    0,
                    0,
                    0,
                    " . $CurrencyRate. ",
                    '" . str_replace('CANCELADA','',DB_escape_string(strtoupper($Comments))) . "',
                    " . $Defaultshipvia . ",
                    '"  . 0  . "',
                    '" . $CurrAbrev . "',
                    " . $Tagref .",
                    '" . $fechaemision . "'
	)";
$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
$IDtransCorrecion = DB_Last_Insert_ID($db,'debtortrans','id');

$monto=0;
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
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
$IDcustallocns = DB_Last_Insert_ID($db,'custallocns','id');

?>