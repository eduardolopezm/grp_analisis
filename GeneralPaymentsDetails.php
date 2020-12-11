<?php
 /*
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 24-FEB-2011
 CAMBIOS: 
	1. SE GENERO REPORTE
 FIN DE CAMBIOS
*/

$funcion=244;
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Reporte de Pagos De Pagos a Proveedores X Documento');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/SecurityFunctions.inc');
$msg='';
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
    
if (isset($_POST['FromDia'])) {
    $FromDia=$_POST['FromDia'];
} else {
    $FromDia=date('d');
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
if (isset($_POST['ToDia'])) {
    $ToDia=$_POST['ToDia'];
} else {
    $ToDia=date('d');
}
	
echo $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia)."1<br>";
    echo $fechafin= rtrim($ToYear). '-'.rtrim($ToMes).'-'.rtrim($ToDia). "2<br>";
     
     $fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
     $fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
     
    echo $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2)."3<br>";
    echo $fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1)."4<br>";;
/*INICIO
**RECUPERO LOS VALORES QUE VIENEN EN EL URL, ESTO SE APLICA CUANDO SE LLAMA
** LA OPCION DE ELIMINAR ALGUN RECIBO
*/
{
	if (isset($_GET['txtfechadesde'])){
		$_POST['txtfechadesde'] = $_GET['txtfechadesde'];
	}
	if (isset($_GET['txtfechahasta'])){
		$_POST['txtfechahasta'] = $_GET['txtfechahasta'];
	}
	if (isset($_GET['txtrecibono'])){
		$_POST['txtrecibono'] = $_GET['txtrecibono'];
	}
	if (isset($_GET['cbounidadnegocio'])){
		$_POST['cbounidadnegocio'] = $_GET['cbounidadnegocio'];
	}
}
/*FIN
*/

if (isset($_POST['txtfechadesde'])){
	$fechadesde = $_POST['txtfechadesde'];	
}else{
	$fechadesde = date('m') . "/" . date('d') . "/" . date('Y');
	//$fechadesde = date('m') . "/" . "23" . "/" . date('Y');
}

if (isset($_POST['txtfechahasta'])){
	$fechahasta = $_POST['txtfechahasta'];	
}else{
	$fechahasta = date('m') . "/" . date('d') . "/" . date('Y');
	//$fechahasta = date('m') . "/" . "23" . "/" . date('Y');
}

if (isset($_POST['txtrecibono'])){
	$recibono = $_POST['txtrecibono'];	
}else{
	$recibono = '';
}

if (isset($_POST['txtprov'])){
	$prov = $_POST['txtprov'];	
}else{
	$prov = '';
}

if (isset($_POST['cbounidadnegocio'])){
	$unidadnegocio = $_POST['cbounidadnegocio'];	
}else{
	$unidadnegocio = 0;
}

if (isset($_POST['cbTipodocumento'])){
	$tipodocumento = $_POST['cbTipodocumento'];	
}else{
	$tipodocumento = '';
}


if(isset($_POST['legalid'])) {
	$legalid = $_POST['legalid'];
} else {
	$legalid = '';
}


$serie = $_GET['serie'];
$folio = $_GET['folio'];
$rfc = $_GET['rfc'];
$keyfact = $_GET['keyfact'];

echo '<form action=' . $_SERVER['PHP_SELF'] . ' method=post name=form1>';
echo '<p class="page_title_text">' . ' ' . _('REPORTE DE PAGOS A PROVEEDORES') . '</p>';


echo "<table width='80%' cellpadding='0' cellspacing='0' border='0'>";
echo "<tr>";
echo "<td colspan=8>";
/*echo "<td></td><td></td>";
echo "<td></td>";
echo "<td style='text-align:right;'><b>" . _('Desde') . ": &nbsp;</b></td>";
echo "<td><input type='text' name='txtfechadesde' value='" . $fechadesde . "' readonly size='10' class='date' alt='m/d/Y'></td>";
echo "<td></td>";
echo "<td style='text-align:right;'><b>" . _('Hasta') . ": &nbsp;</b></td>";
echo "<td><input type='text' name='txtfechahasta' value='" . $fechahasta . "' readonly size='10' class='date' alt='m/d/Y'></td>";
echo "<td></td>";
*/

