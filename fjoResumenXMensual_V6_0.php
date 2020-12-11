
<script LANGUAGE="JavaScript">

function confirmation(linkto) {
	var answer = confirm("Seguro de Eliminar Movimiento ?")
	if (answer){
		//alert("Movimiento Eliminado !")
		window.location = linkto;
	}
	else{
		//alert("No Fue Eliminado !")
	}
}

function seleccionaCheckBoxLegal(indice) {
      //var answer = confirm("chk"+indice);

      var md = document.getElementById("legallist");

      md.value = "";
}

function seleccionaCheckBox(indice) {
      //var answer = confirm("chk"+indice);

      var md = document.getElementById("chk"+indice);

      md.checked = true;
}

function seleccionaCheckBoxCXC(indice) {
      //var answer = confirm("chk"+indice);

      var md = document.getElementById("chkCXC"+indice);

      md.checked = true;
}

function seleccionaCheckBoxCXP(indice) {
      //var answer = confirm("chk"+indice);

      var md = document.getElementById("chkCXP"+indice);

      md.checked = true;
}

</script>

<?php


$funcion=1589;

$PageSecurity = 3;
include('includes/session.inc');

$title = _('Reporte Subcategoria X Mensual');
if (! isset ( $_POST ['PrintEXCEL'] )) {
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';
}

include('includes/SQL_CommonFunctions.inc');
 /* OBTENGO FECHAS*/

function NombreMes($MesId) {
	switch ($MesId) {
		case 1:
			$nmes = 'Ene';
			break;
		case 2:
			$nmes = 'Feb';
			break;
		case 3:
			$nmes = 'Mar';
			break;
		case 4:
			$nmes = 'Abr';
			break;
		case 5:
			$nmes = 'May';
			break;
		case 6:
			$nmes = 'Jun';
			break;
		case 7:
			$nmes = 'Jul';
			break;
		case 8:
			$nmes = 'Ago';
			break;
		case 9:
			$nmes = 'Sep';
			break;
		case 10:
			$nmes = 'Oct';
			break;
		case 11:
			$nmes = 'Nov';
			break;
		case 12:
			$nmes = 'Dic';
			break;
	}
	return $nmes;
}

if (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
} elseif (isset($_GET['FromYear'])) {
	$FromYear=$_GET['FromYear'];
} else {
	$FromYear=date('Y');
}

if (isset($_GET['FromMes'])) {
	$FromMes=$_GET['FromMes'];
}elseif (isset($_POST['FromMes1'])) {
	$FromMes=$_POST['FromMes1'];
}elseif (isset($_POST['FromMes'])) {
	$FromMes=$_POST['FromMes'];
} else {
        $FromMes=date('m');
}

if (isset($_POST['FromDia'])) {
	$FromDia=$_POST['FromDia'];
} else {
        $FromDia=date('d');
}

$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
$fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);

if (isset($_GET['Oper'])){
	$_POST['Oper'] = $_GET['Oper'];
}

if (isset($_GET['legalid'])){
	$_POST['legalid'] = $_GET['legalid'];
}
if (isset($_GET['FromMes'])){
	$_POST['FromMes'] = $_GET['FromMes'];
}
if (isset($_GET['FromYear'])){
	$_POST['FromYear'] = $_GET['FromYear'];
}

if (isset($_GET['BankAccount'])){
	$_POST['BankAccount'] = $_GET['BankAccount'];
}
if (isset($_GET['u_movimiento'])){
	$_POST['u_movimiento'] = $_GET['u_movimiento'];
}
///////////////////////////////

if ((isset($_POST ["checkBanco"])) or !isset($_POST ['BankAccount'])) {
	$checkbanco = 'checked';
}
if ((isset($_POST ["checkCxC"])) or !isset( $_POST ['BankAccount'])) {
	$checkCxC = 'checked';
}
if ((isset($_POST ["checkCxP"])) or !isset( $_POST ['BankAccount'])) {
	$checkCxP = 'checked';
}
if ((isset($_POST ["checkP"])) or !isset($_POST ['BankAccount'])) {
	$checkP = 'checked';
}

if (!isset($_POST['legalid'])){
	$_POST['legalid'] = 0;
}
if (!isset($_POST['FromMes'])){
	$_POST['FromMes'] = $FromMes;
}

if ($_POST['FromYear']==""){
	$_POST['FromYear'] = $FromYear;
}

if ($_POST['BankAccount']==""){
	$_POST['BankAccount'] = -1;
}

if ($_GET['area'])
	$_POST['xArea'] = $_GET['area'];

if ($_POST['xArea']=="")
	$_POST['xArea'] = "*";

if (isset($_POST['thislegalid']) AND strlen($_POST['thislegalid']) > 0) {
	$thislegalid = $_POST['thislegalid'];
} elseif (isset($_GET['thislegalid']) ) {
	$thislegalid = ' '.$_GET['thislegalid'];
} else {
	$thislegalid = '';
	if (isset($_POST['legalid'])) {
		for ($i=0;$i<=count($_POST['legalid'])-1; $i++) {
			//echo 'empresa:' . $_POST['legalid'][$i] . '<br>';
			if ($i == 0)
				$thislegalid = $thislegalid . " " . $_POST['legalid'][$i] . "";
			else
				$thislegalid = $thislegalid . "," . $_POST['legalid'][$i]. "";
		}
	} else {
		$thislegalid = '-1';
	}
}

if (trim($thislegalid) == '') {
	$thislegalid = '-1';
}


