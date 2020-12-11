<?php
/* $Revision: 1.11 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('UTILITY PAGE To Changes A Customer Code In All Tables');
include('includes/header.inc');

$funcion=981;
include('includes/SecurityFunctions.inc');

if (isset($_POST['ProcessCustomerChange'])){

/*First check the customer code exists */
	$result=DB_query("SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['OldDebtorNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg ('<br><br>' . _('El codigo de cliente origen no existe en el sistema') . ': ' . $_POST['OldDebtorNo'] ,'error');
		include('includes/footer.inc');
		exit;
	}

	if ($_POST['NewDebtorNo']==''){
		prnMsg(_('El nuevo codigo de cliente no debe ser vacio'),'error');
		include('includes/footer.inc');
		exit;
	}
	
/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT debtorno FROM debtorsmaster WHERE debtorno='" . $_POST['NewDebtorNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('El nuevo codigo de cliente no existe') .': ' . $_POST['NewDebtorNo'] . ' ' . _('este debe de existir para poder traspasar los movimientos a el') ,'error');
		include('includes/footer.inc');
		exit;
	}
	
	/* Verificar que solo tenga una sucursal y que el codigo de esta sea igual al codigo del cliente */
	$result=DB_query("SELECT debtorno FROM custbranch WHERE branchcode<>debtorno AND debtorno='" . $_POST['OldDebtorNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		prnMsg(_('El cliente origen') .': ' . $_POST['OldDebtorNo'] . ' ' . _('tiene mas de una sucursal o su sucursal tiene un codigo diferente, este es un caso especial...') ,'error');
		//include('includes/footer.inc');
		//exit;
	}

	$result = DB_Txn_Begin($db);

	prnMsg(_('Changing debtor transaction records'),'info');
	$sql = "UPDATE debtortrans SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "', reference=CONCAT('" . $_POST['NewDebtorNo'] . "',' ',reference) WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update debtor transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	
	prnMsg(_('Changing sales analysis records'),'info');
	$sql = "UPDATE salesanalysis SET cust='" . $_POST['NewDebtorNo'] . "', custbranch='" . $_POST['NewDebtorNo'] . "' WHERE cust='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update Sales Analysis records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg (_('Changing order delivery differences records'),'info');
	$sql = "UPDATE orderdeliverydifferenceslog SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update order delivery differences records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*
	 SELECT prices.debtorno, 
	prices.branchcode, 
	prices.price
	FROM prices
	*/
	prnMsg(_('Changing pricing records'),'info');
	$sql = "UPDATE prices SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update prices records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*
	SELECT debtortransmovs.debtorno, 
	debtortransmovs.branchcode
	FROM debtortransmovs
	*/
	prnMsg(_('Changing debtortransmovs records'),'info');
	$sql = "UPDATE debtortransmovs SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update debtortransmovs records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*
	SELECT custcontacts.debtorno
	FROM custcontacts
	*/	
	prnMsg(_('Changing custcontacts records'),'info');
	$sql = "UPDATE custcontacts SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update custcontacts records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	/*
	SELECT custnotes.debtorno, 
		custnotes.noteid
	FROM custnotes
	*/
	prnMsg(_('Changing custnotes records'),'info');
	$sql = "UPDATE custnotes SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update custnotes records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	/*
	SELECT contracts.debtorno, 
		contracts.branchcode
	FROM contracts
	*/
	prnMsg(_('Changing contracts records'),'info');
	$sql = "UPDATE contracts SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update contracts records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*
	SELECT recurringsalesorders.debtorno, 
		recurringsalesorders.branchcode, 
		recurringsalesorders.recurrorderno
	FROM recurringsalesorders
	*/
	prnMsg(_('Changing recurringsalesorders records'),'info');
	$sql = "UPDATE recurringsalesorders SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update recurringsalesorders records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*
	SELECT rutasxcliente.debtorno, 
		rutasxcliente.rutaid
	FROM rutasxcliente
	*/
	prnMsg(_('Changing rutasxcliente records'),'info');
	$sql = "UPDATE rutasxcliente SET debtorno='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update rutasxcliente records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	/*
	SELECT salesorders.debtorno, 
		salesorders.branchcode, 
		salesorders.orderno
	FROM salesorders
	*/
	prnMsg(_('Changing salesorders records'),'info');
	$sql = "UPDATE salesorders SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "', comments=CONCAT('" . $_POST['OldDebtorNo'] . "',' ',comments) WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update salesorders records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	/*	
	SELECT www_users.branchcode
	FROM www_users
	*/
	prnMsg(_('Changing www_users records'),'info');
	$sql = "UPDATE www_users SET branchcode='" . $_POST['NewDebtorNo'] . "' WHERE branchcode='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update www_users records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	/*
	SELECT notesorders.debtorno, 
		notesorders.branchcode
	FROM notesorders
	*/
	prnMsg(_('Changing notesorders records'),'info');
	$sql = "UPDATE notesorders SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "', comments=CONCAT('" . $_POST['OldDebtorNo'] . "',' ',comments) WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update notesorders records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	/*
	SELECT recurringsalesorders.debtorno, 
		recurringsalesorders.branchcode
	FROM recurringsalesorders
	*/
	prnMsg(_('Changing recurringsalesorders records'),'info');
	$sql = "UPDATE recurringsalesorders SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "' WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update recurringsalesorders records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	/*
	SELECT stockmoves.debtorno, 
		stockmoves.branchcode
	FROM stockmoves
	*/
	prnMsg(_('Changing stockmoves records'),'info');
	$sql = "UPDATE stockmoves SET debtorno='" . $_POST['NewDebtorNo'] . "', branchcode='" . $_POST['NewDebtorNo'] . "', reference=CONCAT('" . $_POST['OldDebtorNo'] . "',' ',reference) WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//Actualiza tabla de recordatorios
	$sql = "UPDATE debtorsreminder 
			SET debtorno='" . $_POST['NewDebtorNo'] . "'
			WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE debtortaxes
			SET debtorno='" . $_POST['NewDebtorNo'] . "'
			WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "DELETE FROM  diasxcliente
			WHERE id_cliente='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	
	$sql = "UPDATE prospect_movimientos
			SET debtorno='" . $_POST['NewDebtorNo'] . "',
				branchcode='" . $_POST['NewDebtorNo'] . "'
			WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE vehiclesbycostumer
			SET debtorno='" . $_POST['NewDebtorNo'] . "',
				branchcode='" . $_POST['NewDebtorNo'] . "'
			WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to update stockmoves records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	
	// actualiza sucursales de client
	$sql="insert custbranch(branchcode,debtorno,brname,taxid,braddress1,braddress2,braddress3,
			braddress4,braddress5,braddress6,braddress7,lat,lng,estdeliverydays,area,salesman,
			fwddate,movilno,nextelno,phoneno,faxno,contactname,email,lineofbusiness,
			flagworkshop,defaultlocation,taxgroupid,defaultshipvia,deliverblind,disabletrans,
			brpostaddr1,brpostaddr2,brpostaddr3,brpostaddr4,brpostaddr5,brpostaddr6,
			specialinstructions,custbranchcode,creditlimit,custdata1,custdata2,custdata3,
			custdata4,custdata5,custdata6,ruta,brnumint,brnumext,paymentname,nocuenta,
			welcomemail,SectComClId,custpais,NumeAsigCliente,descclientecomercial,descclientepropago,
			descclienteop,typeaddenda)";
	$sql = $sql." select concat(".$_POST['NewDebtorNo'].",RIGHT(branchcode,3)),".$_POST['NewDebtorNo'].",brname,taxid,braddress1,braddress2,braddress3,
			braddress4,braddress5,braddress6,braddress7,lat,lng,estdeliverydays,area,salesman,
			fwddate,movilno,nextelno,phoneno,faxno,contactname,email,lineofbusiness,
			flagworkshop,defaultlocation,taxgroupid,defaultshipvia,deliverblind,disabletrans,
			brpostaddr1,brpostaddr2,brpostaddr3,brpostaddr4,brpostaddr5,brpostaddr6,
			specialinstructions,custbranchcode,creditlimit,custdata1,custdata2,custdata3,
			custdata4,custdata5,custdata6,ruta,brnumint,brnumext,paymentname,nocuenta,
			welcomemail,SectComClId,custpais,NumeAsigCliente,descclientecomercial,descclientepropago,
			descclienteop,typeaddenda
	 FROM custbranch WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	//echo '<pre>sql:'.$sql;
	$ErrMsg = _('The SQL to delete the old CustBranch records for the old debtor record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Deleting the old customer branch records from the CustBranch table'),'info');
	$sql = "DELETE FROM custbranch WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to delete the old CustBranch records for the old debtor record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);


	prnMsg(_('Deleting the customer code from the DebtorsMaster table'),'info');
	$sql = "DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['OldDebtorNo'] . "'";
	$ErrMsg = _('The SQL to delete the old debtor record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	$result = DB_Txn_Commit($db);
}

echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Clave Cliente Origrn') . ":</td>
		<td><input type=Text name='OldDebtorNo' size=20 maxlength=20></td>
	</tr>";
echo '<tr><td> ' . _('Clave Cliente Destino') . ":</td>
	<td><input type=Text name='NewDebtorNo' size=20 maxlength=20></td>
	</tr>
	</table>";

echo "<input type=submit name='ProcessCustomerChange' VALUE='" . _('Process') . "'>";

echo '</form>';

include('includes/footer.inc');

?>