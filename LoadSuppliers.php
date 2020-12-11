<?php
/* ARCHIVO POR: Lucero Rubio*/
/* FECHA: 22/junio/2010*/
/*DESCRIPCION: ALTA DE PROVEEDORES POR MEDIO DE UN ARCHIVO CSV O TXT
SEPARADO POR COMAS*/
$PageSecurity = 5;
$funcion=752;
include('includes/session.inc');
$title = traeNombreFuncion($funcion, $db);
include('includes/header.inc');
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

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
    
    if (isset($_GET['SupplierID'])){
	$SupplierID = strtoupper($_GET['SupplierID']);
    } elseif (isset($_POST['SupplierID'])){
	    $SupplierID = strtoupper($_POST['SupplierID']);
    } else {
	    unset($SupplierID);
    } 
    

if (isset($_POST['mostrar']) || (isset($_POST['cargar'])))
{
    if (isset($_POST['mostrar']))
    {
	$nombre_archivo = $_FILES['userfile']['name']; 
	$tipo_archivo = $_FILES['userfile']['type']; 
	$tamano_archivo = $_FILES['userfile']['size'];
	
	$filename = 'pricelists/'.$nombre_archivo;
     //le decimos que archivo queremos leer
     //$xl_reader->read($nombre_archivo);
    //echo "EL TIPO DE ARCHIVO ES:".$tipo_archivo;
    // echo $tipo_archivo;
	if ($tipo_archivo == 'text/csv' OR $tipo_archivo=='text/plain' OR $tipo_archivo=='application/vnd.ms-excel' OR $tipo_archivo=='application/octet-stream')
	{
	    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $filename))
	    {
		# UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
		
		#Declara variables
		
		$tieneerrores=0;
		

		$lineanombre = 0;
		$linearfc = 1;
		$lineacalle = 2;
		$lineacolonia = 3;
		$lineaciudad = 4;
		$lineaestado = 5;
		$lineacp = 6;
		$lineaclaveproveedor = 7;
		$lineamoneda = 8;
		$lineaemail = 9;
		$lineaactivo = 10;
		$lineacuentacontable = 11;
		$lineacurp = 12;
		$linearepresentantelegal = 13;
		
	
		
		$lineatitulo=-1; //-1 para que no verifique columnas del titulo
		$mincolumnas=14;
		
		    # ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO

		    $lineas = file($filename);
		    $cont=0;
		   	
			echo "<table class='table table-bordered'>";
			echo "<thead>";
			echo  "<tr class='header-verde'>";
			echo  "<th scope='col'>Id</th>";
			echo  "<th scope='col'>Razon Social/Nombre</th>";
			echo  "<th scope='col'>RFC</th>";
			echo  "<th scope='col'>Calle</th>";
			echo  "<th scope='col'>Colonia</th>";
			echo  "<th scope='col'>Ciudad</th>";
			echo  "<th scope='col'>Estado</th>";
			echo  "<th scope='col'>C.P</th>";
			echo  "<th scope='col'>Proveedor</th>";
			echo  "<th scope='col'>Moneda</th>";
			echo  "<th scope='col'>Email</th>";
			echo  "<th scope='col'>Activo</th>";
			echo  "<th scope='col'>Cuenta contable</th>";
			echo  "<th scope='col'>CURP</th>";
			echo  "<th scope='col'>Representante legal</th>";
			echo  "</tr>";
			echo  "</thead>";
			echo  "<tbody>";
			
		    $arrLineas = array();
		    $lineaerror = false;
		    
		foreach ($lineas as $line_num => $line)
		{
		    $lineaerror = false;
		    $arrLineas[$line_num+1] = 0;
		    if ($line_num == $lineatitulo) {
			// echo "<tr>";
			// echo "<td>Id</td>"; 
			// echo "<td>Nombre o Razon Social</td>";
			// echo "<td>RFC</td>";
			// echo "<td>Calle</td>";
			// echo "<td>Colonia</td>";
			// echo "<td>Ciudad/Estado</td>";
			// echo "<td>CP</td>";
			// echo "<td>Clave Proveedor</td>";
			// echo "<td>Moneda</td>";
			// echo "<th>Email</td>";
			// echo "<th>Activo</td>";
			// echo "<th>Cuenta contable</th>";
			// echo "<th>Curp</th>";
			// echo "<th>Representante legal</th>";
			
			// echo "</tr>";
		    } else
		    {
			unset($datos);
			$datos = explode($separador, $line); # Convierte en array cada una de las lineas
			$columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
				
			if ($columnaslinea<$mincolumnas)
			{
			    $tieneerrores = 1;
			    echo "<br>";
			    $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO.. ') . intval($line_num+1);
			    $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'" y tiene '.$columnaslinea );
			    prnMsg($error,'error');
			    $con=1;
			} else
			{
				    # *** RECORRE LAS LINEAS DE DATOS
								    
				    # *****COLUMNA DE NOMBRE O RAZON SOCIAL******
				    # si viene vacia la RAZON SOCIAL
					
					if ($datos[$lineanombre]=='')
                                {
				    $codigonombre='';
				    echo "<br>";
                                    $error =_('EL NOMBRE SOCIAL NO PUEDE ESTAR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
                                    prnMsg($error,'error');
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    $con=1;
				    
                                }else {
                                        $codigonombre = trim($datos[$lineanombre]);
					if ($arrLineas[$line_num+1] == 0){
					    $arrLineas[$line_num+1] = 0;
					    $lineaerror = 0;
					}
					}
				    
				    # *****COLUMNA RFC******
				    # si viene vacia la columna de RFC
				
				if ($datos[$linearfc]=='')
				    {
					$codigorfc ='';
					$error =_('EL RFC NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
				    }else {
					  $codigorfc = trim($datos[$linearfc]);
					  if ($arrLineas[$line_num+1] == 0){
					      $arrLineas[$line_num+1] = 0;
					      $lineaerror = 0;
					  }
				      }
					    
				    # *****COLUMNA CALLE******
				    # si viene vacia la columna de calle
				if ($datos[$lineacalle]==''){
				    $codigocalle ='';
				    $error =_('LA CALLE PUEDE NO IR VACIA .<br> Verificar la linea no. ').intval($line_num+1);
				    prnMsg($error,'error');
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    //$con=1;
				      
                                }else {
				    $codigocalle = trim($datos[$lineacalle]);
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}
										
				    # *****COLUMNA COLONIA******
				    # si viene vacia la columna de colonia
				    
				if ($datos[$lineacolonia]=='') 
                                  {
				    $codigocolonia ='';
				    $error =_('LA COLONIA NO PUEDE IR VACIA .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
                                  } else {
					
                                        $codigocolonia = trim($datos[$lineacolonia]);
					if ($arrLineas[$line_num+1] == 0){
					    $arrLineas[$line_num+1] = 0;
					    $lineaerror = 0;
					}
				    }
				    
				# *****COLUMNA CIUDAD/ESTADO******
				    # si viene vacia la columna de estado
				    
				if ($datos[$lineaciudad]=='') 
                                  {
				    $codigociudad ='';
				    $error =_('LA COLUMNA CIUDAD/ESTADO NO PUEDE IR VACIA .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
                                  } else {
					
                                        $codigociudad = trim($datos[$lineaciudad]);
					if ($arrLineas[$line_num+1] == 0){
					    $arrLineas[$line_num+1] = 0;
					    $lineaerror = 0;
					}
					}
					
					# *****COLUMNA CIUDAD/ESTADO******
				    # si viene vacia la columna de estado
				    
				if ($datos[$lineaestado]=='') 
				{
					$codigoestado ='';
					$error =_('LA COLUMNA CIUDAD/ESTADO NO PUEDE IR VACIA .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
						
									} else {
					
										$codigoestado = trim($datos[$lineaestado]);
					if ($arrLineas[$line_num+1] == 0){
						$arrLineas[$line_num+1] = 0;
						$lineaerror = 0;
					}
					}
										
				# *****COLUMNA CODIGO POSTAL******
				    # si viene vacia la columna de CP
				    
				if ($datos[$lineacp]==''){
				    $codigocp ='';
				    $error =_('EL CODIGO POSTAL NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
                                } else {
				    $codigocp = trim($datos[$lineacp]);
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}
				
				# *****COLUMNA Telefono******
				# si viene vacia la columna de telefono
				/*if ($datos[$lineatelefono]==''){
				    $codigotelefono ='';
				    //$error =_('EL TELEFONO NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    //$con=1;
				      
                                } else {
				    $codigotelefono = trim($datos[$lineatelefono]);
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}*/
				
				# *****COLUMNA CLAVE******
				    # si viene vacia la columna de CP
				$lineaclaveproveedor = 7;
				
				if ($datos[$lineaclaveproveedor]==''){
				    $codigoclaveproveedor ='';
				    //$error =_('LA CLAVE DEL PROVEEDOR NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
				    //prnMsg($error,'error');
				    $arrLineas[$line_num+1] = 1;
				    $lineaerror = 1;
				    //$con=1;
				      
                                } else {
				    $codigoclaveproveedor = trim($datos[$lineaclaveproveedor]);
				    if ($arrLineas[$line_num+1] == 0){
					$arrLineas[$line_num+1] = 0;
					$lineaerror = 0;
				    }
				}
				
				
				# *****COLUMNA moneda******
				
				if ($datos[$lineamoneda]=='') {
					$codigomoneda ='MXN';
				
				}else {
					$codigovalido = 1;
					$codigomoneda = trim($datos[$lineamoneda]);
				}

				# *****COLUMNA email******
				
				if ($datos[$lineaemail]=='')
				    {
					$codigoemail ='';
					$error =_('EL CURP NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
				    }else {
					  $codigoemail = trim($datos[$lineaemail]);
					  if ($arrLineas[$line_num+1] == 0){
					      $arrLineas[$line_num+1] = 0;
					      $lineaerror = 0;
					  }
				      }

				    # *****COLUMNA CURP******
				    
				if ($datos[$lineacurp]=='')
				    {
					$codigocurp ='';
					$error =_('EL CURP NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
				    }else {
					  $codigocurp = trim($datos[$lineacurp]);
					  if ($arrLineas[$line_num+1] == 0){
					      $arrLineas[$line_num+1] = 0;
					      $lineaerror = 0;
					  }
				      }

					# *****COLUMNA ACTIVO******
										
				if ($datos[$lineaactivo]=='')
					{
					$codigoactivo ='';
					$error =_('EL ESTATUS DE ACTIVO O INACTIVO NO PUEDE IR VACIO .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
					
					}else {
					$codigoactivo = trim($datos[$lineaactivo]);
					if ($arrLineas[$line_num+1] == 0){
						$arrLineas[$line_num+1] = 0;
						$lineaerror = 0;
					}
					}  

				    # *****COLUMNA CUENTA CONTABLE******
				    
				if ($datos[$lineacuentacontable] != '')
				{
					$codigocuentacontable = trim($datos[$lineacuentacontable]);
					$validacion  = fnValidarCuentaContable($db, $codigocuentacontable);
					if (!$validacion['success']) {
						prnMsg("No existe la cuenta contable." . $codigocuentacontable,'info');
						$lineaerror = 1;
					}
				}  
					
				    # *****COLUMNA representante legal******
				    
				if ($datos[$linearepresentantelegal]=='')
				    {
					$codigorepresentantelegal ='';
					$error =_('LA REPRESENTANTE LEGAL NO PUEDE IR VACIA .<br> Verificar la linea no. ').intval($line_num+1);
					prnMsg($error,'error');
					$arrLineas[$line_num+1] = 1;
					$lineaerror = 1;
					//$con=1;
				      
				    }else {
					  $codigorepresentantelegal = trim($datos[$linearepresentantelegal]);
					  if ($arrLineas[$line_num+1] == 0){
					      $arrLineas[$line_num+1] = 0;
					      $lineaerror = 0;
					  }
				      }    
				
				# Inserta registro en la tabla "budgets"
				if ($arrLineas[$line_num+1] == 1)
				{
				$bgcolor = "#FFFF00";
				}else{
				$bgcolor = "#FFFFFF";
				}

				echo  "<tr>";
				echo  "<th scope='col'>".($line_num+1)."</th>";
				echo  "<th scope='col'>".$codigonombre."</th>";
				echo  "<th scope='col'>".$codigorfc."</th>";
				echo  "<th scope='col'>".$codigocalle."</th>";
				echo  "<th scope='col'>".$codigocolonia."</th>";
				echo  "<th scope='col'>".$codigociudad."</th>";
				echo  "<th scope='col'>".$codigoestado."</th>";
				echo  "<th scope='col'>".$codigocp."</th>";
				echo  "<th scope='col'>".$codigoclaveproveedor."</th>";
				echo  "<th scope='col'>".$codigomoneda."</th>";
				echo  "<th scope='col'>".$codigoemail."</th>";
				echo  "<th scope='col'>".$codigoactivo."</th>";
				echo  "<th scope='col'>".$codigocuentacontable."</th>";
				echo  "<th scope='col'>".$codigocurp."</th>";
				echo  "<th scope='col'>".$codigorepresentantelegal."</th>";
				echo  "</tr>";

			}// FIN DEL ELSE QUE RECORRE LAS LINEAS DE DATOS
		    }
		}// FIN DEL FOR QUE RECORRE CADA LINEA


		echo  "</tbody>";
		echo  "</table>";
		
		    // SI NO HAY ERROR CON LA VARIABLE "CON"
		    if ($lineaerror<1)
		    {
			echo "<tr><td colspan='6' style='text-align:center;'>";
			echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form2">';
			echo "<br>";
			echo "<component-button type='submit' name='cancel' value='Cancelar' class='glyphicon glyphicon-trash'></component-button>";
			echo "&nbsp;&nbsp;";
			echo "<component-button type='submit' name='cargar' value='Subir archivo' class='glyphicon glyphicon-circle-arrow-up'></component-button>";
			echo "&nbsp;&nbsp;";
			echo "<component-button type='submit' name='mostrar' value='Mostrar Informacion' class='glyphicon glyphicon-thumbs-up'></component-button>"; 
            echo "<br><br>";
			echo "<input type='hidden' name='nombrearchivo' value='" . $nombre_archivo . "'>";
			echo "<input type='hidden' name='separador' value='" . $separador . "'>";
			echo "<input type='hidden' name='codigovalido' value='" . $codigovalido . "'>";
			echo '</form>';
			echo "</td></tr>";   
		    }else{
			//nada
		    }
	    }else{
		echo "<br><center>";
		prnMsg(_('Ocurri� algun error, el fichero no pudo guardarse'),'error');
		}
	}
	else{
	
	    echo "<br><center>";
		prnMsg(_('La extensi�n del archivo no es correcta'),'error');
		
		exit;
		
	}
    }// FIN DE SELECCION DE MOSTRAR


if (isset($_POST['cargar']) and $separador<>'') {
	    $nombre_archivo = $_POST['nombrearchivo']; 
	    
	    $filename = 'pricelists/'.$nombre_archivo;
	    
		# UNA VEZ QUE EL ARCHIVO SE CARGO, ENTRA A LEER EL CONTENIDO
		#Declara variables
		
		$tieneerrores=0;
		
		$lineanombre = 0;
		$linearfc = 1;
		$lineacalle = 2;
		$lineacolonia = 3;
		$lineaciudad = 4;
		$lineaestado = 5;
		$lineacp = 6;
		$lineaclaveproveedor = 7;
		$lineamoneda = 8;
		$lineaemail = 9;
		$lineaactivo = 10;
		$lineacuentacontable = 11;
		$lineacurp = 12;
		$linearepresentantelegal = 13;
	
		
		$lineatitulo=-1; //-1 para que no verifique columnas del titulo
		$mincolumnas=14;
		
		# ABRE EL ARCHIVO Y LO ALMACENA EN UN OBJETO    
		$lineas = file($filename);
		
		$cont=0;
		echo "<table class='table table-bordered'>";
		
		foreach ($lineas as $line_num => $line)
		{
		    if ($line_num == $lineatitulo) {
			  
			  echo "<thead>";
			  echo  "<tr class='header-verde'>";
			  echo  "<th scope='col'>Razon Social/Nombre</th>";
			  echo  "<th scope='col'>RFC</th>";
			  echo  "<th scope='col'>Calle</th>";
			  echo  "<th scope='col'>Colonia</th>";
			  echo  "<th scope='col'>Ciudad</th>";
			  echo  "<th scope='col'>Estado</th>";
			  echo  "<th scope='col'>C.P</th>";
			  echo  "<th scope='col'>Proveedor</th>";
			  echo  "<th scope='col'>Moneda</th>";
			  echo  "<th scope='col'>Email</th>";
			  echo  "<th scope='col'>Activo</th>";
			  echo  "<th scope='col'>Cuenta contable</th>";
			  echo  "<th scope='col'>CURP</th>";
			  echo  "<th scope='col'>Representante legal</th>";
			  echo  "</tr>";
			  echo  "</thead>";
			  echo  "<tbody>";
			  echo  "</tbody>";
			  echo  "</table>";

		    } else
		    {
			$datos = explode($separador, $line); # Convierte en array cada una de las lineas
			$columnaslinea = count($datos);           # Obtiene el numero de columnas de la linea en base al separador
				
			if ($columnaslinea<$mincolumnas)
			{
			    $tieneerrores = 1;
			    $error = _('EL NUMERO MINIMO DE COLUMNAS REQUERIDAS NO SE CUMPLE EN LA LINEA NO.. ') . intval($line_num+1);
			    $error .= '<br>'. _('La estructura del archivo debe de tener al menos '.$mincolumnas.' datos separados por "'.$separador.'" y tiene '.$columnaslinea );
			    prnMsg($error,'error');
			    exit;
			}    
			else
			{
				    # *** RECORRE LAS LINEAS DE DATOS
								    
			 
				    # *****COLUMNA RAZON SOCIAL / NOMBRE******
				    # si viene vacio el nombre
				
					if ($datos[$lineanombre]=='')
					{
					$codigonombre='';
					echo " ";
		
					}else {
					$codigovalido = 1;
					$codigonombre = trim($datos[$lineanombre]);
				}
				    
				    # *****COLUMNA RFC******
				    # si viene vacia la columna de RFC
				    if ($datos[$linearfc]=='')
				      {
					$codigorfc ='';
					echo " ";
					 
				      }else {
				    $codigovalido = 1;
				    $codigorfc = trim($datos[$linearfc]);
				}
					    
				    # *****COLUMNA calle******
				    # si viene vacia la columna de calle
				if ($datos[$lineacalle]==''){
				    $codigocalle ='';
				    echo " ";
				}else{
				    $codigovalido = 1;
				    $codigocalle = trim($datos[$lineacalle]);
				    //echo "<br>" . $codigocalle;
				    //echo "<br>-" . substr($codigocalle,strlen($codigocalle)-8) . "-";
				    if (substr($codigocalle,strlen($codigocalle)-8)  ==" No. Int"){
					$codigocalle = substr($codigocalle,0,strlen($codigocalle)-8);
				    }
				    //echo "<br>" . $codigocalle;
				    
				}
				    
				    # *****COLUMNA colonia******
				    # si viene vacia la columna de colonia
				if ($datos[$lineacolonia]=='') 
                                  {
				    $codigocolonia ='';
				    echo " ";
				      
                                  }else {
				    $codigovalido = 1;
				    $codigocolonia = trim($datos[$lineacolonia]);
				}
				
				# *****COLUMNA CIUDAD/ESTADO******
				    # si viene vacia la columna de estado
				if ($datos[$lineaciudad]=='') 
                                  {
				    $codigociudad ='';
				    echo " ";
				      
                                  }else {
				    $codigovalido = 1;
				    $codigociudad = trim($datos[$lineaciudad]);
				}

				# *****COLUMNA CIUDAD/ESTADO******
				    # si viene vacia la columna de estado
					if ($datos[$lineaestado]=='') 
					{
					$codigoestado ='';
					echo " ";
						
									}else {
					$codigovalido = 1;
					$codigoestado = trim($datos[$lineaestado]);
				}
				    
				    # *****COLUMNA codigo postal******
				    # si viene vacia la columna de CP
				if ($datos[$lineacp]=='') 
                                  {
				    $codigocp ='';
				    echo " ";
				      
                                  }else {
				    $codigovalido = 1;
				    $codigocp = trim($datos[$lineacp]);
				}
				
				# *****COLUMNA clave proveedor******
				# si viene vacia la columna de clave proveedor
				if ($datos[$lineaclaveproveedor]=='') {
				    $codigoclaveproveedor ='';
				  
                                }else {
				    $codigovalido = 1;
				    $codigoclaveproveedor = trim($datos[$lineaclaveproveedor]);
				}
				
				
				# *****COLUMNA moneda******
				if ($datos[$lineamoneda]=='') {
					$codigomoneda ='MXN';
				
				}else {
					$codigovalido = 1;
					$codigomoneda = trim($datos[$lineamoneda]);
				}

				# *****COLUMNA Email******
				if ($datos[$lineaemail]=='') {
					$codigoemail ='';
				
				}else {
					$codigovalido = 1;
					$codigoemail = trim($datos[$lineaemail]);
				}
				
				# *****COLUMNA Activo******
				    # si viene vacia la columna Activo
					if ($datos[$lineaactivo]=='') 
					{
					$codigoactivo ='';
					echo " ";
						
									}else {
					$codigovalido = 1;
					$codigoactivo = trim($datos[$lineaactivo]);
				}

				# *****COLUMNA CUENTA CONTABLE******
				    # si viene vacia la columna Activo
					if ($datos[$lineacuentacontable]=='') 
					{
					$codigocuentacontable ='';
					echo " ";
						
									}else {
					$codigovalido = 1;
					$codigocuentacontable = trim($datos[$lineacuentacontable]);
				}

				# *****COLUMNA CURP******
				    # si viene vacia la columna Activo
					if ($datos[$lineacurp]=='') 
					{
					$codigocurp ='';
					echo " ";
						
									}else {
					$codigovalido = 1;
					$codigocurp = trim($datos[$lineacurp]);
				}

				# *****COLUMNA CURP******
				    # si viene vacia la columna Activo
					if ($datos[$linearepresentantelegal]=='') 
					{
					$codigorepresentantelegal ='';
					echo " ";
						
									}else {
					$codigovalido = 1;
					$codigorepresentantelegal = trim($datos[$linearepresentantelegal]);
				}
				
				# *****COLUMNA observaciones******
				
				if ($datos[$lineaobservaciones]=='') {
					$codigoobservaciones ='';
				
				}else {
					$codigovalido = 1;
					$codigoobservaciones = trim($datos[$lineaobservaciones]);
				}
				
				
				
				
			    if ($codigovalido==1)
			    {
				# Inserta registro en la tabla "budgets"
					echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$codigonombre."</b></font>";
				    echo "</td>";
					echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigorfc."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigocalle."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigocolonia."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigociudad."</b></font>";
					echo "</td>";
					echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigoestado."</b></font>";
					echo "</td>";
				    echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$codigocp."</b></font>";
				    echo "</td>";
				    /*echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$codigotelefono."</b></font>";
				    echo "</td>";*/
				    echo "<td style='text'>";
					echo "<font size=2 color=Darkblue><b>".$codigoclaveproveedor."</b></font>";
				    echo "</td>";				     
				    echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigomoneda."</b></font>";
				    echo "</td>";				    
				    echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigoemail."</b></font>";
				    echo "</td>";
				    echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigoactivo."</b></font>";
				    echo "</td>";
				    echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigocuentacontable."</b></font>";
				    echo "</td>";
				    echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigocurp."</b></font>";
				    echo "</td>";
				    echo "<td style='text'>";
				    echo "<font size=2 color=Darkblue><b>".$codigorepresentantelegal."</b></font>";
				    echo "</td>";
					echo "</tr>";
					
					
				
				//PARA ASIGNAR EL SUPPLIERID
				//$sql= "SELECT max(cast(supplierid as UNSIGNED )) + 1  FROM suppliers";
				//$result = DB_query($sql, $db);
				//$myrow = DB_fetch_row($result);
				//$SupplierID = add_ceros($myrow['0'],5);


				$SupplierID = $codigoclaveproveedor;
				$validacion  = fnValidarProveedor($db, $SupplierID);
				if ($validacion['success']) {
					prnMsg("Ya existe provedor en el sistema.".$SupplierID,'info');
		
		
				}else{
					$tipoproveedor = 12;	
				$sql = "INSERT INTO suppliers
							(supplierid,
							suppname,
							taxid,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							lat,
							lng,
							currcode,
							suppliersince,
							paymentterms,
							lastpaid,
							lastpaiddate,
							bankact,
							bankref,
							bankpartics,
							remittance,
							taxgroupid,
							factorcompanyid,
							taxref,
							phn,
							port, 
							active, 
							newcode, 
							accion, 
							narrative, 
							limitcredit,
							supptaxname, 
							idspecialty, 
							email, 
							distancia, 
							nombre_movil,
							tipodetercero, 
							flagagentaduanal, 
							u_typediot,
							u_typeoperation,
							typeid,
							ln_tipoPersona, 
							ln_curp,
							ln_representante_legal, 
							nu_interior, 
							nu_exterior, 
							id_nu_entidad_federativa, 
							id_nu_municipio, 
							nu_tesofe, 
							id_nu_tipo)
				VALUES ('$SupplierID',
							'$codigonombre',
							'GACM480921CD3',
							'$codigocalle',
							'$codigocolonia',
							'$codigociudad',
							'$codigoestado',
							'$codigocp',
							'1111111111',
							'0',
							'0',
							'$codigomoneda',
							'2019-10-31',
							'01',
							'0',
							NULL,
							' ',
							' ',
							' ',
							'1',
							'1',
							'1',
							' ',
							' ',
							' ', 
							'$codigoactivo', 
							' ', 
							' ', 
							NULL, 
							NULL,
							NULL, 
							NULL, 
							'$codigoemail', 
							'0', 
							' ',
							'1', 
							NULL, 
							NULL,
							NULL,
							'1',
							'1', 
							'$codigocurp',
							'$codigorepresentantelegal', 
							'O', 
							'O', 
							'22', 
							'12', 
							'0', 
							'1')";
				//echo "<br><br>" . $sql;
				//$result = DB_query($sql,$db);
				
				/*$usql = "UPDATE suppliers
						SET email = '" . $codigoemail. "'
						WHERE supplierid = '" . $SupplierID . "'";
				$result = DB_query($usql,$db);
				*/

				
				$result = DB_query($sql,$db,$ErrMsg);
				prnMsg("Se cargo exitosamente al sistema.",'sucess');
			}
				
				
				if (empty($codigocuentacontable)) {
					prnMsg("Algunas filas no contenian cuenta contable.",'info');
				}else{
					$validacion  = fnValidarCuentaContable($db, $codigocuentacontable);
			     	if (!$validacion['success']) {
					prnMsg("No existe la cuenta contable." . $codigocuentacontable,'info');
			      }else{
					$sql="INSERT INTO `accountxsupplier` (`accountcode`, `supplierid`, `concepto`, `u_typeoperation`, `deductibleflag`, `typeoperationdiot`, `flagdiot`)
					VALUES(	'$codigocuentacontable', '$SupplierID', '$codigonombre', 0, 0, 0, 0)";
					$ErrMsg = _('No pude agregar el registro de DETALLE de cuenta');
					$result = DB_query($sql,$db,$ErrMsg);
					

				  }
					
				}
				
				}
				
			}//fin de ELSE revisar lineas
		    }//fin else
		} // Fin del for que recorre cada linea
		    
		    echo"<br><br>";
			echo "</table>";
			?>
			<component-button type='submit' name='back' value='Regresar' class='glyphicon glyphicon-trash'><a href="index.php"></a></component-button> 
			&nbsp;&nbsp; 
	<?php
	}//fin de cargar archivo
	
} else {
	echo "<form method='post' enctype='multipart/form-data' action=" . $_SERVER['PHP_SELF'] . '?' . SID . ' name="form">';
	echo "<br>";

	?>
	
	<table class="table table-bordered">
	  <thead>
	    <tr class="header-verde">
	      <th scope="col">Razon Social / Nombre</th>
	      <th scope="col">RFC</th>
	      <th scope="col">Calle</th>
	      <th scope="col">Colonia</th>
	      <th scope="col">Ciudad</th>
	      <th scope="col">Estado</th>
	      <th scope="col">C.P</th>
	      <th scope="col">Proveedor</th>
	      <th scope="col">Moneda</th>
	      <th scope="col">Email</th>
	      <th scope="col">Activo</th>
	      <th scope="col">Cuenta contable</th>
	      <th scope="col">CURP</th>
	      <th scope="col">Representante legal</th>
	    </tr>
	  </thead>
	  <tbody>

	  </tbody>
	</table>

	
	<div class='row'></div>

	<div align="left">

	<div class="col-md-4">
		<div class="form-group">
		<label for="exampleInputEmail1">Caracter Separador</label>
		<input class="form-control" type="text"  name='separador' placeholder="Caracter Separador" value="<?php echo $separador; ?>" size="1" maxlength="1" />
		</div>
	</div>

	<div class="col-md-4">
		<div class="form-group">
		<label for="exampleInputEmail1">Archivo (.csv o .txt)</label>
		<input class="form-control" type="file" id="userfile" name="userfile" size="50" />
		</div>
	</div>

	<div class="col-md-4">
		<div class="form-group">
		<label for="exampleInputEmail1">Omitir Primera Fila</label>
		<br>
		<input type="checkbox" id="omitFirstRow" name="omitFirstRow" <?php echo $checked; ?> />
		</div>
	</div>

	</div>

	<div class='row'></div>

	<div align='center'>
	<component-button type="submit" type='submit' name='mostrar' class="glyphicon glyphicon-circle-arrow-up" value="Mostrar Informacion"></component-button>
	<br><br>
	</div>

	<div class='row'></div>

<?php
}
?>
	
			<br><br>


<?php
include 'includes/footer_Index.inc';
?>