/****SALDO INICIAL****/

function Traesaldoinicial($anio, $mes, $legalid, $funcion, $BankAccount, $checkbanco, $checkCxC, $checkCxP, $checkP, $FechaProy, $db){


	$SQLBanco = "SELECT
					sum(banktrans.amount) as saldo
			FROM banktrans
				INNER JOIN tagsxbankaccounts ON tagsxbankaccounts.accountcode = banktrans.bankact
					AND tagsxbankaccounts.tagref = banktrans.tagref
					AND tagsxbankaccounts.tagref in
						(select tagref from tags where (legalid in (" . trim($legalid) . ") or '" . $legalid . "' = '-1' ))
				INNER JOIN chartmaster ON banktrans.bankact = chartmaster.accountcode
				INNER JOIN tags ON tags.tagref=banktrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=banktrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
						AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE year(banktrans.transdate) < '" . $anio . "'
				AND abs(banktrans.amount)!=0";
		if(isset($legalid) && $legalid != '-1'){
			$SQLBanco = $SQLBanco ." AND tags.legalid in (" . trim($legalid) . ")";
		}
		/*if(isset($BankAccount) && $BankAccount != '-1'){
			$SQLBanco = $SQLBanco ." AND banktrans.bankact='".$BankAccount."'";
		}*/
		if ( $BankAccount ) {
	        // seleccion multiple CGA 09-2016
	        $bankaccount2 = "(";
	        $comma = "";
	        foreach($BankAccount as $row){
	        	if ($row != '-1') {
	        		$bankaccount2 .= $comma . "'". $row . "'" ;
	            	$comma = ",";
	        	}
	        }
	        $bankaccount2 .= ")";
	        $SQLBanco = $SQLBanco . " AND banktrans.bankact IN " . $bankaccount2;
	        // echo "entra";
	    }

	//echo '<pre>sql:'.$SQLBanco;.
	// Consulta movimientos de cxp

	$SQLCxP="	SELECT
					sum((((supptrans.ovamount+supptrans.ovgst)-alloc)*-1)) as saldo
			FROM supptrans
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=supptrans.type and systypesbyreport.functionid=".$funcion."
				INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
				INNER JOIN tags ON tags.tagref=supptrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=supptrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE year(supptrans.promisedate) < '" . $anio . "'
					AND abs(((supptrans.ovamount+supptrans.ovgst)-alloc))!=0
		 ";
		if(isset($legalid) && $legalid!='-1'){
			$SQLCxP = $SQLCxP ." AND tags.legalid in (" . trim($legalid) . ")";
		}
	// consulta movimientos de cxc
	$SQLCxC="	SELECT
			sum(case when debtortrans.ovamount+debtortrans.ovgst>0 then ((debtortrans.ovamount+debtortrans.ovgst)-alloc)
			else  ((debtortrans.ovamount+debtortrans.ovgst)-alloc) end ) as saldo
			FROM debtortrans
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=debtortrans.type and systypesbyreport.functionid=".$funcion."
				INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				INNER JOIN tags ON tags.tagref=debtortrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=debtortrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE year(debtortrans.duedate) < '" . $anio. "'
					AND abs(((debtortrans.ovamount+debtortrans.ovgst)-alloc))!=0
		 ";
		if(isset($legalid) && $legalid!='-1'){
			$SQLCxC = $SQLCxC ." AND tags.legalid in (" . trim($legalid) . ")";
		}

	// consulta movimientos de proyeccion
	$SQLProy="	SELECT
					sum(amount) as saldo
			FROM fjo_Movimientos
				INNER JOIN tags ON tags.tagref=fjo_Movimientos.tagref
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE year(fjo_Movimientos.fechapromesa) < '" . $fechaini . "'
						AND fjo_Movimientos.fechapromesa >= '" . $FechaProy . "'
					AND abs(amount)!=0";
		if(isset($_POST['legalid']) && $_POST['legalid']!='-1'){
			$SQLProy = $SQLProy ." AND tags.legalid in (" . trim($legalid) . ")";
		}

	$unionCXC = ' UNION ';
	if ($checkbanco != 'checked') {
		$SQLBanco = '';
		$unionCXC = '';
	}
	$unionCXP = ' UNION ';
	if ($checkCxC != 'checked') {
		$SQLCxC = '';
		$unionCXP = '';
	}
	$unionP = ' UNION ';
	if ($checkCxP != 'checked') {
		$SQLCxP = '';
		$unionP = '';
	}
	if ($checkP != 'checked') {
		$SQLProy = '';
	}
	// echo '<pre>sql:<br>'.$SQLBanco;
	/**/
	$SQL = $SQLBanco . $unionCXC . $SQLCxC . $unionCXP . $SQLCxP . $unionP . $SQLProy;

	//$SQL = $SQLBanco . ' UNION ' . $SQLCxC . ' UNION ' . $SQLCxP  . ' UNION ' . $SQLProy;

	$validaunion=left(strrev($SQL), 6);
	if(trim($validaunion)=='NOINU'){
		$sqllen=strlen($SQL)-6;
		$SQL=left($SQL, $sqllen);
	}

	$SQLSaldo="select sum(saldo) as saldofin from (";
	$SQLSaldo=$SQLSaldo . $SQL;
	$SQLSaldo=$SQLSaldo . ") as saldofin";

	//echo '<pre>sql:'.$SQLSaldo.'<br><br>';
	$Result = DB_query($SQLSaldo, $db);
	$Row = DB_fetch_row($Result);
	return $Row[0];

}

