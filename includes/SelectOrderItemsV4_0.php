<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

/* $Revision: 4.2 $ 
Elaboro: Desarrollador
FECHA DE MODIFICACION: 24-ene-2011
CAMBIOS:
   1. Listado de productos por lista de precio x tipo de cliente
   
 FIN DE CAMBIOS
*/
include('includes/DefineCartClassV2_0.php');
include('includes/DefineSerialItems.php');
$PageSecurity = 1;
include('includes/session.inc');
$debuggaz = 0;

if (isset($_GET['PartSearch'])) $_POST['PartSearch'] = $_GET['PartSearch'];
if (isset($_GET['CurrAbrev'])) $_POST['CurrAbrev'] = $_GET['CurrAbrev'];
if (isset($_GET['Tagref'])) $_POST['Tagref'] = $_GET['Tagref'];
if (isset($_GET['StockCat'])) $_POST['StockCat'] = $_GET['StockCat'];
if (isset($_GET['StockLock'])) $_POST['StockLock'] = $_GET['StockLock'];
if (isset($_GET['Keywords'])) $_POST['Keywords'] = $_GET['Keywords'];
if (isset($_GET['StockCode'])) $_POST['StockCode'] = $_GET['StockCode'];
if (isset($_GET['QuickEntry'])) $_POST['QuickEntry'] = $_GET['QuickEntry'];
if (isset($_GET['Search'])) $_POST['Search'] = $_GET['Search'];
if (isset($_GET['lineaxs'])) $_POST['lineaxs'] = $_GET['lineaxs'];

if (isset($_GET['part_1'])) {
	$_POST['part_1'] = $_GET['part_1'];
	$_POST['qty_1'] = $_GET['qty_1'];
	$_POST['price_1'] = $_GET['price_1'];
	$_POST['desc1_1'] = $_GET['desc1_1'];
	$_POST['desc2_1'] = $_GET['desc2_1'];
	$_POST['desc3_1'] = $_GET['desc3_1'];
	$_POST['Stock_1'] = $_GET['Stock_1'];
}



/* ESTA VARIABLE GUARDA EL NUMERO DE PRODUCTOS EN CAPTURA RAPIDA */
if (isset($_POST['lineaxs'])) {
	$totallineas=$_POST['lineaxs'];
}elseif (isset($_GET['lineaxs'])){
	$totallineas=$_GET['lineaxs'];
}else{
	$totallineas=0;
}

/* $debuggaz=1 para desplegar diferentes mensajes en pantalla para ver valores de variables... */
if ($debuggaz==1)
	echo '$totallineas:'.$totallineas.'<br>';

unset($_SESSION['quotationANT']);
if (isset($_POST['ModifyOrderNumber'])) {
	$ordernumber = $_POST['ModifyOrderNumber'];
}elseif(isset($_SESSION['ExistingOrder'])){
	$ordernumber =  $_SESSION['ExistingOrder'];
}elseif(isset($_GET['ModifyOrderNumber'])){
	$ordernumber =  $_GET['ModifyOrderNumber'];
}else{
	$ordernumber =0;
}
if ($debuggaz==1)
	echo '$ordernumber:'.$ordernumber.'<br>';

if (isset($ordernumber) and $ordernumber<>'') {
	$title = _('Modificar Pedido') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$title = _('Introducir Datos De Pedido');
}
if ($debuggaz==1)
	echo '$title:'.$title.'<br>';

include('includes/header.inc');
include('includes/GetPrice.inc');

$funcion=4;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/GetSalesTransGLCodes.inc');


if (isset($_POST['Recalculate']) ){
   unset($_POST['QuickEntry']);
}

if ($debuggaz==1)
	echo "POST['QuickEntry']:".$_POST['QuickEntry']."<br>";
	
if (isset($_POST['order_items'])){
	$xbill=0;
	foreach ($_POST as $key => $value) {
		
		if ($debuggaz==1)
			echo $key.' - '.$value. '<br>';
			if (strstr($key,"itm")) {
				$NewItem_array[substr($key,3)] = trim($value);
				
			}
	}
	
}
if ($debuggaz==1)
	echo "POST['order_items']:".$_POST['order_items']."<br>";

if (isset($_GET['agregarproducto'])){
	foreach ($_GET as $key => $value) {
		if (strstr($key,"itm")) {
			$NewItem_array[substr($key,3)] = trim($value);
			$_POST['order_items'] = '1';
			$precioproductoagregado = $_GET['precioproductoagregado'];
			$costoproductoagregado =$_GET['costoproductoagregado'];	
		}
	}
}
if ($debuggaz==1)
	echo "GET['agregarproducto']:".$_GET['agregarproducto']."<br>";

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}
if ($debuggaz==1)
	echo "GET['NewItem']:".$_GET['NewItem']."<br>";

if ($debuggaz==1)
	echo "GET['identifier']:".$_GET['identifier']."<br>";
if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}
if ($debuggaz==1)
	echo "identifier:".$identifier."<br>";
	
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
//echo "moneda:".$_SESSION['CurrAbrev'];
if ($debuggaz==1)
	echo "SESSION['CurrAbrev']:".$_SESSION['CurrAbrev']."<br>";
	
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
if ($debuggaz==1)
	echo "SESSION['Tagref']:".$_SESSION['Tagref']."<br>";
	
$qohsql = "SELECT areacode
	FROM tags
	WHERE   tagref = '" . $_SESSION['Tagref'] . "'";
$qohresult =  DB_query($qohsql,$db);
$qohrow = DB_fetch_row($qohresult);
$codigoarea=$qohrow[0];

if ($codigoactual!=$myrow['stockid']){
     if ($xsuc!=1) {
	     echo "</td></tr>";
     }
}
	
if (isset($_GET['placa'])){
	$_SESSION['placa']=$_GET['placa'];
	$placa=$_SESSION['placa'];
}

if (isset($_GET['kilometraje'])){
	$_SESSION['kilometraje']=$_GET['kilometraje'];
	$kilometraje=$_SESSION['kilometraje'];
}

if (isset($_GET['serie'])){
	$_SESSION['serie']=$_GET['serie'];
	$serie=$_SESSION['serie'];
}

if (isset($_GET['serie'])){
	$_SESSION['serie']=$_GET['serie'];
}

if (isset($_GET['anticipo'])){
	$_SESSION['anticipo']=$_GET['anticipo'];
	$anticipo=$_SESSION['anticipo'];
}

//echo $SQL;
if ($debuggaz==1) {
	echo 'CLIENTE:'.$_SESSION['Items'.$identifier]->DebtorNo.'<br>';
	echo 'IDENTIFIER:'.$identifier.'<br>';
	echo 'SID:'.SID.'<br>';
}
	
unset($listaloccxbussines);


//ARREGLO PARA CONOCER ALMACENES POR CurrAbrev
if (isset($_SESSION['Tagref']) and strlen($_SESSION['Tagref'])>0){
	
	$SQL='SELECT distinct l.loccode,t.legalid
	  FROM areas a, tags t, locations l, sec_loccxusser sec
	  WHERE t.areacode=a.areacode
		AND  l.loccode=sec.loccode 
		AND sec.userid="'.$_SESSION['UserID'].'"
		AND l.tagref=t.tagref
		AND l.tagref='.$_SESSION['Tagref'];
	
	$result_tag = DB_query($SQL,$db);
	$listaloccxbussines=array();
	$counter_bussines=0;
	while ($myrow_bussines = DB_fetch_array($result_tag)) {
		$listaloccxbussines[$counter_bussines]=$myrow_bussines['loccode'];
		$counter_bussines=$counter_bussines + 1;
	}
}

/*PARA EL CAMBIO DE LA LISTA DE PRECIOS DESDE LA LIGA*/
if (isset($_GET['SalesType'])){
	$_SESSION['Items'.$identifier]->DefaultSalesType=$_GET['SalesType'];
	$_SESSION['SalesType']=$_GET['SalesType'];
}

// precio de lista
if (isset($_SESSION['SalesType']) and strlen($_SESSION['SalesType'])>0 ){	
	$_SESSION['Items'.$identifier]->DefaultSalesType=$_SESSION['SalesType'];
	$_SESSION['SalesType']=$_SESSION['SalesType'];
} else {
	if (isset($_POST['SalesType'])) {
		$_SESSION['Items'.$identifier]->DefaultSalesType=$_POST['SalesType'];
		$_SESSION['SalesType']=$_POST['SalesType'];
	} else {
		if (isset($_GET['SalesType'])){
			$_SESSION['Items'.$identifier]->DefaultSalesType=$_GET['SalesType'];
			$_SESSION['SalesType']=$_GET['SalesType'];
		}
	}
}

if ($debuggaz==1)
	echo "SESSION['SalesType']:".$_SESSION['SalesType']."<br>";

/* inicializa variable de cotizacion */
if (!isset($_SESSION['quotation'])) { $_SESSION['quotation']=0; }

if ($debuggaz==1)
	echo "SESSION['quotation']:".$_SESSION['quotation']."<br>";

/*******************************************************************************************************************/				
/* AQUI INICIA EL PROCESO DE UNA NUEVA ORDEN      ******************************************************************/
if (isset($_GET['NewOrder'])){

	/* New order entry - clear any existing order details from the Items object and initiate a newy */
	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
	}
	
	unset ($_SESSION['SalesType']);
	
	$_SESSION['ExistingOrder']=0;
	$_SESSION['Items'.$identifier] = new cart;
	if (count($_SESSION['AllowedPageSecurityTokens'])==1){ //its a customer logon
		$_SESSION['Items'.$identifier]->DebtorNo=$_SESSION['CustomerID'];
		$_SESSION['RequireCustomerSelection']=0;
	} else {
		$_SESSION['Items'.$identifier]->DebtorNo='';
		$_SESSION['RequireCustomerSelection']=1;
	}	
}
/*******************************************************************************************************************/				
// archivo de inclusion para generar orden de compra en automatico
if(isset($_POST['AutomaticCompra'])){
	include('includes/Automaticpurchase.inc');
	
}
/*******************************************************************************************************************/				


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
	//echo $OrderHeaderSQL;
	if ($debuggaz==1)			
		echo $OrderHeaderSQL;
	
	$ErrMsg =  _('La orden de venta no se puede  recuperar por que');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);
	if (DB_num_rows($GetOrdHdrResult)==1) {
		$myrow = DB_fetch_array($GetOrdHdrResult);
		
		/* // Validacion NO necesaria, ya que el vendedor puede ser diferente al vendedor que registra la venta
		 if ($_SESSION['SalesmanLogin']!='' AND $_SESSION['SalesmanLogin']!=$myrow['salesman']){
			prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to modify this order'),'error');
			include('includes/footer.inc');
			exit;
		}*/
		
		$_SESSION['Tagref']=$myrow['tagref'];
		$_SESSION['CurrAbrev']=$myrow['currcode'];
		$_SESSION['SelectedVehicle']=$myrow['vehiculo'];
		//echo "<br>vehiculo:".$myrow['debtorno'];
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
		$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
		$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
		if (DB_num_rows($GetCurrency)==1) {	       
			$myrowCurrency = DB_fetch_array($GetCurrency);
			$_SESSION['CurrencyRate']=$myrowCurrency['rate'];
		}else{
			$_SESSION['CurrencyRate']=1;
		}
		
		/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
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
					salesorderdetails.quantity,
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
					salesorderdetails.quantity,
					salesorderdetails.warranty,
					stockcategory.redinvoice,
					stockcategory.disabledprice,
					stockmaster.taxcatidret,
					salesorderdetails.pocost,
					salesorderdetails.servicestatus,
					salesorderdetails.salestype
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
		//echo $LineItemsSQL.'<br>';		
		if ($debuggaz==1)
			echo '<br>LINES: '.$LineItemsSQL;
		
		$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
		if (db_num_rows($LineItemsResult)>0) {
			while ($myrow=db_fetch_array($LineItemsResult)) {
				if ($myrow['completed']==0){
					//echo "entra";
				if ($_SESSION['TypeCostStock']==1){
					$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$myrow['stkcode'], $db);
				}else{
					$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
					$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$myrow['stkcode'], $db);
				}
					
					$existenciasalma=ExistenciasXAlmacen($myrow['fromstkloc1'],$_GET['ModifyOrderNumber'],$myrow['stkcode'],$db);
					$myrow['qohatloc']=$myrow['qohatloc']-$existenciasalma;
					$unitprice=$myrow['unitprice'];
					$ssql = 'SELECT case when actprice>0 then actprice* (1+(margenaut/100))else stdcostunit* (1+(margenaut/100)) end ';
					$ssql .= ' FROM purchorders o, purchorderdetails d, locations l, stockmaster s,stockcategory c';
					$ssql .= ' WHERE l.loccode=o.intostocklocation';
					$ssql .= ' AND o.orderno=d.orderno';
					$ssql .= ' AND s.stockid=d.itemcode';
					$ssql .= ' AND s.categoryid=c.categoryid';
					$ssql .= ' AND s.stockid="'.$myrow['stkcode'].'"';
					#$ssql .= ' AND d.completed=0';
					$ssql .= ' AND requisitionno="'.$_GET['ModifyOrderNumber'] .'"';
					$resultorders = DB_query($ssql,$db);
					/*if (DB_num_rows($resultorders)>0)
					{
						$myrowdetails = DB_fetch_array($resultorders);
						$unitprice=$myrowdetails[0];
					}*/
					//echo '<br>entraaaaaa     tipo:'.$myrow['salestype'];	
					$_SESSION['Items'.$identifier]->add_to_cart($myrow['stkcode'],
											$myrow['quantity'],
											$myrow['description'],
											$unitprice,
											$myrow['discountpercent'],
											$myrow['units'],
											$myrow['volume'],
											$myrow['kgs'],
											$myrow['qohatloc'],
											$myrow['mbflag'],
											$myrow['actualdispatchdate'],
											$myrow['qtyinvoiced'],
											$myrow['discountcategory'],
											$myrow['controlled'],	/*Controlled*/
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
											$myrow['warranty'],
											0,
											$myrow['redinvoice'],
											$myrow['pocost'],
											$myrow['disabledprice'],
											$myrow['servicestatus'],
											$myrow['salestype']								
										);
					
					/*Just populating with existing order - no DBUpdates */
						if ($_SESSION['MultipleBilling']==1){
							$_SESSION['loccode']=$myrow['fromstkloc1'];
						}
					}
					
					if ($debuggaz==1)
						echo '<br>LINEAXX: '.$myrow['orderlineno'];
			
					$LastLineNo = $myrow['orderlineno'];
					//echo '<br>linea:'.$LastLineNo.' '.$myrow['stkcode'].'tax:'.$myrow['taxcatid'].'costo:'.$EstimatedAvgCost.'<br><br>';
					//$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
					$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
					if ($myrow['taxcatidret']>0){
						$_SESSION['Items'.$identifier]->GetTaxesRet($LastLineNo);
						
					}
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
		} //end of checks on returned data set
		
	}
	
	$_SESSION['AccessOrder']=0;
	$_POST['PartSearch']='yes';
}
/************************************************************************************************/


