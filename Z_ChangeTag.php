<?php
/*
FECHA CREACION: JUEVES 11 DE NOVIEMBRE DE 2012
DESCRIPCION: UTILERIA QUE PERMITE PASAR MOVIMIENTOS DE UNA UNIDAD DE NEGOCIO A OTRA

INSERT INTO `sec_functions` VALUES ('631', '8', 'Unificacion de Unidade de Negocio', '1', 'Z_ChangeTag.php', '1', 'Unificacion de U. Neg', '14', 'Unificacion de Unidades de Negocio', '');

*/

$funcion=631;
include ('includes/session.inc');
$title = $funcion . " - " . _('UNIFICAR UNIDADES DE NEGOCIO');
include('includes/header.inc');


include('includes/SecurityFunctions.inc');

/****** SELECCION DE cuenta ORIGEN *****/
	
echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>"; 
	echo "<table border='0'>";
		echo "<tr>";
			echo "<td>";
				echo "&nbsp;&nbsp;&nbsp;<b></b>"._('Unidad Origen: ') . "&nbsp;&nbsp;";
				echo "<select name='OldTag' style='font-size:9pt;align='center'>";
					echo "<option selected value=''>Seleccione</option>";
					$SQL=" SELECT tags.*
						FROM tags , sec_unegsxuser
						WHERE  tags.tagref=sec_unegsxuser.tagref
						AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
						ORDER BY tags.tagdescription";
					$resultOld = DB_query($SQL,$db,'','');
					while ($xmyrow=DB_fetch_array($resultOld)){
						if ($xmyrow['tagref'] == $_POST['tagref']) {
							echo "<option selected Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagref'] . " - " . $xmyrow['tagdescription'] .'</option>';
						}
						else {
							echo "<option Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagref'] . " - " . $xmyrow['tagdescription'] .'</option>';
						}
					}
				echo "</select></td>";
				/****** SELECCION DE cuenta DESTINO *****/
			echo "<td>";
				echo "&nbsp;&nbsp;&nbsp;<b></b>"._('Unidad Destino: ') . "&nbsp;&nbsp;";
				echo "<select name='NewTag' style='font-size:9pt;'>";
					echo "<option selected value=''>Seleccione</option>";
					$SQL=" SELECT tags.*
						FROM tags , sec_unegsxuser
						WHERE  tags.tagref=sec_unegsxuser.tagref
						AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
						ORDER BY tags.tagdescription";
					$resultNew = DB_query($SQL,$db,'','');
					while ($xmyrow=DB_fetch_array($resultNew)){
						if ($xmyrow['tagref'] == $_POST['tagref']) {
							echo "<option selected Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagref'] . " - " . $xmyrow['tagdescription'] .'</option>';
						}
						else {
							echo "<option Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagref'] . " - " . $xmyrow['tagdescription'] .'</option>';
						}
					}
				echo "</select></td></tr>";
		echo "<tr align:'center'><td colspan='2' style='text-align:center;'><br><input type=submit name='ProcessAccountChange' VALUE='" . _('Procesar Cambio...') . "'></td></tr>";
	echo '</table>';
	
