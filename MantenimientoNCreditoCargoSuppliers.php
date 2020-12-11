<?php
$funcion=259;
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Mantenimiento de Notas de Credito / Cargo de Proveedor');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$msg='';
if (isset($_POST['FromYear'])) {
	$FromYear=$_POST['FromYear'];
} elseif(isset($_GET['FromYear'])) {
	$FromYear=$_GET['FromYear'];
}else{
	$FromYear=date('Y');
}

if (isset($_POST['FromMes'])) {
	$FromMes=$_POST['FromMes'];
} elseif(isset($_GET['FromMes'])) {
	$FromMes=$_GET['FromMes'];
}else{
	$FromMes=date('m');
}

if (isset($_GET['FromDia'])) {
	$FromDia=$_GET['FromDia'];
}elseif(isset($_POST['FromDia'])) {
	$FromDia=$_POST['FromDia'];
}else{
	$FromDia=date('d');
}

if (isset($_POST['ToYear'])) {
	$ToYear=$_POST['ToYear'];
} elseif(isset($_GET['ToYear'])) {
	$ToYear=$_GET['ToYear'];
}else{
	$ToYear=date('Y');
}

if (isset($_POST['ToMes'])) {
	$ToMes=$_POST['ToMes'];
} elseif(isset($_GET['ToMes'])) {
	$ToMes=$_GET['ToMes'];
}else{
	$ToMes=date('m');
}
if (isset($_GET['ToDia'])) {
	$ToDia=$_GET['ToDia'];
} elseif(isset($_POST['ToDia'])) {
	$ToDia=$_POST['ToDia'];
}else{
	$ToDia=date('d');
}
$fechadesde= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
$fechahasta= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);

if (isset($_POST['txtncc'])){
	$nccno = $_POST['txtncc'];	
}else{
	$nccno = '';
}
if (isset($_POST['cbounidadnegocio'])){
	$unidadnegocio = $_POST['cbounidadnegocio'];	
}else{
	$unidadnegocio = 0;
}

if (isset($_POST['cbotiponcc'])){
	$tiponcc = $_POST['cbotiponcc'];	
}else{
	$tiponcc = -1;
}

$serie = $_GET['serie'];
$folio = $_GET['folio'];
$rfc = $_GET['rfc'];
$keyfact = $_GET['keyfact'];
	

echo '<form action=' . $_SERVER['PHP_SELF'] . ' method=post name=form1>';
echo '<p class="page_title_text">' . ' ' . _('MANTENIMIENTO DE  NOTAS DE CREDITO / CARGO DE PROVEEDORES') . '</p>';
echo "<table width='90%' cellpadding='0' cellspacing='0' border='0'>";
echo "<tr>";
//echo "<td></td>";
echo "<td style='text-align:right; font-size:8pt;'><b>" . _('Desde') . ": &nbsp;</b></td>";
echo '<td colspan=2><select Name="FromDia">';
	$sql = "SELECT * FROM cat_Days";
	$dias = DB_query($sql,$db);
	while ($myrowdia=DB_fetch_array($dias,$db)){
	    $diabase=$myrowdia['DiaId'];
	    if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
		echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
	    }else{
		echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
	    }
	}
   //echo'</td>'; 
   echo '</select><select Name="FromMes">';
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
	     echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
		     
     echo '</td>
   
   <td colspan=3><b>' . _('Hasta:').'</b>' ;
   echo'<select Name="ToDia">';
	     $sql = "SELECT * FROM cat_Days";
	     $Todias = DB_query($sql,$db);
	     while ($myrowTodia=DB_fetch_array($Todias,$db)){
		 $Todiabase=$myrowTodia['DiaId'];
		 if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
		     echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
		 }else{
		     echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
		 }
	     }
   echo '</select>';
   //echo'';
	echo'<select Name="ToMes">';
	$sql = "SELECT * FROM cat_Months";
	$ToMeses = DB_query($sql,$db);
	while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
	    $ToMesbase=$myrowToMes['u_mes'];
	    if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
		echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
	    }else{
		echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
	    }
	}
	echo '</select>';
	echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
	
   echo'</td>';
