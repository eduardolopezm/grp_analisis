<?php
/* $Revision: 1.15 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');
fecha: 09/12/2009
2.- se arreglo la variable $funcion*/
/*
Cambios
POR:IBETH ORTIZ
FECHA: 07 DE JULIO DE 2010
*/
$PageSecurity = 2;

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} else {
	$StockID = '';
}
include('includes/session.inc');
$title = _('Movimientos del Producto - ' . $StockID );
include('includes/header.inc');
$funcion=315;
include('includes/SecurityFunctions.inc');
// This is already linked from this page
//echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" .  _('Back to Items') . '</a><br>';
echo '<p Class="page_title_text">ACTUALIZACION DE COSTO MOVIMIENTOS INVENTARIO DE PRODUCTO</p>';


if (isset($_POST['UpdateDatabase']))
{
	$total=$_POST['total'];
	$InputError=0;
	
	for($x=1;$x<=$total;$x++){
		$Monto=$_POST['Monto'.$x];
		$MontoAnterior=$_POST['MontoAnterior'.$x];
		$coments=$_POST['coments'.$x];
		if ($Monto<>$MontoAnterior){ 
			if(strlen($coments)<2){
				 prnMsg( _('Realice los comentarios que justifican el cambio de costo del movimiento.'),'error');
				 $InputError=1;
				 break;
			}
		}
	}
	
	if ($InputError==0){
		for($x=1;$x<=$total;$x++){
			$Monto=$_POST['Monto'.$x];
			$MontoAnterior=$_POST['MontoAnterior'.$x];
			$transno=$_POST['transno'.$x];
			$type=$_POST['type'.$x];
			$coments=$_POST['coments'.$x];
			
			$sql= "Select reference
					From stockmoves 
					where stkmoveno= ".$transno." 
					and stockid= '".$StockID."'";
			
			$datos= DB_query($sql, $db);
			$registro= DB_fetch_array($datos);
			$reference= $registro["reference"];
			
			if ($Monto<>$MontoAnterior){
				$ResultUpdate = DB_Txn_Begin($db);
				$texto= $reference." - ".$coments." [ Movimiento Generado por: ".$_SESSION["UserID"]." ]";
				
				$SQLInventario="Update stockmoves
					set standardcost='".$Monto."', reference='".$texto."'  
					where stkmoveno=".$transno."
						and stockid='".$StockID."'";
					$ErrMsg=_('No se realizo la actualizacion de costo');
					$DbgMsg=_('El SQL Utilizado es');
					$ResultUpdate = DB_query($SQLInventario, $db, $ErrMsg, $DbgMsg);
					
					$SQLInventario="insert into logstockmoves (userid,transno,type,trandate,initialcost,endcost,comments)
							values('".$_SESSION['UserID'] ."','".$transno."','".$type."',now(),'".$MontoAnterior."','".$Monto."','".$coments."')";
					$ErrMsg=_('No se realizo la actualizacion de costo');
					$DbgMsg=_('El SQL Utilizado es');
					$ResultUpdate = DB_query($SQLInventario, $db, $ErrMsg, $DbgMsg);
				$ResultUpdate = DB_Txn_Commit($db);
			}
		}
		
		prnMsg( _('Cambios en costo de movimiento realizados de manera exitosa.'),'sucess');
		echo "<div style='text-align:center'><br><br>";
		echo "<br><a href='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Mostrar Uso de Inventario') . '</a>';
		echo "<br><a href='$rootpath/StockStatus.php?" . SID . "&StockID=$StockID'>" . _('Mostrar Estatus de Inventario') . '</a>';
		echo "<br><a href='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Buscar Pedidos de Venta Pendientes') . '</a>';
		echo "<br><a href='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Buscar Pedidos de Venta Completados') . '</a>';
		echo '</div>';
		echo "<br>";
		include('includes/footer.inc');
		exit;
	}
	unset($_POST['UpdateDatabase']);
}

if ($StockID<>'')
{
	$result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	echo '<p Class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventario') . '" alt=""><b>' . ' ' . $StockID . ' - ' . $myrow['0'] . ' (' . $myrow[1] . ')';
}
echo "<div class='centre'><form action='". $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";
echo _('Código ') . " : <input type=TEXT name='StockID' size=21 VALUE='$StockID' maxlength=20>";

