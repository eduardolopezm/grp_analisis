<?php
/* ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 10-FEB-2010
 CAMBIOS: 
	1. VALIDACION DE DESCUENTO DE IVAS EN CXC 
 FIN DE CAMBIOS
 FECHA DE MODIFICACION: 23-FEB-2010
 CAMBIOS: 
	1. VALIDACION DE INVENTARIO EN ROJO PARA PAQUETES
 FIN DE CAMBIOS
*/
if (isset($_SESSION['ExistingOrder']) and $_SESSION['ExistingOrder']!=0){
    $OrderNo=$_SESSION['ExistingOrder'];
}else{
    $_SESSION['quotationANT']=1;
}
if ($_POST['makeremision']=='1'){
	$remisionmake=1;
} else{
	$remisionmake=0;
}
$totaldesccategory=0;
/*$sql="SELECT termsindicator FROM paymentterms WHERE daysbeforedue='1' and numberOfPayments='0'";
$result =DB_query($sql,$db);
$myrow= DB_fetch_array($result);
$terminopagoremision=$myrow['termsindicator'];
if ($terminopagoremision!=$terminopago){
		$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$Result = DB_Txn_Commit($db);	
		prnMsg( _('Para realizar una remision es necesario que el termino de pago sea exclusivamente de contado, por lo tanto ') . ' ' . _('no ha sido posible facturar, intentelo nuevamente'),'error');
		echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber=' . $OrderNo  .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
		include('includes/footer.inc');
		exit;
}
*/

$SQL="SELECT * FROM paymentterms WHERE termsindicator='".$terminopago."'";
$result_term = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
if (DB_num_rows($result_term)==1) {
	//$myrowterm = DB_fetch_row($result_term);
	$myrowterm = DB_fetch_array($result_term);
	$generapagare=$myrowterm['generateCreditNote'];
	$numberOfPayments=$myrowterm['numberOfPayments'];
	$_SESSION['Paypromissory']=$myrowterm['automatic'];
	$pagares=$myrowterm['numberOfPayments'];
	$daysbeforevencimiento=$myrowterm['daysbeforedue'];

}


