<?php
$result = DB_Txn_Begin($db);/////////
if (isset($_POST['selMovimiento'])) {
	if(isset($_POST['Oper'])){
		foreach ($_POST['selMovimiento'] as $movto){
			$u_movto = $movto;
			if($_POST['Oper'] == 'cambiaMontos'){
				if($_POST['changeuser'] <> "*" and isset($_POST['changeuser'])){
					$sql = "UPDATE workorders
							SET workorders.u_user = '".$_POST['changeuser']."'
							WHERE workorders.wo = '".$u_movto."'";
					$result = DB_query($sql, $db);
					$sql = "INSERT INTO wocontrolpanel_comentarios (wo,
																	comentario,
																	fecha,
																	idstatus,
																	userid,
																	operacion)
							VALUE (
									'".$u_movto."',
									'Actualizacion recurso',
									NOW(),
									'',
									'".$_SESSION['UserID']."',
									'Actualizacion')";
					$result = DB_query($sql, $db);
				}
				if($_POST['EstatusId_GLOBAL'] <> '*'){
					if(!isset($_POST['comentarioGlobal']) or $_POST['comentarioGlobal'] == ""){
						$_POST['comentarioGlobal'] = 'Se cambio de estatus';
					}
					$sql = "UPDATE workorders
							SET workorders.idstatus = '".$_POST['EstatusId_GLOBAL']."'
							WHERE workorders.wo = '".$u_movto."'";
					$result = DB_query($sql, $db);
					$sql = "INSERT INTO wocontrolpanel_comentarios (wo, 
																	comentario, 
																	fecha, 
																	idstatus, 
																	userid, 
																	operacion)
							VALUE (
									'".$u_movto."',
									'".$_POST['comentarioGlobal']."',
									NOW(),
									'".$_POST['EstatusId_GLOBAL']."',
									'".$_SESSION['UserID']."',
									'cambioestatus')";
					$result = DB_query($sql, $db);
				}elseif(isset($_POST['wodescription_'.$u_movto])){
					$sql = "UPDATE workorders
							SET workorders.wodescription = '".$_POST['wodescription_'.$u_movto]."',
								workorders.confirmado = 0
							WHERE workorders.wo = '".$u_movto."'";
					$result = DB_query($sql, $db);
					$sql = "INSERT INTO wocontrolpanel_comentarios (wo,
																	comentario,
																	fecha,
																	idstatus,
																	userid,
																	operacion)
							VALUE (
									'".$u_movto."',
									'Actualizacion descripcion',
									NOW(),
									'',
									'".$_SESSION['UserID']."',
									'Actualizacion')";
					$result = DB_query($sql, $db);
				}
				
			}elseif($_POST['Oper'] == "pendMovimiento"){
				$sql = "UPDATE workorders
							SET workorders.confirmado = 1
							WHERE workorders.wo = '".$u_movto."'";
				$result = DB_query($sql, $db);
			}elseif($_POST['Oper'] == "confMovimiento"){
				$sql = "UPDATE workorders
							SET workorders.confirmado = 0
							WHERE workorders.wo = '".$u_movto."'";
				$result = DB_query($sql, $db);
			}
		}////
	}
}else{
	
	if(isset($_GET['Oper']) and $_GET['Oper'] == 'confMovimiento'){
		
		if($_GET['confirmado'] == 0){
			$sql = "UPDATE workorders
							SET workorders.confirmado = 0
							WHERE workorders.wo = '".$_GET['wo']."'";
			$result = DB_query($sql, $db);
		}elseif($_GET['confirmado'] == 1){
			$sql = "UPDATE workorders
							SET workorders.confirmado = 1
							WHERE workorders.wo = '".$_GET['wo']."'";
			$result = DB_query($sql, $db);
		}//
	}elseif(isset($_GET['Oper']) and $_GET['Oper'] == 'cancelar' and isset($_POST['si']) and $_POST['si'] == "SI"){
		$flagcancelar = ValidaCancelarWO($db, $_GET['wo']);
		
		if($flagcancelar == 0){
			$flaginserto = CancelarWo($db, $_GET['wo']);
		}else{
			if($flagcancelar == 3 and Havepermission($_SESSION['UserID'], 1182, $db)==1) {
				$flaginserto = CancelarWo($db, $_GET['wo']);
			}elseif ($flagcancelar == 3 and Havepermission($_SESSION['UserID'], 1182, $db)==0){
				prnMsg("La orden de trabajo ".$_GET['wo']." Ha generado movimientos de inventario, no tiene el permiso para reversar la O.T","error");
			}elseif ($flagcancelar == 1){
				prnMsg("La orden de trabajo ".$_GET['wo']." Ya tiene ordenes de compra autorizadas pendintes a recibir, favor de verificarlo","error");
				
				
				$sql = "SELECT purchorders.orderno,
						FROM purchorders
						WHERE purchorders.wo = '".$_GET['wo']."'
							AND purchorders.status = 'Authorised'
							AND purchorderdetails.quantityrecd < purchorderdetails.quantityord";
				$result = DB_query($sql, $db);
				if(DB_num_rows($result) > 0){
					while($myrow = DB_fetch_array($result)){
						$Ordenes = $Ordenes = 'O.C. '.$myrow['orderno'].' ';
					}
					prnMsg("Son las siguientes ordernes de comora".$Ordenes,"error");
				}
			}
		}
		if($flaginserto == 1){
			prnMsg("Se cancela la orden de trabajo de manera exitosa ".$_GET['wo'],"info");
		}
	}
}
$result = DB_Txn_Commit($db)
?>