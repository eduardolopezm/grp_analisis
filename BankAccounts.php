<?php
/* $Revision: 1.21 $ */
/* Cambios
1.- Se agrego el include('includes/SecurityFunctions.inc');*/
/*
 * AHA
* 6-Nov-2014
* Cambio de ingles a espa�ol los mensajes de usuario de tipo error,info,warning, y success.
*/
$PageSecurity = 10;

include('includes/session.inc');

$title = _('Mantenimiento de Cuentas de Bancos');

include('includes/header.inc');
$funcion=94;
include('includes/SecurityFunctions.inc');



echo '<div class="page_help_text">' . _('Update Bank Account details.  Account Code is for SWIFT or BSB type Bank Codes.  Set Default for Invoices to "yes" to print Account details on Invoices (only one account can be set to "yes").') . '.</div><br>';

if (isset($_GET['SelectedBankAccount'])) {
	$SelectedBankAccount=$_GET['SelectedBankAccount'];
} elseif (isset($_POST['SelectedBankAccount'])) {
	$SelectedBankAccount=$_POST['SelectedBankAccount'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();	

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	$sql="SELECT count(accountcode) 
			FROM bankaccounts WHERE accountcode='".$_POST['AccountCode']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedBankAccount)) {
		$InputError = 1;
		prnMsg( _('El codigo de la cuenta bancaria ya existe en la base de datos'),'error');
		$Errors[$i] = 'AccountCode';
		$i++;		
	}
	if (strlen($_POST['BankAccountName']) >50) {
		$InputError = 1;
		prnMsg(_('El nombre de la cuenta bancaria debe ser cincuenta caracteres o menos'),'error');
		$Errors[$i] = 'AccountName';
		$i++;		
	}
	if ( trim($_POST['BankId']) == '' ) {
		$InputError = 1;
		prnMsg(_('El nombre del banco no puede estar vacío.'), 'error');
		$Errors[$i] = 'BankId';
		$i++;
	}
	if ( trim($_POST['BankAccountName']) == '' ) {
		$InputError = 1;
		prnMsg(_('El nombre de la cuenta bancaria no puede estar vac�o.'),'error');
		$Errors[$i] = 'AccountName';
		$i++;		
	}
	if ( trim($_POST['BankAccountNumber']) == '' ) {
		$InputError = 1;
		prnMsg(_('El numero de cuenta bancaria no puede estar vac�o.'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;		
	}
	if (strlen($_POST['BankAccountNumber']) >50) {
		$InputError = 1;
		prnMsg(_('El numero de cuenta bancaria debe ser cincuenta caracteres o menos'),'error');
		$Errors[$i] = 'AccountNumber';
		$i++;		
	}
	if (strlen($_POST['BankAddress']) >50) {
		$InputError = 1;
		prnMsg(_('La direcci�n del banco debe ser cincuenta caracteres o menos'),'error');
		$Errors[$i] = 'BankAddress';
		$i++;		
	}

	if (isset($SelectedBankAccount) AND $InputError !=1) {
		
		/*Check if there are already transactions against this account - cant allow change currency if there are*/
		
		$sql = 'SELECT * FROM banktrans WHERE bankact="' . $SelectedBankAccount.'"';
		$BankTransResult = DB_query($sql,$db);
		if (DB_num_rows($BankTransResult)>0) {
			$sql = "UPDATE bankaccounts
				SET bankaccountname='" . $_POST['BankAccountName'] . "',
				bankaccountcode='" . $_POST['BankAccountCode'] . "',
				bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
				bankaddress='" . $_POST['BankAddress'] . "',
				invoice ='" . $_POST['DefAccount'] . "',
				tagref = '" . $_POST['TagRef'] . "',
				bankid = '" . $_POST['BankId'] . "'
			WHERE accountcode = '" . $SelectedBankAccount . "'";
			prnMsg(_('Tenga en cuenta que no es posible cambiar la moneda de la cuenta una vez que hay transacciones en su contra'),'warn');
	echo '<br>';
		} else {
			$sql = "UPDATE bankaccounts
				SET bankaccountname='" . $_POST['BankAccountName'] . "',
				bankaccountcode='" . $_POST['BankAccountCode'] . "',
				bankaccountnumber='" . $_POST['BankAccountNumber'] . "',
				bankaddress='" . $_POST['BankAddress'] . "',
				currcode ='" . $_POST['CurrCode'] . "',
				invoice ='" . $_POST['DefAccount'] . "',
				tagref = '" . $_POST['TagRef'] . "',
				bankid = '" . $_POST['BankId'] . "'
				WHERE accountcode = '" . $SelectedBankAccount . "'";
		}

		$msg = _('Los detalles de cuentas bancarias han sido actualizados');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO bankaccounts (
						accountcode,
						bankaccountname,
						bankaccountcode,
						bankaccountnumber,
						bankaddress,
						currcode,
						invoice,
						tagref,
						bankid)
				VALUES ('" . $_POST['AccountCode'] . "',
					'" . $_POST['BankAccountName'] . "',
					'" . $_POST['BankAccountCode'] . "',
					'" . $_POST['BankAccountNumber'] . "',
					'" . $_POST['BankAddress'] . "', 
					'" . $_POST['CurrCode'] . "',
					'" . $_POST['DefAccount'] . "',
					'" . $_POST['TagRef'] . "', 
					'" . $_POST['BankId'] . "'
					)";
		$msg = _('La nueva cuenta bancaria se ha introducido');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('La cuenta bancaria no puede ser insertada o modificada debido a');
		$DbgMsg = _('El SQL que se utiliza para insertar/modificar los datos de la cuenta bancaria es');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	
		/*eliminamos los permisos para unidades de negocio para este usuario*/
		$sql="Delete from tagsxbankaccounts WHERE accountcode = '".$_POST['AccountCode']."'";
		$ErrMsg = _('Las operaciones sobre las unidades de negocio para esta cuenta no han sido posibles por que ');
		$DbgMsg = _('El SQL utilizado es:');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		/* Agregar permisos de unidad de negocio por usuario*/
		if (isset($_POST['TotalUnidades'])){
			$TotalUnidades=$_POST['TotalUnidades'];
			for ( $unidad = 0 ; $unidad <= $TotalUnidades ; $unidad ++) {
				if ($_POST['UNSel'.$unidad]==TRUE){
					$NameUnidad=$_POST['NameUnidad'.$unidad];
					$sql="insert into tagsxbankaccounts (tagref,accountcode)";
					#$sql=$sql." values('".$usuarioseleccionado."','".$NameUnidad."')";
					$sql=$sql." values('".$NameUnidad."','".$_POST['AccountCode']."')";
					$ErrMsg = _('Las operaciones sobre las unidades de negocio para esta cuenta no han sido posibles por que ');
					$DbgMsg = _('El SQL utilizado es:');
					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
				}
			}
		}		
	
		
		prnMsg($msg,'success');
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		echo '<br>';
		unset($_POST['AccountCode']);
		unset($_POST['BankAccountName']);
		unset($_POST['BankAccountNumber']);
		unset($_POST['BankAddress']);
		unset($_POST['CurrCode']);
		unset($_POST['DefAccount']);
		unset($_POST['TagRef']);
		unset($_POST['BankId']);
		unset($SelectedBankAccount);
	}
	

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(*) FROM banktrans WHERE banktrans.bankact='$SelectedBankAccount'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('No se puede eliminar esta cuenta bancaria porque las transacciones se han creado utilizando esta cuenta'),'warn');
		echo '<br> ' . _('Hay') . ' ' . $myrow[0] . ' ' . _('las transacciones con este codigo de cuenta bancaria');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM bankaccounts WHERE accountcode='$SelectedBankAccount'";
		$result = DB_query($sql,$db);
		prnMsg(_('Cuenta bancaria borrada'),'success');
	} //end if Delete bank account
	
	unset($_GET['delete']);
	unset($SelectedBankAccount);
}

