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

if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

/*Display the batches already entered with quantities if not serialised */
if ($LineItem->Serialised==1){
	$tableheader .= '<tr>
			<th>'. _('Serial No').'</th>';
	$tableheader .= '<th>'. _('Costo Serie'). '</th>';
	$tableheader .= '<th>'. _('Componente'). '</th>';
	$tableheader .= '</Tr>';
} else {
	$tableheader = '<TR>
			<th>'. _('Lote'). ' #</th>
			<th class=tableheader>'. _('Cantidad'). '</th>
			<th class=tableheader>'. _('Componente'). '</th>
			</tr>';
}
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

	if ($LineItem->Serialised==0){
		echo '<td class=number>' . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</td>';
		
	}
	echo '<td class=number>' . number_format($Bundle->CostSerialItem, $LineItem->DecimalPlaces) . '</td>';
	echo '<td>' . $Bundle->StockIDParent . '</td>';
	echo '<td>
	<a href="' . $_SERVER['PHP_SELF'] . '?' .
	SID . 'Delete=' . $Bundle->BundleRef .
	'&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .
	'&identifier='.$identifier .
	'&lineaxs='. $_SESSION['Items'.$identifier]->LineCounter.'">'. _('Eliminar'). '</a></td></tr>';

	$TotalQuantity += $Bundle->BundleQty;
}
if ($LineItem->Serialised==1){
	echo '<tr><td class=number><B>'. _('Total '). ': ' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td></tr>';
} else {
	echo '<tr><td class=number><B>'. _('Total '). ':</b></td><td class=number><b>' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</b></td></tr>';
}

/*Close off old table */
echo '</table>';