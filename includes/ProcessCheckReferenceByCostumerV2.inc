<?php

$Result = DB_Txn_Begin($db);

if(empty($comments)) {
	$comments = 'Factura de remision';
}

include('ProcessNoteCreditByCostumer.inc');
include('RegistraFactura.inc');

$SQL = "SELECT debtorsmaster.name,debtorsmaster.address1,debtorsmaster.coments,custbranch.taxid AS rfc, currencies.currency, paymentterms.termsindicator AS terms,
		debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription,custbranch.phoneno AS tel ,
                debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,debtorsmaster.address6,
                custbranch.email,custbranch.defaultlocation,custbranch.salesman,custbranch.deliverblind,custbranch.defaultshipvia,
				custbranch.paymentname,custbranch.nocuenta
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
	//echo "<pre>$SQL</pre><br/>";
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
$paymentname = $CustomerRecordDestino['paymentname'];
$nocuenta=$CustomerRecordDestino['nocuenta'];
$terminopago=$CustomerRecordDestino['terms'];
//$_POST['Salesman']=$CustomerRecordDestino['salesman']; se comento porque el valor viene en el arreglo del POST porque se agrego un combo para seleccionar al vendedor

/*
$sql="SELECT termsindicator FROM paymentterms WHERE daysbeforedue='1' and numberOfPayments='0'";
$result =DB_query($sql,$db);
$myrow= DB_fetch_array($result);
$terminopago=$myrow['termsindicator'];
*/

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
////
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
										paymentname,
										nocuenta
                                       )
                                VALUES (
                                        '. $OrderNo . ',
                                        '."'" . $_SESSION['CustomerIDestinoRemisionInvoice']. "'".',
                                        '."'" . $_SESSION['BranchcodeDestinoRemisionInvoice'] . "'".',

                                        '."'". DB_escape_string($comments) ."'".',
                                        '."'" . Date("Y-m-d H:i") . "'".',
                                        '."'{$_SESSION['DefaultPriceList']}'".',
                                        '."'".$_POST['shipvia']."'".',
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
                                         ' ."'" . $paymentname . "'" .',
                                          ' ."'" . $nocuenta . "'" .'

                                      )';
$ErrMsg = _('La orden no se puede agregar, por que');
$Result = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg,true);
if(DEBUG) {
	//echo "<pre>$HeaderSQL</pre><br/>";
}

$debtornoOrigen="";

