<?php

$PageSecurity = 2;
$rootpath = "https://23.111.130.190/ap_grp";
include('includes/session.inc');

$title = _('Estatus de Inventario');

$funcion=58;
include('includes/header.inc');
include('includes/SecurityFunctions.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}
if (isset($_GET['Barcode'])){
	$Barcode = trim(strtoupper($_GET['Barcode']));
} elseif (isset($_POST['StockID'])){
	$Barcode = trim(strtoupper($_POST['Barcode']));
} else {
	$Barcode = '';
}

$StockIDTemp = $StockID;

if (isset($_POST['StockLocation']))
	$StockLocation = $_POST['StockLocation'];
else
	$StockLocation = 0;

// This is already linked from this page
//echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</a><br>';

if($_SESSION['SearchBarcode'] == 1) {
	if(isset($Barcode) and $Barcode <> ""){
		$condStockId = "(stockid='$StockID'
		OR barcode = '$Barcode')";
	}else{
		$condStockId = "stockid='$StockID'";
	}
	
} else {
	$condStockId = "stockid='$StockID'";
}
$sql2 = "SELECT description,
                           units,
                           mbflag,
                           decimalplaces,
                           serialised,
                           controlled,
                           stockid
                    FROM
                           stockmaster
                    WHERE
                           $condStockId";

$result = DB_query($sql2,$db,
                           _('No pude obtener el código requerido'),
                           _('El SQL que utilice fue'));

$myrow = DB_fetch_row($result);

if(empty($StockID) == FALSE) {
	$StockID = trim(strtoupper($myrow[6]));
}
$DecimalPlaces = $myrow[3];
$Serialised = $myrow[4];
$Controlled = $myrow[5];
$Mbflag=  $myrow[2];
echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventarios') . '" alt=""><b>' . ' ' . $StockIDTemp . ' - ' . $myrow['0'] . ' : ' . _('Unidad Medida') . ' : ' . $myrow[1] . '';

$Its_A_KitSet_Assembly_Or_Dummy =False;
if ($myrow[2]=='K'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	prnMsg( _('Este es un producto definido como KIT') . ', ' . _('Solo la cantidad pendiente en ordenes de venta abiertas es mostrada'),'info');
} elseif ($myrow[2]=='A'){
	$Its_A_KitSet_Assembly_Or_Dummy =True;
	prnMsg(_('Este es un producto armado, no puede tener existencias') . ', ' . _('Solo la cantidad pendiente en ordenes de venta abiertas es mostrada'),'info');
} elseif ($myrow[2]=='D'){
	$Its_A_KitSet_Assembly_Or_Dummy =false;
	prnMsg( _('Este es un producto Servicio/Mano de obra, no puede tener existencias') . ', ' . _('Solo la cantidad pendiente en ordenes de venta abiertas es mostrada'),'info');
}

echo '<div class="centre"><form action="' . $_SERVER['PHP_SELF'] . '?'. SID . '" method=post>';
echo '<div class="w75p text-center"><span class="text-right w20p fl p10 mb20">'._('X Código') . ': </span><input class="wA mb20 mr10 mt5 fl form-control" type=text name="StockID" size=21 value="' . $StockIDTemp . '" maxlength=20><input class="botonVerde mb20 fl wA h35" type=submit name="ShowStatus" VALUE="' . _('Muestra Estado de Existencias') . '"><a class="botonVerde p5 wA fl" href="' . $rootpath . '/SelectProduct.php?' . SID . '">Regresar a la búsqueda de productos</a></div>';
			
$sql = "SELECT locstock.loccode,
               locations.locationname,
               locstock.quantity,
               locstock.reorderlevel,
			   locstock.qtybysend,
	      	   locations.managed,
	       	   ontransit,
	           locstock.localidad,flaglocationto
               FROM locstock,
                    locations
               WHERE locstock.loccode=locations.loccode AND
                     locstock.stockid = '" . $StockID . "'
		      and (locations.temploc=0 or (locations.temploc in(1,4) and locstock.quantity <> 0))";

if ($_SESSION['ShowOnlyOnStock'] == 1) {
	$sql .= " AND (locstock.quantity > 0 OR locstock.reorderlevel > 0)"; 
}

$sql .= " ORDER BY locstock.loccode";

