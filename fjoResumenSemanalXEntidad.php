 <?php

/*
 SP - 28/01/2013 
 
*/

$funcion=669;

$PageSecurity = 3;
include('includes/session.inc');

$title = _('Flujo Resumen X Semana');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

 /* OBTENGO FECHAS*/

if (isset($_POST['FromYear'])) {
    $FromYear=$_POST['FromYear'];
} else {
	$FromYear=date('Y');
}

if (isset($_POST['FromMes'])) {
	$FromMes=$_POST['FromMes'];
} else {
    $FromMes=date('m');
}

if (isset($_POST['ToYear'])) {
    $ToYear=$_POST['ToYear'];
} else {
	$ToYear=date('Y');
}

if (isset($_POST['ToMes'])) {
	$ToMes=$_POST['ToMes'];
} else {
    $ToMes=date('m');
}
        

	
$thislegalid = '';
if (isset($_POST['legalid'])) {	
	for ($i=0;$i<=count($_POST['legalid'])-1; $i++) {
	//echo 'empresa:' . $_POST['legalid'][$i] . '<br>';
		if ($i == 0)
			$thislegalid = $_POST['legalid'][$i];
		else
			$thislegalid .= "," . $_POST['legalid'][$i];
		}
} else {
	$thislegalid = '-1';
}

