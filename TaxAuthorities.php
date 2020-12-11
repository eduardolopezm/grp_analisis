<?php

$PageSecurity=15;
include('includes/session.inc');
$title = _('Autoridades de Impuestos');
include('includes/header.inc');
$funcion=96;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');



if (isset($_POST['SelectedTaxAuthID'])){
	$SelectedTaxAuthID =$_POST['SelectedTaxAuthID'];
} elseif(isset($_GET['SelectedTaxAuthID'])){
	$SelectedTaxAuthID =$_GET['SelectedTaxAuthID'];
}


if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if ( trim( $_POST['Description'] ) == '' ) {
		$InputError = 1;
		prnMsg( _('La descripcion de tipo de impuesto no puede ser vacia'), 'error');
	}

	if (isset($SelectedTaxAuthID)) {

		/*SelectedTaxAuthID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$sql = 'UPDATE taxauthorities
				SET taxglcode =' . $_POST['TaxGLCode'] . ',
				purchtaxglaccount =' . $_POST['PurchTaxGLCode'] . ',
				taxglcodePaid =' . $_POST['TaxGLCodePaid'] . ',
				purchtaxglaccountPaid =' . $_POST['PurchTaxGLCodePaid'] . ",
				description = '" . $_POST['Description'] . "',
				bank = '". $_POST['Bank']."',
				bankacctype = '". $_POST['BankAccType']."',
				bankacc = '". $_POST['BankAcc']."',
				bankswift = '". $_POST['BankSwift']."'
			WHERE taxid = " . $SelectedTaxAuthID;

		$ErrMsg = _('La actualizacion de esta autoridad de impuestos fallo porque');
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('El registro de autoridad de impuestos ha sido actualizado');

	} elseif ($InputError !=1) {

	/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$sql = "INSERT INTO taxauthorities (
						taxglcode,
						purchtaxglaccount,
						taxglcodePaid,
						purchtaxglaccountPaid,
						description,
						bank,
						bankacctype,
						bankacc,
						bankswift)
			VALUES (
				" . $_POST['TaxGLCode'] . ",
				" . $_POST['PurchTaxGLCode'] . ",
				" . $_POST['TaxGLCodePaid'] . ",
				" . $_POST['PurchTaxGLCodePaid'] . ",
				'" .$_POST['Description'] . "',
				'" .$_POST['Bank'] . "',
				'" .$_POST['BankAccType'] . "',
				'" .$_POST['BankAcc'] . "',
				'" .$_POST['BankSwift'] . "'
				)";

		$Errmsg = _('La creacion de esta autoridad de impuestos fallo porque');
		$result = DB_query($sql,$db,$ErrMsg);

		$msg = _('El registro de esta autoridad de impuestos ha sido insertado a la base de datos');

		$NewTaxID = DB_Last_Insert_ID($db,'taxauthorities','taxid');

		$sql = 'INSERT INTO taxauthrates (
					taxauthority,
					dispatchtaxprovince,
					taxcatid
					)
				SELECT
					' . $NewTaxID  . ',
					taxprovinces.taxprovinceid,
					taxcategories.taxcatid
				FROM taxprovinces,
					taxcategories';

			$InsertResult = DB_query($sql,$db);
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) and $InputError !=1) {
		unset( $_POST['TaxGLCode']);
		unset( $_POST['PurchTaxGLCode']);
		unset( $_POST['TaxGLCodePaid']);
		unset( $_POST['PurchTaxGLCodePaid']);
		unset( $_POST['Description']);
		unset( $SelectedTaxID );
	}

	prnMsg($msg);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$sql= 'SELECT COUNT(*)
			FROM taxgrouptaxes
		WHERE taxauthid=' . $SelectedTaxAuthID;

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnmsg(_('No se pudo eliminar esta autoridad de impuestos porque existen grupos de impuestos definidos que la utilizan'),'advertencia');
	} else {
		/*Cascade deletes in TaxAuthLevels */
		$result = DB_query('DELETE FROM taxauthrates WHERE taxauthority= ' . $SelectedTaxAuthID,$db);
		$result = DB_query('DELETE FROM taxauthorities WHERE taxid= ' . $SelectedTaxAuthID,$db);
		prnMsg(_('El registro de autoridad fiscal seleccionado ha sido borrado'),'success');
		unset ($SelectedTaxAuthID);
	} // end of related records testing
}

