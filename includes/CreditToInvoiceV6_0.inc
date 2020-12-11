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
					salesorders.salesman,
					salesorders.placa,
					salesorders.serie,
					salesorders.kilometraje,
					salesorders.tagref,
					salesorders.currcode,
					salesorders.quotation,
					salesorders.paytermsindicator,
					custbranch.taxid as rfc,
					salesorders.vehicleno as vehiculo,
					idprospect,
					descclientecomercial,
					descclienteop,
					discount ,
					stockmoves.loccode as fromstkloc1,
					custbranch.deliverblind
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
		
		
		$_SESSION['ItemsNotes'.$identifier]->Tagref=$myrow['tagref'];
		// referencia de nota de factura de venta
		$_SESSION['ItemsNotes'.$identifier]->InvoiceType=$_GET['InvoiceType'];
		$_SESSION['ItemsNotes'.$identifier]->InvoiceNo=$_GET['InvoiceNumber'];
		$_SESSION['ItemsNotes'.$identifier]->TransID = $myrow['transid'];
		
		$_SESSION['ItemsNotes'.$identifier]->CurrAbrev=$myrow['currcode'];
		$_SESSION['ItemsNotes'.$identifier]->SelectedVehicle=$myrow['vehiculo'];
		$_SESSION['ItemsNotes'.$identifier]->prospectid=$myrow['idprospect'];
		$_SESSION['ItemsNotes'.$identifier]->OrderNo = 0;
		$_SESSION['ItemsNotes'.$identifier]->DebtorNo = $myrow['debtorno'];
		$_SESSION['ItemsNotes'.$identifier]->Branch = $myrow['branchcode'];
		$_SESSION['ItemsNotes'.$identifier]->CustomerName = $myrow['name'];
		$_SESSION['ItemsNotes'.$identifier]->CustRef = $myrow['customerref'];
		$_SESSION['ItemsNotes'.$identifier]->Comments = stripcslashes($myrow['comments']);
		$_SESSION['ItemsNotes'.$identifier]->PuestaEnMarcha = stripcslashes($myrow['puestaenmarcha']);
		$_SESSION['ItemsNotes'.$identifier]->PaymentTerms =$myrow['paytermsindicator'];
		$_SESSION['ItemsNotes'.$identifier]->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['ItemsNotes'.$identifier]->SalesTypeName =$myrow['sales_type'];
		$_SESSION['ItemsNotes'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['ItemsNotes'.$identifier]->ShipVia = $myrow['shipvia'];
		$_SESSION['ItemsNotes'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['ItemsNotes'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['ItemsNotes'.$identifier]->DelAdd1 = $myrow['deladd1'];
		$_SESSION['ItemsNotes'.$identifier]->DelAdd2 = $myrow['deladd2'];
		$_SESSION['ItemsNotes'.$identifier]->DelAdd3 = $myrow['deladd3'];
		$_SESSION['ItemsNotes'.$identifier]->DelAdd4 = $myrow['deladd4'];
		$_SESSION['ItemsNotes'.$identifier]->DelAdd5 = $myrow['deladd5'];
		$_SESSION['ItemsNotes'.$identifier]->DelAdd6 = $myrow['deladd6'];
		$_SESSION['ItemsNotes'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['ItemsNotes'.$identifier]->Email = $myrow['contactemail'];
		$_SESSION['ItemsNotes'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['ItemsNotes'.$identifier]->LocationName = $myrow['locationname'];
		$_SESSION['ItemsNotes'.$identifier]->Quotation = $myrow['quotation'];
		$_SESSION['ItemsNotes'.$identifier]->FreightCost = $myrow['freightcost'];
		$_SESSION['ItemsNotes'.$identifier]->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['ItemsNotes'.$identifier]->Quotation =$myrow['quotation'];
		$_SESSION['ItemsNotes'.$identifier]->Salesman=$myrow['salesman'];
		$_SESSION['ItemsNotes'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['ItemsNotes'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['ItemsNotes'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['ItemsNotes'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['ItemsNotes'.$identifier]->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['ItemsNotes'.$identifier]->CostumerRFC=$myrow['rfc'];
		$_SESSION['ItemsNotes'.$identifier]->placa=$myrow['placa'];
		$_SESSION['ItemsNotes'.$identifier]->serie=$myrow['serie'];
		$_SESSION['ItemsNotes'.$identifier]->kilometraje=$myrow['kilometraje'];
		//Descuentos a nivel cliente
		$_SESSION['ItemsNotes'.$identifier]->deccomercial = $myrow['descclientecomercial'];
		$_SESSION['ItemsNotes'.$identifier]->descCliente2 = $myrow['descclienteop'];
		$_SESSION['ItemsNotes'.$identifier]->descCliente1 = $myrow['discount'];
		
		if ($_SESSION['ItemsNotes'.$identifier]->descCliente1=="")
			$_SESSION['ItemsNotes'.$identifier]->descCliente1 = 0;
			
		if ($_SESSION['ItemsNotes'.$identifier]->deccomercial=="")
			$_SESSION['ItemsNotes'.$identifier]->deccomercial = 0;
			
		if ($_SESSION['ItemsNotes'.$identifier]->descCliente2=="")
			$_SESSION['ItemsNotes'.$identifier]->descCliente2 = 0;
			
		$_SESSION['ItemsNotes'.$identifier]->deccomercial = $_SESSION['ItemsNotes'.$identifier]->deccomercial/100;
		
		$_SESSION['SalesmanCredit']=$myrow['salesman'];
		$_SESSION['ItemsNotes'.$identifier]->OrderCredit=$myrow['order_'];
		$_SESSION['ItemsNotes'.$identifier]->Comments ="Factura ".$myrow['transno'].''.$myrow['invtext'];
		$_SESSION['ItemsNotes'.$identifier]->DefaultSalesType =$myrow['tpe'];
		$_SESSION['ItemsNotes'.$identifier]->FreightCost=0;
		

		$sql = "SELECT 	salestypes.sales_type
			FROM salestypes
			WHERE salestypes.typeabbrev = '" . $myrow['tpe'] . "'";
		$ErrMsg = _('Los detalles del cliente seleccionado') . ': ' .  $_POST['Select'] . ' ' . _('no se pueden recuperar, por que ');
		$DbgMsg = _('El SQL utilizado para recuperar los detalles del cliente fue') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		$myrow2 = DB_fetch_row($result);
		$_SESSION['ItemsNotes'.$identifier]->SalesTypeName = $myrow2[0];
		$_SESSION['ItemsNotes'.$identifier]->GetFreightTaxes();
		$_SESSION['ItemsNotes'.$identifier]->CreditAvailable = GetCreditAvailable($myrow['debtorno'],$db);
		/*LO AGREGUE PARA RECIBIR LA UNIDAD DE NEGOCIO*/
		
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
					stockmaster.discountcategory,
					locations.locationname as locationname1,
				    stockcategory.redinvoice,
					stockcategory.disabledprice,
					stockmaster.materialcost 
						+ stockmaster.labourcost 
						+ stockmaster.overheadcost AS standardcost,
					stockmoves.narrative,
					stockmoves.loccode as fromstkloc1,
					stockmoves.discountpercent1,
					stockmoves.discountpercent2,
					stockmoves.warranty,
					stockmoves.standardcost as costo,
					stockmoves.ref4 as poline,
					stockmaster.flagadvance,
					if(stockmoves.refundpercentmv = 0,1,stockmoves.refundpercentmv) as refundpercentmv,
					stockmoves.showdescription
				FROM stockmoves, stockmaster INNER JOIN stockcategory ON stockcategory.categoryid=stockmaster.categoryid, locations
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
				$Discount=$myrow['discountpercent'];
				
				$LineNumber = $_SESSION['ItemsNotes'.$identifier]->LineCounter;
				$quantity = $myrow['quantity'];
				if($myrow['flagadvance'] == 1){
					$sql2 = "SELECT	abs(qty)-abs(qtyinvoiced) as cantidad
							FROM stockmoves
							WHERE type = '".$_SESSION['ItemsNotes'.$identifier]->InvoiceType."'
							AND transno = '".$_SESSION['ItemsNotes'.$identifier]->InvoiceNo."'";
					$result2 = DB_query($sql2,$db);
					$myrow2 = DB_fetch_array($result2);
					$quantity = $myrow2['cantidad'];
					/*if($_SESSION['UserID'] == "admin"){
					 echo "<pre>".$sql2;
					exit;
					}*/
				}
				$_SESSION['ItemsNotes'.$identifier]->add_to_cart($myrow['stockid'],
						$quantity,
						$myrow['description'],
						$myrow['price'],
						$Discount,
						$myrow['units'],
						$myrow['volume'],
						$myrow['kgs'],
						$myrow['qohatloc'],
						$myrow['mbflag'],
						0,
						0,
						$myrow['discountcategory'],
						$myrow['controlled'],	/*Controlled*/
						$myrow['serialised'],
						$myrow['decimalplaces'],
						$myrow['narrative'],
						'No', /* Update DB */
						$LineNumber,
						//								ConvertSQLDate($myrow['itemdue']),
						$myrow['taxcatid'],
						'',
						ConvertSQLDate($myrow['trandate']),
						$myrow['poline'],
						$EstimatedAvgCost,
						$myrow['eoq'],
						$myrow['nextserialno'],
						$myrow['fromstkloc1'],
						$myrow['locationname1'],
						$myrow['discountpercent1'],
						$myrow['discountpercent2'],
						$myrow['warranty'],
						0,
						$myrow['redinvoice'],
						$myrow['costo'],
						$myrow['disabledprice'],
						$myrow['servicestatus'],
						$Devolucion,
						$totalsale,
						1,
						$myrow['salestype'],
						$myrow['showdescription'],
						$_SESSION['ItemsNotes'.$identifier]->deccomercial,
						$directpriceInComercial,
						$directpriceInPriceList,
						$myrow['readOnlyValues'],
						$_SESSION['ItemsNotes'.$identifier]->descCliente1,
						$_SESSION['ItemsNotes'.$identifier]->descCliente2,
						$myrow['modifiedpriceanddiscount'],
						$groupname,
						$myrow['flagadvance'],
						$myrow['stkmovid']
							
				);
				
				$_SESSION['ItemsNotes'.$identifier]->GetExistingTaxes($LineNumber, $myrow['stkmoveno']);
				//echo 'Moneda:'. $_SESSION['CurrAbrev'];
				
				if ($myrow['controlled']==1){/* Populate the SerialItems array too*/
					$SQL = 'SELECT 	serialno,
							abs(moveqty) as moveqty ,
							standardcost
						FROM stockserialmoves
						WHERE stockmoveno=' . $myrow['stkmoveno'] . "
							AND stockid = '" . $myrow['stockid'] . "'";
					//echo '<pre>sql:'.$SQL;
					$ErrMsg = _('This invoice can not be credited using this program') . '. ' . _('A manual credit note will need to be prepared') . '. ' . _('The line item') . ' ' . $myrow['stockid'] . ' ' . _('is controlled but the serial numbers or batch numbers could not be retrieved because');
					$DbgMsg = _('The SQL used to get the controlled item details was');
					$SerialItemsResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
					while ($SerialItemsRow = DB_fetch_array($SerialItemsResult)){
						$_SESSION['ItemsNotes'.$identifier]->LineItems[$LineNumber]->SerialItems[$SerialItemsRow['serialno']] = new SerialItem($SerialItemsRow['serialno'], $SerialItemsRow['moveqty'],$SerialItemsRow['standardcost'],0);
						$_SESSION['ItemsNotes'.$identifier]->LineItems[$LineNumber]->QtyDispatched = $SerialItemsRow['moveqty'];
					}
				} /* end if the item is a controlled item */
			} /* loop thro line items from stock movement records */
		}
	}
?>