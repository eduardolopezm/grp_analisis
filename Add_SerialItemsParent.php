<?php
/* $Revision: 1.9 $ */
/*ProcessSerialItems.php takes the posted variables and adds to the SerialItems array
 in either the cartclass->LineItems->SerialItems or the POClass->LineItems->SerialItems */

/********************************************
        Added KEYED Entry values
********************************************/

if ( isset($_POST['AddBatches']) && $_POST['AddBatches']!='') {
	for ($i=0;$i < count($_POST['Bundles']);$i++){ /*there is an entry in the multi select list box */
		//echo count($_POST['Bundles']);
		//$CostSerialItem = ValidBundleRefCost($_POST['Bundles'][$i], $LocationOut, $_POST['Bundles'][$i]);
		
		//if ($LineItem->Serialised==1){	/*only if the item is serialised */
		//$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i],  ($InOutModifier>0?1:-1), $CostSerialItem,$StockIDParent);
		//} else {
			list($SerialNo, $Qty, $CostSerialItem,$StockIDParent2) = explode ('/|/', $_POST['Bundles'][$i]);
			//echo $Qty*($InOutModifier>0?1:-1);
			//$CostSerialItem = ValidBundleRefCost($StockIDParent2, $LocationOut, $SerialNo);
			//echo 
			if ($Qty != 0) {
				
				$LineItem->SerialItems[$SerialNo] =
					new SerialItem ($SerialNo,  $Qty*($InOutModifier>0?1:-1) ,$CostSerialItem,$StockIDParent2);
			}
		//}
	}
} /*end if the user hit the enter button on Keyed Entry */

if (isset($_GET['DELETEALL'])){
        $RemAll = $_GET['DELETEALL'];
} else {
        $RemAll = 'NO';
}

if ($RemAll == 'YES'){
        unset($LineItem->SerialItems);
        $LineItem->SerialItems=array();
	unset($_SESSION['CurImportFile']);
}

if (isset($_GET['Delete'])){
        unset($LineItem->SerialItems[$_GET['Delete']]);
}


include ('includes/InputSerialItemsKeyedParent.php');

 /********************************************
   Add a Sequence of Items and save entries
 ********************************************/

/********************************************
  Validate an uploaded FILE and save entries
********************************************/
$valid = true;
/********************************************
  Revalidate Array of Items
     The point of this is to allow "copying" an array of items from 1 object to another, checking them, and insuring that nothing else
	 is added. So, after the validation, we will exit and NOT allow more items to be added.

********************************************/
/*
if (isset($_GET['REVALIDATE']) || isset($_POST['REVALIDATE'])) {
	$invalid_imports = 0;
	$OrigLineItem = $LineItem; //grab a copy of the old one...
	$LineItem->SerialItems = array(); // and then reset it so we can add back to it.
	foreach ($OrigLineItem->SerialItems as $Item){
		if ($OrigLineItem->Serialised == 1){
			if(trim($Item->BundleRef) != ""){
				$valid=false;
				if (strlen($Item->BundleRef) <= 0 ){
					$valid=false;
				} else {
					$valid=true;
				}
				if ($valid){
					/*If the user enters a duplicate serial number the later one over-writes the first entered one - no warning given though ? 
					$NewSerialNo = $Item->BundleRef;
					$NewQty = ($InOutModifier>0?1:-1) * $Item->BundleQty;
				}
			} else {
				$valid = false;
			}
		} else {
		//for controlled only items, we must receive: BatchID, Qty in a comma delimited  file
			if($Item->BundleRef != "" && $Item->BundleQty != "" && is_numeric($Item->BundleQty) && $Item->BundleQty > 0 ){
			/*If the user enters a duplicate batch number the later one over-writes
			the first entered one - no warning given though ? 
					//$LineItem->SerialItems[$pieces[0]] = new SerialItem ($pieces[0],  $pieces[1] );
					$NewSerialNo = $Item->BundleRef;
					$NewQty = ($InOutModifier>0?1:-1) * $Item->BundleQty;
			} else {
					$valid = false;
			}
		}
		$TotalLines++;
		if ($ItemMustExist){
			$ExistingBundleQty = ValidBundleRef($StockID, $LocationOut, $NewSerialNo);
			$CostSerialItem = ValidBundleRefCost($StockID, $LocationOut, $NewSerialNo);
			if ($ExistingBundleQty >0){
				$AddThisBundle = true;
					/*If the user enters a duplicate serial number the later one over-writes the first entered one - no warning given though ? 
					if ($NewQty > $ExistingBundleQty){
							if ($LineItem->Serialised ==1){
									echo '<BR>' . '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> '. _('ha sido vendido'). '.';
									$AddThisBundle = false;
								} elseif ($ExistingBundleQty==0) { /* and its a batch 
									echo '<BR>' . _('There is none of'). ' <a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> '. _('restantes') .'.';
									$AddThisBundle = false;
							} else {
									echo '<BR>'. _('solo hay') . ' ' . $ExistingBundleQty . ' '. _('de') . ' ' .
												'<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> ' . _('restantes') . '. '.
												_('ingrese una cantidad menor de esta serie');
									$NewQty = $ExistingBundleQty;
									$AddThisBundle = true;
							}
					}
					if ($AddThisBundle==true){
							$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty,$CostSerialItem);
					}
			} /*end if ExistingBundleQty >0 
			else {
				echo '<BR>';
				prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> ' . _('no disponible') . '...' ,'', 'Notice' );
			}
			if (!$valid) $invalid_imports++;
			// of MustExist
		} else {
			//Serialised items can not exist w/ Qty > 0 if we have an $NewQty of 1
			//Serialised items must exist w/ Qty = 1 if we have $NewQty of -1
			$SerialError = false;
			if ($LineItem->Serialised){
				$ExistingQty = ValidBundleRef($StockID, $LocationOut, $NewSerialNo);
				$CostSerialItem = ValidBundleRefCost($StockID, $LocationOut, $NewSerialNo);
				if ($NewQty == 1 && $ExistingQty != 0){
					prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a>: '. _("The Serial Number being added exists with a Quantity that is not Zero (0)!"), 'error' );
					$SerialError = true;
				} elseif ($NewQty == -1 && $ExistingQty != 1){
					prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> : '. _("The Serial Number being removed exists with a Quantity that is not One (1)!"), 'error');
					$SerialError = true;
				}
			}
			if (!$SerialError){
				$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty,$CostSerialItem);
			} else {
				$invalid_imports++;
			}
		}
	}//foreach OrigItems
	$LineItem->Quantity = sizeof($LineItem->SerialItems);
	if ($invalid_imports > 0){
		prnMsg( _('Finalio proceso de validacion y se encontraron') . ' : ' . $invalid_imports . ' ' . _('problemas. por favor verifique el numero de serie') . '.', 'warn' );
	} else {
		prnMsg( _('Finalizo el proceso de validacion de serie sin errores').' ', 'success' );
	}
	include('includes/footer.inc');
	exit;

}//ReValidate
*/
/********************************************
  Process Remove actions
********************************************/

?>
