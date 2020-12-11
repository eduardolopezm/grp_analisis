<?php

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Cargar Secciones');

include('includes/header.inc');
$funcion=556;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

#recupera el signo que servira para hacer la separacion de cada valor
if (isset($_POST['separador']))
{
    $separador = $_POST['separador'];
}
else{
    if (isset($_GET['separador'])){
        $separador = $_GET['separador'];
    }
    else{
        #se queda por defaul la coma (,)
        $separador = ",";
    }
}

#si la accion es cargar el archivo y la variable que sirve de separador de valores es diferente de vacio
if (isset($_POST['cargar']) and $separador<>''){
    $nombre_archivo = $_FILES['userfile']['name'];
    $tipo_archivo = $_FILES['userfile']['type'];
    $tamano_archivo = $_FILES['userfile']['size']; 
    $filename = 'pricelists/'.$nombre_archivo;
    
  //  echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;
     
    if ($tipo_archivo=='text/plain' or $tipo_archivo=='text/csv' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream'){
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename)){
        
            $tieneerrores=0;
            $mincolumnas=3;
            
            $numeroseccion=0;
            $descripcion=1;
            $tiposeccion=2;
            
            
            # ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO    
            $lineas = file($filename);
            
            # ****************************
            # **** RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO ****
            # ****************************
            
            echo "<table width=100% cellpadding=3 border=1>";
            echo "<tr>";
              echo "<td style='text-align:center;' colspan=12>";
                echo "<font size=2 color=Darkblue><b>"._('RESULTADOS DE LA CARGA DE LAS CUENTAS')."</b></font>";
              echo "</td>";
            echo "</tr>";
           
            $cont=0;
            foreach ($lineas as $line_num => $line){
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
			    $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
                if ($columnaslinea<$mincolumnas){
                  $tieneerrores = 1;
                  $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO.. ') . intval($line_num+1);
                  $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'" y tiene '.$columnaslinea );
                  prnMsg($error,'error');
                  exit;
                }    

				if ($line_num > 0){
					//verificar ultima linea
					if ($datos[$numeroseccion]=='' && $datos[$descripcion]=='' &&  $datos[$tiposeccion]==''){
						echo "</table>";
						prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$cont." GRUPOS AUTORIZADOS.",'sucess');
						exit;
					}
				
					if ($datos[$numeroseccion]==''){
						$error = _('EL NUMERO DE LA SECCION NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
						prnMsg($error,'error');
						exit;
					}           #verifica que los datos de la seccion sean numericos
					if (!is_numeric($datos[$numeroseccion])) {
						$error = _('EL NUMERO DE LA SECCION DEBE SER UN NUMERO ENTERO.<br> Verificar la linea no. ') . intval($line_num+1);
						prnMsg($error,'error');
						exit;
					}
									#verifica que el dato de la seccion sea un dato entero
					if (strpos($datos[$numeroseccion],".")>0) {
						$error = _('EL NUMERO DE LA SECCION DEBE SER UN NUMERO ENTERO.<br> Verificar la linea no. ') . intval($line_num+1);
						prnMsg($error,'error');
						exit;		
					} 
					$codigoseccion = trim($datos[$numeroseccion]);
	
					if ($datos[$descripcion]==''){
						 
						$error = _('LA DESCRIPCION DE LA SECCION NO DEBE DE IR VACIA IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
						prnMsg($error,'error');
						exit;
						  
					} 
					$codigodescripcion = trim($datos[$descripcion]);
														
					if ($datos[$tiposeccion]==''){
						$error = _('EL TIPO DE LA SECCION NO PUEDE IR VACIO .<br> Verificar la linea no. ') . intval($line_num+1);
						prnMsg($error,'error');
						exit;
						  
					} 
					$codigotiposeccion = trim($datos[$tiposeccion]);
														   
					echo "<tr>";
					echo "<td style='text-align:left;'>";
					echo "<font size=2 color=red>".$cont."</font>";
					echo "</td>";
					echo "<td style='text-align:left;'>";
					echo "<font size=2 color=Darkblue><b>".$codigoseccion."</b></font>";
					echo "</td>";
					echo "<td style='text-align:left;'>";
					echo "<font size=2 color=Darkblue>".$codigodescripcion."</font>";
					echo "</td>";
					echo "<td style='text-align:left;'>";
					echo "<font size=2 color=Darkblue>".$codigotiposeccion."</font>";
					echo "</td>";
					echo "</tr>";
	
	
					$sql= "SELECT COUNT(*)
							FROM accountsection
							WHERE sectionid='".$codigoseccion."'";
					$result = DB_query($sql,$db);
					$myrow = DB_fetch_row($result);
															 
					if ($myrow[0]==0){    
						$sql = "INSERT INTO accountsection (
										sectionid,
										sectionname,
										sectiontype)
								VALUES (
										" . $codigoseccion . ",
										'" . $codigodescripcion ."',
										". $codigotiposeccion ."
									)";
						$msg = _('Record inserted');
						$result = DB_query($sql,$db);
					} else {
						$sql = "UPDATE accountsection
								SET sectionname='" . $codigodescripcion . "', 
								sectiontype =". $codigotiposeccion ." 
								WHERE sectionid = " . $codigoseccion;
								$msg = _('Record update');
						 $result = DB_query($sql,$db);
					}
					 $cont = $cont + 1;
                }; //if primera
				    
              }; # Fin del for que recorre cada linea
            
            echo "</table>";
            prnMsg("SE CARGARON EXITOSAMENTE AL SISTEMA ".$cont." CUENTAS AUTORIZADAS.",'sucess');

        }else{ 
           echo "Ocurrió algún error al subir el fichero. No pudo guardarse."; 
        };
    };
}

echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  echo "<br>";
  echo "<table width=100% cellpadding=3>";
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('CARGAR CUENTAS')."</b></font>";
       // echo "<br><br><font size=2 color=A00000><b>*** "._('La información que se cargue aplicará para todas las sucursales')." ***</b></font><br><br>";
      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('ESTE ES EL FORMATO DEL ARCHIVO A SUBIR.')."</b></font>";
        echo "<br><br>";
      echo "</td>";
    echo "</tr>";
    
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo '<table cellpadding=2>';
          echo '<tr>';
            echo '<td style="text-align:center;"><font size=1>Numero de seccion</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Nombre de la seccion</font></td>';
            echo '<td style="text-align:center;"><font size=1>,</font></td>';
            echo '<td style="text-align:center;"><font size=1>Tipo de seccion</font></td>';
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