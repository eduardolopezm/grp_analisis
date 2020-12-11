<?php
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
fecha: 09/12/2009
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;

include('includes/session.inc');

$title = _('Listado de Proveedores');

if (isset($_POST['orderby'])){
	$orderbby = $_POST['orderbby'];
}else{
	$orderbby = "porid";
}

if (!isset($_POST['PrintEXCEL']))  {
	include('includes/header.inc');
	$funcion=970;
	include('includes/SecurityFunctions.inc');
	
	echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';
	echo "<table border='0' cellspacing='0' cellpadding='0' width='100%'>";
	echo "<tr><td Class='page_title_text'>" . _('LISTADO DE PROVEEDORES...') . "</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td style='text-align:left;'>";
		echo "<input type='submit' name='ShowMoves' VALUE='" . _('Muestra Catalogo de Proveedores...') . "'>";
		echo "&nbsp;&nbsp;&nbsp;&nbsp;". _('ORDENAR POR: ') ."";
		echo "<select name='orderby'>";
			if ($_POST['orderby'] == "porid"){
				echo "<option selected value='porid'>" . _('POR ID') . "</option>";	
			}else{
				echo "<option value='porid'>" . _('POR ID') . "</option>";	
			}
			
			if ($_POST['orderby'] == "pornombre"){
				echo "<option selected value='pornombre'>" . _('POR NOMBRE') . "</option>";
			}else{
				echo "<option value='pornombre'>" . _('POR NOMBRE') . "</option>";
			}
			
		echo "</select>";
	echo "</td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "<tr><td style='text-align:left;'><input type='submit' Name='PrintEXCEL' Value='"._('Exporta a EXCEL')."'></td></tr>";		
	
	echo "</table>";
}

if (isset($_POST['PrintEXCEL'])) {
	
	header("Content-type: application/ms-excel");
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=excelreport.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';

	echo '<link href="css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "' . $rootpath . '/javascripts/MiscFunctions.js"></script>';
	
} 

$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$sql = "SELECT suppliers.supplierid, 
	suppliers.suppname, 
	suppliers.address1, 
	suppliers.address2, 
	suppliers.address3, 
	suppliers.address4, 
	suppliers.address5,
	suppliers.address6,
	suppliers.currcode,
	suppliers.taxid, 
	supplierstype.typename,
	suppliers.bankact,
	suppliers.bankpartics,
	paymentterms.terms,
	accountxsupplier.flagdiot
FROM suppliers LEFT JOIN supplierstype ON supplierstype.typeid = suppliers.typeid
LEFT JOIN paymentterms ON suppliers.paymentterms = paymentterms.termsindicator
LEFT JOIN accountxsupplier ON accountxsupplier.supplierid = suppliers.supplierid
GROUP BY suppliers.supplierid
ORDER BY suppliers.typeid";
if ($_POST['orderby'] == "porid"){
	$sql = $sql . ", suppliers.supplierid";
}elseif ($_POST['orderby'] == "pornombre"){
	$sql = $sql . ", suppliers.suppname";
}


//echo $sql;

$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because');
$MovtsResult = DB_query($sql, $db,$ErrMsg);

echo '<table cellpadding=5 CELLSPACING=4 BORDER=0>';
$tableheader = '<tr>
		<th>' . _('Codigo') . '</th>
		<th>' . _('Nombre') . '</th>
		<th>' . _('RFC') . '</th>
		<th>' . _('Direccion') . '</th>
		<th>' . _('CP') . '</th>
		<th>' . _('Telefono') . '</th>
		<th>' . _('Moneda') . '</th>
		<th>' . _('Termino de Pago') . '</th>
		<th>' . _('Cuenta Bancaria') . '</th>
		<th>' . _('Banco') . '</th>
		<th>' . _('Diot') . '</th>
		</tr>';
echo $tableheader;

$j = 1;
$k=0; //row colour counter

$totalqty = 0;
$tipoant = "";
while ($myrow=DB_fetch_array($MovtsResult)) {

	if ($k==1){
		echo '<tr class="OddTableRows">';
		$k=0;
	} else {
		echo '<tr class="EvenTableRows">';
		$k=1;
	}
	if ($tipoant != $myrow['typename']){
		echo "<tr><td colspan='11' style='font-weight:bold; background-color:yellow;'>" . strtoupper($myrow['typename']) . "</td></tr>";
		$tipoant = $myrow['typename'];
	}
	
	$diot = 'Si';
	if(empty($myrow['flagdiot'])) {
		$diot = 'No';
	}


	$DisplayTranDate = ConvertSQLDate($myrow['trandate']);
	echo "<td style='font-size:8pt; vertical-align:top;'>" . strtoupper($myrow['supplierid']) . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['suppname'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['taxid'] . "</td>";
	if (trim($myrow['address1']) != ""){
		echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['address1'] . ", Col. " . $myrow['address2'] . "<br>" .
		$myrow['address3'] . "</td>";
	}else{
		echo "<td style='font-size:8pt; vertical-align:top;'>&nbsp;</td>";
	}
	echo "<td align=right style='font-size:8pt; vertical-align:top;'>" . $myrow['address4'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['address6'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['currcode'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['terms'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['bankact'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $myrow['bankpartics'] . "</td>";
	echo "<td style='font-size:8pt; vertical-align:top;'>" . $diot . "</td>";
	echo "</tr>";
	$j++;
	
	if ($j == 16){
		$j=1;
		//echo $tableheader;
	}
//end of page full new headings if
}
//end of while loop

echo '</table><hr>';

if (isset($_POST['PrintEXCEL'])) {
	exit;
}
echo '</form>';

include('includes/footer.inc');

?>