for ($z=1; $z<=$totalcuenta; $z++)
{
    $iddoctochange= $_POST['doctoid'.$z];
    $change= $_POST['docto'.$z];

    if ($change== true)
    {
    				//extraer debtorno
    				//if ($debtornoOrigen==""){
    					$sql = "Select debtorno FROM debtortrans
    							WHERE id = ".$iddoctochange;

    					$rs = DB_query($sql,$db);
    					$reg = DB_fetch_array($rs);
    					$debtornoOrigen = $reg['debtorno'];
    				//}

                    //extrae descuento de inventario
                    // conocer si el servicio o mano de obra si lo es no inserta el registro en el pedido
                    $DetalleSQL = "
                    INSERT INTO salesorderdetails (orderlineno,orderno,stkcode,unitprice,
                                                    quantity,discountpercent,discountpercent1,
                                                    discountpercent2,narrative,fromstkloc,
                                                    showdescrip,warranty, userlost) VALUES  ";

                    $arrdata = $arrdatatoinv[$iddoctochange];

                    foreach($arrdata as $arrvalues){
                    	$DetalleSQLP="('".$arrvalues['stkmoveno']."','$OrderNo','".$arrvalues['stkid']."',
                    					'".$arrvalues['price']."','".$arrvalues['qty']."','".$arrvalues['disc']."',
                    					'".$arrvalues['disc1']."','".$arrvalues['disc2']."','".DB_escape_string($arrvalues['narrative'])."',
                    					'".$arrvalues['loccode']."','".$arrvalues['showdesc']."','".$arrvalues['warranty']."','".$debtornoOrigen."')";
                    	//echo '<pre><br><br>sql:'.$DetalleSQL;

                    	$DetalleSQLexe=$DetalleSQL.' '.$DetalleSQLP;
                    	$ErrMsg = _('La orden no se puede agregar, por que');
                    	$Result = DB_query($DetalleSQLexe,$db,$ErrMsg,$DbgMsg,true);
                    }

                    //$DetalleSQL = substr($DetalleSQL,0,strlen($DetalleSQL)-1);
                    //echo '<pre><br><br>sql:'.$DetalleSQL;

                   // $ErrMsg = _('La orden no se puede agregar, por que');
                    //$Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);
                    foreach($arrdata as $arrvalues){
                    	$lineaorden = $arrvalues['stkmoveno'];
/************************************** Inserta Categoria Extra *********************/
                    	//echo 'moveno'.$lineaorden;
                    	//echo 'variable'.$_POST['TotalPropDefault'.$lineaorden];
                    	$totalcampos=$_POST['TotalPropDefault'.$lineaorden];
                    	//echo 'totalcampos'.$totalcampos;
                    	//exit;
                    	if ($totalcampos>0){
                    		//echo 'entro';
                    		for ($i=0;$i<$totalcampos;$i++){
                    			$stockid=$_POST['PropDefaultval'.$lineaorden.'_'.$i];
                    			//echo $stockid;
                    			//exit;
                    			$valorstock=$_POST['PropDefault'.$lineaorden.'_'.$i];
                    			$tipoobj=$_POST['tipoobjeto'.$lineaorden.'_'.$i];
                    			$consulta=$_POST['consulta'.$lineaorden.'_'.$i];
                    			$campo=$_POST['campo'.$lineaorden.'_'.$i];
                    			$classe = $_POST['class'.$lineaorden.'_'.$i];
                    			if ($tipoobj=='checkbox'){
                    				//if ($valorstock==true){
                    				if ($valorstock==true or $valorstock=='on'){
                    					$valorstock="SI";
                    				}else{
                    					$valorstock="NO";
                    				}
                    			}
                    			$valorbase=$valorstock;
                    			//echo $stockid;
                    			if($tipoobj==5){
                    				if (strlen($consulta)>5){
                    					$sqlcampos=$consulta.' and '. $campo.' = "'.$valorstock.'"';
                    					$DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                    					$ErrMsg = _('No se pudo obtener el valor, por que');
                    					$Result = DB_query($sqlcampos,$db,$ErrMsg,$DbgMsg,true);
                    					$Rowcampos = DB_fetch_array($Result);
                    					$valorbase=$Rowcampos[1];
                    				}


                    				if ($valorstock=="0" and strlen($consulta)>5  and $tipoobjeto == 5){

                    					$sqlcampos = $consulta.' and salesmanname like "%sin trabaja%" and tags.tagref= '.$_SESSION['Tagref'].' limit 1';
                    					$DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
                    					$ErrMsg = _('No se pudo obtener el valor, por que');


                    					$Result = DB_query($sqlcampos,$db,$ErrMsg,$DbgMsg,true);
                    					$Rowcampos = DB_fetch_array($Result);
                    					$valorbase=$Rowcampos[1];
                    					$valorstock=$Rowcampos[0];
                    				}
                    			}
                    			//echo 'valor stock'.$valorstock.'<br>';
                    			if ($valorstock!="0" and $valorstock != ""){
                    				$existeprop=ValidaSalesProperty($stockid,$OrderNo,$lineaorden,trim($valorstock),66,$db);
                    				if ($existeprop==0){
                    					$SQL = "INSERT INTO salesstockproperties (
															stkcatpropid,
															orderno,
															orderlineno,
															valor,
															InvoiceValue,
															typedocument

														)
														VALUES
														(
															". $stockid . ",
															" . $OrderNo . ",
															" . $lineaorden . ",
															'" . trim($valorstock) . "',
															'" . trim($valorbase) . "',
															66
														)";
                    					$ErrMsg="Error al insertar los valores extra";
                    					//echo '<pre>'.$SQL;
                    					$Result = DB_query($SQL,$db,$ErrMsg);
                    				}
                    			}elseif(($valorstock == "" or $valorstock == "0") and $_SESSION['AutomaticWorkOrders'] == 1 and $classe == "date"){
                    				prnMsg('Favor de agregar fecha de publicacion ','error');

                    			}

                    		}
                    	}
/************************************** Inserta Categoria Extra ********************/
                    }
                    if(DEBUG) {
                    	//echo "<pre>$DetalleSQL</pre><br/>";
                    }


    }
}

