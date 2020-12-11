<?php

include_once('Numbers/Words.php');

$debtortransId 	= $iddocto;
$addendaRow		= array();

$SQL = "SELECT
	xmlnode,
	value
	FROM valueaddenda
	INNER JOIN fieldaddenda
	ON valueaddenda.fieldid = fieldaddenda.fieldid
	WHERE debtorid = '$debtortransId'";

$rs = DB_query($SQL, $db);
while ($row = DB_fetch_array($rs)) {
	$addendaRow[$row['xmlnode']] = strtoupper($row['value']);
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
$INILISTAPROD		= $xml->createElement("INILISTAPROD");
$Encabezado			= $xml->createElement("Encabezado");

$IdDoc				= $xml->createElement("IdDoc");
$AnoAprob			= $xml->createElement("AnoAprob", $aprobRow['anioAprobacion']);
$NroAprob			= $xml->createElement("NroAprob", $aprobRow['noAprobacion']);
$Tipo				= $xml->createElement("Tipo", "33");
//$Serie				= $xml->createElement("Serie");
$Folio				= $xml->createElement("Folio", $fol_);
$Estado				= $xml->createElement("Estado", "ORIGINAL");
$FechaEmis			= $xml->createElement("FechaEmis", $origtrandate);
$FormaPago			= $xml->createElement("FormaPago", "PAGO EN UNA SOLA EXHIBICION");
$Area				= $xml->createElement("Area");
$IdArea				= $xml->createElement("IdArea", "001");
$IdRevision			= $xml->createElement("IdRevision", "003");

$IdDoc->appendChild($NroAprob);
$IdDoc->appendChild($AnoAprob);
$IdDoc->appendChild($Tipo);
//$IdDoc->appendChild($Serie);
$IdDoc->appendChild($Folio);
$IdDoc->appendChild($Estado);
$IdDoc->appendChild($FechaEmis);
$IdDoc->appendChild($FormaPago);
$IdDoc->appendChild($Area);
$Area->appendChild($IdArea);
$Area->appendChild($IdRevision);

$ExEmisor			= $xml->createElement("ExEmisor");
$RFCEmisor			= $xml->createElement("RFCEmisor", strtoupper($row['taxid']));
$NmbEmisor			= $xml->createElement("NmbEmisor", strtoupper($row['legalname']));
$CodigoExEmisor		= $xml->createElement("CodigoExEmisor");
$TpoCdgIntEmisor	= $xml->createElement("TpoCdgIntEmisor", "EXT");
$CdgIntEmisor		= $xml->createElement("CdgIntEmisor", "32868");
$Sucursal			= $xml->createElement("Sucursal");

$ExEmisor->appendChild($RFCEmisor);
$ExEmisor->appendChild($NmbEmisor);
$ExEmisor->appendChild($CodigoExEmisor);
$ExEmisor->appendChild($Sucursal);
$CodigoExEmisor->appendChild($TpoCdgIntEmisor);
$CodigoExEmisor->appendChild($CdgIntEmisor);

$DomFiscal		= $xml->createElement("DomFiscal");
$Calle			= $xml->createElement("Calle", strtoupper($row['address1']));
$NroExterior	= $xml->createElement("NroExterior", strtoupper($row['address6']));
$Colonia		= $xml->createElement("Colonia", strtoupper($row['address2']));
$Localidad		= $xml->createElement("Localidad", strtoupper($row['address2']));
$Municipio		= $xml->createElement("Municipio", strtoupper($row['address3']));
$Estado			= $xml->createElement("Estado", strtoupper($row['address4']));
$Pais			= $xml->createElement("Pais", 'MEXICO');
$CodigoPostal	= $xml->createElement("CodigoPostal", strtoupper($row['address5']));

$ExEmisor->appendChild($DomFiscal);
$DomFiscal->appendChild($Calle);
$DomFiscal->appendChild($NroExterior);
$DomFiscal->appendChild($Colonia);
$DomFiscal->appendChild($Localidad);
$DomFiscal->appendChild($Municipio);
$DomFiscal->appendChild($Estado);
$DomFiscal->appendChild($Pais);
$DomFiscal->appendChild($CodigoPostal);

$LugarExped		= $xml->createElement("LugarExped");
$Calle2			= $xml->createElement("Calle", strtoupper($row['address1']));
$NroExterior2	= $xml->createElement("NroExterior", "2");
$Colonia2		= $xml->createElement("Colonia", strtoupper($row['address2']));
$Localidad2		= $xml->createElement("Localidad", strtoupper($row['address2']));
$Municipio2		= $xml->createElement("Municipio", strtoupper($row['address3']));
$Estado2		= $xml->createElement("Estado", strtoupper($row['address4']));
$Pais2			= $xml->createElement("Pais", 'MEXICO');
$CodigoPostal2	= $xml->createElement("CodigoPostal", strtoupper($row['address5']));

$ExEmisor->appendChild($LugarExped);
$LugarExped->appendChild($Calle2);
$LugarExped->appendChild($NroExterior2);
$LugarExped->appendChild($Colonia2);
$LugarExped->appendChild($Localidad2);
$LugarExped->appendChild($Municipio2);
$LugarExped->appendChild($Estado2);
$LugarExped->appendChild($Pais2);
$LugarExped->appendChild($CodigoPostal2);

if (empty($row['phone'])) {
	$row['phone'] = '4107779';
}

$ContactoEmisor	= $xml->createElement("ContactoEmisor");
$Tipo			= $xml->createElement("Tipo", "MATRIZ");
$Nombre			= $xml->createElement("Nombre", strtoupper($row['realname']));
$eMail			= $xml->createElement("eMail", strtoupper($row['useremail']));
$Telefono		= $xml->createElement("Telefono", $row['phone']);

$ExEmisor->appendChild($ContactoEmisor);
$ContactoEmisor->appendChild($Tipo);
$ContactoEmisor->appendChild($Nombre);
$ContactoEmisor->appendChild($eMail);
$ContactoEmisor->appendChild($Telefono);

$ExReceptor	= $xml->createElement("ExReceptor");
$RFCRecep	= $xml->createElement("RFCRecep", "MTE440316E54");
$NmbRecep	= $xml->createElement("NmbRecep", "MAPFRE TEPEYAC, S.A.");
$Sucursal2	= $xml->createElement("Sucursal", "CORPORATIVO MAPFRE TEPEYAC");

$ExReceptor->appendChild($RFCRecep);
$ExReceptor->appendChild($NmbRecep);
$ExReceptor->appendChild($Sucursal2);

$DomFiscalRcp		= $xml->createElement("DomFiscalRcp");
$CalleRcp			= $xml->createElement("Calle", "AV. PASEO DE LA REFORMA");
$NroExteriorRcp		= $xml->createElement("NroExterior", "243");
$ColoniaRcp			= $xml->createElement("Colonia", "CUAUHTEMOC");
$LocalidadRcp		= $xml->createElement("Localidad", "DISTRITO FEDERAL");
$MunicipioRcp		= $xml->createElement("Municipio", "CUAUHTEMOC");
$EstadoRcp			= $xml->createElement("Estado", "DISTRITO FEDERAL");
$PaisRcp			= $xml->createElement("Pais", "MEXICO");
$CodigoPostalRcp	= $xml->createElement("CodigoPostal", "06500");

$ExReceptor->appendChild($DomFiscalRcp);
$DomFiscalRcp->appendChild($CalleRcp);
$DomFiscalRcp->appendChild($NroExteriorRcp);
$DomFiscalRcp->appendChild($ColoniaRcp);
$DomFiscalRcp->appendChild($LocalidadRcp);
$DomFiscalRcp->appendChild($MunicipioRcp);
$DomFiscalRcp->appendChild($EstadoRcp);
$DomFiscalRcp->appendChild($PaisRcp);
$DomFiscalRcp->appendChild($CodigoPostalRcp);

$LugarRecep			= $xml->createElement("LugarRecep");
$CalleRcp2			= $xml->createElement("Calle", "AV. PASEO DE LA REFORMA");
$NroExteriorRcp2	= $xml->createElement("NroExterior", "243");
$ColoniaRcp2		= $xml->createElement("Colonia", "CUAUHTEMOC");
$LocalidadRcp2		= $xml->createElement("Localidad", "DISTRITO FEDERAL");
$MunicipioRcp2		= $xml->createElement("Municipio", "CUAUHTEMOC");
$EstadoRcp2			= $xml->createElement("Estado", "DISTRITO FEDERAL");
$PaisRcp2			= $xml->createElement("Pais", "MEXICO");
$CodigoPostalRcp2	= $xml->createElement("CodigoPostal", "06500");

$ExReceptor->appendChild($LugarRecep);
$LugarRecep->appendChild($CalleRcp2);
$LugarRecep->appendChild($NroExteriorRcp2);
$LugarRecep->appendChild($ColoniaRcp2);
$LugarRecep->appendChild($LocalidadRcp2);
$LugarRecep->appendChild($MunicipioRcp2);
$LugarRecep->appendChild($EstadoRcp2);
$LugarRecep->appendChild($PaisRcp2);
$LugarRecep->appendChild($CodigoPostalRcp2);

$ContactoReceptor	= $xml->createElement("ContactoReceptor");
$TipoRcp			= $xml->createElement("Tipo", "COORDINADOR");
$NombreRcp			= $xml->createElement("Nombre", "ANGEL RAMIREZ");

$ExReceptor->appendChild($ContactoReceptor);
$ContactoReceptor->appendChild($TipoRcp);
$ContactoReceptor->appendChild($NombreRcp);

if ($row['currcode'] == 'MXN') {
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
$FctConv	= $xml->createElement("FctConv", "1");
$SubTotal	= $xml->createElement("SubTotal", $row['ovamount']);
$MntBase	= $xml->createElement("MntBase", $row['ovamount']);
$MntImp		= $xml->createElement("MntImp", $row['ovgst']);
$VlrPagar	= $xml->createElement("VlrPagar", $total);
$VlrPalabras= $xml->createElement("VlrPalabras", strtoupper($totalletras));

$Totales->appendChild($Moneda);
$Totales->appendChild($FctConv);
$Totales->appendChild($SubTotal);
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
$Tipo		= $xml->createElement("Tipo", "AUTOS");
$Numero		= $xml->createElement("Numero", $addendaRow['nopoliza']);
$INC		= $xml->createElement("INC", "0");
$TpoCliente	= $xml->createElement("TpoCliente", $addendaRow['tipocliente']);
$NroReporte	= $xml->createElement("NroReporte", "0");
$NroSint	= $xml->createElement("NroSint", $addendaRow['nosiniestro']);
$NmbAseg	= $xml->createElement("NmbAseg", $addendaRow['nombreasegurador']);

$Poliza->appendChild($Tipo);
$Poliza->appendChild($Numero);
$Poliza->appendChild($INC);
$Poliza->appendChild($TpoCliente);
$Poliza->appendChild($NroReporte);
$Poliza->appendChild($NroSint);
$Poliza->appendChild($NmbAseg);

$Vehiculo	= $xml->createElement("Vehiculo");
$Tipo		= $xml->createElement("Tipo", $addendaRow['tipovehiculo']);
$Marca		= $xml->createElement("Marca", $addendaRow['marcavehiculo']);
$Modelo		= $xml->createElement("Modelo", $addendaRow['modelovehiculo']);
$Ano		= $xml->createElement("Ano", $addendaRow['aniovehiculo']);
$Color		= $xml->createElement("Color", $addendaRow['colorvehiculo']);
$NroChasis	= $xml->createElement("NroChasis", $addendaRow['nochasis']);
$NroSerie	= $xml->createElement("NroSerie", $addendaRow['serievehiculo']);
$Placa		= $xml->createElement("Placa", $addendaRow['placavehiculo']);

$Vehiculo->appendChild($Tipo);
$Vehiculo->appendChild($Marca);
$Vehiculo->appendChild($Modelo);
$Vehiculo->appendChild($Ano);
$Vehiculo->appendChild($Color);
$Vehiculo->appendChild($NroChasis);
$Vehiculo->appendChild($NroSerie);
$Vehiculo->appendChild($Placa);

$TimeStamp		= $xml->createElement("TimeStamp", $origtrandate);
$Personalizados	= $xml->createElement("Personalizados");
$campoString1	= $xml->createElement("campoString");
$campoString2	= $xml->createElement("campoString");

cargaAtt($ECFD, array(
		"version" => "1.0",
		"xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
		"xmlns" => "http://www.fact.com.mx/schema/mte",
		"xsi:schemaLocation" => "http://www.fact.com.mx/schema/mte http://www.mysuitemex.com/fact/schema/mte_2013.xsd",
	)
);
$folioDoc = "T33" . $ser_ . $fol_;
cargaAtt($Documento, array("ID" => $folioDoc));
cargaAtt($campoString1, array("name" => "IdArea"));
cargaAtt($campoString2, array("name" => "Tramitador"));

//$root = $xml->documentElement;
$root->appendChild($addenda);
$addenda->appendChild($ECFD);
$ECFD->appendChild($Documento);
$ECFD->appendChild($Personalizados);
$Documento->appendChild($Encabezado);
//$Documento->appendChild($INILISTAPROD);
$Encabezado->appendChild($IdDoc);
$Encabezado->appendChild($ExEmisor);
$Encabezado->appendChild($ExReceptor);
$Encabezado->appendChild($Totales);
$Encabezado->appendChild($ExImpuestosb);
$Encabezado->appendChild($Poliza);
$Encabezado->appendChild($Vehiculo);
$Personalizados->appendChild($campoString1);
$Personalizados->appendChild($campoString2);

$SQL = "SELECT taxrate, description, orderlineno, stkcode, quantity, unitprice, units FROM salesorderdetails INNER JOIN stockmaster ON stockmaster.stockid =  salesorderdetails.stkcode INNER JOIN taxauthrates ON stockmaster.taxcatid = taxauthrates.taxcatid WHERE orderno = '{$row['order_']}'";
$rs = DB_query($SQL, $db);
while ($row2 = DB_fetch_array($rs)) {
	$ivatmp 		= round($row2['taxrate'] * 100);
	$ivastock		= round(($row2['unitprice'] * $row2['taxrate']), 2);
	$Detalle 		= $xml->createElement("Detalle");
	$NroLinDet 		= $xml->createElement("NroLinDet", ($row2['orderlineno'] + 1));
	$CdgItem 		= $xml->createElement("CdgItem");
	$TpoCodigo		= $xml->createElement("TpoCodigo", "INT");
	$VlrCodigo		= $xml->createElement("VlrCodigo", strtoupper($row2['stkcode']));
	$IndListaItem	= $xml->createElement("IndListaItem", "0");
	$TpoListaItem	= $xml->createElement("TpoListaItem", "0");
	$DscLang 		= $xml->createElement("DscLang", "ES");
	$descItem 		= strtoupper($row2['description']);
	$descItem		= str_replace('&AMP;', '', $descItem);
	$descItem		= str_replace('&QUOT;', '', $descItem);
	$DscItem 		= $xml->createElement("DscItem", $descItem);
	$QtyItem 		= $xml->createElement("QtyItem", $row2['quantity']);
	$UnmdItem 		= $xml->createElement("UnmdItem", strtoupper($row2['units']));
	$PrcBrutoItem 	= $xml->createElement("PrcBrutoItem", $row2['unitprice']);
	$PrcNetoItem 	= $xml->createElement("PrcNetoItem", $row2['unitprice']);
	$ImpuestosDet 	= $xml->createElement("ImpuestosDet");
	$TipoImp		= $xml->createElement("TipoImp", "IVA");
	$TasaImp		= $xml->createElement("TasaImp", $ivatmp);
	$MontoImp		= $xml->createElement("MontoImp", $ivastock);
	$MontoBrutoItem = $xml->createElement("MontoBrutoItem", $row2['unitprice']);
	$MontoNetoItem 	= $xml->createElement("MontoNetoItem", $row2['unitprice']);
	$MontoTotalItem = $xml->createElement("MontoTotalItem", $row2['unitprice'] + $ivastock);

	$Detalle->appendChild($NroLinDet);
	$CdgItem->appendChild($TpoCodigo);
	$CdgItem->appendChild($VlrCodigo);
	$Detalle->appendChild($CdgItem);
	$Detalle->appendChild($IndListaItem);
	$Detalle->appendChild($TpoListaItem);
	$Detalle->appendChild($DscLang);
	$Detalle->appendChild($DscItem);
	$Detalle->appendChild($QtyItem);
	$Detalle->appendChild($UnmdItem);
	$Detalle->appendChild($PrcBrutoItem);
	$Detalle->appendChild($PrcNetoItem);
	$Detalle->appendChild($ImpuestosDet);
	$ImpuestosDet->appendChild($TipoImp);
	$ImpuestosDet->appendChild($TasaImp);
	$ImpuestosDet->appendChild($MontoImp);
	$Detalle->appendChild($MontoBrutoItem);
	$Detalle->appendChild($MontoNetoItem);
	$Detalle->appendChild($MontoTotalItem);
	$Documento->appendChild($Detalle);
}

$Documento->appendChild($TimeStamp);

$campoString1->appendChild($xml->createTextNode($addendaRow['idarea']));
$campoString2->appendChild($xml->createTextNode($addendaRow['tramitador']));

?>