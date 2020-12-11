<?php
/* Elaboro Jesus Guadalupe Vargas Montes
 * Fecha Creacion 14 Marzo 2014
* 1. Se creo la funcion de agregar los camps de manera masiva
*/

include('includes/session.inc');
$title = _('Actualizacions Base de Datos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['btnDesarrollo'])){
	$script = $_POST['script'];

	$script = str_replace("`", "", $script);
	$diagonal = '\/';

	$diagonal = substr($diagonal, 0,1);
	$diagonal = $diagonal."n";
	$script = str_replace($diagonal, "", $script);

	$diagonal = '\/';
	$diagonal = substr($diagonal, 0,1);//
	$diagonal = $diagonal."r";
	$script = str_replace($diagonal, "", $script);

	$diagonal = '\/';
	$diagonal = substr($diagonal, 0,1);//
	$script = str_replace($diagonal, "", $script);

	$script = str_replace("'", '"', $script);

	$arrayscripts = explode("?", $script);
	foreach ($arrayscripts as $script){
		$posicion = strpos($script, "CREATE");
		if ($posicion === false) {
			$posicion = strpos($script, "create");
			if ($posicion === false) {
				$posicion = strpos($script, "ALTER");
				if ($posicion === false) {
					$posicion = strpos($script, "alter");
					if ($posicion === false) {
					}else{
						$descripcion = substr($script, $posicion,10);
						$posicion2 = strpos($script, "ADD");
						if ($posicion2 === false) {
							if ($posicion2 === false) {
							}else{
								$posicion = $posicion + 10;
								$posicionR = $posicion2 - $posicion;
								$nombretabla = substr($script, $posicion, $posicionR);
							}
						}else{
							$posicion = $posicion + 10;
							$posicionR = $posicion2 - $posicion;
							$nombretabla = substr($script, $posicion, $posicionR);
						}
						
					}
					
				}else{
					$descripcion = substr($script, $posicion,10);
					$posicionR = $posicion2 - $posicion;
					$nombretabla = substr($script, $posicion, $posicionR);
				}
				
			}else{
				$descripcion = substr($script, $posicion,12);
				$posicion2 = strpos($script, "(");
				$posicion = $posicion + 12;
				$posicionR = $posicion2 - $posicion;
				$nombretabla = substr($script, $posicion, $posicionR);
			}
		}else{
			$descripcion = substr($script, $posicion,12);
			$posicion2 = strpos($script, "(");
			$posicion = $posicion + 12;
			$posicionR = $posicion2 - $posicion;
			$nombretabla = substr($script, $posicion, $posicionR);
		}
		$arraybd = $_POST['bddistribucion'];
		echo $script;
		foreach( $arraybd as $arreglo){
			$SQL = "SELECT ERP_UPDATES.sync_servers_db_DES.ipDb as ipdb,
						ERP_UPDATES.sync_servers_db_DES.nombreDb as nombrebd,
						ERP_UPDATES.sync_servers_db_DES.portDb as puertobd,
						ERP_UPDATES.sync_servers_db_DES.userdb as userdb,
						ERP_UPDATES.sync_servers_db_DES.passDB as passDB
				FROM ERP_UPDATES.sync_servers_db_DES
				WHERE ERP_UPDATES.sync_servers_db_DES.estado = 'activo'
				AND ERP_UPDATES.sync_servers_db_DES.nombre = '".$arreglo."'";
			$result = DB_query($SQL, $db);
			while ($myrow = DB_fetch_array($result)){
				$ipdb = $myrow['ipdb'];
				$nombrebd = $myrow['nombrebd'];
				$puertobd = $myrow['puertobd'];
				$userdb = $myrow['userdb'];
				$passDB = $myrow['passDB'];
				mysql_connect($ipdb,$userdb,$passDB) or die('Cannot connect to the database because: ' . mysql_error());
				mysql_select_db($nombrebd);
				$resultado = mysql_query($script);
				if (!$resultado) {
					prnMsg("consulta fallo",'error');
					prnMsg(mysql_error(),"error");
					prnMsg("Se encontreo el error en la implementacion".$nombrebd, 'error');
					
				}else{
					prnMsg("Se ejecuto de manera correcta el script de la tabla ".$nombrebd." en la implementacion ".$arreglo." DES"."info");
				}
			}
		}
	}
}elseif(isset($_POST['btnProduccion'])){
	$script = $_POST['script'];
	$script = str_replace("`", "", $script);
	$diagonal = '\/';
	$diagonal = substr($diagonal, 0,1);
	$diagonal = $diagonal."n";
	$script = str_replace($diagonal, "", $script);
	$diagonal = '\/';
	$diagonal = substr($diagonal, 0,1);//
	$diagonal = $diagonal."r";
	$script = str_replace($diagonal, "", $script);
	$diagonal = '\/';
	$diagonal = substr($diagonal, 0,1);//
	$script = str_replace($diagonal, "", $script);
	$script = str_replace("'", '"', $script);
	$arrayscripts = explode("?", $script);
	foreach ($arrayscripts as $script){
		$posicion = strpos($script, "CREATE");
		if ($posicion === false) {
			$posicion = strpos($script, "create");
			echo $posicion;
			if ($posicion === false) {
				$posicion = strpos($script, "ALTER");
				if ($posicion === false) {
					$posicion = strpos($script, "alter");
					if ($posicion === false) {
					}else{
						$descripcion = substr($script, $posicion,10);
						$posicion2 = strpos($script, "ADD");
						if ($posicion2 === false) {
							if ($posicion2 === false) {
							}else{
								$posicion = $posicion + 10;
								$posicionR = $posicion2 - $posicion;
								$nombretabla = substr($script, $posicion, $posicionR);
							}
						}else{
							$posicion = $posicion + 10;
							$posicionR = $posicion2 - $posicion;
							$nombretabla = substr($script, $posicion, $posicionR);
						}
					}
						
				}else{
					$descripcion = substr($script, $posicion,10);
					$posicionR = $posicion2 - $posicion;
					$nombretabla = substr($script, $posicion, $posicionR);
				}
		
			}else{
				$descripcion = substr($script, $posicion,12);
				$posicion2 = strpos($script, "(");
				$posicion = $posicion + 12;
				$posicionR = $posicion2 - $posicion;
				$nombretabla = substr($script, $posicion, $posicionR);
			}
		}else{
			$descripcion = substr($script, $posicion,12);
			$posicion2 = strpos($script, "(");
			$posicion = $posicion + 12;
			$posicionR = $posicion2 - $posicion;
			$nombretabla = substr($script, $posicion, $posicionR);
		}
		$arraybd = $_POST['bddistribucion'];
		foreach( $arraybd as $arreglo){
			$SQL = "SELECT ERP_UPDATES.sync_servers_db.ipdb as ipdb,
						ERP_UPDATES.sync_servers_db.nombreDb as nombrebd,
						ERP_UPDATES.sync_servers_db.portDb as puertobd,
						ERP_UPDATES.sync_servers_db.userdb as userdb,
						ERP_UPDATES.sync_servers_db.passDB as passDB
				FROM ERP_UPDATES.sync_servers_db
				WHERE ERP_UPDATES.sync_servers_db.estado = 'activo'
				AND ERP_UPDATES.sync_servers_db.nombre = '".$arreglo."'";
			$result = DB_query($SQL, $db);
			while ($myrow = DB_fetch_array($result)){
				$ipdb = $myrow['ipdb'];
				$nombrebd = $myrow['nombrebd'];
				$puertobd = $myrow['puertobd'];
				$userdb = $myrow['userdb'];
				$passDB = $myrow['passDB'];
				mysql_connect($ipdb,$userdb,$passDB) or die('Cannot connect to the database because: ' . mysql_error());
				mysql_select_db($nombrebd);
				$resultado = mysql_query($script);
				if (!$resultado) {
					prnMsg("consulta fallo",'error');
					prnMsg(mysql_error(),"error");
					prnMsg("Se encontreo el error en la implementacion".$nombrebd, 'error');
					
				}else{
					prnMsg("Se ejecuto de manera correcta el script de la tabla ".$nombrebd." en la implementacion ".$arreglo.""."info");
				}
			}
		
			$SQL = "SELECT ERP_UPDATES.sync_servers_db_CAPA.ipdb as ipdb,
						ERP_UPDATES.sync_servers_db_CAPA.nombreDb as nombrebd,
						ERP_UPDATES.sync_servers_db_CAPA.portDb as puertobd,
						ERP_UPDATES.sync_servers_db_CAPA.userdb as userdb,
						ERP_UPDATES.sync_servers_db_CAPA.passDB as passDB
				FROM ERP_UPDATES.sync_servers_db_CAPA
				WHERE ERP_UPDATES.sync_servers_db_CAPA.estado = 'activo'
					AND ERP_UPDATES.sync_servers_db_CAPA.nombre ='".$arreglo."'";
			$result = DB_query($SQL, $db);
			while ($myrow = DB_fetch_array($result)){
				$ipdb = $myrow['ipdb'];
				$nombrebd = $myrow['nombrebd'];
				$puertobd = $myrow['puertobd'];
				$userdb = $myrow['userdb'];
				$passDB = $myrow['passDB'];
				mysql_connect($ipdb,$userdb,$passDB) or die('Cannot connect to the database because: ' . mysql_error());
				mysql_select_db($nombrebd);
				$resultado = mysql_query($script);
				if (!$resultado) {
					prnMsg("consulta fallo",'error');
					prnMsg(mysql_error(),"error");
					prnMsg("Se encontreo el error en la implementacion".$nombrebd, 'error');
					
				}else{
					prnMsg("Se ejecuto de manera correcta el script de la tabla ".$nombrebd." en la implementacion ".$arreglo." CAPA"."info");
				}
				
			}
		}
		prnMsg("Se ejecuto de manera correcta el script"."info");
	}
}
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
echo "<table border=1 style='margin-left: auto; margin-right: auto; width: 80%;' border=0 width=500 style='background-color:#F0F0F0'>";
echo "<tr>";
echo "<td colspan=4>"._("AGREGA SCRIPT PARA ACTUALIZAR LAS BASES DE DATOS")."</td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan=3><textarea rows=10 cols=70 name=script></textarea></td>";
echo "<td>";
echo "<table>";
echo "<tr>";
echo "<td><input type=submit name=btnDesarrollo value=".('Desarrollo')."></td>";
echo "</tr>";
echo "<br>";
echo "<tr>";
echo "<td colspan=3>";
echo "<hr></hr>";
echo "</td>";
echo "</tr>";
echo "<br>";
echo "<tr>";
echo "<td><input type=submit name=btnProduccion value="._('Produccion')."></td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>".('Distribucion')."</td>";
echo "<td>".('Servicio')."</td>";
echo "<td>".('Materiales')."</td>";
echo "<td>".('Otros')."</td>";
echo "</tr>";
echo "<tr>";
/* Despliegua Clientes Distribucion */
echo "<td>";
echo "<table>";
$i = 0;
$SQL = "SELECT distinct ERP_UPDATES.sync_servers_db.nombre,
				ERP_UPDATES.sync_servers_db.nombre as cliente
		FROM ERP_UPDATES.sync_servers_db
		WHERE ERP_UPDATES.sync_servers_db.tipo = 'Distribucion'
		AND ERP_UPDATES.sync_servers_db.estado = 'activo'";
