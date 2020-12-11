<?php

//$Result = DB_Txn_Begin($db);
$Total=$_POST['Total'];
$cantidadTotal= $TrfLine->ReceiveQty;
$sql="SELECT transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem
	FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
	WHERE transferrequistions.transferno = ".$LnItm->Referencevirtual."
	 and requisitionorderdetails.podetailitem='".$LnItm->Podetailitem."'
	 and requisitionorderdetails.itemcode='".$LnItm->StockID."'
	 Order by priority desc ,requisitionorderdetails.orddate asc";
	 echo '<bR>'.$sql;
$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de requisicion de compra no se pudo obtener');
$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de compra');
$ResultCompra = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
while ($myrow=DB_fetch_array($ResultCompra)){
	
	$_POST['comments']="Traspaso Automatico de la Compra:".$_SESSION['PO']->OrderNo.' De La Requisision:'.$myrow['podetailitem'];
	$CantidadT=$myrow['quantityord'];
	$LocationReq=$myrow['intostocklocation'];
	$Tagref = $myrow['tagref'];
	$requisitionno=$myrow['podetailitem'];
	
	if($CantidadT>0 and strlen($CantidadT)>0){
		// se actualiza la requisicion con el numero de traspaso
		$sql = "INSERT INTO transferrequistions (norequisition,
					transferno,
					type,
					quantityord,
					quantityrecd
					)
			VALUES ('".$LnItm->Podetailitem."',
				'" .  $_POST['Trf_ID'] ."',
				'Transferencia',
				'" .$CantidadT. "',
				0
				)";
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		$sql = "UPDATE locstock
			SET ontransit=ontransit+" . $CantidadT .
		       " WHERE stockid='" . $LnItm->StockID  .
		       "' AND loccode='" . $LocDestination . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		// se actualiza la requisicion con el numero de traspaso
		$sql = "UPDATE requisitionorderdetails
			SET statusdetail='En Transferencia',
			    quantityvirtual=quantityvirtual-" . $CantidadT.",
			    quantityontransit=quantityontransit+" . $CantidadT."
		        WHERE podetailitem='" . $LnItm->Podetailitem . "'
				and itemcode='" . $LnItm->StockID . "'";
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		//se actualiza el log de transferencia
		$sql = "INSERT INTO logrequisitionorders (norequisition,
					itemcode,
					status,
					comments,
					userid,
					datetransaction
					)
			VALUES ('".$LnItm->Podetailitem."',
					'" .   $LnItm->StockID  . "',
					'En Transferencia',
					'Se Genero transferencia del Producto ".  $LnItm->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$LocDestination ."',
					'" .  $_SESSION['UserID'] . "',
					now()
				)";
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	}
}
//$Result=DB_Txn_Commit($db);

?>