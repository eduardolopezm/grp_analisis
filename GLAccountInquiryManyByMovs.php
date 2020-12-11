<?php

$PageSecurity = 8;
include ('includes/session.inc');
$title = _('Consulta Transacciones de una Cuenta Contable');
if (isset($_POST['PrintEXCEL'])) {

}else{
	include('includes/header.inc');
}
include('includes/GLPostings.inc');

if ((isset($_GET['GLCode'])) and ($_GET['GLCode'] != "")){
	$_POST['GLCode'] = $_GET['GLCode'];
}

if ((isset($_GET['Account'])) and ($_GET['Account'] != "")){
	$_POST['Account'] = $_GET['Account'];
}

if ((isset($_GET['cbRangoDe'])) and ($_GET['cbRangoDe'] != "")){
	$_POST['cbRangoDe'] = $_GET['cbRangoDe'];
}

if ((isset($_GET['cbRangoA'])) and ($_GET['cbRangoA'] != "")){
	$_POST['cbRangoA'] = $_GET['cbRangoA'];
}


if ((isset($_POST['Account']) && ($_POST['Account'] != ""))) {
	$SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])) {
	$SelectedAccount = $_GET['Account'];
	$_POST['Account'] = $SelectedAccount;
}

if ($_POST['GLCode'] != '') {
	$SelectedAccount = $_POST['GLCode'];
}
//-----Biviana---
if ($_POST['cbRangoDe']!=''  and $_POST['cbRangoA']!='') {
	$rangoDe = $_POST['cbRangoDe'];
	$rangoA = $_POST['cbRangoA'];
}
//------

if (isset($_POST['legalid'])) {
	$SelectedLegal = $_POST['legalid'];
} elseif (isset($_GET['legalid'])) {
	$SelectedLegal = $_GET['legalid'];
	$_POST['legalid'] = $SelectedLegal;
}

if (isset($_POST['xRegion'])) {
	$SelectedRegion = $_POST['xRegion'];
} elseif (isset($_GET['xRegion'])) {
	$SelectedRegion = $_GET['xRegion'];
	$_POST['xRegion'] = $SelectedRegion;
}

if (isset($_POST['tag'])) {
	$SelectedTag = $_POST['tag'];
} elseif (isset($_GET['tag'])) {
	$SelectedTag = $_GET['tag'];
	$_POST['tag'] = $SelectedTag;
}

if (isset($_POST['FromPeriod'])) {
	$SelectedPeriod = $_POST['FromPeriod'];
} elseif (isset($_GET['FromPeriod'])) {
	$SelectedPeriod = $_GET['FromPeriod'];
}

if (isset($_POST['FromPeriod'])) {
	$SelectedFromPeriod = $_POST['FromPeriod'];
} elseif (isset($_GET['FromPeriod'])) {
	$SelectedFromPeriod = $_GET['FromPeriod'];
	$_POST['FromPeriod'] = $SelectedFromPeriod;
}

if (isset($_POST['ToPeriod'])) {
	$SelectedToPeriod = $_POST['ToPeriod'];
} elseif (isset($_GET['ToPeriod'])) {
	$SelectedToPeriod = $_GET['ToPeriod'];
	$_POST['ToPeriod'] = $SelectedToPeriod;
}