$k=0;
$result = DB_query($SQL, $db);
$nRows = DB_num_rows($result);
echo "<tr class='EvenTableRows'>";
echo "<td><input type='checkbox' onclick='activar(this.checked, \"dist\", $nRows)' /></td>";
echo "<td>Sel</td>";
echo "</tr>";
while ($myrow = DB_fetch_array($result)){
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	$i++;
	echo "<td><input type=checkbox id='dist$i' name=bddistribucion[] value='".$myrow['cliente']."'></td>";
	echo "<td>$i. ".$myrow['cliente']."</td>";
	echo "</tr>";
}
echo "</table>";
echo"</td>";
$i = 0;
/* Despliegua Clientes Servicios */
echo "<td>";
echo "<table>";
$SQL = "SELECT distinct ERP_UPDATES.sync_servers_db.nombre,
				ERP_UPDATES.sync_servers_db.nombre as cliente
		FROM ERP_UPDATES.sync_servers_db
		WHERE ERP_UPDATES.sync_servers_db.tipo = 'Servicios'
		AND ERP_UPDATES.sync_servers_db.estado = 'activo'";
$result = DB_query($SQL, $db);
$k=0;
$nRows = DB_num_rows($result);
echo "<tr class='EvenTableRows'>";
echo "<td><input type='checkbox' onclick='activar(this.checked, \"serv\", $nRows)' /></td>";
echo "<td>Sel</td>";
echo "</tr>";
while ($myrow = DB_fetch_array($result)){
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	$i++;
	echo "<td><input type=checkbox id='serv$i' name=bddistribucion[] value='".$myrow['cliente']."'></td>";
	echo "<td>" . $i . ". " . $myrow['cliente']."</td>";
	echo "</tr>";
}
echo "</table>";
echo"</td>";
/* Despliegua Clientes Materiales */
echo "<td>";
echo "<table>";
$i = 0;
$SQL = "SELECT distinct ERP_UPDATES.sync_servers_db.nombre,
				ERP_UPDATES.sync_servers_db.nombre as cliente
		FROM ERP_UPDATES.sync_servers_db
		WHERE ERP_UPDATES.sync_servers_db.tipo = 'Materiales'
		AND ERP_UPDATES.sync_servers_db.estado = 'activo'";
