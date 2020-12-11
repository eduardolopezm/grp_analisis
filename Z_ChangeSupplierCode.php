<?php
/* $Revision: 1.11 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('PAGINA DE UTILERIA PARA UNIFICAR CUENTAS CONTABLES');
include('includes/header.inc');

$funcion=980;
include('includes/SecurityFunctions.inc');

if (isset($_POST['ProcessSupplierChange'])){

/*First check the supplier code exists */
	$result=DB_query("SELECT supplierid, currcode FROM suppliers WHERE supplierid='" . $_POST['OldDebtorNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg ('<br><br>' . _('El codigo de proveedor') . ': ' . $_POST['OldDebtorNo'] . ' ' . _('no existe actualmente en la base de datos del sistema'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$monedaOrigen = $myrow[1];
	}


	if ($_POST['NewDebtorNo']==''){
		prnMsg(_('El codigo de proveedor destino debe de ser capturado'),'error');
		include('includes/footer.inc');
		exit;
	}
	
/*Now check that the new code also exist */
	$result=DB_query("SELECT supplierid, currcode FROM suppliers WHERE supplierid='" . $_POST['NewDebtorNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('El codigo de proveedor de reemplazo') .': ' . $_POST['NewDebtorNo'] . ' ' . _('no existe actualmente en la base de datos del sistema') . ' - ' . _('este codigo debe de existir en el sistema antes de migrar movimientos de otro proveedor...'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$monedaDestino = $myrow[1];
	}
	
	
	if ($monedaDestino != $monedaOrigen){
		prnMsg(_('El tipo de moneda del proveedor origen es ') .': ' . $monedaOrigen . ' y del destino es ' . $monedaDestino . ' - ' . _(' las monedas deben de ser las mismas para poder traspasar movimientos...'),'error');
		include('includes/footer.inc');
		exit;
	}

	$result = DB_Txn_Begin($db);


	$sql = "UPDATE grns SET supplierid='" . $_POST['NewDebtorNo'] . "' WHERE supplierid='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update GRNs transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de recepcion de productos...'),'info');


	$sql = "UPDATE purchdata SET supplierno='" . $_POST['NewDebtorNo'] . "' WHERE supplierno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update purchdata transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de datos de compra de proveedores X productos...'),'info');


	$sql = "UPDATE shipments SET supplierid='" . $_POST['NewDebtorNo'] . "' WHERE supplierid='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update shipments transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de EMBARQUES...'),'info');



	$sql = "UPDATE suppliercontacts SET supplierid='" . $_POST['NewDebtorNo'] . "' WHERE supplierid='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update suppliercontacts transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de contactos de proveedores...'),'info');



	$sql = "UPDATE suppliers SET narrative = CONCAT(narrative,'+','" . $_POST['OldDebtorNo'] . "')  WHERE supplierid='" . $_POST['NewDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de proveedor...'),'info');
	
	$sql = "DELETE FROM suppliers WHERE supplierid='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to DELETE old suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Borrando clave de proveedor anterior...'),'info');


	$sql = "UPDATE suppnotesorders SET supplierno = '" . $_POST['NewDebtorNo'] . "' WHERE supplierno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update suppnotesorders record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de notas de credito proveedor...'),'info');

	
	$sql = "UPDATE supptrans SET supplierno = '" . $_POST['NewDebtorNo'] . "', ref2 = CONCAT(ref2,'+','" . $_POST['OldDebtorNo'] . "') WHERE supplierno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update supptrans record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de transacciones de proveedor...'),'info');
	
	$sql = "UPDATE purchorders SET supplierno = '" . $_POST['NewDebtorNo'] . "'WHERE supplierno='" . $_POST['OldDebtorNo'] . "'";

	$ErrMsg = _('The SQL to update supptrans record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros de transacciones de proveedor...'),'info');

	$result = DB_Txn_Commit($db);
}

echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Codigo de Proveedor Origen') . ":</td>
		<td><input type=Text name='OldDebtorNo' size=20 maxlength=20></td>
	</tr>";
echo '<tr><td> ' . _('Codigo de Proveedor Destino') . ":</td>
	<td><input type=Text name='NewDebtorNo' size=20 maxlength=20></td>
	</tr>
	</table>";

echo "<input type=submit name='ProcessSupplierChange' VALUE='" . _('Procesar Cambio...') . "'>";

echo '</form>';

include('includes/footer.inc');

?>