<?php

/* $Revision: 1.11 $ */
/*Cambiosd
1.- Se le agrego el  include('includes/SecurityFunctions.inc');
fecha: 09/12/2009
2.- se arreglo la variable $funcion*/
$PageSecurity = 11;

include('includes/session.inc');
$title = _('Search Shipments');
include('includes/header.inc');
$funcion=36;
include('includes/SecurityFunctions.inc');

$title = "BUSQUEDA DE EMBARQUES";
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png"  alt="">' . ' ' . $title;


if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem=$_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem=$_POST['SelectedStockItem'];
}

if (isset($_GET['ShiptRef'])){
	$ShiptRef=$_GET['ShiptRef'];
} elseif (isset($_POST['ShiptRef'])){
	$ShiptRef=$_POST['ShiptRef'];
}

if (isset($_GET['SelectedSupplier'])){
	$SelectedSupplier=$_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SelectedSupplier=$_POST['SelectedSupplier'];
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method=post>';


If (isset($ShiptRef) && $ShiptRef!="") {
	if (!is_numeric($ShiptRef)){
		  echo '<br>';
		  prnMsg( _('The Shipment Number entered MUST be numeric') );
		  unset ($ShiptRef);
	} else {
		//echo _('Shipment Number'). ' - '. $ShiptRef;
	}
} 

echo '<div class="centre">';

echo '<table width="80%">';


echo '<tr>
				<td width=50% style="text-align:right">No Embarque </td>
				<td width=50%><input type=text name="ShiptRef" MAXLENGTH =10 size=10>
		 </tr>
		<tr>
			<td style="text-align:right">Almacen: </td>
			<td><select name="StockLocation"><option selected value="">Todos</option> ';

#$sql = "SELECT loccode, locationname FROM locations";
$sql = 'SELECT l.loccode, locationname
			FROM locations l, sec_loccxusser lxu
			where l.loccode=lxu.loccode and userid="'.$_SESSION['UserID'].'"';

					$resultStkLocs = DB_query($sql,$db);
					while ($myrow=DB_fetch_array($resultStkLocs)){
						if (isset($_POST['StockLocation'])){
							if ($myrow['loccode'] == $_POST['StockLocation']){
								echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
							} else {
								echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
							}
						} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
							$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
							echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
						} else {
							echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
						}
					}

					echo '</select></td></tr>';
					echo ' <tr>
				<td style="text-align:right">Estatus: </td>
			    <td><select name="OpenOrClosed">';
					if ($_POST['OpenOrClosed']==1){
						echo '<option selected VALUE=1>'. _('Embarques cerrados');
						echo '<option VALUE=0>'. _('Embarques abiertos');
					} else {
						$_POST['OpenOrClosed']=0;
						echo '<option VALUE=1>'. _('Embarques cerrados');
						echo '<option selected VALUE=0>'. _('Embarques abiertos');
					}
					echo '</select></td></tr>';

					echo '<tr>
			<td style="text-align:right"> Categoria</td>
			<td><select name="StockCat"><option selected value="">Todas</option>';

					$SQL='SELECT sto.categoryid, categorydescription FROM stockcategory sto, sec_stockcategory sec
			WHERE stocktype<>"D" AND sto.categoryid=sec.categoryid
			AND userid="'.$_SESSION['UserID'].'"
			ORDER BY categorydescription';
					$result1 = DB_query($SQL,$db);
					while ($myrow1 = DB_fetch_array($result1)) {
						if (isset($_POST['StockCat']) and $myrow1['categoryid']==$_POST['StockCat']){
							echo '<option selected VALUE="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
						} else {
							echo '<option VALUE="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'].'</option>';
						}
					}
					echo '</select></td></tr>';

					echo '<tr>
			<td style="text-align:right"> Descripcion producto</td>
			<td><input type="Text" name="Keywords" size=20 ></td>
		  </tr>
		  <tr>
			<td style="text-align:right">Codigo producto</td>
			<td><input type="Text" name="StockCode" size=15 ></td>
		  </tr>
					';

	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	echo '<tr><td colspan="2" style="text-align:center"><input type=submit name="SearchShipments" VALUE="'. _('Buscar'). '"></td></tr></table></div>';