$result = DB_query($SQL, $db);
$k=0;
$nRows = DB_num_rows($result);
echo "<tr class='EvenTableRows'>";
echo "<td><input type='checkbox' onclick='activar(this.checked, \"mat\", $nRows)' /></td>";
echo "<td>Sel</td>";
echo "</tr>";
while ($myrow = DB_fetch_array($result)){
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	$i++;
	echo "<td><input type=checkbox id='mat$i' name=bddistribucion[] value='".$myrow['cliente']."'></td>";
	echo "<td>" . $i . ". " . $myrow['cliente']."</td>";
	echo "</tr>";
}
echo "</table>";
echo"</td>";
/* Despliegua Clientes Individuales */
echo "<td>";
echo "<table>";
$i = 0;
$SQL = "SELECT distinct ERP_UPDATES.sync_servers_db.nombre,
				ERP_UPDATES.sync_servers_db.nombre as cliente
		FROM ERP_UPDATES.sync_servers_db
		WHERE ERP_UPDATES.sync_servers_db.tipo = 'Otros'
		AND ERP_UPDATES.sync_servers_db.estado = 'activo'";
$result = DB_query($SQL, $db);
$k=0;
$nRows = DB_num_rows($result);
echo "<tr class='EvenTableRows'>";
echo "<td><input type='checkbox' onclick='activar(this.checked, \"client\", $nRows)' /></td>";
echo "<td>Sel</td>";
echo "</tr>";
while ($myrow = DB_fetch_array($result)){
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}
	$i++;
	echo "<td><input type=checkbox id='client$i' name=bddistribucion[] value='".$myrow['cliente']."'></td>";
	echo "<td>" . $i . ". " . $myrow['cliente']."</td>";
	echo "</tr>";
}
echo "</table>";
echo"</td>";
echo "</tr>";
echo "</table>";
echo '</form>';
?>

<script>
function activar(checked, name, rows) {
	for (var i = 1; i <= rows; i++) {
		var checkbox = document.getElementById(name + i);
		checkbox.checked = checked;
	}
}
</script>

<?php
include('includes/footer.inc');
?>