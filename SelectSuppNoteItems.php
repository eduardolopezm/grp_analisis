<?php
 /*
  ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 14-FEB-2011
 CAMBIOS: 
	1. SI LA ORDEN DE NOTA DE CREDITO ES NUEVA PERMITA ELEGIR UNIDAD DE NEGOCIO
 FIN DE CAMBIOS
 
*/
include('includes/DefineCartSuppNotesClass.php');
$PageSecurity = 1;
include('includes/session.inc');
$debuggaz = 0;
if (isset($_POST['lineaxs'])) {
	$totallineas=$_POST['lineaxs'];
}elseif (isset($_GET['lineaxs'])){
	$totallineas=$_GET['lineaxs'];
}else{
	$totallineas=0;
}
if (isset($_POST['ModifyOrderNumber'])) {
  $ordernumber = $_POST['ModifyOrderNumber'];
}elseif(isset($_SESSION['ExistingOrder'])){
	$ordernumber =  $_SESSION['ExistingOrder'];
}elseif(isset($_GET['ModifyOrderNumber'])){
	$ordernumber =  $_GET['ModifyOrderNumber'];
}else{
	$ordernumber =0;
}

if (isset($ordernumber) and $ordernumber<>'') {
	$title = _('Modificar Nota de Credito ') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$title = _('Introducir Datos De Nota de Credito');
}

include('includes/header.inc');
include('includes/GetPrice.inc');
$funcion=367;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');
if (isset($_POST['QuickEntry'])){
   unset($_POST['PartSearch']);
}

if (isset($_POST['order_items'])){
	foreach ($_POST as $key => $value) {
		if (strstr($key,"itm")) {
			$NewItem_array[substr($key,3)] = trim($value);
		}
	}
}

if (isset($_GET['agregarproducto'])){
	foreach ($_GET as $key => $value) {
		if (strstr($key,"itm")) {
			$NewItem_array[substr($key,3)] = trim($value);
			$_POST['order_items'] = '1';
			$precioproductoagregado = $_GET['precioproductoagregado'];
		}
	}
}

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (isset($_SESSION['CurrAbrev']) and strlen($_SESSION['CurrAbrev'])>0 ){
	$_SESSION['CurrAbrev']=$_SESSION['CurrAbrev'];
} else {
	if (isset($_POST['CurrAbrev'])) {
		$_SESSION['CurrAbrev']=$_POST['CurrAbrev'];
	} else {
		if (isset($_GET['CurrAbrev'])){
			$_SESSION['CurrAbrev']=$_GET['CurrAbrev'];
		}
	}
}

if (isset($_SESSION['Tagref']) and strlen($_SESSION['Tagref'])>0 ){
	$_SESSION['Tagref']=$_SESSION['Tagref'];
} else {
	if (isset($_POST['Tagref'])) {
		$_SESSION['Tagref']=$_POST['Tagref'];
	} else {
		if (isset($_GET['Tagref'])){
			$_SESSION['Tagref']=$_GET['Tagref'];
		}
	}
}

unset($listaloccxbussines);

//ARREGLO PARA CONOCER ALMACENES POR CurrAbrev
if (isset($_SESSION['Tagref']) and strlen($_SESSION['Tagref'])>0){	
	$SQL='SELECT l.loccode,t.legalid
	      FROM areas a, tags t, locations l
	      where t.areacode=a.areacode
		and l.tagref=t.tagref
		and l.tagref='.$_SESSION['Tagref'];
	$result_tag = DB_query($SQL,$db);
	$listaloccxbussines=array();
	$counter_bussines=0;
	while ($myrow_bussines = DB_fetch_array($result_tag)) {
		$listaloccxbussines[$counter_bussines]=$myrow_bussines['loccode'];
		$counter_bussines=$counter_bussines + 1;
	}
}


/* inicializa variable de cotizacion */
if (!isset($_SESSION['quotation'])) { $_SESSION['quotation']=0; }
/*******************************************************************************************************************/				
/* AQUI INICIA EL PROCESO DE UNA NUEVA ORDEN      ******************************************************************/
if (isset($_GET['NewOrder'])){
	/* New order entry - clear any existing order details from the Items object and initiate a newy */
	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
		
	}
	$_SESSION['Tagref']='';
	$_SESSION['CurrAbrev']='';
	unset($_SESSION['Tagref']);
	unset($_SESSION['CurrAbrev']);
	//echo "entra";
	$_SESSION['ExistingOrder']=0;
	$_SESSION['InvoiceNo']=0;
	$_SESSION['InvoiceType']=0;
	$_SESSION['Items'.$identifier] = new cart;
	if (count($_SESSION['AllowedPageSecurityTokens'])==1){ //its a customer logon
		$_SESSION['Items'.$identifier]->SupplierNo=$_SESSION['SupplierID'];
		$_SESSION['RequireCustomerSelection']=0;
	} else {
		$_SESSION['Items'.$identifier]->SupplierNo='';
		$_SESSION['RequireCustomerSelection']=1;
	}
}elseif(isset($_GET['InvoiceNumber'])){
	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
	}
	
	$_SESSION['ExistingOrder']=0;
	$_SESSION['InvoiceNo']=$_GET['InvoiceNumber'];
	$_SESSION['InvoiceType']=$_GET['InvoiceType'];
	$InvoiceType=$_GET['InvoiceType'];
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['RequireCustomerSelection']=0;
	
}
/*******************************************************************************************************************/				

/*******************************************************************************************************************/				
/* AQUI INICIA EL PROCESO DE MODIFICACION DE UNA ORDEN      ********************************************************/

/* UNICAMENTE RECUPERA VALORES Y LOS ASIGNA A LAS VARIABLES DE SESSION */

