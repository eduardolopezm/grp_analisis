<?php

function createShippingMovements($shippingno, $trackingNumber, $name, $deliveryDate, $comments, $stkmoves = array(), $totpeso=0, $totpzas=0, &$db,$stkmovesserial = array(),$camion=NULL,$chofer=NULL,$serializado = array(),$stkmovesqtyserial = array(),$stkmovestotal = array()) {
	$Result = DB_Txn_Begin($db);
	define('DOCUMENT_TYPE', 52);

	$error 	  		= FALSE;
	$filteredMoves 	= array();
	$folio 			= '';
	$order 			= '';
	$typeno			= '';
	$type			= '';
	$tagref			= '';
	$areacode		= '';
	$shippingDate   = '';
	$debtortransid  = '';
	$debtorno		= '';
	$branchcode		= '';
	$userid			= '';
	$prd 			= '';
	$reference		= '';
	$tpe			= '';
	$posted			= 0;
	$jobref			= 0;
	$chequeno		= 0;
	$amount 		= 0;
	$narrative		= '';
	$account		= '';
	$idShippingLog	= 0;
	$legalid		= '';

	list($year, $month, $day) = explode('-', $deliveryDate);

//	$periodno = GetPeriod($day . '-' . $month . '-' . $day, $db);
	$DefaultDispatchDate=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
	$periodno = GetPeriod($DefaultDispatchDate, $db);
//echo '<pre>'.var_dump($stkmoves);
//exit;
	foreach($stkmoves as $stkmoveno => $qty) {

		if($qty <= 0) {
			$error = TRUE;
			prnMsg(_('Verifique el producto' . ' ' . $row['stockid'] . ' ' . 'no tiene cantidad positiva'), 'error');
		}

		$SQL = "SELECT price, stockid, qty, qty_sent, loccode, avgcost FROM shippingorderdetails WHERE stkmoveno = '$stkmoveno' AND shippingno = '$shippingno'";
		$rs = DB_query($SQL, $db);
		if($row = DB_fetch_array($rs)) {
			$total = $row['qty_sent'] + $qty;
			if($total > $row['qty']) {
				$error = TRUE;
				prnMsg(_('Verifique el producto' . ' ' . $row['stockid'] . ' ' . 'ha sobrepasado la cantidad a enviar'), 'error');
			} else {

				$filteredMoves[$stkmoveno] = array(

					'qty' 		=> $qty,
					'price' 	=> $row['price'],
					'stockid'	=> $row['stockid'],
					'loccode'	=> $row['loccode'],
					'avgcost'	=> $row['avgcost'],
					'serial'	=> $stkmovesserial[$stkmoveno],
					'serializado' => $serializado[$stkmoveno]
				);
			}
		}
	}
	//actualizar peso total y cantidad de piezas
	foreach($filteredMoves as $stkmoveno => $prop) {
		$cantporenvia = $prop['qty'];
	}

	$cantidadenviadas = $cantporenvia + $cantidadenviadas;

	$qry2 = "SELECT shippingorderdetails.qty_sent,
					shippingorderdetails.qty
			FROM shippingorderdetails
			WHERE shippingno = '".$shippingno."'";
	$result2 = DB_query($qry2, $db);
	while($myrow2 = DB_fetch_array($result2)){
		$cantidad = $myrow2['qty'];
		$cantidadenviadas = $myrow2['qty_sent'];
	}

	$cantidadenviadas = $cantporenvia + $cantidadenviadas;

	if($cantidad == $cantidadenviadas){
		$qry = "UpDate shippingorders
		Set shippingstatusid = 1,
		totalpeso = $totpeso,
		totalpiezas = $totpzas
		WHERE shippingno = '$shippingno'";
		//$r = DB_query($qry,$db);
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo actuaizar el peso, total y cantidad de piezas');
		$DbgMsg = _('El siguiente SQL para actuaizar el peso, total y cantidad de piezas');
		$Result = DB_query($qry, $db, $ErrMsg, $DbgMsg, true);

	}elseif($cantidad > $cantidadenviadas){
		$qry = "UpDate shippingorders
		Set shippingstatusid = 4,
		totalpeso = $totpeso,
		totalpiezas = $totpzas
		WHERE shippingno = '$shippingno'";
		//$r = DB_query($qry,$db);
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo actuaizar el peso, total y cantidad de piezas');
		$DbgMsg = _('El siguiente SQL para actuaizar el peso, total y cantidad de piezas');
		$Result = DB_query($qry, $db, $ErrMsg, $DbgMsg, true);

	}


	if(empty($filteredMoves)) {
		$error = TRUE;
	}
//echo var_dump($filteredMoves).'<br>';//
	$SQL = "
		SELECT
		shippingorders.folio,
		shippingorders.orderno,
		shippingorders.tagref,
		shippingorders.shippingdate,
		shippingorders.userid,
		debtortrans.id,
		debtortrans.tpe,
		debtortrans.debtorno,
		debtortrans.branchcode,
		debtortrans.transno,
		tags.areacode,
		tags.legalid
		FROM shippingorders
		INNER JOIN debtortrans
		ON debtortrans.id = shippingorders.debtortransid
		LEFT JOIN tags
		ON tags.tagref = shippingorders.tagref
		WHERE shippingno = '$shippingno'
	";

	$rs = DB_query($SQL, $db);

	if($row = DB_fetch_array($rs)) {
		$folio 			= $row['folio'];
		$order			= $row['orderno'];
		$tagref			= $row['tagref'];
		$shippingDate	= $row['shippingdate'];
		$userid			= $row['userid'];
		$debtortransid	= $row['id'];
		$debtorno		= $row['debtorno'];
		$branchcode		= $row['branchcode'];
		$reference  	= ''; //$row['transno'];
		$tpe  			= $row['tpe'];
		$areacode		= $row['areacode'];
		$legalid		= $row['legalid'];
	} else {
		$error = TRUE;
		prnMsg(_('Numero de orden no valido'), 'error');
	}

	if($error == FALSE) {

		$typeno = GetNextTransNo(DOCUMENT_TYPE, $db);

		$InvoiceNoTAG = DocumentNext(DOCUMENT_TYPE, $tagref, $areacode, $legalid, $db);
		$separa = explode('|', $InvoiceNoTAG);
		$serieEnvio = $separa[1];
		$folioEnvio = $separa[0];

		$SQL = "INSERT INTO shippinglog (
				shippingno,
				type,
				typeno,
				folio,
				orderno,
				debtortransid,
				comments,
				name,
				trackingnumber,
				userid,
				deliverydate,
				shippingdate,
				tagref,
				dateadded,
				camion,
				chofer,
				folio_envio
			) VALUES (
				'$shippingno',
				'" . DOCUMENT_TYPE . "',
				'$typeno',
				'$folio',
				'$order',
				'$debtortransid',
				'$comments',
				'$name',
				'$trackingNumber',
				'$userid',
				'$deliveryDate',
				'$shippingDate',
				'$tagref',
				NOW(),
				'$camion',
				'$chofer',
				'$serieEnvio|$folioEnvio'
			)
		";
		//echo '<pre>'.$SQL;
		//DB_query($SQL, $db);
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo insertar en el log');
		$DbgMsg = _('El siguiente SQL para insertar en el log:');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		$idShippingLog = DB_Last_Insert_ID($db, 'shippinglog', 'shippinglogid');

		foreach($filteredMoves as $stkmoveno => $prop) {

			$SQL = "UPDATE shippingorderdetails SET qty_sent = qty_sent + {$prop["qty"]} WHERE stkmoveno = '$stkmoveno' AND shippingno = '$shippingno'";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El embarque no se puede actualizar por que');
			$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			$SQL = "UPDATE locstock SET quantity = quantity - {$prop["qty"]}, qtybysend = qtybysend - {$prop["qty"]} WHERE stockid = '{$prop["stockid"]}' AND loccode = '{$prop["loccode"]}'";
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo actualizar el movimiento del producto');
					$DbgMsg = _('El siguiente SQL para actualizar el movimiento del producto es:');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			$amount 	= $prop['qty'] * $prop['avgcost'];
			if($prop['serializado'] == 1){
				for($y = 0; $y<=$stkmovestotal[$stkmoveno];$y++){
					if($stkmovesserial[$stkmoveno][$y] >0){
						$reference .=  $stkmovesserial[$stkmoveno][$y].',';
					}

				}
			}else{
				$reference 	= $stkmoveno;
			}




			$SQL = "INSERT INTO stockmoves (
					stockid,
					type,
					transno,
					loccode,
					trandate,
					debtorno,
					branchcode,
					price,
					prd,
					reference,
					tagref,
					qty
				) VALUES (
					'{$prop['stockid']}',
					'" . DOCUMENT_TYPE . "',
					'$typeno',
					'{$prop['loccode']}',
					NOW(),
					'$debtorno',
					'$branchcode',
					'$amount',
					'$prd',
					'$reference',
					'$tagref',
					-'{$prop['qty']}'
				)
			";

			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo insertar el movimiento del producto');
					$DbgMsg = _('El siguiente SQL para insertar el movimiento del producto:');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			//echo '<br><pre>'.$SQL;

			$narrative = "ENVIO DE MATERIAL STKMOVENO $stkmoveno";
			$amount = abs($amount);

			$cuentainventario = GetCOGSGLAccountByDelivery($areacode, $prop['stockid'], $tpe, $db);
			//$cuentainventario=GetCOGSGLAccount($areacode, $prop['stockid'], $tpe, $db);

			//$account='215000008';
			$SQL = "INSERT INTO gltrans (
					type,
					typeno,
					chequeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					posted,
					jobref,
					tag,
					lasttrandate
				) VALUES (
					'" . DOCUMENT_TYPE . "',
					'$typeno',
					'$chequeno',
					now(),
					'$periodno',
					'$cuentainventario',
					'$narrative',
					'$amount',
					'$posted',
					'$jobref',
					'$tagref',
					NOW()
				)
			";

			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo insertar los movimientos contables');
			$DbgMsg = _('El siguiente SQL para No se pudo insertar los movimientos contables');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);


			$account = GetCOGSGLAccountByDelivery($areacode, $prop['stockid'], $tpe, $db);
			//$account='215000008';
			//$account='215000008';

			$SQL = "INSERT INTO gltrans (
					type,
					typeno,
					chequeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					posted,
					jobref,
					tag,
					lasttrandate
				) VALUES (
					'" . DOCUMENT_TYPE . "',
					'$typeno',
					'$chequeno',
					now(),
					'$periodno',
					'$account',
					'$narrative',
					'" . ($amount * -1) . "',
					'$posted',
					'$jobref',
					'$tagref',
					NOW()
				)
			";
			//echo '<br><pre>sql2<br>'.$SQL;
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se pudo insertar los movimientos contables');
			$DbgMsg = _('El siguiente SQL para No se pudo insertar los movimientos contables');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			/* actualizar el serial */

			if($prop['serializado'] == 1){

				if($prop['serial'] <> ""){
					/////////////////////////////////////////;

					for($y = 0; $y<=$stkmovestotal[$stkmoveno];$y++){
						if($stkmovesqtyserial[$stkmoveno][$y] >0){
							$SQL = "UPDATE stockserialitems
											SET quantity= quantity - " . $stkmovesqtyserial[$stkmoveno][$y] . "
											WHERE stockid='" . $prop['stockid'] . "'
												AND loccode='" . $prop['loccode'] . "'
												AND serialno='" . $stkmovesserial[$stkmoveno][$y] . "'";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede actualizar por que');
							$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');

							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
														stockid,
														serialno,
														moveqty,
														standardcost
														)
													VALUES (" . $stkmoveno . ",
															'" . $prop['stockid']. "',
															'" . $stkmovesserial[$stkmoveno][$y] . "',
															" . - $stkmovesqtyserial[$stkmoveno][$y] .",
															" . $prop['avgcost'] .
																						")";
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede insertar por que');
							$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');

							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

							$SQL = "INSERT INTO shippingserialitems (serialno,
															 cantidad,
															 shippinglogid,
															 stkmoveno,
															 stockid)
							VALUES ('".$stkmovesserial[$stkmoveno][$y]."',
									'".$stkmovesqtyserial[$stkmoveno][$y]."',
									'".$idShippingLog."',
									'".$stkmoveno."',
									'".$prop['stockid']."')";
							//echo '<pre>'.$SQL;//
							$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede insertar por que');
							$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');
							//echo '<pre>2'.$SQL;////
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}
					}

				}
			}
			/* actualizar el serial*/
		}
	}
	$Result = DB_Txn_Commit($db);
	return $idShippingLog;
}
		//
