<?php

/* $Revision: 1.54 $ */
/*cambios

//** MODIFICADO POR: Desarrollador
//** FECHA: 25/ENE/2011
//** CAMBIOS:
//** 1.- AGREGUE CAMPO DE ShowAllProductsForSales PARA MOSTRAR PRODUCTOS EN PÉDIDOS DE VENTA
//** FIN DE CAMBIOS

$PageSecurity =15;

*/
include('includes/session.inc');

$title = _('Configuracion General Del Sistema');

include('includes/header.inc');
$funcion=88;
include('includes/SecurityFunctions.inc');

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	/*
		Note: the X_ in the POST variables, the reason for this is to overcome globals=on replacing
		the actial system/overidden variables.
	*/
	if (strlen($_POST['X_PastDueDays1']) > 3 || !is_numeric($_POST['X_PastDueDays1']) ) {
		$InputError = 1;
		prnMsg(_('First overdue deadline days must be a number'),'error');
	} elseif (strlen($_POST['X_PastDueDays2'])  > 3 || !is_numeric($_POST['X_PastDueDays2']) ) {
		$InputError = 1;
		prnMsg(_('Second overdue deadline days must be a number'),'error');
	} elseif (strlen($_POST['X_DefaultCreditLimit']) > 12 || !is_numeric($_POST['X_DefaultCreditLimit']) ) {
		$InputError = 1;
		prnMsg(_('Default Credit Limit must be a number'),'error');
	} elseif (strstr($_POST['X_RomalpaClause'], "'") || strlen($_POST['X_RomalpaClause']) > 5000) {
		$InputError = 1;
		prnMsg(_('The Romalpa Clause may not contain single quotes and may not be longer than 5000 chars'),'error');
	} elseif (strlen($_POST['X_QuickEntries']) > 2 || !is_numeric($_POST['X_QuickEntries']) ||
		$_POST['X_QuickEntries'] < 1 || $_POST['X_QuickEntries'] > 99 ) {
		$InputError = 1;
		prnMsg(_('No less than 1 and more than 99 Quick entries allowed'),'error');
	} elseif (strlen($_POST['X_FreightChargeAppliesIfLessThan']) > 12 || !is_numeric($_POST['X_FreightChargeAppliesIfLessThan']) ) {
		$InputError = 1;
		prnMsg(_('Freight Charge Applies If Less Than must be a number'),'error');
	} elseif (strlen($_POST['X_NumberOfPeriodsOfStockUsage']) > 2 || !is_numeric($_POST['X_NumberOfPeriodsOfStockUsage']) ||
		$_POST['X_NumberOfPeriodsOfStockUsage'] < 1 || $_POST['X_NumberOfPeriodsOfStockUsage'] > 12 ) {
		$InputError = 1;
		prnMsg(_('Financial period per year must be a number between 1 and 12'),'error');
	} elseif (strlen($_POST['X_TaxAuthorityReferenceName']) >25) {
		$InputError = 1;
		prnMsg(_('The Tax Authority Reference Name must be 25 characters or less long'),'error');
	} elseif (strlen($_POST['X_OverChargeProportion']) > 3 || !is_numeric($_POST['X_OverChargeProportion']) ||
		$_POST['X_OverChargeProportion'] < 0 || $_POST['X_OverChargeProportion'] > 100 ) {
		$InputError = 1;
		prnMsg(_('Over Charge Proportion must be a percentage'),'error');
	} elseif (strlen($_POST['X_OverReceiveProportion']) > 3 || !is_numeric($_POST['X_OverReceiveProportion']) ||
		$_POST['X_OverReceiveProportion'] < 0 || $_POST['X_OverReceiveProportion'] > 100 ) {
		$InputError = 1;
		prnMsg(_('Over Receive Proportion must be a percentage'),'error');
	} elseif (strlen($_POST['X_PageLength']) > 3 || !is_numeric($_POST['X_PageLength']) ||
		$_POST['X_PageLength'] < 1 ) {
		$InputError = 1;
		prnMsg(_('Lines per page must be greater than 1'),'error');
	} elseif (strlen($_POST['X_MonthsAuditTrail']) > 2 || !is_numeric($_POST['X_MonthsAuditTrail']) ||
		$_POST['X_MonthsAuditTrail'] < 0 ) {
		$InputError = 1;
		prnMsg(_('The number of months of audit trail to keep must be zero or a positive number less than 100 months'),'error');
	}elseif (strlen($_POST['X_DefaultTaxCategory']) > 1 || !is_numeric($_POST['X_DefaultTaxCategory']) ||
		$_POST['X_DefaultTaxCategory'] < 1 ) {
		$InputError = 1;
		prnMsg(_('DefaultTaxCategory must be between 1 and 9'),'error');
	} elseif (strlen($_POST['X_DefaultDisplayRecordsMax']) > 3 || !is_numeric($_POST['X_DefaultDisplayRecordsMax']) ||
		$_POST['X_DefaultDisplayRecordsMax'] < 1 ) {
		$InputError = 1;
		prnMsg(_('Default maximum number of records to display must be between 1 and 500'),'error');
	}elseif (strlen($_POST['X_MaxImageSize']) > 3 || !is_numeric($_POST['X_MaxImageSize']) ||
		$_POST['X_MaxImageSize'] < 1 ) {
		$InputError = 1;
		prnMsg(_('The maximum size of item image files must be between 50 and 500 (NB this figure refers to KB)'),'error');
	}elseif (!IsEmailAddress($_POST['X_FactoryManagerEmail'])){
		$InputError = 1;
		prnMsg(_('The Factory Manager Email address does not appear to be valid'),'error');
	}

	if ($InputError !=1){

		$sql = array();

		if ($_SESSION['DefaultDateFormat'] != $_POST['X_DefaultDateFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultDateFormat']."' WHERE confname = 'DefaultDateFormat'";
		}
		if ($_SESSION['DefaultTheme'] != $_POST['X_DefaultTheme'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultTheme']."' WHERE confname = 'DefaultTheme'";
		}
		if ($_SESSION['PastDueDays1'] != $_POST['X_PastDueDays1'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PastDueDays1']."' WHERE confname = 'PastDueDays1'";
		}
		if ($_SESSION['PastDueDays2'] != $_POST['X_PastDueDays2'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PastDueDays2']."' WHERE confname = 'PastDueDays2'";
		}
		if ($_SESSION['DefaultCreditLimit'] != $_POST['X_DefaultCreditLimit'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultCreditLimit']."' WHERE confname = 'DefaultCreditLimit'";
		}
		
		if ($_SESSION['Show_Settled_LastMonth'] != $_POST['X_Show_Settled_LastMonth'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Show_Settled_LastMonth']."' WHERE confname = 'Show_Settled_LastMonth'";
		}
		if ($_SESSION['RomalpaClause'] != $_POST['X_RomalpaClause'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_RomalpaClause'] . "' WHERE confname = 'RomalpaClause'";
		}
		if ($_SESSION['QuickEntries'] != $_POST['X_QuickEntries'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_QuickEntries']."' WHERE confname = 'QuickEntries'";
		}
		if ($_SESSION['DispatchCutOffTime'] != $_POST['X_DispatchCutOffTime'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DispatchCutOffTime']."' WHERE confname = 'DispatchCutOffTime'";
		}
		if ($_SESSION['AllowSalesCost'] != $_POST['X_AllowSalesCost'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_AllowSalesCost']."' WHERE confname = 'AllowSalesCost'";
		}
		if ($_SESSION['ProhibitSalesBelowCost'] != $_POST['X_ProhibitSalesBelowCost'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_ProhibitSalesBelowCost']."' WHERE confname = 'ProhibitSalesBelowCost'";
		}
		if ($_SESSION['AllowSalesOfZeroCostItems'] != $_POST['X_AllowSalesOfZeroCostItems'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_AllowSalesOfZeroCostItems']."' WHERE confname = 'AllowSalesOfZeroCostItems'";
		}
		if ($_SESSION['CreditingControlledItems_MustExist'] != $_POST['X_CreditingControlledItems_MustExist'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_CreditingControlledItems_MustExist']."' WHERE confname = 'CreditingControlledItems_MustExist'";
		}
		
		if ($_SESSION['AplicaClienteFI'] != $_POST['X_AplicaClienteFI']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AplicaClienteFI'] . "' WHERE confname='AplicaClienteFI'";
		}
		
		if ($_SESSION['ClaveClienteUsoInterno'] != $_POST['X_ClaveClienteUsoInterno']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ClaveClienteUsoInterno'] . "' WHERE confname='ClaveClienteUsoInterno'";
		}
		
		if ($_SESSION['DefaultPriceList'] != $_POST['X_DefaultPriceList'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultPriceList']."' WHERE confname = 'DefaultPriceList'";
		}
		if ($_SESSION['Default_Shipper'] != $_POST['X_Default_Shipper'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Default_Shipper']."' WHERE confname = 'Default_Shipper'";
		}
		if ($_SESSION['DoFreightCalc'] != $_POST['X_DoFreightCalc'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DoFreightCalc']."' WHERE confname = 'DoFreightCalc'";
		}
		if ($_SESSION['FreightChargeAppliesIfLessThan'] != $_POST['X_FreightChargeAppliesIfLessThan'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_FreightChargeAppliesIfLessThan']."' WHERE confname = 'FreightChargeAppliesIfLessThan'";
		}
		if ($_SESSION['DefaultTaxCategory'] != $_POST['X_DefaultTaxCategory'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultTaxCategory']."' WHERE confname = 'DefaultTaxCategory'";
		}
		if ($_SESSION['TaxAuthorityReferenceName'] != $_POST['X_TaxAuthorityReferenceName'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_TaxAuthorityReferenceName'] . "' WHERE confname = 'TaxAuthorityReferenceName'";
		}
		if ($_SESSION['CountryOfOperation'] != $_POST['X_CountryOfOperation'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_CountryOfOperation'] ."' WHERE confname = 'CountryOfOperation'";
		}
		if ($_SESSION['NumberOfPeriodsOfStockUsage'] != $_POST['X_NumberOfPeriodsOfStockUsage'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_NumberOfPeriodsOfStockUsage']."' WHERE confname = 'NumberOfPeriodsOfStockUsage'";
		}
		if ($_SESSION['Check_Qty_Charged_vs_Del_Qty'] != $_POST['X_Check_Qty_Charged_vs_Del_Qty'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Check_Qty_Charged_vs_Del_Qty']."' WHERE confname = 'Check_Qty_Charged_vs_Del_Qty'";
		}
		if ($_SESSION['Check_Price_Charged_vs_Order_Price'] != $_POST['X_Check_Price_Charged_vs_Order_Price'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Check_Price_Charged_vs_Order_Price']."' WHERE confname = 'Check_Price_Charged_vs_Order_Price'";
		}
		if ($_SESSION['OverChargeProportion'] != $_POST['X_OverChargeProportion'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_OverChargeProportion']."' WHERE confname = 'OverChargeProportion'";
		}
		if ($_SESSION['OverReceiveProportion'] != $_POST['X_OverReceiveProportion'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_OverReceiveProportion']."' WHERE confname = 'OverReceiveProportion'";
		}
		if ($_SESSION['PO_AllowSameItemMultipleTimes'] != $_POST['X_PO_AllowSameItemMultipleTimes'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PO_AllowSameItemMultipleTimes']."' WHERE confname = 'PO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['SO_AllowSameItemMultipleTimes'] != $_POST['X_SO_AllowSameItemMultipleTimes'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_SO_AllowSameItemMultipleTimes']."' WHERE confname = 'SO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['YearEnd'] != $_POST['X_YearEnd'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_YearEnd']."' WHERE confname = 'YearEnd'";
		}
		if ($_SESSION['PageLength'] != $_POST['X_PageLength'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PageLength']."' WHERE confname = 'PageLength'";
		}
		if ($_SESSION['DefaultDisplayRecordsMax'] != $_POST['X_DefaultDisplayRecordsMax'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultDisplayRecordsMax']."' WHERE confname = 'DefaultDisplayRecordsMax'";
		}
		if ($_SESSION['MaxImageSize'] != $_POST['X_MaxImageSize'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_MaxImageSize']."' WHERE confname = 'MaxImageSize'";
		}
		if ($_SESSION['part_pics_dir'] != $_POST['X_part_pics_dir'] ) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_part_pics_dir']."' WHERE confname = 'part_pics_dir'";
		}
		if ($_SESSION['reports_dir'] != $_POST['X_reports_dir'] ) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_reports_dir']."' WHERE confname = 'reports_dir'";
		}
		if ($_SESSION['AutoDebtorNo'] != $_POST['X_AutoDebtorNo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_AutoDebtorNo'])."' WHERE confname = 'AutoDebtorNo'";
		}
		if ($_SESSION['HTTPS_Only'] != $_POST['X_HTTPS_Only'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_HTTPS_Only'])."' WHERE confname = 'HTTPS_Only'";
		}
		if ($_SESSION['DB_Maintenance'] != $_POST['X_DB_Maintenance'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_DB_Maintenance'])."' WHERE confname = 'DB_Maintenance'";
		}
		if ($_SESSION['DefaultBlindPackNote'] != $_POST['X_DefaultBlindPackNote'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_DefaultBlindPackNote'])."' WHERE confname = 'DefaultBlindPackNote'";
		}
		if ($_SESSION['PackNoteFormat'] != $_POST['X_PackNoteFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_PackNoteFormat'])."' WHERE confname = 'PackNoteFormat'";
		}
		if ($_SESSION['CheckCreditLimits'] != $_POST['X_CheckCreditLimits'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_CheckCreditLimits'])."' WHERE confname = 'CheckCreditLimits'";
		}
		if ($_SESSION['WikiApp'] != $_POST['X_WikiApp'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_WikiApp']."' WHERE confname = 'WikiApp'";
		}
		if ($_SESSION['WikiPath'] != $_POST['X_WikiPath'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_WikiPath']."' WHERE confname = 'WikiPath'";
		}
		if ($_SESSION['ProhibitJournalsToControlAccounts'] != $_POST['X_ProhibitJournalsToControlAccounts'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_ProhibitJournalsToControlAccounts']."' WHERE confname = 'ProhibitJournalsToControlAccounts'";
		}
		if ($_SESSION['InvoicePortraitFormat'] != $_POST['X_InvoicePortraitFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_InvoicePortraitFormat']."' WHERE confname = 'InvoicePortraitFormat'";
		}
		if ($_SESSION['AllowOrderLineItemNarrative'] != $_POST['X_AllowOrderLineItemNarrative'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_AllowOrderLineItemNarrative']."' WHERE confname = 'AllowOrderLineItemNarrative'";
		}
		if ($_SESSION['geocode_integration'] != $_POST['X_geocode_integration'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_geocode_integration']."' WHERE confname = 'geocode_integration'";
		}
		if ($_SESSION['Extended_SupplierInfo'] != $_POST['X_Extended_SupplierInfo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_Extended_SupplierInfo']."' WHERE confname = 'Extended_SupplierInfo'";
		}
		if ($_SESSION['Extended_CustomerInfo'] != $_POST['X_Extended_CustomerInfo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_Extended_CustomerInfo']."' WHERE confname = 'Extended_CustomerInfo'";
		}
		if ($_SESSION['ProhibitPostingsBefore'] != $_POST['X_ProhibitPostingsBefore'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ProhibitPostingsBefore']."' WHERE confname = 'ProhibitPostingsBefore'";
		}
		if ($_SESSION['WeightedAverageCosting'] != $_POST['X_WeightedAverageCosting'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WeightedAverageCosting']."' WHERE confname = 'WeightedAverageCosting'";
		}
		if ($_SESSION['AutoIssue'] != $_POST['X_AutoIssue']){
			$sql[] = 'UPDATE config SET confvalue=' . $_POST['X_AutoIssue'] . " WHERE confname='AutoIssue'";
		}
		if ($_SESSION['ProhibitNegativeStock'] != $_POST['X_ProhibitNegativeStock']){
			$sql[] = 'UPDATE config SET confvalue=' . $_POST['X_ProhibitNegativeStock'] . " WHERE confname='ProhibitNegativeStock'";
		}
		if ($_SESSION['MonthsAuditTrail'] != $_POST['X_MonthsAuditTrail']){
			$sql[] = 'UPDATE config SET confvalue=' . $_POST['X_MonthsAuditTrail'] . " WHERE confname='MonthsAuditTrail'";
		}
		if ($_SESSION['ShowAllProductsForSales'] != $_POST['X_ShowAllProductsForSales']){
			$sql[] = 'UPDATE config SET confvalue=' . $_POST['X_ShowAllProductsForSales'] . " WHERE confname='ShowAllProductsForSales'";
		}
		
		if ($_SESSION['UpdateCurrencyRatesDaily'] != $_POST['X_UpdateCurrencyRatesDaily']){
			if ($_POST['X_UpdateCurrencyRatesDaily']=='Auto'){
				$sql[] = "UPDATE config SET confvalue='" . Date('Y-m-d',mktime(0,0,0,Date('m'),Date('d')-1,Date('Y'))) . "' WHERE confname='UpdateCurrencyRatesDaily'";
			} else {
				$sql[] = "UPDATE config SET confvalue='0' WHERE confname='UpdateCurrencyRatesDaily'";
			}
		}
		if ($_SESSION['FactoryManagerEmail'] != $_POST['X_FactoryManagerEmail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FactoryManagerEmail'] . "' WHERE confname='FactoryManagerEmail'";
			//echo "UPDATE config SET confvalue='" . $_POST['X_FactoryManagerEmail'] . "' WHERE confname='FactoryManagerEmail'";
			
		}
		if ($_SESSION['AutoCreateWOs'] != $_POST['X_AutoCreateWOs']){
			$sql[] = 'UPDATE config SET confvalue=' . $_POST['X_AutoCreateWOs'] . " WHERE confname='AutoCreateWOs'";
		}
		if ($_SESSION['DefaultFactoryLocation'] != $_POST['X_DefaultFactoryLocation']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultFactoryLocation'] . "' WHERE confname='DefaultFactoryLocation'";
		}
		if ($_SESSION['DefineControlledOnWOEntry'] != $_POST['X_DefineControlledOnWOEntry']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefineControlledOnWOEntry'] . "' WHERE confname='DefineControlledOnWOEntry'";
		}
		
		if ($_SESSION['ReversePurchOrders'] != $_POST['X_ReversePurchOrders']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ReversePurchOrders'] . "' WHERE confname='ReversePurchOrders'";
		}
		if ($_SESSION['InvoiceCash'] != $_POST['X_InvoiceCash']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_InvoiceCash'] . "' WHERE confname='InvoiceCash'";
		}
		if ($_SESSION['InvoiceCash'] != $_POST['X_ForzarCapturaRFC']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_InvoiceCash'] . "' WHERE confname='InvoiceCash'";
		}
		if ($_SESSION['LabelText1'] != $_POST['LabelText1']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['LabelText1'] . "' WHERE confname='LabelText1'";
		}
		if ($_SESSION['LabelText2'] != $_POST['LabelText2']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['LabelText2'] . "' WHERE confname='LabelText2'";
		}
		if ($_SESSION['LabelText3'] != $_POST['LabelText3']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['LabelText3'] . "' WHERE confname='LabelText3'";
		}
		/**campos para mostrar en facturacion electronica**/
		if ($_SESSION['Showdiscount1'] != $_POST['X_Showdiscount1']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_Showdiscount1'] . "' WHERE confname='Showdiscount1'";
		}
		if ($_SESSION['Showdiscount2'] != $_POST['X_Showdiscount2']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_Showdiscount2'] . "' WHERE confname='Showdiscount2'";
		}
		if ($_SESSION['ShowWorkers'] != $_POST['X_ShowWorkers']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowWorkers'] . "' WHERE confname='ShowWorkers'";
		}
		if ($_SESSION['ShowPriceList'] != $_POST['X_ShowPriceList']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowPriceList'] . "' WHERE confname='ShowPriceList'";
		}
		if ($_SESSION['ShowLabelText1'] != $_POST['X_ShowLabelText1']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowLabelText1'] . "' WHERE confname='ShowLabelText1'";
		}
		if ($_SESSION['ShowLabelText2'] != $_POST['X_ShowLabelText2']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowLabelText2'] . "' WHERE confname='ShowLabelText2'";
		}		
		if ($_SESSION['ShowLabelText3'] != $_POST['X_ShowLabelText3']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowLabelText3'] . "' WHERE confname='ShowLabelText3'";
		}
		if ($_SESSION['ShowSello'] != $_POST['']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST[''] . "' WHERE confname='ShowSello'";
		}
		
		if ($_SESSION['ShowLocation'] != $_POST['X_ShowLocation']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowLocation'] . "' WHERE confname='ShowLocation'";
		}
		if ($_SESSION['PriceLess'] != $_POST['X_PriceLess']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_PriceLess'] . "' WHERE confname='PriceLess'";
		}
		if ($_SESSION['ShowAllSalesman'] != $_POST['X_ShowAllSalesman']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ShowAllSalesman'] . "' WHERE confname='ShowAllSalesman'";
		}
		
		if ($_SESSION['ProhibitPurchD'] != $_POST['X_ProhibitPurchD']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ProhibitPurchD'] . "' WHERE confname='ProhibitPurchD'";
		}
		
		//************************************INICIO**************************************
		//***********************AGREGAR CAMPO DE defaulttaxforadvances*******************
		//********************************iva de anticipos********************************
		
		if ($_SESSION['defaulttaxforadvances'] != $_POST['defaulttaxforadvances']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['defaulttaxforadvances'] . "' WHERE confname='defaulttaxforadvances'";
		}
		
		//************************************FIN*****************************************
		//***********************AGREGAR CAMPO DE defaulttaxforadvances*******************
		//********************************iva de anticipos********************************
		
		if ($_SESSION['TasaIETU'] != $_POST['tasaietu']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['tasaietu'] . "' WHERE confname='TasaIETU'";
		}

		/*INICIO** Interes Moratorio**
		*/
		if ($_SESSION['InteresMoratorio'] != $_POST['porcmoratorios']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['porcmoratorios'] . "' WHERE confname='InteresMoratorio'";
		}
		/*FIN** **
		*/
		
		/*INICIO** Periodo de Gracia**
		*/
		if ($_SESSION['PeriodoGracia'] != $_POST['peridogracia']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['peridogracia'] . "' WHERE confname='PeriodoGracia'";
		}
		/*FIN** **
		*/
		
		/*INICIO** Aplicar o no cargo a interes moratorio**
		*/
		if ($_SESSION['CargoInteresMoratorio'] != $_POST['cargointeresmoratorio']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['cargointeresmoratorio'] . "' WHERE confname='CargoInteresMoratorio'";
		}
		/*FIN** **
		*/
		
		/*INICIO**Capturar o no cobrador**
		*/
		if ($_SESSION['CapturarCobrador'] != $_POST['capturarcobrador']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['capturarcobrador'] . "' WHERE confname='CapturarCobrador'";
		}
		/*FIN** **
		*/
		
		/*INICIO**Validadar Corte de Caja Dia anterior**
		*/
		if ($_SESSION['ValidarCorteCaja'] != $_POST['ValidarCorteCaja']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['ValidarCorteCaja'] . "' WHERE confname='ValidarCorteCaja'";
		}
		/*FIN** **
		*/
		
		
		/*INICIO**Extraer si o no Numero de Pedido**
		*/
		if ($_SESSION['ExtractOrderNumber'] != $_POST['extractordernumber']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['extractordernumber'] . "' WHERE confname='ExtractOrderNumber'";
		}
		/*FIN** **
		*/
		/*INICIO**Seleccionar usuario para asignación de tarea**
		*/
		if ($_SESSION['UserTask'] != $_POST['usertask']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['usertask'] . "' WHERE confname='UserTask'";
		}
		/*FIN** **
		*/
		/*INICIO**Seleccionar si se genera IVA en anticipos ono**
		*/
		if ($_SESSION['TaxForAdvances'] != $_POST['TaxForAdvances']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['TaxForAdvances'] . "' WHERE confname='TaxForAdvances'";
		}
		/*FIN** **
		*/
		
		$ErrMsg =  _('The system configuration could not be updated because');
		if (sizeof($sql) > 1 ) {
			$result = DB_Txn_Begin($db);
			foreach ($sql as $line) {
				$result = DB_query($line,$db,$ErrMsg);
 			}
			$result = DB_Txn_Commit($db);
		} elseif(sizeof($sql)==1) {
			$result = DB_query($sql,$db,$ErrMsg);
		}

		prnMsg( _('System configuration updated'),'success');

		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;
	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '>';
echo '<table BORDER=1>';

$TableHeader = '<tr><th>' . _('System Variable Name') . '</th>
	<th>' . _('Value') . '</th>
	<th>' . _('Notes') . '</th>';

echo '<tr><th colspan=3>' . _('General Settings') . '</th></tr>';
echo $TableHeader;

// DefaultDateFormat
echo '<tr><td>' . _('Default Date Format') . ':</td>
	<td><select Name="X_DefaultDateFormat">
	<option '.(($_SESSION['DefaultDateFormat']=='d/m/Y')?'selected ':'').'Value="d/m/Y">d/m/Y</option>
	<option '.(($_SESSION['DefaultDateFormat']=='d.m.Y')?'selected ':'').'Value="d.m.Y">d.m.Y</option>
	<option '.(($_SESSION['DefaultDateFormat']=='m/d/Y')?'selected ':'').'Value="m/d/Y">m/d/Y</option>
	<option '.(($_SESSION['DefaultDateFormat']=='Y/m/d')?'selected ':'').'Value="Y/m/d">Y/m/d</option>
	</select></td>
	<td>' . _('The default date format for entry of dates and display.') . '</td></tr>';

// DefaultTheme
echo '<tr><td>' . _('New Users Default Theme') . ':</td>
	 <td><select Name="X_DefaultTheme">';
$ThemeDirectory = dir('css/');
while (false != ($ThemeName = $ThemeDirectory->read())){
	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != 'CVS'){
		if ($_SESSION['DefaultTheme'] == $ThemeName)
			echo "<option selected value='$ThemeName'>$ThemeName";
		else
			echo "<option value='$ThemeName'>$ThemeName";
	}
}
echo '</select></td>
	<td>' . _('The default theme is used for new users who have not yet defined the display colour scheme theme of their choice') . '</td></tr>';

echo '<tr><th colspan=3>' . _('Accounts Receivable/Payable Settings') . '</th></tr>';

// PastDueDays1
echo '<tr><td>' . _('First Overdue Deadline in (days)') . ':</td>
	<td><input type="Text" class="number" Name="X_PastDueDays1" value="' . $_SESSION['PastDueDays1'] . '" size=3 maxlength=3></td>
	<td>' . _('Customer and supplier balances are displayed as overdue by this many days. This parameter is used on customer and supplier enquiry screens and aged listings') . '</td></tr>';

// PastDueDays2
echo '<tr><td>' . _('Second Overdue Deadline in (days)') . ':</td>
	<td><input type="Text" class="number" Name="X_PastDueDays2" value="' . $_SESSION['PastDueDays2'] . '" size=3 maxlength=3></td>
	<td>' . _('As above but the next level of overdue') . '</td></tr>';


// DefaultCreditLimit
echo '<tr><td>' . _('Default Credit Limit') . ':</td>
	<td><input type="Text" class="number" Name="X_DefaultCreditLimit" value="' . $_SESSION['DefaultCreditLimit'] . '" size=6 maxlength=12></td>
	<td>' . _('The default used in new customer set up') . '</td></tr>';

// Check Credit Limits
echo '<tr><td>' . _('Check Credit Limits') . ':</td>
	<td><select Name="X_CheckCreditLimits">
	<option '.($_SESSION['CheckCreditLimits']==0?'selected ':'').'value="0">'._('Do not check').'
	<option '.($_SESSION['CheckCreditLimits']==1?'selected ':'').'value="1">'._('Warn on breach').'
	<option '.($_SESSION['CheckCreditLimits']==2?'selected ':'').'value="2">'._('Prohibit Sales').'
	</select></td>
	<td>' . _('Credit limits can be checked at order entry to warn only or to stop the order from being entered where it would take a customer account balance over their limit') . '</td></tr>';

// Show_Settled_LastMonth
echo '<tr><td>' . _('Show Settled Last Month') . ':</td>
	<td><select Name="X_Show_Settled_LastMonth">
	<option '.($_SESSION['Show_Settled_LastMonth']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['Show_Settled_LastMonth']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('This setting refers to the format of customer statements. If the invoices and credit notes that have been paid and settled during the course of the current month should be shown then select Yes. Selecting No will only show currently outstanding invoices, credits and payments that have not been allocated') . '</td></tr>';

//RomalpaClause
echo '<tr><td>' . _('Romalpa Clause') . ':</td>
	<td><textarea Name="X_RomalpaClause" rows=3 cols=40>' . htmlentities($_SESSION['RomalpaClause']) . '</textarea></td>
	<td>' . _('This text appears on invoices and credit notes in small print. Normally a reservation of title clause that gives the company rights to collect goods which have not been paid for - to give some protection for bad debts.') . '</td></tr>';

// QuickEntries
echo '<tr><td>' . _('Quick Entries') . ':</td>
	<td><input type="Text" class="number" Name="X_QuickEntries" value="' . $_SESSION['QuickEntries'] . '" size=3 maxlength=2></td>
	<td>' . _('This parameter defines the layout of the sales order entry screen. The number of fields available for quick entries. Any number from 1 to 99 can be entered.') . '</td></tr>';

//'AllowOrderLineItemNarrative'
echo '<tr><td>' . _('Order Entry allows Line Item Narrative') . ':</td>
	<td><select Name="X_AllowOrderLineItemNarrative">
	<option '.($_SESSION['AllowOrderLineItemNarrative']=='1'?'selected ':'').'value="1">'._('Allow Narrative Entry').'
	<option '.($_SESSION['AllowOrderLineItemNarrative']=='0'?'selected ':'').'value="0">'._('No Narrative Line').'
	</select></td>
	<td>' . _('Select whether or not to allow entry of narrative on order line items. This narrative will appear on invoices and packing slips. Useful mainly for service businesses.') . '</td>
	</tr>';
//UpdateCurrencyRatesDaily
echo '<tr><td>' . _('Auto Update Exchange Rates Daily') . ':</td>
	<td><select Name="X_UpdateCurrencyRatesDaily">
	<option '.($_SESSION['UpdateCurrencyRatesDaily']!='0'?'selected ':'').'value="Auto">'._('Automatic').'
	<option '.($_SESSION['UpdateCurrencyRatesDaily']=='0'?'selected ':'').'value="0">'._('Manual').'
	</select></td>
	<td>' . _('Automatic updates to exchange rates will retrieve the latest daily rates from the European Central Bank once per day - when the first user logs in for the day. Manual will never update the rates automatically - exchange rates will need to be maintained manually') . '</td>
	</tr>';

//Default Packing Note Format
echo '<tr><td>' . _('Format of Packing Slips') . ':</td>
	<td><select Name="X_PackNoteFormat">
	<option '.($_SESSION['PackNoteFormat']=='1'?'selected ':'').'value="1">'._('Laser Printed').'
	<option '.($_SESSION['PackNoteFormat']=='2'?'selected ':'').'value="2">'._('Special Stationery').'
	</select></td>
	<td>' . _('Choose the format that packing notes should be printed by default') . '</td>
	</tr>';

//Default Invoice Format
echo '<tr><td>' . _('Invoice Orientation') . ':</td>
	<td><select Name="X_InvoicePortraitFormat">
	<option '.($_SESSION['InvoicePortraitFormat']=='0'?'selected ':'').'value="0">'._('Landscape').'
	<option '.($_SESSION['InvoicePortraitFormat']=='1'?'selected ':'').'value="1">'._('Portrait').'
	</select></td>
	<td>' . _('Select the invoice layout') . '</td>
	</tr>';

//Blind packing note
echo '<tr><td>' . _('Show company details on packing slips') . ':</td>
	<td><select Name="X_DefaultBlindPackNote">
	<option '.($_SESSION['DefaultBlindPackNote']=="1"?'selected ':'').'value="1">'._('Show Company Details').'
	<option '.($_SESSION['DefaultBlindPackNote']=="2"?'selected ':'').'value="2">'._('Hide Company Details').'
	</select></td>
	<td>' . _('Customer branches can be set by default not to print packing slips with the company logo and address. This is useful for companies that ship to customers customers and to show the source of the shipment would be inappropriate. There is an option on the setup of customer branches to ship blind, this setting is the default applied to all new customer branches') . '</td>
	</tr>';


// DispatchCutOffTime
echo '<tr><td>' . _('Dispatch Cut-Off Time') . ':</td>
	<td><select Name="X_DispatchCutOffTime">';
for ($i=0; $i < 24; $i++ )
	echo '<option '.($_SESSION['DispatchCutOffTime'] == $i?'selected ':'').'value="'.$i.'">'.$i;
echo '</select></td>
	<td>' . _('Orders entered after this time will default to be dispatched the following day, this can be over-ridden at the time of sales order entry') . '</td></tr>';

// AllowSalesCost
echo '<tr><td>' . _('Allow Sales Cost') . ':</td>
	<td><select Name="X_AllowSalesCost">
	<option '.($_SESSION['AllowSalesCost']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['AllowSalesCost']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('If an item selected at order entry does not have a cost set up then if this parameter is set to No then the order line will not be able to be entered') . '</td></tr>';

// AllowSalesOfZeroCostItems
echo '<tr><td>' . _('Allow Sales Of Zero Cost Items') . ':</td>
	<td><select Name="X_AllowSalesOfZeroCostItems">
	<option '.($_SESSION['AllowSalesOfZeroCostItems']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['AllowSalesOfZeroCostItems']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('If an item selected at order entry does not have a cost set up then if this parameter is set to No then the order line will not be able to be entered') . '</td></tr>';

// CreditingControlledItems_MustExist
echo '<tr><td>' . _('Controlled Items Must Exist For Crediting') . ':</td>
	<td><select Name="X_CreditingControlledItems_MustExist">
	<option '.($_SESSION['CreditingControlledItems_MustExist']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['CreditingControlledItems_MustExist']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('This parameter relates to the behaviour of the controlled items code. If a serial numbered item has not previously existed then a credit note for it will not be allowed if this is set to Yes') . '</td></tr>';
// precio de venta mayor al costo
echo '<tr><td>' . _('Usar Cliente Unico de facturacion Interna') . ':</td>
	<td><select Name="X_AplicaClienteFI">
	<option '.($_SESSION['AplicaClienteFI']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['AplicaClienteFI']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Valida si la facturacion interna sera con un solo cliente identificado. ') . '</td></tr>';
	
echo '<tr><td>' . _('Clave de Cliente de factura Interna') . ':</td>
	<td><input type="Text"  Name="X_ClaveClienteUsoInterno" size=20 value="' . $_SESSION['ClaveClienteUsoInterno'] . '"></td>
	<td>' . _('Clave de Cliente de Factura Interna') . '</td></tr>';

echo '<tr><td>' . _('Permitir ventas debajo del costo') . ':</td>
	<td><select Name="X_ProhibitSalesBelowCost">
	<option '.($_SESSION['ProhibitSalesBelowCost']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ProhibitSalesBelowCost']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Si se establece un precio menor al costo de venta no se realizara la factura. ') . '</td></tr>';
// valida cual de las consultas debe mostrar	
echo '<tr><td>' . _('Mostrar Todos los vendedores en facturacion') . ':</td>
	<td><select Name="X_ShowAllSalesman">
	<option '.($_SESSION['ShowAllSalesman']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['ShowAllSalesman']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar Todos los vendedores en facturacion sin importar el area de negocio en la que se encuentra asignado el vendedor. ') . '</td></tr>';
	
// DefaultPriceList
$sql = 'SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type';
$ErrMsg = _('Could not load price lists');
$result = DB_query($sql,$db,$ErrMsg);
echo '<tr><td>' . _('Default Price List') . ':</td>';
echo '<td><select Name="X_DefaultPriceList">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected value="">'._('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['DefaultPriceList'] == $row['typeabbrev']?'selected ':'').'value="'.$row['typeabbrev'].'">'.$row['sales_type'];
	}
}
echo '</select></td>
	<td>' . _('This price list is used as a last resort where there is no price set up for an item in the price list that the customer is set up for') . '</td></tr>';

// Default_Shipper
$sql = 'SELECT shipper_id, shippername FROM shippers ORDER BY shippername';
$ErrMsg = _('Could not load shippers');
$result = DB_query($sql,$db,$ErrMsg);
echo '<tr><td>' . _('Default Shipper') . ':</td>';
echo '<td><select Name="X_Default_Shipper">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected value="">'._('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['Default_Shipper'] == $row['shipper_id']?'selected ':'').'value="'.$row['shipper_id'].'">'.$row['shippername'];
	}
}
echo '</select></td>
	<td>' . _('This shipper is used where the best shipper for a customer branch has not been defined previously') . '</td></tr>';

// DoFreightCalc
echo '<tr><td>' . _('Do Freight Calculation') . ':</td>
	<td><select Name="X_DoFreightCalc">
	<option '.($_SESSION['DoFreightCalc']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['DoFreightCalc']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('If this is set to Yes then the system will attempt to calculate the freight cost of a dispatch based on the weight and cubic and the data defined for each shipper and their rates for shipping to various locations. The results of this calculation will only be meaningful if the data is entered for the item weight and volume in the stock item setup for all items and the freight costs for each shipper properly maintained.') . '</td></tr>';

//FreightChargeAppliesIfLessThan
echo '<tr><td>' . _('Apply freight charges if an order is less than') . ':</td>
	<td><input type="Text" class="number" Name="X_FreightChargeAppliesIfLessThan" size=6 maxlength=12 value="' . $_SESSION['FreightChargeAppliesIfLessThan'] . '"></td>
	<td>' . _('This parameter is only effective if Do Freight Calculation is set to Yes. If it is set to 0 then freight is always charged. The total order value is compared to this value in deciding whether or not to charge freight') .'</td></tr>';


// AutoDebtorNo
echo '<tr><td>' . _('Create Debtor Codes Automatically') . ':</td>
	<td><select Name="X_AutoDebtorNo">';

if ($_SESSION['AutoDebtorNo']==0) {
	echo '<option selected value=0>' . _('Manual Entry');
	echo '<option value=1>' . _('Automatic');
} else {
	echo '<option selected value=1>' . _('Automatic');
	echo '<option value=0>' . _('Manual Entry');
}
echo '</select></td>
	<td>' . _('Set to Automatic - customer codes are automatically created - as a sequential number') .'</td></tr>';

//==HJ== drop down list for tax category
$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
$ErrMsg = _('Could not load tax categories table');
$result = DB_query($sql,$db,$ErrMsg);
echo '<tr><td>' . _('Default Tax Category') . ':</td>';
echo '<td><select Name="X_DefaultTaxCategory">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected value="">'._('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['DefaultTaxCategory'] == $row['taxcatid']?'selected ':'').'value="'.$row['taxcatid'].'">'.$row['taxcatname'];
	}
}
echo '</select></td>
	<td>' . _('This is the tax category used for entry of supplier invoices and the category at which freight attracts tax') .'</td></tr>';


//TaxAuthorityReferenceName
echo '<tr><td>' . _('TaxAuthorityReferenceName') . ':</td>
	<td><input type="Text" Name="X_TaxAuthorityReferenceName" size=16 maxlength=25 value="' . $_SESSION['TaxAuthorityReferenceName'] . '"></td>
	<td>' . _('This parameter is what is displayed on tax invoices and credits for the tax authority of the company eg. in Australian this would by A.B.N.: - in NZ it would be GST No: in the UK it would be VAT Regn. No') .'</td></tr>';

// CountryOfOperation
$sql = 'SELECT currabrev, country FROM currencies ORDER BY country';
$ErrMsg = _('Could not load the countries from the currency table');
$result = DB_query($sql,$db,$ErrMsg);
echo '<tr><td>' . _('Country Of Operation') . ':</td>';
echo '<td><select name="X_CountryOfOperation">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected value="">'._('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['CountryOfOperation'] == $row['currabrev']?'selected ':'').'value="'.$row['currabrev'].'">'.$row['country'] . '</option>';
	}
}
echo '</select></td>
	<td>' . _('This parameter is only effective if Do Freight Calculation is set to Yes. Country names come from the currencies table.') .'</td></tr>';

// NumberOfPeriodsOfStockUsage
echo '<tr><td>' . _('Number Of Periods Of StockUsage') . ':</td>
	<td><select Name="X_NumberOfPeriodsOfStockUsage">';
for ($i=1; $i <= 12; $i++ )
	echo '<option '.($_SESSION['NumberOfPeriodsOfStockUsage'] == $i?'selected ':'').'value="'.$i.'">'.$i;
echo '</select></td><td>' . _('In stock usage inquiries this determines how many periods of stock usage to show. An average is calculated over this many periods') .'</td></tr>';

// Check_Qty_Charged_vs_Del_Qty
echo '<tr><td>' . _('Check Quantity Charged vs Deliver Qty') . ':</td>
	<td><select Name="X_Check_Qty_Charged_vs_Del_Qty">
	<option '.($_SESSION['Check_Qty_Charged_vs_Del_Qty']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['Check_Qty_Charged_vs_Del_Qty']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('In entry of AP invoices this determines whether or not to check the quantities received into stock tie up with the quantities invoiced') .'</td></tr>';

// Check_Price_Charged_vs_Order_Price
echo '<tr><td>' . _('Check Price Charged vs Order Price') . ':</td>
	<td><select Name="X_Check_Price_Charged_vs_Order_Price">
	<option '.($_SESSION['Check_Price_Charged_vs_Order_Price']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['Check_Price_Charged_vs_Order_Price']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('In entry of AP invoices this parameter determines whether or not to check invoice prices tie up to ordered prices') .'</td></tr>';

// OverChargeProportion
echo '<tr><td>' . _('Allowed Over Charge Proportion') . ':</td>
	<td><input type="Text" class="number" Name="X_OverChargeProportion" size=4 maxlength=3 value="' . $_SESSION['OverChargeProportion'] . '"></td>
	<td>' . _('If check price charges vs Order price is set to yes then this proportion determines the percentage by which invoices can be overcharged with respect to price') .'</td></tr>';

// OverReceiveProportion
echo '<tr><td>' . _('Allowed Over Receive Proportion') . ':</td>
	<td><input type="Text" class="number" Name="X_OverReceiveProportion" size=4 maxlength=3 value="' . $_SESSION['OverReceiveProportion'] . '"></td>
	<td>' . _('If check quantity charged vs delivery quantity is set to yes then this proportion determines the percentage by which invoices can be overcharged with respect to delivery') .'</td></tr>';

// PO_AllowSameItemMultipleTimes
echo '<tr><td>' . _('Purchase Order Allows Same Item Multiple Times') . ':</td>
	<td><select Name="X_PO_AllowSameItemMultipleTimes">
	<option '.($_SESSION['PO_AllowSameItemMultipleTimes']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['PO_AllowSameItemMultipleTimes']?'selected ':'').'value="0">'._('No').'
	</select></td>&nbsp;<td></td></tr>';

// SO_AllowSameItemMultipleTimes
echo '<tr><td>' . _('Sales Order Allows Same Item Multiple Times') . ':</td>
	<td><select Name="X_SO_AllowSameItemMultipleTimes">
	<option '.($_SESSION['SO_AllowSameItemMultipleTimes']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['SO_AllowSameItemMultipleTimes']?'selected ':'').'value="0">'._('No').'
	</select></td><td>&nbsp;</td></tr>';

//************************************INICIO**************************************
//***********************AGREGAR CAMPO DE defaulttaxforadvances*******************
//********************************iva de anticipos********************************
echo '<tr><td>' . _('IVA para Anticipos') . ':</td>
	<td><input type="Text" class="number" Name="defaulttaxforadvances" size=5 maxlength=5 value="' . $_SESSION['defaulttaxforadvances'] . '"></td>
	<td>&nbsp;</td></tr>';
//************************************FIN**************************************
//***********************AGREGAR CAMPO DE defaulttaxforadvances*******************
//********************************iva de anticipos********************************

//*INICIO**AGREGAR CAMPO para porcentaje de Intereses Moratorios**
echo '<tr><td>' . _('% Intereses Moratorios') . ':</td>
	<td><input type="Text" class="number" Name="porcmoratorios" size=5 maxlength=4 value="' . $_SESSION['InteresMoratorio'] . '"></td>
	<td>&nbsp;</td></tr>';
//*FIN**AGREGAR CAMPO para porcentaje de Intereses Moratorios**

//*INICIO**AGREGAR CAMPO para dias de periodo de gracia, para el cobro de Intereses moratorios**
echo '<tr><td>' . _('Periodo de Gracia') . ':</td>
	<td><input type="Text" class="number" Name="peridogracia" size=5 maxlength=4 value="' . $_SESSION['PeriodoGracia'] . '"></td>
	<td>&nbsp;</td></tr>';
//*FIN**AGREGAR CAMPO para dias de periodo de gracia, para el cobro de Intereses moratorios**

//*INICIO**AGREGAR CAMPO para definir si la empresa aplica o no el cargo de los intereses moratorios**
echo '<tr><td>' . _('Cargo Interes Moratorio') . ':</td>
	<td><select Name="cargointeresmoratorio">
	<option '.($_SESSION['CargoInteresMoratorio']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['CargoInteresMoratorio']?'selected ':'').'value="0">'._('No').'
	</select></td><td>&nbsp;</td>
	</tr>';
	
echo '<tr><td>' . _('Captura Cobrador') . ':</td>
	<td><select Name="capturarcobrador">
	<option '.($_SESSION['CapturarCobrador']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['CapturarCobrador']?'selected ':'').'value="0">'._('No').'
	</select></td><td>&nbsp;</td></tr>';
//VALIDAR CORTE DE CAJA
echo '<tr><td>' . _('Validar Corte Caja') . ':</td>
	<td><select Name="ValidarCorteCaja">
	<option '.($_SESSION['ValidarCorteCaja']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['ValidarCorteCaja']?'selected ':'').'value="0">'._('No').'
	</select></td><td>&nbsp;</td></tr>';	
	
echo '<tr><td>' . _('Extraer Numero De Pedido') . ':</td>
	<td><select Name="extractordernumber">
	<option '.($_SESSION['ExtractOrderNumber']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['ExtractOrderNumber']?'selected ':'').'value="0">'._('No').'
	</select></td><td>' . _('Selección de Productos para pedidos rápidos.') . '</td></tr>';

//SELECCION DE USUARIO PARA REALIZAR SERVICIO O TAREA POR FACTURA

echo '<tr><td>' . _('Usuario Default tarea') . ':</td>';
	echo '<td><select Name="usertask">';
	$sql = "SELECT usuario.userid, usuario.realname,perfil.active
		FROM www_users as usuario
		INNER JOIN sec_profilexuser as perfilxu ON usuario.userid=perfilxu.userid
		INNER JOIN sec_profiles as perfil ON perfilxu.profileid=perfil.profileid
		WHERE perfil.active='1'
		AND usuario.realname <> ''
		ORDER BY usuario.realname";

	$result = DB_query($sql,$db,$ErrMsg);
	
	echo '<option  VALUE="" selected>Ninguno ';
	while($row=DB_fetch_array($result,$db)) {
		echo '<option '.($_SESSION['UserTask'] == $row['userid']?'selected ':'').'value="'.$row['userid'].'">'.$row['realname'];

		/*if ($row['userid'] ==''){ 
                echo '<option  VALUE="' . $row['userid'] .  '  " selected>' .$row['realname'];
            }else{
                echo '<option  VALUE="' . $row['userid'] .  '" >' .$row['realname'];
            }*/
	}
	echo '<input type="hidden" name="UserTask" value=' . $_POST['UserTask'] . '>';

	echo '</select></td><td>' . _('Selección de Usuario para tarea.') . '</td></tr>';
	
//CAMPO PARA DEFINIR SI EN ANTICIPOS SE APLICA IVA O NO
echo '<tr><td>' . _('IVA en Anticipos') . ':</td>
	<td><select Name="TaxForAdvances">
	<option '.($_SESSION['TaxForAdvances']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['TaxForAdvances']?'selected ':'').'value="0">'._('No').'
	</select></td><td>' . _('Selecciona si al generar un anticipo se genera IVA o no.') . '</td></tr>';

//CAMPO PARA CAPTURAR TASA SE IETU
echo '<tr><td>' . _('Tasa IEUT') . ':</td>
	<td>
		<input type="text" name="tasaietu" value="' . $_SESSION['TasaIETU'] . '" size="5" maxlength="6">
	</td><td>' . _('Captura el valor de la tasa ietu') . '</td></tr>';
	
//*INICIO**AGREGAR CAMPO para definir si la empresa aplica o no el cargo de los intereses moratorios**

	
echo '<tr><th colspan=3>' . _('General Settings') . '</th></tr>';
echo $TableHeader;

// YearEnd
$MonthNames = array( 1=>_('January'),
			2=>_('February'),
			3=>_('March'),
			4=>_('April'),
			5=>_('May'),
			6=>_('June'),
			7=>_('July'),
			8=>_('August'),
			9=>_('September'),
			10=>_('October'),
			11=>_('November'),
			12=>_('December') );
echo '<tr><td>' . _('Financial Year Ends On') . ':</td>
	<td><select Name="X_YearEnd">';
for ($i=1; $i <= sizeof($MonthNames); $i++ )
	echo '<option '.($_SESSION['YearEnd'] == $i ? 'selected ' : '').'value="'.$i.'">'.$MonthNames[$i];
echo '</select></td>
	<td>' . _('Defining the month in which the financial year ends enables the system to provide useful defaults for general ledger reports') .'</td></tr>';

//PageLength
echo '<tr><td>' . _('Report Page Length') . ':</td>
	<td><input type="text" class="number" name="X_PageLength" size=4 maxlength=6 value="' . $_SESSION['PageLength'] . '"></td><td>&nbsp;</td>
</tr>';

//DefaultDisplayRecordsMax
echo '<tr><td>' . _('Default Maximum Number of Records to Show') . ':</td>
	<td><input type="text" class="number" name="X_DefaultDisplayRecordsMax" size=4 maxlength=3 value="' . $_SESSION['DefaultDisplayRecordsMax'] . '"></td>
	<td>' . _('When pages have code to limit the number of returned records - such as select customer, select supplier and select item, then this will be the default number of records to show for a user who has not changed this for themselves in user settings.') . '</td>
	</tr>';

//MaxImageSize
echo '<tr><td>' . _('Maximum Size in KB of uploaded images') . ':</td>
	<td><input type="text" class="number" name="X_MaxImageSize" size=4 maxlength=3 value="' . $_SESSION['MaxImageSize'] . '"></td>
	<td>' . _('Picture files of items can be uploaded to the server. The system will check that files uploaded are less than this size (in KB) before they will be allowed to be uploaded. Large pictures will make the system slow and will be difficult to view in the stock maintenance screen.') .'</td>
</tr>';

//$part_pics_dir
echo '<tr><td>' . _('The directory where images are stored') . ':</td>
	<td><select name="X_part_pics_dir">';

$CompanyDirectory = 'companies/' . $_SESSION['DatabaseName'] . '/';
$DirHandle = dir($CompanyDirectory);

while ($DirEntry = $DirHandle->read() ){

	if (is_dir($CompanyDirectory . $DirEntry)
		AND $DirEntry != '..'
		AND $DirEntry!='.'
		AND $DirEntry != 'CVS'
		AND $DirEntry != 'reports'
		AND $DirEntry != 'locale'
		AND $DirEntry != 'fonts'   ){

		if ($_SESSION['part_pics_dir'] == $CompanyDirectory . $DirEntry){
			echo '<option selected value="' . $DirEntry . '">' . $DirEntry . '</option>';
		} else {
			echo '<option value="' . $DirEntry . '">' . $DirEntry  . '</option>';
		}
	}
}
echo '</select></td>
	<td>' . _('The directory under which all image files should be stored. Image files take the format of ItemCode.jpg - they must all be .jpg files and the part code will be the name of the image file. This is named automatically on upload. The system will check to ensure that the image is a .jpg file') . '</td>
	</tr>';


//$reports_dir
echo '<tr><td>' . _('The directory where reports are stored') . ':</td>
	<td><select name="X_reports_dir">';

$DirHandle = dir($CompanyDirectory);

while (false != ($DirEntry = $DirHandle->read())){

	if (is_dir($CompanyDirectory . $DirEntry)
		AND $DirEntry != '..'
		AND $DirEntry != 'includes'
		AND $DirEntry!='.'
		AND $DirEntry != 'doc'
		AND $DirEntry != 'css'
		AND $DirEntry != 'CVS'
		AND $DirEntry != 'sql'
		AND $DirEntry != 'part_pics'
		AND $DirEntry != 'locale'
		AND $DirEntry != 'fonts'      ){

		if ($_SESSION['reports_dir'] == $CompanyDirectory . $DirEntry){
			echo '<option selected value="' . $DirEntry . '">' . $DirEntry . '</option>';
		} else {
			echo '<option value="' . $DirEntry . '">' . $DirEntry  . '</option>';
		}
	}
}

echo '</select></td>
	<td>' . _('The directory under which all report pdf files should be created in. A separate directory is recommended') . '</td>
	</tr>';


// HTTPS_Only
echo '<tr><td>' . _('Only allow secure socket connections') . ':</td>
	<td><select name="X_HTTPS_Only">
	<option '.($_SESSION['HTTPS_Only']?'selected ':'').'value="1">'._('Yes') . '</option>
	<option '.(!$_SESSION['HTTPS_Only']?'selected ':'').'value="0">'._('No') . '</option>
	</select></td>
	<td>' . _('Force connections to be only over secure sockets - ie encrypted data only') . '</td>
	</tr>';

/*Perform Database maintenance DB_Maintenance*/
echo '<tr><td>' . _('Perform Database Maintenance At Logon') . ':</td>
	<td><select name="X_DB_Maintenance">';
	if ($_SESSION['DB_Maintenance']=='1'){
		echo '<option selected value="1">'._('Daily') . '</option>';
	} else {
		echo '<option value="1">'._('Daily') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='7'){
		echo '<option selected value="7">'._('Weekly') . '</option>';
	} else {
		echo '<option value="7">'._('Weekly') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='30'){
		echo '<option selected value="30">'._('Monthly') . '</option>';
	} else {
		echo '<option value="30">'._('Monthly') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='0'){
		echo '<option selected value="0">'._('Never') . '</option>';
	} else {
		echo '<option value="0">'._('Never') . '</option>';
	}

	echo '</select></td>
	<td>' . _('Uses the function DB_Maintenance defined in ConnectDB_XXXX.inc to perform database maintenance tasks, to run at regular intervals - checked at each and every user login') . '</td>
	</tr>';

$WikiApplications = array( _('Disabled'),
					_('WackoWiki'),
					_('MediaWiki') );

echo '<tr><td>' . _('Wiki application') . ':</td>
	<td><select name="X_WikiApp">';
for ($i=0; $i < sizeof($WikiApplications); $i++ ) {
	echo '<option '.($_SESSION['WikiApp'] == $WikiApplications[$i] ? 'selected ' : '').'value="'.$WikiApplications[$i].'">'.$WikiApplications[$i]  . '</option>';
}
echo '</select></td>
	<td>' . _('This feature makes webERP show links to a free form company knowledge base using a wiki. This allows sharing of important company information - about customers, suppliers and products and the set up of work flow menus and/or company procedures documentation') .'</td></tr>';

echo '<tr><td>' . _('Wiki Path') . ':</td>
	<td><input type="text" name="X_WikiPath" size=40 maxlength=40 value="' . $_SESSION['WikiPath'] . '"></td>
	<td>' . _('The path to the wiki installation to form the basis of wiki URLs - this should be the directory on the web-server where the wiki is installed. The wiki must be installed on the same web-server as webERP') .'</td></tr>';

echo '<tr><td>' . _('Geocode Customers and Suppliers:') . ':</td>
        <td><select name="X_geocode_integration">';
if ($_SESSION['geocode_integration']==1){
        echo  '<option selected value="1">' . _('Geocode Integration Enabled') . '</option>';
        echo  '<option value="0">' . _('Geocode Integration Disabled') . '</option>';
} else {
        echo  '<option selected value="0">' . _('Geocode Integration Disabled') . '</option>';
        echo  '<option value="1">' . _('Geocode Integration Enabled') . '</option>';
}
echo '</select></td>
        <td>' . _('This feature will give Latitude and Longitude coordinates to customers and suppliers. Requires access to a mapping provider. You must setup this facility under Main Menu - Setup - Geocode Setup. This feature is experimental.') .'</td></tr>';

echo '<tr><td>' . _('Extended Customer Information') . ':</td>
        <td><select name="X_Extended_CustomerInfo">';
if ($_SESSION['Extended_CustomerInfo']==1){
        echo  '<option selected value="1">' . _('Extended Customer Info Enabled') . '</option>';
        echo  '<option value="0">' . _('Extended Customer Info Disabled') . '</option>';
} else {
        echo  '<option selected value="0">' . _('Extended Customer Info Disabled') . '</option>';
        echo  '<option value="1">' . _('Extended Customer Info Enabled') . '</option>';
}
echo '</select></td>
        <td>' . _('This feature will give extended information in the Select Customer screen.') .'</td></tr>';

echo '<tr><td>' . _('Extended Supplier Information') . ':</td>
        <td><select name="X_Extended_SupplierInfo">';
if ($_SESSION['Extended_SupplierInfo']==1){
        echo  '<option selected value="1">' . _('Extended Supplier Info Enabled') . '</option>';
        echo  '<option value="0">' . _('Extended Supplier Info Disabled') . '</option>';
} else {
        echo  '<option selected value="0">' . _('Extended Supplier Info Disabled') . '</option>';
        echo  '<option value="1">' . _('Extended Supplier Info Enabled') . '</option>';
}
echo '</select></td>
        <td>' . _('This feature will give extended information in the Select Supplier screen.') .'</td></tr>';

echo '<tr><td>' . _('Prohibit GL Journals to Control Accounts') . ':</td>
	<td><select name="X_ProhibitJournalsToControlAccounts">';
if ($_SESSION['ProhibitJournalsToControlAccounts']=='1'){
		echo  '<option selected value="1">' . _('Prohibited') . '</option>';
		echo  '<option value="0">' . _('Allowed') . '</option>';
} else {
		echo  '<option value="1">' . _('Prohibited') . '</option>';
		echo  '<option selected value="0">' . _('Allowed') . '</option>';
}
echo '</select></td><td>' . _('Setting this to prohibited prevents accidentally entering a journal to the automatically posted and reconciled control accounts for creditors (AP) and debtors (AR)') . '</td></tr>';


echo '<tr><td>' . _('Prohibit GL Journals to Periods Prior To') . ':</td>
	<td><select Name="X_ProhibitPostingsBefore">';

$sql = 'SELECT lastdate_in_period FROM periods ORDER BY periodno DESC';
$ErrMsg = _('Could not load periods table');
$result = DB_query($sql,$db,$ErrMsg);
while ($PeriodRow = DB_fetch_row($result)){
	if ($_SESSION['ProhibitPostingsBefore']==$PeriodRow[0]){
		echo  '<option selected value="' . $PeriodRow[0] . '">' . ConvertSQLDate($PeriodRow[0]) . '</option>';
	} else {
		echo  '<option value="' . $PeriodRow[0] . '">' . ConvertSQLDate($PeriodRow[0]) . '</option>';
	}
}
echo '</select></td><td>' . _('This allows all periods before the selected date to be locked from postings. All postings for transactions dated prior to this date will be posted in the period following this date.') . '</td></tr>';

echo '<tr><td>' . _('Inventory Costing Method') . ':</td>
	<td><select name="X_WeightedAverageCosting">';

if ($_SESSION['WeightedAverageCosting']==1){
	echo  '<option selected value="1">' . _('Weighted Average Costing') . '</option>';
	echo  '<option value="0">' . _('Standard Costing') . '</option>';
} else {
	echo  '<option selected value="0">' . _('Standard Costing') . '</option>';
	echo  '<option value="1">' . _('Weighted Average Costing') . '</option>';
}

echo '</select></td><td>' . _('webERP allows inventory to be costed based on the weighted average of items in stock or full standard costing with price variances reported. The selection here determines the method used and the general ledger postings resulting from purchase invoices and shipment closing') . '</td></tr>';

echo '<tr><td>' . _('Auto Issue Components') . ':</td>
		<td>
		<select name="X_AutoIssue">';
if ($_SESSION['AutoIssue']==0) {
	echo '<option selected value=0>' . _('No') . '</option>';
	echo '<option value=1>' . _('Yes') . '</option>';
} else {
	echo '<option selected value=1>' . _('Yes') . '</option>';
	echo '<option value=0>' . _('No') . '</option>';
	}
echo '</select></td><td>' . _('When items are manufactured it is possible for the components of the item to be automatically decremented from stock in accordance with the Bill of Material setting') . '</td></tr>' ;

echo '<tr><td>' . _('Prohibit Negative Stock') . ':</td>
		<td>
		<select name="X_ProhibitNegativeStock">';
if ($_SESSION['ProhibitNegativeStock']==0) {
	echo '<option selected value=0>' . _('No') . '</option>';
	echo '<option value=1>' . _('Yes') . '</option>';
} else {
	echo '<option selected value=1>' . _('Yes') . '</option>';
	echo '<option value=0>' . _('No') . '</option>';
}
echo '</select></td><td>' . _('Setting this parameter to Yes prevents invoicing and the issue of stock if this would result in negative stock. The stock problem must be corrected before the invoice or issue is allowed to be processed.') . '</td></tr>' ;

echo '<tr><td>' . _('Solo mostrar productos con optimo autorizado o disponibles') . ':</td>
	<td><select Name="X_ShowAllProductsForSales">
	<option '.($_SESSION['ShowAllProductsForSales']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['ShowAllProductsForSales']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('En el alta de pedido de venta mostrar solo productos con disponibilidad mayor a cero o con inventario autorizado mayor a cero') .'</td></tr>';

echo '<tr><td>' . _('Prohibir Compra de productos de mano de obra') . ':</td>
		<td>
		<select name="X_ProhibitPurchD">';
if ($_SESSION['ProhibitPurchD']==0) {
	echo '<option selected value=0>' . _('No') . '</option>';
	echo '<option value=1>' . _('Si') . '</option>';
} else {
	echo '<option selected value=1>' . _('Si') . '</option>';
	echo '<option value=0>' . _('No') . '</option>';
}
echo '</select></td><td>' . _(' Prohibe la compra de articulos que se encuentran categorizados como mano de obra.') . '</td></tr>' ;


//Months of Audit Trail to Keep
echo '<tr><td>' . _('Months of Audit Trail to Retain') . ':</td>
	<td><input type="text" class="number" name="X_MonthsAuditTrail" size=3 maxlength=2 value="' . $_SESSION['MonthsAuditTrail'] . '"></td><td>' . _('If this parameter is set to 0 (zero) then no audit trail is retained. An audit trail is a log of which users performed which additions updates and deletes of database records. The full SQL is retained') . '</td>
</tr>';

//DefineControlledOnWOEntry
echo '<tr><td>' . _('Controlled Items Defined At Work Order Entrry') . ':</td>
	<td><select Name="X_DefineControlledOnWOEntry">
	<option '.($_SESSION['DefineControlledOnWOEntry']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['DefineControlledOnWOEntry']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('When set to yes, controlled items are defined at the time of the work order creation. Otherwise controlled items (serial numbers and batch/roll/lot references) are entered at the time the finished items are received against the work order') . '</td></tr>';

//AutoCreateWOs
echo '<tr><td>' . _('Auto Create Work Orders') . ':</td>
		<td>
		<select name="X_AutoCreateWOs">';

if ($_SESSION['AutoCreateWOs']==0) {
	echo '<option selected value=0>' . _('No') . '</option>';
	echo '<option value=1>' . _('Yes') . '</option>';
} else {
	echo '<option selected value=1>' . _('Yes') . '</option>';
	echo '<option value=0>' . _('No') . '</option>';
}
echo '</select></td><td>' . _('Setting this parameter to Yes will ensure that when a sales order is placed if there is insufficient stock then a new work order is created at the default factory location') . '</td></tr>' ;

echo '<tr><td>' . _('Default Factory Location') . ':</td>
	<td><select Name="X_DefaultFactoryLocation">';

$sql = 'SELECT loccode,locationname FROM locations';
$ErrMsg = _('Could not load locations table');
$result = DB_query($sql,$db,$ErrMsg);
while ($LocationRow = DB_fetch_array($result)){
	if ($_SESSION['DefaultFactoryLocation']==$LocationRow['loccode']){
		echo  '<option selected value="' . $LocationRow['loccode'] . '">' . $LocationRow['locationname'] . '</option>';
	} else {
		echo  '<option value="' .  $LocationRow['loccode'] . '">' . $LocationRow['locationname'] . '</option>';
	}
}
echo '</select></td><td>' . _('This location is the location where work orders will be created from when the auto create work orders option is activated') . '</td></tr>';

//$_SESSION['FactoryManagerEmail'] = "algo@algo.com";
echo '<tr><td>' . _('Factory Manager Email Address') . ':</td>
	<td><input type="text" name="X_FactoryManagerEmail" size=50 maxlength=50 value="' . $_SESSION['FactoryManagerEmail'] . '"></td>
	<td>' . _('Work orders automatically created when sales orders are entered will be emailed to this address') .'</td></tr>';

echo '<tr><td>' . _('Envio XSA') . ':</td>
	<td><select Name="X_EnvioXSA">
	<option '.($_SESSION['EnvioXSA']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['EnvioXSA']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Aplica o No la Facturación electronica') . '</td></tr>';

echo '<tr><td>' . _('Product Search') . ':</td>
	<td><select Name="X_ProductSearch">
	<option '.($_SESSION['ProductSearch']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ProductSearch']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Busqueda de Producto con (SI) o sin (NO) parametro de busqueda ') . '</td></tr>';
	
echo '<tr><td>' . _('Type Cost Stock') . ':</td>
	<td><select Name="X_TypeCostStock">
	<option '.($_SESSION['TypeCostStock']?'selected ':'').'value="1">'._('1').'
	<option '.(!$_SESSION['TypeCostStock']?'selected ':'').'value="0">'._('0').'
	</select></td>
	<td>' . _('Tipo de costeo O=unidad de negocio - 1=Empresa') . '</td></tr>';

echo '<tr><td>' . _('Invoice Cash') . ':</td>
	<td><select Name="X_InvoiceCash">
	<option '.($_SESSION['InvoiceCash']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['InvoiceCash']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Envio de Factura Directo a Caja con Selección (SI)') . '</td></tr>';
	
echo '<tr><td>' . _('Forzar Captura RFC') . ':</td>
	<td><select Name="X_ForzarCapturaRFC">
	<option '.($_SESSION['ForzarCapturaRFC']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ForzarCapturaRFC']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('No Permite dar de Alta al Cliente o Modificarlo Si no esta Capturado el RFC') . '</td></tr>';
	
echo '<tr><td>' . _('Permitir Reversa de Productos en Rojo') . ':</td>
	<td><select Name="X_ReversePurchOrders">
	<option '.($_SESSION['ReversePurchOrders']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ReversePurchOrders']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Permite Reversar productos de ordenes de compra si no existen en el inventario del almacen en donde se realizo la compra') . '</td></tr>';
	
echo '<tr><td>' . _('Validar Precio de venta en facturacion') . ':</td>
	<td><select Name="X_PriceLess">
	<option '.($_SESSION['PriceLess']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['PriceLess']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Valida que el precio de venta no sea menor al de la lista de precio') .'</td></tr>';

echo '<tr><th colspan=3>' . _('Datos Generales de Facturacion Electronica') . '</th></tr>';
echo '<tr><td>' . _('XSA') . ':</td>
	<td><input type="text" name="X_XSA" size=40 maxlength=40 value="' . $_SESSION['XSA'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
	
echo '<tr><td>' . _('Texto Facturacion 1') . ':</td>
	<td><input type="text" name="LabelText1" size=40 maxlength=40 value="' . $_SESSION['LabelText1'] . '"></td>
	<td>' . _('Etiqueta de facturacion de campos extra') .'</td></tr>';
	

echo '<tr><td>' . _('Texto Facturacion 2') . ':</td>
	<td><input type="text" name="LabelText2" size=40 maxlength=40 value="' . $_SESSION['LabelText2'] . '"></td>
	<td>' . _('Etiqueta de facturacion de campos extra') .'</td></tr>';
	

echo '<tr><td>' . _('Texto Facturacion 3') . ':</td>
	<td><input type="text" name="LabelText3" size=40 maxlength=40 value="' . $_SESSION['LabelText3'] . '"></td>
	<td>' . _('Etiqueta de facturacion de campos extra') .'</td></tr>';
	
echo '<tr><td>' . _('Muestra Descuento 2') . ':</td>
	<td><select Name="X_Showdiscount1">
	<option '.($_SESSION['Showdiscount1']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['Showdiscount1']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Muestra en Factura el descuento 2') . '</td></tr>';

echo '<tr><td>' . _('Muestra Descuento 3') . ':</td>
	<td><select Name="X_Showdiscount2">
	<option '.($_SESSION['Showdiscount2']?'selected ':'').'value="1">'._('Si').'
	<option '.(!$_SESSION['Showdiscount2']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Muestra en Factura el descuento 3') . '</td></tr>';
	
echo '<tr><td>' . _('Mostrar Trabajadores en Factura') . ':</td>
	<td><select Name="X_ShowWorkers">
	<option '.($_SESSION['ShowWorkers']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowWorkers']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Muestra los trabajadores que realizaron el trabajo manual en la factura.') . '</td></tr>';
	
echo '<tr><td>' . _('Mostrar Lista de Precios') . ':</td>
	<td><select Name="X_ShowPriceList">
	<option '.($_SESSION['ShowPriceList']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowPriceList']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar lista de precio aplicado en la factura') . '</td></tr>';
	
echo '<tr><td>' . _('Mostrar') .$_SESSION['LabelText1']. ':</td>
	<td><select Name="X_ShowLabelText1">
	<option '.($_SESSION['ShowLabelText1']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowLabelText1']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar') . $_SESSION['LabelText1'] . _(' en la factura.') .'</td></tr>';
	
echo '<tr><td>' . _('Mostrar') .$_SESSION['LabelText2']. ':</td>
	<td><select Name="X_ShowLabelText2">
	<option '.($_SESSION['ShowLabelText2']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowLabelText2']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar') . $_SESSION['LabelText2'] . _(' en la factura.') .'</td></tr>';

echo '<tr><td>' . _('Mostrar') .$_SESSION['LabelText3']. ':</td>
	<td><select Name="X_ShowLabelText3">
	<option '.($_SESSION['ShowLabelText3']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowLabelText3']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar') . $_SESSION['LabelText1'] . _(' en la factura.') .'</td></tr>';
	
echo '<tr><td>' . _('Mostrar Sello digital y cadena original en Recibos') . ':</td>
	<td><select Name="X_ShowSello">
	<option '.($_SESSION['ShowSello']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowSello']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar Sello y cadena original en comprobantes de Recibos') .'</td></tr>';

echo '<tr><td>' . _('Mostrar Nombre Almacen en Factura') . ':</td>
	<td><select Name="X_ShowLocation">
	<option '.($_SESSION['ShowLocation']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowLocation']?'selected ':'').'value="0">'._('No').'
	</select></td>
	<td>' . _('Mostrar el almacen de venta en documentos fiscales.') .'</td></tr>';

	
//*****VARIABLES QUE NO ESTA DEFINIDA SU FUNCIONALIDAD *****//
//DB_Maintenance_LastRun
/*echo '<tr><td>' . _('DB_Maintenance_LastRun') . ':</td>
	<td><input type="text" name="DB_Maintenance_LastRun" size=40 maxlength=40 value="' . $_SESSION['DB_Maintenance_LastRun'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//DefaultCustomerType
echo '<tr><td>' . _('DefaultCustomerType') . ':</td>
	<td><input type="text" name="DefaultCustomerType" size=40 maxlength=40 value="' . $_SESSION['DefaultCustomerType'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//defaulttaxforadvances	
echo '<tr><td>' . _('defaulttaxforadvances') . ':</td>
	<td><input type="text" name="defaulttaxforadvances" size=40 maxlength=40 value="' . $_SESSION['defaulttaxforadvances'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//EDIHeaderMsgId
echo '<tr><td>' . _('EDIHeaderMsgId') . ':</td>
	<td><input type="text" name="EDIHeaderMsgId" size=40 maxlength=40 value="' . $_SESSION['EDIHeaderMsgId'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//EDIReference
echo '<tr><td>' . _('EDIReference') . ':</td>
	<td><input type="text" name="EDIReference" size=40 maxlength=40 value="' . $_SESSION['EDIReference'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//EDI_Incoming_Orders
echo '<tr><td>' . _('EDI_Incoming_Orders') . ':</td>
	<td><input type="text" name="EDI_Incoming_Orders" size=40 maxlength=40 value="' . $_SESSION['EDI_Incoming_Orders'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//EDI_MsgPending
echo '<tr><td>' . _('EDI_MsgPending') . ':</td>
	<td><input type="text" name="EDI_MsgPending" size=40 maxlength=40 value="' . $_SESSION['EDI_MsgPending'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//EDI_MsgSent
echo '<tr><td>' . _('EDI_MsgSent') . ':</td>
	<td><input type="text" name="EDI_MsgSent" size=40 maxlength=40 value="' . $_SESSION['EDI_MsgSent'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//FreightTaxCategory
echo '<tr><td>' . _('FreightTaxCategory') . ':</td>
	<td><input type="text" name="FreightTaxCategory" size=40 maxlength=40 value="' . $_SESSION['FreightTaxCategory'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//FutureDate
echo '<tr><td>' . _('FutureDate') . ':</td>
	<td><input type="text" name="FutureDate" size=40 maxlength=40 value="' . $_SESSION['FutureDate'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadioBeaconFileCounter
echo '<tr><td>' . _('RadioBeaconFileCounter') . ':</td>
	<td><input type="text" name="RadioBeaconFileCounter" size=40 maxlength=40 value="' . $_SESSION['RadioBeaconFileCounter'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadioBeaconFTP_user_name
echo '<tr><td>' . _('RadioBeaconFTP_user_name') . ':</td>
	<td><input type="text" name="RadioBeaconFTP_user_name" size=40 maxlength=40 value="' . $_SESSION['RadioBeaconFTP_user_name'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadioBeaconHomeDir
echo '<tr><td>' . _('RadioBeaconHomeDir') . ':</td>
	<td><input type="text" name="RadioBeaconHomeDir" size=40 maxlength=40 value="' . $_SESSION['RadioBeaconHomeDir'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadioBeaconStockLocation
echo '<tr><td>' . _('RadioBeaconStockLocation') . ':</td>
	<td><input type="text" name="RadioBeaconStockLocation" size=40 maxlength=40 value="' . $_SESSION['RadioBeaconStockLocation'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadioBraconFTP_server
echo '<tr><td>' . _('RadioBraconFTP_server') . ':</td>
	<td><input type="text" name="RadioBraconFTP_server" size=40 maxlength=40 value="' . $_SESSION['RadioBraconFTP_server'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadioBreaconFilePrefix
echo '<tr><td>' . _('RadioBreaconFilePrefix') . ':</td>
	<td><input type="text" name="RadioBreaconFilePrefix" size=40 maxlength=40 value="' . $_SESSION['RadioBreaconFilePrefix'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';
//RadionBeaconFTP_user_pass
echo '<tr><td>' . _('RadionBeaconFTP_user_pass') . ':</td>
	<td><input type="text" name="RadionBeaconFTP_user_pass" size=40 maxlength=40 value="' . $_SESSION['RadionBeaconFTP_user_pass'] . '"></td>
	<td>' . _('Captura de Direccion URL para Consultar Facturas Eléctronicas') .'</td></tr>';*/
//*****FIN VARIABLES QUE NO ESTA DEFINIDA SU FUNCIONALIDAD *****//

echo '</table><div class="centre"><input type="Submit" Name="submit" value="' . _('Update') . '"></div></form>';

include('includes/footer.inc');
?>