/*******************************************************************************************************************/				
/* SI ES UNA ORDEN NUEVA ENTRA AQUI PUESTO QUE NO SE HAN ASIGNADO VALORES A LA SESSION ITEMS      ******************/
if (!isset($_SESSION['Items'.$identifier])){
	
	/* It must be a new order being created $_SESSION['Items'.$identifier] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */
	
	/* ESTA VARIABLE ES IMPORTANTE YA QUE DETERMINA SI SE INSERTA O SE ACTUALIZA EL REGISTRO EN LA
	   SIGUIENTE PANTALLA DE Delivery SCREEN */
	$_SESSION['ExistingOrder']=0;
	
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

// cambio 

/************************************************************************************************/
/* ENTRA AQUI SI SE BUSCO CLIENTES POR CAMPOS OPCIONALES O SI QUEREMOS VER LA PAGINA SIGUIENTE DE
	LOS RESULTADOS DE LA BUSQUEDA O IR A UNA PAGINA DE RESULTADOS DIRECTAMENTE */
if ((isset($_POST['SearchCust']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) AND $_SESSION['RequireCustomerSelection']==1){
	
	if (isset($_POST['SearchCust'])){
		/* SELECCIONA LA PRIMERA PAGINA DE RESULTADOS INCIALMENTE */
		$_POST['PageOffset'] = 1;
	}
	if (($_POST['CustCode']!='') AND (($_POST['CustKeywords']!='') OR ($_POST['CustPhone']!='') OR ($_POST['CustTaxid']!='') OR ($_POST['CustContact']!=''))) {
		prnMsg( _('Se ha utilizado el codigo, nombre o telefono de las oficinas del cliente'), 'warn');
	}
	if (($_POST['CustCode']!='') AND ($_POST['CustPhone']!='')) {
		prnMsg(_('Se ha utilizado el codigo de cliente para la busqueda.'), 'warn');
	}
	if (($_POST['CustKeywords']=='') AND ($_POST['CustCode']=='')  AND ($_POST['CustPhone']=='') AND ($_POST['CustTaxid']=='') AND ($_POST['CustContact']=='')) {
		prnMsg(_('Se ha utilizado el codigo de cliente o telefono para realizar la busqueda.'), 'warn');
	} 
		//echo "entra";
		/**********************************************************************************/
		/* GENERA EL SQL DE LA BUSQUEDA DEL CLIENTE */
		
		/* SI LA BUSQUEDA ES POR NOMBRE DEL CLIENTE */
		if (strlen($_POST['CustKeywords'])>0) {
			//insert wildcard characters in spaces
			$_POST['CustKeywords'] = strtoupper(trim($_POST['CustKeywords']));
			$i=0;
			$SearchString = '%';
			while (strpos($_POST['CustKeywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['CustKeywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['CustKeywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['CustKeywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['CustKeywords'],$i).'%';
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name,
					custbranch.taxid,
					debtorsmaster.currcode,
					custbranch.braddress3 as ciudad,
					debtorsmaster.blacklist,
					vehiclesbycostumer.vehicleno,
					concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
					vehiclemarks.mark,vehiclemodels.model
				FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
				        INNER JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='".$_SESSION['UserID']."'
					LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
						AND vehiclesbycostumer.branchcode=custbranch.branchcode
					LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
					LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
				WHERE (custbranch.brname " . LIKE . " '%" . $SearchString . "%' or
				debtorsmaster.name " . LIKE . " '%" . $SearchString . "%') ";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			if (strlen(trim($_POST['plate']))>0 and $_POST['plate']!=''){
				$SQL .= " AND vehiclesbycostumer.plate like '%" . $_POST['plate'] . "%'";
			}
			if (strlen(trim($_POST['seriecode']))>0 and $_POST['seriecode']!=''){
				$SQL .= " AND vehiclesbycostumer.serie like '%" . $_POST['seriecode'] . "%'";
			}
			if (strlen(trim($_POST['Noeco']))>0 and $_POST['Noeco']!=''){
				$SQL .= " AND vehiclesbycostumer.numeco like '%" . $_POST['Noeco'] . "%'";
			}	
			$SQL .=	" AND custbranch.disabletrans=0 and
				(custbranch.area = '".$_POST['SalesArea']."' or '".$_POST['SalesArea']."'='0')
				ORDER BY custbranch.debtorno, custbranch.branchcode, vehiclesbycostumer.vehicleno";
		} elseif (strlen($_POST['CustCode'])>0){
			$_POST['CustCode'] = strtoupper(trim($_POST['CustCode']));
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name,
					custbranch.taxid,
					debtorsmaster.currcode,
					custbranch.braddress3 as ciudad,
						debtorsmaster.blacklist,
					vehiclesbycostumer.vehicleno,
					concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
					vehiclemarks.mark,vehiclemodels.model
				FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
				        INNER JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='".$_SESSION['UserID']."'
					LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
						AND vehiclesbycostumer.branchcode=custbranch.branchcode
					LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
					LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
				WHERE  (custbranch.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
				OR custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%')";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			if (strlen(trim($_POST['plate']))>0 and $_POST['plate']!=''){
				$SQL .= " AND vehiclesbycostumer.plate like '%" . $_POST['plate'] . "%'";
			}
			if (strlen(trim($_POST['seriecode']))>0 and $_POST['seriecode']!=''){
				$SQL .= " AND vehiclesbycostumer.serie like '%" . $_POST['seriecode'] . "%'";
			}
			if (strlen(trim($_POST['Noeco']))>0 and $_POST['Noeco']!=''){
				$SQL .= " AND vehiclesbycostumer.numeco like '%" . $_POST['Noeco'] . "%'";
			}
			
			$SQL .=	' AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno, custbranch.branchcode, vehiclesbycostumer.vehicleno ';
		} elseif (strlen($_POST['CustTaxid'])>0){
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name,
					custbranch.taxid,
					debtorsmaster.currcode,
					custbranch.braddress3 as ciudad,
					debtorsmaster.blacklist,
					vehiclesbycostumer.vehicleno,
					concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
					vehiclemarks.mark,vehiclemodels.model
				FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
				        INNER JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='".$_SESSION['UserID']."'
					LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
						AND vehiclesbycostumer.branchcode=custbranch.branchcode
					LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
					LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
				WHERE custbranch.taxid " . LIKE . " '%" . $_POST['CustTaxid'] . "%'";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='%" . $_SESSION['SalesmanLogin'] . "%'";
			}
			if (strlen(trim($_POST['plate']))>0 and $_POST['plate']!=''){
				$SQL .= " AND vehiclesbycostumer.plate like '%" . $_POST['plate'] . "%'";
			}
			if (strlen(trim($_POST['seriecode']))>0 and $_POST['seriecode']!=''){
				$SQL .= " AND vehiclesbycostumer.serie like '%" . $_POST['seriecode'] . "%'";
			}
			if (strlen(trim($_POST['Noeco']))>0 and $_POST['Noeco']!=''){
				$SQL .= " AND vehiclesbycostumer.numeco like '%" . $_POST['Noeco'] . "%'";
			}
			$SQL .=	' AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno, custbranch.branchcode, vehiclesbycostumer.vehicleno';
		} elseif (strlen($_POST['CustContact'])>0){
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name,
					custbranch.taxid,
					debtorsmaster.currcode,
					custbranch.braddress3 as ciudad,
					debtorsmaster.blacklist,
					vehiclesbycostumer.vehicleno,
					concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
					vehiclemarks.mark,vehiclemodels.model
				FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
				        INNER JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='".$_SESSION['UserID']."'
					LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
						AND vehiclesbycostumer.branchcode=custbranch.branchcode
					LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
					LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
				WHERE custbranch.contactname " . LIKE . " '%" . $_POST['CustContact'] . "%'";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			if (strlen(trim($_POST['plate']))>0 and $_POST['plate']!=''){
				$SQL .= " AND vehiclesbycostumer.plate like '%" . $_POST['plate'] . "%'";
			}
			if (strlen(trim($_POST['seriecode']))>0 and $_POST['seriecode']!=''){
				$SQL .= " AND vehiclesbycostumer.serie like '%" . $_POST['seriecode'] . "%'";
			}
			if (strlen(trim($_POST['Noeco']))>0 and $_POST['Noeco']!=''){
				$SQL .= " AND vehiclesbycostumer.numeco like '%" . $_POST['Noeco'] . "%'";
			}
			$SQL .=	" AND custbranch.disabletrans=0 and
				(custbranch.area = '".$_POST['SalesArea']."' or '".$_POST['SalesArea']."'='0')
				ORDER BY custbranch.debtorno, custbranch.branchcode, vehiclesbycostumer.vehicleno";
		} elseif (strlen($_POST['CustPhone'])>0){
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name,
					custbranch.taxid,
					debtorsmaster.currcode,
					custbranch.braddress3 as ciudad,
					debtorsmaster.blacklist,
					vehiclesbycostumer.vehicleno,
					concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
					vehiclemarks.mark,vehiclemodels.model
				FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
				        INNER JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='".$_SESSION['UserID']."'
					LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
						AND vehiclesbycostumer.branchcode=custbranch.branchcode
					LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
					LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
				WHERE custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'";				
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			if (strlen(trim($_POST['plate']))>0 and $_POST['plate']!=''){
				$SQL .= " AND vehiclesbycostumer.plate like '%" . $_POST['plate'] . "%'";
			}
			if (strlen(trim($_POST['seriecode']))>0 and $_POST['seriecode']!=''){
				$SQL .= " AND vehiclesbycostumer.serie like '%" . $_POST['seriecode'] . "%'";
			}
			if (strlen(trim($_POST['Noeco']))>0 and $_POST['Noeco']!=''){
				$SQL .= " AND vehiclesbycostumer.numeco like '%" . $_POST['Noeco'] . "%'";
			}
			$SQL .=	" AND custbranch.disabletrans=0 and
				(custbranch.area = '".$_POST['SalesArea']."' or '".$_POST['SalesArea']."'='0')
				ORDER BY custbranch.debtorno, custbranch.branchcode, vehiclesbycostumer.vehicleno";
		}elseif (strlen($_POST['plate'])>0 or strlen($_POST['seriecode'])>0 or strlen($_POST['Noeco'])>0){
			$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name,
					custbranch.taxid,
					debtorsmaster.currcode,
					custbranch.braddress3 as ciudad,
					debtorsmaster.blacklist,
					vehiclesbycostumer.vehicleno,
					concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
					vehiclemarks.mark,vehiclemodels.model
				FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
					INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
				        INNER JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='".$_SESSION['UserID']."'
					LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
						AND vehiclesbycostumer.branchcode=custbranch.branchcode
					LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
					LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
				WHERE custbranch.disabletrans=0 " ;				
			
			if (strlen(trim($_POST['plate']))>0 and $_POST['plate']!=''){
				$SQL .= " AND vehiclesbycostumer.plate like '%" . $_POST['plate'] . "%'";
			}
			if (strlen(trim($_POST['seriecode']))>0 and $_POST['seriecode']!=''){
				$SQL .= " AND vehiclesbycostumer.serie like '%" . $_POST['seriecode'] . "%'";
			}
			if (strlen(trim($_POST['Noeco']))>0 and $_POST['Noeco']!=''){
				$SQL .= " AND vehiclesbycostumer.numeco like '%" . $_POST['Noeco'] . "%'";
			}
			$SQL .=	" 
				ORDER BY custbranch.debtorno, custbranch.branchcode, vehiclesbycostumer.vehicleno";
		}
		/* FIN DE GENERACION DEL SQL PARA LA BUSQUEDA DE CLIENTES */
		/**********************************************************************************/
		//echo $SQL;
		$ErrMsg = _('La busquedas en los registros de clientes solicitada no puede ser recuperada por');
		$result_CustSelect = DB_query($SQL,$db,$ErrMsg);
		if (DB_num_rows($result_CustSelect)==1){
			/* SI EL RESULTADO ARROJA SOLO UN CLIENTE, ENTONCES DIRECTAMENTE ASIGNA LA VARIABLE Select */
			$myrow=DB_fetch_array($result_CustSelect);
			$_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'].'|Auto:'.$myrow['vehicleno'];
		} elseif (DB_num_rows($result_CustSelect)==0){
			/* SI EL RESULTADO ARROJA NINGUN REGISTRO, ENTONCES DESPLEGAR LIGA PARA NUEVO CLIENTE*/
			prnMsg(_('No existen registros de Oficinas para el cliente que contengan los criterios de búsqueda') . ' - ' . _('intentelo de nuevo') . ' - ' . _('Nota: El Nombre de la sucursal puede ser diferente al Nombre del cliente'),'info');
		}
		
		/* MANTIENE VALORES DE RESULTADOS DE CLIENTES EN $result_CustSelect RECORDSET */
		
	//} /*one of keywords or custcode was more than a zero length string */
} /* end of if search for customer codes/names */
   
/************************************************************************************************/
   
/* SI SELECCIONAMOS LIGA DE CLIENTE DE MOSTRADOR, ASIGNA VALOR A VARIABLE POST */   
if (isset($_GET['Select']) AND $_GET['Select']!='') {
	$_POST['Select'] = $_GET['Select'];
}

/* ENTRAREMOS A ESTA PARTE DEL CODIGO SOLO SI VENIMOS DE SELECCIONAR UN CLIENTE DE LA PANTALLA DE
   RESULTADOS DE BUSQUEDA O DE LA LIGA DE CLIENTE DE MOSTRADOR O SI EL RESULTADO DE LA BUSQUEDA
   DE CLIENTES SOLO TRAJO UN RESULTADO */
if (isset($_POST['Select']) AND $_POST['Select']!='') {
	
	//echo "entra session cliente".$_POST['Select'] ;
	
	
	/* EXTRAER EL CODIGO DE LA SUCURSAL DE LA VARIABLE Select, ES EL VALOR QUE SIGUE DEL GUION */
	$branchcode= substr($_POST['Select'],strpos($_POST['Select'],' - ')+3);
	$branch=explode('|',$branchcode);
	
	$_SESSION['Items'.$identifier]->Branch=$branch[0];
	$vehicleno=$branch[1];
	$vehicleauto=explode(':',$vehicleno);
	$vehicleno=$vehicleauto[1];
	//echo 'vehiculo:'.$vehicleno;
	//exit;
	//substr($_SESSION['Items'.$identifier]->Branch,strpos($_SESSION['Items'.$identifier]->Branch,'  ')+3);
	/* EXTRAER EL CODIGO DEL CLIENTE DE LA VARIABLE Select, ES EL VALOR QUE ESTA ANTES DEL GUION
	   Y ASIGNA EL RESULTADO A LA MISMA VARIABLE POST Select */
	$_POST['Select'] = substr($_POST['Select'],0,strpos($_POST['Select'],' - '));
	$_SESSION['SelectedEvaluation']=$_GET['SelectedEvaluation'];
	$_SESSION['SelectedVehicle']=$vehicleno;
	// Enviar datos a captura de seleccion de vehiculo en caso de no tener placas
	if ($_SESSION['VehicleXCustomer']==1 and strlen(trim($vehicleno))==0){
		// redirecciona a la pagina de captura de vehiculos
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/VehicleXCostumer.php?frompage=selectorderitems&DebtorNo=" . $_POST['Select'] . '&BranchCode=' . $_SESSION['Items'.$identifier]->Branch ."&identifier=".$identifier. "&CurrAbrev=".$_SESSION['CurrAbrev']."&Tagref=".$_SESSION['Tagref']."&ModifyOrderNumber=".$_SESSION['ExistingOrder']."'>";
		echo '<div class="centre">' . _('Debes ser direccionado al alta de vehiculos por cliente') .
		'. ' . _('Si esto no sucede') .' (' . _('Tu explorador no soporta META Refresh, da click en el siguiente enlace') . ') ' .
		"<a href='" . $rootpath . "/VehicleXCostumer.php?" .
		SID . "&frompage=selectorderitems&DebtorNo=" . $_POST['Select'] . '&BranchCode=' . $_SESSION['Items'.$identifier]->Branch ."&identifier=".$identifier.
		"&CurrAbrev=".$_SESSION['CurrAbrev']."&Tagref=".$_SESSION['Tagref']."&ModifyOrderNumber=".$_SESSION['ExistingOrder'].'.</div>';
		exit;
	}elseif($_SESSION['VehicleXCustomer']==1 and strlen(trim($vehicleno))>0 and strlen($_SESSION['SelectedEvaluation'])==0 and $_SESSION['SelectedEvaluation']==''){
		//redirecciona a la pagina de captura de puntos de seguridad
	/*	echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/VehicleEvaluation.php?SelectedVehicle=".$vehicleno."&frompage=selectorderitems&DebtorNo=" . $_POST['Select'] . '&BranchCode=' . $_SESSION['Items'.$identifier]->Branch ."&identifier=".$identifier. "&CurrAbrev=".$_SESSION['CurrAbrev']."&Tagref=".$_SESSION['Tagref']."&ModifyOrderNumber=".$_SESSION['ExistingOrder']."'>";
		echo '<div class="centre">' . _('Debes ser direccionado al alta de vehiculos por cliente') .
		'. ' . _('Si esto no sucede') .' (' . _('Tu explorador no soporta META Refresh, da click en el siguiente enlace') . ') ' .
		"<a href='" . $rootpath . "/VehicleEvaluation.php?" .
		SID . "&SelectedVehicle=".$vehicleno."&frompage=selectorderitems&DebtorNo=" . $_POST['Select'] . '&BranchCode=' . $_SESSION['Items'.$identifier]->Branch ."&identifier=".$identifier.
		"&CurrAbrev=".$_SESSION['CurrAbrev']."&Tagref=".$_SESSION['Tagref']."&ModifyOrderNumber=".$_SESSION['ExistingOrder']. '.</div>';
		exit;
*/
	}
	//echo "entra session cliente:".$placas.'<br>' ;
	/* VERIFICA QUE LA CUENTA DEL CLIENTE NO ESTA BOLETINADA ! */
	$sql = "SELECT debtorsmaster.name,
			holdreasons.dissallowinvoices,
			debtorsmaster.salestype,
			salestypes.sales_type,
			debtorsmaster.currcode,
			debtorsmaster.customerpoline,
			paymentterms.terms,
			currencies.rate as currency_rate,
			debtorsmaster.blacklist
		FROM debtorsmaster,
			holdreasons,
			salestypes,
			paymentterms,
			currencies
		WHERE debtorsmaster.salestype=salestypes.typeabbrev
			AND debtorsmaster.currcode = currencies.currabrev
			AND debtorsmaster.holdreason=holdreasons.reasoncode
			AND debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $_POST['Select'] . "'";
	$ErrMsg = _('Los detalles del cliente seleccionado') . ': ' .  $_POST['Select'] . ' ' . _('no se pueden recuperar, por que ');
	$DbgMsg = _('El SQL utilizado para recuperar los detalles del cliente fue') . ':';
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($result);
	//echo '<br>sql1:'.$sql;
	/* VERIFICA SI LA VARIABLE dissallowinvoices es diferente de uno */
	if ($myrow[1] != 1){
		
		/* EL VALOR DOS INDICA PRECAUCION !!! */
		if ($myrow[1] == 2){
			prnMsg(_('La') . ' ' . $myrow[0] . ' ' . _('cuenta esta marcada como una cuenta que tiene que ser observada. Pongase en contacto con el personal de control de creditos'),'warn');
		}
		
		
		/**********************************************************************************************/
		/* ASIGNA TODOS LOS VALORES DEL CLIENTE Y LA SUCURSAL DEL CLIENTE EN LAS VARIABLES DE SESSION */
		
		$_SESSION['Items'.$identifier]->DebtorNo=$_POST['Select'];
		$_SESSION['RequireCustomerSelection']=0;
		$_SESSION['Items'.$identifier]->CustomerName = $myrow[0];
		
		# the sales type determines the price list to be used by default the customer of the user is
		# defaulted from the entry of the userid and password.
		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow[2];
		$_SESSION['Items'.$identifier]->SalesTypeName = $myrow[3];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow[4];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow[5];
		$_SESSION['Items'.$identifier]->PaymentTerms = $myrow[6];
		$_SESSION['CurrencyRate'] = $myrow[7];
		$blacklist = $myrow[8];
		# the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway
		$sql = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
				custbranch.specialinstructions,
				custbranch.estdeliverydays,
				locations.locationname,
				custbranch.salesman,
				custbranch.taxid,
				custbranch.contactname,
				locations.taxprovinceid,
				custbranch.taxgroupid
			FROM custbranch INNER JOIN locations
				ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_POST['Select'] . "'";
		//echo '<br>sql2:'.$sql;	
		$ErrMsg = _('El registro de la oficina del cliente seleccionado') . ': ' . $_POST['Select'] . ' ' . _('no se puede recuperar por que');
		$DbgMsg = _('El SQL utilizado para recuperar los detalles de la oficina fue') . ':';
		$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result)==0){
			prnMsg(_('Los detalles de la oficina ') . ': ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('con el codigo del cliente') . ': ' . $_POST['Select'] . ' ' . _('no coinciden') . '. ' . _('Verifique la configuracion del cliente y la oficina'),'error');
			if ($debug==1){
				echo '<br>' . _('El SQL utilizado para recuperar los detalles de la oficina fue') . ':<br>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}
		
		// into_ echo
		echo '<br>';
		$myrow = DB_fetch_row($result);
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow[0];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow[1];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow[2];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow[3];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow[4];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow[5];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow[6];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow[7];
		$_SESSION['Items'.$identifier]->Email = $myrow[8];
		$_SESSION['Items'.$identifier]->Location = $myrow[9];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow[10];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow[11];
		$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow[12];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow[13];
		$_SESSION['Items'.$identifier]->LocationName = $myrow[14];
		$_SESSION['Items'.$identifier]->CostumerRFC = $myrow[16];
		$_SESSION['Items'.$identifier]->CostumerContact = $myrow[17];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow[18];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow[19];
		$_SESSION['Salesman']=$myrow[15];
		if ($_SESSION['Items'.$identifier]->SpecialInstructions)
			prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
			
		/* VERIFICACION DE LINEAS DE CREDITO */
		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit sales */
							   
			$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($_POST['Select'],$db);
			if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
				prnMsg(_('La') . ' ' . $myrow[0] . ' ' . _('cuenta esta en o sobre su limite de credito'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
				prnMsg(_('No se pueden realizar mas pedidos para ') . ' ' . $myrow[0] . ' ' . _('su cuenta esta en o sobre su limite de credito'),'warn');
				include('includes/footer.inc');
				exit;
			}
		}
		//Verificacion rfc
		if ($_SESSION['ForzarCapturaRFC']==1){
			if (strlen($_SESSION['Items'.$identifier]->CostumerRFC)==0){
				prnMsg(_('El RFC es necesario para facturar, verifique datos del cliente'),'error');
			}
		}
		//Verificacion lista Negra
		if ($blacklist==1){
				prnMsg(_('No se pueden realizar mas pedidos para ') . ' ' . $myrow[0] . ' ' . _('se encuentra en lista negra, consulte con personal administrativo'),'warn');
				include('includes/footer.inc');
				exit;
			
		}
		
		
		
		$_SESSION['AccessOrder']=1;
		if ($_SESSION['ExistingOrder'] ==0   and $_SESSION['ExtractOrderNumber1']!='' and $_SESSION['ExtractOrderNumber1']!=1){
			$_SESSION['AccessOrder']=0;
			$_POST['PartSearch']='yes';
			
		}else{
			$sql="Select *
			      from salesorders
			      where debtorno='". $_SESSION['Items'.$identifier]->DebtorNo."'
			       and branchcode='".$_SESSION['Items'.$identifier]->Branch."'
			       order by orderno desc
			       limit 1
			       ";
			$ErrMsg =  _('No existen ordenes de venta');
			//echo $sql;
			$GetOrderResult = DB_query($sql,$db,$ErrMsg);
			if (DB_num_rows($GetOrderResult)>0) {
				$_SESSION['AccessOrder']=1;
			}else{
				$_SESSION['AccessOrder']=0;
				$_POST['PartSearch']='yes';
			}
			
		}
		
		/* FIN DE ASIGNA TODOS LOS VALORES DEL CLIENTE Y LA SUCURSAL DEL CLIENTE EN LAS VARIABLES DE SESSION */
		/*****************************************************************************************************/
		
	} else {
		prnMsg(_('La') . ' ' . $myrow[0] . ' ' . _('cuenta se encuentra actualmente suspendida, pongase en contacto con el personal de control de creditos'),'warn');
	}
	
} elseif (!$_SESSION['Items'.$identifier]->DefaultSalesType OR $_SESSION['Items'.$identifier]->DefaultSalesType=='')	{
	
	/* ENTRA AQUI SI EL CLIENTE ESTA CREANDO SU PROPIA ORDEN */
	
	#Possible that the check to ensure this account is not on hold has not been done
	#if the customer is placing own order, if this is the case then
	#DefaultSalesType will not have been set as above
	$sql = "SELECT debtorsmaster.name,
			holdreasons.dissallowinvoices,
			debtorsmaster.salestype,
			debtorsmaster.currcode,
			debtorsmaster.customerpoline,
			currencies.rate as currency_rate
		FROM debtorsmaster, holdreasons , currencies
		WHERE debtorsmaster.holdreason=holdreasons.reasoncode
			AND debtorsmaster.currcode = currencies.currabrev	
			AND debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";
	if (isset($_POST['Select'])) {
		$ErrMsg = _('Los detalles para el cliente seleccionado') . ': ' . $_POST['Select'] . ' ' . _('no se puede recuperar, por que');
	} else {
		$ErrMsg = '';
	}
	$DbgMsg = _('El SQL utilizado para recuperar los datos de los clientes es') . ':<br>' . $sql;
	$result =DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$myrow = DB_fetch_row($result);
	if ($myrow[1] == 0){
		$_SESSION['CurrencyRate'] = $myrow[5];
		$_SESSION['Items'.$identifier]->CustomerName = $myrow[0];
		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow[2];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow[3];
		$_SESSION['Items'.$identifier]->Branch = $_SESSION['UserBranch'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow[4];
		$sql = "SELECT custbranch.brname,
			       custbranch.braddress1,
			       custbranch.braddress2,
			       custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.deliverblind,
				custbranch.estdeliverydays,
				locations.locationname,
				custbranch.salesman,
				custbranch.taxid,
				custbranch.contactname,
				locations.taxprovinceid,
				custbranch.taxgroupid
			FROM custbranch INNER JOIN locations
				ON custbranch.defaultlocation=locations.loccode
			WHERE custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";
		if (isset($_POST['Select'])) {
			$ErrMsg = _('Los datos de la Oficina del cliente') . ': ' . $_POST['Select'] . ' ' . _('no se pudieron recuperar por que');
		} else {
			$ErrMsg = '';
		}
		$DbgMsg = _('El SQL utilizado para obtener los detalles de la oficina es:');
		$result =DB_query($sql,$db,$ErrMsg, $DbgMsg);
		$myrow = DB_fetch_row($result);
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow[0];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow[1];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow[2];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow[3];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow[4];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow[5];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow[6];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow[7];
		$_SESSION['Items'.$identifier]->Email = $myrow[8];
		$_SESSION['Items'.$identifier]->Location = $myrow[9];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow[10];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow[11];
		$_SESSION['Items'.$identifier]->LocationName = $myrow[12];
		$_SESSION['Items'.$identifier]->CostumerRFC = $myrow[14];
		$_SESSION['Items'.$identifier]->CostumerContact = $myrow[15];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow[16];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow[17];
		//echo "entra aqui";
	} else {
		prnMsg(_('Lo sentimos, su cuenta ha quedado suspendida por algun motivo, pongase en contacto con el personal de control de creditos.'),'warn');
		include('includes/footer.inc');
		exit;
	}
} /* FIN DE ASIGNACION DE DATOS A REGISTROS DEL CLIENTE */
    
/*********************************************************************************************************************/

/* AQUI EXTRAEMOS ULTIMA VENTA DE ESTE CLIENTE */
if ( $_SESSION['ExistingOrder'] ==0
    and $_SESSION['ExtractOrderNumber']!=''
    and $_SESSION['ExtractOrderNumber']!=0
    and $_SESSION['Items'.$identifier]->DebtorNo!='' and $_SESSION['AccessOrder']==1){
	
	// extrae ultima pedido de venta
	$sql="Select *
	      from salesorders
	      where debtorno='". $_SESSION['Items'.$identifier]->DebtorNo."'
	       and branchcode='".$_SESSION['Items'.$identifier]->Branch."'
	       order by orderno desc
	       limit 1
	       ";
	$ErrMsg =  _('No existen ordenes de venta');
	$GetOrderResult = DB_query($sql,$db,$ErrMsg);
	if (DB_num_rows($GetOrderResult)>0) {
		$myrowx = DB_fetch_array($GetOrderResult);
		$orderno=$myrowx[0];
		if (isset($_SESSION['Items'.$identifier])){
			unset ($_SESSION['Items'.$identifier]->LineItems);
			unset ($_SESSION['Items'.$identifier]);
		}
		
		$_SESSION['RequireCustomerSelection'] = 0;
		$_SESSION['Items'.$identifier] = new cart;
		/*read in all the guff from the selected order into the Items cart  */
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
					custbranch.salesman,
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
					 salesorders.vehicleno
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
					AND debtorsmaster.paymentterms=paymentterms.termsindicator
					AND locations.loccode=salesorders.fromstkloc
					AND salesorders.orderno = ' . $orderno;
		//echo "sql".$OrderHeaderSQL;			
		$ErrMsg =  _('La orden de venta no se puede  recuperar por que');
		$GetOrdHdrResult = DB_query($OrderHeaderSQL,$db,$ErrMsg);
		if (DB_num_rows($GetOrdHdrResult)==1) {
			$myrow = DB_fetch_array($GetOrdHdrResult);
			$_SESSION['Tagref']=$myrow['tagref'];
			$_SESSION['SelectedVehicle']=$myrow['vehicleno'];
			$_SESSION['CurrAbrev']=$myrow['currcode'];
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
			$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
			$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
			if (DB_num_rows($GetCurrency)==1) {	       
				$myrowCurrency = DB_fetch_array($GetCurrency);
				$_SESSION['CurrencyRate']=$myrowCurrency['rate'];
			}else{
				$_SESSION['CurrencyRate']=1;
			}
			
			/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
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
						salesorderdetails.quantity,
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
						salesorderdetails.quantity,
						salesorderdetails.warranty,
						stockcategory.redinvoice,
						stockcategory.disabledprice,
						stockmaster.taxcatidret,
						salesorderdetails.pocost,
						salesorderdetails.servicestatus
						salesorderdetails.salestype
					FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode = stockmaster.stockid
						INNER JOIN locstock ON locstock.stockid = stockmaster.stockid,
						locations, stockcategory
					WHERE  locstock.loccode=locations.loccode
						AND stockcategory.categoryid=stockmaster.categoryid
						AND locstock.loccode=salesorderdetails.fromstkloc
						AND salesorderdetails.completed=0
						AND salesorderdetails.orderno =" . $orderno . "
					ORDER BY salesorderdetails.orderlineno";
					
			$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
			$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
			//echo $LineItemsSQL."<br>";
			if (db_num_rows($LineItemsResult)>0) {
				while ($myrow=db_fetch_array($LineItemsResult)) {
					
						$existenciasalma=ExistenciasXAlmacen($myrow['fromstkloc1'],$orderno,$myrow['stkcode'],$db);
						$myrow['qohatloc']=$myrow['qohatloc']-$existenciasalma;
						
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
												0,
												$myrow['discountcategory'],
												$myrow['controlled'],	/*Controlled*/
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
												$myrow['standardcost'],
												$myrow['eoq'],
												$myrow['nextserialno'],
												$myrow['fromstkloc1'],
												$myrow['locationname1'],
												$myrow['discountpercent1'],
												$myrow['discountpercent2'],
												$myrow['warranty'],
												0,
												$myrow['redinvoice'],$myrow['pocost'],
												$myrow['disabledprice'],
												$myrow['servicestatus'],
												$myrow['salestype']
											);
						/*Just populating with existing order - no DBUpdates */
						//}
						
						if ($debuggaz==1)
							echo '<br>LINEAXX: '.$myrow['orderlineno'];
				
						$LastLineNo = $myrow['orderlineno'];
						
						
						
						
						
						//$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
						$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
						if ($myrow['taxcatidret']>0){
							$_SESSION['Items'.$identifier]->GetTaxesRet($LastLineNo);	
						}
						//echo "<br>lineay:".$LastLineNo;
				} /* line items from sales order details */
				 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
			} //end of checks on returned data set
		}// fin de consulta de orden encabezado
	}// no existen ordenes de venta anteriores
	$_SESSION['AccessOrder']=0;
}// fin de extract
/*******************************************************************************************************************/				

if ($debuggaz==1) {
	echo "PARA ENTRAR A SELECCION DE CLIENTE!<BR>";
	echo "SESSION['RequireCustomerSelection']:".$_SESSION['RequireCustomerSelection']."<br>";
	echo "SESSION['Items'.identifier]->DebtorNo:".$_SESSION['Items'.$identifier]->DebtorNo."<br>";
	echo "GET['ChangeCustomer']:".$_GET['ChangeCustomer']."<br>";
}

/******************************************************************************************************/
/*********   INICIO DE DESPLIEGUE DE PANTALLA DE BUSQUEDA DE CLIENTES *********************************/
if ($_SESSION['RequireCustomerSelection'] ==1 OR !isset($_SESSION['Items'.$identifier]->DebtorNo) OR $_SESSION['Items'.$identifier]->DebtorNo=='' OR isset($_GET['ChangeCustomer'])) {

	/* SI REGRESAMOS A BUSQUEDA DE CLIENTE PARA CAMBIARLO NO INICIALICES MONEDA Y UNIDAD NEGOCIO */
	if (!isset($_GET['ChangeCustomer'])) {
		unset($_SESSION['CurrAbrev']);
		unset($_SESSION['Tagref']);
		unset($_SESSION['loccode']);
	}
	
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . 
	' ' . _('Captura una Pedido o Cotizacion') . ' : ' . _('Busqueda Cliente.') . '</p>';
	echo '<div class="page_help_text">' . _('Los Pedidos/Cotizaciones son en base a sucursal. Un cliente puede tener varias sucursales.') . '</div>';
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF'] . '?' .SID .'&identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'] .'&Tagref='.$_SESSION['Tagref'];?>" name="SelectCustomer" method=post>
	<b><?php echo '<p>' . $msg; ?></p>
	<table border="1" width="100%" class="tableindex">
		<tr>
			<? /* PARTE IZQUIERDA PARA DESPLEGAR CAMPOS DE BUSQUEDA DE CLIENTE */ ?>
			<td valign=top width=40%>
				<table cellpadding=1 border="0" align="left">
					<?php
					//***********************************************************
					//*********INICIO AGREGA FUNCION DE ALTA CLIENTES************
					//***********************************************************				
					$sql = "SELECT  distinct F.functionid, F.submoduleid,F.title,F.url, F.categoryid,C.name,C.imagecategory,case when FxU.permiso is null then 1 else FxU.permiso end as permiso
						FROM sec_modules s,
						     sec_submodules sm,
						     www_users u,
						     sec_profilexuser PU,
						     sec_funxprofile FP,
						     sec_functions F left join sec_funxuser FxU on  FxU.functionid=F.functionid and FxU.userid='".$_SESSION['UserID']."',
						     sec_categories C
						WHERE s.moduleid=sm.moduleid and s.active=1
						      and FP.profileid=PU.profileid
						      and F.submoduleid=sm.submoduleid
						      and C.categoryid=F.categoryid
						      and u.userid=PU.userid and PU.userid='".$_SESSION['UserID']."'
						      and s.moduleid=2
						      and u.userid=PU.userid
						      and F.submoduleid=9
						      and F.functionid = 159
						      order by C.orderno, F.orderno, C.name,F.title
						      ";
					$ReFuntion = DB_query($sql, $db);
					if (DB_num_rows($ReFuntion)>0 ) {
						echo '<tr><td></td><td class="menu_group_item_button"  colspan="2">';
						echo '<a href="' . $rootpath . '/Customers.php?from=selectorderitems">' . _('Alta Nuevo Cliente') . '</a><br>';
						echo '</td></tr><tr height=15px><td></td></tr>';
					}
					
					//***********************************************************
					//*********FIN AGREGA FUNCION DE ALTA CLIENTES************
					//***********************************************************
					?>				
					<tr>
						<td><?php echo _('X Sucursal'); ?>:</td>
						<td><select name="SalesArea">'
						<?php
					
						/* Pinta las areas que puedo ver en base a mi configuracion de usuario	*/
						
						$SQL = "SELECT  areas.areacode, areas.areadescription";
						$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
						$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
						$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' 
								GROUP BY areas.areacode
								ORDER BY areas.areacode";
							
						$result=DB_query($SQL,$db);
						echo '<option value=0>TODAS...';
						while ($myrow=DB_fetch_array($result)){
							if (isset($_POST['SalesArea']) and $_POST['SalesArea']==$myrow['areacode']){
								echo '<option selected value=' . $myrow['areacode'] . '>' . $myrow['areadescription'];
							} else {
								echo '<option value=' . $myrow['areacode'] . '>' . $myrow['areadescription'];
							}
						}
						echo '</select></td>';
						// End select area
						
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					
					<tr>
						<td><?php echo _('X Clave'); ?>:</td>
						<?php
						if (isset($_POST['CustCode'])) {
							?>
							<td><input tabindex=1 type="Text" name="CustCode" value="<?php echo $_POST['CustCode']?>" size=18 maxlength=18></td>
							<?php
						} else {
							?>
							<td><input tabindex=1 type="Text" name="CustCode" size=15 maxlength=18></td>
							<?php
						}
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					<tr>
						<td><?php echo _('X Nombre'); ?>:</td>
						<?php
						if (isset($_POST['CustKeywords'])) {
							?>
							<td><input tabindex=2 type="Text" name="CustKeywords" value="<?php echo $_POST['CustKeywords']?>" size=30 maxlength=40></td>
							<?php
						} else {
							?>
							<td><input tabindex=2 type="Text" name="CustKeywords" size=30 maxlength=40></td>
							<?php
						}
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					<tr>
						<td><?php echo _('X RFC'); ?>:</td>
						<?php
						if (isset($_POST['CustTaxid'])) {
							?>
							<td><input tabindex=3 type="Text" name="CustTaxid" value="<?php echo $_POST['CustTaxid']?>" size=13 maxlength=20></td>
							<?php
						} else {
							?>
							<td><input tabindex=3 type="Text" name="CustTaxid" size=13 maxlength=20></td>
							<?php
						}
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					<tr>
						<td><?php echo _('X Contacto'); ?>:&nbsp;&nbsp;&nbsp;&nbsp;</td>
						<?php
						if (isset($_POST['CustContact'])) {
							?>
							<td><input tabindex=4 type="Text" name="CustContact" value="<?php echo $_POST['CustContact']?>" size=30 maxlength=40></td>
							<?php
						} else {
							?>
							<td><input tabindex=4 type="Text" name="CustContact" size=30 maxlength=40></td>
							<?php
						}
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					<tr>
						<td><?php echo _('X Telefono'); ?>:</td>
						<?php
						if (isset($_POST['CustPhone'])) {
							?>
							<td><input tabindex=5 type="Text" name="CustPhone" value="<?php echo $_POST['CustPhone']?>" size=13 maxlength=20></td>
							<?php
						} else {
							?>
							<td><input tabindex=5 type="Text" name="CustPhone" size=13 maxlength=20></td>
							<?php
						}
						?>
						
						<td>&nbsp;</td>
					</tr>
						<?php // BUSQUEDA POR VEHICULOS DEL CLIENTE
					
						if ($_SESSION['VehicleXCustomer']==1){	
					?>
					<tr>
						<td><?php echo _('X Placa'); ?>:</td>
						<?php
						if (isset($_POST['plate'])) {
							?>
							<td><input tabindex=1 type="Text" name="plate" value="<?php echo $_POST['plate']?>" size=18 maxlength=18></td>
							<?php
						} else {
							?>
							<td><input tabindex=1 type="Text" name="plate" size=15 maxlength=18></td>
							<?php
						}
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					
					<tr>
						<td><?php echo _('X Serie'); ?>:</td>
						<?php
						if (isset($_POST['seriecode'])) {
							?>
							<td><input tabindex=1 type="Text" name="seriecode" value="<?php echo $_POST['seriecode']?>" size=18 maxlength=18></td>
							<?php
						} else {
							?>
							<td><input tabindex=1 type="Text" name="seriecode" size=15 maxlength=18></td>
							<?php
						}
						?>
						<td><b><?php echo _('ó'); ?></b></td>
					</tr>
					
					<tr>
						<td nowrap><?php echo _('X No. Economico'); ?>:</td>
						<?php
						if (isset($_POST['Noeco'])) {
							?>
							<td><input tabindex=1 type="Text" name="Noeco" value="<?php echo $_POST['Noeco']?>" size=18 maxlength=18></td>
							<?php
						} else {
							?>
							<td><input tabindex=1 type="Text" name="Noeco" size=15 maxlength=18></td>
							<?php
						}
						?>
						<td></td>
					</tr>
					<?php
						}	
					/**********************************************************************************************/ ?>
					
					<tr>
						<td></td><td colspan="2">
							<div class="centre">
								<br>
								<input tabindex=4 type=submit name="SearchCust" value="<?php echo _('Buscar Cliente'); ?>">
								<input tabindex=5 type=submit action=reset value="<?php echo _('Limpia Campos'); ?>">
							</div>
						</td>
					</tr>
					
					<tr><td></td><td class="menu_group_item_button"  colspan="2">
					<!--<a href="<? echo $rootpath ?>/SelectOrderItems.php?<? echo SID .'&identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'] ?>&Select=<?php echo $_SESSION["ClaveClienteMostrador"]?>"><?php echo _('USAR CLIENTE MOSTRADOR...')?></a><br> -->
					<a href="<? echo $rootpath ?>/SelectOrderItemsV4_0.php?<? echo SID .'&identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'] ?>&Select=cMos_<?php echo intval($_SESSION["DefaultArea"]).' - cMos_'.intval($_SESSION["DefaultArea"])?>"><?php echo _('USAR CLIENTE MOSTRADOR...')?></a><br>
					</td></tr><tr height=15px><td></td></tr>
				</table>
			</td>
			<? /* FIN DE PARTE IZQUIERDA PARA DESPLEGAR CAMPOS DE BUSQUEDA DE CLIENTE    */ ?>
			<? /**************************************************************************/ ?>
			
			<? /* PARTE DERECHA PARA DESPLEGAR RESULTADOS DE LA BUSQUEDA DE CLIENTES */ ?>
			<td  valign=top>
				<?php
				if (isset($result_CustSelect)) {
					
					//****************************************************************
					//*********************INICIO PAGINACION**************************
					//****************************************************************
					
					$ListCount=DB_num_rows($result_CustSelect);
					$ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);					
					if (isset($_POST['Next'])) {
						if ($_POST['PageOffset'] < $ListPageMax) {
							$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
						}
					}
				
					if (isset($_POST['Previous'])) {
						if ($_POST['PageOffset'] > 1) {
							$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
						}
					}
					echo "<input type=\"hidden\" name=\"PageOffset\" VALUE=\"". $_POST['PageOffset'] ."\"/>";
					if ($ListPageMax >1) {
						echo "<p><div class=centre>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ': ';
						echo '<select name="PageOffset">';
						$ListPage=1;
						while($ListPage <= $ListPageMax) {
							if ($ListPage == $_POST['PageOffset']) {
								echo '<option VALUE=' . $ListPage . ' selected>' . $ListPage . '</option>';
							} else {
								echo '<option VALUE=' . $ListPage . '>' . $ListPage . '</option>';
							}
							$ListPage++;
						}
						echo '</select>
							<input type=submit name="Go" VALUE="' . _('Ir') . '">
							<input type=submit name="Previous" VALUE="' . _('Anterior') . '">
							<input type=submit name="Next" VALUE="' . _('Siguiente') . '">';
						echo '</div>';
					}
					$j = 1;
					$k = 0; //row counter to determine background colour
					$RowIndex = 0;
					//*****************************************************************
					//***********************FIN PAGINACION****************************
					//*****************************************************************
					echo '<table cellpadding=1 border=2 width=98%>';
					$TableHeader = '<tr>
							<th>' . _('Clave') . '</th>
							<th>' . _('Nombre Cliente') . '</th>
							<th>' . _('Nombre Sucursal') . '</th>
							<th>' . _('Ciudad') . '</th>';
					/*if ($_SESSION['VehicleXCustomer']==1){		
						$TableHeader=$TableHeader.'<th>' . _('Placas') . '</th>';
						$TableHeader=$TableHeader.'<th>' . _('Marca') . '</th>';
						$TableHeader=$TableHeader.'<th>' . _('Modelo') . '</th>';
					}*/
					
					$TableHeader=$TableHeader.'</tr>';
					echo $TableHeader;
					$numvehicle=0;
					$j = 1;
					$k = 0; //row counter to determine background colour
					$pasa=false;
					if ($ListCount >0) 
					{
						DB_data_seek($result_CustSelect, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
						//while ($myrow=DB_fetch_array($result_CustSelect)) {
						while (($myrow=DB_fetch_array($result_CustSelect)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
							if ($k==1){
								echo '<tr class="EvenTableRows">';
								$k=0;
							} else {
								echo '<tr class="OddTableRows">';
								$k=1;
							}
							
							/* Para que no se despliegue el mismo nombre del cliente
							   en sucursal, verifico que sean diferentes */
							$tempBranchName = "";
							if ($myrow['name'] != $myrow['brname']) {
								$tempBranchName = $myrow['brname'];
							}
							$blacklist = $myrow['blacklist'];
							//echo "blacklist:".$blacklist;
							/* Se utiliza el valor del boton s - s para pasar parametros del cliente
							   y sucursal a siguiente pantalla, ojo al modificar */
							if ($blacklist==1){
								if ($_SESSION['VehicleXCustomer']==1){	
								printf('<td class="peque">%s - %s</td>
									<td class="peque">%s</td>
									<td class="peque">%s</td>
									<td class="peque">%s<br>%s</td>
									
									</tr>',
									$myrow['debtorno'],
									$myrow['branchcode'],
									$myrow['name'],
									$tempBranchName,
									$myrow['ciudad'],
									$myrow['taxid']
									
									);
								}else{
									printf('<td class="peque">%s - %s</td>
									<td class="peque">%s</td>
									<td class="peque">%s</td>
									<td class="peque">%s<br>%s</td>
									</tr>',
									$myrow['debtorno'],
									$myrow['branchcode'],
									$myrow['name'],
									$tempBranchName,
									$myrow['ciudad'],
									$myrow['taxid']
									);
									
								}
								
							}else{
							if ($_SESSION['VehicleXCustomer']==1){
								if ($myrow['debtorno']!=$debtornoant or $branchcodeant!=$myrow['branchcode']){
								if ($numvehicle>0 and $debtornoant!='' and $entrada==1){
									
									//echo 'entra';
									echo '</table>';
									echo '</td>';
									echo '</tr>';
									$pasa=true;
									//echo "<tr class=centre height=1 ><td colspan=4 height=1 ><b><hr color =black ></b></td></tr>";	
								}
								
								printf('<td class="peque"><input style="font-size:9px;height:25px;width:140px;" tabindex='.number_format($j+5).' type=submit name="Select" value="%s - %s"</td>
									<td class="peque">%s</td>
									<td class="peque">%s</td>
									<td class="peque">%s<br>%s</td>
									</tr>',
									$myrow['debtorno'],
									$myrow['branchcode'],
									$myrow['name'],
									$tempBranchName,
									$myrow['ciudad'],
									$myrow['taxid']
									);
									$vehicleautover=0;
								
									
									if ($myrow['vehicleno']>0){
										
										//echo "<tr class=centre><td bgcolor=#E0ECF8 colspan=4 style='font-size:9px;' class=centre>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>".('Vehiculos x Cliente')."</b></td></tr>";	
										//echo "entra dos";
										$entrada=1;
										
										echo "<tr  ><td colspan=4>";
										echo '<table align=right bgcolor=#E0ECF8 border=1 width=95% cellpadding=0 cellspacing=0 >';
										echo"<tr>
											<th></th>
											
											<th>"._('Placa /Serie /No. Eco')." </th>
											<th>"._('Marca')." </th>
											<th>"._('Modelo')." </th>";
										echo "</tr>";	
										printf('<tr><td width=100px class="peque">
										       <input style="font-size:9px;height:25px;width:60px;" tabindex='.number_format($j+5).' type=submit name="Select" value="%s - %s|Auto:%s">
										       <input style="font-size:9px;height:25px;width:10px;" tabindex='.number_format($j+5).' type=hidden name="Selectx" value="%s - %s">
										       </td>
										
										<td class="peque">&nbsp;%s</td>
										<td class="peque">&nbsp;%s</td>
										<td class="peque">&nbsp;%s</td>
										</tr>',
										$myrow['debtorno'],
										$myrow['branchcode'],
										$myrow['vehicleno'],
										$myrow['debtorno'],
										$myrow['branchcode'],
										
										$myrow['plate'],
										$myrow['mark'],
										$myrow['model']
										);
									}else{
										$entrada=0;
									}
									
								}
								else{
									if ($myrow['vehicleno']>0){
										
										printf('<tr><td class="peque">
										       <input style="font-size:9px;height:25px;width:60px;" tabindex='.number_format($j+5).' type=submit name="Select" value="%s - %s|Auto:%s">
										       <input style="font-size:9px;height:25px;width:100px;" tabindex='.number_format($j+5).' type=hidden name="Selectx" value="%s - %s">
										       </td>
										
										<td class="peque">&nbsp;%s</td>
										<td class="peque">&nbsp;%s</td>
										<td class="peque">&nbsp;%s</td>
										</tr>',
										$myrow['debtorno'],
										$myrow['branchcode'],
										
										$myrow['debtorno'],
										$myrow['branchcode'],
										$myrow['vehicleno'],
										$myrow['plate'],
										$myrow['mark'],
										$myrow['model']
										);
									}
									
									
									
								}
							}else{
								printf('<td class="peque"><input style="font-size:9px;height:25px;width:140px;" tabindex='.number_format($j+5).' type=submit name="Select" value="%s - %s"</td>
									<td class="peque">%s</td>
									<td class="peque">%s</td>
									<td class="peque">%s<br>%s</td>
									
									</tr>',
									$myrow['debtorno'],
									$myrow['branchcode'],
									$myrow['name'],
									$tempBranchName,
									$myrow['ciudad'],
									$myrow['taxid']
									);
								
								
							}
							}
							$debtornoant=$myrow['debtorno'];
							$branchcodeant=$myrow['branchcode'];
							$j++;
							if ($j == 11 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
								$j=1;
							}
							$RowIndex++;
							$numvehicle=$numvehicle+1;
						//end of page full new headings if
						}
					}
					else
					{
						echo '<tr><td></td><td class="menu_group_item_button">';
						//***********************************************************
						//*********INICIO AGREGA FUNCION DE ALTA CLIENTES************
						//***********************************************************
						
						$sql = "SELECT  distinct F.functionid, F.submoduleid,F.title,F.url, F.categoryid,C.name,C.imagecategory,case when FxU.permiso is null then 1 else FxU.permiso end as permiso
							FROM sec_modules s,
							     sec_submodules sm,
							     www_users u,
							     sec_profilexuser PU,
							     sec_funxprofile FP,
							     sec_functions F left join sec_funxuser FxU on  FxU.functionid=F.functionid and FxU.userid='".$_SESSION['UserID']."',
							     sec_categories C
							WHERE s.moduleid=sm.moduleid and s.active=1
							      and FP.profileid=PU.profileid
							      and F.submoduleid=sm.submoduleid
							      and C.categoryid=F.categoryid
							      and u.userid=PU.userid and PU.userid='".$_SESSION['UserID']."'
							      and s.moduleid=2
							      and u.userid=PU.userid
							      and F.submoduleid=9
							      and F.functionid = 159
							      order by C.orderno, F.orderno, C.name,F.title
							      ";
							      //echo "sql:".$sql;
						$ReFuntion = DB_query($sql, $db);
						if (DB_num_rows($ReFuntion)>0 ) {
							
							echo '<a href="' . $rootpath . '/Customers.php?from=selectorderitems">' . _('Agregar Nuevo Cliente') . '</a><br>';
							
						}
						//***********************************************************
						//*********FIN AGREGA FUNCION DE ALTA CLIENTES************
						//***********************************************************
						echo '</td><td></td></tr>';
					}
					//end of while loop
					echo '</table>';
				}//end if results to show
				?>
			</td>
			<? /* FIN PARTE DERECHA PARA DESPLEGAR RESULTADOS DE LA BUSQUEDA DE CLIENTES */ ?>
			<? /**************************************************************************/ ?>
		</tr>
	</table>
<?php
/*********   FIN DE DESPLIEGUE DE PANTALLA DE BUSQUEDA DE CLIENTES *********************************/
/***************************************************************************************************/

} else { /* SI NO SE REQUIERE SELECCION DEL CLIENTE ENTOCES... */
	
/*************************************************************************************************/
/*************************************************************************************************/
/**** CLIENTE SELECCIONADO, COMIENZA AQUI LA SELECCION DE PRODUCTOS Y ORDEN DE VENTA *************/

	/* SI SE SELECCIONO EL BOTON DE CANCELAR ORDEN */
 	if (isset($_POST['CancelOrder'])) {
		$OK_to_delete=1;	//assume this in the first instance
		if($_SESSION['ExistingOrder']!=0) { //need to check that not already dispatched
			$sql = 'SELECT qtyinvoiced
					FROM salesorderdetails
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
				$SQL = 'DELETE FROM salesorderdetails WHERE salesorderdetails.orderno =' . $_SESSION['ExistingOrder'];
				$ErrMsg =_('El detalle de lineas de pedido no puede ser eliminado por que');
				$DelResult=DB_query($SQL,$db,$ErrMsg);
				$SQL = 'DELETE FROM salesorders WHERE salesorders.orderno=' . $_SESSION['ExistingOrder'];
				$ErrMsg = _('El encabezado del pedido no puede ser eliminado por que');
				$DelResult=DB_query($SQL,$db,$ErrMsg);
				$_SESSION['ExistingOrder']=0;
			}
			unset($_SESSION['Items'.$identifier]->LineItems);
			$_SESSION['Items'.$identifier]->ItemsOrdered=0;
			unset($_SESSION['Items'.$identifier]);
			$_SESSION['Items'.$identifier] = new cart;
			if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
				$_SESSION['RequireCustomerSelection'] = 1;
			} else {
				$_SESSION['RequireCustomerSelection'] = 0;
			}
			echo '<br><br>';
			prnMsg(_('El pedido de venta ha sido cancelada'),'success');
			include('includes/footer.inc');
			exit;
		}
	/* FIN  SELECCIONO EL BOTON DE CANCELAR ORDEN */
	} else { /* SI NOOO SE SELECCIONO EL BOTON DE CANCELAR ORDEN */ 
		
		/*
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Pedido') . '" alt="">' . ' ';
		if ($_SESSION['Items'.$identifier]->Quotation==1){
			echo _('Cotizacion') . ' ';
		} else {
			echo _('Pedido de Venta') . ' ';
		}
		
		if (isset($ordernumber) and $ordernumber<>'') {
			echo 'No. ' . $ordernumber;	
		}	
								      */
		
		
		//LIGA DE REGRESAR A BUSQUEDA DE CLIENTE
		/*************************************************************************************/
		/* TABLA DE LIGAS PARA MODIFICAR CLIENTE O REGRESAR A PANTALLA DE BUSQUEDA DE CLIENTE*/
		echo '<table border=0 width=100%>';
		echo '<tr><td>';
		echo '	<a href="' . $rootpath . '/SelectOrderItemsV4_0.php?ChangeCustomer=Yes&ModifyOrderNumber='.$_SESSION['ExistingOrder'].'&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'">
				<img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Cliente') . '" alt=""> Cambiar Cliente para este pedido</a>';
		echo '  <BR>';		
		echo '	<a href="' . $rootpath . '/Customers.php?from=selectorderitems&DebtorNo='.$_SESSION['Items'.$identifier]->DebtorNo .'&BranchCode='.$_SESSION['Items'.$identifier]->Branch. SID .'&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'&placa='.$placa.'&serie='.$serie.'&kilometraje='.$kilometraje.'&anticipo='.$anticipo.'&ModifyOrderNumber='.$_SESSION['ExistingOrder'] . '">
				<img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Cliente') . '" alt=""> Modificar Datos De Este Cliente </a>';
		echo '  <BR>';		
		echo '	<a href="' . $rootpath . '/CustomerBranches.php?from=selectorderitems&DebtorNo='.$_SESSION['Items'.$identifier]->DebtorNo .'&SelectedBranch='.$_SESSION['Items'.$identifier]->Branch. SID .'&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'&placa='.$placa.'&serie='.$serie.'&kilometraje='.$kilometraje.'&anticipo='.$anticipo.'&ModifyOrderNumber='.$_SESSION['ExistingOrder'] . '">
				<img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Cliente') . '" alt=""> Modificar Datos De Sucursal De Este Cliente </a>';
		echo '  <BR>';		
		/*echo '	<a href="' . $rootpath . '/VehicleXCostumer.php?frompage=selectorderitems&DebtorNo="' . $_SESSION['Items'.$identifier]->DebtorNo . '&BranchCode=' . $_SESSION['Items'.$identifier]->Branch ."&identifier=".$identifier.
		"&CurrAbrev=".$_SESSION['CurrAbrev']."&Tagref=".$_SESSION['Tagref']."&ModifyOrderNumber=".$_SESSION['ExistingOrder']. '">
				<img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Cliente') . '" alt=""> '._('Modificar Datos de Vehiculo').'</a>';
			   */
		echo '</td>
			<td width=33%>';
			
		echo '<img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Pedido') . '" alt="">' . '<font size=3><b>';
		if ($_SESSION['Items'.$identifier]->Quotation==1){
			echo _('Cotizacion') . ' ';
		} else {
			echo _('Pedido de Venta') . ' ';
		}
		
		if (isset($ordernumber) and $ordernumber<>'') {
			echo 'No. ' . $ordernumber;	
		}	
			
		echo '</b></font>
			</td><td width=33%> </td></tr>	
			</table>';
		/*************************************************************************************/
		
		
		/*************************************************************************************/
		
		$SQL = "SELECT 	d.debtorno,
			d.name
		FROM	debtorsmaster d
		WHERE	d.debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."'";
		
		$Result = DB_query($SQL,$db);
		$myrow2 = DB_fetch_array($Result);
		// extrae datos del automovil a facturar
		if ($_SESSION['SelectedVehicle']>0){
			$SQL = "SELECT 	*
			FROM	vehiclesbycostumer d
			WHERE	d.vehicleno = '".$_SESSION['SelectedVehicle']."'";
			$Result = DB_query($SQL,$db);
			$myrow3 = DB_fetch_array($Result);
		}
		/* TABLA DE DATOS DEL CLIENTE EN ENCABEZADO                                          */
		echo '<table border=1 width=100%>';
		echo '<tr><td width=30%>';
			echo '<table border="0" CELLPADDING=0 CELLSPACING=0>';
				echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos Del Cliente') . '</td></tr>';
				echo '<tr><td>' . _('Cliente') . ':</td><td style=font-weight:normal;>' .$myrow2['debtorno'].' - '. $_SESSION['Items'.$identifier]->DeliverTo . '</td></tr>';
				echo '<tr><td>' . _('Precio Lista') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->SalesTypeName . '</td></tr>';
				echo '<tr><td>' . _('Terminos') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->PaymentTerms . '</td></tr>';
				echo '<tr><td>' . _('Credito Disp') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->CreditAvailable . '</td></tr>';
			echo '</table>';
		echo '</td><td  width=30% valign=top>';
			echo '<table border="0" width=100% CELLPADDING=0 CELLSPACING=0 >';
			echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos De la Oficina del Cliente') . '</td></tr>';
			echo '<tr><td>' . _('RFC') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->CostumerRFC ._('&nbsp;'). '</td></tr>';
			echo '<tr><td>' . _('Direccion') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->DelAdd1.' '.$_SESSION['Items'.$identifier]->DelAdd2 . '</td></tr>';
			echo '<tr><td>' . _('Ciudad') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->DelAdd3 ._('&nbsp;'). '</td></tr>';
			echo '<tr><td>' . _('Contacto') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->CostumerContact . _('&nbsp;').'</td></tr>';
			if ($_SESSION['SelectedVehicle']>0){
				echo '<tr><td nowrap  class="EvenTableRows" colspan=2>' . _('Datos Vehiculo') . '</td><tr>';
				echo '<tr><td nowrap style=font-weight:normal; ><b>' . _('No. Vehiculo') . ': <a TARGET="_blank" href=VehicleInquery.php?SelectedVehicle='.$_SESSION['SelectedVehicle'].'&DebtorNo='.$myrow2['debtorno'].'></b>'.$_SESSION['SelectedVehicle'].'</a></td>';
				echo '<td nowrap style=font-weight:normal;><b>' . _('Placas') . ':</b> '.$myrow3['plate'].'/'.$myrow3['serie'].'/'.$myrow3['numeco'].'</td><tr>';
			}
			
			echo '</table>';
		echo '</td>';
		
		if (isset($_SESSION['Tagref']) and strlen($_SESSION['Tagref'])>0){
			
			$SQLTags="SELECT *
				  FROM tags
				  WHERE tagref=".$_SESSION['Tagref'];
			$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
			$GetTAGS = DB_query($SQLTags,$db,$ErrMsg);
			if (DB_num_rows($GetTAGS)==1) {	       
				$myrowTAG = DB_fetch_array($GetTAGS);
				$_SESSION['TagName']=strtoupper($myrowTAG['tagdescription']);
			}
			$SQLCurrency="SELECT c.rate,c.currency 
					FROM currencies c
					WHERE c.currabrev='".$_SESSION['CurrAbrev']."'";
			$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
			$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
			if (DB_num_rows($GetCurrency)==1) {	       
				$myrowCurrency = DB_fetch_array($GetCurrency);
				$_SESSION['CurrencyName']=strtoupper($myrowCurrency['currency']);
			}
			
			$SQLCurrency="SELECT *
					FROM salestypes c
					WHERE c.typeabbrev='".$_SESSION['SalesType']."'";
			$ErrMsg =  _('No se pudo recuperar el valor de la moneda ');
			$GetCurrency = DB_query($SQLCurrency,$db,$ErrMsg);
			if (DB_num_rows($GetCurrency)==1) {	       
				$myrowCurrency = DB_fetch_array($GetCurrency);
				$_SESSION['SalesName']=strtoupper($myrowCurrency['sales_type']);
			}

			
		}
		
		if (isset($_SESSION['Tagref']) and strlen($_SESSION['Tagref'])>0){
		
		echo '<td  width=30% valign=top>';
			echo '<table border="0" width=100% CELLPADDING=0 CELLSPACING=0 >';
			echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos De Facturacion') . '</td></tr>';
			echo '<tr><td>' . _('Unidad Negocio') . ':</td><td style=font-weight:normal;>' . $_SESSION['TagName'] ._('&nbsp;'). '</td></tr>';
			echo '<tr><td>' . _('Moneda') . ':</td><td style=font-weight:normal;>' . $_SESSION['CurrencyName'] . '</td></tr>';
			echo '<tr><td>' . _('Lista Precio') . ':</td><td style=font-weight:normal;>' . $_SESSION['SalesName'] . '</td></tr>';
			echo '</table>';
		echo '</td>';
		}
		echo '</tr>';
		echo '</table>';
		
		/*************************************************************************************/
		
	}
	
	/* *************************ORDEN DE VENTA***************************************/
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
					
					//prnMsg(_('PASO POR AQUI '),'warn');
					
					if (isset($_POST['Discount_' . $OrderLine->LineNumber]))
						$DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
					else
						$DiscountPercentage = 0;
						
					if (isset($_POST['Discount_' . $OrderLine->LineNumber]))
						$DiscountPercentage1 = $_POST['Discount1_' . $OrderLine->LineNumber];
					else
						$DiscountPercentage1 = 0;
						
					if (isset($_POST['Discount_' . $OrderLine->LineNumber]))
						$DiscountPercentage2 = $_POST['Discount2_' . $OrderLine->LineNumber];
					else
						$DiscountPercentage2 = 0;
						
					// En caso de que quieran descontar el iva al precio
					$checkwithtax=$_POST['Itemwithtax_' . $OrderLine->LineNumber];
					if ($checkwithtax==true){
						
						$Price=$Price * (1-($DiscountPercentage/100));
						//echo "<br>desc1: ".$Price;
						$Price=$Price * (1-($DiscountPercentage1/100));
						//echo "<br>desc2: ".$Price;
						$Price=$Price * (1-($DiscountPercentage2/100));
						//echo "<br>desc3: ".$Price;
						foreach ($OrderLine->Taxes AS $Tax) {
							if (empty($TaxTotals[$Tax->TaxAuthID])) {
								//$TaxTotals[$Tax->TaxAuthID]=0;
							}
							if ($Tax->TaxOnTax ==1){
								
								$Price = ($Price /(1+$Tax->TaxRate));
								
								//$Price = $Price * (1-$DiscountPercentage);
								//$Price = $Price * (1-$DiscountPercentage1);
								//$Price = $Price * (1-$DiscountPercentage2);
							} else {
								$Price = ($Price /(1+$Tax->TaxRate));
								//$Price = $Price * (1-$DiscountPercentage);
								//$Price = $Price * (1-$DiscountPercentage1);
								//$Price = $Price * (1-$DiscountPercentage2);
							}
						}
						//echo "<br>iva: ".$Price;
						$DiscountPercentage=0;
						$DiscountPercentage1=0;
						$DiscountPercentage2=0;
					}
					
					$servicestatus=$_POST['Itemservicestatus_' . $OrderLine->LineNumber];
					if ($servicestatus==TRUE){
						$servicestatus=1;
					}else{
						$servicestatus=0;
					}
					$warranty=$_POST['Itemwarranty_' . $OrderLine->LineNumber];
					if ($warranty==TRUE){
						$warranty=1;
					}else{
						$warranty=0;
					}
					
					$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
					if (isset($Narrative)) {
						$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
					} else {
						$Narrative = $OrderLine->Narrative;
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
								$DiscountPercentage=0;

							}
						}else{
							prnMsg(_('El producto no se puede actualizar por que el descuento 1 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
							$OrderLine->DiscountPercent = 0;
							$DiscountPercentage=0;
						}
					}
					
					if(($DiscountPercentage1/100)>$_SESSION['discount2']){
						if ($_SESSION['ExistingOrder']>0){
							if ($DiscountPercentageant2<($DiscountPercentage1/100)){
								prnMsg(_('El producto no se puede actualizar por que el descuento 2 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
								$DiscountPercentage1=0;
								$OrderLine->DiscountPercent1 = 0;
							}
						}else{
							prnMsg(_('El producto no se puede actualizar por que el descuento 2 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
							$DiscountPercentage1=0;
							$OrderLine->DiscountPercent1 = 0;
						}
					}
					
					if(($DiscountPercentage2/100)>$_SESSION['discount3']){
						if ($_SESSION['ExistingOrder']>0){
							if ($DiscountPercentageant3<($DiscountPercentage2/100)){
								prnMsg(_('El producto no se puede actualizar por que el descuento 3 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
								$OrderLine->DiscountPercent2 = 0;
								$DiscountPercentage2=0;
							}
						}else{
							prnMsg(_('El producto no se puede actualizar por que el descuento 3 que intenta ingresar es menor al que tiene autorizado: '.$_SESSION['discount1']*100),'warn');
							$OrderLine->DiscountPercent2 = 0;
							$DiscountPercentage2=0;
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
					} elseif ($OrderLine->Quantity !=$Quantity OR $OrderLine->Price != $Price OR ABS($OrderLine->DiscountPercent -$DiscountPercentage/100) >0.001 OR $OrderLine->Narrative != $Narrative OR $OrderLine->ItemDue != $ItemDue OR $OrderLine->POLine != $POLine OR ABS($OrderLine->DiscountPercent1 -$DiscountPercentage1/100) >0.001 OR ABS($OrderLine->DiscountPercent2 -$DiscountPercentage2/100) >0.001
						  or $servicestatus!=$OrderLine->servicestatus ) {
						
						if ($debuggaz==1)
							echo '<BR>Actualiza:'.$OrderLine->LineNumber;
							
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
												 $warranty,
												 $servicestatus
											);
						$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
						
						$sql="select stockmaster.taxcatidret from stockmaster where stockid='".$OrderLine->StockID."'";
						 $ErrMsg = _('El Codigo') . ' ' . $OrderLine->StockID . ' ' . _('no se ha encontrado por que');
						$DbgMsg = _('El SQL utilizado para recuperar los detalles de los precios');
						$result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
						$myItemRow = DB_fetch_array($result1);
						if ($myItemRow['taxcatidret']>0){
							$_SESSION['Items'.$identifier]->GetTaxesRet($OrderLine->LineNumber);
						}
					}
				} //page not called from itself - POST variables not set
				
			} /* FIN DE LOOP PARA CADA LINEA DE PRODUCTO EN LA ORDEN DE VENTA */
		} /* FIN IF SI EXISTEN YA PRODUCTOS */
	} /* FIN DE ACTUALIZACION DE PRODUCTOS O ALTA DE LINEA DE LA ORDEN */
	    
	/* SI SE SELECCIONO LA OPCION DE CONFIRMAR EL PEDIDO, ENTRA AQUI Y REDIRECCIONA A PAGINA DE DeliveryDetailsV2_0.php */
	if (isset($_POST['DeliveryDetails'])){
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/DeliveryDetailsV2_0.php?' . SID .'&identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'&quotation='.$_SESSION['quotation'].'">';
		prnMsg(_('Deberias de ser redireccionado automaticamente a la pagina de confirmacion') . '. ' . _('si esto no sucede') . ' (' . _('si el explorador no soporta META REFRESS') . ') ' .
           '<a href="' . $rootpath . '/DeliveryDetailsV2_0.php?' . SID .'&identifier='.$identifier . '">' . _('haz click aqui') . '</a> ' . _('para continuar'), 'info');
	   	exit;
	}
	/*******************************************************************************************************/

	// INICIO DE AGREGAR A CLASE LOS PRODUCTOS POR SUCURSAL
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'&identifier='.$identifier . '"& name="SelectParts" method=post>';
	echo '<fieldset>';
	echo '<div class="centre"><hr></div>';
	if (isset($_POST['order_items']) or isset($_POST['QuickEntry']) or isset($_POST['QuickEntryOrder'])){
		
	}
	if (isset($_SESSION['Items'.$identifier]->LineCounter)){
		$lineax=($_SESSION['Items'.$identifier]->LineCounter-1);
	}
	
	if (isset($NewItem_array) && isset($_POST['order_items'])) {
		$xbill=0;
		foreach($NewItem_array as $NewItem => $NewItemQty) {
			
			if($NewItemQty > 0){
				$lineax=$lineax+1;
				$NewItemS=strstr($NewItem,"|");
				$NewItemS=str_replace("|","",$NewItemS);
				
				$NewItem=substr($NewItem,0,strpos($NewItem,"|"));
				//if(!isset($_GET['agregarproducto'])){
					if ($_SESSION['MultipleBilling']==1){
						if (isset($_GET['agregarproducto'])){
							/*if(!isset($_SESSION['loccode'])){
								$_SESSION['loccode']=$NewItemS;
							}else{
								$NewItemS=$_SESSION['loccode'];
							}*/
							$NewItemS=$NewItemS;
						}else{
							$NewItemS=$_POST['stockbill'.$NewItemS];
							if(!isset($_SESSION['loccode'])){
								$_SESSION['loccode']=$NewItemS;
							}elseif(!isset($_GET['agregarproducto'])){
								$NewItemS=$_SESSION['loccode'];
							}
						}
					}
				//}
				
				
				//echo 'almacen:'.$almacenbill;
				$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";
				//echo $sql;
					
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
							include('includes/SelectOrderItemsProducts_IntoCartV2.inc');
				}
					} else { /*Its not a kit set item*/
						
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = $lineax;
						$loccalmacen=$NewItemS;
						include('includes/SelectOrderItemsProducts_IntoCartV2.inc');
					}
				} /* end of if its a new item */
				    
			} /*end of if its a new item */
			   
		}//Fin de For
		
		
		$_POST['PartSearch']='Yes';
		
	}//Fin de validacion Array

// ENTRADA RAPIDA DE PRODUCTOS
	/*Process Quick Entry */
	if (isset($_POST['order_items']) or isset($_POST['QuickEntry']) or isset($_POST['QuickEntryOrder']) ){
		//echo "entra".$_POST['QuickEntryOrder'];
		
		// if enter is pressed on the quick entry screen, the default button may be Recalculate
		$Discount = 0;
		$i=1;
		
		//$lineax=0;
		//echo "valor:".$lineax;
		while ($i<=$_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i]!='') {
			$QuickEntryCode = 'part_' . $i;
			$QuickEntryQty = 'qty_' . $i;
			$QuickEntryLoc ='Stock_' . $i;
			$QuickEntryPrice ='price_' . $i;
			$QuickEntryDesc1 ='desc1_' . $i;
			$QuickEntryDesc2 ='desc2_' . $i;
			$QuickEntryDesc3 ='desc3_' . $i;
			
			//echo 'alamacen 1 :'.$QuickEntryLoc;
			
			$i++;
			
			$lineax=$lineax+1;
			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = strtoupper($_POST[$QuickEntryCode]);
			}
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = $_POST[$QuickEntryQty];
			}
			
			if (isset($_POST[$QuickEntryPrice])) {
				$NewItemPrice = $_POST[$QuickEntryPrice];
			}
			
			if (isset($_POST[$QuickEntryDesc1])) {
				$NewItemDesc1 = $_POST[$QuickEntryDesc1];
			}
			
			if (isset($_POST[$QuickEntryDesc2])) {
				$NewItemDesc2 = $_POST[$QuickEntryDesc2];
			}
			
			if (isset($_POST[$QuickEntryDesc3])) {
				$NewItemDesc3 = $_POST[$QuickEntryDesc3];
			}
			
			if (isset($_POST[$QuickEntryLoc])) {
				$loccalmacen= $_POST[$QuickEntryLoc];
			}
			
			//$loccalmacen=strtoupper($_POST[$QuickEntryCode]);
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
						include('includes/SelectOrderItemsProducts_IntoCartV2.inc');
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else {
				
					// ENVIO INFORMACION DEL ALMACEN
					// si categoria <> servicios modifico consulta
					/*$SQL="  SELECT   stockcategory.stocktype as tipo
						FROM stockmaster,stockcategory
						WHERE  stockmaster.categoryid=stockcategory.categoryid
							AND stockmaster.stockid='".$NewItem."'";
					$result_type = DB_query($SQL,$db);
					while ($myrow_type = DB_fetch_array($result_type)) {
							$typepr=$myrow_type['tipo'];
					}
					
					$SQL="SELECT l.loccode,quantity
					      FROM areas a, tags t, locations l ,locstock s
					      WHERE t.areacode=a.areacode
						    AND s.loccode=l.loccode
						    AND l.tagref=t.tagref
						    AND t.tagref='".$_SESSION['Tagref']."'
						    AND s.stockid='".$NewItem."'";
						if ($typepr!="L"){
							    $SQL=$SQL." AND l.temploc=0";
						}   
					$result_tag = DB_query($SQL,$db);
					while ($myrow_locations = DB_fetch_array($result_tag)) {
						$loccalmacen=$myrow_locations['loccode'];
						$loccalmacenxuni=$myrow_locations['loccode'];
						$cantidadxl=$myrow_locations['quantity'];
						if ($cantidadxl>0){
							break;
						}
						
					}
					
					
					if ($cantidadxl<=0){
						$SQL="SELECT l.loccode,quantity
						      FROM areas a, tags t, locations l ,locstock s
						      WHERE t.areacode=a.areacode
							    AND s.loccode=l.loccode
							    AND l.tagref=t.tagref
							    AND t.tagref='".$_SESSION['DefaultUnidad']."'
							    AND s.stockid='".$NewItem."'";
						if ($typepr!="L"){
							    $SQL=$SQL." AND l.temploc=0";
						}
						$result_tag = DB_query($SQL,$db);
						while ($myrow_locations = DB_fetch_array($result_tag)) {
							$loccalmacen=$myrow_locations['loccode'];
							$cantidadxl=$myrow_locations['quantity'];
							if ($cantidadxl>0){
								break;
							}
						}
					}
					if ($cantidadxl<=0){
						$loccalmacen=$loccalmacenxuni;
					}*/
					
					
					//echo 'almacen:'.$loccalmacen;
					
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = $lineax ;
					include('includes/SelectOrderItemsProducts_IntoCartV2.inc');
					//$lineax = $lineax + 1;
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
							  
	echo '
		<table width="90%" cellpadding="1" colspan="7" border="1">
		<tr>
			<td colspan=14>
				<div class="centre"><b>Detalle del Pedido</b></div>
			</td>
		</tr>
		<tr bgcolor=#800000>';
		if($_SESSION['Items'.$identifier]->DefaultPOLine == 1){
			echo '<th>' . _('PO Line') . '</th>';
		}
		//echo '<div class="page_help_text">' . _('Quantity (required) - Enter the number of units ordered.  Price (required) - Enter the unit price.  Discount (optional) - Enter a percentage discount.  GP% (optional) - Enter a percentage Gross Profit (GP) to add to the unit cost.  Due Date (optional) - Enter a date for delivery.') . '</div><br>'
	
	echo   '<th>' . _('PROMOS') . '</th>';
	if($_SESSION['ExistingOrder']!=0 and Havepermission($_SESSION['UserID'],273, $db)==1){
		echo'<th>' . _('O.C.') . '</th>';
	}
	echo'<th>' . _('Codigo') . '</th>
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
			      <th>' . _('IVA') . '</th>
			      <th>' . _('Fin') . '</th>
			      ';
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
	//echo '<th>' . _('Garan') . '</th>';
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
				
				//echo 'XX:'.Count($OrderLine->Taxes).'<br>';
				
				foreach ($OrderLine->Taxes AS $Tax) {
					
					//echo 'Tax:'.$Tax->TaxRate.'<br>';
					
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
				
				/* POR ALGUNA RAZON EL PRODUCTO DE SERVIBILLETE NO TOMA EL IVA AL AGREGARSE, ESTO LO CORRIGE */
				if ($TaxLineTotal == 0) {
					$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
					
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
				
				$TaxTotal += $TaxLineTotal;
				$DisplayLineTotal = number_format($LineTotal,2);
				$DisplayDiscount = number_format(($OrderLine->DiscountPercent * 100),2);
				$DisplayDiscount1 =number_format(($OrderLine->DiscountPercent1 * 100),2);
				$DisplayDiscount2 = number_format(($OrderLine->DiscountPercent2 * 100),2);
				$QtyOrdered = $OrderLine->Quantity;
				$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;
				
				if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M') and $OrderLine->RedInvoice==0) {
					$RowStarter = '<tr bgcolor="#EEAABB">';
				} elseif ($k==1){
					$RowStarter = '<tr class="OddTableRows">';
					$k=0;
				} else {
					$RowStarter = '<tr class="EvenTableRows">';
					$k=1;
				}
				
				echo $RowStarter;
				
				
				/*******************************************************************/
				/***************** INICIO DE BOTONES PROMOCIONES *******************/
				echo '<td>';
				echo '<table><tr>';
					
					/* MUESTRA BOTONES RAPIDOS DE PROMOCIONES */
					$SQL='SELECT *
						FROM stockpromosales
						ORDER BY displayorder';
				    
					$result2 = DB_query($SQL,$db);
					
					$i = 1;
					$j = 1;
					while ($myrow2 = DB_fetch_array($result2)) {
						//$i = $i + 1;
						
						echo '<td>';
						//echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier . '" method=post name="quickentry_'.$i.'">';
						/*
						echo '<input type="hidden" name="PartSearch" value="Yes">';
						echo '<input type="hidden" name="CurrAbrev" value="'.$_SESSION['CurrAbrev'].'">';
						echo '<input type="hidden" name="Tagref" value="'.$_SESSION['Tagref'] .'">';
						
						echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
						echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
						echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
						echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';    
						*/
						
						$compliga = "";
						if (strlen($myrow2['stockid'])>2) {
							$compliga = $compliga.'&part_' . $j . '='.$myrow2['stockid'].'';
							//echo '<input type="hidden" name="part_' . $j . '" value="'.$myrow2['stockid'].'">';
						} else {
							$compliga = $compliga.'&part_' . $j . '='.$OrderLine->StockID.'';
							//echo '<input type="hidden" name="part_' . $j . '" value="'.$OrderLine->StockID.'">';	
						}
						
						if ($myrow2['fixedquantity']>0) {
							$compliga = $compliga.'&qty_' . $j . '='.$myrow2['fixedquantity'].'';
							//echo '<input type="hidden" name="qty_' . $j . '"  value="'.$myrow2['fixedquantity'].'">';	
						} else {
							$compliga = $compliga.'&qty_' . $j . '='.$OrderLine->Quantity*$myrow2['quantityfactor'].'';
							//echo '<input type="hidden" name="qty_' . $j . '"  value="'.$OrderLine->Quantity*$myrow2['quantityfactor'].'">';
						}
						
						if ($myrow2['fixedprice']>0) {
							$compliga = $compliga.'&price_' . $j . '='.$myrow2['fixedprice'].'';
							//echo '<input type="hidden" name="price_' . $j . '"  value="'.$myrow2['fixedprice'].'">';	
						} else {
							$compliga = $compliga.'&price_' . $j . '='.$OrderLine->Price*$myrow2['pricefactor'].'';
							//echo '<input type="hidden" name="price_' . $j . '"  value="'.$OrderLine->Price*$myrow2['pricefactor'].'">';
						}
						
						$compliga = $compliga.'&desc1_' . $j . '='.($myrow2['desc1']/100).'';
						//echo '<input type="hidden" name="desc1_' . $j . '"  value="'.($myrow2['desc1']/100).'">';
						$compliga = $compliga.'&desc2_' . $j . '='.($myrow2['desc2']/100).'';
						//echo '<input type="hidden" name="desc2_' . $j . '"  value="'.($myrow2['desc2']/100).'">';
						$compliga = $compliga.'&desc3_' . $j . '='.($myrow2['desc3']/100).'';
						//echo '<input type="hidden" name="desc3_' . $j . '"  value="'.($myrow2['desc3']/100).'">';
						
						$compliga = $compliga.'&Stock_' . $j . '='.$OrderLine->AlmacenStock.'';
						//echo '<input type="hidden" name="Stock_'. $j . '"  value="'.$OrderLine->AlmacenStock.'">';
						$compliga = $compliga.'&QuickEntry='.$OrderLine->StockID.'';
						//echo '<input type="hidden" name="QuickEntry" value="'.$OrderLine->StockID.'">';
						
						$compliga = $compliga.'&lineaxs=1';
						//echo '<input type=hidden name="lineaxs" value=1>';
						
						if ($myrow2['image']!='') {
							
							echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier . '&PartSearch=Yes&CurrAbrev='.$_SESSION['CurrAbrev'].'
								&Tagref='.$_SESSION['Tagref'].'&StockCat='.$_POST['StockCat'].'&StockLock='.$_POST['StockLock'].'
								&Keywords='.$_POST['Keywords'].'&StockCode='.$_POST['StockCode'].''.$compliga.'">';
							echo '<img  width=25 height=25 src="'.$rootpath.'/'.$myrow2['image'].'" title="' . $myrow2['promoname'] . '" alt="'.$myrow2['promoname'].'">';
							echo '</a>';
							//onclick="submit();"
							
						} else {
							//echo '<input type="submit" name="QuickEntryButton" value="'.$myrow2['promoname'].'">';
						}
						
						echo '</td>';
						//echo '</form>';
					
					}
					
				echo '</tr></table>';	
				echo '</td>';
				if($OrderLine->RedInvoice==0){
					$valordispo=$OrderLine->QOHatLoc;
				}else{
					$valordispo=" - ";
				}
				//echo $valordispo;
				if ($OrderLine->Quantity<$valordispo or $valordispo==" - "){
					$checkwithcompra="";
				}else{
					$checkwithcompra="checked";
				}
				// CHECK PARA ORDENES DE COMPRA AUTOMATICAS
				if($_SESSION['ExistingOrder']!=0 and Havepermission($_SESSION['UserID'],273, $db)==1){
					echo '<td>
						<input type="checkbox" name="itemordencompra_' . $OrderLine->LineNumber .'" '.$checkwithcompra .'>
					      </td>';
				}
				/***************** INICIO DE BOTONES PROMOCIONES *******************/
				/*******************************************************************/
				
				if($_SESSION['Items'.$identifier]->DefaultPOLine ==1){ //show the input field only if required
					echo '<td><input tabindex=1 type=text name="POLine_' . $OrderLine->LineNumber . '" size=20 maxlength=20 value=' . $OrderLine->POLine . '></td>';
				} else {
					echo '<input type="hidden" name="POLine_' .	 $OrderLine->LineNumber . '" value="">';
				}
				
				echo '<td  style="font-face:verdana;font-size:10px"><a target="_blank" href="' . $rootpath . '/StockStatus.php?' . SID .'&identifier='.$identifier . '&StockID=' . $OrderLine->StockID . '&DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '">' . $OrderLine->StockID . '</a></td>
					<td nowrap style="font-face:verdana;font-size:10px">' . $OrderLine->ItemDescription ;
				echo '<input type="hidden" class="number" name="' . $OrderLine->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=4 size=4 value="' . $Tax->TaxRate*100 . '">';
				echo '</td>';
				echo '<td nowrap style="font-face:verdana;font-size:10px"  >' . substr($OrderLine->AlmacenStockName,0,15) . '</td>';	
				echo '<td class="number"  ><input class="number" onKeyPress="return restrictToNumbers(this, event)" tabindex=2 type=tect name="Quantity_' . $OrderLine->LineNumber . '" size=9 maxlength=7 value=' . $OrderLine->Quantity . '>';
				if ($QtyRemain != $QtyOrdered){
					echo '<br>'.$OrderLine->QtyInv.' de '.$OrderLine->Quantity.' facturado';
				}
				
				
				echo '</td>
				<td class="number">' . $valordispo. '</td>
				<td>' . $OrderLine->Units . '</td>';
				
				$checkwithtax='';
				if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
					//echo $OrderLine->disabledprice;
					$permisoprecio=Havepermission($_SESSION['UserID'],1012, $db);
					if ($OrderLine->disabledprice==1 or $permisoprecio==1){
						echo '<td nowrap>
							<input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Price_' . $OrderLine->LineNumber . '" size=9 maxlength=16 value=' . $OrderLine->Price . '>
							<input type="checkbox" name="Itemwithtax_' . $OrderLine->LineNumber .'" '.$checkwithtax .'>
						</td>';
					}else{
						echo '<td nowrap>
							<input  type=hidden name="Price_' . $OrderLine->LineNumber . '" size=9 maxlength=16 value=' . $OrderLine->Price . '>
							<input  type=text disabled name="PriceNM_' . $OrderLine->LineNumber . '" size=9 maxlength=16 value=' . $OrderLine->Price . '>
							<input type="checkbox" name="Itemwithtax_' . $OrderLine->LineNumber .'" '.$checkwithtax .'>
						      </td>';	
					}
					
					      echo'<td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount_' . $OrderLine->LineNumber . '" size=2 maxlength=3 value=' . ($OrderLine->DiscountPercent * 100) . '>%</td>
					      <td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount1_' . $OrderLine->LineNumber . '" size=2 maxlength=3 value=' . ($OrderLine->DiscountPercent1 * 100) . '>%</td>
					      <td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount2_' . $OrderLine->LineNumber . '" size=2 maxlength=3 value=' . ($OrderLine->DiscountPercent2 * 100) . '>%
					      <input type="hidden" class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="GPPercent_' . $OrderLine->LineNumber . '" size=5 maxlength=4 value=' . $GPPercent . '>';
					echo '</td>';
					echo '<td  class="number" nowrap>
					<input type="hidden" class="number" name="' . $OrderLine->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=2 size=2 value="' . $Tax->TaxRate*100 . '"> 
					<input type="text" class="number" disabled name="ver" maxlength=2 size=2 value="' . $Tax->TaxRate*100 . '"> %';
				} else {
					echo '<td align=right nowrap>' . $OrderLine->Price . '';
					echo '<input type=hidden name="Price_' . $OrderLine->LineNumber . '" value=' . $OrderLine->Price . '>
					 <input type="checkbox" name="Itemwithtax_' . $OrderLine->LineNumber .'" '. $checkwithtax .'>
					</td>';
				}
				if ($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)){
					$RemTxt = _('Borra Resto');
				} else {
					$RemTxt = _('Borrar');
				}
				$servicestatus=$_POST['Itemservicestatus_' . $OrderLine->LineNumber];
				
				if ($OrderLine->servicestatus==0){
					$servicestatus="";
				}else{
					$servicestatus="checked";
				}
				$i=0; // initialise the number of taxes iterated through
				$TaxLineTotal =0; //initialise tax total for the line
				echo '</td>';
				echo '<td class=centre> <input type="checkbox" name="Itemservicestatus_' . $OrderLine->LineNumber .'" '. $servicestatus .'></td>';
				echo'<td class=number>' . $DisplayLineTotal . '</td>';
				$LineDueDate = $OrderLine->ItemDue;
				if (!Is_Date($OrderLine->ItemDue)){
					$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
					$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
				}
				
				echo '<td nowrap><input type=hidden class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ItemDue_' . $OrderLine->LineNumber . '" size=10 maxlength=10 value=' . $LineDueDate . '>';
				echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . SID .
					'&identifier='.$identifier . '&Delete=' . $OrderLine->LineNumber . '&lineaxs='.$_SESSION['Items'.$identifier]->LineCounter. '&ModifyOrderNumber='.$ordernumber.'" onclick="return confirm(\'' .
					_('¿Esta seguro de quitar este producto del pedido?') . '\');">' . $RemTxt . '</a></td>';
				echo '</tr>';
				
				
				
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
					echo '<tr><td>' . _('Texto') . ':</td><td colspan=5><textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="45" rows="2">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea></td></tr>';
				} else {
					echo '<input type=hidden name="Narrative" value="">';
				}
				
				$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
				$_SESSION['Items'.$identifier]->totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
				$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;
				//echo "ordenadas:".$OrderLine->LineCounter;
			} /*FIN DE IF DE ELIMINADOS */
		} /* end of loop around items */
	    
		$DisplayTotal = number_format($_SESSION['Items'.$identifier]->total,2);
		$ColSpanNumber = 13;
		$totalcuenta=$_SESSION['Items'.$identifier]->total;
		$totalcuenta=$totalcuenta + $TaxTotal;
		$totallineas=($_SESSION['Items'.$identifier]->LineCounter);
		//echo $totallineas;
	echo'<tr><td class=number colspan="'.$ColSpanNumber.'"><b>' . _('SUBTOTAL') . '</b></td>
		<td class=number>' . $DisplayTotal . '</td></tr>';
	echo '<tr><td  class=number colspan="'.$ColSpanNumber.'"><b>' . _('IVA') . '</b></td>
		<td class=number>' . number_format($TaxTotal,2)  . '</td></tr>';
	echo '<tr><td  class=number colspan="'.$ColSpanNumber.'"><b>' . _('TOTAL') . '</b></td>
		<td class=number>' . number_format($totalcuenta,2)  . '</td></tr>';
	echo '</table>';
		$DisplayVolume = number_format($_SESSION['Items'.$identifier]->totalVolume,2);
		$DisplayWeight = number_format($_SESSION['Items'.$identifier]->totalWeight,2);
		
	
		
	echo '<br><div class="centre">
		<input type="submit" name="Recalculate" style="font-size:12px;height:25px;width:165px;font-weight: bold" value="' . _('Recalcular') . '">';
	if($_SESSION['ExistingOrder']!=0 and Havepermission($_SESSION['UserID'],273, $db)==1){
		echo '<input type="submit" name="AutomaticCompra" style="font-size:12px;height:25px;width:165px;font-weight: bold" value="' . _('Generar Orden Compra') . '">';
	}
	echo '<input type="submit" name="DeliveryDetails" style="font-size:12px;height:25px;width:165px;font-weight: bold" value="' . _('CONFIRMAR PEDIDO') . '">';
	
	
	echo '</div><hr>';
	
	} # end of if lines
	
	
	// CAMPOS DE TAG Y MONEDA PARA REALIZAAR LA FACTURACION
	echo "<table border='0' width='100%'>";
	//echo '</form>';
	if ($_SESSION['CurrAbrev']==''){
		//echo '<form action="' . $_SERVER['PHP_SELF'] . '?identifier='.$identifier . SID . '" name="SelectParts" method=post>';
		
		echo '<tr>';
		if ($_SESSION['Tagref']==''){
			echo '<td><b>' . _('Unidad de Negocio') . ':</b>&nbsp;&nbsp; ';
			// consulta las unidades de negocio
			
			$SQL=" SELECT tags.*
			       FROM tags , sec_unegsxuser
			       WHERE  tags.tagref=sec_unegsxuser.tagref
			       AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
			       ORDER BY tags.tagdescription";
			       
			//echo "sql:".$SQL;
			$resultunitB = DB_query($SQL,$db);
			if (DB_num_rows($resultunitB)==1){
				$myrowlegal=DB_fetch_array($resultunitB);
				$tagref = $myrowlegal['tagref'];
				echo '<input type="hidden" name="Tagref" value="'.$tagref.'">';
				echo "<span style='font-size:8pt'>";
				echo $myrowlegal['tagdescription'];
				echo "</span>";
			}else {
				echo "<select name='Tagref' style='font-size:8pt'>";
				while ($myrowlegal = DB_fetch_array($resultunitB)) {
					if ($_SESSION['Tagref']==$myrowlegal['tagref']){
						echo '<option selected value=' . $myrowlegal['tagref'] . '>' . $myrowlegal['tagdescription'].'</option>';
					} else {
						echo '<option value='. $myrowlegal['tagref'] . '>' . $myrowlegal['tagdescription'].'</option>';
					}
				}
				echo '</select>';
			}
			
			echo '<td> <b>' . _('Moneda De Docto') . ':</b>&nbsp;&nbsp; ';
			// consulta las unidades de negocio
			$SQL=" SELECT *
			       FROM currencies
				ORDER BY rate desc";
			$resultunitB = DB_query($SQL,$db);
			if (DB_num_rows($resultunitB)==1){
				$myrowCurrency=DB_fetch_array($resultunitB);
				$currabrev = $myrowCurrency['currabrev'];
				echo '<input type="hidden" name="CurrAbrev" value="'.$currabrev.'">';
				echo "<span style='font-size:8pt'>";
				echo $myrowCurrency['currency'];
				echo "</span>";
			}else {
				echo "<select name='CurrAbrev' style='font-size:8pt'>";
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
		
			echo '<td> <b>' . _('Lista Precio') . ':</b>&nbsp;&nbsp; ';
			$SQLClient="SELECT typeid FROM debtorsmaster WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo."'";
			$result_typeclient = DB_query($SQLClient,$db,$ErrMsg,$DbgMsg,true);
			//echo "num de rows:".DB_num_rows($result_typeclient);
			if (DB_num_rows($result_typeclient)==1) {
				$myrowtype = DB_fetch_array($result_typeclient);
				$tipocliente=$myrowtype[0];
			}
			// Consulta las listas de precio
			$SQL =" SELECT distinct salestypes.typeabbrev,salestypes.sales_type
				FROM salestypes, sec_pricelist, salestypesxcustomer
				WHERE sec_pricelist.pricelist = salestypes.typeabbrev
					AND salestypesxcustomer.typeabbrev = salestypes.typeabbrev
					AND salestypesxcustomer.typeclient='".$tipocliente."'
					AND sec_pricelist.userid='" . $_SESSION['UserID'] . "'
					
					OR salestypes.typeabbrev='".$_SESSION['Items'.$identifier]->DefaultSalesType."'";
			
			$resultunitB = DB_query($SQL,$db);
			if (DB_num_rows($resultunitB)==1){
				$myrowCurrency=DB_fetch_array($resultunitB);
				$SalesType = $myrowCurrency['typeabbrev'];
				echo '<input type="hidden" name="SalesType" value="'.$SalesType.'">';
				echo "<span style='font-size:8pt'>";
				echo $myrowCurrency['sales_type'];
				echo "</span>";
			}else {
				echo "<select name='SalesType' style='font-size:8pt'>";
				while ($myrowlegal = DB_fetch_array($resultunitB)) {
					if ($_SESSION['Items'.$identifier]->DefaultSalesType==$myrowlegal['typeabbrev']){
						echo '<option selected value=' . $myrowlegal['typeabbrev'] . '>' . $myrowlegal['sales_type'].'</option>';
					} else {
						echo '<option value='. $myrowlegal['typeabbrev'] . '>' . $myrowlegal['sales_type'].'</option>';
					}
				}
				echo '</select>';
			}		
			echo '</td>';
			
		}
		echo '</tr>';
		echo "<tr style='height:10px;'><td colspan='3'></td></tr>";
		echo "<tr>";
			echo "<td colspan='3' style='text-align:right'>";
			echo "<input type='submit' name='setOrder' value='IR A SELECCION PRODUCTOS'>";
			echo "</td>";
		echo "</tr>";
		
	}
	echo '</table>';
	
	echo '<input type=hidden name="lineaxs" value='.$totallineas.'>';
	echo '<input type="hidden" name="QuickEntryOrden" value="'.$i.'">';
	echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '">';
	
	echo '</form>';

	if ($_SESSION['ExtractOrderNumber']==0 and $_SESSION['ExtractOrderNumber1']==0 and !isset($_POST['QuickEntry']))
	{
		$_SESSION['AccessOrder']=0;
		$_POST['PartSearch']='yes';
	}
	
	/**************************/
	/* BUSQUEDA DE PRODUCTOS  */
	//echo '<table width=100% border=0><tr><td valign=top >';
	//ho "compra".$_SESSION['AccessOrder'];
	//echo "compra:".$_POST['PartSearch'];
	
	if ($debuggaz==1) {
		echo "** PARA ENTRAR A BUSQUEDA DE PRODUCTOS:<br>";
		echo "POST['PartSearch']:".$_POST['PartSearch']."<br>";
		echo "SESSION['AccessOrder']:".$_SESSION['AccessOrder']."<br>";
		echo "POST['QuickEntry']:".$_POST['QuickEntry']."<br>";
	}
	
	if (isset($_POST['PartSearch']) && $_POST['PartSearch']!='' and $_SESSION['AccessOrder']==0 ) { /*|| !isset($_POST['QuickEntry'])*/
		
		echo '<table border=0 width=100%>';
		
		if (isset($_SESSION['Tagref']) and $_SESSION['Tagref'] > 0) {
			/***************************************************************/
			/***************** INICIO DE BOTONES RAPIDOS *******************/
			echo '<tr><td colspan=2>';
			echo '<table><tr>';
			
				$SQL='SELECT l.loccode,locationname
				      FROM areas a, tags t, locations l
				      where t.areacode=a.areacode
					and l.tagref=t.tagref
					and l.tagref="'.$_SESSION['Tagref'].'"';
				
				$result2 = DB_query($SQL,$db);
				
				$codigoDeAlmacen = 0;
				
				if ($myrow2 = DB_fetch_array($result2)) {
					$codigoDeAlmacen = $myrow2['loccode'];
				}
				
				/* MUESTRA BOTONES RAPIDOS DE PRODUCTOS PERO SOLO DE LOS QUE EL USUARIO TIENE ACCESO A TRAVES DE LA CATEGORIA */
				/* MUESTRA BOTONES RAPIDOS DE PRODUCTOS PERO SOLO DE LOS QUE EL USUARIO TIENE ACCESO A TRAVES DE LA CATEGORIA */
				$SQL='SELECT *
					FROM stockshortsales JOIN stockmaster ON stockshortsales.stockid = stockmaster.stockid
					JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
					JOIN sec_stockcategory ON sec_stockcategory.categoryid= stockcategory.categoryid 
										AND sec_stockcategory.userid="'.$_SESSION['UserID'].'"
					ORDER BY displayline, displayorder';
			    
				$result2 = DB_query($SQL,$db);
				
				$i = 0;
				$j = 1;
				$botonline = 0;
				$lineaant = 1;
				
				while ($myrow2 = DB_fetch_array($result2)) {
					$i = $i + 1;
					$botonline = $botonline + 1;
					
					//SI LINEA CAMBIA ENTONCES FIN DE LINEA Y COMIENZA NUEVA
					if ($myrow2['displayline'] <> $lineaant ) {
						echo '</tr><tr>';
						
						$botonline = 1;
					}
					
					echo '<td><form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier . '" method=post name="quickentry_'.$i.'">';
					
					echo '<input type="hidden" name="PartSearch" value="Yes">';
					echo '<input type="hidden" name="CurrAbrev" value="'.$_SESSION['CurrAbrev'].'">';
					echo '<input type="hidden" name="Tagref" value="'.$_SESSION['Tagref'] .'">';
					echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
					echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
					echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
					echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';    
					
					echo '<input type="hidden" name="part_' . $j . '" value="'.$myrow2['stockid'].'">';
					echo '<input type="hidden" name="qty_' . $j . '"  value="'.$myrow2['quantity'].'">';
					echo '<input type="hidden" name="price_' . $j . '"  value="'.$myrow2['price'].'">';
					echo '<input type="hidden" name="desc1_' . $j . '"  value="'.$myrow2['desc1'].'">';
					echo '<input type="hidden" name="desc2_' . $j . '"  value="'.$myrow2['desc2'].'">';
					echo '<input type="hidden" name="desc3_' . $j . '"  value="'.$myrow2['desc3'].'">';
					
					echo '<input type="hidden" name="Stock_'. $j . '"  value="'.$codigoDeAlmacen.'">';
					echo '<input type="hidden" name="QuickEntry" value="'.$myrow2['stockid'].'">';
					
					echo '<input type=hidden name="lineaxs" value=1>';
					
					if (strlen($myrow2['image'])>3) {
						
						
						//echo '<a href="" >';
						echo '<img onclick="javascript: document.quickentry_'.$i.'.submit();" src="'.$rootpath.'/'.$myrow2['image'].'" title="' . $myrow2['stockid'] . '" alt="'.$myrow2['shortname'].'">';
						//echo '</a>';
						
					} else {
						echo '<input type="submit" name="QuickEntryButton" value="'.$myrow2['shortname'].'">';
					}
					
					
						
					//echo '<input type="submit" name="part_' . $i . '" value="'.$myrow2['stockid'].'" size=10 maxlength=20>';
					$lineaant = $myrow2['displayline'];
					echo '</form></td>';
				
				}
				
			echo '</tr></table>';	
			echo '</td></tr>';
		}
		/***************** FIN DE BOTONES RAPIDOS **********************/
		/***************************************************************/
		
		
		  
		
		if (isset($_SESSION['ExistingOrder']) AND $_SESSION['ExistingOrder']<>'0')
		{
			/* AGREGAR LIGA A ALTA DE ORDEN DE COMPRA AMARRADA A ORDEN DE SERVICIO */
			echo '	<tr><td colspan=2 bgcolor="#f4f98c">';
				echo '<div class="centre">';
				echo '<p class="page_title_text">';
				echo '	<a href="' . $rootpath . '/PO_Header.php?&NewOrder=Yes&TieToOrderNumber=' . $_SESSION['ExistingOrder'].'&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'">
					<img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . _('Orden de Compra') . '" alt="">&nbsp;<b>GENERAR ORDEN DE COMPRA PARA ESTE PEDIDO</b></a></p></div>';
				echo '<td>
				</tr>';
				
			$ssql = 'SELECT o.orderno, itemcode,itemdescription,
				locationname,quantityord,quantityrecd,margenaut,
				loccode,case when actprice>0  and status<>"Completed" then actprice else stdcostunit end ,
				case when actprice>0 then actprice* (1+(margenaut/100))else stdcostunit* (1+(margenaut/100)) end,status ';
			$ssql .= ' FROM purchorders o, purchorderdetails d, locations l, stockmaster s,stockcategory c';
			$ssql .= ' WHERE l.loccode=o.intostocklocation';
			$ssql .= ' AND o.orderno=d.orderno';
			$ssql .= ' AND s.stockid=d.itemcode';
			$ssql .= ' AND s.categoryid=c.categoryid';
			#$ssql .= ' AND status<>"Completed"';
			#$ssql .= ' AND d.completed=0';
			$ssql .= ' AND requisitionno="'.$_SESSION['ExistingOrder'].'"';
			$resultorders = DB_query($ssql,$db);
			if (DB_num_rows($resultorders)>0)
			{
				echo '  <tr><td colspan=2>';
				/* CODIGO PARA DESPLEGAR ESTATUS DE ORDENES DE COMPRA PENDIENTES PARA ESTA ORDEN DE SERVICIO */
				
				
				echo '<table width="90%" cellpadding="2" border="1">
					<tr><td colspan=10>
						<div class="centre"><b>Órdenes de Compra del Pedido</b></div>
						</td>
					</tr>';

					echo '<tr bgcolor=#800000>';
					
					echo '<th>' . _('Orden No.') . '</th>
						<th>' . _('Codigo Producto') . '</th>
						<th>' . _('Descripcion') . '</th>
						<th>' . _('Almacen') . '</th>
						<th>' . _('Costo') . '</th>
						<th>' . _('Cantidad').'<br>'._('Solicitada') . '</th>
						<th>' . _('Cantidad').'<br>'._('Recibida') . '</th>
						<th>' . _('Margen').'<br>'._('Aut') . '</th>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						';
					echo '</tr>';
					
					while ($myrowdetails = DB_fetch_array($resultorders))
					{
						echo '<tr>';
						echo '<td nowrap style="text-align:center;">
						   <a href="GoodsReceived.php?'. SID .'&PONumber='.$myrowdetails[0].'&TieToOrderNumber='.$_SESSION['ExistingOrder'].'"><font size=1>' . $myrowdetails[0] . '</font></a></td>
						   <td nowrap><font size=1>' . $myrowdetails[1] . '</td>
						   <td nowrap><font size=1>' . $myrowdetails[2] . '</td>
						   <td nowrap><font size=1>' . $myrowdetails[3] . '</td>
						   <td nowrap style="text-align:right;"><font size=1>$ ' . number_format($myrowdetails[8],2) . '</td>
						   <td nowrap style="text-align:center;"><font size=1>' . $myrowdetails[4] . '</td>
						   <td nowrap style="text-align:center;"><font size=1>' . $myrowdetails[5] . '</td>
						   <td nowrap style="text-align:center;"><font size=1>' . $myrowdetails[6] . '%</td>
						   <td nowrap style="text-align:center;">&nbsp;';
							if ($myrowdetails[5]>0)
							{
								if ($myrowdetails[10]!='Completed'){
									$myrowdetails[8]=0;
								}
							     echo '<a href="SelectOrderItemsV4_0.php?' . SID .'&ModifyOrderNumber='.$_SESSION['ExistingOrder'].'&costoproductoagregado='.$myrowdetails[8].'&lineaxs='.($totallineas).'&agregarproducto=yes&itm'.$myrowdetails[1].'|'.$myrowdetails[7].'='.$myrowdetails[5].'&almacenorigen='.$myrowdetails[7].'&precioproductoagregado='.$myrowdetails[9].' "><font size=1>agregar a pedido</font></a>';
							} 
						echo '</td>';
						   
						echo '<td nowrap style="text-align:center;">&nbsp;';
							if ($myrowdetails[5]!=$myrowdetails[4])
							{
							     echo '<a href="GoodsReceived.php?' . SID .'&PONumber='.$myrowdetails[0].'&ModifyOrderNumber='.$_SESSION['ExistingOrder'].'"><font size=1>recibir orden</font></a>';
							}
						echo '</td>';
						echo '</tr>';					 
					};	
					
					echo '</table>';
				
				/*****************************************************************************/
				
				echo '  </td></tr>';
				/************************************************************************/
			};	
		};
		
		echo '  <tr><td colspan=2>';
		echo '          <table border=0 width=100%>';
		echo '			<tr>	<td>';
		echo '					  ';
		echo '				</td>';
		echo '				<td>';
		echo '					  ';
		echo '				</td>';
		echo '			</tr>';
		echo '		</table>';
		echo '	</tr>';
		
		
		
		echo '  <tr><td colspan=2>';
		echo '          <table border=0 width=100%>';
		echo '			<tr>	<td>';
		// TABLA 1
		
		
		if (isset($_SESSION['Tagref']) and $_SESSION['Tagref'] > 0 ) {
		
			echo	'<table border=1 width=100%>
					<tr><td><table>';
			
			echo '<form action="' . $_SERVER['PHP_SELF'] . '?&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref']. SID . '" name="SelectParts" method=post>';
			echo '<input type="hidden" name="PartSearch" value="Yes">
			<input type=hidden name="lineaxs" value='.$totallineas.'>';		
			
			
			$SQL="SELECT distinct stockcategory.categoryid,
				categorydescription
			 FROM stockcategory, sec_stockcategory
			 WHERE sec_stockcategory.categoryid= stockcategory.categoryid
				   AND sec_stockcategory.userid='".$_SESSION['UserID']."'
			 ORDER BY categorydescription";
			$result1 = DB_query($SQL,$db);
			
			echo '	<tr>
					<td colspan=2>';
						echo '<div class="centre"><b><p>' . $msg . '</b></p>';
						echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ';
						echo _('Busqueda de Productos') . '</p></div>';
					echo '</td>
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
						echo '<b>'. _('X_Descripcion').':</b>
					</td>
			
					<td>
						<input tabindex=2 type="Text" name="Keywords" size=30 maxlength=40 value="'.$_POST['Keywords'].'">
					</td>
				</tr>
				<tr>
					<td align="right">
						<b> </b><b>'._('X Codigo').':</b>
					</td>
					<td>
						<input tabindex=3 type="Text" name="StockCode" size=15 maxlength=18 value="'.$_POST['StockCode'].'">
					</td>
				</tr>';
				if($_SESSION['ShowAllProductsForSales']==1){
				echo'<tr>
					<td align="right">
						<b> </b><b>'._('Incluye Productos Sin Optimo').':</b>
					</td>
					<td>
						<input type="checkbox" name="prodsinoptimo" value=1>
					</td>
				</tr>';
				}
				echo'
				<tr>
				<td></td><td height=100%><br>
					
						<input tabindex=4 type=submit name="Search" value="'._('Buscar Productos').'">
				</td>
			</tr>';
			
			echo '</form>';
				
			echo '</table>';
		}
		
		// FIN TABLA 1
		echo '				</td>';
		echo '				<td>';
		// TABLA 2
		
		//echo 'AQUI VA LA BUSQUEDA RAPIDA SOLO QUE YA SE HAYA SELECCIONADO LA UNIDAD DE NEGOCIO ';
		if (isset($_SESSION['Tagref']) and $_SESSION['Tagref'] > 0) {
			 $SQLRazonsocial="select legalid from tags where tagref ='". $_SESSION['Tagref']."'";
			$ErrMsg = _('The demand for this product from') . ' ' . $loccationstock . ' ' . _('cannot be retrieved because');
			$LegalResult = DB_query($SQLRazonsocial,$db,$ErrMsg,$DbgMsg);
			
			if (DB_num_rows($LegalResult)==1){
			  $LegalRow = DB_fetch_row($LegalResult);
			  $LegalID =  $LegalRow[0];
			} else {
			  $LegalID =0;
			}
			 if(($_SESSION['MultipleBilling'])==0)   {
				$SQL='SELECT l.loccode,locationname
				      FROM areas a, tags t, locations l,sec_loccxusser sec
				      where t.areacode=a.areacode
				        and l.loccode=sec.loccode
					 AND sec.userid="'.$_SESSION['UserID'].'"
					and l.tagref=t.tagref
					and l.tagref="'.$_SESSION['Tagref'].'"';
				
					if ($totallineas>0){
						//$totallineas=$totallineas+1;
					}
			 }else{
			
			    $SQL="SELECT locations.loccode,
					locationname
				 FROM locations, sec_loccxusser, tags
				 WHERE locations.loccode=sec_loccxusser.loccode
				       AND sec_loccxusser.userid='".$_SESSION['UserID']."'
				       AND tags.tagref=locations.tagref
				       AND tags.legalid = '" .  $LegalID . "'";
				    if(isset($_SESSION['loccode']) and strlen($_SESSION['loccode'])>0)   {
					$SQL=$SQL."AND locations.loccode = '" .  $_SESSION['loccode'] . "'";
				    }
			 }
			//echo $SQL;
			    echo '<table>
					<tr>
						<td>';
			    echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier.'" method=post name="quickentryZ">';
							echo '<input type="hidden" name="PartSearch" value="Yes">';
							echo '<input type="hidden" name="CurrAbrev" value="'.$_SESSION['CurrAbrev'].'">';
							echo '<input type="hidden" name="Tagref" value="'.$_SESSION['Tagref'] .'">';
							echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
							echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
							echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
							echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';     
					echo '	</td>
					</tr>
					<tr>
						<td>';
							echo '<table border=1>
								<tr>';
								echo '<th>' . _('Codigo') . '</th>
								      <th>' . _('Cantidad') . '</th>
								      <th>' . _('Almacen') . '</th>
								</tr>';
								
							for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){
								echo '<tr class="OddTableRow">';
								echo '		<td><input type="text" name="part_' . $i . '" size=21 maxlength=20></td>
										<td>
											<input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qty_' . $i . '" size=6 maxlength=6>
											
										</td>
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
							<input type=hidden name="lineaxs" value='.$_SESSION['QuickEntries'].'>
							<input type="submit" name="QuickEntry" value="' . _('AGREGAR') . '">
						</div>
						
						</td>
					</tr>';
				echo '</form>';
				echo '</table>';
				
		}
		
		// FIN TABLA 2
		echo '				</td>';
		echo '			</tr>';
		echo '		</table>';
		echo '	</tr>';
		
	}
	
	if($_SESSION['MultipleBilling']==0){	
		// resultados de busqueda de productos
		if (isset($_POST['Search']) or isset($_POST['NextO']) or isset($_POST['PrevO']) or isset($_POST['Go1'])){
			
		
			echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'&identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'"& name="SelectParts" method=post>';
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
				$descripcion2=$_POST['Keywords'];
				$i=0;
				$SearchString = '%';
				while (strpos($_POST['Keywords'], ' ', $i)) {
					$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
					$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
					$i=strpos($_POST['Keywords'],' ',$i) +1;
				}
				$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';
				
				$_POST['Keywords']=$descripcion2;
				if ($_POST['StockCat']=='All'){
					
					/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
					/* ORDENA PRIMERO POR CANTIDAD DISPONIBLE EN ALMACENES DEL USUARIO Y DESPUES POR PRECIO */
					//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')		
					$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							disponibilidad.disponible,
							disponibilidad.optimo,
							stockmaster.mbflag,
							locstock.ontransit,
							COALESCE((prices.price),9999999) as price
						FROM  ( SELECT locstock.stockid, sum(locstock.quantity-locstock.ontransit) as disponible,
							sum(locstock.reorderlevel) as optimo
							FROM (
								select stockmaster.stockid, stockmaster.categoryid
								from stockmaster
								where stockmaster.description LIKE  '". $SearchString ."'
									AND stockmaster.mbflag <>'G' AND stockmaster.discontinued=0
							 ) AS stockmaster1 JOIN locstock ON locstock.stockid = stockmaster1.stockid 
								JOIN locations ON locstock.loccode = locations.loccode
								
								JOIN sec_stockcategory ON sec_stockcategory.categoryid= stockmaster1.categoryid
								AND sec_stockcategory.userid='".$_SESSION['UserID']."'
								JOIN sec_loccxusser ON sec_loccxusser.loccode=locstock.loccode AND
								sec_loccxusser.userid='".$_SESSION['UserID']."'
								GROUP BY stockmaster1.stockid
							)as disponibilidad JOIN stockmaster ON disponibilidad.stockid = stockmaster.stockid 
							LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
							typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
							(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
							JOIN locstock ON disponibilidad.stockid = locstock.stockid
							JOIN locations ON locstock.loccode = locations.loccode ";
					$SQL = $SQL. " WHERE ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";		
					if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
						$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
					}
					
					//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
					if (true){
						$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
					}
					
					
					/*$SQL = $SQL. " GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							locstock.ontransit,
							disponibilidad.disponible,
							stockcategory.redinvoice";
					*/
					/*$SQL = $SQL. " 
						ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
									       */
						
				} else {
					
					/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
					/*$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units,
						locstock.loccode, locstock.quantity, disponibilidad.disponible,
							stockcategory.redinvoice,COALESCE(MIN(prices.price),9999999) as price,locstock.ontransit
						FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
						FROM stockmaster, stockcategory ,locstock,sec_loccxusser , sec_stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
							AND sec_stockcategory.categoryid= stockcategory.categoryid
							AND sec_stockcategory.userid='".$_SESSION['UserID']."'";
						
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
					
					//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
					if (true){
						$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
					}
					
					$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
					$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
					$SQL = $SQL. " GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							locstock.ontransit,
							disponibilidad.disponible,
							stockcategory.redinvoice";
					*/
					
					$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							disponibilidad.disponible,
							disponibilidad.optimo,
							stockmaster.mbflag,
							locstock.ontransit,
							COALESCE((prices.price),9999999) as price
						FROM  ( SELECT locstock.stockid, sum(locstock.quantity-locstock.ontransit) as disponible,
							sum(locstock.reorderlevel) as optimo
							FROM (
								select stockmaster.stockid, stockmaster.categoryid
								from stockmaster
								where stockmaster.description LIKE  '". $SearchString ."'
									AND stockmaster.mbflag <>'G' AND stockmaster.discontinued=0
							 ) AS stockmaster1 JOIN locstock ON locstock.stockid = stockmaster1.stockid 
								JOIN locations ON locstock.loccode = locations.loccode
								
								JOIN sec_stockcategory ON sec_stockcategory.categoryid= stockmaster1.categoryid
								AND sec_stockcategory.userid='".$_SESSION['UserID']."'
								JOIN sec_loccxusser ON sec_loccxusser.loccode=locstock.loccode AND
								sec_loccxusser.userid='".$_SESSION['UserID']."'
								GROUP BY stockmaster1.stockid
							)as disponibilidad JOIN stockmaster ON disponibilidad.stockid = stockmaster.stockid 
							LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
							typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
							(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
							JOIN locstock ON disponibilidad.stockid = locstock.stockid
							JOIN locations ON locstock.loccode = locations.loccode ";
							
					$SQL = $SQL. " WHERE ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";		
					if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
						$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
					}
					
					//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
					if (true){
						$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
					}
					
					$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
					
					
					
					/*$SQL = $SQL. " 
						ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
									       */
				}
		
			} elseif (strlen($_POST['StockCode'])>0){
			
				$_POST['StockCode'] = strtoupper($_POST['StockCode']);
				$SearchString = $_POST['StockCode']; //'%' . $_POST['StockCode'] . '%';
				if ($_POST['StockCat']=='All'){
					
					/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
					/*$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible,
							stockcategory.redinvoice,COALESCE(MIN(prices.price),9999999) as price,locstock.ontransit
						FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
						FROM stockmaster, stockcategory ,locstock,sec_loccxusser ,sec_stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
							AND sec_stockcategory.categoryid= stockcategory.categoryid
							AND sec_stockcategory.userid='".$_SESSION['UserID']."'";
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
					
					//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
					if (true){
						$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";
					}
					
					$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
					$SQL = $SQL. " GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							locstock.ontransit,
							disponibilidad.disponible,
							stockcategory.redinvoice";*/
					/*$SQL = $SQL. " 
						ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
									       */
							
					$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							disponibilidad.disponible,
							disponibilidad.optimo,
							stockmaster.mbflag,
							locstock.ontransit,
							COALESCE((prices.price),9999999) as price
						FROM  ( SELECT locstock.stockid, sum(locstock.quantity-locstock.ontransit) as disponible,
							sum(locstock.reorderlevel) as optimo
							FROM (
								select stockmaster.stockid, stockmaster.categoryid
								from stockmaster
								where stockmaster.stockid LIKE  '". $SearchString ."'
									AND stockmaster.mbflag <>'G' AND stockmaster.discontinued=0
							 ) AS stockmaster1 JOIN locstock ON locstock.stockid = stockmaster1.stockid 
								JOIN locations ON locstock.loccode = locations.loccode
								
								JOIN sec_stockcategory ON sec_stockcategory.categoryid= stockmaster1.categoryid
								AND sec_stockcategory.userid='".$_SESSION['UserID']."'
								JOIN sec_loccxusser ON sec_loccxusser.loccode=locstock.loccode AND
								sec_loccxusser.userid='".$_SESSION['UserID']."'
								GROUP BY stockmaster1.stockid
							)as disponibilidad JOIN stockmaster ON disponibilidad.stockid = stockmaster.stockid 
							LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
							typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
							(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
							JOIN locstock ON disponibilidad.stockid = locstock.stockid
							JOIN locations ON locstock.loccode = locations.loccode ";
						$SQL = $SQL. " WHERE ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";		
						if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
							$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
						}
						
						//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
						if (true){
							$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
						}
						
						
				} else {
					
					/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
					/*$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible,
							stockcategory.redinvoice,
							COALESCE(MIN(prices.price),9999999) as price,locstock.ontransit
						FROM  ( SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
						FROM stockmaster, stockcategory ,locstock,sec_loccxusser , sec_stockcategory
						WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
							AND sec_stockcategory.categoryid= stockcategory.categoryid
							AND sec_stockcategory.userid='".$_SESSION['UserID']."'";
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
					
					//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
					if (true){
						$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
					}
					
					$SQL= $SQL. " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
					$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
					$SQL = $SQL. " GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							locstock.ontransit,
							disponibilidad.disponible,
							stockcategory.redinvoice";*/
					$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							disponibilidad.disponible,
							disponibilidad.optimo,
							stockmaster.mbflag,
							locstock.ontransit,
							COALESCE((prices.price),9999999) as price
						FROM  ( SELECT locstock.stockid, sum(locstock.quantity-locstock.ontransit) as disponible,
							sum(locstock.reorderlevel) as optimo
							FROM (
								select stockmaster.stockid, stockmaster.categoryid
								from stockmaster
								where stockmaster.stockid LIKE  '". $SearchString ."'
									AND stockmaster.mbflag <>'G' AND stockmaster.discontinued=0
							 ) AS stockmaster1 JOIN locstock ON locstock.stockid = stockmaster1.stockid 
								JOIN locations ON locstock.loccode = locations.loccode
								
								JOIN sec_stockcategory ON sec_stockcategory.categoryid= stockmaster1.categoryid
								AND sec_stockcategory.userid='".$_SESSION['UserID']."'
								JOIN sec_loccxusser ON sec_loccxusser.loccode=locstock.loccode AND
								sec_loccxusser.userid='".$_SESSION['UserID']."'
								GROUP BY stockmaster1.stockid
							)as disponibilidad JOIN stockmaster ON disponibilidad.stockid = stockmaster.stockid 
							LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
							typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
							(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
							JOIN locstock ON disponibilidad.stockid = locstock.stockid
							JOIN locations ON locstock.loccode = locations.loccode ";
						$SQL = $SQL. " WHERE ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";		
						if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
							$SQL= $SQL. " AND locstock.loccode ='". $_POST['StockLock']."'";	
						}
						
						//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
						if (true){
							$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
						}
						
					
					
					/*$SQL = $SQL. " 
						ORDER BY price, disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode";
									       */	
				}
		
			} else {
				if ($_SESSION['ProductSearch']==0 and ($_POST['StockCat']=='All' or strlen($_POST['StockCat'])<=0)){
					//if ($_POST['StockCat']=='All' or strlen($_POST['StockCat'])<=0){
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
					//}	
						
				} else {
					if ($_SESSION['ProductSearch']==1 and $_POST['StockCat']=='All'){
						$_POST['StockCat']="";
					}
					/*NUEVO SQL QUE ORDENA POR DISPONIBILIDAD Y PRECIO, TAMBIEN TRAE EN PRECIO*/
					/*$SQL = "SELECT stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity,locstock.ontransit,
						disponibilidad.disponible,
						stockcategory.redinvoice,
							COALESCE(MIN(prices.price),9999999) as price
						FROM  (
							SELECT stockmaster.stockid, sum(locstock.quantity-locstock.ontransit) as disponible
							FROM stockmaster, stockcategory ,locstock,sec_loccxusser, sec_stockcategory
							WHERE stockmaster.categoryid=stockcategory.categoryid AND stockmaster.stockid=locstock.stockid
							AND sec_stockcategory.categoryid= stockcategory.categoryid
							AND sec_stockcategory.userid='".$_SESSION['UserID']."'";
						//AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					$SQL = $SQL . " AND stockmaster.mbflag <>'G' AND 
						sec_loccxusser.loccode=locstock.loccode AND sec_loccxusser.userid='".$_SESSION['UserID']."' AND 
						stockmaster.discontinued=0";
					if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
						$SQL= $SQL. " AND locstock.loccode like '%". $_POST['StockLock']."%'";	
					}
					$SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
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
					
					//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
					if (true){
						$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
					}
					
					$SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
					$SQL = $SQL. " AND locstock.loccode = locations.loccode AND ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";
					$SQL = $SQL. " GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							locstock.ontransit,
							disponibilidad.disponible,
							stockcategory.redinvoice
							";
														  */
					$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							locstock.loccode,
							locstock.quantity,
							disponibilidad.disponible,
							disponibilidad.optimo,
							stockmaster.mbflag,
							locstock.ontransit,
							COALESCE((prices.price),9999999) as price
						FROM  ( SELECT locstock.stockid, sum(locstock.quantity-locstock.ontransit) as disponible,
							sum(locstock.reorderlevel) as optimo
							FROM (
								select stockmaster.stockid, stockmaster.categoryid
								from stockmaster
								where stockmaster.stockid LIKE  '%". $SearchString ."%'
									AND stockmaster.mbflag <>'G' AND stockmaster.discontinued=0
							 ) AS stockmaster1 JOIN locstock ON locstock.stockid = stockmaster1.stockid 
								JOIN locations ON locstock.loccode = locations.loccode
								
								JOIN sec_stockcategory ON sec_stockcategory.categoryid= stockmaster1.categoryid
								AND sec_stockcategory.userid='".$_SESSION['UserID']."'
								JOIN sec_loccxusser ON sec_loccxusser.loccode=locstock.loccode AND
								sec_loccxusser.userid='".$_SESSION['UserID']."'
								GROUP BY stockmaster1.stockid
							)as disponibilidad JOIN stockmaster ON disponibilidad.stockid = stockmaster.stockid 
							LEFT JOIN prices ON  stockmaster.stockid = prices.stockid and
							typeabbrev = '".$_SESSION['Items'.$identifier]->DefaultSalesType."' and currabrev = '".$_SESSION['CurrAbrev']."' and
							(debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."' or debtorno = '') and (branchcode = '".$_SESSION['Items'.$identifier]->Branch."' or branchcode = '')
							JOIN locstock ON disponibilidad.stockid = locstock.stockid
							JOIN locations ON locstock.loccode = locations.loccode ";
						$SQL = $SQL. " WHERE ((locations.temploc = 1 and locstock.quantity > 0) or locations.temploc = 0)";		
						if (strlen($_POST['StockLock'])>0 && $_POST['StockLock']!='All'){
							$SQL= $SQL. " AND locstock.loccode like '%". $_POST['StockLock']."%'";	
						}
						$SQL= $SQL. " AND stockmaster.categoryid like '%" . $_POST['StockCat'] . "%' ";
						//PARAMETRIZAR QUE SOLO PUEDA VER ALMACENES DE LA UNIDAD DE NEGOCIO DE LA QUE VOY A FACTURAR
						if (true){
							$SQL= $SQL. " AND locations.tagref ='". $_SESSION['Tagref']."'";	
						}
						
					
						
				  }
			}
			if ($_POST['prodsinoptimo']=='1'and $_SESSION['ShowAllProductsForSales']==1){
				//$SQL = $SQL. " AND case when stockmaster.mbflag !='L' then (disponibilidad.optimo>0) else abs((disponibilidad.disponible))>0 end ";
			}elseif($_SESSION['ShowAllProductsForSales']==1){
					$SQL = $SQL. " AND case when stockmaster.mbflag !='L'  then (disponibilidad.disponible>0) else abs((disponibilidad.disponible))>0 end ";
			}
			
			$SQL = $SQL. " GROUP BY  stockmaster.stockid, stockmaster.description, stockmaster.units, locstock.loccode, locstock.quantity, disponibilidad.disponible, disponibilidad.optimo, stockmaster.mbflag, locstock.ontransit, COALESCE((prices.price),9999999) ";
			
			
			$SQL = $SQL. " ORDER BY  disponibilidad.disponible desc, stockmaster.stockid, locstock.loccode,price";	
			//echo $SQL;
			//echo "sql:".$SQL."<br>";
			//exit;
			
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
				$num_reg=$_SESSION['DisplayRecordsMax'];
			}else{
				$num_reg=$_POST['num_reg'];
			}
			//echo "<br>sql:<br>".$SQL;
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
		}elseif(isset($_POST['Search']) or isset($_POST['NextO']) or isset($_POST['PrevO']) or isset($_POST['Go1']) and $_SESSION['MultipleBilling']==1){//cambia por tipo de facturacion multiple
			
			
			
			
			
		}
		//end of if search
	
	
		//*************************************Mostrar productos despues de la busqueda
		if (isset($SearchResult)) {
			echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'&identifier='.$identifier . ' method=post name="orderform">';
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
			$TableHeader = '<tr><th style="font-face:verdana;font-size:10px" width=5%>' . _('Codigo') . '</th>
					<th style="font-face:verdana;font-size:10px"  width=25%>' . _('Descripcion') . '</th>
					<th style="font-face:verdana;font-size:10px"  width=10%>' . _('Almacen') . '</th>
					<th style="font-face:verdana;font-size:10px"  width=5%>' . _('Disp:') . '</th>
					<th style="font-face:verdana;font-size:10px"  width=5%>' . _('Disp Gral') . '</th>';
			$TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px" width=5% >' . _('Cant:') . '</th>';
			
			$SQLClient="SELECT typeid FROM debtorsmaster WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo."'";
			$result_typeclient = DB_query($SQLClient,$db,$ErrMsg,$DbgMsg,true);
			//echo "num de rows:".DB_num_rows($result_typeclient);
			if (DB_num_rows($result_typeclient)==1) {
				$myrowtype = DB_fetch_array($result_typeclient);
				$tipocliente=$myrowtype[0];
			}
			
			
			$SQLprice =" SELECT distinct salestypes.typeabbrev,salestypes.sales_type
				FROM salestypes, sec_pricelist, salestypesxcustomer
				WHERE	sec_pricelist.pricelist = salestypes.typeabbrev
					AND salestypesxcustomer.typeabbrev = salestypes.typeabbrev
					AND salestypesxcustomer.typeclient = '".$tipocliente."'
					AND sec_pricelist.userid='" . $_SESSION['UserID'] . "'
					or  salestypes.typeabbrev='".$_SESSION['Items'.$identifier]->DefaultSalesType."'
				ORDER BY salestypes.sales_type";
			$prices = DB_query($SQLprice,$db);
			$listaprecio=array();
			$listacolorprecio=array();
			$countlista=0;
			while ($myrows=DB_fetch_array($prices)) {
				$lprecio=$myrows['sales_type'];
				$abrelist=$myrows['typeabbrev'];
				$TableHeader =$TableHeader .' <th style="font-face:verdana;font-size:10px">
				<a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier . '&lineaxs='.$totallineas.'
				&StockCat='.$_POST['StockCat'].'&StockLock='.$_POST['StockLock'].'&SalesType='.$myrows['typeabbrev'].'
				&StockCode='.$_POST['StockCode'].'&PartSearch=Yes&Search=Yes&Keywords='.$_POST['Keywords'].'">'.
				$lprecio . '</a></th>';
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
				$redinvoice=$myrow['redinvoice'];
				$qohsql = "SELECT loccode, locationname, areacode 
					   FROM locations JOIN tags ON locations.tagref = tags.tagref
					   WHERE   loccode = '" . $loccationstock . "'";
				$qohresult =  DB_query($qohsql,$db);
				$qohrow = DB_fetch_row($qohresult);
				$qoh =  $qohrow[1];
				$codigobranch=$qohrow[0];
				$codigoarea=$qohrow[2];
				
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
					echo '<td style=" vertical-align:top;">
						<a style="text-align:center; font-face:verdana;font-size:10px; vertical-align:top;" target="_blank" href="' . $rootpath . '/StockStatus.php?' . SID .'&identifier='.$identifier . '&StockID=' . $myrow['stockid'] . '&DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '">' . $myrow['stockid'] . '</a>
					</td>';
					echo "<td style='vertical-align:top;font-face:verdana;font-size:12px' width=120><b>".$myrow['description']."</b></td>";
					$counter_sucbus=$counter_sucbus+1;
				} else {
					echo '<td></td><td></td>';
				}
				
				//}
				
				$sqlLL = "SELECT case when isnull(SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)) then 0 else SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) end  AS dem
					FROM salesorderdetails,
					     salesorders
					WHERE salesorders.orderno = salesorderdetails.orderno 
					AND salesorderdetails.completed=0 AND
					salesorders.quotation=0 AND
					salesorderdetails.fromstkloc!='" . $loccationstock . "' AND
					salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
				//echo $sqlLL;	
			       $ErrMsg = _('The demand for this product from') . ' ' . $loccationstock . ' ' . _('cannot be retrieved because');
			       $DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
			       if (DB_num_rows($DemandResult)==1){
				 $DemandRow = DB_fetch_row($DemandResult);
				 $DemandGpo =  $DemandRow[0];
			       } else {
				 $DemandGpo =0;
			       }
				
				$SQL = "SELECT sum(locstock.quantity-locstock.ontransit)-".$DemandGpo." as quantity
					FROM locstock
					WHERE locstock.stockid='" .  $myrow['stockid'] . "'
						AND locstock.loccode!='" . $loccationstock . "'";
				$ErrMsg = _('No se puede recuperar la cantidad para facturar');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				$CheckNegRow = DB_fetch_array($Result);
				$AvailableGpo=$CheckNegRow['quantity'];
				//echo $SQL;
				$sqlLL = "SELECT case when SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced)is null then 0 else SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) end AS dem
					FROM salesorderdetails,
					     salesorders
					WHERE salesorders.orderno = salesorderdetails.orderno AND
					salesorderdetails.fromstkloc='" . $loccationstock . "' AND
					salesorderdetails.completed=0 AND
					salesorders.quotation=0 AND
					salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
		       
			       $ErrMsg = _('The demand for this product from') . ' ' . $loccationstock . ' ' . _('cannot be retrieved because');
			       $DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
		       
			       if (DB_num_rows($DemandResult)==1){
				 $DemandRow = DB_fetch_row($DemandResult);
				 $DemandQty =  $DemandRow[0];
			       } else {
				 $DemandQty =0;
			       }
				
				$stockid=$myrow['stockid'];
				
				//$Available = $myrow['quantity'];
				$Available = ($myrow['quantity']-$myrow['ontransit']) - $DemandQty;
				
				//echo 'entra'.$Available;
				$vertabla=$vertabla+1;
	
				if (in_array($codigobranch,$listaloccxbussines)){
					$tipocaja="text";
				}else{
					$tipocaja="hidden";
				}
				
				
				
				
				echo "<td style='font-face:verdana;font-size:10px' title='".$qoh."' class=".$classtd.">".$loccationstock."</td>";
				
				if ($Available > 0) {
					echo "<td style='text-align:right' class=".$classtd."><b>".number_format($Available,0)."</b></td>";
					echo "<td style='text-align:right' class=".$classtd."><b>".number_format($AvailableGpo,0)."</b></td>";
				} elseif($myrow['mbflag']=='L') {
					echo "<td style='text-align:right' class=".$classtd.">-</td>";
					echo "<td style='text-align:right' class=".$classtd.">-</td>";
					//echo "<td style='text-align:right' class=".$classtd."><b>".number_format($Available,0)."<b></td>";
				}else{
					//echo "<td style='text-align:right' class=".$classtd."><b>-<b></td>";
					echo "<td style='text-align:right' class=".$classtd."><b>".number_format($Available,0)."<b></td>";
					echo "<td style='text-align:right' class=".$classtd."><b>".number_format($AvailableGpo,0)."<b></td>";
				}
				
				echo '<td style="text-align:center">
					<input class="number" onKeyPress="return restrictToNumbers(this, event)" tabindex='.number_format($j+7).' type="'.$tipocaja.'" size=4 name="itm'.$myrow['stockid'].'|'.$codigobranch.'" value=0>
				</td>';
				//echo 'moneda'.$_SESSION['CurrAbrev'];
				//echo "<td style='text-align:right'>$".number_format($Pricex,2)."</td>";
				for($countlista=0;$countlista<$listapreciototal;$countlista++) {
					$listapreciox=$listaprecio[$countlista];
					
					$Pricey = GetPriceWTAX($stockid, $_SESSION['Items'.$identifier]->DebtorNo,$listapreciox,$_SESSION['CurrAbrev'], $codigoarea, $db);
					
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
						//SI ES FONDO BLANCO, Y SI ES LA LISTA SELECCIONADA SOMBREALA CON AMARILLO
						if ($_SESSION['Items'.$identifier]->DefaultSalesType!=$listapreciox){
							echo "<td style='font-face:verdana;font-size:10px;text-align:right;' class=".$classtd." >".$valorlista."</td>";
						} else {
							echo "<td style='font-face:verdana;font-size:10px;text-align:right;background-color:#C9C900' class=".$classtd." >".$valorlista."</td>";
						}
					} else {
						echo "<td style='font-face:verdana;font-size:10px;text-align:right;background-color:".$bgcolorlista."' class=".$classtd." >".$valorlista."</td>";
					}
					//echo $_SESSION['Items'.$identifier]->DefaultSalesType;
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
	}elseif($_SESSION['MultipleBilling']==1){
		include('includes/MultipleBilling.inc');
	}
	
	// Muestra entrada rapida
	elseif(!isset($SearchResult) and isset($_POST['QuickEntry'])){
		
	}elseif(!isset($SearchResult) 
		and  $_SESSION['ExistingOrder'] ==0
		and $_SESSION['ExtractOrderNumber1']!=''
		and $_SESSION['ExtractOrderNumber1']!=0
		and $_SESSION['Items'.$identifier]->DebtorNo!=''
		and $_SESSION['AccessOrder']==1){
		
		// extrae ultima orden de compra
		$sql="Select *
		      from salesorders
		      where debtorno='". $_SESSION['Items'.$identifier]->DebtorNo."'
		       and branchcode='".$_SESSION['Items'.$identifier]->Branch."'
		       order by orderno desc
		       limit 1
		       ";
		$ErrMsg =  _('No existen ordenes de venta');
		$GetOrderResult = DB_query($sql,$db,$ErrMsg);
		if (DB_num_rows($GetOrderResult)>0) {
			$myrowx = DB_fetch_array($GetOrderResult);
			$orderno=$myrowx[0];
			
			$tagorder=$myrowx[31];
			$_SESSION['Tagref']=$tagorder;
			$LineItemsSQL = "SELECT salesorderdetails.stkcode,
					salesorderdetails.quantity,
					locstock.loccode as fromstkloc1,
					locations.locationname as locationname1,
					salesorderdetails.quantity,
					salesorderdetails.warranty,
					stockcategory.redinvoice,
					stockmaster.description as producto
				FROM salesorderdetails INNER JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
				     INNER JOIN locstock ON locstock.stockid = stockmaster.stockid,
				     locations, stockcategory
				WHERE  locstock.loccode=locations.loccode
					AND stockcategory.categoryid=stockmaster.categoryid
					AND locstock.loccode=salesorderdetails.fromstkloc
					AND salesorderdetails.orderno =" . $orderno . "
				ORDER BY salesorderdetails.orderlineno";
				$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
				$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
				if (db_num_rows($LineItemsResult)>0) {
					
				$SQL='SELECT l.loccode,locationname
				      FROM areas a, tags t, locations l
				      where t.areacode=a.areacode
				      and l.tagref=t.tagref
				      and l.tagref='.$tagorder;
				
				echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'] . ' method="post" name="quickentryorder">';
				echo '<table width=100% >
				<tr>
					<td><div>';
						echo '<p class="page_title_text">
							<img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . '';
						       echo _('Ultima Orden') . '</p>
						     </div>';
						echo '<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'">';
						echo '<input type="hidden" name="StockLock" value="'.$_POST['StockLock'].'">';
						echo '<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'">';
						echo '<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'">';
						echo '<input type="hidden" name="identifier" value="'.$identifier.'">';
						//echo '<input type="hidden" name="order_items" value="1">';
						
				echo '	</td>
				</tr>
				<tr>
					<td>';
						echo '<table border=1>
							<tr>';
							echo '<th>' . _('Codigo') . '</th>
							       <th>' . _('Descripcion') . '</th>
							      <th>' . _('Cantidad') . '</th>
							      <th>' . _('Almacen') . '</th>
							</tr>';
						$i=0;
					     while ($myrowextract = DB_fetch_array($LineItemsResult)) {
							$i=$i+1;
							echo '<tr class="OddTableRow">';
							echo ' <td><input type="text" name="part_' . $i . '" size=21 maxlength=20 value="'.$myrowextract['stkcode'].'"></td>
								<td style="font-face:verdana;font-size:8px; vertical-align:top;" ><b>'.$myrowextract['producto'].'</b></td>
								<td>
									<input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qty_' . $i . '" size=6 maxlength=6 value="'.$myrowextract['quantity'].'">
								</td>
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
						<input type="submit" name="QuickEntryOrder" value="' . _('Ordenar') . '">
						<input type=hidden name="lineaxs" value='.$totallineas.'>
						<input type="hidden" name="QuickEntryOrden" value="'.$i.'">
						<input type=submit name="Search" value="'. _('Buscar').'">
						<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '">
					</div>
					
					</td>
				</tr>
				</table></form>';	
			}
		$_SESSION['AccessOrder']=0;
		}
		
		
	}// fin de orden existente
	echo '</td></tr></table>';	
} /* FIN DEL ELSE ULTIMO */
    
/* FIN DE CLIENTE SELECCIONADO, COMIENZA AQUI LA SELECCION DE PRODUCTOS Y ORDEN DE VENTA */
/*****************************************************************************************/

if (isset($_GET['NewOrder']) and $_GET['NewOrder']!='') {
	echo '<script  type="text/javascript">defaultControl(document.SelectCustomer.CustCode);</script>';	
}
include('includes/footer.inc');
?>
