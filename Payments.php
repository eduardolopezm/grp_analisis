<?php

$funcion=103;
$PageSecurity = 5;

include('includes/DefinePaymentClass.php');
include('includes/session.inc');

include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include ('includes/XSAInvoicing.inc');

$title = _('Modulo de Cheques');


include('includes/header.inc');

if (isset($_POST['FromYear'])) {
	$FromYear=$_POST['FromYear'];
}elseif(isset($_GET['FromYear'])) {
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
if ((isset($_GET['tag'])) and ($_GET['tag'] != "")){
	$_POST['tag'] = $_GET['tag'];	
}elseif ((!isset($_POST['tag'])) and ($_POST['tag'] == "")){
	$_POST['tag'] = 0;
}
if ((isset($_GET['BankAccount'])) and ($_GET['BankAccount'] != "")){
	$_POST['BankAccount'] = $_GET['BankAccount'];	
}
if ((isset($_GET['Currency'])) and ($_GET['Currency'] != "")){
	$_POST['Currency'] = $_GET['Currency'];	
}
if ((isset($_GET['ExRate'])) and ($_GET['ExRate'] != "")){
	$_POST['ExRate'] = $_GET['ExRate'];	
}
if ((isset($_GET['FunctionalExRate'])) and ($_GET['FunctionalExRate'] != "")){
	$_POST['FunctionalExRate'] = $_GET['FunctionalExRate'];	
}
if ((isset($_GET['Paymenttype'])) and ($_GET['Paymenttype'] != "")){
	$_POST['Paymenttype'] = $_GET['Paymenttype'];	
}
if ((isset($_GET['ChequeNum'])) and ($_GET['ChequeNum'] != "")){
	$_POST['ChequeNum'] = $_GET['ChequeNum'];	
}
if ((isset($_GET['Beneficiario'])) and ($_GET['Beneficiario'] != "")){
	$_POST['Beneficiario'] = $_GET['Beneficiario'];	
}
if ((isset($_GET['Narrative'])) and ($_GET['Narrative'] != "")){
	$_POST['Narrative'] = $_GET['Narrative'];	
}


if (isset($_POST['ToYear'])) {
	$ToYear=$_POST['ToYear'];
} else if(isset($_GET['ToYear'])) {
	$ToYear=$_GET['ToYear'];
} else {
	$ToYear=date('Y');
}	
if (isset($_POST['ToMes'])) {
	$ToMes=$_POST['ToMes'];
} else if(isset($_GET['ToMes'])) {
	$ToMes=$_GET['ToMes'];
} else {
	$ToMes=date('m');
}
if (isset($_POST['ToDia'])) {
	$ToDia=$_POST['ToDia'];
} else if(isset($_GET['ToDia'])) {
	$ToDia=$_GET['ToDia'];
} else {
	$ToDia=date('d');
}


if ((isset($_SESSION['PaySupCurrency'])) and ($_SESSION['PaySupCurrency']!='')){
	$_SESSION['PaymentDetail']->Currency = $_SESSION['PaySupCurrency'];
}

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

//esto se agrego para dejar un solo campo de captura de nnum de cheque a solicitud del cliente. 
//El campo de captura "cheque" se cambio a tipo "hidden" y se le asigna el valor capturado en el campo ChequeNum
$_POST['cheque'] = $ChequeNum;


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

if ((isset($_GET['SupplierID'])) and ($_GET['SupplierID'] != "")){
	$_POST['SupplierID'] = $_GET['SupplierID'];	
}elseif ((isset($_POST['SupplierID'])) and ($_POST['SupplierID'] != "")){
	$_GET['SupplierID'] = $_POST['SupplierID'];
}

//note this is already linked from this page
echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Ir a pagina de Proveedores') . '</a><br>';

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Captura de Pagos') . '" alt="">' . ' ' . _('Pagos de Cuentas de Cheques') . '</p>';

echo '<div class="centre">';

if (isset($_POST['Currency'])) {
	$MonedaProveedor = $_POST['Currency'];
}

/**********************************************/
/*VALIDA SI EL PAGO VA A SER PARA UN PROVEEDOR*/
if (isset($_GET['SupplierID'])){
	/*The page was called with a supplierID check it is valid and
	default the inputs for Supplier Name and currency of payment */
	/*INICIALIZA LA CLASE DE LINEAS CONTABLES*/
	unset($_SESSION['PaymentDetail']->GLItems);
	unset($_SESSION['PaymentDetail']);
	unset($_SESSION['PaySupCurrency']);
	$_SESSION['PaymentDetail'] = new Payment;
	$_SESSION['PaymentDetail']->GLItemCounter = 1;

	$MonedaProveedor = '';
	
	/*OBTINENE DATOS DEL PROVEEDOR*/
	$SQL= "SELECT suppname,
		address1,
		address2,
		address3,
		address4,
		address5,
		address6,
		currcode,
		factorcompanyid, taxid
		FROM suppliers
		WHERE supplierid='" . $_GET['SupplierID'] . "'";
	$Result = DB_query($SQL, $db);
	
	if (DB_num_rows($Result)==0){
		prnMsg( _('El codigo de Proveedor con el que esta pagina fue llamada, no existe en base de datos de Proveedores') . '. ' . _('Si esta pagina es llamada desde la pagina de Proveedores, esto garantiza que el proveedor existe!'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		/*CODIGO DE PROVEEDOR VALIDO*/
		$myrow = DB_fetch_array($Result);
		if ($myrow['factorcompanyid'] == 1) {
			$_SESSION['PaymentDetail']->SuppName = $myrow['suppname'];
			$_SESSION['PaymentDetail']->TaxId= $myrow['taxid'];
			$_SESSION['PaymentDetail']->Address1 = $myrow['address1'];
			$_SESSION['PaymentDetail']->Address2 = $myrow['address2'];
			$_SESSION['PaymentDetail']->Address3 = $myrow['address3'];
			$_SESSION['PaymentDetail']->Address4 = $myrow['address4'];
			$_SESSION['PaymentDetail']->Address5 = $myrow['address5'];
			$_SESSION['PaymentDetail']->Address6 = $myrow['address6'];
			$_SESSION['PaymentDetail']->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail']->Currency = $myrow['currcode'];
			$_SESSION['PaySupCurrency']=$myrow['currcode'];
			//echo "<br>@1.PaySupCurrency: " .$_SESSION['PaySupCurrency'];
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
			$_SESSION['PaymentDetail']->SuppName = $myrow['suppname'];
			$_SESSION['PaymentDetail']->TaxId= $myrow['taxid'];
			$_SESSION['PaymentDetail']->Address1 = $myfactorrow['address1'];
			$_SESSION['PaymentDetail']->Address2 = $myfactorrow['address2'];
			$_SESSION['PaymentDetail']->Address3 = $myfactorrow['address3'];
			$_SESSION['PaymentDetail']->Address4 = $myfactorrow['address4'];
			$_SESSION['PaymentDetail']->Address5 = $myfactorrow['address5'];
			$_SESSION['PaymentDetail']->Address6 = $myfactorrow['address6'];
			$_SESSION['PaymentDetail']->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail']->Currency = $myrow['currcode'];
			$_SESSION['PaySupCurrency']=$myrow['currcode'];
			
			$_POST['Currency'] = $_SESSION['PaymentDetail']->Currency;
		}
		
		$MonedaProveedor = $myrow['currcode'];
	}
} //FIN DE IF SI PROVEEDOR SELECCIONADO
//echo "<br>2.@Moneda Proveedor: " . $_SESSION['PaymentDetail']->Currency;

/* SI CUENTA DE CHEQUES FUE SELECCIONADA ASIGNA VALORES A CLASE */
if (isset($_POST['BankAccount']) and $_POST['BankAccount']!=''){
	$_SESSION['PaymentDetail']->Account=$_POST['BankAccount'];
	/*Get the bank account currency and set that too */
	$ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
	$result = DB_query('SELECT currcode FROM bankaccounts WHERE accountcode ="' . $_POST['BankAccount'].'"',$db,$ErrMsg);
	if ($myrow = DB_fetch_row($result)) {
		$_SESSION['PaymentDetail']->AccountCurrency=$myrow[0];
		$MonedaCuentaCheques = $myrow[0];
	} else {
		$MonedaCuentaCheques = '';
	}
	
} else {
	if ($_SESSION['PaymentDetail']->AccountCurrency == "")
		$_SESSION['PaymentDetail']->AccountCurrency = $_SESSION['CompanyRecord']['currencydefault'];
	
	$MonedaCuentaCheques = '';
}

/* SI NO SE TIENE EL TIPO DE CAMBIO DE LA OPERACION TODAVIA */
if (isset($_POST['ExRate']) AND $_POST['ExRate'] != 1) {
	$TipoDeCambioDelDia = $_POST['ExRate'];
} else {
	/* VERIFICA QUE LA MONEDA DE EL PROVEEDOR O DE LA CUENTA DE CHEQUES SEA DIFERENTE A LA MONEDA BASE, SI NO PARA QUE NECESITAMOS EL TIPO DE CAMBIO */
	if ($_SESSION['CompanyRecord']['currencydefault'] != $MonedaCuentaCheques AND $MonedaCuentaCheques != '') {
		if ($MonedaCuentaCheques!=$_SESSION['CountryOfOperation']){
			$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$MonedaCuentaCheques."' order by fecha desc limit 1";
			$result = DB_query($SQLRate,$db);
			if ($myrow = DB_fetch_row($result)) {
				$TipoDeCambioDelDia = 1/$myrow[0]; /* ESTE ES EL TIPO DE CAMBIO DE MONEDA DE CUENTA DE CHEQUES */
			} else {
				prnMsg( _('El tipo de cambio para la moneda de Chequera:').$MonedaCuentaCheques._(' no existe en base de datos!'),'error');
				include('includes/footer.inc');
				exit;
			}
		}else{
			
			$TipoDeCambioDelDia=1;
		}
	} elseif ($_SESSION['CompanyRecord']['currencydefault'] != $MonedaProveedor AND $MonedaProveedor != '' ) {
		if ($MonedaProveedor!=$_SESSION['CountryOfOperation']){
			$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$MonedaProveedor."' order by fecha desc limit 1";
			$result = DB_query($SQLRate,$db);
			if ($myrow = DB_fetch_row($result)) {
				$TipoDeCambioDelDia = 1/$myrow[0]; /* ESTE ES EL TIPO DE CAMBIO DE MONEDA DE PROVEEDOR */
			} else {
				prnMsg( _('El tipo de cambio para la moneda del Proveedor:').$MonedaProveedor._(' no existe en base de datos!'),'error');
				include('includes/footer.inc');
				exit;
			}
		}else{
			$TipoDeCambioDelDia=1;
		}
	} else {
		$TipoDeCambioDelDia = 1;
	}
} 


/* CALCULA LOS TIPOS DE CAMBIO SI CUENTA YA SE SELECCIONO Y NO SE HA CALCULADO EL TIPO DE CAMBIO */
if (isset($_POST['BankAccount']) and $_POST['BankAccount']!=''){
	if (!isset($_POST['ExRate'])) {
		/*Get the exchange rate between the functional currency and the payment currency*/
		$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$_SESSION['PaymentDetail']->AccountCurrency."' order by fecha desc limit 1";
		$result = DB_query($SQLRate,$db);
		$myrow = DB_fetch_row($result);
		$tableExRateCount = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
		$tableFunctionalExRateCount = 1;
		$TipoCurrencyCount = $_SESSION['PaymentDetail']->AccountCurrency;
		//echo  "<br>@moneda del banco " . $TipoCurrencyCount;		
	} else {
		
	}
	
} else {
	/* Si todavia no se selecciona ninguna cuenta de cheques */
	$tableExRateCount = 1;
}


//-biviana--if (isset($_POST['DatePaid']) and $_POST['DatePaid']!='' AND Is_Date($_POST['DatePaid'])){
if (!isset($_SESSION['PaymentDetail']->DatePaid)
	OR isset($_POST['ToYear'])
	OR isset($_GET['ToYear'])) {
	
	$anio = $ToYear;
	
	if(strlen($ToMes) == 1)
		$mes = '0'.$ToMes;
	else
		$mes = $ToMes;
	
	if(strlen($ToDia) == 1)
		$dia = '0'.$ToDia;
	else
		$dia = $ToDia;

	$_SESSION['PaymentDetail']->DatePaid = $dia.'/'.$mes.'/'.$_POST['ToYear'];

//	$_SESSION['PaymentDetail']->DatePaid = $_POST['DatePaid'];
	//echo "<br>@no entra aki";
}

if (isset($_POST['ExRate']) and $_POST['ExRate']!=''){ /*desarrollo 1/$_POST[ExRate] */
	$_SESSION['PaymentDetail']->ExRate=($_POST['ExRate']); //ex rate between payment currency and account currency
}

/*
if (isset($_POST['FunctionalExRate']) and $_POST['FunctionalExRate']!=''){
	$_SESSION['PaymentDetail']->FunctionalExRate=$_POST['FunctionalExRate']; //ex rate between payment currency and account currency
}
*/

if (isset($_POST['Paymenttype']) and $_POST['Paymenttype']!=''){
	$_SESSION['PaymentDetail']->Paymenttype = $_POST['Paymenttype'];
}

/* CALCULA LOS TIPOS DE CAMBIO SI MONEDA NO MXN */

if (isset($_POST['Currency']) and $_POST['Currency']!='' and $_SESSION['PaymentDetail']->ExRate==''){
	//echo "<br>@tampoco entra aki";
	$_SESSION['PaymentDetail']->Currency=$_POST['Currency']; //payment currency

	/*Get the exchange rate between the functional currency and the payment currency*/
	$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$_SESSION['PaymentDetail']->Currency."' order by fecha desc limit 1";
	$result = DB_query($SQLRate,$db);
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
		$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$_SESSION['PaymentDetail']->AccountCurrency."' order by fecha desc limit 1";
		$result = DB_query($SQLRate,$db);
		$myrow = DB_fetch_row($result);
		$SuggestedFunctionalExRate = $myrow[0];

		/*Get the exchange rate between the functional currency and the payment currency*/
		$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$_SESSION['PaymentDetail']->Currency."' order by fecha desc limit 1";
		$result = DB_query($SQLRate,$db);
		$myrow = DB_fetch_row($result);
		$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
		/*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
		$SuggestedExRate = $tableExRate/$SuggestedFunctionalExRate;
		
		$_SESSION['PaymentDetail']->ExRate = $SuggestedFunctionalExRate;
		$_SESSION['PaymentDetail']->FunctionalExRate=1;
		


	}
	//echo "entro";
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
	//echo "<br>@taxrate: " . $taxrate;
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
$BankAccounts=array();
$SQL = 'SELECT accountcode FROM bankaccounts ORDER BY accountcode';
$result = DB_query($SQL,$db);
$i=0;
while ($Act = DB_fetch_row($result)){
	$BankAccounts[$i]= $Act[0];
	$i++;
}
// se agrega validacion de cuentas no permitidas en este modulo
foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
	if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1){
		if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' AND $PaymentItem->GLCode  == $_SESSION['CompanyRecord']['debtorsact']){
			prnMsg(_('Polizas que afecten cuentas de clientes no pueden ser procesadas en este modulo pues pueden causar desbalanceo entre el modulo del SaaS tecnoaplicada WebERP. Existe un parametro en configuracion para deshabilitar esta opcion.'),'warn');
			include('includes/footer.inc');
			exit;
		}
		if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' AND $PaymentItem->GLCode == $_SESSION['CompanyRecord']['creditorsact']){
			prnMsg(_('Polizas que afecten cuentas de proveedores no pueden ser procesadas en este modulo pues pueden causar desbalanceo entre el modulo del SaaS tecnoaplicada WebERP. Existe un parametro en configuracion para deshabilitar esta opcion.'),'warn');
			include('includes/footer.inc');
			exit;
		}
	}

	if ( $PaymentItem->GLCode  == '101001') {
		prnMsg(_('Polizas que afecten cuentas de cheques no pueden ser procesadas en este modulo pues pueden causar desbalanceo entre el modulo del SaaS tecnoaplicada WebERP.') . '. ' . _('Movimientos de Cuentas de Cheques deberan de ser capturadas en modulo de Cheques polizar y Depositos.'),'warn');
		include('includes/footer.inc');
		exit;
	}
}

/*
echo "Moneda Proveedor:".$MonedaProveedor."<BR>";
echo "Moneda Chequera:".$MonedaCuentaCheques."<BR>";
echo "Tipo de Cambio HOY:".$TipoDeCambioDelDia."<BR>";
*/


// echo "se encontro valor:".$_POST["Paymenttype"]." en la cadena: ".stripos($_POST["Paymenttype"], "Transferencia");

/***************************************************************************/
/**************** INICIA PROCESO Y CONFIRMACION DE MOVIMIENTOS *************/
if (isset($_POST['CommitBatch'])){

	if ($_POST['tag']=="" || $_POST['tag']=="0"){
		prnMsg('Debe seleccionar la unidad de negocio para realizar sus movimientos contables','error');
		include('includes/footer.inc');
		exit;

	}

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
	$fechaini= rtrim($ToYear).'/'.add_ceros(rtrim($ToMes),2).'/'.add_ceros(rtrim($ToDia),2);
	
	$fecha_especificada=$fechaini; //FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid);
	$fecha_actual=date("Y/m/d");
	$permiso =Havepermission($_SESSION['UserID'],410, $db);
	$permiso2 =Havepermission($_SESSION['UserID'],776, $db);
	$permisofuncion=0;
	$permisofuncion3=0;
	$permisofuncion2=0;
	
	
	if(($fecha_especificada > $fecha_actual) and $permiso==1){
		$permisofuncion=1;
	}
	
	if(($fecha_especificada <= $fecha_actual) and $permiso2==1){
		$permisofuncion2=1;
	}
	if(($fecha_especificada == $fecha_actual) and $permiso2==0 and $permiso==0){
		$permisofuncion3=1;
	}
	
	if($permisofuncion=1 or $permisofuncion3=1 or $permisofuncion2=1 ){
		/*First off  check we have an amount entered as paid ?? */
		
		$TotalAmount =0;
		foreach ($_SESSION['PaymentDetail']->GLItems AS $PaymentItem) {
			$TotalAmount += $PaymentItem->Amount;
		}
		
		/*
		if (stripos($_POST["Paymenttype"], "Transferencia") != false) {
			if (empty($_POST["bancodestino"]) || empty($_POST["cuentadestino"])) {
				prnMsg(_('Para el tipo de pago por transferencia debe de capturar Banco y Cuenta !!!'),'error');
				echo '<br>';
				include('includes/footer.inc');
				exit;
			}
		}
		*/
		
		if ($TotalAmount==0 AND
			($_SESSION['PaymentDetail']->Discount + $_SESSION['PaymentDetail']->Amount)/$_SESSION['PaymentDetail']->ExRate ==0){
			prnMsg( _('Este pago no tiene cantidades capturadas y no sera procesado'),'warn');
			include('includes/footer.inc');
			exit;
		}

		if ($_SESSION['FutureDate']==1){
			if (Date1GreaterThanDate2($_SESSION['PaymentDetail']->DatePaid,date("d/m/Y"))==1 and Havepermission($_SESSION['UserID'],410, $db)==0){
				prnMsg(_('La fecha es posterior y no cuenta con los permisos para realizar esta operacion'),'error');
					echo '<br>';
					include('includes/footer.inc');
					exit;
				
			}
		}

		if ($fecha_especificada < $fecha_actual and Havepermission($_SESSION['UserID'],776, $db)==0 ){
				prnMsg(_('La fecha:'.$fecha_especificada.' es anterior a la actual:'.$fecha_actual.' y no cuenta con los permisos para realizar esta operacion'),'error');
					echo '<br>';
					include('includes/footer.inc');
					exit;
		}
		
		
		//descomponer fecha
		$PeriodNo = GetPeriod($_SESSION['PaymentDetail']->DatePaid, $db, $_POST['tag']);
	
		if (isset($_POST['tag'])){
			if ($_POST['tag'] == "0"){
				prnMsg(_('Debe de Seleccionar una Unidad de Negocio'),'error');
				echo '<br>';
				prnMsg (_('<a href="#" onclick="history.go(-1); return false;">Regresar</a>'),'error');
				exit;
				
			} else {
				$SQL = 'SELECT 	tagref
					FROM 	tagsxbankaccounts
					WHERE 	tagsxbankaccounts.accountcode="' . $_POST['BankAccount'] . '" and
						tagsxbankaccounts.tagref='.$_POST['tag'];
				$result = DB_query($SQL,$db);
				if (!$Act = DB_fetch_row($result)){
					prnMsg(_('La Unidad de Negocio seleccionada no es valida para la chequera seleccionada..***.'),'error');
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

		//OBTENGO EL TRANSNO QUE CORRESPONDE ANTES DE ENTRAR AL DB_Txn_Begin, SI NO HARIA EL COMMIT ANTES DE TIEMPO.
		if ($_SESSION['PaymentDetail']->SupplierID=='') {
			$TransNo = GetNextTransNo( 1, $db);
		}else{
			if ((isset($_POST['anticipo'])) and ($_POST['anticipo']!='')){
				$Transtype = 121;
			}else{
				$Transtype = 22;
			}
			$TransNo = GetNextTransNo($Transtype, $db);
		}
		
		$result = DB_Txn_Begin($db);

		// PAGO CONTABLE ....
		if ($_SESSION['PaymentDetail']->SupplierID=='') {
			//its a nominal bank transaction type 1
			//$TransNo = GetNextTransNo( 1, $db);
			
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

					if ($PaymentItem->tag==0){
						prnMsg('Debe seleccionar la unidad de negocio para realizar sus movimientos contables (PaymentItem->tag)','error');
						include('includes/footer.inc');
						exit;
				
					}

					/*The functional currency amount will be the
					 payment currenct amount  / the bank account currency exchange rate  - to get to the bank account currency
					 then / the functional currency exchange rate to get to the functional currency */
					if ($PaymentItem->cheque=='') $PaymentItem->cheque=0;
					
					/*
					$SQL = 'SELECT currcode, rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode = currencies.currabrev
							WHERE accountcode=' . $_SESSION['PaymentDetail']->Account;
					//echo 	$SQL;	
					$TrfToAccountResult = DB_query($SQL,$db);
					$TrfToBankRow = DB_fetch_array($TrfToAccountResult) ;
					$TrfToBankCurrCode = $TrfToBankRow['currcode'];
					$TrfToBankExRate = $TrfToBankRow['rate'];
					
					if ($TrfToBankCurrCode != "MXN" && $_POST['Currency'] == "MXN")
						$CreditorTotal = $PaymentItem->Amount;
					elseif ($TrfToBankCurrCode == "MXN" && $_POST['Currency'] == "MXN")
						$CreditorTotal = $PaymentItem->Amount;
					elseif ($TrfToBankCurrCode == "MXN" && $_POST['Currency'] == "USD")
						$CreditorTotal = $PaymentItem->Amount / ($_SESSION['PaymentDetail']->FunctionalExRate/$_SESSION['PaymentDetail']->ExRate);
					elseif ($TrfToBankCurrCode == "USD" && $_POST['Currency'] == "USD")
						$CreditorTotal = ($PaymentItem->Amount)/($_SESSION['PaymentDetail']->ExRate/$_SESSION['PaymentDetail']->FunctionalExRate);
					else
						$CreditorTotal = $PaymentItem->Amount;    
					*/
					
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$PaymentItem->Narrative = $PaymentItem->Narrative . "*TC:".$TipoDeCambioDelDia;
						$CreditorTotal = $PaymentItem->Amount * $TipoDeCambioDelDia;
					} else {
						$CreditorTotal = $PaymentItem->Amount;
					}
					    
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
						'". $TransNo ."',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) ."',
						'". $PeriodNo ."',
						'". $PaymentItem->GLCode ."',
						'" . $PaymentItem->Narrative ." @ ".$Beneficiario."',
						'". $CreditorTotal ."',
						'". $PaymentItem->cheque ."',
						'" . $PaymentItem->tag . "'
						)";
// 					$Narrative = $PaymentItem->Narrative;//
// 					$ErrMsg = _('No pude insertar el movimiento contable usando este SQL');
// 					$ISQL = Insert_Gltrans(1,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $PaymentItem->GLCode,$Narrative, $PaymentItem->tag ,$_SESSION['UserID'],
// 							$_SESSION['PaymentDetail']->ExRate,
// 							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $CreditorTotal,$db,$PaymentItem->cheque,'CUENTA CONTABLE');
// 					$result = DB_query($ISQL,$db,$ErrMsg,_('El SQL que fallo fue'),true);
	
					$Narrative = $PaymentItem->Narrative . "/moneda:". $MonedaCuentaCheques. " -TC:".$TipoDeCambioDelDia." @ ".$Beneficiario;
					$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
					$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
					$ISQL = Insert_Gltrans(1,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $PaymentItem->GLCode,$Narrative, $PaymentItem->tag ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $CreditorTotal,$db,$PaymentItem->cheque,'CUENTA CONTABLE', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					
					
					//$TotalAmount += $PaymentItem->Amount;
					$TotalAmount +=$CreditorTotal;
				}
				//echo "<br>Tipo cambio:".$_SESSION['PaymentDetail']->ExRate;
				$_SESSION['PaymentDetail']->Amount = $TotalAmount;
				$_SESSION['PaymentDetail']->Discount=0;
			}

			/*Run through the GL postings to check to see if there is a
			posting to another bank account (or the same one) if there
			is then a receipt needs to be created for this account too*/
			
			/* RECORRE TODAS LAS CUENTAS A PAGAR PARA VERIFICAR QUE NO ES UN PAGO ENTRE CUENTAS
			   O A LA MISMA CUENTA.  SI ES ASI, UN RECIBO SE NECESITA CREAR PARA ESTA CUENTA */

			//FRUEBEL: SE COMENTA ESTE FOREACH DEBIDO A QUE NO TIENE SENTIDO, VERIFICAR
			//GONZALO: COMO NO VA A TENER SENTIDO, SI ES UN TRASPASO DEBEN DE GENERARSE DOS MOVIMIENTOS EN EL BANK TRANS.
			
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
				if (in_array($PaymentItem->GLCode, $BankAccounts)) {
					
					/* SI LA CUENTA A PAGAR SI ES CUENTA DE BANCO */
					
					/* Need to deal with the case where the payment from one bank
					account could be to a bank account in another currency */
	
					/* Get the currency and rate of the bank account transferring to */
					$SQL = 'SELECT currcode, rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode = currencies.currabrev
							WHERE accountcode="' . $PaymentItem->GLCode.'"';
							
					$TrfToAccountResult = DB_query($SQL,$db);
					$TrfToBankRow = DB_fetch_array($TrfToAccountResult) ;
					$TrfToBankCurrCode = $TrfToBankRow['currcode'];
					$TrfToBankExRate = $TrfToBankRow['rate'];
					
					
					if ($MonedaCuentaCheques == $TrfToBankCurrCode){
						/*Make sure to use the same rate if the transfer is
						between two bank accounts in the same currency */
						$TrfToBankExRate = $TipoDeCambioDelDia;
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
					
					//$ReceiptTransNo = GetNextTransNo( 2, $db);
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
								beneficiary,
								tagref
								)
						VALUES (' . $TransNo . ",
							1,
							'". $PaymentItem->GLCode . "', 'ENTRE CUENTAS BANCO:"
														. _('Transferencia Desde ') . $_SESSION['PaymentDetail']->Account . ' - ' . $PaymentItem->Narrative . ' - ' .$_SESSION['PaymentDetail']->Narrative . " ',
							" . $TrfToBankExRate. ",
							" .  (1/$TipoDeCambioDelDia). ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							'" . $_SESSION['PaymentDetail']->Paymenttype . "',
							" . $PaymentItem->Amount*$TipoDeCambioDelDia . ",
							'" . $TrfToBankCurrCode . "',
							'" . $Beneficiario . "',
							'" . $_POST['tag'] . "'
						)";
					
					/*
					//$ReceiptTransNo = GetNextTransNo( 2, $db);					
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
								beneficiary,
								tagref, 
								cuentadestino, 
								bancodestino, 
								rfcdestino
								)
						VALUES (' . $TransNo . ',
							1,
							' . $PaymentItem->GLCode . ", 'ENTRE CUENTAS BANCO:"
							. _('Transferencia Desde ') . $_SESSION['PaymentDetail']->Account . ' - ' . $PaymentItem->Narrative . ' - ' .$_SESSION['PaymentDetail']->Narrative . " ',
							" . $TrfToBankExRate. ",
							" .  (1/$TipoDeCambioDelDia). ",
							'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							'" . $_SESSION['PaymentDetail']->Paymenttype . "',
							" . $PaymentItem->Amount*$TipoDeCambioDelDia . ",
							'" . $TrfToBankCurrCode . "',
							'" . $Beneficiario . "',
							'" . $_POST['tag'] . "',
							'" . $_POST['cuentadestino'] . "',
							'" . $_POST['bancodestino'] . "',
							'" . $_POST['rfcdestino'] . "'
						)"; */
					/*if($_SESSION['UserID'] == "admin"){
						echo 'SQL1 <pre>'.$SQL;
					}*/
					//echo $SQL;	
					$ErrMsg = _('No pude insertar una transaccion en banco porque');
					$DbgMsg =  _('No pude insertar una transaccion en banco con el SQL');
					$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	
				} //FIN DE IF SI LA CUENTA SI ES DE CHEQUES
				else {
					echo "Cuenta".$PaymentItem->GLCode." no es de cheques -**";
				}
			} // FIN DEL FOR DE LAS APLICACIONES A CUENTAS CONTABLES
		// FIN DE PAGOS CONTABLES
		} else {
			/*********************************************************************/
			// INICIO DE PAGO A PROVEEDOR
			/* Its a supplier payment type 22 */
			
			//echo "ivas " . $_POST['RetencionIVA'] . " " . $_POST['RetencionISR'];
			$DescuentoAlPago = $_POST['Discount'];
			$RetencionIVA = $_POST['RetencionIVA'];
			$RetencionISR = $_POST['RetencionISR'];
			$Beneficiario = $_POST['Beneficiario'];
			$RetencionxCedular = $_POST['RetencionxCedular'];
			$RetencionxFletes = $_POST['RetencionxFletes'];
			$RetencionxComisiones = $_POST['RetencionxComisiones'];
			$RetencionxArrendamiento = $_POST['RetencionxArrendamiento'];
			$RetencionxIVAArrendamiento = $_POST['RetencionxIVAArrendamiento'];
			$GananciaPerdidaCambiaria = $_POST['GananciaPerdidaCambiaria'];
			$mntIVA = $_POST['mntIVA'];
			$ChequeNum = $_POST['ChequeNum'];
			
			$CurrDescuentoAlPago = $DescuentoAlPago;
			$CurrRetencionIVA = $_POST['RetencionIVA'];
			$CurrRetencionISR = $_POST['RetencionISR'];
			$CurrBeneficiario = $_POST['Beneficiario'];
			$CurrRetencionxCedular = $_POST['RetencionxCedular'];
			$CurrRetencionxFletes = $_POST['RetencionxFletes'];
			$CurrRetencionxComisiones = $_POST['RetencionxComisiones'];
			$CurrRetencionxArrendamiento = $_POST['RetencionxArrendamiento'];
			$CurrRetencionxIVAArrendamiento = $_POST['RetencionxIVAArrendamiento'];
			$CurrGananciaPerdidaCambiaria = $_POST['GananciaPerdidaCambiaria'];
			$CurrmntIVA = $_POST['mntIVA'];
			
			// Aplicar reembolso caja
			$aplicarReembolso = false;
			$supplierId = $_SESSION['PaymentDetail']->SupplierID;
			$sql = "SELECT aplicaretencion FROM suppliers 
				INNER JOIN supplierstype USING(typeid)
				WHERE supplierid = '$supplierId'";
			$rsRem = DB_query($sql, $db);
			if ($rowRem = DB_fetch_array($rsRem)) {
				$aplicaretencion = ($rowRem['aplicaretencion'] == 1);
			}
			
			if ($aplicarReembolso) {
				
				if (
					empty($RetencionIVA) && 
					empty($RetencionISR) && 
					empty($RetencionxCedular) && 
					empty($RetencionxArrendamiento) && 
					empty($RetencionxComisiones) && 
					empty($RetencionxFletes) && 
					empty($RetencionxIVAArrendamiento)
				) {
					prnMsg('Debe especificar las retenciones.', 'error');
					include('includes/footer.inc');
					exit;
				}
			}
			
			if ((isset($_POST['anticipo'])) and ($_POST['anticipo']!='')){
				$Transtype = 121;
			}else{
				$Transtype = 22;
			}
		
			if (!isset($legaid) or $legaid=='') {
				$sql ="Select legalid,areacode from tags where tagref=".$_POST['tag'];
				$result = DB_query($sql, $db);
				while ($myrow=DB_fetch_array($result,$db)) {
					$legaid=$myrow['legalid'];
					$area=$myrow['areacode'];
				}
			}
			
			//******------******//
			$InvoiceNoTAG = DocumentNext($Transtype, $_POST['tag'],$area,$legaid, $db);
			//******------******//
			
			$MontoProveedorMonedaChequera = $_SESSION['PaymentDetail']->Amount+$DescuentoAlPago+$CurrRetencionIVA+$CurrRetencionISR+$CurrRetencionxCedular+$CurrRetencionxFletes+$CurrRetencionxComisiones+$CurrRetencionxArrendamiento+$CurrRetencionxIVAArrendamiento;
			if ($mntIVA == 0 || $mntIVA == ""){
				$MontoIvaMonedaChequera=($MontoProveedorMonedaChequera / (1 +$taxrate));
				$MontoIvaMonedaChequera=$MontoProveedorMonedaChequera-$MontoIvaMonedaChequera;
			}	
			else
				$MontoIvaMonedaChequera=$mntIVA;
				
			if ($MonedaCuentaCheques != $MonedaProveedor AND $MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
				$TipoCambioChequera = $TipoDeCambioDelDia;
				$MontoProveedorSupp = ($_SESSION['PaymentDetail']->Amount+$DescuentoAlPago+$CurrRetencionIVA+$CurrRetencionISR+$CurrRetencionxCedular+$CurrRetencionxFletes+$CurrRetencionxComisiones+$CurrRetencionxArrendamiento+$CurrRetencionxIVAArrendamiento) * $TipoDeCambioDelDia;
				$_SESSION['PaymentDetail']->Narrative = $_SESSION['PaymentDetail']->Narrative. "*TC:" . $TipoDeCambioDelDia;
			} elseif ($MonedaCuentaCheques != $MonedaProveedor AND $MonedaCuentaCheques == $_SESSION['CompanyRecord']['currencydefault']) {
				$TipoCambioChequera = 1;
				$MontoProveedorSupp = ($_SESSION['PaymentDetail']->Amount+$DescuentoAlPago+$CurrRetencionIVA+$CurrRetencionISR+$CurrRetencionxCedular+$CurrRetencionxFletes+$CurrRetencionxComisiones+$CurrRetencionxArrendamiento+$CurrRetencionxIVAArrendamiento) / $TipoDeCambioDelDia;
				$_SESSION['PaymentDetail']->Narrative = $_SESSION['PaymentDetail']->Narrative. "/TC:" . $TipoDeCambioDelDia;
			} else {
				$TipoCambioChequera = $TipoDeCambioDelDia;
				$MontoProveedorSupp = $_SESSION['PaymentDetail']->Amount+$DescuentoAlPago+$CurrRetencionIVA+$CurrRetencionISR+$CurrRetencionxCedular+$CurrRetencionxFletes+$CurrRetencionxComisiones+$CurrRetencionxArrendamiento+$CurrRetencionxIVAArrendamiento;
			}
			
			if ($mntIVA == 0 || $mntIVA == ""){
				$taximpuesto=($MontoProveedorSupp / (1 +$taxrate));
				$taximpuesto=$MontoProveedorSupp-$taximpuesto;
			}	
			else
				$taximpuesto=$mntIVA;
				
			
			//$SQLRate = "SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency ."'";
			$SQLRate = "select rate from tipocambio where fecha <= NOW() and currency = '".$_SESSION['PaymentDetail']->Currency."' order by fecha desc limit 1";
			$ResultRate=DB_query($SQLRate,$db);
			$myrowRate=DB_fetch_row($ResultRate);
			$todayRate=(1/$myrowRate[0]);
			//
			$SQLProv = "SELECT suppliers.supplierid,
								suppliers.suppname
						FROM suppliers
						WHERE suppliers.supplierid = '".$_SESSION['PaymentDetail']->SupplierID."'";
			$ResProv = DB_query($SQLProv,$db);
			$RowProv = DB_fetch_array($ResProv);
			$nomProv = $RowProv['suppname'];
			
			/* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
			/* Create a SuppTrans entry for the supplier payment *///
			$SQL = "INSERT INTO supptrans (transno,
						type,
						supplierno,
						trandate,
						suppreference,
						rate,
						ovamount,
						ovgst,
						transtext,
						tagref,
						origtrandate,
						ref1,
						currcode,
						duedate,
						trandaterate
						) ";
			$SQL = $SQL . 'VALUES (' . $TransNo . ",
					'" . $Transtype . "',
					'" . $_SESSION['PaymentDetail']->SupplierID . "',
					'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
					'" . $_SESSION['PaymentDetail']->Paymenttype . "',
					" . (1/$TipoCambioChequera) . ",
					" . -($MontoProveedorMonedaChequera-$MontoIvaMonedaChequera) . ",
					" . -$MontoIvaMonedaChequera . ",
					'" . $_SESSION['PaymentDetail']->Narrative . "/Descuento Al Pago:".$DescuentoAlPago."',
					" . $_POST['tag'] . ",
					now(),
					'" . $ChequeNum . "',
					'" . $MonedaCuentaCheques . "',
					'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
					'".$todayRate."'
				)";
			
			$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
			$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			/* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
			/*Update the supplier master with the date and amount of the last payment made */
			$SQL = "UPDATE suppliers SET
				lastpaiddate = '" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				lastpaid=" . ($_SESSION['PaymentDetail']->Amount+$DescuentoAlPago) ."
				WHERE suppliers.supplierid='" . $_SESSION['PaymentDetail']->SupplierID . "'";

			$ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
			$DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$_SESSION['PaymentDetail']->Narrative = $_SESSION['PaymentDetail']->SupplierID ." ". $nomProv  ."-" . $_SESSION['PaymentDetail']->Narrative;
		
			$CreditorTotal = $MontoProveedorSupp;
			
			/* SI ESTA HABILITADA LA INTEGRACION CONTABLE CON CUENTAS POR PAGAR */
			if ($_SESSION['CompanyRecord']['gllink_creditors']==1){
				/* then do the supplier control GLTrans */
				/* Now debit creditors account with payment + discount */
			
				//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
				if ($_SESSION['PaymentDetail']->SupplierID != ''){
					$tipoproveedor = ExtractTypeSupplier($_SESSION['PaymentDetail']->SupplierID,$db);
					if ((isset($_POST['anticipo'])) and ($_POST['anticipo']!='')){
						$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_debtoradvances",$db);
					}else{
						$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
					}
				}else{
					
					$ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
				}
				
				if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
					$CreditorTotal = ($_SESSION['PaymentDetail']->Amount+$DescuentoAlPago+$CurrRetencionIVA+$CurrRetencionISR+$CurrRetencionxCedular+$CurrRetencionxFletes+$CurrRetencionxComisiones+$CurrRetencionxArrendamiento+$CurrRetencionxIVAArrendamiento) * $TipoDeCambioDelDia;
				} else {
					$CreditorTotal = $_SESSION['PaymentDetail']->Amount + $DescuentoAlPago+$CurrRetencionIVA+$CurrRetencionISR+$CurrRetencionxCedular+$CurrRetencionxFletes+$CurrRetencionxComisiones+$CurrRetencionxArrendamiento+$CurrRetencionxIVAArrendamiento;
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
						'". $Transtype ."',
						'". $TransNo ."',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) ."',
						'". $PeriodNo ."',
						'". $ctaxtipoproveedor ."',
						'" . $_SESSION['PaymentDetail']->Narrative . "/moneda:". $MonedaCuentaCheques. " -TC:".$TipoDeCambioDelDia."',
						'". $CreditorTotal ."',
						'". $_POST['tag'] ."',
						'". $ChequeNum ."'
					)";
				$Narrative = $_SESSION['PaymentDetail']->Narrative . "/moneda:". $MonedaCuentaCheques. " -TC:".$TipoDeCambioDelDia." @ ".$Beneficiario;
				$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
				$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
				$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $ctaxtipoproveedor,$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
						$_SESSION['PaymentDetail']->ExRate,
						'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $CreditorTotal,$db,$ChequeNum,'PROVEEDOR', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
				$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				
				/*****************************************/
				/* MOVIMIENTOS DE IVA POR PAGAR A PAGADO */
			//
				//$SQL = 'select * from taxauthorities where taxid=1'.$_SESSION['DefaultTaxCategory'];
				$SQL = "SELECT *
						FROM taxauthorities
						WHERE taxid = 1";
				$result2 = DB_query($SQL,$db);//
				$Taxautorities = DB_fetch_array($result2);
				$SQL = 'select * from taxcategories where taxcatid='.$_POST['TaxCat'];
				//
				$result2 = DB_query($SQL,$db);
				if ($TaxAccs = DB_fetch_array($result2)){
					
					$sql = "select taxrate from taxauthrates where taxcatid = '" . $TaxAccs['taxid'] . "'";
					$resultx = DB_query($sql, $db);
					if ($myrowtx = DB_fetch_array($resultx)) {
						$tasaiva = $myrowtx['taxrate'];
					} else {
						$tasaiva = 0;
						$texto='IVA 0%';
					}
					
					
					if ($mntIVA == 0 || $mntIVA == ""){
						$taximpuesto=($CreditorTotal / (1 +$taxrate));
						$taximpuesto=$CreditorTotal-$taximpuesto;
					}	
					else
						$taximpuesto=$mntIVA;
					
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
							'". $Transtype . "',
							'". $TransNo . "',
							'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							'". $PeriodNo ."',
							'". $TaxAccs['purchtaxglaccount'] ."',
							'" . $_SESSION['PaymentDetail']->Narrative . "-IVA:".$taxrate."',
							'". ($taximpuesto*-1) ."',
							'". $_POST['tag'] ."',
							'". $ChequeNum ."'
						)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative . "-IVA:".$taxrate." @ ".$Beneficiario;
					$Monto = ($taximpuesto*-1);
					$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
					$DbgMsg = _('El SQL utilizado fue');
					
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $Taxautorities['purchtaxglaccount'],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'IVA '.$TaxAccs['taxcatname'], 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
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
							'". $Transtype . "',
							'". $TransNo . "',
							'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
							'". $PeriodNo . "',
							'". $TaxAccs['purchtaxglaccountPaid'] ."',
							'" . $_SESSION['PaymentDetail']->Narrative . "',
							'". $taximpuesto ."',
							'". $_POST['tag'] ."',
							'". $ChequeNum ."'
						)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
					$DbgMsg = _('El SQL utilizado fue');
					$Monto = ($taximpuesto);
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $Taxautorities['purchtaxglaccountPaid'],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'IVA '.$TaxAccs['taxcatname'], 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
									
				} //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS
			
				if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
					$MDiscount = $_SESSION['PaymentDetail']->Discount * $TipoDeCambioDelDia;
				} else {
					$MDiscount = $_SESSION['PaymentDetail']->Discount;
				}
				
				if ($_SESSION['PaymentDetail']->Discount !=0){
		
				       //echo "Entro " . $_SESSION['CompanyRecord']['gllink_creditors'];	
					
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
						'". $TransNo ."',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo ."',
						'". $_SESSION['CompanyRecord']["pytdiscountact"] ."',
						'" . $_SESSION['PaymentDetail']->Narrative ."',
						'". (-1)*($MDiscount)  /*(($_SESSION['PaymentDetail']->Discount/$_SESSION['PaymentDetail']->ExRate)/$_SESSION['PaymentDetail']->FunctionalExRate)*/ . "',
						'". $_POST['tag'] ."',
						'". $ChequeNum ."'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($MDiscount);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $_SESSION['CompanyRecord']["pytdiscountact"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'DESCUENTO', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					
				} // end if discount


				if ($CurrRetencionISR !=0){
					/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
					
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionISR = $CurrRetencionISR * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionISR = $CurrRetencionISR;
					}	
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
						'". $TransNo . "',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo . "',
						'". $_SESSION['CompanyRecord']["gllink_retencionhonorarios"] ."',
						'" . $_SESSION['PaymentDetail']->Narrative ."',
						'". (-1)*($CurrRetencionISR) . "',						
						'". $_POST['tag'] ."',
						'" . $ChequeNum . "'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionISR);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento isr porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento isr utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $_SESSION['CompanyRecord']["gllink_retencionhonorarios"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'ISR', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount
			
			
			
				if ($CurrRetencionIVA !=0){
					
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionIVA = $CurrRetencionIVA * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionIVA = $CurrRetencionIVA;
					}
					
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
						'" . $TransNo . "',
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'" . $PeriodNo . "',
						'" . $_SESSION['CompanyRecord']["gllink_retencioniva"] . "',
						'" . $_SESSION['PaymentDetail']->Narrative . "',						
						'" . (-1)*($CurrRetencionIVA) . "',						
						'" . $_POST['tag'] . "',
						'" . $ChequeNum . "'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionIVA);
					$ErrMsg = _('No pude insertar la transaccion contable para la descuento retencion iva porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento retencion iva utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $_SESSION['CompanyRecord']["gllink_retencioniva"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'IVA RETENIDO', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount				


			//echo "Cedular " . $RetencionxCedular . "<br>";

				if ($CurrRetencionxCedular !=0){
	
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionxCedular = $CurrRetencionxCedular * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionxCedular = $CurrRetencionxCedular;
					}		
									    
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
					$SQL=$SQL . "VALUES ('". $Transtype . "',
						'". $TransNo . "',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo . "',
						'". $_SESSION['CompanyRecord']["gllink_retencionCedular"] . "',
						'" . $_SESSION['PaymentDetail']->Narrative . "',						
						'". (-1)*($CurrRetencionxCedular) . "',						
						'". $_POST['tag'] . "',
						'". $ChequeNum . "'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionxCedular);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento cedular porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento cedular utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo, $_SESSION['CompanyRecord']["gllink_retencionCedular"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'RETENCION CEDULAR', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount				


				if ($CurrRetencionxFletes !=0){
					if($_SESSION['UserID'] == "admin"){
						echo 'entro1<br>';
						echo '<br>'.$CurrRetencionxFletes.'<br>';
					}
					
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionxFletes = $CurrRetencionxFletes * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionxFletes = $CurrRetencionxFletes;
					}
					
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
						'". $TransNo . "',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo . "',
						'". $_SESSION['CompanyRecord']["gllink_retencionFletes"] . "',
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						'". (-1)*($CurrRetencionxFletes) . "',						
						'". $_POST['tag'] . "',
						'". $ChequeNum . "'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionxFletes);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento fletes porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento fletes utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,  $_SESSION['CompanyRecord']["gllink_retencionFletes"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'RETENCION FLETE', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount				


				if ($CurrRetencionxComisiones !=0){
					
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionxComisiones = $CurrRetencionxComisiones * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionxComisiones = $CurrRetencionxComisiones;
					}
					
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
						'". $TransNo . "',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo . "',
						'". $_SESSION['CompanyRecord']["gllink_retencionComisiones"] . "',
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						'". (-1)*($CurrRetencionxComisiones) . "',						
						'". $_POST['tag'] . "',
						'". $ChequeNum . "'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionxComisiones);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento comisiones porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento comisiones utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,  $_SESSION['CompanyRecord']["gllink_retencionComisiones"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'RETENCION COMISIONES', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount				

				if ($CurrRetencionxArrendamiento !=0){
						   
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionxArrendamiento = $CurrRetencionxArrendamiento * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionxArrendamiento = $CurrRetencionxArrendamiento;
					}
					
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
						'". $TransNo . "',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo . "',
						'". $_SESSION['CompanyRecord']["gllink_retencionarrendamiento"] . "',
						'" . $_SESSION['PaymentDetail']->Narrative . "',
						'". (-1)*($CurrRetencionxArrendamiento) . "',						
						'". $_POST['tag'] . "',
						'". $ChequeNum . "'
					)";
					$Narrative = $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionxArrendamiento);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,  $_SESSION['CompanyRecord']["gllink_retencionarrendamiento"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'RETENCION ARRENDAMIENTO', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount
			
				if ($CurrRetencionxIVAArrendamiento !=0){
					
					if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
						$CurrRetencionxIVAArrendamiento = $CurrRetencionxIVAArrendamiento * $TipoDeCambioDelDia;
					} else {
						$CurrRetencionxIVAArrendamiento = $CurrRetencionxIVAArrendamiento;
					}
					
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
						'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						" . $PeriodNo . ",
						" . $_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"] . ",
						'RET IVA ARR:" . $_SESSION['PaymentDetail']->Narrative . "',
						" . (-1)*($CurrRetencionxIVAArrendamiento) . ",						
						" . $_POST['tag'] . ",
						'" . $ChequeNum . "'
					)";
					$Narrative = "RET IVA ARR:" . $_SESSION['PaymentDetail']->Narrative." @ ".$Beneficiario;
					$Monto = (-1)*($CurrRetencionxIVAArrendamiento);
					$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,  $_SESSION['CompanyRecord']["gllink_retencionIVAarrendamiento"],$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
							$_SESSION['PaymentDetail']->ExRate,
							'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'IVA RETENCION ARRENDAMIENTO', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
					$ErrMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento porque');
					$DbgMsg = _('No pude insertar la transaccion contable para el descuento arrendamiento utilizando el SQL');
					$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} // end if discount
			} // end if gl creditors
		} // FIN DE IF DE PAGO A PROVEEDOR


		/* AHORA REALIZA LAS TRANSACCIONES COMUNES DE CONTABILIDAD*/
		if ($_SESSION['CompanyRecord']['gllink_creditors']==1){ /* then do the common GLTrans */
			$SumaRetenciones = $RetencionIVA + $RetencionISR + $RetencionxCedular + $RetencionxFletes + $RetencionxComisiones + $RetencionxArrendamiento + $RetencionxIVAArrendamiento;		
			/*Validacion si la cuenta es NO pesos y el proveedor es en pesos*/
			
			if ($_SESSION['PaymentDetail']->SupplierID=='') {
				/* YA ESTA CONVERTIDO AL TIPO DE CAMBIO LOCAL .... */
				$MCuadra = $_SESSION['PaymentDetail']->Amount;
			}else{
				if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault']) {
					$MCuadra = $_SESSION['PaymentDetail']->Amount * $TipoDeCambioDelDia;
				} else {
					$MCuadra = $_SESSION['PaymentDetail']->Amount;
				}
				
			}	
			
			if ($MCuadra !=0){
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
				$SQL = $SQL . "VALUES ('". $Transtype ."',
						'". $TransNo ."',
						'". FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
						'". $PeriodNo . "',
						'". $_SESSION['PaymentDetail']->Account ."',
						'" . $_SESSION['PaymentDetail']->Narrative . "-DESC:".$DescuentoAlPago."-TC:".$TipoDeCambioDelDia."',
						'". (-1)* ($MCuadra)  . "',
						'". $_POST['tag'] . "',
						'". $ChequeNum . "'
					)";
					
				$Narrative = $_SESSION['PaymentDetail']->Narrative . "-DESC:".$DescuentoAlPago."-TC:".$TipoDeCambioDelDia." @ ".$Beneficiario;
				$Monto = (-1)* ($MCuadra);
				$ErrMsg =  _('No pude insertar el cargo a la cuenta de bancos porque');
				$DbgMsg =  _('No pude insertar el cargo a la cuenta de bancos usando el SQL');
				
				$ISQL = Insert_Gltrans($Transtype,$TransNo,FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid),$PeriodNo,  $_SESSION['PaymentDetail']->Account,$Narrative, $_POST['tag'] ,$_SESSION['UserID'],
						$_SESSION['PaymentDetail']->ExRate,
						'','','',0,0,'',0,$_SESSION['PaymentDetail']->SupplierID ,0, $Monto,$db,$ChequeNum,'PROVEEDOR', 0, $_POST['bancodestino'], $_POST['rfcdestino'], $_POST['cuentadestino']);
				$result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
	
			} // FIN IF SI MONTO DIFERENTE DE CERO
		} // FIN IF SI ESTA INTEGRADO PROVEEDORES CON CONTABILIDAD


		$conversion = $_SESSION['PaymentDetail']->Amount + $_SESSION['PaymentDetail']->Discount - $SumaRetenciones;
		$tipomoneda = $MonedaCuentaCheques;
		
		if ($_SESSION['PaymentDetail']->SupplierID=='') {	
			$conversion = $_SESSION['PaymentDetail']->Amount;
		}else{
			$conversion = $_SESSION['PaymentDetail']->Amount;
		}

		/* AHORA CAPTURA LAS TRANSACCIONES BANCARIAS */
		//echo "Transtype " . $Transtype;
		
		//se cambio $TipoDeCambioDelDia por $TipoCambioChequera porque se da el caso de que un proveedor en USD
		//realiza un pago a una cuenta en MXN por lo que el pago es en MXN y entonces si la moneda de la cuenta es igual 
		//a la de la compania tipo cambio es 1 aunque la moneda del proveedor sea diferente a la moneda de la compania
		
		//este valor se define arriba pero si no queda definido entonces le asigno el tc del dia 
		if ($TipoCambioChequera=="")
			$TipoCambioChequera = $TipoDeCambioDelDia;
		
		if (($Transtype==22) or ($Transtype==121)) {
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
				'" . $_SESSION['PaymentDetail']->Account . "',
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . (1/$TipoCambioChequera). " , 
				" . 1 . ",
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
								
			$SQL .=	 (-1)*$conversion . ",
			
				'" . $MonedaCuentaCheques . "',
				" . $_POST['tag'] . ",
				'" . $Beneficiario . "',
				'" . $ChequeNum . "'
			)";
			
			/*
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
					cuentadestino,
					bancodestino,
					rfcdestino) ";
				
			$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				" . $_SESSION['PaymentDetail']->Account . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . (1/$TipoCambioChequera). " ,
				" . 1 . ",
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
			
			$SQL .=	 (-1)*$conversion . ",
		
				'" . $MonedaCuentaCheques . "',
				" . $_POST['tag'] . ",
				'" . $Beneficiario . "',
				'" . $ChequeNum . "',
				'" . $_POST['cuentadestino'] . "',
				'" . $_POST['bancodestino'] . "',
				'" . $_POST['rfcdestino'] . "'
			)";*/
			/*if($_SESSION['UserID'] == "admin"){
				echo 'SQL2 <pre>'.$SQL;
			}*/
			$ErrMsg = _('No pude insertar la transaccion bancaria porque');
			$DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);	
		} else {
			$tamount = 0;
			$ttag = "0";
			$tchequeno = "0";
			foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
				$tamount = $tamount + $PaymentItem->Amount;
				$ttag = $PaymentItem->tag;
				$tchequeno = $PaymentItem->cheque;
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
				chequeno) ";
				
			$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				'" . $_SESSION['PaymentDetail']->Account . "',
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . (1/$TipoDeCambioDelDia) . " ,
				" . 1 . ",
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
			$SQL .=	  -$tamount  . ",
				'" . $MonedaCuentaCheques . "',
				" . $ttag . ",
				'" . $Beneficiario . "',
				'" . $tchequeno . "'
			)";
			/*
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
				cuentadestino,
				bancodestino,
				rfcdestino) ";
			
			$SQL= $SQL . "VALUES (" . $TransNo . ",
				" . $Transtype . ",
				" . $_SESSION['PaymentDetail']->Account . ",
				'" . $_SESSION['PaymentDetail']->Narrative . "',
				" . (1/$TipoDeCambioDelDia) . " ,
				" . 1 . ",
				'" . FormatDateForSQL($_SESSION['PaymentDetail']->DatePaid) . "',
				'" . $_SESSION['PaymentDetail']->Paymenttype . "',";
			$SQL .=	  -$tamount  . ",
				'" . $MonedaCuentaCheques . "',
				" . $ttag . ",
				'" . $Beneficiario . "',
				'" . $tchequeno . "',
				'" . $_POST['cuentadestino'] . "',
				'" . $_POST['bancodestino'] . "',
				'" . $_POST['rfcdestino'] . "'
			)"; */
			
			/*if($_SESSION['UserID'] == "admin"){
				echo 'SQL3 <pre>'.$SQL;
			}*/
			//echo "dos -> " .  -$PaymentItem->Amount . "<br>";//

			$ErrMsg = _('No pude insertar la transaccion bancaria porque');
			$DbgMsg = _('No pude insertar la transaccion bancaria utilizando el SQL');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$lasttag=$PaymentItem->tag;
		}
	
		//$SQL = "COMMIT";
		$ErrMsg = _('No pude hacer COMMIT de los cambios porque');
		$DbgMsg = _('El COMMIT a la base de datos fallo');
		//$result= DB_query($SQL,$db,$ErrMsg,$DbgMsg);
		$result = DB_Txn_Commit($db);
	
		prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('ha sido exitosamente procesado'),'success');
		
		$lastSupplier = ($_SESSION['PaymentDetail']->SupplierID);
		$lasttag=$_POST['tag'];
		
		/*
		$liga = GetUrlToPrint($_POST['tag'],$Transtype,$db);
		*/
		
		$tdate = $_SESSION['PaymentDetail']->DatePaid;
		$liga = $rootpath . "/PrintJournal.php?PrintPDF=yes&FromCust=1&ToCust=1&type=".$Transtype."&TransNo=" . $TransNo . "&Tagref=" . $lasttag . "&SuppName=" . $Beneficiario ."&periodo=$PeriodNo&trandate=$tdate". SID;
		$liga2 = $liga;
		
		//echo 'tipo:'.$Transtype;
		unset($_POST['BankAccount']);
		//unset($_POST['DatePaid']);
		unset($_POST['ExRate']);
		unset($_POST['Paymenttype']);
		unset($_POST['Currency']);
		unset($_POST['Narrative']);
		unset($_POST['Amount']);
		unset($_POST['Discount']);
		unset($_SESSION['PaymentDetail']->GLItems);
		unset($_SESSION['PaymentDetail']);
		unset($_SESSION['PaySupCurrency']);
	
		/*Set up a newy in case user wishes to enter another */
		
		/******************************** AGREGUE DE VERSION DE MATERIALES ---desarrollo***** */
		/*Set up a newy in case user wishes to enter another */
		$link="PrintJournalCh.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $Transtype. "&TransNo=" . $TransNo ;
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
		. '" alt="">' . ' ' .'<a  target="_blank" href="'.$link.'">'._('Imprimir Cheque usando formato pre-impreso').'</a>';
		//'<a  target="_blank" href="' . $rootpath . '/PrintCheque_01'.$pdfprefix.'.php?' . SID .'&TransNo='.$TransNo.'&type='.$Transtype.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
		
		if ($_SESSION['PrintJournalCh']!="")
			$liga = '<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
			. '" alt="">' . ' ' .
			'<a  target="_blank" href="' . $rootpath . '/'. $_SESSION['PrintJournalCh'].'?type='.$Transtype.'&TransNo='.$TransNo.'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
		
		echo '<br>'.$liga.'<br>';
		/*********************************************************************************/
		$liga2='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
		. '" alt="">' . ' ' .
		'<a  target="_blank" href="' . $rootpath . '/PrintCheque_01'.$pdfprefix.'.php?' . SID .'&TransNo='.$TransNo.'&type='.$Transtype.'&periodno='.$PeriodNo.'">'. _('Imprimir Cheque con detalle contable usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
		
			
		echo $liga2.'<br>';//
		/*
		$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
		. '" alt="">' . ' ' .
		'<a  target="_blank" href="' . $liga2 .'">'. _('Imprimir Cheque usando formato pre-impreso') . ' (' . _('Laser') . ')' .'</a>';
				
		//echo '<br><a href="' . $rootpath . '/PrintCheque.php?' . SID . '&ChequeNum=' . $ChequeNum .'&TransNo='.$TransNo.'&Currency='.$Currency.'&SuppName='.$SuppName.'" target="_blank">' . _('Imprimir Cheque usando formato pre-impreso') . '</a><br>';
		echo '<br>'.$liga.'<br>';*/
		
		echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Capturar pago contable') . '</a><br>';
		echo '<br><a href="' . $rootpath . '/Payments.php?SupplierID=' . $lastSupplier . '" >' . _('Capturar otro pago a este proveedor') . '</a>';
		
		if(Havepermission($_SESSION['UserID'],31, $db)==1){
			$aplicacionpagos=HavepermissionURL($_SESSION['UserID'],31, $db);
			echo '<br><br><a href="' . $rootpath . '/'.$aplicacionpagos.'?&SupplierID=' . $lastSupplier . '" >' . _('Aplicaci�n de pago a proveedor') . '</a>';
		}

	}else{
		//$ErrMsg = _('La Fecha no puede ser anterior a la actual por que no cuenta con los permisos necesarios');
	}
	include('includes/footer.inc');
	exit;

} elseif (isset($_GET['Delete'])){
	/* User hit delete the receipt entry from the batch */
	$_SESSION['PaymentDetail']->Remove_GLItem($_GET['Delete']);
	
} elseif (isset($_GET['DeleteModifica'])){
	/* User hit delete the receipt entry from the batch */
	foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
		if($PaymentItem->ID==$_GET['DeleteModifica']){
			$_POST['GLNarrative']=$PaymentItem->Narrative;
			$_POST['GLCode']=$PaymentItem->GLCode;
			$_POST['GLManualCode']=$PaymentItem->GLCode;
			if ($_SESSION['PaymentDetail']->SupplierID==''){
				if ($PaymentItem->Amount<0){
					$_POST['GLAmountA']=$PaymentItem->Amount*-1;
				}else{
					$_POST['GLAmountC']=$PaymentItem->Amount;
				}
			}else{
				$_POST['GLAmount']=$PaymentItem->Amount;
			}
			$_POST['cheque']=$PaymentItem->cheque;
			break;
		}
		
	}
	
	$_SESSION['PaymentDetail']->Remove_GLItem($_GET['DeleteModifica']);
	
}elseif (isset($_POST['SearchAccount']) == 'Buscar'){
	$_POST['GLManualCode'] = '';
}elseif (isset($_POST['Process'])){
	//user hit submit a new GL Analysis line into the payment
//        echo '<br>Boton process';
	if($_POST['cheque']!=''){
		$ChequeNoSQL='select * from banktrans where chequeno="'.$_POST['cheque'].'" and tagref = "'.$_POST['tag'].'"';
		$ChequeNoResult=DB_query($ChequeNoSQL, $db);
		$numcheques=DB_num_rows($ChequeNoResult);
	}else{
		$numcheques=0;
	}
//	echo '<br>Cheque:'.$_POST['cheque'];
	if (($_POST['tag'] != '') and ($_POST['tag'] != '0'))
	{
//            echo '<br>Validar tag:'.$_POST['tag'];
		if (($_POST['GLManualCode'] != 0 || $_POST['GLManualCode'] != '') && $_POST['GLCode'] != "0")
		{
//                    echo '<br>Validar GLManualCode y GLCode:'.$_POST['GLManualCode'];
			//$_POST['GLManualCode'] = $_POST['GLCode'];
		
			if ($_POST['GLManualCode']!="" AND !is_numeric($_POST['GLManualCode']))
			{
//                            echo '<br>Validar GLManualCode:'.$_POST['GLManualCode'];
				$SQL = "SELECT * FROM chartpayscheque WHERE accountcode = '" . $_POST['GLManualCode'] . "'";
				$SQL = "select accountname FROM chartmaster WHERE accountcode = '" . $_POST['GLManualCode'] . "'";
				
				$Result=DB_query($SQL,$db);
				
				$legalid=ExtractLegalid($_POST['tag'],$db);
//                                echo '<br>Consulta de codigo contable capturado manualmente:'.$SQL;
				if (DB_num_rows($Result)==0){
					prnMsg( _('El codigo contable capturado manualmente no existe') . ' - ' . _('asi que esta linea de la aplicacion contable no se pudo agregar'),'warn');
					unset($_POST['GLManualCode']);
				} else if ($numcheques!=0 and $_POST['cheque']!=''){
					prnMsg( _('El numero de Cheque/Deposito ya ha sido utilizado') . ' - ' . _('asi que esta linea de la aplicacion contable no se pudo agregar'),'error');
				} else {
					
					$SQL = "select accountname
							FROM chartmaster
							WHERE accountcode='" . $_POST['GLManualCode']."'";
					
					$Result=DB_query($SQL,$db);
					
					$myrow = DB_fetch_array($Result);
					if ($_POST['GLAmountC'] != ""){
						$monto = $_POST['GLAmountC'];	
					}elseif ($_POST['GLAmountA'] != ""){ 
						$monto = -$_POST['GLAmountA'];
					}
					
					
					$_SESSION['PaymentDetail']->add_to_glanalysis($monto,
										$_POST['GLNarrative'],
										$_POST['GLManualCode'],
										$myrow['accountname'],
										$_POST['tag'],
										$_POST['cheque'],
										$_POST['Beneficiary2'],
										$legalid
										);
				}
				
			} 
			else if (DB_num_rows($ChequeNoResult)!=0 and $_POST['cheque']!=''){
					prnMsg( _('El numero de Cheque/Deposito ya ha sido utilizado') . ' - ' . _('asi que esta linea de la aplicacion contable no se pudo agregar'),'error');
			} 
			else 
			{
				$legalid=ExtractLegalid($_POST['tag'],$db);
				$SQL = "select accountname FROM chartmaster WHERE accountcode='" . $_POST['GLCode']."'";
				$Result=DB_query($SQL,$db);
				$myrow=DB_fetch_array($Result);
				$_SESSION['PaymentDetail']->add_to_glanalysis($_POST['GLAmount'],
										$_POST['GLNarrative'],
										$_POST['GLCode'],
										$myrow['accountname'],
										$_POST['tag'],
										$_POST['cheque'],
										$_POST['Beneficiary2'],
										$legalid
										);
			}
		}
		else 
		{
			prnMsg( _('Debe seleccionar una cuenta contable valida...'),'error');
		}
	}else{
		prnMsg( _('Debes de seleccionar una unidad de negocio correcta...'),'error');
	}
//        echo 'Detalles de pagos: ';var_dump($_SESSION['PaymentDetail']);
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
/*-----biviana
if ((isset($_GET['DatePaid'])) and ($_GET['DatePaid'] != "")) {
	$_POST['DatePaid'] = $_GET['DatePaid'];
}elseif (!isset($_POST['DatePaid'])) {
	$_POST['DatePaid'] = '';
}
--------*/

//---biviana----if (isset($_POST['DatePaid']) and ($_POST['DatePaid']=="" OR !Is_Date($_SESSION['PaymentDetail']->DatePaid))){
if (!Is_Date($_SESSION['PaymentDetail']->DatePaid)){
	
	$_SESSION['PaymentDetail']->DatePaid = Date($_SESSION['DefaultDateFormat']);
/*--biviana--  
	$_POST['DatePaid']= Date($_SESSION['DefaultDateFormat']);	
	$_SESSION['PaymentDetail']->DatePaid = $_POST['DatePaid'];
	
*/
	//echo "<br>@entra fecha";
}

if ($_SESSION['PaymentDetail']->Currency=='' AND $_SESSION['PaymentDetail']->SupplierID==''){
	$_SESSION['PaymentDetail']->Currency = $_SESSION['CompanyRecord']['currencydefault'];
	//echo "<br>@currency";
}

if (isset($_POST['BankAccount']) AND $_POST['BankAccount']!='') {
	$SQL = "SELECT bankaccountname
		FROM bankaccounts, chartmaster
		WHERE bankaccounts.accountcode= chartmaster.accountcode
		AND chartmaster.accountcode='" . $_POST['BankAccount']."'";

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


echo "<p><table border='1' cellpadding='2'>";
	
	if ($_SESSION['PaymentDetail']->SupplierID==''){
		/*
		echo '<tr><td>' . _('Moneda') . ':</td><td><select name="Currency">';
		$SQL = 'SELECT currency, currabrev, rate FROM currencies';
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
			echo '</select></td><td><i>' . _('La moneda de la transaccion no tiene que ser la misma que la moneda de la transaccion') . '</i></td></tr>';
		}
		*/
		echo '<input type="hidden" name="Currency" value="'.$_SESSION['CompanyRecord']['currencydefault'].'">';
		
	}else{ /*its a supplier payment so it must be in the suppliers currency */
		echo '<tr><td>' . _('Moneda el Proveedor') . ':</td><td>' . $_SESSION['PaymentDetail']->Currency . '</td></tr>';
		echo '<input type="hidden" name="Currency" value="' . $_SESSION['PaymentDetail']->Currency . '">';
		/*get the default rate from the currency table if it has not been set */
		if (!isset($_POST['ExRate']) OR $_POST['ExRate']==''){
			//$SQL = "SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail']->Currency ."'";
			$SQL = "select rate from tipocambio where fecha <= NOW() and currency = '".$_SESSION['PaymentDetail']->Currency."' order by fecha desc limit 1";
			$Result=DB_query($SQL,$db);
			$myrow=DB_fetch_row($Result);
			$_POST['ExRate']=(1/$myrow[0]); /*desarrollolo cambie para que aparezca en base a dll  13.XX */
		}
	}



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
	$SQL = $SQL .	" WHERE u.tagref = t.tagref  $wcond";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagdescription, t.tagref";
	
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		}
	}
	echo '</select><input style="background-color:orange" type="submit" name="UpdateChequeras" value="' . _('busca cuentas de cheques validas...'). '"></td><tr>';
	// End select tag


	$SQL = 'SELECT bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode
		FROM bankaccounts inner join  chartmaster on bankaccounts.accountcode=chartmaster.accountcode
		inner join  tagsxbankaccounts on bankaccounts.accountcode = tagsxbankaccounts.accountcode
		inner join  sec_unegsxuser on tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
			sec_unegsxuser.userid = "' . $_SESSION['UserID'] . '"
		WHERE tagsxbankaccounts.tagref = "'.$_POST['tag'].'"
		GROUP BY bankaccountname,
		bankaccounts.accountcode,
		bankaccounts.currcode';

	$ErrMsg = _('Las cuentas de cheques no se pudieron recuperar porque');
	$DbgMsg = _('El SQL utilizado para recuperar las cuentas de cheques fue');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Cuenta de Cheques') . ':</td><td><select name="BankAccount">';

	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td></tr>';
		//prnMsg( _('No existen cuentas de cheques definidas aun') . ' <a href="' . $rootpath . '/BankAccounts.php">' . _('configurar cuentas de cheques') . '</a> ' . _('y las cuentas contables que estas afectaran'),'warn');
		//include('includes/footer.inc');
		//exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
			/*list the bank account names */
			if (!isset($_POST['BankAccount']) AND $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
				$_POST['BankAccount']=$myrow['accountcode'];
			}
			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo '<option selected VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			} else {
				echo '<option VALUE="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'];
			}
		}
		echo '</select></td></tr>';
	}
	
	if ($MonedaCuentaCheques == $MonedaProveedor)
		echo '<tr><td>' . _('Moneda Chequera') . ':</td><td>'.$MonedaCuentaCheques.'</td></tr>';
	else 
		echo '<tr style="background-color:orange"><td>' . _('Moneda Chequera') . ':</td><td>'.$MonedaCuentaCheques.'</td></tr>';
		
	if (!isset($_SESSION['SuppTrans']->TranDate)){
	    //$_SESSION['PaymentDetail']->DatePaid=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
	}

	/*--biviana--
	if (isset($_POST['DatePaid'])){
		$_SESSION['PaymentDetail']->DatePaid= $_POST['DatePaid'];
	}*/

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
		echo "</td>";//-----------