if (!isset($SelectedTaxAuthID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxAuthID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = 'SELECT taxid,
			description,
			taxglcode,
			purchtaxglaccount,
			taxglcodePaid,
			purchtaxglaccountPaid,
			bank,
			bankacc,
			bankacctype,
			bankswift
		FROM taxauthorities';

	$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTAR ESTE ERROR Y BUSCAR ASISTENCIA') . ': ' . _('La autoridad de impuestos no pudo ser obtenida porque');
	$DbgMsg = _('El siguiente SQL fue utilizado para obtener la autoridad de impuestos');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo '<CENTER><table border=1>';
	echo "<tr>
		<th>" . _('ID') . "</th>
		<th>" . _('Descripcion') . "</th>
		<th>" . _('Impuesto Por Pagar') . '<BR>' . _('Cuenta Contable') . "</th>
		<th>" . _('Impuesto Facturado') . '<BR>' . _('Cuenta Contable') . "</th>
		<th>" . _('Impuesto Pagado') . '<BR>' . _('Cuenta Contable') . "</th>
		<th>" . _('Impuesto Cobrado') . '<BR>' . _('Cuenta Contable') . "</th>
		<th>" . _('Banco') . "</th>
		<th>" . _('Cuenta de Banco') . "</th>
		<th>" . _('Tipo de Cuenta') . "</th>
		<th>" . _('Swift') . "</th>
		</tr></FONT>";

	while ($myrow = DB_fetch_row($result)) {

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><td><a href=\"%s&TaxAuthority=%s\">" . _('Editar Tasas') . "</a></td>
				<td><a href=\"%s&SelectedTaxAuthID=%s\">" . _('Editar') . "</a></td>
				<td><a href=\"%s&SelectedTaxAuthID=%s&delete=yes\">" . _('Eliminar') . '</a></td>
			</tr>',
			$myrow[0],
			$myrow[1],
			$myrow[3],
			$myrow[2],
			$myrow[5],
			$myrow[4],
			$myrow[6],
			$myrow[7],
			$myrow[8],
			$myrow[9],
			$rootpath . '/TaxAuthorityRates.php?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0]);

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</table></CENTER><p>';
}



if (isset($SelectedTaxAuthID)) {
	echo "<Center><a href='" .  $_SERVER['PHP_SELF'] . '?' . SID ."'>" . _('Listar todos los registros de Autoridad de Impuestos') . '</a></Center>';
 }


echo "<P><FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID .'>';

if (isset($SelectedTaxAuthID)) {
	//editing an existing tax authority

	$sql = 'SELECT taxglcode,
			purchtaxglaccount,
			taxglcodePaid,
			purchtaxglaccountPaid,
			description,
			bank,
			bankacc,
			bankacctype,
			bankswift
		FROM taxauthorities
		WHERE taxid=' . $SelectedTaxAuthID;

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['TaxGLCode']	= $myrow['taxglcode'];
	$_POST['PurchTaxGLCode']= $myrow['purchtaxglaccount'];
	$_POST['TaxGLCodePaid']	= $myrow['taxglcodePaid'];
	$_POST['PurchTaxGLCodePaid']= $myrow['purchtaxglaccountPaid'];
	$_POST['Description']	= $myrow['description'];
	$_POST['Bank']		= $myrow['bank'];
	$_POST['BankAccType']	= $myrow['bankacctype'];
	$_POST['BankAcc'] 	= $myrow['bankacc'];
	$_POST['BankSwift']	= $myrow['bankswift'];


	echo "<INPUT TYPE=HIDDEN NAME='SelectedTaxAuthID' VALUE=" . $SelectedTaxAuthID . '>';

}  //end of if $SelectedTaxAuthID only do the else when a new record is being entered


$SQL = 'SELECT accountcode,
		accountname
	FROM chartmaster,
		accountgroups
	WHERE chartmaster.group_=accountgroups.groupname
	AND accountgroups.pandl=0
	ORDER BY accountcode';
$result = DB_query($SQL,$db);

if (!isset($_POST['Description'])) {
	$_POST['Description']='';
}

echo '<CENTER><TABLE>
<TR><TD>' . _('Descripcion de Autoridad') . ":</TD>
<TD><input type=Text name='Description' SIZE=21 MAXLENGTH=20 value='" . $_POST['Description'] . "'></TD></TR>";


echo '<TR><TD>' . _('Cuenta de Impuesto por Pagar') . ':</TD>
	<TD><SELECT name=PurchTaxGLCode>';

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['PurchTaxGLCode']) and $myrow['accountcode']==$_POST['PurchTaxGLCode']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

echo '</SELECT></TD></TR>';

DB_data_seek($result,0);


echo '<TR><TD>' . _('Cuenta de Impuesto Facturado') . ':</TD>
	<TD><SELECT name=TaxGLCode>';


while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['TaxGLCode']) and $myrow['accountcode']==$_POST['TaxGLCode']) {
		echo "<OPTION SELECTED VALUE='";
	} else {
		echo "<OPTION VALUE='";
	}
	echo $myrow['accountcode'] . "'>" . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

