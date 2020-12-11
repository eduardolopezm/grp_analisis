<?php

/* CGM 29/09/2014 Generacion de funcionalidad*/
 
$dbhost = "23.111.130.190";
$dbuser = "desarrollo";
$dbpassword = "p0rtAli70s";
$dbname = "ap_grp";
  
$db = mysql_connect($dbhost, $dbuser, $dbpassword) or die("Connection Error: " . mysql_error()); mysql_select_db($dbname) or die("Error al conectar a la base de datos.");

$tableheader='<tr>';
$tableheader=$tableheader.'<td>Nombre Campo</td>';
$tableheader=$tableheader.'<td>Tipo Dato</td>';
$tableheader=$tableheader.'<td>Tama–o</td>';
$tableheader=$tableheader.'<td>Nulos</td>';
$tableheader=$tableheader.'<td>Tipo Indice</td>';
$tableheader=$tableheader.'<td>Valor Default</td>';
$tableheader=$tableheader.'<td>Informacion Adicional</td>';
$tableheader=$tableheader.'<td>Comentario</td></tr>';



//MOSTRAMOS TODAS LAS TABLAS
$Sql ="SHOW TABLES FROM ". $dbname;
$i=1;
$result = mysql_query( $Sql ) or die("No se puede ejecutar la consulta: ".mysql_error());
while($Rs = mysql_fetch_array($result)) {   
//	echo '<pre>';
	echo '<table border=1>';
	echo '<tr><td colspan=7><b>Nombre Tabla:</b> '. $Rs[0].'</td>';
	echo '</tr>';
	echo '<tr><td colspan=7><b>Descripcion:</b></td>';
	echo '</tr>';
	
	echo $tableheader;
	
	//MOSTRAMOS LA INFORMACION DE LOS CAMPOS
	//$Sql2 ="DESCRIBE  `". $Rs[0]."`";
	$Sql2 = "SHOW FULL COLUMNS FROM `".$Rs[0]."`";

	//echo $Sql2;
	$result2 = mysql_query( $Sql2 ) or die("No se puede ejecutar la consulta: ".mysql_error());
	$band = false;

	while($Rs2 = mysql_fetch_array($result2)) {
		//extraemos el campo que estamos buscando
		$tama–o=explode('(',$Rs2[1]);
	//	echo '<br><br>'.var_dump($tama–o);
			echo '<tr>';
			echo '<td>'.$Rs2["Field"].'&nbsp</td>';
			echo '<td>'.$tama–o[0].'&nbsp</td>';
			echo '<td>'.str_replace(')','',$tama–o[1]).'&nbsp</td>';
			echo '<td>'.$Rs2[3].'&nbsp</td>';
			echo '<td>'.$Rs2[4].'&nbsp</td>';
			echo '<td>'.$Rs2[5].'&nbsp</td>';
			//echo '<td>Descripcion campo</td>';
			echo '<td>'.$Rs2[6].'&nbsp</td>';
			echo '<td>'.$Rs2[8].'&nbsp</td>';
			echo '</tr>';	
			
			//echo '<br>'.$i.' - '.$Rs[0].' @@@ '.strtolower($Rs2[0]).' - '.$Rs2[1];//.'---- posicion:'.var_dump(strpos(strtolower($Rs2[0]),'glcode'));
		
	}

	$i=$i+1;

	echo '</table>';
	echo '<br><br>';
	//exit;
}   

// PARA CADA TABLA DESCRIBIMOS LOS CAMPOS
/*
	$Sql2 ="DESCRIBE ".$Rs[0];   
	$result2 = mysql_query( $Sql2 ) or die("No se puede ejecutar la consulta: ".mysql_error());   
	while($Rs2 = mysql_fetch_array($result2)) {   
		echo '<tr>';   
		echo '<td width="55%">'.$Rs2['Field'].'</td>';   
		echo '<td width="25%">'.$Rs2['Type'].'</td>';   
		echo '</tr>';   
	}   
*/

?>
