<?php

/* $Revision: 1.38 $ */
/*Cambios:
1.- Se le agrego el  include('includes/SecurityFunctions.inc');*/
$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');
$funcion=401;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

$title = _('Modulo de Cheques');
//$_SESSION['DefaultDateFormat'];
//echo "DIA:".(Date($_SESSION['DefaultDateFormat']));
include('includes/header.inc');

if (isset($_SESSION['PaymentDetail']->Currency)){
	$Currency=$_SESSION['PaymentDetail']->Currency;
}

if (isset($_SESSION['PaymentDetail']->SuppName)){
	$SuppName=$_SESSION['PaymentDetail']->SuppName;
}

if (isset($_POST['ChequeNum'])){
	$ChequeNum=$_POST['ChequeNum'];
} elseif (isset($_GET['ChequeNum'])){
	$ChequeNum=$_GET['ChequeNum'];
}

if (isset($_POST['PaymentCancelled'])) {
	prnMsg(_('Pago cancelado pues cheque no fue impreso'), 'warning');
	include('includes/footer.inc');
	exit();
}

if (isset($_GET['NewPayment']) and $_GET['NewPayment']=='Yes'){
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
}

if (!isset($_SESSION['PaymentDetail'])){
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;
}

//if (!isset($_SESSION['RetencionIVA'])){
//	$_SESSION['RetencionIVA'] = $_POST['RetencionIVA'];
//}

//if (!isset($_SESSION['RetencionIVA'])){
//	$_SESSION['RetencionIVA'] = $_POST['RetencionIVA'];
//}
//note this is already linked from this page
echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Ir a pagina de Proveedores') . '</a><br>';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Captura de Pagos') . '" alt="">' . ' ' . _('Pagos de Cheques a Multiples Proveedores') . '</p>';
echo '<div class="centre">';

/**********************************************/
/*VALIDA SI EL PAGO VA A SER PARA UN PROVEEDOR*/
if (isset($_GET['Inicializa'])){
	/*The page was called with a supplierID check it is valid and
	default the inputs for Supplier Name and currency of payment */

	//echo "inicializa";

	/*INICIALIZA LA CLASE DE LINEAS CONTABLES*/
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
	
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;


} //FIN DE IF SI PROVEEDOR SELECCIONADO
//echo '<pre>xxxx:'.var_dump($_SESSION['PaymentDetail']);

/* SI CUENTA DE CHEQUES FUE SELECCIONADA ASIGNA VALORES A CLASE */
if (isset($_POST['BankAccount']) and $_POST['BankAccount']!=''){
	$_SESSION['PaymentDetail']->Account=$_POST['BankAccount'];
	
	/*Get the bank account currency and set that too */
	$ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
	$result = DB_query('SELECT currcode FROM bankaccounts WHERE accountcode =' . $_POST['BankAccount'],$db,$ErrMsg);
	$myrow = DB_fetch_row($result);
	$_SESSION['PaymentDetail']->AccountCurrency=$myrow[0];
} else {
	$_SESSION['PaymentDetail']->AccountCurrency =$_SESSION['CompanyRecord']['currencydefault'];
}


if (isset($_POST['DatePaid']) and $_POST['DatePaid']!='' AND Is_Date($_POST['DatePaid'])){
	$_SESSION['PaymentDetail']->DatePaid=$_POST['DatePaid'];
	//echo 'fecha:'.$_SESSION['PaymentDetail']->DatePaid;
}
if (isset($_POST['ExRate']) and $_POST['ExRate']!=''){
	$_SESSION['PaymentDetail']->ExRate=$_POST['ExRate']; //ex rate between payment currency and account currency
}
if (isset($_POST['FunctionalExRate']) and $_POST['FunctionalExRate']!=''){
	$_SESSION['PaymentDetail']->FunctionalExRate=$_POST['FunctionalExRate']; //ex rate between payment currency and account currency
}

if (isset($_POST['Paymenttype']) and $_POST['Paymenttype']!=''){
	$_SESSION['PaymentDetail']->Paymenttype = $_POST['Paymenttype'];
}

