<?php
/* $Revision: 1.10 $ */

$PageSecurity=5;

include('includes/session.inc');

$title = _('Contactos de Proveedores');

include('includes/header.inc');


if (isset($_GET['SupplierID'])){
	$SupplierID = $_GET['SupplierID'];
} elseif (isset($_POST['SupplierID'])){
	$SupplierID = $_POST['SupplierID'];
}
// Tabla para el titulo de la pagina
echo "<table align='center' border=0 style='background-color:#ffff;' width=100% nowrap>";
echo '	<tr>
    		<td class="fecha_titulo">
    			<img src="images/i_contactos_30.png">' . $title . '<br>
    		</td>';
echo '	</tr>
	  </table><br>';
echo "<div align=center><a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'><img src='images/b_regresar_25.png' title=" .'("REGRESAR A PROVEEDORES")'."' ></a></div><br>";

if (!isset($SupplierID)) {


	echo '<p><p>';
	prnMsg(_('Esta p&aacute;gina debe ser llamada con el c&aocute;digo del proveedor del proveedor para el que desee editar los contactos') . '<br>' . _('Cuando la p&aacute;gina se llama desde dentro del sistema este siempre ser&aacute; el caso') .
			'<br>' . _('Seleccione un proveedor de primera, a continuaci&oacute;n, seleccione el enlace para añadir / editar / borrar contactos'),'info');

	echo '<input type="text" id="SupplierBusqueda"> ';
	include('includes/footer.inc');
	echo '<script type="text/javascript" src="javascripts/suppliers.js"></script>';
	exit;
}

if (isset($_GET['SelectedContact'])){
	$SelectedContact = $_GET['SelectedContact'];
} elseif (isset($_POST['SelectedContact'])){
	$SelectedContact = $_POST['SelectedContact'];
}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Contact']) == 0) {
		$InputError = 1;
		prnMsg(_('El nombre del contacto es obligatorio...'),'error');
	}


	if (isset($SelectedContact) AND $InputError != 1) {

		/*SelectedContact could also exist if submit had not been clicked this code would not run in this case 'cos submit is false of course see the delete code below*/

		$sql = "UPDATE suppliercontacts SET position='" . $_POST['Position'] . "', 
							tel='" . $_POST['Tel'] . "', 
							fax='" . $_POST['Fax'] . "', 
							email='" . $_POST['Email'] . "',
							suppcategoryid='" . $_POST['suppcategoryid'] . "', 
							mobile = '". $_POST['Mobile'] . "' 
				WHERE contact='$SelectedContact' AND supplierid='$SupplierID'";

		$msg = _('La informaci&oacute;n del contacto ha sido actualizada');

	} elseif ($InputError != 1) {

	/*Selected contact is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new supplier  contacts form */

		$sql = "INSERT INTO suppliercontacts (supplierid, 
							contact, 
							position, 
							tel, 
							fax, 
							email,
							suppcategoryid,
							mobile) 
				VALUES ('" . $SupplierID . "', 
					'" . $_POST['Contact'] . "', 
					'" . $_POST['Position'] . "', 
					'" . $_POST['Tel'] . "', 
					'" . $_POST['Fax'] . "', 
					'" . $_POST['Email'] . "',
					'" . $_POST['suppcategoryid'] . "', 
					'" . $_POST['Mobile'] . "')";

		$msg = _('El nuevo contacto ha sido registrado exit&oacute;samente...');
	}
	//run the SQL from either of the above possibilites

	$ErrMsg = _('La informaci&oacute;n del contacto no puede registrarse o actualizarse porque');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	prnMsg($msg,'success');

	unset($SelectedContact);
	unset($_POST['Contact']);
	unset($_POST['Position']);
	unset($_POST['Tel']);
	unset($_POST['Fax']);
	unset($_POST['Email']);
	unset($_POST['Mobile']);

} elseif (isset($_GET['delete'])) {

	$sql = "DELETE FROM suppliercontacts 
			WHERE contact='$SelectedContact' 
			AND supplierid = '$SupplierID'";

	$ErrMsg = _('El contacto no puede eliminarse porque');
	$DbgMsg = _('The SQL that was used but failed was');

	$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

	echo '<br>' . _('La informaci&oacute;n del contacto ha sido borrada exit&oacute;samente...') . '<p>';

}