echo "<table width='80%' cellpadding='0' cellspacing='0' border='0'>";
echo "<tr>";
    echo '<td><b>' . _('Desde:') . '</b></td>
    <td><select Name="FromDia">';
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
	 
    echo'</td>'; 
    echo '<td><select Name="FromMes">';
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
		      
      echo '</td>';
    echo '<td>
	 &nbsp;
    </td>
    <td><b>' . _('Hasta:') . '</b></td>';
    echo'<td><select Name="ToDia">';
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
    echo '</td>';
    echo'<td>';
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
echo '<td>
	 &nbsp;
    </td>';

echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</tr><tr height=10><td></td></tr>";

//SELECCION RAZON SOCIAL
echo '<tr><td><b>'._('X Razon Social:').'</b><td><select name="legalid">';

///Pinta las razones sociales
$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname
		FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
		WHERE u.tagref = t.tagref 
		and u.userid ='" . $_SESSION['UserID'] . "' 
	    GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY legalbusinessunit.legalname";

$result=DB_query($SQL,$db);
echo "<option selected value=''>Todas las razones sociales...</option>";
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
		echo '<option selected value=' . $myrow['legalid'] . '>'.$myrow['legalname'];
	} else {
		echo '<option value=' . $myrow['legalid'] . '>'.$myrow['legalname'];
	}
}
echo '</select></td></tr>';

echo "<tr><td style='text-align:right;' nowrap><b>" . _('Documento No.') . ": &nbsp;</b></td>";
echo "<td ><input type='text' name='txtrecibono' value='" . $recibono . "' size='10' maxlength='10' class='number'></td>";
echo "<td style='text-align:right;' nowrap><b>" . _('No. Prov.') . ": &nbsp;</b></td>";
echo "<td ><input type='text' name='txtprov' value='" . $prov . "' size='10' maxlength='10'></td>";
echo "<td></td>";

//**************************************************
echo "<td style='text-align:right;' nowrap><b>" . _('Tipo de Documento') . ": &nbsp;</b></td>";
echo "<td>";
echo "<select name='cbTipodocumento'>";
echo "<option value=''>TODOS</option>";
$SQL = "SELECT typeid,typename FROM systypescat
		WHERE typeid IN('480','22','24','121',501) 
		ORDER BY typename";
$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);
while ($myrow=DB_fetch_array($TransResult)) {
	if ($myrow['typeid'] == $tipodocumento){
		echo "<option selected value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";	
	}else{
		echo "<option value='" . $myrow['typeid'] . "'>" . $myrow['typename'] . "</option>";
	}
}
if($tipodocumento == 'otros'){
	echo "<option selected value='otros'>Otros</option>";
}else{
	echo "<option value='otros'>Otros</option>";
}
echo "</select>";
echo "</td>";
//**********************************************

//echo "<td></td>";
/************************************/


echo "<td style='text-align:right;'><b>" . _('Unidad Negocio') . ": &nbsp;</b></td>";
echo "<td>";
echo "<select name='cbounidadnegocio'>";
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
echo "<td><input type='submit' name='buscar' value='BUSCAR'></td>";
echo "</tr>";
echo "</tr><tr height=10><td></td></tr><tr>";
echo "</table>";