/* CALCULA LOS TIPOS DE CAMBIO SI MONEDA NO MXN */
if (isset($_POST['Currency']) and $_POST['Currency']!=''){
	$_SESSION['PaymentDetail']->Currency=$_POST['Currency']; //payment currency
	
	/*Get the exchange rate between the functional currency and the payment currency*/
	$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency . "'",$db);
	$myrow = DB_fetch_row($result);
	$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency

	if ($_POST['Currency']==$_SESSION['PaymentDetail']->AccountCurrency){
		$_POST['ExRate']=1;
		$_SESSION['PaymentDetail']->ExRate=$_POST['ExRate']; //ex rate between payment currency and account currency
		$SuggestedExRate=1;
	}
	if ($_SESSION['PaymentDetail']->AccountCurrency==$_SESSION['CompanyRecord']['currencydefault']){
		$_POST['FunctionalExRate']=1;
		$_SESSION['PaymentDetail']->FunctionalExRate=$_POST['FunctionalExRate'];
		$SuggestedFunctionalExRate =1;
		$SuggestedExRate = $tableExRate;

	} else {
		/*To illustrate the rates required
			Take an example functional currency NZD payment in USD from an AUD bank account
			1 NZD = 0.80 USD
			1 NZD = 0.90 AUD
			The FunctionalExRate = 0.90 - the rate between the functional currency and the bank account currency
			The payment ex rate is the rate at which one can purchase the payment currency in the bank account currency
			or 0.8/0.9 = 0.88889
		*/

		/*Get suggested FunctionalExRate */
		$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->AccountCurrency . "'",$db);
		$myrow = DB_fetch_row($result);
		$SuggestedFunctionalExRate = $myrow[0];

		/*Get the exchange rate between the functional currency and the payment currency*/
		$result = DB_query("select rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency . "'",$db);
		$myrow = DB_fetch_row($result);
		$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
		/*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
		$SuggestedExRate = $tableExRate/$SuggestedFunctionalExRate;

	}
} //FIN DE VERIFICACION DEL TIPO DE CAMBIO


if (isset($_POST['Narrative']) and $_POST['Narrative']!=''){
	$_SESSION['PaymentDetail']->Narrative=$_POST['Narrative'];
}

if (isset($_POST['Amount']) and $_POST['Amount']!=""){
	$_SESSION['PaymentDetail']->Amount=0; //$_POST['Amount'];
} else {
	if (!isset($_SESSION['PaymentDetail']->Amount)) {
		$_SESSION['PaymentDetail']->Amount=0;
	}
}


$taxrate = 0;
if (isset($_POST['TaxCat']) and $_POST['TaxCat']!=""){
	$result = DB_query("select taxrate FROM taxauthrates WHERE taxcatid=" . $_POST['TaxCat'],$db);
	$myrow = DB_fetch_row($result);
	$taxrate = $myrow[0];	
	//echo 'ENTRO A BUSQUEDA DE TAXID';
}

if (isset($_POST['Discount']) and $_POST['Discount']!=''){
	$_SESSION['PaymentDetail']->Discount=$_POST['Discount'];
} else {
	if (!isset($_SESSION['PaymentDetail']->Discount)) {
	  $_SESSION['PaymentDetail']->Discount=0;
  }
}


$msg='';

/***************************************************************************/
/**************** INICIA PROCESO Y CONFIRAMCION DE MOVIMIENTOS *************/
if (isset($_POST['CommitBatch'])){

  /* once the GL analysis of the payment is entered (if the Creditors_GLLink is active),
  process all the data in the session cookie into the DB creating a banktrans record for
  the payment in the batch and SuppTrans record for the supplier payment if a supplier was selected
  A GL entry is created for each GL entry (only one for a supplier entry) and one for the bank
  account credit.

  NB allocations against supplier payments are a separate exercice

  if GL integrated then
  first off run through the array of payment items $_SESSION['Payment']->GLItems and
  create GL Entries for the GL payment items
  */

	/*First off  check we have an amount entered as paid ?? */
	$TotalAmount =0;
	foreach ($_SESSION['PaymentDetail']->GLItems AS $PaymentItem) {
		$TotalAmount += $PaymentItem->Amount;
	}

	//echo ($_SESSION['PaymentDetail']->Discount + $_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate . "<br>";
	//echo $_SESSION['PaymentDetail']->Discount . "<br>";
	//echo $_SESSION['PaymentDetail']->Amount . "<br>";
	//echo $_SESSION['PaymentDetail']->ExRate . "<br>";
	//echo $TotalAmount;

	if ($TotalAmount==0 AND
		($_SESSION['PaymentDetail']->Discount + $_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate ==0){
		prnMsg( _('Este pago no tiene cantidades capturadas y no sera procesado'),'warn');
		include('includes/footer.inc');
		exit;
	}


	if ($ChequeNum ==""){
		prnMsg( _('Este pago no contiene un numero de Cheque'),'warn');
		echo '<br>';
		prnMsg (_('<a href="#" onclick="history.go(-1); return false;">Regresar</a>'),'warn');
		include('includes/footer.inc');
		exit;
	}



	/*Make an array of the defined bank accounts */
	$SQL = 'SELECT bankaccounts.accountcode
		FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
		WHERE bankaccounts.accountcode=chartmaster.accountcode and
			bankaccounts.accountcode = tagsxbankaccounts.accountcode and
			tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '" 
		GROUP BY bankaccounts.accountcode';
			
	$result = DB_query($SQL,$db);
	$BankAccounts = array();
	$i=0;

	while ($Act = DB_fetch_row($result)){
		$BankAccounts[$i]= $Act[0];
		$i++;
	}
	//descomponer fecha
	$PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid, $db, $_POST['tag']);
	
	//echo 'PERIODO AFECTADO: ->'.$PeriodNo. ' - '. $fechax . ' SI ESTE PERIODO ESTA MAL FAVOR DE IMPRIMIR ESTA PANTALLA Y REPORTARLO A GONZALO...';
	

	if (isset($_POST['tag'])){
		
		///echo $_POST['tag'];
		if ($_POST['tag'] == "0"){
				
				//echo _('Debe de Seleccionar una Unidad de Negocio') . '?<br><br>';
				prnMsg(_('Debe de Seleccionar una Unidad de Negocio'),'error');
				echo '<br>';
				prnMsg (_('<a href="#" onclick="history.go(-1); return false;">Regresar</a>'),'error');
				exit;
			
		}
		
	}		
	
	// first time through commit if supplier cheque then print it first
	if ((!isset($_POST['ChequePrinted']))
		AND (!isset($_POST['PaymentCancelled']))
		AND ($_SESSION['PaymentDetail']->Paymenttype == 'Cheque')) {
		
		// it is a supplier payment by cheque and haven't printed yet so print cheque

		//echo '<br><a href="' . $rootpath . '/PrintCheque.php?' . SID . '&ChequeNum=' . $ChequeNum .'&TransNo='.$TransNo.'&Currency='.$Currency.'&SuppName='.$SuppName.'">' . _('Imprimir Cheque usando formato pre-impreso') . '</a><br><br>';

		echo '<form method=post action="' . $_SERVER['PHP_SELF'] . '">';
		echo _('Fue impreso el cheque') . '?<br><br>';
		echo '<input type="hidden" name="CommitBatch" VALUE="' . $_POST['CommitBatch'] . '">';
		echo '<input type="hidden" name="tag" VALUE="' . $_POST['tag'] . '">';
		echo '<input type="hidden" name="TaxCat" VALUE="' . $_POST['TaxCat'] . '">';
		echo '<input type="hidden" name="ChequeNum" VALUE="' . $_POST['ChequeNum'] . '">';
		echo '<input type="submit" name="ChequePrinted" VALUE="' . _('Si / Continua') . '">&nbsp;&nbsp;';
		echo '<input type="submit" name="PaymentCancelled" VALUE="' . _('No / Cancela Pago') . '">';
		echo '<input type="hidden" name="RetencionIVA" VALUE="' . $_POST['RetencionIVA'] . '">';
		echo '<input type="hidden" name="RetencionISR" VALUE="' . $_POST['RetencionISR'] . '">';
		echo '<input type="hidden" name="Beneficiario" VALUE="' . $_POST['Beneficiario'] . '">';
		echo '<input type="hidden" name="RetencionxCedular" VALUE="' . $_POST['RetencionxCedular'] . '">';
		echo '<input type="hidden" name="RetencionxFletes" VALUE="' . $_POST['RetencionxFletes'] . '">';
		echo '<input type="hidden" name="RetencionxComisiones" VALUE="' . $_POST['RetencionxComisiones'] . '">';
		echo '<input type="hidden" name="RetencionxArrendamiento" VALUE="' . $_POST['RetencionxArrendamiento'] . '">';
				
		
		
	} else {

		//Start a transaction to do the whole lot inside
	//	$SQL = 'BEGIN';
		//$result = DB_query($SQL,$db);
		$result = DB_Txn_Begin($db);

		// PAGO CONTABLE ....
		if ($_SESSION['PaymentDetail']->SupplierID=='' and false) {

			

		} else {
			
		/*********************************************************************/
		// INICIO DE PAGO A PROVEEDOR
		/*Its a supplier payment type 22 */

			$Beneficiario = $_POST['Beneficiario'];					
			$TotalAmount =0;
			
			foreach ($_SESSION['PaymentDetail']->GLItems AS $PaymentItem) {
				$TotalAmount += $PaymentItem->Amount;
				$TotalDescuento += $PaymentItem->Discountsupp;
				$sumaRetenciones += ($PaymentItem->RetencionIVA + $PaymentItem->RetencionISR + $PaymentItem->RetencionxArrendamiento + $PaymentItem->RetencionxComisiones + $PaymentItem->RetencionxFletes + $PaymentItem->RetencionxCedular);
			}
			
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {	

				$TransNo = GetNextTransNo(22, $db);
				$Transtype = 22;
				/* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
				/* Create a SuppTrans entry for the supplier payment */
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
							ref1
							) ";
				$SQL = $SQL . 'VALUES (' . $TransNo . ",
						22,
						'" . $PaymentItem->SupplierID . "',
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'" . $_SESSION['PaymentDetail']->Paymenttype . "',
						" . ($_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) . ",
						" . (-$PaymentItem->Amount+$PaymentItem->Discountsupp) . ",
						'" . $PaymentItem->Narrative . "',
						" . $_POST['tag'] . ",
						now(),
						'" . $ChequeNum . "'
					)";
				
				//echo $SQL . "<br>";
				//exit;
				$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
				$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
				/* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
				/*Update the supplier master with the date and amount of the last payment made */
				$SQL = "UPDATE suppliers SET
					lastpaiddate = '" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
					lastpaid=" . $PaymentItem->Amount ."
					WHERE suppliers.supplierid='" . $PaymentItem->SupplierID . "'";
	
				$ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
				$DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				$supplierid = $PaymentItem->SupplierID;

			}
			
			
			

			/* SI ESTA HABILITADA LA INTEGRACION CONTABLE CON CUENTAS POR PAGAR */
			if ($_SESSION['CompanyRecord']['gllink_creditors']==1){
				/* then do the supplier control GLTrans */
				/* Now debit creditors account with payment + discount */


				//foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {	
				//}

					//$CreditorTotal = ($PaymentItem->Amount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;
					$_SESSION['PaymentDetail']->Narrative = $PaymentItem->SupplierID . "-" . $PaymentItem->Narrative;
					//$sumaRetenciones = ($PaymentItem->RetencionIVA + $PaymentItem->RetencionISR + $PaymentItem->RetencionxArrendamiento + $PaymentItem->RetencionxComisiones + $PaymentItem->RetencionxFletes + $PaymentItem->RetencionxCedular);
					
					
					//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
					if ($supplierid != ''){
						$tipoproveedor = ExtractTypeSupplier($supplierid,$db);
						if ((isset($_POST['anticipo'])) and ($_POST['anticipo']!='')){
							$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_debtoradvances",$db);
						}else{
							$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
						}
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
							22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							'" . $ctaxtipoproveedor . "',
							'" .$PaymentItem->Narrative . "',";
													

					$SQL=$SQL   .	($TotalAmount - $TotalDescuento - $sumaRetenciones)   . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
							
						)";

					
						
					$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
					$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');					
				//	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					$Narrative=$PaymentItem->Narrative." @ ".$Beneficiario;
					$montocontable=($TotalAmount - $TotalDescuento - $sumaRetenciones);
					// ejecuta funcion para insertar cargo en tabla de contabilidad
					$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $ctaxtipoproveedor,$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'PROVEEDOR');
					$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
					$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
			

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
							22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['PaymentDetail']->Account . ",
							'" .$_SESSION['PaymentDetail']->Narrative . "',
							" . (-1)*($TotalAmount - $TotalDescuento ) . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
					$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
					$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
					//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);		
					$montocontable=($TotalAmount - $TotalDescuento - $sumaRetenciones)*(-1);
					$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),
							$PeriodNo, $_SESSION['PaymentDetail']->Account,$Narrative, $_POST['tag'] ,
							$_SESSION['UserID'],$_SESSION['PaymentDetail']->ExRate,'','','',
							0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'PROVEEDOR');
					$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
					$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					
					
				foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
					
					/**************************************************/
					/*MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
					
					$CreditorTotal = ($PaymentItem->Amount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;
					
					//$SQL = 'select * from taxauthorities where taxid=1'.$_SESSION['DefaultTaxCategory'];
					//$SQL = 'select * from taxauthorities where taxid=1';
					$SQL = 'select * from taxcategories where taxcatid='.$PaymentItem->taxrate;
					$result2 = DB_query($SQL,$db);
					if ($TaxAccs = DB_fetch_array($result2)){

						//$taximpuesto=($CreditorTotal / (1 +$taxrate));
						//$taximpuesto=$CreditorTotal-$taximpuesto;

						//echo 
						$sql = "select taxrate from taxauthrates where taxcatid = '" . $TaxAccs['taxid'] . "'";
						$resultx = DB_query($sql, $db);
						if ($myrowtx = DB_fetch_array($resultx)) {
							$tasaiva = $myrowtx['taxrate'];
						} else {
							$tasaiva = 0;
							$texto='IVA 0%';
						}
						//

						if ($PaymentItem->AmountTax != 0)
							$taximpuesto=$PaymentItem->AmountTax;
						else{
							$taximpuesto=($CreditorTotal / (1 + $PaymentItem->taxrate));
							$taximpuesto=$CreditorTotal-$taximpuesto;							
						}


						////
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
								22,
								" . $TransNo . ",
								'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
								" . $PeriodNo . ",
								" . $TaxAccs['purchtaxglaccount'] . ",
								'" . $_SESSION['PaymentDetail']->Narrative . "',
								" . ($taximpuesto*-1) . ",
								" . $_POST['tag'] . ",
								'" . $ChequeNum . "'
							)";
						$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
						$DbgMsg = _('El SQL utilizado fue');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;//$PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate .$texto. " - reembolso de gastos / IVA1";
						$montocontable=(($taximpuesto)*-1);
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $TaxAccs['purchtaxglaccount'],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'IVA '.$TaxAccs['taxcatname']);
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);

						//echo "query iva1 ------------<br>";
						//echo $SQL;

						
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
								22,
								" . $TransNo . ",
								'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
								" . $PeriodNo . ",
								" . $TaxAccs['purchtaxglaccountPaid'] . ",
								'" . $_SESSION['PaymentDetail']->Narrative . "',
								" . $taximpuesto . ",
								" . $_POST['tag'] . ",
								'" . $ChequeNum . "'
							)";
						$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
						$DbgMsg = _('El SQL utilizado fue');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;//$PaymentItem->referencia . " @ "  . $_SESSION['PaymentDetail']->rate .$texto. " - reembolso de gastos / IVA1";
						$montocontable=(($taximpuesto));
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $TaxAccs['purchtaxglaccount'],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'IVA '.$TaxAccs['taxcatname']);
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
						
						//echo "query iva2 ------------<br>";
						//echo $SQL . "<br><br>";

											
					} //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS
				
				
					//echo "Descuento " . $PaymentItem->Discount;
					
					//MOVIMIENTO DE DESCUENTOS
					if ($PaymentItem->Discountsupp !=0){
			
						$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									tag,
									chequeno) ";
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["pytdiscountact"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . (-$PaymentItem->Discountsupp/$_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable=(-$PaymentItem->Discountsupp/$_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) ;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $_SESSION['CompanyRecord']["pytdiscountact"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'DESCUENTO');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
						
	
						$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									tag,
									chequeno) ";
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["payrollact"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . ($PaymentItem->Discountsupp/$_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable=($PaymentItem->Discountsupp/$_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) ;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $_SESSION['CompanyRecord']["payrollact"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'DESCUENTO');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
	
						
					} // end if discount


			///////////////////RETENCIONES
			

					if ($PaymentItem->RetencionIVA !=0){
						
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
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["gllink_retencioniva"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $PaymentItem->RetencionIVA . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para la descuento retencion iva porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento retencion iva utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable= $PaymentItem->RetencionIVA;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,$_SESSION['CompanyRecord']["gllink_retencioniva"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'IVA RETENIDO');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
						
					} // end if discount				

	
					if ($PaymentItem->RetencionISR !=0){
						
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
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["gllink_retencionhonorarios"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $PaymentItem->RetencionISR . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento isr porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento isr utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable= $PaymentItem->RetencionISR;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,$_SESSION['CompanyRecord']["gllink_retencionhonorarios"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'ISR');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if discount
					
							
					if ($PaymentItem->RetencionxArrendamiento !=0){
						
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
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["gllink_retencionarrendamiento"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $PaymentItem->RetencionxArrendamiento . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable= $PaymentItem->RetencionxArrendamiento;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,$_SESSION['CompanyRecord']["gllink_retencionarrendamiento"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'RETENCION ARRENDAMIENTO');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if discount						
			


					if ($PaymentItem->RetencionxComisiones !=0){
						
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
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["gllink_retencionComisiones"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $PaymentItem->RetencionxComisiones . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento comisiones porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento comisiones utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable= $PaymentItem->RetencionxComisiones;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,$_SESSION['CompanyRecord']["gllink_retencionComisiones"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'RETENCION COMISIONES');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if discount				




					if ($PaymentItem->RetencionxFletes !=0){
						
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
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["gllink_retencionFletes"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $PaymentItem->RetencionxFletes . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento fletes porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento fletes utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable= $PaymentItem->RetencionxFletes;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,$_SESSION['CompanyRecord']["gllink_retencionFletes"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'RETENCION FLETE');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if discount				



					if ($PaymentItem->RetencionxCedular !=0){
						
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
						$SQL=$SQL . "VALUES (22,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['CompanyRecord']["gllink_retencionCedular"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $PaymentItem->RetencionxCedular . ",
							" . $_POST['tag'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento cedular porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento cedular utilizando el SQL');
						//$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						$Narrative= $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
						$montocontable= $PaymentItem->RetencionxCedular;
						// ejecuta funcion para insertar cargo en tabla de contabilidad
						$ISQL = Insert_Gltrans(22,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,$_SESSION['CompanyRecord']["gllink_retencionCedular"],$Narrative,$_POST['tag'] ,$_SESSION['UserID'],
								$_SESSION['PaymentDetail']->ExRate,
								'','','',0,0,'',0,$supplierid,0, $montocontable,$db,$ChequeNum,'RETENCION CEDULAR');
						$DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
						$ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
						$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					} // end if discount				

				}
				
			}				
				/**************************************************/
								
				
			//} // end if gl creditors
			//echo "total monto cheque " . $TotalAmount;
			
		} // FIN DE IF DE PAGO A PROVEEDOR


		/* AHORA CAPTURA LAS TRANSACCIONES BANCARIAS */
		//echo "Transtype " . $Transtype;
		if ($Transtype==22) {
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
					chequeno) ";
			//echo $SQL . "<br>";		
			$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				" . $_SESSION['PaymentDetail']->Account . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . $_SESSION['PaymentDetail']->ExRate . " ,
				" . $_SESSION['PaymentDetail']->FunctionalExRate . ",
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
			//echo $SQL . "<br>";		
						
				
			$SQL .=	($TotalAmount - $TotalDescuento - $sumaRetenciones) * (-1) . ",
				'" . $_SESSION['PaymentDetail']->Currency . "',
				" . $_POST['tag'] . ",
				'" . $Beneficiario . "',
				'" . $ChequeNum . "'
			)";

			//echo "<br>operacion ->" .floatval($RetencionIVA + $RetencionISR) . "<br>";
			//echo "<br>Consulta -> ". $SQL . "<br>";	
			$ErrMsg = _('No pude insertar la transaccion bancaria porque');
			$DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			//echo "query bancos ------------<br>";
			//echo $SQL . "<br><br>";

			//exit;
			
		} else {
		}

		$SQL = "COMMIT";
		$ErrMsg = _('No pude hacer COMMIT de los cambios porque');
		$DbgMsg = _('El COMMIT a la base de datos fallo');
		//$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);


		$result = DB_Txn_Commit($db);
		
		prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('ha sido exitosamente procesado'),'success');
		
		$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
		$lasttag=$_POST['tag'];
		
	//	$liga = GetUrlToPrint($_POST['tag'],$Transtype,$db);
	$fechaoper=$_SESSION['PaymentDetail']->DatePaid;
		//echo 'tipo:'.$Transtype;
		unset($_POST['BankAccount']);
		unset($_POST['DatePaid']);
		unset($_POST['ExRate']);
		unset($_POST['Paymenttype']);
		unset($_POST['Currency']);
		unset($_POST['Narrative']);
		unset($_POST['Amount']);
		unset($_POST['Discount']);
		unset($_SESSION['PaymentDetail']->GLItems);
		unset($_SESSION['PaymentDetail']);

		/*Set up a newy in case user wishes to enter another */
		
		/*$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
		. '" alt="">' . ' ' .
		'<a  target="_blank" href="' . $rootpath . '/'. $liga . SID .'&Tagref='.$lasttag.'&TransNo='.$TransNo.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
				
		//echo '<br><a href="' . $rootpath . '/PrintCheque.php?' . SID . '&ChequeNum=' . $ChequeNum .'&TransNo='.$TransNo.'&Currency='.$Currency.'&SuppName='.$SuppName.'" target="_blank">' . _('Imprimir Cheque usando formato pre-impreso') . '</a><br>';
		echo '<br>'.$liga.'<br>';*/
		
		
		echo '<br><div class="centre">';
		$liga="PrintJournal2.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $Transtype . "&TransNo=" . $TransNo . "&periodo=" . $PeriodNo . "&trandate=" .$fechaoper;
		echo "<a TARGET='_blank' href='" . $liga . "'>" . _('Imprimir Cheque Poliza') . "</a></div>";
			
		/**************************/
			
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
		. '" alt="">' . ' ' .
		'<a  target="_blank" href="' . $rootpath . '/PrintCheque_01'.$pdfprefix.'.php?' . SID .'&TransNo='.$TransNo.'&type='.$Transtype.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
		
			
		echo $liga.'<br>';
		
		
		
		echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Capturar pago contable') . '</a><br>';
		echo '<br><a href="' . $rootpath . '/PaymentsMult.php?SupplierID=' . $lastSupplier . '" >' . _('Capturar otro pago a este proveedor') . '</a>';
	}

	include('includes/footer.inc');
	exit;

} elseif (isset($_GET['Delete'])){
	/* User hit delete the receipt entry from the batch */
	$_SESSION['PaymentDetail']->Remove_GLItem($_GET['Delete']);
	
} elseif (isset($_POST['Process'])){
	//user hit submit a new GL Analysis line into the payment

	$ChequeNoSQL='select account from gltrans where chequeno="'.$_POST['cheque'].'" and tag = "'.$_POST['tag'].'"';
	$ChequeNoResult=DB_query($ChequeNoSQL, $db);

	if ($_POST['GLManualCode']!="" AND is_numeric($_POST['GLManualCode'])){

		$SQL = "select accountname
			FROM chartmaster
			WHERE accountcode=" . $_POST['GLManualCode'];

		$Result=DB_query($SQL,$db);

		if (DB_num_rows($Result)==0){
			prnMsg( _('El codigo contable capturado manualmente no existe') . ' - ' . _('asi que esta linea de la aplicacion contable no se pudo agregar'),'warn');
			unset($_POST['GLManualCode']);
		} else if (DB_num_rows($ChequeNoResult)!=0 and $_POST['cheque']!=''){
			prnMsg( _('El numero de Cheque/Deposito ya ha sido utilizado') . ' - ' . _('asi que esta linea de la aplicacion contable no se pudo agregar'),'error');
		} else {
			$myrow = DB_fetch_array($Result);
			$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
								$_POST['GLNarrative'],
								$_POST['GLManualCode'],
								$myrow['accountname'],
								$_POST['tag'],
								$_POST['cheque'],
								$_POST['Beneficiary2']
								);
		}
		
	} else if (DB_num_rows($ChequeNoResult)!=0 and $_POST['cheque']!=''){
		prnMsg( _('El numero de Cheque/Deposito ya ha sido utilizado') . ' - ' . _('asi que esta linea de la aplicacion contable no se pudo agregar'),'error');
	} else {
		$SQL = "select accountname FROM chartmaster WHERE accountcode=" . $_POST['GLCode'];
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_array($Result);
		$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
							$_POST['GLNarrative'],
							$_POST['GLCode'],
							$myrow['accountname'],
							$_POST['tag'],
							$_POST['cheque'],
							$_POST['Beneficiary2']
							);
	}

	/*Make sure the same receipt is not double processed by a page refresh */
	$_POST['Cancel'] = 1;



} elseif (isset($_POST['AddSupp'])){



	//echo $_POST['Discount'];
	$_SESSION['PaymentDetail']->add_to_glanalysisAddSupp($_POST['Amount'],
						$_POST['Narrative'],
						$_POST['Beneficiario'],
						$_POST['Narrative'],
						$_POST['tag'],
						$_POST['ChequeNum'],
						'0',
						'0',
						'nombreproveedor',
						$_POST['supplierid'],
						$_POST['Discount'],
						$_POST['RetencionIVA'],
						$_POST['RetencionISR'],
						$_POST['RetencionxCedular'],
						$_POST['RetencionxFletes'],
						$_POST['RetencionxComisiones'],
						$_POST['RetencionxArrendamiento'],
						$_POST['AmountTax'],
						$taxrate,
						$_POST['TaxCat']
						);
		
}


