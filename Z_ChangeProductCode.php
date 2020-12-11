<?php
/* $Revision: 1.11 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('PAGINA DE UTILERIA PARA CAMBIAR CODIGO DE PRODUCTO EN TODAS LAS TABLAS');
include('includes/header.inc');

$funcion=1016;//CAMBIAR
include('includes/SecurityFunctions.inc');

if (isset($_POST['ProcessStockidChange'])){

/*Primero checa que el codigo del Tagref Exista y la categoria de serializado o no sean la misma*/
	$result=DB_query("SELECT stockid, serialised,controlled FROM stockmaster WHERE stockid='" . $_POST['OldStockidNo'] . "'",$db);
	if (DB_num_rows($result)==0){
prnMsg ('<br><br>' . _('El codigo de producto') . ': ' . $_POST['OldStockidNo'] . ' ' . _('no existe actualmente en la base de datos del sistema'),'error');
	include('includes/footer.inc');
	exit;
	} else {
		$myrow=DB_fetch_row($result);
		$monedaOrigen = $myrow[1];
		$controlledold = $myrow[3];
	}


	if ($_POST['NewStockidNo']==''){
		prnMsg(_('El codigo de producto destino debe de ser capturado'),'error');
		include('includes/footer.inc');
		exit;
	}
	
