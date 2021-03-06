<?php
Class Cart {
	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $total; /*total cost of the items ordered */
	var $totalVolume;
	var $totalWeight;
	var $LineCounter;
	var $ItemsOrdered; /*no of different line items ordered */
	var $DeliveryDate;
	var $DefaultSalesType;
	var $SalesTypeName;
	var $DefaultCurrency;
	var $PaymentTerms;
	var $Comments;
	var $Location;
	var $LocationName;
	var $SupplierNo;
	var $SupplierName;
	var $Orig_OrderDate;
	var $TransID;
	var $FreightTaxes;
	var $OrderNo;
	var $Quotation;
	var $TaxGroup;
	var $DispatchTaxProvince;
	var $vtigerProductID;
	var $DefaultPOLine;
	var $DeliveryDays;
	var $AlmacenStock;
	var $AlmacenStockName;
	var $RFC;
	var $CostumerContact;
	var $DiscPercent1;
	var $DiscPercent2;
	var $warranty;



	function Cart(){
	/*Constructor function initialises a new shopping cart */
		$this->LineItems = array();
		$this->total=0;
		$this->ItemsOrdered=0;
		$this->LineCounter=0;
		$this->DefaltSalesType="";
		$this->FreightCost =0;
		$this->FreightTaxes = array();
	}

	function add_to_cart($StockID,
				$Qty,
				$Descr,
				$Price,
				$Disc=0,
				$UOM,
				$Volume,
				$Weight,
				$QOHatLoc=0,
				$MBflag='B',
				$ActDispatchDate=NULL,
				$QtyInvoiced=0,
				$DiscCat='',
				$Controlled=0,
				$Serialised=0,
				$DecimalPlaces=0,
				$Narrative='',
				$UpdateDB='No',
				$LineNumber=-1,
				$TaxCategory=0,
				$vtigerProductID='',
				$ItemDue = '',
				$POLine='',
				$StandardCost=0,
				$EOQ=1,
				$NextSerialNo=0,
				$AlmacenStock='',
				$AlmacenStockName='',
				$DiscPercent1=0,
				$DiscPercent2=0,
				$warranty=0
				){
		if (isset($StockID) AND $StockID!="" AND $Qty>0 AND isset($Qty)){
			if ($Price<0){ /*madness check - use a credit note to give money away!*/
				$Price=0;
			}

			if ($LineNumber==-1){
				$LineNumber = $this->LineCounter;  //desarrollo LineCounter;
				//echo "<br>ItemsOrdered:".$this->ItemsOrdered;
				//echo "<br>LineCounter:".$this->LineCounter;

			}

			$this->LineItems[$LineNumber] = new LineDetails($LineNumber,
									$StockID,
									$Descr,
									$Qty,
									$Price,
									$Disc,
									$UOM,
									$Volume,
									$Weight,
									$QOHatLoc,
									$MBflag,
									$ActDispatchDate,
									$QtyInvoiced,
									$DiscCat,
									$Controlled,
									$Serialised,
									$DecimalPlaces,
									$Narrative,
									$TaxCategory,
									$ItemDue,
									$POLine,
									$StandardCost,
									$EOQ,
									$NextSerialNo,
									$AlmacenStock,
									$AlmacenStockName,
									$DiscPercent1,
									$DiscPercent2,
									$warranty,
									0
									);
			$this->ItemsOrdered++;
			if ($UpdateDB=='Yes'){
				/*ExistingOrder !=0 set means that an order is selected or created for entry
				of items - ExistingOrder is set to 0 in scripts that should not allow
				adding items to the order - New orders have line items added at the time of
				committing the order to the DB in DeliveryDetails.php
				 GET['ModifyOrderNumber'] is only set when the items are first
				being retrieved from the DB - dont want to add them again - would return
				errors anyway */
				global $db;
				$sql = "INSERT INTO suppnotesorderdetails (orderlineno,
									orderno,
									stkcode,
									quantity,
									unitprice,
									discountpercent1,
									discountpercent2,
									discountpercent,
									itemdue,
									fromstkloc,
									poline,
									warranty
									)
								VALUES(" . $this->LineCounter . ",
									" . $_SESSION['ExistingOrder'] . ",
									'" . trim(strtoupper($StockID)) ."',
									" . $Qty . ",
									" . $Price . ",
									" . $DiscPercent1 . ",
									" . $DiscPercent2 . ",
									" . $Disc . ",'
									" . $ItemDue . "',
									'" . trim(strtoupper($AlmacenStock)) ."',
									" . $POLine . ",
									" . $warranty . "

									)";
				echo '<br>sql:'.$sql.'<br>';
				$result = DB_query($sql,$db , _('El Producto ') . ' ' . strtoupper($StockID) . ' ' ._('no se puede insertar'));

			}
			$this->LineCounter++;
			Return 1;
		}
		Return 0;

	}

	function update_cart_item( $UpdateLineNumber, $Qty, $Price, $Disc, $Narrative, $UpdateDB='No', $ItemDue, $POLine,$DiscPercent1,$DiscPercent2,$AlmacenStock,$warranty){

		if ($Qty>0){
			$this->LineItems[$UpdateLineNumber]->Quantity = $Qty;
		}
		$this->LineItems[$UpdateLineNumber]->Price = $Price;
		$this->LineItems[$UpdateLineNumber]->DiscountPercent = $Disc;
		$this->LineItems[$UpdateLineNumber]->DiscountPercent1 = $DiscPercent1;
		$this->LineItems[$UpdateLineNumber]->DiscountPercent2 = $DiscPercent2;
		$this->LineItems[$UpdateLineNumber]->Narrative = $Narrative;
		$this->LineItems[$UpdateLineNumber]->ItemDue = $ItemDue;
		$this->LineItems[$UpdateLineNumber]->POLine = $POLine;
		$this->LineItems[$UpdateLineNumber]->AlmacenStock = $AlmacenStock;
		$this->LineItems[$UpdateLineNumber]->warranty = $warranty;

		if ($UpdateDB=='Yes'){
			global $db;
			$result = DB_query("UPDATE suppnotesorderdetails
						SET quantity=" . $Qty . ",
						unitprice=" . $Price . ",
						discountpercent=" . $Disc . ",
						discountpercent1=" . $DiscPercent1 . ",
						discountpercent2=" . $DiscPercent2 . ",
						narrative ='" . DB_escape_string($Narrative) . "',
						itemdue = '" . FormatDateForSQL($ItemDue) . "',
						poline = '" . DB_escape_string($POLine) . "',
						fromstkloc = '" . DB_escape_string($AlmacenStock) . "',
						warranty=" . $warranty . "
					WHERE orderno=" . $_SESSION['ExistingOrder'] . "
					AND orderlineno=" . $UpdateLineNumber
				, $db
				, _('El producto') . ' ' . $UpdateLineNumber .  ' ' . _('no se puede actualizar'));
		}
	}

	function remove_from_cart($LineNumber, $UpdateDB='No'){
		if (!isset($LineNumber) || $LineNumber=='' || $LineNumber < 0){ /* over check it */
			prnMsg(_('No Line Number passed to remove_from_cart, so nothing has been removed.'), 'error');
			return;
		}
		if ($UpdateDB=='Yes'){
			global $db;
			if ($this->Some_Already_Delivered($LineNumber)==0){
				/* nothing has been delivered, delete it. */
				$result = DB_query('DELETE FROM suppnotesorderdetails
						     WHERE orderno=' . $_SESSION['ExistingOrder'] . '
						     AND orderlineno=' . $LineNumber,
									$db,
									_('The order line could not be deleted because')
									);
				prnMsg( _('Deleted Line Number'). ' ' . $LineNumber . ' ' . _('from existing Order Number').' ' . $_SESSION['ExistingOrder'], 'success');
			} else {
				/* something has been delivered. Clear the remaining Qty and Mark Completed */
				$result = DB_query('UPDATE suppnotesorderdetails SET quantity=qtyinvoiced, completed=1
									WHERE orderno='.$_SESSION['ExistingOrder'].' AND orderlineno=' . $LineNumber ,
									$db,
								   _('The order line could not be updated as completed because')
								   );
				prnMsg(_('Removed Remaining Quantity and set Line Number '). ' ' . $LineNumber . ' ' . _('as Completed for existing Order Number').' ' . $_SESSION['ExistingOrder'], 'success');
			}
		}
		/* Since we need to check the LineItem above and might affect the DB, don't unset until after DB is updates occur */
		//unset($this->LineItems[$LineNumber]);
		$this->LineItems[$LineNumber]->erased = 1;
		$this->ItemsOrdered--;

	}//remove_from_cart()

	function erase_from_cart($LineNumber){
		//if (!isset($LineNumber) || $LineNumber=='' || $LineNumber < 0){ /* over check it */
		//	prnMsg(_('No Line Number passed to remove_from_cart, so nothing has been removed.').$LineNumber, 'error');
		//	return;
		//}
		/* Since we need to check the LineItem above and might affect the DB, don't unset until after DB is updates occur */
		unset($this->LineItems[$LineNumber]);
		//$this->LineItems[$LineNumber]->erased = 1;
		//$this->ItemsOrdered--;

	}//remove_from_cart()

	function Get_StockID_List(){
		/* Makes a comma seperated list of the stock items ordered
		for use in SQL expressions */

		$StockID_List="";
		foreach ($this->LineItems as $StockItem) {
			$StockID_List .= ",'" . $StockItem->StockID . "'";

		}

		return substr($StockID_List, 1);

	}

	function Any_Already_Delivered(){
		/* Checks if there have been deliveries of line items */
		foreach ($this->LineItems as $StockItem) {
			if ($StockItem->QtyInv !=0){
				return 1;
			}
		}
		return 0;
	}

	function Some_Already_Delivered($LineNumber){
		/* Checks if there have been deliveries of a specific line item */
		if ($this->LineItems[$LineNumber]->QtyInv !=0){
			return 1;
		}
		return 0;
	}

	function AllDummyLineItems(){
		foreach ($this->LineItems as $StockItem) {
			if($StockItem->MBflag !='D'){
				return false;
			}
		}
		return false;
	}

	function GetExistingTaxes($LineNumber, $stkmoveno){
		global $db;
		/*Gets the Taxes and rates applicable to this line from the TaxGroup of the branch and TaxCategory of the item
		and the taxprovince of the dispatch location */
		$sql = 'SELECT stockmovestaxes.taxauthid,
				taxauthorities.description,
				taxauthorities.taxglcode,
				stockmovestaxes.taxcalculationorder,
				stockmovestaxes.taxontax,
				stockmovestaxes.taxrate
			FROM stockmovestaxes INNER JOIN taxauthorities
				ON stockmovestaxes.taxauthid = taxauthorities.taxid
			WHERE stkmoveno = ' . $stkmoveno . '
			ORDER BY taxcalculationorder';
		$ErrMsg = _('The taxes and rates for this item could not be retrieved because');
		$GetTaxRatesResult = DB_query($sql,$db,$ErrMsg);

		while ($myrow = DB_fetch_array($GetTaxRatesResult)){

			$this->LineItems[$LineNumber]->Taxes[$myrow['taxcalculationorder']] =
								  new Tax($myrow['taxcalculationorder'],
										$myrow['taxauthid'],
										$myrow['description'],
										$myrow['taxrate'],
										$myrow['taxontax'],
										$myrow['taxglcode']);
		}
	} //end method GetExistingTaxes

	function GetTaxes($LineNumber){
		global $db;
		/*Gets the Taxes and rates applicable to this line from the TaxGroup of the branch and TaxCategory of the item
		and the taxprovince of the dispatch location */

		$this->DispatchTaxProvince = 1;

		$sql = "SELECT taxcatid
			FROM stockmaster
			WHERE stockid='".$this->LineItems[$LineNumber]->StockID."'";
		$TaxCatQuery = DB_query($sql, $db);
		if ($TaxCatRow = DB_fetch_array($TaxCatQuery)) {
			$this->LineItems[$LineNumber]->TaxCategory = $TaxCatRow['taxcatid'];
		}else{
			$this->LineItems[$LineNumber]->TaxCategory=4;
		}


		$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.purchtaxglaccount as taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates INNER JOIN taxgrouptaxes ON
				taxauthrates.taxauthority=taxgrouptaxes.taxauthid
				INNER JOIN taxauthorities ON
				taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid=" . $this->TaxGroup . "
			AND taxauthrates.dispatchtaxprovince=" . $this->DispatchTaxProvince . "
			AND taxauthrates.taxcatid = " . $this->LineItems[$LineNumber]->TaxCategory . "
			ORDER BY taxgrouptaxes.calculationorder";
		$ErrMsg = _('The taxes and rates for this item could not be retrieved because GetTaxes:');
		$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg);
		while ($myrow = DB_fetch_array($GetTaxRatesResult)){
			$this->LineItems[$LineNumber]->Taxes[$myrow['calculationorder']] = new Tax($myrow['calculationorder'],
													$myrow['taxauthid'],
													$myrow['description'],
													$myrow['taxrate'],
													$myrow['taxontax'],
													$myrow['taxglcode']);
		}
	} //end method GetTaxes

	function GetFreightTaxes () {
		global $db;
		/*Gets the Taxes and rates applicable to the freight based on the tax group of the branch combined with the tax category for this particular freight
		and SESSION['FreightTaxCategory'] the taxprovince of the dispatch location */
		$sql = "SELECT taxcatid FROM taxcategories WHERE taxcatname='Freight'";
		$TaxCatQuery = DB_query($sql, $db);
		if ($TaxCatRow = DB_fetch_array($TaxCatQuery)) {
		  $TaxCatID = $TaxCatRow['taxcatid'];
		} else {
  		  prnMsg( _('No se puede encontrar la categoria de impuesto fiscal'),'error');
		  exit();
		}



		$SQL = 'SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.purchtaxglaccount as taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
				FROM taxauthrates INNER JOIN taxgrouptaxes ON
					taxauthrates.taxauthority=taxgrouptaxes.taxauthid
					INNER JOIN taxauthorities ON
					taxauthrates.taxauthority=taxauthorities.taxid
				WHERE taxgrouptaxes.taxgroupid=' . $this->TaxGroup . '
				AND taxauthrates.dispatchtaxprovince=' . $this->DispatchTaxProvince . '
				AND taxauthrates.taxcatid = ' . $TaxCatID . '
				ORDER BY taxgrouptaxes.calculationorder';
		$ErrMsg = _('Los impuestos y tasas para este producto no se pueden recuperar por que GetFreightTaxes:');
		$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg);
		while ($myrow = DB_fetch_array($GetTaxRatesResult)){
			$this->FreightTaxes[$myrow['calculationorder']] = new Tax($myrow['calculationorder'],
											$myrow['taxauthid'],
											$myrow['description'],
											$myrow['taxrate'],
											$myrow['taxontax'],
											$myrow['taxglcode']);
		}
	} //end method GetFreightTaxes()

} /* end of cart class defintion */
Class LineDetails {
	Var $LineNumber;
	Var $StockID;
	Var $ItemDescription;
	Var $Quantity;
	Var $Price;
	Var $DiscountPercent;
	Var $Units;
	Var $Volume;
	Var $Weight;
	Var $ActDispDate;
	Var $QtyInv;
	Var $QtyDispatched;
	Var $StandardCost;
	Var $QOHatLoc;
	Var $MBflag;	/*Make Buy Dummy, Assembly or Kitset */
	Var $DiscCat; /* Discount Category of the item if any */
	Var $Controlled;
	Var $Serialised;
	Var $DecimalPlaces;
	Var $SerialItems;
	Var $Narrative;
	Var $TaxCategory;
	Var $Taxes;
	Var $WorkOrderNo;
	Var $ItemDue;
	Var $POLine;
	Var $EOQ;
	Var $NextSerialNo;
	Var $AlmacenStock;
	Var $AlmacenStockName;
	Var $DiscountPercent1;
	Var $DiscountPercent2;
	Var $warranty;
	Var $erased;
	function LineDetails ($LineNumber,
				$StockItem,
				$Descr,
				$Qty,
				$Prc,
				$DiscPercent,
				$UOM,
				$Volume,
				$Weight,
				$QOHatLoc,
				$MBflag,
				$ActDispatchDate,
				$QtyInvoiced,
				$DiscCat,
				$Controlled,
				$Serialised,
				$DecimalPlaces,
				$Narrative,
				$TaxCategory,
				$ItemDue,
				$POLine,
				$StandardCost,
				$EOQ,
				$NextSerialNo,
				$AlmacenStock,
				$AlmacenStockName,
				$DiscountPercent1,
				$DiscountPercent2,
				$warranty,
				$erased=0
				){
/* Constructor function to add a new LineDetail object with passed params */
		$this->LineNumber = $LineNumber;
		$this->StockID =$StockItem;
		$this->ItemDescription = $Descr;
		$this->Quantity = $Qty;
		$this->Price = $Prc;
		$this->DiscountPercent = $DiscPercent;
		$this->DiscountPercent1 = $DiscountPercent1;
		$this->DiscountPercent2 = $DiscountPercent2;
		$this->Units = $UOM;
		$this->Volume = $Volume;
		$this->Weight = $Weight;
		$this->ActDispDate = $ActDispatchDate;
		$this->QtyInv = $QtyInvoiced;
		$this->warranty=$warranty;
		$this->erased = 0;
		if ($Controlled==1){
			$this->QtyDispatched = 0;
		} else {
			$this->QtyDispatched = $Qty - $QtyInvoiced;
		}
		$this->QOHatLoc = $QOHatLoc;
		$this->MBflag = $MBflag;
		$this->DiscCat = $DiscCat;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->SerialItems = array();
		$this->Narrative = $Narrative;
		$this->Taxes = array();
		$this->TaxCategory =$TaxCategory;
		$this->WorkOrderNo = 0;
		$this->ItemDue = $ItemDue;
		$this->POLine = $POLine;
		$this->StandardCost = $StandardCost;
		$this->EOQ = $EOQ;
		$this->NextSerialNo = $NextSerialNo;
		$this->AlmacenStock = $AlmacenStock;
		$this->AlmacenStockName =$AlmacenStockName;
	} //end constructor function for LineDetails
}

Class Tax {
	Var $TaxCalculationOrder;  /*the index for the array */
	Var $TaxAuthID;
	Var $TaxAuthDescription;
	Var $TaxRate;
	Var $TaxOnTax;
	Var $TaxGLCode;
	function Tax ($TaxCalculationOrder,
			$TaxAuthID,
			$TaxAuthDescription,
			$TaxRate,
			$TaxOnTax,
			$TaxGLCode){
		$this->TaxCalculationOrder = $TaxCalculationOrder;
		$this->TaxAuthID = $TaxAuthID;
		$this->TaxAuthDescription = $TaxAuthDescription;
		$this->TaxRate =  $TaxRate;
		$this->TaxOnTax = $TaxOnTax;
		$this->TaxGLCode = $TaxGLCode;
	}
}

?>
