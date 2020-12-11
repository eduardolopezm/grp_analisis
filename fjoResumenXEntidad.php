
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

$title = _('Resumen X Entidad');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

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


if (isset($_POST['legalid'])) {
	$thislegalid = $_POST['legalid'];
	//echo $_POST['thislegalid'];
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

	/************************************/
	/* SELECCION DEL RAZON SOCIAL */
	echo '<tr><td style="text-align:right"><b>' . _('X Razon Social') . ':</b></td><td>
		<select name="legalid">';

	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
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

	echo '</table>
		<br><div class="centre"><input tabindex="6" type=submit name="ReportePantalla" value="' . _('Consulta Flujo') . '">&nbsp;&nbsp;';
	echo '<input tabindex="7" type=submit name="PrintPDF" value="' . _('Imprime Archivo PDF') . '"></div><br>';
	echo "</form>";

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

	echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'><table>";
	echo '<input Name="FromMes" type=hidden value="'.$FromMes.'">';

	echo '<input type=hidden id=legallist name=thislegalid value="'.$thislegalid.'">';

	echo '<br><table cellspacing=0 border=1 bordercolor=white cellpadding=2 colspan="7">';

	//** DIBUJA PRIMERA LINEA ENCABEZADO

	echo "<TR style='background-color:#DE8E92;'>";
	echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:center' width=220><b>LOTE</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:center' width=120><b>$ VENTA</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:center' width=120><b>$ PAGADO</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:center' width=120><b>$ X COBRAR</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:center' width=120><b>$ TOTAL</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:center' width=180><b>VENDEDOR</b></TD>";

	echo "</TR>";


	$sql = "select 	E.Nombre, E.doble1 as PrecioVenta,
			sum(CASE Confirmado WHEN 0 THEN M.abono - M.cargo ELSE 0 END) as Pagado,
			sum(CASE Confirmado WHEN 1 THEN M.abono - M.cargo ELSE 0 END) as PorCobrar,
			sum(M.abono - M.cargo) as Total, E.Campo2 as Vendedor
		from 	Movimientos M JOIN usrEntidades E ON E.u_entidad = M.u_entidad
		where 	M.u_empresa = ". $thislegalid ." and activo = 1 AND erp = 0
		group by E.Nombre, E.doble1, E.Campo2
		Order by E.Nombre ASC";


	$result = DB_query($sql,$db);

	$totVenta = 0;
	$totPagado = 0;
	$totXCobrar = 0;
	$totTotal = 0;

	$i =0;
	while ($rMovimientos = DB_fetch_array($result)) {

		if ($i==0) {
			print "<tr style='background-color:#B1CDF0'>";
			$i = 1;
		} else {
			print "<tr style='background-color:#FFFFFF'>";
			$i = 0;
		}

		echo "<TD align=center style='font-size:12px;font-weight:normal;text-align:left'><b>".$rMovimientos['Nombre']."</b></TD>";
		echo "<TD align=center style='font-size:12px;font-weight:normal;text-align:right'>".number_format($rMovimientos['PrecioVenta'],0)."</TD>";
		echo "<TD align=center style='font-size:12px;font-weight:normal;text-align:right'>".number_format($rMovimientos['Pagado'],0)."</TD>";
		echo "<TD align=center style='font-size:12px;font-weight:normal;text-align:right'>".number_format($rMovimientos['PorCobrar'],0)."</TD>";
		echo "<TD align=center style='font-size:12px;font-weight:normal;text-align:right'>".number_format($rMovimientos['Total'],0)."</TD>";
		echo "<TD align=center style='font-size:12px;font-weight:bold;text-align:left'><b>".$rMovimientos['Vendedor']."</b></TD>";

		echo "</TR>";

		$totVenta = $totVenta + $rMovimientos['PrecioVenta'];
		$totPagado = $totPagado + $rMovimientos['Pagado'];
		$totXCobrar = $totXCobrar + $rMovimientos['PorCobrar'];
		$totTotal = $totTotal + $rMovimientos['Pagado'] + $rMovimientos['PorCobrar'];

	}

	/* SALDOS FINALES */
	print "<TR style='background-color:#000000;height:15px'>";
	print "<TD colspan=6 style='height:15px'></TD>";
	print "</TR>";

	print "<TR style='background-color:#DE8E92'>";
	print "<TD align=left style='font-size:12px;font-weight:bold'>TOTALES</TD>";

	echo "<TD align=center style='font-size:12px;font-weight:bold'><b>".number_format($totVenta,0)."</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold'><b>".number_format($totPagado,0)."</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold'><b>".number_format($totXCobrar,0)."</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold'><b>".number_format($totTotal,0)."</b></TD>";
	echo "<TD align=center style='font-size:12px;font-weight:bold'><b></b></TD>";

	print "</TR>";


	print "</table>";
	print "<p style='font-size:8px'>Impreso el:".DATE('Y-m-d H:m:s').'</p>';


include('includes/footer.inc');
?>
