<?php


$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Edo. Resultados UNIDADES DE NEGOCIOS EN COLUMNAS');

$funcion=940;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

if (isset($_POST['FromPeriod']) and ($_POST['FromPeriod'] > $_POST['ToPeriod'])){
	prnMsg(_('El periodo seleccionado Desde es posterior al periodo Hasta') . '! ' . _('Favor de re-seleccionar rangos de periodos'),'error');
	$_POST['SelectADifferentPeriod']='Select A Different Period';
}

if ((!isset($_POST['FromPeriod']) AND !isset($_POST['ToPeriod'])) OR isset($_POST['SelectADifferentPeriod'])){

	include('includes/header.inc');
	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	if (Date('m') > $_SESSION['YearEnd']){
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}
	$period=GetPeriod($FromDate, $db);

	/*Show a form to allow input of criteria for profit and loss to show */
	echo '<table><tr><td>'._('Desde Periodo').":</td><td><select Name='FromPeriod'>";

	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	$Periods = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['periodno']){
				echo '<option selected VALUE=' . $myrow['periodno'] . '>' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<option VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<option selected VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<option VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		}
	}

	echo '</select></td></tr>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$lastDate = date("Y-m-d",mktime(0,0,0,Date('m')+1,0,Date('Y')));
		$sql = "SELECT periodno FROM periods where lastdate_in_period = '$lastDate'";
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultToPeriod = (int) ($MaxPrdrow[0]);

	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr><td>' . _('Hasta Periodo') . ":</td><td><select Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<option selected VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<option VALUE =' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</select></td></tr>';


	//Select the razon social
	echo '<tr><td>'._('Seleccione Una Razon Social:').'<td><select name="legalid">';

	echo '<option selected value=0>' . _('Todas a las que tengo acceso...');

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
	/************************************/
	/* SELECCION DEL DEPARTAMENT0 */
	echo '<tr><td>' . _('X Departamento') . ':' . "</td>
		<td><select tabindex='4' name='xDepto'>";

	$sql = "SELECT department as departamento, departments.u_department
			FROM departments
			JOIN tags ON tags.u_department = departments.u_department
			JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY departments.department";

	$result=DB_query($sql,$db);

	echo "<option selected value='0'>Todos los departamentos...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['u_department'] == $_POST['xDepto']){
			echo "<option selected value='" . $myrow["u_department"] . "'>" . $myrow['departamento'];
	      } else {
		      echo "<option value='" . $myrow['u_department'] . "'>" . $myrow['departamento'];
	      }
	}
	echo '</select></td></tr>';
	//Select the tag
	echo '<tr><td>'._('Seleccione Unidad de Negocio:').'<td><select name="tag">';

	///Pinta las unidades de negocio por usuario
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";

	$result=DB_query($SQL,$db);

	echo '<option selected value=0>' . _('Todas a las que tengo acceso...');

	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow["tagref"]){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select></td>';
	// End select tag

	echo '<tr><td>'._('Columnas Por').":</td><td><select Name='Columnas'>";
		echo "<option selected VALUE='Periodos'>"._('X Periodos');
		echo "<option VALUE='Razones'>"._('X Razon Social');
		echo "<option VALUE='Regiones'>"._('X Regiones');
		echo "<option VALUE='Areas'>"._('X Areas');
		echo "<option VALUE='Departamentos'>"._('X Departamentos');
		echo "<option VALUE='Tags'>"._('X Unidades de Negocios');
	echo '</select></td></tr>';

	echo '<tr><td>'._('Detalle o Resumen').":</td><td><select Name='Detail'>";
		echo "<option selected VALUE='Summary'>"._('Resumen');
		echo "<option VALUE='Detailed'>"._('Todas las Cuentas');
	echo '</select></td></tr>';

	echo '</table>';

	echo "<div class='centre'><input type=submit Name='ShowPL' Value='"._('Muestra Estado de Resultados por Unidad de Negocios')."'><br>";
	//echo "<input type=submit Name='PrintPDF' Value='"._('Estado de Resultados en PDF')."'><br>";
	//echo "<input type=submit Name='PrintEXCEL' Value='"._('Exporta a EXCEL')."'>";
	echo "</div>";

	/*Now do the posting while the user is thinking about the period to select */

	/*include ('includes/GLPostings.inc');*/
} else if (isset($_POST['PrintPDF'])) {


	$FromPeriod=$_POST['FromPeriod'];
	$ToPeriod=$_POST['ToPeriod'];
	$tag=$_POST['tag'];
	$legalid=$_POST['legalid'];
	$xArea=$_POST['xArea'];
	$xDepto=$_POST['xDepto'];
	$xRegion=$_POST['xRegion'];
	$legalid=$_POST['legalid'];
	$Columnas=$_POST['Columnas'];
	$Detail=$_POST['Detail'];

	$SQLGroupby='';


echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/PDFGLTagProfit_LossMultiColums.php?&FromPeriod=".$FromPeriod."
	&ToPeriod=".$ToPeriod."&tag=".$tag."&legalid=".$legalid."&xArea=".$xArea."&xDepto=".$xDepto."
	&xRegion=".$xRegion."&Columnas=".$Columnas."&Detail=".$Detail."'>";





} else {

	if (isset($_POST['PrintEXCEL'])) {

		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=excelreport.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';

		echo '<link href="css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
		echo '<script type="text/javascript" src = "' . $rootpath . '/javascripts/MiscFunctions.js"></script>';

	} else {
		include('includes/header.inc');
		echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
		echo "<input type=hidden name='FromPeriod' VALUE=" . $_POST['FromPeriod'] . "><input type=hidden name='ToPeriod' VALUE=" . $_POST['ToPeriod'] . '>';
	}



	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	if ($NumberOfMonths >12){
		echo '<p>';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
		include('includes/footer.inc');
		exit;
	}

	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);

	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['FromPeriod'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodFromDate = MonthAndYearFromSQLDate($myrow[0]);



	// FIN DE CALCULO DE INGRESOS...

	unset($_SESSION['IdColumnArray']);
	unset($_SESSION['ColumnNameArray']);

	unset($_SESSION['ColumnTotIngresos']);
	unset($_SESSION['SectionPrdActual']);
	unset($_SESSION['GrpPrdActual']);


	/* PROCESA LA LISTA DE COLUMNAS DE AUCERDO A LOS CRITERIOS DEL REPORTE */
	if ($_POST['Columnas'] == 'Periodos') {
		$SQL = 'SELECT periodno, lastdate_in_period
			FROM periods
			WHERE
			periodno >= ' . $_POST['FromPeriod'] .' and periodno <= ' . $_POST['ToPeriod'] . '
			ORDER BY periodno';
	} else {
		if ($_POST['Columnas'] == 'Tags')
			$SQL = 'SELECT t.tagref, t.tagname';
		elseif ($_POST['Columnas'] == 'Razones')
			$SQL = 'SELECT t.legalid, legalbusinessunit.legalname';
		elseif ($_POST['Columnas'] == 'Regiones')
			$SQL = 'SELECT regions.regioncode, regions.name';
		elseif ($_POST['Columnas'] == 'Areas')
			$SQL = 'SELECT areas.areacode, areas.areadescription';
		elseif ($_POST['Columnas'] == 'Departamentos')
			$SQL = 'SELECT departments.u_department, departments.department';



		$SQL = $SQL . '	FROM tags t JOIN sec_unegsxuser ON sec_unegsxuser.tagref = t.tagref
			JOIN areas ON t.areacode = areas.areacode
			JOIN regions ON areas.regioncode = regions.regioncode
			JOIN departments ON t.u_department=departments.u_department
				';

		$SQL = $SQL . '
			JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid

			WHERE (t.tagref='.$_POST['tag'].' OR "0"="'.$_POST['tag'].'")';

		$SQL = $SQL . '
			and (areas.regioncode = '.$_POST['xRegion'].' or '.$_POST['xRegion'].'=0)
			and (areas.areacode = '.$_POST['xArea'].' or '.$_POST['xArea'].'=0)
			and (departments.u_department = '.$_POST['xDepto'].' or '.$_POST['xDepto'].'=0)';

		$SQL = $SQL . '
			AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			AND (t.legalid = "'.$_POST['legalid'].'" OR "'.$_POST['legalid'].'" = "0")';

		if ($_POST['Columnas'] == 'Tags')
			$SQL = $SQL .'  GROUP BY t.tagref, t.tagname
					ORDER BY t.tagref';
		elseif ($_POST['Columnas'] == 'Razones')
			$SQL = $SQL . ' GROUP BY t.legalid, legalbusinessunit.legalname
					ORDER BY t.legalid';
		elseif ($_POST['Columnas'] == 'Regiones')
			$SQL = $SQL . ' GROUP BY regions.regioncode, regions.name
				 ORDER BY regions.regioncode';
		elseif ($_POST['Columnas'] == 'Areas')
			$SQL = $SQL . ' GROUP BY areas.areacode, areas.areadescription
				 ORDER BY areas.areacode';
		elseif ($_POST['Columnas'] == 'Departamentos')
			$SQL = $SQL . ' GROUP BY departments.u_department, departments.department
				 ORDER BY departments.u_department';
	}

	//echo $SQL.'<br>';



	$result = DB_query($SQL,$db);
	$i=0;
	while ($Act = DB_fetch_row($result)){
		$_SESSION['IdColumnArray'][$i]= $Act[0];
		$_SESSION['ColumnNameArray'][$i]= $Act[1];

		$_SESSION['ColumnTotIngresos'][$i]= 0;

		$_SESSION['SectionPrdActual'][$i]= array(0);
		$_SESSION['GrpPrdActual'][$i]= array(0);

		$montoTotalIngresos[$i] = 0;

		$TotalIncome[$i] = 0;
		$PeriodProfitLoss[$i] = 0;
		$UtilidadAcumulada[$i] = 0;

		$i++;
	}


	// PRIMERO CALCULA EL TOTAL DE INGRESOS PARA PODER CALCULAR PORCENTAJES CONTRA ESTE MONTO
	if ($_POST['Columnas'] == 'Periodos')
		$SQL = 'SELECT accountsection.sectiontype, gltrans.periodno as id, periods.lastdate_in_period as nombre,';
	elseif ($_POST['Columnas'] == 'Razones')
		$SQL = 'SELECT accountsection.sectiontype, t.legalid as id, legalbusinessunit.legalname as nombre,';
	elseif ($_POST['Columnas'] == 'Tags')
		$SQL = 'SELECT accountsection.sectiontype, t.tagref as id, t.tagname as nombre,';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = 'SELECT accountsection.sectiontype, regions.regioncode as id, regions.name as nombre,';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = 'SELECT accountsection.sectiontype, areas.areacode as id, areas.areadescription as nombre,';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = 'SELECT accountsection.sectiontype, departments.u_department as id, departments.department as nombre,';

	$SQL = $SQL . 'Sum(CASE WHEN (gltrans.periodno>=' . $_POST['FromPeriod'] . ' and gltrans.periodno<=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
		Sum(CASE WHEN (gltrans.periodno=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN gltrans
		ON chartmaster.accountcode= gltrans.account INNER JOIN accountsection
		ON accountgroups.sectioninaccounts = accountsection.sectionid
		JOIN sec_unegsxuser ON  gltrans.tag = sec_unegsxuser.tagref
		JOIN tags t ON sec_unegsxuser.tagref = t.tagref
		JOIN areas ON t.areacode = areas.areacode
		JOIN regions ON areas.regioncode = regions.regioncode
		JOIN departments ON t.u_department=departments.u_department
		JOIN periods ON gltrans.periodno = periods.periodno ';

	$SQL = $SQL . '
		JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid


		WHERE accountgroups.pandl=1 and accountsection.sectiontype = 1
			AND gltrans.periodno >= ' . $_POST['FromPeriod'] .' and gltrans.periodno <= ' . $_POST['ToPeriod'] . '
			AND (gltrans.tag='.$_POST['tag'].' OR "0"="'.$_POST['tag'].'")
			and (areas.regioncode = '.$_POST['xRegion'].' or '.$_POST['xRegion'].'=0)
			and (areas.areacode = '.$_POST['xArea'].' or '.$_POST['xArea'].'=0)
			and (departments.u_department = '.$_POST['xDepto'].' or '.$_POST['xDepto'].'=0)';

	$SQL = $SQL . '
			AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			AND gltrans.type <> 999
			AND (t.legalid = "'.$_POST['legalid'].'" OR "'.$_POST['legalid'].'" = "0")';


	if ($_POST['Columnas'] == 'Periodos')
		$SQL = $SQL .' GROUP BY accountsection.sectiontype, gltrans.periodno, periods.lastdate_in_period
				ORDER BY gltrans.periodno';
	elseif ($_POST['Columnas'] == 'Razones')
		$SQL = $SQL . ' GROUP BY accountsection.sectiontype, t.legalid, legalbusinessunit.legalname
				ORDER BY t.legalid';
	elseif ($_POST['Columnas'] == 'Tags')
		$SQL = $SQL .'  GROUP BY accountsection.sectiontype, t.tagref, t.tagname
				ORDER BY t.tagref';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .' GROUP BY accountsection.sectiontype, regions.regioncode, regions.name
			 ORDER BY regions.regioncode';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .' GROUP BY accountsection.sectiontype, areas.areacode, areas.areadescription
			 ORDER BY areas.areacode';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .' GROUP BY accountsection.sectiontype, departments.u_department, departments.department
			 ORDER BY departments.u_department';

	$count = count($_SESSION['IdColumnArray']);
	$result = DB_query($SQL,$db);
	$jr=0;

	//echo $SQL.'<br>';

	$montoTotalIngresosGral = 0;
	while ($myrow = DB_fetch_row($result)){

		/* SIEMPRE QUE EL CONTADOR SEA MENOR QUE LE NUMERO MAXIMO DE ELEMETOS DEL ARREGLO */
		for ($i=$jr; $i < $count; $i++) {
			if ($myrow[1] == $_SESSION['IdColumnArray'][$i]) {
				$montoTotalIngresos[$i] = -$myrow[3];
				$montoTotalIngresosGral = $montoTotalIngresosGral + (-$myrow[3]);

				$jr = $i;
				break;
			}
		}
	}

	$SQL = 'SELECT  legalbusinessunit.legalname,
			accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			gltrans.account ,
			chartmaster.accountname,
			chartmaster.naturaleza, ';

	if ($_POST['Columnas'] == 'Periodos')
		$SQL = $SQL .' accountsection.sectiontype, gltrans.periodno as columna,';
	elseif ($_POST['Columnas'] == 'Razones')
		$SQL = $SQL . ' accountsection.sectiontype, t.legalid as columna,';
	elseif ($_POST['Columnas'] == 'Tags')
		$SQL = $SQL .' accountsection.sectiontype, t.tagref as columna,';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .' accountsection.sectiontype, regions.regioncode as columna,';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .' accountsection.sectiontype, areas.areacode as columna,';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .' accountsection.sectiontype, departments.u_department as columna,';

	$SQL = $SQL . ' Sum(CASE WHEN (gltrans.periodno>=' . $_POST['FromPeriod'] . ' and gltrans.periodno<=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
			Sum(CASE WHEN (gltrans.periodno=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN gltrans
		ON chartmaster.accountcode= gltrans.account INNER JOIN accountsection
		ON accountgroups.sectioninaccounts = accountsection.sectionid
		JOIN sec_unegsxuser ON  gltrans.tag = sec_unegsxuser.tagref
		JOIN tags t ON sec_unegsxuser.tagref = t.tagref
		JOIN areas ON t.areacode = areas.areacode
		JOIN regions ON areas.regioncode = regions.regioncode
		JOIN departments ON t.u_department=departments.u_department';

	$SQL = $SQL . '
		JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid

		WHERE accountgroups.pandl=1
			AND gltrans.periodno >= ' . $_POST['FromPeriod'] .' and gltrans.periodno <= ' . $_POST['ToPeriod'] . '
			AND (gltrans.tag='.$_POST['tag'].' OR "0"="'.$_POST['tag'].'")
			and (areas.regioncode = '.$_POST['xRegion'].' or '.$_POST['xRegion'].'=0)
			and (areas.areacode = '.$_POST['xArea'].' or '.$_POST['xArea'].'=0)
			and (departments.u_department = '.$_POST['xDepto'].' or '.$_POST['xDepto'].'=0)';

	$SQL = $SQL . '
			AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			AND (t.legalid = "'.$_POST['legalid'].'" OR "'.$_POST['legalid'].'" = "0")
			AND gltrans.type <> 999

		GROUP BY legalbusinessunit.legalname,
			accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			gltrans.account,
			chartmaster.accountname,
			chartmaster.naturaleza,
			accountsection.sectiontype,';

	if ($_POST['Columnas'] == 'Periodos')
		$SQL = $SQL .'  gltrans.periodno';
	elseif ($_POST['Columnas'] == 'Razones')
		$SQL = $SQL . ' t.legalid';
	elseif ($_POST['Columnas'] == 'Tags')
		$SQL = $SQL .'  t.tagref';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .'  regions.regioncode';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .'  areas.areacode';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .'  departments.u_department';

	$SQL = $SQL . '
		ORDER BY accountgroups.sectioninaccounts,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			gltrans.account,
			';

	if ($_POST['Columnas'] == 'Periodos')
		$SQL = $SQL .'  gltrans.periodno';
	elseif ($_POST['Columnas'] == 'Razones')
		$SQL = $SQL . ' t.legalid';
	elseif ($_POST['Columnas'] == 'Tags')
		$SQL = $SQL .'  t.tagref';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .'  regions.regioncode';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .'  areas.areacode';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .'  departments.u_department';

	//echo $SQL.'<br>';
	if($_SESSION['UserID'] == "admin"){
		echo '<pre>'.$SQL;
	}

	$AccountsResult = DB_query($SQL,$db,_('No general ledger accounts were returned by the SQL because'),_('The SQL that failed was'));

	$sql='SELECT tagdescription FROM tags WHERE tagref='.$_POST['tag'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$n_unidaddenegocio = $myrow[0];

	$sql='SELECT name FROM regions WHERE regioncode='.$_POST['xRegion'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$n_region = $myrow[0];

	$sql='SELECT areadescription FROM areas WHERE areacode='.$_POST['xArea'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$n_area = $myrow[0];

	$sql='SELECT department FROM departments WHERE u_department='.$_POST['xDepto'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$n_departamento = $myrow[0];

	$sql='SELECT legalname FROM legalbusinessunit WHERE legalid='.$_POST['legalid'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
	$n_razonsocial = $myrow[0];

	echo '<div class="centre"><font size=4 color=BLUE><b>' . _('Estado de Resultados') . '<br>'.$n_razonsocial;

	if ($_POST['xRegion'] != 0) {
		echo '<br>REGION:'.$n_region;
	}

	if ($_POST['xArea'] != 0) {
		echo '<br>AREA:'.$n_area;
	}

	if ($_POST['xDepto'] != 0) {
		echo '<br>DEPARTAMENTO:'.$n_departamento;
	}

	if ($_POST['tag'] != 0) {
		echo '<br>UNIDAD NEGOCIOS:' . $n_unidaddenegocio;
	}
	//setlocale(LC_TIME,'spanish');
	//strftime("%B");
	echo '<br>'. _('De:') . ' ' . $PeriodFromDate . ' ' . _('<br>A:'). ' ' . $PeriodToDate . '</b></font></div><br>';

	/* show a table of the accounts info returned by the SQL
	Account Code , Account Name, Month Actual, Month Budget, Period Actual, Period Budget */

	echo '<table cellpadding=2 border=0>';

	$separador = "<td style='width=5px;background-color:green'></td>";
	$separador2 = "<td></td>";


	$TableHeader = "<tr>
			<th>"._('Cuenta')."</th>
			<th>"._('Nombre')."</th>";

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno >= " . $_POST['FromPeriod'] . "
	and periodno <= " . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);

	$i = 0;
	while ($myrowp=DB_fetch_array($PrdResult)){
		$i = $i + 1;
		if ($i == 1){
			$colspan = 3;
		}else{
			$colspan = 2;
		}
		$fechaperiodo = MonthAndYearFromSQLDate($myrowp[0]);
		$TableHeader = $TableHeader . "<th colspan='" . $colspan . "'>".$fechaperiodo."</th>
			<th>"._('%')."</th>".$separador."</th>";
	}



	$TableHeader = $TableHeader ."<th colspan=2><font size=3><b>"._('TOTALES')."</b></font></th>
			<th>"._('%')."</th>
			</tr>";

	/////////////////////////////////////////////////////////////////////////////////////////////////
	$TableHeader2 = "<tr>
			<th>"._('Cuenta')."</th>
			<th>"._('Nombre')."</th>
			<th></th>";

	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {
		$TableHeader2 = $TableHeader2 ."<th colspan=2><b>".$_SESSION['ColumnNameArray'][$i]."</b></th>
						<th>"._('%')."</th>".$separador."";
	}

	$TableHeader2 = $TableHeader2 ."<th colspan=2><font size=3><b>"._('TOTALES')."</b></font></th>
			<th>"._('%')."</th>
			</tr>";






	/*$TableHeader2 = "<tr>
			<th>"._('Cuenta')."</th>
			<th>"._('Nombre')."</th>
			<th></th>";

	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {
		$TableHeader2 = $TableHeader2 ."<th colspan=2><b>Monto</b></th>
						<th>"._('%')."</th>".$separador."";
	}

	$TableHeader2 = $TableHeader2 ."<th colspan=2><font size=3><b>"._('TOTALES')."</b></font></th>
			<th>"._('%')."</th>
			</tr>";*/

	$TableFooter = "<tr style='height:2px;background-color:#A0A0A0'>
			<td></td>
			<td></td>
			<td></td>";



	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {
		$TableFooter = $TableFooter ."<td></td>
						<td></td>
						<td></td>".$separador."";
	}

	$TableFooter = $TableFooter ."<td></td>
						<td></td>
						<td></td>
					</tr>";

	$j = 1;
	$k=0; //row colour counter
	$Section='';
	$SectionType = '';

	$ActGrp ='';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level]='';

	/* INICIALIZA VARIABLES DE ACUMULADO GENERAL DE COLUMNA DE TOTALES */
	$SectionPrdActualGral = array(0);
	$GrpPrdActualGral = array(0);

	$PeriodProfitLossGral = 0;
	$UtilidadAcumuladaGral = 0;


	$FirstTimeIn = 1;

	if($_POST['Columnas']=='Periodos'){
		echo $TableHeader;
	}else{
		echo $TableHeader2;
	}

	while ($myrow=DB_fetch_array($AccountsResult)) {


		if ($myrow['groupname']!= $ActGrp){
			if ($myrow['parentgroupname']!=$ActGrp AND $ActGrp!=''){


				/* SI ES LA PRIMERA LINEA DE LA CONSULTA NO COMPLETES COLUMNAS */
				if ($FirstTimeIn == 0 AND $_POST['Detail']=='Detailed') {
					$count = count($_SESSION['IdColumnArray']);
					if ($LastColumnDisplayed != $_SESSION['IdColumnArray'][$count-1]) {
						for ($i = 0; $i < $count; $i++) {
							if ($LastColumnDisplayed < $_SESSION['IdColumnArray'][$i]) {
								echo '<td></td><td></td><td></td>'.$separador.'';
							}
						}

						if ($LastSectionType == 1)
							echo '<td></td><td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
						else
							echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td><td></td>';

						if ( $montoTotalIngresosGral == 0)
							echo '<td class=number>'.number_format(0*100,2).'%</td>';
						else
							echo '<td class=number>'.number_format(abs($AccountPeriodActualGral)/$montoTotalIngresosGral*100,2).'%</td>';


						$AccountPeriodActualGral = 0;
						echo '</tr>';
					}
				}
				$FirstTimeIn = 0;

				while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
					if ($_POST['Detail']=='Detailed'){

						/* Despliega una linea horizontan antes de sub totalizar */
						echo $TableFooter;

						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('<b>SUB TOTAL</b>');
					} else {
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
					}

					if ($SectionType == 1){ /*Income */
						printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
							$ActGrpLabel);

						/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
						$count = count($_SESSION['IdColumnArray']);
						for ($i = 0; $i < $count; $i++) {
							printf('<td></td>
								<td class=number><b>%s</b></td>
								<td class=number>%s</td>'.$separador.'',
								number_format($GrpPrdActual[$i][$Level]),
								number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
						}

						printf('<td></td>
							<td class=number><b>%s</b></td>
							<td class=number>%s</td>',
							number_format($GrpPrdActualGral[$Level]),
							number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
						echo '</tr>';

					} else { /*Costs */
						printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
							$ActGrpLabel);

						/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
						$count = count($_SESSION['IdColumnArray']);
						for ($i = 0; $i < $count; $i++) {
							if ($montoTotalIngresos[$i] ==0){
								$montoti = 0;
							}else{
								$montoti = $GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100;
							}
							printf('<td class=number><b>%s</b></td>
								<td></td>
								<td class=number>%s</td>'.$separador.'',
								number_format($GrpPrdActual[$i][$Level]),
								number_format($montoti,2)."%");
						}
						printf('<td class=number><b>%s</b></td>
							<td></td>
							<td class=number>%s</td>',
							number_format($GrpPrdActualGral[$Level]),
							number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
						echo '</tr>';
					}

					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {
						$GrpPrdActual[$i][$Level] = 0;
					}
					$GrpPrdActualGral[$Level] = 0;

					$ParentGroups[$Level] ='';
					$Level--;

				}//end while

				//still need to print out the old group totals
				if ($_POST['Detail']=='Detailed'){

					/* Despliega una linea horizontan antes de sub totalizar */
					echo $TableFooter;

					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('<b>SUB TOTAL</b>');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}

				if ($SectionType ==1){ /*Income */
					printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
						$ActGrpLabel);

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {
						if ($montoTotalIngresos[$i] != 0)
						printf('<td></td>
							<td class=number><b>%s</b></td>
							<td class=number>%s</td>'.$separador.'',
							number_format(-$GrpPrdActual[$i][$Level]),
							number_format(-$GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
						else
						printf('<td></td>
							<td class=number><b>%s</b></td>
							<td class=number>%s</td>'.$separador.'',
							number_format(-$GrpPrdActual[$i][$Level]),
							number_format(0,2)."%");
					}
					if ($montoTotalIngresosGral != 0)
					printf('<td></td>
						<td class=number><b>%s</b></td>
						<td class=number>%s</td>',
						number_format(-$GrpPrdActualGral[$Level]),
						number_format(-$GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
					else
					printf('<td></td>
						<td class=number><b>%s</b></td>
						<td class=number>%s</td>',
						number_format(-$GrpPrdActualGral[$Level]),
						number_format(0,2)."%");
					echo '</tr>';

				} else { /*Costs */
					printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
						$ActGrpLabel);

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {
						if($montoTotalIngresos[$i] != 0)
						printf('<td class=number><b>%s</b></td>
							<td></td>
							<td class=number>%s</td>'.$separador.'',
							number_format($GrpPrdActual[$i][$Level]),
							number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
						else
						printf('<td class=number><b>%s</b></td>
							<td></td>
							<td class=number>%s</td>'.$separador.'',
							number_format($GrpPrdActual[$i][$Level]),
							number_format(0,2)."%");
					}
					if ($montoTotalIngresosGral != 0)
					printf('<td class=number><b>%s</b></td>
						<td></td>
						<td class=number>%s</td>',
						number_format($GrpPrdActualGral[$Level]),
						number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
					else
					printf('<td class=number><b>%s</b></td>
						<td></td>
						<td class=number>%s</td>',
						number_format($GrpPrdActualGral[$Level]),
						number_format(0,2)."%");
					echo '</tr>';
				}

				$count = count($_SESSION['IdColumnArray']);
				for ($i = 0; $i < $count; $i++) {
					$GrpPrdActual[$i][$Level] = 0;
				}
				$GrpPrdActualGral[$Level] = 0;
				$ParentGroups[$Level] ='';
			}
			$j++;
		}

		if ($myrow['sectioninaccounts']!= $Section){

			$algunTotal = 0;
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {
				if ($SectionPrdActual[$i][$Level] != 0) {
					$algunTotal = $SectionPrdActual[$i][$Level];
					break;
				}
			}

			if ($algunTotal !=0){

				if ($SectionType == 1) { /*Income*/

					/* Despliega una linea horizontan antes de sub totalizar */
					echo $TableFooter;

					printf('<tr style="background-color:yellow">
						<td colspan=3><font size=3><b>%s</b></font></td>',
						$Sections[$Section]);

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {
						if ($montoTotalIngresos[$i] != 0)
						printf('<td></td>
							<td class=number><font size=2><b>%s</b></font></td>
							<td class=number><font size=2>%s</font></td>'.$separador.'',
							number_format(-$SectionPrdActual[$i][$Level]),
							number_format(-$SectionPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
						else
						printf('<td></td>
							<td class=number><font size=2><b>%s</b></font></td>
							<td class=number><font size=2>%s</font></td>'.$separador.'',
							number_format(-$SectionPrdActual[$i][$Level]),
							number_format(0,2)."%");
						/* Solo incrementa el Ingreso en esta seccion para tener totalizados los ingresos para margenes brutos */
						$TotalIncome[$i] = $TotalIncome[$i] - $SectionPrdActual[$i][$Level];

						$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
						$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActual[$i][$Level];
					}
					if ($montoTotalIngresosGral != 0)
					printf('<td></td>
						<td class=number><font size=2><b>%s</b></font></td>
						<td class=number><font size=2>%s</font></td>',
						number_format(-$SectionPrdActualGral[$Level]),
						number_format(-$SectionPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
					else
					printf('<td></td>
						<td class=number><font size=2><b>%s</b></font></td>
						<td class=number><font size=2>%s</font></td>',
						number_format(-$SectionPrdActualGral[$Level]),
						number_format(0,2)."%");
					echo '</tr>';

					echo '<tr>
						<td colspan='.($count*4+3).'><br><br></td>
						</tr>';

				} else {

					/* Despliega una linea horizontan antes de sub totalizar */
					echo $TableFooter;

					printf('<tr style="background-color:yellow">
						<td colspan=3><font size=3><b>%s</b></font></td>',
						$Sections[$Section]);

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {

						if($montoTotalIngresos[$i] != 0)
						printf('<td class=number><font size=2><b>%s</b></font></td>
							<td></td>
							<td class=number><font size=2>%s</font></td>'.$separador.'',
							number_format($SectionPrdActual[$i][$Level]),
							number_format($SectionPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
						else
						printf('<td class=number><font size=2><b>%s</b></font></td>
							<td></td>
							<td class=number><font size=2>%s</font></td>'.$separador.'',
							number_format($SectionPrdActual[$i][$Level]),
							number_format(0,2)."%");

						$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
						$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActual[$i][$Level];
					}
					if($montoTotalIngresosGral != 0)
					printf('<td class=number><font size=2><b>%s</b></font></td>
						<td></td>
						<td class=number><font size=2>%s</font></td>',
						number_format($SectionPrdActualGral[$Level]),
						number_format($SectionPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
					else
					printf('<td class=number><font size=2><b>%s</b></font></td>
						<td></td>
						<td class=number><font size=2>%s</font></td>',
						number_format($SectionPrdActualGral[$Level]),
						number_format(0,2)."%");
					echo '</tr>';

					echo '<tr>
						<td colspan='.($count*4+3).'><br><br></td>
						</tr>';
				}

				if ($SectionType == 2){ /*Cost of Sales - need sub total for Gross Profit*/

					/* Despliega una linea horizontan antes de sub totalizar */
					echo $TableFooter;



					printf('<tr style="background-color:orange">
						<td colspan=3><font size=3><b>%s</b></font></td>',
						_('Utilidad Bruta'));

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {

						if ($TotalIncome[$i] !=0){
							$PrdGPPercent = 100*($UtilidadAcumulada[$i]*-1)/($TotalIncome[$i]*-1);
						} else {
							$PrdGPPercent =0;
						}

						if ($UtilidadAcumulada[$i] > 0) {
							printf('<td class=number><font size=2><b>%s</b></font></td>
								<td></td>
								<td class=number><font size=2>%s</font></td>'.$separador.'',
								number_format($UtilidadAcumulada[$i]),
								number_format($PrdGPPercent,2)."%");
						} else {
							printf('<td></td>
								<td class=number><font size=2><b>%s</b></font></td>
								<td class=number><font size=2>%s</font></td>'.$separador.'',
								number_format($UtilidadAcumulada[$i]*-1),
								number_format($PrdGPPercent*-1,2)."%");
						}
					}

					if ($montoTotalIngresosGral !=0){
						$PrdGPPercent = 100*($UtilidadAcumuladaGral*-1)/($montoTotalIngresosGral*-1);
					} else {
						$PrdGPPercent =0;
					}

					if ($UtilidadAcumuladaGral > 0) {
						printf('<td class=number><font size=2><b>%s</b></font></td>
							<td></td>
							<td class=number><font size=2>%s</font></td>',
							number_format($UtilidadAcumuladaGral),
							number_format($PrdGPPercent,2)."%");
					} else {
						printf('<td></td>
							<td class=number><font size=2><b>%s</b></font></td>
							<td class=number><font size=2>%s</font></td>',
							number_format($UtilidadAcumuladaGral*-1),
							number_format($PrdGPPercent*-1,2)."%");
					}

					echo '</tr>';

					echo '<tr>
						<td colspan='.($count*4+3).'><br><br></td>
						</tr>';

					$j++;
				}
			}
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {
				$SectionPrdActual[$i][$Level] = 0;
			}
			$SectionPrdActualGral[$Level] = 0;

			$Section = $myrow['sectioninaccounts'];
			$SectionType = $myrow['sectiontype'];

			if ($_POST['Detail']=='Detailed'){
				// ESTE IMPRIME EL COMIENZO DE UNA CUENTA DE TITULO
				printf('<tr>
					<td colspan=9><font size=4 color=BLUE><b>%s</b></font></td>
					</tr>',
					$Sections[$myrow['sectioninaccounts']]);

				//echo $TableHeader;
			} else {
				//echo $TableHeader;
			}
			$j++;

		}

		//var_dump($_SESSION['IdColumnArray']);

		if ($myrow['groupname']!= $ActGrp){

			if ($myrow['parentgroupname']==$ActGrp AND $ActGrp !=''){ //adding another level of nesting
				$Level++;
			}

			$ParentGroups[$Level] = $myrow['groupname'];
			$ActGrp = $myrow['groupname'];
			if ($_POST['Detail']=='Detailed'){
				printf('<tr>
					<td colspan=9><font size=2 color=BLUE><b>%s</b></font></td>
					</tr>',
					$myrow['groupname']);
					//echo $TableHeader2;

					$FirstTimeIn = 1;
			}
		}


		if ($_POST['Detail']==_('Detailed')){

			$ActEnquiryURL = "<a target=blank_ href='$rootpath/GLAccountInquiryMany.php?" . SID . 'legalid=' . $_POST['legalid'] .'&tag=' . $_POST['tag'] .'&xRegion=' . $_POST['xRegion'] .'&FromPeriod=' . $_POST['FromPeriod'] .'&ToPeriod=' . $_POST['ToPeriod'] . '&Account=' . $myrow['account'] . "&Show=Yes'>" . $myrow['account'] . '<a>';

			if ($myrow['accountname'] != $lastAccountDisplayed) {

				/* SI ES LA PRIMERA LINEA DE LA CONSULTA NO COMPLETES COLUMNAS */
				if ($FirstTimeIn == 0 AND $_POST['Detail']=='Detailed') {
					$count = count($_SESSION['IdColumnArray']);
					if ($LastColumnDisplayed != $_SESSION['IdColumnArray'][$count-1]) {
						for ($i = 0; $i < $count; $i++) {
							if ($LastColumnDisplayed < $_SESSION['IdColumnArray'][$i]) {
								echo '<td></td><td></td><td></td>'.$separador.'';
							}
						}

						if ($LastSectionType == 1)
							echo '<td></td><td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
						else
							echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td><td></td>';

						if ( $montoTotalIngresosGral == 0)
							echo '<td class=number>'.number_format(0*100,2).'%</td>';
						else
							echo '<td class=number>'.number_format(abs($AccountPeriodActualGral)/$montoTotalIngresosGral*100,2).'%</td>';


						$AccountPeriodActualGral = 0;
						echo '</tr>';
					}
				}
				$FirstTimeIn = 0;

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}
				printf('<td>%s</td>
					<td nowrap>%s</td>
					<td></td>',
					$ActEnquiryURL,
					$myrow['accountname']
					);

				$LastColumnDisplayed = 0;
			}
		}

		$AccountPeriodActual = $myrow['TotalAllPeriods'];
		$AccountPeriodActualGral = $AccountPeriodActualGral + $AccountPeriodActual;

		for ($i=0;$i<=$Level;$i++){
			if (!isset($GrpPrdActualGral[$i]))
				{$GrpPrdActualGral[$i]=0;}

			if (!isset($SectionPrdActualGral[$i]))
				{$SectionPrdActualGral[$i]=0;}

			$GrpPrdActualGral[$i] +=$AccountPeriodActual;
			$SectionPrdActualGral[$i] +=$AccountPeriodActual;
		}

		$count = count($_SESSION['IdColumnArray']);
		for ($jk = 0; $jk < $count; $jk++) {
			if ($myrow['columna'] == $_SESSION['IdColumnArray'][$jk]) {
				for ($i=0;$i<=$Level;$i++){
					if (!isset($GrpPrdActual[$jk][$i]))
						{$GrpPrdActual[$jk][$i]=0;}

					if (!isset($SectionPrdActual[$jk][$i]))
						{$SectionPrdActual[$jk][$i]=0;}

					$GrpPrdActual[$jk][$i] +=$AccountPeriodActual;
					$SectionPrdActual[$jk][$i] +=$AccountPeriodActual;
				}

				$PeriodProfitLoss[$jk] +=$AccountPeriodActual;
			}
		}

		$PeriodProfitLossGral +=$AccountPeriodActual;
		$flag = 1;
		if ($_POST['Detail']==_('Detailed')){

			if ($SectionType == 1){

				$count = count($_SESSION['IdColumnArray']);

				for ($i = 0; $i < $count; $i++) {
					if ($myrow['columna'] == $_SESSION['IdColumnArray'][$i]) {

						echo '<td></td><td class=number>'.number_format(-$AccountPeriodActual).'</td>';
						if ( $montoTotalIngresos[$i] == 0)
							echo '<td class=number>'.number_format(0*100,2).'%</td>';
						else
							echo '<td class=number>'.number_format(-$AccountPeriodActual/$montoTotalIngresos[$i]*100,2).'%</td>';

						echo $separador;
						break;

						} elseif ($myrow['columna'] > $_SESSION['IdColumnArray'][$i] AND $LastColumnDisplayed < $_SESSION['IdColumnArray'][$i]) {
						echo '<td></td><td></td><td></td>'.$separador.'';

					} elseif($lastAccountDisplayed <> $myrow['accountname']){
						echo '<td></td><td></td><td></td>'.$separador.'';
					}
				}

				if ($i == $count-1) {
					echo '<td></td><td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
					if ( $montoTotalIngresosGral == 0)
						echo '<td class=number>'.number_format(0*100,2).'%</td>';
					else
						echo '<td class=number>'.number_format(-$AccountPeriodActualGral/$montoTotalIngresosGral*100,2).'%</td>';

					$AccountPeriodActualGral = 0;
					echo '</tr>';
				}

			} else {

				$count = count($_SESSION['IdColumnArray']);

				for ($i = 0; $i < $count; $i++) {
					if ($myrow['columna'] == $_SESSION['IdColumnArray'][$i]) {

						echo '<td class=number>'.number_format($AccountPeriodActual).'</td><td></td>';
						if ( $montoTotalIngresos[$i] == 0)
							echo '<td class=number>'.number_format(0*100,2).'%</td>';
						else
							echo '<td class=number>'.number_format($AccountPeriodActual/$montoTotalIngresos[$i]*100,2).'%</td>';

						echo $separador;
						break;

						} elseif ($myrow['columna'] > $_SESSION['IdColumnArray'][$i] AND $LastColumnDisplayed < $_SESSION['IdColumnArray'][$i]) {

						echo '<td></td><td></td><td></td>'.$separador.'';
					} elseif($lastAccountDisplayed <> $myrow['accountname']){

						echo '<td></td><td></td><td></td>'.$separador.'';

					}
				}

				if ($i == $count-1) {
					echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td><td></td>';
						if ( $montoTotalIngresosGral == 0)
							echo '<td class=number>'.number_format(0*100,2).'%</td>';
						else
							echo '<td class=number>'.number_format($AccountPeriodActualGral/$montoTotalIngresosGral*100,2).'%</td>';
					echo '</tr>';
					$AccountPeriodActualGral = 0;
				}
			}

			$lastAccountDisplayed = $myrow['accountname'];
			$LastColumnDisplayed = $myrow['columna'];
			$LastSectionType = $SectionType;

			$j++;
		}
	}
	//end of loop



	/* SI ES LA PRIMERA LINEA DE LA CONSULTA NO COMPLETES COLUMNAS */

	if ($_POST['Detail']=='Detailed') {
		$count = count($_SESSION['IdColumnArray']);
		if ($LastColumnDisplayed != $_SESSION['IdColumnArray'][$count-1]) {
			for ($i = 0; $i < $count; $i++) {
				if ($LastColumnDisplayed < $_SESSION['IdColumnArray'][$i]) {
					echo '<td></td><td></td><td></td>'.$separador.'';
				}
			}

			if ($LastSectionType == 1)
				echo '<td></td><td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
			else
				echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td><td></td>';

			if ( $montoTotalIngresosGral == 0)
				echo '<td class=number>'.number_format(0*100,2).'%</td>';
			else
				echo '<td class=number>'.number_format(abs($AccountPeriodActualGral)/$montoTotalIngresosGral*100,2).'%</td>';


			$AccountPeriodActualGral = 0;
			echo '</tr>';
		}
	}

	while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
		if ($_POST['Detail']=='Detailed'){

			/* Despliega una linea horizontan antes de sub totalizar */
			echo $TableFooter;

			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('<b>SUB TOTAL</b>');
		} else {
			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
		}

		if ($SectionType == 1){ /*Income */
			printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
				$ActGrpLabel);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {
				printf('<td></td>
					<td class=number><b>%s</b></td>
					<td class=number>%s</td>'.$separador.'',
					number_format($GrpPrdActual[$i][$Level]),
					number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
			}

			printf('<td></td>
					<td class=number><b>%s</b></td>
					<td class=number>%s</td>',
					number_format($GrpPrdActualGral[$Level]),
					number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
			echo '</tr>';

		} else { /*Costs */
			printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
				$ActGrpLabel);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {
				printf('<td class=number><b>%s</b></td>
					<td></td>
					<td class=number>%s</td>'.$separador.'',
					number_format($GrpPrdActual[$i][$Level]),
					number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
			}
			printf('<td class=number><b>%s</b></td>
					<td></td>
					<td class=number>%s</td>',
					number_format($GrpPrdActualGral[$Level]),
					number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
			echo '</tr>';
		}

		$count = count($_SESSION['IdColumnArray']);
		for ($i = 0; $i < $count; $i++) {
			$GrpPrdActual[$i][$Level] = 0;
		}
		$GrpPrdActualGral[$Level] = 0;
		$ParentGroups[$Level] ='';
		$Level--;
	}//end while

	//still need to print out the old group totals
	if ($_POST['Detail']=='Detailed'){

			/* Despliega una linea horizontan antes de sub totalizar */
			echo $TableFooter;

			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('<b>SUB TOTAL</b>');
		} else {
			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
		}

	if ($SectionType == 1){ /*Income */
		printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
			$ActGrpLabel);

		/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
		$count = count($_SESSION['IdColumnArray']);
		for ($i = 0; $i < $count; $i++) {
			if($montoTotalIngresos[$i] != 0)
			printf('<td></td>
				<td class=number><b>%s</b></td>
				<td class=number>%s</td>'.$separador.'',
				number_format($GrpPrdActual[$i][$Level]),
				number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
			else
			printf('<td></td>
				<td class=number><b>%s</b></td>
				<td class=number>%s</td>'.$separador.'',
				number_format($GrpPrdActual[$i][$Level]),
				number_format(0,2)."%");
		}
		printf('<td></td>
				<td class=number><b>%s</b></td>
				<td class=number>%s</td>',
				number_format($GrpPrdActualGral[$Level]),
				number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
		echo '</tr>';

	} else { /*Costs */
		printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
			$ActGrpLabel);

		/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
		$count = count($_SESSION['IdColumnArray']);
		for ($i = 0; $i < $count; $i++) {
			if($montoTotalIngresos[$i] != 0)
			printf('<td class=number><b>%s</b></td>
				<td></td>
				<td class=number>%s</td>'.$separador.'',
				number_format($GrpPrdActual[$i][$Level]),
				number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
			else
			printf('<td class=number><b>%s</b></td>
				<td></td>
				<td class=number>%s</td>'.$separador.'',
				number_format($GrpPrdActual[$i][$Level]),
				number_format(0,2)."%");
		}
		if($montoTotalIngresos[$i] != 0)
		printf('<td class=number><b>%s</b></td>
				<td></td>
				<td class=number>%s</td>',
				number_format($GrpPrdActualGral[$Level]),
				number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
		else
		printf('<td class=number><b>%s</b></td>
				<td></td>
				<td class=number>%s</td>',
				number_format($GrpPrdActualGral[$Level]),
				number_format(0,2)."%");
		echo '</tr>';
	}

	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {
		$GrpPrdActual[$i][$Level] = 0;
	}
	$GrpPrdActualGral[$Level] = 0;
	$ParentGroups[$Level] ='';

	if ($myrow['sectioninaccounts']!= $Section){

		if ($SectionType == 1) { /*Income*/

			/* Despliega una linea horizontan antes de sub totalizar */
			echo $TableFooter;

			printf('<tr style="background-color:yellow">
				<td colspan=3><font size=3><b>%s</b></font></td>',
				$Sections[$Section]);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {

				if ($montoTotalIngresos[$i] != 0)
				printf('<td></td>
					<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActual[$i][$Level]),
					number_format($SectionPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
				else
				printf('<td></td>
					<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActual[$i][$Level]),
					number_format(0,2)."%");

				/* Solo incrementa el Ingreso en esta seccion para tener totalizados los ingresos para margenes brutos */
				$TotalIncome[$i] = $TotalIncome[$i] - $SectionPrdActual[$i][$Level];
				$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
			}
			$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActualGral[$Level];

			printf('<td></td>
					<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>',
					number_format($SectionPrdActualGral[$Level]),
					number_format($SectionPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
			echo '</tr>';

			echo '<tr>
				<td colspan='.($count*4+3).'><br><br></td>
				</tr>';

		} else {

			/* Despliega una linea horizontan antes de sub totalizar */
			echo $TableFooter;

			printf('<tr style="background-color:yellow">
				<td colspan=3><font size=3><b>%s</b></font></td>',
				$Sections[$Section]);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {
				if ($montoTotalIngresos[$i] != 0)
				printf('<td class=number><font size=2><b>%s</b></font></td>
					<td></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActual[$i][$Level]),
					number_format($SectionPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,2)."%");
				else
				printf('<td class=number><font size=2><b>%s</b></font></td>
					<td></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActual[$i][$Level]),
					number_format(0,2)."%");

				$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
				$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActual[$i][$Level];
			}
			if ($montoTotalIngresosGral != 0)
			printf('<td class=number><font size=2><b>%s</b></font></td>
					<td></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActualGral[$Level]),
					number_format($SectionPrdActualGral[$Level]/$montoTotalIngresosGral*100,2)."%");
			else
			printf('<td class=number><font size=2><b>%s</b></font></td>
					<td></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActualGral[$Level]),
					number_format(0,2)."%");

			echo '</tr>';

			echo '<tr>
				<td colspan='.($count*4+3).'><br><br></td>
				</tr>';
		}
	}

	/* Despliega una linea horizontan antes de sub totalizar */
	echo $TableFooter;

	printf('<tr style="background-color:lightgreen">
		<td colspan=3><font size=3 color=BLUE><b>%s</b></font></td>',
		_('UTILIDAD DEL PERIODO'));

	/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {

		if ($TotalIncome[$i] !=0){
			$PrdGPPercent = 100*($UtilidadAcumulada[$i])/($TotalIncome[$i]);
		} else {
			$PrdGPPercent =0;
		}

		if ($UtilidadAcumulada[$i] > 0) {

			printf('<td class=number><font size=3 color=RED><b>%s</b></font></td>
				<td></td>
				<td class=number><font size=3 color=RED>%s</font></td>'.$separador.'',
				number_format($UtilidadAcumulada[$i]),
				number_format($PrdGPPercent,1).'%');
		} else {

			printf('<td></td>
				<td class=number><font size=3 color=BLUE><b>%s</b></font></td>
				<td class=number><font size=3 color=BLUE>%s</font></td>'.$separador.'',
				number_format($UtilidadAcumulada[$i]*-1),
				number_format($PrdGPPercent*-1,1).'%');
		}
	}

	if ($montoTotalIngresosGral !=0){
		$PrdGPPercent = 100*($PeriodProfitLossGral)/($montoTotalIngresosGral);
	} else {
		$PrdGPPercent =0;
	}

	if ($PeriodProfitLossGral > 0) {

		printf('<td class=number><font size=3 color=RED><b>%s</b></font></td>
			<td></td>
			<td class=number><font size=3 color=RED>%s</font></td>',
			number_format($PeriodProfitLossGral),
			number_format($PrdGPPercent,1).'%');
	} else {

		printf('<td></td>
			<td class=number><font size=3 color=BLUE><b>%s</b></font></td>
			<td class=number><font size=3 color=BLUE>%s</font></td>',
			number_format($PeriodProfitLossGral*-1),
			number_format($PrdGPPercent*-1,1).'%');
	}
	echo '</tr>';

	echo '<tr>
		<td colspan='.($count*4+3).'><br><br></td>
		</tr>';

	//echo $TableHeader;

	echo '</table>';

	if (isset($_POST['PrintEXCEL'])) {
		exit;
	}
	echo "<div class='centre'><input type=submit Name='SelectADifferentPeriod' Value='"._('Selecciona un periodo diferente')."'></div>";


}
echo '</form>';
include('includes/footer.inc');

?>
