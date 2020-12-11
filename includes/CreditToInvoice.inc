<?php
/*read in all the guff from the selected invoice into the Items cart	*/
	$InvoiceHeaderSQL = "SELECT DISTINCT
					debtortrans.id as transid,
					debtortrans.debtorno,
					debtorsmaster.name,
					debtortrans.branchcode,
					debtortrans.reference,
					debtortrans.invtext,
					debtortrans.order_,
					debtortrans.trandate,
					debtortrans.tpe,
					debtortrans.shipvia,
					debtortrans.ovfreight,
					debtortrans.rate AS currency_rate,
					debtortrans.tagref,
					debtortrans.currcode,
					custbranch.defaultlocation,
					custbranch.taxgroupid,";
					//stockmoves.loccode,  LO QUITE PARA QUE NO DESPLIEGUE DOS REGISTROS DEL HEADER
	$InvoiceHeaderSQL = $InvoiceHeaderSQL . "	
					locations.taxprovinceid,
					debtortrans.tagref,
                                        debtortrans.transno,
					custbranch.brname as deliverto,
					custbranch.braddress1 as deladd1,
					custbranch.braddress2 as deladd2,
					custbranch.braddress3 as deladd3,
					custbranch.braddress4 as deladd4,
					custbranch.braddress5 as deladd5,
					custbranch.braddress6 as deladd6,
					custbranch.phoneno as contactphone,
					custbranch.email as contactemail,
					custbranch.taxid,
					debtortrans.origtrandate,
					salesorders.salesman
				FROM debtortrans INNER JOIN debtorsmaster ON
					debtortrans.debtorno = debtorsmaster.debtorno
					INNER JOIN salesorders ON salesorders.orderno=debtortrans.order_
					INNER JOIN custbranch ON 
					debtortrans.branchcode = custbranch.branchcode
					AND debtortrans.debtorno = custbranch.debtorno
					INNER JOIN currencies ON
					debtorsmaster.currcode = currencies.currabrev
					INNER JOIN stockmoves ON
					stockmoves.transno=debtortrans.transno
					INNER JOIN locations ON
					stockmoves.loccode = locations.loccode
				WHERE debtortrans.transno = " . $_GET['InvoiceNumber'] . "
					AND debtortrans.type=" . $_GET['InvoiceType'] . " 
					AND stockmoves.type=" . $_GET['InvoiceType'];
	$ErrMsg = _('No se puede generar una Nota de Credito de la factura seleccionada') . '. ' . _('Los detalles de la Factura no pueden recuperarse');
	$DbgMsg = _('El SQL que se uso para recuperar la Factura fue...');
	$GetInvHdrResult = DB_query($InvoiceHeaderSQL,$db,$ErrMsg,$DbgMsg);
	if (DB_num_rows($GetInvHdrResult)==1) {
		$myrow = DB_fetch_array($GetInvHdrResult);
/*CustomerID variable registered by header.inc */
		$_SESSION['Tagref']=$myrow['tagref'];
		$_SESSION['SalesmanCredit']=$myrow['salesman'];
		$_SESSION['OrderCredit']=$myrow['order_'];
		
		$_SESSION['CurrAbrev']=$myrow['currcode'];
		//echo 'aaaMoneda:'. $_SESSION['CurrAbrev'].'<br>';
		$_POST['CurrAbrev']=$myrow['currcode'];
		$_SESSION['Items'.$identifier]->DebtorNo = $myrow['debtorno'];
		$_SESSION['Items'.$identifier]->TransID = $myrow['transid'];
		$_SESSION['Items'.$identifier]->Branch = $myrow['branchcode'];
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
		$_SESSION['Items'.$identifier]->CustRef = $myrow['reference'];
		$_SESSION['Items'.$identifier]->Comments ="Factura ".$myrow['transno'].''.$myrow['invtext'];
		$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['tpe'];
		$sql = "SELECT 	salestypes.sales_type
			FROM salestypes
			WHERE salestypes.typeabbrev = '" . $myrow['tpe'] . "'";
		$ErrMsg = _('Los detalles del cliente seleccionado') . ': ' .  $_POST['Select'] . ' ' . _('no se pueden recuperar, por que ');
		$DbgMsg = _('El SQL utilizado para recuperar los detalles del cliente fue') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow2 = DB_fetch_row($result);
		$_SESSION['Items'.$identifier]->SalesTypeName = $myrow2[0];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->Location = $myrow['defaultlocation'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['trandate']);
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['deladd1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['deladd2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['deladd3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['deladd4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['deladd5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['deladd6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
		$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['Items'.$identifier]->FreightCost = $myrow['ovfreight'];
		$_SESSION['Items'.$identifier]->Orig_OrderDate = $myrow['origtrandate'];
		$_SESSION['Items'.$identifier]->CostumerRFC = $myrow['taxid'];

		$_SESSION['Old_FreightCost'] = $myrow['ovfreight'];
		$_SESSION['CurrencyRate'] = $myrow['currency_rate'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['Items'.$identifier]->FreightCost = $myrow['ovfreight'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['Items'.$identifier]->GetFreightTaxes();
		$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($myrow['debtorno'],$db);
		/*LO AGREGUE PARA RECIBIR LA UNIDAD DE NEGOCIO*/
		$_SESSION['Items'.$identifier]->tagref = $myrow['tagref'];
		DB_free_result($GetInvHdrResult);
/*now populate the line items array with the stock movement records for the invoice*/
		$LineItemsSQL = "SELECT stockmoves.stkmoveno,
					stockmoves.stockid,
					stockmaster.description,
					stockmaster.volume,
					stockmaster.kgs,
					stockmaster.mbflag,
					stockmaster.controlled,
					stockmaster.serialised,
					stockmaster.decimalplaces,
					stockmaster.taxcatid,
					stockmaster.units,
					stockmaster.discountcategory,
					(stockmoves.price) AS price, -
					stockmoves.qty as quantity,
					stockmoves.discountpercent,
					stockmoves.trandate,
					stockmaster.materialcost 
						+ stockmaster.labourcost 
						+ stockmaster.overheadcost AS standardcost,
					stockmoves.narrative,
					stockmoves.loccode,
					locations.locationname,
					stockmoves.discountpercent1,
					stockmoves.discountpercent2,
					stockmoves.warranty,
					if(stockmoves.refundpercentmv = 0,1,stockmoves.refundpercentmv) as refundpercentmv
				FROM stockmoves, stockmaster, locations
				WHERE stockmoves.stockid = stockmaster.stockid
				AND locations.loccode=stockmoves.loccode
				AND stockmoves.transno =" . $_GET['InvoiceNumber'] . "
				AND stockmoves.type=" . $_GET['InvoiceType'] . " 
				AND stockmoves.show_on_inv_crds=1";
		$ErrMsg = _('Esta factura no puede ser recuperada utilizando esta pantalla') . '. ' . _('Una nota de credito manual sera necesaria') . '. ' . _('Las lineas de la factura no pueden ser recuperadas');
		$Dbgmsg = _('El SQL utilizado fue');
		
		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg, $DbgMsg);
		if (db_num_rows($LineItemsResult)>0) {
			while ($myrow=db_fetch_array($LineItemsResult)) {
				if ($_SESSION['TypeCostStock']==1){
					$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$myrow['stockid'], $db);
				}else{
					$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
					$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$myrow['stockid'], $db);
				}
				
				$tipoalmacen = GetTypeLocation($myrow['loccode'],$db);
				if ($tipoalmacen==2){
					$QuerySQL="select loccode,locationname from locations where temploc=0 and tagref=".$_SESSION['Tagref'];
					//echo $QuerySQL;
					$ErrMsg = _('No fue posible extraer el tipo de almacen') ;
					$DbgMsg=_('El SQL utilizado es: ');
					$DemandResult = DB_query($QuerySQL,$db,$ErrMsg,$DbgMsg);
					$DemandRow = DB_fetch_row($DemandResult);
					$myrow['loccode'] =  $DemandRow[0];
					$myrow['locationname']=  $DemandRow[1];
				}
				$LineNumber = $_SESSION['Items'.$identifier]->LineCounter;
				$_SESSION['Items'.$identifier]->add_to_cart($myrow['stockid'],
									$myrow['quantity'],
									$myrow['description'],
									$myrow['price'],
									$myrow['discountpercent'],
									$myrow['units'],
									$myrow['volume'],
									$myrow['kgs'],
									0,
									$myrow['mbflag'],
									$myrow['trandate'],
									0,
									$myrow['discountcategory'],
									$myrow['controlled'],
									$myrow['serialised'],
									$myrow['decimalplaces'],
									$myrow['narrative'],
									'No',
									-1,
									$myrow['taxcatid'],
									'',
									'',
									'',
									$EstimatedAvgCost,
									0,
									0,
									$myrow['loccode'],
									$myrow['locationname'],
									$myrow['discountpercent1'],
									$myrow['discountpercent2'],
									$myrow['warranty'],
									$myrow['refundpercentmv']
									);
				$_SESSION['Items'.$identifier]->GetExistingTaxes($LineNumber, $myrow['stkmoveno']);
				//echo 'Moneda:'. $_SESSION['CurrAbrev'];
				if ($myrow['controlled']==1){/* Populate the SerialItems array too*/
					$SQL = 'SELECT 	serialno,
							moveqty,
							standardcost
						FROM stockserialmoves
						WHERE stockmoveno=' . $myrow['stkmoveno'] . "
							AND stockid = '" . $myrow['stockid'] . "'";
					$ErrMsg = _('This invoice can not be credited using this program') . '. ' . _('A manual credit note will need to be prepared') . '. ' . _('The line item') . ' ' . $myrow['stockid'] . ' ' . _('is controlled but the serial numbers or batch numbers could not be retrieved because');
					$DbgMsg = _('The SQL used to get the controlled item details was');
					$SerialItemsResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
					while ($SerialItemsRow = DB_fetch_array($SerialItemsResult)){
						$_SESSION['Items'.$identifier]->LineItems[$LineNumber]->SerialItems[$SerialItemsRow['serialno']] = new SerialItem($SerialItemsRow['serialno'], -$SerialItemsRow['moveqty'],$SerialItemsRow['standardcost'],0);
						$_SESSION['Items'.$identifier]->LineItems[$LineNumber]->QtyDispatched -= $SerialItemsRow['moveqty'];
					}
				} /* end if the item is a controlled item */
			} /* loop thro line items from stock movement records */
		}
	}
?>