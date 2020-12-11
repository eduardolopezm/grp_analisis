<?PHP
$PageSecurity = 8;
include('includes/session.inc');

$funcion = 110;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include ('Numbers/Words.php');

include('includes/PDFStarter.php');
$PageNumber = 0;
$FontSize = 10;
$pdf->addinfo('Title', utf8_decode(_('Balanza de Comprobación') ));
$pdf->addinfo('Subject', utf8_decode(_('Balanza de Comprobación') ));
$line_height = 10;


$arreglo = explode("_",$_GET['legalid']);
$Razon = $arreglo[0];

if ($_POST['xRSocial'] != 0){
	$arrxRSocial = explode("_",$_POST['xRSocial']);
	$idRSocial = $arrxRSocial[0];
	$lblRSocial = $arrxRSocial[1];	
    }else{
	$idRSocial = "0";
	$lblRSocial = "";
    }
	
    if ($_POST['xRegion'] != 0){
        $arrxRegion = explode("_",$_POST['xRegion']);
        $idRegion = $arrxRegion[0];
        $lblRegion = $arrxRegion[1];
    }else{
	$idRegion = "0";
	$lblRegion = "";
		
    }
	
    if ($_POST['xArea'] != 0){
	$arrxArea = explode("_",$_POST['xArea']);
	$idArea = $arrxArea[0];
	$lblArea = $arrxArea[1];
    }else{
	$idArea = "0";
	$lblArea = "";
    }
	
    if ($_POST['unidadnegocio'] != 0){
	$arrunidadnegocio = explode("_",$_POST['unidadnegocio']);
	$idunidadnegocio = $arrunidadnegocio[0];
	$lblunidadnegocio = $arrunidadnegocio[1];
    }else{
	$idunidadnegocio = "0";
	$lblunidadnegocio = "";
    }

$NumberOfMonths = $_GET['Hasta'] - $_GET['Desde'] + 1;

