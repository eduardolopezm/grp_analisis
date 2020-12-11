<?php
/**
 * Proceso de Compra
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 08/08/2017
 * Fecha Modificación: 08/08/2017
 * Realizar proceso de compra
 */

$digitos=0;
if(isset($_SESSION['TruncarDigitos']))
{
	$digitos=$_SESSION['TruncarDigitos'];
}else{
	$digitos=4;
}
/* $Revision: 1.16 $ */
/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

	if (isset($_SESSION['PO'.$identifier])){
		unset ($_SESSION['PO'.$identifier]->LineItems);
		unset ($_SESSION['PO'.$identifier]);
	}

	$_SESSION['ExistingPurchOrder']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireSupplierSelection'] = 0;
	$_SESSION['PO'.$identifier] = new PurchOrder;

	$_SESSION['PO'.$identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

	$OrderHeaderSQL = 'SELECT purchorders.supplierno, 
				suppliers.suppname, 
				purchorders.comments,
				date_format(purchorders.orddate,"%Y/%m/%d") as orddate,
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
			    purchorders.deliveryby,
				purchorders.servicetype,
				purchorders.currcode,
				purchorders.contact,
				purchorders.telephoneContact,
				locations.managed ,
				purchorders.wo,
                purchorders.tagref,
                purchorders.realorderno,
                tags.tagdescription,
                tags.legalid,
                legalbusinessunit.legalname,
                tb_suficiencias.nu_type,
                (tb_suficiencias.nu_transno) as nu_transno,
                purchorders.systypeorder,
                purchorders.nu_ue
			FROM purchorders 
				LEFT JOIN tb_suficiencias ON tb_suficiencias.sn_orderno = purchorders.orderno
				LEFT JOIN tags ON tags.tagref = purchorders.tagref
				LEFT JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
				LEFT JOIN locations ON purchorders.intostocklocation=locations.loccode,
				suppliers 
			WHERE purchorders.supplierno = suppliers.supplierid 
			AND purchorders.orderno = ' . $_GET['ModifyOrderNumber'];

       $ErrMsg =  _('The order cannot be retrieved because');
       $DbgMsg =  _('The SQL statement that was used and failed was');
       $GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg,$DbgMsg);

	//if (DB_num_rows($GetOrdHdrResult)==1) {
	if (DB_num_rows($GetOrdHdrResult) > 0) {
 
              $myrow = DB_fetch_array($GetOrdHdrResult);
              $tagref = $myrow['tagref'];
              $rate = $myrow['rate'];
              $fechaorigen = $myrow['orddate'];
              $supplierid = $myrow['supplierno'];
              $_SESSION['PO'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
              $_SESSION['PO'.$identifier]->SupplierID = $myrow['supplierno'];
              $_SESSION['PO'.$identifier]->SupplierName = $myrow['suppname'];
              $_SESSION['PO'.$identifier]->CurrCode = $myrow['currcode'];
              $_SESSION['PO'.$identifier]->Orig_OrderDate = $myrow['deliverydate'];
              $_SESSION['PO'.$identifier]->AllowPrintPO = $myrow['allowprint'];
              $_SESSION['PO'.$identifier]->DatePurchaseOrderPrinted = $myrow['dateprinted'];
              $_SESSION['PO'.$identifier]->Comments = $myrow['comments'];
              $_SESSION['PO'.$identifier]->ExRate = $myrow['rate'];
              $_SESSION['PO'.$identifier]->deliveryby = $myrow['deliveryby'];
              $_SESSION['PO'.$identifier]->Wo = $myrow['wo'];
              $_SESSION['PO'.$identifier]->tag = $tagref;
              $_SESSION['PO'.$identifier]->tagname = $tagref." - ".$myrow['tagdescription'];
              $_SESSION['PO'.$identifier]->legalname = $myrow['legalname'];
              $_SESSION['PO'.$identifier]->suficienciaType = $myrow['nu_type'];

              // Obtener el transno mas alto, los otros pueden ser rechazos
              // $SQL = "SELECT (tb_suficiencias.nu_transno) as nu_transno, nu_estatus FROM tb_suficiencias WHERE sn_orderno = '".$_GET['ModifyOrderNumber']."' AND nu_estatus <> 0 GROUP BY nu_estatus";
              // Suficiencia por orden de compra
              $SQL = "SELECT nu_transno, nu_estatus FROM tb_suficiencias WHERE sn_orderno = '".$_GET['ModifyOrderNumber']."' ORDER BY nu_transno DESC LIMIT 1";
              // Suficiencia por requisicion
              $SQL = "SELECT tb_suficiencias.nu_transno, tb_suficiencias.nu_estatus, tb_suficiencias.nu_type
			FROM tb_suficiencias 
			LEFT JOIN purchorders ON purchorders.orderno = tb_suficiencias.sn_orderno
			WHERE purchorders.requisitionno = '".$myrow['requisitionno']."' ORDER BY tb_suficiencias.nu_transno 
			DESC LIMIT 1";
              $datosSuf = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
              $myrowSuf = DB_fetch_array($datosSuf);

              $_SESSION['PO'.$identifier]->suficienciaType = $myrowSuf['nu_type'];
              $_SESSION['PO'.$identifier]->suficienciaTransno = $myrowSuf['nu_transno'];
              $_SESSION['PO'.$identifier]->suficienciaEstatus = $myrowSuf['nu_estatus'];
              $_SESSION['PO'.$identifier]->separarOrdenCompra = $myrow['systypeorder'];
              $_SESSION['PO'.$identifier]->Typeorder = $myrow['systypeorder'];
              $_SESSION['PO'.$identifier]->unidadEjecutora = $myrow['nu_ue'];
              $_SESSION['PO'.$identifier]->estatus = $myrow['status'];
              // agrega texto de descipcion de la OT
              if( $myrow['wo']>0){
              	$SQL="select wodescription from workorders where wo=". $myrow['wo'];
              	$ErrMsg =  _('The order cannot be retrieved because');
              	$DbgMsg =  _('The SQL statement that was used and failed was');
              	$GetOrWOResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
              	
              	if (DB_num_rows($GetOrWOResult)==1) {
              	
              		$myrowWO = DB_fetch_array($GetOrWOResult);
              		$_SESSION['PO'.$identifier]->WoDescription = $myrowWO['wodescription'];
              	}
              	
              }
              
              if ($_SESSION['PO'.$identifier]->CurrCode != "MXN"){
              	$fecha = $myrow['orddate'];
              	$moneda = $_SESSION['PO'.$identifier]->CurrCode;
              	$_SESSION['PO'.$identifier]->ExRate = GetCurrencyRateByDate($fecha, $moneda, $db);
              }
              
              $_SESSION['PO'.$identifier]->Location = $myrow['intostocklocation'];
              $_SESSION['PO'.$identifier]->Initiator = $myrow['initiator'];
              $_SESSION['PO'.$identifier]->RequisitionNo = $myrow['requisitionno'];
              $_SESSION['PO'.$identifier]->ServiceType = $myrow['servicetype'];
              
              $_SESSION['PO'.$identifier]->contact = $myrow['contact'];
              $_SESSION['PO'.$identifier]->telephoneContact = $myrow['telephoneContact'];
	      
	      $TieToOrderNumber = $myrow['requisitionno'];
	      $_POST['Select']=$myrow['supplierno'];
              $_SESSION['PO'.$identifier]->DelAdd1 = $myrow['deladd1'];
              $_SESSION['PO'.$identifier]->DelAdd2 = $myrow['deladd2'];
              $_SESSION['PO'.$identifier]->DelAdd3 = $myrow['deladd3'];
              $_SESSION['PO'.$identifier]->DelAdd4 = $myrow['deladd4'];
              $_SESSION['PO'.$identifier]->DelAdd5 = $myrow['deladd5'];
              $_SESSION['PO'.$identifier]->DelAdd6 = $myrow['deladd6'];
              $_SESSION['PO'.$identifier]->Managed = $myrow['managed'];
	            $_SESSION['PO'.$identifier]->version = $myrow['version'];
	            $_SESSION['PO'.$identifier]->OrderNo2 = $myrow['realorderno'];

                if($myrow['status'] == 'Authorised'){
                    $statusactual = 'Authorised'; 
                    $statusmodf = 'Pending'; 
                }else{
                    $statusmodf = $myrow['status'];
                }
		$_SESSION['PO'.$identifier]->Stat = $statusmodf;
		$_SESSION['PO'.$identifier]->StatComments = $myrow['stat_comment'];
		$_SESSION['PO'.$identifier]->deliverydate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['ExistingPurchOrder'] = $_SESSION['PO'.$identifier]->OrderNo;
			
/*now populate the line PO array with the purchase order details records */

              $LineItemsSQL = "SELECT purchorderdetails.orderlineno_, podetailitem, itemcode,
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
				purchorderdetails.narrative,
				purchorderdetails.justification,
              	purchorderdetails.estimated_cost,
              	purchorderdetails.nw,
              	purchorderdetails.refundpercent,
              	purchorderdetails.womasterid,
              	purchorderdetails.wocomponent,
                purchorderdetails.clavepresupuestal,
                stockmaster.mbflag
				FROM purchorderdetails 
				LEFT JOIN stockmaster ON purchorderdetails.itemcode=stockmaster.stockid
				LEFT JOIN chartmaster ON purchorderdetails.glcode=chartmaster.accountcode
				WHERE purchorderdetails.completed=0
				AND purchorderdetails.orderno= '" . $_GET['ModifyOrderNumber'] . "'
				AND purchorderdetails.status=2
				AND purchorderdetails.quantityord <> 0
				ORDER BY purchorderdetails.orderlineno_";

		//echo "<br><pre>".$LineItemsSQL;
	      $ErrMsg =  _('The lines on the purchase order cannot be retrieved because');
	      $DbgMsg =  _('The SQL statement that was used to retrieve the purchase order lines was');
              //
              $LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);
              $totalcompra = 0;
              $GRN = GetNextTransNo(556, $db);//556 Nuevo movimiento para cancelacion de autorizacion de orden de compra

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
					 
					 
					 if($myrow['unitprice']==0 ){
						$uomsql='SELECT price 
						       FROM purchdata
						       WHERE supplierno="'.$_SESSION['PO'.$identifier]->SupplierID.'"
						       AND stockid="'.$StockID.'"';
						//echo '<pre><br>'.$uomsql;
					       $uomresult=DB_query($uomsql, $db);
					       if (DB_num_rows($uomresult)>0) {
						       $uomrow=DB_fetch_array($uomresult);
						       if (strlen($uomrow['price'])>0 and $uomrow['price']>0) {
							       $myrow['unitprice']=$uomrow['price'];
						       } else {
							       $myrow['unitprice']=$myrow['unitprice'];
						       }
					       } else {
						       $myrow['unitprice']=$myrow['unitprice'];
					       }
					}

					$_SESSION['PO'.$identifier]->add_to_order($myrow['orderlineno_'],
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
							$myrow['nw'],0,0,0,0,
							$myrow['narrative'],
							$myrow['justification'],
							'',
							$myrow['refundpercent'],
							0,
							0,
							$myrow['estimated_cost'],
							$myrow['womasterid'],
							$myrow['wocomponent'],
                            $myrow['clavepresupuestal'],
                            '',
                            $myrow['mbflag']
							);
                        // echo'<br>clavepresupuestal'.$myrow['clavepresupuestal'];
                        $_SESSION['PO'.$identifier]->LineItems[$myrow['orderlineno_']]->PODetailRec = $myrow['podetailitem'];
	                $_SESSION['PO'.$identifier]->LineItems[$myrow['orderlineno_']]->StandardCost = $myrow['stdcostunit'];
                        
                      if($rate == ""){
                          $rate = 1;
                      }
                      //buscar margen automatico para costo en categoria de inventario
                      $qry = "SELECT margenautcost, 
                                    taxauthrates.taxrate
                              FROM stockcategory
                              INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                              INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
                              WHERE stockmaster.stockid = '".$StockID."'";
                      $rsm = DB_query($qry, $db);
                      $rowm = DB_fetch_array($rsm);
                      $margenautcost = $rowm['margenautcost'] / 100;
                      $porcentaje_impuesto= 1 + $rowm['taxrate'];
                      $price += ($myrow['unitprice'] * $margenautcost);
                      $LocalCurrencyPrice = ($myrow['unitprice'] / $rate);
                      if ($StockID != '') {
                        $avgcost = 0;
                        $avgcost = $LocalCurrencyPrice;
                        $avgcost = $avgcost - ($avgcost * ($myrow['discountpercent1'] / 100));
                        $avgcost = $avgcost - ($avgcost * ($myrow['discountpercent2'] / 100));
                        $avgcost = $avgcost - ($avgcost * ($myrow['discountpercent3'] / 100));
                     }
                     
                    $purchdatasql='SELECT conversionfactor,price
                                    FROM purchdata
                                    WHERE purchdata.supplierno = "'.$supplierid .'"
                                    AND purchdata.stockid="'.$StockID.'"';
                    $rsm = DB_query($purchdatasql, $db);
                    $rowm = DB_fetch_array($rsm);
                    $factordeConversion = 1;
                    if (is_numeric($rowm['conversionfactor'])) {
                        $factordeConversion = $rowm['conversionfactor'];
                    }
                    $CurrentStandardCost = $avgcost;
                    $CurrentStandardCost = $CurrentStandardCost / $factordeConversion;
                    $totalcompra= ($CurrentStandardCost * $myrow['quantityord']) * $porcentaje_impuesto;
                    
                    if($statusactual == 'Authorised'){
                            //date('d/m/Y')
                        
                            $Date_ArrayRe = explode('/',$fechaorigen);
                            $feacha_origen = $Date_ArrayRe[2].'/'.$Date_ArrayRe[1].'/'.$Date_ArrayRe[0];
                            
                           $totalcompra = truncateFloat($totalcompra,$digitos);
                           $PeriodNo = GetPeriod($feacha_origen, $db, $tagref);
                           $resultado= GeneraMovimientoContablePresupuesto(556, "COMPROMETIDO", "POREJERCER", $GRN, $PeriodNo,
                                    $totalcompra, $tagref, $fechaorigen, $myrow['clavepresupuestal'], $_GET['ModifyOrderNumber'], $db);
                           $SQLACTUA = "UPDATE purchorders
                                       SET status = '".$statusmodf."'
                                       WHERE purchorders.orderno= '".$_GET['ModifyOrderNumber']."'";
                           $resultactua = db_query($SQLACTUA,$db,$ErrMsg,$DbgMsg);
                    } 
                        /*Needed for receiving goods and GL interface *///
             } /* line PO from purchase order details */
             //
             
             
      } //end is there were lines on the order
   } // end if there was a header for the order//
?>