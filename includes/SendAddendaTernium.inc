<?php 

$debtortransId = $iddocto;


$rs = DB_query("
	SELECT * 
	FROM debtortrans 
	WHERE id = '$debtortransId'
", $db);



$monedaTxt = "";
$tipocambioTxt=1;

if($row = DB_fetch_array($rs)) {
	$monedaTxt	= $row["currcode"];
	$tipocambioTxt=number_format(1/$row["rate"],4);
}

$addenda 	  = $xml->createElement("Addenda");
$documento    = $xml->createElement("Generales");
$root->appendChild($addenda);
$addenda->appendChild($documento);
$TipoCambio = $xml->createElement("TipoCambio");
$moneda 	= $xml->createElement("Moneda");
$documento->appendChild($TipoCambio);
$documento->appendChild($moneda);
$TipoCambio->appendChild($xml->createTextNode($tipocambioTxt));
$moneda->appendChild($xml->createTextNode($monedaTxt));


?>
