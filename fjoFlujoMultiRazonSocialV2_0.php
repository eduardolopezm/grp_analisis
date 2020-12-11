
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

/*
desarrollo- 10/OCTUBRE/2011 - Agregue tipos de movimientos en alta.
 
desarrollo- 21/JULIO/2011  -  Adicione funcion para poder copiar la seleccion de partidas a la siguiente semana o a el siguiente mes !
				Esto para agilizar las proyecciones para meses futuros...
				
desarrollo- 11/JULIO/2011  -  Cambie la seleccion de empresas a checkbox para poder omitir o seleccionar varias.
desarrollo- 11/JULIO/2011  -  Correccion de errores menores, no funcionaba actualizacion de siguiente semana y no desplegaba bien saldo inicial despues de hacer actualizaciones.
				Ahora se puede modificar directamente la descripcion del concepto asi como la referencia.
 
 ARCHIVO CREADO POR: GONZALO ALVAREZ ZERCERO
 FECHA DE MODIFICACION: 01-MARZO-2011
 
 
 
 Ejecutar este SQL para agregar columna de fecha de promesa para CXC
 

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `Movimientos`
-- ----------------------------
DROP TABLE IF EXISTS `Movimientos`;
CREATE TABLE `Movimientos` (
  `u_movimiento` int(11) NOT NULL auto_increment,
  `u_empresa` int(11) default NULL,
  `dia` int(11) default NULL,
  `mes` int(11) default NULL,
  `anio` int(11) default NULL,
  `concepto` varchar(100) default NULL,
  `descripcion` text,
  `cargo` double(18,2) default NULL,
  `civa` double(18,2) default NULL,
  `cretencion` double(18,2) default NULL,
  `abono` double(18,2) default NULL,
  `aiva` double(18,2) default NULL,
  `aretencion` double(18,2) default NULL,
  `referencia` varchar(50) default NULL,
  `prioridad` int(11) default NULL,
  `u_banco` int(11) default NULL,
  `u_movimiento_rec` int(11) default NULL,
  `confirmado` int(11) default NULL,
  `UserId` int(11) default NULL,
  `TipoMovimientoId` int(11) default NULL,
  `estimado` int(11) default NULL,
  `convenio` int(11) default NULL,
  `IVA` int(11) default NULL,
  `vencimiento` datetime default NULL,
  `u_entidad` int(11) default NULL,
  `u_cajaChica` int(11) default NULL,
  `periodo_dev` int(11) default NULL,
  `dev_mes` int(11) default NULL,
  `dev_anio` int(11) default NULL,
  `erp` int(11) default NULL,
  `fecha` date default NULL,
  `activo` int(11) NOT NULL default '1',
  `grupo_contable` varchar(60) NOT NULL default '0',
  PRIMARY KEY  (`u_movimiento`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;


ALTER TABLE `debtortrans` ADD COLUMN `sent` int(11) NOT NULL AFTER `discountpercent`;

ALTER TABLE `debtortrans`
ADD COLUMN `duedate`  date NULL AFTER `sent`;

ALTER TABLE `debtortrans`
ADD COLUMN `priority`  int DEFAULT 5 AFTER `duedate`;

ALTER TABLE `debtortrans`
ADD COLUMN `activo` int(11) NOT NULL default '1' AFTER `priority`;



ALTER TABLE `supptrans` ADD COLUMN `sent` int(11) NOT NULL AFTER `lasttrandate`;

ALTER TABLE `supptrans`
ADD COLUMN `priority`  int DEFAULT 5 AFTER `sent`;

ALTER TABLE `supptrans`
ADD COLUMN `activo` int(11) NOT NULL default '1' AFTER `priority`;



PARA MIGRAR DEL FLUJO ANTERIOR...

update Movimientos
set u_empresa = 1,
	descripcion = concepto,
	fecha = CONCAT(anio,'-',mes,'-',dia)
where u_empresa = 222


update Movimientos
set confirmado = 2
where confirmado = 1;

update Movimientos
set confirmado = 1
where confirmado = 0;

update Movimientos
set confirmado = 0
where confirmado = 2;

update Movimientos
set activo = 0
where estimado = 1;

update Movimientos JOIN bancosFlujo ON Movimientos.u_banco = bancosFlujo.u_banco
set Movimientos.u_banco = bancosFlujo.Icono

 
*/

$funcion=2050;

$PageSecurity = 3;
include('includes/session.inc');

$title = _('Flujo de Efectivo Multi-Razone Social');

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');



echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';


/* ACTUALIZA A LA VERSION 2.0 */
/*$sql="update `sec_functions` set `url`='fjoFlujoV2_0.php' where `url`='fjoFlujoV2_0.php' ";
$ErrMsg = _('The authentication details cannot be deleted because');
$Result=DB_query($sql,$db,$ErrMsg);*/
	
 /* OBTENGO FECHAS*/

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