/*
	if (($permiso2==1) or ($permiso==1)){
		echo '<td><input type="text" name="DatePaid" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength=10 size=11 onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . $_SESSION['PaymentDetail']->DatePaid . '"></td>
		</tr>';
	}else{
		echo '<td><input type="text" name="DatePaid"  alt="'.$_SESSION['DefaultDateFormat'].'" maxlength=10 size=11 onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" readonly value="' . $_SESSION['PaymentDetail']->DatePaid . '"></td>
		</tr>';
	}*/
	
	

	if (!isset($_POST['ExRate'])){
		$_POST['ExRate']=1;
	}

	if (!isset($_POST['FunctionalExRate'])){
		$_POST['FunctionalExRate']=1;
	}
/*
	if (($_SESSION['PaymentDetail']->AccountCurrency != $_SESSION['PaymentDetail']->Currency) AND (isset($_SESSION['PaymentDetail']->AccountCurrency))){
		if (isset($SuggestedExRate)){
			$SuggestedExRateText = '<b>' . _('Tipo de Cambio Sugerido:') . ' ' . number_format(1/$SuggestedExRate,4) . '</b>';
		} else {
			$SuggestedExRateText = '';
		}
		if ($_POST['ExRate']==1 AND isset($SuggestedExRate)){
			$_POST['ExRate'] = $SuggestedExRate;
		}
		echo '<tr><td>' . _('Tipo de Cambio de Operacion') . ':</td>
			<td><input type="text" name="ExRate" maxlength=10 size=12 value="' . ($TipoDeCambioDelDia) . '"></td>
			<td>' . $SuggestedExRateText . '</td></tr>';
	}
*/

	if ($MonedaCuentaCheques != $_SESSION['CompanyRecord']['currencydefault'] OR $MonedaProveedor != $_SESSION['CompanyRecord']['currencydefault']) {
		echo '<tr><td>' . _('Tipo de Cambio de Operacion') . ':</td>
			<td><input type="text" name="ExRate" maxlength=20 size=20 value="' . ($TipoDeCambioDelDia) . '">&nbsp;&nbsp;&nbsp;' . $SuggestedExRateText . '</td></tr>';
	}

