<?php

/* $Revision: 1.12 $ */
/*Cambios
1.- Se le agrego el  include('includes/SecurityFunctions.inc');
fecha: 09/12/2009
2.- se arreglo la variable $funcion*/
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Consulta de Ordenes de Compra Por Articulo');
include('includes/header.inc');
$funcion=27;
include('includes/SecurityFunctions.inc');

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem=$_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem=$_POST['SelectedStockItem'];
}

if (isset($_GET['OrderNumber'])){
	$OrderNumber=$_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber=$_POST['OrderNumber'];
}

if (isset($_GET['SelectedSupplier'])){
	$SelectedSupplier=$_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SelectedSupplier=$_POST['SelectedSupplier'];
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';


If ($_POST['ResetPart']){
     unset($SelectedStockItem);
}

If (isset($OrderNumber) && $OrderNumber!="") {
	if (!is_numeric($OrderNumber)){
		  prnMsg( _('The Order Number entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric'), 'error');
		  unset ($OrderNumber);
	} else {
		echo _('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	If ($SelectedSupplier) {
		echo _('For supplier') . ': ' . $SelectedSupplier . ' ' . _('and') . ' ';
		echo '<input type=hidden name="SelectedSupplier" value=' . $SelectedSupplier . '>';
	}
	If ($SelectedStockItem) {
		 echo _('for the part') . ': ' . $SelectedStockItem . ' ' . _('and') . ' <input type=hidden name="SelectedStockItem" value="' . $SelectedStockItem . '">';
	}
}

if ($_POST['SearchParts']){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'),'info');
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$i=0;
		$SearchString = "%";
		while (strpos($_POST['Keywords'], " ", $i)) {
			$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
			$i=strpos($_POST['Keywords']," ",$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) as qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.description " . LIKE . " '$SearchString'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif ($_POST['StockCode']){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord,
				stockmaster.units
			FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
			FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE purchorderdetails.completed=1
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

}

/* Not appropriate really to restrict search by date since user may miss older
ouststanding orders
	$OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
*/

if ($OrderNumber=="" OR !isset($OrderNumber)){

	echo _('No. Orden') . ': <input type=text name="OrderNumber" MAXLENGTH =8 size=9> ' . _('Almacén') . ':<select name="StockLocation"> ';
	$sql = 'SELECT loccode, 
		locationname 
		FROM areas a, tags t, locations l
		where t.areacode=a.areacode
		and l.tagref=t.tagref
		and a.areacode='.$_SESSION['DefaultArea'];	
	
	
	$resultStkLocs = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($myrow['loccode'] == $_POST['StockLocation']){
			echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			} else {
			echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
			}
		} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
			echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
			echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	}

	echo '</select> <input type=submit name="SearchOrders" VALUE="' . _('Buscar') . '">';
}

$SQL="SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
$result1 = DB_query($SQL,$db);
$permisoConsolidar= Havepermission($_SESSION['UserID'], 2272, $db);
?>

<hr>
<font size=1><?php echo _('Para buscar Ordenes de Compra de un producto específico, utilice las siguientes opciones :'); ?></font>
<input type=submit name="SearchParts" VALUE="<?php echo _('Buscar x Producto'); ?>">
<input type=submit name="ResetPart" VALUE="<?php echo _('Muestra Todos'); ?>">
<table>
<tr>
<td><font size=1><?php echo _('Categoría de Inventario'); ?>:</font>
<select name="StockCat">
<?php
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['categoryid']==$_POST['StockCat']){
		echo "<option selected VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	} else {
		echo "<option VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}
}
?>
</select>
<td><font size=1><?php echo _('Captura parte de la'); ?> <b><?php echo _('descripción'); ?></b>:</font></td>
<td><input type="Text" name="Keywords" size=20 maxlength=25></td></tr>
<tr><td></td>
<td><font SIZE 3><b><?php echo _('O'); ?> </b></font><font size=1><?php echo _('Captura parte del '); ?> <b><?php echo _('Código del Producto'); ?></b>:</font></td>
<td><input type="Text" name="StockCode" size=15 maxlength=18></td>
</tr>
</table>

<hr>

<?php

If ($StockItemsResult) {

	echo '<table cellpadding=2 colspan=7 BORDER=2>';
	$TableHeader = '<tr><td class="tableheader">' . _('Código') . '</td>
				<td class="tableheader">' . _('Descripción') . '</td>
				<td class="tableheader">' . _('Disponible') . '</td>
				<td class="tableheader">' . _('Ordenes') . '<br>' . _('Pendientes') . '</td>
				<td class="tableheader">' . _('Unid.') . '</td>
			</tr>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		printf("<td><input type=submit name='SelectedStockItem' VALUE='%s'</td>
		        <td>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
			$myrow['qord'],
			$myrow['units']);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available

	if (isset($OrderNumber) && $OrderNumber !="") {
		$SQL = "SELECT purchorders.orderno,
				suppliers.suppname,
				purchorders.orddate,
				purchorders.initiator,
				purchorders.requisitionno,
				purchorders.allowprint,
				suppliers.currcode,
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
			FROM purchorders,
				purchorderdetails,
				suppliers
			WHERE purchorders.orderno = purchorderdetails.orderno
			AND purchorders.supplierno = suppliers.supplierid
			AND purchorders.orderno=". $OrderNumber ."
			GROUP BY purchorders.orderno";
	} else {

	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

		if (isset($SelectedSupplier)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
					FROM purchorders,
						purchorderdetails,
						suppliers
					WHERE purchorders.orderno = purchorderdetails.orderno
					AND purchorders.supplierno = suppliers.supplierid
					AND  purchorderdetails.itemcode='". $SelectedStockItem ."'
					AND purchorders.supplierno='" . $SelectedSupplier ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					GROUP BY purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode";
			} else {
				$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
					FROM purchorders,
						purchorderdetails,
						suppliers
					WHERE purchorders.orderno = purchorderdetails.orderno
					AND purchorders.supplierno = suppliers.supplierid
					AND purchorders.supplierno='" . $SelectedSupplier ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					GROUP BY purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode";
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode,
						SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
					FROM purchorders,
						purchorderdetails,
						suppliers
					WHERE purchorders.orderno = purchorderdetails.orderno
					AND purchorders.supplierno = suppliers.supplierid
					AND purchorderdetails.itemcode='". $SelectedStockItem ."'
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					GROUP BY purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode";
			} else {
				$SQL = "SELECT purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode,
						sum(purchorderdetails.unitprice*purchorderdetails.quantityord) as ordervalue
					FROM purchorders,
						purchorderdetails,
						suppliers
					WHERE purchorders.orderno = purchorderdetails.orderno
					AND purchorders.supplierno = suppliers.supplierid
					AND purchorders.intostocklocation = '". $_POST['StockLocation'] . "'
					GROUP BY purchorders.orderno,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode";
			}

		} //end selected supplier
	} //end not order number selected

	$ErrMsg = _('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL,$db,$ErrMsg);

	if (DB_num_rows($PurchOrdersResult)>0){
		/*show a table of the orders returned by the SQL */

		echo '<table cellpadding=5 colspan=7 WIDTH=100%>';
		$TableHeader = '<tr><td class="tableheader">' . _('Ver') . '</td>
				<td class="tableheader">' . _('Provedor') . '</td>
				<td class="tableheader">' . _('Moneda') . '</td>
				<td class="tableheader">' . _('Requisición') . '</td>
				<td class="tableheader">' . _('Fecha Orden') . '</td>
				<td class="tableheader">' . _('Ordenado por') . '</td>
				<td class="tableheader">' . _('Total') . '</td>
				</tr>';

		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($PurchOrdersResult)) {


			if ($k==1){ /*alternate bgcolour of row for highlighting */
				echo '<tr bgcolor="#CCCCCC">';
				$k=0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k++;
			}

			$ViewPurchOrder = $rootpath . '/PO_OrderDetails.php?' . SID . 'OrderNo=' . $myrow['orderno'];

			$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
			$FormatedOrderValue = number_format($myrow['ordervalue'],2);
	/*						  View					   Supplier				    Currency			   Requisition		     Order Date			     Initiator				Order Total
						ModifyPage, $myrow["orderno"],	      $myrow["suppname"],		    $myrow["currcode"],	     $myrow["requisitionno"]	    $FormatedOrderDate,		     $myrow["initiator"]		     $FormatedOrderValue */
			printf("<td><a href='%s'>%s</a></td>
			        <td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td align=right>%s</td>
				</tr>",
				$ViewPurchOrder,
				$myrow['orderno'],
				$myrow['suppname'],
				$myrow['currcode'],
				$myrow['requisitionno'],
				$FormatedOrderDate,
				$myrow['initiator'],
				$FormatedOrderValue);

			$j++;
			If ($j == 12){
				$j=1;
				echo $TableHeader;
			}
		//end of page full new headings if
		}
		//end of while loop

		echo '</table>';
	} // end if purchase orders to show
}

echo '</form>';
include('includes/footer.inc');
?>