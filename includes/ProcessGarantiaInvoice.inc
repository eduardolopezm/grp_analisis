<?
/* 
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 09-FEB-2010
 CAMBIOS: 
	1. SE MODIFICO PARA QUE EL PRECIO LO TOME DEL COSTO MAS EL INCREMENTO DE COSTO
 FIN DE CAMBIOS
 
*/

$tipodefacturacion=118;
if (isset($_SESSION['ExistingOrder']) and $_SESSION['ExistingOrder']!=0){
    $OrderNo=$_SESSION['ExistingOrder'];
}
if ($_SESSION['ForzarCapturaRFC']==1){
		if (strlen($_SESSION['Items'.$identifier]->CustRef)==0){
			
			$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
			$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
			$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			$InputErrors =1;
			prnMsg(_('El RFC es necesario para realizar el alta de la factura'),'error');
			echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber=' . $OrderNo  .'"><b>'. _('Regresar a la Orden de Venta No.').' ' . $OrderNo .'</a>';
			include('includes/footer.inc');
			exit ;
		}
}
  
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
		if ($totalserie<$OrderLine->Quantity){
			$QuantityControlled =false;
			break;
		}
		
	}
}
  
if ($QuantityControlled==false){
	$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	prnMsg( _('No ha ingresado los numeros de serie por lo tanto,') . ' ' . _('no ha sido posible facturar, intentelo nuevamente'),'error');
	echo '<p><a href="' . $rootpath . '/SelectOrderItemsV4_0.php?' . SID .'identifier=' . $identifier .'"><b>'. _('Regresar a modificar la orden de venta') .'</a>';
	include('includes/footer.inc');
	exit;
}
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
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
}
  
$TaxTotal =0;
$TaxTotals = array();
$TaxGLCodes = array();
$TaxLineTotal=0;
$totalfactura=0;
foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
	// DESCUENTO UNO
	// se agrego que el precio cambie de acuerdo al costo promedio mas un porcentaje por categoria
	$sql="SELECT warrantycost
	      FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
	      AND stockid= '".$OrderLine->StockID."'";
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Result)==1){
	    $DescuentoRow = DB_fetch_row($Result);
	    $DescuentoCat =  $DescuentoRow[0];
	} 
	if ($_SESSION['TypeCostStock']==1){
		$EstimatedAvgCostCat=ExtractAvgCostXtag($_SESSION['Tagref'],$OrderLine->StockID, $db);
	}else{
		$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
		$EstimatedAvgCostCat=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
	}
	
	
	$OrderLine->Price=$EstimatedAvgCostCat+(($EstimatedAvgCostCat*$DescuentoCat)/100);
	//Actualizo los precios de cada partida
	$SQL = "UPDATE salesorderdetails
		    SET unitprice='".$OrderLine->Price."'
		    WHERE orderno= " .  $OrderNo."
			AND orderlineno=".$OrderLine->LineNumber;
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
	
	$LineTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
	//DESCUENTO DOS
	$LineTotal=$LineTotal * (1 -$OrderLine->DiscountPercent1);
	//DESCUENTO TRES
	$LineTotal=$LineTotal * (1 - $OrderLine->DiscountPercent2);
	$totalfactura=$totalfactura+$LineTotal;
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
	}
}

$TaxTotal = $TaxLineTotal;
$_SESSION['Items'.$identifier]->total =$totalfactura;
//actualizo el impuesto de la orden de compra
$SQL = "UPDATE salesorders
	SET taxtotal='".$TaxTotal."'
	WHERE orderno= " .  $OrderNo;
$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