/*
	if (($_SESSION['PaymentDetail']->AccountCurrency!=$_SESSION['CompanyRecord']['currencydefault']) AND (isset($_SESSION['PaymentDetail']->AccountCurrency))){
		if (isset($SuggestedFunctionalExRate)){
			$SuggestedFunctionalExRateText = '<b>' . _('Tipo de Cambio Sugerido:') . ' ' . number_format(1/$SuggestedFunctionalExRate,4) . '</b>';
		} else {
			$SuggestedFunctionalExRateText ='';
		}
		if ($_POST['FunctionalExRate']==1 AND isset($SuggestedFunctionalExRate)){
			$_POST['FunctionalExRate'] = $SuggestedFunctionalExRate;
		}
		echo '<tr><td>' . _('Tipo de Cambio Funcional') . ':</td><td><input type="text" name="FunctionalExRate" maxlength=10 size=12 value="' . $_POST['FunctionalExRate'] . '"></td>
			<td>' . ' ' . $SuggestedFunctionalExRateText . ' <i>' . _('El tipo de cambio entre la moneda del negocio y la moneda de la cuenta de cheques') .  '. 1 ' . $_SESSION['CompanyRecord']['currencydefault'] . ' = ? ' . $_SESSION['PaymentDetail']->AccountCurrency . '</i></td></tr>';
	}
*/

	echo '<tr><td>' . _('Tipo de Pago') . ':</td><td><select name="Paymenttype" onchange="activadestino(this.value);">';
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
	/*
	echo "<tr style='background-color: #F7F2E0;'><td colspan=2>";
	echo "<div name='datosbanco'>Banco destino:<input type='text' name='bancodestino' id='bancodestino' value='' size='30' disabled>&nbsp;&nbsp;&nbsp;Cuenta Destino:<input type='text' name='cuentadestino' id='cuentadestino' value='' size='50' disabled></div>";
	echo "</td></tr>";
*/
	if (!isset($_POST['ChequeNum'])) {
		$_POST['ChequeNum']='';
	}

	echo '<tr><td>' . _('Cheque Numero') . ':</td>
		<td><input type="text" name="ChequeNum" maxlength=8 size=10 value="' . $_POST['ChequeNum'] . '"> ' . _('(si utiliza formas pre-impresas)') . '</td></tr>';

	echo "<tr>
			<td>RFC Beneficiario:</td>
			<td><input type='text' name='rfcdestino' value='".$_SESSION["PaymentDetail"]->TaxId."'></td>
		</tr>";
	
	if (!isset($_POST['Narrative'])) {
		$_POST['Narrative']='';
	}

	if (!isset($_POST['Beneficiario'])) {
		$_POST['Beneficiario']=$_SESSION['PaymentDetail']->SuppName;
	}

	echo '<tr><td>' . _('Beneficiario') . ':</td>
		<td colspan=2><textarea name="Beneficiario" rows="1" cols="62" size=82>' . $_POST['Beneficiario'] . '</textarea></td></tr>';
	
	echo '<tr><td>' . _('Referencia / Concepto') . ':</td>
		<td colspan=2><input type="text" name="Narrative" maxlength=200 size=82 value="' . $_POST['Narrative'] . '">  ' . _('(Max. 200 caracteres de longitud)') . '</td></tr>';
			
			
			
	echo '<tr><td colspan=3><div class="centre">
		<input type="hidden" name="SupplierID" value="'.$_POST['SupplierID'].'">
		<input type="submit" name="UpdateHeader" value="' . _('Actualizar'). '"></td></tr>';

