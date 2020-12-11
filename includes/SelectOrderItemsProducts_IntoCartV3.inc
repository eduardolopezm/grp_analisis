<?php
/*
SP 07/05/2013
Archivo igual al SelectOrderItemsProducts_IntoCartV2.inc
Se agrega codigo para insertar nuevos campos en el metodo add_to_cart
*/

include_once('ManufacturedStockUnitcost.inc');

$AlreadyOnThisOrder =0;

if (count($_SESSION['Items'.$identifier]->LineItems)>0 AND $_SESSION['SO_AllowSameItemMultipleTimes']==0){

	foreach ($_SESSION['Items'.$identifier]->LineItems AS $OrderItem) {

	/* do a loop round the items on the order to see that the item
	is not already on this order */

		if (strcasecmp($OrderItem->StockID, $NewItem)==0) {
			$AlreadyOnThisOrder = 1;
			prnMsg(_('The item') . ' ' . $NewItem . ' ' . _('is already on this order the system is set up to prevent the same item being on the order more than once. However you can change the quantity ordered of the existing line if necessary'));
       		}
	} /* end of the foreach loop to look for preexisting items of the same code */
}
if ($AlreadyOnThisOrder!=1){

    $sql = "SELECT stockmaster.description,
			stockmaster.stockid,
			stockmaster.units,
			stockmaster.volume,
			stockmaster.kgs,
			(materialcost+labourcost+overheadcost) AS standardcost,
			locstock.quantity,
			stockmaster.mbflag,
			stockmaster.discountcategory,
			stockmaster.decimalplaces,
			stockmaster.discontinued,
			stockmaster.serialised,
			stockmaster.eoq,
			stockmaster.nextserialno,
			stockmaster.taxcatid,
			locations.locationname,
			stockmaster.controlled
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid,
			locations
		WHERE locstock.loccode=locations.loccode
		      AND locstock.loccode='" . $loccalmacen . "'
			AND stockmaster.stockid = '". $NewItem . "'";
			
			
	   $sql = "SELECT stockmaster.description,
			stockmaster.stockid,
			stockmaster.units,
			stockmaster.volume,
			stockmaster.kgs,
			(materialcost+labourcost+overheadcost) AS standardcost,
			(locstock.quantity-locstock.ontransit-locstock.qtybysend) as quantity,
			stockmaster.mbflag,
			stockmaster.discountcategory,
			stockmaster.decimalplaces,
			stockmaster.discontinued,
			stockmaster.serialised,
			stockmaster.eoq,
			stockmaster.nextserialno,
			stockmaster.taxcatid,
			locations.locationname,
			stockmaster.controlled,
			stockcategory.redinvoice as red,
			stockcategory.disabledprice,
			stockmaster.taxcatidret,
			stockmaster.manufacturer
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid,
			locations, stockcategory
		WHERE locstock.loccode=locations.loccode
		      AND stockcategory.categoryid=stockmaster.categoryid
		      AND locstock.loccode='" . $loccalmacen . "'";
		      //$sql=$sql." AND stockmaster.stockid = '". $NewItem . "'";
		if ($_SESSION['SearchBarcode']==0){
			$sql=$sql." AND stockmaster.stockid = '". $NewItem . "'";
		}elseif(!isset($_POST['QuickEntry'])){
			$sql=$sql." AND (stockmaster.stockid = '". $NewItem . "' /*or stockmaster.stockid = '". $NewItem . "'*/)";
		}else{
			$sql=$sql." AND (stockmaster.barcode = '". $NewItem . "')";
			
		}
		if($_SESSION['UserID']=='admin'){
			//echo '<br><pre>'.$sql;
		}
    $ErrMsg = _('El Codigo') . ' ' . $NewItem . ' ' . _('no se ha encontrado por que');
    $DbgMsg = _('El SQL utilizado para recuperar los detalles de los precios');
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    if (DB_num_rows($result1)==0){
		prnMsg(_('El codigo de producto') . ' ' . $NewItem  . ' '  . _('No se puede encontrar en la base de datos') . ' - ' . _('no ha sido añadido a la orden'),'warn',_('El codigo no existe'));
    } elseif ($myItemRow = DB_fetch_array($result1)){
	$NewItem=$myItemRow['stockid'];
	
	if ($_SESSION['TypeCostStock']==1){
		$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$NewItem, $db);
	}else{
		$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
		$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$NewItem, $db);
	}
    	if ($myItemRow['discontinued']==1){
			prnMsg(_('El Codigo') . ' ' . $NewItem . ' ' . _('No se puede agregar a la orden, ya que se ha marcado como descontinuado'),'error',_('Codigo Descontinuado'));
		}elseif (($_SESSION['AllowSalesOfZeroCostItems'] == false
			AND $EstimatedAvgCost>0
			AND ($myItemRow['mbflag']=='B'
			OR $myItemRow['mbflag']=='M'))
		OR ($_SESSION['AllowSalesOfZeroCostItems'] == false
			AND ($myItemRow['mbflag']=='A'
			OR $myItemRow['mbflag']=='D'
			OR $myItemRow['mbflag']=='K'))
		OR $_SESSION['AllowSalesOfZeroCostItems']==true) {
		/*these checks above ensure that the item has a cost if the
		config.php variable AllowSalesOfZeroCostItems is set to false */
		   	if ($_SESSION['ExistingOrder']!=0){
				$UpdateDB = 'Yes';
				$result = DB_query('SELECT MAX(orderlineno) AS newlineno FROM  salesorderdetails WHERE orderno=' . $_SESSION['ExistingOrder'],$db);
				$MaxNumRow = DB_fetch_row($result);
				if ($MaxNumRow[0] != "" && $MaxNumRow[0] > 0) {
					$NewLineNo = $MaxNumRow[0]+1;
				} else {
					$NewLineNo = $_SESSION['Items'.$identifier]->LineCounter; /* AGREGOdesarrolloPARA QUE CUANDO BORRO TODOS TOME EL CONTADOR MAXIMO*/
				}
				
				if ($debuggaz==1)
					echo '<BR>Esta es la nueva linea:'.$NewLineNo;
				
			} else {
				$UpdateDB = 'No';
				$NewLineNo = -1; /* this is ok b/c CartClass will change to the correct line no */
			}

			if (isset($StockItem) and $myItemRow['discountcategory'] != "" ){
				$DiscCatsDone[$Counter]=$StockItem->DiscCat;
				$QuantityOfDiscCat =0;
				$result = DB_query("SELECT MAX(discountrate) AS discount
										FROM discountmatrix
										WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
										AND discountcategory ='" . $myrow['discountcategory'] . "'
										AND quantitybreak <" . $NewItemQty,$db);
				$DiscCatRow = DB_fetch_row($result);
				if ($DiscCatRow[0] != "" && $DiscCatRow[0] > 0) {
					$Discount = $DiscCatRow[0];
				} else {
					$Discount = 0;
				}
			} elseif (!isset($Discount)){
				$Discount = 0;
			}
			$qohsql = "SELECT loccode, locationname, areacode 
					   FROM locations JOIN tags ON locations.tagref = tags.tagref
					   WHERE   loccode = '" . $loccalmacen . "'";
			$qohresult =  DB_query($qohsql,$db);
			$qohrow = DB_fetch_row($qohresult);
			$qoh =  $qohrow[1];
			$codigobranch=$qohrow[0];
			$codigoarea=$qohrow[2];

			/* LO AGREGUE PARA PODER ASIGNAR PRECIO DESDE BOTONES RAPIDOS */
			if (isset($NewItemPrice) and $NewItemPrice != 0) {
				$Price = $NewItemPrice;
				
			} else {
				/*$Price = GetPriceWNoTAX($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->DefaultSalesType,$_SESSION['CurrAbrev'], $codigoarea, $db);
				//$Pricey = GetPriceWTAX($stockid, $_SESSION['Items'.$identifier]->DebtorNo,$listapreciox,$_SESSION['CurrAbrev'], $codigoarea, $db);
				$separa2 = explode('|',$Price);	
				$bgcolorlista2 = $separa2[1];
				$Price = $separa2[0];*/
				if($_SESSION['SalesChangePrices']==0){
					$Price = GetPriceWNoTAX($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->DefaultSalesType,$_SESSION['CurrAbrev'], $codigoarea, $db);
					
					$separa2 = explode('|',$Price);
					$Price2 = $separa2[0];
					
					if($Price2 == 0 and $_SESSION['DefaultCurrencySale']!=$_SESSION['CurrAbrev']){
						$Price = GetPriceWNoTAX($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->DefaultSalesType,$_SESSION['DefaultCurrencySale'], $codigoarea, $db);
					//echo 'entra uno';
					}
					
					
					
				}else{
					//extrae el precio de lista que debe de llevar
					$SQLClient="SELECT typeid FROM debtorsmaster WHERE debtorno='".$_SESSION['Items'.$identifier]->DebtorNo."'";
					$result_typeclient = DB_query($SQLClient,$db,$ErrMsg,$DbgMsg,true);
					if (DB_num_rows($result_typeclient)==1) {
						$myrowtype = DB_fetch_array($result_typeclient);
						$tipoClient=$myrowtype['typeid'];
					}
					$listprice=GetListPrice($NewItem,$tipoClient,$NewItemQty,$_SESSION['Items'.$identifier]->DefaultSalesType,$db);
					//echo '<br>tipo lista:'.$NewItem.'<br>'.$tipoClient.'<br>'.$NewItemQty;
					//extrae precio en base a nueva lista de precio
					$Price = GetPriceWNoTAX($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$listprice,$_SESSION['CurrAbrev'], $codigoarea, $db);
					$separa2 = explode('|',$Price);
					$bgcolorlista2 = $separa2[1];
					$Price2 = $separa2[0];
			
					
					if($Price2==0 and $_SESSION['DefaultCurrencySale']!=$_SESSION['CurrAbrev']){
						$Price = GetPriceWNoTAX($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$listprice,$_SESSION['DefaultCurrencySale'], $codigoarea, $db);
						//echo 'entra dos';
						
					}
				}
				//$Pricey = GetPriceWTAX($stockid, $_SESSION['Items'.$identifier]->DebtorNo,$listapreciox,$_SESSION['CurrAbrev'], $codigoarea, $db);
				$separa2 = explode('|',$Price);	
				$bgcolorlista2 = $separa2[1];
				if ($Price2==0 and $_SESSION['CurrAbrev']!=$_SESSION['DefaultCurrencySale']){
					//echo '<br>entraaaa';
					$fechaemisionpago=date('Y').'-'.date('m').'-'.date('d');
					$ratepago=GetCurrencyRateByDate($fechaemisionpago,$_SESSION['DefaultCurrencySale'],$db);
					//echo 'entraaaaaa'.$ratepago.' aca:'.$separa2[0];
					$separa2[0]=$separa2[0]/$ratepago;
					
					$separa2[0]=number_format($separa2[0],4,'.','');
				}
				$Price = $separa2[0];
				
			}
			
			
			if ($Price==0)
			{	if ($precioproductoagregado!=''){
					$Price=$precioproductoagregado;
				}
			}
			
			if ($costoproductoagregado!='' and strlen($costoproductoagregado)>0){
					$costoproductoagregado=$costoproductoagregado;
				}else{
					$costoproductoagregado=0;
				}
				
			$sql="SELECT shrinkfactor as margin
				FROM stockmaster
				WHERE stockid='" .  $NewItem . "'";
			/* SI SE QUIERE PERMITIR FACTURAR EN NEGATIVO, DEFINA MARGEN NEGATIVO */
			$ErrMsg = _('No se recupero el margen negativo que permite la venta');
			$DbgMsg= _('El sql que fallo fue:');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);//DB_query($sql,$db);
			$DiscCatRow = DB_fetch_array($result);
			if ($DiscCatRow[0] != "" && $DiscCatRow[0] != 0) {
				$margin = $DiscCatRow[0];
			} else {
				$margin = 0;
			}
			
			/* ESTO SE HACIA ANTERIORMENTE DENTRO DE EL CARRITO DE COMPRAS */
			if ($Price<0) {
				if ($margin >= 0) {
					$Price = 0;
				}
			}
			
			//si producto es manufacturado poner precio unitario
			if($_SESSION['botonrappromorarsocial'] <> 1){
				if ($myItemRow['mbflag']=="M"){
					$v = round(calculate_cost($db,$NewItem,'',1,0),2);
					if ($v > 0)
						$Price = $v;
						
				}
			}

				
			
			
			if ($debuggaz==1) {
				echo 'Price:'.$Price.'<br>';
			}
			
			$WithinCreditLimit = true;

			if (!isset($_SESSION['WarnOnce']) and $_SESSION['Items'.$identifier]->SpecialInstructions) {
			  	prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
		       	$WarnOnce=1;
            }
			if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn
											and 2 for prohibit sales */
				$_SESSION['Items'.$identifier]->CreditAvailable -= round(($NewItemQty * $Price * (1- $Discount)),2);

				if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					if (isset($_SESSION['WarnOnce']) and $_SESSION['WarnOnce'] < 2)
					prnMsg(_('El limite de credito esta siendo excedido, verifique ...'),'warn');
					$_SESSION['WarnOnce']=2;
				} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('No se pueden agregar mas productos a este pedido la cuenta de cliente se encuentra actualmente en o sobre su limite de credito'),'warn');
					$WithinCreditLimit = false;
				}
			}
			if ($Discount == 0) {
				$Discount = $NewItemDesc1;
				
				
			}
			$DiscountPercentage1=$NewItemDesc2;
			$DiscountPercentage2=$NewItemDesc3;
			if(strlen($servicestatus)==0){
				$servicestatus=0;
			}
			$NewItem=$myItemRow['stockid'];
			if ($_SESSION['AplicaDevolucion']==1){
				// verifica el porcetaje de devolucion
				$percentdevolucion=TraePercentDevXDebtorno($_SESSION['Items'.$identifier]->DebtorNo,$NewItem,$myItemRow['manufacturer'],$_SESSION['Items'.$identifier]->DefaultSalesType,$db);
				//echo $percentdevolucion;
				$separa = explode('|',$percentdevolucion);
				$Devolucion = $separa[0]/100;
				$Discount=$separa[1]/100;
				$NewItemDesc1=$Discount;
				$totalsale=$separa[2];
			}
			if($_SESSION['SalesXDiscount']==0){
				$Discount = $NewItemDesc1;
			}else{
				if($_SESSION['SalesXDiscount']==1){
					$Discountxsales = GetListPricexDiscounts($NewItem, $db);
				}else{
					$Discountxsales = GetListPricexDiscountsMin($NewItem,$_SESSION['SalesType'], $db);
					//echo "entra aqui".$Discountxsales;
					//$Discountxsales=0;
				}
				if($Discountxsales>$Discount){
					$Discount=$Discountxsales;
				}
			}
			
			$demandQtyQuotations = $_SESSION['DemandQtyQuotations'];
			if(empty($demandQtyQuotations)) {
				$demandQtyQuotations = "0"; // Por defecto pedidos cerrados
			}
			
			  $sqlLL = "SELECT ifnull(sum(ifnull(salesorderdetails.quantity-salesorderdetails.qtyinvoiced,0)),0) AS dem
					FROM salesorderdetails,
					     salesorders
					WHERE salesorders.orderno = salesorderdetails.orderno AND
					salesorderdetails.fromstkloc='" . $loccalmacen . "' AND
					salesorderdetails.completed=0 AND
					salesorders.quotation in ($demandQtyQuotations) ";
					if($_SESSION['ExistingOrder']>0){
						$sqlLL=$sqlLL.	" AND salesorders.orderno<>".$_SESSION['ExistingOrder'];
					}
					
					$sqlLL=$sqlLL." AND
					salesorderdetails.stkcode='" . $NewItem . "'";
					//echo '<pre>'.$sqlLL;
			if($_SESSION['UserID'] == 'admin') {
				//echo "<pre>".$sqlLL;
			}
			$ErrMsg = _('The demand for this product from') . ' ' .  $OrderLine->AlmacenStock . ' ' . _('cannot be retrieved because');
			$DemandResult = DB_query($sqlLL,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($DemandResult)==1){
			 $DemandRow = DB_fetch_row($DemandResult);
			 $DemandQtySales =  $DemandRow[0];
			 $ordenescerradas=true;
			} else {
			 $DemandQtySales =0;
			 
			}
			if ($WithinCreditLimit ==true){
				
				if ($debuggaz==1)
					echo '<BR>AGREGA PRODUCTO...DESC('.$Discount.')';
				
				
				//traer informacion de descuentos del cliente
				$sql = "Select descclientecomercial,descclienteop,discount 
						FROM custbranch , debtorsmaster
						WHERE debtorsmaster.debtorno = custbranch.debtorno
						and custbranch.debtorno = '".$_SESSION['Items'.$identifier]->DebtorNo."'
						and custbranch.branchcode = '".$_SESSION['Items'.$identifier]->Branch."'		
						"; 
				$rscomercial = DB_query($sql,$db);
				$rowcomercial = DB_fetch_array($rscomercial);
				$deccomercial = $rowcomercial['descclientecomercial'];
				$descCliente2 = $rowcomercial['descclienteop'];
				$descCliente1 = $rowcomercial['discount'];
				
				if ($descCliente1=="")
					$descCliente1 = 0;
				
				if ($deccomercial=="")
					$deccomercial = 0;
				
				if ($descCliente2=="")
					$descCliente2 = 0;
				
				$deccomercial = $deccomercial/100;
				$descCliente2 = $descCliente2/100;
				
				//buscar info de si decuento de PL y Comercial aplican o no sobre precio directo en categoria de inventario
				$sql = "Select discountInPriceListOnPrice,discountInComercialOnPrice 
						FROM stockcategory
						 INNER JOIN stockmaster
						 ON stockcategory.categoryid = stockmaster.categoryid
						WHERE stockmaster.stockid = '$NewItem'";
				$rsdisc = DB_query($sql,$db);
				$directpriceInComercial=0;
				$directpriceInPriceList=0;
				
				if ($regdisc = DB_fetch_array($rsdisc)){
					$directpriceInComercial=$regdisc['discountInComercialOnPrice'];
					$directpriceInPriceList=$regdisc['discountInPriceListOnPrice'];
				}
				
				$listaPrecio = $_SESSION['SalesType'];
				if ($_SESSION['Items'.$identifier]->DefaultSalesType!="")
					$listaPrecio = $_SESSION['Items'.$identifier]->DefaultSalesType;
				
				$qry="Select * FROM prices
							WHERE typeabbrev = '".$listaPrecio."'
							and stockid = '".$NewItem."'
			       		    and currabrev = '".$_SESSION['Items'.$identifier]->DefaultCurrency."'
							";
					
				$rsdesc = DB_query($qry,$db);
				$reg =DB_fetch_array($rsdesc);
				$pricelistdesc = $reg['percentdesc'];
				if ($pricelistdesc=="")
					$pricelistdesc=0;
				
				//buscar en salesxdiscounts pues tiene preferencia sobre prices
				$qry = "Select discountpercentmax from salesxdiscounts
							where current_date between effectiveafter and effectiveto
							and stockid = '".$NewItem."'
							and pricelistdis = '".$listaPrecio."'";
				$rs = DB_query($qry,$db);
				if (DB_num_rows($rs) > 0){
					$reg =DB_fetch_array($rs);
					if ($reg['discountpercentmax'] > 0)
						$pricelistdesc=$reg['discountpercentmax']/100;
				
				}
				
				
				if ($directpriceInPriceList==1){
					$Price -= ($Price*$pricelistdesc);
						
				}
				
				if ($directpriceInComercial==1){
					$Price -= ($Price*$deccomercial);
						
				}
				
				
				//es promo..
				$readOnlyValues=0;
				if ($_GET['espromo']==1 ){
					//"&identifier=1367951420&PartSearch=Yes&CurrAbrev=USD&Tagref=4&StockCat=&StockLock=&Keywords=&StockCode=&part_1=15261308&qty_1=1&price_1=186.5&desc1_1=0.5&desc2_1=0&desc3_1=0&Stock_1=101&QuickEntry=15261308&lineaxs=1";
					$NewItem = $_GET['part_1'];
					$NewItemQry = $_GET['qty_1'];
					$Price = $_GET['price_1'];
					$Discount = $_GET['desc1_1'];
					$DiscountPercentage1 = $_GET['desc2_1'];
					$DiscountPercentage2 = $_GET['desc3_1'];
					
					if ($_SESSION['PromoReadOnly']==1)
						$readOnlyValues=1;
					
					
				} 
				$modified=0;
				
				
				
				if ($_POST['itemdesc'.$NewItem] > 0){
					$Discount = $_POST['itemdesc'.$NewItem]/100;
					//se valida que el usuario no de un descuento mayor al que tiene autorizado
					if ($Discount > $_SESSION['discount1']){
						$mensaje=_('El descuento de '). $Discount*100;
						$mensaje=$mensaje. _(' es mayor al que tiene el usuario  de ').($_SESSION['discount1']*100);
						$mensaje=$mensaje. _('; por lo tanto la partida se calculara con descuento de ').($_SESSION['discount1']*100);
						prnMsg($mensaje,'warn');
							
						$Discount = $_SESSION['discount1'];
						
						
					}
					
				}
				
				if ($_SESSION['Items'.$identifier]->add_to_cart ($NewItem,
							$NewItemQty,
							$myItemRow['description'],
							$Price,
							$Discount,
							$myItemRow['units'],
							$myItemRow['volume'],
							$myItemRow['kgs'],
							$myItemRow['quantity']-$DemandQtySales,
							$myItemRow['mbflag'],
							NULL, /*Actual Dispatch Date */
							$NewItemQtyInvoiced, /*Qty Invoiced */
							$myItemRow['discountcategory'],
							$myItemRow['controlled'], /*Controlled - dont care */
							$myItemRow['serialised'], /* need to know for autocreation wos */
							$myItemRow['decimalplaces'],
							'', /*Narrative - none yet */
							$UpdateDB,
							$NewLineNo,
							$myItemRow['taxcatid'],
							'',
							$NewItemDue,
							$NewPOLine,
							$EstimatedAvgCost,
							$myItemRow['eoq'],
							$myItemRow['nextserialno'],
							$loccalmacen,
							$myItemRow['locationname'],
							$DiscountPercentage1,
							$DiscountPercentage2,
							1, // campo de garantia.........
							0,
							$myItemRow['red'],
							// Facturar en rojo por categoria,
							$costoproductoagregado,
							$myItemRow['disabledprice'],
							$servicestatus,
							$Devolucion,
							$totalsale,
							1,
							$listaPrecio,
							'1',
							$deccomercial,
							$directpriceInComercial,
							$directpriceInPriceList,
							$readOnlyValues,
							$descCliente1,
							$descCliente2,
							$modified
					)==1){
					//echo $myItemRow['disabledprice'];
					//$this->LineCounter
					//echo $_SESSION['SalesType'];
					$lin=$_SESSION['Items'.$identifier]->LineCounter;
					
					if ($debuggaz==1)
						echo "<BR>NewLineNo:".$lin." linea:".$NewLineNo;
					
					if ($NewLineNo == -1)
						$NewLineNo = 0;
						
					$_SESSION['Items'.$identifier]->GetTaxes($NewLineNo);
						
					if ($myItemRow['taxcatidret']>0){
						$_SESSION['Items'.$identifier]->GetTaxesRet($NewLineNo);
					}	
				}else{
					
				}
			}
			
         } else {
			prnMsg(_('El codigo de producto') . ' ' . $NewItem . ' ' . _('no tiene un costo y de entrada de pedidos esta configurado para prohibir la venta de productos con costo cero'),'warn');
	     }
	}
} /* end of if not already on the order */
    
?>
