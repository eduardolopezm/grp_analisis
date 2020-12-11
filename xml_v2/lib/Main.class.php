<?php 
include_once realpath(dirname(__FILE__)) . '/SatXmlValidatorManager.class.php';
include_once realpath(dirname(__FILE__)) . '/factory/ComprobanteFactory.class.php';
include_once realpath(dirname(__FILE__)) . '/validators/SatGeneralValidator.class.php';
include_once realpath(dirname(__FILE__)) . '/validators/SatXsdValidator.class.php';
include_once realpath(dirname(__FILE__)) . '/validators/SatStampValidator.class.php';
include_once realpath(dirname(__FILE__)) . '/validators/SatSepomexValidator.class.php';
include_once realpath(dirname(__FILE__)) . '/validators/SatDocument2xValidator.class.php';
include_once realpath(dirname(__FILE__)) . '/validators/SatDocument3xValidator.class.php';

/**
 * 
 * @author javierhernandezpineda
 */
class Main {
	//
	public static function getValidationManager($path, $filename, $db) {
		
		$satXsdValidator = new SatXsdValidator();
		// Ignorar error de timbre fiscal  
		$satXsdValidator->addIgnoreError(1845);
		$satStampValidator = new SatStampValidator($path);
		$satGeneralValidator = new SatGeneralValidator($db);
		$satSepomexValidator = new SatSepomexValidator($db);
		
		$manager = new SatXmlValidatorManager($path . $filename, $db);
		$comprobante = $manager->getComprobante();
		
		if ($comprobante != null) {
			$manager->addValidator($satXsdValidator);
			$manager->addValidator($satGeneralValidator);
			$manager->addValidator($satSepomexValidator);
			$manager->addValidator($satStampValidator);
			if ($comprobante->getVersion() == Comprobante::CFD_VERSION_30
				|| $comprobante->getVersion() == Comprobante::CFD_VERSION_32) {
				$satDocument3xValidator = new SatDocument3xValidator($db);
				$manager->addValidator($satDocument3xValidator);
			}
			if ($comprobante->getVersion() == Comprobante::CFD_VERSION_22 
				|| $comprobante->getVersion() == Comprobante::CFD_VERSION_20) {
				$satDocument2xValidator = new SatDocument2xValidator($db);
				$manager->addValidator($satDocument2xValidator);
			}
		}
		return $manager;
	}	
}
?>
