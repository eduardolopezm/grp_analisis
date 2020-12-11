<?php
$Total=$_POST['Total'];
$PeriodNo= GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
//$cantidadTotal= $OrderLine->ReceiveQty;
foreach ($_SESSION['PO'.$identifier]->LineItems as $OrderLine) {
	
	$sql="SELECT transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem,transferrequistions.quantityrecd,statustransfer
		FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
		WHERE transferrequistions.purchorderno = ".$OrderNoCompra."
		 and requisitionorderdetails.itemcode='".$OrderLine->StockID."'
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
		if ($opercompra=='Cancel'){
			// se actualiza la requisicion con el numero de traspaso
			$sql = "UPDATE requisitionorderdetails
				SET statusdetail='Solicitud',
				    quantitypurch=quantitypurch-" . $CantidadT.",
				   quantitycancel=quantitycancel+" . $CantidadT."
				WHERE podetailitem='" . $myrow['podetailitem'] . "'";
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
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
						'" .   $OrderLine->StockID  . "',
						'Compra Cancelada',
						'Se Cancelo Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Compra:'.$OrderNoCompra  ."',
						'" .  $_SESSION['UserID'] . "',
						now()
					)";
			$ErrMsg = _('No se puede insertar en el log');
			$DbgMsg = _('El SQL es');
			$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$comentariosReq="Se cancelo el Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Compra:'.$OrderNoCompra;
		}else{
			//echo "entra";
			if($myrow['statustransfer']=='Cancel'){
				$sql = "UPDATE requisitionorderdetails
					SET statusdetail='En Compra',
					    quantitypurch=quantitypurch+" . $CantidadT."
				WHERE podetailitem='" . $myrow['podetailitem'] . "'";
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
				// se actualiza la transferencia para que quede en ceros
				$sql = "UPDATE transferrequistions
					SET quantityrecd=quantityrecd-".$CantidadT.",
						statustransfer='En Compra'
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
							'" .   $OrderLine->StockID  . "',
							'Compra Autorizada',
							'Se Cancelo Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Compra:'.$OrderNoCompra  ."',
							'" .  $_SESSION['UserID'] . "',
							now()
						)";
				$ErrMsg = _('No se puede insertar en el log');
				$DbgMsg = _('El SQL es');
				$Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			}
			
			$comentariosReq="Se autorizo el Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Compra:'.$OrderNoCompra;
		}
		//envio de email al usuario de origen
		$NoRequisition=$myrow['podetailitem'];
		$productoReq=$OrderLine->StockID;
		//$statusReq='Recibida';
		//$comentariosReq="Se Genero transferencia del Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$_SESSION['PO']->OrderNo;
		$UsuarioReq=$_SESSION['UserID'];
		include('SendEmailChangeRequisitions.inc');
		
	}
}
?>