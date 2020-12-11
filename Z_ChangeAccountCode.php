<?php
/* $Revision: 1.11 $ */
/*Script to Delete all sales transactions*/
/*
FECHA CREACION: JUEVES 11 DE NOVIEMBRE
*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('PAGINA DE UTILERIA PARA UNIFICAR CUENTAS CONTABLES');
include('includes/header.inc');

$funcion=21;
include('includes/SecurityFunctions.inc');

/****** SELECCION DE cuenta ORIGEN *****/
	
echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>"; 
	echo "<table border='0'>";
	echo "<tr>";
	echo "<br><td ><br>";
		
		echo "&nbsp;&nbsp;&nbsp;<b></b>"._('Cuenta Origen: ') . "&nbsp;&nbsp;";
		echo "<select name='OldAccountNo' style='font-size:9pt;align='center'>";
			echo "<option selected value=''>Seleccione</option>";
			$sql = "SELECT accountcode,accountname
				FROM chartmaster
				ORDER BY accountcode";
			$resultOld = DB_query($sql,$db,'','');
		
			while ($xmyrow=DB_fetch_array($resultOld))
			{
				if ($xmyrow['accountcode'] == $_POST['accountname']) {
					echo "<option selected Value='" . $xmyrow['accountcode'] . "'>" . $xmyrow['accountcode'].'-'.$xmyrow['accountname'] .'</option>';
				}
				else {
					echo "<option Value='" . $xmyrow['accountcode'] . "'>" .$xmyrow['accountcode'].'-'. $xmyrow['accountname'] .'</option>';
				}
			}
		echo "</select></td>";
		/****** SELECCION DE cuenta DESTINO *****/
		echo "<td><br>";
		
		echo "&nbsp;&nbsp;&nbsp;<b></b>"._('Cuenta Destino: ') . "&nbsp;&nbsp;";
		echo "<select name='NewAccountNo' style='font-size:9pt;'>";
		echo "<option selected value=''>Seleccione</option>";
		$sql = "SELECT accountcode,accountname
				FROM chartmaster
				ORDER BY accountcode";
		$resultNew = DB_query($sql,$db,'','');

	while ($xmyrow=DB_fetch_array($resultNew))
	{
		if ($xmyrow['accountcode'] == $_POST['accountcode']) {
			echo "<option selected Value='" . $xmyrow['accountcode'] . "'>" .$xmyrow['accountcode'].'-'. $xmyrow['accountname'] .'</option>';
		}
		else {
			echo "<option Value='" . $xmyrow['accountcode'] . "'>" .$xmyrow['accountcode'].'-'. $xmyrow['accountname'] .'</option>';
		}
	}
	echo "</select></td></tr>";
	
	echo "<tr align:'center'><td colspan=2><br><input type=submit name='ProcessAccountChange' VALUE='" . _('Procesar Cambio...') . "'></td></tr>";
	
	echo '</table>';
	