/* Always show the list of accounts */
If (!isset($SelectedBankAccount)) {
	$sql = "SELECT bankaccounts.accountcode,
			bankaccounts.bankaccountcode,
			chartmaster.accountname,
			bankaccountname,
			bankaccountnumber,
			bankaddress,
			currcode,
			invoice,
			bank_name
		FROM bankaccounts LEFT JOIN banks ON bankaccounts.accountcode = banks.bank_id,
			chartmaster
		WHERE bankaccounts.accountcode = chartmaster.accountcode";
	
	$ErrMsg = _('Las cuentas bancarias creadas no pueden ser recuperados porque');
	$DbgMsg = _('El SQL para recuperar los datos de la cuenta bancaria es') . '<br>' . $sql;
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<table  class="table table-bordered">';

	echo "<tr class='header-verde'><th>" . _('GL Codigo Cuenta') . "</th>
		<th style='text-align: center;'>" . _('Nombre Cuenta Banco') . "</th>
		<th style='text-align: center;'>" . _('Codigo Cuenta Banco') . "</th>
		<th style='text-align: center;'>" . _('Numero Cuenta Banco') . "</th>
		<th style='text-align: center;'>" . _('Direccion Banco') . "</th>
		<th style='text-align: center;'>" . _('Moneda') . "</th>
		<th style='text-align: center;'>" . _('Default para Facturas') . "</th>
		<th style='text-align: center;'>" . _('Banco') . "</th>
		<th style='text-align: center;'>" . _('Actualizar') . "</th>
		<th style='text-align: center;'>" . _('Eliminar') . "</th>
	</tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}
	if ($myrow[7]==0) {
		$defacc=_('No');
	} else {
		$defacc=_('Yes');
	}
	printf("<td>%s<br><font size=2>%s</font></td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href=\"%s?SelectedBankAccount=%s\">" . _('Edit') . "</td>
		<td style='text-align: center;' ><a href=\"%s?SelectedBankAccount=%s&delete=1\"><span class='glyphicon glyphicon-trash'></span></td>
		</tr>",
		$myrow[0],
		$myrow[2],
		$myrow[3],
		$myrow[1],
		$myrow[4],
		$myrow[5],
		$myrow[6],
		$defacc,
		$myrow[8],
		$_SERVER['PHP_SELF'],
		$myrow[0],
		$_SERVER['PHP_SELF'],
		$myrow[0]);

	}
	//END WHILE LIST LOOP


	echo '</table><p>';
}

