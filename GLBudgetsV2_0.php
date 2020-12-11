<?php
/*Cambios:
  
 ejecutar este script
 
 DROP TABLE IF EXISTS `chartdetailsbudget`;
CREATE TABLE `chartdetailsbudget` (
  `budgetid` int(11) NOT NULL AUTO_INCREMENT,
  `accountcode` varchar(50) DEFAULT NULL,
  `legalid` int(11) DEFAULT NULL,
  `budget` float DEFAULT NULL,
  `period` double DEFAULT NULL,
  PRIMARY KEY (`budgetid`),
  KEY `index1` (`accountcode`,`legalid`,`period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
 insert chartdetailsbudget
			select null, chartmaster.accountcode, legalbusinessunit.legalid,0,periods.periodno
			from chartmaster CROSS JOIN (select * from periods 
			UNION
			select periodno+0.5 as periodno, lastdate_in_period
			from periods where MID(lastdate_in_period,6,2) = 12
			) as periods CROSS JOIN legalbusinessunit
			left join chartdetailsbudget ON chartdetailsbudget.accountcode = chartmaster.accountcode and
			chartdetailsbudget.period = periods.periodno and
			chartdetailsbudget.legalid = legalbusinessunit.legalid
			where chartdetailsbudget.accountcode is null
			
  
1.- Se agrego el  include('includes/SecurityFunctions.inc');*/
$PageSecurity = 10;

include('includes/session.inc');
$funcion=129;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Creacion de presupuesto contable');

include('includes/header.inc');

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}

if (isset($_POST['Previous'])) {
	$SelectedAccount = $_POST['PrevAccount'];
} elseif (isset($_POST['Next'])) {
	$SelectedAccount = $_POST['NextAccount'];
}

if (isset($_POST['update'])) {
	prnMsg('Budget updated successfully', 'success');
}

//If an account hasn't been selected then select one here.
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="selectaccount">';
echo '<table>';

echo '</br><tr><td>'.  _('Select GL Account').  ":</td><td><select name='SelectedAccount' 
		onChange='ReloadForm(selectaccount.Select)'>";

$SQL = 'SELECT accountcode,
						accountname
					FROM chartmaster
					ORDER BY accountcode';

$result=DB_query($SQL,$db);
if (DB_num_rows($result)==0){
	echo '</select></td></tr>';
	prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('budgets cannot be allocated until the GL accounts are set up'),'warn');
} else {
	while ($myrow=DB_fetch_array($result)){
		$account = $myrow['accountcode'] . ' - ' . $myrow['accountname'];
		if (isset($SelectedAccount) and isset($LastCode) and $SelectedAccount==$myrow['accountcode']){
			echo '<option selected value=' . $myrow['accountcode'] . '>' . $account;
			$PrevCode=$LastCode;
		} else {
			echo '<option value=' . $myrow['accountcode'] . '>' . $account;
			if (isset($SelectedAccount) and isset($LastCode) and $SelectedAccount == $LastCode) {
				$NextCode=$myrow['accountcode'];
			}
		}
		$LastCode=$myrow['accountcode'];
	}
	echo '</select></td></tr>';
}
echo "<tr>";
echo "<tr><td>"._('Razon social')."</td>";
$SQL = "SELECT legalbusinessunit.legalid,
				legalbusinessunit.legalname
		FROM legalbusinessunit";
$result = DB_query($SQL, $db);
echo "<td><select name='razonsocial'>";
//echo '<option selected value=0>Todas las razones sociales</option>';
while($myrow = DB_fetch_array($result)){
	if($_POST['razonsocial'] == $myrow['legalid']){
		echo "<option selected value='".$myrow['legalid']."'>".$myrow['legalid'].' - '.$myrow['legalname']."</option>";
	}else{
		echo "<option value='".$myrow['legalid']."'>".$myrow['legalid'].' - '.$myrow['legalname']."</option>";
	}
}
echo '</select></td>';
echo "</tr>";
if (!isset($PrevCode)) {$PrevCode='';}
if (!isset($NextCode)) {$NextCode='';}

echo '<input type="hidden" name=PrevAccount value='.$PrevCode.'>';
echo '<input type="hidden" name=NextAccount value='.$NextCode.'>';
echo '</table>';
echo "<br><table><tr><td><input type='submit' name=Previous value='" . _('Prev Account') . "'></td>";
echo "<td><input type='submit' name=Select value='" . _('Select Account') . "'></td>";
echo "<td><input type='submit' name=Next value='" . _('Next Account') . "'></td></tr>";
echo '</table></br>';
echo '</form>';