if (!isset($_POST['PrintEXCEL'])) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Consulta de Cuentas Contables') . '" alt="">' . ' ' . _('Consulta de Cuentas Contables') . '</p>';

	echo '<div class="page_help_text">' . _('Utiliza la tecla shift presionada para seleccionar varios periodos...') . '</div><br>';

	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	/*Dates in SQL format for the last day of last month*/
	$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

	/*Show a form to allow input of criteria for TB to show */
	echo '<table style="margin:auto;">';

	$sql='SELECT *
		FROM chartspecialgroups
		ORDER BY groupname';

	$result=DB_query($sql, $db);

	echo '<tr><td><b>' . _('Agrupacion') . ':</b></td><td><select name="GLGroup" >';

	echo "<option selected value=''>Seleccionar Agrupacion Especial...</option>";

	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['GLGroup']) and $_POST['GLGroup']==$myrow['id']) {
			echo '<option selected value=' . $myrow['id'] . '>' . $myrow['groupname'];
		} else {
			echo '<option value=' . $myrow['id'] . '>' . $myrow['groupname'];
		}
	}
	echo '</select></td></tr>';
	/*********************************/

	/********************************************/
	/* SECCION DE BUSQUEDA DE CUENTAS CONTABLES */
	//echo '<tr><td></td>';
	// End select tag

	echo '<tr><td align=right><b>Busca Cta x Nombre:</b></td><td><input
		type=Text Name="GLManualSearch" Maxlength=40 size=40 VALUE='. $_POST['GLManualSearch'] .'  >
		<input type=submit name="SearchAccount" value="'._('Buscar').'"></td>';
	echo '</tr>';
	/********************************************/

	// End select tag

	if (!isset($_POST['GLManualCode'])) {
		$_POST['GLManualCode']='';
	}
	/*echo '<td  colspan=2><input class="number" type=Text Name="GLManualCode" Maxlength=17 size=17 onChange="inArray(this.value, GLCode.options,'.
		"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"' .
			' VALUE='. $_POST['GLManualCode'] .'  > ò -> </td>';*/

	$sql='SELECT accountcode,
				accountname
			FROM chartmaster
			WHERE accountname like "%'.$_POST['GLManualSearch'].'%"
			ORDER BY accountcode';

	$result=DB_query($sql, $db);
	//echo '<td></td><td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';
	echo '<td></td><td><select name="GLCode" >';
	echo "<option selected value=''>Seleccionar Cuenta...</option>";
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['GLCode']) and $_POST['GLCode']==$myrow['accountcode']) {
			echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		} else {
			echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		}
	}
	echo '</select></td>';
	/*********************************/
	echo '<tr><td align=right><b>O Num. de Cuenta</b></td></tr>';
        echo '<tr>
         <td>'._('Inicio del NUmero de Cuenta').":</td>
         <td><input type=text Name='Account' value='".$_POST['Account']."'></td></tr>";

	//***************-Rango De:----------------
	echo '<tr><td align=right><b>O Rango</b></td></tr>';
	echo '<tr><td>' . _('De') . ':</td><td><select name="cbRangoDe">';
	$sql='SELECT accountcode,
				accountname
			FROM chartmaster
			ORDER BY accountcode';
	$result=DB_query($sql, $db);
	echo "<option selected value=''>Seleccionar Cuenta...</option>";
	while ($myrow=DB_fetch_array($result)){
		if($myrow['accountcode'] == $_POST['cbRangoDe'])
		{
			echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		}else{
			echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		}
	}
	echo '</select></td></tr>';

	//---------------A:--cbRangoA---------------------
	echo '<tr><td>' . _('A') . ':</td><td><select name="cbRangoA" >';
	$sql='SELECT accountcode,
				accountname
			FROM chartmaster
			ORDER BY accountcode';

	$result=DB_query($sql, $db);
	echo "<option selected value=''>Seleccionar Cuenta...</option>";
	while ($myrow=DB_fetch_array($result)){
		if($myrow['accountcode'] == $_POST['cbRangoA']){
			echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		}else{
			echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		}
	}
	echo '</select></td></tr>';

	//------------------Combo Tipo----------------------
	$sql = 'SELECT tipo,nombreMayor FROM chartTipos ORDER BY tipo';
	$result = DB_query($sql, $db);

	echo '<TR><TD>' . _('Tipo') . ':</TD><TD><SELECT NAME=Tipo>';
	echo "<option selected value='TODOS'>TODOS</option>";
	while ($myrow = DB_fetch_array($result)){
		if (isset($_POST['Tipo']) and $myrow[0]==$_POST['Tipo']){
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow[0] . "'>" . $myrow[1];
	}
	echo '</select></TD</TR>';
	//----------------------------

	//------------------Combo Tipo Polizas----------------------
	$sql = 'SELECT typeid, typename FROM systypescat ORDER BY typeid';
	$result = DB_query($sql, $db);

	echo '<TR><TD>' . _('Polizas Tipo') . ':</TD><TD><SELECT NAME="TipoPoliza">';
	echo "<option selected value='TODOS'>TODOS</option>";
	while ($myrow = DB_fetch_array($result)){
		if (isset($_POST['TipoPoliza']) and $myrow[0]==$_POST['TipoPoliza']){
			echo "<OPTION SELECTED VALUE='";
		} else {
			echo "<OPTION VALUE='";
		}
		echo $myrow[0] . "'>" . $myrow[0].' - '.$myrow[1].'</option>';
	}
	echo '</select></TD</TR>';
	//----------------------------

	//Select the razon social
	echo '<tr><td>'._('Seleccione Una Razon Social:').'<td><select name="legalid">';

	///Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";

	$result=DB_query($SQL,$db);

	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		}
	}
	echo '</select></td>';
	// End select tag

	/************************************/
	/* SELECCION DEL REGION */
	echo '<tr><td>' . _('X Region') . ':' . "</td>
		<td><select tabindex='4' name='xRegion'>";

	$sql = "SELECT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name FROM regions JOIN areas ON areas.regioncode = regions.regioncode
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY regions.regioncode, regions.name";

	$result=DB_query($sql,$db);

	echo "<option selected value='0'>Todas las regiones...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['regioncode'] == $_POST['xRegion']){
			echo "<option selected value='" . $myrow["regioncode"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['regioncode'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	/* SELECCION DEL AREA */
	echo '<tr><td>' . _('X Area') . ':' . "</td>
		<td><select tabindex='4' name='xArea'>";

	$sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
			FROM areas
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY areas.areacode, areas.areadescription";

	$result=DB_query($sql,$db);

	echo "<option selected value='0'>Todas las areas...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['areacode'] == $_POST['xArea']){
			echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';

	/************************************/
	/* SELECCION DEL DEPARTAMENTO       */
	echo '<tr><td>' . _('X Departamento') . ':' . "</td>
		<td><select tabindex='4' name='xDepartamento'>";

	$sql = "SELECT u_department, CONCAT(u_department,' - ',department) as name FROM departments";
	$result=DB_query($sql,$db);

	echo "<option selected value='0'>Todos los departamentos...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['u_department'] == $_POST['xDepartamento']){
			echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['name'];
	      } else {
		      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['name'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/

	/************************************/

	//Select the tag
	echo '<tr><td>' . _('Unidad de Negocio') . ':</td><td><select name="tag">';

	///Pinta las unidades de negocio por usuario
	$SQL = "SELECT t.tagref, t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagdescription, t.tagref";


	$result=DB_query($SQL,$db);
	echo '<option selected value=all>Todas Las Unidades de Negocio...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		}
	}
	echo '</select></td>';
	// End select tag
/*
         echo '<tr>
         <td>'._('Rango de periodos').':</td>
         <td><select style="height:120px" Name=Period[] multiple>';
	 $sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	 $Periods = DB_query($sql,$db);
         $id=0;
         while ($myrow=DB_fetch_array($Periods,$db)){

            if((isset($SelectedPeriod[$id]) and $myrow['periodno'] == $SelectedPeriod[$id]) or (isset($SelectedFromPeriod) and $myrow['periodno'] >= $SelectedFromPeriod
													and $myrow['periodno'] <= $SelectedToPeriod)){
              echo '<option selected VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
            $id++;
            } else {
	      if (!isset($SelectedFromPeriod)) {
		echo '<option VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
	      }
            }

         }
         echo "</select></td>
        </tr>";*/

echo '<TR><TD>' . _('Periodo Desde:') . '</TD><TD><SELECT Name="FromPeriod">';
	$nextYear = date("Y-m-d",strtotime("+1 Year"));
	$sql = "SELECT periodno, lastdate_in_period FROM periods where lastdate_in_period < '$nextYear' ORDER BY periodno DESC";
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['periodno']){
				echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<OPTION VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		}
	}

	echo '</SELECT></TD></TR>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$lastDate = date("Y-m-d",mktime(0,0,0,Date('m')+1,0,Date('Y')));
		$sql = "SELECT periodno FROM periods where lastdate_in_period = '$lastDate'";
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultToPeriod = (int) ($MaxPrdrow[0]);

	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<TR><TD>' . _('Periodo Hasta:') .'</TD><TD><SELECT Name="ToPeriod">';

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<OPTION SELECTED VALUE="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<OPTION VALUE ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</SELECT></TD></TR>';

	echo "<tr>";
			echo "<td colspan='2' style='text-align:center;'>";
				echo "<table border='0' width='100%'>";
					echo "<tr>";
						echo "<td style='text-align:center;'><input type=submit name='Show' VALUE='"._('Buscar Transacciones')."'></td>";
						echo "<td style='text-align:center;'><input type='hidden' name='PrintPDF' value='" . _('Exportar a PDF') . "'></td>";
						echo "<td style='text-align:center;'><input type='submit' name='PrintEXCEL' value='" . _('Exportar a Excel') . "'></td>";
					echo "</tr>";
				echo "</table>";
			echo "</td>";
		echo "</tr>";