for($contador=1;$contador<=$_POST['Contant'];$contador++){

	if(isset($_POST['montoant'.$contador]) and $_POST['montoant'.$contador] > 0){

		$typeand = $_POST['typeant'.$contador];
		$transnoand = $_POST['transnoant'.$contador];
		$montoaplant = $_POST['montoant'.$contador];
		$sql = "SELECT *
                    				FROM stockmoves
                    				WHERE type = '".$typeand."'
                    					AND transno = '".$transnoand."'";
		$rs = DB_query($sql,$db);

		$row = DB_fetch_array($rs);
		$stkmovenoant = $row['stkmoveno'];
		$stockidant = $row['stockid'];
		$discant = $row['discountpercent'];
		$disc1ant = $row['discountpercent1'];
		$disc2ant = $row['discountpercent2'];
		$loccodeant = $row['loccode'];
		$narrativeant = $row['narrative'];
		$warrantyant = $row['warranty'];
		$showant = $row['showdescription'];
		//$priceant = $row['price'];//
		//$quantityant = $row['qty'];
		//$cantidadaplicada = ($montoaplant*$quantityant)/$priceant;*/
		$montoanticipo = $montoanticipo + $_POST['montoant'.$contador];
	}
}

if($montoanticipo <> 0){
	$DetalleSQL = "INSERT INTO salesorderdetails (orderno,stkcode,unitprice,
                                                    quantity,discountpercent,discountpercent1,
                                                    discountpercent2,narrative,fromstkloc,
                                                    showdescrip,warranty)
                    						VALUES ('".$OrderNo."', '".$stockidant."','".$montoanticipo."',
                    								-1,'".$discant."','".$disc1ant."',
                    								'".$disc2ant."','".$narrativeant."','".$loccodeant."',
                    								'".$showant."','".$warrantyant."')";
	$ErrMsg = _('La orden no se puede agregar, por que');

	$Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);
}//

for($contador=1;$contador<=$_POST['Contant'];$contador++){

	if(isset($_POST['montoant'.$contador]) and $_POST['montoant'.$contador] > 0){

		$typeand = $_POST['typeant'.$contador];
		$transnoand = $_POST['transnoant'.$contador];
		$montoaplant = $_POST['montoant'.$contador];
		$sql = "SELECT *
				FROM stockmoves
				WHERE type = '".$typeand."'
				AND transno = '".$transnoand."'";
		$rs = DB_query($sql,$db);
		$row = DB_fetch_array($rs);
		$stkmovenoant = $row['stkmoveno'];
		$stockidant = $row['stockid'];
		$priceant = $row['price'];
		$quantityant = $row['qty'];
		$cantidadaplicada = ($montoaplant*$quantityant)/$priceant;

		$DetalleSQL = "INSERT INTO stockmovesremisionanticipo (	orderno,
																stkmoveno,
																stockid,
																quantity)
						VALUES('".$OrderNo."',
								'".$stkmovenoant."',
								'".$stockid."',
								'".$cantidadaplicada."')";
		$ErrMsg = _('La orden no se puede agregar, por que');
		$Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);
	}
}

$notas_credito= array();

foreach($agrupacliente as $cliente)
{
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
		     AND debtorsmaster.debtorno = '" . $cliente. "'";

	$ErrMsg =_('The customer details could not be retrieved by the SQL because');
	$CustomerResultOrigen = DB_query($SQL,$db,$ErrMsg);
	if(DEBUG) {
		//echo "<pre>$SQL</pre><br/>";
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
	$HeaderSQLNC = 'INSERT INTO notesorders (orderno,
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
	                                        '."'" . $cliente. "'".',
	                                        '."'" . $cliente. "'".',

	                                        '."'". DB_escape_string($comments) ."'".',
	                                        '."'" . Date("Y-m-d H:i") . "'".',
	                                        '."'{$_SESSION['DefaultPriceList']}'".',
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
		//echo "<pre>$HeaderSQLNC</pre><br/>";
	}

	//registrar la nota de credito//
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
	    INNER JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
	    WHERE stockmaster.flagadvance <> 1
	    	AND orderno = " . $OrderNo . " and userlost= '". $cliente ."'";

	$ErrMsg = _('La orden no se puede agregar, por que');
	if($_SESSION['UserID'] == "admin"){
		//echo '<pre>'.$DetalleSQL;
	}
	$Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);


	//Agregar a tabla de fechas de pedidos
	$qry = "INSERT INTO notesdate(orderno,fecha_solicitud,usersolicitud)
			VALUES(".$OrderNoNC.",now(),'".$_SESSION['UserID']."')";

	$Result = DB_query($qry,$db);

	// Actualizo pedido a status inicial//
	$sql="select * from notesfielddate where statusid=2";
	$resultstatus = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	while ($RowOrders = DB_fetch_array($resultstatus)){
		$sql="UPDATE salesdate";
		if($RowOrders[2]==1){
			$sql=$sql." SET ".$RowOrders[0]."=now() ";
		}else{
			$sql=$sql." SET ".$RowOrders[0]."='".$_SESSION['UserID']."'";
		}
		$sql=$sql." WHERE orderno= ".  $OrderNoNC;
		if($RowOrders[3]==1){
			$sql=$sql." AND ".  $RowOrders[0] ." is null";
		}
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	}

	if(DEBUG) {
		//echo "<pre>$DetalleSQL</pre><br/>";
	}

	$notas_credito[$OrderNoNC]= $OrderNoNC;
	//$notas_credito[$OrderNoNC]= array("NumNota" => $OrderNoNC, )

} // fin de ciclo para agregar notas de credito por cliente

