<?php
/* $Revision: 1.9 $ */
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
global $LineItem;
//$LineNo = initPvar('LineNo', $LineNo);
if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

echo '<DIV Align=Center>';
echo '<TABLE style="text-align: center; margin: 0 auto">';
echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

/*Display the batches already entered with quantities if not serialised */
foreach ($LineItem->SerialItems as $Bundle){

	$RowCounter++;
	//only show 1st 10 lines
	if ($RowCounter < 10){
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		echo '<TD>' . $Bundle->BundleRef . '</TD>';
		echo '<TD>' . $Bundle->Customs . '</TD>';
		echo '<TD>' . $Bundle->CustomsNumber . '</TD>';
		echo '<TD>' . $Bundle->CustomsDate . '</TD>';
		echo '<TD>0</TD>';
		
		if ($LineItem->Serialised==0){
			echo '<TD ALIGN=RIGHT>' . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . '</TD>';
		}
	}

	$TotalQuantity += $Bundle->BundleQty;
}


/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo '<TR><TD ALIGN=RIGHT><B>'.  _('Cantidad Total'). ': ' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</B></TD></TR>';
	echo '<TR><TD><HR></TD></TR>';
} else {
	echo '<TR><TD ALIGN=RIGHT><B>'. _('Cantidad Total'). ':</B></TD><TD ALIGN=RIGHT><B>' . number_format($TotalQuantity,$LineItem->DecimalPlaces) . '</B></TD></TR>';
	echo '<TR><TD COLSPAN=2><HR></TD></TR>';
}

echo '</TABLE><HR>';
//echo "<TABLE><TR><TD>";

//DISPLAY FILE INFO
// do some inits & error checks...
$ShowFileInfo = false;
if (!isset($_SESSION['CurImportFile']) ){
		$_SESSION['CurImportFile'] = '';
		$LineItem->SerialItemsValid=false;
}
if ($_FILES['ImportFile']['name'] == '' && $_SESSION['CurImportFile'] == ''){
	$msg = _('Por favor seleccione un archivo para importarlo');
	prnMsg($msg);
	$LineItem->SerialItemsValid=false;
	echo '</TD></TR></TABLE>';
	include('includes/footer.inc');
	exit();
}
if ($_FILES['ImportFile']['error'] != '' && !isset($_SESSION['CurImportFile'])){
		echo _('Hubo un problema con el archivo subido') . '. ' . _('Recibidos').':<BR>'.
				 _('Nombre').':'.$_FILES['ImportFile']['name'].'<br>'.
				 _('Tamaño').':'.number_format($_FILES['ImportFile']['size']/1024,2).'kb<br>'.
				 _('Tipo').':'.$_FILES['ImportFile']['type'].'<br>';
		echo '<br>'._('El error fue').' '.$_FILES['ImportFile']['error'].'<br>';
		$LineItem->SerialItemsValid=false;
		echo '</TD></TR></TABLE>';
		include('includes/footer.inc');
		exit();
} elseif ($_FILES['ImportFile']['name']!=''){
	//User has uploaded importfile. reset items, then just 'get hold' of it for later.

	$LineItem->SerialItems=array();
	$LineItem->SerialItemsValid=false;
	$_SESSION['CurImportFile']['Processed']=false;
	$_SESSION['CurImportFile'] = $_FILES['ImportFile'];
	$_SESSION['CurImportFile']['tmp_name'] = './reports/'.$LineItem->StockID.'_'.$LineNo.'_'.uniqid(4);
	if (!move_uploaded_file($_FILES['ImportFile']['tmp_name'],$_SESSION['CurImportFile']['tmp_name'])){
		prnMsg(_('Error moviendo el archivo temporal') . '. ' . _('Revisa la configuración'),'error' );
		$LineItem->SerialItemsValid=false;
		echo '</TD></TR></TABLE>';
		include('includes/footer.inc');
		exit;
	}

	if ($_FILES['ImportFile']['name']!=''){
		echo _('Recibido exitosamente').':<br>';
		$ShowFileInfo = true;
	}
} elseif (isset($_SESSION['CurImportFile']) && $_SESSION['CurImportFile']['Processed'] ) {
	//file exists, some action performed...
	echo _('Trabajando con'). ':<br>';
	$ShowFileInfo = true;
} elseif ($LineItem->SerialItemsValid && $_SESSION['CurImportFile']['Processed']){
	$ShowInfo = true;
}
if ($ShowFileInfo){
	/********************************************
	  Display file info for visual verification
	********************************************/
	echo '<TABLE>';
	echo '<tr><td>'._('Nombre').':</td><td>'.$_SESSION['CurImportFile']['name'].'</td></tr>
		<tr><td>'. _('Tamaño') .':</td><td>' . number_format($_SESSION['CurImportFile']['size']/1024,4) . 'kb</td></tr>
		<tr><td>'. _('Tipo') .':</td><td>' . $_SESSION['CurImportFile']['type'] . '</td></tr>
		<tr><td>'. _('Nombre Temporal') .':</td><td>' . $_SESSION['CurImportFile']['tmp_name'] . '</td></tr>
	   <tr><td>'. _('Estado') .':</td><td>' . 
	($LineItem->SerialItemsValid?getMsg(_('Valido'),'success'):getMsg(_('No valido'),'error')) . '</td></tr>
	   </TABLE>'.
		$invalid_imports.' '. _('de') .' '.$TotalLines.' '. _(' registros no validos').'<br>';
	$filename = $_SESSION['CurImportFile']['tmp_name'];
}  

if (!$LineItem->SerialItemsValid && !$_SESSION['CurImportFile']['Processed']){
		// IF all items are not valid, show the raw first 10 lines of the file. maybe it will help.
	$filename = $_SESSION['CurImportFile']['tmp_name'];
		$handle = fopen($filename, 'r');
		$i=0;
		while (!feof($handle) && $i < 10) {
				$contents .= fgets($handle, 4096);
				$i++;
		}
		fclose($handle);

	echo '<br><form method=POST>
			<input type=submit name=ValidateFile value=' . _('Validar Archivo') . '>
			<input type=hidden name=LineNo value="' . $LineNo . '">
			<input type=hidden name=StockID value="' . $StockID . '">
			<input type=hidden name=EntryType value="FILE">
			</form>
			<p>'. _('Primeras lineas del archivo'). '....
			<hr width=15%>
		<pre>';

	echo $contents;

	echo '</pre>';

} else {
		//Otherwise we have all valid records. show the first (100)  for visual verification.
	echo _('Se muestran los primeros 100 registros analizados');
	echo '<hr width=20%>';
	foreach($LineItem->SerialItems as $SItem){
		echo $SItem->BundleRef.'<br>';
		$i++;
		if ($i == 100) {
			break;
				}
	}
}

?>