$tipodefacturacion = 580;



	
	$QuantityInvoicedIsPositive = false;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		if ($OrderLine->Quantity > 0){
			$QuantityInvoicedIsPositive =true;
		}
	}
	
	$QuantityControlled=true;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		if ($OrderLine->Controlled==1 OR $OrderLine->Serialised){
			if (count($OrderLine->SerialItems)==0){
				$QuantityControlled = false;
				break;
			}
			//$TotalQuantity += $Bundle->BundleQty;
			$totalserie=0;
			foreach($OrderLine->SerialItems as $Item){
				$totalserie=$totalserie+$Item->BundleQty ;
				
			}
			if ($totalserie<$OrderLine->QtyDispatched){
				$QuantityControlled =false;
				break;
			}
			
		}
	}
	
	if ($QuantityControlled==false){
		$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		prnMsg( _('No ha ingresado los numeros de serie por lo tanto,') . ' ' . _('no ha sido posible facturar, intentelo nuevamente'),'error');
		echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'identifier=' . $identifier .'"><b>'. _('Regresar a modificar la orden de venta') .'</a>';
		include('includes/footer.inc');
		exit;
	}
	
	/*foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		if($_SESSION['AllowSalesCost'] == 1 and ($OrderLine->Price/$_SESSION['CurrencyRate']) < $OrderLine->StandardCost ){
			// no permitir ventas en costo menor a precio
			$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			prnMsg(_('No se pueden realizar la factura por que existe un producto por que tiene un precio menor al costo '),'error');
			//echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber=' . $OrderNo  .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .' '._('para modificar el precio').'</a>';
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			include('includes/footer.inc');
			exit;	
		}
	}*/
	
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
	    // tome costo de orden de compra que tiene asignado el producto
	    
			$ssql = 'SELECT case when actprice>0  and status<>"Completed" then actprice else stdcostunit end';
			$ssql .= ' FROM purchorders o, purchorderdetails d';
			$ssql .= ' WHERE ';
			$ssql .= ' o.orderno=d.orderno';
			$ssql .= " AND d.itemcode='".$OrderLine->StockID."'";
			$ssql .= ' AND requisitionno="'.$OrderNo.'"';
			$resultorders = DB_query($ssql,$db);
			if (DB_num_rows($resultorders)>0)
			{
			    $myrowdetails = DB_fetch_array($resultorders);
			    if($OrderLine->costoproducto==0){
				 $OrderLine->costoproducto=$myrowdetails[0];
			    }
			}
		 $costoconmargen=($OrderLine->StandardCost+($OrderLine->StandardCost*($OrderLine->margenutilidad/100)));		 
		if($_SESSION['ProhibitSalesBelowCost'] == 1 and ($OrderLine->Price/$_SESSION['CurrencyRate']) < $costoconmargen and $permisobajocosto==0){
			// no permitir ventas en costo menor a precio
			$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$Result = DB_Txn_Commit($db);
			
			prnMsg(_('No se pueden realizar la factura por que el producto ').$OrderLine->StockID._(' tiene un precio menor al margen minimo '),'error');
			//echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber=' . $OrderNo  .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .' '._('para modificar el precio').'</a>';
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			include('includes/footer.inc');
			exit;	
		}
	}
	
	$TaxTotal =0;
	$TaxTotals = array();
	$TaxGLCodes = array();
	$TaxGLCodesDiscount = array();
	$TaxLineTotal=0;
	$qohsql = "SELECT  areacode 
		   FROM tags 
		   WHERE   tagref = '" . $_SESSION['Tagref'] . "'";
	$qohresult =  DB_query($qohsql,$db);
	$qohrow = DB_fetch_row($qohresult);
	$codigoarea=$qohrow[0];
	$PriceLess=true;
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
	 
		// VALIDACION DEL PRECIO DE LISTA VS PRECIO DE VENTA
		if ($_SESSION['PriceLess']==1){
		 //EXTRAE EL PRECIO DE LISTA PAR ESTE PRODUCTO
		 $listapreciox=$_SESSION['Items'.$identifier]->DefaultSalesType;
		 $preciolista=GetPriceWTAX($OrderLine->StockID, $_SESSION['Items'.$identifier]->DebtorNo,$listapreciox,$_SESSION['CurrAbrev'], $codigoarea, $db);
		 $separa = explode('|',$preciolista);
		 $Pricexlista = $separa[0];
		  if($OrderLine->Price<$Pricexlista){
		       $PriceLess =false;
		      $codigopreciomenor=$OrderLine->StockID;
		  }
		}
		if($PriceLess==false){
		  break;
		}
		// DESCUENTO UNO
		$LineTotal = $OrderLine->QtyDispatched * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		//DESCUENTO DOS
		$LineTotal=$LineTotal * (1 -$OrderLine->DiscountPercent1);
		//DESCUENTO TRES
		$LineTotal=$LineTotal * (1 - $OrderLine->DiscountPercent2);
		foreach ($OrderLine->Taxes AS $Tax) {
			if (empty($TaxTotals[$Tax->TaxAuthID])) {
				$TaxTotals[$Tax->TaxAuthID]=0;
			}
			
			if ($Tax->TaxOnTax ==1){
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
				$TaxLineTotal += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
			} else {
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $LineTotal);
				$TaxLineTotal += ($Tax->TaxRate * $LineTotal);
			}
			$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
			$TaxGLCodesDiscount[$Tax->TaxAuthID] = $Tax->TaxGLCodeDiscount;
		}
	}
	$TaxTotal = $TaxLineTotal;
	if($PriceLess==false){
	     $SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
	     $ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
	     $DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	     $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	     $Result = DB_Txn_Commit($db);	
	     prnMsg(_('No se puede realizar la factura por que el precio de venta del producto '.$codigopreciomenor.' es menor al precio de lista'),'error');
	     echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
	     include('includes/footer.inc');
	     exit;	
	}
	
	if ($_SESSION['LimitxDepto']==1 and $tipodefacturacion==10){
	// limites por departamento
		$limitexdeptoxclient=LimitXDeptoYClient($_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Tagref'],$db);
		$totalxfactxclien=$_SESSION['Items'.$identifier]->total+$TaxTotal;
		$depto=DeptoXtag($_SESSION['Tagref'],$db);
		$saldodeuda=SaldoClientxDepto($_SESSION['Items'.$identifier]->DebtorNo,$depto,$_SESSION['BaseDataware'],$db);
		$totalxfactxclien=$saldodeuda+$totalxfactxclien;
		if ($limitexdeptoxclient<$totalxfactxclien){
			$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$Result = DB_Txn_Commit($db);	
			prnMsg(_('No se pueden realizar la factura por que se ha excedido el limite de credito del cliente por departamento'),'error');
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			include('includes/footer.inc');
			exit;	
			
		}
		
	}elseif($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0 and $tipodefacturacion=10){
		$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$Result = DB_Txn_Commit($db);	
		prnMsg(_('No se pueden realizar la factura por que se ha excedido el limite de credito del cliente por departamento'),'error');
		echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $_SESSION['ExistingOrder'] .'</a>';
		include('includes/footer.inc');
		exit;	
		
	}
	
	
	
	/*******   VERIFICACION DE CANTIDAD POSITIVA  *******/
	if (! $QuantityInvoicedIsPositive){
		prnMsg( _('No hay líneas en este orden con una cantidad de la factura') . '. ' . _('No ha sido posible facturar'),'error');
		include('includes/footer.inc');
		exit;
	}
	
	if ($_SESSION['ProhibitNegativeStock']==1){ // checks for negative stock after processing invoice
	//sadly this check does not combine quantities occuring twice on and order and each line is considered individually :-(
		$NegativesFound = false;
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			$sqlprodxalm = "SELECT sum(salesorderdetails.quantitydispatched) AS totalprd
					FROM salesorderdetails
					WHERE	salesorderdetails.fromstkloc='" . $OrderLine->AlmacenStock  . "'
						AND salesorderdetails.stkcode='" . $OrderLine->StockID . "'
						AND salesorderdetails.orderno='" . $OrderNo . "'";
			$ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
			$ResultProdalm = DB_query($sqlprodxalm,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($ResultProdalm)==1){
			 $TotalstockXorder = DB_fetch_row($ResultProdalm);
			 $QtyOrder =  $TotalstockXorder[0];
			} 
			 $ordenescerradas=false;
		       $sqlLL = "SELECT ifnull(sum(ifnull(salesorderdetails.quantity-salesorderdetails.qtyinvoiced,0)),0) AS dem
					FROM salesorderdetails,
					     salesorders
					WHERE salesorders.orderno = salesorderdetails.orderno AND
					salesorderdetails.fromstkloc='" . $OrderLine->AlmacenStock  . "' AND
					salesorderdetails.completed=0 AND
					salesorders.quotation in(0,6) AND salesorders.orderno<>".$OrderNo." AND
					salesorderdetails.stkcode='" . $OrderLine->StockID . "'";
			//echo '<br>'.$sqlLL;
			$ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
			$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($DemandResult)==1){
		
			 $DemandRow = DB_fetch_row($DemandResult);
			// echo "<br><br>ENTRA cantidad:".$DemandRow[0].' prdo:'.$OrderLine->StockID."<br><br>";
			 $DemandQtySales =  $DemandRow[0];
			  $ordenescerradas=true;
			} else {
			 $DemandQtySales =0;
			}
			//echo "<br><br>cantidad demanda:".$DemandRow[0].'<br>';
			$SQL = "SELECT stockmaster.description,
					(locstock.quantity-locstock.ontransit)-".$DemandQtySales." as quantity,
					stockmaster.mbflag
		 		FROM locstock
		 			INNER JOIN stockmaster
					ON stockmaster.stockid=locstock.stockid
				WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
					AND locstock.loccode='" . $OrderLine->AlmacenStock . "'";
		         //echo "SQL:".$SQL;
			$ErrMsg = _('No se puede recuperar la cantidad para facturar');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$CheckNegRow = DB_fetch_array($Result);
			if ($CheckNegRow['mbflag']=='B' OR $CheckNegRow['mbflag']=='M'){
				if ($CheckNegRow['quantity'] < $QtyOrder and $OrderLine->RedInvoice==0 ){
					prnMsg( _('No se puede facturar en rojo, ya que asi lo establecen los parametros del sistema.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('No se admite facturaciones en cantidades de inventario negativas'));
					$NegativesFound = true;
					//break;
					// tiene ordenes de pedido cerradas
					if ($DemandQtySales>0){
					      $sqlLL = "SELECT distinct salesorders.orderno,orddate,salesorders.debtorno,name,quantity
					     FROM salesorderdetails,
						  salesorders, debtorsmaster
					     WHERE salesorders.orderno = salesorderdetails.orderno
					     AND salesorderdetails.fromstkloc='" . $OrderLine->AlmacenStock  . "'
					     AND debtorsmaster.debtorno=salesorders.debtorno AND
					     salesorderdetails.completed=0 AND
					     salesorders.quotation in(0,6) AND salesorders.orderno<>".$OrderNo." AND
					     salesorderdetails.stkcode='" . $OrderLine->StockID . "'";
					     $ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
					     $Result = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg,true);
					     echo '<UL><LI><FONT SIZE=2 COLOR=DARKBLUE>'._('Existen los siguientes pedidos cerrados que no permite la facturacion:').'</FONT></LI><UL><br>';
					     while ($RowOrders = DB_fetch_array($Result)){
						 echo '<br><UL><LI><FONT SIZE=2 COLOR=DARKBLUE>'._('Pedido:').$RowOrders['orderno']._(' Cliente:').$RowOrders['debtorno'].' - '.$RowOrders['name']._(' Cantidad:').$RowOrders['quantity'].'</LI></UL>';
					     }
					}
				    
					$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
					$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
					$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
					break;
					//echo "tipo red :".$OrderLine->RedInvoice;
				}
			} elseif ($CheckNegRow['mbflag']=='A') {
				$itemsarray=array();
				$itemx=0;
				foreach($OrderLine->SerialItems as $Item){
					$itemsarray[$itemx]=strtoupper($Item->StockIDParent);
					$itemx=$itemx+1;
				}
				$QuantityControlled=true;
				/*Now look for assembly components that would go negative */
				$SQL = "SELECT bom.component,
						stockmaster.description,
						stockmaster.serialised,
						stockmaster.controlled,
						(locstock.quantity-locstock.ontransit)-(" . $OrderLine->Quantity  . "*bom.quantity) AS qtyleft,
						stockmaster.mbflag,
						stockcategory.redinvoice
					FROM bom
						INNER JOIN locstock
						ON bom.component=locstock.stockid
						INNER JOIN stockmaster
						ON stockmaster.stockid=bom.component
						INNER JOIN stockcategory ON stockcategory.categoryid=stockmaster.categoryid
					WHERE bom.parent='" . $OrderLine->StockID . "'
						AND locstock.loccode='" . $OrderLine->AlmacenStock . "'
						AND effectiveafter <'" . Date('Y-m-d') . "'
						AND effectiveto >='" . Date('Y-m-d') . "'";
				
				$ErrMsg = _('No se pueden facturar ordenes de trabajo en negativo');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				while ($NegRow = DB_fetch_array($Result)){
				    $sqlLL = "SELECT ifnull(sum(ifnull(salesorderdetails.quantity-salesorderdetails.qtyinvoiced,0)),0) AS dem
					FROM salesorderdetails,
					     salesorders
					WHERE salesorders.orderno = salesorderdetails.orderno AND
					salesorderdetails.fromstkloc='" . $OrderLine->AlmacenStock  . "' AND
					salesorderdetails.completed=0 AND
					salesorders.quotation in(0) AND salesorders.orderno<>".$OrderNo." AND
					salesorderdetails.stkcode='" . $NegRow['component'] . "'";
				    $ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
				    $DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
				    if (DB_num_rows($DemandResult)==1){
					$DemandRow = DB_fetch_row($DemandResult);
					$DemandQtySalesComponent =  $DemandRow[0];
				    } else {
				     $DemandQtySalesComponent =0;
				    }
				    $NegRow['qtyleft']=$NegRow['qtyleft']-$DemandQtySalesComponent;
					if ($NegRow['qtyleft']<0 and $NegRow['redinvoice']==0 and ($NegRow['mbflag']=='B' OR $NegRow['mbflag']=='M')){
						prnMsg(_('No se puede facturar en rojo en el componente, ya que asi lo establecen los parametros del sistema.'),'error',$NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('No se admite facturaciones en cantidades de inventario negativas'));
						$NegativesFound = true;
						
						// deja la orden como cotizacion
						$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
						$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
						$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
					} // end if negative would result
					else{
						// meter aqui validacion de numeros de serie de componentes ensamblados
						if ($NegRow['serialised']==1 or $NegRow['controlled']==1 and $OrderLine->RedInvoice==0){
							$NegRow['component']=strtoupper($NegRow['component']);
						    if (in_array($NegRow['component'],$itemsarray)){
							foreach($OrderLine->SerialItems as $Item){
								if ($NegRow['component']==$Item->StockIDParent){
									if (count($Item->BundleQty)<=0){
										$QuantityControlled=false;
										break;
									};
								}
							}
						    }else{
							
								$QuantityControlled=false;
								break;
						    }
						}
					}
				} //loop around the components of an assembly item
			}//end if its an assembly item - check component stock

		} //end of loop around items on the order for negative check
		//$QuantityControlled=false;
		//$NegativesFound=true;
		
		/************************************************************************/
		/******     SI HAY VENTAS CON EXISTENCIAS EN NEGATIVO   *****************/
		/************************************************************************/
		if ($NegativesFound){
			$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$Result = DB_Txn_Commit($db);	
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			echo '<div class="centre">
					</div>';
			include('includes/footer.inc');
			exit;
		}
		
		if ($QuantityControlled==false){
			$SQL = "UPDATE salesorders SET quotation=".$_SESSION['quotationANT']." WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$Result = DB_Txn_Commit($db);	
			prnMsg( _('No ha ingresado los numeros de serie x componente por lo tanto,') . ' ' . _('no ha sido posible facturar, intentelo nuevamente'),'error');
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'identifier=' . $identifier .'"><b>'. _('Regresar a la Orden de Venta') .'</a>';
			//echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber=' . $OrderNo  .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			include('includes/footer.inc');
			exit;
		}

	}//end of testing for negative stocks
	/* Now Get the area where the sale is to from the branches table */
	$SQL = "SELECT area,
		       defaultshipvia
		FROM custbranch
		WHERE custbranch.debtorno ='". $_SESSION['Items'.$identifier]->DebtorNo . "'
		AND custbranch.branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "'";
	$ErrMsg = _('No se pudo cargar la oficina del cliente') . '. ' . _('Verifique');
	$Result = DB_query($SQL,$db, $ErrMsg,$DbgMsg,true);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	$DefaultShipVia = $myrow[1];
	//DB_free_result($Result);
	
	/*company record read in on login with info on GL Links and debtors GL account*/
	if ($_SESSION['CompanyRecord']==0){
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg( _('La informacion de la compañia y sus preferencias no se pudieron recuperar') . ' - ' . _('consulte al administrador del sistema'), 'error');
		include('includes/footer.inc');
		exit;
	}
	/*Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else may have invoiced them */
	/*$SQL = "SELECT stkcode,
			quantity,
			qtyinvoiced,
			orderlineno
		FROM salesorderdetails
		WHERE completed=0
		AND orderno = " . $OrderNo;
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
	if (DB_num_rows($Result) != count($_SESSION['Items'.$identifier]->LineItems)){
		if ($debug==1){
			echo '<br>'.$SQL;
			echo '<br>' . _('Numero de registros de SQL') . ':' . DB_num_rows($Result);
			echo '<br>' . _('Numero de productos') . ' ' . count($_SESSION['Items'.$identifier]->LineItems);
		}
		
		echo '<br>';
		prnMsg( _('La orden ha sido facturada con anterioridad'), 'error');
		unset($_SESSION['Items']->LineItems);
		unset($_SESSION['Items']);
		unset($_SESSION['ProcessingOrder']);
		unset($_SESSION['CurrAbrev']);
		unset($_SESSION['Tagref']);
		
		include('includes/footer.inc'); exit;
	}*/
	$Changes =0;	

