<?php
	$Result = DB_Txn_Begin($db);
	$arraypolizas = $_POST['modifica'];
	foreach ($arraypolizas as $poliza){
		$index = $poliza;
		$SQL = "INSERT INTO logeliminapoliza (type, typeno, chequeno, trandate, periodno, account, narrative, amount, tag ,userid, origtrandate, comentarios, useridelimino)
				SELECT gltrans.type, gltrans.typeno, gltrans.chequeno, gltrans.trandate, gltrans.periodno, gltrans.account, gltrans.narrative, gltrans.amount, gltrans.tag, gltrans.userid, '2014-04-03', 'Se elimino la poliza', '".$_SESSION['UserID']."'
				FROM gltrans
				WHERE gltrans.counterindex = '".$index."'";
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No fue posible eliminar la poliza');
		$DbgMsg = _('El siguiente SQL se utilizo para eliminar la poliza');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		
		$SQL = "DELETE
				FROM gltrans
				WHERE gltrans.counterindex = '".$index."'";
		$ErrMsg = _('ERROR CRITICO') . ' ' . _('ANOTE EL ERROR') . ': ' . _('No fue posible eliminar la poliza');
		$DbgMsg = _('El siguiente SQL se utilizo para eliminar la poliza');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		if($true = true){
			prnMsg(_("Se elimino la poliza de manera correcta"),"info");
		}
	}
	$Result = DB_Txn_Commit($db);
?>