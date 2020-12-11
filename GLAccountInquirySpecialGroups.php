<?php

/* $Revision: 1.00 $ */

/* CREADO POR Desarrollador
   FECHA: 14/FEBRERO/2011
	1.- Este es el reporte similar al auxiliar de cuentas pero seleccionando los
	grupos especiales de cuentas contables que previamente se hayan creado en la
	pagina de GLAccountSpecialGroups.php
	
	FIN DE CAMBIOS
*/

$PageSecurity = 8;
include ('includes/session.inc');
$title = _('Consulta Transacciones Por Agrupaciones Especiales');
if (isset($_POST['PrintEXCEL'])) {
	
}else{
	include('includes/header.inc');
}

/* ES IMPORTANTE QUE EL NUMERO DE FUNCION EXISTA ANTES DE LA VERIFICACION DE
	LAS BASES DE DATOS POR SI NO EXISTE QUE SE INSERTE EN SEGURIDAD */
$funcion=1105;

/* ***********************************************************************************************
EN ESTA SECCION SE REVISAN LOS CAMBIOS NECESARIOS PARA ACTUALIZAR LA VERSION DE LA BASE DE DATOS */

	$sql="SELECT COUNT(*) as existe
		FROM sec_functions
		WHERE functionid=".$funcion;

	$result=DB_query($sql, $db);
	if ($myrow=DB_fetch_array($result)){
		if ($myrow['existe'] == 0) {
			
			/* INSERTA NUEVA FUNCION DE SEGURIDAD SI NO EXISTE... */
			DB_query("INSERT INTO `sec_functions`
				 (`functionid`, `submoduleid`, `title`, `active`, `url`,
				 `categoryid`, `shortdescription`, `orderno`, `comments`, `type`) 
				VALUES ('".$funcion."', '7', 'Consultas X Agrupaciones', '1', 'GLAccountInquirySpecialGroups.php',
				'2', 'Consultas X Agrupaciones', '5', 'Consultas X Agrupaciones', 'Consulta de Auxiliar por Agrupaciones especiales de
				cuentas contables para solicitar auxiliares de varias cuentas en un solo reporte')", $db);
			
			prnMsg(_('La funcion de seguridad No.').$funcion._(' fue insertada con exito,
				solo necesita asignarla a perfiles de seguridad...'),'success');
		}
	}
	
/*************************************************************************************************/
	
/* VERIFICA QUE EL USUARIO CUENTA CON LOS PERMISOS DE ACCESO PARA ENTRAR A ESTA PAGINA */
include('includes/SecurityFunctions.inc');


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

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
} elseif (isset($_GET['Period'])) {
	$SelectedPeriod = $_GET['Period'];
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
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Auxiliar X Agrupaciones Especiales') . '" alt="">' . ' ' . _('Auxiliar X Agrupaciones Especiales') . '</p>';
	
	echo '<div class="page_help_text">' . _('Utiliza la tecla shift presionada para seleccionar varios periodos...') . '</div><br>';
	
	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	
	
	/*Dates in SQL format for the last day of last month*/
	$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));
	
	/*Show a form to allow input of criteria for TB to show */
	echo '<table>';
	
	$sql='SELECT *
		FROM chartspecialgroups
		ORDER BY groupname';

	$result=DB_query($sql, $db);
	
	echo '<tr><td><b>' . _('Agrupacion') . ':</b></td><td><select name="GLGroup" >';
	
	//echo "<option selected value=''>Seleccionar Agrupacion Especial...</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['GLGroup']) and $_POST['GLGroup']==$myrow['id']) {
			echo '<option selected value=' . $myrow['id'] . '>' . $myrow['groupname'];
		} else {
			echo '<option value=' . $myrow['id'] . '>' . $myrow['groupname'];
		}
	}
	echo '</select></td></tr>';
	/*********************************/
	
	//------------------Combo Tipo----------------------
	$sql = 'SELECT tipo,nombreMayor FROM chartTipos ORDER BY tipo';
	$result = DB_query($sql, $db);
	
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
	echo '<tr><td><b>'._('Seleccione Una Razon Social:').'</b><td><select name="legalid">';
		
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
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
		
	
	$result=DB_query($SQL,$db);
	echo '<option selected value=all>Todas Las Unidades de Negocio...';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select></td>';
	// End select tag

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
        </tr>";
	
	// End select tag
	echo '<tr><td>'._('Otros parametros').":</td><td><select Name='parametros'>";
	
	if (isset($_POST['parametros']) AND $_POST['parametros'] == '=1')
		echo "<option selected VALUE='=1'>"._('Solo Movimientos Posteados...')."</option>";
	else
		echo "<option VALUE='=1'>"._('Solo Movimientos Posteados...')."</option>";
		
	if (isset($_POST['parametros']) AND $_POST['parametros'] == '=0')	
		echo "<option selected VALUE='=0'>"._('Solo Movimientos NO Posteados...')."</option>";
	else
		echo "<option VALUE='=0'>"._('Solo Movimientos NO Posteados...')."</option>";
				
	echo '</select></td></tr>';
		
        echo "</table>
		<div class='centre'><input type=submit name='Show' VALUE='" . _('Buscar Transacciones') ."'></div>";	
	echo "<BR><div class='centre'><input type=submit name='PrintEXCEL' Value='"._('Exporta a EXCEL')."'></form>";