if (isset($SelectedBankAccount)) {
	echo '<p>';
	echo '<div class="centre"><p><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Mostrar listado de Cuentas de Bancos Registradas') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . ">";

if (isset($SelectedBankAccount) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT accountcode,
			bankaccountname,
			bankaccountcode,
			bankaccountnumber,
			bankaddress,
			currcode,
			invoice,
			tagref,
			bankid
		FROM bankaccounts
		WHERE bankaccounts.accountcode='$SelectedBankAccount'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['AccountCode'] = $myrow['accountcode'];
	$_POST['BankAccountName']  = $myrow['bankaccountname'];
	$_POST['BankAccountCode']  = $myrow['bankaccountcode'];
	$_POST['BankAccountNumber'] = $myrow['bankaccountnumber'];
	$_POST['BankAddress'] = $myrow['bankaddress'];
	$_POST['CurrCode'] = $myrow['currcode'];
	$_POST['DefAccount'] = $myrow['invoice'];
	$_POST['TagRef'] = $myrow['tagref'];
	$_POST['BankId'] = $myrow['bankid'];

	echo '<input type=hidden name=SelectedBankAccount VALUE=' . $SelectedBankAccount . '>';
	echo '<input type=hidden name=AccountCode VALUE=' . $_POST['AccountCode'] . '>';
	echo '<table> <tr><td>' . _('Codigo GL Cuenta Banco') . ':</td><td>';
	echo $_POST['AccountCode'] . '</td></tr>';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table><tr><td>' . _('Codigo GL Cuenta Banco') . 
		":</td><td><Select tabindex='1' " . (in_array('AccountCode',$Errors) ?  'class="selecterror"' : '' ) ." name='AccountCode'>";

	$sql = "SELECT accountcode,
			accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_ = accountgroups.groupname
		AND accountgroups.pandl = 0
		ORDER BY accountcode";

	$result = DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['AccountCode']) and $myrow['accountcode']==$_POST['AccountCode']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname'];

	} //end while loop

	echo '</select></td></tr>';
}

// Check if details exist, if not set some defaults
if (!isset($_POST['BankAccountName'])) {
	$_POST['BankAccountName']='';
}
if (!isset($_POST['BankAccountNumber'])) {
	$_POST['BankAccountNumber']='';
}
if (!isset($_POST['BankAccountCode'])) {
        $_POST['BankAccountCode']='';
}
if (!isset($_POST['BankAddress'])) {
	$_POST['BankAddress']='';
}

echo '<tr><td>' . _('Banco') .
":</td><td><Select " . (in_array('BankId',$Errors) ?  'class="selecterror"' : '' ) ." name='BankId'>";

$sql = "SELECT bank_id, bank_name
		FROM banks
		WHERE bank_active = 1
		ORDER BY bank_name";

$result = DB_query($sql,$db);
//var_dump($sql);
//if ($myrow = DB_fetch_array($result)>0) {
var_dump($sql);
	echo "<option value=''>" . _("Seleccione un banco ...") . "</option>";
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['BankId']) and $myrow['bank_id']==$_POST['BankId']) {
			echo '<option selected VALUE=';
		} else {
			echo '<option VALUE=';
		}
		echo $myrow['bank_id'] . '>' . $myrow['bank_name'];
	
	} //end while loop