echo '</table><br>';


if ($_SESSION['CompanyRecord']['gllink_creditors']==1 AND $_SESSION['PaymentDetail']->SupplierID==''){
	/* Set upthe form for the transaction entry for a GL Payment Analysis item */

	echo '<table class="table1" border="1"><tr>
		<th>' . _('Numero de Cheque').'</th>
		<th>' . _('Monto') . ' (' . $_SESSION['PaymentDetail']->Currency . ')</th>
		<th>' . _('Cuenta Contable') . '</th>
		<th>' . _('Concepto') . '</th>
		<th>' . _('Unidad de Negocio') . '</th>
		<th colspan=2 align=center >' . _('Acciones') . '</th>
		</tr>';

	$PaymentTotal = 0;
	$legalidant = 0;
	$flagdiferenterazonsocial = false;
	foreach ($_SESSION['PaymentDetail']->GLItems as $PaymentItem) {
		if ($legalidant == 0){
			$legalidant = $PaymentItem->legalid;
		}
		
		if ($legalidant != $PaymentItem->legalid){
			$flagdiferenterazonsocial = true;
		}
		
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
			<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Narrative=' . $_POST['Narrative'] . '&Beneficiario=' . $_POST['Beneficiario'] . '&ChequeNum=' . $_POST['ChequeNum'] . '&Paymenttype=' . $_POST['Paymenttype'] . '&FunctionalExRate=' . $_POST['FunctionalExRate'] . '&ExRate=' . $_POST['ExRate'] . '&Currency=' . $_POST['Currency'] . '&BankAccount=' . $_POST['BankAccount'] . '&DatePaid=' . $_SESSION['PaymentDetail']->DatePaid . '&tag=' . $_POST['tag'] . '&Delete=' . $PaymentItem->ID . '&ToDia=' . $ToDia . '&ToMes=' . $ToMes . '&ToYear=' . $ToYear . '">' . _('Eliminar') . '</a></td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Narrative=' . $_POST['Narrative'] . '&Beneficiario=' . $_POST['Beneficiario'] . '&ChequeNum=' . $_POST['ChequeNum'] . '&Paymenttype=' . $_POST['Paymenttype'] . '&FunctionalExRate=' . $_POST['FunctionalExRate'] . '&ExRate=' . $_POST['ExRate'] . '&Currency=' . $_POST['Currency'] . '&BankAccount=' . $_POST['BankAccount'] . '&DatePaid=' . $_SESSION['PaymentDetail']->DatePaid . '&tag=' . $_POST['tag'] . '&DeleteModifica=' . $PaymentItem->ID . '&ToDia=' . $ToDia . '&ToMes=' . $ToMes . '&ToYear=' . $ToYear . '">' . _('Modificar') . '</a></td>
			</tr>';
		$PaymentTotal += $PaymentItem->Amount;
		
		
	}
	echo '<tr><td></td><td align=right><b>' . number_format($PaymentTotal,2) . '</b></td><td></td><td></td><td></td></tr></table>';


	echo '<br><font size=3 color=BLUE><div class="centre">' . _('Captura de Analisis de Pago Contable') . '</div></font><br><table>';

	/*now set up a GLCode field to select from avaialble GL accounts */
	if (isset($_POST['GLManualCode'])) {
		echo '<tr><td>' . _('Cuenta Contable (Manual)') . ':</td>
			<td><input type=Text Name="GLManualCode" Maxlength=12 size=12 onChange="return inArray(this, this.value, GLCode.options,'.
		"'".'El codigo de cuenta '."'".'+ this.value+ '."'".' no existe'."'".')"' .
			' onKeyPress="return restrictToNumbers(this, event)" VALUE='. $_POST['GLManualCode'] .'  ></td></tr>';
	} else {
	
		echo '<tr><td>' . _('Cuenta Contable (Manual)') . ':</td>
			<td><input type=Text Name="GLManualCode" Maxlength=12 size=12 onChange="return inArray(this, this.value, GLCode.options,'.
		"'".'El codigo de cuenta '."'".'+ this.value+ '."'".' no existe'."'".')"' .
			' onKeyPress="return restrictToNumbers(this, event)"></td></tr>';		
	}
	
	/* SECCION DE BUSQUEDA DE CUENTAS CONTABLES */
	echo '<tr><td align=right><b>Busca Cta x Nombre:</b></td><td><input
		type=Text Name="GLManualSearch" Maxlength=40 size=40 VALUE='. $_POST['GLManualSearch'] .'  >
		<input type=submit name="SearchAccount" value="'._('Buscar').'"></td>';
	echo '</tr>';

	
	/********************************************/
	
	echo '<tr><td>' . _('Selecciona la Cuenta ') . ':</td>
		<td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';

	$SQL = 'SELECT accountcode,
			 accountname, group_ as padre
			FROM chartmaster
			WHERE accountname like "%'.$_POST['GLManualSearch'].'%"
			ORDER BY group_, accountcode';			

	$result=DB_query($SQL,$db);
	$cambioGrupo= "";
	
	if (DB_num_rows($result)==0){
		echo '</select></td></tr>';
		prnMsg(_('No se han configurado las cuentas contables todavia') . ' - ' . _('pagos no se pueden analizar contra cuentas si no estan dadas de alta'),'error');
	} else {
		while ($myrow=DB_fetch_array($result))
		{
			if ($cambioGrupo <> $myrow['padre']) {
				echo '<option  value="0">****** ' .$myrow['padre'] . '</option>';
			}
			
			if (isset($_POST['GLCode']) and $_POST['GLCode']==$myrow["accountcode"]){
				echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
			} else {
				echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
			}
			
			$cambioGrupo= $myrow['padre'];
		}
		echo '</select></td></tr>';
	}
	
	if (isset($_POST['cheque'])) {
		echo '<tr ><td><!--'. _('Numero de Cheque/Deposito') .'--></td><td><input type="hidden" name="cheque" value="'.$_POST['cheque'].'" Maxlength=12 size=12></td></tr>';
	}else{
		echo '<tr><td><!--'. _('Numero de Cheque/Deposito') .'--></td><td><input type="hidden" name="cheque" Maxlength=12 size=12></td></tr>';
	}
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
		echo '<tr>';
			echo '<td>' . _('Monto') . ' (' . $MonedaCuentaCheques . '):</td>';
			//echo '<td><input type=Text Name="GLAmount" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)"  onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')" VALUE=' . $_POST['GLAmount'] . '></td>';
			echo '<td><input type=Text Name="GLAmount" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)"  onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')" VALUE=""></td>';
		echo '</tr>';		
	} else {
		echo '<tr style="background-color:#A0F0A0">';
			echo '<td>' . _('Monto Cargo') . ' (' . $MonedaCuentaCheques . '):</td>';
			//echo '<td><input type=Text Name="GLAmountC" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)" value="'.$_POST['GLAmountC'].'" onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')"></td>';
			echo '<td><input type=Text Name="GLAmountC" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)" value="" onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')"></td>';
		echo '</tr>';
		echo '<tr style="background-color:#909090">';
			echo '<td>' . _('Monto Abono') . ' (' . $MonedaCuentaCheques . '):</td>';
			echo '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			//echo '<input type=Text Name="GLAmountA" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)" value="'.$_POST['GLAmountA'].'" onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')"></td>';
			echo '<input type=Text Name="GLAmountA" Maxlength=12 size=12 onKeyPress="return restrictToNumbers(this, event)" value="" onChange="numberFormat(this,2)" onFocus="return setTextAlign(this, '."'".'right'."'".')"></td>';
		echo '</tr>';
	}	

	echo '</table>';
	echo '<div class="centre"><input type=submit name="Process" value="' . _('Procesar') . '"><input type=submit name="Cancel" value="' . _('Cancelar') . '"></div>';

} else {
/*a supplier is selected or the GL link is not active then set out
the fields for entry of receipt amt and disc */

	echo "<table border='1'>";
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
	

	echo '<tr><td>' . _('IVA ') . ' ' . $MonedaCuentaCheques . ':</td>
		<td><input type="text" name="mntIVA" maxlength=12 size=13 value="0">'._('*solo en caso de ser diferente la tasa que la seleccion de arriba').'</td></tr>';
	
	echo '<tr><td>' . _('Monto Total del Pago ') . ' ' . $MonedaCuentaCheques . ':</td>
		<td><input type="text" name="Amount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Amount . '>'._('*es el monto al que saldria el cheque, los descuentos y <br>retenciones se suman a este monto !!!. El cargo a Proveedores seria la suma <br> de todos los montos en esta pagina a excepcion del IVA.').'</td></tr>';

	if (isset($_SESSION['PaymentDetail']->SupplierID)){ /*So it is a supplier payment so show the discount entry item */
		echo '<tr><td>' . _('Monto de Descuento') .  ' ' . $MonedaCuentaCheques . ':</td>
			<td colspan=2><input type="text" name="Discount" maxlength=12 size=13 value=' . $_SESSION['PaymentDetail']->Discount . '></td>
			</tr>';
		echo '<input type="hidden" name="SuppName" value="' . $_SESSION['PaymentDetail']->SuppName . '">';
	} else {
		echo '<input type="hidden" name="discount" Value=0>';
	}

	echo '<tr><td>' . _('Retencion IVA HONORARIOS')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionIVA" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion ISR HONORARIOS')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionISR" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion IVA Arrendamiento ')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionxIVAArrendamiento" maxlength=12 size=13 value="0"></td></tr>';
		  
	echo '<tr><td>' . _('Retencion ISR Arrendamiento ')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionxArrendamiento" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retenciones x IVA Comisiones')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionxComisiones" maxlength=12 size=13 value="0"></td></tr>';
	
	//Ganancia o Perdida Cambiaria
	/*
	echo '<tr><td>' . _('Ganancia o Perdida Cambiaria')  . ' ' . $_SESSION['PaymentDetail']->AccountCurrency . ':</td>
		  <td><input type="text" name="GananciaPerdidaCambiaria" maxlength=12 size=13 value="0"></td></tr>';
	*/
	
	echo '<tr><td>' . _('Retencion x IVA Fletes ')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionxFletes" maxlength=12 size=13 value="0"></td></tr>';

	echo '<tr><td>' . _('Retencion x Impuesto Cedular ')  . ' ' . $MonedaCuentaCheques . ':</td>
		  <td><input type="text" name="RetencionxCedular" maxlength=12 size=13 value="0"></td></tr>';

	echo '</table>';

}
if ($_SESSION['PaymentDetail']->SupplierID != '') {
	echo "<br><input type='checkbox' name='anticipo' value='anticipo'>GENERAR ANTICIPO";
}
if ($flagdiferenterazonsocial == true){
	echo "<br><br>" . prnMsg(_('Los movimientos de la poliza deben ser de una sola razon social'),'warn');;
}else{
	echo '<br><br><input type=submit name="CommitBatch" value="' . _('Aceptar y Procesar el Pago') . '">';
}

echo '</form>';

include('includes/footer.inc');
?>

<script type="text/javascript">

function activadestino(valor) {
	return;
	
	var banco= document.getElementById("bancodestino");
	var cuenta= document.getElementById("cuentadestino");

	var encontro = valor.indexOf("Transfer");
	var encontro2= valor.indexOf("TRANSFER");
	
	if (encontro>=0 || encontro2 >= 0) {
		banco.disabled= false;
		cuenta.disabled= false;
		banco.focus();
	} else {
		banco.disabled= true;
		cuenta.disabled= true;
	}
}

</script>


