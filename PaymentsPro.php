<?php
//echo date("Y-m-d");
/* $Revision: 1.38 $ */
/*Cambios:
1.- Se le agrego el  include('includes/SecurityFunctions.inc');*/
$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');
$funcion=600;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include ('includes/XSAInvoicing.inc');

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

//note this is already linked from this page
echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Ir a pagina de Proveedores') . '</a><br>';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Captura de Pagos') . '" alt="">' . ' ' . _('Pagos de Cuentas de Cheques') . '</p>';
echo '<div class="centre">';

/**********************************************/
/*VALIDA SI EL PAGO VA A SER PARA UN PROVEEDOR*/
if (isset($_GET['SupplierID'])){
	/*The page was called with a supplierID check it is valid and
	default the inputs for Supplier Name and currency of payment */

	/*INICIALIZA LA CLASE DE LINEAS CONTABLES*/
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
	
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;


	/*OBTINENE DATOS DEL PROVEEDOR*/
	$SQL= "SELECT suppname,
			address1,
			address2,
			address3,
			address4,
			address5,
			address6,
			currcode,
			factorcompanyid
		FROM suppliers
		WHERE supplierid='" . $_GET['SupplierID'] . "'";

	$Result = DB_query($SQL, $db);
	if (DB_num_rows($Result)==0){
		prnMsg( _('El codigo de Porveedor con el que esta pagina fue llamada, no existe en base de datos de Proveedores') . '. ' . _('Si esta pagina es llamada desde la pagina de Proveedores, esto garantiza que el proveedor existe!'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		/*CODIGO DE PROVEEDOR VALIDO*/
		$myrow = DB_fetch_array($Result);
		if ($myrow['factorcompanyid'] == 1) {
			$_SESSION['PaymentDetail']->SuppName = $myrow['suppname'];
			$_SESSION['PaymentDetail']->Address1 = $myrow['address1'];
			$_SESSION['PaymentDetail']->Address2 = $myrow['address2'];
			$_SESSION['PaymentDetail']->Address3 = $myrow['address3'];
			$_SESSION['PaymentDetail']->Address4 = $myrow['address4'];
			$_SESSION['PaymentDetail']->Address5 = $myrow['address5'];
			$_SESSION['PaymentDetail']->Address6 = $myrow['address6'];
			$_SESSION['PaymentDetail']->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail']->Currency = $myrow['currcode'];
		} else {
			$factorsql= "SELECT coyname,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6
					FROM factorcompanies
					WHERE id='" . $myrow['factorcompanyid'] . "'";

			$FactorResult = DB_query($factorsql, $db);
			$myfactorrow = DB_fetch_array($FactorResult);
			$_SESSION['PaymentDetail']->SuppName = $myrow['suppname'] . _(' care of ') . $myfactorrow['coyname'];
			$_SESSION['PaymentDetail']->Address1 = $myfactorrow['address1'];
			$_SESSION['PaymentDetail']->Address2 = $myfactorrow['address2'];
			$_SESSION['PaymentDetail']->Address3 = $myfactorrow['address3'];
			$_SESSION['PaymentDetail']->Address4 = $myfactorrow['address4'];
			$_SESSION['PaymentDetail']->Address5 = $myfactorrow['address5'];
			$_SESSION['PaymentDetail']->Address6 = $myfactorrow['address6'];
			$_SESSION['PaymentDetail']->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail']->Currency = $myrow['currcode'];
			$_POST['Currency'] = $_SESSION['PaymentDetail']->Currency;
		}
	}
} //FIN DE IF SI PROVEEDOR SELECCIONADO


/* SI CUENTA DE CHEQUES FUE SELECCIONADA ASIGNA VALORES A CLASE */
if (isset($_POST['BankAccount']) and $_POST['BankAccount']!=''){
	$_SESSION['PaymentDetail']->Account=$_POST['BankAccount'];
	
	/*Get the bank account currency and set that too */
	$ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
	$result = DB_query('SELECT currcode FROM bankaccounts WHERE accountcode =' . $_POST['BankAccount'],$db,$ErrMsg);
	$myrow = DB_fetch_row($result);
	$_SESSION['PaymentDetail']->AccountCurrency=$myrow[0];
} else {
	if ($_SESSION['PaymentDetail']->AccountCurrency == "")
		$_SESSION['PaymentDetail']->AccountCurrency =$_SESSION['CompanyRecord']['currencydefault'];
}



/* CALCULA LOS TIPOS DE CAMBIO SI MONEDA NO MXN  PARA LA CUENTA*/
if (isset($_POST['BankAccount']) and $_POST['BankAccount']!=''){
	
	/*Get the exchange rate between the functional currency and the payment currency*/
	$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->AccountCurrency . "'",$db);
	$myrow = DB_fetch_row($result);
	$tableExRateCount = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
	$tableFunctionalExRateCount = 1;
	$TipoCurrencyCount = $_SESSION['PaymentDetail']->AccountCurrency;
		
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
		$_SESSION['PaymentDetail']->FunctionalExRate = 1;
		
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
		
		$_SESSION['PaymentDetail']->ExRate = $SuggestedFunctionalExRate;
		$_SESSION['PaymentDetail']->FunctionalExRate=1;
	}
} //FIN DE VERIFICACION DEL TIPO DE CAMBIO