/********************/


if (! isset ( $_POST ['PrintEXCEL'] )) {

	echo "<form name='FDatosA' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
	echo "<table cellspacing='0' cellpadding='0' border='0' width='1100' style='margin-left: 5px;'>";
		echo "<tr><td>";
		/************************************/
		/* SELECCION DEL RAZON SOCIAL       */
		echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
		echo '<table border="0" width="1100">';
			echo '<tr><td colspan=2 style="vertical-align:top;text-align:center"><b>'._('X Razon Social:').'</b></td></tr>';
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
			$arrthislegalid = explode(',',$thislegalid);
			while ($myrow=DB_fetch_array($result)){
				$columncounter++;
				if ($columncounter > 4) {
					$columncounter = 1;
					echo '</tr>';
					echo '<tr>';
				}

				echo '<td style="font-size:x-small;">';
				if ($thislegalid != "-1"){
					if (in_array($myrow["legalid"],$arrthislegalid) !==false)
						echo '<input type="checkbox" onclick="seleccionaCheckBoxLegal('.$myrow['legalid'].')" name="legalid[]" checked value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'] . '<br>';
					else
						echo '<input type="checkbox" onclick="seleccionaCheckBoxLegal('.$myrow['legalid'].')" name="legalid[]" unchecked value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname']. '<br>';
				} else {
					echo '<input type="checkbox" onclick="seleccionaCheckBoxLegal('.$myrow['legalid'].')" name="legalid[]" unchecked value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname']. '<br>';
				}
				echo '</td>';
			}
			for ($i=$columncounter;$i<=3;$i++) {
				echo '<td>&nbsp;</td>';
			}
			echo '</tr>';
			echo '<tr>';
				echo '<td colspan="4" style="text-align:center">';
					echo '<input type="submit" name="actualizactas" value="Actualiza Cuentas">';
				echo '</td>';
			echo '</tr>';
		echo '</table>';
	echo '</td></tr>';
		/*************************************/
	echo '<tr><td><br></td><td>&nbsp;</td></tr>';

	/* SELECCIONA EL BANCO */

	echo '<tr><td style="text-align:right"><b>' . _('X Cuenta de Cheques') . ':</b></td><td>
		<select name="BankAccount[]" id="rSociales"  multiple="multiple">';
		$SQL = 'SELECT bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
		FROM bankaccounts, sec_unegsxuser, tagsxbankaccounts
				JOIN tags ON tagsxbankaccounts.tagref = tags.tagref
		WHERE bankaccounts.accountcode = tagsxbankaccounts.accountcode and
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
			tags.legalid in ('. $thislegalid .')
			and bankaccounts.accountcode <> "101084"
		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';

	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	$selected = "";
	foreach ($_POST['BankAccount'] as $dato) {
		if ('-1' == $dato) {
			$selected = "selected";
		}
	}
	echo "<option value='-1' ".$selected.">Todas las cuentas de cheques...</option>";
	while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names
		if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
			//$_POST['BankAccount']=$myrow['accountcode'];
		}
		if ($_POST['BankAccount']==$myrow['accountcode']){
			echo '<option selected VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
		} else {
			echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
		}*/
		$selected = "";
		foreach ($_POST['BankAccount'] as $dato) {
			if ($myrow['accountcode'] == $dato) {
				$selected = "selected";
			}
		}
		echo '<option value="' . $myrow['accountcode'] . '" '.$selected.'>' . $myrow['bankaccountname']. ' - ' . $myrow['currcode'] .'</option>';
	}
	echo '</select></td></tr>';


	/* SELECCIONA EL RANGO DE FECHAS */

	echo '<tr>';
		echo '<td  style="text-align:right"><b>' . _('X Anio de Consulta:') . '</b></td>';
	 	echo '<td>&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
	 	echo '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<td colspan="2" style="text-align:center;">';
			echo '<table border="0" cellpadding="0" cellspacing="0"  style="margin:auto;">';
				echo '<tr>';
					echo '<td style="background-color:lightgreen;text-align:right;">
							<input type="checkbox" ' . $checkbanco . ' name="checkBanco">
						  <td>';
					echo '<td>' . _ ( '(B) Banco' ) . '<td>';

					echo '<td style="background-color:#F3F781;text-align:right;">
							<input type="checkbox" ' . $checkCxC . ' name="checkCxC">
						 <td>';
					echo '<td>' . _ ( '(CxC) Cuenta x Cobrar' ) . '<td>';

					echo '<td style="background-color:#Fa9090;text-align:right;">
							<input type="checkbox" ' . $checkCxP . ' name="checkCxP">
						  <td>';
					echo '<td>' . _ ( '(CxP) Cuenta x Pagar' ) . '<td>';

					echo '<td style="background-color:#FAAC58;text-align:right;">
							<input type="checkbox" ' . $checkP . ' name="checkP">
						  <td>';
					echo '<td>' . _ ( '(P) Proyeccion' ) . '<td>';
				echo '</tr>';
			echo '</table>';
		echo '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<td colspan="2" style="text-align:center;">';
			echo '<table border="0" cellpadding="0" cellspacing="0" width="50%" style="margin:auto;">';
				echo '<tr>';
					echo '<td style="text-align:center;"><input type="submit" name="ReportePantalla" value="' . _('Consulta Flujo') . '"></td>';
					echo '<td style="text-align:center;"><input type="submit" name="PrintEXCEL" value="' . _ ( 'Exportar a Excel' ) . '"></td>';
				echo '</tr>';
			echo '</table>';
		echo '</td>';
	echo '</tr>';

	echo '</table>';

	/*******CAMPOS PARA MOSTRAR PROYECCIONES APARTIR DE UNA FECHA*****/
	if (isset ($_POST ['FromYearProy'] )) {
		$FromYearProy = $_POST ['FromYearProy'];
	} else {
		$FromYearProy = date ( 'Y' );
	}
	if (isset ( $_POST ['FromMesProy'] )) {
		$FromMesProy = $_POST ['FromMesProy'];
	} else {
		$FromMesProy = date ( 'm' );
	}
	if (strlen($FromMesProy) == 1){
		$FromMesProy = "0" . $FromMesProy;
	}

	if (isset ( $_POST ['FromDiaProy'] )) {
		$FromDiaProy = $_POST ['FromDiaProy'];
	} else {
		$FromDiaProy = date ( 'd' );
	}
	if (strlen($FromDiaProy) == 1){
		$FromDiaProy = "0" . $FromDiaProy;
	}


	$FechaProy = $FromYearProy . "-" . $FromMesProy . "-" . $FromDiaProy . " 00:00:00.000";

	echo '<input type="hidden" name="FromYearProy" value="' . $FromYearProy . '">';
	echo '<input type="hidden" name="FromMesProy" value="' . $FromMesProy . '">';
	echo '<input type="hidden" name="FromDiaProy" value="' . $FromDiaProy . '">';

	/*****************************************************************/

	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo "</form>";
}

	/*************************************************************************/
	/************** COMIENZA CODIGO DE REPORTE RESUMEN ***********************/
	/*************************************************************************/

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