if (!isset($_POST['Bank'])) {
	$_POST['Bank']='';
}
if (!isset($_POST['BankAccType'])) {
	$_POST['BankAccType']='';
}
if (!isset($_POST['BankAcc'])) {
	$_POST['BankAcc']='';
}
if (!isset($_POST['BankSwift'])) {
	$_POST['BankSwift']='';
}

echo '</SELECT></TD></TR>';


echo '<TR><TD>' . _('Cuenta de Impuesto Pagado') . ':</TD>
	<TD><SELECT name=PurchTaxGLCodePaid>';

DB_data_seek($result,0);

while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['PurchTaxGLCodePaid']) and $myrow['accountcode']==$_POST['PurchTaxGLCodePaid']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

echo '</SELECT></TD></TR>';

DB_data_seek($result,0);


echo '<TR><TD>' . _('Cuenta de Impuesto Cobrado') . ':</TD>
	<TD><SELECT name=TaxGLCodePaid>';


while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['TaxGLCodePaid']) and $myrow['accountcode']==$_POST['TaxGLCodePaid']) {
		echo "<OPTION SELECTED VALUE='";
	} else {
		echo "<OPTION VALUE='";
	}
	echo $myrow['accountcode'] . "'>" . $myrow['accountname'] . ' ('.$myrow['accountcode'].')';

} //end while loop

echo '</SELECT></TD></TR>';


echo '<TR><TD>' . _('Nombre del Banco') . ':</TD>';
echo '<TD><input type=Text name="Bank" SIZE=41 MAXLENGTH=40 value="' . $_POST['Bank'] . '"></TD></TR>';
echo '<TR><TD>' . _('Tipo de Cuenta') . ':</TD>';
echo '<TD><input type=Text name="BankAccType" SIZE=15 MAXLENGTH=20 value="' . $_POST['BankAccType'] . '"></TD></TR>';
echo '<TR><TD>' . _('Numero de Cuenta') . ':</TD>';
echo '<TD><input type=Text name="BankAcc" SIZE=21 MAXLENGTH=20 value="' . $_POST['BankAcc'] . '"></TD></TR>';
echo '<TR><TD>' . _('Swift No') . ':</TD>';
echo '<TD><input type=Text name="BankSwift" SIZE=15 MAXLENGTH=14 value="' . $_POST['BankSwift'] . '"></TD></TR>';

echo '</TABLE>';

echo '<input type=submit name=submit value=' . _('Procesa Informacion') . '></CENTER></FORM>';

include('includes/footer.inc');

?>
