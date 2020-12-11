<?php
$Total=$_POST['Total'];
$PeriodNo= GetPeriod (Date('Y-m-d'), $db);
//$cantidadTotal= $OrderLine->ReceiveQty;
//foreach ($_SESSION['PO'.$identifier]->LineItems as $OrderLine) {
	
	$sql="SELECT transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem,transferrequistions.quantityrecd,statustransfer
		FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
		WHERE transferrequistions.transferno = ".$transferRefNo."
		 and requisitionorderdetails.itemcode='".$StockID."'
		  AND statustransfer<>'Cancel'
		 Order by priority desc ,requisitionorderdetails.orddate asc";
	echo $sql;
	$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('El encabezado de la  ordern de requisicion de compra no se pudo obtener');
	$DbgMsg = _('El siguiente SQL se utilizo para actualizar la orden de compra');
	$ResultCompra = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	while ($myrow=DB_fetch_array($ResultCompra)){
		$_POST['comments']="Traspaso Automatico de la Compra:".$transferRefNo.' De La Requisision:'.$myrow['podetailitem'];
		$CantidadT=$myrow['quantityord'];
		$LocationReq=$myrow['intostocklocation'];
		$Tagref = $myrow['tagref'];
		$requisitionno=$myrow['podetailitem'];
		if(($cantidadTotal-$CantidadT)<0 and $cantidadTotal>0 ){
			$CantidadT=$cantidadTotal;
			
		}else{
			$cantidadTotal=$cantidadTotal-$CantidadT;
		}
		if ($opercompra=='Cancel'){
			// se actualiza la requisicion con el numero de traspaso
			$sql = "UPDATE requisitionorderdetails
				SET /*statusdetail='Solicitud',*/
				    quantityvirtual=quantityvirtual-" . $CantidadT.",
				   quantitycancel=quantitycancel+" . $CantidadT."
				WHERE podetailitem='" . $myrow['podetailitem'] . "'";
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			echo '<br><br><br>'.$sql;
			// se actualiza la transferencia para que quede en ceros
			$sql = "UPDATE transferrequistions
				SET quantityrecd=".$CantidadT.",
				statustransfer='Cancel'
				WHERE norequisition=".$myrow['podetailitem']."
					AND purchorderno=".$OrderNoCompra;
			$ErrMsg = _('No se puede insertar en el log');
			$DbgMsg = _('El SQL es');
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
						'" .   $StockID . "',
						'Compra Cancelada',
						'Se Cancelo Producto ".  $StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Transferencia:'.$OrderNoCompra  ."',
						'" .  $_SESSION['UserID'] . "',
						now()
					)";
			$ErrMsg = _('No se puede insertar en el log');
			$DbgMsg = _('El SQL es');
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$comentariosReq="Se cancelo el Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Transferencia:'.$OrderNoCompra;
		}
		//envio de email al usuario de origen
		$NoRequisition=$myrow['podetailitem'];
		$productoReq=$OrderLine->StockID;
		//$statusReq='Recibida';
		//$comentariosReq="Se Genero transferencia del Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$_SESSION['PO']->OrderNo;
		$UsuarioReq=$_SESSION['UserID'];
		include('SendEmailChangeRequisitions.inc');
		
	}
//}
?>