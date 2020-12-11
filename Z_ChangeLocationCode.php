<?php
/*
 FECHA DE MODIFICACION: 17-MAR-2011
 CAMBIOS: 
	1. SE GENERO EL ARCHIVO
 FIN DE CAMBIOS
*/
include ('includes/session.inc');
$title = _('PAGINA DE UTILERIA PARA CAMBIAR CODIGO DE ALMACEN EN TODAS LAS TABLAS');
include('includes/header.inc');
$funcion=255;//CAMBIAR
include('includes/SecurityFunctions.inc');

if (isset($_POST['ProcessStockidChange'])){

	/*Primero checa que el codigo del almacen Exista */
	$result=DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['OldStockidNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg ('<br><br>' . _('El codigo del almacen ') . ': ' . $_POST['OldStockidNo'] . ' ' . _('no existe actualmente en la base de datos del sistema'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$LocationOrigen = $myrow[0];
	}


	if ($_POST['NewStockidNo']==''){
		prnMsg(_('El codigo de almacen destino debe de ser capturado'),'error');
		include('includes/footer.inc');
		exit;
	}
	
	/*Ahora checa que el nuevo codigo de almacen exista */
	$result=DB_query("SELECT loccode FROM locations WHERE loccode='" . $_POST['NewStockidNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('El codigo de almacen de reemplazo') .': ' . $_POST['NewStockidNo'] . ' ' . _('no existe actualmente en la base de datos del sistema') . ' - ' . _('este codigo debe de existir en el sistema antes de migrar movimientos de otro proveedor...'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$LocationDestino = $myrow[0];
	}
	
	$result = DB_Txn_Begin($db);

	//Actualiza el stockid nuevo 
	$sql = "UPDATE bom SET loccode='" . $_POST['NewStockidNo'] . "' WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update bom transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE contractbom SET loccode='" . $_POST['NewStockidNo'] . "' WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update contractbom transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE custbranch SET defaultlocation='" . $_POST['NewStockidNo'] . "' WHERE defaultlocation='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update custbranch transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE freightcosts SET locationfrom='" . $_POST['NewStockidNo'] . "' WHERE locationfrom='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update freightcosts transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE loctransfers SET recloc='" . $_POST['NewStockidNo'] . "' WHERE recloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE loctransfers SET shiploc='" . $_POST['NewStockidNo'] . "' WHERE shiploc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update loctransfers transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE notesorderdetails SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update notesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE notesorders SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update notesorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE purchorders SET intostocklocation='" . $_POST['NewStockidNo'] . "' WHERE intostocklocation='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update purchorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE recurringsalesorders SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update recurringsalesorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE salesorderdetails SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update salesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE salesorders SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update salesorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE shipments SET location='" . $_POST['NewStockidNo'] . "' WHERE location='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update shipments transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE stockmoves SET loccode='" . $_POST['NewStockidNo'] . "' WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE stockserialimages SET loccode='" . $_POST['NewStockidNo'] . "' WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockserialimages transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE stockserialitems SET loccode='" . $_POST['NewStockidNo'] . "' WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update stockserialitems transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE suppnotesorderdetails SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update suppnotesorderdetails transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE suppnotesorders SET fromstkloc='" . $_POST['NewStockidNo'] . "' WHERE fromstkloc='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update suppnotesorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	
	$sql = "UPDATE workcentres SET location='" . $_POST['NewStockidNo'] . "' WHERE location='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update workcentres transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');
	
	$sql = "UPDATE workorders SET loccode='" . $_POST['NewStockidNo'] . "' WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to update workorders transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros  ...'),'info');

	//Actualiza las nuevas uniades de lockstock
	$SqlOld= "SELECT stockid FROM stockcheckfreeze
		WHERE loccode='".$_POST['OldStockidNo']."'";
	$Resultold = DB_query($SqlOld,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Resultold)>0){
		//Para cada producto obtiene  la cantidad y costo k hay en la tabla locstock de acuerdo al loccode correspondiente
		while ($myrow2 = DB_fetch_array($Resultold)){
			//Consulta que trae el oldcosto y oldcantidad de cada producto existente en la tabla locstock
			$sql_old ="SELECT sum(qoh) as qtyold
				   FROM  stockcheckfreeze 
				   WHERE stockid='".$myrow2['stockid']."'
					 AND loccode='".$_POST['OldStockidNo']."'";
			$ResultOlddos = DB_query($sql_old,$db,$ErrMsg,$DbgMsg,true);
			$myrowold = DB_fetch_array($ResultOlddos);
			//Consulta que trae el newcosto y newcantidad de cada producto existente en la tabla locstock
			$sql_new ="SELECT sum(qoh) as qtynew
				   FROM  stockcheckfreeze 
				   WHERE stockid='".$myrow2['stockid']."'
				      AND loccode='".$_POST['NewStockidNo']."'";
			$ResultNew = DB_query($sql_new,$db,$ErrMsg,$DbgMsg,true);
			$myrownew = DB_fetch_array($ResultNew);
			//Actualiza el nuevo costo promedio para cada producto perteneciente al newtag
			$sql = "UPDATE stockcheckfreeze
				SET qoh=('".$myrowold['qtyold']."' + '".$myrownew['qtynew']."')
				WHERE stockid='" .$myrow2['stockid'] . "'
				AND loccode='".$_POST['NewStockidNo']."'";
			$ErrMsg = _('The SQL to update stockcostsxlegal transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		}
	}
	prnMsg(_('Cambiando registros de stockcheckfreeze ...'),'info');
	
	//Eliminación de stockid anterior de la Tabla de locstock//
	$sql = "DELETE FROM stockcheckfreeze WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to DELETE old stockid record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Borrando registros de stockcheckfreeze anterior...'),'info');
	
	//Actualiza las nuevas uniades de lockstock
	$SqlOld= "SELECT stockid FROM stockcounts
		WHERE loccode='".$_POST['OldStockidNo']."'";
	$Resultold = DB_query($SqlOld,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Resultold)>0){
		//Para cada producto obtiene  la cantidad y costo k hay en la tabla locstock de acuerdo al loccode correspondiente
		while ($myrow2 = DB_fetch_array($Resultold)){
			//Consulta que trae el oldcosto y oldcantidad de cada producto existente en la tabla locstock
			$sql_old ="SELECT sum(qtycounted) as qtyold
				   FROM  stockcounts 
				   WHERE stockid='".$myrow2['stockid']."'
					 AND loccode='".$_POST['OldStockidNo']."'";
			$ResultOld = DB_query($sql_old,$db,$ErrMsg,$DbgMsg,true);
			$myrowold = DB_fetch_array($ResultOld);
			//Consulta que trae el newcosto y newcantidad de cada producto existente en la tabla locstock
			
			$sql_new ="SELECT sum(qtycounted) as qtynew
				   FROM  stockcounts 
				   WHERE stockid='".$myrow2['stockid']."'
				      AND loccode='".$_POST['NewStockidNo']."'";
			$ResultNew = DB_query($sql_new,$db,$ErrMsg,$DbgMsg,true);
			$myrownew = DB_fetch_array($ResultNew);
			//Actualiza el nuevo costo promedio para cada producto perteneciente al newtag
			$sql = "UPDATE stockcounts
				SET qtycounted=('".$myrowold['qtyold']."' + '".$myrownew['qtynew']."')
				WHERE stockid='" .$myrow2['stockid'] . "'
				AND loccode='".$_POST['NewStockidNo']."'";
			$ErrMsg = _('The SQL to update stockcostsxlegal transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			//echo "<br><br>update".$sql;
		}
	}
	prnMsg(_('Cambiando registros de stockcounts ...'),'info');
	
	//Eliminación de stockid anterior de la Tabla de locstock//
	$sql = "DELETE FROM stockcounts WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to DELETE old stockid record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Borrando registros de stockcounts anterior...'),'info');
	
	//Actualiza las nuevas uniades de lockstock
	$SqlOld= "SELECT stockid FROM locstock
		WHERE loccode='".$_POST['OldStockidNo']."'";
	$Resultold = DB_query($SqlOld,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Resultold)>0){
		//Para cada producto obtiene  la cantidad y costo k hay en la tabla locstock de acuerdo al loccode correspondiente
		while ($myrow2 = DB_fetch_array($Resultold)){
			//Consulta que trae el oldcosto y oldcantidad de cada producto existente en la tabla locstock
			$sql_old ="SELECT sum(locstock.quantity) as qtyold,sum(ontransit) as oldtransit,
					sum(reorderlevel) as oldreorder
				   FROM  locstock 
				   WHERE locstock.stockid='".$myrow2['stockid']."'
					 AND locstock.loccode='".$_POST['OldStockidNo']."'";
			$ResultOlduno = DB_query($sql_old,$db,$ErrMsg,$DbgMsg,true);
			//echo "<br><br>qtyold".$sql_old;	
			$myrowold = DB_fetch_array($ResultOlduno);
			//Consulta que trae el newcosto y newcantidad de cada producto existente en la tabla locstock
			
			$sql_new ="SELECT sum(locstock.quantity) as qtynew,sum(ontransit) as newtransit,
					sum(reorderlevel) as newreorder
				   FROM  locstock 
				   WHERE locstock.stockid='".$myrow2['stockid']."'
				      AND locstock.loccode='".$_POST['NewStockidNo']."'";
			$ResultNew = DB_query($sql_new,$db,$ErrMsg,$DbgMsg,true);
			$myrownew = DB_fetch_array($ResultNew);
			//Actualiza el nuevo costo promedio para cada producto perteneciente al newtag
			$sql = "UPDATE locstock
				SET quantity=('".$myrowold['qtyold']."' + '".$myrownew['qtynew']."'),
				ontransit=('".$myrowold['oldtransit']."' + '".$myrownew['newtransit']."'),
				reorderlevel=('".$myrowold['oldreorder']."' + '".$myrownew['newreorder']."')
				WHERE stockid='" .$myrow2['stockid'] . "'
				AND loccode='".$_POST['NewStockidNo']."'";
			$ErrMsg = _('The SQL to update locstock transaction records failed');
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			//echo "<br><br>update".$sql;
		}
	}
	prnMsg(_('Cambiando registros de lockstock ...'),'info');
	
	$sql = "DELETE FROM locstock WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to DELETE old lockstock record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Borrando registros de lockstock anterior...'),'info');
	
	
	//Actualiza las nuevas uniades de lockstock
	$SqlOld= "SELECT userid FROM sec_loccxusser
		WHERE loccode='".$_POST['OldStockidNo']."'";
	$Resultold = DB_query($SqlOld,$db,$ErrMsg,$DbgMsg,true);
	if (DB_num_rows($Resultold)>0){
		//Para cada producto obtiene  la cantidad y costo k hay en la tabla locstock de acuerdo al loccode correspondiente
		while ($myrow2 = DB_fetch_array($Resultold)){
			//Consulta que trae el oldcosto y oldcantidad de cada producto existente en la tabla locstock
			$sql_old ="SELECT *
				   FROM  sec_loccxusser 
				   WHERE loccode='".$_POST['NewStockidNo']."'
					AND userid='".$myrow2['userid']."'";
			$ResultNew = DB_query($sql_old,$db,$ErrMsg,$DbgMsg,true);
			if (DB_num_rows($ResultNew)==0){
				$sql="insert into sec_loccxusser (userid,loccode)";
				$sql=$sql." values('".$myrow2['userid']."','".$_POST['NewStockidNo']."')";
				$ErrMsg = _('Las operaciones sobre las sucursales para este registro no han sido posibles por que ');
				$DbgMsg = _('El SQL utilizado es:');
				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			}
		}
	}
	$sql = "DELETE FROM sec_loccxusser WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Borrando almacenes de tabla de seguridad...'),'info');
	
	$sql = "DELETE FROM locations WHERE loccode='" . $_POST['OldStockidNo'] . "'";
	$ErrMsg = _('The SQL to DELETE old loccode record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Borrando clave de almacen anterior...'),'info');
		
	$result = DB_Txn_Commit($db);
}

echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Codigo de Almacen Origen') . ":</td>
		<td><input type=Text name='OldStockidNo' size=20 maxlength=20></td>
	</tr>";
echo '<tr><td> ' . _('Codigo de Almacen Destino') . ":</td>
	<td><input type=Text name='NewStockidNo' size=20 maxlength=20></td>
	</tr>";
	

echo "<tr><td colspan='2'><input type=submit name='ProcessStockidChange' VALUE='" . _('Procesar Cambio...') . "'>";
echo "</td></tr></table>";
echo '</form>';

include('includes/footer.inc');

?>