<?php

if ( isset($_POST['AddBatches']) && $_POST['AddBatches']!='') {

	for ($i=0;$i < 10;$i++){

		if(strlen($_POST['SerialNo' . $i])>0){

			if ($ItemMustExist){

				$ExistingBundleQty = ValidBundleRef($StockID, $LocationOut, $_POST['SerialNo' . $i]);

				if($_SESSION['ChangeSerialItemsCost'] == 1) {
					$CostSerialItem = $_POST['CostSerialItem' . $i];
				} else {
					$CostSerialItem = ValidBundleRefCost($StockID, $LocationOut, $_POST['SerialNo' . $i]);
				}

				if ($ExistingBundleQty >0 or ($ExistingBundleQty==1 and $IsCredit=true)){
					$AddThisBundle = true;


					if ($_POST['Qty' . $i] > $ExistingBundleQty){
						if ($LineItem->Serialised ==1){
							echo '<BR>';
							prnMsg ( $_POST['SerialNo' . $i] . ' ' .
								 _('YA ha sido vendido'),'warning' );
							$AddThisBundle = false;
						} elseif ($ExistingBundleQty==0) { /* and its a batch */
							echo '<BR>';
							prnMsg ( _('No existe serie') . ' '. $_POST['SerialNo' . $i] .
								' '. _('sobrante').'.', 'warn');
							$AddThisBundle = false;
						} else {
							echo '<BR>';
						 	prnMsg (  _('Solo existe'). ' ' . $ExistingBundleQty .
									' '._('de') . ' ' . $_POST['SerialNo' . $i] . ' '. _('restantes') . '. ' .
									_('redusca la cantidad a solicitar de esta serie'),
									'warn');
							$_POST['Qty' . $i] = $ExistingBundleQty;
							$AddThisBundle = true;
						}
					}
					if ($AddThisBundle==true){

						$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i], ($InOutModifier>0?1:-1) * $_POST['Qty' . $i], $CostSerialItem, '', $_POST['Aduana' . $i], $_POST['NoAduana' . $i], $_POST['FechaAduana' . $i], $_POST['Puerto' . $i]);
					}

				else {
        	        echo '<BR>';
	                prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='.$_POST['SerialNo'. $i] . '" target=_blank>'.$_POST['SerialNo'. $i]. '</a> ' ._('no disponible') . '...' , '', 'Notice' );
					unset($_POST['SerialNo' . $i]);
				}
			}
			else {


				$SerialError = false;
				$NewQty = ($InOutModifier>0?1:-1) * $_POST['Qty' . $i];
				$NewSerialNo = $_POST['SerialNo' . $i];
				$CostSerialItem = $_POST['CostSerialItem' . $i];
				if ($LineItem->Serialised){
					$ExistingQty = ValidBundleRef($StockID, $LocationOut, $NewSerialNo);

					if($_SESSION['ChangeSerialItemsCost'] == 1) {
						$CostSerialItem = $_POST['CostSerialItem' . $i];
					} else {
						$CostSerialItem = ValidBundleRefCost($StockID, $LocationOut, $NewSerialNo);
					}

					if ($NewQty == 1 && $ExistingQty != 0){
						prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> : '. _("The Serial Number being added exists with a Quantity that is not Zero (0)!"), 'error' );
						$SerialError = true;
					} elseif ($NewQty == -1 && $ExistingQty != 1){
						prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> : '. _("The Serial Number being removed exists with a Quantity that is not One (1)!"), 'error');
						$SerialError = true;
					}
				}

				if (!$SerialError){


					$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($_POST['SerialNo' . $i], $NewQty,$CostSerialItem, '', $_POST['Aduana' . $i], $_POST['NoAduana' . $i], $_POST['FechaAduana' . $i], $_POST['Puerto' . $i]);
				}
			}
		}

	}

	for ($i=0;$i < count($_POST['Bundles']);$i++){


		$CostSerialItem = ValidBundleRefCost($StockID, $LocationOut, $_POST['Bundles'][$i]);
		$serialnotmp = explode ('/|/', $_POST['Bundles'][$i]);

		$SQL = "
			SELECT  standardcost
			FROM stockserialitems
			WHERE stockid='" . $StockID . "'
			AND loccode ='" . $LocationOut . "'
			AND serialno='" . $serialnotmp[0] . "'
		";

		$ResultTmp 		= DB_query($SQL, $db);
		$NewAduana 		= '';
		$NewNoAduana 	= '';
		$NewFechaAduana = '';
		if($rowTmp = DB_fetch_array($ResultTmp)) {
			$NewAduana 		= $rowTmp['customs'];
			$NewNoAduana 	= $rowTmp['customs_number'];
			$NewFechaAduana = $rowTmp['customs_date'];
		}

		if ($LineItem->Serialised==1){
			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i],  ($InOutModifier>0?1:-1), $CostSerialItem, '', $NewAduana,$NewNoAduana,$NewFechaAduana);
		} else {
			list($SerialNo, $Qty, $CostSerialItem) = explode ('/|/', $_POST['Bundles'][$i]);

		if ($Qty != 0) {

				if($showoptions==false){

					if($RecvQty<$Qty){

					}
				}

				$LineItem->SerialItems[$SerialNo] =
					new SerialItem ($SerialNo,  $Qty*($InOutModifier>0?1:-1) ,$CostSerialItem);
			}
		}
	}

}
if ( isset($_POST['AddSequence']) && $_POST['AddSequence']!='') {

	$BeginNo =  $_POST['BeginNo'];
	$EndNo   = $_POST['EndNo'];
	if ($BeginNo > $EndNo){
		prnMsg( _('Para agregar numeros series de manera secuencial el numero de inicio debe ser mayor al numero final'), 'error');
	} else {
		$sql = "SELECT serialno FROM stockserialitems
			WHERE serialno BETWEEN '". $BeginNo . "' AND '". $EndNo . "'
			AND stockid = '". $StockID."' AND loccode='". $LocationOut . "'";
		$Qty = ($InOutModifier>0?1:0);
		if ($LineItem->Serialised == 1){
			$sql .= " AND quantity = ".$Qty;
		}
		$SeqItems = DB_query($sql,$db);
		if(DB_num_rows($SeqItems) > 0) {
			while ($myrow=db_fetch_array($SeqItems)) {
				$LineItem->SerialItems[$myrow['serialno']] = new SerialItem ($myrow['serialno'], ($InOutModifier>0?1:-1) ,$CostSerialItem, '', $_POST['Aduana'], $_POST['NoAduana'], $_POST['FechaAduana']);

				$_POST['EntryType'] = 'KEYED';
			}
		} else {
			prnMsg( _("No se encontraron número de serie válidos").' ', 'error' );
		}
	}
}
$valid = true;
if ($_POST['EntryType']=='FILE' && isset($_POST['ValidateFile'])){

	$filename = $_SESSION['CurImportFile']['tmp_name'];

	$handle = fopen($filename, 'r');
	$TotalLines=0;
	$LineItem->SerialItemsValid=false;
	while (!feof($handle)) {
		$contents = trim(fgets($handle, 4096));

		$pieces  = explode(",",$contents);

		if ($LineItem->Serialised == 1){


			if(trim($pieces[0]) != ""){
				$valid=false;
				if (strlen($pieces[0]) <= 0 ){
					$valid=false;
				} else {
					$valid=true;
				}
				if ($valid){

					$NewSerialNo = $pieces[0];
					$NewQty = ($InOutModifier>0?1:-1);
					$NewAduana = $pieces[2];
					$NewNoAduana = $pieces[3];
					$NewFechaAduana = $pieces[4];
				}
			} else {
				$valid = false;
			}
		} else {

			if($pieces[0] != "" && $pieces[1] != "" && is_numeric($pieces[1]) && $pieces[1] > 0 ){

					$NewSerialNo = $pieces[0];
					$NewQty = ($InOutModifier>0?1:-1) * $pieces[1];
					$NewAduana = $pieces[2];
					$NewNoAduana = $pieces[3];
					$NewFechaAduana = $pieces[4];
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

					if ($NewQty > $ExistingBundleQty){
							if ($LineItem->Serialised ==1){
									echo '<BR>' . '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> '. _('ha sido vendida'). '.';
									$AddThisBundle = false;
								} elseif ($ExistingBundleQty==0) { /* and its a batch */
									echo '<BR>' . _('There is none of'). ' <a href="/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> '. _('restan') .'.';
									$AddThisBundle = false;
							} else {
									echo '<BR>'. _('Existen solo') . ' ' . $ExistingBundleQty . ' '. _('de') . ' ' .
												'<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> ' . _('restan') . '. '.
												_('ingrese una cantidad menor para esta serie o lote');
									$NewQty = $ExistingBundleQty;
									$AddThisBundle = true;
							}
					}
					if ($AddThisBundle==true){
							$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty,$CostSerialItem,'',$NewAduana,$NewNoAduana,$NewFechaAduana);
					}
			}
			else {
				echo '<BR>';
				prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a>  ' . _('no disponible') ,'', 'Notice' );
			}
			if (!$valid) $invalid_imports++;

		} else {


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
				$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty,$CostSerialItem,'',$NewAduana,$NewNoAduana,$NewFechaAduana);
			}

		}
	}
	if ($invalid_imports==0){
		$LineItem->SerialItemsValid=true;
		$_SESSION['CurImportFile']['Processed']=true;
	}
	fclose($handle);

}

