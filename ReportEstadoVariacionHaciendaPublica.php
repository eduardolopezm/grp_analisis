<?php
include ('includes/session.inc');
$title = _ ( 'Estado de Variacion Hacienda Publica' );

$funcion = 1496;

include ('includes/SecurityFunctions.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

//se empieza a quitar
// todo lo que no sirve//



function obtenermontosxcuenta($groupname, $legalid, $fromperiod, $toperiod,  $tab, $color, $db){
	$montoactual = Array();

	$sql = "SELECT m.group_,
				SUM(c.actual+c.bfwd) as actual
			FROM chartdetails c FORCE INDEX(Period)
				INNER JOIN (
					SELECT tags.legalid,tags.tagref, tags.u_department, areas.areacode,
						regions.idregion, regions.regioncode
					FROM tags
						INNER JOIN areas ON tags.areacode=areas.areacode
						INNER JOIN regions ON areas.regioncode = regions.regioncode
						INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
									AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
				) as tags ON tags.tagref=c.tagref
				INNER JOIN chartmaster m ON c.accountcode = m.accountcode
			WHERE (c.period >= " . $fromperiod . "
					and c.period <= " . ($toperiod) . ")
				AND m.group_ = '" . $groupname . "'
				AND (tags.legalid IN (" . $legalid . ") or '" . $legalid . "'='0')
	 		GROUP BY m.group_
			ORDER BY m.group_";

	$ErrMsg = _ ( 'No se recupero ninguna transaccion por SQL porque' );
	$Subgrup = DB_query($sql, $db, $ErrMsg);

	//echo "<pre>CONsulta----->>".$sql;
	if ($myrow = DB_fetch_array($Subgrup)) {
		$montoactual = $myrow['actual'];
	}else{
		$montoactual = 0;
	}
	
	$sql = "SELECT sectioninaccounts,groupname,parentgroupname,level, formula
				FROM accountgroups
				WHERE parentgroupname = '" . $groupname . "'";
	$Group = DB_query ( $sql, $db );
  //echo "<pre>Segunda consulta ----->".$sql;
	$monto = 0;
	$totalmonto = 0;
	$totalmontos= 0;
	
	
	while ($myrowg = DB_fetch_array ( $Group )) {
		$_SESSION['nivel'] = $_SESSION['nivel']+1;
		
		$strtab = "";
		for($conttab = 1; $conttab <= ($tab*3); $conttab++){
			$strtab = $strtab . "&nbsp;";
		}
		
		$monto = 0;
		$monto = obtenermontosxcuenta($myrowg['groupname'], $legalid, $_POST['FromPeriod'], $_POST['ToPeriod'], $tab+1, $color+16, $db);
		$totalxgrupo[0]+=$monto;
		
		$totalmontos = $totalmontos + $monto;
	}

	$montoactual = $montoactual + $totalmontos;
	

	return $montoactual;



}



function calculoperiodoanterior ($cuenta, $numcuenta, $periodo, $db){				//echo "numero de cuenta......." .$cuenta;
	$sql = "SELECT importe 
		FROM chartdetails_saldosiniciales
		WHERE cuenta = '" . $numcuenta . "' 
				and periodo = '" . $periodo . "' 
				and importe <> ''";
 	$resultfuncion = DB_query ( $sql, $db );
	if ($myrow = DB_fetch_array ( $resultfuncion )){					//echo "<pre> en IF";
		$monto = $myrow['importe'];										//echo "<pre> monto = ".$monto;
	}else{
		$monto = 0;
	}

	$sql = "SELECT groupcodetb,sectioninaccounts,groupname,parentgroupname,level, formula
			FROM accountgroups
			WHERE parentgroupname = '" . $cuenta . "'";
	//echo "<br>" . $sql;
	$Groupss = DB_query ( $sql, $db );								    //echo "<pre>Resultado cuenta hijos --->".$sql;
	$monto_hijo = 0;

	while ($myrowg = DB_fetch_array ( $Groupss )) {
		//echo "<br>@@: " . $cuenta . "==" . $myrowg['groupname'] . "==" . $myrowg['groupcodetb'];
		$montoanterior = calculoperiodoanterior($myrowg['groupname'], $myrowg['groupcodetb'], $periodo, $db);
		$monto_hijo = $monto_hijo + $montoanterior;
	
	}
	$monto = $monto_hijo + $monto;
	//echo "<br>**** " . $monto;
	return $monto;
}




if (isset ( $_POST ['FromPeriod'] ) and ($_POST ['FromPeriod'] > $_POST ['ToPeriod'])) {
	prnMsg ( _ ( 'El periodo seleccionado Desde es posterior al periodo Hasta' ) . '! ' 
			. _ ( 'Favor de re-seleccionar rangos de periodos' ), 'error' );
	$_POST ['SelectADifferentPeriod'] = 'Select A Different Period';
}

if ((!isset($_POST['FromPeriod']) and !isset ($_POST['ToPeriod'])) or isset ($_POST['SelectADifferentPeriod'])){
	if (!isset ($_POST['PrintEXCEL'])){
		include ('includes/header.inc');
		
		echo "<form method='POST' action=" . $_SERVER ['PHP_SELF'] . '?' . SID . '>';
		if (Date ( 'm' ) > $_SESSION ['YearEnd']) {
			$DefaultFromDate = Date ( 'Y-m-d', Mktime ( 0, 0, 0, $_SESSION ['YearEnd'] + 2, 0, Date ( 'Y' ) ) );
			$FromDate = Date ( $_SESSION ['DefaultDateFormat'], Mktime ( 0, 0, 0, $_SESSION ['YearEnd'] + 2, 0, Date ( 'Y' ) ) );
		} else {
			$DefaultFromDate = Date ( 'Y-m-d', Mktime ( 0, 0, 0, $_SESSION ['YearEnd'] + 2, 0, Date ( 'Y' ) - 1 ) );
			$FromDate = Date ( $_SESSION ['DefaultDateFormat'], Mktime ( 0, 0, 0, $_SESSION ['YearEnd'] + 2, 0, Date ( 'Y' ) - 1 ) );
		}
		echo '<table border="0" cellpadding="2" cellspacing="2" style="margin:auto;"><tr><td style="text-align:right;">' . _ ( 'Periodo' ) . ": </td><td><select Name='FromPeriod'>";
		$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
		$Periods = DB_query ( $sql, $db );
		while ( $myrow = DB_fetch_array ( $Periods, $db ) ) {
			if (isset ( $_POST ['FromPeriod'] ) and $_POST ['FromPeriod'] != '') {
				if ($_POST ['FromPeriod'] == $myrow ['periodno']) {
					echo '<option selected VALUE=' . $myrow ['periodno'] . '>' . translatedate(MonthAndYearFromSQLDate ( $myrow ['lastdate_in_period'] ));
				} else {
					echo '<option VALUE=' . $myrow ['periodno'] . '>' . translatedate(MonthAndYearFromSQLDate ( $myrow ['lastdate_in_period']));
				}
			} else {
				if ($myrow ['lastdate_in_period'] == $DefaultFromDate) {
					echo '<option selected VALUE=' . $myrow ['periodno'] . '>' . translatedate(MonthAndYearFromSQLDate ( $myrow ['lastdate_in_period'] ));
				} else {
					echo '<option VALUE=' . $myrow ['periodno'] . '>' . translatedate(MonthAndYearFromSQLDate ( $myrow ['lastdate_in_period'] ));
				}
			}
		}
		echo '</select>';
		if (! isset ( $_POST ['ToPeriod'] ) or $_POST ['ToPeriod'] == '') {
			$lastDate = date ( "Y-m-d", mktime ( 0, 0, 0, Date ( 'm' ) + 1, 0, Date ( 'Y' ) ) );
			$sql = "SELECT periodno FROM periods where lastdate_in_period = '$lastDate'";
			$MaxPrd = DB_query ( $sql, $db );
			$MaxPrdrow = DB_fetch_row ( $MaxPrd );
			$DefaultToPeriod = ( int ) ($MaxPrdrow [0]);
		} else {
			$DefaultToPeriod = $_POST ['ToPeriod'];
		}
		echo "&nbsp;&nbsp;&nbsp;" . _ (' AL ') . "&nbsp;&nbsp;&nbsp;" . "<select Name='ToPeriod'>";
		$RetResult = DB_data_seek ( $Periods, 0 );
		while ( $myrow = DB_fetch_array ( $Periods, $db ) ) {
			if ($myrow ['periodno'] == $DefaultToPeriod) {
				echo '<option selected VALUE=' . $myrow ['periodno'] . '>' . translatedate(MonthAndYearFromSQLDate ( $myrow ['lastdate_in_period'] ));
			} else {
				echo '<option VALUE =' . $myrow ['periodno'] . '>' . translatedate(MonthAndYearFromSQLDate ( $myrow ['lastdate_in_period'] ));
			}
		}
		echo '</select></td></tr>';
		// Select the razon social
		echo '<tr><td>' . _ ( 'Seleccione Una Razon Social' ) . ': <td style="text-align:left;"><select name="legalid">';
		// /Pinta las razones sociales
		$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
		$SQL = $SQL . " FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
		$SQL = $SQL . " WHERE u.tagref = t.tagref ";
		$SQL = $SQL . " and u.userid = '" . $_SESSION ['UserID'] . "'
				  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";
		$result = DB_query ( $SQL, $db );
		while ( $myrow = DB_fetch_array ( $result ) ) {
			if (isset ( $_POST ['legalid'] ) and $_POST ['legalid'] == $myrow ["legalid"]) {
				echo '<option selected value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
			} else {
				echo '<option value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
			}
		}
		echo '</select></td>';
		// End select tag
		/**
		 * *********************************
		 */
	
		
		echo "<tr><td colspan='2'>";
		echo "<table border='0' cellpadding='1' cellspacing='1'>";
		echo "<tr>";
		echo "<td><input type='submit' Name='ShowPL' Value='" . _ ( 'MOSTRAR EN PANTALLA' ) . "'></td>";
		echo "<td><input type='submit' Name='PrintPDF' Value='" . _ ( 'EXPORTAR A PDF' ) . "'></td>";
		echo "<td><input type='submit' Name='PrintEXCEL' Value='" . _ ( 'EXPORTAR A EXCEL' ) . "'></td>";
		echo "</tr>";
		echo "</table>";
		echo "</td></tr>";
		echo '</table>';
		/* Now do the posting while the user is thinking about the period to select */
		// include ('includes/GLPostings.inc');
	}
} else if (isset ( $_POST ['PrintPDF'] )) {
	
} else {
	if (isset ( $_POST ['PrintEXCEL'] )) {
		header ( "Content-type: application/ms-excel" );
		// replace excelfile.xls with whatever you want the filename to default to
		header ( "Content-Disposition: attachment; filename=EstadoVariacionHaciendaPublica.xls" );
		header ( "Pragma: no-cache" );
		header ( "Expires: 0" );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="' . $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	} else {
		include ('includes/header.inc');
		echo "<form method='POST' action=" . $_SERVER ['PHP_SELF'] . '?' . SID . '>';
		echo "<input type=hidden name='FromPeriod' VALUE=" . $_POST ['FromPeriod'] . "><input type=hidden name='ToPeriod' VALUE=" . $_POST ['ToPeriod'] . '>';
	}
	$NumberOfMonths = $_POST ['ToPeriod'] - $_POST ['FromPeriod'] + 1;
	
	if ($NumberOfMonths > 12) {
		echo '<p>';
		prnMsg ( _('Por favor selecciona un periodo diferente, debido a que el reporte muestra un comparativo con al anio anterior' ), 'error' );
		include ('includes/footer.inc');
		exit ();
	}
	
	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST ['ToPeriod'];
	$PrdResult = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $PrdResult );
	$PeriodToDate = MonthAndYearFromSQLDate ( $myrow [0] );
	
	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST ['FromPeriod'];
	$PrdResult = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $PrdResult );
	$PeriodFromDate = MonthAndYearFromSQLDate ( $myrow [0] );
	
	$sql = 'SELECT tagdescription FROM tags WHERE tagref=' . $_POST ['tag'];
	//$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $result );
	$n_unidaddenegocio = $myrow [0];
	
	$sql = 'SELECT name FROM regions WHERE regioncode=' . $_POST ['xRegion'];
	//$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $result );
	$n_region = $myrow [0];
	
	$sql = 'SELECT legalname FROM legalbusinessunit WHERE legalid=' . $_POST ['legalid'];
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_row ( $result );
	$n_razonsocial = $myrow [0];
	
	echo '<div class="centre"><font size=4 color=BLUE><b>' . $title . '<br>' . $n_razonsocial;
	
	if ($_POST ['xRegion'] != 0) {
		echo '<br>REGION:' . $n_region;
	}
	
	echo '<br>' . _ ( 'De:' ) . ' ' . translatedate($PeriodFromDate) . '&nbsp;&nbsp;' . _ ( 'A' ) . '&nbsp;&nbsp;' . translatedate($PeriodToDate) . '</b></font></div><br>';
	
	
	if (isset ( $_POST ['PrintEXCEL'] )) {
		exit ();
	}

}



