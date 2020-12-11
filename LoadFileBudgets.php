<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', '1');
*/

$PageSecurity = 5;
include('includes/session.inc');
$title = _('Cargar Ingresos...');
include('includes/header.inc');
$funcion=1598;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');


//Ver config
/*$DatawareDB = $_SESSION['DwDatabase'];
$host       = $_SESSION['DwHost'];
$dbuser     = $_SESSION['DwUser'];
$dbpassword = $_SESSION['DwPass'];
$mysqlport  = $_SESSION['DwDBPort'];
$dbsocket   = $_SESSION['DwSock'];

$host = "localhost";
$dbuser = 'root';
$dbpassword = 'p0rtali70s';


$dbDataware=mysqli_connect($host , $dbuser, $dbpassword,$DatawareDB, $mysqlport,$dbsocket);


if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}*/



#recupera el signo que servira para hacer la separacion de cada valor
if (isset($_POST['separador'])){
	$separador = $_POST['separador'];
}else{
	if (isset($_GET['separador'])){
		$separador = $_GET['separador'];
    }else{
		#se queda por defaul la coma (,)//
		$separador = ",";
	}
}

#si la accion es cargar el archivo y la variable que sirve de separador de valores es diferente de vacio
if (isset($_POST['cargar']) and $separador<>''){
	$nombre_archivo = $_FILES['userfile']['name'];
	$tipo_archivo = $_FILES['userfile']['type'];
	$tamano_archivo = $_FILES['userfile']['size'];
	$filename = 'productlist/'.$nombre_archivo;

	if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='text/csv' OR $tipo_archivo=='application/octet-stream'){
		//echo 'entraaaaa';
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
			$tieneerrores=0;
			$mincolumnas=8;
			
			$colfecha=0;
			$colrazon=1;
			$coluninego=2;
			$colsubcatego=3;
			$colref=4;
			$colconcepto=5;
			$colcargo =6;
			$colabono=7;
			

			# ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO
			$lineas = file($filename);

			# ****************************
			# **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
			# ****************************
			echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="FDatosB">';
			echo "<table width=80% cellpadding=3 border=1 style='margin:auto;'>";
			echo "<tr>";
			echo "<td style='text-align:center;' colspan=18>";
            echo "<font size=2 color=Darkblue><b>"._('RESULTADO ARCHIVO PRESUPUESTOS')."</b></font>";
            echo "</td>";
            echo "</tr>";
            echo '<tr>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('No.') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Fecha') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Razon') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Unidad Negocio') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Subcategoria') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Referencia') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Concepto') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Cargo') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Abono') . '</td>';
            echo '</tr>';
		                				 
            $cont=0;
            $error = "";
            $flagerror = false;
            foreach ($lineas as $line_num => $line){
            	$error = "";
            	$datos = explode($separador, $line); # Convierte en array cada una de las lineas
            	$columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
            	if ($columnaslinea<$mincolumnas){
            		$tieneerrores = 1;
            		$error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO ');
                    $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' columnas separadas por "'.$separador.'" y tiene solo '.$columnaslinea. ' columnas' );
                    prnMsg($error,'error');
                  	exit;
            	}
            	
				if ($line_num > 0){ //primera linea son titulos y no se procesa
					
					$fecha = $datos[$colfecha];
                    $partes= explode("/", $fecha);
	       				$razon = $datos[$colrazon];
						$uninego = $datos[$coluninego];
						$subcatego = $datos[$colsubcatego];
						$ref = $datos[$colref];
						$concepto = $datos[$colconcepto];
						$cargo = $datos[$colcargo];
						$abono = $datos[$colabono];
						//Muestra los datos que hay en el cvs en la tabla
						echo "<tr>";
	            		  	echo "<td style='text-align:left;'>";
	            		  		echo "<font size=2 color=red>".$cont."</font>";
						  	echo "</td>";
						  	echo "<td style='text-align:left;'>";
						  		echo "<font size=2 color=Darkblue><b>".$fecha."</b></font>";
	            		  	echo "</td>";
	            		  	echo "<td style='text-align:right;'>";
	            		  		echo "<font size=2 color=Darkblue><b>".$razon."</b></font>";
	                	  	echo "</td>";
						  	echo "<td style='text-align:right;'>";
						  		echo "<font size=2 color=Darkblue><b>".$uninego."</b></font>";
						  	echo "</td>";
						  	echo "<td style='text-align:right;'>";
						  		echo "<font size=2 color=Darkblue><b>".$subcatego."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$ref."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$concepto."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$cargo."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$abono."</b></font>";
							echo "</td>";
                            /*echo "<td style='text-align:right;'>";
								if (checkdate ($partes[1],$partes[0],"20".$partes[2])){
				            		echo "<font size=2 color=Darkblue><b>La fecha es correcta</b></font>";
				            	}else{
				            		echo '<font color="red">La fecha no es correcta</font>';
				            		$flagerror = true;
				            	}
				            echo "</td>";*/
							
						echo "</tr>";		  
						$cont = $cont + 1;
					//}
				} //if primer linea
			} # Fin del for que recorre cada linea
			$currabrev = $_POST['currabrev'];
			echo "<tr>";
				echo "<td colspan = '18' style='text-align:center;'>";
					if (!$flagerror){
						echo "<input type='submit' style='text-align:center;' name='confirmar' value='CONFIRMAR'>";
					}else{
						echo "<input type='submit' style='text-align:center;' name='regresar' value='REGRESAR'>";
					}
					
                    echo "<input name='nombrearchivo' type='hidden' value ='" . $nombre_archivo . "'>";
                    echo "<input name='tipoarchivo' type='hidden' value ='" . $tipo_archivo . "'>";
                    echo "<input name='separador' type='hidden' value ='" . $separador . "'>";
                    echo "<br><font size=2 color=Darkblue><b>El nombre del archivo es : . $filename. </b></font>";
				echo "</td>";
			echo "</tr>";
			echo "</table>";
			echo "</form>";
			exit;
			//prnMsg("..SE CARGARON EXITOSAMENTE AL SISTEMA ".$cont." LOS DATOS DE COMPRA.",'sucess');*/
             
		}else{
	    	echo "Ocurrio algun error al subir el ficheroooo. No pudo guardarse.";  
		};
	};  

}

