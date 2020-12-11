<?php 

$razonSocialXLS		= "";
$domicilioXLS		= "";
$coloniaXLS			= "";
$ciudadXLS 			= "";
$rfcXLS 			= "";
$faxXLS 			= "";
$proveedorXLS 		= "";
$telefonoXLS 		= "";
$contactoXLS 		= "";
$contactoRazonXLS 	= "";
$fechaSolicitudXLS 	= $_SESSION['PO'.$identifier]->Orig_OrderDate;
$noSolicitudXLS 	= $_SESSION['PO'.$identifier]->OrderNo;
$emailXLS 			= $_SESSION['FactoryManagerEmail'];
$emailEnviarXLS		= $_SESSION['POReceiveEmail'];
$totalNetoXLS 		= 0;
$totalListaXLS 		= 0;
$productsTemplate 	= array();
	
$resultLegal = DB_query("
	SELECT legalbusinessunit.*
	FROM tags
	INNER JOIN legalbusinessunit
	USING(legalid)
	WHERE tagref = '$tagref'"
, $db);
	
if($rowLegal = DB_fetch_array($resultLegal)) {
	$razonSocialXLS		= $rowLegal['legalname'];
	$domicilioXLS 		= $rowLegal['address1'];
	$coloniaXLS 		= $rowLegal['address2'];
	$ciudadXLS 			= $rowLegal['address3'];
	$rfcXLS 			= $rowLegal['taxid'];
	$faxXLS 			= $rowLegal['fax'];
	$contactoRazonXLS 	= $rowLegal['email'];
}
	
$resultSupplier = DB_query("
	SELECT *
	FROM suppliers
	WHERE supplierid = '" . $_SESSION['PO'.$identifier]->SupplierID . "'"
, $db);

if($rowSupplier = DB_fetch_array($resultSupplier)) {
	$proveedorXLS	= $rowSupplier['suppname'];
	$telefonoXLS 	= $rowSupplier['address6'];
	$contactoXLS 	= $rowSupplier['address1'];
}

foreach($_SESSION['PO'.$identifier]->LineItems as $POLine) {
	
	if($POLine->Deleted == False) {
		
		$resultTax = DB_query("
			SELECT taxrate
			FROM stockmaster
			INNER JOIN taxcategories
			ON stockmaster.taxcatid = taxcategories.taxcatid
			INNER JOIN taxauthrates
			ON taxcategories.taxcatid = taxauthrates.taxcatid
			WHERE stockid = '" . $POLine->StockID . "'"
		, $db);
		
		$taxRate = 0;
		if($rowTax = DB_fetch_array($resultTax)) {
			$taxRate = $rowTax['taxrate'];
		}
			
		$totalNetoXLS += $POLine->Price + ($POLine->Price * $taxRate);
		
		$productsTemplate[] = array(
			'claveXLS'		=> $POLine->StockID,
			'cantXLS'		=> $POLine->Quantity,
			'udmXLS'		=> $POLine->pcunit,
			'descXLS'		=> $POLine->ItemDescription,
			'tiempoXLS' 	=> '',
			'precioXLS' 	=> number_format($POLine->Price, 2),
			'precioIVAXLS'	=> number_format($POLine->Price + ($POLine->Price * $taxRate), 2),
			'cargoXLS'		=> '',
			'netoXLS'		=> number_format($POLine->Price + ($POLine->Price * $taxRate), 2),
			'listaXLS'		=> number_format($POLine->Price + ($POLine->Price * $taxRate), 2)
		);
	}
}//

		
$totalNetoXLS 	= number_format($totalNetoXLS, 2);
$totalListaXLS 	= $totalNetoXLS;
$pathTmp 		= "$rootpath/";

include_once realpath(dirname(__FILE__)) . '/HTMLTemplateSolicitud.php';
include_once realpath(dirname(__FILE__)) . '/mail.php';

$mail 		= new Mail();
$filename 	= $pathTmp . "archivos/Solicitud_" . $_SESSION['PO'.$identifier]->OrderNo . ".xls";
$to			= $_SESSION['FactoryManagerEmail'];

file_put_contents($filename, $template);

$mail->setTo($to);
$mail->setFrom("soporte@tecnoaplicada.com");
$mail->setSender("Soporte");
$mail->setSubject("Solicitud Cotizacion a Proveedor");
$mail->addAttachment($filename);
$mail->setHtml($template);
$mail->send();
?>