function cancelShippingNo($shippingNo, &$db) {

	$result = DB_query("SELECT type, typeno, folio, orderno, debtortransid FROM shippinglog WHERE shippingno = '$shippingNo'", $db);

	if(DB_num_rows($result) > 0) {

		while($row = DB_fetch_array($result)) {

			$type 		= $row['type'];
			$typeno 	= $row['typeno'];
			$orderno 	= $row['orderno'];

			$sql = "
				SELECT
				stockid,
				loccode,
				trandate,
				debtorno,
				branchcode,
				price,
				prd,
				reference,
				qty
				FROM stockmoves
				WHERE type = '$type'
				AND transno = '$typeno'
			";

			$rs = DB_query($sql, $db);

			while($stockmov = DB_fetch_array($rs)) {

				$stockid 	= $stockmov['stockid'];
				$loccode 	= $stockmov['loccode'];
				$trandate 	= $stockmov['trandate'];
				$debtorno 	= $stockmov['debtorno'];
				$branchcode = $stockmov['branchcode'];
				$price 		= $stockmov['price'];
				$prd 		= $stockmov['prd'];
				$reference 	= $stockmov['reference'];
				$qty 		= abs($stockmov['qty']);

				$SQL = "UPDATE shippingorderdetails SET qty_sent = qty_sent - $qty WHERE stkmoveno = '$reference' AND shippingno = '$shippingNo'";
				DB_query($SQL, $db);

				$SQL = "UPDATE locstock SET
				quantity = quantity + $qty,
				qtybysend = if(qtybysend - $qty > 0, qtybysend - $qty, 0)
				WHERE stockid = '$stockid' AND loccode = '$loccode'";
				DB_query($SQL, $db);

				$SQL = "INSERT INTO stockmoves (
						stockid,
						type,
						transno,
						loccode,
						trandate,
						debtorno,
						branchcode,
						price,
						prd,
						reference,
						qty
					) VALUES (
						'$stockid',
						'$type',
						'$typeno',
						'$loccode',
						NOW(),
						'$debtorno',
						'$branchcode',
						'$price',
						'$prd',
						'$reference',
						'" . ($qty) ."'
					)
				";

				DB_query($SQL, $db);
			}

			$sql = "
				SELECT
				chequeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				posted,
				jobref,
				tag,
				lasttrandate
				FROM gltrans
				WHERE type = '$type'
				AND typeno = '$typeno'
			";

			$rs = DB_query($sql, $db);

			while($gltrans = DB_fetch_array($rs)) {

				$chequeno 		= $gltrans['chequeno'];
				$trandate 		= $gltrans['trandate'];
				$periodno 		= $gltrans['periodno'];
				$account 		= $gltrans['account'];
				$narrative 		= $gltrans['narrative'];
				$amount 		= $gltrans['amount'];
				$posted 		= $gltrans['posted'];
				$jobref 		= $gltrans['jobref'];
				$tag 			= $gltrans['tag'];
				$lasttrandate 	= $gltrans['lasttrandate'];

				$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						chequeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						posted,
						jobref,
						tag,
						lasttrandate
					) VALUES (
						'$type',
						'$typeno',
						'$chequeno',
						'$trandate',
						'$periodno',
						'$account',
						'$narrative',
						'" . ($amount * -1) . "',
						'$posted',
						'$jobref',
						'$tag',
						'$lasttrandate'
					)
				";

				DB_query($SQL, $db);
			}

			DB_query("UPDATE shippingorders SET cancelled = 1 WHERE shippingno = '$shippingNo'", $db);

		}

		return TRUE;

	} else {
		// Cancelar aunque no tenga movimientos
		$SQL	= "SELECT stockid, loccode, qty FROM shippingorderdetails WHERE shippingno =  '$shippingNo'";
		$rsy 	= DB_query($SQL, $db);

		while($row = DB_fetch_array($rsy)) {
			$stockid 	= $row['stockid'];
			$loccode	= $row['loccode'];
			$qty		= $row['qty'];
			$SQL	 	= "UPDATE locstock SET qtybysend = if(qtybysend - $qty > 0, qtybysend - $qty, 0) WHERE stockid = '$stockid' AND loccode = '$loccode'";
			$Result 	= DB_query($SQL, $db);
		}

		$rs = DB_query("SELECT 1 FROM shippingorders WHERE shippingno = '$shippingNo'", $db);
		if(DB_num_rows($rs) > 0) {
			DB_query("UPDATE shippingorders SET cancelled = 1 WHERE shippingno = '$shippingNo'", $db);
			return TRUE;
		}
	}
	return FALSE;
}

function cancelarMovimientosXEntregar($shippingNo, &$db) {

	// Cancelar Movimientos de tipo 51 (X Entregar)
	$tipo 	= 51;

	$SQL = "INSERT INTO stockmoves(stockid, type, transno, loccode, trandate, debtorno, branchcode, price,
	prd, reference, qty, discountpercent, standardcost, show_on_inv_crds,newqoh,hidemovt, narrative,
	warranty, tagref, discountpercent1, discountpercent2, totaldescuento, avgcost)
	SELECT stockid,type,transno,loccode,trandate,debtorno,branchcode,price,
	prd, reference,qty*-1,discountpercent, standardcost, show_on_inv_crds,newqoh,hidemovt,CONCAT('CANCELADO ', narrative) AS narrative,
	warranty, tagref, discountpercent1, discountpercent2, totaldescuento, avgcost
	FROM stockmoves
	WHERE type = '$tipo'
	AND transno = '$shippingNo'
	";

	DB_query($SQL, $db);
}
?>
