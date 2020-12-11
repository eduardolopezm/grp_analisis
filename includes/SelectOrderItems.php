<?php
/* $Revision: 4.2 $ 
   ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 01-DIC-2009 
 CAMBIOS:
   1. CAMBIO DE MENSAJES A ESPAÑOL
   2. CAMBIO DE FACTURACION POR ALMACEN

 FECHA DE MODIFICACION: 09-DIC-2009 
 CAMBIOS:
   1. CAMBIO BUSQUEDA DE PRODUCTOS X ALMACEN Y UNIDAD DE NEGOCIO
 FECHA DE MODIFICACION: 10-DIC-2009 -17-12-2009
 CAMBIOS:
   1. VALIDACION DE DESCUENTO 1,2 Y 3
   2. Entrada de productos rapida
   3. Paginacion
   4. Cambio de moneda
   5. cambio de tag
 FECHA DE MODIFICACION: 10-DIC-2009 -17-12-2009
 CAMBIOS:
   1. AGREGAR ANTICIPOS EN LA FACTURA 
 FECHA DE MODIFICACION: 22-DIC-2009 
 CAMBIOS:
   1. Busqueda de productos por lista de precio y tipo de moneda
   2. Agregar combo de lista de precio
   3. Cancelacion de pedidos
   4. Folio por unidad de negocio

FECHA DE MODIFICACION: 23-DIC-2009
CAMBIOS:
   1. Listado de produstos por lista de precio con iva
   2. Cambiar de cliente la cotizacion
   3. Coloreada por tipo de lista de precio
 FIN DE CAMBIOS
*/
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
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

if ($debuggaz==1)
	echo '$totallineas:'.$totallineas;


if (isset($_POST['ModifyOrderNumber'])) {
  $ordernumber = $_POST['ModifyOrderNumber'];
}elseif(isset($_SESSION['ExistingOrder'])){
	$ordernumber =  $_SESSION['ExistingOrder'];
}elseif(isset($_GET['ModifyOrderNumber'])){
	$ordernumber =  $_GET['ModifyOrderNumber'];
}else{
	$ordernumber =0;
}
//echo $ordernumber;
if (isset($ordernumber) and $ordernumber<>'') {
	$title = _('Modificar Pedido') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$title = _('Introducir Datos De Pedido');
}