// End of account selection

if (isset($SelectedAccount) and $SelectedAccount != '') {

	$CurrentYearEndPeriod = GetPeriodByDate(Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],0)),$db);

// If the update button has been hit, then update chartdetails with the budget figures
// for this year and next.
	if (isset($_POST['update'])) {
		
		$ErrMsg = _('Cannot update GL budgets');
		$DbgMsg = _('The SQL that failed to update the GL budgets was');
		for ($i=1; $i<=12; $i++) {
			//a–o anterior
			
			$SQL="SELECT *
				  FROM chartdetailsbudget
				  WHERE legalid=".$_POST['razonsocial']."
				  		AND accountcode = '" . $SelectedAccount."'
				  		AND period='".($CurrentYearEndPeriod-(24-$i))."';
				  		";
			$GetCurrency = DB_query($SQL,$db,$ErrMsg);
			if (DB_num_rows($GetCurrency)==0) {
				// Inserta informacion para la cuenta y razon social seleccionada
				$SQL="INSERT chartdetailsbudget
					select null, '".$SelectedAccount."', ".$_POST['razonsocial'].",0,periods.periodno
					from periods 
					WHERE periodno=".($CurrentYearEndPeriod-(24-$i));
				$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			
			}
			
			$SQL='UPDATE chartdetailsbudget 
				  SET budget='.Round($_POST[$i.'last'],2). '
					WHERE period=' . ($CurrentYearEndPeriod-(24-$i)) ."
					AND  accountcode = '" . $SelectedAccount."'
					AND  legalid = '".$_POST['razonsocial']."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			
			//a–o actual
			$SQL="SELECT *
				  FROM chartdetailsbudget
				  WHERE legalid=".$_POST['razonsocial']."
				  		AND accountcode = '" . $SelectedAccount."'
				  		AND period='".($CurrentYearEndPeriod-(12-$i))."';
				  		";
			$GetCurrency = DB_query($SQL,$db,$ErrMsg);
			if (DB_num_rows($GetCurrency)==0) {
				// Inserta informacion para la cuenta y razon social seleccionada
				$SQL="INSERT chartdetailsbudget
					select null, '".$SelectedAccount."', ".$_POST['razonsocial'].",0,periods.periodno
					from periods
					WHERE periodno=".($CurrentYearEndPeriod-(12-$i));
				$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
					
			}
			
			$SQL='UPDATE chartdetailsbudget 
				  SET budget='.Round($_POST[$i.'this'],2). '
					WHERE period=' . ($CurrentYearEndPeriod-(12-$i)) ."
					AND  accountcode = '" . $SelectedAccount."'
					AND  legalid = '".$_POST['razonsocial']."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);

			// proximo a–o
			$SQL="SELECT *
				  FROM chartdetailsbudget
				  WHERE legalid=".$_POST['razonsocial']."
				  		AND accountcode = '" . $SelectedAccount."'
				  		AND period='".($CurrentYearEndPeriod+($i))."';
				  		";
			$GetCurrency = DB_query($SQL,$db,$ErrMsg);
			if (DB_num_rows($GetCurrency)==0) {
				// Inserta informacion para la cuenta y razon social seleccionada
				$SQL="INSERT chartdetailsbudget
					select null, '".$SelectedAccount."', ".$_POST['razonsocial'].",0,periods.periodno
					from periods
					WHERE periodno=".($CurrentYearEndPeriod+($i));
				$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
					
			}
			
			$SQL='UPDATE chartdetailsbudget
					SET budget='.Round($_POST[$i.'next'],2).'
					WHERE period=' .  ($CurrentYearEndPeriod+$i) ."
					AND  accountcode = '" . $SelectedAccount."'
					AND  legalid = '".$_POST['razonsocial']."'";
			$result=DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			
		}
	}
// End of update

	$YearEndYear=Date('Y', YearEndDate($_SESSION['YearEnd'],0));
	//echo $YearEndYear;