/*Ahora checa que el nuevo codigo de tagref exista */
	$result=DB_query("SELECT stockid,serialised,controlled FROM stockmaster WHERE stockid='" . $_POST['NewStockidNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('El codigo de producto de reemplazo') .': ' . $_POST['NewStockidNo'] . ' ' . _('no existe actualmente en la base de datos del sistema') . ' - ' . _('este codigo debe de existir en el sistema antes de migrar movimientos de otro proveedor...'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$monedaDestino = $myrow[1];
		$controllednew = $myrow[3];
	}
	
	
	if ($controlledold != $controllednew){
		prnMsg(_('El producto origen es ') .': ' . $controlledold . ' y del destino es ' . $controllednew . ' - ' . _(' ambos productos deben de ser controlados o no controlados para poder traspasar movimientos...'),'error');
		include('includes/footer.inc');
		exit;
	}

	$result = DB_Txn_Begin($db);

	/****** SELECT DE DE MOVIMIENTOS DONDE EXISTE EL CAMPO Stockid ******/
	
	/*
	SELECT Directa
	stockcounts.id 
	stockcounts.stockid 
	stockcounts.loccode 
	stockcounts.qtycounted 
	stockcounts.reference
	FROM stockcounts
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE stockcounts SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockcounts transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	/*Directa
	SELECT
	purchorderdetails.podetailitem
	purchorderdetails.orderno
	purchorderdetails.itemcode
	purchorderdetails.deliverydate
	purchorderdetails.itemdescription
	purchorderdetails.glcode
	purchorderdetails.qtyinvoiced
	purchorderdetails.unitprice
	purchorderdetails.actprice
	purchorderdetails.stdcostunit
	purchorderdetails.quantityord
	purchorderdetails.quantityrecd
	purchorderdetails.shiptref
	purchorderdetails.jobref
	purchorderdetails.completed
	purchorderdetails.itemno
	purchorderdetails.uom
	purchorderdetails.subtotal_amount
	purchorderdetails.package
	purchorderdetails.pcunit
	purchorderdetails.nw
	purchorderdetails.suppliers_partno
	purchorderdetails.gw
	purchorderdetails.cuft
	purchorderdetails.total_quantity
	purchorderdetails.total_amount
	purchorderdetails.discountpercent1
	purchorderdetails.discountpercent2
	purchorderdetails.discountpercent3
	purchorderdetails.narrative
	purchorderdetails.justification
	FROM purchorderdetails
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE purchorderdetails SET itemcode='" . $_POST['NewStockidNo'] . "' WHERE itemcode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update purchorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en ordenes de compra ...'),'info');

	/*Directa
	SELECT
	grns.grnbatch
	grns.grnno
	grns.podetailitem
	grns.itemcode
	grns.deliverydate
	grns.itemdescription
	grns.qtyrecd
	grns.quantityinv
	grns.supplierid
	grns.stdcostunit
	FROM grns
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE grns SET itemcode='" . $_POST['NewStockidNo'] . "' WHERE itemcode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update grns transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en las recepciones de ordenes de compra ...'),'info');
	
	/*Directa
	SELECT
	suppnotesorderdetails.orderlineno
	suppnotesorderdetails.orderno
	suppnotesorderdetails.stkcode
	suppnotesorderdetails.fromstkloc
	suppnotesorderdetails.qtyinvoiced
	suppnotesorderdetails.unitprice
	suppnotesorderdetails.quantity
	suppnotesorderdetails.estimate
	suppnotesorderdetails.discountpercent
	suppnotesorderdetails.discountpercent1
	suppnotesorderdetails.discountpercent2
	suppnotesorderdetails.actualdispatchdate
	suppnotesorderdetails.completed
	suppnotesorderdetails.narrative
	suppnotesorderdetails.itemdue
	suppnotesorderdetails.warranty
	suppnotesorderdetails.poline
	FROM suppnotesorderdetails
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE suppnotesorderdetails SET stkcode='" . $_POST['NewStockidNo'] . "' WHERE stkcode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to updatesuppnotesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en las notas de proveedor full ...'),'info');
	
	/*Directa
	 SELECT
	recurrsalesorderdetails.recurrorderno
	recurrsalesorderdetails.stkcode
	recurrsalesorderdetails.unitprice
	recurrsalesorderdetails.quantity
	recurrsalesorderdetails.discountpercent
	recurrsalesorderdetails.narrative
	FROM recurrsalesorderdetails
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE recurrsalesorderdetails SET stkcode='" . $_POST['NewStockidNo'] . "' WHERE stkcode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to recurrsalesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en las ordenes de compra recurrentes ...'),'info');

	/*Directa
	SELECT
	notesorderdetails.orderlineno
	notesorderdetails.orderno
	notesorderdetails.stkcode
	notesorderdetails.fromstkloc
	notesorderdetails.qtyinvoiced
	notesorderdetails.unitprice
	notesorderdetails.quantity
	notesorderdetails.estimate
	notesorderdetails.discountpercent
	notesorderdetails.discountpercent1
	notesorderdetails.discountpercent2
	notesorderdetails.actualdispatchdate
	notesorderdetails.completed
	notesorderdetails.narrative
	notesorderdetails.itemdue
	notesorderdetails.poline
	notesorderdetails.warranty
	FROM notesorderdetails
 	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE notesorderdetails SET stkcode='" . $_POST['NewStockidNo'] . "' WHERE stkcode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to notesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en las notas de credito del cliente...'),'info');
	
	/*
	SELECT Directa
	salesorderdetails.orderlineno
	salesorderdetails.orderno
	salesorderdetails.stkcode
	salesorderdetails.fromstkloc
	salesorderdetails.qtyinvoiced
	salesorderdetails.unitprice
	salesorderdetails.quantity
	salesorderdetails.estimate
	salesorderdetails.discountpercent
	salesorderdetails.discountpercent1
	salesorderdetails.discountpercent2
	salesorderdetails.actualdispatchdate
	salesorderdetails.completed
	salesorderdetails.narrative
	salesorderdetails.itemdue
	salesorderdetails.poline
	salesorderdetails.warranty
	salesorderdetails.idtarea
	FROM salesorderdetails
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE salesorderdetails SET stkcode='" . $_POST['NewStockidNo'] . "' WHERE stkcode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to salesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en las ordenes de venta ...'),'info');
	
	/*Directa
	SELECT
	salescatprod.salescatid 
	salescatprod.stockid
	FROM salescatprod
	*/
	//Eliminación de producto anterior 
	$sql = "DELETE FROM salescatprod WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to DELETE old stockid record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Borrando clave de stockid anterior...'),'info');

	/*
	SELECT Directa
	supptransdetails.detailid 
	supptransdetails.supptransid 
	supptransdetails.stockid 
	supptransdetails.description 
	supptransdetails.price 
	supptransdetails.qty 
	supptransdetails.orderno 
	supptransdetails.grns
	FROM supptransdetails
	*/
	$sql = "UPDATE supptransdetails SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update supptransdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en los detalles de la factura del proveedor ...'),'info');
	
	/*Directo
	SELECT
	stockserialmoves.stkitmmoveno 
	stockserialmoves.stockmoveno 
	stockserialmoves.stockid 
	stockserialmoves.serialno 
	stockserialmoves.moveqty 
	stockserialmoves.standardcost 
	stockserialmoves.orderno 
	stockserialmoves.orderdetailno
	FROM stockserialmoves
	*/
	$sql = "UPDATE stockserialmoves SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockserialmoves transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en los movimientos de producos serializados ...'),'info');
	
	/*Directa
	SELECT
	stockmoves.stkmoveno 
	stockmoves.stockid 
	stockmoves.type 
	stockmoves.transno 
	stockmoves.loccode 
	stockmoves.trandate 
	stockmoves.debtorno 
	stockmoves.branchcode 
	stockmoves.price 
	stockmoves.prd 
	stockmoves.reference 
	stockmoves.qty 
	stockmoves.discountpercent 
	stockmoves.standardcost 
	stockmoves.show_on_inv_crds 
	stockmoves.newqoh 
	stockmoves.hidemovt 
	stockmoves.narrative 
	stockmoves.warranty 
	stockmoves.tagref0 
	stockmoves.discountpercent1 
	stockmoves.discountpercent2 
	stockmoves.totaldescuento 
	stockmoves.avgcost 
	stockmoves.standardcostv2
	FROM stockmoves 
	*/
	$sql = "UPDATE stockmoves SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en los movimientos del producos  ...'),'info');
	
	
	
	/*
	SELECT Directa
	orderdeliverydifferenceslog.orderno 
	orderdeliverydifferenceslog.invoiceno 
	orderdeliverydifferenceslog.stockid 
	orderdeliverydifferenceslog.quantitydiff 
	orderdeliverydifferenceslog.debtorno 
	orderdeliverydifferenceslog.branch 
	orderdeliverydifferenceslog.can_or_bo
	FROM orderdeliverydifferenceslog
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE orderdeliverydifferenceslog SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update orderdeliverydifferenceslog transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en las diferencias de ordenes de compra guardadas ...'),'info');
	
	
	/********* FIN MOVIMIENTOS *******/
	
	/********* INICIO DE MOVIMIENTOS EN INVENTARIOS EN LAS TABLAS DONDE SE ENCUENTRE EL CAMPO STOCKID *******/
	
	/*NDirecta
	 SELECT 
	hs_stockcostsxlegal.legalid 
	hs_stockcostsxlegal.stockid 
	hs_stockcostsxlegal.lastcost 
	hs_stockcostsxlegal.avgcost 
	hs_stockcostsxlegal.lastpurchase 
	hs_stockcostsxlegal.lastpurchaseqty 
	hs_stockcostsxlegal.lastupdatedate 
	hs_stockcostsxlegal.trandate
	FROM hs_stockcostsxlegal
	*/
	$sql = "UPDATE hs_stockcostsxlegal SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update hs_stockcostsxlegal transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en costos x legal ...'),'info');
	
	/*SELECT
	hs_stockcostsxtag.tagref0 
	hs_stockcostsxtag.stockid 
	hs_stockcostsxtag.lastcost 
	hs_stockcostsxtag.avgcost 
	hs_stockcostsxtag.lastpurchase 
	hs_stockcostsxtag.lastpurchaseqty 
	hs_stockcostsxtag.lastupdatedate 
	hs_stockcostsxtag.trandate
	FROM hs_stockcostsxtag
	*/
	$sql = "UPDATE hs_stockcostsxtag SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update hs_stockcostsxtag transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en costos x tag ...'),'info');

	/*
	SELECT Ndirecta
	stockcostsxtag.tagref0 
	stockcostsxtag.stockid 
	stockcostsxtag.lastcost 
	stockcostsxtag.avgcost 
	stockcostsxtag.lastpurchase 
	stockcostsxtag.lastpurchaseqty 
	stockcostsxtag.lastupdatedate 
	stockcostsxtag.trandate 
	stockcostsxtag.Comments 
	stockcostsxtag.id 
	stockcostsxtag.eliminar
	FROM stockcostsxtag
	*/
	$SqlOld= "SELECT stockid,tagref,trandate FROM stockcostsxtag
		WHERE stockid='".$_POST['OldStockidNo']."'";
	$Resultold = DB_query($SqlOld,$db,$ErrMsg,$DbgMsg,true);
	
	$SqlNew= "SELECT stockid,tagref,trandate FROM stockcostsxtag
		WHERE stockid='".$_POST['NewStockidNo']."'";
	$Resultnew = DB_query($SqlNew,$db,$ErrMsg,$DbgMsg,true);
	
	if (DB_num_rows($Resultold)>0 and DB_num_rows($Resultnew)>0){
	
	//Para cada producto obtiene  la cantidad y costo k hay en la tabla locstock de acuerdo al loccode correspondiente
	while ($myrow2 = DB_fetch_array($Resultold)){
	//Consulta que trae el oldcosto y oldcantidad de cada producto existente en la tabla locstock
	$sql_old ="SELECT sum(locstock.quantity) as qtyold,stockcostsxtag.avgcost as costold,locations.loccode,
				stockcostsxtag.trandate
		   FROM stockcostsxtag
				INNER JOIN locstock ON stockcostsxtag.stockid=locstock.stockid
				INNER JOIN locations ON stockcostsxtag.tagref=locations.tagref
				WHERE locstock.loccode=locations.loccode
				AND  stockcostsxtag.tagref='" .$myrow2['tagref']. "'
				AND  locations.tagref='" . $myrow2['tagref'] . "'
				AND stockcostsxtag.stockid='".$_POST['OldStockidNo']."'
		   GROUP BY locations.tagref";
						
	$ResultOld = DB_query($sql_old,$db,$ErrMsg,$DbgMsg,true);
	$myrowold = DB_fetch_array($ResultOld);
	//echo "<br><br>cuantitiold".$sql_old;
	//Consulta que trae el newcosto y newcantidad de cada producto existente en la tabla locstock
	$sql_new ="SELECT sum(locstock.quantity) as qtynew,stockcostsxtag.avgcost as costnew,locations.loccode,
		          stockcostsxtag.trandate
		  FROM stockcostsxtag
			INNER JOIN locstock ON stockcostsxtag.stockid=locstock.stockid
			INNER JOIN locations ON stockcostsxtag.tagref=locations.tagref
			WHERE locstock.loccode=locations.loccode
			AND  stockcostsxtag.tagref='".$myrow2['tagref']."'
			AND  locations.tagref='".$myrow2['tagref']."'
			AND stockcostsxtag.stockid='" . $_POST['NewStockidNo'] . "'
		GROUP BY locations.tagref";
	$ResultNew = DB_query($sql_new,$db,$ErrMsg,$DbgMsg,true);
	//echo "<br><br>cuantitinew".$sql_new;
	$myrownew = DB_fetch_array($ResultNew);
	$qtyold=$myrowold['qtyold'];
	$costold=$myrowold['costold'];
	$qtynew=$myrownew['qtynew'];
	$costnew=$myrownew['costnew'];
	$tranold=$myrowold['trandate'];
	$trannew=$myrownew['trandate'];
	
	if(($qtyold+$qtynew)>=1){
		//echo "<br>Entra a formula";
		$formula=((($qtyold*$costold)+($qtynew*$costnew))/($qtyold+$qtynew));
	}else{
		$formula=0;
		//echo "<br>No Entra a formula";
	}
	if($tranold > $trannew){
		
		$trannew=$tranold;
	}
	//Actualiza el nuevo costo promedio para cada producto perteneciente al newtag
		$sql = "UPDATE stockcostsxtag
			SET  avgcost='" . $formula . "',trandate='". $trannew . "'
			WHERE stockid='" . $_POST['NewStockidNo'] . "'
			AND tagref='" . $myrow2['tagref'] . "'";
	$ErrMsg = _('The SQL to update stockcostsxtag transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//echo "<br><br>update".$sql;
	}
	}
	prnMsg(_('Cambiando registros en costos por unidad de negocio...'),'info');
	//Borra el oldtag de la tabla
	$sql = "DELETE FROM stockcostsxtag WHERE stockid='".$_POST['OldStockidNo']."'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	//Actualiza el nuevo costo promedio para cada producto perteneciente al newtag en la table de stockcostxlegalid
	$SqlOld= "SELECT stockid,legalid FROM stockcostsxlegal
		WHERE stockid='".$_POST['OldStockidNo']."'";
	$Resultold = DB_query($SqlOld,$db,$ErrMsg,$DbgMsg,true);
	$SqlNew= "SELECT stockid,legalid FROM stockcostsxlegal
		WHERE stockid='".$_POST['NewStockidNo']."'";
	$Resultnew = DB_query($SqlNew,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Resultold)>0 and DB_num_rows($Resultnew)>0){
	
	//Para cada producto obtiene  la cantidad y costo k hay en la tabla locstock de acuerdo al loccode correspondiente
	while ($myrow2 = DB_fetch_array($Resultold)){
	//Consulta que trae el oldcosto y oldcantidad de cada producto existente en la tabla locstock
	$sql_old ="SELECT stockcostsxlegal.legalid,tags.tagref,locations.loccode,stockcostsxlegal.stockid as stocklegal,sum(locstock.quantity) as qtyold,
			locstock.stockid as stocidlocstock,stockcostsxlegal.avgcost as costold,locations.loccode,tags.legalid as legaltag,stockcostsxlegal.trandate
			FROM stockcostsxlegal
			INNER JOIN tags ON stockcostsxlegal.legalid=tags.legalid
			INNER JOIN locations ON tags.tagref=locations.tagref
			INNER JOIN locstock ON locations.loccode=locstock.loccode and locstock.stockid='".$_POST['OldStockidNo']."'
			WHERE locstock.loccode=locations.loccode
			AND stockcostsxlegal.legalid='".$myrow2['legalid']."'
			AND stockcostsxlegal.stockid='".$_POST['OldStockidNo']."'
			AND locstock.stockid='".$_POST['OldStockidNo']."'
			GROUP BY stockcostsxlegal.legalid";
					
	$ResultOld = DB_query($sql_old,$db,$ErrMsg,$DbgMsg,true);
	//echo "<br><br>qtyold".$sql_old;	
	$myrowold = DB_fetch_array($ResultOld);
	//Consulta que trae el newcosto y newcantidad de cada producto existente en la tabla locstock
	$sql_new ="SELECT stockcostsxlegal.legalid,tags.tagref,locations.loccode,stockcostsxlegal.stockid as stocklegal,sum(locstock.quantity) as qtynew,
			locstock.stockid as stocidlocstock,stockcostsxlegal.avgcost as costnew,locations.loccode,tags.legalid as legaltag,stockcostsxlegal.trandate
			FROM stockcostsxlegal
			INNER JOIN tags ON stockcostsxlegal.legalid=tags.legalid
			INNER JOIN locations ON tags.tagref=locations.tagref
			INNER JOIN locstock ON locations.loccode=locstock.loccode and locstock.stockid='".$_POST['NewStockidNo']."'
			WHERE locstock.loccode=locations.loccode
			AND stockcostsxlegal.legalid='".$myrow2['legalid']."'
			AND stockcostsxlegal.stockid='".$_POST['NewStockidNo']."'
			AND locstock.stockid='".$_POST['NewStockidNo']."'
			GROUP BY stockcostsxlegal.legalid";
	$ResultNew = DB_query($sql_new,$db,$ErrMsg,$DbgMsg,true);
	$myrownew = DB_fetch_array($ResultNew);
	//echo "<br><br>qtynew".$sql_new;
	$qtyold=$myrowold['qtyold'];
	$costold=$myrowold['costold'];
	$qtynew=$myrownew['qtynew'];
	$costnew=$myrownew['costnew'];
	$tranold=$myrowold['trandate'];
	$trannew=$myrownew['trandate'];
	
	if(($qtyold+$qtynew)>=1){
		//echo "<br>Entra a formula";
		$formula=((($qtyold*$costold)+($qtynew*$costnew))/($qtyold+$qtynew));
		$formulaOT = ((($qtyold*$costold)+($qtynew*$costnew))/($qtyold+$qtynew));
	}else{
		$formula=0;
		//echo "<br>No Entra a formula";
	}
	if($tranold > $trannew){
		
		$trannew=$tranold;
	}
	//Actualiza el nuevo costo promedio para cada producto perteneciente al newtag
		$sql = "UPDATE stockcostsxlegal
			SET  avgcost='" . $formula . "',trandate='" . $trannew . "'
			WHERE stockid='" . $_POST['NewStockidNo'] . "'
			AND legalid='" . $myrow2['legalid'] . "'";
	$ErrMsg = _('The SQL to update stockcostsxlegal transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//echo "<br><br>update".$sql;
	}
	}
	prnMsg(_('Cambiando registros en costos por legalid...'),'info');
	//Borra el oldtag de la tabla
	$sql = "DELETE FROM stockcostsxlegal WHERE stockid='".$_POST['OldStockidNo']."'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*Actualiza las ordenes de trabajo del anterior codigo de producto al nuevo*///
	$flagoldot = 0;
	$flagnewot = 0;
	$sqlot = "SELECT workorders.wo
			  FROM workorders";
	$resultot = DB_query($sqlot, $db);
	while($myrowot = DB_fetch_array($resultot)){
		$wo = $myrowot['wo'];
		$sqlwo = "SELECT *
				FROM worequirements
				WHERE wo = '".$wo."'
				AND worequirements.stockid = '".$_POST['OldStockidNo']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) > 0){
			$flagoldot = 1;
		}
		$sqlwo = "SELECT *
				FROM worequirements
				WHERE wo = '".$wo."'
				AND worequirements.stockid = '".$_POST['NewStockidNo']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) > 0){
			$flagnewot = 1;
		}
		if($flagnewot == 1 and $flagoldot == 1){
			$sql_otold = "SELECT sum(worequirements.qtypu) as qtyold,
						worequirements.stdcost as costold
				FROM worequirements
				WHERE stockid = '".$_POST['OldStockidNo']."'
				AND worequirements.wo = '".$wo."'";
			$ResultOT_old = DB_query($sql_otold, $db);
			$myrowotold = DB_fetch_array($ResultOT_old);
			$qtyotold = $myrowotold['qtyold'];
			$costotold = $myrowotold['costold'];
				
			$ql_otnew = "SELECT sum(worequirements.qtypu) as qtynew,
						worequirements.stdcost as costnew
				FROM worequirements
				WHERE stockid = '" . $_POST['NewStockidNo'] . "'
				AND worequirements.wo = '".$wo."'";
			$ResultOT_NEW = DB_query($ql_otnew, $db);
			$myrowotnew = DB_fetch_array($ResultOT_NEW);
			$qtyotonew = $myrowotnew['qtynew'];
			$costotnew = $myrowotnew['costnew'];
			$cantidadtotal = $qtyotold+$qtyotonew;
			
			$sql = "UPDATE worequirements
			SET worequirements.stdcost = '".$formulaOT."',
				worequirements.qtypu = '" . $cantidadtotal . "'
			WHERE worequirements.stockid = '" . $_POST['NewStockidNo'] . "'
			AND worequirements.wo = '".$wo."'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				
			$sql = "DELETE
					FROM worequirements
					WHERE worequirements.stockid = '".$_POST['OldStockidNo']."'
					AND worequirements.wo = '".$wo."'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}elseif($flagoldot == 1){
			$sql = "UPDATE worequirements
			SET worequirements.stockid = '".$_POST['NewStockidNo']."'
			WHERE worequirements.stockid = '" . $_POST['OldStockidNo'] . "'
			AND worequirements.wo = '".$wo."'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
	}
	
	$flagoldot = 0;
	$flagnewot = 0;
	$sqlot = "SELECT workorders.wo
			  FROM workorders";
	$resultot = DB_query($sqlot, $db);
	while($myrowot = DB_fetch_array($resultot)){
		$wo = $myrowot['wo'];
		$sqlwo = "SELECT *
				FROM woitems
				WHERE wo = '".$wo."'
				AND woitems.stockid = '".$_POST['OldStockidNo']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) > 0){
			$flagoldot = 1;
		}
		$sqlwo = "SELECT *
				FROM woitems
				WHERE wo = '".$wo."'
				AND woitems.stockid = '".$_POST['NewStockidNo']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) > 0){
			$flagnewot = 1;
		}
		if($flagnewot == 1 and $flagoldot == 1){
			$sql_otold = "SELECT sum(woitems.qtyrecd) as qtyold,
						woitems.stdcost as costold
				FROM woitems
				WHERE stockid = '".$_POST['OldStockidNo']."'
				AND woitems.wo = '".$wo."'";
			$ResultOT_old = DB_query($sql_otold, $db);
			$myrowotold = DB_fetch_array($ResultOT_old);
			$qtyotold = $myrowotold['qtyold'];
			$costotold = $myrowotold['costold'];
	
			$ql_otnew = "SELECT sum(woitems.qtyrecd) as qtynew,
						woitems.stdcost as costnew
				FROM woitems
				WHERE stockid = '" . $_POST['NewStockidNo'] . "'
				AND woitems.wo = '".$wo."'";
			$ResultOT_NEW = DB_query($ql_otnew, $db);
			$myrowotnew = DB_fetch_array($ResultOT_NEW);
			$qtyotonew = $myrowotnew['qtynew'];
			$costotnew = $myrowotnew['costnew'];
			$cantidadtotal = $qtyotold+$qtyotonew;
			
			$sql = "UPDATE woitems
			SET woitems.stdcost = '".$formulaOT."',
				woitems.qtyrecd = '" . $cantidadtotal . "'
			WHERE woitems.stockid = '" . $_POST['NewStockidNo'] . "'
			AND woitems.wo = '".$wo."'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
			$sql = "DELETE
					FROM woitems
					WHERE woitems.stockid = '".$_POST['OldStockidNo']."'
					AND woitems.wo = '".$wo."'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}elseif($flagoldot == 1){
			$sql = "UPDATE woitems
			SET woitems.stockid = '".$_POST['NewStockidNo']."'
			WHERE woitems.stockid = '" . $_POST['OldStockidNo'] . "'
			AND woitems.wo = '".$wo."'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
	}
	// actualiza tabla de stockserial items
	$flagoldot = 0;
	$flagnewot = 0;
	$sqlot = "SELECT *
			  FROM stockserialitems
			WHERE stockid='".$_POST['OldStockidNo']."'";
	$resultot = DB_query($sqlot, $db);
	while($myrowot = DB_fetch_array($resultot)){
		$serial = $myrowot['serialno'];
		$loccode=$myrowot['loccode'];
		$cantidad=$myrowot['quantity'];
		$Costo=$myrowot['standardcost'];
		
		$sqlwo = "SELECT *
				FROM stockserialitems
				WHERE serialno = '".$serial."'
				AND loccode='".$loccode."'
				AND stockid = '".$_POST['NewStockidNo']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) > 0){
			$sqlwo = "UPDATE stockserialitems
					  SET quantity=quantity+".$cantidad."
					  WHERE serialno = '".$serial."'
						AND loccode='".$loccode."'
						AND stockid = '".$_POST['NewStockidNo']."'";
			$result = DB_query($sqlwo, $db);
		}else{
			$SQL = "INSERT INTO stockserialitems (stockid, loccode, serialno, quantity,standardcost)
                    VALUES ('" .$_POST['NewStockidNo']. "', '" . $loccode . "', '" . $serial . "',  ". $cantidad .",".$Costo.")";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
			$DbgMsg = _('The following SQL to update the serial stock item record was used');
			$result = DB_query($SQL, $db);
		}
		// elimino producto de stockserialitems
		$SQL = "DELETE FROM stockserialitems
				WHERE serialno = '".$serial."'
				AND loccode='".$loccode."'
				AND stockid = '".$_POST['OldStockidNo']."'";
		$result = DB_query($SQL, $db);            
	}
	
	
	
	/*Directa
	 SELECT
	salesanalysis.typeabbrev 
	salesanalysis.periodno 
	salesanalysis.amt 
	salesanalysis.cost 
	salesanalysis.cust 
	salesanalysis.custbranch 
	salesanalysis.qty 
	salesanalysis.disc 
	salesanalysis.stockid 
	salesanalysis.area 
	salesanalysis.budgetoractual 
	salesanalysis.salesperson 
	salesanalysis.stkcategory 
	salesanalysis.id
	FROM salesanalysis 
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE salesanalysis SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update salesanalysis transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');

	/*Directa
	SELECT
	loctransfers.reference 
	loctransfers.stockid 
	loctransfers.shipqty 
	loctransfers.recqty 
	loctransfers.shipdate 
	loctransfers.recdate 
	loctransfers.shiploc 
	loctransfers.recloc 
	loctransfers.comments 
	loctransfers.serialno
	FROM loctransfers
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE loctransfers SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros de transferencias de almacen...'),'info');
	
	$sql = "UPDATE android_emisiones SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de emisiones desde movil..'),'info');
	
	$sql = "UPDATE android_frentes_bancos SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	//$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de android_frentes_bancos desde movil..'),'info');
	
	$sql = "UPDATE android_proveedores_precios SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de android_proveedores_precios desde movil..'),'info');
	
	$sql = "UPDATE android_recepciones SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de android_recepciones desde movil..'),'info');
	
	$sql = "UPDATE gltrans SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de contabilidad..'),'info');
	
	$sql = "UPDATE gltranspay SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de gltranspay ..'),'info');
	
	$sql = "UPDATE lastcostrollup SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de lastcostrollup ..'),'info');
	
	
	$sql = "UPDATE mrp_calendar_AM_detail SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update mrp_calendar_AM_detail transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de mrp_calendar_AM_detail ..'),'info');
	
	$sql = "UPDATE notesorderstockserials SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update notesorderstockserials transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de notesorderstockserials ..'),'info');
	
	$sql = "UPDATE notesorderstockserials SET stockidparent='" . $_POST['NewStockidNo'] . "' WHERE stockidparent='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update notesorderstockserials transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de notesorderstockserials ..'),'info');
	
	$sql = "UPDATE plcdata SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update plcdata transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de plcdata ..'),'info');
	
	$sql = "UPDATE prdpropiedad SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update prdpropiedad transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de prdpropiedad ..'),'info');
	
	
	$sql = "UPDATE priceparameters SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update priceparameters transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de priceparameters ..'),'info');
	
	$sql = "UPDATE purchaseagreement SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update priceparameters transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de priceparameters ..'),'info');
	
	$sql = "UPDATE purchbudgetdetails SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update priceparameters transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de priceparameters ..'),'info');
	
	$sql = "UPDATE salesorderstockserials SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update salesorderstockserials transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de salesorderstockserials ..'),'info');
	
	$sql = "UPDATE salesorderstockserials SET stockidparent='" . $_POST['NewStockidNo'] . "' WHERE stockidparent='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update salesorderstockserials transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de salesorderstockserials ..'),'info');
	
	$sql = "UPDATE salesxdiscounts SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update salesxdiscounts transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de salesxdiscounts ..'),'info');
	
	$sql = "UPDATE shipmentcharges SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update shipmentcharges transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de shipmentcharges ..'),'info');
	
	$sql = "UPDATE shippinglog SET shippingparent='" . $_POST['NewStockidNo'] . "' WHERE shippingparent='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update shipmentcharges transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de shipmentcharges ..'),'info');
	
	$sql = "UPDATE shippingorderdetails SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update shippingorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de shippingorderdetails ..'),'info');
	
	$sql = "UPDATE shippingorders SET shippingparent='" . $_POST['NewStockidNo'] . "' WHERE shippingparent='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update shippingorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de shippingorders ..'),'info');
	
	$sql = "UPDATE shippingserialitems SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update shippingserialitems transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de shippingserialitems ..'),'info');
	
	$sql = "UPDATE stockadjustmentorders SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockadjustmentorders transaction records failed');
	//$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de stockadjustmentorders ..'),'info');
	
	$sql = "UPDATE stockcheckfreeze SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockcheckfreeze transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de stockcheckfreeze ..'),'info');
	
	
	$sql = "UPDATE stockmovesremisionanticipo SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockmovesremisionanticipo transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de stockmovesremisionanticipo ..'),'info');
	
	
	$sql = "UPDATE stockrelations SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockrelations transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de stockrelations ..'),'info');
	
	$sql = "UPDATE woserialnos SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update woserialnos transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de woserialnos ..'),'info');
	
	
	// actualiza tabla de bom
	$sqlot = "SELECT *
			  FROM bom
			WHERE parent='".$_POST['OldStockidNo']."'";
	$resultot = DB_query($sqlot, $db);
	while($myrowot = DB_fetch_array($resultot)){
		$sqlwo = "SELECT *
				FROM bom
				WHERE parent = '".$_POST['NewStockidNo']."'
				AND component='".$myrowot['component']."'
				AND bom_category_id = '".$myrowot['bom_category_id']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) == 0){
			$sql = "UPDATE bom SET parent='" . $_POST['NewStockidNo'] . "'
					 WHERE parent='" . $_POST['OldStockidNo'] . "'
					 	AND component='".$myrowot['component']."'
						AND bom_category_id = '".$myrowot['bom_category_id']."'";
			$ErrMsg = _('The SQL to update bom transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}else{
			$sql = "DELETE bom 
					WHERE parent='" . $_POST['OldStockidNo'] . "'
					 	AND component='".$myrowot['component']."'
						AND bom_category_id = '".$myrowot['bom_category_id']."'";
			$ErrMsg = _('The SQL to update bom transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
	}
	
	prnMsg(_('Cambiando registros en tabla de bom-parent ..'),'info');

	// actualiza tabla de bom component
	$sqlot = "SELECT *
			  FROM bom
			WHERE component='".$_POST['OldStockidNo']."'";
	$resultot = DB_query($sqlot, $db);
	while($myrowot = DB_fetch_array($resultot)){
		$sqlwo = "SELECT *
				FROM bom
				WHERE component = '".$_POST['NewStockidNo']."'
				AND parent='".$myrowot['parent']."'
				AND bom_category_id = '".$myrowot['bom_category_id']."'";
		$resultwo = DB_query($sqlwo, $db);
		if(DB_num_rows($resultwo) == 0){
			$sql = "UPDATE bom SET component='" . $_POST['NewStockidNo'] . "'
					 WHERE component='" . $_POST['OldStockidNo'] . "'
					 	AND parent='".$myrowot['parent']."'
						AND bom_category_id = '".$myrowot['bom_category_id']."'";
			$ErrMsg = _('The SQL to update bom transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}else{
			$sql = "DELETE bom
					WHERE component='" . $_POST['OldStockidNo'] . "'
					 	AND parent='".$myrowot['parent']."'
						AND bom_category_id = '".$myrowot['bom_category_id']."'";
			$ErrMsg = _('The SQL to update bom transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
	}
	
	prnMsg(_('Cambiando registros en tabla de bom-component ..'),'info');
	
	/********* FIN MOVIMIENTOS *******/
	
	/********* INICIO DE MOVIMIENTOS EN PRODUCTOS EN LAS TABLAS DONDE SE ENCUENTRE EL CAMPO STOCKID *******/
	/*Directa
	SELECT
	prices.stockid 
	prices.typeabbrev 
	prices.currabrev 
	prices.debtorno 
	prices.price 
	prices.branchcode 
	prices.areacode 
	prices.bgcolor
	FROM prices
	*/
	//Actualiza el stockid nuevo
	//FCC - 02042011 .- COMENTE LAS LINEAS DEL UPDATE DE LA TABLA DE PRICES, DEBIDO Q' NO SE UNIFICA POR QUE EL PRECIO QUE DEBE QUEDAR
	//EL DEL PRODUCTO AL CUAL SE UNIFICO, EN SU LUGAR AGREGUE LA SENTENCIA SQL PARA ELIMINAR ESE PRECIO.
	/**$sql = "UPDATE prices SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update prices transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de precios ...'),'info');
	*/
	
	$sql = "DELETE FROM prices WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('La sentencia SQL fallo debido a');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Eliminando registros de la tabla de precios ...'),'info');
	
	/*Directa Vacia
	SELECT
	stockshortsales.shortid 
	stockshortsales.stockid 
	stockshortsales.shortname 
	stockshortsales.quantity 
	stockshortsales.price 
	stockshortsales.image 
	stockshortsales.displayorder 
	stockshortsales.desc1 
	stockshortsales.desc2 
	stockshortsales.desc3 
	stockshortsales.displayline
	FROM stockshortsales
	*/
	//Actualiza el stockid nuevo 
	/*$sql = "UPDATE stockshortsales SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockshortsales transaction records failed');
	//$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla  ...'),'info');*/
	
	/*Directa Vacia
	SELECT
	stockpromosales.promoid 
	stockpromosales.promoname 
	stockpromosales.fixedquantity 
	stockpromosales.quantityfactor 
	stockpromosales.fixedprice 
	stockpromosales.pricefactor 
	stockpromosales.desc1 
	stockpromosales.desc2 
	stockpromosales.desc3 
	stockpromosales.image 
	stockpromosales.displayorder 
	stockpromosales.stockid
	FROM stockpromosales
	*/
	//Actualiza el stockid nuevo 
	/*$sql = "UPDATE stockshortsales SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockshortsales transaction records failed');
	//$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla  ...'),'info');*/

	/*Directa
	SELECT 
	stockserialimages.stockid 
	stockserialimages.loccode 
	stockserialimages.serialno 
	stockserialimages.imagenid 
	stockserialimages.imagen
	FROM stockserialimages
	*/
	//Actualiza el stockid nuevo 
	$sql = "UPDATE stockserialimages SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockserialimages transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de numeros de serie x producto ...'),'info');
	
	/*Vacia
	SELECT
	stockitemproperties.stockid 
	stockitemproperties.stkcatpropid 
	stockitemproperties.value
	FROM stockitemproperties 
	*/
	//Actualiza el stockid nuevo 
	/*$sql = "UPDATE stockitemproperties SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockitemproperties transaction records failed');
	//$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla de propiedades del producto ...'),'info');*/
	
	
	/* NDirecta	
	SELECT
	locstock.loccode 
	locstock.stockid 
	locstock.quantity 
	locstock.reorderlevel 
	locstock.ontransit 
	FROM locstock 
	*/
	
	//Busqueda de producto oldstockid 
	$Sql= "SELECT stockid, loccode FROM locstock
		WHERE stockid='".$_POST['OldStockidNo']."'";
	$Result = DB_query($Sql,$db,$ErrMsg,$DbgMsg,true);
	
	while ($myrow2 = DB_fetch_array($Result)){
	//Realiza la suma de cantidades existente del oldtag para actalizar el newstockid y poder borrar el viejo
		$sql = "SELECT sum(quantity) as qtyold,sum(ontransit) as oldtransit,sum(reorderlevel) as oldreorder
			FROM locstock
			WHERE stockid='".$_POST['OldStockidNo']."'
			AND loccode='" . $myrow2['loccode'] . "'";
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$myrow = DB_fetch_array($result);
		//echo "<br><br>consulta qtyold".$sql;
	//Realiza la suma de cantidades existente del newstockid 
		$sql2 = "SELECT sum(quantity) as qtynew,sum(ontransit) as newtransit,sum(reorderlevel) as newreorder
			FROM locstock
			WHERE stockid='".$_POST['NewStockidNo']."'
			AND loccode='" . $myrow2['loccode'] . "'";
		$result2 = DB_query($sql2,$db,$ErrMsg,$DbgMsg,true);
		$myrownew = DB_fetch_array($result2);
		//echo "<br><br>consulta qtynew".$sql2;
	//Actualizacion de quantity, ontransit para el newstockid,//
		$sql = "UPDATE locstock
		SET quantity=('".$myrow['qtyold']."' + '".$myrownew['qtynew']."'),
		ontransit=('".$myrow['oldtransit']."' + '".$myrownew['newtransit']."'),
		reorderlevel=('".$myrow['oldreorder']."' + '".$myrownew['newreorder']."')
		WHERE loccode='" .$myrow2['loccode'] . "'
		AND stockid='".$_POST['NewStockidNo']."'";
		$ErrMsg = _('The SQL to update locstock transaction records failed');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	}
		prnMsg(_('Cambiando registros en cada almacen ...'),'info');
	
	//Eliminación de stockid anterior de la Tabla de locstock//
	$sql = "DELETE FROM locstock WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to DELETE old stockid record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Borrando clave de stockid anterior...'),'info');
	
	/*	
	SELECT
	purchdata.supplierno 
	purchdata.stockid 
	purchdata.price 
	purchdata.suppliersuom 
	purchdata.conversionfactor 
	purchdata.supplierdescription 
	purchdata.leadtime 
	purchdata.preferred 
	purchdata.effectivefrom 
	purchdata.suppliers_partno
	FROM purchdata 
	*/
	$Sql= "SELECT supplierno FROM purchdata WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$resultado1 = DB_query($Sql,$db,$ErrMsg,$DbgMsg,true);
	$myrowres = DB_fetch_array($resultado1);
	$Sql2= "SELECT supplierno FROM purchdata WHERE stockid='" . $_POST['NewStockidNo'] . "'";
	$resultado2 = DB_query($Sql2,$db,$ErrMsg,$DbgMsg,true);
	$myrowres2 = DB_fetch_array($resultado2);
	//Actualiza el stockid nuevo
	if($myrowres['supplierno'] != $myrowres2['supplierno'])
	{
	$sql = "UPDATE purchdata SET stockid='" . $_POST['NewStockidNo'] . "' WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update purchdata transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros en tabla ...'),'info');
	}else{
	$sql = "DELETE FROM purchdata WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old stockid record failed');
	prnMsg(_('Borrando clave de producto anterior...'),'info');	
		
	}
	//POR ULTIMO SE UNIFICA EN LA TABLA STOCKMASTER.
	/*
	 SELECT  
	stockmaster.stockid 
	stockmaster.spes 
	stockmaster.categoryid 
	stockmaster.description 
	stockmaster.longdescription 
	stockmaster.manufacturer 
	stockmaster.units 
	stockmaster.mbflag 
	stockmaster.lastcurcostdate 
	stockmaster.actualcost 
	stockmaster.lastcost 
	stockmaster.materialcost 
	stockmaster.labourcost 
	stockmaster.overheadcost 
	stockmaster.lowestlevel 
	stockmaster.discontinued 
	stockmaster.controlled 
	stockmaster.eoq 
	stockmaster.volume 
	stockmaster.kgs 
	stockmaster.barcode 
	stockmaster.discountcategory 
	stockmaster.taxcatid 
	stockmaster.serialised 
	stockmaster.appendfile 
	stockmaster.perishable 
	stockmaster.decimalplaces 
	stockmaster.nextserialno 
	stockmaster.pansize 
	stockmaster.shrinkfactor 
	stockmaster.netweight 
	stockmaster.idclassproduct 
	stockmaster.stocksupplier
	FROM stockmaster
	*/

	$sql = "DELETE FROM stockmaster WHERE stockid='" . $_POST['OldStockidNo'] . "'";
	
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old stockid record failed');

	prnMsg(_('Borrando clave de producto anterior...'),'info');
		
	$result = DB_Txn_Commit($db);
}

echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Codigo de Stockid Origen') . ":</td>
		<td><input type=Text name='OldStockidNo' size=20 maxlength=20></td>
	</tr>";
echo '<tr><td> ' . _('Codigo de Stockid Destino') . ":</td>
	<td><input type=Text name='NewStockidNo' size=20 maxlength=20></td>
	</tr>";
	

echo "<tr><td colspan='2'><input type=submit name='ProcessStockidChange' VALUE='" . _('Procesar Cambio...') . "'>";
echo "</td></tr></table>";
echo '</form>';

include('includes/footer.inc');

?>