if ($_SESSION['LimitxDepto']==1 and $tipodefacturacion==10){
// limites por departamento
	$limitexdeptoxclient=LimitXDeptoYClient($_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Tagref'],$db);
	$totalxfactxclien=$_SESSION['Items'.$identifier]->total+$TaxTotal;
	$depto=DeptoXtag($_SESSION['Tagref'],$db);
	$saldodeuda=SaldoClientxDepto($_SESSION['Items'.$identifier]->DebtorNo,$depto,$_SESSION['BaseDataware'],$db);
	$totalxfactxclien=$saldodeuda+$totalxfactxclien;
	if ($limitexdeptoxclient<$totalxfactxclien){
		$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
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
	$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
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
	    		$sqlprodxalm = " SELECT sum(salesorderdetails.quantity) AS totalprd
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
	    
		$SQL = "SELECT stockmaster.description,
				locstock.quantity,
				stockmaster.mbflag
			FROM locstock
				INNER JOIN stockmaster
				ON stockmaster.stockid=locstock.stockid
			WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
				AND locstock.loccode='" . $OrderLine->AlmacenStock . "'";
		$ErrMsg = _('No se puede recuperar la cantidad para facturar');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		$CheckNegRow = DB_fetch_array($Result);
		if ($CheckNegRow['mbflag']=='B' OR $CheckNegRow['mbflag']=='M'){
			if ($CheckNegRow['quantity'] < $QtyOrder and $OrderLine->RedInvoice==0){
				prnMsg( _('No se puede facturar en rojo, ya que asi lo establecen los parametros del sistema.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('No se admite facturaciones en cantidades de inventario negativas'));
				$NegativesFound = true;
				
				$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
				$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
				$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
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
					locstock.quantity-(" . $OrderLine->Quantity  . "*bom.quantity) AS qtyleft
				FROM bom
					INNER JOIN locstock
					ON bom.component=locstock.stockid
					INNER JOIN stockmaster
					ON stockmaster.stockid=bom.component
				WHERE bom.parent='" . $OrderLine->StockID . "'
					AND locstock.loccode='" . $OrderLine->AlmacenStock . "'
					AND effectiveafter <'" . Date('Y-m-d') . "'
					AND effectiveto >='" . Date('Y-m-d') . "'";
			
			$ErrMsg = _('No se pueden facturar ordenes de trabajo en negativo');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			while ($NegRow = DB_fetch_array($Result)){
				if ($NegRow['qtyleft']<0 and $OrderLine->RedInvoice==0){
					prnMsg(_('No se puede facturar en rojo, ya que asi lo establecen los parametros del sistema.'),'error',$NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('No se admite facturaciones en cantidades de inventario negativas'));
					$NegativesFound = true;
					// deja la orden como cotizacion
					$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
					$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
					$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
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
		$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
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
		$SQL = "UPDATE salesorders SET quotation=1 WHERE orderno= " .  $OrderNo;
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
$SQL = "SELECT stkcode,
		quantity,
		qtyinvoiced,
		orderlineno
	FROM salesorderdetails
	WHERE completed=0
	AND orderno = " . $OrderNo;
$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

if (DB_num_rows($Result) != count($_SESSION['Items'.$identifier]->LineItems)){
/*there should be the same number of items returned from this query as there are lines on the invoice - if  not
then someone has already invoiced or credited some lines */

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
}
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
		discountpercentpayment
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
		'" . $_POST['descuentopago'] . "'
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
			SET quantity = quantity - " . ($OrderLine->Quantity - $OrderLine->QtyDispatched) . " WHERE orderno = " . $OrderNo . " AND stkcode = '" . $OrderLine->StockID . "'";
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
	   
	/*Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */
	if ($OrderLine->Quantity !=0 AND $OrderLine->Quantity!="" AND $OrderLine->Quantity) {
		// Test above to see if the line is completed or not
		if ($OrderLine->Quantity>=0 OR $_POST['BOPolicy']=="CAN"){
			$SQL = "UPDATE salesorderdetails
				SET qtyinvoiced = qtyinvoiced + " . $OrderLine->Quantity . ",
				actualdispatchdate = '" . $DefaultDispatchDate .  "',
				completed=1
				WHERE orderno = " .  $OrderNo . "
				AND orderlineno = '" . $OrderLine->LineNumber . "'";
		} else {
			$SQL = "UPDATE salesorderdetails
				SET qtyinvoiced = qtyinvoiced + " . $OrderLine->Quantity . ",
				actualdispatchdate = '" . $DefaultDispatchDate .  "'
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
				SET quantity = locstock.quantity - " . $OrderLine->Quantity . "
				WHERE locstock.stockid = '" . $OrderLine->StockID . "'
				AND loccode = '" . $OrderLine->AlmacenStock . "'";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
			$DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} else if ($MBFlag=='A'){ /* its an assembly */
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
						avgcost,
						ref4
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
						 " . -$AssParts['quantity'] * $OrderLine->Quantity . ",
						 " . $AssParts['standard'] . ",
						 0,
						 " . ($QtyOnHandPrior -($AssParts['quantity'] * $OrderLine->Quantity)) . ",
						 " .$_SESSION['Tagref'] .",
						 '" .$EstimatedAvgCost ."',
						 '".$OrderLine->LineNumber."'
					)";
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
				$DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				// INSERTAR MOVIMIENTO CONTABLE POR CADA PRODUCTO DEL ENSAMBLE
					include('ProcessAssemblyInvoice.inc');	
				/***********************************************************/
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
				
				$SQL = "UPDATE locstock
					SET quantity = locstock.quantity - " . $AssParts['quantity'] * $OrderLine->Quantity . "
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
		$montodescuento = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
		//DESCUENTO DOS
		$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
		//DESCUENTO TRES
		$montodescuento=$montodescuento * (1 - $OrderLine->DiscountPercent2);
		$montodescuento=($OrderLine->Quantity * $OrderLine->Price)-$montodescuento;
		if ($_SESSION['TypeCostStock']==1){
			$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$OrderLine->StockID, $db);
		}else{
			$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
			$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$OrderLine->StockID, $db);
		}
		/*if ($OrderLine->costoproducto!=0){
			$costo=$OrderLine->costoproducto;
		}else{
			$costo=$OrderLine->StandardCost;
		}*/
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
					avgcost,
					ref4
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
					" . -$OrderLine->Quantity . ",
					" . $OrderLine->DiscountPercent . ",
					" . $OrderLine->DiscountPercent1 . ",
					" . $OrderLine->DiscountPercent2 . ",
					" . $montodescuento . ",
					" . $costoproducto . ",
					" . ($QtyOnHandPrior - $OrderLine->Quantity) . ",
					" . $OrderLine->warranty . ",
					'" . DB_escape_string($OrderLine->Narrative) . "',
					" . $_SESSION['Tagref'] . ",
					'" . $EstimatedAvgCost . "',
					'".$OrderLine->LineNumber."'
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
					avgcost,
					ref4
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
					" . -$OrderLine->Quantity . ",
					" . $OrderLine->DiscountPercent . ",
					" . $OrderLine->DiscountPercent1 . ",
					" . $OrderLine->DiscountPercent2 . ",
					" . $montodescuento . ",
					" . $costoproducto . ",
					" . $OrderLine->warranty . ",
					'" . DB_escape_string(htmlspecialchars_decode($OrderLine->Narrative,ENT_NOQUOTES)) . "',
					" . $_SESSION['Tagref'] . ",
					" . $EstimatedAvgCost . ",
					'".$OrderLine->LineNumber."'
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
				SET amt=amt+" . ($OrderLine->Price * $OrderLine->Quantity / $_SESSION['CurrencyRate']) . ",
				cost=cost+" . ($OrderLine->StandardCost * $OrderLine->Quantity) . ",
				qty=qty +" . $OrderLine->Quantity . ",
				disc=disc+" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->Quantity / $_SESSION['CurrencyRate']) . "
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
			$totalsindescuento=($OrderLine->Price * $OrderLine->Quantity)/$_SESSION['CurrencyRate'];
			$montodescuento= $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
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
					" . ($OrderLine->Price * $OrderLine->Quantity / $_SESSION['CurrencyRate']) . ",
					" . ($OrderLine->StandardCost * $OrderLine->Quantity) . ",
					'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
					'" . $_SESSION['Items'.$identifier]->Branch . "',
					" . $OrderLine->Quantity . ",
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
					" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db) . ",
					'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".$OrderLine->ItemDescription." x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . " |".$seriesventa. "',
					" . $OrderLine->StandardCost * $OrderLine->Quantity . ",
					" . $_SESSION['Tagref'] . "
				)";

			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
/*now the stock entry*/
			$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);
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
					'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".$OrderLine->ItemDescription . " x " . $OrderLine->Quantity . " @ " . $OrderLine->StandardCost . " |".$seriesventa. "',
					" . (-$OrderLine->StandardCost * $OrderLine->Quantity) . ",
					" . $_SESSION['Tagref'] . "
				)";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		} /* end of if GL and stock integrated and standard cost !=0 */
		if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){
		//Post sales transaction to GL credit sales
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
					'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " | ".$OrderLine->ItemDescription . " x " . $OrderLine->Quantity . " @ " . $OrderLine->Price . " @ " . (1/$_SESSION['CurrencyRate']) . " |". $seriesventa."',
					" . (-$OrderLine->Price * $OrderLine->Quantity/$_SESSION['CurrencyRate']) . ",
					" . $_SESSION['Tagref'] . "
				)";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
			$DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			// calcula total de descuentos
			$totalsindescuento=($OrderLine->Price * $OrderLine->Quantity); // /$_SESSION['CurrencyRate']
			$montodescuento= $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
			$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent1);
			$montodescuento=$montodescuento * (1 -$OrderLine->DiscountPercent2);
			$montodescuento=$totalsindescuento-$montodescuento;
			$montodescuento=$montodescuento/$_SESSION['CurrencyRate'];
			// concatena texto de descripcion del movimiento
			$textodescuento= $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID ." | ".$OrderLine->ItemDescription;		
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
					'" . $_SESSION['Items'.$identifier]->DebtorNo . " @".$_SESSION['Items'.$identifier]->CustomerName."',
					" . (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate']) . ",
					" . $_SESSION['Tagref'] . "
				)";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
		$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
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
					" . (-$TaxAmount/$_SESSION['CurrencyRate']) . ",
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
// ************************************** FIN DE ANTICIPOS *****************************************************************
// ************************************** INICIO DE PAGARES *****************************************************************	
if ($_SESSION['Paypromissory']==1){
	include('includes/ProcessPromissory.inc');	
}

// ***********************************************FIN DE FACTURACION DIRECTA *****************************************************************

echo '<br>';
if ($_POST['verserie']==TRUE){
	$verserie=1;
} else{
	$verserie=0;
}
if ($_POST['verkm']==TRUE){
	$verkm=1;
} else{
	$verkm=0;
}
if ($_POST['verplaca']==TRUE){
	$verplaca=1;
} else{
	$verplaca=0;
}
if ($_POST['vercomentarios']==TRUE){
	$vercomentarios=1;
} else{
	$vercomentarios=0;
}
if (isset($_POST['ProcessOrder']) && $_POST['ProcessOrder']== _('Factura Garantia') ){
	prnMsg(_('Se ha generado el Numero de Factura:') . ' ' . $InvoiceNo . ' ' . _(' con exito'),'success');
	// imprime pagares que genero la factura
	if ($generapagare!=0 or $total_anticipo!=0 and $_SESSION['Paypromissory']==0){
	   if ($_SESSION['EnvioXSA']==1){
		$ligapaga=  "PDFPagarePage.php?" . SID . "&PrintPDF=Yes&TransNo=" . $InvoiceNo ;
		$tienepagares="<a target='_blank' href='" . $rootpath . "/". $ligapaga ."'><br>";
		
	    }else{
		$ligapaga = GetUrlToPrintNu($_SESSION['Tagref'],$area,$legaid,70,$db);
		$tienepagares="<a target='_blank' href='" . $rootpath . "/".$ligapaga . SID . "&TransNo=" . $InvoiceNo ."&tagref=".$_SESSION['Tagref']."'>";
		
		$ligapaga2 = GetUrlToPrintNu2($_SESSION['Tagref'],$area,$legaid,70,$db);
		$tienepagares2="<a target='_blank' href='" . $rootpath . "/".$ligapaga2 . SID . "&TransNo=" . $InvoiceNo ."&tagref=".$_SESSION['Tagref']."'>";			
	    }
		echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir Pagares') . '" alt="">'.$tienepagares._('Imprimir Pagares').'</a><br><br>';
		//echo '<img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Ficha Deposito') . '" alt="">'.$tienepagares2._('Ficha Deposito').'</a><br><br>';
	}
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
	$InvoiceNoTAG = DocumentNext(10, $_SESSION['Tagref'],$area,$legaid, $db);
	$separa = explode('|',$InvoiceNoTAG);
	$serie = $separa[1];
	$folio = $separa[0];
	
	$factelectronica= XSAInvoicing($InvoiceNo, $OrderNo, $_SESSION['Items'.$identifier]->DebtorNo, $tipodefacturacion,$_SESSION['Tagref'],$serie,$folio, $db);
	// Envia  los datos al archivooooo
	$mitxt = "txt_fact/Fact".$tipodefacturacion.'_'.$_SESSION['Tagref'].'_'.$InvoiceNo.".txt"; //Ponele el nombre que quieras al archivo
	//$myfile=$mitxt; //Ponele el nombre que quieras al archivo
	$myfile="Fact".$tipodefacturacion.'_'.$_SESSION['Tagref'].'_'.$InvoiceNo.".txt"; //Ponele el nombre que quieras al archivo
	$factelectronica=utf8_encode($factelectronica);
	$fp = fopen($mitxt,"w");
	fwrite($fp,$factelectronica);
	fclose($fp);
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
	
	$SQL = "UPDATE salesorders SET quotation=4, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	if ($_SESSION['InvoiceTask']==1){
	// Funcion para insertar tarea
		InsertTareas($OrderNo,$OrderLine->ItemDescription,$_SESSION['Items'.$identifier]->CustomerName,$db);
	//exit();
	}
	/*** COMMIT ***/		
	$Result = DB_Txn_Commit($db);		
	$empresa=$keyfact.'-'.$rfc;
	$nombre=$nombre;
	$tipo='Factura';
	$myfile=$myfile;
	$factelectronica=$factelectronica;
	
	$param=array('in0'=>$empresa, 'in1'=>$nombre,'in2'=>$tipo,'in3'=>$myfile,'in4'=>$factelectronica);
	if ($tipofacturacionxtag==1){
		try{	
			$client = new SoapClient($_SESSION['XSA']."xsamanager/services/FileReceiverService?wsdl");
			$codigo=$client->guardarDocumento($param);
		}catch (SoapFault $exception) {
			$errorMessage = $exception->getMessage();
		}
		$liga=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' .$liga . '">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
	} elseif($tipofacturacionxtag==2){
		$XMLElectronico=generaXML($factelectronica,'ingreso',$_SESSION['Tagref'],$serie,$folio,$DebtorTransID,'Facturas',$OrderNo,$db);
		$liga="PDFInvoice.php?&clave=chequepoliza_sefia";
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFInvoice.php?OrderNo='.$OrderNo.'&TransNo=' . $InvoiceNo .'&Type='.$tipodefacturacion.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
	}
	else{
			$liga = GetUrlToPrintNu($_SESSION['Tagref'],$area,$legaid,10,$db);
			$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID .'&identifier='.$identifier . '&OrderNo='.$InvoiceNo.'&TransNo=' . $OrderNo .'&verplaca='.$verplaca .'&verkilometraje='.$verkm .'&verserie='.$verserie .'&vercomentarios='.$vercomentarios.'&Tagref='.$_SESSION['Tagref'].'">'. _('Imprimir Factura') . ' (' . _('Laser') . ')' .'</a>';
	}
	
	echo $liga;
	
	/*INICIO:
	SE AGREGO ESTA CONDICION, EN CASO DE SER FACTURA DE CONTADO, SE REDIRECCIONA LA PAGINA DE PAGO RAPIDO
	*/
	if ($tipodefacturacion == 110 and $_SESSION['InvoiceCash']==1){
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/CustomerReceiptFacturaContado.php?transnofac=' . $InvoiceNo . '&debtorno=' . $_SESSION['Items'.$identifier]->DebtorNo . '&branchcode=' . $_SESSION['Items'.$identifier]->Branch . '&tag=' . $_SESSION['Tagref'] . '">';
	}
	/*FIN:
	SE AGREGO ESTA CONDICION, EN CASO DE SER FACTURA DE CONTADO, SE REDIRECCIONA LA PAGINA DE PAGO RAPIDO
	*/
	// se agrega la condicion de generar pagares con edicion de fechas	
	if ($_SESSION['Paypromissory']!=1 and $tipodefacturacion=10){
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/ConfirmCredit_Invoice.php?'. SID .'identifier='.$identifier.'&InvoiceNo=' . $InvoiceNo . '&debtorno=' . $_SESSION['Items'.$identifier]->DebtorNo . '&branchcode=' . $_SESSION['Items'.$identifier]->Branch . '&Tagref=' . $_SESSION['Tagref'] .'&anticipo='.$anticipo.'&pagares='.$pagares.'&CurrAbrev='. $_SESSION['CurrAbrev'].'">';
	}	
	// *************************************************************************************************************
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
