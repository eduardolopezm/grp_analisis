<?php
/* $Revision: 1.6 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');*/
/*
* AHA
* 7-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/
$PageSecurity = 15;

include('includes/session.inc');
$title = _('Tipos de clientes') . ' / ' . _('Mantenimiento');
include('includes/header.inc');
$funcion=118;
include('includes/SecurityFunctions.inc');


if (isset($_POST['SelectedType'])){
	$SelectedType = strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])){
	$SelectedType = strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Tipos de Clientes') . '" alt="">' . _('Configuraciï¿½n de Tipos de Clientes') . '</p>';
//echo '<div class="page_help_text">' . _('Agregar/Modificar/Eliminar Tipos de Clientes') . '</div><br>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (strlen($_POST['typename']) >100) {
		$InputError = 1;
		echo prnMsg(_('El tipo de cliente debe ser menor de 100 caracteres'),'error');
		//echo prnMsg(_('The customer type name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'CustomerType';
		$i++;
	}

	if (strlen($_POST['typename'])==0) {
		$InputError = 1;
		echo prnMsg(_('El tipo de cliente debe ser mayor a un caracter'),'error');
		//echo prnMsg(_('The customer type name description must contain at least one character'),'error');
		$Errors[$i] = 'CustomerType';
		$i++;
	}
//
	if($_POST['submit'] == "Agregar"){
		$checksql = "SELECT count(*)
		     FROM debtortype
		     WHERE typename = '" . $_POST['typename'] . "'";
		$checkresult=DB_query($checksql, $db);
		$checkrow=DB_fetch_row($checkresult);
		if ($checkrow[0]>0) {
			$InputError = 1;
			echo prnMsg(_('Ya existe ese tipo de cliente').' '.$_POST['typename'],'error');
			//echo prnMsg(_('You already have a customer type called').' '.$_POST['typename'],'error');
			$Errors[$i] = 'CustomerName';
			$i++;
		}
	}
	
	
	if ($_POST['submit'] == "Actualizar" AND $InputError !=1) {

		$sql = "UPDATE debtortype
			SET typename = '" . $_POST['typename'] . "',
				nexttypeid = '".$_POST['nexttypeid']."'
			WHERE typeid = '$SelectedType'";

		$msg = _('El tipo de cliente') . ' ' . $SelectedType . ' ' .  _('a sido actualizado');
		//$msg = _('The customer type') . ' ' . $SelectedType . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM debtortype
			     WHERE typeid = '" . $_POST['typeid'] . "'";

		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('El tipo de cliente ') . $_POST['typeid'] . _(' ya existe.'),'error');
			//prnMsg( _('The customer type ') . $_POST['typeid'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO debtortype
						(typename)
					VALUES ('" . $_POST['typename'] . "')";

			$msg = _('El tipo de cliente') . ' ' . $_POST["typename"] .  ' ' . _('ha sido creado');
			//$msg = _('Customer type') . ' ' . $_POST["typename"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(typeid)
			     FROM debtortype";
			$result = DB_query($checkSql, $db);
			$flag = "Insert";
			$row = DB_fetch_row($result);
			
			

		}
	}
//
	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites//
		$result = DB_query($sql,$db);
		$tipoid = DB_Last_Insert_ID($db, 'debtortype' , 'typeid');
		if($flag == "Insert"){
			$sql = "UPDATE debtortype
					SET nexttypeid = '".$tipoid."'
					WHERE typeid = '".$tipoid."'";
			$result = DB_query($sql, $db);
			
			$sql = "INSERT INTO sec_debtorxuser (typeid, 
												userid)
					VALUES('".$tipoid."', 
							'".$_SESSION['UserID']."')";
			$result = DB_query($sql, $db);
		}
		

	// Fetch the default price list.
		$sql = "SELECT confvalue
					FROM config
					WHERE confname='DefaultCustomerType'";
		$result = DB_query($sql,$db);
		$CustomerTypeRow = DB_fetch_row($result);
		$DefaultCustomerType = $CustomerTypeRow[0];

	// Does it exist
		$checkSql = "SELECT count(*)
			     FROM debtortype
			     WHERE typeid = '" . $DefaultCustomerType . "'";
		$checkresult = DB_query($checkSql,$db);
		$checkrow = DB_fetch_row($checkresult);

	// If it doesnt then update config with newly created one.
		if ($checkrow[0] == 0) {
			$sql = "UPDATE config
					SET confvalue='" . $_POST['typeid'] . "'
					WHERE confname='DefaultCustomerType'";
			$result = DB_query($sql,$db);
			$_SESSION['DefaultCustomerType'] = $_POST['typeid'];
		}

		prnMsg($msg,'success');

		unset($SelectedType);
		unset($_POST['typeid']);
		unset($_POST['typename']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions

	$sql= "SELECT COUNT(*)
	       FROM debtortrans
	       WHERE debtortrans.type='$SelectedType'";

	$ErrMsg = _('El nï¿½mero de transacciones usadas para este tipo de cliente podrian no ser recuperadas');
	//$ErrMsg = _('The number of transactions using this customer type could not be retrieved');
	$result = DB_query($sql,$db,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('No puede eliminar este tipo porque tiene transacciones en uso') . '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('transacciones usadas por este tipo'),'error');
		//prnMsg(_('Cannot delete this type because customer transactions have been created using this type') . '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions using this type'),'error');

	} else {

		$sql = "SELECT COUNT(*) FROM debtorsmaster WHERE typeid='$SelectedType'";

		$ErrMsg = _('El numero de transacciones usadas por este tipo podrï¿½an no ser recuperadas porque');
		//$ErrMsg = _('The number of transactions using this Type record could not be retrieved because');
		$result = DB_query($sql,$db,$ErrMsg);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg (_('No puede eliminar este tipo, porque los clientes estan configuragos para hacer uso de el') . '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('Clientes con este tipo'));
			//prnMsg (_('Cannot delete this type because customers are currently set up to use this type') . '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('customers with this type code'));
		} else {

			$sql="DELETE FROM debtortype WHERE typeid='$SelectedType'";
			$ErrMsg = _('El registro no pudo ser eliminada porque');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('El tipo de cliente') . $SelectedType  . ' ' . _('a sido eliminado') ,'success');
			//prnMsg(_('Customer type') . $SelectedType  . ' ' . _('has been deleted') ,'success');

			unset ($SelectedType);
			unset($_GET['delete']);

		}
	} //end if sales type used in debtor transactions or in customers set up
}

if (!isset($SelectedType)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = 'SELECT typeid, typename FROM debtortype';
	$result = DB_query($sql,$db);

	echo '<br><table border=1>';
	echo "<tr>
		<th>" . _('ID') . "</th>
		<th>" . _('Tipo') . "</th>
		<th>" . _('Mostrar en <br>Prospectos') . "</th>
		<th>" . _('') . "</th>
		<th>" . _('') . "</th>
		</tr>";

$k=0; //row colour counter

while ($myrow = DB_fetch_row($result)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	$show = "NO";
	if ($myrow[2]==1)
		$show="SI";

	printf("
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td><a href='%sSelectedType=%s'>" . _('Modificar') . "</td>
		<td><a href='%sSelectedType=%s&delete=yes' onclick=\"return confirm('" . _('Estas seguro que quieres eliminar este tipo de cliente?') . "');\">" . _('Eliminar') . "</td>
		</tr>",
		$myrow[0],
		$myrow[1],
		$show,
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow[0],
		$_SERVER['PHP_SELF'] . '?' . SID, $myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre"><p><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Mostrar todos los tipos de clientes') . '</a></div><p>';
}
if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<p><table border=1>'; //Main table
	echo '<td><table>'; // First column


	// The user wish to EDIT an existing type
	if ( isset($SelectedType) AND $SelectedType!='' )
	{

		$sql = "SELECT typeid,
			       typename
		        FROM debtortype
		        WHERE typeid='$SelectedType'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['typeid'] = $myrow['typeid'];
		$_POST['typename']  = $myrow['typename'];
		$_POST['nexttypeid'] = $myrow['nexttypeid'];

		echo "<input type=hidden name='SelectedType' VALUE=" . $SelectedType . ">";
		echo "<input type=hidden name='typeid' VALUE=" . $_POST['typeid'] . ">";
		echo "<table> <tr><td>";

		// We dont allow the user to change an existing type code

		echo 'Type ID: ' . $_POST['typeid'] . '</td></tr>';

	} else 	{

		// This is a new type so the user may volunteer a type code//
		echo "<table>";

	}
		

	if (!isset($_POST['typename'])) {
		$_POST['typename']='';
	}
	echo "<tr><td>" . _('Tipo') . ":</td><td><input type='Text' name='typename' value='" . $_POST['typename'] . "'></td></tr>";
	if(isset($_POST['typeid'])){
		echo '<tr>';
		echo "<td>" . _('Sig. tipo') . ":</td>";
		$sqlo = "SELECT debtortype.typeid,
					debtortype.typename
					FROM debtortype
 					INNER JOIN (SELECT nexttypeid
 			 					FROM debtortype
 			 									) AS tipo ON tipo.nexttypeid = debtortype.typeid ";
		$resulto = DB_query($sqlo, $db);
		echo "<td><select name=nexttypeid>";
		while ($myrowo = DB_fetch_array($resulto)){
			if($_POST['nexttypeid'] == $myrowo['nexttypeid']){
				echo "<option selected value='".$myrowo['typeid']."'>".$myrowo['typename']."</option>";	
			}else{
				echo "<option value='".$myrowo['typeid']."'>".$myrowo['typename']."</option>";
			}
		}
		echo "</select></td></tr>";
	}
   	echo '</table>'; // close table in first column
   	echo '</td></tr></table>'; // close main table
   	if(isset($_POST['typeid'])){
   		echo '<p><div class="centre"><input type=submit name=submit VALUE="' . _('Actualizar') . '"></div>';
   	}else{
   		echo '<p><div class="centre"><input type=submit name=submit VALUE="' . _('Agregar') . '"></div>';
   	}
	

	echo '</form>';

} // end if user wish to delete


include('includes/footer.inc');
?>