if (isset($_POST['ProcessAccountChange'])){

/*First check the account code exists */
	$result=DB_query("SELECT accountcode, tipo FROM chartmaster WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg ('<br><br>' . _('El codigo de la cuenta') . ': ' . $_POST['OldAccountNo'] . ' ' . _('no existe actualmente en la base de datos del sistema'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$tipoOrigen = $myrow['tipo'];
	}


	if ($_POST['NewAccountNo']==''){
		prnMsg(_('El codigo de la cuenta destino debe de ser seleccionada'),'error');
		include('includes/footer.inc');
		exit;
	}
	
	
/*Now check that the new code also exist */
	$result=DB_query("SELECT accountcode, tipo FROM chartmaster WHERE accountcode='" . $_POST['NewAccountNo'] . "'",$db);
	if (DB_num_rows($result)==0){
		prnMsg(_('El codigo de proveedor de reemplazo') .': ' . $_POST['NewAccountNo'] . ' ' . _('no existe actualmente en la base de datos del sistema') . ' - ' . _('este codigo debe de existir en el sistema antes de migrar movimientos de otro proveedor...'),'error');
		include('includes/footer.inc');
		exit;
	} else {
		$myrow=DB_fetch_row($result);
		$tipoDestino = $myrow['tipo'];
	}
	
	
	if ($tipoDestino != $tipoOrigen){
		prnMsg(_('El tipo de cuenta  origen es ') .': ' . $tipoOrigen . ' y del destino es ' . $tipoDestino . ' - ' . _(' los tipos de cuentas deben de ser las mismas para poder traspasar movimientos...'),'error');
		include('includes/footer.inc');
		exit;
	}

	$result = DB_Txn_Begin($db);

	//accountxsupplier
	/*
	accountxsupplier.accountcode 
	accountxsupplier.supplierid 
	accountxsupplier.concepto 
	*/
	
	
	$sql = "DELETE FROM RePostGL WHERE  accountcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update accountxsupplier transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Eliminando registros de cuentas para reposteo...'),'info');
	
	//$sql = "DELETE FROM RePostGL WHERE  accountcode='" . $_POST['OldAccountNo'] . "'";
	$sql = "UPDATE ReembolsoPagoEncabezado SET glcode='" . $_POST['NewAccountNo'] . "' WHERE glcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update accountxsupplier transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de reembolso de pagos...'),'info');
	
	
	$sql = "UPDATE banktrans SET bankact='" . $_POST['NewAccountNo'] . "' WHERE bankact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update accountxsupplier transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de transacciones de bancos de ERP...'),'info');
	
	
	$sql = "UPDATE accountxsupplier SET accountcode='" . $_POST['NewAccountNo'] . "' WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update accountxsupplier transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Cambiando registros de cuentas por proveeedor...'),'info');
	
	//bankaccounts
	/*
	bankaccounts.accountcode 
	bankaccounts.currcode 
	bankaccounts.invoice 
	bankaccounts.bankaccountcode 
	bankaccounts.bankaccountname 
	bankaccounts.bankaccountnumber 
	bankaccounts.bankaddress 
	bankaccounts.tagref 

	*/
	$result=DB_query("SELECT accountcode FROM bankaccounts WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
	$sql = "DELETE FROM bankaccounts WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old Accountcode record failed');

	prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	//chartbridge
	/*
	chartbridge.accountcode 
	chartbridge.concept 
	*/
	$result=DB_query("SELECT accountcode FROM chartbridge WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
	$sql = "DELETE FROM chartbridge WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old Accountcode record failed');

	prnMsg(_('Borrando cuenta anterior de chartbridge...'),'info');
	}
	
	$result=DB_query("SELECT accountcode FROM chartbridge_supp WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$sql = "DELETE FROM chartbridge_supp WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	
		prnMsg(_('Borrando cuenta anterior de chartbridge_supp...'),'info');
	}
	
	$result=DB_query("SELECT accountcode FROM chartbridgecargos_supp WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$sql = "DELETE FROM chartbridgecargos_supp WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	
		prnMsg(_('Borrando cuenta anterior de chartbridgecargos_supp...'),'info');
	}
	
	
	
	//gltrans
	/*
	gltrans.counterindex 
	gltrans.type 
	gltrans.typeno 
	gltrans.chequeno 
	gltrans.trandate 
	gltrans.periodno 
	gltrans.account 
	gltrans.narrative 
	gltrans.amount 
	gltrans.posted 
	gltrans.jobref 
	gltrans.tag0 
	gltrans.lasttrandate
	*/

	$sql = "UPDATE gltrans SET account ='" . $_POST['NewAccountNo'] . "' WHERE account='" . $_POST['OldAccountNo'] . "'";

	$ErrMsg = _('The SQL to update shipments transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros en movimientos...'),'info');
	
	//prorrxuneg
	/*
	prorrxuneg.prorrxunegid 
	prorrxuneg.prorrateoid 
	prorrxuneg.tagref0 
	prorrxuneg.porcentaje 
	prorrxuneg.account
	*/ 

	$sql = "UPDATE prorrxuneg SET account='" . $_POST['NewAccountNo'] . "' WHERE account='" . $_POST['OldAccountNo'] . "'";

	$ErrMsg = _('The SQL to update prorrxuneg transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Cambiando registros ...'),'info');
	
	//chartdetails
	/*
	chartdetails.accountcode 
	chartdetails.period 
	chartdetails.tagref0 
	chartdetails.budget 
	chartdetails.actual 
	chartdetails.bfwd 
	chartdetails.bfwdbudget 
	chartdetails.cargos 
	chartdetails.abonos 
	*/
	$sql ="SELECT period,tagref,actual,bfwd,cargos,abonos FROM chartdetails WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$result = DB_query($sql,$db,$ErrMsg);
	
	while ($myrow=DB_fetch_array($result))
	{
		
	$Sql="SELECT period,tagref,actual,bfwd,cargos,abonos FROM chartdetails WHERE accountcode='" . $_POST['NewAccountNo'] . "'
		AND period= '".$myrow['period']."' AND tagref='".$myrow['tagref']."'";
	$resultNew=DB_query($Sql,$db,$ErrMsg);
	
	$myrowNew=DB_fetch_array($resultNew);
	$actualnuevo=$myrow['actual']+$myrowNew['actual'];
	$bfwdnuevo=$myrow['bfwd']+$myrowNew['bfwd'];
	$cargosnuevo=$myrow['cargos']+$myrowNew['cargos'];
	$abonosnuevo=$myrow['abonos']+$myrowNew['abonos'];
	
	$sql = "UPDATE chartdetails SET actual = '" . $actualnuevo . "',bfwd='" . $bfwdnuevo . "',cargos='" . $cargosnuevo . "',abonos='" . $abonosnuevo . "'
		WHERE accountcode='" . $_POST['NewAccountNo'] . "' AND tagref='".$myrow['tagref']."' AND period='".$myrow['period']."'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	}
	
	prnMsg(_('Cambiando registros de proveedor...'),'info');
	
	$sql = "DELETE FROM chartdetails WHERE accountcode='" . $_POST['OldAccountNo'] . "'";

	$ErrMsg = _('The SQL to DELETE old suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);

	prnMsg(_('Borrando cuenta anterior...'),'info');
	
	//chartdrop
	/*
	chartdrop.accountcode 
	chartdrop.concept 
	*/
	
	$result=DB_query("SELECT accountcode FROM chartdrop WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
	$sql = "DELETE FROM chartdrop WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old Accountcode record failed');

	prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	//chartdrop_supp
	/*
	chartdrop_supp.accountcode 
	chartdrop_supp.concept 
	*/
	$result=DB_query("SELECT accountcode FROM chartdrop_supp WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
	$sql = "DELETE FROM chartdrop_supp WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
	//$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old Accountcode record failed');

	prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	//chartlog
	/*
	chartlog.id 
	chartlog.accountcode 
	chartlog.trandate 
	chartlog.narrative 
	chartlog.line 
	*/
	
	//chartmaster
	/*
	chartmaster.accountcode 
	chartmaster.accountname 
	chartmaster.group_ 
	chartmaster.naturaleza 
	chartmaster.tipo 
	chartmaster.accountnameing 
	chartmaster.sectionnameing 
	*/
	
	$result=DB_query("SELECT accountcode FROM chartinternalinvoice WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$sql = "DELETE FROM chartinternalinvoice WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	
		prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	
	$result=DB_query("SELECT accountcode FROM chartspecialaccounts WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$sql = "DELETE FROM chartspecialaccounts WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	
		prnMsg(_('Borrando de chartspecialaccounts...'),'info');
	}
	
	
	
	//chartdebtortype
	/*
	chartdebtortype.typedebtorid 
	chartdebtortype.gl_accountsreceivable 
	chartdebtortype.gl_notesreceivable 
	chartdebtortype.gl_debtoradvances 
	chartdebtortype.gl_debtormoratorio 
	chartdebtortype.gl_accountcontado 
	chartdebtortype.gl_taxdebtoradvances 
	chartdebtortype.gl_debitnote 
	*/
	$sql = "UPDATE chartdebtortype SET gl_accountsreceivable = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_accountsreceivable='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype SET gl_notesreceivable='" . $_POST['NewAccountNo'] . "'
	WHERE gl_notesreceivable='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype SET gl_debtoradvances='" . $_POST['NewAccountNo'] . "'
	WHERE gl_debtoradvances='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype SET gl_debtormoratorio='" . $_POST['NewAccountNo'] . "'
	WHERE gl_debtormoratorio='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype SET gl_accountcontado='" . $_POST['NewAccountNo'] . "'
	WHERE gl_accountcontado='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype SET gl_taxdebtoradvances='" . $_POST['NewAccountNo'] . "'
	WHERE gl_taxdebtoradvances='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype SET gl_debitnote='" . $_POST['NewAccountNo'] . "'
	WHERE gl_debitnote='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype 
			SET gl_accountprovisional='" . $_POST['NewAccountNo'] . "'
			WHERE gl_accountprovisional='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE chartdebtortype
			SET gl_accountret='" . $_POST['NewAccountNo'] . "'
			WHERE gl_accountret='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	
	$result=DB_query("SELECT accountcode FROM chartdetailsbudget WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$sql = "DELETE FROM chartdetailsbudget WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	
		prnMsg(_('Borrando de tabla chartdetailsbudget...'),'info');
	}
	
	$result=DB_query("SELECT accountcode FROM chartdetailsbudgetbytag WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
		$sql = "DELETE FROM chartdetailsbudgetbytag WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	
		prnMsg(_('Borrando de tabla chartdetailsbudget...'),'info');
	}
	
	
	//tagsxbankaccounts
	/*
	tagsxbankaccounts.tagref
	tagsxbankaccounts.accountcode 
	*/
	$sql = "UPDATE tagsxbankaccounts SET accountcode='" . $_POST['NewAccountNo'] . "'
	WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//taxauthorities
	/*
	taxauthorities.taxid 
	taxauthorities.description 
	taxauthorities.taxglcode 
	taxauthorities.purchtaxglaccount 
	taxauthorities.bank 
	taxauthorities.bankacctype 
	taxauthorities.bankacc 
	taxauthorities.bankswift 
	taxauthorities.taxglcodePaid 
	taxauthorities.purchtaxglaccountPaid 
	*/
	$sql = "UPDATE taxauthorities SET taxglcode = '" . $_POST['NewAccountNo'] . "'
		WHERE taxglcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities SET purchtaxglaccount = '" . $_POST['NewAccountNo'] . "'
		WHERE purchtaxglaccount='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities SET bank = '" . $_POST['NewAccountNo'] . "'
		WHERE bank='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities SET bankacctype = '" . $_POST['NewAccountNo'] . "'
		WHERE bankacctype='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities SET bankacc = '" . $_POST['NewAccountNo'] . "'
		WHERE bankacc='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities SET bankswift = '" . $_POST['NewAccountNo'] . "'
		WHERE bankswift='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities SET taxglcodePaid = '" . $_POST['NewAccountNo'] . "'
		WHERE taxglcodePaid='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE taxauthorities 
			SET purchtaxglaccountPaid = '" . $_POST['NewAccountNo'] . "'
			WHERE purchtaxglaccountPaid='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE taxauthorities
			SET taxglcoderet = '" . $_POST['NewAccountNo'] . "'
			WHERE taxglcoderet='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE taxauthorities
			SET taxglcodediscountpaid = '" . $_POST['NewAccountNo'] . "'
			WHERE taxglcodediscountpaid='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE taxauthorities
			SET taxglcodediscount = '" . $_POST['NewAccountNo'] . "'
			WHERE taxglcodediscount='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Actualizando  tabla taxauthorities...'),'info');
	
	
	
	//chartpays
	/*
	chartpays.accountcode
	chartpays.concept
	*/
	$result=DB_query("SELECT accountcode FROM chartpays WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
	$sql = "DELETE FROM chartpays WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old Accountcode record failed');

	prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	//chartpurch
	/*
	chartpurch.accountcode
	chartpurch.concept
	*/
	$result=DB_query("SELECT accountcode FROM chartpurch WHERE accountcode='" . $_POST['OldAccountNo'] . "'",$db);
	if (DB_num_rows($result)!=0){
	$sql = "DELETE FROM chartpurch WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
	prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	//
	/*
	chartsupplierstype.typedebtorid
	chartsupplierstype.gl_accountsreceivable
	chartsupplierstype.gl_notesreceivable
	chartsupplierstype.gl_debtoradvances
	chartsupplierstype.gl_debtormoratorio
	chartsupplierstype.gl_accountcontado
	chartsupplierstype.gl_taxdebtoradvances
	chartsupplierstype.gl_debitnote
	*/
	$sql = "UPDATE chartsupplierstype SET gl_accountsreceivable = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_accountsreceivable='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE chartsupplierstype SET gl_notesreceivable = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_notesreceivable='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE chartsupplierstype SET gl_debtoradvances = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_debtoradvances='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE chartsupplierstype SET gl_debtormoratorio = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_debtormoratorio='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE chartsupplierstype SET gl_accountcontado = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_accountcontado='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE chartsupplierstype SET gl_taxdebtoradvances = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_taxdebtoradvances='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	$sql = "UPDATE chartsupplierstype SET gl_debitnote = '" . $_POST['NewAccountNo'] . "'
		WHERE gl_debitnote='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	//
	/*
	chartlog.id
	chartlog.accountcode
	chartlog.trandate
	chartlog.narrative
	chartlog.line
	*/
	$sql = "UPDATE chartlog SET accountcode = '" . $_POST['NewAccountNo'] . "'
		WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	//POR ULTIMO SE BORRA DE LA TABLA DE CHARTMASTER LA CUENTA ANTERIOR
	/*
	chartmaster.accountcode
	chartmaster.accountname
	chartmaster.group_
	chartmaster.naturaleza
	chartmaster.tipo
	chartmaster.accountnameing
	chartmaster.sectionnameing 
	*/
	
	
	$sql = "UPDATE cogsglpostings SET glcode='" . $_POST['NewAccountNo'] . "' WHERE glcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update accountxsupplier transaction records failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de interfase de costo...'),'info');
	
	$sql = "UPDATE companies 
			SET debtorsact = '" . $_POST['NewAccountNo'] . "'
			WHERE debtorsact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET pytdiscountact = '" . $_POST['NewAccountNo'] . "'
			WHERE pytdiscountact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET creditorsact = '" . $_POST['NewAccountNo'] . "'
			WHERE creditorsact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET payrollact = '" . $_POST['NewAccountNo'] . "'
			WHERE payrollact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET grnact = '" . $_POST['NewAccountNo'] . "'
			WHERE grnact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET exchangediffact = '" . $_POST['NewAccountNo'] . "'
			WHERE exchangediffact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET purchasesexchangediffact = '" . $_POST['NewAccountNo'] . "'
			WHERE purchasesexchangediffact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE companies
			SET freightact = '" . $_POST['NewAccountNo'] . "'
			WHERE freightact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de companies...'),'info');
	
	$sql = "UPDATE fjoSubCategory
			SET accountcode = '" . $_POST['NewAccountNo'] . "'
			WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de fjoSubCategory...'),'info');
	
	
	$sql = "UPDATE lastcostrollup
			SET stockact = '" . $_POST['NewAccountNo'] . "'
			WHERE stockact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE lastcostrollup
			SET adjglact = '" . $_POST['NewAccountNo'] . "'
			WHERE adjglact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Actualizando tabla de lastcostrollup...'),'info');
	
	$sql = "UPDATE notescreditorders
			SET account = '" . $_POST['NewAccountNo'] . "'
			WHERE account='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Actualizando tabla de notescreditorders...'),'info');
	
	$sql = "UPDATE purchbudgetdetails
			SET accountcode = '" . $_POST['NewAccountNo'] . "'
			WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de purchbudgetdetails...'),'info');
	
	$sql = "UPDATE purchorderauth
			SET account = '" . $_POST['NewAccountNo'] . "'
			WHERE account='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de purchorderauth...'),'info');
	
	$sql = "UPDATE purchorderdetails
			SET glcode = '" . $_POST['NewAccountNo'] . "'
			WHERE glcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de purchorderdetails...'),'info');
	
	$sql = "UPDATE salesglpostings
			SET discountglcode = '" . $_POST['NewAccountNo'] . "'
			WHERE discountglcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE salesglpostings
			SET salesglcode = '" . $_POST['NewAccountNo'] . "'
			WHERE salesglcode='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE salesglpostings
			SET salesgldiscount = '" . $_POST['NewAccountNo'] . "'
			WHERE salesgldiscount='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE salesglpostings
			SET salesglprovision = '" . $_POST['NewAccountNo'] . "'
			WHERE salesglprovision='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE salesglpostings
			SET salesglprovisiondesc = '" . $_POST['NewAccountNo'] . "'
			WHERE salesglprovisiondesc='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Actualizando tabla de salesglpostings...'),'info');
	
	$sql = "UPDATE salesman
			SET glaccountsalesprov = '" . $_POST['NewAccountNo'] . "'
			WHERE glaccountsalesprov='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE salesman
			SET glaccountsales = '" . $_POST['NewAccountNo'] . "'
			WHERE glaccountsales='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de salesman...'),'info');
	
	$sql = "UPDATE shippers
			SET international_account = '" . $_POST['NewAccountNo'] . "'
			WHERE international_account='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE shippers
			SET national_account = '" . $_POST['NewAccountNo'] . "'
			WHERE national_account='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Actualizando tabla de shippers...'),'info');
	
	
	$sql = "UPDATE shiptypecost
			SET account = '" . $_POST['NewAccountNo'] . "'
			WHERE account='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	prnMsg(_('Actualizando tabla de shiptypecost...'),'info');
	
	$sql = "UPDATE shiptypecostaccount
			SET account = '" . $_POST['NewAccountNo'] . "'
			WHERE account='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	prnMsg(_('Actualizando tabla de shiptypecostaccount...'),'info');
	
	
	$sql = "UPDATE stockcategory
			SET stockact = '" . $_POST['NewAccountNo'] . "'
			WHERE stockact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET adjglact = '" . $_POST['NewAccountNo'] . "'
			WHERE adjglact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET purchpricevaract = '" . $_POST['NewAccountNo'] . "'
			WHERE purchpricevaract='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET wipact = '" . $_POST['NewAccountNo'] . "'
			WHERE wipact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET adjglacttransf = '" . $_POST['NewAccountNo'] . "'
			WHERE adjglacttransf='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET stockconsignmentact = '" . $_POST['NewAccountNo'] . "'
			WHERE stockconsignmentact='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET materialuseagevarac = '" . $_POST['NewAccountNo'] . "'
			WHERE materialuseagevarac='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET glcodebydelivery = '" . $_POST['NewAccountNo'] . "'
			WHERE glcodebydelivery='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	$sql = "UPDATE stockcategory
			SET stockshipty = '" . $_POST['NewAccountNo'] . "'
			WHERE stockshipty='" . $_POST['OldAccountNo'] . "'";
	$ErrMsg = _('The SQL to update suppliers record failed');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
	
	
	prnMsg(_('Actualizando tabla de stockcategory...'),'info');
	
	$sql="SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
	$resultx=DB_query($sql,$db);
	
	if (DB_num_rows($resultx)!=0){
		$sql = "DELETE FROM chartmaster WHERE accountcode='" . $_POST['OldAccountNo'] . "'";
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg,true);
		$ErrMsg = _('The SQL to DELETE old Accountcode record failed');
		prnMsg(_('Borrando cuenta anterior...'),'info');
	}
	
	$result = DB_Txn_Commit($db);
}

/*echo "<form action='" . $_SERVER['PHP_SELF'] . "?=" . $SID . "' method=post>";

echo '<p><table>
	<tr><td>' . _('Codigo de Cuenta Origen') . ":</td>
		<td><input type=Text name='OldAccountNo' size=20 maxlength=20></td>
	</tr>";
echo '<tr><td> ' . _('Codigo de Cuenta Destino') . ":</td>
	<td><input type=Text name='NewAccountNo' size=20 maxlength=20></td>
	</tr>
	</table>";

echo "<input type=submit name='ProcessAccountChange' VALUE='" . _('Procesar Cambio...') . "'>";

echo '</form>';*/
echo '</form>';
include('includes/footer.inc');

?>