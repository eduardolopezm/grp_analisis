
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



$funcion=2030;

$PageSecurity = 3;
include('includes/session.inc');

$title = _('Impresion de Flujo de Efectivo Detallado');

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
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

if (isset($_GET['legalid'])){
	$_POST['legalid'] = $_GET['legalid'];
}

if (!isset($_POST['legalid'])){
	$_POST['legalid'] = 0;
}
if (!isset($_POST['FromMes'])){
	$_POST['FromMes'] = $FromMes;
}
if (!isset($_POST['FromYear'])){
	$_POST['FromYear'] = $FromYear;
}

if (!isset($_POST['BankAccount'])){
	$_POST['BankAccount'] = 0;
}

if (isset($_POST['LlevaIva'])){
	$iva=1;
}else{
	$iva=0;
}

if (isset($_POST['LlevaISR'])){
	$isr=1;
}else{
	$isr=0;
}

if(isset($_GET['entidadNegocio'])) {
	$_POST['entidadNegocio'] = $_GET['entidadNegocio'];
} else if(isset($_POST['entidadNegocio']) == FALSE) {
	$_POST['entidadNegocio'] = '*';
}

if (isset($_GET['thislegalid']))
	$_POST['thislegalid'] = $_GET['thislegalid'];

if (isset($_POST['thislegalid']) AND strlen($_POST['thislegalid']) > 0) {
	$thislegalid = $_POST['thislegalid'];
	//echo $_POST['thislegalid'];
} elseif (isset($_GET['thislegalid']) ) {
	$thislegalid = $_GET['thislegalid'];
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


/* EJECUTA TODAS LAS OPERACIONES DE BD */
//include('includes/MiFlujoHeader.inc');

//echo $_POST['BankAccount'];

	echo "<form name='FDatosA' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	/************************************/
	/* SELECCION DEL RAZON SOCIAL       */

	//echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
	echo '<input type=hidden id=legallist name=BankAccount value="'.$_POST['BankAccount'].'">';
	echo '<input type=hidden id=legallist name=FromMes value="'.$FromMes.'">';
	echo '<input type=hidden id=legallist name=FromYear value="'.$FromYear.'">';

	echo '<table border=1>';
	echo '<tr><td style="vertical-align:top;text-align:center"><b>'._('X Razon Social:').'</b></td>';
	echo '<td>';


	/************************************/
	/* SELECCION DEL RAZON SOCIAL */

	echo '<select name="thislegalid">';
	///Imprime las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'

			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";

	$result=DB_query($SQL,$db);

	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='".$thislegalid."'>Todas las seleccionadas...</option>";

	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['thislegalid']) and $_POST['thislegalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		}
	}
	echo '</select></td></tr>';
	/************************************/

	echo '<tr><td><br></td><td>&nbsp;';
	echo '</td></tr>';

	/* SELECCIONA EL BANCO */

	$SQL = 'SELECT bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode
		FROM bankaccounts, sec_unegsxuser, tagsxbankaccounts
				JOIN tags ON tagsxbankaccounts.tagref = tags.tagref
		WHERE bankaccounts.accountcode = tagsxbankaccounts.accountcode and
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
			tags.legalid in ('. $thislegalid .')';
	if($_POST['BankAccount'] != '*') {
		$SQL .= ' and bankaccounts.accountcode = '.$_POST['BankAccount'];
	}
	$SQL .=
		' GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';

	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	if ($myrow=DB_fetch_array($AccountsResults,$db)){
		echo '<tr><td style="text-align:right"><b>' . _('X Cuenta de Cheques') . ':</b></td><td>';
		echo $myrow['accountcode'] . '-'. $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
		echo '</td></tr>';
	}
	/* SELECCIONA EL RANGO DE FECHAS */

	echo '<tr>';
	 echo '<td  style="text-align:right"><b>' . _('X Mes de Consulta:') . '</b></td>';
	 echo '<td><select Name="FromMes">';

		echo "<option selected value='*'>Todos los meses...</option>";

		   $sql = "SELECT * FROM cat_Months";
		   $Meses = DB_query($sql,$db);
		   while ($myrowMes=DB_fetch_array($Meses,$db)){
		       $Mesbase=$myrowMes['u_mes'];
		       if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" selected>' .$myrowMes['mes'];
		       }else{
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
		       }
		   }

		   echo '</select>';
		   echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
	   echo '</td>';

	 echo '</tr>';
	 echo '</tr>';

	 /************************************/
	/* SELECCION ENTIDAD */

	echo "<tr><td>" . _('Entidad Negocios') . ":</td><td>";
	//echo "--> " . $thislegalid;

	echo 	"<select name='entidadNegocio'>";

	$SQL = "select u_entidad, legalid, Nombre
		  from usrEntidades
		  where (legalid in (" . $thislegalid . "))
		  order by Nombre";

	$resultGC=DB_query($SQL,$db);

	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='*'>Todas las entidades...</option>";

	while ($myrowGC=DB_fetch_array($resultGC)){
		if (isset($_POST['entidadNegocio']) AND $_POST['entidadNegocio'] == $myrowGC['u_entidad'] )
			echo '<option value="' . $myrowGC['u_entidad'] . '" selected>' . $myrowGC['Nombre'] .'</option>';
		else
			echo '<option value="' . $myrowGC['u_entidad'] . '">' . $myrowGC['Nombre'] .'</option>';
	}
	echo '</select>';
	echo "</td></tr>";
	/************************************/

	echo '<tr><td><br></td><td>';
	echo '</td></tr>';
	echo '</table><br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Consulta Flujo') . '">&nbsp;&nbsp;';

		echo '<input tabindex="7" type=submit id="btn2" name="PrintPDF" value="' . _('Formato Impresion') . '"></div><br>';

	echo "</form>";

	echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
