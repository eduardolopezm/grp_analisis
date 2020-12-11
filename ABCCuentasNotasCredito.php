<?php

/*
 ARCHIVO CREADO POR: ISRAEL BARRERA NAVARRO
 FECHA DE MODIFICACION: 25-FEBRERO-2011
*/


//ABC Cuentas para Notas de Credito de Proveedor
$funcion=252;
$PageSecurity = 3;
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

		$sql="select * from chartbridge_supp";
		$res=(db_query($sql,$db));
		}
		
	
	if ($men == "2" ){
	
		$sql="select * from chartbridge_supp where accountcode='" . $_GET['cuenta'] . "'";
		$res=(db_query($sql,$db));
		$myrow = DB_fetch_array($res);		
		}
		
			
	if ($men == "3"){
	
		$sql="select * from chartbridge_supp where accountcode='" . $_GET['cuenta'] . "'";
		$res=(db_query($sql,$db));
		$myrow = DB_fetch_array($res);		
		}


	
	if ($ok_process == true && $_POST['boton'] == 'Procesar' ){
		
		
			$sql="select count(*) as cuenta from chartbridge_supp where accountcode='". $_POST['cuenta1'] ."'";
			$res=(db_query($sql,$db));
			$myrow=db_fetch_row($res);
		
			if ($myrow[0]>0){
				echo " <b> <font color=#FF0000> Error ya existe la cuenta: </font> </b><b>" .$_POST['cuenta1']."</b>";
				echo "<center <a href='ABCCuentasNotasCredito.php?men=1' > REGRESAR </a> </center>";

				}
			else {
				$sql="insert into chartbridge_supp(accountcode,concept)";
				$sql=$sql."VALUES('".$_POST['cuenta1']."','".$_POST['concepto']."')";
				$x=db_query($sql,$db);
				echo " <b> <font color=#0000FF> Se ha Agregado la cuenta: </font> </b><b>" .$_POST['cuenta1']."</b>";
				$men="1";
				$listado="1";
				$sql="select * from chartbridge_supp";
				$res=(db_query($sql,$db));
			
				}
	}elseif ( $cuenta_process == true && $_POST['boton2'] == 'Buscar' ){
		
			$sql="select count(*) as cuenta from chartbridge_supp where accountcode like '%". $_POST['cuenta1'] ."%'";
			$res=(db_query($sql,$db));
			$myrow=db_fetch_row($res);
			if ($myrow[0]<=0){
				echo " <b> <font color=#FF0000> NO EXISTE REGISTRO </font> </b><b>" .$_POST['cuenta1']."</b>";
				echo "<br>";
				echo "<br>";
				echo "<center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Regresar') . '</a></center>';
				
				}
			
				else {
		
				$sql="select * from chartbridge_supp where accountcode like '%".$_POST['cuenta1']."%'";
				$res=(db_query($sql,$db));
				$listado="2";
				echo "<br>";
				echo "<br>";
				echo "<center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Regresar') . '</a></center>';				

				
				}
	}elseif ($_POST['bot3']=='Modificar'){
			$sql="UPDATE chartbridge_supp SET concept='$_POST[concepto]' WHERE accountcode='$myrow[accountcode]'";
			$res=(db_query($sql,$db));
			$listado="1";
			$men="1";
			$sql="select * from chartbridge_supp";
			$res=(db_query($sql,$db));
			echo "<b> <center> <font color=#0000FF> Actualizacion Realizada con Exito </font></center></b>";
			echo"<META HTTP-EQUIV='refresh' CONTENT='2; URL=ABCCuentasNotasCredito.php?men=1'>";
			
	}elseif($_POST['botsi'] == 'ELIMINAR'){
			$sql="DELETE FROM chartbridge_supp WHERE accountcode='$myrow[accountcode]'";
			$res=(db_query($sql,$db));
			$listado="1";
			$men="1";
			$sql="select * from chartbridge_supp";
			$res=(db_query($sql,$db));
			echo "<b> <center> <font color=#0000FF> El Registro se ha ELIMINADO con Exito </font></center></b>";
			echo"<META HTTP-EQUIV='refresh' CONTENT='2; URL=ABCCuentasNotasCredito.php?men=1'>";
	}




		
if ($men != "2" && $men != "3"){
	
		
 
	echo "<form name='FDatos' method='POST' action=''>";
	echo "<table>";
	echo "<br>";
	echo "<br>";

		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/note_add.png" title="' . _('CUENTAS NOTAS DE CREDITO') . '" alt="">' . ' ' ." ABC CUENTAS NOTAS DE CREDITO </p>";
		
		echo "<tr><td>Numero de Cuenta : </td>";
		echo "<td><input type='text' name='cuenta1' value=''></td></tr>";
						
		echo "<tr><td>Concepto :</td>";
		echo "<td><input type='text' name='concepto' value=''></td></tr><br>";
		
	echo "</table>";
	
	echo "<table>";
	
		echo "<tr><td><center> <input type='submit' name='boton' value='Procesar'> </center></td><br>";
		echo "<tr><td><center> <input type='submit' name='boton2' value='Buscar'> </center></td></tr>";
			
	echo "</table>";

	echo "<br>";
	echo "<br>";
	echo "<br>";

	echo "<table border='2' width '100%'>";

	
	echo "<tr><th><center> <b>Cuenta </b> </center></th>";
	echo "<th><center> <b>Concepto </b> </center><th><th></th></tr>";	
		
	while ($myrow = DB_fetch_array($res)) {
		
	echo "<tr>";
		echo "<td>";
			echo $myrow['accountcode'];
		echo "</td>";
		echo "<td>";
			echo $myrow['concept'];
		echo "</td>";
		echo "<td>";
			echo "<a href='ABCCuentasNotasCredito.php?menu=2&cuenta=" . $myrow['accountcode'] . "'> Editar </a>";
		echo "</td>";
		
		echo "<td>";
			echo "<a href='ABCCuentasNotasCredito.php?menu=3&cuenta=" . $myrow['accountcode'] . "'> Eliminar </a>";
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
	echo "<table>";
	echo "<H3> <center> MODIFICACION DE CUENTAS</center> </H3><br>";
			
			echo "<tr><td>Numero de Cuenta : </td>";
			echo "<td>" . $myrow['accountcode'] . " </td></tr>";
			
			echo "<tr><td>Concepto : </td>";
			echo "<td><input type='text' size='40' name='concepto' value='" . $myrow['concept'] . "'><br></td></tr>";		
			
	echo "</table>";
	echo "<table>";
			echo "<tr><tr><td><center> <input type='Submit' name='bot3' value='Modificar'> </center></td><br>";
			echo "<tr><td><a href='" . $_SERVER['PHP_SELF'] ."?". SID . "'>" . _('Regresar') . "</a></td></tr>";				
		

			
	echo "</table>";
	
 
	echo "<br>";
	echo "<br>";
		
}

		
if ($men == "3"){
	echo "<form name='FDatos' method='POST' action=''>";
	echo "<H3> <center><font color='blue'> ESTA SEGURO DE QUERER ELIMINAR LA CUENTA : </center> </H3>";
	echo "<table>";
	
	
	echo "<H3> <center><font color='Navy'>  " . $_GET['cuenta'] ."  ". $myrow['concept']."  </center> </H3>";
	
	echo "<tr><td><center> <input type='Submit' name='botsi' value='ELIMINAR'> </center></td><br>";
	echo "<td><td><center><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Regresar') . '</a></center></td></tr>';				

	echo "</table>";
	
 
	echo "<br>";
	echo "<br>";
	
	}
	





include('includes/footer.inc');
?>