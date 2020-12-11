<?php

/* $Revision: 1.4 $ */

/**
If the User has selected Keyed Entry, show them this special select list...
it is just in the way if they are doing file imports
it also would not be applicable in a PO and possible other situations... 
**/
global $tableheader;

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

/*Display the batches already entered with quantities if not serialised */

echo '<table><tr><td valign=top><table>';
//echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;
/*

foreach ($LineItem->SerialItems as $Bundle){

	if ($RowCounter == 10){
		echo $tableheader;
		$RowCounter =0;
	} else {
		$RowCounter++;
	}

	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k=1;
	}

	echo '<td>' . $Bundle->BundleRef . '</td>';

	if ($LineItem->Serialised==0){
		echo '<td class=number>' . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</td>';
		
	}
	echo '<td class=number>' . number_format($Bundle->CostSerialItem, $LineItem->DecimalPlaces) . '</td>';
	echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $Bundle->BundleRef . '&StockID=' . $LineItem->StockID . '&LineNo=' . $LineNo .'">'. _('Eliminar'). '</a></td></tr>';

	$TotalQuantity += $Bundle->BundleQty;
}

*/
$xparent=0;
foreach ($LineItem->SerialItems as $Bundle){
	if ($xparent==0){
		$stockparents="'".$Bundle->StockIDParent;
	}else{
		$stockparents.="','".$Bundle->StockIDParent;
	}
	$xparent=$xparent+1;
}

if ($xparent>0){
	$stockparents=$stockparents."'";
}
if ($_POST['EntryType'] == 'KEYED'){
        /*Also a multi select box for adding bundles to the dispatch without keying */
        $sql = "SELECT serialno, quantity, standardcost ,stockid
			FROM stockserialitems 
			WHERE stockid in (" . $StockIDParent . " )";
	if ($xparent>0){		
		$sql=$sql." and stockid not in (".$stockparents.")";
	}
	$sql=$sql."		AND loccode ='" .
		$LocationOut."' AND (quantity > 0) order by stockid";
	//echo $sql;
//echo $sql;
	$ErrMsg = '<BR>'. _('No existe series disponibles para el codigo'). ' ' . $StockID;
        $Bundles = DB_query($sql,$db, $ErrMsg );
	echo '<TABLE><TR>';
        if (DB_num_rows($Bundles)>0){
                $AllSerials=array();
		$AllSerialscost=array();
                
		foreach ($LineItem->SerialItems as $Itm){ 
			$AllSerials[$Itm->BundleRef] = $Itm->BundleQty;
			$AllSerialscost[$Itm->BundleRef] = $Itm->CostSerialItem;
			
		}
                
		echo '<TD VALIGN=TOP><B>'. _('Selecciona series disponibles'). '</B><BR>';
                
		echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" METHOD="POST">
                        <input type=hidden name=LineNo value="' . $LineNo . '">
                        <input type=hidden name=StockID value="' . $StockID . '">
			
                        <input type=hidden name=EntryType value="KEYED">
			<input type=hidden name="identifier" value="'. $identifier. '">
			<input type=hidden name=EditControlled value="true">
			<SELECT Name=Bundles[] multiple style="width:350px;height:150px">';

                $id=0;
		$ItemsAvailable=0;
                while ($myrow=DB_fetch_array($Bundles,$db)){
			/*if ($LineItem->Serialised==1){
				if ( !array_key_exists($myrow['serialno'], $AllSerials) ){
	                        	echo '<OPTION VALUE="' . $myrow['serialno'] . '">' . $myrow['serialno'].' --- Stock:'. $myrow['stockid'].'</OPTION>';
					$ItemsAvailable++;
				}
                        } else {*/
                               if ( !array_key_exists($myrow['serialno'], $AllSerials)) {
					
					$RecvQty = $myrow['quantity'] - $AllSerials[$myrow['serialno']];
					
					$sql = "SELECT bom.component,
							bom.quantity
						 FROM bom
						 WHERE bom.parent='" . $LineItem->StockID . "'
						       AND bom.effectiveto > '" . Date('Y-m-d') . "'
						       AND bom.effectiveafter < '" . Date('Y-m-d') . "'
						       AND bom.component='" . $myrow['stockid'] . "'";
					 $ErrMsg = _('No se pudo recuperar los componentes de la base de datos por que');
					 $KitResultXunit = DB_query($sql,$db,$ErrMsg);
					 while ($KitPartsXComponent = DB_fetch_array($KitResultXunit,$db)){
						$Canti=$KitPartsXComponent['quantity'];
					 }
					if ($RecvQty>$Canti ) {
						$RecvQty=$Canti;
					}
					
					$CostSerialItem=$myrow['standardcost'];
                                        echo '<OPTION VALUE="' . $myrow['serialno'] . '/|/'. $RecvQty . '/|/'. $CostSerialItem . '/|/' .$myrow['stockid'].'">' . 
						 _('*') . $myrow['stockid'].' - '._('Serie').':'. $myrow['serialno'] . '</OPTION>';
					$ItemsAvailable += $RecvQty;
                                }
			//}
                }
                echo '</SELECT><br>';
		echo '<br><center><INPUT TYPE=SUBMIT NAME="AddBatches" VALUE="'. _('Agregar'). '"></center><BR>';	
		echo '</FORM>';
		echo $ItemsAvailable . ' ' . _('series disponibles');
		echo '</TD>';
        } else {
		if ($xparent>0){
			echo '<TD>'. prnMsg( _('Ha concluido proceso de seleccion, regresar a la orden de venta') , 'info') . '</TD>';
		}else{
			echo '<TD>'. prnMsg( _('No existe serie ') . ' ' . $StockID . ' ' . _(' en'). ' '. $LocationOut , 'warn') . '</TD>';
		}
	}

        echo '</TR></TABLE>';
}