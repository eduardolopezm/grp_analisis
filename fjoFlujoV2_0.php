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
ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);

*/

$funcion=2010;

$PageSecurity = 3;
include('includes/session.inc');

$title = _('Administracion de Flujo de Efectivo');

if(!isset($_POST['excel'])) {
	include('includes/header.inc');
}

include('includes/SQL_CommonFunctions.inc');


if(!isset($_POST['excel'])) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';
}

/* ACTUALIZA A LA VERSION 2.0 */
/*$sql="update `sec_functions` set `url`='fjoFlujoV2_0.php' where `url`='fjoFlujoV2_0.php' ";
$ErrMsg = _('The authentication details cannot be deleted because');
$Result=DB_query($sql,$db,$ErrMsg);*/
	
 /* OBTENGO FECHAS*/

if (isset($_POST['FromYear1'])) {
	$_POST['FromYear'] = $_POST['FromYear1'];
}

if (isset($_GET['FromYear'])) {
	$FromYear=$_GET['FromYear'];
	$_POST['FromYear']=$_GET['FromYear'];
	
} elseif (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
} else {
	$FromYear=date('Y');
}

if (isset($_POST['BankAccount2']))
	$_POST['BankAccount'] = $_POST['BankAccount2'];

if (isset($_GET['FromMes'])) {
	$FromMes=$_GET['FromMes'];
}elseif (isset($_POST['FromMes1'])) {
	$FromMes=$_POST['FromMes1'];
}elseif (isset($_POST['FromMes'])) {
	$FromMes=$_POST['FromMes'];
} else {
        $FromMes=date('m');
}

if (isset($_GET['CategoryId'] )) {
	$_POST['CategoryId'] = 	$_GET['CategoryId'];
} elseif (isset($_POST['CategoryId'])) {
	$_GET['CategoryId'] = $_POST['CategoryId'];
} else {
	$_POST['CategoryId'] = '*';
}

