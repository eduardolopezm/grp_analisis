<?php

function RegistrarFacturaPedido($OrderNo,$tipodefacturacion,$db, $pathprefix = '')
{

	$debug = 0;
	
	$identifier=date('U');
	unset ($_SESSION['Items'.$identifier]->LineItems);
	unset ($_SESSION['Items'.$identifier]);
	$_SESSION['DefaultDateFormat']='d/m/Y';
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['TypeCostStock']=2;
	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		unset ($_SESSION['Items'.$identifier]);
	}
	$_SESSION['ExistingOrder']=$OrderNo;
	$_GET['ModifyOrderNumber']=$OrderNo;
	
	$OrderHeaderSQL = 'SELECT salesorders.debtorno,
				debtorsmaster.name,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.comments,
				salesorders.orddate,
				salesorders.ordertype,
				salestypes.sales_type,
				salesorders.shipvia,
				salesorders.deliverto,
				salesorders.deladd1,
				salesorders.deladd2,
				salesorders.deladd3,
				salesorders.deladd4,
				salesorders.deladd5,
				salesorders.deladd6,
				salesorders.contactphone,
				salesorders.contactemail,
				salesorders.freightcost,
				salesorders.deliverydate,
				paymentterms.terms,
				salesorders.fromstkloc,
				salesorders.printedpackingslip,
				salesorders.datepackingslipprinted,
				salesorders.quotation,
				salesorders.deliverblind,
				debtorsmaster.customerpoline,
				locations.locationname,
				custbranch.estdeliverydays,
				salesorders.salesman,
				locations.taxprovinceid,
				custbranch.taxgroupid,
				salesorders.placa,
				salesorders.serie,
				salesorders.kilometraje,
				salesorders.tagref,
				salesorders.currcode,
				salesorders.quotation,
				salesorders.paytermsindicator,
				custbranch.taxid as rfc,
				salesorders.vehicleno as vehiculo
			FROM salesorders,
				debtorsmaster,
				salestypes,
				custbranch,
				paymentterms,
				locations
			WHERE salesorders.ordertype=salestypes.typeabbrev
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND salesorders.debtorno = custbranch.debtorno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.paytermsindicator=paymentterms.termsindicator
				AND locations.loccode=salesorders.fromstkloc
				AND salesorders.orderno = ' . $_GET['ModifyOrderNumber'];
	$ErrMsg =  _('La orden de venta no se puede  recuperar por que');
	
	if($debug) {
		echo '<pre>' . $OrderHeaderSQL;
	}
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);
	if (DB_num_rows($GetOrdHdrResult)==1) {
		
		// UPDATE PO COST
		include_once($pathprefix . 'includes/ManufacturedStockUnitcost.inc');
		$sqlCost = "SELECT mbflag, stockid, legalid,
		 IF(salesorderdetails.quantitydispatched IS NULL OR salesorderdetails.quantitydispatched = 0, salesorderdetails.quantity, salesorderdetails.quantitydispatched) AS quantity,
		 pocost, orderlineno 
			FROM salesorders
			INNER JOIN salesorderdetails
			ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN tags
			ON tags.tagref = salesorders.tagref
			INNER JOIN stockmaster
			ON stockmaster.stockid = salesorderdetails.stkcode
			WHERE salesorders.orderno = '{$_GET['ModifyOrderNumber']}'";
		
		$rsCost = DB_query($sqlCost, $db);
		while($rowCost = DB_fetch_array($rsCost)) {
			if($rowCost['mbflag'] == 'M') {
				$pocost = calculate_cost($db, $rowCost['stockid'], '', $rowCost['quantity'], $rowCost['legalid']);
				$sqlCostUpdate = "UPDATE salesorderdetails SET pocost = '$pocost' WHERE orderno = '{$_GET['ModifyOrderNumber']}' AND orderlineno = '{$rowCost['orderlineno']}'";
				DB_query($sqlCostUpdate, $db);
			}
		}
		
		$myrow = DB_fetch_array($GetOrdHdrResult);
		
		$_SESSION['Tagref']=$myrow['tagref'];
		
		// Ver si la unidad de negocio es facturable o no
		/*$typetaxRs 			= DB_query("SELECT typetax FROM tags WHERE tagref = '" . $_SESSION['Tagref'] . "'", $db);
		$esUnidadFacturable = FALSE;
		
		if($debug) {
			echo '<pre>' . "SELECT typetax FROM tags WHERE tagref = '" . $_SESSION['Tagref'] . "'";
		}
		
		if($typetaxRow = DB_fetch_array($typetaxRs)) {
			$esUnidadFacturable = ($typetaxRow['typetax'] == 1);
		}*/
		$esUnidadFacturable=1;
		$_SESSION['CurrAbrev']=$myrow['currcode'];
		$_SESSION['SelectedVehicle']=$myrow['vehiculo'];
		$_SESSION['Items'.$identifier] = new Cart();
		$_SESSION['Items'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
		
		$_SESSION['Items'.$identifier]->DebtorNo = $myrow['debtorno'];
		$_SESSION['Items'.$identifier]->Branch = $myrow['branchcode'];
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
		$_SESSION['Items'.$identifier]->CustRef = $myrow['customerref'];
		$_SESSION['Items'.$identifier]->Comments = stripcslashes($myrow['comments']);
		
		$_SESSION['Items'.$identifier]->PaymentTerms =$myrow['paytermsindicator'];
		$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items'.$identifier]->SalesTypeName =$myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['deladd1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['deladd2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['deladd3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['deladd4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['deladd5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['deladd6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
		$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		$_SESSION['Items'.$identifier]->Quotation = $myrow['quotation'];
		$_SESSION['Items'.$identifier]->FreightCost = $myrow['freightcost'];
		$_SESSION['Items'.$identifier]->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
		$_SESSION['quotation']=$myrow['quotation'];
		$_SESSION['Salesman']=$myrow['salesman'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['Items'.$identifier]->CostumerRFC=$myrow['rfc'];
		$_SESSION['placa']=$myrow['placa'];
		$_SESSION['serie']=$myrow['serie'];
		$_SESSION['kilometraje']=$myrow['kilometraje'];
		
		
		// OBTENER EL VALOR DE LA MONEDA PARA FINES DE FACTURACION
		$SQLCurrency="SELECT c.rate
			       FROM currencies c
			       WHERE c.currabrev='".$_SESSION['CurrAbrev']."'";
		if($debug) {
			echo '<pre>' . $SQLCurrency;
		}
		$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
		$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
		if (DB_num_rows($GetCurrency)==1) {	       
			$myrowCurrency = DB_fetch_array($GetCurrency);
			$_SESSION['CurrencyRate']=$myrowCurrency['rate'];
		}else{
			$_SESSION['CurrencyRate']=1;
		}
		
		$TaxTotal =0;
		$TaxTotals = array();
		$TaxGLCodes = array();
		$TaxLineTotal=0;
		$qohsql = "SELECT  areacode 
			   FROM tags 
			   WHERE   tagref = '" . $_SESSION['Tagref'] . "'";
		if($debug) {
			echo '<pre>' . $qohresult;
		}
		$qohresult =  DB_query($qohsql,$db);
		$qohrow = DB_fetch_row($qohresult);
		$codigoarea=$qohrow[0];
		$PriceLess=true;
		$TaxTotalsRet = array();
		$TaxGLCodesRet = array();
		$taxret=0;
		$taxretline=0;
		$SQL = "SELECT area,
		       defaultshipvia
		FROM custbranch
		WHERE custbranch.debtorno ='". $_SESSION['Items'.$identifier]->DebtorNo . "'
		AND custbranch.branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "'";
		if($debug) {
			echo '<pre>' . $SQL;
		}
		$ErrMsg = _('No se pudo cargar la oficina del cliente') . '. ' . _('Verifique');
		$ResultArea = DB_query($SQL,$db, $ErrMsg);
		$myrow = DB_fetch_row($ResultArea);
		$Area = $myrow[0];
		$DefaultShipVia = $myrow[1];
	
		$SQLClient="SELECT typeid FROM debtorsmaster WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo."'";
		if($debug) {
			echo '<pre>' . $SQLClient;
		}
		$result_typeclient = DB_query($SQLClient,$db,$ErrMsg);
		if (DB_num_rows($result_typeclient)==1) {
			$myrowtype = DB_fetch_array($result_typeclient);
			$tipocliente=$myrowtype['typeid'];
		}
		
		
		/*
		$SQL="SELECT * FROM paymentterms WHERE termsindicator='".$_SESSION['Items'.$identifier]->PaymentTerms."'";
		$result_term = DB_query($SQL,$db,$ErrMsg);
		if (DB_num_rows($result_term)==1) {
			//$myrowterm = DB_fetch_row($result_term);/*
			$myrowterm = DB_fetch_array($result_term);
			$generapagare=$myrowterm['generateCreditNote'];
			$numberOfPayments=$myrowterm['numberOfPayments'];
			$_SESSION['Paypromissory']=$myrowterm['automatic'];
			$pagares=$myrowterm['numberOfPayments'];
			$daysbeforevencimiento=$myrowterm['daysbeforedue'];
			
			if ($generapagare==1){
				//genero pagares, tipo de venta credito
				if ($numberOfPayments==0){
					$tipodefacturacion=110;
					$cuentacargo=ClientAccount($tipocliente,'gl_accountcontado',$db);//$_SESSION['CompanyRecord']['gllink_Invoice'];
				}else{
					$tipodefacturacion=10;
					$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);//$_SESSION['CompanyRecord']['debtorsact'];
				}
			} else {
				if($numberOfPayments==0){
					// no tiene vencimientos, tipo de venta de contado
					$tipodefacturacion=110;
					// cuenta de cargo es facturas de contado
					$cuentacargo=ClientAccount($tipocliente,'gl_accountcontado',$db);//$_SESSION['CompanyRecord']['gllink_Invoice'];
				}else{
					// tiene vencimientos, tipo de venta de credito	
					$tipodefacturacion=10;
					// cuenta de cargo, es cuentas por cobrar
					$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);//$_SESSION['CompanyRecord']['debtorsact'];
				}
			}
		}*/
			
			
		if ($tipodefacturacion==125){
			$cuentacargo=ClientAccount($tipocliente,'gl_accountcontado',$db);//$_SESSION['CompanyRecord']['gllink_Invoice'];
		}else{
			$cuentacargo=ClientAccount($tipocliente,'gl_accountsreceivable',$db);//$_SESSION['CompanyRecord']['debtorsact'];
			$generapagare=0;
			$numberOfPayments=1;
			$_SESSION['Paypromissory']=0;
			$pagares=0;
			$daysbeforevencimiento=30;
		}	
		
		
		// registra la factura en debtortrans
		$_POST['fechafactura']=$_SESSION['Items'.$identifier]->DeliveryDate;
		$DefaultDispatchDate=$_POST['fechafactura'];
		//$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
		
		$_POST['InvoiceText']=' Orden '.$OrderNo.' Facturacion Directa';
		$_SESSION['Items'.$identifier]->ShipVia=1;
		$InvoiceNo = GetNextTransNo($tipodefacturacion, $db);
		$folio=$tipodefacturacion.'-'.$_SESSION['Tagref'].$InvoiceNo;
		$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
		
	
		$fechaemision=$_POST['fechafactura'];
		$separae = explode('/',$fechaemision);
		$diae=$separae[0];;
		$mese = $separae[1];
		$anioe = $separae[2];
		$DefaultDispatchDate=$anioe.'-'.$mese.'-'.$diae;
		$horax = date('H:i:s');
		$horax = strtotime($horax);
		$hora=date('H');
		$minuto=date('i');
		$segundo=date('s');
		$fechainic=mktime($hora,$minuto,$segundo,rtrim($mese),rtrim($diae),rtrim($anioe));
		$fechaemision=date("Y-m-d H:i:s",$fechainic);
		//VARIABLES QUE SE DEFINEN EN CERO
		$_POST['ChargeFreightCost']=0;
		$_SESSION['Items'.$identifier]->total =0;
		$taxret=0;
		$_POST['descuentopago']=0;
		$TaxTotal=0;
		
		$SQL = "UPDATE salesorders SET quotation=4, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
		if($debug) {
			echo '<pre>' . $SQL;
		}
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la venta no se pudo actualizar con el numero de factura');
		$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de venta');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
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
				origtrandate,
				discountpercentpayment,
				taxret,
				nocuenta,
				paymentname
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
				'" . $fechaemision . "',
				'" . $_POST['descuentopago'] . "',
				'-" . $taxret . "',
				'".$_POST['nocuenta2']."',
				'".$_POST['paymentname2']."'
			)";
		
		//if($debug) {
			//echo '<pre>' . $SQL;
		//}
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
	
		$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');	
		
		$StandardCost=0;
		$LineItemsSQL = "SELECT salesorderdetails.orderlineno,
					salesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.volume,
					stockmaster.kgs,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.controlled,
					stockmaster.nextserialno,
					stockmaster.eoq,
					salesorderdetails.unitprice,
					IF(salesorderdetails.quantitydispatched IS NULL OR salesorderdetails.quantitydispatched = 0, salesorderdetails.quantity, salesorderdetails.quantitydispatched) AS quantity,
					salesorderdetails.discountpercent,
					salesorderdetails.discountpercent1,
					salesorderdetails.discountpercent2,
					salesorderdetails.actualdispatchdate,
					salesorderdetails.qtyinvoiced,
					salesorderdetails.narrative,
					salesorderdetails.itemdue,
					salesorderdetails.poline,
					(locstock.quantity-locstock.ontransit) as qohatloc,
					stockmaster.mbflag,				
					stockmaster.taxcatid,					
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					salesorderdetails.completed,
					locstock.loccode as fromstkloc1,
					locations.locationname as locationname1,
					salesorderdetails.warranty,
					stockcategory.redinvoice,
					stockcategory.disabledprice,
					stockmaster.taxcatidret,
					salesorderdetails.pocost,
					salesorderdetails.servicestatus,
					salesorderdetails.showdescrip,
					stkmovid
				FROM salesorderdetails INNER JOIN stockmaster
					ON salesorderdetails.stkcode = stockmaster.stockid
					INNER JOIN locstock ON locstock.stockid = stockmaster.stockid,
					locations, stockcategory
				WHERE  locstock.loccode=locations.loccode
					AND stockcategory.categoryid=stockmaster.categoryid
					AND salesorderdetails.completed=0
					AND locstock.loccode=salesorderdetails.fromstkloc
					AND salesorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . "
				ORDER BY salesorderdetails.orderlineno";
		
		if($debug) {
			echo '<pre>' . $LineItemsSQL;
		}
		$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
		//echo $LineItemsSQL;
		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg);
		
		$LastLineNo=0;
		if (DB_num_rows($LineItemsResult)>0) {
			
			while ($myrow=db_fetch_array($LineItemsResult)) {
				if ($myrow['completed']==0){
					
					
					$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
					$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$myrow['stkcode'], $db);
					$myrow['qohatloc']=$myrow['qohatloc'];
					$unitprice=$myrow['unitprice'];
					//Actualiza el detalle como pedido cerrado y facturado
					
					$SQL = "SELECT quantitydispatched, qtyinvoiced, quantity
						FROM salesorderdetails WHERE orderno = " .  $OrderNo . "
						AND orderlineno = '" . $myrow['orderlineno'] . "'";
					$completed = 1;
					$rsQtys = DB_query($SQL, $db);
					if($rowQtys = DB_fetch_array($rsQtys)) {
						$qtyDispatched = abs($rowQtys['quantitydispatched']);
						$qtyTotal = abs($rowQtys['quantity']);
						$qtyInvoiced = abs($rowQtys['qtyinvoiced']);
						
						if(($qtyDispatched + $qtyInvoiced) == $qtyTotal) {
							$completed = 1;
						} else if(($qtyDispatched + $qtyInvoiced) > $qtyTotal) {
							$myrow['quantity'] = $qtyTotal - $qtyInvoiced;
							$completed = 1;
						} else {
							$completed = 0;
						}	
					}
					if($myrow['stkmovid']>0){
						$myrow['quantity']=$myrow['quantity']*-1;
					}
					
					$SQL = "UPDATE salesorderdetails
						SET qtyinvoiced = qtyinvoiced + " . $myrow['quantity']. ",
						    actualdispatchdate = '" . $DefaultDispatchDate .  "',
						    completed='$completed'
						WHERE orderno = " .  $OrderNo . "
							AND orderlineno = '" . $myrow['orderlineno'] . "'";
					
					if($debug) {
						echo '<pre>' . $SQL;
					}
					
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El detalle de la orden de venta no se pudo actualizar');
					$DbgMsg = _('El SQL para actualizar el detalle de la orden de venta es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					//actualiza lockstock e inserta movimientos de productos tipo ensamble
					$Resultprod = DB_query("SELECT mbflag FROM stockmaster
							   WHERE stockid = '" . $myrow['stkcode']. "'",
							   $db,"<br>No se puede recuperar la bandera del producto");
					$myrowprod = DB_fetch_row($Resultprod);
					$MBFlag = $myrowprod[0];
					
					if ($MBFlag=="B" OR $MBFlag=="M") {
						$Assembly = False;
						/* Need to get the current location quantity
						will need it later for the stock movement */
						$SQL="SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $myrow['stkcode'] . "'
							AND loccode= '" . $myrow['fromstkloc1']. "'";
						if($debug) {
							echo '<pre>' . $SQL;
						}
						$ErrMsg = _('WARNING') . ': ' . _('No se puede recuperar la sucursal del stock');
						$Resulttotal = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);
						if (DB_num_rows($Resulttotal)==1){
							$LocQtyRow = DB_fetch_row($Resulttotal);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
							/* There must be some error this should never happen */
							$QtyOnHandPrior = 0;
						}
						$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . $myrow['quantity'] . "
							WHERE locstock.stockid = '" . $myrow['stkcode'] . "'
							AND loccode = '" . $myrow['fromstkloc1'] . "'";
						if($debug) {
							echo '<pre>' . $SQL;
						}
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El stock por almacen no se puede modificar');
						$DbgMsg = _('El SQL para actualizar el registro de las existencias del almacen es:');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					} else if ($MBFlag=='A'){ /* its an assembly */
						$Assembly=True;
						$SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard,
								 stockmaster.longdescription as descripcion
							FROM bom, stockmaster
							WHERE bom.component=stockmaster.stockid
							AND bom.parent='" . $myrow['stkcode']. "'
							AND bom.effectiveto > '" . Date("Y-m-d") . "'
							AND bom.effectiveafter < '" . Date("Y-m-d") . "'";
						if($debug) {
							echo '<pre>' . $SQL;
						}
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo recuperar los componentes de montaje de la base de datos de'). ' '. $OrderLine->StockID . _('por que').' ';
						$DbgMsg = _('El SQL utilizado es:');
						$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						//echo $SQL;
						
						while ($AssParts = DB_fetch_array($AssResult,$db)){
						
							//$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
							/* Need to get the current location quantity will need it later for the stock movement */
							$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $AssParts['component'] . "'
								AND loccode= '" .$myrow['fromstkloc1']. "'";
							if($debug) {
								echo '<pre>' . $SQL;
							}
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede recuperar componentes y montaje de las cantidades ubicaci?n de las existencias debido a ');
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
							$StandardCost=0;
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
									showdescription,
									avgcost
								) VALUES (
									'" . $AssParts['component'] . "',
									 " . $tipodefacturacion . ",
									 " . $InvoiceNo . ",
									 '" . $myrow['fromstkloc1'] . "',
									 '" . $DefaultDispatchDate . "',
									 '" . $_SESSION['Items'.$identifier]->DebtorNo  . "',
									 '" . $_SESSION['Items'.$identifier]->Branch  . "',
									 " . $PeriodNo . ",
									 '" . _('Ensamble') . ': ' . $myrow['stkcode'] . ' ' . _('Orden') . ': ' . $OrderNo . "',
									 " . -$AssParts['quantity'] * $myrow['quantity'] . ",
									 " . $EstimatedAvgCost . ",
									 0,
									 " . ($QtyOnHandPrior -($AssParts['quantity'] * $myrow['quantity'])) . ",
									 " .$_SESSION['Tagref'] .",
									 '" .$myrow['showdescrip'] ."',
									 '" .$EstimatedAvgCost ."'
								)";
							if($debug) {
								echo '<pre>' . $SQL;
							}
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
							$DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
							// INSERTAR MOVIMIENTO CONTABLE POR CADA PRODUCTO DEL ENSAMBLE
							include($pathprefix . 'includes/ProcessAssemblyInvoiceWebService.inc');	
							$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
							
							$SQL = "UPDATE locstock
								SET quantity = locstock.quantity - " . $AssParts['quantity'] * $myrow['quantity'] . "
								WHERE locstock.stockid = '" . $AssParts['component'] . "'
								AND loccode = '" . $myrow['fromstkloc1']. "'";
							if($debug) {
								echo '<pre>' . $SQL;
							}
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registro de las existencias del almacen no se puede actualizar para un componente de ensamble, por que');
							$DbgMsg = _('El siguiente SQL para actualizar el registro de las existencias por almacen para el componente de ensamble es:');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
							$totalseriessol=0;
							$totalserie=0;
							$costoproducto=$costoproducto+$StandardCost;
						} 
					}// fin de tipo de producto
					// inserta movimientos de inventario
					$LocalCurrencyPrice= ($myrow['unitprice']);
					
					// Suma descuentos en cascada en el campo de descuento para los movimientos de stock
					$montodescuento = $myrow['quantity'] * $myrow['unitprice'] * (1 - $myrow['discountpercent']);
					//DESCUENTO DOS
					$montodescuento=$montodescuento * (1 -$myrow['discountpercent1']);
					//DESCUENTO TRES
					$montodescuento=$montodescuento * (1 - $myrow['discountpercent2']);
					$montodescuento=($myrow['quantity'] * $myrow['unitprice'])-$montodescuento;
					if ($_SESSION['TypeCostStock']==1){
						$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$myrow['stkcode'], $db);
					}else{
						$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
						$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$myrow['stkcode'], $db);
					}
					if ($myrow['pocost']!=0){
						$EstimatedAvgCost=$myrow['pocost'];
					}
					$costoproducto=$EstimatedAvgCost;
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
								showdescription,
								avgcost
								)
							VALUES ('" . $myrow['stkcode']. "',
								" . $tipodefacturacion . ",
								" . $InvoiceNo . ",
								'" . $myrow['fromstkloc1'] . "',
								'" . $DefaultDispatchDate . "',
								'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
								'" . $_SESSION['Items'.$identifier]->Branch . "',
								" .  $LocalCurrencyPrice . ",
								" .  $PeriodNo . ",
								'" . $OrderNo . "',
								" . -$myrow['quantity'] . ",
								" . $myrow['discountpercent'] . ",
								" . $myrow['discountpercent1'] . ",
								" . $myrow['discountpercent2'] . ",
								" . $montodescuento . ",
								" . $costoproducto . ",
								" . ($QtyOnHandPrior - $myrow['quantity']) . ",
								" . $myrow['warranty'] . ",
								'" . DB_escape_string($myrow['narrative']) . "',
								" . $_SESSION['Tagref'] . ",
								'" . $myrow['showdescrip'] . "',
								'" . $EstimatedAvgCost . "'
								)";
								
					} else {
						
					// its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
						
						// producto de ensamble con el total del costo calculado por cada componente
					       if ($MBFlag=='A'){
						   $EstimatedAvgCost=$costoproducto;
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
								showdescription,
								avgcost
								)
							VALUES ('" . $myrow['stkcode'] . "',
								" . $tipodefacturacion . ",
								" . $InvoiceNo . ",
								'" . $myrow['fromstkloc1']. "',
								'" . $DefaultDispatchDate . "',
								'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
								'" . $_SESSION['Items'.$identifier]->Branch . "',
								" . $LocalCurrencyPrice . ",
								" . $PeriodNo . ",
								'" . $OrderNo . "',
								" . -$myrow['quantity'] . ",
								" . $myrow['discountpercent'] . ",
								" . $myrow['discountpercent1']. ",
								" . $myrow['discountpercent2'] . ",
								" . $montodescuento . ",
								" . $costoproducto . ",
								" . $myrow['warranty'] . ",
								'" . DB_escape_string(htmlspecialchars_decode($myrow['narrative'],ENT_NOQUOTES)) . "',
								" . $_SESSION['Tagref'] . ",
								'" . $myrow['showdescrip'] . "',
								" . $EstimatedAvgCost . "
								)";
					}
					//echo '<br>mov de stockmoves:'.$SQL;
					if($debug) {
						echo '<pre>' . $SQL;
					}
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de stock no puede insertarse, por que');
					$DbgMsg = _('El siguiente SQL para insertar la contabilidad deL movimiento existencias utilizado es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					/*Get the ID of the StockMove... */
					$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
					
					if($esUnidadFacturable) {
					
						$SQL = "SELECT taxgrouptaxes.calculationorder,
								taxauthorities.description,
								taxgrouptaxes.taxauthid,
								taxauthorities.taxglcode,
								taxgrouptaxes.taxontax,
								taxauthrates.taxrate,
								taxauthorities.taxglcodediscount
							FROM taxauthrates INNER JOIN taxgrouptaxes ON
								taxauthrates.taxauthority=taxgrouptaxes.taxauthid
								INNER JOIN taxauthorities ON
								taxauthrates.taxauthority=taxauthorities.taxid
							WHERE taxgrouptaxes.taxgroupid=1
								AND taxauthrates.dispatchtaxprovince=1
								AND taxauthrates.taxcatid = " . $myrow['taxcatid'] . "
							ORDER BY taxgrouptaxes.calculationorder";
						$ErrMsg = _('Los impuestos y tasas para este producto no se pueden recuperar por que GetFreightTaxes:');
						$DbgMsg= _('El sql que fallo fue:');
						if($debug) {
							echo '<pre>' . $SQL;
						}
						$GetTaxRatesResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						while ($myrowtax = DB_fetch_array($GetTaxRatesResult)){
							$SQL = 'INSERT INTO stockmovestaxes (stkmoveno,
											taxauthid,
											taxrate,
											taxcalculationorder,
											taxontax)
								VALUES (' . $StkMoveNo . ',
									' . $myrowtax['taxauthid'] . ',
									' . $myrowtax['taxrate'] . ',
									' . $myrowtax['calculationorder'] . ',
									' . $myrowtax['taxontax'] . ')';
							if($debug) {
								echo '<pre>' . $SQL;
							}
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
							$DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
							$cuentaIVA=$myrowtax['taxglcode'] ;
							 $LineTotal = $LocalCurrencyPrice * (1 - $myrow['discountpercent'] );
							//DESCUENTO DOS
							$LineTotal=$LineTotal * (1 -$myrow['discountpercent1'] );
							//DESCUENTO TRES
							$LineTotal=$LineTotal * (1 - $myrow['discountpercent2'] );
							$TaxTotal=$TaxTotal+($myrowtax['taxrate']*($LineTotal*$myrow['quantity']));
						}

					} else {
						
						$LineTotal = $LocalCurrencyPrice * (1 - $myrow['discountpercent'] );
						//DESCUENTO DOS
						$LineTotal=$LineTotal * (1 -$myrow['discountpercent1'] );
						//DESCUENTO TRES
						$LineTotal=$LineTotal * (1 - $myrow['discountpercent2'] );
						$TaxTotal = 0;
					}
					
					
					$_SESSION['Items'.$identifier]->total=$_SESSION['Items'.$identifier]->total+($LineTotal*$myrow['quantity']);	
					
					//inserta movimiento contable
					if ($costoproducto !=0 and $MBFlag!='A'){
						// saca tipo de almacen para saber
					       $tipoalmacen = GetTypeLocation($myrow['fromstkloc1'],$db);
					       //echo 'tipoalmacen:'.$tipoalmacen.'<br>';
					       if ($tipoalmacen!=2){
						$cuentainventario=GetCOGSGLAccount($Area, $myrow['stkcode'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
					       }else{
						 $cuentainventario=GetCOGSGLAccountConsigment($Area,$myrow['stkcode'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
					       }
					       $seriesventa='';
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
							      '" . $tipodefacturacion . "',
							      '" . $InvoiceNo . "',
							       '" . $DefaultDispatchDate . "',
							       '" . $PeriodNo . "',
							       '" . $cuentainventario  . "',
							       '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $myrow['stkcode'] . " | ".$myrow['description']." x " . $myrow['quantity'] . " @ " . $costoproducto . " |".$seriesventa. "',
							       '" . $costoproducto* $myrow['quantity']. "',
							       '" . $_SESSION['Tagref'] . "'
						       )";
					
					       $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
					       $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					       $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					       $StockGLCode = GetStockGLCode($myrow['stkcode'],$db);
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
							       '" . $tipodefacturacion . "',
							       '" . $InvoiceNo . "',
							       '" . $DefaultDispatchDate . "',
							       '" . $PeriodNo . "',
							       '" . $cuentaalmacen. "',
							       '" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $myrow['stkcode']. " | ".$myrow['description']. " x " . $myrow['quantity']. " @ " . $costoproducto . " |".$seriesventa. "',
							       '" . (-$costoproducto * $myrow['quantity']) . "',
							       '" . $_SESSION['Tagref'] . "'
						       )";
					
					       $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
					       $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					       $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					}
					//movimiento de venta
					if ($LocalCurrencyPrice!=0){
					//Post sales transaction to GL credit sales
						$SalesGLAccounts = GetSalesGLAccount($Area, $myrow['stkcode'], $_SESSION['Items'.$identifier]->DefaultSalesType, $db);
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
								'" . $tipodefacturacion . "',
								'" . $InvoiceNo . "',
								'" . $DefaultDispatchDate . "',
								'" . $PeriodNo . "',
								'" . $SalesGLAccounts['salesglcode'] . "',
								'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $myrow['stkcode']. " | ".$myrow['description'] . " x " . $myrow['quantity'] . " @ " . $LocalCurrencyPrice . " @ " . (1/$_SESSION['CurrencyRate']) . " |". $seriesventa."',
								'" . (-$myrow['unitprice'] * $myrow['quantity']/$_SESSION['CurrencyRate']) . "',
								'" . $_SESSION['Tagref'] . "'
							)";
				
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
						$DbgMsg = '<br>' ._('El siguiente SQL para insertar en GLTrans es:');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						// calcula total de descuentos
						$totalsindescuento=($myrow['unitprice'] * $myrow['quantity']); // /$_SESSION['CurrencyRate']
						$montodescuento= $myrow['quantity'] * $myrow['unitprice'] * (1 -$myrow['discountpercent']);
						$montodescuento=$montodescuento * (1 -$myrow['discountpercent1']);
						$montodescuento=$montodescuento * (1 -$myrow['discountpercent2']);
						$montodescuento=$totalsindescuento-$montodescuento;
						$montodescuento=$montodescuento/$_SESSION['CurrencyRate'];
						// concatena texto de descripcion del movimiento
						$seriesventa='';
						$textodescuento= $_SESSION['Items'.$identifier]->DebtorNo . " - " . $myrow['stkcode'] ." | ".$myrow['description'];		
						$textodescuento= $textodescuento . " @ " . ($myrow['discountpercent'] * 100). " % ";
						$textodescuento= $textodescuento . " @ " . ($myrow['discountpercent1'] * 100)." % ";
						$textodescuento= $textodescuento . " @ " . ($myrow['discountpercent2'] * 100)." % ";
						$textodescuento= $textodescuento . " | " . $seriesventa;
						//echo '<pre>'.$textodescuento;
						if ($myrow['discountpercent']!=0 or $myrow['discountpercent1']!=0 or $myrow['discountpercent2']!=0 ){
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
									'" . $tipodefacturacion . "',
									'" . $InvoiceNo . "',
									'" . $DefaultDispatchDate . "',
									'" . $PeriodNo . "',
									'" . $SalesGLAccounts['discountglcode'] . "',
									'".$textodescuento. "',
									'" . $montodescuento . "',
									'" . $_SESSION['Tagref'] . "'
								)";
						
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El descuento de la venta no se ha insertado, por que');
							$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						} /*end of if discount !=0 */
					}
					
					
					
					
				}// fin de completed
			}// fin de while
		} //end of checks on returned data set
		// movimientos contables de debtortrans
		if (($_SESSION['Items'.$identifier]->total + $TaxTotal) !=0) {
			$totalclientes=(($_SESSION['Items'.$identifier]->total +  $TaxTotal));
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
						'" . $tipodefacturacion . "',
						'" . $InvoiceNo . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" .  $cuentacargo . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . " @".$_SESSION['Items'.$identifier]->CustomerName."',
						'" . $totalclientes . "',
						'" . $_SESSION['Tagref'] . "'
					)";
	
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La cuenta por cobrar no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);			
			// ***************************************** inserta en movimientos de debtortrasnmov para estado de cuenta*********************************************
			$GLTransID = DB_Last_Insert_ID($db,'gltrans','counterindex');
			// Insertar en debtortransmov para consulta de estado de cuenta
			$_POST['InvoiceText']='Factura WebService';
			$_POST['ChargeFreightCost'] =0;
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
					'admin'
				)";
			//
			$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
			$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		if (($TaxTotal) !=0) {
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
						'" . $tipodefacturacion . "',
						'" . $InvoiceNo . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $cuentaIVA . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . (-$TaxTotal/$_SESSION['CurrencyRate']) . "',
						'" . $_SESSION['Tagref'] . "'
					)";
		
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		$SQL="UPDATE debtortrans
		      SET ovamount='" . $_SESSION['Items'.$identifier]->total  . "',
			ovgst='" . $TaxTotal . "'
		      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
		
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		return $InvoiceNo.'|'.$tipodefacturacion;
	}
	return "";
}
/************************************************************************************************/

?>