<?php
if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0){
/*first the cost of sales entry*/

                                $tipoalmacen = GetTypeLocation($OrderLine->AlmacenStock,$db);
				//echo 'tipoalmacen:'.$tipoalmacen.'<br>';
				if ($tipoalmacen!=2){
				     $cuentainventario=GetCOGSGLAccount($Area, $AssParts['component'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				}else{
				     $cuentainventario=GetCOGSGLAccountConsigment($Area, $AssParts['component'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
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
							)
					VALUES (
						" . $tipodefacturacion . ",
						" . $InvoiceNo . ",
						'" . $DefaultDispatchDate . "',
						" . $PeriodNo . ",
						" . $cuentainventario . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $AssParts['component'] . " | ".$AssParts['descripcion']." x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . " |".$seriesventa. "',
						" . $OrderLine->StandardCost * $OrderLine->Quantity . ",
						" . $_SESSION['Tagref'] . "
					)";

				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
/*now the stock entry*/
				$StockGLCode = GetStockGLCode($AssParts['component'],$db);
				$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag
							)
					VALUES (
						" . $tipodefacturacion . ",
						" . $InvoiceNo . ",
						'" . $DefaultDispatchDate . "',
						" . $PeriodNo . ",
						" . $StockGLCode['stockact'] . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $AssParts['component'] . " | ".$AssParts['descripcion'] . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . " |".$seriesventa. "',
						" . (-$OrderLine->StandardCost * $OrderLine->Quantity) . ",
						" . $_SESSION['Tagref'] . "
					)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} /* end of if GL and stock integrated and standard cost !=0 */
?>