if (isset($_POST['Alta'])) {
		
	$SQL = "INSERT INTO Movimientos (
				u_empresa,
				dia,
				mes,
				anio,
				concepto,
				descripcion,
				u_banco,
				cargo,
				abono,
				IVA,
				prioridad,
				referencia,
				periodo_dev,
				erp,
				TipoMovimientoId,
				estimado,
				fecha,
				grupo_contable,
				confirmado,
				activo,
				u_entidad
				)
			VALUES( '". $_POST['legalid'] . "',
				'". $_POST['FromDia'] ."',
				'". $_POST['FromMes1'] ."',
				'". $_POST['FromYear1'] ."',
				'". $_POST['Concepto'] ."',
				'". $_POST['Concepto'] ."',
				'". $_POST['BankAccount2'] ."',
				'". $_POST['Cargo'] ."',
				'". $_POST['abono'] ."',
				$iva,
				'5',
				'". $_POST['factura'] ."',
				'". $_POST['periodoxdevengar'] ."',
				'0',
				'". $_POST['tipoMovimiento'] ."',
				'0',
				'". $_POST['FromYear1'] ."/". trim($_POST['FromMes1']) ."/". trim($_POST['FromDia']) ."',
				'".$_POST['grupo_contable']."',
				1,1,
				'".$_POST['entidadNegocio']."'
				)";
	$result = DB_query($SQL,$db);
	
	//echo $SQL;
	
}

if (isset($_GET['Delete'])) {
	$sql='DELETE FROM Movimientos
		WHERE u_movimiento ='.$_POST['u_movimiento'].'';
	$ErrMsg = _('The authentication details cannot be deleted because');
	$Result=DB_query($sql,$db,$ErrMsg);
	//echo $sql;
}

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

if(isset($_POST['PrintPDF'])){
      
        echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/fjoFlujoImpresion.php?&thislegalid=".trim($thislegalid)."&BankAccount=".trim($_POST['BankAccount'])."&FromMes=".trim($_POST['FromMes'])."&FromYear=".trim($_POST['FromYear'])."'>";
	
	include('includes/footer.inc');
	exit;
	
}

/* EJECUTA TODAS LAS OPERACIONES DE BD */
include('includes/MiFlujoHeader.inc');