//******************************************************************************************************************************
//******************************************************************************************************************************
//********************************* INICIO DE PROCESO DE FACTURACION ***********************************************************

	$DefaultDispatchDate=$_POST['fechafactura'];//Date($fechaf,CalcEarliestDispatchDate());
	$InvoiceNo = GetNextTransNo($tipodefacturacion, $db);
	$folio=$tipodefacturacion.'-'.$_SESSION['Tagref'].$InvoiceNo;
	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
	/*Start an SQL transaction */
	//DB_Txn_Begin($db);
	/*if ($DefaultShipVia != $_SESSION['Items']->ShipVia){
		$SQL = "UPDATE custbranch SET defaultshipvia ='" . $_SESSION['Items']->ShipVia . "' WHERE debtorno='" . $_SESSION['Items']->DebtorNo . "' AND branchcode='" . $_SESSION['Items']->Branch . "'";
		$ErrMsg = _('Could not update the default shipping carrier for this branch because');
		$DbgMsg = _('The SQL used to update the branch default carrier was');
		$result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	}
	*/
	
	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {
		/* Check to see if the quantity reduced to the same quantity
		as already invoiced - so should set the line to completed */
		if ($StockItem->Quantity == ($StockItem->QtyInv+$StockItem->QtyDispatched)){
			$Completed = 1;
			//echo "entra";
		} else {  /* order line is not complete */
			$Completed = 0;
		}
		$LineItemsSQL = 'UPDATE salesorderdetails
				 SET completed=' . $Completed . '
				 WHERE salesorderdetails.orderno=' . $OrderNo . '
					AND salesorderdetails.orderlineno='."'" . $StockItem->LineNumber . "'";
		//echo "<br>".$LineItemsSQL."<br> ";
		$DbgMsg = _('El SQL utilizado para modificar el producto de la orden no se ejecuto');
		$ErrMsg = _('La actualizacion del producto de la orden no se puede actualizar, por que');
		$Upd_LineItemResult = DB_query($LineItemsSQL,$db,$ErrMsg,$DbgMsg,true);
	}
	
	$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
	$_POST['InvoiceText']=' Orden '.$OrderNo.' Facturacion Directa';
	// gastos de envio en 1 que significa que no se cobra y flete se quedan en ceros
	$_SESSION['Items'.$identifier]->ShipVia=1;
	$_POST['ChargeFreightCost']=0;
	$fechaemision=$_POST['fechafactura'];
	$separae = explode('/',$fechaemision);
	$diae=$separae[0];;
	$mese = $separae[1];
	$anioe = $separae[2];
	$horax = date('H:i:s');
	$horax = strtotime($horax);
	$hora=date(H)-1;
	$minuto=date('i');
	$segundo=date('s');
	$fechainic=mktime($hora,$minuto,$segundo,rtrim($mese),rtrim($diae),rtrim($anioe));
	$fechaemision=date("Y-m-d H:i:s",$fechainic);
	/*Update order header for invoice charged on */
	$SQL = "UPDATE salesorders SET quotation=4, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	/*Now insert the DebtorTrans */
	$SQL = "INSERT INTO debtortrans (
			transno,
			type,
			debtorno,
			branchcode,
			trandate,
			prd,
			reference,
			tpe,
			order_,
			ovamount,
			ovgst,
			ovfreight,
			rate,
			invtext,
			shipvia,
			consignment,
			currcode,
			tagref,
			folio,
			origtrandate,
			flagdiscount,
			discountpercent
			)
		VALUES (
			". $InvoiceNo . ",
			" . $tipodefacturacion . ",
			'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
			'" . $_SESSION['Items'.$identifier]->Branch . "',
			'" . $DefaultDispatchDate . "',
			" . $PeriodNo . ",
			'',
			'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
			" .  $OrderNo . ",
			" . $_SESSION['Items'.$identifier]->total . ",
			" . $TaxTotal . ",
			" . $_POST['ChargeFreightCost'] . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . str_replace('CANCELADA','',DB_escape_string(strtoupper($_SESSION['Items'.$identifier]->Comments))) . "',
			" . $_SESSION['Items'.$identifier]->ShipVia . ",
			'"  . $OrderNo  . "',
			'" . $_SESSION['CurrAbrev'] . "',
			" . $_SESSION['Tagref'] .",
			'" . $folio . "',
			'" . $fechaemision . "',
			'" . $remisionmake . "',
			'" . $_SESSION['PercentDiscountGeneral'] . "'
		)";

	$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
	$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
 	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');	
	/* Insert the tax totals for each tax authority where tax was charged on the invoice */
	foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {
		$SQL = 'INSERT INTO debtortranstaxes (debtortransid,
							taxauthid,
							taxamount)
				VALUES (' . $DebtorTransID . ',
					' . $TaxAuthID . ',
					' . $TaxAmount . ')';
		// quite division de /$_SESSION['CurrencyRate']

		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE ESTE ERROR') . ': ' . _('El registro de los impuestos para el cliente no se realizo');
		$DbgMsg = _('El SQL utilizado para el registro de los impuestos es:');
 		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}
	/* If balance of the order cancelled update sales order details quantity. Also insert log records for OrderDeliveryDifferencesLog */
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		$seriesventa="";
		if ($_POST['BOPolicy']=='CAN'){
			$SQL = "UPDATE salesorderdetails
				SET quantity = quantity - " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . " WHERE orderno = " . $_SESSION['ProcessingOrder'] . " AND stkcode = '" . $OrderLine->StockID . "'";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El detalle de la orden de venta no se pudo actualizar');
			$DbgMsg = _('El SQL utilizado para el registro del detalle de la orden es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			if (($OrderLine->Quantity)>0){
				$SQL = "INSERT INTO orderdeliverydifferenceslog (
						orderno,
						invoiceno,
						stockid,
						quantitydiff,
						debtorno,
						branch,
						can_or_bo
						)
					VALUES (
						" .  $OrderNo . ",
						" . $InvoiceNo . ",
						'" . $OrderLine->StockID . "',
						" . ($OrderLine->Quantity) . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						'CAN'
						)";

				$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Las difeencias de entrega no se pudo realizar');
				$DbgMsg = _('El SQL utilizado para insertar las diferencias de la orden de entrega es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		} elseif (($OrderLine->Quantity - $OrderLine->QtyDispatched) >0 && DateDiff(ConvertSQLDate($DefaultDispatchDate),$_SESSION['Items']->DeliveryDate,'d') >0) {
		/*The order is being short delivered after the due date - need to insert a delivery differnce log */
			$SQL = "INSERT INTO orderdeliverydifferenceslog (
					orderno,
					invoiceno,
					stockid,
					quantitydiff,
					debtorno,
					branch,
					can_or_bo
				)
				VALUES (
					" . $OrderNo . ",
					" . $InvoiceNo . ",
					'" . $OrderLine->StockID . "',
					" . ($OrderLine->Quantity) . ",
					'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
					'" . $_SESSION['Items'.$identifier]->Branch . "',
					'BO'
				)";
			$ErrMsg =  '<br>' . _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Las difeencias de entrega no se pudo realizar');
			$DbgMsg = _('El SQL utilizado para insertar las diferencias de la orden de entrega es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} /*end of order delivery differences log entries */
		   
		if ($OrderLine->Quantity == ($OrderLine->QtyDispatched) and $_POST['ProcessOrder']== _('Facturar')){
			  $Completed = 1;
			} else {  /* order line is not complete */
			  $Completed = 0;
			}   
		/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */
		if ($OrderLine->QtyDispatched !=0 AND $OrderLine->QtyDispatched!="" AND $OrderLine->QtyDispatched) {
			// Test above to see if the line is completed or not
			$percentdiscountgral = GetPercentDiscount($OrderLine->StockID, $db);
			if($remisionmake==0){
				$percentdiscountgral=0;
			}
			if ($OrderLine->QtyDispatched>=0 OR $_POST['BOPolicy']=="CAN"){
				$SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
					actualdispatchdate = '" . $DefaultDispatchDate .  "',
					cashdiscount= '" . $percentdiscountgral .  "'
					/* completed= '" . $Completed .  "'*/
					
					WHERE orderno = " .  $OrderNo . "
					AND orderlineno = '" . $OrderLine->LineNumber . "'";
			} else {
				$SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
					actualdispatchdate = '" . $DefaultDispatchDate .  "',
					cashdiscount= '" . $percentdiscountgral .  "'
					 /* completed= '" . $Completed .  "'*/
					WHERE orderno = " .  $OrderNo . "
					AND orderlineno = '" . $OrderLine->LineNumber . "'";
			}
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El detalle de la orden de venta no se pudo actualizar');
			$DbgMsg = _('El SQL para actualizar el detalle de la orden de venta es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			 /* Update location stock records if not a dummy stock item need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'",$db,"<br>No se puede recuperar la bandera del producto");
			
			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];
			if ($MBFlag=="B" OR $MBFlag=="M") {
				$Assembly = False;
				/* Need to get the current location quantity
				will need it later for the stock movement */
               			$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $OrderLine->AlmacenStock . "'";
				$ErrMsg = _('WARNING') . ': ' . _('No se puede recuperar la sucursal del stock');
               			$Result = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);
				if (DB_num_rows($Result)==1){
                       			$LocQtyRow = DB_fetch_row($Result);
                       			$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must be some error this should never happen */
					$QtyOnHandPrior = 0;
				}
				$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $OrderLine->QtyDispatched . "
					WHERE locstock.stockid = '" . $OrderLine->StockID . "'
					AND loccode = '" . $OrderLine->AlmacenStock . "'";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
				$DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} elseif ($MBFlag=='A'){ /* its an assembly */
				/*Need to get the BOM for this part and make stock moves for the components then update the Location stock balances */
				$Assembly=True;
				
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT bom.component,
						bom.quantity,
						stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard,
						 stockmaster.longdescription as descripcion
					FROM bom, stockmaster
					WHERE bom.component=stockmaster.stockid
					AND bom.parent='" . $OrderLine->StockID . "'
					AND bom.effectiveto > '" . Date("Y-m-d") . "'
					AND bom.effectiveafter < '" . Date("Y-m-d") . "'";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo recuperar los componentes de montaje de la base de datos de'). ' '. $OrderLine->StockID . _('por que').' ';
				$DbgMsg = _('El SQL utilizado es:');
				$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
				while ($AssParts = DB_fetch_array($AssResult,$db)){
				
					//$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity will need it later for the stock movement */
	                  		$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $AssParts['component'] . "'
						AND loccode= '" . $OrderLine->AlmacenStock . "'";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede recuperar componentes y montaje de las cantidades ubicación de las existencias debido a ');
					$DbgMsg = _('El SQL que fallo es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	                  		if (DB_num_rows($Result)==1){
	                  			$LocQtyRow = DB_fetch_row($Result);
	                  			$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
					}
					if (empty($AssParts['standard'])) {
						$AssParts['standard']=0;
					}
					if ($_SESSION['TypeCostStock']==1){
						$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$AssParts['component'], $db);
					}else{
						$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
						$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$AssParts['component'], $db);
					}
					$OrderLine->StandardCost=$EstimatedAvgCost;
					
					$StandardCost += ($EstimatedAvgCost * $AssParts['quantity']) ;
					
					$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							prd,
							reference,
							qty,
							standardcost,
							show_on_inv_crds,
							newqoh,
							tagref,
							avgcost
						) VALUES (
							'" . $AssParts['component'] . "',
							 " . $tipodefacturacion . ",
							 " . $InvoiceNo . ",
							 '" . $OrderLine->AlmacenStock . "',
							 '" . $DefaultDispatchDate . "',
							 '" . $_SESSION['Items']->DebtorNo . "',
							 '" . $_SESSION['Items']->Branch . "',
							 " . $PeriodNo . ",
							 '" . _('Ensamble') . ': ' . $OrderLine->StockID . ' ' . _('Orden') . ': ' . $OrderNo . "',
							 " . -$AssParts['quantity'] * $OrderLine->QtyDispatched . ",
							 " . $EstimatedAvgCost . ",
							 0,
							 " . ($QtyOnHandPrior -($AssParts['quantity'] * $OrderLine->QtyDispatched)) . ",
							 " .$_SESSION['Tagref'] .",
							 '" .$EstimatedAvgCost ."'
						)";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
					$DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
					$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
					// INSERTAR MOVIMIENTO CONTABLE POR CADA PRODUCTO DEL ENSAMBLE
					include('ProcessAssemblyInvoice.inc');	
					/***********************************************************/
					$SQL = "UPDATE locstock
						SET quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->QtyDispatched . "
						WHERE locstock.stockid = '" . $AssParts['component'] . "'
						AND loccode = '" . $OrderLine->AlmacenStock . "'";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registro de las existencias del almacen no se puede actualizar para un componente de ensamble, por que');
					$DbgMsg = _('El siguiente SQL para actualizar el registro de las existencias por almacen para el componente de ensamble es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					$totalseriessol=0;
					$totalserie=0;
					foreach($OrderLine->SerialItems as $Item){
						if (strtoupper($AssParts['component'])==strtoupper($Item->StockIDParent)){
							$SQL = "UPDATE stockserialitems
									SET quantity= quantity - " . $Item->BundleQty . "
									WHERE stockid='" . $AssParts['component'] . "'
									AND loccode='" . $OrderLine->AlmacenStock . "'
									AND serialno='" . $Item->BundleRef . "'";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
							$DbgMsg = _('El siguiente SQL para actualizar el numero de serie del stock es:');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							//echo "serials:<br>".$SQL."<br>";
							/* now insert the serial stock movement */
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
												stockid,
												serialno,
												moveqty,
												standardcost
												)
								VALUES (" . $StkMoveNo . ",
									'" . $AssParts['component'] . "',
									'" . $Item->BundleRef . "',
									" . -$Item->BundleQty .",
									" . $Item->CostSerialItem .
									")";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede insertar por que');
							$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							$totalserie=$totalserie+$Item->CostSerialItem;
							$totalseriessol=$totalseriessol+$Item->BundleQty;
							$SQL = "UPDATE stockmoves
							SET standardcost=  " . $Item->CostSerialItem . "
							WHERE stkmoveno=" . $StkMoveNo ;
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
							$DbgMsg = _('El siguiente SQL para actualizar el numero de serie del stock es:');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							
							$SQL = "UPDATE salesorderdetails
								SET narrative = CONCAT(narrative,' , No Serie: ','" . $Item->BundleRef . "')
								WHERE orderno = " .  $OrderNo . "
								AND orderlineno = '" . $OrderLine->LineNumber . "'";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie en la orden de venta no se puede actualizar por que');
							$DbgMsg = _('El siguiente SQL para actualizar el numero de serie de venta es:');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							$seriesventa= $seriesventa. " |No Serie:".$Item->BundleRef;
						// actualizamos el costo del numero de serie
						}
						
						$StandardCost=$totalserie/$totalseriessol;
						$OrderLine->StandardCost=$StandardCost;
					}
				} /* end of assembly explosion and updates */
				/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
				$OrderLine->StandardCost = $StandardCost;
			} /* end of its an assembly */
			//echo "entro a mov de inventarios"    ;
			// Insert stock movements - with unit cost
			$LocalCurrencyPrice= ($OrderLine->Price);// / $_SESSION['CurrencyRate']);
			if (empty($OrderLine->StandardCost)) {
				$OrderLine->StandardCost=0;
			}
			// Suma descuentos en cascada en el campo de descuento para los movimientos de stock
			$montodescuento = $OrderLine->QtyDispatched * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
			//DESCUENTO DOS
			$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
			//DESCUENTO TRES
			$montodescuento=$montodescuento * (1 - $OrderLine->DiscountPercent2);
			$montodescuento=($OrderLine->QtyDispatched * $OrderLine->Price)-$montodescuento;
			if ($_SESSION['TypeCostStock']==1){
				$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$OrderLine->StockID, $db);
			}else{
				$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
				$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
			}
			if ($OrderLine->costoproducto!=0){
				$OrderLine->StandardCost=$OrderLine->costoproducto;
			}
			$costoproducto=$OrderLine->StandardCost;
			if ($MBFlag=='B' OR $MBFlag=='M'){
            			$SQL = "INSERT INTO stockmoves (
						stockid,
						type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
						price,
						prd,
						reference,
						qty,
						discountpercent,
						discountpercent1,
						discountpercent2,
						totaldescuento,
						standardcost,
						newqoh,
						warranty,
						narrative,
						tagref,
						avgcost
						)
					VALUES ('" . $OrderLine->StockID . "',
						" . $tipodefacturacion . ",
						" . $InvoiceNo . ",
						'" . $OrderLine->AlmacenStock . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						" .  $LocalCurrencyPrice . ",
						" .  $PeriodNo . ",
						'" . $OrderNo . "',
						" . -$OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->DiscountPercent1 . ",
						" . $OrderLine->DiscountPercent2 . ",
						" . $montodescuento . ",
						" . $costoproducto . ",
						" . ($QtyOnHandPrior - $OrderLine->QtyDispatched) . ",
						" . $OrderLine->warranty . ",
						'" . DB_escape_string($OrderLine->Narrative) . "',
						" . $_SESSION['Tagref'] . ",
						'" . $EstimatedAvgCost . "'
						)";
						
			} else {
			// its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
				if (empty($OrderLine->StandardCost)) {
					$OrderLine->StandardCost=0;
				}
				// producto de ensamble con el total del costo calculado por cada componente
				if ($MBFlag=='A'){
				 $costoproducto=$StandardCost;
				 $EstimatedAvgCost=$StandardCost;
				 $OrderLine->StandardCost=$StandardCost;
				}
				$SQL = "INSERT INTO stockmoves (
						stockid,
						type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
						price,
						prd,
						reference,
						qty,
						discountpercent,
						discountpercent1,
						discountpercent2,
						totaldescuento,
						standardcost,
						warranty,
						narrative,
						tagref,
						avgcost
						)
					VALUES ('" . $OrderLine->StockID . "',
						" . $tipodefacturacion . ",
						" . $InvoiceNo . ",
						'" . $OrderLine->AlmacenStock . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . $OrderNo . "',
						" . -$OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->DiscountPercent1 . ",
						" . $OrderLine->DiscountPercent2 . ",
						" . $montodescuento . ",
						" . $costoproducto . ",
						" . $OrderLine->warranty . ",
						'" . DB_escape_string(htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)) . "',
						" . $_SESSION['Tagref'] . ",
						" . $EstimatedAvgCost . "
						)";
			}
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de stock no puede insertarse, por que');
			$DbgMsg = _('El siguiente SQL para insertar la contabilidad deL movimiento existencias utilizado es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
			/*Insert the taxes that applied to this line */
			foreach ($OrderLine->Taxes as $Tax) {
				$SQL = 'INSERT INTO stockmovestaxes (stkmoveno,
									taxauthid,
									taxrate,
									taxcalculationorder,
									taxontax)
						VALUES (' . $StkMoveNo . ',
							' . $Tax->TaxAuthID . ',
							' . $Tax->TaxRate . ',
							' . $Tax->TaxCalculationOrder . ',
							' . $Tax->TaxOnTax . ')';
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
				$DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
			/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
			if ($OrderLine->Controlled ==1){
			        /*We need to add the StockSerialItem record and The StockSerialMoves as well */
			        if($MBFlag!='A'){
					foreach($OrderLine->SerialItems as $Item){
						$SQL = "UPDATE stockserialitems
								SET quantity= quantity - " . $Item->BundleQty . "
								WHERE stockid='" . $OrderLine->StockID . "'
								AND loccode='" . $OrderLine->AlmacenStock . "'
								AND serialno='" . $Item->BundleRef . "'";
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
						$DbgMsg = _('El siguiente SQL para actualizar el numero de serie del stock es:');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						/* now insert the serial stock movement */
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
											stockid,
											serialno,
											moveqty,
											standardcost
											)
							VALUES (" . $StkMoveNo . ",
								'" . $OrderLine->StockID . "',
								'" . $Item->BundleRef . "',
								" . -$Item->BundleQty .",
								" . $Item->CostSerialItem .
								")";
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede insertar por que');
						$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						// actualizamos el costo del numero de serie
						$SQL = "UPDATE stockmoves
							SET standardcost=  " . $Item->CostSerialItem . "
							WHERE stkmoveno=" . $StkMoveNo ;
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
						$DbgMsg = _('El siguiente SQL para actualizar el numero de serie del stock es:');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						$totalserie=$totalserie+$Item->CostSerialItem;
						$totalseriessol=$totalseriessol+$Item->BundleQty;
						$seriesventa= $seriesventa. " |No Serie:".$Item->BundleRef;
						$OrderLine->StandardCost=$totalserie/$totalseriessol;
					}/* foreach controlled item in the serialitems array */
				}// fin de ensamble
				else{
					$SQL = "UPDATE stockmoves
						SET standardcost=  " . $totalserie . "
						WHERE stkmoveno=" . $StkMoveNo ;
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
					$DbgMsg = _('El siguiente SQL para actualizar el numero de serie del stock es:');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
				}
			} /*end if the orderline is a controlled item */
			/*Insert Sales Analysis records */
			$SQL="  SELECT COUNT(*),
					salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson
				FROM salesanalysis,
					custbranch,
					stockmaster
				WHERE salesanalysis.stkcategory=stockmaster.categoryid
					AND salesanalysis.stockid=stockmaster.stockid
					AND salesanalysis.cust=custbranch.debtorno
					AND salesanalysis.custbranch=custbranch.branchcode
					AND salesanalysis.area=custbranch.area
					AND salesanalysis.salesperson=custbranch.salesman
					AND salesanalysis.typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
					AND salesanalysis.periodno=" . $PeriodNo . "
					AND salesanalysis.cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					AND salesanalysis.custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
					AND salesanalysis.stockid " . LIKE . " '" . $OrderLine->StockID . "'
					AND salesanalysis.budgetoractual=1
				GROUP BY salesanalysis.stockid,
					salesanalysis.stkcategory,
					salesanalysis.cust,
					salesanalysis.custbranch,
					salesanalysis.area,
					salesanalysis.periodno,
					salesanalysis.typeabbrev,
					salesanalysis.salesperson";
			$ErrMsg = _('El recuento de registros actuales para el analisis de ventas no se pudo recuperar por que');
			$DbgMsg = '<br>'. _('El SQL para contar el numero de registros de analisis de ventas es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$myrow = DB_fetch_row($Result);
			if ($myrow[0]>0){  /*Update the existing record that already exists */
				$SQL = "UPDATE salesanalysis
					SET amt=amt+" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
					cost=cost+" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
					qty=qty +" . $OrderLine->QtyDispatched . ",
					disc=disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
					WHERE salesanalysis.area='" . $myrow[5] . "'
					AND salesanalysis.salesperson='" . $myrow[8] . "'
					AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
					AND periodno = " . $PeriodNo . "
					AND cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					AND custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
					AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
					AND salesanalysis.stkcategory ='" . $myrow[2] . "'
					AND budgetoractual=1";
			} else { /* insert a new sales analysis record */
				// cambio de los descuentos en cascada    
				$totalsindescuento=($OrderLine->Price * $OrderLine->QtyDispatched)/$_SESSION['CurrencyRate'];
				$montodescuento= $OrderLine->QtyDispatched * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
				$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
				$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent2);
				$montodescuento=$totalsindescuento-$montodescuento;
				$montodescuento=$montodescuento/$_SESSION['CurrencyRate'];
				$SQL = "INSERT INTO salesanalysis (
						typeabbrev,
						periodno,
						amt,
						cost,
						cust,
						custbranch,
						qty,
						disc,
						stockid,
						area,
						budgetoractual,
						salesperson,
						stkcategory
						)
					SELECT '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
						" . $PeriodNo . ",
						" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
						" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . $_SESSION['Items'.$identifier]->Branch . "',
						" . $OrderLine->QtyDispatched . ",
						" . $montodescuento . ",
						'" . $OrderLine->StockID . "',
						custbranch.area,
						1,
						custbranch.salesman,
						stockmaster.categoryid
					FROM stockmaster,
						custbranch
					WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
					AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					AND custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'";
			}
			$ErrMsg = _('El analisis de ventas no se pudo registrar por que');
			$DbgMsg = _('El siguiente SQL para actualizar el analisis de ventas es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/
			if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0 and $MBFlag!='A'){
/*first the cost of sales entry*/
				$tipoalmacen = GetTypeLocation($OrderLine->AlmacenStock,$db);
				//echo 'tipoalmacen:'.$tipoalmacen.'<br>';
				if ($tipoalmacen!=2){
				     $cuentainventario=GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
				}else{
				     $cuentainventario=GetCOGSGLAccountConsigment($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
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
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".DB_escape_string($OrderLine->ItemDescription)." x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . " |".$seriesventa. "',
						" . $OrderLine->StandardCost * $OrderLine->QtyDispatched . ",
						" . $_SESSION['Tagref'] . "
					)";

				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
/*now the stock entry*/
				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);
				if ($tipoalmacen!=2){
				    $cuentaalmacen=$StockGLCode['stockact'];
				}else{
				    $cuentaalmacen=$StockGLCode['stockconsignmentact'];
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
						" . $cuentaalmacen . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".DB_escape_string($OrderLine->ItemDescription) . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . " |".$seriesventa. "',
						" . (-$OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
						" . $_SESSION['Tagref'] . "
					)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} /* end of if GL and stock integrated and standard cost !=0 */
			// calcula total de descuentos
			$totalsindescuento=($OrderLine->Price * $OrderLine->QtyDispatched); // /$_SESSION['CurrencyRate']
			$montodescuento= $OrderLine->QtyDispatched * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
			$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
			$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent2);
			
			$montodescuentoxcategory=$montodescuento;
			$montodescuento=$totalsindescuento-$montodescuento;
			$percentdiscountgral = GetPercentDiscount($OrderLine->StockID, $db);
			//echo '<br>total de descuento'.$percentdiscountgral;
			if($remisionmake==0){
				$percentdiscountgral=0;
			}
			//echo '<br>total de descuento make'.$percentdiscountgral;
			$totaldesccategorytemp=0;
			if($percentdiscountgral>0){
					$totaldesccategorytemp=($montodescuentoxcategory*$percentdiscountgral)/100;
			}
			$totaldesccategory=$totaldesccategory+($totaldesccategorytemp);
			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){
			//Post sales transaction to GL credit sales
			    
				/**************************************************/
				// dividir la parte del descuento
				/**************************************************/
				$SDescuento=($OrderLine->Price * $OrderLine->QtyDispatched/$_SESSION['CurrencyRate']);
				$SDescuento=$SDescuento-$totaldesccategorytemp;
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
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
						" . $SalesGLAccounts['salesglcode'] . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".DB_escape_string($OrderLine->ItemDescription) . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . " @ " . (1/$_SESSION['CurrencyRate']) . " |". $seriesventa."',
						" . -$SDescuento. ",
						" . $_SESSION['Tagref'] . "
					)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
				$DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				/**************************************************/
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
						" . $SalesGLAccounts['salesgldiscount'] . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".DB_escape_string($OrderLine->ItemDescription) . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . " @ " . (1/$_SESSION['CurrencyRate']) . " |". $seriesventa."',
						" . -$totaldesccategorytemp . ",
						" . $_SESSION['Tagref'] . "
					)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
				$DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
				//$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				/**************************************************/
				
				
				//echo '<br>total de descuento'.$totaldesccategory;
				$montodescuento=$montodescuento/$_SESSION['CurrencyRate'];
				// concatena texto de descripcion del movimiento
				$textodescuento= $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID ." | ".DB_escape_string($OrderLine->ItemDescription);		
				$textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent * 100). " % ";
				$textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent1 * 100)." % ";
				$textodescuento= $textodescuento . " @ " . ($OrderLine->DiscountPercent2 * 100)." % ";
				$textodescuento= $textodescuento . " | " . $seriesventa;
				if ($OrderLine->DiscountPercent!=0 or $OrderLine->DiscountPercent1!=0 or $OrderLine->DiscountPercent2!=0 ){
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
							" . $SalesGLAccounts['discountglcode'] . ",
							'".$textodescuento. "',
							" . $montodescuento . ",
							" . $_SESSION['Tagref'] . "
						)";
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El descuento de la venta no se ha insertado, por que');
					$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */
	   
	if ($_SESSION['CompanyRecord']['gllink_debtors']==1 ){
/*Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales */
		// Agrega a Contabilidad el movimiento de factura de contado/credito
		if (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal) !=0) {
				$montofacturatotal=($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost  )/$_SESSION['CurrencyRate'];
				$montofacturatotal=$montofacturatotal-$totaldesccategory;
				$TaxAmountD=$totaldesccategory*$TaxTotal/$_SESSION['Items'.$identifier]->total;
				$TaxAmountSD=$TaxAmount-$TaxAmountD;
			$tipocliente=ExtractTypeClient($_SESSION['Items'.$identifier]->DebtorNo,$db);
			$cuentadescuento=ClientAccount($tipocliente,'gl_categorydiscount',$db);//$_SESSION['CompanyRecord']['gllink_notesdebtors'];
			$cuentacargo=ClientAccount($tipocliente,'gl_accountcontado',$db);
			$totalclientes=(($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal));
			$totalclientes=$totalclientes-$taxret;
			$totalclientes=$totalclientes/$_SESSION['CurrencyRate'];
			
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
						" .  $cuentacargo . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " @ ".$_SESSION['Items'.$identifier]->CustomerName."',
						" . ($totalclientes) . ",
						" . $_SESSION['Tagref'] . "
					)";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			if($totaldesccategory>0){
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
						" .  $cuentadescuento . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " @".$_SESSION['Items'.$identifier]->CustomerName."',
						" . ($totaldesccategory+$TaxAmountD) . ",
						" . $_SESSION['Tagref'] . "
					)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				//$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
