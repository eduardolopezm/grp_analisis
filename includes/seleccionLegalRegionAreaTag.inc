<?php


if ($legalsDisp != 0) {
	/*-------------------------------------------------------------------*/
	/*----- DESPLIEGA LISTA DE RAZONES SOCIALES -------------------------*/
	echo '<tr>
		<td style="text-align:left;">'
			._('Seleccione Una Razon Social:').'
				</td>
		<td>';
	
	/* Pinta las razones sociales unicamente que este usuario tiene acceso... */
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser JOIN tags ON sec_unegsxuser.tagref = tags.tagref
									  JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname
		ORDER BY legalbusinessunit.legalname";
	
	$result=DB_query($SQL,$db);
	
	echo '<select name="legalid">';
	echo '<option selected value=-999>Todas a las que tengo acceso...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalname'];
		}
	}
	echo '</select>';
	echo '<input type="submit" value="->" name="btnLegal"></td>';
	echo '</tr>';
	/*-------------------------------------------------------------------*/
} else {
	echo '<input type=hidden name="legalid" value=-999>';
}


if ($regionsDisp != 0) {
	/*-------------------------------------------------------------------*/
	/*----- DESPLIEGA LISTA DE REGIONES ---------------------------------*/
	$wcond ="";
	if ($_POST['legalid'])
		$wcond .= " AND (tags.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."' = '-999')";
	
	echo '<tr>';
	echo '<td style="text-align:left;" >' . _('Region — Matriz') . ':</td> ';
	
	
	$SQL=" SELECT regions.regioncode, regions.name
		FROM areas  JOIN regions ON areas.regioncode = regions.regioncode
					JOIN tags ON areas.areacode = tags.areacode
					JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and
				(tags.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."'='' OR '".$_POST['legalid']."'='-999')
		GROUP BY  regions.regioncode, regions.name
		ORDER BY regions.name";
	
	$resultarea = DB_query($SQL,$db);
	echo "<td><select name='region' style='font-size:8pt'>";
	echo '<option selected value=-999>Todas a las que tengo acceso...</option>';
	while ($myrowarea = DB_fetch_array($resultarea)) {
		if ($_POST['region']==$myrowarea['regioncode']){
			echo '<option selected value="' . $myrowarea['regioncode'] . '">' . $myrowarea['name'].'</option>';
		} else {
			echo '<option  value="' . $myrowarea['regioncode'] . '">' . $myrowarea['name'].'</option>';
		}
	}
	echo '</select>';
	echo '<input type="submit" value="->" name="btnRegion"></td>';
	echo '</tr>';
	
	/*-------------------------------------------------------------------*/
} else {
	echo '<input type=hidden name="region" value=-999>';	
}


if ($areasDisp != 0) {
	/*-------------------------------------------------------------------*/
	/*----- DESPLIEGA LISTA DE AREAS O SUCURSALES------------------------*/
	$wcond ="";
	if ($_POST['region'])
		$wcond = " AND (areas.regioncode = '".$_POST['region']."' OR '".$_POST['region']."' = '-999')";
	
	if ($_POST['legalid'])
		$wcond .= " AND (tags.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."' = '-999')";
	
	echo '<tr>';
	echo '<td  style="text-align:left;">' . _('Area') . ':</td> ';
	
	
	$SQL=" SELECT areas.areacode, areas.areadescription
		FROM areas  JOIN regions ON areas.regioncode = regions.regioncode
					JOIN tags ON areas.areacode = tags.areacode
					JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' $wcond
			GROUP BY  areas.areacode, areas.areadescription
			ORDER BY areas.areadescription";
	
			$resultarea = DB_query($SQL,$db);
			echo "<td><select name='area' style='font-size:8pt'>";
			echo '<option selected value=-999>Todas a las que tengo acceso...</option>';
			while ($myrowarea = DB_fetch_array($resultarea)) {
			if ($_POST['area']==$myrowarea['areacode']){
			echo '<option selected value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
			} else {
		echo '<option  value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
			}
}
echo '</select>';
echo '<input type="submit" value="->" name="btnArea"></td>';
echo '</tr>';
	
	/*-------------------------------------------------------------------*/
} else {
	echo '<input type=hidden name="area" value=-999>';	
}


if ($tagsDisp != 0) {
	/*-------------------------------------------------------------------*/
	/*----- DESPLIEGA LISTA DE UNIDADES DE NEGOCIOS----------------------*/
	$wcond ="";
	if ($_POST['region'])
		$wcond = " AND (areas.regioncode = '".$_POST['region']."' OR '".$_POST['region']."' = '-999')";
	
	if ($_POST['area'])
		$wcond .= " AND (tags.areacode = '".$_POST['area']."' OR '".$_POST['area']."' = '-999')";
	
	if ($_POST['legalid'])
		$wcond .= " AND (tags.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."' = '-999')";
	
	echo '<tr><td style="text-align:left;">'._('Unidad de Negocios:').'</td><td>';
	
	$SQL = "SELECT tags.tagref,tags.tagdescription";
	$SQL = $SQL .	" FROM areas JOIN regions ON areas.regioncode = regions.regioncode
							 JOIN tags ON areas.areacode = tags.areacode
							 JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref ";
	$SQL = $SQL .	" WHERE 1=1 $wcond";
	$SQL = $SQL .	" and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		GROUP BY tags.tagref,tags.tagdescription
		ORDER BY tags.tagdescription";
	
	$result=DB_query($SQL,$db);
	echo '<select name="tag">';
	echo '<option value=-999>Todas a las que tengo acceso...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		}
	}
	echo '</select>';
	echo '<input type="submit" value="->" name="btnTag"></td>';
	echo '</tr>';
	/*-------------------------------------------------------------------*/
} else {
	echo '<input type=hidden name="tag" value=-999>';	
}

