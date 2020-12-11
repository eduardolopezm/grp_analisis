<?php

/* $Revision: 4.0 $ 

MODIFICADO POR: Desarrollador
FECHA: 30/NOV/2009
CAMBIOS:
1.- AGREGUE EN DETALLE ORDEN ALMACEN
FIN DE CAMBIOS

MODIFICADO POR: Desarrollador
FECHA: 04/DIC/2009
CAMBIOS:
1.- AGREGUE DESCUENTOS EN CASCADA
FIN DE CAMBIOS
*/

/*SelectOrderItemsProducts_IntoCart.inc

This snippet is used to enter order line items into the cart object:

Used only in: SelectOrderItems.php

The reason that it is in this seperate file is because it is used within a loop to get kitset
items into the cart as well as plain vanilla items outside of the kitset loop
*/

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
			stockmaster.controlled,
			stockcategory.redinvoice as red
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid,
			locations, stockcategory
		WHERE locstock.loccode=locations.loccode
		      AND stockcategory.categoryid=stockmaster.categoryid
		      AND locstock.loccode='" . $loccalmacen . "'
			AND stockmaster.stockid = '". $NewItem . "'";
	
    $ErrMsg = _('El Codigo') . ' ' . $NewItem . ' ' . _('no se ha encontrado por que');
    $DbgMsg = _('El SQL utilizado para recuperar los detalles de los precios');
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    if (DB_num_rows($result1)==0){
		prnMsg(_('El codigo de producto') . ' ' . $NewItem  . ' '  . _('No se puede encontrar en la base de datos') . ' - ' . _('no ha sido añadido a la orden'),'warn',_('El codigo no existe'));
    } elseif ($myItemRow = DB_fetch_array($result1)){
    	if ($myItemRow['discontinued']==1){
			prnMsg(_('El Codigo') . ' ' . $NewItem . ' ' . _('No se puede agregar a la orden, ya que se ha marcado como descontinuado'),'error',_('Codigo Descontinuado'));
		}elseif (($_SESSION['AllowSalesOfZeroCostItems'] == false
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

			$Price = GetPriceDOS($NewItem, $_SESSION['Items'.$identifier]->DebtorNo,$_SESSION['Items'.$identifier]->DefaultSalesType,$_SESSION['CurrAbrev'], $db);
			$separa2 = explode('|',$Price);	
			$bgcolorlista2 = $separa2[1];
			$Price = $separa2[0];
				
			if ($Price==0)
			{	if ($precioproductoagregado!=''){
					$Price=$precioproductoagregado;
				}
			}
			
			$WithinCreditLimit = true;

			if (!isset($_SESSION['WarnOnce']) and $_SESSION['Items'.$identifier]->SpecialInstructions) {
			  	prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
		       	$WarnOnce=1;
            }
			if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn
											and 2 for prohibit sales */
				$_SESSION['Items'.$identifier]->CreditAvailable -= round(($NewItemQty * $Price * (1- $Discount)),6);

				if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					if (isset($_SESSION['WarnOnce']) and $_SESSION['WarnOnce'] < 2) prnMsg(_('The customer account will breach their credit limit'),'warn');
					$_SESSION['WarnOnce']=2;
				} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
					prnMsg(_('No se pueden agregar mas productos a este pedido la cuenta de cliente se encuentra actualmente en o sobre su limite de credito'),'warn');
					$WithinCreditLimit = false;
				}
			}
			$DiscountPercentage1=0;
			$DiscountPercentage2=0;
			if ($WithinCreditLimit ==true){
				
				if ($debuggaz==1)
					echo '<BR>AGREGA PRODUCTO...';
				
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
							$myItemRow['standardcost'],
							$myItemRow['eoq'],
							$myItemRow['nextserialno'],
							$loccalmacen,
							$myItemRow['locationname'],
							$DiscountPercentage1,
							$DiscountPercentage2,
							1, // campo de garantia.........
							0,
							$myItemRow['red']// Facturar en rojo por categoria
							
					)==1){
					
					//$this->LineCounter
					$lin=$_SESSION['Items'.$identifier]->LineCounter;
					
					if ($debuggaz==1)
						echo "<BR>NewLineNo:".$lin." linea:".$NewLineNo;
					
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