//echo $qry.'<br>';

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

for($contador=1;$contador<=$_POST['Contant'];$contador++){

	if(isset($_POST['montoant'.$contador]) and $_POST['montoant'.$contador] > 0){

		$typeand = $_POST['typeant'.$contador];
		$transnoand = $_POST['transnoant'.$contador];
		$montoaplant = $_POST['montoant'.$contador];
		$sql = "SELECT *
                    				FROM stockmoves
                    				WHERE type = '".$typeand."'
                    					AND transno = '".$transnoand."'";
		$rs = DB_query($sql,$db);

		$row = DB_fetch_array($rs);
		$stkmovenoant = $row['stkmoveno'];
		$priceant = $row['price'];
		$quantityant = $row['qty'];
		$cantidadaplicada = ($montoaplant*$quantityant)/$priceant;
		$DetalleSQL="UPDATE stockmoves
						  SET qtyinvoiced=qtyinvoiced+".($cantidadaplicada)."
					  	  WHERE stkmoveno='".$stkmovenoant."'";

		$ErrMsg = _('La orden no se puede agregar, por que');

		$Result = DB_query($DetalleSQL,$db,$ErrMsg,$DbgMsg,true);
	}

}

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

foreach ($notas_credito as $notacredito)
{
	// genera nota de credito
	if ($_POST['flagprovision']==0){
		$NotaCredito=RegistrarNCredito($notacredito,65,$db);
	}else{
		$NotaCredito=RegistrarNCreditoProvision($notacredito,65,$db);
	}

	$separa = explode('|', $NotaCredito);
	$NotaNo = $separa[0];
	$tiponc = $separa[1];

	$qohsql = "SELECT  id, debtorno
	                FROM debtortrans
	                WHERE   type = '" . $tiponc . "'
	                AND transno=".$NotaNo;

	$qohresult =  DB_query($qohsql,$db);
	if(DEBUG) {
		echo "<pre>$qohsql</pre><br/>";
	}

	$qohrow = DB_fetch_row($qohresult);
	$IDtransCorrecion= $qohrow[0];
	$IDCliente= $qohrow[1];

	$arr_idnotas[$IDCliente]= array("idtrans" => $IDtransCorrecion,
											"numnota" => $notacredito,
											"numchange" => 0,
											"subtotal" => 0,
											"total" => 0,
											"iva" => 0);

	$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice,l.legalname,l.keysend
	        FROM legalbusinessunit l, tags t
	        WHERE l.legalid=t.legalid AND tagref='".$Tagref."'";
	$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if(DEBUG) {
		//echo "<pre>$SQL</pre><br/>";
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

	$PrintDispatchNote = $rootpath . '/' . $liga  . '?' . SID . 'PrintPDF=Yes&OrderNo='.$notacredito.'&Tagref='.$Tagref.'&TransNo='.$NotaNo;
	$ligaNotaCR='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'.$liga.'?OrderNo='.$notacredito.'&TransNo=' . $NotaNo .'&Type='.$tiponc.'&Tagref='.$Tagref.'">'. _('Imprimir Nota Credito') . ' (' . _('Laser') . ')' .'</a>';


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
}

//$IDcustallocns = DB_Last_Insert_ID($db,'custallocns','id');
//if(DEBUG) {
//	echo "<pre>$SQL</pre><br/>";
//}
$Result = DB_Txn_Commit($db);
?>