if (isset($_GET['ModifyOrderNumber']) AND $_GET['ModifyOrderNumber']!='' AND $_GET['ModifyOrderNumber']!=0){
/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */
	if ($debuggaz==1)
		echo 'entro a nueva orden !!';
	
	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		unset ($_SESSION['Items'.$identifier]);
	}
	
	$_SESSION['ExistingOrder']=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Items'.$identifier] = new cart;
	
	/*read in all the guff from the selected order into the Items cart  */
	$OrderHeaderSQL = 'SELECT suppnotesorders.supplierno,
				suppliers.suppname as name,
				suppnotesorders.comments,
				suppnotesorders.orddate,
				suppnotesorders.deliverydate,
				paymentterms.terms,
				suppnotesorders.fromstkloc,
				suppnotesorders.quotation,
				" " as locationname,
				1 as taxprovinceid,
				suppliers.taxgroupid,
				suppnotesorders.tagref,
				suppnotesorders.currcode,
				suppnotesorders.quotation,
				paymentterms.termsindicator
			FROM suppnotesorders,
				suppliers,
				paymentterms
				
			WHERE 
				 suppnotesorders.supplierno = suppliers.supplierid
				AND paymentterms.termsindicator=suppliers.paymentterms
				AND suppnotesorders.orderno = ' . $_GET['ModifyOrderNumber'];
	$ErrMsg =  _('La orden de nota de credito no se puede  recuperar por que');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);
	if (DB_num_rows($GetOrdHdrResult)==1) {
		$myrow = DB_fetch_array($GetOrdHdrResult);
		$_SESSION['Tagref']=$myrow['tagref'];
		$_SESSION['CurrAbrev']=$myrow['currcode'];
		$_SESSION['Items'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
		$_SESSION['Items'.$identifier]->SupplierNo = $myrow['supplierno'];
		$_SESSION['Items'.$identifier]->Comments = stripcslashes($myrow['comments']);
		$_SESSION['Items'.$identifier]->PaymentTerms =$myrow['paytermsindicator'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['Items'.$identifier]->Quotation = $myrow['quotation'];
		$_SESSION['quotation']=$myrow['quotation'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];
		// OBTENER EL VALOR DE LA MONEDA PARA FINES DE FACTURACION
		$SQLCurrency="SELECT c.rate
			       FROM currencies c, suppliers d
			       WHERE c.currabrev=d.currcode
			       AND d.supplierid='".$myrow['supplierno']."'";
		$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
		$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
		if (DB_num_rows($GetCurrency)==1) {	       
			$myrowCurrency = DB_fetch_array($GetCurrency);
			$_SESSION['CurrencyRate']=$myrowCurrency['rate'];
		}else{
			$_SESSION['CurrencyRate']=1;
		}
		
		/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
		$LineItemsSQL = "SELECT suppnotesorderdetails.orderlineno,
					suppnotesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.volume,
					stockmaster.kgs,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.nextserialno,
					stockmaster.eoq,
					stockmaster.controlled,
					suppnotesorderdetails.unitprice,
					suppnotesorderdetails.quantity,
					suppnotesorderdetails.discountpercent,
					suppnotesorderdetails.discountpercent1,
					suppnotesorderdetails.discountpercent2,
					suppnotesorderdetails.actualdispatchdate,
					suppnotesorderdetails.qtyinvoiced,
					suppnotesorderdetails.narrative,
					suppnotesorderdetails.itemdue,
					suppnotesorderdetails.poline,
					locstock.quantity as qohatloc,
					stockmaster.mbflag,				
					stockmaster.taxcatid,					
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					suppnotesorderdetails.completed,
					locstock.loccode as fromstkloc1,
					locations.locationname as locationname1,
					suppnotesorderdetails.quantity,
					suppnotesorderdetails.warranty
				FROM suppnotesorderdetails INNER JOIN stockmaster
					ON suppnotesorderdetails.stkcode = stockmaster.stockid
					INNER JOIN locstock ON locstock.stockid = stockmaster.stockid,
					locations
				WHERE  locstock.loccode=locations.loccode
					AND locstock.loccode=suppnotesorderdetails.fromstkloc
					AND suppnotesorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . "
				ORDER BY suppnotesorderdetails.orderlineno";
				
		if ($debuggaz==1)
			echo '<br>LINES: '.$LineItemsSQL;
			
		$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
		if (db_num_rows($LineItemsResult)>0) {
			while ($myrow=db_fetch_array($LineItemsResult)) {
				if ($myrow['completed']==0){
				if ($_SESSION['TypeCostStock']==1){
					$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$NewItem, $db);
				}else{
					$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
					$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$NewItem, $db);
				}
					$_SESSION['Items'.$identifier]->add_to_cart($myrow['stkcode'],
											$myrow['quantity'],
											$myrow['description'],
											$myrow['unitprice'],
											$myrow['discountpercent'],
											$myrow['units'],
											$myrow['volume'],
											$myrow['kgs'],
											$myrow['qohatloc'],
											$myrow['mbflag'],
											$myrow['actualdispatchdate'],
											$myrow['qtyinvoiced'],
											$myrow['discountcategory'],
											$myrow['controlled'],
											//0,	/*Controlled*/
											$myrow['serialised'],
											$myrow['decimalplaces'],
											$myrow['narrative'],
											'No', /* Update DB */
											$myrow['orderlineno'],
			//								ConvertSQLDate($myrow['itemdue']),
											$myrow['taxcatid'],
											'',
											ConvertSQLDate($myrow['itemdue']),
											$myrow['poline'],
											$EstimatedAvgCost,
											$myrow['eoq'],
											$myrow['nextserialno'],
											$myrow['fromstkloc1'],
											$myrow['locationname1'],
											$myrow['discountpercent1'],
											$myrow['discountpercent2'],
											$myrow['warranty']
										);
					/*Just populating with existing order - no DBUpdates */
					}
					$LastLineNo = $myrow['orderlineno'];
					$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
		} //end of checks on returned data set
		
	}
}
/*******************************************************************************************************************/				
/* SI ES UNA ORDEN NUEVA ENTRA AQUI PUESTO QUE NO SE HAN ASIGNADO VALORES A LA SESSION ITEMS      ******************/
if (!isset($_SESSION['Items'.$identifier])){
	/* ESTA VARIABLE ES IMPORTANTE YA QUE DETERMINA SI SE INSERTA O SE ACTUALIZA EL REGISTRO EN LA
	   SIGUIENTE PANTALLA DE Delivery SCREEN */
	$_SESSION['ExistingOrder']=0;
	$_SESSION['RequireCustomerSelection']==1;
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['PrintedPackingSlip'] =0; /*Of course cos the order aint even started !!*/
	if (in_array(2,$_SESSION['AllowedPageSecurityTokens']) AND ($_SESSION['Items'.$identifier]->DebtorNo=='' OR !isset($_SESSION['Items'.$identifier]->DebtorNo))){
	/* need to select a customer for the first time out if authorisation allows it and if a customer
	 has been selected for the order or not the session variable CustomerID holds the customer code
	 already as determined from user id /password entry  */
		$_SESSION['RequireCustomerSelection'] = 1;
	} else {
		$_SESSION['RequireCustomerSelection'] = 0;
	}
}
/*******************************************************************************************************************/				

/* SI QUEREMOS CAMBIAR EL CLIENTE PARA ESTA ORDEN, VERIFICAMOS QUE NO EXISTAN PRODUCTOS YA ENTREGADOS
	Y FORZAMOS A LA SELECCION DEL NUEVO CLIENTE */
if ((isset($_POST['ChangeCustomer']) AND $_POST['ChangeCustomer']!='') OR (isset($_GET['ChangeCustomer']) AND $_GET['ChangeCustomer']!='')){
	if ($_SESSION['Items'.$identifier]->Any_Already_Delivered()==0){
		$_SESSION['RequireCustomerSelection']=1;
	} else {
		prnMsg(_('El Pedido no puede ser modificada, ya que algunas de las partidas del pedido ya han sido facturadas'),'warn');
	}
}
$msg='';

/************************************************************************************************/

/* ENTRAREMOS A ESTA PARTE DEL CODIGO SOLO SI VENIMOS DE SELECCIONAR UN CLIENTE DE LA PANTALLA DE
   RESULTADOS DE BUSQUEDA O DE LA LIGA DE CLIENTE DE MOSTRADOR O SI EL RESULTADO DE LA BUSQUEDA
   DE CLIENTES SOLO TRAJO UN RESULTADO */
if (isset($_GET['Select']) AND $_GET['Select']!='' ) {
	
	$sql = "SELECT suppliers.suppname as name,
			suppliers.currcode,
			paymentterms.terms,
			currencies.rate as currency_rate,
			suppliers.taxid,
			suppliers.taxgroupid
		FROM suppliers,
			paymentterms,
			currencies
		WHERE suppliers.currcode = currencies.currabrev
			AND suppliers.paymentterms=paymentterms.termsindicator
			AND suppliers.supplierid = '" . $_GET['Select'] . "'";
	$ErrMsg = _('Los detalles del cliente seleccionado') . ': ' .  $_POST['Select'] . ' ' . _('no se pueden recuperar, por que ');
	$DbgMsg = _('El SQL utilizado para recuperar los detalles del proveedor fue') . ':';
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($result);
	/**********************************************************************************************/
	/* ASIGNA TODOS LOS VALORES DEL PROVEEDOR EN LAS VARIABLES DE SESSION */
	$_SESSION['Items'.$identifier]->SupplierNo=$_GET['Select'];
	$_SESSION['RequireCustomerSelection']=0;
	$_SESSION['Items'.$identifier]->SupplierName = $myrow[0];
	$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow[1];
	$_SESSION['Items'.$identifier]->PaymentTerms = $myrow[2];
	$_SESSION['CurrencyRate'] = $myrow[3];
	$_SESSION['Items'.$identifier]->RFC = $myrow[4];
	$_SESSION['Items'.$identifier]->TaxGroup = $myrow[5];
	$_SESSION['Items'.$identifier]->DispatchTaxProvince = 1;
	/*****************************************************************************************************/
} 