echo "<form name='FDatosA' id='SubFrm' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";

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
			if (strpos($thislegalid,$myrow["legalid"]) > 0)
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
//echo "BANCO".$SQL;
	
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
	 echo '<td  style="text-align:right"><b>' . _('X Mes de Consulta:') . '</b></td>';				    
	 echo '<td><select Name="FromMes">';
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
	 
	 
	
	echo '<tr><td><br></td><td>';
	echo '</td></tr>';
	 
	echo '<tr><td style="vertical-align:top"  style="text-align:right"><b>'._('DATOS ERP:').'</b><td>';	
	
	if (isset($_POST['ERPCXC'])){
		echo '<input type="checkbox" name="ERPCXC" checked>' . _('Desplegar Movimientos X Cobrar ERP') . '<br>';
	} else {
		echo '<input type="checkbox" name="ERPCXC">' . _('Desplegar Movimientos X Cobrar ERP') . '<br>';
	}
	echo '</td></tr>';
	/*************************************/
	
	echo '<tr><td style="vertical-align:top"><td>';	
	
	if (isset($_POST['ERPCXP'])){
		echo '<input type="checkbox" name="ERPCXP" checked>' . _('Desplegar Movimientos X Pagar ERP') . '<br>';
	} else {
		echo '<input type="checkbox" name="ERPCXP">' . _('Desplegar Movimientos X Pagar ERP') . '<br>';
	}
	echo '</td></tr>';
	/*************************************/
	
	
		
	echo '</table>
		<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Consulta Flujo') . '">&nbsp;&nbsp;';
		
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
		echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#A00000'><b>$&nbsp;". number_format($SaldoInicial1['saldo'],0). "</b></font></TD>
			<TD colspan=1 style='background-color:#0174DF;'></TD>";
	} else {
	  	echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;". number_format($SaldoInicial1['saldo'],0). "</b></font></TD>
			<TD colspan=1 style='background-color:#0174DF;'></TD>";
	}
	
	if ($SaldoInicial1_CONFIRMADO['saldo'] < 0) {
		echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#A00000'><b>$&nbsp;". number_format($SaldoInicial1_CONFIRMADO['saldo'],0). "</b></font></TD>
			<TD colspan=2 style='background-color:#0174DF;'></TD>";
	} else {
	  	echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;". number_format($SaldoInicial1_CONFIRMADO['saldo'],0). "</b></font></TD>
			<TD colspan=2 style='background-color:#0174DF;'></TD>";
	}
	echo "</TR>";
	
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
		AND ((M.u_banco = " . $_POST['BankAccount'] ."
		AND ". $_POST['BankAccount']." > 0 )  or ". $_POST['BankAccount'] ." = 0)
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
			usrEntidades.Nombre as nombreEntidad
		FROM	Movimientos LEFT JOIN legalbusinessunit	ON Movimientos.u_empresa = legalbusinessunit.legalid
					LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
					LEFT JOIN accountgroups ON Movimientos.grupo_contable = accountgroups.groupname
					LEFT JOIN usrTipoMovimiento ON usrTipoMovimiento.TipoMovimientoId = Movimientos.TipoMovimientoId
					LEFT JOIN usrEntidades ON Movimientos.u_entidad = usrEntidades.u_entidad
		WHERE mes = '". $FromMes ."' AND anio = '". $FromYear ."'
			and (Movimientos.u_empresa in (". $thislegalid ."))
			and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0) AND erp = 0 ";
			
		
		
		/* MOVIMIENTOS DE CXC */
	//echo $SQL;	
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
	
	$SQL = $SQL . " ORDER BY dia,prioridad,abono desc,cargo desc";
	
	$result = DB_query($SQL,$db);
	//ECHO $SQL;
	
	/* AREGLO PARA DESPLEGAR EL MES CON TEXTO EN VES DE NUMERO */
	$friendlymes = array(1=>"Ene",2=>"Feb",3=>"Mar",4=>"Abr",5=>"May",6=>"Jun",7=>"Jul",8=>"Ago",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dic");
			
	
	$SaldoInicial = $SaldoInicial1['saldo'];
	$SaldoInicialConfirmado = $SaldoInicial1_CONFIRMADO['saldo'];
	
	$I = 0;
	$semanant='';
	while($myrow = DB_fetch_array($result)){
		$I ++;
	      if($semanant != $myrow['sem']){
			if($semanant != ''){
				$sql = "select sum(abono) as abono
				from Movimientos
				where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1
					and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
					and (Movimientos.u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
					and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0)
					AND erp = 0 ";
				
				$ResultS2 = DB_query($sql,$db);
				$abonoT2 = DB_fetch_array($ResultS2);
				//echo $sql;
				$sql = "select sum(cargo) as cargo
				from Movimientos
				where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."'  and activo = 1
					and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
					and (Movimientos.u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
					and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0)
					AND erp = 0 ";
				
				$ResultS2 = DB_query($sql,$db);
				$cargoT2 = DB_fetch_array($ResultS2);
					
				
				$SaldoFinalC2 = $cargoT2['cargo'];
				$SaldoFinalA2 = $abonoT2['abono'];
				
						
				echo "<TR height=20 bgcolor=#A9D0F5>";
				echo "<TD colspan=5 align=left style='font-size:11px;font-weight:bold;'><b>SALDO FINAL DE  SEMANA".'_'.$semanant."</b></TD>";
				echo "<TD colspan=2 align=center style='font-size:11px;font-weight:bold;'><b></b></TD>";
				echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;'><b>$&nbsp;" . number_format($SaldoFinalC2,0) . "</b></TD>";
				echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;'><b>$&nbsp;" . number_format($SaldoFinalA2,0) . "</b></TD>";
				if ($saldo < 0 ){
					echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#A01010' align=right><b>$&nbsp;" . number_format($saldo,0) . "</b></TD>";
				}else{
					echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#101010' align=right><b>$&nbsp;" . number_format($saldo,0) . "</b></TD>";
				}
				echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>X</td>";
				
				if ($saldoConfirmado < 0 ){
					echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#A01010' align=right><b>$&nbsp;" . number_format($saldoConfirmado,0) . "</b></TD>";
				}else{
					echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#101010' align=right><b>$&nbsp;" . number_format($saldoConfirmado,0) . "</b></TD>";
				}
				
				echo "</TR>";
			}
			$semanant = $myrow['sem'];
		
		}
		if($myrow['confirmado'] == '0'){
			$borrar = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Delete=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=eliminaMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
					<img alt='borrar' border=0 src='".$rootpath."/css/flujo/flecha_borrar.gif' width=5 height=10'>";
			$modificar = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Edit=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=pendMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
					<img alt='modificar' border=0 src='".$rootpath."/css/flujo/flecha_lock.gif' width=5 height=10'>";	
			$bloquear = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Block=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=confMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
					<img alt='bloquear' border=0 src='".$rootpath."/css/flujo/flecha_unlock.gif' width=5 height=10'>";
			$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> ";	
			$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'>";
				
			$check = "<INPUT type=checkbox id='chk".$I."' name='selMovimiento[]' value='".$myrow['u_movimiento']."')'>&nbsp;&nbsp;&nbsp;";
			
			
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
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['dia'].' '. $friendlymes[abs($_POST['FromMes'])] .' '. $_POST['FromYear'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['sem'] ."</td><td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td style='font-size:9px;font-weight:bold;'>". $myrow['referencia'] ."</td>";
			echo 	"<td style='font-size:9px;font-weight:bold;'>".$myrow['nombreEntidad']."</td>";
			
			
			
			
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			}else{
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right>&nbsp;</td>";
			}
			
			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			}
			else{
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right>&nbsp;</td>";
			}
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,0) ."</td>";
			
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			    
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $myrow['prioridad'] ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			
			$bankAccount = isset($_POST['BankAccount']) ? $_POST['BankAccount'] : '';
			if($bankAccount == '*') {
				echo "<td nowrap style='font-size:9px;font-weight:bold;'>X ASIGNAR</td>";
			} else {
				echo "<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['bankaccountname'] ."</td>";
			}
			
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
			$ligajs = "fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Delete=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=eliminaMovimiento&u_movimiento=" . $myrow['u_movimiento'];
			
			$borrar = "<a href='javascript:confirmation(\"".$ligajs."\")'>
					<img alt='borrar' border=0 src='".$rootpath."/css/flujo/flecha_borrar.gif' width=5 height=10'></a>&nbsp;";
			$modificar = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Edit=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=pendMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
					<img alt='modificar' border=0 src='".$rootpath."/css/flujo/flecha_lock.gif' width=5 height=10'></a>";	
			$bloquear = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Block=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=confMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
					<img alt='bloquear' border=0 src='".$rootpath."/css/flujo/flecha_unlock.gif' width=5 height=10'></a>";
			$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";	
			$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";
			$antdia =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorDia&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='dia anterior' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
			$sigdia = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteDia&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='dia siguiente' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antsem =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorSemana&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
						
			$sigsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteSemana&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='siguiente semana' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";
			
			$antmes =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorMes&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mes anterior' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
			$sigmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteMes&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mes siguiente' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";
			$antanio =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorAnio&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='año anterior' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
			$siganio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteAnio&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='año siguiente' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";				
			$check = "<INPUT type=checkbox id='chk".$I."' name='selMovimiento[]' value='".$myrow['u_movimiento']."')'>&nbsp;";
			
			$modificarDatos = "<a target='_blank' href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Edit=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=EditarMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
					</a>";	
			
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
			echo 	"<td align=center nowrap style='font-size:9px;font-weight:bold;'>". $antdia .''. $myrow['dia'] .''. $sigdia .''. $antmes .''. $friendlymes[abs($_POST['FromMes'])]
							.''. $sigmes .''. $antanio .''. $_POST['FromYear'] .''. $siganio ."</td>";
			
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap><input STYLE='text-align:left' type='text' size='40' value='". $myrow['concepto'] ."' onchange='seleccionaCheckBox(".$I.")' name='Concepto_".$myrow['u_movimiento']."'></td>";
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td><input STYLE='text-align:left' type='text' size='7' value='". $myrow['referencia'] ."' onchange='seleccionaCheckBox(".$I.")' name='Ref_".$myrow['u_movimiento']."'></td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>".$myrow['nombreEntidad']."</td>";
			
			if ($myrow['cargo'] != 0)
			
			    echo "<td><input STYLE='text-align:right' class=number type='text' size='10' value='".number_format($myrow['cargo'],0)."' onchange='seleccionaCheckBox(".$I.")' name='Cargo_".$myrow['u_movimiento']."'></td>";
			    
			    //echo 	"<td><p align=right>$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			else
			    echo "<td><input STYLE='text-align:right' class=number type='text' size='10' value='' onchange='seleccionaCheckBox(".$I.")' name='Cargo_".$myrow['u_movimiento']."'o></td>";
			    //echo 	"<td><p align=right>&nbsp;</td>";
			
			if ($myrow['abono'] != 0)
			    echo "<td><input STYLE='text-align:right' class=number type='text' size='10' value='".number_format($myrow['abono'],0)."' onchange='seleccionaCheckBox(".$I.")' name='Abono_".$myrow['u_movimiento']."'></td>";
			    
			    //echo 	"<td style='color:green;'><p align=right>$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			else
			    echo "<td><input STYLE='text-align:right' class=number type='text' size='10' value='' onchange='seleccionaCheckBox(".$I.")' name='Abono_".$myrow['u_movimiento']."'></td>";
			    //echo 	"<td><p align=right>&nbsp;</td>";
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			//echo 	"<td><p align=right>$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:red;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			    
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $prioridadmas .''. $myrow['prioridad'] .''. $prioridadmenos ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:gray;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:#45AAAA;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			    
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['bankaccountname'] ."</td>";
			
			
			/************************************/
			/* SELECCION GRUPO CONTABLE */
			
			echo 	"<td nowrap>";
			
			echo 	"<select name='tipoMovimiento_".$myrow['u_movimiento']."'>";
			
			$SQL = "select distinct tipomovimientoid, descripcion, categoria
				  from usrTipoMovimiento
				  where (legalid in (" . $thislegalid . "))
				  order by categoria, Orden";		
		
			$resultGC=DB_query($SQL,$db);
			echo $SQL;
			/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
			echo "<option selected value='0'>sin seleccion...</option>";
			
			while ($myrowGC=DB_fetch_array($resultGC)){
				if ($myrow["CategoriaId"]==$myrowGC["tipomovimientoid"]){
					echo '<option selected value="' . $myrowGC['tipomovimientoid'] . '">' . $myrowGC['categoria'] .'> '.$myrowGC['descripcion'].'</option>';
				} else {
					echo '<option value="' . $myrowGC['tipomovimientoid'] . '">' . $myrowGC['categoria'] .'> '.$myrowGC['descripcion'].'</option>';
				}
			}
			echo '</select>';
			echo "</td>";
			/************************************/
			
			echo "</tr>";
			
			$SaldoInicial = $saldo;
			$SaldoInicialConfirmado = $saldoConfirmado;
			
			if($myrow['activo'] == '0'){
				$SaldoFinalS = $SaldoFinalS;
			} else {
				$SaldoFinalS = $SaldoFinalS + $SaldoInicial;
			}
			
		}elseif($myrow['confirmado'] == '2'){
			$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridadCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a> ";	
			$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridadCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antdia =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorDiaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
			$sigdia = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteDiaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			
			$antsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorSemanaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
						
			$sigsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteSemanaCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='siguiente semana' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			
			$antmes =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorMesCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteMesCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antanio =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorAnioCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$siganio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXC&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteAnioCXC&u_movimiento=" . $myrow['u_movimiento'] . "'>
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
			echo 	"<td align=center nowrap>". $antdia .''. $myrow['dia'] .''. $sigdia .''. $antmes .''. $friendlymes[abs($_POST['FromMes'])]
							.''. $sigmes .''. $antanio .''. $_POST['FromYear'] .''. $siganio ."</td>";
							
			echo 	"<td nowrap>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td  nowrap style='background-color:#FF9e9e' >". $myrow['referencia'] ."</td>";
			echo 	"<td>".$myrow['nombreEntidad']."</td>";
			
			
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			}else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}
			
			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			}
			else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,0) ."</td>";
			
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			    
			echo 	"<td><p align=center>". $prioridadmas .''. $myrow['prioridad'] .''. $prioridadmenos ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			    
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
			$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridadCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";	
			$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridadCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antdia =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorDiaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigdia = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteDiaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorSemanaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteSemanaCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='siguiente semana' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorMesCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$sigmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteMesCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
			$antanio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorAnioCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'>  </a>";
			$siganio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=CXP&legalid=" . $myrow['legalid'] . "&FromMes=" . $_POST['FromMes'] . "&FromYear=" . $_POST['FromYear'] . "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteAnioCXP&u_movimiento=" . $myrow['u_movimiento'] . "'>
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
			echo 	"<td align=center nowrap>". $antdia .''. $myrow['dia'] .''. $sigdia .''. $antmes .''. $friendlymes[abs($_POST['FromMes'])]
							.''. $sigmes .''. $antanio .''. $_POST['FromYear'] .''. $siganio ."</td>";
							
			echo 	"<td nowrap>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td  nowrap style='background-color:#FF9e9e' >". $myrow['referencia'] ."</td>";
			echo 	"<td>".$myrow['nombreEntidad']."</td>";
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['cargo'],0) ."</td>";
			}else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}
			
			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td><p align=right ".$colormonto.">$&nbsp;". number_format($myrow['abono'],0) ."</td>";
			}
			else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,0) ."</td>";
			
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldo,0) ."</b></td>";
			    
			echo 	"<td><p align=center>". $myrow['prioridad'] ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,0) ."</b></td>";
			    
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
			and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0)
			AND erp = 0";
		
		$ResultS2 = DB_query($sql,$db);
		$cargoT2 = DB_fetch_array($ResultS2);
			
		
		$SaldoFinalC2 = $cargoT2['cargo'];
		$SaldoFinalA2 = $abonoT2['abono'];
		
				
		echo "<TR height=20 bgcolor=#A9D0F5>";
		echo "<TD colspan=5 align=left style='font-size:11px;font-weight:bold;'><b>SALDO FINAL DE  SEMANA".'_'.$semanant."</b></TD>";
		echo "<TD colspan=2 align=center style='font-size:11px;font-weight:bold;'><b></b></TD>";
		echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;'><b>$&nbsp;" . number_format($SaldoFinalC2,0) . "</b></TD>";
		echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;'><b>$&nbsp;" . number_format($SaldoFinalA2,0) . "</b></TD>";
		if ($saldo < 0 ){
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#A01010' align=right><b>$&nbsp;" . number_format($saldo,0) . "</b></TD>";
		}else{
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#101010' align=right><b>$&nbsp;" . number_format($saldo,0) . "</b></TD>";
		}
		echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>X</td>";
		
		if ($saldoConfirmado < 0 ){
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#A01010' align=right><b>$&nbsp;" . number_format($saldoConfirmado,0) . "</b></TD>";
		}else{
			echo "<TD style='text-align:right' style='font-size:11px;font-weight:bold;color:#101010' align=right><b>$&nbsp;" . number_format($saldoConfirmado,0) . "</b></TD>";
		}
		
		echo "</TR>";
	      }
		  
	      }
	
	$sql = "select sum(cargo) as cargo
		from Movimientos
		where mes = '". $_POST['FromMes'] ."'
			and anio = '". $_POST['FromYear'] ."'
			and (u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
			and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0) and activo = 1
			AND erp = 0";
		
	$ResultS = DB_query($sql,$db);
	$cargoT = DB_fetch_array($ResultS);
	//echo $sql;
	
	$sql = "select sum(abono) as abono
		from Movimientos
		where mes = '". $_POST['FromMes'] ."'
			and anio = '". $_POST['FromYear'] ."'
			and (u_empresa in (". $thislegalid .")  or u_banco = '". $_POST['BankAccount'] ."')
			and (u_banco = '". $_POST['BankAccount'] ."' or ". $_POST['BankAccount'] ." =0) and activo = 1
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
		echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;" . number_format($SaldoFinalC,0) . "</b></font></TD>";
                echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;" . number_format($SaldoFinalA,0) . "</b></font></TD>";
		
		if ($saldo < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$&nbsp;" . number_format($saldo,0) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$&nbsp;" . number_format($saldo,0) . "</b></font></TD>";
		}
		
		echo "<TD colspan=1 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";
		
		if ($saldoConfirmado < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$&nbsp;" . number_format($saldoConfirmado,0) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$&nbsp;" . number_format($saldoConfirmado,0) . "</b></font></TD>";
		}
		
		echo "<TD colspan=2 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";
		
        	echo "</TR></TABLE>";
		
		echo "<TABLE valign=top CELLPADDING=0 CELLSPACING=0 border=0 width='100%' height='0'>";
		echo "<TR  valign=bottom height=20 bgcolor=#BDF7AB><TD align=left colspan=20><td>
			  <font face='Arial Narrow' size='2' color='#000000'>OPERACION:</td><td>";
				     
		echo "<SELECT name='Oper'>";
		echo "<OPTION value='cambiaMontos'>cambia montos</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='diaDirecto'>dia directo -></OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='anteriorDia'>dia anterior</OPTION>";
		echo "<OPTION value='siguienteDia'>dia siguiente</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='anteriorDia5'>5 dias menos</OPTION>";
		echo "<OPTION value='siguienteDia5'>5 dias mas</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='anteriorMes'>mes anterior</OPTION>";
		echo "<OPTION value='siguienteMes'>mes siguiente</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='anteriorAnio'>anio anterior</OPTION>";
		echo "<OPTION value='siguienteAnio'>anio siguiente</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='borraMovimiento'>Borra Movimientos</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='pendMovimiento'>Abre Modificaciones</OPTION>";
		echo "<OPTION value='confMovimiento'>Cierra Modificaciones</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='siguientePrioridad'>Siguiente Prioridad</OPTION>";
		echo "<OPTION value='anteriorPrioridad'>Anterior Prioridad</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";				
		echo "<OPTION value='inhabilita'>Inhabilitar</OPTION>";
		echo "<OPTION value='habilita'>Habilitar</OPTION>";
		echo "<OPTION value='nada'>--------------------</OPTION>";
		echo "<OPTION value='copynextweek'>Copiar Movimientos +1Semana</OPTION>";
		echo "<OPTION value='copynextmonth'>Copiar Movimientos +1Mes</OPTION>";
		//echo "<OPTION value='habilitaconvenio'>Habilitar Convenio</OPTION>";
		//echo "<OPTION value='deshabilitaconvenio'>DesHabilitar Convenio</OPTION>";
							
		echo "</SELECT>";
		echo "<INPUT name=diaDirecto value='".$FromDia."' size=2 maxsize=2>";
		echo "</td><td>";
		
				  	
		echo "<INPUT type=hidden name=arreglo value=1></td><td width=850>&nbsp;</td>";		  
		
		$SaldoFinalC = 0;
		$SaldoFinalA = 0;
		
	echo '</TR>';
	
	/************************************/
	/* SELECCION ENTIDAD */
	
	echo "<TR  valign=bottom height=20 bgcolor=#BDF7AB><TD align=left colspan=20><td nowrap>
			  <font face='Arial Narrow' size='2' color='#000000'>ENTIDAD NEGOCIOS:</td><td>";
	//echo "--> " . $thislegalid;
	echo 	"<select name='entidadNegocio1'>";
	
	
	$SQL = "select u_entidad, legalid, Nombre
		  from usrEntidades
		  where (legalid in (" . $thislegalid . "))
		  order by Nombre";		

	$resultGC=DB_query($SQL,$db);
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='*'>sin seleccion...</option>";
	
	while ($myrowGC=DB_fetch_array($resultGC)){
		echo '<option value="' . $myrowGC['u_entidad'] . '">' . $myrowGC['Nombre'] .'</option>';
	}
	echo '</select>';
	echo "</td><td>";
		/************************************/
		
		echo "</td><td width=850>&nbsp;</td>";
		
	echo '</TR>';
	/************************************/
	
		echo "<TR  valign=bottom height=20 bgcolor=#BDF7AB><TD align=left colspan=20><td nowrap>
			  <font face='Arial Narrow' size='2' color='#000000'>BANCO:</td><td>";
				     
		/************************************/
	
		/* SELECCIONA EL BANCO */
		
		$SQL = 'SELECT bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
			FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
			WHERE bankaccounts.accountcode=chartmaster.accountcode and
				bankaccounts.accountcode = tagsxbankaccounts.accountcode and
				tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
				sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
			GROUP BY bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode';
			
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
	
		echo '<select name="BankAccount_GLOBAL">';
		if (DB_num_rows($AccountsResults)==0){
			echo '</select>';
			prnMsg( _('No existen cuentas de cheques definidas aun') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('configurar cuentas de cheques') . '</a> ' . _('y las cuentas contables que estas afectaran'),'warn');
			include('includes/footer.inc');
			exit;
		} else {
			echo "<option selected value='*'>Cuenta de Cheques por asignar...</option>";
			while ($myrow=DB_fetch_array($AccountsResults)){
			    /*list the bank account names */
			    echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			}
			echo '</select>&nbsp;<INPUT type=submit name=instruccionGeneral value=PROCESAR></td><td>';
		}
		
		echo "</td><td width=850>&nbsp;</td>";
		
	echo '</TR></table><br>';