echo '  ' . _('En el Almacén') . " : <select name='StockLocation'> ";
#$sql = 'SELECT loccode, locationname FROM locations';
	$sql = 'SELECT l.loccode, 
		l.locationname 
		FROM sec_loccxusser sl, locations l
		where sl.loccode=l.loccode
		and sl.userid="'.$_SESSION['UserID'].'"';
$resultStkLocs = DB_query($sql,$db);
echo "<option VALUE='0'>TODAS...";
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo "<option selected VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		} else {
		     echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo "<option selected VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
	}
}
echo '</select><br><br>';

if ( $StockID <> '' ) {
	echo '<div>  ' . _('Numero de Serie') . " : <select name='SerialItem'> ";
	#$sql = 'SELECT loccode, locationname FROM locations';
	$sql = 'SELECT serialno, count(*)
		FROM stockserialitems
		where stockserialitems.stockid = "'.$StockID.'"
		Group By serialno
		Order by serialno';
	echo $sql;
	$resultStkLocs = DB_query($sql,$db);
	
	echo "<option VALUE='0'>todos los numeros de serie...";
	while ($myrow=DB_fetch_array($resultStkLocs)){
		
		if ($myrow['serialno'] == $_POST['SerialItem']){
		     echo "<option selected VALUE='" . $myrow['serialno'] . "'>" . $myrow['serialno'];
		} else {
		     echo "<option VALUE='" . $myrow['serialno'] . "'>" . $myrow['serialno'];
		}
	}
	echo '</select><BR><BR></div>';
}
if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
   $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
   $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,1,1,Date("y")));
}
echo ' ' . _('Mostrar movimientos del ') . ' : <input type=TEXT name="AfterDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="12" maxlength="12" VALUE="' . $_POST['AfterDate'] . '">';
echo ' ' . _('al') . ' : <input type=TEXT name="BeforeDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="12" maxlength="12" VALUE="' . $_POST['BeforeDate'] . '">';
echo "     <input type=submit name='ShowMoves' VALUE='" . _('Mostrar') . "'>";
echo '<hr>';

$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

if (isset($_POST['StockLocation']))
	$StockLocation = $_POST['StockLocation'];
else
	$StockLocation = 0;
	
$sql = "SELECT stockmoves.stockid,
		sum(stockmoves.qty) as initqty
	FROM stockmoves
	INNER JOIN systypescat ON stockmoves.type=systypescat.typeid
	INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
	INNER JOIN locations ON stockmoves.loccode = locations.loccode
	LEFT JOIN stockserialitems ON stockmoves.loccode = stockserialitems.loccode AND stockmoves.stockid = stockserialitems.stockid
	WHERE (stockmoves.loccode='" . $StockLocation . "' or '0'='".$StockLocation."')
	 AND stockmoves.trandate < '". $SQLAfterDate . "'
	 AND stockmoves.stockid = '" . $StockID . "'
	 AND hidemovt=0
	 AND (stockmaster.controlled = 0 OR (stockmaster.controlled = 1 AND (stockserialitems.serialno = '".$_POST['SerialItem']."' OR '".$_POST['SerialItem']."' = 0)))
	 GROUP BY stockmoves.stockid";
