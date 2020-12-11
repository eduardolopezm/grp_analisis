<?php
/* 31/MAYO/2012 -desarrollo- NUEVA FUNCION */
/* INICIAN CAMBIOS
 * ELABORO: JESUS GUADALUPE VARGAS MONTES
 * FECHA MODIFICO: 04-ABRIL-2013
 * CAMBIO
 * SE AGREGO LA FUNCION PARA QUE EN EL MOMENTO QUE SE INSERTE LA CATEGORIA
 * HAGA LO MISMO CON EL PERMISO DEL USUARIO EL CUAL LA HA AGREGADO
 * TERMINA CAMBIOS
 */
$PageSecurity = 10;
include('includes/session.inc');
$title = _('Mantenimiento de Categorias');
include('includes/header.inc');
$funcion=2010;
include('includes/SecurityFunctions.inc');

if (isset($Errors)) {
	unset($Errors);
}
	
$Errors = array();

if (isset($_GET['legalidsearch'])) {
	$_POST['legalidsearch'] = $_GET['legalidsearch'];
} elseif (!isset($_POST['legalidsearch'])) {
	$_POST['legalidsearch'] = '*';
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
	
	
	echo '</select>';
	
	/************************************/
	
	echo '<input type=submit name=buscar value=' . _('Buscar') . '></td></tr>';
	
	
	echo '</table>';
	
	echo "<div class='centre'><hr width=50%></div><br>";

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;
	$i=1;

	if (strpos($_POST['CategoryName'],'&')>0 OR strpos($_POST['CategoryName'],"'")>0) {
		$InputError = 1;
		prnMsg( _('El Nombre de la categoria no puede contener carateres:') . " '&' " . _(' o caracteres') ." '",'error');
		$Errors[$i] = 'CategoryName';
		$i++;		
	} 
	if (strlen($_POST['CategoryName'])==0) {
		$InputError = 1;
		prnMsg( _('El Nombre de la categoria debe tener por lo menos un caracter') ,'error');
		$Errors[$i] = 'CategoryName';
		$i++;		
	}
	if ($_POST['legalid']=='0') {
		$InputError = 1;
		prnMsg( _('Seleccion una empresa para la categoria') ,'error');
		$Errors[$i] = 'legalid';
		$i++;		
	}
	if ($_POST['newareacode']=='0') {
		$InputError = 1;
		prnMsg( _('Seleccion un proyecto para la categoria') ,'error');
		$Errors[$i] = 'newareacode';
		$i++;		
	}
	
	if (isset($_POST['SelectedCategoryID']) and $_POST['SelectedCategoryID']!='' AND $InputError !=1) {

		/*SelectedSectionID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE fjoCategory
				SET cat_name ='" . $_POST['CategoryName'] . "', 
				legalid ='". $_POST['legalidsearch'] ."',
				fjoCategory.order = '". $_POST['CatOrden'] ."'
				WHERE cat_id = " . $_POST['SelectedCategoryID'];

		$msg = _('Registro Actualizado');
	} elseif ($InputError !=1) {

	/*SelectedSectionID is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new account section form */

		$sql = "INSERT INTO fjoCategory (
					cat_id,
					cat_name,
					legalid,
					areacode,
					fjoCategory.order)
			VALUES (null,
				'" . $_POST['CategoryName'] . "',
				'" . $_POST['legalidsearch'] ."',
				'" . $_POST['areacodenew'] ."',
				'" . $_POST['CatOrden'] ."'
				)";
		$msg = _('Registro Insertado');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$result = DB_query($sql,$db);
		prnMsg($msg,'Exito !');
		unset ($_POST['SelectedCategoryID']);
		unset ($_POST['CategoryID']);
		unset ($_POST['CatOrden']);
		unset ($_POST['CategoryName']);
		//Inicia cambios jvm
		$CatId= DB_Last_Insert_ID($db,'fjoCategory','cat_id');
		$SQL = "insert into  sec_stockcategory (categoryid, userid) ";
		$SQL = $SQL . "values ('".$CatId."', '".$_SESSION['UserID']."')";
		$result2 = DB_query($SQL,$db);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'accountgroups'
	$sql= "SELECT COUNT(*) FROM fjoSubCategory WHERE cat_id='" . $_GET['SelectedCategoryID'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg( _('No puedo borrar esta categoria pues existen registros en sub categorias que lo utilizan !'),'warn');
	} else {
		//Fetch section name
		$sql = "SELECT cat_name FROM fjoCategory WHERE cat_id='".$_GET['SelectedCategoryID'] . "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$CategoryName = $myrow[0];
		
		$sql="DELETE FROM fjoCategory WHERE cat_id='" . $_GET['SelectedCategoryID'] . "'";
		$result = DB_query($sql,$db);
		prnMsg( $CategoryName . ' ' . _(' categoria ha sido borrada...') . '!','success');

	} //end if account group used in GL accounts
	unset ($_GET['SelectedCategoryID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedCategoryID']);
	unset ($_POST['CategoryID']);
	unset ($_POST['CatOrden']);
	unset ($_POST['CategoryName']);
}