/*}else{
	$MSG = "No existen cuentas de banco, agregue alguna antes de continuar...";
	prnMsg($MSG,'error');
	var_dump($MSG);
}*/

echo '</select></td></tr>';

echo '<tr><td>' . _('Nombre Cuenta Banco') . ': </td>
			<td><input tabindex="2" ' . (in_array('AccountName',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAccountName" value="' . $_POST['BankAccountName'] . '" size=40 maxlength=50></td></tr>
		<tr><td>' . _('Codigo Cuenta Banco') . ': </td>
                        <td><input tabindex="3" ' . (in_array('AccountCode',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAccountCode" value="' . $_POST['BankAccountCode'] . '" size=40 maxlength=50></td></tr>
		<tr><td>' . _('Numero Cuenta Banco') . ': </td>
			<td><input tabindex="3" ' . (in_array('AccountNumber',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAccountNumber" value="' . $_POST['BankAccountNumber'] . '" size=40 maxlength=50></td></tr>
		<tr><td>' . _('Direccion Banco') . ': </td>
			<td><input tabindex="4" ' . (in_array('BankAddress',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="BankAddress" value="' . $_POST['BankAddress'] . '" size=40 maxlength=50></td></tr>
';

		
echo '<tr><td>' . _('Moneda') . ': </td><td><select tabindex="5" name="CurrCode">';

if (!isset($_POST['CurrCode']) OR $_POST['CurrCode']==''){
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
}
$result = DB_query('SELECT currabrev, currency FROM currencies',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['currabrev']==$_POST['CurrCode']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['currabrev'] . '>' . $myrow['currabrev'];
} //end while loop

echo '</select></td>';

echo		'<tr><td>' . _('Default para Facturas') . ': </td><td><select tabindex="6" name="DefAccount">';

if (!isset($_POST['DefAccount']) OR $_POST['DefAccount']==''){
        $_POST['DefAccount'] = $_SESSION['CompanyRecord']['currencydefault'];
}

if (isset($SelectedBankAccount)) {
	$result = DB_query('SELECT invoice FROM bankaccounts where accountcode =' . $SelectedBankAccount ,$db);
	while ($myrow = DB_fetch_array($result)) {
		if ($myrow['invoice']== 1) {
			echo '<option selected VALUE=1>'._('Yes').'</option><option value=0>'._('No').'</option>';
		} else {
			echo '<option selected VALUE=0>'._('No').'</option><option value=1>'._('Yes').'</option>';
		}
	}//end while loop
} else {
	echo '<option VALUE=0>'._('Yes').'</option><option value=1>'._('No').'</option>';
}
echo '</select></td>';

echo '<tr><td colspan=2 align=center> &nbsp;&nbsp;&nbsp;<li>'. _('Unidades de Negocio por Cuenta').'</li></td></tr>';
echo '<tr><td colspan=2>';
	$sql = "SELECT L.tagref as codsuc,U.tagref as coduser, L.tagdescription as suc FROM tags L left join tagsxbankaccounts U on  L.tagref = U.tagref and U.accountcode='".$_POST['AccountCode']."'";
	$Result = DB_query($sql, $db);
	echo '<table width=80% align=center>';
	if (DB_num_rows($Result)>0 ) {
		echo "<tr><td ><hr></td></tr>";
	}
	$k=0; //row colour counter
	$j=0;
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo '<td nowrap>';
		$sucursal=$AvailRow['codsuc'];
		$coduser=$AvailRow['coduser'];
		$nombresuc=$AvailRow['suc'];
		if(is_null($coduser)) {
			echo '<INPUT type=checkbox name=UNSel'.$j.' >';
			echo '<INPUT type=hidden name=NameUnidad'.$j.' value='.$sucursal.' >';
			echo $nombresuc;
		} else{
			echo '<INPUT type=checkbox name=UNSel'.$j.' checked>';
			echo '<INPUT type=hidden name=NameUnidad'.$j.' value='.$sucursal.' >';
			echo $nombresuc;
		}
		$j=$j+1;
		echo '</td>';
		echo '</tr>';
	}
	echo '<tr><td colspan=2><input type=hidden name=TotalUnidades value='.$j.'></td></tr>';
	echo '</table>';
echo '</td></tr>';


echo '</tr></table>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Guardar Informacion') .'"></div>';

echo '</form>';



include 'includes/footer_Index.inc';
?>