if (isset($_GET['delete'])){
	$transno = $_GET['transno'];

	$permiso = Havepermission($_SESSION['UserID'],206, $db);
	echo "<table border='0' cellpadding='0' cellspacing='0' width='80%'><tr height=30><td></td></tr><tr>";
	if ($permiso==0){
		echo "<td>&nbsp;</td>";
	}else{
		echo "<td style='text-align:center; font-size:14pt;'>";
		echo _('Confirmar que deseas cancelar el Recibo No.') . " " . $transno . " ? ";
		echo "</td><td style='text-align:center; font-size:14pt;'><a href='" . $rootpath . "/CustomerReceiptCancel.php?confirmdelete=yes&serie=" . $serie . "&rfc=" . $rfc  . "&folio=" . $folio . "&keyfact=" . $keyfact . "&transno=" . $transno . "&txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio . "' style='font-size:14pt;'>";
		echo "SI";
		echo "</a></td><td width='50'></td>";
		echo "<td style='text-align:center; font-size:14pt;'><a href='" . $rootpath . "/CustomerReceiptCancel.php?txtfechadesde=" . $fechadesde . "&txtfechahasta=" . $fechahasta . "&txtrecibono=" . $recibono . "&cbounidadnegocio=" . $unidadnegocio . "' style='font-size:14pt'>";
		echo "NO";
		echo "</a></td>";
		
	}
	echo "</tr></table>";
	
	
}else{

echo "<table width='100%' cellpadding='3' cellspacing='3' border='0'>";
echo "<tr>";
echo "<th><b>" . _('Unidad Negocio'). "</b></th>";
echo "<th><b>" . _('Fecha'). "</b></th>";
echo "<th><b>" . _('Folio '). "</b></th>";
echo "<th><b>" . _('Ref. Prov '). "</b></th>";
echo "<th><b>" . _('Cod Proveedor'). "</b></th>";
echo "<th><b>" . _('Proveedor'). "</b></th>";
echo "<th><b>" . _('Sucursal'). "</b></th>";
echo "<th><b>" . _('Referencia'). "</b></th>";
echo "<th><b>" . _('Tipo Docum.'). "</b></th>";
echo "<th><b>" . _('Monto'). "</b></th>";
echo "<th><b>" . _('IVA'). "</b></th>";
echo "<th><b>" . _('Total'). "</b></th>";
//echo "<th><b><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir Corte Caja') . "' alt=''></b></th>";
//echo "<th><b><img src='part_pics/Delete.png' border=0 title='" . _('Cancelar Recibo') . "' alt=''></b></th>";
echo "</tr>";

$SQL = "SELECT supptrans.tagref,
		supptrans.transno,
		supptrans.supplierno,
		supptrans.supplierno as branchcode,
		supptrans.suppreference as reference,
		abs(supptrans.ovamount) as ovamount,
		supptrans.transtext as invtext,
		abs(supptrans.ovgst) as ovgst,
		abs(supptrans.alloc) as alloc,
		tags.tagdescription,
		suppliers.supplierid as supplierno,
		suppliers.suppname as name,
		supptrans.trandate,
		day(supptrans.trandate) as daytrandate,
		month(supptrans.trandate) as monthtrandate,
		year(supptrans.trandate) as yeartrandate,
		 case when instr(folio,'|')>0 then folio else concat(type,'-',transno) end as folio,
		supptrans.id,
		legalbusinessunit.taxid,
		legalbusinessunit.address5,
		tags.tagdescription,
		tags.typeinvoice,
		supptrans.id as recibo,
		systypescat.typename
FROM  tags INNER JOIN supptrans on supptrans.tagref=tags.tagref
INNER JOIN systypescat on systypescat.typeid=supptrans.type
, sec_unegsxuser, suppliers, legalbusinessunit  
";



$SQL = $SQL . " 
WHERE supptrans.type IN('480','22','24','121',501,20)
AND supptrans.tagref = tags.tagref
and supptrans.supplierno = suppliers.supplierid
and supptrans.trandate between  STR_TO_DATE('" . $fechaini . "', '%Y-%m-%d')
and STR_TO_DATE('" . $fechafin . "', '%Y-%m-%d')
and sec_unegsxuser.tagref = tags.tagref
and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' 
and legalbusinessunit.legalid = tags.legalid";

//and tags.areacode = " . $_SESSION['DefaultArea'] . "
if($_POST['cbTipodocumento'] != '' and $_POST['cbTipodocumento'] != 'otros'){
	$SQL = $SQL . " and systypescat.typeid = '" . $_POST['cbTipodocumento'] . "'";
}else if($_POST['cbTipodocumento'] == 'otros'){
	$SQL = $SQL . " and systypescat.typeid NOT IN('480','22','24','121',501)";
}

if ($recibono != ''){
	$SQL = $SQL . " and supptrans.transno = " . $recibono;
}
if ($unidadnegocio != 0){
	$SQL = $SQL . " and supptrans.tagref = " . $unidadnegocio;
}

if ($prov != ''){
	$SQL = $SQL . " and supptrans.supplierno = '" . $prov."'";
}

if($legalid != '') {
	$SQL .= " AND legalbusinessunit.legalid = '$legalid'";
}


$SQL = $SQL . " order by supptrans.tagref,supptrans.folio, supptrans.trandate";

//echo '<pre><br>'.$SQL;

$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);
$k = 1;