if (isset($_GET['subCategoriaSel'] )) {
	$_POST['subCategoriaSel'] = $_GET['subCategoriaSel'];
} elseif (isset($_POST['subCategoriaSel'])) {
	$_GET['subCategoriaSel'] = $_POST['subCategoriaSel'];
} else {
	$_POST['subCategoriaSel'] = '*';
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

if (!isset($_POST['unidadnegocio'])){
	$_POST['unidadnegocio'] = 0;
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

if (isset($_GET['thislegalid'])){
	$_POST['legalid'] = $_GET['thislegalid'];
} 

$InputError = 0;
if (isset($_POST['Alta'])) {
	if(empty($_POST['unidadnegocioalta'])) {
		prnMsg(_('La unidad de negocio en el alta de movimientos no est� definida'), 'error');
		$InputError = 1;
	}
}

if (isset($_POST['Alta']) && $InputError == 0) {
		
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
				tagref,
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
				'".$_POST['unidadnegocioalta']."',
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

$thislegalid = '';

if (isset($_GET['legalid']) ) {
	$thislegalid = $_GET['legalid'];
} else {
	$thislegalid = $_POST['legalid'];
}

if (trim($thislegalid) == '') {
	$thislegalid = '-1';
}

if(isset($_POST['PrintPDF'])){
      
        echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/fjoFlujoImpresion.php?&thislegalid=".trim($thislegalid)."&BankAccount=".trim($_POST['BankAccount'])."&FromMes=".trim($_POST['FromMes'])."&FromYear=".trim($_POST['FromYear'])."'>";
	
	include('includes/footer_Index.inc');
	exit;
	
}

/* EJECUTA TODAS LAS OPERACIONES DE BD */
include('includes/MiFlujoHeader.inc');

	if(!isset($_POST['excel'])) {
		
		echo "<form name='FDatosA' id='SubFrm' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	
		/************************************/
		/* SELECCION DEL RAZON SOCIAL       */
		
		echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
		echo '<table border=0 width=1000>';
		
		/************************************/
		/* SELECCION DEL RAZON SOCIAL */
		
		echo '<tr><td style="text-align:right"><b>'._('X Razon Social:').'</b></td><td><select name="legalid">';	
		///Imprime las razones sociales
		$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
		$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
		$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
		$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
					 
				  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		
	
		$result=DB_query($SQL,$db);
		
		/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
		echo "<option selected value='0'>Selecciona una razon social...</option>";
		
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
				echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
			} else {
				echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
			}
		}
		echo '</select></td></tr>';
		/************************************/
	
		echo '<tr>';
		echo '<td style="text-align:right"><b>' . _('X Area') . ':</td>';
		$SQL=" SELECT areas.areacode,areas.areadescription
				       FROM areas
					   INNER JOIN regions ON areas.regioncode = regions.regioncode
				       ORDER BY areadescription";	   
		$resultarea = DB_query($SQL,$db);
		echo "<td><select name='area' style='font-size:8pt'>";
		echo '<option selected value="">Todas</option>';
		while ($myrowarea = DB_fetch_array($resultarea)) {
			if ($_POST['area']==$myrowarea['areacode']){
				echo '<option selected value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
			} else {
				echo '<option  value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
			}
		}
		echo '</select>&nbsp;&nbsp;';
		echo '<input type="submit" value="->" name="btnArea"></td>';
		echo '</tr>';
		$wcond="";
		if ($_POST['area'])
			$wcond = "AND t.areacode = '".$_POST['area']."'";
	
		
		/************************************/
		/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
		echo "<tr><td style='text-align:right'><b>" . _('X Unidad de Negocio') . ":</b></td><td>";
		echo "<select name='unidadnegocio'>";
		$SQL = "SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription, t.tagdescription ";//areas.areacode, areas.areadescription";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
			$SQL = $SQL .	" $wcond and u.userid = '" . $_SESSION['UserID'] . "'  and (t.legalid = '".$_POST['legalid']."' OR '0'= '".$_POST['legalid']."' )
					ORDER BY t.tagdescription, areas.areacode";
	
		$ErrMsg = _('No transactions were returned by the SQL because');
		$TransResult = DB_query($SQL,$db,$ErrMsg);
		
		echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
		
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['tagref'] == $_POST['unidadnegocio']){
				echo "<option selected value='" . $myrow['tagref'] . "'>". $myrow['tagref']."-".$myrow['tagdescription'] . "</option>";	
			}else{
				echo "<option value='" . $myrow['tagref'] ."'>".$myrow['tagref']."-".$myrow['tagdescription'] . "</option>";
			}
		}
		 
		echo "</select>";
		echo "</td></tr>";
		
		echo '<tr><td><br></td><td>&nbsp;';
		echo '</td></tr>';
	
		/* SELECCIONA EL BANCO */
	
		echo '<tr><td style="text-align:right"><b>' . _('X Cuenta de Cheques') . ':</b></td><td>
			<select name="BankAccount">';
		$warea="";
		if ($_POST['area']!="")
			$warea = " and areas.areacode = '".$_POST['area']."'";
			
		$SQL = "SELECT bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
			FROM bankaccounts, sec_unegsxuser, tagsxbankaccounts
			JOIN tags ON tagsxbankaccounts.tagref = tags.tagref
			JOIN areas ON tags.areacode = areas.areacode
			WHERE bankaccounts.accountcode = tagsxbankaccounts.accountcode and
				tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
				sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and
				(tags.legalid = '". $_POST['legalid'] ."' OR '".$_POST['legalid']."' = '0') and
				(tags.tagref = '". $_POST['unidadnegocio'] ."' OR '".$_POST['unidadnegocio']."' = '0')
				$warea
			GROUP BY bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode";
			
			
		$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
		$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
		$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	
		
			echo "<option selected value='*'>Todas las cuentas de cheques...</option>";
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
		
		
		/* SELECCIONA LA CATEGORIA */
	
		echo '<tr><td style="text-align:right"><b>' . _('X Categoria') . ':</b></td><td>
			<select name="CategoryId">';
		
		$SQL = "select fjoCategory.cat_name, fjoCategory.cat_id
					  from fjoCategory 
					  where (fjoCategory.legalid =" . $_POST['legalid'] ." OR '".$_POST['legalid']."' = '0')
					  order by fjoCategory.Order";	
			
		$ErrMsg = _('Las Categorias no se pudieron recuperar porque');
		$DbgMsg = _('El SQL utilizado para recuperar las Categorias fue');
		
		echo "<option selected value='*'>Todas las categorias...</option>";
		$resultGC=DB_query($SQL,$db);
		
		while ($myrowGC=DB_fetch_array($resultGC)){
			if ($_POST["CategoryId"]==$myrowGC["cat_id"]){
				echo '<option selected value="' . $myrowGC['cat_id'] . '">' . $myrowGC['cat_name'] .'</option>';
			} else {
				echo '<option value="' . $myrowGC['cat_id'] . '">' . $myrowGC['cat_name'] . '</option>';
			}
		}
		echo '</select></td></tr>';
		
		
		echo '<tr><td style="text-align:right"><b>' . _('X Sub Categoria') . ':</b></td><td>
			<select name="subCategoriaSel">';
		
		$order = "fjoCategory.order, fjoSubCategory.order";
		if ($_SESSION['OrderByNameSubCategory']==1)
			$order = "fjoCategory.cat_name, fjoSubCategory.subcat_name";
		
		$SQL = "select fjoCategory.cat_name,  fjoSubCategory.subcat_name, fjoSubCategory.subcat_id
			  from fjoCategory JOIN fjoSubCategory ON fjoCategory.cat_id = fjoSubCategory.cat_id
			  where ((fjoCategory.legalid =" . $_POST['legalid'] ." OR '".$_POST['legalid']."' = '0')) AND
				(fjoCategory.cat_id ='". $_POST["CategoryId"]."' OR '*'='". $_POST["CategoryId"]."')
			  order by $order ";		
	
		$resultGC=DB_query($SQL,$db);
		
		/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
		
		
		echo "<option selected value='*'>Todas las Sub Categorias...</option>";
		
		if ($_POST["subCategoriaSel"] == 'null')
			echo "<option selected value='null'>Sin Sub-Categoria...</option>";
		else
			echo "<option value='null'>Sin Sub-Categoria...</option>";
			
		$catant = '';
		while ($myrowGC=DB_fetch_array($resultGC)){
			if ($catant != $myrowGC['cat_name']) {
				echo '<option value="novalida"> -------------------------------------------</option>';
				echo '<option value="novalida"> ******** ' . $myrowGC['cat_name'] .'</option>';
				$catant = $myrowGC['cat_name'];
			}
			if ($_POST["subCategoriaSel"]==$myrowGC["subcat_id"]){
				echo '<option selected value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
			} else {
				echo '<option value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
			}
		}
		echo '</select></td></tr>';
		/**********************************/
		
		
		//echo '<tr><td><br></td><td>';
		/*
		 echo 	"<select name='tipoMovimiento_".$myrow['u_movimiento']."'>";
				
				$SQL = "select fjoCategory.cat_name,  fjoSubCategory.subcat_name, fjoAccount.act_name, fjoAccount.acc_id
					  from fjoCategory JOIN fjoSubCategory ON fjoCategory.cat_id = fjoSubCategory.cat_id
						JOIN fjoAccount ON  fjoSubCategory.subcat_id = fjoAccount.subcat_id
					  where (fjoCategory.legalid in (" . $thislegalid . "))
					  order by fjoCategory.Order, fjoSubCategory.Order, fjoAccount.Order";		
			
				$resultGC=DB_query($SQL,$db);
				echo $SQL;
				
				echo "<option selected value='0'>X sin seleccion...</option>";
				
				while ($myrowGC=DB_fetch_array($resultGC)){
					if ($myrow["CategoriaId"]==$myrowGC["acc_id"]){
						echo '<option selected value="' . $myrowGC['acc_id'] . '">' . $myrowGC['cat_name'] .'> '.$myrowGC['subcat_name'].'> '.$myrowGC['act_name'].'</option>';
					} else {
						echo '<option value="' . $myrowGC['acc_id'] . '">' . $myrowGC['cat_name'] .'> '.$myrowGC['subcat_name'].'> '.$myrowGC['act_name'].'</option>';
					}
				}
				echo '</select>';
		*/
		//echo '</td></tr>';
		
		
		/* SELECCIONA EL RANGO DE FECHAS */
	
		echo '<tr>';
		 echo '<td  style="text-align:right"><b>' . _('X Mes de Consulta:') . '</b></td>';				    
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
			   
			   echo '</select>';
			   echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
		   echo '</td>';
			 
		 echo '</tr>';
		 echo '</tr>';
		
		echo '<tr><td><br></td><td>';
		echo '</td></tr>';
		 
		echo '</table>
			<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Consulta Flujo') . '">&nbsp;&nbsp;';
			
			echo '<input tabindex="8" type=submit name="excel" value="' . _('Exportar a Excel') . '" /><input tabindex="7" type=submit id="btn2" name="PrintPDF" value="' . _('Formato Impresion') . '"></div><br>';
			echo "</form>";
	}

	if(isset($_POST['excel'])) {
		header("Content-type: application/ms-excel");
		header("Content-Disposition: attachment; filename=Flujo.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
	}
	if(!isset($_POST['excel'])) {
	echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";

	echo '<input Name="FromMes" type=hidden value="'.$FromMes.'">';
	
	echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';
	}
	echo '<br><table cellspacing=0 border=1 bordercolor=white cellpadding=2 colspan="7">';
		/*desarrollo- Quite estos encabezados que ya no uso - 15/OCT/2011
		       <th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('T') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('S') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' color='#FFFF00'>" . _('V') . "</th>
			
			*/
		if(isset($_POST['excel'])) {
			$colorencabezado='';
			
		}else{
			$colorencabezado="color='#FFFF00'";
		}
		echo "<tr>	
			<th nowrap style='background-color:#150C67;'><b><font face='arial' size=1 ".$colorencabezado."><b>
			<img src='".$rootpath."/css/flujo/flecha_borrar.gif' width=5 height=10'>
			<img border=0 src='".$rootpath."/css/flujo/flecha_lock.gif' width=5 height=10'>
			<img border=0 src='".$rootpath."/css/flujo/flecha_unlock.gif' width=5 height=10'></b></th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Fecha') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Sem') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Concepto') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('ID') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Ref') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Ent') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado." nowrap>" . _('Cargo') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado." nowrap>" . _('Abono') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Saldo') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Prio') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Confirmado') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('Banco') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('SubCategoria') . "</th>
			<th style='background-color:#150C67;'><b><font size=2 face='arial' ".$colorencabezado.">" . _('U.Neg') . "</th>";
		echo "</tr>";
	
	$warea="";
	if ($_POST['area']!="")
		$warea = " and areas.areacode = '".$_POST['area']."'";

	$sql = "select sum(x.saldo) as saldo
	
	from (	
		SELECT sum(Movimientos.abono - Movimientos.cargo) as saldo
		FROM Movimientos LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
				 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
		                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
				 LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid AND /*tagsxbankaccounts.tagref = tags.tagref
				 AND */ Movimientos.tagref=tags.tagref
				 LEFT JOIN areas ON tags.areacode=areas.areacode
				 LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
				 LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
		WHERE (Movimientos.u_empresa in (". $thislegalid .")) and activo = 1 /* and tags.tagref is not null */
		and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
		$warea
		AND (bankaccounts.accountcode = '". $_POST['BankAccount'] ."' OR '". $_POST['BankAccount'] ."' = '*')
		AND DATE(Movimientos.fecha) < DATE('".$FromYear."-".trim($FromMes)."-01')
		AND erp = 0 ";
	//agregar saldo inicial para ver datos de cxc,cxp y banco contable
	
	// agrega movimientos de erp
				
	$sql=$sql." ) as x";
	//echo "<pre>$sql";
	//echo '<pre>'.$sql;
	$result = DB_query($sql,$db);
	$SaldoInicial1 = DB_fetch_array($result);
	$warea="";
	if ($_POST['area']!="")
		$warea = " and areas.areacode = '".$_POST['area']."'";
	
	$sql = "select sum(x.saldo) as saldo
	
	from (	SELECT sum(Movimientos.abono - Movimientos.cargo) as saldo
		FROM Movimientos LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
				 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
		                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
				 LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid AND /*tagsxbankaccounts.tagref = tags.tagref
				 AND */ Movimientos.tagref=tags.tagref
				 LEFT JOIN areas ON tags.areacode=areas.areacode
				 LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
				 LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
		WHERE (Movimientos.u_empresa in (". $thislegalid .")) and activo = 1 /* and tags.tagref is not null */
		and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
		$warea
		AND (bankaccounts.accountcode = '". $_POST['BankAccount'] ."' OR '". $_POST['BankAccount'] ."' = '*')
		AND DATE(Movimientos.fecha) < DATE('".$FromYear."-".trim($FromMes)."-01')
		AND erp = 0
		AND confirmado = 0 ";  

	
	$sql=$sql." ) as x";
	
	
	$result = DB_query($sql,$db);
	$SaldoInicial1_CONFIRMADO = DB_fetch_array($result);
	
	if(!isset($_POST['excel'])) {
		echo 	"<TR>
				<TD colspan=3 style='background-color:#0174DF;'></TD>
				<TD style='background-color:#0174DF;' nowrap><b><font face='Arial Narrow' size='2' color='#F8FB02'><b>SALDO INICIAL DEL MES:</b></font></TD>
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
				<TD colspan=3 style='background-color:#0174DF;'></TD>";
		} else {
		  	echo 	"<TD style='background-color:#0174DF;text-align:right'><b><font face='Arial Narrow' size='2' color='#000000'><b>$&nbsp;". number_format($SaldoInicial1_CONFIRMADO['saldo'],2). "</b></font></TD>
				<TD colspan=3 style='background-color:#0174DF;'></TD>";
		}
		echo "</TR>";
	}else{
		echo 	"<TR>
				<TD colspan=3 ></TD>
				<TD  nowrap><b><font face='Arial Narrow' size='2' ><b>SALDO INICIAL DEL MES:</b></font></TD>
				<TD colspan=5 ></TD>";
		
		if ($SaldoInicial1['saldo'] < 0) {
			echo 	"<TD style='text-align:right'><b><font face='Arial Narrow' size='2' ><b>$". number_format($SaldoInicial1['saldo'],2). "</b></font></TD>
				<TD colspan=1 ></TD>";
		} else {
			echo 	"<TD style='text-align:right'><b><font face='Arial Narrow' size='2' ><b>$". number_format($SaldoInicial1['saldo'],2). "</b></font></TD>
				<TD colspan=1 ></TD>";
		}
		
		if ($SaldoInicial1_CONFIRMADO['saldo'] < 0) {
			echo 	"<TD style='text-align:right'><b><font face='Arial Narrow' size='2' ><b>$". number_format($SaldoInicial1_CONFIRMADO['saldo'],2). "</b></font></TD>
				<TD colspan=3 ></TD>";
		} else {
			echo 	"<TD style='text-align:right'><b><font face='Arial Narrow' size='2' ><b>$". number_format($SaldoInicial1_CONFIRMADO['saldo'],2). "</b></font></TD>
				<TD colspan=3 ></TD>";
		}
		echo "</TR>";
		
	}
	
	/**************************************************************************/
	/* CORRIGE BASE DE DATOS CXP EN CASO DE FALTAR FECHAS ASIGNADAS           */
	$sql = "UPDATE supptrans
		  set duedate = trandate
		  WHERE duedate is null
		";
	$result = DB_query($sql,$db);
	
	$sql = "UPDATE supptrans
		  set promisedate = duedate
		  WHERE promisedate = '0000-00-00'
		";
	$result = DB_query($sql,$db);
	/* FIN DE CORRECCION BASE DE DATOS CXP EN CASO DE FALTAR FECHAS ASIGNADAS */
	/**************************************************************************/
	$warea="";
	if ($_POST['area']!="")
		$warea = " and areas.areacode = '".$_POST['area']."'";
	$sql = "select sum(x.saldo) as saldo,
	sum(x.saldo_confirmado) as saldo_confirmado
	from (	
	
	select  sum(M.abono - M.cargo) as saldo,
			sum(CASE WHEN (confirmado = 0) THEN (M.abono - M.cargo) ELSE 0 END) as saldo_confirmado
		from Movimientos M LEFT JOIN bankaccounts B ON M.u_banco = B.accountcode
						 LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = M.TipoMovimientoId
		                 /*LEFT JOIN tagsxbankaccounts ON M.u_banco = tagsxbankaccounts.accountcode*/
		                 LEFT JOIN legalbusinessunit O ON M.u_empresa = O.legalid
				 LEFT JOIN tags ON O.legalid = tags.legalid /* AND tagsxbankaccounts.tagref = tags.tagref*/
				 AND M.tagref=tags.tagref
				 JOIN areas ON tags.areacode = areas.areacode
		where (M.u_empresa in (". $thislegalid ."))
		and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
		$warea
		AND ((M.u_banco = '" . $_POST['BankAccount'] ."'
		AND '". $_POST['BankAccount']."' > '0' )  or '". $_POST['BankAccount'] ."' = '*')
		AND M.activo = 1 /*and tags.tagref is not null*/
		AND mes = '". $FromMes ."'
		AND anio = '". $FromYear ."'
		AND erp = 0
		";
		
		
	if ($_POST['subCategoriaSel'] == 'null') {
		$sql = $sql . "
			and (fjoSubCategory.subcat_id is null)
			AND M.u_empresa= O.legalid
			AND (M.u_empresa in (". $thislegalid .")) 
			AND ((M.u_banco = '" . $_POST['BankAccount'] ."'
			AND '". $_POST['BankAccount']."' > '0' )  or '". $_POST['BankAccount'] ."' = '*')
			AND M.activo = 1
			AND mes = '". $FromMes ."'
			AND anio = '". $FromYear ."'
			AND erp = 0
			";
	} else {
		$sql = $sql . "
			and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
			AND M.u_empresa= O.legalid
			AND (M.u_empresa in (". $thislegalid .")) 
			AND ((M.u_banco = '" . $_POST['BankAccount'] ."'
			AND '". $_POST['BankAccount']."' > '0' )  or '". $_POST['BankAccount'] ."' = '*')
			AND M.activo = 1
			AND mes = '". $FromMes ."'
			AND anio = '". $FromYear ."'
			AND erp = 0
			";
		
	}
	//agregar saldo inicial para ver datos de cxc,cxp y banco contable
	
	// agrega movimientos de erp
	
				
	$sql=$sql." ) as x";
	//echo "<pre>$sql";
	
	//echo $sql;
	$result = DB_query($sql,$db);
	$myrow2 = DB_fetch_array($result);
	
	$warea="";
	if ($_POST['area']!="")
		$warea = " and areas.areacode = '".$_POST['area']."'";
	
	$SQL = "SELECT distinct u_movimiento,
			concepto,
			Movimientos.descripcion,
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
			fjoSubCategory.subcat_name as Categoria,
			week(concat(anio,'-',mes,'-',dia),1) as sem,
			grupo_contable,
			activo,
			fjoSubCategory.subcat_id as CategoriaId,
			usrEntidades.Nombre as nombreEntidad,
			anio,
			mes,
			1 as tipoMovimiento,
			Movimientos.tagref,
			(select tagdescription from tags where tagref = Movimientos.tagref) as tagdescription  			
		FROM	Movimientos LEFT JOIN legalbusinessunit	ON Movimientos.u_empresa = legalbusinessunit.legalid
					/*JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
					LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid /*AND tagsxbankaccounts.tagref = tags.tagref*/
					LEFT JOIN areas ON tags.areacode = areas.areacode
					LEFT JOIN bankaccounts ON Movimientos.u_banco = bankaccounts.accountcode
					LEFT JOIN accountgroups ON Movimientos.grupo_contable = accountgroups.groupname
					LEFT JOIN usrTipoMovimiento ON usrTipoMovimiento.TipoMovimientoId = Movimientos.TipoMovimientoId
					LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
					LEFT JOIN usrEntidades ON Movimientos.u_entidad = usrEntidades.u_entidad
		WHERE mes = '". $FromMes ."' AND anio = '". $FromYear ."'
            /*and not tags.tagref is null*/
			and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
			$warea
			and (Movimientos.u_empresa in (". $thislegalid ."))
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*')
			and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
			
		if ($_POST['subCategoriaSel'] == 'null') {
			$SQL = $SQL . "
			and (fjoSubCategory.subcat_id is null)
			AND erp = 0 ";
		} else {
			$SQL = $SQL . "
			and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
			AND erp = 0 ";
		}
	
	$SQL = $SQL . " ORDER BY dia,prioridad,referencia,abono desc,cargo desc";
	
	$result = DB_query($SQL,$db);
	
	if($_SESSION['UserID'] == 'desarrollo') {
		//echo '<pre>'.$SQL;
	}
	
	/* AREGLO PARA DESPLEGAR EL MES CON TEXTO EN VES DE NUMERO */
	$friendlymes = array(1=>"Ene",2=>"Feb",3=>"Mar",4=>"Abr",5=>"May",6=>"Jun",7=>"Jul",8=>"Ago",9=>"Sep",10=>"Oct",11=>"Nov",12=>"Dic");
			
	
	$SaldoInicial = $SaldoInicial1['saldo'];
	$SaldoInicialConfirmado = $SaldoInicial1_CONFIRMADO['saldo'];
	
	$I = 0;
	$semanant='';
	$colorlinea = 0;
	$saldotmp = 0;
	$saldotmptotal = 0;

	while($myrow = DB_fetch_array($result)){
		$I ++;
	      if($semanant != $myrow['sem']){
			if($semanant != ''){
				$sql = "select sum(x.abono) as abono from (
				select sum(abono) as abono
				from Movimientos LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
				where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1
					and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
					and Movimientos.u_empresa in (". $thislegalid .")  
					and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*')
					and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
				
				if ($_POST['subCategoriaSel'] == 'null') {
					$sql = $sql . "
					and (fjoSubCategory.subcat_id is null)
					AND erp = 0 ";
				} else {
					$sql = $sql . "
					and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
					AND erp = 0 ";
				}
				
				// agrega movimientos de erp
				
				
				$sql=$sql." ) as x";
				//echo "<pre>$sql";
				$ResultS2 = DB_query($sql,$db);
				$abonoT2 = DB_fetch_array($ResultS2);
				
				$warea="";
				if ($_POST['area']!="")
					$warea = " and areas.areacode = '".$_POST['area']."'";
				
				$sql = "select sum(x.cargo) as cargo from (
							select sum(cargo) as cargo
							from Movimientos LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
										LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
							                 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
							                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
									 LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid /*AND tagsxbankaccounts.tagref = tags.tagref*/
									 AND Movimientos.tagref=tags.tagref
									 LEFT JOIN areas ON areas.areacode = tags.areacode
							where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1 /* and tags.tagref is not null*/
								and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
								$warea
								and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
								and Movimientos.u_empresa in (". $thislegalid .")  
								and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*')
								and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
						
					if ($_POST['subCategoriaSel'] == 'null') {
						$sql = $sql . "
						and (fjoSubCategory.subcat_id is null)
						AND erp = 0";
					} else {
						$sql = $sql . "
						and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
						AND erp = 0";
					}
				// agrega movimientos de erp
						
				$sql=$sql." ) as x";	
				//echo "<pre>$sql";
				$ResultS2 = DB_query($sql,$db);
				$cargoT2 = DB_fetch_array($ResultS2);
					
				
				$SaldoFinalC2 = $cargoT2['cargo'];
				$SaldoFinalA2 = $abonoT2['abono'];
				
				if(!isset($_POST['excel'])) {		
					echo "<TR height=20 bgcolor=#A9D0F5>";
					$espacio='&nbsp;';
				}else{
					echo "<TR height=20 >";
					$espacio='';
				}
				echo "<TD colspan=5 align=left style='font-size:9px;font-weight:bold;'><b>SALDO FINAL DE  SEMANA".'_'.$semanant."</b></TD>";
				echo "<TD colspan=2 align=center style='font-size:9px;font-weight:bold;'><b></b></TD>";
				echo "<TD style='text-align:right;font-size:9px;font-weight:bold;' nowrap><b>$".$espacio . number_format($SaldoFinalC2,2) . "</b></TD>";
				echo "<TD style='text-align:right;font-size:9px;font-weight:bold;' nowrap><b>$".$espacio . number_format($SaldoFinalA2,2) . "</b></TD>";
				if ($saldo < 0 ){
					echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#A01010' align=right><b>$".$espacio . number_format($saldotmp,2) . "</b></TD>";
				}else{
					echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#101010' align=right><b>$".$espacio . number_format($saldotmp,2) . "</b></TD>";
				}
				echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>X</td>";
				
				if ($saldoConfirmado < 0 ){
					echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#A01010' align=right><b>$".$espacio . number_format($saldoConfirmado,2) . "</b></TD>";
				}else{
					echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#101010' align=right><b>$".$espacio . number_format($saldoConfirmado,2) . "</b></TD>";
				}
				echo 	"<td colspan='3'>&nbsp;</td>";

				echo "</TR>";
			}
			$saldotmp = 0;
			$semanant = $myrow['sem'];
		
		}
		if($myrow['confirmado'] == '0'){
			if(!isset($_POST['excel'])) {
				$borrar = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Delete=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&Oper=eliminaMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='borrar' border=0 src='".$rootpath."/css/flujo/flecha_borrar.gif' width=5 height=10'>";
				$modificar = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Edit=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" .  $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&Oper=pendMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='modificar' border=0 src='".$rootpath."/css/flujo/flecha_lock.gif' width=5 height=10'>";	
				$bloquear = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Block=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" .  $myrow['mes'] . "&FromYear=" . $myrow['anio']. "&BankAccount=" . $_POST['BankAccount'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&Oper=confMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='bloquear' border=0 src='".$rootpath."/css/flujo/flecha_unlock.gif' width=5 height=10'>";
				$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" .  $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&Oper=anteriorPrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> ";	
				$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" .  $myrow['mes'] . "&FromYear=" . $myrow['anio'] . "&BankAccount=" . $_POST['BankAccount'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&Oper=siguientePrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'>";
					
				$check = "<INPUT type=checkbox id='chk".$I."' name='selMovimiento[]' 
				value='".$myrow['u_movimiento'].">&nbsp;&nbsp;&nbsp;";
			}else{
				$borrar='';
				$modifiar='';
				$bloquear='';
				$prioridadmas='';
				$prioridadmenos='';
				$check='';
			}
			
			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0F0'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFA'>";
					$colorlinea = 0;
				}
				
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];
				
				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($myrow['confirmado'] == '0')
					$saldoConfirmado = $SaldoInicialConfirmado - $myrow['cargo'] + $myrow['abono'];
					
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0FF'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFF'>";
					$colorlinea = 0;
				}
			}
			$saldotmp += $saldo;
			$saldotmptotal += $saldo;
			echo    "<td align=center nowrap>". $check .''. $modificar ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['dia'].' '. $friendlymes[abs($myrow['mes'])] .' '. $myrow['anio'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['sem'] ."</td><td nowrap style='font-size:9px;font-weight:bold;' width=150>". $myrow['concepto']."<br><br> *p:".$myrow['descripcion'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td style='font-size:9px;font-weight:bold;' width=80>". $myrow['referencia'] ."</td>";
			echo 	"<td style='font-size:9px;font-weight:bold;' width=80>".$myrow['nombreEntidad']."</td>";
			
			
			
			
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td style='font-size:9px;font-weight:bold;' nowrap ><p align=right ".$colormonto.">$". number_format($myrow['cargo'],2) ."</td>";
			}else{
			    echo 	"<td style='font-size:9px;font-weight:bold;'nowrap ><p align=right>&nbsp;</td>";
			}
			
			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red'";
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right ".$colormonto.">$". number_format($myrow['abono'],2) ."</td>";
			}
			else{
			    echo 	"<td style='font-size:9px;font-weight:bold;'><p align=right>&nbsp;</td>";
			}
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,2) ."</td>";
			
			
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px;font-weight:bold;'><p align=right><b>$". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;' style='font-size:9px;font-weight:bold;'><p align=right><b>$". number_format($saldo,2) ."</b></td>";
			    
			//comentado
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $myrow['prioridad'] ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px;font-weight:bold;'><p align=right><b>$". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;' style='font-size:9px;font-weight:bold;'><p align=right><b>$". number_format($saldoConfirmado,2) ."</b></td>";
			//fin comentado
			
			echo "<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['bankaccountname'] ."</td>";
						
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['Categoria'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['tagdescription'] ."UN</td>";
			echo "</tr>";
			
			$SaldoInicial = $saldo;
			$SaldoInicialConfirmado = $saldoConfirmado;
			
			if($myrow['activo'] == '0'){
				$SaldoFinalS = $SaldoFinalS;
			} else {
				$SaldoFinalS = $SaldoFinalS + $SaldoInicial;
			}
				
		}elseif($myrow['confirmado'] == '1'){
			if(!isset($_POST['excel'])) {
				$ligajs = "fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Delete=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio']."&CategoryId=" . $_POST['CategoryId'] . "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=eliminaMovimiento&u_movimiento=" . $myrow['u_movimiento'];
				$borrar = "<a href='javascript:confirmation(\"".$ligajs."\")'>
						<img alt='borrar' border=0 src='".$rootpath."/css/flujo/flecha_borrar.gif' width=5 height=10'></a>&nbsp;";
				$modificar = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Edit=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio']."&CategoryId=" . $_POST['CategoryId'] . "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=pendMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='modificar' border=0 src='".$rootpath."/css/flujo/flecha_lock.gif' width=5 height=10'></a>";	
				$bloquear = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Block=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=confMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
						<img alt='bloquear' border=0 src='".$rootpath."/css/flujo/flecha_unlock.gif' width=5 height=10'></a>";
				$prioridadmas =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio']."&CategoryId=" . $_POST['CategoryId'] . "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorPrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='mas importante' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";	
				$prioridadmenos = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguientePrioridad&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='menos importante' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";
				$antdia =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio']."&CategoryId=" . $_POST['CategoryId'] . "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorDia&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='dia anterior' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
				$sigdia = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteDia&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='dia siguiente' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'> </a>";
				$antsem =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio']."&CategoryId=" . $_POST['CategoryId'] . "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorSemana&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='anterior semana' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
							
				$sigsem = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteSemana&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='siguiente semana' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";
				
				$antmes =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio']."&CategoryId=" . $_POST['CategoryId'] . "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorMes&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='mes anterior' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
				$sigmes = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteMes&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='mes siguiente' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";
				$antanio =	"<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=anteriorAnio&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='a�o anterior' border=0 src='".$rootpath."/css/flujo/flecha_izquierda.gif' width=5 height=10'> </a>";
				$siganio = "<a href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=siguienteAnio&u_movimiento=" . $myrow['u_movimiento'] . "'>
							<img alt='a�o siguiente' border=0 src='".$rootpath."/css/flujo/flecha_derecha.gif' width=5 height=10'></a>";				
				$check = "<INPUT type=checkbox id='chk".$I."' name='selMovimiento[]' value='".$myrow['u_movimiento'].">&nbsp;";
				
				$modificarDatos = "<a target='_blank' href='fjoFlujoV2_0.php?thislegalid=".$thislegalid."&tipo=FJO&Edit=Yes&legalid=" . $myrow['legalid'] . "&FromMes=" . $myrow['mes'] . "&FromYear=" . $myrow['anio'] ."&CategoryId=" . $_POST['CategoryId']. "&subCategoriaSel=" . $_POST['subCategoriaSel']. "&BankAccount=" . $_POST['BankAccount'] . "&Oper=EditarMovimiento&u_movimiento=" . $myrow['u_movimiento'] . "'>
						</a>";	
			}else{
				$ligajs='';
				$modificarDatos='';
				$check='';
				$borrar='';
				$bloquear='';
			}
			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				$saldoConfirmado = $SaldoInicialConfirmado;
				
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0F0'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFA'>";
					$colorlinea = 0;
				}
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];
				$saldoConfirmado = $SaldoInicialConfirmado;
					
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0FF'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFF'>";
					$colorlinea = 0;
				}
			}
			$saldotmp += $saldo;
			$saldotmptotal += $saldo;
			echo    "<td align=center nowrap>". $borrar .''. $check .''. $bloquear ."</td>";
			echo 	"<td align=center nowrap style='font-size:9px;font-weight:bold;'>". $antdia .''. $myrow['dia'] .''. $sigdia .'<br>'. $antmes .''. $friendlymes[abs($_POST['FromMes'])]
							.''. $sigmes .'<br>'. $antanio .''. $_POST['FromYear'] .''. $siganio ."</td>";
			
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td nowrap><input STYLE='text-align:left' type='text' size='40' width=300 value='". $myrow['concepto'] ."' onchange='seleccionaCheckBox(".$I.")' name='Concepto_".$myrow['u_movimiento']."'><br>
								*P:<input STYLE='text-align:left' type='text' size='40' value='". $myrow['descripcion'] ."' onchange='seleccionaCheckBox(".$I.")' name='Descripcion_".$myrow['u_movimiento']."'></td>";
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td width=80><input STYLE='text-align:left' type='text' size='7' value='". $myrow['referencia'] ."' onchange='seleccionaCheckBox(".$I.")' name='Ref_".$myrow['u_movimiento']."'></td>";
			echo 	"<td  width=80 style='font-size:9px;font-weight:bold;'>".$myrow['nombreEntidad']."</td>";
			if(!isset($_POST['excel'])) {
				if ($myrow['cargo'] != 0)
				
				    echo "<td><input STYLE='text-align:right' class=number type='text' size='10' value='".$myrow['cargo']."' onchange='seleccionaCheckBox(".$I.")' name='Cargo_".$myrow['u_movimiento']."'></td>";
				    
				    //echo 	"<td><p align=right>$&nbsp;". number_format($myrow['cargo'],2) ."</td>";
				else
				    echo "<td><input STYLE='text-align:right' class=number type='text' size='10' value='' onchange='seleccionaCheckBox(".$I.")' name='Cargo_".$myrow['u_movimiento']."'o></td>";
				    //echo 	"<td><p align=right>&nbsp;</td>";
			}else{
				echo "<td>$".number_format($myrow['cargo'],2)."</td>";
			}
			if(!isset($_POST['excel'])) {
				if ($myrow['abono'] != 0)
				    echo "<td nowrap ><input STYLE='text-align:right' class=number type='text' size='10' value='".$myrow['abono']."' onchange='seleccionaCheckBox(".$I.")' name='Abono_".$myrow['u_movimiento']."'></td>";
				    
				    //echo 	"<td style='color:green;'><p align=right>$&nbsp;". number_format($myrow['abono'],2) ."</td>";
				else
				    echo "<td nowrap><input STYLE='text-align:right' class=number type='text' size='10' value='' onchange='seleccionaCheckBox(".$I.")' name='Abono_".$myrow['u_movimiento']."'></td>";
			    //echo 	"<td><p align=right>&nbsp;</td>";
			}else{
				echo "<td>$".number_format($myrow['abono'],2)."</td>";
			}
			//echo 	"<td><p align=right>$&nbsp;". number_format($myrow['cargo'],2) ."</td>";
			//echo 	"<td><p align=right>$&nbsp;". number_format($myrow['abono'],2) ."</td>";
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:red;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldo,2) ."</b></td>";
			    
			//comentado
			echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>". $prioridadmas .''. $myrow['prioridad'] .''. $prioridadmenos ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:gray;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:#45AAAA;' style='font-size:9px;font-weight:bold;'><p align=right><b>$&nbsp;". number_format($saldoConfirmado,2) ."</b></td>";
			//fin comentado
			  
			
			
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['bankaccountname'] ."</td>";
			
			
			/************************************/
			/*    SELECCION GRUPO CONTABLE      */
			echo 	"<td nowrap>";
			if(!isset($_POST['excel'])) {
				echo 	"<select name='tipoMovimiento_".$myrow['u_movimiento']."'>";
				
				$SQL = "select fjoCategory.cat_name,  fjoSubCategory.subcat_name, fjoSubCategory.subcat_id
					  from fjoCategory JOIN fjoSubCategory ON fjoCategory.cat_id = fjoSubCategory.cat_id
					   where (fjoCategory.legalid in (" . $thislegalid . "))
					  order by fjoCategory.Order, fjoSubCategory.Order";		
			
				$resultGC=DB_query($SQL,$db);
				
				/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
				
				echo "<option selected value='0'>sin seleccion...</option>";
				
				$catant = '';
				while ($myrowGC=DB_fetch_array($resultGC)){
					if ($catant != $myrowGC['cat_name']) {
						echo '<option value="novalida"> -------------------------------------------</option>';
						echo '<option value="novalida"> ******** ' . $myrowGC['cat_name'] .'</option>';
						$catant = $myrowGC['cat_name'];
					}
					if ($myrow["CategoriaId"]==$myrowGC["subcat_id"]){
						echo '<option selected value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
					} else {
						echo '<option value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
					}
				}
				echo '</select>';
			}
			echo "</td>";
			/**********************************/
			$cond="";
			if ($_POST['legalid'] > 0)
				$cond = "and tags.legalid = '".$_POST['legalid']."'";
			
			$sql = "Select tags.tagref,tags.tagdescription
					FROM tags
					  	INNER JOIN sec_unegsxuser ON tags.tagref = sec_unegsxuser.tagref
					WHERE sec_unegsxuser.userid = '".$_SESSION['UserID']."'
					and tags.showflujo=1
					$cond";
			
		

			echo "<td nowrap>
					   <select name='tagref_".$myrow['u_movimiento']."'><option selected value=''>Seleccionar</option>";
			
			$rs = DB_query($sql,$db);
			while ($regs = DB_fetch_array($rs)){
				if ($myrow['tagref'] == $regs['tagref'])
					echo "<option selected value='".$regs['tagref']."'>".$regs['tagdescription']."</option>";
				else 
					echo "<option value='".$regs['tagref']."'>".$regs['tagdescription']."</option>";
						
			}
			echo "</select></td>";			
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
			$check = "<INPUT type=checkbox id='chkCXC".$I."' name='selMovimientoCXC[]' value='".$myrow['u_movimiento'].">&nbsp;&nbsp;&nbsp;";
			
			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0F0'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFA'>";
					$colorlinea = 0;
				}
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];
				
				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($myrow['confirmado'] == '0')
					$saldoConfirmado = $SaldoInicialConfirmado - $myrow['cargo'] + $myrow['abono'];
					
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0FF'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFF'>";
					$colorlinea = 0;
				}
			}
			$saldotmp += $saldo;
			$saldotmptotal += $saldo;
			if(isset($_POST['excel'])) {
				$check='';
				$prioridadmas='';
				$prioridadmenos='';
			}
			echo    "<td align=center nowrap  style='background-color:#9eFF9e;font-size:9px'>". $check ." CXC</td>";
			echo 	"<td align=center nowrap style='font-size:9px'>". $antdia .''. $myrow['dia'] .''. $sigdia .'<br>'. $antmes .''. $friendlymes[abs($myrow['mes'])]
							.''. $sigmes .'<br>'. $antanio .''. $myrow['anio'] .''. $siganio ."</td>";
							
			echo 	"<td nowrap style='font-size:9px'>". $antsem .''. $myrow['sem'] . $sigsem ."</td><td width=300 style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center style='font-size:9px'><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td style='background-color:#FF9e9e;font-size:9px'  width=80>". $myrow['referencia'] ."</td>";
			echo 	"<td style='font-size:9px' width=80>".$myrow['nombreEntidad']."</td>";
			
			
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black;font-size:9px'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red;font-size:9px'";
			    echo 	"<td nowrap ><p align=right ".$colormonto.">$". number_format($myrow['cargo'],2) ."</td>";
			}else{
			    echo 	"<td nowrap><p align=right>&nbsp;</td>";
			}
			
			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green;font-size:9px'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red;font-size:9px'";
			    echo 	"<td nowrap ><p align=right ".$colormonto.">$". number_format($myrow['abono'],2) ."</td>";
			}
			else{
			    echo 	"<td nowrap ><p align=right>&nbsp;</td>";
			}
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,2) ."</td>";style='color:green;font-size:9px
			
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px'><p align=right><b>$". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;font-size:9px'><p align=right><b>$". number_format($saldo,2) ."</b></td>";
			
			//estaba comentado
			echo 	"<td style='font-size:9px'><p align=center>". $prioridadmas .''. $myrow['prioridad'] .''. $prioridadmenos ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px'><p align=right><b>$". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;font-size:9px'><p align=right><b>$". number_format($saldoConfirmado,2) ."</b></td>";
			 //hasta qui comentado 
			
			
			echo 	"<td nowrap style='font-size:9px'>".$myrow['bankaccountname']."</td>";
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
			
			/********************************************/
			/* MOVIMIENTOS DE MODULO DE CUENTAS X PAGAR */
			/********************************************/
			
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
			$check = " <INPUT type=checkbox id='chkCXP".$I."' name='selMovimientoCXP[]' value='".$myrow['u_movimiento'].">&nbsp;&nbsp;&nbsp;";
			
			if($myrow['activo'] == '0'){
				$saldo = $SaldoInicial;
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0F0'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFA'>";
					$colorlinea = 0;
				}
			} else {
				$saldo = $SaldoInicial - $myrow['cargo'] + $myrow['abono'];
				
				$saldoConfirmado = $SaldoInicialConfirmado;
				if ($myrow['confirmado'] == '0')
					$saldoConfirmado = $SaldoInicialConfirmado - $myrow['cargo'] + $myrow['abono'];
					
				if ($colorlinea == 0) {
					echo "<tr style='background-color:#F0F0FF'>";
					$colorlinea = 1;
				} else {
					echo "<tr style='background-color:#FAFAFF'>";
					$colorlinea = 0;
				}
			}
			$saldotmp += $saldo;
			$saldotmptotal += $saldo;
			if(isset($_POST['excel'])) {
				$check='';
				$prioridadmas='';
				$prioridadmenos='';
			}
			
			echo "<tr>";
			echo    "<td align=center nowrap  style='background-color:#FF9e9e;font-size:9px'>". $check ." CXP</td>";
			echo 	"<td align=center nowrap style='font-size:9px'>".  $myrow['dia'] . '/'. $friendlymes[abs($_POST['FromMes'])]
							.'/'. $_POST['FromYear'] ."</td>";
							
			echo 	"<td nowrap style='font-size:9px'>". $myrow['sem'] ."</td><td width=300  style='font-size:9px;font-weight:bold;'>". $myrow['concepto'] ."</td>";
			echo 	"<td align=center><p align=center>". $myrow['legalid'] ."</td>";
			echo 	"<td width=80 style='background-color:#FF9e9e;font-size:9px' >". $myrow['referencia'] ."</td>";
			echo 	"<td width=80>".$myrow['nombreEntidad']."</td>";
			if ($myrow['cargo'] != 0){
			    $colormonto = "style='color:black;font-size:9px'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red;font-size:9px'";
			    echo 	"<td nowrap ><p align=right ".$colormonto.">$". number_format($myrow['cargo'],2) ."</td>";
			}else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}
			
			if ($myrow['abono'] != 0){
			    $colormonto = "style='color:green;font-size:9px'";
			    if ($myrow['atrasado'] == 1)
			      $colormonto = "style='color:red;font-size:9px'";
			    echo 	"<td nowrap ><p align=right ".$colormonto.">$;". number_format($myrow['abono'],2) ."</td>";
			}
			else{
			    echo 	"<td><p align=right>&nbsp;</td>";
			}
			    
			//echo 	"<td><p align=right>$&nbsp;". number_format($saldo,2) ."</td>";
			
			if ($saldo >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px'><p align=right><b>$". number_format($saldo,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;font-size:9px'><p align=right><b>$". number_format($saldo,2) ."</b></td>";
			    
			//comentado
			echo 	"<td><p align=center>". $myrow['prioridad'] ."</td>";
			
			if ($saldoConfirmado >= 0)
			    echo 	"<td nowrap style='color:green;font-size:9px'><p align=right><b>$". number_format($saldoConfirmado,2) ."</b></td>";
			else
			    echo 	"<td nowrap style='color:orange;font-size:9px'><p align=right><b>$". number_format($saldoConfirmado,2) ."</b></td>";
			//hasta qui comentado
			
			echo 	"<td nowrap style='font-size:9px'>".$myrow['bankaccountname']."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['Categoria'] ."</td>";
			echo 	"<td nowrap style='font-size:9px;font-weight:bold;'>". $myrow['tagdescription'] ."</td>";
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

		$warea="";
		if ($_POST['area']!="")
			$warea = " and areas.areacode = '".$_POST['area']."'";
			
		$sql = "select sum(x.abono) as abono from (
		select sum(abono) as abono
		from Movimientos LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
		                 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
		                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
				 LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid /*AND tagsxbankaccounts.tagref = tags.tagref*/
				 AND Movimientos.tagref=tags.tagref
				 LEFT JOIN areas ON tags.areacode = areas.areacode
		where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1 /*and tags.tagref is not null*/
			and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
			$warea
			and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
			and Movimientos.u_empresa in (". $thislegalid .")  
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*')
			and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
		
		if ($_POST['subCategoriaSel'] == 'null') {
			$sql = $sql . "
			and (fjoSubCategory.subcat_id is null)
			AND erp = 0";
		} else {
			$sql = $sql . "
			and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
			AND erp = 0";
		}
		// agrega movimientos de erp
			
		$sql=$sql." ) as x";
		//echo "<pre>$sql";
		$ResultS1 = DB_query($sql,$db);
		$abonoT2 = DB_fetch_array($ResultS1);
		//echo $sql;

		$warea="";
		if ($_POST['area']!="")
			$warea = " and areas.areacode = '".$_POST['area']."'";
		
		$sql = "select sum( x.cargo) as cargo from (
		select sum(cargo) as cargo
		from Movimientos LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
		                 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
		                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
				 LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid /*AND tagsxbankaccounts.tagref = tags.tagref*/
				 AND Movimientos.tagref=tags.tagref
				 	LEFT JOIN areas ON areas.areacode = tags.areacode
		where week(concat(anio,'-',mes,'-',dia),1)='".$semanant."' and activo = 1 /*and tags.tagref is not null*/
			and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0')
			$warea
			and mes = '". $FromMes ."' AND anio = '". $FromYear ."'
			and Movimientos.u_empresa in (". $thislegalid .")  
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*')
			and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
			
		if ($_POST['subCategoriaSel'] == 'null') {
			$sql = $sql . "
			and (fjoSubCategory.subcat_id is null)
			AND erp = 0";
		} else {
			$sql = $sql . "
			and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
			AND erp = 0";
		}
				
		$sql=$sql." ) as x";	
	//	echo '<pre>sql2 : '.$sql;
		$ResultS2 = DB_query($sql,$db);
		$cargoT2 = DB_fetch_array($ResultS2);
			
		$SaldoFinalC2 = $cargoT2['cargo'];
		$SaldoFinalA2 = $abonoT2['abono'];
		
				
		echo "<TR height=20 bgcolor=#A9D0F5>";
		echo "<TD colspan=5 align=left style='font-size:9px;font-weight:bold;'><b> SALDO FINAL DE  SEMANA".'_'.$semanant."</b></TD>";
		echo "<TD colspan=2 align=center style='font-size:9px;font-weight:bold;'><b></b></TD>";
		echo "<TD style='text-align:right;font-size:9px;font-weight:bold;'><b>$" . number_format($SaldoFinalC2,2) . "</b></TD>";
		echo "<TD style='text-align:right;font-size:9px;font-weight:bold;'><b>$" . number_format($SaldoFinalA2,2) . "</b></TD>";
		if ($saldo < 0 ){
			echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#A01010' align=right><b>$" . number_format($saldotmp,2) . "</b></TD>";
		}else{
			echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#101010' align=right><b>$" . number_format($saldotmp,2) . "</b></TD>";
		}
		echo 	"<td style='font-size:9px;font-weight:bold;'><p align=center>X</td>";
		
		if ($saldoConfirmado < 0 ){
			echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#A01010' align=right><b>$" . number_format($saldoConfirmado,2) . "</b></TD>";
		}else{
			echo "<TD style='text-align:right;font-size:9px;font-weight:bold;color:#101010' align=right><b>$" . number_format($saldoConfirmado,2) . "</b></TD>";
		}
		echo 	"<td colspan='3'>&nbsp;</td>";
		echo "</TR>";
      }
	}

	$warea="";
	if ($_POST['area']!="")
		$warea = " and areas.areacode = '".$_POST['area']."'";
	
	$sql = "select sum(x.cargo) as cargo from (
		select sum(cargo) as cargo
		from Movimientos LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
	                 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
	                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
				 LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid /*AND tagsxbankaccounts.tagref = tags.tagref*/
				 AND Movimientos.tagref=tags.tagref
				LEFT JOIN areas ON tags.areacode = areas.areacode
		where mes = '". $_POST['FromMes'] ."'
			and anio = '". $_POST['FromYear'] ."'
			and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0') /*and tags.tagref is not null*/
			$warea
			and u_empresa in (". $thislegalid .")  
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*') and activo = 1
			and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
		
	if ($_POST['subCategoriaSel'] == 'null') {
		$sql = $sql . "
			and (fjoSubCategory.subcat_id is null)
			AND erp = 0";
	} else {
		$sql = $sql . "
			and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
			AND erp = 0";
	}
	// agrega movimientos de erp
		
	
	$sql = $sql .') as x';
	
	//echo "<br><pre>cargo: $sql";	
	$ResultS = DB_query($sql,$db);
	$cargoT = DB_fetch_array($ResultS);
	$warea="";
	if ($_POST['area']!="")
		$warea = " and areas.areacode = '".$_POST['area']."'";
				
	
	
	$sql = "select sum(x.abono) as abono from (
		select sum(abono) as abono
		from Movimientos LEFT JOIN fjoSubCategory ON fjoSubCategory.subcat_id = Movimientos.TipoMovimientoId
					LEFT JOIN fjoCategory  ON fjoCategory.cat_id = fjoSubCategory.cat_id
	                 /*LEFT JOIN tagsxbankaccounts ON Movimientos.u_banco = tagsxbankaccounts.accountcode*/
	                 LEFT JOIN legalbusinessunit ON Movimientos.u_empresa = legalbusinessunit.legalid
			LEFT JOIN tags ON legalbusinessunit.legalid = tags.legalid /*AND tagsxbankaccounts.tagref = tags.tagref*/
			AND Movimientos.tagref=tags.tagref
				 LEFT JOIN areas ON tags.areacode = areas.areacode
		where mes = '". $_POST['FromMes'] ."'
			and anio = '". $_POST['FromYear'] ."'
			and (tags.tagref = '". $_POST['unidadnegocio'] ."' or '". $_POST['unidadnegocio'] ."' ='0') /*and tags.tagref is not null*/
			$warea
			and u_empresa in (". $thislegalid .") 
			and (u_banco = '". $_POST['BankAccount'] ."' or '". $_POST['BankAccount'] ."' ='*') and activo = 1 
			and (fjoCategory.cat_id = '". $_POST['CategoryId'] ."' or '". $_POST['CategoryId'] ."' ='*')";
	if ($_POST['subCategoriaSel'] == 'null') {
		$sql = $sql . "
			and (fjoSubCategory.subcat_id is null)
			AND erp = 0";
	} else {
		$sql = $sql . "
			and (fjoSubCategory.subcat_id = '". $_POST['subCategoriaSel'] ."' or '". $_POST['subCategoriaSel'] ."' ='*')
			AND erp = 0";
	}
	// agrega movimientos de erp
				
	$sql=$sql." ) as x";
	
	$ResultS = DB_query($sql,$db);
	$abonoT = DB_fetch_array($ResultS);
	//echo "<br><br><pre>abono:$sql";
	
	$SaldoFinalC = $cargoT['cargo'];
	$SaldoFinalA = $abonoT['abono'];
	$SaldoFinalS = $myrow2['saldo'] + $SaldoInicial1['saldo'];
	
		echo "<TR height=20 bgcolor=#0D91EE>";
		echo "<TD colspan=5 align=left><font face='Arial Narrow' size='2' color='#000000'><b>SALDO FINAL</b></font></TD>";
		echo "<TD colspan=2 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";
		echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>$" . number_format($SaldoFinalC,2) . "</b></font></TD>";
                echo "<TD style='text-align:right'><font face='Arial Narrow' size='2' color='#000000'><b>$" . number_format($SaldoFinalA,2) . "</b></font></TD>";
		
		if ($saldo < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$" . number_format($saldotmptotal,2) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$" . number_format($saldotmptotal,2) . "</b></font></TD>";
		}
		
		echo "<TD colspan=1 align=center><font face='Arial' size='2' color='#000000'><b></b></font></TD>";
		
		if ($saldoConfirmado < 0 ){
		 	echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#A01010'><b>$" . number_format($saldoConfirmado,2) . "</b></font></TD>";
		}else{
			echo "<TD style='text-align:right' align=right><font face='Arial Narrow' size='2' color='#101010'><b>$" . number_format($saldoConfirmado,2) . "</b></font></TD>";
		}
		echo 	"<td colspan='3'>&nbsp;</td>";	
        	echo "</TR></TABLE>";
		
	if(!isset($_POST['excel'])) {
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
		echo '<select Name="mesDirecto">';
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
		   
		   echo '</select>';
		   echo '&nbsp;<input name="anioDirecto" type="text" size="4" value='.$FromYear.'>';
		echo "</td><td>";
		
				  	
		echo "<INPUT type=hidden name=CategoryId value='".$_POST['CategoryId']."'>";
		echo "<INPUT type=hidden name=subCategoriaSel value='".$_POST['subCategoriaSel']."'>";
		echo "<INPUT type=hidden name=arreglo value=1>
				</td><td width=850>&nbsp;</td>";		  
		
		$SaldoFinalC = 0;
		$SaldoFinalA = 0;
		
	echo '</TR>';
	
	/************************************/
	/* SELECCION ENTIDAD */
	
	echo "<TR  valign=bottom height=20 bgcolor=#BDF7AB><TD align=left colspan=20><td nowrap>
			  <font face='Arial Narrow' size='2' color='#000000'>ENTIDAD NEGOCIOS:</td><td>";
	//echo "--> " . $thislegalid;
	echo 	"<select name='entidadNegocio1'>";
	
	
	$SQL = "select distinct usrEntidades.u_entidad, usrEntidades.legalid, usrEntidades.Nombre
		  from usrEntidades JOIN tags ON usrEntidades.legalid = tags.legalid   
		  where (tags.legalid in (" . trim($thislegalid) . "))
		  order by usrEntidades.Nombre";		
	
	$resultGC=DB_query($SQL,$db);
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<option selected value='*'>sin seleccion...</option>";
	echo "<option value='0'>eliminar asignacion...</option>";
	
	while ($myrowGC=DB_fetch_array($resultGC)){
		echo '<option value="' . $myrowGC['u_entidad'] . '">' . $myrowGC['Nombre'] .'</option>';
	}
	echo '</select>';
	//echo $SQL;
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
			FROM bankaccounts, sec_unegsxuser, tagsxbankaccounts
					JOIN tags ON tagsxbankaccounts.tagref = tags.tagref
			WHERE bankaccounts.accountcode = tagsxbankaccounts.accountcode and
				tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
			and	sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
				tags.legalid in ('. $thislegalid .')
	
			GROUP BY bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode';

	
		$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
		$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
		$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	
		//echo $SQL;
		echo '<select name="BankAccount_GLOBAL">';
		if (DB_num_rows($AccountsResults)==0){
			echo '</select>';
			//prnMsg( _('No existen cuentas de cheques definidas aun') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('configurar cuentas de cheques') . '</a> ' . _('y las cuentas contables que estas afectaran'),'warn');
			include('includes/footer_Index.inc');
			exit;
		} else {
			echo "<option selected value='*'>Cuenta de Cheques por asignar...</option>";
			while ($myrow=DB_fetch_array($AccountsResults)){
			    /*list the bank account names */
			    echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			}
			echo '</select>&nbsp;</td><td>';
		}
		
		echo "</td><td width=850>&nbsp;</td>";
		
	echo '</TR>';
	
	echo "<TR  valign=bottom height=20 bgcolor=#BDF7AB><TD align=left colspan=20><td nowrap>
			  <font face='Arial Narrow' size='2' color='#000000'>Asigna Sub Categoria:</td><td>";

		$order = "fjoCategory.order, fjoSubCategory.order";
		if ($_SESSION['OrderByNameSubCategory']==1)
			$order = "fjoCategory.cat_name, fjoSubCategory.subcat_name";
						     
		/************************************/
			
		$SQL = "select fjoCategory.cat_name,  fjoSubCategory.subcat_id, fjoSubCategory.subcat_name
			  from fjoCategory JOIN fjoSubCategory ON fjoCategory.cat_id = fjoSubCategory.cat_id
			  where (fjoCategory.legalid in (" . $thislegalid . "))
				
			  order by $order";		
		
		$resultGC=DB_query($SQL,$db);
		echo 	"<select name='asignaSubCtaDirecta'>";
		
		/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
		
		echo "<option selected value='novalida'>sin seleccion...</option>";
		
		$catant = '';
		while ($myrowGC=DB_fetch_array($resultGC)){
			if ($catant != $myrowGC['cat_name']) {
				echo '<option value="novalida"> -------------------------------------------</option>';
				echo '<option value="novalida"> ******** ' . $myrowGC['cat_name'] .'</option>';
				$catant = $myrowGC['cat_name'];
			}
			echo '<option value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
			
		}
		
		//echo '<input type=hidden name="subCategoriaSel" value="' . $_POST['subCategoriaSel'] . '">';
		echo '<input type=hidden name="CategoryId" value="' . $_POST['CategoryId'] . '">';
		echo '</select><INPUT type=submit name=instruccionGeneral value=PROCESAR></td><td>';
		
		echo "</td><td width=300>&nbsp;</td>";
		
	echo '</TR>';
	
	
	echo '</table><br>';

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
				 /*and t.legalid in (". $thislegalid .")*/
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
	echo '</select>';
	echo '<input type="submit" value="->" name="btnLegal"></td>';
	echo '</tr>';
	/************************************/
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
		
		$whereLegalId = "";
		if(empty($_POST['legalid']) == FALSE) {
			$whereLegalId = " AND t.legalid = " . $_POST['legalid'];
		}
		
		echo "<tr><td style='text-align:right'>" . _('X Unidad de Negocio') . ":</td><td>";
		echo "<select name='unidadnegocioalta'>";
		$SQL = "SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription, t.tagdescription ";//areas.areacode, areas.areadescription";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref $whereLegalId";
			$SQL = $SQL .	" $wcond and showflujo=1 and u.userid = '" . $_SESSION['UserID'] . "'  and (t.legalid = '".$_POST['legalid']."' OR '0'= '".$_POST['legalid']."' )
					ORDER BY t.tagdescription, areas.areacode";
		
		$ErrMsg = _('No transactions were returned by the SQL because');
		$TransResult = DB_query($SQL,$db,$ErrMsg);
		
		//echo "<option selected value='0'>Todas a las que tengo accceso...</option>";
		
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['tagref'] == $_POST['unidadnegocioalta']){
				echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";	
			}else{
				echo "<option value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
			}
		}
		 
		echo "</select>";
		echo "</td></tr>";
	
	
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
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref 
		and	sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
			tags.legalid in ('. $thislegalid .')

		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';
		/*and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" and
			tags.legalid in ('. $thislegalid .')*/

	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Selecciona un Banco') . ':</td><td><select name="BankAccount2">';
	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td></tr></table><p>';
		prnMsg( _('No existen cuentas de cheques definidas aun') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('configurar cuentas de cheques') . '</a> ' . _('y las cuentas contables que estas afectaran'),'warn');
		include('includes/footer_Index.inc');
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
	
	/*
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
	echo "<tr><td>" . _('No. Factura � Cheque') . ":</td><td>";
	echo "<input type='text' size='12' name='factura'>";
	echo "</td></tr>";
	
	echo "<tr><td>" . _('Cargo') . ":</td><td>";
	echo "<input type='text' size='7' name=Cargo>";
	echo "</td></tr>";
	
	echo "<tr><td>" . _('Abono') . ":</td><td>";
	echo "<input type='text' size='7' name='abono'><br><br>";
	echo "</td></tr>";
	
	
	echo '<tr><td style="text-align:left">' . _('X Sub Categoria') . ':</td><td>
		<select name="tipoMovimiento">';
	
	$order = "fjoCategory.order, fjoSubCategory.order";
	if ($_SESSION['OrderByNameSubCategory']==1)
		$order = "fjoCategory.cat_name, fjoSubCategory.subcat_name";

	$SQL = "select fjoCategory.cat_name,  fjoSubCategory.subcat_name, fjoSubCategory.subcat_id
		  from fjoCategory JOIN fjoSubCategory ON fjoCategory.cat_id = fjoSubCategory.cat_id
		  where ((fjoCategory.legalid =" . $_POST['legalid'] ." OR '".$_POST['legalid']."' = '0')) AND
			(fjoCategory.cat_id ='". $_POST["CategoryId"]."' OR '*'='". $_POST["CategoryId"]."')
		  order by $order ";		

	$resultGC=DB_query($SQL,$db);
	
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	
	echo "<option selected value='*'>Todas las Sub Categorias...</option>";
	
	if ($_POST["subCategoriaSel"] == 'null')
		echo "<option selected value='null'>Sin Sub-Categoria...</option>";
	else
		echo "<option value='null'>Sin Sub-Categoria...</option>";
		
	$catant = '';
	while ($myrowGC=DB_fetch_array($resultGC)){
		if ($catant != $myrowGC['cat_name']) {
			echo '<option value="novalida"> -------------------------------------------</option>';
			echo '<option value="novalida"> ******** ' . $myrowGC['cat_name'] .'</option>';
			$catant = $myrowGC['cat_name'];
		}
		if ($_POST["subCategoriaSel"]==$myrowGC["subcat_id"]){
			echo '<option selected value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
		} else {
			echo '<option value="' . $myrowGC['subcat_id'] . '">'.$myrowGC['subcat_name'].'</option>';
		}
	}
	echo '</select></td></tr>';
	/**********************************/
	
	
	/************************************/
	/* SELECCION ENTIDAD 		    */
	echo "<tr><td>" . _('Entidad Negocios') . ":</td><td>";
	//echo "--> " . $thislegalid;
	echo 	"<select name='entidadNegocio'>";
	
	
	$SQL = "select distinct u_entidad, tags.legalid, Nombre
		  from usrEntidades JOIN tags ON usrEntidades.legalid = tags.legalid
		  where (tags.legalid in (" . trim($thislegalid) . "))
		  order by Nombre";		
	/*	  
	$SQL = "select u_entidad, legalid, Nombre
		  from usrEntidades
		  where (legalid in (" . trim($thislegalid) . "))
		  order by Nombre";			  
	*/
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
	echo "</table>";

	/*
	$pruba = 'hola como stan /r/n espero /r/n que super bn /r/n';
	$bodytag = str_replace("/r/n", " ", ".$pruba.");
	
	echo $bodytag;
	 */
	//var_dump($saldos);
	include('includes/footer_Index.inc');
}
?>