/* End of the Form  rest of script is what happens if the show button is hit*/

}

if (isset($_POST['Show']) or isset($_POST['PrintEXCEL'])){ 

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
	
	/*Is the account a balance sheet or a profit and loss account */
	$sql = "SELECT pandl
		FROM accountgroups
		INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
		INNER JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode
		WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		
	$result = DB_query($sql,$db);
	$PandLRow = DB_fetch_row($result);
	
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);

	if ($_POST['tag']=='all') {
		
 		$sql= "SELECT gltrans.account, chartmaster.accountname, gltrans.type, typename, gltrans.typeno,
			gltrans.trandate,
			CASE WHEN gltrans.type in(10,11,12,13,21,70,110)
			THEN concat(gltrans.narrative,' @ ',debtorsmaster.name) ELSE
			CASE WHEN gltrans.type in(20,22) THEN concat(gltrans.narrative,' @ ',suppliers.suppname)
			ELSE gltrans.narrative END end AS narrative,
			amount, periodno, tag,debtortrans.folio,
			debtortrans.order_, chartmaster.naturaleza,
			gltrans.posted
			FROM gltrans JOIN tags ON gltrans.tag = tags.tagref
			inner join systypescat on gltrans.type=systypescat.typeid
			JOIN areas ON tags.areacode = areas.areacode
			JOIN regions ON areas.regioncode = regions.regioncode
			JOIN departments ON tags.u_department=departments.u_department
			JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode
			JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode
			LEFT JOIN debtortrans ON gltrans.type = debtortrans.type and gltrans.typeno = debtortrans.transno and gltrans.tag = debtortrans.tagref
			LEFT join debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
			LEFT JOIN supptrans ON gltrans.type = supptrans.type and gltrans.typeno = supptrans.transno
			left join suppliers ON supptrans.supplierno=suppliers.supplierid
			
			WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		
		$sql= $sql." and (areas.regioncode = ".$_POST['xRegion']." or ".$_POST['xRegion']."=0)
			and (areas.areacode = ".$_POST['xArea']." or ".$_POST['xArea']."=0)
			and (departments.u_department = ".$_POST['xDepartamento']." or ".$_POST['xDepartamento']."=0)
			
		AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tags.legalid = ".$_POST['legalid']."";
				
		if($_POST['TipoPoliza'] != 'TODOS'){
			$sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
		}
		
		$sql= $sql." AND gltrans.posted ".$_POST['parametros'];
		 
		$sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, counterindex";
		//echo "<br><br>sql1".$sql;

	} else {
 		$sql= "SELECT gltrans.account,
			chartmaster.accountname, gltrans.type,debtortrans.debtorno,debtorsmaster.name,suppliers.suppname
			typename,
			gltrans.typeno,
			gltrans.trandate,
			CASE WHEN gltrans.type in(10,11,12,13,21,70,110) THEN concat(gltrans.narrative,' @ ',debtorsmaster.name)
			ELSE
			CASE WHEN gltrans.type in(20,22) THEN concat(gltrans.narrative,' @ ',suppliers.suppname)
			ELSE gltrans.narrative END END AS narrative,
			amount,
			periodno,
			tag,debtortrans.folio, debtortrans.order_, chartmaster.naturaleza,
			gltrans.posted
		FROM gltrans JOIN tags ON gltrans.tag = tags.tagref
			INNER JOIN systypescat on gltrans.type=systypescat.typeid
			JOIN chartmaster ON gltrans.account = chartmaster.accountcode
			JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode
			LEFT JOIN debtortrans ON gltrans.type = debtortrans.type and gltrans.typeno = debtortrans.transno  and gltrans.tag = debtortrans.tagref
			LEFT JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
			LEFT JOIN supptrans ON gltrans.type = supptrans.type and gltrans.typeno = supptrans.transno
			LEFT JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
			WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];

		$sql= $sql." 	AND systypescat.typeid=gltrans.type
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tag='".$_POST['tag']."'
		AND legalid = ".$_POST['legalid']."";
		if($_POST['TipoPoliza'] != 'TODOS'){
			$sql= $sql." AND gltrans.type = '".$_POST['TipoPoliza']."'";
		}
		$sql= $sql." AND gltrans.posted ".$_POST['parametros'];
		
		$sql= $sql." ORDER BY gltrans.account, periodno, gltrans.trandate, counterindex";
	}
	//echo $sql;
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table>';

	$TableHeader = "<tr>
			<th>" . _('Fecha') . "</th>
			<th>" . _('Tipo') . "</th>
			<th>" . _('Numero') . "</th>
			<th>" . _('Folio') . "</th>
			<th>" . _('Concepto') . "</th>
			<th>" . _('Cargos') . "</th>
			<th>" . _('Abonos') . "</th>
			<th>" . _('Saldo') . "</th>
			<th>" . _('Unidad Negocio') . "</th>
			<th>" . _('POSTEADA') . '</th>
			</tr>';

	echo $TableHeader;

	if ($PandLAccount==True) {
		$RunningTotal = 0;
	} else {
	       // added to fix bug with Brought Forward Balance always being zero
		$sql = "SELECT sum(bfwd) as bfwd,
			sum(actual) as actual,
			period, naturaleza
		FROM chartdetails JOIN tags ON chartdetails.tagref = tags.tagref
					JOIN chartmaster ON chartdetails.accountcode = chartmaster.accountcode
					JOIN chartspecialaccounts ON chartmaster.accountcode = chartspecialaccounts.accountcode
		WHERE chartspecialaccounts.id = ".$_POST['GLGroup'];
		$sql = $sql ." AND chartdetails.period=" . $FirstPeriodSelected."
		AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
		AND legalid = ".$_POST['legalid']."";
		$sql = $sql ." GROUP BY period, naturaleza";

		$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
		$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
		// --------------------
				
		$RunningTotal =$ChartDetailRow['bfwd'];
		
		echo "<tr bgcolor='#FDFEEF'>
			<td></td><td></td><td></td><td></td>
			<td colspan=3><b>" . _('SALDO INICIAL:') . '</b></td>
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
	$totalcuenta = 0;


	while ($myrow=DB_fetch_array($TransResult)) {
	
		if ($j == 1) {
			
			// DIBUJA ENCABEZADO DE INICIO DE CADA CUENTA CONTABLE
			
			$cuentacontable = $myrow['account'];
			$nombrecuenta = $myrow['accountname'];
			
			
			$sql = "SELECT sum(bfwd) as bfwd,
				sum(actual) as actual,
				period
			FROM chartdetails JOIN tags ON chartdetails.tagref = tags.tagref
			WHERE chartdetails.accountcode = '".$cuentacontable."'
			AND chartdetails.period=" . $FirstPeriodSelected."
			AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
			AND legalid = ".$_POST['legalid']."
			GROUP BY period";
	
			$ErrMsg = _('El detalle de la cuenta') . ' ' . $cuentacontable . ' ' . _('no pudo ser recuperado de la BD');
			
			$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
			$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
			
			echo "<tr bgcolor='#FDFEEF'>
					<td colspan=6><b>I:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
				
			echo '<td></td>
				<td class=number nowrap><b>I:' . number_format($ChartDetailRow['bfwd'],2) . '</b></td>
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
					<td colspan=5><b>T:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
				
				
			echo '  <td class=number nowrap><b>T:' . number_format($saldoxcuentacargos,2) . '</b></td>
				<td class=number nowrap><b>T:' . number_format($saldoxcuentaabonos,2) . '</b></td>
				<td class=number nowrap><b>T:' . number_format($saldoxcuenta,2) . '</b></td>
				<td></td><td></td>
				</tr>';
				
			$sql = "SELECT sum(bfwd) as bfwd,
				sum(actual) as actual,
				period
			FROM chartdetails  JOIN tags ON chartdetails.tagref = tags.tagref
			WHERE chartdetails.accountcode = '".$myrow['account']."'
			AND chartdetails.period=" . $FirstPeriodSelected."
			AND (chartdetails.tagref='".$_POST['tag']."' or 'all'='".$_POST['tag']."')
			AND legalid = ".$_POST['legalid']."
			GROUP BY period";
	
			$ErrMsg = _('El detalle de la cuenta') . ' ' . $cuentacontable . ' ' . _('no pudo ser recuperado de la BD');
			$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
			$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
			
			$cuentacontable = $myrow['account'];
			$nombrecuenta = $myrow['accountname'];
			
			$saldoxcuenta = $ChartDetailRow['bfwd']*$ChartDetailRow['naturaleza'];
			$saldoxcuentacargos = 0;
			$saldoxcuentaabonos = 0;
			
			echo "<tr bgcolor='#FFFFFF'>
					<td colspan=6><b><br><br></b></td>";
				
			echo '<td></td>
				<td class=number><b></b></td>
				<td></td><td></td>
				</tr>';
				
			echo "<tr bgcolor='#FDFEEF'>
					<td colspan=6><b>" . $cuentacontable . ' '. $nombrecuenta . '(INICIAL)</b></td>';
				
			echo '<td></td>
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

		$RunningTotal += $myrow['amount'];
		$PeriodTotal  += $myrow['amount'];
		
		//LLEVA EL SALDO DE ESTA CUENTA...
		//$saldoxcuenta += ($myrow['amount']*$myrow['naturaleza']);
		$saldoxcuenta += ($myrow['amount']);

		if($myrow['amount']>=0){
			$DebitAmount = number_format($myrow['amount'],2);
			$CreditAmount = '';
			$saldoxcuentacargos = $saldoxcuentacargos + $myrow['amount'];
		} else {
			$CreditAmount = number_format(-$myrow['amount'],2);
			$DebitAmount = '';
			$saldoxcuentaabonos = $saldoxcuentaabonos + ($myrow['amount']*-1);
		}
	

		$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
		$URL_to_TransDetail = $rootpath . '/GLTransInquiryV2.php?' . SID . '&TypeID=' . $myrow['type'] . '&TransNo=' . $myrow['typeno'];
		

		//SOLO APLICA PARA TACSA
				
		if ($myrow['type'] == 110){
			$tipo = 10;
		}else{
			$tipo = $myrow['type'];
		}
		
		//if ($myrow['typeno'] == '1707'){
			//echo "<br>" . $myrow['tag'] . "-" . $tipo . "-" . $myrow['order_'] . "-" .  $myrow['typeno'];
		//}
		
		$liga = GetUrlToPrint($myrow['tag'],$tipo,$db);
		//$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$tagref;
		
		//SE PUSO LA CONDICION POR QUE CUANDO ES RECIBO RECIBE UN PARAMETRO DIFERENTE InvoiceNo.
		if ($tipo==70){
			$parametro = $myrow['order_'];
		}else{
			$parametro = $myrow['typeno'];
		}
		
		if ($tipo == 12){
			$URL_to_TransFolio = $rootpath . '/' . $liga . '&InvoiceNo=' . $parametro .  '&Tagref='.$myrow['tag'] ;
		}else{
			$URL_to_TransFolio = $rootpath . '/' . $liga . '&TransNo=' . $parametro .  '&Tagref='.$myrow['tag'];
			
		}
		//link de impresion para pagos contables
		if ($tipo == 1){
			$liga='PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=1';
			$URL_to_TransFolio = $rootpath . '/' . $liga . '&TransNo=' . $parametro . '&periodo='.$ChartDetailRow['period'] .  '&trandate='.$myrow['trandate'] ;
		}
		//link de impresion para entrega orden de compra
		if ($tipo == 25){
			$liga='PDFGrn2.php?';
			//PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=1&TransNo=583&periodo=37&trandate=2010-06-02
			$URL_to_TransFolio = $rootpath . '/' . $liga . '&PONo=' .$parametro  ;
		}
		//link de impresion para ajuste de existencias
		if ($tipo == 17){
			$liga='PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes';
			$URL_to_TransFolio = $rootpath . '/' . $liga . '&type=' .$myrow['type']. '&TransNo=' .$parametro .'&periodo='.$ChartDetailRow['period'] . '&trandate='.$myrow['trandate'];
		}
		//link de impresion para factura de compra
		if ($tipo == 20){
			$liga='PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes';
			$URL_to_TransFolio = $rootpath . '/' . $liga . '&type=' .$myrow['type']. '&TransNo=' .$parametro .'&periodo='.$ChartDetailRow['period'] . '&trandate='.$myrow['trandate'];
		}
		
		if  ($myrow['folio'] == ''){
			$folio = 'IMPRIMIR';
		}else{
			$folio = $myrow['folio'];
		}
		
		
		$tagsql='SELECT tagdescription FROM tags WHERE tagref='.$myrow['tag'];
		$tagresult=DB_query($tagsql,$db);
		$tagrow = DB_fetch_array($tagresult);
		
		/*
		    if($myrow['type']='10' or $myrow['type']='11' or $myrow['type']='12' or $myrow['type']='13' or $myrow['type']='21' or $myrow['type']='110'){
			$narrative=$myrow['narrative'].'@'.$myrow['name']
		}*/
		
		printf("<td>%s</td>
			<td>%s</td>
			<td class=number><a href='%s'>%s</a></td>
			<td><a target='BLANK_' href='%s'>%s</a></td>
			<td>%s</td>
			<td class=number>%s</td>
			<td class=number>%s</td>
			<td class=number nowrap><b>%s</b></td>
			<td>%s</td><td>%s</td>
			</tr>",
			$FormatedTranDate,
			$myrow['typename'],
			$URL_to_TransDetail,
			$myrow['typeno'],
			$URL_to_TransFolio,
			$folio,
			$myrow['narrative'],
			$DebitAmount,
			$CreditAmount,
			number_format($saldoxcuenta,2),
			$tagrow['tagdescription'],
			$myrow['posted']);
	}
	
	//******************************************
	//******IMPRIME ULTIMO TOTAL DE CUENTA
	
	echo "<tr bgcolor='#FDFEEF'>
			<td colspan=5><b>T:" . $cuentacontable . ' '. $nombrecuenta . '</b></td>';
		
		
		echo '	<td class=number nowrap><b>T:' . number_format($saldoxcuentacargos,2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($saldoxcuentaabonos,2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($saldoxcuenta,2) . '</b></td>
			<td></td><td></td>
			</tr>';
	
	echo "<tr bgcolor='#FFFFFF'>
			<td colspan=6><b><br><br></b></td>";
		
	echo '<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
	//******************************************
	

	echo "<tr bgcolor='#FDFEEF'><td></td><td></td><td></td><td colspan=3><b>";
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
	echo "<tr><td colspan=6><br></td></tr>";
	echo "<tr bgcolor='#FDFEEF'><td></td><td></td><td></td>
			<td colspan=2 style='text-align:center'><b>"._('Total Acumulado: ')."</b></td>";
		
		echo '<td class=number nowrap><b>T:' . number_format($totalcuentacargos,2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($totalcuentaabonos,2) . '</b></td>
			<td class=number nowrap><b>T:' . number_format($totalcuenta,2) . '</b></td>
			<td></td><td></td>
			</tr>';
	
	echo "<tr bgcolor='#FFFFFF'>
			<td colspan=6><b><br><br></b></td>";
	echo '<td></td>
		<td class=number><b></b></td>
		<td></td><td></td>
		</tr>';
	//******************************************

	echo '</table>';

if (isset($_POST['PrintEXCEL'])) {
	exit;
}

	
} /* end of if Show button hit */


if (isset($ShowIntegrityReport) and $ShowIntegrityReport==True){
	if (!isset($IntegrityReport)) {$IntegrityReport='';}
	prnMsg( _('Existen diferencias entre el detalle de las transacciones y la informacion del detalle de acumulados de la cuenta en ChartDetails') . '. ' . _('Un registro de las diferencias se muestra abajo'),'warn');
	echo '<p>'.$IntegrityReport;
}



include('includes/footer.inc');
?>