// ***************************************** inserta en movimientos de debtortrasnmov para estado de cuenta*********************************************
			$GLTransID = DB_Last_Insert_ID($db,'gltrans','counterindex');
			// Insertar en debtortransmov para consulta de estado de cuenta
			$SQL = "INSERT INTO debtortransmovs (
					transno,
					type,
					debtorno,
					branchcode,
					trandate,
					prd,
					reference,
					tpe,
					order_,
					ovamount,
					ovgst,
					ovfreight,
					rate,
					invtext,
					shipvia,
					consignment,
					currcode,
					tagref,
					idgltrans,
					origtrandate,
					userid
					)
				VALUES (
					". $InvoiceNo . ",
					" . $tipodefacturacion . ",
					'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
					'" . $_SESSION['Items'.$identifier]->Branch . "',
					'" . $DefaultDispatchDate . "',
					" . $PeriodNo . ",
					'',
					'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
					" .  $OrderNo . ",
					" . $_SESSION['Items'.$identifier]->total . ",
					" . $TaxTotal . ",
					" . $_POST['ChargeFreightCost'] . ",
					" . $_SESSION['CurrencyRate'] . ",
					'" . $_POST['InvoiceText'] . "',
					" . $_SESSION['Items'.$identifier]->ShipVia . ",
					'"  . $OrderNo  . "',
					'" . $_SESSION['CurrAbrev'] . "',
					" . $_SESSION['Tagref'] .",
					" . $GLTransID . ",
					'"  . $fechaemision . "',
					'"  . $_SESSION['UserID'] . "'
				)";
			$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
			$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			//echo "sql:".$sql;
		}
		/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */
		if ($_SESSION['Items']->FreightCost !=0) {
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
					" . $_SESSION['CompanyRecord']['freightact'] . ",
					'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
					" . (-($_SESSION['Items'.$identifier]->FreightCost)/$_SESSION['CurrencyRate']) . ",
					" . $_SESSION['Tagref'] . "
				)";

			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de flete de la venta no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		foreach ( $TaxTotals as $TaxAuthID => $TaxAmount){
			if ($TaxAmount !=0 ){
				// descuento de iva separado
				
				$TaxAmountD=$totaldesccategory*$TaxAmount/$_SESSION['Items'.$identifier]->total;
				$TaxAmountSD=$TaxAmount-$TaxAmountD;
				
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
						" . $TaxGLCodes[$TaxAuthID] . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						" . (-$TaxAmountSD/$_SESSION['CurrencyRate']) . ",
						" . $_SESSION['Tagref'] . "
					)";

				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
				/******************************************/
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
						" . $TaxGLCodesDiscount[$TaxAuthID] . ",
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						" . (-$TaxAmountD/$_SESSION['CurrencyRate']) . ",
						" . $_SESSION['Tagref'] . "
					)";

				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
				$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
				
			}
		}
	} /*end of if Sales and GL integrated */
	