//if (isset($_POST['ReportePantalla']) or isset($_GET['Edit'])) {
	echo '<input Name="FromMes" type=hidden value="'.$FromMes.'">';

	echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';

	echo '<br><table cellspacing=0 border=1 bordercolor=white cellpadding=2 colspan="7">';
		/*desarrollo- Quite estos encabezados que ya no uso - 15/OCT/2011
		       <th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('T') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('S') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('V') . "</th>
			*/
		echo "<tr>
			<th nowrap width= 5% style='background-color:#150C67;'><b><font face='arial' size=1 color='#FFFF00'><b>
			<img src='".$rootpath."/css/flujo/flecha_borrar.gif' width=5 height=10'>
			<img border=0 src='".$rootpath."/css/flujo/flecha_lock.gif' width=5 height=10'>
			<img border=0 src='".$rootpath."/css/flujo/flecha_unlock.gif' width=5 height=10'></b></th>
			<th width= 10% style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Fecha') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Sem') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Concepto') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('ID') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Ref') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Ent') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Cargo') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Abono') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Saldo') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Prio') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Confirmado') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Banco') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('Categoria') . "</th>";
		echo "</tr>";

	$SaldoInicial1 = 0;
	$SaldoInicial1_CONFIRMADO = 0;

	if (!isset($_POST['entidadNegocio']) OR $_POST['entidadNegocio'] =='*') {

		$sql = "SELECT sum(Movimientos.abono - Movimientos.cargo) as saldo
			FROM Movimientos LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
					 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
			WHERE (Movimientos.u_empresa in (". $thislegalid .")) and activo = 1
			AND (bankaccounts.accountcode = '". $_POST['BankAccount'] ."' OR '". $_POST['BankAccount'] ."' = '0')
			AND DATE(Movimientos.fecha) < DATE('".$FromYear."-".trim($FromMes)."-01')
			AND erp = 0 ";

		//echo $sql;
		$result = DB_query($sql,$db);
		$SaldoInicial1 = DB_fetch_array($result);

		$sql = "SELECT sum(Movimientos.abono - Movimientos.cargo) as saldo
			FROM Movimientos LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
					 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
			WHERE (Movimientos.u_empresa in (". $thislegalid .")) and activo = 1
			AND (bankaccounts.accountcode = '". $_POST['BankAccount'] ."' OR '". $_POST['BankAccount'] ."' = '0')
			AND DATE(Movimientos.fecha) < DATE('".$FromYear."-".trim($FromMes)."-01')
			AND erp = 0
			AND confirmado = 0 ";

		//echo $sql;
		$result = DB_query($sql,$db);
		$SaldoInicial1_CONFIRMADO = DB_fetch_array($result);

			echo 	"<TR>
				<TD colspan=3 style='background-color:#0174DF;'></TD>
				<TD style='background-color:#0174DF;'><b><font face='Arial Narrow' size='2' color='#F8FB02'><b>SALDO INICIAL DEL MES:</b></font></TD>
				<TD colspan=5 style='background-color:#0174DF;'></TD>";

		if ($SaldoInicial1['saldo'] < 0) {
			echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#A00000'><b>$&nbsp;". number_format($SaldoInicial1['saldo'],2). "</b></font></TD>
				<TD colspan=1 style='background-color:#0174DF;'></TD>";
		} else {
			echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;". number_format($SaldoInicial1['saldo'],2). "</b></font></TD>
				<TD colspan=1 style='background-color:#0174DF;'></TD>";
		}

		if ($SaldoInicial1_CONFIRMADO['saldo'] < 0) {
			echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#A00000'><b>$&nbsp;". number_format($SaldoInicial1_CONFIRMADO['saldo'],2). "</b></font></TD>
				<TD colspan=2 style='background-color:#0174DF;'></TD>";
		} else {
			echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;". number_format($SaldoInicial1_CONFIRMADO['saldo'],2). "</b></font></TD>
				<TD colspan=2 style='background-color:#0174DF;'></TD>";
		}
		echo "</TR>";
	}
	/*
	$sql = "UPDATE debtortrans
		  set duedate = trandate
		  WHERE duedate is null
		";

	$result = DB_query($sql,$db);
	*/

	$sql = "select  sum(M.abono - M.cargo) as saldo,
			sum(CASE WHEN (confirmado = 0) THEN (M.abono - M.cargo) ELSE 0 END) as saldo_confirmado
		from Movimientos  M, bankaccounts B, legalbusinessunit O
		where M.u_banco = B.accountcode
		AND M.u_empresa= O.legalid
		AND (M.u_empresa in (". $thislegalid ."))
		AND ((M.u_banco = '" . $_POST['BankAccount'] ."'
		AND '". $_POST['BankAccount']."' > '0' )  or '". $_POST['BankAccount'] ."' = '0')
		AND M.activo = 1
		AND mes = '". $FromMes ."'
		AND anio = '". $FromYear ."'
		AND erp = 0
		";


	//echo $sql;
	$result = DB_query($sql,$db);
	$myrow2 = DB_fetch_array($result);


	$SQL = "SELECT u_movimiento,
			concepto,
			dia,
			0 as atrasado,
			referencia,
			cargo,
			abono,
			legalbusinessunit.legalid,
			legalname,
			bankaccountname,
			confirmado,
			prioridad,
			usrTipoMovimiento.Descripcion as Categoria,
			week(concat(anio,'-',mes,'-',dia),1) as sem,
			grupo_contable,
			activo,
			usrTipoMovimiento.TipoMovimientoId as CategoriaId,
			usrEntidades.Nombre as nombreEntidad,
			mes,
			anio
		FROM	Movimientos LEFT JOIN legalbusinessunit	ON Movimientos.u_empresa = legalbusinessunit.legalid
					LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
					LEFT JOIN accountgroups ON Movimientos.grupo_contable = accountgroups.groupname
					LEFT JOIN usrTipoMovimiento ON usrTipoMovimiento.TipoMovimientoId = Movimientos.TipoMovimientoId
					LEFT JOIN usrEntidades ON Movimientos.u_entidad = usrEntidades.u_entidad
		WHERE (mes = '". $FromMes ."' OR '".$FromMes."'='*') AND (anio = '". $FromYear ."' OR '". $FromYear ."'='*')
			and (Movimientos.u_empresa in (". $thislegalid ."))
			and (Movimientos.u_entidad = '". $_POST['entidadNegocio'] ."' or '". $_POST['entidadNegocio'] ."'='*')
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' =0) AND erp = 0 ";



		/* MOVIMIENTOS DE CXC */

	if (isset($_POST['ERPCXC'])){
	  $SQL = $SQL."
		UNION
		Select debtortrans.id as u_movimiento,
		debtorsmaster.name as concepto,
		CASE WHEN DATE_FORMAT(debtortrans.duedate,'%Y-%m') < CONCAT('".$_POST['FromYear']."','-',RIGHT(CONCAT(0,'".$_POST['FromMes']."'),2)) THEN
		1 ELSE DAY(debtortrans.duedate) END as dia,
		CASE WHEN DATE_FORMAT(debtortrans.duedate,'%Y-%m') < CONCAT('".$_POST['FromYear']."','-',RIGHT(CONCAT(0,'".$_POST['FromMes']."'),2)) THEN
		1 ELSE 0 END as atrasado,
		CONCAT('TIPO:',systypescat.typeid,'<br>',debtortrans.folio) as referencia,
		CASE WHEN ((debtortrans.ovamount+debtortrans.ovgst)/debtortrans.rate) < 0 THEN debtortrans.ovamount+debtortrans.ovgst ELSE 0 END as cargo,
		CASE WHEN ((debtortrans.ovamount+debtortrans.ovgst)/debtortrans.rate) >= 0 THEN debtortrans.ovamount+debtortrans.ovgst ELSE 0 END as abono,
		tags.legalid as legalid,
		legalbusinessunit.legalname as legalname,
		'' as bankaccountname, 2 as confirmado,
		priority as prioridad,
		'' as Categoria,
		CASE WHEN DATE_FORMAT(debtortrans.duedate,'%Y-%m') < CONCAT('".$_POST['FromYear']."','-',RIGHT(CONCAT(0,'".$_POST['FromMes']."'),2)) THEN
		week(CONCAT('".$_POST['FromYear']."','-',RIGHT(CONCAT(0,'".$_POST['FromMes']."'),2),'-01'),1) ELSE week(debtortrans.duedate,1) END as sem,
		'' as grupo_contable,
			activo,
		'' as CategoriaId,
		'' as nombreEntidad

		from debtortrans JOIN systypescat ON debtortrans.type = systypescat.typeid
		JOIN tags ON debtortrans.tagref = tags.tagref
		JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid
		JOIN departments ON departments.u_department = tags.u_department
		JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = 'admin'
		JOIN areas ON tags.areacode = areas.areacode JOIN regions ON areas.regioncode = regions.regioncode
		JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
		JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
		LEFT JOIN salesman ON salesman.salesmancode=custbranch.salesman

		where (ABS(debtortrans.ovamount + debtortrans.ovgst - debtortrans.alloc) > 0.9) and
		debtortrans.type in ('10','21','70','110','400','410','450','440') and (debtorsmaster.typeid = 0 or 0=0) and
		(custbranch.salesman = -1 or '-1'='-1') AND (legalbusinessunit.legalid = '". $_POST['legalid'] ."' or '". $_POST['legalid'] ."'='0') and
		(areas.regioncode = 0 or 0=0) and (areas.areacode = '0' or '0'='0') and
		(departments.u_department = '0' or '0'='0') and (debtortrans.tagref = 0 or 0=0) and
		DATE_FORMAT(debtortrans.duedate,'%Y-%m') <= CONCAT('".$_POST['FromYear']."','-',RIGHT(CONCAT(0,'".$_POST['FromMes']."'),2)) and
		((ovamount + ovgst) - alloc) > 1 ";
	}

	/* MOVIMIENTOS DE CXP */
	if (isset($_POST['ERPCXP'])){
	    $SQL = $SQL. "UNION

			  Select supptrans.id as u_movimiento,
				  suppliers.suppname as concepto,
				  CASE WHEN DATE_FORMAT(supptrans.duedate,'%Y-%m') < '".$_POST['FromYear']."-".$_POST['FromMes']."' THEN
				  1 ELSE DAY(supptrans.duedate) END as dia,
				  CASE WHEN DATE_FORMAT(supptrans.duedate,'%Y-%m') < '".$_POST['FromYear']."-".$_POST['FromMes']."' THEN
				  1 ELSE 0 END as atrasado,
				  CONCAT('TIPO:',systypescat.typeid,'<br>',supptrans.folio) as referencia,
				  CASE WHEN ((supptrans.ovamount+supptrans.ovgst)/supptrans.rate) >= 0 THEN supptrans.ovamount+supptrans.ovgst ELSE 0 END as cargo,
				  CASE WHEN ((supptrans.ovamount+supptrans.ovgst)/supptrans.rate) < 0 THEN supptrans.ovamount+supptrans.ovgst ELSE 0 END as abono,
				  tags.legalid as legalid,
				  legalbusinessunit.legalname as legalname,
				  '' as bankaccountname, 3 as confirmado,
				  5 as prioridad,
				  '' as Categoria,
				  CASE WHEN DATE_FORMAT(supptrans.duedate,'%Y-%m') < '".$_POST['FromYear']."-".$_POST['FromMes']."' THEN
				  week('".$_POST['FromYear']."-".$_POST['FromMes']."-01',1) ELSE week(supptrans.duedate,1) END as sem,
				  '' as grupo_contable,
				activo ,
				'' as CategoriaId,
				'' as nombreEntidad
			  from supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
				JOIN tags ON supptrans.tagref = tags.tagref
				JOIN legalbusinessunit on tags.legalid=legalbusinessunit.legalid
				JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
				JOIN areas ON tags.areacode = areas.areacode
				JOIN regions ON areas.regioncode = regions.regioncode
				JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
			  where (abs((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc) > .01) and
				supptrans.type in ('480','470','20','32','33','34','22','121')
				and (tags.legalid = '".$_POST['legalid']."' or '".$_POST['legalid']."'='0')
				and DATE_FORMAT(supptrans.duedate,'%Y-%m') <= '".$_POST['FromYear']."-".$_POST['FromMes']."' and
				((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc) > 1

				";
	}

	$SQL = $SQL . " ORDER BY anio,mes,dia,prioridad,referencia,abono desc,cargo desc";

	$result = DB_query($SQL,$db);
	//ECHO $SQL;

	/* AREGLO PARA DESPLEGAR EL MES CON TEXTO EN VES DE NUMERO */
	$friendlymes = array(1=>"Ene",2=>"Feb",3=>"Mar",4=>"Abr",5=>"May",6=>"Jun",7=>"Jul",8=>"Ago",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dic");


	$SaldoInicial = $SaldoInicial1['saldo'];
	$SaldoInicialConfirmado = $SaldoInicial1_CONFIRMADO['saldo'];


	$totalCargos = 0;
	$totalAbonos = 0;

	$I = 0;
	$semanant='';
	while($myrow = DB_fetch_array($result)){
		$I ++;

		$totalCargos = $totalCargos + $myrow['cargo'];
		$totalAbonos = $totalAbonos + $myrow['abono'];

		if($myrow['confirmado'] == '0'){
			$borrar = "";
			$modificar = "";
			$bloquear = "";
			$prioridadmas =	"";
			$prioridadmenos = "";
			$check = "";


			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				$saldoConfirmado = $SaldoInicialConfirmado;
				echo "<tr style='background-color:pink'>";
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];

				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($myrow['confirmado'] == '0')
					$saldoConfirmado = $SaldoInicialConfirmado - $myrow['cargo'] + $myrow['abono'];

				echo "<tr style='background-color:#A0B0A0'>";
			}

			echo    "<td align=center nowrap>". $check .''. $modificar ."</td>";
			echo 	"<td nowrap align=right style='font-size:9px;font-weight:bold;text-align:right'>". $myrow['dia'].'/'. $friendlymes[$myrow['mes']] .'/'. $myrow['anio'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['sem'] ."</td><td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td style='font-size:9px;font-weight:bold;'>". $myrow['referencia'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>".$myrow['nombreEntidad']."</td>";




			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['cargo'],2) ."</td>";
			}else{
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right>&nbsp;</td>";
			}

			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['abono'],2) ."</td>";
			}
			else{
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right>&nbsp;</td>";
			}

			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,0) ."</td>";

			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";

			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $myrow['prioridad'] ."</td>";

			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";

			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['bankaccountname'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['Categoria'] ."</td>";
			echo "</tr>";

			$SaldoInicial = $saldo;
			$SaldoInicialConfirmado = $saldoConfirmado;

			if($myrow['activo'] == '0'){
				$SaldoFinalS = $SaldoFinalS;
			} else {
				$SaldoFinalS = $SaldoFinalS + $SaldoInicial;
			}

		}elseif($myrow['confirmado'] == '1'){
			$ligajs = "";

			$borrar = "";
			$modificar = "";
			$bloquear = "";
			$prioridadmas =	"";
			$prioridadmenos = "";
			$antdia =	"";
			$sigdia = "";
			$antsem =	"";

			$sigsem = "";

			$antmes =	"";
			$sigmes = "";
			$antanio =	"";
			$siganio = "";
			$check = "";

			$modificarDatos = "";

			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				$saldoConfirmado = $SaldoInicialConfirmado;

				echo "<tr style='background-color:pink;'>";
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];
				$saldoConfirmado = $SaldoInicialConfirmado;

				echo "<tr>";
			}


			echo    "<td align=center nowrap>". $borrar .''. $check .''. $bloquear ."</td>";
			echo 	"<td align=right nowrap style='font-size:9px;font-weight:bold;text-align:right'>". $antdia .''. $myrow['dia'] .'/'. $sigdia .''. $antmes .''. $friendlymes[$myrow['mes']]
							.'/'. $sigmes .''. $antanio .''. $myrow['anio'].''. $siganio ."</td>";

			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap>". $myrow['concepto'] ."</td>";
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td>". $myrow['referencia'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>".$myrow['nombreEntidad']."</td>";

			if ($myrow['cargo'] != 0)

			    echo "<td>".number_format($myrow['cargo'],2)."</td>";

			    //echo 	"<td><p align=right>$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			else
			    echo "<td></td>";
			    //echo 	"<td><p align=right>&nbsp;</td>";

			if ($myrow['abono'] != 0)
			    echo "<td>".number_format($myrow['abono'],2)."</td>";

			    //echo 	"<td style='color:green;'><p align=right>$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			else
			    echo "<td></td>";
			    //echo 	"<td><p align=right>&nbsp;</td>";

			//echo 	"<td><p align=right>$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			//echo 	"<td><p align=right>$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:red;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";

			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $prioridadmas .''. $myrow['prioridad'] .''. $prioridadmenos ."</td>";

			/*
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:gray;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:#45AAAA;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			*/
			echo 	"<td nowrap style='color:#45AAAA;' style='font-size:9px;font-weight:bold;'><p align=right><b></b></td>";

			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['bankaccountname'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['Categoria'] ."</td>";
			echo "</tr>";

			$SaldoInicial = $saldo;
			$SaldoInicialConfirmado = $saldoConfirmado;

			if($myrow['activo'] == '0'){
				$SaldoFinalS = $SaldoFinalS;
			} else {
				$SaldoFinalS = $SaldoFinalS + $SaldoInicial;
			}

		}elseif($myrow['confirmado'] == '2'){
			$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridadCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a> ";
			$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridadCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antdia =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorDiaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
			$sigdia = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteDiaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";

			$antsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorSemanaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";

			$sigsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteSemanaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='siguiente semana' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";

			$antmes =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorMesCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteMesCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antanio =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorAnioCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$siganio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteAnioCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$check = "<INPUT type=checkbox id='chkCXC".$I."' name='selMovimientoCXC[]' value='".$myrow['u_movimiento']."')'>&nbsp;&nbsp;&nbsp;";

			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				echo "<tr style='background-color:pink'>";
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];

				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($myrow['confirmado'] == '0')
					$saldoConfirmado = $SaldoInicialConfirmado - $myrow['cargo'] + $myrow['abono'];

				echo "<tr>";
			}

			echo    "<td align=center nowrap  style='background-color:#9eFF9e'>". $check ." CXC</td>";
			echo 	"<td align=center nowrap>". $antdia .''. $myrow['dia'] .''. $sigdia .''. $antmes .''. $myrow['mes']
							.''. $sigmes .''. $antanio .''. $myrow['anio'] .''. $siganio ."</td>";

			echo 	"<td nowrap>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td  nowrap style='background-color:#FF9e9e' >". $myrow['referencia'] ."</td>";
			echo 	"<td>".$myrow['nombreEntidad']."</td>";


			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['cargo'],2) ."</td>";
			}else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}

			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['abono'],2) ."</td>";
			}
			else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}

			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,0) ."</td>";

			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";

			echo 	"<td><p align=center>". $prioridadmas .''. $myrow['prioridad'] .''. $prioridadmenos ."</td>";

			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";

			echo 	"<td nowrap>-</td>";
			echo 	"<td nowrap>-</td>";
			echo "</tr>";

			$SaldoInicial = $saldo;
			$SaldoInicialConfirmado = $saldoConfirmado;

			if($myrow['activo'] == '0'){
				$SaldoFinalS = $SaldoFinalS;
			} else {
				$SaldoFinalS = $SaldoFinalS + $SaldoInicial;
			}

		}elseif($myrow['confirmado'] == '3'){
			$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridadCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridadCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antdia =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorDiaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigdia = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteDiaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorSemanaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteSemanaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='siguiente semana' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorMesCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteMesCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antanio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorAnioCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$siganio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteAnioCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$check = " <INPUT type=checkbox id='chkCXP".$I."' name='selMovimientoCXP[]' value='".$myrow['u_movimiento']."')'>&nbsp;&nbsp;&nbsp;";

			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				echo "<tr style='background-color:pink'>";
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];

				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($myrow['confirmado'] == '0')
					$saldoConfirmado = $SaldoInicialConfirmado - $myrow['cargo'] + $myrow['abono'];

				echo "<tr>";
			}

			echo "<tr>";
			echo    "<td align=center nowrap  style='background-color:#FF9e9e'>". $check ." CXP</td>";
			echo 	"<td align=center nowrap>". $antdia .''. $myrow['dia'] .''. $sigdia .''. $antmes .''. $myrow['mes']
							.''. $sigmes .''. $antanio .''. $myrow['anio'] .''. $siganio ."</td>";

			echo 	"<td nowrap>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td  nowrap style='background-color:#FF9e9e' >". $myrow['referencia'] ."</td>";
			echo 	"<td>".$myrow['nombreEntidad']."</td>";
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['cargo'],2) ."</td>";
			}else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}

			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['abono'],2) ."</td>";
			}
			else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}

			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,0) ."</td>";

			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";

			echo 	"<td><p align=center>". $myrow['prioridad'] ."</td>";

			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";

			echo 	"<td nowrap>-</td>";
			echo 	"<td nowrap>-</td>";
			echo "</tr>";

			$SaldoInicial = $saldo;
			$SaldoInicialConfirmado = $saldoConfirmado;

			if($myrow['activo'] == '0'){
				$SaldoFinalS = $SaldoFinalS;
			} else {
				$SaldoFinalS = $SaldoFinalS + $SaldoInicial;
			}
		}
	}

	if($semanant != $myrow['sem']){
		if($semanant != ''){
		$sql = "select sum(abono) as abono
		from Movimientos
		where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1
			and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
			and (Movimientos.u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
			and (Movimientos.u_entidad = '". $_POST['entidadNegocio'] ."' or '". $_POST['entidadNegocio'] ."'='*')
			and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0)
			AND erp = 0";

		$ResultS2 = DB_query($sql,$db);
		$abonoT2 = DB_fetch_array($ResultS2);
		//echo $sql;

		$sql = "select sum(cargo) as cargo
		from Movimientos
		where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1
			and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
			and (Movimientos.u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
			and (Movimientos.u_entidad = '". $_POST['entidadNegocio'] ."' or '". $_POST['entidadNegocio'] ."'='*')
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' =0)
			AND erp = 0";

		$ResultS2 = DB_query($sql,$db);
		$cargoT2 = DB_fetch_array($ResultS2);


		$SaldoFinalC2 = $cargoT2['cargo'];
		$SaldoFinalA2 = $abonoT2['abono'];


		echo "<TR height=20 bgcolor=#A9D0F5>";
		echo "<TD colspan=5 align=left style='font-size:11px;font-weight:bold;'><b>SALDO FINAL DE  SEMANA".'_'.$semanant."</b></TD>";
		echo "<TD colspan=2 align=center style='font-size:11px;font-weight:bold;'><b></b></TD>";
		echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;'><b>$&nbsp;" . number_format($SaldoFinalC2,2) . "</b></TD>";
		echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;'><b>$&nbsp;" . number_format($SaldoFinalA2,2) . "</b></TD>";
		if ($saldo < 0 ){
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#A01010' align=right><b>$&nbsp;" . number_format($saldo,2) . "</b></TD>";
		}else{
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#101010' align=right><b>$&nbsp;" . number_format($saldo,2) . "</b></TD>";
		}
		echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>X</td>";

		if ($saldoConfirmado < 0 ){
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#A01010' align=right><b>$&nbsp;" . number_format($saldoConfirmado,2) . "</b></TD>";
		}else{
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#101010' align=right><b>$&nbsp;" . number_format($saldoConfirmado,2) . "</b></TD>";
		}

		echo "</TR>";
	      }

	      }

	$sql = "select sum(cargo) as cargo
		from Movimientos
		where mes = '". $_POST['FromMes'] ."'
			and anio = '". $_POST['FromYear'] ."'
			and (u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
			and (Movimientos.u_entidad = '". $_POST['entidadNegocio'] ."' or '". $_POST['entidadNegocio'] ."'='*')
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' =0) and activo = 1
			AND erp = 0";

	$ResultS = DB_query($sql,$db);
	$cargoT = DB_fetch_array($ResultS);
	//echo $sql;

	$sql = "select sum(abono) as abono
		from Movimientos
		where mes = '". $_POST['FromMes'] ."'
			and anio = '". $_POST['FromYear'] ."'
			and (u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
			and (Movimientos.u_entidad = '". $_POST['entidadNegocio'] ."' or '". $_POST['entidadNegocio'] ."'='*')
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' =0) and activo = 1
			AND erp = 0";

	$ResultS = DB_query($sql,$db);
	$abonoT = DB_fetch_array($ResultS);
	//echo $sql;

	$SaldoFinalC = $cargoT['cargo'];
	$SaldoFinalA = $abonoT['abono'];
	$SaldoFinalS = $myrow2['saldo'] + $SaldoInicial1['saldo'];

		echo "<TR height=20 bgcolor=#0D91EE>";
		echo "<TD colspan=5 align=left><font face='Arial Narrow' size='2' color='#000000'><b>SALDO FINAL</b></font></TD>";
		echo "<TD colspan=2 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";
		echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;" . number_format($SaldoFinalC,2) . "</b></font></TD>";
                echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;" . number_format($SaldoFinalA,2) . "</b></font></TD>";

		if ($saldo < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$&nbsp;" . number_format($saldo,2) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$&nbsp;" . number_format($saldo,2) . "</b></font></TD>";
		}

		echo "<TD colspan=1 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";

		if ($saldoConfirmado < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$&nbsp;" . number_format($saldoConfirmado,2) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$&nbsp;" . number_format($saldoConfirmado,2) . "</b></font></TD>";
		}

		echo "<TD colspan=2 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";

        	echo "</TR>";

		echo "<TR height=20 bgcolor=#0D91EE>";
		echo "<TD colspan=5 align=left><font face='Arial Narrow' size='2' color='#000000'><b>SALDO PENDIENTE</b></font></TD>";
		echo "<TD colspan=2 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";
		echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>&nbsp;</b></font></TD>";
                echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>&nbsp;</b></font></TD>";

		if (($saldo-$saldoConfirmado) < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$&nbsp;" . number_format($saldo-$saldoConfirmado,2) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$&nbsp;" . number_format($saldo-$saldoConfirmado,2) . "</b></font></TD>";
		}

		echo "<TD colspan=1 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";

		echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>&nbsp;</b></font></TD>";

		echo "<TD colspan=2 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";

        	echo "</TR></TABLE>";

/*
$pruba = 'hola como stan /r/n espero /r/n que super bn /r/n';
$bodytag = str_replace("/r/n", " ", ".$pruba.");

echo $bodytag;
 */
//include('includes/footer.inc');
?>
