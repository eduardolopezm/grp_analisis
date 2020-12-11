<?php

$Result = DB_Txn_Begin($db);
$Total=$_POST['Total'];
//$PeriodNo= GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
$cantidadTotal= $OrderLine->ReceiveQty;
$CantidadTpiv=0;
$lineacompra=0;
$sql="SELECT transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem
	FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
	WHERE transferrequistions.purchorderno = ".$_SESSION['PO']->OrderNo."
	 and requisitionorderdetails.itemcode='".$OrderLine->StockID."'
	 Order by priority desc ,requisitionorderdetails.orddate asc";
$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de requisicion de compra no se pudo obtener');
$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de compra');
$ResultCompra = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
while ($myrow=DB_fetch_array($ResultCompra)){
	
	

	$_POST['comments']="Traspaso Automatico de la Compra:".$_SESSION['PO']->OrderNo.' De La Requisision:'.$myrow['podetailitem'];
	$CantidadT=$myrow['quantityord'];
	$LocationReq=$myrow['intostocklocation'];
	$LocationReqIni=$myrow['intostocklocation'];
	$Tagref = $myrow['tagref'];
	$requisitionno=$myrow['podetailitem'];
	// Buscar el almacen temporal para hacer la transferencia
	//$SQL="select * from locations where tagref='".$Tagref."' and temploc=1";
	//$Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	//$CompraAlmacenRow = DB_fetch_row($Resultalmacen);
	//$LocationReq = $CompraAlmacenRow[0];
	if(($cantidadTotal-($CantidadTpiv+$CantidadT))<0){
		$CantidadT=$cantidadTotal-($CantidadTpiv);
	}else{
		$CantidadT=$CantidadT;
	}
	if($CantidadT>0 and strlen($CantidadT)>0 and $LocationReq!=$_SESSION['PO']->Location){
		$Trf_ID = GetNextTransNo(16,$db);
		$_POST['Trf_ID'] = $Trf_ID;
		$sql = "INSERT INTO loctransfers (reference,
							stockid,
							shipqty,
							recqty,
							shipdate,
							recdate,
							shiploc,
							recloc,
							comments,
							serialno,
							userregister,
							transferline
							)
					VALUES ('" . $_POST['Trf_ID'] . "',
						'" . strtoupper($OrderLine->StockID) . "',
						'" . $CantidadT . "',
						0,
						
						'" . Date('Y-m-d') . "',
						'" . Date('Y-m-d') . "',
						'" . $_SESSION['PO']->Location  ."',
						'" . $LocationReq. "',
						'" . $_POST['comments'] . "',
						'" . $_POST['serialno'.$i] . "',
						'" . $_SESSION['UserID'] . "',
						'" . $lineacompra . "'
						)";
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to enter Location Transfer record for'). ' '.$OrderLine->StockID;
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		// actualiza el detalle de transferencia de compra
		$sql = "UPDATE transferrequistions
			SET quantityrecd=".$CantidadT.",
			    grnbatch=".$GRN.",
			    transferpurchno=".$_POST['Trf_ID'] ."
			WHERE norequisition=".$myrow['podetailitem']."
				AND purchorderno=".$_SESSION['PO']->OrderNo;
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		//echo '<br>update: '.$sql.'<br><br>';
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		// se actualiza la requisicion con el numero de traspaso
		$sql = "INSERT INTO transferrequistions (norequisition,
					transferno,
					type,
					quantityord,
					quantityrecd,
					intolocation,
				    fromlocation
					)
			VALUES ('".$requisitionno."',
				'" .  $_POST['Trf_ID'] ."',
				'Transferencia',
				'" .$CantidadT. "',
				0,
				'" .$LocationReqIni . "',
				'" .$_SESSION['PO']->Location . "'
				
				)";
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		/*********************************************************************/
		$unidaddenegocio=ExtractTagrefXLoc($_SESSION['PO']->Location,$db);
		// aqui van los movimientos de inv para el almacen que envia
		$sql = "UPDATE locstock
			SET quantity=quantity-" . $CantidadT .
		       " WHERE stockid='" . $OrderLine->StockID  .
		       "' AND loccode='" . $_SESSION['PO']->Location . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		// funcion de avgcost por tagref
		$unitscostXtag=StockAvgUnits($OrderLine->StockID,$unidaddenegocio,$db);
		$estavgcostXtag=StockAvgcost($OrderLine->StockID,$unidaddenegocio,$db);
		$tagavgcostlocfrom = $estavgcostXtag;
		$lastcostXtag=StockLastCost($OrderLine->StockID,$unidaddenegocio,$db);
		$lastcostXlegal=StockLastCostXLegal($OrderLine->StockID,$unidaddenegocio,$db);
		$unitscostXlegal=StockAvgUnitsXLegal($OrderLine->StockID,$unidaddenegocio,$db);
		$estavgcostXlegal=StockAvgcostXLegal($OrderLine->StockID,$unidaddenegocio,$db);
		
		if ($_SESSION['TypeCostStock']==1){
			$unitscost=$unitscostXtag;
			$estavgcost=$estavgcostXtag;
			$EstimatedAvgCost=$tagavgcostlocfrom; //$EstimatedAvgCostXtag;
			$lastcost= $tagavgcostlocfrom; //$lastcostXtag;
		}else{
			$unitscost=$unitscostXlegal;
			$estavgcost=$estavgcostXlegal;
			$EstimatedAvgCost= $estavgcostXlegal; //$EstimatedAvgCostXlegal;
			$lastcost= $lastcostXlegal; //$lastcostXlegal;
			$standardcost=$EstimatedAvgCost;
		}
		$sql="Select locationname from locations where loccode='".$LocationReq."'";
		$Result = DB_query($sql, $db, _('No se pudo determinar el nombre del almacen de recepcion') );
		$LocQtyRow = DB_fetch_row($Result);
		$StockLocationToName = $LocQtyRow[0];
		//movimiento de salida		
		
		$SQL = "INSERT INTO stockmoves (
					stockid,
					type,
					transno,
					loccode,
					trandate,
					prd,
					reference,
					qty,
					newqoh,
					standardcost,
					avgcost,
					tagref
					)
			VALUES (
				'" . strtoupper($OrderLine->StockID) . "',
				16,
				" . $_POST['Trf_ID'] . ",
				'" . $_SESSION['PO']->Location . "',
				'" . date('Y-m-d') . "',
				" . $PeriodNo . ",
				'" . _('To') . ' ' . $StockLocationToName . "',
				" . -$CantidadT . ",
				" . ($CantidadT) . ",
				" . $standardcost . ",
				" . $EstimatedAvgCost. ",
				" . $unidaddenegocio. "
			)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		//movimiento de entrada
		$sql="Select locationname from locations where loccode='". $_SESSION['PO']->Location ."'";
		$Result = DB_query($sql, $db, _('No se pudo determinar el nombre del almacen de recepcion') );
		$LocQtyRow = DB_fetch_row($Result);
		$StockLocationFromName = $LocQtyRow[0];
		
		$SQL = "INSERT INTO stockmoves (
				stockid,
				type,
				transno,
				loccode,
				trandate,
				prd,
				reference,
				qty,
				newqoh,
				standardcost,
				avgcost,
				tagref
				)
			VALUES (
				'" . strtoupper($OrderLine->StockID) . "',
				16,
				" . $_POST['Trf_ID'] . ",
				'" . $LocationReq . "',
				'" . Date('Y-m-d') . "'," . $PeriodNo . ",
				'" . _('From') . ' ' . $StockLocationFromName ."',
				" .$CantidadT . ", " . ($CantidadT) . ",
				" . $standardcost . ",
				" . $EstimatedAvgCost. ",
				" . $unidaddenegocio. "
				)";

		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
		$DbgMsg =  _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		
		
		
		/*********************************************************************/
		
		
		$sql = "UPDATE locstock
			SET quantity=quantity+" . $CantidadT .
		       " WHERE stockid='" . $OrderLine->StockID  .
		       "' AND loccode='" . $LocationReq . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		// se actualiza la requisicion con el numero de traspaso
		$sql = "UPDATE requisitionorderdetails
			SET statusdetail='En Almacen Virtual',
			    quantitypurch=quantitypurch-" . $CantidadT.",
			    quantityvirtual=quantityvirtual+" . $CantidadT.",
			    quantitypurchrecd=quantitypurchrecd+" . $CantidadT."
		        WHERE podetailitem='" . $myrow['podetailitem'] . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		//echo $sql;
		//se actualiza el log de transferencia
		$sql = "INSERT INTO logrequisitionorders (norequisition,
					itemcode,
					status,
					comments,
					userid,
					datetransaction
					)
			VALUES ('".$myrow['podetailitem']."',
					'" .   $OrderLine->StockID  . "',
					'En Almacen Virtual',
					'Se Genero transferencia del Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$_SESSION['PO']->OrderNo  ."',
					'" .  $_SESSION['UserID'] . "',
					now()
				)";
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		//envio de email al usuario de origen
		$NoRequisition=$myrow['podetailitem'];
		$productoReq=$OrderLine->StockID;
		$statusReq='En Almacen Virtual';
		$comentariosReq="Se Genero transferencia del Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$_SESSION['PO']->OrderNo;
		$UsuarioReq=$_SESSION['UserID'];
		include('SendEmailChangeRequisitions.inc');
		$CantidadTpiv=$CantidadTpiv+$CantidadT;
		$lineacompra=$lineacompra+1;
		// imprime comprobantes de transferencias
		prnMsg( _('<B>LA TRANSEFERENCIA DE INVENTARIO SE HA GENERADO EXITOSAMENTE...</B>'),'success');
		echo '<p><a href="'.$rootpath.'/PDFStockLocTransfer.php?' . SID . 'TransferNo=' . $_POST['Trf_ID'] . '" target="_blank"><b>'._('Imprimir Comprobante de Transferencia # ') . $_POST['Trf_ID']. '</b></a>';

	}else{
		$sql = "UPDATE transferrequistions
			SET quantityrecd=".$CantidadT.",
			    grnbatch=".$GRN.",
			    transferpurchno=".$_POST['Trf_ID'] ."
			WHERE norequisition=".$myrow['podetailitem']."
				AND purchorderno=".$_SESSION['PO']->OrderNo;
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		//echo '<br>update: '.$sql.'<br><br>';
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		if( $LocationReq==$_SESSION['PO']->Location){
			// se actualiza la requisicion con el numero de traspaso
			$sql = "UPDATE requisitionorderdetails
				SET statusdetail='Recibida',
				    quantitypurch=quantitypurch-" . $CantidadT.",
				    quantityvirtual=quantityvirtual+" . $CantidadT.",
				    quantitypurchrecd=quantitypurchrecd+" . $CantidadT."
				WHERE podetailitem='" . $myrow['podetailitem'] . "'";
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
		include('SendEmailChangeRequisitions.inc');
		
	}
}
$Result=DB_Txn_Commit($db);
?>