if ($localizacionesDisp != 0) { 
	/*-------------------------------------------------------------------*/
	/*----- DESPLIEGA LISTA DE ALMACENES --------------------------------*/
	$wcond ="";
	if ($_POST['region'])
		$wcond = " AND (areas.regioncode = '".$_POST['region']."' OR '".$_POST['region']."' = '-999')";
	
	if ($_POST['area'])
		$wcond .= " AND (tags.areacode = '".$_POST['area']."' OR '".$_POST['area']."' = '-999')";
	
	if ($_POST['tag'])
		$wcond .= " AND (locations.tagref = '".$_POST['tag']."' OR '".$_POST['tag']."' = '-999')";
	
	if ($_POST['legalid'])
		$wcond .= " AND (tags.legalid = '".$_POST['legalid']."' OR '".$_POST['legalid']."' = '-999')";
	
	echo '<tr><td style="text-align:left;">'._('Almacen — Localizaci—n:').'</td><td>';
	
	$SQL = "SELECT locations.loccode, locationname";
	$SQL = $SQL .	" FROM areas JOIN regions ON areas.regioncode = regions.regioncode
							 JOIN tags ON areas.areacode = tags.areacode
							 JOIN locations ON tags.tagref = locations.tagref
							 JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref ";
	$SQL = $SQL .	" WHERE 1=1 $wcond";
	$SQL = $SQL .	" and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		GROUP BY locations.loccode, locationname
		ORDER BY locationname";
	
	$result=DB_query($SQL,$db);
	echo '<select name="loccode">';
	echo '<option value=-999>Todos a las que tengo acceso...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['loccode']) and $_POST['loccode']==$myrow['loccode']){
			echo '<option selected value=' . $myrow['loccode'] . '>' .$myrow['locationname'];
		} else {
			echo '<option value=' . $myrow['loccode'] . '>' . $myrow['locationname'];
		}
	}
	echo '</select>';
	echo '<input type="submit" value="->" name="btnLocation"></td>';
	echo '</tr>';
	
	/*-------------------------------------------------------------------*/
} else {
	echo '<input type=hidden name="loccode" value=-999>';	
}


?>