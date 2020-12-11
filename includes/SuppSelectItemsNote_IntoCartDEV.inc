<?php
/*
$Revision: 4.1 $ 
MODIFICADO POR: Desarrollador
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
			locstock.quantity,
			stockmaster.controlled,
			stockmaster.mbflag,
			stockmaster.discountcategory,
			stockmaster.taxcatid,
			locations.locationname,
			supptransdetails.supptransid,
			supptrans.transtext ,
			supptrans.suppreference as foliofact,
			case when purchorders.refundpercentpurch=0 then supptransdetails.qty* ((purchorderdetails.refundpercent-purchorderdetails.totalrefundpercent)/100) else supptransdetails.qty*((purchorders.refundpercentpurch-purchorders.totalrefundpercentpurch)/100) end  as cantdev,
			case when purchorders.refundpercentpurch=0 then ((purchorderdetails.refundpercent-purchorderdetails.totalrefundpercent)/100) else ((purchorders.refundpercentpurch-purchorders.totalrefundpercentpurch)/100) end as porcdevuelto,
			case when purchorders.refundpercentpurch=0 then purchorderdetails.refundpercent else purchorders.refundpercentpurch end as porcdevolucion,
			purchorders.orderno,
			supptransdetails.price,
			purchorders.orddate,
			supptrans.origtrandate,
			stockmaster.stockautor,
			stockmaster.barcode,
			case when purchorders.refundpercentpurch=0 then 0 else 1 end as tipodev
		FROM stockmaster 
			INNER JOIN locstock ON stockmaster.stockid=locstock.stockid
			INNER JOIN locations ON locstock.loccode=locations.loccode
			INNER JOIN supptransdetails ON supptransdetails.stockid=stockmaster.stockid
			INNER JOIN supptrans ON supptransdetails.supptransid=supptrans.id
			INNER JOIN purchorders ON supptransdetails.orderno=purchorders.orderno
			INNER JOIN purchorderdetails ON purchorderdetails.orderno=purchorders.orderno
				AND supptransdetails.orderno=purchorderdetails.orderno
				AND purchorderdetails.itemcode=supptransdetails.stockid
		WHERE locstock.loccode=locations.loccode
		      AND locstock.loccode='" . $loccalmacen . "'
		      AND supptrans.supplierno='" . $_SESSION['SupplierID'] . "'";
			if ($_SESSION['SearchBarcode']==0){
				$sql=$sql." AND stockmaster.stockid = '". $NewItem . "'";
			}elseif(!isset($_POST['QuickEntry'])){
				$sql=$sql." AND (stockmaster.barcode = '". $NewItem . "' or stockmaster.stockid = '". $NewItem . "')";
			}else{
				$sql=$sql." AND (stockmaster.barcode = '". $NewItem . "')";
			}
		//      $sql=$sql." AND locstock.stockid='" . $NewItem . "'";
		      
		      $sql=$sql." AND purchorders.systypeorder = '". $_SESSION['TypeDev'] . "'
		      AND purchorders.orddate>= '".$_SESSION['FechaCompraIni']."' AND purchorders.orddate<='".$_SESSION['FechaCompraFin']. " 23:59:59' 
		      AND CASE WHEN purchorders.refundpercentpurch=0 then (purchorderdetails.refundpercent-purchorderdetails.totalrefundpercent)>0 ELSE (purchorders.refundpercentpurch-purchorders.totalrefundpercentpurch)>0 END ";
	//echo '<pre>'.$sql;
    $ErrMsg = _('El Codigo') . ' ' . $NewItem . ' ' . _('no se ha encontrado por que');
    $DbgMsg = _('El SQL utilizado para recuperar los detalles de los precios');
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);
    if (DB_num_rows($result1)==0){
		prnMsg(_('El codigo de producto') . ' ' . $NewItem  . ' '  . _('No cuenta con facturas de compra pendientes de devolucion') . ' - ' . _('no ha sido añadido a la orden'),'warn',_('Verifique'));
    } else {
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
	
		while ($myItemRow = DB_fetch_array($result1)){
			
			
			
			if ($_SESSION['Items'.$identifier]->add_to_cart ($NewItem,
						$myItemRow['description'],
						$myItemRow['stockautor'],
						$myItemRow['price'],
						$myItemRow['cantfact'],
						$myItemRow['cantdev'],
						$myItemRow['porcdevolucion'],
						$myItemRow['porcdevuelto'],
						$myItemRow['foliofact'],
						$myItemRow['orddate'],
						$myItemRow['origtrandate'],
						$myItemRow['taxcatid'],
						$myItemRow['orderno'],
						$myItemRow['supptransid'],
						$myItemRow['cantdev'],
						$loccalmacen,
						'',
						$myItemRow['taxcatid'],
						$myItemRow['locationname'],
						'Yes',
						$myItemRow['barcode'],
						$NewLineNo,
						$myItemRow['tipodev']
						
						
						
						
				)==1)
			{
				$lin=$_SESSION['Items'.$identifier]->LineCounter;
				if ($NewLineNo == -1)
					$NewLineNo = 0;
				//echo 'entraaaa'.$NewLineNo;	
				$_SESSION['Items'.$identifier]->GetTaxes($NewLineNo);
			}
			if (DB_num_rows($result1)>1){
				$NewLineNo=$NewLineNo+1;
			}
		}
        } 
} /* end of if not already on the order */
?>