if (isset($_POST['ReportePantalla']) or isset($_POST['PrintEXCEL'])){
	if (! isset ( $_POST ['PrintEXCEL'] )) {
		echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
		echo '<input Name="FromMes" type=hidden value="'.$FromMes.'">';
		echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
	}

	$SQLBanco = "SELECT  DWH_Tiempo.mes,
	 				IFNULL(banktrans_ext.subcat_id, 0) as subcategoria,
					SUM(banktrans.amount*-1) as saldo
			FROM banktrans
	 			INNER JOIN DWH_Tiempo ON year(banktrans.transdate) = DWH_Tiempo.Anio
	 					and month(banktrans.transdate) = DWH_Tiempo.Mes
	 					and day(banktrans.transdate) = DWH_Tiempo.Dia
				LEFT JOIN banktrans_ext ON banktrans.banktransid = banktrans_ext.banktransid
	 			INNER JOIN tagsxbankaccounts ON tagsxbankaccounts.accountcode = banktrans.bankact
					AND tagsxbankaccounts.tagref = banktrans.tagref
					AND tagsxbankaccounts.tagref in (select tagref from tags where (legalid in (" . trim($thislegalid) . ") or '" . $thislegalid . "' = '-1' ))
				LEFT JOIN supptrans ON banktrans.type = supptrans.type and banktrans.transno = supptrans.transno
				INNER JOIN tags ON tags.tagref=banktrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=banktrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			  	LEFT JOIN chartmaster on chartmaster.accountcode=banktrans.bankact
			WHERE year(banktrans.transdate) = '" . $_POST['FromYear'] . "'
				AND abs(banktrans.amount)!=0
				/*AND banktrans.ref NOT LIKE '%Cance%'*/";
			if (isset ( $_POST ['legalid'] ) && $thislegalid != '-1') {
				$SQLBanco = $SQLBanco . " AND tags.legalid in (" . trim($thislegalid) . ")";
			}
			/*if (isset ( $_POST ['BankAccount'] ) && $_POST ['BankAccount'] != '-1') {
				$SQLBanco = $SQLBanco . " AND banktrans.bankact='" . $_POST ['BankAccount'] . "'";
			}*/
			if ( $_POST['BankAccount'] ) {
		        // seleccion multiple CGA 09-2016
		        $bankaccount = "(";
		        $comma = "";
		        foreach($_POST['BankAccount'] as $row){
		            $bankaccount .= $comma . "'". $row . "'" ;
		            $comma = ",";
		        }
		        $bankaccount .= ")";
		        $SQLBanco .= " AND banktrans.bankact IN " . $bankaccount;
		        // echo "entra";
		    }
			$SQLBanco = $SQLBanco . " GROUP BY DWH_Tiempo.mes, IFNULL(banktrans_ext.subcat_id, 0)";
			if ($_SESSION['UserID'] == 'ocruz'){
				//echo "<br>sql: " . $SQLBanco;
			}
	// Consulta movimientos de cxp

	$SQLCxP = "	SELECT  DWH_Tiempo.mes,
					IFNULL(supptrans_ext.subcat_id, 0) as subcategoria,
					SUM(((supptrans.ovamount+supptrans.ovgst)-alloc)/rate) as saldo
			FROM supptrans
				INNER JOIN DWH_Tiempo ON year(supptrans.promisedate) = DWH_Tiempo.Anio
	 					and month(supptrans.promisedate) = DWH_Tiempo.Mes
	 					and day(supptrans.promisedate) = DWH_Tiempo.Dia
				LEFT JOIN supptrans_ext ON supptrans.id = supptrans_ext.id
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=supptrans.type and systypesbyreport.functionid=" . $funcion . "
				INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
				INNER JOIN tags ON tags.tagref=supptrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=supptrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			WHERE year(supptrans.promisedate) = '" . $_POST['FromYear'] . "'
				AND abs(((supptrans.ovamount+supptrans.ovgst)-alloc))>0.99";
			if (isset ( $_POST ['legalid'] ) && $thislegalid != '-1') {
				$SQLCxP = $SQLCxP . " AND tags.legalid in (" . trim($thislegalid) . ")";
			}
			$SQLCxP = $SQLCxP . " GROUP BY DWH_Tiempo.mes, IFNULL(supptrans_ext.subcat_id, 0)";
	// consulta movimientos de cxc

	$SQLCxC = "	SELECT  DWH_Tiempo.mes,
	 				IFNULL(debtortrans_ext.subcat_id, 0) as subcategoria,
	 				SUM((case when debtortrans.ovamount+debtortrans.ovgst>0
						then ((debtortrans.ovamount+debtortrans.ovgst)-alloc) *-1
						else ((debtortrans.ovamount+debtortrans.ovgst)-alloc)*-1 end) / debtortrans.rate) as saldo
			FROM debtortrans
	 			INNER JOIN DWH_Tiempo ON year(debtortrans.duedate) = DWH_Tiempo.Anio
	 					and month(debtortrans.duedate) = DWH_Tiempo.Mes
	 					and day(debtortrans.duedate) = DWH_Tiempo.Dia
				LEFT JOIN debtortrans_ext ON debtortrans.id = debtortrans_ext.id
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=debtortrans.type and systypesbyreport.functionid=" . $funcion . "
				INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				INNER JOIN tags ON tags.tagref=debtortrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=debtortrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			WHERE year(debtortrans.duedate) = '" . $_POST['FromYear'] . "'
					AND abs(((debtortrans.ovamount+debtortrans.ovgst)-alloc))>.99";
			if (isset ( $_POST ['legalid'] ) && $thislegalid != '-1') {
				$SQLCxC = $SQLCxC . " AND tags.legalid in (" . trim($thislegalid) . ")";
			}

			$SQLCxC = $SQLCxC . " GROUP BY DWH_Tiempo.mes, IFNULL(debtortrans_ext.subcat_id, 0)";
	// consulta movimientos de proyeccion

	$SQLProy = "SELECT  DWH_Tiempo.mes,
						subcategoria,
						SUM(amount) as saldo
			FROM fjo_Movimientos
				INNER JOIN DWH_Tiempo ON year(fjo_Movimientos.fechapromesa) = DWH_Tiempo.Anio
	 					and month(fjo_Movimientos.fechapromesa) = DWH_Tiempo.Mes
	 					and day(fjo_Movimientos.fechapromesa) = DWH_Tiempo.Dia
				INNER JOIN tags ON tags.tagref=fjo_Movimientos.tagref
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			WHERE year(fjo_Movimientos.fechapromesa) = '" . $_POST['FromYear'] . "'
					AND fjo_Movimientos.fechapromesa >= '" . $FechaProy . "'
					AND abs(amount)!=0";
			if (isset ( $_POST ['legalid'] ) && $thislegalid != '-1') {
				$SQLProy = $SQLProy . " AND tags.legalid in (" . trim($thislegalid) . ")";
			}
			$SQLProy = $SQLProy . " GROUP BY DWH_Tiempo.mes, IFNULL(subcategoria, 0)";


	$unionCXC = ' UNION ';
	if ($checkbanco != 'checked') {
		$SQLBanco = '';
		$unionCXC = '';
	}
	$unionCXP = ' UNION ';
	if ($checkCxC != 'checked') {
		$SQLCxC = '';
		$unionCXP = '';
	}
	$unionP = ' UNION ';
	if ($checkCxP != 'checked') {
		$SQLCxP = '';
		$unionP = '';
	}
	if ($checkP != 'checked') {
		$SQLProy = '';
	}

	$sqlunion = $SQLBanco . $unionCXC . $SQLCxC . $unionCXP . $SQLCxP . $unionP . $SQLProy;
	$validaunion = left ( strrev ( $sqlunion ), 6 );
	if (trim ( $validaunion ) == 'NOINU') {
		$sqllen = strlen ( $sqlunion ) - 6;
		$sqlunion = left ( $sqlunion, $sqllen );
	}

	$sqlgral = "SELECT sc.subcat_id,
					sc.subcat_name,
					IFNULL(ct.color,'#FAAC58') as colorcat,
					IFNULL(sc.color,'#FFFFFF') as colorsubcat,
					ct.cat_name,
					kc.kindcategory as tipo,
			  		IFNULL(mes,0) as mes,
					IFNULL(SUM(saldo),0) as saldo
			  	FROM fjoSubCategory sc
					LEFT JOIN fjoCategory ct ON sc.cat_id = ct.cat_id
			  		LEFT JOIN fjokindcategory kc ON ct.kindcategoryid = kc.kindcategoryid
					LEFT JOIN (" . $sqlunion . "
					) as x ON x.subcategoria = sc.subcat_id
				GROUP BY sc.subcat_id, ct.cat_name, mes
				ORDER BY ct.order, sc.order, x.mes";

	//echo "<br>" . $sqlgral;
	//*****
	$sqlmeses = "SELECT mes,
						min(fecha) as fechainicio,
						max(fecha) as fechafin,
						CASE WHEN Now() >= min(fecha) and Now() <= max(fecha) THEN 1 ELSE 0 END as mesact
					FROM DWH_Tiempo t
					WHERE Anio = '" . $_POST['FromYear'] . "'
			  		GROUP BY mes
			  		ORDER BY mes";


	$ErrMsg = _('');
	$DbgMsg = _('');
	$ResultMeses = DB_query($sqlmeses,$db,$ErrMsg,$DbgMsg);

	$arrmeses = array();
	$arrfechainiciomes = array();
	$arrfechafinmes = array();
	$indice = -1;
	$mesinicial = 0;
	$mesfinal = 0;
	$mesactual = 0;
	while ($myrowmes=DB_fetch_array($ResultMeses)){
		if($mesinicial == 0){
			$mesinicial = $myrowmes['mes'];
		}
		$mesfinal = $myrowmes['mes'];
		if($myrowmes['mesact']=='1'){
			$mesactual = $myrowmes['mes'];
		}
		$indice++;
		$arrmeses[$indice] = $myrowmes['mes'];
		$arrfechainiciomes[$indice] = $myrowmes['fechainicio'];
		$arrfechafinmes[$indice] = $myrowmes['fechafin'];
	}


	if (isset ( $_POST ['PrintEXCEL'] )) {

		header ( "Content-type: application/ms-excel" );
		// replace excelfile.xls with whatever you want the filename to default to
		header ( "Content-Disposition: attachment; filename=ResumenflujoMensual.xls" );
		header ( "Pragma: no-cache" );
		header ( "Expires: 0" );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="' . $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	}



	$saldoinicial = Traesaldoinicial($_POST['FromYear'], 0, $thislegalid, $funcion, $_POST ['BankAccount'], $checkbanco, $checkCxC, $checkCxP, $checkP, $FechaProy, $db);

	$tabla0 = '<table border="1" cellpadding="0" cellspacing="0" width="100%">';
		$tabla0 = $tabla0 . '<tr style="background-color:#F3F781;">';
			$tabla0 = $tabla0 . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('') . '</td>';
			$arrtotalesxmes = array();
			$arrtotalesxmesxcat = array();
			$arrsaldoinicial = array();
			$arrtotalesxmesxtipo = array();

			$contmes = count($arrmeses);
			for($i=0;$i<$contmes;$i++){
				$strmes = $arrmeses[$i];

				$tooltip = substr($arrfechainiciomes[$i],0,10) . " " . _('Al') . " " . substr($arrfechafinmes[$i],0,10);
				if($arrmeses[$i] == $mesactual){
					$tabla0 = $tabla0 . '<td nowrap style="font-size:x-small; font-weight:bold; text-align:center; background-color:white;">';
				}else{
					$tabla0 = $tabla0 . '<td nowrap style="font-size:x-small; font-weight:bold; text-align:center;">';
				}

				$tabla0 = $tabla0 . '<span title="' . $tooltip . '">';
				$tabla0 = $tabla0 . '*** ' . $NMes[$strmes] . ' ***';
				$tabla0 = $tabla0 . '</span></td>';




				$arrtotalesxmes[$i] = 0;
				$arrtotalesxmesxcat[$i] = 0;
				$arrtotalesxmesxtipo[$i] = 0;
				$arrsaldoinicial[$i] = 0;
			}
			$tabla0 = $tabla0 . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:center;">' . _('') . '</td>';
		$tabla0 = $tabla0 . '</tr>';
		$ErrMsg = _('');
		$DbgMsg = _('');
		$Result = DB_query($sqlgral,$db,$ErrMsg,$DbgMsg);
		$catant = '';
		$subcatante = '';
		$tipoant = '';
		$totalsubcategoria = 0;

		$totales = 0;

		/**/
		while ($myrow=DB_fetch_array($Result)){
			if($myrow['mes'] != '0'){
				$flagfincate = false;
				$flagfintipo = false;

				if($tipoant != $myrow['tipo']){/******CAMBIO DE TIPO******/
					if ($subcatante != ""){
						for($i=$indice0; $i<$contmes; $i++){
							if($arrmeses[$i] == $mesactual){
								$bgcolormesactual = 'background-color:#F3F781;';
							}else{
								$bgcolormesactual = '';
							}
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolormesactual . '">0.00</td>';
						}
						if($totalsubcategoria < 0){
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
						}else{
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
						}

						$totales = $totales + $totalsubcategoria;
						$tabla1 = $tabla1 . '</tr>';
						$flagfincate = true;
						$totalsubcategoria = 0;
					}
					if($catant != ""){
						$tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
						$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;">' . _('ST ') . $catant . '</td>';
						$totalxcategoria = 0;
						for($i=0; $i<$contmes; $i++){
							$totalxcategoria = $totalxcategoria + $arrtotalesxmesxcat[$i];
							if($arrtotalesxmesxcat[$i] < 0){
								$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesxmesxcat[$i]),2) . '</td>';
							}else{
								$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesxmesxcat[$i]),2) . '</td>';
							}

							$arrtotalesxmesxcat[$i] = 0;
						}
						if($totalxcategoria < 0){
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($totalxcategoria),2) . '</td>';
						}else{
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($totalxcategoria),2) . '</td>';
						}

						$tabla1 = $tabla1 . '</tr>';
						$flagfintipo = true;
					}
					if($tipoant != ""){
						$tabla1 = $tabla1 . '<tr style="background-color:#0431B4; color:white;">';
						$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left; color:white;">' . _('TOTAL ') . $tipoant . '</td>';
						$totalxtipo = 0;
						for($i=0; $i<$contmes; $i++){
							$totalxtipo = $totalxtipo + $arrtotalesxmesxtipo[$i];
							if($arrtotalesxmesxtipo[$i] < 0){
								$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($arrtotalesxmesxtipo[$i]),2) . ')</td>';
							}else{
								$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($arrtotalesxmesxtipo[$i]),2) . '</td>';
							}

							$arrtotalesxmesxtipo[$i] = 0;
						}
						if($totalxtipo < 0){
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($totalxtipo),2) . ')</td>';
						}else{
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">' . number_format(($totalxtipo),2) . '</td>';
						}
						$tabla1 = $tabla1 . '</tr>';
					}

					$tabla1 = $tabla1 . '<tr style="background-color:#0431B4;">';
					$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left; color:white" colspan="' . ($contmes+1) . '">' . $myrow['tipo'] . '</td>';
					$tabla1 = $tabla1 . '<td style="font-size:x-small; background-color:#0431B4; font-weight:bold; text-align:left;"></td>';

					$tabla1 = $tabla1 . '</tr>';
					$tipoant = $myrow['tipo'];


				}/*******FIN CAMBIO DE TIPO*********/


				if($catant != $myrow['cat_name']){
					if ($subcatante != ""  and $flagfincate == false){
						for($i=$indice0; $i<$contmes; $i++){
							if($arrmeses[$i] == $mesactual){
								$bgcolormesactual = 'background-color:#F3F781;';
							}else{
								$bgcolormesactual = '';
							}
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolormesactual . '">0.00</td>';
						}
						if($totalsubcategoria < 0){
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
						}else{
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
						}

						$totales = $totales + $totalsubcategoria;
						$tabla1 = $tabla1 . '</tr>';
						$flagfincate = true;
						$totalsubcategoria = 0;
					}
					if($catant != ""  and $flagfintipo == false){
						$tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
						$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;">' . _('ST ') . $catant . '</td>';
						$totalxcategoria = 0;
						for($i=0; $i<$contmes; $i++){
							$totalxcategoria = $totalxcategoria + $arrtotalesxmesxcat[$i];
							if($arrtotalesxmesxcat[$i] < 0){
								$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesxmesxcat[$i]),2) . '</td>';
							}else{
								$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesxmesxcat[$i]),2) . '</td>';
							}

							$arrtotalesxmesxcat[$i] = 0;
						}
						if($totalxcategoria < 0){
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($totalxcategoria),2) . '</td>';
						}else{
							$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($totalxcategoria),2) . '</td>';
						}

						$tabla1 = $tabla1 . '</tr>';
					}


					$tabla1 = $tabla1 . '<tr style="background-color:' . $myrow['colorcat'] . ';">';
						$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;" colspan="' . ($contmes+1) . '">' . $myrow['cat_name'] . '</td>';
						$tabla1 = $tabla1 . '<td style="font-size:x-small; background-color:#FFFF00; font-weight:bold; text-align:left;"></td>';

					$tabla1 = $tabla1 . '</tr>';
					$catant = $myrow['cat_name'];
				}
				if($subcatante != $myrow['subcat_name']){
					if ($subcatante != "" and $flagfincate == false){
							for($i=$indice0; $i<$contmes; $i++){
								if($arrmeses[$i] == $mesactual){
									$bgcolormesactual = 'background-color:#F3F781;';
								}else{
									$bgcolormesactual = '';
								}
								$tabla1 = $tabla1 . '<td style="font-size:xx-small;  text-align:right; ' . $bgcolormesactual . '">0.00</td>';
							}
							if($totalsubcategoria < 0){
								$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
							}else{
								$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
							}

							$totales = $totales + $totalsubcategoria;
						$tabla1 = $tabla1 . '</tr>';
						$totalsubcategoria = 0;
					}
					$tabla1 = $tabla1 . '<tr style="background-color:' . $myrow['colorsubcat'] . ';">';
					$tabla1 = $tabla1 . '<td nowrap style="font-size:x-small; font-weight:bold; text-align:left;">' . $myrow['subcat_name'] . '</td>';
					$subcatante = $myrow['subcat_name'];
					$indice0 = 0;
				}


				for($i=$indice0; $i<$contmes; $i++){
					if($arrmeses[$i] == $myrow['mes']){
						$totalsubcategoria = $totalsubcategoria + ($myrow['saldo']*-1);
						$arrtotalesxmes[$i] = $arrtotalesxmes[$i] + ($myrow['saldo']*-1);
						$arrtotalesxmesxcat[$i] = $arrtotalesxmesxcat[$i] + ($myrow['saldo']*-1);
						$arrtotalesxmesxtipo[$i] = $arrtotalesxmesxtipo[$i] + ($myrow['saldo']*-1);

						if($arrmeses[$i] == $mesactual){
							$bgcolormesactual = 'background-color:#F3F781;';
						}else{
							$bgcolormesactual = '';
						}

						if(($myrow['saldo']*-1) < 0){
							$tabla1 = $tabla1 .'<td style="font-size:xx-small; color:red; text-align:right; ' . $bgcolormesactual . '">' . number_format(abs($myrow['saldo']),2) . '</td>';
						}else{
							$tabla1 = $tabla1 .'<td style="font-size:xx-small; text-align:right; ' . $bgcolormesactual . '">' . number_format(abs($myrow['saldo']),2) . '</td>';
						}


						break;
					}else{
						if($arrmeses[$i] == $mesactual){
							$bgcolormesactual = 'background-color:#F3F781;';
						}else{
							$bgcolormesactual = '';
						}
						$tabla1 = $tabla1 .'<td style="font-size:xx-small; text-align:right; ' . $bgcolormesactual. '">0.00</td>';
					}
				}
				$indice0 = $i + 1;
			}
		}/*FIN WHILE*/
		for($i=$indice0; $i<$contmes; $i++){
			if($arrmeses[$i] == $mesactual){
				$bgcolormesactual = 'background-color:#F3F781;';
			}else{
				$bgcolormesactual = '';
			}
			$tabla1 = $tabla1 . '<td style="font-size:xx-small; text-align:right; ' . $bgcolormesactual . '">0.00</td>';
		}
		if($totalsubcategoria < 0){
			$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; color:red; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
		}else{
			$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(abs($totalsubcategoria),2) . '</td>';
		}

		if($catant != ""){
			$tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
			$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left;">' . _('ST ') . $catant . '</td>';
			$totalxcategoria = 0;
			for($i=0; $i<$contmes; $i++){
				$totalxcategoria = $totalxcategoria + $arrtotalesxmesxcat[$i];
				if($arrtotalesxmesxcat[$i]<0){
					$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesxmesxcat[$i]),2) . '</td>';
				}else{
					$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesxmesxcat[$i]),2) . '</td>';
				}
			}
			if($totalxcategoria<0){
				$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($totalxcategoria),2) . '</td>';
			}else{
				$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($totalxcategoria),2) . '</td>';
			}

			$tabla1 = $tabla1 . '</tr>';
		}

		if($tipoant != ""){
			$tabla1 = $tabla1 . '<tr style="background-color:#0431B4; color:white;">';
			$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:left; color:white;">' . _('TOTAL ') . $tipoant . '</td>';
			$totalxtipo = 0;
			for($i=0; $i<$contmes; $i++){
				$totalxtipo = $totalxtipo + $arrtotalesxmesxtipo[$i];
				if($arrtotalesxmesxtipo[$i] < 0){
					$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($arrtotalesxmesxtipo[$i]),2) . '</td>';
				}else{
					$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:white;">' . number_format(($arrtotalesxmesxtipo[$i]),2) . '</td>';
				}

				$arrtotalesxmesxtipo[$i] = 0;
			}
			if($totalxtipo < 0){
				$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">(' . number_format(abs($totalxtipo),2) . ')</td>';
			}else{
				$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#0431B4; font-weight:bold; text-align:right; color:white;">' . number_format(($totalxtipo),2) . '</td>';
			}
			$tabla1 = $tabla1 . '</tr>';
		}

		$totales = $totales + $totalsubcategoria;
		$tablasi = '<tr style="background-color:#F5DA81;">';
		$tablasi = $tablasi . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('S.Inicial') . '</td>';
		$contmes = count($arrmeses);
		$arrsaldoinicial[0] = $saldoinicial;
		for($i=0;$i<$contmes;$i++){
			if($i>0){
				$arrsaldoinicial[$i] = $arrsaldoinicial[$i-1] +  $arrtotalesxmes[$i-1];
			}
			if($arrsaldoinicial[$i]<0){
				$tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right; color:red;">&nbsp;&nbsp;' . number_format(abs($arrsaldoinicial[$i]),2) . '</td>';
			}else{
				$tablasi = $tablasi . '<td nowrap style="background-color:#F5DA81; font-size:x-small; font-weight:bold; text-align:right;">&nbsp;&nbsp;' . number_format($arrsaldoinicial[$i],2) . '</td>';
			}

		}
		if($arrsaldoinicial[0] < 0){
			$tablasi = $tablasi . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:right; color:red;">&nbsp;&nbsp;' . number_format(abs($arrsaldoinicial[0]),2) . '</td>';
		}else{
			$tablasi = $tablasi . '<td style="font-size:x-small; font-weight:bold; background-color:#FFFF00; text-align:right;">&nbsp;&nbsp;' . number_format($arrsaldoinicial[0],2) . '</td>';
		}

		$tablasi = $tablasi . '</tr>';

		$tabla1 = $tabla1 . '</tr>';
		$tabla1 = $tabla1 . '<tr style="background-color:#ECF6CE">';
			$tabla1 = $tabla1 . '<td style="font-size:x-small; font-weight:bold; text-align:center;">' . _('Saldo Final') . '</td>';
			for($i=0; $i<$contmes; $i++){
				if(($arrtotalesxmes[$i] + $arrsaldoinicial[$i]) < 0){
					$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right; color:red;">' . number_format(abs($arrtotalesxmes[$i] + $arrsaldoinicial[$i]),2) . '</td>';
				}else{
					$tabla1 = $tabla1 .'<td style="font-size:xx-small; font-weight:bold; text-align:right;">' . number_format(($arrtotalesxmes[$i] + $arrsaldoinicial[$i]),2) . '</td>';
				}

			}
			if(($saldoinicial + $totales) < 0){
				$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right; color:red;">' . number_format(abs($saldoinicial + $totales),2) . '</td>';
			}else{
				$tabla1 = $tabla1 . '<td style="font-size:xx-small; background-color:#FFFF00; font-weight:bold; text-align:right;">' . number_format(($saldoinicial + $totales),2) . '</td>';
			}

		$tabla1 = $tabla1 . '</tr>';

	$tabla1 = $tabla0 . $tablasi . $tabla1 . '</table>';
	echo $tabla1;
}
	if (isset ( $_POST ['PrintEXCEL'] )) {
		exit;
	}

include('includes/footer.inc');
?>