//echo $sql;
$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that was used to update the stock item and failed was');
$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<br><table cellpadding=2 BORDER=1 class="w100p tableHeaderVerde" >';

if ($Its_A_KitSet_Assembly_Or_Dummy == True){
	$tableheader = '<tr>
			<th>' . _('Localizacion') . '</th>
			<th>' . _('Demanda') . '</th>
			</tr>';
} else {
	$tableheader = '<tr>
			<th rowspan=2>' . _('Localizacion') . '</th>
			<th rowspan=2>' . _('Existencias') . '</th>
			<th rowspan=2>' . _('En Tránsito') . '</th>			
			<th rowspan=2>' . _('Nivel de Reorden') . '</font></th>
			<th rowspan=2>' . _('En Embarque') . '</th>
			<th rowspan=2>' . _('En Orden Trabajo') . '</th>
			<th colspan=2> ' . _('En Pedidos Vta.') . '</th>
			<th rowspan=2>' . _('Disponible') . '</th>
			<th rowspan=2>' . _('En Orden de Compra') . '</th>
			<th rowspan=2>' . _('Ultima Compra') . '</th>
			<th rowspan=2>' . _('Fecha Compra') . '</th>
			<th rowspan=2>' . _('Ubicacion') . '</th>
			</tr>';
	$tableheader=$tableheader.'<tr>
	<th > ' . _('Cerrados') . '</th>
	<th > ' . _('Abiertos') . '</th>
	</tr>';
}

echo $tableheader;
$j = 1;
$k=0; //row colour counter

$onshippingtotal = 0;
$totexistencias = 0;
$tottransito = 0;
$totreorden = 0;
$totpedidos = 0;
$totdisponible = 0;
$totcompras = 0;

