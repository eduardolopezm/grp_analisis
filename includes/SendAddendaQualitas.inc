<?php
include_once('Numbers/Words.php');


$debtortransId 	= $iddocto;
$addendaRow		= array();

$SQL = "		
	SELECT
	xmlnode,
	value
	FROM valueaddenda
	INNER JOIN fieldaddenda
	ON valueaddenda.fieldid = fieldaddenda.fieldid
	WHERE debtorid = '$debtortransId'
";

$rs = DB_query($SQL, $db);
while($row = DB_fetch_array($rs)) {
	$addendaRow[$row['xmlnode']] = $row['value'];
}

$SQL = "
	SELECT 
	debtortrans.ovamount, 
	debtortrans.ovgst, 
	debtortrans.folio, 
	debtortrans.currcode, 
	debtortrans.paymentname, 
	debtortrans.id, 
	debtortrans.order_,
	debtortrans.origtrandate,
	debtortrans.discountpercent,
	legalbusinessunit.taxid,
	legalbusinessunit.legalname,
	legalbusinessunit.address1,
	legalbusinessunit.address2,
	legalbusinessunit.address3,
	legalbusinessunit.address4,
	legalbusinessunit.address5,
	legalbusinessunit.address6,
	legalbusinessunit.telephone,
	legalbusinessunit.fax,
	legalbusinessunit.email,
	www_users.realname,
	www_users.email AS useremail,
	www_users.phone,
	custbranch.brname,
	custbranch.taxid AS custtaxid,
	custbranch.braddress1,
	custbranch.braddress2,
	custbranch.braddress3,
	custbranch.braddress4,
	custbranch.braddress5,
	custbranch.braddress6,
	custbranch.brnumext
	FROM debtortrans
	INNER JOIN tags
	ON tags.tagref = debtortrans.tagref
	INNER JOIN legalbusinessunit
	ON legalbusinessunit.legalid = tags.legalid
	INNER JOIN salesorders
	ON salesorders.orderno = debtortrans.order_
	INNER JOIN www_users
	ON www_users.userid = salesorders.UserRegister
	INNER JOIN custbranch
	ON custbranch.debtorno = debtortrans.debtorno 
	AND custbranch.branchcode = debtortrans.branchcode
	WHERE id = '$debtortransId'
";

$rs 				= DB_query($SQL, $db);
$row				= DB_fetch_array($rs);
$row 				= array_map('utf8_encode', $row);
list($ser_, $fol_)  = explode('|', $row['folio']);
$SQL 				= "SELECT anioAprobacion, noAprobacion FROM AprobacionFolios WHERE serie='$ser_' AND $fol_ BETWEEN Inicial AND final AND rfc='{$row['taxid']}'";
$rsAprob			= DB_query($SQL, $db);
$aprobRow			= DB_fetch_array($rsAprob);
$row['ovamount']	= round($row['ovamount'], 2);
$row['ovgst']		= round($row['ovgst'], 2);
$total 				= $row['ovamount'] + $row['ovgst'];
$total				= round($total, 2);
$separa_			= explode(".", $total);
$montoctvs2 		= $separa_[1];
$montoctvs1 		= $separa_[0];
$totalletras		= Numbers_Words::toWords($montoctvs1, 'es');
$origtrandate		= str_replace(' ', 'T', $row['origtrandate']);
$addenda 			= $xml->createElement("cfdi:Addenda");
$ECFD				= $xml->createElement("ECFD");
$Documento			= $xml->createElement("Documento");
$Encabezado			= $xml->createElement("Encabezado");

$IdDoc				= $xml->createElement("IdDoc");
$AnoAprob			= $xml->createElement("AnoAprob", $aprobRow['anioAprobacion']);
$NroAprob			= $xml->createElement("NroAprob", $aprobRow['noAprobacion']);
$Tipo				= $xml->createElement("Tipo", "33");
$Folio				= $xml->createElement("Folio", $fol_);
$Estado				= $xml->createElement("Estado", "ORIGINAL");
$NumeroInterno		= $xml->createElement("NumeroInterno", "48");
$FechaEmis			= $xml->createElement("FechaEmis", $origtrandate);
$FormaPago			= $xml->createElement("FormaPago", "PAGO EN UNA SOLA EXHIBICION");
$Area				= $xml->createElement("Area");
$IdArea				= $xml->createElement("IdArea", "001");
$IdRevision			= $xml->createElement("IdRevision", "003");