if (isset($_POST['Cancel'])){
	unset($_POST['GLAmount']);
	unset($_POST['GLNarrative']);
	unset($_POST['GLCode']);
	unset($_POST['AccountName']);
}

/*set up the form whatever */
if (!isset($_POST['DatePaid'])) {
	$_POST['DatePaid'] = '';
}

if (isset($_POST['DatePaid']) and ($_POST['DatePaid']=="" OR !Is_Date($_SESSION['PaymentDetail']->DatePaid))){
	$_POST['DatePaid']= Date($_SESSION['DefaultDateFormat']);
	$_SESSION['PaymentDetail']->DatePaid = $_POST['DatePaid'];
}

if ($_SESSION['PaymentDetail']->Currency=='' AND $_SESSION['PaymentDetail']->SupplierID==''){
	$_SESSION['PaymentDetail']->Currency=$_SESSION['CompanyRecord']['currencydefault'];
}

if (isset($_POST['BankAccount']) AND $_POST['BankAccount']!='') {
	$SQL = "SELECT bankaccountname
			FROM bankaccounts,
				chartmaster
		WHERE bankaccounts.accountcode= chartmaster.accountcode
		AND chartmaster.accountcode=" . $_POST['BankAccount'];

	$ErrMsg = _('El nombre de la cuenta no se pudo recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar el nombre de la cuenta fue');

	$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($result)==1){
		$myrow = DB_fetch_row($result);
		$_SESSION['PaymentDetail']->BankAccountName = $myrow[0];
		unset($result);
	} elseif (DB_num_rows($result)==0){
		prnMsg( _('El numero de la cuenta de cheques') . ' ' . $_POST['BankAccount'] . ' ' . _('no esta configurada con una cuenta contable asociada valida'),'error');
	}
}


echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';


echo '<font size=3 color=BLUE>' . _('PAGO');

if ($_SESSION['PaymentDetail']->SupplierID!=""){
	echo ' ' . _('A') . ' ' . $_SESSION['PaymentDetail']->SuppName;
}

if ($_SESSION['PaymentDetail']->BankAccountName!=""){
	echo ' ' . _('DESDE CUENTA') . ' ' . $_SESSION['PaymentDetail']->BankAccountName;
}

echo ' ' . _('EL') . ' ' . $_SESSION['PaymentDetail']->DatePaid . '</font>';

// Note this is duplicated
//echo '<div class="page_help_text">' . _('Note: To enter a payment FROM ') . $_SESSION['PaymentDetail']->SuppName . _(' use a negative Payment amount.');

echo '<p><table border=0>';

	  echo '<tr>';
	  echo '<td>' . _('Area') . ':</b></td>';
	  $SQL=" SELECT areas.areacode,areas.areadescription
			 FROM areas
			 INNER JOIN regions ON areas.regioncode = regions.regioncode
			 ORDER BY areadescription";	   
	  $resultarea = DB_query($SQL,$db);
	  echo "<td><select name='area' style='font-size:8pt'>";
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

	//Select the tag
	echo '<tr><td>' . _('Unidad de Negocio') . ':</td><td><select name="tag">';
	echo '<option selected value="0">Seleccionar una Unidad de Negocio';

	///Pinta las unidades de negocio por usuario	
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref  $wcond ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
	
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select></td><tr>';
	// End select tag


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

