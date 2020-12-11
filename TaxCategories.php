<?php
/* $Revision: 1.7 $ */
/*cambios
1.- se agrego el include('includes/SecurityFunctions.inc');*/
/*
 * AHA
* 7-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/
$PageSecurity = 15;

include('includes/session.inc');

$title = _('Tax Categories');

include('includes/header.inc');
$funcion=99;
include('includes/SecurityFunctions.inc');

if ( isset($_GET['SelectedTaxCategory']) )
	$SelectedTaxCategory = $_GET['SelectedTaxCategory'];
elseif (isset($_POST['SelectedTaxCategory']))
	$SelectedTaxCategory = $_POST['SelectedTaxCategory'];

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['TaxCategoryName'],'&')>0 OR strpos($_POST['TaxCategoryName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('El nombre de la categoria fiscal no puede contener el caracter') . " '&' " . _('o el car‡cter') ." '",'error');
	}
	if (trim($_POST['TaxCategoryName']) == '') {
		$InputError = 1;
		prnMsg( _('El nombre de la categoria de impuestos no puede estar vac’o'), 'error');
	}

	if ($_POST['SelectedTaxCategory']!='' AND $InputError !=1) {

		/*SelectedTaxCategory could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM taxcategories
				WHERE taxcatid <> " . $SelectedTaxCategory ."
				AND taxcatname ".LIKE." '" . $_POST['TaxCategoryName'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('La categoria de impuestos no se puede cambiar porque ya existe otro con el mismo nombre .'),'error');
		} else {
			// Get the old name and check that the record still exists
			if(isset($_POST['flagdiot']) and $_POST['flagdiot']=='on'){
				$_POST['flagdiot']=1;
			}else{
				$_POST['flagdiot']=0;
			}
			$sql = "SELECT taxcatname FROM taxcategories
				WHERE taxcatid = " . $SelectedTaxCategory;
			$result = DB_query($sql,$db);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldTaxCategoryName = $myrow[0];
				$sql = "UPDATE taxcategories
					SET taxcatname='" . $_POST['TaxCategoryName'] . "',
							flagdiot='" . $_POST['flagdiot'] . "'
					WHERE taxcatname ".LIKE." '".$OldTaxCategoryName."'";
				$ErrMsg = _('La categoria de impuestos no se pudo actualizar');
				$result = DB_query($sql,$db,$ErrMsg);
			} else {
				$InputError = 1;
				prnMsg( _('La categoria de impuestos ya no existe'),'error');
			}
		}
		$msg = _('El nombre de la categoria de impuestos cambio');
	} elseif ($InputError !=1) {
		/*SelectedTaxCategory is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM taxcategories
				WHERE taxcatname " .LIKE. " '".$_POST['TaxCategoryName'] ."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('La categoria de impuestos no se puede crear porque ya existe otro con el mismo nombre'),'error');
		} else {
			if(isset($_POST['flagdiot']) and $_POST['flagdiot']=='on'){
				$_POST['flagdiot']=1;
			}else{
				$_POST['flagdiot']=0;
			}
			$result = DB_Txn_Begin($db);
			$sql = "INSERT INTO taxcategories (
						taxcatname,flagdiot )
				VALUES (
					'" . $_POST['TaxCategoryName'] ."',
					'".$_POST['flagdiot']."'		
					)";
			$ErrMsg = _('La nueva categoria de impuestos no se puede a–adir');
			$result = DB_query($sql,$db,$ErrMsg,true);

			$LastTaxCatID = DB_Last_Insert_ID($db, 'taxcategories','taxcatid');

			$sql = 'INSERT INTO taxauthrates (taxauthority,
					dispatchtaxprovince,
					
					taxcatid)
				SELECT taxauthorities.taxid,
 					taxprovinces.taxprovinceid,
					' . $LastTaxCatID . '
				FROM taxauthorities CROSS JOIN taxprovinces';
			$result = DB_query($sql,$db,$ErrMsg,true);

			$result = DB_Txn_Commit($db);
		}
		$msg = _('Se a–adio una Nueva categoria de impuestos');
	}

	if ($InputError!=1){
		prnMsg($msg,'success');
	}
	unset ($SelectedTaxCategory);
	unset ($_POST['SelectedTaxCategory']);
	unset ($_POST['TaxCategoryName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the tax category the ID is just a secure way to find the tax category
	$sql = "SELECT taxcatname FROM taxcategories
		WHERE taxcatid = " . $SelectedTaxCategory;
	$result = DB_query($sql,$db);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( _('No se puede eliminar esta categoria de impuestos debido a que ya no existe'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldTaxCategoryName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM stockmaster WHERE taxcatid ".LIKE." '" . $OldTaxCategoryName . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se puede eliminar esta categoria de impuestos, porque los articulos del inventario se han creado con esta categoria de impuestos'),'warn');
			echo '<br>' . _('Hay') . ' ' . $myrow[0] . ' ' . _('los articulos del inventario que se refieran a esta categoria de impuestos') . '</font>';
		} else {
			$sql = 'DELETE FROM taxauthrates WHERE taxcatid = ' . $SelectedTaxCategory;
			$result = DB_query($sql,$db);
			$sql = 'DELETE FROM taxcategories WHERE taxcatid = ' .$SelectedTaxCategory;;
			$result = DB_query($sql,$db);
			prnMsg( $OldTaxCategoryName . ' ' . _('se ha suprimido la categoria de impuestos y los tipos impositivos establecidos para ello'),'success');
		}
	} //end if
	unset ($SelectedTaxCategory);
	unset ($_GET['SelectedTaxCategory']);
	unset($_GET['delete']);
	unset ($_POST['SelectedTaxCategory']);
	unset ($_POST['TaxCategoryName']);
}

 if (!isset($SelectedTaxCategory)) {

/* An tax category could be posted when one has been edited and is being updated
  or GOT when selected for modification
  SelectedTaxCategory will exist because it was sent with the page in a GET .
  If its the first time the page has been displayed with no parameters
  then none of the above are true and the list of account groups will be displayed with
  links to delete or edit each. These will call the same page again and allow update/input
  or deletion of the records*/

	$sql = "SELECT taxcatid,
			taxcatname
			FROM taxcategories
			ORDER BY taxcatid";

	$ErrMsg = _('No se pudo obtener las categorias de impuestos porque');
	$result = DB_query($sql,$db,$ErrMsg);

	echo "<table>
		<tr>
		<th>" . _('Tax Categories') . "</th>
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

		echo '<td>' . $myrow[1] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedTaxCategory=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedTaxCategory=' . $myrow[0] . '&delete=1">' . _('Delete') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($SelectedTaxCategory)) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Review Tax Categories') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($SelectedTaxCategory)) {
		//editing an existing section

		$sql = "SELECT taxcatid,
				taxcatname,flagdiot
				FROM taxcategories
				WHERE taxcatid=" . $SelectedTaxCategory;

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('No se pudo recuperar la categoria de impuestos solicitada, por favor, intentalo de nuevo.'),'warn');
			unset($SelectedTaxCategory);
		} else {
			$myrow = DB_fetch_array($result);
			$_POST['flagdiot']= $myrow['flagdiot'];
			$_POST['TaxCategoryName']  = $myrow['taxcatname'];

			echo "<input type=hidden name='SelectedTaxCategory' VALUE='" . $myrow['taxcatid'] . "'>";
			echo "<table>";
		}

	}  else {
		$_POST['TaxCategoryName']='';
		echo "<table>";
	}
	echo "<tr>
		<td>" . _('Tax Category Name') . ':' . "</td>
		<td><input type='Text' name='TaxCategoryName' size=30 maxlength=30 value='" . $_POST['TaxCategoryName'] . "'></td>
		</tr>";
	
	echo "<tr><td>" . _('Aplica DIOT') . ":</td>";
	if ($_POST['flagdiot']==1) {
		print "<td>
        <input type='checkbox' checked='checked'  name='flagdiot'></td></tr>";
	} else {
		print "<td><input type='checkbox' name='flagdiot'></td></tr>";
	}
	//echo '</tr>';
	
	echo '</table>';

	echo '<div class="centre"><input type=Submit name=submit value=' . _('Enter Information') . '></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>