$IdDoc->appendChild($NroAprob);
$IdDoc->appendChild($AnoAprob);
$IdDoc->appendChild($Tipo);
$IdDoc->appendChild($Folio);
$IdDoc->appendChild($Estado);
$IdDoc->appendChild($NumeroInterno);
$IdDoc->appendChild($FechaEmis);
$IdDoc->appendChild($FormaPago);
$IdDoc->appendChild($Area);
$Area->appendChild($IdArea);
$Area->appendChild($IdRevision);

$nodes = $xml->getElementsByTagName("Emisor");
foreach ($nodes as $node) {
	$emisorXP = $node;
}

$ExEmisor			= $xml->createElement("ExEmisor");
$RFCEmisor			= $xml->createElement("RFCEmisor", $emisorXP->getAttribute("rfc"));
$NmbEmisor			= $xml->createElement("NmbEmisor", $emisorXP->getAttribute("nombre"));
$CodigoExEmisor		= $xml->createElement("CodigoExEmisor");
$TpoCdgIntEmisor	= $xml->createElement("TpoCdgIntEmisor", "EXT");
$CdgIntEmisor		= $xml->createElement("CdgIntEmisor", "07748");

$ExEmisor->appendChild($RFCEmisor);
$ExEmisor->appendChild($NmbEmisor);
$ExEmisor->appendChild($CodigoExEmisor);
$CodigoExEmisor->appendChild($TpoCdgIntEmisor);
$CodigoExEmisor->appendChild($CdgIntEmisor);

$nodes = $xml->getElementsByTagName("DomicilioFiscal");
foreach ($nodes as $node) {
	$domXP = $node;
}

$DomFiscal		= $xml->createElement("DomFiscal");
$Calle			= $xml->createElement("Calle", $domXP->getAttribute("calle"));
$NroExterior	= $xml->createElement("NroExterior", $row['address6']);
$Colonia		= $xml->createElement("Colonia", $domXP->getAttribute("colonia"));
$Municipio		= $xml->createElement("Municipio", $domXP->getAttribute("municipio"));
$Estado			= $xml->createElement("Estado", $domXP->getAttribute("estado"));
$Pais			= $xml->createElement("Pais", $domXP->getAttribute("pais"));
$CodigoPostal	= $xml->createElement("CodigoPostal", $domXP->getAttribute("codigoPostal"));

$ExEmisor->appendChild($DomFiscal);
$DomFiscal->appendChild($Calle);
$DomFiscal->appendChild($NroExterior);
$DomFiscal->appendChild($Colonia);
$DomFiscal->appendChild($Municipio);
$DomFiscal->appendChild($Estado);
$DomFiscal->appendChild($Pais);
$DomFiscal->appendChild($CodigoPostal);

$nodes = $xml->getElementsByTagName("ExpedidoEn");
foreach ($nodes as $node) {
	$expXP = $node;
}

$LugarExped		= $xml->createElement("LugarExped");
$Calle2			= $xml->createElement("Calle", $expXP->getAttribute("calle"));
$NroExterior2	= $xml->createElement("NroExterior", $expXP->getAttribute("noExterior"));
$Colonia2		= $xml->createElement("Colonia", $expXP->getAttribute("colonia"));
$Municipio2		= $xml->createElement("Municipio", $expXP->getAttribute("municipio"));
$Estado2		= $xml->createElement("Estado", $expXP->getAttribute("estado"));
$Pais2			= $xml->createElement("Pais", $expXP->getAttribute("pais"));
$CodigoPostal2	= $xml->createElement("CodigoPostal", $expXP->getAttribute("codigoPostal"));

$ExEmisor->appendChild($LugarExped);
$LugarExped->appendChild($Calle2);
$LugarExped->appendChild($NroExterior2);
$LugarExped->appendChild($Colonia2);
$LugarExped->appendChild($Municipio2);
$LugarExped->appendChild($Estado2);
$LugarExped->appendChild($Pais2);
$LugarExped->appendChild($CodigoPostal2);

