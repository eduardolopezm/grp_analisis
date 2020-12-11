<?php
/* $Revision: 1.7 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php

*/

//we start with a batch or serial no header and need to display something for verification...
global $tableheader;

$xfact = 0;
if(isset($_GET['XFact'])) {
	$xfact = $_GET['XFact'];
} else if(isset($_POST['XFact'])) {
	$xfact = $_POST['XFact'];
}

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

/*Display the batches already entered with quantities if not serialised */

echo '<table><tr><td valign=top><table>';
echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

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
	echo '<td>' . $Bundle->BundleQty . '</td>';
	echo '<td>' . $Bundle->Customs . '</td>';
	echo '<td>' . $Bundle->CustomsNumber . '</td>';
	echo '<td nowrap > ' . $Bundle->CustomsDate . '</td>';
	echo '<td nowrap >' . $Bundle->CostSerialItem . '</td>';
	echo '<td nowrap >' . $Bundle->EntryPort . '</td>';
	echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'Delete=' . $Bundle->BundleRef . '&StockID=' . $LineItem->StockID . '&LineNo=' . $LineNo .'&identifier=' . $identifier . '&XFact=' . $xfact . '">'. _('Eliminar'). '</a></td></tr>';

	$TotalQuantity += $Bundle->BundleQty;
	$_SESSION['TotalQuantity']=$TotalQuantity;
}


/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo '<tr><td class=number><B>'. _('Cantidad Total'). ': ' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td></tr>';
} else {
	echo '<tr><td class=number><B>'. _('Cantidad Total'). ':</b></td><td class=number><b>' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td></tr>';
}

/*Close off old table */
echo '</table></td><td valign=top>';

/*Start a new table for the Serial/Batch ref input  in one column (as a sub table
then the multi select box for selection of existing bundle/serial nos for dispatch if applicable*/
//echo '<TABLE><TR><TD valign=TOP>';

/*in the first column add a table for the input of newies */
echo '<table>';
echo $tableheader;


echo '<form action="' . $_SERVER['PHP_SELF'] . '?=' . $SID . '" name="Ga6uF5Wa" method="post">
      <input type=hidden name=LineNo value="' . $LineNo . '">
      <input type=hidden name=StockID value="' . $StockID . '">
      <input type=hidden name="identifier" value="'. $identifier. '">
      <input type=hidden name=EntryType value="KEYED">';
if ( isset($_GET['EditControlled']) ) {
	$EditControlled = isset($_GET['EditControlled'])?$_GET['EditControlled']:false;
} elseif ( isset($_POST['EditControlled']) ){
	$EditControlled = isset($_POST['EditControlled'])?$_POST['EditControlled']:false;
}
$StartAddingAt = 0;