//}


	
	echo '<table align = "center">';
	echo "<th colspan='2'><font size=2 face='arial bold' color='black'>ALTA DE MOVIMIENTOS</th><br>";
	
	/* SELECCIONA EL RANGO DE FECHAS */

	echo '<tr>';
	 echo '<td>' . _('X Fecha:') . '</td>';				    
	 echo '<td><select Name="FromDia">';
		    $sql = "SELECT * FROM cat_Days";
		    $dias = DB_query($sql,$db,'','');
		    while ($myrowdia=DB_fetch_array($dias,$db)){
			$diabase=$myrowdia['DiaId'];
			if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
			    echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
			}else{
			    echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
			}
		    }
		   
		   echo '</select>';
		   
	// echo '<td>' . _('X Mes de Consulta:') . '</td>';				    
	 echo '<select Name="FromMes1">';
		   $sql = "SELECT * FROM cat_Months";
		   $Meses = DB_query($sql,$db);
		   while ($myrowMes=DB_fetch_array($Meses,$db)){
		       $Mesbase=$myrowMes['u_mes'];
		       if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
		       }else{
			   echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
		       }
		   }
		   
		   echo '</select>';
		   echo '&nbsp;<input name="FromYear1" type="text" size="4" value='.$FromYear.'>';
	   echo '</td>';
		 
	 echo '</tr>';
	 echo '<tr><td><br></td><td></tr>';
	
	/************************************/
	/* SELECCION DEL RAZON SOCIAL */
	
	echo '<tr><td>'._('X Razon Social:').'<td><select name="legalid">';	
	///Imprime las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
				 and t.legalid in (". $thislegalid .")
			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		

	$result=DB_query($SQL,$db);
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	//echo "<option selected value='0'>Todas las razones sociales...</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		}
	}
	echo '</select></td></tr>';
	/************************************/
	
	echo "<tr><td>" . _('Concepto') . ":</td><td>";
	echo '<textarea name=Concepto cols=40 rows=2></textarea>';
	 echo '<tr><td><br></td><td></tr>';
	
	/************************************/
	/* SELECCION DEL CATEGORIA DE PRODUCTOS */
	
	/************************************/
	
	/* SELECCIONA EL BANCO */
	
	$SQL = 'SELECT bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode
		FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
		WHERE bankaccounts.accountcode=chartmaster.accountcode and
			bankaccounts.accountcode = tagsxbankaccounts.accountcode and
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';
		
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
//echo $SQL;
	echo '<tr><td>' . _('Selecciona un Banco') . ':</td><td><select name="BankAccount2">';
	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td></tr></table><p>';
		prnMsg( _('No existen cuentas de cheques definidas aun') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('configurar cuentas de cheques') . '</a> ' . _('y las cuentas contables que estas afectaran'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		echo "<option selected value='*'>Cuenta de Cheques por asignar...</option>";
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
	}
	
	/* SELECCION DEL PERIODO A TRABAJAR */
	
	echo "<tr><td>" . _('Periodo X Devengar') . ":</td><td>";
	echo "<select name='periodoxdevengar'>";
	$SQL = "SELECT  periodno, lastdate_in_period from periods order by periodno desc";
        
	$currPeriod = $_POST['periodoxdevengar'];
        if (!isset($_POST['periodoxdevengar'])) {
            $currPeriod = Date('Y-m',CalcEarliestDispatchDate());
	}
	
	
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	echo "<option selected value='0'>Seleccione periodo...</option>";
	
	while ($myrow=DB_fetch_array($TransResult)) {
		  $SourceDate = strtotime($myrow['lastdate_in_period']);
		  if ($currPeriod == date('Y-m',$SourceDate)){
			echo "<option selected value='" . date('Y-m',$SourceDate) . "'>" . date('F Y',$SourceDate) . "</option>";	
		  }else{
			echo "<option value='" . date('Y-m',$SourceDate) . "'>" . date('F Y',$SourceDate) . "</option>";
		  }
	}
	 
	echo "</select>";
	echo "</td></tr>";
	/************************************/
	echo "<tr><td>" . _('No. Factura ó Cheque') . ":</td><td>";
	echo "<input type='text' size='12' name='factura'>";
	echo "</td></tr>";
	
	echo "<tr><td>" . _('Cargo') . ":</td><td>";
	echo "<input type='text' size='7' name=Cargo>";
	echo "</td></tr>";
	
	echo "<tr><td>" . _('Abono') . ":</td><td>";
	echo "<input type='text' size='7' name='abono'><br><br>";
	echo "</td></tr>";
	
	/************************************/
	/* SELECCION TIPO MOVIMIENTO */
	
	echo "<tr><td>" . _('Tipo Movimiento') . ":</td><td>";
	//echo "--> " . $thislegalid;
	echo 	"<select name='tipoMovimiento'>";
	
	
	$SQL = "select distinct tipomovimientoid, descripcion, categoria
		  from usrTipoMovimiento
		  where (legalid in (" . $thislegalid . "))
		  order by categoria, Orden";		

	$resultGC=DB_query($SQL,$db);
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='0'>sin seleccion...</option>";
	
	while ($myrowGC=DB_fetch_array($resultGC)){
		if ($myrow["tipomovimientoid"]==$myrowGC["tipomovimientoid"]){
			echo '<option selected value="' . $myrowGC['tipomovimientoid'] . '">' . $myrowGC['categoria'] .'> '.$myrowGC['descripcion'].'</option>';
		} else {
			echo '<option value="' . $myrowGC['tipomovimientoid'] . '">' . $myrowGC['categoria'] .'> '.$myrowGC['descripcion'].'</option>';
		}
	}
	echo '</select>';
	echo "</td></tr>";
	/************************************/
	
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
	echo "<option selected value='0'>sin seleccion...</option>";
	
	while ($myrowGC=DB_fetch_array($resultGC)){
		echo '<option value="' . $myrowGC['u_entidad'] . '">' . $myrowGC['Nombre'] .'</option>';
	}
	echo '</select>';
	echo "</td></tr>";
	/************************************/
	
	/*
	echo "<tr><td>" . _('Desglosar el IVA') . ":</td><td>";
	echo "<input type=checkbox name=LlevaIva value=1>";
	
	echo "<tr><td>" . _('Honorarios-Arrendamiento') . ":</td><td>";
	echo "<input type=checkbox name=LlevaISR value=1>";
	*/
	
	echo "<tr><td><br></td><td>";
	echo "</td></tr>";
	
	echo "<tr><td>" . _('Operacion ->') . ":</td><td>";
	echo '<input tabindex="7" type=submit name="Alta" value="' . _('Procesar Alta') . '">';
	echo "</td></tr>";



/*
$pruba = 'hola como stan /r/n espero /r/n que super bn /r/n';
$bodytag = str_replace("/r/n", " ", ".$pruba.");

echo $bodytag;
 */
include('includes/footer.inc');
?>