$ContactoEmisor	= $xml->createElement("ContactoEmisor");
$Tipo			= $xml->createElement("Tipo", "VENDEDOR");
$Nombre			= $xml->createElement("Nombre", $row['realname']);
$eMail			= $xml->createElement("eMail", $row['useremail']);
$Telefono		= $xml->createElement("Telefono", $row['phone']);

$ExEmisor->appendChild($ContactoEmisor);
$ContactoEmisor->appendChild($Tipo);
$ContactoEmisor->appendChild($Nombre);
$ContactoEmisor->appendChild($eMail);
$ContactoEmisor->appendChild($Telefono);

$nodes = $xml->getElementsByTagName("Receptor");
foreach ($nodes as $node) {
	$recepXP = $node;
}

$ExReceptor			= $xml->createElement("ExReceptor");
$RFCRecep			= $xml->createElement("RFCRecep", $recepXP->getAttribute("rfc"));
$NmbRecep			= $xml->createElement("NmbRecep", $recepXP->getAttribute("nombre"));
$CodigoExReceptor	= $xml->createElement("CodigoExReceptor");
$TpoCdgIntRecep		= $xml->createElement("TpoCdgIntRecep", "INT");
$CdgIntRecep		= $xml->createElement("CdgIntRecep", "BBOJ901017EHLERN98");

$ExReceptor->appendChild($RFCRecep);
$ExReceptor->appendChild($NmbRecep);
$ExReceptor->appendChild($CodigoExReceptor);
$CodigoExReceptor->appendChild($TpoCdgIntRecep);
$CodigoExReceptor->appendChild($CdgIntRecep);

$nodes = $xml->getElementsByTagName("Domicilio");
foreach ($nodes as $node) {
	$domXP = $node;
}

$DomFiscalRcp		= $xml->createElement("DomFiscalRcp");
$CalleRcp			= $xml->createElement("Calle", $domXP->getAttribute("calle"));
$NroExteriorRcp		= $xml->createElement("NroExterior", $row['brnumext']);
$ColoniaRcp			= $xml->createElement("Colonia", $domXP->getAttribute("colonia"));
$MunicipioRcp		= $xml->createElement("Municipio", $domXP->getAttribute("municipio"));
$EstadoRcp			= $xml->createElement("Estado", $domXP->getAttribute("estado"));
$PaisRcp			= $xml->createElement("Pais", $domXP->getAttribute("pais"));
$CodigoPostalRcp	= $xml->createElement("CodigoPostal", $domXP->getAttribute("codigoPostal"));

$ExReceptor->appendChild($DomFiscalRcp);
$DomFiscalRcp->appendChild($CalleRcp);
$DomFiscalRcp->appendChild($NroExteriorRcp);
$DomFiscalRcp->appendChild($ColoniaRcp);
$DomFiscalRcp->appendChild($MunicipioRcp);
$DomFiscalRcp->appendChild($EstadoRcp);
$DomFiscalRcp->appendChild($PaisRcp);
$DomFiscalRcp->appendChild($CodigoPostalRcp);

$LugarRecep			= $xml->createElement("LugarRecep");
$CalleRcp2			= $xml->createElement("Calle", $domXP->getAttribute("calle"));
$NroExteriorRcp2	= $xml->createElement("NroExterior", $row['brnumext']);
$ColoniaRcp2		= $xml->createElement("Colonia", $domXP->getAttribute("colonia"));
$MunicipioRcp2		= $xml->createElement("Municipio", $domXP->getAttribute("municipio"));
$EstadoRcp2			= $xml->createElement("Estado", $domXP->getAttribute("estado"));
$PaisRcp2			= $xml->createElement("Pais", $domXP->getAttribute("pais"));
$CodigoPostalRcp2	= $xml->createElement("CodigoPostal", $domXP->getAttribute("codigoPostal"));

$ExReceptor->appendChild($LugarRecep);
$LugarRecep->appendChild($CalleRcp2);
$LugarRecep->appendChild($NroExteriorRcp2);
$LugarRecep->appendChild($ColoniaRcp2);
$LugarRecep->appendChild($MunicipioRcp2);
$LugarRecep->appendChild($EstadoRcp2);
$LugarRecep->appendChild($PaisRcp2);
$LugarRecep->appendChild($CodigoPostalRcp2);