echo "</tr><tr height=10><td></td></tr><tr>";
echo "<td style='text-align:right; font-size:8pt;'><b>" . _('NCC No.') . ": &nbsp;</b></td>";
echo "<td><input type='text' name='txtncc' value='" . $nccno . "' size='10' maxlength='10' class='number' style='font-size:8pt;'></td>";
echo "<td style='text-align:right; font-size:8pt;'><b>" . _('Unidad Negocio') . ": &nbsp;</b></td>";
echo "<td>";
echo "<select name='cbounidadnegocio' style='font-size:8pt;'>";
echo "<option value='0'>TODOS</option>";
//$SQL = "SELECT * FROM tags WHERE areacode = '" . $_SESSION['DefaultArea'] . "'";
$SQL = "SELECT  t.tagref, t.tagdescription";//areas.areacode, areas.areadescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' 
			ORDER BY areas.areacode";

$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);
while ($myrow=DB_fetch_array($TransResult)) {
	if ($myrow['tagref'] == $unidadnegocio){
		echo "<option selected value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";	
	}else{
		echo "<option value='" . $myrow['tagref'] . "'>" . $myrow['tagdescription'] . "</option>";
	}
}

echo "</select>";
echo "</td>";
echo "<td style='text-align:right; font-size:8pt;'><b>" . _('Tipo') . ": &nbsp;</b></td>";
echo "<td>";
echo "<select name='cbotiponcc' style='font-size:8pt;'>";
echo "<option value='-1'>TODOS</option>";
$SQL = "SELECT  t.typeid, t.typename
	FROM systypescat t
	WHERE typeid in (32,34)
	ORDER BY t.typeid";
$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);
while ($myrow=DB_fetch_array($TransResult)) {
	if ($myrow['typeid'] == $tiponcc){
		echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";	
	}else{
		echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";
	}
}

echo "</select>";
echo "</td>";
echo "</tr>";
echo "<tr height=10><td></td></tr>";
echo "<tr>";
echo "<td colspan='6' style='text-align:center;'><input type='submit' name='buscar' value='BUSCAR' style='font-size:8pt;'></td>";
echo "</tr>";
echo "</tr><tr height=5><td></td></tr><tr>";
echo "</table>";