if (!isset($SelectedContact)){

	$sql = "SELECT suppliers.suppname, 
			contact, 
			position, 
			tel, 
			suppliercontacts.fax, 
			suppliercontacts.email
		FROM suppliercontacts, 
			suppliers
		WHERE suppliercontacts.supplierid=suppliers.supplierid
		AND suppliercontacts.supplierid = '$SupplierID'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);

	if ($myrow) {
		echo '<div class="centre"><b>' . _('Contactos Registrados del Proveedor : ') . strtoupper($myrow[0])."</b></div>";
	}
	echo '<br>';
	echo "<table cellspacing=0 border=1 align='center' bordercolor=lightgray cellpadding=3>\n";
	echo "	<tr>
				<th class='titulos_principales'>" . _('Nombre') . "</th>
				<th class='titulos_principales'>" . _('Puesto') . "</th>
				<th class='titulos_principales'>" . _('Tel&eacute;fono') . "</th>
				<th class='titulos_principales'>" . _('Fax') . "</th>
				<th class='titulos_principales'>" . _('Email') ."</th>
				<th class='titulos_principales' colspan=2></th>
			</tr>\n";

	do {
		printf("<tr>
					<td class='texto_normal2'>%s</td>
					<td class='texto_normal2'>%s</td>
					<td class='numero_celda'>%s</td>
					<td class='numero_celda'>%s</td>
					<td class='texto_normal2'><a href='mailto:%s'>%s</td>
					<td class='numero_normal'><a href='%s&SupplierID=%s&SelectedContact=%s'><img src='images/lapiz_25.png'></td>
					<td class='numero_normal'><a href='%s&SupplierID=%s&SelectedContact=%s&delete=yes' onclick=\"return confirm('" . _('Est&aacute;s seguro de ELIMINAR este contacto?') . "');\"><img src='images/eliminar.png'></td>
				</tr>",
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				$myrow[5],
				$myrow[5],
				$_SERVER['PHP_SELF'] . '?' . SID,
				$SupplierID,
				$myrow[1],
				$_SERVER['PHP_SELF']. '?' . SID,
				$SupplierID,
				$myrow[1]);

	} while ($myrow = DB_fetch_row($result));

	//END WHILE LIST LOOP
}

//end of ifs and buts!

echo '</table><p>';

if (isset($SelectedContact)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "SupplierID=$SupplierID" . "'>" .
		  _('Mostrar la informaci&oacute;;n de todos los contactos de ') . ' ' . $SupplierID . '</a></div></p>';
}

