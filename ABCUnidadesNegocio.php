<?php
/*
* AHA
* 7-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/
date_default_timezone_set('America/Mexico_City');

include('includes/session.inc');
$title = _('ABC Unidades de Negocio');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/MiscFunctions.inc');
// $funcion = 1272;
// include('includes/SecurityFunctions.inc');

// Datos de tabla que almacena la relacion muchos a muchos
$tableName = 'sec_unegsxuser';
$tableIdColumn = 'tagref';
$tableColumns = array('userid', 'tagref');

// Datos de tabla principal
$mainTableName = 'tags';
$mainTableIdColumn = 'tagref';
$mainTableTextColumn = 'tagdescription';

$optionSelected = 'users';
if (isset($_POST['optionSelected'])) {
	$optionSelected = $_POST['optionSelected'];
}

$datoSearch = "";
if (isset($_POST['datoSearch'])) {
	$datoSearch = $_POST['datoSearch']; 
}

$profileSearch = "";
if (isset($_POST['profileSearch'])) {
	$profileSearch = $_POST['profileSearch'];
}

$userSearch = "";
if (isset($_POST['userSearch'])) {
	$userSearch = $_POST['userSearch'];
}

$users = $_POST['users'];
$profiles = $_POST['profiles'];
$datos = $_POST['datos'];
$usersTemp = array();

if (isset($_POST['saveBtn'])) {
	if ($optionSelected == 'users') {
		$usersTemp = $_POST['users'];
	} else if ($optionSelected == 'profiles') {
		$usersTemp = getUsersByProfiles($profiles, $db);
	}
	
	if (empty($usersTemp) == false) {
	
		foreach ($usersTemp as $user) {
			$values = array();
			$sql = "DELETE FROM $tableName WHERE userid = '$user'";
			DB_query($sql, $db);
				
			if (empty($datos) == false)
			{
				foreach ($datos as $dato) {
					$values[] = "('$user', '$dato')";
				}
				$sql = "INSERT INTO $tableName (" . implode(',', $tableColumns) . ") VALUES " . implode(',', $values);
				DB_query($sql, $db);
			}
		}
	}
}

if (isset($_POST['showByUser'])) {
	$datos = array();
	$userid = $_POST['showByUser'];
	$sql = "SELECT " . implode(",", $tableColumns) . " FROM $tableName WHERE userid = '$userid'";
	$rs = DB_query($sql, $db);
	while ($row = DB_fetch_array($rs)) {
		$datos[] = $row[$tableIdColumn];
	}
}

if (isset($_POST['showByProfile'])) {
	$datos = array();
	$usersTemp = array();
	$profileid = $_POST['showByProfile'];
	$sql = "SELECT DISTINCT userid FROM sec_profilexuser WHERE profileid = '$profileid'";
	$rs = DB_query($sql, $db);
	while ($row = DB_fetch_array($rs)) {
		$usersTemp[] = "'" . $row['userid'] . "'";
	}
	$sql = "SELECT " . implode(",", $tableColumns) . " FROM $tableName WHERE userid IN (" . implode(",", $usersTemp) . ")";
	$rs = DB_query($sql, $db);
	while ($row = DB_fetch_array($rs)) {
		$datos[] = $row[$tableIdColumn];
	}
}

function getUsersByProfiles($profiles, &$db) {
	$users = array();
	if (empty($profiles) == false) {
		$sql = "SELECT DISTINCT userid FROM sec_profilexuser WHERE profileid IN (" . implode(',', $profiles). ")";
		$rs = DB_query($sql, $db);
		while ($row = DB_fetch_array($rs)) {
			$users[] = $row['userid'];
		}
	}
	return $users;
}

echo "<br><div class='titulos_sec' style='text-align:center;'>$title</div><br>";

echo "<form method='post'>";
echo "<div id='container' style='margin:0 auto; width:603px;'>";
	
	echo "<div style='clear:both; border-bottom:3px solid lightgray'>";
		if ($optionSelected == 'users') {
			echo "<input style='padding:.2em;margin:0; border:1px solid lightgray; color:white; background-color:#056693; font-size:1.1em; cursor:pointer' onclick='onFilterByUser()' type='button' name='ByUser' id='ByUser' value='" . _("Por Usuario") . "' />";
		} else {
			echo "<input style='padding:.2em;margin:0; border:1px solid lightgray; background-color:#fff; font-size:1.1em; cursor:pointer' onclick='onFilterByUser()' type='button' name='ByUser' id='ByUser' value='" . _("Por Usuario") . "' />";
		} 

		if ($optionSelected == 'profiles') {
			echo "<input style='padding:.2em;margin:0; border:1px solid lightgray; color:white; background-color:#056693; font-size:1.1em; cursor:pointer' onclick='onFilterByProfile()' type='button' name='ByProfile' id='ByProfile' value='" . _("Por Perfil") . "' />";
		} else {
			echo "<input style='padding:.2em;margin:0; border:1px solid lightgray; background-color:#fff; font-size:1.1em; cursor:pointer' onclick='onFilterByProfile()' type='button' name='ByProfile' id='ByProfile' value='" . _("Por Perfil") . "' />";
		}
		
		echo "<input style='padding:.2em;margin:0; border:1px solid lightgray; background-color:#fff; font-size:1.1em; cursor:pointer' type='submit' name='saveBtn' id='saveBtn' value='" . _("Guardar Informacion") . "' />";
	echo "</div>";
	
	if ($optionSelected == 'users') {
		echo "<div id='users' style='float:left; width:300px; height:800px; overflow:scroll; border-left:1px solid lightgray;'>";
	} else {
		echo "<div id='users' style='display:none; float:left; width:300px; height:800px; overflow:scroll; border-left:1px solid lightgray;'>";
	}
	
	echo "<h3 style='margin:0 auto; padding:.1em; text-align:center; background-color:#f6de9b;'>" . _("Usuarios") . "</h3>";
	$sql = "SELECT userid, realname FROM www_users WHERE realname LIKE '%$userSearch%' ORDER BY realname";
	$rs = DB_query($sql, $db);
	
	echo "<div style='margin-bottom:.3em'>";
	echo "<input type='text' style='padding:0; margin:0; display:block; width:294px' name='userSearch' value='$userSearch' />";
	echo "<input type='submit' style='padding:0; margin:0; display:block; width:300px' name='userSearchBtn' value='" . _("Buscar") . "' />";
	echo "</div>";
	
	echo "<ol style='padding:0; margin:0'>";
	echo "<input type='checkbox' name='users' value='' onclick='selectAll(this);' />";
	echo "<strong>" . _("Activar/Desactivar Todos") . "</strong>";
	while ($row = DB_fetch_array($rs)) {
		echo "<li style='border-bottom: 1px dotted #000; list-style:none; padding:.2em'>";
		if (in_array($row['userid'], $users)) {
			echo "<input checked='checked' type='checkbox' name='users[]' value='{$row['userid']}' />";
		} else {
			echo "<input type='checkbox' name='users[]' value='{$row['userid']}' />";
		}
		echo ucwords(strtolower($row['realname']));
		echo "<button style='margin:.1em; padding:0' type='submit' name='showByUser' value='{$row['userid']}'>" . _("ver") . "</button>";
		echo "</li>";
	}
	echo "</ol>";
	echo "</div>";
	
	if ($optionSelected == 'profiles') {
		echo "<div id='profiles' style='float:left; width:300px; height:800px; overflow:scroll; border-left:1px solid lightgray'>";
	} else {
		echo "<div id='profiles' style='display:none; float:left; width:300px; height:800px; overflow:scroll; border-left:1px solid lightgray'>";
	}
	echo "<h3 style='margin:0 auto; padding:.1em; text-align:center; background-color:#f6de9b;'>" . _("Perfiles") . "</h3>";
	$sql = "SELECT profileid, name FROM sec_profiles WHERE name LIKE '%$profileSearch%' ORDER BY name";
	$rs = DB_query($sql, $db);
	
	echo "<div style='margin-bottom:.3em'>";
	echo "<input type='text' style='padding:0; margin:0; display:block; width:294px' name='profileSearch' value='$profileSearch' />";
	echo "<input type='submit' style='padding:0; margin:0; display:block; width:300px' name='profileSearchBtn' value='" . _("Buscar") . "' />";
	echo "</div>";
	
	echo "<ol style='padding:0; margin:0'>";
	echo "<li style='border-bottom: 1px dotted #000; list-style:none; padding:.2em'>";
	echo "<input type='checkbox' name='profiles' value='' onclick='selectAll(this);' />";
	echo "<strong>" . _("Activar/Desactivar Todos") . "</strong>"; 
	
	while ($row = DB_fetch_array($rs)) {
		echo "<li style='border-bottom: 1px dotted #000; list-style:none; padding:.2em'>";
		if (in_array($row['profileid'], $profiles)) {
			echo "<input checked='checked' type='checkbox' name='profiles[]' value='{$row['profileid']}' />";
		} else {
			echo "<input type='checkbox' name='profiles[]' value='{$row['profileid']}' />";
		}
		echo ucwords(strtolower($row['name']));
		echo "<button style='margin:.1em; padding:0' type='submit' name='showByProfile' value='{$row['profileid']}'>" . _("ver") . "</button>";
		echo "</li>";
	}
	echo "</ol>";
	echo "</div>";
	
	echo "<div id='branches' style='float:left; overflow:scroll; width:300px; height:800px; background-color:#ccc; border-left:1px dotted #000; border-right:1px solid lightgray'>";
	echo "<h3 style='margin:0 auto; padding:.1em; text-align:center; background-color:#f6de9b;'>" . _("UnidadesNegocio") . "</h3>";
	
	$sql = "SELECT $mainTableIdColumn, $mainTableTextColumn FROM $mainTableName
		WHERE $mainTableTextColumn LIKE '%$datoSearch%' ORDER BY $mainTableTextColumn";
	$rs = DB_query($sql, $db);
	
	echo "<div style='margin-bottom:.3em'>";
	echo "<input type='text' style='padding:0; margin:0; display:block; width:294px' name='datoSearch' value='$datoSearch' />";
	echo "<input type='submit' style='padding:0; margin:0; display:block; width:300px' name='datoSearchBtn' value='" . _("Buscar") . "' />";
	echo "</div>";
	
	echo "<ol style='padding:0; margin:0'>";
	echo "<input type='checkbox' name='datos' value='' onclick='selectAll(this);' />";
	echo "<strong>" . _("Activar/Desactivar Todos") . "</strong>";
	while ($row = DB_fetch_array($rs)) {
		echo "<li style='border-bottom: 1px dotted #000; list-style:none; padding:.2em'>";
		if (in_array($row[$mainTableIdColumn], $datos)) {
			echo "<input checked='checked' type='checkbox' name='datos[]' value='{$row[$mainTableIdColumn]}' />";
		} else {
			echo "<input type='checkbox' name='datos[]' value='{$row[$mainTableIdColumn]}' />";
		}
		echo ucwords(strtolower($row[$mainTableTextColumn]));
		echo "</li>";
	}
	echo "</ol>";
	echo "</div>";

echo "</div>";
echo "<input type='hidden' value='$optionSelected' name='optionSelected' id='optionSelected' />";
echo "</form>";

echo "<div style='clear:both;'></div>";
echo "<br>";
echo "<br>";

include('includes/footer.inc');

?>

<script type="text/javascript">
<!--
function onFilterByUser() {
	var userDiv = document.getElementById("users");
	var profileDiv = document.getElementById("profiles");
	var optionSelected = document.getElementById("optionSelected");
	var byUser= document.getElementById("ByUser");
	var byprofile= document.getElementById("ByProfile");
	
	userDiv.style.display = 'block';
	profileDiv.style.display = 'none';
	optionSelected.value = 'users';

	byUser.style.backgroundColor= "#056693";
	byUser.style.color= "#fff";
	byprofile.style.backgroundColor= "#fff";
	byprofile.style.color= "#000";
}

function onFilterByProfile() {
	var userDiv = document.getElementById("users");
	var profileDiv = document.getElementById("profiles");
	var optionSelected = document.getElementById("optionSelected");
	var byprofile= document.getElementById("ByProfile");
	var byUser= document.getElementById("ByUser");
	
	userDiv.style.display = 'none';
	profileDiv.style.display = 'block';
	optionSelected.value = 'profiles';

	byprofile.style.backgroundColor= "#056693";
	byprofile.style.color= "#fff";
	byUser.style.backgroundColor= "#fff";
	byUser.style.color= "#000";
}

function selectAll(chk) {
	var checkboxes = document.getElementsByName(chk.name + "[]");
	for (var i = 0; i < checkboxes.length; i++) {
		checkboxes[i].checked = chk.checked;
	}
}

//-->
</script>