$ContactoReceptor	= $xml->createElement("ContactoReceptor");
$TipoRcp			= $xml->createElement("Tipo", "coordinador");
$NombreRcp			= $xml->createElement("Nombre", "Liliana Segura");
$eMailRcp			= $xml->createElement("eMail", "lsegura@qualitas.com.mx");
$TelefonoRcp		= $xml->createElement("Telefono", "50025500");

$ExReceptor->appendChild($ContactoReceptor);
$ContactoReceptor->appendChild($TipoRcp);
$ContactoReceptor->appendChild($NombreRcp);
$ContactoReceptor->appendChild($eMailRcp);
$ContactoReceptor->appendChild($TelefonoRcp);

if($row['currcode'] == 'MXN') {
	$totalletras = ucwords($totalletras) . " Pesos ". $montoctvs2 . "/100 M.N.";
} else {
	if($row['currcode'] == 'EUR') {
		$totalletras = ucwords($totalletras) . " Euros ". $montoctvs2 . "/100 EUR";
	} else {
		$totalletras = ucwords($totalletras) . " Dolares ". $montoctvs2 . "/100 USD";
	}
}

$Totales	= $xml->createElement("Totales");
$Moneda		= $xml->createElement("Moneda", $row['currcode']);
$SubTotal	= $xml->createElement("SubTotal", $row['ovamount']);
$MntDcto	= $xml->createElement("MntDcto", "0.00");
$PctDcto	= $xml->createElement("PctDcto", "0.00");
$MntBase	= $xml->createElement("MntBase", $row['ovamount']);
$MntImp		= $xml->createElement("MntImp", $row['ovgst']);
$VlrPagar	= $xml->createElement("VlrPagar", $total);
$VlrPalabras= $xml->createElement("VlrPalabras", $totalletras);

$Totales->appendChild($Moneda);
$Totales->appendChild($SubTotal);
$Totales->appendChild($MntDcto);
$Totales->appendChild($PctDcto);
$Totales->appendChild($MntBase);
$Totales->appendChild($MntImp);
$Totales->appendChild($VlrPagar);
$Totales->appendChild($VlrPalabras);

$ivatmp			= ($row["ovgst"] * 100 / $row["ovamount"]);
$ivatmp			= round($ivatmp);
$ExImpuestosb	= $xml->createElement("ExImpuestos");
$TipoImp		= $xml->createElement("TipoImp", "IVA");
$TasaImp		= $xml->createElement("TasaImp", $ivatmp);
$MontoImp		= $xml->createElement("MontoImp", $row['ovgst']);

$ExImpuestosb->appendChild($TipoImp);
$ExImpuestosb->appendChild($TasaImp);
$ExImpuestosb->appendChild($MontoImp);

$Poliza		= $xml->createElement("Poliza");
$Tipo		= $xml->createElement("Tipo", "autos");
$Numero		= $xml->createElement("Numero", $addendaRow['nopoliza']);
$INC		= $xml->createElement("INC", "0000");
$TpoCliente	= $xml->createElement("TpoCliente", $addendaRow['tipocliente']);
$NroReporte	= $xml->createElement("NroReporte", $addendaRow['noreportevehiculo']);
$NroSint	= $xml->createElement("NroSint", $addendaRow['nosiniestro']);

$Poliza->appendChild($Tipo);
$Poliza->appendChild($Numero);
$Poliza->appendChild($INC);
$Poliza->appendChild($TpoCliente);
$Poliza->appendChild($NroReporte);
$Poliza->appendChild($NroSint);

$Vehiculo	= $xml->createElement("Vehiculo");
$Tipo		= $xml->createElement("Tipo", $addendaRow['tipovehiculo']);
$Marca		= $xml->createElement("Marca", $addendaRow['marcavehiculo']);
$Modelo		= $xml->createElement("Modelo", $addendaRow['modelovehiculo']);
$Ano		= $xml->createElement("Ano", $addendaRow['aniovehiculo']);
$Color		= $xml->createElement("Color", $addendaRow['colorvehiculo']);
$NroSerie	= $xml->createElement("NroSerie", $addendaRow['serievehiculo']);
$Placa		= $xml->createElement("Placa", $addendaRow['placavehiculo']);