$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

echo '<tr><td>' . _('Cuenta de Cheques') . ':</td><td><select name="BankAccount">';

if (DB_num_rows($AccountsResults)==0){
	echo '</select></td></tr></table><p>';
	prnMsg( _('No existen cuentas de cheques definidas aun') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('configurar cuentas de cheques') . '</a> ' . _('y las cuentas contables que estas afectaran'),'warn');
	include('includes/footer.inc');
	exit;
} else {
	while ($myrow=DB_fetch_array($AccountsResults)){
	/*list the bank account names */
		if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
			$_POST['BankAccount']=$myrow['accountcode'];
		}
		if ($_POST['BankAccount']==$myrow['accountcode']){
			echo '<option selectED VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
		} else {
			echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
		}
	}
	echo '</select></td></tr>';
}
if (!isset($_SESSION['SuppTrans']->TranDate)){
	
	$_SESSION['PaymentDetail']->DatePaid=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
	
}

if (isset($_POST['DatePaid'])){
	$_SESSION['PaymentDetail']->DatePaid= $_POST['DatePaid'];
}

echo '<tr><td>' . _('Fecha de Pago') . ':</td>
	<td><input type="text" name="DatePaid" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength=10 size=11 onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . $_SESSION['PaymentDetail']->DatePaid . '"></td>
	</tr>';