if (isset($_POST['Narrative']) and $_POST['Narrative']!=''){
	$_SESSION['PaymentDetail']->Narrative=$_POST['Narrative'];
}

if (isset($_POST['Amount']) and $_POST['Amount']!=""){
	$_SESSION['PaymentDetail']->Amount=$_POST['Amount'];
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

	if ($TotalAmount==0 AND
		($_SESSION['PaymentDetail']->Discount + $_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate ==0){
		prnMsg( _('Este pago no tiene cantidades capturadas y no sera procesado'),'warn');
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
	
	if ($_SESSION['FutureDate']==1){
		if (Date1GreaterThanDate2($_SESSION['PaymentDetail']->DatePaid,date("d/m/Y"))==1 and Havepermission($_SESSION['UserID'],410, $db)==0){
			prnMsg(_('La fecha es posterior y no cuenta con los permisos para realizar esta operacion'),'error');
				echo '<br>';
				//prnMsg (_('<a href="#" onclick="history.go(-1); return false;">Regresar</a>'),'error');
				include('includes/footer.inc');
				exit;
			
		}
	}
	//descomponer fecha
	$PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid,$db,$_POST['tag']);
	
	
	
	//echo 'PERIODO AFECTADO: ->'.$PeriodNo. ' - '. $fechax . ' SI ESTE PERIODO ESTA MAL FAVOR DE IMPRIMIR ESTA PANTALLA Y REPORTARLO A GONZALO...';
	

	if (isset($_POST['tag'])){
		
		if ($_POST['tag'] == "0"){
			
			if (!isset($_POST['pro']) or (isset($_POST['pro']) and $_POST['pro'] == 0)) {
				prnMsg(_('Debe de Seleccionar una Unidad de Negocio'),'error');
				echo '<br>';
				prnMsg (_('<a href="#" onclick="history.go(-1); return false;">Regresar</a>'),'error');
				exit;
			}
		} else {
			//VERIFICA QUE UNIDAD DE NEGOCIO SEA VALIDA PARA LA CHEQUERA
			/*
			$SQL = 'SELECT 	tagref
				FROM 	tagsxbankaccounts
				WHERE 	tagsxbankaccounts.accountcode="'.$PaymentItem->GLCode.'" and
					tagsxbankaccounts.tagref='.$_POST['tag'];
			*/
			$SQL = 'SELECT 	tagref
				FROM 	tagsxbankaccounts
				WHERE 	tagsxbankaccounts.accountcode="'.$_POST['BankAccount'].'" and
					tagsxbankaccounts.tagref='.$_POST['tag'];
			$result = DB_query($SQL,$db);
			
			if (!$Act = DB_fetch_row($result)){
				prnMsg(_('La Unidad de Negocio seleccionada no es valida para la chequera seleccionada...'),'error');
				echo '<br>';
				prnMsg (_('<a href="#" onclick="history.go(-1); return false;">Regresar</a>'),'error');
				exit;
			}
		}
	}		
	
	if ((!isset($_POST['tableExRateCount'])) or ($_POST['tableExRateCount']=="")){
		$_POST['tableExRateCount'] = $tableExRateCount;
	}
	if ((!isset($_POST['tableFunctionalExRateCount'])) or $_POST['tableFunctionalExRateCount'] == ""){
		$_POST['tableFunctionalExRateCount'] = $tableFunctionalExRateCount;
	}

	//Start a transaction to do the whole lot inside
	$SQL = 'BEGIN';
	$result = DB_query($SQL,$db);


	// PAGO CONTABLE ....
	if ($_SESSION['PaymentDetail']->SupplierID=='') {

		//its a nominal bank transaction type 1

		$TransNo = GetNextTransNo( 1, $db);
		$Transtype = 1;
		$Beneficiario = $_POST['Beneficiario'];
		//echo $Beneficiario;
		if (!isset($legaid) or $legaid=='') {
		$sql ="Select legalid,areacode from tags where tagref=".$_POST['tag'];
		$result = DB_query($sql, $db);
		while ($myrow=DB_fetch_array($result,$db)) {
			$legaid=$myrow['legalid'];
			$area=$myrow['areacode'];
		}
		}
		//******------******//
		$InvoiceNoTAG = DocumentNext(1, $_POST['tag'],$area,$legaid, $db);
		//******------******//
		if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then enter GLTrans */
			
			$TotalAmount=0;
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {

				 /*The functional currency amount will be the
				 payment currenct amount  / the bank account currency exchange rate  - to get to the bank account currency
				 then / the functional currency exchange rate to get to the functional currency */
				if ($PaymentItem->cheque=='') $PaymentItem->cheque=0;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						$SQL = 'INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									chequeno,
									tag) ';
						$SQL= $SQL . "VALUES (1,
							" . $TransNo . ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $PaymentItem->GLCode . ",
							'" . $PaymentItem->Narrative . "',
							" . ($myrowi['porcentaje']*$PaymentItem->Amount/$_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) . ",
							'". $PaymentItem->cheque ."',
							'" . $myrowi['tagref'] . "'
							)";
						$ErrMsg = _('No pude insertar el movimiento contable usando este SQL');
						$result = DB_query($SQL,$db,$ErrMsg,_('El SQL que fallo fue'),true);
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
					$SQL = 'INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount,
								chequeno,
								tag) ';
					$SQL= $SQL . "VALUES (1,
						" . $TransNo . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $PaymentItem->GLCode . ",
						'" . $PaymentItem->Narrative . "',
						" . ($PaymentItem->Amount/$_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate) . ",
						'". $PaymentItem->cheque ."',
						'" . $PaymentItem->tag . "'
						)";
					$ErrMsg = _('No pude insertar el movimiento contable usando este SQL');
					$result = DB_query($SQL,$db,$ErrMsg,_('El SQL que fallo fue'),true);
				}
				//FIN PRORRATEO
				
				$TotalAmount += $PaymentItem->Amount;
			}
			$_SESSION['PaymentDetail']->Amount = $TotalAmount;
			$_SESSION['PaymentDetail']->Discount=0;
		}

		/*Run through the GL postings to check to see if there is a
		posting to another bank account (or the same one) if there
		is then a receipt needs to be created for this account too*/
		
		/* RECORRE TODAS LAS CUENTAS A PAGAR PARA VERIFICAR QUE NO ES UN PAGO ENTRE CUENTAS
		   O A LA MISMA CUENTA.  SI ES ASI, UN RECIBO SE NECESITA CREAR PARA ESTA CUENTA */

		foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
			
			

			if (in_array($PaymentItem->GLCode, $BankAccounts)) {
				
				/* SI LA CUENTA A PAGAR SI ES CUENTA DE BANCO */
				
				/* Need to deal with the case where the payment from one bank
				account could be to a bank account in another currency */

				/* Get the currency and rate of the bank account transferring to */
				$SQL = 'SELECT currcode, rate
						FROM bankaccounts INNER JOIN currencies
						ON bankaccounts.currcode = currencies.currabrev
						WHERE accountcode=' . $PaymentItem->GLCode;
						
				$TrfToAccountResult = DB_query($SQL,$db);
				$TrfToBankRow = DB_fetch_array($TrfToAccountResult) ;
				$TrfToBankCurrCode = $TrfToBankRow['currcode'];
				$TrfToBankExRate = $TrfToBankRow['rate'];

				if ($_SESSION['PaymentDetail']->AccountCurrency == $TrfToBankCurrCode){
					/*Make sure to use the same rate if the transfer is
					between two bank accounts in the same currency */
					$TrfToBankExRate = $_SESSION['PaymentDetail']->FunctionalExRate;
				}

				/*Consider an example
				 functional currency NZD
				 bank account in AUD - 1 NZD = 0.90 AUD (FunctionalExRate)
				 paying USD - 1 AUD = 0.85 USD  (ExRate)
				 to a bank account in EUR - 1 NZD = 0.52 EUR

				 oh yeah - now we are getting tricky!
				 Lets say we pay USD 100 from the AUD bank account to the EUR bank account

				 To get the ExRate for the bank account we are transferring money to
				 we need to use the cross rate between the NZD-AUD/NZD-EUR
				 and apply this to the

				 the payment record will read
				 exrate = 0.85 (1 AUD = USD 0.85)
				 amount = 100 (USD)
				 functionalexrate = 0.90 (1 NZD = AUD 0.90)

				 the receipt record will read

				 amount 100 (USD)
				 exrate    (1 EUR =  (0.85 x 0.90)/0.52 USD)
									(ExRate x FunctionalExRate) / USD Functional ExRate
				 functionalexrate =     (1NZD = EUR 0.52)

				*/

				$ReceiptTransNo = GetNextTransNo( 2, $db);					
				$SQL= 'INSERT INTO banktrans (transno,
							type,
							bankact,
							ref,
							exrate,
							functionalexrate,
							transdate,
							banktranstype,
							amount,
							currcode,
							beneficiary)
					VALUES (' . $ReceiptTransNo . ',
						2,
						' . $PaymentItem->GLCode . ", '"
						. _('Transferencia Desde ') . $_SESSION['PaymentDetail']->Account . ' - ' . $PaymentItem->Narrative . " ',
						" . (($_SESSION['PaymentDetail']->ExRate * $_SESSION['PaymentDetail']->FunctionalExRate)/$TrfToBankExRate). ",
						" . $TrfToBankExRate . ",
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'" . $_SESSION['PaymentDetail']->Paymenttype . "',
						" . $PaymentItem->Amount . ",
						'" . $_SESSION['PaymentDetail']->Currency . "',
						'" . $Beneficiario . "'
					)";
				//echo $SQL;	
				$ErrMsg = _('No pude insertar una transaccion en banco porque');
				$DbgMsg =  _('No pude insertar una transaccion en banco con el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			} //FIN DE IF SI LA CUENTA SI ES DE CHEQUES
		} // FIN DEL FOR DE LAS APLICACIONES A CUENTAS CONTABLES
	// FIN DE PAGOS CONTABLES
	} else {
		
	/*********************************************************************/
	// INICIO DE PAGO A PROVEEDOR
	/*Its a supplier payment type 22 */
	
		//echo "ivas " . $_POST['RetencionIVA'] . " " . $_POST['RetencionISR'];
		$RetencionIVA = $_POST['RetencionIVA'];
		$RetencionISR = $_POST['RetencionISR'];
		$Beneficiario = $_POST['Beneficiario'];
		$RetencionxCedular = $_POST['RetencionxCedular'];
		$RetencionxFletes = $_POST['RetencionxFletes'];
		$RetencionxComisiones = $_POST['RetencionxComisiones'];
		$RetencionxArrendamiento = $_POST['RetencionxArrendamiento'];
		$RetencionxIVAArrendamiento = $_POST['RetencionxIVAArrendamiento'];
		$mntIVA = $_POST['mntIVA'];
		$ChequeNum = $_POST['ChequeNum'];
	
		
		//$CreditorTotal = (($_SESSION['PaymentDetail']->Discount + $_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;
					
		$TransNo = GetNextTransNo(22, $db);
		$Transtype = 22;
		
		if (!isset($legaid) or $legaid=='') {
		$sql ="Select legalid,areacode from tags where tagref=".$_POST['tag'];
		$result = DB_query($sql, $db);
		while ($myrow=DB_fetch_array($result,$db)) {
			$legaid=$myrow['legalid'];
			$area=$myrow['areacode'];
		}
		}
		//******------******//
		$InvoiceNoTAG = DocumentNext(22, $_POST['tag'],$area,$legaid, $db);
		//******------******//
		
		/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/		
		if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN"){
			$MontoProveedorSupp = ((($_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
			$tipocambioaldia = $_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate;
		}
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN"){
			$MontoProveedorSupp = $_SESSION['PaymentDetail']->Amount;
			$tipocambioaldia = $_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate;
		}
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
			$MontoProveedorSupp = ($_SESSION['PaymentDetail']->Amount)/($_SESSION['PaymentDetail']->FunctionalExRate/$_SESSION['PaymentDetail']->ExRate);
			$tipocambioaldia = $_SESSION['PaymentDetail']->FunctionalExRate/$_SESSION['PaymentDetail']->ExRate;
		}			
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
			$MontoProveedorSupp = $_SESSION['PaymentDetail']->Amount;
			$tipocambioaldia = $_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate;
		}
		else{
			$MontoProveedorSupp = $_SESSION['PaymentDetail']->Amount;
			$tipocambioaldia = $_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate;
		}
		
		//echo $MontoProveedorSupp;
		//exit;

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
				'" . $_SESSION['PaymentDetail']->SupplierID . "',
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',
				" . ($tipocambioaldia) . ",
				" . -$MontoProveedorSupp/*(-$_SESSION['PaymentDetail']->Amount+$_SESSION['PaymentDetail']->Discount)*/ . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . $_POST['tag'] . ",
				now(),
				'" . $ChequeNum . "'
			)";
			
		$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
		$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		/* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
		/*Update the supplier master with the date and amount of the last payment made */
		$SQL = "UPDATE suppliers SET
			lastpaiddate = '" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
			lastpaid=" . $_SESSION['PaymentDetail']->Amount ."
			WHERE suppliers.supplierid='" . $_SESSION['PaymentDetail']->SupplierID . "'";

		$ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
		$DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$_SESSION['PaymentDetail']->Narrative = $_SESSION['PaymentDetail']->SupplierID . "-" . $_SESSION['PaymentDetail']->Narrative;
		
		/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
		if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
			$CreditorTotal = (($_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
			$CreditorTotal = $_SESSION['PaymentDetail']->Amount;
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
			$CreditorTotal = $_SESSION['PaymentDetail']->Amount;
		}
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
			$CreditorTotal = (($_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;
		}
		else
			$CreditorTotal = $_SESSION['PaymentDetail']->Amount;
		
		/* SI ESTA HABILITADA LA INTEGRACION CONTABLE CON CUENTAS POR PAGAR */
		if ($_SESSION['CompanyRecord']['gllink_creditors']==1){
			/* then do the supplier control GLTrans */
			/* Now debit creditors account with payment + discount */

			//SI SE SELECCIONO PRORRATEO
			if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
				$SQL = "SELECT pd.porcentaje as porcentaje,
					       pd.tagref as tagref
					       FROM prorrxuneg as pd
					       WHERE pd.prorrateoid=".$_POST['pro'];
				$result=DB_query($SQL,$db);
				
				while ($myrowi=DB_fetch_array($result)) {
					//REALIZA LA APLICACION CONTABLE CON PRORRATEO
					//$myrowi['porcentaje']
					//$myrowi['tagref']
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
							" . $_SESSION['CompanyRecord']['creditorsact'] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . $CreditorTotal*$myrowi['porcentaje'] . ",
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
					$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
					$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			} else {
				//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . $_SESSION['CompanyRecord']['creditorsact'] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . $CreditorTotal . ",
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
				$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
				$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			}
			//FIN PRORRATEO
		
			
			/**************************************************/
			/* MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
			
			//$SQL = 'select * from taxauthorities where taxid=1'.$_SESSION['DefaultTaxCategory'];
			$SQL = 'select * from taxauthorities where taxid=1';
			$result2 = DB_query($SQL,$db);
			if ($TaxAccs = DB_fetch_array($result2)){
			
				if ($mntIVA == 0 || $mntIVA == ""){
					$taximpuesto=($CreditorTotal / (1 +$taxrate));
					$taximpuesto=$CreditorTotal-$taximpuesto;
				}	
				else
					$taximpuesto=$mntIVA;
					
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
						
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
								" . ($myrowi['porcentaje']*$taximpuesto*-1) . ",
								" . $myrowi['tagref'] . ",
								'" . $ChequeNum . "'
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
								22,
								" . $TransNo . ",
								'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
								" . $PeriodNo . ",
								" . $TaxAccs['purchtaxglaccountPaid'] . ",
								'" . $_SESSION['PaymentDetail']->Narrative . "',
								" . $myrowi['porcentaje']*$taximpuesto . ",
								" . $myrowi['tagref'] . ",
								'" . $ChequeNum . "'
							)";
						$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
						$DbgMsg = _('El SQL utilizado fue');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
					
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
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
				//FIN PRORRATEO
				
				
									
			} //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS
			
			
			/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
			if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
				$MDiscount = (($_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
			elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
				$MDiscount = $_SESSION['PaymentDetail']->Discount;
			elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
				$MDiscount = $_SESSION['PaymentDetail']->Discount;
			}	
			elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
				$MDiscount = (($_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
			}
			else
				$MDiscount = $_SESSION['PaymentDetail']->Discount;
			
			if ($_SESSION['PaymentDetail']->Discount !=0){
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*($MDiscount)  /*(($_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate)*/ . ",
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . $_SESSION['CompanyRecord']["pytdiscountact"] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-1)*($MDiscount)  /*(($_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate)*/ . ",
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
				
				
			} // end if discount


			if ($RetencionISR !=0){



				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionISR = (($RetencionISR/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionISR = $RetencionISR;
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionISR = $RetencionISR;						
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionISR = (($RetencionISR/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}					
				else
					$MRetencionISR = $RetencionISR;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*($MRetencionISR) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento isr porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento isr utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . (-1)*($MRetencionISR) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento isr porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento isr utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
				
			} // end if discount
			
			
			
			if ($RetencionIVA !=0){


				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionIVA = (($RetencionIVA/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionIVA = (($RetencionIVA/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionIVA = $RetencionIVA;
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionIVA = (($RetencionIVA/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}										
				else					
					$MRetencionIVA = $RetencionIVA;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*($MRetencionIVA) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para la descuento retencion iva porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento retencion iva utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . (-1)*($MRetencionIVA) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para la descuento retencion iva porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento retencion iva utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
				
			} // end if discount				


			//echo "Cedular " . $RetencionxCedular . "<br>";

			if ($RetencionxCedular !=0){



				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxCedular = (($RetencionxCedular/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxCedular = $RetencionxCedular;
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxCedular = $RetencionxCedular;
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxCedular = (($RetencionxCedular/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}										
				else
					$MRetencionxCedular = $RetencionxCedular;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*($MRetencionxCedular) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento cedular porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento cedular utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . (-1)*($MRetencionxCedular) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento cedular porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento cedular utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
				
			} // end if discount				


			if ($RetencionxFletes !=0){
				
				

				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxFletes = (($RetencionxFletes/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxFletes = $RetencionxFletes;
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxFletes = $RetencionxFletes;
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxFletes = (($RetencionxFletes/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}										
				
				else
					$MRetencionxFletes = $RetencionxFletes;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*(($MRetencionxFletes/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento fletes porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento fletes utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . (-1)*(($MRetencionxFletes/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento fletes porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento fletes utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
				
			} // end if discount				


			if ($RetencionxComisiones !=0){


				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxComisiones = (($RetencionxComisiones/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxComisiones = $RetencionxComisiones;
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxComisiones = $RetencionxComisiones;											
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxComisiones = (($RetencionxComisiones/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}										
									
				else
					$MRetencionxComisiones = $RetencionxComisiones;
				
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*($MRetencionxComisiones) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento comisiones porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento comisiones utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
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
						" . (-1)*($MRetencionxComisiones) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento comisiones porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento comisiones utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
				
			} // end if discount				


			if ($RetencionxArrendamiento !=0){



				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxComisiones = (($RetencionxArrendamiento/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxComisiones = $RetencionxArrendamiento;
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxComisiones = $RetencionxArrendamiento;											
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxComisiones = (($RetencionxArrendamiento/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}										
				
				else
					$MRetencionxComisiones = $RetencionxArrendamiento;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . (-1)*$myrowi['porcentaje']*($MRetencionxComisiones) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
					
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
						" . (-1)*($MRetencionxComisiones) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
			} // end if discount
			
			if ($RetencionxIVAArrendamiento !=0){



				/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
				if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxComisiones = (($RetencionxIVAArrendamiento/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
					$MRetencionxComisiones = $RetencionxIVAArrendamiento;
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxComisiones = $RetencionxIVAArrendamiento;											
				}
				elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
					$MRetencionxComisiones = (($RetencionxIVAArrendamiento/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate);
				}										
				
				else
					$MRetencionxComisiones = $RetencionxIVAArrendamiento;
				
				//SI SE SELECCIONO PRORRATEO
				if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
					$SQL = "SELECT pd.porcentaje as porcentaje,
						       pd.tagref as tagref
						       FROM prorrxuneg as pd
						       WHERE pd.prorrateoid=".$_POST['pro'];
					$result=DB_query($SQL,$db);
					
					while ($myrowi=DB_fetch_array($result)) {
						//REALIZA LA APLICACION CONTABLE CON PRORRATEO
						//$myrowi['porcentaje']
						//$myrowi['tagref']
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
							" . $_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"] . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . (-1)*$myrowi['porcentaje']*($MRetencionxComisiones) . ",						
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
						$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
						$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
						$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						
						
					}
				} else {
					//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
					
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
						" . $_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"] . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-1)*($MRetencionxComisiones) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
				}
				//FIN PRORRATEO
				
			} // end if discount
			
			
		} // end if gl creditors
	} // FIN DE IF DE PAGO A PROVEEDOR


	
	
	/* AHORA REALIZA LAS TRANSACCIONES COMUNES DE CONTABILIDAD*/
	if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then do the common GLTrans */

		$SumaRetenciones = $RetencionIVA + $RetencionISR + $RetencionxCedular + $RetencionxFletes + $RetencionxComisiones + $RetencionxArrendamiento + $RetencionxIVAArrendamiento;		
		/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
		if ($_SESSION['PaymentDetail']->AccountCurrency != "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
		{
			$v1 = (($_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;	
			$MCuadra = $v1;
		}
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "MXN")
		{
			$MCuadra = $_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones;
		}
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "MXN" && $_SESSION['PaymentDetail']->Currency == "USD"){
			$MCuadra = $_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones;				
		}
		
		elseif ($_SESSION['PaymentDetail']->AccountCurrency == "USD" && $_SESSION['PaymentDetail']->Currency == "USD"){
			$v1 = (($_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate;	
			$MCuadra = $v1;
		}													
		else
			$MCuadra = $_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones;
		
		if ($_SESSION['PaymentDetail']->Amount !=0){
			
			//SI SE SELECCIONO PRORRATEO
			if ( isset($_POST['pro']) and $_POST['pro']>0 ) {
				$SQL = "SELECT pd.porcentaje as porcentaje,
					       pd.tagref as tagref
					       FROM prorrxuneg as pd
					       WHERE pd.prorrateoid=".$_POST['pro'];
				$result=DB_query($SQL,$db);
				
				while ($myrowi=DB_fetch_array($result)) {
					//REALIZA LA APLICACION CONTABLE CON PRORRATEO
					//$myrowi['porcentaje']
					//$myrowi['tagref']
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
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							" . $PeriodNo . ",
							" . $_SESSION['PaymentDetail']->Account . ",
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							" . (-1)*$myrowi['porcentaje']* ($MCuadra) /*((($_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate)*/ . ",
							" . $myrowi['tagref'] . ",
							'" . $ChequeNum . "'
						)";
						
		
					$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
					$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					
					
				}
			} else {
				//REALIZA LA APLICACION CONTABLE SIN PRORRATEO
				
				/* Bank account entry first */
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
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $_SESSION['PaymentDetail']->Account . ",
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-1)* ($MCuadra) /*((($_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate)*/ . ",
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					
	
				$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
				$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
				$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
			}
			//FIN PRORRATEO
			
			

		} // FIN IF SI MONTO DIFERENTE DE CERO
	} // FIN IF SI ESTA INTEGRADO PROVEEDORES CON CONTABILIDAD


	$conversion = $_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones;
	$tipomoneda = $_SESSION['PaymentDetail']->AccountCurrency;


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
		
		$SQL= $SQL . "VALUES (" . $TransNo . ",
			" . $Transtype . ",
			" . $_SESSION['PaymentDetail']->Account . ",
			'" . $_SESSION['PaymentDetail']->Narrative . "',
			" . /*$_SESSION['PaymentDetail']->ExRate*/ $_POST['tableExRateCount'] . " ,
			" . /*$_SESSION['PaymentDetail']->FunctionalExRate*/ $_POST['tableFunctionalExRateCount'] . ",
			'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
			'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
							
		//$SQL .=	 (-1)*((($_SESSION['PaymentDetail']->Amount - $_SESSION['PaymentDetail']->Discount - $SumaRetenciones)/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate) . ",
		$SQL .=	 (-1)*$conversion . ",
		
			'" . $tipomoneda . "',
			" . $_POST['tag'] . ",
			'" . $Beneficiario . "',
			'" . $ChequeNum . "'
		)";

		$ErrMsg = _('No pude insertar la transaccion bancaria porque');
		$DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);	
	} else {
		foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
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
				
			$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				" . $_SESSION['PaymentDetail']->Account . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . $_SESSION['PaymentDetail']->ExRate . " ,
				" . $_SESSION['PaymentDetail']->FunctionalExRate . ",
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
			$SQL .=	  -$PaymentItem->Amount  . ",
				'" . $_SESSION['PaymentDetail']->Currency . "',
				" . $PaymentItem->tag . ",
				'" . $Beneficiario . "',
				'" . $PaymentItem->cheque . "'
			)";
			
			//echo "dos -> " .  -$PaymentItem->Amount . "<br>";

			$ErrMsg = _('No pude insertar la transaccion bancaria porque');
			$DbgMsg = _('No pude insertar la transaccion bancaria utilizando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$lasttag=$PaymentItem->tag;
		}
	}

	$SQL = "COMMIT";
	$ErrMsg = _('No pude hacer COMMIT de los cambios porque');
	$DbgMsg = _('El COMMIT a la base de datos fallo');
	$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('ha sido exitosamente procesado'),'success');
	
	$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
	$lasttag=$_POST['tag'];
	
	$liga = GetUrlToPrint($_POST['tag'],$Transtype,$db);
	if ($liga=="")
		$liga="PrintJournal.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=$Transtype";
		
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
	
	$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
	. '" alt="">' . ' ' .
	'<a  target="_blank" href="' . $rootpath . '/'. $liga . '&Tagref='.$lasttag.'&TransNo='.$TransNo.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
			
	//echo '<br><a href="' . $rootpath . '/PrintCheque.php?' . SID . '&ChequeNum=' . $ChequeNum .'&TransNo='.$TransNo.'&Currency='.$Currency.'&SuppName='.$SuppName.'" target="_blank">' . _('Imprimir Cheque usando formato pre-impreso') . '</a><br>';
	echo '<br>'.$liga.'<br>';
	
	echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Capturar pago contable') . '</a><br>';
	echo '<br><a href="' . $rootpath . '/Payments.php?SupplierID=' . $lastSupplier . '" >' . _('Capturar otro pago a este proveedor') . '</a>';


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

echo '<p><table>';

	//Select the prorrateo
	if (Havepermission($_SESSION['UserID'],600, $db)>0) {
		echo '<tr>
			<td>' . _('Prorrateo') . ':</td><td><select name="pro">';
			//consulta que mostrara las unidades de negosio 
			$SQL = "SELECT p.prorrateoid as idp,p.descripcion as descripcion";
			$SQL = $SQL .	" FROM prorrateos as p";		
			//$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
			$result=DB_query($SQL,$db);
			echo '<option selected value=0>Seleccionar Prorrateo';
			//mientras
			while ($myrow=DB_fetch_array($result)){
				//si
				if (isset($_POST['pro']) and $_POST['pro']==$myrow['idp']){
					echo '<option selected value=' . $myrow['idp'] . '>' . $myrow['descripcion'];
				} else {
					echo '<option value=' . $myrow['idp'] . '>' . $myrow['descripcion'];
				}
			}
		echo '</select></td></tr>';
		//End select prorrateo	
	}
	//Select the tag
	
	echo '<tr><td>' . _('Unidad de Negocio') . ':</td><td><select name="tag">';
	echo '<option selected value="0">Seleccionar una Unidad de Negocio';

	///Pinta las unidades de negocio por usuario	
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
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
			<td colspan=2><input type="text" name="Narrative" maxlength=80 size=82 value="' . $_POST['Narrative'] . '">  ' . _('(Max. 80 caracteres de longitud)') . '</td></tr>';
			
echo '<tr><td colspan=3><div class="centre"><input type="submit" name="UpdateHeader" value="' . _('Actualizar'). '"></td></tr>';
echo '</table><br>';

if ($_SESSION['CompanyRecord']['gllink_creditors']==1 AND $_SESSION['PaymentDetail']->SupplierID==''){
/* Set upthe form for the transaction entry for a GL Payment Analysis item */

	echo '<table class="table1" border="1"><tr>
			<th>' . _('Numero de Cheque').'</th>
			<th>' . _('Monto') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
			<th>' . _('Cuenta Contable') . '</th>
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
		echo '<tr>
			<td align=left>' . $PaymentItem->cheque . '</td>
			<td align=right>' . number_format($PaymentItem->Amount,2) . '</td>
			<td>' . $PaymentItem->GLCode . ' - ' . $PaymentItem->GLActName . '</td>
			<td>' . $PaymentItem->Narrative  . '</td>
			<td>' . $PaymentItem->tag . ' - ' . $tagname . '</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $PaymentItem->ID . '">' . _('Eliminar') . '</a></td>
			</tr>';
		$PaymentTotal += $PaymentItem->Amount;
		
		

	}
	echo '<tr><td></td><td align=right><b>' . number_format($PaymentTotal,2) . '</b></td><td></td><td></td><td></td></tr></table>';


	echo '<br><font size=3 color=BLUE><div class="centre">' . _('Captura de Analisis de Pago Contable') . '</div></font><br><table>';

	/*now set up a GLCode field to select from avaialble GL accounts */
	if (isset($_POST['GLManualCode'])) {
		echo '<tr><td>' . _('Cuenta Contable (Manual)') . ':</td>
			<td><input type=Text Name="GLManualCode" Maxlength=12 size=12onChange="return inArray(this, this.value, GLCode.options,'.
		"'".'El codigo de cuenta '."'".'+ this.value+ '."'".' no existe'."'".')"' .
			' onKeyPress="return restrictToNumbers(this, event)" VALUE='. $_POST['GLManualCode'] .'  ></td></tr>';
	} else {
		echo '<tr><td>' . _('Cuenta Contable (Manual)') . ':</td>
			<td><input type=Text Name="GLManualCode" Maxlength=12 size=12 onChange="return inArray(this, this.value, GLCode.options,'.
		"'".'El codigo de cuenta '."'".'+ this.value+ '."'".' no existe'."'".')"' .
			' onKeyPress="return restrictToNumbers(this, event)"></td></tr>';		
	}
	echo '<tr><td>' . _('Selecciona la Cuenta') . ':</td>
		<td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';

	$SQL = 'SELECT accountcode,
			accountname
			FROM chartmaster
			ORDER BY accountcode';

	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
		echo '</select></td></tr>';
		prnMsg(_('No se an configurado las cuentas contables todavia') . ' - ' . _('pagos no se pueden analizar contra cuentas si no estan dadas de alta'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['GLCode']) and $_POST['GLCode']==$myrow["accountcode"]){
				echo '<option selectED value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
			} else {
				echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
			}
		}
		echo '</select></td></tr>';
	}

	echo '<tr><td>'. _('Numero de Cheque/Deposito') .'</td><td><input type="text" name="cheque" Maxlength=12 size=12></td></tr>';

	if (isset($_POST['GLNarrative'])) {
		echo '<tr><td>' . _('Concepto') . ':</td><td><input type="text" name="GLNarrative" maxlength=50 size=52 value="' . $_POST['GLNarrative'] . '"></td></tr>';
	} else {
		echo '<tr><td>' . _('Concepto') . ':</td><td><input type="text" name="GLNarrative" maxlength=50 size=52></td></tr>';		
	}
	
	/*
	if (isset($_POST['Beneficiary2'])) {
		echo '<tr><td>' . _('Beneficiario').':</td><td>
			<textarea name="Beneficiary2" rows="2" cols="62" size=82>' . $_POST['Beneficiary2'] . '</textarea>
		</td></tr>';		
	} else {
		echo '<tr>
		<td>' . _('Beneficiario') . ':</td>
		<td>
		<textarea name="Beneficiary2" rows="2" cols="62" size=82></textarea>
		</td></tr>';
	}	
	*/
	
	
	
	if (isset($_POST['GLAmount'])) {
		echo '<tr><td>' . _('Monto') . ' (' . $_SESSION['PaymentDetail']->Currency . '):</td><td><input type=Text Name="GLAmount" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)"  onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')" VALUE=' . $_POST['GLAmount'] . '></td></tr>';		
	} else {
		echo '<tr><td>' . _('Monto') . ' (' . $_SESSION['PaymentDetail']->Currency . '):</td><td><input type=Text Name="GLAmount" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)"  onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')"></td></tr>';
	}	

	echo '</table>';
	echo '<div class="centre"><input type=submit name="Process" value="' . _('Procesar') . '"><input type=submit name="Cancel" value="' . _('Cancelar') . '"></div>';

} else {
/*a supplier is selected or the GL link is not active then set out
the fields for entry of receipt amt and disc */



	echo '<table>';
	
	
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
	} //end while loop
	
	echo '</select></td></tr>';
	

	echo '<tr><td>' . _('IVA ') . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
					<td><input type="text" name="mntIVA" maxlength=12 size=13 value="0">'._('*solo en caso de ser diferente la tasa que la seleccion de arriba').'</td></tr>';
	
	echo '<tr><td>' . _('Monto Total del Pago ') . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
					<td><input type="text" name="Amount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Amount . '></td></tr>';

	if (isset($_SESSION['PaymentDetail']->SupplierID)){ /*So it is a supplier payment so show the discount entry item */
		echo '<tr><td>' . _('Monto de Descuento') .  ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
			  <td colspan=2><input type="text" name="Discount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Discount . '></td>
		      </tr>';
		echo '<input type="hidden" name="SuppName" value="' . $_SESSION['PaymentDetail']->SuppName . '">';
	} else {
		echo '<input type="hidden" name="discount" Value=0>';
	}

	echo '<tr><td>' . _('Retencion IVA HONORARIOS')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionIVA" maxlength=12 size=13 value="0"></td></tr>';

	
	echo '<tr><td>' . _('Retencion ISR HONORARIOS')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionISR" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion IVA Arrendamiento ')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionxIVAArrendamiento" maxlength=12 size=13 value="0"></td></tr>';
		  
	echo '<tr><td>' . _('Retencion ISR Arrendamiento ')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionxArrendamiento" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x IVA Comisiones ')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionxComisiones" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x IVA Fletes ')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionxFletes" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x Impuesto Cedular ')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="RetencionxCedular" maxlength=12 size=13 value="0"></td></tr>';

	
	echo '</table>';

}
echo '<br><br><input type=submit name="CommitBatch" value="' . _('Aceptar y Procesar el Pago') . '">';
echo '</form>';

include('includes/footer.inc');
?>