$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_GET['Hasta'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);
	
	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_GET['Desde'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodFromDate = MonthAndYearFromSQLDate($myrow[0]);

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
/*
if (isset($_GET['noZeroes'])){
	$noZeroes=$_GET['noZeroes'];
}else{
	$noZeroes=0;
}

if ($noZeroes == 0) {*/


$tagref=$_GET['unidadnegocio'];
$xDepartamento=$_GET['xDepartamento'];
$xArea=$_GET['xArea'];
$xRegion=$_GET['xRegion'];
$legalid=$_GET['legalid'];
$noZeroes=$_GET['noZeroes'];
$accounttype=$_GET['accounttype'];
$arrlegalid = explode("_",$_GET['legalid']);
$legalid=$arrlegalid[0];
//$legalid=$_GET['legalid'];
$SQLGroupby='';

if (isset($_GET['noZeroes']) and $_GET['noZeroes']==0) {

$SQL = 'SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			chartmaster.tipo,
			chartmaster.naturaleza,';
		$SQL = $SQL.'
			Sum(CASE WHEN chartdetails.period=' . $_GET['Desde'] . ' THEN chartdetails.bfwd ELSE 0 END) AS prdInicial,
			sum(chartdetails.actual) AS prdActual,
			sum(chartdetails.cargos) AS prdCargos,
			sum(chartdetails.abonos) AS prdAbonos,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Desde'] . ' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS prdFinal,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
			
		FROM chartdetails FORCE INDEX(Period) INNER JOIN 
			(select tags.legalid,tags.tagref, tags.u_department, areas.areacode, regions.idregion, regions.regioncode 
			from tags 
				INNER JOIN areas ON tags.areacode=areas.areacode
				INNER JOIN regions ON areas.regioncode = regions.regioncode
				INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			) as tags ON tags.tagref=chartdetails.tagref 
												
			INNER JOIN chartmaster ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
		
		WHERE chartdetails.period>=' .      $_GET['Desde'] . ' and chartdetails.period<=' . $_GET['Hasta'] . '
			AND chartmaster.group_<>"" ';
		
		/*
		 	FROM chartdetails INNER JOIN chartmaster ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
			INNER JOIN tags ON tags.tagref=chartdetails.tagref
			INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			INNER JOIN departments ON tags.u_department = departments.u_department
			INNER JOIN areas ON tags.areacode=areas.areacode
			INNER JOIN regions ON areas.regioncode = regions.regioncode*/
		
		if ($tagref<>0){
			$SQL = $SQL . " AND tags.tagref ='".$tagref."'";
			$SQLGroupby=$SQLGroupby.', tags.tagref ';
		}
		if ($xDepartamento<>0){
			$SQL = $SQL . ' AND tags.u_department = '.$xDepartamento;
			$SQLGroupby=$SQLGroupby.', tags.u_department ';
		}
		if ($xRegion<>0){
			$SQL = $SQL . ' AND areas.regioncode ='."'".$xRegion."'";
			$SQLGroupby=$SQLGroupby.', areas.regioncode ';
		}
		if ($legalid<>0){
			$SQL = $SQL . ' AND tags.legalid='.$legalid;
			$SQLGroupby=$SQLGroupby.', tags.legalid ';
		}
		
		if ($accounttype<>0){
			//DE MAYOR
			if ($accounttype==1){
				$SQL = $SQL . ' AND chartdetails.level='.$accounttype;
			
			//SUBCUENTAS
			}elseif ($accounttype==2){
				$SQL = $SQL . ' AND chartdetails.level>='.$accounttype;
			
			//ULTIMO NIVEL
			}else{
				$SQL = $SQL . ' AND chartdetails.level>='.$accounttype;
			}
		}
		
		$SQL = $SQL . '
		GROUP BY accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			chartmaster.tipo,
			chartmaster.naturaleza ';
		//$SQL=$SQL.' '.$SQLGroupby;
		/*$SQL=$SQL. '
		ORDER BY 
			accountgroups.sequenceintb,
			chartdetails.accountcode,
			accountgroups.groupname';*/
		$SQL = $SQL. " ORDER BY chartdetails.accountcode ,accountgroups.sequenceintb";
	} else {
		$SQL = 'SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			chartmaster.tipo,
			chartmaster.naturaleza,';
		$SQL = $SQL.'
			Sum(CASE WHEN chartdetails.period=' . $_GET['Desde'] . ' THEN chartdetails.bfwd ELSE 0 END) AS prdInicial,
			sum(chartdetails.actual) AS prdActual,
			sum(chartdetails.cargos) AS prdCargos,
			sum(chartdetails.abonos) AS prdAbonos,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Desde'] . ' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS prdFinal,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period=' . $_GET['Hasta'] . ' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		
		FROM chartdetails FORCE INDEX(Period) INNER JOIN 
			(select tags.legalid,tags.tagref, tags.u_department, areas.areacode, regions.idregion, regions.regioncode 
			from tags 
				INNER JOIN areas ON tags.areacode=areas.areacode
				INNER JOIN regions ON areas.regioncode = regions.regioncode
				INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			) as tags ON tags.tagref=chartdetails.tagref 
												
			INNER JOIN chartmaster ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN accountgroups ON chartmaster.group_ = accountgroups.groupname
		
		WHERE chartdetails.period>=' .      $_GET['Desde'] . ' and chartdetails.period<=' . $_GET['Hasta'] . '
			AND chartmaster.group_<>"" ';
			
		if ($tagref<>0){
			$SQL = $SQL . " AND tags.tagref ='".$tagref."'";
			$SQLGroupby=$SQLGroupby.', tags.tagref ';
		}
		if ($xDepartamento<>0){
			$SQL = $SQL . ' AND tags.u_department = '.$xDepartamento;
			$SQLGroupby=$SQLGroupby.', tags.u_department ';
		}
		if ($xRegion<>0){
			$SQL = $SQL . ' AND areas.regioncode ='."'".$xRegion."'";
			$SQLGroupby=$SQLGroupby.', areas.regioncode ';
		}
		
		//echo 'xxxxxxxxxxx'.$legalid;
		if ($legalid<>0){
			$SQL = $SQL . ' AND tags.legalid='.$legalid;
			$SQLGroupby=$SQLGroupby.', tags.legalid ';
		}
		
		if ($accounttype<>0){
			if ($accounttype<3){
				$SQL = $SQL . ' AND chartdetails.level='.$accounttype;
			}else{
				$SQL = $SQL . ' AND chartdetails.level>='.$accounttype;
			}
			$SQLGroupby=$SQLGroupby.', chartdetails.level ';
		}
		$SQL = $SQL . '
		GROUP BY accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			chartmaster.tipo,
			chartmaster.naturaleza ';
		//$SQL=$SQL.' '.$SQLGroupby;
		
				
		if ($noZeroes==1){
			$SQL = $SQL . 'HAVING
					 (
						abs(prdActual) > 0.1
					)
				      ';
				      
		}elseif($noZeroes==2){
			$SQL = $SQL . ' HAVING (abs(prdActual + prdFinal) > 0.1) ';
			
		}elseif($noZeroes==3){
			$SQL = $SQL . ' HAVING (abs(prdActual + prdFinal) > 0.1 and abs(prdActual) > 0.1) ';
			
		}elseif($noZeroes==4){
			$SQL = $SQL . ' HAVING (abs(prdActual + prdFinal) > 0.1 or abs(prdActual) > 0.1) ';	
		}
		
		//$SQL=$SQL.' '.$SQLGroupby;
		/*$SQL=$SQL.'
			ORDER BY 
			accountgroups.sequenceintb,
			chartdetails.accountcode,
			accountgroups.groupname
			';*/
		$SQL = $SQL. " ORDER BY chartdetails.accountcode ,accountgroups.sequenceintb";
	}
	//echo "<br>".$SQL."<br>".$unidadnegocio.' '.$_POST['unidadnegocio'];
	//exit;
	
	//echo $SQL;
	//exit;
	$AccountsResult = DB_query($SQL,$db,_('Ninguna cuenta contable se recupero por el SQL porque'),_('El SQL que fallo fue:'));
	if (DB_error_no($db) !=0) {
		$title = utf8_decode(_('Balanza de Comprobación')) . ' - ' . _('Reporte de Problema') . '....';
		include('includes/header.inc');
		include('includes/footer.inc');
		exit;
	}
	
	///Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' and legalbusinessunit.legalid = ".$legalid."
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		
	
	//echo $SQL;
	//exit;
	$result=DB_query($SQL,$db);
	
	include('includes/PDFGLTrialBalanceHeader.inc');
	
	if ($myrow=DB_fetch_array($result)){
		//$pdf->addTextWrap(200,$YPos + 15,300,10,$myrow['legalname']);
	}
	
	$FontSize = 7.5;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,utf8_decode(_('Código Cuenta')));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,100,$FontSize,_('Nombre de la Cuenta'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,70,$FontSize,_('Saldo Inicial'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+325,$YPos,100,$FontSize,utf8_decode(_('Rango de Períodos')),'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+305,$YPos -10,70,$FontSize,_('Cargos'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos -10,70,$FontSize,_('Abonos'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+440,$YPos,70,$FontSize,_('Saldo Final'),'right');
	
	$YPos -= (1 * $line_height);
	$FontSize = 8;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,'');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,100,$FontSize,'');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,140,$FontSize,_('Deudora   Acreedora'),'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,'','right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+370,$YPos,70,$FontSize,'','right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+450,$YPos,140,$FontSize,_('Deudora   Acreedora'),'left');
	
	$pdf->selectFont('./fonts/Arial.afm');
	$YPos -= (1 * $line_height);
	
	$j = 1;
	$k=0; //row colour counter
	$ActGrp ='';
	$ParentGroups = array();
	$Level =1; //level of nested sub-groups
	$ParentGroups[$Level]='';
	$GrpActual =array(0);
	$GrpBudget =array(0);
	$GrpPrdActual =array(0);
	$GrpPrdBudget =array(0);
		
	$PeriodProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;
	$MonthProfitLoss = 0;
	$MonthBudgetProfitLoss = 0;
	$BFwdProfitLoss = 0;
	$CheckMonth = 0;
	$CheckBudgetMonth = 0;
	$CheckPeriodActual = 0;
	$CheckPeriodBudget = 0;
		
	$totalInicial = 0;
	$totalInicialAcreedora = 0;
	$totalCargos = 0;
	$totalAbonos = 0;
	$totalFinal = 0;
	$totalFinalAcreedora = 0;

	while ($myrow=DB_fetch_array($AccountsResult))
	{
		if ($YPos < ($Bottom_Margin+ (2 * $line_height)))
		{
			include('includes/PDFGLTrialBalanceHeader.inc');
			
		}
		
		if ($myrow['pandl']==1){
			$AccountPeriodActual = $myrow['prdFinal'] - $myrow['prdInicial'];
			$AccountPeriodBudget = $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];

			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $myrow['monthactual'];
			$MonthBudgetProfitLoss += $myrow['monthbudget'];
			$BFwdProfitLoss += $myrow['prdInicial'];
		} else { /*PandL ==0 its a balance sheet account */
			if ($myrow['accountcode']==$RetainedEarningsAct){
				$AccountPeriodActual = $BFwdProfitLoss + $myrow['prdFinal'];
				$AccountPeriodBudget = $BFwdProfitLoss + $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
			} else {
				$AccountPeriodActual = $myrow['prdFinal'];
				$AccountPeriodBudget = $myrow['prdInicial'] + $myrow['lastprdbudgetcfwd'] - $myrow['firstprdbudgetbfwd'];
			}

		}
		
		if (!isset($GrpActual[$Level])) {
			$GrpActual[$Level]=0;
		}
		if (!isset($GrpBudget[$Level])) {
			$GrpBudget[$Level]=0;
		}
		if (!isset($GrpPrdActual[$Level])) {
			$GrpPrdActual[$Level]=0;
		}
		if (!isset($GrpPrdBudget[$Level])) {
			$GrpPrdBudget[$Level]=0;
		}
				
		$line_height=2;
		$GrpActual[$Level] +=$myrow['monthactual'];
		$GrpBudget[$Level] +=$myrow['monthbudget'];
		$GrpPrdActual[$Level] +=$AccountPeriodActual;
		$GrpPrdBudget[$Level] +=$AccountPeriodBudget;

		$CheckMonth += $myrow['monthactual'];
		$CheckBudgetMonth += $myrow['monthbudget'];
		$CheckPeriodActual += $AccountPeriodActual;
		$CheckPeriodBudget += $AccountPeriodBudget;
		
		$YPos -= (.5 * $line_height);
		$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin+530, $YPos+$line_height);  
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$YPos -= (5 * $line_height);
		$pdf->selectFont('./fonts/Helvetica.afm');
		//$ParentGroups[$Level]=$myrow['groupname'];
		
		if ($YPos < ($Bottom_Margin)){
			$line_height=10;
			include('includes/PDFGLTrialBalanceHeader.inc');
			$line_height=2;
			
		}
		
		$FontSize=7;
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$myrow['accountcode']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,190,$FontSize,$myrow['accountname']);
		
		if ($myrow['naturaleza']==1) {
			$LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,70,$FontSize,number_format($myrow['prdInicial'],2),'right');
			$totalInicial = $totalInicial + $myrow['prdInicial'];
			
		} else {
			$LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,70,$FontSize,number_format($myrow['prdInicial']*-1,2),'right');
			$totalInicialAcreedora = $totalInicialAcreedora + $myrow['prdInicial']*-1;
		}
			
		if ($myrow['naturaleza']==1) {
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($myrow['prdCargos'],2),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos,70,$FontSize,number_format($myrow['prdAbonos'],2),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+410,$YPos,70,$FontSize,number_format($myrow['prdFinal'],2),'right');
			$totalFinal = $totalFinal + $myrow['prdFinal'];
			
		} else {
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($myrow['prdCargos'],2),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos,70,$FontSize,number_format($myrow['prdAbonos'],2),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+460,$YPos,70,$FontSize,number_format($myrow['prdFinal']*-1,2),'right');
			$totalFinalAcreedora = $totalFinalAcreedora + $myrow['prdFinal']*-1;
		}
		
		$totalCargos = $totalCargos + $myrow['prdCargos'];
		$totalAbonos = $totalAbonos + $myrow['prdAbonos'];
		$j++;
		
		$FontSize=7;
		$YPos -= $line_height;
		
	}
			
	$pdf->selectFont('./fonts/Arial-Bold.afm');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos-20,100,$FontSize,utf8_decode('Verificación de Totales'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos-20,70,$FontSize,number_format($totalInicial,2),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+360,$YPos-35,70,$FontSize,number_format($totalAbonos,2),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos-20,70,$FontSize,number_format($totalCargos,2),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+40,$YPos-35,100,$FontSize,utf8_decode('Verificación de Totales'),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+410,$YPos-20,70,$FontSize,number_format($totalFinal,2),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos-35,70,$FontSize,number_format($totalInicialAcreedora,2),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+460,$YPos-35,70,$FontSize,number_format($totalFinalAcreedora,2),'right');
	
	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	
	if ($len<=20){
		$title = utf8_decode(_('Error al Imprimir Balanza de Comprobación'));
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('No existen registros que imprimir') );
		echo '<BR><A HREF="'. $rootpath.'/index.php?' . SID . '">'. utf8_decode(_('Regresar al Menú')). '</A>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=CustomerList.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();
	}

?>