if (isset($_GET['REVALIDATE']) || isset($_POST['REVALIDATE'])) {
	$invalid_imports = 0;
	$OrigLineItem = $LineItem;
	$LineItem->SerialItems = array();
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

					$NewSerialNo = $Item->BundleRef;
					$NewQty = ($InOutModifier>0?1:-1) * $Item->BundleQty;
					$NewAduana = $Item->Customs;
					$NewNoAduana = $Item->CustomsNumber;
					$NewFechaAduana = $Item->CustomsDate;

				}
			} else {
				$valid = false;
			}
		} else {

			if($Item->BundleRef != "" && $Item->BundleQty != "" && is_numeric($Item->BundleQty) && $Item->BundleQty > 0 ){


					$NewSerialNo = $Item->BundleRef;
					$NewAduana = $Item->Customs;
					$NewNoAduana = $Item->CustomsNumber;
					$NewFechaAduana = $Item->CustomsDate;
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

					if ($NewQty > $ExistingBundleQty){
							if ($LineItem->Serialised ==1){
									echo '<BR>' . '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> '. _('ha sido vendido'). '.';
									$AddThisBundle = false;
								} elseif ($ExistingBundleQty==0) { /* and its a batch */
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
							$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty,$CostSerialItem,'',$NewAduana,$NewNoAduana,$NewFechaAduana);
					}
			}
			else {
				echo '<BR>';
				prnMsg( '<a href="'.$rootpath.'/StockSerialItemResearch.php?serialno='. $NewSerialNo . '" target=_blank>'.$NewSerialNo. '</a> ' . _('no disponible') . '...' ,'', 'Notice' );
			}
			if (!$valid) $invalid_imports++;

		} else {


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
				$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty,$CostSerialItem,'',$NewAduana,$NewNoAduana,$NewFechaAduana);
			} else {
				$invalid_imports++;
			}
		}
	}
	$LineItem->Quantity = sizeof($LineItem->SerialItems);
	if ($invalid_imports > 0){
		prnMsg( _('Finalio proceso de validacion y se encontraron') . ' : ' . $invalid_imports . ' ' . _('problemas. por favor verifique el numero de serie') . '.', 'warn' );
	} else {
		prnMsg( _('Finalizo el proceso de validacion de serie sin errores').' ', 'success' );
	}
	include('includes/footer.inc');
	exit;

}

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
?>