$Vehiculo->appendChild($Tipo);
$Vehiculo->appendChild($Marca);
$Vehiculo->appendChild($Modelo);
$Vehiculo->appendChild($Ano);
$Vehiculo->appendChild($Color);
$Vehiculo->appendChild($NroSerie);
$Vehiculo->appendChild($Placa);

$Referencia	= $xml->createElement("Referencia");
$NroLinRef	= $xml->createElement("NroLinRef", "1");
$TpoDocRef	= $xml->createElement("TpoDocRef", "FE");
$FolioRef	= $xml->createElement("FolioRef", $fol_);
$RazonRef	= $xml->createElement("RazonRef", "X");

$Referencia->appendChild($NroLinRef);
$Referencia->appendChild($TpoDocRef);
$Referencia->appendChild($FolioRef);
$Referencia->appendChild($RazonRef);

$TimeStamp		= $xml->createElement("TimeStamp", $origtrandate);
$Personalizados	= $xml->createElement("Personalizados");
$campoString1	= $xml->createElement("campoString");
$campoString2	= $xml->createElement("campoString");
$campoString3	= $xml->createElement("campoString");
$campoString4	= $xml->createElement("campoString");
$campoString5	= $xml->createElement("campoString");
$campoString6	= $xml->createElement("campoString");
$campoString7	= $xml->createElement("campoString");
$campoString8	= $xml->createElement("campoString");
$campoString9	= $xml->createElement("campoString");
$campoString10	= $xml->createElement("campoString");
$campoString11	= $xml->createElement("campoString");
$campoString12	= $xml->createElement("campoString");
$campoString13	= $xml->createElement("campoString");
$campoString14	= $xml->createElement("campoString");
$campoString15	= $xml->createElement("campoString");
$campoString16	= $xml->createElement("campoString");

cargaAtt($ECFD, array("version" => "1"));
cargaAtt($Documento, array("ID" => "T336875"));
cargaAtt($campoString1, array("name" => "montoManoObra"));
cargaAtt($campoString2, array("name" => "montoRefacciones"));
cargaAtt($campoString3, array("name" => "fechaFiniquito"));
cargaAtt($campoString4, array("name" => "fechaEntregaRefacciones"));
cargaAtt($campoString5, array("name" => "oficinaEntregaFactura"));
cargaAtt($campoString6, array("name" => "folioElectronico"));
cargaAtt($campoString7, array("name" => "montoDeducible"));
cargaAtt($campoString8, array("name" => "bancoDepositoDeducible"));
cargaAtt($campoString9, array("name" => "fechaDepositoDeducible"));
cargaAtt($campoString10, array("name" => "folioFicha_ReferenciaDeducible"));
cargaAtt($campoString11, array("name" => "montoDemerito_Recupero"));
cargaAtt($campoString12, array("name" => "bancoDepositoDemerito_Recupero"));
cargaAtt($campoString13, array("name" => "fechaDepositoDemerito_Recupero"));
cargaAtt($campoString14, array("name" => "folioFicha_ReferenciaDemerito"));
cargaAtt($campoString15, array("name" => "Default1"));
cargaAtt($campoString16, array("name" => "Default2"));

//$root = $xml->documentElement;
$root->appendChild($addenda);

$addenda->appendChild($ECFD);
$ECFD->appendChild($Documento);
$ECFD->appendChild($Personalizados);
$Documento->appendChild($Encabezado);
$Encabezado->appendChild($IdDoc);
$Encabezado->appendChild($ExEmisor);
$Encabezado->appendChild($ExReceptor);
$Encabezado->appendChild($Totales);
$Encabezado->appendChild($ExImpuestosb);
$Encabezado->appendChild($Poliza);
$Encabezado->appendChild($Vehiculo);
$Personalizados->appendChild($campoString1);
$Personalizados->appendChild($campoString2);
$Personalizados->appendChild($campoString3);
$Personalizados->appendChild($campoString4);
$Personalizados->appendChild($campoString5);
$Personalizados->appendChild($campoString6);
$Personalizados->appendChild($campoString7);
$Personalizados->appendChild($campoString8);
$Personalizados->appendChild($campoString9);
$Personalizados->appendChild($campoString10);
$Personalizados->appendChild($campoString11);
$Personalizados->appendChild($campoString12);
$Personalizados->appendChild($campoString13);
$Personalizados->appendChild($campoString14);
$Personalizados->appendChild($campoString15);
$Personalizados->appendChild($campoString16);

