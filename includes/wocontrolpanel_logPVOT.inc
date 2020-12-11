<?php


$title = _('Elimina Orden  de Trabajo');


$sql = "INSERT INTO LogOTPV (LogOTPV.orderno, LogOTPV.orderlineno, LogOTPV.wo, LogOTPV.userid, LogOTPV.comentarios, LogOTPV.fechapublicacion, 
			LogOTPV.tipomovimiento, LogOTPV.fechaeliminado, LogOTPV.stockid)
Values ('".$orderno."', '".$lineno."', '".$wo."', '".$_SESSION['UserID']."', '".$_POST['Comments']."', '".$fecha."', 'Elimino Orden de Trabajo', Now(),
		'".$stockid."')";
$resultinsert = DB_query($sql, $db);


?>
