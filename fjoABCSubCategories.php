<?php
/* 31/MAYO/2012 -desarrollo- NUEVA FUNCION */

$PageSecurity = 10;
include('includes/session.inc');
$title = _('Mantenimiento de Sub Categorias');
include('includes/header.inc');
$funcion=2016;
include('includes/SecurityFunctions.inc');

if (isset($Errors)) {
	unset($Errors);
}
	
$Errors = array();

if (isset($_GET['legalidsearch'])) {
	$_POST['legalidsearch'] = $_GET['legalidsearch'];
} elseif (!isset($_POST['legalidsearch'])) {
	$_POST['legalidsearch'] = '0';
}

if (isset($_GET['catidsearch'])) {
	$_POST['catidsearch'] = $_GET['catidsearch'];
} elseif (!isset($_POST['catidsearch'])) {
	$_POST['catidsearch'] = '*';
}

if (isset($_GET['areacode'])) {
	$_POST['areacode'] = $_GET['areacode'];
} elseif (!isset($_POST['areacode'])) {
	$_POST['areacode'] = '0';
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
echo '<table border=0 style="margin:auto;">';

	echo '<tr>';
	
	/************************************/
	/* SELECCION DEL RAZON SOCIAL */
	
	echo '<tr><td style="text-align:right"><b>'._('X Empresa:').'</b></td><td>
	
	<select name="legalidsearch">';	
	///Imprime las razones sociales
	$SQL = "SELECT regions.regioncode,regions.name";
	$SQL = $SQL .	" FROM regions";
	
	$result=DB_query($SQL,$db);
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='0'>Selecciona una Empresa..</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalidsearch']) and $_POST['legalidsearch']==$myrow["regioncode"]){
			echo '<option selected value=' . $myrow['regioncode'] . '>' . $myrow['regioncode'].' - ' .$myrow['name']. '</option>';
		} else {
			echo '<option value=' . $myrow['regioncode'] . '>' . $myrow['regioncode'].' - ' .$myrow['name']. '</option>';
		}
	}
	
	echo '</select>
		</td></tr>';
	
	/************************************/
	/* SELECCION DEL PROYECTO           */
	
	echo '<tr><td style="text-align:right"><b>'._('X Proyecto:').'</b></td><td>';	
	///Imprime las razones sociales
	$SQL = "SELECT areas.areacode,areas.areadescription";
	$SQL = $SQL .	" FROM areas
			WHERE (areas.regioncode = '".$_POST['legalidsearch']."' OR '0'='".$_POST['legalidsearch']."')";
	
	$result=DB_query($SQL,$db);
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<select name='areacode'>
		<option selected value='0'>Todos los proyectos..</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['areacode']) and $_POST['areacode']==$myrow["areacode"]){
			echo '<option selected value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
		} else {
			echo '<option value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
		}
	}
	
	
	echo '</select></td></tr>';
	/************************************/
	
		echo '<tr>';
	
	/************************************/
	/* SELECCION categoria */
	

		$SQL = "SELECT cat_id, cat_name";
		$SQL = $SQL .	" FROM fjoCategory";
		$SQL = $SQL .	" WHERE (legalid = '".$_POST['legalidsearch']."' OR '" . $_POST['legalidsearch'] . "' = '0')
					AND (areacode = '".$_POST['areacode']."' OR '" . $_POST['areacode'] . "' = '0')";
		$SQL = $SQL .	" ORDER BY fjoCategory.order, cat_name";
		
		//echo $SQL;
		$result=DB_query($SQL,$db);
	echo '<tr><td style="text-align:right"><b>'._('X Categoria:').'</b></td><td><select name="catidsearch">';	
	///Imprime las razones sociales
	
	
	
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='*'>Todas las categorias...</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['catidsearch']) and $_POST['catidsearch']==$myrow["cat_id"]){
			echo '<option selected value=' . $myrow['cat_id'] . '>'  .$myrow['cat_name'];
		} else {
			echo '<option value=' . $myrow['cat_id'] . '>' . $myrow['cat_name'];
		}
	}
	echo '</select><input type=submit name=buscar value=' . _('Buscar') . '></td></tr>';
	/************************************/
	
	echo '</table>';
	
	echo "<div class='centre'><hr width=50%></div><br>";

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;
	$i=1;

	if (strpos($_POST['CategoryName'],'&')>0 OR strpos($_POST['CategoryName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('El Nombre de la sub categoria no puede contener carateres:') . " '&' " . _(' o caracteres') ." '",'error');
		$Errors[$i] = 'CategoryName';
		$i++;		
	} 
	if (strlen($_POST['CategoryName'])==0) {
		$InputError = 1;
		prnMsg( _('El Nombre de la sub categoria debe tener por lo menos un caracter') ,'error');
		$Errors[$i] = 'CategoryName';
		$i++;		
	}
	if ($_POST['catid']=='*') {
		$InputError = 1;
		prnMsg( _('Seleccion una categoria para la sub categoria') ,'error');
		$Errors[$i] = 'catid';
		$i++;		
	}
	
	if (isset($_POST['SelectedCategoryID']) and $_POST['SelectedCategoryID']!='' AND $InputError !=1) {

		/*SelectedSectionID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE fjoSubCategory
				SET subcat_name ='" . $_POST['CategoryName'] . "', 
				cat_id =". $_POST['catid'] .",
				fjoSubCategory.order = '". $_POST['CatOrden'] ."'
				WHERE subcat_id = " . $_POST['SelectedCategoryID'];

		$msg = _('Registro Actualizado');
	} elseif ($InputError !=1) {

	/*SelectedSectionID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account section form */
		$sql= "SELECT legalid FROM fjoCategory WHERE cat_id=" . $_POST['catid'] . "";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$legalidcat = $myrow[0];
		
		$sql = "INSERT INTO fjoSubCategory (
					subcat_id,
					cat_id,
					subcat_name,
					legalid,
					fjoSubCategory.order,
					accountcode)
			VALUES (null,
				'" . $_POST['catid'] . "',
				'" . $_POST['CategoryName'] . "',
				'" . $legalidcat ."',
				'" . $_POST['CatOrden'] ."',
				'" . $_POST['areacodenew'] ."'
				)";
		$msg = _('Registro Insertado');
		
		//echo $sql;
		
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'Exito !');
		unset ($_POST['SelectedCategoryID']);
		unset ($_POST['catid']);
		unset ($_POST['CategoryID']);
		unset ($_POST['CatOrden']);
		unset ($_POST['CategoryName']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'accountgroups'
	$sql= "SELECT COUNT(*) FROM Movimientos WHERE TipoMovimientoId=" . $_GET['SelectedCategoryID'] . "";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg( _('No puedo borrar esta sub categoria pues existen registros en movimientos del flujo que lo utilizan !'),'warn');
	} else {
		//Fetch section name
		$sql = "SELECT subcat_name FROM fjoSubCategory WHERE subcat_id='".$_GET['SelectedCategoryID'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$CategoryName = $myrow[0];
		
		$sql="DELETE FROM fjoSubCategory WHERE subcat_id='" . $_GET['SelectedCategoryID'] . "'";
		$result = DB_query($sql,$db);
		prnMsg( $CategoryName . ' ' . _(' sub categoria ha sido borrada...') . '!','success');

	} //end if account group used in GL accounts
	unset ($_GET['SelectedCategoryID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedCategoryID']);
	unset ($_POST['CategoryID']);
	unset ($_POST['CatOrden']);
	unset ($_POST['catid']);
	unset ($_POST['CategoryName']);
}

if (!isset($_GET['SelectedCategoryID']) OR !isset($_POST['SelectedCategoryID'])) {

	$sql = "SELECT fjoSubCategory.subcat_id, fjoSubCategory.subcat_name, regions.name as legalname, 
			fjoSubCategory.order, fjoCategory.cat_name, areas.areadescription, regions.regioncode, areas.areacode
		FROM fjoSubCategory JOIN areas ON fjoSubCategory.accountcode = areas.areacode
			JOIN regions ON areas.regioncode = regions.regioncode
			JOIN fjoCategory ON fjoSubCategory.cat_id = fjoCategory.cat_id ";
	$sql = $sql." WHERE 1=1 ";
		
	if (isset($_POST['legalidsearch']) and $_POST['legalidsearch'] != '0')
		$sql = $sql." and fjoSubCategory.legalid = '".$_POST['legalidsearch']."'";
		
	if (isset($_POST['areacode']) and $_POST['areacode'] != '0')
		$sql = $sql." and fjoSubCategory.accountcode = '".$_POST['areacode']."'";
		
	if (isset($_POST['catidsearch']) and $_POST['catidsearch'] != '*')
		$sql = $sql." and fjoSubCategory.cat_id = '".$_POST['catidsearch']."'";
		
	$sql = $sql." ORDER BY fjoSubCategory.order, subcat_name ";

	//echo $sql;
	$ErrMsg = _('No se pudo encontrar registros de sub categorias por');
	$result = DB_query($sql,$db,$ErrMsg);
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';

	echo "<table style='margin:auto;'>
		<tr>
		<th>" . _('ID') . "</th>
		<th>" . _('Orden') . "</th>
		<th>" . _('Nombre sub categoria') . "</th>
		<th>" . _('Categoria') . "</th>
		<th>" . _('Empresa') . "</th>
		<th>" . _('Proyecto') . "</th>
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

		echo '<td nowrap>' . $myrow[0] . '</td><td nowrap>' . $myrow[3] . '</td><td nowrap>' . $myrow[1] . '</td><td nowrap>' . $myrow[4] . '</td><td nowrap>' . $myrow[5] . '</td>
					<td nowrap>' . $myrow[2] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedCategoryID=' . $myrow[0] . '&areacode=' . $myrow[7] . '&legalidsearch=' . $myrow[6] . '&catidsearch=' . $_POST['catidsearch'] . '">' . _('Modificar') . '</a></td>';
		
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedCategoryID=' . $myrow[0] . '&delete=1&areacode=' . $myrow[7] . '&legalidsearch=' . $myrow[6] . '&catidsearch=' . $_POST['catidsearch'] . '">' . _('Eliminar') .'</a></td>';
		

	} //END WHILE LIST LOOP
	echo '</table><p>';
} //end of ifs and buts!


