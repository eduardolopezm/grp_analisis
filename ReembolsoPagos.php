<?php


include('includes/DefinePaymentClass2.php');
include('includes/session.inc');
$funcion=690;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Reembolso de Pagos (UNICAMENTE EN PESOS)');

include('includes/header.inc');

if (isset($_POST['ToYear'])) {
	$ToYear=$_POST['ToYear'];
}elseif(isset($_GET['ToYear'])) {
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
}elseif(isset($_POST['ToDia'])) {
	$ToDia=$_POST['ToDia'];
}else{
	$ToDia=date('d');
}

if ((isset($_GET['tag'])) and ($_GET['tag'] != "")){
	$_POST['tag'] = $_GET['tag'];
}elseif ((!isset($_POST['tag'])) and ($_POST['tag'] == "")){
	$_POST['tag'] = 0;
}

if ((isset($_GET['legalid'])) and ($_GET['legalid'] != "")){
	$_POST['legalid'] = $_GET['legalid'];
}elseif ((!isset($_POST['legalid'])) and ($_POST['legalid'] == "")){
	$_POST['legalid'] = 0;
}


echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Reembolso de Pagos') . '" alt="">' . ' ' . _('Reembolso de Pagos') . '</p>';


/**********************************************/
/*VALIDA SI EL PAGO VA A SER PARA UN PROVEEDOR*/


if ((isset($_POST['actualizar']) and $_POST['actualizar'] <> '') or (isset($_POST['UpdateHeader']) and $_POST['UpdateHeader'] <> '')){
	$error = 0;
	if (isset($_POST['actualizar'])){

		if (strlen($_POST['ToMes'])==1){
			$_POST['ToMes'] = "0" . $_POST['ToMes'];
		}
		if (strlen($_POST['ToDia'])==1){
			$_POST['ToDia'] = "0" . $_POST['ToDia'];
		}

		$trandate = $_POST['ToYear'] . "-" . $_POST['ToMes'] . "-" . $_POST['ToDia'];


		$fecha_actual = date('Y') . "-" . date('m') . "-" . date('d');//date("Y/m/d");
		if ($_SESSION['FutureDate']==1){
			if ($trandate > $fecha_actual and Havepermission($_SESSION['UserID'],410, $db)==0){
				prnMsg(_('La fecha es posterior y no cuenta con los permisos para realizar esta operacion'),'error');
				$error = 1;
			}
		}

		if ($trandate < $fecha_actual and Havepermission($_SESSION['UserID'],776, $db)==0 ){
				prnMsg(_('La fecha es anterior a la actual y no cuenta con los permisos para realizar esta operacion'),'error');
				$error = 1;
		}

		if ($_POST['tag'] == 0){
			prnMsg(_('Selecciona Unidad de Negocio...'),'error');
			$error = 1;
		}
		if ($_POST['BankAccount'] == "0"){
			prnMsg(_('Selecciona cuenta de cheques...'),'error');
			$error = 1;
		}
		/* NO ES NECESARIA ESTA VALIDACION PARA CUANDO SERA UN REEMBOLSO CONTRA UN ACREEDOR PUES ESTO NO FUE CON CHEQUE SINO VA CONTRA SU CUENTA*/
		if ($_POST['ChequeNum'] == "" AND substr($_POST['BankAccount'],0,2) == 'B:' AND $_POST['Paymenttype'] == 'Cheque'){
			prnMsg(_('Captura numero de Cheque...'),'error');
			$error = 1;
		}
		/* NO ES NECESARIA ESTA VALIDACION PARA CUANDO SERA UN REEMBOLSO CONTRA UN ACREEDOR PUES EL BENEFICIARIO ES EL MISMO*/
		if ($_POST['Beneficiario'] == ""  AND substr($_POST['BankAccount'],0,2) == 'B:' AND ( $_POST['Paymenttype'] == 'Cheque' OR  $_POST['Paymenttype'] == 'Transferencia')){
			prnMsg(_('Captura datos del Beneficiario'),'error');
			$error = 1;
		}

		if ($_POST['Narrative'] == ""){
			prnMsg(_('Captura Referecia/Concepto') ,'error');
			$error = 1;
		}
	}


	if ((!isset($_SESSION['PaymentDetail'])) and ($error == 0)){
		$tagref = $_POST['tag'];
		$glcode = $_POST['BankAccount'];

		if (strlen($_POST['ToMes'])==1){
			$_POST['ToMes'] = "0" . $_POST['ToMes'];
		}
		if (strlen($_POST['ToDia'])==1){
			$_POST['ToDia'] = "0" . $_POST['ToDia'];
		}

		$trandate = $_POST['ToYear'] . "-" . $_POST['ToMes'] . "-" . $_POST['ToDia'];


		$currency = $_POST['Currency'];
		$sql ="SELECT currabrev, rate FROM currencies WHERE currabrev='" . $currency . "'";
		$result = DB_query($sql, $db);
		if ($myrow = DB_fetch_array($result,$db)) {
			$rate = $myrow['rate'];
		}

		$paymenttype = $_POST['Paymenttype'];
		$cheque = $_POST['ChequeNum'];
		$beneficiary = $_POST['Beneficiario'];
		$narrative = $_POST['Narrative'];
		$acreedor = $_POST['acreedor'];

		$sql ="Select legalid,areacode, tagref, tagdescription from tags where tagref=" . $_POST['tag'];
		//echo "<br>" . $sql;
		$result = DB_query($sql, $db);
		if ($myrow = DB_fetch_array($result,$db)) {
			$tagname = $myrow['tagdescription'];
			//echo "<br>tagname: " . $tagname;
		}

		$_SESSION['PaymentDetail'] = new Payment;
		$_SESSION['PaymentDetail']->GLItemCounter = 1;
		//echo "<br>fecha:" .  $trandate;
		$_SESSION['PaymentDetail']->Add_To_GLAnalysis($tagref, $glcode, $trandate, $currency, $paymenttype, $cheque, $beneficiary, $narrative, $tagname, $acreedor, $rate);
	}

	if (isset($_POST['UpdateHeader']) and $_POST['UpdateHeader'] <> '') {
		//echo "<br>entra al detalle";
		/*OBTINENE DATOS DEL PROVEEDOR*/
		$SQL= "SELECT suppname
			FROM suppliers
			WHERE supplierid='" . $_POST['SupplierID'] . "'";
		$Result = DB_query($SQL, $db);
		if (DB_num_rows($Result)==0){
			//prnMsg( _('El codigo de Proveedor con el que esta pagina fue llamada, no existe en base de datos de Proveedores') . '. ' . _('Si esta pagina es llamada desde la pagina de Proveedores, esto garantiza que el proveedor existe!'),'warn');
			//include('includes/footer.inc');
			//exit;
		} else {
			$myrow = DB_fetch_array($Result);
			$SuppName = $myrow['suppname'];
		}

		$SupplierID = $_POST['SupplierID'];
		$taxrate = $_POST['TaxCat'];
		$AmountTax = $_POST['mntIVA'];
		$Amount = $_POST['Amount'];

		if ($_POST['Discount'] != ""){
			$Discountsupp = $_POST['Discount'];
		}else{
			$Discountsupp = 0;
		}

		if ($_POST['RetencionIVA'] != ""){
			$RetencionIVA = $_POST['RetencionIVA'];
		}else{
			$RetencionIVA = 0;
		}
		if ($_POST['RetencionISR'] != ""){
			$RetencionISR = $_POST['RetencionISR'];
		}else{
			$RetencionISR = 0;
		}

		if ($_POST['RetencionxIVAArrendamiento'] != ""){
			$RetencionxArrendamiento = $_POST['RetencionxIVAArrendamiento'];
		}else{
			$RetencionxArrendamiento = 0;
		}

		if ($_POST['RetencionxComisiones'] != ""){
			$RetencionxComisiones = $_POST['RetencionxComisiones'];
		}else{
			$RetencionxComisiones = 0;
		}

		if ($_POST['RetencionxFletes'] != ""){
			$RetencionxFletes = $_POST['RetencionxFletes'];
		}else{
			$RetencionxFletes = 0;
		}

		if ($_POST['RetencionxCedular'] != ""){
			$RetencionxCedular = $_POST['RetencionxCedular'];
		}else{
			$RetencionxCedular = 0;
		}

		if ($_POST['tagMovto'] != "0"){
			$tagMovto = $_POST['tagMovto'];
		} else {
			$tagMovto = 0;
		}


		$descripciongasto = $_POST['descripciongasto'];
		$referencia = $_POST['referencia'];
		$ctaproveedor = $_POST['ctaproveedor'];

		$error2 = 0;
		//echo "<br>Monto:" . $Amount;
		if ($Amount == 0){
			prnMsg(_('Captura el monto del pago...'),'error');
			$error2 = 1;
		}
		if ($_SESSION['MontoMaximoXPartidaReembolso'] > 0){
			if ($Amount > $_SESSION['MontoMaximoXPartidaReembolso']){
				prnMsg(_('El monto a reembolsar es mayor de '.$_SESSION['MontoMaximoXPartidaReembolso'].' no puede reembolsar esta cantidad '.$Amount), 'error');
				$error2 = 1;
			}
		}
		if (($AmountTax == 0) or ($AmountTax == "")){

			if($taxrate != ""){
				$sql = "select taxrate from taxauthrates where taxcatid = '" . $taxrate . "'";
				//echo "<br>" . $sql;
				$result = DB_query(	$sql, $db);
				if ($myrow = DB_fetch_array($result)) {
					$AmountTax = (($Amount+$RetencionIVA+$RetencionISR+$RetencionxArrendamiento+$RetencionxComisiones+$RetencionxFletes+$RetencionxCedular+$Discountsupp)/(1+$myrow['taxrate']))*$myrow['taxrate'];
				}
			}
		}else{
			//$Amount = $Amount - $AmountTax;
		}

		if ($ctaproveedor == 0){
			prnMsg(_('Selecciona la cuenta del proveedor...'),'error');
			$error2 = 1;
		}

		if (strlen($SupplierID) == 0){
			prnMsg(_('Selecciona el proveedor...'),'error');
			$error2 = 1;
		}

		if ($descripciongasto == ""){
			prnMsg(_('Captura descripcion del gasto...'),'error');
			$error2 = 1;
		}

		if ($referencia == ""){
			prnMsg(_('Captura referencia  del gasto...'),'error');
			$error2 = 1;
		}

		if ($tagMovto == "0" OR $tagMovto == 0){
			prnMsg(_('Selecciona Unidad de Negocio de Factura...'),'error');
			$error2 = 1;
		}




		if ($error2==0){
			$_POST['Discount'] = 0;

			$_POST['RetencionIVA'] = 0;
			$_POST['RetencionISR'] = 0;
			$_POST['RetencionxIVAArrendamiento'] = 0;
			$_POST['RetencionxComisiones'] = 0;
			$_POST['RetencionxFletes'] = 0;
			$_POST['RetencionxCedular'] = 0;

			$_POST['SupplierID'] = "";
			$_POST['TaxCat'] = 4;
			$_POST['mntIVA'] = 0;
			$_POST['Amount'] = 0;

			$_POST['descripciongasto'] = "";
			$_POST['referencia'] = "";

			if(isset($_POST['ID']) and $_POST['ID']<>''){
				//echo "entra a la modificacion: ";
				$_SESSION['PaymentDetail']->Edit_To_GLAnalysisAddSupp($_POST['ID'], $SupplierID, $SuppName, $taxrate, $AmountTax, $Amount, $Discountsupp, $RetencionIVA, $RetencionISR,
						$RetencionxArrendamiento, $RetencionxComisiones, $RetencionxFletes, $RetencionxCedular, $descripciongasto, $referencia, $ctaproveedor, $tagMovto);
			}else{
				$_SESSION['PaymentDetail']->Add_To_GLAnalysisAddSupp($SupplierID, $SuppName, $taxrate, $AmountTax, $Amount, $Discountsupp, $RetencionIVA, $RetencionISR,
						$RetencionxArrendamiento, $RetencionxComisiones, $RetencionxFletes, $RetencionxCedular, $descripciongasto, $referencia, $ctaproveedor, $tagMovto);
			}
		}



	}
}


