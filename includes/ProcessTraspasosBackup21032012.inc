<?php
// 12
$Result = DB_Txn_Begin($db);
$Trf_ID = GetNextTransNo(16,$db);
$_POST['Trf_ID'] = $Trf_ID;

$Trf_ID2 = GetNextTransNo(16,$db);

$Total=$_POST['Total'];
$entra=false;
$PeriodNo=GetPeriod (Date('Y-m-d'), $db);;
for ($i=1;$i <= $Total;$i++){
	if($_POST['Sel' . $i]==true){
		$_POST['comments']="Traspaso Automatico de la Req. ".$_POST['requisitionno_' . $i];
		$totalatraspaso=$_POST['Ordenadas_'.$i];
		$totalatraspaso=$totalatraspaso-$_POST['Solicitadas_'.$i];
		
		//valido no traspasen mas unidades de las ordenadas
		if($totalatraspaso>0){
			if($entra==false){
				$entra=true;
			}
			if($_POST['CantidadT_' . $i]>0 and strlen($_POST['CantidadT_' . $i])>0){
				$SQL="select tagref from locations where loccode='".$_POST['LocationReq_'.$i]."'";
				$Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				$CompraAlmacenRow = DB_fetch_row($Resultalmacen);
				$Tagref = $CompraAlmacenRow[0];
				$unidaddenegocio=$CompraAlmacenRow[0];
				// Buscar el almacen temporal para hacer la transferencia
				$SQL="select * from locations where tagref='".$Tagref."' and temploc=1";
				$Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				$CompraAlmacenRow = DB_fetch_row($Resultalmacen);
				$AlmacenStock = $CompraAlmacenRow[0];
				//realizar la transferencia
				$sql = "INSERT INTO loctransfers (reference,
									stockid,
									shipqty,
									recqty,
									shipdate,
									recdate,
									shiploc,
									recloc,
									comments,
									serialno,
									userregister,
									userrec,
									transferline
									)
							VALUES ('" . $_POST['Trf_ID'] . "',
								'" . strtoupper($_POST['itemcode_' . $i]) . "',
								'" . $_POST['CantidadT_' . $i] . "',
								'" . $_POST['CantidadT_' . $i] . "',
								'" . Date('Y-m-d') . "',
								'" . Date('Y-m-d') . "',
								'" . $_POST['StockLocationDist_'.$i]  ."',
								'" . $AlmacenStock . "',
								'" . $_POST['comments'] . "',
								'" . $_POST['serialno'.$i] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $i . "'
								)";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				/*********************************************************************/
				$CantidadT=$_POST['CantidadT_' . $i] ;
				//$unidaddenegocio=ExtractTagrefXLoc($_SESSION['PO']->Location,$db);
				// aqui van los movimientos de inv para el almacen que envia
				$sql = "UPDATE locstock
					SET quantity=quantity-" . $CantidadT .
				       " WHERE stockid='" . $_POST['itemcode_' . $i]  .
				       "' AND loccode='" . $_POST['StockLocationDist_'.$i]  . "'";
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				// funcion de avgcost por tagref
				$unitscostXtag=StockAvgUnits($_POST['itemcode_' . $i] ,$unidaddenegocio,$db);
				$estavgcostXtag=StockAvgcost($_POST['itemcode_' . $i] ,$unidaddenegocio,$db);
				$tagavgcostlocfrom = $estavgcostXtag;
				$lastcostXtag=StockLastCost($_POST['itemcode_' . $i] ,$unidaddenegocio,$db);
				$lastcostXlegal=StockLastCostXLegal($_POST['itemcode_' . $i] ,$unidaddenegocio,$db);
				$unitscostXlegal=StockAvgUnitsXLegal($_POST['itemcode_' . $i] ,$unidaddenegocio,$db);
				$estavgcostXlegal=StockAvgcostXLegal($_POST['itemcode_' . $i] ,$unidaddenegocio,$db);
				
				if ($_SESSION['TypeCostStock']==1){
					$unitscost=$unitscostXtag;
					$estavgcost=$estavgcostXtag;
					$EstimatedAvgCost=$tagavgcostlocfrom; //$EstimatedAvgCostXtag;
					$lastcost= $tagavgcostlocfrom; //$lastcostXtag;
				}else{
					$unitscost=$unitscostXlegal;
					$estavgcost=$estavgcostXlegal;
					$EstimatedAvgCost= $estavgcostXlegal; //$EstimatedAvgCostXlegal;
					$lastcost= $lastcostXlegal; //$lastcostXlegal;
					$standardcost=$EstimatedAvgCost;
				}
				$sql="Select locationname from locations where loccode='".$AlmacenStock."'";
				$Result = DB_query($sql, $db, _('No se pudo determinar el nombre del almacen de recepcion') );
				$LocQtyRow = DB_fetch_row($Result);
				$StockLocationToName = $LocQtyRow[0];
				//movimiento de salida		
				
				$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							prd,
							reference,
							qty,
							newqoh,
							standardcost,
							avgcost,
							tagref
							)
					VALUES (
						'" . strtoupper($_POST['itemcode_' . $i]) . "',
						16,
						" . $_POST['Trf_ID'] . ",
						'" . $_POST['StockLocationDist_'.$i]  . "',
						'" . date('Y-m-d') . "',
						" . $PeriodNo . ",
						'" . _('To') . ' ' . $StockLocationToName . "',
						" . -$CantidadT . ",
						" . ($CantidadT) . ",
						" . $standardcost . ",
						" . $EstimatedAvgCost. ",
						" . $unidaddenegocio. "
					)";
		
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
				//movimiento de entrada
				$sql="Select locationname from locations where loccode='". $_POST['StockLocationDist_'.$i]."'";
				$Result = DB_query($sql, $db, _('No se pudo determinar el nombre del almacen de recepcion') );
				$LocQtyRow = DB_fetch_row($Result);
				$StockLocationFromName = $LocQtyRow[0];
				
				$SQL = "INSERT INTO stockmoves (
						stockid,
						type,
						transno,
						loccode,
						trandate,
						prd,
						reference,
						qty,
						newqoh,
						standardcost,
						avgcost,
						tagref
						)
					VALUES (
						'" . strtoupper($_POST['itemcode_' . $i]) . "',
						16,
						" . $_POST['Trf_ID'] . ",
						'" . $AlmacenStock . "',
						'" . Date('Y-m-d') . "'," . $PeriodNo . ",
						'" . _('From') . ' ' . $StockLocationFromName ."',
						" .$CantidadT . ", " . ($CantidadT) . ",
						" . $standardcost . ",
						" . $EstimatedAvgCost. ",
						" . $unidaddenegocio. "
						)";
		
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
				$DbgMsg =  _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				
				
				
				/*********************************************************************/
				//actualiza el ontransit del almacen de distribucion
				$sql = "UPDATE locstock
					SET quantity=quantity+" . $_POST['CantidadT_' . $i] .
				       " WHERE stockid='" . $_POST['itemcode_' . $i] .
				       "' AND loccode='" . $AlmacenStock . "'";
				//echo $sql;
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				// se actualiza el estatus y la cantidad en transito
				$sql = "UPDATE requisitionorderdetails
					SET statusdetail='En Almacen Virtual',
					    quantityvirtual=quantityvirtual+'" .  $_POST['CantidadT_' . $i] ."'
					WHERE podetailitem='" . $_POST['requisitionno_' . $i] . "'";
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				// se actualiza la requisicion con el numero de traspaso
				$sql = "INSERT INTO transferrequistions (norequisition,
							transferno,
							type,
							quantityord,
							quantityrecd
							)
					VALUES ('".$_POST['requisitionno_' . $i]."',
						'" .  $_POST['Trf_ID'] ."',
						'Transferencia',
						'" . $_POST['CantidadT_' . $i]. "',
						0
						)";
				$ErrMsg = _('No se puede insertar en el log');
				$DbgMsg = _('El SQL es');
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				//se actualiza el log de transferencia
				$sql = "INSERT INTO logrequisitionorders (norequisition,
							itemcode,
							status,
							comments,
							userid,
							datetransaction
							)
					VALUES ('".$_POST['requisitionno_' . $i]."',
							'" .   $_POST['itemcode_'.$i] . "',
							'En Almacen Virtual',
							'Se Genero transferencia del Producto ". $_POST['itemcode_'.$i].'X la Cantidad de '. $_POST['CantidadT_' . $i].' En El Almacen:'.$_POST['StockLocationDist_'.$i] ."',
							'" .  $_SESSION['UserID'] . "',
							now()
						)";
				$ErrMsg = _('No se puede insertar en el log');
				$DbgMsg = _('El SQL es');
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				$NoRequisition=$_POST['requisitionno_' . $i];
				$productoReq=$_POST['itemcode_'.$i];
				$statusReq='En Almacen Virtual';
				$comentariosReq="Se Genero transferencia del Producto ". $_POST['itemcode_'.$i].'X la Cantidad de '. $_POST['CantidadT_' . $i].' En El Almacen:'.$_POST['StockLocationDist_'.$i];
				$UsuarioReq=$_SESSION['UserID'];
				//enviar email a los que participan en la requisicion de la orden de compra
				include('SendEmailChangeRequisitions.inc');
				// realiza transferencia de almacen virtual a almacen de ventas
				//realizar la transferencia
				$sql = "INSERT INTO loctransfers (reference,
									stockid,
									shipqty,
									recqty,
									shipdate,
									recdate,
									shiploc,
									recloc,
									comments,
									serialno,
									userregister,
									userrec,
									transferline,
									requisitionno
									)
							VALUES ('" . $Trf_ID2 . "',
								'" . strtoupper($_POST['itemcode_' . $i]) . "',
								'" . $_POST['CantidadT_' . $i] . "',
								0,
								'" . Date('Y-m-d') . "',
								'" . Date('Y-m-d') . "',
								'" . $AlmacenStock  ."',
								'" . $_POST['LocationReq_'.$i] . "',
								'" . $_POST['comments'] . "',
								'" . $_POST['serialno'.$i] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $_SESSION['UserID'] . "',
								'" . $i . "',
								'".$_POST['requisitionno_' . $i]."'
								)";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$_POST['StockID' . $i];
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				//actualiza el ontransit del almacen de distribucion
				$sql = "UPDATE locstock
					SET ontransit=ontransit+" . $_POST['CantidadT_' . $i] .
				       " WHERE stockid='" . $_POST['itemcode_' . $i] .
				       "' AND loccode='" . $AlmacenStock . "'";
				//echo $sql;
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				// se actualiza la requisicion con el numero de traspaso
				$sql = "INSERT INTO transferrequistions (norequisition,
							transferno,
							type,
							quantityord,
							quantityrecd
							)
					VALUES ('".$_POST['requisitionno_' . $i]."',
						'" . $Trf_ID2 ."',
						'Transferencia',
						'" . $_POST['CantidadT_' . $i]. "',
						0
						)";
				$ErrMsg = _('No se puede insertar en el log');
				$DbgMsg = _('El SQL es');
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
				
				$sql = "UPDATE requisitionorderdetails
					SET quantityvirtual=quantityvirtual-'" .  $_POST['CantidadT_' . $i] ."',
					    quantityontransit=quantityontransit+'" .  $_POST['CantidadT_' . $i] ."'
					WHERE podetailitem='" . $_POST['requisitionno_' . $i] . "'";
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			}
		}
	}
}
$Result=DB_Txn_Commit($db);
if($entra==true){
	prnMsg( _('<B>LA TRANSEFERENCIA DE INVENTARIO SE HA GENERADO EXITOSAMENTE...</B>'),'success');
	echo '<p><a href="'.$rootpath.'/PDFStockLocTransfer.php?' . SID . 'TransferNo=' . $_POST['Trf_ID'] . '" target="_blank"><b>'._('Imprimir Comprobante de Transferencia # ') . $_POST['Trf_ID']. '</b></a>';
}else{
	prnMsg( _('Los productos seleccionados no generan transferencia por tener operaciones en transito pendientes.'),'error');
}


?>