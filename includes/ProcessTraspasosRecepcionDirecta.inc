<?php

//$Result = DB_Txn_Begin($db);
$Total=$_POST['Total'];
$PeriodNo= //GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
$unidadVenta=$unidaddenegocio;
$almacenVenta=$_SESSION['PO']->Location;
// almacen de venta para ordenes directas
$SQL=" SELECT tags.tagref, locations.loccode,tags.tagsupplier
       FROM locations INNER JOIN tags ON tags.tagref=locations.tagref
       WHERE locations.temploc=4 AND tags.areacode='". $areacodeloc."'";
     //  echo '<br>'.$SQL;
$resultalmacen=DB_query($SQL, $db);
if (DB_num_rows($resultalmacen)>0) {
	$myrow=DB_fetch_array($resultalmacen);
	$unidaddenegocio=$myrow[0];
	$unidadCompra=$myrow[0];
	$almacenCompra=$myrow[1];
	//echo '<br>almacen de config:'.$almacenCompra;
	$ProveedorCompra=$myrow[2];
}else{
	echo 'no entra';
	
}
//compara si el legalid= al legal de recepcion hace la fatura de compra si no no lo hace

//echo '<br>almacen compra:'.$almacenCompra;
	$legalidEnvia=ExtractLegalid($unidadCompra,$db);
	$legalIDRecibe=ExtractLegalid($unidadVenta,$db);
	if ($legalidEnvia!=$legalIDRecibe){
		//echo 'almacen compra:'.$almacenCompra;
		$_SESSION['PO']->Location=$almacenCompra;

		foreach ($_SESSION['PO']->LineItems as $OrderLine) {
		//consulta el nuevo codigo de acuerdo a la categoria de inventario del producto
		if ($OrderLine->ReceiveQty !=0 AND $OrderLine->ReceiveQty!='' AND isset($OrderLine->ReceiveQty)) {

			$LocalCurrencyPrice = ($OrderLine->Price / $_SESSION['PO']->ExRate);
			
			
/*Update SalesOrderDetails for the new quantity received and the standard cost used for postings to GL and recorded in the stock movements for FIFO/LIFO stocks valuations*/

			if ($OrderLine->StockID!='') { /*Its a stock item line*/
				$avgcost = 0;
				$avgcost = $LocalCurrencyPrice;
				$avgcost = $avgcost - ($avgcost*($OrderLine->Desc1/100));
				$avgcost = $avgcost - ($avgcost*($OrderLine->Desc2/100));
				$avgcost = $avgcost - ($avgcost*($OrderLine->Desc3/100));
				
				
				if ($OrderLine->QtyReceived==0){ //its the first receipt against this line
					$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
				}
				$CurrentStandardCost = $avgcost;
				$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $CurrentStandardCost;
			} elseif ($OrderLine->QtyReceived==0 AND $OrderLine->StockID=="") {
				$avgcost = $LocalCurrencyPrice;
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc1/100));
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc2/100));
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc3/100));
				$CurrentStandardCost = $avgcost;
				/*Its a nominal item being received */
				/*Need to record the value of the order per unit in the standard cost field to ensure GRN account entries clear */
				$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
			}

			if ($OrderLine->StockID=='') { /*Its a NOMINAL item line */
				$avgcost = $LocalCurrencyPrice;
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc1/100));
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc2/100));
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc3/100));
				$CurrentStandardCost = $avgcost;
				$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
			}
			
