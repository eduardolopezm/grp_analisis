<?php

$PageSecurity = 8;

$funcion=940;
include ('includes/session.inc');
$title = $funcion . " - " . _('Balance General En Columnas');


include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable


function nombremescorto($idmes){
	$nombremescorto = "";
	switch ($idmes) {
		case 1:
			$nombremescorto = "ENE";
			break;
		case 2:
			$nombremescorto = "FEB";
			break;
		case 3:
			$nombremescorto = "MAR";
			break;
		case 4:
			$nombremescorto = "ABR";
			break;
		case 5:
			$nombremescorto = "MAY";
			break;
		case 6:
			$nombremescorto = "JUN";
			break;
		case 7:
			$nombremescorto = "JUL";
			break;
		case 8:
			$nombremescorto = "AGO";
			break;
		case 9:
			$nombremescorto = "SEP";
			break;
		case 10:
			$nombremescorto = "OCT";
			break;
		case 11:
			$nombremescorto = "NOV";
			break;
		case 12:
			$nombremescorto = "DIC";
			break;

	}
	return $nombremescorto;
}

/*Convierte todas las variables GET a POST para poder pasar una configuracion de reporte desde una liga fija !*/
/**************************************************************************************************************/
if (isset($_GET['FromPeriod'])) {
	$_POST['FromPeriod'] = $_GET['FromPeriod'];
}
if (isset($_GET['ToPeriod'])) {
	$_POST['ToPeriod'] = $_GET['ToPeriod'];
}
if (isset($_GET['legalid'])) {
	$_POST['legalid'] = $_GET['legalid'];
}
if (isset($_GET['xRegion'])) {
	$_POST['xRegion'] = $_GET['xRegion'];
}
if (isset($_GET['xArea'])) {
	$_POST['xArea'] = $_GET['xArea'];
}
if (isset($_GET['xTag'])) {
	$_POST['xTag'] = $_GET['xTag'];
}
if (isset($_GET['xDepto'])) {
	$_POST['xDepto'] = $_GET['xDepto'];
}
if (isset($_GET['tag'])) {
	$_POST['tag'] = $_GET['tag'];
}
if (isset($_GET['Columnas'])) {
	$_POST['Columnas'] = $_GET['Columnas'];
}
if (isset($_GET['ShowPL'])) {
	$_POST['ShowPL'] = $_GET['ShowPL'];
}
if (isset($_GET['Detail'])) {
	$_POST['Detail'] = $_GET['Detail'];
}

