<?php

include_once('Numbers/Words.php');

$addenda = $xml->createElement("cfdi:Addenda");
$xmlnscfdi=$xml->createAttribute("xmlns:cfdi");
$xmlnscfdi->value='http://www.sat.gob.mx/cfd/3';
$addenda->appendChild($xmlnscfdi);
$Addenda_Pemex =$xml->createElement("pm:Addenda_Pemex");
$Addenda_Pemex =$addenda->appendChild($Addenda_Pemex);

cargaAtt($Addenda_Pemex, array("xmlns:pm"=>"http://pemex.com/facturaelectronica/addenda/v2",
"xmlns:xsi"=>"http://www.w3.org/2001/XMLSchema-instance",
"schemaLocation"=>"http://pemex.com/facturaelectronica/addenda/v2 https://pemex.reachcore.com/schemas/addenda-pemex-v2.xsd"));

$debtortransId = $iddocto;

$SQL = "		
	SELECT
	xmlnode,
	value
	FROM valueaddenda
	INNER JOIN fieldaddenda
	ON valueaddenda.fieldid = fieldaddenda.fieldid
	WHERE debtorid = '$debtortransId' 
	order by fieldaddenda.fieldid";
$rs = DB_query($SQL, $db);
while($row = DB_fetch_array($rs)) {
	$nodo	=  $xml->createElement($row['xmlnode']);
	$Addenda_Pemex->appendChild($nodo);
	$nodo->appendChild($xml->createTextNode($row['value']));
}


?>