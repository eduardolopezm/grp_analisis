<?php
/*
$Revision: 4.1 $ 
MODIFICADO POR: desarrollo
FECHA: 12/ENE/2010
CAMBIOS:
	1.- AGREGUE EN DETALLE DE NOTAS DE CREDITO
FIN DE CAMBIOS
*/
$AlreadyOnThisOrder =0;
if (count($_SESSION['Items'.$identifier]->LineItems)>0 AND $_SESSION['SO_AllowSameItemMultipleTimes']==0){
	foreach ($_SESSION['Items'.$identifier]->LineItems AS $OrderItem) {
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
			stockmaster.controlled,
			stockmaster.mbflag,
			stockmaster.discountcategory,
			stockmaster.decimalplaces,
			stockmaster.discontinued,
			stockmaster.serialised,
			stockmaster.eoq,
			stockmaster.nextserialno,
			stockmaster.taxcatid,
			locations.locationname
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid,
			locations
		WHERE locstock.loccode=locations.loccode
		      AND locstock.loccode='" . $loccalmacen . "'
			AND stockmaster.stockid = '". $NewItem . "'";
    if($_SESSION['UserID'] == "admin"){
    	echo '<pre>'.$LineItemsSQL;
    }
    $ErrMsg = _('El Codigo') . ' ' . $NewItem . ' ' . _('no se ha encontrado por que');
    $DbgMsg = _('El SQL utilizado para recuperar los detalles de los precios');
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    if (DB_num_rows($result1)==0){
		prnMsg(_('El codigo de producto') . ' ' . $NewItem  . ' '  . _('No se puede encontrar en la base de datos') . ' - ' . _('no ha sido a�adido a la orden'),'warn',_('El codigo no existe'));
    } elseif ($myItemRow = DB_fetch_array($result1)){
	if ($_SESSION['TypeCostStock']==1){
		$EstimatedAvgCost=ExtractAvgCostXtag($_SESSION['Tagref'],$NewItem, $db);
	}else{
		$legalid=ExtractLegalid($_SESSION['Tagref'],$db);
		$EstimatedAvgCost=ExtractAvgCostXlegal($legalid,$NewItem, $db);
	}
    	if ($myItemRow['discontinued']==1){
			prnMsg(_('El Codigo') . ' ' . $NewItem . ' ' . _('No se puede agregar a la orden, ya que se ha marcado como descontinuado'),'error',_('Codigo Descontinuado'));
		} elseif (($_SESSION['AllowSalesOfZeroCostItems'] == false
			AND $myItemRow['standardcost']>0
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
			$Price = GetPrice($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->Branch, $db);
			if ($Price==0)
			{	if ($precioproductoagregado!=''){
					$Price=$precioproductoagregado;
				}
			}
			$WithinCreditLimit = true;
			$DiscountPercentage1=0;
			$DiscountPercentage2=0;
			if ($WithinCreditLimit ==true){
				// Consulta el almacen para visualizacion en tabla
				if ($_SESSION['Items'.$identifier]->add_to_cart ($NewItem,
							$NewItemQty,
							$myItemRow['description'],
							$Price,
							$Discount,
							$myItemRow['units'],
							$myItemRow['volume'],
							$myItemRow['kgs'],
							$myItemRow['quantity'],
							$myItemRow['mbflag'],
							NULL, /*Actual Dispatch Date */
							0, /*Qty Invoiced */
							$myItemRow['discountcategory'],
							$myItemRow['controlled'],
							//0, /*Controlled - dont care */
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
							1 // campo de garantia.........
							
					)==1){
					$lin=$_SESSION['Items'.$identifier]->LineCounter;
					if ($NewLineNo == -1)
						$NewLineNo = 0;
					$_SESSION['Items'.$identifier]->GetTaxes($NewLineNo);
				}else{
				}
			}
         } else {
			prnMsg(_('El codigo de producto') . ' ' . $NewItem . ' ' . _('no tiene un costo y de entrada de pedidos esta configurado para prohibir la venta de productos con costo cero'),'warn');
	     }
	}
} /* end of if not already on the order */
?>