/********************************************************************************************************************/
if($_SESSION['UserID'] == "admin"){
	echo '<br>ToPeriod '.$_POST['ToPeriod'];
	echo '<br>SelectADifferentPeriod '.$_POST['SelectADifferentPeriod'];
}
if (!isset($_POST['ToPeriod']) OR isset($_POST['SelectADifferentPeriod']) OR isset($_POST['btnfilrazon'])){

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

	$sql = 'SELECT periodno, month(lastdate_in_period) as mes, year(lastdate_in_period) as anio FROM periods ORDER BY periodno DESC';
	$Periods = DB_query($sql,$db);

	//$period=GetPeriod($FromDate, $db);

	/*Show a form to allow input of criteria for profit and loss to show */
	echo '<table>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$lastDate = date("Y-m-d",mktime(0,0,0,Date('m'),0,Date('Y')));
		$sql = "SELECT periodno,month(lastdate_in_period) as mes, year(lastdate_in_period) as anio FROM periods where lastdate_in_period = '$lastDate'";
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultToPeriod = (int) ($MaxPrdrow[0]);

	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr><td>' . _('Desde Periodo') . ":</td><td><select Name='FromPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<option selected VALUE=' . $myrow['periodno'] . '>' . nombremescorto($myrow['mes'])." ".$myrow['anio'];
		} else {
			echo '<option VALUE =' . $myrow['periodno'] . '>' . nombremescorto($myrow['mes'])." ".$myrow['anio'];
		}
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Hasta Periodo') . ":</td><td><select Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

			if($myrow['periodno']==$DefaultToPeriod){
				echo '<option selected VALUE=' . $myrow['periodno'] . '>' . nombremescorto($myrow['mes'])." ".$myrow['anio'];
			} else {
				echo '<option VALUE =' . $myrow['periodno'] . '>' . nombremescorto($myrow['mes'])." ".$myrow['anio'];
			}
		}//
	echo '</select></td></tr>';

	echo "<TR><TD align='right'></TD><TD align='left'><br>";
	echo '</TD></TR>';

	//Select the razon social
	echo '<tr><td>'._('Seleccione Una Razon Social:').'</td><td><select name="legalid">';

	echo '<option selected value=0>' . _('Todas a las que tengo acceso...');

	///Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalname";

	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>'  .$myrow['legalname'];
		}
	}
	echo '</select><input type=submit name=btnfilrazon value="->"></td>';
	// End select tag
	echo "</tr><tr>";
	echo "<td>"._('Seleccione Unidad de Negocio:')."</td>";
	$SQL = "SELECT tags.tagref,
					tags.tagdescription
			FROM tags
				INNER JOIN sec_unegsxuser as sec ON tags.tagref = sec.tagref
			WHERE sec.userid = '".$_SESSION['UserID']."'";

	if(isset($_POST['legalid']) and $_POST['legalid'] <> 0){

		$SQL = $SQL." AND tags.legalid = '".$_POST['legalid']."'";

	}

		$SQL = $SQL." ORDER BY tags.tagdescription";

	$result=DB_query($SQL,$db);
	echo "<td><select name=tag>";
	echo '<option selected value=0>' . _('Todas a las que tengo acceso...')."</option>";
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow["tagref"]){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo "</tr><tr>";

	echo "<TR><TD align='right'></TD><TD align='left'><br>";
	echo '</TD></TR>';

	/************************************/
	/* SELECCION DEL REGION */
	echo '<tr><td>' . _('X Obra') . ':' . "</td>
		<td><select tabindex='4' name='xRegion'>";

	$sql = "SELECT regions.regioncode, CONCAT(regions.regioncode,' - ',regions.name) as name FROM regions JOIN areas ON areas.regioncode = regions.regioncode
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY regions.regioncode, regions.name";

	$result=DB_query($sql,$db);

	echo "<option selected value='0'>Todas las obras...</option>";

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
	echo '<tr><td>' . _('X Etapa/Frente') . ':' . "</td>
		<td><select tabindex='4' name='xArea'>";

	$sql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
			FROM areas
			JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
		  WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		  GROUP BY areas.areacode, areas.areadescription";

	$result=DB_query($sql,$db);

	echo "<option selected value='0'>Todas las etapas...</option>";

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
	/*
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
	*/

	echo "<input type=hidden name='xDepto' value='0'>";
	echo "<input type=hidden name='tag' value='0'>";

	echo "<TR><TD align='right'></TD><TD align='left'><br>";
	echo '</TD></TR>';

	echo '<tr><td>'._('Columnas Por').":</td><td><select Name='Columnas'>";
		echo "<option selected VALUE='Periodos'>"._('X Periodos');
		echo "<option VALUE='Regiones'>"._('X Obras');
		echo "<option VALUE='Areas'>"._('X Etapas/Frentes');
		echo "<option VALUE='UnidadNegocios'>"._('X Unidad de Negocios');
	echo '</select></td></tr>';

	echo '<tr><td>'._('Detalle o Resumen').":</td><td><select Name='Detail'>";
		echo "<option VALUE='Summary'>"._('Resumen');
		echo "<option selected VALUE='Detailed'>"._('Todas las Cuentas');
	echo '</select></td></tr>';

	/*
	echo "<TR><TD align='right'>"._('Solo cuentas con movimientos').":</TD><TD align='left'>";
	echo "<INPUT type='checkbox' NAME='noZeroValues' VALUE='noZeroes'>";
	echo '</TD></TR>';


	echo "<TR><TD align='right'>"._('Solo cuentas diferentes cero').":</TD><TD align='left'>";
	echo "<INPUT type='checkbox' NAME='noZero' VALUE='1'>";
	echo '</TD></TR>';
	*/

	echo "<tr>";
		echo "<td colspan='2'>";
			echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
				echo "<tr>";
					echo "<td style='text-align:center;'><br><input type='submit' Name='ShowPL' Value='" ._('Mostrar Reporte En Pantalla') . "'></td>";
					/*echo "<td style='text-align:center;'><input type='submit' Name='PrintPDF' Value='" . _('Exportar a PDF') . "'></td>";
					echo "<td style='text-align:center;'><input type='submit' Name='PrintEXCEL' Value='" . _('Exporta a EXCEL') . "'></td>";*/
				echo "<tr>";
			echo "</table>";
		echo "</td>";
	echo "</tr>";

	echo "<TR><TD align='right'></TD><TD align='left'><br>";
	echo '</TD></TR>';

	$FromPeriod=$period;
	$ToPeriod=$DefaultToPeriod;

	echo "<TR><TD align='right'></TD><TD align='left'><br>";
	/*RESUMEN X PERIODO*/
	echo "<a href='" . $rootpath ."/GLTagBalanceMultiColumns_Executive.php?&FromPeriod=".$FromPeriod."
	&ToPeriod=".$ToPeriod."&legalid=0&tag=0&xArea=0&xDepto=0
	&xRegion=0&Columnas=Periodos&Detail=Summary'>1.- Balance Sheet By Period (click here)</a><br><br>";

	/*DETALLE X PERIODO*/
	echo "<a href='" . $rootpath ."/GLTagBalanceMultiColumns_Executive.php?&FromPeriod=".$FromPeriod."
	&ToPeriod=".$ToPeriod."&legalid=0&tag=0&xArea=0&xDepto=0
	&xRegion=0&Columnas=Periodos&Detail=Detailed'>2.- Balance Sheet Detail By Period (click here)</a><br><br>";

	/*DETALLE X OBRA*/
	echo "<a href='" . $rootpath ."/GLTagBalanceMultiColumns_Executive.php?&FromPeriod=".$FromPeriod."
	&ToPeriod=".$ToPeriod."&legalid=0&tag=0&xArea=0&xDepto=0
	&xRegion=0&Columnas=Regiones&Detail=Detailed'>3.- Balance Sheet Detail By Project (click here)</a><br><br>";

	/*DETALLE X OTOMI*/

	echo "<a href='" . $rootpath ."/GLTagBalanceMultiColumns_Executive.php?&FromPeriod=".$FromPeriod."
	&ToPeriod=".$ToPeriod."&legalid=0&tag=0&xArea=0&xDepto=0
	&xRegion=109&Columnas=Areas&Detail=Detailed'>5.- Balance Sheet Detail - <b>OTOMI</b> (click here)</a><br><br>";

	echo '</TD></TR>';

	echo "<TR><TD align='right'></TD><TD align='left'><br>";
	echo '</TD></TR>';

	echo '</table>';

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





} elseif (isset($_POST['ShowPL'])) {

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


	$sql = 'SELECT lastdate_in_period, month(lastdate_in_period) as mes, year(lastdate_in_period) as anio FROM periods WHERE periodno=' . $_POST['ToPeriod'];

	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_array($PrdResult);
	$PeriodToDate = nombremescorto($myrow['mes'])." ".$myrow['anio'];


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
					periodno <= ' . $_POST['ToPeriod'] . '
				ORDER BY periodno';
	} else {
		if ($_POST['Columnas'] == 'UnidadNegocios')
			$SQL = 'SELECT t.tagref, t.tagname';
		elseif ($_POST['Columnas'] == 'Razones')
			$SQL = 'SELECT t.legalid, legalbusinessunit.legalname';
		elseif ($_POST['Columnas'] == 'Regiones')
			$SQL = 'SELECT regions.regioncode, regions.name';
		elseif ($_POST['Columnas'] == 'Areas')
			$SQL = 'SELECT areas.areacode, areas.areadescription';
		elseif ($_POST['Columnas'] == 'Tags')
			$SQL = 'SELECT t.tagref, t.tagdescription';
		elseif ($_POST['Columnas'] == 'Departamentos')
			$SQL = 'SELECT departments.u_department, departments.department';



		$SQL = $SQL . '	FROM tags t JOIN sec_unegsxuser ON sec_unegsxuser.tagref = t.tagref
			LEFT JOIN areas ON t.areacode = areas.areacode
			LEFT JOIN regions ON areas.regioncode = regions.regioncode
			LEFT JOIN departments ON t.u_department=departments.u_department
				';

		$SQL = $SQL . '
			LEFT JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid

			WHERE (t.tagref='.$_POST['tag'].' OR "0"="'.$_POST['tag'].'")';

		$SQL = $SQL . '
			and (areas.regioncode = '.$_POST['xRegion'].' or '.$_POST['xRegion'].'=0)
			and (areas.areacode = '.$_POST['xArea'].' or '.$_POST['xArea'].'=0)
			and (departments.u_department = '.$_POST['xDepto'].' or '.$_POST['xDepto'].'=0)';

		$SQL = $SQL . '
			AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			AND (t.legalid = "'.$_POST['legalid'].'" OR "'.$_POST['legalid'].'" = "0")';

		if ($_POST['Columnas'] == 'UnidadNegocios')
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
		elseif ($_POST['Columnas'] == 'tags')
			$SQL = $SQL . ' GROUP BY t.tagref, t.tagdescription
				 ORDER BY areas.areacode';
		elseif ($_POST['Columnas'] == 'Departamentos')
			$SQL = $SQL . ' GROUP BY departments.u_department, departments.department
				 ORDER BY departments.u_department';
	}

	//echo "<pre>".$SQL.'<br>';



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


	// PRIMERO CALCULA EL TOTAL DE RESULTADOS DEL EJERCICIO
	if ($_POST['Columnas'] == 'Periodos')
		$SQL = 'SELECT "RESULTADO DEL EJERCICIO" as sectiontype, gltrans.periodno as id, periods.lastdate_in_period as nombre,';
	elseif ($_POST['Columnas'] == 'Razones')
	$SQL = 'SELECT "RESULTADO DEL EJERCICIO" as sectiontype, t.legalid as id, legalbusinessunit.legalname as nombre,';
	elseif ($_POST['Columnas'] == 'UnidadNegocios')
	$SQL = 'SELECT "RESULTADO DEL EJERCICIO" as sectiontype, t.tagref as id, t.tagname as nombre,';
	elseif ($_POST['Columnas'] == 'Regiones')
	$SQL = 'SELECT "RESULTADO DEL EJERCICIO" as sectiontype, regions.regioncode as id, regions.name as nombre,';
	elseif ($_POST['Columnas'] == 'Areas')
	$SQL = 'SELECT "RESULTADO DEL EJERCICIO" as sectiontype, areas.areacode as id, areas.areadescription as nombre,';
	elseif ($_POST['Columnas'] == 'Departamentos')
	$SQL = 'SELECT "RESULTADO DEL EJERCICIO" as sectiontype, departments.u_department as id, departments.department as nombre,';

	$SQL = $SQL . 'Sum(CASE WHEN (gltrans.periodno<=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
		Sum(CASE WHEN (gltrans.periodno=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN gltrans
		ON chartmaster.accountcode= gltrans.account INNER JOIN accountsection
		ON accountgroups.sectioninaccounts = accountsection.sectionid
		JOIN sec_unegsxuser ON  gltrans.tag = sec_unegsxuser.tagref
		JOIN tags t ON sec_unegsxuser.tagref = t.tagref
		LEFT JOIN areas ON t.areacode = areas.areacode
		LEFT JOIN regions ON areas.regioncode = regions.regioncode
		LEFT JOIN departments ON t.u_department=departments.u_department
		JOIN periods ON gltrans.periodno = periods.periodno ';

	$SQL = $SQL . '
		JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid


		WHERE accountgroups.pandl=1
			AND gltrans.periodno <= ' . $_POST['ToPeriod'] . '
			AND (gltrans.tag='.$_POST['tag'].' OR "0"="'.$_POST['tag'].'")
			and (areas.regioncode = '.$_POST['xRegion'].' or '.$_POST['xRegion'].'=0)
			and (areas.areacode = '.$_POST['xArea'].' or '.$_POST['xArea'].'=0)
			and (departments.u_department = '.$_POST['xDepto'].' or '.$_POST['xDepto'].'=0)';

	$SQL = $SQL . '
			AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			AND gltrans.type <> 999
			AND (t.legalid = "'.$_POST['legalid'].'" OR "'.$_POST['legalid'].'" = "0")';


	if ($_POST['Columnas'] == 'Periodos')
		$SQL = $SQL .' GROUP BY "RESULTADO DEL EJERCICIO", gltrans.periodno, periods.lastdate_in_period
				ORDER BY gltrans.periodno';
	elseif ($_POST['Columnas'] == 'Razones')
	$SQL = $SQL . ' GROUP BY "RESULTADO DEL EJERCICIO", t.legalid, legalbusinessunit.legalname
				ORDER BY t.legalid';
	elseif ($_POST['Columnas'] == 'UnidadNegocios')
	$SQL = $SQL .'  GROUP BY "RESULTADO DEL EJERCICIO", t.tagref, t.tagname
				ORDER BY t.tagref';
	elseif ($_POST['Columnas'] == 'Regiones')
	$SQL = $SQL .' GROUP BY "RESULTADO DEL EJERCICIO", regions.regioncode, regions.name
			 ORDER BY regions.regioncode';
	elseif ($_POST['Columnas'] == 'Areas')
	$SQL = $SQL .' GROUP BY "RESULTADO DEL EJERCICIO", areas.areacode, areas.areadescription
			 ORDER BY areas.areacode';
	elseif ($_POST['Columnas'] == 'Departamentos')
	$SQL = $SQL .' GROUP BY "RESULTADO DEL EJERCICIO", departments.u_department, departments.department
			 ORDER BY departments.u_department';

	$count = count($_SESSION['IdColumnArray']);
	$result = DB_query($SQL,$db);
	$jr=0;

	//echo $SQL.'<br>';
	$montoTotalIngresosGral = 0;
	while ($myrow = DB_fetch_row($result)){

		for ($i=$jr; $i < $count; $i++) {
			if ($myrow[1] == $_SESSION['IdColumnArray'][$i]) {
				$montoTotalIngresos[$i] = $myrow[3];
				$montoTotalIngresosGral = $montoTotalIngresosGral + ($myrow[3]);

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
	elseif ($_POST['Columnas'] == 'UnidadNegocios')
		$SQL = $SQL .' accountsection.sectiontype, t.tagref as columna,';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .' accountsection.sectiontype, regions.regioncode as columna,';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .' accountsection.sectiontype, areas.areacode as columna,';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .' accountsection.sectiontype, departments.u_department as columna,';

	$SQL = $SQL . ' Sum(CASE WHEN (gltrans.periodno<=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
					Sum(CASE WHEN (gltrans.periodno=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN gltrans
		ON chartmaster.accountcode= gltrans.account INNER JOIN accountsection
		ON accountgroups.sectioninaccounts = accountsection.sectionid
		JOIN sec_unegsxuser ON  gltrans.tag = sec_unegsxuser.tagref
		JOIN tags t ON sec_unegsxuser.tagref = t.tagref
		LEFT JOIN areas ON t.areacode = areas.areacode
		LEFT JOIN regions ON areas.regioncode = regions.regioncode
		LEFT JOIN departments ON t.u_department=departments.u_department';

	$SQL = $SQL . '
		LEFT JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid

		WHERE accountgroups.pandl=0
			AND gltrans.periodno <= ' . $_POST['ToPeriod'] . '
			AND (gltrans.tag='.$_POST['tag'].' OR "0"="'.$_POST['tag'].'")
			and (areas.regioncode = '.$_POST['xRegion'].' or '.$_POST['xRegion'].'=0)
			and (areas.areacode = '.$_POST['xArea'].' or '.$_POST['xArea'].'=0)
			and (departments.u_department = '.$_POST['xDepto'].' or '.$_POST['xDepto'].'=0)';

	$SQL = $SQL . '
			AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			AND (t.legalid = "'.$_POST['legalid'].'" OR "'.$_POST['legalid'].'" = "0")
			AND gltrans.type <> 999';

	if (isset($_POST['noZeroValues'])) {
		$SQL = $SQL . " and gltrans.periodno<=" . $_POST['ToPeriod'];
	}

	$SQL = $SQL . '	GROUP BY /*legalbusinessunit.legalname,*/
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
	elseif ($_POST['Columnas'] == 'UnidadNegocios')
		$SQL = $SQL .'  t.tagref';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .'  regions.regioncode';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .'  areas.areacode';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .'  departments.u_department';

	if (isset($_POST['noZero']) and $_POST['noZero']==1){
		$SQL = $SQL . " HAVING (ABS(TotalAllPeriods) > 0.1)";
	}

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
	elseif ($_POST['Columnas'] == 'UnidadNegocios')
		$SQL = $SQL .'  t.tagref';
	elseif ($_POST['Columnas'] == 'Regiones')
		$SQL = $SQL .'  regions.regioncode';
	elseif ($_POST['Columnas'] == 'Areas')
		$SQL = $SQL .'  areas.areacode';
	elseif ($_POST['Columnas'] == 'Departamentos')
		$SQL = $SQL .'  departments.u_department';

	//echo "<pre>".$SQL.'<br>';

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

	echo '<div class="centre"><font size=4 color=BLUE><b>' . _('Balance General') . '<br>'.$n_razonsocial;

	if ($_POST['xRegion'] != 0) {
		echo '<br>OBRA:'.$n_region;
	}

	if ($_POST['xArea'] != 0) {
		echo '<br>FRENTE/ETAPA:'.$n_area;
	}

	if ($_POST['xDepto'] != 0) {
		echo '<br>DEPARTAMENTO:'.$n_departamento;
	}

	if ($_POST['tag'] != 0) {
		echo '<br>UNIDAD NEGOCIOS:' . $n_unidaddenegocio;
	}
	//setlocale(LC_TIME,'spanish');
	//strftime("%B");
	echo '<br>'. _('A:'). ' ' . $PeriodToDate . '</b></font></div><br>';

	/* show a table of the accounts info returned by the SQL
	Account Code , Account Name, Month Actual, Month Budget, Period Actual, Period Budget */

	echo '<table cellpadding=2 border=0>';

	$separador = "<td style='width=5px;background-color:green'></td>";
	$separador2 = "<td></td>";


	$TableHeader = "<tr>
			<th>&nbsp;</th>
			<th>"._('Nombre')."</th>";

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno <= " . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);

	$i = 0;
	while ($myrowp=DB_fetch_array($PrdResult)){
		$i = $i + 1;
		if ($i == 1){
			$colspan = 1;
		}else{
			$colspan = 1;
		}
		$fechaperiodo = MonthAndYearFromSQLDate($myrowp[0]);
		$TableHeader = $TableHeader . "<th colspan='" . $colspan . "'>" . substr($fechaperiodo,0,3) . "</th>
			<th>"._('%')."</th>".$separador."</th>";
	}



	$TableHeader = $TableHeader ."<th colspan=1><font size=3><b>"._('TOTALES')."</b></font></th>
			<th>"._('%')."</th>
			</tr>";

	/////////////////////////////////////////////////////////////////////////////////////////////////
	$TableHeader2 = "<tr>
			<th>&nbsp</th>
			<th>"._('Nombre')."</th>
			";

	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {
		$TableHeader2 = $TableHeader2 ."<th ><b>".$_SESSION['ColumnNameArray'][$i]."</b></th>
						<th>"._('%')."</th>".$separador."";
	}

	$TableHeader2 = $TableHeader2 ."<th colspan=1><font size=3><b>"._('TOTALES')."</b></font></th>
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
			<td></td>";



	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {
		$TableFooter = $TableFooter ."<td></td>
						<td></td>".$separador."";
	}

	$TableFooter = $TableFooter ."<td></td>
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

	//echo "<pre>Columnas: ";
	//print_r($_SESSION['IdColumnArray']);

	while ($myrow=DB_fetch_array($AccountsResult)) {


		if ($myrow['groupname']!= $ActGrp){
			if ($myrow['parentgroupname']!=$ActGrp AND $ActGrp!=''){


				/* SI ES LA PRIMERA LINEA DE LA CONSULTA NO COMPLETES COLUMNAS */
				if ($FirstTimeIn == 0 AND $_POST['Detail']=='Detailed') {
					$count = count($_SESSION['IdColumnArray']);
					if ($LastColumnDisplayed != ($count-1)/*$_SESSION['IdColumnArray'][$count-1]*/) {
						for ($i = 0; $i < $count; $i++) {
							if ($LastColumnDisplayed < $i/*$_SESSION['IdColumnArray'][$i]*/) {
								echo '<td></td><td></td>'.$separador.'';
							}
						}

						if ($LastSectionType == 1)
							echo '<td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
						else
							echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td>';

						echo '<td class=number>'.number_format(0*100,0).'</td>';

						$AccountPeriodActualGral = 0;
						echo '</tr>';
					}
				}
				$FirstTimeIn = 0;

				while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
					if ($_POST['Detail']=='Detailed'){

						/* Despliega una linea horizontan antes de sub totalizar */
						echo $TableFooter;

						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ';
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
							printf('<td class=number><b>%s</b></td>
								<td class=number>%s</td>'.$separador.'',
								number_format($GrpPrdActual[$i][$Level]),
								"-");
						}

						printf('
							<td class=number><b>%s</b></td>
							<td class=number>%s</td>',
							number_format($GrpPrdActualGral[$Level]),
							"-");
						echo '</tr>';

					} else { /*Costs */
						printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
							$ActGrpLabel);

						/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
						$count = count($_SESSION['IdColumnArray']);
						for ($i = 0; $i < $count; $i++) {
							$montoti = 0;

							printf('<td class=number><b>%s</b></td>
								<td class=number>%s</td>'.$separador.'',
								number_format($GrpPrdActual[$i][$Level]),
								number_format($montoti,0));
						}
						printf('<td class=number><b>%s</b></td>
							<td class=number>%s</td>',
							number_format($GrpPrdActualGral[$Level]),
							"-");
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

					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ';
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}
				/*IMPRIME VALORES POR GRUPO DE CUENTA*/
				if ($SectionType ==1){ /*Income */
					printf('<tr><td></td>
						<td colspan=1 nowrap><font size=2><I>%s </I></font></td>',
						substr($ActGrpLabel,strpos($ActGrpLabel," ")+1));

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {

						printf('<td class=number><b>%s</b></td>
							<td class=number>%s</td>'.$separador.'',
							number_format(-$GrpPrdActual[$i][$Level]),
							number_format(0,2));
					}

					printf('<td class=number><b>%s</b></td>
							<td class=number>%s</td>',
							number_format(-$GrpPrdActualGral[$Level]),
							number_format(0,0));
						echo '</tr>';

				} else { /*Costs */
					printf('<tr><td></td>
						<td colspan=1 nowrap><font size=2><I>%s </I></font></td>',
						substr($ActGrpLabel,strpos($ActGrpLabel," ")+1));

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {

						printf('<td class=number><b>%s</b></td>
							<td class=number>%s</td>'.$separador.'',
							number_format($GrpPrdActual[$i][$Level]),
							number_format(0,0));
					}

					printf('<td class=number><b>%s</b></td>
						<td class=number>%s</td>',
						number_format($GrpPrdActualGral[$Level]),
						number_format(0,0));
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
						<td colspan=2><font size=3><b>4:%s</b></font></td>',
						$Sections[$Section]);

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {

						printf('
								<td class=number><font size=2><b>%s</b></font></td>
								<td class=number><font size=2>%s</font></td>'.$separador.'',
								number_format(-$SectionPrdActual[$i][$Level]),
								number_format(0,0));
						/* Solo incrementa el Ingreso en esta seccion para tener totalizados los ingresos para margenes brutos */
						$TotalIncome[$i] = $TotalIncome[$i] - $SectionPrdActual[$i][$Level];

						$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
						$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActual[$i][$Level];
					}

					printf('<td class=number><font size=2><b>%s</b></font></td>
						<td class=number><font size=2>%s</font></td>',
						number_format(-$SectionPrdActualGral[$Level]),
						number_format(0,0));
					echo '</tr>';

					echo '<tr>
						<td colspan='.($count*4+3).'><br><br></td>
						</tr>';

				} else {

					/* Despliega una linea horizontan antes de sub totalizar */
					echo $TableFooter;

					printf('<tr style="background-color:yellow">
						<td colspan=2><font size=3><b>1:%s</b></font></td>',
						$Sections[$Section]);

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {


						printf('<td class=number><font size=2><b>%s</b></font></td>
							<td class=number><font size=2>%s</font></td>'.$separador.'',
							number_format($SectionPrdActual[$i][$Level]),
							number_format(0,0));

						$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
						$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActual[$i][$Level];
					}

					printf('<td class=number><font size=2><b>%s</b></font></td>
						<td class=number><font size=2>%s</font></td>',
						number_format($SectionPrdActualGral[$Level]),
						number_format(0,0));
					echo '</tr>';

					echo '<tr>
						<td colspan='.($count*4+3).'><br><br></td>
						</tr>';
				}

				if ($SectionType == 2){ /*Cost of Sales - need sub total for Gross Profit*/

					/* Despliega una linea horizontan antes de sub totalizar */
					echo $TableFooter;



					printf('<tr style="background-color:orange">
						<td colspan=2><font size=3><b>%s</b></font></td>',
						_('Utilidad Bruta'));

					/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
					$count = count($_SESSION['IdColumnArray']);
					for ($i = 0; $i < $count; $i++) {


						$PrdGPPercent =0;


						if ($UtilidadAcumulada[$i] > 0) {
							printf('<td class=number><font size=2><b>%s</b></font></td>
								<td class=number><font size=2>%s</font></td>'.$separador.'',
								number_format($UtilidadAcumulada[$i]),
								number_format($PrdGPPercent,0));
						} else {
							printf('
								<td class=number><font size=2><b>%s</b></font></td>
								<td class=number><font size=2>%s</font></td>'.$separador.'',
								number_format($UtilidadAcumulada[$i]*-1),
								number_format($PrdGPPercent*-1,0));
						}
					}

					$PrdGPPercent =0;

					if ($UtilidadAcumuladaGral > 0) {
						printf('<td class=number><font size=2><b>%s</b></font></td>
							<td class=number><font size=2>%s</font></td>',
							number_format($UtilidadAcumuladaGral),
							number_format($PrdGPPercent,0));
					} else {
						printf('<td class=number><font size=2><b>%s</b></font></td>
							<td class=number><font size=2>%s</font></td>',
							number_format($UtilidadAcumuladaGral*-1),
							number_format($PrdGPPercent*-1,0));
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
					if ($LastColumnDisplayed != ($count-1)/*$_SESSION['IdColumnArray'][$count-1]*/) {
						for ($i = 0; $i < $count; $i++) {
							if ($LastColumnDisplayed < $i /*$_SESSION['IdColumnArray'][$i]*/) {
								echo '<td></td><td></td>'.$separador.'';
							}
						}

						if ($LastSectionType == 1)
							echo '<td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
						else
							echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td>';


						echo '<td class=number>'.number_format(0*100,0).'</td>';

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
					<td nowrap>%s</td>',
					"&nbsp;",
					$myrow['accountname']
					);

				$LastColumnDisplayed = -1;
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

		if ($_POST['Detail']==_('Detailed')){

			if ($SectionType == 1){

				$count = count($_SESSION['IdColumnArray']);

				for ($i = 0; $i < $count; $i++) {
					if ($myrow['columna'] == $_SESSION['IdColumnArray'][$i]) {

						echo '<td class=number>'.number_format(-$AccountPeriodActual).'</td>';
						echo '<td class=number>'.number_format(0*100,0).'</td>';

						echo $separador;
						break;

					} elseif ($i > $LastColumnDisplayed /*$myrow['columna'] > $_SESSION['IdColumnArray'][$i] AND $LastColumnDisplayed < $_SESSION['IdColumnArray'][$i]*/) {
						echo '<td></td><td></td>'.$separador.'';
					} else {
						echo '';
					}
				}

				if ($i == $count-1) {
					echo '<td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
					echo '<td class=number>'.number_format(0*100,0).'</td>';

					$AccountPeriodActualGral = 0;
					echo '</tr>';
				}

			} else {

				$count = count($_SESSION['IdColumnArray']);

				for ($i = 0; $i < $count; $i++) {
					if ($myrow['columna'] == $_SESSION['IdColumnArray'][$i]) {

						echo '<td class=number>'.number_format($AccountPeriodActual).'</td>';
						echo '<td class=number>'.number_format(0*100,0).'</td>';

						echo $separador;
						break;

					} elseif ( $i > $LastColumnDisplayed /*abs($myrow['columna']) > abs($_SESSION['IdColumnArray'][$i]) AND abs($LastColumnDisplayed) < abs($_SESSION['IdColumnArray'][$i])*/) {
						echo '<td></td><td></td>'.$separador.'';
					} else {
						echo '';
					}
				}

				if ($i == $count-1) {
					echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td>';
						echo '<td class=number>'.number_format(0*100,0).'</td>';
					echo '</tr>';
					$AccountPeriodActualGral = 0;
				}
			}

			$lastAccountDisplayed = $myrow['accountname'];
			$LastColumnDisplayed = $i;//$myrow['columna'];
			$LastSectionType = $SectionType;

			$j++;
		}
	}
	//end of loop



	/* SI ES LA PRIMERA LINEA DE LA CONSULTA NO COMPLETES COLUMNAS */

	if ($_POST['Detail']=='Detailed') {
		$count = count($_SESSION['IdColumnArray']);
		if ($LastColumnDisplayed != ($count-1) /*$_SESSION['IdColumnArray'][$count-1]*/) {
			for ($i = 0; $i < $count; $i++) {
				if ($LastColumnDisplayed < $i/*$_SESSION['IdColumnArray'][$i]*/) {
					echo '<td></td><td></td>'.$separador.'';
				}
			}

			if ($LastSectionType == 1)
				echo '<td class=number>'.number_format(-$AccountPeriodActualGral).'</td>';
			else
				echo '<td class=number>'.number_format($AccountPeriodActualGral).'</td>';

			echo '<td class=number>'.number_format(0*100,0).'</td>';

			$AccountPeriodActualGral = 0;
			echo '</tr>';
		}
	}

	while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
		if ($_POST['Detail']=='Detailed'){

			/* Despliega una linea horizontan antes de sub totalizar */
			echo $TableFooter;

			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ';
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
				printf('
					<td class=number><b>%s</b></td>
					<td class=number>%s</td>'.$separador.'',
					number_format($GrpPrdActual[$i][$Level]),
					number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,0));
			}

			printf('<td></td>
					<td class=number><b>%s</b></td>
					<td class=number>%s</td>',
					number_format($GrpPrdActualGral[$Level]),
					number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,0));
			echo '</tr>';

		} else { /*Costs */
			printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
				$ActGrpLabel);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {
				printf('<td class=number><b>%s</b></td>
					<td class=number>%s</td>'.$separador.'',
					number_format($GrpPrdActual[$i][$Level]),
					number_format($GrpPrdActual[$i][$Level]/$montoTotalIngresos[$i]*100,0));
			}
			printf('<td class=number><b>%s</b></td>
					<td class=number>%s</td>',
					number_format($GrpPrdActualGral[$Level]),
					number_format($GrpPrdActualGral[$Level]/$montoTotalIngresosGral*100,0));
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

			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ';
		} else {
			$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
		}

	if ($SectionType == 1){ /*Income */
		printf('<tr><td></td>
						<td colspan=2 nowrap><font size=2><I>%s </I></font></td>',
			substr($ActGrpLabel,strpos($ActGrpLabel," ")+1));

		/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
		$count = count($_SESSION['IdColumnArray']);
		for ($i = 0; $i < $count; $i++) {

			printf('
				<td class=number><b>%s</b></td>
				<td class=number>%s</td>'.$separador.'',
				number_format($GrpPrdActual[$i][$Level]),
				number_format(0,0));
		}
		printf('
				<td class=number><b>%s</b></td>
				<td class=number>%s</td>',
				number_format($GrpPrdActualGral[$Level]),
				"-");
		echo '</tr>';

	} else { /*Costs */
		printf('<tr><td></td>
						<td colspan=1 nowrap><font size=2><I>%s </I></font></td>',
			substr($ActGrpLabel,strpos($ActGrpLabel," ")+1));

		/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
		$count = count($_SESSION['IdColumnArray']);
		for ($i = 0; $i < $count; $i++) {

			printf('<td class=number><b>%s</b></td>
				<td class=number>%s</td>'.$separador.'',
				number_format($GrpPrdActual[$i][$Level]),
				number_format(0,0));
		}

		printf('<td class=number><b>%s</b></td>
				<td class=number>%s</td>',
				number_format($GrpPrdActualGral[$Level]),
				number_format(0,0));
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
				<td colspan=2><font size=3><b>2:%s</b></font></td>',
				$Sections[$Section]);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {


				printf('<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>'.$separador.'',
					number_format($SectionPrdActual[$i][$Level]),
					number_format(0,0));

				/* Solo incrementa el Ingreso en esta seccion para tener totalizados los ingresos para margenes brutos */
				$TotalIncome[$i] = $TotalIncome[$i] - $SectionPrdActual[$i][$Level];
				$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
			}
			$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActualGral[$Level];

			printf('<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>',
					number_format($SectionPrdActualGral[$Level]),
					"-");
			echo '</tr>';

			echo '<tr>
				<td colspan='.($count*4+3).'><br><br></td>
				</tr>';

		} else {

			/* Despliega una linea horizontan antes de sub totalizar */
			echo $TableFooter;

			/************** DESPLIEGA AQUI TOTAL DE RESULTADOS DEL EJERCICIO **********************/
			echo '<tr style="background-color:cyan">
				<td colspan=2><font size=3><b>RESULTADOS DEL EJERCICIO</b></font></td>';

			$sumaResultados = 0;
			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {

				echo '<td class=number><font size=2><b>'.
						number_format($montoTotalIngresos[$i]).'</b></font></td>
					<td class=number><font size=2>-</font></td>'.$separador.'';

				$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $montoTotalIngresos[$i];
				$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $montoTotalIngresos[$i];
				$sumaResultados = $sumaResultados + $montoTotalIngresos[$i];
			}
			/**************************************************************************************/

			printf('<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>',
								number_format($sumaResultados),
								number_format(0,0));

			echo '</tr>';

			echo $TableFooter;

			printf('<tr style="background-color:yellow">
				<td colspan=2><font size=3><b>3:%s</b></font></td>',
				$Sections[$Section]);

			/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */

			$count = count($_SESSION['IdColumnArray']);
			for ($i = 0; $i < $count; $i++) {

					printf('<td class=number><font size=2><b>%s</b></font></td>
						<td class=number><font size=2>%s</font></td>'.$separador.'',
						number_format($SectionPrdActual[$i][$Level]+$montoTotalIngresos[$i]),
						number_format(0,0));

				$UtilidadAcumulada[$i] = $UtilidadAcumulada[$i] + $SectionPrdActual[$i][$Level];
				$UtilidadAcumuladaGral = $UtilidadAcumuladaGral + $SectionPrdActual[$i][$Level];
			}

			printf('<td class=number><font size=2><b>%s</b></font></td>
					<td class=number><font size=2>%s</font></td>',
					number_format($SectionPrdActualGral[$Level]+$sumaResultados),
					number_format(0,0));

			echo '</tr>';

			echo '<tr>
				<td colspan='.($count*4+3).'><br><br></td>
				</tr>';
		}
	}


	/* Despliega una linea horizontan antes de sub totalizar */
	echo $TableFooter;

	printf('<tr style="background-color:lightgreen">
		<td colspan=2><font size=3 color=BLUE><b>%s</b></font></td>',
		_('CUADRES'));

	/* Despliega Sub Totales de Esta Sub Cuenta por cada TAG */
	$count = count($_SESSION['IdColumnArray']);
	for ($i = 0; $i < $count; $i++) {


		$PrdGPPercent =0;

		printf('<td class=number><font size=3 color=BLUE><b>%s</b></font></td>
				<td class=number><font size=3 color=BLUE>%s</font></td>'.$separador.'',
				number_format($UtilidadAcumulada[$i]),
				"-");

	}


	$PrdGPPercent =0;



	printf('<td class=number><font size=3 color=BLUE><b>%s</b></font></td>
			<td class=number><font size=3 color=BLUE>%s</font></td>',
			number_format($PeriodProfitLossGral+$sumaResultados),
			number_format($PrdGPPercent*-1,0));

	echo '</tr>';

	echo '<tr>
		<td colspan='.($count*4+3).'><br><br></td>
		</tr>';

	//echo $TableHeader;

	echo '</table>';

	if (isset($_POST['PrintEXCEL'])) {
		exit;
	}
	echo "<div class='centre'><input type=submit Name='SelectADifferentPeriod' Value='"._('Selecciona un periodo diferente')."'><br><br></div>";


}
echo '</form>';
include('includes/footer.inc');

?>
