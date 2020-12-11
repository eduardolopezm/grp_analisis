<?php 

$debtortransId = $iddocto;
$encabezadoId  = 0;

$rs = DB_query("
	SELECT * 
	FROM debtortrans 
	WHERE id = '$debtortransId'
", $db);

/*if($row = DB_fetch_array($rs)) {
	$encabezadoId = $row["idorigen"];
}

$rs = DB_query("
	SELECT * 
	FROM facturacion.encabezado 
	WHERE id_encabezado = '$encabezadoId'
", $db);
 */

$noVersAddTxt 	= "";
$claseDocTxt	= "";
$noSociedadTxt	= "";
$noProveedorTxt	= "";
$noPedidoTxt	= "";
$monedaTxt		= "";
$noEntradaTxt	= "";
$noRemisionTxt	= "";
$noSocioTxt		= "";
$centroTxt		= "";
$finPerLiqTxt	= "";
$iniPerLiqTxt	= "";
$retencion1Txt	= "";
$retencion2Txt	= "";
$emailTxt		= "";
$datosATxt 		= "";
$tipoOpTxt		= "";

if($row = DB_fetch_array($rs)) {
	$noVersAddTxt 	= '01';
	//1=Factura,2=Consignacio?n,3=Retenciones, 8=Nota de Cargo,9=Nota de Cre?dito
	if($row['type']==21){
		$claseDocTxt=9;
	}elseif($row['type']==11 or $row['type']==13  ){
		$claseDocTxt=9;
	}else{
		$claseDocTxt=1;
	}
	
	
	
	//$claseDocTxt	= $row["add_femclaseDoc"];
	$noSociedadTxt	= '0123';//$row["add_femnoSociedad"];
	$noProveedorTxt	= '0000019850';//$row["add_femnoProveedor"];
	$noPedidoTxt	= $row["nopedidof"];
	$monedaTxt	= $row["currcode"];
	$noEntradaTxt	= $row["noentradaf"];
	$noRemisionTxt	= $row["noremisionf"];
	$noSocioTxt = '';//$row["add_femnoSocio"];
	$centroTxt = '';//$row["add_femCentro"];
	$finPerLiqTxt = '';//$row["add_femFinPerLiq"];
	//$iniPerLiqTxt	= $row["add_femIniPerLiq"];
	$retencion1Txt	= '';//$row["add_femretencion1"];
	$retencion2Txt	= '';//$row["add_femretencion2"];
	$emailTxt	= $_SESSION["FEMSAMail"];
	$datosATxt 	= '';//$row["add_femdatosAdicionales"];
	$tipoOpTxt	= '';//$row["add_femtipoOperacion"];
}

$addenda 	  = $xml->createElement("Addenda");
$documento    = $xml->createElement("Documento");
$facturaFemsa = $xml->createElement("FacturaFemsa");

$root->appendChild($addenda);
$addenda->appendChild($documento);
$documento->appendChild($facturaFemsa);

$noVersAdd 	 = $xml->createElement("noVersAdd");
$claseDoc 	 = $xml->createElement("claseDoc");
$noSociedad  = $xml->createElement("noSociedad");
$noProveedor = $xml->createElement("noProveedor");
$noPedido	 = $xml->createElement("noPedido");
$moneda 	 = $xml->createElement("moneda");
$noEntrada   = $xml->createElement("noEntrada");
$noRemision  = $xml->createElement("noRemision");
$noSocio     = $xml->createElement("noSocio");
$centro      = $xml->createElement("centro");
$finPerLiq   = $xml->createElement("finPerLiq");
$iniPerLiq   = $xml->createElement("iniPerLiq");
$retencion1  = $xml->createElement("retencion1");
$retencion2  = $xml->createElement("retencion2");
$email       = $xml->createElement("email");
$datosA      = $xml->createElement("datosAdicionales");
$tipoOp      = $xml->createElement("tipoOperacion");

$facturaFemsa->appendChild($noVersAdd);
$facturaFemsa->appendChild($claseDoc);
$facturaFemsa->appendChild($noSociedad);
$facturaFemsa->appendChild($noProveedor);
$facturaFemsa->appendChild($noPedido);
$facturaFemsa->appendChild($moneda);
$facturaFemsa->appendChild($noEntrada);
$facturaFemsa->appendChild($noRemision);
$facturaFemsa->appendChild($noSocio);
$facturaFemsa->appendChild($centro);
$facturaFemsa->appendChild($finPerLiq);
$facturaFemsa->appendChild($iniPerLiq);
$facturaFemsa->appendChild($retencion1);
$facturaFemsa->appendChild($retencion2);
$facturaFemsa->appendChild($email);
$facturaFemsa->appendChild($datosA);
$facturaFemsa->appendChild($tipoOp);

$noVersAdd->appendChild($xml->createTextNode($noVersAddTxt));
$claseDoc->appendChild($xml->createTextNode($claseDocTxt));
$noSociedad->appendChild($xml->createTextNode($noSociedadTxt));
$noProveedor->appendChild($xml->createTextNode($noProveedorTxt));
$noPedido->appendChild($xml->createTextNode($noPedidoTxt));
$moneda->appendChild($xml->createTextNode($monedaTxt));
$noEntrada->appendChild($xml->createTextNode($noEntradaTxt));
$noRemision->appendChild($xml->createTextNode($noRemisionTxt));
$noSocio->appendChild($xml->createTextNode($noSocioTxt));
$centro->appendChild($xml->createTextNode($centroTxt));
$finPerLiq->appendChild($xml->createTextNode($finPerLiqTxt));
$iniPerLiq->appendChild($xml->createTextNode($iniPerLiqTxt));
$retencion1->appendChild($xml->createTextNode($retencion1Txt));
$retencion2->appendChild($xml->createTextNode($retencion2Txt));
$email->appendChild($xml->createTextNode($emailTxt));
$datosA->appendChild($xml->createTextNode(ReglasXCadena($datosATxt)));
$tipoOp->appendChild($xml->createTextNode($tipoOpTxt));

?>