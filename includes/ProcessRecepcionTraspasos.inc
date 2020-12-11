<?php

$Result = DB_Txn_Begin($db);
//$Trf_ID = GetNextTransNo(16,$db);
$_POST['Trf_ID'] = $Trf_ID;
$Total=$_POST['Total'];
$CantidadT= $TrfLine->Quantity;
$sql="SELECT distinct transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem
	FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
	WHERE transferrequistions.transferno = ".$_SESSION['Transfer']->TrfID."
	 and requisitionorderdetails.itemcode='".$TrfLine->StockID."'
	 and requisitionorderdetails.podetailitem='".$TrfLine->Podetailitem."'
	 and transferrequistions.transferlineno='" . $TrfLine->transferline . "'
	 Order by priority desc ,requisitionorderdetails.orddate asc";
	  //echo $sql . '<br />' . $CantidadT;
	 // exit;
$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de requisicion de compra no se pudo obtener');
$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de compra');
$ResultCompra = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
while ($myrow=DB_fetch_array($ResultCompra)){
	//$CantidadT=$myrow['quantityord'];
	
	/*$SQL="select * from locations where tagref='".$Tagref."' and temploc=1";
	$Resultalmacen = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	$CompraAlmacenRow = DB_fetch_row($Resultalmacen);
	$LocationReq = $CompraAlmacenRow[0];
	*/
	$LocationReq=$myrow['intostocklocation'];
	
	if($CantidadT>0 and strlen($CantidadT)>0){
		
		// se actualiza la requisicion con el numero de traspaso
		$sql = "UPDATE requisitionorderdetails
			SET quantityrecd=quantityrecd+'" . $CantidadT."',
			statusdetail='Recibida',
			quantityontransit=quantityontransit-'" . $CantidadT."'".
		       " WHERE podetailitem='" . $myrow['podetailitem'] .
		       "' AND itemcode='" . $TrfLine->StockID  . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		//Actualizar el status de la orden de requisicion
		/*$sql = "Select sum(quantityord)-sum(quantityrecd) as status
			from requisitionorderdetails
		        WHERE orderno='" . $myrow['orden'] . "'";*/
		
		$sql = "SELECT (quantityord-quantityrecd) AS status FROM requisitionorderdetails
				WHERE podetailitem='" . $myrow['podetailitem'] .
				"' AND itemcode='" . $TrfLine->StockID  . "'";
		//echo $sql;
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$LocQtyRow = DB_fetch_row($Result);
                $QtyOnHandPrior = $LocQtyRow[0];
		if($QtyOnHandPrior==0){
			$status='Recibida';
		}else{
			$sql = "SELECT status FROM logrequisitionorders
					WHERE norequisition='" . $myrow['podetailitem'] . "' AND itemcode='" . $TrfLine->StockID  . "'  
					ORDER BY idlogrequisition";
			//echo $sql;
			$rs = DB_query($sql, $db);
			$index = DB_num_rows($rs) - 1;
			if($index >= 0) {
				DB_data_seek($rs, $index);
				$row = DB_fetch_array($rs);
				$status = $row['status'];
			} else {
				$status = 'Transferida';
			}
		}
		
		$sql = "UPDATE requisitionorderdetails
			SET statusdetail='".$status."'
		        WHERE podetailitem='" . $myrow['podetailitem'] .
		       "' AND itemcode='" . $TrfLine->StockID  . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		//se actualiza el log de transferencia
		$sql = "INSERT INTO logrequisitionorders (norequisition,
					itemcode,
					status,
					comments,
					userid,
					datetransaction
					)
			VALUES ('".$myrow['podetailitem']."',
					'" .   $TrfLine->StockID  . "',
					'".$status."',
					'Se Recibio transferencia del Producto ".  $TrfLine->StockID.'X la Cantidad de '. $CantidadT."',
					'" .  $_SESSION['UserID'] . "',
					now()
				)";
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	}
}

$Result=DB_Txn_Commit($db);
//prnMsg( _('<B>LA TRANSEFERENCIA DE RECEPCION DE INVENTARIO SE HA GENERADO EXITOSAMENTE...</B>'),'success');

?>