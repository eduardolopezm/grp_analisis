<?php

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Cuentas');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


	

$cuenta_process=true;
$concepto_process=true;
$ok_process=false;
$men=$_GET['menu'];







	if ((strlen ($_POST ['cuenta1'])<=0)){
		$cuenta_process=false ;
		//$listado="1";
		
		
	}
	if ((strlen ($_POST ['concepto'])<=0)){
		$concepto_process=false ;
		$listado="1";
	}
	
	if (($cuenta_process == true) && ($cuenta_process == true)){
		$ok_process=true;
		
	}
	
	if ($listado == "1"){

		$sql="select * from chartbridge";
		$res=(db_query($sql,$db));
		}
		
	
	if ($men == "2" ){
	
		$sql="select * from chartbridge where accountcode='" . $_GET['cuenta'] . "'";
		$res=(db_query($sql,$db));
		$myrow = DB_fetch_array($res);		
		}
		
			
	if ($men == "3"){
	
		$sql="select * from chartbridge where accountcode='" . $_GET['cuenta'] . "'";
		$res=(db_query($sql,$db));
		$myrow = DB_fetch_array($res);		
		}


	
	if ($ok_process == true && $_POST['boton'] == 'Procesar' ){
		
		
			$sql="select count(*) as cuenta from chartbridge where accountcode='". $_POST['cuenta1'] ."'";
			$res=(db_query($sql,$db));
			$myrow=db_fetch_row($res);
		
			if ($myrow[0]>0){
				echo " <b> <font color=#FF0000> Error ya existe la cuenta: </font> </b><b>" .$_POST['cuenta1']."</b>";
				echo "<center <a href='ABCCuentaspuente.php?men=1' > REGRESAR </a> </center>";

				}
			else {
				$sql="insert into chartbridge(accountcode,concept)";
				$sql=$sql."VALUES('".$_POST['cuenta1']."','".$_POST['concepto']."')";
				$x=db_query($sql,$db);
				echo " <b> <font color=#0000FF> Se ha Agregado la cuenta: </font> </b><b>" .$_POST['cuenta1']."</b>";
				$men="1";
				$listado="1";
				$sql="select * from chartbridge";
				$res=(db_query($sql,$db));
			
				}
	}elseif ( $cuenta_process == true && $_POST['boton2'] == 'Buscar' ){
		
			$sql="select count(*) as cuenta from chartbridge where accountcode like '%". $_POST['cuenta1'] ."%'";
			$res=(db_query($sql,$db));
			$myrow=db_fetch_row($res);
			if ($myrow[0]<=0){
				echo " <b> <font color=#FF0000> NO EXISTE REGISTRO </font> </b><b>" .$_POST['cuenta1']."</b>";
				echo "<br>";
				echo "<br>";
				echo "<center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Regresar') . '</a></center>';
				//echo '<center <a href=' . $rootpath . '/ABCCuentaspuente_mau1.php?' . SID . '> REGRESAR </a> </center>';
				//echo "<META HTTP-EQUIV='Refresh' CONTENT='1; URL=" . $rootpath ."/ibeth2.php?" . SID . "'>";
				}
			
				else {
		
				$sql="select * from chartbridge where accountcode like '%".$_POST['cuenta1']."%'";
				$res=(db_query($sql,$db));
				$listado="2";
				echo "<br>";
				echo "<br>";
				echo "<center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Regresar') . '</a></center>';				//echo '<center <a href=' . $rootpath . '/ABCCuentaspuente_mau1.php?' . SID . '> REGRESAR </a> </center>';

				//echo "<center <a href='ABCCuentaspuente_mau1.php?men=1' > REGRESAR </a> </center>";
				}
	}elseif ($_POST['bot3']=='Modificar'){
			$sql="UPDATE chartbridge SET concept='$_POST[concepto]' WHERE accountcode='$myrow[accountcode]'";
			$res=(db_query($sql,$db));
			$listado="1";
			$men="1";
			$sql="select * from chartbridge";
			$res=(db_query($sql,$db));
			echo "<b> <center> <font color=#0000FF> Actualizacion Realizada con Exito </font></center></b>";
			echo"<META HTTP-EQUIV='refresh' CONTENT='2; URL=ABCCuentaspuente.php?men=1'>";
			
	}elseif($_POST['botsi'] == 'ELIMINAR'){
			$sql="DELETE FROM chartbridge WHERE accountcode='$myrow[accountcode]'";
			$res=(db_query($sql,$db));
			$listado="1";
			$men="1";
			$sql="select * from chartbridge";
			$res=(db_query($sql,$db));
			echo "<b> <center> <font color=#0000FF> El Registro se ha ELIMINADO con Exito </font></center></b>";
			echo"<META HTTP-EQUIV='refresh' CONTENT='2; URL=ABCCuentaspuente.php?men=1'>";

	}




		
