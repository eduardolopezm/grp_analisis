<?php
// Systems can temporarily force a reload by setting the variable
// $ForceConfigReload to true

/* ARCHIVO MODIFICADO POR: FRUEBEL CANTERA*/
/* FECHA DE MODIFICACION: 14-DIC-2009 */
/* CAMBIOS:*/
/* 1.- AGREGUE LIGAS DE CONTABILIDAD */
/* FIN DE CAMBIOS*/
/*
   ARCHIVO MODIFICADO POR: FRUEBEL CANTERA
   FECHA DE MODIFICACION: 18-DIC-2009 
   CAMBIOS:
           1.- AGREGUE gllink_taxadvance para iva de anticipos y gllink_Invoice para cuenta de facturas de contado
   FIN DE CAMBIOS
*///

if(isset($ForceConfigReload) and $ForceConfigReload==TRUE OR !isset($_SESSION['CompanyDefaultsLoaded'])) {
	$arrayconfig = array("SupplierDefault" => "SupplierDefault",
						"SupplierDefaultUSD" => "SupplierDefaultUSD");
	$sql = 'SELECT confname, confvalue FROM config'; // dont care about the order by
	$ConfigResult = DB_query($sql,$db);
	while( $myrow = DB_fetch_row($ConfigResult) ) {
		if (is_numeric($myrow[1]) and $myrow[0]!='DefaultPriceList' and !in_array($myrow[0], $arrayconfig)){
			//the variable name is given by $myrow[0]
			$_SESSION[$myrow[0]] = (double) $myrow[1];
		} else {
			$_SESSION[$myrow[0]] =  $myrow[1];
		}

	} //end loop through all config variables
	$_SESSION['CompanyDefaultsLoaded'] = true;
	DB_free_result($ConfigResult); // no longer needed
	/*Maybe we should check config directories exist and try to create if not */

/* Also reads all the company data set up in the company record and returns an array */

	$sql=	'SELECT
				coyname,
				gstno,
				regoffice1,
				regoffice2,
				regoffice3,
				regoffice4,
				regoffice5,
				regoffice6,
				telephone,
				fax,
				email,
				currencydefault,
				debtorsact,
				pytdiscountact,
				creditorsact,
				payrollact,
				grnact,
				exchangediffact,
				purchasesexchangediffact,
				retainedearnings,
				freightact,
				gllink_debtors,
				gllink_creditors,
				gllink_stock,
				gllink_notesdebtors,
				gllink_moratorios,
				gllink_advancesdebtors,
				gllink_taxadvance,
				gllink_Invoice,
				creditnote,
				debitnote,
				gllink_loccpuente,
				gllink_acreeddiversos,
				gllink_deudoresdiversos,
				gllink_intpordevengar,
				gllink_intdevengados,
				gllink_dxctransferenciacredito,
				gllink_retencioniva,
				gllink_retencionhonorarios,
				gllink_retencionCedular,
				gllink_retencionFletes,
				gllink_retencionComisiones,
				gllink_retencionarrendamiento,
				gllink_retencionIVAarrendamiento,
				gllink_sobrantesfaltantescaja,
				gllink_descuentopp,
				gllink_purchasesexchangediffactutil,
				gllink_exchangediffactutil,
				gllink_shipmentclose,
				gllink_presupuestalingreso,
				gllink_presupuestalingresoEjecutar,
				gllink_presupuestalingresoModificado,
				gllink_presupuestalingresoDevengado,
				gllink_presupuestalingresoRecaudado,
				gllink_presupuestalegreso,
				gllink_presupuestalegresoEjercer,
				gllink_presupuestalegresoModificado,
				gllink_presupuestalegresocomprometido,
				gllink_presupuestalegresodevengado,
				gllink_presupuestalegresoejercido,
				gllink_presupuestalegresopagado
				gnrlocation
			FROM companies
				WHERE coycode=1';

	$ErrMsg = _('An error occurred accessing the database to retrieve the company information');
	$ReadCoyResult = DB_query($sql,$db,$ErrMsg);

	if (DB_num_rows($ReadCoyResult)==0) {
      		echo '<BR><B>';
		prnMsg( _('The company record has not yet been set up') . '</B><BR>' . _('From the system setup tab select company maintenance to enter the company information and system preferences'),'error',_('CRITICAL PROBLEM'));
		exit;
	} else {
		$_SESSION['CompanyRecord'] = DB_fetch_array($ReadCoyResult);
	}
} //end if force reload or not set already

	/*


Stay in config.php
$DefaultLanguage = en_GB
$allow_demo_mode = 1



$EDIHeaderMsgId = D:01B:UN:EAN010
$EDIReference = WEBERP
$EDI_MsgPending = EDI_Pending
$EDI_MsgSent = EDI_Sent
$EDI_Incoming_Orders = EDI_Incoming_Orders

$RadioBeaconStockLocation = BL
$RadioBeaconHomeDir = /home/RadioBeacon
$RadioBeaconFileCounter = /home/RadioBeacon/FileCounter
$RadioBeaconFilePrefix = ORDXX
$RadioBeaconFTP_server = 192.168.2.2
$RadioBeaconFTP_user_name = RadioBeacon ftp server user name
$RadionBeaconFTP_user_pass = Radio Beacon remote ftp server password
*/
?>