if (isset($_POST['UpdateHeader'])){

}

/***************************************************************************/
/**************** INICIA PROCESO Y CONFIRAMCION DE MOVIMIENTOS *************/
if (isset($_POST['CommitBatch'])){

}elseif (isset($_GET['Delete'])){
	$_SESSION['PaymentDetail']->Remove_GLItem($_GET['Delete']);

	$_POST['tag'] = $_GET['tag'];
	$_POST['BankAccount'] = $_GET['BankAccount'];
	$_POST['ToYear'] = $_GET['ToYear'];
	$_POST['ToMes'] = $_GET['ToMes'];
	$_POST['ToDia'] = $_GET['ToDia'];
	$_POST['Currency'] = $_GET['Currency'];
	$_POST['Paymenttype'] = $_GET['Paymenttype'];
	$_POST['ChequeNum'] = $_GET['ChequeNum'];
	$_POST['Beneficiario'] = $_GET['Beneficiario'];
	$_POST['Narrative'] = $_GET['Narrative'];
	$_POST['acreedor'] = $_GET['acreedor'];


}elseif (isset($_GET['DeleteModifica'])){
	$_POST['tag'] = $_GET['tag'];
	$_POST['BankAccount'] = $_GET['BankAccount'];
	$_POST['ToYear'] = $_GET['ToYear'];
	$_POST['ToMes'] = $_GET['ToMes'];
	$_POST['ToDia'] = $_GET['ToDia'];
	$_POST['Currency'] = $_GET['Currency'];
	$_POST['Paymenttype'] = $_GET['Paymenttype'];
	$_POST['ChequeNum'] = $_GET['ChequeNum'];
	$_POST['Beneficiario'] = $_GET['Beneficiario'];
	$_POST['Narrative'] = $_GET['Narrative'];
	$_POST['acreedor'] = $_GET['acreedor'];

	$ID = $_GET['DeleteModifica'];
	//echo "<br>monto:" . $_SESSION['PaymentDetail']->GLItems[$ID]->Amount;

	$_POST['SupplierID'] = $_SESSION['PaymentDetail']->GLItems[$ID]->SupplierID;
	$_POST['TaxCat'] = $_SESSION['PaymentDetail']->GLItems[$ID]->taxrate;
	$_POST['mntIVA'] = $_SESSION['PaymentDetail']->GLItems[$ID]->AmountTax;
	$_POST['Amount'] = $_SESSION['PaymentDetail']->GLItems[$ID]->Amount;
	$_POST['Discount'] = $_SESSION['PaymentDetail']->GLItems[$ID]->Discountsupp;
	$_POST['RetencionIVA'] = $_SESSION['PaymentDetail']->GLItems[$ID]->RetencionIVA;

	$_POST['RetencionISR'] = $_SESSION['PaymentDetail']->GLItems[$ID]->RetencionISR;
	$_POST['RetencionxIVAArrendamiento'] = $_SESSION['PaymentDetail']->GLItems[$ID]->RetencionxArrendamiento;
	$_POST['RetencionxComisiones'] = $_SESSION['PaymentDetail']->GLItems[$ID]->RetencionxComisiones;
	$_POST['RetencionxFletes'] = $_SESSION['PaymentDetail']->GLItems[$ID]->RetencionxFletes;
	$_POST['RetencionxCedular'] = $_SESSION['PaymentDetail']->GLItems[$ID]->RetencionxCedular;
	$_POST['descripciongasto'] = $_SESSION['PaymentDetail']->GLItems[$ID]->descripciongasto;
	$_POST['referencia'] = $_SESSION['PaymentDetail']->GLItems[$ID]->referencia;
	$_POST['ctaproveedor'] = $_SESSION['PaymentDetail']->GLItems[$ID]->ctaproveedor;
	$_POST['tagMovto'] = $_SESSION['PaymentDetail']->GLItems[$ID]->tagMovto;

}elseif (isset($_POST['SearchAccount']) == 'Buscar'){
}elseif (isset($_POST['procesar'])){
		$totalamountvalidacion = 0;
	foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
		$totalamountvalidacion = $totalamountvalidacion + $PaymentItem->Amount;

	}
	if ($_SESSION['MontoMaximoTPartidaReembolso'] > 0){
		if ( $totalamountvalidacion > $_SESSION['MontoMaximoTPartidaReembolso']){
			prnMsg(_('El total de los reembolso  no pueden pasar de '.$_SESSION['MontoMaximoTPartidaReembolso']),'error');
		}else{
			$Result = DB_Txn_Begin($db);
			$tipodocto = 20;

			$arrfecha = explode("-", $_SESSION['PaymentDetail']->trandate);
			$nuevafecha = $arrfecha[2] . "/" . $arrfecha[1] . "/" . $arrfecha[0];
			$PeriodNo = GetPeriod($nuevafecha, $db, $_SESSION['PaymentDetail']->tagref);
			$SQLInvoiceDate = $_SESSION['PaymentDetail']->trandate;
			$today = getdate();


			$matrizfacturas = array();
			$matrizpagos = array();

			$arrnotascredito = array();

			$indexarr = -1;
			$suppreference="";
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

				/* PARA CADA MOVIMIENTO GENERA SU FACTURA DE COMPRA !!!*/
				$tipodocto = 20;
				$indexarr++;
				$InvoiceNo = GetNextTransNo($tipodocto, $db);

				//GASTO
				$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
				VALUES (" . $tipodocto . ",
						" . $InvoiceNo . ",
						'" . $SQLInvoiceDate . "',
						" . $PeriodNo . ",
						'" . $PaymentItem->ctaproveedor . "',
						'" . $PaymentItem->SupplierID . " " . $PaymentItem->referencia . " - Reembolso de gastos',
						" . round((($PaymentItem->Amount +
										$PaymentItem->RetencionISR +
										$PaymentItem->RetencionxArrendamiento +
										$PaymentItem->RetencionxComisiones +
										$PaymentItem->RetencionxFletes +
										$PaymentItem->RetencionxCedular +
										$PaymentItem->Discountsupp +
										$PaymentItem->RetencionIVA -
										$PaymentItem->AmountTax
								)/$_SESSION['PaymentDetail']->rate),2) . ",
						" . $PaymentItem->tagMovto . ")";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate,2);

				$SQL = 'select * from taxauthorities where taxid=1';
				$result2 = DB_query($SQL,$db);
				if ($TaxAccs = DB_fetch_array($result2)){
					$TaxGLCode = $TaxAccs['purchtaxglaccount'];
				}

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag)
						VALUES (" . $tipodocto . ",
									" . $InvoiceNo . ",
								'" . $SQLInvoiceDate . "',
								" . $PeriodNo . ",
								'" . $TaxGLCode . "',
								'" . $PaymentItem->SupplierID . " - " . _('Inv') . " " . $PaymentItem->referencia . $PaymentItem->AmountTax  . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
								" . round(($PaymentItem->AmountTax/$_SESSION['PaymentDetail']->rate),2) . ",
								" . $PaymentItem->tagMovto . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$tipoproveedor = ExtractTypeSupplier($PaymentItem->SupplierID,$db);
				$cuentacontable = SupplierAccount($tipoproveedor,'gl_accountsreceivable',$db);
				$monto = round((($PaymentItem->Amount +
						$PaymentItem->RetencionISR +
						$PaymentItem->RetencionxArrendamiento +
						$PaymentItem->RetencionxComisiones +
						$PaymentItem->RetencionxFletes +
						$PaymentItem->RetencionxCedular +
						$PaymentItem->Discountsupp +
						$PaymentItem->RetencionIVA
				)/$_SESSION['PaymentDetail']->rate),2);

				$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										tag)
					VALUES (" . $tipodocto . ",
							" . $InvoiceNo . ",
							'" . $SQLInvoiceDate . "',
							" . $PeriodNo . ",
							'" . $cuentacontable . "',
							'" . $PaymentItem->SupplierID . " - " . _('Inv') . " " . $PaymentItem->referencia . ' ' . number_format($monto,2) . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
							" . -$monto . ",
							" . $PaymentItem->tagMovto . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$SQL = "INSERT INTO supptrans (transno,
											tagref,
											type,
											supplierno,
											suppreference,
											origtrandate,
											trandate,
											duedate,
											ovamount,
											ovgst,
											rate,
											transtext,
											alt_tagref,
											settled,
											currcode,
											alloc)
					VALUES (" . $InvoiceNo . ",
							'" . $PaymentItem->tagMovto . "',
							" . $tipodocto . " ,
							'" . $PaymentItem->SupplierID . "',
							'" . $PaymentItem->referencia . "',
							'" . $SQLInvoiceDate . "',
							'" . $SQLInvoiceDate . "',
							'" . $SQLInvoiceDate . "',
							" . round($monto-$PaymentItem->AmountTax,2) . ",
							" . round($PaymentItem->AmountTax,2) . ",
							" .  $_SESSION['PaymentDetail']->rate . ",
							'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
							" . $PaymentItem->tagMovto . ",
							1,
							'" . $_SESSION['PaymentDetail']->currency . "',
							" . round($monto,2) . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
				$DbgMsg = _('The following SQL to insert the supplier invoice was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
				// insertar detalle de la factura del proveedor
				$SQL="INSERT  INTO supptransdetails(supptransid,stockid,description,price,qty,orderno,grns)
						VALUES(".$SuppTransID.",'".$cuentacontable."','".'Reembolso de Caja'."','".($monto-$PaymentItem->AmountTax)."','1','0','0')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede registrar el detalle de la factura del proveedor');
				$DbgMsg = _('El SQL utilizado es');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				//echo '<pre>sql de supptransdetails:'.$SQL;
				//consulta de gltrans
				$sql= "SELECT gltrans.type, gltrans.typeno, gltrans.tag, gltrans.trandate, gltrans.periodno, sum(CASE WHEN gltrans.amount > 0 THEN gltrans.amount ELSE 0 END) as amount,
				tags.tagdescription, day(gltrans.trandate) as daytrandate, month(gltrans.trandate) as monthtrandate, year(gltrans.trandate) as yeartrandate,
				legalbusinessunit.taxid, legalbusinessunit.address5, systypescat.typename
			FROM  tags, sec_unegsxuser, gltrans, legalbusinessunit, systypescat
			WHERE gltrans.tag = tags.tagref  and gltrans.type =" . $tipodocto . " and gltrans.typeno='" . $InvoiceNo . "'  and sec_unegsxuser.tagref = tags.tagref
				and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and legalbusinessunit.legalid = tags.legalid  and systypescat.typeid = gltrans.type
			GROUP BY gltrans.type, gltrans.typeno, gltrans.tag, gltrans.trandate, gltrans.periodno, tags.tagdescription, legalbusinessunit.taxid,
				legalbusinessunit.address5, systypescat.typename
			ORDER BY gltrans.trandate, gltrans.type, gltrans.typeno";

				$Result = DB_query($sql,$db,$ErrMsg);
				$myrow2=DB_fetch_array($Result);

				$matrizfacturas[$indexarr] = array('type' => $myrow2['type'], 'typeno' => $myrow2['typeno'], 'periodno' => $myrow2['periodno'], 'trandate' => $myrow2['trandate'], 'id' => $SuppTransID, 'amount'=>$monto);

				//GENERACION DE PAGOS
				//$PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid,$db);

				$Transtype = 501;
				$TransNo = GetNextTransNo($Transtype, $db);
				$tipocambioaldia = $_SESSION['PaymentDetail']->rate;
				$arrnotascredito[$indexarr] = $TransNo;

				$SQL = "INSERT INTO supptrans (transno,
											type,
											supplierno,
											trandate,
											suppreference,
											rate,
											ovamount,
											transtext,
											tagref,
											origtrandate,
											ref1,
											currcode,
											duedate,
											settled,
											alloc) ";
				$SQL = $SQL . 'VALUES (' . $TransNo . ",
									'" . $Transtype . "',
									'" . $PaymentItem->SupplierID . "',
									'" . $SQLInvoiceDate . "',
									'" . $_SESSION['PaymentDetail']->paymenttype . "',
									" . ($tipocambioaldia) . ",
									" . - round($monto,2) . ",
									'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
									" . $_SESSION['PaymentDetail']->tagref . ",
									'" . $SQLInvoiceDate . "',
									'" . $_SESSION['PaymentDetail']->cheque . "',
									'" . $_SESSION['PaymentDetail']->currency . "',
									'" . $SQLInvoiceDate . "',
									1,
									" . - round($monto,2) . ")"; // tenia sumado  este valor $PaymentItem->AmountTax y al sacar el saldo daba que le sobra el iva

				$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
				$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$SuppTransIDCheque = DB_Last_Insert_ID($db,'supptrans','id');

				$matrizpagos[$indexarr] = array('type' => $Transtype, 'typeno' => $TransNo, 'periodno' => $PeriodNo, 'trandate' => $SQLInvoiceDate, 'id' => $SuppTransIDCheque, 'amount'=>-$monto);


				$SQL = "INSERT suppallocs(amt, datealloc, transid_allocfrom,
										  transid_allocto,rate_from,currcode_from,rate_to,
										  currcode_to,ratealloc,diffonexch_alloc)
				VALUES(" . round(($monto),2) . ",'" . $SQLInvoiceDate . "',
					   " . $SuppTransIDCheque . ",
					   	" . $SuppTransID . ",
					   	'" . $tipocambioaldia . "',
					   	'" .  $_SESSION['PaymentDetail']->currency . "',
					   	'" . $tipocambioaldia . "',
					   	'" .  $_SESSION['PaymentDetail']->currency . "',
					    '" . $tipocambioaldia . "',0
					   	)";

				$ErrMsg = _('No pude insertar el registro de la aplicacion de pago');
				$DbgMsg = _('No pude insertar el registro de la aplicacion de pago actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
				$SQL = "UPDATE suppliers SET
				lastpaiddate = '" . $SQLInvoiceDate . "',
				lastpaid=" . ($monto) ."
				WHERE suppliers.supplierid='" . $PaymentItem->SupplierID . "'";

				$ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
				$DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


				//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
				if ($PaymentItem->SupplierID != ''){
					$tipoproveedor = ExtractTypeSupplier($PaymentItem->SupplierID,$db);
					$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
				}else{
					$ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
				}

				$SQL="INSERT INTO gltrans ( type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										tag,
										chequeno) ";
				$SQL=$SQL . "VALUES (
					'" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $ctaxtipoproveedor . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / PAGO',
					" . (round((($PaymentItem->Amount +
									$PaymentItem->RetencionISR +
									$PaymentItem->RetencionxArrendamiento +
									$PaymentItem->RetencionxComisiones +
									$PaymentItem->RetencionxFletes +
									$PaymentItem->RetencionxCedular +
									$PaymentItem->RetencionIVA +
									$PaymentItem->Discountsupp
							)/$_SESSION['PaymentDetail']->rate),2) ) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";


				$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
				$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				/**************************************************/
				/* MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
				$SQL = 'select * from taxauthorities where taxid=1';
				$result2 = DB_query($SQL,$db);
				if ($TaxAccs = DB_fetch_array($result2)){
					$taximpuesto = $PaymentItem->AmountTax;

					$sql = "select taxrate from taxauthrates where taxcatid = '" . $TaxAccs['taxid'] . "'";
					$resultx = DB_query($sql, $db);
					if ($myrowtx = DB_fetch_array($resultx)) {
						$tasaiva = $myrowtx['taxrate'];
					} else {
						$tasaiva = 0;
					}

					$SQL="INSERT INTO gltrans ( type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag,
											chequeno) ";
					$SQL=$SQL . "VALUES (
								'" . $Transtype . "',
								" . $TransNo . ",
								'" . $SQLInvoiceDate . "',
								" . $PeriodNo . ",
								" . $TaxAccs['purchtaxglaccount'] . ",
								'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / IVA1',
								" . (($taximpuesto/$tipocambioaldia)*-1) . ",
								" . $_SESSION['PaymentDetail']->tagref . ",
								'" . $_SESSION['PaymentDetail']->cheque . "'
						)";

					$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
					$DbgMsg = _('El SQL utilizado fue');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES (
						'" . $Transtype . "',
						" . $TransNo . ",
						'" . $SQLInvoiceDate . "',
						" . $PeriodNo . ",
						" . $TaxAccs['purchtaxglaccountPaid'] . ",
						'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / IVA2',
						" . (($taximpuesto-($PaymentItem->Discountsupp/(1+$tasaiva)*($tasaiva)))/$tipocambioaldia) . ",
						" . $_SESSION['PaymentDetail']->tagref . ",
						'" . $_SESSION['PaymentDetail']->cheque . "'
					)";

					$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
					$DbgMsg = _('El SQL utilizado fue');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				} //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS

				if ($PaymentItem->Discountsupp != 0){
					$MDiscount = $PaymentItem->Discountsupp;
					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["pytdiscountact"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Descuento',
					" . (-1)*((($MDiscount-($PaymentItem->Discountsupp/(1+$tasaiva)*($tasaiva)))/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";

					$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount

				if ($PaymentItem->RetencionISR != 0){
					$MRetencionISR = $PaymentItem->RetencionISR;

					$SQL="INSERT INTO gltrans ( type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag,
					chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionhonorarios"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion ISR',
					" . (-1)*(($MRetencionISR/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento isr porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento isr utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}

				if ($PaymentItem->RetencionIVA !=0){
					/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
					$MRetencionIVA = $PaymentItem->RetencionIVA;

					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencioniva"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion IVA',
					" . (-1)*(($MRetencionIVA/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
					$ErrMsg = _('No pude insertar la transaccion contable para la descuento retencion iva porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento retencion iva utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}

				if ($PaymentItem->RetencionxCedular !=0){
					$MRetencionxCedular = $PaymentItem->RetencionxCedular;
					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionCedular"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Cedular',
					" . (-1)*(($MRetencionxCedular/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento cedular porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento cedular utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}


				if ($PaymentItem->RetencionxFletes !=0){
					$MRetencionxFletes = $PaymentItem->RetencionxFletes;

					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionFletes"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Fletes',
					" . (-1)*(($MRetencionxFletes/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento fletes porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento fletes utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}


				if ($PaymentItem->RetencionxComisiones !=0){
					$MRetencionxComisiones = $PaymentItem->RetencionxComisiones;

					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionComisiones"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Comisiones',
					" . (-1)*(($MRetencionxComisiones/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";

					$ErrMsg = _('No pude insertar la transaccion contable para el descuento comisiones porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento comisiones utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}


				if ($PaymentItem->RetencionxArrendamiento !=0){
					$MRetencionxArrendamiento = $PaymentItem->RetencionxArrendamiento;

					/* Now credit Discount received account with discounts */
					$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
					$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionarrendamiento"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Arrendamiento',
					" . (-1)*(($MRetencionxArrendamiento/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}

				$SumaRetenciones = $MRetencionIVA + $MRetencionISR + $MRetencionxCedular + $MRetencionxFletes + $MRetencionxComisiones + $MRetencionxArrendamiento;

				$MCuadra = $PaymentItem->Amount - $MDiscount - $SumaRetenciones;
				if ($PaymentItem->Amount !=0){
					/* Bank account entry first */

					/*
						if ($PaymentItem->SupplierID != ''){
					$tipoproveedor = ExtractTypeSupplier($PaymentItem->SupplierID,$db);
					$ctaxtipoproveedor2 = SupplierAccount($tipoproveedor,"gl_notesreceivable",$db);
					}else{
					$ctaxtipoproveedor2 = $_SESSION['CompanyRecord']['creditorsact'];
					}desarrolloOLD*/

					if (substr($_SESSION['PaymentDetail']->GLCode,0,2) == 'A:'){
						$tipoproveedor = ExtractTypeSupplier(substr($_SESSION['PaymentDetail']->GLCode,2),$db);
						$ctaxtipoproveedor2 = SupplierAccount($tipoproveedor,"gl_notesreceivable",$db);
					}else{
						$ctaxtipoproveedor2 = substr($_SESSION['PaymentDetail']->GLCode,2);
					}

					$SQL = "INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno)";
					$SQL = $SQL . 'VALUES (' . $Transtype . ',
						' . $TransNo . ",
						'" . $SQLInvoiceDate . "',
						" . $PeriodNo . ",
						" . $ctaxtipoproveedor2 . ",
						'BANCO/ACREEDOR - " . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / BancoAcreedor',
						" . (-1)*($PaymentItem->Amount/$tipocambioaldia) . ",
						" . $_SESSION['PaymentDetail']->tagref . ",
						'" . $_SESSION['PaymentDetail']->cheque . "'
					)";

					$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
					$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				} // FIN IF SI MONTO DIFERENTE DE CERO

				$conversion = $PaymentItem->Amount - $MDiscount - $SumaRetenciones;
				$tipomoneda = $_SESSION['PaymentDetail']->currency;
				$tamount = $tamount + ($PaymentItem->Amount);

				$suppreference.=$Transtype."-".$TransNo."|";   // gardo las referencias de los documentos 501 para poder cancelar si es necesario
			}

			$ttag = $_SESSION['PaymentDetail']->tagref;
			$tchequeno = $_SESSION['PaymentDetail']->cheque;
			$Beneficiario =  $_SESSION['PaymentDetail']->Beneficiary;

			if (substr($_SESSION['PaymentDetail']->GLCode,0,2) != 'A:'){

				//**********IMPRESION DE CHEQUES
				$Transtype = 501;
				//$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
				$lasttag = $_SESSION['PaymentDetail']->tagref;
				$liga = GetUrlToPrint($lasttag,$Transtype,$db);

				/* BUSCA SI CHEQUERA UTILIZA UN FORMATO ESPECIAL DE IMPRESION */
				$sql="select *
				FROM bankaccounts
				WHERE accountcode='" . $_SESSION['PaymentDetail']->GLCode . "'";
				$Result = DB_query($sql, $db);
				$myrow = DB_fetch_array($Result);
				$pdfprefix=$myrow['pdfprefix'];
				if ($pdfprefix == null){
					$pdfprefix = "";
				}

				/****IMPRIMIR FACTURA****/
				$strtypeno = "";
				$strtypenopipe = "";
				foreach($matrizpagos as $arrpago){
					if ($strtypeno != ""){
						$strtypeno = $strtypeno . "_" . $arrpago['typeno'];
						$strtypenopipe = $strtypenopipe . "|" . $arrpago['typeno'];
					}else{
						$strtypeno = $arrpago['typeno'];
						$strtypenopipe = $arrpago['typeno'];
						$strtype = $arrpago['type'];
						$strperiodo = $arrpago['periodno'];
						$strtrandate = $arrpago['trandate'];
					}
				}

				$SQL="INSERT INTO banktrans (transno,
					type,
					bankact,
					ref,
					exrate,
					functionalexrate,
					transdate,
					banktranstype,
					amount,
					currcode,
					tagref,
					beneficiary,
					chequeno,
					usuario) ";
				$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				" . substr($_SESSION['PaymentDetail']->GLCode,2) . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . $tipocambioaldia . " ,
				" . $tipocambioaldia . ",
				'" . $SQLInvoiceDate . "',
				'" . $_SESSION['PaymentDetail']->paymenttype . "',";
				$SQL .=	  -$tamount  . ",
				'" . $_SESSION['PaymentDetail']->currency . "',
				" . $ttag . ",
				'" . $Beneficiario . "@".$strtypenopipe."',
				'" . $tchequeno . "',
				'".$_SESSION['UserID']."'
			)";
				/*if($_SESSION['UserID'] == "admin"){
					echo 'SQL1 <pre>'.$SQL;
				}*/
				$ErrMsg = _('No pude insertar la transaccion bancaria porque');
				$DbgMsg = _('No pude insertar la transaccion bancaria utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				echo '<br><div class="centre">';
				$liga="PrintJournal2.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $strtype . "&TransNo=" . $strtypeno . "&periodo=" . $strperiodo . "&trandate=" . $strtrandate;
				echo "<a TARGET='_blank' href='" . $liga . "'>" . _('Imprimir Cheque Poliza') . "</a></div>";

				/**************************/
				$arrtransno = explode('_',$strtypeno);

				$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
				. '" alt="">' . ' ' .
				'<a  target="_blank" href="' . $rootpath . '/PrintCheque_01'.$pdfprefix.'.php?' . SID .'&TransNo='.$arrtransno[0].'&type='.$Transtype.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';


				echo $liga.'<br>';

				//***********************
			} else {
				/* Es Acreedor, generar prestamo de acreedor*/
				$Transtype = 801;
				$TransNo = GetNextTransNo($Transtype, $db);

				$suppreference = substr($suppreference,0,strlen($suppreference)-1);


				$SQL = "INSERT INTO supptrans (transno,
				type,
				supplierno,
				trandate,
				suppreference,
				rate,
				ovamount,
				transtext,
				tagref,
				origtrandate,
				ref1,
				currcode,
				duedate,
				settled,
				alloc) ";
				$SQL = $SQL . 'VALUES (' . $TransNo . ",
					'" . $Transtype . "',
					'" . substr($_SESSION['PaymentDetail']->GLCode,2) . "',
					'" . $SQLInvoiceDate . "',
					'" . $suppreference . "',
					" . ($tipocambioaldia) . ",
					" . round($tamount/$tipocambioaldia,2) . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $SQLInvoiceDate . "',
					'" . $_SESSION['PaymentDetail']->cheque . "',
					'" . $_SESSION['PaymentDetail']->currency . "',
					'" . $SQLInvoiceDate . "',
					0,
					0)";

				$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
				$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$tipoproveedor = ExtractTypeSupplier(substr($_SESSION['PaymentDetail']->GLCode,2),$db);
				$ctaxtipoproveedor2 = SupplierAccount($tipoproveedor,"gl_notesreceivable",$db);

				$SQL = "INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						chequeno)";
				$SQL = $SQL . 'VALUES (' . $Transtype . ',
					' . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $ctaxtipoproveedor2 . ",
					'BANCO/ACREEDOR - " . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / BancoAcreedor',
					" . (-1)*($tamount/$tipocambioaldia) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
			)";

				$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
				$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$SQL = "INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						chequeno)";
				$SQL = $SQL . 'VALUES (' . $Transtype . ',
					' . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $ctaxtipoproveedor2 . ",
					'BANCO/ACREEDOR - " . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / BancoAcreedor',
					" . ($tamount) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
			)";

				$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
				$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			}

			$result = DB_Txn_Commit($db);

			prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('ha sido exitosamente procesado'),'success');

			/****IMPRIMIR FACTURA****/
			$strtypeno = "";
			foreach($matrizfacturas as $arrfactura){
				if ($strtypeno != ""){
					$strtypeno = $strtypeno . "_" . $arrfactura['typeno'];
				}else{
					$strtypeno = $arrfactura['typeno'];
					$strtype = $arrfactura['type'];
					$strperiodo = $arrfactura['periodno'];
					$strtrandate = $arrfactura['trandate'];
				}
			}

			echo '<br><div class="centre">';
			$liga="PrintJournal2.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $strtype . "&TransNo=" . $strtypeno . "&periodo=" . $strperiodo . "&trandate=" . $strtrandate;
			echo "<a TARGET='_blank' href='" . $liga . "'>" . _('Imprimir Poliza de Factura') . "</a></div>";

			/**************************/
			echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Capturar Reembolso Pagos') . '</a><br>';

			include('includes/footer.inc');
			exit;
		}
	} else {

		$Result = DB_Txn_Begin($db);
		$tipodocto = 20;

		$arrfecha = explode("-", $_SESSION['PaymentDetail']->trandate);
		$nuevafecha = $arrfecha[2] . "/" . $arrfecha[1] . "/" . $arrfecha[0];
		$PeriodNo = GetPeriod($nuevafecha, $db, $_SESSION['PaymentDetail']->tagref);
		$SQLInvoiceDate = $_SESSION['PaymentDetail']->trandate;
		$today = getdate();


		$matrizfacturas = array();
		$matrizpagos = array();

		$arrnotascredito = array();

		$indexarr = -1;
		$suppreference="";
		foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

			/* PARA CADA MOVIMIENTO GENERA SU FACTURA DE COMPRA !!!*/
			$tipodocto = 20;
			$indexarr++;
			$InvoiceNo = GetNextTransNo($tipodocto, $db);

			//GASTO
			$SQL = "INSERT INTO gltrans (type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag)
				VALUES (" . $tipodocto . ",
						" . $InvoiceNo . ",
						'" . $SQLInvoiceDate . "',
						" . $PeriodNo . ",
						'" . $PaymentItem->ctaproveedor . "',
						'" . $PaymentItem->SupplierID . " " . $PaymentItem->referencia . " - Reembolso de gastos',
						" . round((($PaymentItem->Amount +
						  	 $PaymentItem->RetencionISR +
						 	 $PaymentItem->RetencionxArrendamiento +
						 	 $PaymentItem->RetencionxComisiones +
						     $PaymentItem->RetencionxFletes +
						   	 $PaymentItem->RetencionxCedular +
						   	 $PaymentItem->Discountsupp +
						   	 $PaymentItem->RetencionIVA -
						   	 $PaymentItem->AmountTax
						  	  )/$_SESSION['PaymentDetail']->rate),2) . ",
						" . $PaymentItem->tagMovto . ")";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
			$DbgMsg = _('The following SQL to insert the GL transaction was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			$LocalTotal += round($EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate,2);

			$SQL = 'select * from taxauthorities where taxid=1';
			$result2 = DB_query($SQL,$db);
			if ($TaxAccs = DB_fetch_array($result2)){
				$TaxGLCode = $TaxAccs['purchtaxglaccount'];
			}

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag)
						VALUES (" . $tipodocto . ",
									" . $InvoiceNo . ",
								'" . $SQLInvoiceDate . "',
								" . $PeriodNo . ",
								'" . $TaxGLCode . "',
								'" . $PaymentItem->SupplierID . " - " . _('Inv') . " " . $PaymentItem->referencia . $PaymentItem->AmountTax  . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
								" . round(($PaymentItem->AmountTax/$_SESSION['PaymentDetail']->rate),2) . ",
								" . $PaymentItem->tagMovto . ")";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$tipoproveedor = ExtractTypeSupplier($PaymentItem->SupplierID,$db);
				$cuentacontable = SupplierAccount($tipoproveedor,'gl_accountsreceivable',$db);
				$monto = round((($PaymentItem->Amount +
						    	$PaymentItem->RetencionISR +
						    	$PaymentItem->RetencionxArrendamiento +
						    	$PaymentItem->RetencionxComisiones +
						    	$PaymentItem->RetencionxFletes +
						    	$PaymentItem->RetencionxCedular +
						    	$PaymentItem->Discountsupp +
						    	$PaymentItem->RetencionIVA
						    	)/$_SESSION['PaymentDetail']->rate),2);

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										tag)
					VALUES (" . $tipodocto . ",
							" . $InvoiceNo . ",
							'" . $SQLInvoiceDate . "',
							" . $PeriodNo . ",
							'" . $cuentacontable . "',
							'" . $PaymentItem->SupplierID . " - " . _('Inv') . " " . $PaymentItem->referencia . ' ' . number_format($monto,2) . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
							" . -$monto . ",
							" . $PaymentItem->tagMovto . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');
			$DbgMsg = _('The following SQL to insert the GL transaction was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			$SQL = "INSERT INTO supptrans (transno,
											tagref,
											type,
											supplierno,
											suppreference,
											origtrandate,
											trandate,
											duedate,
											ovamount,
											ovgst,
											rate,
											transtext,
											alt_tagref,
											settled,
											alloc)
					VALUES (" . $InvoiceNo . ",
							'" . $PaymentItem->tagMovto . "',
							" . $tipodocto . " ,
							'" . $PaymentItem->SupplierID . "',
							'" . $PaymentItem->referencia . "',
							'" . $SQLInvoiceDate . "',
							'" . $SQLInvoiceDate . "',
							'" . $SQLInvoiceDate . "',
							" . round($monto-$PaymentItem->AmountTax,2) . ",
							" . round($PaymentItem->AmountTax,2) . ",
							" .  $_SESSION['PaymentDetail']->rate . ",
							'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
							" . $PaymentItem->tagMovto . ",
							1,
							" . round($monto,2) . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
			$DbgMsg = _('The following SQL to insert the supplier invoice was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
			// insertar detalle de la factura del proveedor
			$SQL="INSERT  INTO supptransdetails(supptransid,stockid,description,price,qty,orderno,grns)
						VALUES(".$SuppTransID.",'".$cuentacontable."','".'Reembolso de Caja'."','".($monto-$PaymentItem->AmountTax)."','1','0','0')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se puede registrar el detalle de la factura del proveedor');
			$DbgMsg = _('El SQL utilizado es');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			//echo '<pre>sql de supptransdetails:'.$SQL;
			//consulta de gltrans
			$sql= "SELECT gltrans.type, gltrans.typeno, gltrans.tag, gltrans.trandate, gltrans.periodno, sum(CASE WHEN gltrans.amount > 0 THEN gltrans.amount ELSE 0 END) as amount,
				tags.tagdescription, day(gltrans.trandate) as daytrandate, month(gltrans.trandate) as monthtrandate, year(gltrans.trandate) as yeartrandate,
				legalbusinessunit.taxid, legalbusinessunit.address5, systypescat.typename
			FROM  tags, sec_unegsxuser, gltrans, legalbusinessunit, systypescat
			WHERE gltrans.tag = tags.tagref  and gltrans.type =" . $tipodocto . " and gltrans.typeno='" . $InvoiceNo . "'  and sec_unegsxuser.tagref = tags.tagref
				and sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "' and legalbusinessunit.legalid = tags.legalid  and systypescat.typeid = gltrans.type
			GROUP BY gltrans.type, gltrans.typeno, gltrans.tag, gltrans.trandate, gltrans.periodno, tags.tagdescription, legalbusinessunit.taxid,
				legalbusinessunit.address5, systypescat.typename
			ORDER BY gltrans.trandate, gltrans.type, gltrans.typeno";

			$Result = DB_query($sql,$db,$ErrMsg);
			$myrow2=DB_fetch_array($Result);

			$matrizfacturas[$indexarr] = array('type' => $myrow2['type'], 'typeno' => $myrow2['typeno'], 'periodno' => $myrow2['periodno'], 'trandate' => $myrow2['trandate'], 'id' => $SuppTransID, 'amount'=>$monto);

			//GENERACION DE PAGOS
			//$PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid,$db);

			$Transtype = 501;
			$TransNo = GetNextTransNo($Transtype, $db);
			$tipocambioaldia = $_SESSION['PaymentDetail']->rate;
			$arrnotascredito[$indexarr] = $TransNo;

			$SQL = "INSERT INTO supptrans (transno,
											type,
											supplierno,
											trandate,
											suppreference,
											rate,
											ovamount,
											transtext,
											tagref,
											origtrandate,
											ref1,
											currcode,
											duedate,
											settled,
											alloc) ";
			$SQL = $SQL . 'VALUES (' . $TransNo . ",
									'" . $Transtype . "',
									'" . $PaymentItem->SupplierID . "',
									'" . $SQLInvoiceDate . "',
									'" . $_SESSION['PaymentDetail']->paymenttype . "',
									" . ($tipocambioaldia) . ",
									" . - round($monto,2) . ",
									'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
									" . $_SESSION['PaymentDetail']->tagref . ",
									'" . $SQLInvoiceDate . "',
									'" . $_SESSION['PaymentDetail']->cheque . "',
									'" . $_SESSION['PaymentDetail']->currency . "',
									'" . $SQLInvoiceDate . "',
									1,
									" . - round($monto,2) . ")"; // tenia sumado  este valor $PaymentItem->AmountTax y al sacar el saldo daba que le sobra el iva

			$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
			$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$SuppTransIDCheque = DB_Last_Insert_ID($db,'supptrans','id');

			$matrizpagos[$indexarr] = array('type' => $Transtype, 'typeno' => $TransNo, 'periodno' => $PeriodNo, 'trandate' => $SQLInvoiceDate, 'id' => $SuppTransIDCheque, 'amount'=>-$monto);


		/*	$SQL = "INSERT suppallocs(amt, datealloc, transid_allocfrom, transid_allocto)
				VALUES(" . round(($monto),2) . ",'" . $SQLInvoiceDate . "'," . $SuppTransIDCheque . "," . $SuppTransID . ")";

			*/
			$SQL = "INSERT suppallocs(amt, datealloc, transid_allocfrom,
										  transid_allocto,rate_from,currcode_from,rate_to,
										  currcode_to,ratealloc,diffonexch_alloc)
				VALUES(" . round(($monto),2) . ",'" . $SQLInvoiceDate . "',
					   " . $SuppTransIDCheque . ",
					   	" . $SuppTransID . ",
					   	'" . $tipocambioaldia . "',
					   	'" .  $_SESSION['PaymentDetail']->currency . "',
					   	'" . $tipocambioaldia . "',
					   	'" .  $_SESSION['PaymentDetail']->currency . "',
					    '" . $tipocambioaldia . "',0
					   	)";

			$ErrMsg = _('No pude insertar el registro de la aplicacion de pago');
			$DbgMsg = _('No pude insertar el registro de la aplicacion de pago actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);




			/* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
			$SQL = "UPDATE suppliers SET
				lastpaiddate = '" . $SQLInvoiceDate . "',
				lastpaid=" . ($monto) ."
				WHERE suppliers.supplierid='" . $PaymentItem->SupplierID . "'";

			$ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
			$DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


			//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
			if ($PaymentItem->SupplierID != ''){
				$tipoproveedor = ExtractTypeSupplier($PaymentItem->SupplierID,$db);
				$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
			}else{
				$ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
			}

			$SQL="INSERT INTO gltrans ( type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										tag,
										chequeno) ";
			$SQL=$SQL . "VALUES (
					'" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $ctaxtipoproveedor . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / PAGO',
					" . (round((($PaymentItem->Amount +
								    $PaymentItem->RetencionISR +
								    $PaymentItem->RetencionxArrendamiento +
								    $PaymentItem->RetencionxComisiones +
								    $PaymentItem->RetencionxFletes +
								    $PaymentItem->RetencionxCedular +
								    $PaymentItem->RetencionIVA +
								    $PaymentItem->Discountsupp
								    )/$_SESSION['PaymentDetail']->rate),2) ) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";


			$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
			$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			/**************************************************/
			/* MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
			$SQL = 'select * from taxauthorities where taxid=1';
			$result2 = DB_query($SQL,$db);
			if ($TaxAccs = DB_fetch_array($result2)){
				$taximpuesto = $PaymentItem->AmountTax;

				$sql = "select taxrate from taxauthrates where taxcatid = '" . $TaxAccs['taxid'] . "'";
				$resultx = DB_query($sql, $db);
				if ($myrowtx = DB_fetch_array($resultx)) {
					$tasaiva = $myrowtx['taxrate'];
				} else {
					$tasaiva = 0;
				}

				$SQL="INSERT INTO gltrans ( type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag,
											chequeno) ";
				$SQL=$SQL . "VALUES (
								'" . $Transtype . "',
								" . $TransNo . ",
								'" . $SQLInvoiceDate . "',
								" . $PeriodNo . ",
								" . $TaxAccs['purchtaxglaccount'] . ",
								'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / IVA1',
								" . (($taximpuesto/$tipocambioaldia)*-1) . ",
								" . $_SESSION['PaymentDetail']->tagref . ",
								'" . $_SESSION['PaymentDetail']->cheque . "'
						)";

				$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
				$DbgMsg = _('El SQL utilizado fue');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES (
						'" . $Transtype . "',
						" . $TransNo . ",
						'" . $SQLInvoiceDate . "',
						" . $PeriodNo . ",
						" . $TaxAccs['purchtaxglaccountPaid'] . ",
						'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / IVA2',
						" . (($taximpuesto-($PaymentItem->Discountsupp/(1+$tasaiva)*($tasaiva)))/$tipocambioaldia) . ",
						" . $_SESSION['PaymentDetail']->tagref . ",
						'" . $_SESSION['PaymentDetail']->cheque . "'
					)";

				$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
				$DbgMsg = _('El SQL utilizado fue');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS

			if ($PaymentItem->Discountsupp != 0){
				$MDiscount = $PaymentItem->Discountsupp;
				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["pytdiscountact"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Descuento',
					" . (-1)*((($MDiscount-($PaymentItem->Discountsupp/(1+$tasaiva)*($tasaiva)))/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";

				$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			} // end if discount

			if ($PaymentItem->RetencionISR != 0){
				$MRetencionISR = $PaymentItem->RetencionISR;

				$SQL="INSERT INTO gltrans ( type,
					typeno,
					trandate,
					periodno,
					account,
					narrative,
					amount,
					tag,
					chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionhonorarios"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion ISR',
					" . (-1)*(($MRetencionISR/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
				$ErrMsg = _('No pude insertar la transaccion contable para el descuento isr porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento isr utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

			if ($PaymentItem->RetencionIVA !=0){
				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				$MRetencionIVA = $PaymentItem->RetencionIVA;

				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencioniva"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion IVA',
					" . (-1)*(($MRetencionIVA/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
				$ErrMsg = _('No pude insertar la transaccion contable para la descuento retencion iva porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento retencion iva utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

			if ($PaymentItem->RetencionxCedular !=0){
				$MRetencionxCedular = $PaymentItem->RetencionxCedular;
				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionCedular"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Cedular',
					" . (-1)*(($MRetencionxCedular/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
				$ErrMsg = _('No pude insertar la transaccion contable para el descuento cedular porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento cedular utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}


			if ($PaymentItem->RetencionxFletes !=0){
				$MRetencionxFletes = $PaymentItem->RetencionxFletes;

				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionFletes"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Fletes',
					" . (-1)*(($MRetencionxFletes/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
				$ErrMsg = _('No pude insertar la transaccion contable para el descuento fletes porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento fletes utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}


			if ($PaymentItem->RetencionxComisiones !=0){
				$MRetencionxComisiones = $PaymentItem->RetencionxComisiones;

				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionComisiones"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Comisiones',
					" . (-1)*(($MRetencionxComisiones/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";

				$ErrMsg = _('No pude insertar la transaccion contable para el descuento comisiones porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento comisiones utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}


			if ($PaymentItem->RetencionxArrendamiento !=0){
				$MRetencionxArrendamiento = $PaymentItem->RetencionxArrendamiento;

				/* Now credit Discount received account with discounts */
				$SQL="INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno) ";
				$SQL=$SQL . "VALUES ('" . $Transtype . "',
					" . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']["gllink_retencionarrendamiento"] . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / Retencion Arrendamiento',
					" . (-1)*(($MRetencionxArrendamiento/$tipocambioaldia)) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
				)";
				$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
				$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

			$SumaRetenciones = $MRetencionIVA + $MRetencionISR + $MRetencionxCedular + $MRetencionxFletes + $MRetencionxComisiones + $MRetencionxArrendamiento;

			$MCuadra = $PaymentItem->Amount - $MDiscount - $SumaRetenciones;
			if ($PaymentItem->Amount !=0){
				/* Bank account entry first */

				/*
				if ($PaymentItem->SupplierID != ''){
					$tipoproveedor = ExtractTypeSupplier($PaymentItem->SupplierID,$db);
					$ctaxtipoproveedor2 = SupplierAccount($tipoproveedor,"gl_notesreceivable",$db);
				}else{
					$ctaxtipoproveedor2 = $_SESSION['CompanyRecord']['creditorsact'];
				}desarrolloOLD*/

				if (substr($_SESSION['PaymentDetail']->GLCode,0,2) == 'A:'){
					$tipoproveedor = ExtractTypeSupplier(substr($_SESSION['PaymentDetail']->GLCode,2),$db);
					$ctaxtipoproveedor2 = SupplierAccount($tipoproveedor,"gl_notesreceivable",$db);
				}else{
					$ctaxtipoproveedor2 = substr($_SESSION['PaymentDetail']->GLCode,2);
				}

				$SQL = "INSERT INTO gltrans ( type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount,
							tag,
							chequeno)";
				$SQL = $SQL . 'VALUES (' . $Transtype . ',
						' . $TransNo . ",
						'" . $SQLInvoiceDate . "',
						" . $PeriodNo . ",
						" . $ctaxtipoproveedor2 . ",
						'BANCO/ACREEDOR - " . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / BancoAcreedor',
						" . (-1)*($PaymentItem->Amount/$tipocambioaldia) . ",
						" . $_SESSION['PaymentDetail']->tagref . ",
						'" . $_SESSION['PaymentDetail']->cheque . "'
					)";

				$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
				$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} // FIN IF SI MONTO DIFERENTE DE CERO

			$conversion = $PaymentItem->Amount - $MDiscount - $SumaRetenciones;
			$tipomoneda = $_SESSION['PaymentDetail']->currency;
			$tamount = $tamount + ($PaymentItem->Amount);

			$suppreference.=$Transtype."-".$TransNo."|";   // gardo las referencias de los documentos 501 para poder cancelar si es necesario
		}

		$ttag = $_SESSION['PaymentDetail']->tagref;
		$tchequeno = $_SESSION['PaymentDetail']->cheque;
		$Beneficiario =  $_SESSION['PaymentDetail']->Beneficiary;

		if (substr($_SESSION['PaymentDetail']->GLCode,0,2) != 'A:'){

			//**********IMPRESION DE CHEQUES
			$Transtype = 501;
			//$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
			$lasttag = $_SESSION['PaymentDetail']->tagref;
			$liga = GetUrlToPrint($lasttag,$Transtype,$db);

			/* BUSCA SI CHEQUERA UTILIZA UN FORMATO ESPECIAL DE IMPRESION */
			$sql="select *
				FROM bankaccounts
				WHERE accountcode='" . $_SESSION['PaymentDetail']->GLCode . "'";
			$Result = DB_query($sql, $db);
			$myrow = DB_fetch_array($Result);
			$pdfprefix=$myrow['pdfprefix'];
			if ($pdfprefix == null){
				$pdfprefix = "";
			}

			/****IMPRIMIR FACTURA****/
			$strtypeno = "";
			$strtypenopipe = "";
			foreach($matrizpagos as $arrpago){
				if ($strtypeno != ""){
					$strtypeno = $strtypeno . "_" . $arrpago['typeno'];
					$strtypenopipe = $strtypenopipe . "|" . $arrpago['typeno'];
				}else{
					$strtypeno = $arrpago['typeno'];
					$strtypenopipe = $arrpago['typeno'];
					$strtype = $arrpago['type'];
					$strperiodo = $arrpago['periodno'];
					$strtrandate = $arrpago['trandate'];
				}
			}

			$SQL="INSERT INTO banktrans (transno,
					type,
					bankact,
					ref,
					exrate,
					functionalexrate,
					transdate,
					banktranstype,
					amount,
					currcode,
					tagref,
					beneficiary,
					chequeno,
					usuario) ";
			$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				" . substr($_SESSION['PaymentDetail']->GLCode,2) . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . $tipocambioaldia . " ,
				" . $tipocambioaldia . ",
				'" . $SQLInvoiceDate . "',
				'" . $_SESSION['PaymentDetail']->paymenttype . "',";
			$SQL .=	  -$tamount  . ",
				'" . $_SESSION['PaymentDetail']->currency . "',
				" . $ttag . ",
				'" . $Beneficiario . "@".$strtypenopipe."',
				'" . $tchequeno . "',
				'".$_SESSION['UserID']."'
			)";
			/*if($_SESSION['UserID'] == "admin"){
				echo 'SQL2 <pre>'.$SQL;
			}*/
			$ErrMsg = _('No pude insertar la transaccion bancaria porque');
			$DbgMsg = _('No pude insertar la transaccion bancaria utilizando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			echo '<br><div class="centre">';
			$liga="PrintJournal2.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $strtype . "&TransNo=" . $strtypeno . "&periodo=" . $strperiodo . "&trandate=" . $strtrandate;
			echo "<a TARGET='_blank' href='" . $liga . "'>" . _('Imprimir Cheque Poliza') . "</a></div>";

			/**************************/

			/*$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
			. '" alt="">' . ' ' .
			'<a  target="_blank" href="' . $rootpath . '/PrintCheque_01'.$pdfprefix.'.php?' . SID .'&TransNo='.$strtypeno.'&type='.$Transtype.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';


			echo $liga.'<br>';*/

			//***********************
		} else {
			/* Es Acreedor, generar prestamo de acreedor*/
			$Transtype = 801;
			$TransNo = GetNextTransNo($Transtype, $db);

			$suppreference = substr($suppreference,0,strlen($suppreference)-1);


			$SQL = "INSERT INTO supptrans (transno,
				type,
				supplierno,
				trandate,
				suppreference,
				rate,
				ovamount,
				transtext,
				tagref,
				origtrandate,
				ref1,
				currcode,
				duedate,
				settled,
				alloc) ";
			$SQL = $SQL . 'VALUES (' . $TransNo . ",
					'" . $Transtype . "',
					'" . substr($_SESSION['PaymentDetail']->GLCode,2) . "',
					'" . $SQLInvoiceDate . "',
					'" . $suppreference . "',
					" . ($tipocambioaldia) . ",
					" . round($tamount/$tipocambioaldia,2) . ",
					'" . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos',
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $SQLInvoiceDate . "',
					'" . $_SESSION['PaymentDetail']->cheque . "',
					'" . $_SESSION['PaymentDetail']->currency . "',
					'" . $SQLInvoiceDate . "',
					0,
					0)";

			$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
			$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$tipoproveedor = ExtractTypeSupplier(substr($_SESSION['PaymentDetail']->GLCode,2),$db);
			$ctaxtipoproveedor2 = SupplierAccount($tipoproveedor,"gl_notesreceivable",$db);

			$SQL = "INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						chequeno)";
			$SQL = $SQL . 'VALUES (' . $Transtype . ',
					' . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $ctaxtipoproveedor2 . ",
					'BANCO/ACREEDOR - " . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / BancoAcreedor',
					" . (-1)*($tamount/$tipocambioaldia) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
			)";

			$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
			$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$SQL = "INSERT INTO gltrans ( type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount,
						tag,
						chequeno)";
			$SQL = $SQL . 'VALUES (' . $Transtype . ',
					' . $TransNo . ",
					'" . $SQLInvoiceDate . "',
					" . $PeriodNo . ",
					" . $ctaxtipoproveedor2 . ",
					'BANCO/ACREEDOR - " . $PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate . " - reembolso de gastos / BancoAcreedor',
					" . ($tamount) . ",
					" . $_SESSION['PaymentDetail']->tagref . ",
					'" . $_SESSION['PaymentDetail']->cheque . "'
			)";

			$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
			$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		}

		$result = DB_Txn_Commit($db);

		prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('ha sido exitosamente procesado'),'success');

		/****IMPRIMIR FACTURA****/
		$strtypeno = "";
		foreach($matrizfacturas as $arrfactura){
			if ($strtypeno != ""){
				$strtypeno = $strtypeno . "_" . $arrfactura['typeno'];
			}else{
				$strtypeno = $arrfactura['typeno'];
				$strtype = $arrfactura['type'];
				$strperiodo = $arrfactura['periodno'];
				$strtrandate = $arrfactura['trandate'];
			}
		}
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
		. '" alt="">' . ' ' .
		'<a  target="_blank" href="' . $rootpath . '/PrintCheque_01'.$pdfprefix.'.php?' . SID .'&TransNo='.$strtypeno.'&type='.$Transtype.'&periodno='.$PeriodNo.'">'. _('Imprimir Cheque con detalle contable usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';


		echo $liga.'<br>';//
		echo '<br><div class="centre">';
		$liga="PrintJournal2.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $strtype . "&TransNo=" . $strtypeno . "&periodo=" . $strperiodo . "&trandate=" . $strtrandate;
		echo "<a TARGET='_blank' href='" . $liga . "'>" . _('Imprimir Poliza de Factura') . "</a></div>";

		/**************************/
		echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Capturar Reembolso Pagos') . '</a><br>';

		include('includes/footer.inc');
		exit;
	}

}
if ($_SESSION['MontoMaximoTPartidaReembolso'] > 0){
	if ( $totalamountvalidacion > $_SESSION['MontoMaximoTPartidaReembolso']){

	} else {
		if ( 	!isset($_POST['actualizar']) and
		!isset($_POST['UpdateHeader']) and
		!isset($_GET['Delete']) and
		!isset($_GET['DeleteModifica']) and
		!isset($_POST['buscarprov']) and
		!isset($_POST['cuentasxproveedor']) and
		!isset($_POST['cuentasxproveedor'])       ){
			unset($_SESSION['PaymentDetail']->GLItems);
			unset($_SESSION['PaymentDetail']);
			unset($_SESSION['PaySupCurrency']);
		}
	}
}else {
	if ( 	!isset($_POST['actualizar']) and
	!isset($_POST['UpdateHeader']) and
	!isset($_GET['Delete']) and
	!isset($_GET['DeleteModifica']) and
	!isset($_POST['buscarprov']) and
	!isset($_POST['cuentasxproveedor']) and
	!isset($_POST['cuentasxproveedor'])       ){
		unset($_SESSION['PaymentDetail']->GLItems);
		unset($_SESSION['PaymentDetail']);
		unset($_SESSION['PaySupCurrency']);
	}
}
if ( isset($_POST['Cancel']) ){
	unset($_POST['GLAmount']);
	unset($_POST['GLNarrative']);
	unset($_POST['GLCode']);
	unset($_POST['AccountName']);
}




echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
	echo "<tr>";
		echo "<td style='text-align:center'>";

			if ($_SESSION['PaymentDetail']->SupplierID!=""){
				echo '<font size=3 color=BLUE>' . _('PAGO');
				echo ' ' . _('A') . ' ' . $_SESSION['PaymentDetail']->SuppName;
			}

			if ($_SESSION['PaymentDetail']->BankAccountName!=""){
				echo ' ' . _('DESDE CUENTA') . ' ' . $_SESSION['PaymentDetail']->BankAccountName;
			}
			if ($_SESSION['PaymentDetail']->Trandate != "")
			echo ' ' . _('EL') . ' ' . $_SESSION['PaymentDetail']->DatePaid . '</font>';
		echo "</td>";
	echo "</tr>";
echo "</table>";

echo "<table border='1' cellpadding='2'>";

echo "<tr style='background-color:#050505;height:5px'><td colspan=2></td></tr>";

	echo '<tr><td><b>' . _('Razon Social') . ':<b></td><td><select name="legalid">';
	echo '<option selected value="0">Seleccionar una Razon Social';
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'

			  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['legalid']) and $_POST['legalid']==$myrow['legalid']){
			echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		} else {
			echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
		}
	}
	echo '</select>';
		echo "<input type='submit' name='mostrarcuentas' value='->'";
	echo "</td><tr>";

	echo '<tr><td>' . _('Unidad de Negocio') . ':</td><td><select name="tag">';

	$SQL = "SELECT t.tagref,t.tagdescription
		FROM sec_unegsxuser u,tags t
		WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' and t.legalid = '".$_POST['legalid']."' ORDER BY t.tagref";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result) == 0) {
		echo '<option selected value="0">primero seleccione razon social...';
	} else {
		echo '<option selected value="0">Seleccionar una Unidad de Negocio';
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
				echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
			} else {
				echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
			}
		}
	}
	echo '</select>';
		echo "<input type='submit' name='mostrarcuentas' value='MOSTRAR CUENTAS'";
	echo "</td><tr>";

	$SQL = "SELECT '1> BANCO: ' as tipo, bankaccountname as nombre,
		CONCAT('B:',bankaccounts.accountcode) as id,
		bankaccounts.currcode
		FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
		WHERE bankaccounts.accountcode=chartmaster.accountcode
			AND bankaccounts.accountcode = tagsxbankaccounts.accountcode
			AND tagsxbankaccounts.tagref = sec_unegsxuser.tagref
			AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			AND tagsxbankaccounts.tagref = '" . $_POST['tag'] . "'
			AND bankaccounts.currcode = 'MXN'
		GROUP BY bankaccountname,
			bankaccounts.accountcode,
			bankaccounts.currcode
		UNION
		SELECT '2> ACREE: ' as tipo, suppname as nombre, CONCAT('A:',supplierid) as id, currcode
		FROM suppliers JOIN supplierstype ON suppliers.typeid = supplierstype.typeid
			where supplierstype.aplicareembolsocaja = 1
			and suppliers.currcode = 'MXN'
		order by tipo, nombre";

	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Chequera / Acreedor') . ':</td><td>';

	echo '<select name="BankAccount">';
	if (DB_num_rows($AccountsResults)==0 OR !isset($_POST['tag']) OR $_POST['tag']=="0"){
		echo '<option selected value="0">primero seleccione unidad de negocios...';
	} else {
		echo '<option selected value="0">Seleccionar Cuenta de Abono...';
		$cuentatipoant = "";
		$comparaciones = "";
		while ($myrow=DB_fetch_array($AccountsResults)){
			if ($myrow['tipo'] != $cuentatipoant) {
				echo '<option value="0">******************************...';
			}
			$cuentatipoant = $myrow['tipo'];

			if ($_POST['BankAccount'] == $myrow['id']){
				echo '<option selected VALUE="'.$myrow['id'].'">' . $myrow['tipo'] . '' . $myrow['nombre'] . ' - ' . $myrow['currcode'];
			} else {
				echo '<option VALUE="'.$myrow['id'].'">' . $myrow['tipo'] . '' . $myrow['nombre'] . ' - ' . $myrow['currcode'];
			}
		}
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Fecha de Pago') . ':</td>';
	$permiso2 =Havepermission($_SESSION['UserID'],776, $db);
	$permiso =Havepermission($_SESSION['UserID'],410, $db);

	echo "<td></select>";
		echo'<select Name="ToDia">';
		$sql = "SELECT * FROM cat_Days";
		$Todias = DB_query($sql,$db);
		while ($myrowTodia=DB_fetch_array($Todias,$db)){
			$Todiabase=$myrowTodia['DiaId'];
			if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){
				echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" selected>' .$myrowTodia['Dia'];
			}else{
				echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
			}
		}
		echo "</select>";
		echo'  <select Name="ToMes">';
		$sql = "SELECT * FROM cat_Months";
		$ToMeses = DB_query($sql,$db);
		while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
			$ToMesbase=$myrowToMes['u_mes'];
			if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){
				echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" selected>' .$myrowToMes['mes'];
			}else{
				echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
			}
		}
		echo '</select>';
		echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
	echo "</td>";

	echo '<tr><td>' . _('Moneda') . ':</td><td><select name="Currency">';
	$SQL = 'SELECT currency, currabrev, rate FROM currencies WHERE currabrev = "MXN"';
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
		echo '</select></td></tr>';
		prnMsg( _('No existen monedas definidas. Pagos no pueden ser procesados hasta que las monedas sean definidas'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
			if (!isset($_POST['Currency'])){
				$_POST['Currency'] = $_SESSION['PaymentDetail']->Currency;
			}
			if ($_POST['Currency']==$myrow['currabrev']){
				echo '<option selected value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
			} else {
				echo '<option value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
			}
		}
		echo '</select></td></tr>';
	}


	echo '<tr><td>' . _('Tipo de Pago') . ':</td><td><select name="Paymenttype">';
	include('includes/GetPaymentMethods.php');

	foreach ($PaytTypes as $PaytType) {
		if (isset($_POST['Paymenttype']) and $_POST['Paymenttype']==$PaytType){
			echo '<option selected value="' . $PaytType . '">' . $PaytType;
		} else {
			echo '<option Value="' . $PaytType . '">' . $PaytType;
		}
	} //end foreach
	echo '</select></td></tr>';

	if (!isset($_POST['ChequeNum'])) {
		$_POST['ChequeNum']='';
	}

	echo '<tr><td>' . _('Cheque Numero') . ':</td>
		<td><input type="text" name="ChequeNum" maxlength=8 size=10 value="' . $_POST['ChequeNum'] . '"> ' . _('(o Referencia de traspaso)') . '</td></tr>';

	if (!isset($_POST['Narrative'])) {
		$_POST['Narrative']='';
	}

	if (!isset($_POST['Beneficiario'])) {
		$_POST['Beneficiario']=$_SESSION['PaymentDetail']->SuppName;
	}

	echo '<tr><td>' . _('Beneficiario') . ':</td>
		<td colspan=2><textarea name="Beneficiario" rows="1" cols="62" size=82>' . $_POST['Beneficiario'] . '</textarea>*solo en caso de chequera...</td></tr>';

	echo '<tr><td>' . _('Referencia / Concepto') . ':</td>
		<td colspan=2><input type="text" name="Narrative" maxlength=80 size=82 value="' . $_POST['Narrative'] . '">  ' . _('(Max. 80 caracteres de longitud)') . '</td></tr>';

	echo "<tr>";
		echo "<td colspan='2' style='text-align:center;'>";
			echo "<input type='submit' name='actualizar' value='Actualizar'>";
		echo "</td>";
	echo "</tr>";

	echo "<tr>";
		echo "<td colspan=2>";

			echo "<table border='1' cellpadding='0' cellspacing='0'>";
				echo "<tr>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Proveedor') . "</th>";
					//echo "<th>" . _('Numero de<br>Cheque') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Monto<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Monto<br>Impuestos<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Descuento<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Ret IVA<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Ret ISR<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Ret Cedular<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Ret Fletes<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Ret Comisi<br>(MXN)') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Ret Arrendam<br>(MXN)') . "</th>";
					//echo "<th>" . _('Beneficiario') . "</th>";
					echo "<th style='font-size:8px; font-weight:bold; text-align:center'>" . _('Concepto') . "</th>";
					echo "<th>" . _('Unidad de Negocio') . "</th>";
				echo "</tr>";
				//echo "<br>" . "entra aki2 --> " . $_SESSION['PaymentDetail']->GLItemCounter;
				$totalamount = 0;
				$totalamounttax = 0;
				$totaldiscountsupp = 0;
				$totalretencioniva = 0;
				$totalretencionisr = 0;
				$totalretencionxcedular = 0;
				$totalretencionxfletes = 0;
				$totalretencionxcomisiones = 0;
				$totalretencionarrendamiento = 0;
				$flagexistepago = false;
				foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
					$flagexistepago = true;
					echo "<tr>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:left'>" . $PaymentItem->SuppName . "</td>";
						//echo "<td align='right'>" . $_SESSION['PaymentDetail']->cheque . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->Amount,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->AmountTax,2)  . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->Discountsupp,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->RetencionIVA,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->RetencionISR,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->RetencionxCedular,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->RetencionxFletes,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->RetencionxComisiones,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($PaymentItem->RetencionxArrendamiento,2) . "</td>";
						//echo "<td>" . $_SESSION['PaymentDetail']->Beneficiary . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:left'>" . $PaymentItem->descripciongasto . "</td>";

						$SQL = 'SELECT tagname FROM tags WHERE tagref = "'.$PaymentItem->tagMovto.'"';
						$resultTAG=DB_query($SQL,$db);
						if ($myrowTAG=DB_fetch_array($resultTAG)) {
							echo "<td>" . $myrowTAG['tagname'] . "</td>";
						} else {
							echo "<td>no se encontro...!</td>";
						}

						echo "<td style='font-size:8px; font-weight:bold; text-align:center'><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Narrative=" . $_POST['Narrative'] . "&Beneficiario=" . $_POST['Beneficiario'] . "&ChequeNum=" . $_POST['ChequeNum'] . "&Paymenttype=" . $_POST['Paymenttype'] . "&Currency=" . $_POST['Currency'] . "&BankAccount=" . $_POST['BankAccount'] . "&ToYear=" . $_POST['ToYear'] . "&ToMes=" . $_POST['ToMes'] . "&ToDia=" . $_POST['ToDia'] . "&tag=" . $_POST['tag'] . "&legalid=" . $_POST['legalid'] . "&acreedor=" . $_POST['acreedor'] . "&Delete=" . $PaymentItem->ID . "'>" . _('Eliminar') . "</a></td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:center'><a href='" . $_SERVER['PHP_SELF'] . "?" . SID . "&Narrative=" . $_POST['Narrative'] . "&Beneficiario=" . $_POST['Beneficiario'] . "&ChequeNum=" . $_POST['ChequeNum'] . "&Paymenttype=" . $_POST['Paymenttype'] . "&Currency=" . $_POST['Currency'] . "&BankAccount=" . $_POST['BankAccount'] . "&ToYear=" . $_POST['ToYear'] . "&ToMes=" . $_POST['ToMes'] . "&ToDia=" . $_POST['ToDia'] . "&tag=" . $_POST['tag'] . "&legalid=" . $_POST['legalid'] . "&acreedor=" . $_POST['acreedor'] . "&DeleteModifica=" . $PaymentItem->ID . "'>" . _('Modificar') . "</a></td>";
					echo "</tr>";

					$totalamount = $totalamount + $PaymentItem->Amount;
					$totalamounttax = $totalamounttax + $PaymentItem->AmountTax;
					$totaldiscountsupp = $totaldiscountsupp + $PaymentItem->Discountsupp;
					$totalretencioniva = $totalretencioniva + $PaymentItem->RetencionIVA;
					$totalretencionisr = $totalretencionisr + $PaymentItem->RetencionISR;
					$totalretencionxcedular = $totalretencionxcedular + $PaymentItem->RetencionxCedular;
					$totalretencionxfletes = $totalretencionxfletes + $PaymentItem->RetencionxFletes;
					$totalretencionxcomisiones = $totalretencionxcomisiones + $PaymentItem->RetencionxComisiones;
					$totalretencionarrendamiento = $totalretencionarrendamiento + $PaymentItem->RetencionxArrendamiento;
				}
				if ($flagexistepago){
					echo "<tr>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:left'>" . _('Totales') . ":</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalamount,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalamounttax,2)  . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totaldiscountsupp,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalretencioniva,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalretencionisr,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalretencionxcedular,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalretencionxfletes,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalretencionxcomisiones,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'>" . number_format($totalretencionarrendamiento,2) . "</td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:right'></td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:center'></td>";
						echo "<td style='font-size:8px; font-weight:bold; text-align:center'></td>";
						echo "<td><input type='hidden' name='actualizar' value='Actualizar'></td>";
					echo "</tr>";

					echo "<tr>";
						echo "<td colspan='13'>&nbsp;</td>";
					echo "</tr>";
					echo "<tr>";
						echo "<td style='text-align:center' colspan='13'>";
							echo "<input type='submit' name='procesar' value='PROCESAR'>";
						echo "</td>";
					echo "</tr>";
				}
			echo "</table>";
		echo "</td>";

	echo "</tr>";

	echo "<tr><td><b>" . ('Busca Proveedor x Nombre') . "</b></td>";
		echo "<td><input type='text' name='proveedor' value='' size='60'>";
		echo "<input type='submit' name='buscarprov' value='Buscar'></td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td></td>";
		echo "<td>";
			echo "<select name='SupplierID'>";
				echo "<option value=''>" . _('Seleccione un Proveedor') . "</option>";
				$sql = "SELECT supplierid, suppname
					FROM suppliers where suppliers.currcode = '".$_POST['Currency']."' ";
				if (isset($_POST['proveedor']) and $_POST['proveedor'] != ""){
					$sql = $sql . " and suppname like '%" . $_POST['proveedor'] . "%'";
				}
				$sql = $sql . " Order By suppname asc";
				$result = DB_query($sql, $db);
				while ($myrow = DB_fetch_array($result)) {
					if ($_POST['SupplierID'] == $myrow['supplierid']){
						echo '<option selected value=' . $myrow['supplierid'] . '>' .$myrow['supplierid'] .'-'. $myrow['suppname'] . '</option>';
					} else {
						echo '<option value=' . $myrow['supplierid'] . '>' .$myrow['supplierid'] .'-' . $myrow['suppname'] . '</option>';
					}
				}

			echo "</select>";
			echo "<input type='submit' name='cuentasxproveedor' value='BUSCAR CUENTAS X PROVEEDOR'>";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td></td>";
		/*echo "<td>";
			echo "<select name='ctaproveedor'>";
				echo "<option value='0'>" . _('Seleccione un Cuenta') . "</option>";
				$sql = "SELECT ac.accountcode, ac.concepto, a.accountname
					FROM accountxsupplier ac
						LEFT JOIN chartmaster a ON ac.accountcode = a.accountcode
					WHERE a.accountname IS NOT NULL
					AND ac.supplierid = '" . $_POST['SupplierID'] . "'";

				$result = DB_query($sql, $db);
				while ($myrow = DB_fetch_array($result)) {
					if ($_POST['ctaproveedor'] == $myrow['accountcode']){
						echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . " - " . $myrow['accountname'] . '</option>';
					} else {
						echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . " - " . $myrow['accountname'] . '</option>';
					}
				}

			echo "</select>";*/
		echo "<td>";
		echo "<select name='ctaproveedor'>";
		echo "<option value='0'>" . _('Seleccione un Concepto') . "</option>";
		$sql = "SELECT ac.accountcode, ac.concepto, a.accountname
					FROM accountxsupplier ac
						LEFT JOIN chartmaster a ON ac.accountcode = a.accountcode
					WHERE a.accountname IS NOT NULL
					AND ac.supplierid = '" . $_POST['SupplierID'] . "'";

		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($_POST['ctaproveedor'] == $myrow['accountcode']){
				echo '<option selected value=' . $myrow['accountcode'] . '>' .$myrow['concepto'] . '</option>';
			} else {
				echo '<option value=' . $myrow['accountcode'] . '>' .$myrow['concepto'] . '</option>';
			}
		}

		echo "</select>";

			if (isset($_POST['SupplierID']))
				echo "<a target='_pestanaCuentasXProveedor' href='ABCCuentasxProveedor.php?" . SID . "&SupplierID=".$_POST['SupplierID']."'>agregar cuenta de este proveedor...</a>";

		echo "</td>";
	echo "</tr>";

	echo '<tr><td>' . _('Unidad de Negocio') . ':</td><td><select name="tagMovto">';

	$SQL = "SELECT t.tagref,t.tagdescription
		FROM sec_unegsxuser u,tags t
		WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' and t.legalid = '".$_POST['legalid']."' ORDER BY t.tagdescription";
	$result=DB_query($SQL,$db);
	if (DB_num_rows($result) == 0) {
		echo '<option selected value="0">primero seleccione razon social...';
	} else {
		echo '<option selected value="0">Seleccionar una Unidad de Negocio';
		while ($myrow=DB_fetch_array($result)){
			if ((isset($_POST['tagMovto']) and $_POST['tagMovto'] != "0" and $_POST['tagMovto']==$myrow['tagref']) OR ($_POST['tagMovto'] == "0" AND $_POST['tag']==$myrow['tagref'])){
				echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
			} else {
				echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
			}
		}
	}
	echo '</select>';
	echo "</td><tr>";


	//if (isset($_POST['SupplierID']) && $_POST['SupplierID'] != "" && $_POST['SupplierID'] != "0"){
	if ((isset($_POST['actualizar']) or isset($_POST['UpdateHeader']) or isset($_GET['Delete']) or isset($_GET['DeleteModifica']) or isset($_POST['buscarprov']) or isset($_POST['cuentasxproveedor'])) and ($error==0)){
		echo "<tr>";
			echo "<td></td>";
			echo "<td>";
				echo "<table border='0' cellpadding='0' cellspacing='0'>";
					echo '<tr><td>' . _('Categoria Impuestos') . ':</td><td><select name="TaxCat">';
					$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
					$result = DB_query($sql, $db);

					if (!isset($_POST['TaxCat'])){
						$_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
					}

					while ($myrow = DB_fetch_array($result)) {
						if ($_POST['TaxCat'] == $myrow['taxcatid']){
							echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
						} else {
							echo '<option value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
						}
					}

					echo '</select></td></tr>';

					echo '<tr><td>' . _('Monto Impuesto') . ':</td>
						<td><input type="text" name="mntIVA" maxlength=12 size=13 value="' . $_POST['mntIVA'] . '">'._('*solo en caso de ser diferente la tasa que la seleccion de arriba').'</td></tr>';

					echo '<tr><td>' . _('Monto del Pago ') . ':</td>
						<td><input type="text" name="Amount" maxlength=12 size=13 value=' . $_POST['Amount'] . '>*MONTO FINAL A PAGAR!, despues de retenciones, descuentos e incluyendo IVA...</td></tr>';

					echo '<tr><td>' . _('Monto de Descuento') . ':</td>
						<td colspan=2><input type="text" name="Discount" maxlength=12 size=13 value=' . $_POST['Discount'] . '></td>
						</tr>';


					echo '<tr><td>' . _('Retencion IVA')  . ':</td>
						  <td><input type="text" name="RetencionIVA" maxlength=12 size=13 value="' . $_POST['RetencionIVA'] . '"></td></tr>';

					echo '<tr><td>' . _('Retencion ISR')  .  ':</td>
						  <td><input type="text" name="RetencionISR" maxlength=12 size=13 value="' . $_POST['RetencionISR'] . '"></td></tr>';

					echo '<tr><td>' . _('Retencion x Arrendamiento ')  . ':</td>
						  <td><input type="text" name="RetencionxIVAArrendamiento" maxlength=12 size=13 value="'. $_POST['RetencionxIVAArrendamiento'] . '"></td></tr>';

					echo '<tr><td>' . _('Retencion x Comisiones ')  . ':</td>
						  <td><input type="text" name="RetencionxComisiones" maxlength=12 size=13 value="' . $_POST['RetencionxComisiones'] . '"></td></tr>';

					echo '<tr><td>' . _('Retencion x Fletes ')  . ':</td>
						<td><input type="text" name="RetencionxFletes" maxlength=12 size=13 value="' . $_POST['RetencionxFletes'] . '"></td></tr>';

					echo '<tr><td>' . _('Retencion x Cedular ')  . ':</td>
						<td><input type="text" name="RetencionxCedular" maxlength=12 size=13 value="' . $_POST['RetencionxCedular'] . '"></td></tr>';

					echo '<tr><td>' . _('Descripcion del Gasto')  . ':</td>
						<td><input type="text" name="descripciongasto" maxlength=100 size=50 value="' . $_POST['descripciongasto'] .'"></td></tr>';

					echo '<tr><td>' . _('Referencia / Factura')  . ':</td>
						<td><input type="text" name="referencia" maxlength=100 size=50 value="' . $_POST['referencia'] . '"></td></tr>';
				echo "</table>";
			echo "</td>";
		echo "</tr>";

		echo '<tr>';
			echo '<td colspan=3>';
				echo "<input type='hidden' name='ID' value='" . $ID. "'>";
				echo '<div class="centre"><input type="submit" name="UpdateHeader" value="' . _('Agregar'). '"></td>';
		echo '</tr>';
	}
echo '</table><br>';

echo '</form>';

include('includes/footer.inc');
?>