if (trim($thislegalid) == '') {
	$thislegalid = '-1';
}


	echo "<form name='FDatosA' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	/************************************/
	/* SELECCION DEL RAZON SOCIAL       */
	
	echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
	echo '<table border=0 width=1000>';
	echo '<tr><td colspan=2 style="vertical-align:top;text-align:center"><b>'._('X Razon Social:').'</b><td></tr>';
	echo '<tr><td colspan=2><table border=1 cellspacing=1 bordercolor="#aaaaaa" cellpadding=2 width=100%><tr>';
	///Imprime las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname
			  ORDER BY legalbusinessunit.legalid, t.tagref";		

	$result=DB_query($SQL,$db);
	
	
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	//echo '<input type="checkbox" name="legalid[]" checked value="all">' . _('Todas las razones sociales') . '<br><br>';
	$columncounter=0;
	while ($myrow=DB_fetch_array($result)){
		$columncounter++;
		if ($columncounter > 3) {
			$columncounter = 1;
			echo '</tr>';
			echo '<tr>';
		}
		
		echo '<td>';
		if ($thislegalid != "-1"){
			if (strpos($thislegalid,$myrow["legalid"]) !== false)
				echo '<input type="checkbox" onclick="seleccionaCheckBoxLegal('.$myrow['legalid'].')" name="legalid[]" checked value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'] . '<br>';
			else
				echo '<input type="checkbox" onclick="seleccionaCheckBoxLegal('.$myrow['legalid'].')" name="legalid[]" unchecked value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname']. '<br>';
		} else {
			echo '<input type="checkbox" onclick="seleccionaCheckBoxLegal('.$myrow['legalid'].')" name="legalid[]" unchecked value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname']. '<br>';
		}
		echo '</td>';
	}
	for ($i=$columncounter;$i<=2;$i++) {
		echo '<td>&nbsp;</td>';
	}
	//echo strpos($thislegalid,$myrow["legalid"]);
	echo '</tr></table></td></tr>';
	/*************************************/
	
	
	
	echo '<tr><td><br></td><td>&nbsp;';
	echo '</td></tr>';

	/* SELECCIONA EL BANCO */

	echo '<tr><td style="text-align:right"><b>' . _('X Cuenta de Cheques') . ':</b></td><td>
		<select name="BankAccount">';
	
	$SQL = 'SELECT bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode
		FROM bankaccounts, sec_unegsxuser, tagsxbankaccounts
				JOIN tags ON tagsxbankaccounts.tagref = tags.tagref
		WHERE bankaccounts.accountcode = tagsxbankaccounts.accountcode and
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
			tags.legalid in ('. $thislegalid .')
		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';
		
	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	
		echo "<option selected value=''>Todas las cuentas de cheques...</option>";
		while ($myrow=DB_fetch_array($AccountsResults)){
			/*list the bank account names */
			if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
				//$_POST['BankAccount']=$myrow['accountcode'];
			}
			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo '<option selected VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			} else {
				echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			}
		}
		echo '</select></td></tr>';
	
	
	//echo '<tr><td><br></td><td>';
	//echo '</td></tr>';
	
	/* SELECCIONA EL RANGO DE FECHAS */

	echo '<tr>';
	 echo '<td  style="text-align:right"><b>' . _('Periodo Inicial:') . '</b></td>';				    
	 echo '<td><select Name="FromMes">';
		   $sql = "SELECT LPAD(u_mes,2,'0') as u_mes, mes FROM cat_Months";
		   $Meses = DB_query($sql,$db);
		   while ($myrowMes=DB_fetch_array($Meses,$db)){
		       $Mesbase=$myrowMes['u_mes'];
		       if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" selected>' .$myrowMes['mes'];
		       }else{
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
		       }
		   }
		   
		   echo '</select>&nbsp;&nbsp;&nbsp;
	 		<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
	   echo '</td>';
		 
	 echo '</tr>';
	echo '<tr>';
	 echo '<td  style="text-align:right"><b>' . _('Periodo Final:') . '</b></td>';				    
	 echo '<td><select Name="ToMes">';
		   $sql = "SELECT LPAD(u_mes,2,'0') as u_mes, mes FROM cat_Months";
		   $Meses = DB_query($sql,$db);
		   while ($myrowMes=DB_fetch_array($Meses,$db)){
		       $Mesbase=$myrowMes['u_mes'];
		       if (rtrim(intval($ToMes))==rtrim(intval($Mesbase))){ 
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" selected>' .$myrowMes['mes'];
		       }else{
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
		       }
		   }
		   
		   echo '</select>&nbsp;&nbsp;&nbsp;
	 		<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
	   echo '</td>';
		 
	 echo '</tr>';
	
	echo '<tr><td colspan=2>&nbsp;</td></tr>';
		
	echo '</table>
		<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Mostrar') . '">&nbsp;&nbsp;';
	echo '</div><br>';
	echo "</form>";
	
	/*************************************************************************/
	/************** COMIENZA CODIGO DE REPORTE RESUMEN ***********************/
	/*************************************************************************/
	
	if (isset($_POST['ReportePantalla'])){
		
		$NMes[1]="ENE";
		$NMes[2]="FEB";
		$NMes[3]="MAR";
		$NMes[4]="ABR";
		$NMes[5]="MAY";
		$NMes[6]="JUN";
		$NMes[7]="JUL";
		$NMes[8]="AGO";
		$NMes[9]="SEP";
		$NMes[10]="OCT";
		$NMes[11]="NOV";
		$NMes[12]="DIC";

		$fechaini = $FromYear."-".$FromMes."-01";
		$fechafin = $ToYear."-".$ToMes."-01";
		$qry = "Select last_day('$fechafin') as lastdate";
		$r = DB_query($qry,$db);
		$row = DB_fetch_array($r);
		$fechafin = $row['lastdate'];

		$qry = "Select distinct year(fecha) as anio,
					month(fecha) as mes,
					weekofyear(fecha) as semana
				from Movimientos
				where fecha between '$fechaini' and '$fechafin'
				Order By anio,mes,fecha ";
		$rs = DB_query($qry,$db);
		$arrHeader=array();
		$anio="";
		$mes="";
		while($regs = DB_fetch_array($rs)){
			if ($anio!=$regs['anio']){
				$anio = $regs['anio'];
			}
			
			if ($mes!=$regs['mes']){
				$mes = $regs['mes'];
			}
			
			$arrHeader[$anio][$mes][] = $regs['semana'];
			
		}
		
		echo "<table width='100%' border=1 bordercolor=white cellpadding=3 cellspacing=3>";
		$colspanYear=0;
		$meses="";
		$semanas="";		
		foreach($arrHeader as $anio=>$arrmeses){
			foreach($arrmeses as $mes=>$arrsemanas){
				foreach($arrsemanas as $value)
					$semanas.="<th nowrap>Sem. ".$value."</th>";
					
				$meses.="<td style='text-align:center' colspan='".count($arrsemanas)."'><b>".$NMes[$mes]."</b></td>";	
				$colspanYear+=count($arrsemanas);
			}
			$anios.="<td style='text-align:center' colspan='$colspanYear'><b>$anio</b></td>";
			$colspanYear=0;
		}
		$header.="<tr><td>&nbsp;</td>$anios<td>&nbsp;</td></tr>
				  <tr><td>&nbsp;</td>$meses<td>&nbsp;</td></tr>
				  <tr><th>Entidad</th>$semanas<th>Total</th></tr>
					";
		echo $header;


		$totGeneral = 0;
		
		$where=""; 
		if ($_POST['BankAccount'])
			  $where.=" AND bankaccounts.accountcode = '". $_POST['BankAccount'] ."'";
	
		if (isset($_POST['SoloConfirmados']))
			$where.= " AND confirmado = 0 ";
		
	
				
		$sql = "SELECT usrEntidades.u_entidad as MId,
					usrEntidades.Nombre as Orden,
					IFNULL(year(Movimientos.fecha),0) as anio,
					IFNULL(month(Movimientos.fecha),0) as mes,
					IFNULL(weekofyear(Movimientos.fecha),0) as semana,
					sum(IFNULL(abono,0) - IFNULL(cargo,0)) as Total						
				 FROM usrEntidades 
				 	 LEFT JOIN Movimientos ON usrEntidades.u_entidad = Movimientos.u_entidad
					 LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
					 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
					 LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					 LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id				
				WHERE (Movimientos.u_empresa in (". $thislegalid .")) 
				AND activo = 1
				$where
				AND Movimientos.fecha between '$fechaini' and '$fechafin'
				AND erp = 0 
				";
		/* esto es para incluir CXC y CXP pero no se puede porque no hay union con la tabla de usrEntidades		
		$sql.=" UNION
				select 
					year(debtortrans.duedate) as anio,
					month(debtortrans.duedate) as mes,
					weekofyear(debtortrans.duedate) as semana,				
					sum(CASE WHEN (debtortrans.ovamount) >= 0 THEN (debtortrans.ovamount+debtortrans.ovgst-alloc) ELSE (debtortrans.ovamount+debtortrans.ovgst)*-1 END) as Total
				
					from debtortrans 
					JOIN tags ON debtortrans.tagref = tags.tagref 
					JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid 
					JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."' 
					
					where (ABS(debtortrans.ovamount + debtortrans.ovgst - debtortrans.alloc) > 0.9) and 
					debtortrans.type in ('10','21','70','110','400','410','450','440',200) 
					legalbusinessunit.legalid in (". $thislegalid .") 
					debtortrans.duedate between '$fechaini' and '$fechafin 23:59'
					and ((ovamount + ovgst) - alloc) > 1
					";		
		
		$sql .= " UNION
				  Select 
					  sum(CASE WHEN (supptrans.ovamount) >= 0 THEN (supptrans.ovamount+supptrans.ovgst-alloc) ELSE (supptrans.ovamount+supptrans.ovgst)*-1 END) as saldo
					  
				  from supptrans
					left JOIN suppallocs ON suppallocs.transid_allocfrom = supptrans.id
					JOIN tags ON supptrans.tagref = tags.tagref
					JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid 
					JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
					LEFT JOIN banktrans on banktrans.type=supptrans.type and banktrans.transno=supptrans.transno
					LEFT JOIN chartmaster ON banktrans.bankact=chartmaster.accountcode
				  where supptrans.type in ('22',20)
					and case when supptrans.type =20 then (ABS(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) > 0.9) else supptrans.ovamount <>0 end 
					and tags.legalid in (".$thislegalid.")
					and supptrans.trandate between '$fechaini' and '$fechafin 23:59'
					
					";		
		*/		
		$sql.="		GROUP BY usrEntidades.u_entidad,
					IFNULL(year(Movimientos.fecha),0),
					IFNULL(month(Movimientos.fecha),0),
					IFNULL(weekofyear(Movimientos.fecha),0)
				ORDER BY Orden,anio,mes,Movimientos.fecha";
		//echo $sql;
		$result = DB_query($sql,$db);
		$arrData = array();
		$anio="";
		$mes="";
		$entidad="";
		while ($rMovimientos = DB_fetch_array($result)) {
			if ($entidad!=$rMovimientos['Orden']){
				$entidad = $rMovimientos['Orden'];
				$anio = $rMovimientos['anio'];
				$mes = $rMovimientos['mes'];
			}

			if ($anio!=$rMovimientos['anio']){
				$anio = $rMovimientos['anio'];
				$mes = $rMovimientos['mes'];
			}
			
			if ($mes!=$rMovimientos['mes']){
				$mes = $rMovimientos['mes'];
			}
			
			$arrData[$entidad][$anio][$mes][] = array('semana'=>$rMovimientos['semana'],'total'=>$rMovimientos['Total']);


		}
		
		foreach($arrData as $entidad=>$arranios){
		
			$semanas="";
			$totfila = 0;		
			foreach($arrHeader as $anio=>$arrmeses){
				foreach($arrmeses as $mes=>$arrsemanas){
					foreach($arrsemanas as $value){
						$tot = 0;
						foreach($arrData[$entidad][$anio][$mes] as $arrd){
							if ($arrd['semana']==$value){
								$tot = $arrd['total'];
								break;
							}
						}
						$color="";
						if ($tot<0)
							$color="color:#AA0000";
						
						$semanas.="<td style='text-align:right;$color' nowrap>".number_format(abs($tot))."</td>";
						$totfila+=$tot;	
							
					}
				}
			}
			$color="";
			if ($totfila<0)
				$color="color:#AA0000";
				
			echo "<tr>
					<td nowrap>$entidad</td>$semanas<td style='text-align:right;$color' nowrap><b>".number_format(abs($totfila))."</b></td>
				  </tr>";
		
		}
				
		echo "</table>";		
	}
	
include('includes/footer.inc');
?>