//echo 'lineas:'.$_SESSION['Delete'].'<br>';
include('includes/header.inc');
include('includes/GetPrice.inc');
$funcion=1;
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
				custbranch.salesman,
				locations.taxprovinceid,
				custbranch.taxgroupid,
				salesorders.placa,
				salesorders.serie,
				salesorders.kilometraje,
				salesorders.tagref,
				salesorders.currcode,
				salesorders.quotation,
				salesorders.paytermsindicator
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
				AND salesorders.orderno = ' . $_GET['ModifyOrderNumber'];
	
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
		//echo "bhhjkh:".$_SESSION['quotation'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['placa']=$myrow['placa'];
		$_SESSION['serie']=$myrow['serie'];
		$_SESSION['kilometraje']=$myrow['kilometraje'];
		
		// OBTENER EL VALOR DE LA MONEDA PARA FINES DE FACTURACION
		$SQLCurrency="SELECT c.rate
			       FROM currencies c, debtorsmaster d
			       WHERE c.currabrev=d.currcode
			       AND d.debtorno='".$myrow['debtorno']."'";
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
					locstock.quantity as qohatloc,
					stockmaster.mbflag,				
					stockmaster.taxcatid,					
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					salesorderdetails.completed,
					locstock.loccode as fromstkloc1,
					locations.locationname as locationname1,
					salesorderdetails.quantity,
					salesorderdetails.warranty
				FROM salesorderdetails INNER JOIN stockmaster
					ON salesorderdetails.stkcode = stockmaster.stockid
					INNER JOIN locstock ON locstock.stockid = stockmaster.stockid,
					locations
				WHERE  locstock.loccode=locations.loccode
					AND locstock.loccode=salesorderdetails.fromstkloc
					AND salesorderdetails.orderno =" . $_GET['ModifyOrderNumber'] . "
				ORDER BY salesorderdetails.orderlineno";
				
		if ($debuggaz==1)
			echo '<br>LINES: '.$LineItemsSQL;
		
		$ErrMsg = _('Las partidas de la orden no se pueden recuperar, por que');
		$LineItemsResult = db_query($LineItemsSQL,$db,$ErrMsg);
		if (db_num_rows($LineItemsResult)>0) {
			while ($myrow=db_fetch_array($LineItemsResult)) {
				if ($myrow['completed']==0){
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
											$myrow['warranty']
										);
					/*Just populating with existing order - no DBUpdates */
					}
					
					if ($debuggaz==1)
						echo '<br>LINEAXX: '.$myrow['orderlineno'];
			
					$LastLineNo = $myrow['orderlineno'];
					//$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
					$_SESSION['Items'.$identifier]->GetTaxes($LastLineNo);
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
		} //end of checks on returned data set
		
	}
}
/*******************************************************************************************************************/				

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
	} else {
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
					custbranch.braddress3 as ciudad
				FROM custbranch, debtorsmaster
				WHERE custbranch.debtorno = debtorsmaster.debtorno
				and (custbranch.brname " . LIKE . " '%" . $SearchString . "%' or
				debtorsmaster.name " . LIKE . " '%" . $SearchString . "%') ";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}	
			$SQL .=	" AND custbranch.disabletrans=0 and
				(custbranch.area = '".$_POST['SalesArea']."' or '".$_POST['SalesArea']."'='0')
				ORDER BY custbranch.debtorno, custbranch.branchcode";
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
					custbranch.braddress3 as ciudad
				FROM custbranch, debtorsmaster
				WHERE custbranch.debtorno = debtorsmaster.debtorno
				and (custbranch.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
				OR custbranch.branchcode " . LIKE . " '%" . $_POST['CustCode'] . "%')";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			$SQL .=	' AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno';
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
					custbranch.braddress3 as ciudad
				FROM custbranch, debtorsmaster
				WHERE custbranch.debtorno = debtorsmaster.debtorno
				and custbranch.taxid " . LIKE . " '%" . $_POST['CustTaxid'] . "%'";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			$SQL .=	' AND custbranch.disabletrans=0
				ORDER BY custbranch.debtorno';
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
					custbranch.braddress3 as ciudad
				FROM custbranch, debtorsmaster
				WHERE custbranch.debtorno = debtorsmaster.debtorno and custbranch.contactname " . LIKE . " '%" . $_POST['CustContact'] . "%'";
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}			
			$SQL .=	" AND custbranch.disabletrans=0 and
				(custbranch.area = '".$_POST['SalesArea']."' or '".$_POST['SalesArea']."'='0')
				ORDER BY custbranch.debtorno";
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
					custbranch.braddress3 as ciudad
				FROM custbranch, debtorsmaster
				WHERE custbranch.debtorno = debtorsmaster.debtorno and custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'";				
			if ($_SESSION['SalesmanLogin']!=''){
				$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
			}
			$SQL .=	" AND custbranch.disabletrans=0 and
				(custbranch.area = '".$_POST['SalesArea']."' or '".$_POST['SalesArea']."'='0')
				ORDER BY custbranch.debtorno";
		}
		/* FIN DE GENERACION DEL SQL PARA LA BUSQUEDA DE CLIENTES */
		/**********************************************************************************/
		
		$ErrMsg = _('La busquedas en los registros de clientes solicitada no puede ser recuperada por');
		$result_CustSelect = DB_query($SQL,$db,$ErrMsg);
		if (DB_num_rows($result_CustSelect)==1){
			/* SI EL RESULTADO ARROJA SOLO UN CLIENTE, ENTONCES DIRECTAMENTE ASIGNA LA VARIABLE Select */
			$myrow=DB_fetch_array($result_CustSelect);
			$_POST['Select'] = $myrow['debtorno'] . ' - ' . $myrow['branchcode'];
		} elseif (DB_num_rows($result_CustSelect)==0){
			/* SI EL RESULTADO ARROJA NINGUN REGISTRO, ENTONCES DESPLEGAR LIGA PARA NUEVO CLIENTE*/
			prnMsg(_('No existen registros de Oficinas para el cliente que contengan los criterios de búsqueda') . ' - ' . _('intentelo de nuevo') . ' - ' . _('Nota: El Nombre de la sucursal puede ser diferente al Nombre del cliente'),'info');
		}
		
		/* MANTIENE VALORES DE RESULTADOS DE CLIENTES EN $result_CustSelect RECORDSET */
		
	} /*one of keywords or custcode was more than a zero length string */
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
	
	//echo "entra session cliente";
	
	/* EXTRAER EL CODIGO DE LA SUCURSAL DE LA VARIABLE Select, ES EL VALOR QUE SIGUE DEL GUION */
	$_SESSION['Items'.$identifier]->Branch = substr($_POST['Select'],strpos($_POST['Select'],' - ')+3);
	
	/* EXTRAER EL CODIGO DEL CLIENTE DE LA VARIABLE Select, ES EL VALOR QUE ESTA ANTES DEL GUION
	   Y ASIGNA EL RESULTADO A LA MISMA VARIABLE POST Select */
	$_POST['Select'] = substr($_POST['Select'],0,strpos($_POST['Select'],' - '));
	
	/* VERIFICA QUE LA CUENTA DEL CLIENTE NO ESTA BOLETINADA ! */
	$sql = "SELECT debtorsmaster.name,
			holdreasons.dissallowinvoices,
			debtorsmaster.salestype,
			salestypes.sales_type,
			debtorsmaster.currcode,
			debtorsmaster.customerpoline,
			paymentterms.terms,
			currencies.rate as currency_rate
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
	} else {
		prnMsg(_('Lo sentimos, su cuenta ha quedado suspendida por algun motivo, pongase en contacto con el personal de control de creditos.'),'warn');
		include('includes/footer.inc');
		exit;
	}
} /* FIN DE ASIGNACION DE DATOS A REGISTROS DEL CLIENTE */