if ($_SESSION['PaymentDetail']->SupplierID==''){
	echo '<tr><td>' . _('Moneda') . ':</td><td><select name="Currency">';
	$SQL = 'SELECT currency, currabrev, rate FROM currencies';
	$result=DB_query($SQL,$db);

	if (DB_num_rows($result)==0){
		echo '</select></td></tr>';
		prnMsg( _('No existen monedas definidas. Pagos no pueden ser procesados hasta que las monedas sean definidas'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
		if ($_SESSION['PaymentDetail']->Currency==$myrow['currabrev']){
			echo '<option selected value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		} else {
			echo '<option value=' . $myrow['currabrev'] . '>' . $myrow['currency'];
		}
		}
		echo '</select></td><td><i>' . _('La moneda de la transaccion no tiene que ser la misma que la moneda de la transaccion') . '</i></td></tr>';
	}
} else { /*its a supplier payment so it must be in the suppliers currency */
	echo '<tr><td>' . _('Moneda el Proveedor') . ':</td><td>' . $_SESSION['PaymentDetail']->Currency . '</td></tr>';
	echo '<input type="hidden" name="Currency" value="' . $_SESSION['PaymentDetail']->Currency . '">';
	/*get the default rate from the currency table if it has not been set */
	if (!isset($_POST['ExRate']) OR $_POST['ExRate']==''){
		$SQL = "SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency ."'";
		$Result=DB_query($SQL,$db);
		$myrow=DB_fetch_row($Result);
		$_POST['ExRate']=$myrow[0];
	}

}

if (!isset($_POST['ExRate'])){
	$_POST['ExRate']=1;
}

if (!isset($_POST['FunctionalExRate'])){
	$_POST['FunctionalExRate']=1;
}
if ($_SESSION['PaymentDetail']->AccountCurrency!=$_SESSION['PaymentDetail']->Currency AND isset($_SESSION['PaymentDetail']->AccountCurrency)){
	if (isset($SuggestedExRate)){
		$SuggestedExRateText = '<b>' . _('Tipo de Cambio Sugerido:') . ' ' . number_format($SuggestedExRate,4) . '</b>';
	} else {
		$SuggestedExRateText ='';
	}
	if ($_POST['ExRate']==1 AND isset($SuggestedExRate)){
		$_POST['ExRate'] = $SuggestedExRate;
	}
	echo '<tr><td>' . _('Tipo de Cambio del Pago') . ':</td>
				<td><input type="text" name="ExRate" maxlength=10 size=12 value="' . $_POST['ExRate'] . '"></td>
			<td>' . $SuggestedExRateText . ' <i>' . _('El tipo de cambio entre la moneda del banco y la moneda del pago') . '. 1 ' . $_SESSION['PaymentDetail']->AccountCurrency . ' = ? ' . $_SESSION['PaymentDetail']->Currency . '</i></td></tr>';
}

if ($_SESSION['PaymentDetail']->AccountCurrency!=$_SESSION['CompanyRecord']['currencydefault']
												AND isset($_SESSION['PaymentDetail']->AccountCurrency)){
	if (isset($SuggestedFunctionalExRate)){
		$SuggestedFunctionalExRateText = '<b>' . _('Tipo de Cambio Sugerido:') . ' ' . number_format($SuggestedFunctionalExRate,4) . '</b>';
	} else {
		$SuggestedFunctionalExRateText ='';
	}
	if ($_POST['FunctionalExRate']==1 AND isset($SuggestedFunctionalExRate)){
		$_POST['FunctionalExRate'] = $SuggestedFunctionalExRate;
	}
	echo '<tr><td>' . _('Tipo de Cambio Funcional') . ':</td><td><input type="text" name="FunctionalExRate" maxlength=10 size=12 value="' . $_POST['FunctionalExRate'] . '"></td>
			<td>' . ' ' . $SuggestedFunctionalExRateText . ' <i>' . _('El tipo de cambio entre la moneda del negocio y la moneda de la cuenta de cheques') .  '. 1 ' . $_SESSION['CompanyRecord']['currencydefault'] . ' = ? ' . $_SESSION['PaymentDetail']->AccountCurrency . '</i></td></tr>';
}
echo '<tr><td>' . _('Tipo de Pago') . ':</td><td><select name="Paymenttype">';

include('includes/GetPaymentMethods.php');
/* The array Payttypes is set up in includes/GetPaymentMethods.php
payment methods can be modified from the setup tab of the main menu under payment methods*/

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
		<td><input type="text" name="ChequeNum" maxlength=8 size=10 value="' . $_POST['ChequeNum'] . '"> ' . _('(si utiliza formas pre-impresas)') . '</td></tr>';

if (!isset($_POST['Narrative'])) {
	$_POST['Narrative']='';
}

if (!isset($_POST['Beneficiario'])) {
	$_POST['Beneficiario']=$_SESSION['PaymentDetail']->SuppName;
}

echo '<tr><td>' . _('Beneficiario') . ':</td>
			<td colspan=2><textarea name="Beneficiario" rows="1" cols="62" size=82>' . $_POST['Beneficiario'] . '</textarea></td></tr>';

echo '<tr><td>' . _('Referencia / Concepto') . ':</td>
			<td colspan=2><input type="text" name="Narrative" maxlength=200 size=82 value="' . $_POST['Narrative'] . '"></td></tr>';
			

echo '<tr><td colspan=3><hr></td></tr>';

echo '<tr><td colspan=3 align=center> <p align=center><b>Seleccione un Proveedor </b></p><br></td></tr>';


echo '<tr><td colspan=3><div class="center">';

	echo '<table class="table1" border="1"><tr>
			<th>' . _('Proveedor') . '</th>
			<th>' . _('Numero de Cheque').'</th>
			<th>' . _('Monto') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Monto Impuesto') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Descuento') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Ret Iva') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Ret ISR') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Ret Cedular') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Ret Fletes') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Ret Comisi') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Ret Arrendam') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>			
			
			<th>' . _('Beneficiario') . '</th>
			<th>' . _('Concepto') . '</th>
			<th>' . _('Unidad de Negocio') . '</th>
		</tr>';

	$PaymentTotal = 0;
	foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
		
		$tagsql='SELECT tagdescription from tags where tagref='.$PaymentItem->tag;
		$tagresult=DB_query($tagsql, $db);
		$tagmyrow=DB_fetch_row($tagresult);
		if ($PaymentItem->tag==0) {
			$tagname='None';
		} else {
			$tagname=$tagmyrow[0];
		}
		
		$tagsql='SELECT supplierid,suppname from suppliers where supplierid="'.$PaymentItem->SupplierID.'"';
		//echo $tagsql . "<br>";
		$tagresult=DB_query($tagsql, $db);
		$tagmyrow=DB_fetch_row($tagresult);
		if (strlen($PaymentItem->SupplierID) == 0) {
			$nombreproveedor=$PaymentItem->SupplierID;
		} else {
			$nombreproveedor=$tagmyrow[1];
		}
		
		//echo $PaymentItem->AmountTax . ' ' .  $PaymentItem->taxrate . '<br>'; 
		if ($PaymentItem->AmountTax != 0)
			$rateresult = $PaymentItem->AmountTax;		
		else
			$rateresult = $PaymentItem->taxrate;		
		
		
		
		echo '<tr>
			<td align=left>' . $nombreproveedor . '&nbsp;</td>
			<td align=left>' . $PaymentItem->cheque . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->Amount,2) . '&nbsp;</td>
			<td align=right>' . number_format($rateresult,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->Discountsupp,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->RetencionIVA,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->RetencionISR,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->RetencionxCedular,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->RetencionxFletes,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->RetencionxComisiones,2) . '&nbsp;</td>
			<td align=right>' . number_format($PaymentItem->RetencionxArrendamiento,2) . '&nbsp;</td>			
			
			<td>' . $PaymentItem->GLCode . '&nbsp;</td>
			<td>' . $PaymentItem->Narrative  . '&nbsp;</td>
			<td>' . $PaymentItem->tag . ' - ' . $tagname . '&nbsp;</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $PaymentItem->ID . '">' . _('Eliminar') . '</a></td>
		</tr>';
			
		$PaymentTotal += $PaymentItem->Amount;
	}
	echo '<tr><td colspan=2></td><td align=right><b>' . number_format($PaymentTotal,2) . '</b></td><td></td><td></td><td></td></tr></table>';


