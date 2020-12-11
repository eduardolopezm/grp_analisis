<?php
/* $Revision: 1.16 $ */
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */
	
	$decimalesTipoCambio = 8;
	if(empty($_SESSION['TCDecimals']) == false) {
		$decimalesTipoCambio = $_SESSION['TCDecimals'];
	}

	if (isset($_SESSION['PO'])){
		unset ($_SESSION['PO']->LineItems);
		unset ($_SESSION['PO']);
	}

	$_SESSION['ExistingOrderRecived']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireSupplierSelection'] = 0;
	$_SESSION['PO'] = new PurchOrder;

	$_SESSION['PO']->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

	$OrderHeaderSQL = 'SELECT purchorders.supplierno, 
				suppliers.suppname, 
				purchorders.comments, 
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
				purchorders.currcode,
				locations.managed ,
				purchorders.refundpercentpurch,
				purchorders.systypeorder,
				purchorders.wo, 
				purchorders.realorderno,
				purchorders.tagref,
				tags.tagdescription,
				tags.legalid,
				legalbusinessunit.legalname,
				purchorders.nu_ue,
				tb_cat_unidades_ejecutoras.desc_ue
			FROM purchorders 
			LEFT JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ue = purchorders.nu_ue AND tb_cat_unidades_ejecutoras.ur = purchorders.tagref
			LEFT JOIN tags ON tags.tagref = purchorders.tagref
			LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
			LEFT JOIN locations ON purchorders.intostocklocation=locations.loccode, suppliers 
			WHERE purchorders.supplierno = suppliers.supplierid 
			AND purchorders.orderno = ' . $_GET['ModifyOrderNumber'];

       $ErrMsg =  _('The order cannot be retrieved because');
       $DbgMsg =  _('The SQL statement that was used and failed was');
       $GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {

              $myrow = DB_fetch_array($GetOrdHdrResult);
              $_SESSION['PO']->OrderNo = $_GET['ModifyOrderNumber'];
              $_SESSION['PO']->SupplierID = $myrow['supplierno'];
              $_SESSION['PO']->SupplierName = $myrow['suppname'];
              $_SESSION['PO']->CurrCode = $myrow['currcode'];
              $_SESSION['PO']->Orig_OrderDate = $myrow['orddate'];
              $_SESSION['PO']->AllowPrintPO = $myrow['allowprint'];
              $_SESSION['PO']->DatePurchaseOrderPrinted = $myrow['dateprinted'];
              $_SESSION['PO']->Comments = $myrow['comments'];
              $_SESSION['PO']->ExRate = number_format($myrow['rate'], $decimalesTipoCambio);
              $_SESSION['PO']->Wo = $myrow['wo'];
              $_SESSION['PO']->OrderNo2 = $myrow['realorderno'];
              $_SESSION['PO']->tagname = $myrow['tagref']." - ".$myrow['tagdescription'];
              $_SESSION['PO']->legalname = $myrow['legalname'];

              $_SESSION['PO'.$identifier]->unidadEjecutora = $myrow['nu_ue'];
              $_SESSION['PO'.$identifier]->unidadEjecutoraNombre = $myrow['nu_ue']." - ".$myrow['desc_ue'];
              
              if ($_SESSION['PO']->CurrCode != "MXN" and $_SESSION['PO']->ExRate==0){
              	$fecha = $myrow['orddate'];
              	$moneda = $_SESSION['PO']->CurrCode;
              	$_SESSION['PO']->ExRate = GetCurrencyRateByDate($fecha, $moneda, $db);
              }
              
              $_SESSION['PO']->Location = $myrow['intostocklocation'];
	     // echo 'entra:'.$_SESSION['PO']->Location;
              $_SESSION['PO']->Initiator = $myrow['initiator'];
              $_SESSION['PO']->RequisitionNo = $myrow['requisitionno'];
              $_SESSION['PO']->DelAdd1 = $myrow['deladd1'];
              $_SESSION['PO']->DelAdd2 = $myrow['deladd2'];
              $_SESSION['PO']->DelAdd3 = $myrow['deladd3'];
              $_SESSION['PO']->DelAdd4 = $myrow['deladd4'];
              $_SESSION['PO']->DelAdd5 = $myrow['deladd5'];
              $_SESSION['PO']->DelAdd6 = $myrow['deladd6'];
              $_SESSION['PO']->Managed = $myrow['managed'];
	      $_SESSION['PO']->version = $myrow['version'];
	      $_SESSION['PO']->Stat = $myrow['status'];	
	      $_SESSION['PO']->StatComments = $myrow['stat_comment'];
	      $_SESSION['PO']->deliverydate = $myrow['deliverydate']; //ConvertSQLDate($myrow['deliverydate']);
		  $_SESSION['PO']->Typeorder = $myrow['systypeorder'];
	      $_SESSION['ExistingOrderRecived'] = $_SESSION['PO']->OrderNo;
		  
	      $refundpercent=$myrow['refundpercentpurch'];
			
/*now populate the line PO array with the purchase order details records */

              $LineItemsSQL = "SELECT podetailitem, itemcode,
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
				purchorderdetails.refundpercent,
				purchorderdetails.clavepresupuestal,
				purchorderdetails.ln_clave_iden,
				stockmaster.categoryid,
				stockmaster.mbflag
				FROM purchorderdetails 
				LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
				LEFT JOIN chartmaster ON purchorderdetails.glcode=chartmaster.accountcode
				WHERE purchorderdetails.completed=0 AND purchorderdetails.status=2
				AND purchorderdetails.orderno = '" . $_GET['ModifyOrderNumber'] . "'
				ORDER BY podetailitem";
		
	      $ErrMsg =  _('The lines on the purchase order cannot be retrieved because');
	      $DbgMsg =  _('The SQL statement that was used to retrieve the purchase order lines was');
              $LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);

	      if (db_num_rows($LineItemsResult) > 0) {
            while ($myrow=db_fetch_array($LineItemsResult)) {
			 	if (is_null($myrow['glcode'])){
					$GLCode = '';
				 } else {
					$GLCode = $myrow['glcode'];
				 }

				 // Obtener cuenta de la matriz Inicio
				 $GLCode = "";
				 $cuentaAbonoProveedor = "";
				 $sqlWhere = "";
				 if (trim($myrow['ln_clave_iden']) != '') {
				 	// Solo cuando se tiene identidicador
				 	$sqlWhere = " AND ln_clave = '".$myrow['ln_clave_iden']."' ";
				 }
				 $SQL = "SELECT stockact, accountegreso FROM stockcategory 
				 WHERE 
				 categoryid = '".$myrow['categoryid']."' 
				 AND accountegreso IN (SELECT accountcode FROM accountxsupplier WHERE supplierid = '".$_SESSION['PO']->SupplierID."') ".$sqlWhere;
				 if ($_SESSION['UserID'] == 'desarrollo') {
				 	// echo "<br> SQL: ".$SQL;
				 }
				 $resultCuenta = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
				 while ($myrowCuenta=db_fetch_array($resultCuenta)) {
				 	$GLCode = $myrowCuenta['stockact'];
				 	$cuentaAbonoProveedor = $myrowCuenta['accountegreso'];
				 }
				 // Obtener cuenta de la matriz FIN

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
						$myrow['shiptref'],
						$myrow['completed'],
						$myrow['jobref'],
						$myrow['qtyinvoiced'],
						$myrow['quantityrecd'],
						$myrow['accountname'],
						$myrow['decimalplaces'],0,0,0,0,0,0,0,0,0,0,0,'','',$myrow['barcode'],$myrow['refundpercent'],$refundpercent,
						$myrow['stockupdate'], 0, '', '', $myrow['clavepresupuestal'], $cuentaAbonoProveedor, $myrow['mbflag']);

			    $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
                $_SESSION['PO']->LineItems[$_SESSION['PO']->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];  /*Needed for receiving goods and GL interface */
         } /* line PO from purchase order details */
      } //end is there were lines on the order
   } // end if there was a header for the order
?>