if ($men != "2" && $men != "3"){
	
		
 
	echo "<form name='FDatos' method='POST' action=''>";
	echo "<table border='2' width '100%'>";
	echo "<br>";
	echo "<br>";

		echo "<H2> <center> ABC CUENTAS NC </center> </H2>";
		
		echo "<tr><td>Numero de Cuenta </td>";
		echo "<td><input type='text' name='cuenta1' value=''></td></tr>";
						
		echo "<tr><td>Concepto</td>";
		echo "<td><input type='text' name='concepto' value=''></td></tr>";
		
			
	
		echo "<tr><td><center> <input type='submit' name='boton' value='Procesar'> </center></td>";
		echo "<td><center> <input type='submit' name='boton2' value='Buscar'> </center></td></tr>";
			
	echo "</table>";

	echo "<br>";
	echo "<br>";
	echo "<br>";

	echo "<table border='2' width '100%'>";

	
	echo "<tr><td><center> <b>Cuenta </b> </center></td>";
	echo "<td><center> <b>Concepto </b> </center></td></tr>";	
		
	while ($myrow = DB_fetch_array($res)) {
		
	echo "<tr>";
		echo "<td>";
			echo $myrow['accountcode'];
		echo "</td>";
		echo "<td>";
			echo $myrow['concept'];
		echo "</td>";
		echo "<td>";
			echo "<a href='ABCCuentaspuente.php?menu=2&cuenta=" . $myrow['accountcode'] . "'> Editar </a>";
		echo "</td>";
		
		echo "<td>";
			echo "<a href='ABCCuentaspuente.php?menu=3&cuenta=" . $myrow['accountcode'] . "'> Eliminar </a>";
		echo "</td>";
	
	echo "<tr>";
	}
	
	echo "</table>";

	echo "<br>";
	echo "<br>";
}

	echo "<br>";
	echo "<br>";
		
		
		
	
		
if ($men == "2"){
	echo "<form name='FDatos' method='POST' action=''>";
	echo "<table border='2' width '100%'>";
	echo "<H2> <center> MODIFICACION DE CUENTAS</center> </H2>";
			
			echo "<tr><td>Numero de Cuenta</td>";
			echo "<td><center>" . $myrow['accountcode'] . " </center></td></tr>";
			
			echo "<tr><td>Concepto</td>";
			echo "<td><input type='text' size='40' name='concepto' value='" . $myrow['concept'] . "'></td></tr>";		
			
			echo "<tr><td><center> <input type='Submit' name='bot3' value='Modificar'> </center></td>";
			echo "<td><a href='" . $_SERVER['PHP_SELF'] ."?". SID . "'>" . _('Regresar') . "'</a></td></tr>";				
		//echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Cuentas existentes') . "'</a></div>';

			
	echo "</table>";
	
 
	echo "<br>";
	echo "<br>";
		
}

		
if ($men == "3"){
	echo "<form name='FDatos' method='POST' action=''>";
	echo "<table border='2' width '100%'>";
	echo "<H2> <center> ESTA SEGURO DE QUERER ELIMINAR LA CUENTA : </center> </H2>";
	
	echo "<H3> <center>  " . $_GET['cuenta'] ."  ". $myrow['concept']."  </center> </H3>";
	
	echo "<tr><td><center> <input type='Submit' name='botsi' value='ELIMINAR'> </center></td>";
	echo "<td><center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Regresar') . '</a></center></td></tr>';				//echo '<center <a href=' . $rootpath . '/ABCCuentaspuente_mau1.php?' . SID . '> REGRESAR </a> </center>';

	//echo "<td><center <a href='ABCCuentaspuente_mau1.php?menu='1' > REGRESAR </a> </center></td></tr>";
			
			
	echo "</table>";
	
 
	echo "<br>";
	echo "<br>";
	
	}
	

include('includes/footer.inc');
?>