while ($myrow=DB_fetch_array($LocStockResult)) {
	//echo '<br>localidad:'.$myrow['loccode'].' localidad2:'.$myrow['flaglocationto'];
	
	//echo '<br><br>localidad3:'.$myrow['loccode'].'localidad4:'.$myrow['flaglocationto'];
	
	$ontransit = $myrow['ontransit'];
	
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	
	$sql = "SELECT SUM(case when salesorders.quotation in (0,6) then salesorderdetails.quantity-salesorderdetails.qtyinvoiced else 0 end) AS dem,
		SUM(case when salesorders.quotation=2 then salesorderdetails.quantity-salesorderdetails.qtyinvoiced else 0 end) AS abiertos
                 FROM salesorderdetails,
                      salesorders
                 WHERE salesorders.orderno = salesorderdetails.orderno AND
                 salesorderdetails.fromstkloc='" . $myrow['loccode'] . "' AND
                 salesorderdetails.completed=0 AND
		 salesorders.quotation in (".$_SESSION['DemandQtyQuotations'].") AND
                 salesorderdetails.stkcode='" . $StockID . "'";

	$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
	if (DB_num_rows($DemandResult)==1){
	  $DemandRow = DB_fetch_row($DemandResult);
	  if (is_null($DemandRow[0])){
	  	$DemandRow[0]=0;
	  }
	  if (is_null($DemandRow[1])){
	  	$DemandRow[1]=0;
	  }//
	  $DemandQty =  $DemandRow[0];
	  $DemandQtyOpen=  $DemandRow[1];
	} else {
	  $DemandQty =0;
	  $DemandQtyOpen =0;
	}

	//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.//
	$sql = "SELECT SUM(case when salesorders.quotation in (0,6) then (salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity else 0 end) AS dem,
		       SUM(case when salesorders.quotation=2 then (salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity else 0 end) AS abiertos
                 FROM salesorderdetails,
                      salesorders,
                      bom,
                      stockmaster
                 WHERE salesorderdetails.stkcode=bom.parent AND
                       salesorders.orderno = salesorderdetails.orderno AND
                       salesorders.fromstkloc='" . $myrow['loccode'] . "' AND
                       salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0 AND
                       bom.component='" . $StockID . "' AND stockmaster.stockid=bom.parent AND
                       stockmaster.mbflag='A'
		       AND salesorders.quotation in (".$_SESSION['DemandQtyQuotations'].")";
//echo '<pre>sql:'.$sql;
	$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		if (is_null($DemandRow[0])){
			$DemandRow[0]=0;
		}
		if (is_null($DemandRow[1])){
			$DemandRow[1]=0;
		}
		$DemandQty += $DemandRow[0];
		$DemandQtyOpen += $DemandRow[1];
	}
	
	if($DemandQty > 0){
		//exit;
	}
	if ($Its_A_KitSet_Assembly_Or_Dummy == False){

		$sql = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qoo,max(purchorders.orderno) as ordencompra,purchorders.orddate
                   	FROM purchorderdetails
                   	INNER JOIN purchorders ON purchorderdetails.orderno=purchorders.orderno and purchorders.status <> 'Cancelled' 
                   	WHERE purchorders.intostocklocation in (" . $myrow['flaglocationto'] . ")  AND
                   	purchorderdetails.itemcode='" . $StockID . "' GROUP BY purchorders.orddate
			order by purchorders.orderno desc ";
		$ErrMsg = _('The quantity ordencompraon order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
			$ultimacompra=$QOORow[1];
			$FechaCompra=$QOORow[2];
		} else {
			$QOO = 0;
		}
		//echo '<br>oc:'.$QOO;
		$WOO=0;
		//Also the on work order quantities
		$sql = "SELECT SUM(qtypu/*(woitems.qtyreqd - woitems.qtyrecd)*/) AS woqtydemo
				FROM woitems INNER JOIN worequirements
				ON woitems.stockid=worequirements.parentstockid
				INNER JOIN workorders
				ON woitems.wo=workorders.wo
				AND woitems.wo=worequirements.wo
				WHERE workorders.loccode in (" . $myrow['flaglocationto'] . ")
				AND worequirements.stockid='" . $StockID . "'
				AND workorders.closed=0";
		$ErrMsg = _('The quantity on work orders for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg, $DbgMsg);
		
		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$WOO +=  $QOORow[0];
		}else{
			$WOO=0;
		}
		
		$endemanda=$myrow['quantity'];
		if($endemanda>0){
			$bgcolor='bgcolor="#EEAABB"';
		}else{
			$bgcolor='';
		}
		
	        //positivos color=verde , negativos = rojo , vacios = blanco			
		
		$quantity   = number_format($myrow['quantity'], $DecimalPlaces);
		$vontransit = number_format($myrow['ontransit'], $DecimalPlaces);
		$reorderlevel = number_format($myrow['reorderlevel'], $DecimalPlaces);
		$onshipping = number_format($myrow['qtybysend'], $DecimalPlaces);
		$vDemandQty = number_format($DemandQty, $DecimalPlaces);
		$QOOWo= number_format($WOO, $DecimalPlaces);
		/*
		echo "<br>A: " . $myrow['quantity']; 
		echo "<br>B: " . $myrow['ontransit']; 
		echo "<br>C: " . $myrow['qtybysend'];
		echo "<br>D: " . $WOO;
		echo "<br>E: " . $DemandQty;
		*/
		$available = number_format($myrow['quantity'] - $myrow['ontransit'] - $myrow['qtybysend']-$WOO - $DemandQty, $DecimalPlaces);
		//exit;
		$totexistencias = $totexistencias + $myrow['quantity'];
		$tottransito = $tottransito + $myrow['ontransit'];
		$totreorden = $totreorden + $myrow['reorderlevel'];
		$totpedidos = $totpedidos + $DemandQty;
		$totwo=$totwo+$WOO;
		$totdisponible = $totdisponible + ($myrow['quantity'] - $myrow['ontransit'] - $myrow['qtybysend'] - $DemandQty-$WOO) ;
		
		$totpedidosopen=$totpedidosopen+$DemandQtyOpen;
		$totcompras = $totcompras + $QOO;

		
		
		//$available = number_format($quantity - $DemandQty - $ontransit, $DecimalPlaces);
		
		#if ($available<=0)
		#{
		#   $available = number_format($myrow['quantity'] - $DemandQty + $ontransit, $DecimalPlaces);
		#}
		#else
		#{
		  #$available = number_format($myrow['quantity'] - $DemandQty - $ontransit, $DecimalPlaces);
		#}
		
		$vQOO = number_format($QOO, $DecimalPlaces);
		

		if ($myrow['quantity'] == "0"){
			$quantity = "&nbsp;";
		}


		if ($myrow['ontransit'] == "0"){
			$vontransit = "&nbsp;";
			
		}
		
		
		if ($myrow['reorderlevel'] == "0"){
			$reorderlevel = "&nbsp;";
			
		}
		
		if ($onshipping == "0"){
			$onshipping = "&nbsp;";
		}
		
		if ($DemandQty == "0"){
			$vDemandQty = "&nbsp;";
			
		}
		if ($DemandQtyOpen == "0"){
			$DemandQtyOpen = "&nbsp;";
			
		}

		if ($available == "0"){
			$available = "&nbsp;";
			
		}

		if ($vQOO == "0"){
			$vQOO = "&nbsp;";
			
		}
		
		if ($WOO == "0"){
			$WOO = "&nbsp;";
				
		}
		

		if ($myrow['quantity'] == 0 && $myrow['ontransit'] == 0 && $myrow['reorderlevel'] == 0 && $DemandQty == 0 && $available == 0 && $vQOO == 0){
			$bgcolor='bgcolor="#FFFFFF"';
		}
		elseif ($myrow['quantity'] < 0 || $myrow['ontransit'] < 0 || $myrow['reorderlevel'] < 0 || $DemandQty < 0 || $available < 0 || $vQOO < 0){
			$bgcolor='bgcolor="#F5A9A9"';  //color rojo
		}
		else
			$bgcolor='bgcolor="#CCFF99"';
		
		
		echo '<td '.$bgcolor1.'>' . $myrow['loccode']  .' ' . $myrow['locationname'] . '</td>';
		
		printf("<td style='text-align:center;' ".$bgcolor." >%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td nowrap style='text-align:center;' ".$bgcolor."><a href='StockStatus.php?VerEmbarque=yes&StockID=".$StockID."&loccode=".$myrow['loccode']."'>%s</a></td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			<td style='text-align:center;' ".$bgcolor.">%s</td>
			",
			$quantity,//number_format($myrow['quantity'] , $DecimalPlaces),	
			$vontransit,//number_format($myrow['ontransit'], $DecimalPlaces),			
			$reorderlevel,//number_format($myrow['reorderlevel'], $DecimalPlaces),
			
			$onshipping,
			$WOO,
			//number_format($DemandQty, $DecimalPlaces),
			 $vDemandQty,
			$DemandQtyOpen,
			
			$available, //number_format($myrow['quantity'] - $DemandQty - $ontransit, $DecimalPlaces),
			$vQOO ,//number_format($QOO, $DecimalPlaces),
			$ultimacompra,
			$FechaCompra,
			$myrow['localidad']
			);

		if ($Serialised ==1){ /*The line is a serialised item*/

			echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?' . SID . '&Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' .$StockID . '">' . _('Serial Numbers') . '</a></td></tr>';
		} elseif ($Controlled==1){
			echo '<td><a target="_blank" href="' . $rootpath . '/StockSerialItems.php?' . SID . '&Location=' . $myrow['loccode'] . '&StockID=' .$StockID . '">' . _('Batches') . '</a></td></tr>';
		}

	} else {
	/* It must be a dummy, assembly or kitset part */

		printf("<td>%s</td>
			<td align=right>%s</td>
			</tr>",
			$myrow['locationname'],
			number_format($DemandQty, $DecimalPlaces)
			);
	}
	
	
	
//end of page full new headings if
}