$SQL = "SELECT description, orderlineno, stkcode, quantity, unitprice, units FROM salesorderdetails INNER JOIN stockmaster ON stockmaster.stockid =  salesorderdetails.stkcode WHERE orderno = '{$row['order_']}'";
$rs = DB_query($SQL, $db);
$NroLinDetN = 1;

while($row2 = DB_fetch_array($rs)) {
	$Detalle 		= $xml->createElement("Detalle");
	$NroLinDet 		= $xml->createElement("NroLinDet", $NroLinDetN);
	$CdgItem 		= $xml->createElement("CdgItem");
	$TpoCodigo		= $xml->createElement("TpoCodigo", "INT");
	$VlrCodigo		= $xml->createElement("VlrCodigo", $row2['stkcode']);
	$DscLang 		= $xml->createElement("DscLang", "ES");
	$DscItem 		= $xml->createElement("DscItem", $row2['description']);
	$QtyItem 		= $xml->createElement("QtyItem", $row2['quantity']);
	$UnmdItem 		= $xml->createElement("UnmdItem", $row2['units']);
	$PrcNetoItem 	= $xml->createElement("PrcNetoItem", $row2['unitprice']);
	$MontoNetoItem 	= $xml->createElement("MontoNetoItem", $row2['unitprice']);

	$Detalle->appendChild($NroLinDet);
	$CdgItem->appendChild($TpoCodigo);
	$CdgItem->appendChild($VlrCodigo);
	$Detalle->appendChild($CdgItem);
	$Detalle->appendChild($DscLang);
	$Detalle->appendChild($DscItem);
	$Detalle->appendChild($QtyItem);
	$Detalle->appendChild($UnmdItem);
	$Detalle->appendChild($PrcNetoItem);
	$Detalle->appendChild($MontoNetoItem);
	$Documento->appendChild($Detalle);
	
	$NroLinDetN += 1;
}

$Documento->appendChild($Referencia);
$Documento->appendChild($TimeStamp);

if(empty($addendaRow['montodeducible'])) {
	$addendaRow['montodeducible'] = '0.00';
}

if(empty($addendaRow['fechadeposito'])) {
	$addendaRow['fechadeposito'] = '0000-00-00';
}

if(empty($addendaRow['bancodeposito'])) {
	$addendaRow['bancodeposito'] = 'X';
}

if($addendaRow['montodeducible'] == '0.00') {
	$addendaRow['fechadeposito'] 	= '0000-00-00';
	$addendaRow['bancodeposito'] 	= 'X';
	$addendaRow['foliodeducible'] 	= '';
}

$campoString1->appendChild($xml->createTextNode($addendaRow['montoobra']));
$campoString2->appendChild($xml->createTextNode($addendaRow['montorefaccionaria']));
$campoString3->appendChild($xml->createTextNode($origtrandate));
$campoString4->appendChild($xml->createTextNode($origtrandate));
$campoString5->appendChild($xml->createTextNode("083"));
$campoString6->appendChild($xml->createTextNode($addendaRow['folioelectronico']));
$campoString7->appendChild($xml->createTextNode($addendaRow['montodeducible']));
$campoString8->appendChild($xml->createTextNode(strtoupper($addendaRow['bancodeposito'])));
$campoString9->appendChild($xml->createTextNode($addendaRow['fechadeposito']));
$campoString10->appendChild($xml->createTextNode($addendaRow['foliodeducible']));
$campoString11->appendChild($xml->createTextNode($addendaRow['montodeducible']));
$campoString12->appendChild($xml->createTextNode(strtoupper($addendaRow['bancodeposito'])));
$campoString13->appendChild($xml->createTextNode($addendaRow['fechadeposito']));
$campoString14->appendChild($xml->createTextNode($addendaRow['foliodeducible']));
$campoString15->appendChild($xml->createTextNode("string"));
$campoString16->appendChild($xml->createTextNode("string"));

?>