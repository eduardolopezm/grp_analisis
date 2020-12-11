<?php
$Total=$_POST['Total'];
$PeriodNo= GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
//$cantidadTotal= $OrderLine->ReceiveQty;
foreach ($_SESSION['PO'.$identifier]->LineItems as $OrderLine) {
	
	$sql="SELECT transferrequistions.quantityord,requisitionorderdetails.intostocklocation,requisitionorderdetails.tagref,podetailitem,transferrequistions.quantityrecd,statustransfer,requisitionorderdetails.statusdetail
		FROM requisitionorderdetails inner join transferrequistions on  transferrequistions.norequisition=requisitionorderdetails.podetailitem
		WHERE transferrequistions.purchorderno = ".$OrderNoCompra."
		 and requisitionorderdetails.itemcode='".$OrderLine->StockID."'
		 Order by priority desc ,requisitionorderdetails.orddate asc";
		// echo '<pre>'.$sql;
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
		
		$comentariosReq="El Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$AlmacenCancel.'De La Compra:'.$OrderNoCompra.' Sera entregado el dia '.$OrderLine->ReqDelDate;
		//envio de email al usuario de origen
		$NoRequisition=$myrow['podetailitem'];
		$productoReq=$OrderLine->StockID;
		$Status=$myrow['statusdetail'];
		 //se actualiza el log de transferencia
        $sql = "INSERT INTO logrequisitionorders (norequisition,
                                itemcode,
                                status,
                                comments,
                                userid,
                                datetransaction
                                )
                VALUES ('".$NoRequisition."',
                                '".$OrderLine->StockID."',
                                '".$Status."',
                                '".$comentariosReq."',
                                '" .  $_SESSION['UserID'] . "',
                                now()
                        )";
       // echo $sql;
        $ErrMsg = _('No se puede insertar en el log');
        $DbgMsg = _('El SQL es');
        $Result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		
		//$statusReq='Recibida';
		//$comentariosReq="Se Genero transferencia del Producto ".  $OrderLine->StockID.'X la Cantidad de '. $CantidadT.' En El Almacen:'.$_SESSION['PO']->Location.'De La Compra:'.$_SESSION['PO']->OrderNo;
		$UsuarioReq=$_SESSION['UserID'];
		include('SendEmailChangeRequisitions.inc');
		
	}
}
?>