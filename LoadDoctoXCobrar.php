<?php
/*REALIZADO POR: Ibeth Ortiz
1.- SE REALIZO ARCHIVO PARA LA MEGALTA DE PRODUCTOS DESDE UN ARCHIVO DE EXCEL O EDITOR DE TEXTOS ;
FECHA: 21/06/2010
*/

$PageSecurity = 5;

include('includes/session.inc');

$title = _('Carga de documentos de Cuentas X Cobrar');

include('includes/header.inc');
$funcion=755;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
//include('includes/MiscFunctions.inc');

if (isset($_POST['separador']))
{
    $separador = $_POST['separador'];
}
else{
    if (isset($_GET['separador'])){
        $separador = $_GET['separador'];
    }
    else{
        $separador = ",";
    }
}
if (isset($_POST['subir']))
{
    $subir = $_POST['subir'];
}

//CONDICIONES AL LEER EL ARCHIVO
if (isset($_POST['mostrar']) and $separador<>'')
{

    $nombre_archivo = $_FILES['userfile']['name'];
    $tipo_archivo = $_FILES['userfile']['type'];
    $tamano_archivo = $_FILES['userfile']['size'];
//echo '<br>tipo:'.$tipo_archivo;
    $filename = 'docxcobrarlist/'.$nombre_archivo;
     
    if ($tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream')
    {
       
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename))
        {
            
            # UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
        
            #Declara variables
        
            $tieneerrores=0;
            $lineatitulo=0;
            $mincolumnas=5;
        
            $columnacodigo=0;
            $columnaunineg=1;
            $columnatipo=2;
            $columnafecha=4;
            $columnaref=4;
            $columnacambio=5;
            $columnamonto=6;
            $columnaiva=7;
            $columnafolio=8;
            $columnamoneda=9;
            
            $columnapreciosIni=10;
            $columnapreciosFin=0;
        
                    
        };

    }
    
    $lineas = file($filename);
           
          //RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO 
          
            $cont=0;
            $j=0;
            $flagerror=false;
            
            foreach ($lineas as $line_num =>$line)
            {
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
                if(j>0){
                       
                    if($columnaslinea==10)
                        {
                                        //VALIDACION PARA CLIENTE
                                        $codigo=trim($datos[0]);
                                        
                                        if($codigo != ''){
                                        $sql= "select deb.debtorno, cus.debtorno from debtorsmaster as deb, custbranch as cus  where deb.debtorno='".$codigo."' and cus.debtorno='".$codigo."'";
                                        $result = DB_query($sql,$db);
                                        $myrow = DB_fetch_row($result);
                                        
                                        if ((DB_num_rows($result)==0))
                                        {
                                        $error = _('EL CLIENTE "' . $codigo . '" NO ESTA REGISTRADO EN EL SISTEMA. Verificar la linea no. ') .$j;
                                        prnMsg($error,'error');
                                        $flagerror=true;
                                            
                                        }
                                        }else{
                                        $error = _('EL CODIGO DEL CLIENTE VIENE VACIO. Verificar la linea no. ') .$j;
                                        prnMsg($error,'error');
                                         $flagerror=true;
                                        }
                        
                                        //VALIDACION SI LA UNIDAD DE NEGOCIO  VIENE VACIA O NO ESTA REGISTRADA EN EL SISTEMA
                                        $unineg=trim($datos[1]);
                                        
                                        $sql= "select tagref from tags where tagref='".$unineg."'";
                                        $result = DB_query($sql,$db);
                                        $myrow = DB_fetch_row($result);
                                        if($unineg != ''){
                                          if(DB_num_rows($result)==0){
                                          $error = _('LA UNIDAD DE NEGOCIO NO EXISTE EN EL SISTEMA') .$j;
                                          prnMsg($error,'error');
                                          $flagerror=true;
                                          }
                                        }else{
                                        $error = _('LA UNIDAD DE NEGOCIO VIENE VACIA ') .$j;
                                        prnMsg($error,'error');
                                         $flagerror=true;
                                        }
                                        
                                        //VALIDACION SI EL TIPO DE DOCUMENTO VIENE VACIA
                                        $tipo=trim($datos[2]);
                                        $sql= "select typeid from systypes where typeid='".$tipo."'";
                                        $result = DB_query($sql,$db);
                                        $myrow = DB_fetch_row($result);
                                        
                                        if($tipo != ''){
                                           if(DB_num_rows($result)==0){
                                          $error = _('EL TIPO DE DOCUMENTO NO EXISTE EN EL SISTEMA') .$j;
                                          prnMsg($error,'error');
                                          $flagerror=true;
                                          } 
                                            
                                        }else{
                                        $error = _('EL TIPO DE DOCUMENTO VIENE VACIA. Verificar la linea no. ') .$j;
                                        prnMsg($error,'error');
                                         $flagerror=true;
                                        }
                                        
                                        
                                        
                                        //VALIDACION SI LA FECHA DE VENCIMIENTO VIENE VACIA
                                        $fecha=trim($datos[4]);
                                        
                                        if($fecha == ''){
                                        $error = _('EL CAMPO DE LA FECHA VIENE VACIA O NO ESTA ESCRITA CORRECTAMENTE Verificar la linea no. ') .$j;
                                        prnMsg($error,'error');
                                         $flagerror=true;
                                        }
                                        
                                        //VALIDACION SI EL CAMPO DE TIPO DE CAMBIO VIENE VACIO
                                        $cambio=trim($datos[5]);
                                        
                                        if($cambio == ''){
                                        $error = _('EL CAMPO DE TIPO DE CAMBIO VIENE VACIA. Verificar la linea no. ') .$j;
                                        prnMsg($error,'error');
                                         $flagerror=true;
                                        }
                                        
                                        //VALIDACION SI EL MONTO VIENE VACIO 
                                        $monto=trim($datos[6]);
                                                                                
                                        if($monto == ''){
                                            $error = _('EL CAMPO DE LA MONTO VIENE VACIO. Verificar la linea no. ') .$j;
                                            prnMsg($error,'error');
                                             $flagerror=true;
                                        }
                                        
                                        //VALIDACION SI EL IVA
                                        $iva=trim($datos[7]);
                                                                                
                                        if($iva == ''){
                                            $error = _('EL CAMPO DEL IVA VIENE VACIO. Verificar la linea no. ') .$j;
                                            prnMsg($error,'error');
                                             $flagerror=true;
                                        }
                                         
                                        //VALIDACION SI EL FOLIO VACIO 
                                        $folio=trim($datos[8]);
                                                                   
                                        if($folio != ''){
                                        $sql= "select folio from debtortrans where folio='".$folio."'";
                                        
                                        $result = DB_query($sql,$db);
                                        
//                                        $myrow = DB_fetch_row($result);
                                            if(DB_num_rows($result)>0){
                                            $error = _('EL FOLIO YA EXISTE EN EL SISTEMA. Verificar la linea no.') .$j;
                                            prnMsg($error,'error');
                                            $flagerror=true;
                                          } 
                                            
                                        }else{
                                            $error = _('EL CAMPO DEL FOLIO VIENE VACIO. Verificar la linea no. ') .$j;
                                            
                                            prnMsg($error,'error');
                                             $flagerror=true;
                                        }
                                        
                                        //VALIDACION EL TIPO DE MONEDA VIENE VACIO O SI ESTA EN EL SISTEMA
                                        $moneda=trim($datos[9]);
                                         
                                        $sql= "select currabrev from currencies where currabrev='".$moneda."'";
                                        $result = DB_query($sql,$db);
                                        $myrow = DB_fetch_row($result);
                                        
                                        if($moneda != ''){
                                           if(DB_num_rows($result)==0){
                                          $error = _('EL TIPO DE MONEDA NO EXISTE EN EL SISTEMA. Verificar la linea no.') .$j;
                                          prnMsg($error,'error');
                                          $flagerror=true;
                                          } 
                                            
                                        }else{
                                        $error = _('EL TIPO DE MONEDA VIENE VACIO. Verificar la linea no. ') .$j;
                                        prnMsg($error,'error');
                                         $flagerror=true;
                                        }
                                        
                        }else{
                            $error = _('EL NUMERO DE COLUMNAS NO ES IGUAL A 10 EN LA LINEA no.'.$j);
                            prnMsg($error,'error');
                            $flagerror=true;

                            }
                                
                        
                }
                $j++;//incremento de numeros de linea
            }//fin de foreach
}
//FIN DE CONDICIONES
if(isset($_GET['subir'])){
    $subir='nosubir';
}elseif(isset($_POST['subir'])){
    $subir='sisubir';
    
}else{
    $subir='';
}

                                       