echo '</div><br></td></tr>';



echo '<tr><td colspan=3><div class="center">';

	echo '<table border="0">';
	if(!isset($_POST['nomprov']) or $_POST['nomprov'] == ""){
		$_POST['nomprov'] = "*";
	}//
	echo '<tr><td>' . _('Proveedor') . ':</td>';
	echo "<td><input type=text name='nomprov' value='".$_POST['nomprov']."'>";
	echo "<input type='submit' name='btnnomprov' value='Buscar'></td>";
	echo "</tr><tr>";
	echo '<td></td><td><select name="supplierid">';
	echo '<option selected value="0">Seleccione un Proveedor';
	
	///Pinta las unidades de negocio por usuario	
	$SQL = "SELECT supplierid,suppname 
			FROM suppliers 
			WHERE suppname <> ''";
	if ($_POST['nomprov'] <> "*"){
		$SQL = $SQL." AND suppname like '%".$_POST['nomprov']."%'";
	}
	$SQL = $SQL. " ORDER BY suppname asc";
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['supplierid']) and $_POST['supplierid']==$myrow['supplierid']){
			echo '<option selected value="' . $myrow['supplierid'] . '">' .$myrow['suppname'];
		} else {
			echo '<option value="' . $myrow['supplierid'] . '">' .$myrow['suppname'];
		}
	}
	echo '</select></td><tr>';
	// End select tag
	
	
	echo '<tr><td>' . _('Categoria Impuestos') . ':</td><td colspan=2><select name="TaxCat">';
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
	} //end while loop
	
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _('Monto Impuesto') . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
					<td><input type="text" name="AmountTax" maxlength=12 size=13 value=0></td></tr>';
	
	
	echo '<tr><td>' . _('Monto del Pago') . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
					<td><input type="text" name="Amount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Amount . '></td></tr>';
	
	echo '<tr><td>' . _('Monto de Descuento') . ':</td>
		  <td colspan=2><input type="text" name="Discount" maxlength=12 size=13 value=0></td></tr>';

	echo '<tr><td>' . _('Retencion IVA ')  . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
		  <td><input type="text" name="RetencionIVA" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion ISR ')  . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
		  <td><input type="text" name="RetencionISR" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x Arrendamiento ')  . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
		  <td><input type="text" name="RetencionxArrendamiento" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x Comisiones ')  . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
		  <td><input type="text" name="RetencionxComisiones" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x Fletes ')  . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
		  <td><input type="text" name="RetencionxFletes" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x Cedular ')  . ' ' . $_SESSION['PaymentDetail']->Currency . ':</td>
		  <td><input type="text" name="RetencionxCedular" maxlength=12 size=13 value="0"></td></tr>';

	echo '</table>';

echo '</div></td></tr>';


		
echo '<tr><td colspan=3 ><p align="center"><input type="submit" name="AddSupp" value="' . _('Agregar Proveedor'). '"></p></td></tr>';

echo '</table><br>';

echo '<input type=submit name="CommitBatch" value="' . _('Aceptar y Procesar el Pago') . '">';
echo '</form>';

include('includes/footer.inc');
?>