/*Now the SQL to do the update to the PurchOrderDetails */
			if ($OrderLine->ReceiveQty >= ($OrderLine->Quantity - $OrderLine->QtyReceived)){
				$SQL = "UPDATE purchorderdetails SET
							quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
							stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							completed=1
					WHERE podetailitem = " . $OrderLine->PODetailRec;
					
			} else {
				$SQL = "UPDATE purchorderdetails SET
							quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
							stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							completed=0
					WHERE podetailitem = " . $OrderLine->PODetailRec;
			}
			//echo "<br>2.-" . $SQL;
			$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El detalle de la orden de compra no se realizo');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
			
			if ($OrderLine->StockID !=''){ /*Its a stock item so use the standard cost for the journals */
				$UnitCost = $CurrentStandardCost;
			} else {  /*otherwise its a nominal PO item so use the purchase cost converted to local currency */
				$UnitCost = $OrderLine->Price / $_SESSION['PO']->ExRate ;
				
				$avgcost = $UnitCost;
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc1/100));
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc2/100));
				$avgcost = $avgcost - ($avgcost*($LnItm->Desc3/100));
				
				$UnitCost = $avgcost;
			}
			/*Need to insert a GRN item */
			

			if ($OrderLine->StockID!=''){ /* if the order line is in fact a stock item */

/* Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts */

/* Need to get the current location quantity will need it later for the stock movement */
				$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['PO']->Location . "'";
				$result = DB_query($SQL, $db);
				if (DB_num_rows($result)==1){
					$LocQtyRow = DB_fetch_row($result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}
				$sql='SELECT conversionfactor
					FROM purchdata
					WHERE supplierno="'.$_SESSION['PO']->SupplierID.'"
					AND stockid="'.$OrderLine->StockID.'"';
				$result=DB_query($sql, $db);
				if (DB_num_rows($result)>0) {
					$myrow=DB_fetch_array($result);
					if (($myrow['conversionfactor'] == 0) or ($myrow['conversionfactor'] == '')){
						$conversionfactor=1;
					}else{
						$conversionfactor=$myrow['conversionfactor'];	
					}
				} else {
					$conversionfactor=1;
				}
				$OrderLine->ReceiveQty=$OrderLine->ReceiveQty*$conversionfactor;
				$SQL = "UPDATE locstock
					SET quantity = locstock.quantity + " . $OrderLine->ReceiveQty . "
					WHERE locstock.stockid = '" . $OrderLine->StockID . "'
					AND loccode = '" . $_SESSION['PO']->Location . "'";
					//echo "<br>4.-" . $SQL;
				$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la actualizacion en almacen');
				$DbgMsg =  _('El SQL utiliado es');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				/* If its a stock item still .... Insert stock movements - with unit cost */
				if ($OrderLine->Controlled !=1){
				/*************************************************************/	
				// Aqui va seccion de requisiciones
				/*************************************************************/
					include('includes/ProcessRequisitionsDirect.inc');
				/*************************************************************/	
				
					$SQL = "INSERT INTO stockmoves (stockid,
									type,
									transno,
									loccode,
									trandate,
									price,
									prd,
									reference,
									qty,
									standardcost,
									newqoh,
									discountpercent,
									discountpercent1,
									discountpercent2,
									tagref,
									avgcost
									)
						VALUES ('" . $OrderLine->StockID . "',
							25,
							" . $GRN . ", '" . $_SESSION['PO']->Location . "',
							'" . $_POST['DefaultReceivedDate'] . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - " .$_SESSION['PO']->OrderNo . "',
							" . $OrderLine->ReceiveQty . ",
							" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
							" . ($QtyOnHandPrior + $OrderLine->ReceiveQty) . ",
							" . $OrderLine->Desc1 . ",
							" . $OrderLine->Desc2 . ",
							" . $OrderLine->Desc3 . ",
							" . $unidaddenegocio . ",
							'" . $EstimatedAvgCost . "')";
					//echo "<br>5.-" . $SQL;
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realice el movimiento de inventario');
					$DbgMsg =  _('El SQL utilizado es');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					/*Get the ID of the StockMove... */
					$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
					//agregue la recepcion para cada partida
					if ($OrderLine->Controlled ==0){
					$SQL = "INSERT INTO grns (grnbatch,
							podetailitem,
							itemcode,
							itemdescription,
							deliverydate,
							qtyrecd,
							supplierid,
							stdcostunit)
							VALUES (" . $GRN . ",
								" . $OrderLine->PODetailRec . ",
								'" . $OrderLine->StockID . "',
								'" . $OrderLine->ItemDescription . "',
								'" . $_POST['DefaultReceivedDate'] . "',
								" . $OrderLine->ReceiveQty . ",
								'" . $_SESSION['PO']->SupplierID . "',
								" . $CurrentStandardCost . ')';
						$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la transaccion');
						$DbgMsg =  _('El SQL utilizado es');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
					}
					
					
					
					
				}
				/* Do the Controlled Item INSERTS HERE */
				if ($OrderLine->Controlled ==1){
					foreach($OrderLine->SerialItems as $Item){
						$GRN = GetNextTransNo(25, $db);
						$SQL = "INSERT INTO grns (grnbatch,
							podetailitem,
							itemcode,
							itemdescription,
							deliverydate,
							qtyrecd,
							supplierid,
							stdcostunit)
							VALUES (" . $GRN . ",
								" . $OrderLine->PODetailRec . ",
								'" . $OrderLine->StockID . "',
								'" . $OrderLine->ItemDescription . "',
								'" . $_POST['DefaultReceivedDate'] . "',
								" . $Item->BundleQty . ",
								'" . $_SESSION['PO']->SupplierID . "',
								" . $CurrentStandardCost . ')';
						$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la transaccion');
						$DbgMsg =  _('El SQL utilizado es');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						
						$SQL = "INSERT INTO stockmoves (stockid,
									type,
									transno,
									loccode,
									trandate,
									price,
									prd,
									reference,
									qty,
									standardcost,
									newqoh,
									discountpercent,
									discountpercent1,
									discountpercent2,
									tagref,
									narrative,
									avgcost
									)
						VALUES ('" . $OrderLine->StockID . "',
							25,
							" . $GRN . ", '" . $_SESSION['PO']->Location . "',
							'" . $_POST['DefaultReceivedDate'] . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - " .$_SESSION['PO']->OrderNo . "',
							" . $Item->BundleQty . ",
							" . $CurrentStandardCost . ",
							" . ($QtyOnHandPrior + $Item->BundleQty ) . ",
							" . $OrderLine->Desc1 . ",
							" . $OrderLine->Desc2 . ",
							" . $OrderLine->Desc3 . ",
							" . $unidaddenegocio . ",
							'" . $Item->BundleRef . "',
							'" . $CurrentStandardCost . "')";
						//echo "<br>5.-" . $SQL;
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realice el movimiento de inventario');
						$DbgMsg =  _('El SQL utilizado es');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						/*Get the ID of the StockMove... */
						$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
						
						/* we know that StockItems return an array of SerialItem (s)
						 We need to add the StockSerialItem record and
						 The StockSerialMoves as well */
						//need to test if the controlled item exists first already
							$SQL = "SELECT COUNT(*) FROM stockserialitems
									WHERE stockid='" . $OrderLine->StockID . "'
									AND loccode = '" . $_SESSION['PO']->Location . "'
									AND serialno = '" . $Item->BundleRef . "'";
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch or lot stock item already exists because');
							$DbgMsg =  _('The following SQL to test for an already existing controlled but not serialised stock item was used');
							$result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							$AlreadyExistsRow = DB_fetch_row($result);
							if (trim($Item->BundleRef) != ""){
								if ($AlreadyExistsRow[0]>0){
									if ($OrderLine->Serialised == 1) {
										$SQL = 'UPDATE stockserialitems SET quantity = ' . $Item->BundleQty . ' , standardcost=' .$CurrentStandardCost . ' ';
									} else {
										$SQL = 'UPDATE stockserialitems SET quantity = quantity + ' . $Item->BundleQty . ' , standardcost=' .$CurrentStandardCost . ' ';
									}
									$SQL .= "WHERE stockid='" . $OrderLine->StockID . "'
											 AND loccode = '" . $_SESSION['PO']->Location . "'
											 AND serialno = '" . $Item->BundleRef . "'";
								} else {
									$SQL = "INSERT INTO stockserialitems (stockid,
												loccode,
												serialno,
												qualitytext,
												quantity,
												standardcost
												)
											VALUES ('" . $OrderLine->StockID . "',
												'" . $_SESSION['PO']->Location . "',
												'" . $Item->BundleRef . "',
												'',
												" . $Item->BundleQty . ",
												" . $CurrentStandardCost . ")";
								}
								$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La actualizacion de los numero de serie no se realizo');
								$DbgMsg =  _('El SQL utilizado es');
								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							//assetmanager								
							/** end of handle stockserialitems records */
							/** now insert the serial stock movement **/
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty,
											standardcost,
											orderno,
											orderdetailno
											)
									VALUES (" . $StkMoveNo . ",
										'" . $OrderLine->StockID . "',
										'" . $Item->BundleRef . "',
										" . $Item->BundleQty . ",
										" . $CurrentStandardCost .",
										" . (int) $_SESSION['PO']->OrderNo .",
										" . $OrderLine->PODetailRec .")";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El movimiento de los numeros de serie no se realizo');
							$DbgMsg = _('El SQL utilizado es');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}//non blank BundleRef
						
					
					} //end foreach
				}
			} /*end of its a stock item - updates to locations and insert movements*/
			//actualiza cuenta de inventario
			
		} /*Quantity received is != 0 */
		$avgcosttotal=$avgcosttotal+$avgcost;
	} /*end of OrderLine loop */
	$completedsql='SELECT SUM(completed) as completedlines,
						COUNT(podetailitem) as alllines
					FROM purchorderdetails 
					WHERE orderno='.$_SESSION['PO']->OrderNo;
	$completedresult=DB_query($completedsql,$db);
	$mycompletedrow=DB_fetch_array($completedresult);
	$status=$mycompletedrow['alllines']-$mycompletedrow['completedlines'];
	if ($status==0) {
		$sql='SELECT stat_comment FROM purchorders WHERE orderno='.$_SESSION['PO']->OrderNo;
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$comment=$myrow['stat_comment'];
		$date = date($_SESSION['DefaultDateFormat']);
		$StatusComment=$date.' - Order Completed'.'<br>'.$comment;
		$sql="UPDATE purchorders 
				SET status='"._('Completed')."',
				stat_comment='".$StatusComment."'
				WHERE orderno=".$_SESSION['PO']->OrderNo;
		//$result=DB_query($sql,$db);
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		//echo "<br>8.-" . $SQL;
	}
	// aqui va la seccion de compara venta entre razones sociales
	include('ProcessCompraVentaTransferRecDirect.inc');
	$Result = DB_Txn_Commit($db);
	$PONo = $_SESSION['PO']->OrderNo;
	echo '<br><a href=PDFGrn.php?GRNNo='.$GRN .'&PONo='.$PONo.' target="_blank">'. _('Imprimir comprobante de productos recibidos').'</a><br><br>';
	include('includes/footer.inc');
	unset($_SESSION['PO']->LineItems);
	unset($_SESSION['PO']);
	unset($_POST['ProcessGoodsReceived']);
	exit;
}
	
?>