//echo $sql;
$ErrMsg = _('X The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';
$invinicial = 0;
$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
if ($myrow=DB_fetch_array($MovtsResult)) {
	$invinicial = $myrow['initqty'];
}
if (!isset($_POST['SerialItem'])){
	$_POST['SerialItem'] = '0';
}
$sql = "SELECT stockmoves.stkmoveno, stockmoves.stockid, 
		systypescat.typename, 
		stockmoves.type,
		locations.tagref,
		stockmoves.transno, 	
		purchorderdetails.orderno,	
		stockmoves.trandate, 
		stockmoves.debtorno, 
		stockmoves.branchcode,";
		if ($_POST['SerialItem'] != "0"){
			$sql = $sql . " stockserialmoves.moveqty as qty,";
		}else{
			$sql = $sql . " stockmoves.qty as qty,";
		}
		$sql = $sql . " stockmoves.reference, 
		stockmoves.price, 
		stockmoves.standardcost,
		stockmoves.avgcost,
		purchorderdetails.actprice,
		stockmoves.discountpercent, 
		stockmoves.newqoh, 
		stockmaster.decimalplaces, 
		stockmoves.loccode, 
		locations.locationname,
		grns.qtyrecd,
		grns.podetailitem,
		grns.stdcostunit,
		purchorderdetails.actprice,	
		purchorderdetails.`discountpercent1`,
		purchorderdetails.`discountpercent2`,
		purchorderdetails.`discountpercent3`,
		purchorderdetails.pcunit,
		purchorderdetails.stdcostunit,
		purchorderdetails.unitprice,
		debtortrans.folio,
		stockmaster.materialcost,
		purchorderdetails.actprice as preciocompra,";
		
		if ($_POST['SerialItem'] != "0"){
			$sql = $sql . " IFNULL(stockserialmoves.serialno,'') as serialno";
		}else{
			$sql = $sql . " '' as serialno";
		}
	$sql = $sql . " FROM ";
	
	if ($_POST['SerialItem'] != "0"){
		$sql = $sql . " stockserialmoves, ";
	}
	
	$sql = $sql . " stockmoves
	INNER JOIN systypescat ON stockmoves.type=systypescat.typeid
	INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
	INNER JOIN locations ON stockmoves.loccode = locations.loccode";
	
	$sql = $sql . " LEFT JOIN grns ON stockmoves.transno = grns.grnbatch AND stockmoves.type = 25 AND grns.itemcode = stockmoves.stockid
		AND grns.qtyrecd=stockmoves.qty
	LEFT JOIN purchorderdetails ON grns.podetailitem = purchorderdetails.podetailitem 
		AND purchorderdetails.itemcode = stockmoves.stockid
		AND   grns.itemcode = purchorderdetails.itemcode
		AND stockmoves.qty =purchorderdetails.qtyinvoiced
	LEFT JOIN debtortrans ON stockmoves.transno = debtortrans.transno AND debtortrans.type = stockmoves.type AND debtortrans.tagref = stockmoves.tagref
				
	WHERE (stockmoves.loccode='" . $StockLocation . "' or '0'='".$StockLocation."')
	 AND stockmoves.trandate >= '". $SQLAfterDate . "'
	 AND stockmoves.stockid = '" . $StockID . "'
	 AND stockmoves.trandate <= '" . $SQLBeforeDate . "'
	 AND hidemovt=0";
	 
	 if ($_POST['SerialItem'] != "0"){
		$sql = $sql . " and stockmoves.stkmoveno = stockserialmoves.stockmoveno
				and stockmaster.controlled = 1
			       AND (trim(stockserialmoves.serialno) = '".trim($_POST['SerialItem']," ")."')";
	}
	 $sql = $sql . " ORDER BY stockmoves.trandate,stockmoves.stkmoveno";
//echo $sql;
$ErrMsg = _('X The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';

$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
echo '<table cellpadding=5 BORDER=1>';
#$tableheader = "<tr>
#		<th>" . _('Tipo') . "</th><th>" . _('Folio') . "</th>
#		<th>" . _('Fecha') . "</th><th>" . _('Cliente') . "</th>
#		<th>" . _('Branch') . "</th><th>" . _('Cantidad') . "</th>
#		<th>" . _('Referencia') . "</th><th>" . _('Precio') . "</th>
#		<th>" . _('Descuento') . "</th><th>" . _('New Qty') . "</th>
#		</tr>";
printf("<td colspan=2 style='text-align:center;font-weight:normal;'>%s</td>
		<td colspan=4 style='font-weight:normal;'>INVENTARIO INICIAL</td>
		<td style='font-weight:normal;' style='text-align:center;'>%s</td>
		<td style='font-weight:normal;' style='text-align:center;'></td>
		<td style='font-weight:normal;'></td>
		<td style='font-weight:normal;' style='text-align:right;'></td>
		</tr>",
		$StockID,
		number_format($invinicial,0));
$tableheader = "<tr>
		<th>" . _('#') . "</th>
		<th>" . _('Tipo') . "</th>
		<th>" . _('Almacen') . "</th>
		<th>" . _('Folio') . "</th>
		<th>" . _('Fecha') . "</th>
		<th>" . _('Cliente') . "</th>
		<th>" . _('Cant') . "</th>
		<th>" . _('Acum') . "</th>
		<th>" . _('Referencia') . "</th>
		<th>" . _('Costo') . "</th>
		<th>" . _('Comentarios') . "</th>
		</tr>";
echo $tableheader;

$j = 0;
$k=0; //row colour counter
$sumaqty =0;
$acumqty = $invinicial;
$cuenta=0;
while ($myrow=DB_fetch_array($MovtsResult)) {
	$j=$j+1;
	$sumaqty = $sumaqty + $myrow['qty'];
	echo '<tr>';
	//echo $myrow['qty'];
	//echo $myrow['decimalplaces'].'<br>';
	$DisplayTranDate = ConvertSQLDate($myrow['trandate']);
	$acumqty = $acumqty + $myrow['qty'];
	
	if ($_POST['SerialItem'] != "0"){
		$varserie = $myrow['serialno'];
	}else{
		$varserie = "consulta a tabla de stockserialmoves";
		
		$xsql = "select serialno from stockserialmoves where stockmoveno = '"  . $myrow['stkmoveno'] . "'";
		$xResult= DB_query($xsql,$db);
		$varserie = "";
		
		while ($xmyrow=DB_fetch_array($xResult)) {
			$varserie = $varserie . $xmyrow['serialno'] . "; ";
		}
		
	}
	if ($varserie != ""){
		$referencia = $myrow['reference'] . "<br>S: " . $varserie;
	}else{
		$referencia = $myrow['reference'];
	}
	$tagref= $myrow['tagref'];
	
	$SQL=" SELECT l.taxid,l.address5,t.tagdescription
		FROM legalbusinessunit l, tags t
		WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
		$Result= DB_query($SQL,$db);
		if (DB_num_rows($Result)==1) {
			$myrowtags = DB_fetch_array($Result);
			$rfc=trim($myrowtags['taxid']);
			$keyfact=$myrowtags['address5'];
			$nombre=$myrowtags['tagdescription'];
		}   
	$tipofac=$myrow['type'];
	$foliox=" "; 
	$foliox=$myrow['folio'];
	$separa = explode('|',$foliox);
	if ($tipofac=='12'){
		$serie = $separa[1];
		$folio = $separa[0];
	}else{
		$serie = $separa[0];
		$folio = $separa[1];
	}
	if ($_SESSION['EnvioXSA']==1){
		$pagina=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact ;
	}else{
		$liga = GetUrlToPrint($tagref,10,$db);
		$pagina=$liga.'&TransNo='.$order.'&Tagref='.$tagref;
	}
	if ($myrow['type']==10 or $myrow['type']==110){ /*its a sales invoice allow link to show invoice it was sold on*/
		printf("<td style='text-align:center;font-weight:normal;font-size:8pt;'>%s</td><td>
		<a TARGET='_blank' href='%s'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
		<td style='font-weight:normal;text-align:center;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;text-align:center;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;text-align:right;font-size:8pt;'>
			<input type=text class=number size=8 name=Monto" . $j . " value=%s>
			<input type=hidden class=number name=MontoAnterior" . $j . " value=%s>
			<input type=hidden class=number name=transno" . $j . " value=%s>
			<input type=hidden class=number name=type" . $j . " value=%s>
		</td>
		
		<td style='font-weight:normal;text-align:right;font-size:8pt;'>
			<textarea name=coments" . $j . " cols=30 rows=2 >%s</textarea>
		</td>	
		</tr>",
		$j,
		$pagina,
		$myrow['typename'],
		$myrow['loccode'].' '.$myrow['locationname'],
		'F:'.$myrow['folio'].'<br>ERP:'.$myrow['transno'],
		$DisplayTranDate,
		$myrow['debtorno'],
		number_format($myrow['qty'],$myrow['decimalplaces']),
		number_format($acumqty,$myrow['decimalplaces']),
		$referencia,
		number_format($myrow['standardcost'],2),
		number_format($myrow['standardcost'],2),
		$myrow['stkmoveno'],
		$myrow['type'],
		''
		);

	} elseif ($myrow['type']==11){
		printf("<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td>
		<a style='font-size:8pt;' TARGET='_blank' href='%s'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
		<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
		<td style='font-weight:normal;font-size:8pt;'>%s</td>
		<td style='font-weight:normal;text-align:right;font-size:8pt;'>
			<input type=text class=number size=8 name=Monto" . $j . " value=%s>
			<input type=hidden class=number name=MontoAnterior" . $j . " value=%s>
			<input type=hidden class=number name=transno" . $j . " value=%s>
			
			<input type=hidden class=number name=type" . $j . " value=%s>
		</td>
		<td style='font-weight:normal;text-align:right;font-size:8pt;'>
			<textarea name=coments" . $j . " cols=30 rows=2 >%s</textarea>
		</td>	
		</tr>",
		$j,
		$pagina,
		$myrow['typename'],
		$myrow['loccode'].' '.$myrow['locationname'],
		'F:'.$myrow['folio'].'<br>ERP:'.$myrow['transno'],
		$DisplayTranDate,
		$myrow['debtorno'],
		
		number_format($myrow['qty'],$myrow['decimalplaces']),
		number_format($acumqty,$myrow['decimalplaces']),
		$referencia,
		number_format($myrow['standardcost'],2),
		number_format($myrow['standardcost'],2),
		$myrow['stkmoveno'],
		$myrow['type'],
		''
		);
	} elseif ($myrow['type']==25){ /////////////////////////////////////////////////////////////////////////////////////////////////////////////
		printf("<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;text-align:right;font-size:8pt;'>
				<input type=text size=8 class=number name=Monto" . $j . " value=%s>
				<input type=hidden class=number name=MontoAnterior" . $j . " value=%s>
				<input type=hidden class=number name=transno" . $j . " value=%s>
				<input type=hidden class=number name=type" . $j . " value=%s>
			</td>			
			<td style='font-weight:normal;text-align:right;font-size:8pt;'>
				<textarea name=coments" . $j . " cols=30 rows=2 >%s</textarea>
			</td>	
			</tr>",
			$j,
			$myrow['typename'],
			$myrow['loccode'].' '.$myrow['locationname'],
			'OC:'.$myrow['orderno'].'<br>REC:'.$myrow['transno'],
			$DisplayTranDate,
			$myrow['debtorno'],
			number_format($myrow['qty'],$myrow['decimalplaces']),
			number_format($acumqty,$myrow['decimalplaces']),
			$referencia,
			number_format($myrow['standardcost'],2),
			number_format($myrow['standardcost'],2),
			$myrow['stkmoveno'],
			$myrow['type'],
			''
			);
	} else {
		printf("<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;text-align:right;font-size:8pt;'>
				<input type=text size=8 class=number name=Monto" . $j . " value=%s>
				<input type=hidden class=number name=MontoAnterior" . $j . " value=%s>
				<input type=hidden class=number name=transno" . $j . " value=%s>
				<input type=hidden class=number name=type" . $j . " value=%s>	
			</td>
			<td style='font-weight:normal;text-align:right;font-size:8pt;'>
				<textarea name=coments" . $j . " cols=30 rows=2 >%s</textarea>
			</td>	
			</tr>",
			$j,
			$myrow['typename'],
			$myrow['loccode'].' '.$myrow['locationname'],
			$myrow['transno'],
			$DisplayTranDate,
			$myrow['debtorno'],
			number_format($myrow['qty'],$myrow['decimalplaces']),
			number_format($acumqty,$myrow['decimalplaces']),
			$referencia,
			number_format($myrow['standardcost'],2),
			number_format($myrow['standardcost'],2),
			$myrow['stkmoveno'],
			$myrow['type'],
			''
			);
	}
//end of page full new headings if
}
echo '<tr><td colspan=5 style="text-align:right;">TOTAL : </td><td style="text-align:center;">'.($sumaqty + $invinicial).'</td><td colspan=2>&nbsp;</td></tr>';
echo '</table><hr>';
echo "<div class='centre'><input tabindex=".$j." type=submit name=UpdateDatabase value=" . _('Actualizar') . " style='font-size:8pt;'>";
echo "<input type=hidden class=number name=total value=" . $j . ">";
echo '</form>';
//end of while loop

//INICIO DE TABLAS DE TRANSFERENCIAS
if($StockLocation=='0'){
	$SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
	locations.locationname as destino, l.locationname as origen
	FROM loctransfers INNER JOIN locations
	ON loctransfers.recloc=locations.loccode
	INNER JOIN locations as l
	ON loctransfers.shiploc=l.loccode
	WHERE recqty='0'
	AND stockid='".$StockID."'";
	$Result= DB_query($SQL,$db);
	
	if ( DB_num_rows($Result) > 0 ) {
		echo "<table cellpadding=5 border=1>
		      <tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS EN PROCESO') . "</p></td></tr>";
		
		$tableheader2 = "<tr>
				<th>" . _('# Linea') . "</th>
				<th>" . _('# Transferencia') . "</th>
				<th>" . _('Almacen de Origen') . "</th>
				<th>" . _('Almacen Destino') . "</th>
				<th>" . _('Fecha') . "</th>
				<th>" . _('Cantidad') . "</th>
				</tr>";
		echo $tableheader2;
			$j=1;
			$k=0; //row colour counter
			while ($myrow = DB_fetch_array($Result)) {
				if ($k==1){
					echo '<tr style="font-weight:normal">';                                         
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
			}
		echo "</tr>";
		echo "</table><hr>";
	}
}
else
{
	$SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
		locations.locationname as destino, l.locationname as origen
		FROM loctransfers INNER JOIN locations
		ON loctransfers.recloc=locations.loccode
		INNER JOIN locations as l
		ON loctransfers.shiploc=l.loccode
		WHERE recqty='0'
		AND shiploc='".$StockLocation."'
		AND stockid='".$StockID."'";
		$Result= DB_query($SQL,$db);
	if ( DB_num_rows($Result) > 0 ) {
		echo "<table cellpadding=5 border=1>
			<tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS DE SALIDA') . "</p></td></tr>";
		  $tableheader2 = "<tr>
				  <th>" . _('# Linea') . "</th>
				  <th>" . _('# Transferencia') . "</th>
				  <th>" . _('Almacen de Origen') . "</th>
				  <th>" . _('Almacen Destino') . "</th>
				  <th>" . _('Fecha') . "</th>
				  <th>" . _('Cantidad') . "</th>
				  </tr>";
		  echo $tableheader2;
			  $j=1;
			  $k=0; //row colour counter
			  $suma1=0;
			while ($myrow = DB_fetch_array($Result)){
				$suma1=$suma1 + $myrow['shipqty'];
				/*$j=$j+1;
				$sumaqty = $sumaqty + $myrow['qty'];
				echo '<tr>';
				$DisplayTranDate = ConvertSQLDate($myrow['trandate']);
				$acumqty = $acumqty + $myrow['qty'];*/
				if ($k==1){
					  echo '<tr style="font-weight:normal">';                                         
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
			}
	  echo "</tr>";
	  echo '<tr><td colspan=5 style="text-align:right;">TOTAL : </td><td style="text-align:right;">'.(-$suma1).'</td></tr>';
	  echo "</table>";
	}
	$SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
		locations.locationname as destino, l.locationname as origen
		FROM loctransfers INNER JOIN locations
		ON loctransfers.recloc=locations.loccode
		INNER JOIN locations as l
		ON loctransfers.shiploc=l.loccode
		WHERE recqty='0'
		AND recloc='".$StockLocation."'
		AND stockid='".$StockID."'";
		$Result= DB_query($SQL,$db);
if ( DB_num_rows($Result) > 0 ) {
	echo "<table cellpadding=5 border=1>
	      <tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS DE ENTRADA') . "</p></td></tr>";
	$tableheader2 = "<tr>
			<th>" . _('# Linea') . "</th>
			<th>" . _('# Transferencia') . "</th>
			<th>" . _('Almacen de Origen') . "</th>
			<th>" . _('Almacen Destino') . "</th>
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
	echo '<table border="0" align="right">
	<tr><td style="text-align:right;">DISPONIBLES : </td><td style="text-align:right;" colspan=7>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.(($sumaqty + $invinicial+$suma2)-$suma1).'</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td></tr><br>
      </table>';
}
echo "<br><br>";
echo "<br><a href='$rootpath/StockStatus.php?" . SID . "&StockID=$StockID'>" . _('Mostrar Estatus de Inventario') . '</a>';
echo "<br><a href='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Mostrar Uso de Inventario') . '</a>';
echo "<br><a href='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Buscar Pedidos de Venta Pendientes') . '</a>';
echo "<br><a href='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Buscar Pedidos de Venta Completados') . '</a>';
echo '</form></div>';
echo "<br>";
include('includes/footer.inc');
?>