echo "</table><p>";
//<div class='centre'><input type=submit name='Show' VALUE='"._('Buscar Transacciones')."'></div>
//<div class='centre'><input type=submit name='PrintEXCEL' VALUE='" . _('Exporta a Excel') ."'></div></form>";
/* End of the Form  rest of script is what happens if the show button is hit*/

}

if (isset($_POST['Show'])or isset($_POST['PrintEXCEL'])){

	if (isset($_POST['PrintEXCEL'])) {
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=Consulta de Cuentas Contables");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	}

	if (!isset($SelectedPeriod)){
		prnMsg(_('Seleccione un periodo de la lista'),'info');
		include('includes/footer.inc');
		exit;
	}

//	if ((!isset($SelectedAccount)) || (strlen($SelectedAccount) < 3) || (!isset($rangoDe)) || ($rangoDe=='') || (!isset($rangoA)) || ($rangoA=='')){

	if ((!isset($SelectedAccount)) || (strlen($SelectedAccount) < 3)){
		if((!isset($rangoDe)) || ($rangoDe=='') || (!isset($rangoA)) || ($rangoA=='')){
			if ($_POST['GLCode'] != ""){
				prnMsg(_('Selecciona una cuenta, o escribe los tres primeros carecteres de la cuenta...'),'info');
				include('includes/footer.inc');
				exit;
			}
		}
	}


	/*Is the account a balance sheet or a profit and loss account */
	$sql = "SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_";
		if($SelectedAccount != ''){
			$sql = $sql ." WHERE chartmaster.accountcode like '".$SelectedAccount."%'";
		}else{
			$sql= $sql ." WHERE chartmaster.accountcode between '".$rangoDe."' and '".$rangoA."'";
		}
		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql ." AND chartmaster.tipo = ".$_POST['Tipo']."";
		}
	$result = DB_query($sql,$db);
	$PandLRow = DB_fetch_row($result);

	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	$FirstPeriodSelected = ($SelectedPeriod);
	//$LastPeriodSelected = max($SelectedPeriod);
	$LastPeriodSelected = $_POST['ToPeriod'];

	if ($_POST['tag']=='all') {

 		$sql= "SELECT gltrans.account,
			chartmaster.accountname,
			gltrans.type,
			typename,
			chartmaster.naturaleza,
			SUM(amount) as amount,
			SUM(CASE when amount > 0 THEN amount ELSE 0 END) AS cargos,
			SUM(CASE when amount < 0 THEN amount ELSE 0 END) AS abonos
			FROM gltrans
				JOIN tags ON gltrans.tag = tags.tagref
				INNER JOIN systypescat ON gltrans.type=systypescat.typeid
				JOIN areas ON tags.areacode = areas.areacode
				JOIN regions ON areas.regioncode = regions.regioncode
				JOIN departments ON tags.u_department=departments.u_department
				JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
				JOIN chartmaster ON gltrans.account = chartmaster.accountcode";

		if($_POST['GLGroup'] != ''){
			$sql= $sql." JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode";
		}

		if($_POST['GLGroup'] != ''){
			$sql= $sql." WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		}elseif($SelectedAccount != ''){
			$sql= $sql." WHERE gltrans.account like '".$SelectedAccount."%'";
		}else{
			$sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
		}



		$sql= $sql." and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (departments.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)

		AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tags.legalid = ".$_POST['legalid']."";

		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql." AND chartmaster.tipo = ".$_POST['Tipo']."";
		}
		if($_POST['TipoPoliza'] != 'TODOS'){
			$sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
		}
		$sql= $sql . " GROUP BY gltrans.account, chartmaster.accountname, gltrans.type, typename, chartmaster.naturaleza";
		$sql= $sql." ORDER BY gltrans.account,typename";
		//echo "<br><br>sql1".$sql;
	} else {
 		$sql= "SELECT gltrans.account,
			chartmaster.accountname,
			gltrans.type,
			typename,
			chartmaster.naturaleza,
			SUM(amount) as amount,
			SUM(CASE when amount > 0 THEN amount ELSE 0 END) AS cargos,
			SUM(CASE when amount < 0 THEN amount ELSE 0 END) AS abonos
		FROM gltrans
			JOIN tags ON gltrans.tag = tags.tagref
			INNER JOIN systypescat on gltrans.type=systypescat.typeid
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode";

		if($_POST['GLGroup'] != ''){
			$sql= $sql." JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode";
		}

		if($_POST['GLGroup'] != ''){
			$sql= $sql." WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		}elseif($SelectedAccount != ''){
			$sql= $sql." WHERE gltrans.account like '".$SelectedAccount."%'";
		}else{
			$sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
		}

		$sql= $sql." 	AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tag='".$_POST['tag']."'
		AND legalid = ".$_POST['legalid']."";
		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql." AND chartmaster.tipo = ".$_POST['Tipo']."";
		}
		if($_POST['TipoPoliza'] != 'TODOS'){
			$sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
		}
		$sql= $sql . " GROUP BY gltrans.account, chartmaster.accountname, gltrans.type, typename, chartmaster.naturaleza";
		$sql= $sql." ORDER BY gltrans.account,typename";
		//$sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, counterindex";
	}

	//echo "<br><br>sql2".$sql;
	if($SelectedAccount != ''){
 		$ErrMsg = _('Las transacciones para la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudieron ser recuperadas porque') ;
	}else{
 		$ErrMsg = _('Las transacciones para la cuenta') . ' ' . $rangoDe . ' ' . $rangoA . ' ' . _('no pudieron ser recuperadas porque') ;
	}

	//echo $sql;
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table style="margin:auto;">';

	$TableHeader = "<tr>
			<th>" . _('Cuenta') . "</th>
			<th>" . _('Nombre Cuenta') . "</th>
			<th>" . _('Tipo ID') . "</th>
			<th>" . _('Tipo') . "</th>
			<th>" . _('Cargos') . "</th>
			<th>" . _('Abonos') . "</th>
			<th>" . _('Suma') . "</th>
			<th>" . _('Saldo') . "</th>
			</tr>";

	echo $TableHeader;

	if ($PandLAccount==True) {
		$RunningTotal = 0;
	} else {
	       // added to fix bug with Brought Forward Balance always being zero
		$sql = "SELECT sum(bfwd) as bfwd,
			sum(actual) as actual,
			period, naturaleza
		FROM chartdetails JOIN tags ON chartdetails.tagref = tags.tagref
					JOIN chartmaster ON chartdetails.accountcode = chartmaster.accountcode";

		if($_POST['GLGroup'] != ''){
			$sql= $sql." JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode";
		}

		if($_POST['GLGroup'] != ''){
			$sql= $sql." WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		}elseif($SelectedAccount != ''){
			$sql= $sql." WHERE chartdetails.accountcode like '".$SelectedAccount."%'";
		}else{
			$sql= $sql." WHERE chartdetails.accountcode between '".$rangoDe."' and '".$rangoA."'";
		}


		$sql = $sql ." AND chartdetails.period=" . $FirstPeriodSelected."
		AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
		AND legalid = ".$_POST['legalid']."";
		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql ." AND chartmaster.tipo = ".$_POST['Tipo']."";
		}
		$sql = $sql ." GROUP BY period";

		if($SelectedAccount != ''){
			$ErrMsg = _('El detalle de la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudo ser recuperado de la BD');
		}else{
			$ErrMsg = _('El detalle de la cuenta') . ' De: ' . $rangoDe . ' A: ' . $rangoA . ' ' . _('no pudo ser recuperado de la BD');
		}

		//echo '<pre>sql0:'.$sql;

		$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
		$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
		// --------------------

		$RunningTotal =$ChartDetailRow['bfwd'];

		echo "<tr bgcolor='#FDFEEF'>
			<td></td><td></td><td></td>
			<td colspan=3><b>" . _('SALDO INICIAL:') . '</b></td>
			<td></td>
			<td class=number><b>' . number_format($RunningTotal,2) . '</b></td>
			<td colspan=3></td>
			</tr>';
	}

	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k=0; //row colour counter

	$totalcuentacargos = 0;
	$totalcuentaabonos = 0;
	$totalcuentasuma = 0;
	$totalcuenta = 0;


	while ($myrow=DB_fetch_array($TransResult)) {
		if ($j == 1) {
			// DIBUJA ENCABEZADO DE INICIO DE CADA CUENTA CONTABLE
			$cuentacontable = $myrow['account'];
			$nombrecuenta = $myrow['accountname'];

			$sql = "SELECT sum(bfwd) as bfwd,
				sum(actual) as actual
			FROM chartdetails JOIN tags ON chartdetails.tagref = tags.tagref
			WHERE chartdetails.accountcode = '".$cuentacontable."'
				AND chartdetails.period=" . $FirstPeriodSelected."
				AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
				AND legalid = ".$_POST['legalid']."
			";
			//echo '<pre>sql1:'.$sql;
			$ErrMsg = _('El detalle de la cuenta') . ' ' . $cuentacontable . ' ' . _('no pudo ser recuperado de la BD');

			$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
			$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

			echo "<tr bgcolor='#FDFEEF'>
					<td colspan=5><b>I:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
			echo '<td></td>
				<td></td>
				<td class=number nowrap><b>' . number_format($ChartDetailRow['bfwd'],2) . '</b></td>
				<td></td><td></td>
				</tr>';
			$saldoxcuenta = $ChartDetailRow['bfwd'];
			$saldoxcuentacargos = 0;
			$saldoxcuentaabonos = 0;
		}

		$j = $j + 1;

		//IMPRIMIR SUB TOTALES POR CUENTA
		if (trim($myrow['account']) != trim($cuentacontable)) {
			/*calcula el acumulado de las cuentas*/
			$totalcuenta = $totalcuenta + $saldoxcuenta;
			$totalcuentacargos = $totalcuentacargos + $saldoxcuentacargos;
			$totalcuentaabonos = $totalcuentaabonos + $saldoxcuentaabonos;

			echo "<tr bgcolor='#FDFEEF'>
				<td colspan=4><b>" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
			echo '  <td class=number nowrap><b>' . number_format($saldoxcuentacargos,2) . '</b></td>
				<td class=number nowrap><b>' . number_format($saldoxcuentaabonos,2) . '</b></td>
				<td class=number nowrap><b>' . number_format($saldoxcuentasuma,2) . '</b></td>
				<td class=number nowrap><b>' . number_format($saldoxcuenta,2) . '</b></td>
				<td></td><td></td>
				</tr>';

			$sql = "SELECT sum(bfwd) as bfwd,
				sum(actual) as actual
			FROM chartdetails  JOIN tags ON chartdetails.tagref = tags.tagref
			WHERE chartdetails.accountcode = '".$myrow['account']."'
				AND chartdetails.period=" . $FirstPeriodSelected."
				AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
				AND legalid = ".$_POST['legalid']."
			";
		//	echo '<pre>sql2:'.$sql;
			$ErrMsg = _('El detalle de la cuenta') . ' ' . $cuentacontable . ' ' . _('no pudo ser recuperado de la BD');
			$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
			$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

			$cuentacontable = $myrow['account'];
			$nombrecuenta = $myrow['accountname'];

			$saldoxcuenta = $ChartDetailRow['bfwd'];//*$ChartDetailRow['naturaleza'];
			$saldoxcuentacargos = 0;
			$saldoxcuentaabonos = 0;
			$saldoxcuentasuma = 0;

			echo "<tr bgcolor='#FFFFFF'>
					<td colspan=5><b><br><br></b></td>";

			echo '<td></td>
				<td></td>
				<td class=number><b></b></td>
				<td></td><td></td>
				</tr>';

			echo "<tr bgcolor='#FDFEEF'>
					<td colspan=5><b>" . $cuentacontable . ' '. $nombrecuenta . '(INICIAL)</b></td>';

			echo '<td></td>
				<td></td>
				<td class=number nowrap><b>' . number_format($saldoxcuenta,2) . '</b></td>
				<td></td><td></td>
				</tr>';
		}


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$RunningTotal += ($myrow['cargos'] + $myrow['abonos']);
		$PeriodTotal  += ($myrow['cargos'] + $myrow['abonos']);

		//LLEVA EL SALDO DE ESTA CUENTA...
		$saldoxcuenta += ($myrow['cargos'] + $myrow['abonos']);

		/*if($myrow['amount']>=0){*/
			$DebitAmount = number_format($myrow['cargos'],2);
			$CreditAmount = number_format($myrow['abonos']*-1,2);
			$saldoxcuentacargos = $saldoxcuentacargos + $myrow['cargos'];
			$saldoxcuentaabonos = $saldoxcuentaabonos + ($myrow['abonos']*-1);
			$saldoxcuentasuma = $saldoxcuentasuma + ($myrow['cargos']+$myrow['abonos']);
		/*} else {
			$CreditAmount = number_format(-$myrow['amount'],2);
			$DebitAmount = '';

		}*/



		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>

			<td class=number>%s</td>
			<td class=number>%s</td>
			<td class=number nowrap><b>%s</b></td>
			<td class=number nowrap><b>%s</b></td>
			</tr>",
			$myrow['account'],
			$myrow['accountname'],
			$myrow['type'],
			$myrow['typename'],

			$DebitAmount,
			$CreditAmount,
			number_format(($myrow['cargos'] + $myrow['abonos']),2),
			number_format($saldoxcuenta,2));

	}

	//******************************************
	//******IMPRIME ULTIMO TOTAL DE CUENTA

	echo "<tr bgcolor='#FDFEEF'>
			<td colspan=4><b>" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';


		echo '	<td class=number nowrap><b>' . number_format($saldoxcuentacargos,2) . '</b></td>
			<td class=number nowrap><b>' . number_format($saldoxcuentaabonos,2) . '</b></td>
			<td class=number nowrap ><b>' . number_format($saldoxcuentacargos-$saldoxcuentaabonos,2) . '</b></td>
			<td class=number nowrap><b>' . number_format($saldoxcuenta,2) . '</b></td>
			<td></td><td></td>
			</tr>';

	echo "<tr bgcolor='#FFFFFF'>
			<td colspan=5><b><br><br></b></td>";

	echo '<td></td>
		<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
	//******************************************


	echo "<tr bgcolor='#FDFEEF'><td></td><td></td><td></td><td colspan=2><b>";
	if ($PandLAccount==True){
		echo _('Total Movimientos del Periodo');
	} else { /*its a balance sheet account*/
		echo _('Saldo Acumulado');
	}
	echo '</b></td>';

	echo '<td></td><td align=right nowrap><b>' . number_format(($RunningTotal),2) . '</b></td><td colspan=2></td><td></td></tr>';


	//**************Imprimie un total general****************
	$totalcuenta = $totalcuenta + $saldoxcuenta;
	$totalcuentacargos = $totalcuentacargos + $saldoxcuentacargos;
	$totalcuentaabonos = $totalcuentaabonos + $saldoxcuentaabonos;
	echo "<tr><td colspan=5><br></td></tr>";
	echo "<tr bgcolor='#FDFEEF'><td></td><td></td>
			<td colspan=2 style='text-align:center'><b>"._('Total Acumulado: ')."</b></td>";

		echo '<td class=number nowrap><b>' . number_format($totalcuentacargos,2) . '</b></td>
			<td class=number nowrap><b>' . number_format($totalcuentaabonos,2) . '</b></td>
			<td class=number nowrap><b>' . number_format($totalcuenta,2) . '</b></td>
			<td></td><td></td>
			</tr>';

	echo "<tr bgcolor='#FFFFFF'>
			<td colspan=5><b><br><br></b></td>";
	echo '<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
	//******************************************

	echo '</table>';


	/* COMIENZA RESUMEN X CUENTA */

	/*Is the account a balance sheet or a profit and loss account */
	$sql = "SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_";
		if($SelectedAccount != ''){
			$sql = $sql ." WHERE chartmaster.accountcode like '".$SelectedAccount."%'";
		}else{
			$sql= $sql ." WHERE chartmaster.accountcode between '".$rangoDe."' and '".$rangoA."'";
		}
		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql ." AND chartmaster.tipo = ".$_POST['Tipo']."";
		}
	$result = DB_query($sql,$db);
	$PandLRow = DB_fetch_row($result);

	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	$FirstPeriodSelected = ($SelectedPeriod);
	//$LastPeriodSelected = max($SelectedPeriod);
	$LastPeriodSelected = $_POST['ToPeriod'];

	if ($_POST['tag']=='all') {

 		$sql= "SELECT
			gltrans.type,
			typename,
			SUM(amount) as amount,
			SUM(CASE when amount > 0 THEN amount ELSE 0 END) AS cargos,
			SUM(CASE when amount < 0 THEN amount ELSE 0 END) AS abonos
			FROM gltrans
				JOIN tags ON gltrans.tag = tags.tagref
				INNER JOIN systypescat ON gltrans.type=systypescat.typeid
				JOIN areas ON tags.areacode = areas.areacode
				JOIN regions ON areas.regioncode = regions.regioncode
				JOIN departments ON tags.u_department=departments.u_department
				JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
				JOIN chartmaster ON gltrans.account = chartmaster.accountcode";

		if($_POST['GLGroup'] != ''){
			$sql= $sql." JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode";
		}

		if($_POST['GLGroup'] != ''){
			$sql= $sql." WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		}elseif($SelectedAccount != ''){
			$sql= $sql." WHERE gltrans.account like '".$SelectedAccount."%'";
		}else{
			$sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
		}



		$sql= $sql." and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (departments.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)

		AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tags.legalid = ".$_POST['legalid']."";

		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql." AND chartmaster.tipo = ".$_POST['Tipo']."";
		}
		if($_POST['TipoPoliza'] != 'TODOS'){
			$sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
		}
		$sql= $sql . " GROUP BY gltrans.type, typename";
		$sql= $sql." ORDER BY typename";
		//echo "<br><br>sql1".$sql;
	} else {
 		$sql= "SELECT
			gltrans.type,
			typename,
			SUM(amount) as amount,
			SUM(CASE when amount > 0 THEN amount ELSE 0 END) AS cargos,
			SUM(CASE when amount < 0 THEN amount ELSE 0 END) AS abonos
		FROM gltrans
			JOIN tags ON gltrans.tag = tags.tagref
			INNER JOIN systypescat on gltrans.type=systypescat.typeid
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode";

		if($_POST['GLGroup'] != ''){
			$sql= $sql." JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode";
		}

		if($_POST['GLGroup'] != ''){
			$sql= $sql." WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		}elseif($SelectedAccount != ''){
			$sql= $sql." WHERE gltrans.account like '".$SelectedAccount."%'";
		}else{
			$sql= $sql." WHERE gltrans.account between '".$rangoDe."' and '".$rangoA."'";
		}

		$sql= $sql." 	AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tag='".$_POST['tag']."'
		AND legalid = ".$_POST['legalid']."";
		if($_POST['Tipo'] != 'TODOS'){
			$sql= $sql." AND chartmaster.tipo = '".$_POST['Tipo']."'";
		}
		if($_POST['TipoPoliza'] != 'TODOS'){
			$sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
		}
		$sql= $sql . " GROUP BY gltrans.type, typename";
		$sql= $sql." ORDER BY typename";
		//$sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, counterindex";
	}

	//echo "<br><br>sql2".$sql;
	if($SelectedAccount != ''){
 		$ErrMsg = _('Las transacciones para la cuenta') . ' ' . $SelectedAccount . ' ' . _('no pudieron ser recuperadas porque') ;
	}else{
 		$ErrMsg = _('Las transacciones para la cuenta') . ' ' . $rangoDe . ' ' . $rangoA . ' ' . _('no pudieron ser recuperadas porque') ;
	}

	//echo $sql;
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table>';

	$TableHeader = "<tr>
			<th>" . _('Tipo ID') . "</th>
			<th>" . _('Tipo') . "</th>
			<th>" . _('Cargos') . "</th>
			<th>" . _('Abonos') . "</th>
			<th>" . _('Suma') . "</th>
			<th>" . _('Saldo') . "</th>
			</tr>";

	echo $TableHeader;

	$RunningTotal = 0;

	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k=0; //row colour counter

	$totalcuentacargos = 0;
	$totalcuentaabonos = 0;
	$totalcuentasuma = 0;
	$totalcuenta = 0;

	$saldoxcuenta = 0;

	while ($myrow=DB_fetch_array($TransResult)) {
		$j = $j + 1;

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$RunningTotal += ($myrow['cargos'] + $myrow['abonos']);
		$PeriodTotal  += ($myrow['cargos'] + $myrow['abonos']);

		//LLEVA EL SALDO DE ESTA CUENTA...
		$saldoxcuenta += ($myrow['cargos'] + $myrow['abonos']);

		/*if($myrow['amount']>=0){*/
			$DebitAmount = number_format($myrow['cargos'],2);
			$CreditAmount = number_format($myrow['abonos']*-1,2);
			$saldoxcuentacargos = $saldoxcuentacargos + $myrow['cargos'];
			$saldoxcuentaabonos = $saldoxcuentaabonos + ($myrow['abonos']*-1);
			$saldoxcuentasuma = $saldoxcuentasuma + ($myrow['cargos']+$myrow['abonos']);
		/*} else {
			$CreditAmount = number_format(-$myrow['amount'],2);
			$DebitAmount = '';

		}*/



		printf("
			<td>%s</td>
			<td>%s</td>

			<td class=number>%s</td>
			<td class=number>%s</td>
			<td class=number nowrap><b>%s</b></td>
			<td class=number nowrap><b>%s</b></td>
			</tr>",
			$myrow['type'],
			$myrow['typename'],

			$DebitAmount,
			$CreditAmount,
			number_format(($myrow['cargos'] + $myrow['abonos']),2),
			number_format($saldoxcuenta,2));

	}

	//******************************************
	//******IMPRIME ULTIMO TOTAL DE CUENTA

	echo "<tr bgcolor='#FDFEEF'>
			<td colspan=2><b>" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';


		echo '	<td class=number nowrap><b>' . number_format($saldoxcuentacargos,2) . '</b></td>
			<td class=number nowrap><b>' . number_format($saldoxcuentaabonos,2) . '</b></td>
			<td></td>
			<td class=number nowrap><b>' . number_format($saldoxcuenta,2) . '</b></td>
			<td></td><td></td>
			</tr>';

	echo "<tr bgcolor='#FFFFFF'>
			<td colspan=5><b><br><br></b></td>";

	echo '<td></td>
		<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
	//******************************************


	echo "<tr bgcolor='#FDFEEF'><td></td><td></td><td></td><td colspan=2><b>";

		echo _('Total Movimientos del Periodo');
	echo '</b></td>';

	echo '<td></td><td align=right nowrap><b>' . number_format(($RunningTotal),2) . '</b></td><td colspan=2></td><td></td></tr>';

	echo '</table>';

} /* end of if Show button hit */


if (isset($ShowIntegrityReport) and $ShowIntegrityReport==True){
	if (!isset($IntegrityReport)) {$IntegrityReport='';}
	prnMsg( _('Existen diferencias entre el detalle de las transacciones y la informacion del detalle de acumulados de la cuenta en ChartDetails') . '. ' . _('Un registro de las diferencias se muestra abajo'),'warn');
	echo '<p>'.$IntegrityReport;
}

if (isset($_POST['PrintEXCEL'])) {
		exit;
	}

include('includes/footer.inc');
?>