// ************************************** INICIO DE ANTICIPOS *****************************************************************
	$anticipo=0;
	$total_saldar=$TaxTotal+$_SESSION['Items'.$identifier]->total;
	if (isset($_POST['anticipo'])){
		if ($_POST['anticipo']>0 and strlen($_POST['anticipo'])>0 and is_numeric($_POST['anticipo']) and $_POST['anticipo']< $total_saldar) {
			if ($tipodefacturacion==10){
				// aplica solo para documentos de tipo 10 donde la venta
				$anticipo=$_POST['anticipo'];
			}
			else
			{
				$anticipo=0;
			}
		} else
		{
			$anticipo=0;
		}
	}	
/**************************************** FIN DE ANTICIPOS *****************************************************************/
/*************************************** INICIO DE backup replicacion ******************************************************/
	
// *************************************************************************************************************************/
// ***********************************************FIN DE FACTURACION DIRECTA *****************************************************************

	if (isset($_POST['ProcessOrder']) && $_POST['ProcessOrder']== _('Prestamo') ){
		
		prnMsg(_('Se ha generado el Numero de Factura:') . ' ' . $InvoiceNo . ' ' . _(' con exito'),'success');
		
				
		// imprime pagares que genero la factura
		echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Alta de recibo de pago') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/CustomerReceiptcls4.php?Search=auto' . SID .'&debtorno=' . $_SESSION['Items'.$identifier]->DebtorNo .'&branchcode='.$_SESSION['Items'.$identifier]->Branch . '">'. _('Alta de recibo de pago') .'</a>';
		
		// *************************************************************************************************************
		// ************************** Manda a Traer la Facturacion Electronica******************************************
		// Consulta el rfc y clave de facturacion electronica
		$SQL=" SELECT l.taxid,l.address5,t.tagname,t.areacode,l.legalid,t.typeinvoice
		       FROM legalbusinessunit l, tags t
		       WHERE l.legalid=t.legalid AND tagref='".$_SESSION['Tagref']."'";
		$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$rfc=trim($myrowtags['taxid']);
			$keyfact=$myrowtags['address5'];
			$nombre=$myrowtags['tagname'];
			$area=$myrowtags['areacode'];
			$legaid=$myrowtags['legalid'];
			$tipofacturacionxtag=$myrowtags['typeinvoice'];
		}
		//****//
		$InvoiceNoTAG = DocumentNext($tipodefacturacion, $_SESSION['Tagref'],$area,$legaid, $db);
		$separa = explode('|',$InvoiceNoTAG);
		$serie = $separa[1];
		$folio = $separa[0];
		
		$factelectronica= XSAInvoicing($InvoiceNo, $OrderNo, $_SESSION['Items'.$identifier]->DebtorNo, $tipodefacturacion,$_SESSION['Tagref'],$serie,$folio, $db);
		// Envia  los datos al archivooooo
		$mitxt = "txt_fact/Fact".$tipodefacturacion.'_'.$_SESSION['Tagref'].'_'.$InvoiceNo.".txt"; //Ponele el nombre que quieras al archivo
		//$myfile=$mitxt; //Ponele el nombre que quieras al archivo
		$myfile="Fact".$tipodefacturacion.'_'.$_SESSION['Tagref'].'_'.$InvoiceNo.".txt"; //Ponele el nombre que quieras al archivo
		$factelectronica=utf8_encode($factelectronica);
		//$fp = fopen($mitxt,"w");
		//fwrite($fp,$factelectronica);
		//fclose($fp);
		$codigoref=strtoupper($_SESSION['Items'.$identifier]->CustRef);
		$tipoarea=add_cerosstring($area,2);
		$translegal=add_cerosstring($legaid,2);
		$cuentareferenciada=$translegal.$tipoarea.$codigoref;
		//extrae banco activo para cuentas referenciadas
		$sql="Select * from bancosreferencia where active=1";
		$result= DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($result)!=0)
		{
			while ($myrowcuenta = DB_fetch_array($result,$db)){
				$bankid=$myrowcuenta['bancoid'];
				// genera digito verificador
				$cuentaref = strtoupper($cuentareferenciada.GeneraCuentaReferenciada($db,$cuentareferenciada,$bankid));
				// inserta en tabla de referencias bancarias
				$insertarefe=InsertaCuentaBank($cuentaref,$DebtorTransID,$bankid,$db);				
			}
		}
		//Actualizar el documento para folio
		$SQL="UPDATE debtortrans
		      SET folio='" . $serie.'|'.$folio . "',
			  ref1='" . $cuentaref. "'
		      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		$SQL = "UPDATE salesorders SET quotation=5, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if ($_SESSION['InvoiceTask']==1){
		// Funcion para insertar tarea
			//InsertTareas($OrderNo,$OrderLine->ItemDescription,$_SESSION['Items'.$identifier]->CustomerName,$db);
			$descripciontarea = " " . $_SESSION['Items'.$identifier]->CustomerName . "; Pedido: " . $OrderNo . "; Folio SAT: " . $serie.'|'.$folio;
			$fechaEntrega=CalculaTiempo($OrderNo,$tipodefacturacion,$InvoiceNo,$db);
			$separa=explode('|',$fechaEntrega);
			$diastarea = $separa[1];
		 	$fechatarea = $separa[0];
			$estimado=$separa[2];
			InsertTareas($OrderNo,$tipodefacturacion,$descripciontarea,$fechatarea,$diastarea,$estimado,$db);
		//exit();
		}
		/*** COMMIT ***/		
		//$Result = DB_Txn_Commit($db);		
		$empresa=$keyfact.'-'.$rfc;
		$nombre=$nombre;
		$tipo='Factura';
		$myfile=$myfile;
		$factelectronica=$factelectronica;
		
		// Consulta el total de productos vendidos
		$SQL=" SELECT sum(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS totalprd
		       FROM salesorderdetails
		       WHERE orderno='".$OrderNo."'";
		$Result= DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$totalprd=trim($myrowtags['totalprd']);
		}
		//****//
		if ($totalprd==0){
		 $SQL = "UPDATE salesorders SET quotation=5, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
		}else{
		 $SQL = "UPDATE salesorders SET quotation=6, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . ", productos pendientes') WHERE orderno= " .  $OrderNo;
		}
		
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		$param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
			//$XMLElectronico=generaXML($factelectronica,'ingreso',$_SESSION['Tagref'],$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
			$liga="PDFInvoice.php?&clave=chequepoliza_sefia";
			$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'.$liga.'&OrderNo='.$OrderNo.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Nota de Prestamo') . ' (' . _(' Laser') . ')' .'</a>';
		
		echo $liga;
		if ($_SESSION['Paypromissory']==1){
			include('ProcessPromissory.inc');	
		}
		
		/*INICIO:
		SE AGREGO ESTA CONDICION, EN CASO DE SER FACTURA DE CONTADO, SE REDIRECCIONA LA PAGINA DE PAGO RAPIDO
		*/
		if($remisionmake==1){
				include('ProcessRemisionCash.inc');
		}else{
				$Result=DB_Txn_Commit($db);
			//echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CustomerReceiptFacturaContado.php?transnofac=' . $InvoiceNo . '&debtorno=' . $_SESSION['Items'.$identifier]->DebtorNo . '&branchcode=' . $_SESSION['Items'.$identifier]->Branch . '&tag=' . $_SESSION['Tagref'] . '&typeinvoice=' . $tipodefacturacion .'">';
		}
		
		# INICIO Mandar correo de notificación
		if(function_exists("emailNotifier")) {
			emailNotifier($_POST['ProcessOrder'], $OrderNo, $db);
		}
		# FIN Mandar correo de notificación
		
		/*FIN:
		SE AGREGO ESTA CONDICION, EN CASO DE SER FACTURA DE CONTADO, SE REDIRECCIONA LA PAGINA DE PAGO RAPIDO
		*/
		// se agrega la condicion de generar pagares con edicion de fechas	
		
		// *************************************************************************************************************	

	} else {
		if ($_SESSION['Items'.$identifier]->Quotation==1){
			prnMsg(_('<B>LA COTIZAZION NO.') . ' ' . $OrderNo . ' ' . _('HA SIDO PROCESADA EXITOSAMENTE'),'success');
		} else {
			prnMsg(_('<B>LA ORDEN NO.') . ' ' . $OrderNo . ' ' . _('HA SIDO PROCESADA EXITOSAMENTE'),'success');
		}
		if ($_POST['Quotation']==0) { /*then its not a quotation its a real order */
			echo '<div style="text-align:center">';
			  echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir Orden de Venta') . '" alt="">' . ' ' . '<a target="_blank" href="' . $rootpath . '/PDFSalesOrderQuotePageHeader.php?' . SID .'identifier='.$identifier . '&TransNo=' . $OrderNo .'&verplaca='.$verplaca .'&verkilometraje='.$verkm .'&verserie='.$verserie .'&vercomentarios='.$vercomentarios. '">'. _('<b>Imprimir Orden de Venta No.') . $OrderNo .'</a>';
				#echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Factura') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'identifier='.$identifier . '&ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden No.').' ' . $OrderNo .'</a></div>';
			  echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			echo '</div>';
			echo '<div style="text-align:center" bgcolor="#81F781">';
			  echo '<p><a href="' . $rootpath . '/PO_Header.php?' . SID .'identifier='.$identifier . '&NewOrder=Yes&TieToOrderNumber=' . $OrderNo .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'"><b>'. _('GENERAR ORDEN DE COMPRA A PROVEEDOR'). '</a></div>';
			echo '</div>';  
		} else {
			echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/reports.png" title="' . _('Imprimir Cotizacion') . '" alt="">' . ' ' . '<a href="' . $rootpath . '/PDFSalesOrderQuotePageHeader.php?' . SID .'identifier='.$identifier . '&TransNo=' . $OrderNo .'&verplaca='.$verplaca .'&verkilometraje='.$verkm .'&verserie='.$verserie .'&vercomentarios='.$vercomentarios. '">'. _('Imprimir Cotizacion No. ') . $OrderNo .'</a>';
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'identifier='.$identifier . '&ModifyOrderNumber=' . $OrderNo .'"><b>'. _('Regresar a la Cotizacion No. ') . $OrderNo .'</a></div>';
		}
		
/*** COMMIT ***/
		$Result=DB_Txn_Commit($db);
	}
		
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	unset($_SESSION['CurrAbrev']);
	unset($_SESSION['Tagref']);
	unset($_SESSION['TagName']);
	unset($_SESSION['CurrencyName']);
	unset($_SESSION['SalesName']);
	include('includes/footer.inc');
	exit;
?>