/******************************************************************************************************/
/*********   INICIO DE DESPLIEGUE DE PANTALLA DE BUSQUEDA DE CLIENTES *********************************/

if ($_SESSION['RequireCustomerSelection'] ==1 OR !isset($_SESSION['Items'.$identifier]->DebtorNo) OR $_SESSION['Items'.$identifier]->DebtorNo=='' OR isset($_GET['ChangeCustomer'])) {
	
	/* SI REGRESAMOS A BUSQUEDA DE CLIENTE PARA CAMBIARLO NO INICIALICES MONEDA Y UNIDAD NEGOCIO */
	if (!isset($_GET['ChangeCustomer'])) {
		unset($_SESSION['CurrAbrev']);
		unset($_SESSION['Tagref']);
	}
	
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . 
	' ' . _('Captura una Pedido o Cotizacion') . ' : ' . _('Busqueda Cliente.') . '</p>';
	echo '<div class="page_help_text">' . _('Los Pedidos/Cotizaciones son en base a sucursal. Un cliente puede tener varias sucursales.') . '</div>';
	?>
	
	<form action="<?php echo $_SERVER['PHP_SELF'] . '?' .SID .'identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'] .'&Tagref='.$_SESSION['Tagref'];?>" name="SelectCustomer" method=post>
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
					<!--<a href="<? echo $rootpath ?>/SelectOrderItems.php?<? echo SID .'identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'] ?>&Select=<?php echo $_SESSION["ClaveClienteMostrador"]?>"><?php echo _('USAR CLIENTE MOSTRADOR...')?></a><br> -->
					<a href="<? echo $rootpath ?>/SelectOrderItems.php?<? echo SID .'identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'] ?>&Select=cMos_<?php echo intval($_SESSION["DefaultArea"]).' - cMos_'.intval($_SESSION["DefaultArea"])?>"><?php echo _('USAR CLIENTE MOSTRADOR...')?></a><br>
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
							<th>' . _('Nombre') . '</th>
							<th>' . _('Ciudad') . '</th>
							</tr>';
					echo $TableHeader;
					$j = 1;
					$k = 0; //row counter to determine background colour
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
							
							/* Se utiliza el valor del boton s - s para pasar parametros del cliente
							   y sucursal a siguiente pantalla, ojo al modificar */
							printf('<td class="peque"><input style="font-size:9px;height:25px;width:140px;" tabindex='.number_format($j+5).' type=submit name="Select" value="%s - %s"</td>
									<td class="peque">%s<br>%s</td>
									<td class="peque">%s<br>%s</td>
									</tr>',
									$myrow['debtorno'],
									$myrow['branchcode'],
									$myrow['name'],
									$tempBranchName,
									$myrow['ciudad'],
									$myrow['taxid']);
							$j++;
							if ($j == 11 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
								$j=1;
							}
							$RowIndex++;
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
		
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Pedido') . '" alt="">' . ' ';
		if ($_SESSION['Items'.$identifier]->Quotation==1){
			echo _('Cotizacion') . ' ';
		} else {
			echo _('Pedido de Venta') . ' ';
		}
		
		if (isset($ordernumber) and $ordernumber<>'') {
			echo 'No. ' . $ordernumber;	
		}	
		
		
		
		if (true) { //LIGA DE REGRESAR A BUSQUEDA DE CLIENTE
			/*************************************************************************************/
			/* TABLA DE LIGAS PARA MODIFICAR CLIENTE O REGRESAR A PANTALLA DE BUSQUEDA DE CLIENTE*/
			echo '<table border=0 width=100%>';
			echo '<tr><td>';
			echo '	<a href="' . $rootpath . '/SelectOrderItems.php?ChangeCustomer=Yes&ModifyOrderNumber='.$_SESSION['ExistingOrder'].'&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'">
					<img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Cliente') . '" alt=""> Cambiar Cliente para este pedido</a>';
			echo '  <BR><BR>';		
			echo '	<a href="' . $rootpath . '/Customers.php?from=selectorderitems&DebtorNo='.$_SESSION['Items'.$identifier]->DebtorNo .'&BranchCode='.$_SESSION['Items'.$identifier]->Branch. SID .'&identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'&placa='.$placa.'&serie='.$serie.'&kilometraje='.$kilometraje.'&anticipo='.$anticipo.'&ModifyOrderNumber='.$_SESSION['ExistingOrder'] . '">
					<img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' . _('Cliente') . '" alt=""> Modificar Datos De Este Cliente </a>';
			echo '</td></tr></table>';
			/*************************************************************************************/
		}
		
		/*************************************************************************************/
		/* TABLA DE DATOS DEL CLIENTE EN ENCABEZADO                                          */
		echo '<table border=0 width=100%>';
		echo '<tr><td width=40></td><td>';
		echo '<table border="1" CELLPADDING=0 CELLSPACING=0>';
			echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos Del Cliente') . '</td><tr>';
			echo '<tr><td>' . _('Cliente') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->DeliverTo . '</td><tr>';
			echo '<tr><td>' . _('Precio Lista') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->SalesTypeName . '</td><tr>';
			echo '<tr><td>' . _('Terminos') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->PaymentTerms . '</td><tr>';
			echo '<tr><td>' . _('Credito Disp') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->CreditAvailable . '</td><tr>';
		echo '</table>';
		echo '</td><td valign=top>';
			echo '<table border="1" width=100% CELLPADDING=0 CELLSPACING=0 >';
			echo '<tr class="EvenTableRows"><td width=100% colspan=2 align="center" >' . _('Datos De la Oficina del Cliente') . '</td><tr>';
			echo '<tr><td>' . _('RFC') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->CostumerRFC ._('&nbsp;'). '</td><tr>';
			echo '<tr><td>' . _('Direccion') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->DelAdd1.' '.$_SESSION['Items'.$identifier]->DelAdd2 . '</td><tr>';
			echo '<tr><td>' . _('Ciudad') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->DelAdd3 ._('&nbsp;'). '</td><tr>';
			echo '<tr><td>' . _('Contacto') . ':</td><td style=font-weight:normal;>' . $_SESSION['Items'.$identifier]->CostumerContact . _('&nbsp;').'</td><tr>';
			echo '</table>';
		echo '</td><td width=40></td></tr>';
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
					$DiscountPercentage = $_POST['Discount_' . $OrderLine->LineNumber];
					$DiscountPercentage1 = $_POST['Discount1_' . $OrderLine->LineNumber];
					$DiscountPercentage2 = $_POST['Discount2_' . $OrderLine->LineNumber];
					
					
					// En caso de que quieran descontar el iva al precio
					$checkwithtax=$_POST['Itemwithtax_' . $OrderLine->LineNumber];
					if ($checkwithtax==true){
						
						$Price=$Price * (1-($DiscountPercentage/100));
						echo "<br>desc1: ".$Price;
						$Price=$Price * (1-($DiscountPercentage1/100));
						echo "<br>desc2: ".$Price;
						$Price=$Price * (1-($DiscountPercentage2/100));
						echo "<br>desc3: ".$Price;
						foreach ($OrderLine->Taxes AS $Tax) {
							if (empty($TaxTotals[$Tax->TaxAuthID])) {
								$TaxTotals[$Tax->TaxAuthID]=0;
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
						echo "<br>iva: ".$Price;
						$DiscountPercentage=0;
						$DiscountPercentage1=0;
						$DiscountPercentage2=0;
					}
					
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
												 $warranty
											);
						$_SESSION['Items'.$identifier]->GetTaxes($OrderLine->LineNumber);
					}
				} //page not called from itself - POST variables not set
				
			} /* FIN DE LOOP PARA CADA LINEA DE PRODUCTO EN LA ORDEN DE VENTA */
		} /* FIN IF SI EXISTEN YA PRODUCTOS */
	} /* FIN DE ACTUALIZACION DE PRODUCTOS O ALTA DE LINEA DE LA ORDEN */
	    
	/* SI SE SELECCIONO LA OPCION DE CONFIRMAR EL PEDIDO, ENTRA AQUI Y REDIRECCIONA A PAGINA DE DeliveryDetails.php */
	if (isset($_POST['DeliveryDetails'])){
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/DeliveryDetails.php?' . SID .'identifier='.$identifier . '&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref'].'&quotation='.$_SESSION['quotation'].'">';
		prnMsg(_('Deberias de ser redireccionado automaticamente a la pagina de confirmacion') . '. ' . _('si esto no sucede') . ' (' . _('si el explorador no soporta META REFRESS') . ') ' .
           '<a href="' . $rootpath . '/DeliveryDetails.php?' . SID .'identifier='.$identifier . '">' . _('haz click aqui') . '</a> ' . _('para continuar'), 'info');
	   	exit;
	}
	/*******************************************************************************************************/

	// INICIO DE AGREGAR A CLASE LOS PRODUCTOS POR SUCURSAL
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '"& name="SelectParts" method=post>';
	echo '<div class="centre"><hr></div>';
	if (isset($_POST['order_items']) or isset($_POST['QuickEntry'])){
	/*	if (isset($_POST['lineaxs'])){
			$lineax=$_POST['lineaxs']-1;
			if ($lineax==0){
				$lineax=-1;
			}
			
		} elseif(isset($_GET['lineaxs'])) {
			$lineax=$_GET['lineaxs']-1;
			if ($lineax==0){
				$lineax=-1;
			}
		}else {
			$lineax=-1;
		}
		if (isset($_SESSION['Delete'])){
			$lineax=$_SESSION['Delete']-1;
			unset($_SESSION['Delete']);
		}
	*/
	
	}
	if (isset($_SESSION['Items'.$identifier]->LineCounter)){
		$lineax=($_SESSION['Items'.$identifier]->LineCounter-1);
	}
	//echo "linea:".$lineax;
	if (isset($NewItem_array) && isset($_POST['order_items'])) {
		
		foreach($NewItem_array as $NewItem => $NewItemQty) {	
			if($NewItemQty > 0){
				$lineax=$lineax+1;
				$NewItemS=strstr($NewItem,"|");
				$NewItemS=str_replace("|","",$NewItemS);
				$NewItem=substr($NewItem,0,strpos($NewItem,"|"));
				//echo "producto".$NewItem."<br>sucursal:".$NewItem."<br>";
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
							include('includes/SelectOrderItemsProducts_IntoCart.inc');
				}
					} else { /*Its not a kit set item*/
						
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = $lineax;
						$loccalmacen=$NewItemS;
						include('includes/SelectOrderItemsProducts_IntoCart.inc');
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
			$i++;
			$lineax=$lineax+1;
			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = strtoupper($_POST[$QuickEntryCode]);
			}
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = $_POST[$QuickEntryQty];
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
						include('includes/SelectOrderItemsProducts_IntoCart.inc');
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else {
				
					// ENVIO INFORMACION DEL ALMACEN
					$SQL="SELECT l.loccode,quantity
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
					}
					
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					$NewPOLine = $lineax ;
					include('includes/SelectOrderItemsProducts_IntoCart.inc');
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
		//echo '<div class="page_help_text">' . _('Quantity (required) - Enter the number of units ordered.  Price (required) - Enter the unit price.  Discount (optional) - Enter a percentage discount.  GP% (optional) - Enter a percentage Gross Profit (GP) to add to the unit cost.  Due Date (optional) - Enter a date for delivery.') . '</div><br>'
	
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
			      <th>' . _('IVA') . '</th>
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
				echo '<td class="number"  ><input class="number" onKeyPress="return restrictToNumbers(this, event)" tabindex=2 type=tect name="Quantity_' . $OrderLine->LineNumber . '" size=2 maxlength=6 value=' . $OrderLine->Quantity . '>';
				if ($QtyRemain != $QtyOrdered){
					echo '<br>'.$OrderLine->QtyInv.' de '.$OrderLine->Quantity.' facturado';
				}
				echo '</td>
				<td class="number">' . $OrderLine->QOHatLoc . '</td>
				<td>' . $OrderLine->Units . '</td>';
				$checkwithtax='';
				if (in_array(2,$_SESSION['AllowedPageSecurityTokens'])){
					echo '<td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Price_' . $OrderLine->LineNumber . '" size=9 maxlength=16 value=' . $OrderLine->Price . '>
						  <input type="checkbox" name="Itemwithtax_' . $OrderLine->LineNumber .'" '.$checkwithtax .'>
					</td>
					      <td nowrap><input class="number" onKeyPress="return restrictToNumbers(this, event)"  type=text name="Discount_' . $OrderLine->LineNumber . '" size=2 maxlength=3 value=' . ($OrderLine->DiscountPercent * 100) . '>%</td>
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
				echo '<a href="' . $_SERVER['PHP_SELF'] . '?' . SID .'identifier='.$identifier . '&Delete=' . $OrderLine->LineNumber . '&lineaxs='.$_SESSION['Items'.$identifier]->LineCounter. '&ModifyOrderNumber='.$ordernumber.'" onclick="return confirm(\'' . _('¿Esta seguro de quitar este producto del pedido?') . '\');">' . $RemTxt . '</a></td>';
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
					echo '<td>' . _('Texto') . ':</td><td colspan=5><textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="45" rows="2">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea></td></tr>';
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
		$ColSpanNumber = 11;
		$totalcuenta=$_SESSION['Items'.$identifier]->total;
		$totalcuenta=$totalcuenta+$TaxTotal;
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
	echo '<br><div class="centre"><input type=submit name="Recalculate" Value="' . _('Recalcular') . '">
		<input type=submit name="DeliveryDetails" style="font-weight:bold;" value="' . _('CONFIRMAR PEDIDO') . '">
		</div><hr>';
	
	} # end of if lines
	// CAMPOS DE TAG Y MONEDA PARA REALIZAAR LA FACTURACION
	echo '<table >';
	if ($_SESSION['CurrAbrev']==''){
		echo '<tr>';
		if ($_SESSION['Tagref']==''){
			echo '<td><b>' . _('XUnidad de Negocio') . ':</b>&nbsp;&nbsp; ';
			// consulta las unidades de negocio
			
			/* CAMBIO PARA SERVILLANTAS */
			/*USE CONDICION, EN CASO DE SER SERVILLANTAS, QUE SOLO PUEDA FACTURAR DEL TAG
			QUE LE CORRESPONDE, DE OTRA MANERA EN CASO DE PRUEBAS PUEDE FACTURAR DE CUALQUIER
			TAG DE ACUERO A SU AREA*/
			if ($_SERVER['SERVER_NAME'] == "erp.servillantas.com"){
				///*
				$SQL=" SELECT *
				       FROM tags
				       WHERE tagref='".$_SESSION['DefaultUnidad']."'";
				//*/     
			}else{
				$SQL=" SELECT *
			       FROM tags
			       WHERE areacode='".$_SESSION['DefaultArea']."'";
			}
			
						  
			
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
			       FROM currencies ";
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
		
		echo '<td> <b>' . _('Lista Precio') . ':</b>&nbsp;&nbsp; ';
		// Consulta las listas de precio
		$SQL =" SELECT distinct salestypes.typeabbrev,salestypes.sales_type
			FROM salestypes, sec_pricelist
			WHERE sec_pricelist.pricelist = salestypes.typeabbrev
				AND sec_pricelist.userid='" . $_SESSION['UserID'] . "'
				OR salestypes.typeabbrev='".$_SESSION['Items'.$identifier]->DefaultSalesType."'";
		
		$resultunitB = DB_query($SQL,$db);
		if (DB_num_rows($resultunitB)==1){
			$myrowCurrency=DB_fetch_array($resultunitB);
			$SalesType = $myrowCurrency['typeabbrev'];
			echo '<input type="hidden" name="SalesType" value="'.$SalesType.'">';
			echo $myrowCurrency['sales_type'];
		}else {
			echo '<select name="SalesType">';
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
	}
	echo '</table>';


	/**************************/
	/* BUSQUEDA DE PRODUCTOS  */
	echo '<table><tr><td valign=top >';
	if (isset($_POST['PartSearch']) && $_POST['PartSearch']!='' || !isset($_POST['QuickEntry'])){
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?identifier='.$identifier .'&CurrAbrev='.$_SESSION['CurrAbrev'].'&Tagref='.$_SESSION['Tagref']. SID . '& name="SelectParts" method=post>';
		echo '<input type="hidden" name="PartSearch" value="' .  _('Yes Please') . '">
		<input type=hidden name="lineaxs" value='.$totallineas.'>
		';		
		$SQL="SELECT categoryid,
			     categorydescription
		      FROM stockcategory
		      WHERE stocktype='F' OR stocktype='D'
		      ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);
		echo '<table>';
		
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
				
			$ssql = 'SELECT o.orderno, itemcode,itemdescription,locationname,quantityord,quantityrecd,margenaut, loccode,unitprice, unitprice* (1+(margenaut/100))';
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
					     echo '<td nowrap style="text-align:center;"><a href="GoodsReceived.php?'. SID .'&PONumber='.$myrowdetails[0].'&TieToOrderNumber='.$_SESSION['ExistingOrder'].'"><font size=1>' . $myrowdetails[0] . '</font></a></td>
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
							     echo '<a href="SelectOrderItems.php?' . SID .'&ModifyOrderNumber='.$_SESSION['ExistingOrder'].'&lineaxs='.($totallineas).'&agregarproducto=yes&itm'.$myrowdetails[1].'|'.$myrowdetails[7].'='.$myrowdetails[5].'&precioproductoagregado='.$myrowdetails[9].' "><font size=1>agregar a pedido</font></a>';
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
					echo '<table border=1>
						<tr>';
						echo '<th>' . _('Codigo') . '</th>
						      <th>' . _('Cantidad') . '</th>
						</tr>';
				     for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){
					echo '<tr class="OddTableRow">';
					echo '		<td><input type="text" name="part_' . $i . '" size=21 maxlength=20></td>
							<td><input class="number" onKeyPress="return restrictToNumbers(this, event)" type="text" name="qty_' . $i . '" size=6 maxlength=6></td>
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
} /* FIN DEL ELSE ULTIMO */
    
/* FIN DE CLIENTE SELECCIONADO, COMIENZA AQUI LA SELECCION DE PRODUCTOS Y ORDEN DE VENTA */
/*****************************************************************************************/

if (isset($_GET['NewOrder']) and $_GET['NewOrder']!='') {
	echo '<script  type="text/javascript">defaultControl(document.SelectCustomer.CustCode);</script>';	
}
include('includes/footer.inc');
?>