/* If the periods dont exist then create them - There must be a better way of doing this
*/
	for ($i=1; $i <=36; $i++) {
		$MonthEnd=mktime(0,0,0,$_SESSION['YearEnd']+1+$i,0,$YearEndYear-2);
		$period=GetPeriodByDate(Date($_SESSION['DefaultDateFormat'],$MonthEnd),$db);
		$PeriodEnd[$period]=Date('M Y',$MonthEnd);
	}
	//echo 'RZ:'.$_POST['razonsocial'];
// End of create periods
	$SQL="SELECT period,
					(budget) AS budget
				FROM chartdetailsbudget 
				WHERE accountcode='" . $SelectedAccount."'
						and  legalid=".$_POST['razonsocial']."
								and period not like '%.5%'
				group by period";
// echo '<br>sql:'.$SQL;
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)) {
		$budget[intval($myrow['period'])]=$myrow['budget'];
		
	}
	$SQL='SELECT period,
					(budget) AS budget,
					SUM(actual) AS actual
				FROM chartdetails inner join tags on tags.tagref=chartdetails.tagref
				WHERE accountcode=' . $SelectedAccount.'
						and  legalid='.$_POST['razonsocial'].'
						and period not like "%.5%"
				group by period';
//	echo '<br>sql:'.$SQL;
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)) {
		$actual[intval($myrow['period'])]=$myrow['actual'];
	}
	
	
	
	if (isset($_POST['apportion'])) {
		for ($i=1; $i<=12; $i++) {
			if ($_POST['AnnualAmount'] != '0') 
				$budget[$CurrentYearEndPeriod+($i)]	= $_POST['AnnualAmount']/12;
			if ($_POST['AnnualAmountTY'] != '0') 
				$budget[$CurrentYearEndPeriod+($i)-12]	= $_POST['AnnualAmountTY']/12;
		}
	}

	$LastYearActual=0;
	$LastYearBudget=0;
	$ThisYearActual=0;
	$ThisYearBudget=0;
	$NextYearActual=0;
	$NextYearBudget=0;

// Table Headers

	echo '<form name="form" action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
	echo '<br><table>';
	echo '<tr><th colspan=3>'. _('Anterior') .'</th>';
	echo '<th colspan=3> '. _('Actual') .'</th>';
	echo '<th colspan=3> '. _('Siguiente') .'</th></tr>';

	echo '<tr><th colspan=3>  '.
		Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'], -1)) .'</th>';
	echo '<th colspan=3>  '.
		Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],0)) .'</th>';
	echo '<th colspan=3>  '.
		Date($_SESSION['DefaultDateFormat'],YearEndDate($_SESSION['YearEnd'],1)) .'</th></tr>';

	echo '<tr>';
	for ($i=0; $i<3; $i++) {
		echo '<th>'. _('Periodo'). '</th>
				<th>'. _('Actual') . '</th>
				<th>'. _('Presupuesto') . '</th>';
	}
	echo '</tr>';


// Main Table

	for ($i=1; $i<=12; $i++) {
		echo '<tr>';
		echo '<th>'. $PeriodEnd[$CurrentYearEndPeriod-(24-$i)] .'</th>';
		echo '<td bgcolor="d2e5e8" class="number">'.number_format($actual[$CurrentYearEndPeriod-(24-$i)],2,'.','').'</td>';
		echo '<td><input type="text" onKeyPress="return restrictToNumbers(this, event)" class="number" size=14 name='.$i.'last'.' value="'.number_format($budget[$CurrentYearEndPeriod-(24-$i)],2,'.','').'"></td>';
		
		
		
		//echo '<td bgcolor="d2e5e8" class="number">'.number_format($budget[$CurrentYearEndPeriod-(24-$i)],2,'.','').'</td>';
		
		echo '<th>'. $PeriodEnd[$CurrentYearEndPeriod-(12-$i)] .'</th>';
		echo '<td bgcolor="d2e5e8" class="number">'.number_format($actual[($CurrentYearEndPeriod-(12-$i))],2,'.','').'</td>';
		echo '<td><input type="text" onKeyPress="return restrictToNumbers(this, event)" class="number" size=14 name='.$i.'this'.' value="'.number_format($budget[$CurrentYearEndPeriod-(12-$i)],2,'.','').'"></td>';
		
		echo '<th>'. $PeriodEnd[$CurrentYearEndPeriod+($i)] .'</th>';
		echo '<td bgcolor="d2e5e8" class="number">'.number_format($actual[$CurrentYearEndPeriod+($i)],2,'.','').'</td>';
		echo '<td><input type="text" onKeyPress="return restrictToNumbers(this, event)" class="number" size=14 name='.$i.'next'.' value='.number_format($budget[$CurrentYearEndPeriod+($i)],2,'.','').'></td>';
		echo '</tr>';
		$LastYearActual=$LastYearActual+$actual[$CurrentYearEndPeriod-(24-$i)];
		$LastYearBudget=$LastYearBudget+$budget[$CurrentYearEndPeriod-(24-$i)];
		$ThisYearActual=$ThisYearActual+$actual[$CurrentYearEndPeriod-(12-$i)];
		$ThisYearBudget=$ThisYearBudget+$budget[$CurrentYearEndPeriod-(12-$i)];
		$NextYearActual=$NextYearActual+$actual[$CurrentYearEndPeriod+($i)];
		$NextYearBudget=$NextYearBudget+$budget[$CurrentYearEndPeriod+($i)];
	}