if (isset($_POST['SearchShipments']) OR $SelectedStockItem!="") {

	If ($SelectedSupplier) {
		echo '<br>' ._('Proveedor'). ': '. $SelectedSupplier . ' ' . '<br>';
		//echo '<input type=hidden name="SelectedSupplier" value="'. $SelectedSupplier. '">';
	}
	If (isset($SelectedStockItem)) {
		echo _('Partida'). ': ' . $SelectedStockItem . '<br>';
		//echo '<input type=hidden name="SelectedStockItem" value="'. $SelectedStockItem. '">';
	}
	
	$where = "";
	
	If ($_POST['Keywords'] or $_POST['StockCode'] or $_POST['StockCat']){
	
		if ($_POST['Keywords']){
			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';
			
			$where.= " and stockmaster.description LIKE  '$SearchString'";
		}
		
		if ($_POST['StockCode'])
			$where.= " and stockmaster.stockid LIKE '%" . $_POST['StockCode'] . "%'";

		if ($_POST['StockCat'])
			$where.=" and categoryid='" . $_POST['StockCat']."'";
		
		
		$SQL = "SELECT stockmaster.stockid,
			description,
			SUM(locstock.quantity) AS qoh,
			units,
			SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid = locstock.stockid
		INNER JOIN purchorderdetails
			ON stockmaster.stockid=purchorderdetails.itemcode
		WHERE purchorderdetails.shiptref IS NOT NULL
		AND purchorderdetails.shiptref<>0
		$where		
		
		GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
		 	ORDER BY stockmaster.stockid
		";
		
		$ErrMsg = _('No Stock Items were returned from the database because'). ' - '. DB_error_msg($db);
		$StockItemsResult = DB_query($SQL,$db, $ErrMsg);
		

		If (DB_num_rows($StockItemsResult) > 0) {
		
			echo "<table cellpadding=2 colspan=7 BORDER=2>";
			$TableHeader = '<tr>
			<th>'. _('Code').'</th>
			<th>'. _('Description').'</th>
			<th>'. _('On Hand').'</th>
			<th>'. _('Orders') . '<br>' . _('Outstanding').'</th>
			<th>'. _('Units').'</th>
			</tr>';
		
			echo $TableHeader;
		
			$j = 1;
			$k=0; //row colour counter
		
			while ($myrow=DB_fetch_array($StockItemsResult)) {
		
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				/*
				 Code	 Description	On Hand		 Orders Ostdg     Units		 Code	Description 	 On Hand     Orders Ostdg	Units	 */
				printf('<td><input type=submit name="SelectedStockItem" VALUE="%s"</td>
						<td>%s</td>
						<td align=right>%s</td>
						<td align=right>%s</td>
						<td>%s</td></tr>',
						$myrow['stockid'], $myrow['description'], $myrow['qoh'], $myrow['qord'],$myrow['units']);
		
				$j++;
				If ($j == 15){
					$j=1;
					echo $TableHeader;
				}
				//end of page full new headings if
			}
			//end of while loop
		
			echo '</table>';
		
		}
		
	}
	else{
		
		if (isset($ShiptRef) && $ShiptRef !="") {
			$SQL = "SELECT shipments.shiptref,
				vessel,
				voyageref,
				suppliers.suppname,
				shipments.eta,
				shipments.closed
			FROM shipments INNER JOIN suppliers
				ON shipments.supplierid = suppliers.supplierid
			WHERE shipments.shiptref=". $ShiptRef;
			$SQL = $SQL." ORDER BY shipments.shiptref";
		} else {
			
		
			if ($_POST['StockLocation'])
				$where.=" and purchorders.intostocklocation = '". $_POST['StockLocation'] . "'";
			
			if (isset($SelectedSupplier)) 
				$where.=" and shipments.supplierid='" . $SelectedSupplier ."'";
		
			if (isset($SelectedStockItem)) 
				$where .= " and purchorderdetails.itemcode='". $SelectedStockItem ."'";

			$SQL = "SELECT DISTINCT shipments.shiptref, vessel, voyageref, suppliers.suppname, shipments.eta, shipments.closed
					FROM shipments INNER JOIN suppliers
						ON shipments.supplierid = suppliers.supplierid
					INNER JOIN purchorderdetails
						ON purchorderdetails.shiptref=shipments.shiptref
					INNER JOIN purchorders
						ON purchorderdetails.orderno=purchorders.orderno
					
					WHERE  shipments.closed=" . $_POST['OpenOrClosed']."
					$where		
					";
			$SQL = $SQL." ORDER BY shipments.shiptref";
		}				
					
		//echo "<pre>$SQL";
		$ErrMsg = _('No shipments were returned by the SQL because');
		$ShipmentsResult = DB_query($SQL,$db,$ErrMsg);
		
		
		if (DB_num_rows($ShipmentsResult)>0){
			/*show a table of the shipments returned by the SQL */
		
			echo '<table cellpadding=2 colspan=7 WIDTH=100%>';
			$TableHeader = '<tr>
				<th>'. _('Embarque'). '</th>
				<th>'. _('Proveedor'). '</th>
				<th>'. _('Transporte/Agente aduanal'). '</th>
				<th>'. _('No Pedimiento'). '</th>
				<th>'. _('Fecha'). '</th>
				</tr>';
		
			echo $TableHeader;
		
			$j = 1;
			$k=0; //row colour counter
			while ($myrow=DB_fetch_array($ShipmentsResult)) {
		
		
				if ($k==1){ /*alternate bgcolour of row for highlighting */
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}
		
				$URL_Modify_Shipment = $rootpath . '/Shipments.php?' . SID . 'SelectedShipment=' . $myrow['shiptref'];
				$URL_View_Shipment = $rootpath . '/ShipmentCosting.php?' . SID . 'SelectedShipment=' . $myrow['shiptref'];
		
				$FormatedETA = ConvertSQLDate($myrow['eta']);
				/* ShiptRef   Supplier  Vessel  Voyage  ETA */
		
				if ($myrow['closed']==0){
		
					$URL_Close_Shipment = $URL_View_Shipment . '&Close=Yes';
		
					printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s">'._('Costing').'</a></td>
					<td><a href="%s">'._('Modify').'</a></td>
					<td><a href="%s"><b>'._('Close').'</b></a></td>
					</tr>',
							$myrow['shiptref'],
							$myrow['suppname'],
							$myrow['vessel'],
							$myrow['voyageref'],
							$FormatedETA,
							$URL_View_Shipment,
							$URL_Modify_Shipment,
							$URL_Close_Shipment);
		
				} else {
					printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s">'._('Costing').'</a></td>
					</tr>',
							$myrow['shiptref'],
							$myrow['suppname'],
							$myrow['vessel'],
							$myrow['voyageref'],
							$FormatedETA,
							$URL_View_Shipment);
				}
				$j++;
				If ($j == 15){
					$j=1;
					echo $TableHeader;
				}
				//end of page full new headings if
			}
			//end of while loop
		
			echo '</table>';
		} // end if shipments to show
		
	} 

}

echo '</form>';
include('includes/footer.inc');
?>
