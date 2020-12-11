<?php
$Total=$_POST['Total'];
$PeriodNo= GetPeriod (Date('Y-m-d'), $db);
$cantidadTotal= $OrderLine->ReceiveQty;
$sql="SELECT transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem
	FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
	WHERE transferrequistions.purchorderno = ".$OrderNoReq."
	 and requisitionorderdetails.itemcode='".$StockID."'
	  and transferrequistions.grnbatch='".$GRNReq."'
	  AND statustransfer<>'Cancel'
	 Order by priority desc ,requisitionorderdetails.orddate asc";
$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de requisicion de compra no se pudo obtener');
$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de compra');
$ResultCompra = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
while ($myrow=DB_fetch_array($ResultCompra)){
	$_POST['comments']="Traspaso Automatico de la Compra:".$_SESSION['PO']->OrderNo.' De La Requisision:'.$myrow['podetailitem'];
	$CantidadT=$myrow['quantityord'];
	$LocationReq=$myrow['intostocklocation'];
	$Tagref = $myrow['tagref'];
	$requisitionno=$myrow['podetailitem'];
	if(($cantidadTotal-$CantidadT)<0 and $cantidadTotal>0 ){
		$CantidadT=$cantidadTotal;
	}else{
		$cantidadTotal=$cantidadTotal-$CantidadT;
	}
	
	// se actualiza la requisicion con el numero de traspaso
	$sql = "UPDATE requisitionorderdetails
		SET quantitypurch=quantitypurch-" . $CantidadT.",
		   quantitycancel=quantitycancel+" . $CantidadT.",
		    quantitypurchrecd=quantitypurchrecd-" . $CantidadT."
		WHERE podetailitem='" . $myrow['podetailitem'] . "'";
	$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE transferrequistions
			SET quantityrecd=quantityrecd-".$CantidadT.",
			statustransfer='Cancel'
			WHERE norequisition=".$myrow['podetailitem']."
				AND purchorderno=".$OrderNoReq;
		$ErrMsg = _('No se puede insertar en el log');
		$DbgMsg = _('El SQL es');
		//echo '<br>update: '.$sql.'<br><br>';
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
				'" .   $StockID . "',
				'Recepcion Cancelada',
				'Se Cancelo Producto ".  $StockID.'X la Cantidad de '. $CantidadT.' De La Compra:'.$OrderNoReq  ."',
				'" .  $_SESSION['UserID'] . "',
				now()
			)";
	$ErrMsg = _('No se puede insertar en el log');
	$DbgMsg = _('El SQL es');
	$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//envio de email al usuario de origen
	$NoRequisition=$myrow['podetailitem'];
	$productoReq=$OrderLine->StockID;
	$statusReq='Cancelada';
	$comentariosReq="Se Cancelo Producto ".  $StockID.'X la Cantidad de '. $CantidadT.' De La Compra:'.$OrderNoReq;
	$UsuarioReq=$_SESSION['UserID'];
	include('SendEmailChangeRequisitions.inc');
	
}
?>