//ALTA DE INFORMACION
if ($subir=='sisubir' )
{
    $nombre_archivo = $_POST['archivo'];
   

    $filename = 'docxcobrarlist/'.$nombre_archivo;
     
            
            $columnacodigo=0;
            $columnaunineg=1;
            $columnatipo=2;
            $columnafecha=3;
            $columnaref=4;
            $columnacambio=5;
            $columnamonto=6;
            $columnaiva=7;
            $columnafolio=8;
            $columnamoneda=9;
 $j=0;           
 $lineas = file($filename);
 foreach($lineas as $line_num =>$line)
              {
                echo "entra<br>";
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
                if($j>0){
                //ASIGNACION DE CADA POSICION DEL ARREGLO A UNA VARIABLE
                $codigo=trim($datos[0]);
                $unineg=trim($datos[1]);
                $tipo=trim($datos[2]);
                $fecha=trim($datos[3]);
                $ref=trim($datos[4]);
                $cambio=trim($datos[5]);
                $monto=trim($datos[6]);
                $iva=trim($datos[7]);
                $folio=trim($datos[3]);
                $moneda=trim($datos[9]);
                
                $codigo=$datos[0];
                $unineg=$datos[1];
                $tipo=$datos[2];
                $fecha=$datos[5];
                $fechaorigen=$datos[4];
                $ref=$datos[3];
                $cambio=1;//$datos[5];
                $monto=$datos[6];
                $iva=$datos[7];
                $saldo=$monto-$iva;//$datos[8];
                $moneda='MXN';
                if ($tipo=='Factura'){
                    if ($saldo>0){
                        $tipo=410;
                    }else{
                        $tipo=450;
                    }
                    
                }else{
                    $tipo=450;
                }
                $transno = GetNextTransNo($tipo, $db);
                //Convertimos a mayusculas el valor de las variables
                //$folio=strtoupper($folio);
                $moneda=strtoupper($moneda);
                $ref=strtoupper($ref);
                
                $iva=0;
            $sql = "INSERT INTO debtortrans (debtorno,
                                tagref,
				type,
				trandate,
				reference,
                                rate,
				ovamount,
				ovgst,
				folio,
                                transno,
				currcode,
                                origtrandate
                                )
			VALUES ('".$codigo."',
				 " .$unineg. ",
				 " .$tipo. ",
				'" .$fecha. "',
				'" .$ref. "',
                                 " .$cambio. ",
				 " .$saldo. ",
				 " .$iva. ",
				 '" .$folio. "',
                                  '" .$transno. "',
				 '" .$moneda. "',
                                  '" .$fechaorigen. "'
				)";
                  
				
				$result = DB_query($sql,$db);
                                //echo '<br>sql:'.$sql.'<br>';
                                
                               
                                
                }
                $j++;
                                
              }
              prnMsg("SE CARGARON EXITOSAMENTE LOS DOCUMENTOS AL SISTEMA ",'sucess');
              
}else{
                 
          
                   
};

