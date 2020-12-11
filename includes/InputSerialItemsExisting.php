<?php

/* $Revision: 1.4 $ */

/**
If the User has selected Keyed Entry, show them this special select list...
it is just in the way if they are doing file imports
it also would not be applicable in a PO and possible other situations... 
**/

$xfact = 0;
if(isset($_GET['XFact'])) {
	$xfact = $_GET['XFact'];
} else if(isset($_POST['XFact'])) {
	$xfact = $_POST['XFact'];
}

if ($_POST['EntryType'] == 'KEYED'){
        /*Also a multi select box for adding bundles to the dispatch without keying */
        $sql = "SELECT serialno, quantity, standardcost 
			FROM stockserialitems 
			WHERE stockid='" . $StockID . "' AND loccode ='" .
		$LocationOut."' AND quantity > 0";
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
                
		echo '<TD VALIGN=TOP><B>'. _('Selecciona serie disponible'). '</B><BR>';
                
		echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" METHOD="POST">
                        <input type=hidden name=LineNo value="' . $LineNo . '">
                        <input type=hidden name=StockID value="' . $StockID . '">
			
                        <input type=hidden name=EntryType value="KEYED">
			<input type=hidden name="identifier" value="'. $identifier. '">
			<input type=hidden name="XFact" value="'. $xfact. '">
			<input type=hidden name=EditControlled value="true">
			<SELECT Name=Bundles[] multiple style="width:200px">';

                $id=0;
		$ItemsAvailable=0;
                while ($myrow=DB_fetch_array($Bundles,$db)){
			if ($LineItem->Serialised==1){
				if ( !array_key_exists($myrow['serialno'], $AllSerials) ){
	                        	echo '<OPTION VALUE="' . $myrow['serialno'] . '">' . $myrow['serialno'].'</OPTION>';
					$ItemsAvailable++;
				}
                        } else {
                               if ( !array_key_exists($myrow['serialno'], $AllSerials)  ||
					($myrow['quantity'] - $AllSerials[$myrow['serialno']] >= 0) ) {
					$RecvQty = $myrow['quantity'] - $AllSerials[$myrow['serialno']];
					
					if($xfact != 0) {
						if($xfact <= $RecvQty) {
							$RecvQty = $xfact;
						}
					}
					
					$CostSerialItem=$myrow['standardcost'];
                                        echo '<OPTION VALUE="' . $myrow['serialno'] . '/|/'. $RecvQty . '/|/'. $CostSerialItem .'">' . 
						$myrow['serialno'].' - ' . _('Qty left'). ': ' . $RecvQty . '</OPTION>';
					$ItemsAvailable += $RecvQty;
                                }
			}
                }
                echo '</SELECT><br>';
		echo '<br><center><INPUT TYPE=SUBMIT NAME="AddBatches" VALUE="'. _('Agregar'). '"></center><BR>';	
		echo '</FORM>';
		echo $ItemsAvailable . ' ' . _('series disponibles');
		echo '</TD>';
        } else {
		echo '<TD>'. prnMsg( _('No existe serie ') . ' ' . $StockID . ' ' . _(' en'). ' '. $LocationOut , 'warn') . '</TD>';
	}

        echo '</TR></TABLE>';
}