echo $tableheader;

echo '<tr><td>TOTALES</td><td  style="text-align:center">';
	
	echo $totexistencias . '</td><td  style="text-align:center">';
	echo $tottransito . '</td><td  style="text-align:center">';
	echo $totreorden. '</td><td  style="text-align:center">';
	echo $onshippingtotal. '</td><td  style="text-align:center">';
	echo $totwo. '</td><td  style="text-align:center">';
	echo $totpedidos. '</td><td  style="text-align:center">';
	echo $totpedidosopen. '</td><td  style="text-align:center">';
	echo $totdisponible. '</td><td  style="text-align:center">';
	echo $totcompras . '</td></tr>';
	
//end of while loop
echo '</table>';

if (isset($_GET['DebtorNo'])){
	$DebtorNo = trim(strtoupper($_GET['DebtorNo']));
} elseif (isset($_POST['DebtorNo'])){
	$DebtorNo = trim(strtoupper($_POST['DebtorNo']));
} elseif (isset($_SESSION['CustomerID'])){
	$DebtorNo=$_SESSION['CustomerID'];
}

if ($DebtorNo) { /* display recent pricing history for this debtor and this stock item */

	$sql = "SELECT stockmoves.trandate,
				stockmoves.qty,
				stockmoves.price,
				stockmoves.discountpercent
			FROM stockmoves
			WHERE stockmoves.debtorno='" . $DebtorNo . "'
				AND stockmoves.type=10
				AND stockmoves.stockid = '" . $StockID . "'
				AND stockmoves.hidemovt=0
			ORDER BY stockmoves.trandate DESC";

	/* only show pricing history for sales invoices - type=10 */

	$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
	$DbgMsg = _('The SQL that failed was') . ' ';

	$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$k=1;
	while ($myrow=DB_fetch_array($MovtsResult)) {
	  if ($LastPrice != $myrow['price'] or $LastDiscount != $myrow['discount']) { /* consolidate price history for records with same price/discount */
	    if ($qty) {
	    	$DateRange=ConvertSQLDate($FromDate);
	    	if ($FromDate != $ToDate) {
	        	$DateRange .= ' - ' . ConvertSQLDate($ToDate);
	     	}
	    	$PriceHistory[] = array($DateRange, $qty, $LastPrice, $LastDiscount);
	    	$k++;
	    	if ($k > 9) break; /* 10 price records is enough to display */
	    	if ($myrow['trandate'] < FormatDateForSQL(time() - 366*86400))
	    	  break; /* stop displaying pirce history more than a year old once we have at least one  to display */
	    }
		$LastPrice = $myrow['price'];
		$LastDiscount = $myrow['discount'];
	    $ToDate = $myrow['trandate'];
		$qty = 0;
	  }
	  $qty += $myrow['qty'];
	  $FromDate = $myrow['trandate'];
	}
	if (isset($qty)) {
		$DateRange = ConvertSQLDate($FromDate);
		if ($FromDate != $ToDate) {
	   		$DateRange .= ' - '.ConvertSQLDate($ToDate);
		}
		$PriceHistory[] = array($DateRange, $qty, $LastPrice, $LastDiscount);
	}
	if (isset($PriceHistory)) {
	  echo '<p>' . _('Historial de Precios del producto') . ' ' . $StockID . ' ' . _('to') . ' ' . $DebtorNo;
	  echo '<table cellpadding=2 BORDER=0>';
	  $tableheader = "<tr>
			<th>" . _('Rango de Fechas') . "</th>
			<th>" . _('Cantidad') . "</th>
			<th>" . _('Precio') . "</th>
			<th>" . _('Descuento') . "</th>
			</tr>";

	  $j = 0;
	  $k = 0; //row colour counter

	  foreach($PriceHistory as $ph) {
		$j--;
		If ($j < 0 ){
			$j = 11;
			echo $tableheader;
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

			printf("<td>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
			<td align=right>%s%%</td>
			</tr>",
			$ph[0],
			number_format($ph[1],$DecimalPlaces),
			number_format($ph[2],2),
			number_format($ph[3]*100,2)
			);
	  }
	 echo '</table>';
	 }
	//end of while loop
	else {
	  echo '<p>'._('No hay historia de ventas') . ' ' . $StockID . ' ' . _('para cliente') . ' ' . $DebtorNo;
	}
}
echo '<br>';
//end of displaying price history for a debtor
$sql = "SELECT locstock.loccode,
               locations.locationname,
               locstock.quantity,
               locstock.reorderlevel,
	       locations.managed,
	       ontransit
               FROM locstock,
                    locations
               WHERE locstock.loccode=locations.loccode AND
                     locstock.stockid = '" . $StockID . "'
		     and (locations.temploc=0 or (locations.temploc=1 and locstock.quantity <> 0))
               ORDER BY locstock.loccode";
//echo $sql;
$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that was used to update the stock item and failed was');
$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
while ($myrowloc = DB_fetch_array($LocStockResult)) {

	$SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
		locations.locationname as destino, l.locationname as origen
		FROM loctransfers INNER JOIN locations
		ON loctransfers.recloc=locations.loccode
		INNER JOIN locations as l
		ON loctransfers.shiploc=l.loccode
		WHERE (shipqty-recqty) > 0 /*recqty='0'*/
		AND recloc='".$myrowloc['loccode']."'
		AND stockid='".$StockID."'";
		$Result= DB_query($SQL,$db);
		//
if ( DB_num_rows($Result) > 0 ) {
	echo "<table cellpadding=5 border=1>
	      <tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS DE ENTRADA') . "</p></td></tr>";
	$tableheader2 = "<tr>
			<th>" . _('# Linea') . "</th>
			<th>" . _('# Transferencia') . "</th>
			<th>" . _('Almacén de Origen') . "</th>
			<th>" . _('Almacén Destino') . "</th>
			<th>" . _('Fecha') . "</th>
			<th>" . _('Cantidad') . "</th>
			</tr>";
	echo $tableheader2;
		$j=1;
		$k=0; //row colour counter
		$suma2=0;
		while ($myrow = DB_fetch_array($Result)) {
			$suma2=$suma2 + $myrow['shipqty'];	
			if ($k==1){
					echo '<tr style="font-weight:normal" >';                                         
					$k=0;
				} else {
					echo '<tr style="font-weight:normal">'; 
					$k=1;
				}
		 printf("<td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td style='font-weight:normal;font-size:9pt;text-align:right;'>%s</td>
			</tr>",
			$j,
			$myrow['reference'],
			$myrow['origen'],
			$myrow['destino'],
			$myrow['shipdate'],
			$myrow['shipqty']);
		    $j++;
		    $_POST['shipqty']=$myrow['shipqty'];
		    $cantidad2=$_POST['shipqty'];
		}
	echo "</tr>";
	echo '<tr><td colspan=5 style="text-align:right;">TOTAL : </td><td style="text-align:right;">'.($suma2).'</td></tr>';
	echo "</table>";
	}

}

/*****Cambio para visualizar el embarque*****/
if($_GET['VerEmbarque']=='yes'){
	
	$SQLEmbarque="
						select 
								*
						from 
								shippingorderdetails join shippingorders 
						on 
								shippingorderdetails.`shippingno`= shippingorders.`shippingno`
						join 
								debtortrans 
						on 
								shippingorders.`debtortransid`= debtortrans.id 
						join 
								debtorsmaster 
						on
								`debtortrans`.debtorno = debtorsmaster.`debtorno`
						join 
								tags 
						on
								tags.`tagref`= debtortrans.tagref
									
						where 
								shippingorderdetails.`stockid`='".$StockID."' 
						and 
								shippingorderdetails.`loccode`='".$_GET['loccode']."'
										";
	//echo $SQLEmbarque;
	$ResultEmbarque= DB_query($SQLEmbarque,$db);
	
	

echo '<div>
		<table cellpadding=2 BORDER=1 class="tableHeaderVerde">
			<tr>
				<th>No. Embarque</td>
				<th>No. Pedido</td>
				<th>No. Factura</td>
				<th>Cliente</td>	
				<th>Unidad de Negocio</td>
				<th>Fecha Entrega</td>
				
			</tr>';
	while($myrowEmbarque = DB_fetch_array($ResultEmbarque)){	
			echo '<tr>
				<td>'.$myrowEmbarque['name'].'</td>	
				<td>'.$myrowEmbarque['shippingno'].'</td>	
				<td>'.$myrowEmbarque['folio'].'</td>
				<td>'.$myrowEmbarque['brname'].'</td>	
				<td>'.$myrowEmbarque['tagname'].'</td>	
				<td>'.$myrowEmbarque['shippingdate'].'</td>						
			</tr>';
	}
		
		echo '</table>';

}

echo '</form></div>';

/*****Cambio para visualizar el embarque*****/





/*echo '<div class="centre">';
echo '<br><a href="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockIDTemp . '">' . _('Movimientos del Producto') . '</a>';
echo '<br><a href="' . $rootpath . '/StockUsage.php?' . SID . '&StockID=' . $StockID . '">' . _('Mostrar el USO') . '</a>';
echo '<br><a href="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Ordenes de Venta Pendientes') . '</a>';
echo '<br><a href="' . $rootpath . '/SelectCompletedOrder_V6_0.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Ordenes de Venta Cerradas') . '</a>';
if ($Its_A_KitSet_Assembly_Or_Dummy ==False){
	echo '<br><a href="' . $rootpath . '/PO_SelectOSPurchOrder.php?' .SID . '&SelectedStockItem=' . $StockID . '">' . _('Buscar Ordenes de Compra Pendientes') . '</a>';
}


echo '</div>';*/
include('includes/footer_Index.inc');

?>