if (isset($_GET['delete'])){
	echo "OPCION DELETE";
}else{
	echo "<table width='100%' cellpadding='3' cellspacing='3' border='1'>";
	echo "<tr>";
	echo "<th><b>" . _('Unidad Negocio'). "</b></th>";
	echo "<th><b>" . _('Tipo'). "</b></th>";
	echo "<th><b>" . _('Fecha'). "</b></th>";
	echo "<th><b>" . _('Folio ERP'). "</b></th>";
	echo "<th><b>" . _('Cod Prov'). "</b></th>";
	echo "<th><b>" . _('Proveedor'). "</b></th>";
	echo "<th><b>" . _('Referencia'). "</b></th>";
	echo "<th><b>" . _('Monto'). "</b></th>";
	echo "<th><b><img src='part_pics/Delete.png' border=0 title='" . _('Cancelar') . "' alt=''></b></th>";
	echo "</tr>";
	
	$SQL = "SELECT supptrans.tagref,
			supptrans.type,
			supptrans.transno,
			supptrans.suppreference as reference,
			supptrans.ovamount,
			supptrans.ovgst,
			supptrans.alloc,
			tags.tagdescription,
			suppliers.suppname as name,
			suppliers.supplierid,
			supptrans.trandate,
			supptrans.origtrandate,
			day(supptrans.origtrandate) as daytrandate,
			month(supptrans.origtrandate) as monthtrandate,
			year(supptrans.origtrandate) as yeartrandate,
			supptrans.folio,
			legalbusinessunit.taxid,
			legalbusinessunit.address5,
			supptrans.currcode,
			systypescat.typename,
			supptrans.id as iddocto,
			tags.typeinvoice,
			legalbusinessunit.legalid,
			supptrans.transtext,
			case when gltrans.periodno is null then 0 else gltrans.periodno end as periodno
	FROM  tags, sec_unegsxuser, suppliers, legalbusinessunit, supptrans
		LEFT JOIN gltrans ON supptrans.type=gltrans.type and supptrans.transno=gltrans.typeno
		, systypescat
	WHERE supptrans.type=systypescat.typeid
		and supptrans.tagref = tags.tagref
		and supptrans.supplierno = suppliers.supplierid
		and sec_unegsxuser.tagref = tags.tagref
		and supptrans.origtrandate >= '" . $fechadesde . "'
		and supptrans.origtrandate <= '" . $fechahasta . "'
		and sec_unegsxuser.tagref = tags.tagref
		and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' 
		and legalbusinessunit.legalid = tags.legalid";
	
	if ($nccno != ''){
		$SQL = $SQL . " and supptrans.transno = " . $nccno;
	}
	if ($unidadnegocio != 0){
		$SQL = $SQL . " and supptrans.tagref = " . $unidadnegocio;
	}
	
	if ($tiponcc != -1){
		$SQL = $SQL . " and supptrans.type = " . $tiponcc;
	}else{
		$SQL = $SQL . " and supptrans.type in (32,34) ";
	}
	
	$SQL = $SQL . " group by supptrans.transno";
	
	$SQL = $SQL . " order by supptrans.tagref, supptrans.trandate";
	
	//echo "<br>" . $SQL;
	$ErrMsg = _('No transactions were returned by the SQL because');
	$TransResult = DB_query($SQL,$db,$ErrMsg);
	
	$k = 1;
	$Ttotal = 0;
	while ($myrow=DB_fetch_array($TransResult)) {
		$typedocto=$myrow['type'];
		$fechaemision=$myrow['origtrandate'];
		$iddocto=$myrow['iddocto'];
		$suppliersel=$myrow['supplierid'];
		$tipofacturacionxtag=$myrow['typeinvoice'];
		$legalid=$myrow['legalid'];
		$periodocontable=$myrow['periodno'];
		$total=abs($myrow['ovamount']  + $myrow['ovgst']);
		
		$statusPeriodo=TraestatusPeriod($legalid,$periodocontable,$db);
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		if(($typedocto==34)){
		    $SQL="select * from suppallocs where transid_allocto=".$iddocto;
		    $ResultConsultados = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($ResultConsultados)==0 and $total>0) {
				$permiso = Havepermission($_SESSION['UserID'],260, $db);
				if ($permiso==1){
					if ($statusPeriodo==0){	
					    $Cancelar = $rootpath . '/Cancel_DoctosSuppliers.php?TransNo='.$transno.'&SupplierNo='.$suppliersel.'&iddocto='.$iddocto;
					    $Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
					}else{
						$Cancelar='<b>'._('Contabilidad').'<br>'._('Cerrada').'</b>';
					}
				}else{
				    $Cancelar="&nbsp;&nbsp;&nbsp;&nbsp;";
				}
			}elseif($total==0){
				$Cancelar =_('Cancel');
			}else{
				$Cancelar=_('Aplicada');
			}
		}elseif($typedocto==32 or $typedocto==39){
		
			$SQL="select * from suppallocs where transid_allocfrom=".$iddocto;
			$ResultConsultados = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
			if (DB_num_rows($ResultConsultados)==0 and $total>0) {
				$permiso = Havepermission($_SESSION['UserID'],260, $db);
				if ($permiso==1){
					if ($statusPeriodo==0){	
						$Cancelar = $rootpath . '/Cancel_DoctosSuppliers.php?TransNo='.$transno.'&SupplierNo='.$suppliersel.'&iddocto='.$iddocto;
						$Cancelar="<a href=".$Cancelar."><img src='part_pics/Delete.png' border=0></a>";
					}else{
						$Cancelar='<b>'._('Contabilidad').'<br>'._('Cerrada').'</b>';
					}
				}else{
				    $Cancelar="&nbsp;&nbsp;&nbsp;&nbsp;";
				}
			}elseif($total==0){
				$Cancelar =_('Cancel');
			}else{
				$Cancelar=_('Aplicada');
			}
		}
	       
		
		echo "<td style='font-size:7pt;'>" . $myrow['tagdescription'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['typename'] . "</td>";
		echo "<td style='font-size:7pt;' nowrap>" . $myrow['daytrandate'] . " - " . glsnombremescorto($myrow['monthtrandate']) . " - " . $myrow['yeartrandate'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['transno'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['supplierid'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['name'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['reference'].' '.$myrow['transtext'] . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format(($myrow['ovamount']  + $myrow['ovgst']),2) . "</td>";
		
		 echo "<td style='text-align:center;'>".$Cancelar."</td>";
		//echo "<td><b><a href='#'><img src='part_pics/Delete.png' onclick='cancela(" .  $myrow['transno']   . ",1);' border=0 title='" . _('Cancelar NCC') . "' alt=''></a></b></td>";
	
		echo "</tr>";
		$Ttotal =  $Ttotal + ($myrow['ovamount']  + $myrow['ovgst']);
	}
	
}
echo "<tr>";
	echo "<td colspan='8' style='font-size:9pt; text-align:right; font-weight:bold;'>" . _('Total') . ":</td>";
	echo "<td style='font-size:9pt; text-align:right; font-weight:bold;'>" . number_format($Ttotal,2) . "</td>";
echo "</tr>";
echo "<br><br>";
echo '</form>';


echo '</table></form>';
echo '<p><p><p>';
include('includes/footer.inc');
?>





	