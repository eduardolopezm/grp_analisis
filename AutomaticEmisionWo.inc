<?php
$SQL = "SELECT distinct grns.*,woreq_purchorders.wo,stockmaster.mbflag,woreq_purchorders.flagautoemision,stockmoves.tagref,
		stockidnivel,loccode,prd,trandate,
		 stockcategory.wipact,
         stockcategory.stockact
	FROM grns INNER JOIN purchorderdetails
		ON  grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN stockmaster ON stockmaster.stockid=purchorderdetails.itemcode
		LEFT JOIN stockmoves ON stockmoves.transno=grns.grnbatch and stockmoves.`type`=25  and stockmoves.stockid=purchorderdetails.itemcode
		INNER JOIN purchorders ON purchorders.orderno = purchorderdetails.orderno
		inner join stockcategory on stockcategory.categoryid=stockmaster.categoryid
		 JOIN woreq_purchorders ON purchorderdetails.wo=woreq_purchorders.wo
					AND woreq_purchorders.stockid=purchorderdetails.itemcode and woreq_purchorders.stockid=stockmoves.stockid
					AND woreq_purchorders.stockidnivel=purchorderdetails.wocomponent
	WHERE grns.grnbatch = '" . $GRN . "'
	ORDER BY grns.grnno";
$GRNResultsWO = DB_query($SQL,$db);
$WOIssueNo = GetNextTransNo(28, $db);
while ($myrowot=DB_fetch_array($GRNResultsWO)){
	if($myrowot['mbflag']=='D' or $myrowot['flagautoemision']==1){
		$estavgcostXlegal=StockAvgcostXLegal($myrowot['itemcode'],$myrowot['tagref'],$db);
		$QuantityIssued=$myrowot['qtyrecd'];
		
		$sql = "UPDATE woreq_purchorders 
				SET transferqty = transferqty + $QuantityIssued WHERE wo = '{$myrowot['wo']}'
				AND stockid = '".$myrowot['itemcode']."'
				AND stockidnivel='". $myrowot['stockidnivel']."'";
				// echo $sql;
		$Result=DB_query($sql, $db);
		
		$StockGLCode = GetStockGLCode($myrowot['itemcode'],$db);
					
		$SQL = "UPDATE locstock
				SET quantity = locstock.quantity - " . $QuantityIssued . "
				WHERE locstock.stockid = '" . $myrowot['itemcode'] . "'
					AND loccode = '" . $myrowot['loccode'] . "'";
		$Result=DB_query($SQL, $db);
		$NewQtyOnHand=0;
		$SQL = "INSERT INTO stockmoves (stockid,
                        type,
                        transno,
                        loccode,
                        trandate,
                        price,
                        prd,
                        reference,
                        qty,
                        standardcost,
						tagref,
                        newqoh)
                    VALUES ('" . $myrowot['itemcode'] . "',
                            28,
                            " . $WOIssueNo . ",
                            '" . $myrowot['loccode'] . "',
                            '" .  $myrowot['trandate'] . "',
                            " . $estavgcostXlegal . ",
                            " . $myrowot['prd'] . ",
                            '" . $myrowot['wo'] . "',
                            " . -$QuantityIssued . ",
                            " . $estavgcostXlegal . ",
                            ".$myrowot['tagref'].",
                            " . $NewQtyOnHand . ")";
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
		$DbgMsg =  _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		//Movimientos contables
		if ($_SESSION['CompanyRecord']['gllink_stock']==1){
			$tagref=$myrowot['tagref'];
			
			$SQL = "INSERT INTO gltrans (type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,tag)
                    VALUES (28,
                        " . $WOIssueNo . ",
                        '" .  $myrowot['trandate'] . "',
                        " . $myrowot['prd'] . ",
                        '" . $myrowot['wipact'] . "',
                        '" . $myrowot['wo'] . " " . $myrowot['itemcode'] . ' x ' . $QuantityIssued . " @ " . number_format($estavgcostXlegal,2) . "',
                        " . ($estavgcostXlegal * $QuantityIssued) . ",$tagref)";
				
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The issue of the item to the work order GL posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the work order issue GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
				
				
				
			/*now the credit Stock entry*/
			$SQL = "INSERT INTO gltrans (type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,tag)
                    VALUES (28,
                        " . $WOIssueNo . ",
                        '" . $myrowot['trandate'] . "',
                        " . $myrowot['prd'] . ",
                        '" . $StockGLCode['stockact'] . "',
                        '" . $myrowot['wo'] . " " . $myrowot['itemcode'] . ' x ' . $QuantityIssued . " @ " . number_format($estavgcostXlegal,2) . "',
                        " . -($estavgcostXlegal * $QuantityIssued) . ",$tagref)";
				
			//echo "<pre>$SQL";
				
			$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock account credit on the issue of items to a work order GL posting could not be inserted because');
			$DbgMsg =  _('The following SQL to insert the stock GLTrans record was used');
			$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
		}
		prnMsg(_('Se realizo la emision de material el codigo de producto '.$myrowot['itemcode']),'sucess');
		
		$SQL="UPDATE workorders
                        SET costissued=costissued+" . ($QuantityIssued*$estavgcostXlegal) . "
                        WHERE wo=" . $myrowot['wo'];
		$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);
		
	}
		
}
?>
