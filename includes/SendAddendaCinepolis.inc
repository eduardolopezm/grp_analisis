<?php
$debtortransId = $iddocto;
$addendaRow	= array();

$SQL = "SELECT
	xmlnode,
	value
	FROM valueaddenda
	INNER JOIN fieldaddenda
	ON valueaddenda.fieldid = fieldaddenda.fieldid
	WHERE debtorid = '$debtortransId'";

while ($row = DB_fetch_array($rs)) {
	$value = $row["value"];
	$value = substr($value, 0, 10);
	$value = str_pad($value, 10, "0", STR_PAD_LEFT);
	$addendaRow[$row['xmlnode']] = $value;
}

$rs 				= DB_query($SQL, $db);
$idProveedorStr 	= "0000006784";
$addenda 			= $xml->createElement("cfdi:Addenda");
$cinepolisRecepcion	= $xml->createElement("CinepolisRecepcion");
$idProveedor		= $xml->createElement("idProveedor", $idProveedorStr);
$NotaRecepcion		= $xml->createElement("NotaRecepcion", $addendaRow['NotaRecepcion']);
$Contrato			= $xml->createElement("Contrato", $addendaRow['Contrato']);

cargaAtt($cinepolisRecepcion,
	array(
		"xmlns" 				=> "http://repository.edicomnet.com/schemas/mx/cfd/addenda",
		"xmlns:xsi" 			=> "http://www.w3.org/2001/XMLSchema-instance",
		"xsi:schemaLocation"	=> "http://repository.edicomnet.com/schemas/mx/cfd/addenda http://repository.edicomnet.com/schemas/mx/cfd/addenda/CinepolisRecepcion.xsd"
	)
);

// $root = $xml->documentElement;
$root->appendChild($addenda);
$addenda->appendChild($cinepolisRecepcion);
$cinepolisRecepcion->appendChild($idProveedor);
$cinepolisRecepcion->appendChild($NotaRecepcion);
$cinepolisRecepcion->appendChild($Contrato);
?>