If (isset($_POST['confirmar']) and $separador<>''){
	
	/**LEE ARCHIVO**/
	$nombre_archivo = $_POST['nombrearchivo'];
	$tipo_archivo = $_POST['tipoarchivo'];
	$filename = 'productlist/'.$nombre_archivo;
	/*$tipo_moneda = $tipo_moneda;
	$selecionprovedor = $_POST[''];*/	
		
	if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='text/csv' OR $tipo_archivo=='application/octet-stream'){
		$tieneerrores=0;
		$mincolumnas=8;
		
		$colfecha=0;
			$colrazon=1;
			$coluninego=2;
			$colsubcatego=3;
			$colref=4;
			$colconcepto=5;
			$colcargo =6;
			$colabono=7;
	
		# ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO
		$lineas = file($filename);
	
		# ****************************
		# **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
		# ****************************
        echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="FDatosB">';
	    echo "<table style='text-align:center;' width=100% cellpadding=3 border=1>";
			echo "<tr>";
				echo "<td style='text-align:center;' colspan=17>";
					echo "<font size=2 color=Darkblue><b>"._('ARCHIVO CARGADO')."</b></font>";
				echo "</td>";
			echo "</tr>";
			echo '<tr>';
				echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('No.') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Fecha') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Razon') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Unidad Negocio') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Subcategoria') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Referencia') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Concepto') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Cargo') . '</td>';
            	echo '<td style="text-align:center; font-size:small; font-weight:bold;">' . _('Abono') . '</td>';
			echo '</tr>';
			$cont=0;
			$error = "";
			foreach ($lineas as $line_num => $line){
				$datos = explode($separador, $line); # Convierte en array cada una de las lineas
				$columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
				if ($columnaslinea<$mincolumnas){
					$tieneerrores = 1;
					$error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO ') . intval($line_num+1);
					$error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' columnas separadas por "'.$separador.'" y tiene solo '.$columnaslinea. ' columnas' );
					prnMsg($error,'error');
					exit;
				}
				if ($line_num > 0){ //primera linea son titulos y no se procesa
					$fecha = $datos[$colfecha];
					$arrfecha = explode("/",$fecha);
                    $fecha = $arrfecha[2] . "-" . $arrfecha[1] . "-" . $arrfecha[0]; 
	       				$razon = $datos[$colrazon];
						$uninego = $datos[$coluninego];
						$subcatego = $datos[$colsubcatego];
						$ref = $datos[$colref];
						$concepto = $datos[$colconcepto];
						$cargo = $datos[$colcargo];
						$abono = $datos[$colabono];
					  	echo "<tr>";
	            		  	echo "<td style='text-align:left;'>";
	            		  		echo "<font size=2 color=red>".$cont."</font>";
						  	echo "</td>";
						  	echo "<td style='text-align:left;'>";
						  		echo "<font size=2 color=Darkblue><b>".$fecha."</b></font>";
	            		  	echo "</td>";
	            		  	echo "<td style='text-align:right;'>";
	            		  		echo "<font size=2 color=Darkblue><b>".$razon."</b></font>";
	                	  	echo "</td>";
						  	echo "<td style='text-align:right;'>";
						  		echo "<font size=2 color=Darkblue><b>".$uninego."</b></font>";
						  	echo "</td>";
						  	echo "<td style='text-align:right;'>";
						  		echo "<font size=2 color=Darkblue><b>".$subcatego."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$ref."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$concepto."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$cargo."</b></font>";
							echo "</td>";
							echo "<td style='text-align:right;'>";
								echo "<font size=2 color=Darkblue><b>".$abono."</b></font>";
							echo "</td>";
							
						echo "</tr>";
						$cont = $cont + 1;
						if($cargo == ''){
							$amount = ($abono*-1);
						}else{
							$amount=$cargo;
						}
						$SQL="INSERT INTO fjo_Movimientos(tagref,referencia,concepto,amount,userregister,fecha,fechapromesa,subcategoria)
						VALUES('" . $uninego . "','" . $ref . "','" . $concepto . "','" . $amount . "','".$_SESSION["UserID"]."',
						'".$fecha."','".$fecha."','".$subcatego."')";
						//echo "$SQL";
						$result = DB_query ( $SQL, $db );
						
						
						
						//echo "<br>SQL." . $SQL;
						//DB_query($SQL, $dbDataware);
						//mysqli_query($dbDataware, $SQL);
					//}
				} //if primer linea	
			}//fin del for
			echo "<tr>";
				echo "<td colspan = '18' style='text-align:center;'>";
					echo '<font color="red"><p>Los datos han sido guardados con exito.</p></font>';
					echo "<input type='submit' style='text-align:center;' name='nuevo' value='SUBIR OTRO ARCHIVO'>";
				echo "</td>";
			echo "</tr>";
		echo "</table>";
		echo "</form>";
		exit;
	}//fin del tipo de archivo	
	/**FIN LEE ARCHIVO**/
} 

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
echo "<table width=100% cellpadding=3 style='margin:auto;'>";
	echo "<tr>";
		echo "<td style='text-align:center;' colspan=2>";
        	echo "<font size=2 color=Darkblue><b>"._('CARGAR DATOS')."</b></font>";
      	echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td style='text-align:center;' colspan=2>";
        	echo "<font size=2 color=Darkblue><b>"._('ESTE ES EL FORMATO DEL ARCHIVO A SUBIR.')."</b></font>";
        	echo "<br><br>";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td style='text-align:center;' colspan='2'>";
			echo '<table cellspacing="1" cellpadding="1" style="margin:auto;" border="1">';
				echo '<tr>';
					echo '<td style="text-align:center;"><font size=2>'.('Fecha,').'</font></td>';
					echo '<td style="text-align:center;"><font size=2>'.('Razon social,').'</font></td>';
		            echo '<td style="text-align:center;"><font size=2>'.('unidad de negocio,').'</font></td>';
		            echo '<td style="text-align:center;"><font size=2>'.('subcategoria,').'</font></td>';
		            echo '<td style="text-align:center;"><font size=2>'.('referencia,').'</font></td>';
		            echo '<td style="text-align:center;"><font size=2>'.('concepto,').'</font></td>';
					echo '<td style="text-align:center;"><font size=2>'.('cargo,').'</font></td>';
					echo '<td style="text-align:center;"><font size=2>'.('abono').'</font></td>';
					
				echo '</tr>';
				echo '<tr>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
					echo '<td style="text-align:center;"><font size=1></font></td>';
				echo '</tr>';
			echo '</table>';
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td style='text-align:center;'><br><br>";
			echo "<font size=2 >"._('Caracter Separador')." : </font>&nbsp;";
			echo "<input style='text-align:center;' type='text' size=1 maxlength=1 name='separador' value='".$separador."'>&nbsp;&nbsp;";
			echo "&nbsp;&nbsp;<font size=2>". _('Archivo (.csv o .txt)') . " : </font>&nbsp;";
			echo "<input type='file' name='userfile' size=50 >&nbsp;";
		echo "</td>";
	echo "</tr>";
	echo "<tr>"; 
		echo "<td style='text-align:center;' colspan=2>";  
			echo "<br><br><input type='submit' name='cargar' value='SUBIR INFORMACION'>";
		echo "</td>";
	echo "</tr>";  
echo "</table>";
echo "</form>";
				  								
?>