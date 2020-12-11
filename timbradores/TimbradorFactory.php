<?php

include_once realpath(dirname(__FILE__)) . '/TimbradorEcodex.php';
include_once realpath(dirname(__FILE__)) . '/TimbradorTralix.php';
include_once realpath(dirname(__FILE__)) . '/TimbradorSOFTTI.php';
include_once realpath(dirname(__FILE__)) . '/TimbradorFinkok.php';
include_once realpath(dirname(__FILE__)) . '/TimbradorFinkokDebug.php';
///include_once realpath(dirname(__FILE__)) . '/TimbradorInvoiceOne.php';
include_once realpath(dirname(__FILE__)) . '/TimbradorFake.php';
include_once realpath(dirname(__FILE__)) . '/TimbradorFakeRet.php';

class TimbradorFactory {

	/**
	 *
	 * @param Array $config
	 * @return TimbradorInterface Obtenemos instancia que implementa la interface TimbradorInterface
	 */
	public static function getTimbrador($config, $iddocto=0, $type="cfdi") {

		$timbradortype = $config['Timbrador'];
		$timbrador = null;

		switch ($timbradortype) {

			case 'TRALIX':
				$timbrador = new TimbradorTralix($config['XSA'], $config['Keyfact']);
				break;
			case 'ECODEX':
				$timbrador = new TimbradorEcodex($config['EcodexIntegrador']);
				break;
			case 'SOFTTI':
				if($type == "cfdi") {
					$timbrador = new TimbradorSOFTTI($config['SOFTTIUser'], $config['SOFTTIPass'], $config['SOFTTIUrlStamp'], $config['SOFTTIUrlCancel'], $config['SOFTTIRfc']);
				} else if($type == "retenciones") {
					$timbrador = new TimbradorSOFTTI($config['SOFTTIUser'], $config['SOFTTIPass'], $config['SOFTTIRetUrlStamp'], $config['SOFTTIRetUrlCancel'], $config['SOFTTIRfc']);
				} else {
					$timbrador = new TimbradorSOFTTI($config['SOFTTIUser'], $config['SOFTTIPass'], $config['SOFTTIUrlStamp'], $config['SOFTTIUrlCancel'], $config['SOFTTIRfc']);
				}
				break;
			case 'FINKOK':
				if($type == "cfdi") {
					$timbrador = new TimbradorFinkok($config['FinkokUser'], $config['FinkokPass'], $config['DatabaseName'], $config['FinkokUrlStamp'], $config['FinkokUrlCancel'], $iddocto);
				} else if($type == "retenciones") {
					$timbrador = new TimbradorFinkok($config['FinkokUser'], $config['FinkokPass'], $config['DatabaseName'], $config['FinkokRetUrlStamp'], $config['FinkokRetUrlCancel']);
				} else {
					$timbrador = new TimbradorFinkok($config['FinkokUser'], $config['FinkokPass'], $config['DatabaseName'], $config['FinkokUrlStamp'], $config['FinkokUrlCancel']);
				}
				break;
			case 'FINKOKDEBUG':
				if($type == "cfdi") {
					$timbrador = new TimbradorFinkokDebug($config['FinkokUser'], $config['FinkokPass'], $config['DatabaseName'], $config['FinkokUrlStamp'], $config['FinkokUrlCancel'], $iddocto);
				} else if($type == "retenciones") {
					$timbrador = new TimbradorFinkokDebug($config['FinkokUser'], $config['FinkokPass'], $config['DatabaseName'], $config['FinkokRetUrlStamp'], $config['FinkokRetUrlCancel']);
				} else {
					$timbrador = new TimbradorFinkokDebug($config['FinkokUser'], $config['FinkokPass'], $config['DatabaseName'], $config['FinkokUrlStamp'], $config['FinkokUrlCancel']);
				}
				break;
			case 'INVOICEONE':
				$timbrador = new TimbradorInvoiceOne($config['InvoiceOneUser'], $config['InvoiceOnePass']);
				break;
			case 'FAKE':
				if($type == "cfdi") {
					$timbrador = new TimbradorFake();
				} else if($type == "retenciones") {
					$timbrador = new TimbradorFakeRet();
				} else {
					$timbrador = new TimbradorFake();
				}
				break;
		}

		return $timbrador;
	}
}

?>