<?php

/**
 * FUNCION PARA NOTIFICAR EL CAMBIO DE ESTADO DE UN PEDIDO
 *
 * @param String $status
 * @param int $order
 * @param Resource $db
 */
function emailNotifier($status, $order, & $db) {

	$message 	= "";
	$subject 	= "";
	$userName 	= "";



	$enviocompras=0;
	//echo '<br>prueba:'.$estado;
	if($status == 'Pedido Cerrado' or $status=='Pedido Abierto') {

		$sql 	= "SELECT distinct purchorderdetails.orderno FROM purchorderdetails
		inner join purchorders on purchorders.orderno=purchorderdetails.orderno
		WHERE purchorders.requisitionno = '$order'
		and status <>'Cancelled'";
		//echo $sql;
		$result = DB_query($sql, $db);

		if (DB_num_rows($result)>=1) {
		$purchOrderNo="";
				while($row = DB_fetch_array($result)){
				$puchOrderNo .= $row['orderno'].",";
				}
				$purchOrderNo = substr($puchOrderNo,0,strlen($purchOrderNo)-1);
				$message .= "El pedido No. $order  ha cambiado a $status y tiene asociada las ordenes de compra . $puchOrderNo";
				$enviocompras=1;
		} else {
			$message .= "El pedido No. $order, que no tiene asociada ninguna orden de compra";
				
		}
			
	} else {
		if($status == 'Cancelar Pedido') {
			$status = 'Cancelado';
		} else if($status == 'Facturar') {
			$status = 'Facturado';
		} else if($status == 'Remision') {
			$status = 'Remisionado';
		}
		$message .= "El estado del Pedido No. $order ha cambiado a $status";
	}

	$subject = strtoupper($_SESSION['DatabaseName']) . " - Notificacion de ".$status." No. " . $order;


	if($enviocompras==1){
		$to = $_SESSION['FactoryManagerEmail'];
	}else{
		$to = 'a@a.com';
	}
	$userId		= $_SESSION['UserID'];
	$rsUser 	= DB_query("SELECT realname, email FROM www_users WHERE userid = '$userId'", $db);

	if($rowUser = DB_fetch_array($rsUser)) {
		$userName = ucwords(strtolower($rowUser['realname']));
		$userEmail = $rowUser['email'];
		if(empty($userEmail) == FALSE) {
			$to .= "," . $userEmail;
		}
	}

	$messagex = "El usuario $userName ha realizado la siguiente operacion: <br />";
	$messagey=$messagex.' '.$message;
	
	require_once("/var/www/html/" . dirname($_SERVER['PHP_SELF']) . "/includes/mail.php");
	//echo $messagey;
	$mail = new Mail();

	$mail->setTo($to);
	$mail->setFrom("soporte@tecnoaplicada.com");
	$mail->setSender("Soporte");
	$mail->setSubject($subject);
	$mail->setHtml($messagey);
	if($_SESSION['UserID'] != 'pruebas') {
		$mail->send();
	}
}

?>