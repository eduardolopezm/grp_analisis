<?php
/* $Revision: 1.16 $ */
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

$decimalesTipoCambio = 8;
if(empty($_SESSION['TCDecimals']) == false) {
	$decimalesTipoCambio = $_SESSION['TCDecimals'];
}

if(isset($_SESSION['PurchOrders'])) {
	unset($_SESSION['PurchOrders']);
}

if(isset($_SESSION['PO'])) {
	unset($_SESSION['PO']->LineItems);
	unset($_SESSION['PO']);
}

//$_SESSION['ExistingOrderRecived'] = $_GET['ModifySupplierOrderNumber'];
$_SESSION['ModifySupplierOrderNumber'] = $_GET['ModifySupplierOrderNumber'];
$_SESSION['RequireSupplierSelection'] = 0;
$_SESSION['PurchOrders'] = array();

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

$OrderHeaderSQL = '
	SELECT purchorders.supplierno, 
	suppliers.suppname, 
	purchorders.comments, 
	purchorders.orderno,
	purchorders.orddate, 
	purchorders.rate, 
	purchorders.dateprinted, 
	purchorders.deladd1, 
	purchorders.deladd2, 
	purchorders.deladd3, 
	purchorders.deladd4, 
	purchorders.deladd5, 
	purchorders.deladd6, 
	purchorders.allowprint, 
	purchorders.requisitionno, 
	purchorders.intostocklocation, 
	purchorders.initiator, 
	purchorders.version, 
	purchorders.status, 
	purchorders.stat_comment, 
	purchorders.deliverydate,
	suppliers.currcode,
	locations.managed ,
	purchorders.refundpercentpurch,
	purchorders.systypeorder
	FROM purchorders 
	LEFT JOIN locations ON purchorders.intostocklocation=locations.loccode,
	suppliers 
	WHERE purchorders.supplierno = suppliers.supplierid 
	AND purchorders.supplierorderno = ' . $_GET['ModifySupplierOrderNumber'];

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg =  _('The SQL statement that was used and failed was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

while($myrow = DB_fetch_array($GetOrdHdrResult)) {

	$purchOrder = new PurchOrder();
	$purchOrder->OrderNo = $myrow['orderno'];
    $purchOrder->SupplierID = $myrow['supplierno'];
    $purchOrder->SupplierName = $myrow['suppname'];
    $purchOrder->CurrCode = $myrow['currcode'];
    $purchOrder->Orig_OrderDate = $myrow['orddate'];
    $purchOrder->AllowPrintPO = $myrow['allowprint'];
    $purchOrder->DatePurchaseOrderPrinted = $myrow['dateprinted'];
    $purchOrder->Comments = $myrow['comments'];
    $purchOrder->ExRate = number_format($myrow['rate'], $decimalesTipoCambio);
    
    if ($purchOrder->CurrCode != "MXN" AND $purchOrder->ExRate == 0) {
    	$fecha = $myrow['orddate'];
    	$moneda = $purchOrder->CurrCode;
    	$purchOrder->ExRate = GetCurrencyRateByDate($fecha, $moneda, $db);
    }
    
    $purchOrder->Location = $myrow['intostocklocation'];
    $purchOrder->Initiator = $myrow['initiator'];
    $purchOrder->RequisitionNo = $myrow['requisitionno'];
    $purchOrder->DelAdd1 = $myrow['deladd1'];
    $purchOrder->DelAdd2 = $myrow['deladd2'];
    $purchOrder->DelAdd3 = $myrow['deladd3'];
    $purchOrder->DelAdd4 = $myrow['deladd4'];
    $purchOrder->DelAdd5 = $myrow['deladd5'];
    $purchOrder->DelAdd6 = $myrow['deladd6'];
    $purchOrder->Managed = $myrow['managed'];
	$purchOrder->version = $myrow['version'];
	$purchOrder->Stat = $myrow['status'];	
	$purchOrder->StatComments = $myrow['stat_comment'];
	$purchOrder->deliverydate = ConvertSQLDate($myrow['deliverydate']);
	$purchOrder->Typeorder = $myrow['systypeorder'];
	$purchOrder->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];
	$_SESSION['ExistingOrderRecived'] = $purchOrder->OrderNo;	  
	$refundpercent = $myrow['refundpercentpurch'];
}		
	/*now populate the line PO array with the purchase order details records */

   /* $LineItemsSQL = '
    	SELECT podetailitem, itemcode,
		stockmaster.description,
		deliverydate,
		itemdescription,
		glcode,
		accountname,
		qtyinvoiced,
		unitprice,
		units,
		quantityord,
		quantityrecd,
		shiptref,
		completed,
		jobref,
		stdcostunit,
		stockmaster.controlled,
		stockmaster.serialised,
		stockmaster.decimalplaces,
		discountpercent1,
		discountpercent2,
		discountpercent3,
		stockmaster.barcode,
		stockupdate,
		purchorderdetails.refundpercent
		FROM purchorderdetails LEFT JOIN stockmaster
		ON purchorderdetails.itemcode=stockmaster.stockid
		LEFT JOIN chartmaster
		ON purchorderdetails.glcode=chartmaster.accountcode
		WHERE purchorderdetails.completed=0
		AND purchorderdetails.orderno =' . $purchOrder->OrderNo . "
		ORDER BY podetailitem";*/
	$LineItemsSQL = "SELECT podetailitem, 
		purchorderdetails.itemcode,
		stockmaster.description,
		purchorderdetails.deliverydate,
		itemdescription,
		glcode,
		accountname,
		qtyinvoiced,
		sum(unitprice)/tabla.contador as unitprice,
		units,
		sum(quantityord) as quantityord,
		sum(quantityrecd) as quantityrecd,
		shiptref,
		completed,
		jobref,
		stdcostunit,
		stockmaster.controlled,
		stockmaster.serialised,
		stockmaster.decimalplaces,
		sum(purchorderdetails.discountpercent1)/tabla.contador as discountpercent1,
		sum(purchorderdetails.discountpercent2)/tabla.contador as discountpercent2,
		sum(purchorderdetails.discountpercent3)/tabla.contador as discountpercent3,
		stockmaster.barcode,
		stockupdate,
		purchorderdetails.refundpercent
		FROM purchorderdetails LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
		LEFT JOIN chartmaster ON purchorderdetails.glcode=chartmaster.accountcode
		INNER JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
		INNER JOIN (SELECT COUNT(purchorders.orderno) as contador,
											purchorders.supplierorderno,
											purchorderdetails.itemcode
									FROM purchorders
										INNER JOIN purchorderdetails on purchorderdetails.orderno = purchorders.orderno
									WHERE purchorders.supplierorderno ='".$_GET['ModifySupplierOrderNumber']."'
									GROUP BY  purchorderdetails.itemcode
									) as tabla ON  purchorders.supplierorderno = tabla.supplierorderno and purchorderdetails.itemcode = tabla.itemcode
		WHERE purchorderdetails.completed=0
		AND purchorders.supplierorderno = '".$_GET['ModifySupplierOrderNumber']."'
		GROUP BY  purchorderdetails.itemcode
		ORDER BY podetailitem";
    $ErrMsg =  _('The lines on the purchase order cannot be retrieved because');
	$DbgMsg =  _('The SQL statement that was used to retrieve the purchase order lines was');
    $LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);//

	if(db_num_rows($LineItemsResult) > 0) {

    	while($myrow = DB_fetch_array($LineItemsResult)) {

			if(is_null($myrow['glcode'])) {
				$GLCode = '';
			} else {
				$GLCode = $myrow['glcode'];
			}
			if(is_null($myrow['units'])) {
				$Units = _('each');
			} else {
				$Units = $myrow['units'];
			}
			if(is_null($myrow['itemcode'])){
				$StockID = '';
			} else {
				$StockID = $myrow['itemcode'];
			}

			$purchOrder->add_to_order(
				$purchOrder->LinesOnOrder+1,
				$StockID,
				$myrow['serialised'],
				$myrow['controlled'],
				$myrow['quantityord'],
				stripslashes($myrow['itemdescription']),
				$myrow['unitprice'],
				$myrow['discountpercent1'],
				$myrow['discountpercent2'],
				$myrow['discountpercent3'],
				$Units,
				$GLCode,
				ConvertSQLDate($myrow['deliverydate']),
				$myrow['shiptref'],
				$myrow['completed'],
				$myrow['jobref'],
				$myrow['qtyinvoiced'],
				$myrow['quantityrecd'],
				$myrow['accountname'],
				$myrow['decimalplaces'],
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				0,
				'',
				'',
				$myrow['barcode'],
				$myrow['refundpercent'],
				$refundpercent,
				$myrow['stockupdate']
			);

			$purchOrder->LineItems[$purchOrder->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
            $purchOrder->LineItems[$purchOrder->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];  /*Needed for receiving goods and GL interface */
		} /* line PO from purchase order details */
    } //end is there were lines on the order
    
    $_SESSION['PurchOrders'][$purchOrder->OrderNo] = $purchOrder;
    
   	//echo '<pre>'.var_dump($_SESSION['PurchOrders']);
    
 // end if there was a header for the order
?>