if (isset($_POST['ShowPL'])){
	//echo "Entro aki ->>>>";
	echo '<table cellpadding="0" cellspacing="0" border="0" width="90%" style="margin:auto;">';
		echo '<tr>';
			echo '<td style="vertical-align: text-top;">';
				echo '<table border="1" cellpadding="0" cellspacing="0">';
					echo "<tr>
						<th width='40%'>" . _ ( 'CONCEPTO' ) . "</th>";
						$arrtemp = explode(' ',$PeriodFromDate);
						$anioperido = trim($arrtemp[1]);
						$SQL = "SELECT *
								FROM accountgroups
								WHERE sectioninaccounts = '3' and level = '1'
								ORDER BY groupcodetb";
						$Section = DB_query ( $SQL, $db );
						$arrgroupname = array();
						$arrtotales = array();
						$indice = -1;
						while ( $myrows = DB_fetch_array ( $Section ) ) {
							$indice++;
							$arrtotales[$indice] = 0;
							$arrgroupname[$indice] = $myrows['groupname'] . "!@" . ($_POST ['FromPeriod']-12);
							$indice++;
							$arrtotales[$indice] = 0;
							$arrgroupname[$indice] = $myrows['groupname'] . "!@" . $_POST ['FromPeriod'];
							
							if ($myrows['groupcodetb'] == '3.2'){
								echo "<th width='12%'> " . $myrows['groupname'] . " " . _('Ejercicio Anterior') . "</th>";
								echo "<th width='12%'> " . $myrows['groupname'] . " " . _('del Ejercicio') . " </th>";
							}else{
								echo "<th width='12%'> " . $myrows['groupname'] . " </th>";
							}
							
						}
						echo "<th>" . _('TOTAL') . "</th>";
					echo "</tr>";
				
					$sqlr = "SELECT *
								FROM accountgroups
								WHERE groupcodetb = '3.2.5'";
					$resultr = DB_query ($sqlr, $db);
					
					if ($myrowr = DB_fetch_array($resultr)) {
						$monto = obtenermontosxcuenta($myrowr['groupname'], $_POST ['legalid'], $_POST['FromPeriod'], $_POST['ToPeriod'], $tab+1, $color+16, $db);
						//$montoanterior = calculoperiodoanterior($myrowr['groupname'], $myrowr['groupcodetb'], $_POST['ToPeriod']-12, $db);
						
						echo "<tr>";
							echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">' . $myrowr['groupname'] . '</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($monto == 0) ? "&nbsp" : number_format($monto,2)) . '</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($monto == 0) ? "&nbsp" : number_format($monto,2)) . '</td>';
						echo "</tr>";
						
						echo "<tr>";
							echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
						echo "</tr>";
						$arrtotales[1] = $arrtotales[1] + $monto[0];
					}
					/***************************/
					
					$sqlc = "SELECT Sum(chartdetails.actual+chartdetails.bfwd) AS accumprofitbfwd,
								'0' AS lyaccumprofitbfwd
							FROM chartdetails FORCE INDEX(Period) INNER JOIN
								(select tags.legalid,tags.tagref, tags.u_department, areas.areacode, regions.idregion, regions.regioncode
								from tags
									INNER JOIN areas ON tags.areacode=areas.areacode
									INNER JOIN regions ON areas.regioncode = regions.regioncode
									INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
								) as tags ON tags.tagref=chartdetails.tagref
								INNER JOIN chartmaster ON chartmaster.accountcode= chartdetails.accountcode
								INNER JOIN chartTipos ON chartmaster.tipo = chartTipos.tipo
								INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
							WHERE accountgroups.pandl=1
								and chartdetails.period=" . $_POST ['FromPeriod'] . "
								and (tags.legalid IN (" . $_POST ['legalid'] . ") or '" . $_POST ['legalid'] . "'='0')";
					$Resultejer = DB_query ( $sqlc, $db, _ ( 'No se obtuvieron registros de la consulta del SQL debido a:' ) );
										
					while ($myrow = DB_fetch_array($Resultejer)) {
						$periodoactual = $myrow['accumprofitbfwd'];
						$periodoanterior = $myrow['lyaccumprofitbfwd'];
						
					}
						
					
					
					$sqlperiods = "SELECT * 
								FROM periods
								WHERE periodno in (". $_POST ['FromPeriod'] . ", " . ($_POST ['FromPeriod']-12) . ")
								ORDER BY periodno asc";
				
					$resulperiods = DB_query ($sqlperiods, $db);
					
					//echo "<br>Periodos" .$sqlperiods;
					while ($myrowperiod = DB_fetch_array ($resulperiods)) {
						$i=-1;
						$sqlg = "SELECT *
							FROM accountgroups
							WHERE sectioninaccounts = '3' and level = '1'
							ORDER BY groupcodetb";
						$resultg = DB_query ($sqlg, $db );
						
						
						while($myrowg = DB_fetch_array ($resultg)){
							if($myrowg['groupname']=='PATRIMONIO CONTRIBUIDO'){
								if($_POST ['FromPeriod'] == $myrowperiod['periodno']){
									echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">' . _('Cambios en la Haciendo Publica  ') . '</td>';
									$monto = obtenermontosxcuenta($myrowg['groupname'], $_POST ['legalid'], $myrowperiod['periodno'], $_POST['ToPeriod'], $tab+1, $color+16, $db);
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($monto == 0) ? "&nbsp" : number_format($monto,2))  . '</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">'. (($monto == 0) ? "&nbsp" : number_format($monto,2)) .'</td>';
								}else{
									echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">' . _('Patrimonio Neto Inicial Ajustado del Ejercicio') . '</td>';
									$anioperiodo = substr($myrowperiod['lastdate_in_period'],0,4);
									if ($anioperiodo <= 2014){
										//echo "++**: " . $myrowg['groupname'] . " ==> " . $myrowg['groupcodetb'];
										$monto = calculoperiodoanterior($myrowg['groupname'], $myrowg['groupcodetb'], $myrowperiod['periodno'], $db);
									}else{
										$monto = obtenermontosxcuenta($myrowg['groupname'], $_POST ['legalid'], $myrowperiod['periodno'], $_POST['ToPeriod'], $tab+1, $color+16, $db);
									}
									
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">'. (($monto == 0) ? "&nbsp" : number_format($monto,2)) .'</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">'. (($monto == 0) ? "&nbsp" : number_format($monto,2)) .'</td>';
									
								}
								
									
							}
						
							if($myrowg['groupname']=='PATRIMONIO GENERADO'){
								echo "<tr>";
									echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">' . _('Variaciones de la Hacienda Publica ') . '</td>';
									if($_POST ['FromPeriod'] == $myrowperiod['periodno']){
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
										$monto = obtenermontosxcuenta($myrowg['groupname'], $_POST ['legalid'], $myrowperiod['periodno'], $_POST['ToPeriod']-12, $tab+1, $color+16, $db);
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . ((($monto+$periodoactual) == 0) ? "&nbsp" : number_format(($monto+$periodoactual),2)) . '</td>';
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . ((($monto+$periodoactual) == 0) ? "&nbsp" : number_format(($monto+$periodoactual),2)) . '</td>';
									}else{
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
										$anioperiodo = substr($myrowperiod['lastdate_in_period'],0,4);
										if ($anioperiodo <= 2014){
											$monto = calculoperiodoanterior($myrowg['groupname'], $myrowg['groupcodetb'], $myrowperiod['periodno'], $db);
										}else{
											$monto = obtenermontosxcuenta($myrowg['groupname'], $_POST ['legalid'], $myrowperiod['periodno'], $_POST['ToPeriod'], $tab+1, $color+16, $db);
										}
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">'. (($monto == 0) ? "&nbsp" : number_format($monto,2)) .'</td>';
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">'. (($monto == 0) ? "&nbsp" : number_format($monto,2)) .'</td>';
										
									}
									//$montofinal = $monto+$periodoactual;
									
								echo "</tr>";
							}
							
							$sqlg2 = "SELECT *
								FROM accountgroups
								WHERE parentgroupname = '" . $myrowg['groupname'] . "'
								ORDER BY groupcodetb";
							$resultg2 = DB_query ($sqlg2, $db );
							while($myrowg2 = DB_fetch_array ($resultg2)){
								echo "<tr>";
									$tab = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
									echo '<td style="font-size:10pt; color:#778899;" nowrap;>' . $tab . $myrowg2['groupcodetb'] . " " . $myrowg2['groupname'] . '</td>';
									$totalxrenglon = 0;
									$cont = count($arrgroupname);
									
									for($i=0; $i<$cont; $i++){
										if($_POST['FromPeriod'] ==$myrowperiod['periodno']){
											$i++;
										}
										$flagtemp = false;
										if((($myrowg['groupname'] ."!@" . $myrowperiod['periodno']) == $arrgroupname[$i])){
											if($_POST['FromPeriod'] ==$myrowperiod['periodno']){
												$monto = obtenermontosxcuenta($myrowg2['groupname'], $_POST ['legalid'], $myrowperiod['periodno'], $_POST['ToPeriod'], $tab+1, $color+16, $db);
											}else{
												$anioperiodo = substr($myrowperiod['lastdate_in_period'],0,4);
												if ($anioperiodo <= 2014){
													$monto = calculoperiodoanterior($myrowg2['groupname'], $myrowg2['groupcodetb'], $myrowperiod['periodno'], $db);
												}else{
													$monto = obtenermontosxcuenta($myrowg2['groupname'], $_POST ['legalid'], ($myrowperiod['periodno']), ($_POST['ToPeriod']), $tab+1, $color+16, $db);
												}
											}
											if ($myrowg['groupcodetb'] == '3.2'){
												$flagtemp = true;
											}
											if ($monto != 0){
												$arrtotales[$i] = $arrtotales[$i] + $monto;
												$totalxrenglon = $totalxrenglon + $monto;
												if($_POST['FromPeriod'] == $myrowperiod['periodno']){
													if ($flagtemp == true){
														echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>'; 
														echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($monto,2) . '</td>';
													}else{
														echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($monto,2) . '</td>';
													}
												}else{
													echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($monto,2) . '</td>';
												}
												
											}else{
												if($myrowg2['groupname']=='RESULTADOS DEL EJERCICIO: (AHORRO/ DESAHORRO)'){
													if($myrowperiod['periodno'] >= 4){
														$arrtotales[3] = $arrtotales[3] + $periodoactual;
														echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
														echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">'.$periodoactual.'</td>';
													}else{
														echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
													}
													
												}else{
													if($_POST['FromPeriod'] == $myrowperiod['periodno']){
														if ($flagtemp == true){
															echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
														}
													}
													echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
												}
											}
										}else{
											if((("PATRIMONIO GENERADO" ."!@" . ($myrowperiod['periodno'])) == $arrgroupname[$i])){
												echo "<td>&nbsp;</td>";
												echo "<td>&nbsp;</td>";
												$i++;
											}else{
												
												echo "<td>&nbsp;</td>";
											}
											
										}
										if($_POST['FromPeriod'] != $myrowperiod['periodno']){
											if ($flagtemp == false){
												$i++;
											}
										}
											
									}
									if($totalxrenglon == 0){
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
									}else{
										echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($totalxrenglon,2) . '</td>';
									}
									
								echo "</tr>";
							}
						}//Se pone el subtotal
						
						if($_POST ['FromPeriod'] == $myrowperiod['periodno']){
								
						}else{
							echo "<tr>";
							echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo "</tr>";
							
							echo "<tr>";
							echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">Hacienda Publica / Patrimonio Neto del Ejercicio </td>';
							
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[0] == 0) ? "&nbsp" : number_format($arrtotales[0],2)) . '</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[2] == 0) ? "&nbsp" : number_format($arrtotales[2],2)) . '</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[4] == 0) ? "&nbsp" : number_format($arrtotales[4],2)) . '</td>';
							$ttotal = (($arrtotales[0]+$arrtotales[2]+$arrtotales[4]) == 0) ? "&nbsp;" : ($arrtotales[0]+$arrtotales[2]+$arrtotales[4]); 
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($ttotal,2) . '</td>';
							echo "</tr>";
							
							echo "<tr>";
							echo '<td style="font-size:10pt; color:#000000; font-weight:bold;">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">&nbsp;</td>';
							echo "</tr>";
						}
					}
					
					
				/*	
				echo '<tr>';
					echo '<td style="font-size:10pt; color:#778899;">' . _('Saldo Neto en la Hacienda Publica ') . '</td>';
					$cont = count($arrgroupname);
					$total = 0;
					for($i=0; $i<$cont; $i++){
						$total = $total +  $arrtotales[$i];
						echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($arrtotales[$i],2) . '</td>';
					}
					echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($total,2) . '</td>';
				echo '</tr>';
				*/
				echo "<tr>";
					echo '<td style="font-size:10pt; color:#778899;">' . _('Saldo Neto en la Hacienda Publica ') . '</td>';
					echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[0] == 0) ? "&nbsp" : number_format($arrtotales[0],2)) . '</td>';
					echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[2] == 0) ? "&nbsp" : number_format($arrtotales[2],2)) . '</td>';
					echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[3] == 0) ? "&nbsp" : number_format($arrtotales[3],2)) . '</td>';
					echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . (($arrtotales[4] == 0) ? "&nbsp" : number_format($arrtotales[4],2)) . '</td>';
					$ttotal = (($arrtotales[0]+$arrtotales[2]+$arrtotales[3]+$arrtotales[4]) == 0) ? "&nbsp;" : ($arrtotales[0]+$arrtotales[2]+$arrtotales[3]+$arrtotales[4]);
					echo '<td style="font-size:10pt; color:#778899; font-weight:bold; text-align:center">' . number_format($ttotal,2) . '</td>';
				echo "</tr>";
					
					
				echo "<tr>
						<td colspan='6' style='text-align:center'><input type=submit Name='SelectADifferentPeriod' Value='" . _ ( 'Selecciona un periodo diferente' ) . "'>
					</tr>";
			echo "</table>";
		echo "</td>";
	echo "</tr>";
	echo "</table>";
}
include ('includes/footer.inc');

?>
