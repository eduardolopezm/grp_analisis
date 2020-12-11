<?php 
// La variable $identifier viene del php PO_items.php
if(empty($_POST['partidas']) == FALSE) {
	$debug = 0;
	$newOrderNo = 0;
	$insertarCabecera = TRUE;
	$partidas = $_POST['partidas'];
	$orderno = $_SESSION['ExistingPurchOrder'];
	$tienePartidas = FALSE;
	
	
	foreach($partidas as $partida) {
		$partidasObj = $_SESSION['PO' . $identifier];
					$result = DB_Txn_Begin($db);
					$sql = "
						INSERT INTO purchorderdetails (orderno,itemcode,deliverydate,itemdescription,glcode,qtyinvoiced,unitprice,
							actprice,stdcostunit,quantityord,quantityrecd,shiptref,jobref,completed,itemno,uom,subtotal_amount,package,
							pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,discountpercent1,discountpercent2,discountpercent3,
							narrative,justification,refundpercent,totalrefundpercent,estimated_cost,orderlineno_,saleorderno_,qtywo,wo
						) 
						SELECT orderno,itemcode,deliverydate,itemdescription,glcode,0,unitprice,
							actprice,stdcostunit,quantityord,0,shiptref,jobref,completed,itemno,uom,subtotal_amount,package,
							pcunit,nw,suppliers_partno,gw,cuft,total_quantity,total_amount,discountpercent1,discountpercent2,discountpercent3,
							narrative,justification,refundpercent,totalrefundpercent,estimated_cost,orderlineno_,saleorderno_,qtywo,wo
						FROM purchorderdetails
						WHERE podetailitem = '" . $partida . "'
					";
					$ErrMsg = _('El detalle de la orden de compra no pudo ser insertada');
					$DbgMsg = _('El SQL que fallo fue:');
					if($debug) {
						echo "<pre>$sql";
					} else {
						$result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
					}
					//echo "<pre>$sql";
					
					$result = DB_Txn_Commit($db);
					
					$podetailitem=DB_Last_Insert_ID($db,'purchorderdetails','podetailitem');
					
					
					//Agregar a clase la partida duplicada
					
					$LineItemsSQL = 'SELECT podetailitem, itemcode,
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
							              	purchorderdetails.refundpercent
									FROM purchorderdetails LEFT JOIN stockmaster
												ON purchorderdetails.itemcode=stockmaster.stockid
												LEFT JOIN chartmaster
												ON purchorderdetails.glcode=chartmaster.accountcode
									WHERE purchorderdetails.podetailitem =' .$podetailitem . "
											ORDER BY podetailitem";
					
					$ErrMsg =  _('The lines on the purchase order cannot be retrieved because');
					$DbgMsg =  _('The SQL statement that was used to retrieve the purchase order lines was');
					//echo '<pre><br>'.$LineItemsSQL;
					$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg);
					
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
					
							$_SESSION['PO'.$identifier]->add_to_order($_SESSION['PO'.$identifier]->LinesOnOrder+1,
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
									$myrow['estimated_cost']
							);
							//$_SESSION['PO'.$identifier]->LinesOnOrder=$_SESSION['PO'.$identifier]->LinesOnOrder+1;
					
							$_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->PODetailRec = $myrow['podetailitem'];
							$_SESSION['PO'.$identifier]->LineItems[$_SESSION['PO'.$identifier]->LinesOnOrder]->StandardCost = $myrow['stdcostunit'];
					
					
						}// fin de while
					}// fin de if de consulta
					prnMsg(_("La partida seleccionada se ha duplicado"), 'info');
				}// fin de agrega partida
					
		}// fin de tiene partida
		else{
			prnMsg(_("No hay partidas seleccionadas1!"), 'error');
				
		}		
		
		
?>