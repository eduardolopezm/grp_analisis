<?php
/* $Revision: 1.12 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php
*/

include ('includes/Add_SerialItemsParent.php');

/*Setup the Data Entry Types */
if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}
/*
        Entry Types:
             Keyed Mode: 'Qty' Rows of Input Fields. Upto X shown per page (100 max)
             Barcode Mode: Part Keyed, part not. 1st, 'Qty' of barcodes entered. Then extra data as/if
             necessary
             FileUpload Mode: File Uploaded must fulfill item requirements when parsed... no form based data
                 entry. 1-upload, 2-parse&validate, 3-bad>1 good>4, 4-import.
        switch the type we are updating from, w/ some rules...
                Qty < X   - Default to keyed
                X < Qty < Y - Default to barcode
                Y < Qty - Default to upload

        possibly override setting elsewhere.
*/

if (!isset($_POST['EntryType']) OR trim($_POST['EntryType']) == ''){
	if ($RecvQty <= 50) {
		$_POST['EntryType'] = 'KEYED';
	} //elseif ($RecvQty <= 50) { $EntryType = "BARCODE"; }
	else {
		$_POST['EntryType'] = 'FILE';
	}
}
	
$invalid_imports = 0;
$valid = true;
if (strrpos($_SERVER['PHP_SELF'],'ConfirmDispatchControlled_Invoice.php')>0){
	$showoptions=false;
}else{
	$showoptions=true;
};

global $tableheader;
/* Link to clear the list and start from scratch */
$EditLink =  '<br><div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier='.$identifier . '&EditControlled=true&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .'">'. _('Editar'). '</a> | ';
$RemoveLink = '<a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier='.$identifier .'&DELETEALL=YES&StockID=' . $LineItem->StockID .
	'&LineNo=' . $LineNo .'">'. _('Quitar todos'). '</a><br></div>';
if ($LineItem->Serialised==1){
	$tableheader .= '<tr>
			<th>'. _('Serial No').'</th>';
	if ($NoShowcost==false){
		$tableheader .= '<th>'. _('Costo Serie'). '</th>';
	}
	$tableheader .= '</Tr>';
} else {
	$tableheader = '<TR>
			<th>'. _('Lote'). ' #</th>
			<th class=tableheader>'. _('Cantidad'). '</th>
			</tr>';
}

//echo $EditLink . $RemoveLink;
echo '<table><tr><td>';
include('includes/InputSerialItemsExistingParent.php');
echo '</td></tr></table>';
?>