//FIN DE ALTA DE INFORMACION


echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
  
  
  echo "<br>";
  echo "<table width=100% >";
    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2>";
        echo "<font size=2 color=Darkblue><b>"._('CARGA DE DOCUMENTOS DE CUENTAS X COBRAR')."</b></font><br><br>";
        echo "<font size=2 color=Darkblue><b>"._('Lista de Documentos')."</b></font><br><br>";
      echo "</td>";
    echo "</tr>";
    
//if(isset($_POST['mostrar'])){


echo '<CENTER><table border=1>';
	echo "<tr>
		<th>" . _('Línea') . "</th>
                <th>" . _('Clave Cliente') . "</th>
		<th>" . _('Unidad de Negocio') . "</th>
		<th>" . _('Tipo de Documento') . "</th>
		<th>" . _('Fecha de Vencimiento') . "</th>
		<th>" . _('Referencia') . "</th>
                <th>" . _('Tipo de Cambio') . "</th>
                <th>" . _('Monto sin IVA') . "</th>
                <th>" . _('IVA') . "</th>
                <th>" . _('Folio') . "</th>
                <th>" . _('Moneda') . "</th>
                
                
	</tr>";
        
   
            $k=0; //row colour counter
            $j=0;
            if(file($filename)!=''){
            $lineas = file($filename);
           
          //RECORRE CADA UNA DE LAS LINEAS DEL ARCHIVO 
          
            //$cont=0;
            
            foreach ($lineas as $line_num =>$line)
              {
                $datos = explode($separador, $line); # Convierte en array cada una de las lineas
                $columnaslinea = count($datos);      # Obtiene el numero de columnas de la linea en base al separador
                if($j>0){
                //ASIGNACION DE CADA POSICION DEL ARREGLO A UNA VARIABLE
                $codigo=$datos[0];
                $unineg=$datos[1];
                $tipo=$datos[2];
                $fecha=$datos[4];
                $ref=$datos[3];
                $cambio=1;//$datos[5];
                $monto=$datos[6];
                $iva=$datos[7];
                $folio=$datos[8];
                $moneda='MXN';//$datos[9];
                
                
                //Convertimos a mayusculas el valor de las variables
               // $folio=strtoupper($folio);
                $moneda=strtoupper($moneda);
                $ref=strtoupper($ref);
                
        
        
		if ($k==1){
			echo '<tr class="EvenTableRows">';                                         
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
       
        printf("<td><font size=1>%s</td>
		<td><font size=1>%s</td>
		<td><font size=1>%s</td>
		<td><font size=1>%s</td>
		<td><font size=1>%s</td>
		<td><font size=1>%s</td>
                <td><font size=1>%s</td>
		<td><font size=1>%s</td>
                <td><font size=1>%s</td>
		<td><font size=1>%s</td>
                <td><font size=1>%s</td>
		
                
		</tr>",
                $j,
                $codigo,
                $unineg,
                $tipo,
                $fecha,
                $ref,
                $cambio,
                $monto,
                $iva,
                $folio,
                $moneda);
                }
                $j++;//numeracion de lineas
                
               }
            
            }
            
        echo '</table>';
      echo "</td>";
    echo "</tr>";
 //}


    echo "</select></td></tr>";
 
    if(!isset($_POST['mostrar'])){
    echo "<tr>";
      echo "<td style='text-align:center;'><br><br>";
        echo "<font size=2 >"._('Caracter Separador')." : </font>&nbsp;";
        echo "<input style='text-align:center;' type='text' size=1 maxlength=1 name='separador' value='".$separador."'>&nbsp;&nbsp;";
        echo "&nbsp;&nbsp;<font size=2>". _('Archivo (.csv o .txt)') . " : </font>&nbsp;";
        echo "<input type='file' name='userfile' size=50 >&nbsp;";        
      echo "</td>";
    echo "</tr>";

    echo "<tr>";
      echo "<td style='text-align:center;' colspan=2><br>";

        echo "<input type='submit' name='mostrar' value='MOSTRAR INFORMACION'>";
        
      echo "</td>";
    echo "</tr>"; 
    
    }
    else{

       echo "<td style='text-align:center;' colspan=2><br><br>";

        echo "<input type='submit' name='subir' value='SUBIR INFORMACION'><br><br>";
        echo "<input  type='hidden'  name='archivo' value='".$nombre_archivo."'>";
        echo '<a href="' . $rootpath . '/LoadDoctoXCobrar.php?subir=nosubir'.'">' . _('Cargar Nuevamente Información') . '</a><br>' . "\n";


      echo "</td>";
    echo "</tr>"; 
    
  echo "</table>";
    }
    

        //unset($subir);

    echo "</form>";
    
  



?>