if (!isset($_GET['SelectedCategoryID']) OR !isset($_POST['SelectedCategoryID'])) {

	$sql = "SELECT fjoCategory.cat_id, fjoCategory.cat_name, fjoCategory.order, areas.areadescription
		FROM fjoCategory 
				JOIN areas ON fjoCategory.areacode = areas.areacode
		WHERE 1=1 ";
	/*
	if (isset($_POST['legalidsearch']) and $_POST['legalidsearch'] != '0')
		$sql = $sql." AND regions.regioncode = '".$_POST['legalidsearch']."'";
		*/
	if (isset($_POST['areacode']) and $_POST['areacode'] != '0')
		$sql = $sql." AND areas.areacode = '".$_POST['areacode']."'";
		
	$sql = $sql." ORDER BY fjoCategory.order, cat_name ";
	
	$ErrMsg = _('No se pudo encontrar registros de Categorias por');
	$result = DB_query($sql,$db,$ErrMsg);
	//echo "bus---".$sql;
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';

	echo "<table style='margin:auto;'>
		<tr>
		<th>" . _('Codigo de Categoria') . "</th>
		<th>" . _('Empresa') . "</th>
		<th>" . _('Nombre Categoria') . "</th>
		<th>" . _('Orden') . "</th>
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

		echo '<td>' . $myrow[0] . '</td><td>' . $myrow[3] . '</td><td>' . $myrow[1] . '</td><td>' . $myrow[2] . '</td><td>' . $myrow[4] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedCategoryID=' . $myrow[0] . '&legalidsearch=' . $_POST['legalidsearch'] . '">' . _('Modificar') . '</a></td>';
		
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&SelectedCategoryID=' . $myrow[0] . '&delete=1&legalidsearch=' . $_POST['legalidsearch'] . '">' . _('Eliminar') .'</a></td>';
		

	} //END WHILE LIST LOOP //
	echo '</table><p>';
} //end of ifs and buts!


if (isset($_POST['SelectedCategoryID']) or isset($_GET['SelectedCategoryID'])) {
	echo '<div class="centre"><a href=' . $_SERVER['PHP_SELF'] . '?' . SID .'>' . _('Revisar Categorias') . '</a></div>';
}

echo '<p>';

if (! isset($_GET['delete'])) {

	echo "<form method='post' name='AccountSections' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (isset($_GET['SelectedCategoryID'])) {
		//editing an existing section

		$sql = "SELECT *
			FROM fjoCategory
			WHERE cat_id='" . $_GET['SelectedCategoryID'] ."'";

		$result = DB_query($sql, $db);
		//echo "<pre>consulta --" .$sql;
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('No pude recuperar la categoria seleccionada.'),'warn');
			unset($_GET['SelectedCategoryID']);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['CategoryID'] = $myrow['cat_id'];
			$_POST['CategoryName']  = $myrow['cat_name'];
			$_POST['legalid'] = $myrow['legalid'];
			$_POST['CatOrden'] = $myrow['order'];
			$_POST['areacodenew'] = $myrow['areacode'];
			
			echo "<input type=hidden name='SelectedCategoryID' value='" . $_GET['SelectedCategoryID'] . "'>";
			echo "<table style='margin:auto;'>
			<td>" . _('ID Categoria') . ':' . "</td>
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
		}
		echo "<table style='margin:auto;'>";
	}
	
	if (isset($_POST['legalidsearch']) && $_POST['legalidsearch'] != '0') {
		/* SOLO QUE ESTE SELECCIONADA UNA EMPRESA SE PUEDEN DAR DE ALTA CATEGORIAS */
		
		echo "<tr><td>" . _('Nombre') . ':' . '</td>
			<td><input tabindex="2" ' . (in_array('CategoryName',$Errors) ?  'class="inputerror"' : '' ) ." type='text' name='CategoryName' size=30 maxlength=30 value='" . $_POST['CategoryName'] . "'></td>
			</tr>";
			
		echo '<tr>';
		
		
		/**************************************/
		/* SELECCION DEL PROYECTO           */
		
		echo '<tr><td style="text-align:right"><b>'._('X Proyecto 2:').'</b></td><td>';	
		///Imprime las razones sociales
		$SQL = "SELECT areas.areacode,areas.areadescription";
		$SQL = $SQL .	" FROM areas
				";
		
		$result=DB_query($SQL,$db);
		/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
		echo "<select name='areacodenew'>
			<option selected value='0'>Selecciona un proyecto..</option>";
		
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['areacodenew']) and $_POST['areacodenew']==$myrow["areacode"]){
				echo '<option selected value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
			} else {
				echo '<option value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
			}
		}
		
		
		echo '</select></td></tr>';
		
		echo "<tr><td>" . _('Orden') . ':' . '</td>
			<td><input tabindex="2" ' . (in_array('CatOrden',$Errors) ?  'class="inputerror"' : '' ) ." type='text' name='CatOrden' size=3 maxlength=10 value='" . $_POST['CatOrden'] . "'></td>
			</tr>";
			
		echo '<tr><td></td><td colspan=2><input tabindex="3" type=Submit name=submit value="' . _('Procesar Categoria') . '"></td></tr>';
		
	} else {
		echo "Para dar de alta una categoria, seleccione una empresa...";
	}
	
	
	echo '</table>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>