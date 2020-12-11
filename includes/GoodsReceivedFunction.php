<?php
function receivePurchOrder($orderno, &$db) {
	
	//Valores para truncar datos
	$digitos;
	if(isset($_SESSION['TruncarDigitos']))
	{
		$digitos=$_SESSION['TruncarDigitos'];
	}else{
		$digitos=4;
	}
	
	include_once realpath(dirname(__FILE__)) . '/CostHandle.inc';
	
	$_GET['ModifyOrderNumber'] = $orderno;
	include_once realpath(dirname(__FILE__)) . '/PO_ReadInOrderReceived.inc';
	
	$podetails = array();
	$sql = "SELECT podetailitem, (quantityord - quantityrecd) AS qty
		FROM purchorderdetails WHERE orderno = $orderno";
	
	$result = DB_query($sql, $db);
	
	while ($row = DB_fetch_array($result)) {
		$podetails[$row['podetailitem']] = $row['qty'];
	}
	
	foreach ($_SESSION['PO']->LineItems as &$Line) {
            foreach ($podetails as $id => $qty) {
                if ($Line->PODetailRec == $id) {
                    if (!is_numeric($qty)) {
                        $qty = 0;
                    }
                    $Line->ReceiveQty = $qty;
                    break;
                }
            }		
	}
	
	$SomethingReceived = 0;
	if (count($_SESSION['PO']->LineItems) > 0) {
		foreach ($_SESSION['PO']->LineItems as $OrderLine) {
			if ($OrderLine->ReceiveQty > 0){
				$SomethingReceived = 1;
			}
		}
	}
	
	$DeliveryQuantityTooLarge = 0;
	$NegativesFound = false;
	$InputError = false;
	
	if (count($_SESSION['PO']->LineItems) > 0) {
	
		foreach ($_SESSION['PO']->LineItems as $OrderLine) {
	
			if ($OrderLine->ReceiveQty + $OrderLine->QtyReceived > $OrderLine->Quantity * (1 + ($_SESSION['OverReceiveProportion'] / 100))) {
				$DeliveryQuantityTooLarge = 1;
				$InputError = true;
			}
			if ($OrderLine->ReceiveQty < 0 AND $_SESSION['ProhibitNegativeStock'] == 1) {
	
				$SQL = "SELECT locstock.quantity FROM
					locstock WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['PO']->Location . "'";
				
				$CheckNegResult = DB_query($SQL, $db);
				$CheckNegRow = DB_fetch_row($CheckNegResult);
				if ($CheckNegRow[0] + $OrderLine->ReceiveQty < 0) {
					$NegativesFound = true;
				}
			}
		}
	}
	
	if ($SomethingReceived == 0) {
		
		return false;
		
	} else if ($NegativesFound) {
		
		return false;
		
	} else if ($DeliveryQuantityTooLarge == 1) {
		
		return false;
		
	} else if ($SomethingReceived == 1 AND $InputError == false) {

		if ($_SESSION['CompanyRecord'] == 0) {
			// The company information and preferences could not be retrieved
			return false;
		}
		
		$SQL = 'SELECT itemcode, glcode, quantityord, quantityrecd, 
			qtyinvoiced, shiptref, jobref FROM purchorderdetails
			WHERE orderno=' . (int) $_SESSION['PO']->OrderNo . '
			AND completed = 0
			ORDER BY podetailitem';
		
		$Result = DB_query($SQL, $db);
		$Changes = 0;
		$LineNo = 1;
		
		while ($myrow = DB_fetch_array($Result)) 
		{
			if ($_SESSION['PO']->LineItems[$LineNo]->GLCode != $myrow['glcode'] OR
				$_SESSION['PO']->LineItems[$LineNo]->ShiptRef != $myrow['shiptref'] OR
				$_SESSION['PO']->LineItems[$LineNo]->JobRef != $myrow['jobref'] OR
				$_SESSION['PO']->LineItems[$LineNo]->QtyInv != $myrow['qtyinvoiced'] OR
				$_SESSION['PO']->LineItems[$LineNo]->StockID != $myrow['itemcode'] OR
				$_SESSION['PO']->LineItems[$LineNo]->Quantity != $myrow['quantityord'] OR
				$_SESSION['PO']->LineItems[$LineNo]->QtyReceived != $myrow['quantityrecd']) {
				
				// This order has been changed or invoiced since this delivery was started to be actioned
				return false;
			}
			$LineNo++;
		}
		
		DB_free_result($Result);
		
		$QuantityControlled = true;
		$totalserie = 0;
		$recibidas = 0;
		foreach ($_SESSION['PO']->LineItems as $OrderLine) {
			if ($OrderLine->Controlled ==1 OR $OrderLine->Serialised) {
				if (!isset($OrderLine->SerialItems)) {
					$QuantityControlled = false;
				} else {
					$totalserie = 0;
					$recibidas = 0;
					foreach ($OrderLine->SerialItems as $Item) {
						if (count($Item->BundleQty) > 0) {
							$totalserie = $totalserie + $Item->BundleQty;
						} else {
							$QuantityControlled = false;
							break;
						}
					}
					$recibidas = $OrderLine->ReceiveQty;
					if ($totalserie != $recibidas) {
						$QuantityControlled = false;
						break;
					}
				}
			}
		}
		
		if ($QuantityControlled == false) {
			// No ha ingresado los numeros de serie
			return false;
		}
		
		$Result = DB_Txn_Begin($db);

		$unidaddenegocio = 0;
		$typelocation = 0;
		$areacode = 0;
		$legalid = 0;
		
		$sql = 'SELECT locations.tagref,locations.temploc,locations.areacod,tags.legalid
		  FROM locations INNER JOIN tags ON locations.tagref = tags.tagref
		  WHERE loccode="' . $_SESSION['PO']->Location . '"';
		$result = DB_query($sql, $db);
		
		if (DB_num_rows($result) > 0) {
			$myrow = DB_fetch_array($result);
			$unidaddenegocio = $myrow['tagref'];
			$typelocation = $myrow['temploc'];
			$areacodeloc = $myrow['areacod'];
			$legalid = $myrow['legalid'];
		}
		
		$tipodefacturacion = 25;
		$PeriodNo = GetPeriod(date('d-m-Y'), $db, $unidaddenegocio);
		$_POST['DefaultReceivedDate'] = date('Y-m-d');
		$GRN = GetNextTransNo($tipodefacturacion, $db);
		$totalcompra= 0;
		
		if ($typelocation == 3) {
			include_once realpath(dirname(__FILE__)) . '/ProcessTraspasosRecepcionDirecta.inc';
			$_SESSION['PO']->Location = $almacenCompra;
		}
		
		foreach ($_SESSION['PO']->LineItems as $OrderLine) 
		{
			if ($OrderLine->ReceiveQty != 0 AND $OrderLine->ReceiveQty != '' AND isset($OrderLine->ReceiveQty)) {
						
				if ($_SESSION['PO']->ExRate == '') {
					$_SESSION['PO']->ExRate = 1;
				}

				//buscar margen automatico para costo en categoria de inventario
				$qry = "SELECT margenautcost, taxauthrates.taxrate
					FROM stockcategory
					INNER JOIN stockmaster ON stockcategory.categoryid = stockmaster.categoryid
                                        INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid
					WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'";
				
				$rsm = DB_query($qry, $db);
				$rowm = DB_fetch_array($rsm);
				$margenautcost = $rowm['margenautcost'] / 100;
                                $porcentaje_impuesto= 1 + $rowm['taxrate'];
					
				$OrderLine->Price += ($OrderLine->Price * $margenautcost);
				$LocalCurrencyPrice = ($OrderLine->Price / $_SESSION['PO']->ExRate);
					
				if ($OrderLine->StockID != '') { 
			
					$avgcost = 0;
					$avgcost = $LocalCurrencyPrice;
					$avgcost = $avgcost - ($avgcost * ($OrderLine->Desc1 / 100));
					$avgcost = $avgcost - ($avgcost * ($OrderLine->Desc2 / 100));
					$avgcost = $avgcost - ($avgcost * ($OrderLine->Desc3 / 100));
			
					$purchdatasql = 'SELECT conversionfactor,price
						FROM purchdata
						WHERE purchdata.supplierno = "' . $_SESSION['PO']->SupplierID . '"
						AND purchdata.stockid="' . $OrderLine->StockID . '"';
						
					$rsm = DB_query($purchdatasql, $db);
					$rowm = DB_fetch_array($rsm);
						
					$factordeConversion = 1;
					if (is_numeric($rowm['conversionfactor'])) {
						$factordeConversion = $rowm['conversionfactor'];
					}
			
					if ($OrderLine->StockID != '') {
						$type = 30;
						$ref = $_SESSION['PO']->RequisitionNo;
						if (strlen($_SESSION['PO']->RequisitionNo) == 0) {
							$type = 0;
							$ref = 0;
						}
						
						$resf = Entradas($legalid, $_SESSION['PO']->Location, $OrderLine->StockID, $avgcost / $factordeConversion, $OrderLine->ReceiveQty * $factordeConversion, $type, $ref, $db);
						$EstimatedAvgCost = getAVGCost($legalid, $OrderLine->StockID, $db);	
					}
					
					if (strlen($_SESSION['PO']->RequisitionNo) == 0) {
						
						// CALCULA COSTO PROMEDIO A NIVEL RAZON SOCIAL
						$unitsXLegal=StockUnitsXLegal($OrderLine->StockID, $unidaddenegocio, $db);
						$estavgcostXlegal=StockAvgcostXLegal($OrderLine->StockID, $unidaddenegocio, $db);
						$lastcostant=StockLastCostXLegal($OrderLine->StockID, $unidaddenegocio, $db);
						$EstimatedAvgCostXlegal=EstimatedAvgCostXLegal($OrderLine->StockID, $unidaddenegocio, $unitsXLegal, $estavgcostXlegal, $OrderLine->ReceiveQty * $factordeConversion, $avgcost / $factordeConversion, $lastcostant, 20, $db);
						
						// CALCULAR COSTO PROMEDIO A NIVEL UNIDAD DE NEGOCIO
						$estavgcostXtag=StockAvgcost($OrderLine->StockID, $unidaddenegocio, $db);
						$lastcostantXtag=StockLastCost($OrderLine->StockID, $unidaddenegocio, $db);
						$unitscostXtag=StockUnitsXTag($OrderLine->StockID, $unidaddenegocio, $db);
						$EstimatedAvgCostXtag=EstimatedAvgCost($OrderLine->StockID, $unidaddenegocio, $unitscostXtag, $estavgcostXtag, $OrderLine->ReceiveQty * $factordeConversion, $avgcost / $factordeConversion, $lastcostantXtag, 20, $db);
					}
						
					if ($OrderLine->QtyReceived == 0) { //its the first receipt against this line
						$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
					}
					$CurrentStandardCost = $avgcost;
					$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $CurrentStandardCost;
			
				} elseif ($OrderLine->QtyReceived == 0 AND $OrderLine->StockID == "") {
			
					$avgcost = $LocalCurrencyPrice;
					$avgcost = $avgcost - ($avgcost * ($LnItm->Desc1 / 100));
					$avgcost = $avgcost - ($avgcost * ($LnItm->Desc2 / 100));
					$avgcost = $avgcost - ($avgcost * ($LnItm->Desc3 / 100));
			
					$CurrentStandardCost = $avgcost;
					$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
			
				}
			
				if ($OrderLine->StockID == '') { /*Its a NOMINAL item line */
			
					$avgcost = $LocalCurrencyPrice;
					$avgcost = $avgcost - ($avgcost * ($LnItm->Desc1 / 100));
					$avgcost = $avgcost - ($avgcost * ($LnItm->Desc2 / 100));
					$avgcost = $avgcost - ($avgcost * ($LnItm->Desc3 / 100));
			
					$CurrentStandardCost = $avgcost;
					$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $avgcost;
				}
						
				/*Now the SQL to do the update to the PurchOrderDetails */
				if ($OrderLine->ReceiveQty >= ($OrderLine->Quantity - $OrderLine->QtyReceived)) {
					$SQL = "UPDATE purchorderdetails SET
						quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
						stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
						completed = 1
						WHERE podetailitem = " . $OrderLine->PODetailRec;
									
				} else {
					$SQL = "UPDATE purchorderdetails SET
						quantityrecd = quantityrecd + " . $OrderLine->ReceiveQty . ",
						stdcostunit=" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
						completed = 0
						WHERE podetailitem = " . $OrderLine->PODetailRec;
					}
					
					$Result = DB_query($SQL, $db);
		
					if ($OrderLine->StockID != '') { 
						$UnitCost = $CurrentStandardCost;
					} else {
						$UnitCost = $LocalCurrencyPrice;
						$avgcost = $UnitCost;
						$avgcost = $avgcost - ($avgcost * ($LnItm->Desc1 / 100));
						$avgcost = $avgcost - ($avgcost * ($LnItm->Desc2 / 100));
						$avgcost = $avgcost - ($avgcost * ($LnItm->Desc3 / 100));
						$UnitCost = $avgcost;
					}
							
					$campouno = '';
					$campodos = '';
								
					/*Need to insert a GRN item */
					if ($OrderLine->Controlled != 1) {
						$CurrentStandardCostCompra = $OrderLine->Price;
						$CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc1/100));
						$CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc2/100));
						$CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc3/100));
						
						$SQL = "INSERT INTO grns (
								grnbatch,
								podetailitem,
								itemcode,
								itemdescription,
								deliverydate,
								qtyrecd,
								supplierid,
								rategr,
								textgr,
								textgr1,
								stdcostunit
							) VALUES (
								" . $GRN . ",
								" . $OrderLine->PODetailRec . ",
								'" . $OrderLine->StockID . "',
								'" . DB_escape_string($OrderLine->ItemDescription) . "',
								'" . $_POST['DefaultReceivedDate'] . "',
								" . $OrderLine->ReceiveQty . ",
								'" . $_SESSION['PO']->SupplierID . "',
								'" . $_SESSION['PO']->ExRate . "',
								'" . DB_escape_string($campouno) . "',
								'" . DB_escape_string($campodos) . "',
								" . $CurrentStandardCostCompra . '
							)';
											
											
						$Result = DB_query($SQL, $db);			
					}
			
					if ($OrderLine->StockID != '') { /* if the order line is in fact a stock item */
			
						$SQL = "SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $OrderLine->StockID . "'
							AND loccode= '" . $_SESSION['PO']->Location . "'";
						
						$result = DB_query($SQL, $db);
						if (DB_num_rows($result) == 1) {
							$LocQtyRow = DB_fetch_row($result);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
							/*There must actually be some error this should never happen */
							$QtyOnHandPrior = 0;
						}
							
						$sql = 'SELECT conversionfactor FROM purchdata
							WHERE supplierno="' . $_SESSION['PO']->SupplierID . '"
							AND stockid="' . $OrderLine->StockID . '"';
						$result=DB_query($sql, $db);
						
						if (DB_num_rows($result)>0) {
							$myrow = DB_fetch_array($result);
							if (($myrow['conversionfactor'] == 0) or ($myrow['conversionfactor'] == '')){
								$conversionfactor = 1;
							} else {
								$conversionfactor = $myrow['conversionfactor'];
							}
						} else {
							$conversionfactor = 1;
						}
							
						$OrderLine->ReceiveQty = $OrderLine->ReceiveQty * $conversionfactor;
						$LocalCurrencyPrice = $LocalCurrencyPrice / $conversionfactor;
						$_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost = $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost / $conversionfactor;
						$UnitCost = $UnitCost/$conversionfactor;
						$CurrentStandardCost = $CurrentStandardCost / $conversionfactor;
						
						$SQL = "UPDATE locstock
							SET quantity = locstock.quantity + " . $OrderLine->ReceiveQty . "
							WHERE locstock.stockid = '" . $OrderLine->StockID . "'
							AND loccode = '" . $_SESSION['PO']->Location . "'";
					
						$Result = DB_query($SQL, $db);
						/* If its a stock item still .... Insert stock movements - with unit cost */
						$refSalesOrder = "";
						if (empty($_SESSION['PO']->RequisitionNo) == false) {
							$refSalesOrder = " - Pedido Venta: " . $_SESSION['PO']->RequisitionNo;
						}
		
						if ($OrderLine->Controlled != 1) {
							
							include_once realpath(dirname(__FILE__)) . '/ProcessTraspasosCompras.inc';
		
							$SQL = "INSERT INTO stockmoves (
									stockid,
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
								) VALUES ('" . $OrderLine->StockID . "',
									$tipodefacturacion,
									" . $GRN . ", '" . $_SESSION['PO']->Location . "',
									'" . $_POST['DefaultReceivedDate'] . "',
									'" . $LocalCurrencyPrice . "',
									" . $PeriodNo . ",
									'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - " .$_SESSION['PO']->OrderNo . $refSalesOrder . "',
									" . $OrderLine->ReceiveQty . ",
									" . $_SESSION['PO']->LineItems[$OrderLine->LineNo]->StandardCost . ",
									" . ($QtyOnHandPrior + $OrderLine->ReceiveQty) . ",
									" . $OrderLine->Desc1 . ",
									" . $OrderLine->Desc2 . ",
									" . $OrderLine->Desc3 . ",
									" . $unidaddenegocio . ",
									'" . $EstimatedAvgCost . "'
								)";
																	
							$Result = DB_query($SQL, $db);
							/*Get the ID of the StockMove... */
							$StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
						}
							
						/* Do the Controlled Item INSERTS HERE */
						if ($OrderLine->Controlled == 1) {
							
							foreach ($OrderLine->SerialItems as $Item) {
								
								$GRN = GetNextTransNo($tipodefacturacion, $db);
								$CurrentStandardCostCompra = ($OrderLine->Price / $_SESSION['PO']->ExRate);
								$CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc1/100));
								$CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc2/100));
								$CurrentStandardCostCompra = $CurrentStandardCostCompra - ($CurrentStandardCostCompra*($OrderLine->Desc3/100));
								$CurrentStandardCostCompraControl = ($OrderLine->Price);
								$CurrentStandardCostCompraControl = $CurrentStandardCostCompraControl - ($CurrentStandardCostCompraControl*($OrderLine->Desc1/100));
								$CurrentStandardCostCompraControl = $CurrentStandardCostCompraControl - ($CurrentStandardCostCompraControl*($OrderLine->Desc2/100));
								$CurrentStandardCostCompraControl = $CurrentStandardCostCompraControl - ($CurrentStandardCostCompraControl*($OrderLine->Desc3/100));
		
								$SQL = "INSERT INTO grns (
										grnbatch,
										podetailitem,
										itemcode,
										itemdescription,
										deliverydate,
										qtyrecd,
										supplierid,
										rategr,
										stdcostunit
									) VALUES (
										" . $GRN . ",
										" . $OrderLine->PODetailRec . ",
										'" . $OrderLine->StockID . "',
										'" . $OrderLine->ItemDescription . "',
										'" . $_POST['DefaultReceivedDate'] . "',
										" . ($Item->BundleQty) . ",
										'" . $_SESSION['PO']->SupplierID . "',
										'" . $_SESSION['PO']->ExRate . "',
										" . $CurrentStandardCostCompraControl . '
									)';
								
								$Result = DB_query($SQL, $db);
		
								$SQL = "INSERT INTO stockmoves (
										stockid,
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
									) VALUES (
										'" . $OrderLine->StockID . "',
										$tipodefacturacion,
										" . $GRN . ", '" . $_SESSION['PO']->Location . "',
										'" . $_POST['DefaultReceivedDate'] . "',
										" . $CurrentStandardCostCompra . ",
										" . $PeriodNo . ",
										'" . $_SESSION['PO']->SupplierID . " (" . $_SESSION['PO']->SupplierName . ") - " .$_SESSION['PO']->OrderNo . $refSalesOrder . "',
										" . ($Item->BundleQty*$conversionfactor) . ",
										" . $CurrentStandardCost . ",
										" . ($QtyOnHandPrior + $Item->BundleQty ) . ",
										" . $OrderLine->Desc1 . ",
										" . $OrderLine->Desc2 . ",
										" . $OrderLine->Desc3 . ",
										" . $unidaddenegocio . ",
										'" . $Item->BundleRef . "',
										'" . $CurrentStandardCost . "'
									)";
								
								$Result = DB_query($SQL, $db);
								/*Get the ID of the StockMove... */
								$StkMoveNo = DB_Last_Insert_ID($db, 'stockmoves', 'stkmoveno');
		
								$SQL = "SELECT COUNT(*) FROM stockserialitems
									WHERE stockid='" . $OrderLine->StockID . "'
									AND loccode = '" . $_SESSION['PO']->Location . "'
									AND serialno = '" . $Item->BundleRef . "'";
									
								$result = DB_query($SQL, $db);
								$AlreadyExistsRow = DB_fetch_row($result);
								if (trim($Item->BundleRef) != "") {
		
									list($mes, $dia, $anio) = explode('/', $Item->CustomsDate);
									$customsDate = "$anio-$mes-$dia";
		
									if ($AlreadyExistsRow[0] > 0) {
										
										if ($OrderLine->Serialised == 1) {
											
											$SQL = "UPDATE stockserialitems
												SET quantity = " . $Item->BundleQty . " ,
												standardcost=" .$CurrentStandardCost . ",
												customs = '" . $Item->Customs . "',
												pedimento = '".$Item->CustomsNumber."',
												customs_number = '" . $Item->CustomsNumber . "',
											    customs_date = '" . $customsDate . "'";
										} else {
											
											$SQL = "UPDATE stockserialitems
												SET quantity = quantity + " . ($Item->BundleQty * $conversionfactor) . " ,
												standardcost=" .$CurrentStandardCost . ",
												customs = '" . $Item->Customs . "',
												pedimento = '".$Item->CustomsNumber."',
												customs_number = '" . $Item->CustomsNumber . "',
												customs_date = '" . $customsDate . "'";
										}
										$SQL .= "WHERE stockid='" . $OrderLine->StockID . "'
											AND loccode = '" . $_SESSION['PO']->Location . "'
											AND serialno = '" . $Item->BundleRef . "'";
										
									} else {
								
										$SQL = "INSERT INTO stockserialitems (
												stockid,
												loccode,
												serialno,
												qualitytext,
												quantity,
												standardcost,
												customs,
												customs_number,
												customs_date,
												pedimento
											) VALUES (
												'" . $OrderLine->StockID . "',
												'" . $_SESSION['PO']->Location . "',
												'" . $Item->BundleRef . "',
												'" . $puertoentrada . "',
												" . ($Item->BundleQty * $conversionfactor) . ",
												" . $CurrentStandardCost . ",
												'" . $Item->Customs . "',
												'" . $Item->CustomsNumber . "',
												'" . $customsDate . "',
												'".$Item->CustomsNumber."'
											)";
								}
								
								$Result = DB_query($SQL, $db);
								
								$SQL = "INSERT INTO stockserialmoves (
										stockmoveno,
										stockid,
										serialno,
										moveqty,
										standardcost,
										orderno,
										orderdetailno
									) VALUES (
										" . $StkMoveNo . ",
										'" . $OrderLine->StockID . "',
										'" . $Item->BundleRef . "',
										" . ($Item->BundleQty*$conversionfactor) . ",
										" . $CurrentStandardCost .",
										" . (int) $_SESSION['PO']->OrderNo .",
										" . $OrderLine->PODetailRec ."
									)";
									
								$Result = DB_query($SQL, $db);
							}//non blank BundleRef
		
							$LocCode = $_SESSION['PO']->Location;
						} //end foreach
					}
				} /*end of its a stock item - updates to locations and insert movements*/
							
				/* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/
				if ($_SESSION['PO']->GLLink == 1 AND $OrderLine->GLCode != 0) { /*GLCode is set to 0 when the GLLink is not activated this covers a situation where the GLLink is now active but it wasn't when this PO was entered */
					/*first the debit using the GLCode in the PO detail record entry*/
					$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag
						) VALUES (
							$tipodefacturacion,
							'" . $GRN . "',
							'" . $_POST['DefaultReceivedDate'] . "',
							'" . $PeriodNo . "',
							'" . $OrderLine->GLCode . "',
							'PO: " . $_SESSION['PO']->OrderNo . " " . $_SESSION['PO']->SupplierID . " - " . $OrderLine->StockID . " - " . DB_escape_string($OrderLine->ItemDescription) . " x " . $OrderLine->ReceiveQty . " @ " . number_format($CurrentStandardCost,2) . "',
							'" . $CurrentStandardCost * $OrderLine->ReceiveQty . "',
							'" . $unidaddenegocio . "'
						)";
					
					$Result = DB_query($SQL,$db);
					if($_SESSION['UserID'] == "admin"){
						// echo '<br><pre>sql1 '.$SQL;
					}
		
					$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag
						) VALUES (
							$tipodefacturacion,
							'" . $GRN . "',
							'" . $_POST['DefaultReceivedDate'] . "',
							'" . $PeriodNo . "',
							'" . $_SESSION['CompanyRecord']['grnact'] . "', '" .
							_('PO') . ': ' . $_SESSION['PO']->OrderNo . ' ' . $_SESSION['PO']->SupplierID . ' - ' . $OrderLine->StockID . ' - ' . DB_escape_string($OrderLine->ItemDescription) . ' x ' . $OrderLine->ReceiveQty . ' @ ' . number_format($UnitCost,2) . "',
							'" . -$UnitCost * $OrderLine->ReceiveQty . "',
							'" . $unidaddenegocio . "'
						)";
					if($_SESSION['UserID'] == "admin"){
						// echo '<br><pre>sql2 '.$SQL;
					}
					$Result = DB_query($SQL, $db);
				} /* end of if GL and stock integrated and standard cost !=0 */
				
				$totalcompra+= ($CurrentStandardCost * $OrderLine->ReceiveQty) * $porcentaje_impuesto;
				
			} /* Quantity received is != 0 */
		}
		// echo "<script>alert('Total validar: ".$totalcompra."');</script>";
                
		$totalcompra = truncateFloat($totalcompra,$digitos);
		$resultado= GeneraMovimientoContablePresupuesto($tipodefacturacion, "POREJERCER", "COMPROMETIDO", $GRN, $PeriodNo,
				$totalcompra, $unidaddenegocio, $_SESSION['PO']->Orig_OrderDate, $db);
		
		$completedsql = 'SELECT SUM(completed) as completedlines,
			COUNT(podetailitem) as alllines
			FROM purchorderdetails
			WHERE orderno=' . $_SESSION['PO']->OrderNo;
		
		$completedresult = DB_query($completedsql, $db);
		$mycompletedrow = DB_fetch_array($completedresult);
		$status = $mycompletedrow['alllines'] - $mycompletedrow['completedlines'];
		if ($status == 0) {
			$sql = 'SELECT stat_comment FROM purchorders WHERE orderno='.$_SESSION['PO']->OrderNo;
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			$comment = $myrow['stat_comment'];
			$date = date($_SESSION['DefaultDateFormat']);
			$StatusComment = $date.' - Order Completed' . '<br>' . $comment;
			$sql = "UPDATE purchorders
				SET status='" . _('Completed') . "',
				stat_comment='" . $StatusComment . "'
				WHERE orderno=" . $_SESSION['PO']->OrderNo;
			
			$Result = DB_query($sql,$db);
		}
		$PONo = $_SESSION['PO']->OrderNo;
		$SupplierPONo = $_SESSION['PO']->SupplierOrderNo;	
		
		// genera nueva orden de compra para que esta quede completa de acuerdo a los check seleccionados por el usuario
		include_once realpath(dirname(__FILE__)) . '/GeneraNewPurchOrder.inc';
		// si viene de orden de trabajo
		if ($_SESSION['PO']->Wo > 0) {
			include_once realpath(dirname(__FILE__)) . '/AutomaticEmisionWo.inc';
		}
		$Result = DB_Txn_Commit($db);
		
		return true;
	}
	
	return false;
}
?>