$totalIVAAplicado = 0;

$subtotalPago = 0;
$ivaPago = 0;
$totalPago = 0;

$subtotalApli = 0;
$ivaApli = 0;
$totalApli = 0;

while ($myrow=DB_fetch_array($TransResult)) {
	//if (($myrow['ovamount']  + $myrow['ovgst']) != 0){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$tipofacturacionxtag=$myrow['typeinvoice'];
		
		
		
		echo "<td style='font-size:7pt;'>" . $myrow['tagdescription'] . "</td>";
		echo "<td style='font-size:7pt;' nowrap>" . $myrow['daytrandate'] . " - " . glsnombremescorto($myrow['monthtrandate']) . " - " . $myrow['yeartrandate'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['folio'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['reference'] . "</td>";
		echo "<td style='font-size:7pt; text-align:center;'>" . $myrow['supplierno'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['name'] . "</td>";
		echo "<td style='font-size:7pt; text-align:left;'>" . $myrow['branchcode'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['invtext'] . "</td>";
		echo "<td style='font-size:7pt;'>" . $myrow['typename'] . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrow['ovamount'],2) . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrow['ovgst'],2) . "</td>";
		echo "<td style='font-size:7pt; text-align:right;'>" . number_format(($myrow['ovamount']  + $myrow['ovgst']),2) . "</td>";
		
		$subtotalPago += $myrow['ovamount'];
		$ivaPago += $myrow['ovgst'];
		$totalPago += $myrow['ovamount']  + $myrow['ovgst'];
		
		
	
		
		echo "</tr>";
		// while de la factura
		$sqlfactura="SELECT  case when instr(folio,'|')>0 then folio else concat(type,'-',transno) end as folio,
				((amt/rate)+abs(diffonexch)) as amt,tagdescription,trandate,transno,transtext as invtext,typename,day(trandate) as daytrandate,
				month(trandate) as monthtrandate,year(trandate) as yeartrandate,typename,supptrans.suppreference,
				supptrans.ovgst/supptrans.ovamount as porciva
			     FROM supptrans INNER JOIN suppallocs on suppallocs.transid_allocto=supptrans.id
				INNER JOIN tags on tags.tagref=supptrans.tagref
				INNER JOIN systypescat on systypescat.typeid=supptrans.type
			     where  suppallocs.transid_allocfrom=".$myrow['recibo'];
		$resultbanco=DB_query($sqlfactura,$db, $ErrMsg);
		while ($myrowfacturas=DB_fetch_array($resultbanco)){
			echo "<tr bgcolor=#F7F8E0>";
			echo "<td style='font-size:7pt;'>" . $myrowfacturas['tagdescription'] . "</td>";
			echo "<td style='font-size:7pt;' nowrap>" . $myrowfacturas['daytrandate'] . " - " . glsnombremescorto($myrowfacturas['monthtrandate']) . " - " . $myrowfacturas['yeartrandate'] . "</td>";
			echo "<td style='font-size:7pt; text-align:center;'>" . $myrowfacturas['folio'] . "</td>";
			echo "<td style='font-size:7pt; text-align:center;'>" . $myrowfacturas['suppreference'] . "</td>";
			echo "<td style='font-size:7pt;' colspan=3>" . $myrowfacturas['invtext'] . "</td>";
			echo "<td style='font-size:7pt;' colspan=2>Ad24". $myrowfacturas['typename'] . "</td>";
			echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrowfacturas['amt']/(1+$myrowfacturas['porciva']),2) . "</td>";
			echo "<td style='font-size:7pt; text-align:right;'>" . number_format($myrowfacturas['amt']/(1+$myrowfacturas['porciva'])*$myrowfacturas['porciva'],2) . "</td>";
			echo "<td style='font-size:7pt; text-align:right;'>" . number_format(($myrowfacturas['amt']),2) . "</td>";
			//echo "<td colspan=2 style='font-size:7pt; text-align:right;'></td>";
			echo "<tr>";
			
			$subtotalApli += $myrowfacturas['amt']/(1+$myrowfacturas['porciva']);
			$ivaApli += $myrowfacturas['amt']/(1+$myrowfacturas['porciva'])*$myrowfacturas['porciva'];
			$totalApli += $myrowfacturas['amt'];
			
			$totalIVAAplicado += $myrowfacturas['amt']/(1+$myrowfacturas['porciva'])*$myrowfacturas['porciva'];
			
		}
		echo "<tr>";
			echo "<td colspan=13 style='font-size:9pt; text-align:center;' ><b><hr border=0 height=1px color=#FF0000></b></td>";
			echo "</tr>";
       $Totalmonto=$Totalmonto+$myrow['ovamount'] ;
       $Totaliva=$Totaliva+$myrow['ovgst'];
		
		
} 

echo "<tr class='EvenTableRows'>";
echo "<td colspan=9  style='font-size:11pt;text-align:right;'><b>" . _('Totales Pagos') . "</b></td>";
echo "<td style='font-size:11pt; text-align:right;'><b>$" . number_format($subtotalPago,2) . "</b></td>";
echo "<td style='font-size:11pt; text-align:right;'><b>$" . number_format($ivaPago,2) . "</b></td>";
echo "<td style='font-size:11pt; text-align:right;'><b>$" . number_format(($totalPago),2) . "</b></td>";
echo "<tr>";

echo "<tr bgcolor='#F7F8E0'>";
echo "<td colspan=9  style='font-size:11pt;text-align:right;'><b>" . _('Totales Facturas') . "</b></td>";
echo "<td style='font-size:11pt; text-align:right;'><b>$" . number_format($subtotalApli,2) . "</b></td>";
echo "<td style='font-size:11pt; text-align:right;'><b>$" . number_format($ivaApli,2) . "</b></td>";
echo "<td style='font-size:11pt; text-align:right;'><b>$" . number_format(($totalApli),2) . "</b></td>";
echo "<tr>";

echo "<tr>";
echo "<th colspan=9  style='font-size:11pt;text-align:right;'><b>" . _('Pendiente de Aplicar') . "</b></th>";
echo "<th style='font-size:11pt; text-align:right;'><b>$" . number_format($subtotalPago-$subtotalApli,2) . "</b></th>";
echo "<th style='font-size:11pt; text-align:right;'><b>$" . number_format($ivaPago-$ivaApli,2) . "</b></th>";
echo "<th style='font-size:11pt; text-align:right;'><b>$" . number_format(($totalPago-$totalApli),2) . "</b></th>";
//echo "<td colspan=2 style='font-size:7pt; text-align:right;'></td>";
echo "<tr>";

/*
echo "<tr>";
echo "<th colspan=9  style='font-size:11pt;text-align:right;'><b>" . _('Totales') . "</b></th>";
echo "<th style='font-size:11pt; text-align:right;'><b>$" . number_format($Totalmonto-$totalIVAAplicado,2) . "</b></th>";
echo "<th style='font-size:11pt; text-align:right;'><b>$" . number_format($totalIVAAplicado,2) . "</b></th>";
echo "<th style='font-size:11pt; text-align:right;'><b>$" . number_format(($Totaliva+$Totalmonto),2) . "</b></th>";
//echo "<td colspan=2 style='font-size:7pt; text-align:right;'></td>";
echo "<tr>";
*/

echo "</table>";
}
echo "<br><br>";
echo '</form>';

?>

	