// Total Line

	echo '<tr><th>'. _('Total') .'</th>';
	echo '<th align="right">'.number_format($LastYearActual,2,'.',''). '</th>';
	echo '<th align="right">'.number_format($LastYearBudget,2,'.',''). '</th>';
	echo '<th align="right"></th>';
	echo '<th align="right">'.number_format($ThisYearActual,2,'.',''). '</th>';
	echo '<th align="right">'.number_format($ThisYearBudget,2,'.',''). '</th>';
	echo '<th align="right"></th>';
	echo '<th align="right">'.number_format($NextYearActual,2,'.',''). '</th>';
	echo '<th align="right">'.number_format($NextYearBudget,2,'.',''). '</th></tr>';
	echo '<tr><td></td><td></td><td></td><td colspan=2>'._('Annual Budget').'</td>';
	echo '<td><input onKeyPress="return restrictToNumbers(this, event)" type="text" size=14 name="AnnualAmountTY" style="text-align: right" value=0.00></td>';
	echo '</td><td></td><td>';
	echo '<td><input onChange="numberFormat(this,2)" onKeyPress="return restrictToNumbers(this, event)" type="text" size=14 name="AnnualAmount" style="text-align: right" value=0.00></td>';
	echo '<td><input type="submit" name="apportion" value="Apportion budget"></td>';
	echo '</tr>';
	echo '</table>';
	echo '<input type="hidden" name="SelectedAccount" value='.$SelectedAccount.'>';
	echo '<input type="hidden" name=razonsocial value="'.$_POST['razonsocial'].'">';

	echo "<script>defaultControl(document.form.1next);</script>";
	echo '</br><div class="centre"><input type="submit" name=update value="' . _('Actualizar') . '"></div></form>';

	$SQL="SELECT MIN(periodno) FROM periods";
	$result=DB_query($SQL,$db);
	$MyRow=DB_fetch_array($result);
	$FirstPeriod=$MyRow[0];

	$SQL="SELECT MAX(periodno) FROM periods";
	$result=DB_query($SQL,$db);
	$MyRow=DB_fetch_array($result);//
	$LastPeriod=$MyRow[0];

	/* for ($i=$FirstPeriod;$i<=$LastPeriod;$i++) {
		$sql='SELECT accountcode,
							period,
							budget,
							actual,
							bfwd,
							bfwdbudget
						FROM chartdetails
						WHERE period ='. $i . ' AND  accountcode = ' . $SelectedAccount;

		$ErrMsg = _('Could not retrieve the ChartDetail records because');
		$result = DB_query($sql,$db,$ErrMsg);

		while ($myrow=DB_fetch_array($result)){

			$CFwdBudget = $myrow['bfwdbudget'] + $myrow['budget'];
			$sql = 'UPDATE chartdetails SET bfwdbudget=' . $CFwdBudget . ' 
					WHERE period=' . ($myrow['period'] +1) . ' 
					AND  accountcode = ' . $SelectedAccount.'
					AND  chartdetails.tagref in ( 	SELECT tags.tagref
							   						FROM tags
													WHERE tags.legalid = "'.$_POST['razonsocial'].'" or "'.$_POST['razonsocial'].'" = "0")';

			$ErrMsg =_('Could not update the chartdetails record because');
			$updresult = DB_query($sql,$db,$ErrMsg);
		}
	}*/ /* end of for loop */
}

include('includes/footer.inc');

?>