if (isset($_POST['SelectedCategoryID']) or isset($_GET['SelectedCategoryID'])) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Revisar sub categorias') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {
	
	echo "<form method='post' name='AccountSections' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($_GET['SelectedCategoryID'])) {
		//editing an existing section

		$sql = "SELECT *
			FROM fjoSubCategory
			WHERE subcat_id='" . $_GET['SelectedCategoryID'] ."'";

		$result = DB_query($sql, $db);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('No pude recuperar la sub categoria seleccionada.'),'warn');
			unset($_GET['SelectedCategoryID']);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['CategoryID'] = $myrow['subcat_id'];
			$_POST['CategoryName']  = $myrow['subcat_name'];
			$_POST['legalid'] = $myrow['legalid'];
			$_POST['CatOrden'] = $myrow['order'];
			$_POST['catid'] = $myrow['cat_id'];
			
			echo "<input type=hidden name='SelectedCategoryID' value='" . $_GET['SelectedCategoryID'] . "'>";
			echo "<input type=hidden name='legalid' value='" . $myrow['legalid'] . "'>";
			echo "<input type=hidden name='areacodenew' value='" . $myrow['accountcode'] . "'>";
			
			echo "<table style='margin:auto;'>
			<td>" . _('ID sub categoria') . ':' . "</td>
			<td>" . $_GET['SelectedCategoryID'] . "</td>";
		}

	}  else {

		if (!isset($_POST['SelectedCategoryID'])){
			$_POST['SelectedCategoriID']='';
		}
		if (!isset($_POST['CategoryID'])){
			$_POST['CategoryID']='';
		}
		if (!isset($_POST['CatOrden'])){
			$_POST['CatOrden']='1';
		}
		if (!isset($_POST['CategoryName'])) {
			$_POST['CategoryName']='';
			$_POST['legalid'] = '';
			$_POST['catid'] = '';
		}
		
		echo "<input type=hidden name='areacodenew' value='" . $_POST['areacode'] . "'>";
		
		//echo "areacodenew:". $_POST['areacode']."<br>";
		echo "<table style='margin:auto;'>";
	}
	
	if (isset($_POST['areacode']) && $_POST['areacode'] != '0') {
		echo "<tr><td>" . _('Nombre') . ':' . '</td>
			<td><input tabindex="2" ' . (in_array('CategoryName',$Errors) ?  'class="inputerror"' : '' ) ." type='text' name='CategoryName' size=60 maxlength=250 value='" . $_POST['CategoryName'] . "'></td>
			</tr>";
			
		
		echo '<tr>';
		
		/************************************/
		/* SELECCION categoria */
		
		echo '<tr><td style="text-align:right"><b>'._('Categoria:').'</b></td><td><select name="catid">';	
		///Imprime las razones sociales
		$SQL = "SELECT cat_id, cat_name";
		$SQL = $SQL .	" FROM fjoCategory";
		$SQL = $SQL .	" WHERE legalid = '".$_POST['legalidsearch']."'";		
		$SQL = $SQL .	" ORDER BY fjoCategory.order, cat_name";		
	
		$result=DB_query($SQL,$db);
		
		/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['catid']) and $_POST['catid']==$myrow["cat_id"]){
				echo '<option selected value=' . $myrow['cat_id'] . '>'  .$myrow['cat_name'];
			} else {
				echo '<option value=' . $myrow['cat_id'] . '>' . $myrow['cat_name'];
			}
		}
		echo '</select></td></tr>';
		/************************************/
		
		echo "<tr><td>" . _('Orden') . ':' . '</td>
			<td><input tabindex="2" ' . (in_array('CatOrden',$Errors) ?  'class="inputerror"' : '' ) ." type='text' name='CatOrden' size=3 maxlength=10 value='" . $_POST['CatOrden'] . "'></td>
			</tr>";
			
		echo '<tr><td></td><td colspan=2><input tabindex="3" type=Submit name=submit value="' . _('Procesar Sub Categoria') . '"></td></tr>';
	} else {
		echo "<tr><td>ALTA DE SUB CATEGORIA:</td>
			<td>Es Necesario seleccionar un Proyecto para dar de alta sub categorias...</td>
			</tr>";
	}
	
	echo '</table>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>