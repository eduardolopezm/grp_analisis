<?php

$debtortransId = $iddocto;

$SQL 				= "SELECT ovamount, ovgst, folio, currcode, paymentname, id, order_, discountpercent FROM debtortrans WHERE id = '$debtortransId'";
$rs 				= DB_query($SQL, $db);
$row				= DB_fetch_array($rs);
$addenda 			= $xml->createElement("Addenda");
$facturaInter		= $xml->createElement("if:FacturaInterfactura");
$emisor				= $xml->createElement("if:Emisor");
$receptor			= $xml->createElement("if:Receptor");
$encabezado			= $xml->createElement("if:Encabezado");

cargaAtt($facturaInter,
	array(
		"xmlns:if" 		=> "https://www.interfactura.com/Schemas/Documentos",
		"TipoDocumento"	=> "Factura"
	)
);

cargaAtt($emisor,
	array(
		"RI" => "Indicar_RI_Emisor"
	)
);

cargaAtt($receptor,
	array(
		"RI" => "Indicar_RI_Receptor"
	)
);

$ivatmp	= ($row["ovgst"] * 100 / $row["ovamount"]);
$ivatmp	= round($ivatmp);

cargaAtt($encabezado,
	array(
		"formaDePago"		=> $row["paymentname"],
		"SubTotal"			=> $row["ovamount"],
		"Descuento"			=> $row["discountpercent"],
		"IVAPCT"			=> $ivatmp,
		"Iva"				=> $row["ovgst"],
		"Total"				=> ($row["ovamount"] + $row["ovgst"]),
		"Moneda"			=> $row["currcode"],
		"TipoDocumento" 	=> "Factura",
		"TipoComprobante"	=> "[Campo libre 4]",
		"ProcesoId"			=> "1",
		"NumeroAtencion"	=> "[Campo libre 5]",
		"NumeroFolio"		=> "[Campo libre 6]",
		"NumeroDictamen"	=> "[Campo libre 7]",
		"NombreSolicitante"	=> "[Campo libre 1]",
		"PaternoSolicitante"=> "[Campo libre 2]",
		"MaternoSolicitante"=> "[Campo libre 3]",
		"DiasHospitalizacion"=>"[Campo libre 10]",
		"ClaveTipoAtencion" => "[Campo libre 8]",
		"NombreTipoAtencion"=> "[Campo libre 9]"
	)
);


//$root = $xml->documentElement;
$root->appendChild($addenda);
$addenda->appendChild($facturaInter);
$facturaInter->appendChild($emisor);
$facturaInter->appendChild($receptor);
$facturaInter->appendChild($encabezado);

$SQL = "SELECT description, orderlineno, stkcode, quantity, unitprice FROM salesorderdetails INNER JOIN stockmaster ON stockmaster.stockid =  salesorderdetails.stkcode WHERE orderno = '{$row['order_']}'";
$rs = DB_query($SQL, $db);
while($row = DB_fetch_array($rs)) {
	$cuerpo	= $xml->createElement("if:Cuerpo");
	cargaAtt($cuerpo,
		array(
			"Renglon"	=> $row['orderlineno'],
			"Cantidad"	=> $row['quantity'],
			"Codigo"	=> $row['stkcode'],
			"Concepto"	=> $row['description'],
			"PUnitario"	=> $row['unitprice'],
			"Importe"	=> ($row['unitprice'] * $row['quantity'])
		)
	);
	$encabezado->appendChild($cuerpo);
}
?>