if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

	if (isset($SelectedContact)) {
		//editing an existing branch

		$sql = "SELECT contact, 
				position, 
				tel, 
				fax, 
				mobile, 
				email,
				suppcategoryid
			FROM suppliercontacts
			WHERE contact='$SelectedContact'
			AND supplierid='$SupplierID'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['Contact']  = $myrow['contact'];
		$_POST['Position']  = $myrow['position'];
		$_POST['Tel']  = $myrow['tel'];
		$_POST['Fax']  = $myrow['fax'];
		$_POST['Email']  = $myrow['email'];
		$_POST['Mobile']  = $myrow['mobile'];
		$_POST['suppcategoryid']  = $myrow['suppcategoryid'];
		
		echo "<input type=hidden name='SelectedContact' VALUE='" . $_POST['Contact'] . "'>";
		echo "<input type=hidden name='Contact' VALUE='" . $_POST['Contact'] . "'>";
		
	echo '	<table align=center bgcolor="#eeeeee">
				<tr>
					<td class="texto_lista">' . _('Contacto') . ':</td>
					<td>' . $_POST['Contact'] . '</td>
				</tr>';

	} else { //end of if $SelectedContact only do the else when a new record is being entered
		if (!isset($_POST['Contact'])) {
			$_POST['Contact']='';
		}
		echo '	<table align=center bgcolor="#eeeeee">
					<tr>
						<td class="texto_lista">' . _('Nombre') . ":</td>
						<td><input type='Text' name='Contact' size=41 maxlength=40 VALUE='" . $_POST['Contact'] . "'></td></tr>";
	}
	if (!isset($_POST['Position'])) {
		$_POST['Position']='';
	}
	if (!isset($_POST['Tel'])) {
		$_POST['Tel']='';
	}
	if(!isset($_POST['Fax'])) {
		$_POST['Fax']='';
	}
	if (!isset($_POST['Mobile'])) {
		$_POST['Mobile']='';
	}
	if (!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}

	echo "<input type=hidden name='SupplierID' VALUE='" . $SupplierID . "'>
					<tr>
						<td class='texto_lista'>" . _('Puesto') . ":</td>
						<td><input type=text name='Position' size=31 maxlength=30 VALUE='" . $_POST['Position'] . "'></td>
					</tr>
					<tr>
						<td class='texto_lista'>" . _('Tel&eacute;fono') . ":</td>
						<td><input type=text name='Tel' size=31 maxlength=30 VALUE='" . $_POST['Tel'] . "'></td>
					</tr>
					<tr>
						<td class='texto_lista'>" . _('Fax') . ":</td>
						<td><input type=text name='Fax' size=31 maxlength=30 VALUE='" . $_POST['Fax'] . "'></td>
					</tr>
					<tr>
						<td class='texto_lista'>" . _('Celular') . ":</td>
						<td><input type=text name='Mobile' size=31 maxlength=30 VALUE='" . $_POST['Mobile'] . "'></td>
					</tr>";
		
	echo "			<tr>";
	
	$SQL='SELECT sto.categoryid, categorydescription';
	$SQL.=' FROM stockcategory sto, sec_stockcategory sec';
	$SQL.=' WHERE sto.categoryid=sec.categoryid';
	$SQL.=' AND userid="'.$_SESSION['UserID'].'"';
	$SQL.=' ORDER BY categorydescription';
	
	$result1 = DB_query($SQL,$db);
	if (DB_num_rows($result1) == 0) {
	    echo '<p><font size=4 color=red>' . _('Reporte de Problema') . ':</font><br>' . _('No hay categorias de inventario definidas en el sistema. favor de ir a la siguiente liga para configurarlas');
	    echo '<br><a href="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Definir Categorias de Inventario') . '</a>';
	    exit;
	}
	echo '<td class="texto_lista">'. _('Categoria de Inventario') . ':</td><td>';
		echo '<select name="suppcategoryid">';
		
		if (!isset($_POST['suppcategoryid'])) {
			$_POST['suppcategoryid'] = "";
		}
		
		if ($_POST['suppcategoryid'] == "All") {
			echo '<option selected value="All">' . _('Todas');
		} else {
			echo '<option value="All">' . _('Todas');
		}
		
		while ($myrow1 = DB_fetch_array($result1)) {
			if ($myrow1['categoryid'] == $_POST['suppcategoryid']) {
				echo '<option selected VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
			} else {
				echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
			}
		}
		
	echo '</select></td></tr>';
	
	echo "<tr><td class='texto_lista'><a href='Mailto:" . $_POST['Email'] . "'>" . _('Email') . ":</a></td>
		<td><input type=text name='Email' size=51 maxlength=50 VALUE='" . $_POST['Email'] . "'></td></tr>";
		
	echo "</table>";

	echo "<div class='centre'>
			<button type='Submit' name='submit' style='cursor:pointer; border:0; background-color:transparent;'>
        		<img src='images/guardar_net_25.png' title='REGRISTRAR'>
        	</button>";
	echo '</div></form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>
