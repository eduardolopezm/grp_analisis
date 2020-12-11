<?php
/* $Revision: 1.16 $ */
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

	if (isset($_SESSION['PO'])){
		unset ($_SESSION['PO']->LineItems);
		unset ($_SESSION['PO']);
	}

	$_SESSION['ExistingOrderRecived']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireSupplierSelection'] = 0;
	$_SESSION['PO'] = new PurchOrder;

	$_SESSION['PO']->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];
	
/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

	

               $sql = "SELECT distinct reference,
                    locations.locationname as trffromloc,
                    locations2.locationname as trftoloc,
		     locations2.loccode as trftolocode,
                    loctransfers.shipdate as deliverydate, 
                    recdate,
                    sum(materialcost) as materialcost,
                    shipqty as quantityord,
		    recqty as recqty,
                    stockcostsxlegal.avgcost*shipqty as tot,
		    userregister,
		    userrec,
		    requisitionorderdetails.podetailitem,
		    loctransfers.stockid AS itemcode,
		    stockmaster.serialised,
		    controlled,
		    itemdescription,
		    discountpercent1,
		    discountpercent2,
		    discountpercent3,
		    unitprice,
		    quantityvirtual,
		    stockmaster.barcode,
		    requisitionorderdetails.intostocklocation,
		    loctransfers.transferline

                FROM loctransfers
                INNER JOIN locations ON loctransfers.shiploc=locations.loccode 
                JOIN  (select * from locations) as locations2 ON loctransfers.recloc=locations2.loccode 
                INNER JOIN stockmaster ON loctransfers.stockid=stockmaster.stockid
		INNER JOIN tags ON locations.tagref = tags.tagref
		INNER JOIN stockcostsxlegal ON tags.legalid = stockcostsxlegal.legalid AND stockcostsxlegal.stockid = loctransfers.stockid
		INNER JOIN transferrequistions ON transferrequistions.transferno=loctransfers.reference
		INNER JOIN requisitionorderdetails ON requisitionorderdetails.podetailitem=transferrequistions.norequisition AND loctransfers.stockid = requisitionorderdetails.itemcode AND requisitionorderdetails.quantityvirtual>0
		WHERE shipqty > recqty ";
		$sql=$sql."  and loctransfers.recloc='" . $LocDestination."'";
		$sql=$sql." and loctransfers.shipdate >='" . $fechaini."'";
		$sql=$sql." and loctransfers.shipdate <='" . $fechafin."'";
		if(strlen(trim($Reference))>0){
			$sql=$sql." and loctransfers.reference='" . $Reference."'";
		}
		$sql=$sql." GROUP BY reference, locations.locationname,
                    locations2.locationname,loctransfers.stockid,
                    shipdate, loctransfers.transferline,
                    recdate
		ORDER BY loctransfers.stockid";
		//echo $sql;
	      $ErrMsg =  _('The lines on the purchase order cannot be retrieved because');
	      $DbgMsg =  _('The SQL statement that was used to retrieve the purchase order lines was');
              $LineItemsResult = db_query($sql,$db,$ErrMsg,$DbgMsg);
	      $indice=0;
	      if (db_num_rows($LineItemsResult) > 0) {

                while ($myrow=db_fetch_array($LineItemsResult)) {

					 if (is_null($myrow['glcode'])){
						$GLCode = '';
					 } else {
						$GLCode = $myrow['glcode'];
					 }
					 if (is_null($myrow['units'])){
						$Units = _('each');
					 } else {
						$Units = $myrow['units'];
					 }
					 if (is_null($myrow['itemcode'])){
						$StockID = '';
					 } else {
						$StockID = $myrow['itemcode'];
					 }
					// $indice=$indice+1;

					$_SESSION['PO']->add_to_order($_SESSION['PO']->LinesOnOrder+1,
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
							0,
							0,
							0,
							$myrow['quantityvirtual'],
							$myrow['recqty'],
							$myrow['accountname'],
							$myrow['decimalplaces'],0,0,0,0,0,0,0,0,0,0,0,'','',$myrow['barcode'],
							$myrow['podetailitem'],
							$myrow['trftoloc'],
							$myrow['intostocklocation'],
							$myrow['trffromloc'],
							$myrow['reference'],
							$myrow['transferline']
							
							);

				    $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
	                $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];  /*Needed for receiving goods and GL interface */
			$indice=$indice+1;
			//echo "<br>entra". $StockID;
             } /* line PO from purchase order details */
	}
      
?>