if (isset($_POST['ProcessAccountChange'])){

	/*VALIDA SI EXISTE UNIDAD ORIGEN*/
	$result=DB_query("SELECT tagref,tagdescription FROM tags WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg ('<br><br>' . _('El codigo de la unidad') . ': ' . $_POST['OldTag'] . ' ' . _('no existe actualmente en la base de datos del sistema'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$tipoOrigen = $myrow[0];
		
		$accountnameorigen = $myrow[1];
	}

	if ($_POST['NewTag']==''){
		prnMsg(_('El codigo de la unidad destino debe de ser seleccionada'),'error');
		include('includes/footer.inc');
		exit;
	}
	
	
	/*VALIDA QUE EXISTA UNIDAD DESTINO*/
	$result=DB_query("SELECT tagref,tagdescription FROM tags WHERE tagref='" . $_POST['NewTag'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('El codigo de la unidad') .': ' . $_POST['NewTag'] . ' ' . _('no existe actualmente en la base de datos del sistema') . ' - ' . _('este codigo debe de existir en el sistema antes de migrar movimientos de otra unidad de negocio...'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$tipoDestino = $myrow[0];
		$accountnamedestino  = $myrow[1];
	}
	
	
	if ($tipoDestino == $tipoOrigen){
		prnMsg(_('La unidad de origen es ') .': ' . $tipoOrigen . ' y de la destino es ' . $tipoDestino . ' - ' . _(' Las unidad de negocio es la misma, verifique.'),'error');
		include('includes/footer.inc');
		exit;
	}

	$result = DB_Txn_Begin($db);

	
	/*ComentsXUser*/
	$sql = "UPDATE ComentsXUser SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de ComentsXUser fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros de cuentas por proveeedor (ComentsXUser)...'),'info');
	
	/*Notesordersauth*/
	$result=DB_query("SELECT tagref FROM Notesordersauth WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)!=0){
		
		$result2=DB_query("SELECT tagref FROM Notesordersauth WHERE tagref='" . $_POST['NewTag'] . "'",$db);
		if (DB_num_rows($result)==0){
			$sql = "UPDATE Notesordersauth SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
			$result3 = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('La sentencia SQL para actualizar registros de Notesordersauth fallo...');
			prnMsg(_('ACTUALIZANDO REGISTROS (Notesordersauth)...'),'info');
		}else{
			$sql = "DELETE FROM Notesordersauth WHERE tagref='" . $_POST['OldTag'] . "'";
			$result3 = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('La sentencia SQL para eliminar registros de Notesordersauth fallo...');
			prnMsg(_('ELIMINANDO REGISTROS (Notesordersauth)...'),'info');	
		}
		
	}
	
	/*bankaccounts*/
	$result=DB_query("SELECT tagref FROM bankaccounts WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)!=0){
		
		$result2=DB_query("SELECT tagref FROM bankaccounts WHERE tagref='" . $_POST['NewTag'] . "'",$db);
		if (DB_num_rows($result)==0){
			$sql = "UPDATE bankaccounts SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
			$result3 = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('La sentencia SQL para actualizar registros de bankaccounts fallo...');
			prnMsg(_('ACTUALIZANDO REGISTROS (bankaccounts)...'),'info');
		}else{
			$sql = "DELETE FROM bankaccounts WHERE tagref='" . $_POST['OldTag'] . "'";
			$result3 = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('La sentencia SQL para eliminar registros de bankaccounts fallo...');
			prnMsg(_('ELIMINANDO REGISTROS (bankaccounts)...'),'info');	
		}
		
		
	}
	
	/*bankinvoice*/
	$result=DB_query("SELECT tagref FROM bankinvoice WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)!=0){
		
		$result2=DB_query("SELECT tagref FROM bankinvoice WHERE tagref='" . $_POST['NewTag'] . "'",$db);
		if (DB_num_rows($result)==0){
			$sql = "UPDATE bankinvoice SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
			$result3 = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('La sentencia SQL para actualizar registros de bankaccounts fallo...');
			prnMsg(_('ACTUALIZANDO REGISTROS (bankaccounts)...'),'info');
		}else{
			$sql = "DELETE FROM bankinvoice WHERE tagref='" . $_POST['OldTag'] . "'";
			$result3 = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('La sentencia SQL para eliminar registros de bankaccounts fallo...');
			prnMsg(_('ELIMINANDO REGISTROS (bankaccounts)...'),'info');	
		}	
	}
	/*banktrans*/
	$sql = "UPDATE banktrans SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (banktrans)...'),'info');
	
	/*budgets*/
	$result=DB_query("SELECT tagref FROM budgets WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$result=DB_query("SELECT tagref FROM budgets WHERE tagref='" . $_POST['NewTag'] . "'",$db);
		if (DB_num_rows($result)==0){
			$sql = "UPDATE budgets SET tagref = '" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('The SQL to DELETE old tagref record failed');
			prnMsg(_('Borrando cuenta anterior...'),'info');
		}else{
			$sql = "DELETE FROM budgets WHERE tagref='" . $_POST['OldTag'] . "'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('The SQL to DELETE old tagref record failed');
			prnMsg(_('Borrando cuenta anterior...'),'info');
		}
	}
	
	/*catdepartamentos*/
	$sql = "DELETE FROM catdepartamentos WHERE tagref='" . $_POST['OldTag'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old tagref record failed');
	prnMsg(_('Borrando de unidad en tabla catdepartamentos ...'),'warn');
	
	/*chartdetails*/
	$sql = "DELETE FROM chartdetails WHERE tagref='" . $_POST['OldTag'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old tagref record failed');
	prnMsg(_('Borrando de unidad en tabla de reposteo, favor de repostear en cuanto termine el proceso de unificacion ...'),'warn');
	
	
	/*debtortrans*/
	$sql = "UPDATE debtortrans SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (debtortrans)...'),'info');
	
	/*debtortransmovs*/
	$sql = "UPDATE debtortransmovs SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (debtortransmovs)...'),'info');
	
	/*estadoscuentabancarios*/
	$sql = "UPDATE estadoscuentabancarios SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (estadoscuentabancarios)...'),'info');

	/*gltrans*/
	$sql = "UPDATE gltrans SET tag='" . $_POST['NewTag'] . "' WHERE tag='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (gltrans)...'),'info');
	
	/*hs_stockcostsxtag*/
	$sql = "DELETE FROM hs_stockcostsxtag WHERE tagref='" . $_POST['OldTag'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old tagref record failed');
	prnMsg(_('Borrando de unidad en tabla hs_stockcostsxtag...'),'info');
	
	/*locations*/
	$sql = "UPDATE locations SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (locations)...'),'info');
	
	/*notesorders*/
	$sql = "UPDATE notesorders SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de banktrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (notesorders)...'),'info');
	
	/*prdproyectos*/
	$sql = "UPDATE prdproyectos SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de prdproyectos fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (prdproyectos)...'),'info');
	
	/*prorrxuneg*/
	$result=DB_query("SELECT tagref FROM prorrxuneg WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$result=DB_query("SELECT tagref FROM prorrxuneg WHERE tagref='" . $_POST['NewTag'] . "'",$db);
		if (DB_num_rows($result)==0){
			$sql = "UPDATE prorrxuneg SET tagref = '" . $_POST['NewTag'] . "', concept='" . $accountnamedestino . "' WHERE tagref='" . $_POST['OldTag'] . "'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('The SQL to DELETE old tagref record failed');
			prnMsg(_('Borrando unidad anterior...'),'info');
		}else{
			$sql = "DELETE FROM prorrxuneg WHERE tagref='" . $_POST['OldTag'] . "'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('The SQL to DELETE old tagref record failed');
			prnMsg(_('Borrando unidad anterior...'),'info');
		}
	}
	
	/*purchorders*/
	$sql = "UPDATE purchorders SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de prdproyectos fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (purchorders)...'),'info');
	
	/*salesorders*/
	$sql = "UPDATE salesorders SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de salesorders fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (salesorders)...'),'info');
	
	/*sec_unegsxuser*/
	$sql = "DELETE FROM sec_unegsxuser WHERE tagref='" . $_POST['OldTag'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old tagref record failed');
	prnMsg(_('Borrando unidad anterior de la tabla de sec_unegsxuser verifique permisos de usuario...'),'info');

	/*stockcostsxtag*/
	$sql = "DELETE FROM stockcostsxtag WHERE tagref='" . $_POST['OldTag'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old tagref record failed');
	prnMsg(_('Borrando unidad anterior de la tabla de stockcostsxtag...'),'info');	
	
	/*stockmoves*/
	$sql = "UPDATE stockmoves SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de stockmoves fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (stockmoves)...'),'info');
	
	/*suppnotesorders*/
	$sql = "UPDATE suppnotesorders SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de suppnotesorders fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (suppnotesorders)...'),'info');	
	
	/*supptrans*/
	$sql = "UPDATE supptrans SET tagref='" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de supptrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (supptrans)...'),'info');		
	
	/*supptrans*/
	$sql = "UPDATE supptrans SET alt_tagref='" . $_POST['NewTag'] . "' WHERE alt_tagref='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de supptrans fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (supptrans...alt_tagref..)...'),'info');
	
	/*tagsxbankaccounts*/
	$result=DB_query("SELECT tagref FROM tagsxbankaccounts WHERE tagref='" . $_POST['OldTag'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$result=DB_query("SELECT tagref FROM tagsxbankaccounts WHERE tagref='" . $_POST['NewTag'] . "'",$db);
		if (DB_num_rows($result)==0){
			$sql = "UPDATE tagsxbankaccounts SET tagref = '" . $_POST['NewTag'] . "' WHERE tagref='" . $_POST['OldTag'] . "'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('The SQL to DELETE old tagref record failed');
			prnMsg(_('Borrando unidad anterior...'),'info');
		}else{
			$sql = "DELETE FROM tagsxbankaccounts WHERE tagref='" . $_POST['OldTag'] . "'";
			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
			$ErrMsg = _('The SQL to DELETE old tagref record failed');
			prnMsg(_('Borrando unidad anterior...'),'info');
		}
	}
	
	/*usrcortecaja*/
	$sql = "UPDATE usrcortecaja SET tag='" . $_POST['NewTag'] . "' WHERE tag='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de usrcortecaja fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (usrcortecaja)...'),'info');
	
	/*usrcortecaja*/
	$sql = "UPDATE www_users SET defaultunidadNegocio='" . $_POST['NewTag'] . "' WHERE defaultunidadNegocio='" . $_POST['OldTag'] . "'";
	$ErrMsg = _('La sentencia SQL para actualizar registros de www_users fallo...');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('ACTUALIZANDO REGISTROS (www_users)...'),'info');
	
	/*sec_unegsxuser*/
	$sql = "DELETE FROM tags WHERE tagref='" . $_POST['OldTag'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old tagref record failed');
	prnMsg(_('Borrando unidad anterior de la tabla de tags'),'sucess');
	
	
	$result = DB_Txn_Commit($db);
}

/*echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Codigo de Cuenta Origen') . ":</td>
		<td><input type=Text name='OldTag' size=20 maxlength=20></td>
	</tr>";
echo '<tr><td> ' . _('Codigo de Cuenta Destino') . ":</td>
	<td><input type=Text name='NewTag' size=20 maxlength=20></td>
	</tr>
	</table>";

echo "<input type=submit name='ProcessAccountChange' VALUE='" . _('Procesar Cambio...') . "'>";

echo '</form>';*/
echo '</form>';
include('includes/footer.inc');

?>