
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

$funcion=2020;
$PageSecurity = 3;
include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_POST['echoEXCEL'])) {
	$title = _('Reporte Subcategoria X Anio');
	include('includes/header.inc');
}
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

 /* OBTENGO FECHAS*/
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
	$_POST['BankAccount'] = 0;
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


if (!isset($_POST['echoEXCEL'])) {

	echo "<form name='FDatosA' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
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
			if (strpos($thislegalid,$myrow["legalid"]) !==false)
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

	echo '<tr><td><br></td><td>&nbsp;';
	echo '</td></tr>';
	echo '<tr><td style="text-align:right" nowrap><b>' . _('X Area') . ':' . "</b></td>
			<td><select tabindex='4' name='xArea'>";

	$sql = "SELECT distinct regions.name as regionname, areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
				FROM areas JOIN regions ON areas.regioncode = regions.regioncode
				JOIN tags ON tags.areacode=areas.areacode

			  WHERE tags.legalid in (". $thislegalid .")
			  GROUP BY regions.name, areas.areacode, areas.areadescription";

	$result=DB_query($sql,$db);

	echo "<option selected value='*'>Todas las areas...</option>";

	$regionAnterior = '';
	while ($myrow=DB_fetch_array($result)){
		if ($regionAnterior != $myrow["regionname"]) {
			echo "<option style='background-color:DarkGray' value='*'>***" . $myrow['regionname']."</option>";
			$regionAnterior = $myrow["regionname"];
		}

		if ($myrow['areacode'] == $_POST['xArea']){
			echo "<option selected value='" . $myrow["areacode"] . "'>" . $myrow['name']."</option>";
		} else {
			echo "<option value='" . $myrow['areacode'] . "'>" . $myrow['name']."</option>";
		}
	}
	echo '</select></td></tr>';
	/************************************/

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
			and bankaccounts.accountcode <> "101084"
		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';

	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);


		echo "<option selected value='0'>Todas las cuentas de cheques...</option>";
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
	 echo '<td  style="text-align:right"><b>' . _('X Anio de Consulta:') . '</b></td>';
	 echo '<td>&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
	 echo '</td>';

	 echo '</tr>';
	 echo '</tr>';



	echo '<tr><td><br></td><td>';
	echo '</td></tr>';

	echo '</table>
		  <br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Consulta Flujo') . '">&nbsp;&nbsp;';
	echo '<input tabindex="7" type=submit name="echoEXCEL" value="' . _('Exportar a Excel') . '">';
	echo '</div><br>';
	echo "</form>";
	echo '<hr/>';
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

	if (isset($_POST['echoEXCEL'])) {
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=GeneralSales.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
	}

	if (!isset($_POST['echoEXCEL'])) {
		echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
	}
	//echo "<table>";

	//echo '<input tabindex="7" type=submit name="echoEXCEL" value="' . _('Exportar a Excel') . '">';
		echo '<input Name="FromMes" type=hidden value="'.$FromMes.'">';
		echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
		if (isset($_POST['echoEXCEL'])) {
		echo "<h1 style='font-size:24px'>Reporte Subcategoria X Anio</h1>";
		echo '<br><br>';}
		echo '<table cellspacing=0 border=1 bordercolor=#101010 cellpadding=2 colspan="7">';
	//** DIBUJA PRIMERA LINEA CON MESES
			echo "<TR style='background-color:lightgray;'>";
				echo "<TD align=left width=20></TD>";
				echo "<TD align=right><b></b></TD>";
				for ($i=1; $i<=12; $i=$i+1){
					echo "<TD style='text-align:center' ><b>
								<a target='_blank' href=fjoResumenXSemana.php?&thislegalid=".trim($thislegalid)."&BankAccount=".trim($_POST['BankAccount'])."&mes=".$i."&FromYear=".trim($_POST['FromYear'])."&grupo=".$_GET['grupo'].">
					".$NMes[$i]."</a></b></TD>";
		$SaldoInicial[$i]=0;
		$SaldoFinal[$i]=0;
	}

				echo "<TD></TD>
					  <TD colspan=2 style='text-align:center'><b>".$FromYear."</b></TD>
					  <TD></TD>";
			echo "</TR>";

	//** DIBUJA LINEA CON SALDOS INICIALES
	if ($_POST['xArea']=="*"){
			echo "<TR bgcolor=\"#98fb98\">";
				echo "<TD align=left width=20 nowrap></TD>";
				echo "<TD align=right style='font-size:9px;font-weight:bold;'>SALDO INICIAL:</TD>";
					$bank = "";
					if ($_POST['BankAccount'] == '0')
						$bank = "and Movimientos.u_banco <> '101084'";
						for ($i=1; $i<=12; $i=$i+1){
								$colspan=1;
								$sql = "SELECT sum(Movimientos.abono - Movimientos.cargo) as saldo
								FROM Movimientos LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
								LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
								LEFT JOIN tags ON tags.tagref = Movimientos.tagref
								WHERE (Movimientos.u_empresa in (". $thislegalid .")) and activo = 1
								$bank
								AND (bankaccounts.accountcode = '". $_POST['BankAccount'] ."' OR '". $_POST['BankAccount'] ."' = '0')
								AND DATE(Movimientos.fecha) < DATE('".$FromYear."-".$i."-01')
								AND (tags.areacode = '". $_POST['xArea'] ."' OR '". $_POST['xArea'] ."' = '*')
								AND erp = 0 ";
								if (isset($_POST['SoloConfirmados'])){
									$sql = $sql . "AND confirmado = 0";
								}
							//	echo '<br><br><pre>sql:'.$sql;
								$result = DB_query($sql,$db);
								if ($SaldoInicial1 = DB_fetch_array($result)) {
									$SaldoInicial = $SaldoInicial1['saldo'];

									if ($SaldoInicial<0){
										echo "<TD style='text-align:right' colspan=".$colspan." style='font-size:10px;font-weight:bold;color:#AA0000'><b>(".number_format(abs($SaldoInicial),0).")</b></TD>";
									} else {
										echo "<TD style='text-align:right' colspan=".$colspan." style='font-size:10px;font-weight:bold;color:#000000'><b>".number_format($SaldoInicial,0)."</b></TD>";
									}
								} else {
									$SaldoInicial=0;
									echo "<TD style='text-align:right' colspan=".$colspan." ></TD>";
								}

								$SaldoFinal[$i]=$SaldoInicial;
						}

			echo "<TD></TD>
				  <TD style='text-align:right'></TD>
				  <TD></TD>";
		echo "</TR>";
	}
	//** DIBUJA LINEA DE TITULOS DE ENCABEZADO BAJO CADA MES
	echo "<TR>";
		echo "<TD align=left width=20></TD>";
		echo "<TD align=right><b></b></TD>";
		for ($i=1; $i<=12; $i=$i+1){
			echo "<TD width=55 style='text-align:center' style='font-size:12px;font-weight:bold'><b>+</b></TD>";
			$TotalCategoria[$i]=0;
			$TotalMes[$i] = 0;
		}

		$SaldoFinalPagado=0;
		$SaldoFinalPendiente=0;

		$TotalCategoriaPres=0;
		$TotalCategoriaPagado=0;
		$TotalCategoriaPendiente=0;

		$totGeneral = 0;

		echo "<TD></TD>
			   <TD colspan=2 style='text-align:center'><b></b></TD>
			   <TD></TD>";
	echo "</TR>";
	$cond="";
	if ($_GET['grupo'])
		$cond = "and fjoCategory.cat_id = '".$_GET['grupo']."'";
		$bank = "";
	if ($_POST['BankAccount'] == '0')
		$bank = "and Movimientos.u_banco <> '101084'";
		$sql = "SELECT fjoCategory.cat_name as GRUPO,
					fjoSubCategory.subcat_id as MId,
					fjoCategory.order as Orden,
					fjoSubCategory.subcat_name as Categoria,
					IFNULL(Mes,0) as Mes,
					sum(IFNULL(cargo,0)) as Cargos,
					sum(IFNULL(abono,0)) as Abonos,
					sum(IFNULL(abono,0) - IFNULL(cargo,0)) as Total,
					sum(CASE WHEN Movimientos.Confirmado=0 THEN IFNULL(abono,0) - IFNULL(cargo,0) ELSE 0 END) as Pagado,
					sum(CASE WHEN Movimientos.Confirmado=1 THEN IFNULL(abono,0) - IFNULL(cargo,0) ELSE 0 END) as PorCobrar
				FROM fjoCategory JOIN fjoSubCategory ON fjoCategory.cat_id = fjoSubCategory.cat_id

						LEFT JOIN Movimientos ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
						LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
						LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
					    LEFT JOIN tags ON tags.tagref = Movimientos.tagref
				WHERE (Movimientos.u_empresa in (". $thislegalid .")) and activo = 1
				$bank
				AND (bankaccounts.accountcode = '". $_POST['BankAccount'] ."' OR '". $_POST['BankAccount'] ."' = '0')
				AND YEAR(Movimientos.fecha) = '".$FromYear."'
				AND (tags.areacode = '". $_POST['xArea'] ."' OR '". $_POST['xArea'] ."' = '*')
				$cond
				AND erp = 0 ";

		if (isset($_POST['SoloConfirmados'])){
			$sql = $sql . "AND confirmado = 0 ";
		}

		$sql = $sql. "GROUP BY fjoCategory.cat_name,
					fjoSubCategory.subcat_id,
					fjoCategory.order,
					fjoSubCategory.subcat_name,
					Mes
				ORDER BY fjoCategory.order,
					fjoCategory.cat_name,
					fjoSubCategory.order,
					fjoSubCategory.subcat_name,
					Mes";
	//echo "<pre><br><br>sql2:".$sql;
	$result = DB_query($sql,$db);

	$grupoAnterior = "";
	$categoriaAnterior = "";

	$lingris = 0;
	$mesAnterior = 0;
	while ($rMovimientos = DB_fetch_array($result)) {
		if ($grupoAnterior == "" OR $grupoAnterior != $rMovimientos['GRUPO']) {

			if ($grupoAnterior != "") {
				/* Quiere decir que no es el primer grupo, entonces hay que escribir sub totales del anterior*/

				if ($categoriaAnterior != "") {
					for ($nummes = $mesAnterior;$nummes < 12;$nummes++) {
						echo "<TD style='text-align:center'>-</TD>";
					}

					echo "<TD style='text-align:center'></TD>";
					if ($totalCategoria < 0) {
						echo "<TD colspan=2 style='font-size:10px;font-weight:bold;text-align:right;color:#AA0000'><b>(".number_format(abs($totalCategoria),0).")</b></TD>";
					} else {
						echo "<TD colspan=2 style='font-size:10px;font-weight:bold;text-align:right'><b>".number_format($totalCategoria,0)."</b></TD>";
					}

					echo "</TR>";
				}


				echo "<TR style='background-color:red;height:5px'>";
					echo "<TD colspan=18 style='height:5px'></TD>";
				echo "</TR>";

				echo "<TR style='background-color:lightgray'>";
					echo "<TD align=left></TD>";
					echo "<TD align=left>SUB-TOTAL</TD>";

				$totMovimientos = 0;
				for ($i=1; $i<=12; $i=$i+1){
					if ($TotalCategoria[$i] < 0)
						echo "<TD align=right style='text-align:right;color:#AA0000;font-size:10px;font-weight:bold'><b>(".number_format(abs($TotalCategoria[$i]),0).")</b></TD>";
					else
						echo "<TD align=right style='text-align:right;font-size:10px;font-weight:bold'><b>".number_format($TotalCategoria[$i],0)."</b></TD>";

					$totMovimientos = $totMovimientos + $TotalCategoria[$i];
					$TotalCategoria[$i]=0;
				}

				if ($totMovimientos < 0) {
					echo "<TD></TD>
					<TD colspan=2 style='text-align:right;color:#AA0000' style='font-size:11px;font-weight:bold'><b>(".number_format(abs($totMovimientos),0).")</b></TD>
					<TD></TD>";
				} else {
					echo "<TD></TD>
					<TD colspan=2 style='text-align:right' style='font-size:11px;font-weight:bold'><b>".number_format($totMovimientos,0)."</b></TD>
					<TD></TD>";
				}


				echo "</TR>";
				$totalCategoria = 0;

			}

			echo "<TR style='background-color:white;height:15px'>";
				echo "<TD colspan=18 style='height:15px'></TD>";
			echo "</TR>";

			echo "<TR style='background-color:yellow'>";
			echo "<TD align=left colspan=18 style='font-size:12px;font-weight:bold' nowrap>".$rMovimientos['GRUPO']."</TD>";
			echo "</TR>";

			$categoriaAnterior = "";
		}


		if ($categoriaAnterior == "" OR $categoriaAnterior != $rMovimientos['Categoria']) {
			if ($categoriaAnterior != "") {
				for ($nummes = $mesAnterior;$nummes < 12;$nummes++) {
					echo "<TD style='text-align:center'>-</TD>";
				}

				echo "<TD style='text-align:center'></TD>";
				if ($totalCategoria < 0) {
					echo "<TD colspan=2 style='font-size:10px;font-weight:bold;text-align:right;color:#AA0000'><b>(".number_format(abs($totalCategoria),0).")</b></TD>";
				} else {
					echo "<TD colspan=2 style='font-size:10px;font-weight:bold;text-align:right;' ><b>".number_format($totalCategoria,0)."</b></TD>";
				}

				echo "</TR>";
			}

			if ($lingris == 0) {
				echo "<TR style='background-color:#FEFEFE'>";
				$lingris = 1;
			} else {
				echo "<TR style='background-color:#FAFAFA'>";
				$lingris = 0;
			}

			echo "<TD align=left></TD>";

			$ligaCategoria =  "<a target='".$rMovimientos['MId']."' href=" . $rootpath ."/fjoFlujoReclasificaV1_0.php?&TipoMovimiento=".$rMovimientos['MId']."&thislegalid=".trim($thislegalid)."&BankAccount=".trim($_POST['BankAccount'])."&FromMes=*&FromYear=".trim($_POST['FromYear']).">";

			echo "<TD align=left style='font-size:12px;font-weight:bold' nowrap>".$ligaCategoria." ".$rMovimientos['Categoria']." </a></TD>";
			$mesAnterior = 0;
			$totalCategoria = 0;
		}

		for ($nummes = $mesAnterior;$nummes < $rMovimientos['Mes']-1;$nummes++) {
			echo "<TD style='text-align:center'>-</TD>";
		}

		if ($rMovimientos['Total'] < 0)
			echo "<TD style='text-align:right;color:red' style='font-size:9px;font-weight:normal'>(".number_format(abs($rMovimientos['Total']),0).")</TD>";
		else
			echo "<TD style='text-align:right' style='font-size:9px;font-weight:normal'>".number_format($rMovimientos['Total'],0)."</TD>";

		$grupoAnterior = $rMovimientos['GRUPO'];
		$categoriaAnterior = $rMovimientos['Categoria'];
		$mesAnterior = $rMovimientos['Mes'];

		$TotalCategoria[$mesAnterior]=$TotalCategoria[$mesAnterior] + $rMovimientos['Total'];
		$TotalMes[$mesAnterior]=$TotalMes[$mesAnterior] + $rMovimientos['Total'];
		$totalCategoria = $totalCategoria + $rMovimientos['Total'];
	}

	if ($grupoAnterior != "") {
		/* Quiere decir que no es el primer grupo, entonces hay que escribir sub totales del anterior*/

		for ($nummes = $mesAnterior;$nummes < 12;$nummes++) {
			echo "<TD align=left>-</TD>";
		}

		echo "<TD style='text-align:center'></TD>";
		if ($totalCategoria < 0) {
			echo "<TD colspan=2 style='font-size:10px;font-weight:bold;text-align:right;color:#AA0000'><b>(".number_format(abs($totalCategoria),0).")</b></TD>";
		} else {
			echo "<TD colspan=2 style='font-size:10px;font-weight:bold;text-align:right'><b>".number_format($totalCategoria,0)."</b></TD>";
		}
		echo "</TR>";

		echo "<TR style='background-color:red;height:5px'>";
		echo "<TD colspan=18 style='height:5px'></TD>";
		echo "</TR>";

		echo "<TR style='background-color:lightgray' style='font-size:12px;font-weight:bold'>";
		echo "<TD align=left></TD>";
		echo "<TD align=left>SUB-TOTAL</TD>";

		$totMovimientos = 0;
		for ($i=1; $i<=12; $i=$i+1){
			if ($TotalCategoria[$i] < 0)
				echo "<TD style='text-align:right;color:#AA0000;font-size:10px;font-weight:bold'><b>(".number_format(abs($TotalCategoria[$i]),0).")</b></TD>";
			else
				echo "<TD style='text-align:right;font-size:10px;font-weight:bold'><b>".number_format($TotalCategoria[$i],0)."</b></TD>";

			$totMovimientos = $totMovimientos + $TotalCategoria[$i];
			$TotalCategoria[$i]=0;
		}

		if ($totMovimientos < 0) {
			echo "<TD></TD>
			<TD colspan=2 style='text-align:right;color:#AA0000;font-size:11px;font-weight:bold'><b>(".number_format(abs($totMovimientos),0).")</b></TD>
			<TD></TD>";
		} else {
			echo "<TD></TD>
			<TD colspan=2 style='text-align:right;font-size:11px;font-weight:bold'><b>".number_format($totMovimientos,0)."</b></TD>
			<TD></TD>";
		}

		echo "</TR>";
	}

	/* SALDOS FINALES */

	echo "<TR style='background-color:white;height:15px'>";
	echo "<TD colspan=18 style='height:15px'></TD>";
	echo "</TR>";

	echo "<TR style='background-color:#F0FFF0'>";
	echo "<TD align=left colspan=2 style='font-size:12px;font-weight:bold'>TOTAL MOVIMIENTOS</TD>";

	for ($i=1; $i<=12; $i=$i+1){
		if ($TotalMes[$i] < 0)
			echo "<TD style='text-align:right;color:#AA0000' style='font-size:11px;font-weight:bold'><b>(".number_format(abs($TotalMes[$i]),0).")</b></TD>";
		else
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold'><b>".number_format($TotalMes[$i],0)."</b></TD>";

		$totGeneral = $totGeneral + $TotalMes[$i];
	}

	if ($totGeneral < 0)
		echo "<TD></TD>
			<TD colspan=2 style='text-align:right;color:red' style='font-size:12px;font-weight:bold'><b>(".number_format(abs($totGeneral),0).")</b></TD>
			<TD></TD>";
	else
		echo "<TD></TD>
			<TD colspan=2 style='text-align:right' style='font-size:12px;font-weight:bold'><b>".number_format(abs($totGeneral),0)."</b></TD>
			<TD></TD>";

	echo "</TR>";

	/* SALDOS FINALES -- INICIAL MENOS MOVIMIENTOS */
	if ($_POST['xArea']=="*"){
		echo "<TR style='background-color:white;height:15px'>";
		echo "<TD colspan=18 style='height:15px'></TD>";
		echo "</TR>";

		echo "<TR style='background-color:#A0FFA0'>";
		echo "<TD align=left colspan=2 style='font-size:12px;font-weight:bold'>SALDO FINAL</TD>";

		for ($i=1; $i<=12; $i=$i+1){
			if (($SaldoFinal[$i]+$TotalMes[$i]) < 0)
				echo "<TD style='text-align:right;color:#AA0000' style='font-size:11px;font-weight:bold'><b>(".number_format(abs($SaldoFinal[$i]+$TotalMes[$i]),0).")</b></TD>";
			else
				echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold'><b>".number_format($SaldoFinal[$i]+$TotalMes[$i],0)."</b></TD>";
		}

		echo "<TD></TD>
			<TD colspan=2 style='text-align:right' style='font-size:12px;font-weight:bold'><b></b></TD>
			<TD></TD>";

		echo "</TR>";
	}

	echo "</table>";
if (!isset($_POST['echoEXCEL'])) {
	echo "</form>";
}
	echo "<p style='font-size:8px'>Impreso el:".DATE('Y-m-d H:m:s').'</p>';
if (!isset($_POST['echoEXCEL'])) {
	include('includes/footer.inc');
}
?>
