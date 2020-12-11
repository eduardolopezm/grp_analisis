<?php
include_once('GetSalesTransGLCodes.inc');

function RegistrarNCredito($OrderNo,$tipodefacturacion,$db)
{
	$identifier=date('U');
	unset ($LineItems);
	/*unset ($_SESSION['Items'.$identifier]);
	$_SESSION['DefaultDateFormat']='d/m/Y';
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['TypeCostStock']=2;
	if (isset($_SESSION['Items'.$identifier])){
		unset ($LineItems);
		unset ($_SESSION['Items'.$identifier]);
	}*/
	$_SESSION['ExistingOrder']=$OrderNo;
	$_GET['ModifyOrderNumber']=$OrderNo;
	
	$OrderHeaderSQL = 'SELECT notesorders.debtorno,
				debtorsmaster.name,
				notesorders.branchcode,
				notesorders.customerref,
				notesorders.comments,
				notesorders.orddate,
				notesorders.ordertype,
				
				notesorders.shipvia,
				notesorders.deliverto,
				notesorders.deladd1,
				notesorders.deladd2,
				notesorders.deladd3,
				notesorders.deladd4,
				notesorders.deladd5,
				notesorders.deladd6,
				notesorders.contactphone,
				notesorders.contactemail,
				notesorders.freightcost,
				notesorders.deliverydate,
				paymentterms.terms,
				notesorders.fromstkloc,
				notesorders.printedpackingslip,
				notesorders.datepackingslipprinted,
				notesorders.quotation,
				notesorders.deliverblind,
				debtorsmaster.customerpoline,
				locations.locationname,
				custbranch.estdeliverydays,
				notesorders.salesman,
				locations.taxprovinceid,
				custbranch.taxgroupid,
				notesorders.placa,
				notesorders.serie,
				notesorders.kilometraje,
				notesorders.tagref,
				notesorders.currcode,
				notesorders.quotation,
				notesorders.paytermsindicator,
				custbranch.taxid as rfc
			FROM notesorders,
				debtorsmaster,
				
				custbranch,
				paymentterms,
				locations
			WHERE notesorders.debtorno = debtorsmaster.debtorno
				AND notesorders.debtorno = custbranch.debtorno
				AND notesorders.branchcode = custbranch.branchcode
				AND notesorders.paytermsindicator=paymentterms.termsindicator
				AND locations.loccode=notesorders.fromstkloc
				AND notesorders.orderno = ' . $_GET['ModifyOrderNumber'];
	$ErrMsg =  _('La orden de venta no se puede  recuperar por que');
	
	//echo '<pre>'.$OrderHeaderSQL;
	
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);
	if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);
		
		$_SESSION['Tagref']=$myrow['tagref'];
		$_SESSION['CurrAbrev']=$myrow['currcode'];
		$_SESSION['SelectedVehicle']=$myrow['vehiculo'];
		$OrderNo = $_GET['ModifyOrderNumber'];
		
		$DebtorNo = $myrow['debtorno'];
		$Branch = $myrow['branchcode'];
		$CustomerName = $myrow['name'];
		$CustRef = $myrow['customerref'];
		$Comments = stripcslashes($myrow['comments']);
		
		$PaymentTerms =$myrow['paytermsindicator'];
		$DefaultSalesType =$myrow['ordertype'];
		//$SalesTypeName =$myrow['sales_type'];
		$DefaultCurrency = $myrow['currcode'];
		$ShipVia = $myrow['shipvia'];
		$DeliverTo = $myrow['deliverto'];
		$DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$DelAdd1 = $myrow['deladd1'];
		$DelAdd2 = $myrow['deladd2'];
		$DelAdd3 = $myrow['deladd3'];
		$DelAdd4 = $myrow['deladd4'];
		$DelAdd5 = $myrow['deladd5'];
		$DelAdd6 = $myrow['deladd6'];
		$PhoneNo = $myrow['contactphone'];
		$Email = $myrow['contactemail'];
		$Location = $myrow['fromstkloc'];
		$LocationName = $myrow['locationname'];
		$Quotation = $myrow['quotation'];
		$FreightCost = $myrow['freightcost'];
		$Orig_OrderDate = $myrow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
		$_SESSION['quotation']=$myrow['quotation'];
		$_SESSION['Salesman']=$myrow['salesman'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$DeliverBlind = $myrow['deliverblind'];
		$DefaultPOLine = $myrow['customerpoline'];
		$DeliveryDays = $myrow['estdeliverydays'];
		$DispatchTaxProvince = $myrow['taxprovinceid'];
		$TaxGroup = $myrow['taxgroupid'];
		$CostumerRFC=$myrow['rfc'];
		$_SESSION['placa']=$myrow['placa'];
		$_SESSION['serie']=$myrow['serie'];
		$_SESSION['kilometraje']=$myrow['kilometraje'];
		
		
		// OBTENER EL VALOR DE LA MONEDA PARA FINES DE FACTURACION
		$SQLCurrency="SELECT c.rate
			       FROM currencies c
			       WHERE c.currabrev='".$_SESSION['CurrAbrev']."'";
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
		WHERE custbranch.debtorno ='". $DebtorNo . "'
		AND custbranch.branchcode = '" . $Branch . "'";
		$ErrMsg = _('No se pudo cargar la oficina del cliente') . '. ' . _('Verifique');
		$ResultArea = DB_query($SQL,$db, $ErrMsg);
		$myrow = DB_fetch_row($ResultArea);
		$Area = $myrow[0];
		$DefaultShipVia = $myrow[1];
	
		$SQLClient="SELECT typeid FROM debtorsmaster WHERE debtorno='".$DebtorNo."'";
		$result_typeclient = DB_query($SQLClient,$db,$ErrMsg);
		if (DB_num_rows($result_typeclient)==1) {
			$myrowtype = DB_fetch_array($result_typeclient);
			$tipocliente=$myrowtype['typeid'];
		}
		/*
		$SQL="SELECT * FROM paymentterms WHERE termsindicator='".$PaymentTerms."'";
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
		$_POST['fechafactura']=$DeliveryDate;
		$DefaultDispatchDate=$_POST['fechafactura'];
		//$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
		$_POST['InvoiceText']=' Orden '.$OrderNo.' Facturacion Directa';
		$ShipVia=1;
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
		$total =0;
		$taxret=0;
		$_POST['descuentopago']=0;
		$TaxTotal=0;
		
		$SQL = "UPDATE notesorders SET quotation=2, comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "') WHERE orderno= " .  $OrderNo;
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
				taxret
				)
			VALUES (
				". $InvoiceNo . ",
				" . $tipodefacturacion . ",
				'" . $DebtorNo . "',
				'" . $Branch . "',
				'" . $DefaultDispatchDate . "',
				" . $PeriodNo . ",
				'',
				'" . $DefaultSalesType . "',
				" .  $OrderNo . ",
				" . $total . ",
				" . $TaxTotal . ",
				'" . $_POST['ChargeFreightCost'] . "',
				" . $_SESSION['CurrencyRate'] . ",
				'" . str_replace('CANCELADA','',DB_escape_string(strtoupper($Comments))) . "',
				" . $ShipVia . ",
				'"  . $OrderNo  . "',
				'" . $_SESSION['CurrAbrev'] . "',
				" . $_SESSION['Tagref'] .",
				'" . $fechaemision . "',
				'" . $_POST['descuentopago'] . "',
				'-" . $taxret . "'
			)";
		
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El registro de la factura para el cliente no se realizo');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
	
		$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');	
		
		$StandardCost=0;
		$LineItemsSQL = "SELECT notesorderdetails.orderlineno,
					notesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.volume,
					stockmaster.kgs,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.controlled,
					stockmaster.nextserialno,
					stockmaster.eoq,
					notesorderdetails.unitprice,
					notesorderdetails.quantity,
					notesorderdetails.discountpercent,
					notesorderdetails.discountpercent1,
					notesorderdetails.discountpercent2,
					notesorderdetails.actualdispatchdate,
					notesorderdetails.qtyinvoiced,
					notesorderdetails.narrative,
					notesorderdetails.itemdue,
					notesorderdetails.poline,
					(locstock.quantity-locstock.ontransit) as qohatloc,
					stockmaster.mbflag,				
					stockmaster.taxcatid,					
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					notesorderdetails.completed,
					locstock.loccode as fromstkloc1,
					locations.locationname as locationname1,
					notesorderdetails.warranty,
					stockcategory.redinvoice,
					stockcategory.disabledprice,
					stockmaster.taxcatidret
				FROM notesorderdetails INNER JOIN stockmaster
					ON notesorderdetails.stkcode = stockmaster.stockid
					INNER JOIN locstock ON locstock.stockid = stockmaster.stockid,
					locations, stockcategory
				WHERE  locstock.loccode=locations.loccode
					AND stockcategory.categoryid=stockmaster.categoryid
					AND notesorderdetails.completed=0
					AND locstock.loccode=notesorderdetails.fromstkloc
					AND notesorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . "
				ORDER BY notesorderdetails.orderlineno";
		
		$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
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
					
					$SQL = "UPDATE notesorderdetails
						SET qtyinvoiced = qtyinvoiced + " . $myrow['quantity']. ",
						    actualdispatchdate = '" . $DefaultDispatchDate .  "',
						    completed=1
						WHERE orderno = " .  $OrderNo . "
							AND orderlineno = '" . $myrow['orderlineno'] . "'";
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
							SET quantity = locstock.quantity + " . $myrow['quantity'] . "
							WHERE locstock.stockid = '" . $myrow['stkcode'] . "'
							AND loccode = '" . $myrow['fromstkloc1'] . "'";
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
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo recuperar los componentes de montaje de la base de datos de'). ' '. $OrderLine->StockID . _('por que').' ';
						$DbgMsg = _('El SQL utilizado es:');
						$AssResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						while ($AssParts = DB_fetch_array($AssResult,$db)){
						
							//$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
							/* Need to get the current location quantity will need it later for the stock movement */
							$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $AssParts['component'] . "'
								AND loccode= '" .$myrow['fromstkloc1']. "'";
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
									avgcost
								) VALUES (
									'" . $AssParts['component'] . "',
									 " . $tipodefacturacion . ",
									 " . $InvoiceNo . ",
									 '" . $myrow['fromstkloc1'] . "',
									 '" . $DefaultDispatchDate . "',
									 '" . $DebtorNo  . "',
									 '" . $Branch  . "',
									 " . $PeriodNo . ",
									 '" . _('Ensamble') . ': ' . $myrow['stkcode'] . ' ' . _('Orden') . ': ' . $OrderNo . "',
									 " . $AssParts['quantity'] * $myrow['quantity'] . ",
									 " . $EstimatedAvgCost . ",
									 0,
									 " . ($QtyOnHandPrior -($AssParts['quantity'] * $myrow['quantity'])) . ",
									 " .$_SESSION['Tagref'] .",
									 '" .$EstimatedAvgCost ."'
								)";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de existencias de los componentes de ensamble de'). ' '. $OrderLine->StockID . ' ' . _('no se pudieron registrar por que');
							$DbgMsg = _('El SQL para insertar componentes y montaje de los registros de movimientos de existencias es:');
							$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
							// INSERTAR MOVIMIENTO CONTABLE POR CADA PRODUCTO DEL ENSAMBLE
							include('includes/ProcessAssemblyInvoiceWebService.inc');	
							$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
							
							$SQL = "UPDATE locstock
								SET quantity = locstock.quantity + " . $AssParts['quantity'] * $myrow['quantity'] . "
								WHERE locstock.stockid = '" . $AssParts['component'] . "'
								AND loccode = '" . $myrow['fromstkloc1']. "'";
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
								avgcost
								)
							VALUES ('" . $myrow['stkcode']. "',
								" . $tipodefacturacion . ",
								" . $InvoiceNo . ",
								'" . $myrow['fromstkloc1'] . "',
								'" . $DefaultDispatchDate . "',
								'" . $DebtorNo . "',
								'" . $Branch . "',
								" .  $LocalCurrencyPrice . ",
								" .  $PeriodNo . ",
								'" . $OrderNo . "',
								" . $myrow['quantity'] . ",
								" . $myrow['discountpercent'] . ",
								" . $myrow['discountpercent1'] . ",
								" . $myrow['discountpercent2'] . ",
								" . $montodescuento . ",
								" . $costoproducto . ",
								" . ($QtyOnHandPrior - $myrow['quantity']) . ",
								" . $myrow['warranty'] . ",
								'" . DB_escape_string($myrow['narrative']) . "',
								" . $_SESSION['Tagref'] . ",
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
								avgcost
								)
							VALUES ('" . $myrow['stkcode'] . "',
								" . $tipodefacturacion . ",
								" . $InvoiceNo . ",
								'" . $myrow['fromstkloc1']. "',
								'" . $DefaultDispatchDate . "',
								'" . $DebtorNo . "',
								'" . $Branch . "',
								" . $LocalCurrencyPrice . ",
								" . $PeriodNo . ",
								'" . $OrderNo . "',
								" . $myrow['quantity'] . ",
								" . $myrow['discountpercent'] . ",
								" . $myrow['discountpercent1']. ",
								" . $myrow['discountpercent2'] . ",
								" . $montodescuento . ",
								" . $costoproducto . ",
								" . $myrow['warranty'] . ",
								'" . DB_escape_string(htmlspecialchars_decode($myrow['narrative'],ENT_NOQUOTES)) . "',
								" . $_SESSION['Tagref'] . ",
								" . $EstimatedAvgCost . "
								)";
					}
					//echo '<br>mov de stockmoves:'.$SQL;
					
					$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Registros de movimientos de stock no puede insertarse, por que');
					$DbgMsg = _('El siguiente SQL para insertar la contabilidad deL movimiento existencias utilizado es:');
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					/*Get the ID of the StockMove... */
					$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
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
						$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('Los impuestos y tasas aplicables a esta partida de la factura, no pueden insertarse, por que');
						$DbgMsg = _('El siguiente SQL para insertar los registros de detalle de valores del impuesto es:');
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$cuentaIVA=$myrowtax['taxglcode'] ;
					$TaxTotal=$TaxTotal+($myrowtax['taxrate']*($LocalCurrencyPrice*$myrow['quantity']));
					}	
					$total=$total+($LocalCurrencyPrice*$myrow['quantity']);	
					
					//inserta movimiento contable
					if ($costoproducto !=0 and $MBFlag!='A'){
						// saca tipo de almacen para saber
					       $tipoalmacen = GetTypeLocation($myrow['fromstkloc1'],$db);
					       //echo 'tipoalmacen:'.$tipoalmacen.'<br>';
					       if ($tipoalmacen!=2){
						$cuentainventario=GetCOGSGLAccount($Area, $myrow['stkcode'], $DefaultSalesType, $db);
					       }else{
						 $cuentainventario=GetCOGSGLAccountConsigment($Area,$myrow['stkcode'], $DefaultSalesType, $db);
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
							       " . $tipodefacturacion . ",
							       " . $InvoiceNo . ",
							       '" . $DefaultDispatchDate . "',
							       " . $PeriodNo . ",
							       " . $cuentainventario  . ",
							       '" . $DebtorNo . " - " . $myrow['stkcode'] . " | ".$myrow['description']." x " . $myrow['quantity'] . " @ " . $costoproducto . " |".$seriesventa. "',
							       " . -$costoproducto* $myrow['quantity']. ",
							       " . $_SESSION['Tagref'] . "
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
							       " . $tipodefacturacion . ",
							       " . $InvoiceNo . ",
							       '" . $DefaultDispatchDate . "',
							       " . $PeriodNo . ",
							       " . $cuentaalmacen. ",
							       '" . $DebtorNo . " - " . $myrow['stkcode']. " | ".$myrow['description']. " x " . $myrow['quantity']. " @ " . $costoproducto . " |".$seriesventa. "',
							       " . ($costoproducto * $myrow['quantity']) . ",
							       " . $_SESSION['Tagref'] . "
						       )";
					       $ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El costo de las ventas no se ha insertado, por que');
					       $DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
					       $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					}
					//movimiento de venta
					if ($LocalCurrencyPrice!=0){
					//Post sales transaction to GL credit sales
						$SalesGLAccounts = GetSalesGLAccount($Area, $myrow['stkcode'], $DefaultSalesType, $db);
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
								'" . $DebtorNo . " - " . $myrow['stkcode']. " | ".$myrow['description'] . " x " . $myrow['quantity'] . " @ " . $LocalCurrencyPrice . " @ " . (1/$_SESSION['CurrencyRate']) . " |". $seriesventa."',
								" . ($myrow['unitprice'] * $myrow['quantity']/$_SESSION['CurrencyRate']) . ",
								" . $_SESSION['Tagref'] . "
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
						$textodescuento= $DebtorNo . " - " . $myrow['stkcode'] ." | ".$myrow['description'];		
						$textodescuento= $textodescuento . " @ " . ($myrow['discountpercent'] * 100). " % ";
						$textodescuento= $textodescuento . " @ " . ($myrow['discountpercent1'] * 100)." % ";
						$textodescuento= $textodescuento . " @ " . ($myrow['discountpercent2'] * 100)." % ";
						$textodescuento= $textodescuento . " | " . $seriesventa;
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
									" . $tipodefacturacion . ",
									" . $InvoiceNo . ",
									'" . $DefaultDispatchDate . "',
									" . $PeriodNo . ",
									" . $SalesGLAccounts['discountglcode'] . ",
									'".$textodescuento. "',
									" . -$montodescuento . ",
									" . $_SESSION['Tagref'] . "
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
		if (($total + $TaxTotal) !=0) {
			$totalclientes=(($total +  $TaxTotal));
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
						'" . $DebtorNo . " @".$CustomerName."',
						" . -$totalclientes . ",
						" . $_SESSION['Tagref'] . "
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
					'" . $DebtorNo . "',
					'" . $Branch . "',
					'" . $DefaultDispatchDate . "',
					" . $PeriodNo . ",
					'',
					'" . $DefaultSalesType . "',
					" .  $OrderNo . ",
					" . -$total . ",
					" . -$TaxTotal . ",
					'" . $_POST['ChargeFreightCost'] . "',
					" . $_SESSION['CurrencyRate'] . ",
					'" . $_POST['InvoiceText'] . "',
					" . $ShipVia . ",
					'"  . $OrderNo  . "',
					'" . $_SESSION['CurrAbrev'] . "',
					" . $_SESSION['Tagref'] .",
					" . $GLTransID . ",
					'"  . $fechaemision . "',
					'admin'
				)";
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
						" . $tipodefacturacion . ",
						" . $InvoiceNo . ",
						'" . $DefaultDispatchDate . "',
						" . $PeriodNo . ",
						" . $cuentaIVA . ",
						'" . $DebtorNo . "',
						" . ($TaxTotal/$_SESSION['CurrencyRate']) . ",
						" . $_SESSION['Tagref'] . "
					)";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El impuesto de la venta no se ha insertado, por que');
			$DbgMsg = _('El siguiente SQL para insertar en GLTrans es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
		$SQL="UPDATE debtortrans
		      SET ovamount='" . -$total  . "',
			ovgst='" . -$TaxTotal . "'
		      WHERE transno=".$InvoiceNo." and type=".$tipodefacturacion;
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}
	
	return $InvoiceNo.'|'.$tipodefacturacion;	
}
/************************************************************************************************/

?>