if ($EditControlled==true and $ShowExisting==false){
	
	foreach ($LineItem->SerialItems as $Bundle){

		echo '<tr><td valign=top><input type=text name="SerialNo'. $StartAddingAt .'"
			value="'.$Bundle->BundleRef.'" size=21  maxlength=20></td>';
		
		echo '<td valign=top><input type=text name="Aduana'. $StartAddingAt .'"
			value="'.$Bundle->Customs.'" size=21  maxlength=20></td>';
		
		echo '<td valign=top><input type=text name="NoAduana'. $StartAddingAt .'"
			value="'.$Bundle->CustomsNumber.'" size=21  maxlength=20></td>';
		
		echo '<td valign=top><input type=text name="FechaAduana'. $StartAddingAt .'"
			value="'.$Bundle->CustomsDate.'" size=21  maxlength=20></td>';
		
		if($_SESSION['ChangeSerialItemsCost'] == 1) {
			echo '<td valign=top><input type=text name="CostSerialItem'. $StartAddingAt .'"
			value="'.$Bundle->CostSerialItem.'" size=21  maxlength=20></td>';
		} else {
			echo '<td valign=top>0</td>';
		}
	

		/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
		into the form for entry of quantities manually */

		if ($LineItem->Serialised==1){
			echo '<input type=hidden name="Qty' . $StartAddingAt .'" Value=1></TR>';
		} else {
			echo '<td><input type=text class="number" name="Qty' . $StartAddingAt .'" size=11
				value="'. number_format($Bundle->BundleQty, $LineItem->DecimalPlaces). '" maxlength=10></tr>';
		}

		$StartAddingAt++;
	}
}
if(isset($_SESSION['PO']->OrderNo)){
	$qry = "Select customs,pedimento,date_format(dateship,'%m/%d/%Y') as dateship ,inputport
			FROM purchorderdetails 
			WHERE orderno = ".$_SESSION['PO']->OrderNo." and itemcode = '$StockID'" ;
	//*and orderlineno_ = ". ($LineNo-1)'';
	
	$res = DB_query($qry,$db);
	$row = DB_fetch_array($res);
	$aduana = $row['customs'];
	$pedimento = $row['pedimento'];
	$dateship = $row['dateship'];
	$puertoentrada = $row['inputport'];
	
	
	//buscar datos de embarque
	
	$qry = "Select voyageref
		FROM purchorderdetails inner join shipments
		WHERE purchorderdetails.shiptref = shipments.shiptref
		and purchorderdetails.orderno = ".$_SESSION['PO']->OrderNo."
		and purchorderdetails.itemcode = '".$StockID."'";
	$rsc = DB_query($qry,$db);
	//$pedimento="";
	if (DB_num_rows($rsc) > 0){
		$reg = DB_fetch_array($rsc);
		$aduana = $reg[0];
		
	}
	
	$qry = "Select serialised, controlled FROM stockmaster
			WHERE stockid = '$StockID'";
	$res = DB_query($qry,$db);
	$row = DB_fetch_array($res);
	if ($row['controlled'] == 1 and $row['serialised'] == 0){
		
		$pedimento = $aduana;
	}
	
}

if($ShowExisting==false or $ShowCapture==true){
	for ($i=0;$i < 10;$i++){
		echo '<tr><td valign=top>';
		
		echo '<input type=text name="SerialNo'. ($StartAddingAt+$i) .'" size=21  maxlength=50 value="'.$serialno.'"></td>';
		if($LineItem->Serialised==1) {
			echo '<td><input type=text class="number" readonly="readonly" name="Qty' . ($StartAddingAt+$i) .'" size=11  value="1" maxlength=50></td>';
		} else {
			echo '<td><input type=text class="number" name="Qty' . ($StartAddingAt+$i) .'" size=11  maxlength=50></td>';
		}
		echo '<td valign=top><input type=text name="Aduana'. ($StartAddingAt+$i) .'" size=21  maxlength=50 value="'.$aduana.'"></td>';
		echo '<td valign=top><input class="number" type=text name="NoAduana'. ($StartAddingAt+$i) .'" size=21 value="'.$pedimento.'"  maxlength=50></td>';
		echo '<td valign=top><input class="date" type=text name="FechaAduana'. ($StartAddingAt+$i) .'" size=21 value="'.$dateship.'"  maxlength=50></td>';
		
		
		if($_SESSION['ChangeSerialItemsCost'] == 1) {
			echo '<td valign=top><input class="number" type=text name="CostSerialItem'. ($StartAddingAt+$i) .'"
			value="'.$serialcost.'" size=21  maxlength=20></td>';
		} else {
			echo '<td valign=top>0</td>';
		}
	
		echo '<td valign=top>'.$puertoentrada.'</td>
			  <input type=hidden name="Puerto' . ($StartAddingAt+$i) .'" value="' . $puertoentrada . '">
			</tr>';
		//echo '<td valign=top><input class="number" type=text name="Puerto'. ($StartAddingAt+$i) .'" size=21 value="'.$pedimento.'"  maxlength=50></td>';
		
		
		$aduana = "";
		$pedimento = "";
		$dateship = "";
		$serialcost = "";
		$serialno = "";
	
		/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
		into the form for entry of quantities manually */
	
		
	}
	
	echo '</table>';
	echo '<br><input type=submit name="AddBatches" value="'. _('Ingresar'). '"><br>';
	echo '</form></td><td valign=top>';
}

if ($ShowExisting){
	
	include('includes/InputSerialItemsExisting.php');
}
echo '</td></tr></table><script type="text/javascript">
//<![CDATA[
document.Ga6uF5Wa.SerialNo0.focus();
//]]>
</script>'; /*end of nested table */
?>
