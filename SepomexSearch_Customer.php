<?php 
//
$PageSecurity = 2;

include('includes/session.inc');
$title = _('Direciones Sepomex');

$idOpener = "";
if(isset($_POST['idOpener'])) {
	$idOpener = $_POST['idOpener'];
} else if($_GET['idOpener']) {
	$idOpener = $_GET['idOpener'];
}

$direccion = "*";
if(isset($_POST['direccion'])) {
	$direccion = $_POST['direccion'];
}

$municipio = "*";
if(isset($_POST['municipio'])) {
	$municipio = $_POST['municipio'];
}

$cp = "*";
if(isset($_POST['cp'])) {
	$cp = $_POST['cp'];
}

$buscar = false;
if(isset($_POST['search'])) {
	$buscar = true;
}

include('includes/SQL_CommonFunctions.inc');
$funcion = 4;
include('includes/SecurityFunctions.inc');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
echo '<meta http-equiv="Content-Type" content="text/html; charset=' . _('ISO-8859-1') . '" />';
echo '<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE"/>';
echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
echo '<script type="text/javascript" src = "' . $rootpath . '/javascripts/MiscFunctions.js"></script>';
echo '<script type="text/javascript">';
echo "
	function updateFields(id) {
	
		var idOpener = '$idOpener';
		var direccion = document.getElementById('direccion'+id).value;
		var municipio = document.getElementById('municipio'+id).value;
		var estado = document.getElementById('estado'+id).value;
		var ciudad = document.getElementById('ciudad'+id).value;
		var cp = document.getElementById('cp'+id).value;
		var pais = document.getElementById('pais'+id).value;
		
		var windowOpener = window.opener;
		if(windowOpener != null) {	
			windowOpener.document.getElementById('Address2').value = direccion;
			windowOpener.document.getElementById('Address3').value =ciudad;
			windowOpener.document.getElementById('Address5').value = cp;
			windowOpener.document.getElementById('custpais').value = pais;
			windowOpener.document.getElementById('Address4').value = estado;
		}
		window.close();
		return false;
	}
";
echo '</script>';
echo '</head>';
echo '<body>';

echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Direcciones Sepomex') . '" alt=""> ' . _('Direcciones Sepomex') . '</p>';

echo "<div style='text-align:center; padding:.5em'><a href='javascript:window.close();'>CERRAR ESTA VENTANA</a></div>";
echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method="post">';

echo "<table style='margin: 0 auto; text-align: center'>";
echo "<tr>";
echo "<td>" . _('Codigo Postal') . ":</td>";
echo "<td><input size='30' type='text' name='cp' value='$cp' /> " . _('* = Todos') . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . _('Direccion') . ":</td>";
echo "<td><input size='30' type='text' name='direccion' value='$direccion' /> " . _('* = Todos') . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . _('Municipio') . ":</td>";
echo "<td><input size='30' type='text' name='municipio' value='$municipio' /> " . _('* = Todos') . "</td>";
echo "</tr>";
echo "<tr>";
echo "<td></td>";
echo "<td><input type='submit' name='search' value='" . _("Buscar") . "' /></td>";
echo "</tr>";
echo "</table>";

if(empty($direccion) OR empty($municipio) OR empty($cp)) {
	$buscar = false;
	prnMsg("Los campos son obligatorios", "error");
}

if($buscar) {
	
	$sql = "SELECT id, d_asenta AS direccion, D_mnpio AS municipio, d_estado AS estado, d_ciudad AS ciudad, d_codigo as cp 
		FROM sepomex WHERE 1";
	
	if($direccion != '*') {
		$sql .= " AND d_asenta LIKE '%$direccion%'";
	}
	
	if($municipio != '*') {
		$sql .= " AND D_mnpio LIKE '%$municipio%'";
	}
	
	if($cp != '*') {
		$sql .= " AND d_codigo = '$cp'";
	}
	
	$sql .= " ORDER BY direccion";
	
	$rs = DB_query($sql, $db);
	$i = 1;
	echo "<table style='margin: 0 auto; text-align: center'>";
	echo "<tr>";
	echo "<th>" . _("Direccion") . "</th>";
	echo "<th>" . _("Municipio") . "</th>";
	echo "<th>" . _("Estado") . "</th>";
	echo "<th>" . _("Ciudad") . "</th>";
	echo "<th>" . _("CP") . "</th>";
	echo "<th></th>";
	echo "</tr>";
while($row = DB_fetch_array($rs)) {		
		
		$liga = "";
		
		if($i % 2 == 0) {
			echo "<tr class='EvenTableRows'>";
		} else {
			echo "<tr class='OddTableRows'>";
		}
		
		$estado = str_replace(chr(193),'a', $row['estado']);
		$estado = str_replace(chr(201),'e', $estado);
		$estado = str_replace(chr(205),'i', $estado);
		$estado = str_replace(chr(211),'o', $estado);
		$estado = str_replace(ù,'u', $estado);
		$estado = strtoupper($estado);
		
		
		$direccion = utf8_encode($row['direccion']);
		$municipio = utf8_encode($row['municipio']);
		$estado = utf8_encode($row['estado']);
		$estado = strtoupper($estado);
		$ciudad = utf8_encode($row['ciudad']);
		
		
		echo "<td>{$direccion}</td>";
		echo "<td>{$municipio}</td>";
		echo "<td>{$row['estado']}</td>";
		echo "<td>{$ciudad}</td>";
		echo "<td>{$row['cp']}";
		echo "<input type='hidden' id='direccion{$row['id']}' value='{$direccion}' />";
		echo "<input type='hidden' id='municipio{$row['id']}' value='{$municipio}' />";
		echo "<input type='hidden' id='estado{$row['id']}' value='{$estado}' />";
		echo "<input type='hidden' id='ciudad{$row['id']}' value='{$ciudad}' />";
		echo "<input type='hidden' id='cp{$row['id']}' value='{$row['cp']}' />";
		echo "<input type='hidden' id='pais{$row['id']}' value='México'/>";
		echo "</td>";
		echo "<td><a href='#' onclick='return updateFields({$row['id']})' >" . _("Seleccionar") . "</a></td>";
		echo "</tr>";
		$i++;
	}
	
	echo "</table>";
}

echo "<input name='idOpener' type='hidden' id='idOpener' value='$idOpener' />";
echo "</form>";
echo "</body>";
echo "</html>";

?>