/* SI SE SELECCIONO EL BOTON DE CANCELAR ORDEN */
if (isset($_POST['CancelOrder'])) {
	$OK_to_delete=1;	//assume this in the first instance
	if($_SESSION['ExistingOrder']!=0) { //need to check that not already dispatched
		$sql = 'SELECT qtyinvoiced
				FROM suppnotesorderdetails
				WHERE orderno=' . $_SESSION['ExistingOrder'] . '
				AND qtyinvoiced>0';
		$InvQties = DB_query($sql,$db);
		if (DB_num_rows($InvQties)>0){
			$OK_to_delete=0;
			prnMsg( _('Hay lineas de este pedido que ya se han facturado. Por favor, elimina solo las lineas en la orden que ya no son necesarias') . '<p>' . _('Existe una opcion en la confirmacion de un despacho o factura a cancelar automaticamente cualquier saldo de la orden en el momento de la facturacion, en caso de que el cliente no quiera el pedido'),'warn');
		}
	}
	
	if ($OK_to_delete==1){
		if($_SESSION['ExistingOrder']!=0){
			$SQL = 'DELETE FROM suppnotesorderdetails WHERE suppnotesorderdetails.orderno =' . $_SESSION['ExistingOrder'];
			$ErrMsg =_('El detalle de lineas de pedido no puede ser eliminado por que');
			$DelResult=DB_query($SQL,$db,$ErrMsg);
			$SQL = 'DELETE FROM suppnotesorders WHERE suppnotesorders.orderno=' . $_SESSION['ExistingOrder'];
			$ErrMsg = _('El encabezado del pedido no puede ser eliminado por que');
			$DelResult=DB_query($SQL,$db,$ErrMsg);
			$_SESSION['ExistingOrder']=0;
		}
		unset($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset($_SESSION['Items'.$identifier]);
		$_SESSION['Items'.$identifier] = new cart;
		$_SESSION['RequireCustomerSelection'] = 0;
		echo '<br><br>';
		prnMsg(_('El pedido de venta ha sido cancelada'),'success');
		include('includes/footer.inc');
		exit;
	}
/* FIN  SELECCIONO EL BOTON DE CANCELAR ORDEN */
} else { /* SI NOOO SE SELECCIONO EL BOTON DE CANCELAR ORDEN */ 
	
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Pedido') . '" alt="">' . ' ';
	if ($_SESSION['Items'.$identifier]->Quotation==1){
		echo _('Autorizacion de Nota de Credito') . ' ';
	} else {
		echo _('Nota de Credito') . ' ';
	}
	
	if (isset($ordernumber) and $ordernumber<>'') {
		echo 'No. ' . $ordernumber;	
	}	
	
	/*************************************************************************************/
	/* TABLA DE DATOS DEL PROVEEDOR EN ENCABEZADO                                          */
	echo '<table border=0 width=100%>';
	echo '<tr><td width=40></td><td>';
	echo '<table border="1" CELLPADDING=0 CELLSPACING=0>';
		echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos Del Proveedor') . '</td><tr>';
		echo '<tr><td>' . _('Codigo Proveedor') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->SupplierNo . '</td><tr>';
		echo '<tr><td>' . _('Proveedor') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->SupplierName . '</td><tr>';
		
	echo '</table>';
	echo '</td><td valign=top>';
		echo '<table border="1" width=100% CELLPADDING=0 CELLSPACING=0 >';
		echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos Extra') . '</td><tr>';
		echo '<tr><td>' . _('RFC') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->RFC . '</td><tr>';
		echo '<tr><td>' . _('Moneda') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->DefaultCurrency._('&nbsp;'). '</td><tr>';
		echo '<tr><td>' . _('Terminos Pago') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->PaymentTerms . '</td><tr>';
		echo '</table>';
	echo '</td><td width=40></td></tr>';
	echo '</table>';
	/*************************************************************************************/	
}
/* *************************ORDEN DE NOTA DE CREDITO PROVEEDOR***************************************/
/* SIEMPRE ENTRA OTRA VES AQUI CUANDO SE ESTA TRABAJANDO EN LA ORDEN DE VENTA   */
if ((isset($_SESSION['Items'.$identifier])) OR isset($NewItem)){
		$_SESSION['Items'.$identifier]->DefaultCurrency=$_SESSION['CurrAbrev'];
		/* SI SE SELECCIONO LA LIGA DE BORRAR DE UNA LINEA DE PRODUCTO ENTRA AQUI */
		if(isset($_GET['Delete'])){
			//page called attempting to delete a line - GET['Delete'] = the line number to delete
			if($_SESSION['Items'.$identifier]->Some_Already_Delivered($_GET['Delete'])==0){
				$_SESSION['Items'.$identifier]->remove_from_cart($_GET['Delete'], 'Yes');  /*Do update DB */
			} else {
				prnMsg( _('Esta linea no puede ser eliminada porque ya se entrego el producto o servicio'),'warn');
			}
		}
		/* PARA CADA LINEA DE PRODUCTO EN LA ORDEN DE VENTA */
		if (isset($_SESSION['Items'.$identifier]->LineItems)) {
			foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
				if (isset($_POST['Quantity_' . $OrderLine->LineNumber])){
					$Quantity = $_POST['Quantity_' . $OrderLine->LineNumber];
					if ($_POST['Price_' . $OrderLine->LineNumber] == ''){
						$_POST['Price_' . $OrderLine->LineNumber] = 0;
					}
					if ($OrderLine->Price == $_POST['Price_' . $OrderLine->LineNumber]
								AND ABS($OrderLine->DiscountPercent - ($_POST['Discount_' . $OrderLine->LineNumber]/100)) < 0.001
								AND is_numeric($_POST['GPPercent_' . $OrderLine->LineNumber])
								AND $_POST['GPPercent_' . $OrderLine->LineNumber]<100
								AND $_POST['GPPercent_' . $OrderLine->LineNumber]>0) {
	
						if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
								$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'",$db);
								if (DB_num_rows($ExRateResult)>0){
									$ExRateRow = DB_fetch_row($ExRateResult);
									$ExRate = $ExRateRow[0];
								} else {
									$ExRate =1;
								}
						} else {
							$ExRate = 1;
						}
						if ($OrderLine->StandardCost!=0){
							$Price = round(($OrderLine->StandardCost*$ExRate)/(1 -(($_POST['GPPercent_' . $OrderLine->LineNumber]+$_POST['Discount_' . $OrderLine->LineNumber])/100)),3);
						} else {
							$Price = $_POST['Price_' . $OrderLine->LineNumber];
						}
					} else {
						$Price = $_POST['Price_' . $OrderLine->LineNumber];
					}
					$DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
					$DiscountPercentage1 = $_POST['Discount1_' . $OrderLine->LineNumber];
					$DiscountPercentage2 = $_POST['Discount2_' . $OrderLine->LineNumber];
					$warranty=$_POST['Itemwarranty_' . $OrderLine->LineNumber];
					if ($warranty==TRUE){
						$warranty=1;
					}else{
						$warranty=0;
					}
					if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
						$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
					} else {
						$Narrative = '';
					}
					$ItemDue = $_POST['ItemDue_' . $OrderLine->LineNumber];
					$POLine = $_POST['POLine_' . $OrderLine->LineNumber];
					if (!isset($OrderLine->DiscountPercent)) {
						$OrderLine->DiscountPercent = 0;
						$DiscountPercentageant=100;
					}else{
						$DiscountPercentageant=$OrderLine->DiscountPercent;
					}
					if (!isset($OrderLine->DiscountPercent1)) {
						$OrderLine->DiscountPercent1 = 0;
						$DiscountPercentageant1=100;
					}else{
						$DiscountPercentageant1=$OrderLine->DiscountPercent1;
					}
					if (!isset($OrderLine->DiscountPercent2)) {
						$OrderLine->DiscountPercent2 = 0;
						$DiscountPercentageant2=100;
					}else{
						$DiscountPercentageant2=$OrderLine->DiscountPercent2;
					}
					if(($DiscountPercentage/100)>$_SESSION['discount1']){
						if ($_SESSION['ExistingOrder']>0){
							if ($DiscountPercentageant1<($DiscountPercentage1/100)){
								prnMsg(_('El producto no se puede actualizar por que el descuento 1 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
								$OrderLine->DiscountPercent = 0;
							}
						}else{
							prnMsg(_('El producto no se puede actualizar por que el descuento 1 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
							$OrderLine->DiscountPercent = 0;
						}
					}
					if(($DiscountPercentage1/100)>$_SESSION['discount2']){
						if ($_SESSION['ExistingOrder']>0){
							if ($DiscountPercentageant2<($DiscountPercentage1/100)){
								prnMsg(_('El producto no se puede actualizar por que el descuento 2 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
								$OrderLine->DiscountPercent1 = 0;
							}
						}else{
							prnMsg(_('El producto no se puede actualizar por que el descuento 2 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
							$OrderLine->DiscountPercent1 = 0;
						}
					}
					if(($DiscountPercentage2/100)>$_SESSION['discount3']){
						if ($_SESSION['ExistingOrder']>0){
							if ($DiscountPercentageant3<($DiscountPercentage2/100)){
								prnMsg(_('El producto no se puede actualizar por que el descuento 3 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
								$OrderLine->DiscountPercent2 = 0;
							}
						}else{
							prnMsg(_('El producto no se puede actualizar por que el descuento 3 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
							$OrderLine->DiscountPercent2 = 0;
						}
					}
					if(!Is_Date($ItemDue)) {
						prnMsg(_('La Fecha de Entrada no es valida ') . ' ' . $NewItem . ' ' . _('La fecha') . ' ' . $ItemDue . ' ' . _('debe tener el formato') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
						$ItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
					}
					if ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
						prnMsg(_('El producto no se pudo actualizar porque esta intentando establecer una cantidad menor de 0 o el precio menor que 0 o el descuento de mayor del 100% o menos al 0%'),'warn');
					} elseif($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->Price != $Price) {
						prnMsg(_('El producto ya ha sido facturado y no puede cambiar a un precio unitario posterior a la facturacion'),'warn');
					} elseif($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage/100)) {
						prnMsg(_('El producto ya ha sido facturado y no puede cambiar a un descuento posterior a la facturacion'),'warn');
					} elseif ($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity){
						prnMsg( _('La cantidad pedida que se esta intentando modificar es una cantidad menor que ya haya sido facturado') . '. ' . _('La cantidad entregada y facturada no puede ser modificada a posteriorir a la factura'),'warn');
					} elseif ($OrderLine->Quantity !=$Quantity OR $OrderLine->Price != $Price OR ABS($OrderLine->DiscountPercent -$DiscountPercentage/100) >0.001 OR $OrderLine->Narrative != $Narrative OR $OrderLine->ItemDue != $ItemDue OR $OrderLine->POLine != $POLine OR ABS($OrderLine->DiscountPercent1 -$DiscountPercentage1/100) >0.001 OR ABS($OrderLine->DiscountPercent2 -$DiscountPercentage2/100) >0.001) {
						//if ($debuggaz==1)
							//echo '<BR>Actualiza:'.$OrderLine->LineNumber;
						/* ACTUALIZA CAMBIOS EN CANTIDAD, PRECIO O DESCUENTOS */				
						$_SESSION['Items'.$identifier]->update_cart_item(
												 $OrderLine->LineNumber,
												 $Quantity,
												 $Price,
												 ($DiscountPercentage/100),
												 $Narrative,
												 'Yes', /*Update DB */
												 $ItemDue, /*added line 8/23/2007 by Morris Kelly to get line item due date*/
												 $POLine,
												 ($DiscountPercentage1/100),
												 ($DiscountPercentage2/100),
												 $OrderLine->AlmacenStock,
												 $warranty
											);
						$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
						//echo "entra";
					}
				} //page not called from itself - POST variables not set
				
			} /* FIN DE LOOP PARA CADA LINEA DE PRODUCTO EN LA ORDEN DE VENTA */
		} /* FIN IF SI EXISTEN YA PRODUCTOS */
	} /* FIN DE ACTUALIZACION DE PRODUCTOS O ALTA DE LINEA DE LA ORDEN */
	    
	/* SI SE SELECCIONO LA OPCION DE CONFIRMAR EL PEDIDO, ENTRA AQUI Y REDIRECCIONA A PAGINA DE DeliveryDetails.php */
	if (isset($_POST['DeliveryDetails'])){
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SuppNotesDetails.php?' . SID .'identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'&quotation='.$_SESSION['quotation'].'">';
		prnMsg(_('Deberias de ser redireccionado automaticamente a la pagina de confirmacion') . '. ' . _('si esto no sucede') . ' (' . _('si el explorador no soporta META REFRESS') . ') ' .
           '<a href="' . $rootpath . '/SuppNotesDetails.php?' . SID .'identifier='.$identifier . '">' . _('haz click aqui') . '</a> ' . _('para continuar'), 'info');
	   	exit;
	}
	/*******************************************************************************************************/
	// INICIO DE AGREGAR A CLASE LOS PRODUCTOS POR SUCURSAL
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '"& name="SelectParts" method=post>';
	echo '<div class="centre"><hr></div>';
	if (isset($_SESSION['Items'.$identifier]->LineCounter)){
		$lineax=($_SESSION['Items'.$identifier]->LineCounter-1);
	}
	if (isset($NewItem_array) && isset($_POST['order_items'])) {
		foreach($NewItem_array as $NewItem => $NewItemQty) {	
			if($NewItemQty > 0){
				$lineax=$lineax+1;
				$NewItemS=strstr($NewItem,"|");
				$NewItemS=str_replace("|","",$NewItemS);
				$NewItem=substr($NewItem,0,strpos($NewItem,"|"));
				$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";
				$ErrMsg =  _('No se pudo determinar si la parte era un kitset o no, porque');
				$KitResult = DB_query($sql, $db,$ErrMsg);
				$Discount = 0; 
				if ($myrow=DB_fetch_array($KitResult)){
					if ($myrow['mbflag']=='K'){	/*It is a kit set item */
						$sql = "SELECT bom.component,
							       bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
							      AND bom.effectiveto > '" . Date('Y-m-d') . "'
							      AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
						$ErrMsg = _('No se pudo recuperar los componentes de la base de datos por que');
						$KitResult = DB_query($sql,$db,$ErrMsg);
						$ParentQty = $NewItemQty;
						while ($KitParts = DB_fetch_array($KitResult,$db)){
							$NewItem = $KitParts['component'];
							$NewItemQty = $KitParts['quantity'] * $ParentQty;
							$NewItemDue = date($_SESSION['DefaultDateFormat']);
							$NewPOLine = $lineax;
							$loccalmacen=$NewItemS;
							include('includes/SuppSelectItemsNote_IntoCart.inc');
				}
					} else { /*Its not a kit set item*/
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = $lineax;
						$loccalmacen=$NewItemS;
						include('includes/SuppSelectItemsNote_IntoCart.inc');
					}
				} /* end of if its a new item */
			} /*end of if its a new item */
		}//Fin de For
	}//Fin de validacion Array
	// ENTRADA RAPIDA DE PRODUCTOS
	/*Process Quick Entry */
	if (isset($_POST['order_items']) or isset($_POST['QuickEntry'])){
		// if enter is pressed on the quick entry screen, the default button may be Recalculate
		$Discount = 0;
		$i=1;
		//$lineax=0;
		//echo "valor:".$lineax;
		while ($i<=$_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') {
			$QuickEntryCode = 'part_' . $i;
			$QuickEntryQty = 'qty_' . $i;
			$QuickEntrylocc='Stock_' . $i;
			$i++;
			$lineax=$lineax+1;
			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = strtoupper($_POST[$QuickEntryCode]);
			}
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = $_POST[$QuickEntryQty];
			}
			if (isset($_POST[$QuickEntrylocc])) {
				$loccalmacen = $_POST[$QuickEntrylocc];
			}
			if (!isset($NewItem)){
				unset($NewItem);
				break;    /* break out of the loop if nothing in the quick entry fields*/
			}
			/*Now figure out if the item is a kit set - the field MBFlag='K'*/
			$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";
			$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
			$KitResult = DB_query($sql, $db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($KitResult)==0){
				prnMsg( _('El codigo de producto') . ' ' . $NewItem . ' ' . _('no pudo ser recuperado de la base de datos y no fue agregado al pedido'),'warn');
			} elseif ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
							bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
							AND bom.effectiveto > '" . Date('Y-m-d') . "'
							AND bom.effectiveafter < '" . Date('Y-m-d') . "'";
					$ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
					$KitResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult,$db)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = $lineax;
						include('includes/SuppSelectItemsNote_IntoCart.inc');
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else {
					// ENVIO INFORMACION DEL ALMACEN
					/*$SQL="SELECT l.loccode,quantity
					      FROM areas a, tags t, locations l ,locstock s
					      WHERE t.areacode=a.areacode
						    AND s.loccode=l.loccode
						    AND l.tagref=t.tagref
						    AND a.areacode='".$_SESSION['DefaultArea']."'
						    AND s.stockid='".$NewItem."'";
					$result_tag = DB_query($SQL,$db);
					while ($myrow_locations = DB_fetch_array($result_tag)) {
						$loccalmacen=$myrow_locations['loccode'];
						$cantidadxl=$myrow_locations['quantity'];
						if ($cantidadxl>0){
							break;
						}
					}*/
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = $lineax ;
					include('includes/SuppSelectItemsNote_IntoCart.inc');
				}
			}
		} /* END WHILE */
		unset($NewItem);
	} /* end of if quick entry */
	// IMPRIME EL RESULTADO DE LOS PRODUCTOS QUE SE HAN SELECCIONADO PREVIAMENTE
	$DiscCatsDone = array();
	$counter =0;
	if (isset($_SESSION['Items'.$identifier]->LineItems)) {
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			if ($OrderLine->DiscCat !="" AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
				$DiscCatsDone[$counter]=$OrderLine->DiscCat;
				$QuantityOfDiscCat =0;
				foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
					/* add up total quantity of all lines of this DiscCat */
					if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
						$QuantityOfDiscCat += $StkItems_2->Quantity;
					}
				}
				$result = DB_query("SELECT MAX(discountrate) AS discount
						    FROM discountmatrix
						    WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
							  AND discountcategory ='" . $OrderLine->DiscCat . "'
							  AND quantitybreak <" . $QuantityOfDiscCat,$db);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]!=0){ /* need to update the lines affected */
					foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
						if ($StkItems_2->DiscCat==$OrderLine->DiscCat AND $StkItems_2->DiscountPercent == 0){
							$_SESSION['Items'.$identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $myrow[0];
							
						}
					}
				}
			}
		} /* end of discount matrix lookup code */
	}
	if (count($_SESSION['Items'.$identifier]->LineItems)>0){
		/*only show order lines if there are any */
	echo '<br>
		<table width="90%" cellpadding="2" colspan="7" border="1">
		<tr>
			<td colspan=14>
				<div class="centre"><b>Detalle del Pedido</b></div>
			</td>
		</tr>
		<tr bgcolor=#800000>';
		if($_SESSION['Items'.$identifier]->DefaultPOLine == 1){
			echo '<th>' . _('PO Line') . '</th>';
		}
	echo '<th>' . _('Codigo') . '</th>
		<th>' . _('Descrip') . '</th>
		<th>' . _('Almacen') . '</th>
		<th>' . _('Cantidad') . '</th>
		<th>' . _('Disp.') . '</th>
		<th>' . _('Unidad') . '</th>
		<th>' . _('Precio') . '</th>';
		if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){	
			echo '<th>' . _('Desc') . '</th>
			      <th>' . _('Desc1') . '</th>
			      <th>' . _('Desc2') . '</th>
			      <th>' . _('IVA') . '</th>';
			if (!isset($ExRate)){
				if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']){
					$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'",$db);
					if (DB_num_rows($ExRateResult)>0){
						$ExRateRow = DB_fetch_row($ExRateResult);
						$ExRate = $ExRateRow[0];
					} else {
						$ExRate =1;
					}
				} else {
					$ExRate = 1;
				}
			}
		}
	echo '<th>' . _('Total') . '</th>';
	echo '<th>' . _('Garan') . '</th>';
	echo '</tr>';
	
		$_SESSION['Items'.$identifier]->total = 0;
		$_SESSION['Items'.$identifier]->totalVolume = 0;
		$_SESSION['Items'.$identifier]->totalWeight = 0;
		$k =0;  //row colour counter	
		$TaxTotals = array();
		$TaxGLCodes = array();
		$TaxTotal =0;
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			if ($OrderLine->erased !=1){
				if ($OrderLine->Price !=0){
					$GPPercent = (($OrderLine->Price * (1 - $OrderLine->DiscountPercent)) - ($OrderLine->StandardCost * $ExRate))*100/$OrderLine->Price;
				} else {
					$GPPercent = 0;
				}
				// DESCUENTO UNO
				$LineTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
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
					//echo 'cuenta iva'.$Tax->TaxGLCode;
					$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
				}
				$TaxTotal += $TaxLineTotal;
				$DisplayLineTotal = number_format($LineTotal,2);
				$DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100),2);
				$DisplayDiscount1 =number_format(($OrderLine->DiscountPercent1 * 100),2);
				//echo "desc:".$OrderLine->DiscountPercent1."<br>";
				$DisplayDiscount2 = number_format(($OrderLine->DiscountPercent2 * 100),2);
				$QtyOrdered = $OrderLine->Quantity;
				$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;
				if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
					$RowStarter = '<tr bgcolor="#EEAABB">';
				} elseif ($k==1){
					$RowStarter = '<tr class="OddTableRows">';
					$k=0;
				} else {
					$RowStarter = '<tr class="EvenTableRows">';
					$k=1;
				}
				
				echo $RowStarter;
				if($_SESSION['Items'.$identifier]->DefaultPOLine ==1){ //show the input field only if required
					echo '<td><input tabindex=1 type=text name="POLine_' . $OrderLine->LineNumber . '" size=20 maxlength=20 value=' . $OrderLine->POLine . '></td>';
				} else {
					echo '<input type="hidden" name="POLine_' .	 $OrderLine->LineNumber . '" value="">';
				}
				echo '<td  style="font-face:verdana;font-size:10px" ><a target="_blank" href="' . $rootpath . '/StockStatus.php?' . SID .'identifier='.$identifier . '&StockID=' . $OrderLine->StockID . '&DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
					<td nowrap style="font-face:verdana;font-size:10px">' . $OrderLine->ItemDescription ;
				echo '<input type="hidden" class="number" name="' . $OrderLine->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=4 size=4 value="' . $Tax->TaxRate*100 . '">';
				echo '</td>';
				echo '<td nowrap style="font-face:verdana;font-size:10px"  >' . substr($OrderLine->AlmacenStockName,1,15) . '</td>';	
				echo '<td class="number"  ><input class="number" onKeyPress="return restrictToNumbers(this, event)" tabindex=2 type=text name="Quantity_' . $OrderLine->LineNumber . '" size=6 maxlength=6 value=' . $OrderLine->Quantity . '>';
				if ($QtyRemain != $QtyOrdered){
					echo '<br>'.$OrderLine->QtyInv.' de '.$OrderLine->Quantity.' facturado';
				}
				echo '</td>
				<td class="number">' . $OrderLine->QOHatLoc . '</td>
				<td>' . $OrderLine->Units . '</td>';
				if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
					echo '<td><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Price_' . $OrderLine->LineNumber . '" size=9 maxlength=16 value=' . $OrderLine->Price . '></td>
					      <td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount_' . $OrderLine->LineNumber . '" size=6 maxlength=6 value=' . ($OrderLine->DiscountPercent * 100) . '>%</td>
					      <td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount1_' . $OrderLine->LineNumber . '" size=6 maxlength=6 value=' . ($OrderLine->DiscountPercent1 * 100) . '>%</td>
					      <td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount2_' . $OrderLine->LineNumber . '" size=6 maxlength=6 value=' . ($OrderLine->DiscountPercent2 * 100) . '>%
					      <input type="hidden" class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="GPPercent_' . $OrderLine->LineNumber . '" size=5 maxlength=4 value=' . $GPPercent . '>';
					echo '</td>';
					echo '<td  class="number" nowrap>
					<input type="hidden" class="number" name="' . $OrderLine->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=2 size=2 value="' . $Tax->TaxRate*100 . '"> 
					<input type="text" class="number" disabled name="ver" maxlength=2 size=2 value="' . $Tax->TaxRate*100 . '"> %';
				} else {
					echo '<td align=right>' . $OrderLine->Price . '';
					echo '<input type=hidden name="Price_' . $OrderLine->LineNumber . '" value=' . $OrderLine->Price . '></td>';
				}
				if ($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)){
					$RemTxt = _('Borra Resto');
				} else {
					$RemTxt = _('Borrar Partida');
				}
				$i=0; // initialise the number of taxes iterated through
				$TaxLineTotal =0; //initialise tax total for the line
				echo '</td><td class=number>' . $DisplayLineTotal . '</td>';
				$LineDueDate = $OrderLine->ItemDue;
				if (!Is_Date($OrderLine->ItemDue)){
					$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
					$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
				}
				if ($OrderLine->warranty==0){
					$checkwar = "";
				}else{
					$checkwar = "checked";
				}
				echo '<td style="text-align:center"><input type="checkbox" name="Itemwarranty_' . $OrderLine->LineNumber .'" '.$checkwar .'></td>';
				echo '<td nowrap><input type=hidden class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ItemDue_' . $OrderLine->LineNumber . '" size=10 maxlength=10 value=' . $LineDueDate . '>';
				echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '&Delete=' . $OrderLine->LineNumber . '&lineaxs='.$_SESSION['Items'.$identifier]->LineCounter. '&ModifyOrderNumber='.$ordernumber.'" onclick="return confirm(\'' . _('¿Esta seguro de quitar este producto de la nota?') . '\');">' . $RemTxt . '</a></td></tr>';				
				/************************************************************************************/
				/* A NIVEL CATEGORIA DE PRODUCTO AGREGAR ESTA CONDICION DE PERMITIR TEXTO NARRATIVO */
				$allowNarrRes = DB_query("select allowNarrativePOLine from stockmaster LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
							  where stockid = '" . $OrderLine->StockID . "'",$db);
				if (DB_num_rows($allowNarrRes)>0){
					$allowNarrRow = DB_fetch_row($allowNarrRes);
					$allowNarr = $allowNarrRow[0];
				} else {
					$allowNarr =0;
				}
				/************************************************************************************/
				if ($allowNarr == 1){
					echo $RowStarter;
					echo '<td>' . _('Texto') . ':</td><td colspan=5><textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="45" rows="2">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea></td></tr>';
				} else {
					echo '<input type=hidden name="Narrative" value="">';
				}
				$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
				$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
				$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;
			} /*FIN DE IF DE ELIMINADOS */
		} /* end of loop around items */
		$DisplayTotal = number_format($_SESSION['Items'.$identifier]->total,$_SESSION['DecimalPlaces']);
		$ColSpanNumber = 11;
		$totalcuenta=$_SESSION['Items'.$identifier]->total;
		$totalcuenta=$totalcuenta+$TaxTotal;
		$totallineas=($_SESSION['Items'.$identifier]->LineCounter);
	echo'<tr><td class=number colspan="'.$ColSpanNumber.'"><b>' . _('SUBTOTAL') . '</b></td>
		<td class=number>' . $DisplayTotal . '</td></tr>';
	echo '<tr><td  class=number colspan="'.$ColSpanNumber.'"><b>' . _('IVA') . '</b></td>
		<td class=number>' . number_format($TaxTotal,$_SESSION['DecimalPlaces'])  . '</td></tr>';
	echo '<tr><td  class=number colspan="'.$ColSpanNumber.'"><b>' . _('TOTAL') . '</b></td>
		<td class=number>' . number_format($totalcuenta,$_SESSION['DecimalPlaces'])  . '</td></tr>';
	echo '</table>';
		$DisplayVolume = number_format($_SESSION['Items'.$identifier]->totalVolume,2);
		$DisplayWeight = number_format($_SESSION['Items'.$identifier]->totalWeight,2);
	echo '<br><div class="centre"><input type=submit name="Recalculate" Value="' . _('Recalcular') . '">
		<input type=submit name="DeliveryDetails" style="font-weight:bold;" value="' . _('CONFIRMAR NOTA DE CREDITO') . '">
		</div><hr>';
	} # end of if lines
	// CAMPOS DE TAG Y MONEDA PARA REALIZAAR LA FACTURACION
	echo '<table >';
	if ($_SESSION['CurrAbrev']==''){
		echo '<tr>';
		if ($_SESSION['Tagref']==''){
			echo '<td><b>' . _('X Unidad de Negocio') . ':</b>&nbsp;&nbsp; ';
			// consulta las unidades de negocio
			$SQL=" SELECT tags.*
			       FROM tags , sec_unegsxuser
			       WHERE  tags.tagref=sec_unegsxuser.tagref
			       AND sec_unegsxuser.userid='".$_SESSION['UserID']."'";
			       
			$resultunitB = DB_query($SQL,$db);
			if (DB_num_rows($resultunitB)==1){
				$myrowlegal=DB_fetch_array($resultunitB);
				$tagref = $myrowlegal['tagref'];
				echo '<input type="hidden" name="Tagref" value="'.$tagref.'">';
				echo $myrowlegal['tagdescription'];
			}else {
				echo '<select name="Tagref">';
				while ($myrowlegal = DB_fetch_array($resultunitB)) {
					if ($_SESSION['Tagref']==$myrowlegal['tagref']){
						echo '<option selected value=' . $myrowlegal['tagref'] . '>' . $myrowlegal['tagdescription'].'</option>';
					} else {
						echo '<option value='. $myrowlegal['tagref'] . '>' . $myrowlegal['tagdescription'].'</option>';
					}
				}
				echo '</select>';
			}
			echo '<td> <b>' . _('Moneda De Documento') . ':</b>&nbsp;&nbsp; ';
			// consulta las unidades de negocio
			$SQL=" SELECT *
			       FROM currencies order by rate desc";
			$resultunitB = DB_query($SQL,$db);
			if (DB_num_rows($resultunitB)==1){
				$myrowCurrency=DB_fetch_array($resultunitB);
				$currabrev = $myrowlegal['currabrev'];
				echo '<input type="hidden" name="CurrAbrev" value="'.$currabrev.'">';
				echo $myrowlegal['currency'];
			}else {
				echo '<select name="CurrAbrev">';
				while ($myrowlegal = DB_fetch_array($resultunitB)) {
					if ($_SESSION['CurrAbrev']==$myrowlegal['currabrev']){
						echo '<option selected value=' . $myrowlegal['currabrev'] . '>' . $myrowlegal['currency'].'</option>';
					} else {
						echo '<option value='. $myrowlegal['currabrev'] . '>' . $myrowlegal['currency'].'</option>';
					}
				}
				echo '</select>';
			}		
			echo '</td>';
			echo '</tr>';
		}
	}
	echo '</table>';
	/**************************/
	/* BUSQUEDA DE PRODUCTOS  */
	echo '<table><tr><td valign=top >';
	if (isset($_POST['PartSearch']) && $_POST['PartSearch']!='' || !isset($_POST['QuickEntry'])){
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref']. SID . '& name="SelectParts" method=post>';
		echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '">
		<input type=hidden name="lineaxs" value='.$totallineas.'>';		
		$SQL="SELECT categoryid,
			     categorydescription
		      FROM stockcategory
		      WHERE stocktype='F' OR stocktype='D'
		      ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);
		echo '<table>';
		echo '	<tr>
				<td colspan=2>';
					echo '<div class="centre"><b><p>' . $msg . '</b></p>';
					echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ';
					echo _('Busqueda de Productos') . '</p></div>';
				echo '<td>
			</tr>';
		echo '  <tr>
				<td>
					<b>' . _('X Categoria') . ':</b>
				</td>
				<td>
					<select tabindex=1 name="StockCat">';
						if (!isset($_POST['StockCat'])){
						echo "<option selected value='All'>" . _('Todas') ."</option>";
					} else {
						echo "<option value='All'>" . _('Todas')."</option>";;
					}
					while ($myrow1 = DB_fetch_array($result1)) {
						if ($_POST['StockCat']==$myrow1['categoryid']){
							echo '<option selected value=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
						} else {
							echo '<option value='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
						}
					}
					echo "</select>";
		echo "		</td>
			</tr>
			<tr>";
			$SQL="SELECT locations.loccode,
				     locationname
			      FROM locations, sec_loccxusser
			      WHERE locations.loccode=sec_loccxusser.loccode
				    AND sec_loccxusser.userid='".$_SESSION['UserID']."'
			      ORDER BY locationname";
			$result2 = DB_query($SQL,$db);
		echo '		<td nowrap>
					<b>' . _('X Almacen') . ':</b>
				</td>
				<td>';
		echo '			<select tabindex=1 name="StockLock">';
					if (!isset($_POST['StockLock'])){
						echo "<option selected value='All'>" . _('Todas')."</option>";
					} else {
						echo "<option value='All'>" . _('Todas')."</option>";
					}
					while ($myrow2 = DB_fetch_array($result2)) {
						if ($_POST['StockLock']==$myrow2['loccode']){
							echo '<option selected value=' . $myrow2['loccode'] . '>' . $myrow2['locationname'];
						} else {
							echo '<option value='. $myrow2['loccode'] . '>' . $myrow2['locationname'];
						}
					}
		echo '			</select>
				</td>
			</tr>
			<tr>
				<td>';
					echo '<b>'. _('X Descripcion').':</b>
				</td>';
		?>
				<td>
					<input tabindex=2 type="Text" name="Keywords" size=40 maxlength=40 value="<?php echo $_POST['Keywords']; ?>">
				</td>
			</tr>
			<tr>
				<td align="right">
					<b> </b><b><?php echo _('X Codigo'); ?> :</b>
				</td>
				<td>
					<input tabindex=3 type="Text" name="StockCode" size=15 maxlength=18 value="<?php echo $_POST['StockCode']; ?>">
				</td>
			</tr>
			<tr>
			<td></td><td>
				<div class="centre">
					<input tabindex=4 type=submit name="Search" value="<?php echo _('Buscar Productos'); ?>">
					<input tabindex=4 type=submit name="QuickEntry" value="<?php echo _('Entrada Rapida'); ?>">
				</div>
			</td>
		</tr>
		</table>
		<?php
			echo '</form>';
	}
	echo '</td></tr></table><table><tr><td>';
  
	// resultados de busqueda de productos
	if (isset($_POST['Search']) or isset($_POST['NextO']) or isset($_POST['PrevO']) or isset($_POST['Go1']) ){
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'"& name="SelectParts" method=post>';
		if ($_POST['Keywords']!=='' AND $_POST['StockCode']=='') {
			$msg='</b><div class="page_help_text">' . _('La descripcion del producto se utilizo en la busqueda') . '.</div>';
		} elseif ($_POST['StockCode']!=='' AND $_POST['Keywords']=='') {
			$msg='</b><div class="page_help_text">' . _('Codigo de Producto se utilizo en la busqueda') . '.</div>';
		} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
			$msg='</b><div class="page_help_text">' . _('Categoria de producto se utilizo en la busqueda') . '.</div>';
		} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='' AND $_POST['StockLock']!='All') {
			$msg='</b><div class="page_help_text">' . _('Categoria de producto se utilizo en la busqueda') . '.</div>';
		}
		
		if (isset($_POST['Keywords']) AND strlen($_POST['Keywords'])>2) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = strtoupper($_POST['Keywords']);
			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';
			if ($_POST['StockCat']=='All'){
				
				/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
				/* ORDENA PRIMERO POR CANTIDAD DISPONIBLE EN ALMACENES DEL USUARIO Y DESPUES POR PRECIO */
				
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.loccode,
						locstock.quantity,
						disponibilidad.disponible,
						COALESCE(MIN(prices.price),9999999) as price
					FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity) as disponible
						FROM stockmaster, stockcategory , locstock, sec_loccxusser 
						WHERE stockmaster.categoryid = stockcategory.categoryid
							AND stockmaster.stockid=locstock.stockid";
						//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . "  	AND stockmaster.mbflag <>'G' AND 
							sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."'
							AND stockmaster.description " . LIKE . " '". $SearchString ."'
							AND stockmaster.discontinued=0";				
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL = $SQL. "
						GROUP BY stockmaster.stockid ) as
					disponibilidad, stockcategory, locstock, sec_loccxusser, locations,
					stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
						typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
						(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
					WHERE stockmaster.stockid = disponibilidad.stockid
						AND stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
						//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . "  AND stockmaster.mbflag <>'G' AND 
						sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
						stockmaster.description " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
						
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
				$SQL = $SQL. " GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.loccode,
						locstock.quantity,
						disponibilidad.disponible ";
				$SQL = $SQL. " 
					ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
					
			} else {
				
				/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
				$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible,
						COALESCE(MIN(prices.price),9999999) as price
					FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity) as disponible
					FROM stockmaster, stockcategory ,locstock,sec_loccxusser 
					WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . "  AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.description " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
				$SQL = $SQL. "
					GROUP BY stockmaster.stockid
					) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,
					stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
									typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
									(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
					WHERE stockmaster.stockid = disponibilidad.stockid AND 
					stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
						//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . "  AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.description " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
				$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
				$SQL = $SQL. " GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.loccode,
						locstock.quantity,
						disponibilidad.disponible ";
				$SQL = $SQL. " 
					ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
			}
	
		} elseif (strlen($_POST['StockCode'])>0){
			$_POST['StockCode'] = strtoupper($_POST['StockCode']);
			$SearchString = $_POST['StockCode']; //'%' . $_POST['StockCode'] . '%';
			if ($_POST['StockCat']=='All'){
				
				/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
				$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible,
						COALESCE(MIN(prices.price),9999999) as price
					FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity) as disponible
					FROM stockmaster, stockcategory ,locstock,sec_loccxusser 
					WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid ";
				  //AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.stockid " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL = $SQL. "
					GROUP BY stockmaster.stockid
					) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,
					stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
									typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
									(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
					WHERE stockmaster.stockid = disponibilidad.stockid AND 
					stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					$SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.stockid " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
				$SQL = $SQL. " GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.loccode,
						locstock.quantity,
						disponibilidad.disponible ";
				$SQL = $SQL. " 
					ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";		
			} else {
				/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
				$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible,
						COALESCE(MIN(prices.price),9999999) as price
					FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity) as disponible
					FROM stockmaster, stockcategory ,locstock,sec_loccxusser 
					WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.stockid " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
				$SQL = $SQL. "
					GROUP BY stockmaster.stockid
					) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,
					stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
									typeabbrev = 'PL' and currabrev = 'MXN' and
									debtorno = '' and branchcode = ''									
					WHERE stockmaster.stockid = disponibilidad.stockid AND 
					stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid ";
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
			$SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.stockid " . LIKE . " '". $SearchString ."' AND stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
				$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
				$SQL = $SQL. " GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.loccode,
						locstock.quantity,
						disponibilidad.disponible ";
				$SQL = $SQL. " 
					ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
			}
		} else { 
			if ($_POST['StockCat']=='All' or strlen($_POST['StockCat'])<=0){
				/* BUSCAR TODOS LOS PRODUCTOS, NO BUEN A IDEA */
				if (strlen($_POST['Keywords']) > 0) {
					?><script type="text/javascript">
						alert("Escribe por lo menos tres caracteres en campo de descripcion para evitar que la busqueda tarde mucho tiempo...");
					</script><?
					exit;
				} else {
					?><script type="text/javascript">
						alert("Selecciona alguno de los criterios de busqueda para evitar que la busqueda tarde mucho tiempo...");
					</script><?
					exit;
				}
					
			} else {
				/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
				$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible,
						COALESCE(MIN(prices.price),9999999) as price
					FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity) as disponible
					FROM stockmaster, stockcategory ,locstock,sec_loccxusser 
					WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid";
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
				$SQL = $SQL. "
					GROUP BY stockmaster.stockid
					) as disponibilidad, stockcategory ,locstock,sec_loccxusser, locations,
					stockmaster LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
									typeabbrev = 'PL' and currabrev = 'MXN' and
									debtorno = '' and branchcode = ''									
					WHERE stockmaster.stockid = disponibilidad.stockid AND 
					stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid ";
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
				$SQL = $SQL . "	AND stockmaster.mbflag <>'G' AND 
					sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
					stockmaster.discontinued=0";
				if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
					$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
				}
				$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
				$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
				$SQL = $SQL. " GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.loccode,
						locstock.quantity,
						disponibilidad.disponible ";
				$SQL = $SQL. " 
					ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
					
			  }
		}
		if (isset($_POST['NextO'])) {
			$Offset = $_POST['nextlistO'];
		}
		if (isset($_POST['PrevO'])) {
			$Offset = $_POST['previousO'];
		}
		if (isset($_POST['Go1'])){
			$Offset = $_POST['Offset1'];
		}
		if (!isset($Offset) or $Offset<0) {
			$Offset=0;
		}
		if (!isset($_POST['num_reg'])){
			$num_reg= 30; //$_SESSION['DisplayRecordsMax'];
		}else{
			$num_reg=$_POST['num_reg'];
		}
		
		$SearchResult = DB_query($SQL,$db);
		$ListCount=DB_num_rows($SearchResult);
		$ListPageMax=ceil($ListCount/$num_reg);
		//$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'].' OFFSET '.number_format($_SESSION['DisplayRecordsMax']*$Offset);
		$SQL = $SQL . ' LIMIT '.$num_reg.' OFFSET '. number_format($Offset * $num_reg) ;
		
		$ErrMsg = _('Existe un problema de seleccion de  para mostrar parte por que');
		$DbgMsg = _('El SQL usado para obtener producto fue');
		$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
		$SearchResultArray = DB_query($SQL,$db,$ErrMsg, $DbgMsg);
		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('No hay productos disponibles que cumplan los criterios especificados'),'info');
		}
		if (DB_num_rows($SearchResult)==1){
			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}
		if (DB_num_rows($SearchResult)<$num_reg){
			$Offset=0;
		}
	} //end of if search

	//*************************************Mostrar productos despues de la busqueda
	if (isset($SearchResult)) {
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . ' method=post name="orderform">';
		echo '<table width=100% ><tr><td colspan=6>';
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ';
		echo _('Seleccion de Productos') . '</p>
			<input type=hidden name="lineaxs" value='.$totallineas.'>
			</div>';
		echo '<td></tr>';
		if ($ListPageMax >1) {
			if ($Offset==0){
				$Offsetpagina=1;	
			} else {
				$Offsetpagina=$Offset+1;
			}
			echo '<tr>
				<td colspan=6 style="text-align:center;"><b>'.$Offsetpagina. ' </b> ' . _('de') . '<b> ' . $ListPageMax . ' </b> ' . _('Paginas') . '.  ' . _('Ir a la Pagina') . ':';
				echo '<select name="Offset1">';
					$ListPage=0;
					while($ListPage < $ListPageMax) {			
						if ($ListPage == $Offset) {
							echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage+1) . '</option>';
						} else {
							echo '<option VALUE=' . $ListPage . '>' . ($ListPage+1) . '</option>';
						}
							$ListPage++;
							$Offsetpagina=$Offsetpagina+1;
						}
					echo '</select>
				
				<input type="text" name="num_reg" size=4 value="' .$num_reg. '">
					<input type=submit style="width:50px;" name="Go1" size=5 VALUE="' . _('IR') . '">
				</td></tr>';
		}
		echo '<tr><td><input type="hidden" name="previousO" value='.number_format($Offset-1).'><input tabindex='.number_format($j+7).' type="submit" name="PrevO" value="'._('Anterior').'"></td>';
		echo '<td style="text-align:center" colspan=2><input type="hidden" name="nextlistO" value='.number_format($Offset+1).'><input tabindex='.number_format($j+9).' type="submit" name="NextO" value="'._('Siguiente').'"></td>';
		echo '<td colspan=4><input type="hidden" name="order_items" value=1><input tabindex='.number_format($j+8).' type="submit" value="'._('Ordenar').'">';
		echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
		echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
		echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
		echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';
		echo '</td></tr>';
		// array para rowspan por codigooooo
		$xsuc=0;
		$vertabla2=0;
		$listaloccxstock=array();
		$counter_sucbus=0;
	
		while ($myrows=DB_fetch_array($SearchResultArray)) {
			$stsucursal=$myrows['stockid'];
			if ($xsuc!=0 and $stsucursal!=$stsucursalant){
				$listaloccxstock[$counter_sucbus]=$xsuc;
				$counter_sucbus=$counter_sucbus+1;
				$xsuc=0;
			}
			$xsuc=$xsuc+1;
			$stsucursalant=$stsucursal;
		}
		$listaloccxstock[$counter_sucbus]=$xsuc+1;
		//Fin de array para colspan
		
		// Tabla con listado de precios
		$TableHeader = '<tr><th style="font-face:verdana;font-size:10px">' . _('Codigo') . '</th>
				<th style="font-face:verdana;font-size:10px" >' . _('Descripcion') . '</th>
				<th style="font-face:verdana;font-size:10px">' . _('Almacen') . '</th>
				<th style="font-face:verdana;font-size:10px">' . _('Disp:') . '</th>';
		$TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">' . _('Cant:') . '</th>';
		
		$SQLprice =" SELECT distinct salestypes.typeabbrev,salestypes.sales_type
			FROM salestypes, sec_pricelist
			WHERE sec_pricelist.pricelist = salestypes.typeabbrev
				AND sec_pricelist.userid='" . $_SESSION['UserID'] . "'
				OR salestypes.typeabbrev='".$_SESSION['Items'.$identifier]->DefaultSalesType."'
			ORDER BY salestypes.sales_type";
		$prices = DB_query($SQLprice,$db);
		$listaprecio=array();
		$listacolorprecio=array();
		$countlista=0;
		while ($myrows=DB_fetch_array($prices)) {
			$lprecio=$myrows['sales_type'];
			$abrelist=$myrows['typeabbrev'];
			$TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">' . $lprecio . '</th>';
			$listaprecio[$countlista]=$abrelist;
			$countlista=$countlista+1;
			
		}
		$listapreciototal=$countlista;
		
		$TableHeader =$TableHeader .'</tr>';
		$j = 1;
		$k = 0; //row colour counter
		$sucursales='';
		$xsuc=1;
		$vertabla=0;
		$codigoactual="";
		echo $TableHeader;
		$counter_sucbus=0;
		while ($myrow=DB_fetch_array($SearchResult)) {
			$loccationstock=$myrow['loccode'];
			$ImageSource = _('No Image');
			$qohsql = "SELECT *
				   FROM locations
				   WHERE   loccode = '" . $loccationstock . "'";
			$qohresult =  DB_query($qohsql,$db);
			$qohrow = DB_fetch_row($qohresult);
			$qoh =  $qohrow[1];
			$codigobranch=$qohrow[0];
			if ($codigoactual!=$myrow['stockid']){
				if ($xsuc!=1) {
					echo "</td></tr>";
				}
			}
			
			if ($codigoactual!=$myrow['stockid']){
				if ($k==1) {$k=0;} else {$k=1;}; //CAMBIA EL COLOR DE LA LINEA EN CADA PRODUCTO !!
			}
			
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$classtd="EvenTableRows";
			} else {
				echo '<tr class="OddTableRows">';
				$classtd="OddTableRows";
			}
			if ($codigoactual!=$myrow['stockid']){
				echo '<td style="text-align:center; font-face:verdana;font-size:10px; vertical-align:top;">
					<a style="text-align:center; font-face:verdana;font-size:10px; vertical-align:top;" target="_blank" href="' . $rootpath . '/StockStatus.php?' . SID .'identifier='.$identifier . '&StockID=' . $myrow['stockid'] . '&DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '">' . $myrow['stockid'] . '</a>
				</td>';
				echo "<td style='vertical-align:top;font-face:verdana;font-size:12px' width=120><b>".$myrow['description']."</b></td>";
				$counter_sucbus=$counter_sucbus+1;
			} else {
				echo '<td></td><td></td>';
			}
			//}
			$stockid=$myrow['stockid'];
			$Available = $myrow['quantity'];
			$vertabla=$vertabla+1;
			if (in_array($codigobranch,$listaloccxbussines)){
				$tipocaja="text";
			}else{
				$tipocaja="hidden";
			}
			echo "<td style='font-face:verdana;font-size:10px' nowrap class=".$classtd.">".$qoh."</td>";
			if ($Available > 0) {
				echo "<td style='text-align:right' class=".$classtd."><b>".number_format($Available,0)."</b></td>";
			} else {
				echo "<td style='text-align:right' class=".$classtd.">-</td>";
			}
			
			echo '<td style="text-align:center">
				<input class="number" onKeyPress="return restrictToNumbers(this, event)" tabindex='.number_format($j+7).' type="'.$tipocaja.'" size=4 name="itm'.$myrow['stockid'].'|'.$codigobranch.'" value=0>
			</td>';
			
			//echo "<td style='text-align:right'>$".number_format($Pricex,2)."</td>";
			for($countlista=0;$countlista<$listapreciototal;$countlista++) {
				$listapreciox=$listaprecio[$countlista];
				
				$Pricey = GetPriceDOS($stockid, $_SESSION['Items'.$identifier]->DebtorNo,$listapreciox,$_SESSION['CurrAbrev'], $db);
				$separa = explode('|',$Pricey);
				
				$bgcolorlista = $separa[1];
				
				$Pricex = $separa[0];
				
				if ($_SESSION['Items'.$identifier]->DefaultSalesType==$listapreciox){
					if ($Pricex>0){
						$valorlista='<b>'.number_format($Pricex,2).'</b>';
					} else{
						$valorlista='N/A';
					}
				}else{
					if ($Pricex>0){
						$valorlista=number_format($Pricex,2);
					} else{
						$valorlista='N/A';
					}
				}
				if ($bgcolorlista=='#FFFFFF') {
					echo "<td style='font-face:verdana;font-size:10px;text-align:right;' class=".$classtd." >".$valorlista."</td>";
				} else {
					echo "<td style='font-face:verdana;font-size:10px;text-align:right;background-color:".$bgcolorlista."' class=".$classtd." >".$valorlista."</td>";
				}
				
			}
			
			if ($j==1) $jsCall = '<script  type="text/javascript">defaultControl(document.SelectParts.itm'.$myrow['stockid'].'|'.$codigobranch.');</script>';
			$codigoactual=$myrow['stockid'];
			$xsuc=$xsuc+1;
			$j++;
			
		}#fin de while de productos
		if ($xsuc!=1){
			echo "</tr></table></td></tr>";	
		}
		echo '</table></form>';
		echo $jsCall;
	}#end if SearchResults to show
	
	// Muestra entrada rapida
	elseif(!isset($SearchResult) and isset($_POST['QuickEntry'])){
		
		if ($totallineas>0){
			//$totallineas=$totallineas+1;
		}
	    echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'] . ' method=post name="quickentry">';
	    echo '<table width=100% >
			<tr>
				<td><div>';
					echo '<p class="page_title_text">
						<img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . '';
					       echo _('Captura de Productos') . '</p>
					     </div>';
					echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
					echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
					echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
					echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';     
			echo '	</td>
			</tr>
			<tr>
				<td>';
				
			$SQL='SELECT l.loccode,locationname
			      FROM areas a, tags t, locations l
			      WHERE t.areacode=a.areacode
				AND l.tagref=t.tagref
				AND l.tagref="'.$_SESSION['Tagref'].'"';
				      
					echo '<table border=1>
						<tr>';
						echo '<th>' . _('Codigo') . '</th>
						      <th>' . _('Cantidad') . '</th>
						       <th>' . _('Almacen') . '</th>
						</tr>';
				     for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){
					echo '<tr class="OddTableRow">';
					echo '		<td><input type="text" name="part_' . $i . '" size=21 maxlength=20></td>
							<td><input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qty_' . $i . '" size=6 maxlength=6></td>
							<td>';
							$result2 = DB_query($SQL,$db);
							echo "<select name='Stock_". $i . "'>";
							while ($myrow2 = DB_fetch_array($result2)) {
								echo "<option value=". $myrow2['loccode'] . ">" . $myrow2['locationname'];
							}
							echo '</td>
					      </tr>';
				     }
					echo '</table>
				</td>
			</tr>
			<tr>
				<td>
				<br><div class="centre">
					<input type="submit" name="QuickEntry" value="' . _('Entrada Rapida') . '">
					<input type=submit name="Search" value="'. _('Buscar').'">
					<input type=hidden name="lineaxs" value='.$totallineas.'>
					
				</div>
				
				</td>
			</tr>
		</table>';
	}
	echo '</td></tr></table>';	

/*****************************************************************************************/

if (isset($_GET['NewOrder']) and $_GET['NewOrder']!='') {
	echo '<script  type="text/javascript">defaultControl(document.SelectCustomer.CustCode);</script>';	
}
include('includes/footer.inc');
?>
